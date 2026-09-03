<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-instant-indexing') : 
                $instant_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'console';
                if (!in_array($instant_tab, array('console', 'google_settings', 'bing_settings', 'indexnow_history'), true)) {
                    $instant_tab = 'console';
                }

                $google_json_key = get_option('gmb_ranker_google_json_key', '');
                $google_post_types = get_option('gmb_ranker_google_post_types', array('post', 'page'));
                if (!is_array($google_post_types)) $google_post_types = array('post', 'page');

                $indexnow_post_types = get_option('gmb_ranker_indexnow_post_types', array('post', 'page'));
                if (!is_array($indexnow_post_types)) $indexnow_post_types = array('post', 'page');

                $indexnow_key = GMB_Ranker_SEO_Instant_Indexing::get_indexnow_key();
                $key_location = GMB_Ranker_SEO_Instant_Indexing::get_key_location();
                $limits = GMB_Ranker_SEO_Instant_Indexing::get_limits();
                $public_post_types = get_post_types(array('public' => true), 'objects');

                // Pre-filled URL if arriving from row action
                $prefill_url = isset($_GET['url']) ? esc_url_raw(wp_unslash($_GET['url'])) : home_url('/');
                $prefill_action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : (!empty($google_json_key) ? 'update' : 'bing_submit');
            ?>
            <div class="rm-tab-content active" id="rm-tab-instant-indexing">
                <div class="gmb-indexing-container">
                    
                    <!-- Header -->
                    <div class="gmb-indexing-header">
                        <div>
                            <h2 class="gmb-indexing-title">Instant Indexing API Hub</h2>
                            <p class="gmb-indexing-desc">Directly notify Google, Bing, Yandex, and IndexNow when pages are published, updated, or removed.</p>
                        </div>
                        <div>
                            <?php if (!empty($google_json_key) && !empty($indexnow_key)) : ?>
                                <span class="gmb-status-pill gmb-status-pill--success">
                                    <span class="gmb-status-dot"></span>
                                    Google &amp; IndexNow Active
                                </span>
                            <?php elseif (!empty($indexnow_key)) : ?>
                                <span class="gmb-status-pill gmb-status-pill--info">
                                    <span class="gmb-status-dot"></span>
                                    IndexNow Active · Google Pending
                                </span>
                            <?php else : ?>
                                <span class="gmb-status-pill gmb-status-pill--danger">
                                    <span class="gmb-status-dot"></span>
                                    Not Connected
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sub-Navigation Tabs -->
                    <div class="gmb-indexing-tabs">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=console')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'console') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="gmb-icon gmb-icon--sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Console
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'google_settings') ? 'active' : ''; ?>">
                            <?php if (!empty($google_json_key)) : ?><span class="gmb-text-success">&check;</span><?php endif; ?>
                            Google API Settings
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=bing_settings')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'bing_settings') ? 'active' : ''; ?>">
                            <span class="gmb-text-success">&check;</span>
                            IndexNow API Settings
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=indexnow_history')); ?>" class="gmb-indexing-tab-link <?php echo ($instant_tab === 'indexnow_history') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="gmb-icon gmb-icon--sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Submission History
                        </a>
                    </div>

                    <!-- TAB 1: CONSOLE -->
                    <?php if ($instant_tab === 'console') : ?>
                        <div class="gmb-indexing-grid">
                            
                            <!-- Left Column: Submission Console -->
                            <div class="gmb-indexing-main">
                                <form id="gmb-instant-indexing-form" onsubmit="return gmbSubmitInstantIndexing(event);">
                                    <?php wp_nonce_field('gmb_instant_indexing_nonce', 'gmb_instant_nonce'); ?>
                                    
                                    <div class="gmb-mb-14">
                                        <label class="gmb-label-bold-sm">URLs (one per line, up to 100 for Google &amp; 10,000 for IndexNow):</label>
                                        <textarea id="gmb_indexing_urls" rows="5" placeholder="https://example.com/&#10;https://example.com/blog-post/" class="gmb-indexing-textarea"><?php echo esc_textarea($prefill_url); ?></textarea>
                                    </div>

                                    <div class="gmb-engine-selector-box">
                                        <label class="gmb-label-bold-md">Select Indexing Action &amp; Target Engine:</label>
                                        
                                        <div class="gmb-flex-col-gap-10">
                                            <?php if (!empty($google_json_key)) : ?>
                                                <div class="gmb-engine-card gmb-engine-card--connected">
                                                    <div class="gmb-flex-between-mb-8">
                                                        <strong class="gmb-text-success-flex">
                                                            <span class="gmb-status-dot-sm-green"></span>
                                                            Google Indexing API (Connected)
                                                        </strong>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-link-sm-blue">Settings &rarr;</a>
                                                    </div>
                                                    <div class="gmb-col-gap-6-pl-4">
                                                        <label class="gmb-checkbox-label">
                                                            <input type="radio" name="gmb_api_action" value="update" <?php checked($prefill_action, 'update'); ?> onchange="gmbUpdateIndexBtnText();" />
                                                            <span><strong>Google:</strong> Publish / update URL (Instant Googlebot Crawl)</span>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="radio" name="gmb_api_action" value="remove" <?php checked($prefill_action, 'remove'); ?> onchange="gmbUpdateIndexBtnText();" />
                                                            <span><strong>Google:</strong> Remove URL from Google Search Index</span>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="radio" name="gmb_api_action" value="getstatus" <?php checked($prefill_action, 'getstatus'); ?> onchange="gmbUpdateIndexBtnText();" />
                                                            <span><strong>Google:</strong> Get URL Status &amp; Last Crawl Metadata</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php else : ?>
                                                <div class="gmb-engine-card gmb-engine-card--warning">
                                                    <div class="gmb-flex-between-wrap-8">
                                                        <div class="gmb-flex-center-gap-6">
                                                            <span class="gmb-status-dot-sm-orange"></span>
                                                            <strong class="gmb-text-main">Google Indexing API</strong>
                                                            <span class="gmb-badge-warn-sm">Not Connected</span>
                                                        </div>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="button button-small gmb-badge-info-sm">Connect Service Key &rarr;</a>
                                                    </div>
                                                    <p class="gmb-text-desc-sm">
                                                        <strong>How Google Instant Indexing works:</strong> By connecting your Google Cloud Service Account, GMB Ranker directly commands Google's Web Search Indexing servers to crawl and index your new &amp; updated pages within minutes.
                                                    </p>
                                                    <div class="gmb-meta-info-box-sm">
                                                        <span><strong>1.</strong> Create Free Service Account</span>
                                                        <span class="gmb-text-sep">&bull;</span>
                                                        <span><strong>2.</strong> Add to Google Search Console</span>
                                                        <span class="gmb-text-sep">&bull;</span>
                                                        <span><strong>3.</strong> Paste JSON Key &amp; Auto-Index</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- IndexNow Action Option -->
                                            <div class="gmb-engine-card">
                                                <label class="gmb-checkbox-label">
                                                    <input type="radio" name="gmb_api_action" value="bing_submit" <?php checked($prefill_action, 'bing_submit'); ?> onchange="gmbUpdateIndexBtnText();" />
                                                    <span><strong>IndexNow Protocol:</strong> Submit URL (Instant syndication to Bing, Yandex, Seznam, Naver)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="gmb-indexing-btn-row">
                                        <button type="submit" id="gmb-indexing-submit-btn" class="gmb-btn-index-now">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span id="gmb-submit-btn-label"><?php echo ($prefill_action === 'bing_submit') ? 'Submit to IndexNow' : 'Submit to Google API'; ?></span>
                                        </button>
                                        <span id="gmb-indexing-spinner" class="gmb-text-muted gmb-text-sm">Dispatching request to indexing servers...</span>
                                    </div>
                                </form>

                                <!-- Live Response Box -->
                                <div id="gmb-indexing-response-box" class="gmb-response-box">
                                    <div id="gmb-indexing-response-header" class="gmb-response-header">
                                        <strong id="gmb-indexing-response-title" class="gmb-text-main">API Response</strong>
                                        <button type="button" onclick="document.getElementById('gmb-indexing-raw-json').style.display = (document.getElementById('gmb-indexing-raw-json').style.display === 'none' ? 'block' : 'none');" class="gmb-btn-raw-toggle">
                                            Toggle Raw JSON
                                        </button>
                                    </div>
                                    <div class="gmb-response-body">
                                        <div id="gmb-indexing-response-msg" class="gmb-response-text"></div>
                                        <textarea id="gmb-indexing-raw-json" readonly class="gmb-raw-response-textarea"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Quota & Verification Panel (Stacked Vertically) -->
                            <div class="gmb-indexing-sidebar">
                                
                                <!-- Quota Card -->
                                <div class="gmb-quota-card">
                                    <h3 class="gmb-card-header-flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="gmb-icon-primary-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Remaining Quota Limits
                                    </h3>

                                    <div class="gmb-quota-grid">
                                        <div class="gmb-quota-box">
                                            <span class="gmb-quota-label">Publish Requests/Day</span>
                                            <div class="gmb-quota-numbers">
                                                <span id="gmb-limit-publishperday" class="gmb-quota-val"><?php echo absint($limits['publishperday']); ?></span>
                                                <span class="gmb-quota-max">/ <?php echo absint($limits['publishperday_max']); ?></span>
                                            </div>
                                        </div>
                                        <div class="gmb-quota-box">
                                            <span class="gmb-quota-label">Requests/Minute</span>
                                            <div class="gmb-quota-numbers">
                                                <span id="gmb-limit-permin" class="gmb-quota-val"><?php echo absint($limits['permin']); ?></span>
                                                <span class="gmb-quota-max">/ <?php echo absint($limits['permin_max']); ?></span>
                                            </div>
                                        </div>
                                        <div class="gmb-quota-box">
                                            <span class="gmb-quota-label">Metadata Requests/Min</span>
                                            <div class="gmb-quota-numbers">
                                                <span id="gmb-limit-metapermin" class="gmb-quota-val"><?php echo absint($limits['metapermin']); ?></span>
                                                <span class="gmb-quota-max">/ <?php echo absint($limits['metapermin_max']); ?></span>
                                            </div>
                                        </div>
                                        <div class="gmb-quota-box">
                                            <span class="gmb-quota-label">IndexNow Requests/Day</span>
                                            <div class="gmb-quota-numbers">
                                                <span id="gmb-limit-indexnowperday" class="gmb-quota-val"><?php echo absint($limits['indexnowperday']); ?></span>
                                                <span class="gmb-quota-max">/ <?php echo absint($limits['indexnowperday_max']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verification & Endpoints Card -->
                                <div class="gmb-quota-card">
                                    <h3 class="gmb-heading-4">Indexing Protocol Status</h3>
                                    <div class="gmb-protocol-status-list">
                                        <div class="gmb-protocol-status-row">
                                            <span class="gmb-protocol-name">IndexNow Protocol</span>
                                            <span class="gmb-protocol-badge--verified">Verified &check;</span>
                                        </div>
                                        <div class="gmb-protocol-status-row">
                                            <span class="gmb-protocol-name">Google Cloud Auth</span>
                                            <?php if (!empty($google_json_key)) : ?>
                                                <span class="gmb-protocol-badge--verified">Connected &check;</span>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing&tab=google_settings')); ?>" class="gmb-protocol-badge--warning">
                                                    Not Connected · Connect &rarr;
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="gmb-protocol-status-row">
                                            <span class="gmb-protocol-name">Auto-Dispatch on Publish</span>
                                            <span class="gmb-protocol-badge--verified">Enabled &check;</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- TAB 2: GOOGLE API SETTINGS -->
                    <?php if ($instant_tab === 'google_settings') : 
                        $parsed_google_key = !empty($google_json_key) ? json_decode($google_json_key, true) : null;
                        $google_email = (is_array($parsed_google_key) && !empty($parsed_google_key['client_email'])) ? $parsed_google_key['client_email'] : '';
                        $google_project = (is_array($parsed_google_key) && !empty($parsed_google_key['project_id'])) ? $parsed_google_key['project_id'] : '';
                    ?>
                        <form method="post" action="options.php" enctype="multipart/form-data">
                            <?php settings_fields('gmb_ranker_google_indexing_group'); ?>

                            <div class="gmb-settings-cards-stack">
                                
                                <!-- Connection Status Card -->
                                <?php if (!empty($google_email)) : ?>
                                    <div class="gmb-service-account-card-ok">
                                        <div class="gmb-flex-center-gap-sm">
                                            <span class="gmb-status-dot-green"></span>
                                            <div>
                                                <strong class="gmb-service-account-name">Google Service Account Connected &amp; Authenticated &check;</strong>
                                                <div class="gmb-service-account-meta">
                                                    <span>Email: <code class="gmb-service-account-badge"><?php echo esc_html($google_email); ?></code></span>
                                                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo esc_js($google_email); ?>'); alert('Service Account email copied to clipboard!\n\nNow open Google Search Console > Settings > Users and Permissions > Add User, and add this email as an OWNER.');" class="gmb-btn-copy-email">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                                        Copy Email
                                                    </button>
                                                    <span class="gmb-text-muted">· Project: <strong><?php echo esc_html($google_project); ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="gmb-flex-gap-sm gmb-items-center">
                                            <a href="https://search.google.com/search-console" target="_blank" class="gmb-btn-console-link">
                                                Search Console &nearr;
                                            </a>
                                            <span class="gmb-key-status-badge-ok">Active</span>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="gmb-service-account-card-warn">
                                        <div class="gmb-flex-center-gap-sm">
                                            <span class="gmb-status-dot-orange"></span>
                                            <div>
                                                <strong class="gmb-service-account-name gmb-text-warning-dark">Google Cloud Auth: Not Connected</strong>
                                                <span class="gmb-text-warning-sub">Follow the 4-step setup guide below to connect your free Google Service Account key.</span>
                                            </div>
                                        </div>
                                        <span class="gmb-key-status-badge-warn">Setup Required</span>
                                    </div>
                                <?php endif; ?>

                                <!-- How Google Indexing Works Workflow Card -->
                                <div class="gmb-card gmb-workflow-card">
                                    <h4 class="gmb-card-header-flex-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="gmb-icon-primary-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        How Google Instant Indexing Works
                                    </h4>
                                    <p class="gmb-text-muted gmb-mb-14">
                                        Unlike regular sitemaps where Googlebot may take days or weeks to discover changes, the <strong>Google Indexing API</strong> pushes direct crawl notifications into Google's priority processing queue in real-time.
                                    </p>
                                    <div class="gmb-workflow-steps-grid">
                                        <div class="gmb-workflow-step-card">
                                            <div class="gmb-step-num-badge">1</div>
                                            <div class="gmb-step-body">
                                                <strong class="gmb-step-title">Publish or Update Post</strong>
                                                <span class="gmb-step-desc">Triggered instantly via WordPress when you publish, edit, or delete a post.</span>
                                            </div>
                                        </div>
                                        <div class="gmb-workflow-step-card">
                                            <div class="gmb-step-num-badge">2</div>
                                            <div class="gmb-step-body">
                                                <strong class="gmb-step-title">Direct API Dispatch</strong>
                                                <span class="gmb-step-desc">GMB Ranker signs an RS256 JWT and submits the URL to Google's indexing endpoint.</span>
                                            </div>
                                        </div>
                                        <div class="gmb-workflow-step-card">
                                            <div class="gmb-step-num-badge">3</div>
                                            <div class="gmb-step-body">
                                                <strong class="gmb-step-title">Instant Googlebot Crawl</strong>
                                                <span class="gmb-step-desc">Googlebot visits and indexes the URL within minutes instead of standard crawl latency.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-card">
                                    <div class="gmb-key-field-header">
                                        <label class="gmb-key-field-title">Google Service Account JSON Key</label>
                                        <div>
                                            <input type="file" id="gmb_google_json_file_picker" accept=".json,application/json" onchange="gmbHandleGoogleJsonFileUpload(this);" />
                                            <button type="button" onclick="document.getElementById('gmb_google_json_file_picker').click();" class="gmb-btn gmb-btn-secondary gmb-btn-action-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                Upload Service Account (.json) File
                                            </button>
                                        </div>
                                    </div>
                                    <p class="gmb-text-muted">Upload your downloaded Google Cloud JSON key file or paste its raw JSON contents below.</p>
                                    <div id="gmb_google_json_upload_badge" class="gmb-copy-feedback-box"></div>
                                    <textarea id="gmb_ranker_google_json_key_field" name="gmb_ranker_google_json_key" rows="6" placeholder='{ "type": "service_account", "project_id": "...", "private_key": "...", "client_email": "..." }' class="gmb-key-input-box"><?php echo esc_textarea($google_json_key); ?></textarea>
                                </div>

                                <div class="gmb-card">
                                    <label class="gmb-form-label">Auto-Submit Posts to Google</label>
                                    <p class="gmb-text-muted">Automatically send URLs to Google Indexing API whenever a post of these types is published or updated.</p>
                                    <div class="gmb-flex-gap-16-wrap">
                                        <?php foreach ($public_post_types as $pt) : ?>
                                            <label class="gmb-checkbox-label-inline">
                                                <input type="checkbox" name="gmb_ranker_google_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $google_post_types, true)); ?> />
                                                <span><?php echo esc_html($pt->label); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Setup Instructions Guide -->
                                <div class="gmb-info-instruction-box">
                                    <h4 class="gmb-info-instruction-title">Step-by-Step Google Setup Guide:</h4>
                                    <ol class="gmb-info-instruction-list">
                                        <li>Go to the <a href="https://console.cloud.google.com/" target="_blank" class="gmb-text-primary-semibold">Google Cloud Console</a> and create or select a project.</li>
                                        <li>Enable the <strong>Web Search Indexing API</strong> from the API Library.</li>
                                        <li>Create a <strong>Service Account</strong> with the <em>Owner</em> role, and generate a new <strong>JSON Key</strong>.</li>
                                        <li>Copy the Service Account email and add it to your <a href="https://search.google.com/search-console" target="_blank" class="gmb-text-primary-semibold">Google Search Console</a> property as an <strong>Owner</strong>.</li>
                                        <li>Paste the JSON key content into the box above and click <strong>Save Google Settings</strong>.</li>
                                    </ol>
                                </div>

                                <div class="gmb-flex-end">
                                    <button type="submit" class="gmb-btn gmb-btn-primary gmb-btn--primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Save Google Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <!-- TAB 3: INDEXNOW API SETTINGS -->
                    <?php if ($instant_tab === 'bing_settings') : ?>
                        <form method="post" action="options.php">
                            <?php settings_fields('gmb_ranker_bing_indexing_group'); ?>

                            <div class="gmb-settings-cards-stack">
                                <div class="gmb-card">
                                    <label class="gmb-form-label">Auto-Submit Posts to IndexNow</label>
                                    <p class="gmb-text-muted">Submit posts from these types automatically to the IndexNow protocol (Bing, Yandex, Seznam) upon publish/edit.</p>
                                    <div class="gmb-flex-gap-16-wrap">
                                        <?php foreach ($public_post_types as $pt) : ?>
                                            <label class="gmb-checkbox-label-inline">
                                                <input type="checkbox" name="gmb_ranker_indexnow_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $indexnow_post_types, true)); ?> />
                                                <span><?php echo esc_html($pt->label); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="gmb-card">
                                    <label class="gmb-form-label">IndexNow API Key</label>
                                    <p class="gmb-text-muted-xs-8">This unique key verifies ownership of your website with search engine crawlers.</p>
                                    <div class="gmb-flex-gap-sm">
                                        <input type="text" id="gmb_indexnow_key_field" name="gmb_ranker_indexnow_key" readonly value="<?php echo esc_attr($indexnow_key); ?>" class="gmb-input-key-full" />
                                        <button type="button" id="gmb-reset-indexnow-key-btn" onclick="gmbResetIndexNowKey();" class="gmb-btn gmb-btn-secondary">Change Key</button>
                                    </div>
                                </div>

                                <div class="gmb-card">
                                    <label class="gmb-form-label">IndexNow Key Location Endpoint</label>
                                    <p class="gmb-text-muted-xs-8">Search engine bots check this URL to verify site ownership match.</p>
                                    <div class="gmb-flex-center-gap-sm">
                                        <code id="gmb_indexnow_key_location" class="gmb-key-file-display"><?php echo esc_url($key_location); ?></code>
                                        <a id="gmb_indexnow_check_key_link" href="<?php echo esc_url($key_location); ?>" target="_blank" class="gmb-btn gmb-btn-secondary gmb-btn-view-key">Check Key &nearr;</a>
                                    </div>
                                </div>

                                <div class="gmb-flex-end">
                                    <button type="submit" class="gmb-btn gmb-btn-primary gmb-btn--primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Save IndexNow Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <!-- TAB 4: INDEXNOW HISTORY -->
                    <?php if ($instant_tab === 'indexnow_history') : 
                        $history_logs = get_option('gmb_ranker_indexnow_log', array());
                        if (!is_array($history_logs)) $history_logs = array();
                        $history_logs = array_reverse($history_logs);
                    ?>
                        <div>
                            <div class="gmb-history-header-row">
                                <p class="gmb-history-header-desc">Recent IndexNow URL submissions and crawler response statuses.</p>
                                <?php if (!empty($history_logs)) : ?>
                                    <button type="button" onclick="gmbClearIndexNowHistory();" class="gmb-btn gmb-btn-secondary gmb-btn-clear-history">Clear History</button>
                                <?php endif; ?>
                            </div>

                            <div class="gmb-history-table-card">
                                <table class="gmb-indexing-history-table">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Submitted URL</th>
                                            <th>Engine</th>
                                            <th>Type</th>
                                            <th>Response Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($history_logs)) : ?>
                                            <?php foreach ($history_logs as $log) : ?>
                                                <tr>
                                                    <td class="gmb-th-time">
                                                        <strong><?php echo esc_html(wp_date('Y-m-d H:i:s', $log['time'])); ?></strong><br>
                                                        <span class="gmb-text-muted-11"><?php echo esc_html(human_time_diff($log['time']) . ' ago'); ?></span>
                                                    </td>
                                                    <td class="gmb-td-history-url">
                                                        <a href="<?php echo esc_url($log['url']); ?>" target="_blank" class="gmb-link-primary"><?php echo esc_html($log['url']); ?></a>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($log['engine']) && $log['engine'] === 'google') : ?>
                                                            <span class="gmb-status-pill gmb-status-pill--primary gmb-text-xs">Google API</span>
                                                        <?php else : ?>
                                                            <span class="gmb-status-pill gmb-status-pill--success gmb-text-xs">IndexNow</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="gmb-td-history-endpoint">
                                                        <?php if (!empty($log['manual_submission'])) : ?>
                                                            <span class="gmb-history-badge-google">Manual</span>
                                                        <?php else : ?>
                                                            <span class="gmb-history-badge-indexnow">Automatic</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="gmb-td-history-status">
                                                        <?php echo esc_html($log['status']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="5" class="gmb-history-empty">
                                                    No submission history logged yet. Use the Console to submit URLs.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Response Code Help Guide -->
                            <div class="gmb-card">
                                <h4 class="gmb-heading-4">IndexNow Response Code Reference</h4>
                                <div class="gmb-endpoint-legend-grid">
                                    <div><strong class="gmb-endpoint-ok">200 OK:</strong> URL submitted and processed immediately.</div>
                                    <div><strong class="gmb-endpoint-blue">202 Accepted:</strong> URL queued and API key will be checked later.</div>
                                    <div><strong class="gmb-endpoint-red">400 Bad Request:</strong> Invalid request format or missing parameters.</div>
                                    <div><strong class="gmb-endpoint-red">403 Forbidden:</strong> Key not matching site ownership location.</div>
                                    <div><strong class="gmb-endpoint-orange">422 Unprocessable:</strong> URL not belonging to current host domain.</div>
                                    <div><strong class="gmb-endpoint-orange">429 Rate Limit:</strong> Exceeded allowable daily submission limits.</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <script>
            function gmbSubmitInstantIndexing(e) {
                e.preventDefault();
                var urls = document.getElementById('gmb_indexing_urls').value;
                var actionInput = document.querySelector('input[name="gmb_api_action"]:checked');
                var action = actionInput ? actionInput.value : 'bing_submit';
                var nonce = document.getElementById('gmb_instant_nonce').value;

                var btn = document.getElementById('gmb-indexing-submit-btn');
                var spinner = document.getElementById('gmb-indexing-spinner');
                var respBox = document.getElementById('gmb-indexing-response-box');
                var respMsg = document.getElementById('gmb-indexing-response-msg');
                var rawJson = document.getElementById('gmb-indexing-raw-json');

                btn.disabled = true;
                spinner.style.display = 'inline';
                respBox.style.display = 'none';

                var formData = new FormData();
                formData.append('action', 'gmb_instant_indexing_submit');
                formData.append('nonce', nonce);
                formData.append('urls', urls);
                formData.append('api_action', action);

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    spinner.style.display = 'none';
                    respBox.style.display = 'block';

                    if (data.data && data.data.limits) {
                        var l = data.data.limits;
                        var elPub = document.getElementById('gmb-limit-publishperday');
                        if (elPub && l.publishperday !== undefined) elPub.textContent = l.publishperday;
                        var elMin = document.getElementById('gmb-limit-permin');
                        if (elMin && l.permin !== undefined) elMin.textContent = l.permin;
                        var elMeta = document.getElementById('gmb-limit-metapermin');
                        if (elMeta && l.metapermin !== undefined) elMeta.textContent = l.metapermin;
                        var elIdx = document.getElementById('gmb-limit-indexnowperday');
                        if (elIdx && l.indexnowperday !== undefined) elIdx.textContent = l.indexnowperday;
                    }

                    if (data.success) {
                        respMsg.innerHTML = '<div class="gmb-alert-success-card">&check; Request successfully submitted! ' + (data.data.message || '') + '</div>';
                        rawJson.value = JSON.stringify(data.data, null, 2);
                    } else {
                        var err = '';
                        if (data.data) {
                            if (data.data.error) err = data.data.error;
                            else if (data.data.message) err = data.data.message;
                            else if (data.data.single && data.data.single.error) err = data.data.single.error;
                            else if (data.data.results) {
                                for (var k in data.data.results) {
                                    if (data.data.results[k].error) {
                                        err = data.data.results[k].error;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!err) err = 'Submission error.';

                        // Smart detection for Google Cloud Indexing API disabled with activation link
                        var activationMatch = err.match(/https:\/\/console\.developers\.google\.com\/apis\/api\/indexing\.googleapis\.com\/overview\?[^\s"']+/);
                        if (activationMatch) {
                            var actUrl = activationMatch[0];
                            respMsg.innerHTML = '<div class="gmb-alert-danger-box">' +
                                '<div class="gmb-heading-alert-danger">' +
                                '<span class="gmb-icon-alert-lg">⚠️</span> Action Required: Web Search Indexing API is Disabled' +
                                '</div>' +
                                '<div class="gmb-text-alert-danger-desc">' +
                                'Google returned that the <strong>Web Search Indexing API</strong> has not been enabled in your Google Cloud Project yet. Click the button below to enable it in your Google Cloud Console, wait 1-2 minutes for Google to propagate, and retry.' +
                                '</div>' +
                                '<a href="' + actUrl + '" target="_blank" rel="noopener noreferrer" class="button button-primary gmb-alert-danger-btn">🚀 Enable Indexing API in Google Cloud Console &rarr;</a>' +
                                '<div class="gmb-alert-danger-sub">' +
                                '<strong>Next Step:</strong> After enabling, also ensure your Google Service Account email is added as an <strong>Owner</strong> in Google Search Console for this domain property.' +
                                '</div>' +
                                '</div>';
                        } else if (err.indexOf('Google Search Console') !== -1 || err.indexOf('OWNER') !== -1 || err.indexOf('PERMISSION_DENIED') !== -1) {
                            respMsg.innerHTML = '<div class="gmb-alert-warning-box">' +
                                '<div class="gmb-heading-alert-warn">⚠️ Permission Denied: Google Search Console Ownership Required</div>' +
                                '<div class="gmb-text-alert-warn-desc">' + err + '</div>' +
                                '<div class="gmb-alert-warning-sub">Open <a href="https://search.google.com/search-console/users" target="_blank" class="gmb-link-warn-underline">Google Search Console &rarr; Settings &rarr; Users and permissions</a> and add your Service Account email as an <strong>Owner</strong>.</div>' +
                                '</div>';
                        } else {
                            respMsg.innerHTML = '<div class="gmb-alert-danger-card">&cross; ' + err + '</div>';
                        }
                        rawJson.value = JSON.stringify(data, null, 2);
                    }
                })
                .catch(function(err) {
                    btn.disabled = false;
                    spinner.style.display = 'none';
                    respBox.style.display = 'block';
                    respMsg.innerHTML = '<div class="gmb-alert-danger-card">Network error: ' + err + '</div>';
                });

                return false;
            }

            function gmbHandleGoogleJsonFileUpload(fileInput) {
                if (!fileInput.files || !fileInput.files[0]) {
                    return;
                }
                var file = fileInput.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        var content = e.target.result;
                        var parsed = JSON.parse(content);
                        if (!parsed.client_email || !parsed.private_key) {
                            alert('Invalid Google Service Account JSON: Missing client_email or private_key.');
                            return;
                        }
                        var textarea = document.getElementById('gmb_ranker_google_json_key_field');
                        if (textarea) {
                            textarea.value = JSON.stringify(parsed, null, 2);
                        }
                        var badge = document.getElementById('gmb_google_json_upload_badge');
                        if (badge) {
                            badge.style.display = 'block';
                            badge.innerHTML = '&check; Loaded Service Account Key for: <strong>' + parsed.client_email + '</strong> (Project: <em>' + (parsed.project_id || 'N/A') + '</em>). Remember to click "Save Google Settings" below.';
                        }
                    } catch (err) {
                        alert('Failed to parse JSON file: ' + err.message);
                    }
                };
                reader.readAsText(file);
            }

            function gmbResetIndexNowKey() {
                if (!confirm('Are you sure you want to generate a new IndexNow API key? Search engines will re-verify the new key location.')) {
                    return;
                }
                var nonce = document.getElementById('gmb_instant_nonce') ? document.getElementById('gmb_instant_nonce').value : '<?php echo wp_create_nonce("gmb_instant_indexing_nonce"); ?>';
                var fd = new FormData();
                fd.append('action', 'gmb_instant_indexing_reset_key');
                fd.append('nonce', nonce);

                fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        document.getElementById('gmb_indexnow_key_field').value = res.data.key;
                        document.getElementById('gmb_indexnow_key_location').textContent = res.data.key_location;
                        document.getElementById('gmb_indexnow_check_key_link').href = res.data.key_location;
                        alert('IndexNow API Key successfully regenerated.');
                    }
                });
            }

            function gmbClearIndexNowHistory() {
                if (!confirm('Clear all IndexNow submission logs?')) {
                    return;
                }
                var nonce = '<?php echo wp_create_nonce("gmb_instant_indexing_nonce"); ?>';
                var fd = new FormData();
                fd.append('action', 'gmb_instant_indexing_clear_history');
                fd.append('nonce', nonce);

                fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        location.reload();
                    }
                });
            }

            function gmbUpdateIndexBtnText() {
                var actionInput = document.querySelector('input[name="gmb_api_action"]:checked');
                var labelSpan = document.getElementById('gmb-submit-btn-label');
                if (!actionInput || !labelSpan) return;
                if (actionInput.value === 'bing_submit') {
                    labelSpan.textContent = 'Submit to IndexNow';
                } else if (actionInput.value === 'remove') {
                    labelSpan.textContent = 'Remove from Google Index';
                } else if (actionInput.value === 'getstatus') {
                    labelSpan.textContent = 'Get Google URL Status';
                } else {
                    labelSpan.textContent = 'Submit to Google API';
                }
            }
            </script>
            <?php endif; ?>

            <!-- Subtab: Integrations -->
