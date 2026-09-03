<?php
/**
 * Canonical System Status & Tools Registry & Domain Manager Service
 *
 * Centralizes system environment diagnostics, importer discovery, database maintenance
 * tool metadata, version control state, and view model generation for status-tools.php.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Tools_Registry {

    /**
     * Get system environment status diagnostics model
     *
     * @return array
     */
    public static function get_system_status() {
        global $wp_version;

        $theme = wp_get_theme();
        $theme_name = $theme->exists() ? $theme->get('Name') : __('Unknown Theme', 'gmb-ranker-seo-automation');

        // Verify cURL extension
        $curl_active = extension_loaded('curl') && function_exists('curl_version');

        // Verify OpenSSL / JWT support
        $openssl_supported = extension_loaded('openssl') && function_exists('openssl_pkey_new');

        // Verify REST API endpoint availability
        $rest_url = rest_url('wp/v2');
        $rest_status = !empty($rest_url) ? __('Active', 'gmb-ranker-seo-automation') : __('Unavailable', 'gmb-ranker-seo-automation');

        $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : __('N/A', 'gmb-ranker-seo-automation');

        return array(
            'wordpress' => array(
                'version'     => !empty($wp_version) ? $wp_version : get_bloginfo('version'),
                'site_url'    => site_url(),
                'multisite'   => is_multisite() ? __('Yes', 'gmb-ranker-seo-automation') : __('No', 'gmb-ranker-seo-automation'),
                'rest_status' => $rest_status,
                'theme_name'  => $theme_name,
            ),
            'server' => array(
                'php_version'       => phpversion(),
                'memory_limit'      => ini_get('memory_limit') ?: __('N/A', 'gmb-ranker-seo-automation'),
                'curl_active'       => $curl_active ? __('Active', 'gmb-ranker-seo-automation') : __('Unavailable', 'gmb-ranker-seo-automation'),
                'openssl_supported' => $openssl_supported ? __('Supported', 'gmb-ranker-seo-automation') : __('Unavailable', 'gmb-ranker-seo-automation'),
                'server_software'   => $server_software,
            ),
        );
    }

    /**
     * Get importer providers & auto-detection state
     *
     * @return array
     */
    public static function get_importers() {
        $rankmath_active = class_exists('RankMath\Helper') || defined('RANK_MATH_FILE');
        $yoast_active    = class_exists('WPSEO_Options') || defined('WPSEO_FILE');

        return array(
            'rankmath' => array(
                'id'          => 'importer-rm-btn',
                'name'        => 'Rank Math SEO',
                'description' => __('Keywords, Titles, Descs, Schema & Redirects', 'gmb-ranker-seo-automation'),
                'is_active'   => $rankmath_active,
                'status_label'=> $rankmath_active ? __('Detected', 'gmb-ranker-seo-automation') : __('Auto-Detect', 'gmb-ranker-seo-automation'),
                'icon'        => GMB_Ranker_SEO_Helpers::asset_url('images/rankmath.jpeg'),
            ),
            'yoast' => array(
                'id'          => 'importer-yoast-btn',
                'name'        => 'Yoast SEO',
                'description' => __('Keywords, Titles, Descriptions & Canonicals', 'gmb-ranker-seo-automation'),
                'is_active'   => $yoast_active,
                'status_label'=> $yoast_active ? __('Detected', 'gmb-ranker-seo-automation') : __('Auto-Detect', 'gmb-ranker-seo-automation'),
                'icon'        => GMB_Ranker_SEO_Helpers::asset_url('images/yost.png'),
            ),
        );
    }

    /**
     * Get database maintenance tools catalog
     *
     * @return array
     */
    public static function get_database_tools() {
        return array(
            'optimize' => array(
                'id'          => 'gmb-db-optimize-btn',
                'title'       => __('Optimize Database Tables', 'gmb-ranker-seo-automation'),
                'description' => __('Defragments core WordPress tables (posts, postmeta, options, terms) and reclaims overhead space.', 'gmb-ranker-seo-automation'),
                'button_text' => __('Run Tool', 'gmb-ranker-seo-automation'),
            ),
            'transients' => array(
                'id'          => 'gmb-db-transients-btn',
                'title'       => __('Clear Transients & Expired Cache', 'gmb-ranker-seo-automation'),
                'description' => __('Deletes all stale, expired transient records and internal cached objects from the options table.', 'gmb-ranker-seo-automation'),
                'button_text' => __('Run Tool', 'gmb-ranker-seo-automation'),
            ),
            'orphan' => array(
                'id'          => 'gmb-db-orphan-btn',
                'title'       => __('Clean Orphan Metadata', 'gmb-ranker-seo-automation'),
                'description' => __('Cleans orphan postmeta, termmeta, and usermeta records whose parent items were deleted.', 'gmb-ranker-seo-automation'),
                'button_text' => __('Run Tool', 'gmb-ranker-seo-automation'),
            ),
            'rankmath_db' => array(
                'id'          => 'gmb-db-import-rankmath-btn',
                'title'       => __('Migrate Rank Math DB Records', 'gmb-ranker-seo-automation'),
                'description' => __('Batch executes full Rank Math metadata parser and imports data into GMB Ranker.', 'gmb-ranker-seo-automation'),
                'button_text' => __('Run Tool', 'gmb-ranker-seo-automation'),
            ),
        );
    }

    /**
     * Get version control information model
     *
     * @return array
     */
    public static function get_version_control() {
        $version = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.0.0';

        return array(
            'installed_version' => $version,
            'title'             => __('Installed Plugin Version', 'gmb-ranker-seo-automation'),
            'subtitle'          => __('GMB Ranker SEO & Automation Core', 'gmb-ranker-seo-automation'),
            'description'       => __('You are running GMB Ranker SEO. Database schemas and REST indexing hooks are synchronized.', 'gmb-ranker-seo-automation'),
            'plugins_url'       => admin_url('plugins.php'),
            'cloud_url'         => 'https://gmbranker.org',
        );
    }

    /**
     * Resolve export URL
     *
     * @return string
     */
    public static function get_export_url() {
        return admin_url('admin.php?page=gmb-ranker-automation&gmb_action=export_settings');
    }

    /**
     * Get complete validated View Model for status-tools presentation layer
     *
     * @param string $requested_tab
     * @return array
     */
    public static function get_view_model($requested_tab = 'gmb-tools-importer') {
        $allowed_tabs = array('gmb-tools-importer', 'gmb-tools-database', 'gmb-tools-system', 'gmb-tools-version');
        $active_tab   = in_array($requested_tab, $allowed_tabs, true) ? $requested_tab : 'gmb-tools-importer';

        return array(
            'active_tab'      => $active_tab,
            'importers'       => self::get_importers(),
            'database_tools'  => self::get_database_tools(),
            'system_status'   => self::get_system_status(),
            'version_control' => self::get_version_control(),
            'export_url'      => self::get_export_url(),
        );
    }
}
