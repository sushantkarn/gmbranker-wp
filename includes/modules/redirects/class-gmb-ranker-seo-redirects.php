<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Redirects {
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_template_redirect'));
        add_filter('category_link', array($this, 'strip_category_base_link'));
        add_filter('request', array($this, 'strip_category_base_request'));
        add_action('post_updated', array($this, 'handle_post_updated'), 10, 3);
    }

    private function is_third_party_redirection_active() {
        if (class_exists('RankMath\Helper') && \RankMath\Helper::is_module_active('redirections')) {
            return true;
        }
        return false;
    }

    private function is_rank_math_strip_category_base_active() {
        if (class_exists('RankMath\Helper') && \RankMath\Helper::get_settings('general.strip_category_base')) {
            return true;
        }
        return false;
    }

    public function strip_category_base_link($link) {
        if ($this->is_rank_math_strip_category_base_active()) {
            return $link;
        }
        if (get_option('gmb_strip_category_base', 'off') === 'on') {
            $category_base = get_option('category_base', 'category');
            if (empty($category_base)) {
                $category_base = 'category';
            }
            $category_base = '/' . trim($category_base, '/') . '/';
            $link = str_replace($category_base, '/', $link);
        }
        return $link;
    }

    public function strip_category_base_request($query_vars) {
        if ($this->is_rank_math_strip_category_base_active()) {
            return $query_vars;
        }
        if (get_option('gmb_strip_category_base', 'off') !== 'on') {
            return $query_vars;
        }
        if (isset($query_vars['category_name'])) {
            return $query_vars;
        }
        if (isset($query_vars['attachment']) || isset($query_vars['name']) || isset($query_vars['pagename'])) {
            $slug = isset($query_vars['attachment']) ? $query_vars['attachment'] : (isset($query_vars['name']) ? $query_vars['name'] : $query_vars['pagename']);
            $term = get_term_by('slug', $slug, 'category');
            if ($term) {
                $query_vars['category_name'] = $slug;
                unset($query_vars['attachment']);
                unset($query_vars['name']);
                unset($query_vars['pagename']);
            }
        }
        return $query_vars;
    }

    public function handle_post_updated($post_ID, $post_after, $post_before) {
        if (!is_object($post_before) || !is_object($post_after)) {
            return;
        }

        if ($post_before->post_name === $post_after->post_name) {
            return;
        }

        // Only handle published posts/pages
        if ($post_after->post_status !== 'publish') {
            return;
        }

        if (get_option('gmb_ranker_auto_post_redirect', 'off') !== 'on') {
            return;
        }

        $old_post = clone $post_after;
        $old_post->post_name = $post_before->post_name;
        $old_permalink = get_permalink($old_post);
        $new_permalink = get_permalink($post_after);

        if ($old_permalink && $new_permalink && $old_permalink !== $new_permalink) {
            $old_path = wp_parse_url($old_permalink, PHP_URL_PATH);
            $new_path = wp_parse_url($new_permalink, PHP_URL_PATH);

            if ($old_path && $new_path && $old_path !== $new_path) {
                $rules = get_option('gmb_ranker_redirects_rules', array());
                if (!is_array($rules)) {
                    $rules = array();
                }

                $exists = false;
                foreach ($rules as $r) {
                    if (trim($r['source']) === $old_path) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $rules[] = array(
                        'id' => uniqid('wp_'),
                        'source' => $old_path,
                        'destination' => $new_path,
                        'code' => 301,
                        'match_type' => 'exact',
                        'status' => 'active',
                        'hits' => 0,
                        'last_accessed' => 0
                    );
                    update_option('gmb_ranker_redirects_rules', $rules);
                }
            }
        }
    }

    public function handle_template_redirect() {
        if ($this->is_third_party_redirection_active()) {
            return;
        }

        if (is_attachment() && get_option('gmb_redirect_attachments', 'off') === 'on') {
            global $post;
            if (!empty($post->post_parent)) {
                $parent_url = get_permalink($post->post_parent);
                if ($parent_url) {
                    wp_safe_redirect($parent_url, 301);
                    exit;
                }
            } else {
                $orphan_fallback = get_option('gmb_redirect_orphan_attachments', '');
                if (!empty($orphan_fallback)) {
                    wp_safe_redirect(esc_url_raw($orphan_fallback), 301);
                    exit;
                }
            }
            wp_safe_redirect(home_url(), 301);
            exit;
        }

        if (is_singular()) {
            $post_id = get_the_ID();
            if ($post_id) {
                $custom_redir = get_post_meta($post_id, '_gmb_ranker_redirect_url', true);
                if (!empty($custom_redir)) {
                    $code = intval(get_post_meta($post_id, '_gmb_ranker_redirect_code', true) ?: 301);
                    if ($code === 410) {
                        status_header(410);
                        nocache_headers();
                        include(get_query_template('404'));
                        exit;
                    }
                    wp_safe_redirect(esc_url_raw($custom_redir), $code);
                    exit;
                }
            }
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $request_path = wp_parse_url($request_uri, PHP_URL_PATH);
        if (!$request_path) {
            $request_path = $request_uri;
        }
        $request_path = '/' . ltrim(trim($request_path), '/');
        
        $rules = get_option('gmb_ranker_redirects_rules', array());
        if (!is_array($rules)) {
            $rules = array();
        }

        $simple_redirects = get_option('gmb_ranker_redirects', array());
        if (is_array($simple_redirects)) {
            foreach ($simple_redirects as $src => $dest) {
                $exists = false;
                foreach ($rules as $r) {
                    if (trim($r['source']) === $src) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $rules[] = array(
                        'id' => 'simple_' . md5($src),
                        'source' => $src,
                        'destination' => $dest,
                        'code' => 301,
                        'match_type' => 'exact',
                        'status' => 'active',
                    );
                }
            }
        }
        
        foreach ($rules as $rule) {
            if (empty($rule['source']) || empty($rule['destination'])) {
                continue;
            }
            
            // Skip if deactivated
            if (isset($rule['status']) && $rule['status'] === 'inactive') {
                continue;
            }
            
            $source_path = wp_parse_url($rule['source'], PHP_URL_PATH);
            if (!$source_path) {
                $source_path = $rule['source'];
            }
            $source = '/' . ltrim(trim($source_path), '/');
            
            $destination = trim($rule['destination']);
            $code = isset($rule['code']) ? intval($rule['code']) : 301;
            
            $is_match = false;
            $match_type = isset($rule['match_type']) ? $rule['match_type'] : 'exact';
            
            if ($match_type === 'exact') {
                $is_match = ($request_path === $source || rtrim($request_path, '/') === rtrim($source, '/'));
            } elseif ($match_type === 'contains') {
                $is_match = (strpos($request_path, $source_path) !== false);
            } elseif ($match_type === 'start') {
                $is_match = (strpos($request_path, $source) === 0);
            } elseif ($match_type === 'end') {
                $is_match = (substr($request_path, -strlen($source)) === $source);
            } elseif ($match_type === 'regex') {
                $is_match = @preg_match('/' . str_replace('/', '\/', $source_path) . '/i', $request_path);
            }
            
            if (!$is_match && strpos($source, '*') !== false) {
                $is_match = $this->match_wildcard($request_path, $source);
            }
            
            if ($is_match) {
                $rules_to_update = get_option('gmb_ranker_redirects_rules', array());
                if (!is_array($rules_to_update)) {
                    $rules_to_update = array();
                }
                foreach ($rules_to_update as &$r) {
                    if (isset($r['id']) && $r['id'] === $rule['id']) {
                        $r['hits'] = (isset($r['hits']) ? intval($r['hits']) : 0) + 1;
                        $r['last_accessed'] = time();
                        break;
                    }
                }
                update_option('gmb_ranker_redirects_rules', $rules_to_update);
                
                if (strpos($destination, 'http://') === 0 || strpos($destination, 'https://') === 0) {
                    wp_redirect($destination, $code);
                } else {
                    $target_url = '/' . ltrim($destination, '/');
                    wp_redirect(home_url($target_url), $code);
                }
                exit;
            }
        }

        if (is_404()) {
            $fallback_behavior = get_option('gmb_ranker_fallback_behavior', 'default');
            $fallback_url = get_option('gmb_ranker_fallback_url', '');

            $ignore_query = get_option('gmb_ranker_404_ignore_query', 'off') === 'on';
            $exclude_paths = get_option('gmb_ranker_404_exclude_paths', '');
            
            $log_uri = $request_uri;
            if ($ignore_query) {
                $log_uri = strtok($request_uri, '?');
            }

            $should_log = true;
            if (!empty($exclude_paths)) {
                $keywords = array_filter(array_map('trim', explode(',', strtolower($exclude_paths))));
                $lower_uri = strtolower($log_uri);
                foreach ($keywords as $kw) {
                    if (strpos($lower_uri, $kw) !== false) {
                        $should_log = false;
                        break;
                    }
                }
            }

            if ($should_log) {
                $logs = get_option('gmb_ranker_404_logs', array());
                if (!is_array($logs)) {
                    $logs = array();
                }
                
                $new_log = array(
                    'uri'        => $log_uri,
                    'ip'         => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                    'referrer'   => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
                    'time'       => time()
                );

                array_unshift($logs, $new_log);
                
                $log_limit = intval(get_option('gmb_ranker_404_limit', 100));
                if ($log_limit > 0 && count($logs) > $log_limit) {
                    $logs = array_slice($logs, 0, $log_limit);
                }
                
                update_option('gmb_ranker_404_logs', $logs);
            }

            if ($fallback_behavior === 'homepage') {
                wp_safe_redirect(home_url(), 302);
                exit;
            } elseif ($fallback_behavior === 'custom' && !empty($fallback_url)) {
                wp_safe_redirect(esc_url_raw($fallback_url), 302);
                exit;
            }
        }
    }

    private function match_wildcard($uri, $pattern) {
        $pattern = str_replace(array('\*', '\?'), array('.*', '.'), preg_quote($pattern, '/'));
        return preg_match('/^' . $pattern . '$/i', $uri);
    }
}
