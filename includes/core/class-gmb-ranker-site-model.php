<?php
/**
 * Generic Website Discovery & Normalized Data Model
 *
 * Dynamically discovers website identity, architecture, business classification,
 * active modules, and SEO state without any hardcoded assumptions.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) exit;

class GMB_Ranker_Site_Model {

    /**
     * Instance holder
     * @var GMB_Ranker_Site_Model
     */
    private static $instance = null;

    /**
     * Discovered site data cache
     * @var array
     */
    private $model_data = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Build and return normalized website model
     *
     * @param bool $force_refresh
     * @return array
     */
    public function get_site_model($force_refresh = false) {
        if (null !== $this->model_data && !$force_refresh) {
            return $this->model_data;
        }

        $this->model_data = array(
            'identity'       => $this->discover_identity(),
            'classification' => $this->discover_classification(),
            'content_state'  => $this->discover_content_state(),
            'module_state'   => $this->discover_module_state(),
            'technical_state'=> $this->discover_technical_state(),
            'local_state'    => $this->discover_local_state(),
            'discovered_at'  => current_time('mysql'),
        );

        return $this->model_data;
    }

    /**
     * Discover Site Identity
     */
    private function discover_identity() {
        $home = home_url('/');
        $domain = wp_parse_url($home, PHP_URL_HOST) ?: 'UNKNOWN';

        return array(
            'site_name'   => get_bloginfo('name') ?: 'UNKNOWN',
            'tagline'     => get_bloginfo('description') ?: 'UNKNOWN',
            'home_url'    => $home,
            'domain'      => $domain,
            'language'    => get_bloginfo('language') ?: 'UNKNOWN',
            'admin_email' => get_bloginfo('admin_email') ?: 'UNKNOWN',
            'charset'     => get_bloginfo('charset') ?: 'UTF-8',
        );
    }

    /**
     * Infer Dynamic Business Classification
     */
    private function discover_classification() {
        $primary = 'Blog';
        $secondary = array();

        $has_woo = class_exists('WooCommerce');
        $has_local_setting = get_option('gmb_local_business_address') || get_option('gmb_local_business_city');
        $has_services_cpt = post_type_exists('service') || post_type_exists('services');
        $has_products_cpt = post_type_exists('product');

        if ($has_woo || $has_products_cpt) {
            $primary = 'Ecommerce';
            if ($has_local_setting) {
                $secondary[] = 'Local Business';
            }
        } elseif ($has_local_setting || $has_services_cpt) {
            $primary = 'Local Business';
            $secondary[] = 'Service Business';
        } else {
            $post_count = wp_count_posts('post')->publish ?? 0;
            $page_count = wp_count_posts('page')->publish ?? 0;
            if ($page_count > $post_count) {
                $primary = 'Professional Services';
                $secondary[] = 'Corporate Website';
            } else {
                $primary = 'Publisher / Blog';
            }
        }

        return array(
            'primary_type'   => $primary,
            'secondary_type' => implode(', ', array_unique($secondary)) ?: 'None',
            'is_ecommerce'   => $has_woo,
            'has_local_intent' => !empty($has_local_setting) || $has_services_cpt,
        );
    }

    /**
     * Discover Content State
     */
    private function discover_content_state() {
        $post_count = intval(wp_count_posts('post')->publish ?? 0);
        $page_count = intval(wp_count_posts('page')->publish ?? 0);
        $prod_count = class_exists('WooCommerce') ? intval(wp_count_posts('product')->publish ?? 0) : 0;

        $public_post_types = get_post_types(array('public' => true), 'names');

        return array(
            'published_posts'    => $post_count,
            'published_pages'    => $page_count,
            'published_products' => $prod_count,
            'public_post_types'  => array_values($public_post_types),
            'total_content_nodes'=> $post_count + $page_count + $prod_count,
        );
    }

    /**
     * Discover Active GMB Ranker Modules
     */
    private function discover_module_state() {
        return array(
            'metadata'        => get_option('gmb_ranker_module_metadata', '1') === '1',
            'sitemaps'        => get_option('gmb_ranker_module_sitemaps', '1') === '1',
            'redirects'       => get_option('gmb_ranker_module_redirects', '1') === '1',
            'schema'          => get_option('gmb_ranker_module_schema', '1') === '1',
            'image_seo'       => get_option('gmb_ranker_module_image_seo', '1') === '1',
            'links'           => get_option('gmb_ranker_module_links', '1') === '1',
            'instant_indexing'=> get_option('gmb_ranker_module_instant_indexing', '1') === '1',
            'local_seo'       => get_option('gmb_ranker_module_local_seo', '1') === '1',
            'toc'             => get_option('gmb_ranker_module_toc', '1') === '1',
            'llmstxt'         => get_option('gmb_ranker_module_llmstxt', '1') === '1',
            'woocommerce'     => get_option('gmb_ranker_module_woocommerce', '1') === '1',
        );
    }

    /**
     * Discover Technical SEO State
     */
    private function discover_technical_state() {
        return array(
            'is_ssl'           => is_ssl(),
            'permalink_structure' => get_option('permalink_structure') ?: 'default',
            'blog_public'      => get_option('blog_public', '1') === '1',
            'sitemap_url'      => home_url('/sitemap.xml'),
            'robots_url'       => home_url('/robots.txt'),
        );
    }

    /**
     * Discover Local Business State
     */
    private function discover_local_state() {
        $city    = get_option('gmb_local_business_city', '');
        $country = get_option('gmb_local_business_country', '');
        $address = get_option('gmb_local_business_address', '');
        $phone   = get_option('gmb_local_business_phone', '');

        return array(
            'is_configured' => !empty($city) || !empty($address),
            'city'          => !empty($city) ? $city : 'UNKNOWN',
            'country'       => !empty($country) ? $country : 'UNKNOWN',
            'address'       => !empty($address) ? $address : 'UNKNOWN',
            'phone'         => !empty($phone) ? $phone : 'UNKNOWN',
        );
    }
}
