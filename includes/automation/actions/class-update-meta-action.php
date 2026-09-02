<?php
/**
 * Update Meta Action for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Update_Meta_Action implements GMB_Ranker_SEO_Action_Interface {

    public function get_id() {
        return 'update_meta';
    }

    public function get_name() {
        return 'Automate Post Title & Description Metadata';
    }

    public function execute(array $context = array(), array $params = array()) {
        $post_id = isset($context['post_id']) ? intval($context['post_id']) : 0;
        if (!$post_id) {
            return array('success' => false, 'message' => 'Missing post ID for update_meta action.', 'data' => array());
        }

        if (!empty($params['seo_title'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_title', sanitize_text_field($params['seo_title']));
        }
        if (!empty($params['seo_desc'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_description', sanitize_textarea_field($params['seo_desc']));
        }
        if (!empty($params['focus_kw'])) {
            $kw_repo = new GMB_Ranker_SEO_Keyword_Repository();
            $kw_repo->set_focus_keyword($post_id, $params['focus_kw']);
        }

        return array(
            'success' => true,
            'message' => 'Post metadata updated successfully.',
            'data'    => array('post_id' => $post_id),
        );
    }
}
