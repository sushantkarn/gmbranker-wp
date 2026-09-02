<?php
/**
 * Automation Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Automation_Repository {

    const OPTION_WORKFLOWS = 'gmb_ranker_automation_workflows';
    const OPTION_HISTORY   = 'gmb_ranker_automation_history';

    /**
     * Get all configured automation workflows
     *
     * @return array
     */
    public function get_workflows() {
        $workflows = get_option(self::OPTION_WORKFLOWS, array());
        return is_array($workflows) ? $workflows : array();
    }

    /**
     * Get a specific workflow by ID
     *
     * @param string $workflow_id
     * @return array|null
     */
    public function get_workflow($workflow_id) {
        $workflows = $this->get_workflows();
        foreach ($workflows as $wf) {
            if (isset($wf['id']) && $wf['id'] === $workflow_id) {
                return $wf;
            }
        }
        return null;
    }

    /**
     * Save or update a workflow
     *
     * @param array $workflow
     * @return bool
     */
    public function save_workflow(array $workflow) {
        $workflows = $this->get_workflows();
        if (empty($workflow['id'])) {
            $workflow['id'] = 'wf_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
        }
        $updated = false;
        foreach ($workflows as $i => $existing) {
            if (isset($existing['id']) && $existing['id'] === $workflow['id']) {
                $workflows[$i] = array_merge($existing, $workflow);
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $workflows[] = $workflow;
        }
        return update_option(self::OPTION_WORKFLOWS, $workflows);
    }

    /**
     * Delete a workflow by ID
     *
     * @param string $workflow_id
     * @return bool
     */
    public function delete_workflow($workflow_id) {
        $workflows = $this->get_workflows();
        $filtered = array();
        foreach ($workflows as $wf) {
            if (!isset($wf['id']) || $wf['id'] !== $workflow_id) {
                $filtered[] = $wf;
            }
        }
        return update_option(self::OPTION_WORKFLOWS, $filtered);
    }

    /**
     * Record execution history entry
     *
     * @param array $entry
     * @return bool
     */
    public function record_history(array $entry) {
        $history = get_option(self::OPTION_HISTORY, array());
        if (!is_array($history)) {
            $history = array();
        }
        $entry['executed_at'] = current_time('mysql');
        $history[] = $entry;
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }
        return update_option(self::OPTION_HISTORY, $history);
    }

    /**
     * Get execution history logs
     *
     * @param int $limit
     * @return array
     */
    public function get_history($limit = 50) {
        $history = get_option(self::OPTION_HISTORY, array());
        if (!is_array($history)) {
            return array();
        }
        return array_slice($history, -$limit);
    }
}
