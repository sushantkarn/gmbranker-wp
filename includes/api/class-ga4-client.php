<?php
/**
 * Google Analytics 4 API Transport Client for GMB Ranker SEO Automation
 *
 * Provides a secure transport layer for Google Analytics 4 Data API (v1beta)
 * and Google Analytics Admin API (v1alpha).
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_GA4_Client {

    /**
     * Canonical Google Analytics API Endpoints
     */
    const GA4_DATA_API_BASE  = 'https://analyticsdata.googleapis.com/v1beta';
    const GA4_ADMIN_API_BASE = 'https://analyticsadmin.googleapis.com/v1alpha';

    /**
     * Sanitize Bearer Access Tokens
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
     * Normalize GA4 Property ID (e.g., '123456789' or 'properties/123456789')
     *
     * @param string $property_id
     * @return string
     */
    public static function normalize_property_id($property_id) {
        $clean = preg_replace('/[^0-9]/', '', (string)$property_id);
        return $clean;
    }

    /**
     * Query GA4 Data API runReport endpoint
     *
     * @param string $token Bearer OAuth Access Token
     * @param string $property_id GA4 Numeric Property ID
     * @param array $payload Query parameters (dateRanges, metrics, dimensions, etc.)
     * @return array|WP_Error
     */
    public function run_report($token, $property_id, array $payload = array()) {
        $clean_token = self::sanitize_token($token);
        if (empty($clean_token)) {
            return new WP_Error('missing_token', __('No valid access token available for Google Analytics query.', 'gmb-ranker-seo-automation'));
        }

        $numeric_id = self::normalize_property_id($property_id);
        if (empty($numeric_id)) {
            return new WP_Error('invalid_property_id', __('Google Analytics 4 Property ID must be valid numbers.', 'gmb-ranker-seo-automation'));
        }

        $endpoint = self::GA4_DATA_API_BASE . '/properties/' . $numeric_id . ':runReport';

        $body_json = wp_json_encode($payload);
        if (false === $body_json) {
            return new WP_Error('json_encode_error', __('Failed to encode GA4 API payload.', 'gmb-ranker-seo-automation'));
        }

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $clean_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'      => $body_json,
            'timeout'   => 20,
            'sslverify' => true,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            $err_data = json_decode($body, true);
            $msg      = isset($err_data['error']['message']) ? $err_data['error']['message'] : sprintf(__('Google Analytics API Error HTTP (%d)', 'gmb-ranker-seo-automation'), $code);
            return new WP_Error('ga4_api_error', $msg, array('code' => $code, 'body' => $body));
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return new WP_Error('invalid_ga4_response', __('Malformed JSON response received from Google Analytics API.', 'gmb-ranker-seo-automation'));
        }

        return $data;
    }

    /**
     * List GA4 Account Summaries & Properties via Admin API
     *
     * @param string $token
     * @return array|WP_Error
     */
    public function list_account_summaries($token) {
        $clean_token = self::sanitize_token($token);
        if (empty($clean_token)) {
            return new WP_Error('missing_token', __('No valid access token available.', 'gmb-ranker-seo-automation'));
        }

        $endpoint = self::GA4_ADMIN_API_BASE . '/accountSummaries';

        $response = wp_remote_get($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $clean_token,
                'Accept'        => 'application/json',
            ),
            'timeout'   => 20,
            'sslverify' => true,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            return new WP_Error('ga4_admin_error', sprintf(__('Google Analytics Admin API Error (%d)', 'gmb-ranker-seo-automation'), $code));
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : new WP_Error('invalid_json', __('Invalid JSON from GA4 Admin API.', 'gmb-ranker-seo-automation'));
    }
}
