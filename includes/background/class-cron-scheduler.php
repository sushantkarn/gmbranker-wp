<?php
/**
 * Cron Scheduler for GMB Ranker SEO Automation
 *
 * Coordinates scheduled events for rankings, health audits, sitemap pings, and automation queue.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Cron_Scheduler {

    const HOURLY_EVENT = 'gmb_ranker_hourly_cron_event';
    const DAILY_EVENT  = 'gmb_ranker_daily_cron_event';

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Cron_Scheduler|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Cron_Scheduler
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register recurring schedules
     */
    public function register_schedules() {
        add_action(self::HOURLY_EVENT, array($this, 'run_hourly_tasks'));
        add_action(self::DAILY_EVENT,  array($this, 'run_daily_tasks'));

        if (!wp_next_scheduled(self::HOURLY_EVENT)) {
            wp_schedule_event(time() + 3600, 'hourly', self::HOURLY_EVENT);
        }
        if (!wp_next_scheduled(self::DAILY_EVENT)) {
            wp_schedule_event(time() + 86400, 'daily', self::DAILY_EVENT);
        }
    }

    /**
     * Clear scheduled events on plugin deactivation
     */
    public function clear_schedules() {
        $hourly = wp_next_scheduled(self::HOURLY_EVENT);
        if ($hourly) {
            wp_unschedule_event($hourly, self::HOURLY_EVENT);
        }
        $daily = wp_next_scheduled(self::DAILY_EVENT);
        if ($daily) {
            wp_unschedule_event($daily, self::DAILY_EVENT);
        }
    }

    /**
     * Execute hourly background tasks
     */
    public function run_hourly_tasks() {
        // 1. Process automation queue
        if (class_exists('GMB_Ranker_SEO_Automation_Queue')) {
            $queue = new GMB_Ranker_SEO_Automation_Queue();
            $queue->process_queue();
        }
    }

    /**
     * Execute daily background tasks
     */
    public function run_daily_tasks() {
        // 1. Generate site health audit report
        if (class_exists('GMB_Ranker_SEO_Report_Service')) {
            $report_service = new GMB_Ranker_SEO_Report_Service();
            $report_service->generate_site_health_report();
        }
    }
}
