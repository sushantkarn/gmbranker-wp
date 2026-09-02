<?php
/**
 * Action Interface for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

interface GMB_Ranker_SEO_Action_Interface {

    /**
     * Get unique action identifier
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
     * Execute action logic with provided context and parameters
     *
     * @param array $context
     * @param array $params
     * @return array [ 'success' => bool, 'message' => string, 'data' => array ]
     */
    public function execute(array $context = array(), array $params = array());
}
