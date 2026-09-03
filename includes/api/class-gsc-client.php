<?php
/**
 * Google Search Console API Transport Client for GMB Ranker SEO Automation
 *
 * Provides a secure, bounded transport boundary for Google Search Console Search Analytics API.
 * Handles property normalization (domain & URL-prefix), access token sanitization, date/dimension
 * payload validation, SSL verification, and Google Search Console error translation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_GSC_Client {

    /**
     * Canonical Google Search Console API Endpoints
     */
    const GSC_API_BASE        = 'https://searchconsole.googleapis.com/v1';
    const GSC_API_BASE_LEGACY = 'https://www.googleapis.com/webmasters/v3';

    /**
     * Sanitize Bearer Access Tokens to prevent header injection
     *
     * @param string $token
     * @return string
     */
    protected static function sanitize_token($token) {
        if (!is_string($token)) {
            return '';
        }
        return trim(str_replace(array("\r", "\n", "\0"), '', $token));
    }

    /**
     * Normalize and validate a Search Console property (Domain or URL-Prefix)
     *
     * @param string $site_url
     * @return string|WP_Error
     */
    public static function normalize_property($site_url) {
        if (empty($site_url) || !is_string($site_url)) {
            return new WP_Error('invalid_property', __('Search Console property must be a non-empty string.', 'gmb-ranker-seo-automation'));
        }

        $clean = trim($site_url);

        // Block path traversal and header injection
        if (strpos($clean, "\r") !== false || strpos($clean, "\n") !== false || strpos($clean, '..') !== false) {
            return new WP_Error('invalid_property_path', __('Invalid characters or path traversal sequence in Search Console property.', 'gmb-ranker-seo-automation'));
        }

        // Domain property: sc-domain:example.com
        if (strpos($clean, 'sc-domain:') === 0) {
            $domain = substr($clean, 10);
            if (preg_match('/^[a-zA-Z0-9\.\-]+$/', $domain)) {
                return 'sc-domain:' . strtolower($domain);
            }
            return new WP_Error('invalid_domain_property', __('Invalid domain structure for Search Console domain property.', 'gmb-ranker-seo-automation'));
        }

        // URL-prefix property: https://example.com/
        if (filter_var($clean, FILTER_VALIDATE_URL)) {
            return esc_url_raw($clean);
        }

        return new WP_Error('malformed_property', __('Malformed Search Console property format.', 'gmb-ranker-seo-automation'));
    }

    /**
     * Validate Search Analytics Query Parameters
     *
     * @param array $query_params
     * @return array|WP_Error Normalized parameters
     */
    public static function validate_query_params(array $query_params) {
        $clean = array();

        // Dates (ISO Y-m-d)
        if (!empty($query_params['startDate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $query_params['startDate'])) {
            $clean['startDate'] = $query_params['startDate'];
        } else {
            $clean['startDate'] = gmdate('Y-m-d', strtotime('-30 days'));
        }

        if (!empty($query_params['endDate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $query_params['endDate'])) {
            $clean['endDate'] = $query_params['endDate'];
        } else {
            $clean['endDate'] = gmdate('Y-m-d', strtotime('-2 days'));
        }

        // Dimensions
        $allowed_dimensions = array('date', 'query', 'page', 'country', 'device', 'searchAppearance');
        if (!empty($query_params['dimensions']) && is_array($query_params['dimensions'])) {
            $clean_dims = array();
            foreach ($query_params['dimensions'] as $dim) {
                $dim_key = strtolower(trim((string)$dim));
                if (in_array($dim_key, $allowed_dimensions, true)) {
                    $clean_dims[] = $dim_key;
                }
            }
            if (!empty($clean_dims)) {
                $clean['dimensions'] = array_unique($clean_dims);
            }
        }

        // Row Limit & Start Row
        if (isset($query_params['rowLimit']) && is_numeric($query_params['rowLimit'])) {
            $clean['rowLimit'] = min(25000, max(1, intval($query_params['rowLimit'])));
        } else {
            $clean['rowLimit'] = 1000;
        }

        if (isset($query_params['startRow']) && is_numeric($query_params['startRow'])) {
            $clean['startRow'] = max(0, intval($query_params['startRow']));
        }

        // Type / Search Type (WEB, IMAGE, VIDEO, NEWS, DISCOVER, GOOGLE_NEWS)
        $allowed_types = array('web', 'image', 'video', 'news', 'discover', 'googleNews');
        if (!empty($query_params['type']) && in_array(strtolower($query_params['type']), array_map('strtolower', $allowed_types), true)) {
            $clean['type'] = strtolower($query_params['type']);
        }

        // Dimension Filter Groups
        if (!empty($query_params['dimensionFilterGroups']) && is_array($query_params['dimensionFilterGroups'])) {
            $clean['dimensionFilterGroups'] = $query_params['dimensionFilterGroups'];
        }

        return $clean;
    }

    /**
     * Query Search Analytics data
     *
     * @param string $access_token
     * @param string $site_url
     * @param array  $query_params
     * @return array|WP_Error Normalized analytics result or WP_Error
     */
    public function query_search_analytics($access_token, $site_url, array $query_params) {
        $token = self::sanitize_token($access_token);
        if (empty($token)) {
            return new WP_Error('missing_token', __('No valid access token available for Search Console query.', 'gmb-ranker-seo-automation'));
        }

        $property = self::normalize_property($site_url);
        if (is_wp_error($property)) {
            return $property;
        }

        $validated_params = self::validate_query_params($query_params);
        if (is_wp_error($validated_params)) {
            return $validated_params;
        }

        $json_body = wp_json_encode($validated_params);
        if ($json_body === false) {
            return new WP_Error('json_encode_failed', __('Failed to serialize Search Analytics request parameters to JSON.', 'gmb-ranker-seo-automation'));
        }

        // Attempt Modern API endpoint first, fallback to Legacy v3 endpoint
        $endpoints = array(
            self::GSC_API_BASE . '/sites/' . urlencode($property) . '/searchAnalytics/query',
            self::GSC_API_BASE_LEGACY . '/sites/' . urlencode($property) . '/searchAnalytics/query',
        );

        $last_error = null;
        foreach ($endpoints as $endpoint) {
            $response = wp_remote_post($endpoint, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ),
                'body'        => $json_body,
                'timeout'     => 30,
                'redirection' => 0, // Block SSRF redirect loops
                'sslverify'   => true,
            ));

            if (is_wp_error($response)) {
                $last_error = new WP_Error('gsc_transport_failed', $response->get_error_message());
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = !empty($body) ? json_decode($body, true) : null;

            if ($code < 200 || $code >= 300) {
                $error_msg = isset($data['error']['message']) 
                    ? sanitize_text_field($data['error']['message']) 
                    : sprintf(__('Google Search Console API HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
                $last_error = new WP_Error('gsc_api_error', $error_msg, array('status' => $code, 'details' => $data));
                continue;
            }

            if (!is_array($data)) {
                $last_error = new WP_Error('invalid_gsc_response', __('Malformed JSON response received from Search Console API.', 'gmb-ranker-seo-automation'));
                continue;
            }

            return array(
                'property' => $property,
                'rows'     => isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : array(),
                'raw'      => $data,
            );
        }

        return $last_error ?: new WP_Error('gsc_query_failed', __('Failed to retrieve Search Analytics data from Google Search Console.', 'gmb-ranker-seo-automation'));
    }
}
