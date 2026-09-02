<?php
/**
 * Tools & Database AJAX Handler for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Tools_Handler {

    /**
     * Handle DB optimize tables
     */
    public function handle_optimize_tables() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $db_tools = new GMB_Ranker_SEO_DB_Tools();
        $result   = $db_tools->optimize_tables();
        wp_send_json_success(array('message' => 'Database tables optimized successfully.', 'data' => $result));
    }

    /**
     * Handle DB clear orphan meta
     */
    public function handle_clear_orphan_meta() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $db_tools = new GMB_Ranker_SEO_DB_Tools();
        $count    = $db_tools->clear_orphan_meta();
        wp_send_json_success(array('message' => 'Orphan postmeta cleared (' . intval($count) . ' rows removed).'));
    }

    /**
     * Handle DB clear transients
     */
    public function handle_clear_transients() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $db_tools = new GMB_Ranker_SEO_DB_Tools();
        $count    = $db_tools->clear_transients();
        wp_send_json_success(array('message' => 'Expired transients cleared (' . intval($count) . ' rows removed).'));
    }
}
