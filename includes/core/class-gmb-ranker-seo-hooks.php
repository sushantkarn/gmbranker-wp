<?php
/**
 * Centralized Hooks Manager for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Hooks {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Hooks|null
     */
    private static $instance = null;

    /**
     * Track registered actions & filters
     *
     * @var array
     */
    private $registered_hooks = array();

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Hooks
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an action hook safely with tracking
     *
     * @param string   $tag             The name of the action to which the $function_to_add is hooked.
     * @param callable $callback        The callback to be run when the action is called.
     * @param int      $priority        Optional. Priority. Default 10.
     * @param int      $accepted_args   Optional. Number of arguments. Default 1.
     */
    public function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
        add_action($tag, $callback, $priority, $accepted_args);
        $this->registered_hooks['actions'][$tag][] = array(
            'callback' => $callback,
            'priority' => $priority,
            'args'     => $accepted_args,
        );
    }

    /**
     * Register a filter hook safely with tracking
     *
     * @param string   $tag             The name of the filter to which the $function_to_add is hooked.
     * @param callable $callback        The callback to be run when the filter is applied.
     * @param int      $priority        Optional. Priority. Default 10.
     * @param int      $accepted_args   Optional. Number of arguments. Default 1.
     */
    public function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
        add_filter($tag, $callback, $priority, $accepted_args);
        $this->registered_hooks['filters'][$tag][] = array(
            'callback' => $callback,
            'priority' => $priority,
            'args'     => $accepted_args,
        );
    }

    /**
     * Get list of registered hooks for debugging/inspection
     *
     * @return array
     */
    public function get_registered_hooks() {
        return $this->registered_hooks;
    }
}
