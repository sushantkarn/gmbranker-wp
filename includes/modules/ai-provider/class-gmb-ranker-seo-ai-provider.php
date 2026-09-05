<?php
/**
 * AI Provider High-Level Orchestrator
 *
 * Delegates completion requests to canonical GMB_Ranker_SEO_AI_Client,
 * eliminating duplicate HTTP request logic and enforcing SSL/SSRF security.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_AI_Provider {

    /** @var array<string, mixed> */
    private static $last_request = array();

    /**
     * Get active configured AI provider ID
     *
     * @return string
     */
    public static function get_active_provider() {
        $provider = get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', ''));

        return sanitize_key(trim((string) $provider));
    }

    /**
     * Resolve configured provider credentials and model without inventing defaults.
     *
     * @param string|null $provider
     * @return array|WP_Error
     */
    public static function get_provider_config($provider = null) {
        $provider = !empty($provider) ? sanitize_key(trim((string) $provider)) : self::get_active_provider();

        if (empty($provider)) {
            return new WP_Error('missing_provider', __('No AI provider is configured.', 'gmb-ranker-seo-automation'));
        }

        $config = array(
            'provider' => $provider,
            'api_key'  => '',
            'model'    => '',
            'options'  => array(),
        );

        switch ($provider) {
            case 'groq':
                $config['api_key'] = get_option('gmb_ai_groq_key', '');
                $config['model']   = get_option('gmb_ai_groq_model', '');
                break;

            case 'ollama':
                $config['model'] = get_option('gmb_ai_ollama_model', '');
                $config['options']['ollama_url'] = get_option('gmb_ai_ollama_url', '');
                break;

            case 'openrouter':
                $config['api_key'] = get_option('gmb_ai_openrouter_key', '');
                $config['model']   = get_option('gmb_ai_openrouter_model', '');
                break;

            case 'nvidia':
                $config['api_key'] = get_option('gmb_ai_nvidia_key', '');
                $config['model']   = get_option('gmb_ai_nvidia_model', '');
                break;

            default:
                return new WP_Error(
                    'unsupported_provider',
                    sprintf(__('Unsupported AI provider: %s', 'gmb-ranker-seo-automation'), esc_html($provider))
                );
        }

        return $config;
    }

    /**
     * Generate completion text from messages
     *
     * @param array $messages
     * @param float $temperature
     * @return string|WP_Error
     */
    public static function generate_ai_response($messages, $temperature = 0.7) {
        $client   = new GMB_Ranker_SEO_AI_Client();
        $options  = array(
            'temperature' => $temperature,
        );
        $options['max_retries'] = min(2, max(0, absint(get_option('gmb_ai_max_retries', 0))));
        $failures = array();
        $candidates = self::get_provider_chain();
        foreach ($candidates as $index => $provider) {
            $config = self::get_provider_config($provider);
            if (is_wp_error($config)) {
                $failures[] = $config->get_error_message();
                continue;
            }
            $provider_options = $options;
            if (!empty($config['options']) && is_array($config['options'])) {
                $provider_options = array_merge($provider_options, $config['options']);
            }
            $result = $client->generate_completion($provider, $config['api_key'], $config['model'], $messages, $provider_options);
            self::$last_request = array(
                'provider' => $provider,
                'model'    => $config['model'],
                'success'  => !is_wp_error($result),
                'priority' => $index + 1,
            );
            if (!is_wp_error($result)) {
                self::$last_request['fallback_used'] = ($index > 0);
                return $result['content'];
            }
            $failures[] = sprintf('%s: %s', $provider, $result->get_error_message());
            $error_data = $result->get_error_data();
            $category = is_array($error_data) ? ($error_data['category'] ?? '') : '';
            if (!self::is_failover_eligible($category)) {
                break;
            }
        }

        return new WP_Error('ai_all_providers_failed', implode(' | ', $failures), array(
            'category' => 'provider_error',
            'failures' => $failures,
        ));
    }

    /** Return enabled providers in persisted priority order with legacy migration. */
    private static function get_provider_chain() {
        $stored = class_exists('GMB_Ranker_SEO_Integration_Registry')
            ? GMB_Ranker_SEO_Integration_Registry::get_ai_provider_chain()
            : get_option('gmb_ai_provider_chain', array());
        $chain = array();
        if (is_array($stored)) {
            foreach ($stored as $entry) {
                if (is_string($entry)) {
                    $entry = array('provider' => $entry, 'enabled' => true, 'priority' => count($chain));
                }
                $provider = isset($entry['provider']) ? sanitize_key($entry['provider']) : '';
                if ($provider && !empty($entry['enabled']) && !in_array($provider, $chain, true)) {
                    $chain[] = $provider;
                }
            }
        }
        if (empty($chain)) {
            $active = self::get_active_provider();
            if ($active) $chain[] = $active;
            $fallback = self::get_fallback_config();
            if (!empty($fallback['enabled']) && !empty($fallback['provider']) && !in_array($fallback['provider'], $chain, true)) {
                $chain[] = $fallback['provider'];
            }
        }
        return $chain;
    }

    private static function is_failover_eligible($category) {
        return in_array($category, array('rate_limited', 'provider_error', 'network_error', 'timeout', 'empty_response'), true);
    }

    /** Execute a minimal request against one explicitly selected trusted provider. */
    public static function test_connection($provider) {
        $config = self::get_provider_config($provider);
        if (is_wp_error($config)) return $config;
        $client = new GMB_Ranker_SEO_AI_Client();
        $started = microtime(true);
        $result = $client->generate_completion($provider, $config['api_key'], $config['model'], array(
            array('role' => 'user', 'content' => 'Reply with the single word OK.'),
        ), array('temperature' => 0, 'max_retries' => 0, 'ollama_url' => $config['options']['ollama_url'] ?? ''));
        if (is_wp_error($result)) return $result;
        $result['latency_ms'] = round((microtime(true) - $started) * 1000);
        return $result;
    }

    /**
     * Return the last provider actually used for observability and UI diagnostics.
     */
    public static function get_last_request() {
        return self::$last_request;
    }

    /**
     * Resolve the explicitly configured fallback provider.
     */
    private static function get_fallback_config() {
        $provider = sanitize_key((string) get_option('gmb_ai_fallback_provider', ''));
        $config = array(
            'enabled'  => (bool) get_option('gmb_ai_fallback_enabled', false),
            'provider' => $provider,
            'api_key'  => '',
            'model'    => sanitize_text_field((string) get_option('gmb_ai_fallback_model', '')),
        );
        if ($provider === 'nvidia') {
            $config['api_key'] = get_option('gmb_ai_nvidia_key', '');
            if (empty($config['model'])) $config['model'] = get_option('gmb_ai_nvidia_model', '');
        } elseif ($provider === 'openrouter') {
            $config['api_key'] = get_option('gmb_ai_openrouter_key', '');
            if (empty($config['model'])) $config['model'] = get_option('gmb_ai_openrouter_model', '');
        } elseif ($provider === 'groq') {
            $config['api_key'] = get_option('gmb_ai_groq_key', '');
            if (empty($config['model'])) $config['model'] = get_option('gmb_ai_groq_model', '');
        } elseif ($provider === 'ollama') {
            $config['model'] = empty($config['model']) ? get_option('gmb_ai_ollama_model', '') : $config['model'];
        } else {
            $config['provider'] = '';
        }
        return $config;
    }
}
