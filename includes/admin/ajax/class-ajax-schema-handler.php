<?php
/**
 * Schema AJAX Handler for GMB Ranker SEO Automation
 *
 * Enterprise-grade, secure, validated AJAX handler for custom schema templates.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Ajax_Schema_Handler')) {

    class GMB_Ranker_SEO_Ajax_Schema_Handler {

        /**
         * @var GMB_Ranker_SEO_Schema_Repository
         */
        protected $repository;

        /**
         * Allowed Schema Rule Scopes
         *
         * @var array<string>
         */
        protected static $allowed_scopes = array('singular', 'archive', 'homepage', 'entire_site');

        /**
         * Constructor
         *
         * @param GMB_Ranker_SEO_Schema_Repository|null $repository
         */
        public function __construct(GMB_Ranker_SEO_Schema_Repository $repository = null) {
            $this->repository = $repository ?: new GMB_Ranker_SEO_Schema_Repository();
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
                'gmb_schema_template_nonce',
                'gmb_schema_preset_nonce',
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
         * Validate JSON-LD Schema Structure & Security
         *
         * @param string $raw_json
         * @return array|false Returns parsed array or false if invalid/unsafe
         */
        protected function validate_schema_json($raw_json) {
            if (empty($raw_json)) {
                return array();
            }

            $decoded = json_decode($raw_json, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return false;
            }

            // Check for unsafe script breakout sequences inside strings
            $json_str = wp_json_encode($decoded);
            if (preg_match('/<script[^>]*>/i', $json_str) || preg_match('/<\/script>/i', $json_str)) {
                return false;
            }

            return $decoded;
        }

        /**
         * Handle Save Schema Template
         */
        public function handle_save_schema_template() {
            $this->verify_ajax_security();

            $template_id_raw = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
            $name_raw        = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
            $type_raw        = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'Article';
            $scope_raw       = isset($_POST['scope']) ? sanitize_text_field(wp_unslash($_POST['scope'])) : 'singular';
            $post_type_raw   = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';
            $enabled_raw     = isset($_POST['enabled']) ? wp_unslash($_POST['enabled']) : 1;
            $schema_data_raw = isset($_POST['schema_data']) ? wp_unslash($_POST['schema_data']) : '';

            $name = trim($name_raw);
            if (empty($name)) {
                wp_send_json_error(array('message' => 'Template name is required.'), 400);
            }

            $type = trim($type_raw) ?: 'Article';
            $scope = in_array($scope_raw, self::$allowed_scopes, true) ? $scope_raw : 'singular';

            // Validate post type against registered post types
            $registered_post_types = get_post_types(array('public' => true));
            $post_type = isset($registered_post_types[$post_type_raw]) ? $post_type_raw : 'post';

            $enabled = filter_var($enabled_raw, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            // Validate schema JSON data
            $parsed_data = $this->validate_schema_json($schema_data_raw);
            if ($parsed_data === false) {
                wp_send_json_error(array('message' => 'Invalid or unsafe Schema JSON structure.'), 400);
            }

            $template_id = !empty($template_id_raw) ? $template_id_raw : ('schema_' . substr(md5(uniqid(wp_rand(), true)), 0, 8));

            $template = array(
                'id'          => $template_id,
                'name'        => $name,
                'type'        => $type,
                'conditions'  => array(
                    'rule'      => $scope,
                    'post_type' => $post_type,
                ),
                'enabled'     => $enabled,
                'schema_json' => !empty($parsed_data) ? wp_json_encode($parsed_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '{}',
                'updated_at'  => current_time('mysql'),
            );

            $saved = $this->repository->save_template($template);
            if ($saved) {
                wp_send_json_success(array(
                    'message'  => 'Schema template saved successfully.',
                    'template' => $template,
                ));
            } else {
                wp_send_json_error(array('message' => 'Failed to save schema template to database options.'), 500);
            }
        }

        /**
         * Handle Delete Schema Template
         */
        public function handle_delete_schema_template() {
            $this->verify_ajax_security();

            $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
            if (empty($template_id)) {
                wp_send_json_error(array('message' => 'Template ID is required.'), 400);
            }

            $existing = $this->repository->get_template($template_id);
            if (!$existing) {
                wp_send_json_error(array('message' => 'Schema template not found or already deleted.'), 404);
            }

            $deleted = $this->repository->delete_template($template_id);
            if ($deleted) {
                wp_send_json_success(array('message' => 'Template deleted successfully.'));
            } else {
                wp_send_json_error(array('message' => 'Failed to delete schema template.'), 500);
            }
        }

        /**
         * Handle Toggle Schema Template
         */
        public function handle_toggle_schema_template() {
            $this->verify_ajax_security();

            $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
            if (empty($template_id)) {
                wp_send_json_error(array('message' => 'Template ID is required.'), 400);
            }

            $template = $this->repository->get_template($template_id);
            if (!$template) {
                wp_send_json_error(array('message' => 'Schema template not found.'), 404);
            }

            $enabled_raw = isset($_POST['enabled']) ? wp_unslash($_POST['enabled']) : 0;
            $enabled = filter_var($enabled_raw, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            $template['enabled'] = $enabled;
            $template['updated_at'] = current_time('mysql');

            $saved = $this->repository->save_template($template);
            if ($saved) {
                wp_send_json_success(array('message' => 'Template status updated.', 'enabled' => $enabled));
            } else {
                wp_send_json_error(array('message' => 'Failed to update template status.'), 500);
            }
        }

        /**
         * Handle Get Schema Template
         */
        public function handle_get_schema_template() {
            $this->verify_ajax_security();

            $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
            if (empty($template_id)) {
                wp_send_json_error(array('message' => 'Template ID is required.'), 400);
            }

            $template = $this->repository->get_template($template_id);
            if (!$template) {
                wp_send_json_error(array('message' => 'Schema template not found.'), 404);
            }

            wp_send_json_success(array('template' => $template));
        }
    }
}
