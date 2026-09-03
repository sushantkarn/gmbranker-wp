<?php
/**
 * Standalone Full-Canvas Setup Wizard View for GMB Ranker SEO
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access the setup wizard.', 'gmb-ranker-seo-automation'));
}

$base_url = defined('GMB_RANKER_SEO_URL') ? GMB_RANKER_SEO_URL : plugins_url('/', dirname(dirname(dirname(dirname(__FILE__)))) . '/gmb-ranker-seo.php');
$base_url = rtrim($base_url, '/') . '/assets/';
$ver = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0';

// Current saved options
$saved_mode           = get_option('gmb_ranker_automation_mode', 'advanced');
$site_type            = get_option('gmb_site_type', 'blog');
$org_name             = get_option('gmb_organization_name', get_bloginfo('name'));
$site_logo            = get_option('gmb_site_logo', '');
$social_image         = get_option('gmb_social_share_image', '');
$api_key              = get_option('gmb_ranker_api_key', '');
$ai_provider          = get_option('gmb_ai_active_provider', get_option('gmb_ai_provider', 'openrouter'));
$ai_key               = '';
if ($ai_provider === 'openrouter') {
    $ai_key = get_option('gmb_ai_openrouter_key', '');
} elseif ($ai_provider === 'groq') {
    $ai_key = get_option('gmb_ai_groq_key', '');
} elseif ($ai_provider === 'gemini') {
    $ai_key = get_option('gmb_ai_gemini_key', '');
} elseif ($ai_provider === 'openai') {
    $ai_key = get_option('gmb_ai_openai_key', '');
}

$module_sitemaps      = get_option('gmb_ranker_module_sitemaps', '1');
$sitemap_images       = get_option('gmb_sitemap_include_images', '1');
$sitemap_pts          = get_option('gmb_sitemap_post_types', array('post', 'page'));
if (!is_array($sitemap_pts)) {
    $sitemap_pts = array('post', 'page');
}

$strip_category       = get_option('gmb_strip_category_base', 'off');
$nofollow_ext         = get_option('gmb_nofollow_external_links', 'on');
$new_window_ext       = get_option('gmb_new_window_external_links', 'on');
$redirect_attachments = get_option('gmb_redirect_attachments', 'on');
$noindex_empty        = get_option('gmb_noindex_empty_taxonomies', 'on');

// System Compatibility Checks
$php_version = phpversion();
$php_pass    = version_compare($php_version, '7.4.0', '>=');
$curl_pass   = function_exists('curl_version');
$ssl_pass    = extension_loaded('openssl');
$dom_pass    = class_exists('DOMDocument');
$json_pass   = function_exists('json_encode');
$wp_version  = get_bloginfo('version');
$wp_pass     = version_compare($wp_version, '5.6', '>=');

$all_pass = $php_pass && $curl_pass && $ssl_pass && $dom_pass && $json_pass && $wp_pass;

$wizard_nonce = wp_create_nonce('gmb_wizard_nonce');

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
    <title>GMB Ranker SEO &mdash; Setup Wizard</title>
    
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

<div class="wiz-container">
    
    <!-- Branding Header -->
    <div class="wiz-logo-wrap">
        <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>">
            <img src="<?php echo esc_url($base_url . 'gmbranker.svg?v=' . $ver); ?>" alt="GMB Ranker" class="wiz-logo-img" />
        </a>
        <div class="wiz-logo-tagline"><?php esc_html_e('Smart SEO & Instant Indexing Autopilot', 'gmb-ranker-seo-automation'); ?></div>
    </div>

    <!-- Stepper Navigation -->
    <div class="wiz-stepper-wrap">
        <div class="wiz-stepper-track">
            <div class="wiz-stepper-progress" id="wiz-progress"></div>
        </div>
        <div class="wiz-stepper">
            <div class="wiz-step-item active" data-step="1" id="wiz-step-indicator-1">
                <div class="wiz-step-circle">1</div>
                <div class="wiz-step-label">Getting Started</div>
            </div>
            <div class="wiz-step-item" data-step="2" id="wiz-step-indicator-2">
                <div class="wiz-step-circle">2</div>
                <div class="wiz-step-label">Your Site</div>
            </div>
            <div class="wiz-step-item" data-step="3" id="wiz-step-indicator-3">
                <div class="wiz-step-circle">3</div>
                <div class="wiz-step-label">API Config</div>
            </div>
            <div class="wiz-step-item" data-step="4" id="wiz-step-indicator-4">
                <div class="wiz-step-circle">4</div>
                <div class="wiz-step-label">Sitemaps</div>
            </div>
            <div class="wiz-step-item" data-step="5" id="wiz-step-indicator-5">
                <div class="wiz-step-circle">5</div>
                <div class="wiz-step-label">Optimization</div>
            </div>
            <div class="wiz-step-item" data-step="6" id="wiz-step-indicator-6">
                <div class="wiz-step-circle">6</div>
                <div class="wiz-step-label">Ready</div>
            </div>
        </div>
    </div>

    <!-- Main Wizard Card Container -->
    <div class="wiz-card">
        
        <!-- ================================================================== -->
        <!-- ================================================================== -->
        <!-- STEP 1: GETTING STARTED (SELECT SETUP MODE)                        -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane active" id="wiz-pane-1">
            <h2 class="wiz-title">Select Setup Mode</h2>
            <p class="wiz-subtitle">Choose how you want GMB Ranker SEO to configure your site settings.</p>

            <form id="wiz-form-mode">
                <!-- Easy Mode -->
                <label class="wiz-option-box <?php echo ($saved_mode === 'easy') ? 'active' : ''; ?>" data-mode="easy">
                    <input type="radio" name="wiz_setup_mode" value="easy" <?php checked($saved_mode, 'easy'); ?> />
                    <div class="wiz-option-content">
                        <strong class="wiz-option-title wiz-option-desc">Easy Mode</strong>
                        <span class="wiz-option-desc">Let autopilot manage all headers, canonicals, and indexation checks automatically. Prefilled for industry standards.</span>
                    </div>
                </label>

                <!-- Advanced Mode -->
                <label class="wiz-option-box <?php echo ($saved_mode === 'advanced') ? 'active' : ''; ?>" data-mode="advanced">
                    <input type="radio" name="wiz_setup_mode" value="advanced" <?php checked($saved_mode, 'advanced'); ?> />
                    <div class="wiz-option-content">
                        <div class="wiz-option-header">
                            <strong class="wiz-option-title">Advanced Mode</strong>
                            <span class="wiz-badge-rec">RECOMMENDED</span>
                        </div>
                        <span class="wiz-option-desc">Fine-tune all indexing engines, schemas, OpenGraph meta, and redirection rules manually.</span>
                    </div>
                </label>

                <!-- Custom Mode -->
                <label class="wiz-option-box <?php echo ($saved_mode === 'custom') ? 'active' : ''; ?>" data-mode="custom">
                    <input type="radio" name="wiz_setup_mode" value="custom" <?php checked($saved_mode, 'custom'); ?> />
                    <div class="wiz-option-content">
                        <div class="wiz-option-header">
                            <strong class="wiz-option-title">Custom Mode</strong>
                            <span class="wiz-badge-pro">PRO</span>
                        </div>
                        <span class="wiz-option-desc">Select this if you have a custom settings preset file you want to use.</span>
                    </div>
                </label>

                <!-- Note box -->
                <div class="wiz-note-box">
                    <span class="wiz-note-tag">NOTE</span>
                    <span>You can easily switch between modes at any point from General Settings.</span>
                </div>

                <!-- Server Compatibility Card -->
                <div class="wiz-compat-card">
                    <div class="wiz-compat-header">
                        <div class="wiz-compat-header-left">
                            <div class="wiz-compat-icon-badge <?php echo $all_pass ? '' : 'is-warn'; ?>">
                                <?php if ($all_pass) : ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <?php else : ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="wiz-compat-info">
                                <span class="wiz-compat-title"><?php echo $all_pass ? 'Your server is 100% compatible to run GMB Ranker SEO' : 'Compatibility Notice'; ?></span>
                                <span class="wiz-compat-subtitle">All core PHP extensions, OpenSSL protocols, and REST hooks are active</span>
                            </div>
                        </div>
                        <button type="button" class="wiz-compat-toggle-btn" id="wiz-toggle-compat-btn">
                            View Details
                            <svg class="toggle-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <div class="wiz-compat-details" id="wiz-compat-details">
                        <div class="wiz-compat-grid">
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name">PHP Version</span>
                                    <span class="wiz-compat-req">Requires PHP 7.4 or higher</span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo esc_html($php_version); ?></span>
                                    <span class="wiz-compat-status <?php echo $php_pass ? 'is-pass' : ''; ?>"><?php echo $php_pass ? ' Pass' : ' Fail'; ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name">cURL Extension</span>
                                    <span class="wiz-compat-req">Required for Google Indexing &amp; IndexNow API</span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $curl_pass ? 'Enabled' : 'Disabled'; ?></span>
                                    <span class="wiz-compat-status <?php echo $curl_pass ? 'is-pass' : ''; ?>"><?php echo $curl_pass ? ' Pass' : ' Fail'; ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name">OpenSSL &amp; JWT</span>
                                    <span class="wiz-compat-req">Required for Google Service Account RSA keys</span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $ssl_pass ? 'Supported' : 'Missing'; ?></span>
                                    <span class="wiz-compat-status <?php echo $ssl_pass ? 'is-pass' : ''; ?>"><?php echo $ssl_pass ? ' Pass' : ' Fail'; ?></span>
                                </div>
                            </div>
                            <div class="wiz-compat-row">
                                <div class="wiz-compat-item-left">
                                    <span class="wiz-compat-name">DOMDocument &amp; XML</span>
                                    <span class="wiz-compat-req">Required for Table of Contents &amp; Sitemap generator</span>
                                </div>
                                <div class="wiz-compat-item-right">
                                    <span class="wiz-compat-value"><?php echo $dom_pass ? 'Active' : 'Missing'; ?></span>
                                    <span class="wiz-compat-status <?php echo $dom_pass ? 'is-pass' : ''; ?>"><?php echo $dom_pass ? ' Pass' : ' Fail'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wiz-card-footer wiz-card-footer-end">
                    <button type="button" class="wiz-btn-primary" id="wiz-btn-next-1">
                        Start Wizard &rsaquo;
                    </button>
                </div>
            </form>
        </div>

        <!-- ================================================================== -->
        <!-- STEP 2: YOUR SITE PROFILE                                          -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane" id="wiz-pane-2">
            <h2 class="wiz-title">Your Site Profile</h2>
            <p class="wiz-subtitle">Provide essential details about your website to help search engines understand your entity.</p>

            <form id="wiz-form-site">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_site_type">Site Type</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <select id="wiz_site_type" class="wiz-input">
                            <option value="blog" <?php selected($site_type, 'blog'); ?>>Personal Blog</option>
                            <option value="business" <?php selected($site_type, 'business'); ?>>Small Business / Local Business</option>
                            <option value="corporation" <?php selected($site_type, 'corporation'); ?>>Corporation / Enterprise</option>
                            <option value="news" <?php selected($site_type, 'news'); ?>>News / Magazine Website</option>
                            <option value="portfolio" <?php selected($site_type, 'portfolio'); ?>>Portfolio / Agency</option>
                            <option value="ecommerce" <?php selected($site_type, 'ecommerce'); ?>>Webshop / eCommerce</option>
                        </select>
                        <div class="wiz-form-desc">Select the primary business categorization for Schema structured data.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_org_name">Business or Website Name</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="text" id="wiz_org_name" class="wiz-input" value="<?php echo esc_attr($org_name); ?>" placeholder="e.g. Acme Corporation" />
                        <div class="wiz-form-desc">The official brand name search engines will associate with your website.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_site_logo">Website Logo</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-upload-group">
                            <input type="text" id="wiz_site_logo" class="wiz-input" value="<?php echo esc_attr($site_logo); ?>" placeholder="https://..." />
                            <button type="button" class="wiz-btn-secondary wiz-media-btn" data-target="#wiz_site_logo" data-preview="#wiz_logo_preview">Upload Image</button>
                        </div>
                        <div id="wiz_logo_preview_wrap" class="wiz-preview-box <?php echo empty($site_logo) ? 'gmb-hidden' : ''; ?>">
                            <img id="wiz_logo_preview" src="<?php echo esc_url($site_logo); ?>" class="wiz-preview-thumb" alt="Logo preview" />
                            <span class="wiz-preview-text">Logo preview for Google Schema Knowledge Graph.</span>
                        </div>
                        <div class="wiz-form-desc">Recommended minimum dimensions: 112x112px in JPG, PNG, SVG or WebP.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_social_image">Default Social Share Image</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-upload-group">
                            <input type="text" id="wiz_social_image" class="wiz-input" value="<?php echo esc_attr($social_image); ?>" placeholder="https://..." />
                            <button type="button" class="wiz-btn-secondary wiz-media-btn" data-target="#wiz_social_image" data-preview="#wiz_social_preview">Upload Image</button>
                        </div>
                        <div id="wiz_social_preview_wrap" class="wiz-preview-box <?php echo empty($social_image) ? 'gmb-hidden' : ''; ?>">
                            <img id="wiz_social_preview" src="<?php echo esc_url($social_image); ?>" class="wiz-preview-thumb" alt="Social preview" />
                            <span class="wiz-preview-text">Fallback image used when posts don't have a featured image.</span>
                        </div>
                        <div class="wiz-form-desc">Recommended dimensions: 1200x630px for Facebook, Twitter/X and LinkedIn previews.</div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="1">&lsaquo; Back</button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="3">Skip Step</button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-2">Save &amp; Continue &rsaquo;</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ================================================================== -->
        <!-- STEP 3: API & AUTOMATION CONFIG                                    -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane" id="wiz-pane-3">
            <h2 class="wiz-title">API &amp; Automation Integrations</h2>
            <p class="wiz-subtitle">Connect IndexNow, Google Indexing, or optional AI keyword generation engines.</p>

            <form id="wiz-form-api">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_api_key">GMB Ranker API Secret Key</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="password" id="wiz_api_key" class="wiz-input" value="<?php echo esc_attr($api_key); ?>" placeholder="gr_sec_..." />
                        <div class="wiz-form-desc">Your unique secret key for headless API requests and remote indexing hooks.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_ai_provider">AI Keyword Engine</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <select id="wiz_ai_provider" class="wiz-input">
                            <option value="openrouter" <?php selected($ai_provider, 'openrouter'); ?>>OpenRouter (Recommended &bull; Free Tier)</option>
                            <option value="groq" <?php selected($ai_provider, 'groq'); ?>>Groq Cloud (Ultra Fast Llama 3.3)</option>
                            <option value="gemini" <?php selected($ai_provider, 'gemini'); ?>>Google Gemini (Flash 1.5)</option>
                            <option value="openai" <?php selected($ai_provider, 'openai'); ?>>OpenAI (GPT-4o Mini)</option>
                            <option value="claude" <?php selected($ai_provider, 'claude'); ?>>Anthropic Claude (Haiku 3.5)</option>
                            <option value="ollama" <?php selected($ai_provider, 'ollama'); ?>>Ollama (Local Offline LLM)</option>
                        </select>
                        <div class="wiz-form-desc">Used for 1-click SEO title recommendations, meta descriptions, and focus keyword extraction.</div>
                    </div>
                </div>

                <div class="wiz-form-row" id="wiz_ai_key_row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_ai_key">AI Provider API Key</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <input type="password" id="wiz_ai_key" class="wiz-input" value="<?php echo esc_attr($ai_key); ?>" placeholder="sk-or-... / gsk_..." />
                        <div class="wiz-form-desc">Enter your provider key. OpenRouter and Groq offer generous free keys with zero setup fees.</div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="2">&lsaquo; Back</button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="4">Skip Step</button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-3">Save &amp; Continue &rsaquo;</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ================================================================== -->
        <!-- STEP 4: SITEMAPS CONFIGURATION                                     -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane" id="wiz-pane-4">
            <h2 class="wiz-title">XML Sitemaps Configuration</h2>
            <p class="wiz-subtitle">Enable automated XML sitemaps to help search engines crawl and discover your latest content.</p>

            <form id="wiz-form-sitemaps">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_module_sitemaps">Sitemaps Module</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_module_sitemaps" <?php checked($module_sitemaps, '1'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Automatically generates dynamic <code>sitemap.xml</code> and nested sub-sitemap indexes.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_sitemap_images">Include Images in Sitemaps</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_sitemap_images" <?php checked($sitemap_images, '1'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Includes featured images and embedded content photos to rank higher on Google Image Search.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label>Public Post Types</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <div class="wiz-badges-group">
                            <?php
                            $public_pts = get_post_types(array('public' => true), 'objects');
                            foreach ($public_pts as $pt_slug => $pt_obj) :
                                if (in_array($pt_slug, array('attachment', 'elementor_library'), true)) continue;
                                $is_checked = in_array($pt_slug, $sitemap_pts, true);
                            ?>
                            <label class="wiz-checkbox-badge <?php echo $is_checked ? 'is-checked' : ''; ?>">
                                <input type="checkbox" name="wiz_post_types[]" value="<?php echo esc_attr($pt_slug); ?>" <?php checked($is_checked); ?> />
                                <span><?php echo esc_html($pt_obj->labels->name); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="wiz-form-desc">Select which content post types to include in sitemaps.</div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="3">&lsaquo; Back</button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="5">Skip Step</button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-4">Save &amp; Continue &rsaquo;</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ================================================================== -->
        <!-- STEP 5: OPTIMIZATION & TWEAKS                                      -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane" id="wiz-pane-5">
            <h2 class="wiz-title">SEO Automations &amp; Tweaks</h2>
            <p class="wiz-subtitle">Apply essential automated SEO tweaks to optimize your link structure and crawl budget.</p>

            <form id="wiz-form-optimization">
                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_strip_cat">Strip Category Base</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_strip_cat" <?php checked($strip_category, 'on'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Removes <code>/category/</code> slug from category permalinks (e.g. <code>example.com/category/news/</code> becomes <code>example.com/news/</code>).</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_nofollow_ext">Nofollow External Links</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_nofollow_ext" <?php checked($nofollow_ext, 'on'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Automatically adds <code>rel="nofollow"</code> attribute to all external links in post content to preserve PageRank.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_new_window">Open External Links in New Tab</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_new_window" <?php checked($new_window_ext, 'on'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Automatically appends <code>target="_blank"</code> to all external links to keep users on your website longer.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_redirect_attachments">Redirect Attachment URLs</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_redirect_attachments" <?php checked($redirect_attachments, 'on'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Redirects thin media attachment pages directly to the parent post where the image is embedded.</div>
                    </div>
                </div>

                <div class="wiz-form-row">
                    <div class="wiz-form-label-col">
                        <label for="wiz_noindex_empty">Noindex Empty Taxonomies</label>
                    </div>
                    <div class="wiz-form-input-col">
                        <label class="wiz-switch">
                            <input type="checkbox" id="wiz_noindex_empty" <?php checked($noindex_empty, 'on'); ?> />
                            <span class="wiz-slider"></span>
                        </label>
                        <div class="wiz-form-desc">Prevents indexing of empty category and tag archive pages that contain zero published posts.</div>
                    </div>
                </div>

                <div class="wiz-card-footer">
                    <button type="button" class="wiz-btn-secondary wiz-btn-back" data-to="4">&lsaquo; Back</button>
                    <div class="wiz-footer-btn-group">
                        <button type="button" class="wiz-btn-secondary wiz-btn-skip" data-to="6">Skip Step</button>
                        <button type="button" class="wiz-btn-primary" id="wiz-btn-next-5">Save &amp; Continue &rsaquo;</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ================================================================== -->
        <!-- STEP 6: READY                                                      -->
        <!-- ================================================================== -->
        <div class="wiz-step-pane" id="wiz-pane-6">
            <div class="wiz-ready-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="wiz-title">Your Site is Ready!</h2>
            <p class="wiz-subtitle">GMB Ranker SEO has been configured and is actively optimizing your website, indexing hooks, and structured schemas.</p>

            <div class="wiz-ready-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="wiz-btn-primary wiz-btn-no-decoration">
                    Go to SEO Dashboard &rsaquo;
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-settings')); ?>" class="wiz-btn-secondary wiz-btn-no-decoration">
                    Configure Advanced Settings
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
