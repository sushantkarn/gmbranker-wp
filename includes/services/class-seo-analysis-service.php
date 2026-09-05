<?php
/**
 * SEO Analysis Service for GMB Ranker SEO Automation
 *
 * Production-grade, evidence-based SEO analysis and orchestration engine.
 * Evaluates search intent, topical depth, keyword/entity coverage, metadata,
 * technical indexability, GSC performance data, schema, media, and internal links,
 * producing structured findings, prioritized recommendations, and contextual health scores.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Analysis_Service')) {

    class GMB_Ranker_SEO_Analysis_Service {

        /**
         * Run evidence-based SEO analysis for a post
         *
         * @param int $post_id
         * @return array Structured audit results contract
         */
        public function audit_post($post_id) {
            $clean_id = intval($post_id);
            if ($clean_id <= 0) {
                return $this->get_empty_audit_response();
            }

            $post = get_post($clean_id);
            if (empty($post) || in_array($post->post_status, array('trash', 'auto-draft'), true)) {
                return $this->get_empty_audit_response();
            }

            // 1. Collect Context & Extract Rendered Content
            $context = $this->collect_page_context($post);

            // 2. Execute Modular Evidence Analyzers
            $findings = array();
            $this->analyze_technical_and_indexability($context, $findings);
            $this->analyze_metadata($context, $findings);
            $this->analyze_search_intent_and_keywords($context, $findings);
            $this->analyze_content_quality_and_structure($context, $findings);
            $this->analyze_internal_and_external_links($context, $findings);
            $this->analyze_media_and_accessibility($context, $findings);
            $this->analyze_schema_and_structured_data($context, $findings);
            $this->analyze_search_console_performance($context, $findings);

            // 3. Prioritize Findings & Compute Contextual Score
            $this->prioritize_findings($findings);
            $scoring = $this->calculate_score_and_results($findings, $context);

            $final_score = $scoring['score'];
            $results     = $scoring['results'];

            // 4. Derive Content / SEO Opportunity Payload
            $opportunity = $this->derive_content_opportunity($context, $findings);

            // Persist diagnostic score for caching & UI sorting
            update_post_meta($clean_id, '_gmb_ranker_seo_score', $final_score);
            $analysis_hash = $this->build_analysis_hash($context);
            update_post_meta($clean_id, '_gmb_ranker_seo_analysis_hash', $analysis_hash);
            update_post_meta($clean_id, '_gmb_ranker_seo_analysis_at', current_time('mysql', true));

            return array(
                'score'       => $final_score,
                'results'     => $results, // Backward compatible formatted results
                'metrics'     => array(
                    'word_count' => $context['word_count'],
                    'title_len'  => $context['title_len'],
                    'desc_len'   => $context['desc_len'],
                    'focus_kw'   => $context['focus_kw'],
                    'h2_count'   => $context['h2_count'],
                    'h3_count'   => $context['h3_count'],
                    'images'     => $context['images_count'],
                    'links'      => $context['links_count'],
                    'intent'     => $context['intent'],
                ),
                'findings'    => $findings, // Enriched structured findings
                'opportunity' => $opportunity, // Content AI opportunity brief payload
                'context'     => array(
                    'post_id'     => $clean_id,
                    'post_type'   => $context['post_type'],
                    'post_status' => $context['post_status'],
                    'permalink'   => $context['permalink'],
                    'is_elementor'=> $context['is_elementor'],
                    'has_blocks'  => $context['has_blocks'],
                    'analysis_hash' => $analysis_hash,
                    'analyzed_at'   => get_post_meta($clean_id, '_gmb_ranker_seo_analysis_at', true),
                ),
            );
        }

        /**
         * Build a fingerprint for the inputs used by the persisted audit.
         *
         * @param array $context
         * @return string
         */
        protected function build_analysis_hash(array $context) {
            return hash('sha256', wp_json_encode(array(
                'title'       => $context['title'],
                'raw_content' => $context['raw_content'],
                'meta_title'  => $context['meta_title'],
                'meta_desc'   => $context['meta_desc'],
                'focus_kw'    => $context['focus_kw'],
                'canonical'   => $context['canonical'],
                'robots'      => $context['robots'],
            )));
        }

        /**
         * Collect Normalized Page Context & Evidence
         *
         * @param WP_Post $post
         * @return array
         */
        protected function collect_page_context($post) {
            $post_id     = $post->ID;
            $title       = $post->post_title;
            $raw_content = $post->post_content;

            // Elementor Data Extraction
            $is_elementor = get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder';
            $elementor_data_raw = get_post_meta($post_id, '_elementor_data', true);
            $extracted_elementor_text = '';

            if ($is_elementor && !empty($elementor_data_raw)) {
                $elements = json_decode($elementor_data_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($elements)) {
                    $extracted_elementor_text = $this->extract_text_from_elementor_data($elements);
                }
            }

            $combined_content = !empty($extracted_elementor_text)
                ? $raw_content . "\n\n" . $extracted_elementor_text
                : $raw_content;

            $has_blocks = function_exists('has_blocks') && has_blocks($raw_content);

            // SEO Metadata (GMB Ranker -> Yoast -> RankMath fallback)
            $meta_title = get_post_meta($post_id, '_gmb_ranker_seo_title', true);
            if (empty($meta_title)) {
                $meta_title = get_post_meta($post_id, '_yoast_wpseo_title', true) 
                    ?: (get_post_meta($post_id, 'rank_math_title', true) ?: $title);
            }

            $meta_desc = get_post_meta($post_id, '_gmb_ranker_seo_description', true);
            if (empty($meta_desc)) {
                $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true) 
                    ?: (get_post_meta($post_id, 'rank_math_description', true) ?: '');
            }

            $focus_kw = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true);
            if (empty($focus_kw)) {
                $focus_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true) 
                    ?: (get_post_meta($post_id, 'rank_math_focus_keyword', true) ?: '');
            }

            // The editor supports multiple keyword pills, but the analysis
            // engine evaluates one primary focus phrase.
            $focus_kw_parts = array_values(array_filter(array_map('trim', explode(',', (string) $focus_kw))));
            $focus_kw = !empty($focus_kw_parts) ? $focus_kw_parts[0] : '';

            $canonical = get_post_meta($post_id, '_gmb_ranker_seo_canonical', true) 
                ?: (get_post_meta($post_id, '_yoast_wpseo_canonical', true) 
                ?: (get_post_meta($post_id, 'rank_math_canonical_url', true) ?: ''));

            $robots = get_post_meta($post_id, '_gmb_ranker_seo_robots', true) 
                ?: (get_post_meta($post_id, 'rank_math_robots', true) ?: '');

            $clean_text = wp_strip_all_tags($combined_content);
            $word_count = str_word_count($clean_text);
            $title_len  = mb_strlen($meta_title);
            $desc_len   = mb_strlen($meta_desc);

            // Extract headings
            preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $combined_content, $h1_matches);
            preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $combined_content, $h2_matches);
            preg_match_all('/<h3[^>]*>(.*?)<\/h3>/i', $combined_content, $h3_matches);

            // Extract images
            preg_match_all('/<img\s+[^>]*>/i', $combined_content, $img_matches);
            $images_count = count($img_matches[0]);
            $images_with_alt = 0;
            if ($images_count > 0) {
                foreach ($img_matches[0] as $img_tag) {
                    if (preg_match('/alt=[\'"][^\'"]+[\'"]/i', $img_tag)) {
                        $images_with_alt++;
                    }
                }
            }

            // Extract links
            preg_match_all('/<a\s+[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)<\/a>/i', $combined_content, $link_matches);
            $links_count    = count($link_matches[0]);
            $site_host      = wp_parse_url(home_url(), PHP_URL_HOST);
            $internal_links = 0;
            $external_links = 0;

            if ($links_count > 0 && isset($link_matches[1])) {
                foreach ($link_matches[1] as $href) {
                    $link_host = wp_parse_url($href, PHP_URL_HOST);
                    if (empty($link_host) || strtolower($link_host) === strtolower($site_host)) {
                        $internal_links++;
                    } else {
                        $external_links++;
                    }
                }
            }

            // Search Intent Classification
            $intent = 'informational';
            if (class_exists('GMB_Ranker_SEO_Content_AI')) {
                $intent = GMB_Ranker_SEO_Content_AI::classify_intent_and_niche($title, $focus_kw) ?: 'informational';
            } elseif ($post->post_type === 'product' || preg_match('/buy|price|shop|store|order/i', $title)) {
                $intent = 'transactional';
            } elseif (preg_match('/best|top|review|vs|compare/i', $title)) {
                $intent = 'commercial';
            }

            // Search Console Evidence (if saved in post meta)
            $gsc_data = array(
                'impressions' => get_post_meta($post_id, '_gmb_ranker_gsc_impressions', true),
                'clicks'      => get_post_meta($post_id, '_gmb_ranker_gsc_clicks', true),
                'ctr'         => get_post_meta($post_id, '_gmb_ranker_gsc_ctr', true),
                'position'    => get_post_meta($post_id, '_gmb_ranker_gsc_position', true),
            );

            return array(
                'post_id'         => $post_id,
                'post_type'       => $post->post_type,
                'post_status'     => $post->post_status,
                'permalink'       => get_permalink($post_id),
                'title'           => $title,
                'raw_content'     => $raw_content,
                'combined_content'=> $combined_content,
                'clean_text'      => $clean_text,
                'word_count'      => $word_count,
                'meta_title'      => $meta_title,
                'title_len'       => $title_len,
                'meta_desc'       => $meta_desc,
                'desc_len'        => $desc_len,
                'focus_kw'        => $focus_kw,
                'canonical'       => $canonical,
                'robots'          => $robots,
                'h1_count'        => count($h1_matches[0]),
                'h2_count'        => count($h2_matches[0]),
                'h3_count'        => count($h3_matches[0]),
                'images_count'    => $images_count,
                'images_with_alt' => $images_with_alt,
                'links_count'     => $links_count,
                'internal_links'  => $internal_links,
                'external_links'  => $external_links,
                'is_elementor'    => $is_elementor,
                'has_blocks'      => $has_blocks,
                'intent'          => $intent,
                'gsc_data'        => $gsc_data,
            );
        }

        /**
         * 1. Technical & Indexability Analyzer
         */
        protected function analyze_technical_and_indexability(array $context, array &$findings) {
            // Check post status
            if ($context['post_status'] !== 'publish') {
                $findings[] = $this->create_finding(
                    'tech_post_not_published',
                    'technical',
                    'warning',
                    50,
                    1.0,
                    __('Post is Not Published', 'gmb-ranker-seo-automation'),
                    sprintf(__('Post is currently in "%s" status and is not publicly indexable by search engines.', 'gmb-ranker-seo-automation'), esc_html($context['post_status'])),
                    __('Publish the post when ready to index.', 'gmb-ranker-seo-automation'),
                    'technical',
                    false
                );
            }

            // Check robots meta
            if (!empty($context['robots']) && strpos(strtolower((string)$context['robots']), 'noindex') !== false) {
                $findings[] = $this->create_finding(
                    'tech_robots_noindex',
                    'technical',
                    'critical',
                    95,
                    1.0,
                    __('Page Configured as Noindex', 'gmb-ranker-seo-automation'),
                    __('This page contains a noindex meta tag or directive, blocking search engines from indexing it.', 'gmb-ranker-seo-automation'),
                    __('Remove noindex directive if this page is intended for search engine traffic.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            }

            // Canonical URL Check
            if (!empty($context['canonical'])) {
                if ($this->urls_are_equivalent($context['canonical'], $context['permalink'])) {
                    $findings[] = $this->create_finding(
                        'tech_canonical_self',
                        'technical',
                        'info',
                        10,
                        1.0,
                        __('Self-Referential Canonical Configured', 'gmb-ranker-seo-automation'),
                        __('Page correctly specifies a self-referential canonical URL.', 'gmb-ranker-seo-automation'),
                        '',
                        'metadata',
                        false
                    );
                } else {
                    $findings[] = $this->create_finding(
                        'tech_canonical_external',
                        'technical',
                        'medium',
                        60,
                        0.9,
                        __('Canonical URL Points Elsewhere', 'gmb-ranker-seo-automation'),
                        sprintf(__('Canonical URL points to %s instead of this page.', 'gmb-ranker-seo-automation'), esc_url($context['canonical'])),
                        __('Verify whether indexing credit should be transferred to the target canonical URL.', 'gmb-ranker-seo-automation'),
                        'metadata',
                        false
                    );
                }
            }
        }

        /**
         * 2. Metadata Analyzer
         */
        protected function analyze_metadata(array $context, array &$findings) {
            // SEO Title
            if ($context['title_len'] === 0) {
                $findings[] = $this->create_finding(
                    'meta_title_missing',
                    'metadata',
                    'critical',
                    90,
                    1.0,
                    __('Missing SEO Title', 'gmb-ranker-seo-automation'),
                    __('No custom SEO Title is specified for this page.', 'gmb-ranker-seo-automation'),
                    __('Add a compelling, keyword-relevant SEO Title.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            } elseif ($context['title_len'] > 70) {
                $findings[] = $this->create_finding(
                    'meta_title_long',
                    'metadata',
                    'low',
                    25,
                    0.8,
                    __('SEO Title May Truncate in SERP', 'gmb-ranker-seo-automation'),
                    sprintf(__('SEO Title is %d characters. Titles longer than 60–70 characters may be truncated in search results.', 'gmb-ranker-seo-automation'), $context['title_len']),
                    __('Consider refining the title for maximum SERP snippet impact.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            } else {
                $findings[] = $this->create_finding(
                    'meta_title_ok',
                    'metadata',
                    'info',
                    5,
                    1.0,
                    __('SEO Title Present', 'gmb-ranker-seo-automation'),
                    sprintf(__('SEO Title is specified (%d characters).', 'gmb-ranker-seo-automation'), $context['title_len']),
                    '',
                    'metadata',
                    false
                );
            }

            // Meta Description
            if ($context['desc_len'] === 0) {
                $findings[] = $this->create_finding(
                    'meta_desc_missing',
                    'metadata',
                    'high',
                    80,
                    1.0,
                    __('Missing Meta Description', 'gmb-ranker-seo-automation'),
                    __('No Meta Description is specified for this page.', 'gmb-ranker-seo-automation'),
                    __('Generate or write a clear, click-worthy Meta Description.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            } elseif ($context['desc_len'] > 170) {
                $findings[] = $this->create_finding(
                    'meta_desc_long',
                    'metadata',
                    'low',
                    20,
                    0.8,
                    __('Meta Description May Truncate', 'gmb-ranker-seo-automation'),
                    sprintf(__('Meta Description is %d characters. Snippets typically show ~155 characters on desktop.', 'gmb-ranker-seo-automation'), $context['desc_len']),
                    __('Trim description to focus on core call to action.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            } else {
                $findings[] = $this->create_finding(
                    'meta_desc_ok',
                    'metadata',
                    'info',
                    5,
                    1.0,
                    __('Meta Description Present', 'gmb-ranker-seo-automation'),
                    sprintf(__('Meta Description is specified (%d characters).', 'gmb-ranker-seo-automation'), $context['desc_len']),
                    '',
                    'metadata',
                    false
                );
            }
        }

        /**
         * 3. Search Intent & Keyword Coverage Analyzer
         */
        protected function analyze_search_intent_and_keywords(array $context, array &$findings) {
            $kw = mb_strtolower(trim($context['focus_kw']));

            if (empty($kw)) {
                $findings[] = $this->create_finding(
                    'kw_none_configured',
                    'keywords',
                    'info',
                    15,
                    0.7,
                    __('No Focus Keyword Assigned', 'gmb-ranker-seo-automation'),
                    __('No focus keyword is explicitly assigned to this page. Topic alignment analyzed via content headings.', 'gmb-ranker-seo-automation'),
                    __('Assign a primary target keyword or topic for exact query tracking.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    false
                );
                return;
            }

            // Keyword Cannibalization & Uniqueness Check
            $post_id = isset($context['post_id']) ? intval($context['post_id']) : 0;
            $cannibalization = self::check_keyword_cannibalization($kw, $post_id);

            if ($cannibalization['is_cannibalized']) {
                $conflict_labels = array();
                foreach ($cannibalization['conflicts'] as $conf) {
                    $conflict_labels[] = sprintf('%s (%s)', $conf['title'], ucfirst($conf['post_type']));
                }
                $findings[] = $this->create_finding(
                    'kw_cannibalization_conflict',
                    'keywords',
                    'high',
                    75,
                    0.8,
                    __('Keyword Cannibalization Conflict Detected', 'gmb-ranker-seo-automation'),
                    sprintf(
                        __('Focus keyword "%1$s" is already targeted by %2$d published item(s): %3$s. Multiple URLs targeting the exact same focus keyword cause keyword cannibalization.', 'gmb-ranker-seo-automation'),
                        esc_html($context['focus_kw']),
                        $cannibalization['conflict_count'],
                        implode(', ', $conflict_labels)
                    ),
                    __('Assign a unique focus keyword or consolidate conflicting content to avoid ranking competition.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    false
                );
            } else {
                $findings[] = $this->create_finding(
                    'kw_uniqueness_ok',
                    'keywords',
                    'info',
                    5,
                    1.0,
                    __('Unique Focus Keyword', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword "%s" is unique and not targeted by any other published post, page, or service.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    '',
                    'metadata',
                    false
                );
            }

            // Keyword in Title
            if (mb_strpos(mb_strtolower($context['meta_title']), $kw) !== false) {
                $findings[] = $this->create_finding(
                    'kw_in_title',
                    'keywords',
                    'info',
                    5,
                    1.0,
                    __('Target Keyword in Title', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword "%s" is present in the SEO Title.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    '',
                    'metadata',
                    false
                );
            } else {
                $findings[] = $this->create_finding(
                    'kw_missing_title',
                    'keywords',
                    'medium',
                    65,
                    0.9,
                    __('Target Keyword Missing from Title', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword "%s" is missing from the SEO Title.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    __('Include the target keyword naturally near the beginning of the title.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            }

            // Keyword in Description
            if (mb_strpos(mb_strtolower($context['meta_desc']), $kw) !== false) {
                $findings[] = $this->create_finding(
                    'kw_in_desc',
                    'keywords',
                    'info',
                    5,
                    1.0,
                    __('Target Keyword in Description', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword "%s" is present in the Meta Description.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    '',
                    'metadata',
                    false
                );
            } else {
                $findings[] = $this->create_finding(
                    'kw_missing_desc',
                    'keywords',
                    'low',
                    40,
                    0.8,
                    __('Target Keyword Missing from Meta Description', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword "%s" is missing from the Meta Description.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    __('Incorporate the target keyword in the description to improve search snippet bolding.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            }

            // Keyword Frequency & Density Check
            $occurrences = mb_substr_count(mb_strtolower($context['clean_text']), $kw);
            $word_count  = max(1, $context['word_count']);
            $density     = ($occurrences / $word_count) * 100;

            if ($occurrences === 0) {
                $findings[] = $this->create_finding(
                    'kw_missing_body',
                    'keywords',
                    'medium',
                    60,
                    0.9,
                    __('Target Keyword Missing from Body Content', 'gmb-ranker-seo-automation'),
                    sprintf(__('Target keyword "%s" does not appear in body text.', 'gmb-ranker-seo-automation'), esc_html($context['focus_kw'])),
                    __('Include the target keyword and related semantic terms in paragraph copy.', 'gmb-ranker-seo-automation'),
                    'content_ai',
                    false
                );
            } elseif ($density > 4.0) {
                $findings[] = $this->create_finding(
                    'kw_stuffing_warning',
                    'keywords',
                    'medium',
                    55,
                    0.85,
                    __('Potential Keyword Over-Optimization', 'gmb-ranker-seo-automation'),
                    sprintf(__('Focus keyword appears %d times (%.1f%% density), which may trigger over-optimization filters.', 'gmb-ranker-seo-automation'), $occurrences, $density),
                    __('Vary phrasing using natural synonyms and LSI terms.', 'gmb-ranker-seo-automation'),
                    'content_ai',
                    false
                );
            } else {
                $findings[] = $this->create_finding(
                    'kw_body_ok',
                    'keywords',
                    'info',
                    5,
                    1.0,
                    __('Target Keyword Naturally Represented', 'gmb-ranker-seo-automation'),
                    sprintf(__('Target keyword appears %d times in content (%.1f%% density).', 'gmb-ranker-seo-automation'), $occurrences, $density),
                    '',
                    'content_ai',
                    false
                );
            }
        }

        /**
         * 4. Content Quality & Structure Analyzer
         */
        protected function analyze_content_quality_and_structure(array $context, array &$findings) {
            $word_count = $context['word_count'];
            $post_type  = $context['post_type'];
            $intent     = $context['intent'];

            // Contextual Word Count Thresholds
            $min_recommended_words = ($post_type === 'product' || $intent === 'transactional') ? 150 : 350;

            if ($word_count < $min_recommended_words) {
                $findings[] = $this->create_finding(
                    'content_thin',
                    'content',
                    'high',
                    75,
                    0.9,
                    __('Thin Content Warning', 'gmb-ranker-seo-automation'),
                    sprintf(__('Page contains %d words. Pages with limited depth often struggle to rank for competitive queries.', 'gmb-ranker-seo-automation'), $word_count),
                    __('Expand key sections with comprehensive information and user guidance.', 'gmb-ranker-seo-automation'),
                    'content_ai',
                    false
                );
            } else {
                $findings[] = $this->create_finding(
                    'content_depth_ok',
                    'content',
                    'info',
                    5,
                    1.0,
                    __('Content Depth Satisfied', 'gmb-ranker-seo-automation'),
                    sprintf(__('Page contains %d words, satisfying topical requirements for %s content.', 'gmb-ranker-seo-automation'), $word_count, esc_html($post_type)),
                    '',
                    'content_ai',
                    false
                );
            }

            // Heading Structure Hierarchy
            if ($context['h2_count'] === 0 && $context['h3_count'] === 0 && $word_count > 200) {
                $findings[] = $this->create_finding(
                    'structure_no_headings',
                    'structure',
                    'medium',
                    50,
                    0.85,
                    __('Lack of Subheadings', 'gmb-ranker-seo-automation'),
                    __('Content contains no H2 or H3 subheadings, reducing readability and topical structure.', 'gmb-ranker-seo-automation'),
                    __('Break up text using H2 and H3 section headings.', 'gmb-ranker-seo-automation'),
                    'content_ai',
                    false
                );
            } elseif ($context['h2_count'] > 0) {
                $findings[] = $this->create_finding(
                    'structure_headings_ok',
                    'structure',
                    'info',
                    5,
                    1.0,
                    __('Good Heading Hierarchy', 'gmb-ranker-seo-automation'),
                    sprintf(__('Content is structured with %d H2 and %d H3 headings.', 'gmb-ranker-seo-automation'), $context['h2_count'], $context['h3_count']),
                    '',
                    'content_ai',
                    false
                );
            }
        }

        /**
         * 5. Link Graph Analyzer
         */
        protected function analyze_internal_and_external_links(array $context, array &$findings) {
            if ($context['links_count'] === 0) {
                $findings[] = $this->create_finding(
                    'links_none_in_content',
                    'links',
                    'medium',
                    45,
                    0.8,
                    __('No Contextual Links in Body Content', 'gmb-ranker-seo-automation'),
                    __('Page contains no internal or external links within body text.', 'gmb-ranker-seo-automation'),
                    __('Add relevant internal links to related site guides or products.', 'gmb-ranker-seo-automation'),
                    'links',
                    true
                );
            } else {
                $findings[] = $this->create_finding(
                    'links_present',
                    'links',
                    'info',
                    5,
                    1.0,
                    __('Contextual Links Present', 'gmb-ranker-seo-automation'),
                    sprintf(__('Page contains %d contextual links (%d internal, %d external).', 'gmb-ranker-seo-automation'), $context['links_count'], $context['internal_links'], $context['external_links']),
                    '',
                    'links',
                    false
                );
            }
        }

        /**
         * 6. Media Accessibility Analyzer
         */
        protected function analyze_media_and_accessibility(array $context, array &$findings) {
            if ($context['images_count'] === 0) {
                $findings[] = $this->create_finding(
                    'media_no_images',
                    'images',
                    'info',
                    10,
                    0.6,
                    __('No Images in Content', 'gmb-ranker-seo-automation'),
                    __('Page contains no embedded images.', 'gmb-ranker-seo-automation'),
                    __('Consider adding relevant illustrations or diagrams to enhance user engagement.', 'gmb-ranker-seo-automation'),
                    'images',
                    false
                );
            } else {
                $missing_alt = $context['images_count'] - $context['images_with_alt'];
                if ($missing_alt > 0) {
                    $findings[] = $this->create_finding(
                        'media_missing_alt',
                        'images',
                        'medium',
                        55,
                        0.9,
                        __('Images Missing Alt Text', 'gmb-ranker-seo-automation'),
                        sprintf(__('%d of %d images are missing alt text descriptions.', 'gmb-ranker-seo-automation'), $missing_alt, $context['images_count']),
                        __('Add descriptive ALT attributes to all content images.', 'gmb-ranker-seo-automation'),
                        'images',
                        true
                    );
                } else {
                    $findings[] = $this->create_finding(
                        'media_alt_ok',
                        'images',
                        'info',
                        5,
                        1.0,
                        __('Image Alt Text Complete', 'gmb-ranker-seo-automation'),
                        sprintf(__('All %d images contain descriptive alt text.', 'gmb-ranker-seo-automation'), $context['images_count']),
                        '',
                        'images',
                        false
                    );
                }
            }
        }

        /**
         * 7. Schema & Structured Data Analyzer
         */
        protected function analyze_schema_and_structured_data(array $context, array &$findings) {
            $schema_json = get_post_meta($context['post_id'], '_gmb_ranker_json_ld', true);
            if (empty($schema_json)) {
                $findings[] = $this->create_finding(
                    'schema_missing',
                    'schema',
                    'low',
                    35,
                    0.8,
                    __('No Custom Schema JSON-LD Found', 'gmb-ranker-seo-automation'),
                    __('No custom JSON-LD schema is provisioned for this post.', 'gmb-ranker-seo-automation'),
                    __('Provision structured schema data (Article, Product, or FAQ) to enhance rich snippets.', 'gmb-ranker-seo-automation'),
                    'schema',
                    true
                );
            } else {
                $decoded = json_decode($schema_json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $findings[] = $this->create_finding(
                        'schema_valid',
                        'schema',
                        'info',
                        5,
                        1.0,
                        __('Structured Data JSON-LD Provisioned', 'gmb-ranker-seo-automation'),
                        __('Valid JSON-LD schema data is present.', 'gmb-ranker-seo-automation'),
                        '',
                        'schema',
                        false
                    );
                } else {
                    $findings[] = $this->create_finding(
                        'schema_invalid_json',
                        'schema',
                        'high',
                        70,
                        0.95,
                        __('Malformed Schema JSON-LD', 'gmb-ranker-seo-automation'),
                        __('The custom schema JSON-LD contains invalid JSON syntax.', 'gmb-ranker-seo-automation'),
                        __('Re-generate schema using the Schema Manager to fix syntax errors.', 'gmb-ranker-seo-automation'),
                        'schema',
                        true
                    );
                }
            }
        }

        /**
         * 8. Search Console Performance Evidence Analyzer
         */
        protected function analyze_search_console_performance(array $context, array &$findings) {
            $gsc = $context['gsc_data'];
            if (empty($gsc['impressions']) || intval($gsc['impressions']) <= 0) {
                return;
            }

            $impressions = intval($gsc['impressions']);
            $clicks      = intval($gsc['clicks'] ?? 0);
            $ctr         = floatval($gsc['ctr'] ?? 0);
            $position    = floatval($gsc['position'] ?? 0);

            if ($impressions > 300 && $ctr < 2.5) {
                $findings[] = $this->create_finding(
                    'gsc_ctr_opportunity',
                    'performance',
                    'high',
                    85,
                    0.9,
                    __('High Impression / Low CTR Opportunity', 'gmb-ranker-seo-automation'),
                    sprintf(__('Page generated %s impressions but has a %.1f%% CTR (avg position %.1f). Title/meta optimization can unlock search traffic.', 'gmb-ranker-seo-automation'), number_format($impressions), $ctr, $position),
                    __('Refine title and meta description to improve click-through rate.', 'gmb-ranker-seo-automation'),
                    'metadata',
                    true
                );
            }
        }

        /**
         * Create a standardized structured finding item
         *
         * @return array
         */
        protected function create_finding($id, $category, $severity, $priority, $confidence, $title, $description, $recommendation, $owner = 'seo_analysis', $can_auto_fix = false) {
            $type_map = array(
                'critical' => 'error',
                'high'     => 'error',
                'medium'   => 'warning',
                'low'      => 'warning',
                'info'     => 'info',
            );

            $type = isset($type_map[$severity]) ? $type_map[$severity] : 'info';
            if ($severity === 'info' && strpos($id, '_ok') !== false) {
                $type = 'success';
            }

            return array(
                'id'             => $id,
                'category'       => $category,
                'severity'       => $severity,
                'priority'       => $priority,
                'confidence'     => $confidence,
                'title'          => $title,
                'description'    => $description,
                'recommendation' => $recommendation,
                'owner'          => $owner,
                'can_auto_fix'   => $can_auto_fix,
                'type'           => $type, // Backward compatibility for legacy UI
                'msg'            => $description, // Backward compatibility for legacy UI
            );
        }

        /**
         * Prioritize findings by priority and severity
         *
         * @param array $findings
         */
        protected function prioritize_findings(array &$findings) {
            usort($findings, function($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });
        }

        /**
         * Derive Content / SEO Opportunity Payload for Content AI
         *
         * @param array $context
         * @param array $findings
         * @return array
         */
        protected function derive_content_opportunity(array $context, array $findings) {
            $gsc = $context['gsc_data'];
            $has_gsc = !empty($gsc['impressions']) && intval($gsc['impressions']) > 0;

            $primary_kw = $context['focus_kw'] ?: $context['title'];

            return array(
                'type'               => 'content_optimization',
                'topic'              => $context['title'],
                'primary_keyword'    => $primary_kw,
                'secondary_keywords' => array(),
                'intent'             => $context['intent'],
                'angle'              => sprintf(__('Search optimization guide for %s', 'gmb-ranker-seo-automation'), esc_html($primary_kw)),
                'reason'             => $has_gsc 
                    ? sprintf(__('Page generating %s impressions in GSC. Content & metadata optimization required.', 'gmb-ranker-seo-automation'), number_format(intval($gsc['impressions'])))
                    : __('On-page SEO optimization and content depth expansion opportunity.', 'gmb-ranker-seo-automation'),
                'gsc_evidence'       => $has_gsc ? array(
                    'query'       => $primary_kw,
                    'impressions' => intval($gsc['impressions']),
                    'clicks'      => intval($gsc['clicks'] ?? 0),
                    'ctr'         => floatval($gsc['ctr'] ?? 0),
                    'position'    => floatval($gsc['position'] ?? 0),
                ) : array(),
                'post_id'            => $context['post_id'],
            );
        }

        /**
         * Calculate Score and Formatted Results Array
         *
         * @param array $findings
         * @param array $context
         * @return array
         */
        protected function calculate_score_and_results(array $findings, array $context) {
            $deductions = 0;
            $results    = array();

            $severity_deductions = array(
                'critical' => 25,
                'high'     => 15,
                'medium'   => 8,
                'low'      => 3,
                'info'     => 0,
            );

            foreach ($findings as $f) {
                $sev = $f['severity'];
                $conf = $f['confidence'];
                $ded = isset($severity_deductions[$sev]) ? $severity_deductions[$sev] : 0;
                $deductions += ($ded * $conf);

                $results[] = array(
                    'type' => $f['type'],
                    'msg'  => $f['description'],
                );
            }

            $score = max(0, min(100, (int)round(100 - $deductions)));

            return array(
                'score'   => $score,
                'results' => $results,
            );
        }

        /**
         * Recursively extract clean text from Elementor data structure
         *
         * @param array $elements
         * @return string
         */
        protected function extract_text_from_elementor_data(array $elements) {
            $extracted = array();
            foreach ($elements as $elem) {
                if (!is_array($elem)) continue;

                if (isset($elem['settings']) && is_array($elem['settings'])) {
                    foreach ($elem['settings'] as $key => $val) {
                        if (in_array($key, array('editor', 'title', 'description', 'text', 'caption', 'testimonial_content'), true) && is_string($val)) {
                            $clean = wp_strip_all_tags($val);
                            if (strlen(trim($clean)) > 0) {
                                $extracted[] = $clean;
                            }
                        }
                    }
                }

                if (!empty($elem['elements']) && is_array($elem['elements'])) {
                    $child_text = $this->extract_text_from_elementor_data($elem['elements']);
                    if (!empty($child_text)) {
                        $extracted[] = $child_text;
                    }
                }
            }

            return implode(" \n", $extracted);
        }

        /**
         * Helper to compare two URLs for equivalence
         *
         * @param string $url1
         * @param string $url2
         * @return bool
         */
        protected function urls_are_equivalent($url1, $url2) {
            $norm1 = strtolower(rtrim(preg_replace('/^https?:\/\//i', '', trim($url1)), '/'));
            $norm2 = strtolower(rtrim(preg_replace('/^https?:\/\//i', '', trim($url2)), '/'));

            return ($norm1 !== '' && $norm1 === $norm2);
        }

        /**
         * Check for keyword cannibalization across all published posts, pages, and custom post types
         *
         * @param string $focus_kw
         * @param int $exclude_post_id
         * @return array
         */
        public static function check_keyword_cannibalization($focus_kw, $exclude_post_id = 0) {
            global $wpdb;

            $kw_parts = array_values(array_filter(array_map('trim', explode(',', (string) $focus_kw))));
            $kw = mb_strtolower(!empty($kw_parts) ? $kw_parts[0] : '');
            if (empty($kw)) {
                return array(
                    'is_cannibalized' => false,
                    'conflict_count'  => 0,
                    'conflicts'       => array(),
                );
            }

            $exclude_id = intval($exclude_post_id);

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT p.ID, p.post_title, p.post_type, p.post_status
                     FROM {$wpdb->posts} p
                     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                     WHERE p.ID != %d
                       AND p.post_status = 'publish'
                       AND pm.meta_key IN ('_gmb_ranker_focus_keyword', '_yoast_wpseo_focuskw', 'rank_math_focus_keyword')
                       AND LOWER(TRIM(pm.meta_value)) = %s
                     GROUP BY p.ID
                     LIMIT 10",
                    $exclude_id,
                    $kw
                )
            );

            $conflicts = array();
            if (!empty($results)) {
                foreach ($results as $row) {
                    $conflicts[] = array(
                        'id'        => (int) $row->ID,
                        'title'     => $row->post_title,
                        'post_type' => $row->post_type,
                        'edit_url'  => get_edit_post_link($row->ID, 'raw'),
                        'permalink' => get_permalink($row->ID),
                    );
                }
            }

            return array(
                'is_cannibalized' => !empty($conflicts),
                'conflict_count'  => count($conflicts),
                'conflicts'       => $conflicts,
            );
        }

        /**
         * Return empty fallback response for invalid post IDs
         *
         * @return array
         */
        protected function get_empty_audit_response() {
            return array(
                'score'       => 0,
                'results'     => array(),
                'metrics'     => array(
                    'word_count' => 0,
                    'title_len'  => 0,
                    'desc_len'   => 0,
                    'focus_kw'   => '',
                    'h2_count'   => 0,
                    'h3_count'   => 0,
                    'images'     => 0,
                    'links'      => 0,
                    'intent'     => 'unknown',
                ),
                'findings'    => array(),
                'opportunity' => array(),
                'context'     => array(),
            );
        }
    }
}
