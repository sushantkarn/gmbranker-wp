<?php
/**
 * Report Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Report_Repository {

    const OPTION_AUDIT_LOGS = 'gmb_ranker_audit_logs';

    /**
     * Get recent audit report logs
     *
     * @param int $limit
     * @return array
     */
    public function get_audit_logs($limit = 50) {
        $logs = get_option(self::OPTION_AUDIT_LOGS, array());
        if (!is_array($logs)) {
            return array();
        }
        return array_slice($logs, -$limit);
    }

    /**
     * Append a new audit report log
     *
     * @param array $report_entry
     * @return bool
     */
    public function record_audit_log(array $report_entry) {
        $logs = get_option(self::OPTION_AUDIT_LOGS, array());
        if (!is_array($logs)) {
            $logs = array();
        }
        $report_entry['timestamp'] = current_time('timestamp');
        $logs[] = $report_entry;
        if (count($logs) > 200) {
            $logs = array_slice($logs, -200);
        }
        return update_option(self::OPTION_AUDIT_LOGS, $logs);
    }
}
