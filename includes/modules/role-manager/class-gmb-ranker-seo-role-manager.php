<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Role_Manager {
    public function __construct() {
        if (is_admin()) {
            add_action('admin_init', array($this, 'initialize_roles'));
        }
    }

    public function initialize_roles() {
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('gmb_ranker_manage_settings');
            $admin->add_cap('gmb_ranker_edit_metadata');
            $admin->add_cap('gmb_ranker_manage_redirects');
        }

        $editor = get_role('editor');
        if ($editor && !$editor->has_cap('gmb_ranker_edit_metadata')) {
            $editor->add_cap('gmb_ranker_edit_metadata');
        }
    }

    public static function check_user_cap($cap) {
        return current_user_can('manage_options') || current_user_can('edit_posts') || current_user_can($cap);
    }
}
