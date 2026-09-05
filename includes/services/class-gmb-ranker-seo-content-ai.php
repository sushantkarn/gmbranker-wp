<?php
/**
 * GMB Ranker SEO — Enterprise Dynamic AI Content & Intent Intelligence Orchestrator
 *
 * 100% dynamic, repository-driven Content AI orchestration layer.
 * Completely eliminates static hardcoded templates, switch-based archetype dictionaries,
 * and hardcoded outline fallbacks. Orchestrates AI Provider completions (OpenRouter, Groq, Ollama)
 * to dynamically plan, structure, and generate search-intent-aligned long-form content, briefs,
 * outlines, and metadata based on target query semantics, entities, site context, and SEO audit results.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Content_AI')) {

    class GMB_Ranker_SEO_Content_AI {

        /**
         * Analyze Topic & Extract Core Semantic Entities Dynamically
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
         * Detect Search Intent & User Information Needs Dynamically via AI or Query Structure
         *
         * @param string $title
         * @param string $keyword
         * @param array  $research_data
         * @return string
         */
        public static function classify_intent_and_niche($title, $keyword, $research_data = array()) {
            $target = trim($title . ' ' . $keyword);
            if (empty($target)) {
                return 'Informational';
            }

            // 1. Check research data if intent was already detected by SERP / Audit Engine
            if (!empty($research_data['intent'])) {
                return ucfirst(strtolower(trim($research_data['intent'])));
            }

            // 2. Query AI provider for dynamic classification
            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an enterprise SEO intent analyst. Classify the primary search intent for the target query into one of: Informational, Commercial, Transactional, Navigational, or Local. Return ONLY the single classification word.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => 'Target Query: ' . $target,
                    ),
                );

                $ai_intent = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.2);
                if (!empty($ai_intent) && !is_wp_error($ai_intent)) {
                    $clean_intent = ucfirst(strtolower(trim(wp_strip_all_tags($ai_intent))));
                    if (in_array($clean_intent, array('Informational', 'Commercial', 'Transactional', 'Navigational', 'Local'), true)) {
                        return $clean_intent;
                    }
                }
            }

            // 3. Heuristic classification based on query modifiers
            $target_lower = strtolower($target);
            if (preg_match('/\b(buy|price|cost|discount|coupon|deal|order|hire)\b/', $target_lower)) {
                return 'Transactional';
            }
            if (preg_match('/\b(best|vs|review|comparison|top|rated)\b/', $target_lower)) {
                return 'Commercial';
            }
            if (preg_match('/\b(near me|location|city|address|map|hours)\b/', $target_lower)) {
                return 'Local';
            }

            return 'Informational';
        }

        /**
         * Dynamically Resolve Tone of Voice
         *
         * @param string $requested_tone
         * @param string $existing_content
         * @return string
         */
        public static function resolve_tone_of_voice($requested_tone = 'auto', $existing_content = '') {
            if (!empty($requested_tone) && $requested_tone !== 'auto') {
                return ucfirst($requested_tone);
            }

            // Check site option if saved tone preference exists
            $site_tone = get_option('gmb_ranker_site_tone_preference', '');
            if (!empty($site_tone)) {
                return ucfirst($site_tone);
            }

            // Neutral fallback when there is no site-specific signal
            return 'Neutral';
        }

        /**
         * Construct Enterprise Context Model from Environment, Input, and Research
         *
         * @param array $params
         * @return array
         */
        public static function build_context_model($params = array()) {
            $title          = isset($params['title']) ? trim($params['title']) : '';
            $keyword        = isset($params['keyword']) ? trim($params['keyword']) : (isset($params['focus_keyword']) ? trim($params['focus_keyword']) : '');
            $post_id        = isset($params['post_id']) ? intval($params['post_id']) : 0;
            $mode           = isset($params['mode']) ? strtolower(trim($params['mode'])) : 'create';
            $user_instr     = isset($params['user_instructions']) ? sanitize_textarea_field($params['user_instructions']) : '';
            $requested_tone = isset($params['tone']) ? trim($params['tone']) : 'auto';
            $requested_intent=isset($params['intent']) ? trim($params['intent']) : 'auto';
            $research_data  = isset($params['research_data']) && is_array($params['research_data']) ? $params['research_data'] : array();

            $entities     = self::analyze_topic_entities($title, $keyword);
            $target_title = !empty($title) ? $title : $entities['target_kw'];
            $target_kw    = $entities['target_kw'];

            $existing_post    = $post_id ? get_post($post_id) : null;
            $existing_content = $existing_post ? $existing_post->post_content : '';
            $existing_title   = $existing_post ? $existing_post->post_title : '';
            $post_type        = $existing_post ? $existing_post->post_type : 'post';
            $word_count       = str_word_count(wp_strip_all_tags($existing_content));

            // Intent resolution
            if (!empty($requested_intent) && $requested_intent !== 'auto') {
                $intent = ucfirst($requested_intent);
                $intent_source = 'user_selected';
            } else {
                $intent = self::classify_intent_and_niche($target_title, $target_kw, $research_data);
                $intent_source = 'dynamic_detection';
            }

            // Tone resolution
            $tone = self::resolve_tone_of_voice($requested_tone, $existing_content);

            return array(
                'mode'              => $mode,
                'target_title'      => $target_title,
                'target_keyword'    => $target_kw,
                'search_intent'     => $intent,
                'intent_source'     => $intent_source,
                'tone'              => $tone,
                'semantic_words'    => $entities['words'],
                'site_name'         => $entities['site_name'],
                'home_url'          => $entities['home_url'],
                'post_id'           => $post_id,
                'post_type'         => $post_type,
                'existing_title'    => $existing_title,
                'existing_content'  => $existing_content,
                'existing_words'    => $word_count,
                'user_instructions' => $user_instr,
                'research_context'  => $research_data,
            );
        }

        /**
         * Build Dynamic Content Brief with Trusted Post & Site Context
         *
         * @param string $title
         * @param string $keyword
         * @param int    $post_id
         * @param string $user_instructions
         * @param string $tone
         * @param string $intent_input
         * @return array
         */
        public static function build_content_brief($title, $keyword, $post_id = 0, $user_instructions = '', $tone = 'auto', $intent_input = 'auto') {
            $context = self::build_context_model(array(
                'title'             => $title,
                'keyword'           => $keyword,
                'post_id'           => $post_id,
                'user_instructions' => $user_instructions,
                'tone'              => $tone,
                'intent'            => $intent_input,
            ));

            return array(
                'target_title'      => $context['target_title'],
                'target_keyword'    => $context['target_keyword'],
                'search_intent'     => $context['search_intent'],
                'intent_source'     => $context['intent_source'],
                'tone'              => $context['tone'],
                'tone_source'       => 'dynamic_resolution',
                'semantic_words'    => $context['semantic_words'],
                'site_name'         => $context['site_name'],
                'home_url'          => $context['home_url'],
                'post_type'         => $context['post_type'],
                'existing_words'    => $context['existing_words'],
                'post_id'           => $context['post_id'],
                'user_instructions' => $context['user_instructions'],
            );
        }

        /**
         * Generate AI-Driven Dynamic Outline (No Static Fallback Arrays)
         *
         * @param array $brief
         * @return array
         */
        public static function generate_dynamic_outline($brief) {
            $target = !empty($brief['target_title']) ? $brief['target_title'] : (!empty($brief['target_keyword']) ? $brief['target_keyword'] : '');
            $kw     = !empty($brief['target_keyword']) ? $brief['target_keyword'] : $target;
            $intent = !empty($brief['search_intent']) ? $brief['search_intent'] : 'Informational';
            $tone   = !empty($brief['tone']) ? $brief['tone'] : 'Neutral';
            $instr  = !empty($brief['user_instructions']) ? "\nUser Guidelines: " . $brief['user_instructions'] : '';

            if (empty($target)) {
                return array();
            }

            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an enterprise SEO content strategist. Construct a dynamic, topic-specific H2 section outline tailored strictly to the target query, search intent, and tone of voice. Return ONLY a plain bulleted list (- Heading) with no intro/outro text or markdown fences.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf("Topic: %s\nTarget Keyword: %s\nSearch Intent: %s\nTone of Voice: %s%s", $target, $kw, $intent, $tone, $instr),
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
         * Sanitize Cliches and Repetitive Fillers from AI Content
         *
         * @param string $content
         * @return string
         */
        public static function sanitize_ai_cliches($content) {
            if (empty($content) || !is_string($content)) {
                return '';
            }
            $cliches = array(
                'In conclusion,', 'At the end of the day,', 'It is important to note that,',
                'Furthermore,', 'In today\'s digital world,', 'As a matter of fact,',
                'In summary,', 'To sum it up,'
            );
            return str_ireplace($cliches, '', $content);
        }

        /**
         * Generate Evidence-Based Contextual Meta Description via AI
         *
         * @param string $title
         * @param string $keyword
         * @param string $content_summary
         * @return string
         */
        public static function generate_meta_description($title, $keyword, $content_summary = '') {
            $entities  = self::analyze_topic_entities($title, $keyword);
            $target_kw = $entities['target_kw'];
            $site_name = $entities['site_name'];

            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $summary_context = !empty($content_summary) ? "\nContent Summary: " . mb_substr(wp_strip_all_tags($content_summary), 0, 300) : '';
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an expert SEO copywriter. Write a compelling, click-worthy meta description (140 to 155 characters) front-loaded with the focus keyword. Return ONLY the meta description text with no quotes.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf("Keyword: %s\nTitle: %s\nSite: %s%s", $target_kw, $title, $site_name, $summary_context),
                    ),
                );

                $ai_desc = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.4);
                if (!empty($ai_desc) && !is_wp_error($ai_desc)) {
                    $clean_desc = trim(wp_strip_all_tags($ai_desc));
                    if (mb_strlen($clean_desc) >= 80 && mb_strlen($clean_desc) <= 170) {
                        return $clean_desc;
                    }
                }
            }

            return '';
        }

        /**
         * Unified Enterprise Content AI Engine Entry Point
         *
         * Accepts structured parameters, builds context model, executes mode-specific generation,
         * validates results, and returns structured enterprise payload.
         *
         * @param array $params
         * @return array
         */
        public static function generate_content($params = array()) {
            $context = self::build_context_model($params);
            $mode    = $context['mode'];

            // Validation: Require minimum input for CREATE NEW mode
            if ($mode === 'create' && empty($context['target_title']) && empty($context['target_keyword']) && empty($context['user_instructions'])) {
                return array(
                    'success'       => false,
                    'status'        => 'insufficient_input',
                    'mode'          => $mode,
                    'output_type'   => isset($params['output_type']) ? $params['output_type'] : 'article',
                    'content'       => '',
                    'draft'         => '',
                    'brief'         => array(),
                    'outline'       => array(),
                    'warnings'      => array(),
                    'errors'        => array(__('Insufficient input provided for content creation. Please provide a target query, topic, or content instructions.', 'gmb-ranker-seo-automation')),
                );
            }

            // Validation: Require existing content for OPTIMIZE mode
            if ($mode === 'optimize' && empty($context['existing_content']) && empty($context['target_title'])) {
                return array(
                    'success'       => false,
                    'status'        => 'no_existing_content',
                    'mode'          => $mode,
                    'output_type'   => isset($params['output_type']) ? $params['output_type'] : 'recommendation',
                    'content'       => '',
                    'draft'         => '',
                    'brief'         => array(),
                    'outline'       => array(),
                    'warnings'      => array(),
                    'errors'        => array(__('No existing page content or post target found to optimize.', 'gmb-ranker-seo-automation')),
                );
            }

            // Delegate to core draft orchestration
            return self::generate_archetype_draft(
                $context['target_title'],
                $context['target_keyword'],
                $context['post_id'],
                $context['user_instructions'],
                $context['tone'],
                $context['search_intent'],
                $mode,
                $context['research_context'],
                isset($params['output_type']) ? $params['output_type'] : 'article'
            );
        }

        /**
         * Orchestrate Dynamic AI Content & Brief Generation (No Hardcoded Template Fallbacks)
         *
         * @param string $title
         * @param string $keyword
         * @param int    $post_id
         * @param string $user_instructions
         * @param string $tone
         * @param string $intent_input
         * @param string $mode
         * @param array  $research_data
         * @param string $output_type
         * @return array
         */
        public static function generate_archetype_draft($title, $keyword, $post_id = 0, $user_instructions = '', $tone = 'auto', $intent_input = 'auto', $mode = 'create', $research_data = array(), $output_type = 'article') {
            $context = self::build_context_model(array(
                'title'             => $title,
                'keyword'           => $keyword,
                'post_id'           => $post_id,
                'user_instructions' => $user_instructions,
                'tone'              => $tone,
                'intent'            => $intent_input,
                'mode'              => $mode,
                'research_data'     => $research_data,
            ));

            $brief = self::build_content_brief($title, $keyword, $post_id, $user_instructions, $tone, $intent_input);
            $outline = array();
            // Reuse headings already returned by the research pass instead of making
            // a second AI request for an outline before generating the draft.
            if (!empty($research_data['ai_research']['heading_gaps']) && is_array($research_data['ai_research']['heading_gaps'])) {
                foreach ($research_data['ai_research']['heading_gaps'] as $heading_gap) {
                    if (!empty($heading_gap['heading_text'])) {
                        $outline[] = sanitize_text_field($heading_gap['heading_text']);
                    }
                }
            }
            if (empty($outline)) {
                $outline = self::generate_dynamic_outline($brief);
            }

            $ai_draft = '';
            $is_ai_success = false;
            $errors = array();

            // Execute AI Provider completion if provider class is available
            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {

                if ($mode === 'optimize') {
                    $system_prompt = "You are a senior enterprise SEO copywriter. Optimize the existing page content for the target keyword, search intent, and tone. Improve readability, add relevant heading sections, address content gaps, and retain valuable existing details. Output ONLY clean HTML copy (using <h2>, <h3>, <p>, <ul>, <ol>). Do not include markdown code fences or commentary. Never invent phone numbers, email addresses, street addresses, prices, testimonials, credentials, guarantees, or service claims. Use only facts present in the supplied context.";
                    $user_content_prompt = sprintf(
                        "Target Query: %s\nFocus Keyword: %s\nSearch Intent: %s\nTone of Voice: %s\nExisting Title: %s\n\n<UNTRUSTED_EXISTING_CONTENT>\n%s\n</UNTRUSTED_EXISTING_CONTENT>\n",
                        $context['target_title'],
                        $context['target_keyword'],
                        $context['search_intent'],
                        $context['tone'],
                        $context['existing_title'],
                        mb_substr(wp_strip_all_tags($context['existing_content']), 0, 4000)
                    );
                } else {
                    $outline_str = !empty($outline) ? implode("\n- ", $outline) : 'Generate a dynamic section outline tailored specifically to the target query and search intent.';
                    $system_prompt = "You are a senior enterprise SEO content strategist. Write comprehensive, search-intent-aligned long-form HTML article copy (using <h2>, <h3>, <p>, <ul>, <ol> tags) for the target topic. Match the requested tone strictly. Unless the user explicitly requests a shorter format, produce at least 800 meaningful words, at least 5 relevant H2 sections, at least 2 useful H3 subsections, and use the target keyword naturally in the opening and throughout the article without stuffing. Output ONLY clean HTML body content. Do not include markdown code fences (like ```html), do not write generic filler, and do not invent phone numbers, email addresses, street addresses, prices, testimonials, credentials, guarantees, or service claims. Use only facts present in the supplied context.";
                    $user_content_prompt = sprintf(
                        "Topic: %s\nTarget Keyword: %s\nSearch Intent: %s\nTone of Voice: %s\nSite Name: %s\nOutline Guidance:\n- %s",
                        $context['target_title'],
                        $context['target_keyword'],
                        $context['search_intent'],
                        $context['tone'],
                        $context['site_name'],
                        $outline_str
                    );
                }

                if (!empty($user_instructions)) {
                    $user_content_prompt .= sprintf(
                        "\n\n<UNTRUSTED_USER_INSTRUCTIONS>\n%s\n</UNTRUSTED_USER_INSTRUCTIONS>",
                        $user_instructions
                    );
                }

                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => $system_prompt,
                    ),
                    array(
                        'role'    => 'user',
                        'content' => $user_content_prompt,
                    ),
                );

                $ai_response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.7);

                if (!empty($ai_response) && !is_wp_error($ai_response)) {
                    $clean_response = trim(preg_replace('/^```(?:html|markdown|json)?/i', '', trim($ai_response)));
                    $clean_response = preg_replace('/```$/', '', $clean_response);
                    $clean_response = trim($clean_response);

                    // Decode JSON response if AI returned structured JSON wrapper
                    if (strpos($clean_response, '{') === 0 && strpos($clean_response, '}') !== false) {
                        $decoded_json = json_decode($clean_response, true);
                        if (is_array($decoded_json)) {
                            if (!empty($decoded_json['draft'])) {
                                $clean_response = $decoded_json['draft'];
                            } elseif (!empty($decoded_json['content'])) {
                                $clean_response = $decoded_json['content'];
                            } elseif (!empty($decoded_json['article'])) {
                                $clean_response = $decoded_json['article'];
                            }
                        }
                    }

                    $clean_text = trim(wp_strip_all_tags($clean_response));
                    if (!empty($clean_text)) {
                        $has_unverified_contact = preg_match('/(?:\+\d[\d\s().-]{7,}\d|\b\d{3}[\s.-]\d{3}[\s.-]\d{4}\b|\b\d{10,}\b)/', $clean_text) || preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $clean_text);
                        if ($has_unverified_contact) {
                            $errors[] = __('AI draft rejected because it contained unverified contact information. No invented contact details were saved.', 'gmb-ranker-seo-automation');
                            $clean_text = '';
                        }
                    }
                    if (!empty($clean_text)) {
                        $has_html = (strpos($clean_response, '<p>') !== false || strpos($clean_response, '<h2') !== false || strpos($clean_response, '<h3') !== false);
                        if ($has_html) {
                            $ai_draft = wp_kses_post(self::sanitize_ai_cliches($clean_response));
                        } else {
                            // Format plain text / markdown lines to standard HTML tags
                            $lines    = explode("\n", $clean_response);
                            $html_out = '';
                            foreach ($lines as $line) {
                                $l = trim($line);
                                if (empty($l)) continue;
                                if (strpos($l, '### ') === 0) {
                                    $html_out .= '<h3>' . esc_html(substr($l, 4)) . '</h3>' . "\n";
                                } elseif (strpos($l, '## ') === 0) {
                                    $html_out .= '<h2>' . esc_html(substr($l, 3)) . '</h2>' . "\n";
                                } elseif (strpos($l, '# ') === 0) {
                                    $html_out .= '<h1>' . esc_html(substr($l, 2)) . '</h1>' . "\n";
                                } elseif (strpos($l, '- ') === 0 || strpos($l, '* ') === 0) {
                                    $html_out .= '<li>' . esc_html(substr($l, 2)) . '</li>' . "\n";
                                } else {
                                    $html_out .= '<p>' . esc_html($l) . '</p>' . "\n";
                                }
                            }
                            $ai_draft = wp_kses_post(self::sanitize_ai_cliches($html_out));
                        }

                        if (!empty($ai_draft)) {
                            $is_ai_success = true;
                        }
                    }
                } elseif (is_wp_error($ai_response)) {
                    $provider_error = $ai_response->get_error_message();
                    $errors[] = (stripos($provider_error, 'AI generation failed:') === 0)
                        ? $provider_error
                        : sprintf(__('AI generation failed: %s', 'gmb-ranker-seo-automation'), $provider_error);
                } else {
                    $errors[] = __('AI generation failed: provider returned an empty response.', 'gmb-ranker-seo-automation');
                }
            } else {
                $errors[] = __('GMB_Ranker_SEO_AI_Provider service is unconfigured or unavailable.', 'gmb-ranker-seo-automation');
            }

            // CRITICAL: TRUTHFUL FAILURES — NO SILENT HARDCODED TEMPLATE FALLBACK!
            // The Master Strategist already performs a research synthesis that requests
            // metadata. Avoid a duplicate provider call in that workflow.
            $meta_description = ($is_ai_success && empty($research_data['ai_research'])) ? self::generate_meta_description($title, $keyword, $ai_draft) : '';

            return array(
                'success'          => $is_ai_success,
                'status'           => $is_ai_success ? 'completed' : 'ai_generation_failed',
                'mode'             => $mode,
                'output_type'      => $output_type,
                'content'          => $ai_draft,
                'draft'            => $ai_draft,
                'intent'           => array(
                    'niche'         => $context['search_intent'],
                    'heading_count' => count($outline),
                    'archetype'     => $context['search_intent'],
                ),
                'brief'            => $brief,
                'outline'          => $outline,
                'meta_description' => $meta_description,
                'warnings'         => array(),
                'errors'           => $errors,
            );
        }
    }
}
