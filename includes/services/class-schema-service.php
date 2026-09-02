<?php
/**
 * Schema Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Schema_Service {

    /**
     * @var GMB_Ranker_SEO_Schema_Repository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param GMB_Ranker_SEO_Schema_Repository|null $repository
     */
    public function __construct(GMB_Ranker_SEO_Schema_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Schema_Repository();
    }

    /**
     * Replace dynamic schema variables
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
        );

        if ($post) {
            $replacements['%post_title%']       = $post->post_title;
            $replacements['%title%']            = $post->post_title;
            $replacements['%url%']              = function_exists('get_permalink') ? get_permalink($post) : home_url();
            $replacements['%post_url%']         = function_exists('get_permalink') ? get_permalink($post) : home_url();
            $replacements['%post_date%']        = function_exists('get_the_date') ? get_the_date('c', $post) : gmdate('c');
            $replacements['%post_modified%']    = function_exists('get_the_modified_date') ? get_the_modified_date('c', $post) : gmdate('c');
            $replacements['%author%']           = function_exists('get_the_author_meta') ? get_the_author_meta('display_name', $post->post_author) : 'Admin';
            $replacements['%post_author%']      = function_exists('get_the_author_meta') ? get_the_author_meta('display_name', $post->post_author) : 'Admin';
            $replacements['%post_excerpt%']     = wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 25));
            $replacements['%seo_title%']        = get_post_meta($post->ID, '_gmb_ranker_seo_title', true) ?: $post->post_title;
            $replacements['%seo_description%']  = get_post_meta($post->ID, '_gmb_ranker_seo_description', true) ?: wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 25));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    /**
     * Check if display conditions match for a given post/view
     *
     * @param array $conditions
     * @param int   $post_id
     * @return bool
     */
    public function matches_display_conditions(array $conditions, $post_id = 0) {
        if (empty($conditions)) {
            return true;
        }

        $rule = isset($conditions['rule']) ? $conditions['rule'] : (isset($conditions['scope']) ? $conditions['scope'] : 'entire_site');

        if ($rule === 'entire_site') {
            return true;
        }

        if ($rule === 'singular') {
            if (!is_singular()) return false;
            $post_type = get_post_type($post_id);
            if (!empty($conditions['post_type']) && $conditions['post_type'] !== 'all') {
                return ($post_type === $conditions['post_type']);
            }
            return true;
        }

        if ($rule === 'homepage') {
            return is_front_page() || is_home();
        }

        if ($rule === 'archive') {
            return is_archive();
        }

        return false;
    }

    /**
     * Get active schema templates for current post
     *
     * @param int $post_id
     * @return array
     */
    public function get_templates_for_post($post_id = 0) {
        $all = $this->repository->get_all_templates();
        $matched = array();

        foreach ($all as $tmpl) {
            if (empty($tmpl['enabled'])) {
                continue;
            }
            $conditions = isset($tmpl['conditions']) ? $tmpl['conditions'] : array();
            if ($this->matches_display_conditions($conditions, $post_id)) {
                $matched[] = $tmpl;
            }
        }

        return $matched;
    }
}
