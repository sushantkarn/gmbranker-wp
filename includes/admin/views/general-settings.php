<?php
if (!defined('ABSPATH')) exit;

$strip_category_base         = isset($strip_category_base) ? $strip_category_base : get_option('gmb_strip_category_base', 'off');
$redirect_attachments        = isset($redirect_attachments) ? $redirect_attachments : get_option('gmb_redirect_attachments', 'off');
$redirect_orphan_attachments = isset($redirect_orphan_attachments) ? $redirect_orphan_attachments : get_option('gmb_redirect_orphan_attachments', '');
$nofollow_external_links     = isset($nofollow_external_links) ? $nofollow_external_links : get_option('gmb_nofollow_external_links', 'off');
$nofollow_image_links        = isset($nofollow_image_links) ? $nofollow_image_links : get_option('gmb_nofollow_image_links', 'off');
$new_window_external_links   = isset($new_window_external_links) ? $new_window_external_links : get_option('gmb_new_window_external_links', 'off');
$affiliate_link_prefixes     = isset($affiliate_link_prefixes) ? $affiliate_link_prefixes : get_option('gmb_affiliate_link_prefixes', '');
$image_alt_temp              = isset($image_alt_temp) ? $image_alt_temp : get_option('gmb_image_seo_alt_template', get_option('gmb_image_alt_template', '%title% %alt%'));
$image_title_temp            = isset($image_title_temp) ? $image_title_temp : get_option('gmb_image_seo_title_template', get_option('gmb_image_title_template', '%title%'));
?>
             <?php if ($current_page === 'gmb-ranker-settings') : ?>
            <div class="rm-tab-content active" id="rm-tab-local">
                <form method="post" action="options.php" novalidate>
                    <?php settings_fields('gmb_ranker_general_group'); ?>
                    
                    <div class="gmb-sidebar-layout-container">
                        
                        <!-- Sidebar Navigation Column -->
                        <?php
                        $active_sub = 'links';
                        $req_sub = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : (isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : ''));
                        if (!empty($req_sub) && in_array($req_sub, array('image', 'links', 'llmstxt', 'toc', 'security', 'ai', 'settings'), true)) {
                            $active_sub = ($req_sub === 'settings') ? 'links' : $req_sub;
                        } elseif (!empty($current_tab) && in_array($current_tab, array('image', 'links', 'llmstxt', 'toc', 'security', 'ai', 'settings'), true)) {
                            $active_sub = ($current_tab === 'settings') ? 'links' : $current_tab;
                        }
                        ?>
                        <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_sub); ?>" />
                        <div class="gmb-sidebar-nav">
                            <ul>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'links') ? 'active' : ''; ?>" data-subtab="gmb-subtab-links">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                    Links
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'image') ? 'active' : ''; ?>" data-subtab="gmb-subtab-image">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    Image SEO
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'llmstxt') ? 'active' : ''; ?>" data-subtab="gmb-subtab-llmstxt">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    Edit llms.txt
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'security') ? 'active' : ''; ?>" data-subtab="gmb-subtab-security">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    Security
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'ai') ? 'active' : ''; ?>" data-subtab="gmb-subtab-ai">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    Content AI
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'toc') ? 'active' : ''; ?>" data-subtab="gmb-subtab-toc">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                    Table of Contents
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Content Settings Column -->
                        <div class="gmb-sidebar-content-panel">
                            
                            <!-- Subtab: Links -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'links') ? 'active' : ''; ?>" id="gmb-subtab-links">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Links</h2>
                                    <p class="gmb-text-muted">Change how some of the links open and operate on your website. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <!-- Option 1: Strip Category Base -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Strip Category Base
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" id="gmb_strip_category_base" name="gmb_strip_category_base" value="on" <?php checked($strip_category_base, 'on'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Remove /category/ from category archive URLs. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Why do this?</a>
                                        </p>
                                        <p class="gmb-code-snippet">
                                            E.g. example.com/category/my-category/ becomes example.com/my-category
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 2: Redirect Attachments -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Redirect Attachments
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" id="gmb_redirect_attachments" name="gmb_redirect_attachments" value="on" <?php checked($redirect_attachments, 'on'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Redirect all attachment page URLs to the post they appear in. For more advanced redirection control, use the built-in <a href="admin.php?page=gmb-ranker-automation&tab=redirects" class="gmb-help-link">Redirection Manager</a>.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 3: Redirect Orphan Attachments -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Redirect Orphan Attachments
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" id="gmb_redirect_orphan_attachments" name="gmb_redirect_orphan_attachments" value="<?php echo esc_url($redirect_orphan_attachments); ?>" class="gmb-input gmb-input--max-480 gmb-mb-8" placeholder="https://..." />
                                        <p class="gmb-form-help">
                                            Redirect attachments without a parent post to this URL. Leave empty for no redirection.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 4: Nofollow External Links -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Nofollow External Links
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" id="gmb_nofollow_external_links" name="gmb_nofollow_external_links" value="on" <?php checked($nofollow_external_links, 'on'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Automatically add <code>rel="nofollow"</code> attribute for external links appearing in your posts, pages, and other post types. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 5: Nofollow Image File Links -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Nofollow Image File Links
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" id="gmb_nofollow_image_links" name="gmb_nofollow_image_links" value="on" <?php checked($nofollow_image_links, 'on'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Automatically add <code>rel="nofollow"</code> attribute for links pointing to external image files. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 6: Open External Links in New Tab/Window -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Open External Links in New Tab/Window
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" id="gmb_new_window_external_links" name="gmb_new_window_external_links" value="on" <?php checked($new_window_external_links, 'on'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Automatically add <code>target="_blank"</code> attribute for external links appearing in your posts, pages, and other post types. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option 7: Affiliate Link Prefix -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Affiliate Link Prefix
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea id="gmb_affiliate_link_prefixes" name="gmb_affiliate_link_prefixes" rows="3" class="gmb-input gmb-textarea--code gmb-input--max-480 gmb-mb-8" placeholder="Example: /get/"><?php echo esc_textarea($affiliate_link_prefixes); ?></textarea>
                                        <p class="gmb-form-help">
                                            Add the URI prefixes you use for affiliate (cloaked) links, which redirect to external sites. These will not count as internal links in the content analysis. Add one per line.
                                        </p>
                                    </div>
                                </div>

                                <!-- Premium Footer card bar -->
                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="gmb-reset-links-options" >Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Image SEO -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'image') ? 'active' : ''; ?>" id="gmb-subtab-image">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Image SEO</h2>
                                    <p class="gmb-text-muted">Configure dynamic generation patterns for missing alt and title attributes on your images. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>

                                <!-- Option: Image Alt Attribute Template -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Image Alt Template
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" id="gmb_image_seo_alt_template" name="gmb_image_seo_alt_template" value="<?php echo esc_attr($image_alt_temp); ?>" class="gmb-input gmb-input--max-480" />
                                        <p class="gmb-form-help">
                                            Format template to populate empty image alt attributes. Use variables like <code>%filename%</code>, <code>%title%</code>.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Image Title Attribute Template -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Image Title Template
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" id="gmb_image_seo_title_template" name="gmb_image_seo_title_template" value="<?php echo esc_attr($image_title_temp); ?>" class="gmb-input gmb-input--max-480" />
                                        <p class="gmb-form-help">
                                            Format template to populate empty image title tags. Use variables like <code>%filename%</code>, <code>%title%</code>.
                                        </p>
                                    </div>
                                </div>

                                <!-- Premium Footer card bar -->
                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="gmb-reset-image-options" >Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>
                            
                            <!-- Subtab: Security settings -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'security') ? 'active' : ''; ?>" id="gmb-subtab-security">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Enterprise Website Security &amp; Hardening</h2>
                                    <p class="gmb-text-muted">Proactively protect your WordPress site from hacker probes, brute-force attacks, code injection, and information leakage. Modeled after enterprise security standards.</p>
                                </div>

                                <?php
                                $sec_service   = class_exists('GMB_Ranker_SEO_Security_Service') ? GMB_Ranker_SEO_Security_Service::get_instance() : null;
                                $sec_score     = $sec_service ? $sec_service->calculate_security_score() : array(
                                    'score' => 60,
                                    'checks' => array(),
                                );
                                $admin_check   = $sec_service ? $sec_service->check_admin_user_exists() : array('exists' => false, 'username' => '');
                                $display_audit = $sec_service ? $sec_service->audit_display_names() : array('has_issues' => false, 'users' => array());
                                ?>

                                <!-- Security Health Scorecard -->
                                <div class="gmb-card gmb-sec-scorecard-card">
                                    <div class="gmb-sec-scorecard-header">
                                        <div class="gmb-sec-score-group">
                                            <?php
                                            $score_cls = ($sec_score['score'] >= 85) ? 'gmb-sec-score-circle--good' : (($sec_score['score'] >= 60) ? 'gmb-sec-score-circle--fair' : 'gmb-sec-score-circle--poor');
                                            ?>
                                            <div class="gmb-sec-score-circle <?php echo esc_attr($score_cls); ?>">
                                                <?php echo esc_html($sec_score['score']); ?>%
                                            </div>
                                            <div>
                                                <h3 class="gmb-sec-title">Security Hardening Status</h3>
                                                <p class="gmb-sec-desc">Automated defenses protect against malware uploads, user discovery, sensitive file leaks, 404 scanner probes, and brute-force lockouts.</p>
                                            </div>
                                        </div>
                                        <div class="gmb-flex-shrink-0">
                                            <button type="button" class="button button-secondary gmb-btn-apply-sec" id="gmb-apply-recommended-sec-btn">
                                                Apply Recommended Hardening
                                            </button>
                                        </div>
                                    </div>

                                    <?php
                                    $issues_count = 0;
                                    $passed_count = 0;
                                    if (!empty($sec_score['checks'])) {
                                        foreach ($sec_score['checks'] as $c) {
                                            if (!empty($c['passed'])) {
                                                $passed_count++;
                                            } else {
                                                $issues_count++;
                                            }
                                        }
                                    }
                                    $check_hints = array(
                                        'ssl'              => __('Ensures visitor traffic is securely encrypted with modern SSL/HTTPS protocols.', 'gmb-ranker-seo-automation'),
                                        'uploads'          => __('Denies execution of arbitrary PHP scripts and web shells in the uploads directory.', 'gmb-ranker-seo-automation'),
                                        'sensitive_files'  => __('Blocks direct web access to debug.log, environment variables (.env), and database backups.', 'gmb-ranker-seo-automation'),
                                        'indexing'         => __('Disables server directory browsing (Options -Indexes) to hide all file listings.', 'gmb-ranker-seo-automation'),
                                        'user_enumeration' => __('Blocks ?author=1 and REST API probes that harvest administrator usernames.', 'gmb-ranker-seo-automation'),
                                        'login_errors'     => __('Masks login error messages to prevent hackers from confirming valid accounts.', 'gmb-ranker-seo-automation'),
                                        'file_edit'        => __('Disables theme and plugin PHP file editing in the WordPress admin dashboard.', 'gmb-ranker-seo-automation'),
                                        'admin_username'   => __('Verifies that no default, high-risk "admin" or "administrator" username exists.', 'gmb-ranker-seo-automation'),
                                        'xmlrpc'           => __('Shuts down xmlrpc.php to neutralize distributed brute-force amplification.', 'gmb-ranker-seo-automation'),
                                        'security_headers' => __('Enforces modern HTTP security headers (nosniff, clickjacking, HSTS).', 'gmb-ranker-seo-automation'),
                                        'brute_force'      => __('Enforces automated IP rate-limiting lockout and bot honeypot on wp-login.php.', 'gmb-ranker-seo-automation'),
                                        'waf_404'          => __('Automatically locks out malicious bot IPs scanning for known backdoor exploits.', 'gmb-ranker-seo-automation'),
                                    );
                                    ?>

                                    <!-- Audit Checklist Header & Filters -->
                                    <div class="gmb-sec-breakdown-bar">
                                        <div>
                                            <span class="gmb-sec-breakdown-title">Security Audit Breakdown</span>
                                            <span class="gmb-sec-breakdown-count">(<?php echo esc_html($issues_count); ?> issue<?php echo $issues_count === 1 ? '' : 's'; ?> detected)</span>
                                        </div>
                                        <div class="gmb-sec-filter-tabs" id="gmb-sec-filter-tabs">
                                            <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--all active" data-filter="all">
                                                All Checks (<?php echo count($sec_score['checks']); ?>)
                                            </button>
                                            <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--issues" data-filter="issues">
                                                Issues Only (<?php echo esc_html($issues_count); ?>)
                                            </button>
                                            <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--passed" data-filter="passed">
                                                Protected (<?php echo esc_html($passed_count); ?>)
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Audit Checklist Rows (One Issue / Check per Row with vertical scroll after 4 cards) -->
                                    <div class="gmb-sec-checks-list">
                                        <?php if (!empty($sec_score['checks'])) : ?>
                                            <?php foreach ($sec_score['checks'] as $key => $check) : ?>
                                                <?php 
                                                $is_passed = !empty($check['passed']); 
                                                $hint = isset($check_hints[$key]) ? $check_hints[$key] : '';
                                                ?>
                                                <div class="gmb-sec-check-row <?php echo $is_passed ? 'gmb-sec-check-row--passed' : 'gmb-sec-check-row--issue'; ?>" data-status="<?php echo $is_passed ? 'passed' : 'issue'; ?>">
                                                    <div class="gmb-sec-check-left">
                                                        <div class="gmb-sec-check-icon <?php echo $is_passed ? 'gmb-sec-check-icon--passed' : 'gmb-sec-check-icon--issue'; ?>">
                                                            <?php echo $is_passed ? '' : ''; ?>
                                                        </div>
                                                        <div class="gmb-flex-1 min-w-0">
                                                            <div class="gmb-sec-check-label"><?php echo esc_html($check['label']); ?></div>
                                                            <?php if (!empty($hint)) : ?>
                                                                <div class="gmb-sec-check-hint"><?php echo esc_html($hint); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="gmb-ml-16 gmb-flex-shrink-0">
                                                        <span class="gmb-sec-check-status-badge <?php echo $is_passed ? 'gmb-sec-check-status-badge--passed' : 'gmb-sec-check-status-badge--issue'; ?>">
                                                            <?php echo esc_html($check['status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($admin_check['exists']) : ?>
                                    <!-- High Risk Warning Alert -->
                                    <div class="gmb-sec-alert-danger">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-sec-alert-icon"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        <div class="gmb-flex-1">
                                            <strong class="gmb-sec-alert-title-danger">Critical Vulnerability: Default "<?php echo esc_html($admin_check['username']); ?>" Account Exists</strong>
                                            <p class="gmb-sec-alert-text-danger">Hackers automatically target the username "<?php echo esc_html($admin_check['username']); ?>" in over 85% of automated brute-force attacks. We strongly recommend changing this username immediately to a secure custom login name.</p>
                                            <div class="gmb-mt-12">
                                                <button type="button" class="button button-primary gmb-open-change-username-modal-btn gmb-btn--danger-action" data-username="<?php echo esc_attr($admin_check['username']); ?>">
                                                    Rename Insecure "<?php echo esc_html($admin_check['username']); ?>" Username
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($display_audit['has_issues'])) : ?>
                                    <!-- Display Name Risk Alert -->
                                    <div id="gmb-display-name-risk-card" class="gmb-sec-alert-warning">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-sec-alert-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <div class="gmb-flex-1">
                                            <strong class="gmb-sec-alert-title-warning">Username Reconnaissance Notice: Public Display Name equals Login Name</strong>
                                            <p class="gmb-sec-alert-text-warning">The administrator account(s) <code><?php echo esc_html(implode(', ', $display_audit['users'])); ?></code> use their exact login name as their public display name. Setting a distinct public display name prevents exposing your private login username.</p>
                                            <div class="gmb-mt-12 gmb-flex-wrap-gap-sm">
                                                <button type="button" class="button button-primary gmb-btn--warning-action" id="gmb-auto-fix-display-name-btn">
                                                    Auto-Fix Display Name
                                                </button>
                                                <button type="button" class="button button-secondary gmb-open-change-username-modal-btn gmb-btn-font-600 gmb-btn-rounded-6">
                                                    Change Login Username
                                                </button>
                                                <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="button button-link gmb-link-sm">
                                                    Edit Profile &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- ========================================================= -->
                                <!-- SECTION 1: CORE SYSTEM & SENSITIVE FILE HARDENING         -->
                                <!-- ========================================================= -->
                                <div class="gmb-settings-section-divider">
                                    <h3 class="gmb-settings-section-title">1. Core System &amp; Sensitive File Hardening</h3>
                                </div>

                                <!-- Option: Block Code Execution in Uploads -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Block Code Execution in Uploads
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_block_uploads_execution" value="1" <?php checked(get_option('gmb_seo_block_uploads_execution', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Denies execution of PHP scripts and web shells in <code>wp-content/uploads/</code>. Protects against arbitrary file uploads via compromised plugins or contact forms.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Protect Sensitive Files & debug.log -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Protect Sensitive Files &amp; debug.log
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_block_sensitive_files" value="1" <?php checked(get_option('gmb_seo_block_sensitive_files', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Blocks public web access to <code>debug.log</code>, <code>readme.html</code>, <code>license.txt</code>, <code>.env</code>, <code>.git</code>, and database backup files (<code>.sql</code>, <code>.bak</code>).
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable Directory Indexing -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable Directory Browsing
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_directory_indexing" value="1" <?php checked(get_option('gmb_seo_disable_directory_indexing', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Prevents web servers from generating directory listings (<code>Options -Indexes</code>) and ensures blank index files exist across uploads directories.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable Insecure HTTP Methods -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable Insecure HTTP Request Methods
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_http_methods" value="1" <?php checked(get_option('gmb_seo_disable_http_methods', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Rejects insecure HTTP methods (<code>TRACE</code> and <code>TRACK</code>) with a 405 Method Not Allowed error, neutralizing Cross-Site Tracing (XST) attacks.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable Application Passwords -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable Application Passwords
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_application_passwords" value="1" <?php checked(get_option('gmb_seo_disable_application_passwords', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Disables WordPress Application Passwords to prevent attackers from creating persistent API access tokens without your knowledge.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Rogue Administrator Account Interceptor -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Rogue Administrator Shield
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_block_unauthorized_admins" value="1" <?php checked(get_option('gmb_seo_block_unauthorized_admins', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Detects and automatically demotes unauthorized administrator accounts created outside the WordPress dashboard (e.g. via direct SQL injection backdoors).
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable Open Public Registration -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable Public Guest Registration
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_open_registration" value="1" <?php checked(get_option('gmb_seo_disable_open_registration', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Forces WordPress to reject public guest registrations, preventing automated user spam bots from cluttering your subscriber list.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Prevent User Enumeration -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Prevent User Enumeration
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_prevent_user_enumeration" value="1" <?php checked(get_option('gmb_seo_prevent_user_enumeration', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Blocks hacker reconnaissance bots from harvesting administrator usernames via <code>?author=1</code> scans, unauthenticated <code>/wp/v2/users</code> REST requests, and user sitemaps.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Mask Login Error Messages -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Prevent Login Information Leakage
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_mask_login_errors" value="1" <?php checked(get_option('gmb_seo_mask_login_errors', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Replaces detailed login error hints ("Unknown username", "Incorrect password") with a generic message, preventing attackers from confirming whether a username exists.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable File Editing -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable Theme &amp; Plugin File Editing
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_file_edit" value="1" <?php checked(get_option('gmb_seo_disable_file_edit', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Locks down the built-in file editor in the WordPress dashboard (<code>DISALLOW_FILE_EDIT</code>). Prevents attackers from injecting backdoors into PHP files if an admin session is hijacked.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Allow Username Editing in User Profiles -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Allow Username Editing in User Profiles
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_allow_username_change" value="1" <?php checked(get_option('gmb_seo_allow_username_change', '1'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Adds a quick "Change Username" button directly onto WordPress <code>Users &rarr; Profile</code> to unlock and rename locked usernames without database access.
                                        </p>
                                    </div>
                                </div>

                                <!-- Tool: Change / Rename Username Live -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Change Login Username Tool
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php
                                        $site_users = function_exists('get_users') ? get_users(array('fields' => array('ID', 'user_login', 'display_name', 'roles'))) : array();
                                        ?>
                                        <div class="gmb-flex-wrap-gap-sm">
                                            <select id="gmb-sec-select-user" class="gmb-select gmb-input--max-280">
                                                <?php foreach ($site_users as $u) : 
                                                    $u_roles = isset($u->roles) ? (array) $u->roles : array();
                                                ?>
                                                    <option value="<?php echo esc_attr($u->ID); ?>" data-login="<?php echo esc_attr($u->user_login); ?>" <?php selected($u->ID, get_current_user_id()); ?>>
                                                        <?php echo esc_html($u->user_login); ?><?php echo !empty($u_roles) ? ' (' . esc_html(implode(', ', $u_roles)) . ')' : ''; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="button button-secondary gmb-btn-font-600 gmb-btn-rounded-6" id="gmb-sec-trigger-rename-btn">
                                                Rename Selected User
                                            </button>
                                        </div>
                                        <p class="gmb-form-help">
                                            Safely rename login usernames and author nicenames in the WordPress database without losing content attribution, role capabilities, or active login sessions.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Disable XML-RPC -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Disable XML-RPC
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_disable_xmlrpc" value="1" <?php checked(get_option('gmb_seo_disable_xmlrpc', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Completely shuts down XML-RPC and blocks <code>xmlrpc.php</code> requests to neutralize distributed brute-force attacks and pingback amplification.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Hide WP Version -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Hide WordPress Version
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_hide_wp_version" value="1" <?php checked(get_option('gmb_seo_hide_wp_version', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Removes generator meta tags from HTML and strips version strings (<code>?ver=</code>) from enqueued styles and scripts to hinder automated vulnerability scanners.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Restrict REST API -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Restrict REST API Access
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_restrict_rest_api" value="1" <?php checked(get_option('gmb_seo_restrict_rest_api', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Blocks public access to WordPress REST endpoints for unauthenticated guests, protecting internal user databases and draft content from external scrapers.
                                        </p>
                                    </div>
                                </div>

                                <!-- ========================================================= -->
                                <!-- SECTION 2: LOGIN SHIELD & ACCESS CONTROL                  -->
                                <!-- ========================================================= -->
                                <div class="gmb-settings-section-divider">
                                    <h3 class="gmb-settings-section-title">2. Login Protection &amp; Access Control</h3>
                                </div>

                                <!-- Option: Custom Login URL -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Custom / Obscured Login URL
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_seo_custom_login_slug" value="<?php echo esc_attr(get_option('gmb_seo_custom_login_slug', '')); ?>" placeholder="e.g. portal-login" class="gmb-input gmb-input--max-320" />
                                        <p class="gmb-form-help">
                                            Specify a secret login slug. When set, direct requests to <code>/wp-login.php</code> without this slug will be redirected to your homepage, stopping 99% of brute-force bot sweeps. (Emergency bypass: append <code>?gmb_sec_bypass=1</code>).
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Login Lockout Shield -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Limit Login Attempts &amp; IP Lockout
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_login_lockout_enabled" value="1" <?php checked(get_option('gmb_seo_login_lockout_enabled', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Enforces automated rate-limiting on failed login attempts. Suspicious IP addresses are temporarily locked out to prevent dictionary and credential-stuffing attacks.
                                        </p>

                                        <div class="gmb-flex-wrap-gap-md gmb-mt-12">
                                            <div>
                                                <label class="gmb-form-sublabel">Max Failed Retries:</label>
                                                <input type="number" name="gmb_seo_max_login_attempts" min="3" max="20" value="<?php echo esc_attr(get_option('gmb_seo_max_login_attempts', 5)); ?>" class="gmb-input gmb-input--width-100" />
                                            </div>
                                            <div>
                                                <label class="gmb-form-sublabel">Lockout Duration (Minutes):</label>
                                                <input type="number" name="gmb_seo_lockout_duration_mins" min="5" max="1440" value="<?php echo esc_attr(get_option('gmb_seo_lockout_duration_mins', 15)); ?>" class="gmb-input gmb-input--width-120" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Option: Login Form Honeypot -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Invisible Login Honeypot
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_login_honeypot" value="1" <?php checked(get_option('gmb_seo_login_honeypot', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Injects an invisible trap field into <code>wp-login.php</code>. Automated attack bots fill out all visible/hidden form inputs and are immediately dropped with a 403 Forbidden response.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Session Expiration & Hide Remember Me -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Session Expiration &amp; Remember Me
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-flex-wrap-gap-md gmb-mb-8">
                                            <div>
                                                <label class="gmb-form-sublabel">Max Session Lifetime (Hours):</label>
                                                <input type="number" name="gmb_seo_session_expiration_hours" min="1" max="720" value="<?php echo esc_attr(get_option('gmb_seo_session_expiration_hours', 24)); ?>" class="gmb-input gmb-input--width-100" />
                                            </div>
                                            <div class="gmb-mt-18">
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_seo_hide_remember_me" value="1" <?php checked(get_option('gmb_seo_hide_remember_me', '0'), '1'); ?> />
                                                    Hide "Remember Me" checkbox on login
                                                </label>
                                            </div>
                                        </div>
                                        <p class="gmb-form-help">
                                            Reduces login cookie lifetime (default 24h instead of 14 days) and removes persistent cookie flags to prevent unauthorized access on shared devices.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Strong Password Policy -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Strong Password Policy
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_strong_password_policy" value="1" <?php checked(get_option('gmb_seo_strong_password_policy', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Enforces a minimum password length of 12 characters containing uppercase, lowercase, numbers, and symbols for all administrator accounts.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Two-Factor Authentication (2FA) -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Administrator Two-Factor Authentication (2FA)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_enable_2fa" value="1" <?php checked(get_option('gmb_seo_enable_2fa', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Sends a secure 6-digit one-time verification code to the administrator's email on login. (Emergency bypass: define <code>GMB_DISABLE_2FA</code> as true in <code>wp-config.php</code>).
                                        </p>
                                    </div>
                                </div>

                                <!-- ========================================================= -->
                                <!-- SECTION 3: WAF & NETWORK DEFENSES                         -->
                                <!-- ========================================================= -->
                                <div class="gmb-settings-section-divider">
                                    <h3 class="gmb-settings-section-title">3. Firewall &amp; Network Access Control</h3>
                                </div>

                                <!-- Option: 404 Exploit Scanner Auto-Lockout -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        WAF 404 Exploit Scanner Lockout
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_404_exploit_lockout" value="1" <?php checked(get_option('gmb_seo_404_exploit_lockout', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Detects and instantly bans attacker IPs probing for backdoor scripts (e.g. <code>eval-stdin.php</code>, <code>phpmyadmin</code>, <code>wp-config.php.bak</code>, <code>shell.php</code>).
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Block Malicious User-Agents -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Block Vulnerability Scanner User-Agents
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_block_malicious_user_agents" value="1" <?php checked(get_option('gmb_seo_block_malicious_user_agents', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Intersects and immediately blocks automated hacker scanners (<code>sqlmap</code>, <code>nikto</code>, <code>wpscan</code>, <code>acunetix</code>, <code>masscan</code>, <code>zgrab</code>, <code>dirbuster</code>, <code>censys</code>) with a 403 Forbidden response.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: IP Whitelist -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        IP Whitelist
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_seo_ip_whitelist" rows="3" class="gmb-textarea gmb-textarea--code gmb-input--max-480" placeholder="One IP per line (e.g. 192.168.1.1)"><?php echo esc_textarea(get_option('gmb_seo_ip_whitelist', '')); ?></textarea>
                                        <p class="gmb-form-help">
                                            IP addresses listed here will never be locked out by rate limiting or exploit detection. (Your current IP: <code><?php echo esc_html($sec_service ? $sec_service->get_client_ip() : ''); ?></code>).
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: IP Blacklist -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        IP Blacklist
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_seo_ip_blacklist" rows="3" class="gmb-textarea gmb-textarea--code gmb-input--max-480" placeholder="One IP per line (e.g. 203.0.113.5)"><?php echo esc_textarea(get_option('gmb_seo_ip_blacklist', '')); ?></textarea>
                                        <p class="gmb-form-help">
                                            IP addresses listed here will be immediately rejected with a 403 Forbidden response on all site pages.
                                        </p>
                                    </div>
                                </div>

                                <!-- ========================================================= -->
                                <!-- SECTION 4: HTTP SECURITY HEADERS (GRADE A+)               -->
                                <!-- ========================================================= -->
                                <div class="gmb-settings-section-divider">
                                    <h3 class="gmb-settings-section-title">4. Advanced HTTP Security Headers</h3>
                                </div>

                                <!-- Option: Base Security Headers -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Core Security Headers
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_enable_security_headers" value="1" <?php checked(get_option('gmb_seo_enable_security_headers', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Sends <code>X-Content-Type-Options: nosniff</code>, <code>X-Frame-Options: SAMEORIGIN</code> (clickjacking defense), and <code>X-XSS-Protection</code> on all public requests.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: HSTS -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Strict Transport Security (HSTS)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_enable_hsts" value="1" <?php checked(get_option('gmb_seo_enable_hsts', '0'), '1'); ?> <?php if (function_exists('disabled')) { disabled(!is_ssl()); } elseif (!is_ssl()) { echo 'disabled="disabled"'; } ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Sends <code>Strict-Transport-Security: max-age=31536000; includeSubDomains; preload</code> to instruct browsers to strictly enforce HTTPS connections. <?php echo !is_ssl() ? '<strong class="gmb-text-danger">(Requires active SSL certificate)</strong>' : ''; ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Referrer Policy -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Referrer Policy
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $current_ref = get_option('gmb_seo_referrer_policy', 'strict-origin-when-cross-origin'); ?>
                                        <select name="gmb_seo_referrer_policy" class="gmb-select gmb-input--max-320">
                                            <option value="strict-origin-when-cross-origin" <?php selected($current_ref, 'strict-origin-when-cross-origin'); ?>>strict-origin-when-cross-origin (Recommended)</option>
                                            <option value="no-referrer-when-downgrade" <?php selected($current_ref, 'no-referrer-when-downgrade'); ?>>no-referrer-when-downgrade</option>
                                            <option value="same-origin" <?php selected($current_ref, 'same-origin'); ?>>same-origin</option>
                                            <option value="origin-when-cross-origin" <?php selected($current_ref, 'origin-when-cross-origin'); ?>>origin-when-cross-origin</option>
                                        </select>
                                        <p class="gmb-form-help">
                                            Controls how much referrer information is transmitted to other websites when visitors follow outbound links.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Permissions Policy -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Permissions-Policy (Hardware Access)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_permissions_policy" value="1" <?php checked(get_option('gmb_seo_permissions_policy', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Sends <code>Permissions-Policy: camera=(), microphone=(), geolocation=()</code> to disable hardware sensors and prevent malicious third-party iframes from accessing visitor devices.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: CSP Frame Ancestors -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Content Security Policy (CSP)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_seo_csp_frame_ancestors" value="1" <?php checked(get_option('gmb_seo_csp_frame_ancestors', '0'), '1'); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Sends <code>Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none';</code> to prevent unauthorized iframe clickjacking and malicious base tag injections.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Cross-Origin-Opener-Policy (COOP) -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Cross-Origin-Opener-Policy (COOP)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $current_coop = get_option('gmb_seo_enable_coop', 'same-origin-allow-popups'); ?>
                                        <select name="gmb_seo_enable_coop" class="gmb-select gmb-input--max-320">
                                            <option value="same-origin-allow-popups" <?php selected($current_coop, 'same-origin-allow-popups'); ?>>same-origin-allow-popups (Recommended)</option>
                                            <option value="same-origin" <?php selected($current_coop, 'same-origin'); ?>>same-origin (Strict Isolation)</option>
                                            <option value="unsafe-none" <?php selected($current_coop, 'unsafe-none'); ?>>unsafe-none</option>
                                            <option value="disabled" <?php selected($current_coop, 'disabled'); ?>>Disabled</option>
                                        </select>
                                        <p class="gmb-form-help">
                                            Isolates your browsing context from cross-origin documents to protect against side-channel attacks (Spectre) while safely allowing OAuth/Social login popups.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Cross-Origin-Resource-Policy (CORP) -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Cross-Origin-Resource-Policy (CORP)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $current_corp = get_option('gmb_seo_enable_corp', 'same-site'); ?>
                                        <select name="gmb_seo_enable_corp" class="gmb-select gmb-input--max-320">
                                            <option value="same-site" <?php selected($current_corp, 'same-site'); ?>>same-site (Recommended)</option>
                                            <option value="same-origin" <?php selected($current_corp, 'same-origin'); ?>>same-origin</option>
                                            <option value="cross-origin" <?php selected($current_corp, 'cross-origin'); ?>>cross-origin</option>
                                            <option value="disabled" <?php selected($current_corp, 'disabled'); ?>>Disabled</option>
                                        </select>
                                        <p class="gmb-form-help">
                                            Instructs browsers to restrict loading of images, scripts, and media resources to your site and approved subdomains.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: Cross-Origin-Embedder-Policy (COEP) -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Cross-Origin-Embedder-Policy (COEP)
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $current_coep = get_option('gmb_seo_enable_coep', 'unsafe-none'); ?>
                                        <select name="gmb_seo_enable_coep" class="gmb-select gmb-input--max-320">
                                            <option value="unsafe-none" <?php selected($current_coep, 'unsafe-none'); ?>>unsafe-none (Recommended for General Sites)</option>
                                            <option value="credentialless" <?php selected($current_coep, 'credentialless'); ?>>credentialless</option>
                                            <option value="require-corp" <?php selected($current_coep, 'require-corp'); ?>>require-corp (High Security)</option>
                                            <option value="disabled" <?php selected($current_coep, 'disabled'); ?>>Disabled</option>
                                        </select>
                                        <p class="gmb-form-help">
                                            Prevents third-party scripts from loading assets without explicit permission via CORS or CORP.
                                        </p>
                                    </div>
                                </div>

                                <!-- Option: X-Permitted-Cross-Domain-Policies -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        X-Permitted-Cross-Domain-Policies
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $current_xdp = get_option('gmb_seo_cross_domain_policies', 'none'); ?>
                                        <select name="gmb_seo_cross_domain_policies" class="gmb-select gmb-input--max-320">
                                            <option value="none" <?php selected($current_xdp, 'none'); ?>>none (Recommended - Strict Isolation)</option>
                                            <option value="master-only" <?php selected($current_xdp, 'master-only'); ?>>master-only</option>
                                            <option value="by-content-type" <?php selected($current_xdp, 'by-content-type'); ?>>by-content-type</option>
                                            <option value="disabled" <?php selected($current_xdp, 'disabled'); ?>>Disabled</option>
                                        </select>
                                        <p class="gmb-form-help">
                                            Prevents Adobe Flash and PDF viewers from making unauthorized cross-domain data queries against your domain.
                                        </p>
                                    </div>
                                </div>

                                <!-- Section 5: Recent Security Incident Activity Log -->
                                <?php
                                $recent_logs = $sec_service ? $sec_service->get_recent_security_incidents(5) : array();
                                ?>
                                <div class="gmb-sec-activity-card">
                                    <div class="gmb-sec-activity-header">
                                        <h4 class="gmb-sec-activity-title">
                                            <span class="gmb-sec-activity-live-dot"></span>
                                            Live Security Shield Activity
                                        </h4>
                                        <span class="gmb-sec-activity-subtitle">Automatic real-time threat defense log</span>
                                    </div>
                                    <?php if (empty($recent_logs)) : ?>
                                        <div class="gmb-sec-activity-empty">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="gmb-icon-inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            <strong>All clear:</strong> No unauthorized administrator attempts, brute-force lockouts, or backdoor scans detected in recent traffic.
                                        </div>
                                    <?php else : ?>
                                        <div class="gmb-sec-activity-list">
                                            <?php foreach ($recent_logs as $log) : ?>
                                                <div class="gmb-sec-activity-item">
                                                    <div class="gmb-sec-activity-meta">
                                                        <span class="gmb-sec-activity-time"><?php echo esc_html(date_i18n('M j, H:i', isset($log['time']) ? $log['time'] : time())); ?></span>
                                                        <span class="gmb-sec-activity-msg"><?php echo esc_html(isset($log['message']) ? $log['message'] : ''); ?></span>
                                                    </div>
                                                    <span class="gmb-sec-activity-ip">
                                                        <?php echo esc_html(isset($log['ip']) ? $log['ip'] : ''); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Premium Footer card bar -->
                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="gmb-reset-security-options">Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Security Settings" />
                                </div>
                            </div>

                            <!-- Subtab: Content AI -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'ai') ? 'active' : ''; ?>" id="gmb-subtab-ai">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Content AI Settings</h2>
                                    <p class="gmb-text-muted">Configure your AI models and API credentials for automated content generation. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-settings-row gmb-settings-row--align-center">
                                    <div class="gmb-settings-label-col">
                                        AI Provider
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php $provider = get_option('gmb_ai_provider', 'openrouter'); ?>
                                        <select name="gmb_ai_provider" id="gmb-ai-provider-select-sub" class="gmb-select gmb-input--md">
                                            <option value="openrouter" <?php selected($provider, 'openrouter'); ?>>OpenRouter (Recommended)</option>
                                            <option value="groq" <?php selected($provider, 'groq'); ?>>Groq Cloud</option>
                                            <option value="ollama" <?php selected($provider, 'ollama'); ?>>Ollama (Local AI)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- OpenRouter Settings Block -->
                                <div id="ai-section-openrouter-sub" class="gmb-ai-section <?php echo ($provider !== 'openrouter') ? 'gmb-hidden' : ''; ?>">
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            <span class="gmb-icon-box gmb-icon-box--dark"><img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/openrouter.svg')); ?>" alt="OpenRouter" class="gmb-icon-img" /></span>
                                            <span>OpenRouter API Key</span>
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="password" name="gmb_ai_openrouter_key" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_key', '')); ?>" class="gmb-input gmb-input--md" />
                                            <p class="gmb-form-help">Get key from <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai</a>.</p>
                                        </div>
                                    </div>
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Default Model
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_ai_openrouter_model" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Groq Settings Block -->
                                <div id="ai-section-groq-sub" class="gmb-ai-section <?php echo ($provider !== 'groq') ? 'gmb-hidden' : ''; ?>">
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/groq.svg')); ?>" alt="Groq" class="gmb-icon-img" />
                                            <span>Groq API Key</span>
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="password" name="gmb_ai_groq_key" value="<?php echo esc_attr(get_option('gmb_ai_groq_key', '')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Default Model
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_ai_groq_model" value="<?php echo esc_attr(get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Ollama Settings Block -->
                                <div id="ai-section-ollama-sub" class="gmb-ai-section <?php echo ($provider !== 'ollama') ? 'gmb-hidden' : ''; ?>">
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/ollama-icon.svg')); ?>" alt="Ollama" class="gmb-icon-img" />
                                            <span>Ollama API Base URL</span>
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_ai_ollama_url" value="<?php echo esc_attr(get_option('gmb_ai_ollama_url', 'http://localhost:11434')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Default Model
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_ai_ollama_model" value="<?php echo esc_attr(get_option('gmb_ai_ollama_model', 'llama3')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="gmb-reset-ai-options" >Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>
                            
                            <!-- Subtab: Edit llms.txt -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'llmstxt') ? 'active' : ''; ?>" id="gmb-subtab-llmstxt">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Edit llms.txt</h2>
                                    <p class="gmb-text-muted">Configure your llms.txt file for custom crawling/indexing rules. <a href="https://llmstxt.org" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-callout gmb-callout--info">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#466afa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon-img"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    <span>
                                        Your llms.txt file is available at: 
                                        <a href="<?php echo esc_url(site_url('llms.txt')); ?>" target="_blank" class="gmb-help-link font-semibold"><?php echo esc_url(site_url('llms.txt')); ?></a>
                                    </span>
                                </div>
                                
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Select Post Types
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php
                                        $included_types = get_option('gmb_llms_post_types', array('post', 'page', 'product'));
                                        if (!is_array($included_types)) {
                                            $included_types = array('post', 'page', 'product');
                                        }
                                        $all_post_types = get_post_types(array('public' => true), 'objects');
                                        ?>
                                        <div class="gmb-mb-12">
                                            <button type="button" class="button button-small" id="llms-select-all-types">Select / Deselect All</button>
                                        </div>
                                        <div class="gmb-checkbox-grid-2col">
                                            <?php foreach ($all_post_types as $pt) : 
                                                if ($pt->name === 'attachment') continue;
                                            ?>
                                                <label class="gmb-checkbox-item">
                                                    <input type="checkbox" class="llms-post-type-checkbox" name="gmb_llms_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $included_types)); ?> />
                                                    <?php echo esc_html($pt->label); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="gmb-form-help">Select the post types to be included in the llms.txt file.</p>
                                    </div>
                                </div>
                                
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Select Taxonomies
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php
                                        $included_taxs = get_option('gmb_llms_taxonomies', array());
                                        if (!is_array($included_taxs)) {
                                            $included_taxs = array();
                                        }
                                        $all_taxs = get_taxonomies(array('public' => true), 'objects');
                                        ?>
                                        <div class="gmb-mb-12">
                                            <button type="button" class="button button-small" id="llms-select-all-taxs">Select / Deselect All</button>
                                        </div>
                                        <div class="gmb-checkbox-grid-2col">
                                            <?php foreach ($all_taxs as $tax) : ?>
                                                <label class="gmb-checkbox-item">
                                                    <input type="checkbox" class="llms-tax-checkbox" name="gmb_llms_taxonomies[]" value="<?php echo esc_attr($tax->name); ?>" <?php checked(in_array($tax->name, $included_taxs)); ?> />
                                                    <?php echo esc_html($tax->label); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="gmb-form-help">Select the taxonomies to be included in the llms.txt file.</p>
                                    </div>
                                </div>
                                
                                <div class="gmb-settings-row gmb-settings-row--align-center">
                                    <div class="gmb-settings-label-col">
                                        Posts/Terms Limit
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="number" name="gmb_llms_limit" value="<?php echo esc_attr(get_option('gmb_llms_limit', '100')); ?>" class="gmb-input gmb-input--sm" />
                                        <p class="gmb-form-help">Maximum number of links to include for each content type.</p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row gmb-settings-row--align-center">
                                    <div class="gmb-settings-label-col">
                                        Feed Title
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_llms_title" value="<?php echo esc_attr(get_option('gmb_llms_title', get_bloginfo('name'))); ?>" class="gmb-input" />
                                    </div>
                                </div>

                                <div class="gmb-settings-row gmb-settings-row--align-center">
                                    <div class="gmb-settings-label-col">
                                        Feed Description
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_llms_desc" rows="2" class="gmb-input"><?php echo esc_textarea(get_option('gmb_llms_desc', get_bloginfo('description'))); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="gmb-settings-row gmb-settings-row--noborder">
                                    <div class="gmb-settings-label-col">
                                        Additional Content
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_llms_additional_content" placeholder="e.g. - [Contact Support](<?php echo esc_url(home_url('/support')); ?>): Reach our helpdesk." class="gmb-input gmb-textarea--code" rows="5"><?php echo esc_textarea(get_option('gmb_llms_additional_content', '')); ?></textarea>
                                        <p class="gmb-form-help">Add any extra text or markdown links you'd like to append to your llms.txt file manually.</p>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="llms-reset-options-btn" >Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Table of Contents -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'toc') ? 'active' : ''; ?>" id="gmb-subtab-toc">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Table of Contents Settings</h2>
                                    <p class="gmb-text-muted">Configure the automatic injection and display preferences for your post Tables of Contents. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>

                                <div class="gmb-card-settings-list">
                                    <!-- Title Form Row -->
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            TOC Box Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_toc_title" value="<?php echo esc_attr(get_option('gmb_toc_title', 'Table of Contents')); ?>" class="gmb-input gmb-input--md" />
                                        </div>
                                    </div>

                                    <!-- Min Headings Form Row -->
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Minimum Headings Trigger
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_toc_min_headings" class="gmb-select gmb-input--sm">
                                                <option value="2" <?php selected(get_option('gmb_toc_min_headings', '2'), '2'); ?>>2 Headings</option>
                                                <option value="3" <?php selected(get_option('gmb_toc_min_headings', '2'), '3'); ?>>3 Headings</option>
                                                <option value="4" <?php selected(get_option('gmb_toc_min_headings', '2'), '4'); ?>>4 Headings</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Heading Levels Form Row -->
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Heading Levels to Include
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php 
                                             $toc_levels = get_option('gmb_toc_levels', array('h2', 'h3'));
                                             if (!is_array($toc_levels)) { $toc_levels = array('h2', 'h3'); }
                                             ?>
                                            <div class="gmb-flex-center-gap-md">
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h1" <?php checked(in_array('h1', $toc_levels)); ?> /> H1</label>
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h2" <?php checked(in_array('h2', $toc_levels)); ?> /> H2</label>
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h3" <?php checked(in_array('h3', $toc_levels)); ?> /> H3</label>
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h4" <?php checked(in_array('h4', $toc_levels)); ?> /> H4</label>
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h5" <?php checked(in_array('h5', $toc_levels)); ?> /> H5</label>
                                                <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h6" <?php checked(in_array('h6', $toc_levels)); ?> /> H6</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Auto Insert Form Row -->
                                    <div class="gmb-settings-row gmb-settings-row--align-center">
                                        <div class="gmb-settings-label-col">
                                            Auto-prepend TOC Box
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_toc_auto_insert" value="1" <?php checked(get_option('gmb_toc_auto_insert', '1'), '1'); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Prepend Table of Contents automatically to the beginning of the post body.</p>
                                        </div>
                                    </div>

                                    <!-- Collapsible Form Row -->
                                    <div class="gmb-settings-row gmb-settings-row--align-center gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Enable Show/Hide Toggle
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_toc_collapsible" value="1" <?php checked(get_option('gmb_toc_collapsible', '1'), '1'); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">Allows readers to collapse or expand the table of contents box.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>
                        </div>
                    </div>
            <!-- Change Username Modal -->
            <div id="gmb-change-username-modal" class="gmb-username-modal-overlay">
                <div class="gmb-username-modal-card">
                    <div class="gmb-username-modal-header">
                        <h3 class="gmb-username-modal-title">
                            <?php esc_html_e('Change / Rename Account Username', 'gmb-ranker-seo-automation'); ?>
                        </h3>
                        <button type="button" id="gmb-close-username-modal" class="gmb-username-modal-close">&times;</button>
                    </div>
                    <p class="gmb-username-modal-desc">
                        <?php esc_html_e('Safely rename any user login name. All existing posts, comments, pages, and capabilities will be preserved seamlessly.', 'gmb-ranker-seo-automation'); ?>
                    </p>
                    <div class="gmb-username-modal-field">
                        <label class="gmb-username-modal-label"><?php esc_html_e('Selected Account', 'gmb-ranker-seo-automation'); ?></label>
                        <select id="gmb-modal-user-select" class="gmb-username-modal-select">
                            <?php 
                            $modal_users = function_exists('get_users') ? get_users() : array();
                            foreach ($modal_users as $u) : 
                                $u_roles = isset($u->roles) ? (array) $u->roles : array();
                            ?>
                                <option value="<?php echo esc_attr($u->ID); ?>" data-login="<?php echo esc_attr($u->user_login); ?>" <?php selected($u->ID, get_current_user_id()); ?>>
                                    <?php echo esc_html($u->user_login); ?><?php echo !empty($u_roles) ? ' (' . esc_html(implode(', ', $u_roles)) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="gmb-username-modal-field">
                        <label class="gmb-username-modal-label"><?php esc_html_e('Current Login Username', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="text" id="gmb-modal-current-username" disabled class="gmb-username-modal-input-current" />
                    </div>
                    <div class="gmb-username-modal-field--last">
                        <label class="gmb-username-modal-label"><?php esc_html_e('New Secure Login Username', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="text" id="gmb-modal-new-username" placeholder="e.g. custom_admin_name" class="gmb-username-modal-input-new" />
                        <span id="gmb-modal-username-error" class="gmb-username-modal-error"></span>
                    </div>
                    <div class="gmb-username-modal-footer">
                        <button type="button" id="gmb-cancel-username-modal" class="button button-secondary gmb-btn-font-600"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" id="gmb-submit-username-modal" class="button button-primary gmb-btn--modal-submit"><?php esc_html_e('Update Username', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <!-- Page: Titles & Meta Settings Page -->
