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
    const ENDPOINT_NVIDIA     = 'https://integrate.api.nvidia.com/v1/chat/completions';

    /**
     * Validate and sanitize an Ollama local endpoint to prevent SSRF
     *
     * @param string $endpoint_raw
     * @return string|WP_Error
     */
    public static function validate_ollama_endpoint($endpoint_raw) {
        if (empty($endpoint_raw) || !is_string($endpoint_raw)) {
            return new WP_Error('missing_ollama_url', __('Ollama endpoint URL is required.', 'gmb-ranker-seo-automation'));
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
        $request_id = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('gmb_ai_', true);
        $started_at = microtime(true);

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
                if (empty($model)) {
                    return new WP_Error('missing_model', __('OpenRouter model is required.', 'gmb-ranker-seo-automation'));
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
                if (empty($model)) {
                    return new WP_Error('missing_model', __('Groq model is required.', 'gmb-ranker-seo-automation'));
                }
                $endpoint = self::ENDPOINT_GROQ;
                $headers['Authorization'] = 'Bearer ' . $api_key;
                break;

            case 'nvidia':
                if (empty($api_key)) {
                    return new WP_Error('missing_api_key', __('NVIDIA API key is required.', 'gmb-ranker-seo-automation'));
                }
                if (empty($model)) {
                    return new WP_Error('missing_model', __('NVIDIA model is required.', 'gmb-ranker-seo-automation'));
                }
                $endpoint = self::ENDPOINT_NVIDIA;
                $headers['Authorization'] = 'Bearer ' . $api_key;
                $headers['Accept'] = 'application/json';
                break;

            case 'ollama':
                $raw_ollama_url = !empty($options['ollama_endpoint']) ? $options['ollama_endpoint'] : (!empty($options['ollama_url']) ? $options['ollama_url'] : '');
                if (empty($raw_ollama_url)) {
                    return new WP_Error('missing_ollama_url', __('Ollama endpoint URL is required.', 'gmb-ranker-seo-automation'));
                }
                $validated_base = self::validate_ollama_endpoint($raw_ollama_url);

                if (is_wp_error($validated_base)) {
                    return $validated_base;
                }

                $endpoint = $validated_base . '/api/chat';
                // Local Ollama endpoints allow non-SSL HTTP
                if (strpos($validated_base, 'http://') === 0) {
                    $ssl_verify = false;
                }
                if (empty($model)) {
                    return new WP_Error('missing_model', __('Ollama model is required.', 'gmb-ranker-seo-automation'));
                }
                break;

            default:
                return new WP_Error('unsupported_provider', sprintf(__('Unsupported AI provider: %s', 'gmb-ranker-seo-automation'), esc_html($provider)));
        }

        // Build Payload
        $payload = array(
            'model'    => $model,
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

        // Keep failover responsive: local Ollama may need longer, but a remote
        // provider should not block the entire research pipeline for minutes.
        $default_timeout = $provider === 'ollama' ? 45 : 20;
        $timeout = isset($options['timeout']) && is_numeric($options['timeout'])
            ? absint($options['timeout'])
            : $default_timeout;
        $timeout = min(60, max(5, $timeout));

        // Execute bounded HTTP request.
        $max_retries = min(2, max(0, absint($options['max_retries'] ?? 0)));
        $attempt = 0;
        do {
            $response = wp_remote_post($endpoint, array(
                'headers'     => $headers,
                'body'        => $json_body,
                'timeout'     => $timeout,
                'redirection' => 0, // Block redirects to prevent SSRF loops
                'sslverify'   => $ssl_verify,
            ));

            $retryable = false;
            if (is_wp_error($response)) {
                $retryable = true;
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $retryable = ($response_code === 408 || $response_code === 429 || $response_code >= 500);
            }
            if (!$retryable || $attempt >= $max_retries) {
                break;
            }
            $retry_after = !is_wp_error($response) ? absint(wp_remote_retrieve_header($response, 'retry-after')) : 0;
            usleep(min(2000000, max(250000, ($retry_after > 0 ? $retry_after * 1000000 : (250000 * (2 ** $attempt))))));
            $attempt++;
        } while ($attempt <= $max_retries);

        if (is_wp_error($response)) {
            $message = sanitize_text_field($response->get_error_message());
            error_log(sprintf('[GMB AI] request_id=%s provider=%s model=%s category=network_error message=%s', $request_id, $provider, $model, $message));
            return new WP_Error('ai_transport_failed', sprintf(__('AI request failed (%s): %s', 'gmb-ranker-seo-automation'), $request_id, $message), array(
                'category'   => 'network_error',
                'provider'   => $provider,
                'model'      => $model,
                'request_id' => $request_id,
            ));
        }

        $code     = wp_remote_retrieve_response_code($response);
        $body     = wp_remote_retrieve_body($response);
        $decoded  = !empty($body) ? json_decode($body, true) : null;

        if ($code < 200 || $code >= 300) {
            $error_msg = self::extract_provider_error_message($decoded, $code);
            $category = self::classify_http_error($code, $error_msg);
            $duration = round((microtime(true) - $started_at) * 1000);
            error_log(sprintf('[GMB AI] request_id=%s provider=%s model=%s status=%d category=%s duration_ms=%d response_bytes=%d message=%s', $request_id, $provider, $model, $code, $category, $duration, strlen($body), $error_msg));
            return new WP_Error('ai_provider_error', sprintf(__('%s (HTTP %d, request %s)', 'gmb-ranker-seo-automation'), $error_msg, $code, $request_id), array(
                'status'     => $code,
                'category'   => $category,
                'provider'   => $provider,
                'model'      => $model,
                'request_id' => $request_id,
            ));
        }

        if (!is_array($decoded)) {
            return new WP_Error('malformed_ai_response', sprintf(__('AI provider returned malformed JSON (request %s).', 'gmb-ranker-seo-automation'), $request_id), array(
                'category'   => 'invalid_response',
                'provider'   => $provider,
                'model'      => $model,
                'request_id' => $request_id,
            ));
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

        if (!is_string($content) || trim($content) === '') {
            return new WP_Error('empty_ai_response', sprintf(__('AI provider returned no usable content (request %s).', 'gmb-ranker-seo-automation'), $request_id), array(
                'status'     => $code,
                'category'   => 'empty_response',
                'provider'   => $provider,
                'model'      => $model,
                'request_id' => $request_id,
            ));
        }

        return array(
            'provider' => $provider,
            'model'    => $payload['model'],
            'content'  => is_string($content) ? trim($content) : '',
            'raw'      => $decoded,
            'request_id' => $request_id,
        );
    }

    /**
     * Extract a useful provider error without returning secrets or the full body.
     */
    private static function extract_provider_error_message($decoded, $status) {
        $message = '';
        if (is_array($decoded)) {
            $message = $decoded['error']['message'] ?? ($decoded['message'] ?? '');
            $nested = $decoded['error']['metadata']['raw'] ?? ($decoded['error']['details'] ?? '');
            $generic_messages = array('Provider returned error', 'provider returned error', 'Internal Server Error');
            if (is_string($nested) && (empty($message) || in_array(trim($message), $generic_messages, true))) {
                $nested_json = json_decode($nested, true);
                $nested_message = is_array($nested_json) ? ($nested_json['error']['message'] ?? $nested_json['message'] ?? '') : $nested;
                if (!empty($nested_message)) {
                    $message = $nested_message;
                }
            }
        }

        $message = sanitize_text_field((string) $message);
        return $message !== '' ? $message : sprintf(__('AI provider returned HTTP error (%d).', 'gmb-ranker-seo-automation'), intval($status));
    }

    /**
     * Classify common provider failures for logs and structured consumers.
     */
    private static function classify_http_error($status, $message) {
        if (intval($status) === 401) return 'authentication_error';
        if (intval($status) === 403) return 'authorization_error';
        if (intval($status) === 404 || stripos($message, 'model') !== false) return 'invalid_model';
        if (intval($status) === 429) return 'rate_limited';
        if (intval($status) >= 500) return 'provider_error';
        return 'provider_error';
    }
}
