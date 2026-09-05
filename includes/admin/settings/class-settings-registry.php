<?php
/**
 * Settings Registry for GMB Ranker SEO Automation
 *
 * Centralized, deterministic, secure settings registration architecture.
 * Manages plugin options, sanitization callbacks, and options.php form persistence safety.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Settings_Registry')) {

    class GMB_Ranker_SEO_Settings_Registry {

        /**
         * Singleton instance
         *
         * @var GMB_Ranker_SEO_Settings_Registry|null
         */
        private static $instance = null;

        /**
         * Tracked registered options to prevent duplicate register_setting calls
         *
         * @var array<string, array<string>>
         */
        protected $registered_group_options = array();

        /**
         * Constructor
         */
        public function __construct() {
            add_filter('pre_update_option', array($this, 'protect_missing_options_on_save'), 10, 3);
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
         * Prevent WordPress options.php from wiping out registered settings omitted from current form submit.
         *
         * @param mixed  $value     The new, unsaved value.
         * @param string $option    The name of the option.
         * @param mixed  $old_value The old option value.
         * @return mixed
         */
        public function protect_missing_options_on_save($value, $option, $old_value) {
            // Protect secret key options from being overwritten by masked strings (containing '*' or '•')
            $secret_options = array(
                'gmb_ranker_api_key',
                'gmb_ai_openrouter_key',
                'gmb_ai_groq_key',
                'gmb_ai_gemini_key',
                'gmb_ai_openai_key',
                'gmb_ai_claude_key',
                'gmb_ai_nvidia_key',
                'gmb_integration_webhook_secret',
            );
            if (in_array($option, $secret_options, true)) {
                if (isset($_POST[$option . '_keep']) && (string) $_POST[$option . '_keep'] === '1' && empty($value)) {
                    return $old_value;
                }
                if (is_string($value) && (strpos($value, '*') !== false || strpos($value, '•') !== false)) {
                    return $old_value;
                }
            }

            if (isset($_POST['option_page'])) {
                $is_plugin_option = (strpos($option, 'gmb_') === 0 || strpos($option, 'gps_') === 0);
                if ($is_plugin_option && !isset($_POST[$option]) && !isset($_FILES[$option])) {
                    $current_group = sanitize_key(wp_unslash($_POST['option_page']));
                    
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

                    return $old_value;
                }
            }
            return $value;
        }

        /**
         * Register an Option for a specific Settings Group safely
         *
         * @param string          $group
         * @param string          $option
         * @param string|callable $sanitizer
         * @param string          $type
         */
        protected function register_option($group, $option, $sanitizer = 'sanitize_text_field', $type = 'string') {
            if (!isset($this->registered_group_options[$group])) {
                $this->registered_group_options[$group] = array();
            }

            if (in_array($option, $this->registered_group_options[$group], true)) {
                return; // Prevent duplicate registration in the same group
            }

            $args = array(
                'group'             => $group,
                'type'              => $type,
                'sanitize_callback' => $sanitizer,
            );

            register_setting($group, $option, $args);
            $this->registered_group_options[$group][] = $option;
        }

        /**
         * Register all plugin settings across all groups & modules
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
         * General Settings Registration
         */
        protected function register_general_settings() {
            $general_options = array(
                'gmb_ranker_api_key'              => 'sanitize_text_field',
                'gmb_webmaster_google_verify'     => 'sanitize_text_field',
                'gmb_webmaster_bing_verify'       => 'sanitize_text_field',
                'gmb_webmaster_pinterest_verify'  => 'sanitize_text_field',
                'gmb_webmaster_yandex_verify'     => 'sanitize_text_field',
                'gmb_webmaster_baidu_verify'      => 'sanitize_text_field',
                'gmb_rss_before_content'          => 'wp_kses_post',
                'gmb_rss_after_content'           => 'wp_kses_post',
                'gmb_strip_category_base'         => 'sanitize_text_field',
                'gmb_redirect_attachments'        => 'sanitize_text_field',
                'gmb_redirect_orphan_attachments' => 'esc_url_raw',
                'gmb_nofollow_external_links'     => 'sanitize_text_field',
                'gmb_nofollow_image_links'        => 'sanitize_text_field',
                'gmb_new_window_external_links'   => 'sanitize_text_field',
                'gmb_affiliate_link_prefixes'     => 'sanitize_textarea_field',
                'gmb_links_exclude_domains'       => 'sanitize_textarea_field',
                'gmb_image_seo_alt_template'      => 'sanitize_text_field',
                'gmb_image_alt_template'          => 'sanitize_text_field',
                'gmb_image_seo_title_template'    => 'sanitize_text_field',
                'gmb_image_title_template'        => 'sanitize_text_field',
                'gmb_llms_limit'                  => 'absint',
                'gmb_llms_title'                  => 'sanitize_text_field',
                'gmb_llms_desc'                   => 'sanitize_textarea_field',
                'gmb_llms_additional_content'     => 'sanitize_textarea_field',
                'gmb_toc_title'                   => 'sanitize_text_field',
                'gmb_toc_min_headings'            => 'absint',
                'gmb_toc_auto_insert'             => 'sanitize_text_field',
                'gmb_toc_collapsible'             => 'sanitize_text_field',
                'gmb_toc_position'                => 'sanitize_text_field',
                'gmb_ai_provider'                 => 'sanitize_text_field',
                'gmb_ai_openrouter_key'           => 'sanitize_text_field',
                'gmb_ai_openrouter_model'         => 'sanitize_text_field',
                'gmb_ai_groq_key'                 => 'sanitize_text_field',
                'gmb_ai_groq_model'               => 'sanitize_text_field',
                'gmb_ai_ollama_url'               => 'esc_url_raw',
                'gmb_ai_ollama_model'             => 'sanitize_text_field',
                'gmb_ai_nvidia_key'               => 'sanitize_text_field',
                'gmb_ai_nvidia_model'             => 'sanitize_text_field',
                'gmb_ai_fallback_enabled'         => 'sanitize_text_field',
                'gmb_ai_fallback_provider'        => 'sanitize_text_field',
                'gmb_ai_fallback_model'           => 'sanitize_text_field',
                'gmb_ai_max_retries'              => 'absint',
                'gmb_ai_provider_chain'           => array(__CLASS__, 'sanitize_provider_chain'),
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_general_group');
            foreach ($groups as $group) {
                foreach ($general_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
                $array_callback = array('GMB_Ranker_SEO_Admin', 'sanitize_array_setting');
                $this->register_option($group, 'gmb_llms_post_types', $array_callback, 'array');
                $this->register_option($group, 'gmb_llms_taxonomies', $array_callback, 'array');
                $this->register_option($group, 'gmb_toc_levels', $array_callback, 'array');
                $this->register_option($group, 'gmb_toc_post_types', $array_callback, 'array');
            }
        }

        /**
         * Sitemaps Settings Registration
         */
        protected function register_sitemaps_settings() {
            $sitemap_options = array(
                'gmb_ranker_module_sitemaps'         => 'sanitize_text_field',
                'gmb_ranker_sitemap_exclude_slugs'   => 'sanitize_text_field',
                'gmb_sitemap_items_per_page'          => 'absint',
                'gmb_sitemap_include_images'          => 'sanitize_text_field',
                'gmb_sitemap_include_featured_images' => 'sanitize_text_field',
                'gmb_sitemap_ping_search_engines'     => 'sanitize_text_field',
                'gmb_sitemap_excluded_posts'          => 'sanitize_text_field',
                'gmb_sitemap_excluded_terms'          => 'sanitize_text_field',
                'gmb_sitemap_custom_urls'             => 'sanitize_textarea_field',
                'gmb_sitemap_include_authors'         => 'sanitize_text_field',
                'gmb_sitemap_html_enable'             => 'sanitize_text_field',
                'gmb_sitemap_html_sort'               => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_sitemaps_group');
            foreach ($groups as $group) {
                foreach ($sitemap_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }

                $sitemap_pts = get_post_types(array('public' => true), 'names');
                foreach ($sitemap_pts as $s_pt) {
                    $this->register_option($group, 'gmb_sitemap_include_pt_' . $s_pt, 'sanitize_text_field');
                    $this->register_option($group, 'gmb_sitemap_images_pt_' . $s_pt, 'sanitize_text_field');
                }

                $sitemap_taxes = get_taxonomies(array('public' => true), 'names');
                foreach ($sitemap_taxes as $s_tax) {
                    $this->register_option($group, 'gmb_sitemap_include_tax_' . $s_tax, 'sanitize_text_field');
                    $this->register_option($group, 'gmb_sitemap_empty_tax_' . $s_tax, 'sanitize_text_field');
                }
            }
        }

        /**
         * Redirects Settings Registration
         */
        protected function register_redirects_settings() {
            $redirect_options = array(
                'gmb_ranker_auto_post_redirect' => 'sanitize_text_field',
                'gmb_ranker_fallback_behavior'  => 'sanitize_text_field',
                'gmb_ranker_fallback_url'       => 'esc_url_raw',
                'gmb_redirect_attachments'      => 'sanitize_text_field',
                'gmb_strip_category_base'       => 'sanitize_text_field',
                'gmb_ranker_404_limit'          => 'absint',
                'gmb_ranker_404_ignore_query'   => 'sanitize_text_field',
                'gmb_ranker_404_exclude_paths'  => 'sanitize_text_field',
                'gmb_ranker_redirects_rules'    => array('GMB_Ranker_SEO_Admin', 'sanitize_redirects_rules'),
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_redirects_group');
            foreach ($groups as $group) {
                foreach ($redirect_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : (is_array($sanitizer) ? 'array' : 'string');
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Integrations Settings Registration
         */
        protected function register_integrations_settings() {
            $integration_options = array(
                'gmb_ranker_api_key'            => 'sanitize_text_field',
                'gmb_ranker_cloud_sync'          => 'sanitize_text_field',
                'gmb_workspace_name'             => 'sanitize_text_field',
                'gmb_workspace_email'            => 'sanitize_email',
                'gmb_workspace_gsc_property'     => 'sanitize_text_field',
                'gmb_workspace_ga4_stream'       => 'sanitize_text_field',
                'gmb_workspace_gmb_location'     => 'sanitize_text_field',
                'gmb_integration_gmb_sync'       => 'sanitize_text_field',
                'gmb_integration_ga4_anonymize'  => 'sanitize_text_field',
                'gmb_integration_indexnow_key'   => 'sanitize_text_field',
                'gmb_ranker_indexnow_key'        => 'sanitize_text_field',
                'gmb_integration_indexnow_auto'  => 'sanitize_text_field',
                'gmb_integration_webhook_url'    => 'esc_url_raw',
                'gmb_integration_webhook_secret' => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_integrations_group');
            foreach ($groups as $group) {
                foreach ($integration_options as $opt => $sanitizer) {
                    $this->register_option($group, $opt, $sanitizer);
                }
            }

            // Google & Bing Indexing
            $google_callback = array('GMB_Ranker_SEO_Admin', 'sanitize_google_json_key');
            $array_callback  = array('GMB_Ranker_SEO_Admin', 'sanitize_array_setting');

            $this->register_option('gmb_ranker_settings_group', 'gmb_ranker_google_json_key', $google_callback);
            $this->register_option('gmb_ranker_settings_group', 'gmb_ranker_google_post_types', $array_callback, 'array');
            $this->register_option('gmb_ranker_google_indexing_group', 'gmb_ranker_google_json_key', $google_callback);
            $this->register_option('gmb_ranker_google_indexing_group', 'gmb_ranker_google_post_types', $array_callback, 'array');

            $this->register_option('gmb_ranker_settings_group', 'gmb_ranker_indexnow_post_types', $array_callback, 'array');
            $this->register_option('gmb_ranker_bing_indexing_group', 'gmb_ranker_indexnow_key', 'sanitize_text_field');
            $this->register_option('gmb_ranker_bing_indexing_group', 'gmb_ranker_indexnow_post_types', $array_callback, 'array');
            $this->register_option('gmb_ranker_bing_indexing_group', 'gmb_integration_indexnow_auto', 'sanitize_text_field');
        }

        /**
         * AI Provider Settings Registration
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
                'gmb_ai_nvidia_key'       => 'sanitize_text_field',
                'gmb_ai_nvidia_model'     => 'sanitize_text_field',
                'gmb_ai_fallback_enabled' => 'sanitize_text_field',
                'gmb_ai_fallback_provider'=> 'sanitize_text_field',
                'gmb_ai_fallback_model'   => 'sanitize_text_field',
                'gmb_ai_max_retries'      => 'absint',
                'gmb_ai_provider_chain'   => array(__CLASS__, 'sanitize_provider_chain'),
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_ai_provider_group', 'gmb_ranker_general_group', 'gmb_ranker_integrations_group');
            foreach ($groups as $group) {
                foreach ($ai_options as $opt => $sanitizer) {
                    $this->register_option($group, $opt, $sanitizer);
                }
            }
        }

        /** Normalize the persisted priority chain to trusted provider IDs and booleans. */
        public static function sanitize_provider_chain($value) {
            if (is_string($value)) {
                $decoded = json_decode(wp_unslash($value), true);
                $value = is_array($decoded) ? $decoded : array();
            }
            if (!is_array($value)) return array();
            $clean = array();
            $known = class_exists('GMB_Ranker_SEO_Integration_Registry')
                ? array_keys(GMB_Ranker_SEO_Integration_Registry::get_ai_providers())
                : array();
            foreach (array_values($value) as $index => $entry) {
                if (!is_array($entry)) continue;
                $provider = isset($entry['provider']) ? sanitize_key($entry['provider']) : '';
                if (!$provider || !in_array($provider, $known, true)) continue;
                $clean[] = array(
                    'provider' => $provider,
                    'enabled'  => !empty($entry['enabled']) ? 1 : 0,
                    'priority' => $index + 1,
                );
            }
            return $clean;
        }

        /**
         * Global Titles & Meta Settings Registration
         */
        protected function register_titles_meta_settings() {
            $tm_options = array(
                'gmb_metadata_post_title_template'    => 'sanitize_text_field',
                'gmb_metadata_post_desc_template'     => 'sanitize_text_field',
                'gmb_metadata_page_title_template'    => 'sanitize_text_field',
                'gmb_metadata_page_desc_template'     => 'sanitize_text_field',
                'gmb_metadata_global_robots'          => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
                'gmb_metadata_global_max_image'       => 'sanitize_text_field',
                'gmb_metadata_global_max_snippet'     => 'sanitize_text_field',
                'gmb_metadata_global_max_video'       => 'sanitize_text_field',
                'gmb_metadata_separator'              => 'sanitize_text_field',
                'gmb_metadata_capitalize_titles'      => 'sanitize_text_field',
                'gmb_metadata_og_thumbnail'           => 'sanitize_text_field',
                'gmb_metadata_twitter_card_type'      => 'sanitize_text_field',
                'gmb_homepage_title_template'         => 'sanitize_text_field',
                'gmb_homepage_desc_template'          => 'sanitize_text_field',
                'gmb_homepage_robots_meta_enable'     => 'sanitize_text_field',
                'gmb_homepage_robots_meta'            => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
                'gmb_author_archives_enable'          => 'sanitize_text_field',
                'gmb_author_base'                     => 'sanitize_text_field',
                'gmb_author_robots_meta_enable'       => 'sanitize_text_field',
                'gmb_author_robots_meta'              => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
                'gmb_author_archive_title'            => 'sanitize_text_field',
                'gmb_author_archive_desc'             => 'sanitize_text_field',
                'gmb_misc_disable_date_archives'      => 'sanitize_text_field',
                'gmb_misc_date_archive_title'         => 'sanitize_text_field',
                'gmb_misc_date_archive_desc'          => 'sanitize_text_field',
                'gmb_misc_404_title'                  => 'sanitize_text_field',
                'gmb_misc_search_title'               => 'sanitize_text_field',
                'gmb_misc_noindex_search_results'     => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_titles_meta_group');
            foreach ($groups as $group) {
                foreach ($tm_options as $opt => $sanitizer) {
                    $type = is_array($sanitizer) ? 'array' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Post Types Settings Registration
         */
        protected function register_post_types_settings() {
            $types = array('posts', 'pages', 'attachment', 'services', 'service_locations', 'team_members');
            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_titles_meta_group');

            foreach ($groups as $group) {
                foreach ($types as $t) {
                    $pt_options = array(
                        'gmb_' . $t . '_title_template'     => 'sanitize_text_field',
                        'gmb_' . $t . '_desc_template'      => 'sanitize_text_field',
                        'gmb_' . $t . '_schema_type'        => 'sanitize_text_field',
                        'gmb_' . $t . '_schema_headline'    => 'sanitize_text_field',
                        'gmb_' . $t . '_schema_desc'        => 'sanitize_textarea_field',
                        'gmb_' . $t . '_article_type'       => 'sanitize_text_field',
                        'gmb_' . $t . '_robots_meta_enable' => 'sanitize_text_field',
                        'gmb_' . $t . '_robots_meta'        => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
                    );

                    foreach ($pt_options as $opt => $sanitizer) {
                        $type = is_array($sanitizer) ? 'array' : 'string';
                        $this->register_option($group, $opt, $sanitizer, $type);
                    }
                }

                if (function_exists('get_post_types')) {
                    $public_pts = get_post_types(array('public' => true), 'names');
                    foreach ($public_pts as $pt_name) {
                        $this->register_option($group, 'gmb_' . $pt_name . '_schema_type', 'sanitize_text_field');
                        $this->register_option($group, 'gmb_' . $pt_name . '_article_type', 'sanitize_text_field');
                        $this->register_option($group, 'gmb_' . $pt_name . '_schema_headline', 'sanitize_text_field');
                        $this->register_option($group, 'gmb_' . $pt_name . '_schema_desc', 'sanitize_textarea_field');
                    }
                }
            }
        }

        /**
         * Taxonomies Settings Registration
         */
        protected function register_taxonomies_settings() {
            $tax_options = array(
                'gmb_categories_archive_title'      => 'sanitize_text_field',
                'gmb_categories_archive_desc'       => 'sanitize_text_field',
                'gmb_categories_robots_meta_enable' => 'sanitize_text_field',
                'gmb_categories_robots_meta'        => array('GMB_Ranker_SEO_Admin', 'sanitize_robots_array'),
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_titles_meta_group');
            foreach ($groups as $group) {
                foreach ($tax_options as $opt => $sanitizer) {
                    $type = is_array($sanitizer) ? 'array' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Local SEO Settings Registration
         */
        protected function register_local_seo_settings() {
            $local_options = array(
                'gmb_local_business_name'          => 'sanitize_text_field',
                'gmb_local_business_phone'         => 'sanitize_text_field',
                'gmb_local_business_address'       => 'sanitize_text_field',
                'gmb_local_use_multiple_locations' => 'sanitize_text_field',
                'gmb_local_business_locations'     => array('GMB_Ranker_SEO_Admin', 'sanitize_business_locations'),
                'gmb_local_seo_type'               => 'sanitize_text_field',
                'gmb_local_seo_business_subtype'   => 'sanitize_text_field',
                'gmb_local_seo_website_name'       => 'sanitize_text_field',
                'gmb_local_seo_name'               => 'sanitize_text_field',
                'gmb_local_seo_logo'               => 'esc_url_raw',
                'gmb_local_seo_url'                => 'esc_url_raw',
                'gmb_local_seo_email'              => 'sanitize_email',
                'gmb_local_seo_phone'              => 'sanitize_text_field',
                'gmb_social_facebook_page_url'     => 'esc_url_raw',
                'gmb_social_twitter_username'      => 'sanitize_text_field',
                'gmb_social_instagram_url'         => 'esc_url_raw',
                'gmb_social_linkedin_url'          => 'esc_url_raw',
                'gmb_social_youtube_url'           => 'esc_url_raw',
                'gmb_social_pinterest_url'         => 'esc_url_raw',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_schema_group', 'gmb_ranker_titles_meta_group');
            foreach ($groups as $group) {
                foreach ($local_options as $opt => $sanitizer) {
                    $type = is_array($sanitizer) ? 'array' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Security Settings Registration
         */
        protected function register_security_settings() {
            $sec_options = array(
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
                'gmb_seo_login_lockout_enabled'      => 'sanitize_text_field',
                'gmb_seo_max_login_attempts'         => 'absint',
                'gmb_seo_lockout_duration_mins'      => 'absint',
                'gmb_seo_login_honeypot'             => 'sanitize_text_field',
                'gmb_seo_custom_login_slug'          => 'sanitize_text_field',
                'gmb_seo_session_expiration_hours'   => 'absint',
                'gmb_seo_hide_remember_me'           => 'sanitize_text_field',
                'gmb_seo_strong_password_policy'     => 'sanitize_text_field',
                'gmb_seo_enable_2fa'                 => 'sanitize_text_field',
                'gmb_seo_enable_security_headers'    => 'sanitize_text_field',
                'gmb_seo_enable_hsts'                => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_general_group', 'gmb_ranker_security_group');
            foreach ($groups as $group) {
                foreach ($sec_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Schema Settings Registration
         */
        protected function register_schema_settings() {
            $schema_options = array(
                'gmb_schema_about_page'         => 'absint',
                'gmb_schema_contact_page'       => 'absint',
                'gmb_schema_enable_website'     => 'sanitize_text_field',
                'gmb_schema_website_name'       => 'sanitize_text_field',
                'gmb_schema_website_alt_name'   => 'sanitize_text_field',
                'gmb_schema_enable_sitelinks'   => 'sanitize_text_field',
                'gmb_schema_custom_jsonld'      => 'sanitize_textarea_field',
                'gmb_schema_default_image'      => 'esc_url_raw',
                'gmb_schema_enable_breadcrumbs' => 'sanitize_text_field',
                'gmb_schema_author_type'        => 'sanitize_text_field',
                'gmb_catalog_source'            => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_schema_group');
            foreach ($groups as $group) {
                foreach ($schema_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }

        /**
         * Misc Tools & Modules Settings Registration
         */
        protected function register_misc_tools_settings() {
            $misc_options = array(
                'gmb_image_seo_alt_template'   => 'sanitize_text_field',
                'gmb_image_alt_template'       => 'sanitize_text_field',
                'gmb_image_seo_title_template' => 'sanitize_text_field',
                'gmb_image_title_template'     => 'sanitize_text_field',
                'gmb_toc_title'                => 'sanitize_text_field',
                'gmb_toc_min_headings'         => 'absint',
                'gmb_toc_auto_insert'          => 'sanitize_text_field',
                'gmb_toc_collapsible'          => 'sanitize_text_field',
            );

            $groups = array('gmb_ranker_settings_group', 'gmb_ranker_general_group');
            foreach ($groups as $group) {
                foreach ($misc_options as $opt => $sanitizer) {
                    $type = ($sanitizer === 'absint') ? 'integer' : 'string';
                    $this->register_option($group, $opt, $sanitizer, $type);
                }
            }
        }
    }
}
