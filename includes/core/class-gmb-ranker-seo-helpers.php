<?php
/**
 * Shared Helper Utilities for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Helpers {

    /**
     * Safely render a template view with extracted variables
     *
     * @param string $view_file Relative path under includes/admin/views/ (e.g. 'dashboard.php')
     * @param array $args Variables to pass to the view
     * @param bool $echo Whether to echo or return output
     * @return string|void
     */
    public static function render_view($view_file, $args = array(), $echo = true) {
        $view_path = dirname(dirname(__FILE__)) . '/admin/views/' . ltrim($view_file, '/');
        
        if (!file_exists($view_path)) {
            if ($echo) {
                echo '<!-- View file not found: ' . esc_html($view_file) . ' -->';
                return;
            }
            return '';
        }

        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        if (!$echo) {
            ob_start();
        }

        include $view_path;

        if (!$echo) {
            return ob_get_clean();
        }
    }

    /**
     * Get the absolute URL to an asset in assets/ directory
     *
     * @param string $path Relative path inside assets/
     * @return string
     */
    public static function asset_url($path) {
        $path = ltrim($path, '/');
        if (strpos($path, 'assets/') === 0) {
            $path = substr($path, 7);
        }
        if (defined('GMB_RANKER_SEO_FILE')) {
            return plugins_url('assets/' . $path, GMB_RANKER_SEO_FILE);
        }
        if (defined('GMB_RANKER_SEO_URL')) {
            return GMB_RANKER_SEO_URL . 'assets/' . $path;
        }
        return plugins_url('assets/' . $path, dirname(dirname(dirname(__FILE__))) . '/gmb-ranker-seo-automation.php');
    }

    /**
     * Check if a module is enabled
     *
     * @param string $module_key
     * @param string $default
     * @return bool
     */
    public static function is_module_enabled($module_key, $default = '1') {
        $val = get_option('gmb_ranker_module_' . $module_key, $default);
        return ($val !== '0' && $val !== 'off');
    }

    /**
     * Get list of public post types excluding attachments
     *
     * @return array
     */
    public static function get_public_post_types() {
        $pts = get_post_types(array('public' => true), 'objects');
        unset($pts['attachment']);
        return $pts;
    }

    /**
     * Get list of public taxonomies
     *
     * @return array
     */
    public static function get_public_taxonomies() {
        return get_taxonomies(array('public' => true), 'objects');
    }

    /**
     * Replace template replacement tags
     *
     * @param string $string
     * @param int|null $post_id
     * @return string
     */
    public static function replace_template_tags($string, $post_id = null) {
        $replacements = array(
            '%sitename%'     => get_bloginfo('name'),
            '%sitedesc%'     => get_bloginfo('description'),
            '%siteurl%'      => home_url(),
            '%currentyear%'  => gmdate('Y'),
            '%currentmonth%' => gmdate('F'),
            '%currentdate%'  => gmdate('F j, Y'),
            '%sep%'          => get_option('gmb_title_separator', '-'),
        );

        if ($post_id) {
            $post = get_post($post_id);
            if ($post) {
                $replacements['%title%']        = get_the_title($post);
                $replacements['%post_title%']   = get_the_title($post);
                $replacements['%excerpt%']      = wp_strip_all_tags($post->post_excerpt);
                $replacements['%post_date%']    = get_the_date('', $post);
                $replacements['%post_year%']    = get_the_date('Y', $post);
                $replacements['%author%']       = get_the_author_meta('display_name', $post->post_author);
                $replacements['%category%']     = self::get_primary_term_name($post_id, 'category');
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    /**
     * Get primary taxonomy term name
     *
     * @param int $post_id
     * @param string $taxonomy
     * @return string
     */
    public static function get_primary_term_name($post_id, $taxonomy = 'category') {
        $terms = get_the_terms($post_id, $taxonomy);
        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return '';
    }
}
