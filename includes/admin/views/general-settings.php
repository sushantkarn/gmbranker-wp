<?php
/**
 * General Settings Subtab View
 *
 * Enterprise-grade presentation layer for plugin settings:
 * Links, Image SEO, Security Control, Content AI, LLMs.txt, and Table of Contents.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'gmb-ranker-settings');
$current_tab  = isset($current_tab) ? $current_tab : (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '');

// Canonical options map
$opt_map = array(
    'strip_category_base'         => isset($strip_category_base) ? $strip_category_base : get_option('gmb_strip_category_base', 'off'),
    'redirect_attachments'        => isset($redirect_attachments) ? $redirect_attachments : get_option('gmb_redirect_attachments', 'off'),
    'redirect_orphan_attachments' => isset($redirect_orphan_attachments) ? $redirect_orphan_attachments : get_option('gmb_redirect_orphan_attachments', ''),
    'nofollow_external_links'     => isset($nofollow_external_links) ? $nofollow_external_links : get_option('gmb_nofollow_external_links', 'off'),
    'nofollow_image_links'        => isset($nofollow_image_links) ? $nofollow_image_links : get_option('gmb_nofollow_image_links', 'off'),
    'new_window_external_links'   => isset($new_window_external_links) ? $new_window_external_links : get_option('gmb_new_window_external_links', 'off'),
    'affiliate_link_prefixes'     => isset($affiliate_link_prefixes) ? $affiliate_link_prefixes : get_option('gmb_affiliate_link_prefixes', ''),
    'image_alt_temp'              => isset($image_alt_temp) ? $image_alt_temp : get_option('gmb_image_seo_alt_template', get_option('gmb_image_alt_template', '%title% %alt%')),
    'image_title_temp'            => isset($image_title_temp) ? $image_title_temp : get_option('gmb_image_seo_title_template', get_option('gmb_image_title_template', '%title%')),
);

// Security options map
$sec_opts = array(
    'block_uploads_execution'    => get_option('gmb_seo_block_uploads_execution', '0'),
    'block_sensitive_files'      => get_option('gmb_seo_block_sensitive_files', '0'),
    'disable_directory_indexing' => get_option('gmb_seo_disable_directory_indexing', '0'),
    'disable_http_methods'       => get_option('gmb_seo_disable_http_methods', '0'),
    'disable_app_passwords'      => get_option('gmb_seo_disable_application_passwords', '0'),
    'block_unauth_admins'        => get_option('gmb_seo_block_unauthorized_admins', '0'),
    'disable_open_registration'  => get_option('gmb_seo_disable_open_registration', '0'),
    'prevent_user_enum'          => get_option('gmb_seo_prevent_user_enumeration', '0'),
    'mask_login_errors'          => get_option('gmb_seo_mask_login_errors', '0'),
    'disable_file_edit'          => get_option('gmb_seo_disable_file_edit', '0'),
    'allow_username_change'      => get_option('gmb_seo_allow_username_change', '1'),
    'disable_xmlrpc'             => get_option('gmb_seo_disable_xmlrpc', '0'),
    'hide_wp_version'            => get_option('gmb_seo_hide_wp_version', '0'),
    'restrict_rest_api'          => get_option('gmb_seo_restrict_rest_api', '0'),
    'custom_login_slug'          => get_option('gmb_seo_custom_login_slug', ''),
    'login_lockout_enabled'      => get_option('gmb_seo_login_lockout_enabled', '0'),
    'max_login_attempts'         => get_option('gmb_seo_max_login_attempts', 5),
    'lockout_duration_mins'      => get_option('gmb_seo_lockout_duration_mins', 15),
    'login_honeypot'             => get_option('gmb_seo_login_honeypot', '0'),
    'session_expiration_hours'   => get_option('gmb_seo_session_expiration_hours', 24),
    'hide_remember_me'           => get_option('gmb_seo_hide_remember_me', '0'),
    'strong_password_policy'     => get_option('gmb_seo_strong_password_policy', '0'),
    'enable_2fa'                 => get_option('gmb_seo_enable_2fa', '0'),
    'exploit_404_lockout'        => get_option('gmb_seo_404_exploit_lockout', '0'),
    'block_malicious_useragents' => get_option('gmb_seo_block_malicious_user_agents', '0'),
    'ip_whitelist'               => get_option('gmb_seo_ip_whitelist', ''),
    'ip_blacklist'               => get_option('gmb_seo_ip_blacklist', ''),
    'enable_security_headers'    => get_option('gmb_seo_enable_security_headers', '0'),
    'enable_hsts'                => get_option('gmb_seo_enable_hsts', '0'),
    'referrer_policy'            => get_option('gmb_seo_referrer_policy', 'strict-origin-when-cross-origin'),
    'permissions_policy'         => get_option('gmb_seo_permissions_policy', '0'),
    'csp_frame_ancestors'        => get_option('gmb_seo_csp_frame_ancestors', '0'),
    'enable_coop'                => get_option('gmb_seo_enable_coop', 'same-origin-allow-popups'),
    'enable_corp'                => get_option('gmb_seo_enable_corp', 'same-site'),
    'enable_coep'                => get_option('gmb_seo_enable_coep', 'unsafe-none'),
    'cross_domain_policies'      => get_option('gmb_seo_cross_domain_policies', 'none'),
);
?>
<?php if ($current_page === 'gmb-ranker-settings') : ?>
<div class="rm-tab-content active" id="rm-tab-local">
    <form method="post" action="options.php" novalidate>
        <?php settings_fields('gmb_ranker_general_group'); ?>
        
        <div class="gmb-sidebar-layout-container">
            
            <!-- Sidebar Navigation Column -->
            <?php
            $active_sub = 'links';
            $req_sub    = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : (isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : ''));
            if (!empty($req_sub) && in_array($req_sub, array('image', 'links', 'llmstxt', 'toc', 'security', 'ai', 'settings'), true)) {
                $active_sub = ($req_sub === 'settings') ? 'links' : $req_sub;
            } elseif (!empty($current_tab) && in_array($current_tab, array('image', 'links', 'llmstxt', 'toc', 'security', 'ai', 'settings'), true)) {
                $active_sub = ($current_tab === 'settings') ? 'links' : $current_tab;
            }
            ?>
            <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_sub); ?>" />
            <div class="gmb-sidebar-nav" role="tablist" aria-label="<?php esc_attr_e('Settings Category Subtabs', 'gmb-ranker-seo-automation'); ?>">
                <ul>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'links') ? 'active' : ''; ?>" data-subtab="gmb-subtab-links" role="tab" aria-selected="<?php echo ($active_sub === 'links') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-links">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <?php esc_html_e('Links', 'gmb-ranker-seo-automation'); ?>
                    </li>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'image') ? 'active' : ''; ?>" data-subtab="gmb-subtab-image" role="tab" aria-selected="<?php echo ($active_sub === 'image') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-image">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <?php esc_html_e('Image SEO', 'gmb-ranker-seo-automation'); ?>
                    </li>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'llmstxt') ? 'active' : ''; ?>" data-subtab="gmb-subtab-llmstxt" role="tab" aria-selected="<?php echo ($active_sub === 'llmstxt') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-llmstxt">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        <?php esc_html_e('Edit llms.txt', 'gmb-ranker-seo-automation'); ?>
                    </li>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'security') ? 'active' : ''; ?>" data-subtab="gmb-subtab-security" role="tab" aria-selected="<?php echo ($active_sub === 'security') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-security">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <?php esc_html_e('Security', 'gmb-ranker-seo-automation'); ?>
                    </li>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'ai') ? 'active' : ''; ?>" data-subtab="gmb-subtab-ai" role="tab" aria-selected="<?php echo ($active_sub === 'ai') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-ai">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <?php esc_html_e('Content AI', 'gmb-ranker-seo-automation'); ?>
                    </li>
                    <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'toc') ? 'active' : ''; ?>" data-subtab="gmb-subtab-toc" role="tab" aria-selected="<?php echo ($active_sub === 'toc') ? 'true' : 'false'; ?>" aria-controls="gmb-subtab-toc">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        <?php esc_html_e('Table of Contents', 'gmb-ranker-seo-automation'); ?>
                    </li>
                </ul>
            </div>
            
            <!-- Content Settings Column -->
            <div class="gmb-sidebar-content-panel">
                
                <!-- Subtab: Links -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'links') ? 'active' : ''; ?>" id="gmb-subtab-links" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Links', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Change how links open and operate on your website.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                    
                    <!-- Option 1: Strip Category Base -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_strip_category_base"><?php esc_html_e('Strip Category Base', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Strip Category Base Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_strip_category_base" name="gmb_strip_category_base" value="on" <?php checked($opt_map['strip_category_base'], 'on'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Remove /category/ from category archive URLs.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                            <p class="gmb-code-snippet">
                                <?php esc_html_e('E.g. example.com/category/my-category/ becomes example.com/my-category', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 2: Redirect Attachments -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_redirect_attachments"><?php esc_html_e('Redirect Attachments', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Redirect Attachments Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_redirect_attachments" name="gmb_redirect_attachments" value="on" <?php checked($opt_map['redirect_attachments'], 'on'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Redirect attachment page URLs to the post they appear in. For more advanced redirection control, use the built-in Redirection Manager.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 3: Redirect Orphan Attachments -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_redirect_orphan_attachments"><?php esc_html_e('Redirect Orphan Attachments', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="text" id="gmb_redirect_orphan_attachments" name="gmb_redirect_orphan_attachments" value="<?php echo esc_url($opt_map['redirect_orphan_attachments']); ?>" class="gmb-input gmb-input--max-480 gmb-mb-8" placeholder="https://..." />
                            <p class="gmb-form-help">
                                <?php esc_html_e('Redirect attachments without a parent post to this URL. Leave empty for no redirection.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 4: Nofollow External Links -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_nofollow_external_links"><?php esc_html_e('Nofollow External Links', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Nofollow External Links Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_nofollow_external_links" name="gmb_nofollow_external_links" value="on" <?php checked($opt_map['nofollow_external_links'], 'on'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Automatically add rel="nofollow" attribute for external links appearing in your posts and pages.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 5: Nofollow Image File Links -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_nofollow_image_links"><?php esc_html_e('Nofollow Image File Links', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Nofollow Image File Links Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_nofollow_image_links" name="gmb_nofollow_image_links" value="on" <?php checked($opt_map['nofollow_image_links'], 'on'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Automatically add rel="nofollow" attribute for links pointing to external image files.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 6: Open External Links in New Tab/Window -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_new_window_external_links"><?php esc_html_e('Open External Links in New Tab/Window', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Open External Links in New Tab Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_new_window_external_links" name="gmb_new_window_external_links" value="on" <?php checked($opt_map['new_window_external_links'], 'on'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Automatically add target="_blank" attribute for external links appearing in your posts and pages.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option 7: Affiliate Link Prefix -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_affiliate_link_prefixes"><?php esc_html_e('Affiliate Link Prefix', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <textarea id="gmb_affiliate_link_prefixes" name="gmb_affiliate_link_prefixes" rows="3" class="gmb-input gmb-textarea--code gmb-input--max-480 gmb-mb-8" placeholder="<?php esc_attr_e('Example: /get/', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea($opt_map['affiliate_link_prefixes']); ?></textarea>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Add URI prefixes used for affiliate (cloaked) links. Add one per line.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Footer card bar -->
                    <div class="gmb-settings-footer">
                        <button type="button" class="button gmb-btn--ghost" id="gmb-reset-links-options" data-action="reset-links"><?php esc_html_e('Reset Options', 'gmb-ranker-seo-automation'); ?></button>
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>

                <!-- Subtab: Image SEO -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'image') ? 'active' : ''; ?>" id="gmb-subtab-image" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Image SEO', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Configure dynamic generation patterns for missing alt and title attributes on your images.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>

                    <!-- Option: Image Alt Attribute Template -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_image_seo_alt_template"><?php esc_html_e('Image Alt Template', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="text" id="gmb_image_seo_alt_template" name="gmb_image_seo_alt_template" value="<?php echo esc_attr($opt_map['image_alt_temp']); ?>" class="gmb-input gmb-input--max-480" />
                            <p class="gmb-form-help">
                                <?php esc_html_e('Format template to populate empty image alt attributes. Use variables like %filename%, %title%.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Image Title Attribute Template -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_image_seo_title_template"><?php esc_html_e('Image Title Template', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="text" id="gmb_image_seo_title_template" name="gmb_image_seo_title_template" value="<?php echo esc_attr($opt_map['image_title_temp']); ?>" class="gmb-input gmb-input--max-480" />
                            <p class="gmb-form-help">
                                <?php esc_html_e('Format template to populate empty image title tags. Use variables like %filename%, %title%.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Footer card bar -->
                    <div class="gmb-settings-footer">
                        <button type="button" class="button gmb-btn--ghost" id="gmb-reset-image-options" data-action="reset-image"><?php esc_html_e('Reset Options', 'gmb-ranker-seo-automation'); ?></button>
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>
                
                <!-- Subtab: Security settings -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'security') ? 'active' : ''; ?>" id="gmb-subtab-security" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Enterprise Website Security & Hardening', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Proactively protect your WordPress site from hacker probes, brute-force attacks, code injection, and information leakage.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>

                    <?php
                    $sec_service   = class_exists('GMB_Ranker_SEO_Security_Service') ? GMB_Ranker_SEO_Security_Service::get_instance() : null;
                    $sec_score     = $sec_service ? $sec_service->calculate_security_score() : array(
                        'score' => 0,
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
                                $score_val = (int)$sec_score['score'];
                                $score_cls = ($score_val >= 85) ? 'gmb-sec-score-circle--good' : (($score_val >= 60) ? 'gmb-sec-score-circle--fair' : 'gmb-sec-score-circle--poor');
                                ?>
                                <div class="gmb-sec-score-circle <?php echo esc_attr($score_cls); ?>">
                                    <?php echo esc_html($score_val); ?>%
                                </div>
                                <div>
                                    <h3 class="gmb-sec-title"><?php esc_html_e('Security Hardening Status', 'gmb-ranker-seo-automation'); ?></h3>
                                    <p class="gmb-sec-desc"><?php esc_html_e('Automated defenses protect against malware uploads, user discovery, sensitive file leaks, and brute-force lockouts.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                            </div>
                            <div class="gmb-flex-shrink-0">
                                <button type="button" class="button button-secondary gmb-btn-apply-sec" id="gmb-apply-recommended-sec-btn">
                                    <?php esc_html_e('Apply Recommended Hardening', 'gmb-ranker-seo-automation'); ?>
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
                                <span class="gmb-sec-breakdown-title"><?php esc_html_e('Security Audit Breakdown', 'gmb-ranker-seo-automation'); ?></span>
                                <span class="gmb-sec-breakdown-count">(<?php echo esc_html($issues_count); ?> <?php esc_html_e('issues detected', 'gmb-ranker-seo-automation'); ?>)</span>
                            </div>
                            <div class="gmb-sec-filter-tabs" id="gmb-sec-filter-tabs" role="tablist">
                                <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--all active" data-filter="all">
                                    <?php echo esc_html(sprintf(__('All Checks (%d)', 'gmb-ranker-seo-automation'), count($sec_score['checks']))); ?>
                                </button>
                                <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--issues" data-filter="issues">
                                    <?php echo esc_html(sprintf(__('Issues Only (%d)', 'gmb-ranker-seo-automation'), $issues_count)); ?>
                                </button>
                                <button type="button" class="gmb-sec-filter-btn gmb-sec-filter-btn--passed" data-filter="passed">
                                    <?php echo esc_html(sprintf(__('Protected (%d)', 'gmb-ranker-seo-automation'), $passed_count)); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Audit Checklist Rows -->
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
                                                <?php echo $is_passed ? '✓' : '!'; ?>
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
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-sec-alert-icon" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div class="gmb-flex-1">
                                <strong class="gmb-sec-alert-title-danger"><?php echo esc_html(sprintf(__('Critical Vulnerability: Default "%s" Account Exists', 'gmb-ranker-seo-automation'), $admin_check['username'])); ?></strong>
                                <p class="gmb-sec-alert-text-danger"><?php esc_html_e('Automated brute-force attacks target default usernames. We strongly recommend changing this username immediately to a secure custom login name.', 'gmb-ranker-seo-automation'); ?></p>
                                <div class="gmb-mt-12">
                                    <button type="button" class="button button-primary gmb-open-change-username-modal-btn gmb-btn--danger-action" data-username="<?php echo esc_attr($admin_check['username']); ?>">
                                        <?php echo esc_html(sprintf(__('Rename Insecure "%s" Username', 'gmb-ranker-seo-automation'), $admin_check['username'])); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($display_audit['has_issues'])) : ?>
                        <!-- Display Name Risk Alert -->
                        <div id="gmb-display-name-risk-card" class="gmb-sec-alert-warning">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-sec-alert-icon" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div class="gmb-flex-1">
                                <strong class="gmb-sec-alert-title-warning"><?php esc_html_e('Username Reconnaissance Notice: Public Display Name equals Login Name', 'gmb-ranker-seo-automation'); ?></strong>
                                <p class="gmb-sec-alert-text-warning"><?php echo esc_html(sprintf(__('The administrator account(s) %s use their login name as their public display name.', 'gmb-ranker-seo-automation'), implode(', ', $display_audit['users']))); ?></p>
                                <div class="gmb-mt-12 gmb-flex-wrap-gap-sm">
                                    <button type="button" class="button button-primary gmb-btn--warning-action" id="gmb-auto-fix-display-name-btn">
                                        <?php esc_html_e('Auto-Fix Display Name', 'gmb-ranker-seo-automation'); ?>
                                    </button>
                                    <button type="button" class="button button-secondary gmb-open-change-username-modal-btn gmb-btn-font-600 gmb-btn-rounded-6">
                                        <?php esc_html_e('Change Login Username', 'gmb-ranker-seo-automation'); ?>
                                    </button>
                                    <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="button button-link gmb-link-sm">
                                        <?php esc_html_e('Edit Profile →', 'gmb-ranker-seo-automation'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- SECTION 1: CORE SYSTEM HARDENING -->
                    <div class="gmb-settings-section-divider">
                        <h3 class="gmb-settings-section-title"><?php esc_html_e('1. Core System & Sensitive File Hardening', 'gmb-ranker-seo-automation'); ?></h3>
                    </div>

                    <!-- Option: Block Code Execution in Uploads -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_block_uploads_execution"><?php esc_html_e('Block Code Execution in Uploads', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Block Code Execution in Uploads Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_block_uploads_execution" name="gmb_seo_block_uploads_execution" value="1" <?php checked($sec_opts['block_uploads_execution'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Denies execution of PHP scripts and web shells in wp-content/uploads/.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Protect Sensitive Files & debug.log -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_block_sensitive_files"><?php esc_html_e('Protect Sensitive Files & debug.log', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Protect Sensitive Files Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_block_sensitive_files" name="gmb_seo_block_sensitive_files" value="1" <?php checked($sec_opts['block_sensitive_files'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Blocks public web access to debug.log, readme.html, license.txt, .env, .git, and backup files.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable Directory Indexing -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_directory_indexing"><?php esc_html_e('Disable Directory Browsing', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable Directory Browsing Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_directory_indexing" name="gmb_seo_disable_directory_indexing" value="1" <?php checked($sec_opts['disable_directory_indexing'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Prevents web servers from generating directory listings (Options -Indexes).', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable Insecure HTTP Methods -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_http_methods"><?php esc_html_e('Disable Insecure HTTP Request Methods', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable Insecure HTTP Request Methods Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_http_methods" name="gmb_seo_disable_http_methods" value="1" <?php checked($sec_opts['disable_http_methods'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Rejects TRACE and TRACK HTTP methods with a 405 Method Not Allowed error.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable Application Passwords -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_application_passwords"><?php esc_html_e('Disable Application Passwords', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable Application Passwords Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_application_passwords" name="gmb_seo_disable_application_passwords" value="1" <?php checked($sec_opts['disable_app_passwords'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Disables WordPress Application Passwords to prevent persistent API access tokens.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Rogue Administrator Account Interceptor -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_block_unauthorized_admins"><?php esc_html_e('Rogue Administrator Shield', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Rogue Administrator Shield Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_block_unauthorized_admins" name="gmb_seo_block_unauthorized_admins" value="1" <?php checked($sec_opts['block_unauth_admins'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Detects and demotes unauthorized administrator accounts created outside dashboard.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable Open Public Registration -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_open_registration"><?php esc_html_e('Disable Public Guest Registration', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable Public Guest Registration Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_open_registration" name="gmb_seo_disable_open_registration" value="1" <?php checked($sec_opts['disable_open_registration'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Forces WordPress to reject public guest registrations.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Prevent User Enumeration -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_prevent_user_enumeration"><?php esc_html_e('Prevent User Enumeration', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Prevent User Enumeration Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_prevent_user_enumeration" name="gmb_seo_prevent_user_enumeration" value="1" <?php checked($sec_opts['prevent_user_enum'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Blocks hacker reconnaissance bots from harvesting administrator usernames via ?author=1 scans and REST API.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Mask Login Error Messages -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_mask_login_errors"><?php esc_html_e('Prevent Login Information Leakage', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Prevent Login Information Leakage Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_mask_login_errors" name="gmb_seo_mask_login_errors" value="1" <?php checked($sec_opts['mask_login_errors'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Replaces detailed login error hints with a generic error message.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable File Editing -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_file_edit"><?php esc_html_e('Disable Theme & Plugin File Editing', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable Theme & Plugin File Editing Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_file_edit" name="gmb_seo_disable_file_edit" value="1" <?php checked($sec_opts['disable_file_edit'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Locks down the built-in file editor in the WordPress dashboard (DISALLOW_FILE_EDIT).', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Allow Username Editing in User Profiles -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_allow_username_change"><?php esc_html_e('Allow Username Editing in User Profiles', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Allow Username Editing Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_allow_username_change" name="gmb_seo_allow_username_change" value="1" <?php checked($sec_opts['allow_username_change'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Adds a quick "Change Username" button directly onto WordPress Users → Profile.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Tool: Change / Rename Username Live -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb-sec-select-user"><?php esc_html_e('Change Login Username Tool', 'gmb-ranker-seo-automation'); ?></label>
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
                                    <?php esc_html_e('Rename Selected User', 'gmb-ranker-seo-automation'); ?>
                                </button>
                            </div>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Safely rename login usernames and author nicenames in the database.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Disable XML-RPC -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_disable_xmlrpc"><?php esc_html_e('Disable XML-RPC', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Disable XML-RPC Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_disable_xmlrpc" name="gmb_seo_disable_xmlrpc" value="1" <?php checked($sec_opts['disable_xmlrpc'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Shuts down XML-RPC and blocks xmlrpc.php requests to neutralize brute-force attacks.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Hide WP Version -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_hide_wp_version"><?php esc_html_e('Hide WordPress Version', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Hide WordPress Version Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_hide_wp_version" name="gmb_seo_hide_wp_version" value="1" <?php checked($sec_opts['hide_wp_version'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Removes generator meta tags and version strings (?ver=) from scripts and styles.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Restrict REST API -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_restrict_rest_api"><?php esc_html_e('Restrict REST API Access', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Restrict REST API Access Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_restrict_rest_api" name="gmb_seo_restrict_rest_api" value="1" <?php checked($sec_opts['restrict_rest_api'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Blocks public access to WordPress REST endpoints for unauthenticated guests.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- SECTION 2: LOGIN PROTECTION -->
                    <div class="gmb-settings-section-divider">
                        <h3 class="gmb-settings-section-title"><?php esc_html_e('2. Login Protection & Access Control', 'gmb-ranker-seo-automation'); ?></h3>
                    </div>

                    <!-- Option: Custom Login URL -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_custom_login_slug"><?php esc_html_e('Custom / Obscured Login URL', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="text" id="gmb_seo_custom_login_slug" name="gmb_seo_custom_login_slug" value="<?php echo esc_attr($sec_opts['custom_login_slug']); ?>" placeholder="<?php esc_attr_e('e.g. portal-login', 'gmb-ranker-seo-automation'); ?>" class="gmb-input gmb-input--max-320" />
                            <p class="gmb-form-help">
                                <?php esc_html_e('Specify a secret login slug. Requests to /wp-login.php without this slug redirect to homepage.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Login Lockout Shield -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_login_lockout_enabled"><?php esc_html_e('Limit Login Attempts & IP Lockout', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Limit Login Attempts Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_login_lockout_enabled" name="gmb_seo_login_lockout_enabled" value="1" <?php checked($sec_opts['login_lockout_enabled'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Enforces rate-limiting on failed login attempts to prevent dictionary attacks.', 'gmb-ranker-seo-automation'); ?>
                            </p>

                            <div class="gmb-flex-wrap-gap-md gmb-mt-12">
                                <div>
                                    <label for="gmb_seo_max_login_attempts" class="gmb-form-sublabel"><?php esc_html_e('Max Failed Retries:', 'gmb-ranker-seo-automation'); ?></label>
                                    <input type="number" id="gmb_seo_max_login_attempts" name="gmb_seo_max_login_attempts" min="3" max="20" value="<?php echo esc_attr($sec_opts['max_login_attempts']); ?>" class="gmb-input gmb-input--width-100" />
                                </div>
                                <div>
                                    <label for="gmb_seo_lockout_duration_mins" class="gmb-form-sublabel"><?php esc_html_e('Lockout Duration (Mins):', 'gmb-ranker-seo-automation'); ?></label>
                                    <input type="number" id="gmb_seo_lockout_duration_mins" name="gmb_seo_lockout_duration_mins" min="5" max="1440" value="<?php echo esc_attr($sec_opts['lockout_duration_mins']); ?>" class="gmb-input gmb-input--width-120" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option: Login Form Honeypot -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_login_honeypot"><?php esc_html_e('Invisible Login Honeypot', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Invisible Login Honeypot Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_login_honeypot" name="gmb_seo_login_honeypot" value="1" <?php checked($sec_opts['login_honeypot'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Injects an invisible trap field into wp-login.php to catch automated attack bots.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Session Expiration -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_session_expiration_hours"><?php esc_html_e('Session Expiration & Remember Me', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <div class="gmb-flex-wrap-gap-md gmb-mb-8">
                                <div>
                                    <label for="gmb_seo_session_expiration_hours" class="gmb-form-sublabel"><?php esc_html_e('Max Session Lifetime (Hours):', 'gmb-ranker-seo-automation'); ?></label>
                                    <input type="number" id="gmb_seo_session_expiration_hours" name="gmb_seo_session_expiration_hours" min="1" max="720" value="<?php echo esc_attr($sec_opts['session_expiration_hours']); ?>" class="gmb-input gmb-input--width-100" />
                                </div>
                                <div class="gmb-mt-18">
                                    <label class="gmb-checkbox-label" for="gmb_seo_hide_remember_me">
                                        <input type="checkbox" id="gmb_seo_hide_remember_me" name="gmb_seo_hide_remember_me" value="1" <?php checked($sec_opts['hide_remember_me'], '1'); ?> />
                                        <?php esc_html_e('Hide "Remember Me" checkbox on login', 'gmb-ranker-seo-automation'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option: Strong Password Policy -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_strong_password_policy"><?php esc_html_e('Strong Password Policy', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Strong Password Policy Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_strong_password_policy" name="gmb_seo_strong_password_policy" value="1" <?php checked($sec_opts['strong_password_policy'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Enforces a minimum password length of 12 characters containing uppercase, lowercase, numbers, and symbols.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Two-Factor Authentication (2FA) -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_2fa"><?php esc_html_e('Administrator Two-Factor Authentication (2FA)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Administrator Two-Factor Authentication Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_enable_2fa" name="gmb_seo_enable_2fa" value="1" <?php checked($sec_opts['enable_2fa'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Sends a 6-digit verification code to administrator email on login.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- SECTION 3: FIREWALL & NETWORK -->
                    <div class="gmb-settings-section-divider">
                        <h3 class="gmb-settings-section-title"><?php esc_html_e('3. Firewall & Network Access Control', 'gmb-ranker-seo-automation'); ?></h3>
                    </div>

                    <!-- Option: 404 Exploit Scanner Auto-Lockout -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_404_exploit_lockout"><?php esc_html_e('WAF 404 Exploit Scanner Lockout', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('WAF 404 Exploit Scanner Lockout Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_404_exploit_lockout" name="gmb_seo_404_exploit_lockout" value="1" <?php checked($sec_opts['exploit_404_lockout'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Bans attacker IPs probing for backdoor scripts and configuration files.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Block Malicious User-Agents -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_block_malicious_user_agents"><?php esc_html_e('Block Vulnerability Scanner User-Agents', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Block Vulnerability Scanner User-Agents Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_block_malicious_user_agents" name="gmb_seo_block_malicious_user_agents" value="1" <?php checked($sec_opts['block_malicious_useragents'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Blocks automated hacker scanners (sqlmap, nikto, wpscan) with 403 Forbidden.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: IP Whitelist -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_ip_whitelist"><?php esc_html_e('IP Whitelist', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <textarea id="gmb_seo_ip_whitelist" name="gmb_seo_ip_whitelist" rows="3" class="gmb-textarea gmb-textarea--code gmb-input--max-480" placeholder="<?php esc_attr_e('One IP per line (e.g. 192.168.1.1)', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea($sec_opts['ip_whitelist']); ?></textarea>
                            <p class="gmb-form-help">
                                <?php esc_html_e('IP addresses listed here will never be locked out.', 'gmb-ranker-seo-automation'); ?> (<?php esc_html_e('Current IP:', 'gmb-ranker-seo-automation'); ?> <code><?php echo esc_html($sec_service ? $sec_service->get_client_ip() : ''); ?></code>)
                            </p>
                        </div>
                    </div>

                    <!-- Option: IP Blacklist -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_ip_blacklist"><?php esc_html_e('IP Blacklist', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <textarea id="gmb_seo_ip_blacklist" name="gmb_seo_ip_blacklist" rows="3" class="gmb-textarea gmb-textarea--code gmb-input--max-480" placeholder="<?php esc_attr_e('One IP per line (e.g. 203.0.113.5)', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea($sec_opts['ip_blacklist']); ?></textarea>
                            <p class="gmb-form-help">
                                <?php esc_html_e('IP addresses listed here will be rejected with 403 Forbidden response on all site pages.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- SECTION 4: HTTP SECURITY HEADERS -->
                    <div class="gmb-settings-section-divider">
                        <h3 class="gmb-settings-section-title"><?php esc_html_e('4. Advanced HTTP Security Headers', 'gmb-ranker-seo-automation'); ?></h3>
                    </div>

                    <!-- Option: Base Security Headers -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_security_headers"><?php esc_html_e('Core Security Headers', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Core Security Headers Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_enable_security_headers" name="gmb_seo_enable_security_headers" value="1" <?php checked($sec_opts['enable_security_headers'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Sends X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN, and X-XSS-Protection.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: HSTS -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_hsts"><?php esc_html_e('Strict Transport Security (HSTS)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Strict Transport Security Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_enable_hsts" name="gmb_seo_enable_hsts" value="1" <?php checked($sec_opts['enable_hsts'], '1'); ?> <?php if (!is_ssl()) { echo 'disabled="disabled"'; } ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Sends Strict-Transport-Security header to instruct browsers to enforce HTTPS connections.', 'gmb-ranker-seo-automation'); ?> <?php echo !is_ssl() ? '<strong class="gmb-text-danger">(' . esc_html__('Requires active SSL certificate', 'gmb-ranker-seo-automation') . ')</strong>' : ''; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: Referrer Policy -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_referrer_policy"><?php esc_html_e('Referrer Policy', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <select id="gmb_seo_referrer_policy" name="gmb_seo_referrer_policy" class="gmb-select gmb-input--max-320">
                                <option value="strict-origin-when-cross-origin" <?php selected($sec_opts['referrer_policy'], 'strict-origin-when-cross-origin'); ?>><?php esc_html_e('strict-origin-when-cross-origin (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="no-referrer-when-downgrade" <?php selected($sec_opts['referrer_policy'], 'no-referrer-when-downgrade'); ?>><?php esc_html_e('no-referrer-when-downgrade', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="same-origin" <?php selected($sec_opts['referrer_policy'], 'same-origin'); ?>><?php esc_html_e('same-origin', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="origin-when-cross-origin" <?php selected($sec_opts['referrer_policy'], 'origin-when-cross-origin'); ?>><?php esc_html_e('origin-when-cross-origin', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Option: Permissions Policy -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_permissions_policy"><?php esc_html_e('Permissions-Policy (Hardware Access)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Permissions Policy Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_permissions_policy" name="gmb_seo_permissions_policy" value="1" <?php checked($sec_opts['permissions_policy'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Sends Permissions-Policy header to disable camera, microphone, and geolocation.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: CSP Frame Ancestors -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_csp_frame_ancestors"><?php esc_html_e('Content Security Policy (CSP)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <label class="gmb-switch" aria-label="<?php esc_attr_e('Content Security Policy Toggle', 'gmb-ranker-seo-automation'); ?>">
                                <input type="checkbox" id="gmb_seo_csp_frame_ancestors" name="gmb_seo_csp_frame_ancestors" value="1" <?php checked($sec_opts['csp_frame_ancestors'], '1'); ?> />
                                <span class="gmb-slider round"></span>
                            </label>
                            <p class="gmb-form-help">
                                <?php esc_html_e('Sends Content-Security-Policy: frame-ancestors \'self\' to prevent iframe clickjacking.', 'gmb-ranker-seo-automation'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Option: COOP -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_coop"><?php esc_html_e('Cross-Origin-Opener-Policy (COOP)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <select id="gmb_seo_enable_coop" name="gmb_seo_enable_coop" class="gmb-select gmb-input--max-320">
                                <option value="same-origin-allow-popups" <?php selected($sec_opts['enable_coop'], 'same-origin-allow-popups'); ?>><?php esc_html_e('same-origin-allow-popups (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="same-origin" <?php selected($sec_opts['enable_coop'], 'same-origin'); ?>><?php esc_html_e('same-origin (Strict Isolation)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="unsafe-none" <?php selected($sec_opts['enable_coop'], 'unsafe-none'); ?>><?php esc_html_e('unsafe-none', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="disabled" <?php selected($sec_opts['enable_coop'], 'disabled'); ?>><?php esc_html_e('Disabled', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Option: CORP -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_corp"><?php esc_html_e('Cross-Origin-Resource-Policy (CORP)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <select id="gmb_seo_enable_corp" name="gmb_seo_enable_corp" class="gmb-select gmb-input--max-320">
                                <option value="same-site" <?php selected($sec_opts['enable_corp'], 'same-site'); ?>><?php esc_html_e('same-site (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="same-origin" <?php selected($sec_opts['enable_corp'], 'same-origin'); ?>><?php esc_html_e('same-origin', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="cross-origin" <?php selected($sec_opts['enable_corp'], 'cross-origin'); ?>><?php esc_html_e('cross-origin', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="disabled" <?php selected($sec_opts['enable_corp'], 'disabled'); ?>><?php esc_html_e('Disabled', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Option: COEP -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_enable_coep"><?php esc_html_e('Cross-Origin-Embedder-Policy (COEP)', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <select id="gmb_seo_enable_coep" name="gmb_seo_enable_coep" class="gmb-select gmb-input--max-320">
                                <option value="unsafe-none" <?php selected($sec_opts['enable_coep'], 'unsafe-none'); ?>><?php esc_html_e('unsafe-none (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="credentialless" <?php selected($sec_opts['enable_coep'], 'credentialless'); ?>><?php esc_html_e('credentialless', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="require-corp" <?php selected($sec_opts['enable_coep'], 'require-corp'); ?>><?php esc_html_e('require-corp (High Security)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="disabled" <?php selected($sec_opts['enable_coep'], 'disabled'); ?>><?php esc_html_e('Disabled', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Option: X-Permitted-Cross-Domain-Policies -->
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_seo_cross_domain_policies"><?php esc_html_e('X-Permitted-Cross-Domain-Policies', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <select id="gmb_seo_cross_domain_policies" name="gmb_seo_cross_domain_policies" class="gmb-select gmb-input--max-320">
                                <option value="none" <?php selected($sec_opts['cross_domain_policies'], 'none'); ?>><?php esc_html_e('none (Recommended - Strict Isolation)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="master-only" <?php selected($sec_opts['cross_domain_policies'], 'master-only'); ?>><?php esc_html_e('master-only', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="by-content-type" <?php selected($sec_opts['cross_domain_policies'], 'by-content-type'); ?>><?php esc_html_e('by-content-type', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="disabled" <?php selected($sec_opts['cross_domain_policies'], 'disabled'); ?>><?php esc_html_e('Disabled', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 5: RECENT SECURITY INCIDENT ACTIVITY LOG -->
                    <?php
                    $recent_logs = $sec_service ? $sec_service->get_recent_security_incidents(5) : array();
                    ?>
                    <div class="gmb-sec-activity-card">
                        <div class="gmb-sec-activity-header">
                            <h4 class="gmb-sec-activity-title">
                                <span class="gmb-sec-activity-live-dot"></span>
                                <?php esc_html_e('Live Security Shield Activity', 'gmb-ranker-seo-automation'); ?>
                            </h4>
                            <span class="gmb-sec-activity-subtitle"><?php esc_html_e('Automatic real-time threat defense log', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <?php if (empty($recent_logs)) : ?>
                            <div class="gmb-sec-activity-empty">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="gmb-icon-inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <strong><?php esc_html_e('All clear:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('No unauthorized administrator attempts, brute-force lockouts, or backdoor scans detected.', 'gmb-ranker-seo-automation'); ?>
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

                    <!-- Footer card bar -->
                    <div class="gmb-settings-footer">
                        <button type="button" class="button gmb-btn--ghost" id="gmb-reset-security-options" data-action="reset-security"><?php esc_html_e('Reset Options', 'gmb-ranker-seo-automation'); ?></button>
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Security Settings', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>

                <!-- Subtab: Content AI -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'ai') ? 'active' : ''; ?>" id="gmb-subtab-ai" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Content AI Settings', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Configure your AI models and API credentials for automated content generation.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                    
                    <div class="gmb-settings-row gmb-settings-row--align-center">
                        <div class="gmb-settings-label-col">
                            <label for="gmb-ai-provider-select-sub"><?php esc_html_e('AI Provider', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <?php $provider = get_option('gmb_ai_provider', 'openrouter'); ?>
                            <select name="gmb_ai_provider" id="gmb-ai-provider-select-sub" class="gmb-select gmb-input--md">
                                <option value="openrouter" <?php selected($provider, 'openrouter'); ?>><?php esc_html_e('OpenRouter (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="groq" <?php selected($provider, 'groq'); ?>><?php esc_html_e('Groq Cloud', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="ollama" <?php selected($provider, 'ollama'); ?>><?php esc_html_e('Ollama (Local AI)', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- OpenRouter Settings Block -->
                    <div id="ai-section-openrouter-sub" class="gmb-ai-section <?php echo ($provider !== 'openrouter') ? 'gmb-hidden' : ''; ?>">
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <span><?php esc_html_e('OpenRouter API Key', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-settings-input-col">
                                <?php $openrouter_key = get_option('gmb_ai_openrouter_key', ''); ?>
                                <input type="password" name="gmb_ai_openrouter_key" value="<?php echo !empty($openrouter_key) ? '********' : ''; ?>" placeholder="<?php esc_attr_e('Enter API Key...', 'gmb-ranker-seo-automation'); ?>" class="gmb-input gmb-input--md" />
                                <p class="gmb-form-help"><?php esc_html_e('Get key from openrouter.ai.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_ai_openrouter_model"><?php esc_html_e('Default Model', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <input type="text" id="gmb_ai_openrouter_model" name="gmb_ai_openrouter_model" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free')); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>
                    </div>

                    <!-- Groq Settings Block -->
                    <div id="ai-section-groq-sub" class="gmb-ai-section <?php echo ($provider !== 'groq') ? 'gmb-hidden' : ''; ?>">
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <span><?php esc_html_e('Groq API Key', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-settings-input-col">
                                <?php $groq_key = get_option('gmb_ai_groq_key', ''); ?>
                                <input type="password" name="gmb_ai_groq_key" value="<?php echo !empty($groq_key) ? '********' : ''; ?>" placeholder="<?php esc_attr_e('Enter API Key...', 'gmb-ranker-seo-automation'); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_ai_groq_model"><?php esc_html_e('Default Model', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <input type="text" id="gmb_ai_groq_model" name="gmb_ai_groq_model" value="<?php echo esc_attr(get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant')); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>
                    </div>

                    <!-- Ollama Settings Block -->
                    <div id="ai-section-ollama-sub" class="gmb-ai-section <?php echo ($provider !== 'ollama') ? 'gmb-hidden' : ''; ?>">
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_ai_ollama_url"><?php esc_html_e('Ollama API Base URL', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <input type="url" id="gmb_ai_ollama_url" name="gmb_ai_ollama_url" value="<?php echo esc_url(get_option('gmb_ai_ollama_url', 'http://localhost:11434')); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_ai_ollama_model"><?php esc_html_e('Default Model', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <input type="text" id="gmb_ai_ollama_model" name="gmb_ai_ollama_model" value="<?php echo esc_attr(get_option('gmb_ai_ollama_model', 'llama3')); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="gmb-settings-footer">
                        <button type="button" class="button gmb-btn--ghost" id="gmb-reset-ai-options" data-action="reset-ai"><?php esc_html_e('Reset Options', 'gmb-ranker-seo-automation'); ?></button>
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>
                
                <!-- Subtab: Edit llms.txt -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'llmstxt') ? 'active' : ''; ?>" id="gmb-subtab-llmstxt" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Edit llms.txt', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Configure your llms.txt file for custom crawling and indexing rules.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                    
                    <div class="gmb-callout gmb-callout--info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#466afa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon-img" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>
                            <?php esc_html_e('Your llms.txt file is available at:', 'gmb-ranker-seo-automation'); ?> 
                            <a href="<?php echo esc_url(site_url('llms.txt')); ?>" target="_blank" class="gmb-help-link font-semibold"><?php echo esc_url(site_url('llms.txt')); ?></a>
                        </span>
                    </div>
                    
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <?php esc_html_e('Select Post Types', 'gmb-ranker-seo-automation'); ?>
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
                                <button type="button" class="button button-small" id="llms-select-all-types"><?php esc_html_e('Select / Deselect All', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                            <div class="gmb-checkbox-grid-2col">
                                <?php foreach ($all_post_types as $pt) : 
                                    if ($pt->name === 'attachment') continue;
                                ?>
                                    <label class="gmb-checkbox-item">
                                        <input type="checkbox" class="llms-post-type-checkbox" name="gmb_llms_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $included_types, true)); ?> />
                                        <?php echo esc_html($pt->label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="gmb-form-help"><?php esc_html_e('Select post types included in llms.txt.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>
                    
                    <div class="gmb-settings-row">
                        <div class="gmb-settings-label-col">
                            <?php esc_html_e('Select Taxonomies', 'gmb-ranker-seo-automation'); ?>
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
                                <button type="button" class="button button-small" id="llms-select-all-taxs"><?php esc_html_e('Select / Deselect All', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                            <div class="gmb-checkbox-grid-2col">
                                <?php foreach ($all_taxs as $tax) : ?>
                                    <label class="gmb-checkbox-item">
                                        <input type="checkbox" class="llms-tax-checkbox" name="gmb_llms_taxonomies[]" value="<?php echo esc_attr($tax->name); ?>" <?php checked(in_array($tax->name, $included_taxs, true)); ?> />
                                        <?php echo esc_html($tax->label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="gmb-form-help"><?php esc_html_e('Select taxonomies included in llms.txt.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>
                    
                    <div class="gmb-settings-row gmb-settings-row--align-center">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_llms_limit"><?php esc_html_e('Posts/Terms Limit', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="number" id="gmb_llms_limit" name="gmb_llms_limit" value="<?php echo esc_attr(get_option('gmb_llms_limit', '100')); ?>" class="gmb-input gmb-input--sm" />
                            <p class="gmb-form-help"><?php esc_html_e('Maximum number of links to include for each content type.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>

                    <div class="gmb-settings-row gmb-settings-row--align-center">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_llms_title"><?php esc_html_e('Feed Title', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <input type="text" id="gmb_llms_title" name="gmb_llms_title" value="<?php echo esc_attr(get_option('gmb_llms_title', get_bloginfo('name'))); ?>" class="gmb-input" />
                        </div>
                    </div>

                    <div class="gmb-settings-row gmb-settings-row--align-center">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_llms_desc"><?php esc_html_e('Feed Description', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <textarea id="gmb_llms_desc" name="gmb_llms_desc" rows="2" class="gmb-input"><?php echo esc_textarea(get_option('gmb_llms_desc', get_bloginfo('description'))); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="gmb-settings-row gmb-settings-row--noborder">
                        <div class="gmb-settings-label-col">
                            <label for="gmb_llms_additional_content"><?php esc_html_e('Additional Content', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        <div class="gmb-settings-input-col">
                            <textarea id="gmb_llms_additional_content" name="gmb_llms_additional_content" placeholder="<?php esc_attr_e('e.g. - [Contact Support](https://example.com/support): Reach our helpdesk.', 'gmb-ranker-seo-automation'); ?>" class="gmb-input gmb-textarea--code" rows="5"><?php echo esc_textarea(get_option('gmb_llms_additional_content', '')); ?></textarea>
                            <p class="gmb-form-help"><?php esc_html_e('Add extra text or markdown links to append to your llms.txt file manually.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>

                    <div class="gmb-settings-footer">
                        <button type="button" class="button gmb-btn--ghost" id="llms-reset-options-btn" data-action="reset-llms"><?php esc_html_e('Reset Options', 'gmb-ranker-seo-automation'); ?></button>
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>

                <!-- Subtab: Table of Contents -->
                <div class="gmb-subtab-panel <?php echo ($active_sub === 'toc') ? 'active' : ''; ?>" id="gmb-subtab-toc" role="tabpanel">
                    <div class="gmb-settings-panel-header">
                        <h2 class="gmb-heading-2"><?php esc_html_e('Table of Contents Settings', 'gmb-ranker-seo-automation'); ?></h2>
                        <p class="gmb-text-muted"><?php esc_html_e('Configure automatic injection and display preferences for post Tables of Contents.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>

                    <div class="gmb-card-settings-list">
                        <!-- Title Form Row -->
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_toc_title"><?php esc_html_e('TOC Box Title', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <input type="text" id="gmb_toc_title" name="gmb_toc_title" value="<?php echo esc_attr(get_option('gmb_toc_title', 'Table of Contents')); ?>" class="gmb-input gmb-input--md" />
                            </div>
                        </div>

                        <!-- Min Headings Form Row -->
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_toc_min_headings"><?php esc_html_e('Minimum Headings Trigger', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <?php $min_h = get_option('gmb_toc_min_headings', '2'); ?>
                                <select id="gmb_toc_min_headings" name="gmb_toc_min_headings" class="gmb-select gmb-input--sm">
                                    <option value="2" <?php selected($min_h, '2'); ?>><?php esc_html_e('2 Headings', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="3" <?php selected($min_h, '3'); ?>><?php esc_html_e('3 Headings', 'gmb-ranker-seo-automation'); ?></option>
                                    <option value="4" <?php selected($min_h, '4'); ?>><?php esc_html_e('4 Headings', 'gmb-ranker-seo-automation'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Heading Levels Form Row -->
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <?php esc_html_e('Heading Levels to Include', 'gmb-ranker-seo-automation'); ?>
                            </div>
                            <div class="gmb-settings-input-col">
                                <?php 
                                $toc_levels = get_option('gmb_toc_levels', array('h2', 'h3'));
                                if (!is_array($toc_levels)) { $toc_levels = array('h2', 'h3'); }
                                ?>
                                <div class="gmb-flex-center-gap-md">
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h1" <?php checked(in_array('h1', $toc_levels, true)); ?> /> H1</label>
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h2" <?php checked(in_array('h2', $toc_levels, true)); ?> /> H2</label>
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h3" <?php checked(in_array('h3', $toc_levels, true)); ?> /> H3</label>
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h4" <?php checked(in_array('h4', $toc_levels, true)); ?> /> H4</label>
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h5" <?php checked(in_array('h5', $toc_levels, true)); ?> /> H5</label>
                                    <label class="gmb-checkbox-label"><input type="checkbox" name="gmb_toc_levels[]" value="h6" <?php checked(in_array('h6', $toc_levels, true)); ?> /> H6</label>
                                </div>
                            </div>
                        </div>

                        <!-- Auto Insert Form Row -->
                        <div class="gmb-settings-row gmb-settings-row--align-center">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_toc_auto_insert"><?php esc_html_e('Auto-prepend TOC Box', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <label class="gmb-switch" aria-label="<?php esc_attr_e('Auto Prepend TOC Box Toggle', 'gmb-ranker-seo-automation'); ?>">
                                    <input type="checkbox" id="gmb_toc_auto_insert" name="gmb_toc_auto_insert" value="1" <?php checked(get_option('gmb_toc_auto_insert', '1'), '1'); ?> />
                                    <span class="gmb-slider round"></span>
                                </label>
                                <p class="gmb-form-help"><?php esc_html_e('Prepend Table of Contents automatically to post body.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>

                        <!-- Collapsible Form Row -->
                        <div class="gmb-settings-row gmb-settings-row--align-center gmb-settings-row--noborder">
                            <div class="gmb-settings-label-col">
                                <label for="gmb_toc_collapsible"><?php esc_html_e('Enable Show/Hide Toggle', 'gmb-ranker-seo-automation'); ?></label>
                            </div>
                            <div class="gmb-settings-input-col">
                                <label class="gmb-switch" aria-label="<?php esc_attr_e('Enable Show/Hide Toggle', 'gmb-ranker-seo-automation'); ?>">
                                    <input type="checkbox" id="gmb_toc_collapsible" name="gmb_toc_collapsible" value="1" <?php checked(get_option('gmb_toc_collapsible', '1'), '1'); ?> />
                                    <span class="gmb-slider round"></span>
                                </label>
                                <p class="gmb-form-help"><?php esc_html_e('Allows readers to collapse or expand the table of contents box.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="gmb-settings-footer justify-end">
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Username Modal -->
        <div id="gmb-change-username-modal" class="gmb-username-modal-overlay" role="dialog" aria-hidden="true" aria-labelledby="gmb-username-modal-title">
            <div class="gmb-username-modal-card">
                <div class="gmb-username-modal-header">
                    <h3 class="gmb-username-modal-title" id="gmb-username-modal-title">
                        <?php esc_html_e('Change / Rename Account Username', 'gmb-ranker-seo-automation'); ?>
                    </h3>
                    <button type="button" id="gmb-close-username-modal" class="gmb-username-modal-close" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <p class="gmb-username-modal-desc">
                    <?php esc_html_e('Safely rename any user login name. All existing posts, comments, pages, and capabilities will be preserved seamlessly.', 'gmb-ranker-seo-automation'); ?>
                </p>
                <div class="gmb-username-modal-field">
                    <label for="gmb-modal-user-select" class="gmb-username-modal-label"><?php esc_html_e('Selected Account', 'gmb-ranker-seo-automation'); ?></label>
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
                    <label for="gmb-modal-current-username" class="gmb-username-modal-label"><?php esc_html_e('Current Login Username', 'gmb-ranker-seo-automation'); ?></label>
                    <input type="text" id="gmb-modal-current-username" disabled class="gmb-username-modal-input-current" />
                </div>
                <div class="gmb-username-modal-field--last">
                    <label for="gmb-modal-new-username" class="gmb-username-modal-label"><?php esc_html_e('New Secure Login Username', 'gmb-ranker-seo-automation'); ?></label>
                    <input type="text" id="gmb-modal-new-username" placeholder="<?php esc_attr_e('e.g. custom_admin_name', 'gmb-ranker-seo-automation'); ?>" class="gmb-username-modal-input-new" />
                    <span id="gmb-modal-username-error" class="gmb-username-modal-error"></span>
                </div>
                <div class="gmb-username-modal-footer">
                    <button type="button" id="gmb-cancel-username-modal" class="button button-secondary gmb-btn-font-600"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" id="gmb-submit-username-modal" class="button button-primary gmb-btn--modal-submit"><?php esc_html_e('Update Username', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>
