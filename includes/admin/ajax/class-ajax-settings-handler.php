<?php
/**
 * Settings & Modules AJAX Handler for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Settings_Handler {

    /**
     * Handle toggle dashboard module
     */
    public function handle_toggle_module() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_toggle_module_nonce', 'nonce');

        $module = isset($_POST['module']) ? sanitize_text_field(wp_unslash($_POST['module'])) : '';
        $state  = isset($_POST['state']) && $_POST['state'] === '1' ? '1' : '0';

        if (empty($module)) {
            wp_send_json_error(array('message' => 'Invalid module key.'));
        }

        update_option('gmb_ranker_module_' . $module, $state);
        wp_send_json_success(array('module' => $module, 'state' => $state));
    }

    /**
     * Handle save role permissions
     */
    public function handle_save_role_permissions() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $perms = isset($_POST['permissions']) ? (array) $_POST['permissions'] : array();
        $clean = array();
        foreach ($perms as $role => $cap_list) {
            $clean[sanitize_key($role)] = array_map('sanitize_key', (array) $cap_list);
        }

        update_option('gmb_role_manager_caps', $clean);
        wp_send_json_success(array('message' => 'Role permissions saved successfully.'));
    }
}
