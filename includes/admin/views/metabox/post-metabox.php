<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('gmb_ranker_get_schema_icon_svg')) {
    function gmb_ranker_get_schema_icon_svg($type) {
        $key = strtolower(str_replace(array(' ', '_', '-'), '', $type));
        switch ($key) {
            case 'article':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
            case 'book':
                return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
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

wp_nonce_field('gmb_seo_save_nonce', 'gmb_seo_nonce');
        
        $meta_title = get_post_meta($post->ID, '_gmb_ranker_seo_title', true) ?: '';
        $meta_desc = get_post_meta($post->ID, '_gmb_ranker_seo_description', true) ?: '';
        $canonical = get_post_meta($post->ID, '_gmb_ranker_seo_canonical', true) ?: '';
        $robots = get_post_meta($post->ID, '_gmb_ranker_seo_robots', true) ?: 'index, follow';
        $json_ld = get_post_meta($post->ID, '_gmb_ranker_json_ld', true) ?: '';
        $breadcrumb_title = get_post_meta($post->ID, '_gmb_ranker_breadcrumb_title', true) ?: '';
        $redirect_url = get_post_meta($post->ID, '_gmb_ranker_redirect_url', true) ?: '';
        $redirect_code = get_post_meta($post->ID, '_gmb_ranker_redirect_code', true) ?: '301';
        $max_snippet = get_post_meta($post->ID, '_gmb_ranker_max_snippet', true) ?: '-1';
        $max_video = get_post_meta($post->ID, '_gmb_ranker_max_video', true) ?: '-1';
        $max_image = get_post_meta($post->ID, '_gmb_ranker_max_image', true) ?: 'large';

        $focus_keyword = get_post_meta($post->ID, '_gmb_ranker_focus_keyword', true) ?: '';

        $robots_array = array_map('trim', explode(',', strtolower($robots)));
        ?>
        <div class="gmb-seo-meta-container">
            <div class="gmb-seo-header-area">
                <div class="gmb-seo-tabs">
                    <button type="button" class="gmb-tab-btn active" data-tab="gmb-tab-general">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        General
                    </button>
                    <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-advanced">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                        Advanced
                    </button>
                    <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-schema">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        Schema
                    </button>
                    <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-social">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        Social
                    </button>
                </div>
                <div class="gmb-seo-header-actions">
                    <button type="button" id="gmb-ai-optimize-post-btn" class="gmb-btn--ai-post" data-action="gmb-open-ai-modal" onclick="if(window.gmbOpenAiModal) { window.gmbOpenAiModal(event); } return false;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l2.4 5.4L20 10l-5.6 2.4L12 18l-2.4-5.6L4 10l5.6-2.4z"/>
                            <path d="M19 17l.8 1.8L21.5 19.5 19.7 20.3 18.9 22l-.8-1.7-1.8-.8 1.8-.8z"/>
                        </svg>
                        <span>AI Auto-Fix Page SEO</span>
                    </button>
                </div>
            </div>

            <div class="gmb-tab-content active" id="gmb-tab-general">
                <div class="gmb-preview-box">
                    <div class="gmb-preview-header-row">
                        <div class="gmb-preview-title-wrap">
                            <h4 class="gmb-preview-heading">Google Search Snippet Preview</h4>
                            <div class="gmb-preview-device-toggle">
                                <button type="button" class="gmb-device-btn active" data-device="desktop" title="Desktop Preview">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    <span>Desktop</span>
                                </button>
                                <button type="button" class="gmb-device-btn" data-device="mobile" title="Mobile Preview">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                    <span>Mobile</span>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="button button-primary gmb-edit-snippet-btn" id="gmb-edit-snippet-btn">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit Snippet
                        </button>
                    </div>
                    <div class="gmb-preview-google gmb-preview-device--desktop">
                        <?php
                        $site_icon_url = get_site_icon_url(16);
                        if (empty($site_icon_url)) {
                            $site_icon_url = home_url('/favicon.ico');
                        }
                        ?>
                        <div class="gmb-google-site">
                            <?php if (!empty($site_icon_url)) : ?>
                                <img class="gmb-google-fav-img" src="<?php echo esc_url($site_icon_url); ?>" alt="" onerror="this.classList.add('is-hidden'); if(this.nextElementSibling) this.nextElementSibling.classList.remove('is-hidden');" />
                            <?php endif; ?>
                            <span class="gmb-google-fav <?php echo !empty($site_icon_url) ? 'is-hidden' : ''; ?>">G</span>
                            <span class="gmb-google-breadcrumbs"><?php echo esc_url(home_url()); ?> › <?php echo esc_html($post->post_name); ?></span>
                        </div>
                        <h3 class="gmb-google-title" id="gmb-preview-title"><?php echo esc_html($meta_title ?: $post->post_title); ?></h3>
                        <p class="gmb-google-snippet" id="gmb-preview-snippet"><?php echo esc_html($meta_desc ?: 'Please enter a Meta Description below to preview this result...'); ?></p>
                    </div>
                </div>

                <div class="gmb-field-group">
                    <label for="gmb_seo_focus_keyword_input" class="gmb-field-label">Focus Keyword <span class="gmb-help-tip gmb-ml-4" data-gmb-tooltip="Focus keywords are the key search queries you want this page to rank for.">i</span></label>
                    <div class="gmb-focus-keyword-field-wrapper">
                        <div class="gmb-keyword-container" id="gmb-keyword-container-wrapper">
                            <!-- Pills render dynamically via JS -->
                            <input type="text" id="gmb_seo_focus_keyword_input" class="gmb-keyword-input-el" placeholder="Type keyword and press Enter..." />
                        </div>
                        <div class="gmb-keyword-score" id="gmb-metabox-score-badge">
                            <span id="gmb-metabox-score-val">0</span>/100
                        </div>
                    </div>
                    <input type="hidden" id="gmb_seo_focus_keyword_hidden" name="gmb_seo_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" />
                    <?php $saved_score = get_post_meta($post->ID, '_gmb_ranker_seo_score', true) ?: '0'; ?>
                    <input type="hidden" id="gmb_seo_score_hidden" name="gmb_seo_score" value="<?php echo esc_attr($saved_score); ?>" />
                    
                    <?php $is_pillar = get_post_meta($post->ID, '_gmb_ranker_seo_is_pillar', true); ?>
                    <div id="gmb-seo-no-keyword-notice" class="gmb-no-keyword-notice <?php echo !empty($focus_keyword) ? 'is-hidden' : ''; ?>">
                        <span>Add a Focus Keyword to this post to see how well optimized it is.</span>
                    </div>

                    <div class="gmb-pillar-checkbox-row">
                        <input type="checkbox" id="gmb_seo_is_pillar_input" name="gmb_seo_is_pillar" value="1" <?php checked($is_pillar, '1'); ?> />
                        <label for="gmb_seo_is_pillar_input" class="gmb-pillar-label">This post is Pillar Content</label>
                        <span class="gmb-help-tip" data-gmb-tooltip="Pillar content pages are the core foundation pages of your site targeting main keywords.">i</span>
                    </div>
                </div>

                <!-- Preview Snippet Editor Modal -->
                <div class="gmb-modal-backdrop" id="gmb-snippet-modal">
                    <div class="gmb-modal-box">
                        <div class="gmb-modal-header">
                            <span class="gmb-modal-title">Preview Snippet Editor</span>
                            <span class="gmb-modal-close" id="gmb-modal-close-btn">&#x2715;</span>
                        </div>
                        <div class="gmb-modal-tabs">
                            <span class="gmb-modal-tab-btn active" data-modal-tab="gmb-modal-tab-general">General</span>
                            <span class="gmb-modal-tab-btn" data-modal-tab="gmb-modal-tab-social">Social</span>
                        </div>
                        <div class="gmb-modal-body gmb-modal-body-scroll">
                            
                            <!-- General Tab Content -->
                            <div class="gmb-modal-tab-content active" id="gmb-modal-tab-general">
                                <!-- In-modal Snippet Preview -->
                                <div class="gmb-preview-google gmb-modal-preview-box">
                                    <div class="gmb-google-site">
                                        <?php if (!empty($site_icon_url)) : ?>
                                            <img class="gmb-google-fav-img" src="<?php echo esc_url($site_icon_url); ?>" alt="" onerror="this.classList.add('is-hidden'); if(this.nextElementSibling) this.nextElementSibling.classList.remove('is-hidden');" />
                                        <?php endif; ?>
                                        <span class="gmb-google-fav <?php echo !empty($site_icon_url) ? 'is-hidden' : ''; ?>">G</span>
                                        <span class="gmb-google-breadcrumbs"><?php echo esc_url(home_url()); ?> › <?php echo esc_html($post->post_name); ?></span>
                                    </div>
                                    <h3 class="gmb-google-title" id="gmb-modal-preview-title"><?php echo esc_html($meta_title ?: $post->post_title); ?></h3>
                                    <p class="gmb-google-snippet" id="gmb-modal-preview-snippet"><?php echo esc_html($meta_desc ?: 'Please enter a Meta Description below to preview this result...'); ?></p>
                                </div>

                                <div class="gmb-field-group">
                                    <div class="gmb-field-header-row">
                                        <label for="gmb_seo_title_input" class="gmb-field-label gmb-field-label-nomargin">SEO Meta Title</label>
                                        <span class="gmb-metric-char gmb-char-count-bold" id="gmb-title-char-count">0 / 60 chars</span>
                                    </div>
                                    <input type="text" id="gmb_seo_title_input" name="gmb_seo_title" value="<?php echo esc_attr($meta_title); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($post->post_title); ?>" />
                                    <div class="gmb-progress-bar-container">
                                        <div class="gmb-progress-bar-fill" id="gmb-title-progress-fill"></div>
                                    </div>
                                    <!-- SERP Pixel Width Indicator -->
                                    <div class="gmb-title-ctr-row gmb-mt-8">
                                        <div class="gmb-pixel-width-indicator">
                                            <span class="gmb-text-muted-xs">SERP Pixel Width: </span>
                                            <strong id="gmb-title-pixel-val" class="gmb-pixel-val">0px</strong> / 580px
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-field-group">
                                    <div class="gmb-field-header-row">
                                        <label for="gmb_seo_desc_input" class="gmb-field-label gmb-field-label-nomargin">SEO Meta Description</label>
                                        <span class="gmb-metric-char gmb-char-count-bold" id="gmb-desc-char-count">0 / 160 chars</span>
                                    </div>
                                    <textarea id="gmb_seo_desc_input" name="gmb_seo_description" rows="4" class="gmb-field-textarea" placeholder="Summarize your page content here..."><?php echo esc_textarea($meta_desc); ?></textarea>
                                    <div class="gmb-progress-bar-container">
                                        <div class="gmb-progress-bar-fill" id="gmb-desc-progress-fill"></div>
                                    </div>
                                </div>

                                <div class="gmb-field-group">
                                    <label for="gmb_seo_canonical_input" class="gmb-field-label">Canonical URL Override</label>
                                    <input type="url" id="gmb_seo_canonical_input" name="gmb_seo_canonical" value="<?php echo esc_attr($canonical); ?>" class="gmb-field-input" placeholder="<?php echo esc_url(get_permalink($post->ID)); ?>" />
                                    <p class="gmb-field-help">Allows you to avoid indexing duplicate content issues by mapping pointing domains.</p>
                                </div>
                            </div>

                            <!-- Social Tab Content -->
                            <div class="gmb-modal-tab-content gmb-py-5" id="gmb-modal-tab-social">
                                <h4 class="gmb-section-heading gmb-heading-4">Social Sharing Preview Settings</h4>
                                
                                <div class="gmb-field-group gmb-mb-18">
                                    <h3 class="gmb-social-section-title">Facebook Settings</h3>
                                    
                                    <div class="gmb-mb-12">
                                        <label for="gmb_seo_fb_title" class="gmb-field-label">Facebook Title</label>
                                        <input type="text" id="gmb_seo_fb_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_title', true)); ?>" class="gmb-field-input" placeholder="Facebook title override..." />
                                    </div>
                                    
                                    <div class="gmb-mb-12">
                                        <label for="gmb_seo_fb_desc" class="gmb-field-label">Facebook Description</label>
                                        <textarea id="gmb_seo_fb_desc" rows="3" class="gmb-field-textarea" placeholder="Facebook description override..."><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true)); ?></textarea>
                                    </div>

                                    <div class="gmb-mb-14">
                                        <label for="gmb_seo_fb_image" class="gmb-field-label">Facebook Image</label>
                                        <div class="gmb-image-upload-row">
                                            <input type="text" id="gmb_seo_fb_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-field-input" placeholder="No image selected..." />
                                            <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_fb_image">Select Image</button>
                                        </div>
                                        <div class="gmb-social-image-preview <?php echo !empty(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)) ? 'is-active' : ''; ?>" id="gmb_seo_fb_image_preview">
                                            <img src="<?php echo esc_url(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-img-preview-sm" />
                                        </div>
                                    </div>
                                </div>

                                <div class="gmb-field-group gmb-pt-16-bt">
                                    <h3 class="gmb-social-section-title">Twitter Settings</h3>
                                    
                                    <div class="gmb-mb-12">
                                        <label for="gmb_seo_tw_title" class="gmb-field-label">Twitter Title</label>
                                        <input type="text" id="gmb_seo_tw_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_title', true)); ?>" class="gmb-field-input" placeholder="Twitter title override..." />
                                    </div>
                                    
                                    <div class="gmb-mb-12">
                                        <label for="gmb_seo_tw_desc" class="gmb-field-label">Twitter Description</label>
                                        <textarea id="gmb_seo_tw_desc" rows="3" class="gmb-field-textarea" placeholder="Twitter description override..."><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true)); ?></textarea>
                                    </div>

                                    <div class="gmb-mb-14">
                                        <label for="gmb_seo_tw_image" class="gmb-field-label">Twitter Image</label>
                                        <div class="gmb-image-upload-row">
                                            <input type="text" id="gmb_seo_tw_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-field-input" placeholder="No image selected..." />
                                            <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_tw_image">Select Image</button>
                                        </div>
                                        <div class="gmb-social-image-preview <?php echo !empty(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)) ? 'is-active' : ''; ?>" id="gmb_seo_tw_image_preview">
                                            <img src="<?php echo esc_url(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-img-preview-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="gmb-modal-footer">
                            <button type="button" class="button button-primary" id="gmb-modal-save-btn">Save changes</button>
                        </div>
                    </div>
                </div>

                <div class="gmb-audit-wrapper gmb-mt-14">
                    <!-- Basic SEO Accordion (Expanded by default) -->
                    <div class="gmb-accordion-section" id="gmb-acc-basic">
                        <div class="gmb-accordion-header">
                            <div class="gmb-accordion-title-area">
                                <span class="gmb-text-bold">Basic SEO</span>
                                <span class="gmb-badge-count error" id="gmb-basic-count">0 Errors</span>
                            </div>
                            <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="gmb-accordion-content">
                            <div class="gmb-accordion-inner">
                                <ul class="gmb-audit-list" id="gmb-basic-list"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Accordion (Collapsed by default) -->
                    <div class="gmb-accordion-section collapsed" id="gmb-acc-additional">
                        <div class="gmb-accordion-header">
                            <div class="gmb-accordion-title-area">
                                <span class="gmb-text-bold">Additional</span>
                                <span class="gmb-badge-count error" id="gmb-additional-count">0 Errors</span>
                            </div>
                            <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="gmb-accordion-content">
                            <div class="gmb-accordion-inner">
                                <ul class="gmb-audit-list" id="gmb-additional-list"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Title Readability Accordion (Collapsed by default) -->
                    <div class="gmb-accordion-section collapsed" id="gmb-acc-title">
                        <div class="gmb-accordion-header">
                            <div class="gmb-accordion-title-area">
                                <span class="gmb-text-bold">Title Readability</span>
                                <span class="gmb-badge-count error" id="gmb-title-count">0 Errors</span>
                            </div>
                            <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="gmb-accordion-content">
                            <div class="gmb-accordion-inner">
                                <ul class="gmb-audit-list" id="gmb-title-list"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Content Readability Accordion (Collapsed by default) -->
                    <div class="gmb-accordion-section collapsed" id="gmb-acc-content">
                        <div class="gmb-accordion-header">
                            <div class="gmb-accordion-title-area">
                                <span class="gmb-text-bold">Content Readability</span>
                                <span class="gmb-badge-count error" id="gmb-content-count">0 Errors</span>
                            </div>
                            <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="gmb-accordion-content">
                            <div class="gmb-accordion-inner">
                                <ul class="gmb-audit-list" id="gmb-content-list"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gmb-tab-content" id="gmb-tab-advanced">
                <!-- Row 1: ROBOTS META -->
                <div class="gmb-setting-row">
                    <div class="gmb-setting-label-col">
                        <label class="gmb-setting-row-label">Robots Meta</label>
                    </div>
                    <div class="gmb-setting-content-col">
                        <div class="gmb-robots-meta-grid">
                            <div class="gmb-robots-meta-col">
                                <label class="gmb-robot-option">
                                    <input type="checkbox" id="gmb_seo_robot_index" name="gmb_seo_robots[]" value="index" <?php checked(!in_array('noindex', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">Index</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Instructs search engines to index and show these pages in the search results">?</span>
                                </label>
                                <label class="gmb-robot-option">
                                    <input type="checkbox" name="gmb_seo_robots[]" value="nofollow" <?php checked(in_array('nofollow', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">Nofollow</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Instructs search engines not to follow the links on this page">?</span>
                                </label>
                                <label class="gmb-robot-option">
                                    <input type="checkbox" name="gmb_seo_robots[]" value="noimageindex" <?php checked(in_array('noimageindex', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">No Image Index</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Prevents search engines from indexing the images on this page">?</span>
                                </label>
                            </div>
                            <div class="gmb-robots-meta-col">
                                <label class="gmb-robot-option">
                                    <input type="checkbox" id="gmb_seo_robot_noindex" name="gmb_seo_robots[]" value="noindex" <?php checked(in_array('noindex', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">No Index</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Prevents search engines from indexing and showing these pages in the search results">?</span>
                                </label>
                                <label class="gmb-robot-option">
                                    <input type="checkbox" name="gmb_seo_robots[]" value="noarchive" <?php checked(in_array('noarchive', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">No Archive</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Prevents search engines from showing a Cached link for this page">?</span>
                                </label>
                                <label class="gmb-robot-option">
                                    <input type="checkbox" name="gmb_seo_robots[]" value="nosnippet" <?php checked(in_array('nosnippet', $robots_array)); ?> />
                                    <span class="gmb-robot-option-name">No Snippet</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Prevents search engines from showing a snippet of this page in the search results">?</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: ADVANCED ROBOTS META -->
                <div class="gmb-setting-row">
                    <div class="gmb-setting-label-col">
                        <label class="gmb-setting-row-label">Advanced Robots Meta</label>
                    </div>
                    <div class="gmb-setting-content-col">
                        <div class="gmb-adv-robots-list">
                            <div class="gmb-adv-robot-item">
                                <label class="gmb-robot-option gmb-adv-robot-label">
                                    <input type="checkbox" class="gmb-adv-robot-toggle" checked />
                                    <span class="gmb-robot-option-name">Max Snippet</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Specify a maximum text-length, in characters, of a snippet for your page.">?</span>
                                </label>
                                <div class="gmb-adv-robot-input-wrap">
                                    <input type="text" name="gmb_seo_max_snippet" value="<?php echo esc_attr($max_snippet ?: '-1'); ?>" class="gmb-field-input gmb-input-compact" placeholder="-1" />
                                </div>
                            </div>
                            <div class="gmb-adv-robot-item">
                                <label class="gmb-robot-option gmb-adv-robot-label">
                                    <input type="checkbox" class="gmb-adv-robot-toggle" checked />
                                    <span class="gmb-robot-option-name">Max Video Preview</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Specify a maximum duration in seconds of an animated video preview.">?</span>
                                </label>
                                <div class="gmb-adv-robot-input-wrap">
                                    <input type="text" name="gmb_seo_max_video" value="<?php echo esc_attr($max_video ?: '-1'); ?>" class="gmb-field-input gmb-input-compact" placeholder="-1" />
                                </div>
                            </div>
                            <div class="gmb-adv-robot-item">
                                <label class="gmb-robot-option gmb-adv-robot-label">
                                    <input type="checkbox" class="gmb-adv-robot-toggle" checked />
                                    <span class="gmb-robot-option-name">Max Image Preview</span>
                                    <span class="gmb-help-tip" data-gmb-tooltip="Specify a maximum size of image preview to be shown for images on this page.">?</span>
                                </label>
                                <div class="gmb-adv-robot-input-wrap">
                                    <select name="gmb_seo_max_image" class="gmb-field-select gmb-input-compact">
                                        <option value="large" <?php selected($max_image, 'large'); ?>>Large</option>
                                        <option value="standard" <?php selected($max_image, 'standard'); ?>>Standard</option>
                                        <option value="none" <?php selected($max_image, 'none'); ?>>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: CANONICAL URL -->
                <div class="gmb-setting-row">
                    <div class="gmb-setting-label-col">
                        <label for="gmb_seo_canonical_adv" class="gmb-setting-row-label">
                            Canonical URL 
                            <span class="gmb-help-tip" data-gmb-tooltip="The canonical URL tells search engines which URL is the master copy of a page.">?</span>
                        </label>
                    </div>
                    <div class="gmb-setting-content-col">
                        <input type="url" id="gmb_seo_canonical_adv" name="gmb_seo_canonical" value="<?php echo esc_attr($canonical); ?>" class="gmb-field-input" placeholder="<?php echo esc_url(get_permalink($post->ID)); ?>" />
                    </div>
                </div>

                <!-- Row 4: BREADCRUMB TITLE -->
                <div class="gmb-setting-row">
                    <div class="gmb-setting-label-col">
                        <label for="gmb_seo_breadcrumb_title" class="gmb-setting-row-label">
                            Breadcrumb Title 
                            <span class="gmb-help-tip" data-gmb-tooltip="The Breadcrumb Title used for this specific post/page in navigation breadcrumbs.">?</span>
                        </label>
                    </div>
                    <div class="gmb-setting-content-col">
                        <input type="text" id="gmb_seo_breadcrumb_title" name="gmb_seo_breadcrumb_title" value="<?php echo esc_attr($breadcrumb_title); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($post->post_title); ?>" />
                    </div>
                </div>

                <!-- Row 5: REDIRECT -->
                <div class="gmb-setting-row gmb-setting-row-last">
                    <div class="gmb-setting-label-col">
                        <label class="gmb-setting-row-label">Redirect</label>
                    </div>
                    <div class="gmb-setting-content-col">
                        <label class="gmb-form-toggle">
                            <input type="checkbox" id="gmb_enable_redirect_toggle" class="gmb-toggle-input" <?php checked(!empty($redirect_url)); ?> />
                            <span class="gmb-form-toggle__track">
                                <span class="gmb-form-toggle__thumb"></span>
                            </span>
                        </label>
                        <div id="gmb-redirect-details-box" class="gmb-redirect-box <?php echo !empty($redirect_url) ? '' : 'is-hidden'; ?>">
                            <div class="gmb-redirect-field gmb-flex-2">
                                <label for="gmb_seo_redirect_url" class="gmb-field-label gmb-text-xs">Destination URL</label>
                                <input type="url" id="gmb_seo_redirect_url" name="gmb_seo_redirect_url" value="<?php echo esc_attr($redirect_url); ?>" class="gmb-field-input" placeholder="https://example.com/target-page/" />
                            </div>
                            <div class="gmb-redirect-field gmb-flex-1">
                                <label for="gmb_seo_redirect_code" class="gmb-field-label gmb-text-xs">Redirection Type</label>
                                <select id="gmb_seo_redirect_code" name="gmb_seo_redirect_code" class="gmb-field-select gmb-input-h38">
                                    <option value="301" <?php selected($redirect_code, '301'); ?>>301 Permanent Move</option>
                                    <option value="302" <?php selected($redirect_code, '302'); ?>>302 Temporary Move</option>
                                    <option value="307" <?php selected($redirect_code, '307'); ?>>307 Temporary Redirect</option>
                                    <option value="410" <?php selected($redirect_code, '410'); ?>>410 Content Deleted</option>
                                    <option value="451" <?php selected($redirect_code, '451'); ?>>451 Unavailable For Legal Reasons</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gmb-tab-content" id="gmb-tab-schema">
                <div class="gmb-schema-header-toolbar">
                    <div class="gmb-schema-section-intro">
                        <h4 class="gmb-section-heading">Configure Schema Markup</h4>
                        <p class="gmb-text-muted-desc">Configure Schema Markup for your pages. Search engines use structured data to display rich results in SERPs.</p>
                    </div>
                    <button type="button" class="button button-primary gmb-schema-generator-btn" id="gmb-schema-generator-open-btn">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <span>Schema Generator</span>
                    </button>
                </div>
                
                <div class="gmb-schema-in-use-wrapper">
                    <h3 class="gmb-heading-4">Schema in Use</h3>
                    
                    <?php
                    $active_schemas = get_post_meta($post->ID, '_gmb_ranker_active_schemas', true);
                    if (empty($active_schemas)) {
                        $stored_type = get_post_meta($post->ID, '_gmb_ranker_schema_type', true);
                        $active_schemas = !empty($stored_type) ? array($stored_type) : array('Article');
                    }
                    if (is_string($active_schemas)) {
                        $active_schemas = array_filter(array_map('trim', explode(',', $active_schemas)));
                    }
                    if (empty($active_schemas)) {
                        $active_schemas = array('Article');
                    }
                    ?>
                    <input type="hidden" id="gmb_seo_active_schemas" name="gmb_seo_active_schemas" value="<?php echo esc_attr(implode(',', $active_schemas)); ?>" />

                    <div id="gmb-schema-in-use-list" class="gmb-schema-active-list">
                        <?php foreach ($active_schemas as $s_type): 
                            if (empty($s_type)) continue;
                        ?>
                            <div class="gmb-schema-active-card" data-schema-active="<?php echo esc_attr($s_type); ?>">
                                <div class="gmb-schema-active-info">
                                    <span class="gmb-schema-active-icon">
                                        <?php echo gmb_ranker_get_schema_icon_svg($s_type); ?>
                                    </span>
                                    <strong class="gmb-schema-active-title"><?php echo esc_html($s_type); ?></strong>
                                </div>
                                <div class="gmb-schema-active-actions">
                                    <button type="button" class="gmb-schema-action-btn gmb-schema-edit-btn" data-type="<?php echo esc_attr($s_type); ?>" title="Edit Schema">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button type="button" class="gmb-schema-action-btn gmb-schema-code-btn" data-type="<?php echo esc_attr($s_type); ?>" title="Code Validation">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button type="button" class="gmb-schema-action-btn gmb-remove-schema-btn" data-type="<?php echo esc_attr($s_type); ?>" title="Delete Schema">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Schema Generator Modal (Rank Math Template Catalog) -->
                <div class="gmb-modal-backdrop" id="gmb-schema-modal">
                    <div class="gmb-modal-box gmb-modal-box-schema">
                        <div class="gmb-modal-header">
                            <span class="gmb-modal-title">Schema Generator</span>
                            <span class="gmb-modal-close" id="gmb-schema-modal-close-btn" title="Close">&#x2715;</span>
                        </div>
                        <div class="gmb-modal-tabs">
                            <span class="gmb-modal-tab-btn active" data-schema-tab="schema-tab-templates">Schema Templates</span>
                            <span class="gmb-modal-tab-btn" data-schema-tab="schema-tab-import">Import</span>
                            <span class="gmb-modal-tab-btn" data-schema-tab="schema-tab-custom">Custom Schema</span>
                        </div>
                        <div class="gmb-modal-body gmb-modal-body-scroll">
                            
                            <!-- Schema Templates Tab content -->
                            <div class="gmb-schema-tab-content active" id="schema-tab-templates">
                                <h4 class="gmb-schema-grid-title">Available Schema Templates</h4>
                                
                                <div class="gmb-schema-template-grid" id="gmb-schema-templates-grid">
                                    
                                    <div class="gmb-schema-template-card" data-type="Article">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Article</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Article">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Book">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Book</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Book">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Course">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Course</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Course">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Dataset">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Dataset</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Dataset">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Event">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Event</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Event">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="FAQ Page">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">FAQ Page</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="FAQ Page">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="FactCheck">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">FactCheck</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="FactCheck">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="HowTo">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">HowTo</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="HowTo">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Job Posting">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Job Posting</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Job Posting">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Movie">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Movie</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Movie">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Music">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Music</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Music">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Person">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Person</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Person">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Product">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Product</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Product">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Recipe">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Recipe</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Recipe">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Restaurant">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v4M21 2v20M21 2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4"></path></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Restaurant</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Restaurant">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Service">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Service</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Service">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Software">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Software</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Software">+ Use</button>
                                    </div>

                                    <div class="gmb-schema-template-card" data-type="Video">
                                        <div class="gmb-schema-template-info">
                                            <div class="gmb-schema-template-icon">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                                            </div>
                                            <span class="gmb-schema-template-name">Video</span>
                                        </div>
                                        <button type="button" class="button gmb-use-schema-btn" data-type="Video">+ Use</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Import Tab content -->
                            <div class="gmb-schema-tab-content" id="schema-tab-import">
                                <h4 class="gmb-schema-grid-title">Import Schema Markup</h4>
                                <div class="gmb-field-group">
                                    <label for="gmb_seo_schema_import_input" class="gmb-field-label">Paste JSON-LD or HTML containing JSON-LD</label>
                                    <textarea id="gmb_seo_schema_import_input" rows="9" class="gmb-field-textarea code-font" placeholder="Paste schema block here..."></textarea>
                                </div>
                                <button type="button" class="button button-primary gmb-btn-primary-bold" id="gmb-schema-import-submit-btn">Import Schema</button>
                            </div>
                            
                            <!-- Custom Schema Tab content -->
                            <div class="gmb-schema-tab-content" id="schema-tab-custom">
                                <h4 class="gmb-schema-grid-title">Custom Schema JSON-LD Markup</h4>
                                <div class="gmb-field-group">
                                    <textarea id="gmb_seo_schema_input" name="gmb_seo_schema" rows="9" class="gmb-field-textarea code-font" placeholder='{"@context": "https://schema.org", "@type": "Article", ...}'><?php echo esc_textarea($json_ld); ?></textarea>
                                    <p class="gmb-field-help">Modify schema values directly. Be sure to insert valid JSON format.</p>
                                </div>
                            </div>
                            
                        </div>
                        <div class="gmb-modal-footer">
                            <button type="button" class="button button-primary gmb-btn-action-primary" id="gmb-schema-modal-save-btn">Save Schema</button>
                        </div>
                    </div>
                </div>

                <!-- Schema Builder Modal (Rank Math Exact Design) -->
                <div class="gmb-modal-backdrop" id="gmb-schema-builder-modal">
                    <div class="gmb-modal-box gmb-modal-box-builder">
                        <div class="gmb-modal-header">
                            <div class="gmb-flex-center-gap-sm">
                                <span class="gmb-modal-title">Schema Builder</span>
                            </div>
                            <span class="gmb-modal-close" id="gmb-schema-builder-close-btn" title="Close">&#x2715;</span>
                        </div>
                        <div class="gmb-builder-subtabs-bar">
                            <div class="gmb-builder-tabs-nav">
                                <button type="button" class="gmb-modal-tab-btn active" id="gmb-builder-tab-btn-edit" data-builder-tab="edit">Edit</button>
                                <button type="button" class="gmb-modal-tab-btn" id="gmb-builder-tab-btn-validation" data-builder-tab="validation">Code Validation</button>
                            </div>
                            <span class="gmb-builder-info-icon" title="Customize Schema properties for this post"></span>
                        </div>
                        <div class="gmb-modal-body gmb-modal-body-scroll gmb-builder-modal-body">
                            
                            <!-- Edit Panel -->
                            <div class="gmb-builder-panel active" id="gmb-builder-panel-edit">
                                
                                <div class="gmb-builder-schema-header-row">
                                    <span class="gmb-builder-type-pill" id="gmb-builder-schema-type-label">Article</span>
                                </div>
                                
                                <!-- Simple Standard Editor Content -->
                                <div id="gmb-builder-simple-mode-container" class="gmb-builder-fields-list">
                                    <!-- Inputs dynamically populated via JS depending on Schema type -->
                                </div>
                                
                                <!-- Advanced Property Tree Content (Linked Tree UI) -->
                                <div id="gmb-builder-advanced-mode-container" class="gmb-builder-advanced-list">
                                    <div class="gmb-builder-tree-root">
                                        <div class="gmb-builder-tree-root-header">
                                            <div class="gmb-builder-tree-root-badge">
                                                <input type="text" class="gmb-tree-root-type-input" id="gmb-builder-adv-root-type" value="Article" readonly />
                                            </div>
                                            <div class="gmb-builder-tree-root-actions">
                                                <button type="button" class="gmb-group-action-link" id="gmb-builder-add-prop-btn" title="Add Property">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                    <span>Add Property</span>
                                                </button>
                                                <button type="button" class="gmb-group-action-link" id="gmb-builder-add-group-btn" title="Add Property Group">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                                    <span>Add Property Group</span>
                                                </button>
                                                <button type="button" class="gmb-group-action-link gmb-group-action-delete" id="gmb-builder-reset-tree-btn" title="Reset to Defaults">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    <span>Delete</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="gmb-builder-adv-rows gmb-builder-tree-branch" id="gmb-builder-advanced-properties-list">
                                            <!-- Key-value fields rendered dynamically with tree branch connectors -->
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                            <!-- Code Validation Panel -->
                            <div class="gmb-builder-panel" id="gmb-builder-panel-validation">
                                <div class="gmb-code-preview-header">
                                    <span class="gmb-code-preview-title">JSON-LD Structured Data Preview</span>
                                    <div class="gmb-code-actions">
                                        <button type="button" class="button button-small" id="gmb-builder-copy-code-btn">Copy Code</button>
                                        <a href="https://search.google.com/test/rich-results" target="_blank" class="button button-small gmb-btn-test-google">Test with Google &#x2197;</a>
                                    </div>
                                </div>
                                <pre class="gmb-json-code-box"><code id="gmb-builder-validation-code" class="language-json"></code></pre>
                            </div>

                        </div>
                        <div class="gmb-modal-footer gmb-builder-footer">
                            <div class="gmb-builder-footer-left">
                                <button type="button" class="button" id="gmb-builder-toggle-mode-btn">Advanced Editor</button>
                                <button type="button" class="button" id="gmb-builder-save-template-btn">Save as Template</button>
                            </div>
                            <div class="gmb-builder-footer-right">
                                <button type="button" class="button button-primary gmb-btn-primary-bold" id="gmb-builder-save-post-btn">Save for this Post</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="gmb-tab-content" id="gmb-tab-social">
                <div class="gmb-social-header-toolbar">
                    <div class="gmb-social-platform-nav">
                        <button type="button" class="gmb-social-tab-btn active" data-social-tab="gmb-social-fb">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="#1877f2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </button>
                        <button type="button" class="gmb-social-tab-btn" data-social-tab="gmb-social-tw">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            <span>Twitter / X</span>
                        </button>
                    </div>
                    <button type="button" class="button gmb-sync-social-btn" id="gmb-sync-social-btn" title="Copy title and description from General SEO settings">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        <span>Sync from General SEO</span>
                    </button>
                </div>

                <!-- Facebook Subtab Pane -->
                <div class="gmb-social-pane active" id="gmb-social-fb">
                    
                    <!-- Facebook Live Mockup Card -->
                    <div class="gmb-social-preview-wrapper">
                        <div class="gmb-social-platform-label">Facebook Feed Live Preview</div>
                        <div class="gmb-fb-preview-card">
                            <?php 
                            $fb_img_val = get_post_meta($post->ID, '_gmb_ranker_facebook_image', true);
                            $feat_img_val = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '';
                            $current_fb_img = $fb_img_val ?: $feat_img_val;
                            $fb_title_val = get_post_meta($post->ID, '_gmb_ranker_facebook_title', true) ?: ($meta_title ?: $post->post_title);
                            $fb_desc_val = get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true) ?: ($meta_desc ?: 'Learn about our trusted services and read full information.');
                            ?>
                            <div id="gmb-fb-preview-img-box" class="gmb-social-image-box gmb-media-upload-trigger" data-target="gmb_seo_fb_image_metabox" title="Click to upload or change Facebook image">
                                <img id="gmb-fb-preview-img" src="<?php echo esc_url($current_fb_img); ?>" class="gmb-social-img-element <?php echo !empty($current_fb_img) ? 'is-active' : ''; ?>" alt="Facebook Preview" />
                                <div id="gmb-fb-preview-placeholder" class="gmb-social-image-placeholder <?php echo !empty($current_fb_img) ? 'is-hidden' : ''; ?>">
                                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <span class="gmb-social-placeholder-hint">1200 × 630 Recommended</span>
                                    <span class="gmb-social-placeholder-subhint">Click to add image</span>
                                </div>
                                <div class="gmb-social-image-overlay">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    <span>Change Image</span>
                                </div>
                            </div>
                            <div class="gmb-social-meta-box-fb">
                                <div class="gmb-social-meta-domain-fb" id="gmb-fb-preview-domain"><?php echo esc_html(strtoupper(wp_parse_url(home_url(), PHP_URL_HOST))); ?></div>
                                <div class="gmb-social-meta-title-fb" id="gmb-fb-preview-title"><?php echo esc_html($fb_title_val); ?></div>
                                <div class="gmb-social-meta-desc-fb" id="gmb-fb-preview-desc"><?php echo esc_html($fb_desc_val); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <div class="gmb-field-header-flex">
                            <label for="gmb_seo_fb_title_metabox" class="gmb-field-label">Facebook Title</label>
                            <span id="gmb-fb-title-counter" class="gmb-char-counter">0 / 60</span>
                        </div>
                        <input type="text" id="gmb_seo_fb_title_metabox" name="gmb_seo_facebook_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_title', true)); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($meta_title ?: $post->post_title); ?>" />
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <div class="gmb-field-header-flex">
                            <label for="gmb_seo_fb_desc_metabox" class="gmb-field-label">Facebook Description</label>
                            <span id="gmb-fb-desc-counter" class="gmb-char-counter">0 / 160</span>
                        </div>
                        <textarea id="gmb_seo_fb_desc_metabox" name="gmb_seo_facebook_desc" rows="3" class="gmb-field-textarea" placeholder="<?php echo esc_attr($meta_desc ?: 'Summarize your page for Facebook...'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true)); ?></textarea>
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <label for="gmb_seo_fb_image_metabox" class="gmb-field-label">Facebook Image (1200 × 630 px)</label>
                        <div class="gmb-image-upload-row">
                            <input type="text" id="gmb_seo_fb_image_metabox" name="gmb_seo_facebook_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-field-input" placeholder="<?php echo !empty($feat_img_val) ? esc_url($feat_img_val) : 'https://example.com/image.jpg'; ?>" />
                            <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_fb_image_metabox">Select Image</button>
                            <button type="button" class="button button-link-delete gmb-social-clear-img-btn <?php echo !empty($fb_img_val) ? 'is-active' : ''; ?>" data-target="gmb_seo_fb_image_metabox"> Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Twitter / X Subtab Pane -->
                <div class="gmb-social-pane" id="gmb-social-tw">
                    
                    <!-- Twitter Live Mockup Card -->
                    <div class="gmb-social-preview-wrapper">
                        <div class="gmb-social-platform-label">X / Twitter Card Live Preview</div>
                        <?php 
                        $tw_img_val = get_post_meta($post->ID, '_gmb_ranker_twitter_image', true);
                        $current_tw_img = $tw_img_val ?: ($fb_img_val ?: $feat_img_val);
                        $tw_title_val = get_post_meta($post->ID, '_gmb_ranker_twitter_title', true) ?: ($fb_title_val ?: ($meta_title ?: $post->post_title));
                        $tw_desc_val = get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true) ?: ($fb_desc_val ?: ($meta_desc ?: 'Twitter summary description preview...'));
                        $tw_card_type = get_post_meta($post->ID, '_gmb_ranker_twitter_card_type', true) ?: 'summary_large_image';
                        ?>
                        <div class="gmb-tw-preview-card <?php echo ($tw_card_type === 'summary') ? 'gmb-tw-card--summary' : 'gmb-tw-card--large'; ?>" id="gmb-tw-card-container">
                            <div id="gmb-tw-preview-img-box" class="gmb-social-image-box gmb-media-upload-trigger" data-target="gmb_seo_tw_image_metabox" title="Click to upload or change Twitter image">
                                <img id="gmb-tw-preview-img" src="<?php echo esc_url($current_tw_img); ?>" class="gmb-social-img-element <?php echo !empty($current_tw_img) ? 'is-active' : ''; ?>" alt="Twitter Preview" />
                                <div id="gmb-tw-preview-placeholder" class="gmb-social-image-placeholder <?php echo !empty($current_tw_img) ? 'is-hidden' : ''; ?>">
                                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <span class="gmb-social-placeholder-hint">1200 × 600 Recommended</span>
                                    <span class="gmb-social-placeholder-subhint">Click to add image</span>
                                </div>
                                <div class="gmb-social-image-overlay">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    <span>Change Image</span>
                                </div>
                            </div>
                            <div class="gmb-social-meta-box-tw">
                                <div class="gmb-social-meta-domain-tw" id="gmb-tw-preview-domain"><?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?></div>
                                <div class="gmb-social-meta-title-tw" id="gmb-tw-preview-title"><?php echo esc_html($tw_title_val); ?></div>
                                <div class="gmb-social-meta-desc-tw" id="gmb-tw-preview-desc"><?php echo esc_html($tw_desc_val); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <label for="gmb_seo_tw_card_type" class="gmb-field-label">Twitter Card Type</label>
                        <select id="gmb_seo_tw_card_type" name="gmb_seo_twitter_card_type" class="gmb-field-select gmb-input-h38">
                            <option value="summary_large_image" <?php selected($tw_card_type, 'summary_large_image'); ?>>Summary Card with Large Image (Recommended)</option>
                            <option value="summary" <?php selected($tw_card_type, 'summary'); ?>>Summary Card (Compact Thumbnail)</option>
                            <option value="app" <?php selected($tw_card_type, 'app'); ?>>App Card</option>
                            <option value="player" <?php selected($tw_card_type, 'player'); ?>>Player Card (Video / Audio)</option>
                        </select>
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <div class="gmb-field-header-flex">
                            <label for="gmb_seo_tw_title_metabox" class="gmb-field-label">Twitter Title</label>
                            <span id="gmb-tw-title-counter" class="gmb-char-counter">0 / 60</span>
                        </div>
                        <input type="text" id="gmb_seo_tw_title_metabox" name="gmb_seo_twitter_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_title', true)); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($fb_title_val ?: ($meta_title ?: $post->post_title)); ?>" />
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <div class="gmb-field-header-flex">
                            <label for="gmb_seo_tw_desc_metabox" class="gmb-field-label">Twitter Description</label>
                            <span id="gmb-tw-desc-counter" class="gmb-char-counter">0 / 160</span>
                        </div>
                        <textarea id="gmb_seo_tw_desc_metabox" name="gmb_seo_twitter_desc" rows="3" class="gmb-field-textarea" placeholder="<?php echo esc_attr($fb_desc_val ?: ($meta_desc ?: 'Summarize for Twitter...')); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true)); ?></textarea>
                    </div>

                    <div class="gmb-field-group gmb-mb-14">
                        <label for="gmb_seo_tw_image_metabox" class="gmb-field-label">Twitter Image</label>
                        <div class="gmb-image-upload-row">
                            <input type="text" id="gmb_seo_tw_image_metabox" name="gmb_seo_twitter_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-field-input" placeholder="<?php echo !empty($current_tw_img) ? esc_url($current_tw_img) : 'https://example.com/image.jpg'; ?>" />
                            <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_tw_image_metabox">Select Image</button>
                            <button type="button" class="button button-link-delete gmb-social-clear-img-btn <?php echo !empty($tw_img_val) ? 'is-active' : ''; ?>" data-target="gmb_seo_tw_image_metabox"> Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
