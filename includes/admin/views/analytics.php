<?php
/**
 * Analytics & Performance Dedicated View
 *
 * Enterprise-grade, accessible presentation layer for organic search performance,
 * Search Console metrics, impression trajectories, top queries, and landing page reports.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$analytics_engine = class_exists('GMB_Ranker_SEO_Analytics') ? GMB_Ranker_SEO_Analytics::get_instance() : null;
$analytics_data   = $analytics_engine ? $analytics_engine->get_analytics_data() : array();

$totals        = isset($analytics_data['totals']) && is_array($analytics_data['totals']) ? $analytics_data['totals'] : array();
$top_queries   = isset($analytics_data['top_queries']) && is_array($analytics_data['top_queries']) ? $analytics_data['top_queries'] : array();
$top_pages     = isset($analytics_data['top_pages']) && is_array($analytics_data['top_pages']) ? $analytics_data['top_pages'] : array();
$is_connected  = isset($analytics_data['status']) && $analytics_data['status'] === 'connected';
$sparkline     = isset($analytics_data['sparkline']) && is_array($analytics_data['sparkline']) ? $analytics_data['sparkline'] : array();
$has_real_data = (!empty($totals['clicks']) || !empty($totals['impressions']) || !empty($top_queries) || !empty($top_pages));

// Bounded dynamic SVG sparkline trajectory coordinates
$clicks_arr = (!empty($sparkline['clicks']) && is_array($sparkline['clicks'])) ? array_map('floatval', $sparkline['clicks']) : array_fill(0, 28, 0.0);
$imp_arr    = (!empty($sparkline['impressions']) && is_array($sparkline['impressions'])) ? array_map('floatval', $sparkline['impressions']) : array_fill(0, 28, 0.0);

$c_count = max(1, count($clicks_arr));
$max_c   = max(1.0, (float)max($clicks_arr));
$max_imp = max(1.0, (float)max($imp_arr));

$c_pts   = array();
$imp_pts = array();
for ($i = 0; $i < $c_count; $i++) {
    $x       = round(($i / max(1, $c_count - 1)) * 800, 1);
    $c_val   = isset($clicks_arr[$i]) ? $clicks_arr[$i] : 0.0;
    $imp_val = isset($imp_arr[$i]) ? $imp_arr[$i] : 0.0;

    $y_c     = $has_real_data ? round(110.0 - (($c_val / $max_c) * 85.0), 1) : 110.0;
    $y_imp   = $has_real_data ? round(110.0 - (($imp_val / $max_imp) * 85.0), 1) : 110.0;

    $c_pts[]   = "{$x} {$y_c}";
    $imp_pts[] = "{$x} {$y_imp}";
}

$c_path_d   = 'M ' . implode(' L ', $c_pts);
$c_fill_d   = $c_path_d . ' L 800 120 L 0 120 Z';
$imp_path_d = 'M ' . implode(' L ', $imp_pts);
$imp_fill_d = $imp_path_d . ' L 800 120 L 0 120 Z';
?>

<div class="rm-tab-content active" id="rm-tab-performance">
    <div class="gmb-analytics-widget gmb-analytics-standalone-page">
        
        <!-- Header & Action Row -->
        <div class="gmb-analytics-header">
            <div class="gmb-analytics-title-group">
                <div class="gmb-flex-center-gap-sm">
                    <div class="gmb-analytics-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <div>
                        <h2 class="gmb-analytics-title"><?php esc_html_e('Search Console & Organic Analytics Performance', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Organic search impressions, click-through rates, and Google ranking positions via Google Search Console API.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="gmb-flex-center-gap-sm">
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-secondary gmb-btn gmb-btn-secondary" title="<?php esc_attr_e('Configure Google Service Account', 'gmb-ranker-seo-automation'); ?>">
                    <?php esc_html_e('Google Service Account →', 'gmb-ranker-seo-automation'); ?>
                </a>
                <button type="button" class="gmb-analytics-sync-btn" id="gmb-sync-analytics-btn" data-action="sync-analytics">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    <span id="gmb-sync-btn-label"><?php esc_html_e('Sync Live Data', 'gmb-ranker-seo-automation'); ?></span>
                </button>
            </div>
        </div>

        <?php if (!$is_connected) : ?>
            <div class="gmb-analytics-connect-banner">
                <div>
                    <strong class="gmb-analytics-connect-banner-title"><?php esc_html_e('Connect Google Search Console Service Account', 'gmb-ranker-seo-automation'); ?></strong>
                    <p class="gmb-analytics-connect-banner-desc"><?php esc_html_e('Add your Google Service Account JSON key in Instant Indexing / Google Settings to start pulling live ranking metrics, search queries, and impression trajectories.', 'gmb-ranker-seo-automation'); ?></p>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-primary gmb-analytics-connect-banner-btn"><?php esc_html_e('Configure Google Key →', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4 KPI Summary Cards -->
        <div class="gmb-analytics-kpi-grid">
            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span><?php esc_html_e('Total Clicks', 'gmb-ranker-seo-automation'); ?></span>
                    <?php if ($has_real_data && !empty($totals['clicks_diff']) && $totals['clicks_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['clicks_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-clicks">
                    <?php echo isset($totals['clicks']) ? number_format_i18n((int)$totals['clicks']) : '0'; ?>
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Past 28 Days (Organic Search)', 'gmb-ranker-seo-automation'); ?></div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span><?php esc_html_e('Total Impressions', 'gmb-ranker-seo-automation'); ?></span>
                    <?php if ($has_real_data && !empty($totals['imp_diff']) && $totals['imp_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['imp_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-impressions">
                    <?php echo isset($totals['impressions']) ? number_format_i18n((int)$totals['impressions']) : '0'; ?>
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Google Search Visibility', 'gmb-ranker-seo-automation'); ?></div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span><?php esc_html_e('Average CTR', 'gmb-ranker-seo-automation'); ?></span>
                    <?php if ($has_real_data && !empty($totals['ctr_diff']) && $totals['ctr_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['ctr_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-ctr">
                    <?php echo (isset($totals['ctr']) && (float)$totals['ctr'] > 0) ? esc_html($totals['ctr']) . '%' : '0.0%'; ?>
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Click-Through Rate', 'gmb-ranker-seo-automation'); ?></div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span><?php esc_html_e('Average Position', 'gmb-ranker-seo-automation'); ?></span>
                    <?php if ($has_real_data && !empty($totals['pos_diff']) && $totals['pos_diff'] !== '0') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['pos_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-pos">
                    <?php echo (isset($totals['position']) && (float)$totals['position'] > 0) ? esc_html(number_format((float)$totals['position'], 1)) : '—'; ?>
                </div>
                <div class="gmb-kpi-subtext"><?php esc_html_e('Average Search Ranking', 'gmb-ranker-seo-automation'); ?></div>
            </div>
        </div>

        <!-- 28-Day Trajectory Sparkline Graph -->
        <div class="gmb-analytics-graph-card">
            <div class="gmb-graph-header">
                <h3 class="gmb-graph-title"><?php esc_html_e('28-Day Search Performance Trajectory', 'gmb-ranker-seo-automation'); ?></h3>
                <div class="gmb-graph-legend">
                    <span class="legend-dot clicks"></span> <?php esc_html_e('Daily Clicks', 'gmb-ranker-seo-automation'); ?>
                    <span class="legend-dot impressions gmb-legend-dot-margin"></span> <?php esc_html_e('Daily Impressions', 'gmb-ranker-seo-automation'); ?>
                </div>
            </div>
            <div class="gmb-graph-svg-wrap">
                <svg class="gmb-sparkline-svg" viewBox="0 0 800 120" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e('28-Day Search Performance Graph', 'gmb-ranker-seo-automation'); ?>">
                    <defs>
                        <linearGradient id="clicksGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="impGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.20"/>
                            <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <line x1="0" y1="30" x2="800" y2="30" stroke="#f1f5f9" stroke-width="1" />
                    <line x1="0" y1="60" x2="800" y2="60" stroke="#f1f5f9" stroke-width="1" />
                    <line x1="0" y1="90" x2="800" y2="90" stroke="#f1f5f9" stroke-width="1" />

                    <path d="<?php echo esc_attr($imp_fill_d); ?>" fill="url(#impGrad)" />
                    <path d="<?php echo esc_attr($imp_path_d); ?>" fill="none" stroke="#a855f7" stroke-width="2.5" stroke-linecap="round" />

                    <path d="<?php echo esc_attr($c_fill_d); ?>" fill="url(#clicksGrad)" />
                    <path d="<?php echo esc_attr($c_path_d); ?>" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
        </div>

        <!-- 2 Data Tables: Top Search Queries & Top Landing Pages -->
        <div class="gmb-analytics-tables-grid">
            <div class="gmb-table-card">
                <div class="gmb-table-header">
                    <h3 class="gmb-table-title"><?php esc_html_e('Top Ranking Search Queries', 'gmb-ranker-seo-automation'); ?></h3>
                    <span class="gmb-table-subtitle"><?php esc_html_e('Search Console Queries', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <table class="gmb-analytics-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Keyword / Query', 'gmb-ranker-seo-automation'); ?></th>
                            <th class="gmb-text-right"><?php esc_html_e('Clicks', 'gmb-ranker-seo-automation'); ?></th>
                            <th class="gmb-text-right"><?php esc_html_e('Imp.', 'gmb-ranker-seo-automation'); ?></th>
                            <th class="gmb-text-right"><?php esc_html_e('Pos', 'gmb-ranker-seo-automation'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="gmb-tbody-queries">
                        <?php if (!empty($top_queries)) : ?>
                            <?php foreach ($top_queries as $q) : ?>
                                <?php
                                $pos = isset($q['position']) ? (float)$q['position'] : 0.0;
                                $badge_cls = ($pos > 0.0 && $pos <= 3.0) ? 'pos-top3' : (($pos > 0.0 && $pos <= 10.0) ? 'pos-top10' : 'pos-standard');
                                $pos_str   = ($pos > 0.0) ? number_format($pos, 1) : '—';
                                ?>
                                <tr>
                                    <td class="gmb-query-cell"><strong><?php echo esc_html(isset($q['query']) ? $q['query'] : ''); ?></strong></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n(isset($q['clicks']) ? (int)$q['clicks'] : 0); ?></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n(isset($q['impressions']) ? (int)$q['impressions'] : 0); ?></td>
                                    <td class="gmb-text-right">
                                        <span class="gmb-pos-badge <?php echo esc_attr($badge_cls); ?>"><?php echo esc_html($pos_str); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="gmb-analytics-empty-cell">
                                    <?php esc_html_e('No search queries recorded yet. Connect your Google Service Account in Instant Indexing → Google Settings to sync live queries from Google Search Console.', 'gmb-ranker-seo-automation'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="gmb-table-card">
                <div class="gmb-table-header">
                    <h3 class="gmb-table-title"><?php esc_html_e('Top Landing Pages', 'gmb-ranker-seo-automation'); ?></h3>
                    <span class="gmb-table-subtitle"><?php esc_html_e('Organic URLs', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <table class="gmb-analytics-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Page URL', 'gmb-ranker-seo-automation'); ?></th>
                            <th class="gmb-text-right"><?php esc_html_e('Clicks', 'gmb-ranker-seo-automation'); ?></th>
                            <th class="gmb-text-right"><?php esc_html_e('Pos', 'gmb-ranker-seo-automation'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="gmb-tbody-pages">
                        <?php if (!empty($top_pages)) : ?>
                            <?php foreach ($top_pages as $p) : ?>
                                <?php
                                $page_url  = isset($p['page']) ? $p['page'] : (isset($p['url']) ? $p['url'] : '/');
                                $pos       = isset($p['position']) ? (float)$p['position'] : 0.0;
                                $badge_cls = ($pos > 0.0 && $pos <= 3.0) ? 'pos-top3' : (($pos > 0.0 && $pos <= 10.0) ? 'pos-top10' : 'pos-standard');
                                $pos_str   = ($pos > 0.0) ? number_format($pos, 1) : '—';
                                ?>
                                <tr>
                                    <td class="gmb-page-cell"><code><?php echo esc_html($page_url); ?></code></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n(isset($p['clicks']) ? (int)$p['clicks'] : 0); ?></td>
                                    <td class="gmb-text-right">
                                        <span class="gmb-pos-badge <?php echo esc_attr($badge_cls); ?>"><?php echo esc_html($pos_str); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="gmb-analytics-empty-cell">
                                    <?php esc_html_e('No organic landing page data recorded yet.', 'gmb-ranker-seo-automation'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cloud Architecture Card -->
        <div class="gmb-analytics-info-footer">
            <div class="gmb-flex-between">
                <div>
                    <strong><?php esc_html_e('Search Console Engine', 'gmb-ranker-seo-automation'); ?></strong> &bull; <?php esc_html_e('Connects directly via Google Service Account (OAuth2/JWT) & GMB Ranker API • Cached locally via transients.', 'gmb-ranker-seo-automation'); ?>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary"><?php esc_html_e('Back to Dashboard', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        </div>

    </div>
</div>
