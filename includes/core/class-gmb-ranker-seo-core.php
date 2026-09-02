<?php
/**
 * Core Orchestrator for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/class-gmb-ranker-seo-autoloader.php';
require_once dirname(__FILE__) . '/class-gmb-ranker-seo-helpers.php';

class GMB_Ranker_SEO_Core {

    /**
     * Module instances container
     *
     * @var array
     */
    public $modules = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Register Autoloader
        GMB_Ranker_SEO_Autoloader::register();

        // Initialize Active Modules
        $this->init_modules();

        // Register Global Rewrite & Redirect Rules
        add_action('init', array($this, 'gmb_custom_author_base'), 1);
        add_action('template_redirect', array($this, 'gmb_redirect_author_archives'));
        add_action('template_redirect', array($this, 'gmb_redirect_date_archives'));
        add_action('template_redirect', array($this, 'gmb_redirect_attachment_pages'));

        // Initialize Admin Layer
        if (is_admin()) {
            $this->modules['admin'] = new GMB_Ranker_SEO_Admin();
            $this->modules['metabox'] = new GMB_Ranker_SEO_Metabox();
        }
    }

    /**
     * Initialize all feature modules according to enabled settings
     */
    private function init_modules() {
        // Security Module (always load early if enabled)
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('security')) {
            $this->modules['security'] = new GMB_Ranker_SEO_Security();
        }

        // Core SEO Metadata & Snippet Analysis
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('metadata')) {
            $this->modules['metadata'] = new GMB_Ranker_SEO_Metadata();
        }

        // XML & HTML Sitemaps
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('sitemaps')) {
            $this->modules['sitemaps'] = new GMB_Ranker_SEO_Sitemaps();
        }

        // 301/302 Redirections & 404 Monitor
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('redirects')) {
            $this->modules['redirects'] = new GMB_Ranker_SEO_Redirects();
        }

        // Structured Data Schema.org
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('schema')) {
            $this->modules['schema'] = new GMB_Ranker_SEO_Schema();
        }

        // Image SEO Automation
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('image_seo')) {
            $this->modules['image_seo'] = new GMB_Ranker_SEO_Image();
        }

        // Link Optimization & External Target Engine
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('links')) {
            $this->modules['links'] = new GMB_Ranker_SEO_Links();
        }

        // Database Optimizer & Diagnostics
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('db_tools')) {
            $this->modules['db_tools'] = new GMB_Ranker_SEO_DB_Tools();
        }

        // Granular Role Capability Manager
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('role_manager')) {
            $this->modules['role_manager'] = new GMB_Ranker_SEO_Role_Manager();
        }

        // Instant Indexing (Google Indexing API & IndexNow)
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('instant_indexing')) {
            $this->modules['instant_indexing'] = new GMB_Ranker_SEO_Instant_Indexing();
        }

        // Local SEO & Knowledge Graph
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('local_seo')) {
            $this->modules['local_seo'] = new GMB_Ranker_SEO_Local();
        }

        // SEO Score Analysis
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('seo_analysis')) {
            $this->modules['seo_analysis'] = new GMB_Ranker_SEO_Analysis();
        }

        // Google Preferred Sources Button
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('preferred_source')) {
            $this->modules['preferred_source'] = new Google_Preferred_Source();
        }

        // LLMs.txt for AI Search Engines
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('llmstxt')) {
            $this->modules['llmstxt'] = new GMB_Ranker_SEO_LLMs_Txt();
        }

        // Table of Contents Automation
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('toc')) {
            $this->modules['toc'] = new GMB_Ranker_SEO_TOC();
        }

        // AI Provider Engine (OpenRouter, Groq, Ollama)
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('ai_provider')) {
            $this->modules['ai_provider'] = new GMB_Ranker_SEO_AI_Provider();
        }

        // Media Formats & Safe SVG Support
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('media_formats')) {
            $this->modules['media_formats'] = new GMB_Ranker_SEO_Media_Formats();
        }

        // Search Console & Analytics Cloud Bridge
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('analytics')) {
            $this->modules['analytics'] = GMB_Ranker_SEO_Analytics::get_instance();
        }

        // WooCommerce E-Commerce SEO Engine
        if (GMB_Ranker_SEO_Helpers::is_module_enabled('woocommerce')) {
            $this->modules['woocommerce'] = GMB_Ranker_SEO_WooCommerce::get_instance();
        }

        // Lightweight Gutenberg Schema Blocks (FAQ & HowTo)
        $this->modules['blocks'] = GMB_Ranker_SEO_Blocks::get_instance();

        // REST API Engine (always initialized)
        $this->modules['rest_api'] = new GMB_Ranker_SEO_REST_API();
    }

    /**
     * Custom author base rewrite
     */
    public function gmb_custom_author_base() {
        global $wp_rewrite;
        $base = get_option('gmb_author_base', 'author');
        if (!empty($base) && $base !== 'author' && is_object($wp_rewrite)) {
            $wp_rewrite->author_base = sanitize_title($base);
        }
    }

    /**
     * Redirect author archives if disabled
     */
    public function gmb_redirect_author_archives() {
        if (is_author()) {
            $enabled = get_option('gmb_author_archives_enable', 'enabled');
            if ($enabled === 'disabled') {
                wp_safe_redirect(home_url(), 301);
                exit;
            }
        }
    }

    /**
     * Redirect date archives if disabled
     */
    public function gmb_redirect_date_archives() {
        if (is_date()) {
            $disable = get_option('gmb_misc_disable_date_archives', '1');
            if ($disable === '1') {
                wp_safe_redirect(home_url(), 301);
                exit;
            }
        }
    }

    /**
     * Redirect attachment pages
     */
    public function gmb_redirect_attachment_pages() {
        if (is_attachment()) {
            $redirect_mode = get_option('gmb_attachment_redirect_to_parent', 'off');
            if ($redirect_mode === 'parent') {
                $post_id = get_the_ID();
                $parent_id = wp_get_post_parent_id($post_id);
                $target_url = !empty($parent_id) ? get_permalink($parent_id) : home_url('/');
                wp_safe_redirect($target_url, 301);
                exit;
            } elseif ($redirect_mode === 'file') {
                $post_id = get_the_ID();
                $file_url = wp_get_attachment_url($post_id);
                if (!empty($file_url)) {
                    wp_safe_redirect($file_url, 301);
                    exit;
                }
            }
        }
    }
}
