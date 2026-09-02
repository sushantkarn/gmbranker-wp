<?php
/**
 * REST Auth Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Auth_Controller {

    /**
     * Authenticate incoming REST API request via API Key
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function authenticate_request($request) {
        $headers = $request->get_headers();
        $provided_key = isset($headers['x_gmb_ranker_key']) ? $headers['x_gmb_ranker_key'][0] : '';
        if (empty($provided_key)) {
            $provided_key = $request->get_header('X-GMB-Ranker-Key');
        }
        $saved_key = get_option('gmb_ranker_api_key', '');

        if (!is_string($saved_key) || !is_string($provided_key)) {
            return false;
        }

        return (!empty($saved_key) && hash_equals($saved_key, $provided_key));
    }

    /**
     * Handle handshake check
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_handshake($request) {
        return new WP_REST_Response(array(
            'status'     => 'connected',
            'site_url'   => home_url(),
            'version'    => defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0',
            'timestamp'  => time(),
        ), 200);
    }
}
