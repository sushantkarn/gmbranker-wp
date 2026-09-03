<?php
/**
 * Optimize Page Action for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Action_Interface to execute on-page SEO audits,
 * calculate composite health scores, and optionally apply safe automated SEO recommendations.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Optimize_Page_Action implements GMB_Ranker_SEO_Action_Interface {

    /**
     * Unique action identifier
     *
     * @return string
     */
    public function get_id() {
        return 'optimize_page';
    }

    /**
     * Human-readable action name
     *
     * @return string
     */
    public function get_name() {
        return __('Run On-Page Audit & Apply Recommendations', 'gmb-ranker-seo-automation');
    }

    /**
     * Execute on-page audit & optimization action
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
                'message' => __('No valid target post ID provided for optimization.', 'gmb-ranker-seo-automation'),
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

        if (!class_exists('GMB_Ranker_SEO_Analysis_Service')) {
            return array(
                'success' => false,
                'message' => __('SEO analysis service is unavailable.', 'gmb-ranker-seo-automation'),
                'data'    => array(),
            );
        }

        try {
            $service = new GMB_Ranker_SEO_Analysis_Service();
            $audit   = $service->audit_post($post_id);

            if (is_wp_error($audit)) {
                return array(
                    'success' => false,
                    'message' => $audit->get_error_message(),
                    'data'    => array(),
                );
            }

            if (!is_array($audit)) {
                return array(
                    'success' => false,
                    'message' => __('SEO audit service returned malformed response data.', 'gmb-ranker-seo-automation'),
                    'data'    => array(),
                );
            }

            $score           = isset($audit['score']) ? intval($audit['score']) : 0;
            $auto_apply      = !empty($params['auto_apply']) || !empty($context['auto_apply']);
            $applied_changes = array();

            // Auto-apply non-destructive recommendations if requested
            if ($auto_apply) {
                // Ensure meta description exists
                $meta_desc = get_post_meta($post_id, '_gmb_ranker_seo_description', true);
                if (empty($meta_desc)) {
                    $generated_desc = wp_strip_all_tags(mb_substr($post->post_content, 0, 155));
                    if (!empty($generated_desc)) {
                        update_post_meta($post_id, '_gmb_ranker_seo_description', sanitize_text_field($generated_desc));
                        $applied_changes[] = __('Generated and assigned fallback Meta Description from post content.', 'gmb-ranker-seo-automation');
                    }
                }

                // Re-run audit after applying automated fixes if any changes occurred
                if (!empty($applied_changes)) {
                    $audit = $service->audit_post($post_id);
                    $score = isset($audit['score']) ? intval($audit['score']) : $score;
                }
            }

            $message = !empty($applied_changes)
                ? sprintf(__('Post %d audited and automated recommendations applied. Final Score: %d/100', 'gmb-ranker-seo-automation'), $post_id, $score)
                : sprintf(__('Post %d audited successfully. Score: %d/100', 'gmb-ranker-seo-automation'), $post_id, $score);

            return array(
                'success' => true,
                'message' => $message,
                'data'    => array(
                    'post_id'         => $post_id,
                    'post_title'      => get_the_title($post_id),
                    'score'           => $score,
                    'results'         => isset($audit['results']) && is_array($audit['results']) ? $audit['results'] : array(),
                    'metrics'         => isset($audit['metrics']) && is_array($audit['metrics']) ? $audit['metrics'] : array(),
                    'auto_apply'      => $auto_apply,
                    'applied_changes' => $applied_changes,
                ),
            );
        } catch (\Throwable $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('SEO optimization exception: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                'data'    => array(),
            );
        }
    }
}
