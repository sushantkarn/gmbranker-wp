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

    /**
     * Get active configured AI provider ID
     *
     * @return string
     */
    public static function get_active_provider() {
        return get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', 'openrouter'));
    }

    /**
     * Generate completion text from messages
     *
     * @param array $messages
     * @param float $temperature
     * @return string|WP_Error
     */
    public static function generate_ai_response($messages, $temperature = 0.7) {
        $provider = self::get_active_provider();
        $client   = new GMB_Ranker_SEO_AI_Client();

        $api_key = '';
        $model   = '';
        $options = array(
            'temperature' => $temperature,
        );

        if ($provider === 'groq') {
            $api_key = get_option('gmb_ai_groq_key', '');
            $model   = get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant');
        } elseif ($provider === 'ollama') {
            $model                   = get_option('gmb_ai_ollama_model', 'llama3');
            $options['ollama_url']   = get_option('gmb_ai_ollama_url', 'http://localhost:11434');
        } else {
            $provider = 'openrouter';
            $api_key  = get_option('gmb_ai_openrouter_key', '');
            $model    = get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free');
        }

        $result = $client->generate_completion($provider, $api_key, $model, $messages, $options);

        if (is_wp_error($result)) {
            return $result;
        }

        return isset($result['content']) ? $result['content'] : '';
    }
}
