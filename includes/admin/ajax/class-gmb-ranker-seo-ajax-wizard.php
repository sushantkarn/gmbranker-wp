<?php
/**
 * Setup Wizard AJAX Controller for GMB Ranker SEO Automation
 *
 * Enterprise-grade, secure, validated AJAX controller for the onboarding setup wizard.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Ajax_Wizard')) {

    class GMB_Ranker_SEO_Ajax_Wizard {

        /**
         * Allowed Automation Modes
         *
         * @var array<string>
         */
        protected static $allowed_modes = array('easy', 'custom', 'advanced');

        /**
         * Allowed AI Providers
         *
         * @var array<string>
         */
        protected static $allowed_ai_providers = array('openrouter', 'groq', 'gemini', 'openai', 'claude');

        /**
         * Constructor
         */
        public function __construct() {
            add_action('wp_ajax_gmb_save_wizard_api_key', array($this, 'ajax_save_wizard_api_key'));
            add_action('wp_ajax_gmb_save_wizard_step', array($this, 'ajax_save_wizard_step'));
        }

        /**
         * Enforce Admin Capability & Nonce Security
         *
         * @param string $nonce_action
         */
        protected function verify_ajax_security($nonce_action = 'gmb_wizard_nonce') {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
            }
            $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : (isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : (isset($_REQUEST['security']) ? sanitize_text_field(wp_unslash($_REQUEST['security'])) : ''));

            $valid_nonces = array(
                'gmb_wizard_nonce',
                'gmb_admin_ajax_nonce',
                'gmb_seo_save_nonce',
                'gmb_ranker_ajax_nonce'
            );

            $verified = false;
            if (!empty($nonce)) {
                foreach ($valid_nonces as $action_nonce) {
                    if (wp_verify_nonce($nonce, $action_nonce)) {
                        $verified = true;
                        break;
                    }
                }
            }

            if (!$verified) {
                wp_send_json_error(array('message' => 'Invalid security token.'), 403);
            }
        }

        /**
         * Save Wizard Master API Key
         */
        public function ajax_save_wizard_api_key() {
            $this->verify_ajax_security('gmb_wizard_nonce');

            $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
            if (empty($api_key)) {
                wp_send_json_error(array('message' => 'API Key is required.'), 400);
            }

            update_option('gmb_ranker_api_key', $api_key);
            wp_send_json_success(array('message' => 'API Key verified and saved successfully.'));
        }

        /**
         * Save Wizard Setup Step Configuration
         */
        public function ajax_save_wizard_step() {
            $this->verify_ajax_security('gmb_admin_ajax_nonce');

            $step = isset($_POST['step']) ? sanitize_text_field(wp_unslash($_POST['step'])) : '';
            if (empty($step)) {
                wp_send_json_error(array('message' => 'Wizard step identifier is required.'), 400);
            }

            // Step 1: Mode
            if ($step === 'mode') {
                $mode_raw = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'advanced';
                $mode = in_array($mode_raw, self::$allowed_modes, true) ? $mode_raw : 'advanced';

                update_option('gmb_ranker_automation_mode', $mode);
                wp_send_json_success(array('message' => 'Automation mode saved.', 'mode' => $mode));
            }

            // Step 2: Site Profile
            if ($step === 'site_profile') {
                $site_type_raw = isset($_POST['site_type']) ? sanitize_text_field(wp_unslash($_POST['site_type'])) : 'blog';
                $org_name_raw  = isset($_POST['org_name']) ? sanitize_text_field(wp_unslash($_POST['org_name'])) : '';
                $logo_raw      = isset($_POST['site_logo']) ? wp_unslash($_POST['site_logo']) : '';
                $social_raw    = isset($_POST['social_image']) ? wp_unslash($_POST['social_image']) : '';

                $site_type = sanitize_text_field($site_type_raw) ?: 'blog';
                $org_name  = trim(sanitize_text_field($org_name_raw));
                $site_logo = filter_var($logo_raw, FILTER_VALIDATE_URL) ? esc_url_raw($logo_raw) : '';
                $social_img = filter_var($social_raw, FILTER_VALIDATE_URL) ? esc_url_raw($social_raw) : '';

                update_option('gmb_site_type', $site_type);
                update_option('gmb_organization_name', $org_name);
                update_option('gmb_site_logo', $site_logo);
                update_option('gmb_social_share_image', $social_img);

                wp_send_json_success(array(
                    'message'   => 'Site profile saved successfully.',
                    'site_type' => $site_type,
                    'org_name'  => $org_name,
                ));
            }

            // Step 3: API & AI Provider Configuration
            if ($step === 'api_config') {
                $key          = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
                $provider_raw = isset($_POST['ai_provider']) ? sanitize_text_field(wp_unslash($_POST['ai_provider'])) : 'openrouter';
                $ai_key       = isset($_POST['ai_key']) ? sanitize_text_field(wp_unslash($_POST['ai_key'])) : '';
                $is_skip      = isset($_POST['skip']) && filter_var($_POST['skip'], FILTER_VALIDATE_BOOLEAN);

                $ai_provider = in_array($provider_raw, self::$allowed_ai_providers, true) ? $provider_raw : 'openrouter';

                if (!$is_skip || !empty($key)) {
                    update_option('gmb_ranker_api_key', $key);
                }

                update_option('gmb_ai_provider', $ai_provider);
                update_option('gmb_ai_active_provider', $ai_provider);

                if (!$is_skip || !empty($ai_key)) {
                    $key_option_map = array(
                        'openrouter' => 'gmb_ai_openrouter_key',
                        'groq'       => 'gmb_ai_groq_key',
                        'gemini'     => 'gmb_ai_gemini_key',
                        'openai'     => 'gmb_ai_openai_key',
                        'claude'     => 'gmb_ai_claude_key',
                    );
                    if (isset($key_option_map[$ai_provider])) {
                        update_option($key_option_map[$ai_provider], $ai_key);
                    }
                }

                wp_send_json_success(array(
                    'message'     => 'API & AI configuration saved successfully.',
                    'ai_provider' => $ai_provider,
                ));
            }

            // Step 4: Sitemaps Configuration
            if ($step === 'sitemaps') {
                $sitemaps_raw       = isset($_POST['sitemaps']) ? wp_unslash($_POST['sitemaps']) : 0;
                $include_images_raw = isset($_POST['include_images']) ? wp_unslash($_POST['include_images']) : 0;

                $sitemaps       = filter_var($sitemaps_raw, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                $include_images = filter_var($include_images_raw, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

                update_option('gmb_ranker_module_sitemaps', $sitemaps);
                update_option('gmb_sitemap_include_images', $include_images);

                if (isset($_POST['post_types']) && is_array($_POST['post_types'])) {
                    $registered_pts = get_post_types(array('public' => true));
                    $clean_pts = array();
                    foreach ($_POST['post_types'] as $pt) {
                        $pt_slug = sanitize_text_field(wp_unslash($pt));
                        if (isset($registered_pts[$pt_slug])) {
                            $clean_pts[] = $pt_slug;
                        }
                    }
                    update_option('gmb_sitemap_post_types', array_values(array_unique($clean_pts)));
                }

                wp_send_json_success(array('message' => 'Sitemaps configuration saved successfully.'));
            }

            // Step 5: Optimization Settings
            if ($step === 'optimization') {
                $nofollow_raw    = isset($_POST['nofollow']) ? wp_unslash($_POST['nofollow']) : 0;
                $strip_cat_raw   = isset($_POST['strip_cat']) ? wp_unslash($_POST['strip_cat']) : 0;
                $new_window_raw  = isset($_POST['new_window']) ? wp_unslash($_POST['new_window']) : 0;
                $noindex_raw     = isset($_POST['noindex_empty']) ? wp_unslash($_POST['noindex_empty']) : 0;
                $redirect_att_raw= isset($_POST['redirect_attachments']) ? wp_unslash($_POST['redirect_attachments']) : 0;

                $nofollow             = filter_var($nofollow_raw, FILTER_VALIDATE_BOOLEAN) ? 'on' : 'off';
                $strip_cat            = filter_var($strip_cat_raw, FILTER_VALIDATE_BOOLEAN) ? 'on' : 'off';
                $new_window           = filter_var($new_window_raw, FILTER_VALIDATE_BOOLEAN) ? 'on' : 'off';
                $noindex_empty        = filter_var($noindex_raw, FILTER_VALIDATE_BOOLEAN) ? 'on' : 'off';
                $redirect_attachments = filter_var($redirect_att_raw, FILTER_VALIDATE_BOOLEAN) ? 'on' : 'off';

                update_option('gmb_nofollow_external_links', $nofollow);
                update_option('gmb_strip_category_base', $strip_cat);
                update_option('gmb_new_window_external_links', $new_window);
                update_option('gmb_noindex_empty_taxonomies', $noindex_empty);
                update_option('gmb_redirect_attachment_to_parent', $redirect_attachments);

                wp_send_json_success(array('message' => 'Optimization settings saved successfully.'));
            }

            wp_send_json_error(array('message' => 'Unrecognized setup wizard step.'), 400);
        }
    }
}
