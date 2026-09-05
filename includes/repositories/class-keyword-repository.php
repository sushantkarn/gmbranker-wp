<?php
/**
 * Keyword Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Keyword_Repository {

    /**
     * Get primary focus keyword for a post
     *
     * @param int $post_id
     * @return string
     */
    public function get_focus_keyword($post_id) {
        $kw = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true);
        if (empty($kw)) {
            $kw = get_post_meta($post_id, 'rank_math_focus_keyword', true);
        }
        if (empty($kw)) {
            $kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        }
        if (empty($kw)) {
            $kw = get_post_meta($post_id, '_rank_math_focus_keyword', true);
        }
        return is_string($kw) ? trim($kw) : '';
    }

    /**
     * Set focus keyword for a post with backward-compatibility sync
     *
     * @param int    $post_id
     * @param string $keyword
     * @return bool
     */
    public function set_focus_keyword($post_id, $keyword) {
        $keyword = sanitize_text_field(trim(preg_replace('/\s+/', ' ', $keyword)));
        update_post_meta($post_id, '_gmb_ranker_focus_keyword', $keyword);
        update_post_meta($post_id, 'rank_math_focus_keyword', $keyword);
        update_post_meta($post_id, '_rank_math_focus_keyword', $keyword);
        update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
        return true;
    }

    /**
     * Get all secondary keywords for a post
     *
     * @param int $post_id
     * @return array
     */
    public function get_secondary_keywords($post_id) {
        $kws = get_post_meta($post_id, '_gmb_ranker_secondary_keywords', true);
        return is_array($kws) ? $kws : array();
    }

    /**
     * Save secondary keywords for a post
     *
     * @param int   $post_id
     * @param array $keywords
     * @return bool
     */
    public function set_secondary_keywords($post_id, array $keywords) {
        $clean = array_filter(array_map('sanitize_text_field', array_map('trim', $keywords)));
        return (bool) update_post_meta($post_id, '_gmb_ranker_secondary_keywords', $clean);
    }
}
