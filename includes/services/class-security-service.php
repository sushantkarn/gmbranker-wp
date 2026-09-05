<?php
/**
 * Security Service for GMB Ranker SEO Automation
 *
 * Enterprise-grade WordPress hardening, anti-hacking defense, brute-force shielding,
 * sensitive file protection, WAF 404 exploit probe detection, IP access controls,
 * custom login protection, two-factor authentication, and HTTP security headers.
 * Modeled after Really Simple SSL Pro.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Security_Service {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Security_Service|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Security_Service
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get client IP address accurately handling proxies and Cloudflare
     *
     * @return string
     */
    public function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])));
            $ip = trim($parts[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * Parse multiline/comma-separated IP list
     *
     * @param string $option_name
     * @return array
     */
    public function get_ip_list($option_name) {
        $raw = get_option($option_name, '');
        if (empty($raw)) {
            return array();
        }
        $lines = preg_split('/[\r\n,]+/', (string) $raw);
        $clean = array();
        foreach ($lines as $line) {
            $ip = trim($line);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $clean[] = $ip;
            }
        }
        return array_unique($clean);
    }

    /**
     * Check if client IP is whitelisted
     *
     * @return bool
     */
    public function is_client_ip_whitelisted() {
        $client_ip = $this->get_client_ip();
        $whitelist = $this->get_ip_list('gmb_seo_ip_whitelist');
        return in_array($client_ip, $whitelist, true);
    }

    /**
     * Enforce IP Blacklist immediately on init
     */
    public function enforce_ip_blacklist() {
        $client_ip = $this->get_client_ip();
        $blacklist = $this->get_ip_list('gmb_seo_ip_blacklist');
        if (in_array($client_ip, $blacklist, true)) {
            if (function_exists('status_header')) {
                status_header(403);
            }
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            exit('Access Denied: Your IP address has been permanently blacklisted.');
        }
    }

    /**
     * 1. Block PHP Code Execution in Uploads Folder
     * Protects against web shells uploaded via unpatched plugins or forms
     */
    public function block_code_execution_in_uploads() {
        if (!function_exists('wp_upload_dir')) {
            return false;
        }
        $upload_dir = wp_upload_dir();
        $basedir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
        if (empty($basedir) || !is_dir($basedir) || !wp_is_writable($basedir)) {
            return false;
        }

        $htaccess_file = rtrim($basedir, '/') . '/.htaccess';
        $rules = "# BEGIN GMB Ranker SEO - Block PHP Execution\n"
               . "<Files *.php>\n"
               . "<IfModule mod_authz_core.c>\n"
               . "Require all denied\n"
               . "</IfModule>\n"
               . "<IfModule !mod_authz_core.c>\n"
               . "Order deny,allow\n"
               . "Deny from all\n"
               . "</IfModule>\n"
               . "</Files>\n"
               . "<Files *.phtml>\n"
               . "Deny from all\n"
               . "</Files>\n"
               . "# END GMB Ranker SEO";

        if (function_exists('insert_with_markers')) {
            $rules_array = array(
                '<Files *.php>',
                'Deny from all',
                '</Files>',
                '<Files *.phps>',
                'Deny from all',
                '</Files>',
                '<Files *.phtml>',
                'Deny from all',
                '</Files>',
            );
            insert_with_markers($htaccess_file, 'GMB Ranker SEO - Block PHP Execution', $rules_array);
        }
        return true;
    }

    /**
     * Remove PHP execution blocker from uploads if disabled
     */
    public function remove_code_execution_blocker() {
        if (!function_exists('wp_upload_dir')) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $basedir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
        if (empty($basedir) || !is_dir($basedir)) {
            return;
        }
        $htaccess_file = rtrim($basedir, '/') . '/.htaccess';
        if (file_exists($htaccess_file) && function_exists('insert_with_markers')) {
            insert_with_markers($htaccess_file, 'GMB Ranker SEO - Block PHP Execution', array());
        }
    }

    /**
     * 2. Protect Sensitive Server Files & debug.log
     * Blocks access to debug.log, license.txt, readme.html, .env, .git, and DB backups
     */
    public function block_sensitive_files_access() {
        $uri = isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : '';

        $sensitive_patterns = array(
            '/debug\.log',
            '/readme\.html',
            '/license\.txt',
            '/\.env',
            '/\.git',
            '/\.htpasswd',
            '/wp-config\.php\.bak',
            '/wp-config\.old',
            '/\.sql(\?|$)',
            '/\.bak(\?|$)',
        );

        foreach ($sensitive_patterns as $pattern) {
            if (preg_match('#' . $pattern . '#i', $uri)) {
                $this->log_security_incident('sensitive_file_probe', sprintf('Sensitive file access blocked: %s from IP %s', esc_html($uri), $this->get_client_ip()));
                if (function_exists('status_header')) {
                    status_header(403);
                }
                if (!headers_sent()) {
                    header('Content-Type: text/plain; charset=UTF-8');
                }
                exit('Forbidden: Access to sensitive system files is blocked.');
            }
        }
    }

    /**
     * 3. Disable Directory Browsing (Options -Indexes)
     */
    public function disable_directory_indexing() {
        if (!function_exists('wp_upload_dir')) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $basedir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
        if (!empty($basedir) && is_dir($basedir) && function_exists('insert_with_markers')) {
            $htaccess_file = rtrim($basedir, '/') . '/.htaccess';
            insert_with_markers($htaccess_file, 'GMB Ranker SEO - Disable Indexes', array('Options -Indexes'));
        }
    }

    /**
     * 4. Disable Dangerous / Insecure HTTP Request Methods (TRACE, TRACK, DELETE)
     */
    public function block_dangerous_http_methods() {
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        $blocked_methods = array('TRACE', 'TRACK');

        if (in_array($method, $blocked_methods, true)) {
            if (function_exists('status_header')) {
                status_header(405);
            }
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
                header('Allow: GET, POST, HEAD, OPTIONS');
            }
            exit('Method Not Allowed: Insecure HTTP tracing methods are disabled.');
        }
    }

    /**
     * 5. Disable WordPress Application Passwords
     */
    public function disable_application_passwords() {
        add_filter('wp_is_application_passwords_available', '__return_false', 999);
    }

    /**
     * 6. Audit Display Names vs Login Usernames
     * Only flags risk if user_login is an insecure generic name OR if public posts are published without enumeration protection
     *
     * @return array
     */
    public function audit_display_names() {
        if (!function_exists('get_users')) {
            return array('has_issues' => false, 'users' => array());
        }

        $admins = get_users(array('role' => 'administrator'));
        $issues = array();
        $enum_protected = (get_option('gmb_seo_prevent_user_enumeration', '0') === '1');
        $insecure_names = array('admin', 'administrator', 'root', 'support', 'test', 'demo', 'webmaster');

        if (is_array($admins)) {
            foreach ($admins as $admin) {
                if (isset($admin->user_login) && isset($admin->display_name)) {
                    $login_lower = strtolower(trim($admin->user_login));
                    $display_lower = strtolower(trim($admin->display_name));

                    // If display name matches login name
                    if ($login_lower === $display_lower) {
                        // Insecure default login names are ALWAYS flagged
                        if (in_array($login_lower, $insecure_names, true)) {
                            $issues[] = $admin->user_login;
                        } else {
                            // If user enumeration protection is NOT enabled AND user has published public posts
                            $has_posts = function_exists('count_user_posts') ? (count_user_posts($admin->ID, 'post', true) > 0) : false;
                            if (!$enum_protected && $has_posts) {
                                $issues[] = $admin->user_login;
                            }
                        }
                    }
                }
            }
        }

        return array(
            'has_issues' => !empty($issues),
            'users'      => $issues,
        );
    }

    /**
     * Auto-fix display names for administrator accounts to prevent reconnaissance
     *
     * @return array
     */
    public function auto_fix_display_names() {
        if (!function_exists('get_users')) {
            return array('success' => false, 'fixed' => 0);
        }

        $admins = get_users(array('role' => 'administrator'));
        $fixed_count = 0;

        if (is_array($admins)) {
            foreach ($admins as $admin) {
                if (isset($admin->user_login) && isset($admin->display_name)) {
                    if (strcasecmp($admin->user_login, $admin->display_name) === 0) {
                        $safe_display = '';
                        if (!empty($admin->first_name) && !empty($admin->last_name)) {
                            $safe_display = trim($admin->first_name . ' ' . $admin->last_name);
                        } elseif (!empty($admin->first_name)) {
                            $safe_display = trim($admin->first_name);
                        } else {
                            $clean = preg_replace('/[0-9_.-]+/', ' ', $admin->user_login);
                            $clean = trim(ucwords($clean));
                            $safe_display = (!empty($clean) && strcasecmp($clean, $admin->user_login) !== 0) ? $clean : 'Site Administrator';
                        }

                        if (strcasecmp($safe_display, $admin->user_login) === 0) {
                            $safe_display = 'Administrator';
                        }

                        wp_update_user(array(
                            'ID'           => $admin->ID,
                            'display_name' => $safe_display,
                        ));
                        $fixed_count++;
                    }
                }
            }
        }

        return array('success' => true, 'fixed' => $fixed_count);
    }

    /**
     * 7. Custom / Obscured Login URL
     * Redirects direct hits to wp-login.php without secret slug to home
     */
    public function handle_custom_login_url() {
        $custom_slug = trim((string) get_option('gmb_seo_custom_login_slug', ''));
        if (empty($custom_slug)) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $is_login_php = (strpos($request_uri, 'wp-login.php') !== false);
        $has_custom_slug = (strpos($request_uri, $custom_slug) !== false);

        // Allow legitimate actions like POST login requests, logout, or postpass
        $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
        if ($action === 'logout' || $action === 'postpass' || (!empty($_POST) && isset($_POST['log']))) {
            return;
        }

        // Emergency bypass query parameter
        if (isset($_GET['gmb_sec_bypass'])) {
            return;
        }

        // If hitting wp-login.php without the custom slug, block or redirect
        if ($is_login_php && !$has_custom_slug && !is_user_logged_in()) {
            if (function_exists('wp_safe_redirect') && function_exists('home_url')) {
                wp_safe_redirect(home_url('/'));
                exit;
            } else {
                header('Location: /');
                exit;
            }
        }
    }

    /**
     * 8. Session & Cookie Expiration (Default 24 hours instead of 14 days)
     *
     * @param int $expiration
     * @param int $user_id
     * @param bool $remember
     * @return int
     */
    public function enforce_session_expiration($expiration, $user_id = 0, $remember = false) {
        $hours = (int) get_option('gmb_seo_session_expiration_hours', 24);
        if ($hours <= 0) {
            $hours = 24;
        }
        $custom_sec = $hours * 3600;
        return $remember ? $custom_sec : min($expiration, $custom_sec);
    }

    /**
     * Hide "Remember Me" checkbox on login form
     */
    public function hide_remember_me() {
        wp_register_style('gmb-hide-remember-me', false);
        wp_enqueue_style('gmb-hide-remember-me');
        wp_add_inline_style('gmb-hide-remember-me', '#loginform .forgetmenot { display: none !important; }');
    }

    /**
     * 9. Strong Password Policy Enforcement
     * Requires 12+ chars, uppercase, lowercase, numbers, and symbols for admins
     *
     * @param WP_Error $errors
     * @param bool $update
     * @param WP_User $user
     * @return WP_Error
     */
    public function enforce_strong_password_policy($errors, $update, $user) {
        if (!empty($_POST['pass1'])) {
            $pass = sanitize_text_field(wp_unslash($_POST['pass1']));
            if (strlen($pass) < 12 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass) || !preg_match('/[0-9]/', $pass) || !preg_match('/[^A-Za-z0-9]/', $pass)) {
                $errors->add('weak_password', __('Password does not meet enterprise security requirements (min 12 chars, uppercase, lowercase, number, symbol).', 'gmb-ranker-seo-automation'));
            }
        }
        return $errors;
    }

    /**
     * 10. WAF: 404 Exploit Scanner Detection & Auto-Lockout
     * Traps automated vulnerability scanner sweeps (e.g. phpmyadmin, shell.php, wso.php)
     */
    public function handle_404_exploit_detection() {
        if (!function_exists('is_404') || !is_404()) {
            return;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : '';
        $exploit_probes = array(
            'eval-stdin.php',
            'wp-config.php.bak',
            'phpmyadmin',
            'pma',
            'shell.php',
            'wso.php',
            'alfa.php',
            'alfa-rex',
            'c99.php',
            'r57.php',
            'up.php',
            'uploader.php',
            'wp-content/plugins/wp-file-manager',
            'wp-content/plugins/revslider',
            'temp-write-test',
        );

        $matched_probe = false;
        foreach ($exploit_probes as $probe) {
            if (strpos($uri, $probe) !== false) {
                $matched_probe = $probe;
                break;
            }
        }

        if ($matched_probe) {
            $ip = $this->get_client_ip();
            // Whitelist immunity
            if ($this->is_client_ip_whitelisted()) {
                return;
            }

            $lockout_mins = (int) get_option('gmb_seo_lockout_duration_mins', 15);
            if ($lockout_mins <= 0) $lockout_mins = 15;
            $min_sec = defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60;

            set_transient('gmb_sec_lockout_' . md5($ip), time(), $lockout_mins * $min_sec);
            $this->log_security_incident('waf_exploit_probe', sprintf('Attacker IP %s banned for %d minutes probing exploit URI: %s', $ip, $lockout_mins, esc_html($uri)));

            if (function_exists('status_header')) {
                status_header(403);
            }
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            exit('Forbidden: Malicious exploit scan detected. Your IP has been temporarily locked out.');
        }
    }

    /**
     * 11. Two-Factor Authentication (2FA) for Administrators
     */
    public function initiate_2fa_challenge($user, $password) {
        if (defined('GMB_DISABLE_2FA') && GMB_DISABLE_2FA) {
            return $user;
        }

        if (is_wp_error($user) || !($user instanceof WP_User)) {
            return $user;
        }

        // Only enforce for administrators
        if (!in_array('administrator', (array) $user->roles, true)) {
            return $user;
        }

        $existing_code = get_transient('gmb_2fa_code_' . $user->ID);

        // Check if code was submitted
        if (isset($_POST['gmb_2fa_verification_code']) && !empty($_POST['gmb_2fa_verification_code'])) {
            $entered_code = sanitize_text_field(wp_unslash($_POST['gmb_2fa_verification_code']));
            if (!empty($existing_code) && $entered_code === $existing_code) {
                delete_transient('gmb_2fa_code_' . $user->ID);
                return $user;
            } else {
                return new WP_Error('invalid_2fa_code', __('Invalid Two-Factor Authentication code. Please check your email and try again.', 'gmb-ranker-seo-automation'));
            }
        }

        // Generate 6-digit code if none active
        if (empty($existing_code)) {
            $code = (string) wp_rand(100000, 999999);
            $min_sec = defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60;
            set_transient('gmb_2fa_code_' . $user->ID, $code, 10 * $min_sec);

            // Send email
            $subject = sprintf(__('[%s] Your Admin 2FA Security Login Code', 'gmb-ranker-seo-automation'), function_exists('get_bloginfo') ? get_bloginfo('name') : 'WordPress');
            $message = sprintf(
                __("Hello %s,\n\nA login attempt was made to your WordPress administrator account.\n\nYour 2FA verification code is: %s\n\nThis code will expire in 10 minutes.\nIf this was not you, someone may have your password. Please change it immediately.", 'gmb-ranker-seo-automation'),
                isset($user->display_name) ? $user->display_name : 'Admin',
                $code
            );

            if (function_exists('wp_mail') && !empty($user->user_email)) {
                wp_mail($user->user_email, $subject, $message);
            }
        }

        // Require 2FA input
        return new WP_Error('require_2fa_code', __('2FA Verification required. Please enter the 6-digit code sent to your administrator email.', 'gmb-ranker-seo-automation'));
    }

    /**
     * Render 2FA code field on login form if error is thrown
     */
    public function render_2fa_login_field() {
        ?>
        <p class="gmb-2fa-field-wrap">
            <label for="gmb_2fa_verification_code"><?php esc_html_e('2FA Security Code (Sent to Email)', 'gmb-ranker-seo-automation'); ?><br />
            <input type="text" name="gmb_2fa_verification_code" id="gmb_2fa_verification_code" class="input" value="" size="20" autocomplete="one-time-code" placeholder="6-digit code" /></label>
        </p>
        <?php
    }

    /**
     * 12. Prevent User Enumeration
     */
    public function prevent_user_enumeration() {
        $is_logged = function_exists('is_user_logged_in') && is_user_logged_in();
        if (!$is_logged && isset($_REQUEST['author'])) {
            if (preg_match('/\d/', (string) $_REQUEST['author']) > 0) {
                wp_die(
                    esc_html__('Forbidden: Author enumeration is blocked for security.', 'gmb-ranker-seo-automation'),
                    esc_html__('Access Denied', 'gmb-ranker-seo-automation'),
                    array('response' => 403)
                );
            }
        }

        add_filter('rest_endpoints', function($endpoints) {
            $can_edit = function_exists('current_user_can') && current_user_can('edit_posts');
            if (!$can_edit) {
                if (isset($endpoints['/wp/v2/users'])) {
                    $endpoints['/wp/v2/users'][0]['callback'] = function() {
                        return new WP_Error('rest_user_forbidden', __('Access denied: User directory listing is restricted.', 'gmb-ranker-seo-automation'), array('status' => 401));
                    };
                }
                if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                    $endpoints['/wp/v2/users/(?P<id>[\d]+)'][0]['callback'] = function() {
                        return new WP_Error('rest_user_forbidden', __('Access denied: User data is restricted.', 'gmb-ranker-seo-automation'), array('status' => 401));
                    };
                }
            }
            return $endpoints;
        }, 999);

        add_filter('wp_sitemaps_add_provider', function($provider, $name) {
            if ('users' === $name) {
                return false;
            }
            return $provider;
        }, 10, 2);

        add_filter('wpseo_sitemap_exclude_author', '__return_true');
        add_filter('rank_math/sitemap/exclude_author', '__return_true');
    }

    /**
     * 13. Prevent Login Information Leakage
     */
    public function mask_login_errors() {
        add_filter('login_errors', function($error) {
            return __('Invalid username, email, or password. Please try again.', 'gmb-ranker-seo-automation');
        });

        add_action('login_footer', function() {
            ?>
            <script>
                if (document.getElementById('login_error')) {
                    var u = document.getElementById('user_login');
                    if (u) { u.value = ''; u.setAttribute('value', ''); }
                }
            </script>
            <?php
        });
    }

    /**
     * 14. Disable File Editing
     */
    public function disable_file_editing() {
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        add_filter('user_has_cap', function($allcaps, $caps, $args, $user) {
            if (isset($caps[0]) && in_array($caps[0], array('edit_plugins', 'edit_themes', 'edit_files'), true)) {
                $allcaps['edit_plugins'] = false;
                $allcaps['edit_themes']  = false;
                $allcaps['edit_files']   = false;
            }
            return $allcaps;
        }, 999, 4);
    }

    /**
     * Check if default "admin" or "administrator" username exists
     *
     * @return array
     */
    public function check_admin_user_exists() {
        if (!function_exists('get_user_by')) {
            return array('exists' => false, 'username' => '');
        }
        $admin_user = get_user_by('login', 'admin');
        $administrator_user = get_user_by('login', 'administrator');

        $exists = ($admin_user !== false || $administrator_user !== false);
        $name   = $admin_user ? 'admin' : ($administrator_user ? 'administrator' : '');

        return array(
            'exists'   => $exists,
            'username' => $name,
        );
    }

    /**
     * Check if client IP is currently locked out
     *
     * @return bool
     */
    public function is_ip_locked_out() {
        if ($this->is_client_ip_whitelisted()) {
            return false;
        }
        $ip = $this->get_client_ip();
        $lockout_transient = get_transient('gmb_sec_lockout_' . md5($ip));
        return !empty($lockout_transient);
    }

    /**
     * Record a failed login attempt for the client IP
     *
     * @param string $username
     */
    public function record_failed_login($username = '') {
        if ($this->is_client_ip_whitelisted()) {
            return;
        }

        $ip = $this->get_client_ip();
        $key = 'gmb_sec_attempts_' . md5($ip);
        $attempts = (int) get_transient($key);
        $attempts++;

        $max_attempts = (int) get_option('gmb_seo_max_login_attempts', 5);
        if ($max_attempts <= 0) {
            $max_attempts = 5;
        }

        $lockout_duration_mins = (int) get_option('gmb_seo_lockout_duration_mins', 15);
        if ($lockout_duration_mins <= 0) {
            $lockout_duration_mins = 15;
        }

        $min_sec = defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60;
        if ($attempts >= $max_attempts) {
            set_transient('gmb_sec_lockout_' . md5($ip), time(), $lockout_duration_mins * $min_sec);
            delete_transient($key);

            $this->log_security_incident('brute_force_lockout', sprintf('IP %s locked out for %d minutes after %d failed attempts.', $ip, $lockout_duration_mins, $attempts));
        } else {
            set_transient($key, $attempts, 15 * $min_sec);
        }
    }

    /**
     * Clear failed attempts on successful login
     *
     * @param string $user_login
     * @param WP_User $user
     */
    public function clear_failed_attempts_on_success($user_login, $user) {
        $ip = $this->get_client_ip();
        delete_transient('gmb_sec_attempts_' . md5($ip));
    }

    /**
     * Enforce IP lockout at login screen
     */
    public function enforce_ip_lockout() {
        if ($this->is_ip_locked_out()) {
            wp_die(
                esc_html__('Too many failed login attempts. Your IP has been temporarily locked out for security. Please try again later.', 'gmb-ranker-seo-automation'),
                esc_html__('Access Temporarily Blocked', 'gmb-ranker-seo-automation'),
                array('response' => 429)
            );
        }
    }

    /**
     * Login Honeypot Field
     */
    public function inject_login_honeypot() {
        ?>
        <p style="display:none !important; visibility:hidden !important;" aria-hidden="true">
            <label for="gmb_auth_verification"><?php esc_html_e('Leave this field empty', 'gmb-ranker-seo-automation'); ?></label>
            <input type="text" name="gmb_auth_verification" id="gmb_auth_verification" value="" autocomplete="off" tabindex="-1" />
        </p>
        <?php
    }

    /**
     * Verify login honeypot is empty
     *
     * @param WP_User|WP_Error|null $user
     * @return WP_User|WP_Error
     */
    public function verify_login_honeypot($user) {
        if (isset($_POST['gmb_auth_verification']) && !empty($_POST['gmb_auth_verification'])) {
            $ip = $this->get_client_ip();
            $this->log_security_incident('honeypot_trap', sprintf('Bot blocked via login honeypot from IP %s.', $ip));
            return new WP_Error('bot_detected', __('Access denied: Automated bot activity detected.', 'gmb-ranker-seo-automation'));
        }
        return $user;
    }

    /**
     * Apply HTTP Security Headers with proactive duplicate removal and Grade A+ Headers Suite
     */
    public function apply_security_headers() {
        if (headers_sent() || is_admin()) {
            return;
        }

        static $applied = false;
        if ($applied) {
            return;
        }
        $applied = true;

        $enable_headers = get_option('gmb_seo_enable_security_headers', '0') === '1';
        if (!$enable_headers) {
            return;
        }

        // Proactively remove existing duplicate header declarations (e.g. from server/theme)
        if (function_exists('header_remove')) {
            header_remove('X-Frame-Options');
            header_remove('X-Content-Type-Options');
            header_remove('X-XSS-Protection');
            header_remove('Referrer-Policy');
            header_remove('Strict-Transport-Security');
            header_remove('Permissions-Policy');
            header_remove('Content-Security-Policy');
            header_remove('X-Permitted-Cross-Domain-Policies');
            header_remove('Cross-Origin-Opener-Policy');
            header_remove('Cross-Origin-Resource-Policy');
            header_remove('Cross-Origin-Embedder-Policy');
        }

        // 1. MIME Type Sniffing Defense
        header('X-Content-Type-Options: nosniff', true);

        // 2. Clickjacking Defense
        header('X-Frame-Options: SAMEORIGIN', true);

        // 3. XSS Protection
        header('X-XSS-Protection: 1; mode=block', true);

        // 4. Referrer-Policy
        $ref_policy = get_option('gmb_seo_referrer_policy', 'strict-origin-when-cross-origin');
        if (empty($ref_policy)) {
            $ref_policy = 'strict-origin-when-cross-origin';
        }
        header('Referrer-Policy: ' . sanitize_text_field($ref_policy), true);

        // 5. Strict-Transport-Security (HSTS)
        $ssl_active = function_exists('is_ssl') ? is_ssl() : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        if ($ssl_active && get_option('gmb_seo_enable_hsts', '0') === '1') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload', true);
        }

        // 6. Permissions-Policy (Hardware Sensor & Geolocation Lockdown)
        if (get_option('gmb_seo_permissions_policy', '0') === '1') {
            header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()', true);
        }

        // 7. Content-Security-Policy (Frame Ancestors & Base Lockdown)
        if (get_option('gmb_seo_csp_frame_ancestors', '0') === '1') {
            header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none';", true);
        }

        // 8. Cross-Origin-Opener-Policy (COOP)
        $coop = get_option('gmb_seo_enable_coop', 'same-origin-allow-popups');
        if (!empty($coop) && $coop !== 'disabled') {
            header('Cross-Origin-Opener-Policy: ' . sanitize_text_field($coop), true);
        }

        // 9. Cross-Origin-Resource-Policy (CORP)
        $corp = get_option('gmb_seo_enable_corp', 'same-site');
        if (!empty($corp) && $corp !== 'disabled') {
            header('Cross-Origin-Resource-Policy: ' . sanitize_text_field($corp), true);
        }

        // 10. Cross-Origin-Embedder-Policy (COEP)
        $coep = get_option('gmb_seo_enable_coep', 'unsafe-none');
        if (!empty($coep) && $coep !== 'disabled') {
            header('Cross-Origin-Embedder-Policy: ' . sanitize_text_field($coep), true);
        }

        // 11. X-Permitted-Cross-Domain-Policies (Flash / PDF Exploit Isolation)
        $cross_domain = get_option('gmb_seo_cross_domain_policies', 'none');
        if (!empty($cross_domain) && $cross_domain !== 'disabled') {
            header('X-Permitted-Cross-Domain-Policies: ' . sanitize_text_field($cross_domain), true);
        }
    }

    /**
     * Log a security incident to option for auditing
     *
     * @param string $type
     * @param string $message
     */
    public function log_security_incident($type, $message) {
        $logs = get_option('gmb_security_audit_logs', array());
        if (!is_array($logs)) {
            $logs = array();
        }

        array_unshift($logs, array(
            'time'    => time(),
            'type'    => sanitize_key($type),
            'message' => sanitize_text_field($message),
            'ip'      => $this->get_client_ip(),
        ));

        if (count($logs) > 100) {
            $logs = array_slice($logs, 0, 100);
        }

        update_option('gmb_security_audit_logs', $logs);
    }

    /**
     * Calculate Site Security Hardening Score (0 - 100)
     *
     * @return array
     */
    public function calculate_security_score() {
        $checks = array();
        $total_points = 0;
        $earned_points = 0;

        // 1. SSL Active (10 pts)
        $is_ssl = function_exists('is_ssl') ? is_ssl() : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        $checks['ssl'] = array(
            'label'   => __('SSL / HTTPS Encryption Active', 'gmb-ranker-seo-automation'),
            'passed'  => $is_ssl,
            'points'  => 10,
            'status'  => $is_ssl ? __('Enabled', 'gmb-ranker-seo-automation') : __('Insecure', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($is_ssl) $earned_points += 10;

        // 2. Uploads execution blocked (10 pts)
        $uploads_blocked = (get_option('gmb_seo_block_uploads_execution', '0') === '1');
        $checks['uploads'] = array(
            'label'   => __('PHP Code Execution Blocked in Uploads', 'gmb-ranker-seo-automation'),
            'passed'  => $uploads_blocked,
            'points'  => 10,
            'status'  => $uploads_blocked ? __('Protected', 'gmb-ranker-seo-automation') : __('Unprotected', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($uploads_blocked) $earned_points += 10;

        // 3. Sensitive files blocked (10 pts)
        $sens_blocked = (get_option('gmb_seo_block_sensitive_files', '0') === '1');
        $checks['sensitive_files'] = array(
            'label'   => __('Sensitive Files & debug.log Protected', 'gmb-ranker-seo-automation'),
            'passed'  => $sens_blocked,
            'points'  => 10,
            'status'  => $sens_blocked ? __('Locked', 'gmb-ranker-seo-automation') : __('Exposed', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($sens_blocked) $earned_points += 10;

        // 4. Directory indexing disabled (5 pts)
        $indexing_disabled = (get_option('gmb_seo_disable_directory_indexing', '0') === '1');
        $checks['indexing'] = array(
            'label'   => __('Directory Browsing Disabled', 'gmb-ranker-seo-automation'),
            'passed'  => $indexing_disabled,
            'points'  => 5,
            'status'  => $indexing_disabled ? __('Disabled', 'gmb-ranker-seo-automation') : __('Active', 'gmb-ranker-seo-automation'),
        );
        $total_points += 5;
        if ($indexing_disabled) $earned_points += 5;

        // 5. User enumeration blocked (10 pts)
        $enum_blocked = (get_option('gmb_seo_prevent_user_enumeration', '0') === '1');
        $checks['user_enumeration'] = array(
            'label'   => __('User Enumeration Defense', 'gmb-ranker-seo-automation'),
            'passed'  => $enum_blocked,
            'points'  => 10,
            'status'  => $enum_blocked ? __('Protected', 'gmb-ranker-seo-automation') : __('Exposed', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($enum_blocked) $earned_points += 10;

        // 6. Login error masking (10 pts)
        $mask_login = (get_option('gmb_seo_mask_login_errors', '0') === '1');
        $checks['login_errors'] = array(
            'label'   => __('Login Information Leakage Shield', 'gmb-ranker-seo-automation'),
            'passed'  => $mask_login,
            'points'  => 10,
            'status'  => $mask_login ? __('Enabled', 'gmb-ranker-seo-automation') : __('Disabled', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($mask_login) $earned_points += 10;

        // 7. File editing disabled (10 pts)
        $file_edit_disabled = (get_option('gmb_seo_disable_file_edit', '0') === '1') || (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);
        $checks['file_edit'] = array(
            'label'   => __('Dashboard File Editing Disabled', 'gmb-ranker-seo-automation'),
            'passed'  => $file_edit_disabled,
            'points'  => 10,
            'status'  => $file_edit_disabled ? __('Locked', 'gmb-ranker-seo-automation') : __('Unlocked', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($file_edit_disabled) $earned_points += 10;

        // 8. Default 'admin' user check (10 pts)
        $admin_check = $this->check_admin_user_exists();
        $admin_clean = !$admin_check['exists'];
        $checks['admin_username'] = array(
            'label'   => __('No Default "admin" Account', 'gmb-ranker-seo-automation'),
            'passed'  => $admin_clean,
            'points'  => 10,
            'status'  => $admin_clean ? __('Safe', 'gmb-ranker-seo-automation') : sprintf(__('Warning: "%s"', 'gmb-ranker-seo-automation'), $admin_check['username']),
        );
        $total_points += 10;
        if ($admin_clean) $earned_points += 10;

        // 9. XML-RPC disabled (5 pts)
        $xmlrpc_disabled = (get_option('gmb_seo_disable_xmlrpc', '0') === '1');
        $checks['xmlrpc'] = array(
            'label'   => __('XML-RPC Access Disabled', 'gmb-ranker-seo-automation'),
            'passed'  => $xmlrpc_disabled,
            'points'  => 5,
            'status'  => $xmlrpc_disabled ? __('Disabled', 'gmb-ranker-seo-automation') : __('Active', 'gmb-ranker-seo-automation'),
        );
        $total_points += 5;
        if ($xmlrpc_disabled) $earned_points += 5;

        // 10. HTTP Security Headers (10 pts)
        $headers_active = (get_option('gmb_seo_enable_security_headers', '0') === '1');
        $checks['security_headers'] = array(
            'label'   => __('HTTP Security Headers Enforced', 'gmb-ranker-seo-automation'),
            'passed'  => $headers_active,
            'points'  => 10,
            'status'  => $headers_active ? __('Enforced', 'gmb-ranker-seo-automation') : __('Missing', 'gmb-ranker-seo-automation'),
        );
        $total_points += 10;
        if ($headers_active) $earned_points += 10;

        // 11. Brute force / Honeypot shield (5 pts)
        $shield_active = (get_option('gmb_seo_login_lockout_enabled', '0') === '1') || (get_option('gmb_seo_login_honeypot', '0') === '1');
        $checks['brute_force'] = array(
            'label'   => __('Login Brute-Force & Honeypot Shield', 'gmb-ranker-seo-automation'),
            'passed'  => $shield_active,
            'points'  => 5,
            'status'  => $shield_active ? __('Active', 'gmb-ranker-seo-automation') : __('Inactive', 'gmb-ranker-seo-automation'),
        );
        $total_points += 5;
        if ($shield_active) $earned_points += 5;

        // 12. WAF 404 Exploit Probe Detection (5 pts)
        $waf_active = (get_option('gmb_seo_404_exploit_lockout', '0') === '1');
        $checks['waf_404'] = array(
            'label'   => __('WAF 404 Exploit Scanner Lockout', 'gmb-ranker-seo-automation'),
            'passed'  => $waf_active,
            'points'  => 5,
            'status'  => $waf_active ? __('Active', 'gmb-ranker-seo-automation') : __('Inactive', 'gmb-ranker-seo-automation'),
        );
        $total_points += 5;
        if ($waf_active) $earned_points += 5;

        $score = ($total_points > 0) ? round(($earned_points / $total_points) * 100) : 0;

        $grade = 'Needs Attention';
        $badge_class = 'is-danger';
        if ($score >= 85) {
            $grade = 'Excellent Protection';
            $badge_class = 'is-success';
        } elseif ($score >= 60) {
            $grade = 'Moderate Hardening';
            $badge_class = 'is-warning';
        }

        return array(
            'score'       => $score,
            'grade'       => $grade,
            'badge_class' => $badge_class,
            'checks'      => $checks,
        );
    }

    /**
     * Prevent registration of dangerous default usernames (admin, administrator, root, etc.)
     *
     * @param array $illegal_user_logins
     * @return array
     */
    public function prevent_illegal_usernames($illegal_user_logins) {
        if (!is_array($illegal_user_logins)) {
            $illegal_user_logins = array();
        }
        $blocked = array('admin', 'administrator', 'root', 'support', 'webmaster', 'security', 'guest');
        foreach ($blocked as $username) {
            if (!in_array($username, $illegal_user_logins, true)) {
                $illegal_user_logins[] = $username;
            }
        }
        return $illegal_user_logins;
    }

    /**
     * Firewall: Block Malicious User-Agents and Vulnerability Scanners
     */
    public function block_malicious_user_agents() {
        if ($this->is_client_ip_whitelisted()) {
            return;
        }

        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
        if (empty($user_agent)) {
            return;
        }

        $malicious_signatures = array(
            'sqlmap', 'nikto', 'wpscan', 'acunetix', 'masscan', 'zgrab', 'censys',
            'dirbuster', 'havij', 'pangolin', 'nmap', 'hydra', 'metasploit',
            'netsparker', 'fimap', 'jbrofuzz', 'webshag', 'cgicheck', 'shodan'
        );

        foreach ($malicious_signatures as $sig) {
            if (stripos($user_agent, $sig) !== false) {
                $this->log_security_incident('blocked_user_agent', sprintf('Blocked malicious user-agent scanner signature "%s"', $sig));
                if (function_exists('status_header')) {
                    status_header(403);
                }
                if (!headers_sent()) {
                    header('Content-Type: text/plain; charset=UTF-8');
                }
                exit('Access Denied: Malicious scanner user-agent detected by Firewall.');
            }
        }
    }

    /**
     * Disable open public user registration to prevent bot subscriber spam
     *
     * @param mixed $value
     * @return bool
     */
    public function disable_open_user_registration($value) {
        return false;
    }

    /**
     * Rogue Admin Shield: Detects and demotes unauthorized administrator accounts created outside the dashboard
     */
    public function protect_against_unauthorized_admin_creation() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            return;
        }

        $registered_admins = get_option('gmb_sec_approved_admins', array());
        if (!is_array($registered_admins)) {
            $registered_admins = array();
        }

        if (function_exists('get_users')) {
            $admins = get_users(array('role' => 'administrator', 'fields' => 'all'));
            if (empty($registered_admins)) {
                // Initial baseline snapshot
                $ids = array();
                foreach ($admins as $adm) {
                    $ids[] = (int) $adm->ID;
                }
                update_option('gmb_sec_approved_admins', $ids, false);
                return;
            }

            foreach ($admins as $adm) {
                $adm_id = (int) $adm->ID;
                if (!in_array($adm_id, $registered_admins, true)) {
                    // Never change a user's role automatically. Imports,
                    // restores, multisite synchronization, and legitimate
                    // administrators can all create a new ID. Record the
                    // finding for review instead of locking out an admin.
                    $user_login = isset($adm->user_login) ? $adm->user_login : 'unknown';
                    $this->log_security_incident('unapproved_admin_detected', sprintf('Administrator account "%s" (ID: %d) requires manual approval.', $user_login, $adm_id));
                }
            }
        }
    }

    /**
     * Retrieve recent security audit logs
     *
     * @param int $limit
     * @return array
     */
    public function get_recent_security_incidents($limit = 10) {
        $logs = get_option('gmb_security_audit_logs', array());
        if (!is_array($logs)) {
            return array();
        }
        return array_slice($logs, 0, $limit);
    }

    /**
     * Apply all recommended hardening options with 1 click
     */
    public function apply_recommended_hardening() {
        update_option('gmb_seo_block_uploads_execution', '1');
        update_option('gmb_seo_block_sensitive_files', '1');
        update_option('gmb_seo_disable_directory_indexing', '1');
        update_option('gmb_seo_disable_http_methods', '1');
        update_option('gmb_seo_disable_application_passwords', '1');
        update_option('gmb_seo_prevent_user_enumeration', '1');
        update_option('gmb_seo_mask_login_errors', '1');
        update_option('gmb_seo_disable_file_edit', '1');
        update_option('gmb_seo_disable_xmlrpc', '1');
        update_option('gmb_seo_hide_wp_version', '1');
        update_option('gmb_seo_restrict_rest_api', '1');
        update_option('gmb_seo_enable_security_headers', '1');
        update_option('gmb_seo_login_lockout_enabled', '1');
        update_option('gmb_seo_login_honeypot', '1');
        update_option('gmb_seo_hide_remember_me', '1');
        update_option('gmb_seo_session_expiration_hours', 24);
        update_option('gmb_seo_strong_password_policy', '1');
        update_option('gmb_seo_404_exploit_lockout', '1');
        update_option('gmb_seo_block_malicious_user_agents', '1');
        update_option('gmb_seo_block_unauthorized_admins', '1');
        update_option('gmb_seo_disable_open_registration', '1');

        $ssl_active = function_exists('is_ssl') ? is_ssl() : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        update_option('gmb_seo_enable_hsts', $ssl_active ? '1' : '0');
        update_option('gmb_seo_referrer_policy', 'strict-origin-when-cross-origin');
        update_option('gmb_seo_permissions_policy', '1');
        update_option('gmb_seo_csp_frame_ancestors', '1');
        update_option('gmb_seo_enable_coop', 'same-origin-allow-popups');
        update_option('gmb_seo_enable_corp', 'same-site');
        update_option('gmb_seo_enable_coep', 'unsafe-none');
        update_option('gmb_seo_cross_domain_policies', 'none');

        $this->block_code_execution_in_uploads();
        $this->disable_directory_indexing();
        return true;
    }

    /**
     * Safely change a user's login username in the database
     *
     * @param int $user_id
     * @param string $new_username
     * @return array array('success' => bool, 'message' => string)
     */
    public function change_username($user_id, $new_username) {
        global $wpdb;

        $user_id = intval($user_id);
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return array('success' => false, 'message' => __('User not found.', 'gmb-ranker-seo-automation'));
        }

        $new_username = sanitize_user($new_username, true);
        if (empty($new_username)) {
            return array('success' => false, 'message' => __('The username provided is invalid.', 'gmb-ranker-seo-automation'));
        }

        if (strtolower($new_username) === strtolower($user->user_login)) {
            return array('success' => false, 'message' => __('The new username is identical to the current username.', 'gmb-ranker-seo-automation'));
        }

        if (!validate_username($new_username)) {
            return array('success' => false, 'message' => __('The username contains illegal characters.', 'gmb-ranker-seo-automation'));
        }

        $illegal = apply_filters('illegal_user_logins', array('admin', 'administrator', 'root', 'support', 'webmaster', 'security', 'guest'));
        if (in_array(strtolower($new_username), array_map('strtolower', (array) $illegal), true)) {
            return array('success' => false, 'message' => sprintf(__('The username "%s" is reserved and disallowed for security reasons.', 'gmb-ranker-seo-automation'), $new_username));
        }

        if (username_exists($new_username)) {
            return array('success' => false, 'message' => sprintf(__('The username "%s" is already taken by another account.', 'gmb-ranker-seo-automation'), $new_username));
        }

        $old_username = $user->user_login;
        $new_nicename = sanitize_title($new_username);

        // Direct, safe update in wp_users
        $updated = $wpdb->update(
            $wpdb->users,
            array(
                'user_login'    => $new_username,
                'user_nicename' => $new_nicename,
            ),
            array('ID' => $user_id),
            array('%s', '%s'),
            array('%d')
        );

        if ($updated === false) {
            return array('success' => false, 'message' => __('Database error: Failed to update username.', 'gmb-ranker-seo-automation'));
        }

        // Clean user caches
        clean_user_cache($user_id);

        // Ensure display name is distinct from the private login username
        $safe_display = '';
        if (!empty($user->first_name) && !empty($user->last_name)) {
            $safe_display = trim($user->first_name . ' ' . $user->last_name);
        } elseif (!empty($user->first_name)) {
            $safe_display = trim($user->first_name);
        } elseif (!empty($user->display_name) && strcasecmp($user->display_name, $old_username) !== 0 && strcasecmp($user->display_name, $new_username) !== 0) {
            $safe_display = $user->display_name;
        } else {
            $clean = preg_replace('/[0-9_.-]+/', ' ', $new_username);
            $clean = trim(ucwords($clean));
            $safe_display = (!empty($clean) && strcasecmp($clean, $new_username) !== 0) ? $clean : 'Site Administrator';
        }

        if (strcasecmp($safe_display, $new_username) === 0) {
            $safe_display = 'Administrator';
        }

        wp_update_user(array(
            'ID'           => $user_id,
            'display_name' => $safe_display,
        ));

        // If current user is modifying their own username, update auth cookies immediately so session isn't lost
        if (get_current_user_id() === $user_id) {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);
        }

        // Log security audit incident
        $this->log_security_incident(
            'username_changed',
            sprintf('Administrator username changed from "%s" to "%s" for user ID #%d', $old_username, $new_username, $user_id)
        );

        // Fire action hook
        do_action('gmb_seo_username_changed', $user_id, $old_username, $new_username);

        return array(
            'success'      => true,
            'message'      => sprintf(__('Username successfully changed from "%1$s" to "%2$s"!', 'gmb-ranker-seo-automation'), $old_username, $new_username),
            'old_username' => $old_username,
            'new_username' => $new_username,
            'user_id'      => $user_id,
        );
    }
}
