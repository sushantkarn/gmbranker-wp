<?php
/**
 * Settings Registry for GMB Ranker SEO Automation
 *
 * Decomposes monolithic register_setting() logic into clean, modular registrations.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Settings_Registry {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Settings_Registry|null
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('pre_update_option', array($this, 'protect_missing_options_on_save'), 10, 3);
    }

    /**
     * Prevent WordPress options.php from wiping out registered settings that are not present in the currently submitted form.
     *
     * @param mixed  $value     The new, unsaved value.
     * @param string $option    The name of the option.
     * @param mixed  $old_value The old option value.
     * @return mixed
     */
    public function protect_missing_options_on_save($value, $option, $old_value) {
        if (isset($_POST['option_page'])) {
            $is_plugin_option = (strpos($option, 'gmb_') === 0 || strpos($option, 'gps_') === 0);
            if ($is_plugin_option && !isset($_POST[$option]) && !isset($_FILES[$option])) {
                $current_group = sanitize_key(wp_unslash($_POST['option_page']));
                
                // Fetch all options registered for the currently submitted group
                global $new_whitelist_options;
                $group_options = array();
                if (isset($new_whitelist_options[$current_group]) && is_array($new_whitelist_options[$current_group])) {
                    $group_options = $new_whitelist_options[$current_group];
                } elseif (function_exists('get_registered_settings')) {
                    $registered = get_registered_settings();
                    foreach ($registered as $reg_opt => $args) {
                        if (isset($args['group']) && $args['group'] === $current_group) {
                            $group_options[] = $reg_opt;
                        }
                    }
                }

                // If this option is part of the currently submitted group, but omitted from $_POST, it means it's an unchecked checkbox/array
                if (!empty($group_options) && in_array($option, $group_options, true)) {
                    if (is_array($old_value)) {
                        return array();
                    }
                    if ($old_value === 'on' || $old_value === 'off') {
                        return 'off';
                    }
                    if ($old_value === '1' || $old_value === '0') {
                        return '0';
                    }
                    return '';
                }

                // Otherwise preserve existing value to prevent cross-tab form wipeouts
                return $old_value;
            }
        }
        return $value;
    }

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Settings_Registry
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register all plugin settings across all modules
     */
    public function register_all_settings() {
        $this->register_general_settings();
        $this->register_sitemaps_settings();
        $this->register_redirects_settings();
        $this->register_integrations_settings();
        $this->register_ai_provider_settings();
        $this->register_titles_meta_settings();
        $this->register_post_types_settings();
        $this->register_taxonomies_settings();
        $this->register_local_seo_settings();
        $this->register_security_settings();
        $this->register_schema_settings();
        $this->register_misc_tools_settings();
    }

    /**
     * General settings registration
     */
    protected function register_general_settings() {
        $general_options = array(
            // API & Webmaster
            'gmb_ranker_api_key'                 => 'sanitize_text_field',
            'gmb_webmaster_google_verify'        => 'sanitize_text_field',
            'gmb_webmaster_bing_verify'          => 'sanitize_text_field',
            'gmb_webmaster_pinterest_verify'     => 'sanitize_text_field',
            'gmb_webmaster_yandex_verify'        => 'sanitize_text_field',
            'gmb_webmaster_baidu_verify'         => 'sanitize_text_field',
            'gmb_rss_before_content'             => 'wp_kses_post',
            'gmb_rss_after_content'              => 'wp_kses_post',

            // Links Subtab
            'gmb_strip_category_base'            => 'sanitize_text_field',
            'gmb_redirect_attachments'           => 'sanitize_text_field',
            'gmb_redirect_orphan_attachments'    => 'esc_url_raw',
            'gmb_nofollow_external_links'        => 'sanitize_text_field',
            'gmb_nofollow_image_links'           => 'sanitize_text_field',
            'gmb_new_window_external_links'      => 'sanitize_text_field',
            'gmb_affiliate_link_prefixes'        => 'sanitize_textarea_field',
            'gmb_links_exclude_domains'          => 'sanitize_textarea_field',

            // Image SEO Subtab
            'gmb_image_seo_alt_template'         => 'sanitize_text_field',
            'gmb_image_alt_template'             => 'sanitize_text_field',
            'gmb_image_seo_title_template'       => 'sanitize_text_field',
            'gmb_image_title_template'           => 'sanitize_text_field',

            // LLMS.txt Subtab
            'gmb_llms_limit'                     => 'absint',
            'gmb_llms_title'                     => 'sanitize_text_field',
            'gmb_llms_desc'                      => 'sanitize_textarea_field',
            'gmb_llms_additional_content'        => 'sanitize_textarea_field',

            // Table of Contents Subtab
            'gmb_toc_title'                      => 'sanitize_text_field',
            'gmb_toc_min_headings'               => 'absint',
            'gmb_toc_auto_insert'                => 'sanitize_text_field',
            'gmb_toc_collapsible'                => 'sanitize_text_field',
            'gmb_toc_position'                   => 'sanitize_text_field',

            // AI Provider Subtab
            'gmb_ai_provider'                    => 'sanitize_text_field',
            'gmb_ai_openrouter_key'              => 'sanitize_text_field',
            'gmb_ai_openrouter_model'            => 'sanitize_text_field',
            'gmb_ai_groq_key'                    => 'sanitize_text_field',
            'gmb_ai_groq_model'                  => 'sanitize_text_field',
            'gmb_ai_ollama_url'                  => 'esc_url_raw',
            'gmb_ai_ollama_model'                => 'sanitize_text_field',
        );

        foreach ($general_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_general_group', $opt, array('sanitize_callback' => $sanitizer));
        }

        // Array-based options in General Settings
        register_setting( 'gmb_ranker_settings_group', 'gmb_llms_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_settings_group', 'gmb_llms_taxonomies', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_settings_group', 'gmb_toc_levels', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_settings_group', 'gmb_toc_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_llms_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_llms_taxonomies', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_toc_levels', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_toc_post_types', array('sanitize_callback' => 'sanitize_text_field') );
    }

    /**
     * Sitemaps settings registration
     */
    protected function register_sitemaps_settings() {
        $sitemap_options = array(
            'gmb_ranker_module_sitemaps'            => 'sanitize_text_field',
            'gmb_ranker_sitemap_exclude_slugs'      => 'sanitize_text_field',
            'gmb_sitemap_items_per_page'             => 'absint',
            'gmb_sitemap_include_images'             => 'sanitize_text_field',
            'gmb_sitemap_include_featured_images'    => 'sanitize_text_field',
            'gmb_sitemap_ping_search_engines'        => 'sanitize_text_field',
            'gmb_sitemap_excluded_posts'             => 'sanitize_text_field',
            'gmb_sitemap_excluded_terms'             => 'sanitize_text_field',
            'gmb_sitemap_custom_urls'                => 'sanitize_textarea_field',
            'gmb_sitemap_include_authors'            => 'sanitize_text_field',
            'gmb_sitemap_html_enable'                => 'sanitize_text_field',
            'gmb_sitemap_html_sort'                  => 'sanitize_text_field',
        );

        foreach ($sitemap_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_sitemaps_group', $opt, array('sanitize_callback' => $sanitizer));
        }

        $sitemap_pts = get_post_types(array('public' => true), 'names');
        foreach ($sitemap_pts as $s_pt) {
            register_setting('gmb_ranker_settings_group', 'gmb_sitemap_include_pt_' . $s_pt, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_settings_group', 'gmb_sitemap_images_pt_' . $s_pt, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_sitemaps_group', 'gmb_sitemap_include_pt_' . $s_pt, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_sitemaps_group', 'gmb_sitemap_images_pt_' . $s_pt, array('sanitize_callback' => 'sanitize_text_field'));
        }
        $sitemap_taxes = get_taxonomies(array('public' => true), 'names');
        foreach ($sitemap_taxes as $s_tax) {
            register_setting('gmb_ranker_settings_group', 'gmb_sitemap_include_tax_' . $s_tax, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_settings_group', 'gmb_sitemap_empty_tax_' . $s_tax, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_sitemaps_group', 'gmb_sitemap_include_tax_' . $s_tax, array('sanitize_callback' => 'sanitize_text_field'));
            register_setting('gmb_ranker_sitemaps_group', 'gmb_sitemap_empty_tax_' . $s_tax, array('sanitize_callback' => 'sanitize_text_field'));
        }
    }

    /**
     * Redirects settings registration
     */
    protected function register_redirects_settings() {
        $redirect_options = array(
            'gmb_ranker_auto_post_redirect'  => 'sanitize_text_field',
            'gmb_ranker_fallback_behavior'   => 'sanitize_text_field',
            'gmb_ranker_fallback_url'        => 'esc_url_raw',
            'gmb_redirect_attachments'       => 'sanitize_text_field',
            'gmb_strip_category_base'        => 'sanitize_text_field',
            'gmb_ranker_404_limit'           => 'absint',
            'gmb_ranker_404_ignore_query'    => 'sanitize_text_field',
            'gmb_ranker_404_exclude_paths'   => 'sanitize_text_field',
            'gmb_ranker_redirects_rules'     => array('GMB_Ranker_SEO_Admin', 'sanitize_redirects_rules')
        );

        foreach ($redirect_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_redirects_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Integrations settings registration
     */
    protected function register_integrations_settings() {
        // Shared & Dedicated Integrations Group
        $integration_options = array(
            'gmb_ranker_api_key'             => 'sanitize_text_field',
            'gmb_ranker_cloud_sync'           => 'sanitize_text_field',
            'gmb_workspace_name'              => 'sanitize_text_field',
            'gmb_workspace_email'             => 'sanitize_email',
            'gmb_workspace_gsc_property'      => 'sanitize_text_field',
            'gmb_workspace_ga4_stream'        => 'sanitize_text_field',
            'gmb_workspace_gmb_location'      => 'sanitize_text_field',
            'gmb_integration_gmb_sync'        => 'sanitize_text_field',
            'gmb_integration_ga4_anonymize'   => 'sanitize_text_field',
            'gmb_integration_indexnow_key'    => 'sanitize_text_field',
            'gmb_integration_indexnow_auto'   => 'sanitize_text_field',
            'gmb_integration_webhook_url'     => 'esc_url_raw',
            'gmb_integration_webhook_secret'  => 'sanitize_text_field',
            'gmb_ai_provider'                 => 'sanitize_text_field',
            'gmb_ai_openrouter_key'           => 'sanitize_text_field',
            'gmb_ai_openrouter_model'         => 'sanitize_text_field',
            'gmb_ai_groq_key'                 => 'sanitize_text_field',
            'gmb_ai_groq_model'               => 'sanitize_text_field',
            'gmb_ai_ollama_url'               => 'esc_url_raw',
            'gmb_ai_ollama_model'             => 'sanitize_text_field',
        );

        foreach ($integration_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_integrations_group', $opt, array('sanitize_callback' => $sanitizer));
        }

        // Dedicated Google Instant Indexing Group
        register_setting('gmb_ranker_settings_group', 'gmb_ranker_google_json_key', array('sanitize_callback' => array('GMB_Ranker_SEO_Admin', 'sanitize_google_json_key')));
        register_setting( 'gmb_ranker_settings_group', 'gmb_ranker_google_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting('gmb_ranker_google_indexing_group', 'gmb_ranker_google_json_key', array('sanitize_callback' => array('GMB_Ranker_SEO_Admin', 'sanitize_google_json_key')));
        register_setting( 'gmb_ranker_google_indexing_group', 'gmb_ranker_google_post_types', array('sanitize_callback' => 'sanitize_text_field') );

        // Dedicated IndexNow / Bing Instant Indexing Group
        register_setting('gmb_ranker_settings_group', 'gmb_ranker_indexnow_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'gmb_ranker_settings_group', 'gmb_ranker_indexnow_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting('gmb_ranker_settings_group', 'gmb_api_action', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_bing_indexing_group', 'gmb_ranker_indexnow_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'gmb_ranker_bing_indexing_group', 'gmb_ranker_indexnow_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting('gmb_ranker_bing_indexing_group', 'gmb_integration_indexnow_auto', array('sanitize_callback' => 'sanitize_text_field'));
    }

    /**
     * AI Provider settings registration
     */
    protected function register_ai_provider_settings() {
        $ai_options = array(
            'gmb_ai_provider'         => 'sanitize_text_field',
            'gmb_ai_openrouter_key'   => 'sanitize_text_field',
            'gmb_ai_openrouter_model' => 'sanitize_text_field',
            'gmb_ai_groq_key'         => 'sanitize_text_field',
            'gmb_ai_groq_model'       => 'sanitize_text_field',
            'gmb_ai_ollama_url'       => 'esc_url_raw',
            'gmb_ai_ollama_model'     => 'sanitize_text_field',
        );

        foreach ($ai_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_ai_provider_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_general_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Global Titles & Meta settings registration
     */
    protected function register_titles_meta_settings() {
        $tm_options = array(
            'gmb_metadata_post_title_template'       => 'sanitize_text_field',
            'gmb_metadata_post_desc_template'        => 'sanitize_text_field',
            'gmb_metadata_page_title_template'       => 'sanitize_text_field',
            'gmb_metadata_page_desc_template'        => 'sanitize_text_field',
            'gmb_metadata_global_robots'             => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            'gmb_metadata_global_max_image'          => 'sanitize_text_field',
            'gmb_metadata_global_max_snippet'        => 'sanitize_text_field',
            'gmb_metadata_global_max_video'          => 'sanitize_text_field',
            'gmb_metadata_separator'                 => 'sanitize_text_field',
            'gmb_metadata_capitalize_titles'         => 'sanitize_text_field',
            'gmb_metadata_og_thumbnail'              => 'sanitize_text_field',
            'gmb_metadata_twitter_card_type'         => 'sanitize_text_field',

            // Homepage Settings
            'gmb_homepage_title_template'            => 'sanitize_text_field',
            'gmb_homepage_desc_template'             => 'sanitize_text_field',
            'gmb_homepage_robots_meta_enable'        => 'sanitize_text_field',
            'gmb_homepage_robots_meta'               => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            'gmb_homepage_advanced_max_snippet'      => 'sanitize_text_field',
            'gmb_homepage_advanced_max_video'        => 'sanitize_text_field',
            'gmb_homepage_advanced_max_image'        => 'sanitize_text_field',
            'gmb_homepage_facebook_title'            => 'sanitize_text_field',
            'gmb_homepage_facebook_desc'             => 'sanitize_textarea_field',
            'gmb_homepage_facebook_image'            => 'sanitize_text_field',
            'gmb_homepage_twitter_card_type'         => 'sanitize_text_field',

            // Authors Settings
            'gmb_author_archives_enable'             => 'sanitize_text_field',
            'gmb_author_base'                        => 'sanitize_text_field',
            'gmb_author_robots_meta_enable'          => 'sanitize_text_field',
            'gmb_author_robots_meta'                 => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            'gmb_author_archive_title'               => 'sanitize_text_field',
            'gmb_author_archive_desc'                => 'sanitize_text_field',
            'gmb_author_advanced_max_snippet'        => 'sanitize_text_field',
            'gmb_author_advanced_max_video'          => 'sanitize_text_field',
            'gmb_author_advanced_max_image'          => 'sanitize_text_field',
            'gmb_author_slack_sharing'               => 'sanitize_text_field',
            'gmb_author_seo_controls'                => 'sanitize_text_field',

            // Misc Pages Settings
            'gmb_misc_disable_date_archives'         => 'sanitize_text_field',
            'gmb_misc_date_archive_title'            => 'sanitize_text_field',
            'gmb_misc_date_archive_desc'             => 'sanitize_text_field',
            'gmb_misc_date_robots_meta_enable'       => 'sanitize_text_field',
            'gmb_misc_date_robots_meta'              => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            'gmb_misc_404_title'                     => 'sanitize_text_field',
            'gmb_misc_search_title'                  => 'sanitize_text_field',
            'gmb_misc_noindex_search_results'        => 'sanitize_text_field',
            'gmb_misc_noindex_subpages'              => 'sanitize_text_field',
            'gmb_misc_noindex_paginated_single'      => 'sanitize_text_field',
            'gmb_misc_noindex_password_protected'    => 'sanitize_text_field',
        );

        foreach ($tm_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_titles_meta_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Post Types settings registration
     */
    protected function register_post_types_settings() {
        // Built-in post types: posts, pages, attachment, services, service_locations, team_members
        $types = array('posts', 'pages', 'attachment', 'services', 'service_locations', 'team_members');

        foreach ($types as $t) {
            $pt_options = array(
                'gmb_' . $t . '_title_template'          => 'sanitize_text_field',
                'gmb_' . $t . '_desc_template'           => 'sanitize_text_field',
                'gmb_' . $t . '_schema_type'             => 'sanitize_text_field',
                'gmb_' . $t . '_schema_headline'         => 'sanitize_text_field',
                'gmb_' . $t . '_schema_desc'             => 'sanitize_textarea_field',
                'gmb_' . $t . '_article_type'            => 'sanitize_text_field',
                'gmb_' . $t . '_autodetect_video'        => 'sanitize_text_field',
                'gmb_' . $t . '_robots_meta_enable'      => 'sanitize_text_field',
                'gmb_' . $t . '_robots_meta'             => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
                'gmb_' . $t . '_advanced_max_snippet'    => 'sanitize_text_field',
                'gmb_' . $t . '_advanced_max_video'      => 'sanitize_text_field',
                'gmb_' . $t . '_advanced_max_image'      => 'sanitize_text_field',
                'gmb_' . $t . '_twitter_card_type'       => 'sanitize_text_field',
                'gmb_' . $t . '_link_suggestions'        => 'sanitize_text_field',
                'gmb_' . $t . '_link_suggestion_titles'  => 'sanitize_text_field',
                'gmb_' . $t . '_primary_taxonomy'        => 'sanitize_text_field',
                'gmb_' . $t . '_slack_sharing'           => 'sanitize_text_field',
                'gmb_' . $t . '_seo_controls'            => 'sanitize_text_field',
                'gmb_' . $t . '_bulk_editing'            => 'sanitize_text_field',
                'gmb_' . $t . '_custom_fields'           => 'sanitize_textarea_field',
                'gmb_' . $t . '_thumbnail_watermark'     => 'sanitize_text_field',
            );

            foreach ($pt_options as $opt => $sanitizer) {
                register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
                register_setting('gmb_ranker_titles_meta_group', $opt, array('sanitize_callback' => $sanitizer));
            }
        }

        register_setting('gmb_ranker_settings_group', 'gmb_attachment_redirect_to_parent', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_titles_meta_group', 'gmb_attachment_redirect_to_parent', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_settings_group', 'gmb_services_schema_provider_type', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_titles_meta_group', 'gmb_services_schema_provider_type', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_settings_group', 'gmb_service_locations_schema_business_type', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_titles_meta_group', 'gmb_service_locations_schema_business_type', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_settings_group', 'gmb_team_members_schema_job_title', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('gmb_ranker_titles_meta_group', 'gmb_team_members_schema_job_title', array('sanitize_callback' => 'sanitize_text_field'));

        // Dynamic custom post types
        if (function_exists('get_post_types')) {
            $public_pts = get_post_types(array('public' => true), 'names');
            foreach ($public_pts as $pt_name) {
                register_setting('gmb_ranker_settings_group', 'gmb_' . $pt_name . '_schema_type', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_settings_group', 'gmb_' . $pt_name . '_article_type', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_settings_group', 'gmb_' . $pt_name . '_schema_headline', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_settings_group', 'gmb_' . $pt_name . '_schema_desc', array('sanitize_callback' => 'sanitize_textarea_field'));
                register_setting('gmb_ranker_titles_meta_group', 'gmb_' . $pt_name . '_schema_type', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_titles_meta_group', 'gmb_' . $pt_name . '_article_type', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_titles_meta_group', 'gmb_' . $pt_name . '_schema_headline', array('sanitize_callback' => 'sanitize_text_field'));
                register_setting('gmb_ranker_titles_meta_group', 'gmb_' . $pt_name . '_schema_desc', array('sanitize_callback' => 'sanitize_textarea_field'));
            }
        }
    }

    /**
     * Taxonomies settings registration
     */
    protected function register_taxonomies_settings() {
        $tax_options = array(
            'gmb_categories_archive_title'       => 'sanitize_text_field',
            'gmb_categories_archive_desc'        => 'sanitize_text_field',
            'gmb_categories_robots_meta_enable'  => 'sanitize_text_field',
            'gmb_categories_robots_meta'         => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            'gmb_categories_advanced_max_snippet'=> 'sanitize_text_field',
            'gmb_categories_advanced_max_video'  => 'sanitize_text_field',
            'gmb_categories_advanced_max_image'  => 'sanitize_text_field',
            'gmb_categories_twitter_card_type'   => 'sanitize_text_field',
            'gmb_categories_slack_sharing'       => 'sanitize_text_field',
            'gmb_categories_seo_controls'        => 'sanitize_text_field',
            'gmb_categories_bulk_editing'        => 'sanitize_text_field',
            'gmb_categories_remove_snippet'      => 'sanitize_text_field',
            'gmb_categories_custom_fields'       => 'sanitize_textarea_field',
            'gmb_categories_thumbnail_watermark' => 'sanitize_text_field',
        );

        foreach ($tax_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_titles_meta_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Local SEO settings registration
     */
    protected function register_local_seo_settings() {
        $local_options = array(
            'gmb_local_business_name'            => 'sanitize_text_field',
            'gmb_local_business_phone'           => 'sanitize_text_field',
            'gmb_local_business_address'         => 'sanitize_text_field',
            'gmb_local_use_multiple_locations'   => 'sanitize_text_field',
            'gmb_local_business_locations'       => array('GMB_Ranker_SEO_Admin', 'sanitize_business_locations'),
            'gmb_local_seo_type'                 => 'sanitize_text_field',
            'gmb_local_seo_business_subtype'     => 'sanitize_text_field',
            'gmb_local_seo_website_name'         => 'sanitize_text_field',
            'gmb_local_seo_website_alternate_name'=> 'sanitize_text_field',
            'gmb_local_seo_name'                 => 'sanitize_text_field',
            'gmb_local_seo_logo'                 => 'sanitize_text_field',
            'gmb_local_seo_url'                  => 'sanitize_text_field',
            'gmb_local_seo_email'                => 'sanitize_email',
            'gmb_local_seo_phone'                => 'sanitize_text_field',
            'gmb_local_seo_address_street'       => 'sanitize_text_field',
            'gmb_local_seo_address_locality'     => 'sanitize_text_field',
            'gmb_local_seo_address_region'       => 'sanitize_text_field',
            'gmb_local_seo_address_postal'       => 'sanitize_text_field',
            'gmb_local_seo_address_country'      => 'sanitize_text_field',
            'gmb_local_seo_about_page'           => 'sanitize_text_field',
            'gmb_local_seo_contact_page'         => 'sanitize_text_field',
            'gmb_local_business_lat'             => 'sanitize_text_field',
            'gmb_local_business_lng'             => 'sanitize_text_field',
            'gmb_local_business_maps_url'        => 'sanitize_text_field',
            'gmb_local_business_price_range'     => 'sanitize_text_field',
            'gmb_local_business_currencies'      => 'sanitize_text_field',
            'gmb_local_business_opening_hours'   => 'sanitize_text_field',

            // Social Profiles
            'gmb_social_facebook_page_url'       => 'sanitize_text_field',
            'gmb_social_facebook_authorship'     => 'sanitize_text_field',
            'gmb_social_facebook_admin'          => 'sanitize_text_field',
            'gmb_social_facebook_app_id'         => 'sanitize_text_field',
            'gmb_social_facebook_secret'         => 'sanitize_text_field',
            'gmb_social_twitter_username'        => 'sanitize_text_field',
            'gmb_social_instagram_url'           => 'sanitize_text_field',
            'gmb_social_linkedin_url'            => 'sanitize_text_field',
            'gmb_social_youtube_url'             => 'sanitize_text_field',
            'gmb_social_pinterest_url'           => 'sanitize_text_field',
            'gmb_social_tiktok_url'              => 'sanitize_text_field',
            'gmb_social_wikipedia_url'           => 'sanitize_text_field',
            'gmb_social_additional_profiles'     => 'sanitize_textarea_field',
        );

        foreach ($local_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_schema_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_titles_meta_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Security settings registration
     */
    protected function register_security_settings() {
        $sec_options = array(
            // Core Hardening
            'gmb_seo_disable_xmlrpc'             => 'sanitize_text_field',
            'gmb_seo_hide_wp_version'            => 'sanitize_text_field',
            'gmb_seo_restrict_rest_api'          => 'sanitize_text_field',
            'gmb_seo_block_uploads_execution'    => 'sanitize_text_field',
            'gmb_seo_block_sensitive_files'      => 'sanitize_text_field',
            'gmb_seo_disable_directory_indexing' => 'sanitize_text_field',
            'gmb_seo_disable_http_methods'       => 'sanitize_text_field',
            'gmb_seo_disable_application_passwords'=> 'sanitize_text_field',
            'gmb_seo_block_unauthorized_admins'  => 'sanitize_text_field',
            'gmb_seo_disable_open_registration'  => 'sanitize_text_field',
            'gmb_seo_prevent_user_enumeration'   => 'sanitize_text_field',
            'gmb_seo_mask_login_errors'          => 'sanitize_text_field',
            'gmb_seo_disable_file_edit'          => 'sanitize_text_field',
            'gmb_seo_allow_username_change'      => 'sanitize_text_field',

            // Brute Force & Login Shield
            'gmb_seo_login_lockout_enabled'      => 'sanitize_text_field',
            'gmb_seo_max_login_attempts'         => 'absint',
            'gmb_seo_lockout_duration_mins'      => 'absint',
            'gmb_seo_login_honeypot'             => 'sanitize_text_field',
            'gmb_seo_custom_login_slug'          => 'sanitize_text_field',
            'gmb_seo_session_expiration_hours'   => 'absint',
            'gmb_seo_hide_remember_me'           => 'sanitize_text_field',
            'gmb_seo_strong_password_policy'     => 'sanitize_text_field',
            'gmb_seo_enable_2fa'                 => 'sanitize_text_field',

            // WAF & Network Access Control
            'gmb_seo_404_exploit_lockout'        => 'sanitize_text_field',
            'gmb_seo_block_malicious_user_agents'=> 'sanitize_text_field',
            'gmb_seo_ip_whitelist'               => 'sanitize_textarea_field',
            'gmb_seo_ip_blacklist'               => 'sanitize_textarea_field',

            // HTTP Security Headers (Grade A+ Suite)
            'gmb_seo_enable_security_headers'    => 'sanitize_text_field',
            'gmb_seo_enable_hsts'                => 'sanitize_text_field',
            'gmb_seo_referrer_policy'            => 'sanitize_text_field',
            'gmb_seo_permissions_policy'         => 'sanitize_text_field',
            'gmb_seo_csp_frame_ancestors'        => 'sanitize_text_field',
            'gmb_seo_enable_coop'                => 'sanitize_text_field',
            'gmb_seo_enable_corp'                => 'sanitize_text_field',
            'gmb_seo_enable_coep'                => 'sanitize_text_field',
            'gmb_seo_cross_domain_policies'      => 'sanitize_text_field',
        );

        foreach ($sec_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_general_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_security_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Schema settings registration
     */
    protected function register_schema_settings() {
        $schema_options = array(
            'gmb_schema_about_page'                  => 'absint',
            'gmb_schema_contact_page'                => 'absint',
            'gmb_schema_enable_website'              => 'sanitize_text_field',
            'gmb_schema_website_name'                => 'sanitize_text_field',
            'gmb_schema_website_alt_name'            => 'sanitize_text_field',
            'gmb_schema_enable_sitelinks'            => 'sanitize_text_field',
            'gmb_schema_custom_jsonld'               => 'sanitize_textarea_field',
            'gmb_schema_rankmath_graph_integrate'    => 'sanitize_text_field',
            'gmb_schema_yoast_graph_integrate'       => 'sanitize_text_field',
            'gmb_schema_default_image'               => 'sanitize_text_field',
            'gmb_schema_enable_breadcrumbs'          => 'sanitize_text_field',
            'gmb_schema_author_type'                 => 'sanitize_text_field',
            'gmb_schema_author_sameas'               => 'sanitize_textarea_field',
            'gmb_schema_enable_speakable'            => 'sanitize_text_field',
            'gmb_schema_integrate_rankmath'          => 'sanitize_text_field',
            'gmb_schema_integrate_yoast'             => 'sanitize_text_field',
            'gmb_catalog_source'                     => 'sanitize_text_field',
        );

        foreach ($schema_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_schema_group', $opt, array('sanitize_callback' => $sanitizer));
        }
    }

    /**
     * Misc tools & modules settings registration
     */
    protected function register_misc_tools_settings() {
        $misc_options = array(
            'gmb_image_seo_alt_template'         => 'sanitize_text_field',
            'gmb_image_alt_template'             => 'sanitize_text_field',
            'gmb_image_seo_title_template'       => 'sanitize_text_field',
            'gmb_image_title_template'           => 'sanitize_text_field',
            'gmb_links_exclude_domains'          => 'sanitize_text_field',
            'gmb_redirect_orphan_attachments'    => 'sanitize_text_field',
            'gmb_nofollow_external_links'        => 'sanitize_text_field',
            'gmb_nofollow_image_links'           => 'sanitize_text_field',
            'gmb_new_window_external_links'      => 'sanitize_text_field',
            'gmb_affiliate_link_prefixes'        => 'sanitize_text_field',
            'gmb_llms_limit'                     => 'absint',
            'gmb_llms_title'                     => 'sanitize_text_field',
            'gmb_llms_desc'                      => 'sanitize_textarea_field',
            'gmb_llms_additional_content'        => 'sanitize_textarea_field',
            'gmb_toc_title'                      => 'sanitize_text_field',
            'gmb_toc_min_headings'               => 'absint',
            'gmb_toc_auto_insert'                => 'sanitize_text_field',
            'gmb_toc_collapsible'                => 'sanitize_text_field',
        );

        foreach ($misc_options as $opt => $sanitizer) {
            register_setting('gmb_ranker_settings_group', $opt, array('sanitize_callback' => $sanitizer));
            register_setting('gmb_ranker_general_group', $opt, array('sanitize_callback' => $sanitizer));
        }

        register_setting( 'gmb_ranker_settings_group', 'gmb_llms_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_settings_group', 'gmb_llms_taxonomies', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_settings_group', 'gmb_toc_levels', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_llms_post_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_llms_taxonomies', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'gmb_ranker_general_group', 'gmb_toc_levels', array('sanitize_callback' => 'sanitize_text_field') );
    }
}

