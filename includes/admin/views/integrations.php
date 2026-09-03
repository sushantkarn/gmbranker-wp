<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-integrations') : 
                $api_key = get_option('gmb_ranker_api_key', '');
                $cloud_sync = get_option('gmb_ranker_cloud_sync', '1');
                $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
                $workspace_name = get_option('gmb_workspace_name', get_bloginfo('name') . ' Workspace');
                $workspace_email = get_option('gmb_workspace_email', get_option('admin_email'));
                $workspace_gsc = get_option('gmb_workspace_gsc_property', site_url() . '/');
                $workspace_ga4 = get_option('gmb_workspace_ga4_stream', !empty($site_host) ? 'properties/' . $site_host : 'Auto-detected via Cloud');
                $workspace_gmb = get_option('gmb_workspace_gmb_location', get_bloginfo('name'));
                
                $ai_provider = get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', 'openrouter'));
                $openrouter_key = get_option('gmb_ai_openrouter_key', '');
                $openrouter_model = get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free');
                $groq_key = get_option('gmb_ai_groq_key', '');
                $groq_model = get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant');
                $ollama_url = get_option('gmb_ai_ollama_url', 'http://localhost:11434');
                $ollama_model = get_option('gmb_ai_ollama_model', 'llama3');

                $indexnow_key = get_option('gmb_integration_indexnow_key', '');
                $indexnow_auto = get_option('gmb_integration_indexnow_auto', '1');
                $webhook_url = get_option('gmb_integration_webhook_url', '');
                $webhook_secret = get_option('gmb_integration_webhook_secret', '');
                if (empty($webhook_secret)) {
                    $webhook_secret = wp_generate_password(24, false);
                    update_option('gmb_integration_webhook_secret', $webhook_secret);
                }
            ?>
            <div class="rm-tab-content active" id="rm-tab-integrations">
                <div class="gmb-integrations-container">
                    <div class="gmb-settings-panel-header gmb-text-left">
                        <h2 class="gmb-heading-2">Integrations &amp; API Hub</h2>
                        <p class="gmb-form-help">Connect your website with GMB Ranker Cloud Platform, Google Search Console, Google Analytics 4, IndexNow, free AI engines (OpenRouter, Groq, Ollama), and automation webhooks. <a href="https://gmbranker.org" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                    </div>

                    <form method="post" action="options.php" novalidate>
                        <?php settings_fields('gmb_ranker_integrations_group'); ?>
                    
                    <div class="gmb-integrations-stack">

                        <!-- Card 1: GMB Ranker Cloud Platform & Google Ecosystem Sync -->
                        <div class="gmb-integration-card">
                            <div class="gmb-integration-card-header">
                                <div class="gmb-integration-card-header-left">
                                    <div class="gmb-integration-icon-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="gmb-heading-3">GMB Ranker Cloud &amp; Google Workspace Connections</h3>
                                        <p class="gmb-text-muted">Your GMB Ranker API key automatically links and synchronizes Search Console, Google Analytics 4, and Google Business Profile from your Cloud Workspace.</p>
                                    </div>
                                </div>
                                <div>
                                    <?php if (!empty($api_key)) : ?>
                                        <span class="gmb-status-pill gmb-status-pill--success">
                                            <span class="gmb-status-dot"></span>
                                            Connected &amp; Synced
                                        </span>
                                    <?php else : ?>
                                        <span class="gmb-status-pill gmb-status-pill--danger">
                                            <span class="gmb-status-dot"></span>
                                            Disconnected (Key Required)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="gmb-integration-grid-2col">
                                <div>
                                    <label class="gmb-form-label">GMB Ranker API Secret Key</label>
                                    <div class="gmb-flex-gap-sm">
                                        <input type="password" id="gmb_ranker_api_key_input" name="gmb_ranker_api_key" value="<?php echo esc_attr($api_key); ?>" placeholder="Paste your gr_live_... secret key" class="gmb-integration-input gmb-flex-1" />
                                        <button type="button" id="gmb-toggle-key-visibility" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle">Show</button>
                                    </div>
                                    <p class="gmb-form-help">Get your developer API key from <a href="https://gmbranker.org" target="_blank" class="gmb-help-link">GMB Ranker Dashboard &rarr; API Keys</a>.</p>
                                </div>
                                <div>
                                    <label class="gmb-form-label">Cloud Sync Frequency</label>
                                    <select name="gmb_ranker_cloud_sync" class="gmb-integration-select">
                                        <option value="1" <?php selected('1', $cloud_sync); ?>>Real-time Auto Sync (Recommended)</option>
                                        <option value="daily" <?php selected('daily', $cloud_sync); ?>>Daily Background Sync</option>
                                        <option value="manual" <?php selected('manual', $cloud_sync); ?>>Manual Sync Only</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Synchronized Google Ecosystem Connections Panel -->
                            <div class="gmb-synced-connections-wrap">
                                <div class="gmb-synced-connections-header">
                                    <strong class="gmb-synced-title-label">Synced Cloud Workspace Connections (Google Accounts)</strong>
                                    <a href="https://gmbranker.org" target="_blank" class="gmb-synced-link">Manage Connections in GMB Ranker Cloud &nearr;</a>
                                </div>
                                <div class="gmb-synced-connections-grid">
                                    <!-- Google Search Console -->
                                    <div class="gmb-synced-connection-card">
                                        <div class="gmb-synced-connection-title">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-search-console.svg')); ?>" alt="Google Search Console" />
                                            <span>Google Search Console</span>
                                        </div>
                                        <?php if (!empty($api_key)) : ?>
                                            <div class="gmb-synced-status-connected gmb-text-xs">&check; Connected via GMB Ranker</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Property: <strong><?php echo esc_html($workspace_gsc); ?></strong></div>
                                        <?php else : ?>
                                            <div class="gmb-synced-status-pending gmb-text-xs">Pending API Key Link</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Property: <?php echo esc_html($workspace_gsc); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Google Analytics 4 -->
                                    <div class="gmb-synced-connection-card">
                                        <div class="gmb-synced-connection-title">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-analytics.svg')); ?>" alt="Google Analytics 4" />
                                            <span>Google Analytics 4</span>
                                        </div>
                                        <?php if (!empty($api_key)) : ?>
                                            <div class="gmb-synced-status-connected gmb-text-xs">&check; Connected via GMB Ranker</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Stream: <strong><?php echo esc_html($workspace_ga4); ?></strong></div>
                                        <?php else : ?>
                                            <div class="gmb-synced-status-pending gmb-text-xs">Pending API Key Link</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Stream: <?php echo esc_html($workspace_ga4); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Google Business Profile -->
                                    <div class="gmb-synced-connection-card">
                                        <div class="gmb-synced-connection-title">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/connect/google-my-business.svg')); ?>" alt="Google Business Profile" />
                                            <span>Google Business Profile</span>
                                        </div>
                                        <?php if (!empty($api_key)) : ?>
                                            <div class="gmb-synced-status-connected gmb-text-xs">&check; Synced &amp; Reviews Active</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Location: <strong><?php echo esc_html($workspace_gmb); ?></strong></div>
                                        <?php else : ?>
                                            <div class="gmb-synced-status-pending gmb-text-xs">Pending API Key Link</div>
                                            <div class="gmb-text-muted gmb-text-xs gmb-synced-meta-text">Location: <?php echo esc_html($workspace_gmb); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: AI Provider & Free Model Engine (OpenRouter, Groq, Ollama) -->
                        <div class="gmb-integration-card">
                            <div class="gmb-integration-card-header">
                                <div class="gmb-integration-card-header-left">
                                    <div class="gmb-ai-icons-row">
                                        <span class="gmb-ai-icon-box"><img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/openrouter.svg')); ?>" alt="OpenRouter" /></span>
                                        <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/groq.svg')); ?>" alt="Groq" class="gmb-ai-icon-sm" />
                                        <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/ollama-icon.svg')); ?>" alt="Ollama" class="gmb-ai-icon-sm" />
                                    </div>
                                    <div>
                                        <h3 class="gmb-heading-3">AI Provider &amp; Model Engine (Free &amp; Open-Source)</h3>
                                        <p class="gmb-text-muted">Powers automated SEO meta generation, focus keyword suggestions, and local content optimization using free/fast AI providers.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-mb-16">
                                <label class="gmb-form-label">Active AI Provider</label>
                                <select id="gmb_ai_provider_select" name="gmb_ai_provider" class="gmb-integration-select gmb-integration-select-ai">
                                    <option value="openrouter" <?php selected('openrouter', $ai_provider); ?>>OpenRouter (Recommended — Free Llama 3.1 &amp; Gemma models)</option>
                                    <option value="groq" <?php selected('groq', $ai_provider); ?>>Groq Cloud (Ultra Fast Llama 3.1 Free Tier)</option>
                                    <option value="ollama" <?php selected('ollama', $ai_provider); ?>>Ollama (Local AI / 100% Free &amp; Offline)</option>
                                </select>
                            </div>

                            <!-- OpenRouter Section -->
                            <div id="ai-section-openrouter" class="gmb-ai-section <?php echo ($ai_provider === 'openrouter') ? 'is-active' : ''; ?>">
                                <div class="gmb-grid-2">
                                    <div>
                                        <label class="gmb-form-label">
                                            <span class="gmb-ai-icon-badge"><img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/openrouter.svg')); ?>" alt="OpenRouter" /></span>
                                            OpenRouter API Key
                                        </label>
                                        <input type="password" name="gmb_ai_openrouter_key" value="<?php echo esc_attr($openrouter_key); ?>" placeholder="sk-or-v1-..." class="gmb-integration-input" />
                                        <p class="gmb-form-help">Get your free API key at <a href="https://openrouter.ai/keys" target="_blank" class="gmb-help-link">openrouter.ai/keys</a>.</p>
                                    </div>
                                    <div>
                                        <label class="gmb-form-label">OpenRouter Model</label>
                                        <input type="text" name="gmb_ai_openrouter_model" value="<?php echo esc_attr($openrouter_model); ?>" placeholder="meta-llama/llama-3.1-8b-instruct:free" class="gmb-integration-input" />
                                        <p class="gmb-form-help">Presets: <code>meta-llama/llama-3.1-8b-instruct:free</code>, <code>mistralai/mistral-7b-instruct:free</code>, <code>google/gemma-2-9b-it:free</code></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Groq Section -->
                            <div id="ai-section-groq" class="gmb-ai-section <?php echo ($ai_provider === 'groq') ? 'is-active' : ''; ?>">
                                <div class="gmb-grid-2">
                                    <div>
                                        <label class="gmb-form-label">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/groq.svg')); ?>" alt="Groq" class="gmb-ai-icon-inline" />
                                            Groq API Key
                                        </label>
                                        <input type="password" name="gmb_ai_groq_key" value="<?php echo esc_attr($groq_key); ?>" placeholder="gsk_..." class="gmb-integration-input" />
                                        <p class="gmb-form-help">Get your ultra-fast free key at <a href="https://console.groq.com/keys" target="_blank" class="gmb-help-link">console.groq.com/keys</a>.</p>
                                    </div>
                                    <div>
                                        <label class="gmb-form-label">Groq Model</label>
                                        <input type="text" name="gmb_ai_groq_model" value="<?php echo esc_attr($groq_model); ?>" placeholder="llama-3.1-8b-instant" class="gmb-integration-input" />
                                        <p class="gmb-form-help">Presets: <code>llama-3.1-8b-instant</code>, <code>llama-3.3-70b-versatile</code>, <code>mixtral-8x7b-32768</code></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Ollama Section -->
                            <div id="ai-section-ollama" class="gmb-ai-section <?php echo ($ai_provider === 'ollama') ? 'is-active' : ''; ?>">
                                <div class="gmb-grid-2">
                                    <div>
                                        <label class="gmb-form-label">
                                            <img src="<?php echo esc_url(GMB_Ranker_SEO_Helpers::asset_url('images/ai/ollama-icon.svg')); ?>" alt="Ollama" class="gmb-ai-icon-inline" />
                                            Ollama API Base URL
                                        </label>
                                        <input type="text" name="gmb_ai_ollama_url" value="<?php echo esc_attr($ollama_url); ?>" placeholder="http://localhost:11434" class="gmb-integration-input" />
                                        <p class="gmb-form-help">Local host server endpoint running Ollama.</p>
                                    </div>
                                    <div>
                                        <label class="gmb-form-label">Ollama Model</label>
                                        <input type="text" name="gmb_ai_ollama_model" value="<?php echo esc_attr($ollama_model); ?>" placeholder="llama3" class="gmb-integration-input" />
                                        <p class="gmb-form-help">Locally pulled model tag e.g. <code>llama3</code>, <code>mistral</code>, <code>gemma2</code>.</p>
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
                                        <h3 class="gmb-heading-3">IndexNow &amp; Search Engine Protocols</h3>
                                        <p class="gmb-text-muted">Instantly submit newly published or updated content to Bing, Yandex, Seznam, and IndexNow endpoints.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-grid-2 gmb-mb-16">
                                <div>
                                    <label class="gmb-form-label">IndexNow API Key</label>
                                    <div class="gmb-flex-gap-sm">
                                        <input type="text" id="gmb_indexnow_key_input" name="gmb_integration_indexnow_key" value="<?php echo esc_attr($indexnow_key); ?>" placeholder="32-character hexadecimal key" class="gmb-integration-input gmb-flex-1" />
                                        <button type="button" id="gmb-generate-indexnow-key" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle">Generate</button>
                                    </div>
                                </div>
                                <div class="gmb-webhook-row">
                                    <label class="gmb-checkbox-label-inline">
                                        <input type="checkbox" name="gmb_integration_indexnow_auto" value="1" <?php checked('1', $indexnow_auto); ?> />
                                        <span>Automatically submit URLs to IndexNow upon post/page publish &amp; update</span>
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
                                        <h3 class="gmb-heading-3">Webhooks &amp; Automation Endpoints (Zapier / Make / Pabbly)</h3>
                                        <p class="gmb-text-muted">Receive real-time notifications or trigger external workflows when rankings, 404 logs, or audits update.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-grid-2 gmb-mb-16">
                                <div>
                                    <label class="gmb-form-label">Inbound Webhook Endpoint URL</label>
                                    <div class="gmb-flex-gap-sm">
                                        <input type="text" id="gmb_webhook_endpoint" readonly value="<?php echo esc_url(rest_url('gmb-ranker/v1/webhook')); ?>" class="gmb-integration-input gmb-flex-1 gmb-integration-input-readonly" />
                                        <button type="button" id="gmb-copy-webhook-btn" class="gmb-btn gmb-btn-secondary gmb-btn-key-toggle">Copy URL</button>
                                    </div>
                                </div>
                                <div>
                                    <label class="gmb-form-label">Webhook Secret Token</label>
                                    <input type="text" name="gmb_integration_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="gmb-integration-input" />
                                </div>
                            </div>

                            <div>
                                <label class="gmb-form-label">Outbound Webhook Trigger URL (Optional)</label>
                                <input type="url" name="gmb_integration_webhook_url" value="<?php echo esc_attr($webhook_url); ?>" placeholder="https://hooks.zapier.com/hooks/catch/..." class="gmb-integration-input" />
                                <p class="gmb-form-help">GMB Ranker will post payload JSON to this URL whenever SEO audit scores change or critical 404 thresholds are exceeded.</p>
                            </div>
                        </div>

                        <!-- Form Footer Actions -->
                        <div class="gmb-integrations-footer">
                            <button type="submit" class="gmb-btn gmb-btn-primary gmb-btn--primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Save Integration Settings
                            </button>
                        </div>

                    </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Subtab: Import & Export / Status & Tools Hub -->
