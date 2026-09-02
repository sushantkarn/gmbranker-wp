<?php
/**
 * Automation Queue for GMB Ranker SEO Automation
 *
 * Provides non-blocking background task execution via WP-Cron / shutdown callbacks.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Automation_Queue {

    const OPTION_QUEUE = 'gmb_ranker_automation_queue';
    const CRON_HOOK    = 'gmb_ranker_process_automation_queue';

    public function __construct() {
        add_action(self::CRON_HOOK, array($this, 'process_queue'));
    }

    /**
     * Push a job to the background queue
     *
     * @param string $action_id
     * @param array  $context
     * @param array  $params
     * @return bool
     */
    public function push($action_id, array $context = array(), array $params = array()) {
        $queue = get_option(self::OPTION_QUEUE, array());
        if (!is_array($queue)) {
            $queue = array();
        }

        $queue[] = array(
            'id'        => 'job_' . substr(md5(uniqid(wp_rand(), true)), 0, 8),
            'action_id' => $action_id,
            'context'   => $context,
            'params'    => $params,
            'queued_at' => time(),
        );

        $saved = update_option(self::OPTION_QUEUE, $queue);

        // Schedule async worker if not scheduled
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 5, self::CRON_HOOK);
        }

        return $saved;
    }

    /**
     * Process pending queue items
     *
     * @return int Count of processed items
     */
    public function process_queue() {
        $queue = get_option(self::OPTION_QUEUE, array());
        if (empty($queue) || !is_array($queue)) {
            return 0;
        }

        // Limit batch size to prevent timeouts
        $batch = array_splice($queue, 0, 10);
        update_option(self::OPTION_QUEUE, $queue);

        $manager = GMB_Ranker_SEO_Automation_Manager::get_instance();
        $processed = 0;

        foreach ($batch as $job) {
            if (isset($job['action_id'])) {
                $manager->execute_action($job['action_id'], $job['context'], $job['params']);
                $processed++;
            }
        }

        // Reschedule if more jobs remain
        if (!empty($queue) && !wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 10, self::CRON_HOOK);
        }

        return $processed;
    }
}
