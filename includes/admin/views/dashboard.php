<?php
if (!defined('ABSPATH')) exit;

$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'gmb-ranker-automation');
$current_tab  = isset($current_tab) ? $current_tab : (isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '');

$mod_metadata         = isset($mod_metadata) ? $mod_metadata : (get_option('gmb_ranker_module_metadata', '1') !== '0' ? '1' : '0');
$mod_sitemaps         = isset($mod_sitemaps) ? $mod_sitemaps : (get_option('gmb_ranker_module_sitemaps', '1') !== '0' ? '1' : '0');
$mod_redirects        = isset($mod_redirects) ? $mod_redirects : (get_option('gmb_ranker_module_redirects', '1') !== '0' ? '1' : '0');
$mod_schema           = isset($mod_schema) ? $mod_schema : (get_option('gmb_ranker_module_schema', '1') !== '0' ? '1' : '0');
$mod_pref_source      = isset($mod_pref_source) ? $mod_pref_source : (get_option('gmb_ranker_module_preferred_source', '1') !== '0' ? '1' : '0');
$mod_image_seo        = isset($mod_image_seo) ? $mod_image_seo : (get_option('gmb_ranker_module_image_seo', '1') !== '0' ? '1' : '0');
$mod_db_tools         = isset($mod_db_tools) ? $mod_db_tools : (get_option('gmb_ranker_module_db_tools', '1') !== '0' ? '1' : '0');
$mod_role_manager     = isset($mod_role_manager) ? $mod_role_manager : (get_option('gmb_ranker_module_role_manager', '1') !== '0' ? '1' : '0');
$mod_instant_indexing = isset($mod_instant_indexing) ? $mod_instant_indexing : (get_option('gmb_ranker_module_instant_indexing', '1') !== '0' ? '1' : '0');
$mod_links            = isset($mod_links) ? $mod_links : (get_option('gmb_ranker_module_links', '1') !== '0' ? '1' : '0');
$mod_local_seo        = isset($mod_local_seo) ? $mod_local_seo : (get_option('gmb_ranker_module_local_seo', '1') !== '0' ? '1' : '0');
$mod_seo_analysis     = isset($mod_seo_analysis) ? $mod_seo_analysis : (get_option('gmb_ranker_module_seo_analysis', '1') !== '0' ? '1' : '0');
$mod_security         = isset($mod_security) ? $mod_security : (get_option('gmb_ranker_module_security', '1') !== '0' ? '1' : '0');
$mod_llmstxt          = isset($mod_llmstxt) ? $mod_llmstxt : (get_option('gmb_ranker_module_llmstxt', '1') !== '0' ? '1' : '0');
$mod_ai_provider      = isset($mod_ai_provider) ? $mod_ai_provider : (get_option('gmb_ranker_module_ai_provider', '1') !== '0' ? '1' : '0');
$mod_toc              = isset($mod_toc) ? $mod_toc : (get_option('gmb_ranker_module_toc', '1') !== '0' ? '1' : '0');
$mod_media_formats    = isset($mod_media_formats) ? $mod_media_formats : (get_option('gmb_ranker_module_media_formats', '1') !== '0' ? '1' : '0');
$mod_analytics        = isset($mod_analytics) ? $mod_analytics : (get_option('gmb_ranker_module_analytics', '1') !== '0' ? '1' : '0');
$mod_woocommerce      = isset($mod_woocommerce) ? $mod_woocommerce : (get_option('gmb_ranker_module_woocommerce', '1') !== '0' ? '1' : '0');

