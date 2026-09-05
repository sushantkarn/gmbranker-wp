<?php
/**
 * REST Links Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Links_Controller {

    /**
     * @var GMB_Ranker_SEO_Content_Service
     */
    protected $content_service;

    public function __construct(GMB_Ranker_SEO_Content_Service $content_service = null) {
        $this->content_service = $content_service ?: new GMB_Ranker_SEO_Content_Service();
    }

    /**
     * Handle internal link injection into post or Elementor content
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_inject_internal_link($request) {
        $post_id = intval($request->get_param('post_id'));
        $anchor  = $request->get_param('anchor');
        $url     = $request->get_param('url');

        if (!$post_id || empty($anchor) || empty($url)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'post_id, anchor, and url are required parameters.',
            ), 400);
        }

        if (!get_post($post_id)) {
            return new WP_REST_Response(array('success' => false, 'message' => 'The requested post does not exist.'), 404);
        }

        if (is_user_logged_in() && !current_user_can('edit_post', $post_id)) {
            return new WP_REST_Response(array('success' => false, 'message' => 'You are not allowed to edit this post.'), 403);
        }

        $success = $this->content_service->inject_link_in_post($post_id, $anchor, $url);

        return new WP_REST_Response(array(
            'success' => $success,
            'message' => $success ? 'Link injected successfully.' : 'Anchor text not found in post content.',
            'post_id' => $post_id,
        ), $success ? 200 : 422);
    }
}
