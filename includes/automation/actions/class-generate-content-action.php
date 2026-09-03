<?php
/**
 * Generate Content Action for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Action_Interface to generate AI-optimized draft articles
 * or content sections. Integrates with canonical AI Provider, validates post types,
 * sanitizes block content, and provisions initial SEO metadata.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Generate_Content_Action implements GMB_Ranker_SEO_Action_Interface {

    /**
     * Unique action identifier
     *
     * @return string
     */
    public function get_id() {
        return 'generate_content';
    }

    /**
     * Human-readable action name
     *
     * @return string
     */
    public function get_name() {
        return __('Generate AI Optimized Draft / Section', 'gmb-ranker-seo-automation');
    }

    /**
     * Execute content generation action
     *
     * @param array $context
     * @param array $params
     * @return array [ 'success' => bool, 'message' => string, 'data' => array ]
     */
    public function execute(array $context = array(), array $params = array()) {
        // Resolve input topic / keyword / prompt from context or params
        $topic = !empty($context['topic']) 
            ? $context['topic'] 
            : (!empty($params['topic']) 
                ? $params['topic'] 
                : (!empty($context['keyword']) 
                    ? $context['keyword'] 
                    : (!empty($params['keyword']) ? $params['keyword'] : '')));

        if (empty($topic)) {
            return array(
                'success' => false,
                'message' => __('Content topic or target keyword is required for generation.', 'gmb-ranker-seo-automation'),
                'data'    => array(),
            );
        }

        $clean_topic = sanitize_text_field(wp_unslash($topic));

        // Validate Post Type
        $post_type = !empty($params['post_type']) ? sanitize_key($params['post_type']) : 'post';
        if (!post_type_exists($post_type)) {
            $post_type = 'post';
        }

        // Validate Post Status
        $allowed_statuses = array('draft', 'pending', 'publish', 'private');
        $post_status      = !empty($params['post_status']) ? strtolower(trim($params['post_status'])) : 'draft';
        if (!in_array($post_status, $allowed_statuses, true)) {
            $post_status = 'draft';
        }

        $tone     = !empty($params['tone']) ? sanitize_text_field($params['tone']) : 'professional';
        $language = !empty($params['language']) ? sanitize_text_field($params['language']) : get_locale();

        // Check if AI generation is enabled and provider is available
        $ai_used       = false;
        $title         = sprintf(__('Comprehensive Guide: %s', 'gmb-ranker-seo-automation'), $clean_topic);
        $content       = '';
        $meta_desc     = '';

        if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
            $system_prompt = "You are an expert SEO content strategist. Write a comprehensive, well-structured article in Gutenberg block format (using <!-- wp:heading --> and <!-- wp:paragraph --> comments). Focus on providing valuable, original insights for the given topic. Language: {$language}. Tone: {$tone}.";
            $user_prompt   = "Topic: {$clean_topic}\n\nPlease generate a full draft article including:\n1. A clear Title\n2. Key H2 section headings\n3. Detailed body paragraphs\n4. A 150-character SEO meta description.";

            $messages = array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user', 'content' => $user_prompt),
            );

            $ai_response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages);

            if (!is_wp_error($ai_response) && is_string($ai_response) && !empty($ai_response)) {
                $generated_text = trim($ai_response);

                // Parse out title if present on first line
                if (preg_match('/^(?:Title|#)\s*:\s*(.+)$/m', $generated_text, $matches)) {
                    $title = sanitize_text_field($matches[1]);
                    $generated_text = preg_replace('/^(?:Title|#)\s*:\s*.+$/m', '', $generated_text, 1);
                }

                $content = wp_kses_post($generated_text);
                $ai_used = true;
            }
        }

        // Fallback content structure if AI was disabled or unavailable
        if (empty($content)) {
            $content  = sprintf("<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->\n\n", sprintf(__('An in-depth analysis and complete guide covering %s.', 'gmb-ranker-seo-automation'), esc_html($clean_topic)));
            $content .= sprintf("<!-- wp:heading {\"level\":2} -->\n<h2>%s</h2>\n<!-- /wp:heading -->\n\n", sprintf(__('Understanding %s', 'gmb-ranker-seo-automation'), esc_html($clean_topic)));
            $content .= sprintf("<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->", __('Key strategies and insights for optimizing your search visibility and audience engagement.', 'gmb-ranker-seo-automation'));
        }

        if (empty($meta_desc)) {
            $meta_desc = wp_strip_all_tags(mb_substr($clean_topic . ' - ' . __('Learn key strategies and best practices in this comprehensive guide.', 'gmb-ranker-seo-automation'), 0, 160));
        }

        // Create the Post
        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => $post_type,
            'post_author'  => get_current_user_id() ?: 1,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message(),
                'data'    => array(),
            );
        }

        // Provision SEO Meta Description
        update_post_meta($post_id, '_gmb_seo_description', sanitize_text_field($meta_desc));
        update_post_meta($post_id, '_gmb_seo_ai_generated', $ai_used ? '1' : '0');

        return array(
            'success' => true,
            'message' => $ai_used 
                ? __('AI-optimized draft content generated and saved successfully.', 'gmb-ranker-seo-automation')
                : __('Draft content created successfully.', 'gmb-ranker-seo-automation'),
            'data'    => array(
                'post_id'   => $post_id,
                'post_type' => $post_type,
                'title'     => $title,
                'status'    => $post_status,
                'ai_used'   => $ai_used,
            ),
        );
    }
}
