<?php
/**
 * Canonical Integration Registry & Manager Service
 *
 * Centralizes configuration, credentials, SSRF validation,
 * AI provider definitions, IndexNow key lifecycle, and webhook tokens.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Integration_Registry {

    /**
     * Get list of registered AI providers
     *
     * @return array
     */
    public static function get_ai_providers() {
        $providers = array(
            'openrouter' => array(
                'id'            => 'openrouter',
                'name'          => __('OpenRouter', 'gmb-ranker-seo-automation'),
                'description'   => __('Cloud AI gateway offering access to free Llama 3.1, Gemma 2, and Mistral models.', 'gmb-ranker-seo-automation'),
                'icon'          => 'images/ai/openrouter.svg',
                'key_option'    => 'gmb_ai_openrouter_key',
                'model_option'  => 'gmb_ai_openrouter_model',
                'default_model' => 'meta-llama/llama-3.1-8b-instruct:free',
                'model_presets' => array(
                    'meta-llama/llama-3.1-8b-instruct:free',
                    'mistralai/mistral-7b-instruct:free',
                    'google/gemma-2-9b-it:free',
                ),
                'doc_url'       => 'https://openrouter.ai/keys',
                'key_placeholder' => 'sk-or-v1-...',
                'model_placeholder' => 'meta-llama/llama-3.1-8b-instruct:free',
                'is_local'      => false,
            ),
            'groq' => array(
                'id'            => 'groq',
                'name'          => __('Groq Cloud', 'gmb-ranker-seo-automation'),
                'description'   => __('Ultra-fast LPU inference engine for Llama 3.1 and Mixtral models.', 'gmb-ranker-seo-automation'),
                'icon'          => 'images/ai/groq.svg',
                'key_option'    => 'gmb_ai_groq_key',
                'model_option'  => 'gmb_ai_groq_model',
                'default_model' => 'llama-3.1-8b-instant',
                'model_presets' => array(
                    'llama-3.1-8b-instant',
                    'llama-3.3-70b-versatile',
                    'mixtral-8x7b-32768',
                ),
                'doc_url'       => 'https://console.groq.com/keys',
                'key_placeholder' => 'gsk_...',
                'model_placeholder' => 'llama-3.1-8b-instant',
                'is_local'      => false,
            ),
            'ollama' => array(
                'id'            => 'ollama',
                'name'          => __('Ollama (Local AI)', 'gmb-ranker-seo-automation'),
                'description'   => __('100% Free & Offline local AI server running directly on your host environment.', 'gmb-ranker-seo-automation'),
                'icon'          => 'images/ai/ollama-icon.svg',
                'url_option'    => 'gmb_ai_ollama_url',
                'model_option'  => 'gmb_ai_ollama_model',
                'default_url'   => 'http://localhost:11434',
                'default_model' => 'llama3',
                'model_presets' => array(
                    'llama3',
                    'mistral',
                    'gemma2',
                ),
                'doc_url'       => 'https://ollama.com',
                'url_placeholder' => 'http://localhost:11434',
                'model_placeholder' => 'llama3',
                'is_local'      => true,
            ),
        );

        /**
         * Filter registered AI providers to allow custom extensions
         *
         * @param array $providers
         */
        return apply_filters('gmb_ranker_ai_providers', $providers);
    }

    /**
     * Get active AI provider ID
     *
     * @return string
     */
    public static function get_active_ai_provider() {
        $provider = get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', 'openrouter'));
        $valid_providers = array_keys(self::get_ai_providers());
        if (!in_array($provider, $valid_providers, true)) {
            $provider = 'openrouter';
        }
        return $provider;
    }

    /**
     * Get single canonical IndexNow API key
     *
     * Handles legacy option key precedence and migration cleanly.
     *
     * @return string
     */
    public static function get_indexnow_key() {
        $key = get_option('gmb_ranker_indexnow_key', '');
        if (empty($key)) {
            $key = get_option('gmb_integration_indexnow_key', '');
        }

        if (empty($key) && class_exists('GMB_Ranker_SEO_Instant_Indexing')) {
            $key = GMB_Ranker_SEO_Instant_Indexing::get_indexnow_key();
        }

        if (!empty($key)) {
            update_option('gmb_ranker_indexnow_key', $key);
            update_option('gmb_integration_indexnow_key', $key);
        }

        return $key;
    }

    /**
     * Get stored webhook secret without render-time side effects
     *
     * @param bool $generate_if_empty Whether to generate if empty (only set true on explicit setup/save actions)
     * @return string
     */
    public static function get_webhook_secret($generate_if_empty = false) {
        $secret = get_option('gmb_integration_webhook_secret', '');
        if (empty($secret) && $generate_if_empty) {
            $secret = wp_generate_password(24, false);
            update_option('gmb_integration_webhook_secret', $secret);
        }
        return $secret;
    }

    /**
     * Rotate webhook secret token explicitly
     *
     * @return string New secret token
     */
    public static function rotate_webhook_secret() {
        $new_secret = wp_generate_password(32, false);
        update_option('gmb_integration_webhook_secret', $new_secret);
        return $new_secret;
    }

    /**
     * Get inbound webhook REST endpoint URL
     *
     * @return string
     */
    public static function get_webhook_endpoint() {
        return rest_url('gmb-ranker/v1/webhook');
    }

    /**
     * Mask sensitive API key or secret token
     *
     * @param string $secret
     * @return string
     */
    public static function mask_secret($secret) {
        $secret = trim((string) $secret);
        if (empty($secret)) {
            return '';
        }
        $length = strlen($secret);
        if ($length <= 8) {
            return '********';
        }
        $prefix = substr($secret, 0, 8);
        return $prefix . '****************';
    }

    /**
     * Validate URL for outbound requests with SSRF protections
     *
     * @param string $url
     * @param bool $allow_local Whether to allow local IPs (e.g. for local Ollama)
     * @return string|false Validated URL or false on failure/SSRF risk
     */
    public static function validate_outbound_url($url, $allow_local = false) {
        $url = esc_url_raw(trim($url));
        if (empty($url)) {
            return false;
        }

        $parsed = wp_parse_url($url);
        if (!is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, array('http', 'https'), true)) {
            return false;
        }

        $host = strtolower($parsed['host']);

        // Check if allowed local server (e.g., local Ollama instance)
        if (!$allow_local) {
            if (in_array($host, array('localhost', '127.0.0.1', '::1', '0.0.0.0', '169.254.169.254'), true)) {
                return false;
            }
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return false;
                }
            }
        }

        return $url;
    }

    /**
     * Get complete, truthful view model for integrations presentation layer
     *
     * @return array
     */
    public static function get_view_model() {
        $api_key    = get_option('gmb_ranker_api_key', '');
        $cloud_sync = get_option('gmb_ranker_cloud_sync', '1');

        $is_api_key_configured = !empty(trim($api_key));

        // Workspace Metadata (Only display linked values when key is configured)
        $workspace_name = get_option('gmb_workspace_name', '');
        $workspace_email = get_option('gmb_workspace_email', '');
        $workspace_gsc  = get_option('gmb_workspace_gsc_property', '');
        $workspace_ga4  = get_option('gmb_workspace_ga4_stream', '');
        $workspace_gmb  = get_option('gmb_workspace_gmb_location', '');

        $ai_providers = self::get_ai_providers();
        $active_ai_provider = self::get_active_ai_provider();

        $ai_provider_data = array();
        foreach ($ai_providers as $pid => $pdata) {
            $key_val = isset($pdata['key_option']) ? get_option($pdata['key_option'], '') : '';
            $url_val = isset($pdata['url_option']) ? get_option($pdata['url_option'], $pdata['default_url']) : '';
            $model_val = isset($pdata['model_option']) ? get_option($pdata['model_option'], $pdata['default_model']) : '';

            $ai_provider_data[$pid] = array(
                'definition'  => $pdata,
                'key'         => $key_val,
                'key_masked'  => self::mask_secret($key_val),
                'url'         => $url_val,
                'model'       => $model_val,
                'configured'  => !empty($key_val) || ($pdata['is_local'] && !empty($url_val)),
            );
        }

        $indexnow_key  = self::get_indexnow_key();
        $indexnow_auto = get_option('gmb_integration_indexnow_auto', '1');

        $webhook_url    = get_option('gmb_integration_webhook_url', '');
        $webhook_secret = self::get_webhook_secret(false); // No render side-effects!

        return array(
            'cloud' => array(
                'api_key'        => $api_key,
                'api_key_masked' => self::mask_secret($api_key),
                'configured'     => $is_api_key_configured,
                'sync_mode'      => $cloud_sync,
                'workspace'      => array(
                    'name'  => $workspace_name,
                    'email' => $workspace_email,
                    'gsc'   => $workspace_gsc,
                    'ga4'   => $workspace_ga4,
                    'gmb'   => $workspace_gmb,
                ),
            ),
            'ai' => array(
                'active_provider' => $active_ai_provider,
                'providers'       => $ai_provider_data,
            ),
            'indexnow' => array(
                'key'  => $indexnow_key,
                'auto' => $indexnow_auto,
            ),
            'webhooks' => array(
                'endpoint'      => self::get_webhook_endpoint(),
                'secret'        => $webhook_secret,
                'secret_masked' => self::mask_secret($webhook_secret),
                'outbound_url'  => $webhook_url,
            ),
        );
    }
}
