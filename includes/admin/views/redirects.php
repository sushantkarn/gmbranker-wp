<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-redirects') : ?>
                <?php 
                $redirects_mod_val = get_option('gmb_ranker_module_redirects', '1');
                if ($redirects_mod_val === '0' || $redirects_mod_val === 'off') : 
                ?>
                    <div class="rm-tab-content active">
                        <div class="gmb-empty-state">
                            <h2 class="gmb-heading-2">Redirections Module is Disabled</h2>
                            <p class="gmb-text-muted">Enable the Redirections module to configure 301/302/307 redirect rules, 404 monitoring, and auto-fixes.</p>
                            <div class="gmb-flex-center-gap-md">
                                <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_redirects" >Enable Module</button>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-btn-header-action">Go to Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php else : 
                    $rules_count = is_array($redirects_rules) ? count($redirects_rules) : 0;
                    $active_rules_count = 0;
                    $total_hits_count = 0;
                    if (!empty($redirects_rules) && is_array($redirects_rules)) {
                        foreach ($redirects_rules as $r) {
                            if (!isset($r['status']) || $r['status'] === 'active') {
                                $active_rules_count++;
                            }
                            if (isset($r['hits'])) {
                                $total_hits_count += intval($r['hits']);
                            }
                        }
                    }
                    $logs_count = is_array($logs_404) ? count($logs_404) : 0;
                ?>
                    <div class="rm-tab-content active" id="rm-tab-redirects">
                        <div class="gmb-subtab-panel active gmb-redirect-container" id="gmb-subtab-redirects">
                        <div class="gmb-settings-panel-header">
                            <h2 class="gmb-heading-2">Redirections &amp; 404 Monitor</h2>
                            <p class="gmb-text-muted">Configure dynamic URL redirection rules, monitor 404 crawl detections, and configure fallback settings. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                        </div>
                        
                        <!-- Redirect Sub Navigation Buttons -->
                        <div class="gmb-redirect-subnav-bar">
                            <div class="gmb-redirect-subnav-group">
                                <button type="button" class="gmb-redirect-subnav active" data-sub="gmb-redirect-manage">
                                    <span>Manage Redirects</span>
                                    <span class="gmb-pill-badge gmb-pill-badge--blue"><?php echo intval($rules_count); ?></span>
                                </button>
                                <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-404">
                                    <span>404 Monitor</span>
                                    <span class="gmb-pill-badge gmb-pill-badge--red"><?php echo intval($logs_count); ?></span>
                                </button>
                                <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-settings">
                                    Settings
                                </button>
                                <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-import-export">
                                    Import &amp; Export
                                </button>
                            </div>
                            <div id="gmb-redirect-manage-top-actions">
                                <button type="button" id="gmb-toggle-add-form-btn" class="gmb-btn-add-redirect">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    Add Redirection
                                </button>
                            </div>
                        </div>

                        <!-- Add / Edit Redirection Form (Collapsible, starts hidden) -->
                        <div id="gmb-redirect-form-container">
                            <div class="gmb-redirect-form-header">
                                <h3 id="gmb-redirect-form-title" class="gmb-heading-3">Add New Redirection</h3>
                                <span id="gmb-redirect-edit-badge" class="gmb-redirect-edit-badge">Editing Rule</span>
                            </div>
                            
                            <input type="hidden" id="gmb-redirect-edit-id" value="" />

                            <div class="gmb-grid-2 gmb-mb-16">
                                <div class="gmb-form-group">
                                    <label class="gmb-form-label">Source URL (From)</label>
                                    <div class="gmb-flex-gap-sm">
                                        <input type="text" id="gmb-redirect-source" class="gmb-input gmb-flex-1" placeholder="e.g. /old-campaign-page" />
                                        <select id="gmb-redirect-match-type" class="gmb-select gmb-select-match">
                                            <option value="exact">Exact Match</option>
                                            <option value="contains">Contains Match</option>
                                            <option value="start">Starts With</option>
                                            <option value="end">Ends With</option>
                                            <option value="regex">Regex Match</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="gmb-form-group">
                                    <label class="gmb-form-label">Destination URL (To)</label>
                                    <input type="text" id="gmb-redirect-destination" class="gmb-input" placeholder="e.g. /new-campaign-page or https://..." />
                                </div>
                            </div>

                            <div class="gmb-redirect-grid-3col">
                                <div class="gmb-form-group">
                                    <label class="gmb-form-label">Redirection Code / Type</label>
                                    <select id="gmb-redirect-code" class="gmb-select">
                                        <option value="301">301 Permanent Move (Recommended)</option>
                                        <option value="302">302 Temporary Move</option>
                                        <option value="307">307 Temporary Redirect</option>
                                        <option value="410">410 Content Deleted</option>
                                        <option value="451">451 Unavailable for Legal Reasons</option>
                                    </select>
                                </div>
                                
                                <div class="gmb-form-group">
                                    <label class="gmb-form-label">Status</label>
                                    <select id="gmb-redirect-status" class="gmb-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="gmb-form-group">
                                    <label class="gmb-form-label">Note / Label (Optional)</label>
                                    <input type="text" id="gmb-redirect-note" class="gmb-input" placeholder="e.g. Broken backlink fix" />
                                </div>
                            </div>

                            <div class="gmb-redirect-actions-row">
                                <button type="button" id="gmb-cancel-add-btn" class="gmb-btn gmb-btn--secondary">Cancel</button>
                                <button type="button" id="gmb-add-rule-btn" class="gmb-btn gmb-btn--primary">Save Redirection</button>
                            </div>
                        </div>

                        <!-- Subtab Panel 1: Manage View -->
                        <div id="gmb-redirect-manage-view" class="gmb-redirect-view-panel active">
                            
                            <!-- Search & Filter Controls Toolbar -->
                            <div class="gmb-redirect-toolbar">
                                <div class="gmb-redirect-toolbar-left">
                                    <div class="gmb-bulk-actions-wrap">
                                        <select id="gmb-bulk-redirect-action" class="gmb-select gmb-bulk-select">
                                            <option value="">Bulk Actions</option>
                                            <option value="activate">Activate</option>
                                            <option value="deactivate">Deactivate</option>
                                            <option value="reset_hits">Reset Hits</option>
                                            <option value="delete">Delete Selected</option>
                                        </select>
                                        <button type="button" id="gmb-bulk-apply-btn" class="gmb-btn-apply">Apply</button>
                                    </div>
                                    <div class="gmb-filter-dropdowns">
                                        <select id="gmb-filter-redirect-code" class="gmb-select gmb-filter-select">
                                            <option value="all">All Status Codes</option>
                                            <option value="301">301 Permanent</option>
                                            <option value="302">302 Temporary</option>
                                            <option value="307">307 Redirect</option>
                                            <option value="410">410 Deleted</option>
                                            <option value="451">451 Legal</option>
                                        </select>
                                        <select id="gmb-filter-redirect-status" class="gmb-select gmb-filter-select">
                                            <option value="all">All Statuses</option>
                                            <option value="active">Active Only</option>
                                            <option value="inactive">Inactive Only</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="gmb-redirect-toolbar-right">
                                    <div class="gmb-search-input-wrapper">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="gmb-search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        <input type="text" id="gmb-redirect-search" class="gmb-search-input" placeholder="Search URLs or notes..." />
                                    </div>
                                </div>
                            </div>

                            <div id="gmb-rules-list-container" class="gmb-table-wrap">
                                <?php if (!empty($redirects_rules)) : ?>
                                    <table id="gmb-rules-table" class="gmb-data-table">
                                        <thead>
                                            <tr>
                                                <th class="gmb-th-checkbox">
                                                    <input type="checkbox" id="gmb-select-all-rules" />
                                                </th>
                                                <th class="gmb-th-source">Source URL (From)</th>
                                                <th class="gmb-th-dest">Destination (To)</th>
                                                <th class="gmb-text-center">Type</th>
                                                <th class="gmb-text-center">Hits</th>
                                                <th class="gmb-text-center">Status</th>
                                                <th class="gmb-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($redirects_rules as $rule) : 
                                                $r_id = isset($rule['id']) ? $rule['id'] : '';
                                                $r_src = isset($rule['source']) ? $rule['source'] : '';
                                                $r_dest = isset($rule['destination']) ? $rule['destination'] : '';
                                                $r_code = isset($rule['code']) ? intval($rule['code']) : 301;
                                                $r_hits = isset($rule['hits']) ? intval($rule['hits']) : 0;
                                                $r_status = isset($rule['status']) ? $rule['status'] : 'active';
                                                $r_match = isset($rule['match_type']) ? $rule['match_type'] : 'exact';
                                                $r_note = isset($rule['note']) ? $rule['note'] : '';
                                            ?>
                                                <tr class="gmb-rule-row" data-id="<?php echo esc_attr($r_id); ?>" data-code="<?php echo esc_attr($r_code); ?>" data-status="<?php echo esc_attr($r_status); ?>">
                                                    <td class="gmb-text-center">
                                                        <input type="checkbox" class="gmb-rule-checkbox" value="<?php echo esc_attr($r_id); ?>" />
                                                    </td>
                                                    <td class="gmb-td-source">
                                                        <span class="gmb-rule-source-text"><?php echo esc_html($r_src); ?></span>
                                                        <div class="gmb-td-meta-row">
                                                            <span class="gmb-badge-match">Match: <?php echo esc_html(ucfirst($r_match)); ?></span>
                                                            <?php if (!empty($r_note)) : ?>
                                                                <span class="gmb-rule-note-text gmb-text-muted gmb-text-xs gmb-italic">— <?php echo esc_html($r_note); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="gmb-td-dest">
                                                        <span class="gmb-rule-dest-text"><?php echo esc_html($r_dest); ?></span>
                                                    </td>
                                                    <td class="gmb-text-center">
                                                        <span class="gmb-status-code-badge gmb-status-code-badge--<?php echo esc_attr($r_code); ?>">
                                                            <?php echo esc_html($r_code); ?>
                                                        </span>
                                                    </td>
                                                    <td class="gmb-td-center">
                                                        <?php echo esc_html(number_format_i18n($r_hits)); ?>
                                                    </td>
                                                    <td class="gmb-text-center">
                                                        <button type="button" class="gmb-toggle-rule-status-btn gmb-toggle-rule-status-btn--<?php echo $r_status === 'active' ? 'active' : 'inactive'; ?>" data-id="<?php echo esc_attr($r_id); ?>">
                                                            <?php echo $r_status === 'active' ? 'Active' : 'Inactive'; ?>
                                                        </button>
                                                    </td>
                                                    <td class="gmb-td-actions">
                                                        <button type="button" class="gmb-edit-rule-btn gmb-table-action-btn gmb-table-action-btn--primary" data-id="<?php echo esc_attr($r_id); ?>" data-source="<?php echo esc_attr($r_src); ?>" data-dest="<?php echo esc_attr($r_dest); ?>" data-code="<?php echo esc_attr($r_code); ?>" data-match="<?php echo esc_attr($r_match); ?>" data-status="<?php echo esc_attr($r_status); ?>" data-note="<?php echo esc_attr($r_note); ?>">Edit</button>
                                                        <a href="<?php echo esc_url(site_url($r_src)); ?>" target="_blank" class="gmb-table-action-btn gmb-table-action-btn--muted">Test &rarr;</a>
                                                        <button type="button" class="gmb-delete-rule-btn gmb-table-action-btn gmb-table-action-btn--danger" data-id="<?php echo esc_attr($r_id); ?>">Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <div class="gmb-table-empty">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="gmb-table-empty-icon"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                        <p class="gmb-table-empty-title">No redirection rules defined yet.</p>
                                        <p class="gmb-table-empty-desc">Click "+ Add Redirection" above to create your first redirection rule.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Subtab Panel 2: 404 Monitor View -->
                        <div id="gmb-redirect-404-view" class="gmb-redirect-view-panel">
                            <div class="gmb-redirect-toolbar">
                                <div class="gmb-redirect-toolbar-left">
                                    <h3 class="gmb-heading-3">404 Crawl Detection Log</h3>
                                    <div class="gmb-search-input-wrapper">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="gmb-search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        <input type="text" id="gmb-404-search" class="gmb-search-input" placeholder="Filter 404 URLs..." />
                                    </div>
                                </div>
                                <div class="gmb-redirect-toolbar-right gmb-flex-gap-xs">
                                     <button type="button" id="gmb-ai-suggest-404-btn" class="gmb-btn-ai-404 gmb-btn--ai">
                                         <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                         <span>✨ AI Auto-Fix 404s</span>
                                     </button>
                                     <button type="button" id="gmb-clear-404-btn" class="gmb-btn-purge-404">
                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                         Purge All Logs
                                     </button>
                                </div>
                            </div>
                            
                            <div id="gmb-logs-list-container" class="gmb-table-wrap">
                                <?php if (!empty($logs_404)) : ?>
                                    <table id="gmb-404-logs-table" class="gmb-data-table">
                                        <thead>
                                            <tr>
                                                <th class="gmb-th-404-url">URL / Requested Path</th>
                                                <th class="gmb-th-404-ref">Referrer</th>
                                                <th class="gmb-text-center">Date/Time</th>
                                                <th class="gmb-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs_404 as $log) : 
                                                $log_uri = isset($log['uri']) ? $log['uri'] : '';
                                                $log_ref = !empty($log['referrer']) ? $log['referrer'] : 'Direct Access / None';
                                                $log_time = isset($log['time']) ? $log['time'] : time();
                                            ?>
                                                <tr class="gmb-404-log-row">
                                                    <td class="gmb-td-source">
                                                        <span class="gmb-badge-404">404</span>
                                                        <span class="gmb-404-uri-text"><?php echo esc_html($log_uri); ?></span>
                                                    </td>
                                                    <td class="gmb-td-dest">
                                                        <?php echo esc_html($log_ref); ?>
                                                    </td>
                                                    <td class="gmb-td-center">
                                                        <?php echo esc_html(human_time_diff($log_time, time()) . ' ago'); ?>
                                                    </td>
                                                    <td class="gmb-td-actions">
                                                        <button type="button" class="gmb-ai-single-suggest-btn gmb-btn-ai-sm" data-url="<?php echo esc_attr($log_uri); ?>">✨ AI Fix</button>
                                                        <button type="button" class="gmb-create-redirect-btn gmb-btn-create-redirect" data-url="<?php echo esc_attr($log_uri); ?>">Redirect &rarr;</button>
                                                        <button type="button" class="gmb-delete-single-404-btn gmb-table-action-btn gmb-table-action-btn--danger" data-url="<?php echo esc_attr($log_uri); ?>">Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <div class="gmb-table-empty">
                                        <p class="gmb-table-empty-title">404 log archive is currently clean.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Subtab Panel 3: Settings View -->
                        <div id="gmb-redirect-settings-view" class="gmb-redirect-view-panel gmb-panel-box">
                            <form method="post" action="options.php">
                                <?php settings_fields('gmb_ranker_redirects_group'); ?>
                                
                                <div class="gmb-card-settings-list">
                                    <!-- Option: Auto Post Redirect -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Auto Post Redirect
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_ranker_auto_post_redirect" value="on" <?php checked('on', get_option('gmb_ranker_auto_post_redirect', 'on')); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Automatically creates a 301 redirection whenever you change the slug or permalink of a published post, page, or custom post type.</p>
                                        </div>
                                    </div>

                                    <!-- Option: Fallback Behavior -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Fallback 404 Behavior
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_ranker_fallback_behavior" id="gmb_ranker_fallback_behavior" class="gmb-select-320">
                                                <option value="default" <?php selected('default', get_option('gmb_ranker_fallback_behavior', 'default')); ?>>Default 404 (Standard WordPress Error Page)</option>
                                                <option value="homepage" <?php selected('homepage', get_option('gmb_ranker_fallback_behavior', 'default')); ?>>Redirect to Homepage (302)</option>
                                                <option value="custom" <?php selected('custom', get_option('gmb_ranker_fallback_behavior', 'default')); ?>>Redirect to Custom URL (302)</option>
                                            </select>
                                            <p class="gmb-form-help">Action taken when a visitor or bot encounters an unhandled 404 error.</p>

                                            <div id="gmb-fallback-url-wrap" class="gmb-fallback-url-wrap gmb-mt-10 <?php echo get_option('gmb_ranker_fallback_behavior', 'default') === 'custom' ? 'is-active' : ''; ?>">
                                                <input type="url" name="gmb_ranker_fallback_url" value="<?php echo esc_attr(get_option('gmb_ranker_fallback_url', '')); ?>" placeholder="https://example.com/custom-404-landing" class="gmb-input-max-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Option: Redirect Attachments -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Redirect Media Attachments
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_redirect_attachments" value="on" <?php checked('on', get_option('gmb_redirect_attachments', 'on')); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Redirect media attachment URLs directly to the parent post they are attached to, preventing thin content penalty.</p>
                                        </div>
                                    </div>

                                    <!-- Option: Strip Category Base -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Strip Category Base
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_strip_category_base" value="on" <?php checked('on', get_option('gmb_strip_category_base', 'off')); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Remove <code>/category/</code> prefix from all category archive URLs (e.g. <code>example.com/category/news/</code> &rarr; <code>example.com/news/</code>).</p>
                                        </div>
                                    </div>

                                    <!-- Option: 404 Log Max Limit -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            404 Log Retention Limit
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="number" name="gmb_ranker_404_limit" min="10" max="5000" step="50" value="<?php echo esc_attr(get_option('gmb_ranker_404_limit', '100')); ?>" class="gmb-input-140" />
                                            <p class="gmb-form-help">Maximum number of 404 crawl detections to retain in the database before auto-pruning.</p>
                                        </div>
                                    </div>

                                    <!-- Option: Ignore Query Parameters in 404 -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Ignore 404 Query Strings
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_ranker_404_ignore_query" value="on" <?php checked('on', get_option('gmb_ranker_404_ignore_query', 'off')); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Group and clean query strings (e.g. <code>?utm_source=...</code>) from 404 log entries.</p>
                                        </div>
                                    </div>

                                    <!-- Option: Exclude Paths from 404 Logging -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Exclude Paths from 404 Log
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_ranker_404_exclude_paths" placeholder=".php, .env, wp-login, autodiscover, xmlrpc" class="gmb-textarea-max-480"><?php echo esc_textarea(get_option('gmb_ranker_404_exclude_paths', '')); ?></textarea>
                                            <p class="gmb-form-help">Comma-separated keywords or extensions to exclude from 404 monitoring (e.g. common bot probing files).</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Settings" />
                                </div>
                            </form>
                        </div>

                        <!-- Subtab Panel 4: Import & Export View -->
                        <div id="gmb-redirect-import-export-view" class="gmb-redirect-view-panel gmb-panel-box">
                            <div class="gmb-grid-2col-24">
                                
                                <!-- Export Box -->
                                <div class="gmb-card">
                                    <h3 class="gmb-heading-3">Export Redirection Rules</h3>
                                    <p class="gmb-text-muted">Download all configured redirection rules as a backup or for migration to other sites.</p>
                                    <div class="gmb-flex-gap-sm gmb-mt-16">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-redirects&gmb_action=export_redirects_json')); ?>" class="button gmb-btn-action-outline">
                                            Export JSON
                                        </a>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-redirects&gmb_action=export_redirects_csv')); ?>" class="button gmb-btn-action-outline">
                                            Export CSV
                                        </a>
                                    </div>
                                </div>

                                <!-- Bulk Paste Tool -->
                                <div class="gmb-card">
                                    <h3 class="gmb-heading-3">Bulk Paste Redirections</h3>
                                    <p class="gmb-text-muted">Paste one rule per line in format: <code>/from-url /to-url [301]</code></p>
                                    
                                    <textarea id="gmb-bulk-import-textarea" placeholder="/old-summer-sale /sale 301&#10;/old-blog-post /blog/new-post 301" class="gmb-textarea-paste"></textarea>
                                    
                                    <div class="gmb-flex-between">
                                        <select id="gmb-bulk-import-match" class="gmb-select gmb-select-match">
                                            <option value="exact">Exact Match</option>
                                            <option value="contains">Contains Match</option>
                                        </select>
                                        <button type="button" id="gmb-bulk-import-submit-btn" class="gmb-btn-action-primary">
                                            Import Rules
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Auto-Fix Redirection Modal -->
                <div id="gmb-ai-redirect-modal" class="gmb-modal-overlay">
                    <div class="gmb-modal-container gmb-modal-lg">
                        <div class="gmb-modal-header">
                            <h3 class="gmb-modal-title">✨ AI Auto-Fix 404 Redirections</h3>
                            <button type="button" class="gmb-modal-close" id="gmb-ai-modal-close">&times;</button>
                        </div>
                        <div class="gmb-modal-body">
                            <div id="gmb-ai-modal-loading" class="gmb-ai-loading-box">
                                <div class="gmb-spinner"></div>
                                <p class="gmb-ai-loading-text">Analyzing 404 logs against your website's published content with AI...</p>
                            </div>
                            <div id="gmb-ai-modal-content" class="gmb-hidden">
                                <p class="gmb-text-muted gmb-mb-12">Below are the AI-recommended redirection targets mapped to your live site pages. Uncheck any rules you do not wish to apply.</p>
                                <div class="gmb-table-wrap gmb-max-h-400">
                                    <table class="gmb-data-table gmb-table-compact">
                                        <thead>
                                            <tr>
                                                <th class="gmb-th-checkbox"><input type="checkbox" id="gmb-ai-select-all" checked /></th>
                                                <th>404 Source Path</th>
                                                <th>AI Suggested Destination</th>
                                                <th>Code</th>
                                                <th>Confidence</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gmb-ai-suggestions-tbody">
                                            <!-- Dynamically populated via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="gmb-modal-footer">
                            <button type="button" class="button gmb-btn-secondary" id="gmb-ai-modal-cancel">Cancel</button>
                            <button type="button" class="button button-primary gmb-btn--primary" id="gmb-ai-apply-btn" disabled>Batch Apply AI Rules</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

            <!-- Page: Instant Indexing -->
