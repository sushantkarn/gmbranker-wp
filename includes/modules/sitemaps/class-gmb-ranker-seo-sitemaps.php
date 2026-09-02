<?php
/**
 * XML & HTML Dynamic Sitemap Engine
 *
 * @package GMB_Ranker_SEO_Automation
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Sitemaps {

    /**
     * Initialize Sitemap hooks.
     */
    public function __construct() {
        add_action('parse_request', array($this, 'handle_sitemap_requests'));
        add_action('transition_post_status', array($this, 'maybe_ping_search_engines'), 10, 3);
        add_shortcode('gmb_html_sitemap', array($this, 'render_html_sitemap_shortcode'));
    }

    /**
     * Check if a conflicting third-party sitemap is active.
     *
     * @return bool
     */
    private function is_third_party_sitemap_active() {
        if (class_exists('RankMath\Helper') && \RankMath\Helper::is_module_active('sitemap')) {
            return true;
        }
        if (class_exists('WPSEO_Options')) {
            $yoast_options = get_option('wpseo');
            if (is_array($yoast_options) && !empty($yoast_options['enable_xml_sitemap'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Intercept and handle dynamic sitemap HTTP requests.
     *
     * @param WP $wp
     */
    public function handle_sitemap_requests($wp) {
        if (get_option('gmb_ranker_module_sitemaps', '1') === '0') {
            return;
        }

        if ($this->is_third_party_sitemap_active()) {
            return;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        if (preg_match('/\/gmb-sitemap\.xsl$/i', $uri)) {
            $this->generate_sitemap_xsl();
            if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
        } elseif (preg_match('/\/sitemap\.xml(\?.*)?$/i', $uri)) {
            wp_safe_redirect(home_url('/sitemap_index.xml'), 301);
            if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
        } elseif (preg_match('/\/sitemap_index\.xml$/i', $uri)) {
            $this->generate_sitemap_index();
            if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
        } elseif (preg_match('/\/author-sitemap\.xml$/i', $uri)) {
            $this->generate_author_sitemap();
            if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
        } elseif (preg_match('/\/custom-sitemap\.xml$/i', $uri)) {
            $this->generate_custom_sitemap();
            if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
        } elseif (preg_match('/\/([a-zA-Z0-9_\-]+)-sitemap\.xml$/i', $uri, $matches)) {
            $sitemap_slug = $matches[1];
            $public_post_types = get_post_types(array('public' => true), 'names');
            $public_taxonomies = get_taxonomies(array('public' => true), 'names');
            
            if (isset($public_post_types[$sitemap_slug])) {
                $this->generate_post_sitemap($sitemap_slug);
                if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
            } elseif (isset($public_taxonomies[$sitemap_slug])) {
                $this->generate_taxonomy_sitemap($sitemap_slug);
                if (!defined('GMB_RANKER_SEO_TESTS')) { exit; }
            }
        }
    }

    /**
     * Generate the main XML Sitemap Index.
     */
    private function generate_sitemap_index() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(home_url('/gmb-sitemap.xsl')) . '"?>';
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Dynamically loop over all public post types
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $pt_name => $pt_obj) {
            $is_included = get_option('gmb_sitemap_include_pt_' . $pt_name, ($pt_name === 'attachment' ? '0' : '1'));
            if ($is_included === '0') {
                continue;
            }

            // Check if there is at least one published post
            $counts = wp_count_posts($pt_name);
            if (!empty($counts->publish) && intval($counts->publish) > 0) {
                $last_post = get_posts(array(
                    'post_type'      => $pt_name,
                    'post_status'    => 'publish',
                    'posts_per_page' => 1,
                    'orderby'        => 'modified',
                    'order'          => 'DESC',
                ));
                $lastmod = !empty($last_post[0]) ? mysql2date('Y-m-d\TH:i:s\Z', $last_post[0]->post_modified_gmt) : gmdate('Y-m-d\TH:i:s\Z');

                echo "\t<sitemap>\n";
                echo "\t\t<loc>" . esc_url(home_url('/' . $pt_name . '-sitemap.xml')) . "</loc>\n";
                echo "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
                echo "\t</sitemap>\n";
            }
        }

        // Dynamically loop over all public taxonomies
        $taxonomies = get_taxonomies(array('public' => true), 'names');

        foreach ($taxonomies as $tax_name) {
            $is_included = get_option('gmb_sitemap_include_tax_' . $tax_name, ($tax_name === 'post_format' ? '0' : '1'));
            if ($is_included === '0') {
                continue;
            }

            $hide_empty = get_option('gmb_sitemap_empty_tax_' . $tax_name, '0') !== '1';
            $term_count = wp_count_terms(array('taxonomy' => $tax_name, 'hide_empty' => $hide_empty));
            
            if (!is_wp_error($term_count) && intval($term_count) > 0) {
                echo "\t<sitemap>\n";
                echo "\t\t<loc>" . esc_url(home_url('/' . $tax_name . '-sitemap.xml')) . "</loc>\n";
                echo "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
                echo "\t</sitemap>\n";
            }
        }

        // Include Author Sitemap if enabled
        if (get_option('gmb_sitemap_include_authors', '0') === '1') {
            $authors = get_users(array(
                'has_published_posts' => array('post', 'page'),
                'number'              => 1,
            ));
            if (!empty($authors)) {
                echo "\t<sitemap>\n";
                echo "\t\t<loc>" . esc_url(home_url('/author-sitemap.xml')) . "</loc>\n";
                echo "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
                echo "\t</sitemap>\n";
            }
        }

        // Include Custom URLs Sitemap if custom URLs exist
        $custom_urls_raw = get_option('gmb_sitemap_custom_urls', '');
        if (!empty(trim($custom_urls_raw))) {
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . esc_url(home_url('/custom-sitemap.xml')) . "</loc>\n";
            echo "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
            echo "\t</sitemap>\n";
        }

        echo '</sitemapindex>';
    }

    /**
     * Generate individual post type sitemap.
     *
     * @param string $post_type
     */
    private function generate_post_sitemap($post_type) {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(home_url('/gmb-sitemap.xsl')) . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        $items_per_page = intval(get_option('gmb_sitemap_items_per_page', 1000));
        if ($items_per_page < 10) {
            $items_per_page = 1000;
        }

        $query_args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $items_per_page,
            'orderby'        => 'modified',
            'order'          => 'DESC'
        );

        // Exclude specific post IDs
        $excluded_ids_str = get_option('gmb_sitemap_excluded_posts', '');
        if (!empty($excluded_ids_str)) {
            $excluded_ids = array_filter(array_map('absint', explode(',', $excluded_ids_str)));
            if (!empty($excluded_ids)) {
                $query_args['post__not_in'] = $excluded_ids;
            }
        }

        $query = new WP_Query($query_args);

        $exclude_slugs_str = get_option('gmb_ranker_sitemap_exclude_slugs', '');
        $excluded_slugs = array_filter(array_map('trim', explode(',', strtolower($exclude_slugs_str))));

        $include_images = get_option('gmb_sitemap_include_images', '1') !== '0';
        $pt_image_override = get_option('gmb_sitemap_images_pt_' . $post_type, '1') !== '0';

        $front_page_id = (get_option('show_on_front') === 'page') ? intval(get_option('page_on_front')) : 0;
        $home_url_normalized = untrailingslashit(home_url());
        $emitted_urls = array();

        // If post_type is page, place the front page at the top with Priority 1.0 and daily freq
        if ($post_type === 'page') {
            if ($front_page_id > 0) {
                $front_post = get_post($front_page_id);
                if ($front_post && $front_post->post_status === 'publish') {
                    $robots = get_post_meta($front_post->ID, '_gmb_ranker_seo_robots', true);
                    $exclude = get_post_meta($front_post->ID, '_gmb_ranker_seo_exclude_sitemap', true);
                    if (strpos((string)$robots, 'noindex') === false && $exclude !== 'yes' && $exclude !== '1') {
                        $this->render_sitemap_url_entry($front_post, '1.0', 'daily', $include_images && $pt_image_override);
                        $emitted_urls[untrailingslashit(get_permalink($front_post->ID))] = true;
                        $emitted_urls[$home_url_normalized] = true;
                    }
                }
            } else {
                // Blog on front
                $latest_post = !empty($query->posts[0]) ? $query->posts[0] : null;
                $lastmod = $latest_post ? mysql2date('Y-m-d\TH:i:s\Z', $latest_post->post_modified_gmt) : gmdate('Y-m-d\TH:i:s\Z');
                
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url(home_url('/')) . "</loc>\n";
                echo "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
                echo "\t\t<changefreq>daily</changefreq>\n";
                echo "\t\t<priority>1.0</priority>\n";
                echo "\t</url>\n";
                $emitted_urls[$home_url_normalized] = true;
            }
        }

        foreach ($query->posts as $post) {
            $perm = untrailingslashit(get_permalink($post->ID));
            
            // Prevent duplicate emission if this page is the front page already rendered
            if (isset($emitted_urls[$perm]) || ($front_page_id > 0 && $post->ID === $front_page_id)) {
                continue;
            }

            $robots = get_post_meta($post->ID, '_gmb_ranker_seo_robots', true);
            if ($robots && strpos((string)$robots, 'noindex') !== false) {
                continue;
            }
            
            $exclude_sitemap = get_post_meta($post->ID, '_gmb_ranker_seo_exclude_sitemap', true);
            if ($exclude_sitemap === 'yes' || $exclude_sitemap === '1' || $exclude_sitemap === true) {
                continue;
            }

            if (!empty($excluded_slugs) && in_array(strtolower($post->post_name), $excluded_slugs, true)) {
                continue;
            }

            $priority = ($post_type === 'page') ? '0.8' : '0.7';
            $changefreq = 'weekly';
            
            $this->render_sitemap_url_entry($post, $priority, $changefreq, $include_images && $pt_image_override);
            $emitted_urls[$perm] = true;
        }

        echo '</urlset>';
    }

    /**
     * Render a single sitemap <url> node with metadata and extracted images.
     *
     * @param WP_Post $post
     * @param string  $priority
     * @param string  $changefreq
     * @param bool    $include_images
     */
    private function render_sitemap_url_entry($post, $priority, $changefreq, $include_images) {
        echo "\t<url>\n";
        echo "\t\t<loc>" . esc_url(get_permalink($post->ID)) . "</loc>\n";
        echo "\t\t<lastmod>" . mysql2date('Y-m-d\TH:i:s\Z', $post->post_modified_gmt) . "</lastmod>\n";
        echo "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        echo "\t\t<priority>" . esc_html($priority) . "</priority>\n";

        if ($include_images) {
            $images = $this->get_post_images($post);
            foreach ($images as $img) {
                echo "\t\t<image:image>\n";
                echo "\t\t\t<image:loc>" . esc_url($img['loc']) . "</image:loc>\n";
                if (!empty($img['title'])) {
                    echo "\t\t\t<image:title>" . esc_xml($img['title']) . "</image:title>\n";
                }
                echo "\t\t</image:image>\n";
            }
        }

        echo "\t</url>\n";
    }

    /**
     * Extract all unique, valid image URLs from a post (featured image, post content, blocks, Elementor data).
     *
     * @param WP_Post $post
     * @return array Array of array('loc' => ..., 'title' => ...)
     */
    public function get_post_images($post) {
        if (!is_object($post) || empty($post->ID)) {
            return array();
        }

        $images = array();
        $seen_urls = array();
        $default_title = $post->post_title;
        $include_feat = get_option('gmb_sitemap_include_featured_images', '1') !== '0';

        // 1. Featured Image
        if ($include_feat && function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
            $thumb_id = get_post_thumbnail_id($post->ID);
            $thumb_url = get_the_post_thumbnail_url($post->ID, 'full');
            if ($thumb_url) {
                $thumb_title = get_the_title($thumb_id);
                if (empty($thumb_title) || is_numeric($thumb_title)) {
                    $thumb_title = $default_title;
                }
                $clean_url = $this->normalize_image_url($thumb_url);
                if ($clean_url && !isset($seen_urls[$clean_url])) {
                    $images[] = array(
                        'loc'   => $clean_url,
                        'title' => $thumb_title,
                    );
                    $seen_urls[$clean_url] = true;
                }
            }
        }

        // 2. Parse Raw Post Content (HTML, Gutenberg, shortcodes, lazy load data attributes)
        if (!empty($post->post_content)) {
            // Match standard src, data-src, data-lazy-src, data-orig-file, data-full-url
            if (preg_match_all('/<img[^>]+(?:src|data-src|data-lazy-src|data-orig-file|data-full-url)=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $img_tags, PREG_SET_ORDER)) {
                foreach ($img_tags as $tag_match) {
                    $raw_url = $tag_match[1];
                    $clean_url = $this->normalize_image_url($raw_url);
                    if (!$clean_url || isset($seen_urls[$clean_url])) {
                        continue;
                    }

                    // Extract alt or title if present
                    $img_title = $default_title;
                    if (preg_match('/alt=["\']([^"\']+)["\']/i', $tag_match[0], $alt_match) && !empty(trim($alt_match[1]))) {
                        $img_title = trim($alt_match[1]);
                    } elseif (preg_match('/title=["\']([^"\']+)["\']/i', $tag_match[0], $title_match) && !empty(trim($title_match[1]))) {
                        $img_title = trim($title_match[1]);
                    }

                    $images[] = array(
                        'loc'   => $clean_url,
                        'title' => $img_title,
                    );
                    $seen_urls[$clean_url] = true;
                }
            }

            // Match Gutenberg cover, image, gallery, or media-text block image URLs
            if (preg_match_all('/<!--\s*wp:(?:image|cover|media-text)[^>]*\{[^}]*"url"\s*:\s*"([^"]+)"/i', $post->post_content, $block_matches)) {
                foreach ($block_matches[1] as $block_img_url) {
                    $clean_url = $this->normalize_image_url(stripslashes($block_img_url));
                    if ($clean_url && !isset($seen_urls[$clean_url])) {
                        $images[] = array(
                            'loc'   => $clean_url,
                            'title' => $default_title,
                        );
                        $seen_urls[$clean_url] = true;
                    }
                }
            }
        }

        // 3. Parse Elementor Data (if page was built with Elementor)
        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
        if (!empty($elementor_data) && is_string($elementor_data)) {
            if (preg_match_all('/"(?:url|image_url)":\s*"([^"]+\.(?:jpg|jpeg|png|webp|gif|svg|avif)[^"]*)"/i', $elementor_data, $el_matches)) {
                foreach ($el_matches[1] as $el_url) {
                    $clean_url = $this->normalize_image_url(stripslashes(str_replace('\/', '/', $el_url)));
                    if ($clean_url && !isset($seen_urls[$clean_url])) {
                        $images[] = array(
                            'loc'   => $clean_url,
                            'title' => $default_title,
                        );
                        $seen_urls[$clean_url] = true;
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Normalize and validate an image URL.
     *
     * @param string $url
     * @return string|false
     */
    private function normalize_image_url($url) {
        if (empty($url) || !is_string($url)) {
            return false;
        }

        $url = trim($url);

        // Filter out data URIs or base64
        if (strpos($url, 'data:image') === 0 || strpos($url, 'blob:') === 0) {
            return false;
        }

        // Filter out inline SVGs or placeholder 1x1 gifs
        if (strpos($url, 'spacer.gif') !== false || strpos($url, 'blank.gif') !== false) {
            return false;
        }

        // Convert protocol-relative //example.com/img.jpg
        if (strpos($url, '//') === 0) {
            $url = (is_ssl() ? 'https:' : 'http:') . $url;
        }
        // Convert root-relative /wp-content/uploads/...
        elseif (strpos($url, '/') === 0) {
            $url = site_url($url);
        }

        // Must be a valid HTTP/HTTPS URL
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            return false;
        }

        return esc_url_raw($url);
    }

    /**
     * Generate taxonomy sitemap.
     *
     * @param string $taxonomy
     */
    private function generate_taxonomy_sitemap($taxonomy) {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(home_url('/gmb-sitemap.xsl')) . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $hide_empty = get_option('gmb_sitemap_empty_tax_' . $taxonomy, '0') !== '1';
        $term_args = array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => $hide_empty,
        );

        $excluded_terms_str = get_option('gmb_sitemap_excluded_terms', '');
        if (!empty($excluded_terms_str)) {
            $excluded_term_ids = array_filter(array_map('absint', explode(',', $excluded_terms_str)));
            if (!empty($excluded_term_ids)) {
                $term_args['exclude'] = $excluded_term_ids;
            }
        }

        $terms = get_terms($term_args);

        $exclude_slugs_str = get_option('gmb_ranker_sitemap_exclude_slugs', '');
        $excluded_slugs = array_filter(array_map('trim', explode(',', strtolower($exclude_slugs_str))));

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                if (!empty($excluded_slugs) && in_array(strtolower($term->slug), $excluded_slugs, true)) {
                    continue;
                }
                
                $term_link = get_term_link($term);
                if (is_wp_error($term_link)) {
                    continue;
                }

                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url($term_link) . "</loc>\n";
                echo "\t\t<changefreq>weekly</changefreq>\n";
                echo "\t\t<priority>0.6</priority>\n";
                echo "\t</url>\n";
            }
        }

        echo '</urlset>';
    }

    /**
     * Generate author archive sitemap.
     */
    private function generate_author_sitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(home_url('/gmb-sitemap.xsl')) . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $authors = get_users(array(
            'has_published_posts' => array('post', 'page'),
        ));

        if (!empty($authors)) {
            foreach ($authors as $author) {
                $author_url = get_author_posts_url($author->ID);
                if ($author_url) {
                    echo "\t<url>\n";
                    echo "\t\t<loc>" . esc_url($author_url) . "</loc>\n";
                    echo "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
                    echo "\t\t<changefreq>weekly</changefreq>\n";
                    echo "\t\t<priority>0.5</priority>\n";
                    echo "\t</url>\n";
                }
            }
        }

        echo '</urlset>';
    }

    /**
     * Generate custom URLs sitemap.
     */
    private function generate_custom_sitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(home_url('/gmb-sitemap.xsl')) . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $custom_urls_raw = get_option('gmb_sitemap_custom_urls', '');
        $lines = explode("\n", $custom_urls_raw);

        foreach ($lines as $line) {
            $u = trim($line);
            if (!empty($u) && strpos($u, 'http') === 0) {
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url($u) . "</loc>\n";
                echo "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
                echo "\t\t<changefreq>monthly</changefreq>\n";
                echo "\t\t<priority>0.5</priority>\n";
                echo "\t</url>\n";
            }
        }

        echo '</urlset>';
    }

    /**
     * Automatically ping search engines when a post is published or updated.
     *
     * @param string  $new_status
     * @param string  $old_status
     * @param WP_Post $post
     */
    public function maybe_ping_search_engines($new_status, $old_status, $post) {
        if ($new_status !== 'publish' || !is_object($post)) {
            return;
        }

        if (get_option('gmb_sitemap_ping_search_engines', '1') !== '1') {
            return;
        }

        // Avoid pinging on autosaves or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $sitemap_url = esc_url(home_url('/sitemap_index.xml'));

        // Ping Google
        wp_remote_get('https://www.google.com/ping?sitemap=' . urlencode($sitemap_url), array(
            'blocking' => false,
            'timeout'  => 5,
        ));

        // Ping Bing
        wp_remote_get('https://www.bing.com/ping?sitemap=' . urlencode($sitemap_url), array(
            'blocking' => false,
            'timeout'  => 5,
        ));
    }

    /**
     * Render user-facing HTML sitemap shortcode [gmb_html_sitemap].
     *
     * @param array $atts
     * @return string
     */
    public function render_html_sitemap_shortcode($atts) {
        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style(
                'gmb-ranker-seo-frontend',
                GMB_Ranker_SEO_Helpers::asset_url('css/frontend.css'),
                array(),
                '2.1.0'
            );
        }

        $sort_order = get_option('gmb_sitemap_html_sort', 'published');
        $orderby = ($sort_order === 'alphabetical') ? 'title' : 'date';
        $order = ($sort_order === 'alphabetical') ? 'ASC' : 'DESC';

        $output = '<div class="gmb-html-sitemap-container">';

        $public_post_types = get_post_types(array('public' => true), 'objects');
        $exclude_types = array('attachment');

        foreach ($public_post_types as $pt_name => $pt_obj) {
            if (in_array($pt_name, $exclude_types, true)) {
                continue;
            }

            $posts = get_posts(array(
                'post_type'      => $pt_name,
                'post_status'    => 'publish',
                'posts_per_page' => 100,
                'orderby'        => $orderby,
                'order'          => $order,
            ));

            if (!empty($posts)) {
                $section_title = is_object($pt_obj) && isset($pt_obj->labels->name) ? $pt_obj->labels->name : ucfirst($pt_name);
                $output .= '<div class="gmb-html-sitemap-section">';
                $output .= '<h3 class="gmb-html-sitemap-title">' . esc_html($section_title) . '</h3>';
                $output .= '<ul class="gmb-html-sitemap-list">';
                foreach ($posts as $p) {
                    $output .= '<li><a class="gmb-html-sitemap-link" href="' . esc_url(get_permalink($p->ID)) . '">' . esc_html($p->post_title) . '</a></li>';
                }
                $output .= '</ul>';
                $output .= '</div>';
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Generate the interactive XSL stylesheet for human browsers.
     */
    private function generate_sitemap_xsl() {
        header('Content-Type: text/xsl; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
<xsl:stylesheet version="2.0"
    xmlns:html="http://www.w3.org/TR/REC-html40"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title>XML Sitemap | GMB Ranker SEO</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style type="text/css">
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    color: #4b5563;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 0;
                }
                .banner {
                    background-color: #3b76f6;
                    color: #ffffff;
                    padding: 35px 40px;
                    margin-bottom: 30px;
                }
                .banner-inner {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                .banner h1 {
                    font-size: 28px;
                    color: #ffffff;
                    margin: 0 0 12px 0;
                    font-weight: 600;
                }
                .banner p {
                    font-size: 14px;
                    color: #ffffff;
                    margin: 0;
                    line-height: 1.6;
                    opacity: 0.95;
                }
                .banner a {
                    color: #ffffff;
                    text-decoration: underline;
                    font-weight: 500;
                }
                .content-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 20px 40px 20px;
                }
                .summary {
                    font-size: 13.5px;
                    color: #4b5563;
                    margin-bottom: 20px;
                    font-weight: 500;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    background-color: #ffffff;
                }
                th {
                    background-color: #3b76f6;
                    color: #ffffff;
                    font-weight: 600;
                    font-size: 13px;
                    text-align: left;
                    padding: 12px 16px;
                    border: none;
                }
                td {
                    padding: 12px 16px;
                    font-size: 13px;
                    color: #4b5563;
                    border-bottom: 1px solid #f3f4f6;
                    word-break: break-all;
                }
                tr:nth-child(even) td {
                    background-color: #f9fafb;
                }
                tr:hover td {
                    background-color: #f3f4f6;
                }
                td a {
                    color: #0066cc;
                    text-decoration: none;
                }
                td a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="banner">
                <div class="banner-inner">
                    <h1>XML Sitemap</h1>
                    <p>This XML Sitemap is generated dynamically by <a href="https://gmbranker.org/" target="_blank">GMB Ranker SEO</a>. It allows search engines like Google and Bing to crawl and re-crawl posts, pages, images, and taxonomy archives. Learn more about <a href="https://www.sitemaps.org/" target="_blank">XML Sitemaps</a>.</p>
                </div>
            </div>
            <div class="content-container">
                <xsl:if test="sitemap:sitemapindex">
                    <div class="summary">
                        This XML Sitemap Index contains <strong><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></strong> sub-sitemaps.
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Sitemap</th>
                                <th>Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                                <tr>
                                    <td>
                                        <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                                    </td>
                                    <td>
                                        <xsl:value-of select="sitemap:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:if>
                
                <xsl:if test="sitemap:urlset">
                    <div class="summary">
                        This XML Sitemap contains <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong> URLs.
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Images</th>
                                <th>Change Frequency</th>
                                <th>Priority</th>
                                <th>Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url">
                                <tr>
                                    <td>
                                        <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                                    </td>
                                    <td>
                                        <xsl:value-of select="count(image:image)"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="sitemap:changefreq"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="sitemap:priority"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="sitemap:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:if>
            </div>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
        <?php
    }
}
