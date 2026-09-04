<?php
/**
 * Editor Metabox & Content Analysis Controller
 *
 * Serves as a thin orchestration controller managing post metabox registration,
 * post save hook delegation, post list column rendering, user profile SEO fields,
 * and AI post modal view rendering.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Metabox {

    /**
     * Constructor
     */
    public function __construct() {
        if (get_option('gmb_ranker_module_metadata', '1') === '0' || get_option('gmb_ranker_module_metadata', '1') === 'off') {
            return;
        }

        add_action('add_meta_boxes', array($this, 'add_seo_metabox'));
        add_action('save_post', array($this, 'save_seo_metabox_data'));
        add_action('post_submitbox_misc_actions', array($this, 'add_seo_score_to_publish_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_metabox_assets'));

        // Post list columns
        add_filter('manage_posts_columns', array($this, 'add_seo_columns'));
        add_action('manage_posts_custom_column', array($this, 'render_seo_columns'), 10, 2);
        add_filter('manage_pages_columns', array($this, 'add_seo_columns'));
        add_action('manage_pages_custom_column', array($this, 'render_seo_columns'), 10, 2);

        // User profile author SEO
        add_action('show_user_profile', array($this, 'render_user_profile_seo_fields'));
        add_action('edit_user_profile', array($this, 'render_user_profile_seo_fields'));
        add_action('personal_options_update', array($this, 'save_user_profile_seo_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_seo_fields'));

        // Single Post AI SEO Modal Footer Hook
        add_action('admin_footer', array($this, 'render_ai_post_modal'));
    }

    /**
     * Enqueue assets for post editor screen
     *
     * @param string $hook
     */
    public function enqueue_metabox_assets($hook) {
        if ($hook === 'post.php' || $hook === 'post-new.php' || $hook === 'edit.php') {
            $css_ver = (defined('GMB_RANKER_SEO_PATH') && file_exists(GMB_RANKER_SEO_PATH . 'assets/css/admin-metabox.css')) 
                ? filemtime(GMB_RANKER_SEO_PATH . 'assets/css/admin-metabox.css') 
                : '2.1.0';
            wp_enqueue_style(
                'gmb-ranker-admin-metabox',
                class_exists('GMB_Ranker_SEO_Helpers') ? GMB_Ranker_SEO_Helpers::asset_url('css/admin-metabox.css') : plugins_url('assets/css/admin-metabox.css', dirname(dirname(__FILE__))),
                array(),
                $css_ver
            );
        }

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_media();
            $js_ver = (defined('GMB_RANKER_SEO_PATH') && file_exists(GMB_RANKER_SEO_PATH . 'assets/js/admin-metabox.js')) 
                ? filemtime(GMB_RANKER_SEO_PATH . 'assets/js/admin-metabox.js') 
                : '2.1.0';
            wp_enqueue_script(
                'gmb-ranker-admin-metabox',
                class_exists('GMB_Ranker_SEO_Helpers') ? GMB_Ranker_SEO_Helpers::asset_url('js/admin-metabox.js') : plugins_url('assets/js/admin-metabox.js', dirname(dirname(__FILE__))),
                array('jquery'),
                $js_ver,
                true
            );

            wp_localize_script('gmb-ranker-admin-metabox', 'gmbMetaboxData', array(
                'ajaxUrl'            => admin_url('admin-ajax.php'),
                'nonce'              => wp_create_nonce('gmb_admin_ajax_nonce'),
                'postId'             => get_the_ID(),
                'tocMinHeadings'     => (int) get_option('gmb_toc_min_headings', 2),
                'moduleSchema'       => get_option('gmb_ranker_module_schema', '1') !== '0' && get_option('gmb_ranker_module_schema', '1') !== 'off',
                'moduleImageSeo'     => get_option('gmb_ranker_module_image_seo', '1') !== '0' && get_option('gmb_ranker_module_image_seo', '1') !== 'off',
                'moduleLinks'        => get_option('gmb_ranker_module_links', '1') !== '0' && get_option('gmb_ranker_module_links', '1') !== 'off',
                'moduleLlmstxt'      => get_option('gmb_ranker_module_llmstxt', '1') !== '0' && get_option('gmb_ranker_module_llmstxt', '1') !== 'off',
                'moduleSitemaps'     => get_option('gmb_ranker_module_sitemaps', '1') !== '0' && get_option('gmb_ranker_module_sitemaps', '1') !== 'off',
                'moduleInstantIndex' => get_option('gmb_ranker_module_instant_indexing', '1') !== '0' && get_option('gmb_ranker_module_instant_indexing', '1') !== 'off',
                'moduleSecurity'     => get_option('gmb_ranker_module_security', '1') !== '0' && get_option('gmb_ranker_module_security', '1') !== 'off',
            ));
        }
    }

    /**
     * Add SEO metabox to supported post types
     */
    public function add_seo_metabox() {
        if (class_exists('GMB_Ranker_SEO_Role_Manager') && !GMB_Ranker_SEO_Role_Manager::check_user_cap('gmb_ranker_edit_metadata')) {
            return;
        }

        $screens = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_eligible_post_types() : array('post', 'page');

        foreach ($screens as $screen) {
            add_meta_box(
                'gmb_ranker_seo_box',
                __('GMB Ranker SEO Optimization', 'gmb-ranker-seo-automation'),
                array($this, 'render_seo_metabox'),
                $screen,
                'normal',
                'high'
            );
        }
    }

    /**
     * Add score badge inside publish metabox
     *
     * @param WP_Post $post
     */
    public function add_seo_score_to_publish_metabox($post) {
        $post_type = get_post_type($post);
        $supported = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_eligible_post_types() : array('post', 'page');

        if (!in_array($post_type, $supported, true)) {
            return;
        }

        $score = get_post_meta($post->ID, '_gmb_ranker_seo_score', true);
        if ($score === '' || $score === false) {
            $score = '0';
        }
        $class = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_score_badge_class($score, 'publish') : 'orange';
        ?>
        <div class="misc-pub-section gmb-seo-publish-score-section">
            <span class="gmb-text-bold"><?php esc_html_e('SEO: ', 'gmb-ranker-seo-automation'); ?></span>
            <span id="gmb-publish-score-val" class="gmb-publish-score-badge <?php echo esc_attr($class); ?>">
                <?php echo esc_html($score); ?> / 100
            </span>
        </div>
        <?php
    }

    /**
     * Render SEO Metabox view template
     *
     * @param WP_Post $post
     */
    public function render_seo_metabox($post) {
        if (class_exists('GMB_Ranker_SEO_Helpers')) {
            GMB_Ranker_SEO_Helpers::render_view('metabox/post-metabox.php', array(
                'post' => $post,
            ));
        }
    }

    /**
     * Save SEO metabox data
     *
     * @param int $post_id
     */
    public function save_seo_metabox_data($post_id) {
        if (class_exists('GMB_Ranker_SEO_Role_Manager') && !GMB_Ranker_SEO_Role_Manager::check_user_cap('gmb_ranker_edit_metadata')) {
            return;
        }

        if (!isset($_POST['gmb_seo_nonce']) || !wp_verify_nonce($_POST['gmb_seo_nonce'], 'gmb_seo_save_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (class_exists('GMB_Ranker_SEO_Metabox_Registry')) {
            GMB_Ranker_SEO_Metabox_Registry::save_post_metadata($post_id, $_POST);
        }
    }

    /**
     * Add SEO Score & Focus Keyword columns to post listing
     *
     * @param array $columns
     * @return array
     */
    public function add_seo_columns($columns) {
        $columns['gmb_seo_score'] = __('SEO Score', 'gmb-ranker-seo-automation');
        $columns['gmb_focus_kw']  = __('Focus Keyword', 'gmb-ranker-seo-automation');
        return $columns;
    }

    /**
     * Render SEO Score & Focus Keyword column content
     *
     * @param string $column
     * @param int $post_id
     */
    public function render_seo_columns($column, $post_id) {
        if ($column === 'gmb_seo_score') {
            $score = get_post_meta($post_id, '_gmb_ranker_seo_score', true);
            
            // Check Rank Math or Yoast SEO score fallback if empty
            if ($score === '' || $score === false) {
                $rm_score = get_post_meta($post_id, 'rank_math_seo_score', true);
                if ($rm_score !== '' && $rm_score !== false) {
                    $score = $rm_score;
                } else {
                    $yoast_score = get_post_meta($post_id, '_yoast_wpseo_linkdex', true);
                    if ($yoast_score !== '' && $yoast_score !== false) {
                        $score = $yoast_score;
                    }
                }
            }

            $score_num   = ($score !== '' && $score !== false) ? intval($score) : 0;
            $badge_class = class_exists('GMB_Ranker_SEO_Metabox_Registry') ? GMB_Ranker_SEO_Metabox_Registry::get_score_badge_class($score_num, 'table') : 'gmb-score-badge--poor';

            if ($score_num > 0) {
                echo '<span class="gmb-score-badge ' . esc_attr($badge_class) . '"><strong>' . esc_html($score_num) . '</strong> / 100</span>';
            } else {
                echo '<span class="gmb-score-badge gmb-score-badge--poor"><strong>0</strong> / 100</span>';
            }

        } elseif ($column === 'gmb_focus_kw') {
            $kw = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true);
            if (empty($kw)) {
                $kw = get_post_meta($post_id, 'rank_math_focus_keyword', true);
            }
            if (empty($kw)) {
                $kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            }

            if (!empty($kw)) {
                echo '<span class="gmb-focus-kw-tag">' . esc_html($kw) . '</span>';
            } else {
                echo '<span class="gmb-text-muted">—</span>';
            }
        }
    }

    /**
     * Render author profile SEO fields
     *
     * @param WP_User $user
     */
    public function render_user_profile_seo_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }

        $author_custom_title = get_the_author_meta('gmb_author_custom_title', $user->ID);
        $author_custom_desc  = get_the_author_meta('gmb_author_custom_desc', $user->ID);
        $author_noindex      = get_the_author_meta('gmb_author_noindex', $user->ID);
        $author_same_as      = get_the_author_meta('gmb_author_same_as', $user->ID);

        wp_nonce_field('gmb_author_seo_save_action', 'gmb_author_seo_nonce');
        ?>
        <h2><?php esc_html_e('GMB Ranker SEO — Author Settings & E-E-A-T', 'gmb-ranker-seo-automation'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="gmb_author_custom_title"><?php esc_html_e('Custom Author Archive Title', 'gmb-ranker-seo-automation'); ?></label></th>
                <td>
                    <input type="text" name="gmb_author_custom_title" id="gmb_author_custom_title" value="<?php echo esc_attr($author_custom_title); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Overrides default author archive page title tag.', 'gmb-ranker-seo-automation'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_custom_desc"><?php esc_html_e('Custom Author Meta Description', 'gmb-ranker-seo-automation'); ?></label></th>
                <td>
                    <textarea name="gmb_author_custom_desc" id="gmb_author_custom_desc" rows="3" cols="50" class="large-text"><?php echo esc_textarea($author_custom_desc); ?></textarea>
                    <p class="description"><?php esc_html_e('Overrides author archive meta description for search snippets.', 'gmb-ranker-seo-automation'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_noindex"><?php esc_html_e('Robots Meta', 'gmb-ranker-seo-automation'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="gmb_author_noindex" id="gmb_author_noindex" value="1" <?php checked('1', $author_noindex); ?> />
                        <?php esc_html_e('Exclude this author\'s archive from search indexing (noindex, follow)', 'gmb-ranker-seo-automation'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_same_as"><?php esc_html_e('Schema sameAs Profile URLs', 'gmb-ranker-seo-automation'); ?></label></th>
                <td>
                    <textarea name="gmb_author_same_as" id="gmb_author_same_as" rows="3" cols="50" class="large-text" placeholder="https://www.wikidata.org/wiki/...&#10;https://scholar.google.com/..."><?php echo esc_textarea($author_same_as); ?></textarea>
                    <p class="description"><?php esc_html_e('One URL per line. Injects sameAs entity verification into Author Person Schema.', 'gmb-ranker-seo-automation'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save author profile SEO fields
     *
     * @param int $user_id
     */
    public function save_user_profile_seo_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        if (isset($_POST['gmb_author_seo_nonce']) && !wp_verify_nonce($_POST['gmb_author_seo_nonce'], 'gmb_author_seo_save_action')) {
            return;
        }

        if (class_exists('GMB_Ranker_SEO_Metabox_Registry')) {
            GMB_Ranker_SEO_Metabox_Registry::save_author_metadata($user_id, $_POST);
        }
    }

    /**
     * Render Single Post AI SEO Auto-Fix Modal in admin_footer
     */
    public function render_ai_post_modal() {
        if (!is_admin()) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && $screen->base !== 'post') {
            return;
        }

        if (class_exists('GMB_Ranker_SEO_Helpers')) {
            GMB_Ranker_SEO_Helpers::render_view('metabox/ai-post-modal.php');
        }
    }
}
