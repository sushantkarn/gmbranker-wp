<?php
/**
 * Google Search Console API Client for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_GSC_Client {

    const GSC_API_BASE = 'https://www.googleapis.com/webmasters/v3';

    /**
     * Query Search Analytics data
     *
     * @param string $access_token
     * @param string $site_url
     * @param array  $query_params
     * @return array|WP_Error
     */
    public function query_search_analytics($access_token, $site_url, array $query_params) {
        if (empty($access_token)) {
            return new WP_Error('missing_token', 'No access token available for Search Console query.');
        }

        $url = self::GSC_API_BASE . '/sites/' . urlencode($site_url) . '/searchAnalytics/query';
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($query_params),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return is_array($data) ? $data : new WP_Error('invalid_gsc_response', 'Invalid response from GSC API.');
    }
}
