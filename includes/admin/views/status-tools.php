<?php
/**
 * System Status & Tools Administration View
 *
 * Thin presentation layer consuming canonical GMB_Ranker_SEO_Tools_Registry view model.
 * Direct server inspection, inline JavaScript, and domain operations are removed.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page_name = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '');

if ($current_page_name === 'gmb-ranker-importer') :

    $req_tab    = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'gmb-tools-importer';
    $view_model = GMB_Ranker_SEO_Tools_Registry::get_view_model($req_tab);

    $active_tab  = $view_model['active_tab'];
    $importers   = $view_model['importers'];
    $db_tools    = $view_model['database_tools'];
    $sys_status  = $view_model['system_status'];
    $ver_control = $view_model['version_control'];
    $export_url  = $view_model['export_url'];
?>
    <div class="rm-tab-content active" id="rm-tab-importer" role="region" aria-label="<?php esc_attr_e('Status and Tools Panel', 'gmb-ranker-seo-automation'); ?>">
        <div class="gmb-tools-container">
            
            <!-- Subtabs Navigation -->
            <div class="gmb-tools-tabs" role="tablist">
                <button type="button" class="gmb-tools-tab-btn <?php echo ($active_tab === 'gmb-tools-importer') ? 'active' : ''; ?>" data-tab="gmb-tools-importer" role="tab" aria-selected="<?php echo ($active_tab === 'gmb-tools-importer') ? 'true' : 'false'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                    <?php esc_html_e('Import & Export', 'gmb-ranker-seo-automation'); ?>
                </button>
                <button type="button" class="gmb-tools-tab-btn <?php echo ($active_tab === 'gmb-tools-database') ? 'active' : ''; ?>" data-tab="gmb-tools-database" role="tab" aria-selected="<?php echo ($active_tab === 'gmb-tools-database') ? 'true' : 'false'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                    <?php esc_html_e('Database Tools', 'gmb-ranker-seo-automation'); ?>
                </button>
                <button type="button" class="gmb-tools-tab-btn <?php echo ($active_tab === 'gmb-tools-system') ? 'active' : ''; ?>" data-tab="gmb-tools-system" role="tab" aria-selected="<?php echo ($active_tab === 'gmb-tools-system') ? 'true' : 'false'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    <?php esc_html_e('System Status', 'gmb-ranker-seo-automation'); ?>
                </button>
                <button type="button" class="gmb-tools-tab-btn <?php echo ($active_tab === 'gmb-tools-version') ? 'active' : ''; ?>" data-tab="gmb-tools-version" role="tab" aria-selected="<?php echo ($active_tab === 'gmb-tools-version') ? 'true' : 'false'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <?php esc_html_e('Version Control', 'gmb-ranker-seo-automation'); ?>
                </button>
            </div>

            <!-- TAB 1: Import & Export -->
            <div id="gmb-tools-importer" class="gmb-tools-content-panel <?php echo ($active_tab === 'gmb-tools-importer') ? 'active' : ''; ?>" role="tabpanel">
                <div class="gmb-settings-panel-header gmb-text-left">
                    <h2 class="gmb-heading-2"><?php esc_html_e('Import & Export Configuration', 'gmb-ranker-seo-automation'); ?></h2>
                    <p class="gmb-form-help"><?php esc_html_e('Migrate SEO configurations and metadata from supported plugins or export local settings backups.', 'gmb-ranker-seo-automation'); ?> <a href="https://gmbranker.org" target="_blank" class="gmb-help-link"><?php esc_html_e('Learn more', 'gmb-ranker-seo-automation'); ?></a>.</p>
                </div>
                
                <div class="gmb-importer-grid">
                    <!-- Left Column: Import from Other Plugins -->
                    <div class="gmb-importer-card">
                        <div>
                            <div class="gmb-tool-box-header">
                                <h3 class="gmb-heading-3"><?php esc_html_e('Import from Other SEO Plugins', 'gmb-ranker-seo-automation'); ?></h3>
                                <span class="gmb-importer-badge-detect"><?php esc_html_e('Auto-Detect', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <p class="gmb-text-muted gmb-mb-16 gmb-lh-base"><?php esc_html_e('Import metadata and redirects from detected SEO plugins into GMB Ranker options.', 'gmb-ranker-seo-automation'); ?></p>
                            
                            <!-- Importer Rows -->
                            <?php foreach ($importers as $imp_key => $imp) : ?>
                            <div class="gmb-importer-row">
                                <div class="gmb-flex-center-gap-md">
                                    <img src="<?php echo esc_url($imp['icon']); ?>" alt="<?php echo esc_attr($imp['name']); ?>" class="gmb-importer-icon" />
                                    <div class="gmb-text-left">
                                        <strong class="gmb-importer-title"><?php echo esc_html($imp['name']); ?></strong>
                                        <span class="gmb-text-muted gmb-text-xs"><?php echo esc_html($imp['description']); ?></span>
                                    </div>
                                </div>
                                <button type="button" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-importer-action" id="<?php echo esc_attr($imp['id']); ?>"><?php esc_html_e('Import Now', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="gmb-importer-note-box">
                            <span class="gmb-importer-note-bullet">&bull;</span>
                            <span><?php esc_html_e('Existing post metadata from other plugins is safely mapped into GMB Ranker configuration.', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    </div>

                    <!-- Right Column: Backup & Restore -->
                    <div class="gmb-backup-restore-col">
                        <!-- Card 1: Backup -->
                        <div class="gmb-tool-box">
                            <div class="gmb-tool-box-header">
                                <h3 class="gmb-heading-4"><?php esc_html_e('Backup GMB Ranker', 'gmb-ranker-seo-automation'); ?></h3>
                                <span class="gmb-text-muted gmb-text-xs gmb-font-semibold"><?php esc_html_e('JSON Format', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <p class="gmb-text-muted gmb-mb-14 gmb-lh-base"><?php esc_html_e('Download a portable snapshot of metadata templates, redirections, and local schema configurations.', 'gmb-ranker-seo-automation'); ?></p>
                            <div class="gmb-text-left">
                                <a href="<?php echo esc_url($export_url); ?>" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-backup-export">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <?php esc_html_e('Export Settings (JSON)', 'gmb-ranker-seo-automation'); ?>
                                </a>
                            </div>
                        </div>

                        <!-- Card 2: Restore -->
                        <div class="gmb-tool-box">
                            <div class="gmb-tool-box-header">
                                <h3 class="gmb-heading-4"><?php esc_html_e('Restore Backup', 'gmb-ranker-seo-automation'); ?></h3>
                                <span class="gmb-text-muted gmb-text-xs gmb-font-semibold"><?php esc_html_e('Upload JSON', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <p class="gmb-text-muted gmb-mb-14 gmb-lh-base"><?php esc_html_e('Select a valid .json settings backup file previously exported from GMB Ranker to restore settings.', 'gmb-ranker-seo-automation'); ?></p>
                            
                            <!-- Custom File Picker -->
                            <div class="gmb-file-picker-row">
                                <label class="gmb-file-picker-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    <?php esc_html_e('Choose File', 'gmb-ranker-seo-automation'); ?>
                                    <input type="file" id="gmb-restore-file-input" name="settings_file" accept=".json" class="gmb-hidden" />
                                </label>
                                <span id="gmb-restore-filename" class="gmb-restore-filename-text"><?php esc_html_e('No file chosen', 'gmb-ranker-seo-automation'); ?></span>
                                <button type="button" class="gmb-btn gmb-btn-secondary gmb-btn-restore-submit" id="gmb-restore-submit-btn"><?php esc_html_e('Restore Backup', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Database Tools -->
            <div id="gmb-tools-database" class="gmb-tools-content-panel <?php echo ($active_tab === 'gmb-tools-database') ? 'active' : ''; ?>" role="tabpanel">
                <div class="gmb-settings-panel-header gmb-text-left">
                    <h2 class="gmb-heading-2"><?php esc_html_e('Database Cleanup & Maintenance Tools', 'gmb-ranker-seo-automation'); ?></h2>
                    <p class="gmb-form-help"><?php esc_html_e('Optimize database tables, remove expired transients, and clear orphan metadata.', 'gmb-ranker-seo-automation'); ?></p>
                </div>

                <div class="gmb-grid-2">
                    <?php foreach ($db_tools as $tool_key => $tool) : ?>
                    <div class="gmb-db-tool-card">
                        <div>
                            <h4 class="gmb-heading-4 gmb-db-tool-title"><?php echo esc_html($tool['title']); ?></h4>
                            <p class="gmb-text-muted gmb-db-tool-desc"><?php echo esc_html($tool['description']); ?></p>
                        </div>
                        <button type="button" class="gmb-btn gmb-btn-secondary gmb-db-tool-btn" id="<?php echo esc_attr($tool['id']); ?>"><?php echo esc_html($tool['button_text']); ?></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TAB 3: System Status -->
            <div id="gmb-tools-system" class="gmb-tools-content-panel <?php echo ($active_tab === 'gmb-tools-system') ? 'active' : ''; ?>" role="tabpanel">
                <div class="gmb-settings-panel-header gmb-text-left">
                    <h2 class="gmb-heading-2"><?php esc_html_e('System Diagnostic & Server Environment', 'gmb-ranker-seo-automation'); ?></h2>
                    <p class="gmb-form-help"><?php esc_html_e('Comprehensive environment audit to verify SEO automation and indexing compatibility.', 'gmb-ranker-seo-automation'); ?></p>
                </div>

                <div class="gmb-grid-2">
                    <!-- Environment Box 1 -->
                    <div class="gmb-card">
                        <h4 class="gmb-env-card-header"><?php esc_html_e('WordPress Environment', 'gmb-ranker-seo-automation'); ?></h4>
                        <table class="gmb-status-table">
                            <tr><td class="gmb-text-muted"><?php esc_html_e('WordPress Version:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['wordpress']['version']); ?></td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('Site URL:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-td-site-url"><?php echo esc_html($sys_status['wordpress']['site_url']); ?></td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('Multisite:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['wordpress']['multisite']); ?></td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('REST API Endpoint:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-success gmb-font-semibold"><?php echo esc_html($sys_status['wordpress']['rest_status']); ?> &check;</td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('Theme Active:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['wordpress']['theme_name']); ?></td></tr>
                        </table>
                    </div>

                    <!-- Environment Box 2 -->
                    <div class="gmb-card">
                        <h4 class="gmb-env-card-header"><?php esc_html_e('Server & PHP Runtime', 'gmb-ranker-seo-automation'); ?></h4>
                        <table class="gmb-status-table">
                            <tr><td class="gmb-text-muted"><?php esc_html_e('PHP Version:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['server']['php_version']); ?></td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('Memory Limit:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['server']['memory_limit']); ?></td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('cURL Extension:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-success gmb-font-semibold"><?php echo esc_html($sys_status['server']['curl_active']); ?> &check;</td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('OpenSSL JWT Support:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-success gmb-font-semibold"><?php echo esc_html($sys_status['server']['openssl_supported']); ?> &check;</td></tr>
                            <tr><td class="gmb-text-muted"><?php esc_html_e('Web Server:', 'gmb-ranker-seo-automation'); ?></td><td class="gmb-text-right gmb-text-bold"><?php echo esc_html($sys_status['server']['server_software']); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Version Control -->
            <div id="gmb-tools-version" class="gmb-tools-content-panel <?php echo ($active_tab === 'gmb-tools-version') ? 'active' : ''; ?>" role="tabpanel">
                <div class="gmb-settings-panel-header gmb-text-left">
                    <h2 class="gmb-heading-2"><?php esc_html_e('Version Control & Release Information', 'gmb-ranker-seo-automation'); ?></h2>
                    <p class="gmb-form-help"><?php esc_html_e('Inspect current plugin version signatures and environment synchronization.', 'gmb-ranker-seo-automation'); ?></p>
                </div>

                <div class="gmb-version-card">
                    <div class="gmb-version-card-header">
                        <div>
                            <div class="gmb-version-card-title"><?php echo esc_html($ver_control['title']); ?></div>
                            <div class="gmb-version-card-sub"><?php echo esc_html($ver_control['subtitle']); ?></div>
                        </div>
                        <span class="gmb-status-pill gmb-status-pill--info gmb-version-pill">v<?php echo esc_html($ver_control['installed_version']); ?></span>
                    </div>
                    <div class="gmb-version-desc">
                        <?php echo esc_html($ver_control['description']); ?>
                    </div>
                    <div class="gmb-version-actions">
                        <a href="<?php echo esc_url($ver_control['plugins_url']); ?>" class="gmb-btn gmb-btn-secondary gmb-btn-version-link"><?php esc_html_e('View in Plugins List', 'gmb-ranker-seo-automation'); ?></a>
                        <a href="<?php echo esc_url($ver_control['cloud_url']); ?>" target="_blank" class="gmb-btn gmb-btn-secondary gmb-btn-version-cloud"><?php esc_html_e('Check Cloud Releases ↗', 'gmb-ranker-seo-automation'); ?></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php endif; ?>
