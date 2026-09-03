<?php
/**
 * IndexNow Protocol API Client for GMB Ranker SEO Automation
 *
 * Provides a secure, bounded transport boundary for the IndexNow search engine
 * submission protocol (Microsoft Bing, Yandex, Naver, Seznam). Manages host validation,
 * key format verification, URL domain matching, SSL verification, and status code translation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_IndexNow_Client {

    /**
     * IndexNow Endpoint Constant
     */
    const INDEXNOW_API = 'https://api.indexnow.org/indexnow';

    /**
     * Sanitize and validate host string
     *
     * @param string $host
     * @return string|WP_Error
     */
    public static function validate_host($host) {
        if (empty($host) || !is_string($host)) {
            return new WP_Error('invalid_host', __('Host must be a non-empty string.', 'gmb-ranker-seo-automation'));
        }

        $clean = strtolower(trim($host));

        // Strip scheme or path if accidentally passed
        if (strpos($clean, '://') !== false) {
            $clean = wp_parse_url($clean, PHP_URL_HOST);
        } else {
            $clean = preg_replace('/[\/:?#].*$/', '', $clean);
        }

        if (empty($clean) || !preg_match('/^[a-z0-9\.\-]+$/i', $clean)) {
            return new WP_Error('invalid_host_format', __('Invalid hostname format for IndexNow payload.', 'gmb-ranker-seo-automation'));
        }

        return $clean;
    }

    /**
     * Sanitize and validate IndexNow API Key
     *
     * @param string $key
     * @return string|WP_Error
     */
    public static function validate_key($key) {
        if (empty($key) || !is_string($key)) {
            return new WP_Error('invalid_key', __('IndexNow key must be a non-empty string.', 'gmb-ranker-seo-automation'));
        }

        $clean = trim($key);
        // IndexNow keys are 8-128 hex or alphanumeric chars
        if (!preg_match('/^[a-zA-Z0-9\-]{8,128}$/', $clean)) {
            return new WP_Error('invalid_key_format', __('IndexNow key must be an alphanumeric string between 8 and 128 characters.', 'gmb-ranker-seo-automation'));
        }

        return $clean;
    }

    /**
     * Submit single or multiple URLs to IndexNow API
     *
     * @param string $host         Target website host (e.g. example.com)
     * @param string $key          IndexNow API Key
     * @param array  $urls         Array of full URLs to submit
     * @param string $key_location Optional key location URL (e.g. https://example.com/key.txt)
     * @return array|WP_Error Normalized response array or WP_Error
     */
    public function submit_to_indexnow($host, $key, array $urls, $key_location = '') {
        $valid_host = self::validate_host($host);
        if (is_wp_error($valid_host)) {
            return $valid_host;
        }

        $valid_key = self::validate_key($key);
        if (is_wp_error($valid_key)) {
            return $valid_key;
        }

        if (empty($urls)) {
            return new WP_Error('empty_url_list', __('URL list for IndexNow submission cannot be empty.', 'gmb-ranker-seo-automation'));
        }

        // Validate URLs and ensure domain matching
        $clean_urls = array();
        foreach ($urls as $u) {
            if (!is_string($u) || !filter_var($u, FILTER_VALIDATE_URL)) {
                continue;
            }

            $u_scheme = strtolower(wp_parse_url($u, PHP_URL_SCHEME) ?: '');
            $u_host   = strtolower(wp_parse_url($u, PHP_URL_HOST) ?: '');

            if (!in_array($u_scheme, array('http', 'https'), true)) {
                continue;
            }

            // Enforce domain matching to prevent cross-domain submission spoofing
            if ($u_host !== $valid_host && strpos($u_host, '.' . $valid_host) === false) {
                continue;
            }

            $clean_urls[] = esc_url_raw($u);
        }

        $clean_urls = array_values(array_unique($clean_urls));

        if (empty($clean_urls)) {
            return new WP_Error('no_valid_matching_urls', __('No valid URLs matching host domain remaining after verification.', 'gmb-ranker-seo-automation'));
        }

        // Enforce maximum batch size limit (10,000 URLs max per IndexNow protocol)
        if (count($clean_urls) > 10000) {
            $clean_urls = array_slice($clean_urls, 0, 10000);
        }

        // Resolve Key Location URL
        if (empty($key_location) || !filter_var($key_location, FILTER_VALIDATE_URL)) {
            $key_location = esc_url_raw(home_url('/' . $valid_key . '.txt'));
        }

        $payload = array(
            'host'        => $valid_host,
            'key'         => $valid_key,
            'keyLocation' => $key_location,
            'urlList'     => $clean_urls,
        );

        $json_body = wp_json_encode($payload);
        if ($json_body === false) {
            return new WP_Error('json_encode_failed', __('Failed to serialize IndexNow payload to JSON.', 'gmb-ranker-seo-automation'));
        }

        $response = wp_remote_post(self::INDEXNOW_API, array(
            'headers'     => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json',
            ),
            'body'        => $json_body,
            'timeout'     => 25,
            'redirection' => 0, // Block SSRF redirect loops
            'sslverify'   => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('indexnow_transport_failed', $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        $is_success = ($code === 200 || $code === 202);
        $message    = '';

        switch ($code) {
            case 200:
                $message = __('200 OK — URL successfully submitted to IndexNow API.', 'gmb-ranker-seo-automation');
                break;
            case 202:
                $message = __('202 Accepted — URL received; IndexNow key location pending validation.', 'gmb-ranker-seo-automation');
                break;
            case 400:
                $message = __('400 Bad Request — Invalid request parameters or key format.', 'gmb-ranker-seo-automation');
                break;
            case 403:
                $message = __('403 Forbidden — Invalid key or key not found at keyLocation.', 'gmb-ranker-seo-automation');
                break;
            case 422:
                $message = __('422 Unprocessable Entity — Submitted URLs do not match host domain or key location.', 'gmb-ranker-seo-automation');
                break;
            case 429:
                $message = __('429 Too Many Requests — Rate limit exceeded for IndexNow API.', 'gmb-ranker-seo-automation');
                break;
            default:
                $message = sprintf(__('IndexNow API returned HTTP status code %d', 'gmb-ranker-seo-automation'), $code);
                break;
        }

        return array(
            'success'     => $is_success,
            'status_code' => $code,
            'message'     => $message,
            'body'        => $body,
            'submitted'   => count($clean_urls),
        );
    }
}
