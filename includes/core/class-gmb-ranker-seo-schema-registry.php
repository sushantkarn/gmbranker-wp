<?php
/**
 * Canonical Schema Registry & Domain Manager Service
 *
 * Centralizes Schema.org type definitions, catalog metadata, SVG icons,
 * preset blueprints, view model generation, and JSON-LD structural validation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Schema_Registry {

    /**
     * Get SVG icon markup for a schema type
     *
     * @param string $type
     * @return string
     */
    public static function get_schema_icon_svg($type) {
        $type = strtolower(trim((string)$type));
        $type = str_replace(array(' ', '-', '_'), '', $type);

        switch ($type) {
            case 'review':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
            case 'aggregaterating':
            case 'rating':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path><path d="M12 17.77V2"></path></svg>';
            case 'organization':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 21a8 8 0 0 0-16 0"></path><circle cx="10" cy="8" r="5"></circle><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"></path></svg>';
            case 'webpage':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>';
            case 'breadcrumblist':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>';
            case 'medicalclinic':
            case 'medicalentity':
            case 'medicalbusiness':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>';
            case 'article':
            case 'blogposting':
            case 'newsarticle':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
            case 'book':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
            case 'carousel':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M10 4v16"></path></svg>';
            case 'course':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>';
            case 'dataset':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>';
            case 'event':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
            case 'faq':
            case 'faqpage':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            case 'factcheck':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            case 'howto':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';
            case 'job':
            case 'jobposting':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
            case 'localbusiness':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
            case 'movie':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>';
            case 'music':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>';
            case 'person':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
            case 'product':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>';
            case 'recipe':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
            case 'restaurant':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v4M21 2v20M21 2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4"></path></svg>';
            case 'service':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>';
            case 'software':
            case 'softwareapplication':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>';
            case 'video':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>';
            default:
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
        }
    }

    /**
     * Get complete catalog of supported Schema.org types
     *
     * @return array
     */
    public static function get_catalog_schemas() {
        return array(
            array('type' => 'AggregateRating', 'name' => __('Aggregate Rating', 'gmb-ranker-seo-automation'), 'icon_key' => 'aggregaterating'),
            array('type' => 'Article', 'name' => __('Article', 'gmb-ranker-seo-automation'), 'icon_key' => 'article'),
            array('type' => 'Book', 'name' => __('Book', 'gmb-ranker-seo-automation'), 'icon_key' => 'book'),
            array('type' => 'BreadcrumbList', 'name' => __('Breadcrumbs', 'gmb-ranker-seo-automation'), 'icon_key' => 'breadcrumblist'),
            array('type' => 'Carousel', 'name' => __('Carousel', 'gmb-ranker-seo-automation'), 'icon_key' => 'carousel'),
            array('type' => 'Course', 'name' => __('Course', 'gmb-ranker-seo-automation'), 'icon_key' => 'course'),
            array('type' => 'Dataset', 'name' => __('Dataset', 'gmb-ranker-seo-automation'), 'icon_key' => 'dataset'),
            array('type' => 'Event', 'name' => __('Event', 'gmb-ranker-seo-automation'), 'icon_key' => 'event'),
            array('type' => 'FAQPage', 'name' => __('FAQ', 'gmb-ranker-seo-automation'), 'icon_key' => 'faqpage'),
            array('type' => 'FactCheck', 'name' => __('FactCheck', 'gmb-ranker-seo-automation'), 'icon_key' => 'factcheck'),
            array('type' => 'HowTo', 'name' => __('HowTo', 'gmb-ranker-seo-automation'), 'icon_key' => 'howto'),
            array('type' => 'JobPosting', 'name' => __('Job Posting', 'gmb-ranker-seo-automation'), 'icon_key' => 'jobposting'),
            array('type' => 'LocalBusiness', 'name' => __('Local Business', 'gmb-ranker-seo-automation'), 'icon_key' => 'localbusiness'),
            array('type' => 'MedicalClinic', 'name' => __('Medical Clinic', 'gmb-ranker-seo-automation'), 'icon_key' => 'medicalclinic'),
            array('type' => 'Movie', 'name' => __('Movie', 'gmb-ranker-seo-automation'), 'icon_key' => 'movie'),
            array('type' => 'Music', 'name' => __('Music', 'gmb-ranker-seo-automation'), 'icon_key' => 'music'),
            array('type' => 'Organization', 'name' => __('Organization', 'gmb-ranker-seo-automation'), 'icon_key' => 'organization'),
            array('type' => 'Person', 'name' => __('Person', 'gmb-ranker-seo-automation'), 'icon_key' => 'person'),
            array('type' => 'Product', 'name' => __('Product', 'gmb-ranker-seo-automation'), 'icon_key' => 'product'),
            array('type' => 'Recipe', 'name' => __('Recipe', 'gmb-ranker-seo-automation'), 'icon_key' => 'recipe'),
            array('type' => 'Restaurant', 'name' => __('Restaurant', 'gmb-ranker-seo-automation'), 'icon_key' => 'restaurant'),
            array('type' => 'Review', 'name' => __('Review', 'gmb-ranker-seo-automation'), 'icon_key' => 'review'),
            array('type' => 'Service', 'name' => __('Service', 'gmb-ranker-seo-automation'), 'icon_key' => 'service'),
            array('type' => 'SoftwareApplication', 'name' => __('Software Application', 'gmb-ranker-seo-automation'), 'icon_key' => 'softwareapplication'),
            array('type' => 'Video', 'name' => __('Video', 'gmb-ranker-seo-automation'), 'icon_key' => 'video'),
            array('type' => 'WebPage', 'name' => __('Web Page', 'gmb-ranker-seo-automation'), 'icon_key' => 'webpage'),
        );
    }

    /**
     * Get all sitewide Schema settings
     *
     * @return array
     */
    public static function get_settings() {
        return array(
            'module_enabled'       => get_option('gmb_ranker_module_schema', '1') !== '0' && get_option('gmb_ranker_module_schema', '1') !== 'off',
            'enable_website'       => get_option('gmb_schema_enable_website', '1'),
            'website_name'         => get_option('gmb_schema_website_name', get_bloginfo('name')),
            'website_alt_name'     => get_option('gmb_schema_website_alt_name', ''),
            'about_page'           => get_option('gmb_schema_about_page', get_option('gmb_local_seo_about_page', 0)),
            'contact_page'         => get_option('gmb_schema_contact_page', get_option('gmb_local_seo_contact_page', 0)),
            'default_image'        => get_option('gmb_schema_default_image', ''),
            'enable_sitelinks'     => get_option('gmb_schema_enable_sitelinks', '1'),
            'enable_breadcrumbs'   => get_option('gmb_schema_enable_breadcrumbs', '1'),
            'author_type'          => get_option('gmb_schema_author_type', 'Person'),
            'author_sameas'        => get_option('gmb_schema_author_sameas', ''),
            'enable_speakable'     => get_option('gmb_schema_enable_speakable', '0'),
            'local_type'           => get_option('gmb_local_seo_type', 'Organization'),
            'local_name'           => get_option('gmb_local_seo_name', get_bloginfo('name')),
            'local_logo'           => get_option('gmb_local_seo_logo', ''),
            'local_phone'          => get_option('gmb_local_seo_phone', ''),
            'local_email'          => get_option('gmb_local_seo_email', get_bloginfo('admin_email')),
            'address_street'       => get_option('gmb_local_seo_address_street', get_option('gmb_local_street_address', '')),
            'address_locality'     => get_option('gmb_local_seo_address_locality', get_option('gmb_local_locality', '')),
            'address_region'       => get_option('gmb_local_seo_address_region', get_option('gmb_local_region', '')),
            'address_postal'       => get_option('gmb_local_seo_address_postal', get_option('gmb_local_postal_code', '')),
            'address_country'      => get_option('gmb_local_seo_address_country', get_option('gmb_local_country', '')),
            'lat'                  => get_option('gmb_local_business_lat', ''),
            'lng'                  => get_option('gmb_local_business_lng', ''),
            'price_range'          => get_option('gmb_local_business_price_range', '$$'),
            'opening_hours'        => get_option('gmb_local_business_opening_hours', 'Mo-Fr 09:00-18:00'),
            'facebook_url'         => get_option('gmb_social_facebook_page_url', ''),
            'twitter_handle'       => get_option('gmb_social_twitter_username', ''),
            'linkedin_url'         => get_option('gmb_social_linkedin_url', ''),
            'youtube_url'          => get_option('gmb_social_youtube_url', ''),
            'instagram_url'        => get_option('gmb_social_instagram_url', ''),
            'wikipedia_url'        => get_option('gmb_social_wikipedia_url', ''),
            'custom_jsonld'        => get_option('gmb_schema_custom_jsonld', ''),
            'integrate_rankmath'   => get_option('gmb_schema_integrate_rankmath', '1'),
            'integrate_yoast'      => get_option('gmb_schema_integrate_yoast', '1'),
        );
    }

    /**
     * Get complete validated View Model for schema presentation layer
     *
     * @param string $requested_subtab
     * @return array
     */
    public static function get_view_model($requested_subtab = 'general') {
        $settings = self::get_settings();

        $allowed_subtabs = array('general', 'templates', 'post-types', 'knowledge', 'custom', 'presets');
        $active_subtab   = in_array($requested_subtab, $allowed_subtabs, true) ? $requested_subtab : 'general';

        $repo = class_exists('GMB_Ranker_SEO_Schema_Repository') ? new GMB_Ranker_SEO_Schema_Repository() : null;
        $templates_raw = $repo ? $repo->get_all_templates() : get_option('gmb_schema_templates', array());
        if (!is_array($templates_raw)) {
            $templates_raw = array();
        }

        $validated_templates = array();
        foreach ($templates_raw as $tpl) {
            if (!is_array($tpl)) {
                continue;
            }
            $t_id     = isset($tpl['id']) ? sanitize_text_field($tpl['id']) : 'schema_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
            $t_title  = isset($tpl['title']) ? sanitize_text_field($tpl['title']) : (isset($tpl['name']) ? sanitize_text_field($tpl['name']) : __('Untitled Template', 'gmb-ranker-seo-automation'));
            $t_type   = isset($tpl['type']) ? sanitize_text_field($tpl['type']) : 'Custom';
            $t_status = (isset($tpl['status']) && $tpl['status'] === 'active') ? 'active' : ((isset($tpl['enabled']) && $tpl['enabled']) ? 'active' : 'inactive');
            $c_arr    = isset($tpl['conditions']) && is_array($tpl['conditions']) ? $tpl['conditions'] : array();

            $validated_templates[] = array(
                'id'         => $t_id,
                'title'      => $t_title,
                'type'       => $t_type,
                'status'     => $t_status,
                'conditions' => $c_arr,
            );
        }

        // Available post types & categories
        $avail_pts = array();
        $public_post_types = get_post_types(array('public' => true), 'objects');
        foreach ($public_post_types as $p_slug => $p_obj) {
            if ($p_slug === 'attachment') continue;
            $avail_pts[] = array(
                'slug'  => $p_slug,
                'label' => !empty($p_obj->labels->name) ? $p_obj->labels->name : $p_slug,
            );
        }

        $avail_cats = array();
        $categories = get_categories(array('hide_empty' => false));
        if (is_array($categories)) {
            foreach ($categories as $c_obj) {
                $avail_cats[] = array(
                    'slug' => $c_obj->slug,
                    'name' => $c_obj->name,
                );
            }
        }

        return array(
            'module_enabled'  => $settings['module_enabled'],
            'active_subtab'   => $active_subtab,
            'settings'        => $settings,
            'catalog_schemas' => self::get_catalog_schemas(),
            'templates'       => $validated_templates,
            'post_types'      => $avail_pts,
            'categories'      => $avail_cats,
        );
    }
}
