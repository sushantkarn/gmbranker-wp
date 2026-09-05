<?php
/**
 * Integrations & API Hub Subtab View
 *
 * Enterprise-grade presentation layer consuming GMB_Ranker_SEO_Integration_Registry.
 * Zero direct database calls, zero render-time side effects, masked credentials,
 * dynamic AI provider rendering, and strict SSRF-safe outbound validation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '');

if ($current_page === 'gmb-ranker-integrations') : 
    $vm       = class_exists('GMB_Ranker_SEO_Integration_Registry') ? GMB_Ranker_SEO_Integration_Registry::get_view_model() : array();
    $cloud    = isset($vm['cloud']) ? $vm['cloud'] : array();
    $ai       = isset($vm['ai']) ? $vm['ai'] : array();
    $indexnow = isset($vm['indexnow']) ? $vm['indexnow'] : array();
    $webhooks = isset($vm['webhooks']) ? $vm['webhooks'] : array();
    $provider_chain = class_exists('GMB_Ranker_SEO_Integration_Registry') ? GMB_Ranker_SEO_Integration_Registry::get_ai_provider_chain() : array();

    $workspace = isset($cloud['workspace']) ? $cloud['workspace'] : array();
?>
<div class="rm-tab-content active" id="rm-tab-integrations">
    <div class="gmb-integrations-container">
        <div class="gmb-settings-panel-header gmb-text-left">
            <h2 class="gmb-heading-2"><?php esc_html_e('Integrations & API Hub', 'gmb-ranker-seo-automation'); ?></h2>
            <p class="gmb-form-help">
                <?php esc_html_e('Connect your website with GMB Ranker Cloud Platform, Google Search Console, Google Analytics 4, IndexNow, AI engines, and automation webhooks.', 'gmb-ranker-seo-automation'); ?>
                <a href="https://gmbranker.org" target="_blank" rel="noopener noreferrer" class="gmb-help-link"><?php esc_html_e('Learn more', 'gmb-ranker-seo-automation'); ?> &rarr;</a>
            </p>
        </div>

        <form method="post" action="options.php" novalidate>
            <?php settings_fields('gmb_ranker_integrations_group'); ?>
        
            <div class="gmb-integrations-stack">

                <!-- Card 1: GMB Ranker Cloud Platform & Google Ecosystem Sync -->
                <div class="gmb-integration-card">
                    <div class="gmb-integration-card-header">
                        <div class="gmb-integration-card-header-left">
                            <div class="gmb-integration-icon-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h3 class="gmb-heading-3"><?php esc_html_e('GMB Ranker Cloud & Google Workspace Connections', 'gmb-ranker-seo-automation'); ?></h3>
                                <p class="gmb-text-muted"><?php esc_html_e('Your GMB Ranker API key automatically links and synchronizes Search Console, Google Analytics 4, and Google Business Profile from your Cloud Workspace.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                        <div>
                            <?php if (!empty($cloud['configured'])) : ?>
                                <span class="gmb-status-pill gmb-status-pill--success">
                                    <span class="gmb-status-dot"></span>
                                    <?php esc_html_e('Connected & Synced', 'gmb-ranker-seo-automation'); ?>
                                </span>
                            <?php else : ?>
                                <span class="gmb-status-pill gmb-status-pill--danger">
                                    <span class="gmb-status-dot"></span>
                                    <?php esc_html_e('Disconnected (Key Required)', 'gmb-ranker-seo-automation'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="gmb-integration-grid-2col">
                        <div>
                            <label for="gmb_ranker_api_key_input" class="gmb-form-label"><?php esc_html_e('GMB Ranker API Secret Key', 'gmb-ranker-seo-automation'); ?></label>
                            <div class="gmb-flex-gap-sm">
                                <input type="password" id="gmb_ranker_api_key_input" name="gmb_ranker_api_key" value="<?php echo esc_attr($cloud['api_key']); ?>" placeholder="<?php esc_attr_e('Paste your gr_live_... secret key', 'gmb-ranker-seo-automation'); ?>" class="gmb-integration-input gmb-flex-1" autocomplete="off" />
                                <button type="button" id="gmb-toggle-key-visibility" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle"><?php esc_html_e('Show', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                            <p class="gmb-form-help"><?php esc_html_e('Get your developer API key from', 'gmb-ranker-seo-automation'); ?> <a href="https://gmbranker.org" target="_blank" rel="noopener noreferrer" class="gmb-help-link"><?php esc_html_e('GMB Ranker Dashboard → API Keys', 'gmb-ranker-seo-automation'); ?></a>.</p>
                        </div>
                        <div>
                            <label for="gmb_ranker_cloud_sync_select" class="gmb-form-label"><?php esc_html_e('Cloud Sync Frequency', 'gmb-ranker-seo-automation'); ?></label>
                            <select id="gmb_ranker_cloud_sync_select" name="gmb_ranker_cloud_sync" class="gmb-integration-select">
                                <option value="1" <?php selected('1', $cloud['sync_mode']); ?>><?php esc_html_e('Real-time Auto Sync (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="daily" <?php selected('daily', $cloud['sync_mode']); ?>><?php esc_html_e('Daily Background Sync', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="manual" <?php selected('manual', $cloud['sync_mode']); ?>><?php esc_html_e('Manual Sync Only', 'gmb-ranker-seo-automation'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Synchronized Google Ecosystem Connections Panel -->
                    <div class="gmb-synced-connections-wrap">
                        <div class="gmb-synced-connections-header">
                            <strong class="gmb-synced-title-label"><?php esc_html_e('Synced Cloud Workspace Connections (Google Accounts)', 'gmb-ranker-seo-automation'); ?></strong>
                            <a href="https://gmbranker.org" target="_blank" rel="noopener noreferrer" class="gmb-synced-link"><?php esc_html_e('Manage Connections in GMB Ranker Cloud', 'gmb-ranker-seo-automation'); ?> &nearr;</a>
                        </div>
                        <div class="gmb-synced-connections-grid">
                            <!-- Google Search Console -->
                            <div class="gmb-synced-connection-card">
                                <div class="gmb-synced-connection-title">
                                    <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-search-console.svg')); ?>" alt="Google Search Console" />
                                    <span><?php esc_html_e('Google Search Console', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <?php if (!empty($cloud['configured'])) : ?>
                                    <div class="gmb-synced-status-connected gmb-text-xs">&check; <?php esc_html_e('Connected via GMB Ranker', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Property:', 'gmb-ranker-seo-automation'); ?> <strong><?php echo !empty($workspace['gsc']) ? esc_html($workspace['gsc']) : esc_html__('Auto-linked', 'gmb-ranker-seo-automation'); ?></strong></div>
                                <?php else : ?>
                                    <div class="gmb-synced-status-pending gmb-text-xs"><?php esc_html_e('Pending API Key Link', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Property:', 'gmb-ranker-seo-automation'); ?> <?php esc_html_e('Not Linked', 'gmb-ranker-seo-automation'); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Google Analytics 4 -->
                            <div class="gmb-synced-connection-card">
                                <div class="gmb-synced-connection-title">
                                    <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-analytics.svg')); ?>" alt="Google Analytics 4" />
                                    <span><?php esc_html_e('Google Analytics 4', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <?php if (!empty($cloud['configured'])) : ?>
                                    <div class="gmb-synced-status-connected gmb-text-xs">&check; <?php esc_html_e('Connected via GMB Ranker', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Stream:', 'gmb-ranker-seo-automation'); ?> <strong><?php echo !empty($workspace['ga4']) ? esc_html($workspace['ga4']) : esc_html__('Auto-linked', 'gmb-ranker-seo-automation'); ?></strong></div>
                                <?php else : ?>
                                    <div class="gmb-synced-status-pending gmb-text-xs"><?php esc_html_e('Pending API Key Link', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Stream:', 'gmb-ranker-seo-automation'); ?> <?php esc_html_e('Not Linked', 'gmb-ranker-seo-automation'); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Google Business Profile -->
                            <div class="gmb-synced-connection-card">
                                <div class="gmb-synced-connection-title">
                                    <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-my-business.svg')); ?>" alt="Google Business Profile" />
                                    <span><?php esc_html_e('Google Business Profile', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <?php if (!empty($cloud['configured'])) : ?>
                                    <div class="gmb-synced-status-connected gmb-text-xs">&check; <?php esc_html_e('Synced & Reviews Active', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Location:', 'gmb-ranker-seo-automation'); ?> <strong><?php echo !empty($workspace['gmb']) ? esc_html($workspace['gmb']) : esc_html__('Auto-linked', 'gmb-ranker-seo-automation'); ?></strong></div>
                                <?php else : ?>
                                    <div class="gmb-synced-status-pending gmb-text-xs"><?php esc_html_e('Pending API Key Link', 'gmb-ranker-seo-automation'); ?></div>
                                    <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text"><?php esc_html_e('Location:', 'gmb-ranker-seo-automation'); ?> <?php esc_html_e('Not Linked', 'gmb-ranker-seo-automation'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: AI Provider & Model Engine (Dynamic Provider Registry) -->
                <div class="gmb-integration-card">
                    <div class="gmb-integration-card-header">
                        <div class="gmb-integration-card-header-left">
                            <div class="gmb-ai-icons-row">
                                <span class="gmb-ai-icon-box"><img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/openrouter.svg')); ?>" alt="OpenRouter" /></span>
                                <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/groq.svg')); ?>" alt="Groq" class="gmb-ai-icon-sm" />
                                <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/ollama-icon.svg')); ?>" alt="Ollama" class="gmb-ai-icon-sm" />
                            </div>
                            <div>
                                <h3 class="gmb-heading-3"><?php esc_html_e('AI Provider & Model Engine (Free & Open-Source)', 'gmb-ranker-seo-automation'); ?></h3>
                                <p class="gmb-text-muted"><?php esc_html_e('Powers automated SEO meta generation, focus keyword suggestions, and local content optimization using customizable AI providers.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                        <button type="button" class="gmb-btn gmb-btn-secondary gmb-ai-open-config" data-provider="">
                            <span aria-hidden="true">+</span> <?php esc_html_e('Connect Providers', 'gmb-ranker-seo-automation'); ?>
                        </button>
                    </div>

                    <div class="gmb-mb-16">
                        <input type="hidden" id="gmb_ai_provider_chain" name="gmb_ai_provider_chain" value="<?php echo esc_attr(wp_json_encode($provider_chain)); ?>" />
                        <h4 class="gmb-heading-4"><?php esc_html_e('AI Provider Priority', 'gmb-ranker-seo-automation'); ?></h4>
                        <p class="gmb-form-help"><?php esc_html_e('Providers are attempted from top to bottom. Drag rows to reorder, or use the priority buttons. Credentials remain server-side.', 'gmb-ranker-seo-automation'); ?></p>
                        <div class="gmb-ai-provider-table-head" aria-hidden="true">
                            <span><?php esc_html_e('Priority', 'gmb-ranker-seo-automation'); ?></span>
                            <span><?php esc_html_e('Provider', 'gmb-ranker-seo-automation'); ?></span>
                            <span><?php esc_html_e('Model', 'gmb-ranker-seo-automation'); ?></span>
                            <span><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></span>
                            <span><?php esc_html_e('Enabled', 'gmb-ranker-seo-automation'); ?></span>
                            <span><?php esc_html_e('Actions', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <div id="gmb-ai-provider-chain" class="gmb-ai-provider-chain" role="list">
                            <?php foreach ($provider_chain as $chain_index => $chain_entry) :
                                $chain_pid = $chain_entry['provider'];
                                if (!isset($ai['providers'][$chain_pid])) continue;
                                $chain_info = $ai['providers'][$chain_pid];
                                $chain_def = $chain_info['definition'];
                            ?>
                                <div class="gmb-ai-provider-row" draggable="true" role="listitem" data-provider-id="<?php echo esc_attr($chain_pid); ?>">
                                    <span class="gmb-ai-provider-drag" aria-hidden="true">&#9776;</span>
                                    <span class="gmb-ai-provider-order-controls"><button type="button" class="button-link gmb-ai-provider-up" aria-label="<?php esc_attr_e('Move provider up', 'gmb-ranker-seo-automation'); ?>">&#9650;</button><button type="button" class="button-link gmb-ai-provider-down" aria-label="<?php esc_attr_e('Move provider down', 'gmb-ranker-seo-automation'); ?>">&#9660;</button></span>
                                    <strong class="gmb-ai-provider-priority">#<?php echo esc_html($chain_index + 1); ?></strong>
                                    <span class="gmb-ai-provider-name"><?php echo esc_html($chain_def['name']); ?></span>
                                    <span class="gmb-ai-provider-model"><?php echo !empty($chain_info['model']) ? esc_html($chain_info['model']) : esc_html__('Model required', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="gmb-ai-provider-status <?php echo !empty($chain_info['configured']) ? 'is-configured' : 'is-unconfigured'; ?>"><?php echo !empty($chain_info['configured']) ? esc_html__('Configured', 'gmb-ranker-seo-automation') : esc_html__('Configuration required', 'gmb-ranker-seo-automation'); ?></span>
                                    <label class="gmb-checkbox-label"><input type="checkbox" class="gmb-ai-provider-enabled" <?php checked(1, $chain_entry['enabled']); ?> /> <?php esc_html_e('Enabled', 'gmb-ranker-seo-automation'); ?></label>
                                    <button type="button" class="button gmb-ai-open-config" data-provider="<?php echo esc_attr($chain_pid); ?>"><?php esc_html_e('Configure', 'gmb-ranker-seo-automation'); ?></button>
                                    <button type="button" class="button gmb-btn-test-ai-provider" data-provider="<?php echo esc_attr($chain_pid); ?>"><?php esc_html_e('Test Connection', 'gmb-ranker-seo-automation'); ?></button>
                                    <span class="gmb-ai-provider-test-result" aria-live="polite"></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <select id="gmb_ai_provider_select" name="gmb_ai_provider" class="gmb-screen-reader-only" aria-hidden="true" tabindex="-1">
                            <?php foreach ($ai['providers'] as $pid => $pinfo) : 
                                $def = $pinfo['definition'];
                            ?>
                                <option value="<?php echo esc_attr($pid); ?>" <?php selected($pid, $ai['active_provider']); ?>>
                                    <?php echo esc_html($def['name']); ?> <?php echo !empty($pinfo['configured']) ? esc_html__('(Configured)', 'gmb-ranker-seo-automation') : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Provider configuration drawer. Legacy fields remain available to the settings API,
                         but are not rendered as a tall stack in the primary workflow. -->
                    <div id="gmb-ai-provider-config" class="gmb-ai-provider-modal" hidden role="dialog" aria-modal="true" aria-labelledby="gmb-ai-provider-modal-title">
                        <div class="gmb-ai-provider-modal-backdrop" data-ai-modal-close="1"></div>
                        <div class="gmb-ai-provider-modal-panel">
                            <div class="gmb-ai-provider-modal-header">
                                <div>
                                    <h4 id="gmb-ai-provider-modal-title" class="gmb-heading-4"><?php esc_html_e('Connect AI Providers', 'gmb-ranker-seo-automation'); ?></h4>
                                    <p class="gmb-form-help"><?php esc_html_e('Configure one or more providers. Credentials stay masked and are never exposed to the browser.', 'gmb-ranker-seo-automation'); ?></p>
                                </div>
                                <button type="button" class="gmb-ai-modal-close" data-ai-modal-close="1" aria-label="<?php esc_attr_e('Close provider settings', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                            </div>
                            <div class="gmb-ai-provider-config-list">
                    <?php foreach ($ai['providers'] as $pid => $pinfo) : 
                        $def = $pinfo['definition'];
                        $is_active = ($pid === $ai['active_provider']);
                    ?>
                        <div id="ai-section-<?php echo esc_attr($pid); ?>" class="gmb-ai-section <?php echo $is_active ? 'is-selected' : ''; ?>" data-provider-config="<?php echo esc_attr($pid); ?>">
                            <div class="gmb-ai-config-heading">
                                <strong><?php echo esc_html($def['name']); ?></strong>
                                <span class="gmb-ai-config-state <?php echo !empty($pinfo['configured']) ? 'is-configured' : ''; ?>">
                                    <?php echo !empty($pinfo['configured']) ? esc_html__('Configured', 'gmb-ranker-seo-automation') : esc_html__('Not configured', 'gmb-ranker-seo-automation'); ?>
                                </span>
                            </div>
                            <div class="gmb-grid-2">
                                <?php if (!empty($def['is_local'])) : ?>
                                    <div>
                                        <label for="gmb_ai_<?php echo esc_attr($pid); ?>_url" class="gmb-form-label">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url($def['icon'])); ?>" alt="<?php echo esc_attr($def['name']); ?>" class="gmb-ai-icon-inline" />
                                            <?php printf(esc_html__('%s Base URL', 'gmb-ranker-seo-automation'), esc_html($def['name'])); ?>
                                        </label>
                                        <input type="text" id="gmb_ai_<?php echo esc_attr($pid); ?>_url" name="gmb_ai_<?php echo esc_attr($pid); ?>_url" value="<?php echo esc_attr($pinfo['url']); ?>" placeholder="<?php echo esc_attr($def['url_placeholder']); ?>" class="gmb-integration-input" />
                                        <p class="gmb-form-help"><?php echo esc_html($def['description']); ?></p>
                                    </div>
                                <?php else : ?>
                                    <div>
                                        <label for="gmb_ai_<?php echo esc_attr($pid); ?>_key" class="gmb-form-label">
                                            <span class="gmb-ai-icon-badge"><img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url($def['icon'])); ?>" alt="<?php echo esc_attr($def['name']); ?>" /></span>
                                            <?php printf(esc_html__('%s API Key', 'gmb-ranker-seo-automation'), esc_html($def['name'])); ?>
                                        </label>
                                        <input type="password" id="gmb_ai_<?php echo esc_attr($pid); ?>_key" name="gmb_ai_<?php echo esc_attr($pid); ?>_key" value="" placeholder="<?php echo !empty($pinfo['key']) ? esc_attr__('Key saved; leave blank to keep', 'gmb-ranker-seo-automation') : esc_attr($def['key_placeholder']); ?>" class="gmb-integration-input" autocomplete="off" />
                                        <?php if (!empty($pinfo['key'])) : ?><input type="hidden" name="gmb_ai_<?php echo esc_attr($pid); ?>_key_keep" value="1" /><?php endif; ?>
                                        <p class="gmb-form-help"><?php esc_html_e('Get your API key at', 'gmb-ranker-seo-automation'); ?> <a href="<?php echo esc_url($def['doc_url']); ?>" target="_blank" rel="noopener noreferrer" class="gmb-help-link"><?php echo esc_html(wp_parse_url($def['doc_url'], PHP_URL_HOST)); ?></a>.</p>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <label for="gmb_ai_<?php echo esc_attr($pid); ?>_model" class="gmb-form-label">
                                        <?php printf(esc_html__('%s Model', 'gmb-ranker-seo-automation'), esc_html($def['name'])); ?>
                                    </label>
                                    <input type="text" id="gmb_ai_<?php echo esc_attr($pid); ?>_model" name="gmb_ai_<?php echo esc_attr($pid); ?>_model" value="<?php echo esc_attr($pinfo['model']); ?>" placeholder="<?php echo esc_attr($def['model_placeholder']); ?>" class="gmb-integration-input" />
                                    <?php if (!empty($def['model_presets'])) : ?>
                                        <p class="gmb-form-help"><?php esc_html_e('Presets:', 'gmb-ranker-seo-automation'); ?> <code><?php echo esc_html(implode('</code>, <code>', $def['model_presets'])); ?></code></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                            </div>
                            <div class="gmb-ai-provider-modal-footer">
                                <button type="button" class="gmb-btn gmb-btn-secondary" data-ai-modal-close="1"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                                <button type="button" class="gmb-btn gmb-btn-primary gmb-ai-modal-save"><?php esc_html_e('Save All Providers', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                    </div>
                </div>
                </div>

                <!-- Card 3: IndexNow & Search Engine Instant APIs -->
                <div class="gmb-integration-card">
                    <div class="gmb-integration-card-header">
                        <div class="gmb-integration-card-header-left">
                            <div class="gmb-integration-icon-badge gmb-integration-badge-success">
                                <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/indexnow.svg')); ?>" alt="IndexNow" />
                            </div>
                            <div>
                                <h3 class="gmb-heading-3"><?php esc_html_e('IndexNow & Search Engine Protocols', 'gmb-ranker-seo-automation'); ?></h3>
                                <p class="gmb-text-muted"><?php esc_html_e('Instantly submit newly published or updated content to Bing, Yandex, Seznam, Naver, and IndexNow endpoints.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="gmb-grid-2 gmb-mb-16">
                        <div>
                            <label for="gmb_indexnow_key_input" class="gmb-form-label"><?php esc_html_e('IndexNow API Key', 'gmb-ranker-seo-automation'); ?></label>
                            <div class="gmb-flex-gap-sm">
                                <input type="text" id="gmb_indexnow_key_input" name="gmb_integration_indexnow_key" value="<?php echo esc_attr($indexnow['key']); ?>" readonly class="gmb-integration-input gmb-flex-1" />
                                <input type="hidden" id="gmb_ranker_indexnow_key_hidden" name="gmb_ranker_indexnow_key" value="<?php echo esc_attr($indexnow['key']); ?>" />
                                <button type="button" id="gmb-generate-indexnow-key" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle"><?php esc_html_e('Generate', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                        </div>
                        <div class="gmb-webhook-row">
                            <label for="gmb_integration_indexnow_auto" class="gmb-checkbox-label-inline">
                                <input type="checkbox" id="gmb_integration_indexnow_auto" name="gmb_integration_indexnow_auto" value="1" <?php checked('1', $indexnow['auto']); ?> />
                                <span><?php esc_html_e('Automatically submit URLs to IndexNow upon post/page publish & update', 'gmb-ranker-seo-automation'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Webhooks & External Automation -->
                <div class="gmb-integration-card">
                    <div class="gmb-integration-card-header">
                        <div class="gmb-integration-card-header-left">
                            <div class="gmb-integration-icon-badge gmb-integration-badge-purple">
                                <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/addon/webhooks.svg')); ?>" alt="Webhooks" />
                            </div>
                            <div>
                                <h3 class="gmb-heading-3"><?php esc_html_e('Webhooks & Automation Endpoints (Zapier / Make / Pabbly)', 'gmb-ranker-seo-automation'); ?></h3>
                                <p class="gmb-text-muted"><?php esc_html_e('Receive real-time notifications or trigger external workflows when rankings, 404 logs, or audits update.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="gmb-grid-2 gmb-mb-16">
                        <div>
                            <label for="gmb_webhook_endpoint" class="gmb-form-label"><?php esc_html_e('Inbound Webhook Endpoint URL', 'gmb-ranker-seo-automation'); ?></label>
                            <div class="gmb-flex-gap-sm">
                                <input type="text" id="gmb_webhook_endpoint" readonly value="<?php echo esc_url($webhooks['endpoint']); ?>" class="gmb-integration-input gmb-flex-1 gmb-integration-input-readonly" />
                                <button type="button" id="gmb-copy-webhook-btn" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle"><?php esc_html_e('Copy URL', 'gmb-ranker-seo-automation'); ?></button>
                            </div>
                        </div>
                        <div>
                            <label for="gmb_integration_webhook_secret" class="gmb-form-label"><?php esc_html_e('Webhook Secret Token', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="text" id="gmb_integration_webhook_secret" name="gmb_integration_webhook_secret" value="<?php echo esc_attr($webhooks['secret']); ?>" placeholder="<?php esc_attr_e('Generated on save / rotation...', 'gmb-ranker-seo-automation'); ?>" class="gmb-integration-input" />
                        </div>
                    </div>

                    <div>
                        <label for="gmb_webhook_outbound_url" class="gmb-form-label"><?php esc_html_e('Outbound Webhook Trigger URL (Optional)', 'gmb-ranker-seo-automation'); ?></label>
                        <div class="gmb-flex-gap-sm">
                            <input type="url" id="gmb_webhook_outbound_url" name="gmb_integration_webhook_url" value="<?php echo esc_attr($webhooks['outbound_url']); ?>" placeholder="https://hooks.zapier.com/hooks/catch/..." class="gmb-integration-input gmb-flex-1" />
                            <button type="button" id="gmb-test-webhook-btn" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle"><?php esc_html_e('Test Trigger', 'gmb-ranker-seo-automation'); ?> &nearr;</button>
                        </div>
                        <p class="gmb-form-help"><?php esc_html_e('GMB Ranker will post payload JSON to this URL whenever SEO audit scores change or critical 404 thresholds are exceeded.', 'gmb-ranker-seo-automation'); ?></p>
                    </div>
                </div>

                <!-- Form Footer Actions -->
                <div class="gmb-integrations-footer">
                    <button type="submit" class="gmb-btn gmb-btn-primary gmb-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <?php esc_html_e('Save Integration Settings', 'gmb-ranker-seo-automation'); ?>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
<?php endif; ?>
