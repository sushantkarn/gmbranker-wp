<?php
/**
 * Generate Report Action for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Generate_Report_Action implements GMB_Ranker_SEO_Action_Interface {

    public function get_id() {
        return 'generate_report';
    }

    public function get_name() {
        return 'Generate Scheduled Site Audit & Health Report';
    }

    public function execute(array $context = array(), array $params = array()) {
        $service = new GMB_Ranker_SEO_Report_Service();
        $report = $service->generate_site_health_report();

        return array(
            'success' => true,
            'message' => 'Site health audit generated successfully.',
            'data'    => $report,
        );
    }
}
