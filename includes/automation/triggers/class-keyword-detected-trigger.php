<?php
/**
 * Keyword Detected Trigger for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Keyword_Detected_Trigger implements GMB_Ranker_SEO_Trigger_Interface {

    public function get_id() {
        return 'keyword_detected';
    }

    public function get_name() {
        return 'High Opportunity Keyword Detected';
    }

    public function register_listener(callable $dispatcher) {
        add_action('gmb_ranker_opportunity_keyword_found', function($keyword, $metrics) use ($dispatcher) {
            call_user_func($dispatcher, 'keyword_detected', array(
                'keyword' => $keyword,
                'metrics' => $metrics,
            ));
        }, 10, 2);
    }
}
