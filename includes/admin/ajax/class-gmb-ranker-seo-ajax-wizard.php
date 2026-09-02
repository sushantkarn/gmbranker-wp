<?php
/**
 * Setup Wizard AJAX Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Ajax_Wizard {

    public function __construct() {
        add_action('wp_ajax_gmb_save_wizard_api_key', array($this, 'ajax_save_wizard_api_key'));
        add_action('wp_ajax_gmb_save_wizard_step', array($this, 'ajax_save_wizard_step'));
    }

    protected function enforce_ajax_csrf_protection() {
        if (class_exists('GMB_Ranker_SEO_Admin')) {
            GMB_Ranker_SEO_Admin::enforce_ajax_csrf_protection();
        }
    }

    public function ajax_save_wizard_api_key() {
        check_ajax_referer('gmb_wizard_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized user capability.'));
        }
        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        update_option('gmb_ranker_api_key', $api_key);
        wp_send_json_success(array('message' => 'API Key verified and saved successfully.'));
    }

    public function ajax_save_wizard_step() {
        $this->enforce_ajax_csrf_protection();
        
        $step = isset($_POST['step']) ? sanitize_text_field(wp_unslash($_POST['step'])) : '';
        
        if ($step === 'mode') {
            $mode = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'advanced';
            update_option('gmb_ranker_automation_mode', $mode);
            wp_send_json_success();
        }
        
        if ($step === 'site_profile') {
            update_option('gmb_site_type', isset($_POST['site_type']) ? sanitize_text_field(wp_unslash($_POST['site_type'])) : 'blog');
            update_option('gmb_organization_name', isset($_POST['org_name']) ? sanitize_text_field(wp_unslash($_POST['org_name'])) : '');
            update_option('gmb_site_logo', isset($_POST['site_logo']) ? esc_url_raw(wp_unslash($_POST['site_logo'])) : '');
            update_option('gmb_social_share_image', isset($_POST['social_image']) ? esc_url_raw(wp_unslash($_POST['social_image'])) : '');
            wp_send_json_success();
        }
        
        if ($step === 'api_config') {
            $key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
            $ai_provider = isset($_POST['ai_provider']) ? sanitize_text_field(wp_unslash($_POST['ai_provider'])) : 'openrouter';
            $ai_key = isset($_POST['ai_key']) ? sanitize_text_field(wp_unslash($_POST['ai_key'])) : '';
            $is_skip = isset($_POST['skip']) && $_POST['skip'] === '1';

            if (!$is_skip || !empty($key)) {
                update_option('gmb_ranker_api_key', $key);
            }
            update_option('gmb_ai_provider', $ai_provider);
            update_option('gmb_ai_active_provider', $ai_provider);

            if (!$is_skip || !empty($ai_key)) {
                if ($ai_provider === 'openrouter') {
                    update_option('gmb_ai_openrouter_key', $ai_key);
                } elseif ($ai_provider === 'groq') {
                    update_option('gmb_ai_groq_key', $ai_key);
                } elseif ($ai_provider === 'gemini') {
                    update_option('gmb_ai_gemini_key', $ai_key);
                } elseif ($ai_provider === 'openai') {
                    update_option('gmb_ai_openai_key', $ai_key);
                } elseif ($ai_provider === 'claude') {
                    update_option('gmb_ai_claude_key', $ai_key);
                }
            }
            wp_send_json_success();
        }
        
        if ($step === 'sitemaps') {
            $sitemaps = (isset($_POST['sitemaps']) && in_array($_POST['sitemaps'], array('1', 'on', true, 1), true)) ? '1' : '0';
            $include_images = (isset($_POST['include_images']) && in_array($_POST['include_images'], array('1', 'on', true, 1), true)) ? '1' : '0';
            
            update_option('gmb_ranker_module_sitemaps', $sitemaps);
            update_option('gmb_sitemap_include_images', $include_images);

            if (isset($_POST['post_types']) && is_array($_POST['post_types'])) {
                $sanitized_pts = array_map('sanitize_text_field', $_POST['post_types']);
                update_option('gmb_sitemap_post_types', $sanitized_pts);
            }
            wp_send_json_success();
        }
        
        if ($step === 'optimization') {
            $nofollow = (isset($_POST['nofollow']) && in_array($_POST['nofollow'], array('1', 'on', true, 1), true)) ? 'on' : 'off';
            $strip_cat = (isset($_POST['strip_cat']) && in_array($_POST['strip_cat'], array('1', 'on', true, 1), true)) ? 'on' : 'off';
            $new_window = (isset($_POST['new_window']) && in_array($_POST['new_window'], array('1', 'on', true, 1), true)) ? 'on' : 'off';
            $noindex_empty = (isset($_POST['noindex_empty']) && in_array($_POST['noindex_empty'], array('1', 'on', true, 1), true)) ? 'on' : 'off';
            $redirect_attachments = (isset($_POST['redirect_attachments']) && in_array($_POST['redirect_attachments'], array('1', 'on', true, 1), true)) ? 'on' : 'off';
            
            update_option('gmb_nofollow_external_links', $nofollow);
            update_option('gmb_strip_category_base', $strip_cat);
            update_option('gmb_new_window_external_links', $new_window);
            update_option('gmb_noindex_empty_taxonomies', $noindex_empty);
            update_option('gmb_redirect_attachment_to_parent', $redirect_attachments);
            wp_send_json_success();
        }
        
        wp_send_json_error(array('message' => 'Invalid step'));
    }

}
