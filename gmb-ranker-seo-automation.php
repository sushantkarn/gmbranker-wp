<?php
/**
 * Plugin Name:       GMB Ranker SEO Automation
 * Plugin URI:        https://github.com/sushantkarn/gmbranker-wp
 * Description:       Enterprise-grade WordPress SEO Automation & Intelligence Engine. Connects WordPress site content and SEO metadata to GMB Ranker for automated optimization experiments.
 * Version:           1.0.0
 * Author:            GMB Ranker
 * Author URI:        https://gmbranker.com
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       gmb-ranker-seo-automation
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin version and path constants
if (!defined('GMB_RANKER_SEO_VERSION')) {
    define('GMB_RANKER_SEO_VERSION', '1.0.0');
}
if (!defined('GMB_RANKER_SEO_PATH')) {
    define('GMB_RANKER_SEO_PATH', plugin_dir_path(__FILE__));
}
if (!defined('GMB_RANKER_SEO_URL')) {
    define('GMB_RANKER_SEO_URL', plugin_dir_url(__FILE__));
}

// Load core orchestrator class
require_once GMB_RANKER_SEO_PATH . 'includes/class-gmb-ranker-seo-core.php';

/**
 * Activation hook to initialize default module options safely.
 */
function gmb_ranker_seo_activate() {
    $default_modules = array(
        'gmb_ranker_module_metadata'         => '1',
        'gmb_ranker_module_sitemaps'         => '1',
        'gmb_ranker_module_redirects'        => '1',
        'gmb_ranker_module_schema'           => '1',
        'gmb_ranker_module_preferred_source' => '1',
        'gmb_ranker_module_image_seo'        => '1',
        'gmb_ranker_module_links'            => '1',
        'gmb_ranker_module_db_tools'         => '1',
        'gmb_ranker_module_role_manager'     => '1',
        'gmb_ranker_module_instant_indexing' => '1',
        'gmb_ranker_module_local_seo'        => '1',
        'gmb_ranker_module_seo_analysis'     => '1',
        'gmb_ranker_module_security'         => '1',
        'gmb_ranker_module_llmstxt'          => '1',
        'gmb_ranker_module_ai_provider'      => '1',
        'gmb_ranker_module_toc'              => '1',
        'gmb_ranker_module_media_formats'    => '1',
    );

    foreach ($default_modules as $key => $default_val) {
        add_option($key, $default_val);
    }
}
register_activation_hook(__FILE__, 'gmb_ranker_seo_activate');

/**
 * Deactivation hook to clear any cached transient data safely.
 */
function gmb_ranker_seo_deactivate() {
    // Clear rewrite rules cache
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gmb_ranker_seo_deactivate');

/**
 * Bootstrap the GMB Ranker SEO engine.
 *
 * @return GMB_Ranker_SEO_Core
 */
function gmb_ranker_seo_init() {
    return new GMB_Ranker_SEO_Core();
}
gmb_ranker_seo_init();
