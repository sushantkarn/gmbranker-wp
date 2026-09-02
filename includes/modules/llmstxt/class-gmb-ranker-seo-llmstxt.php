<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_LLMs_Txt {
    public function __construct() {
        add_action('parse_request', array($this, 'handle_request'), 1);
    }

    public function handle_request($wp) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $path = wp_parse_url($request_uri, PHP_URL_PATH);

        if (preg_match('/\/llms\.txt\/?$/i', $path)) {
            $this->generate_llms_txt();
            exit;
        }

        if (preg_match('/\/llms-full\.txt\/?$/i', $path)) {
            $this->generate_llms_full_txt();
            exit;
        }
    }

    private function get_query_posts() {
        $limit = intval(get_option('gmb_llms_limit', '100'));
        $post_types = get_option('gmb_llms_post_types', null);
        if (!is_array($post_types) || empty($post_types)) {
            $public_types = array_values(get_post_types(array('public' => true), 'names'));
            $post_types = array_values(array_diff($public_types, array('attachment')));
        }

        $args = array(
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : 100,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        $exclusions = get_option('gmb_llms_exclusions', '');
        if (!empty($exclusions)) {
            $args['post__not_in'] = array_map('intval', explode(',', $exclusions));
        }

        $posts = get_posts($args);
        $valid_posts = array();

        foreach ($posts as $p) {
            $robots = get_post_meta($p->ID, '_gmb_ranker_seo_robots', true);
            if ($robots && strpos($robots, 'noindex') !== false) {
                continue;
            }
            $valid_posts[] = $p;
        }

        return $valid_posts;
    }

    public function generate_llms_txt() {
        header('Content-Type: text/plain; charset=utf-8');

        $title = get_option('gmb_llms_title', get_bloginfo('name'));
        $description = get_option('gmb_llms_desc', get_bloginfo('description'));
        $posts = $this->get_query_posts();

        echo "# " . esc_html($title) . "\n\n";
        if (!empty($description)) {
            echo "> " . esc_html($description) . "\n\n";
        }

        echo "## Information\n";
        echo "- [Full Content Text Feed](" . esc_url(site_url('llms-full.txt')) . ")\n\n";

        echo "## Sections\n";
        echo "- [Main Content](#main-content)\n";
        
        $taxonomies = get_option('gmb_llms_taxonomies', array());
        if (!empty($taxonomies) && is_array($taxonomies)) {
            foreach ($taxonomies as $tax) {
                $tax_obj = get_taxonomy($tax);
                if ($tax_obj) {
                    echo "- [" . esc_html($tax_obj->label) . "](#" . sanitize_title($tax_obj->label) . ")\n";
                }
            }
        }
        echo "\n";

        echo "## Main Content\n";
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $focus_keyword = get_post_meta($post->ID, '_gmb_ranker_focus_keyword', true);
                if (empty($focus_keyword)) {
                    $focus_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true) ?: get_post_meta($post->ID, '_rank_math_focus_keyword', true);
                }
                
                $desc = '';
                if (!empty($focus_keyword)) {
                    $desc = 'Focus Keyword: ' . $focus_keyword;
                } else {
                    $desc = wp_trim_words(wp_strip_all_tags($post->post_content), 12);
                }
                
                echo "- [" . esc_html($post->post_title) . "](" . esc_url(get_permalink($post->ID)) . "): " . esc_html($desc) . "\n";
            }
        } else {
            echo "No content found.\n";
        }

        if (!empty($taxonomies) && is_array($taxonomies)) {
            $limit = intval(get_option('gmb_llms_limit', '100'));
            foreach ($taxonomies as $tax) {
                $tax_obj = get_taxonomy($tax);
                if (!$tax_obj) continue;

                echo "\n## " . esc_html($tax_obj->label) . "\n";
                $terms = get_terms(array(
                    'taxonomy'   => $tax,
                    'hide_empty' => true,
                    'number'     => $limit > 0 ? $limit : 100
                ));

                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        $term_link = get_term_link($term);
                        if (is_wp_error($term_link)) {
                            continue;
                        }
                        echo "- [" . esc_html($term->name) . "](" . esc_url($term_link) . "): Term archive listing.\n";
                    }
                } else {
                    echo "No terms found.\n";
                }
            }
        }

        $additional = get_option('gmb_llms_additional_content', '');
        if (!empty($additional)) {
            echo "\n" . trim($additional) . "\n";
        }
    }

    public function generate_llms_full_txt() {
        header('Content-Type: text/plain; charset=utf-8');

        $title = get_option('gmb_llms_title', get_bloginfo('name'));
        $posts = $this->get_query_posts();

        echo "# Full Content Feed - " . esc_html($title) . "\n";
        echo "This file provides the complete plain text content of all public pages for LLM crawling.\n\n";
        echo "---\n\n";

        if (!empty($posts)) {
            foreach ($posts as $post) {
                echo "# " . esc_html($post->post_title) . "\n";
                echo "URL: " . esc_url(get_permalink($post->ID)) . "\n\n";
                
                $content = strip_shortcodes($post->post_content);
                $content = wp_strip_all_tags($content);
                
                $content = preg_replace("/\r\n|\r|\n/", "\n", $content);
                $content = preg_replace("/\n{3,}/", "\n\n", $content);
                
                echo trim($content) . "\n\n";
                echo "---\n\n";
            }
        } else {
            echo "No content found.\n";
        }

        $additional = get_option('gmb_llms_additional_content', '');
        if (!empty($additional)) {
            echo "\n" . trim($additional) . "\n";
        }
    }
}
