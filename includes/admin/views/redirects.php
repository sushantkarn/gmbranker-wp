<?php
/**
 * Redirections & 404 Monitor Admin View
 *
 * 100% Thin presentation view layer consuming GMB_Ranker_SEO_Redirect_Registry.
 * Contains zero database queries, business logic, or state calculations.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if ($current_page === 'gmb-ranker-redirects') :
    $vm = class_exists('GMB_Ranker_SEO_Redirect_Registry') ? GMB_Ranker_SEO_Redirect_Registry::get_view_model() : array();
    $module_enabled     = isset($vm['module_enabled']) ? $vm['module_enabled'] : true;
    $settings           = isset($vm['settings']) ? $vm['settings'] : array();
    $rules              = isset($vm['rules']) ? $vm['rules'] : array();
    $rules_count        = isset($vm['rules_count']) ? $vm['rules_count'] : 0;
    $active_rules_count = isset($vm['active_rules_count']) ? $vm['active_rules_count'] : 0;
    $logs_404           = isset($vm['logs_404']) ? $vm['logs_404'] : array();
    $logs_count         = isset($vm['logs_count']) ? $vm['logs_count'] : 0;
    $redirect_codes     = isset($vm['redirect_codes']) ? $vm['redirect_codes'] : array();
    $match_types        = isset($vm['match_types']) ? $vm['match_types'] : array();

    if (!$module_enabled) :
?>
        <div class="rm-tab-content active">
            <div class="gmb-empty-state">
                <h2 class="gmb-heading-2"><?php esc_html_e('Redirections Module is Disabled', 'gmb-ranker-seo-automation'); ?></h2>
                <p class="gmb-text-muted"><?php esc_html_e('Enable the Redirections module to configure 301/302/307 redirect rules, 404 monitoring, and auto-fixes.', 'gmb-ranker-seo-automation'); ?></p>
                <div class="gmb-flex-center-gap-md">
                    <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_redirects"><?php esc_html_e('Enable Module', 'gmb-ranker-seo-automation'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-btn-header-action"><?php esc_html_e('Go to Dashboard', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="rm-tab-content active" id="rm-tab-redirects">
            <div class="gmb-subtab-panel active gmb-redirect-container" id="gmb-subtab-redirects">
                <div class="gmb-settings-panel-header">
                    <h2 class="gmb-heading-2"><?php esc_html_e('Redirections & 404 Monitor', 'gmb-ranker-seo-automation'); ?></h2>
                    <p class="gmb-text-muted">
                        <?php esc_html_e('Configure dynamic URL redirection rules, monitor 404 crawl detections, and configure fallback settings.', 'gmb-ranker-seo-automation'); ?>
                        <a href="https://gmbranker.org/" target="_blank" rel="noopener noreferrer" class="gmb-help-link"><?php esc_html_e('Learn more', 'gmb-ranker-seo-automation'); ?></a>.
                    </p>
                </div>
                
                <!-- Redirect Sub Navigation Buttons -->
                <div class="gmb-redirect-subnav-bar" role="tablist" aria-label="<?php esc_attr_e('Redirection Navigation', 'gmb-ranker-seo-automation'); ?>">
                    <div class="gmb-redirect-subnav-group">
                        <button type="button" class="gmb-redirect-subnav active" data-sub="gmb-redirect-manage" role="tab" aria-selected="true">
                            <span><?php esc_html_e('Manage Redirects', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-pill-badge gmb-pill-badge--blue"><?php echo intval($rules_count); ?></span>
                        </button>
                        <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-404" role="tab" aria-selected="false">
                            <span><?php esc_html_e('404 Monitor', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-pill-badge gmb-pill-badge--red"><?php echo intval($logs_count); ?></span>
                        </button>
                        <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-settings" role="tab" aria-selected="false">
                            <?php esc_html_e('Settings', 'gmb-ranker-seo-automation'); ?>
                        </button>
                        <button type="button" class="gmb-redirect-subnav" data-sub="gmb-redirect-import-export" role="tab" aria-selected="false">
                            <?php esc_html_e('Import & Export', 'gmb-ranker-seo-automation'); ?>
                        </button>
                    </div>
                    <div id="gmb-redirect-manage-top-actions">
                        <button type="button" id="gmb-toggle-add-form-btn" class="gmb-btn-add-redirect">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <?php esc_html_e('Add Redirection', 'gmb-ranker-seo-automation'); ?>
                        </button>
                    </div>
                </div>

                <!-- Add / Edit Redirection Form (Collapsible, starts hidden) -->
                <div id="gmb-redirect-form-container">
                    <div class="gmb-redirect-form-header">
                        <h3 id="gmb-redirect-form-title" class="gmb-heading-3"><?php esc_html_e('Add New Redirection', 'gmb-ranker-seo-automation'); ?></h3>
                        <span id="gmb-redirect-edit-badge" class="gmb-redirect-edit-badge"><?php esc_html_e('Editing Rule', 'gmb-ranker-seo-automation'); ?></span>
                    </div>
                    
                    <input type="hidden" id="gmb-redirect-edit-id" value="" />

                    <div class="gmb-grid-2 gmb-mb-16">
                        <div class="gmb-form-group">
                            <label for="gmb-redirect-source" class="gmb-form-label"><?php esc_html_e('Source URL (From)', 'gmb-ranker-seo-automation'); ?></label>
                            <div class="gmb-flex-gap-sm">
                                <input type="text" id="gmb-redirect-source" class="gmb-input gmb-flex-1" placeholder="<?php esc_attr_e('e.g. /old-campaign-page', 'gmb-ranker-seo-automation'); ?>" />
                                <select id="gmb-redirect-match-type" class="gmb-select gmb-select-match">
                                    <?php foreach ($match_types as $m_id => $m_info) : ?>
                                        <option value="<?php echo esc_attr($m_id); ?>"><?php echo esc_html($m_info['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="gmb-form-group">
                            <label for="gmb-redirect-destination" class="gmb-form-label"><?php esc_html_e('Destination URL (To)', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb-redirect-destination" class="gmb-input" placeholder="<?php esc_attr_e('e.g. /new-campaign-page or https://...', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </div>

                    <div class="gmb-redirect-grid-3col">
                        <div class="gmb-form-group">
                            <label for="gmb-redirect-code" class="gmb-form-label"><?php esc_html_e('Redirection Code / Type', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb-redirect-code" class="gmb-select">
                                <?php foreach ($redirect_codes as $c_key => $c_info) : ?>
                                    <option value="<?php echo esc_attr($c_key); ?>"><?php echo esc_html($c_info['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="gmb-form-group">
                            <label for="gmb-redirect-status" class="gmb-form-label"><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb-redirect-status" class="gmb-select">
                                <option value="active"><?php esc_html_e('Active', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="inactive"><?php esc_html_e('Inactive', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>

                        <div class="gmb-form-group">
                            <label for="gmb-redirect-note" class="gmb-form-label"><?php esc_html_e('Note / Label (Optional)', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb-redirect-note" class="gmb-input" placeholder="<?php esc_attr_e('e.g. Broken backlink fix', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </div>

                    <div class="gmb-redirect-actions-row">
                        <button type="button" id="gmb-cancel-add-btn" class="gmb-btn gmb-btn--secondary"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" id="gmb-add-rule-btn" class="gmb-btn gmb-btn--primary"><?php esc_html_e('Save Redirection', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>

                <!-- Subtab Panel 1: Manage View -->
                <div id="gmb-redirect-manage-view" class="gmb-redirect-view-panel active" role="tabpanel">
                    
                    <!-- Search & Filter Controls Toolbar -->
                    <div class="gmb-redirect-toolbar">
                        <div class="gmb-redirect-toolbar-left">
                            <div class="gmb-bulk-actions-wrap">
                                <select id="gmb-bulk-redirect-action" class="gmb-select gmb-bulk-select">
                                    <option value=""><?php esc_html_e('Bulk Actions', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="activate"><?php esc_html_e('Activate', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="deactivate"><?php esc_html_e('Deactivate', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="reset_hits"><?php esc_html_e('Reset Hits', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="delete"><?php esc_html_e('Delete Selected', 'gmb-ranker-seo-automation'); ?></option>
                                </select>
                                <button type="button" id="gmb-bulk-apply-btn" class="gmb-btn-apply"><?php esc_html_e('Apply', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                            <div class="gmb-filter-dropdowns">
                                <select id="gmb-filter-redirect-code" class="gmb-select gmb-filter-select">
                                    <option value="all"><?php esc_html_e('All Status Codes', 'gmb-ranker-seo-automation'); ?></option>
                                    <?php foreach ($redirect_codes as $c_key => $c_info) : ?>
                                        <option value="<?php echo esc_attr($c_key); ?>"><?php echo esc_html($c_info['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="gmb-filter-redirect-status" class="gmb-select gmb-filter-select">
                                    <option value="all"><?php esc_html_e('All Statuses', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="active"><?php esc_html_e('Active Only', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="inactive"><?php esc_html_e('Inactive Only', 'gmb-ranker-seo-automation'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="gmb-redirect-toolbar-right">
                            <div class="gmb-search-input-wrapper">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="gmb-search-icon" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" id="gmb-redirect-search" class="gmb-search-input" placeholder="<?php esc_attr_e('Search URLs or notes...', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>
                    </div>

                    <div id="gmb-rules-list-container" class="gmb-table-wrap">
                        <?php if (!empty($rules)) : ?>
                            <table id="gmb-rules-table" class="gmb-data-table">
                                <thead>
                                    <tr>
                                        <th class="gmb-th-checkbox">
                                            <input type="checkbox" id="gmb-select-all-rules" />
                                        </th>
                                        <th class="gmb-th-source"><?php esc_html_e('Source URL (From)', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-th-dest"><?php esc_html_e('Destination (To)', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-text-center"><?php esc_html_e('Type', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-text-center"><?php esc_html_e('Hits', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-text-center"><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-th-actions"><?php esc_html_e('Actions', 'gmb-ranker-seo-automation'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules as $rule) : 
                                        $r_id     = $rule['id'];
                                        $r_src    = $rule['source'];
                                        $r_dest   = $rule['destination'];
                                        $r_code   = $rule['code'];
                                        $r_hits   = $rule['hits'];
                                        $r_status = $rule['status'];
                                        $r_match  = $rule['match_type'];
                                        $r_note   = $rule['note'];
                                    ?>
                                        <tr class="gmb-rule-row" data-id="<?php echo esc_attr($r_id); ?>" data-code="<?php echo esc_attr($r_code); ?>" data-status="<?php echo esc_attr($r_status); ?>">
                                            <td class="gmb-text-center">
                                                <input type="checkbox" class="gmb-rule-checkbox" value="<?php echo esc_attr($r_id); ?>" />
                                            </td>
                                            <td class="gmb-td-source">
                                                <span class="gmb-rule-source-text"><?php echo esc_html($r_src); ?></span>
                                                <div class="gmb-td-meta-row">
                                                    <span class="gmb-badge-match"><?php printf(esc_html__('Match: %s', 'gmb-ranker-seo-automation'), esc_html(ucfirst($r_match))); ?></span>
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
                                                    <?php echo $r_status === 'active' ? esc_html__('Active', 'gmb-ranker-seo-automation') : esc_html__('Inactive', 'gmb-ranker-seo-automation'); ?>
                                                </button>
                                            </td>
                                            <td class="gmb-td-actions">
                                                <button type="button" class="gmb-edit-rule-btn gmb-table-action-btn gmb-table-action-btn--primary" data-id="<?php echo esc_attr($r_id); ?>" data-source="<?php echo esc_attr($r_src); ?>" data-dest="<?php echo esc_attr($r_dest); ?>" data-code="<?php echo esc_attr($r_code); ?>" data-match="<?php echo esc_attr($r_match); ?>" data-status="<?php echo esc_attr($r_status); ?>" data-note="<?php echo esc_attr($r_note); ?>"><?php esc_html_e('Edit', 'gmb-ranker-seo-automation'); ?></button>
                                                <a href="<?php echo esc_url(site_url(ltrim($r_src, '/'))); ?>" target="_blank" rel="noopener noreferrer" class="gmb-table-action-btn gmb-table-action-btn--muted"><?php esc_html_e('Test →', 'gmb-ranker-seo-automation'); ?></a>
                                                <button type="button" class="gmb-delete-rule-btn gmb-table-action-btn gmb-table-action-btn--danger" data-id="<?php echo esc_attr($r_id); ?>"><?php esc_html_e('Delete', 'gmb-ranker-seo-automation'); ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <div class="gmb-table-empty">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="gmb-table-empty-icon" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                <p class="gmb-table-empty-title"><?php esc_html_e('No redirection rules defined yet.', 'gmb-ranker-seo-automation'); ?></p>
                                <p class="gmb-table-empty-desc"><?php esc_html_e('Click "+ Add Redirection" above to create your first redirection rule.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subtab Panel 2: 404 Monitor View -->
                <div id="gmb-redirect-404-view" class="gmb-redirect-view-panel" role="tabpanel">
                    <div class="gmb-redirect-toolbar">
                        <div class="gmb-redirect-toolbar-left">
                            <h3 class="gmb-heading-3"><?php esc_html_e('404 Crawl Detection Log', 'gmb-ranker-seo-automation'); ?></h3>
                            <div class="gmb-search-input-wrapper">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="gmb-search-icon" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" id="gmb-404-search" class="gmb-search-input" placeholder="<?php esc_attr_e('Filter 404 URLs...', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>
                        <div class="gmb-redirect-toolbar-right gmb-flex-gap-xs">
                             <button type="button" id="gmb-ai-suggest-404-btn" class="gmb-btn-ai-404 gmb-btn--ai">
                                 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                 <span><?php esc_html_e('AI Auto-Fix 404s', 'gmb-ranker-seo-automation'); ?></span>
                             </button>
                             <button type="button" id="gmb-clear-404-btn" class="gmb-btn-purge-404">
                                 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                 <?php esc_html_e('Purge All Logs', 'gmb-ranker-seo-automation'); ?>
                             </button>
                        </div>
                    </div>
                    
                    <div id="gmb-logs-list-container" class="gmb-table-wrap">
                        <?php if (!empty($logs_404)) : ?>
                            <table id="gmb-404-logs-table" class="gmb-data-table">
                                <thead>
                                    <tr>
                                        <th class="gmb-th-404-url"><?php esc_html_e('URL / Requested Path', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-th-404-ref"><?php esc_html_e('Referrer', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-text-center gmb-th-404-date"><?php esc_html_e('Date/Time', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-th-actions"><?php esc_html_e('Actions', 'gmb-ranker-seo-automation'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs_404 as $log) : 
                                        $log_uri  = $log['uri'];
                                        $log_ref  = $log['referrer'];
                                        $log_time = $log['time'];
                                        $time_display = ($log_time > 0) ? sprintf(esc_html__('%s ago', 'gmb-ranker-seo-automation'), human_time_diff($log_time, time())) : esc_html__('Unknown', 'gmb-ranker-seo-automation');
                                    ?>
                                        <tr class="gmb-404-log-row">
                                            <td class="gmb-td-source">
                                                <span class="gmb-badge-404">404</span>
                                                <span class="gmb-404-uri-text"><?php echo esc_html($log_uri); ?></span>
                                            </td>
                                            <td class="gmb-td-dest">
                                                <?php echo esc_html($log_ref); ?>
                                            </td>
                                            <td class="gmb-td-center gmb-td-404-date">
                                                <?php echo esc_html($time_display); ?>
                                            </td>
                                            <td class="gmb-td-actions">
                                                <button type="button" class="gmb-ai-single-suggest-btn gmb-btn-ai-sm" data-url="<?php echo esc_attr($log_uri); ?>"><?php esc_html_e('AI Fix', 'gmb-ranker-seo-automation'); ?></button>
                                                <button type="button" class="gmb-create-redirect-btn gmb-btn-create-redirect" data-url="<?php echo esc_attr($log_uri); ?>"><?php esc_html_e('Redirect →', 'gmb-ranker-seo-automation'); ?></button>
                                                <button type="button" class="gmb-delete-single-404-btn gmb-table-action-btn gmb-table-action-btn--danger" data-url="<?php echo esc_attr($log_uri); ?>"><?php esc_html_e('Delete', 'gmb-ranker-seo-automation'); ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <div class="gmb-table-empty">
                                <p class="gmb-table-empty-title"><?php esc_html_e('404 log archive is currently clean.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subtab Panel 3: Settings View -->
                <div id="gmb-redirect-settings-view" class="gmb-redirect-view-panel gmb-panel-box" role="tabpanel">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_ranker_redirects_group'); ?>
                        
                        <div class="gmb-card-settings-list">
                            <!-- Option: Auto Post Redirect -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Auto Post Redirect', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <label class="gmb-switch">
                                        <input type="checkbox" name="gmb_ranker_auto_post_redirect" value="on" <?php checked('on', $settings['auto_post_redirect']); ?> />
                                        <span class="gmb-slider round"></span>
                                    </label>
                                    <p class="gmb-form-help"><?php esc_html_e('Automatically creates a 301 redirection whenever you change the slug or permalink of a published post, page, or custom post type.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>

                            <!-- Option: Fallback Behavior -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Fallback 404 Behavior', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <select name="gmb_ranker_fallback_behavior" id="gmb_ranker_fallback_behavior" class="gmb-select-320">
                                        <option value="default" <?php selected('default', $settings['fallback_behavior']); ?>><?php esc_html_e('Default 404 (Standard WordPress Error Page)', 'gmb-ranker-seo-automation'); ?></option>
                                        <option value="homepage" <?php selected('homepage', $settings['fallback_behavior']); ?>><?php esc_html_e('Redirect to Homepage (302)', 'gmb-ranker-seo-automation'); ?></option>
                                        <option value="custom" <?php selected('custom', $settings['fallback_behavior']); ?>><?php esc_html_e('Redirect to Custom URL (302)', 'gmb-ranker-seo-automation'); ?></option>
                                    </select>
                                    <p class="gmb-form-help"><?php esc_html_e('Action taken when a visitor or bot encounters an unhandled 404 error.', 'gmb-ranker-seo-automation'); ?></p>

                                    <div id="gmb-fallback-url-wrap" class="gmb-fallback-url-wrap gmb-mt-10 <?php echo $settings['fallback_behavior'] === 'custom' ? 'is-active' : ''; ?>">
                                        <input type="url" name="gmb_ranker_fallback_url" value="<?php echo esc_attr($settings['fallback_url']); ?>" placeholder="https://example.com/custom-404-landing" class="gmb-input-max-400" />
                                    </div>
                                </div>
                            </div>

                            <!-- Option: Redirect Attachments -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Redirect Media Attachments', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <label class="gmb-switch">
                                        <input type="checkbox" name="gmb_redirect_attachments" value="on" <?php checked('on', $settings['redirect_attachments']); ?> />
                                        <span class="gmb-slider round"></span>
                                    </label>
                                    <p class="gmb-form-help"><?php esc_html_e('Redirect media attachment URLs directly to the parent post they are attached to, preventing thin content penalty.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>

                            <!-- Option: Strip Category Base -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Strip Category Base', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <label class="gmb-switch">
                                        <input type="checkbox" name="gmb_strip_category_base" value="on" <?php checked('on', $settings['strip_category_base']); ?> />
                                        <span class="gmb-slider round"></span>
                                    </label>
                                    <p class="gmb-form-help"><?php esc_html_e('Remove /category/ prefix from all category archive URLs (e.g. example.com/category/news/ → example.com/news/).', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>

                            <!-- Option: 404 Log Max Limit -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('404 Log Retention Limit', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <input type="number" name="gmb_ranker_404_limit" min="10" max="5000" step="50" value="<?php echo esc_attr($settings['log_limit']); ?>" class="gmb-input-140" />
                                    <p class="gmb-form-help"><?php esc_html_e('Maximum number of 404 crawl detections to retain in the database before auto-pruning.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>

                            <!-- Option: Ignore Query Parameters in 404 -->
                            <div class="gmb-settings-row">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Ignore 404 Query Strings', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <label class="gmb-switch">
                                        <input type="checkbox" name="gmb_ranker_404_ignore_query" value="on" <?php checked('on', $settings['ignore_query']); ?> />
                                        <span class="gmb-slider round"></span>
                                    </label>
                                    <p class="gmb-form-help"><?php esc_html_e('Group and clean query strings (e.g. ?utm_source=...) from 404 log entries.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>

                            <!-- Option: Exclude Paths from 404 Logging -->
                            <div class="gmb-settings-row gmb-settings-row--noborder">
                                <div class="gmb-settings-label-col">
                                    <?php esc_html_e('Exclude Paths from 404 Log', 'gmb-ranker-seo-automation'); ?>
                                </div>
                                <div class="gmb-settings-input-col">
                                    <textarea name="gmb_ranker_404_exclude_paths" placeholder=".php, .env, wp-login, autodiscover, xmlrpc" class="gmb-textarea-max-480"><?php echo esc_textarea($settings['exclude_paths']); ?></textarea>
                                    <p class="gmb-form-help"><?php esc_html_e('Comma-separated keywords or extensions to exclude from 404 monitoring (e.g. common bot probing files).', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Settings', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </form>
                </div>

                <!-- Subtab Panel 4: Import & Export View -->
                <div id="gmb-redirect-import-export-view" class="gmb-redirect-view-panel gmb-panel-box" role="tabpanel">
                    <div class="gmb-grid-2col-24">
                        <?php $export_nonce = wp_create_nonce('gmb_export_redirects_nonce'); ?>
                        
                        <!-- Export Box -->
                        <div class="gmb-card">
                            <h3 class="gmb-heading-3"><?php esc_html_e('Export Redirection Rules', 'gmb-ranker-seo-automation'); ?></h3>
                            <p class="gmb-text-muted"><?php esc_html_e('Download all configured redirection rules as a backup or for migration to other sites.', 'gmb-ranker-seo-automation'); ?></p>
                            <div class="gmb-flex-gap-sm gmb-mt-16">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-redirects&gmb_action=export_redirects_json&_wpnonce=' . $export_nonce)); ?>" class="button gmb-btn-action-outline">
                                    <?php esc_html_e('Export JSON', 'gmb-ranker-seo-automation'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-redirects&gmb_action=export_redirects_csv&_wpnonce=' . $export_nonce)); ?>" class="button gmb-btn-action-outline">
                                    <?php esc_html_e('Export CSV', 'gmb-ranker-seo-automation'); ?>
                                </a>
                            </div>
                        </div>

                        <!-- Bulk Paste Tool -->
                        <div class="gmb-card">
                            <h3 class="gmb-heading-3"><?php esc_html_e('Bulk Paste Redirections', 'gmb-ranker-seo-automation'); ?></h3>
                            <p class="gmb-text-muted"><?php esc_html_e('Paste one rule per line in format: /from-url /to-url [301]', 'gmb-ranker-seo-automation'); ?></p>
                            
                            <textarea id="gmb-bulk-import-textarea" placeholder="/old-summer-sale /sale 301&#10;/old-blog-post /blog/new-post 301" class="gmb-textarea-paste"></textarea>
                            
                            <div class="gmb-flex-between">
                                <select id="gmb-bulk-import-match" class="gmb-select gmb-select-match">
                                    <?php foreach ($match_types as $m_id => $m_info) : ?>
                                        <option value="<?php echo esc_attr($m_id); ?>"><?php echo esc_html($m_info['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="gmb-bulk-import-submit-btn" class="gmb-btn-action-primary">
                                    <?php esc_html_e('Import Rules', 'gmb-ranker-seo-automation'); ?>
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
                    <h3 class="gmb-modal-title"><?php esc_html_e('AI Auto-Fix 404 Redirections', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="gmb-modal-close" id="gmb-ai-modal-close">&times;</button>
                </div>
                <div class="gmb-modal-body">
                    <div id="gmb-ai-modal-loading" class="gmb-ai-loading-box">
                        <div class="gmb-spinner"></div>
                        <p class="gmb-ai-loading-text"><?php esc_html_e('Analyzing 404 logs against your website\'s published content with AI...', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                    <div id="gmb-ai-modal-content" class="gmb-hidden">
                        <p class="gmb-text-muted gmb-mb-12"><?php esc_html_e('Below are the AI-recommended redirection targets mapped to your live site pages. Uncheck any rules you do not wish to apply.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-table-wrap gmb-ai-table-scroll">
                            <table class="gmb-data-table gmb-table-compact">
                                <thead>
                                    <tr>
                                         <th class="gmb-th-checkbox"><input type="checkbox" id="gmb-ai-select-all" checked /></th>
                                         <th><?php esc_html_e('404 Source Path', 'gmb-ranker-seo-automation'); ?></th>
                                         <th><?php esc_html_e('AI Suggested Destination', 'gmb-ranker-seo-automation'); ?></th>
                                         <th><?php esc_html_e('Code', 'gmb-ranker-seo-automation'); ?></th>
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
                    <button type="button" class="button gmb-btn-secondary" id="gmb-ai-modal-cancel"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" class="button button-primary gmb-btn--primary" id="gmb-ai-apply-btn" disabled><?php esc_html_e('Batch Apply AI Rules', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
