<?php
/**
 * Generate Content Action for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Generate_Content_Action implements GMB_Ranker_SEO_Action_Interface {

    public function get_id() {
        return 'generate_content';
    }

    public function get_name() {
        return 'Generate AI Optimized Draft / Section';
    }

    public function execute(array $context = array(), array $params = array()) {
        $topic = isset($context['topic']) ? $context['topic'] : (isset($params['topic']) ? $params['topic'] : 'SEO Guide');
        $post_type = isset($params['post_type']) ? $params['post_type'] : 'post';

        $title = 'Auto: ' . sanitize_text_field($topic);
        $content = '<!-- wp:paragraph --><p>Auto-generated content for topic: ' . esc_html($topic) . '</p><!-- /wp:paragraph -->';

        $post_id = wp_insert_post(array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'draft',
            'post_type'    => $post_type,
        ));

        if (is_wp_error($post_id)) {
            return array('success' => false, 'message' => $post_id->get_error_message(), 'data' => array());
        }

        return array(
            'success' => true,
            'message' => 'Draft content generated successfully.',
            'data'    => array('post_id' => $post_id),
        );
    }
}
