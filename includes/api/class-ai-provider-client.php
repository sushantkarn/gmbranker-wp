<?php
/**
 * AI Provider API Client for GMB Ranker SEO Automation
 *
 * Supports OpenRouter, Groq, and Ollama with standardized chat completions.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_AI_Client {

    /**
     * Generate completion
     *
     * @param string $provider   openrouter | groq | ollama
     * @param string $api_key
     * @param string $model
     * @param array  $messages   [['role' => 'user', 'content' => '...']]
     * @param array  $options
     * @return array|WP_Error
     */
    public function generate_completion($provider, $api_key, $model, array $messages, array $options = array()) {
        $endpoint = '';
        $headers = array('Content-Type' => 'application/json');

        switch ($provider) {
            case 'openrouter':
                $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
                $headers['Authorization'] = 'Bearer ' . trim($api_key);
                $headers['HTTP-Referer']  = home_url();
                $headers['X-Title']       = get_bloginfo('name') . ' (GMB Ranker)';
                break;

            case 'groq':
                $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
                $headers['Authorization'] = 'Bearer ' . trim($api_key);
                break;

            case 'ollama':
                $custom_endpoint = !empty($options['ollama_endpoint']) ? rtrim($options['ollama_endpoint'], '/') : 'http://localhost:11434';
                $endpoint = $custom_endpoint . '/api/chat';
                break;

            default:
                return new WP_Error('unsupported_provider', 'Unsupported AI provider: ' . esc_html($provider));
        }

        $payload = array(
            'model'    => $model,
            'messages' => $messages,
        );

        if (!empty($options['temperature'])) {
            $payload['temperature'] = floatval($options['temperature']);
        }
        if (!empty($options['max_tokens'])) {
            $payload['max_tokens'] = intval($options['max_tokens']);
        }

        $response = wp_remote_post($endpoint, array(
            'headers' => $headers,
            'body'    => wp_json_encode($payload),
            'timeout' => 45,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP Error ' . $code;
            return new WP_Error('ai_request_failed', $error_msg, array('status' => $code));
        }

        return is_array($data) ? $data : new WP_Error('malformed_ai_response', 'Malformed JSON response from AI provider.');
    }
}
