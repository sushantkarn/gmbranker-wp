<?php
/**
 * Class Autoloader for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Autoloader {

    /**
     * Base directory path for classes
     *
     * @var string
     */
    private static $base_path = '';

    /**
     * Class to file map
     *
     * @var array
     */
    private static $class_map = array();

    /**
     * Register autoloader
     */
    public static function register() {
        self::$base_path = dirname(dirname(__FILE__));
        
        self::$class_map = array(
            // Core Services & Containers
            'GMB_Ranker_SEO_Container'         => self::$base_path . '/core/class-gmb-ranker-seo-container.php',
            'GMB_Ranker_SEO_Hooks'             => self::$base_path . '/core/class-gmb-ranker-seo-hooks.php',
            'GMB_Ranker_SEO_Core'              => self::$base_path . '/core/class-gmb-ranker-seo-core.php',
            'GMB_Ranker_SEO_Helpers'           => self::$base_path . '/core/class-gmb-ranker-seo-helpers.php',
            'GMB_Ranker_SEO_Help_Registry'     => self::$base_path . '/core/class-gmb-ranker-seo-help-registry.php',
            'GMB_Ranker_SEO_Integration_Registry' => self::$base_path . '/core/class-gmb-ranker-seo-integration-registry.php',
            
            // Admin & UI Components
            'GMB_Ranker_SEO_Admin'             => self::$base_path . '/admin/class-gmb-ranker-seo-admin.php',
            'GMB_Ranker_SEO_UI'                => self::$base_path . '/admin/class-gmb-ranker-seo-ui.php',
            'GMB_Ranker_SEO_Metabox'           => self::$base_path . '/admin/class-gmb-ranker-seo-metabox.php',
            'GMB_Ranker_SEO_Settings_Registry' => self::$base_path . '/admin/settings/class-settings-registry.php',
            'GMB_Ranker_SEO_Ajax_Admin'        => self::$base_path . '/admin/ajax/class-gmb-ranker-seo-ajax-admin.php',
            'GMB_Ranker_SEO_Ajax_Wizard'       => self::$base_path . '/admin/ajax/class-gmb-ranker-seo-ajax-wizard.php',
            
            // Feature Modules
            'GMB_Ranker_SEO_Metadata'          => self::$base_path . '/modules/metadata/class-gmb-ranker-seo-metadata.php',
            'GMB_Ranker_SEO_Analysis'          => self::$base_path . '/modules/metadata/class-gmb-ranker-seo-analysis.php',
            'GMB_Ranker_SEO_Sitemaps'          => self::$base_path . '/modules/sitemaps/class-gmb-ranker-seo-sitemaps.php',
            'GMB_Ranker_SEO_Schema'            => self::$base_path . '/modules/schema/class-gmb-ranker-seo-schema.php',
            'GMB_Ranker_SEO_Instant_Indexing'  => self::$base_path . '/modules/instant-indexing/class-gmb-ranker-seo-instant-indexing.php',
            'GMB_Ranker_SEO_Redirects'         => self::$base_path . '/modules/redirects/class-gmb-ranker-seo-redirects.php',
            'GMB_Ranker_SEO_AI_Provider'       => self::$base_path . '/modules/ai-provider/class-gmb-ranker-seo-ai-provider.php',
            'GMB_Ranker_SEO_Local'             => self::$base_path . '/modules/local-seo/class-gmb-ranker-seo-local.php',
            'Google_Preferred_Source'          => self::$base_path . '/modules/preferred-source/class-google-preferred-source.php',
            'GMB_Ranker_SEO_REST_API'          => self::$base_path . '/modules/rest-api/class-gmb-ranker-seo-rest-api.php',
            'GMB_Ranker_SEO_Security'          => self::$base_path . '/modules/security/class-gmb-ranker-seo-security.php',
            'GMB_Ranker_SEO_TOC'               => self::$base_path . '/modules/toc/class-gmb-ranker-seo-toc.php',
            'GMB_Ranker_SEO_Image'             => self::$base_path . '/modules/image-seo/class-gmb-ranker-seo-image.php',
            'GMB_Ranker_SEO_Links'             => self::$base_path . '/modules/links/class-gmb-ranker-seo-links.php',
            'GMB_Ranker_SEO_LLMs_Txt'          => self::$base_path . '/modules/llmstxt/class-gmb-ranker-seo-llmstxt.php',
            'GMB_Ranker_SEO_DB_Tools'          => self::$base_path . '/modules/db-tools/class-gmb-ranker-seo-db-tools.php',
            'GMB_Ranker_SEO_Role_Manager'      => self::$base_path . '/modules/role-manager/class-gmb-ranker-seo-role-manager.php',
            'GMB_Ranker_SEO_Media_Formats'     => self::$base_path . '/modules/media-formats/class-gmb-ranker-seo-media-formats.php',
            'GMB_Ranker_SEO_Analytics'         => self::$base_path . '/modules/analytics/class-gmb-ranker-seo-analytics.php',
            'GMB_Ranker_SEO_WooCommerce'       => self::$base_path . '/modules/woocommerce/class-gmb-ranker-seo-woocommerce.php',
            'GMB_Ranker_SEO_Blocks'            => self::$base_path . '/modules/blocks/class-gmb-ranker-seo-blocks.php',
            'GMB_Ranker_SEO_Research_Engine'   => self::$base_path . '/services/class-gmb-ranker-seo-research-engine.php',
        );

        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload callback
     *
     * @param string $class Class name
     */
    public static function autoload($class) {
        if (isset(self::$class_map[$class]) && file_exists(self::$class_map[$class])) {
            require_once self::$class_map[$class];
            return;
        }

        // Search in structured subdirectories
        $formatted_name = str_replace('_', '-', strtolower($class));
        $file_name = 'class-' . $formatted_name . '.php';
        $interface_name = 'interface-' . $formatted_name . '.php';

        // Candidate directory search paths
        $search_dirs = array(
            self::$base_path . '/repositories',
            self::$base_path . '/services',
            self::$base_path . '/api',
            self::$base_path . '/automation',
            self::$base_path . '/automation/contracts',
            self::$base_path . '/automation/triggers',
            self::$base_path . '/automation/actions',
            self::$base_path . '/automation/conditions',
            self::$base_path . '/automation/queue',
            self::$base_path . '/admin/settings',
            self::$base_path . '/admin/ajax',
            self::$base_path . '/rest',
            self::$base_path . '/background',
        );

        foreach ($search_dirs as $dir) {
            if (file_exists($dir . '/' . $file_name)) {
                self::$class_map[$class] = $dir . '/' . $file_name;
                require_once $dir . '/' . $file_name;
                return;
            }
            if (file_exists($dir . '/' . $interface_name)) {
                self::$class_map[$class] = $dir . '/' . $interface_name;
                require_once $dir . '/' . $interface_name;
                return;
            }
            // Strip plugin prefix for domain files (e.g. gmb-ranker-seo-redirect-repository -> redirect-repository)
            $short_name = str_replace('gmb-ranker-seo-', '', $formatted_name);
            if (file_exists($dir . '/class-' . $short_name . '.php')) {
                self::$class_map[$class] = $dir . '/class-' . $short_name . '.php';
                require_once $dir . '/class-' . $short_name . '.php';
                return;
            }
            if (file_exists($dir . '/interface-' . $short_name . '.php')) {
                self::$class_map[$class] = $dir . '/interface-' . $short_name . '.php';
                require_once $dir . '/interface-' . $short_name . '.php';
                return;
            }
            $interface_clean = str_replace(array('gmb-ranker-seo-', '-interface'), '', $formatted_name);
            if (file_exists($dir . '/interface-' . $interface_clean . '.php')) {
                self::$class_map[$class] = $dir . '/interface-' . $interface_clean . '.php';
                require_once $dir . '/interface-' . $interface_clean . '.php';
                return;
            }
        }

        // Fallback check in includes/ root for backward compatibility
        $fallback_file = self::$base_path . '/class-' . $formatted_name . '.php';
        if (file_exists($fallback_file)) {
            require_once $fallback_file;
        }
    }
}
