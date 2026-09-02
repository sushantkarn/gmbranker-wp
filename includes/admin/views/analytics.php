<?php
/**
 * Analytics & Performance Dedicated View
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$analytics_engine = class_exists('GMB_Ranker_SEO_Analytics') ? GMB_Ranker_SEO_Analytics::get_instance() : null;
$analytics_data   = $analytics_engine ? $analytics_engine->get_analytics_data() : array();
$totals           = isset($analytics_data['totals']) ? $analytics_data['totals'] : array();
$top_queries      = isset($analytics_data['top_queries']) ? $analytics_data['top_queries'] : array();
$top_pages        = isset($analytics_data['top_pages']) ? $analytics_data['top_pages'] : array();
$is_connected     = isset($analytics_data['status']) && $analytics_data['status'] === 'connected';
$source           = isset($analytics_data['source']) ? $analytics_data['source'] : 'preview';
$property         = isset($analytics_data['property']) ? $analytics_data['property'] : home_url('/');
$sparkline        = isset($analytics_data['sparkline']) ? $analytics_data['sparkline'] : array();
$google_json_key  = get_option('gmb_ranker_google_json_key', '');
$has_real_data    = (!empty($totals['clicks']) || !empty($totals['impressions']) || !empty($top_queries) || !empty($top_pages));

// Generate dynamic SVG sparkline trajectory coordinates
$clicks_arr = (!empty($sparkline['clicks']) && is_array($sparkline['clicks'])) ? $sparkline['clicks'] : array_fill(0, 28, 0);
$imp_arr    = (!empty($sparkline['impressions']) && is_array($sparkline['impressions'])) ? $sparkline['impressions'] : array_fill(0, 28, 0);

$c_count = count($clicks_arr);
$max_c   = max(1, max($clicks_arr));
$max_imp = max(1, max($imp_arr));

$c_pts   = array();
$imp_pts = array();
for ($i = 0; $i < $c_count; $i++) {
    $x       = round(($i / max(1, $c_count - 1)) * 800, 1);
    $y_c     = $has_real_data ? round(110 - (($clicks_arr[$i] / $max_c) * 85), 1) : 110;
    $y_imp   = $has_real_data ? round(110 - ((isset($imp_arr[$i]) ? $imp_arr[$i] : 0) / $max_imp * 85), 1) : 110;
    $c_pts[]   = "$x $y_c";
    $imp_pts[] = "$x $y_imp";
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <div>
                        <h2 class="gmb-analytics-title">Search Console &amp; Google Analytics Performance</h2>
                        <p class="gmb-text-muted gmb-text-xs">Real-time organic impressions, click-through rates, and Google ranking positions via Google Search Console API &amp; GMB Ranker Cloud.</p>
                    </div>
                </div>
            </div>
            
            <div class="gmb-flex-center-gap-sm">
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-secondary gmb-btn gmb-btn-secondary" title="Configure Google Service Account">
                    Google Service Account &rarr;
                </a>
                <button type="button" class="gmb-analytics-sync-btn" id="gmb-sync-analytics-btn" onclick="gmbSyncAnalytics()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    <span id="gmb-sync-btn-label">Sync Live Data</span>
                </button>
            </div>
        </div>

        <?php if (empty($google_json_key)) : ?>
            <div class="gmb-analytics-connect-banner">
                <div>
                    <strong class="gmb-analytics-connect-banner-title">Connect Google Search Console Service Account</strong>
                    <p class="gmb-analytics-connect-banner-desc">Add your Google Service Account JSON key in Instant Indexing / Google Settings to start pulling live ranking metrics, search queries, and impression trajectories.</p>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-primary gmb-analytics-connect-banner-btn">Configure Google Key &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4 KPI Summary Cards -->
        <div class="gmb-analytics-kpi-grid">
            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span>Total Clicks</span>
                    <?php if ($has_real_data && !empty($totals['clicks_diff']) && $totals['clicks_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['clicks_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-clicks">
                    <?php echo isset($totals['clicks']) ? number_format_i18n((int)$totals['clicks']) : '0'; ?>
                </div>
                <div class="gmb-kpi-subtext">Past 28 Days (Organic Search)</div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span>Total Impressions</span>
                    <?php if ($has_real_data && !empty($totals['imp_diff']) && $totals['imp_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['imp_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-impressions">
                    <?php echo isset($totals['impressions']) ? number_format_i18n((int)$totals['impressions']) : '0'; ?>
                </div>
                <div class="gmb-kpi-subtext">Google Search Visibility</div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span>Average CTR</span>
                    <?php if ($has_real_data && !empty($totals['ctr_diff']) && $totals['ctr_diff'] !== '0%') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['ctr_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-ctr">
                    <?php echo (isset($totals['ctr']) && (float)$totals['ctr'] > 0) ? esc_html($totals['ctr']) . '%' : '0.0%'; ?>
                </div>
                <div class="gmb-kpi-subtext">Click-Through Rate</div>
            </div>

            <div class="gmb-kpi-card">
                <div class="gmb-kpi-label">
                    <span>Average Position</span>
                    <?php if ($has_real_data && !empty($totals['pos_diff']) && $totals['pos_diff'] !== '0') : ?>
                        <span class="gmb-kpi-badge gmb-badge-up"><?php echo esc_html($totals['pos_diff']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="gmb-kpi-value" id="gmb-kpi-pos">
                    <?php echo (isset($totals['position']) && (float)$totals['position'] > 0) ? esc_html($totals['position']) : '—'; ?>
                </div>
                <div class="gmb-kpi-subtext">Average Search Ranking</div>
            </div>
        </div>

        <!-- 28-Day Trajectory Sparkline Graph -->
        <div class="gmb-analytics-graph-card">
            <div class="gmb-graph-header">
                <h3 class="gmb-graph-title">28-Day Search Performance Trajectory</h3>
                <div class="gmb-graph-legend">
                    <span class="legend-dot clicks"></span> Daily Clicks
                    <span class="legend-dot impressions gmb-legend-dot-margin"></span> Daily Impressions
                </div>
            </div>
            <div class="gmb-graph-svg-wrap">
                <svg class="gmb-sparkline-svg" viewBox="0 0 800 120" preserveAspectRatio="none">
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
                    <!-- Grid background lines -->
                    <line x1="0" y1="30" x2="800" y2="30" stroke="#f1f5f9" stroke-width="1" />
                    <line x1="0" y1="60" x2="800" y2="60" stroke="#f1f5f9" stroke-width="1" />
                    <line x1="0" y1="90" x2="800" y2="90" stroke="#f1f5f9" stroke-width="1" />

                    <!-- Dynamic Impressions Area & Path -->
                    <path d="<?php echo esc_attr($imp_fill_d); ?>" fill="url(#impGrad)" />
                    <path d="<?php echo esc_attr($imp_path_d); ?>" fill="none" stroke="#a855f7" stroke-width="2.5" stroke-linecap="round" />

                    <!-- Dynamic Clicks Area & Path -->
                    <path d="<?php echo esc_attr($c_fill_d); ?>" fill="url(#clicksGrad)" />
                    <path d="<?php echo esc_attr($c_path_d); ?>" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
        </div>

        <!-- 2 Data Tables: Top Search Queries & Top Landing Pages -->
        <div class="gmb-analytics-tables-grid">
            <div class="gmb-table-card">
                <div class="gmb-table-header">
                    <h3 class="gmb-table-title">Top Ranking Search Queries</h3>
                    <span class="gmb-table-subtitle">Search Console Queries</span>
                </div>
                <table class="gmb-analytics-table">
                    <thead>
                        <tr>
                            <th>Keyword / Query</th>
                            <th class="gmb-text-right">Clicks</th>
                            <th class="gmb-text-right">Imp.</th>
                            <th class="gmb-text-right">Pos</th>
                        </tr>
                    </thead>
                    <tbody id="gmb-tbody-queries">
                        <?php if (!empty($top_queries)) : ?>
                            <?php foreach ($top_queries as $q) : ?>
                                <?php
                                $pos = isset($q['position']) ? (float)$q['position'] : 10.0;
                                $badge_cls = $pos <= 3.0 ? 'pos-top3' : ($pos <= 10.0 ? 'pos-top10' : 'pos-standard');
                                ?>
                                <tr>
                                    <td class="gmb-query-cell"><strong><?php echo esc_html($q['query']); ?></strong></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n($q['clicks']); ?></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n($q['impressions']); ?></td>
                                    <td class="gmb-text-right">
                                        <span class="gmb-pos-badge <?php echo esc_attr($badge_cls); ?>"><?php echo esc_html(number_format($pos, 1)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="gmb-analytics-empty-cell">
                                    No search queries recorded yet. Connect your Google Service Account in Instant Indexing &rarr; Google Settings to sync live queries from Google Search Console.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="gmb-table-card">
                <div class="gmb-table-header">
                    <h3 class="gmb-table-title">Top Landing Pages</h3>
                    <span class="gmb-table-subtitle">Organic URLs</span>
                </div>
                <table class="gmb-analytics-table">
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th class="gmb-text-right">Clicks</th>
                            <th class="gmb-text-right">Pos</th>
                        </tr>
                    </thead>
                    <tbody id="gmb-tbody-pages">
                        <?php if (!empty($top_pages)) : ?>
                            <?php foreach ($top_pages as $p) : ?>
                                <?php
                                $page_url = isset($p['page']) ? $p['page'] : (isset($p['url']) ? $p['url'] : '/');
                                $pos = isset($p['position']) ? (float)$p['position'] : 10.0;
                                $badge_cls = $pos <= 3.0 ? 'pos-top3' : ($pos <= 10.0 ? 'pos-top10' : 'pos-standard');
                                ?>
                                <tr>
                                    <td class="gmb-page-cell"><code><?php echo esc_html($page_url); ?></code></td>
                                    <td class="gmb-text-right"><?php echo number_format_i18n(isset($p['clicks']) ? $p['clicks'] : 0); ?></td>
                                    <td class="gmb-text-right">
                                        <span class="gmb-pos-badge <?php echo esc_attr($badge_cls); ?>"><?php echo esc_html(number_format($pos, 1)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="gmb-analytics-empty-cell">
                                    No organic landing page data recorded yet.
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
                    <strong>Search Console Engine</strong> &bull; Connects directly via Google Service Account (OAuth2/JWT) &amp; GMB Ranker API &bull; Cached locally via transients.
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>

    </div>
</div>