$analytics_engine = class_exists('GMB_Ranker_SEO_Analytics') ? GMB_Ranker_SEO_Analytics::get_instance() : null;
$analytics_data = $analytics_engine ? $analytics_engine->get_analytics_data() : array();
$totals = isset($analytics_data['totals']) ? $analytics_data['totals'] : array();
$top_queries = isset($analytics_data['top_queries']) ? $analytics_data['top_queries'] : array();
$top_pages = isset($analytics_data['top_pages']) ? $analytics_data['top_pages'] : array();
$is_connected = isset($analytics_data['status']) && $analytics_data['status'] === 'connected';
?>
            <?php if ($current_page === 'gmb-ranker-automation' && $current_tab !== 'wizard') : ?>
            <!-- Tab 1: Modules Grid -->
            <div class="rm-tab-content active" id="rm-tab-modules">
                
                <!-- Category Filter Pills -->
                <div class="gmb-modules-filter-bar">
                    <button type="button" class="gmb-mod-filter-pill active" data-filter="all" onclick="gmbFilterModules('all', this)">All (20)</button>
                    <button type="button" class="gmb-mod-filter-pill" data-filter="meta" onclick="gmbFilterModules('meta', this)">SEO &amp; Metadata</button>
                    <button type="button" class="gmb-mod-filter-pill" data-filter="sitemap" onclick="gmbFilterModules('sitemap', this)">Sitemaps &amp; Indexing</button>
                    <button type="button" class="gmb-mod-filter-pill" data-filter="ai" onclick="gmbFilterModules('ai', this)">AI &amp; Content</button>
                    <button type="button" class="gmb-mod-filter-pill" data-filter="tools" onclick="gmbFilterModules('tools', this)">Tools &amp; Security</button>
                </div>

                <div id="gmb-modules-form">
                    <div class="rm-grid">
                        
                        <!-- 1. Handshake API -->
                        <div class="rm-card" data-category="sitemap">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Handshake API</h3>
                                </div>
                                <p class="rm-card-desc">Authorizes secure end-to-end communication between GMB Ranker dashboard and WordPress.</p>
                            </div>
                            <div class="rm-card-footer">
                                <button type="button" class="rm-settings-btn" id="open-api-settings" onclick="gmbOpenModal('api-settings-overlay')">Settings</button>
                                <label class="rm-switch">
                                    <input type="checkbox" value="1" checked="checked" disabled />
                                    <span class="rm-slider is-disabled"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Metadata Manager -->
                        <div class="rm-card <?php echo ($mod_metadata === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20h9" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Metadata Manager</h3>
                                </div>
                                <p class="rm-card-desc">Natively automates post/page title tag, meta description, and robots metadata tag overrides.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-metadata&tab=metadata')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_metadata" value="1" <?php checked('1', $mod_metadata); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Dynamic Sitemaps -->
                        <div class="rm-card <?php echo ($mod_sitemaps === '0') ? 'is-inactive' : ''; ?>" data-category="sitemap">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="6" y1="3" x2="6" y2="15" stroke-linecap="round"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9" stroke-linecap="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Dynamic Sitemaps</h3>
                                </div>
                                <p class="rm-card-desc">Generates search engine compliant XML feeds dynamically without writing static files.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-sitemaps&tab=general')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_sitemaps" value="1" <?php checked('1', $mod_sitemaps); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 4. Redirections -->
                        <div class="rm-card <?php echo ($mod_redirects === '0') ? 'is-inactive' : ''; ?>" data-category="sitemap">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-red">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="17 1 21 5 17 9" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 11V9a4 4 0 0 1 4-4h14" stroke-linecap="round"/><polyline points="7 23 3 19 7 15" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 13v2a4 4 0 0 1-4 4H3" stroke-linecap="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Redirections</h3>
                                </div>
                                <p class="rm-card-desc">Monitors 404 hit counts and automates custom HTTP 301/302/307 redirections.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-redirects')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_redirects" value="1" <?php checked('1', $mod_redirects); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 5. Schema (Structured Data) -->
                        <div class="rm-card <?php echo ($mod_schema === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-amber">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="16 18 22 12 16 6" stroke-linecap="round" stroke-linejoin="round"/><polyline points="8 6 2 12 8 18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Schema (Structured Data)</h3>
                                </div>
                                <p class="rm-card-desc">Injects Schema entities (LocalBusiness, Article, FAQ) dynamically to display rich SERP results.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-schema')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_schema" value="1" <?php checked('1', $mod_schema); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 6. Preferred Source Widget -->
                        <div class="rm-card <?php echo ($mod_pref_source === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-cyan">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Preferred Source Widget</h3>
                                </div>
                                <p class="rm-card-desc">Appends E-E-A-T prefer source widget shortcuts to posts allowing readers to subscribe in one click.</p>
                            </div>
                            <div class="rm-card-footer">
                                <button type="button" class="rm-settings-btn" id="open-preferred-source-settings" onclick="gmbOpenModal('preferred-source-settings-overlay')">Settings</button>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_preferred_source" value="1" <?php checked('1', $mod_pref_source); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 7. Image SEO -->
                        <div class="rm-card <?php echo ($mod_image_seo === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Image SEO</h3>
                                </div>
                                <p class="rm-card-desc">Automatically adds alt and title attributes to images dynamically based on title templates.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=image')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_image_seo" value="1" <?php checked('1', $mod_image_seo); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 8. Links Manager -->
                        <div class="rm-card <?php echo ($mod_links === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Links Manager</h3>
                                </div>
                                <p class="rm-card-desc">Appends target="_blank" and rel="nofollow" attributes to external links dynamically.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=links')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_links" value="1" <?php checked('1', $mod_links); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 9. Database Tools -->
                        <div class="rm-card <?php echo ($mod_db_tools === '0') ? 'is-inactive' : ''; ?>" data-category="tools">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Database Tools</h3>
                                </div>
                                <p class="rm-card-desc">Execute database diagnostics, purge transients, and flush redirects hit log entries.</p>
                            </div>
                            <div class="rm-card-footer">
                                <button type="button" class="rm-settings-btn" id="db-tools-trigger-btn" onclick="gmbOpenModal('db-tools-settings-overlay')">Manage DB</button>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_db_tools" value="1" <?php checked('1', $mod_db_tools); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 10. Role Manager -->
                        <div class="rm-card <?php echo ($mod_role_manager === '0') ? 'is-inactive' : ''; ?>" data-category="tools">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-red">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Role Manager</h3>
                                </div>
                                <p class="rm-card-desc">Manage SEO permissions and capability access levels for WP User Roles (Author, Editor).</p>
                            </div>
                            <div class="rm-card-footer">
                                <button type="button" class="rm-settings-btn" id="role-manager-trigger-btn" onclick="gmbOpenModal('role-manager-settings-overlay')">Settings</button>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_role_manager" value="1" <?php checked('1', $mod_role_manager); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 11. Instant Indexing -->
                        <div class="rm-card <?php echo ($mod_instant_indexing === '0') ? 'is-inactive' : ''; ?>" data-category="sitemap">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-amber">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Instant Indexing</h3>
                                </div>
                                <p class="rm-card-desc">Submit published content URLs instantly to IndexNow API for accelerated crawls.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-instant-indexing')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_instant_indexing" value="1" <?php checked('1', $mod_instant_indexing); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 12. Local SEO -->
                        <div class="rm-card <?php echo ($mod_local_seo === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-indigo">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">Local SEO</h3>
                                </div>
                                <p class="rm-card-desc">Inject LocalBusiness address, phone, and hours metadata structured JSON-LD into home screen.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-metadata&tab=local')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_local_seo" value="1" <?php checked('1', $mod_local_seo); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 13. SEO Analysis -->
                        <div class="rm-card <?php echo ($mod_seo_analysis === '0') ? 'is-inactive' : ''; ?>" data-category="ai">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                    </div>
                                    <h3 class="rm-card-title">SEO Analysis</h3>
                                </div>
                                <p class="rm-card-desc">Audit readability, character length boundaries, and optimization density scores.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-metadata&tab=homepage')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_seo_analysis" value="1" <?php checked('1', $mod_seo_analysis); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 14. Security Controls -->
                        <div class="rm-card <?php echo ($mod_security === '0') ? 'is-inactive' : ''; ?>" data-category="tools">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-red">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    </div>
                                    <h3 class="rm-card-title">Security Controls</h3>
                                </div>
                                <p class="rm-card-desc">Protect your website by disabling XML-RPC, hiding core versions, restricting guest REST APIs, and injecting secure headers.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=security')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_security" value="1" <?php checked('1', $mod_security); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 15. LLMs Txt -->
                        <div class="rm-card <?php echo ($mod_llmstxt === '0') ? 'is-inactive' : ''; ?>" data-category="sitemap">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                    <h3 class="rm-card-title">LLMs Txt</h3>
                                </div>
                                <p class="rm-card-desc">Serves clean markdown /llms.txt and /llms-full.txt feeds to optimize search engine content indexing for LLMs.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=llmstxt')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_llmstxt" value="1" <?php checked('1', $mod_llmstxt); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 16. Content AI -->
                        <div class="rm-card <?php echo ($mod_ai_provider === '0') ? 'is-inactive' : ''; ?>" data-category="ai">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    </div>
                                    <h3 class="rm-card-title">Content AI</h3>
                                </div>
                                <p class="rm-card-desc">Connects to cloud or local AI providers to generate real-time content recommendations and audits.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=ai')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_ai_provider" value="1" <?php checked('1', $mod_ai_provider); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 17. Table of Contents -->
                        <div class="rm-card <?php echo ($mod_toc === '0') ? 'is-inactive' : ''; ?>" data-category="ai">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-orange">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                    </div>
                                    <h3 class="rm-card-title">Table of Contents</h3>
                                </div>
                                <p class="rm-card-desc">Automatically parses post headings, assigns anchor links, and prepends a beautifully styled jump-link index.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings&tab=toc')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_toc" value="1" <?php checked('1', $mod_toc); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 18. Media Formats & SVG Support -->
                        <div class="rm-card <?php echo ($mod_media_formats === '0') ? 'is-inactive' : ''; ?>" data-category="tools">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    </div>
                                    <h3 class="rm-card-title">Media Formats &amp; SVG</h3>
                                </div>
                                <p class="rm-card-desc">Enables safe SVG uploads with XML sanitization, WebP/AVIF images, and SEO structured data formats (JSON, CSV).</p>
                            </div>
                            <div class="rm-card-footer">
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_media_formats" value="1" <?php checked('1', $mod_media_formats); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 19. Search Console & Analytics -->
                        <div class="rm-card <?php echo ($mod_analytics === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                    </div>
                                    <h3 class="rm-card-title">Search Console &amp; Analytics</h3>
                                </div>
                                <p class="rm-card-desc">Syncs organic impressions, click-through rates, and Google ranking positions directly via GMB Ranker Cloud.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-analytics')); ?>" class="rm-settings-btn">View Analytics</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_analytics" value="1" <?php checked('1', $mod_analytics); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- 20. WooCommerce E-Commerce SEO -->
                        <div class="rm-card <?php echo ($mod_woocommerce === '0') ? 'is-inactive' : ''; ?>" data-category="meta">
                            <div class="rm-card-body">
                                <div class="rm-card-header">
                                    <div class="rm-card-icon bg-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                    </div>
                                    <h3 class="rm-card-title">WooCommerce SEO</h3>
                                </div>
                                <p class="rm-card-desc">Automates Product Schema, offers, stock status, and enriches XML sitemaps with WooCommerce gallery images.</p>
                            </div>
                            <div class="rm-card-footer">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-schema&tab=general')); ?>" class="rm-settings-btn">Settings</a>
                                <label class="rm-switch">
                                    <input type="checkbox" name="gmb_ranker_module_woocommerce" value="1" <?php checked('1', $mod_woocommerce); ?> />
                                    <span class="rm-slider"></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tab 2: Global & Local SEO Settings Panel -->
