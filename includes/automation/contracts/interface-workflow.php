<?php
/**
 * Workflow Interface for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

interface GMB_Ranker_SEO_Workflow_Interface {

    /**
     * Get unique workflow ID
     *
     * @return string
     */
    public function get_id();

    /**
     * Run the workflow pipeline for context
     *
     * @param array $context
     * @return array Results
     */
    public function run(array $context);
}
