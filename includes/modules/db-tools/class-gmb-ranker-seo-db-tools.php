<?php
/**
 * Database Optimization and Cleanup Tools
 *
 * @package GMB_Ranker_SEO_Automation
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_DB_Tools {

    /**
     * Optimize primary WordPress core tables safely.
     *
     * @return array Status report with optimized table list.
     */
    public static function optimize_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->options,
            $wpdb->users,
            $wpdb->usermeta,
            $wpdb->terms,
            $wpdb->termmeta,
            $wpdb->term_taxonomy,
            $wpdb->term_relationships,
            $wpdb->comments,
            $wpdb->commentmeta,
        );

        $optimized = array();
        foreach ($tables as $table) {
            if (!empty($table)) {
                // Execute table optimization
                $wpdb->query("OPTIMIZE TABLE {$table}");
                $optimized[] = $table;
            }
        }

        return array(
            'success' => true,
            'tables'  => $optimized,
            'count'   => count($optimized)
        );
    }

    /**
     * Delete orphan post, term, and user metadata records.
     *
     * @return int Total deleted records.
     */
    public static function clear_orphan_meta() {
        global $wpdb;

        // 1. Orphan postmeta
        $postmeta_count = $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm 
             LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
             WHERE p.ID IS NULL"
        );

        // 2. Orphan termmeta
        $termmeta_count = 0;
        if (!empty($wpdb->termmeta) && !empty($wpdb->terms)) {
            $termmeta_count = $wpdb->query(
                "DELETE tm FROM {$wpdb->termmeta} tm 
                 LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id 
                 WHERE t.term_id IS NULL"
            );
        }

        // 3. Orphan usermeta
        $usermeta_count = $wpdb->query(
            "DELETE um FROM {$wpdb->usermeta} um 
             LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
             WHERE u.ID IS NULL"
        );

        return (int)$postmeta_count + (int)$termmeta_count + (int)$usermeta_count;
    }

    /**
     * Clear all expired and standard transients safely from options and object cache.
     *
     * @return int Total cleared transients.
     */
    public static function clear_transients() {
        global $wpdb;

        // Fetch transient keys to clear from object cache
        $transient_keys = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_%' 
             AND option_name NOT LIKE '_transient_timeout_%'"
        );

        $count = 0;
        if (!empty($transient_keys)) {
            foreach ($transient_keys as $key) {
                $transient_name = str_replace('_transient_', '', $key);
                if (delete_transient($transient_name)) {
                    $count++;
                }
            }
        }

        // Fallback cleanup for orphaned transient timeouts
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_%' 
             OR option_name LIKE '_site_transient_timeout_%'"
        );

        return $count;
    }
}
