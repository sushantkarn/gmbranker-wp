<?php
/**
 * Redirect Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Redirect_Repository {

    const OPTION_RULES = 'gmb_ranker_redirects_rules';
    const OPTION_LOGS  = 'gmb_ranker_404_logs';
    const OPTION_LIMIT = 'gmb_ranker_404_limit';

    /**
     * Get all active redirect rules
     *
     * @return array
     */
    public function get_all_rules() {
        $rules = get_option(self::OPTION_RULES, array());
        return is_array($rules) ? $rules : array();
    }

    /**
     * Save all redirect rules
     *
     * @param array $rules
     * @return bool
     */
    public function save_rules(array $rules) {
        return update_option(self::OPTION_RULES, $rules);
    }

    /**
     * Add or update a single redirect rule
     *
     * @param array $rule
     * @return bool
     */
    public function save_rule(array $rule) {
        $rules = $this->get_all_rules();
        if (empty($rule['id'])) {
            $rule['id'] = 'redir_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
        }
        $updated = false;
        foreach ($rules as $index => $existing) {
            if (isset($existing['id']) && $existing['id'] === $rule['id']) {
                $rules[$index] = array_merge($existing, $rule);
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $rules[] = $rule;
        }
        return $this->save_rules($rules);
    }

    /**
     * Delete a redirect rule by ID
     *
     * @param string $rule_id
     * @return bool
     */
    public function delete_rule($rule_id) {
        $rules = $this->get_all_rules();
        $filtered = array();
        foreach ($rules as $rule) {
            if (!isset($rule['id']) || $rule['id'] !== $rule_id) {
                $filtered[] = $rule;
            }
        }
        return $this->save_rules($filtered);
    }

    /**
     * Get 404 access logs
     *
     * @return array
     */
    public function get_404_logs() {
        $logs = get_option(self::OPTION_LOGS, array());
        return is_array($logs) ? $logs : array();
    }

    /**
     * Save 404 access logs
     *
     * @param array $logs
     * @return bool
     */
    public function save_404_logs(array $logs) {
        $limit = intval(get_option(self::OPTION_LIMIT, 100));
        if ($limit > 0 && count($logs) > $limit) {
            $logs = array_slice($logs, -$limit);
        }
        return update_option(self::OPTION_LOGS, $logs);
    }

    /**
     * Clear all 404 logs
     *
     * @return bool
     */
    public function clear_404_logs() {
        return update_option(self::OPTION_LOGS, array());
    }
}
