<?php
/**
 * Automation Service for GMB Ranker SEO Automation
 *
 * Real automation orchestration layer providing trigger validation,
 * trusted action resolution, fail-closed condition evaluation, sequential action
 * execution, normalized result contract, exception safety, and truthful history logging.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Automation_Service {

    /**
     * Automation repository instance
     *
     * @var GMB_Ranker_SEO_Automation_Repository
     */
    protected $repository;

    /**
     * Fail-closed condition evaluator
     *
     * @var GMB_Ranker_SEO_Condition_Evaluator|null
     */
    protected $evaluator;

    /**
     * Trusted action registry: array<string, GMB_Ranker_SEO_Action_Interface>
     *
     * @var array
     */
    protected $actions = array();

    /**
     * Sensitive context keys that must be scrubbed from history and logging
     *
     * @var array
     */
    protected static $sensitive_keys = array(
        'api_key',
        'access_token',
        'refresh_token',
        'password',
        'secret',
        'authorization',
        'private_key',
        'bearer',
        'cookie',
        'token',
        'pwd',
        'auth',
    );

    /**
     * Constructor - backward compatible signature
     *
     * @param GMB_Ranker_SEO_Automation_Repository|null $repository
     */
    public function __construct(GMB_Ranker_SEO_Automation_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Automation_Repository();

        if (class_exists('GMB_Ranker_SEO_Condition_Evaluator')) {
            $this->evaluator = new GMB_Ranker_SEO_Condition_Evaluator();
        }

        $this->register_default_actions();
    }

    /**
     * Register core trusted actions from existing plugin architecture
     */
    protected function register_default_actions() {
        // Pull registered actions from singleton Automation Manager if present
        if (class_exists('GMB_Ranker_SEO_Automation_Manager')) {
            $manager = GMB_Ranker_SEO_Automation_Manager::get_instance();
            $manager_actions = $manager->get_actions();
            if (is_array($manager_actions) && !empty($manager_actions)) {
                foreach ($manager_actions as $action) {
                    if ($action instanceof GMB_Ranker_SEO_Action_Interface) {
                        $this->register_action($action);
                    }
                }
            }
        }

        // Direct instantiation of built-in action classes as fallback
        $core_action_classes = array(
            'GMB_Ranker_SEO_Generate_Content_Action',
            'GMB_Ranker_SEO_Optimize_Page_Action',
            'GMB_Ranker_SEO_Update_Meta_Action',
            'GMB_Ranker_SEO_Generate_Report_Action',
            'GMB_Ranker_SEO_Sync_Gbp_Action',
        );

        foreach ($core_action_classes as $class_name) {
            if (class_exists($class_name)) {
                $action_instance = new $class_name();
                if ($action_instance instanceof GMB_Ranker_SEO_Action_Interface) {
                    $this->register_action($action_instance);
                }
            }
        }

        /**
         * Hook to register custom trusted automation action handlers
         *
         * @param GMB_Ranker_SEO_Automation_Service $this
         */
        do_action('gmb_ranker_seo_automation_register_actions', $this);
    }

    /**
     * Register a trusted action handler
     *
     * @param GMB_Ranker_SEO_Action_Interface $action
     */
    public function register_action(GMB_Ranker_SEO_Action_Interface $action) {
        $id = sanitize_key($action->get_id());
        if (!empty($id)) {
            $this->actions[$id] = $action;
        }
    }

    /**
     * Get all registered trusted actions
     *
     * @return array<string, GMB_Ranker_SEO_Action_Interface>
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Trigger a workflow execution by trigger name and context
     *
     * Flow: trigger -> discovery -> validation -> condition evaluation ->
     * action resolution -> action execution -> normalized result -> history persistence
     *
     * @param string $trigger_name
     * @param array  $context
     * @return array Results of triggered workflows
     */
    public function dispatch_trigger($trigger_name, array $context = array()) {
        $started_at_str    = current_time('mysql');
        $execution_results = array();

        // 1. Trigger Validation
        $clean_trigger = is_string($trigger_name) ? sanitize_key(trim($trigger_name)) : '';
        if (empty($clean_trigger)) {
            return array(
                array(
                    'execution_id' => $this->generate_execution_id(),
                    'workflow_id'  => 'none',
                    'trigger'      => (string)$trigger_name,
                    'action'       => 'none',
                    'status'       => 'invalid',
                    'success'      => false,
                    'message'      => __('Invalid or empty trigger name provided.', 'gmb-ranker-seo-automation'),
                    'error_code'   => 'invalid_trigger',
                    'context'      => $this->scrub_context($context),
                    'data'         => array(),
                )
            );
        }

        // 2. Safe Execution Context Scrubbing
        $safe_context = $this->scrub_context($context);

        // 3. Workflow Discovery
        $workflows = $this->repository->get_workflows();
        if (!is_array($workflows)) {
            return array(
                array(
                    'execution_id' => $this->generate_execution_id(),
                    'workflow_id'  => 'none',
                    'trigger'      => $clean_trigger,
                    'action'       => 'none',
                    'status'       => 'failed',
                    'success'      => false,
                    'message'      => __('Failed to query automation workflows repository.', 'gmb-ranker-seo-automation'),
                    'error_code'   => 'repository_error',
                    'context'      => $safe_context,
                    'data'         => array(),
                )
            );
        }

        if (empty($workflows)) {
            return array();
        }

        // 4. Process Eligible Workflows
        foreach ($workflows as $wf_raw) {
            $execution_id  = $this->generate_execution_id();
            $wf_start_time = microtime(true);

            // Workflow Validation
            $validation  = $this->validate_workflow($wf_raw, $clean_trigger);
            $workflow_id = $validation['workflow_id'];

            if (!$validation['is_valid']) {
                // If trigger mismatch or workflow disabled, cleanly ignore without clogging history
                if ($validation['reason'] === 'trigger_mismatch' || $validation['reason'] === 'disabled') {
                    continue;
                }

                // If malformed workflow, record invalid history entry & append diagnostic result
                $wf_result = array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'trigger'      => $clean_trigger,
                    'action'       => 'none',
                    'status'       => 'invalid',
                    'success'      => false,
                    'message'      => $validation['message'],
                    'error_code'   => $validation['error_code'],
                    'context'      => $safe_context,
                    'data'         => array(),
                );

                $this->repository->record_history(array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'trigger'      => $clean_trigger,
                    'status'       => 'invalid',
                    'actions_run'  => 0,
                    'actions_total'=> 0,
                    'actions'      => array(),
                    'started_at'   => $started_at_str,
                    'completed_at' => current_time('mysql'),
                    'duration'     => round(microtime(true) - $wf_start_time, 4),
                    'error_code'   => $validation['error_code'],
                    'summary'      => $validation['message'],
                ));

                $execution_results[] = $wf_result;
                continue;
            }

            // 5. Idempotency / Duplicate Execution Protection
            $lock_key = $this->acquire_workflow_lock($workflow_id, $clean_trigger, $safe_context);
            if (!$lock_key) {
                $wf_result = array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'trigger'      => $clean_trigger,
                    'action'       => 'none',
                    'status'       => 'skipped',
                    'success'      => false,
                    'message'      => __('Workflow execution skipped due to active concurrent execution lock.', 'gmb-ranker-seo-automation'),
                    'error_code'   => 'execution_locked',
                    'context'      => $safe_context,
                    'data'         => array(),
                );
                $execution_results[] = $wf_result;
                continue;
            }

            try {
                // 6. Fail-Closed Condition Evaluation
                $condition_eval = $this->evaluate_workflow_conditions($wf_raw, $safe_context);
                if (!$condition_eval['passes']) {
                    $this->release_workflow_lock($lock_key);

                    $wf_result = array(
                        'execution_id' => $execution_id,
                        'workflow_id'  => $workflow_id,
                        'trigger'      => $clean_trigger,
                        'action'       => 'none',
                        'status'       => 'skipped',
                        'success'      => true, // Non-matching condition produces a normal skipped outcome
                        'message'      => $condition_eval['message'],
                        'error_code'   => $condition_eval['error_code'],
                        'context'      => $safe_context,
                        'data'         => array('conditions' => $condition_eval['details']),
                    );

                    $this->repository->record_history(array(
                        'execution_id' => $execution_id,
                        'workflow_id'  => $workflow_id,
                        'trigger'      => $clean_trigger,
                        'status'       => 'skipped',
                        'actions_run'  => 0,
                        'actions_total'=> count($validation['actions']),
                        'actions'      => array(),
                        'started_at'   => $started_at_str,
                        'completed_at' => current_time('mysql'),
                        'duration'     => round(microtime(true) - $wf_start_time, 4),
                        'error_code'   => $condition_eval['error_code'],
                        'summary'      => $condition_eval['message'],
                    ));

                    $execution_results[] = $wf_result;
                    continue;
                }

                // 7. Sequential Trusted Action Execution
                $action_defs     = $validation['actions'];
                $action_results  = array();
                $completed_count = 0;
                $failed_count    = 0;
                $pipeline_output = array();
                $current_context = $safe_context;

                foreach ($action_defs as $action_def) {
                    $normalized_action = $this->normalize_action_definition($action_def);
                    $action_id        = $normalized_action['id'];
                    $action_params    = $normalized_action['params'];

                    // Inject sanitized output from previous action into context if available
                    if (!empty($pipeline_output)) {
                        $current_context['_previous_action_output'] = $pipeline_output;
                    }

                    // Execute trusted action
                    $act_result = $this->execute_trusted_action(
                        $execution_id,
                        $workflow_id,
                        $action_id,
                        $current_context,
                        $action_params
                    );

                    $action_results[] = $act_result;

                    // Append compatible result structure for dispatch_trigger return format
                    $execution_results[] = array(
                        'execution_id' => $execution_id,
                        'workflow_id'  => $workflow_id,
                        'action'       => $action_id,
                        'status'       => $act_result['status'] === 'completed' ? 'success' : 'failed',
                        'success'      => $act_result['success'],
                        'message'      => $act_result['message'],
                        'error_code'   => $act_result['error_code'],
                        'data'         => $act_result['data'],
                        'context'      => $safe_context,
                    );

                    if ($act_result['success']) {
                        $completed_count++;
                        if (is_array($act_result['data']) && !empty($act_result['data'])) {
                            $pipeline_output = $this->extract_safe_action_output($act_result['data']);
                        }
                    } else {
                        $failed_count++;
                        // Halt pipeline execution on action failure unless explicitly configured to continue
                        if (empty($action_params['continue_on_failure'])) {
                            break;
                        }
                    }
                }

                // 8. Determine Truthful Workflow Execution Status
                $total_actions = count($action_defs);
                if ($failed_count === 0 && $completed_count === $total_actions) {
                    $final_wf_status = 'completed';
                    $wf_summary = sprintf(__('Workflow %s completed successfully (%d actions executed).', 'gmb-ranker-seo-automation'), $workflow_id, $completed_count);
                    $wf_error_code = '';
                } elseif ($completed_count > 0 && $failed_count > 0) {
                    $final_wf_status = 'partially_completed';
                    $wf_summary = sprintf(__('Workflow %s partially completed (%d succeeded, %d failed).', 'gmb-ranker-seo-automation'), $workflow_id, $completed_count, $failed_count);
                    $wf_error_code = 'action_failed';
                } else {
                    $final_wf_status = 'failed';
                    $wf_summary = sprintf(__('Workflow %s failed during action execution.', 'gmb-ranker-seo-automation'), $workflow_id);
                    $wf_error_code = 'action_failed';
                }

                // 9. Truthful Execution History Recording
                $actions_summary = array();
                foreach ($action_results as $ar) {
                    $actions_summary[] = array(
                        'action_id'  => $ar['action'],
                        'status'     => $ar['status'],
                        'success'    => $ar['success'],
                        'message'    => $ar['message'],
                        'error_code' => $ar['error_code'],
                    );
                }

                $this->repository->record_history(array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'trigger'      => $clean_trigger,
                    'status'       => $final_wf_status,
                    'actions_run'  => $completed_count,
                    'actions_total'=> $total_actions,
                    'actions'      => $actions_summary,
                    'started_at'   => $started_at_str,
                    'completed_at' => current_time('mysql'),
                    'duration'     => round(microtime(true) - $wf_start_time, 4),
                    'error_code'   => $wf_error_code,
                    'summary'      => $wf_summary,
                ));

            } catch (\Throwable $e) {
                // Safely log top-level workflow exception
                $this->repository->record_history(array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'trigger'      => $clean_trigger,
                    'status'       => 'failed',
                    'actions_run'  => 0,
                    'actions_total'=> count($validation['actions']),
                    'actions'      => array(),
                    'started_at'   => $started_at_str,
                    'completed_at' => current_time('mysql'),
                    'duration'     => round(microtime(true) - $wf_start_time, 4),
                    'error_code'   => 'action_exception',
                    'summary'      => sprintf(__('Workflow exception occurred: %s', 'gmb-ranker-seo-automation'), $e->getMessage()),
                ));

                $execution_results[] = array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'action'       => 'none',
                    'status'       => 'failed',
                    'success'      => false,
                    'message'      => sprintf(__('Workflow exception occurred: %s', 'gmb-ranker-seo-automation'), $e->getMessage()),
                    'error_code'   => 'action_exception',
                    'context'      => $safe_context,
                    'data'         => array(),
                );
            } finally {
                $this->release_workflow_lock($lock_key);
            }
        }

        return $execution_results;
    }

    /**
     * Execute action safely via trusted internal registry
     *
     * @param string $execution_id
     * @param string $workflow_id
     * @param string $action_id
     * @param array  $context
     * @param array  $params
     * @return array Normalized action result contract
     */
    protected function execute_trusted_action($execution_id, $workflow_id, $action_id, array $context, array $params) {
        $action_id_clean = sanitize_key($action_id);

        if (empty($action_id_clean) || !isset($this->actions[$action_id_clean])) {
            return array(
                'execution_id' => $execution_id,
                'workflow_id'  => $workflow_id,
                'action'       => $action_id,
                'status'       => 'invalid',
                'success'      => false,
                'message'      => sprintf(__('Action "%s" is not registered in the trusted action registry.', 'gmb-ranker-seo-automation'), $action_id),
                'data'         => array(),
                'error_code'   => 'action_not_registered',
                'retryable'    => false,
            );
        }

        $action_handler = $this->actions[$action_id_clean];
        if (!($action_handler instanceof GMB_Ranker_SEO_Action_Interface)) {
            return array(
                'execution_id' => $execution_id,
                'workflow_id'  => $workflow_id,
                'action'       => $action_id_clean,
                'status'       => 'invalid',
                'success'      => false,
                'message'      => sprintf(__('Action handler for "%s" does not implement GMB_Ranker_SEO_Action_Interface.', 'gmb-ranker-seo-automation'), $action_id_clean),
                'data'         => array(),
                'error_code'   => 'action_invalid',
                'retryable'    => false,
            );
        }

        $safe_params = $this->sanitize_action_params($params);

        try {
            $raw_result = $action_handler->execute($context, $safe_params);

            if (!is_array($raw_result)) {
                return array(
                    'execution_id' => $execution_id,
                    'workflow_id'  => $workflow_id,
                    'action'       => $action_id_clean,
                    'status'       => 'failed',
                    'success'      => false,
                    'message'      => sprintf(__('Action "%s" returned malformed non-array response.', 'gmb-ranker-seo-automation'), $action_id_clean),
                    'data'         => array(),
                    'error_code'   => 'action_invalid_response',
                    'retryable'    => false,
                );
            }

            $is_success = !empty($raw_result['success']);
            $message    = !empty($raw_result['message']) 
                ? sanitize_text_field($raw_result['message']) 
                : ($is_success ? __('Action executed successfully.', 'gmb-ranker-seo-automation') : __('Action execution failed.', 'gmb-ranker-seo-automation'));
            $data       = isset($raw_result['data']) && is_array($raw_result['data']) ? $this->extract_safe_action_output($raw_result['data']) : array();
            $error_code = !empty($raw_result['error_code']) ? sanitize_key($raw_result['error_code']) : ($is_success ? '' : 'action_failed');
            $retryable  = !empty($raw_result['retryable']);

            // Retry execution once if handler explicitly flags failure as retryable
            if (!$is_success && $retryable) {
                $retry_result = $action_handler->execute($context, $safe_params);
                if (is_array($retry_result) && !empty($retry_result['success'])) {
                    $is_success = true;
                    $message    = !empty($retry_result['message']) ? sanitize_text_field($retry_result['message']) : __('Action completed on retry.', 'gmb-ranker-seo-automation');
                    $data       = isset($retry_result['data']) && is_array($retry_result['data']) ? $this->extract_safe_action_output($retry_result['data']) : array();
                    $error_code = '';
                    $retryable  = false;
                }
            }

            return array(
                'execution_id' => $execution_id,
                'workflow_id'  => $workflow_id,
                'action'       => $action_id_clean,
                'status'       => $is_success ? 'completed' : 'failed',
                'success'      => $is_success,
                'message'      => $message,
                'data'         => $data,
                'error_code'   => $error_code,
                'retryable'    => $retryable,
            );

        } catch (\Throwable $e) {
            return array(
                'execution_id' => $execution_id,
                'workflow_id'  => $workflow_id,
                'action'       => $action_id_clean,
                'status'       => 'failed',
                'success'      => false,
                'message'      => sprintf(__('Exception executing action "%s": %s', 'gmb-ranker-seo-automation'), $action_id_clean, $e->getMessage()),
                'data'         => array(),
                'error_code'   => 'action_exception',
                'retryable'    => false,
            );
        }
    }

    /**
     * Validate workflow structure, enabled status, trigger matching, and actions array
     *
     * @param mixed  $wf
     * @param string $expected_trigger
     * @return array
     */
    protected function validate_workflow($wf, $expected_trigger) {
        if (!is_array($wf)) {
            return array(
                'is_valid'    => false,
                'workflow_id' => 'wf_malformed_' . substr(md5(uniqid(wp_rand(), true)), 0, 6),
                'reason'      => 'malformed',
                'error_code'  => 'invalid_workflow',
                'message'     => __('Workflow data is not an array.', 'gmb-ranker-seo-automation'),
                'actions'     => array(),
            );
        }

        $workflow_id = !empty($wf['id']) && is_string($wf['id']) ? sanitize_key($wf['id']) : 'wf_' . substr(md5(serialize($wf)), 0, 8);

        if (empty($wf['enabled'])) {
            return array(
                'is_valid'    => false,
                'workflow_id' => $workflow_id,
                'reason'      => 'disabled',
                'error_code'  => 'workflow_disabled',
                'message'     => sprintf(__('Workflow %s is disabled.', 'gmb-ranker-seo-automation'), $workflow_id),
                'actions'     => array(),
            );
        }

        $wf_trigger = !empty($wf['trigger']) && is_string($wf['trigger']) ? sanitize_key($wf['trigger']) : '';
        if ($wf_trigger !== $expected_trigger) {
            return array(
                'is_valid'    => false,
                'workflow_id' => $workflow_id,
                'reason'      => 'trigger_mismatch',
                'error_code'  => 'trigger_mismatch',
                'message'     => sprintf(__('Workflow trigger "%s" does not match target "%s".', 'gmb-ranker-seo-automation'), $wf_trigger, $expected_trigger),
                'actions'     => array(),
            );
        }

        if (empty($wf['actions']) || !is_array($wf['actions'])) {
            return array(
                'is_valid'    => false,
                'workflow_id' => $workflow_id,
                'reason'      => 'malformed',
                'error_code'  => 'invalid_workflow',
                'message'     => sprintf(__('Workflow %s has no valid actions configured.', 'gmb-ranker-seo-automation'), $workflow_id),
                'actions'     => array(),
            );
        }

        return array(
            'is_valid'    => true,
            'workflow_id' => $workflow_id,
            'reason'      => 'valid',
            'error_code'  => '',
            'message'     => __('Workflow validation passed.', 'gmb-ranker-seo-automation'),
            'actions'     => $wf['actions'],
        );
    }

    /**
     * Evaluate conditions array using fail-closed semantics
     *
     * @param array $wf
     * @param array $context
     * @return array
     */
    protected function evaluate_workflow_conditions(array $wf, array $context) {
        if (empty($wf['conditions']) || !is_array($wf['conditions'])) {
            return array(
                'passes'     => true,
                'message'    => __('No conditions configured; condition check passed.', 'gmb-ranker-seo-automation'),
                'error_code' => '',
                'details'    => array(),
            );
        }

        $details = array();
        foreach ($wf['conditions'] as $index => $rule) {
            if (!is_array($rule)) {
                return array(
                    'passes'     => false,
                    'message'    => sprintf(__('Malformed condition rule at index %d.', 'gmb-ranker-seo-automation'), $index),
                    'error_code' => 'invalid_condition',
                    'details'    => $details,
                );
            }

            // Normalize legacy 'key' attribute to 'field'
            $rule_normalized = $rule;
            if (empty($rule_normalized['field']) && !empty($rule_normalized['key'])) {
                $rule_normalized['field'] = $rule_normalized['key'];
            }

            if (empty($rule_normalized['field']) || !is_string($rule_normalized['field'])) {
                return array(
                    'passes'     => false,
                    'message'    => sprintf(__('Condition rule at index %d is missing target field.', 'gmb-ranker-seo-automation'), $index),
                    'error_code' => 'invalid_condition',
                    'details'    => $details,
                );
            }

            $rule_passes = false;
            if ($this->evaluator) {
                $rule_passes = $this->evaluator->evaluate($context, $rule_normalized);
            } else {
                // Fail-closed fallback matching if Evaluator class unavailable
                $field  = trim($rule_normalized['field']);
                $val    = isset($context[$field]) ? $context[$field] : null;
                $target = isset($rule_normalized['value']) ? $rule_normalized['value'] : null;
                $rule_passes = ($val !== null && (string)$val === (string)$target);
            }

            $details[] = array(
                'field'  => $rule_normalized['field'],
                'passes' => $rule_passes,
            );

            if (!$rule_passes) {
                return array(
                    'passes'     => false,
                    'message'    => sprintf(__('Workflow condition on field "%s" was not met.', 'gmb-ranker-seo-automation'), esc_html($rule_normalized['field'])),
                    'error_code' => 'conditions_not_met',
                    'details'    => $details,
                );
            }
        }

        return array(
            'passes'     => true,
            'message'    => __('All workflow conditions were met.', 'gmb-ranker-seo-automation'),
            'error_code' => '',
            'details'    => $details,
        );
    }

    /**
     * Normalize string actions and structured action definitions
     *
     * @param string|array $action_def
     * @return array ['id' => string, 'params' => array]
     */
    protected function normalize_action_definition($action_def) {
        if (is_string($action_def)) {
            return array(
                'id'     => sanitize_key(trim($action_def)),
                'params' => array(),
            );
        }

        if (is_array($action_def)) {
            $id = !empty($action_def['id']) && is_string($action_def['id']) 
                ? sanitize_key(trim($action_def['id'])) 
                : (!empty($action_def['action']) && is_string($action_def['action']) 
                    ? sanitize_key(trim($action_def['action'])) 
                    : '');

            $params = !empty($action_def['params']) && is_array($action_def['params']) 
                ? $action_def['params'] 
                : array();

            return array(
                'id'     => $id,
                'params' => $params,
            );
        }

        return array(
            'id'     => '',
            'params' => array(),
        );
    }

    /**
     * Recursively scrub sensitive credentials (passwords, tokens, API keys) from context
     *
     * @param array $context
     * @return array
     */
    protected function scrub_context(array $context) {
        $clean = array();
        foreach ($context as $key => $value) {
            $key_lower = strtolower((string)$key);

            $is_sensitive = false;
            foreach (self::$sensitive_keys as $s_key) {
                if (strpos($key_lower, $s_key) !== false) {
                    $is_sensitive = true;
                    break;
                }
            }

            if ($is_sensitive) {
                $clean[$key] = '[REDACTED_SENSITIVE_DATA]';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->scrub_context($value);
            } elseif (is_object($value)) {
                $clean[$key] = '[OBJECT:' . get_class($value) . ']';
            } elseif (is_scalar($value) || is_null($value)) {
                $clean[$key] = is_string($value) ? sanitize_text_field(wp_unslash($value)) : $value;
            } else {
                $clean[$key] = null;
            }
        }

        return $clean;
    }

    /**
     * Extract safe action output fields
     *
     * @param array $data
     * @return array
     */
    protected function extract_safe_action_output(array $data) {
        return $this->scrub_context($data);
    }

    /**
     * Sanitize parameters passed to actions
     *
     * @param array $params
     * @return array
     */
    protected function sanitize_action_params(array $params) {
        $clean = array();
        foreach ($params as $key => $val) {
            $clean_key = sanitize_key($key);
            if (is_array($val)) {
                $clean[$clean_key] = $this->sanitize_action_params($val);
            } elseif (is_numeric($val) || is_bool($val)) {
                $clean[$clean_key] = $val;
            } elseif (is_string($val)) {
                $clean[$clean_key] = sanitize_text_field(wp_unslash($val));
            } else {
                $clean[$clean_key] = null;
            }
        }
        return $clean;
    }

    /**
     * Acquire short-lived transient lock for workflow execution idempotency
     *
     * @param string $workflow_id
     * @param string $trigger
     * @param array  $context
     * @return string|false Lock key if acquired, false if locked
     */
    protected function acquire_workflow_lock($workflow_id, $trigger, array $context) {
        $entity_id = !empty($context['post_id']) ? intval($context['post_id']) : (!empty($context['topic']) ? md5((string)$context['topic']) : 'global');
        $lock_key  = 'gmb_wf_lock_' . md5($workflow_id . '_' . $trigger . '_' . $entity_id);

        if (get_transient($lock_key)) {
            return false;
        }

        set_transient($lock_key, time(), 30);
        return $lock_key;
    }

    /**
     * Release workflow transient lock
     *
     * @param string $lock_key
     */
    protected function release_workflow_lock($lock_key) {
        if ($lock_key) {
            delete_transient($lock_key);
        }
    }

    /**
     * Generate unique execution ID for correlation
     *
     * @return string
     */
    protected function generate_execution_id() {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }
        return 'exec_' . substr(md5(uniqid(wp_rand(), true)), 0, 16);
    }
}

