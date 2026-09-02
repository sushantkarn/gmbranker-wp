<?php
/**
 * Sync GBP Action for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Sync_Gbp_Action implements GMB_Ranker_SEO_Action_Interface {

    public function get_id() {
        return 'sync_gbp';
    }

    public function get_name() {
        return 'Sync Business Profiles & Reviews';
    }

    public function execute(array $context = array(), array $params = array()) {
        $service = new GMB_Ranker_SEO_Local_Service();
        $locations = $service->get_locations();

        return array(
            'success' => true,
            'message' => 'Synced ' . count($locations) . ' local profile location(s).',
            'data'    => array('count' => count($locations)),
        );
    }
}
