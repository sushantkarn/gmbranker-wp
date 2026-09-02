<?php
/**
 * Scheduled Cron Trigger for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Scheduled_Trigger implements GMB_Ranker_SEO_Trigger_Interface {

    const CRON_HOOK = 'gmb_ranker_cron_hourly_automation';

    public function get_id() {
        return 'scheduled';
    }

    public function get_name() {
        return 'Scheduled Cron Trigger (Hourly/Daily)';
    }

    public function register_listener(callable $dispatcher) {
        add_action(self::CRON_HOOK, function() use ($dispatcher) {
            call_user_func($dispatcher, 'scheduled', array('timestamp' => time()));
        });

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + 3600, 'hourly', self::CRON_HOOK);
        }
    }
}
