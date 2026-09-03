<?php
/**
 * Google Business Profile API Client for GMB Ranker SEO Automation
 *
 * Provides a secure, bounded transport boundary for Google Business Profile
 * Information API (v1) and Local Posts API (v4). Manages access token sanitization,
 * resource path normalization, payload validation, SSL verification, and Google error translation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_GBP_Client {

    /**
     * Canonical Google API Endpoints
     */
    const ENDPOINT_BUSINESS_INFO      = 'https://mybusinessbusinessinformation.googleapis.com/v1';
    const ENDPOINT_ACCOUNT_MANAGEMENT = 'https://mybusinessaccountmanagement.googleapis.com/v1';
    const ENDPOINT_LOCAL_POSTS         = 'https://mybusiness.googleapis.com/v4';

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
     * Validate and normalize a Google Resource Identifier (Account/Location)
     *
     * @param string $resource_id
     * @param string $expected_prefix 'accounts' or 'locations'
     * @return string|WP_Error
     */
    public static function normalize_resource_id($resource_id, $expected_prefix = 'accounts') {
        if (empty($resource_id) || !is_string($resource_id)) {
            return new WP_Error('invalid_resource_id', __('Resource ID must be a non-empty string.', 'gmb-ranker-seo-automation'));
        }

        $clean = trim($resource_id);

        // Block path traversal and URL injection
        if (strpos($clean, '..') !== false || strpos($clean, '?') !== false || strpos($clean, '#') !== false || strpos($clean, '://') !== false) {
            return new WP_Error('invalid_resource_path', __('Invalid characters or path traversal sequence in Google resource ID.', 'gmb-ranker-seo-automation'));
        }

        // Format as accounts/{id} or locations/{id}
        if (strpos($clean, $expected_prefix . '/') === 0) {
            return $clean;
        }

        // If raw numeric/alphanumeric ID is provided
        if (preg_match('/^[a-zA-Z0-9_\-]+$/', $clean)) {
            return $expected_prefix . '/' . $clean;
        }

        return new WP_Error('malformed_resource_id', sprintf(__('Malformed Google %s identifier format.', 'gmb-ranker-seo-automation'), esc_html($expected_prefix)));
    }

    /**
     * Fetch business locations using validated OAuth/Service Account access token
     *
     * @param string $access_token
     * @param string $account_id
     * @param array  $query_args   Optional pagination or filter args (pageSize, pageToken, readMask)
     * @return array|WP_Error Normalized locations list array or WP_Error
     */
    public function get_locations($access_token, $account_id, array $query_args = array()) {
        $token = self::sanitize_token($access_token);
        if (empty($token)) {
            return new WP_Error('missing_credentials', __('Missing Google access token.', 'gmb-ranker-seo-automation'));
        }

        $account_resource = self::normalize_resource_id($account_id, 'accounts');
        if (is_wp_error($account_resource)) {
            return $account_resource;
        }

        $url = self::ENDPOINT_BUSINESS_INFO . '/' . $account_resource . '/locations';

        // Support pagination and readMask
        $params = array();
        if (!empty($query_args['pageSize']) && is_numeric($query_args['pageSize'])) {
            $params['pageSize'] = min(100, max(1, intval($query_args['pageSize'])));
        }
        if (!empty($query_args['pageToken']) && is_string($query_args['pageToken'])) {
            $params['pageToken'] = sanitize_text_field($query_args['pageToken']);
        }
        if (!empty($query_args['readMask']) && is_string($query_args['readMask'])) {
            $params['readMask'] = sanitize_text_field($query_args['readMask']);
        }

        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'timeout'     => 25,
            'redirection' => 0, // Prevent SSRF redirect loops
            'sslverify'   => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('gbp_transport_failed', $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($data['error']['message']) 
                ? sanitize_text_field($data['error']['message']) 
                : sprintf(__('Google Business Profile API HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
            return new WP_Error('gbp_api_error', $error_msg, array('status' => $code, 'details' => $data));
        }

        if (!is_array($data)) {
            return new WP_Error('invalid_response', __('Malformed response received from Google Business Profile API.', 'gmb-ranker-seo-automation'));
        }

        return array(
            'locations'     => isset($data['locations']) && is_array($data['locations']) ? $data['locations'] : array(),
            'nextPageToken' => isset($data['nextPageToken']) ? sanitize_text_field($data['nextPageToken']) : '',
            'raw'           => $data,
        );
    }

    /**
     * Create a Local Post on Google Business Profile
     *
     * @param string $access_token
     * @param string $location_id
     * @param array  $post_data
     * @return array|WP_Error Normalized local post response array or WP_Error
     */
    public function create_local_post($access_token, $location_id, array $post_data) {
        $token = self::sanitize_token($access_token);
        if (empty($token)) {
            return new WP_Error('missing_credentials', __('Missing Google access token.', 'gmb-ranker-seo-automation'));
        }

        $location_resource = self::normalize_resource_id($location_id, 'locations');
        if (is_wp_error($location_resource)) {
            return $location_resource;
        }

        // Validate Local Post payload
        if (empty($post_data) || !is_array($post_data)) {
            return new WP_Error('invalid_post_data', __('Local post payload must be a non-empty array.', 'gmb-ranker-seo-automation'));
        }

        $clean_post = array();

        // Language Code
        if (!empty($post_data['languageCode']) && is_string($post_data['languageCode'])) {
            $clean_post['languageCode'] = sanitize_text_field($post_data['languageCode']);
        }

        // Summary / Post Text Content
        if (!empty($post_data['summary']) && is_string($post_data['summary'])) {
            $clean_post['summary'] = sanitize_textarea_field(wp_unslash($post_data['summary']));
        }

        // Topic Type
        $allowed_topic_types = array('STANDARD', 'EVENT', 'OFFER', 'ALERT');
        $topic_type          = !empty($post_data['topicType']) ? strtoupper(trim($post_data['topicType'])) : 'STANDARD';
        $clean_post['topicType'] = in_array($topic_type, $allowed_topic_types, true) ? $topic_type : 'STANDARD';

        // Call to Action (CTA)
        if (!empty($post_data['callToAction']) && is_array($post_data['callToAction'])) {
            $action_type = !empty($post_data['callToAction']['actionType']) ? strtoupper(trim($post_data['callToAction']['actionType'])) : 'LEARN_MORE';
            $cta_url     = !empty($post_data['callToAction']['url']) && filter_var($post_data['callToAction']['url'], FILTER_VALIDATE_URL)
                ? esc_url_raw($post_data['callToAction']['url']) 
                : '';

            if (!empty($cta_url)) {
                $clean_post['callToAction'] = array(
                    'actionType' => $action_type,
                    'url'        => $cta_url,
                );
            }
        }

        // Media Items
        if (!empty($post_data['media']) && is_array($post_data['media'])) {
            $clean_media = array();
            foreach ($post_data['media'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $media_url = !empty($item['sourceUrl']) && filter_var($item['sourceUrl'], FILTER_VALIDATE_URL) ? esc_url_raw($item['sourceUrl']) : '';
                $media_format = !empty($item['mediaFormat']) ? strtoupper(trim($item['mediaFormat'])) : 'PHOTO';
                if (!empty($media_url)) {
                    $clean_media[] = array(
                        'mediaFormat' => in_array($media_format, array('PHOTO', 'VIDEO'), true) ? $media_format : 'PHOTO',
                        'sourceUrl'   => $media_url,
                    );
                }
            }
            if (!empty($clean_media)) {
                $clean_post['media'] = $clean_media;
            }
        }

        // Event / Offer Data
        if (!empty($post_data['event']) && is_array($post_data['event'])) {
            $clean_post['event'] = $post_data['event'];
        }
        if (!empty($post_data['offer']) && is_array($post_data['offer'])) {
            $clean_post['offer'] = $post_data['offer'];
        }

        $json_body = wp_json_encode($clean_post);
        if ($json_body === false) {
            return new WP_Error('json_encode_failed', __('Failed to serialize Local Post payload to JSON.', 'gmb-ranker-seo-automation'));
        }

        $url = self::ENDPOINT_LOCAL_POSTS . '/' . $location_resource . '/localPosts';

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'        => $json_body,
            'timeout'     => 30,
            'redirection' => 0,
            'sslverify'   => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('gbp_transport_failed', $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($data['error']['message']) 
                ? sanitize_text_field($data['error']['message']) 
                : sprintf(__('Google Local Post API HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
            return new WP_Error('gbp_post_failed', $error_msg, array('status' => $code, 'details' => $data));
        }

        if (!is_array($data)) {
            return new WP_Error('invalid_response', __('Invalid JSON response received from GBP post endpoint.', 'gmb-ranker-seo-automation'));
        }

        return array(
            'success'      => true,
            'name'         => isset($data['name']) ? sanitize_text_field($data['name']) : '',
            'searchUrl'    => isset($data['searchUrl']) ? esc_url_raw($data['searchUrl']) : '',
            'state'        => isset($data['state']) ? sanitize_text_field($data['state']) : 'LIVE',
            'raw'          => $data,
        );
    }
}
