<?php
/**
 * Ranking Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ranking_Repository {

    const OPTION_RANKING_CACHE = 'gmb_ranker_analytics_cache';
    const OPTION_TRACKED_QUERIES = 'gmb_ranker_tracked_queries';

    /**
     * Get cached search console & ranking dataset
     *
     * @return array
     */
    public function get_cached_analytics() {
        $cached = get_transient(self::OPTION_RANKING_CACHE);
        if (false !== $cached && is_array($cached)) {
            return $cached;
        }
        return array();
    }

    /**
     * Store cached analytics dataset
     *
     * @param array $data
     * @param int   $expiration (seconds)
     * @return bool
     */
    public function set_cached_analytics(array $data, $expiration = 86400) {
        return set_transient(self::OPTION_RANKING_CACHE, $data, $expiration);
    }

    /**
     * Clear analytics cache
     *
     * @return bool
     */
    public function clear_cached_analytics() {
        return delete_transient(self::OPTION_RANKING_CACHE);
    }

    /**
     * Get tracked target queries
     *
     * @return array
     */
    public function get_tracked_queries() {
        $queries = get_option(self::OPTION_TRACKED_QUERIES, array());
        return is_array($queries) ? $queries : array();
    }

    /**
     * Save tracked target queries
     *
     * @param array $queries
     * @return bool
     */
    public function save_tracked_queries(array $queries) {
        return update_option(self::OPTION_TRACKED_QUERIES, $queries);
    }
}
