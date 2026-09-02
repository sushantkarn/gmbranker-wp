<?php
/**
 * Optimize Page Action for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Optimize_Page_Action implements GMB_Ranker_SEO_Action_Interface {

    public function get_id() {
        return 'optimize_page';
    }

    public function get_name() {
        return 'Run On-Page Audit & Apply Recommendations';
    }

    public function execute(array $context = array(), array $params = array()) {
        $post_id = isset($context['post_id']) ? intval($context['post_id']) : 0;
        if (!$post_id) {
            return array('success' => false, 'message' => 'No target post ID provided.', 'data' => array());
        }

        $service = new GMB_Ranker_SEO_Analysis_Service();
        $audit = $service->audit_post($post_id);

        return array(
            'success' => true,
            'message' => 'Post ' . $post_id . ' analyzed. Score: ' . $audit['score'] . '/100',
            'data'    => $audit,
        );
    }
}
