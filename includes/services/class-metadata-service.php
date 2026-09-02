<?php
/**
 * Metadata Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Metadata_Service {

    /**
     * Parse and substitute snippet variables
     *
     * @param string $string
     * @param int    $post_id
     * @return string
     */
    public function replace_variables($string, $post_id = 0) {
        if (!is_string($string) || strpos($string, '%') === false) {
            return $string;
        }

        $post = $post_id ? get_post($post_id) : (is_singular() ? get_post() : null);

        $replacements = array(
            '%site_title%'       => get_bloginfo('name'),
            '%site_description%' => get_bloginfo('description'),
            '%site_url%'         => home_url('/'),
            '%current_year%'     => gmdate('Y'),
            '%current_month%'    => gmdate('F'),
            '%sep%'              => get_option('gmb_title_separator', '-'),
        );

        if ($post) {
            $replacements['%post_title%']    = $post->post_title;
            $replacements['%title%']         = $post->post_title;
            $replacements['%url%']           = function_exists('get_permalink') ? get_permalink($post) : home_url();
            $replacements['%post_url%']      = function_exists('get_permalink') ? get_permalink($post) : home_url();
            $replacements['%author%']        = function_exists('get_the_author_meta') ? get_the_author_meta('display_name', $post->post_author) : 'Admin';
            $replacements['%post_excerpt%']  = wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 25));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    /**
     * Compute effective SEO title for a post
     *
     * @param int $post_id
     * @return string
     */
    public function get_effective_title($post_id) {
        $custom = get_post_meta($post_id, '_gmb_ranker_seo_title', true);
        if (empty($custom)) {
            $custom = get_post_meta($post_id, 'rank_math_title', true);
        }
        if (empty($custom)) {
            $custom = get_post_meta($post_id, '_yoast_wpseo_title', true);
        }
        if (!empty($custom)) {
            return $this->replace_variables($custom, $post_id);
        }

        $post = get_post($post_id);
        return $post ? $post->post_title . ' - ' . get_bloginfo('name') : get_bloginfo('name');
    }

    /**
     * Compute effective SEO description for a post
     *
     * @param int $post_id
     * @return string
     */
    public function get_effective_description($post_id) {
        $custom = get_post_meta($post_id, '_gmb_ranker_seo_description', true);
        if (empty($custom)) {
            $custom = get_post_meta($post_id, 'rank_math_description', true);
        }
        if (empty($custom)) {
            $custom = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        }
        if (!empty($custom)) {
            return $this->replace_variables($custom, $post_id);
        }

        $post = get_post($post_id);
        if ($post && !empty($post->post_excerpt)) {
            return wp_strip_all_tags($post->post_excerpt);
        }
        if ($post && !empty($post->post_content)) {
            return wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...');
        }
        return '';
    }
}
