<?php
/**
 * Admin Router & Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Admin {

    /**
     * AJAX admin instance
     *
     * @var GMB_Ranker_SEO_Ajax_Admin
     */
    public $ajax_admin;

    /**
     * AJAX wizard instance
     *
     * @var GMB_Ranker_SEO_Ajax_Wizard
     */
    public $ajax_wizard;

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize AJAX Controllers
        $this->ajax_admin = new GMB_Ranker_SEO_Ajax_Admin();
        $this->ajax_wizard = new GMB_Ranker_SEO_Ajax_Wizard();

        // Enforce CSRF protection globally for GMB Ranker AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
            if (is_string($action) && strpos($action, 'gmb_') === 0) {
                if ($action !== 'gmb_toggle_dashboard_module') {
                    add_action('admin_init', array($this, 'enforce_ajax_csrf_protection'), 1);
                }
            }
        }

        // Admin Menus & Settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('custom_menu_order', '__return_true');
        add_filter('menu_order', array($this, 'reorder_admin_menu'), 999);
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('admin_body_class', array($this, 'filter_admin_body_class'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Export handlers & Wizard redirection
        add_action('admin_init', array($this, 'handle_export_redirects_download'));
        add_action('admin_init', array($this, 'handle_export_settings_download'));
        add_action('admin_init', array($this, 'intercept_setup_wizard'));
        add_action('admin_footer', array($this, 'add_wizard_menu_target_blank'));
    }

    /**
     * Enforce CSRF security validation across AJAX endpoints
     */
    public static function enforce_ajax_csrf_protection() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized capabilities.'), 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : (isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '');
        $valid_nonces = array(
            'gmb_seo_save_nonce',
            'gmb_toggle_module_nonce',
            'gmb_admin_ajax_nonce',
            'gmb_wizard_nonce',
            'gmb_instant_indexing_nonce',
            'gmb_schema_preset_nonce',
            'gmb_schema_template_nonce',
            'gmb_instant_index_action',
            'gmb_ranker_ajax_nonce'
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
            wp_send_json_error(array('message' => 'CSRF validation security check failed.'), 403);
        }
    }

    /**
     * Register WordPress admin menu hierarchy
     */
    public function add_admin_menu() {
        $logo_path = dirname(dirname(dirname(__FILE__))) . '/assets/gmb-ranker-logo.svg';
        $icon_url = 'dashicons-performance';
        if (file_exists($logo_path)) {
            $svg_content = file_get_contents($logo_path);
            $icon_url = 'data:image/svg+xml;base64,' . base64_encode($svg_content);
        }

        add_menu_page(
            'GMB Ranker',
            'GMB Ranker',
            'manage_options',
            'gmb-ranker-automation',
            array($this, 'render_settings_page'),
            $icon_url,
            2.5
        );

        add_submenu_page(
            'gmb-ranker-automation',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'gmb-ranker-automation',
            array($this, 'render_settings_page')
        );

        if (get_option('gmb_ranker_module_analytics', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Performance & Analytics',
                'Performance',
                'manage_options',
                'gmb-ranker-analytics',
                array($this, 'render_settings_page')
            );
        }

        add_submenu_page(
            'gmb-ranker-automation',
            'General Settings',
            'General Settings',
            'manage_options',
            'gmb-ranker-settings',
            array($this, 'render_settings_page')
        );

        if (get_option('gmb_ranker_module_metadata', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Titles & Meta',
                'Titles & Meta',
                'manage_options',
                'gmb-ranker-metadata',
                array($this, 'render_settings_page')
            );
        }

        if (get_option('gmb_ranker_module_sitemaps', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Sitemap Settings',
                'Sitemap Settings',
                'manage_options',
                'gmb-ranker-sitemaps',
                array($this, 'render_settings_page')
            );
        }

        if (get_option('gmb_ranker_module_schema', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Schema Settings',
                'Schema Settings',
                'manage_options',
                'gmb-ranker-schema',
                array($this, 'render_settings_page')
            );
        }

        if (get_option('gmb_ranker_module_redirects', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Redirections',
                'Redirections',
                'manage_options',
                'gmb-ranker-redirects',
                array($this, 'render_settings_page')
            );
        }

        if (get_option('gmb_ranker_module_instant_indexing', '1') !== '0') {
            add_submenu_page(
                'gmb-ranker-automation',
                'Instant Indexing',
                'Instant Indexing',
                'manage_options',
                'gmb-ranker-instant-indexing',
                array($this, 'render_settings_page')
            );
        }

        add_submenu_page(
            'gmb-ranker-automation',
            'Integrations',
            'Integrations',
            'manage_options',
            'gmb-ranker-integrations',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'gmb-ranker-automation',
            'Status & Tools',
            'Status & Tools',
            'manage_options',
            'gmb-ranker-importer',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'gmb-ranker-automation',
            'Help & Support',
            'Help & Support',
            'manage_options',
            'gmb-ranker-help',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'gmb-ranker-automation',
            'Setup Wizard',
            'Setup Wizard',
            'manage_options',
            'gmb-ranker-wizard',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Position GMB Ranker in the admin menu hierarchy directly above Site Kit (right below Dashboard)
     *
     * @param array $menu_order
     * @return array
     */
    public function reorder_admin_menu(array $menu_order) {
        $gmb_slug = 'gmb-ranker-automation';
        $gmb_key = array_search($gmb_slug, $menu_order, true);
        if ($gmb_key !== false) {
            unset($menu_order[$gmb_key]);
            $menu_order = array_values($menu_order);
        }

        // Search for Google Site Kit menu item if active
        $target_index = false;
        foreach ($menu_order as $idx => $item) {
            if (strpos($item, 'googlesitekit') !== false) {
                $target_index = $idx;
                break;
            }
        }

        if ($target_index !== false) {
            // Place directly ABOVE Site Kit
            array_splice($menu_order, $target_index, 0, $gmb_slug);
        } else {
            // Fallback: place right below Dashboard (index.php)
            $dashboard_index = array_search('index.php', $menu_order, true);
            if ($dashboard_index !== false) {
                array_splice($menu_order, $dashboard_index + 1, 0, $gmb_slug);
            } else {
                array_unshift($menu_order, $gmb_slug);
            }
        }

        return $menu_order;
    }

    /**
     * Add admin body class
     *
     * @param string $classes
     * @return string
     */
    public function filter_admin_body_class($classes) {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (is_string($page) && (strpos($page, 'gmb-ranker') !== false || strpos($page, 'gmb_') !== false || strpos($page, 'gmb-') !== false)) {
            $classes .= ' gmb-admin-page';
        }
        return $classes;
    }

    /**
     * Register all plugin settings with WordPress Settings API
     */
    public function register_settings() {
        GMB_Ranker_SEO_Settings_Registry::get_instance()->register_all_settings();
    }

    /**
     * Enqueue all admin CSS stylesheets and JavaScript scripts
     *
     * @param string $hook
     */
    public function enqueue_admin_assets($hook) {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $is_gmb_page = (is_string($page) && (strpos($page, 'gmb-ranker') !== false || strpos($page, 'gmb_') !== false || strpos($page, 'gmb-') !== false)) 
                       || (is_string($hook) && (strpos($hook, 'gmb-ranker') !== false || strpos($hook, 'gmb-') !== false));
        
        if (!$is_gmb_page) {
            return;
        }

        $ver = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0';
        $assets_dir = dirname(dirname(dirname(__FILE__))) . '/assets/';
        $base_url = plugins_url('assets/', dirname(dirname(__FILE__)));
        $js_ver = file_exists($assets_dir . 'js/admin-dashboard.js') ? filemtime($assets_dir . 'js/admin-dashboard.js') : $ver;
        $css_ver = file_exists($assets_dir . 'css/admin-dashboard.css') ? filemtime($assets_dir . 'css/admin-dashboard.css') : $ver;

        // 1. Master Design Tokens
        wp_enqueue_style('gmb-ranker-tokens', $base_url . 'css/tokens.css', array(), $css_ver);
        
        // 2. Base & Layout Shell
        wp_enqueue_style('gmb-ranker-admin-dashboard', $base_url . 'css/admin-dashboard.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-layout', $base_url . 'css/layouts/admin-layout.css', array('gmb-ranker-tokens'), $css_ver);
        
        // 3. Centralized Reusable Components
        wp_enqueue_style('gmb-ranker-buttons', $base_url . 'css/components/buttons.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-cards', $base_url . 'css/components/cards.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-forms', $base_url . 'css/components/forms.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-toggles', $base_url . 'css/components/toggles.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-tables', $base_url . 'css/components/tables.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-badges', $base_url . 'css/components/badges.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-notices', $base_url . 'css/components/notices.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-modals', $base_url . 'css/components/modals.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-tabs', $base_url . 'css/components/tabs.css', array('gmb-ranker-tokens'), $css_ver);

        // 4. Feature Pages Styles
        wp_enqueue_style('gmb-ranker-schema', $base_url . 'css/pages/schema.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-redirects', $base_url . 'css/pages/redirects.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-instant-indexing', $base_url . 'css/pages/instant-indexing.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-integrations', $base_url . 'css/pages/integrations.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-sitemaps', $base_url . 'css/pages/sitemaps.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-titles-meta', $base_url . 'css/pages/titles-meta.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-status-tools', $base_url . 'css/pages/status-tools.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-help', $base_url . 'css/pages/help.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-analytics', $base_url . 'css/pages/analytics.css', array('gmb-ranker-tokens'), $css_ver);
        wp_enqueue_style('gmb-ranker-wizard', $base_url . 'css/pages/wizard.css', array('gmb-ranker-tokens'), $css_ver);

        wp_enqueue_script('gmb-ranker-admin-js', $base_url . 'js/admin-dashboard.js', array('jquery'), $js_ver, true);
        wp_enqueue_script('gmb-ranker-admin-app-js', $base_url . 'js/admin/admin-app.js', array('jquery', 'gmb-ranker-admin-js'), $js_ver, true);
        $localized_data = array(
            'ajax_url'            => admin_url('admin-ajax.php'),
            'nonce'               => wp_create_nonce('gmb_seo_save_nonce'),
            'admin_nonce'         => wp_create_nonce('gmb_admin_ajax_nonce'),
            'schema_nonce'        => wp_create_nonce('gmb_ranker_ajax_nonce'),
            'toggle_module_nonce' => wp_create_nonce('gmb_toggle_module_nonce'),
            'instant_index_nonce' => wp_create_nonce('gmb_admin_ajax_nonce'),
            'site_name'           => get_bloginfo('name'),
            'site_desc'           => get_bloginfo('description'),
        );
        wp_localize_script('gmb-ranker-admin-js', 'gmb_ranker_admin', $localized_data);
        wp_localize_script('gmb-ranker-admin-app-js', 'gmb_ranker_admin', $localized_data);
    }

    /**
     * Render the main unified settings page and sub-tab views
     */
    public function render_settings_page() {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'gmb-ranker-automation';
        $current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        if ($current_page === 'gmb-ranker-settings' && empty($current_tab)) {
            $current_tab = 'settings';
        }

        // Build global view context arguments
        $api_key = get_option('gmb_ranker_api_key', '');
        
        $mod_metadata         = get_option('gmb_ranker_module_metadata', '1') !== '0' ? '1' : '0';
        $mod_sitemaps         = get_option('gmb_ranker_module_sitemaps', '1') !== '0' ? '1' : '0';
        $mod_redirects        = get_option('gmb_ranker_module_redirects', '1') !== '0' ? '1' : '0';
        $mod_schema           = get_option('gmb_ranker_module_schema', '1') !== '0' ? '1' : '0';
        $mod_pref_source      = get_option('gmb_ranker_module_preferred_source', '1') !== '0' ? '1' : '0';
        $mod_image_seo        = get_option('gmb_ranker_module_image_seo', '1') !== '0' ? '1' : '0';
        $mod_db_tools         = get_option('gmb_ranker_module_db_tools', '1') !== '0' ? '1' : '0';
        $mod_role_manager     = get_option('gmb_ranker_module_role_manager', '1') !== '0' ? '1' : '0';
        $mod_instant_indexing = get_option('gmb_ranker_module_instant_indexing', '1') !== '0' ? '1' : '0';
        $mod_links            = get_option('gmb_ranker_module_links', '1') !== '0' ? '1' : '0';
        $mod_local_seo        = get_option('gmb_ranker_module_local_seo', '1') !== '0' ? '1' : '0';
        $mod_seo_analysis     = get_option('gmb_ranker_module_seo_analysis', '1') !== '0' ? '1' : '0';
        $mod_security         = get_option('gmb_ranker_module_security', '1') !== '0' ? '1' : '0';
        $mod_llmstxt          = get_option('gmb_ranker_module_llmstxt', '1') !== '0' ? '1' : '0';
        $mod_ai_provider      = get_option('gmb_ranker_module_ai_provider', '1') !== '0' ? '1' : '0';
        $mod_toc              = get_option('gmb_ranker_module_toc', '1') !== '0' ? '1' : '0';
        $mod_media_formats    = get_option('gmb_ranker_module_media_formats', '1') !== '0' ? '1' : '0';
        $mod_analytics        = get_option('gmb_ranker_module_analytics', '1') !== '0' ? '1' : '0';
        $mod_woocommerce      = get_option('gmb_ranker_module_woocommerce', '1') !== '0' ? '1' : '0';

        $local_name           = get_option('gmb_local_business_name', '');
        $local_phone          = get_option('gmb_local_business_phone', '');
        $local_address        = get_option('gmb_local_business_address', '');

        $post_title_temp      = get_option('gmb_metadata_post_title_template', '%title% - %sitename%');
        $post_desc_temp       = get_option('gmb_metadata_post_desc_template', '%excerpt%');
        $page_title_temp      = get_option('gmb_metadata_page_title_template', '%title% - %sitename%');
        $page_desc_temp       = get_option('gmb_metadata_page_desc_template', '%excerpt%');

        $redirects_rules      = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($redirects_rules)) {
            $redirects_rules = array();
        }
        $logs_404             = get_option('gmb_ranker_404_logs', array());

        $image_alt_temp       = get_option('gmb_image_seo_alt_template', '%title%');
        $image_title_temp     = get_option('gmb_image_seo_title_template', '%title%');

        $links_exclude_domains       = get_option('gmb_links_exclude_domains', 'google.com, wikipedia.org');
        $redirect_attachments        = get_option('gmb_redirect_attachments', 'off');
        $strip_category_base         = get_option('gmb_strip_category_base', 'off');
        $nofollow_external_links     = get_option('gmb_nofollow_external_links', 'on');
        $new_window_external_links   = get_option('gmb_new_window_external_links', 'on');
        $redirect_orphan_attachments = get_option('gmb_redirect_orphan_attachments', '');
        $nofollow_image_links        = get_option('gmb_nofollow_image_links', 'off');
        $affiliate_link_prefixes     = get_option('gmb_affiliate_link_prefixes', '');

        $editor_role          = get_role('editor');
        $author_role          = get_role('author');
        $contributor_role     = get_role('contributor');

        $use_multiple         = get_option('gmb_local_use_multiple_locations', '0');
        $locations            = get_option('gmb_local_business_locations', array());

        $google_json          = get_option('gmb_ranker_google_json_key', '');
        $client_email         = '';
        if (!empty($google_json)) {
            $decoded_json = json_decode($google_json, true);
            if (is_array($decoded_json) && isset($decoded_json['client_email'])) {
                $client_email = $decoded_json['client_email'];
            }
        }
        $instant_tab          = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'console');
        $indexnow_key         = get_option('gmb_ranker_indexnow_key', '');
        $key_location         = site_url('/' . ($indexnow_key ?: 'key') . '.txt');
        $public_post_types    = get_post_types(array('public' => true), 'objects');
        unset($public_post_types['attachment']);
        $google_post_types    = get_option('gmb_ranker_google_post_types', array('post', 'page'));
        if (!is_array($google_post_types)) $google_post_types = array('post', 'page');
        $indexnow_post_types  = get_option('gmb_ranker_indexnow_post_types', array('post', 'page'));
        if (!is_array($indexnow_post_types)) $indexnow_post_types = array('post', 'page');

        $view_args = compact(
            'current_page', 'current_tab', 'api_key',
            'mod_metadata', 'mod_sitemaps', 'mod_redirects', 'mod_schema', 'mod_pref_source',
            'mod_image_seo', 'mod_db_tools', 'mod_role_manager', 'mod_instant_indexing',
            'mod_links', 'mod_local_seo', 'mod_seo_analysis', 'mod_security', 'mod_llmstxt',
            'mod_ai_provider', 'mod_toc', 'mod_media_formats', 'mod_analytics', 'mod_woocommerce',
            'local_name', 'local_phone', 'local_address',
            'post_title_temp', 'post_desc_temp', 'page_title_temp', 'page_desc_temp',
            'redirects_rules', 'logs_404', 'image_alt_temp', 'image_title_temp',
            'links_exclude_domains', 'redirect_attachments', 'strip_category_base',
            'nofollow_external_links', 'new_window_external_links', 'redirect_orphan_attachments',
            'nofollow_image_links', 'affiliate_link_prefixes',
            'editor_role', 'author_role', 'contributor_role',
            'use_multiple', 'locations',
            'google_json', 'client_email', 'instant_tab', 'indexnow_key', 'key_location',
            'public_post_types', 'google_post_types', 'indexnow_post_types'
        );

        // Output Layout Header
        GMB_Ranker_SEO_Helpers::render_view('layout/header.php', $view_args);

        // Render Active Page View
        $view_map = array(
            'gmb-ranker-automation'       => ($current_tab === 'wizard') ? 'wizard.php' : 'dashboard.php',
            'gmb-ranker-analytics'        => 'analytics.php',
            'gmb-ranker-settings'         => 'general-settings.php',
            'gmb-ranker-metadata'         => 'titles-meta.php',
            'gmb-ranker-sitemaps'         => 'sitemaps.php',
            'gmb-ranker-schema'           => 'schema.php',
            'gmb-ranker-redirects'        => 'redirects.php',
            'gmb-ranker-instant-indexing' => 'instant-indexing.php',
            'gmb-ranker-integrations'     => 'integrations.php',
            'gmb-ranker-importer'         => 'status-tools.php',
            'gmb-ranker-help'             => 'help.php',
            'gmb-ranker-wizard'           => 'wizard.php',
        );

        $view_file = isset($view_map[$current_page]) ? $view_map[$current_page] : 'dashboard.php';
        GMB_Ranker_SEO_Helpers::render_view($view_file, $view_args);

        // Output Layout Footer & Modals
        GMB_Ranker_SEO_Helpers::render_view('layout/footer.php', $view_args);
    }

    /**
     * Handle download export of redirection rules
     */
    public function handle_export_redirects_download() {
        if (isset($_GET['page']) && $_GET['page'] === 'gmb-ranker-redirects' && isset($_GET['action']) && $_GET['action'] === 'export_redirects') {
            check_admin_referer('gmb_export_redirects_nonce');
            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized user capability.');
            }

            $rules = get_option('gmb_ranker_redirects_rules', array());
            $json = wp_json_encode($rules, JSON_PRETTY_PRINT);

            header('Content-Description: File Transfer');
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename=gmb-ranker-redirects-export-' . gmdate('Y-m-d') . '.json');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($json));
            echo $json;
            exit;
        }
    }

    /**
     * Handle download export of plugin settings
     */
    public function handle_export_settings_download() {
        if (isset($_GET['page']) && $_GET['page'] === 'gmb-ranker-importer' && isset($_GET['action']) && $_GET['action'] === 'export_settings') {
            check_admin_referer('gmb_export_settings_nonce');
            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized user capability.');
            }

            global $wpdb;
            $all_options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'gmb_%' OR option_name LIKE 'gps_%'", ARRAY_A);
            $export_data = array();
            foreach ($all_options as $row) {
                $export_data[$row['option_name']] = maybe_unserialize($row['option_value']);
            }

            $json = wp_json_encode($export_data, JSON_PRETTY_PRINT);

            header('Content-Description: File Transfer');
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename=gmb-ranker-seo-settings-export-' . gmdate('Y-m-d') . '.json');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($json));
            echo $json;
            exit;
        }
    }

    /**
     * Intercept standalone setup wizard screen
     */
    public function intercept_setup_wizard() {
        $is_wizard = (isset($_GET['page']) && $_GET['page'] === 'gmb-ranker-wizard') ||
                     (isset($_GET['gmb_wizard']) && $_GET['gmb_wizard'] === '1');

        if ($is_wizard) {
            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized user capability.');
            }

            include GMB_RANKER_SEO_PATH . 'includes/admin/views/wizard.php';
            exit;
        }
    }

    /**
     * Wizard menu target blank attribute helper
     */
    public function add_wizard_menu_target_blank() {
        // No-op or inline DOM modifier
    }

    /**
     * Sanitize robots array
     *
     * @param mixed $input
     * @return array
     */
    public static function sanitize_robots_array($input) {
        if (!is_array($input)) {
            return array();
        }
        $allowed = array('index', 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet', 'noodp', 'notranslate');
        $clean = array();
        foreach ($input as $val) {
            $val = sanitize_key((string)$val);
            if (in_array($val, $allowed, true)) {
                $clean[] = $val;
            }
        }
        return array_values(array_unique($clean));
    }

    /**
     * Sanitize redirects rules array
     *
     * @param mixed $input
     * @return array
     */
    public static function sanitize_redirects_rules($input) {
        if (!is_array($input)) {
            return array();
        }
        $clean = array();
        foreach ($input as $rule) {
            if (is_array($rule) && !empty($rule['source'])) {
                $clean[] = array(
                    'source' => sanitize_text_field($rule['source']),
                    'target' => sanitize_text_field($rule['target']),
                    'type'   => isset($rule['type']) ? sanitize_text_field($rule['type']) : '301',
                    'active' => isset($rule['active']) ? (int) $rule['active'] : 1,
                );
            }
        }
        return $clean;
    }

    /**
     * Sanitize business locations array
     *
     * @param mixed $input
     * @return array
     */
    public static function sanitize_business_locations($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_values($input);
    }

    /**
     * Sanitize Google JSON service account key
     *
     * @param string $key
     * @return string
     */
    public static function sanitize_google_json_key($key) {
        if (!is_string($key)) {
            return get_option('gmb_ranker_google_json_key', '');
        }
        $trimmed = trim($key);
        if (empty($trimmed)) {
            delete_transient('gmb_google_indexing_token');
            return '';
        }

        // Try direct JSON decode
        $decoded = json_decode($trimmed, true);

        // If that fails, unslash and decode (WordPress options.php slashes input)
        if (!is_array($decoded) && function_exists('wp_unslash')) {
            $unslashed = wp_unslash($trimmed);
            $test_decoded = json_decode($unslashed, true);
            if (is_array($test_decoded)) {
                $decoded = $test_decoded;
            }
        }

        if (is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'service_account' && !empty($decoded['client_email']) && !empty($decoded['private_key'])) {
            delete_transient('gmb_google_indexing_token');
            return wp_json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (function_exists('add_settings_error')) {
            add_settings_error('gmb_ranker_google_json_key', 'invalid_json', __('Invalid Google Service Account JSON. Ensure client_email, private_key, and type=service_account are present.', 'gmb-ranker-seo-automation'));
        }

        // If validation fails on non-empty input, keep existing saved key rather than wiping it
        return get_option('gmb_ranker_google_json_key', '');
    }
}
