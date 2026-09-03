<?php
/**
 * GMB Ranker First-Party Cloud API Transport Client
 *
 * Provides a secure, bounded transport boundary for GMB Ranker Cloud API services
 * (heartbeat, licensing, workspace synchronization, and entitlement checks).
 * Manages Bearer token sanitization, site identity normalization, SSL verification,
 * and cloud status code error translation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ranking_Client {

    /**
     * Immutable Canonical GMB Ranker Cloud API Endpoint Base
     */
    const CLOUD_API_BASE = 'https://gmbranker.org/api';

    /**
     * Get the active Cloud API Base URL (filterable for enterprise/test environments)
     *
     * @return string
     */
    public static function get_api_base() {
        $base = apply_filters('gmb_ranker_cloud_api_base', self::CLOUD_API_BASE);
        return filter_var($base, FILTER_VALIDATE_URL) ? esc_url_raw($base) : self::CLOUD_API_BASE;
    }

    /**
     * Retrieve and sanitize the configured GMB Ranker secret API key
     *
     * @return string
     */
    protected function get_api_key() {
        $key = get_option('gmb_ranker_api_key', '');
        if (!is_string($key)) {
            return '';
        }
        return trim(str_replace(array("\r", "\n", "\0"), '', $key));
    }

    /**
     * Normalize canonical Site Identity URL
     *
     * @return string
     */
    public static function get_canonical_site_url() {
        $url = home_url('/');
        return esc_url_raw(untrailingslashit($url));
    }

    /**
     * Send heartbeat / handshake to GMB Ranker Cloud
     *
     * @param array $extra_payload Optional extra telemetry or metadata
     * @return array|WP_Error Normalized Cloud API response array or WP_Error
     */
    public function send_heartbeat(array $extra_payload = array()) {
        $key = $this->get_api_key();
        if (empty($key)) {
            return new WP_Error('missing_api_key', __('GMB Ranker API secret key is not configured.', 'gmb-ranker-seo-automation'));
        }

        // Validate Key format basic sanity
        if (strlen($key) < 8) {
            return new WP_Error('invalid_api_key', __('Configured GMB Ranker API secret key format is invalid.', 'gmb-ranker-seo-automation'));
        }

        $endpoint = self::get_api_base() . '/site/heartbeat';

        $payload = array_merge(array(
            'site_url'    => self::get_canonical_site_url(),
            'version'     => defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0',
            'active'      => true,
            'wp_version'  => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'locale'      => get_locale(),
            'is_multisite'=> is_multisite(),
        ), $extra_payload);

        $json_body = wp_json_encode($payload);
        if ($json_body === false) {
            return new WP_Error('json_encode_failed', __('Failed to serialize heartbeat payload to JSON.', 'gmb-ranker-seo-automation'));
        }

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json; charset=utf-8',
                'Accept'        => 'application/json',
            ),
            'body'        => $json_body,
            'timeout'     => 25,
            'redirection' => 0, // Block SSRF redirect loops
            'sslverify'   => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('cloud_transport_failed', $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($data['message']) 
                ? sanitize_text_field($data['message']) 
                : sprintf(__('GMB Ranker Cloud API HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
            
            switch ($code) {
                case 401:
                    return new WP_Error('cloud_unauthorized', __('Unauthorized: Invalid or expired GMB Ranker API key.', 'gmb-ranker-seo-automation'), array('status' => 401));
                case 403:
                    return new WP_Error('cloud_forbidden', __('Forbidden: Active subscription or workspace access issue.', 'gmb-ranker-seo-automation'), array('status' => 403));
                case 429:
                    return new WP_Error('cloud_rate_limited', __('Rate Limited: GMB Ranker Cloud API rate limit exceeded.', 'gmb-ranker-seo-automation'), array('status' => 429));
                default:
                    return new WP_Error('cloud_api_error', $error_msg, array('status' => $code, 'details' => $data));
            }
        }

        if (!is_array($data)) {
            return new WP_Error('invalid_cloud_response', __('Malformed JSON response received from GMB Ranker Cloud API.', 'gmb-ranker-seo-automation'));
        }

        return array(
            'success'      => true,
            'status_code'  => $code,
            'workspace_id' => isset($data['workspace_id']) ? sanitize_text_field($data['workspace_id']) : '',
            'entitlements' => isset($data['entitlements']) && is_array($data['entitlements']) ? $data['entitlements'] : array(),
            'message'      => isset($data['message']) ? sanitize_text_field($data['message']) : __('Heartbeat successful.', 'gmb-ranker-seo-automation'),
            'raw'          => $data,
        );
    }
}
