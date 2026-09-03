<?php
/**
 * Google Places API Transport Client for GMB Ranker SEO Automation
 *
 * Provides a secure, bounded transport boundary for Google Places API.
 * Handles Place ID normalization, API key sanitization, field masking,
 * SSL verification, Google Places status code handling (OK, REQUEST_DENIED, OVER_QUERY_LIMIT),
 * and normalized Place details response contracts.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Places_Client {

    /**
     * Canonical Google Places API Endpoints
     */
    const PLACES_BASE     = 'https://maps.googleapis.com/maps/api/place';
    const PLACES_NEW_BASE = 'https://places.googleapis.com/v1/places';

    /**
     * Sanitize API Key to prevent header injection or URL corruption
     *
     * @param string $key
     * @return string
     */
    protected static function sanitize_key($key) {
        if (!is_string($key)) {
            return '';
        }
        return trim(str_replace(array("\r", "\n", "\0"), '', $key));
    }

    /**
     * Normalize and validate Google Place ID
     *
     * @param string $place_id
     * @return string|WP_Error
     */
    public static function normalize_place_id($place_id) {
        if (empty($place_id) || !is_string($place_id)) {
            return new WP_Error('invalid_place_id', __('Google Place ID must be a non-empty string.', 'gmb-ranker-seo-automation'));
        }

        $clean = trim($place_id);

        // Block path traversal and header/URL injection
        if (strpos($clean, "\r") !== false || strpos($clean, "\n") !== false || strpos($clean, '..') !== false || strpos($clean, '?') !== false || strpos($clean, '#') !== false) {
            return new WP_Error('invalid_place_id_format', __('Invalid characters or path traversal sequence in Place ID.', 'gmb-ranker-seo-automation'));
        }

        // Place IDs are alphanumeric strings with underscores and hyphens (ChIJ...)
        if (preg_match('/^[a-zA-Z0-9_\-]+$/', $clean)) {
            return $clean;
        }

        return new WP_Error('malformed_place_id', __('Malformed Google Place ID format.', 'gmb-ranker-seo-automation'));
    }

    /**
     * Fetch Place details by Place ID
     *
     * @param string $api_key  Google Maps / Places API key
     * @param string $place_id Google Place ID (ChIJ...)
     * @param array  $fields   Optional array or string of requested fields
     * @return array|WP_Error Normalized place details array or WP_Error
     */
    public function get_place_details($api_key, $place_id, $fields = array()) {
        $key = self::sanitize_key($api_key);
        if (empty($key)) {
            return new WP_Error('missing_api_key', __('Google Places API key is required.', 'gmb-ranker-seo-automation'));
        }

        $valid_place_id = self::normalize_place_id($place_id);
        if (is_wp_error($valid_place_id)) {
            return $valid_place_id;
        }

        // Default requested fields
        $default_fields = array('name', 'formatted_address', 'geometry', 'rating', 'user_ratings_total', 'opening_hours', 'formatted_phone_number', 'website', 'url');
        if (!empty($fields)) {
            $requested_fields = is_array($fields) ? $fields : explode(',', (string)$fields);
            $clean_fields     = array_map('sanitize_key', $requested_fields);
            $fields_str       = implode(',', array_unique(array_filter($clean_fields)));
        } else {
            $fields_str = implode(',', $default_fields);
        }

        // Note: Do NOT pre-urlencode parameters passed to add_query_arg!
        $url = add_query_arg(array(
            'place_id' => $valid_place_id,
            'key'      => $key,
            'fields'   => $fields_str,
        ), self::PLACES_BASE . '/details/json');

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/json',
            ),
            'timeout'     => 25,
            'redirection' => 0, // Block SSRF redirect loops
            'sslverify'   => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('places_transport_failed', $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($data['error_message']) 
                ? sanitize_text_field($data['error_message']) 
                : sprintf(__('Google Places API HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
            return new WP_Error('places_api_error', $error_msg, array('status' => $code, 'details' => $data));
        }

        if (!is_array($data)) {
            return new WP_Error('invalid_places_response', __('Malformed JSON response received from Google Places API.', 'gmb-ranker-seo-automation'));
        }

        // Check Google Places API internal status code
        $places_status = isset($data['status']) ? strtoupper(trim($data['status'])) : 'UNKNOWN';

        if ($places_status !== 'OK') {
            $error_msg = isset($data['error_message']) ? sanitize_text_field($data['error_message']) : sprintf(__('Google Places API status: %s', 'gmb-ranker-seo-automation'), $places_status);
            
            switch ($places_status) {
                case 'ZERO_RESULTS':
                    return new WP_Error('places_zero_results', __('No Place details found for the specified Place ID.', 'gmb-ranker-seo-automation'));
                case 'OVER_QUERY_LIMIT':
                    return new WP_Error('places_quota_exceeded', __('Google Places API quota exceeded or billing not enabled.', 'gmb-ranker-seo-automation'));
                case 'REQUEST_DENIED':
                    return new WP_Error('places_request_denied', __('Google Places API request denied. Check API key restrictions.', 'gmb-ranker-seo-automation'));
                case 'INVALID_REQUEST':
                    return new WP_Error('places_invalid_request', __('Invalid Place ID or request parameters.', 'gmb-ranker-seo-automation'));
                default:
                    return new WP_Error('places_status_error', $error_msg, array('status' => $places_status));
            }
        }

        $result = isset($data['result']) && is_array($data['result']) ? $data['result'] : array();

        // Normalize Places Output Schema
        return array(
            'place_id'           => $valid_place_id,
            'name'               => isset($result['name']) ? sanitize_text_field($result['name']) : '',
            'formatted_address'  => isset($result['formatted_address']) ? sanitize_text_field($result['formatted_address']) : '',
            'phone'              => isset($result['formatted_phone_number']) ? sanitize_text_field($result['formatted_phone_number']) : '',
            'website'            => isset($result['website']) ? esc_url_raw($result['website']) : '',
            'google_maps_url'    => isset($result['url']) ? esc_url_raw($result['url']) : '',
            'rating'             => isset($result['rating']) ? floatval($result['rating']) : 0.0,
            'user_ratings_total' => isset($result['user_ratings_total']) ? intval($result['user_ratings_total']) : 0,
            'geo'                => array(
                'lat' => isset($result['geometry']['location']['lat']) ? floatval($result['geometry']['location']['lat']) : null,
                'lng' => isset($result['geometry']['location']['lng']) ? floatval($result['geometry']['location']['lng']) : null,
            ),
            'opening_hours'      => isset($result['opening_hours']) ? $result['opening_hours'] : array(),
            'raw'                => $result,
        );
    }
}
