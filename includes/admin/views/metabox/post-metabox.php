<?php
/**
 * Post SEO Admin Metabox View
 *
 * Enterprise-grade, accessible, data-driven post SEO metabox view.
 * Displays and edits page-level SEO titles, descriptions, focus keywords, robots directives,
 * redirects, schema markup, and social sharing previews.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('gmb_ranker_get_schema_icon_svg')) {
    /**
     * Helper to return static SVG icons for schema types
     *
     * @param string $type
     * @return string Static SVG HTML string
     */
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

$meta_title       = get_post_meta($post->ID, '_gmb_ranker_seo_title', true) ?: '';
$meta_title       = $meta_title ?: get_post_meta($post->ID, '_yoast_wpseo_title', true);
$meta_title       = $meta_title ?: get_post_meta($post->ID, 'rank_math_title', true);
$meta_desc        = get_post_meta($post->ID, '_gmb_ranker_seo_description', true) ?: '';
$meta_desc        = $meta_desc ?: get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
$meta_desc        = $meta_desc ?: get_post_meta($post->ID, 'rank_math_description', true);
$canonical        = get_post_meta($post->ID, '_gmb_ranker_seo_canonical', true) ?: '';
$robots           = get_post_meta($post->ID, '_gmb_ranker_seo_robots', true) ?: 'index, follow';
$json_ld          = get_post_meta($post->ID, '_gmb_ranker_json_ld', true) ?: '';
$breadcrumb_title = get_post_meta($post->ID, '_gmb_ranker_breadcrumb_title', true) ?: '';
$redirect_url     = get_post_meta($post->ID, '_gmb_ranker_redirect_url', true) ?: '';
$redirect_code    = get_post_meta($post->ID, '_gmb_ranker_redirect_code', true) ?: '301';
$max_snippet      = get_post_meta($post->ID, '_gmb_ranker_seo_max_snippet', true) ?: get_post_meta($post->ID, '_gmb_ranker_max_snippet', true) ?: '-1';
$max_video        = get_post_meta($post->ID, '_gmb_ranker_seo_max_video', true) ?: get_post_meta($post->ID, '_gmb_ranker_max_video', true) ?: '-1';
$max_image        = get_post_meta($post->ID, '_gmb_ranker_seo_max_image', true) ?: get_post_meta($post->ID, '_gmb_ranker_max_image', true) ?: 'large';
$max_snippet_enabled = get_post_meta($post->ID, '_gmb_ranker_seo_max_snippet_enabled', true);
$max_video_enabled   = get_post_meta($post->ID, '_gmb_ranker_seo_max_video_enabled', true);
$max_image_enabled   = get_post_meta($post->ID, '_gmb_ranker_seo_max_image_enabled', true);
$max_snippet_enabled = $max_snippet_enabled === '' ? '1' : $max_snippet_enabled;
$max_video_enabled   = $max_video_enabled === '' ? '1' : $max_video_enabled;
$max_image_enabled   = $max_image_enabled === '' ? '1' : $max_image_enabled;

