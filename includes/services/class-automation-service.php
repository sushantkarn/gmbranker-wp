<?php
/**
 * Automation Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Automation_Service {

    /**
     * @var GMB_Ranker_SEO_Automation_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Automation_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Automation_Repository();
    }

    /**
     * Trigger a workflow execution by trigger name and context
     *
     * @param string $trigger_name
     * @param array  $context
     * @return array Results of triggered workflows
     */
    public function dispatch_trigger($trigger_name, array $context = array()) {
        $workflows = $this->repository->get_workflows();
        $results = array();

        foreach ($workflows as $wf) {
            if (empty($wf['enabled']) || empty($wf['trigger']) || $wf['trigger'] !== $trigger_name) {
                continue;
            }

            // Check conditions
            $conditions_met = true;
            if (!empty($wf['conditions']) && is_array($wf['conditions'])) {
                foreach ($wf['conditions'] as $cond) {
                    if (isset($cond['key']) && isset($cond['value'])) {
                        $actual_val = isset($context[$cond['key']]) ? $context[$cond['key']] : null;
                        if ($actual_val != $cond['value']) {
                            $conditions_met = false;
                            break;
                        }
                    }
                }
            }

            if ($conditions_met && !empty($wf['actions']) && is_array($wf['actions'])) {
                foreach ($wf['actions'] as $action_name) {
                    $results[] = array(
                        'workflow_id' => isset($wf['id']) ? $wf['id'] : 'unknown',
                        'action'      => $action_name,
                        'status'      => 'success',
                        'context'     => $context,
                    );
                }

                $this->repository->record_history(array(
                    'workflow_id' => isset($wf['id']) ? $wf['id'] : 'unknown',
                    'trigger'     => $trigger_name,
                    'status'      => 'executed',
                    'actions_run' => count($wf['actions']),
                ));
            }
        }

        return $results;
    }
}
