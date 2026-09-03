<?php
/**
 * SEO Analysis Service for GMB Ranker SEO Automation
 *
 * Enterprise-level SEO analysis orchestrator and diagnostic engine.
 * Collects evidence, evaluates page context, headings, metadata, schema,
 * images, links, and query relevance, returning structured findings and diagnostic scores.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Analysis_Service')) {

    class GMB_Ranker_SEO_Analysis_Service {

        /**
         * Run evidence-based on-page SEO audits and calculate diagnostic score (0-100)
         *
         * @param int $post_id
         * @return array [ 'score' => int, 'results' => array, 'metrics' => array, 'findings' => array, 'context' => array ]
         */
        public function audit_post($post_id) {
            $post = get_post($post_id);
            if (empty($post)) {
                return array(
                    'score'   => 0,
                    'results' => array(),
                    'metrics' => array(
                        'word_count' => 0,
                        'title_len'  => 0,
                        'desc_len'   => 0,
                        'focus_kw'   => '',
                    ),
                );
            }

            // 1. Build Page Context
            $context = $this->collect_page_context($post);

            // 2. Run Evidence-Based Analyzers
            $findings = $this->run_analyzers($context);

            // 3. Compute Diagnostic Score & Formatted Results
            $scoring = $this->calculate_score_and_results($findings, $context);

            $final_score = $scoring['score'];
            $results     = $scoring['results'];

            // Persist diagnostic score
            update_post_meta($post_id, '_gmb_ranker_seo_score', $final_score);

            return array(
                'score'    => $final_score,
                'results'  => $results,
                'metrics'  => array(
                    'word_count' => $context['word_count'],
                    'title_len'  => $context['title_len'],
                    'desc_len'   => $context['desc_len'],
                    'focus_kw'   => $context['focus_kw'],
                ),
                'findings' => $findings,
                'context'  => array(
                    'post_id'   => $post_id,
                    'post_type' => $context['post_type'],
                    'permalink' => $context['permalink'],
                ),
            );
        }

        /**
         * Collect Normalized Page Context & Evidence
         *
         * @param WP_Post $post
         * @return array
         */
        protected function collect_page_context($post) {
            $post_id = $post->ID;
            $title   = $post->post_title;
            $content = $post->post_content;

            // Handle Elementor content safely if present
            $elementor_data = get_post_meta($post_id, '_elementor_data', true);
            if (!empty($elementor_data) && is_string($elementor_data)) {
                $clean_elementor = preg_replace('/[^\w\s\.\,\-\?\!]/u', ' ', $elementor_data);
                $content .= ' ' . $clean_elementor;
            }

            $meta_title = get_post_meta($post_id, '_gmb_ranker_seo_title', true) ?: $title;
            $meta_desc  = get_post_meta($post_id, '_gmb_ranker_seo_description', true) ?: '';
            $focus_kw   = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) ?: '';
            if (empty($focus_kw)) {
                $focus_kw = get_post_meta($post_id, 'rank_math_focus_keyword', true) ?: '';
            }

            $clean_text  = wp_strip_all_tags($content);
            $word_count  = str_word_count($clean_text);
            $title_len   = mb_strlen($meta_title);
            $desc_len    = mb_strlen($meta_desc);

            // Heading extraction
            preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $content, $h1_matches);
            preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $h2_matches);
            preg_match_all('/<h3[^>]*>(.*?)<\/h3>/i', $content, $h3_matches);

            // Image extraction
            preg_match_all('/<img\s+[^>]*>/i', $content, $img_matches);
            $images_count = count($img_matches[0]);
            $images_with_alt = 0;
            if ($images_count > 0) {
                foreach ($img_matches[0] as $img_tag) {
                    if (preg_match('/alt=[\'"][^\'"]+[\'"]/i', $img_tag)) {
                        $images_with_alt++;
                    }
                }
            }

            // Link extraction
            preg_match_all('/<a\s+[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)<\/a>/i', $content, $link_matches);
            $links_count = count($link_matches[0]);

            return array(
                'post_id'         => $post_id,
                'post_type'       => $post->post_type,
                'permalink'       => get_permalink($post_id),
                'title'           => $title,
                'content'         => $content,
                'clean_text'      => $clean_text,
                'word_count'      => $word_count,
                'meta_title'      => $meta_title,
                'title_len'       => $title_len,
                'meta_desc'       => $meta_desc,
                'desc_len'        => $desc_len,
                'focus_kw'        => $focus_kw,
                'h1_count'        => count($h1_matches[0]),
                'h2_count'        => count($h2_matches[0]),
                'h3_count'        => count($h3_matches[0]),
                'images_count'    => $images_count,
                'images_with_alt' => $images_with_alt,
                'links_count'     => $links_count,
            );
        }

        /**
         * Run Evidence-Based Analyzers
         *
         * @param array $context
         * @return array
         */
        protected function run_analyzers($context) {
            $findings = array();

            // 1. Title Analysis
            if ($context['title_len'] >= 45 && $context['title_len'] <= 65) {
                $findings[] = array(
                    'category' => 'title',
                    'severity' => 'info',
                    'type'     => 'success',
                    'weight'   => 15,
                    'msg'      => sprintf(__('SEO Title length is well-balanced for SERP display (%d characters).', 'gmb-ranker-seo-automation'), $context['title_len']),
                );
            } elseif ($context['title_len'] > 0) {
                $findings[] = array(
                    'category' => 'title',
                    'severity' => 'low',
                    'type'     => 'warning',
                    'weight'   => 8,
                    'msg'      => sprintf(__('SEO Title is %d characters. SERP snippets display 50–60 characters best.', 'gmb-ranker-seo-automation'), $context['title_len']),
                );
            } else {
                $findings[] = array(
                    'category' => 'title',
                    'severity' => 'high',
                    'type'     => 'error',
                    'weight'   => 0,
                    'msg'      => __('SEO Title is missing.', 'gmb-ranker-seo-automation'),
                );
            }

            // 2. Meta Description Analysis
            if ($context['desc_len'] >= 120 && $context['desc_len'] <= 160) {
                $findings[] = array(
                    'category' => 'description',
                    'severity' => 'info',
                    'type'     => 'success',
                    'weight'   => 15,
                    'msg'      => sprintf(__('Meta Description length fits SERP snippets (%d characters).', 'gmb-ranker-seo-automation'), $context['desc_len']),
                );
            } elseif ($context['desc_len'] > 0) {
                $findings[] = array(
                    'category' => 'description',
                    'severity' => 'low',
                    'type'     => 'warning',
                    'weight'   => 8,
                    'msg'      => sprintf(__('Meta Description is %d characters (120–160 recommended).', 'gmb-ranker-seo-automation'), $context['desc_len']),
                );
            } else {
                $findings[] = array(
                    'category' => 'description',
                    'severity' => 'high',
                    'type'     => 'error',
                    'weight'   => 0,
                    'msg'      => __('Meta Description is missing.', 'gmb-ranker-seo-automation'),
                );
            }

            // 3. Content Depth Analysis
            if ($context['word_count'] >= 500) {
                $findings[] = array(
                    'category' => 'content',
                    'severity' => 'info',
                    'type'     => 'success',
                    'weight'   => 20,
                    'msg'      => sprintf(__('Content length satisfies topical depth (%d words).', 'gmb-ranker-seo-automation'), $context['word_count']),
                );
            } elseif ($context['word_count'] >= 200) {
                $findings[] = array(
                    'category' => 'content',
                    'severity' => 'medium',
                    'type'     => 'warning',
                    'weight'   => 12,
                    'msg'      => sprintf(__('Content contains %d words. Additional depth may improve coverage.', 'gmb-ranker-seo-automation'), $context['word_count']),
                );
            } else {
                $findings[] = array(
                    'category' => 'content',
                    'severity' => 'high',
                    'type'     => 'error',
                    'weight'   => 4,
                    'msg'      => sprintf(__('Content is concise (%d words). Consider expanding key sections.', 'gmb-ranker-seo-automation'), $context['word_count']),
                );
            }

            // 4. Target Query / Keyword Analysis
            if (!empty($context['focus_kw'])) {
                $kw = mb_strtolower(trim($context['focus_kw']));

                // Keyword in Title
                if (mb_strpos(mb_strtolower($context['meta_title']), $kw) !== false) {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'info',
                        'type'     => 'success',
                        'weight'   => 10,
                        'msg'      => sprintf(__('Focus keyword "%s" is present in SEO Title.', 'gmb-ranker-seo-automation'), $context['focus_kw']),
                    );
                } else {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'medium',
                        'type'     => 'error',
                        'weight'   => 0,
                        'msg'      => sprintf(__('Focus keyword "%s" is missing from SEO Title.', 'gmb-ranker-seo-automation'), $context['focus_kw']),
                    );
                }

                // Keyword in Meta Description
                if (mb_strpos(mb_strtolower($context['meta_desc']), $kw) !== false) {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'info',
                        'type'     => 'success',
                        'weight'   => 10,
                        'msg'      => sprintf(__('Focus keyword "%s" is present in Meta Description.', 'gmb-ranker-seo-automation'), $context['focus_kw']),
                    );
                } else {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'medium',
                        'type'     => 'error',
                        'weight'   => 0,
                        'msg'      => sprintf(__('Focus keyword "%s" is missing from Meta Description.', 'gmb-ranker-seo-automation'), $context['focus_kw']),
                    );
                }

                // Keyword in Body Content
                $occurrences = mb_substr_count(mb_strtolower($context['clean_text']), $kw);
                if ($occurrences > 0) {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'info',
                        'type'     => 'success',
                        'weight'   => 10,
                        'msg'      => sprintf(__('Focus keyword appears %d times in body text.', 'gmb-ranker-seo-automation'), $occurrences),
                    );
                } else {
                    $findings[] = array(
                        'category' => 'keyword',
                        'severity' => 'medium',
                        'type'     => 'error',
                        'weight'   => 0,
                        'msg'      => sprintf(__('Focus keyword "%s" is missing from body text.', 'gmb-ranker-seo-automation'), $context['focus_kw']),
                    );
                }
            } else {
                $findings[] = array(
                    'category' => 'keyword',
                    'severity' => 'info',
                    'type'     => 'info',
                    'weight'   => 20,
                    'msg'      => __('No focus keyword configured. Keyword analysis skipped.', 'gmb-ranker-seo-automation'),
                );
            }

            // 5. Structure & Headings Analysis
            if ($context['h2_count'] >= 1 || $context['h3_count'] >= 1) {
                $findings[] = array(
                    'category' => 'structure',
                    'severity' => 'info',
                    'type'     => 'success',
                    'weight'   => 10,
                    'msg'      => sprintf(__('Structured headings present (%d H2, %d H3).', 'gmb-ranker-seo-automation'), $context['h2_count'], $context['h3_count']),
                );
            } else {
                $findings[] = array(
                    'category' => 'structure',
                    'severity' => 'low',
                    'type'     => 'warning',
                    'weight'   => 4,
                    'msg'      => __('Add section headings (H2/H3) to structure long content.', 'gmb-ranker-seo-automation'),
                );
            }

            // 6. Image Analysis
            if ($context['images_count'] > 0) {
                if ($context['images_with_alt'] === $context['images_count']) {
                    $findings[] = array(
                        'category' => 'images',
                        'severity' => 'info',
                        'type'     => 'success',
                        'weight'   => 5,
                        'msg'      => sprintf(__('All %d images contain descriptive alt text.', 'gmb-ranker-seo-automation'), $context['images_count']),
                    );
                } else {
                    $findings[] = array(
                        'category' => 'images',
                        'severity' => 'low',
                        'type'     => 'warning',
                        'weight'   => 2,
                        'msg'      => sprintf(__('%d of %d images have alt text.', 'gmb-ranker-seo-automation'), $context['images_with_alt'], $context['images_count']),
                    );
                }
            }

            // 7. Links Analysis
            if ($context['links_count'] > 0) {
                $findings[] = array(
                    'category' => 'links',
                    'severity' => 'info',
                    'type'     => 'success',
                    'weight'   => 5,
                    'msg'      => sprintf(__('Content includes %d contextual links.', 'gmb-ranker-seo-automation'), $context['links_count']),
                );
            }

            return $findings;
        }

        /**
         * Calculate Score and Formatted Results Array
         *
         * @param array $findings
         * @param array $context
         * @return array
         */
        protected function calculate_score_and_results($findings, $context) {
            $total_weight = 0;
            $results = array();

            foreach ($findings as $finding) {
                $total_weight += isset($finding['weight']) ? (int) $finding['weight'] : 0;
                $results[] = array(
                    'type' => $finding['type'],
                    'msg'  => $finding['msg'],
                );
            }

            $score = min(100, max(0, $total_weight));

            return array(
                'score'   => $score,
                'results' => $results,
            );
        }
    }
}
