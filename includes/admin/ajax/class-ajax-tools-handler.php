<?php
/**
 * Tools & Database AJAX Handler for GMB Ranker SEO Automation
 *
 * Enterprise-grade, secure, validated AJAX handler for database optimization,
 * orphan metadata cleanup, and transient clearing.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Ajax_Tools_Handler')) {

    class GMB_Ranker_SEO_Ajax_Tools_Handler {

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
         * Handle DB Optimize Tables
         */
        public function handle_optimize_tables() {
            $this->verify_ajax_security();

            try {
                if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
                    $result = GMB_Ranker_SEO_DB_Tools::optimize_tables();
                } else {
                    $db_tools = new GMB_Ranker_SEO_DB_Tools();
                    $result   = $db_tools->optimize_tables();
                }

                if (is_array($result) && !empty($result['success'])) {
                    wp_send_json_success(array(
                        'message' => 'Database tables optimized successfully.',
                        'data'    => $result,
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Database optimization completed with warnings.'), 500);
                }
            } catch (Exception $e) {
                wp_send_json_error(array('message' => 'Failed to optimize database tables: ' . $e->getMessage()), 500);
            }
        }

        /**
         * Handle DB Clear Orphan Meta
         */
        public function handle_clear_orphan_meta() {
            $this->verify_ajax_security();

            try {
                if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
                    $count = GMB_Ranker_SEO_DB_Tools::clear_orphan_meta();
                } else {
                    $db_tools = new GMB_Ranker_SEO_DB_Tools();
                    $count    = $db_tools->clear_orphan_meta();
                }

                $removed = intval($count);
                wp_send_json_success(array(
                    'message' => sprintf('Orphan postmeta, termmeta, and usermeta cleared (%d rows removed).', $removed),
                    'count'   => $removed,
                ));
            } catch (Exception $e) {
                wp_send_json_error(array('message' => 'Failed to clear orphan metadata: ' . $e->getMessage()), 500);
            }
        }

        /**
         * Handle DB Clear Transients
         */
        public function handle_clear_transients() {
            $this->verify_ajax_security();

            try {
                if (class_exists('GMB_Ranker_SEO_DB_Tools')) {
                    $count = GMB_Ranker_SEO_DB_Tools::clear_transients();
                } else {
                    $db_tools = new GMB_Ranker_SEO_DB_Tools();
                    $count    = $db_tools->clear_transients();
                }

                $removed = intval($count);
                wp_send_json_success(array(
                    'message' => sprintf('Expired transients cleared (%d rows removed).', $removed),
                    'count'   => $removed,
                ));
            } catch (Exception $e) {
                wp_send_json_error(array('message' => 'Failed to clear transients: ' . $e->getMessage()), 500);
            }
        }
    }
}
