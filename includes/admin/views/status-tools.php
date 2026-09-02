<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-importer') : ?>
            <div class="rm-tab-content active" id="rm-tab-importer">
                <div class="gmb-tools-container">
                    
                    <!-- Subtabs Navigation -->
                    <div class="gmb-tools-tabs">
                        <button type="button" class="gmb-tools-tab-btn active" data-tab="gmb-tools-importer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                            Import &amp; Export
                        </button>
                        <button type="button" class="gmb-tools-tab-btn" data-tab="gmb-tools-database">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                            Database Tools
                        </button>
                        <button type="button" class="gmb-tools-tab-btn" data-tab="gmb-tools-system">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                            System Status
                        </button>
                        <button type="button" class="gmb-tools-tab-btn" data-tab="gmb-tools-version">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            Version Control
                        </button>
                    </div>

                    <!-- TAB 1: Import & Export -->
                    <div id="gmb-tools-importer" class="gmb-tools-content-panel active">
                        <div class="gmb-settings-panel-header gmb-text-left">
                            <h2 class="gmb-heading-2">Import &amp; Export Configuration</h2>
                            <p class="gmb-form-help">Migrate SEO content configurations, focus keywords, and metadata from other plugins or export local configuration backups. <a href="https://gmbranker.org" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                        </div>
                        
                        <div class="gmb-importer-grid">
                            
                            <!-- Left Column: Import from Other Plugins -->
                            <div class="gmb-importer-card">
                                <div>
                                    <div class="gmb-tool-box-header">
                                        <h3 class="gmb-heading-3">Import from Other SEO Plugins</h3>
                                        <span class="gmb-importer-badge-detect">Auto-Detect</span>
                                    </div>
                                    <p class="gmb-text-muted gmb-mb-16 gmb-lh-base">GMB Ranker can import data from existing SEO plugins with a single click without modifying your original posts or losing rankings.</p>
                                    
                                    <!-- Rank Math Importer row -->
                                    <div class="gmb-importer-row">
                                        <div class="gmb-flex-center-gap-md">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/rankmath.jpeg')); ?>" alt="Rank Math SEO" class="gmb-importer-icon" />
                                            <div class="gmb-text-left">
                                                <strong class="gmb-importer-title">Rank Math SEO</strong>
                                                <span class="gmb-text-muted gmb-text-xs">Keywords, Titles, Descs, Schema &amp; Redirects</span>
                                            </div>
                                        </div>
                                        <button type="button" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-importer-action" id="importer-rm-btn">Import Now</button>
                                    </div>

                                    <!-- Yoast Importer row -->
                                    <div class="gmb-importer-row">
                                        <div class="gmb-flex-center-gap-md">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/yost.png')); ?>" alt="Yoast SEO" class="gmb-importer-icon" />
                                            <div class="gmb-text-left">
                                                <strong class="gmb-importer-title">Yoast SEO</strong>
                                                <span class="gmb-text-muted gmb-text-xs">Keywords, Titles, Descriptions &amp; Canonicals</span>
                                            </div>
                                        </div>
                                        <button type="button" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-importer-action" id="importer-yoast-btn">Import Now</button>
                                    </div>
                                </div>

                                <div class="gmb-importer-note-box">
                                    <span class="gmb-importer-note-bullet">&bull;</span>
                                    <span>Existing post metadata from other plugins is preserved and safely mapped into GMB Ranker options.</span>
                                </div>
                            </div>

                            <!-- Right Column: Backup & Restore -->
                            <div class="gmb-backup-restore-col">
                                
                                <!-- Card 1: Backup -->
                                <div class="gmb-tool-box">
                                    <div class="gmb-tool-box-header">
                                        <h3 class="gmb-heading-4">Backup GMB Ranker</h3>
                                        <span class="gmb-text-muted gmb-text-xs gmb-font-semibold">JSON Format</span>
                                    </div>
                                    <p class="gmb-text-muted gmb-mb-14 gmb-lh-base">Download a full portable snapshot of metadata templates, redirections, and local schema configurations.</p>
                                    <div class="gmb-text-left">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation&gmb_action=export_settings')); ?>" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-backup-export">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Export Settings (JSON)
                                        </a>
                                    </div>
                                </div>

                                <!-- Card 2: Restore -->
                                <div class="gmb-tool-box">
                                    <div class="gmb-tool-box-header">
                                        <h3 class="gmb-heading-4">Restore Backup</h3>
                                        <span class="gmb-text-muted gmb-text-xs gmb-font-semibold">Upload JSON</span>
                                    </div>
                                    <p class="gmb-text-muted gmb-mb-14 gmb-lh-base">Select a valid <code>.json</code> settings backup file previously exported from GMB Ranker to restore settings.</p>
                                    
                                    <!-- Styled Custom File Picker -->
                                    <div class="gmb-file-picker-row">
                                        <label class="gmb-file-picker-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            Choose File
                                            <input type="file" id="gmb-restore-file-input" name="settings_file" accept=".json" class="gmb-hidden" onchange="document.getElementById('gmb-restore-filename').innerText = this.files[0] ? this.files[0].name : 'No file chosen';" />
                                        </label>
                                        <span id="gmb-restore-filename" class="gmb-restore-filename-text">No file chosen</span>
                                        <button type="button" class="gmb-btn gmb-btn-secondary gmb-btn-restore-submit" id="gmb-restore-submit-btn">Restore Backup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Database Tools -->
                    <div id="gmb-tools-database" class="gmb-tools-content-panel">
                        <div class="gmb-settings-panel-header gmb-text-left">
                            <h2 class="gmb-heading-2">Database Cleanup &amp; Maintenance Tools</h2>
                            <p class="gmb-form-help">Safely defragment database tables, remove expired transients, and clear orphan metadata to keep your WordPress database ultra fast.</p>
                        </div>

                        <div class="gmb-grid-2">
                            <!-- Tool 1: Optimize Tables -->
                            <div class="gmb-db-tool-card">
                                <div>
                                    <h4 class="gmb-heading-4 gmb-db-tool-title">Optimize Database Tables</h4>
                                    <p class="gmb-text-muted gmb-db-tool-desc">Defragments core WordPress tables (posts, postmeta, options, terms) and reclaims overhead space.</p>
                                </div>
                                <button type="button" class="gmb-btn gmb-btn-secondary gmb-db-tool-btn" id="gmb-db-optimize-btn">Run Tool</button>
                            </div>

                            <!-- Tool 2: Clear Transients -->
                            <div class="gmb-db-tool-card">
                                <div>
                                    <h4 class="gmb-heading-4 gmb-db-tool-title">Clear Transients &amp; Expired Cache</h4>
                                    <p class="gmb-text-muted gmb-db-tool-desc">Deletes all stale, expired transient records and internal cached objects from the options table.</p>
                                </div>
                                <button type="button" class="gmb-btn gmb-btn-secondary gmb-db-tool-btn" id="gmb-db-transients-btn">Run Tool</button>
                            </div>

                            <!-- Tool 3: Clean Orphan Metadata -->
                            <div class="gmb-db-tool-card">
                                <div>
                                    <h4 class="gmb-heading-4 gmb-db-tool-title">Clean Orphan Metadata</h4>
                                    <p class="gmb-text-muted gmb-db-tool-desc">Cleans orphan postmeta, termmeta, and usermeta records whose parent items were deleted.</p>
                                </div>
                                <button type="button" class="gmb-btn gmb-btn-secondary gmb-db-tool-btn" id="gmb-db-orphan-btn">Run Tool</button>
                            </div>

                            <!-- Tool 4: Direct Rank Math Migration Tool -->
                            <div class="gmb-db-tool-card">
                                <div>
                                    <h4 class="gmb-heading-4 gmb-db-tool-title">Migrate Rank Math DB Records</h4>
                                    <p class="gmb-text-muted gmb-db-tool-desc">Batch executes full Rank Math metadata parser and imports data into GMB Ranker.</p>
                                </div>
                                <button type="button" class="gmb-btn gmb-btn-secondary gmb-db-tool-btn" id="gmb-db-import-rankmath-btn">Run Tool</button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: System Status -->
                    <div id="gmb-tools-system" class="gmb-tools-content-panel">
                        <div class="gmb-settings-panel-header gmb-text-left">
                            <h2 class="gmb-heading-2">System Diagnostic &amp; Server Environment</h2>
                            <p class="gmb-form-help">Comprehensive environment audit to verify SEO automation and indexing compatibility.</p>
                        </div>

                        <div class="gmb-grid-2">
                            <!-- Environment Box 1 -->
                            <div class="gmb-card">
                                <h4 class="gmb-env-card-header">WordPress Environment</h4>
                                <table class="gmb-status-table">
                                    <tr><td class="gmb-text-muted">WordPress Version:</td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html(get_bloginfo('version')); ?></td></tr>
                                    <tr><td class="gmb-text-muted">Site URL:</td><td class="gmb-td-site-url"><?php echo esc_html(site_url()); ?></td></tr>
                                    <tr><td class="gmb-text-muted">Multisite:</td><td class="gmb-text-right gmb-text-bold"><?php echo is_multisite() ? 'Yes' : 'No'; ?></td></tr>
                                    <tr><td class="gmb-text-muted">REST API Endpoint:</td><td class="gmb-text-right gmb-text-success gmb-font-semibold">Active &check;</td></tr>
                                    <tr><td class="gmb-text-muted">Theme Active:</td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html(wp_get_theme()->get('Name')); ?></td></tr>
                                </table>
                            </div>

                            <!-- Environment Box 2 -->
                            <div class="gmb-card">
                                <h4 class="gmb-env-card-header">Server &amp; PHP Runtime</h4>
                                <table class="gmb-status-table">
                                    <tr><td class="gmb-text-muted">PHP Version:</td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html(phpversion()); ?></td></tr>
                                    <tr><td class="gmb-text-muted">Memory Limit:</td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html(ini_get('memory_limit')); ?></td></tr>
                                    <tr><td class="gmb-text-muted">cURL Extension:</td><td class="gmb-text-right gmb-text-success gmb-font-semibold">Active &check;</td></tr>
                                    <tr><td class="gmb-text-muted">OpenSSL JWT Support:</td><td class="gmb-text-right gmb-text-success gmb-font-semibold">Supported &check;</td></tr>
                                    <tr><td class="gmb-text-muted">Web Server:</td><td class="gmb-text-right gmb-text-bold"><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? esc_html(sanitize_text_field($_SERVER['SERVER_SOFTWARE'])) : 'N/A'; ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Version Control -->
                    <div id="gmb-tools-version" class="gmb-tools-content-panel">
                        <div class="gmb-settings-panel-header gmb-text-left">
                            <h2 class="gmb-heading-2">Version Control &amp; Rollback Manager</h2>
                            <p class="gmb-form-help">Manage GMB Ranker plugin updates, inspect current version signatures, and configure automatic patching.</p>
                        </div>

                        <div class="gmb-version-card">
                            <div class="gmb-version-card-header">
                                <div>
                                    <div class="gmb-version-card-title">Installed Plugin Version</div>
                                    <div class="gmb-version-card-sub">GMB Ranker SEO &amp; Automation Core</div>
                                </div>
                                <span class="gmb-status-pill gmb-status-pill--info gmb-version-pill">v<?php echo esc_html(GMB_RANKER_SEO_VERSION); ?></span>
                            </div>
                            <div class="gmb-version-desc">
                                You are running the latest state-of-the-art enterprise edition of GMB Ranker SEO. Database schemas and REST indexing hooks are fully synchronized.
                            </div>
                            <div class="gmb-version-actions">
                                <a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="gmb-btn gmb-btn-secondary gmb-btn-version-link">View in Plugins List</a>
                                <a href="https://gmbranker.org" target="_blank" class="gmb-btn gmb-btn-secondary gmb-btn-version-cloud">Check Cloud Releases &nearr;</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endif; ?>

            <!-- Subtab: Help & Support -->
