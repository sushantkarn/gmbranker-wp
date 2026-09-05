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
        $focus_keyword = !empty($context['keyword'])
            ? sanitize_text_field(wp_unslash($context['keyword']))
            : (!empty($params['keyword']) ? sanitize_text_field(wp_unslash($params['keyword'])) : $clean_topic);

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

        $tone     = !empty($params['tone']) ? sanitize_text_field($params['tone']) : '';
        $language = !empty($params['language']) ? sanitize_text_field($params['language']) : get_locale();

        // Check if AI generation is enabled and provider is available
        $ai_used       = false;
        $title         = $clean_topic;
        $content       = '';
        $meta_desc     = '';

        if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
            $system_prompt = "You are an expert SEO content strategist. Write a comprehensive, well-structured article in Gutenberg block format (using <!-- wp:heading --> and <!-- wp:paragraph --> comments). Focus on providing valuable, original insights for the given topic. Unless a shorter format is explicitly requested, produce at least 800 meaningful words, at least 5 relevant H2 headings, at least 2 useful H3 headings, and use the focus keyword naturally in the opening and throughout without stuffing. Never invent contact details, prices, testimonials, credentials, guarantees, or unsupported business claims. Language: {$language}. Tone: {$tone}.";
            $user_prompt   = "Topic: {$clean_topic}\nFocus keyword: {$focus_keyword}\n\nPlease generate a full draft article including:\n1. A clear Title\n2. Key H2 and H3 section headings\n3. Detailed body paragraphs\n4. A 150-character SEO meta description.";

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

        if (empty($content)) {
            return array(
                'success' => false,
                'message' => __('AI generation did not return usable content, so no draft was created.', 'gmb-ranker-seo-automation'),
                'data'    => array(
                    'ai_used' => false,
                    'title'   => $title,
                ),
            );
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

        if (!empty($meta_desc)) {
            update_post_meta($post_id, '_gmb_seo_description', sanitize_text_field($meta_desc));
        }
        update_post_meta($post_id, '_gmb_seo_ai_generated', $ai_used ? '1' : '0');
        update_post_meta($post_id, '_gmb_ranker_focus_keyword', $focus_keyword);

        // Populate the canonical score immediately so the editor and Posts list agree.
        if (class_exists('GMB_Ranker_SEO_Analysis_Service')) {
            (new GMB_Ranker_SEO_Analysis_Service())->audit_post($post_id);
        }

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
