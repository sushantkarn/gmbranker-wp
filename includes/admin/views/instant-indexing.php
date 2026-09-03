<?php
/**
 * Instant Indexing Subtab View
 *
 * Enterprise-grade presentation layer for Google Indexing API
 * and IndexNow protocol management, manual submission console, and logs.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '');

if ($current_page === 'gmb-ranker-instant-indexing') : 
    $instant_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'console';
    if (!in_array($instant_tab, array('console', 'google_settings', 'bing_settings', 'indexnow_history'), true)) {
        $instant_tab = 'console';
    }

    $google_json_key = get_option('gmb_ranker_google_json_key', '');
    $parsed_google_key = !empty($google_json_key) ? json_decode($google_json_key, true) : null;
    $google_configured = is_array($parsed_google_key) && !empty($parsed_google_key['client_email']) && !empty($parsed_google_key['private_key']);
    $google_email = ($google_configured && isset($parsed_google_key['client_email'])) ? $parsed_google_key['client_email'] : '';
    $google_project = ($google_configured && isset($parsed_google_key['project_id'])) ? $parsed_google_key['project_id'] : '';

    $google_post_types = get_option('gmb_ranker_google_post_types', array('post', 'page'));
    if (!is_array($google_post_types)) {
        $google_post_types = array('post', 'page');
    }

    $indexnow_post_types = get_option('gmb_ranker_indexnow_post_types', array('post', 'page'));
    if (!is_array($indexnow_post_types)) {
        $indexnow_post_types = array('post', 'page');
    }

    $indexnow_key = class_exists('GMB_Ranker_SEO_Instant_Indexing') ? GMB_Ranker_SEO_Instant_Indexing::get_indexnow_key() : '';
    $key_location = class_exists('GMB_Ranker_SEO_Instant_Indexing') ? GMB_Ranker_SEO_Instant_Indexing::get_key_location() : '';
    $limits       = class_exists('GMB_Ranker_SEO_Instant_Indexing') ? GMB_Ranker_SEO_Instant_Indexing::get_limits() : array(
        'publishperday' => 200, 'publishperday_max' => 200,
        'permin' => 380, 'permin_max' => 380,
        'metapermin' => 180, 'metapermin_max' => 180,
        'indexnowperday' => 10000, 'indexnowperday_max' => 10000
    );
    
    $public_post_types = get_post_types(array('public' => true), 'objects');
    if (isset($public_post_types['attachment'])) {
        unset($public_post_types['attachment']);
    }

    // Pre-filled URL if arriving from row action
    $prefill_url = isset($_GET['url']) ? esc_url_raw(wp_unslash($_GET['url'])) : home_url('/');
    $prefill_action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : ($google_configured ? 'update' : 'bing_submit');
?>
<div class="rm-tab-content active" id="rm-tab-instant-indexing">
    <div class="gmb-indexing-container">
        
        <!-- Header -->
        <div class="gmb-indexing-header">
            <div>
                <h2 class="gmb-indexing-title"><?php esc_html_e('Instant Indexing API Hub', 'gmb-ranker-seo-automation'); ?></h2>
                <p class="gmb-indexing-desc"><?php esc_html_e('Directly notify Google, Bing, Yandex, Seznam, and IndexNow endpoints when site pages are published or updated.', 'gmb-ranker-seo-automation'); ?></p>
            </div>
            <div>
                <?php if ($google_configured && !empty($indexnow_key)) : ?>
                    <span class="gmb-status-pill gmb-status-pill--success">
                        <span class="gmb-status-dot"></span>
                        <?php esc_html_e('Google & IndexNow Active', 'gmb-ranker-seo-automation'); ?>
                    </span>
                <?php elseif (!empty($indexnow_key)) : ?>
                    <span class="gmb-status-pill gmb-status-pill--info">
                        <span class="gmb-status-dot"></span>
                        <?php esc_html_e('IndexNow Active · Google Pending', 'gmb-ranker-seo-automation'); ?>
                    </span>
                <?php else : ?>
                    <span class="gmb-status-pill gmb-status-pill--danger">
                        <span class="gmb-status-dot"></span>
                        <?php esc_html_e('Not Configured', 'gmb-ranker-seo-automation'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sub-Navigation Tabs -->
        <div class="gmb-indexing-tabs" role="tablist" aria-label="<?php esc_attr_e('Instant Indexing Subtabs', 'gmb-ranker-seo-automation'); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=console')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'console') ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo ($instant_tab === 'console') ? 'true' : 'false'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="gmb-icon gmb-icon--sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?php esc_html_e('Console', 'gmb-ranker-seo-automation'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'google_settings') ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo ($instant_tab === 'google_settings') ? 'true' : 'false'; ?>">
                <?php if ($google_configured) : ?><span class="gmb-text-success">&check;</span><?php endif; ?>
                <?php esc_html_e('Google API Settings', 'gmb-ranker-seo-automation'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=bing_settings')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'bing_settings') ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo ($instant_tab === 'bing_settings') ? 'true' : 'false'; ?>">
                <span class="gmb-text-success">&check;</span>
                <?php esc_html_e('IndexNow API Settings', 'gmb-ranker-seo-automation'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=indexnow_history')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'indexnow_history') ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo ($instant_tab === 'indexnow_history') ? 'true' : 'false'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="gmb-icon gmb-icon--sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?php esc_html_e('Submission History', 'gmb-ranker-seo-automation'); ?>
            </a>
        </div>

        <!-- TAB 1: CONSOLE -->
        <?php if ($instant_tab === 'console') : ?>
            <div class="gmb-indexing-grid" role="tabpanel">
                
                <!-- Left Column: Submission Console -->
                <div class="gmb-indexing-main">
                    <form id="gmb-instant-indexing-form">
                        <?php wp_nonce_field('gmb_instant_indexing_nonce', 'gmb_instant_nonce'); ?>
                        
                        <div class="gmb-mb-14">
                            <label for="gmb_indexing_urls" class="gmb-label-bold-sm"><?php esc_html_e('URLs (one per line, HTTP/HTTPS absolute links):', 'gmb-ranker-seo-automation'); ?></label>
                            <textarea id="gmb_indexing_urls" rows="5" placeholder="<?php esc_attr_e("https://example.com/\nhttps://example.com/blog-post/", 'gmb-ranker-seo-automation'); ?>" class="gmb-indexing-textarea"><?php echo esc_textarea($prefill_url); ?></textarea>
                        </div>

                        <div class="gmb-engine-selector-box">
                            <label class="gmb-label-bold-md"><?php esc_html_e('Select Indexing Action & Target Engine:', 'gmb-ranker-seo-automation'); ?></label>
                            
                            <div class="gmb-flex-col-gap-10">
                                <?php if ($google_configured) : ?>
                                    <div class="gmb-engine-card gmb-engine-card--connected">
                                        <div class="gmb-flex-between-mb-8">
                                            <strong class="gmb-text-success-flex">
                                                <span class="gmb-status-dot-sm-green"></span>
                                                <?php esc_html_e('Google Indexing API (Connected)', 'gmb-ranker-seo-automation'); ?>
                                            </strong>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-link-sm-blue"><?php esc_html_e('Settings →', 'gmb-ranker-seo-automation'); ?></a>
                                        </div>
                                        <div class="gmb-col-gap-6-pl-4">
                                            <label class="gmb-checkbox-label" for="gmb_action_google_update">
                                                <input type="radio" id="gmb_action_google_update" name="gmb_api_action" value="update" <?php checked($prefill_action, 'update'); ?> />
                                                <span><strong><?php esc_html_e('Google:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('Publish / update URL (Request Googlebot Crawl)', 'gmb-ranker-seo-automation'); ?></span>
                                            </label>
                                            <label class="gmb-checkbox-label" for="gmb_action_google_remove">
                                                <input type="radio" id="gmb_action_google_remove" name="gmb_api_action" value="remove" <?php checked($prefill_action, 'remove'); ?> />
                                                <span><strong><?php esc_html_e('Google:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('Notify URL Removal', 'gmb-ranker-seo-automation'); ?></span>
                                            </label>
                                            <label class="gmb-checkbox-label" for="gmb_action_google_status">
                                                <input type="radio" id="gmb_action_google_status" name="gmb_api_action" value="getstatus" <?php checked($prefill_action, 'getstatus'); ?> />
                                                <span><strong><?php esc_html_e('Google:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('Get URL Status & Last Crawl Metadata', 'gmb-ranker-seo-automation'); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="gmb-engine-card gmb-engine-card--warning">
                                        <div class="gmb-flex-between-wrap-8">
                                            <div class="gmb-flex-center-gap-6">
                                                <span class="gmb-status-dot-sm-orange"></span>
                                                <strong class="gmb-text-main"><?php esc_html_e('Google Indexing API', 'gmb-ranker-seo-automation'); ?></strong>
                                                <span class="gmb-badge-warn-sm"><?php esc_html_e('Not Connected', 'gmb-ranker-seo-automation'); ?></span>
                                            </div>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-small gmb-badge-info-sm"><?php esc_html_e('Connect Key →', 'gmb-ranker-seo-automation'); ?></a>
                                        </div>
                                        <p class="gmb-text-desc-sm">
                                            <strong><?php esc_html_e('Google Instant Indexing:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('Connecting a Google Cloud Service Account enables direct URL crawl notifications to Google Search servers.', 'gmb-ranker-seo-automation'); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- IndexNow Action Option -->
                                <div class="gmb-engine-card">
                                    <label class="gmb-checkbox-label" for="gmb_action_indexnow_submit">
                                        <input type="radio" id="gmb_action_indexnow_submit" name="gmb_api_action" value="bing_submit" <?php checked($prefill_action, 'bing_submit'); ?> />
                                        <span><strong><?php esc_html_e('IndexNow Protocol:', 'gmb-ranker-seo-automation'); ?></strong> <?php esc_html_e('Submit URL (Syndicate to Bing, Yandex, Seznam, Naver)', 'gmb-ranker-seo-automation'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="gmb-indexing-btn-row">
                            <button type="submit" id="gmb-indexing-submit-btn" class="gmb-btn-index-now">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span id="gmb-submit-btn-label"><?php echo ($prefill_action === 'bing_submit') ? esc_html__('Submit to IndexNow', 'gmb-ranker-seo-automation') : esc_html__('Submit to Google API', 'gmb-ranker-seo-automation'); ?></span>
                            </button>
                            <span id="gmb-indexing-spinner" class="gmb-text-muted gmb-text-sm"><?php esc_html_e('Dispatching request to indexing servers...', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    </form>

                    <!-- Live Response Box -->
                    <div id="gmb-indexing-response-box" class="gmb-response-box">
                        <div id="gmb-indexing-response-header" class="gmb-response-header">
                            <strong id="gmb-indexing-response-title" class="gmb-text-main"><?php esc_html_e('API Response', 'gmb-ranker-seo-automation'); ?></strong>
                            <button type="button" id="gmb-toggle-raw-json-btn" class="gmb-btn-raw-toggle">
                                <?php esc_html_e('Toggle Raw JSON', 'gmb-ranker-seo-automation'); ?>
                            </button>
                        </div>
                        <div class="gmb-response-body">
                            <div id="gmb-indexing-response-msg" class="gmb-response-text"></div>
                            <textarea id="gmb-indexing-raw-json" readonly class="gmb-raw-response-textarea"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Quota & Verification Panel -->
                <div class="gmb-indexing-sidebar">
                    
                    <!-- Quota Card -->
                    <div class="gmb-quota-card">
                        <h3 class="gmb-card-header-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="gmb-icon-primary-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php esc_html_e('Remaining Quota Limits', 'gmb-ranker-seo-automation'); ?>
                        </h3>

                        <div class="gmb-quota-grid">
                            <div class="gmb-quota-box">
                                <span class="gmb-quota-label"><?php esc_html_e('Publish Requests/Day', 'gmb-ranker-seo-automation'); ?></span>
                                <div class="gmb-quota-numbers">
                                    <span id="gmb-limit-publishperday" class="gmb-quota-val"><?php echo absint($limits['publishperday']); ?></span>
                                    <span class="gmb-quota-max">/ <?php echo absint($limits['publishperday_max']); ?></span>
                                </div>
                            </div>
                            <div class="gmb-quota-box">
                                <span class="gmb-quota-label"><?php esc_html_e('Requests/Minute', 'gmb-ranker-seo-automation'); ?></span>
                                <div class="gmb-quota-numbers">
                                    <span id="gmb-limit-permin" class="gmb-quota-val"><?php echo absint($limits['permin']); ?></span>
                                    <span class="gmb-quota-max">/ <?php echo absint($limits['permin_max']); ?></span>
                                </div>
                            </div>
                            <div class="gmb-quota-box">
                                <span class="gmb-quota-label"><?php esc_html_e('Metadata Requests/Min', 'gmb-ranker-seo-automation'); ?></span>
                                <div class="gmb-quota-numbers">
                                    <span id="gmb-limit-metapermin" class="gmb-quota-val"><?php echo absint($limits['metapermin']); ?></span>
                                    <span class="gmb-quota-max">/ <?php echo absint($limits['metapermin_max']); ?></span>
                                </div>
                            </div>
                            <div class="gmb-quota-box">
                                <span class="gmb-quota-label"><?php esc_html_e('IndexNow Requests/Day', 'gmb-ranker-seo-automation'); ?></span>
                                <div class="gmb-quota-numbers">
                                    <span id="gmb-limit-indexnowperday" class="gmb-quota-val"><?php echo absint($limits['indexnowperday']); ?></span>
                                    <span class="gmb-quota-max">/ <?php echo absint($limits['indexnowperday_max']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verification & Endpoints Card -->
                    <div class="gmb-quota-card">
                        <h3 class="gmb-heading-4"><?php esc_html_e('Indexing Protocol Status', 'gmb-ranker-seo-automation'); ?></h3>
                        <div class="gmb-protocol-status-list">
                            <div class="gmb-protocol-status-row">
                                <span class="gmb-protocol-name"><?php esc_html_e('IndexNow Protocol', 'gmb-ranker-seo-automation'); ?></span>
                                <span class="gmb-protocol-badge--verified"><?php esc_html_e('Key Active ✓', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <div class="gmb-protocol-status-row">
                                <span class="gmb-protocol-name"><?php esc_html_e('Google Cloud Auth', 'gmb-ranker-seo-automation'); ?></span>
                                <?php if ($google_configured) : ?>
                                    <span class="gmb-protocol-badge--verified"><?php esc_html_e('Connected ✓', 'gmb-ranker-seo-automation'); ?></span>
                                <?php else : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-protocol-badge--warning">
                                        <?php esc_html_e('Not Connected · Connect →', 'gmb-ranker-seo-automation'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="gmb-protocol-status-row">
                                <span class="gmb-protocol-name"><?php esc_html_e('Auto-Dispatch on Publish', 'gmb-ranker-seo-automation'); ?></span>
                                <span class="gmb-protocol-badge--verified"><?php esc_html_e('Enabled ✓', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>

        <!-- TAB 2: GOOGLE API SETTINGS -->
        <?php if ($instant_tab === 'google_settings') : ?>
            <form method="post" action="options.php" enctype="multipart/form-data" role="tabpanel">
                <?php settings_fields('gmb_ranker_google_indexing_group'); ?>

                <div class="gmb-settings-cards-stack">
                    
                    <!-- Connection Status Card -->
                    <?php if ($google_configured) : ?>
                        <div class="gmb-service-account-card-ok">
                            <div class="gmb-flex-center-gap-sm">
                                <span class="gmb-status-dot-green"></span>
                                <div>
                                    <strong class="gmb-service-account-name"><?php esc_html_e('Google Service Account Connected & Authenticated ✓', 'gmb-ranker-seo-automation'); ?></strong>
                                    <div class="gmb-service-account-meta">
                                        <span><?php esc_html_e('Email:', 'gmb-ranker-seo-automation'); ?> <code class="gmb-service-account-badge"><?php echo esc_html($google_email); ?></code></span>
                                        <button type="button" class="gmb-btn-copy-email" id="gmb-copy-service-email-btn" data-email="<?php echo esc_attr($google_email); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                            <?php esc_html_e('Copy Email', 'gmb-ranker-seo-automation'); ?>
                                        </button>
                                        <?php if (!empty($google_project)) : ?>
                                            <span class="gmb-text-muted">· <?php esc_html_e('Project:', 'gmb-ranker-seo-automation'); ?> <strong><?php echo esc_html($google_project); ?></strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gmb-flex-gap-sm gmb-items-center">
                                <a href="https://search.google.com/search-console" target="_blank" rel="noopener noreferrer" class="gmb-btn-console-link">
                                    <?php esc_html_e('Search Console', 'gmb-ranker-seo-automation'); ?> &nearr;
                                </a>
                                <span class="gmb-key-status-badge-ok"><?php esc_html_e('Active', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="gmb-service-account-card-warn">
                            <div class="gmb-flex-center-gap-sm">
                                <span class="gmb-status-dot-orange"></span>
                                <div>
                                    <strong class="gmb-service-account-name gmb-text-warning-dark"><?php esc_html_e('Google Cloud Auth: Not Connected', 'gmb-ranker-seo-automation'); ?></strong>
                                    <span class="gmb-text-warning-sub"><?php esc_html_e('Upload or paste your Google Cloud Service Account JSON key below.', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                            </div>
                            <span class="gmb-key-status-badge-warn"><?php esc_html_e('Setup Required', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="gmb-card">
                        <div class="gmb-key-field-header">
                            <label for="gmb_ranker_google_json_key_field" class="gmb-key-field-title"><?php esc_html_e('Google Service Account JSON Key', 'gmb-ranker-seo-automation'); ?></label>
                            <div>
                                <input type="file" id="gmb_google_json_file_picker" accept=".json,application/json" class="gmb-hidden-file-input" />
                                <button type="button" id="gmb-trigger-file-upload-btn" class="gmb-btn gmb-btn-secondary gmb-btn-action-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <?php esc_html_e('Upload Service Account (.json) File', 'gmb-ranker-seo-automation'); ?>
                                </button>
                            </div>
                        </div>
                        <p class="gmb-text-muted"><?php esc_html_e('Upload your Google Cloud JSON key file or paste new JSON contents below. Existing credentials are held securely on the server.', 'gmb-ranker-seo-automation'); ?></p>
                        <div id="gmb_google_json_upload_badge" class="gmb-copy-feedback-box"></div>
                        <textarea id="gmb_ranker_google_json_key_field" name="gmb_ranker_google_json_key" rows="5" placeholder="<?php echo $google_configured ? esc_attr__('Credentials saved securely. Paste new JSON key here to replace existing key...', 'gmb-ranker-seo-automation') : esc_attr__('{ "type": "service_account", "project_id": "...", "private_key": "...", "client_email": "..." }', 'gmb-ranker-seo-automation'); ?>" class="gmb-key-input-box"></textarea>
                    </div>

                    <div class="gmb-card">
                        <label class="gmb-form-label"><?php esc_html_e('Auto-Submit Posts to Google', 'gmb-ranker-seo-automation'); ?></label>
                        <p class="gmb-text-muted"><?php esc_html_e('Automatically notify Google Indexing API whenever a post of these types is published or updated.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-flex-gap-16-wrap">
                            <?php foreach ($public_post_types as $pt) : ?>
                                <label class="gmb-checkbox-label-inline" for="gmb_google_pt_<?php echo esc_attr($pt->name); ?>">
                                    <input type="checkbox" id="gmb_google_pt_<?php echo esc_attr($pt->name); ?>" name="gmb_ranker_google_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $google_post_types, true)); ?> />
                                    <span><?php echo esc_html($pt->label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Setup Instructions Guide -->
                    <div class="gmb-info-instruction-box">
                        <h4 class="gmb-info-instruction-title"><?php esc_html_e('Step-by-Step Google Setup Guide:', 'gmb-ranker-seo-automation'); ?></h4>
                        <ol class="gmb-info-instruction-list">
                            <li><?php esc_html_e('Go to Google Cloud Console and select or create a project.', 'gmb-ranker-seo-automation'); ?></li>
                            <li><?php esc_html_e('Enable Web Search Indexing API in API Library.', 'gmb-ranker-seo-automation'); ?></li>
                            <li><?php esc_html_e('Create a Service Account and generate a new JSON key.', 'gmb-ranker-seo-automation'); ?></li>
                            <li><?php esc_html_e('Copy the Service Account email and add it to your Google Search Console property as an Owner.', 'gmb-ranker-seo-automation'); ?></li>
                            <li><?php esc_html_e('Upload or paste the JSON key content above and click Save Google Settings.', 'gmb-ranker-seo-automation'); ?></li>
                        </ol>
                    </div>

                    <div class="gmb-flex-end">
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Google Settings', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <!-- TAB 3: INDEXNOW API SETTINGS -->
        <?php if ($instant_tab === 'bing_settings') : ?>
            <form method="post" action="options.php" role="tabpanel">
                <?php settings_fields('gmb_ranker_bing_indexing_group'); ?>

                <div class="gmb-settings-cards-stack">
                    <div class="gmb-card">
                        <label class="gmb-form-label"><?php esc_html_e('Auto-Submit Posts to IndexNow', 'gmb-ranker-seo-automation'); ?></label>
                        <p class="gmb-text-muted"><?php esc_html_e('Submit posts from these types automatically to the IndexNow protocol (Bing, Yandex, Seznam, Naver) upon publish.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-flex-gap-16-wrap">
                            <?php foreach ($public_post_types as $pt) : ?>
                                <label class="gmb-checkbox-label-inline" for="gmb_indexnow_pt_<?php echo esc_attr($pt->name); ?>">
                                    <input type="checkbox" id="gmb_indexnow_pt_<?php echo esc_attr($pt->name); ?>" name="gmb_ranker_indexnow_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $indexnow_post_types, true)); ?> />
                                    <span><?php echo esc_html($pt->label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="gmb-card">
                        <label for="gmb_indexnow_key_field" class="gmb-form-label"><?php esc_html_e('IndexNow API Key', 'gmb-ranker-seo-automation'); ?></label>
                        <p class="gmb-text-muted-xs-8"><?php esc_html_e('This key verifies site ownership with participating search engine crawlers.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-flex-gap-sm">
                            <input type="text" id="gmb_indexnow_key_field" name="gmb_ranker_indexnow_key" readonly value="<?php echo esc_attr($indexnow_key); ?>" class="gmb-input-key-full" />
                            <button type="button" id="gmb-reset-indexnow-key-btn" class="gmb-btn gmb-btn-secondary"><?php esc_html_e('Change Key', 'gmb-ranker-seo-automation'); ?></button>
                        </div>
                    </div>

                    <div class="gmb-card">
                        <label class="gmb-form-label"><?php esc_html_e('IndexNow Key Location Endpoint', 'gmb-ranker-seo-automation'); ?></label>
                        <p class="gmb-text-muted-xs-8"><?php esc_html_e('Search engine bots query this virtual endpoint to verify site ownership.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-flex-center-gap-sm">
                            <code id="gmb_indexnow_key_location" class="gmb-key-file-display"><?php echo esc_url($key_location); ?></code>
                            <a id="gmb_indexnow_check_key_link" href="<?php echo esc_url($key_location); ?>" target="_blank" rel="noopener noreferrer" class="gmb-btn gmb-btn-secondary gmb-btn-view-key"><?php esc_html_e('Check Key', 'gmb-ranker-seo-automation'); ?> &nearr;</a>
                        </div>
                    </div>

                    <div class="gmb-flex-end">
                        <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save IndexNow Settings', 'gmb-ranker-seo-automation'); ?>" />
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <!-- TAB 4: INDEXNOW HISTORY -->
        <?php if ($instant_tab === 'indexnow_history') : 
            $history_logs = get_option('gmb_ranker_indexnow_log', array());
            if (!is_array($history_logs)) {
                $history_logs = array();
            }
            $history_logs = array_reverse($history_logs);
        ?>
            <div role="tabpanel">
                <div class="gmb-history-header-row">
                    <p class="gmb-history-header-desc"><?php esc_html_e('Recent Instant Indexing URL submissions and API response statuses.', 'gmb-ranker-seo-automation'); ?></p>
                    <?php if (!empty($history_logs)) : ?>
                        <button type="button" id="gmb-clear-history-btn" class="gmb-btn gmb-btn-secondary gmb-btn-clear-history"><?php esc_html_e('Clear History', 'gmb-ranker-seo-automation'); ?></button>
                    <?php endif; ?>
                </div>

                <div class="gmb-history-table-card">
                    <table class="gmb-indexing-history-table">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e('Time', 'gmb-ranker-seo-automation'); ?></th>
                                <th scope="col"><?php esc_html_e('Submitted URL', 'gmb-ranker-seo-automation'); ?></th>
                                <th scope="col"><?php esc_html_e('Engine', 'gmb-ranker-seo-automation'); ?></th>
                                <th scope="col"><?php esc_html_e('Type', 'gmb-ranker-seo-automation'); ?></th>
                                <th scope="col"><?php esc_html_e('Response Status', 'gmb-ranker-seo-automation'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history_logs)) : ?>
                                <?php foreach ($history_logs as $log) : 
                                    $log_time = isset($log['time']) ? intval($log['time']) : time();
                                    $log_url  = isset($log['url']) ? esc_url_raw($log['url']) : '';
                                    $log_eng  = isset($log['engine']) ? sanitize_key($log['engine']) : 'indexnow';
                                    $log_type = !empty($log['manual_submission']) ? __('Manual', 'gmb-ranker-seo-automation') : __('Automatic', 'gmb-ranker-seo-automation');
                                    $log_stat = isset($log['status']) ? sanitize_text_field($log['status']) : '';
                                ?>
                                    <tr>
                                        <td class="gmb-th-time">
                                            <strong><?php echo esc_html(wp_date('Y-m-d H:i:s', $log_time)); ?></strong><br>
                                            <span class="gmb-text-muted-11"><?php echo esc_html(human_time_diff($log_time) . ' ' . __('ago', 'gmb-ranker-seo-automation')); ?></span>
                                        </td>
                                        <td class="gmb-td-history-url">
                                            <a href="<?php echo esc_url($log_url); ?>" target="_blank" rel="noopener noreferrer" class="gmb-link-primary"><?php echo esc_html($log_url); ?></a>
                                        </td>
                                        <td>
                                            <?php if ($log_eng === 'google') : ?>
                                                <span class="gmb-status-pill gmb-status-pill--primary gmb-text-xs"><?php esc_html_e('Google API', 'gmb-ranker-seo-automation'); ?></span>
                                            <?php else : ?>
                                                <span class="gmb-status-pill gmb-status-pill--success gmb-text-xs"><?php esc_html_e('IndexNow', 'gmb-ranker-seo-automation'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="gmb-td-history-endpoint">
                                            <?php if (!empty($log['manual_submission'])) : ?>
                                                <span class="gmb-history-badge-google"><?php esc_html_e('Manual', 'gmb-ranker-seo-automation'); ?></span>
                                            <?php else : ?>
                                                <span class="gmb-history-badge-indexnow"><?php esc_html_e('Automatic', 'gmb-ranker-seo-automation'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="gmb-td-history-status">
                                            <?php echo esc_html($log_stat); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="gmb-history-empty">
                                        <?php esc_html_e('No submission history logged yet. Use the Console to submit URLs.', 'gmb-ranker-seo-automation'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>
