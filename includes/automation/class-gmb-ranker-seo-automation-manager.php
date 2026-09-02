<?php
/**
 * Master Automation Manager for GMB Ranker SEO Automation
 *
 * Orchestrates Triggers, Conditions, Workflows, and Actions.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Automation_Manager {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Automation_Manager|null
     */
    private static $instance = null;

    /**
     * Registered Triggers
     *
     * @var array<string, GMB_Ranker_SEO_Trigger_Interface>
     */
    protected $triggers = array();

    /**
     * Registered Actions
     *
     * @var array<string, GMB_Ranker_SEO_Action_Interface>
     */
    protected $actions = array();

    /**
     * Condition Evaluator
     *
     * @var GMB_Ranker_SEO_Condition_Evaluator
     */
    protected $evaluator;

    /**
     * Automation Queue
     *
     * @var GMB_Ranker_SEO_Automation_Queue
     */
    protected $queue;

    /**
     * Automation Repository
     *
     * @var GMB_Ranker_SEO_Automation_Repository
     */
    protected $repository;

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Automation_Manager
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->evaluator  = new GMB_Ranker_SEO_Condition_Evaluator();
        $this->queue      = new GMB_Ranker_SEO_Automation_Queue();
        $this->repository = new GMB_Ranker_SEO_Automation_Repository();

        $this->register_default_components();
    }

    /**
     * Register core built-in triggers and actions
     */
    protected function register_default_components() {
        // Core Triggers
        $this->register_trigger(new GMB_Ranker_SEO_Manual_Trigger());
        $this->register_trigger(new GMB_Ranker_SEO_Scheduled_Trigger());
        $this->register_trigger(new GMB_Ranker_SEO_Post_Published_Trigger());
        $this->register_trigger(new GMB_Ranker_SEO_Ranking_Changed_Trigger());
        $this->register_trigger(new GMB_Ranker_SEO_Keyword_Detected_Trigger());

        // Core Actions
        $this->register_action(new GMB_Ranker_SEO_Generate_Content_Action());
        $this->register_action(new GMB_Ranker_SEO_Optimize_Page_Action());
        $this->register_action(new GMB_Ranker_SEO_Update_Meta_Action());
        $this->register_action(new GMB_Ranker_SEO_Generate_Report_Action());
        $this->register_action(new GMB_Ranker_SEO_Sync_Gbp_Action());
    }

    /**
     * Register a new Trigger
     *
     * @param GMB_Ranker_SEO_Trigger_Interface $trigger
     */
    public function register_trigger(GMB_Ranker_SEO_Trigger_Interface $trigger) {
        $id = $trigger->get_id();
        $this->triggers[$id] = $trigger;

        // Register listener with trigger
        $trigger->register_listener(array($this, 'handle_trigger_event'));
    }

    /**
     * Register a new Action
     *
     * @param GMB_Ranker_SEO_Action_Interface $action
     */
    public function register_action(GMB_Ranker_SEO_Action_Interface $action) {
        $this->actions[$action->get_id()] = $action;
    }

    /**
     * Get all registered triggers
     *
     * @return array<string, GMB_Ranker_SEO_Trigger_Interface>
     */
    public function get_triggers() {
        return $this->triggers;
    }

    /**
     * Get all registered actions
     *
     * @return array<string, GMB_Ranker_SEO_Action_Interface>
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Event dispatcher callback called when any trigger fires
     *
     * @param string $trigger_id
     * @param array  $context
     */
    public function handle_trigger_event($trigger_id, array $context = array()) {
        $workflows = $this->repository->get_workflows();

        foreach ($workflows as $wf) {
            if (empty($wf['enabled']) || empty($wf['trigger']) || $wf['trigger'] !== $trigger_id) {
                continue;
            }

            // Evaluate conditions
            $passes = true;
            if (!empty($wf['conditions']) && is_array($wf['conditions'])) {
                foreach ($wf['conditions'] as $rule) {
                    if (!$this->evaluator->evaluate($context, $rule)) {
                        $passes = false;
                        break;
                    }
                }
            }

            if ($passes && !empty($wf['actions']) && is_array($wf['actions'])) {
                foreach ($wf['actions'] as $action_def) {
                    $action_id = is_array($action_def) ? $action_def['id'] : $action_def;
                    $params    = is_array($action_def) && isset($action_def['params']) ? $action_def['params'] : array();
                    $async     = !empty($wf['async']);

                    if ($async) {
                        $this->queue->push($action_id, $context, $params);
                    } else {
                        $this->execute_action($action_id, $context, $params);
                    }
                }

                $this->repository->record_history(array(
                    'workflow_id' => isset($wf['id']) ? $wf['id'] : 'wf_auto',
                    'trigger'     => $trigger_id,
                    'status'      => 'success',
                    'actions_run' => count($wf['actions']),
                ));
            }
        }
    }

    /**
     * Execute an action directly
     *
     * @param string $action_id
     * @param array  $context
     * @param array  $params
     * @return array
     */
    public function execute_action($action_id, array $context = array(), array $params = array()) {
        if (!isset($this->actions[$action_id])) {
            return array(
                'success' => false,
                'message' => 'Action ' . esc_html($action_id) . ' is not registered.',
                'data'    => array(),
            );
        }

        return $this->actions[$action_id]->execute($context, $params);
    }
}
