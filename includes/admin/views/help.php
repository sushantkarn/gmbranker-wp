<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-help') : ?>
            <div class="rm-tab-content active" id="rm-tab-help">
                <div class="gmb-help-container">
                    <div class="gmb-settings-panel-header gmb-text-left">
                        <h2 class="gmb-heading-2">Help &amp; Support Resources</h2>
                        <p class="gmb-form-help">Access support channels, reading materials, step-by-step guides, or launch the interactive setup wizard. <a href="https://gmbranker.org" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                    </div>
                    
                    <div class="gmb-help-grid">
                        <!-- Card 1: Direct Support -->
                        <div class="gmb-help-card">
                            <div>
                                <div class="gmb-help-card-header">
                                    <div class="gmb-help-icon-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="gmb-help-card-title">Direct Priority Support</h4>
                                        <span class="gmb-status-pill gmb-status-pill--success gmb-status-pill-sub">
                                            <span class="gmb-status-dot"></span>
                                            Active for Licensed Users
                                        </span>
                                    </div>
                                </div>
                                <p class="gmb-help-card-body">Submit inquiries directly to our certified WordPress SEO engineers and developer team for fast technical troubleshooting.</p>
                            </div>
                            <div class="gmb-help-card-footer">
                                <a href="https://gmbranker.org" target="_blank" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-help-link">
                                    Open Support Ticket &nearr;
                                </a>
                            </div>
                        </div>

                        <!-- Card 2: Documentation -->
                        <div class="gmb-help-card">
                            <div>
                                <div class="gmb-help-card-header">
                                    <div class="gmb-help-icon-badge gmb-help-icon-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="gmb-help-card-title">Documentation &amp; Tutorials</h4>
                                        <span class="gmb-text-muted gmb-text-xs">Guides &amp; Step-by-Step Walkthroughs</span>
                                    </div>
                                </div>
                                <p class="gmb-help-card-body">Learn how to configure Local Business Schema, Instant IndexNow protocols, automated AI focus keywords, and XML sitemaps.</p>
                            </div>
                            <div class="gmb-help-card-footer">
                                <a href="https://gmbranker.org" target="_blank" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                                    Browse Knowledge Base &nearr;
                                </a>
                            </div>
                        </div>

                        <!-- Card 3: Interactive Setup Wizard -->
                        <div class="gmb-help-card">
                            <div>
                                <div class="gmb-help-card-header">
                                    <div class="gmb-help-icon-badge gmb-help-icon-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="gmb-help-card-title">Configuration Wizard</h4>
                                        <span class="gmb-text-muted gmb-text-xs">Re-run Onboarding &amp; Setup</span>
                                    </div>
                                </div>
                                <p class="gmb-help-card-body">Need to change your website business type, indexing preferences, or social profiles? Rerun the step-by-step wizard at any time.</p>
                            </div>
                            <div class="gmb-help-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-wizard')); ?>" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                                    Start Setup Wizard &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Card 4: Community & Feedback -->
                        <div class="gmb-help-card">
                            <div>
                                <div class="gmb-help-card-header">
                                    <div class="gmb-help-icon-badge gmb-help-icon-orange">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="gmb-help-card-title">Community &amp; Feature Requests</h4>
                                        <span class="gmb-text-muted gmb-text-xs">Roadmap &amp; Feedback</span>
                                    </div>
                                </div>
                                <p class="gmb-help-card-body">Join other local SEO professionals and webmasters. Submit feature ideas or vote on upcoming modules in our public roadmap.</p>
                            </div>
                            <div class="gmb-help-card-footer">
                                <a href="https://gmbranker.org/community" target="_blank" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                                    Join Community &nearr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Reference Section (Toggle Based) -->
                    <div class="gmb-faq-section">
                        <div class="gmb-faq-header-row">
                            <h3 class="gmb-faq-header-title">Frequently Asked Questions</h3>
                            <span class="gmb-text-muted gmb-faq-header-tip">Click questions to expand</span>
                        </div>
                        <div class="gmb-faq-list">
                            <details class="gmb-faq-accordion" open>
                                <summary class="gmb-faq-summary">
                                    <span>How does Instant Indexing differ from regular XML Sitemaps?</span>
                                    <svg class="gmb-faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="gmb-faq-content">
                                    XML Sitemaps wait passively for search engine crawlers to periodically visit and discover your sitemap index. Instant Indexing actively pushes newly published or modified URLs directly to Google, Bing, and IndexNow endpoints the moment you publish or update content.
                                </div>
                            </details>

                            <details class="gmb-faq-accordion">
                                <summary class="gmb-faq-summary">
                                    <span>Can I import settings without losing existing SEO rankings?</span>
                                    <svg class="gmb-faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="gmb-faq-content">
                                    Yes! GMB Ranker's 1-click importer safely reads your existing Rank Math or Yoast postmeta records and creates corresponding GMB Ranker entries without altering canonicals, permalinks, or existing structured data schemas.
                                </div>
                            </details>

                            <details class="gmb-faq-accordion">
                                <summary class="gmb-faq-summary">
                                    <span>Which AI providers are free to use for meta &amp; keyword generation?</span>
                                    <svg class="gmb-faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="gmb-faq-content">
                                    OpenRouter offers completely free tiers with high-quality models (such as Meta Llama 3.1 8B Instruct and Google Gemma 2), Groq provides an ultra-fast free API tier, and Ollama lets you run open-source models completely free on your local machine with 100% privacy.
                                </div>
                            </details>

                            <details class="gmb-faq-accordion">
                                <summary class="gmb-faq-summary">
                                    <span>How do Schema Display Conditions and Property Groups work?</span>
                                    <svg class="gmb-faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="gmb-faq-content">
                                    Display Conditions allow you to conditionally inject schemas across your site. Rules within a group are evaluated using AND logic, while separate Property Groups use OR logic—giving you full control over exactly where and when schema types appear.
                                </div>
                            </details>
                        </div>
                    </div>

                </div>
            </div>
            <?php endif; ?>

            <!-- Tab 5: Setup Wizard -->
