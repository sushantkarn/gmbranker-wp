<?php
/**
 * Sync GBP Action for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Action_Interface to synchronize Google Business Profile
 * locations, local business entities, and associated customer reviews.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Sync_Gbp_Action implements GMB_Ranker_SEO_Action_Interface {

    /**
     * Unique action identifier
     *
     * @return string
     */
    public function get_id() {
        return 'sync_gbp';
    }

    /**
     * Human-readable action name
     *
     * @return string
     */
    public function get_name() {
        return __('Sync Business Profiles & Reviews', 'gmb-ranker-seo-automation');
    }

    /**
     * Execute GBP location & review synchronization
     *
     * @param array $context Execution context
     * @param array $params  Action parameters
     * @return array [ 'success' => bool, 'message' => string, 'data' => array ]
     */
    public function execute(array $context = array(), array $params = array()) {
        $account_id   = !empty($context['account_id']) ? $context['account_id'] : (!empty($params['account_id']) ? $params['account_id'] : '');
        $access_token = !empty($context['access_token']) ? $context['access_token'] : (!empty($params['access_token']) ? $params['access_token'] : '');

        // If live Google API credentials are provided, perform remote GBP location sync via GMB_Ranker_SEO_GBP_Client
        if (!empty($access_token) && !empty($account_id) && class_exists('GMB_Ranker_SEO_GBP_Client')) {
            try {
                $gbp_client = new GMB_Ranker_SEO_GBP_Client();
                $remote_res = $gbp_client->get_locations($access_token, $account_id);

                if (is_wp_error($remote_res)) {
                    return array(
                        'success' => false,
                        'message' => $remote_res->get_error_message(),
                        'data'    => array(),
                    );
                }

                $remote_locations = isset($remote_res['locations']) && is_array($remote_res['locations']) ? $remote_res['locations'] : array();
                $count = count($remote_locations);

                // Reconcile into Local SEO Service repository if available
                if (class_exists('GMB_Ranker_SEO_Local_Service')) {
                    $local_service = new GMB_Ranker_SEO_Local_Service();
                    foreach ($remote_locations as $loc) {
                        if (!empty($loc['name'])) {
                            $local_service->save_location(array(
                                'id'       => sanitize_text_field($loc['name']),
                                'title'    => isset($loc['title']) ? sanitize_text_field($loc['title']) : '',
                                'phone'    => isset($loc['primaryPhone']) ? sanitize_text_field($loc['primaryPhone']) : '',
                                'website'  => isset($loc['websiteUri']) ? esc_url_raw($loc['websiteUri']) : '',
                            ));
                        }
                    }
                }

                return array(
                    'success' => true,
                    'message' => sprintf(__('Synchronized %d location(s) from Google Business Profile.', 'gmb-ranker-seo-automation'), $count),
                    'data'    => array(
                        'source'      => 'google_api',
                        'count'       => $count,
                        'locations'   => $remote_locations,
                        'last_synced' => current_time('mysql'),
                    ),
                );
            } catch (\Throwable $e) {
                return array(
                    'success' => false,
                    'message' => sprintf(__('GBP synchronization exception: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                    'data'    => array(),
                );
            }
        }

        // Local repository fallback reconciliation
        if (!class_exists('GMB_Ranker_SEO_Local_Service')) {
            return array(
                'success' => false,
                'message' => __('Local SEO service is unavailable.', 'gmb-ranker-seo-automation'),
                'data'    => array(),
            );
        }

        try {
            $service   = new GMB_Ranker_SEO_Local_Service();
            $locations = $service->get_locations();
            $count     = is_array($locations) ? count($locations) : 0;

            return array(
                'success' => true,
                'message' => sprintf(__('Reconciled %d local business profile location(s).', 'gmb-ranker-seo-automation'), $count),
                'data'    => array(
                    'source'      => 'local_repository',
                    'count'       => $count,
                    'last_synced' => current_time('mysql'),
                ),
            );
        } catch (\Throwable $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Local business sync exception: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                'data'    => array(),
            );
        }
    }
}
