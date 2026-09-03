<?php
/**
 * AI Provider API Transport Client for GMB Ranker SEO Automation
 *
 * Serves as a low-level, secure, and protocol-normalized transport boundary for
 * OpenRouter, Groq, and Ollama AI providers. Handles SSRF validation, header sanitization,
 * payload verification, HTTP request isolation, and provider error normalization.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_AI_Client {

    /**
     * Canonical hosted endpoints
     */
    const ENDPOINT_OPENROUTER = 'https://openrouter.ai/api/v1/chat/completions';
    const ENDPOINT_GROQ       = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * Validate and sanitize an Ollama local endpoint to prevent SSRF
     *
     * @param string $endpoint_raw
     * @return string|WP_Error
     */
    public static function validate_ollama_endpoint($endpoint_raw) {
        if (empty($endpoint_raw) || !is_string($endpoint_raw)) {
            return 'http://localhost:11434';
        }

        $url = trim($endpoint_raw);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_ollama_url', __('Invalid Ollama endpoint URL structure.', 'gmb-ranker-seo-automation'));
        }

        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return new WP_Error('invalid_ollama_components', __('Ollama endpoint missing valid scheme or hostname.', 'gmb-ranker-seo-automation'));
        }

        // Scheme must be http or https
        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, array('http', 'https'), true)) {
            return new WP_Error('invalid_ollama_scheme', __('Ollama endpoint scheme must be http or https.', 'gmb-ranker-seo-automation'));
        }

        // Host validation (Localhost, loopback, or explicitly configured host)
        $host = strtolower($parts['host']);
        
        // Prevent cloud metadata endpoint SSRF (169.254.169.254, etc.)
        $blacklisted_ips = array(
            '169.254.169.254',
            '169.254.169.253',
            'fd00::',
        );

        if (in_array($host, $blacklisted_ips, true)) {
            return new WP_Error('ssrf_blocked', __('Endpoint targets restricted cloud metadata infrastructure.', 'gmb-ranker-seo-automation'));
        }

        // Port default
        $port = !empty($parts['port']) ? ':' . intval($parts['port']) : ($scheme === 'https' ? '' : ':11434');
        $path = !empty($parts['path']) ? rtrim($parts['path'], '/') : '';

        return $scheme . '://' . $host . $port . $path;
    }

    /**
     * Sanitize header strings to prevent HTTP header injection
     *
     * @param string $val
     * @return string
     */
    protected static function sanitize_header_value($val) {
        if (!is_string($val)) {
            return '';
        }
        // Remove CR, LF, and null bytes
        return trim(str_replace(array("\r", "\n", "\0"), '', $val));
    }

    /**
     * Generate completion with standardized request/response structure
     *
     * @param string $provider   openrouter | groq | ollama
     * @param string $api_key
     * @param string $model
     * @param array  $messages   [['role' => 'user', 'content' => '...']]
     * @param array  $options
     * @return array|WP_Error Normalized completion array or WP_Error
     */
    public function generate_completion($provider, $api_key, $model, array $messages, array $options = array()) {
        $provider = strtolower(trim((string)$provider));
        $api_key  = self::sanitize_header_value($api_key);
        $model    = sanitize_text_field(wp_unslash((string)$model));

        // Validate Messages
        if (empty($messages) || !is_array($messages)) {
            return new WP_Error('invalid_messages', __('AI completion requires non-empty messages array.', 'gmb-ranker-seo-automation'));
        }

        $clean_messages = array();
        $allowed_roles  = array('system', 'user', 'assistant', 'developer', 'tool');

        foreach ($messages as $msg) {
            if (!is_array($msg) || empty($msg['role']) || !isset($msg['content'])) {
                continue;
            }
            $role    = strtolower(trim((string)$msg['role']));
            $role    = in_array($role, $allowed_roles, true) ? $role : 'user';
            $content = is_string($msg['content']) ? $msg['content'] : wp_json_encode($msg['content']);

            $clean_messages[] = array(
                'role'    => $role,
                'content' => mb_convert_encoding($content, 'UTF-8', 'UTF-8'),
            );
        }

        if (empty($clean_messages)) {
            return new WP_Error('invalid_messages_format', __('No valid messages remaining after normalization.', 'gmb-ranker-seo-automation'));
        }

        $endpoint  = '';
        $headers   = array('Content-Type' => 'application/json');
        $ssl_verify = true;

        switch ($provider) {
            case 'openrouter':
                if (empty($api_key)) {
                    return new WP_Error('missing_api_key', __('OpenRouter API key is required.', 'gmb-ranker-seo-automation'));
                }
                $endpoint = self::ENDPOINT_OPENROUTER;
                $headers['Authorization'] = 'Bearer ' . $api_key;
                $headers['HTTP-Referer']  = esc_url_raw(home_url());
                $headers['X-Title']       = esc_attr(get_bloginfo('name') . ' (GMB Ranker)');
                break;

            case 'groq':
                if (empty($api_key)) {
                    return new WP_Error('missing_api_key', __('Groq API key is required.', 'gmb-ranker-seo-automation'));
                }
                $endpoint = self::ENDPOINT_GROQ;
                $headers['Authorization'] = 'Bearer ' . $api_key;
                break;

            case 'ollama':
                $raw_ollama_url = !empty($options['ollama_endpoint']) ? $options['ollama_endpoint'] : (!empty($options['ollama_url']) ? $options['ollama_url'] : 'http://localhost:11434');
                $validated_base = self::validate_ollama_endpoint($raw_ollama_url);

                if (is_wp_error($validated_base)) {
                    return $validated_base;
                }

                $endpoint = $validated_base . '/api/chat';
                // Local Ollama endpoints allow non-SSL HTTP
                if (strpos($validated_base, 'http://') === 0) {
                    $ssl_verify = false;
                }
                break;

            default:
                return new WP_Error('unsupported_provider', sprintf(__('Unsupported AI provider: %s', 'gmb-ranker-seo-automation'), esc_html($provider)));
        }

        // Build Payload
        $payload = array(
            'model'    => !empty($model) ? $model : ($provider === 'ollama' ? 'llama3' : 'meta-llama/llama-3.1-8b-instruct:free'),
            'messages' => $clean_messages,
        );

        if ($provider === 'ollama') {
            $payload['stream'] = false;
            $options_block = array();
            if (isset($options['temperature']) && is_numeric($options['temperature'])) {
                $options_block['temperature'] = min(2.0, max(0.0, floatval($options['temperature'])));
            }
            if (!empty($options_block)) {
                $payload['options'] = $options_block;
            }
        } else {
            if (isset($options['temperature']) && is_numeric($options['temperature'])) {
                $payload['temperature'] = min(2.0, max(0.0, floatval($options['temperature'])));
            }
            if (!empty($options['max_tokens']) && is_numeric($options['max_tokens'])) {
                $payload['max_tokens'] = min(32768, max(1, intval($options['max_tokens'])));
            }
        }

        $json_body = wp_json_encode($payload);
        if ($json_body === false) {
            return new WP_Error('json_encode_failed', __('Failed to serialize AI completion request payload to JSON.', 'gmb-ranker-seo-automation'));
        }

        // Execute Bounded HTTP Request
        $response = wp_remote_post($endpoint, array(
            'headers'     => $headers,
            'body'        => $json_body,
            'timeout'     => 45,
            'redirection' => 0, // Block redirects to prevent SSRF loops
            'sslverify'   => $ssl_verify,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('ai_transport_failed', $response->get_error_message());
        }

        $code     = wp_remote_retrieve_response_code($response);
        $body     = wp_remote_retrieve_body($response);
        $decoded  = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = isset($decoded['error']['message']) 
                ? sanitize_text_field($decoded['error']['message']) 
                : sprintf(__('AI Provider HTTP Error (%d)', 'gmb-ranker-seo-automation'), $code);
            return new WP_Error('ai_provider_error', $error_msg, array('status' => $code));
        }

        if (!is_array($decoded)) {
            return new WP_Error('malformed_ai_response', __('Malformed JSON response received from AI provider.', 'gmb-ranker-seo-automation'));
        }

        // Normalize Extracted Content
        $content = '';
        if ($provider === 'ollama') {
            if (isset($decoded['message']['content'])) {
                $content = $decoded['message']['content'];
            }
        } else {
            if (isset($decoded['choices'][0]['message']['content'])) {
                $content = $decoded['choices'][0]['message']['content'];
            }
        }

        return array(
            'provider' => $provider,
            'model'    => $payload['model'],
            'content'  => is_string($content) ? trim($content) : '',
            'raw'      => $decoded,
        );
    }
}
