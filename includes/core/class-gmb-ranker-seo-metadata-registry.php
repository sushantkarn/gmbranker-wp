<?php
/**
 * Canonical Metadata Registry & Domain Manager Service
 *
 * Centralizes Titles, Descriptions, Robots, OpenGraph, Social, Local SEO,
 * template variable tokens, and view model generation for titles-meta.php.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Metadata_Registry {

    /**
     * Get canonical subtab catalog definition
     *
     * @return array
     */
    public static function get_tab_catalog() {
        return array(
            'metadata' => array(
                'id'       => 'metadata',
                'label'    => __('Global Meta', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>',
                'group'    => 'general',
            ),
            'local' => array(
                'id'       => 'local',
                'label'    => __('Local SEO', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
                'group'    => 'general',
            ),
            'social' => array(
                'id'       => 'social',
                'label'    => __('Social Meta', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
                'group'    => 'general',
            ),
            'homepage' => array(
                'id'       => 'homepage',
                'label'    => __('Homepage', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
                'group'    => 'general',
            ),
            'authors' => array(
                'id'       => 'authors',
                'label'    => __('Authors', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                'group'    => 'general',
            ),
            'misc' => array(
                'id'       => 'misc',
                'label'    => __('Misc Pages', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>',
                'group'    => 'general',
            ),
            'posts' => array(
                'id'       => 'posts',
                'label'    => __('Posts', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>',
                'group'    => 'post_types',
            ),
            'pages' => array(
                'id'       => 'pages',
                'label'    => __('Pages', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>',
                'group'    => 'post_types',
            ),
            'attachments' => array(
                'id'       => 'attachments',
                'label'    => __('Attachments', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
                'group'    => 'post_types',
            ),
            'services' => array(
                'id'       => 'services',
                'label'    => __('Services', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
                'group'    => 'post_types',
            ),
            'service_locations' => array(
                'id'       => 'service_locations',
                'label'    => __('Service Locations', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
                'group'    => 'post_types',
            ),
            'team_members' => array(
                'id'       => 'team_members',
                'label'    => __('Team Members', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                'group'    => 'post_types',
            ),
            'categories' => array(
                'id'       => 'categories',
                'label'    => __('Categories', 'gmb-ranker-seo-automation'),
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
                'group'    => 'taxonomies',
            ),
        );
    }

    /**
     * Get title separators options
     *
     * @return array
     */
    public static function get_separator_options() {
        return array('-', '—', '|', '»', '•', '*');
    }

    /**
     * Get twitter card options
     *
     * @return array
     */
    public static function get_twitter_card_options() {
        return array(
            'summary_large_image' => __('Summary Card with Large Image (Recommended)', 'gmb-ranker-seo-automation'),
            'summary'             => __('Summary Card (Small Square Thumbnail)', 'gmb-ranker-seo-automation'),
            'app'                 => __('App Card', 'gmb-ranker-seo-automation'),
            'player'              => __('Player Card (Video / Audio)', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get max image preview options
     *
     * @return array
     */
    public static function get_max_image_options() {
        return array(
            'large'    => __('large (Recommended for Google Discover)', 'gmb-ranker-seo-automation'),
            'standard' => __('standard', 'gmb-ranker-seo-automation'),
            'none'     => __('none', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get all sitewide metadata settings
     *
     * @return array
     */
    public static function get_settings() {
        $global_robots_raw = get_option('gmb_metadata_global_robots', 'index');
        $global_robots_array = is_array($global_robots_raw) ? $global_robots_raw : array_map('trim', explode(',', strtolower((string)$global_robots_raw)));

        return array(
            'module_enabled'          => get_option('gmb_ranker_module_metadata', '1') !== '0' && get_option('gmb_ranker_module_metadata', '1') !== 'off',
            
            // Global Meta
            'post_title_temp'         => get_option('gmb_posts_title_template', '%title% %sep% %sitename%'),
            'post_desc_temp'          => get_option('gmb_posts_description_template', '%excerpt%'),
            'page_title_temp'         => get_option('gmb_pages_title_template', '%title% %sep% %sitename%'),
            'page_desc_temp'          => get_option('gmb_pages_description_template', '%excerpt%'),
            'global_robots'           => $global_robots_array,
            'global_max_image'        => get_option('gmb_metadata_global_max_image', 'large'),
            'global_max_snippet'      => get_option('gmb_metadata_global_max_snippet', '-1'),
            'global_max_video'        => get_option('gmb_metadata_global_max_video', '-1'),
            'separator'               => get_option('gmb_metadata_separator', '-'),
            'capitalize_titles'       => get_option('gmb_metadata_capitalize_titles', '0') === '1',
            'og_thumbnail'            => get_option('gmb_metadata_og_thumbnail', ''),
            'twitter_card_type'       => get_option('gmb_metadata_twitter_card_type', 'summary_large_image'),
            'google_verify'           => get_option('gmb_webmaster_google_verify', ''),
            'bing_verify'             => get_option('gmb_webmaster_bing_verify', ''),
            'pinterest_verify'        => get_option('gmb_webmaster_pinterest_verify', ''),
            'baidu_verify'            => get_option('gmb_webmaster_baidu_verify', ''),
            'yandex_verify'           => get_option('gmb_webmaster_yandex_verify', ''),
            'rss_after_content'       => get_option('gmb_rss_after_content', ''),
            'use_multiple_locations'  => get_option('gmb_local_use_multiple_locations', '0') === '1',
        );
    }

    /**
     * Get complete validated View Model for titles-meta presentation layer
     *
     * @param string $requested_subtab
     * @return array
     */
    public static function get_view_model($requested_subtab = 'metadata') {
        $catalog = self::get_tab_catalog();
        $allowed_subtabs = array_keys($catalog);
        $allowed_subtabs[] = 'settings';

        $active_subtab = in_array($requested_subtab, $allowed_subtabs, true) ? $requested_subtab : 'metadata';
        if ($active_subtab === 'settings') {
            $active_subtab = 'metadata';
        }

        $settings = self::get_settings();

        return array(
            'module_enabled' => $settings['module_enabled'],
            'active_subtab'  => $active_subtab,
            'tab_catalog'    => $catalog,
            'settings'       => $settings,
            'separators'     => self::get_separator_options(),
            'twitter_cards'  => self::get_twitter_card_options(),
            'max_images'     => self::get_max_image_options(),
        );
    }
}
