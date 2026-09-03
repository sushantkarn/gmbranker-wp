<?php
if (!defined('ABSPATH')) exit;
?>
            <?php if ($current_page === 'gmb-ranker-schema') : ?>
                <?php 
                $schema_mod_val = get_option('gmb_ranker_module_schema', '1');
                if ($schema_mod_val === '0' || $schema_mod_val === 'off') : 
                ?>
                    <div class="rm-tab-content active">
                        <div class="gmb-empty-state">
                            <div class="gmb-empty-state-icon--warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            </div>
                            <h2 class="gmb-heading-2">Schema (Structured Data) Module is Disabled</h2>
                            <p class="gmb-text-muted">Enable the Schema module to configure rich structured data types (Article, LocalBusiness, Service, Product, FAQ, Person) for Google, Bing, and AI search engines.</p>
                            <div class="gmb-flex-center-gap-md">
                                <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_schema" >Enable Module</button>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-empty-state-action-btn">Go to Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="rm-tab-content active" id="rm-tab-schema">
                        <form method="post" action="options.php" novalidate>
                            <?php settings_fields('gmb_ranker_schema_group'); ?>
                            
                            <div class="gmb-sidebar-layout-container">
                                
                                <!-- Sidebar Navigation Column -->
                                <?php
                                $active_schema_sub = 'general';
                                $req_sub = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : (isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : ''));
                                if (!empty($req_sub) && in_array($req_sub, array('general', 'templates', 'post-types', 'knowledge', 'custom', 'presets', 'settings'), true)) {
                                    $active_schema_sub = ($req_sub === 'settings') ? 'general' : $req_sub;
                                } elseif (!empty($current_tab) && in_array($current_tab, array('general', 'templates', 'post-types', 'knowledge', 'custom', 'presets', 'settings'), true)) {
                                    $active_schema_sub = ($current_tab === 'settings') ? 'general' : $current_tab;
                                }
                                ?>
                                <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_schema_sub); ?>" />
                                <div class="gmb-sidebar-nav">
                                    <ul>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'general') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-general">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                            General Schema
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'templates') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-templates">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            Schema Builder
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'post-types') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-post-types">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            Post Types Schema
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'knowledge') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-knowledge">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                            Knowledge Graph
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'custom') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-custom">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                            Custom Code &amp; Test
                                        </li>
                                        <li class="gmb-sidebar-nav-item <?php echo ($active_schema_sub === 'presets') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-presets">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                            1-Click Presets
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Content Settings Column -->
                                <div class="gmb-sidebar-content-panel">
                                    
                                    <!-- SUBTAB 1: General Schema -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'general') ? 'active' : ''; ?>" id="gmb-subtab-schema-general">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">General Schema Settings</h2>
                                            <p class="gmb-text-muted">Configure foundational sitewide Schema.org entities to establish semantic authority in Google, Bing, and AI indices.</p>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- WebSite Schema -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    WebSite Schema
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_enable_website" value="1" <?php checked('1', get_option('gmb_schema_enable_website', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Injects the top-level <code>@type: WebSite</code> JSON-LD entity on your homepage to define your site brand, publisher reference, and homepage URL.</p>
                                                </div>
                                            </div>

                                            <!-- WebSite Name -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    WebSite Name
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="text" name="gmb_schema_website_name" value="<?php echo esc_attr(get_option('gmb_schema_website_name', get_bloginfo('name'))); ?>" class="regular-text gmb-input gmb-input--max-520" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                                    <p class="gmb-form-help">The canonical name of your website. Defaults to your WordPress Site Title.</p>
                                                </div>
                                            </div>

                                            <!-- WebSite Alternate Name -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    WebSite Alternate Name
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="text" name="gmb_schema_website_alt_name" value="<?php echo esc_attr(get_option('gmb_schema_website_alt_name', '')); ?>" class="regular-text gmb-input gmb-input--max-520" placeholder="e.g. CNN" />
                                                    <p class="gmb-form-help">An alternate brand name, trading name, or abbreviation for your site (e.g. CNN, BBC, Acme Corp).</p>
                                                </div>
                                            </div>

                                            <!-- About Page Selection -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    About Page Schema
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <?php
                                                    $about_val = get_option('gmb_schema_about_page', get_option('gmb_local_seo_about_page', 0));
                                                    wp_dropdown_pages(array(
                                                        'name'              => 'gmb_schema_about_page',
                                                        'id'                => 'gmb_schema_about_page',
                                                        'show_option_none'  => '— Select About Page —',
                                                        'option_none_value' => '0',
                                                        'selected'          => $about_val,
                                                        'class'             => 'regular-text gmb-select gmb-input--min-280',
                                                    ));
                                                    ?>
                                                    <p class="gmb-form-help">Select your About Us page. GMB Ranker automatically injects <code>@type: AboutPage</code> structured data for Google Knowledge Panels.</p>
                                                </div>
                                            </div>

                                            <!-- Contact Page Selection -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Contact Page Schema
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <?php
                                                    $contact_val = get_option('gmb_schema_contact_page', get_option('gmb_local_seo_contact_page', 0));
                                                    wp_dropdown_pages(array(
                                                        'name'              => 'gmb_schema_contact_page',
                                                        'id'                => 'gmb_schema_contact_page',
                                                        'show_option_none'  => '— Select Contact Page —',
                                                        'option_none_value' => '0',
                                                        'selected'          => $contact_val,
                                                        'class'             => 'regular-text gmb-select gmb-input--min-280',
                                                    ));
                                                    ?>
                                                    <p class="gmb-form-help">Select your Contact Us page. GMB Ranker automatically injects <code>@type: ContactPage</code> schema with customer care endpoints.</p>
                                                </div>
                                            </div>

                                            <!-- Default Schema Fallback Image -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Default Fallback Image
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-flex-center-gap-sm gmb-mb-8">
                                                        <input type="text" id="gmb_schema_default_img_input" name="gmb_schema_default_image" value="<?php echo esc_attr(get_option('gmb_schema_default_image', '')); ?>" class="regular-text gmb-input gmb-input--max-420" placeholder="https://example.com/default-schema-image.jpg" />
                                                        <button type="button" class="button gmb-btn--secondary" id="gmb_schema_upload_default_img_btn">Upload</button>
                                                    </div>
                                                    <p class="gmb-form-help">Used in Article/Blog schema when a post has no featured image. Resolves Google Rich Results <em>Missing field 'image'</em> warnings.</p>
                                                    <?php $curr_def_img = get_option('gmb_schema_default_image', ''); ?>
                                                    <div id="gmb_schema_default_img_preview_wrap" class="gmb-thumb-preview <?php echo empty($curr_def_img) ? 'gmb-hidden' : ''; ?>">
                                                        <img id="gmb_schema_default_img_preview" src="<?php echo esc_url($curr_def_img); ?>" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sitelinks SearchBox -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Sitelinks SearchBox
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_enable_sitelinks" value="1" <?php checked('1', get_option('gmb_schema_enable_sitelinks', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Adds a Google <code>SearchAction</code> parameter inside WebSite schema so Google can show a direct interactive search box in branded search results.</p>
                                                </div>
                                            </div>

                                            <!-- BreadcrumbList Schema -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    BreadcrumbList Schema
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_enable_breadcrumbs" value="1" <?php checked('1', get_option('gmb_schema_enable_breadcrumbs', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Automatically injects <code>BreadcrumbList</code> structured data across all post types and categories for rich URL navigation trails in SERPs.</p>
                                                </div>
                                            </div>

                                            <!-- Author Representation -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Author Representation
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <select name="gmb_schema_author_type" class="gmb-select gmb-input--min-280">
                                                        <option value="Person" <?php selected(get_option('gmb_schema_author_type', 'Person'), 'Person'); ?>>Person (Recommended for Google E-E-A-T)</option>
                                                        <option value="Organization" <?php selected(get_option('gmb_schema_author_type', 'Person'), 'Organization'); ?>>Organization</option>
                                                    </select>
                                                    <p class="gmb-form-help">Defines whether post authors should be rendered as individual persons or attributed to the parent organization.</p>
                                                </div>
                                            </div>

                                            <!-- Author SameAs Profiles -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Author SameAs Profiles
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <textarea name="gmb_schema_author_sameas" rows="3" class="gmb-textarea" placeholder="https://www.linkedin.com/in/author&#10;https://twitter.com/author"><?php echo esc_textarea(get_option('gmb_schema_author_sameas', '')); ?></textarea>
                                                    <p class="gmb-form-help">One URL per line (e.g. Wikipedia, LinkedIn profile, Wikidata link) establishing canonical author entity identity.</p>
                                                </div>
                                            </div>

                                            <!-- Speakable Schema -->
                                            <div class="gmb-settings-row gmb-settings-row--noborder">
                                                <div class="gmb-settings-label-col">
                                                    Speakable Schema
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_enable_speakable" value="1" <?php checked('1', get_option('gmb_schema_enable_speakable', '0')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">Adds SpeakableSpecification pointing to headline and summary for Google Assistant &amp; voice search devices.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                            <input type="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                        </div>
                                    </div>

                                    <!-- SUBTAB: Schema Builder -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'templates') ? 'active' : ''; ?>" id="gmb-subtab-schema-templates">
                                        <?php
                                        if (!function_exists('gmb_ranker_get_schema_icon_svg')) {
                                            function gmb_ranker_get_schema_icon_svg($type) {
                                                $type = strtolower(trim((string)$type));
                                                $type = str_replace(array(' ', '-', '_'), '', $type);
                                                switch ($type) {
                                                    case 'review':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                                                    case 'aggregaterating':
                                                    case 'rating':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path><path d="M12 17.77V2"></path></svg>';
                                                    case 'organization':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 21a8 8 0 0 0-16 0"></path><circle cx="10" cy="8" r="5"></circle><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"></path></svg>';
                                                    case 'webpage':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>';
                                                    case 'breadcrumblist':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
                                                    case 'medicalclinic':
                                                    case 'medicalentity':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>';
                                                    case 'article':
                                                    case 'blogposting':
                                                    case 'newsarticle':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
                                                    case 'book':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
                                                    case 'carousel':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M10 4v16"></path></svg>';
                                                    case 'course':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>';
                                                    case 'dataset':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>';
                                                    case 'event':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
                                                    case 'faq':
                                                    case 'faqpage':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                                                    case 'factcheck':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                                                    case 'howto':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';
                                                    case 'job':
                                                    case 'jobposting':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                                                    case 'localbusiness':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
                                                    case 'movie':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>';
                                                    case 'music':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>';
                                                    case 'person':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
                                                    case 'product':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>';
                                                    case 'recipe':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
                                                    case 'restaurant':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v4M21 2v20M21 2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4"></path></svg>';
                                                    case 'service':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>';
                                                    case 'software':
                                                    case 'softwareapplication':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>';
                                                    case 'video':
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>';
                                                    default:
                                                        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
                                                }
                                            }
                                        }
                                        $gmb_templates = get_option('gmb_schema_templates', array());
                                        $all_avail_pts = array();
                                        foreach (get_post_types(array('public' => true), 'objects') as $p_slug => $p_obj) {
                                             if ($p_slug === 'attachment') continue;
                                             $all_avail_pts[] = array('slug' => $p_slug, 'label' => !empty($p_obj->labels->name) ? $p_obj->labels->name : $p_slug);
                                        }
                                        $all_avail_cats = array();
                                        foreach (get_categories(array('hide_empty' => false)) as $c_obj) {
                                             $all_avail_cats[] = array('slug' => $c_obj->slug, 'name' => $c_obj->name);
                                        }
                                        ?>

                                        <div class="gmb-settings-panel-header">
                                            <?php if (empty($gmb_templates)) : ?>
                                                <h2 class="gmb-heading-2">Schema Builder &amp; Condition Engine</h2>
                                                <p class="gmb-text-muted">Build custom modular Schema.org blueprints and automatically assign them sitewide or conditionally with smart rules.</p>
                                            <?php else : ?>
                                                <div class="gmb-schema-templates-header">
                                                    <div class="gmb-text-left">
                                                        <h2 class="gmb-heading-2">Schema Builder &amp; Condition Engine</h2>
                                                        <p class="gmb-text-muted">Build custom modular Schema.org blueprints and automatically assign them sitewide or conditionally with smart rules.</p>
                                                    </div>
                                                    <button type="button" class="button button-primary gmb-schema-create-btn" id="gmb-open-new-template-modal-btn">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                        Create Schema Template
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div id="gmb-schema-templates-list-wrapper" data-pts="<?php echo esc_attr(wp_json_encode($all_avail_pts)); ?>" data-cats="<?php echo esc_attr(wp_json_encode($all_avail_cats)); ?>">
                                            <?php if (empty($gmb_templates)) : ?>
                                                <div id="gmb-templates-empty-state" class="gmb-templates-empty-state">
                                                    <div class="gmb-templates-empty-state-icon">
                                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                                    </div>
                                                    <h3 class="gmb-templates-empty-state-title">No Custom Schema Templates Yet</h3>
                                                    <p class="gmb-templates-empty-state-desc">Create reusable Schema blueprints for FAQ, Services, Products, Local Business, HowTo guides, and assign them automatically by category, post type, or sitewide.</p>
                                                    <button type="button" class="button button-primary gmb-templates-empty-state-btn" id="gmb-empty-create-template-btn">
                                                        + Create Your First Template
                                                    </button>
                                                </div>
                                            <?php else : ?>
                                                <div class="gmb-templates-table-wrap">
                                                    <table class="wp-list-table widefat fixed striped gmb-templates-table">
                                                        <thead>
                                                            <tr>
                                                                <th class="gmb-templates-table-th--title">Template Title &amp; Type</th>
                                                                <th class="gmb-templates-table-th--conditions">Assigned Display Conditions</th>
                                                                <th class="gmb-templates-table-th--status">Status</th>
                                                                <th class="gmb-templates-table-th--actions">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="gmb-templates-tbody">
                                                            <?php foreach ($gmb_templates as $tpl) : 
                                                                $t_id = esc_attr($tpl['id'] ?? uniqid());
                                                                $t_title = esc_html($tpl['title'] ?? 'Untitled');
                                                                $t_type = esc_html($tpl['type'] ?? 'Custom');
                                                                $t_status = ($tpl['status'] ?? 'active') === 'active' ? 'active' : 'inactive';
                                                                $conditions = $tpl['conditions'] ?? array();
                                                            ?>
                                                                <tr id="gmb-tpl-row-<?php echo $t_id; ?>" class="gmb-templates-row">
                                                                    <td class="gmb-templates-cell">
                                                                        <div class="gmb-templates-title-text">
                                                                            <?php echo $t_title; ?>
                                                                        </div>
                                                                        <span class="gmb-templates-type-badge">
                                                                            <?php echo $t_type; ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="gmb-templates-cell">
                                                                        <div class="gmb-templates-conditions-wrap">
                                                                            <?php if (empty($conditions)) : ?>
                                                                                <span class="gmb-templates-conditions-empty">No conditions (Inactive)</span>
                                                                            <?php else : ?>
                                                                                <?php foreach ($conditions as $c) : 
                                                                                    $is_exc = ($c['type'] ?? '') === 'exclude';
                                                                                    $tgt = $c['target'] ?? 'entire_site';
                                                                                    $val = $c['value'] ?? '';
                                                                                    $badge_cls = $is_exc ? 'gmb-templates-condition-badge--exclude' : 'gmb-templates-condition-badge--include';
                                                                                    $prefix = $is_exc ? 'EXCLUDE: ' : 'INCLUDE: ';
                                                                                    
                                                                                    $desc = 'Entire Site';
                                                                                    if ($tgt === 'homepage') $desc = 'Homepage';
                                                                                    elseif ($tgt === 'post_type') $desc = 'Post Type (' . $val . ')';
                                                                                    elseif ($tgt === 'taxonomy') $desc = 'Category (' . $val . ')';
                                                                                    elseif ($tgt === 'specific_post') $desc = 'Post ID #' . $val;
                                                                                    elseif ($tgt === 'archives') $desc = 'Archives';
                                                                                ?>
                                                                                    <span class="gmb-templates-condition-badge <?php echo $badge_cls; ?>">
                                                                                        <?php echo $prefix . $desc; ?>
                                                                                    </span>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </td>
                                                                    <td class="gmb-templates-cell gmb-text-center">
                                                                        <label class="rm-switch">
                                                                            <input type="checkbox" class="gmb-toggle-template-status" data-id="<?php echo $t_id; ?>" <?php checked($t_status, 'active'); ?> />
                                                                            <span class="rm-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td class="gmb-templates-cell gmb-text-right">
                                                                        <div class="gmb-templates-actions-wrap">
                                                                            <button type="button" class="button button-small gmb-templates-btn-action gmb-edit-template-btn" data-id="<?php echo $t_id; ?>">Edit</button>
                                                                            <button type="button" class="button button-small gmb-templates-btn-action gmb-duplicate-template-btn" data-id="<?php echo $t_id; ?>">Duplicate</button>
                                                                            <button type="button" class="button button-small gmb-templates-btn-delete gmb-delete-template-btn" data-id="<?php echo $t_id; ?>">Delete</button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Interactive Schema Generator & Builder Modal (Rank Math Workflow) -->
                                        <div id="gmb-template-builder-modal" class="gmb-modal-overlay">
                                            <div class="gmb-modal-dialog">
                                                
                                                <input type="hidden" id="gmb-modal-tpl-id" value="" />
                                                <input type="hidden" id="gmb-modal-tpl-type" value="FAQPage" />
                                                <input type="hidden" id="gmb-modal-tpl-status" value="active" />

                                                <!-- ========================================== -->
                                                <!-- VIEW 1: SCHEMA GENERATOR (CATALOG)         -->
                                                <!-- ========================================== -->
                                                <div id="gmb-modal-view-catalog" class="gmb-modal-view-catalog">
                                                    <!-- Catalog Header -->
                                                    <div class="gmb-catalog-modal-header">
                                                        <h3 class="gmb-catalog-modal-title">
                                                            Schema Generator
                                                        </h3>
                                                        <button type="button" class="gmb-modal-close-trigger gmb-modal-close-btn">
                                                            
                                                        </button>
                                                    </div>

                                                    <!-- Catalog Tabs -->
                                                    <div class="gmb-catalog-tabs-bar">
                                                        <div class="gmb-catalog-tabs-group">
                                                            <button type="button" id="gmb-cat-tab-templates" class="gmb-cat-nav-tab active">
                                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                                                Schema Templates
                                                            </button>
                                                            <button type="button" id="gmb-cat-tab-import" class="gmb-cat-nav-tab">
                                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                                Import
                                                            </button>
                                                            <button type="button" id="gmb-cat-tab-custom" class="gmb-cat-nav-tab">
                                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                                Custom Schema
                                                            </button>
                                                        </div>
                                                        <div class="gmb-text-muted" title="Select a Schema type to generate structured data for rich snippets in Google Search.">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                        </div>
                                                    </div>

                                                    <!-- TAB 1 PANEL: SCHEMA TEMPLATES CATALOG -->
                                                    <div id="gmb-cat-panel-templates" class="gmb-cat-panel">
                                                        <!-- Sub-Filter / Search Toolbar -->
                                                        <div class="gmb-catalog-toolbar">
                                                            <div class="gmb-catalog-source-radios">
                                                                <label class="gmb-catalog-radio-label">
                                                                    <input type="radio" name="gmb_catalog_source" id="gmb-catalog-radio-catalog" value="catalog" checked class="gmb-m-0" />
                                                                    Schema Catalog
                                                                </label>
                                                                <label class="gmb-catalog-radio-label gmb-text-muted gmb-font-regular">
                                                                    <input type="radio" name="gmb_catalog_source" id="gmb-catalog-radio-saved" value="saved" class="gmb-m-0" />
                                                                    Your Templates
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <input type="text" id="gmb-catalog-search-input" placeholder="Search..." class="gmb-catalog-search-input" />
                                                            </div>
                                                        </div>

                                                        <!-- Scrollable Catalog Cards Grid Area -->
                                                        <div class="gmb-catalog-cards-scroll">
                                                            <div id="gmb-catalog-cards-grid" class="gmb-schema-template-grid">
                                                                
                                                                <?php
                                                                $catalog_schemas = array(
                                                                    array('type' => 'AggregateRating', 'name' => 'Aggregate Rating', 'icon_key' => 'aggregaterating'),
                                                                    array('type' => 'Article', 'name' => 'Article', 'icon_key' => 'article'),
                                                                    array('type' => 'Book', 'name' => 'Book', 'icon_key' => 'book'),
                                                                    array('type' => 'BreadcrumbList', 'name' => 'Breadcrumbs', 'icon_key' => 'breadcrumblist'),
                                                                    array('type' => 'Carousel', 'name' => 'Carousel', 'icon_key' => 'carousel'),
                                                                    array('type' => 'Course', 'name' => 'Course', 'icon_key' => 'course'),
                                                                    array('type' => 'Dataset', 'name' => 'Dataset', 'icon_key' => 'dataset'),
                                                                    array('type' => 'Event', 'name' => 'Event', 'icon_key' => 'event'),
                                                                    array('type' => 'FAQPage', 'name' => 'FAQ', 'icon_key' => 'faqpage'),
                                                                    array('type' => 'FactCheck', 'name' => 'FactCheck', 'icon_key' => 'factcheck'),
                                                                    array('type' => 'HowTo', 'name' => 'HowTo', 'icon_key' => 'howto'),
                                                                    array('type' => 'JobPosting', 'name' => 'Job Posting', 'icon_key' => 'jobposting'),
                                                                    array('type' => 'LocalBusiness', 'name' => 'Local Business', 'icon_key' => 'localbusiness'),
                                                                    array('type' => 'MedicalClinic', 'name' => 'Medical Clinic', 'icon_key' => 'medicalclinic'),
                                                                    array('type' => 'Movie', 'name' => 'Movie', 'icon_key' => 'movie'),
                                                                    array('type' => 'Music', 'name' => 'Music', 'icon_key' => 'music'),
                                                                    array('type' => 'Organization', 'name' => 'Organization', 'icon_key' => 'organization'),
                                                                    array('type' => 'Person', 'name' => 'Person', 'icon_key' => 'person'),
                                                                    array('type' => 'Product', 'name' => 'Product', 'icon_key' => 'product'),
                                                                    array('type' => 'Recipe', 'name' => 'Recipe', 'icon_key' => 'recipe'),
                                                                    array('type' => 'Restaurant', 'name' => 'Restaurant', 'icon_key' => 'restaurant'),
                                                                    array('type' => 'Review', 'name' => 'Review', 'icon_key' => 'review'),
                                                                    array('type' => 'Service', 'name' => 'Service', 'icon_key' => 'service'),
                                                                    array('type' => 'SoftwareApplication', 'name' => 'Software Application', 'icon_key' => 'softwareapplication'),
                                                                    array('type' => 'Video', 'name' => 'Video', 'icon_key' => 'video'),
                                                                    array('type' => 'WebPage', 'name' => 'Web Page', 'icon_key' => 'webpage'),
                                                                );

                                                                foreach ($catalog_schemas as $cs) :
                                                                ?>
                                                                    <div class="gmb-schema-template-card" data-title="<?php echo esc_attr($cs['name']); ?>" data-type="<?php echo esc_attr($cs['type']); ?>">
                                                                        <div class="gmb-schema-template-info">
                                                                            <span class="gmb-schema-template-icon">
                                                                                <?php echo gmb_ranker_get_schema_icon_svg($cs['icon_key']); ?>
                                                                            </span>
                                                                            <span class="gmb-schema-template-name"><?php echo esc_html($cs['name']); ?></span>
                                                                        </div>
                                                                        <button type="button" class="button button-small gmb-use-schema-btn" data-type="<?php echo esc_attr($cs['type']); ?>">+ Use</button>
                                                                    </div>
                                                                <?php endforeach; ?>

                                                            </div>

                                                            <!-- Container for Saved Templates view in Catalog -->
                                                            <div id="gmb-saved-templates-grid" class="gmb-saved-templates-grid">
                                                                <!-- Populated via JS -->
                                                            </div>

                                                            <!-- Empty Search Results State -->
                                                            <div id="gmb-catalog-no-results" class="gmb-cat-empty-state">
                                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" class="gmb-mb-8"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                                <p class="gmb-text-secondary gmb-font-semibold">No schema types match your search</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TAB 2 PANEL: IMPORT SCHEMA -->
                                                    <div id="gmb-cat-panel-import" class="gmb-cat-panel">
                                                        <div class="gmb-cat-form-card">
                                                            <div class="gmb-mb-16">
                                                                <label class="gmb-form-label">Import Schema Code from</label>
                                                                <select id="gmb-catalog-import-source" class="gmb-select">
                                                                    <option value="jsonld" selected>JSON-LD/Custom Code</option>
                                                                    <option value="html">HTML / Webpage Source Code</option>
                                                                    <option value="url">URL</option>
                                                                </select>
                                                            </div>

                                                            <div id="gmb-catalog-import-code-wrap" class="gmb-mb-20">
                                                                <label id="gmb-catalog-import-code-label" class="gmb-form-label">Custom JSON-LD Code</label>
                                                                <textarea id="gmb-catalog-import-textarea" rows="11" placeholder='{"@context": "https://schema.org", "@type": "MedicalClinic", ...}' class="gmb-textarea gmb-textarea--code"></textarea>
                                                            </div>

                                                            <div id="gmb-catalog-import-url-wrap" class="gmb-mb-20">
                                                                <label class="gmb-form-label">Page URL to Extract Schema</label>
                                                                <div class="gmb-flex-gap-sm">
                                                                    <input type="url" id="gmb-catalog-import-url-input" placeholder="https://example.com/page-with-schema" class="gmb-input" />
                                                                </div>
                                                                <p class="gmb-form-help">Enter any live webpage URL containing JSON-LD or Microdata structured markup to extract and convert.</p>
                                                            </div>

                                                            <div id="gmb-catalog-import-error" class="gmb-callout gmb-callout--danger"></div>

                                                            <div>
                                                                <button type="button" class="button button-primary gmb-btn--primary" id="gmb-catalog-import-process-btn">
                                                                    Process Code
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TAB 3 PANEL: CUSTOM SCHEMA -->
                                                    <div id="gmb-cat-panel-custom" class="gmb-cat-panel">
                                                        <div class="gmb-cat-form-card gmb-cat-form-card--gap">
                                                            <div>
                                                                <h4 class="gmb-heading-3">Create Custom Schema Blueprint</h4>
                                                                <p class="gmb-text-muted">Build any Schema.org structured data entity with custom properties, tailored nested groups, and smart display conditions.</p>
                                                            </div>

                                                            <div class="gmb-grid-2col">
                                                                <div>
                                                                    <label class="gmb-form-label">Custom Schema Type (@type) *</label>
                                                                    <input type="text" id="gmb-custom-type-input" list="gmb-custom-types-datalist" value="Custom" placeholder="e.g. MedicalClinic, Dentist, RealEstateAgent, etc." class="gmb-input" />
                                                                    <datalist id="gmb-custom-types-datalist">
                                                                        <option value="MedicalClinic">
                                                                        <option value="Dentist">
                                                                        <option value="PhysiotherapyClinic">
                                                                        <option value="Hospital">
                                                                        <option value="EmergencyService">
                                                                        <option value="VeterinaryCare">
                                                                        <option value="RealEstateAgent">
                                                                        <option value="LegalService">
                                                                        <option value="FinancialService">
                                                                        <option value="TouristAttraction">
                                                                        <option value="Organization">
                                                                        <option value="ProfessionalService">
                                                                        <option value="Specialty">
                                                                        <option value="Thing">
                                                                        <option value="Custom">
                                                                    </datalist>
                                                                </div>
                                                                <div>
                                                                    <label class="gmb-form-label">Schema Template Title *</label>
                                                                    <input type="text" id="gmb-custom-title-input" value="Custom Schema Blueprint" placeholder="e.g. Medical Clinic Blueprint" class="gmb-input" />
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label class="gmb-form-label">Starter Blueprint Preset</label>
                                                                <select id="gmb-custom-preset-select" class="gmb-select">
                                                                    <option value="blank" selected>Blank Starter Schema</option>
                                                                    <option value="organization">Custom Organization / Company</option>
                                                                    <option value="healthcare">Custom Healthcare / Medical Clinic</option>
                                                                    <option value="service">Custom Professional Service</option>
                                                                    <option value="creative">Custom Creative Work / Article</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="gmb-form-label">Initial JSON-LD Schema Code</label>
                                                                <textarea id="gmb-custom-preview-textarea" rows="7" spellcheck="false" class="gmb-textarea gmb-textarea--code"></textarea>
                                                            </div>

                                                            <div class="gmb-flex-start">
                                                                <button type="button" class="button button-primary gmb-btn--primary" id="gmb-custom-create-btn">
                                                                    Build Custom Schema &rarr;
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- ========================================== -->
                                                <!-- VIEW 2: SCHEMA BUILDER (EDITOR)            -->
                                                <!-- ========================================== -->
                                                <div id="gmb-modal-view-builder" class="gmb-modal-view-builder">
                                                    <!-- Builder Header -->
                                                    <div class="gmb-builder-header">
                                                        <div class="gmb-flex-center-gap-md">
                                                            <button type="button" id="gmb-builder-back-btn" class="gmb-builder-back-btn">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                                Back to Catalog
                                                            </button>
                                                            <span class="gmb-text-subtle">|</span>
                                                            <h3 id="gmb-builder-modal-title" class="gmb-builder-title">
                                                                Schema Builder
                                                            </h3>
                                                        </div>
                                                        <div class="gmb-flex-center-gap-sm">
                                                            <span id="gmb-builder-active-type-badge" class="gmb-builder-badge">
                                                                FAQPage
                                                            </span>
                                                            <button type="button" class="gmb-modal-close-trigger">
                                                                
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Builder Navigation Sub-Tabs -->
                                                    <div class="gmb-builder-subtabs">
                                                        <button type="button" id="gmb-builder-tab-btn-edit" class="gmb-builder-tab-btn active">
                                                            Edit
                                                        </button>
                                                        <button type="button" id="gmb-builder-tab-btn-code" class="gmb-builder-tab-btn">
                                                            Code Validation
                                                        </button>
                                                        <button type="button" id="gmb-builder-tab-btn-conditions" class="gmb-builder-tab-btn">
                                                            Display Conditions
                                                        </button>
                                                    </div>

                                                    <!-- Builder Scrollable Panels Body -->
                                                    <div class="gmb-builder-body">
                                                        
                                                        <!-- TAB 1: EDIT (Visual Property Tree) -->
                                                        <div id="gmb-builder-panel-edit" class="gmb-builder-panel active">
                                                            <!-- Template Name Input -->
                                                            <div class="gmb-builder-card gmb-builder-card--clean">
                                                                <label class="gmb-form-label">Schema Template Name *</label>
                                                                <input type="text" id="gmb-modal-tpl-title" placeholder="Enter template name (e.g. Healthcare FAQ)" class="gmb-input" />
                                                            </div>

                                                            <!-- Root Type Card -->
                                                            <div class="gmb-builder-card">
                                                                <div class="gmb-builder-root-row">
                                                                    <div class="gmb-builder-root-label">@type</div>
                                                                    <input type="text" id="gmb-vis-type-val" readonly value="FAQPage" class="gmb-builder-root-input" />
                                                                </div>

                                                                <!-- Tailored Visual Fields (FAQ, Service, LocalBusiness, etc.) -->
                                                                <div id="gmb-visual-fields-content" class="gmb-flex-col-gap-md">
                                                                    <!-- Rendered dynamically via JS -->
                                                                </div>

                                                                <!-- Rank Math Style Action Links -->
                                                                <div class="gmb-builder-actions">
                                                                    <button type="button" id="gmb-btn-add-property" class="gmb-builder-action-btn">
                                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                                        Add Property
                                                                    </button>
                                                                    <button type="button" id="gmb-btn-add-property-group" class="gmb-builder-action-btn">
                                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                                        Add Property Group
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- TAB 2: CODE VALIDATION (JSON-LD Inspector) -->
                                                        <div id="gmb-builder-panel-code" class="gmb-builder-panel">
                                                            <div class="gmb-ide-card">
                                                                <!-- Code Header Tools -->
                                                                <div class="gmb-ide-tools">
                                                                    <div class="gmb-flex-center-gap-sm">
                                                                        <span class="gmb-ide-file-title">
                                                                            schema-graph.jsonld
                                                                        </span>
                                                                        <span id="gmb-json-syntax-indicator" class="gmb-ide-badge gmb-ide-badge--valid">
                                                                             Valid JSON-LD Syntax
                                                                        </span>
                                                                    </div>
                                                                    <div class="gmb-flex-gap-sm">
                                                                        <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-load-preset-btn">
                                                                            ↺ Reset Blueprint
                                                                        </button>
                                                                        <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-format-json-btn">
                                                                            Format JSON
                                                                        </button>
                                                                        <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-copy-json-btn">
                                                                            Copy Code
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <!-- Protected Dark IDE Console -->
                                                                <div class="gmb-ide-console">
                                                                    <textarea id="gmb-modal-tpl-json" rows="12" spellcheck="false" class="gmb-ide-textarea"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- TAB 3: DISPLAY CONDITIONS -->
                                                        <div id="gmb-builder-panel-conditions" class="gmb-builder-panel">
                                                            <div class="gmb-conditions-card">
                                                                <div class="gmb-conditions-header">
                                                                    <div>
                                                                        <h4 class="gmb-conditions-title">Display Conditions (Automated Rules)</h4>
                                                                        <span class="gmb-text-muted">Specify which posts, pages, or categories automatically output this structured data.</span>
                                                                    </div>
                                                                    <button type="button" id="gmb-add-condition-row-btn" class="gmb-btn-add-condition">
                                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                                        Add Condition Rule
                                                                    </button>
                                                                </div>

                                                                <div id="gmb-modal-conditions-container" class="gmb-flex-col-gap-sm">
                                                                    <!-- Dynamically rendered via JS -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Builder Footer -->
                                                    <div class="gmb-builder-footer">
                                                        <div class="gmb-flex-center-gap-sm">
                                                            <label class="rm-switch">
                                                                <input type="checkbox" id="gmb-modal-status-toggle" checked />
                                                                <span class="rm-slider"></span>
                                                            </label>
                                                            <span id="gmb-modal-status-label" class="gmb-form-label">Active Template</span>
                                                        </div>
                                                        <div class="gmb-flex-center-gap-sm">
                                                            <button type="button" id="gmb-cancel-template-modal-btn" class="button gmb-btn--secondary">Cancel</button>
                                                            <button type="button" id="gmb-save-template-modal-btn" class="button button-primary gmb-btn--primary">
                                                                Save
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- SUBTAB 2: Post Types Schema -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'post-types') ? 'active' : ''; ?>" id="gmb-subtab-schema-post-types">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Default Post Type Schema Mapping</h2>
                                            <p class="gmb-text-muted">Assign default structured data entities for each public post type. Individual posts can override these defaults in the SEO metabox.</p>
                                        </div>

                                        <?php 
                                        $public_post_types_schema = get_post_types(array('public' => true), 'objects');
                                        foreach ($public_post_types_schema as $pt_slug => $pt_obj) :
                                            if ($pt_slug === 'attachment') continue;
                                            
                                            // Determine current schema type
                                            $curr_schema = get_option('gmb_' . $pt_slug . '_schema_type', '');
                                            if (empty($curr_schema)) {
                                                if ($pt_slug === 'post') $curr_schema = get_option('gmb_posts_schema_type', 'article');
                                                elseif ($pt_slug === 'page') $curr_schema = get_option('gmb_pages_schema_type', 'none');
                                                elseif ($pt_slug === 'services' || $pt_slug === 'service') $curr_schema = get_option('gmb_services_schema_type', 'service');
                                                elseif (in_array($pt_slug, array('service_locations', 'service_location', 'locations', 'location'))) $curr_schema = get_option('gmb_service_locations_schema_type', 'localbusiness');
                                                elseif (in_array($pt_slug, array('team_members', 'team_member', 'team'))) $curr_schema = get_option('gmb_team_members_schema_type', 'person');
                                                elseif ($pt_slug === 'product') $curr_schema = 'product';
                                                else $curr_schema = 'none';
                                            }

                                            $curr_headline = get_option('gmb_' . $pt_slug . '_schema_headline', '%seo_title%');
                                            $curr_desc = get_option('gmb_' . $pt_slug . '_schema_desc', '%seo_description%');
                                            $curr_article_type = get_option('gmb_' . $pt_slug . '_article_type', 'article');
                                        ?>
                                            <div class="gmb-schema-pt-card">
                                                <div class="gmb-schema-pt-header">
                                                    <div class="gmb-flex-center-gap-sm">
                                                        <h3 class="gmb-schema-pt-title"><?php echo esc_html($pt_obj->labels->singular_name ?: $pt_obj->label); ?></h3>
                                                        <span class="gmb-schema-pt-badge"><?php echo esc_html($pt_slug); ?></span>
                                                    </div>
                                                </div>

                                                <div class="gmb-schema-pt-grid">
                                                    <!-- Schema Type -->
                                                    <div>
                                                        <label class="gmb-form-label">Schema Type</label>
                                                        <select name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_type" class="gmb-select">
                                                            <option value="none" <?php selected($curr_schema, 'none'); ?>>None (Disable Auto Schema)</option>
                                                            <option value="article" <?php selected($curr_schema, 'article'); ?>>Article</option>
                                                            <option value="service" <?php selected($curr_schema, 'service'); ?>>Service</option>
                                                            <option value="localbusiness" <?php selected($curr_schema, 'localbusiness'); ?>>LocalBusiness</option>
                                                            <option value="product" <?php selected($curr_schema, 'product'); ?>>Product (WooCommerce compatible)</option>
                                                            <option value="person" <?php selected($curr_schema, 'person'); ?>>Person</option>
                                                            <option value="faq" <?php selected($curr_schema, 'faq'); ?>>FAQPage</option>
                                                            <option value="course" <?php selected($curr_schema, 'course'); ?>>Course</option>
                                                            <option value="event" <?php selected($curr_schema, 'event'); ?>>Event</option>
                                                            <option value="jobposting" <?php selected($curr_schema, 'jobposting'); ?>>JobPosting</option>
                                                            <option value="recipe" <?php selected($curr_schema, 'recipe'); ?>>Recipe</option>
                                                            <option value="restaurant" <?php selected($curr_schema, 'restaurant'); ?>>Restaurant</option>
                                                            <option value="software" <?php selected($curr_schema, 'software'); ?>>SoftwareApplication</option>
                                                            <option value="video" <?php selected($curr_schema, 'video'); ?>>VideoObject</option>
                                                            <option value="book" <?php selected($curr_schema, 'book'); ?>>Book</option>
                                                        </select>
                                                    </div>

                                                    <!-- Article Sub-Type if applicable -->
                                                    <?php if ($pt_slug === 'post' || $curr_schema === 'article') : ?>
                                                    <div>
                                                        <label class="gmb-form-label">Article Type</label>
                                                        <select name="gmb_<?php echo esc_attr($pt_slug); ?>_article_type" class="gmb-select">
                                                            <option value="article" <?php selected($curr_article_type, 'article'); ?>>Article (General)</option>
                                                            <option value="blogpost" <?php selected($curr_article_type, 'blogpost'); ?>>BlogPosting</option>
                                                            <option value="newsarticle" <?php selected($curr_article_type, 'newsarticle'); ?>>NewsArticle</option>
                                                        </select>
                                                    </div>
                                                    <?php endif; ?>

                                                    <!-- Headline Template -->
                                                    <div>
                                                        <label class="gmb-form-label">Headline Template</label>
                                                        <input type="text" name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_headline" value="<?php echo esc_attr($curr_headline); ?>" class="gmb-input" placeholder="%seo_title%" />
                                                    </div>

                                                    <!-- Description Template -->
                                                    <div>
                                                        <label class="gmb-form-label">Description Template</label>
                                                        <input type="text" name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_desc" value="<?php echo esc_attr($curr_desc); ?>" class="gmb-input" placeholder="%seo_description%" />
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="gmb-settings-footer justify-end">
                                            <input type="submit" name="submit" class="button button-primary button-large gmb-btn--primary" value="Save Changes" />
                                        </div>
                                    </div>

                                    <!-- SUBTAB 3: Knowledge Graph & Entity -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'knowledge') ? 'active' : ''; ?>" id="gmb-subtab-schema-knowledge">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Knowledge Graph &amp; Publisher Entity</h2>
                                            <p class="gmb-text-muted">Configure publisher identity, physical address, and official social presence representing your brand in Google Knowledge Panels.</p>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- Publisher Type -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Publisher Type
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <select name="gmb_local_seo_type" class="gmb-select gmb-input--min-280">
                                                        <option value="Organization" <?php selected(get_option('gmb_local_seo_type', 'Organization'), 'Organization'); ?>>Organization (Default)</option>
                                                        <option value="LocalBusiness" <?php selected(get_option('gmb_local_seo_type', 'Organization'), 'LocalBusiness'); ?>>LocalBusiness (Physical Store / Firm)</option>
                                                        <option value="MedicalBusiness" <?php selected(get_option('gmb_local_seo_type', 'Organization'), 'MedicalBusiness'); ?>>MedicalBusiness (Clinic / Healthcare)</option>
                                                        <option value="Person" <?php selected(get_option('gmb_local_seo_type', 'Organization'), 'Person'); ?>>Person (Personal Brand / Influencer)</option>
                                                    </select>
                                                    <p class="gmb-form-help">Select whether this site represents a business organization, local healthcare facility, or personal brand.</p>
                                                </div>
                                            </div>

                                            <!-- Publisher Legal Name -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Publisher Legal Name
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <input type="text" name="gmb_local_seo_name" value="<?php echo esc_attr(get_option('gmb_local_seo_name', get_bloginfo('name'))); ?>" class="gmb-input gmb-input--max-520" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                                    <p class="gmb-form-help">Official company or organization name rendered in Schema.org publisher tags.</p>
                                                </div>
                                            </div>

                                            <!-- Publisher Logo -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Publisher Logo
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-flex-center-gap-sm gmb-mb-8">
                                                        <input type="text" id="gmb_schema_logo_input" name="gmb_local_seo_logo" value="<?php echo esc_attr(get_option('gmb_local_seo_logo', '')); ?>" class="gmb-input gmb-input--max-420" placeholder="https://example.com/logo.png" />
                                                        <button type="button" class="button gmb-btn--secondary" id="gmb_schema_upload_logo_btn">Upload</button>
                                                    </div>
                                                    <p class="gmb-form-help">Square or transparent branding logo (min 512x512px) used in Google Knowledge Panels.</p>
                                                    <?php $curr_logo = get_option('gmb_local_seo_logo', ''); ?>
                                                    <div id="gmb_schema_logo_preview_wrap" class="gmb-thumb-preview <?php echo empty($curr_logo) ? 'gmb-hidden' : ''; ?>">
                                                        <img id="gmb_schema_logo_preview" src="<?php echo esc_url($curr_logo); ?>" alt="Logo Preview" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Phone & Email -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Contact Details
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-grid-2 gmb-input--max-520">
                                                        <input type="text" name="gmb_local_seo_phone" value="<?php echo esc_attr(get_option('gmb_local_seo_phone', '')); ?>" placeholder="Phone (+1 555-0199)" class="gmb-input" />
                                                        <input type="email" name="gmb_local_seo_email" value="<?php echo esc_attr(get_option('gmb_local_seo_email', get_bloginfo('admin_email'))); ?>" placeholder="Contact Email" class="gmb-input" />
                                                    </div>
                                                    <p class="gmb-form-help">Customer support telephone and verified email for structured data <code>ContactPoint</code>.</p>
                                                </div>
                                            </div>

                                            <!-- Physical Address -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Physical Address
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-flex-col-gap-10 gmb-input--max-520">
                                                        <input type="text" name="gmb_local_seo_address_street" value="<?php echo esc_attr(get_option('gmb_local_seo_address_street', get_option('gmb_local_street_address', ''))); ?>" placeholder="Street Address, Suite / Floor" class="gmb-input" />
                                                        <div class="gmb-grid-2">
                                                            <input type="text" name="gmb_local_seo_address_locality" value="<?php echo esc_attr(get_option('gmb_local_seo_address_locality', get_option('gmb_local_locality', ''))); ?>" placeholder="City / Locality" class="gmb-input" />
                                                            <input type="text" name="gmb_local_seo_address_region" value="<?php echo esc_attr(get_option('gmb_local_seo_address_region', get_option('gmb_local_region', ''))); ?>" placeholder="State / Province / Region" class="gmb-input" />
                                                        </div>
                                                        <div class="gmb-grid-2">
                                                            <input type="text" name="gmb_local_seo_address_postal" value="<?php echo esc_attr(get_option('gmb_local_seo_address_postal', get_option('gmb_local_postal_code', ''))); ?>" placeholder="Postal / ZIP Code" class="gmb-input" />
                                                            <input type="text" name="gmb_local_seo_address_country" value="<?php echo esc_attr(get_option('gmb_local_seo_address_country', get_option('gmb_local_country', ''))); ?>" placeholder="Country (e.g. US, UK, CA, AU)" class="gmb-input" />
                                                        </div>
                                                    </div>
                                                    <p class="gmb-form-help">Full physical street address required by Google Rich Results for LocalBusiness and Medical clinics.</p>
                                                </div>
                                            </div>

                                            <!-- Geo Coordinates & Price Range -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Geo Coordinates &amp; Pricing
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-flex-col-gap-10 gmb-input--max-520">
                                                        <div class="gmb-grid-2">
                                                            <input type="text" name="gmb_local_business_lat" value="<?php echo esc_attr(get_option('gmb_local_business_lat', '')); ?>" placeholder="Latitude (e.g. 40.7128)" class="gmb-input" />
                                                            <input type="text" name="gmb_local_business_lng" value="<?php echo esc_attr(get_option('gmb_local_business_lng', '')); ?>" placeholder="Longitude (e.g. -74.0060)" class="gmb-input" />
                                                        </div>
                                                        <div class="gmb-grid-2">
                                                            <input type="text" name="gmb_local_business_price_range" value="<?php echo esc_attr(get_option('gmb_local_business_price_range', '$$')); ?>" placeholder="Price Range (e.g. $$)" class="gmb-input" />
                                                            <input type="text" name="gmb_local_business_opening_hours" value="<?php echo esc_attr(get_option('gmb_local_business_opening_hours', 'Mo-Fr 09:00-18:00')); ?>" placeholder="Opening Hours (e.g. Mo-Fr 09:00-18:00)" class="gmb-input" />
                                                        </div>
                                                    </div>
                                                    <p class="gmb-form-help">Exact latitude and longitude for Google Maps schema pin, and price indicator ($ to $$$$).</p>
                                                </div>
                                            </div>

                                            <!-- Social SameAs Links -->
                                            <div class="gmb-settings-row gmb-settings-row--noborder">
                                                <div class="gmb-settings-label-col">
                                                    Social SameAs Links
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <div class="gmb-grid-2 gmb-input--max-520 gmb-flex-col-gap-10">
                                                        <input type="url" name="gmb_social_facebook_page_url" value="<?php echo esc_attr(get_option('gmb_social_facebook_page_url', '')); ?>" placeholder="Facebook Page URL" class="gmb-input" />
                                                        <input type="text" name="gmb_social_twitter_username" value="<?php echo esc_attr(get_option('gmb_social_twitter_username', '')); ?>" placeholder="Twitter / X Profile (@username)" class="gmb-input" />
                                                        <input type="url" name="gmb_social_linkedin_url" value="<?php echo esc_attr(get_option('gmb_social_linkedin_url', '')); ?>" placeholder="LinkedIn Company URL" class="gmb-input" />
                                                        <input type="url" name="gmb_social_youtube_url" value="<?php echo esc_attr(get_option('gmb_social_youtube_url', '')); ?>" placeholder="YouTube Channel URL" class="gmb-input" />
                                                        <input type="url" name="gmb_social_instagram_url" value="<?php echo esc_attr(get_option('gmb_social_instagram_url', '')); ?>" placeholder="Instagram URL" class="gmb-input" />
                                                        <input type="url" name="gmb_social_wikipedia_url" value="<?php echo esc_attr(get_option('gmb_social_wikipedia_url', '')); ?>" placeholder="Wikipedia / Wikidata URL" class="gmb-input" />
                                                    </div>
                                                    <p class="gmb-form-help">URLs of your verified official social profiles used to enrich <code>sameAs</code> knowledge graph links.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                            <input type="submit" name="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                        </div>
                                    </div>

                                    <!-- SUBTAB 4: Custom Code & Validator -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'custom') ? 'active' : ''; ?>" id="gmb-subtab-schema-custom">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">Custom JSON-LD &amp; Live Schema Validator</h2>
                                            <p class="gmb-text-muted">Inject customized structured data into page head and test live URLs directly with Google Rich Results.</p>
                                        </div>

                                        <div class="gmb-card-settings-list">
                                            <!-- Sitewide Custom JSON-LD -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Custom JSON-LD
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <textarea name="gmb_schema_custom_jsonld" rows="8" class="gmb-schema-custom-jsonld-area gmb-textarea--code gmb-input--max-520" placeholder="<?php echo esc_attr("{\n  \"@context\": \"https://schema.org\",\n  \"@type\": \"Organization\",\n  \"name\": \"%sitename%\",\n  \"url\": \"%siteurl%\"\n}"); ?>"><?php echo esc_textarea(get_option('gmb_schema_custom_jsonld', '')); ?></textarea>
                                                    <p class="gmb-form-help">Enter raw JSON-LD (without <code>&lt;script&gt;</code> tags). Supports variables: <code>%sitename%</code>, <code>%siteurl%</code>, <code>%phone%</code>, <code>%email%</code>, <code>%currentyear%</code>.</p>
                                                </div>
                                            </div>

                                            <!-- Integration Compatibility -->
                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Rank Math Graph
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_integrate_rankmath" value="1" <?php checked('1', get_option('gmb_schema_integrate_rankmath', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">If another SEO plugin (Rank Math) is active on this site, merge GMB Ranker schemas seamlessly into the unified graph to avoid conflicts.</p>
                                                </div>
                                            </div>

                                            <div class="gmb-settings-row">
                                                <div class="gmb-settings-label-col">
                                                    Yoast SEO Graph
                                                </div>
                                                <div class="gmb-settings-input-col">
                                                    <label class="gmb-switch">
                                                        <input type="checkbox" name="gmb_schema_integrate_yoast" value="1" <?php checked('1', get_option('gmb_schema_integrate_yoast', '1')); ?> />
                                                        <span class="gmb-slider round"></span>
                                                    </label>
                                                    <p class="gmb-form-help">If Yoast SEO is installed, attaches GMB Ranker schemas directly into Yoast's <code>@graph</code> structured data tree.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Live Testing Tool -->
                                        <div class="gmb-schema-validator-card">
                                            <h3 class="gmb-schema-validator-title">Test Schema in Official Validators</h3>
                                            <p class="gmb-schema-validator-desc">Enter a live URL from your website to test its Schema.org rich snippet compliance.</p>
                                            <div class="gmb-schema-validator-tools">
                                                <input type="url" id="gmb_test_schema_url" value="<?php echo esc_url(home_url('/')); ?>" placeholder="https://example.com/" class="gmb-input gmb-flex-1 gmb-input--min-280" />
                                                <button type="button" class="button gmb-btn-validator" id="gmb_btn_test_google">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" class="gmb-flex-shrink-0"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.94 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
                                                    <span>Google Rich Results</span>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-validator-icon gmb-flex-shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                </button>
                                                <button type="button" class="button gmb-btn-validator" id="gmb_btn_test_schema_org">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-flex-shrink-0"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                                    <span>Schema.org Validator</span>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-validator-icon gmb-flex-shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                            <input type="submit" name="submit" class="button button-primary gmb-btn--primary" value="Save Changes" />
                                        </div>
                                    </div>

                                    <!-- SUBTAB 5: 1-Click Schema Presets -->
                                    <div class="gmb-subtab-panel <?php echo ($active_schema_sub === 'presets') ? 'active' : ''; ?>" id="gmb-subtab-schema-presets">
                                        <div class="gmb-settings-panel-header">
                                            <h2 class="gmb-heading-2">1-Click Schema Architecture Presets</h2>
                                            <p class="gmb-text-muted">Apply recommended schema industry blueprints tailored to your site type with a single click.</p>
                                        </div>

                                        <div class="gmb-schema-preset-grid">
                                            <!-- Preset 1: Healthcare / Medical -->
                                            <div class="gmb-card gmb-preset-card">
                                                <div class="gmb-preset-card-content">
                                                    <div class="gmb-schema-preset-icon gmb-schema-preset-icon--blue">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg>
                                                    </div>
                                                    <h3 class="gmb-heading-3">Healthcare &amp; Agency</h3>
                                                    <p class="gmb-text-muted gmb-mb-16">Configures MedicalBusiness / LocalBusiness, Service entities with organization provider, and Person schema for clinical staff.</p>
                                                </div>
                                                <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--blue" data-preset="healthcare">Apply Blueprint</button>
                                            </div>

                                            <!-- Preset 2: Local Business -->
                                            <div class="gmb-card gmb-preset-card">
                                                <div class="gmb-preset-card-content">
                                                    <div class="gmb-schema-preset-icon gmb-schema-preset-icon--green">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                                    </div>
                                                    <h3 class="gmb-heading-3">Local Business &amp; Multi-Location</h3>
                                                    <p class="gmb-text-muted gmb-mb-16">Sets homepage as LocalBusiness, enables Sitelinks SearchBox, and generates localized business schemas for each service location.</p>
                                                </div>
                                                <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--green" data-preset="local_business">Apply Blueprint</button>
                                            </div>

                                            <!-- Preset 3: News & Publisher -->
                                            <div class="gmb-card gmb-preset-card">
                                                <div class="gmb-preset-card-content">
                                                    <div class="gmb-schema-preset-icon gmb-schema-preset-icon--purple">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                                                    </div>
                                                    <h3 class="gmb-heading-3">News &amp; Content Publisher</h3>
                                                    <p class="gmb-text-muted gmb-mb-16">Emits NewsArticle schemas, enables full E-E-A-T Person author linking with social SameAs profiles, and BreadcrumbList schemas.</p>
                                                </div>
                                                <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--purple" data-preset="publisher">Apply Blueprint</button>
                                            </div>

                                            <!-- Preset 4: WooCommerce Store -->
                                            <div class="gmb-card gmb-preset-card">
                                                <div class="gmb-preset-card-content">
                                                    <div class="gmb-schema-preset-icon gmb-schema-preset-icon--orange">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                                    </div>
                                                    <h3 class="gmb-heading-3">WooCommerce Store</h3>
                                                    <p class="gmb-text-muted gmb-mb-16">Optimizes Product rich snippets with live Price, Offer, Currency, Availability (InStock), and SKU tracking for Google Shopping.</p>
                                                </div>
                                                <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--orange" data-preset="ecommerce">Apply Blueprint</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Subtab: Redirections -->
