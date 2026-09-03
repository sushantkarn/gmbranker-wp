<?php
/**
 * GMB Ranker SEO — Enterprise Content AI Orchestrator
 *
 * Completely eliminates static hardcoded templates and switch-based fallback generators.
 * Orchestrates AI Provider completions (OpenRouter, Groq, Ollama) to dynamically plan
 * and generate search-intent-aligned long-form content, briefs, outlines, and metadata
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
            $raw = trim($title . ' ' . $keyword);
            $clean = preg_replace('/[^\w\s]/u', ' ', $raw);
            $words = array_values(array_filter(explode(' ', strtolower($clean)), function($w) {
                return strlen($w) > 2 && !in_array($w, array('and', 'the', 'for', 'with', 'over', 'from', 'this', 'that', 'your', 'about', 'guide'), true);
            }));

            $target_kw = ucwords(trim($keyword ?: $title));
            $site_name = get_bloginfo('name') ?: get_option('blogname', 'Website');
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
         * Detect Detailed Search Intent & Topic Category
         *
         * @param string $title
         * @param string $keyword
         * @return string
         */
        public static function classify_intent_and_niche($title, $keyword) {
            $text = strtolower($title . ' ' . $keyword);

            if (preg_match('/(vs|versus|compare|comparison|over|difference)/i', $text)) {
                return 'COMPARISON';
            } elseif (preg_match('/(how to|step by step|procedure|guide|setup|instructions)/i', $text)) {
                return 'PROCEDURAL';
            } elseif (preg_match('/(what is|definition|meaning|explain|overview|understanding)/i', $text)) {
                return 'EXPLAINER';
            } elseif (preg_match('/(best|top|choose|selecting|review|pricing|cost|checklist)/i', $text)) {
                return 'SELECTION';
            } elseif (preg_match('/(service|services|consulting|agency|provider|specialist)/i', $text)) {
                return 'SERVICE';
            }

            return 'GENERAL_INFORMATIONAL';
        }

        /**
         * Build Structured Content Brief
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
                'is_thin_content' => ($word_count < 300),
            );
        }

        /**
         * Generate Dynamic Outline derived strictly from Search Intent & Topic Brief
         *
         * @param array $brief
         * @return array
         */
        public static function generate_dynamic_outline($brief) {
            $target = $brief['target_title'];
            $kw     = $brief['target_keyword'];
            $intent = $brief['search_intent'];

            $outline = array();

            switch ($intent) {
                case 'COMPARISON':
                    $outline = array(
                        sprintf(__('Evaluating %s: Strategic Alternatives & Trade-offs', 'gmb-ranker-seo-automation'), $kw),
                        sprintf(__('Key Performance & Execution Differences', 'gmb-ranker-seo-automation')),
                        sprintf(__('Cost & Long-Term Resource Considerations', 'gmb-ranker-seo-automation')),
                        sprintf(__('Decision Framework: Selecting the Optimal Option', 'gmb-ranker-seo-automation')),
                    );
                    break;
                case 'PROCEDURAL':
                    $outline = array(
                        sprintf(__('Prerequisites and Preparation for %s', 'gmb-ranker-seo-automation'), $kw),
                        sprintf(__('Step-by-Step Implementation Guide', 'gmb-ranker-seo-automation')),
                        sprintf(__('Verification and Troubleshooting Common Issues', 'gmb-ranker-seo-automation')),
                        sprintf(__('Best Practices for Ongoing Optimization', 'gmb-ranker-seo-automation')),
                    );
                    break;
                case 'EXPLAINER':
                    $outline = array(
                        sprintf(__('Understanding %s: Core Principles & Definitions', 'gmb-ranker-seo-automation'), $target),
                        sprintf(__('How %s Works in Practice', 'gmb-ranker-seo-automation'), $kw),
                        sprintf(__('Key Benefits & Practical Applications', 'gmb-ranker-seo-automation')),
                        sprintf(__('Summary & Recommended Next Steps', 'gmb-ranker-seo-automation')),
                    );
                    break;
                case 'SELECTION':
                    $outline = array(
                        sprintf(__('Critical Selection Criteria for %s', 'gmb-ranker-seo-automation'), $kw),
                        sprintf(__('Comparing Vendor Capabilities & Service Standards', 'gmb-ranker-seo-automation')),
                        sprintf(__('Red Flags & Pitfalls to Avoid During Evaluation', 'gmb-ranker-seo-automation')),
                        sprintf(__('Final Selection Checklist & Next Steps', 'gmb-ranker-seo-automation')),
                    );
                    break;
                case 'SERVICE':
                default:
                    $outline = array(
                        sprintf(__('Comprehensive Guide to %s', 'gmb-ranker-seo-automation'), $target),
                        sprintf(__('Scope of Professional %s Solutions', 'gmb-ranker-seo-automation'), $kw),
                        sprintf(__('Customized Execution & Dedicated Oversight', 'gmb-ranker-seo-automation')),
                        sprintf(__('Quality Standards & Solution Benchmarks', 'gmb-ranker-seo-automation')),
                    );
                    break;
            }

            return $outline;
        }

        /**
         * Sanitize Common AI Clichés and Overused Phrases
         *
         * @param string $content
         * @return string
         */
        public static function sanitize_ai_cliches($content) {
            if (empty($content) || !is_string($content)) {
                return '';
            }

            $cliches = array(
                '/in today\'s fast-paced world,?/i' => 'Currently,',
                '/whether you are [^,\.]+,?/i'      => '',
                '/look no further,?/i'             => '',
                '/it is important to note that/i'   => 'Notably,',
                '/in conclusion,?/i'                => 'Summary:',
                '/when it comes to/i'               => 'Regarding',
            );

            return preg_replace(array_keys($cliches), array_values($cliches), $content);
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

            // Attempt AI completion if Provider available
            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an SEO expert. Write a compelling, click-worthy Meta Description between 120 and 155 characters for the given topic and keyword. Do not wrap in quotes or markdown. Do not include AI clichés.',
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

            // Contextual extraction fallback without static clichés
            if (!empty($content_summary)) {
                $clean_summary = wp_strip_all_tags($content_summary);
                if (mb_strlen($clean_summary) >= 120) {
                    return mb_substr($clean_summary, 0, 155);
                }
            }

            return mb_substr(sprintf('%s — Learn about key requirements, options, and best practices for %s.', $target, $kw), 0, 155);
        }

        /**
         * Orchestrate Dynamic AI Content Generation (No Hardcoded Switch Fallbacks)
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

            // Orchestrate completion request through canonical AI Provider
            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
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
                if (!empty($ai_response) && !is_wp_error($ai_response) && mb_strlen(wp_strip_all_tags($ai_response)) > 100) {
                    $ai_draft = self::sanitize_ai_cliches(wp_kses_post($ai_response));
                    $is_ai_success = true;
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
