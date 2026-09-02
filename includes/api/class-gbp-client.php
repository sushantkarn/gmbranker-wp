<?php
/**
 * Google Business Profile API Client for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_GBP_Client {

    const ENDPOINT_BASE = 'https://mybusinessbusinessinformation.googleapis.com/v1';

    /**
     * Fetch business locations using service account or OAuth token
     *
     * @param string $access_token
     * @param string $account_id
     * @return array|WP_Error
     */
    public function get_locations($access_token, $account_id) {
        if (empty($access_token) || empty($account_id)) {
            return new WP_Error('missing_credentials', 'Missing access token or account ID for GBP API.');
        }

        $url = self::ENDPOINT_BASE . '/' . trim($account_id) . '/locations';
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 20,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return is_array($data) ? $data : new WP_Error('invalid_response', 'Malformed response from Google Business Profile API.');
    }

    /**
     * Create local post on Google Business Profile
     *
     * @param string $access_token
     * @param string $location_id
     * @param array  $post_data
     * @return array|WP_Error
     */
    public function create_local_post($access_token, $location_id, array $post_data) {
        $url = 'https://mybusiness.googleapis.com/v4/' . trim($location_id) . '/localPosts';
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($post_data),
            'timeout' => 25,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : new WP_Error('invalid_response', 'Invalid response from GBP post endpoint.');
    }
}
