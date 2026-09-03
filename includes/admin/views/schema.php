<?php
/**
 * Schema & Structured Data Administration View
 *
 * Thin presentation layer consuming canonical GMB_Ranker_SEO_Schema_Registry view model.
 * Direct persistence and database reads are removed.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_tab_name = isset($current_tab) ? $current_tab : (isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '');
$current_page_name = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '');

if ($current_page_name === 'gmb-ranker-schema' || $current_tab_name === 'gmb-ranker-schema') :

    $req_sub = isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : (isset($_POST['gmb_active_subtab']) ? sanitize_key(wp_unslash($_POST['gmb_active_subtab'])) : '');
    $view_model = GMB_Ranker_SEO_Schema_Registry::get_view_model($req_sub);

    $is_module_enabled = $view_model['module_enabled'];
    $active_subtab     = $view_model['active_subtab'];
    $s                 = $view_model['settings'];
    $catalog_schemas   = $view_model['catalog_schemas'];
    $templates         = $view_model['templates'];
    $avail_pts         = $view_model['post_types'];
    $avail_cats        = $view_model['categories'];

    if (!$is_module_enabled) : 
    ?>
        <div class="rm-tab-content active" role="region" aria-label="<?php esc_attr_e('Disabled Schema Module Warning', 'gmb-ranker-seo-automation'); ?>">
            <div class="gmb-empty-state">
                <div class="gmb-empty-state-icon--warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
                <h2 class="gmb-heading-2"><?php esc_html_e('Schema (Structured Data) Module is Disabled', 'gmb-ranker-seo-automation'); ?></h2>
                <p class="gmb-text-muted"><?php esc_html_e('Enable the Schema module to configure rich structured data types (Article, LocalBusiness, Service, Product, FAQ, Person) for Google, Bing, and AI search engines.', 'gmb-ranker-seo-automation'); ?></p>
                <div class="gmb-flex-center-gap-md">
                    <button type="button" class="button button-primary gmb-btn-enable-module gmb-btn--primary" data-module="gmb_ranker_module_schema"><?php esc_html_e('Enable Module', 'gmb-ranker-seo-automation'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="button button-secondary gmb-empty-state-action-btn"><?php esc_html_e('Go to Dashboard', 'gmb-ranker-seo-automation'); ?></a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="rm-tab-content active" id="rm-tab-schema" role="region" aria-label="<?php esc_attr_e('Schema Management', 'gmb-ranker-seo-automation'); ?>">
            <form method="post" action="options.php" novalidate>
                <?php settings_fields('gmb_ranker_schema_group'); ?>
                
                <div class="gmb-sidebar-layout-container">
                    
                    <!-- Sidebar Navigation Column -->
                    <input type="hidden" name="gmb_active_subtab" id="gmb_active_subtab_input" value="<?php echo esc_attr($active_subtab); ?>" />
                    <div class="gmb-sidebar-nav">
                        <ul role="tablist">
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'general') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-general" role="tab" aria-selected="<?php echo ($active_subtab === 'general') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                <?php esc_html_e('General Schema', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'templates') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-templates" role="tab" aria-selected="<?php echo ($active_subtab === 'templates') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <?php esc_html_e('Schema Builder', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'post-types') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-post-types" role="tab" aria-selected="<?php echo ($active_subtab === 'post-types') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <?php esc_html_e('Post Types Schema', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'knowledge') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-knowledge" role="tab" aria-selected="<?php echo ($active_subtab === 'knowledge') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                <?php esc_html_e('Knowledge Graph', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'custom') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-custom" role="tab" aria-selected="<?php echo ($active_subtab === 'custom') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <?php esc_html_e('Custom Code & Test', 'gmb-ranker-seo-automation'); ?>
                            </li>
                            <li class="gmb-sidebar-nav-item <?php echo ($active_subtab === 'presets') ? 'active' : ''; ?>" data-subtab="gmb-subtab-schema-presets" role="tab" aria-selected="<?php echo ($active_subtab === 'presets') ? 'true' : 'false'; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <?php esc_html_e('1-Click Presets', 'gmb-ranker-seo-automation'); ?>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Content Settings Column -->
                    <div class="gmb-sidebar-content-panel">
                        
                        <!-- SUBTAB 1: General Schema -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'general') ? 'active' : ''; ?>" id="gmb-subtab-schema-general" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('General Schema Settings', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Configure foundational sitewide Schema.org entities to establish semantic authority in Google, Bing, and AI indices.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-card-settings-list">
                                <!-- WebSite Schema -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('WebSite Schema', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_enable_website" value="1" <?php checked('1', $s['enable_website']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Injects the top-level @type: WebSite JSON-LD entity on your homepage to define your site brand, publisher reference, and homepage URL.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- WebSite Name -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('WebSite Name', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_schema_website_name" value="<?php echo esc_attr($s['website_name']); ?>" class="regular-text gmb-input gmb-input--max-520" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                        <p class="gmb-form-help"><?php esc_html_e('The canonical name of your website. Defaults to your WordPress Site Title.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- WebSite Alternate Name -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('WebSite Alternate Name', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_schema_website_alt_name" value="<?php echo esc_attr($s['website_alt_name']); ?>" class="regular-text gmb-input gmb-input--max-520" placeholder="e.g. CNN" />
                                        <p class="gmb-form-help"><?php esc_html_e('An alternate brand name, trading name, or abbreviation for your site (e.g. CNN, BBC, Acme Corp).', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- About Page Selection -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('About Page Schema', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php
                                        wp_dropdown_pages(array(
                                            'name'              => 'gmb_schema_about_page',
                                            'id'                => 'gmb_schema_about_page',
                                            'show_option_none'  => esc_html__('— Select About Page —', 'gmb-ranker-seo-automation'),
                                            'option_none_value' => '0',
                                            'selected'          => $s['about_page'],
                                            'class'             => 'regular-text gmb-select gmb-input--min-280',
                                        ));
                                        ?>
                                        <p class="gmb-form-help"><?php esc_html_e('Select your About Us page. Injects @type: AboutPage structured data for entity authority.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Contact Page Selection -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Contact Page Schema', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <?php
                                        wp_dropdown_pages(array(
                                            'name'              => 'gmb_schema_contact_page',
                                            'id'                => 'gmb_schema_contact_page',
                                            'show_option_none'  => esc_html__('— Select Contact Page —', 'gmb-ranker-seo-automation'),
                                            'option_none_value' => '0',
                                            'selected'          => $s['contact_page'],
                                            'class'             => 'regular-text gmb-select gmb-input--min-280',
                                        ));
                                        ?>
                                        <p class="gmb-form-help"><?php esc_html_e('Select your Contact Us page. Injects @type: ContactPage schema with customer support endpoints.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Default Schema Fallback Image -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Default Fallback Image', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-flex-center-gap-sm gmb-mb-8">
                                            <input type="text" id="gmb_schema_default_img_input" name="gmb_schema_default_image" value="<?php echo esc_attr($s['default_image']); ?>" class="regular-text gmb-input gmb-input--max-420" placeholder="https://example.com/default-schema-image.jpg" />
                                            <button type="button" class="button gmb-btn--secondary" id="gmb_schema_upload_default_img_btn"><?php esc_html_e('Upload', 'gmb-ranker-seo-automation'); ?></button>
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('Used in Article/Blog schema when a post has no featured image.', 'gmb-ranker-seo-automation'); ?></p>
                                        <div id="gmb_schema_default_img_preview_wrap" class="gmb-thumb-preview <?php echo empty($s['default_image']) ? 'gmb-hidden' : ''; ?>">
                                            <img id="gmb_schema_default_img_preview" src="<?php echo esc_url($s['default_image']); ?>" alt="<?php esc_attr_e('Default Image Preview', 'gmb-ranker-seo-automation'); ?>" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Sitelinks SearchBox -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Sitelinks SearchBox', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_enable_sitelinks" value="1" <?php checked('1', $s['enable_sitelinks']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Adds SearchAction parameters inside WebSite schema for interactive search box eligibility.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- BreadcrumbList Schema -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('BreadcrumbList Schema', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_enable_breadcrumbs" value="1" <?php checked('1', $s['enable_breadcrumbs']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Automatically injects BreadcrumbList structured data across all post types and categories.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Author Representation -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Author Representation', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <select name="gmb_schema_author_type" class="gmb-select gmb-input--min-280">
                                            <option value="Person" <?php selected($s['author_type'], 'Person'); ?>><?php esc_html_e('Person (Recommended for Google E-E-A-T)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="Organization" <?php selected($s['author_type'], 'Organization'); ?>><?php esc_html_e('Organization', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                        <p class="gmb-form-help"><?php esc_html_e('Defines whether post authors should be rendered as individual persons or attributed to the parent organization.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Author SameAs Profiles -->
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Author SameAs Profiles', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_schema_author_sameas" rows="3" class="gmb-textarea" placeholder="https://www.linkedin.com/in/author&#10;https://twitter.com/author"><?php echo esc_textarea($s['author_sameas']); ?></textarea>
                                        <p class="gmb-form-help"><?php esc_html_e('One URL per line (e.g. Wikipedia, LinkedIn profile, Wikidata link) establishing canonical author entity identity.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <!-- Speakable Schema -->
                                <div class="gmb-settings-row gmb-settings-row--noborder">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Speakable Schema', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_enable_speakable" value="1" <?php checked('1', $s['enable_speakable']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('Adds SpeakableSpecification pointing to headline and summary for voice assistant compatibility.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- SUBTAB 2: Schema Builder (Templates) -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'templates') ? 'active' : ''; ?>" id="gmb-subtab-schema-templates" role="tabpanel">

                            <div class="gmb-settings-panel-header">
                                <?php if (empty($templates)) : ?>
                                    <h2 class="gmb-heading-2"><?php esc_html_e('Schema Builder & Condition Engine', 'gmb-ranker-seo-automation'); ?></h2>
                                    <p class="gmb-text-muted"><?php esc_html_e('Build custom modular Schema.org blueprints and automatically assign them sitewide or conditionally with smart rules.', 'gmb-ranker-seo-automation'); ?></p>
                                <?php else : ?>
                                    <div class="gmb-schema-templates-header">
                                        <div class="gmb-text-left">
                                            <h2 class="gmb-heading-2"><?php esc_html_e('Schema Builder & Condition Engine', 'gmb-ranker-seo-automation'); ?></h2>
                                            <p class="gmb-text-muted"><?php esc_html_e('Build custom modular Schema.org blueprints and automatically assign them sitewide or conditionally with smart rules.', 'gmb-ranker-seo-automation'); ?></p>
                                        </div>
                                        <button type="button" class="button button-primary gmb-schema-create-btn" id="gmb-open-new-template-modal-btn">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                            <?php esc_html_e('Create Schema Template', 'gmb-ranker-seo-automation'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div id="gmb-schema-templates-list-wrapper" data-pts="<?php echo esc_attr(wp_json_encode($avail_pts)); ?>" data-cats="<?php echo esc_attr(wp_json_encode($avail_cats)); ?>">
                                <?php if (empty($templates)) : ?>
                                    <div id="gmb-templates-empty-state" class="gmb-templates-empty-state">
                                        <div class="gmb-templates-empty-state-icon">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        </div>
                                        <h3 class="gmb-templates-empty-state-title"><?php esc_html_e('No Custom Schema Templates Yet', 'gmb-ranker-seo-automation'); ?></h3>
                                        <p class="gmb-templates-empty-state-desc"><?php esc_html_e('Create reusable Schema blueprints for FAQ, Services, Products, Local Business, HowTo guides, and assign them automatically by category, post type, or sitewide.', 'gmb-ranker-seo-automation'); ?></p>
                                        <button type="button" class="button button-primary gmb-templates-empty-state-btn" id="gmb-empty-create-template-btn">
                                            <?php esc_html_e('+ Create Your First Template', 'gmb-ranker-seo-automation'); ?>
                                        </button>
                                    </div>
                                <?php else : ?>
                                    <div class="gmb-templates-table-wrap">
                                        <table class="wp-list-table widefat fixed striped gmb-templates-table">
                                            <thead>
                                                <tr>
                                                    <th class="gmb-templates-table-th--title"><?php esc_html_e('Template Title & Type', 'gmb-ranker-seo-automation'); ?></th>
                                                    <th class="gmb-templates-table-th--conditions"><?php esc_html_e('Assigned Display Conditions', 'gmb-ranker-seo-automation'); ?></th>
                                                    <th class="gmb-templates-table-th--status"><?php esc_html_e('Status', 'gmb-ranker-seo-automation'); ?></th>
                                                    <th class="gmb-templates-table-th--actions"><?php esc_html_e('Actions', 'gmb-ranker-seo-automation'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="gmb-templates-tbody">
                                                <?php foreach ($templates as $tpl) : 
                                                    $t_id = esc_attr($tpl['id']);
                                                    $t_title = esc_html($tpl['title']);
                                                    $t_type = esc_html($tpl['type']);
                                                    $t_status = $tpl['status'];
                                                    $conditions = $tpl['conditions'];
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
                                                                    <span class="gmb-templates-conditions-empty"><?php esc_html_e('No conditions (Inactive)', 'gmb-ranker-seo-automation'); ?></span>
                                                                <?php else : ?>
                                                                    <?php foreach ($conditions as $c) : 
                                                                        $is_exc = (isset($c['type']) && $c['type'] === 'exclude');
                                                                        $tgt = isset($c['target']) ? $c['target'] : 'entire_site';
                                                                        $val = isset($c['value']) ? $c['value'] : '';
                                                                        $badge_cls = $is_exc ? 'gmb-templates-condition-badge--exclude' : 'gmb-templates-condition-badge--include';
                                                                        $prefix = $is_exc ? esc_html__('EXCLUDE: ', 'gmb-ranker-seo-automation') : esc_html__('INCLUDE: ', 'gmb-ranker-seo-automation');
                                                                        
                                                                        $desc = esc_html__('Entire Site', 'gmb-ranker-seo-automation');
                                                                        if ($tgt === 'homepage') $desc = esc_html__('Homepage', 'gmb-ranker-seo-automation');
                                                                        elseif ($tgt === 'post_type') $desc = sprintf(esc_html__('Post Type (%s)', 'gmb-ranker-seo-automation'), esc_html($val));
                                                                        elseif ($tgt === 'taxonomy') $desc = sprintf(esc_html__('Category (%s)', 'gmb-ranker-seo-automation'), esc_html($val));
                                                                        elseif ($tgt === 'specific_post') $desc = sprintf(esc_html__('Post ID #%s', 'gmb-ranker-seo-automation'), esc_html($val));
                                                                        elseif ($tgt === 'archives') $desc = esc_html__('Archives', 'gmb-ranker-seo-automation');
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
                                                                <button type="button" class="button button-small gmb-templates-btn-action gmb-edit-template-btn" data-id="<?php echo $t_id; ?>"><?php esc_html_e('Edit', 'gmb-ranker-seo-automation'); ?></button>
                                                                <button type="button" class="button button-small gmb-templates-btn-action gmb-duplicate-template-btn" data-id="<?php echo $t_id; ?>"><?php esc_html_e('Duplicate', 'gmb-ranker-seo-automation'); ?></button>
                                                                <button type="button" class="button button-small gmb-templates-btn-delete gmb-delete-template-btn" data-id="<?php echo $t_id; ?>"><?php esc_html_e('Delete', 'gmb-ranker-seo-automation'); ?></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Interactive Schema Generator & Builder Modal -->
                            <div id="gmb-template-builder-modal" class="gmb-modal-overlay" aria-hidden="true">
                                <div class="gmb-modal-dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Schema Builder Modal', 'gmb-ranker-seo-automation'); ?>">
                                    
                                    <input type="hidden" id="gmb-modal-tpl-id" value="" />
                                    <input type="hidden" id="gmb-modal-tpl-type" value="FAQPage" />
                                    <input type="hidden" id="gmb-modal-tpl-status" value="active" />

                                    <!-- VIEW 1: SCHEMA GENERATOR (CATALOG) -->
                                    <div id="gmb-modal-view-catalog" class="gmb-modal-view-catalog">
                                        <div class="gmb-catalog-modal-header">
                                            <h3 class="gmb-catalog-modal-title"><?php esc_html_e('Schema Generator', 'gmb-ranker-seo-automation'); ?></h3>
                                            <button type="button" class="gmb-modal-close-trigger gmb-modal-close-btn" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                                        </div>

                                        <div class="gmb-catalog-tabs-bar">
                                            <div class="gmb-catalog-tabs-group">
                                                <button type="button" id="gmb-cat-tab-templates" class="gmb-cat-nav-tab active">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                                    <?php esc_html_e('Schema Templates', 'gmb-ranker-seo-automation'); ?>
                                                </button>
                                                <button type="button" id="gmb-cat-tab-import" class="gmb-cat-nav-tab">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                    <?php esc_html_e('Import', 'gmb-ranker-seo-automation'); ?>
                                                </button>
                                                <button type="button" id="gmb-cat-tab-custom" class="gmb-cat-nav-tab">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                    <?php esc_html_e('Custom Schema', 'gmb-ranker-seo-automation'); ?>
                                                </button>
                                            </div>
                                            <div class="gmb-text-muted" title="<?php esc_attr_e('Select a Schema type to generate structured data.', 'gmb-ranker-seo-automation'); ?>">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                            </div>
                                        </div>

                                        <!-- CATALOG PANEL -->
                                        <div id="gmb-cat-panel-templates" class="gmb-cat-panel">
                                            <div class="gmb-catalog-toolbar">
                                                <div class="gmb-catalog-source-radios">
                                                    <label class="gmb-catalog-radio-label">
                                                        <input type="radio" name="gmb_catalog_source" id="gmb-catalog-radio-catalog" value="catalog" checked class="gmb-m-0" />
                                                        <?php esc_html_e('Schema Catalog', 'gmb-ranker-seo-automation'); ?>
                                                    </label>
                                                    <label class="gmb-catalog-radio-label gmb-text-muted gmb-font-regular">
                                                        <input type="radio" name="gmb_catalog_source" id="gmb-catalog-radio-saved" value="saved" class="gmb-m-0" />
                                                        <?php esc_html_e('Your Templates', 'gmb-ranker-seo-automation'); ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="text" id="gmb-catalog-search-input" placeholder="<?php esc_attr_e('Search...', 'gmb-ranker-seo-automation'); ?>" class="gmb-catalog-search-input" />
                                                </div>
                                            </div>

                                            <div class="gmb-catalog-cards-scroll">
                                                <div id="gmb-catalog-cards-grid" class="gmb-schema-template-grid">
                                                    <?php foreach ($catalog_schemas as $cs) : ?>
                                                        <div class="gmb-schema-template-card" data-title="<?php echo esc_attr($cs['name']); ?>" data-type="<?php echo esc_attr($cs['type']); ?>">
                                                            <div class="gmb-schema-template-info">
                                                                <span class="gmb-schema-template-icon">
                                                                    <?php echo GMB_Ranker_SEO_Schema_Registry::get_schema_icon_svg($cs['icon_key']); ?>
                                                                </span>
                                                                <span class="gmb-schema-template-name"><?php echo esc_html($cs['name']); ?></span>
                                                            </div>
                                                            <button type="button" class="button button-small gmb-use-schema-btn" data-type="<?php echo esc_attr($cs['type']); ?>"><?php esc_html_e('+ Use', 'gmb-ranker-seo-automation'); ?></button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div id="gmb-saved-templates-grid" class="gmb-saved-templates-grid"></div>

                                                <div id="gmb-catalog-no-results" class="gmb-cat-empty-state">
                                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" class="gmb-mb-8" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                    <p class="gmb-text-secondary gmb-font-semibold"><?php esc_html_e('No schema types match your search', 'gmb-ranker-seo-automation'); ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- IMPORT PANEL -->
                                        <div id="gmb-cat-panel-import" class="gmb-cat-panel">
                                            <div class="gmb-cat-form-card">
                                                <div class="gmb-mb-16">
                                                    <label class="gmb-form-label"><?php esc_html_e('Import Schema Code from', 'gmb-ranker-seo-automation'); ?></label>
                                                    <select id="gmb-catalog-import-source" class="gmb-select">
                                                        <option value="jsonld" selected><?php esc_html_e('JSON-LD/Custom Code', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="html"><?php esc_html_e('HTML / Webpage Source Code', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="url"><?php esc_html_e('URL', 'gmb-ranker-seo-automation'); ?></option>
                                                    </select>
                                                </div>

                                                <div id="gmb-catalog-import-code-wrap" class="gmb-mb-20">
                                                    <label id="gmb-catalog-import-code-label" class="gmb-form-label"><?php esc_html_e('Custom JSON-LD Code', 'gmb-ranker-seo-automation'); ?></label>
                                                    <textarea id="gmb-catalog-import-textarea" rows="11" placeholder='{"@context": "https://schema.org", "@type": "Organization", ...}' class="gmb-textarea gmb-textarea--code"></textarea>
                                                </div>

                                                <div id="gmb-catalog-import-url-wrap" class="gmb-mb-20">
                                                    <label class="gmb-form-label"><?php esc_html_e('Page URL to Extract Schema', 'gmb-ranker-seo-automation'); ?></label>
                                                    <div class="gmb-flex-gap-sm">
                                                        <input type="url" id="gmb-catalog-import-url-input" placeholder="https://example.com/page-with-schema" class="gmb-input" />
                                                    </div>
                                                    <p class="gmb-form-help"><?php esc_html_e('Enter any live webpage URL containing JSON-LD or Microdata structured markup to extract and convert.', 'gmb-ranker-seo-automation'); ?></p>
                                                </div>

                                                <div id="gmb-catalog-import-error" class="gmb-callout gmb-callout--danger"></div>

                                                <div>
                                                    <button type="button" class="button button-primary gmb-btn--primary" id="gmb-catalog-import-process-btn">
                                                        <?php esc_html_e('Process Code', 'gmb-ranker-seo-automation'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CUSTOM SCHEMA PANEL -->
                                        <div id="gmb-cat-panel-custom" class="gmb-cat-panel">
                                            <div class="gmb-cat-form-card gmb-cat-form-card--gap">
                                                <div>
                                                    <h4 class="gmb-heading-3"><?php esc_html_e('Create Custom Schema Blueprint', 'gmb-ranker-seo-automation'); ?></h4>
                                                    <p class="gmb-text-muted"><?php esc_html_e('Build any Schema.org structured data entity with custom properties, tailored nested groups, and smart display conditions.', 'gmb-ranker-seo-automation'); ?></p>
                                                </div>

                                                <div class="gmb-grid-2col">
                                                    <div>
                                                        <label class="gmb-form-label"><?php esc_html_e('Custom Schema Type (@type) *', 'gmb-ranker-seo-automation'); ?></label>
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
                                                        <label class="gmb-form-label"><?php esc_html_e('Schema Template Title *', 'gmb-ranker-seo-automation'); ?></label>
                                                        <input type="text" id="gmb-custom-title-input" value="<?php esc_attr_e('Custom Schema Blueprint', 'gmb-ranker-seo-automation'); ?>" placeholder="e.g. Service Blueprint" class="gmb-input" />
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="gmb-form-label"><?php esc_html_e('Starter Blueprint Preset', 'gmb-ranker-seo-automation'); ?></label>
                                                    <select id="gmb-custom-preset-select" class="gmb-select">
                                                        <option value="blank" selected><?php esc_html_e('Blank Starter Schema', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="organization"><?php esc_html_e('Custom Organization / Company', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="healthcare"><?php esc_html_e('Custom Healthcare / Medical Clinic', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="service"><?php esc_html_e('Custom Professional Service', 'gmb-ranker-seo-automation'); ?></option>
                                                        <option value="creative"><?php esc_html_e('Custom Creative Work / Article', 'gmb-ranker-seo-automation'); ?></option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="gmb-form-label"><?php esc_html_e('Initial JSON-LD Schema Code', 'gmb-ranker-seo-automation'); ?></label>
                                                    <textarea id="gmb-custom-preview-textarea" rows="7" spellcheck="false" class="gmb-textarea gmb-textarea--code"></textarea>
                                                </div>

                                                <div class="gmb-flex-start">
                                                    <button type="button" class="button button-primary gmb-btn--primary" id="gmb-custom-create-btn">
                                                        <?php esc_html_e('Build Custom Schema →', 'gmb-ranker-seo-automation'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- VIEW 2: SCHEMA BUILDER (EDITOR) -->
                                    <div id="gmb-modal-view-builder" class="gmb-modal-view-builder">
                                        <div class="gmb-builder-header">
                                            <div class="gmb-flex-center-gap-md">
                                                <button type="button" id="gmb-builder-back-btn" class="gmb-builder-back-btn">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                    <?php esc_html_e('Back to Catalog', 'gmb-ranker-seo-automation'); ?>
                                                </button>
                                                <span class="gmb-text-subtle">|</span>
                                                <h3 id="gmb-builder-modal-title" class="gmb-builder-title">
                                                    <?php esc_html_e('Schema Builder', 'gmb-ranker-seo-automation'); ?>
                                                </h3>
                                            </div>
                                            <div class="gmb-flex-center-gap-sm">
                                                <span id="gmb-builder-active-type-badge" class="gmb-builder-badge">
                                                    FAQPage
                                                </span>
                                                <button type="button" class="gmb-modal-close-trigger" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                                            </div>
                                        </div>

                                        <div class="gmb-builder-subtabs">
                                            <button type="button" id="gmb-builder-tab-btn-edit" class="gmb-builder-tab-btn active">
                                                <?php esc_html_e('Edit', 'gmb-ranker-seo-automation'); ?>
                                            </button>
                                            <button type="button" id="gmb-builder-tab-btn-code" class="gmb-builder-tab-btn">
                                                <?php esc_html_e('Code Validation', 'gmb-ranker-seo-automation'); ?>
                                            </button>
                                            <button type="button" id="gmb-builder-tab-btn-conditions" class="gmb-builder-tab-btn">
                                                <?php esc_html_e('Display Conditions', 'gmb-ranker-seo-automation'); ?>
                                            </button>
                                        </div>

                                        <div class="gmb-builder-body">
                                            <!-- EDIT PANEL -->
                                            <div id="gmb-builder-panel-edit" class="gmb-builder-panel active">
                                                <div class="gmb-builder-card gmb-builder-card--clean">
                                                    <label class="gmb-form-label"><?php esc_html_e('Schema Template Name *', 'gmb-ranker-seo-automation'); ?></label>
                                                    <input type="text" id="gmb-modal-tpl-title" placeholder="<?php esc_attr_e('Enter template name', 'gmb-ranker-seo-automation'); ?>" class="gmb-input" />
                                                </div>

                                                <div class="gmb-builder-card">
                                                    <div class="gmb-builder-root-row">
                                                        <div class="gmb-builder-root-label">@type</div>
                                                        <input type="text" id="gmb-vis-type-val" readonly value="FAQPage" class="gmb-builder-root-input" />
                                                    </div>

                                                    <div id="gmb-visual-fields-content" class="gmb-flex-col-gap-md"></div>

                                                    <div class="gmb-builder-actions">
                                                        <button type="button" id="gmb-btn-add-property" class="gmb-builder-action-btn">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                            <?php esc_html_e('Add Property', 'gmb-ranker-seo-automation'); ?>
                                                        </button>
                                                        <button type="button" id="gmb-btn-add-property-group" class="gmb-builder-action-btn">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                            <?php esc_html_e('Add Property Group', 'gmb-ranker-seo-automation'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- CODE VALIDATION PANEL -->
                                            <div id="gmb-builder-panel-code" class="gmb-builder-panel">
                                                <div class="gmb-ide-card">
                                                    <div class="gmb-ide-tools">
                                                        <div class="gmb-flex-center-gap-sm">
                                                            <span class="gmb-ide-file-title">schema-graph.jsonld</span>
                                                            <span id="gmb-json-syntax-indicator" class="gmb-ide-badge gmb-ide-badge--valid">
                                                                <?php esc_html_e('Valid JSON-LD Syntax', 'gmb-ranker-seo-automation'); ?>
                                                            </span>
                                                        </div>
                                                        <div class="gmb-flex-gap-sm">
                                                            <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-load-preset-btn"><?php esc_html_e('↺ Reset Blueprint', 'gmb-ranker-seo-automation'); ?></button>
                                                            <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-format-json-btn"><?php esc_html_e('Format JSON', 'gmb-ranker-seo-automation'); ?></button>
                                                            <button type="button" class="button button-secondary button-small gmb-btn--sm" id="gmb-tpl-copy-json-btn"><?php esc_html_e('Copy Code', 'gmb-ranker-seo-automation'); ?></button>
                                                        </div>
                                                    </div>

                                                    <div class="gmb-ide-console">
                                                        <textarea id="gmb-modal-tpl-json" rows="12" spellcheck="false" class="gmb-ide-textarea"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- DISPLAY CONDITIONS PANEL -->
                                            <div id="gmb-builder-panel-conditions" class="gmb-builder-panel">
                                                <div class="gmb-conditions-card">
                                                    <div class="gmb-conditions-header">
                                                        <div>
                                                            <h4 class="gmb-conditions-title"><?php esc_html_e('Display Conditions (Automated Rules)', 'gmb-ranker-seo-automation'); ?></h4>
                                                            <span class="gmb-text-muted"><?php esc_html_e('Specify which posts, pages, or categories automatically output this structured data.', 'gmb-ranker-seo-automation'); ?></span>
                                                        </div>
                                                        <button type="button" id="gmb-add-condition-row-btn" class="gmb-btn-add-condition">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                            <?php esc_html_e('Add Condition Rule', 'gmb-ranker-seo-automation'); ?>
                                                        </button>
                                                    </div>

                                                    <div id="gmb-modal-conditions-container" class="gmb-flex-col-gap-sm"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gmb-builder-footer">
                                            <div class="gmb-flex-center-gap-sm">
                                                <label class="rm-switch">
                                                    <input type="checkbox" id="gmb-modal-status-toggle" checked />
                                                    <span class="rm-slider"></span>
                                                </label>
                                                <span id="gmb-modal-status-label" class="gmb-form-label"><?php esc_html_e('Active Template', 'gmb-ranker-seo-automation'); ?></span>
                                            </div>
                                            <div class="gmb-flex-center-gap-sm">
                                                <button type="button" id="gmb-cancel-template-modal-btn" class="button gmb-btn--secondary"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                                                <button type="button" id="gmb-save-template-modal-btn" class="button button-primary gmb-btn--primary"><?php esc_html_e('Save', 'gmb-ranker-seo-automation'); ?></button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- SUBTAB 3: Post Types Schema -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'post-types') ? 'active' : ''; ?>" id="gmb-subtab-schema-post-types" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Default Post Type Schema Mapping', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Assign default structured data entities for each public post type. Individual posts can override these defaults in the SEO metabox.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <?php 
                            $public_post_types_schema = get_post_types(array('public' => true), 'objects');
                            foreach ($public_post_types_schema as $pt_slug => $pt_obj) :
                                if ($pt_slug === 'attachment') continue;
                                
                                $curr_schema = get_option('gmb_' . $pt_slug . '_schema_type', '');
                                if (empty($curr_schema)) {
                                    if ($pt_slug === 'post') $curr_schema = get_option('gmb_posts_schema_type', 'article');
                                    elseif ($pt_slug === 'page') $curr_schema = get_option('gmb_pages_schema_type', 'none');
                                    elseif ($pt_slug === 'services' || $pt_slug === 'service') $curr_schema = get_option('gmb_services_schema_type', 'service');
                                    elseif (in_array($pt_slug, array('service_locations', 'service_location', 'locations', 'location'), true)) $curr_schema = get_option('gmb_service_locations_schema_type', 'localbusiness');
                                    elseif (in_array($pt_slug, array('team_members', 'team_member', 'team'), true)) $curr_schema = get_option('gmb_team_members_schema_type', 'person');
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
                                        <div>
                                            <label class="gmb-form-label"><?php esc_html_e('Schema Type', 'gmb-ranker-seo-automation'); ?></label>
                                            <select name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_type" class="gmb-select">
                                                <option value="none" <?php selected($curr_schema, 'none'); ?>><?php esc_html_e('None (Disable Auto Schema)', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="article" <?php selected($curr_schema, 'article'); ?>><?php esc_html_e('Article', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="service" <?php selected($curr_schema, 'service'); ?>><?php esc_html_e('Service', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="localbusiness" <?php selected($curr_schema, 'localbusiness'); ?>><?php esc_html_e('LocalBusiness', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="product" <?php selected($curr_schema, 'product'); ?>><?php esc_html_e('Product', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="person" <?php selected($curr_schema, 'person'); ?>><?php esc_html_e('Person', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="faq" <?php selected($curr_schema, 'faq'); ?>><?php esc_html_e('FAQPage', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="course" <?php selected($curr_schema, 'course'); ?>><?php esc_html_e('Course', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="event" <?php selected($curr_schema, 'event'); ?>><?php esc_html_e('Event', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="jobposting" <?php selected($curr_schema, 'jobposting'); ?>><?php esc_html_e('JobPosting', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="recipe" <?php selected($curr_schema, 'recipe'); ?>><?php esc_html_e('Recipe', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="restaurant" <?php selected($curr_schema, 'restaurant'); ?>><?php esc_html_e('Restaurant', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="software" <?php selected($curr_schema, 'software'); ?>><?php esc_html_e('SoftwareApplication', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="video" <?php selected($curr_schema, 'video'); ?>><?php esc_html_e('VideoObject', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="book" <?php selected($curr_schema, 'book'); ?>><?php esc_html_e('Book', 'gmb-ranker-seo-automation'); ?></option>
                                            </select>
                                        </div>

                                        <?php if ($pt_slug === 'post' || $curr_schema === 'article') : ?>
                                        <div>
                                            <label class="gmb-form-label"><?php esc_html_e('Article Type', 'gmb-ranker-seo-automation'); ?></label>
                                            <select name="gmb_<?php echo esc_attr($pt_slug); ?>_article_type" class="gmb-select">
                                                <option value="article" <?php selected($curr_article_type, 'article'); ?>><?php esc_html_e('Article (General)', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="blogpost" <?php selected($curr_article_type, 'blogpost'); ?>><?php esc_html_e('BlogPosting', 'gmb-ranker-seo-automation'); ?></option>
                                                <option value="newsarticle" <?php selected($curr_article_type, 'newsarticle'); ?>><?php esc_html_e('NewsArticle', 'gmb-ranker-seo-automation'); ?></option>
                                            </select>
                                        </div>
                                        <?php endif; ?>

                                        <div>
                                            <label class="gmb-form-label"><?php esc_html_e('Headline Template', 'gmb-ranker-seo-automation'); ?></label>
                                            <input type="text" name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_headline" value="<?php echo esc_attr($curr_headline); ?>" class="gmb-input" placeholder="%seo_title%" />
                                        </div>

                                        <div>
                                            <label class="gmb-form-label"><?php esc_html_e('Description Template', 'gmb-ranker-seo-automation'); ?></label>
                                            <input type="text" name="gmb_<?php echo esc_attr($pt_slug); ?>_schema_desc" value="<?php echo esc_attr($curr_desc); ?>" class="gmb-input" placeholder="%seo_description%" />
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="gmb-settings-footer justify-end">
                                <input type="submit" name="submit" class="button button-primary button-large gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- SUBTAB 4: Knowledge Graph -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'knowledge') ? 'active' : ''; ?>" id="gmb-subtab-schema-knowledge" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Knowledge Graph & Publisher Entity', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Configure publisher identity, physical address, and official social presence representing your brand.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-card-settings-list">
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Publisher Type', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <select name="gmb_local_seo_type" class="gmb-select gmb-input--min-280">
                                            <option value="Organization" <?php selected($s['local_type'], 'Organization'); ?>><?php esc_html_e('Organization (Default)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="LocalBusiness" <?php selected($s['local_type'], 'LocalBusiness'); ?>><?php esc_html_e('LocalBusiness (Physical Store / Firm)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="MedicalBusiness" <?php selected($s['local_type'], 'MedicalBusiness'); ?>><?php esc_html_e('MedicalBusiness (Clinic / Healthcare)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="Person" <?php selected($s['local_type'], 'Person'); ?>><?php esc_html_e('Person (Personal Brand / Influencer)', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                        <p class="gmb-form-help"><?php esc_html_e('Select whether this site represents a business organization, local facility, or personal brand.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Publisher Legal Name', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <input type="text" name="gmb_local_seo_name" value="<?php echo esc_attr($s['local_name']); ?>" class="gmb-input gmb-input--max-520" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                        <p class="gmb-form-help"><?php esc_html_e('Official company or organization name rendered in Schema.org publisher tags.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Publisher Logo', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-flex-center-gap-sm gmb-mb-8">
                                            <input type="text" id="gmb_schema_logo_input" name="gmb_local_seo_logo" value="<?php echo esc_attr($s['local_logo']); ?>" class="gmb-input gmb-input--max-420" placeholder="https://example.com/logo.png" />
                                            <button type="button" class="button gmb-btn--secondary" id="gmb_schema_upload_logo_btn"><?php esc_html_e('Upload', 'gmb-ranker-seo-automation'); ?></button>
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('Branding logo (min 512x512px) used in Knowledge Graph publisher tags.', 'gmb-ranker-seo-automation'); ?></p>
                                        <div id="gmb_schema_logo_preview_wrap" class="gmb-thumb-preview <?php echo empty($s['local_logo']) ? 'gmb-hidden' : ''; ?>">
                                            <img id="gmb_schema_logo_preview" src="<?php echo esc_url($s['local_logo']); ?>" alt="<?php esc_attr_e('Logo Preview', 'gmb-ranker-seo-automation'); ?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Contact Details', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-grid-2 gmb-input--max-520">
                                            <input type="text" name="gmb_local_seo_phone" value="<?php echo esc_attr($s['local_phone']); ?>" placeholder="Phone (+1 555-0199)" class="gmb-input" />
                                            <input type="email" name="gmb_local_seo_email" value="<?php echo esc_attr($s['local_email']); ?>" placeholder="Contact Email" class="gmb-input" />
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('Customer support telephone and verified email for structured data ContactPoint.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Physical Address', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-flex-col-gap-10 gmb-input--max-520">
                                            <input type="text" name="gmb_local_seo_address_street" value="<?php echo esc_attr($s['address_street']); ?>" placeholder="Street Address, Suite / Floor" class="gmb-input" />
                                            <div class="gmb-grid-2">
                                                <input type="text" name="gmb_local_seo_address_locality" value="<?php echo esc_attr($s['address_locality']); ?>" placeholder="City / Locality" class="gmb-input" />
                                                <input type="text" name="gmb_local_seo_address_region" value="<?php echo esc_attr($s['address_region']); ?>" placeholder="State / Province / Region" class="gmb-input" />
                                            </div>
                                            <div class="gmb-grid-2">
                                                <input type="text" name="gmb_local_seo_address_postal" value="<?php echo esc_attr($s['address_postal']); ?>" placeholder="Postal / ZIP Code" class="gmb-input" />
                                                <input type="text" name="gmb_local_seo_address_country" value="<?php echo esc_attr($s['address_country']); ?>" placeholder="Country Code (e.g. US, UK, CA, AU)" class="gmb-input" />
                                            </div>
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('Full physical street address for LocalBusiness and PostalAddress entities.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Geo Coordinates & Pricing', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-flex-col-gap-10 gmb-input--max-520">
                                            <div class="gmb-grid-2">
                                                <input type="text" name="gmb_local_business_lat" value="<?php echo esc_attr($s['lat']); ?>" placeholder="Latitude (e.g. 40.7128)" class="gmb-input" />
                                                <input type="text" name="gmb_local_business_lng" value="<?php echo esc_attr($s['lng']); ?>" placeholder="Longitude (e.g. -74.0060)" class="gmb-input" />
                                            </div>
                                            <div class="gmb-grid-2">
                                                <input type="text" name="gmb_local_business_price_range" value="<?php echo esc_attr($s['price_range']); ?>" placeholder="Price Range (e.g. $$)" class="gmb-input" />
                                                <input type="text" name="gmb_local_business_opening_hours" value="<?php echo esc_attr($s['opening_hours']); ?>" placeholder="Opening Hours (e.g. Mo-Fr 09:00-18:00)" class="gmb-input" />
                                            </div>
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('Exact latitude and longitude for GeoCoordinates, opening hours, and price range indicator ($ to $$$$).', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row gmb-settings-row--noborder">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Social SameAs Links', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <div class="gmb-grid-2 gmb-input--max-520 gmb-flex-col-gap-10">
                                            <input type="url" name="gmb_social_facebook_page_url" value="<?php echo esc_attr($s['facebook_url']); ?>" placeholder="Facebook Page URL" class="gmb-input" />
                                            <input type="text" name="gmb_social_twitter_username" value="<?php echo esc_attr($s['twitter_handle']); ?>" placeholder="Twitter / X Profile (@username)" class="gmb-input" />
                                            <input type="url" name="gmb_social_linkedin_url" value="<?php echo esc_attr($s['linkedin_url']); ?>" placeholder="LinkedIn Company URL" class="gmb-input" />
                                            <input type="url" name="gmb_social_youtube_url" value="<?php echo esc_attr($s['youtube_url']); ?>" placeholder="YouTube Channel URL" class="gmb-input" />
                                            <input type="url" name="gmb_social_instagram_url" value="<?php echo esc_attr($s['instagram_url']); ?>" placeholder="Instagram URL" class="gmb-input" />
                                            <input type="url" name="gmb_social_wikipedia_url" value="<?php echo esc_attr($s['wikipedia_url']); ?>" placeholder="Wikipedia / Wikidata URL" class="gmb-input" />
                                        </div>
                                        <p class="gmb-form-help"><?php esc_html_e('URLs of your verified official social profiles used to enrich sameAs knowledge graph links.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" name="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- SUBTAB 5: Custom Code & Test -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'custom') ? 'active' : ''; ?>" id="gmb-subtab-schema-custom" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('Custom JSON-LD & Live Schema Validator', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Inject customized structured data into page head and test live URLs directly with Google Rich Results.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-card-settings-list">
                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Custom JSON-LD', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <textarea name="gmb_schema_custom_jsonld" rows="8" class="gmb-schema-custom-jsonld-area gmb-textarea--code gmb-input--max-520" placeholder="<?php echo esc_attr("{\n  \"@context\": \"https://schema.org\",\n  \"@type\": \"Organization\",\n  \"name\": \"%sitename%\",\n  \"url\": \"%siteurl%\"\n}"); ?>"><?php echo esc_textarea($s['custom_jsonld']); ?></textarea>
                                        <p class="gmb-form-help"><?php esc_html_e('Enter raw JSON-LD (without <script> tags). Supports variables: %sitename%, %siteurl%, %phone%, %email%, %currentyear%.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Rank Math Graph', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_integrate_rankmath" value="1" <?php checked('1', $s['integrate_rankmath']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('If Rank Math is active on this site, merge GMB Ranker schemas seamlessly into the unified graph.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>

                                <div class="gmb-settings-row">
                                    <div class="gmb-settings-label-col">
                                        <?php esc_html_e('Yoast SEO Graph', 'gmb-ranker-seo-automation'); ?>
                                    </div>
                                    <div class="gmb-settings-input-col">
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_schema_integrate_yoast" value="1" <?php checked('1', $s['integrate_yoast']); ?> />
                                            <span class="gmb-slider round"></span>
                                        </label>
                                        <p class="gmb-form-help"><?php esc_html_e('If Yoast SEO is installed, attaches GMB Ranker schemas directly into Yoast\'s @graph structured data tree.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Testing Tool -->
                            <div class="gmb-schema-validator-card">
                                <h3 class="gmb-schema-validator-title"><?php esc_html_e('Test Schema in Official Validators', 'gmb-ranker-seo-automation'); ?></h3>
                                <p class="gmb-schema-validator-desc"><?php esc_html_e('Enter a live URL from your website to test its Schema.org rich snippet compliance.', 'gmb-ranker-seo-automation'); ?></p>
                                <div class="gmb-schema-validator-tools">
                                    <input type="url" id="gmb_test_schema_url" value="<?php echo esc_url(home_url('/')); ?>" placeholder="https://example.com/" class="gmb-input gmb-flex-1 gmb-input--min-280" />
                                    <button type="button" class="button gmb-btn-validator" id="gmb_btn_test_google">
                                        <svg width="15" height="15" viewBox="0 0 24 24" class="gmb-flex-shrink-0" aria-hidden="true"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.94 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
                                        <span><?php esc_html_e('Google Rich Results', 'gmb-ranker-seo-automation'); ?></span>
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-validator-icon gmb-flex-shrink-0" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </button>
                                    <button type="button" class="button gmb-btn-validator" id="gmb_btn_test_schema_org">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-flex-shrink-0" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                        <span><?php esc_html_e('Schema.org Validator', 'gmb-ranker-seo-automation'); ?></span>
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-validator-icon gmb-flex-shrink-0" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="gmb-settings-footer-actions gmb-settings-footer justify-end">
                                <input type="submit" name="submit" class="button button-primary gmb-btn--primary" value="<?php esc_attr_e('Save Changes', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </div>

                        <!-- SUBTAB 6: 1-Click Presets -->
                        <div class="gmb-subtab-panel <?php echo ($active_subtab === 'presets') ? 'active' : ''; ?>" id="gmb-subtab-schema-presets" role="tabpanel">
                            <div class="gmb-settings-panel-header">
                                <h2 class="gmb-heading-2"><?php esc_html_e('1-Click Schema Architecture Presets', 'gmb-ranker-seo-automation'); ?></h2>
                                <p class="gmb-text-muted"><?php esc_html_e('Apply recommended schema industry blueprints tailored to your site type with a single click.', 'gmb-ranker-seo-automation'); ?></p>
                            </div>

                            <div class="gmb-schema-preset-grid">
                                <div class="gmb-card gmb-preset-card">
                                    <div class="gmb-preset-card-content">
                                        <div class="gmb-schema-preset-icon gmb-schema-preset-icon--blue">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M2 12h20"/></svg>
                                        </div>
                                        <h3 class="gmb-heading-3"><?php esc_html_e('Healthcare & Agency', 'gmb-ranker-seo-automation'); ?></h3>
                                        <p class="gmb-text-muted gmb-mb-16"><?php esc_html_e('Configures MedicalBusiness / LocalBusiness, Service entities with organization provider, and Person schema for clinical staff.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                    <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--blue" data-preset="healthcare"><?php esc_html_e('Apply Blueprint', 'gmb-ranker-seo-automation'); ?></button>
                                </div>

                                <div class="gmb-card gmb-preset-card">
                                    <div class="gmb-preset-card-content">
                                        <div class="gmb-schema-preset-icon gmb-schema-preset-icon--green">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                        </div>
                                        <h3 class="gmb-heading-3"><?php esc_html_e('Local Business & Multi-Location', 'gmb-ranker-seo-automation'); ?></h3>
                                        <p class="gmb-text-muted gmb-mb-16"><?php esc_html_e('Sets homepage as LocalBusiness, enables Sitelinks SearchBox, and generates localized business schemas for each service location.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                    <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--green" data-preset="local_business"><?php esc_html_e('Apply Blueprint', 'gmb-ranker-seo-automation'); ?></button>
                                </div>

                                <div class="gmb-card gmb-preset-card">
                                    <div class="gmb-preset-card-content">
                                        <div class="gmb-schema-preset-icon gmb-schema-preset-icon--purple">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                                        </div>
                                        <h3 class="gmb-heading-3"><?php esc_html_e('News & Content Publisher', 'gmb-ranker-seo-automation'); ?></h3>
                                        <p class="gmb-text-muted gmb-mb-16"><?php esc_html_e('Emits NewsArticle schemas, enables full E-E-A-T Person author linking with social SameAs profiles, and BreadcrumbList schemas.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                    <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--purple" data-preset="publisher"><?php esc_html_e('Apply Blueprint', 'gmb-ranker-seo-automation'); ?></button>
                                </div>

                                <div class="gmb-card gmb-preset-card">
                                    <div class="gmb-preset-card-content">
                                        <div class="gmb-schema-preset-icon gmb-schema-preset-icon--orange">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                        </div>
                                        <h3 class="gmb-heading-3"><?php esc_html_e('WooCommerce Store', 'gmb-ranker-seo-automation'); ?></h3>
                                        <p class="gmb-text-muted gmb-mb-16"><?php esc_html_e('Optimizes Product rich snippets with live Price, Offer, Currency, Availability (InStock), and SKU tracking.', 'gmb-ranker-seo-automation'); ?></p>
                                    </div>
                                    <button type="button" class="button gmb-apply-preset-btn gmb-apply-preset-btn--orange" data-preset="ecommerce"><?php esc_html_e('Apply Blueprint', 'gmb-ranker-seo-automation'); ?></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
