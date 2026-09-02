<?php
/**
 * REST Content Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Content_Controller {

    /**
     * Handle page content retrieval
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_get_page_content($request) {
        $id = intval($request->get_param('id'));
        if (empty($id)) {
            return new WP_Error('missing_id', 'Missing required post ID', array('status' => 400));
        }

        $post = get_post($id);
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        return new WP_REST_Response(array(
            'id'             => $post->ID,
            'title'          => $post->post_title,
            'content'        => $post->post_content,
            'elementor_data' => get_post_meta($post->ID, '_elementor_data', true),
        ), 200);
    }

    /**
     * Handle AI content draft generation
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_content_ai($request) {
        $topic = $request->get_param('topic') ?: 'SEO Strategy';
        $post_type = $request->get_param('post_type') ?: 'post';

        $action = new GMB_Ranker_SEO_Generate_Content_Action();
        $result = $action->execute(array('topic' => $topic), array('post_type' => $post_type));

        return new WP_REST_Response($result, $result['success'] ? 200 : 400);
    }
}
