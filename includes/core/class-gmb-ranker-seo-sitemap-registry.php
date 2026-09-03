<?php
/**
 * Canonical Sitemap Registry & Domain Manager Service
 *
 * Centralizes XML/HTML sitemap settings, post-type/taxonomy sitemap eligibility,
 * URL endpoint resolution, diagnostics calculation, and view model generation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Sitemap_Registry {

    /**
     * Get all sitewide sitemap settings
     *
     * @return array
     */
    public static function get_settings() {
        return array(
            'module_enabled'          => get_option('gmb_ranker_module_sitemaps', '1') !== '0' && get_option('gmb_ranker_module_sitemaps', '1') !== 'off',
            'items_per_page'          => intval(get_option('gmb_sitemap_items_per_page', '1000')),
            'include_images'          => get_option('gmb_sitemap_include_images', '1') !== '0',
            'include_featured_images' => get_option('gmb_sitemap_include_featured_images', '1') !== '0',
            'ping_search_engines'     => get_option('gmb_sitemap_ping_search_engines', '1') !== '0',
            'excluded_posts'          => get_option('gmb_sitemap_excluded_posts', ''),
            'excluded_terms'          => get_option('gmb_sitemap_excluded_terms', ''),
            'exclude_slugs'           => get_option('gmb_ranker_sitemap_exclude_slugs', ''),
            'custom_urls'             => get_option('gmb_sitemap_custom_urls', ''),
            'include_authors'         => get_option('gmb_sitemap_include_authors', '0') === '1',
            'html_enable'             => get_option('gmb_sitemap_html_enable', '1') !== '0',
            'html_sort'               => get_option('gmb_sitemap_html_sort', 'published'),
        );
    }

    /**
     * Resolve sitemap index main URL
     *
     * @return string
     */
    public static function get_sitemap_index_url() {
        return home_url('/sitemap_index.xml');
    }

    /**
     * Resolve post-type sitemap URL
     *
     * @param string $post_type
     * @return string
     */
    public static function get_post_type_sitemap_url($post_type) {
        return home_url('/' . sanitize_key($post_type) . '-sitemap.xml');
    }

    /**
     * Resolve taxonomy sitemap URL
     *
     * @param string $taxonomy
     * @return string
     */
    public static function get_taxonomy_sitemap_url($taxonomy) {
        return home_url('/' . sanitize_key($taxonomy) . '-sitemap.xml');
    }

    /**
     * Resolve author sitemap URL
     *
     * @return string
     */
    public static function get_author_sitemap_url() {
        return home_url('/author-sitemap.xml');
    }

    /**
     * Resolve custom sitemap URL
     *
     * @return string
     */
    public static function get_custom_sitemap_url() {
        return home_url('/custom-sitemap.xml');
    }

    /**
     * Get author count with published posts
     *
     * @return int
     */
    public static function get_eligible_author_count() {
        $public_pts = get_post_types(array('public' => true), 'names');
        if (empty($public_pts)) {
            $public_pts = array('post', 'page');
        }
        $authors = get_users(array(
            'has_published_posts' => array_values($public_pts),
            'fields'              => 'ID',
        ));
        return is_array($authors) ? count($authors) : 0;
    }

    /**
     * Get count of valid custom URLs
     *
     * @return int
     */
    public static function get_custom_url_count() {
        $custom_urls_str = get_option('gmb_sitemap_custom_urls', '');
        if (empty(trim($custom_urls_str))) {
            return 0;
        }
        $list = array_filter(array_map('trim', explode("\n", $custom_urls_str)));
        return count($list);
    }

    /**
     * Get sitemap index diagnostics rows
     *
     * @return array
     */
    public static function get_diagnostics() {
        $rows = array();

        // 1. Main Index
        $rows[] = array(
            'name'     => 'sitemap_index.xml',
            'type'     => __('Sitemap Index', 'gmb-ranker-seo-automation'),
            'badge'    => __('Main Index', 'gmb-ranker-seo-automation'),
            'items'    => __('All Enabled Sub-sitemaps', 'gmb-ranker-seo-automation'),
            'status'   => 'active',
            'status_l' => __('Active', 'gmb-ranker-seo-automation'),
            'url'      => self::get_sitemap_index_url(),
        );

        // 2. Post Types Sub-sitemaps
        $public_pts = get_post_types(array('public' => true), 'objects');
        foreach ($public_pts as $pt_name => $pt_obj) {
            $is_active = get_option('gmb_sitemap_include_pt_' . $pt_name, ($pt_name === 'attachment' ? '0' : '1')) !== '0';
            $c = wp_count_posts($pt_name);
            $count_num = !empty($c->publish) ? intval($c->publish) : 0;

            $status = 'disabled';
            $status_l = __('Disabled', 'gmb-ranker-seo-automation');
            if ($is_active) {
                if ($count_num > 0) {
                    $status = 'active';
                    $status_l = __('Active', 'gmb-ranker-seo-automation');
                } else {
                    $status = 'empty';
                    $status_l = __('Empty', 'gmb-ranker-seo-automation');
                }
            }

            $rows[] = array(
                'name'     => $pt_name . '-sitemap.xml',
                'type'     => sprintf(__('Post Type (%s)', 'gmb-ranker-seo-automation'), esc_html($pt_obj->labels->singular_name ?: $pt_name)),
                'badge'    => '',
                'items'    => sprintf(_n('%d URL', '%d URLs', $count_num, 'gmb-ranker-seo-automation'), $count_num),
                'status'   => $status,
                'status_l' => $status_l,
                'url'      => self::get_post_type_sitemap_url($pt_name),
            );
        }

        // 3. Taxonomies Sub-sitemaps
        $public_taxes = get_taxonomies(array('public' => true), 'objects');
        foreach ($public_taxes as $tax_name => $tax_obj) {
            $is_active = get_option('gmb_sitemap_include_tax_' . $tax_name, ($tax_name === 'post_format' ? '0' : '1')) !== '0';
            $tc = wp_count_terms(array('taxonomy' => $tax_name, 'hide_empty' => false));
            $t_num = is_wp_error($tc) ? 0 : intval($tc);

            $status = 'disabled';
            $status_l = __('Disabled', 'gmb-ranker-seo-automation');
            if ($is_active) {
                if ($t_num > 0) {
                    $status = 'active';
                    $status_l = __('Active', 'gmb-ranker-seo-automation');
                } else {
                    $status = 'empty';
                    $status_l = __('Empty', 'gmb-ranker-seo-automation');
                }
            }

            $rows[] = array(
                'name'     => $tax_name . '-sitemap.xml',
                'type'     => sprintf(__('Taxonomy (%s)', 'gmb-ranker-seo-automation'), esc_html($tax_obj->labels->singular_name ?: $tax_name)),
                'badge'    => '',
                'items'    => sprintf(_n('%d Term', '%d Terms', $t_num, 'gmb-ranker-seo-automation'), $t_num),
                'status'   => $status,
                'status_l' => $status_l,
                'url'      => self::get_taxonomy_sitemap_url($tax_name),
            );
        }

        // 4. Authors Sub-sitemap
        $is_authors_active = get_option('gmb_sitemap_include_authors', '0') === '1';
        $author_count = self::get_eligible_author_count();
        $author_status = 'disabled';
        $author_status_l = __('Disabled', 'gmb-ranker-seo-automation');
        if ($is_authors_active) {
            if ($author_count > 0) {
                $author_status = 'active';
                $author_status_l = __('Active', 'gmb-ranker-seo-automation');
            } else {
                $author_status = 'empty';
                $author_status_l = __('Empty', 'gmb-ranker-seo-automation');
            }
        }

        $rows[] = array(
            'name'     => 'author-sitemap.xml',
            'type'     => __('Authors Archive', 'gmb-ranker-seo-automation'),
            'badge'    => '',
            'items'    => sprintf(_n('%d Author', '%d Authors', $author_count, 'gmb-ranker-seo-automation'), $author_count),
            'status'   => $author_status,
            'status_l' => $author_status_l,
            'url'      => self::get_author_sitemap_url(),
        );

        // 5. Custom URLs Sub-sitemap
        $custom_count = self::get_custom_url_count();
        if ($custom_count > 0) {
            $rows[] = array(
                'name'     => 'custom-sitemap.xml',
                'type'     => __('Custom URLs', 'gmb-ranker-seo-automation'),
                'badge'    => '',
                'items'    => sprintf(_n('%d URL', '%d URLs', $custom_count, 'gmb-ranker-seo-automation'), $custom_count),
                'status'   => 'active',
                'status_l' => __('Active', 'gmb-ranker-seo-automation'),
                'url'      => self::get_custom_sitemap_url(),
            );
        }

        return $rows;
    }

    /**
     * Get complete validated View Model for sitemaps presentation layer
     *
     * @param string $requested_subtab
     * @return array
     */
    public static function get_view_model($requested_subtab = 'general') {
        $settings = self::get_settings();

        $allowed_subtabs = array('general', 'post-types', 'taxonomies', 'authors', 'html', 'index');
        $active_subtab   = in_array($requested_subtab, $allowed_subtabs, true) ? $requested_subtab : 'general';

        // Post types view model data
        $post_types_vm = array();
        $public_pts = get_post_types(array('public' => true), 'objects');
        foreach ($public_pts as $pt_name => $pt_obj) {
            $c = wp_count_posts($pt_name);
            $pub_count = !empty($c->publish) ? intval($c->publish) : 0;
            $is_inc = get_option('gmb_sitemap_include_pt_' . $pt_name, ($pt_name === 'attachment' ? '0' : '1')) !== '0';
            $is_img = get_option('gmb_sitemap_images_pt_' . $pt_name, '1') !== '0';

            $post_types_vm[] = array(
                'name'             => $pt_name,
                'label'            => !empty($pt_obj->labels->name) ? $pt_obj->labels->name : $pt_name,
                'published_count'  => $pub_count,
                'include'          => $is_inc,
                'include_images'   => $is_img,
                'url'              => self::get_post_type_sitemap_url($pt_name),
            );
        }

        // Taxonomies view model data
        $taxonomies_vm = array();
        $public_taxes = get_taxonomies(array('public' => true), 'objects');
        foreach ($public_taxes as $tax_name => $tax_obj) {
            $tc = wp_count_terms(array('taxonomy' => $tax_name, 'hide_empty' => false));
            $t_count = is_wp_error($tc) ? 0 : intval($tc);
            $is_inc = get_option('gmb_sitemap_include_tax_' . $tax_name, ($tax_name === 'post_format' ? '0' : '1')) !== '0';
            $is_empty = get_option('gmb_sitemap_empty_tax_' . $tax_name, '0') === '1';

            $taxonomies_vm[] = array(
                'name'             => $tax_name,
                'label'            => !empty($tax_obj->labels->name) ? $tax_obj->labels->name : $tax_name,
                'term_count'       => $t_count,
                'include'          => $is_inc,
                'include_empty'    => $is_empty,
                'url'              => self::get_taxonomy_sitemap_url($tax_name),
            );
        }

        return array(
            'module_enabled'     => $settings['module_enabled'],
            'active_subtab'      => $active_subtab,
            'settings'           => $settings,
            'sitemap_index_url'  => self::get_sitemap_index_url(),
            'post_types'         => $post_types_vm,
            'taxonomies'         => $taxonomies_vm,
            'author_count'       => self::get_eligible_author_count(),
            'custom_url_count'   => self::get_custom_url_count(),
            'diagnostics'        => self::get_diagnostics(),
        );
    }
}
