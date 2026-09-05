<?php
/**
 * Admin Layout Footer & Modal Dialogs
 *
 * Enterprise-grade, accessible, dynamic modal overlay templates for API credentials,
 * database tools, role permissions, instant indexing, preferred sources, and AI providers.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$api_key = isset($api_key) ? $api_key : get_option('gmb_ranker_api_key', '');

// Fetch registered WordPress roles dynamically
$wp_roles_obj = wp_roles();
$all_roles = $wp_roles_obj ? $wp_roles_obj->role_objects : array();
$manageable_roles = array();

foreach ($all_roles as $role_slug => $role_obj) {
    if ($role_slug !== 'administrator') {
        $manageable_roles[$role_slug] = $role_obj;
    }
}
?>
        <!-- Modal 1: Autopilot API Key Settings -->
        <div class="rm-overlay" id="api-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="api-settings-title">
            <div class="rm-dialog gmb-input--max-480">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="api-settings-title"><?php esc_html_e('Autopilot API Key Settings', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-api-settings" onclick="gmbCloseModal('api-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_ranker_settings_group'); ?>
                        
                        <div class="gmb-form-group">
                            <label class="gmb-label gmb-form-label" for="gmb_ranker_api_key_field"><?php esc_html_e('GMB Ranker API Secret Key', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="password" id="gmb_ranker_api_key_field" name="gmb_ranker_api_key" value="<?php echo esc_attr($api_key); ?>" class="gmb-input-full-pad" placeholder="<?php esc_attr_e('Paste your gr_... secret key here', 'gmb-ranker-seo-automation'); ?>" autocomplete="off" />
                            <p class="description gmb-form-help"><?php esc_html_e('This key authorizes secure communication between your GMB Ranker dashboard and your WordPress site.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save API Key', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 2: Database Tools & Optimization -->
        <div class="rm-overlay" id="db-tools-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="db-tools-title">
            <div class="rm-dialog gmb-modal-box-600">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="db-tools-title"><?php esc_html_e('Database Cleanup & Optimizations', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-db-settings" onclick="gmbCloseModal('db-tools-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-modal-box-desc"><?php esc_html_e('Execute targeted cleanups and optimizations to keep your database tables fast and lightweight.', 'gmb-ranker-seo-automation'); ?></p>
                    
                    <div class="gmb-flex-col-gap-12">
                        
                        <div class="gmb-module-item-card gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold gmb-modal-toggle-label"><?php esc_html_e('Optimize Database Tables', 'gmb-ranker-seo-automation'); ?></strong>
                                <span class="gmb-text-muted gmb-text-xs d-block"><?php esc_html_e('Runs optimization statements on core WordPress database tables.', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm gmb-flex-shrink-0" id="gmb-db-optimize-btn"><?php esc_html_e('Run Tool', 'gmb-ranker-seo-automation'); ?></button>
                        </div>

                        <div class="gmb-module-item-card gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold gmb-modal-toggle-label"><?php esc_html_e('Clean Orphan Meta', 'gmb-ranker-seo-automation'); ?></strong>
                                <span class="gmb-text-muted gmb-text-xs d-block"><?php esc_html_e('Deletes post, term, and user metadata entries with missing parent records.', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm gmb-flex-shrink-0" id="gmb-db-orphan-btn"><?php esc_html_e('Run Tool', 'gmb-ranker-seo-automation'); ?></button>
                        </div>

                        <div class="gmb-module-item-card gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold gmb-modal-toggle-label"><?php esc_html_e('Purge Transient Cache', 'gmb-ranker-seo-automation'); ?></strong>
                                <span class="gmb-text-muted gmb-text-xs d-block"><?php esc_html_e('Clears expired and temporary database transient entries.', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm gmb-flex-shrink-0" id="gmb-db-transients-btn"><?php esc_html_e('Run Tool', 'gmb-ranker-seo-automation'); ?></button>
                        </div>

                        <div class="gmb-module-item-card gmb-modal-toggle-row">
                            <div>
                                <strong class="gmb-text-main gmb-text-bold gmb-modal-toggle-label"><?php esc_html_e('Import from Rank Math', 'gmb-ranker-seo-automation'); ?></strong>
                                <span class="gmb-text-muted gmb-text-xs d-block"><?php esc_html_e('Imports Focus Keywords, SEO Titles, Descriptions & Robots settings from Rank Math.', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                            <button type="button" class="button button-primary gmb-btn--primary gmb-btn--sm gmb-flex-shrink-0" id="gmb-db-import-rankmath-btn"><?php esc_html_e('Run Tool', 'gmb-ranker-seo-automation'); ?></button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 3: Dynamic Role Permissions Manager -->
        <div class="rm-overlay" id="role-manager-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="role-manager-title">
            <div class="rm-dialog gmb-modal-box-600">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="role-manager-title"><?php esc_html_e('Role Permissions Manager', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-role-settings" onclick="gmbCloseModal('role-manager-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-modal-box-desc"><?php esc_html_e('Toggle capability authorization assignments to manage plugin access per WordPress role.', 'gmb-ranker-seo-automation'); ?></p>
                    
                    <table class="gmb-modal-table">
                        <thead>
                            <tr class="gmb-modal-table-header">
                                <th class="gmb-modal-table-th"><?php esc_html_e('Role Name', 'gmb-ranker-seo-automation'); ?></th>
                                <th class="gmb-text-center"><?php esc_html_e('Manage Settings', 'gmb-ranker-seo-automation'); ?></th>
                                <th class="gmb-text-center"><?php esc_html_e('Edit Metadata', 'gmb-ranker-seo-automation'); ?></th>
                                <th class="gmb-text-center"><?php esc_html_e('Manage Redirects', 'gmb-ranker-seo-automation'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="gmb-border-bottom">
                                <td class="gmb-text-bold"><?php esc_html_e('Administrator', 'gmb-ranker-seo-automation'); ?></td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                                <td class="gmb-text-center"><input type="checkbox" checked disabled /></td>
                            </tr>
                            <?php foreach ($manageable_roles as $r_slug => $r_obj) : ?>
                                <tr class="gmb-border-bottom">
                                    <td class="gmb-text-bold"><?php echo esc_html($r_obj->name); ?></td>
                                    <td class="gmb-text-center">
                                        <input type="checkbox" class="gmb-role-checkbox" data-role="<?php echo esc_attr($r_slug); ?>" data-cap="gmb_ranker_manage_settings" <?php checked($r_obj->has_cap('gmb_ranker_manage_settings')); ?> />
                                    </td>
                                    <td class="gmb-text-center">
                                        <input type="checkbox" class="gmb-role-checkbox" data-role="<?php echo esc_attr($r_slug); ?>" data-cap="gmb_ranker_edit_metadata" <?php checked($r_obj->has_cap('gmb_ranker_edit_metadata')); ?> />
                                    </td>
                                    <td class="gmb-text-center">
                                        <input type="checkbox" class="gmb-role-checkbox" data-role="<?php echo esc_attr($r_slug); ?>" data-cap="gmb_ranker_manage_redirects" <?php checked($r_obj->has_cap('gmb_ranker_manage_redirects')); ?> />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="gmb-text-right" style="margin-top: 16px;">
                        <button type="button" class="wizard-btn-primary gmb-btn-modal-action" id="gmb-save-roles-btn"><?php esc_html_e('Save Role Permissions', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 4: Instant Indexing (IndexNow) -->
        <div class="rm-overlay" id="instant-indexing-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="instant-indexing-title">
            <div class="rm-dialog gmb-input--max-480">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="instant-indexing-title"><?php esc_html_e('Instant Indexing (IndexNow)', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-indexing-settings" onclick="gmbCloseModal('instant-indexing-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <p class="gmb-text-muted-desc"><?php esc_html_e('Manually submit published URLs to IndexNow search engine indexing API instantly.', 'gmb-ranker-seo-automation'); ?></p>
                    
                    <div class="gmb-form-group gmb-mb-20">
                        <label class="gmb-label-bold-sm" for="gmb-indexing-urls"><?php esc_html_e('URLs to Index (One per line)', 'gmb-ranker-seo-automation'); ?></label>
                        <textarea id="gmb-indexing-urls" rows="6" class="gmb-input-full-sm" placeholder="<?php echo esc_url(home_url('/sample-page/')); ?>"></textarea>
                    </div>

                    <div class="gmb-text-right">
                        <button type="button" class="wizard-btn-primary gmb-btn-modal-action" id="gmb-submit-indexing-btn"><?php esc_html_e('Submit to IndexNow', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 5: Preferred Source Button Config -->
        <div class="rm-overlay" id="preferred-source-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="preferred-source-title">
            <div class="rm-dialog gmb-input--max-480">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="preferred-source-title"><?php esc_html_e('Preferred Source Button Config', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-preferred-source-settings" onclick="gmbCloseModal('preferred-source-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_gps_settings_group'); ?>
                        
                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb_gps_target_domain"><?php esc_html_e('Target Domain', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb_gps_target_domain" name="gmb_gps_target_domain" value="<?php echo esc_attr(get_option('gmb_gps_target_domain', wp_parse_url(home_url(), PHP_URL_HOST))); ?>" class="gmb-input-full-pad" placeholder="e.g. <?php echo esc_attr(wp_parse_url(home_url(), PHP_URL_HOST)); ?>" />
                        </div>

                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb_gps_button_text"><?php esc_html_e('Button Text', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb_gps_button_text" name="gmb_gps_button_text" value="<?php echo esc_attr(get_option('gmb_gps_button_text', 'Add to Preferred Sources')); ?>" class="gmb-input-full-pad" />
                        </div>

                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb_gps_button_theme"><?php esc_html_e('Button Theme', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb_gps_button_theme" name="gmb_gps_button_theme" class="gmb-select">
                                <option value="google_white" <?php selected(get_option('gmb_gps_button_theme', 'google_white'), 'google_white'); ?>><?php esc_html_e('Google White', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="google_blue" <?php selected(get_option('gmb_gps_button_theme', 'google_white'), 'google_blue'); ?>><?php esc_html_e('Google Blue', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="google_dark" <?php selected(get_option('gmb_gps_button_theme', 'google_white'), 'google_dark'); ?>><?php esc_html_e('Google Dark', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>

                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb_gps_button_size"><?php esc_html_e('Button Size', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb_gps_button_size" name="gmb_gps_button_size" class="gmb-select">
                                <option value="small" <?php selected(get_option('gmb_gps_button_size', 'medium'), 'small'); ?>><?php esc_html_e('Small', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="medium" <?php selected(get_option('gmb_gps_button_size', 'medium'), 'medium'); ?>><?php esc_html_e('Medium', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="large" <?php selected(get_option('gmb_gps_button_size', 'medium'), 'large'); ?>><?php esc_html_e('Large', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>

                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb_gps_insertion_location"><?php esc_html_e('Insertion Location', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb_gps_insertion_location" name="gmb_gps_insertion_location" class="gmb-select">
                                <option value="content_start" <?php selected(get_option('gmb_gps_insertion_location', 'content_end'), 'content_start'); ?>><?php esc_html_e('Content Start (Top)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="content_end" <?php selected(get_option('gmb_gps_insertion_location', 'content_end'), 'content_end'); ?>><?php esc_html_e('Content End (Bottom)', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>

                        <div class="gmb-form-group gmb-checkbox-row-16">
                            <input type="checkbox" name="gps_enabled" value="1" <?php checked(get_option('gps_enabled', '1'), '1'); ?> id="gps-enabled-chk" />
                            <label for="gps-enabled-chk" class="gmb-cursor-pointer-bold"><?php esc_html_e('Enable Button Injections', 'gmb-ranker-seo-automation'); ?></label>
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Button Config', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 6: AI API Provider Config -->
        <div class="rm-overlay" id="ai-provider-settings-overlay" aria-hidden="true" role="dialog" aria-labelledby="ai-provider-title">
            <div class="rm-dialog gmb-input--max-480">
                <div class="rm-dialog-header">
                    <h3 class="rm-dialog-title" id="ai-provider-title"><?php esc_html_e('AI API Provider Config', 'gmb-ranker-seo-automation'); ?></h3>
                    <button type="button" class="rm-dialog-close" id="close-ai-provider-settings" onclick="gmbCloseModal('ai-provider-settings-overlay')" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <div class="rm-dialog-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('gmb_ranker_settings_group'); ?>
                        
                        <div class="gmb-form-group gmb-mb-16">
                            <label class="gmb-label gmb-form-label" for="gmb-ai-provider-select"><?php esc_html_e('Active AI Provider', 'gmb-ranker-seo-automation'); ?></label>
                            <select name="gmb_ai_provider" id="gmb-ai-provider-select" class="gmb-select">
                                <option value="openrouter" <?php selected(get_option('gmb_ai_provider', ''), 'openrouter'); ?>><?php esc_html_e('OpenRouter', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="groq" <?php selected(get_option('gmb_ai_provider', ''), 'groq'); ?>><?php esc_html_e('Groq', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="ollama" <?php selected(get_option('gmb_ai_provider', ''), 'ollama'); ?>><?php esc_html_e('Ollama (Local AI)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="nvidia" <?php selected(get_option('gmb_ai_provider', ''), 'nvidia'); ?>><?php esc_html_e('NVIDIA NIM', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>

                        <!-- OpenRouter Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-openrouter">
                            <div class="gmb-form-group gmb-mb-12">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_openrouter_key"><?php esc_html_e('OpenRouter API Key', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="password" id="gmb_ai_openrouter_key" name="gmb_ai_openrouter_key" value="" placeholder="<?php echo !empty(get_option('gmb_ai_openrouter_key', '')) ? esc_attr__('Key saved; leave blank to keep', 'gmb-ranker-seo-automation') : ''; ?>" class="gmb-input-full-pad" autocomplete="off" />
                                <?php if (!empty(get_option('gmb_ai_openrouter_key', ''))) : ?><input type="hidden" name="gmb_ai_openrouter_key_keep" value="1" /><?php endif; ?>
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_openrouter_model"><?php esc_html_e('OpenRouter Model', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_ai_openrouter_model" name="gmb_ai_openrouter_model" value="<?php echo esc_attr(get_option('gmb_ai_openrouter_model', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>

                        <!-- Groq Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-groq">
                            <div class="gmb-form-group gmb-mb-12">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_groq_key"><?php esc_html_e('Groq API Key', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="password" id="gmb_ai_groq_key" name="gmb_ai_groq_key" value="" placeholder="<?php echo !empty(get_option('gmb_ai_groq_key', '')) ? esc_attr__('Key saved; leave blank to keep', 'gmb-ranker-seo-automation') : ''; ?>" class="gmb-input-full-pad" autocomplete="off" />
                                <?php if (!empty(get_option('gmb_ai_groq_key', ''))) : ?><input type="hidden" name="gmb_ai_groq_key_keep" value="1" /><?php endif; ?>
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_groq_model"><?php esc_html_e('Groq Model', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_ai_groq_model" name="gmb_ai_groq_model" value="<?php echo esc_attr(get_option('gmb_ai_groq_model', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>

                        <!-- Ollama Configuration -->
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-ollama">
                            <div class="gmb-form-group gmb-mb-12">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_ollama_url"><?php esc_html_e('Ollama Base URL', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_ai_ollama_url" name="gmb_ai_ollama_url" value="<?php echo esc_attr(get_option('gmb_ai_ollama_url', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                            <div class="gmb-form-group">
                                <label class="gmb-label gmb-form-label" for="gmb_ai_ollama_model"><?php esc_html_e('Ollama Model', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_ai_ollama_model" name="gmb_ai_ollama_model" value="<?php echo esc_attr(get_option('gmb_ai_ollama_model', '')); ?>" class="gmb-input-full-pad" />
                            </div>
                        </div>
                        <div class="ai-provider-section gmb-mb-16" id="ai-section-nvidia">
                            <label class="gmb-label gmb-form-label" for="gmb_ai_nvidia_key"><?php esc_html_e('NVIDIA API Key', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="password" id="gmb_ai_nvidia_key" name="gmb_ai_nvidia_key" value="" placeholder="<?php echo !empty(get_option('gmb_ai_nvidia_key', '')) ? esc_attr__('Key saved; leave blank to keep', 'gmb-ranker-seo-automation') : ''; ?>" class="gmb-input-full-pad" autocomplete="off" />
                            <?php if (!empty(get_option('gmb_ai_nvidia_key', ''))) : ?><input type="hidden" name="gmb_ai_nvidia_key_keep" value="1" /><?php endif; ?>
                            <label class="gmb-label gmb-form-label" for="gmb_ai_nvidia_model"><?php esc_html_e('NVIDIA Model', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb_ai_nvidia_model" name="gmb_ai_nvidia_model" value="<?php echo esc_attr(get_option('gmb_ai_nvidia_model', '')); ?>" class="gmb-input-full-pad" />
                        </div>
                        
                        <div class="gmb-text-right">
                            <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save AI Config', 'gmb-ranker-seo-automation'); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
