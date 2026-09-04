<?php
/**
 * Redirects AJAX Handler for GMB Ranker SEO Automation
 *
 * Enterprise-grade, secure, validated AJAX handler for managing
 * URL redirect rules and 404 access logs.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Ajax_Redirects_Handler')) {

    class GMB_Ranker_SEO_Ajax_Redirects_Handler {

        /**
         * @var GMB_Ranker_SEO_Redirect_Service
         */
        protected $service;

        /**
         * @var GMB_Ranker_SEO_Redirect_Repository
         */
        protected $repository;

        /**
         * Allowed HTTP Redirect Status Codes
         *
         * @var array<int>
         */
        protected static $allowed_status_codes = array(301, 302, 307, 308, 410, 451);

        /**
         * Allowed Redirect Match Types
         *
         * @var array<string>
         */
        protected static $allowed_types = array('exact', 'contains', 'regex');

        /**
         * Constructor
         *
         * @param GMB_Ranker_SEO_Redirect_Service|null $service
         * @param GMB_Ranker_SEO_Redirect_Repository|null $repository
         */
        public function __construct(GMB_Ranker_SEO_Redirect_Service $service = null, GMB_Ranker_SEO_Redirect_Repository $repository = null) {
            $this->service    = $service ?: new GMB_Ranker_SEO_Redirect_Service();
            $this->repository = $repository ?: new GMB_Ranker_SEO_Redirect_Repository();
        }

        /**
         * Enforce Admin Capability & Nonce Security
         */
        protected function verify_ajax_security() {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
            }
            $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : (isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : (isset($_REQUEST['security']) ? sanitize_text_field(wp_unslash($_REQUEST['security'])) : ''));

            $valid_nonces = array(
                'gmb_admin_ajax_nonce',
                'gmb_ranker_ajax_nonce',
                'gmb_seo_save_nonce',
                'gmb_toggle_module_nonce',
                'gmb_wizard_nonce'
            );

            $verified = false;
            if (!empty($nonce)) {
                foreach ($valid_nonces as $action_nonce) {
                    if (wp_verify_nonce($nonce, $action_nonce)) {
                        $verified = true;
                        break;
                    }
                }
            }

            if (!$verified) {
                wp_send_json_error(array('message' => 'Invalid security token.'), 403);
            }
        }

        /**
         * Sanitize and Validate Target URL/Path
         *
         * @param string $url
         * @return string|false
         */
        protected function validate_redirect_target($url) {
            $trimmed = trim($url);
            if (empty($trimmed)) {
                return false;
            }

            // Reject dangerous URI schemes
            if (preg_match('/^(javascript|data|file|vbscript):/i', $trimmed)) {
                return false;
            }

            // Allow relative paths starting with /
            if (strpos($trimmed, '/') === 0) {
                return esc_url_raw($trimmed);
            }

            // Validate absolute URLs
            if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
                $parsed = wp_parse_url($trimmed);
                if (isset($parsed['scheme']) && in_array(strtolower($parsed['scheme']), array('http', 'https'), true)) {
                    return esc_url_raw($trimmed);
                }
            }

            return false;
        }

        /**
         * Handle Add Redirect Rule
         */
        public function handle_add_redirect_rule() {
            $this->verify_ajax_security();

            $id_raw     = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
            $source_raw = isset($_POST['source']) ? wp_unslash($_POST['source']) : '';
            $target_raw = isset($_POST['destination']) ? wp_unslash($_POST['destination']) : (isset($_POST['target']) ? wp_unslash($_POST['target']) : '');
            $code_raw   = isset($_POST['code']) ? intval(wp_unslash($_POST['code'])) : 301;
            $type_raw   = isset($_POST['match_type']) ? sanitize_text_field(wp_unslash($_POST['match_type'])) : (isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'exact');
            $status_raw = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active';
            $note_raw   = isset($_POST['note']) ? sanitize_text_field(wp_unslash($_POST['note'])) : '';

            $source = class_exists('GMB_Ranker_SEO_Redirect_Registry') ? GMB_Ranker_SEO_Redirect_Registry::validate_source_url($source_raw) : sanitize_text_field($source_raw);
            if (empty($source)) {
                wp_send_json_error(array('message' => 'Source URL or path is required.'), 400);
            }

            $code   = in_array($code_raw, array(301, 302, 307, 308, 410, 451), true) ? $code_raw : 301;
            $target = class_exists('GMB_Ranker_SEO_Redirect_Registry') ? GMB_Ranker_SEO_Redirect_Registry::validate_destination_url($target_raw, $code) : $this->validate_redirect_target($target_raw);
            
            if ($target === false && !in_array($code, array(410, 451), true)) {
                wp_send_json_error(array('message' => 'Destination URL is invalid or unsafe.'), 400);
            }

            if (class_exists('GMB_Ranker_SEO_Redirect_Registry') && GMB_Ranker_SEO_Redirect_Registry::is_redirect_loop($source, $target)) {
                wp_send_json_error(array('message' => 'Redirect loop detected. Source and destination URLs must be different.'), 400);
            }

            $type = in_array($type_raw, array('exact', 'contains', 'start', 'end', 'regex'), true) ? $type_raw : 'exact';

            if ($type === 'regex' && class_exists('GMB_Ranker_SEO_Redirect_Registry')) {
                if (!GMB_Ranker_SEO_Redirect_Registry::validate_regex_pattern($source)) {
                    wp_send_json_error(array('message' => 'Invalid or unsafe regular expression pattern supplied.'), 400);
                }
            }

            $rule_id = !empty($id_raw) ? $id_raw : ('rule_' . substr(md5(uniqid(wp_rand(), true)), 0, 8));

            $rule = array(
                'id'          => $rule_id,
                'source'      => $source,
                'destination' => $target,
                'target'      => $target,
                'code'        => $code,
                'match_type'  => $type,
                'type'        => $type,
                'status'      => $status_raw === 'inactive' ? 'inactive' : 'active',
                'enabled'     => $status_raw === 'inactive' ? 0 : 1,
                'note'        => $note_raw,
                'hits'        => 0,
                'created_at'  => current_time('mysql'),
            );

            $saved = $this->repository->save_rule($rule);
            if ($saved) {
                wp_send_json_success(array('message' => 'Redirect rule saved successfully.', 'rule' => $rule));
            } else {
                wp_send_json_error(array('message' => 'Failed to save redirect rule.'), 500);
            }
        }

        /**
         * Handle Toggle Redirect Rule
         */
        public function handle_toggle_redirect_rule() {
            $this->verify_ajax_security();

            $rule_id = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';
            if (empty($rule_id)) {
                wp_send_json_error(array('message' => 'Rule ID is required.'), 400);
            }

            $enabled_raw = isset($_POST['enabled']) ? wp_unslash($_POST['enabled']) : 0;
            $enabled = filter_var($enabled_raw, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            $rules = $this->repository->get_all_rules();
            $found = false;

            foreach ($rules as &$rule) {
                if (isset($rule['id']) && $rule['id'] === $rule_id) {
                    $rule['enabled'] = $enabled;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                wp_send_json_error(array('message' => 'Redirect rule not found.'), 404);
            }

            $saved = $this->repository->save_rules($rules);
            if ($saved) {
                wp_send_json_success(array('message' => 'Redirect status updated.', 'enabled' => $enabled));
            } else {
                wp_send_json_error(array('message' => 'Failed to update redirect rule status.'), 500);
            }
        }

        /**
         * Handle Delete Redirect Rule
         */
        public function handle_delete_redirect_rule() {
            $this->verify_ajax_security();

            $rule_id = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';
            if (empty($rule_id)) {
                wp_send_json_error(array('message' => 'Rule ID is required.'), 400);
            }

            $rules = $this->repository->get_all_rules();
            $found = false;

            foreach ($rules as $rule) {
                if (isset($rule['id']) && $rule['id'] === $rule_id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                wp_send_json_error(array('message' => 'Redirect rule not found or already deleted.'), 404);
            }

            $deleted = $this->repository->delete_rule($rule_id);
            if ($deleted) {
                wp_send_json_success(array('message' => 'Redirect rule deleted successfully.'));
            } else {
                wp_send_json_error(array('message' => 'Failed to delete redirect rule.'), 500);
            }
        }

        /**
         * Handle Clear 404 Logs
         */
        public function handle_clear_404_logs() {
            $this->verify_ajax_security();

            $cleared = $this->repository->clear_404_logs();
            if ($cleared) {
                wp_send_json_success(array('message' => '404 logs cleared successfully.'));
            } else {
                wp_send_json_success(array('message' => '404 logs are already empty.'));
            }
        }
    }
}
