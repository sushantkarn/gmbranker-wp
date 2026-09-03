<?php
/**
 * Settings & Modules AJAX Handler for GMB Ranker SEO Automation
 *
 * Enterprise-grade, secure, validated AJAX handler for managing plugin modules
 * and role-based capability permissions.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Ajax_Settings_Handler')) {

    class GMB_Ranker_SEO_Ajax_Settings_Handler {

        /**
         * Canonical List of Valid Plugin Module Keys
         *
         * @var array<string>
         */
        protected static $allowed_modules = array(
            'metadata',
            'sitemaps',
            'redirects',
            'schema',
            'preferred_source',
            'image_seo',
            'links',
            'db_tools',
            'role_manager',
            'instant_indexing',
            'local_seo',
            'seo_analysis',
            'security',
            'llmstxt',
            'ai_provider',
            'toc',
            'media_formats',
            'woocommerce',
            'analytics',
        );

        /**
         * Sensitive Core Capabilities Protected from Non-Admin Roles
         *
         * @var array<string>
         */
        protected static $protected_core_caps = array(
            'manage_options',
            'activate_plugins',
            'edit_plugins',
            'delete_plugins',
            'install_plugins',
            'edit_users',
            'delete_users',
            'create_users',
            'switch_themes',
            'edit_themes',
            'unfiltered_html',
        );

        /**
         * Enforce Admin Capability & Nonce Security
         *
         * @param string $nonce_action
         */
        protected function verify_ajax_security($nonce_action = 'gmb_admin_ajax_nonce') {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
            }
            if (!check_ajax_referer($nonce_action, 'nonce', false)) {
                // Fallback check if gmb_admin_ajax_nonce was sent instead of gmb_toggle_module_nonce
                if ($nonce_action !== 'gmb_admin_ajax_nonce' && check_ajax_referer('gmb_admin_ajax_nonce', 'nonce', false)) {
                    return;
                }
                wp_send_json_error(array('message' => 'Invalid security token.'), 403);
            }
        }

        /**
         * Handle Toggle Dashboard Module
         */
        public function handle_toggle_module() {
            $this->verify_ajax_security('gmb_toggle_module_nonce');

            $module_raw = isset($_POST['module']) ? sanitize_text_field(wp_unslash($_POST['module'])) : '';
            $state_raw  = isset($_POST['state']) ? wp_unslash($_POST['state']) : 0;

            if (empty($module_raw) || !in_array($module_raw, self::$allowed_modules, true)) {
                wp_send_json_error(array('message' => 'Invalid or unrecognized plugin module key.'), 400);
            }

            $state = filter_var($state_raw, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
            $option_key = 'gmb_ranker_module_' . $module_raw;

            $updated = update_option($option_key, $state);
            
            // Note: update_option returns false if value is unchanged, which is still successful
            wp_send_json_success(array(
                'message' => 'Module state updated successfully.',
                'module'  => $module_raw,
                'state'   => $state,
            ));
        }

        /**
         * Handle Save Role Permissions
         */
        public function handle_save_role_permissions() {
            $this->verify_ajax_security('gmb_admin_ajax_nonce');

            $perms_raw = isset($_POST['permissions']) ? $_POST['permissions'] : array();
            if (!is_array($perms_raw)) {
                wp_send_json_error(array('message' => 'Invalid permissions payload format.'), 400);
            }

            $wp_roles_obj = wp_roles();
            $available_roles = $wp_roles_obj ? $wp_roles_obj->get_names() : array();
            $clean_perms = array();

            foreach ($perms_raw as $role_key => $cap_list) {
                $role_slug = sanitize_key(wp_unslash($role_key));
                if (empty($role_slug) || !isset($available_roles[$role_slug])) {
                    continue; // Skip unregistered roles
                }

                $clean_caps = array();
                if (is_array($cap_list)) {
                    foreach ($cap_list as $cap) {
                        $cap_slug = sanitize_key(wp_unslash($cap));
                        if (empty($cap_slug)) {
                            continue;
                        }

                        // Prevent privilege escalation: Non-administrator roles cannot be granted core admin capabilities
                        if ($role_slug !== 'administrator' && in_array($cap_slug, self::$protected_core_caps, true)) {
                            continue;
                        }

                        $clean_caps[] = $cap_slug;
                    }
                }

                $clean_perms[$role_slug] = array_unique($clean_caps);
            }

            update_option('gmb_role_manager_caps', $clean_perms);
            
            wp_send_json_success(array(
                'message'     => 'Role permissions saved successfully.',
                'permissions' => $clean_perms,
            ));
        }
    }
}
