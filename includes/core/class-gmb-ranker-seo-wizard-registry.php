<?php
/**
 * Canonical Wizard Registry & Domain Manager Service
 *
 * Centralizes setup wizard steps, setup modes, site types, AI providers,
 * compatibility diagnostics, secret masking, and ViewModel preparation for wizard.php.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Wizard_Registry {

    /**
     * Get canonical wizard step catalog
     *
     * @return array
     */
    public static function get_steps() {
        return array(
            1 => array(
                'id'          => 'mode',
                'number'      => 1,
                'title'       => __('Getting Started', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('Getting Started', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('Choose how you want GMB Ranker SEO to configure your site settings.', 'gmb-ranker-seo-automation'),
                'form_id'     => 'wiz-form-mode',
                'btn_next_id' => 'wiz-btn-next-1',
            ),
            2 => array(
                'id'          => 'site_profile',
                'number'      => 2,
                'title'       => __('Your Site Profile', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('Your Site Profile', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('Provide essential details about your website to help search engines understand your entity.', 'gmb-ranker-seo-automation'),
                'form_id'     => 'wiz-form-site',
                'btn_next_id' => 'wiz-btn-next-2',
            ),
            3 => array(
                'id'          => 'api_config',
                'number'      => 3,
                'title'       => __('API & Automation Integrations', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('API Integrations', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('Connect IndexNow, Google Indexing, or optional AI keyword generation engines.', 'gmb-ranker-seo-automation'),
                'form_id'     => 'wiz-form-api',
                'btn_next_id' => 'wiz-btn-next-3',
            ),
            4 => array(
                'id'          => 'sitemaps',
                'number'      => 4,
                'title'       => __('XML Sitemaps Configuration', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('XML Sitemaps', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('Enable automated XML sitemaps to help search engines crawl and discover your latest content.', 'gmb-ranker-seo-automation'),
                'form_id'     => 'wiz-form-sitemaps',
                'btn_next_id' => 'wiz-btn-next-4',
            ),
            5 => array(
                'id'          => 'optimization',
                'number'      => 5,
                'title'       => __('SEO Automations & Tweaks', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('SEO Automations', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('Apply essential automated SEO tweaks to optimize your link structure and crawl budget.', 'gmb-ranker-seo-automation'),
                'form_id'     => 'wiz-form-optimization',
                'btn_next_id' => 'wiz-btn-next-5',
            ),
            6 => array(
                'id'          => 'ready',
                'number'      => 6,
                'title'       => __('Your Site is Ready!', 'gmb-ranker-seo-automation'),
                'nav_label'   => __('Site Ready!', 'gmb-ranker-seo-automation'),
                'subtitle'    => __('GMB Ranker SEO has been configured and is actively optimizing your website, indexing hooks, and structured schemas.', 'gmb-ranker-seo-automation'),
                'form_id'     => '',
                'btn_next_id' => '',
            ),
        );
    }

    /**
     * Get setup modes
     *
     * @return array
     */
    public static function get_setup_modes() {
        return array(
            'easy' => array(
                'label'       => __('Easy Mode', 'gmb-ranker-seo-automation'),
                'badge'       => '',
                'description' => __('Let autopilot manage all headers, canonicals, and indexation checks automatically. Prefilled for industry standards.', 'gmb-ranker-seo-automation'),
            ),
            'advanced' => array(
                'label'       => __('Advanced Mode', 'gmb-ranker-seo-automation'),
                'badge'       => __('RECOMMENDED', 'gmb-ranker-seo-automation'),
                'badge_class' => 'wiz-badge-rec',
                'description' => __('Fine-tune all indexing engines, schemas, OpenGraph meta, and redirection rules manually.', 'gmb-ranker-seo-automation'),
            ),
            'custom' => array(
                'label'       => __('Custom Mode', 'gmb-ranker-seo-automation'),
                'badge'       => __('PRO', 'gmb-ranker-seo-automation'),
                'badge_class' => 'wiz-badge-pro',
                'description' => __('Select this if you have a custom settings preset file you want to use.', 'gmb-ranker-seo-automation'),
            ),
        );
    }

    /**
     * Get site type choices
     *
     * @return array
     */
    public static function get_site_types() {
        return array(
            'blog'        => __('Personal Blog', 'gmb-ranker-seo-automation'),
            'business'    => __('Small Business / Local Business', 'gmb-ranker-seo-automation'),
            'corporation' => __('Corporation / Enterprise', 'gmb-ranker-seo-automation'),
            'news'        => __('News / Magazine Website', 'gmb-ranker-seo-automation'),
            'portfolio'   => __('Portfolio / Agency', 'gmb-ranker-seo-automation'),
            'ecommerce'   => __('Webshop / eCommerce', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get AI Provider choices
     *
     * @return array
     */
    public static function get_ai_providers() {
        return array(
            'openrouter' => __('OpenRouter', 'gmb-ranker-seo-automation'),
            'groq'       => __('Groq Cloud', 'gmb-ranker-seo-automation'),
            'ollama'     => __('Ollama (Local AI)', 'gmb-ranker-seo-automation'),
            'nvidia'     => __('NVIDIA NIM', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get System Compatibility Diagnostics
     *
     * @return array
     */
    public static function get_compatibility_diagnostics() {
        $php_version = phpversion();
        $php_pass    = version_compare($php_version, '7.4.0', '>=');
        $curl_pass   = function_exists('curl_version');
        $ssl_pass    = extension_loaded('openssl');
        $dom_pass    = class_exists('DOMDocument');
        $json_pass   = function_exists('json_encode');
        $wp_version  = get_bloginfo('version');
        $wp_pass     = version_compare($wp_version, '5.6', '>=');

        $all_pass = $php_pass && $curl_pass && $ssl_pass && $dom_pass && $json_pass && $wp_pass;

        return array(
            'all_pass'    => $all_pass,
            'php_version' => $php_version,
            'php_pass'    => $php_pass,
            'curl_pass'   => $curl_pass,
            'ssl_pass'    => $ssl_pass,
            'dom_pass'    => $dom_pass,
            'json_pass'   => $json_pass,
            'wp_version'  => $wp_version,
            'wp_pass'     => $wp_pass,
        );
    }

    /**
     * Mask sensitive API key strings so raw credentials are not exposed
     *
     * @param string $secret
     * @return string
     */
    public static function mask_secret($secret) {
        $secret = trim((string)$secret);
        if (empty($secret)) {
            return '';
        }
        if (strlen($secret) <= 8) {
            return '••••••••';
        }
        return substr($secret, 0, 4) . '••••••••' . substr($secret, -4);
    }

    /**
     * Get sitemap post type options
     *
     * @return array
     */
    public static function get_sitemap_eligible_post_types() {
        $public_pts = get_post_types(array('public' => true), 'objects');
        $eligible   = array();
        foreach ($public_pts as $pt_slug => $pt_obj) {
            if (in_array($pt_slug, array('attachment', 'elementor_library'), true)) {
                continue;
            }
            $eligible[$pt_slug] = $pt_obj->labels->name;
        }
        return $eligible;
    }

    /**
     * Get current setup wizard settings
     *
     * @return array
     */
    public static function get_settings() {
        $saved_mode   = get_option('gmb_ranker_automation_mode', 'advanced');
        $site_type    = get_option('gmb_site_type', 'blog');
        $org_name     = get_option('gmb_organization_name', get_bloginfo('name'));
        $site_logo    = get_option('gmb_site_logo', '');
        $social_image = get_option('gmb_social_share_image', '');
        $api_key      = get_option('gmb_ranker_api_key', '');

        $ai_provider  = get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', ''));
        $ai_key_raw   = '';
        if ($ai_provider === 'openrouter') {
            $ai_key_raw = get_option('gmb_ai_openrouter_key', '');
        } elseif ($ai_provider === 'groq') {
            $ai_key_raw = get_option('gmb_ai_groq_key', '');
        }

        $module_sitemaps      = get_option('gmb_ranker_module_sitemaps', '1') !== '0' && get_option('gmb_ranker_module_sitemaps', '1') !== 'off';
        $sitemap_images       = get_option('gmb_sitemap_include_images', '1') === '1';
        $sitemap_pts          = get_option('gmb_sitemap_post_types', array('post', 'page'));
        if (!is_array($sitemap_pts)) {
            $sitemap_pts = array('post', 'page');
        }

        $strip_category       = get_option('gmb_strip_category_base', 'off') === 'on';
        $nofollow_ext         = get_option('gmb_nofollow_external_links', 'on') === 'on';
        $new_window_ext       = get_option('gmb_new_window_external_links', 'on') === 'on';
        $redirect_attachments = get_option('gmb_redirect_attachments', 'on') === 'on' || get_option('gmb_redirect_attachment_to_parent', 'on') === 'on';
        $noindex_empty        = get_option('gmb_noindex_empty_taxonomies', 'on') === 'on';

        return array(
            'mode'                 => $saved_mode,
            'site_type'            => $site_type,
            'org_name'             => $org_name,
            'site_logo'            => $site_logo,
            'social_image'         => $social_image,
            'api_key'              => $api_key,
            'api_key_masked'       => self::mask_secret($api_key),
            'ai_provider'          => $ai_provider,
            'ai_key'               => $ai_key_raw,
            'ai_key_masked'        => self::mask_secret($ai_key_raw),
            'module_sitemaps'      => $module_sitemaps,
            'sitemap_images'       => $sitemap_images,
            'sitemap_post_types'   => $sitemap_pts,
            'strip_category'       => $strip_category,
            'nofollow_ext'         => $nofollow_ext,
            'new_window_ext'       => $new_window_ext,
            'redirect_attachments' => $redirect_attachments,
            'noindex_empty'        => $noindex_empty,
        );
    }

    /**
     * Get complete validated View Model for wizard presentation layer
     *
     * @return array
     */
    public static function get_view_model() {
        $base_url = defined('GMB_RANKER_SEO_URL') ? GMB_RANKER_SEO_URL : plugins_url('/', dirname(dirname(dirname(dirname(__FILE__)))) . '/gmb-ranker-seo.php');
        $base_url = rtrim($base_url, '/') . '/assets/';
        $ver      = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0';

        return array(
            'base_url'          => $base_url,
            'version'           => $ver,
            'steps'             => self::get_steps(),
            'setup_modes'       => self::get_setup_modes(),
            'site_types'        => self::get_site_types(),
            'ai_providers'      => self::get_ai_providers(),
            'diagnostics'       => self::get_compatibility_diagnostics(),
            'eligible_pts'      => self::get_sitemap_eligible_post_types(),
            'settings'          => self::get_settings(),
            'wizard_nonce'      => wp_create_nonce('gmb_wizard_nonce'),
        );
    }
}
