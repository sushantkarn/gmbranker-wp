<?php
/**
 * Schema AJAX Handler for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Schema_Handler {

    /**
     * @var GMB_Ranker_SEO_Schema_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Schema_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Schema_Repository();
    }

    /**
     * Handle save schema template
     */
    public function handle_save_schema_template() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
        $name        = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $type        = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'Article';
        $scope       = isset($_POST['scope']) ? sanitize_text_field(wp_unslash($_POST['scope'])) : 'singular';
        $post_type   = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';
        $enabled     = isset($_POST['enabled']) ? intval(wp_unslash($_POST['enabled'])) : 1;
        $schema_data = isset($_POST['schema_data']) ? wp_unslash($_POST['schema_data']) : '';

        if (empty($name)) {
            wp_send_json_error(array('message' => 'Template name is required.'));
        }

        $parsed_data = json_decode($schema_data, true);
        if ($schema_data && json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => 'Invalid Schema JSON: ' . json_last_error_msg()));
        }

        $template = array(
            'id'          => $template_id ?: ('schema_' . substr(md5(uniqid(wp_rand(), true)), 0, 8)),
            'name'        => $name,
            'type'        => $type,
            'conditions'  => array(
                'rule'      => $scope,
                'post_type' => $post_type,
            ),
            'enabled'     => $enabled,
            'schema_json' => is_array($parsed_data) ? wp_json_encode($parsed_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '{}',
            'updated_at'  => current_time('mysql'),
        );

        $this->repository->save_template($template);

        wp_send_json_success(array(
            'message'  => 'Schema template saved successfully.',
            'template' => $template,
        ));
    }

    /**
     * Handle delete schema template
     */
    public function handle_delete_schema_template() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
        if (empty($template_id)) {
            wp_send_json_error(array('message' => 'Template ID required.'));
        }

        $this->repository->delete_template($template_id);
        wp_send_json_success(array('message' => 'Template deleted successfully.'));
    }

    /**
     * Handle toggle schema template
     */
    public function handle_toggle_schema_template() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
        $enabled     = !empty($_POST['enabled']) ? 1 : 0;

        $template = $this->repository->get_template($template_id);
        if (!$template) {
            wp_send_json_error(array('message' => 'Template not found.'));
        }

        $template['enabled'] = $enabled;
        $this->repository->save_template($template);

        wp_send_json_success(array('message' => 'Template status updated.', 'enabled' => $enabled));
    }

    /**
     * Handle get schema template
     */
    public function handle_get_schema_template() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }
        check_ajax_referer('gmb_admin_ajax_nonce', 'nonce');

        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : '';
        $template = $this->repository->get_template($template_id);

        if (!$template) {
            wp_send_json_error(array('message' => 'Template not found.'));
        }

        wp_send_json_success(array('template' => $template));
    }
}
