<?php
/**
 * Update Meta Action for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Action_Interface to automate post SEO title,
 * meta description, and focus keyword metadata. Synchronizes with third-party
 * SEO plugins (Rank Math, Yoast) via canonical keyword and metadata repositories.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Update_Meta_Action implements GMB_Ranker_SEO_Action_Interface {

    /**
     * Unique action identifier
     *
     * @return string
     */
    public function get_id() {
        return 'update_meta';
    }

    /**
     * Human-readable action name
     *
     * @return string
     */
    public function get_name() {
        return __('Automate Post Title & Description Metadata', 'gmb-ranker-seo-automation');
    }

    /**
     * Execute metadata update action
     *
     * @param array $context Execution context
     * @param array $params  Action parameters
     * @return array [ 'success' => bool, 'message' => string, 'data' => array ]
     */
    public function execute(array $context = array(), array $params = array()) {
        // Resolve target post ID from context or params
        $raw_post_id = !empty($context['post_id']) 
            ? $context['post_id'] 
            : (!empty($params['post_id']) ? $params['post_id'] : 0);

        $post_id = intval($raw_post_id);
        if ($post_id <= 0) {
            return array(
                'success' => false,
                'message' => __('No valid target post ID provided for metadata update.', 'gmb-ranker-seo-automation'),
                'data'    => array(),
            );
        }

        $post = get_post($post_id);
        if (!$post || in_array($post->post_status, array('trash', 'auto-draft'), true)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Post ID %d does not exist or is in an invalid post status.', 'gmb-ranker-seo-automation'), $post_id),
                'data'    => array(),
            );
        }

        $updated_fields = array();

        try {
            // Update SEO Title
            if (isset($params['seo_title'])) {
                $clean_title = sanitize_text_field(wp_unslash($params['seo_title']));
                update_post_meta($post_id, '_gmb_ranker_seo_title', $clean_title);
                update_post_meta($post_id, '_yoast_wpseo_title', $clean_title);
                update_post_meta($post_id, 'rank_math_title', $clean_title);
                $updated_fields[] = 'seo_title';
            }

            // Update SEO Meta Description
            if (isset($params['seo_desc'])) {
                $clean_desc = sanitize_textarea_field(wp_unslash($params['seo_desc']));
                update_post_meta($post_id, '_gmb_ranker_seo_description', $clean_desc);
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $clean_desc);
                update_post_meta($post_id, 'rank_math_description', $clean_desc);
                $updated_fields[] = 'seo_desc';
            }

            // Update Focus Keyword via Keyword Repository
            if (isset($params['focus_kw']) && class_exists('GMB_Ranker_SEO_Keyword_Repository')) {
                $clean_kw = sanitize_text_field(wp_unslash($params['focus_kw']));
                $kw_repo  = new GMB_Ranker_SEO_Keyword_Repository();
                $kw_repo->set_focus_keyword($post_id, $clean_kw);
                $updated_fields[] = 'focus_kw';
            }

            // Update Secondary Keywords if provided
            if (isset($params['secondary_kws']) && is_array($params['secondary_kws']) && class_exists('GMB_Ranker_SEO_Keyword_Repository')) {
                $kw_repo = new GMB_Ranker_SEO_Keyword_Repository();
                $kw_repo->set_secondary_keywords($post_id, $params['secondary_kws']);
                $updated_fields[] = 'secondary_kws';
            }

            if (empty($updated_fields)) {
                return array(
                    'success' => true,
                    'message' => sprintf(__('No metadata changes specified for post %d.', 'gmb-ranker-seo-automation'), $post_id),
                    'data'    => array(
                        'post_id'        => $post_id,
                        'updated_fields' => array(),
                    ),
                );
            }

            // Flush post-level caches if available
            clean_post_cache($post_id);

            return array(
                'success' => true,
                'message' => sprintf(__('Successfully updated %d metadata field(s) for post %d.', 'gmb-ranker-seo-automation'), count($updated_fields), $post_id),
                'data'    => array(
                    'post_id'        => $post_id,
                    'post_title'     => get_the_title($post_id),
                    'updated_fields' => $updated_fields,
                ),
            );
        } catch (\Throwable $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Metadata update exception: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                'data'    => array(),
            );
        }
    }
}
