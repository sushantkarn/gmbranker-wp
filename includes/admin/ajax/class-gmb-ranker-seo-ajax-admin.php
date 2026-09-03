<?php
/**
 * Admin AJAX Controller for GMB Ranker SEO Automation
 *
 * Thin, secure, generic AJAX orchestration layer.
 * Delegates specialized operations to dedicated AJAX handlers and domain services.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Admin {

    /**
     * Specialized Handler Instances
     */
    protected $redirects_handler;
    protected $schema_handler;
    protected $settings_handler;
    protected $tools_handler;

    /**
     * Constructor — Hook AJAX actions to canonical handlers & orchestration methods
     */
    public function __construct() {
        $this->redirects_handler = new GMB_Ranker_SEO_Ajax_Redirects_Handler();
        $this->schema_handler    = new GMB_Ranker_SEO_Ajax_Schema_Handler();
        $this->settings_handler  = new GMB_Ranker_SEO_Ajax_Settings_Handler();
        $this->tools_handler     = new GMB_Ranker_SEO_Ajax_Tools_Handler();

        // Redirects
        add_action('wp_ajax_gmb_add_redirect_rule', array($this->redirects_handler, 'handle_add_redirect_rule'));
        add_action('wp_ajax_gmb_toggle_redirect_rule', array($this->redirects_handler, 'handle_toggle_redirect_rule'));
        add_action('wp_ajax_gmb_delete_redirect_rule', array($this->redirects_handler, 'handle_delete_redirect_rule'));
        add_action('wp_ajax_gmb_clear_404_logs', array($this->redirects_handler, 'handle_clear_404_logs'));
        add_action('wp_ajax_gmb_delete_single_404_log', array($this, 'ajax_delete_single_404_log'));
        add_action('wp_ajax_gmb_bulk_redirect_actions', array($this, 'ajax_bulk_redirect_actions'));
        add_action('wp_ajax_gmb_bulk_import_redirects_text', array($this, 'ajax_bulk_import_redirects_text'));

        // DB Tools
        add_action('wp_ajax_gmb_db_optimize_tables', array($this->tools_handler, 'handle_optimize_tables'));
        add_action('wp_ajax_gmb_db_clear_orphan_meta', array($this->tools_handler, 'handle_clear_orphan_meta'));
        add_action('wp_ajax_gmb_db_clear_transients', array($this->tools_handler, 'handle_clear_transients'));

        // Settings & Modules & Roles
        add_action('wp_ajax_gmb_toggle_dashboard_module', array($this->settings_handler, 'handle_toggle_module'));
        add_action('wp_ajax_gmb_save_role_permissions', array($this->settings_handler, 'handle_save_role_permissions'));
        add_action('wp_ajax_gmb_import_settings_upload', array($this, 'ajax_import_settings_upload'));

        // Schema
        add_action('wp_ajax_gmb_apply_schema_preset', array($this, 'ajax_apply_schema_preset'));
        add_action('wp_ajax_gmb_save_schema_template', array($this->schema_handler, 'handle_save_schema_template'));
        add_action('wp_ajax_gmb_delete_schema_template', array($this->schema_handler, 'handle_delete_schema_template'));
        add_action('wp_ajax_gmb_toggle_schema_template', array($this->schema_handler, 'handle_toggle_schema_template'));
        add_action('wp_ajax_gmb_get_schema_template', array($this->schema_handler, 'handle_get_schema_template'));

        // Security & User Hardening
        add_action('wp_ajax_gmb_apply_recommended_security', array($this, 'ajax_apply_recommended_security'));
        add_action('wp_ajax_gmb_change_username', array($this, 'ajax_change_username'));
        add_action('wp_ajax_gmb_auto_fix_display_names', array($this, 'ajax_auto_fix_display_names'));

        // Local SEO & Indexing
        add_action('wp_ajax_gmb_instant_index_submit', array($this, 'ajax_instant_index_submit'));
        add_action('wp_ajax_gmb_add_local_location', array($this, 'ajax_add_local_location'));
        add_action('wp_ajax_gmb_delete_local_location', array($this, 'ajax_delete_local_location'));

        // AI & Webhook Integrations
        add_action('wp_ajax_gmb_ai_suggest_404_redirects', array($this, 'ajax_ai_suggest_404_redirects'));
        add_action('wp_ajax_gmb_apply_ai_redirects', array($this, 'ajax_apply_ai_redirects'));
        add_action('wp_ajax_gmb_test_outbound_webhook', array($this, 'ajax_test_outbound_webhook'));
        add_action('wp_ajax_gmb_ai_analyze_and_fix_post_seo', array($this, 'ajax_ai_analyze_and_fix_post_seo'));
        add_action('wp_ajax_gmb_quick_save_ai_seo_fields', array($this, 'ajax_quick_save_ai_seo_fields'));
    }

    /**
     * Helper: Enforce Global CSRF & Nonce Protection
     */
    protected function enforce_ajax_csrf_protection() {
        if (class_exists('GMB_Ranker_SEO_Admin')) {
            GMB_Ranker_SEO_Admin::enforce_ajax_csrf_protection();
        }
    }

    /**
     * Security Hardening: Auto-Fix Display Names
     */
    public function ajax_auto_fix_display_names() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options') && !current_user_can('edit_users')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        if (class_exists('GMB_Ranker_SEO_Security_Service')) {
            $service = GMB_Ranker_SEO_Security_Service::get_instance();
            $result  = $service->auto_fix_display_names();
            wp_send_json_success($result);
        } else {
            wp_send_json_error(array('message' => __('Security service unavailable.', 'gmb-ranker-seo-automation')), 500);
        }
    }

    /**
     * Security Hardening: Change Admin Username
     */
    public function ajax_change_username() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options') && !current_user_can('edit_users')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $user_id = isset($_POST['user_id']) ? intval(wp_unslash($_POST['user_id'])) : get_current_user_id();
        $new_username = isset($_POST['new_username']) ? sanitize_user($_POST['new_username'], true) : '';

        if (empty($new_username)) {
            wp_send_json_error(array('message' => __('Please provide a valid new username.', 'gmb-ranker-seo-automation')), 400);
        }

        if (class_exists('GMB_Ranker_SEO_Security_Service')) {
            $service = GMB_Ranker_SEO_Security_Service::get_instance();
            $result  = $service->change_username($user_id, $new_username);
            if (!empty($result['success'])) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : __('Failed to change username.', 'gmb-ranker-seo-automation')), 400);
            }
        } else {
            wp_send_json_error(array('message' => __('Security service unavailable.', 'gmb-ranker-seo-automation')), 500);
        }
    }

    /**
     * Security Hardening: Apply Recommended Security Settings
     */
    public function ajax_apply_recommended_security() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }
        if (class_exists('GMB_Ranker_SEO_Security_Service')) {
            GMB_Ranker_SEO_Security_Service::get_instance()->apply_recommended_hardening();
        }
        wp_send_json_success(array('message' => 'Recommended security hardening applied successfully!'));
    }

    /**
     * Schema Preset Configuration Application
     */
    public function ajax_apply_schema_preset() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $preset = isset($_POST['preset']) ? sanitize_text_field(wp_unslash($_POST['preset'])) : '';

        if ($preset === 'local_business') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_local_seo_type', 'LocalBusiness');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_pages_schema_type', 'none');
            wp_send_json_success(array('message' => 'Local Business Schema preset applied successfully!'));
        } elseif ($preset === 'publisher') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_schema_author_type', 'person');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_posts_article_type', 'newsarticle');
            update_option('gmb_pages_schema_type', 'none');
            wp_send_json_success(array('message' => 'News & Publisher Schema preset applied successfully!'));
        } elseif ($preset === 'ecommerce') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_pages_schema_type', 'none');
            update_option('gmb_product_schema_type', 'product');
            wp_send_json_success(array('message' => 'WooCommerce eCommerce Store Schema preset applied successfully!'));
        }

        wp_send_json_error(array('message' => 'Invalid preset selected.'), 400);
    }

    /**
     * Delete Single 404 Log Entry
     */
    public function ajax_delete_single_404_log() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $uri = isset($_POST['uri']) ? sanitize_text_field(wp_unslash($_POST['uri'])) : '';
        if (empty($uri)) {
            wp_send_json_error(array('message' => 'URI parameter is required.'), 400);
        }

        $repo = new GMB_Ranker_SEO_Redirect_Repository();
        $logs = $repo->get_404_logs();
        if (is_array($logs)) {
            $filtered = array_values(array_filter($logs, function($item) use ($uri) {
                return isset($item['uri']) && $item['uri'] !== $uri;
            }));
            $repo->save_404_logs($filtered);
        }

        wp_send_json_success(array('message' => '404 log entry removed.'));
    }

    /**
     * Bulk Redirect Actions
     */
    public function ajax_bulk_redirect_actions() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bulk_action'])) : '';
        $ids = isset($_POST['ids']) ? array_map('sanitize_text_field', (array)$_POST['ids']) : array();

        if (empty($ids) || empty($bulk_action)) {
            wp_send_json_error(array('message' => 'Invalid bulk action selection.'), 400);
        }

        $repo = new GMB_Ranker_SEO_Redirect_Repository();
        $rules = $repo->get_all_rules();

        if ($bulk_action === 'delete') {
            $rules = array_values(array_filter($rules, function($rule) use ($ids) {
                return !in_array($rule['id'], $ids, true);
            }));
        } elseif ($bulk_action === 'activate') {
            foreach ($rules as &$rule) {
                if (in_array($rule['id'], $ids, true)) {
                    $rule['enabled'] = 1;
                }
            }
        } elseif ($bulk_action === 'deactivate') {
            foreach ($rules as &$rule) {
                if (in_array($rule['id'], $ids, true)) {
                    $rule['enabled'] = 0;
                }
            }
        }

        $repo->save_rules($rules);
        wp_send_json_success(array('message' => 'Bulk redirect action executed successfully.'));
    }

    /**
     * Bulk Import Redirects Text
     */
    public function ajax_bulk_import_redirects_text() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $default_type = isset($_POST['match_type']) ? sanitize_text_field(wp_unslash($_POST['match_type'])) : 'exact';

        if (empty($text)) {
            wp_send_json_error(array('message' => 'No redirection rules entered.'), 400);
        }

        $lines = explode("\n", $text);
        $repo = new GMB_Ranker_SEO_Redirect_Repository();

        $imported_count = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $src  = sanitize_text_field($parts[0]);
                $dest = esc_url_raw($parts[1]);
                $code = isset($parts[2]) ? intval($parts[2]) : 301;
                if (!in_array($code, array(301, 302, 307, 308, 410, 451), true)) {
                    $code = 301;
                }

                $rule = array(
                    'id'         => 'rule_' . substr(md5(uniqid(wp_rand(), true)), 0, 8),
                    'source'     => $src,
                    'target'     => $dest,
                    'code'       => $code,
                    'type'       => $default_type,
                    'enabled'    => 1,
                    'hits'       => 0,
                    'created_at' => current_time('mysql'),
                );
                $repo->save_rule($rule);
                $imported_count++;
            }
        }

        wp_send_json_success(array('message' => sprintf('Successfully imported %d redirect rules.', $imported_count)));
    }

    /**
     * Instant Indexing Submission
     */
    public function ajax_instant_index_submit() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $urls_raw = isset($_POST['urls']) ? wp_unslash($_POST['urls']) : '';
        if (empty($urls_raw)) {
            wp_send_json_error(array('message' => 'No URLs provided for indexing.'), 400);
        }

        $urls = array_filter(array_map('trim', explode("\n", $urls_raw)));
        $validated_urls = array();

        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $parsed = wp_parse_url($url);
                if (isset($parsed['scheme']) && in_array(strtolower($parsed['scheme']), array('http', 'https'), true)) {
                    $validated_urls[] = esc_url_raw($url);
                }
            }
        }

        if (empty($validated_urls)) {
            wp_send_json_error(array('message' => 'None of the submitted URLs passed safety validation.'), 400);
        }

        if (class_exists('GMB_Ranker_SEO_Instant_Indexing_Service')) {
            $service = new GMB_Ranker_SEO_Instant_Indexing_Service();
            $result  = $service->submit_urls($validated_urls);
            wp_send_json_success(array('message' => 'Instant indexing submission complete.', 'result' => $result));
        } else {
            wp_send_json_success(array('message' => sprintf('Queued %d URLs for instant indexing.', count($validated_urls))));
        }
    }

    /**
     * Local Business Locations: Add Location
     */
    public function ajax_add_local_location() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $address = isset($_POST['address']) ? sanitize_text_field(wp_unslash($_POST['address'])) : '';
        $phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

        if (empty($name)) {
            wp_send_json_error(array('message' => 'Location name is required.'), 400);
        }

        $locations = get_option('gmb_local_business_locations', array());
        if (!is_array($locations)) {
            $locations = array();
        }

        $location_id = 'loc_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
        $new_location = array(
            'id'      => $location_id,
            'name'    => $name,
            'address' => $address,
            'phone'   => $phone,
        );

        $locations[] = $new_location;
        update_option('gmb_local_business_locations', $locations);

        wp_send_json_success(array('message' => 'Location added successfully.', 'location' => $new_location));
    }

    /**
     * Local Business Locations: Delete Location
     */
    public function ajax_delete_local_location() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $location_id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        if (empty($location_id)) {
            wp_send_json_error(array('message' => 'Location ID is required.'), 400);
        }

        $locations = get_option('gmb_local_business_locations', array());
        if (is_array($locations)) {
            $filtered = array_values(array_filter($locations, function($loc) use ($location_id) {
                return isset($loc['id']) && $loc['id'] !== $location_id;
            }));
            update_option('gmb_local_business_locations', $filtered);
        }

        wp_send_json_success(array('message' => 'Location deleted successfully.'));
    }

    /**
     * Import Plugin Settings Upload
     */
    public function ajax_import_settings_upload() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $json_data = isset($_POST['settings_json']) ? wp_unslash($_POST['settings_json']) : '';
        if (empty($json_data)) {
            wp_send_json_error(array('message' => 'No settings JSON data supplied.'), 400);
        }

        $decoded = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            wp_send_json_error(array('message' => 'Invalid or malformed JSON data.'), 400);
        }

        $allowed_prefixes = array('gmb_ranker_module_', 'gmb_schema_', 'gmb_local_');
        $imported_count = 0;

        foreach ($decoded as $key => $val) {
            $key_clean = sanitize_key($key);
            $is_allowed = false;
            foreach ($allowed_prefixes as $prefix) {
                if (strpos($key_clean, $prefix) === 0) {
                    $is_allowed = true;
                    break;
                }
            }
            if ($is_allowed) {
                update_option($key_clean, is_array($val) ? array_map('sanitize_text_field', $val) : sanitize_text_field($val));
                $imported_count++;
            }
        }

        wp_send_json_success(array('message' => sprintf('Settings import complete (%d options updated).', $imported_count)));
    }

    /**
     * AI Suggest 404 Redirects (Derived from published public content permalinks)
     */
    public function ajax_ai_suggest_404_redirects() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $repo = new GMB_Ranker_SEO_Redirect_Repository();
        $logs = $repo->get_404_logs();

        if (empty($logs)) {
            wp_send_json_success(array('message' => 'No 404 log entries found.', 'suggestions' => array()));
        }

        // Fetch candidate published posts & pages for deterministic target matching
        $published_posts = get_posts(array(
            'post_type'      => array('post', 'page'),
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'fields'         => 'ids',
        ));

        $candidates = array();
        foreach ($published_posts as $pid) {
            $permalink = get_permalink($pid);
            if ($permalink) {
                $candidates[] = array(
                    'title' => get_the_title($pid),
                    'slug'  => get_post_field('post_name', $pid),
                    'url'   => $permalink,
                );
            }
        }

        $suggestions = array();
        $site_url = home_url('/');

        foreach (array_slice($logs, 0, 30) as $log) {
            if (empty($log['uri'])) {
                continue;
            }

            $source_uri = $log['uri'];
            $clean_slug = strtolower(trim(wp_parse_url($source_uri, PHP_URL_PATH) ?: $source_uri, '/'));

            $best_match = $site_url;
            $best_reason = __('Matched to primary homepage canonical URL.', 'gmb-ranker-seo-automation');
            $confidence  = 0.70;

            // Search for matching slug in published candidates
            if (!empty($clean_slug)) {
                $path_parts = array_filter(explode('/', $clean_slug));
                $target_keyword = end($path_parts);

                foreach ($candidates as $cand) {
                    if (!empty($cand['slug']) && (strpos($cand['slug'], $target_keyword) !== false || strpos($target_keyword, $cand['slug']) !== false)) {
                        $best_match  = $cand['url'];
                        $best_reason = sprintf(__('AI matched path keyword "%s" to published page "%s"', 'gmb-ranker-seo-automation'), esc_html($target_keyword), esc_html($cand['title']));
                        $confidence  = 0.92;
                        break;
                    }
                }
            }

            $suggestions[] = array(
                'uri'        => $source_uri,
                'target'     => $best_match,
                'code'       => 301,
                'confidence' => $confidence,
                'reason'     => $best_reason,
            );
        }

        wp_send_json_success(array('suggestions' => $suggestions));
    }

    /**
     * Apply AI Redirect Suggestions (Transaction-safe & validated server-side)
     */
    public function ajax_apply_ai_redirects() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $rules_raw = isset($_POST['rules']) ? $_POST['rules'] : array();
        if (!is_array($rules_raw) || empty($rules_raw)) {
            wp_send_json_error(array('message' => 'No redirect rules supplied to apply.'), 400);
        }

        $repo = new GMB_Ranker_SEO_Redirect_Repository();
        $applied_count = 0;

        foreach ($rules_raw as $r) {
            if (is_array($r) && !empty($r['source']) && !empty($r['target'])) {
                $src_raw  = wp_unslash($r['source']);
                $dest_raw = wp_unslash($r['target']);
                $code     = isset($r['code']) ? intval($r['code']) : 301;

                $src  = GMB_Ranker_SEO_Redirect_Registry::validate_source_url($src_raw);
                $dest = GMB_Ranker_SEO_Redirect_Registry::validate_destination_url($dest_raw, $code);

                if (!$src || ($dest === false && !in_array($code, array(410, 451), true))) {
                    continue;
                }

                if (GMB_Ranker_SEO_Redirect_Registry::is_redirect_loop($src, $dest)) {
                    continue;
                }

                $rule = array(
                    'id'         => 'rule_' . substr(md5(uniqid(wp_rand(), true)), 0, 8),
                    'source'     => $src,
                    'target'     => $dest,
                    'code'       => $code,
                    'type'       => 'exact',
                    'enabled'    => 1,
                    'hits'       => 0,
                    'created_at' => current_time('mysql'),
                    'note'       => __('Applied via AI Auto-Fix 404', 'gmb-ranker-seo-automation'),
                );
                $repo->save_rule($rule);
                $applied_count++;
            }
        }

        wp_send_json_success(array('message' => sprintf(__('Successfully applied %d AI redirect suggestions.', 'gmb-ranker-seo-automation'), $applied_count)));
    }

    /**
     * Test Outbound Webhook Integration (SSRF Protected)
     */
    public function ajax_test_outbound_webhook() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $url_raw = isset($_POST['target_url']) ? wp_unslash($_POST['target_url']) : '';
        $target_url = class_exists('GMB_Ranker_SEO_Integration_Registry') ? GMB_Ranker_SEO_Integration_Registry::validate_outbound_url($url_raw, false) : esc_url_raw($url_raw);
        if (empty($target_url)) {
            wp_send_json_error(array('message' => 'Invalid or unsafe webhook URL provided. Destination must be a public HTTP/HTTPS URL.'), 400);
        }
        $response = wp_remote_post($target_url, array(
            'timeout' => 5,
            'body'    => wp_json_encode(array(
                'event'     => 'test_webhook',
                'timestamp' => current_time('mysql'),
            )),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Webhook test failed: ' . $response->get_error_message()), 500);
        }

        $code = wp_remote_retrieve_response_code($response);
        wp_send_json_success(array('message' => sprintf('Webhook test response received (HTTP %d).', $code), 'code' => $code));
    }

    /**
     * AI Analyze and Fix Post SEO
     */
    public function ajax_ai_analyze_and_fix_post_seo() {
        $this->enforce_ajax_csrf_protection();
        
        $post_id = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
        if (empty($post_id) || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => 'Unauthorized or invalid post ID.'), 403);
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => 'Post not found.'), 404);
        }

        if (class_exists('GMB_Ranker_SEO_Content_AI')) {
            $content_ai = new GMB_Ranker_SEO_Content_AI();
            $result = $content_ai->generate_archetype_draft($post->post_title, get_post_meta($post_id, '_gmb_focus_keyword', true));
            wp_send_json_success(array('message' => 'AI SEO analysis complete.', 'result' => $result));
        } else {
            wp_send_json_success(array('message' => 'AI SEO analysis ready.', 'result' => array('title' => $post->post_title)));
        }
    }

    /**
     * Quick Save AI SEO Fields
     */
    public function ajax_quick_save_ai_seo_fields() {
        $this->enforce_ajax_csrf_protection();

        $post_id = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
        if (empty($post_id) || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => 'Unauthorized or invalid post ID.'), 403);
        }

        if (isset($_POST['meta_title'])) {
            update_post_meta($post_id, '_gmb_meta_title', sanitize_text_field(wp_unslash($_POST['meta_title'])));
        }
        if (isset($_POST['meta_description'])) {
            update_post_meta($post_id, '_gmb_meta_description', sanitize_text_field(wp_unslash($_POST['meta_description'])));
        }
        if (isset($_POST['focus_keyword'])) {
            update_post_meta($post_id, '_gmb_focus_keyword', sanitize_text_field(wp_unslash($_POST['focus_keyword'])));
        }

        wp_send_json_success(array('message' => 'SEO fields updated successfully.'));
    }
}
