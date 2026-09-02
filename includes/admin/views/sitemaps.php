<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-sitemaps') : ?>
                <?php 
                $sitemaps_mod_val = get_option('gmb_ranker_module_sitemaps', '1');
                if ($sitemaps_mod_val === '0' || $sitemaps_mod_val === 'off') : 
                ?>
                    <div class="rm-tab-content active">
                        <div class="gmb-empty-state">
                            <h2 class="gmb-heading-2">Dynamic Sitemaps Module is Disabled</h2>
                            <p class="gmb-text-muted">Enable the Dynamic Sitemaps module to configure XML Sitemaps, post types, taxonomies, and sitemap indexes.</p>
                            <div class="gmb-flex-center-gap-md">
                                <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_sitemaps" >Enable Module</button>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-btn gmb-btn-secondary">Go to Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="rm-tab-content active" id="rm-tab-sitemaps">
                        <form method="post" action="options.php">
                            <?php settings_fields('gmb_ranker_sitemaps_group'); ?>
                            
                            <div class="gmb-sidebar-layout-container">
                                
                                <!-- Sidebar Navigation Column -->
                                <?php
                                $active_sub = 'general';
                                $req_sub = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : (isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : ''));
                                if (!empty($req_sub) && in_array($req_sub, array('general', 'post-types', 'taxonomies', 'authors', 'html', 'index', 'settings'), true)) {
                                    $active_sub = ($req_sub === 'settings') ? 'general' : $req_sub;
                                } elseif (!empty($current_tab) && in_array($current_tab, array('general', 'post-types', 'taxonomies', 'authors', 'html', 'index', 'settings'), true)) {
                                    $active_sub = ($current_tab === 'settings') ? 'general' : $current_tab;
                                }
                                ?>
                                <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_sub); ?>" />
                                <div class="gmb-sidebar-nav">
                                    <ul>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'general') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-general">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                            General
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'post-types') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-post-types">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            Post Types
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'taxonomies') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-taxonomies">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                            Taxonomies
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'authors') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-authors">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            Authors
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'html') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-html">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                            HTML Sitemap
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'index') ? 'active' : ''; ?>" data-subtab="gmb-subtab-sitemap-index">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                            Sitemap Index &amp; URLs
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Content Settings Column -->
                                <div class="gmb-sidebar-content-panel">
                                    
                                    <!-- Subtab 1: General Sitemap Settings -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'general') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-general">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">General Sitemap Settings</h2>
                                            <p class="gmb-text-muted">Configure dynamic XML sitemaps to optimize crawling and indexing by search engines. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                        </div>

                                        <!-- Main Index Banner -->
                                        <div class="gmb-callout gmb-callout--info gmb-sitemap-banner">
                                            <div class="gmb-sitemap-banner-left">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="#466afa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon-img"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <div>
                                                    <span class="gmb-sitemap-banner-title">
                                                        Your main XML sitemap index is available at:
                                                    </span>
                                                    <div>
                                                        <a href="<?php echo esc_url(home_url('/sitemap_index.xml')); ?>" target="_blank" class="gmb-help-link font-semibold gmb-sitemap-link">
                                                            <?php echo esc_url(home_url('/sitemap_index.xml')); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="gmb-callout-actions">
                                                <a href="<?php echo esc_url(home_url('/sitemap_index.xml')); ?>" target="_blank" class="button gmb-callout-btn gmb-sitemap-btn">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                    Open Sitemap
                                                </a>
                                            </div>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- Option: Enable/Disable Sitemaps -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    XML Sitemaps
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_ranker_module_sitemaps" value="1" <?php checked('1', get_option('gmb_ranker_module_sitemaps', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Natively generate search engine compliant XML sitemap indexes without writing static files.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Links Per Sitemap -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Links Per Sitemap
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="number" name="gmb_sitemap_items_per_page" min="10" max="50000" step="10" value="<?php echo esc_attr(get_option('gmb_sitemap_items_per_page', '1000')); ?>" class="gmb-input-num-sm" />
                                                    <p class="gmb-form-help">Maximum number of URLs per sitemap file (Default: 1,000. Google recommends up to 50,000).</p>
                                                </div>
                                            </div>

                                            <!-- Option: Images in Sitemaps -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Images in Sitemaps
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_sitemap_include_images" value="1" <?php checked('1', get_option('gmb_sitemap_include_images', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Include image tags in sitemap entries to help search engines index your media assets.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Include Featured Images -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Include Featured Images
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_sitemap_include_featured_images" value="1" <?php checked('1', get_option('gmb_sitemap_include_featured_images', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Automatically attach featured post images to the XML sitemap.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Ping Search Engines -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Ping Search Engines
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_sitemap_ping_search_engines" value="1" <?php checked('1', get_option('gmb_sitemap_ping_search_engines', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Automatically notify Google and Bing whenever a post or sitemap is updated.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Exclude Posts -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Exclude Posts by ID
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="text" name="gmb_sitemap_excluded_posts" placeholder="e.g. 12, 45, 108" value="<?php echo esc_attr(get_option('gmb_sitemap_excluded_posts', '')); ?>" class="gmb-input-max-480" />
                                                    <p class="gmb-form-help">Comma-separated list of Post or Page IDs to exclude from the sitemap.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Exclude Terms -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Exclude Terms by ID
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="text" name="gmb_sitemap_excluded_terms" placeholder="e.g. 3, 8" value="<?php echo esc_attr(get_option('gmb_sitemap_excluded_terms', '')); ?>" class="gmb-input-max-480" />
                                                    <p class="gmb-form-help">Comma-separated list of Category or Tag Term IDs to exclude from the taxonomy sitemap.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Exclude Slugs -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Exclude Specific Slugs
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <textarea name="gmb_ranker_sitemap_exclude_slugs" placeholder="e.g. contact-us, privacy-policy" class="gmb-textarea-max-480"><?php echo esc_textarea(get_option('gmb_ranker_sitemap_exclude_slugs', '')); ?></textarea>
                                                    <p class="gmb-form-help">Comma-separated slugs or path segments to exclude from the generated sitemap.</p>
                                                </div>
                                            </div>

                                            <!-- Option: Custom URLs -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Include Custom Extra URLs
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <textarea name="gmb_sitemap_custom_urls" placeholder="https://example.com/custom-landing-page/&#10;https://example.com/special-tool/" class="gmb-textarea-max-480-lg"><?php echo esc_textarea(get_option('gmb_sitemap_custom_urls', '')); ?></textarea>
                                                    <p class="gmb-form-help">One full absolute URL per line to inject into the main sitemap index.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                        </div>
                                    </div>

                                    <!-- Subtab 2: Post Types Sitemaps -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'post-types') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-post-types">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Post Type Sitemaps</h2>
                                            <p class="gmb-text-muted">Choose which post types should be dynamically indexed in your XML sitemaps.</p>
                                        </div>

                                        <div class="gmb-sitemap-cards-list">
                                            <?php
                                            $all_pts = get_post_types(array('public' => true), 'objects');
                                            foreach ($all_pts as $pt_name => $pt_obj) :
                                                $pt_count = wp_count_posts($pt_name);
                                                $pub_count = !empty($pt_count->publish) ? $pt_count->publish : 0;
                                                $is_inc = get_option('gmb_sitemap_include_pt_' . $pt_name, ($pt_name === 'attachment' ? '0' : '1')) !== '0';
                                                $is_img = get_option('gmb_sitemap_images_pt_' . $pt_name, '1') !== '0';
                                            ?>
                                            <div class="gmb-sitemap-card">
                                                <div class="gmb-sitemap-card-header">
                                                    <div class="gmb-flex-center-gap-md">
                                                        <h3 class="gmb-sitemap-card-title"><?php echo esc_html($pt_obj->labels->name); ?></h3>
                                                        <span class="gmb-badge gmb-badge--neutral"><?php echo esc_html($pt_name); ?></span>
                                                        <span class="gmb-badge gmb-badge--info"><?php echo intval($pub_count); ?> published</span>
                                                    </div>
                                                    <a href="<?php echo esc_url(home_url('/' . $pt_name . '-sitemap.xml')); ?>" target="_blank" class="gmb-sitemap-view-link">
                                                        View Sitemap
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                    </a>
                                                </div>

                                                <div class="gmb-grid-2">
                                                    <div class="gmb-module-item-card">
                                                        <div>
                                                            <div class="gmb-form-label">Include in Sitemap</div>
                                                            <div class="gmb-text-muted gmb-text-xs">Add <?php echo esc_html(strtolower($pt_obj->labels->name)); ?> to XML sitemap index</div>
                                                        </div>
                                                        <label class="gmb-switch">
                                                            <input type="checkbox" name="gmb_sitemap_include_pt_<?php echo esc_attr($pt_name); ?>" value="1" <?php checked($is_inc, true); ?> />
                                                            <span class="gmb-slider round"></span>
                                                        </label>
                                                    </div>

                                                    <div class="gmb-module-item-card">
                                                        <div>
                                                            <div class="gmb-form-label">Include Images</div>
                                                            <div class="gmb-text-muted gmb-text-xs">Add image tags for this post type</div>
                                                        </div>
                                                        <label class="gmb-switch">
                                                            <input type="checkbox" name="gmb_sitemap_images_pt_<?php echo esc_attr($pt_name); ?>" value="1" <?php checked($is_img, true); ?> />
                                                            <span class="gmb-slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end" >
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                        </div>
                                    </div>

                                    <!-- Subtab 3: Taxonomies Sitemaps -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'taxonomies') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-taxonomies">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Taxonomy Sitemaps</h2>
                                            <p class="gmb-text-muted">Configure taxonomy archives (Categories, Tags, etc.) in dynamic XML sitemaps.</p>
                                        </div>

                                        <div class="gmb-sitemap-cards-list">
                                            <?php
                                            $all_taxes = get_taxonomies(array('public' => true), 'objects');
                                            foreach ($all_taxes as $tax_name => $tax_obj) :
                                                $term_count = wp_count_terms(array('taxonomy' => $tax_name, 'hide_empty' => false));
                                                $t_count = is_wp_error($term_count) ? 0 : $term_count;
                                                $is_inc = get_option('gmb_sitemap_include_tax_' . $tax_name, ($tax_name === 'post_format' ? '0' : '1')) !== '0';
                                                $is_empty = get_option('gmb_sitemap_empty_tax_' . $tax_name, '0') === '1';
                                            ?>
                                            <div class="gmb-sitemap-card">
                                                <div class="gmb-sitemap-card-header">
                                                    <div class="gmb-flex-center-gap-md">
                                                        <h3 class="gmb-sitemap-card-title"><?php echo esc_html($tax_obj->labels->name); ?></h3>
                                                        <span class="gmb-badge gmb-badge--neutral"><?php echo esc_html($tax_name); ?></span>
                                                        <span class="gmb-badge gmb-badge--info"><?php echo intval($t_count); ?> terms</span>
                                                    </div>
                                                    <a href="<?php echo esc_url(home_url('/' . $tax_name . '-sitemap.xml')); ?>" target="_blank" class="gmb-sitemap-view-link">
                                                        View Sitemap
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--xs"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                    </a>
                                                </div>

                                                <div class="gmb-grid-2">
                                                    <div class="gmb-module-item-card">
                                                        <div>
                                                            <div class="gmb-form-label">Include in Sitemap</div>
                                                            <div class="gmb-text-muted gmb-text-xs">Add <?php echo esc_html(strtolower($tax_obj->labels->name)); ?> to XML sitemap index</div>
                                                        </div>
                                                        <label class="gmb-switch">
                                                            <input type="checkbox" name="gmb_sitemap_include_tax_<?php echo esc_attr($tax_name); ?>" value="1" <?php checked($is_inc, true); ?> />
                                                            <span class="gmb-slider round"></span>
                                                        </label>
                                                    </div>

                                                    <div class="gmb-module-item-card">
                                                        <div>
                                                            <div class="gmb-form-label">Include Empty Terms</div>
                                                            <div class="gmb-text-muted gmb-text-xs">Include terms with 0 assigned posts</div>
                                                        </div>
                                                        <label class="gmb-switch">
                                                            <input type="checkbox" name="gmb_sitemap_empty_tax_<?php echo esc_attr($tax_name); ?>" value="1" <?php checked($is_empty, true); ?> />
                                                            <span class="gmb-slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end" >
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                        </div>
                                    </div>

                                    <!-- Subtab 4: Authors Sitemaps -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'authors') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-authors">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Author Sitemaps</h2>
                                            <p class="gmb-text-muted">Configure author archive pages for crawling and indexing in XML sitemaps.</p>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- Option: Include Authors in Sitemap -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Include Authors
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_sitemap_include_authors" value="1" <?php checked('1', get_option('gmb_sitemap_include_authors', '0')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Add an author sitemap (<code>/author-sitemap.xml</code>) to your XML sitemap index. Useful for multi-author sites to boost author E-E-A-T credentials.</p>
                                                    
                                                    <?php if (get_option('gmb_sitemap_include_authors', '0') === '1') : ?>
                                                    <div class="gmb-mt-12">
                                                        <a href="<?php echo esc_url(home_url('/author-sitemap.xml')); ?>" target="_blank" class="gmb-sitemap-link">View Author Sitemap (author-sitemap.xml) &rarr;</a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end" >
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                        </div>
                                    </div>

                                    <!-- Subtab 5: HTML Sitemap -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'html') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-html">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">HTML Sitemap</h2>
                                            <p class="gmb-text-muted">Generate a clean, user-friendly HTML sitemap to help visitors and search engines navigate your content.</p>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- Option: Enable HTML Sitemap -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    HTML Sitemap Shortcode
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_sitemap_html_enable" value="1" <?php checked('1', get_option('gmb_sitemap_html_enable', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Enables the <code>[gmb_html_sitemap]</code> shortcode for embedding a dynamic HTML sitemap on any page.</p>

                                                    <div class="gmb-shortcode-preview-box">
                                                        <span class="gmb-text-muted gmb-text-sm gmb-font-semibold">Usage Shortcode:</span>
                                                        <code class="gmb-shortcode-code">[gmb_html_sitemap]</code>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Option: HTML Sitemap Sort Order -->
                                            <div class="gmb-settings-row gmb-settings-row--noborder">
                                                <div class="gmb-settings-label-col">
                                                    Sort Order
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <select name="gmb_sitemap_html_sort" class="gmb-select-240">
                                                        <option value="published" <?php selected('published', get_option('gmb_sitemap_html_sort', 'published')); ?>>Date Published (Newest first)</option>
                                                        <option value="alphabetical" <?php selected('alphabetical', get_option('gmb_sitemap_html_sort', 'published')); ?>>Alphabetical Title (A - Z)</option>
                                                    </select>
                                                    <p class="gmb-form-help">Specify how links are ordered inside the HTML sitemap display.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end" >
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                        </div>
                                    </div>

                                    <!-- Subtab 6: Sitemap Index & Diagnostics -->
                                    <div class="gmb-subtab-panel <?php echo ($active_sub === 'index') ? 'active' : ''; ?>" id="gmb-subtab-sitemap-index">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Sitemap Index &amp; Direct URLs</h2>
                                            <p class="gmb-text-muted">Live overview of all dynamic XML sub-sitemaps generated on your site.</p>
                                        </div>

                                        <table class="gmb-table gmb-table--clean">
                                             <thead>
                                                 <tr>
                                                     <th>Sitemap Name</th>
                                                     <th>Type</th>
                                                     <th>Items</th>
                                                     <th>Status</th>
                                                     <th class="gmb-text-right">Actions</th>
                                                 </tr>
                                             </thead>
                                             <tbody>
                                                 <!-- Main Index -->
                                                 <tr class="gmb-table-row--highlight">
                                                     <td>
                                                         <strong>sitemap_index.xml</strong> <span class="gmb-badge gmb-badge--success">Main Index</span>
                                                     </td>
                                                     <td>Sitemap Index</td>
                                                     <td>All Enabled Sub-sitemaps</td>
                                                     <td>
                                                         <span class="gmb-badge gmb-badge--success">Active</span>
                                                     </td>
                                                     <td class="gmb-text-right">
                                                         <a href="<?php echo esc_url(home_url('/sitemap_index.xml')); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm">Open XML</a>
                                                     </td>
                                                 </tr>

                                                 <!-- Post Types Sub-sitemaps -->
                                                 <?php
                                                 $diag_pts = get_post_types(array('public' => true), 'objects');
                                                 foreach ($diag_pts as $pt_name => $pt_obj) :
                                                     $is_active = get_option('gmb_sitemap_include_pt_' . $pt_name, ($pt_name === 'attachment' ? '0' : '1')) !== '0';
                                                     $c = wp_count_posts($pt_name);
                                                     $count_num = !empty($c->publish) ? $c->publish : 0;
                                                 ?>
                                                 <tr>
                                                     <td>
                                                         <strong><?php echo esc_html($pt_name); ?>-sitemap.xml</strong>
                                                     </td>
                                                     <td>Post Type (<?php echo esc_html($pt_obj->labels->singular_name); ?>)</td>
                                                     <td><?php echo intval($count_num); ?> URLs</td>
                                                     <td>
                                                         <?php if ($is_active && $count_num > 0) : ?>
                                                             <span class="gmb-badge gmb-badge--success">Active</span>
                                                         <?php elseif (!$is_active) : ?>
                                                             <span class="gmb-badge gmb-badge--danger">Disabled</span>
                                                         <?php else : ?>
                                                             <span class="gmb-badge gmb-badge--neutral">Empty</span>
                                                         <?php endif; ?>
                                                     </td>
                                                     <td class="gmb-text-right">
                                                         <a href="<?php echo esc_url(home_url('/' . $pt_name . '-sitemap.xml')); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm">Open XML</a>
                                                     </td>
                                                 </tr>
                                                 <?php endforeach; ?>

                                                 <!-- Taxonomies Sub-sitemaps -->
                                                 <?php
                                                 $diag_taxes = get_taxonomies(array('public' => true), 'objects');
                                                 foreach ($diag_taxes as $tax_name => $tax_obj) :
                                                     $is_active = get_option('gmb_sitemap_include_tax_' . $tax_name, ($tax_name === 'post_format' ? '0' : '1')) !== '0';
                                                     $tc = wp_count_terms(array('taxonomy' => $tax_name, 'hide_empty' => false));
                                                     $t_num = is_wp_error($tc) ? 0 : $tc;
                                                 ?>
                                                 <tr>
                                                     <td>
                                                         <strong><?php echo esc_html($tax_name); ?>-sitemap.xml</strong>
                                                     </td>
                                                     <td>Taxonomy (<?php echo esc_html($tax_obj->labels->singular_name); ?>)</td>
                                                     <td><?php echo intval($t_num); ?> Terms</td>
                                                     <td>
                                                         <?php if ($is_active && $t_num > 0) : ?>
                                                             <span class="gmb-badge gmb-badge--success">Active</span>
                                                         <?php elseif (!$is_active) : ?>
                                                             <span class="gmb-badge gmb-badge--danger">Disabled</span>
                                                         <?php else : ?>
                                                             <span class="gmb-badge gmb-badge--neutral">Empty</span>
                                                         <?php endif; ?>
                                                     </td>
                                                     <td class="gmb-text-right">
                                                         <a href="<?php echo esc_url(home_url('/' . $tax_name . '-sitemap.xml')); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm">Open XML</a>
                                                     </td>
                                                 </tr>
                                                 <?php endforeach; ?>

                                                 <!-- Authors Sub-sitemap -->
                                                 <?php
                                                 $is_authors_active = get_option('gmb_sitemap_include_authors', '0') === '1';
                                                 $author_count = count(get_users(array('has_published_posts' => array('post', 'page'))));
                                                 ?>
                                                 <tr>
                                                     <td>
                                                         <strong>author-sitemap.xml</strong>
                                                     </td>
                                                     <td>Authors Archive</td>
                                                     <td><?php echo intval($author_count); ?> Authors</td>
                                                     <td>
                                                         <?php if ($is_authors_active && $author_count > 0) : ?>
                                                             <span class="gmb-badge gmb-badge--success">Active</span>
                                                         <?php elseif (!$is_authors_active) : ?>
                                                             <span class="gmb-badge gmb-badge--danger">Disabled</span>
                                                         <?php else : ?>
                                                             <span class="gmb-badge gmb-badge--neutral">Empty</span>
                                                         <?php endif; ?>
                                                     </td>
                                                     <td class="gmb-text-right">
                                                         <a href="<?php echo esc_url(home_url('/author-sitemap.xml')); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm">Open XML</a>
                                                     </td>
                                                 </tr>

                                                 <!-- Custom URLs Sub-sitemap -->
                                                 <?php
                                                 $custom_urls_str = get_option('gmb_sitemap_custom_urls', '');
                                                 $custom_urls_list = array_filter(array_map('trim', explode("\n", $custom_urls_str)));
                                                 $custom_urls_count = count($custom_urls_list);
                                                 if ($custom_urls_count > 0) :
                                                 ?>
                                                 <tr>
                                                     <td>
                                                         <strong>custom-sitemap.xml</strong>
                                                     </td>
                                                     <td>Custom URLs</td>
                                                     <td><?php echo intval($custom_urls_count); ?> URLs</td>
                                                     <td>
                                                         <span class="gmb-badge gmb-badge--success">Active</span>
                                                     </td>
                                                     <td class="gmb-text-right">
                                                         <a href="<?php echo esc_url(home_url('/custom-sitemap.xml')); ?>" target="_blank" class="button gmb-btn--secondary gmb-btn--sm">Open XML</a>
                                                     </td>
                                                 </tr>
                                                 <?php endif; ?>
                                             </tbody>
                                         </table>
                                     </div>

                                 </div>
                             </div>
                         </form>
                     </div>
                 <?php endif; ?>
             <?php endif; ?>

            <!-- Page: Schema Settings Page -->
