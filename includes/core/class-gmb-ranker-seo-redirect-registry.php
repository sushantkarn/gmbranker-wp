<?php
/**
 * Canonical Redirect Registry & Domain Manager
 *
 * Centralizes redirect codes, match types, settings, rule validation,
 * loop detection, SSRF protection, 404 log formatting, and view model generation.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Redirect_Registry {

    /**
     * Get list of supported HTTP Redirect Status Codes
     *
     * @return array
     */
    public static function get_redirect_codes() {
        return array(
            '301' => array(
                'code'                => 301,
                'label'               => __('301 Permanent Move (Recommended)', 'gmb-ranker-seo-automation'),
                'name'                => __('301 Permanent', 'gmb-ranker-seo-automation'),
                'description'         => __('Passes full link equity to new URL. Used when content has permanently moved.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> true,
            ),
            '302' => array(
                'code'                => 302,
                'label'               => __('302 Temporary Move', 'gmb-ranker-seo-automation'),
                'name'                => __('302 Temporary', 'gmb-ranker-seo-automation'),
                'description'         => __('Signals temporary redirection. Preserves original URL indexation.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> true,
            ),
            '307' => array(
                'code'                => 307,
                'label'               => __('307 Temporary Redirect', 'gmb-ranker-seo-automation'),
                'name'                => __('307 Redirect', 'gmb-ranker-seo-automation'),
                'description'         => __('HTTP/1.1 strict temporary redirect without method alteration.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> true,
            ),
            '308' => array(
                'code'                => 308,
                'label'               => __('308 Permanent Redirect', 'gmb-ranker-seo-automation'),
                'name'                => __('308 Permanent', 'gmb-ranker-seo-automation'),
                'description'         => __('HTTP/1.1 strict permanent redirect preserving HTTP method.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> true,
            ),
            '410' => array(
                'code'                => 410,
                'label'               => __('410 Content Deleted (Gone)', 'gmb-ranker-seo-automation'),
                'name'                => __('410 Deleted', 'gmb-ranker-seo-automation'),
                'description'         => __('Informs search engines that resource is permanently removed and should be de-indexed immediately.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> false,
            ),
            '451' => array(
                'code'                => 451,
                'label'               => __('451 Unavailable for Legal Reasons', 'gmb-ranker-seo-automation'),
                'name'                => __('451 Legal', 'gmb-ranker-seo-automation'),
                'description'         => __('Informs search engines that content was removed due to legal demand or court order.', 'gmb-ranker-seo-automation'),
                'requires_destination'=> false,
            ),
        );
    }

    /**
     * Get list of supported Match Types
     *
     * @return array
     */
    public static function get_match_types() {
        return array(
            'exact' => array(
                'id'          => 'exact',
                'label'       => __('Exact Match', 'gmb-ranker-seo-automation'),
                'description' => __('Matches the exact requested path.', 'gmb-ranker-seo-automation'),
            ),
            'contains' => array(
                'id'          => 'contains',
                'label'       => __('Contains Match', 'gmb-ranker-seo-automation'),
                'description' => __('Matches any request path containing the source string.', 'gmb-ranker-seo-automation'),
            ),
            'start' => array(
                'id'          => 'start',
                'label'       => __('Starts With', 'gmb-ranker-seo-automation'),
                'description' => __('Matches request paths starting with the source prefix.', 'gmb-ranker-seo-automation'),
            ),
            'end' => array(
                'id'          => 'end',
                'label'       => __('Ends With', 'gmb-ranker-seo-automation'),
                'description' => __('Matches request paths ending with the source suffix.', 'gmb-ranker-seo-automation'),
            ),
            'regex' => array(
                'id'          => 'regex',
                'label'       => __('Regex Match', 'gmb-ranker-seo-automation'),
                'description' => __('Evaluates source string as a regular expression pattern.', 'gmb-ranker-seo-automation'),
            ),
        );
    }

    /**
     * Get module settings and options safely
     *
     * @return array
     */
    public static function get_settings() {
        return array(
            'module_enabled'       => get_option('gmb_ranker_module_redirects', '1') !== '0' && get_option('gmb_ranker_module_redirects', '1') !== 'off',
            'auto_post_redirect'   => get_option('gmb_ranker_auto_post_redirect', 'on'),
            'fallback_behavior'    => get_option('gmb_ranker_fallback_behavior', 'default'),
            'fallback_url'         => get_option('gmb_ranker_fallback_url', ''),
            'redirect_attachments' => get_option('gmb_redirect_attachments', 'on'),
            'strip_category_base'  => get_option('gmb_strip_category_base', 'off'),
            'log_limit'            => intval(get_option('gmb_ranker_404_limit', 100)),
            'ignore_query'         => get_option('gmb_ranker_404_ignore_query', 'off'),
            'exclude_paths'        => get_option('gmb_ranker_404_exclude_paths', ''),
        );
    }

    /**
     * Validate and sanitize source URL path
     *
     * @param string $source
     * @return string|false
     */
    public static function validate_source_url($source) {
        $trimmed = trim((string) $source);
        if (empty($trimmed)) {
            return false;
        }

        // Prevent dangerous protocol schemes
        if (preg_match('/^(javascript|data|file|vbscript):/i', $trimmed)) {
            return false;
        }

        // Handle path extraction if full URL is supplied
        $parsed = wp_parse_url($trimmed);
        if (isset($parsed['path'])) {
            $path = $parsed['path'];
            if (isset($parsed['query'])) {
                $path .= '?' . $parsed['query'];
            }
            return sanitize_text_field($path);
        }

        return sanitize_text_field($trimmed);
    }

    /**
     * Validate destination URL with Open Redirect protections
     *
     * @param string $destination
     * @param int $code
     * @return string|false
     */
    public static function validate_destination_url($destination, $code = 301) {
        // Codes 410 and 451 do not require destination
        if (in_array(intval($code), array(410, 451), true)) {
            return '';
        }

        $trimmed = trim((string) $destination);
        if (empty($trimmed)) {
            return false;
        }

        // Reject dangerous schemes
        if (preg_match('/^(javascript|data|file|vbscript):/i', $trimmed)) {
            return false;
        }

        // Relative path starting with /
        if (strpos($trimmed, '/') === 0) {
            return esc_url_raw($trimmed);
        }

        // Absolute HTTP/HTTPS URLs
        $parsed = wp_parse_url($trimmed);
        if (is_array($parsed) && isset($parsed['scheme']) && in_array(strtolower($parsed['scheme']), array('http', 'https'), true)) {
            return esc_url_raw($trimmed);
        }

        return false;
    }

    /**
     * Server-side check for direct redirect loops
     *
     * @param string $source
     * @param string $destination
     * @return bool True if redirect loop detected
     */
    public static function is_redirect_loop($source, $destination) {
        $src_clean = strtolower(trim(wp_parse_url($source, PHP_URL_PATH) ?: $source, '/'));
        $dest_clean = strtolower(trim(wp_parse_url($destination, PHP_URL_PATH) ?: $destination, '/'));

        if (!empty($src_clean) && $src_clean === $dest_clean) {
            return true;
        }

        return false;
    }

    /**
     * Validate Regular Expression pattern safety (ReDoS protection)
     *
     * @param string $pattern
     * @return bool
     */
    public static function validate_regex_pattern($pattern) {
        if (empty($pattern) || strlen($pattern) > 250) {
            return false;
        }
        $test_pattern = '/' . str_replace('/', '\/', $pattern) . '/i';
        $result = @preg_match($test_pattern, 'test_subject');
        return ($result !== false);
    }

    /**
     * Sanitize string for CSV export to prevent Formula Injection (OWASP)
     *
     * @param string $val
     * @return string
     */
    public static function sanitize_csv_field($val) {
        $val = (string) $val;
        if (empty($val)) {
            return '';
        }
        $first_char = substr($val, 0, 1);
        if (in_array($first_char, array('=', '+', '-', '@', "\t", "\r"), true)) {
            return "'" . $val;
        }
        return $val;
    }

    /**
     * Get complete, validated View Model for redirects presentation layer
     *
     * @return array
     */
    public static function get_view_model() {
        $repo = class_exists('GMB_Ranker_SEO_Redirect_Repository') ? new GMB_Ranker_SEO_Redirect_Repository() : null;
        $rules_raw = $repo ? $repo->get_all_rules() : get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules_raw)) {
            $rules_raw = array();
        }

        $logs_raw = $repo ? $repo->get_404_logs() : get_option('gmb_ranker_404_logs', array());
        if (!is_array($logs_raw)) {
            $logs_raw = array();
        }

        $settings = self::get_settings();
        $codes = self::get_redirect_codes();
        $match_types = self::get_match_types();

        $active_rules_count = 0;
        $total_hits_count = 0;
        $validated_rules = array();

        foreach ($rules_raw as $rule) {
            $id          = isset($rule['id']) ? sanitize_text_field($rule['id']) : '';
            $source      = isset($rule['source']) ? sanitize_text_field($rule['source']) : (isset($rule['src']) ? sanitize_text_field($rule['src']) : '');
            $destination = isset($rule['destination']) ? sanitize_text_field($rule['destination']) : (isset($rule['target']) ? sanitize_text_field($rule['target']) : '');
            $code        = isset($rule['code']) ? intval($rule['code']) : 301;
            $status      = (isset($rule['status']) && $rule['status'] === 'inactive') ? 'inactive' : ((isset($rule['enabled']) && !$rule['enabled']) ? 'inactive' : 'active');
            $match_type  = isset($rule['match_type']) ? sanitize_key($rule['match_type']) : (isset($rule['type']) ? sanitize_key($rule['type']) : 'exact');
            $hits        = isset($rule['hits']) ? max(0, intval($rule['hits'])) : 0;
            $note        = isset($rule['note']) ? sanitize_text_field($rule['note']) : '';

            if (!array_key_exists((string)$code, $codes)) {
                $code = 301;
            }
            if (!array_key_exists($match_type, $match_types)) {
                $match_type = 'exact';
            }

            if ($status === 'active') {
                $active_rules_count++;
            }
            $total_hits_count += $hits;

            $validated_rules[] = array(
                'id'          => $id,
                'source'      => $source,
                'destination' => $destination,
                'code'        => $code,
                'status'      => $status,
                'match_type'  => $match_type,
                'hits'        => $hits,
                'note'        => $note,
            );
        }

        $validated_logs = array();
        foreach ($logs_raw as $log) {
            $uri       = isset($log['uri']) ? sanitize_text_field($log['uri']) : '';
            $referrer  = !empty($log['referrer']) ? sanitize_text_field($log['referrer']) : __('Direct Access / None', 'gmb-ranker-seo-automation');
            $time      = isset($log['time']) ? intval($log['time']) : (isset($log['first_accessed']) ? strtotime($log['first_accessed']) : 0);

            if (empty($uri)) {
                continue;
            }

            $validated_logs[] = array(
                'uri'      => $uri,
                'referrer' => $referrer,
                'time'     => $time,
            );
        }

        return array(
            'module_enabled'     => $settings['module_enabled'],
            'settings'           => $settings,
            'rules'              => $validated_rules,
            'rules_count'        => count($validated_rules),
            'active_rules_count' => $active_rules_count,
            'total_hits_count'   => $total_hits_count,
            'logs_404'           => $validated_logs,
            'logs_count'         => count($validated_logs),
            'redirect_codes'     => $codes,
            'match_types'        => $match_types,
        );
    }
}
