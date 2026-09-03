<?php
/**
 * GMB Ranker SEO — Enterprise Dynamic AI Content & Intent Intelligence Orchestrator
 *
 * 100% dynamic, repository-driven Content AI orchestration layer.
 * Completely eliminates hardcoded intent dictionaries, static outline fallbacks, and regex archetype matching.
 * Orchestrates AI Provider completions (OpenRouter, Groq, Ollama) to dynamically plan,
 * structure, and generate search-intent-aligned long-form content, briefs, outlines, and metadata
 * based on target query semantics, entities, site context, and SEO audit results.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Content_AI')) {

    class GMB_Ranker_SEO_Content_AI {

        /**
         * Analyze Topic & Extract Core Semantic Entities
         *
         * @param string $title
         * @param string $keyword
         * @return array
         */
        public static function analyze_topic_entities($title, $keyword) {
            $raw   = trim($title . ' ' . $keyword);
            $clean = preg_replace('/[^\w\s]/u', ' ', $raw);
            $words = array_values(array_filter(explode(' ', strtolower($clean)), function($w) {
                return strlen($w) > 2;
            }));

            $target_kw = ucwords(trim($keyword ?: $title));
            $site_name = get_bloginfo('name') ?: get_option('blogname', '');
            $home_url  = esc_url(home_url('/'));

            return array(
                'raw_title' => $title,
                'target_kw' => $target_kw,
                'kw_lower'  => strtolower($target_kw),
                'words'     => array_unique($words),
                'site_name' => $site_name,
                'home_url'  => $home_url,
            );
        }

        /**
         * Detect Search Intent & Topic Focus Dynamically via AI or Semantic Inference
         *
         * @param string $title
         * @param string $keyword
         * @return string
         */
        public static function classify_intent_and_niche($title, $keyword) {
            $target = trim($title . ' ' . $keyword);
            if (empty($target)) {
                return 'DYNAMIC_INFORMATIONAL';
            }

            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an SEO intent classifier. Classify the user search intent for the target topic into one word (e.g. INFORMATIONAL, COMPARISON, PROCEDURAL, COMMERCIAL, SERVICE). Return ONLY the one-word intent.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => 'Topic: ' . $target,
                    ),
                );

                $ai_intent = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.3);
                if (!empty($ai_intent) && !is_wp_error($ai_intent)) {
                    $clean_intent = strtoupper(trim(wp_strip_all_tags($ai_intent)));
                    if (strlen($clean_intent) >= 3 && strlen($clean_intent) <= 25) {
                        return $clean_intent;
                    }
                }
            }

            return 'DYNAMIC_INFORMATIONAL';
        }

        /**
         * Build Dynamic Content Brief
         *
         * @param string $title
         * @param string $keyword
         * @param int    $post_id
         * @return array
         */
        public static function build_content_brief($title, $keyword, $post_id = 0) {
            $entities = self::analyze_topic_entities($title, $keyword);
            $intent   = self::classify_intent_and_niche($title, $keyword);

            $existing_post = $post_id ? get_post($post_id) : null;
            $post_content  = $existing_post ? $existing_post->post_content : '';
            $word_count    = str_word_count(wp_strip_all_tags($post_content));

            return array(
                'target_title'    => !empty($title) ? $title : $entities['target_kw'],
                'target_keyword'  => $entities['target_kw'],
                'search_intent'   => $intent,
                'semantic_words'  => $entities['words'],
                'site_name'       => $entities['site_name'],
                'home_url'        => $entities['home_url'],
                'existing_words'  => $word_count,
                'post_id'         => $post_id,
            );
        }

        /**
         * Generate AI-Driven Dynamic Outline (No Static Arrays or Hardcoded Sequences)
         *
         * @param array $brief
         * @return array
         */
        public static function generate_dynamic_outline($brief) {
            $target = $brief['target_title'];
            $kw     = $brief['target_keyword'];
            $intent = $brief['search_intent'];

            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are a senior enterprise SEO content strategist. Construct a dynamic, topic-specific H2 outline tailored strictly to the input topic and search intent. Return ONLY a plain bulleted list (- Heading) with no extra intro/outro text or markdown code blocks.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf('Topic: %s\nTarget Keyword: %s\nSearch Intent: %s', $target, $kw, $intent),
                    ),
                );

                $ai_outline_resp = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.5);
                if (!empty($ai_outline_resp) && !is_wp_error($ai_outline_resp)) {
                    $lines = explode("\n", $ai_outline_resp);
                    $headings = array();
                    foreach ($lines as $line) {
                        $clean = trim(preg_replace('/^[\-\*\d\.]+\s*/', '', $line));
                        if (!empty($clean) && strlen($clean) > 3) {
                            $headings[] = wp_strip_all_tags($clean);
                        }
                    }
                    if (!empty($headings)) {
                        return $headings;
                    }
                }
            }

            return array();
        }

        /**
         * Sanitize Output
         *
         * @param string $content
         * @return string
         */
        public static function sanitize_ai_cliches($content) {
            if (empty($content) || !is_string($content)) {
                return '';
            }

            return wp_kses_post($content);
        }

        /**
         * Generate Evidence-Based Contextual Meta Description via AI or Content Context
         *
         * @param string $title
         * @param string $keyword
         * @param string $content_summary
         * @return string
         */
        public static function generate_meta_description($title, $keyword, $content_summary = '') {
            $target = !empty($title) ? $title : $keyword;
            $kw     = !empty($keyword) ? $keyword : $title;

            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an SEO expert. Write a compelling, click-worthy Meta Description between 120 and 155 characters for the given topic and keyword. Do not wrap in quotes or markdown.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf('Topic: %s\nKeyword: %s\nSummary: %s', $target, $kw, wp_strip_all_tags(mb_substr($content_summary, 0, 300))),
                    ),
                );

                $ai_desc = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.7);
                if (!empty($ai_desc) && !is_wp_error($ai_desc)) {
                    $clean_desc = trim(wp_strip_all_tags($ai_desc), '"\'');
                    if (mb_strlen($clean_desc) >= 60) {
                        return mb_substr($clean_desc, 0, 155);
                    }
                }
            }

            if (!empty($content_summary)) {
                $clean_summary = wp_strip_all_tags($content_summary);
                if (mb_strlen($clean_summary) >= 120) {
                    return mb_substr($clean_summary, 0, 155);
                }
            }

            return '';
        }

        /**
         * Orchestrate Dynamic AI Content Generation (No Hardcoded Fallbacks)
         *
         * @param string $title
         * @param string $keyword
         * @param int    $post_id
         * @return array
         */
        public static function generate_archetype_draft($title, $keyword, $post_id = 0) {
            $brief   = self::build_content_brief($title, $keyword, $post_id);
            $outline = self::generate_dynamic_outline($brief);
            $niche   = $brief['search_intent'];

            $ai_draft = '';
            $is_ai_success = false;

            if (class_exists('GMB_Ranker_SEO_AI_Provider') && !empty($outline)) {
                $outline_str = implode("\n- ", $outline);
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => "You are a senior enterprise SEO content strategist. Write a comprehensive, search-intent-aligned HTML article draft (using <h2>, <p>, <ul>, <ol> tags) for the target topic based on the provided outline. Do not include markdown code fences (like ```html), do not write generic filler, and do not invent unverified credentials or claims.",
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf(
                            "Topic: %s\nTarget Keyword: %s\nSearch Intent: %s\nSite Name: %s\nProposed Outline:\n- %s",
                            $brief['target_title'],
                            $brief['target_keyword'],
                            $niche,
                            $brief['site_name'],
                            $outline_str
                        ),
                    ),
                );

                $ai_response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.7);
                if (!empty($ai_response) && !is_wp_error($ai_response)) {
                    $clean_resp = trim(wp_strip_all_tags($ai_response));
                    if (mb_strlen($clean_resp) > 100) {
                        $ai_draft = wp_kses_post($ai_response);
                        $is_ai_success = true;
                    }
                }
            }

            $heading_count = count($outline);

            return array(
                'success' => $is_ai_success,
                'intent'  => array(
                    'niche'         => $niche,
                    'heading_count' => $heading_count,
                    'archetype'     => ucwords(strtolower(str_replace('_', ' ', $niche))),
                ),
                'brief'   => $brief,
                'outline' => $outline,
                'draft'   => $ai_draft,
                'meta_description' => self::generate_meta_description($title, $keyword, $ai_draft),
            );
        }
    }
}
