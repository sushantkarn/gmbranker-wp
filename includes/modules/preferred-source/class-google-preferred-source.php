<?php
if (!defined('ABSPATH')) exit;

class Google_Preferred_Source {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_shortcode('google_preferred_source', array($this, 'render_shortcode'));
        add_filter('the_content', array($this, 'auto_insert_button'));
    }

    public function add_admin_menu() {
        add_options_page(
            __('Google Preferred Source Settings', 'gmb-ranker-seo-automation'),
            __('Google Preferred Source', 'gmb-ranker-seo-automation'),
            'manage_options',
            'gmb-ranker-preferred-source',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('gmb_gps_settings_group', 'gmb_gps_enabled', array(
            'default' => '1',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_target_domain', array(
            'default' => wp_parse_url(home_url(), PHP_URL_HOST),
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_button_text', array(
            'default' => __('Add to Preferred Sources', 'gmb-ranker-seo-automation'),
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_post_types', array(
            'default' => array('post'),
            'sanitize_callback' => array($this, 'sanitize_array')
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_insertion_location', array(
            'default' => 'content_end',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_button_theme', array(
            'default' => 'google_white',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_button_size', array(
            'default' => 'medium',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('gmb_gps_settings_group', 'gmb_gps_custom_css', array(
            'default' => '',
            'sanitize_callback' => 'wp_strip_all_tags'
        ));
    }

    public function sanitize_array($input) {
        if (!is_array($input)) return array();
        return array_map('sanitize_text_field', $input);
    }

    public function enqueue_styles() {
        if (get_option('gmb_gps_enabled', '1') !== '1') return;

        $ver = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0';
        wp_enqueue_style(
            'gmb-gps-frontend',
            plugins_url('assets/css/frontend.css', dirname(dirname(dirname(__FILE__)))),
            array(),
            $ver
        );

        $custom_css = get_option('gmb_gps_custom_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style('gmb-gps-frontend', $custom_css);
        }
    }

    public function render_button() {
        if (get_option('gmb_gps_enabled', '1') !== '1') return '';

        $raw_domain = get_option('gmb_gps_target_domain', '');
        if (empty($raw_domain)) {
            $raw_domain = wp_parse_url(home_url(), PHP_URL_HOST);
        }

        $domain = preg_replace('#^https?://#i', '', trim($raw_domain));
        $domain = rtrim($domain, '/');

        $text  = get_option('gmb_gps_button_text', __('Add to Preferred Sources', 'gmb-ranker-seo-automation'));
        $theme = get_option('gmb_gps_button_theme', 'google_white');
        $size  = get_option('gmb_gps_button_size', 'medium');

        $intent_url = 'https://www.google.com/preferences/source?q=' . urlencode($domain);

        ob_start();
        ?>
        <div class="gps-btn-container gps-align-center">
            <a href="<?php echo esc_url($intent_url); ?>" target="_blank" rel="noopener noreferrer" class="gps-btn gps-theme-<?php echo esc_attr($theme); ?> gps-size-<?php echo esc_attr($size); ?>">
                <svg class="gps-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span><?php echo esc_html($text); ?></span>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_shortcode() {
        return $this->render_button();
    }

    public function auto_insert_button($content) {
        if (!is_singular() || !in_the_loop() || !is_main_query()) return $content;

        $enabled = get_option('gmb_gps_enabled', '1');
        if ($enabled !== '1') return $content;

        $post_types = get_option('gmb_gps_post_types', array('post'));
        if (!is_array($post_types) || !in_array(get_post_type(), $post_types)) return $content;

        $location = get_option('gmb_gps_insertion_location', 'content_end');
        if ($location === 'manual') return $content;

        $button_html = $this->render_button();

        if ($location === 'content_start') {
            return $button_html . $content;
        } else {
            return $content . $button_html;
        }
    }

    public function render_settings_page() {
        $current_page = 'gmb-ranker-preferred-source';
        $current_tab  = 'preferred_source';
        $view_args    = compact('current_page', 'current_tab');

        if (class_exists('GMB_Ranker_SEO_Helpers')) {
            GMB_Ranker_SEO_Helpers::render_view('layout/header.php', $view_args);
        }
        ?>
        <div class="rm-tab-content active" id="rm-tab-preferred-source">
            <div class="gmb-admin-wrap">
                <div class="gmb-analytics-container">

                    <div class="gmb-table-card" style="padding: 24px;">
                        <div class="gmb-header-hero-text" style="margin-bottom: 20px;">
                            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;"><?php esc_html_e('Google Preferred Source Button', 'gmb-ranker-seo-automation'); ?></h2>
                            <p class="gmb-text-muted" style="margin: 4px 0 0 0; color: #64748b; font-size: 0.85rem;"><?php esc_html_e('Boost your E-E-A-T and organic visibility in Google Search by encouraging readers to set your site as a preferred source.', 'gmb-ranker-seo-automation'); ?></p>
                        </div>

                        <form method="post" action="options.php">
                            <?php settings_fields('gmb_gps_settings_group'); ?>
                            <?php do_settings_sections('gmb_gps_settings_group'); ?>

                            <table class="form-table" style="max-width: 800px;">
                                <tr valign="top">
                                    <th scope="row" style="width: 200px;"><?php esc_html_e('Enable Button', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <label class="gmb-switch">
                                            <input type="checkbox" name="gmb_gps_enabled" value="1" <?php checked('1', get_option('gmb_gps_enabled', '1')); ?> />
                                            <span class="gmb-slider"></span>
                                        </label>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Target Domain', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <input type="text" name="gmb_gps_target_domain" value="<?php echo esc_attr(get_option('gmb_gps_target_domain', wp_parse_url(home_url(), PHP_URL_HOST))); ?>" class="gmb-input regular-text" style="width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" />
                                        <p class="gmb-form-help" style="font-size: 0.8rem; color: #64748b; margin-top: 4px;"><?php printf(__('The domain you want users to prefer. (Must match your domain level, e.g., %s)', 'gmb-ranker-seo-automation'), esc_html(wp_parse_url(home_url(), PHP_URL_HOST))); ?></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Button Text', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <input type="text" name="gmb_gps_button_text" value="<?php echo esc_attr(get_option('gmb_gps_button_text', __('Add to Preferred Sources', 'gmb-ranker-seo-automation'))); ?>" class="gmb-input regular-text" style="width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" />
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Display on Post Types', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <div class="gmb-checkbox-group" style="display: flex; gap: 16px; flex-wrap: wrap;">
                                            <?php
                                            $post_types = get_option('gmb_gps_post_types', array('post'));
                                            if (!is_array($post_types)) $post_types = array('post');
                                            $available_post_types = get_post_types(array('public' => true), 'objects');
                                            foreach ($available_post_types as $pt) {
                                                if ($pt->name === 'attachment') continue;
                                                ?>
                                                <label class="gmb-checkbox-label" style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                                                    <input type="checkbox" name="gmb_gps_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $post_types)); ?> />
                                                    <?php echo esc_html($pt->label); ?>
                                                </label>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <p class="gmb-form-help" style="font-size: 0.8rem; color: #64748b; margin-top: 4px;"><?php esc_html_e('Select which content types should automatically have the button appended.', 'gmb-ranker-seo-automation'); ?></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Auto Insertion', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <select name="gmb_gps_insertion_location" class="gmb-select" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                            <option value="content_start" <?php selected('content_start', get_option('gmb_gps_insertion_location', 'content_end')); ?>><?php esc_html_e('Insert at Start of Post Content', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="content_end" <?php selected('content_end', get_option('gmb_gps_insertion_location', 'content_end')); ?>><?php esc_html_e('Insert at End of Post Content', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="manual" <?php selected('manual', get_option('gmb_gps_insertion_location', 'content_end')); ?>><?php esc_html_e('Manual Placement Only (Use Shortcode/Widget)', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Button Theme', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <select name="gmb_gps_button_theme" class="gmb-select" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                            <option value="google_white" <?php selected('google_white', get_option('gmb_gps_button_theme', 'google_white')); ?>><?php esc_html_e('Google Minimalist (White)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="google_blue" <?php selected('google_blue', get_option('gmb_gps_button_theme', 'google_white')); ?>><?php esc_html_e('Google Primary (Blue)', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="google_dark" <?php selected('google_dark', get_option('gmb_gps_button_theme', 'google_white')); ?>><?php esc_html_e('Google Dark Mode (Charcoal)', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Button Size', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <select name="gmb_gps_button_size" class="gmb-select" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                            <option value="small" <?php selected('small', get_option('gmb_gps_button_size', 'medium')); ?>><?php esc_html_e('Small', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="medium" <?php selected('medium', get_option('gmb_gps_button_size', 'medium')); ?>><?php esc_html_e('Medium', 'gmb-ranker-seo-automation'); ?></option>
                                            <option value="large" <?php selected('large', get_option('gmb_gps_button_size', 'medium')); ?>><?php esc_html_e('Large', 'gmb-ranker-seo-automation'); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Custom CSS', 'gmb-ranker-seo-automation'); ?></th>
                                    <td>
                                        <textarea name="gmb_gps_custom_css" rows="5" cols="50" class="gmb-textarea" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder=".gps-btn { margin: 20px auto; }"><?php echo esc_textarea(get_option('gmb_gps_custom_css')); ?></textarea>
                                    </td>
                                </tr>
                            </table>

                            <div class="gmb-form-actions" style="margin-top: 20px;">
                                <input type="submit" class="button button-primary" value="<?php esc_html_e('Save Configuration', 'gmb-ranker-seo-automation'); ?>" />
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>
        <?php
        if (class_exists('GMB_Ranker_SEO_Helpers')) {
            GMB_Ranker_SEO_Helpers::render_view('layout/footer.php', $view_args);
        }
    }
}
