<?php
/**
 * Trigger Interface for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

interface GMB_Ranker_SEO_Trigger_Interface {

    /**
     * Get unique trigger identifier
     *
     * @return string
     */
    public function get_id();

    /**
     * Get human-readable title
     *
     * @return string
     */
    public function get_name();

    /**
     * Register necessary WordPress hooks or listeners for this trigger
     *
     * @param callable $dispatcher Callback to execute when trigger fires
     */
    public function register_listener(callable $dispatcher);
}
