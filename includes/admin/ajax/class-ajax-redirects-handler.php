<?php
/**
 * Redirects AJAX Handler for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Redirects_Handler {

    /**
     * @var GMB_Ranker_SEO_Redirect_Service
     */
    protected $service;

    /**
     * @var GMB_Ranker_SEO_Redirect_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Redirect_Service $service = null, GMB_Ranker_SEO_Redirect_Repository $repository = null) {
        $this->service    = $service ?: new GMB_Ranker_SEO_Redirect_Service();
        $this->repository = $repository ?: new GMB_Ranker_SEO_Redirect_Repository();
    }

    /**
     * Handle add redirect rule
     */
    public function handle_add_redirect_rule() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $source = isset($_POST['source']) ? sanitize_text_field(wp_unslash($_POST['source'])) : '';
        $target = isset($_POST['target']) ? sanitize_text_field(wp_unslash($_POST['target'])) : '';
        $code   = isset($_POST['code']) ? intval(wp_unslash($_POST['code'])) : 301;
        $type   = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'exact';

        if (empty($source) || empty($target)) {
            wp_send_json_error(array('message' => 'Source and Target URLs are required.'));
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

        $this->repository->save_rule($rule);
        wp_send_json_success(array('message' => 'Redirect rule added successfully.', 'rule' => $rule));
    }

    /**
     * Handle toggle redirect rule
     */
    public function handle_toggle_redirect_rule() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $rule_id = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';
        $enabled = !empty($_POST['enabled']) ? 1 : 0;

        $rules = $this->repository->get_all_rules();
        foreach ($rules as &$rule) {
            if (isset($rule['id']) && $rule['id'] === $rule_id) {
                $rule['enabled'] = $enabled;
                break;
            }
        }
        $this->repository->save_rules($rules);
        wp_send_json_success(array('message' => 'Redirect status updated.', 'enabled' => $enabled));
    }

    /**
     * Handle delete redirect rule
     */
    public function handle_delete_redirect_rule() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $rule_id = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';
        $this->repository->delete_rule($rule_id);
        wp_send_json_success(array('message' => 'Redirect rule deleted.'));
    }

    /**
     * Handle clear 404 logs
     */
    public function handle_clear_404_logs() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $this->repository->clear_404_logs();
        wp_send_json_success(array('message' => '404 logs cleared.'));
    }
}
