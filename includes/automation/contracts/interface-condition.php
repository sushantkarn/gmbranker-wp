<?php
/**
 * Condition Interface for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

interface GMB_Ranker_SEO_Condition_Interface {

    /**
     * Evaluate whether condition passes given the execution context
     *
     * @param array $context
     * @param array $rule
     * @return bool
     */
    public function evaluate(array $context, array $rule);
}
