<?php
/**
 * Single Post AI SEO Auto-Fix Modal Presentation Template
 *
 * Rendered in admin_footer on post editing screens. Provides a data-driven,
 * accessible, 3-step AI SEO Master Strategist modal interface.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;
$post_id        = ($post && isset($post->ID)) ? $post->ID : 0;
$focus_keyword  = $post_id ? get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) : '';
$market_groups  = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_search_market_options() : array();
$lang_options   = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_language_options() : array();
$site_locale    = function_exists('get_locale') ? substr(get_locale(), 0, 2) : 'en';
?>
<!-- Single Page AI SEO Optimizer Modal (NeuronWriter-Style 3-Step Flow) -->
<div id="gmb-ai-post-seo-modal" class="gmb-modal-overlay" role="dialog" aria-labelledby="gmb-ai-modal-title" aria-hidden="true">
    <div class="gmb-modal-container gmb-modal-lg">
        <div class="gmb-modal-header">
            <div class="gmb-modal-header-flex">
                <div>
                    <h3 class="gmb-modal-title" id="gmb-ai-modal-title"><?php esc_html_e('AI SEO Master Strategist', 'gmb-ranker-seo-automation'); ?></h3>
                    <p class="gmb-modal-subtitle"><?php esc_html_e('Data-driven SEO research. Real insights. Better rankings.', 'gmb-ranker-seo-automation'); ?></p>
                </div>
                <div class="gmb-modal-stepper">
                    <span class="gmb-step-badge active" id="gmb-step-badge-1"><?php esc_html_e('1. Query Setup', 'gmb-ranker-seo-automation'); ?></span>
                    <span class="gmb-step-arrow">&rarr;</span>
                    <span class="gmb-step-badge" id="gmb-step-badge-2"><?php esc_html_e('2. AI Research', 'gmb-ranker-seo-automation'); ?></span>
                    <span class="gmb-step-arrow">&rarr;</span>
                    <span class="gmb-step-badge" id="gmb-step-badge-3"><?php esc_html_e('3. Optimization', 'gmb-ranker-seo-automation'); ?></span>
                </div>
            </div>
            <button type="button" class="gmb-modal-close" id="gmb-ai-post-modal-close" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
        </div>
        <div class="gmb-modal-body">
            <!-- STEP 1: Query Setup & Target Region Selection -->
            <div id="gmb-ai-post-modal-setup" class="gmb-ai-setup-card">
                <div class="gmb-setup-form-grid">
                    <div class="gmb-form-group gmb-col-12">
                        <label class="gmb-form-label" for="gmb-ai-setup-url"><?php esc_html_e('Target URL / Permalink', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="text" id="gmb-ai-setup-url" class="gmb-integration-input" readonly />
                    </div>
                    <div class="gmb-form-group gmb-col-12">
                        <label class="gmb-form-label" for="gmb-ai-setup-title"><?php esc_html_e('Article Title', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="text" id="gmb-ai-setup-title" class="gmb-integration-input" placeholder="<?php esc_attr_e('Enter target article title...', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                    <div class="gmb-form-group gmb-col-12">
                        <label class="gmb-form-label" for="gmb-ai-setup-query"><?php esc_html_e('What query do you want to rank for? (Target Focus Keyword)', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="text" id="gmb-ai-setup-query" class="gmb-integration-input gmb-input-lg" value="<?php echo esc_attr($focus_keyword); ?>" placeholder="<?php esc_attr_e('e.g. Best SEO Strategies...', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                    <div class="gmb-form-group gmb-col-4">
                        <label class="gmb-form-label" for="gmb-ai-setup-mode"><?php esc_html_e('Mode', 'gmb-ranker-seo-automation'); ?></label>
                        <select id="gmb-ai-setup-mode" class="gmb-integration-select">
                            <option value="optimize" selected><?php esc_html_e('⚡ Optimize (Improve existing content)', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="create"><?php esc_html_e('✍️ Create new (Start from keyword)', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="deep_serp"><?php esc_html_e('🎯 Deep SERP Entity Benchmark', 'gmb-ranker-seo-automation'); ?></option>
                        </select>
                    </div>
                    <div class="gmb-form-group gmb-col-4">
                        <label class="gmb-form-label" for="gmb-ai-setup-country"><?php esc_html_e('Target Search Engine / Country', 'gmb-ranker-seo-automation'); ?></label>
                        <select id="gmb-ai-setup-country" class="gmb-integration-select">
                            <?php foreach ($market_groups as $grp_key => $options) : 
                                $grp_label = ucfirst(str_replace('_', ' ', $grp_key));
                            ?>
                                <optgroup label="<?php echo esc_attr($grp_label); ?>">
                                    <?php foreach ($options as $val => $lbl) : ?>
                                        <option value="<?php echo esc_attr($val); ?>" <?php selected($val, 'GLOBAL|google.com'); ?>><?php echo esc_html($lbl); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="gmb-form-group gmb-col-4">
                        <label class="gmb-form-label" for="gmb-ai-setup-language"><?php esc_html_e('Language', 'gmb-ranker-seo-automation'); ?></label>
                        <select id="gmb-ai-setup-language" class="gmb-integration-select">
                            <?php foreach ($lang_options as $lang_code => $lang_lbl) : ?>
                                <option value="<?php echo esc_attr($lang_code); ?>" <?php selected($lang_code, $site_locale); ?>><?php echo esc_html($lang_lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 2: 3-Column AI Research Workspace -->
            <div id="gmb-ai-post-modal-loading" class="gmb-ai-research-dashboard gmb-hidden">
                <!-- Top Live SERP Status Bar -->
                <div class="gmb-serp-status-bar gmb-mb-16">
                    <div class="gmb-serp-status-left">
                        <svg class="gmb-google-svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <strong class="gmb-serp-title"><?php esc_html_e('Real SERP Data', 'gmb-ranker-seo-automation'); ?></strong>
                        <span class="gmb-serp-divider">|</span>
                        <span class="gmb-serp-label"><?php esc_html_e('Fetching live results for:', 'gmb-ranker-seo-automation'); ?></span>
                        <span class="gmb-serp-kw-pill" id="gmb-serp-kw-pill"><?php esc_html_e('Target Query', 'gmb-ranker-seo-automation'); ?></span>
                    </div>
                </div>

                <!-- 3-Column Research Workspace Grid -->
                <div class="gmb-research-3col-grid">
                    <!-- COLUMN 1: Research Steps Timeline -->
                    <div class="gmb-research-col-steps">
                        <h4 class="gmb-research-col-heading"><?php esc_html_e('Research Steps', 'gmb-ranker-seo-automation'); ?></h4>
                        <div class="gmb-steps-timeline">
                            <div class="gmb-step-item active" id="gmb-res-step-1">
                                <div class="gmb-step-num-icon">1</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Analyzing Current Page', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Extracting content, metadata & SEO signals', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <div class="gmb-step-active-ring"></div>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-2">
                                <div class="gmb-step-num-icon">2</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Detecting Search Intent', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Analyzing query intent & SERP features', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-3">
                                <div class="gmb-step-num-icon">3</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Fetching SERP Results', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Collecting top ranking pages', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-4">
                                <div class="gmb-step-num-icon">4</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Analyzing Competitors', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Extracting content & SEO data', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-5">
                                <div class="gmb-step-num-icon">5</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Semantic & Entity Analysis', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Building topic and entity model', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-6">
                                <div class="gmb-step-num-icon">6</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Content Gap Analysis', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Identifying missing opportunities', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-7">
                                <div class="gmb-step-num-icon">7</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Optimization Strategy', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Generating evidence-based recommendations', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-step-item" id="gmb-res-step-8">
                                <div class="gmb-step-num-icon">8</div>
                                <div class="gmb-step-text">
                                    <strong><?php esc_html_e('Finalizing Results', 'gmb-ranker-seo-automation'); ?></strong>
                                    <small><?php esc_html_e('Validating data & preparing your report', 'gmb-ranker-seo-automation'); ?></small>
                                </div>
                                <span class="gmb-step-status-pill pending"><?php esc_html_e('Pending', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMN 2: Active Step Execution Panel -->
                    <div class="gmb-research-col-center">
                        <div class="gmb-active-step-card">
                            <span class="gmb-step-counter-badge" id="gmb-active-step-counter"><?php esc_html_e('Step 1 of 8', 'gmb-ranker-seo-automation'); ?></span>
                            <h3 class="gmb-active-step-title" id="gmb-active-step-title"><?php esc_html_e('Analyzing Current Page Structure & Metadata', 'gmb-ranker-seo-automation'); ?></h3>
                            <p class="gmb-active-step-desc" id="gmb-active-step-desc"><?php esc_html_e('We\'re extracting and evaluating key elements from your WordPress post.', 'gmb-ranker-seo-automation'); ?></p>

                            <!-- Progress Bar -->
                            <div class="gmb-progress-bar-wrap">
                                <div class="gmb-progress-bar-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                    <div class="gmb-progress-bar-fill" id="gmb-active-progress-fill" style="width: 0%;"></div>
                                </div>
                                <span class="gmb-progress-percent" id="gmb-active-progress-percent">0%</span>
                            </div>

                            <!-- Live Dynamic Tasks List -->
                            <div class="gmb-live-tasks-list" id="gmb-live-tasks-list">
                                <div class="gmb-task-row done"><span class="task-check-circle">✓</span> <?php esc_html_e('Post content loaded', 'gmb-ranker-seo-automation'); ?> (<span id="gmb-task-word-count">--</span> <?php esc_html_e('words', 'gmb-ranker-seo-automation'); ?>)</div>
                                <div class="gmb-task-row done"><span class="task-check-circle">✓</span> <?php esc_html_e('Extracting SEO metadata (title, description, schema)', 'gmb-ranker-seo-automation'); ?></div>
                                <div class="gmb-task-row done"><span class="task-check-circle">✓</span> <?php esc_html_e('Analyzing headings structure (H1-H3)', 'gmb-ranker-seo-automation'); ?></div>
                                <div class="gmb-task-row running"><span class="task-spinner"></span> <?php esc_html_e('Scanning images and alt text...', 'gmb-ranker-seo-automation'); ?></div>
                                <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> <?php esc_html_e('Analyzing internal and external links', 'gmb-ranker-seo-automation'); ?></div>
                                <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> <?php esc_html_e('Calculating readability metrics', 'gmb-ranker-seo-automation'); ?></div>
                                <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> <?php esc_html_e('Detecting semantic terms and entities', 'gmb-ranker-seo-automation'); ?></div>
                            </div>
                        </div>

                    </div>

                    <!-- COLUMN 3: Overview -->
                    <div class="gmb-research-col-right">
                        <!-- Research Overview Card -->
                        <div class="gmb-side-card">
                            <h4 class="gmb-side-card-title">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
                                </svg>
                                <?php esc_html_e('Research Overview', 'gmb-ranker-seo-automation'); ?>
                            </h4>
                            <div class="gmb-overview-kv-list">
                                <div class="gmb-kv-row">
                                    <span class="kv-key"><?php esc_html_e('Target Query', 'gmb-ranker-seo-automation'); ?></span>
                                    <strong class="kv-val" id="gmb-overview-query"><?php echo esc_html($focus_keyword ?: '--'); ?></strong>
                                </div>
                                <div class="gmb-kv-row">
                                    <span class="kv-key"><?php esc_html_e('Target URL', 'gmb-ranker-seo-automation'); ?></span>
                                    <a href="<?php echo esc_url($post_id ? get_permalink($post_id) : home_url('/')); ?>" target="_blank" class="kv-val kv-link" id="gmb-overview-url">
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        <?php echo esc_html(home_url('/...')); ?>
                                    </a>
                                </div>
                                <div class="gmb-kv-row">
                                    <span class="kv-key"><?php esc_html_e('Country / Search Engine', 'gmb-ranker-seo-automation'); ?></span>
                                    <strong class="kv-val" id="gmb-overview-country"><?php esc_html_e('Global (google.com)', 'gmb-ranker-seo-automation'); ?></strong>
                                </div>
                                <div class="gmb-kv-row">
                                    <span class="kv-key"><?php esc_html_e('Language', 'gmb-ranker-seo-automation'); ?></span>
                                    <strong class="kv-val" id="gmb-overview-language"><?php esc_html_e('English', 'gmb-ranker-seo-automation'); ?></strong>
                                </div>
                                <div class="gmb-kv-row">
                                    <span class="kv-key"><?php esc_html_e('Mode', 'gmb-ranker-seo-automation'); ?></span>
                                    <strong class="kv-val" id="gmb-overview-mode"><?php esc_html_e('Optimize Existing Content', 'gmb-ranker-seo-automation'); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- What We'll Analyze Card -->
                        <div class="gmb-side-card gmb-mt-16">
                            <h4 class="gmb-side-card-title">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><path d="M16.2 7.8l-2 5.6-5.6 2 2-5.6z"/>
                                </svg>
                                <?php esc_html_e('What We\'ll Analyze', 'gmb-ranker-seo-automation'); ?>
                            </h4>
                            <ul class="gmb-bullets-checklist">
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Current page SEO health', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('SERP competitors & rank patterns', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Semantic terms & entity coverage', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Content gaps & missing topics', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Search intent alignment', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Readability & content structure', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Internal link opportunities', 'gmb-ranker-seo-automation'); ?></li>
                                <li><span class="chk-blue">✓</span> <?php esc_html_e('Evidence-based recommendations', 'gmb-ranker-seo-automation'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Recommendations & Controlled Apply Screen -->
            <div id="gmb-ai-post-modal-content" class="gmb-hidden">
                <div class="gmb-results-score-banner">
                    <div class="gmb-score-meta">
                        <div class="gmb-target-query-box">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#166534" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <strong><?php esc_html_e('Target Query:', 'gmb-ranker-seo-automation'); ?></strong> <span id="gmb-ai-result-query-label">--</span>
                        </div>
                    </div>
                    <div class="gmb-score-chips-group">
                        <div class="gmb-score-chip potential-score">
                            <?php esc_html_e('Optimization Potential:', 'gmb-ranker-seo-automation'); ?> <strong id="gmb-ai-potential-score">-- / 100</strong>
                        </div>
                    </div>
                </div>

                <div class="gmb-table-wrap gmb-ai-table-scroll">
                    <table class="gmb-data-table gmb-table-compact">
                        <thead>
                            <tr>
                                <th class="gmb-th-checkbox"><input type="checkbox" id="gmb-ai-post-select-all" disabled /></th>
                                <th style="width: 180px;"><?php esc_html_e('SEO Factor', 'gmb-ranker-seo-automation'); ?></th>
                                <th><?php esc_html_e('AI Recommended Optimization', 'gmb-ranker-seo-automation'); ?></th>
                                <th style="width: 130px;"><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="gmb-ai-post-suggestions-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="gmb-modal-footer">
            <button type="button" class="button gmb-btn-secondary" id="gmb-ai-post-modal-cancel"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
            <button type="button" class="button gmb-btn-secondary gmb-hidden" id="gmb-ai-post-modal-prev" style="display: none !important;"><?php esc_html_e('Previous', 'gmb-ranker-seo-automation'); ?></button>
            <button type="button" class="button button-primary gmb-btn--primary" id="gmb-ai-setup-start-btn"><?php esc_html_e('Start AI Analysis', 'gmb-ranker-seo-automation'); ?></button>
            <button type="button" class="button button-primary gmb-btn--primary gmb-hidden" id="gmb-ai-running-btn" disabled style="display: none !important;"><span class="task-spinner"></span> <?php esc_html_e('Running Analysis...', 'gmb-ranker-seo-automation'); ?></button>
            <button type="button" class="button button-primary gmb-btn--primary gmb-hidden" id="gmb-ai-post-apply-btn" disabled style="display: none !important;"><?php esc_html_e('Apply Selected AI Optimizations', 'gmb-ranker-seo-automation'); ?></button>
        </div>
    </div>
</div>
