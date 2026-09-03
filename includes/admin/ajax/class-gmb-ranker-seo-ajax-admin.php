<?php
/**
 * Admin AJAX Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Admin {

    public function __construct() {
        add_action('wp_ajax_gmb_add_redirect_rule', array($this, 'ajax_add_redirect_rule'));
        add_action('wp_ajax_gmb_delete_redirect_rule', array($this, 'ajax_delete_redirect_rule'));
        add_action('wp_ajax_gmb_clear_404_logs', array($this, 'ajax_clear_404_logs'));
        add_action('wp_ajax_gmb_delete_single_404_log', array($this, 'ajax_delete_single_404_log'));
        add_action('wp_ajax_gmb_bulk_redirect_actions', array($this, 'ajax_bulk_redirect_actions'));
        add_action('wp_ajax_gmb_bulk_import_redirects_text', array($this, 'ajax_bulk_import_redirects_text'));
        add_action('wp_ajax_gmb_db_optimize_tables', array($this, 'ajax_db_optimize_tables'));
        add_action('wp_ajax_gmb_db_clear_orphan_meta', array($this, 'ajax_db_clear_orphan_meta'));
        add_action('wp_ajax_gmb_db_clear_transients', array($this, 'ajax_db_clear_transients'));
        add_action('wp_ajax_gmb_db_import_rankmath', array($this, 'ajax_db_import_rankmath'));
        add_action('wp_ajax_gmb_db_import_yoast', array($this, 'ajax_db_import_yoast'));
        add_action('wp_ajax_gmb_save_role_permissions', array($this, 'ajax_save_role_permissions'));
        add_action('wp_ajax_gmb_instant_index_submit', array($this, 'ajax_instant_index_submit'));
        add_action('wp_ajax_gmb_add_local_location', array($this, 'ajax_add_local_location'));
        add_action('wp_ajax_gmb_delete_local_location', array($this, 'ajax_delete_local_location'));
        add_action('wp_ajax_gmb_toggle_redirect_rule', array($this, 'ajax_toggle_redirect_rule'));
        add_action('wp_ajax_gmb_toggle_dashboard_module', array($this, 'ajax_toggle_dashboard_module'));
        add_action('wp_ajax_gmb_import_settings_upload', array($this, 'ajax_import_settings_upload'));
        add_action('wp_ajax_gmb_apply_schema_preset', array($this, 'ajax_apply_schema_preset'));
        add_action('wp_ajax_gmb_save_schema_template', array($this, 'ajax_save_schema_template'));
        add_action('wp_ajax_gmb_delete_schema_template', array($this, 'ajax_delete_schema_template'));
        add_action('wp_ajax_gmb_toggle_schema_template', array($this, 'ajax_toggle_schema_template'));
        add_action('wp_ajax_gmb_get_schema_template', array($this, 'ajax_get_schema_template'));
        add_action('wp_ajax_gmb_apply_recommended_security', array($this, 'ajax_apply_recommended_security'));
        add_action('wp_ajax_gmb_change_username', array($this, 'ajax_change_username'));
        add_action('wp_ajax_gmb_auto_fix_display_names', array($this, 'ajax_auto_fix_display_names'));
        add_action('wp_ajax_gmb_ai_suggest_404_redirects', array($this, 'ajax_ai_suggest_404_redirects'));
        add_action('wp_ajax_gmb_apply_ai_redirects', array($this, 'ajax_apply_ai_redirects'));
        add_action('wp_ajax_gmb_test_outbound_webhook', array($this, 'ajax_test_outbound_webhook'));
        add_action('wp_ajax_gmb_ai_analyze_and_fix_post_seo', array($this, 'ajax_ai_analyze_and_fix_post_seo'));
        add_action('wp_ajax_gmb_quick_save_ai_seo_fields', array($this, 'ajax_quick_save_ai_seo_fields'));
    }

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

    public function ajax_apply_recommended_security() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        if (class_exists('GMB_Ranker_SEO_Security_Service')) {
            GMB_Ranker_SEO_Security_Service::get_instance()->apply_recommended_hardening();
        }
        wp_send_json_success(array('message' => 'Recommended security hardening applied successfully!'));
    }

    protected function enforce_ajax_csrf_protection() {
        if (class_exists('GMB_Ranker_SEO_Admin')) {
            GMB_Ranker_SEO_Admin::enforce_ajax_csrf_protection();
        }
    }

    public function ajax_apply_schema_preset() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'));
        }
        $preset = isset($_POST['preset']) ? sanitize_text_field(wp_unslash($_POST['preset'])) : '';
        if ($preset === 'local_business') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_local_seo_type', 'LocalBusiness');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_pages_schema_type', 'none');
            update_option('gmb_services_schema_type', 'service');
            update_option('gmb_service_locations_schema_type', 'localbusiness');
            update_option('gmb_team_members_schema_type', 'person');
            wp_send_json_success(array('message' => 'Local Business Schema preset applied successfully!'));
        } elseif ($preset === 'publisher') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_schema_author_type', 'person');
            update_option('gmb_schema_author_sameas', '1');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_posts_article_type', 'newsarticle');
            update_option('gmb_pages_schema_type', 'none');
            wp_send_json_success(array('message' => 'News & Publisher Schema preset applied successfully!'));
        } elseif ($preset === 'healthcare') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_local_seo_type', 'MedicalBusiness');
            update_option('gmb_local_seo_business_subtype', 'MedicalBusiness');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_pages_schema_type', 'none');
            update_option('gmb_services_schema_type', 'service');
            update_option('gmb_services_schema_provider_type', 'organization');
            update_option('gmb_service_locations_schema_type', 'localbusiness');
            update_option('gmb_service_locations_schema_business_type', 'MedicalBusiness');
            update_option('gmb_team_members_schema_type', 'person');
            wp_send_json_success(array('message' => 'Healthcare & Medical Service Agency Schema preset applied successfully!'));
        } elseif ($preset === 'ecommerce') {
            update_option('gmb_schema_enable_website', '1');
            update_option('gmb_schema_enable_sitelinks', '1');
            update_option('gmb_schema_enable_breadcrumbs', '1');
            update_option('gmb_posts_schema_type', 'article');
            update_option('gmb_pages_schema_type', 'none');
            update_option('gmb_product_schema_type', 'product');
            wp_send_json_success(array('message' => 'WooCommerce eCommerce Store Schema preset applied successfully!'));
        }
        wp_send_json_error(array('message' => 'Invalid preset selected.'));
    }

    public function sanitize_schema_templates($templates) {
        if (!is_array($templates)) {
            return array();
        }
        $sanitized = array();
        foreach ($templates as $tpl) {
            if (!is_array($tpl)) {
                continue;
            }
            $clean_conditions = array();
            if (isset($tpl['conditions']) && is_array($tpl['conditions'])) {
                foreach ($tpl['conditions'] as $cond) {
                    if (is_array($cond)) {
                        $c_type = (isset($cond['type']) && $cond['type'] === 'exclude') ? 'exclude' : 'include';
                        $c_target = isset($cond['target']) ? sanitize_key($cond['target']) : 'entire_site';
                        $c_value = isset($cond['value']) ? sanitize_text_field($cond['value']) : '';
                        $clean_conditions[] = array(
                            'type'   => $c_type,
                            'target' => $c_target,
                            'value'  => $c_value,
                        );
                    }
                }
            }

            $raw_json = isset($tpl['schema_json']) ? wp_unslash($tpl['schema_json']) : '';
            if (empty($raw_json) && isset($tpl['schema_data'])) {
                $raw_json = is_array($tpl['schema_data']) ? wp_json_encode($tpl['schema_data'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $tpl['schema_data'];
            }

            $sanitized[] = array(
                'id'          => isset($tpl['id']) ? sanitize_key($tpl['id']) : 'tpl_' . uniqid(),
                'title'       => isset($tpl['title']) ? sanitize_text_field($tpl['title']) : 'Untitled Template',
                'type'        => isset($tpl['type']) ? sanitize_text_field($tpl['type']) : 'Custom',
                'status'      => (isset($tpl['status']) && $tpl['status'] === 'inactive') ? 'inactive' : 'active',
                'schema_json' => trim($raw_json),
                'conditions'  => $clean_conditions,
                'created'     => isset($tpl['created']) ? intval($tpl['created']) : time(),
                'updated'     => time(),
            );
        }
        return $sanitized;
    }

    public function ajax_save_schema_template() {
        $this->enforce_ajax_csrf_protection();

        $template_id = isset($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '';
        $title       = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : 'Untitled Template';
        $type        = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'Custom';
        $status      = (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'inactive' : 'active';
        $schema_json = isset($_POST['schema_json']) ? wp_unslash($_POST['schema_json']) : '';

        $conditions = array();
        if (isset($_POST['conditions']) && is_array($_POST['conditions'])) {
            foreach ($_POST['conditions'] as $cond) {
                if (is_array($cond)) {
                    $conditions[] = array(
                        'type'   => (isset($cond['type']) && $cond['type'] === 'exclude') ? 'exclude' : 'include',
                        'target' => isset($cond['target']) ? sanitize_key($cond['target']) : 'entire_site',
                        'value'  => isset($cond['value']) ? sanitize_text_field($cond['value']) : '',
                    );
                }
            }
        }

        $templates = get_option('gmb_schema_templates', array());
        if (!is_array($templates)) {
            $templates = array();
        }

        $now = time();
        $is_new = true;
        if (!empty($template_id)) {
            foreach ($templates as $idx => $item) {
                if (isset($item['id']) && $item['id'] === $template_id) {
                    $templates[$idx] = array(
                        'id'          => $template_id,
                        'title'       => $title,
                        'type'        => $type,
                        'status'      => $status,
                        'schema_json' => trim($schema_json),
                        'conditions'  => $conditions,
                        'created'     => isset($item['created']) ? $item['created'] : $now,
                        'updated'     => $now,
                    );
                    $is_new = false;
                    break;
                }
            }
        }

        if ($is_new) {
            $new_id = !empty($template_id) ? $template_id : 'tpl_' . uniqid();
            $templates[] = array(
                'id'          => $new_id,
                'title'       => $title,
                'type'        => $type,
                'status'      => $status,
                'schema_json' => trim($schema_json),
                'conditions'  => $conditions,
                'created'     => $now,
                'updated'     => $now,
            );
        }

        update_option('gmb_schema_templates', $this->sanitize_schema_templates($templates));
        wp_send_json_success(array(
            'message'   => $is_new ? 'Schema Template created successfully!' : 'Schema Template updated successfully!',
            'templates' => get_option('gmb_schema_templates', array()),
        ));
    }

    public function ajax_delete_schema_template() {
        $this->enforce_ajax_csrf_protection();
        $id = isset($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '';
        if (empty($id)) {
            wp_send_json_error(array('message' => 'Template ID is required.'));
        }

        $templates = get_option('gmb_schema_templates', array());
        $filtered = array();
        foreach ($templates as $tpl) {
            if (isset($tpl['id']) && $tpl['id'] === $id) {
                continue;
            }
            $filtered[] = $tpl;
        }

        update_option('gmb_schema_templates', $filtered);
        wp_send_json_success(array('message' => 'Schema Template deleted.', 'templates' => $filtered));
    }

    public function ajax_toggle_schema_template() {
        $this->enforce_ajax_csrf_protection();
        $id = isset($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '';
        $new_status = 'active';

        $templates = get_option('gmb_schema_templates', array());
        foreach ($templates as $idx => $tpl) {
            if (isset($tpl['id']) && $tpl['id'] === $id) {
                $new_status = (isset($tpl['status']) && $tpl['status'] === 'active') ? 'inactive' : 'active';
                $templates[$idx]['status'] = $new_status;
                break;
            }
        }

        update_option('gmb_schema_templates', $templates);
        wp_send_json_success(array('message' => 'Template status set to ' . $new_status, 'status' => $new_status));
    }

    public function ajax_get_schema_template() {
        $this->enforce_ajax_csrf_protection();
        $id = isset($_GET['id']) ? sanitize_key(wp_unslash($_GET['id'])) : (isset($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '');
        $templates = get_option('gmb_schema_templates', array());
        foreach ($templates as $tpl) {
            if (isset($tpl['id']) && $tpl['id'] === $id) {
                wp_send_json_success(array('template' => $tpl));
            }
        }
        wp_send_json_error(array('message' => 'Template not found.'));
    }

    public function sanitize_array_setting($value) {
        if (!is_array($value)) {
            return array();
        }
        return array_map('sanitize_text_field', $value);
    }

    public function sanitize_redirects_rules($rules) {
        if (!is_array($rules)) {
            return array();
        }
        $sanitized = array();
        foreach ($rules as $rule) {
            if (is_array($rule)) {
                $sanitized[] = array(
                    'id' => isset($rule['id']) ? sanitize_text_field($rule['id']) : uniqid(),
                    'source' => isset($rule['source']) ? sanitize_text_field($rule['source']) : '',
                    'destination' => isset($rule['destination']) ? sanitize_text_field($rule['destination']) : '',
                    'code' => isset($rule['code']) ? intval($rule['code']) : 301,
                    'match_type' => isset($rule['match_type']) ? sanitize_text_field($rule['match_type']) : 'exact',
                    'status' => isset($rule['status']) ? sanitize_text_field($rule['status']) : 'active',
                    'hits' => isset($rule['hits']) ? intval($rule['hits']) : 0,
                    'created' => isset($rule['created']) ? intval($rule['created']) : time(),
                    'last_accessed' => isset($rule['last_accessed']) ? sanitize_text_field($rule['last_accessed']) : ''
                );
            }
        }
        return $sanitized;
    }

    public function sanitize_business_locations($locations) {
        if (!is_array($locations)) {
            return array();
        }
        $sanitized = array();
        foreach ($locations as $loc) {
            if (is_array($loc)) {
                $sanitized[] = array(
                    'id' => isset($loc['id']) ? sanitize_text_field($loc['id']) : uniqid(),
                    'name' => isset($loc['name']) ? sanitize_text_field($loc['name']) : '',
                    'phone' => isset($loc['phone']) ? sanitize_text_field($loc['phone']) : '',
                    'address' => isset($loc['address']) ? sanitize_text_field($loc['address']) : ''
                );
            }
        }
        return $sanitized;
    }

    public function sanitize_robots_array($value) {
        if (is_array($value)) {
            return implode(', ', array_map('sanitize_text_field', $value));
        }
        return sanitize_text_field($value);
    }

    public static function sanitize_google_json_key($input) {
        if (!is_string($input)) {
            return '';
        }
        $trimmed = trim($input);
        if (empty($trimmed)) {
            delete_transient('gmb_google_indexing_token');
            return '';
        }
        // First attempt direct JSON decode without unslashing to avoid corrupting \n in PEM keys
        $data = json_decode($trimmed, true);
        if (!is_array($data) && function_exists('wp_unslash')) {
            $unslashed = wp_unslash($trimmed);
            $test_data = json_decode($unslashed, true);
            if (is_array($test_data)) {
                $data = $test_data;
            }
        }
        if (!is_array($data)) {
            if (function_exists('add_settings_error')) {
                add_settings_error('gmb_ranker_google_json_key', 'invalid_json', __('Invalid JSON syntax. Please check or re-upload your Google Service Account key file.', 'gmb-ranker-seo-automation'));
            }
            return get_option('gmb_ranker_google_json_key', '');
        }
        if (empty($data['client_email']) || empty($data['private_key']) || (isset($data['type']) && $data['type'] !== 'service_account')) {
            if (function_exists('add_settings_error')) {
                add_settings_error('gmb_ranker_google_json_key', 'missing_fields', __('Service Account JSON is missing client_email, private_key, or type is not service_account.', 'gmb-ranker-seo-automation'));
            }
            return get_option('gmb_ranker_google_json_key', '');
        }
        delete_transient('gmb_google_indexing_token');
        return wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function ajax_save_wizard_api_key() {
        $this->enforce_ajax_csrf_protection();
        if (isset($_POST['api_key'])) {
            update_option('gmb_ranker_api_key', sanitize_text_field(wp_unslash($_POST['api_key'])));
            wp_send_json_success();
        }
        wp_send_json_error('API key is required.');
    }

    public function ajax_add_redirect_rule() {
        $this->enforce_ajax_csrf_protection();

        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        $source = isset($_POST['source']) ? sanitize_text_field(wp_unslash($_POST['source'])) : '';
        $destination = isset($_POST['destination']) ? sanitize_text_field(wp_unslash($_POST['destination'])) : '';
        $code = isset($_POST['code']) ? intval(wp_unslash($_POST['code'])) : 301;
        $match_type = isset($_POST['match_type']) ? sanitize_text_field(wp_unslash($_POST['match_type'])) : 'exact';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active';
        $note = isset($_POST['note']) ? sanitize_text_field(wp_unslash($_POST['note'])) : '';

        if (empty($source) || empty($destination)) {
            wp_send_json_error('Please specify both source and destination URLs.');
        }

        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        $norm_source = strtolower(rtrim(trim($source), '/'));
        $updated = false;

        foreach ($rules as &$rule) {
            $r_id = isset($rule['id']) ? $rule['id'] : '';
            $r_src = isset($rule['source']) ? strtolower(rtrim(trim($rule['source']), '/')) : '';

            $match_by_id = (!empty($id) && $r_id === $id);
            $match_by_src = (!empty($r_src) && $r_src === $norm_source);

            if ($match_by_id || $match_by_src) {
                if (empty($rule['id'])) {
                    $rule['id'] = !empty($id) ? $id : uniqid('r_');
                }
                $rule['source']      = $source;
                $rule['destination'] = $destination;
                $rule['code']        = $code;
                $rule['match_type']  = $match_type;
                $rule['status']      = $status;
                $rule['note']        = $note;
                $updated             = true;
                break;
            }
        }

        if (!$updated) {
            $rules[] = array(
                'id'            => !empty($id) ? $id : uniqid('r_'),
                'source'        => $source,
                'destination'   => $destination,
                'code'          => $code,
                'match_type'    => $match_type,
                'status'        => $status,
                'note'          => $note,
                'hits'          => 0,
                'created'       => time(),
                'last_accessed' => ''
            );
        }

        // Run deduplication and cleanup
        $rules = $this->sanitize_redirects_rules($rules);
        update_option('gmb_ranker_redirects_rules', $rules);
        wp_send_json_success($rules);
    }

    public function ajax_delete_single_404_log() {
        $this->enforce_ajax_csrf_protection();

        $uri = isset($_POST['uri']) ? sanitize_text_field(wp_unslash($_POST['uri'])) : '';
        $logs = get_option('gmb_ranker_404_logs', array());
        if (is_array($logs) && !empty($uri)) {
            $filtered = array_filter($logs, function($item) use ($uri) {
                return isset($item['uri']) && $item['uri'] !== $uri;
            });
            update_option('gmb_ranker_404_logs', array_values($filtered));
        }
        wp_send_json_success();
    }

    public function ajax_bulk_redirect_actions() {
        $this->enforce_ajax_csrf_protection();

        $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bulk_action'])) : '';
        $ids = isset($_POST['ids']) ? array_map('sanitize_text_field', (array)$_POST['ids']) : array();

        if (empty($ids) || empty($bulk_action)) {
            wp_send_json_error('Invalid bulk action selection.');
        }

        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        if ($bulk_action === 'delete') {
            $rules = array_values(array_filter($rules, function($rule) use ($ids) {
                return !in_array($rule['id'], $ids, true);
            }));
        } elseif ($bulk_action === 'activate') {
            foreach ($rules as &$rule) {
                if (in_array($rule['id'], $ids, true)) {
                    $rule['status'] = 'active';
                }
            }
        } elseif ($bulk_action === 'deactivate') {
            foreach ($rules as &$rule) {
                if (in_array($rule['id'], $ids, true)) {
                    $rule['status'] = 'inactive';
                }
            }
        } elseif ($bulk_action === 'reset_hits') {
            foreach ($rules as &$rule) {
                if (in_array($rule['id'], $ids, true)) {
                    $rule['hits'] = 0;
                }
            }
        }

        update_option('gmb_ranker_redirects_rules', $rules);
        wp_send_json_success();
    }

    public function ajax_bulk_import_redirects_text() {
        $this->enforce_ajax_csrf_protection();

        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $default_match = isset($_POST['match_type']) ? sanitize_text_field(wp_unslash($_POST['match_type'])) : 'exact';

        if (empty($text)) {
            wp_send_json_error('No redirection rules entered.');
        }

        $lines = explode("\n", $text);
        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        $imported_count = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $src = sanitize_text_field($parts[0]);
                $dest = sanitize_text_field($parts[1]);
                $code = isset($parts[2]) ? intval($parts[2]) : 301;
                if (!in_array($code, array(301, 302, 307, 410, 451), true)) {
                    $code = 301;
                }

                $rules[] = array(
                    'id' => uniqid('r_'),
                    'source' => $src,
                    'destination' => $dest,
                    'code' => $code,
                    'match_type' => $default_match,
                    'status' => 'active',
                    'note' => 'Bulk imported',
                    'hits' => 0,
                    'created' => time(),
                    'last_accessed' => ''
                );
                $imported_count++;
            }
        }

        update_option('gmb_ranker_redirects_rules', $rules);
        wp_send_json_success($imported_count);
    }

    public function handle_export_redirects_download() {
        if (isset($_GET['gmb_action']) && ($_GET['gmb_action'] === 'export_redirects_json' || $_GET['gmb_action'] === 'export_redirects_csv')) {
            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized access.');
            }

            $rules = get_option('gmb_ranker_redirects_rules', array());
            if (!is_array($rules)) {
                $rules = array();
            }

            if ($_GET['gmb_action'] === 'export_redirects_json') {
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename="gmb-ranker-redirects-' . gmdate('Y-m-d') . '.json"');
                echo wp_json_encode($rules, JSON_PRETTY_PRINT);
                exit;
            } elseif ($_GET['gmb_action'] === 'export_redirects_csv') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="gmb-ranker-redirects-' . gmdate('Y-m-d') . '.csv"');
                $output = fopen('php://output', 'w');
                fputcsv($output, array('Source', 'Destination', 'Code', 'Match Type', 'Status', 'Hits', 'Note'));
                foreach ($rules as $r) {
                    fputcsv($output, array(
                        isset($r['source']) ? $r['source'] : '',
                        isset($r['destination']) ? $r['destination'] : '',
                        isset($r['code']) ? $r['code'] : 301,
                        isset($r['match_type']) ? $r['match_type'] : 'exact',
                        isset($r['status']) ? $r['status'] : 'active',
                        isset($r['hits']) ? $r['hits'] : 0,
                        isset($r['note']) ? $r['note'] : '',
                    ));
                }
                fclose($output);
                exit;
            }
        }
    }

    public function ajax_toggle_redirect_rule() {
        $this->enforce_ajax_csrf_protection();

        $id = sanitize_text_field(wp_unslash($_POST['id']));
        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        foreach ($rules as &$rule) {
            if ($rule['id'] === $id) {
                $status = isset($rule['status']) ? $rule['status'] : 'active';
                $rule['status'] = ($status === 'active') ? 'inactive' : 'active';
                break;
            }
        }

        update_option('gmb_ranker_redirects_rules', $rules);
        wp_send_json_success($rules);
    }

    public function ajax_toggle_dashboard_module() {
        $this->enforce_ajax_csrf_protection();

        $module = sanitize_text_field(wp_unslash($_POST['module']));
        $value  = sanitize_text_field(wp_unslash($_POST['value']));

        $valid_modules = array(
            'gmb_ranker_module_metadata',
            'gmb_ranker_module_sitemaps',
            'gmb_ranker_module_redirects',
            'gmb_ranker_module_schema',
            'gmb_ranker_module_preferred_source',
            'gmb_ranker_module_image_seo',
            'gmb_ranker_module_links',
            'gmb_ranker_module_db_tools',
            'gmb_ranker_module_role_manager',
            'gmb_ranker_module_instant_indexing',
            'gmb_ranker_module_local_seo',
            'gmb_ranker_module_seo_analysis',
            'gmb_ranker_module_security',
            'gmb_ranker_module_llmstxt',
            'gmb_ranker_module_ai_provider',
            'gmb_ranker_module_toc'
        );

        if (!in_array($module, $valid_modules)) {
            wp_send_json_error('Invalid module.');
        }

        $status = ($value === '1') ? '1' : '0';
        update_option($module, $status);

        wp_send_json_success(array(
            'module' => $module,
            'status' => $status
        ));
    }

    public function ajax_delete_redirect_rule() {
        $this->enforce_ajax_csrf_protection();

        $id = sanitize_text_field(wp_unslash($_POST['id']));
        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        $filtered = array_filter($rules, function($rule) use ($id) {
            return $rule['id'] !== $id;
        });

        update_option('gmb_ranker_redirects_rules', array_values($filtered));
        wp_send_json_success(array_values($filtered));
    }

    public function ajax_clear_404_logs() {
        $this->enforce_ajax_csrf_protection();

        delete_option('gmb_ranker_404_logs');
        wp_send_json_success();
    }

    public function ajax_db_optimize_tables() {
        $this->enforce_ajax_csrf_protection();

        if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
            $result = GMB_Ranker_SEO_DB_Tools::optimize_tables();
            wp_send_json_success($result);
        }
        wp_send_json_error('Database tools class is missing.');
    }

    public function ajax_db_clear_orphan_meta() {
        $this->enforce_ajax_csrf_protection();

        if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
            $count = GMB_Ranker_SEO_DB_Tools::clear_orphan_meta();
            wp_send_json_success($count);
        }
        wp_send_json_error('Database tools class is missing.');
    }

    public function ajax_db_clear_transients() {
        $this->enforce_ajax_csrf_protection();

        if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
            $count = GMB_Ranker_SEO_DB_Tools::clear_transients();
            wp_send_json_success($count);
        }
        wp_send_json_error('Database tools class is missing.');
    }

    public function ajax_db_import_rankmath() {
        $this->enforce_ajax_csrf_protection();

        global $wpdb;
        
        $query = "
            SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key IN (
                'rank_math_focus_keyword', 
                'rank_math_title', 
                'rank_math_description', 
                'rank_math_canonical_url', 
                'rank_math_robots'
            )
        ";
        
        $post_ids = $wpdb->get_col($query);
        
        if (empty($post_ids)) {
            wp_send_json_success(0);
        }
        
        $imported_count = 0;
        
        foreach ($post_ids as $post_id) {
            $imported_flag = false;
            
            // 1. Focus Keyword
            $rm_keyword = get_post_meta($post_id, 'rank_math_focus_keyword', true);
            if (!empty($rm_keyword)) {
                update_post_meta($post_id, '_gmb_ranker_focus_keyword', sanitize_text_field($rm_keyword));
                $imported_flag = true;
            }
            
            // 2. SEO Title
            $rm_title = get_post_meta($post_id, 'rank_math_title', true);
            if (!empty($rm_title)) {
                update_post_meta($post_id, '_gmb_ranker_seo_title', sanitize_text_field($rm_title));
                $imported_flag = true;
            }
            
            // 3. SEO Description
            $rm_desc = get_post_meta($post_id, 'rank_math_description', true);
            if (!empty($rm_desc)) {
                update_post_meta($post_id, '_gmb_ranker_seo_description', sanitize_textarea_field($rm_desc));
                $imported_flag = true;
            }
            
            // 4. Canonical URL
            $rm_canonical = get_post_meta($post_id, 'rank_math_canonical_url', true);
            if (!empty($rm_canonical)) {
                update_post_meta($post_id, '_gmb_ranker_seo_canonical', esc_url_raw($rm_canonical));
                $imported_flag = true;
            }
            
            // 5. Robots
            $rm_robots = get_post_meta($post_id, 'rank_math_robots', true);
            if (!empty($rm_robots)) {
                $robots_str = '';
                if (is_array($rm_robots)) {
                    $robots_str = implode(', ', array_map('sanitize_text_field', $rm_robots));
                } else if (is_string($rm_robots)) {
                    $robots_str = sanitize_text_field($rm_robots);
                }
                if (!empty($robots_str)) {
                    update_post_meta($post_id, '_gmb_ranker_seo_robots', $robots_str);
                    $imported_flag = true;
                }
            }
            
            if ($imported_flag) {
                $imported_count++;
            }
        }
        
        wp_send_json_success($imported_count);
    }

    public function ajax_save_role_permissions() {
        $this->enforce_ajax_csrf_protection();

        $matrix = isset($_POST['matrix']) && is_array($_POST['matrix']) ? wp_unslash($_POST['matrix']) : array();
        $target_roles = array('editor', 'author', 'contributor');
        $caps = array('gmb_ranker_manage_settings', 'gmb_ranker_edit_metadata', 'gmb_ranker_manage_redirects');

        foreach ($target_roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap) {
                    $has_cap = isset($matrix[$role_name][$cap]) && $matrix[$role_name][$cap] === '1';
                    if ($has_cap) {
                        $role->add_cap($cap);
                    } else {
                        $role->remove_cap($cap);
                    }
                }
            }
        }
        wp_send_json_success();
    }

    public function ajax_instant_index_submit() {
        $this->enforce_ajax_csrf_protection();

        $urls_str = isset($_POST['urls']) ? sanitize_textarea_field(wp_unslash($_POST['urls'])) : '';
        $lines = explode("\n", $urls_str);
        $urls = array();
        foreach ($lines as $line) {
            $u = trim($line);
            if (!empty($u) && strpos($u, 'http') === 0) {
                $urls[] = esc_url_raw($u);
            }
        }

        if (empty($urls)) {
            wp_send_json_error('Please enter at least one valid URL.');
        }

        if (class_exists('GMB_Ranker_SEO_Instant_Indexing')) {
            $result = GMB_Ranker_SEO_Instant_Indexing::submit_urls($urls);
            if ($result) {
                wp_send_json_success();
            } else {
                wp_send_json_error('IndexNow returned an error. Verify your site ownership key file.');
            }
        }
        wp_send_json_error('Instant Indexing class is missing.');
    }

    public function handle_export_settings_download() {
        if (isset($_GET['gmb_action']) && $_GET['gmb_action'] === 'export_settings') {
            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized access.');
            }
            
            $keys = array(
                'gmb_ranker_api_key',
                'gmb_local_business_name',
                'gmb_local_business_phone',
                'gmb_local_business_address',
                'gmb_local_use_multiple_locations',
                'gmb_local_business_locations',
                'gmb_metadata_post_title_template',
                'gmb_metadata_post_desc_template',
                'gmb_metadata_page_title_template',
                'gmb_metadata_page_desc_template',
                'gmb_image_seo_alt_template',
                'gmb_image_seo_title_template',
                'gmb_links_exclude_domains',
                'gmb_redirect_attachments',
                'gmb_strip_category_base',
                'gmb_nofollow_external_links',
                'gmb_new_window_external_links',
                'gmb_redirect_orphan_attachments',
                'gmb_nofollow_image_links',
                'gmb_affiliate_link_prefixes',
                'gmb_ranker_redirects_rules',
                'gmb_ranker_module_metadata',
                'gmb_ranker_module_sitemaps',
                'gmb_ranker_module_redirects',
                'gmb_ranker_module_schema',
                'gmb_ranker_module_preferred_source',
                'gmb_ranker_module_image_seo',
                'gmb_ranker_module_db_tools',
                'gmb_ranker_module_role_manager',
                'gmb_ranker_module_instant_indexing',
                'gmb_ranker_module_links',
                'gmb_ranker_module_local_seo',
                'gmb_ranker_module_seo_analysis',
                'gmb_ranker_module_llmstxt',
                'gmb_ranker_module_ai_provider',
                'gmb_llms_title',
                'gmb_llms_desc',
                'gmb_llms_limit',
                'gmb_llms_exclusions',
                'gmb_llms_exclude_types',
                'gmb_llms_post_types',
                'gmb_llms_taxonomies',
                'gmb_llms_additional_content',
                'gmb_ai_provider',
                'gmb_ai_openrouter_key',
                'gmb_ai_openrouter_model',
                'gmb_ai_groq_key',
                'gmb_ai_groq_model',
                'gmb_ai_ollama_url',
                'gmb_ai_ollama_model'
            );
            
            $export_data = array();
            foreach ($keys as $key) {
                $export_data[$key] = get_option($key);
            }
            
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="gmb-ranker-settings-' . gmdate('Y-m-d') . '.json"');
            echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
            exit;
        }
    }

    public function ajax_import_settings_upload() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'gmb-ranker-seo-automation'));
        }
        
        if (!isset($_FILES['settings_file'])) {
            wp_send_json_error(__('No backup file was uploaded.', 'gmb-ranker-seo-automation'));
        }
        
        $file = $_FILES['settings_file'];
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('Failed to upload files.', 'gmb-ranker-seo-automation'));
        }

        $check = wp_check_filetype($file['name'], array('json' => 'application/json'));
        if (empty($check['ext']) && empty($check['type'])) {
            wp_send_json_error(__('Uploaded file must be a valid .json file.', 'gmb-ranker-seo-automation'));
        }
        
        $content = file_get_contents($file['tmp_name']);
        $data = json_decode($content, true);
        
        if (!is_array($data)) {
            wp_send_json_error(__('Invalid backup settings file format.', 'gmb-ranker-seo-automation'));
        }
        
        $allowed_prefixes = array('gmb_ranker_module_', 'gmb_seo_', 'gmb_ai_', 'gmb_instant_', 'gmb_local_');
        foreach ($data as $key => $value) {
            $clean_key = sanitize_key($key);
            $is_allowed = false;
            foreach ($allowed_prefixes as $pref) {
                if (strpos($clean_key, $pref) === 0) {
                    $is_allowed = true;
                    break;
                }
            }
            if ($is_allowed) {
                $clean_val = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_textarea_field($value);
                update_option($clean_key, $clean_val);
            }
        }
        
        wp_send_json_success(array('message' => __('Settings successfully imported.', 'gmb-ranker-seo-automation')));
    }

    public function ajax_db_import_yoast() {
        $this->enforce_ajax_csrf_protection();

        global $wpdb;
        
        $query = "
            SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key IN (
                '_yoast_wpseo_focuskw', 
                '_yoast_wpseo_title', 
                '_yoast_wpseo_metadesc', 
                '_yoast_wpseo_canonical', 
                '_yoast_wpseo_meta-robots-noindex'
            )
        ";
        
        $post_ids = $wpdb->get_col($query);
        
        if (empty($post_ids)) {
            wp_send_json_success(0);
        }
        
        $imported_count = 0;
        
        foreach ($post_ids as $post_id) {
            $imported_flag = false;
            
            // 1. Focus Keyword
            $yoast_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            if (!empty($yoast_keyword)) {
                update_post_meta($post_id, '_gmb_ranker_focus_keyword', sanitize_text_field($yoast_keyword));
                $imported_flag = true;
            }
            
            // 2. SEO Title
            $yoast_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
            if (!empty($yoast_title)) {
                update_post_meta($post_id, '_gmb_ranker_seo_title', sanitize_text_field($yoast_title));
                $imported_flag = true;
            }
            
            // 3. SEO Description
            $yoast_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            if (!empty($yoast_desc)) {
                update_post_meta($post_id, '_gmb_ranker_seo_description', sanitize_textarea_field($yoast_desc));
                $imported_flag = true;
            }
            
            // 4. Canonical URL
            $yoast_canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
            if (!empty($yoast_canonical)) {
                update_post_meta($post_id, '_gmb_ranker_seo_canonical', esc_url_raw($yoast_canonical));
                $imported_flag = true;
            }
            
            // 5. Robots
            $yoast_noindex = get_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', true);
            if ($yoast_noindex === '1') {
                update_post_meta($post_id, '_gmb_ranker_seo_robots', 'noindex');
                $imported_flag = true;
            }
            
            if ($imported_flag) {
                $imported_count++;
            }
        }
        
        wp_send_json_success($imported_count);
    }

    public function ajax_add_local_location() {
        $this->enforce_ajax_csrf_protection();

        $name = sanitize_text_field(wp_unslash($_POST['name']));
        $phone = sanitize_text_field(wp_unslash($_POST['phone']));
        $address = sanitize_textarea_field(wp_unslash($_POST['address']));

        if (empty($name)) {
            wp_send_json_error('Location name is required.');
        }

        $locations = get_option('gmb_local_business_locations', array());
        $locations[] = array(
            'id' => uniqid(),
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        );

        update_option('gmb_local_business_locations', $locations);
        wp_send_json_success($locations);
    }

    public function ajax_delete_local_location() {
        $this->enforce_ajax_csrf_protection();

        $id = sanitize_text_field(wp_unslash($_POST['id']));
        $locations = get_option('gmb_local_business_locations', array());

        $filtered = array_filter($locations, function($loc) use ($id) {
            return $loc['id'] !== $id;
        });

        update_option('gmb_local_business_locations', array_values($filtered));
        wp_send_json_success(array_values($filtered));
    }



    public function ajax_ai_suggest_404_redirects() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        $single_uri = isset($_POST['uri']) ? sanitize_text_field(wp_unslash($_POST['uri'])) : '';
        $targets = array();

        if (!empty($single_uri)) {
            $targets[] = $single_uri;
        } else {
            $logs = get_option('gmb_ranker_404_logs', array());
            if (is_array($logs) && !empty($logs)) {
                $count = 0;
                foreach ($logs as $l) {
                    if (isset($l['uri']) && !empty($l['uri'])) {
                        $targets[] = $l['uri'];
                        $count++;
                        if ($count >= 25) break;
                    }
                }
            }
        }

        if (empty($targets)) {
            wp_send_json_error('No 404 log entries available for AI analysis.');
        }

        // Gather live site published pages
        $live_pages = array();
        $posts = get_posts(array(
            'post_type'      => array('page', 'post', 'service', 'services', 'product', 'portfolio', 'case_studies'),
            'post_status'    => 'publish',
            'posts_per_page' => 100,
        ));

        foreach ($posts as $p) {
            $permalink = get_permalink($p->ID);
            $path = wp_parse_url($permalink, PHP_URL_PATH) ?: '/';
            $live_pages[] = array(
                'title' => get_the_title($p->ID),
                'url'   => $path,
            );
        }

        // Add homepage
        $live_pages[] = array(
            'title' => 'Home Page',
            'url'   => '/',
        );

        // Add categories
        $categories = get_categories(array('hide_empty' => false, 'number' => 20));
        foreach ($categories as $cat) {
            $link = get_category_link($cat->term_id);
            $path = wp_parse_url($link, PHP_URL_PATH) ?: '/';
            $live_pages[] = array(
                'title' => $cat->name,
                'url'   => $path,
            );
        }

        $suggestions = array();
        $ai_used = false;

        // Try AI completion first if AI provider is available
        if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
            $system_prompt = "You are an expert SEO AI assistant. Match broken 404 URLs to the most semantically relevant live site URL. For security scans or junk files (.env, .json, .php scripts, wp-admin), set destination to '' and code to 410. Return ONLY a raw valid JSON array of objects with keys: source (string), destination (string), code (301 or 410), confidence (high/medium/low), reason (short string). Do NOT wrap in markdown backticks or commentary.";

            $user_prompt = "404 Broken Request URLs:\n" . wp_json_encode($targets) . "\n\nAvailable Live Site Pages:\n" . wp_json_encode($live_pages);

            $messages = array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user', 'content' => $user_prompt)
            );

            $response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.2);
            if (!is_wp_error($response) && !empty($response['choices'][0]['message']['content'])) {
                $raw = trim($response['choices'][0]['message']['content']);
                $raw = preg_replace('/^```(?:json)?/i', '', $raw);
                $raw = preg_replace('/```$/', '', $raw);
                $raw = trim($raw);
                $decoded = json_decode($raw, true);

                if (is_array($decoded) && !empty($decoded)) {
                    $suggestions = $decoded;
                    $ai_used = true;
                }
            }
        }

        // Algorithmic Smart Fallback if AI was unavailable or failed
        if (empty($suggestions)) {
            foreach ($targets as $uri) {
                $best_match = '/';
                $highest_sim = 0;
                $slug = trim(strtolower(basename($uri)));
                $slug_clean = str_replace(array('-', '_', '.html', '.php'), ' ', $slug);

                $is_junk = (bool) preg_match('/\.(env|json|sql|log|txt|xml|bak|zip|gz|tar|php)$/i', $uri);

                if ($is_junk) {
                    $suggestions[] = array(
                        'source'      => $uri,
                        'destination' => '',
                        'code'        => 410,
                        'confidence'  => 'high',
                        'reason'      => 'Security scan or non-existent static file (410 Gone)',
                    );
                    continue;
                }

                foreach ($live_pages as $page) {
                    $page_url = $page['url'];
                    if ($page_url === '/') continue;

                    $page_slug = trim(strtolower(basename($page_url)));
                    $page_title_clean = strtolower($page['title']);

                    similar_text($slug_clean, $page_slug, $sim_slug);
                    similar_text($slug_clean, $page_title_clean, $sim_title);
                    $sim = max($sim_slug, $sim_title);

                    if ($sim > $highest_sim) {
                        $highest_sim = $sim;
                        $best_match = $page_url;
                    }
                }

                $confidence = ($highest_sim > 60) ? 'high' : (($highest_sim > 35) ? 'medium' : 'low');
                $reason = ($highest_sim > 35) ? 'Smart slug & semantic title match' : 'Fallback to homepage';
                if ($highest_sim <= 35) {
                    $best_match = '/';
                }

                $suggestions[] = array(
                    'source'      => $uri,
                    'destination' => $best_match,
                    'code'        => 301,
                    'confidence'  => $confidence,
                    'reason'      => $reason,
                );
            }
        }

        wp_send_json_success(array(
            'suggestions' => $suggestions,
            'ai_used'     => $ai_used,
            'provider'    => class_exists('GMB_Ranker_SEO_AI_Provider') ? GMB_Ranker_SEO_AI_Provider::get_active_provider() : 'Smart Matcher',
        ));
    }

    public function ajax_apply_ai_redirects() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        $rules_json = isset($_POST['rules']) ? wp_unslash($_POST['rules']) : '';
        $rules_data = json_decode($rules_json, true);

        if (!is_array($rules_data) || empty($rules_data)) {
            wp_send_json_error('No valid rules supplied for AI batch import.');
        }

        $existing_rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($existing_rules)) {
            $existing_rules = array();
        }

        $applied_count = 0;
        $applied_sources = array();

        foreach ($rules_data as $rule) {
            $source = isset($rule['source']) ? sanitize_text_field($rule['source']) : '';
            $destination = isset($rule['destination']) ? sanitize_text_field($rule['destination']) : '';
            $code = isset($rule['code']) ? intval($rule['code']) : 301;

            if (empty($source)) continue;

            $existing_rules[] = array(
                'id'            => uniqid('ai_r_'),
                'source'        => $source,
                'destination'   => $destination,
                'code'          => $code,
                'match_type'    => 'exact',
                'status'        => 'active',
                'note'          => 'AI Auto-Generated Redirect Rule',
                'hits'          => 0,
                'created'       => time(),
                'last_accessed' => '',
            );

            $applied_sources[] = $source;
            $applied_count++;
        }

        // Deduplicate and sanitize rules
        $sanitized_rules = $this->sanitize_redirects_rules($existing_rules);
        update_option('gmb_ranker_redirects_rules', $sanitized_rules);

        // Remove applied 404 logs
        $logs = get_option('gmb_ranker_404_logs', array());
        if (is_array($logs) && !empty($applied_sources)) {
            $filtered_logs = array_filter($logs, function($l) use ($applied_sources) {
                return !isset($l['uri']) || !in_array($l['uri'], $applied_sources, true);
            });
            update_option('gmb_ranker_404_logs', array_values($filtered_logs));
        }

        wp_send_json_success(array(
            'message' => sprintf('%d AI redirection rules successfully created & 404 logs cleaned!', $applied_count),
            'count'   => $applied_count
        ));
    }

    public function ajax_test_outbound_webhook() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        $target_url = isset($_POST['target_url']) ? esc_url_raw(wp_unslash($_POST['target_url'])) : '';
        if (empty($target_url)) {
            wp_send_json_error('Please provide a valid target webhook URL.');
        }

        $secret = get_option('gmb_integration_webhook_secret', '');
        $payload = array(
            'event'     => 'test_ping',
            'site_name' => get_bloginfo('name'),
            'site_url'  => site_url(),
            'timestamp' => time(),
            'message'   => 'Test payload sent from GMB Ranker SEO WordPress Plugin'
        );

        $json_payload = wp_json_encode($payload);
        $signature = !empty($secret) ? hash_hmac('sha256', $json_payload, $secret) : '';

        $response = wp_remote_post($target_url, array(
            'headers' => array(
                'Content-Type'        => 'application/json; charset=utf-8',
                'X-GMB-Ranker-Event'  => 'test_ping',
                'X-GMB-Ranker-Sig'    => $signature,
            ),
            'body'    => $json_payload,
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 200 && $code < 300) {
            wp_send_json_success(array(
                'code'    => $code,
                'message' => 'Outbound webhook trigger tested successfully! Endpoint returned HTTP ' . $code
            ));
        } else {
            wp_send_json_error('Endpoint returned HTTP error code: ' . $code);
        }
    }

    public function ajax_ai_analyze_and_fix_post_seo() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        if (empty($title) && $post_id > 0) {
            $title = get_the_title($post_id);
        }

        $content_raw = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        if (empty($content_raw) && $post_id > 0) {
            $post_obj = get_post($post_id);
            if ($post_obj) {
                $content_raw = $post_obj->post_content;
            }
        }
        $content_clean = wp_strip_all_tags($content_raw);
        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';

        $cur_focus = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : '';
        $cur_seo_title = isset($_POST['seo_title']) ? sanitize_text_field(wp_unslash($_POST['seo_title'])) : '';
        $cur_meta_desc = isset($_POST['meta_description']) ? sanitize_textarea_field(wp_unslash($_POST['meta_description'])) : '';

        if (empty($title) && empty($content_clean)) {
            wp_send_json_error('Please enter a Post Title or Content body before running AI analysis.');
        }

        $target_country  = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : 'GLOBAL|google.com';
        $target_language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : 'en';
        $opt_mode        = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'optimize';

        if (class_exists('GMB_Ranker_SEO_Research_Engine')) {
            $research_data = GMB_Ranker_SEO_Research_Engine::run_research_pipeline(array(
                'post_id'       => $post_id,
                'title'         => $title,
                'content'       => $content_raw,
                'focus_keyword' => $cur_focus,
                'country'       => $target_country,
                'language'      => $target_language,
                'mode'          => $opt_mode,
            ));

            wp_send_json_success($research_data);
        }

        wp_send_json_error('Research engine not available.');
    }

    /**
     * Instantly persist applied AI SEO fields directly to database
     */
    public function ajax_quick_save_ai_seo_fields() {
        $this->enforce_ajax_csrf_protection();
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if ($post_id <= 0) {
            wp_send_json_error('Invalid Post ID');
        }

        $applied_count = 0;

        if (isset($_POST['focus_keyword'])) {
            $focus = sanitize_text_field(wp_unslash($_POST['focus_keyword']));
            update_post_meta($post_id, '_gmb_seo_focus_keyword', $focus);
            update_post_meta($post_id, '_gmb_ranker_focus_keyword', $focus);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus);
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus);
            $applied_count++;
        }

        if (isset($_POST['seo_title'])) {
            $title = sanitize_text_field(wp_unslash($_POST['seo_title']));
            update_post_meta($post_id, '_gmb_seo_title', $title);
            update_post_meta($post_id, '_gmb_ranker_seo_title', $title);
            update_post_meta($post_id, '_yoast_wpseo_title', $title);
            update_post_meta($post_id, 'rank_math_title', $title);
            $applied_count++;
        }

        if (isset($_POST['meta_description'])) {
            $desc = sanitize_textarea_field(wp_unslash($_POST['meta_description']));
            update_post_meta($post_id, '_gmb_seo_description', $desc);
            update_post_meta($post_id, '_gmb_ranker_seo_description', $desc);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
            update_post_meta($post_id, 'rank_math_description', $desc);
            $applied_count++;
        }

        if (isset($_POST['slug']) && !empty($_POST['slug'])) {
            $slug = sanitize_title(wp_unslash($_POST['slug']));
            wp_update_post(array(
                'ID'        => $post_id,
                'post_name' => $slug,
            ));
            $applied_count++;
        }

        if (isset($_POST['schema_type']) && !empty($_POST['schema_type'])) {
            $schema = sanitize_text_field(wp_unslash($_POST['schema_type']));
            update_post_meta($post_id, '_gmb_seo_schema_type', $schema);
            update_post_meta($post_id, '_gmb_ranker_active_schemas', array($schema));
            update_post_meta($post_id, '_gmb_ranker_schema_type', $schema);
            $applied_count++;
        }

        if (isset($_POST['content_intro']) && !empty($_POST['content_intro'])) {
            $intro_text = wp_kses_post(wp_unslash($_POST['content_intro']));
            $focus_val = isset($_POST['focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['focus_keyword'])) : get_post_meta($post_id, '_gmb_ranker_focus_keyword', true);
            $post_obj = get_post($post_id);
            if ($post_obj) {
                $cur_content = $post_obj->post_content;
                // Add H2 heading with Focus Keyword if no existing heading contains focus keyword
                if (!empty($focus_val) && !preg_match('/<h[2-4][^>]*>.*?' . preg_quote($focus_val, '/') . '.*?<\/h[2-4]>/i', $cur_content)) {
                    $heading_block = '<h2>Key ' . ucwords($focus_val) . ' Overview & Solutions</h2>' . "\n";
                    if (strpos($intro_text, '<h2>') === false) {
                        $intro_text = $heading_block . $intro_text;
                    }
                }
                
                if (preg_match('/<p[^>]*>.*?<\/p>/is', $cur_content)) {
                    $new_content = preg_replace('/<p[^>]*>.*?<\/p>/is', '<p>' . $intro_text . '</p>', $cur_content, 1);
                } else {
                    $new_content = '<p>' . $intro_text . '</p>' . "\n\n" . $cur_content;
                }
                wp_update_post(array(
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                ));
                $applied_count++;
            }
        }

        wp_send_json_success(array(
            'applied_count' => $applied_count,
            'message'       => sprintf('Applied %d selected optimization(s) directly to database!', $applied_count),
        ));
    }
}
