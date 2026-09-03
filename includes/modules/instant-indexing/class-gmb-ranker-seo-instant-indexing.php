<?php
if (!defined('ABSPATH')) exit;

/**
 * GMB Ranker SEO Instant Indexing Engine
 * Full implementation of Google Indexing API & IndexNow Protocol (Bing, Yandex, Seznam, Naver)
 * Derived from production Rank Math / Fast Indexing architecture with zero bloated external dependencies.
 */
class GMB_Ranker_SEO_Instant_Indexing {

    const THROTTLE_LIMIT = 5;

    public function __construct() {
        // Automatic publish & status hooks
        add_action('transition_post_status', array($this, 'handle_post_transition'), 10, 3);
        add_action('wp_trash_post', array($this, 'handle_post_trash'), 10, 1);
        add_action('init', array($this, 'handle_key_request'), 1);
        add_action('parse_request', array($this, 'handle_key_request'), 1);

        // Row actions and bulk actions in post/page list tables
        add_filter('post_row_actions', array($this, 'add_row_actions'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_row_actions'), 10, 2);

        $public_types = get_post_types(array('public' => true), 'names');
        foreach ($public_types as $pt) {
            add_filter("bulk_actions-edit-{$pt}", array($this, 'register_bulk_actions'));
            add_filter("handle_bulk_actions-edit-{$pt}", array($this, 'handle_bulk_action'), 10, 3);
        }

        // AJAX handlers for console, limits, key reset & history
        add_action('wp_ajax_gmb_instant_indexing_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_gmb_instant_index_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_gmb_instant_indexing_get_limits', array($this, 'ajax_get_limits'));
        add_action('wp_ajax_gmb_instant_indexing_reset_key', array($this, 'ajax_reset_key'));
        add_action('wp_ajax_gmb_instant_indexing_clear_history', array($this, 'ajax_clear_history'));
    }

    // ==========================================
    // INDEXNOW PROTOCOL HANDLERS
    // ==========================================

    public static function get_indexnow_key() {
        $key = get_option('gmb_ranker_indexnow_key', '');
        if (empty($key)) {
            $key = md5(wp_generate_password(24, false) . site_url());
            update_option('gmb_ranker_indexnow_key', $key);
        }
        self::ensure_physical_key_file($key);
        return $key;
    }

    public static function ensure_physical_key_file($key) {
        // Physical ABSPATH file creation removed per WordPress.org Guidelines.
        // Handled dynamically via handle_key_request virtual endpoint.
        return;
    }

    public static function get_key_location() {
        $key = self::get_indexnow_key();
        return home_url('/' . $key . '.txt');
    }

    public function handle_key_request() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $path = wp_parse_url($request_uri, PHP_URL_PATH) ?: '';
        $key = self::get_indexnow_key();
        
        if (preg_match('/^\/indexnow-key\.html$/i', $path) || 
            preg_match('/^\/gmb_ranker_instant_index_key\.txt$/i', $path) || 
            preg_match('/^\/' . preg_quote($key, '/') . '\.txt$/i', $path)) {
            
            header('Content-Type: text/plain; charset=utf-8');
            if (preg_match('/^\/gmb_ranker_instant_index_key\.txt$/i', $path)) {
                echo 'gmb_ranker_instant_index_key';
            } else {
                echo esc_html($key);
            }
            exit;
        }
    }

    /**
     * Validate and sanitize URL for API submission (SSRF Protection)
     *
     * @param string $url
     * @return string|false
     */
    public static function validate_url_for_submission($url) {
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
        if (in_array($host, array('localhost', '127.0.0.1', '::1', '0.0.0.0'), true)) {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }
        return $url;
    }

    public static function submit_to_indexnow($urls, $is_manual = true) {
        $urls = (array)$urls;
        $validated_urls = array();
        foreach ($urls as $u) {
            $v = self::validate_url_for_submission($u);
            if ($v) {
                $validated_urls[] = $v;
            }
        }
        $urls = array_filter(array_unique($validated_urls));
        if (empty($urls)) {
            return array('success' => false, 'error' => __('No valid HTTP/HTTPS URLs provided.', 'gmb-ranker-seo-automation'));
        }

        $host         = wp_parse_url(home_url(), PHP_URL_HOST);
        $key          = self::get_indexnow_key();
        $key_location = self::get_key_location();
        
        $client = new GMB_Ranker_SEO_IndexNow_Client();
        $res    = $client->submit_to_indexnow($host, $key, $urls, $key_location);

        if (is_wp_error($res)) {
            $message = $res->get_error_message();
            self::log_indexnow_submission($urls, $is_manual, 'WP_Error: ' . $message);
            return array('success' => false, 'code' => 500, 'message' => $message);
        }

        $status_code = isset($res['status_code']) ? $res['status_code'] : 500;
        $message     = isset($res['message']) ? $res['message'] : 'Unknown response';
        $is_success  = !empty($res['success']);

        if ($is_success) {
            self::log_indexnow_submission($urls, $is_manual, 'Success: ' . $message);
        } else {
            self::log_indexnow_submission($urls, $is_manual, 'Failed: ' . $message);
        }

        self::log_request('bing_submit', count($urls));

        return array(
            'success' => $is_success,
            'code'    => $status_code,
            'message' => $message,
            'raw'     => isset($res['body']) ? $res['body'] : '',
        );
    }

    public static function log_submission($urls, $is_manual, $status_text, $engine = 'indexnow') {
        $logs = get_option('gmb_ranker_indexnow_log', array());
        if (!is_array($logs)) {
            $logs = array();
        }

        foreach ((array)$urls as $u) {
            $logs[] = array(
                'time'              => time(),
                'url'               => $u,
                'manual_submission' => $is_manual ? 1 : 0,
                'status'            => $status_text,
                'engine'            => $engine
            );
        }

        if (count($logs) > 200) {
            $logs = array_slice($logs, -200);
        }
        update_option('gmb_ranker_indexnow_log', $logs);
    }

    private static function log_indexnow_submission($urls, $is_manual, $status_text) {
        self::log_submission($urls, $is_manual, $status_text, 'indexnow');
    }

    // ==========================================
    // GOOGLE INDEXING API (NATIVE OAUTH2 / JWT)
    // ==========================================

    public static function get_google_access_token() {
        $cached_token = get_transient('gmb_google_indexing_token');
        if (!empty($cached_token)) {
            return $cached_token;
        }

        $json_key_str = get_option('gmb_ranker_google_json_key', '');
        if (empty($json_key_str)) {
            return new WP_Error('missing_key', 'Google Service Account JSON key is not configured.');
        }

        $json_key = json_decode($json_key_str, true);
        if (!is_array($json_key) || empty($json_key['client_email']) || empty($json_key['private_key'])) {
            return new WP_Error('invalid_json', 'Google Service Account JSON key is invalid or corrupted.');
        }

        // Build JWT (RFC 7519)
        $header = array(
            'alg' => 'RS256',
            'typ' => 'JWT'
        );

        $now = time();
        $payload = array(
            'iss'   => $json_key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now
        );

        $base64_header = self::base64url_encode(wp_json_encode($header));
        $base64_payload = self::base64url_encode(wp_json_encode($payload));
        $data_to_sign = $base64_header . '.' . $base64_payload;

        $private_key = $json_key['private_key'];
        $signature = '';
        $success = openssl_sign($data_to_sign, $signature, $private_key, OPENSSL_ALGO_SHA256);

        if (!$success) {
            return new WP_Error('openssl_sign_failed', 'OpenSSL failed to sign Google Service Account JWT with private key.');
        }

        $jwt = $data_to_sign . '.' . self::base64url_encode($signature);

        // Exchange JWT for Bearer token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body'    => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ),
            'timeout' => 20
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['access_token'])) {
            $err_msg = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'Failed to retrieve access token from Google OAuth2.');
            return new WP_Error('oauth_error', $err_msg);
        }

        $access_token = $data['access_token'];
        $expires_in = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;
        set_transient('gmb_google_indexing_token', $access_token, max(300, $expires_in - 300));

        return $access_token;
    }

    public static function submit_to_google($urls, $action = 'update') {
        $urls = (array)$urls;
        $validated_urls = array();
        foreach ($urls as $u) {
            $v = self::validate_url_for_submission($u);
            if ($v) {
                $validated_urls[] = $v;
            }
        }
        $urls = array_filter(array_unique($validated_urls));
        if (empty($urls)) {
            return array('success' => false, 'error' => __('No valid HTTP/HTTPS URLs provided.', 'gmb-ranker-seo-automation'));
        }

        $token = self::get_google_access_token();
        if (is_wp_error($token)) {
            $err = $token->get_error_message();
            self::log_submission($urls, true, 'Google Error: ' . $err, 'google');
            return array('success' => false, 'error' => $err);
        }

        $results = array();
        $overall_success = true;

        foreach ($urls as $url) {
            if ($action === 'getstatus') {
                $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications/metadata?url=' . rawurlencode($url);
                $res = wp_remote_get($endpoint, array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/json'
                    ),
                    'timeout' => 20
                ));
            } else {
                $type = ($action === 'remove') ? 'URL_DELETED' : 'URL_UPDATED';
                $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
                $res = wp_remote_post($endpoint, array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => wp_json_encode(array(
                        'url'  => $url,
                        'type' => $type
                    )),
                    'timeout' => 20
                ));
            }

            if (is_wp_error($res)) {
                $overall_success = false;
                $err = $res->get_error_message();
                $results[$url] = array('success' => false, 'error' => $err);
                self::log_submission(array($url), true, 'Google Error: ' . $err, 'google');
            } else {
                $code = wp_remote_retrieve_response_code($res);
                $body = wp_remote_retrieve_body($res);
                $data = json_decode($body, true);
                if ($code >= 200 && $code < 300) {
                    $results[$url] = array('success' => true, 'code' => $code, 'data' => $data);
                    self::log_submission(array($url), true, '200 OK (Google ' . strtoupper($action) . ')', 'google');
                } else {
                    $overall_success = false;
                    $err_msg = isset($data['error']['message']) ? $data['error']['message'] : ('HTTP ' . $code);
                    if ($code === 403 && (stripos($err_msg, 'ownership') !== false || stripos($err_msg, 'permission') !== false || stripos($err_msg, 'forbidden') !== false || stripos($err_msg, 'verify') !== false)) {
                        $err_msg .= ' — (Setup Required: Please add your Google Service Account email as an OWNER in Google Search Console for this domain property)';
                    }
                    $results[$url] = array('success' => false, 'code' => $code, 'error' => $err_msg, 'data' => $data);
                    self::log_submission(array($url), true, 'HTTP ' . $code . ' (Google): ' . $err_msg, 'google');
                }
            }

            self::log_request($action, 1);
        }

        $first_err = '';
        if (!$overall_success && !empty($results)) {
            foreach ($results as $r) {
                if (!empty($r['error'])) {
                    $first_err = $r['error'];
                    break;
                }
            }
        }

        return array(
            'success' => $overall_success,
            'error'   => $first_err,
            'message' => $first_err,
            'results' => $results,
            'single'  => (count($results) === 1) ? reset($results) : null
        );
    }

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // ==========================================
    // QUOTA & LIMITS TRACKING
    // ==========================================

    public static function log_request($type, $number = 1) {
        $requests = get_option('gmb_instant_indexing_requests', array(
            'update'      => array(),
            'remove'      => array(),
            'getstatus'   => array(),
            'bing_submit' => array()
        ));

        if (!isset($requests[$type])) {
            $requests[$type] = array();
        }

        $now = time();
        for ($i = 0; $i < $number; $i++) {
            $requests[$type][] = $now;
        }

        if (count($requests[$type]) > 600) {
            $requests[$type] = array_slice($requests[$type], -600);
        }

        update_option('gmb_instant_indexing_requests', $requests);
    }

    public static function get_limits() {
        $limit_publish_per_day = 200;
        $limit_per_minute = 380;
        $limit_meta_per_minute = 180;
        $limit_indexnow_per_day = 10000;

        $requests = get_option('gmb_instant_indexing_requests', array(
            'update'      => array(),
            'remove'      => array(),
            'getstatus'   => array(),
            'bing_submit' => array()
        ));

        $one_day_ago = strtotime('-1 day');
        $one_min_ago = strtotime('-1 minute');

        $publish_1day = 0;
        $all_1min = 0;
        $meta_1min = 0;
        $indexnow_1day = 0;

        $updates = isset($requests['update']) ? $requests['update'] : array();
        foreach ($updates as $t) {
            if ($t > $one_day_ago) $publish_1day++;
            if ($t > $one_min_ago) $all_1min++;
        }

        $removes = isset($requests['remove']) ? $requests['remove'] : array();
        foreach ($removes as $t) {
            if ($t > $one_day_ago) $publish_1day++;
            if ($t > $one_min_ago) $all_1min++;
        }

        $getstatus = isset($requests['getstatus']) ? $requests['getstatus'] : array();
        foreach ($getstatus as $t) {
            if ($t > $one_min_ago) {
                $all_1min++;
                $meta_1min++;
            }
        }

        $indexnow = isset($requests['bing_submit']) ? $requests['bing_submit'] : array();
        foreach ($indexnow as $t) {
            if ($t > $one_day_ago) $indexnow_1day++;
        }

        return array(
            'publishperday'     => max(0, $limit_publish_per_day - $publish_1day),
            'publishperday_max' => $limit_publish_per_day,
            'permin'            => max(0, $limit_per_minute - $all_1min),
            'permin_max'        => $limit_per_minute,
            'metapermin'        => max(0, $limit_meta_per_minute - $meta_1min),
            'metapermin_max'    => $limit_meta_per_minute,
            'indexnowperday'    => max(0, $limit_indexnow_per_day - $indexnow_1day),
            'indexnowperday_max'=> $limit_indexnow_per_day,
        );
    }

    // ==========================================
    // AUTO SUBMISSION HOOKS
    // ==========================================

    public function handle_post_transition($new_status, $old_status, $post) {
        if ($new_status !== 'publish' || !is_object($post)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
            return;
        }

        // Check if noindex
        $robots = get_post_meta($post->ID, '_gmb_ranker_seo_robots', true);
        if ($robots && strpos($robots, 'noindex') !== false) {
            return;
        }

        $url = get_permalink($post->ID);
        if (!$url) {
            return;
        }

        // Google Auto-submit check
        $google_post_types = get_option('gmb_ranker_google_post_types', array('post', 'page'));
        if (is_array($google_post_types) && in_array($post->post_type, $google_post_types, true)) {
            $has_json = get_option('gmb_ranker_google_json_key', '');
            if (!empty($has_json)) {
                self::submit_to_google(array($url), 'update');
            }
        }

        // IndexNow Auto-submit check
        $indexnow_post_types = get_option('gmb_ranker_indexnow_post_types', array('post', 'page'));
        if (is_array($indexnow_post_types) && in_array($post->post_type, $indexnow_post_types, true)) {
            self::submit_to_indexnow(array($url), false);
        }
    }

    public function handle_post_trash($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        $google_post_types = get_option('gmb_ranker_google_post_types', array('post', 'page'));
        if (is_array($google_post_types) && in_array($post->post_type, $google_post_types, true)) {
            $url = get_permalink($post->ID);
            if ($url) {
                self::submit_to_google(array($url), 'remove');
            }
        }
    }

    // ==========================================
    // ROW & BULK ACTIONS
    // ==========================================

    public function add_row_actions($actions, $post) {
        if (!current_user_can('manage_options') || $post->post_status !== 'publish') {
            return $actions;
        }

        $url = rawurlencode(get_permalink($post->ID));
        $nonce = wp_create_nonce('gmb_instant_index_action');

        $has_json = get_option('gmb_ranker_google_json_key', '');
        if (!empty($has_json)) {
            $actions['gmb_google_update'] = sprintf(
                '<a href="%s" class="gmb-row-action--primary"><strong>%s</strong></a>',
                admin_url("admin.php?page=gmb-ranker-instant-indexing&tab=console&action=update&url={$url}&_wpnonce={$nonce}"),
                esc_html__('Google: Update URL', 'gmb-ranker-seo-automation')
            );
            $actions['gmb_google_status'] = sprintf(
                '<a href="%s" class="gmb-row-action--muted">%s</a>',
                admin_url("admin.php?page=gmb-ranker-instant-indexing&tab=console&action=getstatus&url={$url}&_wpnonce={$nonce}"),
                esc_html__('Google: Status', 'gmb-ranker-seo-automation')
            );
        }

        $actions['gmb_indexnow_submit'] = sprintf(
            '<a href="%s" class="gmb-row-action--success"><strong>%s</strong></a>',
            admin_url("admin.php?page=gmb-ranker-instant-indexing&tab=console&action=bing_submit&url={$url}&_wpnonce={$nonce}"),
            esc_html__('IndexNow: Submit', 'gmb-ranker-seo-automation')
        );

        return $actions;
    }

    public function register_bulk_actions($bulk_actions) {
        $has_json = get_option('gmb_ranker_google_json_key', '');
        if (!empty($has_json)) {
            $bulk_actions['gmb_bulk_google_update'] = __('Instant Indexing: Google Update', 'gmb-ranker-seo-automation');
        }
        $bulk_actions['gmb_bulk_indexnow_submit'] = __('Instant Indexing: IndexNow Submit', 'gmb-ranker-seo-automation');
        return $bulk_actions;
    }

    public function handle_bulk_action($redirect_to, $doaction, $post_ids) {
        if (!in_array($doaction, array('gmb_bulk_google_update', 'gmb_bulk_indexnow_submit'), true)) {
            return $redirect_to;
        }

        if (!current_user_can('edit_posts')) {
            return $redirect_to;
        }

        $urls = array();
        foreach ($post_ids as $pid) {
            $pid = (int) $pid;
            if ($pid > 0 && current_user_can('edit_post', $pid) && get_post_status($pid) === 'publish') {
                $permalink = get_permalink($pid);
                if ($permalink) {
                    $urls[] = $permalink;
                }
            }
        }

        if (!empty($urls)) {
            if ($doaction === 'gmb_bulk_google_update') {
                self::submit_to_google($urls, 'update');
            } elseif ($doaction === 'gmb_bulk_indexnow_submit') {
                self::submit_to_indexnow($urls, true);
            }
        }

        return add_query_arg('gmb_indexed_count', count($urls), $redirect_to);
    }

    // ==========================================
    // AJAX ENDPOINTS
    // ==========================================

    private function verify_indexing_nonce() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized user.'), 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : (isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '');
        $valid = wp_verify_nonce($nonce, 'gmb_instant_indexing_nonce') ||
                 wp_verify_nonce($nonce, 'gmb_admin_ajax_nonce') ||
                 wp_verify_nonce($nonce, 'gmb_seo_save_nonce') ||
                 wp_verify_nonce($nonce, 'gmb_instant_index_action');
        if (!$valid) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'), 403);
        }
    }

    public function ajax_submit() {
        $this->verify_indexing_nonce();

        $urls_str = isset($_POST['urls']) ? sanitize_textarea_field(wp_unslash($_POST['urls'])) : '';
        $action = isset($_POST['api_action']) ? sanitize_key(wp_unslash($_POST['api_action'])) : 'update';

        $raw_urls = preg_split('/[\r\n]+/', trim($urls_str));
        $urls = array_filter(array_map('trim', $raw_urls));

        if (empty($urls)) {
            wp_send_json_error(array('message' => 'Please enter at least one URL.'));
        }

        if ($action === 'bing_submit') {
            $res = self::submit_to_indexnow($urls, true);
        } else {
            $res = self::submit_to_google($urls, $action);
        }

        $res['limits'] = self::get_limits();

        if (!empty($res['success'])) {
            wp_send_json_success($res);
        } else {
            wp_send_json_error($res);
        }
    }

    public function ajax_get_limits() {
        $this->verify_indexing_nonce();
        wp_send_json_success(self::get_limits());
    }

    public function ajax_reset_key() {
        $this->verify_indexing_nonce();

        $old_key = get_option('gmb_ranker_indexnow_key', '');
        if (!empty($old_key) && defined('ABSPATH')) {
            $old_file = ABSPATH . $old_key . '.txt';
            if (file_exists($old_file) && wp_is_writable($old_file)) {
                @wp_delete_file($old_file);
            }
        }

        $new_key = md5(wp_generate_password(24, false) . site_url() . microtime());
        update_option('gmb_ranker_indexnow_key', $new_key);
        self::ensure_physical_key_file($new_key);

        wp_send_json_success(array(
            'key'          => $new_key,
            'key_location' => self::get_key_location()
        ));
    }

    public function ajax_clear_history() {
        $this->verify_indexing_nonce();
        delete_option('gmb_ranker_indexnow_log');
        wp_send_json_success(array('message' => 'Submission history cleared successfully.'));
    }
}
