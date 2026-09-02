<?php
/**
 * GMB Ranker SEO — WooCommerce & E-Commerce SEO Module
 *
 * Provides automated Product JSON-LD Schema, Gallery Image Sitemaps,
 * and Catalog Indexing controls for WooCommerce.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_WooCommerce {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_WooCommerce|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_WooCommerce
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Hook into Schema Generation
        add_filter('gmb_ranker_seo_schema_product', array($this, 'generate_product_schema'), 10, 2);

        // Hook into Sitemaps for Product Gallery Images
        add_filter('gmb_ranker_sitemap_post_images', array($this, 'add_product_gallery_images_to_sitemap'), 10, 2);

        // Hook into Robots Noindex for Hidden Products
        add_filter('gmb_ranker_robots_directives', array($this, 'filter_robots_for_hidden_products'), 10, 2);
    }

    /**
     * Generate comprehensive Product Schema
     *
     * @param array $schema
     * @param int   $post_id
     * @return array
     */
    public function generate_product_schema($schema = array(), $post_id = 0) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        if (!$post_id || 'product' !== get_post_type($post_id)) {
            return $schema;
        }

        $product = function_exists('wc_get_product') ? wc_get_product($post_id) : null;
        if (!$product) {
            return $schema;
        }

        $permalink = get_permalink($post_id);
        $title     = get_the_title($post_id);
        $desc      = wp_strip_all_tags($product->get_short_description() ? $product->get_short_description() : $product->get_description());
        $image_id  = $product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

        $price     = $product->get_price();
        $currency  = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'USD';
        $in_stock  = $product->is_in_stock();
        $sku       = $product->get_sku();

        $product_schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            '@id'         => $permalink . '#product',
            'name'        => $title,
            'url'         => $permalink,
            'description' => $desc,
        );

        if (!empty($image_url)) {
            $product_schema['image'] = $image_url;
        }

        if (!empty($sku)) {
            $product_schema['sku'] = $sku;
        }

        // Offers Schema
        $product_schema['offers'] = array(
            '@type'         => 'Offer',
            'url'           => $permalink,
            'priceCurrency' => $currency,
            'price'         => $price ? number_format((float)$price, 2, '.', '') : '0.00',
            'availability'  => $in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        );

        // Ratings & Reviews
        if ($product->get_rating_count() > 0) {
            $product_schema['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => $product->get_average_rating(),
                'reviewCount' => $product->get_review_count(),
            );
        }

        // Brand / Organization
        $brand_name = get_bloginfo('name');
        $product_schema['brand'] = array(
            '@type' => 'Brand',
            'name'  => $brand_name,
        );

        return $product_schema;
    }

    /**
     * Add product gallery images to XML sitemap
     *
     * @param array $images
     * @param int   $post_id
     * @return array
     */
    public function add_product_gallery_images_to_sitemap($images = array(), $post_id = 0) {
        if (!$post_id || 'product' !== get_post_type($post_id)) {
            return $images;
        }

        $product = function_exists('wc_get_product') ? wc_get_product($post_id) : null;
        if (!$product) {
            return $images;
        }

        $gallery_ids = $product->get_gallery_image_ids();
        if (!empty($gallery_ids) && is_array($gallery_ids)) {
            foreach ($gallery_ids as $attachment_id) {
                $url = wp_get_attachment_image_url($attachment_id, 'full');
                $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                if ($url) {
                    $images[] = array(
                        'loc'   => $url,
                        'title' => !empty($alt) ? $alt : get_the_title($post_id),
                    );
                }
            }
        }

        return $images;
    }

    /**
     * Auto-noindex hidden WooCommerce catalog products
     *
     * @param array $directives
     * @param int   $post_id
     * @return array
     */
    public function filter_robots_for_hidden_products($directives = array(), $post_id = 0) {
        if (!$post_id || 'product' !== get_post_type($post_id)) {
            return $directives;
        }

        $product = function_exists('wc_get_product') ? wc_get_product($post_id) : null;
        if ($product && 'hidden' === $product->get_catalog_visibility()) {
            if (!in_array('noindex', $directives, true)) {
                $directives[] = 'noindex';
            }
        }

        return $directives;
    }
}
