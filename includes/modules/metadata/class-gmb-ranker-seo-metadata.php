<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Metadata {
    public function __construct() {
        if ($this->is_rank_math_active()) {
            add_filter('rank_math/frontend/title', array($this, 'filter_rank_math_title'), 100);
            add_filter('rank_math/frontend/description', array($this, 'filter_rank_math_description'), 100);
            add_filter('rank_math/frontend/canonical', array($this, 'filter_rank_math_canonical'), 100);
            add_filter('rank_math/frontend/robots', array($this, 'filter_rank_math_robots'), 100);

            // FB / Twitter
            add_filter('rank_math/opengraph/facebook/title', array($this, 'filter_rank_math_fb_title'), 100);
            add_filter('rank_math/opengraph/facebook/description', array($this, 'filter_rank_math_fb_desc'), 100);
            add_filter('rank_math/opengraph/facebook/image', array($this, 'filter_rank_math_fb_image'), 100);
            add_filter('rank_math/opengraph/twitter/title', array($this, 'filter_rank_math_tw_title'), 100);
            add_filter('rank_math/opengraph/twitter/description', array($this, 'filter_rank_math_tw_desc'), 100);
            add_filter('rank_math/opengraph/twitter/image', array($this, 'filter_rank_math_tw_image'), 100);
        } elseif ($this->is_yoast_active()) {
            add_filter('wpseo_title', array($this, 'filter_yoast_title'), 100);
            add_filter('wpseo_metadesc', array($this, 'filter_yoast_description'), 100);
            add_filter('wpseo_canonical', array($this, 'filter_yoast_canonical'), 100);
            add_filter('wpseo_robots', array($this, 'filter_yoast_robots'), 100);

            // FB / Twitter
            add_filter('wpseo_opengraph_title', array($this, 'filter_yoast_fb_title'), 100);
            add_filter('wpseo_opengraph_desc', array($this, 'filter_yoast_fb_desc'), 100);
            add_filter('wpseo_opengraph_image', array($this, 'filter_yoast_fb_image'), 100);
            add_filter('wpseo_twitter_title', array($this, 'filter_yoast_tw_title'), 100);
            add_filter('wpseo_twitter_description', array($this, 'filter_yoast_tw_desc'), 100);
            add_filter('wpseo_twitter_image', array($this, 'filter_yoast_tw_image'), 100);
        } else {
            add_filter('pre_get_document_title', array($this, 'filter_raw_seo_title'), 999);
            add_filter('document_title_parts', array($this, 'filter_seo_title'), 999);
            add_filter('wp_title', array($this, 'filter_wp_title'), 999, 2);
        }

        // Support for AIOSEO & SEOPress
        add_filter('aioseo_title', array($this, 'filter_aioseo_title'), 100);
        add_filter('aioseo_description', array($this, 'filter_aioseo_desc'), 100);
        add_filter('aioseo_canonical_url', array($this, 'filter_aioseo_canonical'), 100);
        add_filter('seopress_titles_title', array($this, 'filter_seopress_title'), 100);
        add_filter('seopress_titles_desc', array($this, 'filter_seopress_desc'), 100);
        add_filter('seopress_titles_canonical', array($this, 'filter_seopress_canonical'), 100);

        // RSS Feed Content Customization
        add_filter('the_content_feed', array($this, 'filter_rss_feed_content'));
        add_filter('the_excerpt_rss', array($this, 'filter_rss_feed_content'));

        // Universal Output Buffer to guarantee <title> replacement on all custom themes
        add_action('template_redirect', array($this, 'start_buffer'), 1);
        add_action('shutdown', array($this, 'end_buffer'), 0);

        // Output meta tags directly to wp_head
        add_action('wp_head', array($this, 'filter_seo_meta_tags'), 1);
    }

    public function is_rank_math_active() {
        return class_exists('RankMath') && class_exists('RankMath\Helper');
    }

    public function is_yoast_active() {
        return defined('WPSEO_VERSION');
    }

    public function start_buffer() {
        if (!is_admin() && !wp_is_json_request() && !is_feed() && !is_robots()) {
            if (ob_get_level() === 0 || !in_array('buffer_callback', (array) ob_list_handlers(), true)) {
                ob_start(array($this, 'buffer_callback'));
            }
        }
    }

    public function end_buffer() {
        if (ob_get_level() > 0 && in_array('buffer_callback', (array) ob_list_handlers(), true)) {
            @ob_end_flush();
        }
    }

    public function buffer_callback($html) {
        if (empty($html) || strpos($html, '<html') === false) {
            return $html;
        }

        $seo_title = $this->get_current_seo_title();
        if (!empty($seo_title)) {
            if (preg_match('/<title(\s[^>]*)?>.*?<\/title>/is', $html)) {
                $html = preg_replace('/<title(\s[^>]*)?>.*?<\/title>/is', '<title$1>' . esc_html($seo_title) . '</title>', $html, 1);
            }
        }

        return $html;
    }

    public function get_current_page_id() {
        if (is_front_page()) {
            $id = get_option('page_on_front');
            if (!empty($id)) return intval($id);
        }
        if (is_home()) {
            $id = get_option('page_for_posts');
            if (!empty($id)) return intval($id);
        }
        if (is_singular()) {
            $id = get_the_ID();
            if (!empty($id)) return intval($id);
        }
        $queried = get_queried_object_id();
        if (!empty($queried)) {
            return intval($queried);
        }
        return 0;
    }

    public function replace_variables($string, $post_id = 0) {
        if (empty($string)) {
            return '';
        }

        $sep = get_option('gmb_metadata_separator', '-');
        $replacements = array(
            '%sitename%'     => get_bloginfo('name'),
            '%sitedesc%'     => get_bloginfo('description'),
            '%date%'         => date_i18n('j'),
            '%currentmonth%' => date_i18n('F'),
            '%currentyear%'  => date_i18n('Y'),
            '%sep%'          => $sep,
            '%page%'         => '',
        );

        if (is_search()) {
            $replacements['%search_query%'] = get_search_query();
        }

        if (is_date()) {
            if (is_day()) {
                $replacements['%date%'] = get_the_date();
            } elseif (is_month()) {
                $replacements['%date%'] = get_the_date('F Y');
            } elseif (is_year()) {
                $replacements['%date%'] = get_the_date('Y');
            } else {
                $replacements['%date%'] = get_the_date();
            }
        }

        if (is_paged() || get_query_var('paged') > 1) {
            $paged = max(1, get_query_var('paged'));
            $max_pages = isset($GLOBALS['wp_query']->max_num_pages) ? intval($GLOBALS['wp_query']->max_num_pages) : 1;
            $replacements['%page%'] = sprintf('page %d of %d', $paged, $max_pages);
        }

        if (is_author()) {
            $author_id = get_query_var('author');
            $replacements['%name%'] = get_the_author_meta('display_name', $author_id);
        }

        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $replacements['%term%'] = $term->name;
                $replacements['%term_description%'] = term_description($term->term_id);
            }
        }

        if (!empty($post_id)) {
            $post = get_post($post_id);
            if (!empty($post)) {
                if ($this->is_rank_math_active() && class_exists('RankMath\Helper')) {
                    return \RankMath\Helper::replace_vars($string, $post);
                }
                $replacements['%title%'] = get_the_title($post_id);
                $replacements['%author%'] = get_the_author_meta('display_name', $post->post_author);
                $replacements['%focus_keyword%'] = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) ?: '';
                $replacements['%date%'] = get_the_date('', $post_id);
                $replacements['%modified%'] = get_the_modified_date('', $post_id);
                $replacements['%id%'] = strval($post_id);
                
                $categories = get_the_category($post_id);
                $replacements['%category%'] = !empty($categories) ? $categories[0]->name : '';
                
                $excerpt = get_the_excerpt($post_id);
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words($post->post_content, 30);
                }
                $replacements['%excerpt%'] = $excerpt;

                if ($post->post_type === 'attachment') {
                    $file_path = get_attached_file($post_id);
                    $replacements['%filename%'] = !empty($file_path) ? basename($file_path) : '';
                    $replacements['%caption%'] = !empty($post->post_excerpt) ? $post->post_excerpt : '';
                    $replacements['%description%'] = !empty($post->post_content) ? $post->post_content : '';
                    $replacements['%alt%'] = get_post_meta($post_id, '_wp_attachment_image_alt', true) ?: '';
                }
            }
        }

        foreach ($replacements as $placeholder => $value) {
            $string = str_replace($placeholder, $value, $string);
        }

        // Support %custom_field(field_name)%
        if (strpos($string, '%custom_field(') !== false && !empty($post_id)) {
            $string = preg_replace_callback('/%custom_field\(([^)]+)\)%/i', function($m) use ($post_id) {
                $field_key = sanitize_text_field(trim($m[1]));
                $field_val = get_post_meta($post_id, $field_key, true);
                return is_scalar($field_val) ? strval($field_val) : '';
            }, $string);
        }

        return $string;
    }

    public function get_current_seo_title() {
        $post_id = $this->get_current_page_id();
        $custom_title = '';

        if (!empty($post_id)) {
            $custom_title = get_post_meta($post_id, '_gmb_ranker_seo_title', true);
            if (empty($custom_title)) {
                $custom_title = get_post_meta($post_id, '_yoast_wpseo_title', true) 
                    ?: (get_post_meta($post_id, 'rank_math_title', true) 
                    ?: get_post_meta($post_id, '_rank_math_title', true));
            }
        }

        if (empty($custom_title)) {
            if (is_front_page() || is_home()) {
                $custom_title = get_option('gmb_homepage_title_template', '%sitename% %sep% %sitedesc%');
            } elseif (is_singular() && !empty($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type === 'post') {
                    $custom_title = get_option('gmb_metadata_post_title_template', '%title% - %sitename%');
                } elseif ($post_type === 'attachment') {
                    $custom_title = get_option('gmb_attachment_title_template', '%title% %sep% %sitename%');
                } elseif ($post_type === 'services') {
                    $custom_title = get_option('gmb_services_title_template', '%title% %sep% %sitename%');
                } elseif ($post_type === 'service_locations' || $post_type === 'service_location') {
                    $custom_title = get_option('gmb_service_locations_title_template', '%title% %sep% %sitename%');
                } elseif ($post_type === 'team_members' || $post_type === 'team_member') {
                    $custom_title = get_option('gmb_team_members_title_template', '%title% %sep% %sitename%');
                } else {
                    $custom_title = get_option('gmb_metadata_page_title_template', '%title% - %sitename%');
                }
            } elseif (is_author()) {
                $author_id = get_query_var('author');
                if (empty($author_id)) {
                    $author_obj = get_queried_object();
                    if ($author_obj instanceof WP_User) {
                        $author_id = $author_obj->ID;
                    }
                }
                $custom_title = !empty($author_id) ? get_user_meta($author_id, '_gmb_ranker_author_title', true) : '';
                if (empty($custom_title)) {
                    $custom_title = get_option('gmb_author_archive_title', '%name% %sep% %sitename% %page%');
                }
            } elseif (is_404()) {
                $custom_title = get_option('gmb_misc_404_title', 'Page Not Found %sep% %sitename%');
            } elseif (is_search()) {
                $custom_title = get_option('gmb_misc_search_title', '%search_query% %page% %sep% %sitename%');
            } elseif (is_date()) {
                $custom_title = get_option('gmb_misc_date_archive_title', '%date% %sep% %sitename% %page%');
            } elseif (is_category() || is_tag() || is_tax()) {
                $custom_title = get_option('gmb_categories_archive_title', '%term% %sep% %sitename%');
            }
        }

        if (!empty($custom_title)) {
            $title = $this->replace_variables($custom_title, $post_id);
            if (get_option('gmb_metadata_capitalize_titles', '0') === '1') {
                $title = ucwords($title);
            }
            return trim($title);
        }

        return '';
    }

    public function get_current_seo_description() {
        $post_id = $this->get_current_page_id();
        $custom_desc = '';

        if (!empty($post_id)) {
            $custom_desc = get_post_meta($post_id, '_gmb_ranker_seo_description', true);
            if (empty($custom_desc)) {
                $custom_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true) 
                    ?: (get_post_meta($post_id, 'rank_math_description', true) 
                    ?: get_post_meta($post_id, '_rank_math_description', true));
            }
        }

        if (empty($custom_desc)) {
            if (is_front_page() || is_home()) {
                $custom_desc = get_option('gmb_homepage_desc_template', '');
                if (empty($custom_desc)) {
                    $custom_desc = get_bloginfo('description');
                }
            } elseif (is_singular() && !empty($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type === 'post') {
                    $custom_desc = get_option('gmb_metadata_post_desc_template', '%excerpt%');
                } elseif ($post_type === 'attachment') {
                    $custom_desc = get_option('gmb_attachment_desc_template', '%caption%');
                } elseif ($post_type === 'services') {
                    $custom_desc = get_option('gmb_services_desc_template', '%excerpt%');
                } elseif ($post_type === 'service_locations' || $post_type === 'service_location') {
                    $custom_desc = get_option('gmb_service_locations_desc_template', '%excerpt%');
                } elseif ($post_type === 'team_members' || $post_type === 'team_member') {
                    $custom_desc = get_option('gmb_team_members_desc_template', '%excerpt%');
                } else {
                    $custom_desc = get_option('gmb_metadata_page_desc_template', '%excerpt%');
                }
            } elseif (is_author()) {
                $author_id = get_query_var('author');
                if (empty($author_id)) {
                    $author_obj = get_queried_object();
                    if ($author_obj instanceof WP_User) {
                        $author_id = $author_obj->ID;
                    }
                }
                $custom_desc = !empty($author_id) ? get_user_meta($author_id, '_gmb_ranker_author_desc', true) : '';
                if (empty($custom_desc)) {
                    $custom_desc = get_option('gmb_author_archive_desc', '');
                    if (empty($custom_desc) && !empty($author_id)) {
                        $custom_desc = get_the_author_meta('description', $author_id);
                    }
                }
            } elseif (is_date()) {
                $custom_desc = get_option('gmb_misc_date_archive_desc', '');
            } elseif (is_category() || is_tag() || is_tax()) {
                $custom_desc = get_option('gmb_categories_archive_desc', '%term_description%');
            }
        }

        if (!empty($custom_desc)) {
            $desc = $this->replace_variables($custom_desc, $post_id);
            return trim(wp_strip_all_tags($desc));
        }

        return '';
    }

    public function get_current_seo_canonical() {
        $post_id = $this->get_current_page_id();
        $canonical = '';
        if (!empty($post_id)) {
            $canonical = get_post_meta($post_id, '_gmb_ranker_seo_canonical', true);
            if (empty($canonical)) {
                $canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true) 
                    ?: (get_post_meta($post_id, 'rank_math_canonical_url', true) 
                    ?: get_post_meta($post_id, '_rank_math_canonical', true));
            }
        }
        if (empty($canonical)) {
            if (is_front_page()) {
                $canonical = home_url('/');
            } elseif (is_singular() && !empty($post_id)) {
                $canonical = get_permalink($post_id);
            }
        }
        return !empty($canonical) ? esc_url($canonical) : '';
    }

    public function get_current_seo_robots() {
        $post_id = $this->get_current_page_id();
        $robots = '';
        if (!empty($post_id)) {
            $robots = get_post_meta($post_id, '_gmb_ranker_seo_robots', true);
        }
        if (empty($robots)) {
            if (is_front_page() || is_home()) {
                $robots_enable = get_option('gmb_homepage_robots_meta_enable', '0');
                if ($robots_enable === '1') {
                    $robots = get_option('gmb_homepage_robots_meta', '');
                }
            } elseif (is_singular() && !empty($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type === 'post' && get_option('gmb_posts_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_posts_robots_meta', '');
                } elseif ($post_type === 'page' && get_option('gmb_pages_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_pages_robots_meta', '');
                } elseif ($post_type === 'attachment' && get_option('gmb_attachment_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_attachment_robots_meta', '');
                } elseif (($post_type === 'services' || $post_type === 'service') && get_option('gmb_services_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_services_robots_meta', '');
                } elseif (in_array($post_type, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations')) && get_option('gmb_service_locations_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_service_locations_robots_meta', '');
                } elseif (in_array($post_type, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members')) && get_option('gmb_team_members_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_team_members_robots_meta', '');
                }
            } elseif (is_category()) {
                $term_id = get_queried_object_id();
                $term_robots = !empty($term_id) ? get_term_meta($term_id, '_gmb_ranker_robots', true) : '';
                if (!empty($term_robots)) {
                    $robots = $term_robots;
                } elseif (get_option('gmb_categories_robots_meta_enable', '0') === '1') {
                    $robots = get_option('gmb_categories_robots_meta', '');
                }
            } elseif (is_author()) {
                $author_id = get_query_var('author');
                if (empty($author_id)) {
                    $author_obj = get_queried_object();
                    if ($author_obj instanceof WP_User) {
                        $author_id = $author_obj->ID;
                    }
                }
                $user_robots = !empty($author_id) ? get_user_meta($author_id, '_gmb_ranker_author_robots', true) : '';
                if (!empty($user_robots)) {
                    $robots = $user_robots;
                } elseif (get_option('gmb_author_robots_meta_enable', '1') === '1') {
                    $robots = get_option('gmb_author_robots_meta', 'noindex');
                }
            } elseif (is_search() && get_option('gmb_misc_noindex_search_results', '1') === '1') {
                $robots = 'noindex';
            } elseif (is_date() && get_option('gmb_misc_date_robots_meta_enable', '1') === '1') {
                $robots = get_option('gmb_misc_date_robots_meta', 'noindex');
            }
        }

        if (empty($robots)) {
            if (is_paged() && get_option('gmb_misc_noindex_subpages', '0') === '1') {
                $robots = 'noindex, follow';
            } elseif (is_singular() && (get_query_var('page') > 1 || get_query_var('paged') > 1) && get_option('gmb_misc_noindex_paginated_single', '0') === '1') {
                $robots = 'noindex, follow';
            } elseif (is_singular() && !empty($post_id) && post_password_required($post_id) && get_option('gmb_misc_noindex_password_protected', '0') === '1') {
                $robots = 'noindex, nofollow';
            }
        }

        if (empty($robots)) {
            $robots = get_option('gmb_metadata_global_robots', 'index, follow');
        }
        return is_array($robots) ? implode(', ', $robots) : $robots;
    }

    public function filter_seo_title($title_parts) {
        $custom = $this->get_current_seo_title();
        if (!empty($custom)) {
            $title_parts['title'] = $custom;
            unset($title_parts['site'], $title_parts['tagline']);
        }
        return $title_parts;
    }

    public function filter_raw_seo_title($title) {
        $custom = $this->get_current_seo_title();
        return !empty($custom) ? $custom : $title;
    }

    public function filter_wp_title($title, $sep = '') {
        return $this->filter_raw_seo_title($title);
    }

    public function filter_rank_math_title($title) {
        $custom = $this->get_current_seo_title();
        return !empty($custom) ? $custom : $title;
    }

    public function filter_rank_math_description($desc) {
        $custom = $this->get_current_seo_description();
        return !empty($custom) ? $custom : $desc;
    }

    public function filter_rank_math_canonical($canonical) {
        $custom = $this->get_current_seo_canonical();
        return !empty($custom) ? $custom : $canonical;
    }

    public function filter_rank_math_robots($robots) {
        $custom = $this->get_current_seo_robots();
        if (!empty($custom)) {
            if (is_array($robots)) {
                return array_filter(array_map('trim', explode(',', $custom)));
            }
            return $custom;
        }
        return $robots;
    }

    // FB / Twitter Filters for Rank Math
    public function filter_rank_math_fb_title($title) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_title', true) : '';
        if (empty($custom)) {
            $custom = $this->get_current_seo_title();
        }
        return !empty($custom) ? $this->replace_variables($custom, $post_id) : $title;
    }

    public function filter_rank_math_fb_desc($desc) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_desc', true) : '';
        if (empty($custom)) {
            $custom = $this->get_current_seo_description();
        }
        return !empty($custom) ? $this->replace_variables($custom, $post_id) : $desc;
    }

    public function filter_rank_math_fb_image($image) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_image', true) : '';
        if (empty($custom) && !empty($post_id) && has_post_thumbnail($post_id)) {
            $custom = get_the_post_thumbnail_url($post_id, 'large');
        }
        if (empty($custom)) {
            $custom = get_option('gmb_metadata_og_thumbnail', '');
        }
        return !empty($custom) ? esc_url($custom) : $image;
    }

    public function filter_rank_math_tw_title($title) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_title', true) ?: get_post_meta($post_id, '_gmb_ranker_facebook_title', true)) : '';
        if (empty($custom)) {
            $custom = $this->get_current_seo_title();
        }
        return !empty($custom) ? $this->replace_variables($custom, $post_id) : $title;
    }

    public function filter_rank_math_tw_desc($desc) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_desc', true) ?: get_post_meta($post_id, '_gmb_ranker_facebook_desc', true)) : '';
        if (empty($custom)) {
            $custom = $this->get_current_seo_description();
        }
        return !empty($custom) ? $this->replace_variables($custom, $post_id) : $desc;
    }

    public function filter_rank_math_tw_image($image) {
        $post_id = $this->get_current_page_id();
        $custom = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_image', true) ?: get_post_meta($post_id, '_gmb_ranker_facebook_image', true)) : '';
        if (empty($custom) && !empty($post_id) && has_post_thumbnail($post_id)) {
            $custom = get_the_post_thumbnail_url($post_id, 'large');
        }
        if (empty($custom)) {
            $custom = get_option('gmb_metadata_og_thumbnail', '');
        }
        return !empty($custom) ? esc_url($custom) : $image;
    }

    // Yoast Fallback Filter Callbacks
    public function filter_yoast_title($title) {
        return $this->filter_rank_math_title($title);
    }

    public function filter_yoast_description($desc) {
        return $this->filter_rank_math_description($desc);
    }

    public function filter_yoast_canonical($canonical) {
        return $this->filter_rank_math_canonical($canonical);
    }

    public function filter_yoast_robots($robots) {
        return $this->filter_rank_math_robots($robots);
    }

    public function filter_yoast_fb_title($title) {
        return $this->filter_rank_math_fb_title($title);
    }

    public function filter_yoast_fb_desc($desc) {
        return $this->filter_rank_math_fb_desc($desc);
    }

    public function filter_yoast_fb_image($image) {
        return $this->filter_rank_math_fb_image($image);
    }

    public function filter_yoast_tw_title($title) {
        return $this->filter_rank_math_tw_title($title);
    }

    public function filter_yoast_tw_desc($desc) {
        return $this->filter_rank_math_tw_desc($desc);
    }

    public function filter_yoast_tw_image($image) {
        return $this->filter_rank_math_tw_image($image);
    }

    public function filter_aioseo_title($title) {
        return $this->filter_rank_math_title($title);
    }

    public function filter_aioseo_desc($desc) {
        return $this->filter_rank_math_description($desc);
    }

    public function filter_aioseo_canonical($canonical) {
        return $this->filter_rank_math_canonical($canonical);
    }

    public function filter_seopress_title($title) {
        return $this->filter_rank_math_title($title);
    }

    public function filter_seopress_desc($desc) {
        return $this->filter_rank_math_description($desc);
    }

    public function filter_seopress_canonical($canonical) {
        return $this->filter_rank_math_canonical($canonical);
    }

    public function filter_seo_meta_tags() {
        // Skip direct echo output if a major SEO plugin is already doing it.
        if ($this->is_rank_math_active() || $this->is_yoast_active()) {
            return;
        }

        $post_id = $this->get_current_page_id();
        $title = $this->get_current_seo_title();
        $desc = $this->get_current_seo_description();
        $canonical = $this->get_current_seo_canonical();
        $robots = $this->get_current_seo_robots();

        echo "\n<!-- GMB Ranker SEO Metadata -->\n";
        if (!empty($desc)) {
            echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
        }
        if (!empty($canonical)) {
            echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
        }
        if (!empty($robots)) {
            $robots_arr = array_map('trim', explode(',', strtolower($robots)));
            if (!in_array('noindex', $robots_arr)) {
                $max_image = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_max_image', true) : '';
                if (empty($max_image) && (is_front_page() || is_home())) {
                    $max_image = get_option('gmb_homepage_advanced_max_image', 'large');
                } elseif (empty($max_image) && is_author()) {
                    $max_image = get_option('gmb_author_advanced_max_image', 'large');
                } elseif (empty($max_image) && is_category()) {
                    $max_image = get_option('gmb_categories_advanced_max_image', 'large');
                } elseif (empty($max_image) && is_singular()) {
                    $pt = get_post_type($post_id);
                    if ($pt === 'post') {
                        $max_image = get_option('gmb_posts_advanced_max_image', 'large');
                    } elseif ($pt === 'page') {
                        $max_image = get_option('gmb_pages_advanced_max_image', 'large');
                    } elseif ($pt === 'attachment') {
                        $max_image = get_option('gmb_attachment_advanced_max_image', 'large');
                    } elseif ($pt === 'services' || $pt === 'service') {
                        $max_image = get_option('gmb_services_advanced_max_image', 'large');
                    } elseif (in_array($pt, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations'))) {
                        $max_image = get_option('gmb_service_locations_advanced_max_image', 'large');
                    } elseif (in_array($pt, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'))) {
                        $max_image = get_option('gmb_team_members_advanced_max_image', 'large');
                    }
                }
                if (empty($max_image)) $max_image = 'large';

                $max_snippet = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_max_snippet', true) : '';
                if (empty($max_snippet) && (is_front_page() || is_home())) {
                    $max_snippet = get_option('gmb_homepage_advanced_max_snippet', '-1');
                } elseif (empty($max_snippet) && is_author()) {
                    $max_snippet = get_option('gmb_author_advanced_max_snippet', '-1');
                } elseif (empty($max_snippet) && is_category()) {
                    $max_snippet = get_option('gmb_categories_advanced_max_snippet', '-1');
                } elseif (empty($max_snippet) && is_singular()) {
                    $pt = get_post_type($post_id);
                    if ($pt === 'post') {
                        $max_snippet = get_option('gmb_posts_advanced_max_snippet', '-1');
                    } elseif ($pt === 'page') {
                        $max_snippet = get_option('gmb_pages_advanced_max_snippet', '-1');
                    } elseif ($pt === 'attachment') {
                        $max_snippet = get_option('gmb_attachment_advanced_max_snippet', '-1');
                    } elseif ($pt === 'services' || $pt === 'service') {
                        $max_snippet = get_option('gmb_services_advanced_max_snippet', '-1');
                    } elseif (in_array($pt, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations'))) {
                        $max_snippet = get_option('gmb_service_locations_advanced_max_snippet', '-1');
                    } elseif (in_array($pt, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'))) {
                        $max_snippet = get_option('gmb_team_members_advanced_max_snippet', '-1');
                    }
                }
                if (empty($max_snippet)) $max_snippet = '-1';

                $max_video = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_max_video', true) : '';
                if (empty($max_video) && (is_front_page() || is_home())) {
                    $max_video = get_option('gmb_homepage_advanced_max_video', '-1');
                } elseif (empty($max_video) && is_author()) {
                    $max_video = get_option('gmb_author_advanced_max_video', '-1');
                } elseif (empty($max_video) && is_category()) {
                    $max_video = get_option('gmb_categories_advanced_max_video', '-1');
                } elseif (empty($max_video) && is_singular()) {
                    $pt = get_post_type($post_id);
                    if ($pt === 'post') {
                        $max_video = get_option('gmb_posts_advanced_max_video', '-1');
                    } elseif ($pt === 'page') {
                        $max_video = get_option('gmb_pages_advanced_max_video', '-1');
                    } elseif ($pt === 'attachment') {
                        $max_video = get_option('gmb_attachment_advanced_max_video', '-1');
                    } elseif ($pt === 'services' || $pt === 'service') {
                        $max_video = get_option('gmb_services_advanced_max_video', '-1');
                    } elseif (in_array($pt, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations'))) {
                        $max_video = get_option('gmb_service_locations_advanced_max_video', '-1');
                    } elseif (in_array($pt, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'))) {
                        $max_video = get_option('gmb_team_members_advanced_max_video', '-1');
                    }
                }
                if (empty($max_video)) $max_video = '-1';

                $robots_str = $robots . ', max-image-preview:' . $max_image . ', max-snippet:' . $max_snippet . ', max-video-preview:' . $max_video;
            } else {
                $robots_str = $robots;
            }
            echo '<meta name="robots" content="' . esc_attr($robots_str) . '" />' . "\n";
        }

        // OpenGraph (Facebook)
        $fb_title = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_title', true) : '';
        if (empty($fb_title) && (is_front_page() || is_home())) {
            $fb_title = get_option('gmb_homepage_facebook_title', '');
        }
        if (empty($fb_title)) {
            $fb_title = $title;
        }

        $fb_desc = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_desc', true) : '';
        if (empty($fb_desc) && (is_front_page() || is_home())) {
            $fb_desc = get_option('gmb_homepage_facebook_desc', '');
        }
        if (empty($fb_desc)) {
            $fb_desc = $desc;
        }

        $fb_img = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_facebook_image', true) : '';
        if (empty($fb_img) && (is_front_page() || is_home())) {
            $fb_img = get_option('gmb_homepage_facebook_image', '');
        }
        if (empty($fb_img) && !empty($post_id) && has_post_thumbnail($post_id)) {
            $fb_img = get_the_post_thumbnail_url($post_id, 'large');
        }
        if (empty($fb_img)) {
            $fb_img = get_option('gmb_metadata_og_thumbnail', '');
        }

        $fb_page = get_option('gmb_social_facebook_page_url', '');
        $fb_author = get_option('gmb_social_facebook_authorship', '');
        $fb_admins = get_option('gmb_social_facebook_admin', '');
        $fb_app_id = get_option('gmb_social_facebook_app_id', '');

        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '" />' . "\n";
        echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '" />' . "\n";
        if (!empty($fb_title)) {
            echo '<meta property="og:title" content="' . esc_attr($this->replace_variables($fb_title, $post_id)) . '" />' . "\n";
        }
        if (!empty($fb_desc)) {
            echo '<meta property="og:description" content="' . esc_attr($this->replace_variables($fb_desc, $post_id)) . '" />' . "\n";
        }
        if (!empty($canonical)) {
            echo '<meta property="og:url" content="' . esc_url($canonical) . '" />' . "\n";
        }
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        if (!empty($fb_img)) {
            echo '<meta property="og:image" content="' . esc_url($fb_img) . '" />' . "\n";
        }
        if (!empty($fb_page)) {
            echo '<meta property="article:publisher" content="' . esc_url($fb_page) . '" />' . "\n";
        }
        if (!empty($fb_author)) {
            echo '<meta property="article:author" content="' . esc_url($fb_author) . '" />' . "\n";
        }
        if (!empty($fb_admins)) {
            echo '<meta property="fb:admins" content="' . esc_attr($fb_admins) . '" />' . "\n";
        }
        if (!empty($fb_app_id)) {
            echo '<meta property="fb:app_id" content="' . esc_attr($fb_app_id) . '" />' . "\n";
        }

        // Twitter Cards
        $tw_title = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_title', true) ?: $fb_title) : $fb_title;
        $tw_desc = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_desc', true) ?: $fb_desc) : $fb_desc;
        $tw_img = !empty($post_id) ? (get_post_meta($post_id, '_gmb_ranker_twitter_image', true) ?: $fb_img) : $fb_img;
        $tw_card_type = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_twitter_card_type', true) : '';
        if (empty($tw_card_type) && (is_front_page() || is_home())) {
            $tw_card_type = get_option('gmb_homepage_twitter_card_type', '');
        } elseif (empty($tw_card_type) && is_category()) {
            $tw_card_type = get_option('gmb_categories_twitter_card_type', '');
        } elseif (empty($tw_card_type) && is_singular()) {
            $pt = get_post_type($post_id);
            if ($pt === 'post') {
                $tw_card_type = get_option('gmb_posts_twitter_card_type', '');
            } elseif ($pt === 'page') {
                $tw_card_type = get_option('gmb_pages_twitter_card_type', '');
            } elseif ($pt === 'attachment') {
                $tw_card_type = get_option('gmb_attachment_twitter_card_type', '');
            } elseif ($pt === 'services' || $pt === 'service') {
                $tw_card_type = get_option('gmb_services_twitter_card_type', '');
            } elseif (in_array($pt, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations'))) {
                $tw_card_type = get_option('gmb_service_locations_twitter_card_type', '');
            } elseif (in_array($pt, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'))) {
                $tw_card_type = get_option('gmb_team_members_twitter_card_type', '');
            }
        }
        if (empty($tw_card_type)) {
            $tw_card_type = get_option('gmb_metadata_twitter_card_type', 'summary_large_image');
        }
        $tw_username = get_option('gmb_social_twitter_username', '');

        echo '<meta name="twitter:card" content="' . esc_attr($tw_card_type) . '" />' . "\n";
        if (!empty($tw_username)) {
            $tw_handle = (strpos($tw_username, '@') === 0) ? $tw_username : '@' . $tw_username;
            echo '<meta name="twitter:site" content="' . esc_attr($tw_handle) . '" />' . "\n";
            echo '<meta name="twitter:creator" content="' . esc_attr($tw_handle) . '" />' . "\n";
        }
        if (!empty($tw_title)) {
            echo '<meta name="twitter:title" content="' . esc_attr($this->replace_variables($tw_title, $post_id)) . '" />' . "\n";
        }
        if (!empty($tw_desc)) {
            echo '<meta name="twitter:description" content="' . esc_attr($this->replace_variables($tw_desc, $post_id)) . '" />' . "\n";
        }
        if (!empty($tw_img)) {
            echo '<meta name="twitter:image" content="' . esc_url($tw_img) . '" />' . "\n";
        }

        // Webmaster Verification Codes (Output on front page or site-wide)
        $google_verify = get_option('gmb_webmaster_google_verify', '');
        if (!empty($google_verify) && (is_front_page() || is_home())) {
            echo '<meta name="google-site-verification" content="' . esc_attr($google_verify) . '" />' . "\n";
        }
        $bing_verify = get_option('gmb_webmaster_bing_verify', '');
        if (!empty($bing_verify) && (is_front_page() || is_home())) {
            echo '<meta name="msvalidate.01" content="' . esc_attr($bing_verify) . '" />' . "\n";
        }
        $pinterest_verify = get_option('gmb_webmaster_pinterest_verify', '');
        if (!empty($pinterest_verify) && (is_front_page() || is_home())) {
            echo '<meta name="p:domain_verify" content="' . esc_attr($pinterest_verify) . '" />' . "\n";
        }
        $baidu_verify = get_option('gmb_webmaster_baidu_verify', '');
        if (!empty($baidu_verify) && (is_front_page() || is_home())) {
            echo '<meta name="baidu-site-verification" content="' . esc_attr($baidu_verify) . '" />' . "\n";
        }
        $yandex_verify = get_option('gmb_webmaster_yandex_verify', '');
        if (!empty($yandex_verify) && (is_front_page() || is_home())) {
            echo '<meta name="yandex-verification" content="' . esc_attr($yandex_verify) . '" />' . "\n";
        }

        echo "<!-- /GMB Ranker SEO Metadata -->\n\n";
    }

    public function filter_rss_feed_content($content) {
        $before = get_option('gmb_rss_before_content', '');
        $after = get_option('gmb_rss_after_content', '');
        $post_id = get_the_ID();

        if (!empty($before)) {
            $before_text = $this->replace_variables($before, $post_id);
            $content = wpautop($before_text) . $content;
        }

        if (!empty($after)) {
            $after_text = $this->replace_variables($after, $post_id);
            $content = $content . wpautop($after_text);
        }

        return $content;
    }
}
