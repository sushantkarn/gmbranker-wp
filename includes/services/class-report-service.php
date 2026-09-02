<?php
/**
 * Report Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Report_Service {

    /**
     * @var GMB_Ranker_SEO_Report_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Report_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Report_Repository();
    }

    /**
     * Generate an overview site health and SEO audit summary
     *
     * @return array
     */
    public function generate_site_health_report() {
        $published_posts = wp_count_posts('post');
        $published_pages = wp_count_posts('page');

        $total_posts = isset($published_posts->publish) ? intval($published_posts->publish) : 0;
        $total_pages = isset($published_pages->publish) ? intval($published_pages->publish) : 0;

        $report = array(
            'generated_at'    => current_time('mysql'),
            'total_posts'     => $total_posts,
            'total_pages'     => $total_pages,
            'has_sitemap'     => (get_option('gmb_ranker_module_sitemaps', '1') === '1'),
            'has_schema'      => (get_option('gmb_ranker_module_schema', '1') === '1'),
            'has_analytics'   => (get_option('gmb_ranker_module_analytics', '1') === '1'),
            'has_indexing'    => (get_option('gmb_ranker_module_instant_indexing', '1') === '1'),
        );

        $this->repository->record_audit_log($report);
        return $report;
    }
}
