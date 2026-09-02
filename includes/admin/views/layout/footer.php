<?php
if (!defined('ABSPATH')) exit;
$api_key          = isset($api_key) ? $api_key : get_option('gmb_ranker_api_key', '');
$editor_role      = isset($editor_role) ? $editor_role : get_role('editor');
$author_role      = isset($author_role) ? $author_role : get_role('author');
$contributor_role = isset($contributor_role) ? $contributor_role : get_role('contributor');
?>
        <!-- Modal: Handshake API Key -->
        <div class="rm-overlay" id="api-settings-overlay">
            <div class="rm-dialog gmb-input--max-480">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">Autopilot API Key Settings</h3>
                    <button type="button" class="rm-dialog-close" id="close-api-settings" onclick="gmbCloseModal('api-settings-overlay')">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_ranker_settings_group'); ?>
                        
                        <div class="gmb-form-group">
                            <label class="gmb-label gmb-form-label" >GMB Ranker API Secret Key</label>
                            <input type="password" name="gmb_ranker_api_key" value="<?php echo esc_attr($api_key); ?>" class="gmb-input-full-pad" placeholder="Paste your gr_... secret key here" />
                            <p class="description" class="gmb-form-help">This key authorizes secure communication between your GMB Ranker dashboard and your WordPress site.</p>
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save API Key"  />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="rm-overlay" id="db-tools-settings-overlay">
            <div class="rm-dialog" class="gmb-modal-box-600">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">Database Cleanup & Optimizations</h3>
                    <button type="button" class="rm-dialog-close" id="close-db-settings" onclick="gmbCloseModal('db-tools-settings-overlay')">×</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-modal-box-desc">Execute targeted cleanups and optimizations to keep your tables fast and database slim.</p>
                    
                    <div class="gmb-flex-col-gap-12">
                        
                        <div class="gmb-module-item-card" class="gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold" class="gmb-modal-toggle-label">Optimize Database Tables</strong>
                                <span class="gmb-text-muted gmb-text-xs">Runs optimization statements on core WP tables.</span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm" id="gmb-db-optimize-btn" class="gmb-flex-shrink-0">Run Tool</button>
                        </div>

                        <div class="gmb-module-item-card" class="gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold" class="gmb-modal-toggle-label">Clean Orphan Meta</strong>
                                <span class="gmb-text-muted gmb-text-xs">Deletes post metadata entries with missing posts.</span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm" id="gmb-db-orphan-btn" class="gmb-flex-shrink-0">Run Tool</button>
                        </div>

                        <div class="gmb-module-item-card" class="gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold" class="gmb-modal-toggle-label">Purge Transient Cache</strong>
                                <span class="gmb-text-muted gmb-text-xs">Clears temporary database transient entries.</span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm" id="gmb-db-transients-btn" class="gmb-flex-shrink-0">Run Tool</button>
                        </div>

                        <div class="gmb-module-item-card" class="gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold" class="gmb-modal-toggle-label">Import from Rank Math</strong>
                                <span class="gmb-text-muted gmb-text-xs">Imports Focus Keywords, SEO Titles, Descriptions, Canonicals & Robots from Rank Math.</span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm" id="gmb-db-import-rankmath-btn" class="gmb-flex-shrink-0">Run Tool</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="rm-overlay" id="role-manager-settings-overlay">
            <div class="rm-dialog" class="gmb-modal-box-600">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">Role Permissions Manager</h3>
                    <button type="button" class="rm-dialog-close" id="close-role-settings" onclick="gmbCloseModal('role-manager-settings-overlay')">×</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-modal-box-desc">Toggle capability authorization assignments to manage access per WordPress role.</p>
                    
                    <table class="gmb-modal-table">
                        <thead>
                            <tr class="gmb-modal-table-header">
                                <th class="gmb-modal-table-th">Role Name</th>
                                <th class="gmb-text-center">Manage Settings</th>
                                <th class="gmb-text-center">Edit Metadata</th>
                                <th class="gmb-text-center">Manage Redirects</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="gmb-border-bottom">
                                <td class="gmb-text-bold">Administrator</td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                            </tr>
                            <tr class="gmb-border-bottom">
                                <td class="gmb-text-bold">Editor</td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="editor" data-cap="gmb_ranker_manage_settings" <?php checked($editor_role && $editor_role->has_cap('gmb_ranker_manage_settings')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="editor" data-cap="gmb_ranker_edit_metadata" <?php checked($editor_role && $editor_role->has_cap('gmb_ranker_edit_metadata')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="editor" data-cap="gmb_ranker_manage_redirects" <?php checked($editor_role && $editor_role->has_cap('gmb_ranker_manage_redirects')); ?> /></td>
                            </tr>
                            <tr class="gmb-border-bottom">
                                <td class="gmb-text-bold">Author</td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="author" data-cap="gmb_ranker_manage_settings" <?php checked($author_role && $author_role->has_cap('gmb_ranker_manage_settings')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="author" data-cap="gmb_ranker_edit_metadata" <?php checked($author_role && $author_role->has_cap('gmb_ranker_edit_metadata')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="author" data-cap="gmb_ranker_manage_redirects" <?php checked($author_role && $author_role->has_cap('gmb_ranker_manage_redirects')); ?> /></td>
                            </tr>
                            <tr class="gmb-border-bottom">
                                <td class="gmb-text-bold">Contributor</td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="contributor" data-cap="gmb_ranker_manage_settings" <?php checked($contributor_role && $contributor_role->has_cap('gmb_ranker_manage_settings')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="contributor" data-cap="gmb_ranker_edit_metadata" <?php checked($contributor_role && $contributor_role->has_cap('gmb_ranker_edit_metadata')); ?> /></td>
                                <td class="gmb-text-center"><input type="checkbox" class="gmb-role-checkbox" data-role="contributor" data-cap="gmb_ranker_manage_redirects" <?php checked($contributor_role && $contributor_role->has_cap('gmb_ranker_manage_redirects')); ?> /></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="gmb-text-right">
                        <button type="button" class="wizard-btn-primary" id="gmb-save-roles-btn" class="gmb-btn-modal-action">Save Role Permissions</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rm-overlay" id="instant-indexing-settings-overlay">
            <div class="rm-dialog gmb-input--max-480" >
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">Instant Indexing (IndexNow)</h3>
                    <button type="button" class="rm-dialog-close" id="close-indexing-settings" onclick="gmbCloseModal('instant-indexing-settings-overlay')">×</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-text-muted-desc">Manually submit published URLs to IndexNow search engine indexing API instantly.</p>
                    
                    <div class="gmb-form-group" class="gmb-mb-20">
                        <label class="gmb-label-bold-sm">URLs to Index (One per line)</label>
                        <textarea id="gmb-indexing-urls" rows="6" class="gmb-input-full-sm" placeholder="<?php echo esc_url(home_url('/some-page/')); ?>"></textarea>
                    </div>

                    <div class="gmb-text-right">
                        <button type="button" class="wizard-btn-primary" id="gmb-submit-indexing-btn" class="gmb-btn-modal-action">Submit to IndexNow</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rm-overlay" id="preferred-source-settings-overlay">
            <div class="rm-dialog gmb-input--max-480" >
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">Preferred Source Button Config</h3>
                    <button type="button" class="rm-dialog-close" id="close-preferred-source-settings" onclick="gmbCloseModal('preferred-source-settings-overlay')">×</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gps_settings_group'); ?>
                        
                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Target Domain</label>
                            <input type="text" name="gps_target_domain" value="<?php echo esc_attr(get_option('gps_target_domain', wp_parse_url(home_url(), PHP_URL_HOST))); ?>" class="gmb-input-full-pad" placeholder="e.g. <?php echo esc_attr(wp_parse_url(home_url(), PHP_URL_HOST)); ?>" />
                        </div>

                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Button Text</label>
                            <input type="text" name="gps_button_text" value="<?php echo esc_attr(get_option('gps_button_text', 'Add to Preferred Sources')); ?>" class="gmb-input-full-pad" />
                        </div>

                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Button Theme</label>
                            <select name="gps_button_theme" class="gmb-select">
                                <option value="google_white" <?php selected(get_option('gps_button_theme', 'google_white'), 'google_white'); ?>>Google White</option>
                                <option value="google_blue" <?php selected(get_option('gps_button_theme', 'google_blue'), 'google_blue'); ?>>Google Blue</option>
                                <option value="google_dark" <?php selected(get_option('gps_button_theme', 'google_dark'), 'google_dark'); ?>>Google Dark</option>
                            </select>
                        </div>

                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Button Size</label>
                            <select name="gps_button_size" class="gmb-select">
                                <option value="small" <?php selected(get_option('gps_button_size', 'medium'), 'small'); ?>>Small</option>
                                <option value="medium" <?php selected(get_option('gps_button_size', 'medium'), 'medium'); ?>>Medium</option>
                                <option value="large" <?php selected(get_option('gps_button_size', 'medium'), 'large'); ?>>Large</option>
                            </select>
                        </div>

                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Insertion Location</label>
                            <select name="gps_insertion_location" class="gmb-select">
                                <option value="content_start" <?php selected(get_option('gps_insertion_location', 'content_end'), 'content_start'); ?>>Content Start (Top)</option>
                                <option value="content_end" <?php selected(get_option('gps_insertion_location', 'content_end'), 'content_end'); ?>>Content End (Bottom)</option>
                            </select>
                        </div>

                        <div class="gmb-form-group" class="gmb-checkbox-row-16">
                            <input type="checkbox" name="gps_enabled" value="1" <?php checked(get_option('gps_enabled', '1'), '1'); ?> id="gps-enabled-chk" />
                            <label for="gps-enabled-chk" class="gmb-cursor-pointer-bold">Enable Button Injections</label>
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Button Config"  />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="rm-overlay" id="ai-provider-settings-overlay">
            <div class="rm-dialog gmb-input--max-480" >
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title">AI API Provider Config</h3>
                    <button type="button" class="rm-dialog-close" id="close-ai-provider-settings" onclick="gmbCloseModal('ai-provider-settings-overlay')">×</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_ranker_settings_group'); ?>
                        
                        <div class="gmb-form-group gmb-mb-16" >
                            <label class="gmb-label gmb-form-label" >Active AI Provider</label>
                            <select name="gmb_ai_provider" id="gmb-ai-provider-select" class="gmb-select">
                                <option value="openrouter" <?php selected(get_option('gmb_ai_provider', 'openrouter'), 'openrouter'); ?>>OpenRouter</option>
                                <option value="groq" <?php selected(get_option('gmb_ai_provider', 'openrouter'), 'groq'); ?>>Groq</option>
                                <option value="ollama" <?php selected(get_option('gmb_ai_provider', 'openrouter'), 'ollama'); ?>>Ollama (Local AI)</option>
                            </select>
                        </div>

                        <!-- OpenRouter Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-openrouter" >
                            <div class="gmb-form-group gmb-mb-12" >
                                <label class="gmb-label gmb-form-label" >OpenRouter API Key</label>
                                <input type="password" name="gmb_ai_openrouter_key" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_key', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" >OpenRouter Model</label>
                                <input type="text" name="gmb_ai_openrouter_model" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>

                        <!-- Groq Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-groq">
                            <div class="gmb-form-group gmb-mb-12" >
                                <label class="gmb-label gmb-form-label" >Groq API Key</label>
                                <input type="password" name="gmb_ai_groq_key" value="<?php echo esc_attr(get_option('gmb_ai_groq_key', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" >Groq Model</label>
                                <input type="text" name="gmb_ai_groq_model" value="<?php echo esc_attr(get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>

                        <!-- Ollama Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-ollama">
                            <div class="gmb-form-group gmb-mb-12" >
                                <label class="gmb-label gmb-form-label" >Ollama Base URL</label>
                                <input type="text" name="gmb_ai_ollama_url" value="<?php echo esc_attr(get_option('gmb_ai_ollama_url', 'http://localhost:11434')); ?>" class="gmb-input-full-pad" />
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" >Ollama Model</label>
                                <input type="text" name="gmb_ai_ollama_model" value="<?php echo esc_attr(get_option('gmb_ai_ollama_model', 'llama3')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save AI Config"  />
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            window.gmb_ranker_admin = window.gmb_ranker_admin || {};
            window.gmb_ranker_admin.nonce = '<?php echo esc_js( wp_create_nonce( "gmb_seo_save_nonce" ) ); ?>';
        </script>

    </div>
</div>

