<?php
/**
 * Plugin Name:       GMB Ranker SEO Automation
 * Plugin URI:        https://github.com/sushantkarn/gmbranker-wp
 * Description:       Enterprise-grade WordPress SEO Automation & Intelligence Engine. Connects WordPress site content and SEO metadata for automated optimization.
 * Version:           2.2.0
 * Author:            Sushant Karn
 * Author URI:        https://gmbranker.org
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
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
if (!defined('GMB_RANKER_SEO_FILE')) {
    define('GMB_RANKER_SEO_FILE', __FILE__);
}
if (!defined('GMB_RANKER_SEO_VERSION')) {
    define('GMB_RANKER_SEO_VERSION', '2.2.0');
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
        'gmb_ranker_module_analytics'        => '1',
        'gmb_ranker_module_woocommerce'      => '1',
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
    wp_clear_scheduled_hook('gmb_ranker_hourly_cron_event');
    wp_clear_scheduled_hook('gmb_ranker_daily_cron_event');
    wp_clear_scheduled_hook('gmb_ranker_process_automation_queue');
    // Clear rewrite rules cache
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gmb_ranker_seo_deactivate');

/**
 * Run non-destructive upgrades when an existing installation is updated.
 * Activation hooks do not run during ordinary plugin updates.
 */
function gmb_ranker_seo_upgrade() {
    $installed_version = get_option('gmb_ranker_seo_version', '0.0.0');
    if (version_compare($installed_version, GMB_RANKER_SEO_VERSION, '<')) {
        add_option('gmb_ranker_module_analytics', '1');
        add_option('gmb_ranker_module_woocommerce', '1');
        update_option('gmb_ranker_seo_version', GMB_RANKER_SEO_VERSION);
    }
}
add_action('plugins_loaded', 'gmb_ranker_seo_upgrade', 5);

/**
 * Bootstrap the GMB Ranker SEO engine.
 *
 * @return GMB_Ranker_SEO_Core
 */
function gmb_ranker_seo_init() {
    return new GMB_Ranker_SEO_Core();
}
gmb_ranker_seo_init();
