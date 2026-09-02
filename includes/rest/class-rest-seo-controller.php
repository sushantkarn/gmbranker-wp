<?php
/**
 * REST SEO Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_SEO_Controller {

    /**
     * @var GMB_Ranker_SEO_Metadata_Service
     */
    protected $metadata_service;

    /**
     * @var GMB_Ranker_SEO_Keyword_Repository
     */
    protected $keyword_repo;

    public function __construct(
        GMB_Ranker_SEO_Metadata_Service $metadata_service = null,
        GMB_Ranker_SEO_Keyword_Repository $keyword_repo = null
    ) {
        $this->metadata_service = $metadata_service ?: new GMB_Ranker_SEO_Metadata_Service();
        $this->keyword_repo     = $keyword_repo ?: new GMB_Ranker_SEO_Keyword_Repository();
    }

    /**
     * Handle get seo data
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_get_seo_data($request) {
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
            'seo_title'      => $this->metadata_service->get_effective_title($post->ID),
            'seo_desc'       => $this->metadata_service->get_effective_description($post->ID),
            'focus_keyword'  => $this->keyword_repo->get_focus_keyword($post->ID),
            'status'         => $post->post_status,
            'url'            => get_permalink($post->ID),
        ), 200);
    }

    /**
     * Handle update seo data
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_update_seo($request) {
        $id = intval($request->get_param('id'));
        if (empty($id)) {
            return new WP_Error('missing_id', 'Missing required post ID', array('status' => 400));
        }

        $post = get_post($id);
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }

        $title = $request->get_param('seo_title');
        $desc  = $request->get_param('seo_desc');
        $kw    = $request->get_param('focus_keyword');

        if (isset($title)) {
            update_post_meta($id, '_gmb_ranker_seo_title', sanitize_text_field($title));
            update_post_meta($id, 'rank_math_title', sanitize_text_field($title));
            update_post_meta($id, '_yoast_wpseo_title', sanitize_text_field($title));
        }
        if (isset($desc)) {
            update_post_meta($id, '_gmb_ranker_seo_description', sanitize_textarea_field($desc));
            update_post_meta($id, 'rank_math_description', sanitize_textarea_field($desc));
            update_post_meta($id, '_yoast_wpseo_metadesc', sanitize_textarea_field($desc));
        }
        if (isset($kw)) {
            $this->keyword_repo->set_focus_keyword($id, $kw);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'id'      => $id,
            'message' => 'SEO metadata updated successfully.',
        ), 200);
    }
}
