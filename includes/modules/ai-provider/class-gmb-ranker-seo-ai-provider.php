<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_AI_Provider {
    public function __construct() {
    }

    public static function get_active_provider() {
        return get_option('gmb_ai_provider', get_option('gmb_ai_active_provider', 'openrouter'));
    }

    public static function generate_ai_response($messages, $temperature = 0.7) {
        $provider = self::get_active_provider();

        if ($provider === 'groq') {
            return self::dispatch_groq($messages, $temperature);
        } elseif ($provider === 'ollama') {
            return self::dispatch_ollama($messages, $temperature);
        } else {
            return self::dispatch_openrouter($messages, $temperature);
        }
    }

    private static function dispatch_openrouter($messages, $temperature) {
        $api_key = get_option('gmb_ai_openrouter_key', '');
        $model = get_option('gmb_ai_openrouter_model', 'meta-llama/llama-3.1-8b-instruct:free');
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        if (empty($api_key)) {
            return new WP_Error('missing_key', 'OpenRouter API Key is missing.');
        }

        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => site_url(),
            'X-Title'       => 'GMB Ranker SEO WordPress Plugin'
        );

        $body = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature
        );

        return self::make_post_request($url, $headers, $body);
    }

    private static function dispatch_groq($messages, $temperature) {
        $api_key = get_option('gmb_ai_groq_key', '');
        $model = get_option('gmb_ai_groq_model', 'llama-3.1-8b-instant');
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        if (empty($api_key)) {
            return new WP_Error('missing_key', 'Groq API Key is missing.');
        }

        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        );

        $body = array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature
        );

        return self::make_post_request($url, $headers, $body);
    }

    private static function dispatch_ollama($messages, $temperature) {
        $base_url = get_option('gmb_ai_ollama_url', 'http://localhost:11434');
        $model = get_option('gmb_ai_ollama_model', 'llama3');
        $url = rtrim($base_url, '/') . '/api/chat';

        $headers = array(
            'Content-Type' => 'application/json'
        );

        // Ollama native API structure
        $body = array(
            'model'    => $model,
            'messages' => $messages,
            'options'  => array(
                'temperature' => $temperature
            ),
            'stream'   => false
        );

        $response = wp_remote_post($url, array(
            'headers'   => $headers,
            'body'      => wp_json_encode($body),
            'timeout'   => 30,
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $res_body = wp_remote_retrieve_body($response);
        $data = json_decode($res_body, true);

        if ($code !== 200 || !isset($data['message']['content'])) {
            return new WP_Error('ollama_error', 'Ollama returned non-200 code: ' . $code);
        }

        return trim($data['message']['content']);
    }

    private static function make_post_request($url, $headers, $body) {
        $response = wp_remote_post($url, array(
            'headers'   => $headers,
            'body'      => wp_json_encode($body),
            'timeout'   => 30,
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $res_body = wp_remote_retrieve_body($response);
        $data = json_decode($res_body, true);

        if ($code !== 200 || !isset($data['choices'][0]['message']['content'])) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP Error ' . $code;
            return new WP_Error('ai_http_error', $msg);
        }

        return trim($data['choices'][0]['message']['content']);
    }
}
