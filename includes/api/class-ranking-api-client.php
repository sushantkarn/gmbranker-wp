<?php
/**
 * GMB Ranker Cloud API Client
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ranking_Client {

    const CLOUD_API_BASE = 'https://gmbranker.org/api';

    /**
     * Get API secret key
     *
     * @return string
     */
    protected function get_api_key() {
        return (string) get_option('gmb_ranker_api_key', '');
    }

    /**
     * Send heartbeat / handshake to GMB Ranker cloud
     *
     * @return array|WP_Error
     */
    public function send_heartbeat() {
        $key = $this->get_api_key();
        if (empty($key)) {
            return new WP_Error('missing_api_key', 'GMB Ranker API key is not configured.');
        }

        $url = self::CLOUD_API_BASE . '/site/heartbeat';
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode(array(
                'site_url' => home_url(),
                'version'  => defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0',
                'active'   => true,
            )),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
