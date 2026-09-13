<?php
/**
 * Overall Website SEO Health & Performance Dashboard
 *
 * Internal, production-grade presentation layer for overall website SEO health,
 * meta optimization ratios, focus keyword coverage, schema structure, and technical indexability.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$analytics_engine = class_exists('GMB_Ranker_SEO_Analytics') ? GMB_Ranker_SEO_Analytics::get_instance() : null;
$health_data      = $analytics_engine ? $analytics_engine->get_site_health_data() : array();

$score          = isset($health_data['overall_score']) ? intval($health_data['overall_score']) : 75;
$total_posts    = isset($health_data['total_posts']) ? intval($health_data['total_posts']) : 0;
$meta_pct       = isset($health_data['meta_pct']) ? intval($health_data['meta_pct']) : 0;
$kw_pct         = isset($health_data['kw_pct']) ? intval($health_data['kw_pct']) : 0;
$schema_pct     = isset($health_data['schema_pct']) ? intval($health_data['schema_pct']) : 0;
$checklist      = isset($health_data['checklist']) && is_array($health_data['checklist']) ? $health_data['checklist'] : array();
?>

<div class="rm-tab-content active" id="rm-tab-performance">
    <div class="gmb-admin-wrap">
        <div class="gmb-analytics-container">

        <!-- 4 Uniform Core SEO Health KPI Cards -->
        <div class="gmb-analytics-kpi-grid">
            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label" style="text-transform: none !important;">
                    <span><?php esc_html_e('Overall SEO Health Score', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-clicks">
                    <?php echo esc_html($score); ?> <span style="font-size: 1rem; color: #94a3b8; font-weight: 500;">/ 100</span>
                </div>
                <div class="gmb-kpi-subtext">
                    <?php echo esc_html(sprintf(__('Based on %d published posts & pages', 'gmb-ranker-seo-automation'), $total_posts)); ?>
                </div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label" style="text-transform: none !important;">
                    <span><?php esc_html_e('Titles & Meta Optimization', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-impressions">
                    <?php echo esc_html($meta_pct); ?>%
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Custom Meta Titles & Descriptions set', 'gmb-ranker-seo-automation'); ?></div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label" style="text-transform: none !important;">
                    <span><?php esc_html_e('Focus Keyword Coverage', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-ctr">
                    <?php echo esc_html($kw_pct); ?>%
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Content assigned target focus keywords', 'gmb-ranker-seo-automation'); ?></div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label" style="text-transform: none !important;">
                    <span><?php esc_html_e('Schema & Structured Data', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-pos">
                    <?php echo esc_html($schema_pct); ?>%
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Content with JSON-LD Schema enabled', 'gmb-ranker-seo-automation'); ?></div>
            </div>
        </div>

        <!-- SEO Health Audit Checklist Table -->
        <div class="gmb-analytics-tables-grid" style="grid-template-columns: 1fr; margin-top: 24px;">
            <div class="gmb-table-card">
                <div class="gmb-table-header" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; margin-bottom: 12px;">
                    <div>
                        <h3 class="gmb-table-title" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a;"><?php esc_html_e('Website SEO Health Audit Checklist', 'gmb-ranker-seo-automation'); ?></h3>
                        <span class="gmb-table-subtitle" style="font-size: 0.8rem; color: #64748b;"><?php esc_html_e('Actionable diagnostic findings across on-page, technical, and indexability factors', 'gmb-ranker-seo-automation'); ?></span>
                    </div>
                    <div>
                        <button type="button" class="gmb-analytics-sync-btn" id="gmb-sync-analytics-btn" data-action="sync-analytics">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                            <span id="gmb-sync-btn-label"><?php esc_html_e('Run Fresh Audit', 'gmb-ranker-seo-automation'); ?></span>
                        </button>
                    </div>
                </div>
                <table class="gmb-analytics-table">
                    <thead>
                        <tr>
                            <th style="width: 30%; text-align: left; padding: 10px 12px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;"><?php esc_html_e('Diagnostic Factor', 'gmb-ranker-seo-automation'); ?></th>
                            <th style="width: 45%; text-align: left; padding: 10px 12px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;"><?php esc_html_e('Audit Finding & Current Status', 'gmb-ranker-seo-automation'); ?></th>
                            <th style="width: 10%; text-align: center; padding: 10px 12px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;"><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></th>
                            <th style="width: 15%; text-align: right; padding: 10px 12px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;"><?php esc_html_e('Action', 'gmb-ranker-seo-automation'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="gmb-tbody-health">
                        <?php
                        $action_url_map = array(
                            'meta_optimization'      => admin_url('admin.php?page=gmb-ranker-metadata'),
                            'focus_keywords'         => admin_url('edit.php'),
                            'schema_structured_data' => admin_url('admin.php?page=gmb-ranker-schema'),
                            'xml_sitemaps'           => admin_url('admin.php?page=gmb-ranker-sitemaps'),
                            'instant_indexing'       => admin_url('admin.php?page=gmb-ranker-instant-indexing'),
                            'robots_txt'             => admin_url('admin.php?page=gmb-ranker-settings'),
                        );
                        foreach ($checklist as $item) :
                            $status_cls = ($item['status'] === 'GOOD') ? 'pos-top3' : (($item['status'] === 'WARNING') ? 'pos-top10' : 'pos-standard');
                            $item_id    = isset($item['id']) ? $item['id'] : '';
                            $target_url = isset($action_url_map[$item_id]) ? $action_url_map[$item_id] : (isset($item['action_url']) ? $item['action_url'] : '#');
                            ?>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td class="gmb-query-cell" style="padding: 12px;">
                                    <strong style="color: #0f172a; font-size: 0.88rem;"><?php echo esc_html($item['title']); ?></strong>
                                </td>
                                <td style="padding: 12px;">
                                    <span class="gmb-text-muted" style="font-size: 0.85rem; color: #475569;"><?php echo esc_html($item['description']); ?></span>
                                </td>
                                <td style="text-align: center; padding: 12px;">
                                    <span class="gmb-pos-badge <?php echo esc_attr($status_cls); ?>" style="padding: 3px 10px; font-weight: 700; font-size: 10px; letter-spacing: 0.04em;">
                                        <?php echo esc_html($item['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; padding: 12px;">
                                    <a href="<?php echo esc_url($target_url); ?>" class="button button-small button-secondary" style="font-size: 11px; height: 28px; line-height: 26px;">
                                        <?php echo esc_html($item['action_label']); ?> &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Internal Architecture Info Card -->
        <div class="gmb-analytics-info-footer" style="margin-top: 24px; padding: 16px 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
            <div class="gmb-flex-between" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.82rem; color: #475569;">
                    <strong style="color: #0f172a;"><?php esc_html_e('Internal SEO Health Engine', 'gmb-ranker-seo-automation'); ?></strong> &bull; <?php esc_html_e('Automated real-time analysis of meta titles, descriptions, focus keywords, schema structured data, sitemaps, and indexability.', 'gmb-ranker-seo-automation'); ?>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary" style="font-size: 11px;"><?php esc_html_e('Back to Dashboard', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        </div>

    </div>
</div>
