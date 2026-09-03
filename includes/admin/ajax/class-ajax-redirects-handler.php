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
            if (!check_ajax_referer('gmb_admin_ajax_nonce', 'nonce', false)) {
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

            $source_raw = isset($_POST['source']) ? wp_unslash($_POST['source']) : '';
            $target_raw = isset($_POST['target']) ? wp_unslash($_POST['target']) : '';
            $code_raw   = isset($_POST['code']) ? intval(wp_unslash($_POST['code'])) : 301;
            $type_raw   = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'exact';

            $source = trim(sanitize_text_field($source_raw));
            if (empty($source)) {
                wp_send_json_error(array('message' => 'Source URL or path is required.'), 400);
            }

            $target = $this->validate_redirect_target($target_raw);
            if (!$target) {
                wp_send_json_error(array('message' => 'Target URL is invalid or unsafe.'), 400);
            }

            $code = in_array($code_raw, self::$allowed_status_codes, true) ? $code_raw : 301;
            $type = in_array($type_raw, self::$allowed_types, true) ? $type_raw : 'exact';

            // Check for duplicate source rules
            $existing_rules = $this->repository->get_all_rules();
            foreach ($existing_rules as $rule) {
                if (isset($rule['source']) && strcasecmp(trim($rule['source'], '/'), trim($source, '/')) === 0) {
                    wp_send_json_error(array('message' => 'A redirect rule for this source path already exists.'), 400);
                }
            }

            $rule = array(
                'id'         => 'rule_' . substr(md5(uniqid(wp_rand(), true)), 0, 8),
                'source'     => $source,
                'target'     => $target,
                'code'       => $code,
                'type'       => $type,
                'hits'       => 0,
                'enabled'    => 1,
                'created_at' => current_time('mysql'),
            );

            $saved = $this->repository->save_rule($rule);
            if ($saved) {
                wp_send_json_success(array('message' => 'Redirect rule added successfully.', 'rule' => $rule));
            } else {
                wp_send_json_error(array('message' => 'Failed to save redirect rule to options.'), 500);
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
