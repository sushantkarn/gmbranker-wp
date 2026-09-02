<?php
/**
 * Manual Trigger for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Manual_Trigger implements GMB_Ranker_SEO_Trigger_Interface {

    public function get_id() {
        return 'manual';
    }

    public function get_name() {
        return 'Manual Execution (On-Demand / Admin / REST)';
    }

    public function register_listener(callable $dispatcher) {
        // Manual triggers are invoked directly via REST or Admin AJAX
    }
}