$focus_keyword    = get_post_meta($post->ID, '_gmb_ranker_focus_keyword', true) ?: '';
$focus_keyword    = $focus_keyword ?: get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
$focus_keyword    = $focus_keyword ?: get_post_meta($post->ID, 'rank_math_focus_keyword', true);
$robots_array     = array_map('trim', explode(',', strtolower($robots)));
?>
<div class="gmb-seo-meta-container">
    <div class="gmb-seo-header-area">
        <div class="gmb-seo-tabs" role="tablist">
            <button type="button" class="gmb-tab-btn active" data-tab="gmb-tab-general" role="tab" aria-selected="true" aria-controls="gmb-tab-general">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <?php esc_html_e('General', 'gmb-ranker-seo-automation'); ?>
            </button>
            <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-advanced" role="tab" aria-selected="false" aria-controls="gmb-tab-advanced">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                <?php esc_html_e('Advanced', 'gmb-ranker-seo-automation'); ?>
            </button>
            <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-schema" role="tab" aria-selected="false" aria-controls="gmb-tab-schema">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                <?php esc_html_e('Schema', 'gmb-ranker-seo-automation'); ?>
            </button>
            <button type="button" class="gmb-tab-btn" data-tab="gmb-tab-social" role="tab" aria-selected="false" aria-controls="gmb-tab-social">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="gmb-icon gmb-icon--sm" aria-hidden="true"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                <?php esc_html_e('Social', 'gmb-ranker-seo-automation'); ?>
            </button>
        </div>
        <div class="gmb-seo-header-actions">
            <button type="button" id="gmb-ai-optimize-post-btn" class="gmb-btn--ai-post" data-action="gmb-open-ai-modal">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2l2.4 5.4L20 10l-5.6 2.4L12 18l-2.4-5.6L4 10l5.6-2.4z"/>
                    <path d="M19 17l.8 1.8L21.5 19.5 19.7 20.3 18.9 22l-.8-1.7-1.8-.8 1.8-.8z"/>
                </svg>
                <span><?php esc_html_e('AI Auto-Fix Page SEO', 'gmb-ranker-seo-automation'); ?></span>
            </button>
        </div>
    </div>

    <!-- General Tab Panel -->
    <div class="gmb-tab-content active" id="gmb-tab-general" role="tabpanel">
        <div class="gmb-preview-box">
            <div class="gmb-preview-header-row">
                <div class="gmb-preview-title-wrap">
                    <h4 class="gmb-preview-heading"><?php esc_html_e('Google Search Snippet Preview', 'gmb-ranker-seo-automation'); ?></h4>
                    <div class="gmb-preview-device-toggle">
                        <button type="button" class="gmb-device-btn active" data-device="desktop" title="<?php esc_attr_e('Desktop Preview', 'gmb-ranker-seo-automation'); ?>">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                            <span><?php esc_html_e('Desktop', 'gmb-ranker-seo-automation'); ?></span>
                        </button>
                        <button type="button" class="gmb-device-btn" data-device="mobile" title="<?php esc_attr_e('Mobile Preview', 'gmb-ranker-seo-automation'); ?>">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            <span><?php esc_html_e('Mobile', 'gmb-ranker-seo-automation'); ?></span>
                        </button>
                    </div>
                </div>
                <button type="button" class="button button-primary gmb-edit-snippet-btn" id="gmb-edit-snippet-btn">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    <?php esc_html_e('Edit Snippet', 'gmb-ranker-seo-automation'); ?>
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
                <p class="gmb-google-snippet" id="gmb-preview-snippet"><?php echo esc_html($meta_desc ?: esc_html__('Please enter a Meta Description below to preview this result...', 'gmb-ranker-seo-automation')); ?></p>
            </div>
        </div>

        <?php $saved_score = get_post_meta($post->ID, '_gmb_ranker_seo_score', true) ?: '0'; ?>
        <div class="gmb-field-group">
            <label for="gmb_seo_focus_keyword_input" class="gmb-field-label"><?php esc_html_e('Focus Keyword', 'gmb-ranker-seo-automation'); ?> <span class="gmb-help-tip gmb-ml-4" data-gmb-tooltip="<?php esc_attr_e('Focus keywords are the key search queries you want this page to rank for.', 'gmb-ranker-seo-automation'); ?>">i</span></label>
            <div class="gmb-focus-keyword-field-wrapper">
                <div class="gmb-keyword-container" id="gmb-keyword-container-wrapper">
                    <!-- Pills render dynamically via JS -->
                    <input type="text" id="gmb_seo_focus_keyword_input" class="gmb-keyword-input-el" placeholder="<?php esc_attr_e('Type keyword and press Enter...', 'gmb-ranker-seo-automation'); ?>" />
                </div>
                <?php
                $saved_score_num = intval($saved_score);
                if (empty($focus_keyword)) {
                    $badge_color_class = 'score-unconfigured';
                } else {
                    $badge_color_class = ($saved_score_num >= 80) ? 'score-good' : (($saved_score_num >= 40) ? 'score-ok' : 'score-poor');
                }
                ?>
                <div class="gmb-keyword-score <?php echo esc_attr($badge_color_class); ?>" id="gmb-metabox-score-badge" title="<?php esc_attr_e('Overall on-page SEO score', 'gmb-ranker-seo-automation'); ?>">
                    <span id="gmb-metabox-score-val"><?php echo esc_html($saved_score); ?></span>/100
                </div>
            </div>
            <div class="gmb-audit-freshness" id="gmb-audit-freshness" data-analysis-hash="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_seo_analysis_hash', true)); ?>">
                <?php if (get_post_meta($post->ID, '_gmb_ranker_seo_analysis_hash', true)) : ?>
                    <?php esc_html_e('Saved audit', 'gmb-ranker-seo-automation'); ?>
                <?php else : ?>
                    <?php esc_html_e('Not audited yet', 'gmb-ranker-seo-automation'); ?>
                <?php endif; ?>
            </div>
            <input type="hidden" id="gmb_seo_focus_keyword_hidden" name="gmb_seo_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" />
            <input type="hidden" id="gmb_seo_score_hidden" name="gmb_seo_score" value="<?php echo esc_attr($saved_score); ?>" />
            
            <?php $is_pillar = get_post_meta($post->ID, '_gmb_ranker_seo_is_pillar', true); ?>
            <div id="gmb-seo-no-keyword-notice" class="gmb-no-keyword-notice <?php echo !empty($focus_keyword) ? 'is-hidden' : ''; ?>">
                <span><?php esc_html_e('Add a Focus Keyword to this post to see how well optimized it is.', 'gmb-ranker-seo-automation'); ?></span>
            </div>

            <div class="gmb-pillar-checkbox-row">
                <input type="checkbox" id="gmb_seo_is_pillar_input" name="gmb_seo_is_pillar" value="1" <?php checked($is_pillar, '1'); ?> />
                <label for="gmb_seo_is_pillar_input" class="gmb-pillar-label"><?php esc_html_e('This post is Pillar Content', 'gmb-ranker-seo-automation'); ?></label>
                <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Pillar content pages are the core foundation pages of your site targeting main keywords.', 'gmb-ranker-seo-automation'); ?>">i</span>
            </div>
        </div>

        <!-- Preview Snippet Editor Modal -->
        <div class="gmb-modal-backdrop" id="gmb-snippet-modal" aria-hidden="true" role="dialog">
            <div class="gmb-modal-box">
                <div class="gmb-modal-header">
                    <span class="gmb-modal-title"><?php esc_html_e('Preview Snippet Editor', 'gmb-ranker-seo-automation'); ?></span>
                    <span class="gmb-modal-close" id="gmb-modal-close-btn" role="button" tabindex="0" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>">&#x2715;</span>
                </div>
                <div class="gmb-modal-tabs" role="tablist">
                    <span class="gmb-modal-tab-btn active" data-modal-tab="gmb-modal-tab-general" role="tab"><?php esc_html_e('General', 'gmb-ranker-seo-automation'); ?></span>
                    <span class="gmb-modal-tab-btn" data-modal-tab="gmb-modal-tab-social" role="tab"><?php esc_html_e('Social', 'gmb-ranker-seo-automation'); ?></span>
                </div>
                <div class="gmb-modal-body gmb-modal-body-scroll">
                    
                    <!-- General Tab Content -->
                    <div class="gmb-modal-tab-content active" id="gmb-modal-tab-general" role="tabpanel">
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
                            <p class="gmb-google-snippet" id="gmb-modal-preview-snippet"><?php echo esc_html($meta_desc ?: esc_html__('Please enter a Meta Description below to preview this result...', 'gmb-ranker-seo-automation')); ?></p>
                        </div>

                        <div class="gmb-field-group">
                            <div class="gmb-field-header-row">
                                <label for="gmb_seo_title_input" class="gmb-field-label gmb-field-label-nomargin"><?php esc_html_e('SEO Meta Title', 'gmb-ranker-seo-automation'); ?></label>
                                <span class="gmb-metric-char gmb-char-count-bold" id="gmb-title-char-count">0 / 60 chars</span>
                            </div>
                            <input type="text" id="gmb_seo_title_input" name="gmb_seo_title" value="<?php echo esc_attr($meta_title); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($post->post_title); ?>" />
                            <div class="gmb-progress-bar-container">
                                <div class="gmb-progress-bar-fill" id="gmb-title-progress-fill"></div>
                            </div>
                            <div class="gmb-title-ctr-row gmb-mt-8">
                                <div class="gmb-pixel-width-indicator">
                                    <span class="gmb-text-muted-xs"><?php esc_html_e('SERP Pixel Width: ', 'gmb-ranker-seo-automation'); ?></span>
                                    <strong id="gmb-title-pixel-val" class="gmb-pixel-val">0px</strong> / 580px
                                </div>
                            </div>
                        </div>

                        <div class="gmb-field-group">
                            <div class="gmb-field-header-row">
                                <label for="gmb_seo_desc_input" class="gmb-field-label gmb-field-label-nomargin"><?php esc_html_e('SEO Meta Description', 'gmb-ranker-seo-automation'); ?></label>
                                <span class="gmb-metric-char gmb-char-count-bold" id="gmb-desc-char-count">0 / 160 chars</span>
                            </div>
                            <textarea id="gmb_seo_desc_input" name="gmb_seo_description" rows="4" class="gmb-field-textarea" placeholder="<?php esc_attr_e('Summarize your page content here...', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea($meta_desc); ?></textarea>
                            <div class="gmb-progress-bar-container">
                                <div class="gmb-progress-bar-fill" id="gmb-desc-progress-fill"></div>
                            </div>
                        </div>

                        <div class="gmb-field-group">
                            <label for="gmb_seo_canonical_input" class="gmb-field-label"><?php esc_html_e('Canonical URL Override', 'gmb-ranker-seo-automation'); ?></label>
                            <input type="url" id="gmb_seo_canonical_input" name="gmb_seo_canonical" value="<?php echo esc_attr($canonical); ?>" class="gmb-field-input" placeholder="<?php echo esc_url(get_permalink($post->ID)); ?>" />
                            <p class="gmb-field-help"><?php esc_html_e('Allows you to avoid indexing duplicate content issues by mapping pointing domains.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>

                    <!-- Social Tab Content -->
                    <div class="gmb-modal-tab-content gmb-py-5" id="gmb-modal-tab-social" role="tabpanel">
                        <h4 class="gmb-section-heading gmb-heading-4"><?php esc_html_e('Social Sharing Preview Settings', 'gmb-ranker-seo-automation'); ?></h4>
                        
                        <div class="gmb-field-group gmb-mb-18">
                            <h3 class="gmb-social-section-title"><?php esc_html_e('Facebook Settings', 'gmb-ranker-seo-automation'); ?></h3>
                            
                            <div class="gmb-mb-12">
                                <label for="gmb_seo_fb_title" class="gmb-field-label"><?php esc_html_e('Facebook Title', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_seo_fb_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_title', true)); ?>" class="gmb-field-input" placeholder="<?php esc_attr_e('Facebook title override...', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                            
                            <div class="gmb-mb-12">
                                <label for="gmb_seo_fb_desc" class="gmb-field-label"><?php esc_html_e('Facebook Description', 'gmb-ranker-seo-automation'); ?></label>
                                <textarea id="gmb_seo_fb_desc" rows="3" class="gmb-field-textarea" placeholder="<?php esc_attr_e('Facebook description override...', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true)); ?></textarea>
                            </div>

                            <div class="gmb-mb-14">
                                <label for="gmb_seo_fb_image" class="gmb-field-label"><?php esc_html_e('Facebook Image', 'gmb-ranker-seo-automation'); ?></label>
                                <div class="gmb-image-upload-row">
                                    <input type="text" id="gmb_seo_fb_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-field-input" placeholder="<?php esc_attr_e('No image selected...', 'gmb-ranker-seo-automation'); ?>" />
                                    <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_fb_image"><?php esc_html_e('Select Image', 'gmb-ranker-seo-automation'); ?></button>
                                </div>
                                <div class="gmb-social-image-preview <?php echo !empty(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)) ? 'is-active' : ''; ?>" id="gmb_seo_fb_image_preview">
                                    <img src="<?php echo esc_url(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-img-preview-sm" alt="" />
                                </div>
                            </div>
                        </div>

                        <div class="gmb-field-group gmb-pt-16-bt">
                            <h3 class="gmb-social-section-title"><?php esc_html_e('Twitter Settings', 'gmb-ranker-seo-automation'); ?></h3>
                            
                            <div class="gmb-mb-12">
                                <label for="gmb_seo_tw_title" class="gmb-field-label"><?php esc_html_e('Twitter Title', 'gmb-ranker-seo-automation'); ?></label>
                                <input type="text" id="gmb_seo_tw_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_title', true)); ?>" class="gmb-field-input" placeholder="<?php esc_attr_e('Twitter title override...', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                            
                            <div class="gmb-mb-12">
                                <label for="gmb_seo_tw_desc" class="gmb-field-label"><?php esc_html_e('Twitter Description', 'gmb-ranker-seo-automation'); ?></label>
                                <textarea id="gmb_seo_tw_desc" rows="3" class="gmb-field-textarea" placeholder="<?php esc_attr_e('Twitter description override...', 'gmb-ranker-seo-automation'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true)); ?></textarea>
                            </div>

                            <div class="gmb-mb-14">
                                <label for="gmb_seo_tw_image" class="gmb-field-label"><?php esc_html_e('Twitter Image', 'gmb-ranker-seo-automation'); ?></label>
                                <div class="gmb-image-upload-row">
                                    <input type="text" id="gmb_seo_tw_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-field-input" placeholder="<?php esc_attr_e('No image selected...', 'gmb-ranker-seo-automation'); ?>" />
                                    <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_tw_image"><?php esc_html_e('Select Image', 'gmb-ranker-seo-automation'); ?></button>
                                </div>
                                <div class="gmb-social-image-preview <?php echo !empty(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)) ? 'is-active' : ''; ?>" id="gmb_seo_tw_image_preview">
                                    <img src="<?php echo esc_url(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-img-preview-sm" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gmb-modal-footer">
                    <button type="button" class="button button-primary" id="gmb-modal-save-btn"><?php esc_html_e('Save changes', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>

        <div class="gmb-audit-wrapper gmb-mt-14">
            <!-- Basic SEO Accordion -->
            <div class="gmb-accordion-section" id="gmb-acc-basic">
                <div class="gmb-accordion-header" role="button" tabindex="0" aria-expanded="true" aria-controls="gmb-basic-list-wrapper">
                    <div class="gmb-accordion-title-area">
                        <span class="gmb-text-bold"><?php esc_html_e('Basic SEO', 'gmb-ranker-seo-automation'); ?></span>
                        <span class="gmb-badge-count error" id="gmb-basic-count">0 Errors</span>
                    </div>
                    <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
                <div class="gmb-accordion-content" id="gmb-basic-list-wrapper">
                    <div class="gmb-accordion-inner">
                        <ul class="gmb-audit-list" id="gmb-basic-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Additional Accordion -->
            <div class="gmb-accordion-section collapsed" id="gmb-acc-additional">
                <div class="gmb-accordion-header" role="button" tabindex="0" aria-expanded="false" aria-controls="gmb-additional-list-wrapper">
                    <div class="gmb-accordion-title-area">
                        <span class="gmb-text-bold"><?php esc_html_e('Additional', 'gmb-ranker-seo-automation'); ?></span>
                        <span class="gmb-badge-count error" id="gmb-additional-count">0 Errors</span>
                    </div>
                    <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
                <div class="gmb-accordion-content" id="gmb-additional-list-wrapper">
                    <div class="gmb-accordion-inner">
                        <ul class="gmb-audit-list" id="gmb-additional-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Title Readability Accordion -->
            <div class="gmb-accordion-section collapsed" id="gmb-acc-title">
                <div class="gmb-accordion-header" role="button" tabindex="0" aria-expanded="false" aria-controls="gmb-title-list-wrapper">
                    <div class="gmb-accordion-title-area">
                        <span class="gmb-text-bold"><?php esc_html_e('Title Readability', 'gmb-ranker-seo-automation'); ?></span>
                        <span class="gmb-badge-count error" id="gmb-title-count">0 Errors</span>
                    </div>
                    <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
                <div class="gmb-accordion-content" id="gmb-title-list-wrapper">
                    <div class="gmb-accordion-inner">
                        <ul class="gmb-audit-list" id="gmb-title-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Content Readability Accordion -->
            <div class="gmb-accordion-section collapsed" id="gmb-acc-content">
                <div class="gmb-accordion-header" role="button" tabindex="0" aria-expanded="false" aria-controls="gmb-content-list-wrapper">
                    <div class="gmb-accordion-title-area">
                        <span class="gmb-text-bold"><?php esc_html_e('Content Readability', 'gmb-ranker-seo-automation'); ?></span>
                        <span class="gmb-badge-count error" id="gmb-content-count">0 Errors</span>
                    </div>
                    <svg class="gmb-accordion-arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
                <div class="gmb-accordion-content" id="gmb-content-list-wrapper">
                    <div class="gmb-accordion-inner">
                        <ul class="gmb-audit-list" id="gmb-content-list"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Tab Panel -->
    <div class="gmb-tab-content" id="gmb-tab-advanced" role="tabpanel">
        <!-- Row 1: ROBOTS META -->
        <div class="gmb-setting-row">
            <div class="gmb-setting-label-col">
                <label class="gmb-setting-row-label"><?php esc_html_e('Robots Meta', 'gmb-ranker-seo-automation'); ?></label>
            </div>
            <div class="gmb-setting-content-col">
                <div class="gmb-robots-meta-grid">
                    <div class="gmb-robots-meta-col">
                        <label class="gmb-robot-option">
                            <input type="checkbox" id="gmb_seo_robot_index" name="gmb_seo_robots[]" value="index" <?php checked(!in_array('noindex', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('Index', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Instructs search engines to index and show these pages in search results.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <label class="gmb-robot-option">
                            <input type="checkbox" name="gmb_seo_robots[]" value="nofollow" <?php checked(in_array('nofollow', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('Nofollow', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Instructs search engines not to follow the links on this page.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <label class="gmb-robot-option">
                            <input type="checkbox" name="gmb_seo_robots[]" value="noimageindex" <?php checked(in_array('noimageindex', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('No Image Index', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Prevents search engines from indexing the images on this page.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                    </div>
                    <div class="gmb-robots-meta-col">
                        <label class="gmb-robot-option">
                            <input type="checkbox" id="gmb_seo_robot_noindex" name="gmb_seo_robots[]" value="noindex" <?php checked(in_array('noindex', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('No Index', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Prevents search engines from indexing and showing these pages in search results.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <label class="gmb-robot-option">
                            <input type="checkbox" name="gmb_seo_robots[]" value="noarchive" <?php checked(in_array('noarchive', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('No Archive', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Prevents search engines from showing a Cached link for this page.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <label class="gmb-robot-option">
                            <input type="checkbox" name="gmb_seo_robots[]" value="nosnippet" <?php checked(in_array('nosnippet', $robots_array, true)); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('No Snippet', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Prevents search engines from showing a snippet of this page in search results.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: ADVANCED ROBOTS META -->
        <div class="gmb-setting-row">
            <div class="gmb-setting-label-col">
                <label class="gmb-setting-row-label"><?php esc_html_e('Advanced Robots Meta', 'gmb-ranker-seo-automation'); ?></label>
            </div>
            <div class="gmb-setting-content-col">
                <div class="gmb-adv-robots-list">
                    <div class="gmb-adv-robot-item">
                        <label class="gmb-robot-option gmb-adv-robot-label">
                            <input type="checkbox" name="gmb_seo_max_snippet_enabled" value="1" class="gmb-adv-robot-toggle" <?php checked($max_snippet_enabled, '1'); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('Max Snippet', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Specify a maximum text-length, in characters, of a snippet for your page.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <div class="gmb-adv-robot-input-wrap">
                            <input type="text" name="gmb_seo_max_snippet" value="<?php echo esc_attr($max_snippet ?: '-1'); ?>" class="gmb-field-input gmb-input-compact" placeholder="-1" />
                        </div>
                    </div>
                    <div class="gmb-adv-robot-item">
                        <label class="gmb-robot-option gmb-adv-robot-label">
                            <input type="checkbox" name="gmb_seo_max_video_enabled" value="1" class="gmb-adv-robot-toggle" <?php checked($max_video_enabled, '1'); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('Max Video Preview', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Specify a maximum duration in seconds of an animated video preview.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <div class="gmb-adv-robot-input-wrap">
                            <input type="text" name="gmb_seo_max_video" value="<?php echo esc_attr($max_video ?: '-1'); ?>" class="gmb-field-input gmb-input-compact" placeholder="-1" />
                        </div>
                    </div>
                    <div class="gmb-adv-robot-item">
                        <label class="gmb-robot-option gmb-adv-robot-label">
                            <input type="checkbox" name="gmb_seo_max_image_enabled" value="1" class="gmb-adv-robot-toggle" <?php checked($max_image_enabled, '1'); ?> />
                            <span class="gmb-robot-option-name"><?php esc_html_e('Max Image Preview', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('Specify a maximum size of image preview to be shown for images on this page.', 'gmb-ranker-seo-automation'); ?>">?</span>
                        </label>
                        <div class="gmb-adv-robot-input-wrap">
                            <select name="gmb_seo_max_image" class="gmb-field-select gmb-input-compact">
                                <option value="large" <?php selected($max_image, 'large'); ?>><?php esc_html_e('Large', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="standard" <?php selected($max_image, 'standard'); ?>><?php esc_html_e('Standard', 'gmb-ranker-seo-automation'); ?></option>
                                <option value="none" <?php selected($max_image, 'none'); ?>><?php esc_html_e('None', 'gmb-ranker-seo-automation'); ?></option>
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
                    <?php esc_html_e('Canonical URL', 'gmb-ranker-seo-automation'); ?>
                    <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('The canonical URL tells search engines which URL is the master copy of a page.', 'gmb-ranker-seo-automation'); ?>">?</span>
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
                    <?php esc_html_e('Breadcrumb Title', 'gmb-ranker-seo-automation'); ?>
                    <span class="gmb-help-tip" data-gmb-tooltip="<?php esc_attr_e('The Breadcrumb Title used for this specific post/page in navigation breadcrumbs.', 'gmb-ranker-seo-automation'); ?>">?</span>
                </label>
            </div>
            <div class="gmb-setting-content-col">
                <input type="text" id="gmb_seo_breadcrumb_title" name="gmb_seo_breadcrumb_title" value="<?php echo esc_attr($breadcrumb_title); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($post->post_title); ?>" />
            </div>
        </div>

        <!-- Row 5: REDIRECT -->
        <div class="gmb-setting-row gmb-setting-row-last">
            <div class="gmb-setting-label-col">
                <label class="gmb-setting-row-label"><?php esc_html_e('Redirect', 'gmb-ranker-seo-automation'); ?></label>
            </div>
            <div class="gmb-setting-content-col">
                <label class="gmb-form-toggle">
                    <input type="checkbox" id="gmb_enable_redirect_toggle" name="gmb_seo_redirect_enabled" value="1" class="gmb-toggle-input" <?php checked(!empty($redirect_url)); ?> />
                    <span class="gmb-form-toggle__track">
                        <span class="gmb-form-toggle__thumb"></span>
                    </span>
                </label>
                <div id="gmb-redirect-details-box" class="gmb-redirect-box <?php echo !empty($redirect_url) ? '' : 'is-hidden'; ?>">
                    <div class="gmb-redirect-field gmb-flex-2">
                        <label for="gmb_seo_redirect_url" class="gmb-field-label gmb-text-xs"><?php esc_html_e('Destination URL', 'gmb-ranker-seo-automation'); ?></label>
                        <input type="url" id="gmb_seo_redirect_url" name="gmb_seo_redirect_url" value="<?php echo esc_attr($redirect_url); ?>" class="gmb-field-input" placeholder="https://example.com/target-page/" />
                    </div>
                    <div class="gmb-redirect-field gmb-flex-1">
                        <label for="gmb_seo_redirect_code" class="gmb-field-label gmb-text-xs"><?php esc_html_e('Redirection Type', 'gmb-ranker-seo-automation'); ?></label>
                        <select id="gmb_seo_redirect_code" name="gmb_seo_redirect_code" class="gmb-field-select gmb-input-h38">
                            <option value="301" <?php selected($redirect_code, '301'); ?>><?php esc_html_e('301 Permanent Move', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="302" <?php selected($redirect_code, '302'); ?>><?php esc_html_e('302 Temporary Move', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="307" <?php selected($redirect_code, '307'); ?>><?php esc_html_e('307 Temporary Redirect', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="410" <?php selected($redirect_code, '410'); ?>><?php esc_html_e('410 Content Deleted', 'gmb-ranker-seo-automation'); ?></option>
                            <option value="451" <?php selected($redirect_code, '451'); ?>><?php esc_html_e('451 Unavailable For Legal Reasons', 'gmb-ranker-seo-automation'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schema Tab Panel -->
    <div class="gmb-tab-content" id="gmb-tab-schema" role="tabpanel">
        <div class="gmb-schema-header-toolbar">
            <div class="gmb-schema-section-intro">
                <h4 class="gmb-section-heading"><?php esc_html_e('Configure Schema Markup', 'gmb-ranker-seo-automation'); ?></h4>
                <p class="gmb-text-muted-desc"><?php esc_html_e('Configure Structured Data Schema Markup for your pages to display rich search engine snippets.', 'gmb-ranker-seo-automation'); ?></p>
            </div>
            <button type="button" class="button button-primary gmb-schema-generator-btn" id="gmb-schema-generator-open-btn">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                <span><?php esc_html_e('Schema Generator', 'gmb-ranker-seo-automation'); ?></span>
            </button>
        </div>
        
        <div class="gmb-schema-in-use-wrapper">
            <h3 class="gmb-heading-4"><?php esc_html_e('Schema in Use', 'gmb-ranker-seo-automation'); ?></h3>
            
            <?php
            $active_schemas = get_post_meta($post->ID, '_gmb_ranker_active_schemas', true);
            if (empty($active_schemas)) {
                $stored_type = get_post_meta($post->ID, '_gmb_ranker_schema_type', true);
                $active_schemas = !empty($stored_type) ? array($stored_type) : array();
            }
            if (is_string($active_schemas)) {
                $active_schemas = array_filter(array_map('trim', explode(',', $active_schemas)));
            }
            ?>
            <input type="hidden" id="gmb_seo_active_schemas" name="gmb_seo_active_schemas" value="<?php echo esc_attr(implode(',', $active_schemas)); ?>" />

            <div id="gmb-schema-in-use-list" class="gmb-schema-active-list">
                <?php if (empty($active_schemas)) : ?>
                    <p class="gmb-text-muted-desc" id="gmb-no-active-schema-notice"><?php esc_html_e('No active schema types assigned to this post. Click "Schema Generator" to select a structured data template.', 'gmb-ranker-seo-automation'); ?></p>
                <?php else : ?>
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
                                <button type="button" class="gmb-schema-action-btn gmb-schema-edit-btn" data-type="<?php echo esc_attr($s_type); ?>" title="<?php esc_attr_e('Edit Schema', 'gmb-ranker-seo-automation'); ?>">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <button type="button" class="gmb-schema-action-btn gmb-schema-code-btn" data-type="<?php echo esc_attr($s_type); ?>" title="<?php esc_attr_e('Code Validation', 'gmb-ranker-seo-automation'); ?>">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                <button type="button" class="gmb-schema-action-btn gmb-remove-schema-btn" data-type="<?php echo esc_attr($s_type); ?>" title="<?php esc_attr_e('Delete Schema', 'gmb-ranker-seo-automation'); ?>">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Schema Generator Modal -->
        <div class="gmb-modal-backdrop" id="gmb-schema-modal" aria-hidden="true" role="dialog">
            <div class="gmb-modal-box gmb-modal-box-schema">
                <div class="gmb-modal-header">
                    <span class="gmb-modal-title"><?php esc_html_e('Schema Generator', 'gmb-ranker-seo-automation'); ?></span>
                    <button type="button" class="gmb-modal-close" id="gmb-schema-modal-close-btn" title="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>" aria-label="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>" onclick="return window.gmbCloseSchemaModal ? window.gmbCloseSchemaModal(event) : false;">&#x2715;</button>
                </div>
                <div class="gmb-modal-tabs" role="tablist">
                    <button type="button" class="gmb-modal-tab-btn active" data-schema-tab="schema-tab-templates" role="tab" aria-selected="true" aria-controls="schema-tab-templates" onclick="return window.gmbSwitchSchemaTab ? window.gmbSwitchSchemaTab('schema-tab-templates', event) : false;"><?php esc_html_e('Schema Templates', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" class="gmb-modal-tab-btn" data-schema-tab="schema-tab-import" role="tab" aria-selected="false" aria-controls="schema-tab-import" onclick="return window.gmbSwitchSchemaTab ? window.gmbSwitchSchemaTab('schema-tab-import', event) : false;"><?php esc_html_e('Import', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" class="gmb-modal-tab-btn" data-schema-tab="schema-tab-custom" role="tab" aria-selected="false" aria-controls="schema-tab-custom" onclick="return window.gmbSwitchSchemaTab ? window.gmbSwitchSchemaTab('schema-tab-custom', event) : false;"><?php esc_html_e('Custom Schema', 'gmb-ranker-seo-automation'); ?></button>
                </div>
                <div class="gmb-modal-body gmb-modal-body-scroll">
                    
                    <!-- Schema Templates Tab content -->
                    <div class="gmb-schema-tab-content active" id="schema-tab-templates" role="tabpanel">
                        <h4 class="gmb-schema-grid-title"><?php esc_html_e('Available Schema Templates', 'gmb-ranker-seo-automation'); ?></h4>
                        
                        <div class="gmb-schema-template-grid" id="gmb-schema-templates-grid">
                            
                            <div class="gmb-schema-template-card" data-type="Article" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Article', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Article</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Article" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Article', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Book" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Book', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Book</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Book" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Book', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Course" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Course', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Course</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Course" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Course', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Dataset" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Dataset', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Dataset</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Dataset" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Dataset', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Event" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Event', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Event</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Event" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Event', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="FAQ Page" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('FAQ Page', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">FAQ Page</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="FAQ Page" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('FAQ Page', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="FactCheck" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('FactCheck', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">FactCheck</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="FactCheck" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('FactCheck', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="HowTo" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('HowTo', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">HowTo</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="HowTo" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('HowTo', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Job Posting" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Job Posting', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Job Posting</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Job Posting" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Job Posting', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Movie" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Movie', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Movie</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Movie" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Movie', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Music" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Music', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Music</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Music" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Music', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Person" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Person', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Person</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Person" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Person', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Product" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Product', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Product</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Product" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Product', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Recipe" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Recipe', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Recipe</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Recipe" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Recipe', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Restaurant" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Restaurant', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v4M21 2v20M21 2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4"></path></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Restaurant</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Restaurant" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Restaurant', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Service" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Service', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Service</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Service" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Service', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Software" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Software', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Software</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Software" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Software', event);">+ Use</button>
                            </div>

                            <div class="gmb-schema-template-card" data-type="Video" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Video', event);">
                                <div class="gmb-schema-template-info">
                                    <div class="gmb-schema-template-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                                    </div>
                                    <span class="gmb-schema-template-name">Video</span>
                                </div>
                                <button type="button" class="button gmb-use-schema-btn" data-type="Video" onclick="if(window.gmbUseSchemaTemplate) window.gmbUseSchemaTemplate('Video', event);">+ Use</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Import Tab content -->
                    <div class="gmb-schema-tab-content" id="schema-tab-import" role="tabpanel">
                        <h4 class="gmb-schema-grid-title"><?php esc_html_e('Import Schema Markup', 'gmb-ranker-seo-automation'); ?></h4>
                        <div class="gmb-field-group">
                            <label for="gmb_seo_schema_import_input" class="gmb-field-label"><?php esc_html_e('Paste JSON-LD or HTML containing JSON-LD', 'gmb-ranker-seo-automation'); ?></label>
                            <textarea id="gmb_seo_schema_import_input" rows="9" class="gmb-field-textarea code-font" placeholder="<?php esc_attr_e('Paste schema block here...', 'gmb-ranker-seo-automation'); ?>"></textarea>
                        </div>
                        <button type="button" class="button button-primary gmb-btn-primary-bold" id="gmb-schema-import-submit-btn"><?php esc_html_e('Import Schema', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                    
                    <!-- Custom Schema Tab content -->
                    <div class="gmb-schema-tab-content" id="schema-tab-custom" role="tabpanel">
                        <h4 class="gmb-schema-grid-title"><?php esc_html_e('Custom Schema JSON-LD Markup', 'gmb-ranker-seo-automation'); ?></h4>
                        <div class="gmb-field-group">
                            <textarea id="gmb_seo_schema_input" name="gmb_seo_schema" rows="9" class="gmb-field-textarea code-font" placeholder='{"@context": "https://schema.org", "@type": "Article", ...}'><?php echo esc_textarea($json_ld); ?></textarea>
                            <p class="gmb-field-help"><?php esc_html_e('Modify schema values directly. Be sure to insert valid JSON format.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>
                    </div>
                    
                </div>
                <div class="gmb-modal-footer">
                    <button type="button" class="button button-primary gmb-btn-action-primary" id="gmb-schema-modal-save-btn"><?php esc_html_e('Save Schema', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>

        <!-- Schema Builder Modal -->
        <div class="gmb-modal-backdrop" id="gmb-schema-builder-modal" aria-hidden="true" role="dialog">
            <div class="gmb-modal-box gmb-modal-box-builder">
                <div class="gmb-modal-header">
                    <div class="gmb-flex-center-gap-sm">
                        <span class="gmb-modal-title"><?php esc_html_e('Schema Builder', 'gmb-ranker-seo-automation'); ?></span>
                    </div>
                    <span class="gmb-modal-close" id="gmb-schema-builder-close-btn" title="<?php esc_attr_e('Close Modal', 'gmb-ranker-seo-automation'); ?>" role="button" tabindex="0">&#x2715;</span>
                </div>
                <div class="gmb-builder-subtabs-bar">
                    <div class="gmb-builder-tabs-nav" role="tablist">
                        <button type="button" class="gmb-modal-tab-btn active" id="gmb-builder-tab-btn-edit" data-builder-tab="edit" role="tab"><?php esc_html_e('Edit', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="gmb-modal-tab-btn" id="gmb-builder-tab-btn-validation" data-builder-tab="validation" role="tab"><?php esc_html_e('Code Validation', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                    <span class="gmb-builder-info-icon" title="<?php esc_attr_e('Customize Schema properties for this post', 'gmb-ranker-seo-automation'); ?>"></span>
                </div>
                <div class="gmb-modal-body gmb-modal-body-scroll gmb-builder-modal-body">
                    
                    <!-- Edit Panel -->
                    <div class="gmb-builder-panel active" id="gmb-builder-panel-edit" role="tabpanel">
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
                                        <button type="button" class="gmb-group-action-link" id="gmb-builder-add-prop-btn" title="<?php esc_attr_e('Add Property', 'gmb-ranker-seo-automation'); ?>">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                            <span><?php esc_html_e('Add Property', 'gmb-ranker-seo-automation'); ?></span>
                                        </button>
                                        <button type="button" class="gmb-group-action-link" id="gmb-builder-add-group-btn" title="<?php esc_attr_e('Add Property Group', 'gmb-ranker-seo-automation'); ?>">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                            <span><?php esc_html_e('Add Property Group', 'gmb-ranker-seo-automation'); ?></span>
                                        </button>
                                        <button type="button" class="gmb-group-action-link gmb-group-action-delete" id="gmb-builder-reset-tree-btn" title="<?php esc_attr_e('Reset to Defaults', 'gmb-ranker-seo-automation'); ?>">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            <span><?php esc_html_e('Delete', 'gmb-ranker-seo-automation'); ?></span>
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
                    <div class="gmb-builder-panel" id="gmb-builder-panel-validation" role="tabpanel">
                        <div class="gmb-code-preview-header">
                            <span class="gmb-code-preview-title"><?php esc_html_e('JSON-LD Structured Data Preview', 'gmb-ranker-seo-automation'); ?></span>
                            <div class="gmb-code-actions">
                                <button type="button" class="button button-small" id="gmb-builder-copy-code-btn"><?php esc_html_e('Copy Code', 'gmb-ranker-seo-automation'); ?></button>
                                <a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener noreferrer" class="button button-small gmb-btn-test-google"><?php esc_html_e('Test with Google ↗', 'gmb-ranker-seo-automation'); ?></a>
                            </div>
                        </div>
                        <pre class="gmb-json-code-box"><code id="gmb-builder-validation-code" class="language-json"></code></pre>
                    </div>

                </div>
                <div class="gmb-modal-footer gmb-builder-footer">
                    <div class="gmb-builder-footer-left">
                        <button type="button" class="button" id="gmb-builder-toggle-mode-btn"><?php esc_html_e('Advanced Editor', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="button" id="gmb-builder-save-template-btn"><?php esc_html_e('Save as Template', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                    <div class="gmb-builder-footer-right">
                        <button type="button" class="button button-primary gmb-btn-primary-bold" id="gmb-builder-save-post-btn"><?php esc_html_e('Save for this Post', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Social Tab Panel -->
    <div class="gmb-tab-content" id="gmb-tab-social" role="tabpanel">
        <div class="gmb-social-header-toolbar">
            <div class="gmb-social-platform-nav" role="tablist">
                <button type="button" class="gmb-social-tab-btn active" data-social-tab="gmb-social-fb" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="#1877f2" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <span><?php esc_html_e('Facebook', 'gmb-ranker-seo-automation'); ?></span>
                </button>
                <button type="button" class="gmb-social-tab-btn" data-social-tab="gmb-social-tw" role="tab">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    <span><?php esc_html_e('Twitter / X', 'gmb-ranker-seo-automation'); ?></span>
                </button>
            </div>
            <button type="button" class="button gmb-sync-social-btn" id="gmb-sync-social-btn" title="<?php esc_attr_e('Copy title and description from General SEO settings', 'gmb-ranker-seo-automation'); ?>">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span><?php esc_html_e('Sync from General SEO', 'gmb-ranker-seo-automation'); ?></span>
            </button>
        </div>

        <!-- Facebook Subtab Pane -->
        <div class="gmb-social-pane active" id="gmb-social-fb" role="tabpanel">
            <div class="gmb-social-preview-wrapper">
                <div class="gmb-social-platform-label"><?php esc_html_e('Facebook Feed Live Preview', 'gmb-ranker-seo-automation'); ?></div>
                <div class="gmb-fb-preview-card">
                    <?php 
                    $fb_img_val     = get_post_meta($post->ID, '_gmb_ranker_facebook_image', true);
                    $feat_img_val   = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '';
                    $current_fb_img = $fb_img_val ?: $feat_img_val;
                    $fb_title_val   = get_post_meta($post->ID, '_gmb_ranker_facebook_title', true) ?: ($meta_title ?: $post->post_title);
                    $fb_desc_val    = get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true) ?: ($meta_desc ?: esc_html__('Learn about our trusted page and read full information.', 'gmb-ranker-seo-automation'));
                    ?>
                    <div id="gmb-fb-preview-img-box" class="gmb-social-image-box gmb-media-upload-trigger" data-target="gmb_seo_fb_image_metabox" title="<?php esc_attr_e('Click to upload or change Facebook image', 'gmb-ranker-seo-automation'); ?>">
                        <img id="gmb-fb-preview-img" src="<?php echo esc_url($current_fb_img); ?>" class="gmb-social-img-element <?php echo !empty($current_fb_img) ? 'is-active' : ''; ?>" alt="<?php esc_attr_e('Facebook Preview', 'gmb-ranker-seo-automation'); ?>" />
                        <div id="gmb-fb-preview-placeholder" class="gmb-social-image-placeholder <?php echo !empty($current_fb_img) ? 'is-hidden' : ''; ?>">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <span class="gmb-social-placeholder-hint"><?php esc_html_e('1200 × 630 Recommended', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-social-placeholder-subhint"><?php esc_html_e('Click to add image', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <div class="gmb-social-image-overlay">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            <span><?php esc_html_e('Change Image', 'gmb-ranker-seo-automation'); ?></span>
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
                    <label for="gmb_seo_fb_title_metabox" class="gmb-field-label"><?php esc_html_e('Facebook Title', 'gmb-ranker-seo-automation'); ?></label>
                    <span id="gmb-fb-title-counter" class="gmb-char-counter">0 / 60</span>
                </div>
                <input type="text" id="gmb_seo_fb_title_metabox" name="gmb_seo_facebook_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_title', true)); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($meta_title ?: $post->post_title); ?>" />
            </div>

            <div class="gmb-field-group gmb-mb-14">
                <div class="gmb-field-header-flex">
                    <label for="gmb_seo_fb_desc_metabox" class="gmb-field-label"><?php esc_html_e('Facebook Description', 'gmb-ranker-seo-automation'); ?></label>
                    <span id="gmb-fb-desc-counter" class="gmb-char-counter">0 / 160</span>
                </div>
                <textarea id="gmb_seo_fb_desc_metabox" name="gmb_seo_facebook_desc" rows="3" class="gmb-field-textarea" placeholder="<?php echo esc_attr($meta_desc ?: esc_html__('Summarize your page for Facebook...', 'gmb-ranker-seo-automation')); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_facebook_desc', true)); ?></textarea>
            </div>

            <div class="gmb-field-group gmb-mb-14">
                <label for="gmb_seo_fb_image_metabox" class="gmb-field-label"><?php esc_html_e('Facebook Image (1200 × 630 px)', 'gmb-ranker-seo-automation'); ?></label>
                <div class="gmb-image-upload-row">
                    <input type="text" id="gmb_seo_fb_image_metabox" name="gmb_seo_facebook_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_facebook_image', true)); ?>" class="gmb-field-input" placeholder="<?php echo !empty($feat_img_val) ? esc_url($feat_img_val) : ''; ?>" />
                    <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_fb_image_metabox"><?php esc_html_e('Select Image', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" class="button button-link-delete gmb-social-clear-img-btn <?php echo !empty($fb_img_val) ? 'is-active' : ''; ?>" data-target="gmb_seo_fb_image_metabox"><?php esc_html_e('Remove', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>

        <!-- Twitter / X Subtab Pane -->
        <div class="gmb-social-pane" id="gmb-social-tw" role="tabpanel">
            <div class="gmb-social-preview-wrapper">
                <div class="gmb-social-platform-label"><?php esc_html_e('X / Twitter Card Live Preview', 'gmb-ranker-seo-automation'); ?></div>
                <?php 
                $tw_img_val     = get_post_meta($post->ID, '_gmb_ranker_twitter_image', true);
                $current_tw_img = $tw_img_val ?: ($fb_img_val ?: $feat_img_val);
                $tw_title_val   = get_post_meta($post->ID, '_gmb_ranker_twitter_title', true) ?: ($fb_title_val ?: ($meta_title ?: $post->post_title));
                $tw_desc_val    = get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true) ?: ($fb_desc_val ?: ($meta_desc ?: esc_html__('Twitter summary description preview...', 'gmb-ranker-seo-automation')));
                $tw_card_type   = get_post_meta($post->ID, '_gmb_ranker_twitter_card_type', true) ?: 'summary_large_image';
                ?>
                <div class="gmb-tw-preview-card <?php echo ($tw_card_type === 'summary') ? 'gmb-tw-card--summary' : 'gmb-tw-card--large'; ?>" id="gmb-tw-card-container">
                    <div id="gmb-tw-preview-img-box" class="gmb-social-image-box gmb-media-upload-trigger" data-target="gmb_seo_tw_image_metabox" title="<?php esc_attr_e('Click to upload or change Twitter image', 'gmb-ranker-seo-automation'); ?>">
                        <img id="gmb-tw-preview-img" src="<?php echo esc_url($current_tw_img); ?>" class="gmb-social-img-element <?php echo !empty($current_tw_img) ? 'is-active' : ''; ?>" alt="<?php esc_attr_e('Twitter Preview', 'gmb-ranker-seo-automation'); ?>" />
                        <div id="gmb-tw-preview-placeholder" class="gmb-social-image-placeholder <?php echo !empty($current_tw_img) ? 'is-hidden' : ''; ?>">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <span class="gmb-social-placeholder-hint"><?php esc_html_e('1200 × 600 Recommended', 'gmb-ranker-seo-automation'); ?></span>
                            <span class="gmb-social-placeholder-subhint"><?php esc_html_e('Click to add image', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <div class="gmb-social-image-overlay">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            <span><?php esc_html_e('Change Image', 'gmb-ranker-seo-automation'); ?></span>
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
                <label for="gmb_seo_tw_card_type" class="gmb-field-label"><?php esc_html_e('Twitter Card Type', 'gmb-ranker-seo-automation'); ?></label>
                <select id="gmb_seo_tw_card_type" name="gmb_seo_twitter_card_type" class="gmb-field-select gmb-input-h38">
                    <option value="summary_large_image" <?php selected($tw_card_type, 'summary_large_image'); ?>><?php esc_html_e('Summary Card with Large Image (Recommended)', 'gmb-ranker-seo-automation'); ?></option>
                    <option value="summary" <?php selected($tw_card_type, 'summary'); ?>><?php esc_html_e('Summary Card (Compact Thumbnail)', 'gmb-ranker-seo-automation'); ?></option>
                    <option value="app" <?php selected($tw_card_type, 'app'); ?>><?php esc_html_e('App Card', 'gmb-ranker-seo-automation'); ?></option>
                    <option value="player" <?php selected($tw_card_type, 'player'); ?>><?php esc_html_e('Player Card (Video / Audio)', 'gmb-ranker-seo-automation'); ?></option>
                </select>
            </div>

            <div class="gmb-field-group gmb-mb-14">
                <div class="gmb-field-header-flex">
                    <label for="gmb_seo_tw_title_metabox" class="gmb-field-label"><?php esc_html_e('Twitter Title', 'gmb-ranker-seo-automation'); ?></label>
                    <span id="gmb-tw-title-counter" class="gmb-char-counter">0 / 60</span>
                </div>
                <input type="text" id="gmb_seo_tw_title_metabox" name="gmb_seo_twitter_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_title', true)); ?>" class="gmb-field-input" placeholder="<?php echo esc_attr($fb_title_val ?: ($meta_title ?: $post->post_title)); ?>" />
            </div>

            <div class="gmb-field-group gmb-mb-14">
                <div class="gmb-field-header-flex">
                    <label for="gmb_seo_tw_desc_metabox" class="gmb-field-label"><?php esc_html_e('Twitter Description', 'gmb-ranker-seo-automation'); ?></label>
                    <span id="gmb-tw-desc-counter" class="gmb-char-counter">0 / 160</span>
                </div>
                <textarea id="gmb_seo_tw_desc_metabox" name="gmb_seo_twitter_desc" rows="3" class="gmb-field-textarea" placeholder="<?php echo esc_attr($fb_desc_val ?: ($meta_desc ?: esc_html__('Summarize for Twitter...', 'gmb-ranker-seo-automation'))); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_gmb_ranker_twitter_desc', true)); ?></textarea>
            </div>

            <div class="gmb-field-group gmb-mb-14">
                <label for="gmb_seo_tw_image_metabox" class="gmb-field-label"><?php esc_html_e('Twitter Image', 'gmb-ranker-seo-automation'); ?></label>
                <div class="gmb-image-upload-row">
                    <input type="text" id="gmb_seo_tw_image_metabox" name="gmb_seo_twitter_image" value="<?php echo esc_attr(get_post_meta($post->ID, '_gmb_ranker_twitter_image', true)); ?>" class="gmb-field-input" placeholder="<?php echo !empty($current_tw_img) ? esc_url($current_tw_img) : ''; ?>" />
                    <button type="button" class="button button-secondary gmb-media-upload-btn" data-target="gmb_seo_tw_image_metabox"><?php esc_html_e('Select Image', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" class="button button-link-delete gmb-social-clear-img-btn <?php echo !empty($tw_img_val) ? 'is-active' : ''; ?>" data-target="gmb_seo_tw_image_metabox"><?php esc_html_e('Remove', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
