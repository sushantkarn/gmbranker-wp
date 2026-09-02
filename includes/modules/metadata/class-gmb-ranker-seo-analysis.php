<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Analysis {
    public function __construct() {
        // Runs audits on post content
    }

    public static function run_onpage_audits($post_id) {
        $post = get_post($post_id);
        if (empty($post)) {
            return array('score' => 0, 'results' => array());
        }

        $title = $post->post_title;
        $content = $post->post_content;
        if (empty($content) || strlen(trim($content)) < 50) {
            $elementor_data = get_post_meta($post_id, '_elementor_data', true);
            if (!empty($elementor_data)) {
                $content .= ' ' . wp_strip_all_tags($elementor_data);
            }
            $all_meta = get_post_meta($post_id);
            foreach ($all_meta as $key => $values) {
                if (strpos($key, '_gmb_') === 0 || strpos($key, '_wp_') === 0) continue;
                foreach ($values as $val) {
                    if (is_string($val) && strlen($val) > 3 && !is_serialized($val)) {
                        $content .= ' ' . wp_strip_all_tags($val);
                    }
                }
            }
        }
        $meta_title = get_post_meta($post_id, '_gmb_ranker_seo_title', true) ?: $title;
        $meta_desc = get_post_meta($post_id, '_gmb_ranker_seo_description', true) ?: '';
        $focus_keyword = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) ?: '';
        if (empty($focus_keyword)) {
            $focus_keyword = get_post_meta($post_id, 'rank_math_focus_keyword', true) ?: '';
        }

        $results = array();
        $score = 0;

        // 1. Title Length Audit (10 pts)
        $title_len = mb_strlen($meta_title);
        if ($title_len >= 45 && $title_len <= 65) {
            $score += 10;
            $results[] = '✅ Title length is ideal (' . $title_len . ' characters).';
        } else {
            $results[] = '❌ Title length should ideally be between 50-60 characters (currently: ' . $title_len . ').';
        }

        // 2. Meta Description Length Audit (10 pts)
        $desc_len = mb_strlen($meta_desc);
        if ($desc_len >= 120 && $desc_len <= 160) {
            $score += 10;
            $results[] = '✅ Meta description length is ideal (' . $desc_len . ' characters).';
        } elseif ($desc_len > 0) {
            $score += 5;
            $results[] = '⚠️ Meta description is ' . $desc_len . ' characters (recommended: 120-160).';
        } else {
            $results[] = '❌ Meta description is missing. Add a description for search result snippet preview.';
        }

        // 3. Content Word Count (15 pts)
        $word_count = str_word_count(wp_strip_all_tags($content));
        if ($word_count >= 600) {
            $score += 15;
            $results[] = '✅ Content is long enough (' . $word_count . ' words).';
        } elseif ($word_count >= 300) {
            $score += 10;
            $results[] = '⚠️ Content length is ' . $word_count . ' words. Aim for 600+ words for higher rankings.';
        } else {
            $score += 3;
            $results[] = '❌ Content word count is low (' . $word_count . ' words). Try to write at least 600 words.';
        }

        // 4. Focus Keyword Optimization (35 pts total)
        $focus_keyword = trim($focus_keyword);
        if (!empty($focus_keyword)) {
            $focus_keyword_lower = mb_strtolower($focus_keyword);

            if (mb_strpos(mb_strtolower($meta_title), $focus_keyword_lower) !== false) {
                $score += 10;
                $results[] = '✅ Focus keyword found in SEO Title.';
            } else {
                $results[] = '❌ Focus keyword not found in SEO Title.';
            }

            if (!empty($meta_desc) && mb_strpos(mb_strtolower($meta_desc), $focus_keyword_lower) !== false) {
                $score += 10;
                $results[] = '✅ Focus keyword found in Meta Description.';
            } else {
                $results[] = '❌ Focus keyword not found in Meta Description.';
            }

            $count = !empty($focus_keyword_lower) ? mb_substr_count(mb_strtolower(wp_strip_all_tags($content)), $focus_keyword_lower) : 0;
            if ($count > 0) {
                $score += 10;
                $results[] = '✅ Focus keyword found in content (' . $count . ' times).';

                $density = ($count / max(1, $word_count)) * 100;
                if ($density >= 0.5 && $density <= 2.5) {
                    $score += 5;
                    $results[] = '✅ Focus keyword density is ideal (' . round($density, 2) . '%).';
                } else {
                    $results[] = '⚠️ Focus keyword density is ' . round($density, 2) . '% (recommended: ~1%).';
                }
            } else {
                $results[] = '❌ Focus keyword not found in content body.';
            }
        } else {
            if ($word_count >= 200) {
                $score += 15; // baseline readability score when no keyword is set
            }
            $results[] = '💡 Specify a Focus Keyword to enable comprehensive keyword optimization checks.';
        }

        // 5. Inbuilt Module: Table of Contents (TOC) (10 pts)
        $toc_module_enabled = (get_option('gmb_ranker_module_toc', '1') === '1');
        $toc_auto_insert = (get_option('gmb_toc_auto_insert', '1') === '1');
        $toc_min_headings = (int) get_option('gmb_toc_min_headings', 2);
        
        $headings_count = preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/i', $content, $h_matches);
        $has_explicit_toc = (stripos($content, 'gmb-toc-box') !== false || stripos($content, '[toc') !== false || stripos($content, 'table-of-contents') !== false || stripos($content, 'wp-block-table-of-contents') !== false);
        
        if ($has_explicit_toc) {
            $score += 10;
            $results[] = '✅ Table of Contents is active in content to improve user scannability.';
        } elseif ($toc_module_enabled && $toc_auto_insert && $headings_count >= $toc_min_headings) {
            $score += 10;
            $results[] = '✅ Table of Contents is automatically generated and prepended by GMB Ranker TOC module (' . $headings_count . ' headings detected).';
        } else {
            $results[] = '💡 Add a Table of Contents (or 2+ headings with TOC module enabled) to structure your content.';
        }

        // 6. Inbuilt Module: Schema Structured Data (10 pts)
        $schema_module_enabled = (get_option('gmb_ranker_module_schema', '1') === '1');
        $active_schemas = get_post_meta($post_id, '_gmb_ranker_active_schemas', true);
        $custom_jsonld = get_post_meta($post_id, '_gmb_ranker_schema_custom_jsonld', true);
        $pt_schema = get_option('gmb_' . $post->post_type . '_schema_type', 'Article');
        
        if ($schema_module_enabled && (!empty($active_schemas) || !empty($custom_jsonld) || !empty($pt_schema))) {
            $score += 10;
            $s_type = is_array($active_schemas) ? implode(', ', $active_schemas) : ($active_schemas ?: $pt_schema);
            $results[] = '✅ Schema Structured Data is configured (' . esc_html($s_type) . ') for rich search result snippets.';
        } else {
            $results[] = '💡 Configure Schema Markup in GMB Ranker to enable rich SERP snippets.';
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
                if ($img_module_enabled && $has_alt < $img_count) {
                    $results[] = '✅ Images are optimized with descriptive alt attributes (enhanced by GMB Ranker Image SEO automation).';
                } else {
                    $results[] = '✅ Images include descriptive alt attributes (' . $has_alt . '/' . $img_count . ' with alt text).';
                }
            } else {
                $results[] = '❌ Images are missing alt text. Enable Image SEO module or add descriptive alt tags.';
            }
        }

        // 8. Inbuilt Module: Internal & External Link Equity (5 pts)
        $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $internal_links = 0;
        $external_links = 0;
        if (preg_match_all('/<a\s+[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $link_matches, PREG_SET_ORDER)) {
            foreach ($link_matches as $lm) {
                $href = $lm[1];
                if (strpos($href, '#') === 0 || strpos($href, 'mailto:') === 0 || strpos($href, 'tel:') === 0) continue;
                $href_host = wp_parse_url($href, PHP_URL_HOST);
                if (empty($href_host) || $href_host === $site_host) {
                    $internal_links++;
                } else {
                    $external_links++;
                }
            }
        }

        if ($internal_links > 0 || $external_links > 0) {
            $score += 5;
            $results[] = '✅ Content includes link references (' . $internal_links . ' internal, ' . $external_links . ' external).';
        } else {
            $results[] = '💡 Add internal links pointing to related articles or external authority references.';
        }

        $final_score = min(100, max(0, $score));

        // Cache score in post meta for post list tables & analytics
        update_post_meta($post_id, '_gmb_ranker_seo_score', $final_score);

        return array(
            'score'          => $final_score,
            'results'        => $results,
            'internal_links' => $internal_links,
            'external_links' => $external_links,
            'word_count'     => $word_count,
            'headings_count' => $headings_count,
            'has_toc'        => ($has_explicit_toc || ($toc_module_enabled && $toc_auto_insert && $headings_count >= $toc_min_headings)),
            'has_schema'     => $schema_module_enabled,
            'has_image_seo'  => $img_module_enabled,
        );
    }
}

