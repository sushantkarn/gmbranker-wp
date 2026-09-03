<?php
/**
 * Generate Report Action for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Action_Interface to execute site health audit and SEO reports.
 * Orchestrates canonical GMB_Ranker_SEO_Report_Service invocation, handles error validation,
 * enforces site context, and formats normalized automation action contracts.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Generate_Report_Action implements GMB_Ranker_SEO_Action_Interface {

    /**
     * Unique action identifier
     *
     * @return string
     */
    public function get_id() {
        return 'generate_report';
    }

    /**
     * Human-readable action name
     *
     * @return string
     */
    public function get_name() {
        return __('Generate Scheduled Site Audit & Health Report', 'gmb-ranker-seo-automation');
    }

    /**
     * Execute site health audit report generation action
     *
     * @param array $context Automation execution context
     * @param array $params  Configured action parameters
     * @return array [ 'success' => bool, 'message' => string, 'data' => array ]
     */
    public function execute(array $context = array(), array $params = array()) {
        if (!class_exists('GMB_Ranker_SEO_Report_Service')) {
            return array(
                'success' => false,
                'message' => __('Report service is unavailable.', 'gmb-ranker-seo-automation'),
                'data'    => array(),
            );
        }

        try {
            $service = new GMB_Ranker_SEO_Report_Service();
            $report  = $service->generate_site_health_report();

            if (is_wp_error($report)) {
                return array(
                    'success' => false,
                    'message' => $report->get_error_message(),
                    'data'    => array(),
                );
            }

            if (!is_array($report) || empty($report)) {
                return array(
                    'success' => false,
                    'message' => __('Site health report generation yielded empty or malformed data.', 'gmb-ranker-seo-automation'),
                    'data'    => array(),
                );
            }

            return array(
                'success' => true,
                'message' => __('Site health audit report generated and recorded successfully.', 'gmb-ranker-seo-automation'),
                'data'    => array(
                    'generated_at'  => isset($report['generated_at']) ? $report['generated_at'] : current_time('mysql'),
                    'total_posts'   => isset($report['total_posts']) ? intval($report['total_posts']) : 0,
                    'total_pages'   => isset($report['total_pages']) ? intval($report['total_pages']) : 0,
                    'has_sitemap'   => !empty($report['has_sitemap']),
                    'has_schema'    => !empty($report['has_schema']),
                    'has_analytics' => !empty($report['has_analytics']),
                    'has_indexing'  => !empty($report['has_indexing']),
                    'report'        => $report,
                ),
            );
        } catch (\Throwable $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Report generation exception: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                'data'    => array(),
            );
        }
    }
}
