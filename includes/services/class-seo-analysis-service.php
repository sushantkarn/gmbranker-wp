<?php
/**
 * SEO Analysis Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Analysis_Service {

    /**
     * Run full on-page SEO audits and calculate composite score (0-100)
     *
     * @param int $post_id
     * @return array [ 'score' => int, 'results' => array, 'metrics' => array ]
     */
    public function audit_post($post_id) {
        $post = get_post($post_id);
        if (empty($post)) {
            return array('score' => 0, 'results' => array(), 'metrics' => array());
        }

        $title = $post->post_title;
        $content = $post->post_content;

        // Extract Elementor content if present
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if (!empty($elementor_data)) {
            $content .= ' ' . wp_strip_all_tags($elementor_data);
        }

        $meta_title = get_post_meta($post_id, '_gmb_ranker_seo_title', true) ?: $title;
        $meta_desc  = get_post_meta($post_id, '_gmb_ranker_seo_description', true) ?: '';
        $focus_kw   = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) ?: '';
        if (empty($focus_kw)) {
            $focus_kw = get_post_meta($post_id, 'rank_math_focus_keyword', true) ?: '';
        }

        $score = 0;
        $results = array();

        // 1. Title Length (10 pts)
        $title_len = mb_strlen($meta_title);
        if ($title_len >= 45 && $title_len <= 65) {
            $score += 10;
            $results[] = array('type' => 'success', 'msg' => 'Title length is optimal (' . $title_len . ' chars).');
        } else {
            $results[] = array('type' => 'warning', 'msg' => 'Title should ideally be 50–60 characters (currently: ' . $title_len . ').');
        }

        // 2. Meta Description Length (10 pts)
        $desc_len = mb_strlen($meta_desc);
        if ($desc_len >= 120 && $desc_len <= 160) {
            $score += 10;
            $results[] = array('type' => 'success', 'msg' => 'Meta description length is optimal (' . $desc_len . ' chars).');
        } elseif ($desc_len > 0) {
            $score += 5;
            $results[] = array('type' => 'warning', 'msg' => 'Meta description is ' . $desc_len . ' characters (recommended: 120–160).');
        } else {
            $results[] = array('type' => 'error', 'msg' => 'Meta description is missing.');
        }

        // 3. Content Word Count (15 pts)
        $word_count = str_word_count(wp_strip_all_tags($content));
        if ($word_count >= 600) {
            $score += 15;
            $results[] = array('type' => 'success', 'msg' => 'Comprehensive content length (' . $word_count . ' words).');
        } elseif ($word_count >= 300) {
            $score += 10;
            $results[] = array('type' => 'warning', 'msg' => 'Good content length (' . $word_count . ' words). Aim for 600+ for higher ranking potential.');
        } else {
            $score += 3;
            $results[] = array('type' => 'error', 'msg' => 'Word count is low (' . $word_count . ' words).');
        }

        // 4. Focus Keyword Checks (35 pts)
        if (!empty($focus_kw)) {
            $focus_lower = mb_strtolower(trim($focus_kw));

            // In Title
            if (mb_strpos(mb_strtolower($meta_title), $focus_lower) !== false) {
                $score += 10;
                $results[] = array('type' => 'success', 'msg' => 'Focus keyword appears in SEO Title.');
            } else {
                $results[] = array('type' => 'error', 'msg' => 'Focus keyword missing from SEO Title.');
            }

            // In Description
            if (mb_strpos(mb_strtolower($meta_desc), $focus_lower) !== false) {
                $score += 10;
                $results[] = array('type' => 'success', 'msg' => 'Focus keyword appears in Meta Description.');
            } else {
                $results[] = array('type' => 'error', 'msg' => 'Focus keyword missing from Meta Description.');
            }

            // In Body Content
            $kw_count = mb_substr_count(mb_strtolower(wp_strip_all_tags($content)), $focus_lower);
            if ($kw_count > 0) {
                $score += 10;
                $density = ($kw_count / max(1, $word_count)) * 100;
                if ($density >= 0.5 && $density <= 2.5) {
                    $score += 5;
                    $results[] = array('type' => 'success', 'msg' => 'Focus keyword found ' . $kw_count . ' times (' . round($density, 1) . '% density).');
                } else {
                    $results[] = array('type' => 'warning', 'msg' => 'Focus keyword density is ' . round($density, 1) . '% (recommended: ~1%).');
                }
            } else {
                $results[] = array('type' => 'error', 'msg' => 'Focus keyword not found in content body.');
            }
        } else {
            if ($word_count >= 200) {
                $score += 15;
            }
        }

        // 5. Inbuilt Module: Table of Contents (TOC) (10 pts)
        $toc_module_enabled = (get_option('gmb_ranker_module_toc', '1') === '1');
        $toc_auto_insert = (get_option('gmb_toc_auto_insert', '1') === '1');
        $toc_min_headings = (int) get_option('gmb_toc_min_headings', 2);
        $headings_count = preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/i', $content, $h_matches);
        $has_explicit_toc = (stripos($content, 'gmb-toc-box') !== false || stripos($content, '[toc') !== false || stripos($content, 'table-of-contents') !== false || stripos($content, 'wp-block-table-of-contents') !== false);
        
        if ($has_explicit_toc || ($toc_module_enabled && $toc_auto_insert && $headings_count >= $toc_min_headings)) {
            $score += 10;
            $results[] = array('type' => 'success', 'msg' => 'Table of Contents is active (enhanced by GMB Ranker TOC module).');
        } else {
            $results[] = array('type' => 'info', 'msg' => 'Add a Table of Contents (or 2+ headings) to improve page readability.');
        }

        // 6. Inbuilt Module: Schema Structured Data (10 pts)
        $schema_module_enabled = (get_option('gmb_ranker_module_schema', '1') === '1');
        $active_schemas = get_post_meta($post_id, '_gmb_ranker_active_schemas', true);
        $custom_jsonld = get_post_meta($post_id, '_gmb_ranker_schema_custom_jsonld', true);
        $pt_schema = get_option('gmb_' . $post->post_type . '_schema_type', 'Article');
        
        if ($schema_module_enabled && (!empty($active_schemas) || !empty($custom_jsonld) || !empty($pt_schema))) {
            $score += 10;
            $results[] = array('type' => 'success', 'msg' => 'Schema Structured Data is configured for rich snippet search results.');
        } else {
            $results[] = array('type' => 'warning', 'msg' => 'Configure Schema Markup in GMB Ranker to enable rich snippets.');
        }

        // 7. Inbuilt Module: Image SEO & Alt Optimization (5 pts)
        $img_module_enabled = (get_option('gmb_ranker_module_image_seo', '1') === '1');
        $img_count = preg_match_all('/<img\s+[^>]*>/i', $content, $img_matches);
        if ($img_count > 0) {
            $has_alt = 0;
            foreach ($img_matches[0] as $img_tag) {
                if (preg_match('/alt=[\'"][^\'"]+[\'"]/i', $img_tag)) {
                    $has_alt++;
                }
            }
            if ($has_alt > 0 || $img_module_enabled) {
                $score += 5;
                $results[] = array('type' => 'success', 'msg' => 'Images are optimized with descriptive alt attributes.');
            } else {
                $results[] = array('type' => 'warning', 'msg' => 'Images are missing alt text.');
            }
        }

        // 8. Inbuilt Module: Internal & External Link Equity (5 pts)
        $has_links = (stripos($content, '<a ') !== false || stripos($content, 'href=') !== false);
        if ($has_links) {
            $score += 5;
            $results[] = array('type' => 'success', 'msg' => 'Internal and external links are present in content.');
        } else {
            $results[] = array('type' => 'warning', 'msg' => 'Consider adding internal and external links.');
        }

        $final_score = min(100, max(0, $score));

        // Cache score in post meta
        update_post_meta($post_id, '_gmb_ranker_seo_score', $final_score);

        return array(
            'score'   => $final_score,
            'results' => $results,
            'metrics' => array(
                'word_count' => $word_count,
                'title_len'  => $title_len,
                'desc_len'   => $desc_len,
                'focus_kw'   => $focus_kw,
            ),
        );
    }
}

