<?php
/**
 * Security Hardening Controller for GMB Ranker SEO Automation
 *
 * Coordinates website hardening, anti-hacking defenses, user enumeration blocking,
 * brute-force shielding, and HTTP security headers.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Security {

    /**
     * @var GMB_Ranker_SEO_Security_Service
     */
    protected $service;

    public function __construct() {
        // Resolve security service
        if (class_exists('GMB_Ranker_SEO_Security_Service')) {
            $this->service = GMB_Ranker_SEO_Security_Service::get_instance();
        }

        // 1. Disable XML-RPC
        if (get_option('gmb_seo_disable_xmlrpc', '0') === '1') {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('xmlrpc_methods', function($methods) {
                return array();
            });

            // Proactive firewall block for xmlrpc.php requests
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if (strpos($request_uri, '/xmlrpc.php') !== false) {
                status_header(403);
                header('Content-Type: text/plain; charset=UTF-8');
                exit('XML-RPC is disabled.');
            }
        }

        // 2. Hide WordPress Version Number
        if (get_option('gmb_seo_hide_wp_version', '0') === '1') {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');

            // Also strip versions from enqueued scripts & styles on front-end for security
            if (!is_admin()) {
                add_filter('style_loader_src', array($this, 'remove_src_version'), 9999);
                add_filter('script_loader_src', array($this, 'remove_src_version'), 9999);
            }

            // Output buffering to strip hardcoded generator tags and version indicators from HTML
            add_action('template_redirect', array($this, 'start_output_buffer'), 1);
        }

        // 3. Restrict REST API to authenticated users
        if (get_option('gmb_seo_restrict_rest_api', '0') === '1') {
            // Firewall blocking on init
            add_action('init', array($this, 'restrict_rest_api_access'), 1);

            // Filter fallback for all rest endpoints
            add_filter('rest_authentication_errors', function($result) {
                if (!empty($result)) {
                    return $result;
                }
                if (!is_user_logged_in()) {
                    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                    $is_rest = (strpos($request_uri, '/wp-json/') !== false || isset($_GET['rest_route']));
                    if ($is_rest) {
                        $is_excluded = (
                            strpos($request_uri, '/contact-form-7/') !== false || 
                            strpos($request_uri, '/oembed/') !== false ||
                            strpos($request_uri, '/gmb-ranker/') !== false
                        );
                        if (!$is_excluded) {
                            return new WP_Error('rest_forbidden', __('REST API restricted to authenticated users.', 'gmb-ranker-seo-automation'), array('status' => 401));
                        }
                    }
                }
                return $result;
            }, 9999);
        }

        // 4. Inject & Deduplicate HTTP Security Headers (Grade A+ Suite)
        if (get_option('gmb_seo_enable_security_headers', '0') === '1') {
            add_action('send_headers', array($this, 'send_security_headers'));
            add_filter('wp_headers', array($this, 'filter_wp_headers'));
        }

        // 5. Block Code Execution in Uploads Folder
        if (get_option('gmb_seo_block_uploads_execution', '0') === '1' && $this->service) {
            $this->service->block_code_execution_in_uploads();

            // Firewall interception for uploads php execution attempts
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if (strpos($request_uri, '/uploads/') !== false && preg_match('/\.php(\?|$)/i', $request_uri)) {
                status_header(403);
                header('Content-Type: text/plain; charset=UTF-8');
                exit('Forbidden: Script execution is blocked in the uploads directory.');
            }
        }

        // 6. Prevent User Enumeration (author query scans and unauthenticated user REST routes)
        if (get_option('gmb_seo_prevent_user_enumeration', '0') === '1' && $this->service) {
            add_action('init', array($this->service, 'prevent_user_enumeration'), 5);
        }

        // 7. Prevent Login Information Leakage (generic error messages)
        if (get_option('gmb_seo_mask_login_errors', '0') === '1' && $this->service) {
            $this->service->mask_login_errors();
        }

        // 8. Disable Theme & Plugin File Editing in Admin Dashboard
        if (get_option('gmb_seo_disable_file_edit', '0') === '1' && $this->service) {
            $this->service->disable_file_editing();
        }

        // 9. Login Brute-Force Protection & Lockout Shield
        if (get_option('gmb_seo_login_lockout_enabled', '0') === '1' && $this->service) {
            add_action('login_init', array($this->service, 'enforce_ip_lockout'), 1);
            add_action('wp_login_failed', array($this->service, 'record_failed_login'), 10, 1);
            add_action('wp_login', array($this->service, 'clear_failed_attempts_on_success'), 10, 2);
        }

        // 10. Login Form Bot Honeypot
        if (get_option('gmb_seo_login_honeypot', '0') === '1' && $this->service) {
            add_action('login_form', array($this->service, 'inject_login_honeypot'));
            add_filter('authenticate', array($this->service, 'verify_login_honeypot'), 20, 1);
        }

        // 11. IP Blacklist Enforcement (Terminates blacklisted IPs immediately)
        if ($this->service) {
            add_action('init', array($this->service, 'enforce_ip_blacklist'), 1);
        }

        // 12. Protect Sensitive Files & debug.log
        if (get_option('gmb_seo_block_sensitive_files', '0') === '1' && $this->service) {
            add_action('init', array($this->service, 'block_sensitive_files_access'), 1);
        }

        // 13. Disable Insecure HTTP Request Methods (TRACE, TRACK)
        if (get_option('gmb_seo_disable_http_methods', '0') === '1' && $this->service) {
            add_action('init', array($this->service, 'block_dangerous_http_methods'), 1);
        }

        // 14. Disable Directory Browsing
        if (get_option('gmb_seo_disable_directory_indexing', '0') === '1' && $this->service) {
            add_action('admin_init', array($this->service, 'disable_directory_indexing'), 10);
        }

        // 15. Disable WordPress Application Passwords
        if (get_option('gmb_seo_disable_application_passwords', '0') === '1' && $this->service) {
            $this->service->disable_application_passwords();
        }

        // 16. Custom / Obscured Login URL
        if (!empty(get_option('gmb_seo_custom_login_slug', '')) && $this->service) {
            add_action('init', array($this->service, 'handle_custom_login_url'), 1);
        }

        // 17. Session & Cookie Expiration
        if ((int) get_option('gmb_seo_session_expiration_hours', 0) > 0 && $this->service) {
            add_filter('auth_cookie_expiration', array($this->service, 'enforce_session_expiration'), 10, 3);
        }

        // 18. Hide "Remember Me" Checkbox
        if (get_option('gmb_seo_hide_remember_me', '0') === '1' && $this->service) {
            add_action('login_head', array($this->service, 'hide_remember_me'));
        }

        // 19. Strong Password Policy for Administrators
        if (get_option('gmb_seo_strong_password_policy', '0') === '1' && $this->service) {
            add_action('user_profile_update_errors', array($this->service, 'enforce_strong_password_policy'), 10, 3);
        }

        // 20. WAF 404 Exploit Scanner Detection & Auto-Lockout
        if (get_option('gmb_seo_404_exploit_lockout', '0') === '1' && $this->service) {
            add_action('template_redirect', array($this->service, 'handle_404_exploit_detection'), 1);
        }

        // 21. Two-Factor Authentication (2FA) for Administrators
        if (get_option('gmb_seo_enable_2fa', '0') === '1' && $this->service) {
            add_filter('wp_authenticate_user', array($this->service, 'initiate_2fa_challenge'), 30, 2);
            add_action('login_form', array($this->service, 'render_2fa_login_field'));
        }

        // 22. Forbid Default Usernames (admin, administrator, root)
        if ($this->service) {
            add_filter('illegal_user_logins', array($this->service, 'prevent_illegal_usernames'));
        }

        // 23. Firewall: Block Malicious User-Agents and Vulnerability Scanners
        if (get_option('gmb_seo_block_malicious_user_agents', '0') === '1' && $this->service) {
            add_action('init', array($this->service, 'block_malicious_user_agents'), 1);
        }

        // 24. Rogue Administrator Account Interceptor
        if (get_option('gmb_seo_block_unauthorized_admins', '0') === '1' && $this->service) {
            add_action('admin_init', array($this->service, 'protect_against_unauthorized_admin_creation'), 10);
        }

        // 25. Disable Open Public Registration Override
        if (get_option('gmb_seo_disable_open_registration', '0') === '1' && $this->service) {
            add_filter('option_users_can_register', array($this->service, 'disable_open_user_registration'), 999);
        }

        // 26. Allow Username Renaming in Profile Screen
        if (get_option('gmb_seo_allow_username_change', '1') === '1' && is_admin()) {
            add_action('admin_footer', array($this, 'render_profile_username_changer_script'));
        }
    }

    /**
     * Render Username Changer Button and Modal on profile.php & user-edit.php
     */
    public function render_profile_username_changer_script() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !in_array($screen->id, array('profile', 'user-edit', 'user-edit-network'), true)) {
            return;
        }

        if (!current_user_can('manage_options') && !current_user_can('edit_users')) {
            return;
        }

        global $user_id;
        $target_user_id = !empty($user_id) ? intval($user_id) : get_current_user_id();
        $target_user = get_user_by('id', $target_user_id);
        if (!$target_user) {
            return;
        }

        wp_enqueue_style('gmb-ranker-tokens', plugins_url('assets/css/tokens.css', dirname(dirname(dirname(__FILE__)))));
        wp_enqueue_style('gmb-ranker-admin-dashboard', plugins_url('assets/css/admin-dashboard.css', dirname(dirname(dirname(__FILE__)))), array('gmb-ranker-tokens'));
        ?>
        <div id="gmb-profile-username-modal" class="gmb-username-modal-overlay">
            <div class="gmb-username-modal-card">
                <div class="gmb-username-modal-header">
                    <h3 class="gmb-username-modal-title">
                        <?php esc_html_e('Change Login Username', 'gmb-ranker-seo-automation'); ?>
                    </h3>
                    <button type="button" id="gmb-close-profile-username-modal" class="gmb-username-modal-close" aria-label="<?php esc_attr_e('Close', 'gmb-ranker-seo-automation'); ?>">&times;</button>
                </div>
                <p class="gmb-username-modal-desc">
                    <?php esc_html_e('Safely rename your WordPress account login name without direct database manipulation or losing capabilities.', 'gmb-ranker-seo-automation'); ?>
                </p>
                <div class="gmb-username-modal-field">
                    <label class="gmb-username-modal-label"><?php esc_html_e('Current Username', 'gmb-ranker-seo-automation'); ?></label>
                    <input type="text" value="<?php echo esc_attr($target_user->user_login); ?>" disabled class="gmb-username-modal-input-current" />
                </div>
                <div class="gmb-username-modal-field gmb-username-modal-field--last">
                    <label class="gmb-username-modal-label"><?php esc_html_e('New Username', 'gmb-ranker-seo-automation'); ?></label>
                    <input type="text" id="gmb-new-profile-username-input" placeholder="e.g. <?php echo esc_attr($target_user->user_login); ?>_secure" class="gmb-username-modal-input-new" />
                    <span id="gmb-profile-username-error" class="gmb-username-modal-error"></span>
                </div>
                <div class="gmb-username-modal-footer">
                    <button type="button" id="gmb-cancel-profile-username-btn" class="button button-secondary"><?php esc_html_e('Cancel', 'gmb-ranker-seo-automation'); ?></button>
                    <button type="button" id="gmb-submit-profile-username-btn" class="button button-primary gmb-btn--modal-submit"><?php esc_html_e('Update Username', 'gmb-ranker-seo-automation'); ?></button>
                </div>
            </div>
        </div>

        <script>
        (function() {
            function initUsernameModifier() {
                var userLoginInput = document.getElementById('user_login');
                if (!userLoginInput || document.getElementById('gmb-open-profile-username-btn')) return;

                var changeBtn = document.createElement('button');
                changeBtn.type = 'button';
                changeBtn.id = 'gmb-open-profile-username-btn';
                changeBtn.className = 'button button-secondary gmb-btn-profile-change-username';
                changeBtn.innerHTML = 'Change Username (GMB Ranker)';

                if (userLoginInput.nextSibling) {
                    userLoginInput.parentNode.insertBefore(changeBtn, userLoginInput.nextSibling);
                } else {
                    userLoginInput.parentNode.appendChild(changeBtn);
                }

                var modal = document.getElementById('gmb-profile-username-modal');
                var closeBtn = document.getElementById('gmb-close-profile-username-modal');
                var cancelBtn = document.getElementById('gmb-cancel-profile-username-btn');
                var submitBtn = document.getElementById('gmb-submit-profile-username-btn');
                var input = document.getElementById('gmb-new-profile-username-input');
                var errorSpan = document.getElementById('gmb-profile-username-error');

                function openModal() {
                    modal.classList.add('is-active');
                    input.value = '';
                    errorSpan.classList.remove('is-active');
                    setTimeout(function() { input.focus(); }, 100);
                }

                function closeModal() {
                    modal.classList.remove('is-active');
                }

                changeBtn.addEventListener('click', openModal);
                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

                submitBtn.addEventListener('click', function() {
                    var newUsername = input.value.trim();
                    if (!newUsername) {
                        errorSpan.textContent = 'Please enter a new username.';
                        errorSpan.style.display = 'block';
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Updating...';
                    errorSpan.style.display = 'none';

                    var formData = new FormData();
                    formData.append('action', 'gmb_change_username');
                    formData.append('user_id', '<?php echo esc_js($target_user_id); ?>');
                    formData.append('new_username', newUsername);
                    if (window.gmb_ranker_admin && window.gmb_ranker_admin.nonce) {
                        formData.append('nonce', window.gmb_ranker_admin.nonce);
                    }

                    fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Update Username';
                        if (data && data.success) {
                            alert(data.data.message || 'Username updated successfully!');
                            window.location.reload();
                        } else {
                            errorSpan.textContent = (data && data.data && data.data.message) ? data.data.message : 'Error updating username.';
                            errorSpan.style.display = 'block';
                        }
                    })
                    .catch(function(err) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Update Username';
                        errorSpan.textContent = 'Network or server error occurred.';
                        errorSpan.style.display = 'block';
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initUsernameModifier);
            } else {
                initUsernameModifier();
            }
        })();
        </script>
        <?php
    }

    /**
     * Send HTTP Security Headers
     */
    public function send_security_headers() {
        if ($this->service) {
            $this->service->apply_security_headers();
        }
    }

    /**
     * Filter WordPress core header array for clean deduplication
     *
     * @param array $headers
     * @return array
     */
    public function filter_wp_headers($headers) {
        if (!is_array($headers) || is_admin()) {
            return $headers;
        }

        if (get_option('gmb_seo_enable_security_headers', '0') !== '1') {
            return $headers;
        }

        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options']        = 'SAMEORIGIN';
        $headers['X-XSS-Protection']       = '1; mode=block';

        $ref_policy = get_option('gmb_seo_referrer_policy', 'strict-origin-when-cross-origin');
        if (empty($ref_policy)) {
            $ref_policy = 'strict-origin-when-cross-origin';
        }
        $headers['Referrer-Policy'] = sanitize_text_field($ref_policy);

        $ssl_active = function_exists('is_ssl') ? is_ssl() : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        if ($ssl_active && get_option('gmb_seo_enable_hsts', '0') === '1') {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        if (get_option('gmb_seo_permissions_policy', '0') === '1') {
            $headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=(), payment=(), usb=()';
        }

        if (get_option('gmb_seo_csp_frame_ancestors', '0') === '1') {
            $headers['Content-Security-Policy'] = "frame-ancestors 'self'; base-uri 'self'; object-src 'none';";
        }

        $coop = get_option('gmb_seo_enable_coop', 'same-origin-allow-popups');
        if (!empty($coop) && $coop !== 'disabled') {
            $headers['Cross-Origin-Opener-Policy'] = sanitize_text_field($coop);
        }

        $corp = get_option('gmb_seo_enable_corp', 'same-site');
        if (!empty($corp) && $corp !== 'disabled') {
            $headers['Cross-Origin-Resource-Policy'] = sanitize_text_field($corp);
        }

        $coep = get_option('gmb_seo_enable_coep', 'unsafe-none');
        if (!empty($coep) && $coep !== 'disabled') {
            $headers['Cross-Origin-Embedder-Policy'] = sanitize_text_field($coep);
        }

        $cross_domain = get_option('gmb_seo_cross_domain_policies', 'none');
        if (!empty($cross_domain) && $cross_domain !== 'disabled') {
            $headers['X-Permitted-Cross-Domain-Policies'] = sanitize_text_field($cross_domain);
        }

        return $headers;
    }

    public function start_output_buffer() {
        if (!is_admin()) {
            ob_start(array($this, 'clean_html_output'));
        }
    }

    public function clean_html_output($html) {
        if (empty($html) || !is_string($html)) {
            return $html;
        }

        // Remove generator tags safely
        $cleaned = preg_replace('/<meta[^>]+name=["\']generator["\'][^>]+content=["\']WordPress[^"\']*["\'][^>]*>/i', '', $html);
        if ($cleaned !== null) {
            $html = $cleaned;
        }

        $cleaned = preg_replace('/<meta[^>]+content=["\']WordPress[^"\']*["\'][^>]+name=["\']generator["\'][^>]*>/i', '', $html);
        if ($cleaned !== null) {
            $html = $cleaned;
        }

        // Strip version query strings (?ver= or &ver=) from script/style source tags safely
        $cleaned = preg_replace('/((?:href|src)=["\'][^"\']+\?)(?:ver|v)=[^&"\']*(?:&([^"\']*))?(["\'])/i', '$1$2$3', $html);
        if ($cleaned !== null) {
            $cleaned = preg_replace('/((?:href|src)=["\'][^"\']+)\?(["\'])/i', '$1$2', $cleaned);
            if ($cleaned !== null) {
                $html = $cleaned;
            }
        }

        return $html;
    }

    public function restrict_rest_api_access() {
        if (!is_user_logged_in()) {
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $is_rest = (strpos($request_uri, '/wp-json/') !== false || isset($_GET['rest_route']));
            if ($is_rest) {
                $is_excluded = (
                    strpos($request_uri, '/contact-form-7/') !== false || 
                    strpos($request_uri, '/oembed/') !== false ||
                    strpos($request_uri, '/gmb-ranker/') !== false
                );
                if (!$is_excluded) {
                    status_header(401);
                    header('Content-Type: application/json; charset=UTF-8');
                    exit(wp_json_encode(array(
                        'code' => 'rest_forbidden',
                        'message' => __('REST API restricted to authenticated users.', 'gmb-ranker-seo-automation'),
                        'data' => array('status' => 401)
                    )));
                }
            }
        }
    }

    public function remove_src_version($src) {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
}
