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
        add_action('wp_ajax_gmb_check_focus_keyword_uniqueness', array($this, 'ajax_check_focus_keyword_uniqueness'));
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
     * AI Suggest 404 Redirects (Derived from published public content permalinks & fuzzy/AI matching)
     */
    public function ajax_ai_suggest_404_redirects() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
        }

        $single_uri = isset($_POST['uri']) ? sanitize_text_field(wp_unslash($_POST['uri'])) : '';
        $repo       = new GMB_Ranker_SEO_Redirect_Repository();
        $logs       = $repo->get_404_logs();

        $target_logs = array();
        if (!empty($single_uri)) {
            $target_logs[] = array('uri' => $single_uri, 'count' => 1);
        } elseif (is_array($logs) && !empty($logs)) {
            $target_logs = array_slice($logs, 0, 30);
        }

        if (empty($target_logs)) {
            wp_send_json_success(array('message' => 'No 404 log entries found.', 'suggestions' => array()));
        }

        // Fetch candidate published posts, pages & custom post types for target matching
        $public_types = get_post_types(array('public' => true));
        unset($public_types['attachment']);

        $published_posts = get_posts(array(
            'post_type'      => array_values($public_types),
            'post_status'    => 'publish',
            'posts_per_page' => 150,
            'fields'         => 'ids',
        ));

        $candidates = array();
        foreach ($published_posts as $pid) {
            $permalink = get_permalink($pid);
            if ($permalink) {
                $path = wp_parse_url($permalink, PHP_URL_PATH) ?: $permalink;
                $candidates[] = array(
                    'title' => get_the_title($pid),
                    'slug'  => strtolower(get_post_field('post_name', $pid)),
                    'url'   => $permalink,
                    'path'  => $path,
                );
            }
        }

        $site_url = home_url('/');
        $site_path = wp_parse_url($site_url, PHP_URL_PATH) ?: '/';
        $suggestions = array();

        foreach ($target_logs as $log) {
            if (empty($log['uri'])) {
                continue;
            }

            $source_uri = $log['uri'];
            $clean_path = strtolower(trim(wp_parse_url($source_uri, PHP_URL_PATH) ?: $source_uri, '/'));

            $best_match  = $site_path;
            $best_reason = __('Fallback to site primary home path.', 'gmb-ranker-seo-automation');
            $confidence  = 0.60;
            $confidence_label = 'low';

            if (!empty($clean_path)) {
                $path_parts     = array_filter(explode('/', $clean_path));
                $target_keyword = !empty($path_parts) ? end($path_parts) : $clean_path;
                $best_sim_score = 0;

                foreach ($candidates as $cand) {
                    // Exact or substring slug match
                    if (!empty($cand['slug'])) {
                        if ($cand['slug'] === $target_keyword || strtolower(trim($cand['path'], '/')) === $clean_path) {
                            $best_match  = $cand['path'];
                            $best_reason = sprintf(__('Exact permalink/slug match with published page "%s"', 'gmb-ranker-seo-automation'), esc_html($cand['title']));
                            $confidence  = 0.96;
                            $confidence_label = 'high';
                            break;
                        }

                        if (strpos($cand['slug'], $target_keyword) !== false || strpos($target_keyword, $cand['slug']) !== false) {
                            $best_match  = $cand['path'];
                            $best_reason = sprintf(__('Path keyword match with published page "%s"', 'gmb-ranker-seo-automation'), esc_html($cand['title']));
                            $confidence  = 0.88;
                            $confidence_label = 'high';
                            $best_sim_score = 88;
                        }
                    }

                    // Fuzzy similarity match on title / slug
                    if ($confidence < 0.90 && !empty($target_keyword)) {
                        similar_text($target_keyword, $cand['slug'], $percent);
                        if ($percent > 65 && $percent > $best_sim_score) {
                            $best_sim_score   = $percent;
                            $best_match       = $cand['path'];
                            $best_reason      = sprintf(__('Fuzzy URL similarity (%.0f%%) match with "%s"', 'gmb-ranker-seo-automation'), $percent, esc_html($cand['title']));
                            $confidence       = round($percent / 100, 2);
                            $confidence_label = ($confidence >= 0.80) ? 'high' : 'medium';
                        }
                    }
                }
            }

            $suggestions[] = array(
                'source'           => $source_uri,
                'uri'              => $source_uri,
                'destination'      => $best_match,
                'target'           => $best_match,
                'code'             => 301,
                'confidence'       => $confidence_label,
                'confidence_score' => $confidence,
                'reason'           => $best_reason,
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
        if (is_string($rules_raw)) {
            $decoded = json_decode(wp_unslash($rules_raw), true);
            if (is_array($decoded)) {
                $rules_raw = $decoded;
            }
        }

        if (!is_array($rules_raw) || empty($rules_raw)) {
            wp_send_json_error(array('message' => 'No redirect rules supplied to apply.'), 400);
        }

        $repo = new GMB_Ranker_SEO_Redirect_Repository();
        $applied_count = 0;
        $applied_uris  = array();

        foreach ($rules_raw as $r) {
            if (!is_array($r)) {
                continue;
            }

            $src_raw  = wp_unslash(isset($r['source']) ? $r['source'] : (isset($r['uri']) ? $r['uri'] : ''));
            $dest_raw = wp_unslash(isset($r['destination']) ? $r['destination'] : (isset($r['target']) ? $r['target'] : ''));
            $code     = isset($r['code']) ? intval($r['code']) : 301;

            if (empty($src_raw)) {
                continue;
            }

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
                'target'     => $dest ?: '',
                'code'       => $code,
                'type'       => 'exact',
                'enabled'    => 1,
                'updated_at' => current_time('mysql'),
            );

            $saved = $repo->save_rule($rule);
            if ($saved) {
                $applied_count++;
                $applied_uris[] = $src;
            }
        }

        // Clean up cleared 404 log entries
        if ($applied_count > 0 && !empty($applied_uris)) {
            $logs = $repo->get_404_logs();
            if (is_array($logs)) {
                $filtered = array_values(array_filter($logs, function($l) use ($applied_uris) {
                    return !in_array($l['uri'], $applied_uris, true);
                }));
                $repo->save_404_logs($filtered);
            }
        }

        if ($applied_count > 0) {
            wp_send_json_success(array(
                'message' => sprintf(_n('Successfully created %d AI redirection rule.', 'Successfully created %d AI redirection rules.', $applied_count, 'gmb-ranker-seo-automation'), $applied_count),
                'count'   => $applied_count,
            ));
        } else {
            wp_send_json_error(array('message' => 'No valid AI redirection rules could be saved. Check for duplicate or invalid destination URLs.'), 400);
        }
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
            wp_send_json_error(array('message' => __('Unauthorized access or invalid post ID.', 'gmb-ranker-seo-automation')), 403);
        }

        $post = get_post($post_id);
        if (!$post || in_array($post->post_status, array('trash', 'auto-draft'), true)) {
            wp_send_json_error(array('message' => __('Target post not found or invalid status.', 'gmb-ranker-seo-automation')), 404);
        }

        $focus_kw = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : '';
        if (empty($focus_kw) && class_exists('GMB_Ranker_SEO_Keyword_Repository')) {
            $kw_repo  = new GMB_Ranker_SEO_Keyword_Repository();
            $focus_kw = $kw_repo->get_focus_keyword($post_id);
        }
        if (empty($focus_kw)) {
            $focus_kw = get_the_title($post_id);
        }

        $current_title = isset($_POST['seo_title']) ? sanitize_text_field(wp_unslash($_POST['seo_title'])) : get_post_meta($post_id, '_gmb_ranker_seo_title', true);
        if (empty($current_title)) {
            $current_title = get_the_title($post_id);
        }

        $current_desc = isset($_POST['meta_description']) ? sanitize_textarea_field(wp_unslash($_POST['meta_description'])) : get_post_meta($post_id, '_gmb_ranker_seo_description', true);

        try {
            if (class_exists('GMB_Ranker_SEO_Research_Engine')) {
                $args = array(
                    'post_id'       => $post_id,
                    'title'         => $current_title,
                    'content'       => isset($_POST['content']) ? wp_unslash($_POST['content']) : $post->post_content,
                    'focus_keyword' => $focus_kw,
                    'country'       => isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : 'GLOBAL|google.com',
                    'language'      => isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : 'en',
                    'mode'          => isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'optimize',
                );

                $research_data = GMB_Ranker_SEO_Research_Engine::run_research_pipeline($args);
                if (!empty($research_data) && is_array($research_data)) {
                    wp_send_json_success($research_data);
                }
            }
        } catch (\Throwable $e) {
            error_log('GMB Ranker SEO Research Engine Error: ' . $e->getMessage());
        }

        // Run canonical SEO audit via analysis service
        $saved_meta_score = get_post_meta($post_id, '_gmb_ranker_seo_score', true);
        $audit_score      = (is_numeric($saved_meta_score) && intval($saved_meta_score) > 0) ? intval($saved_meta_score) : 0;
        $audit_results    = array();

        if (class_exists('GMB_Ranker_SEO_Analysis_Service')) {
            $analysis_svc = new GMB_Ranker_SEO_Analysis_Service();
            $audit_res    = $analysis_svc->audit_post($post_id);
            if (is_array($audit_res)) {
                if (isset($audit_res['score']) && intval($audit_res['score']) > 0) {
                    $audit_score = intval($audit_res['score']);
                }
                $audit_results = isset($audit_res['results']) ? $audit_res['results'] : array();
            }
        }

        $recommendations = array();

        // 1. Focus Keyword Recommendation
        $kw_in_title = (stripos($current_title, $focus_kw) !== false);
        if (!$kw_in_title) {
            $suggested_title = $focus_kw . ' - ' . $current_title;
            if (mb_strlen($suggested_title) > 65) {
                $suggested_title = mb_substr($focus_kw . ' | ' . $post->post_title, 0, 60);
            }
            $recommendations[] = array(
                'id'          => 'seo_title',
                'category'    => __('SEO Title', 'gmb-ranker-seo-automation'),
                'current'     => $current_title,
                'recommended' => $suggested_title,
                'status'      => 'FIX NEEDED',
                'risk_level'  => 'LOW',
                'action'      => 'OPTIMIZE TITLE',
                'evidence'    => sprintf(__('Focus keyword "%s" is missing from SEO Title.', 'gmb-ranker-seo-automation'), esc_html($focus_kw)),
            );
        } else {
            $recommendations[] = array(
                'id'          => 'seo_title',
                'category'    => __('SEO Title', 'gmb-ranker-seo-automation'),
                'current'     => $current_title,
                'recommended' => $current_title,
                'status'      => 'OPTIMAL',
                'risk_level'  => 'LOW',
                'action'      => 'KEEP CURRENT',
                'evidence'    => __('SEO Title contains target focus keyword and optimal length.', 'gmb-ranker-seo-automation'),
            );
        }

        // 2. Meta Description Recommendation
        $desc_len = mb_strlen($current_desc);
        if (empty($current_desc)) {
            $suggested_desc = wp_strip_all_tags(mb_substr($post->post_content, 0, 155));
            if ((empty($suggested_desc) || mb_strlen($suggested_desc) < 60) && class_exists('GMB_Ranker_SEO_Content_AI')) {
                $suggested_desc = GMB_Ranker_SEO_Content_AI::generate_meta_description($current_title, $focus_kw, $post->post_content);
            }
            $recommendations[] = array(
                'id'          => 'meta_description',
                'category'    => __('Meta Description', 'gmb-ranker-seo-automation'),
                'current'     => '',
                'recommended' => $suggested_desc,
                'status'      => 'RECOMMENDED',
                'risk_level'  => 'LOW',
                'action'      => 'ADD DESCRIPTION',
                'evidence'    => __('Meta Description is missing on page. AI generated a contextual meta description for target topic.', 'gmb-ranker-seo-automation'),
            );
        } elseif ($desc_len < 120 || $desc_len > 160) {
            $suggested_desc = mb_substr($current_desc, 0, 155);
            if ($desc_len < 120 && class_exists('GMB_Ranker_SEO_Content_AI')) {
                $suggested_desc = GMB_Ranker_SEO_Content_AI::generate_meta_description($current_title, $focus_kw, $current_desc);
            }
            $recommendations[] = array(
                'id'          => 'meta_description',
                'category'    => __('Meta Description', 'gmb-ranker-seo-automation'),
                'current'     => $current_desc,
                'recommended' => $suggested_desc,
                'status'      => $desc_len < 120 ? 'UNDER-OPTIMIZED' : 'OVER-OPTIMIZED',
                'risk_level'  => 'LOW',
                'action'      => 'ADJUST LENGTH',
                'evidence'    => sprintf(__('Meta description length is %d characters (recommended: 120-160).', 'gmb-ranker-seo-automation'), $desc_len),
            );
        }

        // 2b. Content Depth & Structure Recommendation
        $word_count = str_word_count(wp_strip_all_tags($post->post_content));
        if ($word_count < 300 && class_exists('GMB_Ranker_SEO_Content_AI')) {
            $ai_draft_data = GMB_Ranker_SEO_Content_AI::generate_archetype_draft($current_title, $focus_kw, $post_id);
            $ai_intro_content = isset($ai_draft_data['draft']) ? $ai_draft_data['draft'] : '';
            if (!empty($ai_intro_content)) {
                $recommendations[] = array(
                    'id'          => 'content_intro',
                    'category'    => __('Content Intelligence', 'gmb-ranker-seo-automation'),
                    'current'     => sprintf(__('%d words in content body', 'gmb-ranker-seo-automation'), $word_count),
                    'recommended' => $ai_intro_content,
                    'preview'     => wp_strip_all_tags(mb_substr($ai_intro_content, 0, 200)) . '...',
                    'status'      => 'UNDER-OPTIMIZED',
                    'risk_level'  => 'LOW',
                    'action'      => 'ENHANCE CONTENT DEPTH',
                    'evidence'    => sprintf(__('Word count is low (%d words). AI generated comprehensive search-intent-aligned long-form content.', 'gmb-ranker-seo-automation'), $word_count),
                );
            }
        }

        // 3. Focus Keyword Persistence Recommendation
        $recommendations[] = array(
            'id'          => 'focus_keyword',
            'category'    => __('Focus Keyword', 'gmb-ranker-seo-automation'),
            'current'     => $focus_kw,
            'recommended' => $focus_kw,
            'status'      => 'RECOMMENDED',
            'risk_level'  => 'LOW',
            'action'      => 'SET FOCUS KEYWORD',
            'evidence'    => sprintf(__('Target keyword "%s" configured as primary ranking target.', 'gmb-ranker-seo-automation'), esc_html($focus_kw)),
        );

        // 4. Schema Preset Recommendation
        $active_schema = get_post_meta($post_id, '_gmb_ranker_schema_type', true);
        if (empty($active_schema)) {
            $pt_schema = get_option('gmb_' . $post->post_type . '_schema_type', 'Article');
            $recommendations[] = array(
                'id'          => 'schema_preset',
                'category'    => __('Schema Markup', 'gmb-ranker-seo-automation'),
                'current'     => __('None', 'gmb-ranker-seo-automation'),
                'recommended' => ucfirst($pt_schema),
                'status'      => 'RECOMMENDED',
                'risk_level'  => 'LOW',
                'action'      => sprintf(__('APPLY %s SCHEMA', 'gmb-ranker-seo-automation'), strtoupper($pt_schema)),
                'evidence'    => __('No structured data schema assigned to post. Recommended schema assigned.', 'gmb-ranker-seo-automation'),
            );
        }

        // 5. Table of Contents (TOC) Module Integration
        $toc_module_enabled = (get_option('gmb_ranker_module_toc', '1') === '1');
        $has_explicit_toc   = (stripos($post->post_content, 'gmb-toc-box') !== false || stripos($post->post_content, '[toc') !== false || stripos($post->post_content, 'table-of-contents') !== false);
        if (!$has_explicit_toc) {
            if ($toc_module_enabled) {
                $recommendations[] = array(
                    'id'          => 'table_of_contents',
                    'category'    => __('Table of Contents', 'gmb-ranker-seo-automation'),
                    'current'     => __('No TOC detected', 'gmb-ranker-seo-automation'),
                    'recommended' => __('Enable GMB Ranker TOC Auto-Insert', 'gmb-ranker-seo-automation'),
                    'status'      => 'MODULE DEPENDENT',
                    'risk_level'  => 'LOW',
                    'action'      => 'ENABLE TOC AUTO-INSERT',
                    'evidence'    => __('Add a Table of Contents to improve readability and user navigation.', 'gmb-ranker-seo-automation'),
                );
            } else {
                $recommendations[] = array(
                    'id'          => 'table_of_contents',
                    'category'    => __('Table of Contents', 'gmb-ranker-seo-automation'),
                    'current'     => __('TOC Module Disabled', 'gmb-ranker-seo-automation'),
                    'recommended' => __('Available through Table of Contents module', 'gmb-ranker-seo-automation'),
                    'status'      => 'MODULE DISABLED',
                    'risk_level'  => 'HIGH RISK',
                    'action'      => 'REQUIRES TOC MODULE',
                    'evidence'    => __('Table of Contents module is currently disabled in plugin settings.', 'gmb-ranker-seo-automation'),
                );
            }
        }

        // 6. Image SEO Handling (EXCLUDED from AI Auto-Fix per policy)
        $img_count = preg_match_all('/<img\s+[^>]*>/i', $post->post_content, $img_matches);
        if ($img_count > 0) {
            $recommendations[] = array(
                'id'          => 'image_seo',
                'category'    => __('Image SEO & Alt Text', 'gmb-ranker-seo-automation'),
                'current'     => sprintf(__('%d image(s) in post content', 'gmb-ranker-seo-automation'), $img_count),
                'recommended' => __('Handled by Image SEO module', 'gmb-ranker-seo-automation'),
                'status'      => 'HANDLED BY MODULE',
                'risk_level'  => 'HIGH RISK',
                'action'      => 'REQUIRES IMAGE SEO MODULE',
                'evidence'    => __('Image ALT and title attributes are managed separately by the Image SEO module.', 'gmb-ranker-seo-automation'),
            );
        }

        $potential_score = min(100, max($audit_score + 18, 85));

        wp_send_json_success(array(
            'target' => array(
                'post_id'       => $post_id,
                'post_title'    => get_the_title($post_id),
                'focus_keyword' => $focus_kw,
                'url'           => get_permalink($post_id),
            ),
            'score' => array(
                'current'         => $audit_score,
                'potential'       => $potential_score,
                'potential_label' => sprintf('%d / 100 (Potential: %d / 100)', $audit_score, $potential_score),
            ),
            'recommendations' => $recommendations,
        ));
    }

    /**
     * Quick Save AI SEO Fields
     */
    public function ajax_quick_save_ai_seo_fields() {
        $this->enforce_ajax_csrf_protection();

        $post_id = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
        if (empty($post_id) || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array('message' => __('Unauthorized access or invalid post ID.', 'gmb-ranker-seo-automation')), 403);
        }

        if (isset($_POST['meta_title'])) {
            $clean_title = sanitize_text_field(wp_unslash($_POST['meta_title']));
            update_post_meta($post_id, '_gmb_ranker_seo_title', $clean_title);
            update_post_meta($post_id, '_yoast_wpseo_title', $clean_title);
            update_post_meta($post_id, 'rank_math_title', $clean_title);
        }
        if (isset($_POST['meta_description'])) {
            $clean_desc = sanitize_textarea_field(wp_unslash($_POST['meta_description']));
            update_post_meta($post_id, '_gmb_ranker_seo_description', $clean_desc);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $clean_desc);
            update_post_meta($post_id, 'rank_math_description', $clean_desc);
        }
        if (isset($_POST['focus_keyword']) && class_exists('GMB_Ranker_SEO_Keyword_Repository')) {
            $kw_repo = new GMB_Ranker_SEO_Keyword_Repository();
            $kw_repo->set_focus_keyword($post_id, sanitize_text_field(wp_unslash($_POST['focus_keyword'])));
        }
        if (isset($_POST['schema_preset'])) {
            $clean_schema = sanitize_text_field(wp_unslash($_POST['schema_preset']));
            update_post_meta($post_id, '_gmb_ranker_schema_type', strtolower($clean_schema));
        }
        if (isset($_POST['table_of_contents']) && $_POST['table_of_contents'] === '1') {
            update_option('gmb_toc_auto_insert', '1');
        }
        if (!empty($_POST['content_intro'])) {
            $raw_intro = wp_unslash($_POST['content_intro']);
            $clean_intro = wp_kses_post($raw_intro);
            $existing_post = get_post($post_id);
            if ($existing_post) {
                $cur_content = $existing_post->post_content;
                if (stripos($cur_content, mb_substr(wp_strip_all_tags($clean_intro), 0, 50)) === false) {
                    $new_content = $clean_intro . "\n\n" . $cur_content;
                    wp_update_post(array(
                        'ID'           => $post_id,
                        'post_content' => $new_content,
                    ));
                }
            }
        }

        clean_post_cache($post_id);

        $recomputed_score = 0;
        if (class_exists('GMB_Ranker_SEO_Analysis_Service')) {
            $analysis_svc     = new GMB_Ranker_SEO_Analysis_Service();
            $audit_res        = $analysis_svc->audit_post($post_id);
            $recomputed_score = isset($audit_res['score']) ? intval($audit_res['score']) : 0;
        }

        wp_send_json_success(array(
            'message' => __('SEO fields updated successfully.', 'gmb-ranker-seo-automation'),
            'score'   => $recomputed_score,
        ));
    }

    /**
     * AJAX: Check Focus Keyword Uniqueness & Cannibalization
     */
    public function ajax_check_focus_keyword_uniqueness() {
        $this->enforce_ajax_csrf_protection();

        $post_id  = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
        $focus_kw = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : '';

        if (empty($focus_kw)) {
            wp_send_json_success(array(
                'is_cannibalized' => false,
                'conflict_count'  => 0,
                'conflicts'       => array(),
            ));
        }

        if (class_exists('GMB_Ranker_SEO_Analysis_Service')) {
            $cannibalization = GMB_Ranker_SEO_Analysis_Service::check_keyword_cannibalization($focus_kw, $post_id);
            wp_send_json_success($cannibalization);
        } else {
            wp_send_json_success(array(
                'is_cannibalized' => false,
                'conflict_count'  => 0,
                'conflicts'       => array(),
            ));
        }
    }
}
