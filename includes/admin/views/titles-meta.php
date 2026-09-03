<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-metadata') : ?>
                <?php 
                $meta_mod_val = get_option('gmb_ranker_module_metadata', '1');
                if ($meta_mod_val === '0' || $meta_mod_val === 'off') : 
                ?>
                    <div class="rm-tab-content active">
                        <div class="gmb-empty-state">
                            <h2 class="gmb-heading-2">Metadata Manager Module is Disabled</h2>
                            <p class="gmb-text-muted">Enable the Metadata Manager module to configure SEO Titles, Descriptions, and Robots metadata settings.</p>
                            <div class="gmb-flex-center-gap-md">
                                <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_metadata" >Enable Module</button>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button gmb-btn--secondary">Go to Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="rm-tab-content active" id="rm-tab-metadata">
                        <form method="post" action="options.php" novalidate>
                            <?php settings_fields('gmb_ranker_titles_meta_group'); ?>
                    
                    <div class="gmb-sidebar-layout-container">
                        
                        <!-- Sidebar Navigation Column -->
                        <?php
                        $active_sub = 'metadata';
                        $req_sub = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : (isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : ''));
                        if (!empty($req_sub) && in_array($req_sub, array('metadata', 'local', 'social', 'homepage', 'authors', 'misc', 'posts', 'pages', 'attachments', 'services', 'service_locations', 'team_members', 'categories', 'settings'), true)) {
                            $active_sub = ($req_sub === 'settings') ? 'metadata' : $req_sub;
                        } elseif (!empty($current_tab) && in_array($current_tab, array('metadata', 'local', 'social', 'homepage', 'authors', 'misc', 'posts', 'pages', 'attachments', 'services', 'service_locations', 'team_members', 'categories', 'settings'), true)) {
                            $active_sub = ($current_tab === 'settings') ? 'metadata' : $current_tab;
                        }
                        ?>
                        <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_sub); ?>" />
                        <div class="gmb-sidebar-nav">
                            <ul>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'metadata') ? 'active' : ''; ?>" data-subtab="gmb-subtab-metadata">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    Global Meta
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'local') ? 'active' : ''; ?>" data-subtab="gmb-subtab-local">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    Local SEO
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'social') ? 'active' : ''; ?>" data-subtab="gmb-subtab-social">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                                    Social Meta
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'homepage') ? 'active' : ''; ?>" data-subtab="gmb-subtab-homepage">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                    Homepage
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'authors') ? 'active' : ''; ?>" data-subtab="gmb-subtab-authors">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    Authors
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'misc') ? 'active' : ''; ?>" data-subtab="gmb-subtab-misc">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                                    Misc Pages
                                </li>
                                <li class="gmb-sidebar-nav-heading">Post Types</li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'posts') ? 'active' : ''; ?>" data-subtab="gmb-subtab-posts">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    Posts
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'pages') ? 'active' : ''; ?>" data-subtab="gmb-subtab-pages">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    Pages
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'attachments') ? 'active' : ''; ?>" data-subtab="gmb-subtab-attachments">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    Attachments
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'services') ? 'active' : ''; ?>" data-subtab="gmb-subtab-services">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    Services
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'service_locations') ? 'active' : ''; ?>" data-subtab="gmb-subtab-service_locations">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    Service Locations
                                </li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'team_members') ? 'active' : ''; ?>" data-subtab="gmb-subtab-team_members">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    Team Members
                                </li>
                                <li class="gmb-sidebar-nav-heading">Taxonomies</li>
                                <li class="gmb-sidebar-nav-item <?php echo ($active_sub === 'categories') ? 'active' : ''; ?>" data-subtab="gmb-subtab-categories">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                    Categories
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Content Column -->
                        <div class="gmb-sidebar-content-panel">
                            
                            <!-- Subtab: Metadata -->
                            <?php
                            $post_title_temp = isset($post_title_temp) ? $post_title_temp : get_option('gmb_posts_title_template', '%title% %sep% %sitename%');
                            $post_desc_temp = isset($post_desc_temp) ? $post_desc_temp : get_option('gmb_posts_description_template', '%excerpt%');
                            $page_title_temp = isset($page_title_temp) ? $page_title_temp : get_option('gmb_pages_title_template', '%title% %sep% %sitename%');
                            $page_desc_temp = isset($page_desc_temp) ? $page_desc_temp : get_option('gmb_pages_description_template', '%excerpt%');
                            $use_multiple = isset($use_multiple) ? $use_multiple : get_option('gmb_local_use_multiple_locations', '0');
                            ?>
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'metadata') ? 'active' : ''; ?>" id="gmb-subtab-metadata">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Titles & Meta Settings</h2>
                                    <p class="gmb-text-muted">Configure global templates to automatically generate indexing metadata for your pages and posts. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <!-- Option: Post Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Post Title Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_metadata_post_title_template" name="gmb_metadata_post_title_template" value="<?php echo esc_attr($post_title_temp); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%title%">+ %title%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%sep%">+ %sep%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%sitename%">+ %sitename%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%category%">+ %category%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%author%">+ %author%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_title_template" data-tag="%currentyear%">+ %currentyear%</span>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title template for blog posts. Click variable pills above to insert dynamically.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Post Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Post Description Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_metadata_post_desc_template" name="gmb_metadata_post_desc_template" value="<?php echo esc_attr($post_desc_temp); ?>" placeholder="%excerpt%" class="gmb-input gmb-input--max-480" />
                                            <div>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_desc_template" data-tag="%excerpt%">+ %excerpt%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_desc_template" data-tag="%title%">+ %title%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_desc_template" data-tag="%sitename%">+ %sitename%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_post_desc_template" data-tag="%focus_keyword%">+ %focus_keyword%</span>
                                            </div>
                                            <p class="gmb-form-help">
                                                Meta description template for blog posts. Use variables like <code>%excerpt%</code> or <code>%title%</code>.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Page Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Page Title Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_metadata_page_title_template" name="gmb_metadata_page_title_template" value="<?php echo esc_attr($page_title_temp); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_title_template" data-tag="%title%">+ %title%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_title_template" data-tag="%sep%">+ %sep%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_title_template" data-tag="%sitename%">+ %sitename%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_title_template" data-tag="%currentyear%">+ %currentyear%</span>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title template for static pages. Use variables like <code>%title%</code>, <code>%sitename%</code>.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Page Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Page Description Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_metadata_page_desc_template" name="gmb_metadata_page_desc_template" value="<?php echo esc_attr($page_desc_temp); ?>" placeholder="%excerpt%" class="gmb-input gmb-input--max-480" />
                                            <div>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_desc_template" data-tag="%excerpt%">+ %excerpt%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_desc_template" data-tag="%title%">+ %title%</span>
                                                <span class="gmb-tag-insert-pill" data-target="gmb_metadata_page_desc_template" data-tag="%sitename%">+ %sitename%</span>
                                            </div>
                                            <p class="gmb-form-help">
                                                Meta description template for static pages. Use variables like <code>%excerpt%</code>, <code>%title%</code>.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Robots Meta (Global) -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php
                                             $global_robots = get_option('gmb_metadata_global_robots', 'index');
                                             $global_robots_array = is_array($global_robots) ? $global_robots : array_map('trim', explode(',', strtolower((string)$global_robots)));
                                             $max_img = get_option('gmb_metadata_global_max_image', 'large');
                                             $max_snp = get_option('gmb_metadata_global_max_snippet', '-1');
                                             $max_vid = get_option('gmb_metadata_global_max_video', '-1');
                                             ?>
                                            <div class="gmb-grid-2col-max480">
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="index" <?php checked(in_array('index', $global_robots_array)); ?> />
                                                    <strong>index</strong>
                                                </label>
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="noindex" <?php checked(in_array('noindex', $global_robots_array)); ?> />
                                                    <strong>noindex</strong>
                                                </label>
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="nofollow" <?php checked(in_array('nofollow', $global_robots_array)); ?> />
                                                    <strong>nofollow</strong>
                                                </label>
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="noarchive" <?php checked(in_array('noarchive', $global_robots_array)); ?> />
                                                    <strong>noarchive</strong>
                                                </label>
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="noimageindex" <?php checked(in_array('noimageindex', $global_robots_array)); ?> />
                                                    <strong>noimageindex</strong>
                                                </label>
                                                <label class="gmb-checkbox-label">
                                                    <input type="checkbox" name="gmb_metadata_global_robots[]" value="nosnippet" <?php checked(in_array('nosnippet', $global_robots_array)); ?> />
                                                    <strong>nosnippet</strong>
                                                </label>
                                            </div>

                                            <div class="gmb-meta-directives-box">
                                                <div class="gmb-meta-directives-title">Google Discover & Snippet Preview Directives</div>
                                                <div class="gmb-meta-directive-row">
                                                    <label class="gmb-form-label">Max Image Preview:</label>
                                                    <select name="gmb_metadata_global_max_image" class="gmb-meta-directive-input">
                                                        <option value="large" <?php selected($max_img, 'large'); ?>>large (Recommended for Google Discover)</option>
                                                        <option value="standard" <?php selected($max_img, 'standard'); ?>>standard</option>
                                                        <option value="none" <?php selected($max_img, 'none'); ?>>none</option>
                                                    </select>
                                                </div>
                                                <div class="gmb-meta-directive-row">
                                                    <label class="gmb-form-label">Max Snippet:</label>
                                                    <input type="text" name="gmb_metadata_global_max_snippet" value="<?php echo esc_attr($max_snp); ?>" placeholder="-1" class="gmb-meta-directive-input" />
                                                </div>
                                                <div class="gmb-meta-directive-row">
                                                    <label class="gmb-form-label">Max Video Preview:</label>
                                                    <input type="text" name="gmb_metadata_global_max_video" value="<?php echo esc_attr($max_vid); ?>" placeholder="-1" class="gmb-meta-directive-input" />
                                                </div>
                                            </div>

                                            <p class="gmb-form-help">
                                                Global robot directives for search index crawlers. Can be overridden on individual pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Separator Character -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Separator Character
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php $selected_sep = get_option('gmb_metadata_separator', '-'); ?>
                                            <div class="gmb-separator-selector gmb-flex-gap-sm">
                                                <?php
                                                $seps = array('-', '—', '|', '»', '•', '*');
                                                foreach ($seps as $sep) :
                                                    $active = ($selected_sep === $sep);
                                                ?>
                                                    <label class="gmb-sep-btn <?php echo $active ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_metadata_separator" value="<?php echo esc_attr($sep); ?>" <?php checked($selected_sep, $sep); ?> class="gmb-hidden" onchange="this.closest('.gmb-separator-selector').querySelectorAll('.gmb-sep-btn').forEach(b => b.classList.remove('active')); this.parentNode.classList.add('active');" />
                                                        <?php echo esc_html($sep); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <p class="gmb-form-help">
                                                Character used as a title separator. Will replace <code>%sep%</code> in your metadata templates.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Capitalize Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Capitalize Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php $capitalize = get_option('gmb_metadata_capitalize_titles', '0'); ?>
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_metadata_capitalize_titles" value="1" <?php checked($capitalize, '1'); ?> />
                                                <span class="gmb-slider"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Automatically capitalize the first character of each word in page and post titles.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: OpenGraph Thumbnail -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            OpenGraph Thumbnail
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php $og_thumb = get_option('gmb_metadata_og_thumbnail', ''); ?>
                                            <div class="gmb-flex-center-gap-sm gmb-input--max-480">
                                                <input type="text" id="gmb_metadata_og_thumbnail" name="gmb_metadata_og_thumbnail" value="<?php echo esc_attr($og_thumb); ?>" class="gmb-input gmb-flex-1" placeholder="No image selected..." />
                                                <button type="button" class="button gmb-media-upload-btn" data-target="gmb_metadata_og_thumbnail">Select Image</button>
                                            </div>
                                            <div id="gmb_metadata_og_thumbnail_preview" class="gmb-thumb-preview <?php echo empty($og_thumb) ? 'gmb-hidden' : ''; ?>">
                                                <img src="<?php echo esc_url($og_thumb); ?>" alt="Thumbnail Preview" />
                                            </div>
                                            <p class="gmb-form-help">
                                                When a featured image or custom social image is not set for individual content, this image will be used as a fallback thumbnail when shared on Facebook. Recommended size: 1200 x 630 pixels.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Option: Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <?php $tw_card = get_option('gmb_metadata_twitter_card_type', 'summary_large_image'); ?>
                                            <select name="gmb_metadata_twitter_card_type" class="gmb-select gmb-input--max-480">
                                                <option value="summary_large_image" <?php selected($tw_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($tw_card, 'summary'); ?>>Summary Card (Small Square Thumbnail)</option>
                                                <option value="app" <?php selected($tw_card, 'app'); ?>>App Card</option>
                                                <option value="player" <?php selected($tw_card, 'player'); ?>>Player Card (Video / Audio)</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default card type for Twitter/X fallback sharing.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Section: Webmaster Verification Tools -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Webmaster Verification
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label">Google Search Console ID</label>
                                                <input type="text" name="gmb_webmaster_google_verify" value="<?php echo esc_attr(get_option('gmb_webmaster_google_verify', '')); ?>" placeholder="e.g. 2_x1Y4b..." class="gmb-input" />
                                            </div>
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label">Bing Webmaster Tools ID</label>
                                                <input type="text" name="gmb_webmaster_bing_verify" value="<?php echo esc_attr(get_option('gmb_webmaster_bing_verify', '')); ?>" placeholder="e.g. 883A49..." class="gmb-input" />
                                            </div>
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label">Pinterest Verification ID</label>
                                                <input type="text" name="gmb_webmaster_pinterest_verify" value="<?php echo esc_attr(get_option('gmb_webmaster_pinterest_verify', '')); ?>" placeholder="e.g. 9b8c7..." class="gmb-input" />
                                            </div>
                                            <div class="gmb-webmaster-grid">
                                                <div class="gmb-flex-1">
                                                    <label class="gmb-form-label gmb-text-xs">Baidu Verification</label>
                                                    <input type="text" name="gmb_webmaster_baidu_verify" value="<?php echo esc_attr(get_option('gmb_webmaster_baidu_verify', '')); ?>" placeholder="Baidu code" class="gmb-input gmb-input--small" />
                                                </div>
                                                <div class="gmb-flex-1">
                                                    <label class="gmb-form-label gmb-text-xs">Yandex Verification</label>
                                                    <input type="text" name="gmb_webmaster_yandex_verify" value="<?php echo esc_attr(get_option('gmb_webmaster_yandex_verify', '')); ?>" placeholder="Yandex code" class="gmb-input gmb-input--small" />
                                                </div>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enter your verification IDs/tokens to automatically verify your site ownership in search engine webmaster tools.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Section: RSS Feed Scraper Protection -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            RSS Feed Protection
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label">RSS Content After Each Post</label>
                                                <textarea name="gmb_rss_after_content" rows="3" placeholder="The post %title% appeared first on %sitename% (%siteurl%)." class="gmb-textarea"><?php echo esc_textarea(get_option('gmb_rss_after_content', '')); ?></textarea>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically adds dynamic copyright and backlinks to RSS feed entries to protect against content scraper bots.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer">
                                    <button type="button" class="button gmb-btn--ghost" id="gmb-reset-metadata-options">Reset Options</button>
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                </div>
                            </div>

                            <!-- Subtab: Local Info -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'local') ? 'active' : ''; ?>" id="gmb-subtab-local">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Local Business Info</h2>
                                    <p class="gmb-text-muted">Configure your local business schema data and physical storefront parameters. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <!-- Option: Multiple Locations -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        Multiple Locations
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_local_use_multiple_locations" value="1" id="gmb-toggle-multi-locations" <?php checked('1', $use_multiple); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help">
                                            Enable if you run multiple storefronts or service offices. Adds support for adding individual location details.
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Single Location Form Panel -->
                                <div id="gmb-single-location-panel" class="<?php echo $use_multiple === '1' ? 'gmb-hidden' : ''; ?>">
                                    <?php
                                    $local_type = get_option('gmb_local_seo_type', 'organization');
                                    $local_subtype = get_option('gmb_local_seo_business_subtype', 'LocalBusiness');
                                    $local_web_name = get_option('gmb_local_seo_website_name', get_bloginfo('name'));
                                    $local_web_alt = get_option('gmb_local_seo_website_alternate_name', '');
                                    $local_seo_name = get_option('gmb_local_seo_name', get_option('gmb_local_business_name', get_bloginfo('name')));
                                    $local_logo = get_option('gmb_local_seo_logo', '');
                                    $local_url = get_option('gmb_local_seo_url', home_url());
                                    $local_email = get_option('gmb_local_seo_email', get_bloginfo('admin_email'));
                                    $local_phone = get_option('gmb_local_seo_phone', get_option('gmb_local_business_phone', ''));

                                    $addr_street = get_option('gmb_local_seo_address_street', get_option('gmb_local_business_address', ''));
                                    $addr_locality = get_option('gmb_local_seo_address_locality', '');
                                    $addr_region = get_option('gmb_local_seo_address_region', '');
                                    $addr_postal = get_option('gmb_local_seo_address_postal', '');
                                    $addr_country = get_option('gmb_local_seo_address_country', '');

                                    $geo_lat = get_option('gmb_local_business_lat', '');
                                    $geo_lng = get_option('gmb_local_business_lng', '');
                                    $maps_url = get_option('gmb_local_business_maps_url', '');
                                    $price_range = get_option('gmb_local_business_price_range', '');
                                    $currencies = get_option('gmb_local_business_currencies', '');
                                    $opening_hours = get_option('gmb_local_business_opening_hours', '');

                                    $about_page = get_option('gmb_local_seo_about_page', '');
                                    $contact_page = get_option('gmb_local_seo_contact_page', '');

                                    $pages = get_pages();
                                    ?>
                                    <!-- Person or Company -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Person or Company
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm">
                                                <label class="gmb-type-btn <?php echo ($local_type === 'person') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_local_seo_type" value="person" <?php checked($local_type, 'person'); ?> class="gmb-hidden" onchange="this.closest('.gmb-type-selector').querySelectorAll('.gmb-type-btn').forEach(b => b.classList.remove('active')); this.parentNode.classList.add('active'); const st = document.getElementById('gmb-business-subtype-row'); if(st) st.classList.add('gmb-hidden');" />
                                                    Person
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($local_type === 'organization') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_local_seo_type" value="organization" <?php checked($local_type, 'organization'); ?> class="gmb-hidden" onchange="this.closest('.gmb-type-selector').querySelectorAll('.gmb-type-btn').forEach(b => b.classList.remove('active')); this.parentNode.classList.add('active'); const st = document.getElementById('gmb-business-subtype-row'); if(st) st.classList.remove('gmb-hidden');" />
                                                    Organization
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Choose whether the site represents a person or an organization.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Business Type Subtype (Visible when Organization) -->
                                    <div id="gmb-business-subtype-row" class="gmb-settings-row <?php echo ($local_type === 'person') ? 'gmb-hidden' : ''; ?>">
                                        <div class="gmb-settings-label-col">
                                            Business Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_local_seo_business_subtype" class="gmb-select gmb-input--max-480">
                                                <option value="LocalBusiness" <?php selected($local_subtype, 'LocalBusiness'); ?>>LocalBusiness (General)</option>
                                                <optgroup label="Medical & Healthcare">
                                                    <option value="MedicalBusiness" <?php selected($local_subtype, 'MedicalBusiness'); ?>>MedicalBusiness (Healthcare & Medical)</option>
                                                    <option value="Physician" <?php selected($local_subtype, 'Physician'); ?>>Physician / Doctor Practice</option>
                                                    <option value="MedicalClinic" <?php selected($local_subtype, 'MedicalClinic'); ?>>MedicalClinic (Clinic / Care Center)</option>
                                                    <option value="Hospital" <?php selected($local_subtype, 'Hospital'); ?>>Hospital</option>
                                                    <option value="Pharmacy" <?php selected($local_subtype, 'Pharmacy'); ?>>Pharmacy</option>
                                                </optgroup>
                                                <optgroup label="Professional & Services">
                                                    <option value="ProfessionalService" <?php selected($local_subtype, 'ProfessionalService'); ?>>ProfessionalService (Agency / Consulting)</option>
                                                    <option value="LegalService" <?php selected($local_subtype, 'LegalService'); ?>>LegalService (Lawyer / Attorney)</option>
                                                    <option value="AccountingService" <?php selected($local_subtype, 'AccountingService'); ?>>AccountingService (Accounting / CPA)</option>
                                                    <option value="FinancialService" <?php selected($local_subtype, 'FinancialService'); ?>>FinancialService (Financial / Banking)</option>
                                                    <option value="RealEstateAgent" <?php selected($local_subtype, 'RealEstateAgent'); ?>>RealEstateAgent (Real Estate Agency)</option>
                                                    <option value="TravelAgency" <?php selected($local_subtype, 'TravelAgency'); ?>>TravelAgency (Tours & Travel)</option>
                                                </optgroup>
                                                <optgroup label="Home & Construction">
                                                    <option value="HomeAndConstructionBusiness" <?php selected($local_subtype, 'HomeAndConstructionBusiness'); ?>>HomeAndConstructionBusiness (Home Services)</option>
                                                    <option value="GeneralContractor" <?php selected($local_subtype, 'GeneralContractor'); ?>>GeneralContractor (Construction / Builder)</option>
                                                    <option value="Electrician" <?php selected($local_subtype, 'Electrician'); ?>>Electrician</option>
                                                    <option value="Plumber" <?php selected($local_subtype, 'Plumber'); ?>>Plumber</option>
                                                </optgroup>
                                                <optgroup label="Store & Dining">
                                                    <option value="Store" <?php selected($local_subtype, 'Store'); ?>>Store (Retail / Shopping)</option>
                                                    <option value="FoodEstablishment" <?php selected($local_subtype, 'FoodEstablishment'); ?>>FoodEstablishment (Dining & Catering)</option>
                                                    <option value="Restaurant" <?php selected($local_subtype, 'Restaurant'); ?>>Restaurant</option>
                                                    <option value="CafeOrCoffeeShop" <?php selected($local_subtype, 'CafeOrCoffeeShop'); ?>>CafeOrCoffeeShop</option>
                                                </optgroup>
                                                <optgroup label="Automotive & Other">
                                                    <option value="AutomotiveBusiness" <?php selected($local_subtype, 'AutomotiveBusiness'); ?>>AutomotiveBusiness (Car Dealership / Auto)</option>
                                                    <option value="AutoRepair" <?php selected($local_subtype, 'AutoRepair'); ?>>AutoRepair</option>
                                                    <option value="HealthAndBeautyBusiness" <?php selected($local_subtype, 'HealthAndBeautyBusiness'); ?>>HealthAndBeautyBusiness (Salon / Spa)</option>
                                                    <option value="EducationalOrganization" <?php selected($local_subtype, 'EducationalOrganization'); ?>>EducationalOrganization (School / Academy)</option>
                                                </optgroup>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select your industry's specific Schema.org LocalBusiness subtype for targeted Google rich snippet rankings.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Website Name -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Website Name
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_local_seo_website_name" value="<?php echo esc_attr($local_web_name); ?>" class="gmb-input gmb-input--max-480" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                            <p class="gmb-form-help">
                                                Enter the name of your site to appear in search results.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Website Alternate Name -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Website Alternate Name
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_local_seo_website_alternate_name" value="<?php echo esc_attr($local_web_alt); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. CNN" />
                                            <p class="gmb-form-help">
                                                An alternate version of your site name (for example, an acronym or shorter name).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Person/Organization Name -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Person/Organization Name
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_local_business_name" name="gmb_local_seo_name" value="<?php echo esc_attr($local_seo_name); ?>" class="gmb-input gmb-input--max-480" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" oninput="document.getElementsByName('gmb_local_business_name')[0].value = this.value;" />
                                            <input type="hidden" name="gmb_local_business_name" value="<?php echo esc_attr($local_seo_name); ?>" />
                                            <p class="gmb-form-help">
                                                Your name or company name intended to feature in Google's Knowledge Panel.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Logo -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Logo
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-flex-center-gap-sm gmb-input--max-480">
                                                <input type="text" id="gmb_local_seo_logo" name="gmb_local_seo_logo" value="<?php echo esc_attr($local_logo); ?>" class="gmb-input gmb-flex-1" placeholder="No logo selected..." />
                                                <button type="button" class="button gmb-media-upload-btn" data-target="gmb_local_seo_logo">Select Logo</button>
                                            </div>
                                            <div id="gmb_local_seo_logo_preview" class="gmb-schema-logo-preview-wrap <?php echo !empty($local_logo) ? '' : 'gmb-hidden'; ?>">
                                                <img src="<?php echo esc_url($local_logo); ?>" class="gmb-schema-logo-preview" />
                                            </div>
                                            <p class="gmb-form-help">
                                                Min Size: 112x112px. A squared image is preferred by search engines.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="url" name="gmb_local_seo_url" value="<?php echo esc_url($local_url); ?>" class="gmb-input gmb-input--max-480" placeholder="<?php echo esc_url(home_url()); ?>" />
                                            <p class="gmb-form-help">
                                                URL of the business homepage.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Email
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_local_seo_email" value="<?php echo esc_attr($local_email); ?>" class="gmb-input gmb-input--max-480" placeholder="<?php echo esc_attr(get_bloginfo('admin_email')); ?>" />
                                            <p class="gmb-form-help">
                                                Enter the contact email address that could be displayed on search engines.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Phone
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_local_business_phone" name="gmb_local_seo_phone" value="<?php echo esc_attr($local_phone); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. +1 555-0199" oninput="document.getElementsByName('gmb_local_business_phone')[0].value = this.value;" />
                                            <input type="hidden" name="gmb_local_business_phone" value="<?php echo esc_attr($local_phone); ?>" />
                                            <p class="gmb-form-help">
                                                Search engines may prominently display your contact phone number for mobile users.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Address Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Address
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <input type="text" id="gmb_local_business_address" name="gmb_local_seo_address_street" value="<?php echo esc_attr($addr_street); ?>" placeholder="Street Address" class="gmb-input gmb-mb-8" oninput="document.getElementsByName('gmb_local_business_address')[0].value = this.value;" />
                                            <input type="hidden" name="gmb_local_business_address" value="<?php echo esc_attr($addr_street); ?>" />
                                            <input type="text" name="gmb_local_seo_address_locality" value="<?php echo esc_attr($addr_locality); ?>" placeholder="Locality / City" class="gmb-input gmb-mb-8" />
                                            <input type="text" name="gmb_local_seo_address_region" value="<?php echo esc_attr($addr_region); ?>" placeholder="Region / State" class="gmb-input gmb-mb-8" />
                                            <input type="text" name="gmb_local_seo_address_postal" value="<?php echo esc_attr($addr_postal); ?>" placeholder="Postal Code" class="gmb-input gmb-mb-8" />
                                            <input type="text" name="gmb_local_seo_address_country" value="<?php echo esc_attr($addr_country); ?>" placeholder="2-letter Country Code (e.g. US, UK, CA)" class="gmb-input" />
                                        </div>
                                    </div>

                                    <!-- Geo Coordinates & Google Maps Link -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Geo Coordinates & Maps
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <div class="gmb-flex-gap-sm gmb-mb-8">
                                                <input type="text" name="gmb_local_business_lat" value="<?php echo esc_attr($geo_lat); ?>" placeholder="Latitude (e.g. 40.7128)" class="gmb-input gmb-flex-1" />
                                                <input type="text" name="gmb_local_business_lng" value="<?php echo esc_attr($geo_lng); ?>" placeholder="Longitude (e.g. -74.0060)" class="gmb-input gmb-flex-1" />
                                            </div>
                                            <input type="url" name="gmb_local_business_maps_url" value="<?php echo esc_url($maps_url); ?>" placeholder="Google Maps URL or CID Place Link" class="gmb-input" />
                                            <p class="gmb-form-help">
                                                Geo coordinates (latitude & longitude) and Google Maps URL power local knowledge cards and map rankings.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Opening Hours & Price Range -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Opening Hours & Pricing
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <input type="text" name="gmb_local_business_opening_hours" value="<?php echo esc_attr($opening_hours); ?>" placeholder="e.g. Mo-Fr 09:00-18:00, Sa 10:00-14:00 (or 24/7)" class="gmb-input gmb-mb-8" />
                                            <div class="gmb-flex-gap-sm">
                                                <select name="gmb_local_business_price_range" class="gmb-select gmb-flex-1">
                                                    <option value=""><?php esc_html_e('- Price Range -', 'gmb-ranker-seo-automation'); ?></option>
                                                    <option value="$" <?php selected($price_range, '$'); ?>>$ (Inexpensive / Budget)</option>
                                                    <option value="$$" <?php selected($price_range, '$$'); ?>>$$ (Moderate)</option>
                                                    <option value="$$$" <?php selected($price_range, '$$$'); ?>>$$$ (Expensive)</option>
                                                    <option value="$$$$" <?php selected($price_range, '$$$$'); ?>>$$$$ (Luxury / Premium)</option>
                                                </select>
                                                <input type="text" name="gmb_local_business_currencies" value="<?php echo esc_attr($currencies); ?>" placeholder="Currencies (e.g. NPR, USD)" class="gmb-input gmb-flex-1" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- About Page -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            About Page
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_local_seo_about_page" class="gmb-select gmb-input--max-480">
                                                <option value=""><?php esc_html_e('- Select Page -', 'gmb-ranker-seo-automation'); ?></option>
                                                <?php foreach ($pages as $p) : ?>
                                                    <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($about_page, $p->ID); ?>><?php echo esc_html($p->post_title); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select a page on your site where you want to show the LocalBusiness meta data.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Contact Page -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Contact Page
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_local_seo_contact_page" class="gmb-select gmb-input--max-480">
                                                <option value=""><?php esc_html_e('- Select Page -', 'gmb-ranker-seo-automation'); ?></option>
                                                <?php foreach ($pages as $p) : ?>
                                                    <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($contact_page, $p->ID); ?>><?php echo esc_html($p->post_title); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select a page on your site where you want to show the LocalBusiness meta data.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multiple Locations Form Panel -->
                                <div id="gmb-multiple-locations-panel" class="<?php echo $use_multiple === '1' ? '' : 'gmb-hidden'; ?>">
                                    <!-- Add Location Form -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add Location
                                        </div>
                                        <div class="gmb-add-loc-card">
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label gmb-text-xs">Location Name</label>
                                                <input type="text" id="gmb-new-loc-name" class="gmb-input" placeholder="Branch/City Name" />
                                            </div>
                                            <div class="gmb-mb-12">
                                                <label class="gmb-form-label gmb-text-xs">Phone Number</label>
                                                <input type="text" id="gmb-new-loc-phone" class="gmb-input" placeholder="e.g. +1 555-123-4567" />
                                            </div>
                                            <div class="gmb-mb-16">
                                                <label class="gmb-form-label gmb-text-xs">Street Address</label>
                                                <textarea id="gmb-new-loc-address" rows="2" class="gmb-textarea" placeholder="Full street details"></textarea>
                                            </div>
                                            <div class="gmb-text-right">
                                                <button type="button" class="button button-primary gmb-btn--primary" id="gmb-add-loc-btn">Save Location</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Active Locations -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Active Locations
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <div id="gmb-locations-list-container">
                                                <?php if (!empty($locations)) : ?>
                                                    <table class="gmb-locations-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Phone</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($locations as $loc) : ?>
                                                                <tr class="gmb-border-bottom">
                                                                    <td class="gmb-font-semibold"><?php echo esc_html($loc['name']); ?></td>
                                                                    <td class="gmb-loc-phone"><?php echo esc_html($loc['phone']); ?></td>
                                                                    <td class="gmb-loc-action">
                                                                        <button type="button" class="gmb-delete-loc-btn" data-id="<?php echo esc_attr($loc['id']); ?>">Delete</button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                <?php else : ?>
                                                    <p class="gmb-locations-empty">No additional business locations defined yet.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                </div>
                            </div>

                            <!-- Subtab: Social Meta -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'social') ? 'active' : ''; ?>" id="gmb-subtab-social">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Social Meta</h2>
                                    <p class="gmb-text-muted">Add social account information to your website's Schema, Knowledge Graph, and Open Graph. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $fb_page = get_option('gmb_social_facebook_page_url', '');
                                    $fb_author = get_option('gmb_social_facebook_authorship', '');
                                    $fb_admin = get_option('gmb_social_facebook_admin', '');
                                    $fb_app = get_option('gmb_social_facebook_app_id', '');
                                    $fb_secret = get_option('gmb_social_facebook_secret', '');
                                    $tw_user = get_option('gmb_social_twitter_username', '');
                                    $ig_url = get_option('gmb_social_instagram_url', '');
                                    $li_url = get_option('gmb_social_linkedin_url', '');
                                    $yt_url = get_option('gmb_social_youtube_url', '');
                                    $pin_url = get_option('gmb_social_pinterest_url', '');
                                    $tt_url = get_option('gmb_social_tiktok_url', '');
                                    $wiki_url = get_option('gmb_social_wikipedia_url', '');
                                    $add_profiles = get_option('gmb_social_additional_profiles', '');
                                    $default_social_img = get_option('gmb_metadata_og_thumbnail', '');
                                    ?>

                                    <!-- Default Social Share Image -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Default Social Share Image
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-flex-center-gap-sm gmb-input--max-480">
                                                <input type="text" id="gmb_social_default_img" name="gmb_metadata_og_thumbnail" value="<?php echo esc_attr($default_social_img); ?>" class="gmb-input gmb-flex-1" placeholder="No image selected..." />
                                                <button type="button" class="button gmb-media-upload-btn" data-target="gmb_social_default_img">Select Image</button>
                                            </div>
                                            <div id="gmb_social_default_img_preview" class="gmb-thumb-preview <?php echo empty($default_social_img) ? 'gmb-hidden' : ''; ?>">
                                                <img src="<?php echo esc_url($default_social_img); ?>" alt="Social Preview" />
                                            </div>
                                            <p class="gmb-form-help">
                                                Fallback image used when a post or page is shared on Facebook, Twitter/X, LinkedIn, or Pinterest without a featured image. Recommended: 1200x630px.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Facebook Page URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Facebook Page URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_facebook_page_url" value="<?php echo esc_attr($fb_page); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.facebook.com/BrandName/" />
                                            <p class="gmb-form-help">
                                                Enter your complete Facebook business page URL.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Twitter / X Username -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter / X Username
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_twitter_username" value="<?php echo esc_attr($tw_user); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. @MyBrand or MyBrand" />
                                            <p class="gmb-form-help">
                                                Enter your Twitter/X username to generate <code>twitter:site</code> and <code>twitter:creator</code> tags.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Instagram URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Instagram URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_instagram_url" value="<?php echo esc_attr($ig_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.instagram.com/BrandName/" />
                                            <p class="gmb-form-help">
                                                Enter your Instagram profile URL for Knowledge Graph Schema <code>sameAs</code> attribution.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- LinkedIn URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            LinkedIn URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_linkedin_url" value="<?php echo esc_attr($li_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.linkedin.com/company/BrandName/" />
                                            <p class="gmb-form-help">
                                                Enter your LinkedIn company or personal profile URL.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- YouTube URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            YouTube Channel URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_youtube_url" value="<?php echo esc_attr($yt_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.youtube.com/@ChannelName" />
                                            <p class="gmb-form-help">
                                                Enter your YouTube channel or user URL.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Pinterest URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Pinterest URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_pinterest_url" value="<?php echo esc_attr($pin_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.pinterest.com/BrandName/" />
                                            <p class="gmb-form-help">
                                                Enter your Pinterest business profile URL.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- TikTok URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            TikTok URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_tiktok_url" value="<?php echo esc_attr($tt_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://www.tiktok.com/@BrandName" />
                                            <p class="gmb-form-help">
                                                Enter your TikTok account URL.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Wikipedia / Wikidata URL -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Wikipedia / Wikidata URL
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_social_wikipedia_url" value="<?php echo esc_attr($wiki_url); ?>" class="gmb-input gmb-input--max-480" placeholder="e.g. https://en.wikipedia.org/wiki/BrandName" />
                                            <p class="gmb-form-help">
                                                Wikipedia or Wikidata entity URL for verified Google Knowledge Panel recognition.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Additional Profiles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Additional Profiles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_social_additional_profiles" rows="3" class="gmb-input gmb-input--max-480" placeholder="One URL per line (e.g. GitHub, Soundcloud, Medium profiles)"><?php echo esc_textarea($add_profiles); ?></textarea>
                                            <p class="gmb-form-help">
                                                Any additional URLs to include in the <code>sameAs</code> Schema property list.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Facebook Authorship & Advanced IDs -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Facebook Advanced
                                        </div>
                                        <div class="gmb-settings-input-col gmb-input--max-480">
                                            <div>
                                                <label class="gmb-form-label gmb-text-xs">Facebook Authorship Profile URL</label>
                                                <input type="text" name="gmb_social_facebook_authorship" value="<?php echo esc_attr($fb_author); ?>" class="gmb-input" placeholder="e.g. https://www.facebook.com/username/" />
                                            </div>
                                            <div class="gmb-flex-gap-sm">
                                                <div class="gmb-flex-1">
                                                    <label class="gmb-form-label gmb-text-xs">Facebook Admin ID(s)</label>
                                                    <input type="text" name="gmb_social_facebook_admin" value="<?php echo esc_attr($fb_admin); ?>" class="gmb-input" placeholder="e.g. 123456789" />
                                                </div>
                                                <div class="gmb-flex-1">
                                                    <label class="gmb-form-label gmb-text-xs">Facebook App ID</label>
                                                    <input type="text" name="gmb_social_facebook_app_id" value="<?php echo esc_attr($fb_app); ?>" class="gmb-input" placeholder="e.g. 123456789012345" />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="gmb-form-label gmb-text-xs">Facebook App Secret</label>
                                                <input type="password" name="gmb_social_facebook_secret" value="<?php echo esc_attr($fb_secret); ?>" class="gmb-input" placeholder="••••••••••••••••" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Homepage -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'homepage') ? 'active' : ''; ?>" id="gmb-subtab-homepage">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Homepage Settings</h2>
                                    <p class="gmb-text-muted">Configure homepage title template, description template, social cards, and search engine robots meta options. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>

                                <?php
                                $show_on_front = get_option('show_on_front', 'posts');
                                $page_on_front = get_option('page_on_front', 0);
                                if ($show_on_front === 'page' && !empty($page_on_front) && get_post($page_on_front)) :
                                ?>
                                    <div class="gmb-callout gmb-callout--info">
                                        <div class="gmb-flex-center-gap-md">
                                            <span class="gmb-callout-icon gmb-callout-icon--blue">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            </span>
                                            <div>
                                                <div class="gmb-callout-title">Static Homepage Active: "<?php echo esc_html(get_the_title($page_on_front)); ?>"</div>
                                                <div class="gmb-callout-desc">Your site uses a static front page. You can edit its title, description, and schema directly in the page editor, or manage global fallback templates below.</div>
                                            </div>
                                        </div>
                                        <a href="<?php echo esc_url(get_edit_post_link($page_on_front)); ?>" target="_blank" class="button gmb-callout-btn">Edit Homepage &rarr;</a>
                                    </div>
                                <?php else : ?>
                                    <div class="gmb-callout gmb-callout--subtle">
                                        <span class="gmb-callout-icon gmb-callout-icon--indigo">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#466afa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path><path d="M18 14h-8"></path><path d="M15 18h-5"></path><path d="M10 6h8v4h-8V6Z"></path></svg>
                                        </span>
                                        <div class="gmb-callout-desc--subtle">
                                            <strong>Latest Posts Mode:</strong> Your homepage displays latest blog posts. The metadata templates below apply directly to your main front page URL.
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $homepage_title = get_option('gmb_homepage_title_template', '%sitename% %sep% %sitedesc%');
                                    $homepage_desc = get_option('gmb_homepage_desc_template', '%sitedesc%');
                                    $homepage_robots_enable = get_option('gmb_homepage_robots_meta_enable', '0');
                                    $homepage_robots = get_option('gmb_homepage_robots_meta', '');
                                    $homepage_robots_array = is_array($homepage_robots) ? $homepage_robots : array_map('trim', explode(',', strtolower($homepage_robots)));
                                    $homepage_max_snippet = get_option('gmb_homepage_advanced_max_snippet', '-1');
                                    $homepage_max_video = get_option('gmb_homepage_advanced_max_video', '-1');
                                    $homepage_max_image = get_option('gmb_homepage_advanced_max_image', 'large');
                                    $homepage_fb_title = get_option('gmb_homepage_facebook_title', '');
                                    $homepage_fb_desc = get_option('gmb_homepage_facebook_desc', '');
                                    $homepage_fb_image = get_option('gmb_homepage_facebook_image', '');
                                    $homepage_tw_card = get_option('gmb_homepage_twitter_card_type', 'summary_large_image');
                                    ?>
                                    
                                    <!-- Homepage Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_homepage_title_template" id="gmb_homepage_title_template" value="<?php echo esc_attr($homepage_title); ?>" class="gmb-input gmb-input--max-480" placeholder="%sitename% %sep% %sitedesc%" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_title_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_title_template" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_title_template" data-tag="%sitedesc%" >+ %sitedesc%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_title_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_title_template" data-tag="%currentmonth%" >+ %currentmonth%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title tag template for the homepage. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Meta Description -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Meta Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_homepage_desc_template" id="gmb_homepage_desc_template" rows="3" class="gmb-input gmb-input--max-480" placeholder="%sitedesc%"><?php echo esc_textarea($homepage_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_desc_template" data-tag="%sitedesc%" >+ %sitedesc%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_desc_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_homepage_desc_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Meta description template for the homepage. Recommended length: 120–160 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_homepage_robots_meta_enable" value="1" id="gmb-toggle-homepage-robots" <?php checked('1', $homepage_robots_enable); ?> onchange="document.getElementById('gmb-homepage-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Select custom robots meta for homepage. Otherwise the default meta will be used, as set in the Global Meta tab.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Robots Meta Checkboxes -->
                                    <div id="gmb-homepage-robots-checkboxes" class="gmb-robots-box <?php echo ($homepage_robots_enable === '1') ? '' : 'gmb-hidden'; ?>">
                                        <div class="gmb-settings-row gmb-settings-row--noborder">
                                            <div class="gmb-settings-label-col">
                                                Homepage Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="index" <?php checked(in_array('index', $homepage_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $homepage_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $homepage_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $homepage_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $homepage_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_homepage_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $homepage_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Directives for Homepage -->
                                        <div class="gmb-directives-divider">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots Directives
                                            </div>
                                            <div class="gmb-directives-container">
                                                <div class="gmb-flex-between">
                                                    <label class="gmb-form-label">Max Snippet (characters):</label>
                                                    <input type="number" name="gmb_homepage_advanced_max_snippet" value="<?php echo esc_attr($homepage_max_snippet); ?>" class="gmb-input gmb-input--w-100" />
                                                </div>
                                                <div class="gmb-flex-between">
                                                    <label class="gmb-form-label">Max Video Preview (seconds):</label>
                                                    <input type="number" name="gmb_homepage_advanced_max_video" value="<?php echo esc_attr($homepage_max_video); ?>" class="gmb-input gmb-input--w-100" />
                                                </div>
                                                <div class="gmb-flex-between">
                                                    <label class="gmb-form-label">Max Image Preview:</label>
                                                    <select name="gmb_homepage_advanced_max_image" class="gmb-select gmb-input--w-120">
                                                        <option value="large" <?php selected('large', $homepage_max_image); ?>>Large</option>
                                                        <option value="standard" <?php selected('standard', $homepage_max_image); ?>>Standard</option>
                                                        <option value="none" <?php selected('none', $homepage_max_image); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Facebook Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Facebook Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_homepage_facebook_title" value="<?php echo esc_attr($homepage_fb_title); ?>" class="gmb-input gmb-input--max-480" placeholder="Leave empty to use homepage title template" />
                                            <p class="gmb-form-help">
                                                Title shared on Facebook & OpenGraph social platforms.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Facebook Description -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Facebook Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_homepage_facebook_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="Leave empty to use homepage meta description template"><?php echo esc_textarea($homepage_fb_desc); ?></textarea>
                                            <p class="gmb-form-help">
                                                Description shared on Facebook & OpenGraph social platforms.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Homepage Social Share Image -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Homepage Social Image
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-flex-center-gap-sm gmb-input--max-480">
                                                <input type="text" name="gmb_homepage_facebook_image" id="gmb_homepage_facebook_image" value="<?php echo esc_attr($homepage_fb_image); ?>" class="gmb-input gmb-flex-1" placeholder="No image selected..." />
                                                <button type="button" class="button gmb-media-upload-btn" data-target="gmb_homepage_facebook_image">Select Image</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Image shared when the homepage URL is shared on Facebook or Twitter. Recommended size: 1200 x 630 pixels.
                                            </p>
                                            <div id="gmb_homepage_facebook_image_preview" class="gmb-preview-container <?php echo !empty($homepage_fb_image) ? '' : 'gmb-hidden'; ?>">
                                                <?php if (!empty($homepage_fb_image)) : ?>
                                                    <img src="<?php echo esc_url($homepage_fb_image); ?>" class="gmb-preview-img-box" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Homepage Twitter Card Type -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_homepage_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="summary_large_image" <?php selected('summary_large_image', $homepage_tw_card); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected('summary', $homepage_tw_card); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default card layout when homepage URL is shared on Twitter/X.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Authors -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'authors') ? 'active' : ''; ?>" id="gmb-subtab-authors">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Authors</h2>
                                    <p class="gmb-text-muted">Change SEO options related to author archive pages. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $author_archives = get_option('gmb_author_archives_enable', 'enabled');
                                    $author_base = get_option('gmb_author_base', 'author');
                                    $author_robots_enable = get_option('gmb_author_robots_meta_enable', '1');
                                    
                                    $author_robots = get_option('gmb_author_robots_meta', 'noindex');
                                    $author_robots_array = is_array($author_robots) ? $author_robots : array_map('trim', explode(',', strtolower($author_robots)));
                                    
                                    $author_max_snippet = get_option('gmb_author_advanced_max_snippet', '-1');
                                    $author_max_video = get_option('gmb_author_advanced_max_video', '-1');
                                    $author_max_image = get_option('gmb_author_advanced_max_image', 'large');
                                    $author_title = get_option('gmb_author_archive_title', '%name% %sep% %sitename% %page%');
                                    $author_desc = get_option('gmb_author_archive_desc', '');
                                    $author_slack = get_option('gmb_author_slack_sharing', '1');
                                    $author_controls = get_option('gmb_author_seo_controls', '1');
                                    ?>
                                    
                                    <!-- Author Archives Toggle -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Author Archives
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($author_archives === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_author_archives_enable" value="disabled" <?php checked($author_archives, 'disabled'); ?> onchange="document.getElementById('gmb-author-details-container').style.display='none';" />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($author_archives === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_author_archives_enable" value="enabled" <?php checked($author_archives, 'enabled'); ?> onchange="document.getElementById('gmb-author-details-container').style.display='block';" />
                                                    Enabled
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enables or disables Author Archives. If disabled, author archive URLs are automatically 301 redirected to your homepage.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div id="gmb-author-details-container" class="<?php echo ($author_archives === 'disabled') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Author Base -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Author Base
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_author_base" value="<?php echo esc_attr($author_base); ?>" class="gmb-input gmb-input--max-480" placeholder="author" />
                                                <p class="gmb-form-help">
                                                    Change the <code>/author/</code> slug in author profile URLs (e.g. <code>profile</code> or <code>user</code>).
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Author Robots Meta Enable -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Author Robots Meta
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <label class="gmb-switch">
                                                    <input type="checkbox" name="gmb_author_robots_meta_enable" value="1" id="gmb-toggle-author-robots" <?php checked('1', $author_robots_enable); ?> onchange="document.getElementById('gmb-author-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                    <span class="gmb-slider round"></span>
                                                </label>
                                                <p class="gmb-form-help">
                                                    Select custom robots meta for author pages. Otherwise global default meta will be used.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Author Robots Meta Checkboxes -->
                                        <div id="gmb-author-robots-checkboxes" class="gmb-robots-wrap <?php echo ($author_robots_enable === '1') ? 'is-active' : ''; ?>">
                                            <div class="gmb-robots-row">
                                                <div class="gmb-settings-label-col">
                                                    Author Robots Options
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-grid-2col-max480">
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="index" <?php checked(in_array('index', $author_robots_array)); ?> />
                                                            <strong>index</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $author_robots_array)); ?> />
                                                            <strong>noindex</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $author_robots_array)); ?> />
                                                            <strong>nofollow</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $author_robots_array)); ?> />
                                                            <strong>noarchive</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $author_robots_array)); ?> />
                                                            <strong>noimageindex</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_author_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $author_robots_array)); ?> />
                                                            <strong>nosnippet</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Advanced Directives for Authors -->
                                            <div class="gmb-advanced-robots-row">
                                                <div class="gmb-settings-label-col">
                                                    Advanced Robots Directives
                                                </div>
                                                <div class="gmb-advanced-robots-inputs">
                                                    <div class="gmb-flex-between">
                                                        <label class="gmb-form-label">Max Snippet (characters):</label>
                                                        <input type="number" name="gmb_author_advanced_max_snippet" value="<?php echo esc_attr($author_max_snippet); ?>" class="gmb-input-num-100" />
                                                    </div>
                                                    <div class="gmb-flex-between">
                                                        <label class="gmb-form-label">Max Video Preview (seconds):</label>
                                                        <input type="number" name="gmb_author_advanced_max_video" value="<?php echo esc_attr($author_max_video); ?>" class="gmb-input-num-100" />
                                                    </div>
                                                    <div class="gmb-flex-between">
                                                        <label class="gmb-form-label">Max Image Preview:</label>
                                                        <select name="gmb_author_advanced_max_image" class="gmb-select-120">
                                                            <option value="large" <?php selected('large', $author_max_image); ?>>Large</option>
                                                            <option value="standard" <?php selected('standard', $author_max_image); ?>>Standard</option>
                                                            <option value="none" <?php selected('none', $author_max_image); ?>>None</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Author Archive Title -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Author Archive Title
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_author_archive_title" id="gmb_author_archive_title" value="<?php echo esc_attr($author_title); ?>" class="gmb-input gmb-input--max-480" placeholder="%name% %sep% %sitename% %page%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_title" data-tag="%name%" >+ %name%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_title" data-tag="%sitename%" >+ %sitename%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_title" data-tag="%sep%" >+ %sep%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_title" data-tag="%page%" >+ %page%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_title" data-tag="%currentyear%" >+ %currentyear%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Title tag on author archives. Recommended length: 50–60 characters.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Author Archive Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Author Archive Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_author_archive_desc" id="gmb_author_archive_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%user_description%"><?php echo esc_textarea($author_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_desc" data-tag="%user_description%" >+ %user_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_desc" data-tag="%name%" >+ %name%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_author_archive_desc" data-tag="%currentyear%" >+ %currentyear%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Meta description tag on author archives. Recommended length: 120–160 characters.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Slack Enhanced Sharing -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Slack Enhanced Sharing
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <label class="gmb-switch">
                                                    <input type="checkbox" name="gmb_author_slack_sharing" value="1" <?php checked('1', $author_slack); ?> />
                                                    <span class="gmb-slider round"></span>
                                                </label>
                                                <p class="gmb-form-help">
                                                    Show author name and total published posts when author archive URL is shared on Slack.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Add SEO Controls -->
                                        <div class="gmb-settings-row gmb-settings-row--noborder">
                                            <div class="gmb-settings-label-col">
                                                Add SEO Controls
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <label class="gmb-switch">
                                                    <input type="checkbox" name="gmb_author_seo_controls" value="1" <?php checked('1', $author_controls); ?> />
                                                    <span class="gmb-slider round"></span>
                                                </label>
                                                <p class="gmb-form-help">
                                                    Add custom SEO meta box fields directly into WordPress User profile editor screens.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Misc Pages -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'misc') ? 'active' : ''; ?>" id="gmb-subtab-misc">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Misc Pages</h2>
                                    <p class="gmb-text-muted">Customize SEO meta settings for date archives, 404 pages, search results, pagination, and password-protected pages. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $misc_disable_date = get_option('gmb_misc_disable_date_archives', '1');
                                    $misc_date_title = get_option('gmb_misc_date_archive_title', '%date% %sep% %sitename% %page%');
                                    $misc_date_desc = get_option('gmb_misc_date_archive_desc', '');
                                    $misc_date_robots_enable = get_option('gmb_misc_date_robots_meta_enable', '1');
                                    $misc_date_robots = get_option('gmb_misc_date_robots_meta', 'noindex');
                                    $misc_date_robots_array = is_array($misc_date_robots) ? $misc_date_robots : array_map('trim', explode(',', strtolower($misc_date_robots)));

                                    $misc_404_title = get_option('gmb_misc_404_title', 'Page Not Found %sep% %sitename%');
                                    $misc_search_title = get_option('gmb_misc_search_title', '%search_query% %page% %sep% %sitename%');
                                    $misc_noindex_search = get_option('gmb_misc_noindex_search_results', '1');
                                    $misc_noindex_subpages = get_option('gmb_misc_noindex_subpages', '0');
                                    $misc_noindex_paginated = get_option('gmb_misc_noindex_paginated_single', '0');
                                    $misc_noindex_password = get_option('gmb_misc_noindex_password_protected', '0');
                                    ?>
                                    
                                    <!-- Disable Date Archives -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Disable Date Archives
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_misc_disable_date_archives" value="1" id="gmb-toggle-date-archives" <?php checked('1', $misc_disable_date); ?> onchange="document.getElementById('gmb-date-archives-details').style.display=this.checked ? 'none' : 'block';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable or disable date archives (e.g. <code>domain.com/2026/09/</code>). If enabled, date archives are automatically 301 redirected to your homepage to avoid duplicate content.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Date Archives Details Container (visible when Date Archives NOT disabled) -->
                                    <div id="gmb-date-archives-details" class="<?php echo ($misc_disable_date === '1') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Date Archive Title -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Date Archive Title
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_misc_date_archive_title" id="gmb_misc_date_archive_title" value="<?php echo esc_attr($misc_date_title); ?>" class="gmb-input gmb-input--max-480" placeholder="%date% %sep% %sitename% %page%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_title" data-tag="%date%" >+ %date%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_title" data-tag="%sitename%" >+ %sitename%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_title" data-tag="%sep%" >+ %sep%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_title" data-tag="%page%" >+ %page%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_title" data-tag="%currentyear%" >+ %currentyear%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Title tag on date archive pages. Recommended length: 50–60 characters.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Date Archive Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Date Archive Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_misc_date_archive_desc" id="gmb_misc_date_archive_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%date% archives on %sitename%"><?php echo esc_textarea($misc_date_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_desc" data-tag="%date%" >+ %date%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_date_archive_desc" data-tag="%currentyear%" >+ %currentyear%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Meta description tag on date archives. Recommended length: 120–160 characters.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Date Robots Meta -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Date Robots Meta
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <label class="gmb-switch">
                                                    <input type="checkbox" name="gmb_misc_date_robots_meta_enable" value="1" id="gmb-toggle-date-robots" <?php checked('1', $misc_date_robots_enable); ?> onchange="document.getElementById('gmb-date-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                    <span class="gmb-slider round"></span>
                                                </label>
                                                <p class="gmb-form-help">
                                                    Select custom robots meta for date archive pages.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Date Robots Meta Checkboxes -->
                                        <div id="gmb-date-robots-checkboxes" class="gmb-robots-wrap <?php echo ($misc_date_robots_enable === '1') ? 'is-active' : ''; ?>">
                                            <div class="gmb-robots-row">
                                                <div class="gmb-settings-label-col">
                                                    Date Robots Options
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-grid-2col-max480">
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="index" <?php checked(in_array('index', $misc_date_robots_array)); ?> />
                                                            <strong>index</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $misc_date_robots_array)); ?> />
                                                            <strong>noindex</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $misc_date_robots_array)); ?> />
                                                            <strong>nofollow</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $misc_date_robots_array)); ?> />
                                                            <strong>noarchive</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $misc_date_robots_array)); ?> />
                                                            <strong>noimageindex</strong>
                                                        </label>
                                                        <label class="gmb-checkbox-label">
                                                            <input type="checkbox" name="gmb_misc_date_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $misc_date_robots_array)); ?> />
                                                            <strong>nosnippet</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 404 Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            404 Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_misc_404_title" id="gmb_misc_404_title" value="<?php echo esc_attr($misc_404_title); ?>" class="gmb-input gmb-input--max-480" placeholder="Page Not Found %sep% %sitename%" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_404_title" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_404_title" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_404_title" data-tag="%sitedesc%" >+ %sitedesc%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_404_title" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title tag on 404 Not Found error pages. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Search Results Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Search Results Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" name="gmb_misc_search_title" id="gmb_misc_search_title" value="<?php echo esc_attr($misc_search_title); ?>" class="gmb-input gmb-input--max-480" placeholder="%search_query% %page% %sep% %sitename%" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_search_title" data-tag="%search_query%" >+ %search_query%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_search_title" data-tag="%page%" >+ %page%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_search_title" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_search_title" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_misc_search_title" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title tag on search results pages. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Noindex Search Results -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Noindex Search Results
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_misc_noindex_search_results" value="1" <?php checked('1', $misc_noindex_search); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Prevent internal search results pages from being indexed by search engines. Highly recommended to avoid thin content penalties.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Noindex Subpages -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Noindex Subpages
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_misc_noindex_subpages" value="1" <?php checked('1', $misc_noindex_subpages); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Prevent paginated subpages (<code>/page/2/</code>, <code>/page/3/</code>, etc.) of archives and blog lists from being indexed.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Noindex Paginated Single Pages -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Noindex Paginated Single Pages
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_misc_noindex_paginated_single" value="1" <?php checked('1', $misc_noindex_paginated); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Prevent paginated split pages of single posts and pages (e.g. using <code>&lt;!--nextpage--&gt;</code>) from being indexed beyond page 1.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Noindex Password Protected Pages -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Noindex Password Protected Pages
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_misc_noindex_password_protected" value="1" <?php checked('1', $misc_noindex_password); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Prevent password-protected posts and pages from being indexed by search engines.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Posts -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'posts') ? 'active' : ''; ?>" id="gmb-subtab-posts">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Posts Settings</h2>
                                    <p class="gmb-text-muted">Change global SEO, Schema, and meta settings for single posts. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $post_title_temp = get_option('gmb_metadata_post_title_template', '%title% %sep% %sitename%');
                                    $post_desc_temp = get_option('gmb_metadata_post_desc_template', '%excerpt%');
                                    $posts_schema_type = get_option('gmb_posts_schema_type', 'article');
                                    $posts_schema_headline = get_option('gmb_posts_schema_headline', '%seo_title%');
                                    $posts_schema_desc = get_option('gmb_posts_schema_desc', '%seo_description%');
                                    $posts_article_type = get_option('gmb_posts_article_type', 'blogpost');
                                    $posts_autodetect_video = get_option('gmb_posts_autodetect_video', '1');
                                    $posts_robots_enable = get_option('gmb_posts_robots_meta_enable', '0');
                                    $posts_robots = get_option('gmb_posts_robots_meta', '');
                                    $posts_robots_array = is_array($posts_robots) ? $posts_robots : array_map('trim', explode(',', strtolower($posts_robots)));
                                    $posts_advanced_snippet = get_option('gmb_posts_advanced_max_snippet', '-1');
                                    $posts_advanced_video = get_option('gmb_posts_advanced_max_video', '-1');
                                    $posts_advanced_image = get_option('gmb_posts_advanced_max_image', 'large');
                                    $posts_twitter_card = get_option('gmb_posts_twitter_card_type', '');
                                    $posts_link_suggestions = get_option('gmb_posts_link_suggestions', '1');
                                    $posts_link_suggestion_titles = get_option('gmb_posts_link_suggestion_titles', 'titles');
                                    $posts_primary_taxonomy = get_option('gmb_posts_primary_taxonomy', 'category');
                                    $posts_slack = get_option('gmb_posts_slack_sharing', '1');
                                    $posts_seo_controls = get_option('gmb_posts_seo_controls', '1');
                                    $posts_bulk_editing = get_option('gmb_posts_bulk_editing', 'enabled');
                                    $posts_custom_fields = get_option('gmb_posts_custom_fields', '');
                                    $posts_watermark = get_option('gmb_posts_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Single Post Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Single Post Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_posts_title_template_input" name="gmb_metadata_post_title_template" value="<?php echo esc_attr($post_title_temp); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%category%" >+ %category%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%author%" >+ %author%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_title_template_input" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default title tag for single Post pages. Recommended length: 50–60 characters. Can be customized per post.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Single Post Description -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Single Post Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_posts_desc_template_input" name="gmb_metadata_post_desc_template" rows="3" placeholder="%excerpt%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($post_desc_temp); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_desc_template_input" data-tag="%excerpt%" >+ %excerpt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_desc_template_input" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_desc_template_input" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_desc_template_input" data-tag="%focus_keyword%" >+ %focus_keyword%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_desc_template_input" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default description for single Post pages. Recommended length: 120–160 characters. Can be customized per post.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Schema Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Schema Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_posts_schema_type" id="gmb_posts_schema_type_select" class="gmb-select gmb-input--max-480" onchange="document.getElementById('gmb-posts-schema-fields').style.display = (this.value === 'none') ? 'none' : 'block';">
                                                <option value="none" <?php selected($posts_schema_type, 'none'); ?>>None</option>
                                                <option value="article" <?php selected($posts_schema_type, 'article'); ?>>Article (Default)</option>
                                                <option value="book" <?php selected($posts_schema_type, 'book'); ?>>Book</option>
                                                <option value="course" <?php selected($posts_schema_type, 'course'); ?>>Course</option>
                                                <option value="event" <?php selected($posts_schema_type, 'event'); ?>>Event</option>
                                                <option value="jobposting" <?php selected($posts_schema_type, 'jobposting'); ?>>Job Posting</option>
                                                <option value="movie" <?php selected($posts_schema_type, 'movie'); ?>>Movie</option>
                                                <option value="music" <?php selected($posts_schema_type, 'music'); ?>>Music</option>
                                                <option value="product" <?php selected($posts_schema_type, 'product'); ?>>Product</option>
                                                <option value="recipe" <?php selected($posts_schema_type, 'recipe'); ?>>Recipe</option>
                                                <option value="restaurant" <?php selected($posts_schema_type, 'restaurant'); ?>>Restaurant</option>
                                                <option value="service" <?php selected($posts_schema_type, 'service'); ?>>Service</option>
                                                <option value="software" <?php selected($posts_schema_type, 'software'); ?>>Software Application</option>
                                                <option value="video" <?php selected($posts_schema_type, 'video'); ?>>Video</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default rich snippet Schema type selected when creating a new post.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Schema Sub-Fields Container -->
                                    <div id="gmb-posts-schema-fields" class="<?php echo ($posts_schema_type === 'none') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Headline -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Headline
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_posts_schema_headline" id="gmb_posts_schema_headline" value="<?php echo esc_attr($posts_schema_headline); ?>" class="gmb-input gmb-input--max-480" placeholder="%seo_title%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_headline" data-tag="%seo_title%" >+ %seo_title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_headline" data-tag="%title%" >+ %title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_headline" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Headline of the article schema metadata.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_posts_schema_desc" id="gmb_posts_schema_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%seo_description%"><?php echo esc_textarea($posts_schema_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_desc" data-tag="%seo_description%" >+ %seo_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_desc" data-tag="%excerpt%" >+ %excerpt%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_posts_schema_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Short description of the article schema metadata.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Article Type -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Article Type
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                    <label class="gmb-type-btn <?php echo ($posts_article_type === 'article') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_posts_article_type" value="article" <?php checked($posts_article_type, 'article'); ?>  />
                                                        Article
                                                    </label>
                                                    <label class="gmb-type-btn <?php echo ($posts_article_type === 'blogpost') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_posts_article_type" value="blogpost" <?php checked($posts_article_type, 'blogpost'); ?>  />
                                                        Blog Post
                                                    </label>
                                                    <label class="gmb-type-btn <?php echo ($posts_article_type === 'newsarticle') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_posts_article_type" value="newsarticle" <?php checked($posts_article_type, 'newsarticle'); ?>  />
                                                        News Article
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Autodetect Video -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Autodetect Video
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_posts_autodetect_video" value="1" <?php checked('1', $posts_autodetect_video); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Populate automatic Video Schema by auto-detecting YouTube, Vimeo, or HTML5 videos in the post content.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Post Robots Meta Enable -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Post Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_posts_robots_meta_enable" value="1" id="gmb-toggle-posts-robots" <?php checked('1', $posts_robots_enable); ?> onchange="document.getElementById('gmb-posts-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Select custom robots meta for single posts. Otherwise global default values will be applied.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Post Robots Meta Checkboxes & Advanced Directives -->
                                    <div id="gmb-posts-robots-checkboxes" class="gmb-robots-wrap <?php echo ($posts_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Post Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="index" <?php checked(in_array('index', $posts_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $posts_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $posts_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $posts_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $posts_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_posts_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $posts_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Posts -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_posts_advanced_max_snippet" value="<?php echo esc_attr($posts_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_posts_advanced_max_video" value="<?php echo esc_attr($posts_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_posts_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($posts_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($posts_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($posts_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_posts_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($posts_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($posts_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($posts_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for single posts when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_posts_link_suggestions" value="1" <?php checked('1', $posts_link_suggestions); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable Link Suggestions metabox for this post type to discover internal link opportunities.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestion Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestion Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($posts_link_suggestion_titles === 'titles') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_link_suggestion_titles" value="titles" <?php checked($posts_link_suggestion_titles, 'titles'); ?>  />
                                                    Titles
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($posts_link_suggestion_titles === 'focus') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_link_suggestion_titles" value="focus" <?php checked($posts_link_suggestion_titles, 'focus'); ?>  />
                                                    Focus Keywords
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Primary Taxonomy -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Primary Taxonomy
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_posts_primary_taxonomy" class="gmb-select gmb-input--max-480">
                                                <option value="category" <?php selected($posts_primary_taxonomy, 'category'); ?>>Categories</option>
                                                <option value="post_tag" <?php selected($posts_primary_taxonomy, 'post_tag'); ?>>Tags</option>
                                                <option value="none" <?php selected($posts_primary_taxonomy, 'none'); ?>>None</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_posts_slack_sharing" value="1" <?php checked('1', $posts_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Show author name and estimated reading time when post URL is shared on Slack.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_posts_seo_controls" value="1" <?php checked('1', $posts_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to single post editor screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($posts_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_bulk_editing" value="disabled" <?php checked($posts_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($posts_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_bulk_editing" value="enabled" <?php checked($posts_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($posts_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_bulk_editing" value="readonly" <?php checked($posts_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Posts list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_posts_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($posts_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom fields to make available as template replacement variables (e.g. <code>%custom_field(field_name)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($posts_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_thumbnail_watermark" value="off" <?php checked($posts_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($posts_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_thumbnail_watermark" value="play" <?php checked($posts_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($posts_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_posts_thumbnail_watermark" value="gif" <?php checked($posts_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on post featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Pages -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'pages') ? 'active' : ''; ?>" id="gmb-subtab-pages">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Pages Settings</h2>
                                    <p class="gmb-text-muted">Change global SEO, Schema, and meta settings for single pages. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $page_title_temp = get_option('gmb_metadata_page_title_template', '%title% %sep% %sitename%');
                                    $page_desc_temp = get_option('gmb_metadata_page_desc_template', '%excerpt%');
                                    $pages_schema_type = get_option('gmb_pages_schema_type', 'none');
                                    $pages_schema_headline = get_option('gmb_pages_schema_headline', '%seo_title%');
                                    $pages_schema_desc = get_option('gmb_pages_schema_desc', '%seo_description%');
                                    $pages_article_type = get_option('gmb_pages_article_type', 'article');
                                    $pages_autodetect_video = get_option('gmb_pages_autodetect_video', '1');
                                    $pages_robots_enable = get_option('gmb_pages_robots_meta_enable', '0');
                                    $pages_robots = get_option('gmb_pages_robots_meta', '');
                                    $pages_robots_array = is_array($pages_robots) ? $pages_robots : array_map('trim', explode(',', strtolower($pages_robots)));
                                    $pages_advanced_snippet = get_option('gmb_pages_advanced_max_snippet', '-1');
                                    $pages_advanced_video = get_option('gmb_pages_advanced_max_video', '-1');
                                    $pages_advanced_image = get_option('gmb_pages_advanced_max_image', 'large');
                                    $pages_twitter_card = get_option('gmb_pages_twitter_card_type', '');
                                    $pages_link_suggestions = get_option('gmb_pages_link_suggestions', '1');
                                    $pages_link_suggestion_titles = get_option('gmb_pages_link_suggestion_titles', 'titles');
                                    $pages_slack = get_option('gmb_pages_slack_sharing', '1');
                                    $pages_seo_controls = get_option('gmb_pages_seo_controls', '1');
                                    $pages_bulk_editing = get_option('gmb_pages_bulk_editing', 'enabled');
                                    $pages_custom_fields = get_option('gmb_pages_custom_fields', '');
                                    $pages_watermark = get_option('gmb_pages_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Single Page Title -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Single Page Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_pages_title_template_input" name="gmb_metadata_page_title_template" value="<?php echo esc_attr($page_title_temp); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_title_template_input" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_title_template_input" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_title_template_input" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_title_template_input" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default title tag for single Page pages. Recommended length: 50–60 characters. Can be customized per page.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Single Page Description -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Single Page Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_pages_desc_template_input" name="gmb_metadata_page_desc_template" rows="3" placeholder="%excerpt%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($page_desc_temp); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_desc_template_input" data-tag="%excerpt%" >+ %excerpt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_desc_template_input" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_desc_template_input" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_desc_template_input" data-tag="%focus_keyword%" >+ %focus_keyword%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_desc_template_input" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default description for single Page pages. Recommended length: 120–160 characters. Can be customized per page.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Schema Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Schema Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_pages_schema_type" id="gmb_pages_schema_type_select" class="gmb-select gmb-input--max-480" onchange="document.getElementById('gmb-pages-schema-fields').style.display = (this.value === 'none') ? 'none' : 'block';">
                                                <option value="none" <?php selected($pages_schema_type, 'none'); ?>>None</option>
                                                <option value="article" <?php selected($pages_schema_type, 'article'); ?>>Article</option>
                                                <option value="book" <?php selected($pages_schema_type, 'book'); ?>>Book</option>
                                                <option value="course" <?php selected($pages_schema_type, 'course'); ?>>Course</option>
                                                <option value="event" <?php selected($pages_schema_type, 'event'); ?>>Event</option>
                                                <option value="jobposting" <?php selected($pages_schema_type, 'jobposting'); ?>>Job Posting</option>
                                                <option value="movie" <?php selected($pages_schema_type, 'movie'); ?>>Movie</option>
                                                <option value="music" <?php selected($pages_schema_type, 'music'); ?>>Music</option>
                                                <option value="product" <?php selected($pages_schema_type, 'product'); ?>>Product</option>
                                                <option value="recipe" <?php selected($pages_schema_type, 'recipe'); ?>>Recipe</option>
                                                <option value="restaurant" <?php selected($pages_schema_type, 'restaurant'); ?>>Restaurant</option>
                                                <option value="service" <?php selected($pages_schema_type, 'service'); ?>>Service</option>
                                                <option value="software" <?php selected($pages_schema_type, 'software'); ?>>Software Application</option>
                                                <option value="video" <?php selected($pages_schema_type, 'video'); ?>>Video</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default rich snippet Schema type selected when creating a new page.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Schema Sub-Fields Container -->
                                    <div id="gmb-pages-schema-fields" class="<?php echo ($pages_schema_type === 'none') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Headline -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Headline
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_pages_schema_headline" id="gmb_pages_schema_headline" value="<?php echo esc_attr($pages_schema_headline); ?>" class="gmb-input gmb-input--max-480" placeholder="%seo_title%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_headline" data-tag="%seo_title%" >+ %seo_title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_headline" data-tag="%title%" >+ %title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_headline" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Headline of the article schema metadata.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_pages_schema_desc" id="gmb_pages_schema_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%seo_description%"><?php echo esc_textarea($pages_schema_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_desc" data-tag="%seo_description%" >+ %seo_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_desc" data-tag="%excerpt%" >+ %excerpt%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_pages_schema_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Short description of the article schema metadata.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Article Type -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Article Type
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                    <label class="gmb-type-btn <?php echo ($pages_article_type === 'article') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_pages_article_type" value="article" <?php checked($pages_article_type, 'article'); ?>  />
                                                        Article
                                                    </label>
                                                    <label class="gmb-type-btn <?php echo ($pages_article_type === 'blogpost') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_pages_article_type" value="blogpost" <?php checked($pages_article_type, 'blogpost'); ?>  />
                                                        Blog Post
                                                    </label>
                                                    <label class="gmb-type-btn <?php echo ($pages_article_type === 'newsarticle') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_pages_article_type" value="newsarticle" <?php checked($pages_article_type, 'newsarticle'); ?>  />
                                                        News Article
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Autodetect Video -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Autodetect Video
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_pages_autodetect_video" value="1" <?php checked('1', $pages_autodetect_video); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Populate automatic Video Schema by auto-detecting YouTube, Vimeo, or HTML5 videos in the page content.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Page Robots Meta Enable -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Page Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_pages_robots_meta_enable" value="1" id="gmb-toggle-pages-robots" <?php checked('1', $pages_robots_enable); ?> onchange="document.getElementById('gmb-pages-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Select custom robots meta for single pages. Otherwise global default values will be applied.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Page Robots Meta Checkboxes & Advanced Directives -->
                                    <div id="gmb-pages-robots-checkboxes" class="gmb-robots-wrap <?php echo ($pages_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Page Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="index" <?php checked(in_array('index', $pages_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $pages_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $pages_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $pages_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $pages_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_pages_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $pages_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Pages -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_pages_advanced_max_snippet" value="<?php echo esc_attr($pages_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_pages_advanced_max_video" value="<?php echo esc_attr($pages_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_pages_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($pages_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($pages_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($pages_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_pages_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($pages_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($pages_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($pages_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for single pages when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_pages_link_suggestions" value="1" <?php checked('1', $pages_link_suggestions); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable Link Suggestions metabox for pages to discover internal linking opportunities.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestion Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestion Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($pages_link_suggestion_titles === 'titles') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_link_suggestion_titles" value="titles" <?php checked($pages_link_suggestion_titles, 'titles'); ?>  />
                                                    Titles
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($pages_link_suggestion_titles === 'focus') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_link_suggestion_titles" value="focus" <?php checked($pages_link_suggestion_titles, 'focus'); ?>  />
                                                    Focus Keywords
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_pages_slack_sharing" value="1" <?php checked('1', $pages_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Show author name and estimated reading time when page URL is shared on Slack.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_pages_seo_controls" value="1" <?php checked('1', $pages_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to single page editor screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($pages_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_bulk_editing" value="disabled" <?php checked($pages_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($pages_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_bulk_editing" value="enabled" <?php checked($pages_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($pages_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_bulk_editing" value="readonly" <?php checked($pages_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Pages list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_pages_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($pages_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom fields to make available as template replacement variables (e.g. <code>%custom_field(field_name)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($pages_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_thumbnail_watermark" value="off" <?php checked($pages_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($pages_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_thumbnail_watermark" value="play" <?php checked($pages_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($pages_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_pages_thumbnail_watermark" value="gif" <?php checked($pages_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on page featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Attachments -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'attachments') ? 'active' : ''; ?>" id="gmb-subtab-attachments">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Attachments Settings</h2>
                                    <p class="gmb-text-muted">Configure redirection, SEO meta title, description, and robot directives for media attachments. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $attachment_redirect = get_option('gmb_attachment_redirect_to_parent', 'off');
                                    $attachment_title = get_option('gmb_attachment_title_template', '%title% %sep% %sitename%');
                                    $attachment_desc = get_option('gmb_attachment_desc_template', '%caption%');
                                    $attachment_robots_enable = get_option('gmb_attachment_robots_meta_enable', '0');
                                    $attachment_robots = get_option('gmb_attachment_robots_meta', '');
                                    $attachment_robots_array = is_array($attachment_robots) ? $attachment_robots : array_map('trim', explode(',', strtolower($attachment_robots)));
                                    $attachment_advanced_snippet = get_option('gmb_attachment_advanced_max_snippet', '-1');
                                    $attachment_advanced_video = get_option('gmb_attachment_advanced_max_video', '-1');
                                    $attachment_advanced_image = get_option('gmb_attachment_advanced_max_image', 'large');
                                    $attachment_twitter_card = get_option('gmb_attachment_twitter_card_type', '');
                                    $attachment_seo_controls = get_option('gmb_attachment_seo_controls', '0');
                                    $attachment_bulk_editing = get_option('gmb_attachment_bulk_editing', 'disabled');
                                    ?>

                                    <!-- Redirect Attachments -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Redirect Attachments
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($attachment_redirect === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_redirect_to_parent" value="off" <?php checked($attachment_redirect, 'off'); ?>  />
                                                    Off (Serve Pages)
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($attachment_redirect === 'parent') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_redirect_to_parent" value="parent" <?php checked($attachment_redirect, 'parent'); ?>  />
                                                    Redirect to Parent Post (Recommended)
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($attachment_redirect === 'file') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_redirect_to_parent" value="file" <?php checked($attachment_redirect, 'file'); ?>  />
                                                    Redirect to Media File
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                301-redirect attachment URLs to avoid creating thin, low-content pages in Google search.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Attachment Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Attachment Title Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_attachment_title_template" name="gmb_attachment_title_template" value="<?php echo esc_attr($attachment_title); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%filename%" >+ %filename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%caption%" >+ %caption%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_title_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Configure the default title template of attachment pages when redirection is disabled.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Attachment Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Attachment Meta Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_attachment_desc_template" name="gmb_attachment_desc_template" rows="3" placeholder="%caption%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($attachment_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%caption%" >+ %caption%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%description%" >+ %description%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%alt%" >+ %alt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_attachment_desc_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Configure the default meta description template of attachment pages when redirection is disabled.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Attachment Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Attachments Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_attachment_robots_meta_enable" value="1" <?php checked('1', $attachment_robots_enable); ?> onchange="document.getElementById('gmb-attachment-robots-container').style.display = this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Custom robots meta directives for attachment URLs.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Attachment Robots Checkboxes & Advanced Directives -->
                                    <div id="gmb-attachment-robots-container" class="gmb-robots-wrap <?php echo ($attachment_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Attachment Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="index" <?php checked(in_array('index', $attachment_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $attachment_robots_array)); ?> />
                                                        <strong>noindex (Recommended)</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $attachment_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $attachment_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $attachment_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_attachment_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $attachment_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Attachments -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_attachment_advanced_max_snippet" value="<?php echo esc_attr($attachment_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_attachment_advanced_max_video" value="<?php echo esc_attr($attachment_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_attachment_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($attachment_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($attachment_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($attachment_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_attachment_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($attachment_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($attachment_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($attachment_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for attachment URLs when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_attachment_seo_controls" value="1" <?php checked('1', $attachment_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to media attachment edit screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($attachment_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_bulk_editing" value="disabled" <?php checked($attachment_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($attachment_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_bulk_editing" value="enabled" <?php checked($attachment_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($attachment_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_attachment_bulk_editing" value="readonly" <?php checked($attachment_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Media Library list table.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Services -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'services') ? 'active' : ''; ?>" id="gmb-subtab-services">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Services Settings</h2>
                                    <p class="gmb-text-muted">Configure global SEO title, description, Schema, and robots meta settings for single Services. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $services_title = get_option('gmb_services_title_template', '%title% %sep% %sitename%');
                                    $services_desc = get_option('gmb_services_desc_template', '%excerpt%');
                                    $services_schema_type = get_option('gmb_services_schema_type', 'service');
                                    $services_schema_headline = get_option('gmb_services_schema_headline', '%seo_title%');
                                    $services_schema_desc = get_option('gmb_services_schema_desc', '%seo_description%');
                                    $services_schema_provider = get_option('gmb_services_schema_provider_type', 'organization');
                                    $services_robots_enable = get_option('gmb_services_robots_meta_enable', '0');
                                    $services_robots = get_option('gmb_services_robots_meta', '');
                                    $services_robots_array = is_array($services_robots) ? $services_robots : array_map('trim', explode(',', strtolower($services_robots)));
                                    $services_advanced_snippet = get_option('gmb_services_advanced_max_snippet', '-1');
                                    $services_advanced_video = get_option('gmb_services_advanced_max_video', '-1');
                                    $services_advanced_image = get_option('gmb_services_advanced_max_image', 'large');
                                    $services_twitter_card = get_option('gmb_services_twitter_card_type', '');
                                    $services_link_suggestions = get_option('gmb_services_link_suggestions', '1');
                                    $services_link_suggestion_titles = get_option('gmb_services_link_suggestion_titles', 'titles');
                                    $services_slack = get_option('gmb_services_slack_sharing', '1');
                                    $services_seo_controls = get_option('gmb_services_seo_controls', '1');
                                    $services_bulk_editing = get_option('gmb_services_bulk_editing', 'enabled');
                                    $services_custom_fields = get_option('gmb_services_custom_fields', '');
                                    $services_watermark = get_option('gmb_services_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Services Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Services Title Template
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_services_title_template" name="gmb_services_title_template" value="<?php echo esc_attr($services_title); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_title_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_title_template" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_title_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_title_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default title template for single Services pages. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Services Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Services Meta Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_services_desc_template" name="gmb_services_desc_template" rows="3" placeholder="%excerpt%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($services_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_desc_template" data-tag="%excerpt%" >+ %excerpt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_desc_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_desc_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_desc_template" data-tag="%focus_keyword%" >+ %focus_keyword%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_desc_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default meta description template for single Services pages. Recommended length: 120–160 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Schema Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_services_schema_type" id="gmb_services_schema_type" class="gmb-input gmb-input--max-480" onchange="document.getElementById('gmb-services-schema-fields').style.display = (this.value === 'none') ? 'none' : 'block';">
                                                <option value="service" <?php selected($services_schema_type, 'service'); ?>>Service (Recommended)</option>
                                                <option value="none" <?php selected($services_schema_type, 'none'); ?>>None</option>
                                                <option value="article" <?php selected($services_schema_type, 'article'); ?>>Article</option>
                                                <option value="product" <?php selected($services_schema_type, 'product'); ?>>Product</option>
                                                <option value="course" <?php selected($services_schema_type, 'course'); ?>>Course</option>
                                                <option value="event" <?php selected($services_schema_type, 'event'); ?>>Event</option>
                                                <option value="jobposting" <?php selected($services_schema_type, 'jobposting'); ?>>Job Posting</option>
                                                <option value="localbusiness" <?php selected($services_schema_type, 'localbusiness'); ?>>Local Business</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default structured data Schema type selected when publishing a service.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Sub-Fields Container -->
                                    <div id="gmb-services-schema-fields" class="<?php echo ($services_schema_type === 'none') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Schema Headline / Name -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Name / Headline
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_services_schema_headline" id="gmb_services_schema_headline" value="<?php echo esc_attr($services_schema_headline); ?>" class="gmb-input gmb-input--max-480" placeholder="%seo_title%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_headline" data-tag="%seo_title%" >+ %seo_title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_headline" data-tag="%title%" >+ %title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_headline" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Name of the service in the structured data JSON-LD.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Schema Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_services_schema_desc" id="gmb_services_schema_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%seo_description%"><?php echo esc_textarea($services_schema_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_desc" data-tag="%seo_description%" >+ %seo_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_desc" data-tag="%excerpt%" >+ %excerpt%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_services_schema_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Short description of the service in the structured data JSON-LD.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Service Provider Type -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Service Provider
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                    <label class="gmb-type-btn <?php echo ($services_schema_provider === 'organization') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_services_schema_provider_type" value="organization" <?php checked($services_schema_provider, 'organization'); ?>  />
                                                        Organization (Default)
                                                    </label>
                                                    <label class="gmb-type-btn <?php echo ($services_schema_provider === 'person') ? 'active' : ''; ?>">
                                                        <input type="radio" name="gmb_services_schema_provider_type" value="person" <?php checked($services_schema_provider, 'person'); ?>  />
                                                        Person (Author)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Services Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Services Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_services_robots_meta_enable" value="1" <?php checked('1', $services_robots_enable); ?> onchange="document.getElementById('gmb-services-robots-container').style.display = this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Select custom robots meta directives for service pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Services Robots Checkboxes & Advanced Directives -->
                                    <div id="gmb-services-robots-container" class="gmb-robots-wrap <?php echo ($services_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Services Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="index" <?php checked(in_array('index', $services_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $services_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $services_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $services_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $services_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_services_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $services_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Services -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_services_advanced_max_snippet" value="<?php echo esc_attr($services_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_services_advanced_max_video" value="<?php echo esc_attr($services_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_services_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($services_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($services_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($services_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_services_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($services_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($services_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($services_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for Services pages when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Link Suggestions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_services_link_suggestions" value="1" <?php checked('1', $services_link_suggestions); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable Link Suggestions metabox for services to discover internal linking opportunities.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestion Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestion Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($services_link_suggestion_titles === 'titles') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_link_suggestion_titles" value="titles" <?php checked($services_link_suggestion_titles, 'titles'); ?>  />
                                                    Titles
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($services_link_suggestion_titles === 'focus') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_link_suggestion_titles" value="focus" <?php checked($services_link_suggestion_titles, 'focus'); ?>  />
                                                    Focus Keywords
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_services_slack_sharing" value="1" <?php checked('1', $services_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Show service author name and estimated reading time when service URL is shared on Slack.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_services_seo_controls" value="1" <?php checked('1', $services_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to single service editor screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($services_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_bulk_editing" value="disabled" <?php checked($services_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($services_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_bulk_editing" value="enabled" <?php checked($services_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($services_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_bulk_editing" value="readonly" <?php checked($services_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Services list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_services_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($services_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom fields to make available as template replacement variables (e.g. <code>%custom_field(field_name)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($services_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_thumbnail_watermark" value="off" <?php checked($services_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($services_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_thumbnail_watermark" value="play" <?php checked($services_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($services_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_services_thumbnail_watermark" value="gif" <?php checked($services_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on service featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Service Locations -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'service_locations') ? 'active' : ''; ?>" id="gmb-subtab-service_locations">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Service Locations Settings</h2>
                                    <p class="gmb-text-muted">Configure global SEO title, description, LocalBusiness Schema, and robots meta for single Service Locations. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $loc_title = get_option('gmb_service_locations_title_template', '%title% %sep% %sitename%');
                                    $loc_desc = get_option('gmb_service_locations_desc_template', '%excerpt%');
                                    $loc_schema_type = get_option('gmb_service_locations_schema_type', 'localbusiness');
                                    $loc_schema_headline = get_option('gmb_service_locations_schema_headline', '%seo_title%');
                                    $loc_schema_desc = get_option('gmb_service_locations_schema_desc', '%seo_description%');
                                    $loc_business_type = get_option('gmb_service_locations_schema_business_type', 'LocalBusiness');
                                    $loc_robots_enable = get_option('gmb_service_locations_robots_meta_enable', '0');
                                    $loc_robots = get_option('gmb_service_locations_robots_meta', '');
                                    $loc_robots_array = is_array($loc_robots) ? $loc_robots : array_map('trim', explode(',', strtolower($loc_robots)));
                                    $loc_advanced_snippet = get_option('gmb_service_locations_advanced_max_snippet', '-1');
                                    $loc_advanced_video = get_option('gmb_service_locations_advanced_max_video', '-1');
                                    $loc_advanced_image = get_option('gmb_service_locations_advanced_max_image', 'large');
                                    $loc_twitter_card = get_option('gmb_service_locations_twitter_card_type', '');
                                    $loc_link_suggestions = get_option('gmb_service_locations_link_suggestions', '1');
                                    $loc_link_suggestion_titles = get_option('gmb_service_locations_link_suggestion_titles', 'titles');
                                    $loc_slack = get_option('gmb_service_locations_slack_sharing', '1');
                                    $loc_seo_controls = get_option('gmb_service_locations_seo_controls', '1');
                                    $loc_bulk_editing = get_option('gmb_service_locations_bulk_editing', 'enabled');
                                    $loc_custom_fields = get_option('gmb_service_locations_custom_fields', '');
                                    $loc_watermark = get_option('gmb_service_locations_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Service Locations Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Service Locations Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_service_locations_title_template" name="gmb_service_locations_title_template" value="<?php echo esc_attr($loc_title); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_title_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_title_template" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_title_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_title_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default title template for single Service Location pages. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Service Locations Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Service Locations Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_service_locations_desc_template" name="gmb_service_locations_desc_template" rows="3" placeholder="%excerpt%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($loc_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_desc_template" data-tag="%excerpt%" >+ %excerpt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_desc_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_desc_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_desc_template" data-tag="%focus_keyword%" >+ %focus_keyword%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_desc_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default meta description template for single Service Location pages. Recommended length: 120–160 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Schema Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_service_locations_schema_type" id="gmb_service_locations_schema_type" class="gmb-input gmb-input--max-480" onchange="document.getElementById('gmb-locations-schema-fields').style.display = (this.value === 'none') ? 'none' : 'block';">
                                                <option value="localbusiness" <?php selected($loc_schema_type, 'localbusiness'); ?>>Local Business (Recommended)</option>
                                                <option value="none" <?php selected($loc_schema_type, 'none'); ?>>None</option>
                                                <option value="service" <?php selected($loc_schema_type, 'service'); ?>>Service</option>
                                                <option value="article" <?php selected($loc_schema_type, 'article'); ?>>Article</option>
                                                <option value="product" <?php selected($loc_schema_type, 'product'); ?>>Product</option>
                                                <option value="place" <?php selected($loc_schema_type, 'place'); ?>>Place</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default structured data Schema type for service location pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Sub-Fields Container -->
                                    <div id="gmb-locations-schema-fields" class="<?php echo ($loc_schema_type === 'none') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Schema Headline / Name -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Location Name
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_service_locations_schema_headline" id="gmb_service_locations_schema_headline" value="<?php echo esc_attr($loc_schema_headline); ?>" class="gmb-input gmb-input--max-480" placeholder="%seo_title%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_headline" data-tag="%seo_title%" >+ %seo_title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_headline" data-tag="%title%" >+ %title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_headline" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Name of the location entity in the JSON-LD structured data.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Schema Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Description
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_service_locations_schema_desc" id="gmb_service_locations_schema_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%seo_description%"><?php echo esc_textarea($loc_schema_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_desc" data-tag="%seo_description%" >+ %seo_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_desc" data-tag="%excerpt%" >+ %excerpt%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_service_locations_schema_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Short description of the location in the structured data JSON-LD.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Business Subtype -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Business Subtype
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <select name="gmb_service_locations_schema_business_type" class="gmb-input gmb-input--max-480">
                                                    <option value="LocalBusiness" <?php selected($loc_business_type, 'LocalBusiness'); ?>>LocalBusiness (General)</option>
                                                    <option value="Store" <?php selected($loc_business_type, 'Store'); ?>>Store / Retail</option>
                                                    <option value="MedicalBusiness" <?php selected($loc_business_type, 'MedicalBusiness'); ?>>Medical / Clinic / Healthcare</option>
                                                    <option value="HomeAndConstructionBusiness" <?php selected($loc_business_type, 'HomeAndConstructionBusiness'); ?>>Home & Construction / Contractor</option>
                                                    <option value="AutomotiveBusiness" <?php selected($loc_business_type, 'AutomotiveBusiness'); ?>>Automotive / Repair</option>
                                                    <option value="FinancialService" <?php selected($loc_business_type, 'FinancialService'); ?>>Financial Service / Accounting</option>
                                                    <option value="FoodEstablishment" <?php selected($loc_business_type, 'FoodEstablishment'); ?>>Food Establishment / Restaurant</option>
                                                    <option value="HealthAndBeautyBusiness" <?php selected($loc_business_type, 'HealthAndBeautyBusiness'); ?>>Health & Beauty / Salon / Spa</option>
                                                    <option value="ProfessionalService" <?php selected($loc_business_type, 'ProfessionalService'); ?>>Professional Service / Agency</option>
                                                    <option value="LegalService" <?php selected($loc_business_type, 'LegalService'); ?>>Legal Service / Lawyer</option>
                                                    <option value="RealEstateAgent" <?php selected($loc_business_type, 'RealEstateAgent'); ?>>Real Estate Agent / Agency</option>
                                                    <option value="TravelAgency" <?php selected($loc_business_type, 'TravelAgency'); ?>>Travel Agency</option>
                                                </select>
                                                <p class="gmb-form-help">
                                                    Specific schema.org business category for accurate Google Knowledge Graph indexing.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Service Locations Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Locations Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_service_locations_robots_meta_enable" value="1" <?php checked('1', $loc_robots_enable); ?> onchange="document.getElementById('gmb-locations-robots-container').style.display = this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Custom robots meta directives for service location pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Service Locations Robots Checkboxes & Advanced Directives -->
                                    <div id="gmb-locations-robots-container" class="gmb-robots-wrap <?php echo ($loc_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Locations Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="index" <?php checked(in_array('index', $loc_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $loc_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $loc_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $loc_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $loc_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_service_locations_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $loc_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Service Locations -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_service_locations_advanced_max_snippet" value="<?php echo esc_attr($loc_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_service_locations_advanced_max_video" value="<?php echo esc_attr($loc_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_service_locations_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($loc_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($loc_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($loc_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_service_locations_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($loc_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($loc_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($loc_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for Service Location pages when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Link Suggestions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_service_locations_link_suggestions" value="1" <?php checked('1', $loc_link_suggestions); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable Link Suggestions metabox for service locations to discover internal linking opportunities.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestion Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestion Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($loc_link_suggestion_titles === 'titles') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_link_suggestion_titles" value="titles" <?php checked($loc_link_suggestion_titles, 'titles'); ?>  />
                                                    Titles
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($loc_link_suggestion_titles === 'focus') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_link_suggestion_titles" value="focus" <?php checked($loc_link_suggestion_titles, 'focus'); ?>  />
                                                    Focus Keywords
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_service_locations_slack_sharing" value="1" <?php checked('1', $loc_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Show location author name and estimated reading time when location URL is shared on Slack.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_service_locations_seo_controls" value="1" <?php checked('1', $loc_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to single service location editor screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($loc_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_bulk_editing" value="disabled" <?php checked($loc_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($loc_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_bulk_editing" value="enabled" <?php checked($loc_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($loc_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_bulk_editing" value="readonly" <?php checked($loc_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Service Locations list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_service_locations_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($loc_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom fields to make available as template replacement variables (e.g. <code>%custom_field(address)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($loc_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_thumbnail_watermark" value="off" <?php checked($loc_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($loc_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_thumbnail_watermark" value="play" <?php checked($loc_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($loc_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_service_locations_thumbnail_watermark" value="gif" <?php checked($loc_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on location featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Team Members -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'team_members') ? 'active' : ''; ?>" id="gmb-subtab-team_members">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Team Members Settings</h2>
                                    <p class="gmb-text-muted">Configure global SEO title, description, Person Schema, and robots meta for single Team Members. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $team_title = get_option('gmb_team_members_title_template', '%title% %sep% %sitename%');
                                    $team_desc = get_option('gmb_team_members_desc_template', '%excerpt%');
                                    $team_schema_type = get_option('gmb_team_members_schema_type', 'person');
                                    $team_schema_headline = get_option('gmb_team_members_schema_headline', '%seo_title%');
                                    $team_schema_desc = get_option('gmb_team_members_schema_desc', '%seo_description%');
                                    $team_schema_job_title = get_option('gmb_team_members_schema_job_title', '');
                                    $team_robots_enable = get_option('gmb_team_members_robots_meta_enable', '0');
                                    $team_robots = get_option('gmb_team_members_robots_meta', '');
                                    $team_robots_array = is_array($team_robots) ? $team_robots : array_map('trim', explode(',', strtolower($team_robots)));
                                    $team_advanced_snippet = get_option('gmb_team_members_advanced_max_snippet', '-1');
                                    $team_advanced_video = get_option('gmb_team_members_advanced_max_video', '-1');
                                    $team_advanced_image = get_option('gmb_team_members_advanced_max_image', 'large');
                                    $team_twitter_card = get_option('gmb_team_members_twitter_card_type', '');
                                    $team_link_suggestions = get_option('gmb_team_members_link_suggestions', '1');
                                    $team_link_suggestion_titles = get_option('gmb_team_members_link_suggestion_titles', 'titles');
                                    $team_slack = get_option('gmb_team_members_slack_sharing', '1');
                                    $team_seo_controls = get_option('gmb_team_members_seo_controls', '1');
                                    $team_bulk_editing = get_option('gmb_team_members_bulk_editing', 'enabled');
                                    $team_custom_fields = get_option('gmb_team_members_custom_fields', '');
                                    $team_watermark = get_option('gmb_team_members_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Team Members Title Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Team Members Title
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_team_members_title_template" name="gmb_team_members_title_template" value="<?php echo esc_attr($team_title); ?>" placeholder="%title% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_title_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_title_template" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_title_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_title_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default title template for single Team Member pages. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Team Members Description Template -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Team Members Description
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_team_members_desc_template" name="gmb_team_members_desc_template" rows="3" placeholder="%excerpt%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($team_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_desc_template" data-tag="%excerpt%" >+ %excerpt%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_desc_template" data-tag="%title%" >+ %title%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_desc_template" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_desc_template" data-tag="%focus_keyword%" >+ %focus_keyword%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_desc_template" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Default meta description template for single Team Member pages. Recommended length: 120–160 characters.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Schema Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_team_members_schema_type" id="gmb_team_members_schema_type" class="gmb-input gmb-input--max-480" onchange="document.getElementById('gmb-team-schema-fields').style.display = (this.value === 'none') ? 'none' : 'block';">
                                                <option value="person" <?php selected($team_schema_type, 'person'); ?>>Person (Recommended)</option>
                                                <option value="none" <?php selected($team_schema_type, 'none'); ?>>None</option>
                                                <option value="article" <?php selected($team_schema_type, 'article'); ?>>Article</option>
                                                <option value="localbusiness" <?php selected($team_schema_type, 'localbusiness'); ?>>Local Business</option>
                                                <option value="profilepage" <?php selected($team_schema_type, 'profilepage'); ?>>Profile Page</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Default structured data Schema type for team member profiles.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Schema Sub-Fields Container -->
                                    <div id="gmb-team-schema-fields" class="<?php echo ($team_schema_type === 'none') ? 'gmb-hidden' : ''; ?>">
                                        <!-- Schema Headline / Member Name -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Member Name
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_team_members_schema_headline" id="gmb_team_members_schema_headline" value="<?php echo esc_attr($team_schema_headline); ?>" class="gmb-input gmb-input--max-480" placeholder="%seo_title%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_headline" data-tag="%seo_title%" >+ %seo_title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_headline" data-tag="%title%" >+ %title%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_headline" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Name of the person entity in the JSON-LD structured data.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Schema Description -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Schema Biography / Desc
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <textarea name="gmb_team_members_schema_desc" id="gmb_team_members_schema_desc" rows="3" class="gmb-input gmb-input--max-480" placeholder="%seo_description%"><?php echo esc_textarea($team_schema_desc); ?></textarea>
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_desc" data-tag="%seo_description%" >+ %seo_description%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_desc" data-tag="%excerpt%" >+ %excerpt%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Short biography of the team member in the structured data JSON-LD.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Job Title / Role -->
                                        <div class="gmb-settings-row">
                                            <div class="gmb-settings-label-col">
                                                Job Title / Role
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <input type="text" name="gmb_team_members_schema_job_title" id="gmb_team_members_schema_job_title" value="<?php echo esc_attr($team_schema_job_title); ?>" class="gmb-input gmb-input--max-480" placeholder="%custom_field(job_title)%" />
                                                <div class="gmb-var-tags-wrap">
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_job_title" data-tag="%custom_field(job_title)%" >+ %custom_field(job_title)%</button>
                                                    <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_team_members_schema_job_title" data-tag="%title%" >+ %title%</button>
                                                </div>
                                                <p class="gmb-form-help">
                                                    Designation or professional role for Schema.org Person <code>jobTitle</code> property.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Team Members Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Team Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_team_members_robots_meta_enable" value="1" <?php checked('1', $team_robots_enable); ?> onchange="document.getElementById('gmb-team-robots-container').style.display = this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Custom robots meta directives for team member profile pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Team Members Robots Checkboxes & Advanced Directives -->
                                    <div id="gmb-team-robots-container" class="gmb-robots-wrap <?php echo ($team_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Team Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="index" <?php checked(in_array('index', $team_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $team_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $team_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $team_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $team_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_team_members_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $team_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Team Members -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_team_members_advanced_max_snippet" value="<?php echo esc_attr($team_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_team_members_advanced_max_video" value="<?php echo esc_attr($team_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_team_members_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($team_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($team_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($team_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_team_members_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($team_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($team_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($team_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for Team Member profile pages when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Link Suggestions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_team_members_link_suggestions" value="1" <?php checked('1', $team_link_suggestions); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Enable Link Suggestions metabox for team members to discover internal linking opportunities.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Link Suggestion Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Link Suggestion Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($team_link_suggestion_titles === 'titles') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_link_suggestion_titles" value="titles" <?php checked($team_link_suggestion_titles, 'titles'); ?>  />
                                                    Titles
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($team_link_suggestion_titles === 'focus') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_link_suggestion_titles" value="focus" <?php checked($team_link_suggestion_titles, 'focus'); ?>  />
                                                    Focus Keywords
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_team_members_slack_sharing" value="1" <?php checked('1', $team_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Show team member author name and estimated reading time when profile URL is shared on Slack.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_team_members_seo_controls" value="1" <?php checked('1', $team_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add custom SEO metabox directly to single team member editor screens.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($team_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_bulk_editing" value="disabled" <?php checked($team_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($team_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_bulk_editing" value="enabled" <?php checked($team_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($team_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_bulk_editing" value="readonly" <?php checked($team_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Team Members list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_team_members_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($team_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom fields to make available as template replacement variables (e.g. <code>%custom_field(qualification)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($team_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_thumbnail_watermark" value="off" <?php checked($team_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($team_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_thumbnail_watermark" value="play" <?php checked($team_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($team_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_team_members_thumbnail_watermark" value="gif" <?php checked($team_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on member featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>

                            <!-- Subtab: Categories -->
                            <div class="gmb-subtab-panel <?php echo ($active_sub === 'categories') ? 'active' : ''; ?>" id="gmb-subtab-categories">
                                <div class="gmb-settings-panel-header">
                                    <h2 class="gmb-heading-2">Categories Settings</h2>
                                    <p class="gmb-text-muted">Change global SEO, Schema, and robots meta settings for category archive pages. <a href="https://gmbranker.org/" target="_blank" class="gmb-help-link">Learn more</a>.</p>
                                </div>
                                
                                <div class="gmb-card-settings-list">
                                    <?php
                                    $categories_archive_title = get_option('gmb_categories_archive_title', '%term% %sep% %sitename%');
                                    $categories_archive_desc = get_option('gmb_categories_archive_desc', '%term_description%');
                                    $categories_robots_enable = get_option('gmb_categories_robots_meta_enable', '0');
                                    $categories_robots = get_option('gmb_categories_robots_meta', '');
                                    $categories_robots_array = is_array($categories_robots) ? $categories_robots : array_map('trim', explode(',', strtolower($categories_robots)));
                                    $categories_advanced_snippet = get_option('gmb_categories_advanced_max_snippet', '-1');
                                    $categories_advanced_video = get_option('gmb_categories_advanced_max_video', '-1');
                                    $categories_advanced_image = get_option('gmb_categories_advanced_max_image', 'large');
                                    $categories_twitter_card = get_option('gmb_categories_twitter_card_type', '');
                                    $categories_slack = get_option('gmb_categories_slack_sharing', '1');
                                    $categories_seo_controls = get_option('gmb_categories_seo_controls', '1');
                                    $categories_bulk_editing = get_option('gmb_categories_bulk_editing', 'disabled');
                                    $categories_remove_snippet = get_option('gmb_categories_remove_snippet', '0');
                                    $categories_custom_fields = get_option('gmb_categories_custom_fields', '');
                                    $categories_watermark = get_option('gmb_categories_thumbnail_watermark', 'off');
                                    ?>
                                    
                                    <!-- Category Archive Titles -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Category Archive Titles
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <input type="text" id="gmb_categories_archive_title" name="gmb_categories_archive_title" value="<?php echo esc_attr($categories_archive_title); ?>" placeholder="%term% %sep% %sitename%" class="gmb-input gmb-input--max-480" />
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_title" data-tag="%term%" >+ %term%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_title" data-tag="%sep%" >+ %sep%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_title" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_title" data-tag="%page%" >+ %page%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_title" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Title tag template for Category archives. Recommended length: 50–60 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Category Archive Descriptions -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Category Archive Descriptions
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea id="gmb_categories_archive_desc" name="gmb_categories_archive_desc" rows="3" placeholder="%term_description%" class="gmb-input gmb-input--max-480"><?php echo esc_textarea($categories_archive_desc); ?></textarea>
                                            <div class="gmb-var-tags-wrap">
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_desc" data-tag="%term_description%" >+ %term_description%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_desc" data-tag="%term%" >+ %term%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_desc" data-tag="%sitename%" >+ %sitename%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_desc" data-tag="%page%" >+ %page%</button>
                                                <button type="button" class="gmb-tag-insert-pill gmb-var-tag" data-target="gmb_categories_archive_desc" data-tag="%currentyear%" >+ %currentyear%</button>
                                            </div>
                                            <p class="gmb-form-help">
                                                Meta description template for Category archives. Recommended length: 120–160 characters.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Category Archives Robots Meta -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Category Archives Robots Meta
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_categories_robots_meta_enable" value="1" id="gmb-toggle-categories-robots" <?php checked('1', $categories_robots_enable); ?> onchange="document.getElementById('gmb-categories-robots-checkboxes').style.display=this.checked ? 'block' : 'none';" />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Select custom robots meta for category archive pages. Otherwise the default meta will be used, as set in the Global Meta tab.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Category Archives Robots Meta Checkboxes & Advanced Directives -->
                                    <div id="gmb-categories-robots-checkboxes" class="gmb-robots-wrap <?php echo ($categories_robots_enable === '1') ? 'is-active' : ''; ?>">
                                        <div class="gmb-flex gmb-mb-16">
                                            <div class="gmb-settings-label-col">
                                                Category Robots Options
                                            </div>
                                            <div class="gmb-settings-input-col">
                                                <div class="gmb-grid-2col-max480">
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="index" <?php checked(in_array('index', $categories_robots_array)); ?> />
                                                        <strong>index</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="noindex" <?php checked(in_array('noindex', $categories_robots_array)); ?> />
                                                        <strong>noindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="nofollow" <?php checked(in_array('nofollow', $categories_robots_array)); ?> />
                                                        <strong>nofollow</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="noarchive" <?php checked(in_array('noarchive', $categories_robots_array)); ?> />
                                                        <strong>noarchive</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="noimageindex" <?php checked(in_array('noimageindex', $categories_robots_array)); ?> />
                                                        <strong>noimageindex</strong>
                                                    </label>
                                                    <label class="gmb-checkbox-label">
                                                        <input type="checkbox" name="gmb_categories_robots_meta[]" value="nosnippet" <?php checked(in_array('nosnippet', $categories_robots_array)); ?> />
                                                        <strong>nosnippet</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Advanced Robots Directives for Categories -->
                                        <div class="gmb-flex">
                                            <div class="gmb-settings-label-col">
                                                Advanced Robots
                                            </div>
                                            <div class="gmb-settings-input-col gmb-input--max-480">
                                                <div>
                                                    <label class="gmb-form-label">Max Snippet (characters)</label>
                                                    <input type="number" name="gmb_categories_advanced_max_snippet" value="<?php echo esc_attr($categories_advanced_snippet); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited character snippets.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Video Preview (seconds)</label>
                                                    <input type="number" name="gmb_categories_advanced_max_video" value="<?php echo esc_attr($categories_advanced_video); ?>" placeholder="-1" class="gmb-input" />
                                                    <p class="gmb-form-help">Set to -1 for unlimited video preview length.</p>
                                                </div>
                                                <div>
                                                    <label class="gmb-form-label">Max Image Preview</label>
                                                    <select name="gmb_categories_advanced_max_image" class="gmb-input">
                                                        <option value="large" <?php selected($categories_advanced_image, 'large'); ?>>Large (Recommended)</option>
                                                        <option value="standard" <?php selected($categories_advanced_image, 'standard'); ?>>Standard</option>
                                                        <option value="none" <?php selected($categories_advanced_image, 'none'); ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter Card Type -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Twitter Card Type
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <select name="gmb_categories_twitter_card_type" class="gmb-input gmb-input--max-480">
                                                <option value="" <?php selected($categories_twitter_card, ''); ?>>Default (Use Global Setting)</option>
                                                <option value="summary_large_image" <?php selected($categories_twitter_card, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                                                <option value="summary" <?php selected($categories_twitter_card, 'summary'); ?>>Summary Card</option>
                                            </select>
                                            <p class="gmb-form-help">
                                                Select the card format for Category archive pages when shared on X (Twitter).
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Slack Enhanced Sharing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Slack Enhanced Sharing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_categories_slack_sharing" value="1" <?php checked('1', $categories_slack); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                When the option is enabled and a term from this taxonomy is shared on Slack, additional information will be shown.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Add SEO Controls -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Add SEO Controls
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_categories_seo_controls" value="1" <?php checked('1', $categories_seo_controls); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Add SEO options metabox controls to individual categories editing screen.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bulk Editing -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Bulk Editing
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm" >
                                                <label class="gmb-type-btn <?php echo ($categories_bulk_editing === 'disabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_bulk_editing" value="disabled" <?php checked($categories_bulk_editing, 'disabled'); ?>  />
                                                    Disabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($categories_bulk_editing === 'enabled') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_bulk_editing" value="enabled" <?php checked($categories_bulk_editing, 'enabled'); ?>  />
                                                    Enabled
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($categories_bulk_editing === 'readonly') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_bulk_editing" value="readonly" <?php checked($categories_bulk_editing, 'readonly'); ?>  />
                                                    Read Only
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Enable SEO columns and inline quick-edit capabilities in the Categories list table.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Snippet Data -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Remove Snippet Data
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <label class="gmb-switch">
                                                <input type="checkbox" name="gmb_categories_remove_snippet" value="1" <?php checked('1', $categories_remove_snippet); ?> />
                                                <span class="gmb-slider round"></span>
                                            </label>
                                            <p class="gmb-form-help">
                                                Remove Schema structured data from Category archive pages.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Custom Fields -->
                                    <div class="gmb-settings-row">
                                        <div class="gmb-settings-label-col">
                                            Custom Fields
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <textarea name="gmb_categories_custom_fields" rows="3" class="gmb-input gmb-input--max-480" placeholder="One field per line..."><?php echo esc_textarea($categories_custom_fields); ?></textarea>
                                            <p class="gmb-form-help">
                                                List custom taxonomy meta fields to make available as template replacement variables (e.g. <code>%custom_field(field_name)%</code>). One custom field name per line.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Thumbnail Watermark -->
                                    <div class="gmb-settings-row gmb-settings-row--noborder">
                                        <div class="gmb-settings-label-col">
                                            Default Thumbnail Watermark
                                        </div>
                                        <div class="gmb-settings-input-col">
                                            <div class="gmb-type-selector gmb-flex-gap-sm">
                                                <label class="gmb-type-btn <?php echo ($categories_watermark === 'off') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_thumbnail_watermark" value="off" <?php checked($categories_watermark, 'off'); ?>  />
                                                    Off
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($categories_watermark === 'play') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_thumbnail_watermark" value="play" <?php checked($categories_watermark, 'play'); ?>  />
                                                    Play Icon
                                                </label>
                                                <label class="gmb-type-btn <?php echo ($categories_watermark === 'gif') ? 'active' : ''; ?>">
                                                    <input type="radio" name="gmb_categories_thumbnail_watermark" value="gif" <?php checked($categories_watermark, 'gif'); ?>  />
                                                    GIF Icon
                                                </label>
                                            </div>
                                            <p class="gmb-form-help">
                                                Automatically overlay a video play button or GIF indicator icon on category featured images.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-footer justify-end">
                                    <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes"  />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Page: Sitemap Settings Page -->
