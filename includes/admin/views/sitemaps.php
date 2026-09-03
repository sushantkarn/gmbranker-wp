<?php
/**
 * XML & HTML Sitemaps Administration View
 *
 * Thin presentation layer consuming canonical GMB_Ranker_SEO_Sitemap_Registry view model.
 * Direct persistence, database reads, and independent calculations are removed.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_tab_name = isset($current_tab) ? $current_tab : (isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '');
$current_page_name = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '');

if ($current_page_name === 'gmb-ranker-sitemaps' || $current_tab_name === 'gmb-ranker-sitemaps') :

    $req_sub = isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : '');
    $view_model = GMB_Ranker_SEO_Sitemap_Registry::get_view_model($req_sub);

    $is_module_enabled = $view_model['module_enabled'];
    $active_subtab     = $view_model['active_subtab'];
    $s                 = $view_model['settings'];
    $index_url         = $view_model['sitemap_index_url'];
    $post_types        = $view_model['post_types'];
    $taxonomies        = $view_model['taxonomies'];
    $author_count      = $view_model['author_count'];
    $custom_count      = $view_model['custom_url_count'];
    $diagnostics       = $view_model['diagnostics'];

    if (!$is_module_enabled) : 
    ?>
        <div class="rm-tab-content active" role="region" aria-label="<?php esc_attr_e('Disabled Sitemaps Module Warning', 'gmb-ranker-seo-automation'); ?>">
            <div class="gmb-empty-state">
                <h2 class="gmb-heading-2"><?php esc_html_e('Dynamic Sitemaps Module is Disabled', 'gmb-ranker-seo-automation'); ?></h2>
                <p class="gmb-text-muted"><?php esc_html_e('Enable the Dynamic Sitemaps module to configure XML Sitemaps, post types, taxonomies, and sitemap indexes.', 'gmb-ranker-seo-automation'); ?></p>
                <div class="gmb-flex-center-gap-md">
                    <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_sitemaps"><?php esc_html_e('Enable Module', 'gmb-ranker-seo-automation'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-btn gmb-btn-secondary"><?php esc_html_e('Go to Dashboard', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="rm-tab-content active" id="rm-tab-sitemaps" role="region" aria-label="<?php esc_attr_e('Sitemap Management', 'gmb-ranker-seo-automation'); ?>">
            <form method="post" action="options.php" novalidate>
                <?php settings_fields('gmb_ranker_sitemaps_group'); ?>
                
                <div class="gmb-sidebar-layout-container">
                    
                    <!-- Sidebar Navigation Column -->
                    <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_subtab); ?>" />
                    <div class="gmb-sidebar-nav">
                        <ul role="tablist">
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'general') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-general" role="tab" aria-selected="<?php echo ($active_subtab === 'general') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                <?php esc_html_e('General', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'post-types') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-post-types" role="tab" aria-selected="<?php echo ($active_subtab === 'post-types') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <?php esc_html_e('Post Types', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'taxonomies') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-taxonomies" role="tab" aria-selected="<?php echo ($active_subtab === 'taxonomies') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                <?php esc_html_e('Taxonomies', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'authors') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-authors" role="tab" aria-selected="<?php echo ($active_subtab === 'authors') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?php esc_html_e('Authors', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'html') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-html" role="tab" aria-selected="<?php echo ($active_subtab === 'html') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <?php esc_html_e('HTML Sitemap', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'index') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-index" role="tab" aria-selected="<?php echo ($active_subtab === 'index') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                <?php esc_html_e('Sitemap Index & URLs', 'gmb-ranker-seo-automation'); ?>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Content Settings Column -->
                    <div class="gmb-sidebar-content-panel">
                        
                        <!-- Subtab 1: General Sitemap Settings -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'general') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-general" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('General Sitemap Settings', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Configure dynamic XML sitemaps to optimize crawling and indexing by search engines.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <!-- Main Index Banner -->
                            <div class="gmb-callout gmb-callout--info gmb-sitemap-banner">
                                <div class="gmb-sitemap-banner-left">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#466afa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon-img" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    <div>
                                        <span class="gmb-sitemap-banner-title">
                                            <?php esc_html_e('Your main XML sitemap index is available at:', 'gmb-ranker-seo-automation'); ?>
                                        </span>
                                        <div>
                                            <a href="<?php echo esc_url($index_url); ?>" target="_blank" class="gmb-help-link font-semibold gmb-sitemap-link">
                                                <?php echo esc_url($index_url); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="gmb-callout-actions">
                                    <a href="<?php echo esc_url($index_url); ?>" target="_blank" class="button gmb-callout-btn gmb-sitemap-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        <?php esc_html_e('Open Sitemap', 'gmb-ranker-seo-automation'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="gmb-card-settings-list">
                                <!-- Option: Enable/Disable Sitemaps -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('XML Sitemaps', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_ranker_module_sitemaps" value="0" />
                                            <input type="checkbox" name="gmb_ranker_module_sitemaps" value="1" <?php checked(true, $s['module_enabled']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Natively generate structured XML sitemap indexes for web crawlers.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Links Per Sitemap -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Links Per Sitemap', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="number" name="gmb_sitemap_items_per_page" min="10" max="50000" step="10" value="<?php echo esc_attr($s['items_per_page']); ?>" class="gmb-input-num-sm" />
                                        <p class="gmb-form-help"><?php esc_html_e('Maximum number of URLs per sitemap file (Default: 1,000).', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Images in Sitemaps -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Images in Sitemaps', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_sitemap_include_images" value="0" />
                                            <input type="checkbox" name="gmb_sitemap_include_images" value="1" <?php checked(true, $s['include_images']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Include image tags in sitemap entries to assist media discovery.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Include Featured Images -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Include Featured Images', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_sitemap_include_featured_images" value="0" />
                                            <input type="checkbox" name="gmb_sitemap_include_featured_images" value="1" <?php checked(true, $s['include_featured_images']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Automatically attach featured post images to XML sitemap tags.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Ping Search Engines -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Ping Search Engines', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_sitemap_ping_search_engines" value="0" />
                                            <input type="checkbox" name="gmb_sitemap_ping_search_engines" value="1" <?php checked(true, $s['ping_search_engines']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Trigger ping webhooks to search engines whenever content or sitemaps are updated.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Exclude Posts -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Exclude Posts by ID', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_sitemap_excluded_posts" placeholder="e.g. 12, 45, 108" value="<?php echo esc_attr($s['excluded_posts']); ?>" class="gmb-input-max-480" />
                                        <p class="gmb-form-help"><?php esc_html_e('Comma-separated list of Post or Page IDs to exclude from the sitemap.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Exclude Terms -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Exclude Terms by ID', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_sitemap_excluded_terms" placeholder="e.g. 3, 8" value="<?php echo esc_attr($s['excluded_terms']); ?>" class="gmb-input-max-480" />
                                        <p class="gmb-form-help"><?php esc_html_e('Comma-separated list of Category or Tag Term IDs to exclude from taxonomy sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Exclude Slugs -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Exclude Specific Slugs', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_ranker_sitemap_exclude_slugs" placeholder="e.g. contact-us, privacy-policy" class="gmb-textarea-max-480"><?php echo esc_textarea($s['exclude_slugs']); ?></textarea>
                                        <p class="gmb-form-help"><?php esc_html_e('Comma-separated slugs or path segments to exclude from generated sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Option: Custom URLs -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Include Custom Extra URLs', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_sitemap_custom_urls" placeholder="https://example.com/custom-landing-page/&#10;https://example.com/special-tool/" class="gmb-textarea-max-480-lg"><?php echo esc_textarea($s['custom_urls']); ?></textarea>
                                        <p class="gmb-form-help"><?php esc_html_e('One full absolute URL per line to inject into the main sitemap index.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- Subtab 2: Post Types Sitemaps -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'post-types') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-post-types" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Post Type Sitemaps', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Choose which post types should be dynamically indexed in your XML sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-sitemap-cards-list">
                                <?php foreach ($post_types as $pt) : ?>
                                <div class="gmb-sitemap-card">
                                    <div class="gmb-sitemap-card-header">
                                        <div class="gmb-flex-center-gap-md">
                                            <h3 class="gmb-sitemap-card-title"><?php echo esc_html($pt['label']); ?></h3>
                                            <span class="gmb-badge gmb-badge--neutral"><?php echo esc_html($pt['name']); ?></span>
                                            <span class="gmb-badge gmb-badge--info"><?php echo sprintf(esc_html__('%d published', 'gmb-ranker-seo-automation'), intval($pt['published_count'])); ?></span>
                                        </div>
                                        <a href="<?php echo esc_url($pt['url']); ?>" target="_blank" class="gmb-sitemap-view-link">
                                            <?php esc_html_e('View Sitemap', 'gmb-ranker-seo-automation'); ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>

                                    <div class="gmb-grid-2">
                                        <div class="gmb-module-item-card">
                                            <div>
                                                <div class="gmb-form-label"><?php esc_html_e('Include in Sitemap', 'gmb-ranker-seo-automation'); ?></div>
                                                <div class="gmb-text-muted gmb-text-xs"><?php echo sprintf(esc_html__('Add %s to XML sitemap index', 'gmb-ranker-seo-automation'), esc_html(strtolower($pt['label']))); ?></div>
                                            </div>
                                            <label class="gmb-switch">
                                                <input type="hidden" name="gmb_sitemap_include_pt_<?php echo esc_attr($pt['name']); ?>" value="0" />
                                                <input type="checkbox" name="gmb_sitemap_include_pt_<?php echo esc_attr($pt['name']); ?>" value="1" <?php checked(true, $pt['include']); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                        </div>

                                        <div class="gmb-module-item-card">
                                            <div>
                                                <div class="gmb-form-label"><?php esc_html_e('Include Images', 'gmb-ranker-seo-automation'); ?></div>
                                                <div class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Add image tags for this post type', 'gmb-ranker-seo-automation'); ?></div>
                                            </div>
                                            <label class="gmb-switch">
                                                <input type="hidden" name="gmb_sitemap_images_pt_<?php echo esc_attr($pt['name']); ?>" value="0" />
                                                <input type="checkbox" name="gmb_sitemap_images_pt_<?php echo esc_attr($pt['name']); ?>" value="1" <?php checked(true, $pt['include_images']); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- Subtab 3: Taxonomies Sitemaps -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'taxonomies') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-taxonomies" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Taxonomy Sitemaps', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Configure taxonomy archives (Categories, Tags, etc.) in dynamic XML sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-sitemap-cards-list">
                                <?php foreach ($taxonomies as $tax) : ?>
                                <div class="gmb-sitemap-card">
                                    <div class="gmb-sitemap-card-header">
                                        <div class="gmb-flex-center-gap-md">
                                            <h3 class="gmb-sitemap-card-title"><?php echo esc_html($tax['label']); ?></h3>
                                            <span class="gmb-badge gmb-badge--neutral"><?php echo esc_html($tax['name']); ?></span>
                                            <span class="gmb-badge gmb-badge--info"><?php echo sprintf(esc_html__('%d terms', 'gmb-ranker-seo-automation'), intval($tax['term_count'])); ?></span>
                                        </div>
                                        <a href="<?php echo esc_url($tax['url']); ?>" target="_blank" class="gmb-sitemap-view-link">
                                            <?php esc_html_e('View Sitemap', 'gmb-ranker-seo-automation'); ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>

                                    <div class="gmb-grid-2">
                                        <div class="gmb-module-item-card">
                                            <div>
                                                <div class="gmb-form-label"><?php esc_html_e('Include in Sitemap', 'gmb-ranker-seo-automation'); ?></div>
                                                <div class="gmb-text-muted gmb-text-xs"><?php echo sprintf(esc_html__('Add %s to XML sitemap index', 'gmb-ranker-seo-automation'), esc_html(strtolower($tax['label']))); ?></div>
                                            </div>
                                            <label class="gmb-switch">
                                                <input type="hidden" name="gmb_sitemap_include_tax_<?php echo esc_attr($tax['name']); ?>" value="0" />
                                                <input type="checkbox" name="gmb_sitemap_include_tax_<?php echo esc_attr($tax['name']); ?>" value="1" <?php checked(true, $tax['include']); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                        </div>

                                        <div class="gmb-module-item-card">
                                            <div>
                                                <div class="gmb-form-label"><?php esc_html_e('Include Empty Terms', 'gmb-ranker-seo-automation'); ?></div>
                                                <div class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Include terms with 0 assigned posts', 'gmb-ranker-seo-automation'); ?></div>
                                            </div>
                                            <label class="gmb-switch">
                                                <input type="hidden" name="gmb_sitemap_empty_tax_<?php echo esc_attr($tax['name']); ?>" value="0" />
                                                <input type="checkbox" name="gmb_sitemap_empty_tax_<?php echo esc_attr($tax['name']); ?>" value="1" <?php checked(true, $tax['include_empty']); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- Subtab 4: Authors Sitemaps -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'authors') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-authors" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Author Sitemaps', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Configure author archive pages for crawling and indexing in XML sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-card-settings-list">
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Include Authors', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_sitemap_include_authors" value="0" />
                                            <input type="checkbox" name="gmb_sitemap_include_authors" value="1" <?php checked(true, $s['include_authors']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Add an author sitemap (author-sitemap.xml) to your XML sitemap index for multi-author discoverability.', 'gmb-ranker-seo-automation'); ?></p>
                                        
                                        <?php if ($s['include_authors']) : ?>
                                        <div class="gmb-mt-12">
                                            <a href="<?php echo esc_url(GMB_Ranker_SEO_Sitemap_Registry::get_author_sitemap_url()); ?>" target="_blank" class="gmb-sitemap-link"><?php esc_html_e('View Author Sitemap (author-sitemap.xml) →', 'gmb-ranker-seo-automation'); ?></a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- Subtab 5: HTML Sitemap -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'html') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-html" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('HTML Sitemap', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Generate a clean, user-friendly HTML sitemap to help visitors and search engines navigate your content.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-card-settings-list">
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('HTML Sitemap Shortcode', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="hidden" name="gmb_sitemap_html_enable" value="0" />
                                            <input type="checkbox" name="gmb_sitemap_html_enable" value="1" <?php checked(true, $s['html_enable']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Enables the [gmb_html_sitemap] shortcode for embedding a dynamic HTML sitemap on any page.', 'gmb-ranker-seo-automation'); ?></p>

                                        <div class="gmb-shortcode-preview-box">
                                            <span class="gmb-text-muted gmb-text-sm gmb-font-semibold"><?php esc_html_e('Usage Shortcode:', 'gmb-ranker-seo-automation'); ?></span>
                                            <code class="gmb-shortcode-code">[gmb_html_sitemap]</code>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-row gmb-settings-row--noborder">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Sort Order', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <select name="gmb_sitemap_html_sort" class="gmb-select-240">
                                            <option value="published" <?php selected('published', $s['html_sort']); ?>><?php esc_html_e('Date Published (Newest first)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="alphabetical" <?php selected('alphabetical', $s['html_sort']); ?>><?php esc_html_e('Alphabetical Title (A - Z)', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                        <p class="gmb-form-help"><?php esc_html_e('Specify how links are ordered inside the HTML sitemap display.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- Subtab 6: Sitemap Index & Diagnostics -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'index') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-index" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Sitemap Index & Direct URLs', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Live overview of all dynamic XML sub-sitemaps generated on your site.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <table class="gmb-table gmb-table--clean">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Sitemap Name', 'gmb-ranker-seo-automation'); ?></th>
                                        <th><?php esc_html_e('Type', 'gmb-ranker-seo-automation'); ?></th>
                                        <th><?php esc_html_e('Items', 'gmb-ranker-seo-automation'); ?></th>
                                        <th><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></th>
                                        <th class="gmb-text-right"><?php esc_html_e('Actions', 'gmb-ranker-seo-automation'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($diagnostics as $idx => $row) : 
                                        $row_cls = ($idx === 0) ? 'gmb-table-row--highlight' : '';
                                        $badge_cls = 'gmb-badge--neutral';
                                        if ($row['status'] === 'active') $badge_cls = 'gmb-badge--success';
                                        elseif ($row['status'] === 'disabled') $badge_cls = 'gmb-badge--danger';
                                    ?>
                                    <tr class="<?php echo $row_cls; ?>">
                                        <td>
                                            <strong><?php echo esc_html($row['name']); ?></strong>
                                            <?php if (!empty($row['badge'])) : ?>
                                                <span class="gmb-badge gmb-badge--success"><?php echo esc_html($row['badge']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($row['type']); ?></td>
                                        <td><?php echo esc_html($row['items']); ?></td>
                                        <td>
                                            <span class="gmb-badge <?php echo $badge_cls; ?>"><?php echo esc_html($row['status_l']); ?></span>
                                        </td>
                                        <td class="gmb-text-right">
                                            <a href="<?php echo esc_url($row['url']); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm"><?php esc_html_e('Open XML', 'gmb-ranker-seo-automation'); ?></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
