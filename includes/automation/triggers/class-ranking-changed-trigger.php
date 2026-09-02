<?php
/**
 * Ranking Changed Trigger for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ranking_Changed_Trigger implements GMB_Ranker_SEO_Trigger_Interface {

    public function get_id() {
        return 'ranking_changed';
    }

    public function get_name() {
        return 'Keyword Ranking Position Changed';
    }

    public function register_listener(callable $dispatcher) {
        add_action('gmb_ranker_ranking_updated', function($query, $old_pos, $new_pos) use ($dispatcher) {
            call_user_func($dispatcher, 'ranking_changed', array(
                'query'    => $query,
                'old_pos'  => $old_pos,
                'new_pos'  => $new_pos,
                'diff'     => $old_pos - $new_pos,
            ));
        }, 10, 3);
    }
}
