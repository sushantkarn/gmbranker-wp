<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_REST_API {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function register_rest_routes() {
        register_rest_route('gmb-ranker/v1', '/handshake', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_handshake'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/snapshot', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_handshake'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/seo-data', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_seo_data'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/update-seo', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_update_seo'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/media', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'handle_media'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/redirects', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'handle_redirects'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/content-ai', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_content_ai'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/sitemap', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_sitemap'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));
        
        register_rest_route('gmb-ranker/v1', '/page-content', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_get_page_content'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/broken-links', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'handle_broken_links'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/inject-internal-link', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_inject_internal_link'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/automation/trigger', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_automation_trigger'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/capabilities', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_capabilities'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker/v1', '/automation/dispatch', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_automation_dispatch'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        // Also register under gmb-ranker-seo/v1 alias
        register_rest_route('gmb-ranker-seo/v1', '/capabilities', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_capabilities'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));

        register_rest_route('gmb-ranker-seo/v1', '/automation/dispatch', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_automation_dispatch'),
            'permission_callback' => array($this, 'authenticate_request'),
        ));
    }

    public function handle_automation_trigger($request) {
        $controller = new GMB_Ranker_SEO_REST_Automation_Controller();
        return $controller->handle_trigger($request);
    }

    public function authenticate_request($request) {
        $provided_key = $request->get_header('x-gmb-ranker-key');
        if (empty($provided_key)) {
            $provided_key = $request->get_header('X-GMB-Ranker-Key');
        }
        if (empty($provided_key)) {
            $headers = $request->get_headers();
            if (isset($headers['x_gmb_ranker_key']) && is_array($headers['x_gmb_ranker_key'])) {
                $provided_key = $headers['x_gmb_ranker_key'][0];
            }
        }
        if (empty($provided_key) && isset($_SERVER['HTTP_X_GMB_RANKER_KEY'])) {
            $provided_key = sanitize_text_field($_SERVER['HTTP_X_GMB_RANKER_KEY']);
        }

        $saved_key = get_option('gmb_ranker_api_key', '');
        if (empty($saved_key)) {
            $saved_key = get_option('gmb_ranker_secret', '');
        }

        if (!is_string($saved_key) || !is_string($provided_key) || empty($saved_key) || empty($provided_key)) {
            return false;
        }

        return hash_equals($saved_key, $provided_key);
    }

    public function handle_handshake() {
        return new WP_REST_Response(array('status' => 'connected'), 200);
    }

    public function handle_capabilities($request) {
        $allowed = get_post_types(array('public' => true), 'names');
        unset($allowed['attachment'], $allowed['revision'], $allowed['nav_menu_item'], $allowed['custom_css'], $allowed['customize_changeset']);
        
        $active_modules = array();
        if (get_option('gmb_ranker_module_metadata', '1') === '1') $active_modules[] = 'metadata';
        if (get_option('gmb_ranker_module_toc', '1') === '1') $active_modules[] = 'toc';
        if (get_option('gmb_ranker_module_schema', '1') === '1') $active_modules[] = 'schema';
        if (get_option('gmb_ranker_module_image_seo', '1') === '1') $active_modules[] = 'image_seo';
        if (get_option('gmb_ranker_module_links', '1') === '1') $active_modules[] = 'links';
        if (get_option('gmb_ranker_module_instant_indexing', '1') === '1') $active_modules[] = 'instant_indexing';
        if (get_option('gmb_ranker_module_sitemaps', '1') === '1') $active_modules[] = 'sitemaps';
        if (get_option('gmb_ranker_module_redirects', '1') === '1') $active_modules[] = 'redirects';
        if (get_option('gmb_ranker_module_security', '1') === '1') $active_modules[] = 'security';
        if (get_option('gmb_ranker_module_llmstxt', '1') === '1') $active_modules[] = 'llmstxt';
        if (get_option('gmb_ranker_module_woocommerce', '1') === '1') $active_modules[] = 'woocommerce';

        return new WP_REST_Response(array(
            'success'              => true,
            'plugin_version'       => defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.2',
            'wordpress_version'    => get_bloginfo('version'),
            'php_version'          => PHP_VERSION,
            'site_name'            => get_bloginfo('name'),
            'home_url'             => home_url(),
            'active_modules'       => $active_modules,
            'supported_post_types' => array_values($allowed),
            'supported_actions'    => array(
                'metadata.update',
                'metadata.apply_winner',
                'schema.sync',
                'schema.generate_faq',
                'links.inject',
                'images.optimize_alt',
                'toc.generate',
                'indexing.submit',
                'health.audit',
                'config.sync',
            ),
            'timestamp'            => time(),
        ), 200);
    }

    public function handle_automation_dispatch($request) {
        $action  = sanitize_text_field($request->get_param('action') ?: '');
        $payload = $request->get_param('payload') ?: array();
        if (!is_array($payload)) {
            $payload = array();
        }

        if (empty($action)) {
            return new WP_Error('missing_action', 'The action parameter is required.', array('status' => 400));
        }

        $start_time = microtime(true);
        $result = array();

        switch ($action) {
            case 'metadata.update':
            case 'metadata.apply_winner':
                $post_id = intval(isset($payload['post_id']) ? $payload['post_id'] : (isset($payload['wpPostId']) ? $payload['wpPostId'] : 0));
                if (!$post_id || !get_post($post_id)) {
                    return new WP_Error('invalid_post_id', 'Valid post_id is required.', array('status' => 400));
                }
                if (is_user_logged_in() && !current_user_can('edit_post', $post_id)) {
                    return new WP_Error('forbidden', 'You are not allowed to modify this post.', array('status' => 403));
                }
                if (isset($payload['seo_title'])) {
                    $title = sanitize_text_field($payload['seo_title']);
                    update_post_meta($post_id, '_gmb_ranker_seo_title', $title);
                    update_post_meta($post_id, '_yoast_wpseo_title', $title);
                    update_post_meta($post_id, 'rank_math_title', $title);
                    update_post_meta($post_id, '_rank_math_title', $title);
                    update_post_meta($post_id, '_aioseo_title', $title);
                    update_post_meta($post_id, '_seopress_titles_title', $title);
                }
                if (isset($payload['seo_description'])) {
                    $desc = sanitize_textarea_field($payload['seo_description']);
                    update_post_meta($post_id, '_gmb_ranker_seo_description', $desc);
                    update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
                    update_post_meta($post_id, 'rank_math_description', $desc);
                    update_post_meta($post_id, '_rank_math_description', $desc);
                    update_post_meta($post_id, '_aioseo_description', $desc);
                    update_post_meta($post_id, '_seopress_titles_desc', $desc);
                }
                if (isset($payload['focus_keyword'])) {
                    $kw = sanitize_text_field($payload['focus_keyword']);
                    update_post_meta($post_id, '_gmb_ranker_focus_keyword', $kw);
                    update_post_meta($post_id, '_yoast_wpseo_focuskw', $kw);
                    update_post_meta($post_id, 'rank_math_focus_keyword', $kw);
                    update_post_meta($post_id, '_rank_math_focus_keyword', $kw);
                    update_post_meta($post_id, '_aioseo_keywords', $kw);
                    update_post_meta($post_id, '_seopress_analysis_target_kw', $kw);
                }
                if (isset($payload['canonical_url'])) {
                    update_post_meta($post_id, '_gmb_ranker_canonical_url', esc_url_raw($payload['canonical_url']));
                    update_post_meta($post_id, '_yoast_wpseo_canonical', esc_url_raw($payload['canonical_url']));
                    update_post_meta($post_id, 'rank_math_canonical_url', esc_url_raw($payload['canonical_url']));
                }
                if (isset($payload['robots']) && is_array($payload['robots'])) {
                    update_post_meta($post_id, '_gmb_ranker_robots', array_map('sanitize_text_field', $payload['robots']));
                }

                // Recalculate from the canonical evidence-based analyzer.
                $audit = class_exists('GMB_Ranker_SEO_Analysis_Service')
                    ? (new GMB_Ranker_SEO_Analysis_Service())->audit_post($post_id)
                    : array('score' => null, 'results' => array(), 'error' => 'analysis_unavailable');

                // If instant indexing requested
                if (!empty($payload['trigger_instant_indexing'])) {
                    $permalink = get_permalink($post_id);
                    if ($permalink) {
                        do_action('gmb_ranker_instant_index_urls', array($permalink));
                    }
                }

                $result = array(
                    'post_id'     => $post_id,
                    'score_after' => isset($audit['score']) ? $audit['score'] : null,
                    'results'     => isset($audit['results']) ? $audit['results'] : array(),
                );
                break;

            case 'schema.sync':
                if (isset($payload['local_seo']) && is_array($payload['local_seo'])) {
                    $lseo = $payload['local_seo'];
                    if (!empty($lseo['category'])) update_option('gmb_seo_local_business_type', sanitize_text_field($lseo['category']));
                    if (!empty($lseo['streetAddress'])) update_option('gmb_seo_local_address_street', sanitize_text_field($lseo['streetAddress']));
                    if (!empty($lseo['locality'])) update_option('gmb_seo_local_address_locality', sanitize_text_field($lseo['locality']));
                    if (!empty($lseo['phone'])) update_option('gmb_seo_local_phone', sanitize_text_field($lseo['phone']));
                }
                $result = array('schema_synced' => true);
                break;

            case 'links.inject':
                $post_id = intval(isset($payload['pageId']) ? $payload['pageId'] : (isset($payload['post_id']) ? $payload['post_id'] : 0));
                $modified = isset($payload['modifiedContent']) ? $payload['modifiedContent'] : (isset($payload['content']) ? $payload['content'] : '');
                
                if (!$post_id || empty($modified)) {
                    return new WP_Error('invalid_payload', 'pageId and modifiedContent are required.', array('status' => 400));
                }

                $post = get_post($post_id);
                if (!$post) {
                    return new WP_Error('post_not_found', 'Target post not found.', array('status' => 404));
                }
                if (is_user_logged_in() && !current_user_can('edit_post', $post_id)) {
                    return new WP_Error('forbidden', 'You are not allowed to modify this post.', array('status' => 403));
                }

                $rev_id = wp_update_post(array(
                    'ID'           => $post_id,
                    'post_content' => $modified,
                ));

                $result = array(
                    'post_id'     => $post_id,
                    'revision_id' => $rev_id,
                    'applied'     => ($rev_id > 0),
                );
                break;

            case 'indexing.submit':
                $urls = isset($payload['urls']) && is_array($payload['urls']) ? $payload['urls'] : array();
                if (empty($urls)) {
                    return new WP_Error('empty_urls', 'urls array is required.', array('status' => 400));
                }

                do_action('gmb_ranker_instant_index_urls', $urls);

                $result = array(
                    'submitted_count' => count($urls),
                    'urls'            => $urls,
                );
                break;

            case 'health.audit':
                $posts_count = wp_count_posts('post');
                $pages_count = wp_count_posts('page');
                $total_posts = isset($posts_count->publish) ? $posts_count->publish : 0;
                $total_pages = isset($pages_count->publish) ? $pages_count->publish : 0;
                $result = array(
                    'total_published_posts' => $total_posts,
                    'total_published_pages' => $total_pages,
                    'site_url'              => home_url(),
                );
                break;

            default:
                return new WP_Error('unsupported_action', 'The action "' . esc_html($action) . '" is not supported.', array('status' => 400));
        }

        $execution_time_ms = round((microtime(true) - $start_time) * 1000, 2);

        return new WP_REST_Response(array(
            'success'           => true,
            'action'            => $action,
            'data'              => $result,
            'execution_time_ms' => $execution_time_ms,
            'timestamp'         => time(),
        ), 200);
    }

    public function handle_get_page_content($request) {
        $id = intval($request->get_param('id'));
        if (empty($id)) {
            return new WP_Error('missing_id', 'Missing required post ID', array('status' => 400));
        }
        $post = get_post($id);
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }
        return new WP_REST_Response(array(
            'id' => $post->ID,
            'content' => $post->post_content,
        ), 200);
    }

    public function handle_get_seo_data($request) {
        $page = intval($request->get_param('page')) ?: 1;
        $per_page = intval($request->get_param('per_page')) ?: 50;

        $requested_type = $request->get_param('post_type');
        if (!empty($requested_type) && $requested_type !== 'all') {
            $post_types = array_map('trim', explode(',', $requested_type));
        } else {
            $allowed = get_post_types(array('public' => true), 'names');
            unset($allowed['attachment'], $allowed['revision'], $allowed['nav_menu_item'], $allowed['custom_css'], $allowed['customize_changeset'], $allowed['oembed_cache'], $allowed['user_request'], $allowed['wp_block'], $allowed['wp_template'], $allowed['wp_template_part'], $allowed['wp_global_styles'], $allowed['wp_navigation']);
            $post_types = !empty($allowed) ? array_values($allowed) : array('post', 'page', 'product');
        }

        $args = array(
            'post_type'      => $post_types,
            'post_status'    => $request->get_param('status') ?: 'any',
            'posts_per_page' => $per_page,
            'paged'          => $page,
        );

        $search_query = $request->get_param('s');
        if (!empty($search_query)) {
            $args['s'] = sanitize_text_field($search_query);
        }

        $query = new WP_Query($args);
        $result = array();
        $site_host = wp_parse_url(home_url(), PHP_URL_HOST);

        foreach ($query->posts as $post) {
            // 1. Meta Title (GMB -> Yoast -> RankMath -> AIOSEO -> SEOPress -> Post Title)
            $meta_title = get_post_meta($post->ID, '_gmb_ranker_seo_title', true);
            if (empty($meta_title)) {
                $meta_title = get_post_meta($post->ID, '_yoast_wpseo_title', true) 
                    ?: (get_post_meta($post->ID, 'rank_math_title', true) 
                    ?: (get_post_meta($post->ID, '_rank_math_title', true) 
                    ?: (get_post_meta($post->ID, '_aioseo_title', true) 
                    ?: (get_post_meta($post->ID, '_seopress_titles_title', true) 
                    ?: $post->post_title))));
            }

            // 2. Meta Description (GMB -> Yoast -> RankMath -> AIOSEO -> SEOPress -> Excerpt -> Trimmed Content)
            $meta_desc = get_post_meta($post->ID, '_gmb_ranker_seo_description', true);
            if (empty($meta_desc)) {
                $meta_desc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true) 
                    ?: (get_post_meta($post->ID, 'rank_math_description', true) 
                    ?: (get_post_meta($post->ID, '_rank_math_description', true) 
                    ?: (get_post_meta($post->ID, '_aioseo_description', true) 
                    ?: (get_post_meta($post->ID, '_seopress_titles_desc', true) 
                    ?: ($post->post_excerpt ?: wp_trim_words($post->post_content, 30))))));
            }

            // 3. Focus Keyword (GMB -> Yoast -> RankMath -> AIOSEO -> SEOPress)
            $focus_keyword = get_post_meta($post->ID, '_gmb_ranker_focus_keyword', true);
            if (empty($focus_keyword)) {
                $focus_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true) 
                    ?: (get_post_meta($post->ID, 'rank_math_focus_keyword', true) 
                    ?: (get_post_meta($post->ID, '_rank_math_focus_keyword', true) 
                    ?: (get_post_meta($post->ID, '_aioseo_keywords', true) 
                    ?: get_post_meta($post->ID, '_seopress_analysis_target_kw', true))));
            }

            // 4. Canonical URL (GMB -> Yoast -> RankMath -> AIOSEO -> SEOPress -> Permalink)
            $canonical = get_post_meta($post->ID, '_gmb_ranker_seo_canonical', true);
            if (empty($canonical)) {
                $canonical = get_post_meta($post->ID, '_yoast_wpseo_canonical', true) 
                    ?: (get_post_meta($post->ID, 'rank_math_canonical_url', true) 
                    ?: (get_post_meta($post->ID, '_rank_math_canonical', true) 
                    ?: (get_post_meta($post->ID, '_aioseo_canonical_url', true) 
                    ?: (get_post_meta($post->ID, '_seopress_titles_canonical', true) 
                    ?: get_permalink($post->ID)))));
            }

            // 5. Robots Meta
            $robots = get_post_meta($post->ID, '_gmb_ranker_seo_robots', true);
            if (empty($robots)) {
                $rm_robots = get_post_meta($post->ID, 'rank_math_robots', true);
                if (!empty($rm_robots)) {
                    $robots = is_array($rm_robots) ? implode(', ', $rm_robots) : $rm_robots;
                } elseif (get_post_meta($post->ID, '_yoast_wpseo_meta-robots-noindex', true) === '1') {
                    $robots = 'noindex, follow';
                } else {
                    $robots = 'index, follow';
                }
            }

            // 6. Social Open Graph & Twitter
            $og_title = get_post_meta($post->ID, '_gmb_ranker_facebook_title', true) 
                ?: (get_post_meta($post->ID, '_yoast_wpseo_opengraph-title', true) 
                ?: (get_post_meta($post->ID, 'rank_math_facebook_title', true) 
                ?: $meta_title));

            $og_desc = get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true) 
                ?: (get_post_meta($post->ID, '_yoast_wpseo_opengraph-description', true) 
                ?: (get_post_meta($post->ID, 'rank_math_facebook_description', true) 
                ?: $meta_desc));

            $og_image = get_post_meta($post->ID, '_gmb_ranker_facebook_image', true) 
                ?: (get_post_meta($post->ID, '_yoast_wpseo_opengraph-image', true) 
                ?: (get_post_meta($post->ID, 'rank_math_facebook_image', true) 
                ?: (has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '')));

            $schema_json = get_post_meta($post->ID, '_gmb_ranker_json_ld', true);
            $featured_image = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'full') : '';
            $author_name = get_the_author_meta('display_name', $post->post_author);

            // 7. WooCommerce Details if Product
            $wc_price = '';
            $wc_stock = '';
            if ($post->post_type === 'product') {
                $wc_price = get_post_meta($post->ID, '_price', true) ?: '';
                $wc_stock = get_post_meta($post->ID, '_stock_status', true) ?: '';
            }

            // 8. Elementor Detection
            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
            $is_elementor = !empty($elementor_data) && get_post_meta($post->ID, '_elementor_edit_mode', true) === 'builder';

            // 9. Existing In-Content Link Graph Extraction
            $existing_links = array();
            if (preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $post->post_content, $link_matches, PREG_SET_ORDER)) {
                foreach ($link_matches as $lm) {
                    $href = $lm[1];
                    $anchor = trim(wp_strip_all_tags($lm[2]));
                    $link_host = wp_parse_url($href, PHP_URL_HOST);
                    $is_internal = empty($link_host) || ($link_host === $site_host);
                    $existing_links[] = array(
                        'url'        => $href,
                        'text'       => $anchor,
                        'isInternal' => $is_internal,
                    );
                }
            }

            // 10. Taxonomies Extraction (Categories, Tags, Custom Taxonomies)
            $taxonomies = array();
            $tax_objects = get_object_taxonomies($post->post_type, 'objects');
            $tax_names_list = array();
            foreach ($tax_objects as $tax_obj) {
                $tax_terms = wp_get_post_terms($post->ID, $tax_obj->name, array('fields' => 'names'));
                if (!is_wp_error($tax_terms) && !empty($tax_terms)) {
                    $taxonomies[$tax_obj->name] = $tax_terms;
                    $tax_names_list = array_merge($tax_names_list, $tax_terms);
                }
            }
            $categories = isset($taxonomies['category']) ? $taxonomies['category'] : (!empty($tax_names_list) ? array_slice($tax_names_list, 0, 5) : array());

            // 11. Word Count & Page Hierarchy Analysis
            $word_count = str_word_count(wp_strip_all_tags($post->post_content));
            $is_pillar = preg_match('/guide|pillar|overview|handbook|manual|tutorial|complete-guide/i', $post->post_title . ' ' . $post->post_name);
            $page_type = 'Supporting Article';
            if ($post->post_type === 'product') {
                $page_type = 'Product Page';
            } elseif ($is_pillar || $word_count > 1500) {
                $page_type = 'Pillar Guide';
            }

            $semantic_context = !empty($focus_keyword) ? $focus_keyword : implode(', ', $categories);

            // 12. On-Page SEO Audit Score
            $audit = class_exists('GMB_Ranker_SEO_Analysis_Service')
                ? (new GMB_Ranker_SEO_Analysis_Service())->audit_post($post->ID)
                : array('score' => null, 'results' => array());
            $onpage_score = isset($audit['score']) ? $audit['score'] : null;
            $onpage_results = isset($audit['results']) ? $audit['results'] : array();

            $result[] = array(
                'wpPostId'         => $post->ID,
                'postType'         => $post->post_type,
                'title'            => $post->post_title,
                'slug'             => $post->post_name,
                'url'              => get_permalink($post->ID),
                'metaTitle'        => $meta_title,
                'metaDescription'  => $meta_desc,
                'focusKeyword'     => $focus_keyword,
                'canonical'        => $canonical,
                'status'           => $post->post_status,
                'content'          => $post->post_content,
                'featuredImage'    => $featured_image,
                'author'           => $author_name,
                'datePublished'    => get_the_date('c', $post->ID),
                'dateModified'     => get_the_modified_date('c', $post->ID),
                'isElementor'      => $is_elementor,
                'wcPrice'          => $wc_price,
                'wcStock'          => $wc_stock,
                'onpageSeoScore'   => $onpage_score,
                'onpageSeoResults' => $onpage_results,
                'ogTitle'          => $og_title,
                'ogDescription'    => $og_desc,
                'ogImage'          => $og_image,
                'jsonLdSchema'     => $schema_json,
                'taxonomies'       => array(
                    'categories' => $categories,
                    'all'        => $taxonomies,
                    'metadata'   => array(
                        'pageType'        => $page_type,
                        'existingLinks'   => $existing_links,
                        'semanticContext' => $semantic_context,
                        'robots'          => $robots,
                    ),
                ),
            );
        }

        $response = new WP_REST_Response($result, 200);
        $response->header('X-WP-Total', (int) $query->found_posts);
        $response->header('X-WP-TotalPages', (int) $query->max_num_pages);
        return $response;
    }

    public function handle_update_seo($request) {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = $request->get_params();
        }
        if (!is_array($params) || empty($params['wpPostId'])) {
            return new WP_Error('invalid_params', 'Missing required post ID.', array('status' => 400));
        }

        $wp_post_id = intval($params['wpPostId']);
        if (!get_post($wp_post_id)) {
            return new WP_Error('invalid_post', 'Post not found', array('status' => 404));
        }
        if (is_user_logged_in() && !current_user_can('edit_post', $wp_post_id)) {
            return new WP_Error('forbidden', 'You are not allowed to modify this post.', array('status' => 403));
        }

        // 1. Meta Title Synchronization across all SEO plugins
        if (isset($params['metaTitle'])) {
            $title = sanitize_text_field($params['metaTitle']);
            update_post_meta($wp_post_id, '_gmb_ranker_seo_title', $title);
            update_post_meta($wp_post_id, '_yoast_wpseo_title', $title);
            update_post_meta($wp_post_id, 'rank_math_title', $title);
            update_post_meta($wp_post_id, '_rank_math_title', $title);
            update_post_meta($wp_post_id, '_aioseo_title', $title);
            update_post_meta($wp_post_id, '_seopress_titles_title', $title);
        }

        // 2. Meta Description Synchronization
        if (isset($params['metaDescription'])) {
            $desc = sanitize_textarea_field($params['metaDescription']);
            update_post_meta($wp_post_id, '_gmb_ranker_seo_description', $desc);
            update_post_meta($wp_post_id, '_yoast_wpseo_metadesc', $desc);
            update_post_meta($wp_post_id, 'rank_math_description', $desc);
            update_post_meta($wp_post_id, '_rank_math_description', $desc);
            update_post_meta($wp_post_id, '_aioseo_description', $desc);
            update_post_meta($wp_post_id, '_seopress_titles_desc', $desc);
        }

        // 3. Focus Keyword Synchronization
        if (isset($params['focusKeyword'])) {
            $kw = sanitize_text_field($params['focusKeyword']);
            update_post_meta($wp_post_id, '_gmb_ranker_focus_keyword', $kw);
            update_post_meta($wp_post_id, '_yoast_wpseo_focuskw', $kw);
            update_post_meta($wp_post_id, 'rank_math_focus_keyword', $kw);
            update_post_meta($wp_post_id, '_rank_math_focus_keyword', $kw);
            update_post_meta($wp_post_id, '_aioseo_keywords', $kw);
            update_post_meta($wp_post_id, '_seopress_analysis_target_kw', $kw);
        }

        // 4. Canonical URL Synchronization
        if (isset($params['canonical'])) {
            $canonical = esc_url_raw($params['canonical']);
            update_post_meta($wp_post_id, '_gmb_ranker_seo_canonical', $canonical);
            update_post_meta($wp_post_id, '_yoast_wpseo_canonical', $canonical);
            update_post_meta($wp_post_id, 'rank_math_canonical_url', $canonical);
            update_post_meta($wp_post_id, '_rank_math_canonical', $canonical);
            update_post_meta($wp_post_id, '_aioseo_canonical_url', $canonical);
            update_post_meta($wp_post_id, '_seopress_titles_canonical', $canonical);
        }

        // 5. Robots Meta Synchronization
        if (isset($params['robots'])) {
            $robots = sanitize_text_field($params['robots']);
            update_post_meta($wp_post_id, '_gmb_ranker_seo_robots', $robots);
            update_post_meta($wp_post_id, 'rank_math_robots', array_filter(array_map('trim', explode(',', $robots))));
            if (strpos($robots, 'noindex') !== false) {
                update_post_meta($wp_post_id, '_yoast_wpseo_meta-robots-noindex', '1');
            } else {
                delete_post_meta($wp_post_id, '_yoast_wpseo_meta-robots-noindex');
            }
        }

        // 6. Social OpenGraph & Twitter Cards Synchronization
        if (isset($params['ogTitle'])) {
            $og_title = sanitize_text_field($params['ogTitle']);
            update_post_meta($wp_post_id, '_gmb_ranker_facebook_title', $og_title);
            update_post_meta($wp_post_id, '_gmb_ranker_twitter_title', $og_title);
            update_post_meta($wp_post_id, '_yoast_wpseo_opengraph-title', $og_title);
            update_post_meta($wp_post_id, '_yoast_wpseo_twitter-title', $og_title);
            update_post_meta($wp_post_id, 'rank_math_facebook_title', $og_title);
            update_post_meta($wp_post_id, 'rank_math_twitter_title', $og_title);
        }

        if (isset($params['ogDescription'])) {
            $og_desc = sanitize_textarea_field($params['ogDescription']);
            update_post_meta($wp_post_id, '_gmb_ranker_facebook_desc', $og_desc);
            update_post_meta($wp_post_id, '_gmb_ranker_twitter_desc', $og_desc);
            update_post_meta($wp_post_id, '_yoast_wpseo_opengraph-description', $og_desc);
            update_post_meta($wp_post_id, '_yoast_wpseo_twitter-description', $og_desc);
            update_post_meta($wp_post_id, 'rank_math_facebook_description', $og_desc);
            update_post_meta($wp_post_id, 'rank_math_twitter_description', $og_desc);
        }

        if (isset($params['ogImage'])) {
            $og_img = esc_url_raw($params['ogImage']);
            update_post_meta($wp_post_id, '_gmb_ranker_facebook_image', $og_img);
            update_post_meta($wp_post_id, '_gmb_ranker_twitter_image', $og_img);
            update_post_meta($wp_post_id, '_yoast_wpseo_opengraph-image', $og_img);
            update_post_meta($wp_post_id, '_yoast_wpseo_twitter-image', $og_img);
            update_post_meta($wp_post_id, 'rank_math_facebook_image', $og_img);
            update_post_meta($wp_post_id, 'rank_math_twitter_image', $og_img);
        }

        // 7. Schema JSON-LD Synchronization
        if (isset($params['jsonLdSchema'])) {
            $schema_val = is_array($params['jsonLdSchema']) ? wp_json_encode($params['jsonLdSchema'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $params['jsonLdSchema'];
            update_post_meta($wp_post_id, '_gmb_ranker_json_ld', $schema_val);
        }

        // 8. Content & Elementor Synchronization
        if (isset($params['content'])) {
            $updated_content = $params['content'];
            $updated_post = array(
                'ID'           => $wp_post_id,
                'post_content' => $updated_content,
            );
            wp_update_post($updated_post);

            $elementor_data_json = get_post_meta($wp_post_id, '_elementor_data', true);
            if (!empty($elementor_data_json)) {
                $elementor_data = json_decode($elementor_data_json, true);
                if (is_array($elementor_data)) {
                    preg_match_all('/(.{0,30})(<a\s+[^>]*data-gmb-link="injected"[^>]*>.*?<\/a>)(.{0,30})/is', $updated_content, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        $before = $match[1];
                        $link_html = $match[2];
                        $after = $match[3];
                        
                        preg_match('/<a[^>]*>(.*?)<\/a>/is', $link_html, $anchor_match);
                        $anchor_text = isset($anchor_match[1]) ? $anchor_match[1] : '';
                        
                        if (!empty($anchor_text)) {
                            $search_plain = wp_strip_all_tags($before . $anchor_text . $after);
                            $replace_html = $before . $link_html . $after;
                            $this->replace_text_in_elementor_data($elementor_data, $search_plain, $replace_html);
                        }
                    }
                    update_post_meta($wp_post_id, '_elementor_data', wp_slash(wp_json_encode($elementor_data)));
                }
            }
        }

        if (isset($params['llmsTxtContent'])) {
            $llms_content = sanitize_textarea_field($params['llmsTxtContent']);
            update_option('gmb_llms_additional_content', $llms_content);
        }

        $audit = class_exists('GMB_Ranker_SEO_Analysis_Service')
            ? (new GMB_Ranker_SEO_Analysis_Service())->audit_post($wp_post_id)
            : array('score' => null, 'results' => array());

        return new WP_REST_Response(array(
            'success' => true,
            'score'   => isset($audit['score']) ? $audit['score'] : null,
            'audit'   => $audit,
        ), 200);
    }

    private function replace_text_in_elementor_data(&$elements, $search, $replace) {
        if (!is_array($elements)) return;
        foreach ($elements as $key => &$element) {
            if (is_array($element)) {
                $this->replace_text_in_elementor_data($element, $search, $replace);
            } elseif (is_string($element)) {
                $element_norm = preg_replace('/\s+/', ' ', $element);
                $search_norm = preg_replace('/\s+/', ' ', $search);
                if (strpos($element_norm, $search_norm) !== false) {
                    if (strpos($element, $search) !== false) {
                        $element = str_replace($search, $replace, $element);
                    } else {
                        preg_match('/<a[^>]*data-gmb-link="injected"[^>]*>.*?<\/a>/is', $replace, $link_match);
                        if (!empty($link_match[0])) {
                            preg_match('/<a[^>]*>(.*?)<\/a>/is', $link_match[0], $anchor_match);
                            $anchor = isset($anchor_match[1]) ? $anchor_match[1] : '';
                            if (!empty($anchor) && strpos($element, $anchor) !== false) {
                                $pos = strpos($element, $anchor);
                                $element = substr_replace($element, $link_match[0], $pos, strlen($anchor));
                            }
                        }
                    }
                }
            }
        }
    }

    public function handle_media($request) {
        if ($request->get_method() === 'GET') {
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => 20,
            );
            $query = new WP_Query($args);
            $result = array();
            foreach ($query->posts as $attachment) {
                $alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                if (empty($alt)) {
                    $result[] = array(
                        'wpMediaId' => $attachment->ID,
                        'imageUrl' => wp_get_attachment_url($attachment->ID),
                        'title' => $attachment->post_title,
                    );
                }
            }
            return new WP_REST_Response($result, 200);
        } else {
            $params = $request->get_json_params();
            if (!is_array($params)) {
                $params = $request->get_params();
            }
            $media_id = (is_array($params) && isset($params['wpMediaId'])) ? intval($params['wpMediaId']) : 0;
            $alt_text = (is_array($params) && isset($params['altText'])) ? sanitize_text_field($params['altText']) : '';
            if ($media_id > 0) {
                update_post_meta($media_id, '_wp_attachment_image_alt', $alt_text);
            }
            return new WP_REST_Response(array('success' => true), 200);
        }
    }

    public function handle_redirects($request) {
        if ($request->get_method() === 'GET') {
            $logs = get_option('gmb_ranker_404_logs', array());
            if (!is_array($logs)) {
                $logs = array();
            }
            $aggregated = array();
            foreach ($logs as $entry) {
                if (isset($entry['uri'])) {
                    $uri = $entry['uri'];
                    if (!isset($aggregated[$uri])) {
                        $aggregated[$uri] = 0;
                    }
                    $aggregated[$uri]++;
                }
            }
            return new WP_REST_Response($aggregated, 200);
        } else {
            $params = $request->get_json_params();
            if (!is_array($params)) {
                $params = $request->get_params();
            }
            if (is_array($params) && isset($params['redirects']) && is_array($params['redirects'])) {
                update_option('gmb_ranker_redirects', $params['redirects']);
                
                $logs = get_option('gmb_ranker_404_logs', array());
                if (is_array($logs)) {
                    $redirected_sources = array_keys($params['redirects']);
                    $filtered_logs = array();
                    foreach ($logs as $log) {
                        if (isset($log['uri']) && in_array($log['uri'], $redirected_sources)) {
                            continue;
                        }
                        $filtered_logs[] = $log;
                    }
                    update_option('gmb_ranker_404_logs', $filtered_logs);
                }
            }
            return new WP_REST_Response(array('success' => true), 200);
        }
    }

    public function handle_content_ai($request) {
        $params = $request->get_json_params();
        $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
        $content = isset($params['content']) ? wp_strip_all_tags($params['content']) : '';

        $prompt = "You are a professional SEO auditor. Analyze the following webpage title and body content. Provide 3 highly specific, bulleted actionable SEO optimization recommendations to improve organic search visibility. Format the response strictly as a JSON array of strings: [\"suggestion 1\", \"suggestion 2\", \"suggestion 3\"]. Do not output markdown, preambles, or explanations outside the JSON array.\n\nTitle: $title\nContent:\n$content";

        $messages = array(
            array('role' => 'user', 'content' => $prompt)
        );

        $response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages);
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success'     => false,
                'suggestions'  => array(),
                'message'     => $response->get_error_message(),
            ), 503);
        } else {
            $clean = preg_replace('/```json|```/i', '', $response);
            $suggestions = json_decode(trim($clean), true);
            if (!is_array($suggestions)) {
                $suggestions = array_filter(array_map('trim', explode("\n", wp_strip_all_tags($response))));
            }
        }
        
        $final_suggestions = is_array($suggestions) ? array_slice($suggestions, 0, 3) : array();
        return new WP_REST_Response(array('suggestions' => array_values($final_suggestions)), 200);
    }

    public function handle_sitemap($request) {
        $params = $request->get_json_params();
        if (isset($params['sitemapXml'])) {
            $xml = $params['sitemapXml'];
            update_option('gmb_ranker_sitemap_custom_xml', $xml);
            wp_remote_get('https://www.bing.com/ping?sitemap=' . urlencode(site_url('sitemap_index.xml')));
            return new WP_REST_Response(array('success' => true, 'url' => site_url('sitemap_index.xml')), 200);
        }
        return new WP_REST_Response(array('success' => true, 'url' => site_url('sitemap_index.xml')), 200);
    }

    public function handle_broken_links($request) {
        if ($request->get_method() === 'GET') {
            $posts = get_posts(array(
                'post_type' => array('post', 'page', 'product'),
                'post_status' => 'publish',
                'posts_per_page' => 20,
            ));
            
            $broken_links = array();
            foreach ($posts as $post) {
                preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/is', $post->post_content, $matches);
                if (!empty($matches[1])) {
                    foreach (array_unique($matches[1]) as $url) {
                        if (strpos($url, home_url()) !== false || strpos($url, '/') === 0 || strpos($url, '#') === 0) {
                            continue;
                        }
                        
                        $response = wp_remote_head($url, array('timeout' => 2, 'sslverify' => false));
                        $code = is_wp_error($response) ? 500 : wp_remote_retrieve_response_code($response);
                        
                        if ($code >= 400 || $code === 0) {
                            $broken_links[] = array(
                                'postId' => $post->ID,
                                'postTitle' => $post->post_title,
                                'url' => $url,
                                'status' => $code,
                            );
                        }
                    }
                }
            }
            return new WP_REST_Response($broken_links, 200);
        } else {
            $params = $request->get_json_params();
            $post_id = intval($params['postId']);
            $broken_url = sanitize_text_field($params['brokenUrl']);
            $action = sanitize_text_field($params['action']);
            
            $post = get_post($post_id);
            if (!$post) {
                return new WP_Error('not_found', 'Post not found', array('status' => 404));
            }
            
            $content = $post->post_content;
            if ($action === 'unlink') {
                $pattern = '/<a\s+[^>]*href=["\']' . preg_quote($broken_url, '/') . '["\'][^>]*>(.*?)<\/a>/is';
                $content = preg_replace($pattern, '$1', $content);
            } elseif ($action === 'replace') {
                $replacement_url = esc_url_raw($params['replacementUrl']);
                $pattern = '/href=["\']' . preg_quote($broken_url, '/') . '["\']/is';
                $content = preg_replace($pattern, 'href="' . $replacement_url . '"', $content);
            }
            
            wp_update_post(array('ID' => $post_id, 'post_content' => $content));
            return new WP_REST_Response(array('success' => true), 200);
        }
    }

    public function handle_inject_internal_link($request) {
        $params = $request->get_json_params();
        $post_id = intval($params['postId']);
        $anchor = sanitize_text_field($params['anchorText']);
        $target_url = esc_url_raw($params['targetUrl']);
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', array('status' => 404));
        }
        
        $elementor_data_json = get_post_meta($post_id, '_elementor_data', true);
        $injected = false;
        
        if (!empty($elementor_data_json)) {
            $elementor_data = json_decode($elementor_data_json, true);
            if (is_array($elementor_data)) {
                $this->inject_link_in_elementor_data($elementor_data, $anchor, $target_url, $injected);
                if ($injected) {
                    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($elementor_data)));
                    
                    // Also update post_content as fallback so sitemaps and raw queries see it
                    $content = $post->post_content;
                    $new_content = $this->inject_link_in_html($content, $anchor, $target_url);
                    wp_update_post(array('ID' => $post_id, 'post_content' => $new_content));
                    
                    return new WP_REST_Response(array('success' => true, 'injected' => true), 200);
                }
            }
        }
        
        $content = $post->post_content;
        $new_content = $this->inject_link_in_html($content, $anchor, $target_url);
        
        if ($new_content !== $content) {
            wp_update_post(array('ID' => $post_id, 'post_content' => $new_content));
            return new WP_REST_Response(array('success' => true, 'injected' => true), 200);
        }
        
        return new WP_REST_Response(array('success' => false, 'message' => 'Anchor text not found in post content.'), 200);
    }

    private function inject_link_in_html($html, $anchor, $url) {
        if (empty($html) || empty($anchor) || empty($url)) {
            return $html;
        }

        $anchor_escaped = preg_quote($anchor, '/');
        $pattern = '/(?<!\p{L})(' . $anchor_escaped . ')(?!\p{L})/iu';

        // Split HTML into HTML tags and plain text chunks
        $chunks = preg_split('/(<[^>]+>)/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($chunks) || empty($chunks)) {
            return $html;
        }

        $in_a_tag = 0;
        $in_script_tag = false;
        $in_style_tag = false;
        $in_comment = false;
        $replaced = false;

        foreach ($chunks as &$chunk) {
            if ($chunk === '') continue;

            // If it is a tag or comment
            if ($chunk[0] === '<') {
                $tag_lower = strtolower($chunk);
                
                if (strpos($tag_lower, '<!--') === 0) {
                    if (strpos($tag_lower, '-->') === false) {
                        $in_comment = true;
                    }
                    continue;
                }
                if ($in_comment) {
                    if (strpos($tag_lower, '-->') !== false) {
                        $in_comment = false;
                    }
                    continue;
                }

                if (preg_match('/^<a[\s>]/i', $chunk)) {
                    $in_a_tag++;
                } elseif (preg_match('/^<\/a[\s>]/i', $chunk)) {
                    $in_a_tag = max(0, $in_a_tag - 1);
                } elseif (preg_match('/^<script[\s>]/i', $chunk)) {
                    $in_script_tag = true;
                } elseif (preg_match('/^<\/script[\s>]/i', $chunk)) {
                    $in_script_tag = false;
                } elseif (preg_match('/^<style[\s>]/i', $chunk)) {
                    $in_style_tag = true;
                } elseif (preg_match('/^<\/style[\s>]/i', $chunk)) {
                    $in_style_tag = false;
                }
                continue;
            }

            // If it is plain text outside anchor tags, scripts, and comments
            if (!$replaced && $in_a_tag === 0 && !$in_script_tag && !$in_style_tag && !$in_comment) {
                if (preg_match($pattern, $chunk)) {
                    $replacement = '<a href="' . esc_url($url) . '" data-gmb-link="injected">$1</a>';
                    $chunk = preg_replace($pattern, $replacement, $chunk, 1);
                    $replaced = true;
                }
            }
        }

        if ($replaced) {
            return implode('', $chunks);
        }

        return $html;
    }


    private function inject_link_in_elementor_data(&$elements, $anchor, $url, &$injected) {
        if (!is_array($elements)) return;
        foreach ($elements as $key => &$element) {
            if ($injected) {
                return;
            }
            if (is_array($element)) {
                $this->inject_link_in_elementor_data($element, $anchor, $url, $injected);
            } elseif (is_string($element)) {
                $anchor_escaped = preg_quote($anchor, '/');
                $pattern = '/(?<!\p{L})(' . $anchor_escaped . ')(?!\p{L})/iu';
                
                if (preg_match($pattern, $element)) {
                    $new_val = $this->inject_link_in_html($element, $anchor, $url);
                    if ($new_val !== $element) {
                        $element = $new_val;
                        $injected = true;
                    }
                }
            }
        }
    }
}
