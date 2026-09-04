<?php
/**
 * Standalone Full-Canvas Setup Wizard View for GMB Ranker SEO
 *
 * Refactored into a thin presentation view layer consuming
 * GMB_Ranker_SEO_Wizard_Registry::get_view_model().
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access the setup wizard.', 'gmb-ranker-seo-automation'));
}

$view_model   = GMB_Ranker_SEO_Wizard_Registry::get_view_model();
$base_url     = $view_model['base_url'];
$ver          = $view_model['version'];
$steps        = $view_model['steps'];
$setup_modes  = $view_model['setup_modes'];
$site_types   = $view_model['site_types'];
$ai_providers = $view_model['ai_providers'];
$d            = $view_model['diagnostics'];
$eligible_pts = $view_model['eligible_pts'];
$s            = $view_model['settings'];
$wizard_nonce = $view_model['wizard_nonce'];

// Trigger admin_enqueue_scripts so all asset hooks run before printing styles
do_action('admin_enqueue_scripts', 'gmb-ranker-wizard');

if (function_exists('wp_enqueue_media')) {
    wp_enqueue_media();
}
if (function_exists('wp_enqueue_script')) {
    wp_enqueue_script('jquery');
}
?>
<!DOCTYPE html>
<html <?php if (function_exists('language_attributes')) language_attributes(); ?>>
<head>
    <meta charset="<?php echo function_exists('bloginfo') ? esc_attr(get_bloginfo('charset')) : 'UTF-8'; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('GMB Ranker SEO — Setup Wizard', 'gmb-ranker-seo-automation'); ?></title>
    
    <!-- Stylesheet assets enqueued via WordPress hooks -->

    <?php
    if (function_exists('wp_print_styles')) {
        wp_print_styles();
    }
    if (function_exists('wp_print_head_scripts')) {
        wp_print_head_scripts();
    }
    ?>
</head>
<body class="gmb-wizard-body">

<div class="wiz-container" role="main" aria-label="<?php esc_attr_e('Setup Wizard Container', 'gmb-ranker-seo-automation'); ?>">
    
    <!-- Branding Header -->
    <div class="wiz-logo-wrap">
        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>">
            <img src="<?php echo esc_url($base_url . 'gmbranker.svg?v=' . $ver); ?>" alt="<?php esc_attr_e('GMB Ranker', 'gmb-ranker-seo-automation'); ?>" class="wiz-logo-img" />
        </a>
        <div class="wiz-logo-tagline"><?php esc_html_e('Smart SEO & Instant Indexing Autopilot', 'gmb-ranker-seo-automation'); ?></div>
    </div>

    <!-- Stepper Navigation -->
    <div class="wiz-stepper-wrap" role="navigation" aria-label="<?php esc_attr_e('Wizard Stepper Progress', 'gmb-ranker-seo-automation'); ?>">
        <div class="wiz-stepper-track">
            <div class="wiz-stepper-progress" id="wiz-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="16"></div>
        </div>
        <div class="wiz-stepper">
            <?php foreach ($steps as $step_num => $step_data) : ?>
                <div class="wiz-step-item <?php echo ($step_num === 1) ? 'active' : ''; ?>" data-step="<?php echo esc_attr($step_num); ?>" id="wiz-step-indicator-<?php echo esc_attr($step_num); ?>">
                    <div class="wiz-step-circle"><?php echo esc_html($step_num); ?></div>
                    <div class="wiz-step-label"><?php echo esc_html(!empty($step_data['nav_label']) ? $step_data['nav_label'] : $step_data['title']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Wizard Card Container -->
    <div class="wiz-card">
        
        <!-- STEP 1: GETTING STARTED (SELECT SETUP MODE) -->
        <div class="wiz-step-pane active" id="wiz-pane-1" role="region" aria-label="<?php esc_attr_e('Step 1: Getting Started', 'gmb-ranker-seo-automation'); ?>">
            <h2 class="wiz-title"><?php echo esc_html($steps[1]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[1]['subtitle']); ?></p>

            <form id="wiz-form-mode">
                <?php foreach ($setup_modes as $mode_key => $mode_info) : 
                    $is_active = ($s['mode'] === $mode_key);
                ?>
                    <label class="wiz-option-box <?php echo $is_active ? 'active' : ''; ?>" data-mode="<?php echo esc_attr($mode_key); ?>">
                        <input type="radio" name="wiz_setup_mode" value="<?php echo esc_attr($mode_key); ?>" <?php checked($s['mode'], $mode_key); ?> />
                        <div class="wiz-option-content">
                            <div class="wiz-option-header">
                                <strong class="wiz-option-title"><?php echo esc_html($mode_info['label']); ?></strong>
                                <?php if (!empty($mode_info['badge'])) : ?>
                                    <span class="<?php echo esc_attr($mode_info['badge_class']); ?>"><?php echo esc_html($mode_info['badge']); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="wiz-option-desc"><?php echo esc_html($mode_info['description']); ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>

                <!-- Note box -->
                <div class="wiz-note-box">
                    <span class="wiz-note-tag"><?php esc_html_e('NOTE', 'gmb-ranker-seo-automation'); ?></span>
                    <span><?php esc_html_e('You can easily switch between modes at any point from General Settings.', 'gmb-ranker-seo-automation'); ?></span>
                </div>

                <!-- Server Compatibility Card -->
                <div class="wiz-compat-card">
                    <div class="wiz-compat-header">
                        <div class="wiz-compat-header-left">
                            <div class="wiz-compat-icon-badge <?php echo $d['all_pass'] ? '' : 'is-warn'; ?>">
                                <?php if ($d['all_pass']) : ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <?php else : ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="wiz-compat-info">
                                <span class="wiz-compat-title"><?php echo $d['all_pass'] ? esc_html__('Your server is compatible to run GMB Ranker SEO', 'gmb-ranker-seo-automation') : esc_html__('Compatibility Notice', 'gmb-ranker-seo-automation'); ?></span>
                                <span class="wiz-compat-subtitle"><?php esc_html_e('Core PHP extensions, OpenSSL protocols, and REST hooks active', 'gmb-ranker-seo-automation'); ?></span>
                            </div>
                        </div>
                        <button type="button" class="wiz-compat-toggle-btn" id="wiz-toggle-compat-btn" aria-expanded="false" aria-controls="wiz-compat-details">
                            <?php esc_html_e('View Details', 'gmb-ranker-seo-automation'); ?>
                            <svg class="toggle-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <div class="wiz-compat-details" id="wiz-compat-details">
                        <div class="wiz-compat-grid">
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name"><?php esc_html_e('PHP Version', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-req"><?php esc_html_e('Requires PHP 7.4 or higher', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo esc_html($d['php_version']); ?></span>
                                    <span class="wiz-compat-status <?php echo $d['php_pass'] ? 'is-pass' : ''; ?>"><?php echo $d['php_pass'] ? esc_html__('Pass', 'gmb-ranker-seo-automation') : esc_html__('Fail', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name"><?php esc_html_e('cURL Extension', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-req"><?php esc_html_e('Required for Google Indexing & IndexNow API', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $d['curl_pass'] ? esc_html__('Enabled', 'gmb-ranker-seo-automation') : esc_html__('Disabled', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-status <?php echo $d['curl_pass'] ? 'is-pass' : ''; ?>"><?php echo $d['curl_pass'] ? esc_html__('Pass', 'gmb-ranker-seo-automation') : esc_html__('Fail', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name"><?php esc_html_e('OpenSSL & JWT', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-req"><?php esc_html_e('Required for Google Service Account RSA keys', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $d['ssl_pass'] ? esc_html__('Supported', 'gmb-ranker-seo-automation') : esc_html__('Missing', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-status <?php echo $d['ssl_pass'] ? 'is-pass' : ''; ?>"><?php echo $d['ssl_pass'] ? esc_html__('Pass', 'gmb-ranker-seo-automation') : esc_html__('Fail', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name"><?php esc_html_e('DOMDocument & XML', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-req"><?php esc_html_e('Required for Table of Contents & Sitemap generator', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $d['dom_pass'] ? esc_html__('Active', 'gmb-ranker-seo-automation') : esc_html__('Missing', 'gmb-ranker-seo-automation'); ?></span>
                                    <span class="wiz-compat-status <?php echo $d['dom_pass'] ? 'is-pass' : ''; ?>"><?php echo $d['dom_pass'] ? esc_html__('Pass', 'gmb-ranker-seo-automation') : esc_html__('Fail', 'gmb-ranker-seo-automation'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wiz-card-footer wiz-card-footer-end">
                    <button type="button" class="wiz-btn-primary" id="wiz-btn-next-1">
                        <?php esc_html_e('Start Wizard ›', 'gmb-ranker-seo-automation'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 2: YOUR SITE PROFILE -->
        <div class="wiz-step-pane" id="wiz-pane-2" role="region" aria-label="<?php esc_attr_e('Step 2: Your Site Profile', 'gmb-ranker-seo-automation'); ?>">
            <h2 class="wiz-title"><?php echo esc_html($steps[2]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[2]['subtitle']); ?></p>

            <form id="wiz-form-site">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_site_type"><?php esc_html_e('Site Type', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <select id="wiz_site_type" class="wiz-input">
                            <?php foreach ($site_types as $val => $lbl) : ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($s['site_type'], $val); ?>><?php echo esc_html($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="wiz-form-desc"><?php esc_html_e('Select the primary business categorization for Schema structured data.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_org_name"><?php esc_html_e('Business or Website Name', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="text" id="wiz_org_name" class="wiz-input" value="<?php echo esc_attr($s['org_name']); ?>" placeholder="<?php esc_attr_e('e.g. Acme Corporation', 'gmb-ranker-seo-automation'); ?>" />
                        <div class="wiz-form-desc"><?php esc_html_e('The official brand name search engines will associate with your website.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_site_logo"><?php esc_html_e('Website Logo', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-upload-group">
                            <input type="text" id="wiz_site_logo" class="wiz-input" value="<?php echo esc_attr($s['site_logo']); ?>" placeholder="https://..." />
                            <button type="button" class="wiz-btn-secondary wiz-media-btn" data-target="#wiz_site_logo" data-preview="#wiz_logo_preview"><?php esc_html_e('Upload Image', 'gmb-ranker-seo-automation'); ?></button>
                        </div>
                        <div id="wiz_logo_preview_wrap" class="wiz-preview-box <?php echo empty($s['site_logo']) ? 'gmb-hidden' : ''; ?>">
                            <img id="wiz_logo_preview" src="<?php echo esc_url($s['site_logo']); ?>" class="wiz-preview-thumb" alt="<?php esc_attr_e('Logo preview', 'gmb-ranker-seo-automation'); ?>" />
                            <span class="wiz-preview-text"><?php esc_html_e('Logo preview for Google Schema Knowledge Graph.', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <div class="wiz-form-desc"><?php esc_html_e('Recommended minimum dimensions: 112x112px in JPG, PNG, SVG or WebP.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_social_image"><?php esc_html_e('Default Social Share Image', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-upload-group">
                            <input type="text" id="wiz_social_image" class="wiz-input" value="<?php echo esc_attr($s['social_image']); ?>" placeholder="https://..." />
                            <button type="button" class="wiz-btn-secondary wiz-media-btn" data-target="#wiz_social_image" data-preview="#wiz_social_preview"><?php esc_html_e('Upload Image', 'gmb-ranker-seo-automation'); ?></button>
                        </div>
                        <div id="wiz_social_preview_wrap" class="wiz-preview-box <?php echo empty($s['social_image']) ? 'gmb-hidden' : ''; ?>">
                            <img id="wiz_social_preview" src="<?php echo esc_url($s['social_image']); ?>" class="wiz-preview-thumb" alt="<?php esc_attr_e('Social preview', 'gmb-ranker-seo-automation'); ?>" />
                            <span class="wiz-preview-text"><?php esc_html_e('Fallback image used when posts don\'t have a featured image.', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                        <div class="wiz-form-desc"><?php esc_html_e('Recommended dimensions: 1200x630px for Facebook, Twitter/X and LinkedIn previews.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="1"><?php esc_html_e('‹ Back', 'gmb-ranker-seo-automation'); ?></button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="3"><?php esc_html_e('Skip Step', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-2"><?php esc_html_e('Save & Continue ›', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 3: API & AUTOMATION CONFIG -->
        <div class="wiz-step-pane" id="wiz-pane-3" role="region" aria-label="<?php esc_attr_e('Step 3: API & Automation Integrations', 'gmb-ranker-seo-automation'); ?>">
            <h2 class="wiz-title"><?php echo esc_html($steps[3]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[3]['subtitle']); ?></p>

            <form id="wiz-form-api">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_api_key"><?php esc_html_e('GMB Ranker API Secret Key', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="password" id="wiz_api_key" class="wiz-input" value="<?php echo esc_attr($s['api_key']); ?>" placeholder="gr_sec_..." autocomplete="off" />
                        <div class="wiz-form-desc"><?php esc_html_e('Your unique secret key for headless API requests and remote indexing hooks.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_ai_provider"><?php esc_html_e('AI Keyword Engine', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <select id="wiz_ai_provider" class="wiz-input">
                            <?php foreach ($ai_providers as $val => $lbl) : ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($s['ai_provider'], $val); ?>><?php echo esc_html($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="wiz-form-desc"><?php esc_html_e('Used for 1-click SEO title recommendations, meta descriptions, and focus keyword extraction.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row" id="wiz_ai_key_row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_ai_key"><?php esc_html_e('AI Provider API Key', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="password" id="wiz_ai_key" class="wiz-input" value="<?php echo esc_attr($s['ai_key']); ?>" placeholder="sk-or-... / gsk_..." autocomplete="off" />
                        <div class="wiz-form-desc"><?php esc_html_e('Enter your provider key. OpenRouter and Groq offer generous free keys.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="2"><?php esc_html_e('‹ Back', 'gmb-ranker-seo-automation'); ?></button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="4"><?php esc_html_e('Skip Step', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-3"><?php esc_html_e('Save & Continue ›', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 4: SITEMAPS CONFIGURATION -->
        <div class="wiz-step-pane" id="wiz-pane-4" role="region" aria-label="<?php esc_attr_e('Step 4: XML Sitemaps Configuration', 'gmb-ranker-seo-automation'); ?>">
            <h2 class="wiz-title"><?php echo esc_html($steps[4]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[4]['subtitle']); ?></p>

            <form id="wiz-form-sitemaps">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_module_sitemaps"><?php esc_html_e('Sitemaps Module', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_module_sitemaps" <?php checked(true, $s['module_sitemaps']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Automatically generates dynamic sitemap.xml and nested sub-sitemap indexes.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_sitemap_images"><?php esc_html_e('Include Images in Sitemaps', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_sitemap_images" <?php checked(true, $s['sitemap_images']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Includes featured images and embedded content photos for search crawlers.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label><?php esc_html_e('Public Post Types', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-badges-group">
                            <?php foreach ($eligible_pts as $pt_slug => $pt_label) : 
                                $is_checked = in_array($pt_slug, $s['sitemap_post_types'], true);
                            ?>
                            <label class="wiz-checkbox-badge <?php echo $is_checked ? 'is-checked' : ''; ?>">
                                <input type="checkbox" name="wiz_post_types[]" value="<?php echo esc_attr($pt_slug); ?>" <?php checked($is_checked); ?> />
                                <span><?php echo esc_html($pt_label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="wiz-form-desc"><?php esc_html_e('Select which content post types to include in sitemaps.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="3"><?php esc_html_e('‹ Back', 'gmb-ranker-seo-automation'); ?></button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="5"><?php esc_html_e('Skip Step', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-4"><?php esc_html_e('Save & Continue ›', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 5: OPTIMIZATION & TWEAKS -->
        <div class="wiz-step-pane" id="wiz-pane-5" role="region" aria-label="<?php esc_attr_e('Step 5: SEO Automations & Tweaks', 'gmb-ranker-seo-automation'); ?>">
            <h2 class="wiz-title"><?php echo esc_html($steps[5]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[5]['subtitle']); ?></p>

            <form id="wiz-form-optimization">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_strip_cat"><?php esc_html_e('Strip Category Base', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_strip_cat" <?php checked(true, $s['strip_category']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Removes /category/ slug from category permalinks.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_nofollow_ext"><?php esc_html_e('Nofollow External Links', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_nofollow_ext" <?php checked(true, $s['nofollow_ext']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Automatically adds rel="nofollow" attribute to external links in content.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_new_window"><?php esc_html_e('Open External Links in New Tab', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_new_window" <?php checked(true, $s['new_window_ext']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Automatically appends target="_blank" to external links.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_redirect_attachments"><?php esc_html_e('Redirect Attachment URLs', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_redirect_attachments" <?php checked(true, $s['redirect_attachments']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Redirects media attachment pages directly to the parent post.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_noindex_empty"><?php esc_html_e('Noindex Empty Taxonomies', 'gmb-ranker-seo-automation'); ?></label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_noindex_empty" <?php checked(true, $s['noindex_empty']); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc"><?php esc_html_e('Prevents indexing of empty category and tag archive pages.', 'gmb-ranker-seo-automation'); ?></div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="4"><?php esc_html_e('‹ Back', 'gmb-ranker-seo-automation'); ?></button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="6"><?php esc_html_e('Skip Step', 'gmb-ranker-seo-automation'); ?></button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-5"><?php esc_html_e('Save & Continue ›', 'gmb-ranker-seo-automation'); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 6: READY -->
        <div class="wiz-step-pane" id="wiz-pane-6" role="region" aria-label="<?php esc_attr_e('Step 6: Your Site is Ready!', 'gmb-ranker-seo-automation'); ?>">
            <div class="wiz-ready-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="wiz-title"><?php echo esc_html($steps[6]['title']); ?></h2>
            <p class="wiz-subtitle"><?php echo esc_html($steps[6]['subtitle']); ?></p>

            <div class="wiz-ready-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="wiz-btn-primary wiz-btn-no-decoration">
                    <?php esc_html_e('Go to SEO Dashboard ›', 'gmb-ranker-seo-automation'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings')); ?>" class="wiz-btn-secondary wiz-btn-no-decoration">
                    <?php esc_html_e('Configure Advanced Settings', 'gmb-ranker-seo-automation'); ?>
                </a>
            </div>
        </div>

    </div>

</div>

<input type="hidden" id="gmb-wizard-nonce" value="<?php echo esc_attr($wizard_nonce); ?>" />
<?php
if (function_exists('wp_print_media_templates')) {
    wp_print_media_templates();
}
if (function_exists('wp_print_footer_scripts')) {
    wp_print_footer_scripts();
}
?>
</body>
</html>
