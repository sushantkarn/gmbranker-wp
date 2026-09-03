<?php
/**
 * Editor Metabox & Content Analysis Controller
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
        if (get_option('gmb_ranker_module_metadata', '1') === '0') {
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
            $css_ver = defined('GMB_RANKER_SEO_PATH') && file_exists(GMB_RANKER_SEO_PATH . 'assets/css/admin-metabox.css') 
                ? filemtime(GMB_RANKER_SEO_PATH . 'assets/css/admin-metabox.css') 
                : '2.1.0';
            wp_enqueue_style(
                'gmb-ranker-admin-metabox',
                GMB_Ranker_SEO_Helpers::asset_url('css/admin-metabox.css'),
                array(),
                $css_ver
            );
        }

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_media();
            $js_ver = defined('GMB_RANKER_SEO_PATH') && file_exists(GMB_RANKER_SEO_PATH . 'assets/js/admin-metabox.js') 
                ? filemtime(GMB_RANKER_SEO_PATH . 'assets/js/admin-metabox.js') 
                : '2.1.0';
            wp_enqueue_script(
                'gmb-ranker-admin-metabox',
                GMB_Ranker_SEO_Helpers::asset_url('js/admin-metabox.js'),
                array('jquery'),
                $js_ver,
                true
            );
            global $post;
            $post_type = $post ? get_post_type($post) : 'post';
            $default_pt_schema = get_option('gmb_' . $post_type . '_schema_type', ($post_type === 'page' ? 'WebPage' : 'Article'));

            wp_localize_script('gmb-ranker-admin-metabox', 'gmbMetaboxData', array(
                'ajaxUrl'            => admin_url('admin-ajax.php'),
                'nonce'              => wp_create_nonce('gmb_admin_ajax_nonce'),
                'tocMinHeadings'     => (int) get_option('gmb_toc_min_headings', 2),
                'moduleSchema'       => get_option('gmb_ranker_module_schema', '1') === '1',
                'moduleImageSeo'     => get_option('gmb_ranker_module_image_seo', '1') === '1',
                'moduleLinks'        => get_option('gmb_ranker_module_links', '1') === '1',
                'moduleLlmstxt'      => get_option('gmb_ranker_module_llmstxt', '1') === '1',
                'moduleSitemaps'     => get_option('gmb_ranker_module_sitemaps', '1') === '1',
                'moduleInstantIndex' => get_option('gmb_ranker_module_instant_indexing', '1') === '1',
                'moduleSecurity'     => get_option('gmb_ranker_module_security', '1') === '1',
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

        $public_types = get_post_types(array('public' => true), 'names');
        unset($public_types['attachment'], $public_types['revision'], $public_types['nav_menu_item'], $public_types['custom_css'], $public_types['customize_changeset']);
        $screens = !empty($public_types) ? array_values($public_types) : array('post', 'page', 'product');

        foreach ($screens as $screen) {
            add_meta_box(
                'gmb_ranker_seo_box',
                'GMB Ranker SEO Optimization',
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
        $public_types = get_post_types(array('public' => true), 'names');
        unset($public_types['attachment'], $public_types['revision'], $public_types['nav_menu_item']);
        $supported = !empty($public_types) ? array_values($public_types) : array('post', 'page', 'product');

        if (!in_array($post_type, $supported, true)) {
            return;
        }

        $score = get_post_meta($post->ID, '_gmb_ranker_seo_score', true);
        if ($score === '') {
            $score = '0';
        }
        $class = 'orange';
        if ($score >= 80) {
            $class = 'green';
        } elseif ($score < 60) {
            $class = 'red';
        }
        ?>
        <div class="misc-pub-section gmb-seo-publish-score-section">
            <span class="gmb-text-bold">SEO: </span>
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
        GMB_Ranker_SEO_Helpers::render_view('metabox/post-metabox.php', array(
            'post' => $post,
        ));
    }

    /**
     * Save SEO metabox data
     *
     * @param int $post_id
     */
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

        // Title
        if (isset($_POST['gmb_seo_title'])) {
            $title = sanitize_text_field(wp_unslash($_POST['gmb_seo_title']));
            update_post_meta($post_id, '_gmb_ranker_seo_title', $title);
            update_post_meta($post_id, '_yoast_wpseo_title', $title);
            update_post_meta($post_id, 'rank_math_title', $title);
            update_post_meta($post_id, '_rank_math_title', $title);
            update_post_meta($post_id, '_aioseo_title', $title);
            update_post_meta($post_id, '_seopress_titles_title', $title);
        }

        // Description
        if (isset($_POST['gmb_seo_description'])) {
            $desc = sanitize_textarea_field(wp_unslash($_POST['gmb_seo_description']));
            update_post_meta($post_id, '_gmb_ranker_seo_description', $desc);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
            update_post_meta($post_id, 'rank_math_description', $desc);
            update_post_meta($post_id, '_rank_math_description', $desc);
            update_post_meta($post_id, '_aioseo_description', $desc);
            update_post_meta($post_id, '_seopress_titles_desc', $desc);
        }

        // Canonical
        if (isset($_POST['gmb_seo_canonical'])) {
            $canonical = esc_url_raw(wp_unslash($_POST['gmb_seo_canonical']));
            update_post_meta($post_id, '_gmb_ranker_seo_canonical', $canonical);
            update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical);
            update_post_meta($post_id, 'rank_math_canonical_url', $canonical);
        }

        // Robots
        if (isset($_POST['gmb_seo_robots'])) {
            if (is_array($_POST['gmb_seo_robots'])) {
                $robots_arr = array_map('sanitize_text_field', $_POST['gmb_seo_robots']);
                update_post_meta($post_id, '_gmb_ranker_seo_robots', implode(', ', $robots_arr));
            } else {
                $robots_str = sanitize_text_field(wp_unslash($_POST['gmb_seo_robots']));
                update_post_meta($post_id, '_gmb_ranker_seo_robots', $robots_str);
            }
        } else {
            update_post_meta($post_id, '_gmb_ranker_seo_robots', '');
        }

        // Advanced Robots Controls
        if (isset($_POST['gmb_seo_max_snippet'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_snippet', sanitize_text_field(wp_unslash($_POST['gmb_seo_max_snippet'])));
        }
        if (isset($_POST['gmb_seo_max_video'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_video', sanitize_text_field(wp_unslash($_POST['gmb_seo_max_video'])));
        }
        if (isset($_POST['gmb_seo_max_image'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_image', sanitize_text_field(wp_unslash($_POST['gmb_seo_max_image'])));
        }

        // Breadcrumb Title Override
        if (isset($_POST['gmb_seo_breadcrumb_title'])) {
            update_post_meta($post_id, '_gmb_ranker_breadcrumb_title', sanitize_text_field(wp_unslash($_POST['gmb_seo_breadcrumb_title'])));
        }

        // Page Redirection
        if (isset($_POST['gmb_seo_redirect_url'])) {
            update_post_meta($post_id, '_gmb_ranker_redirect_url', esc_url_raw(wp_unslash($_POST['gmb_seo_redirect_url'])));
        }
        if (isset($_POST['gmb_seo_redirect_code'])) {
            update_post_meta($post_id, '_gmb_ranker_redirect_code', sanitize_text_field(wp_unslash($_POST['gmb_seo_redirect_code'])));
        }

        // Focus Keyword
        $focus_kw = '';
        if (isset($_POST['gmb_seo_focus_keyword'])) {
            $focus_kw = sanitize_text_field(wp_unslash($_POST['gmb_seo_focus_keyword']));
        } elseif (isset($_POST['gmb_seo_focus_kw'])) {
            $focus_kw = sanitize_text_field(wp_unslash($_POST['gmb_seo_focus_kw']));
        }
        if (!empty($focus_kw)) {
            update_post_meta($post_id, '_gmb_ranker_focus_keyword', $focus_kw);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_kw);
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus_kw);
        }

        // Pillar Content
        $is_pillar = isset($_POST['gmb_seo_is_pillar']) ? '1' : '0';
        update_post_meta($post_id, '_gmb_ranker_seo_is_pillar', $is_pillar);

        // SEO Score
        if (isset($_POST['gmb_seo_score'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_score', intval(wp_unslash($_POST['gmb_seo_score'])));
        } elseif (isset($_POST['gmb_seo_score_hidden'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_score', intval(wp_unslash($_POST['gmb_seo_score_hidden'])));
        }

        // Schema JSON-LD & Active Schemas
        if (isset($_POST['gmb_seo_active_schemas'])) {
            $active_schemas_raw = sanitize_text_field(wp_unslash($_POST['gmb_seo_active_schemas']));
            $active_schemas_arr = !empty($active_schemas_raw) ? array_filter(array_map('trim', explode(',', $active_schemas_raw))) : array();
            update_post_meta($post_id, '_gmb_ranker_active_schemas', $active_schemas_arr);
            if (!empty($active_schemas_arr)) {
                $primary_schema = reset($active_schemas_arr);
                update_post_meta($post_id, '_gmb_ranker_schema_type', $primary_schema);
                update_post_meta($post_id, 'rank_math_rich_snippet', strtolower($primary_schema));
            }
        }

        if (isset($_POST['gmb_seo_schema'])) {
            $schema_clean = wp_unslash($_POST['gmb_seo_schema']);
            update_post_meta($post_id, '_gmb_ranker_seo_schema', $schema_clean);
            update_post_meta($post_id, '_gmb_ranker_json_ld', $schema_clean);
        }

        // Facebook (OpenGraph)
        if (isset($_POST['gmb_seo_facebook_title'])) {
            $fb_title = sanitize_text_field(wp_unslash($_POST['gmb_seo_facebook_title']));
            update_post_meta($post_id, '_gmb_ranker_facebook_title', $fb_title);
            update_post_meta($post_id, '_gmb_ranker_og_title', $fb_title);
        }
        if (isset($_POST['gmb_seo_facebook_desc'])) {
            $fb_desc = sanitize_textarea_field(wp_unslash($_POST['gmb_seo_facebook_desc']));
            update_post_meta($post_id, '_gmb_ranker_facebook_desc', $fb_desc);
            update_post_meta($post_id, '_gmb_ranker_og_description', $fb_desc);
        }
        if (isset($_POST['gmb_seo_facebook_image'])) {
            $fb_img = esc_url_raw(wp_unslash($_POST['gmb_seo_facebook_image']));
            update_post_meta($post_id, '_gmb_ranker_facebook_image', $fb_img);
            update_post_meta($post_id, '_gmb_ranker_og_image', $fb_img);
        }

        // Twitter / X
        if (isset($_POST['gmb_seo_twitter_card_type'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_card_type', sanitize_text_field(wp_unslash($_POST['gmb_seo_twitter_card_type'])));
        }
        if (isset($_POST['gmb_seo_twitter_title'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_title', sanitize_text_field(wp_unslash($_POST['gmb_seo_twitter_title'])));
        }
        if (isset($_POST['gmb_seo_twitter_desc'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_desc', sanitize_textarea_field(wp_unslash($_POST['gmb_seo_twitter_desc'])));
            update_post_meta($post_id, '_gmb_ranker_twitter_description', sanitize_textarea_field(wp_unslash($_POST['gmb_seo_twitter_desc'])));
        }
        if (isset($_POST['gmb_seo_twitter_image'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_image', esc_url_raw(wp_unslash($_POST['gmb_seo_twitter_image'])));
        }
    }

    /**
     * Add SEO Score & Focus Keyword columns to post listing
     *
     * @param array $columns
     * @return array
     */
    public function add_seo_columns($columns) {
        $columns['gmb_seo_score'] = 'SEO Score';
        $columns['gmb_focus_kw'] = 'Focus Keyword';
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

            // If still empty, calculate on-page SEO score dynamically
            if ($score === '' || $score === false) {
                if (class_exists('GMB_Ranker_SEO_Analysis')) {
                    $analysis = GMB_Ranker_SEO_Analysis::run_onpage_audits($post_id);
                    $score = isset($analysis['score']) ? intval($analysis['score']) : 0;
                    if ($score === 0) {
                        $post_obj = get_post($post_id);
                        if (!empty($post_obj) && strlen(trim($post_obj->post_content)) > 30) {
                            $score = 45; // baseline readable draft/published post
                        }
                    }
                    if ($score > 0) {
                        update_post_meta($post_id, '_gmb_ranker_seo_score', $score);
                    }
                } else {
                    $score = 0;
                }
            }

            $score_num = intval($score);
            $badge_class = 'gmb-score-badge--poor';
            if ($score_num >= 80) {
                $badge_class = 'gmb-score-badge--good';
            } elseif ($score_num >= 50) {
                $badge_class = 'gmb-score-badge--ok';
            }

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
        $author_custom_title = get_the_author_meta('gmb_author_custom_title', $user->ID);
        $author_custom_desc = get_the_author_meta('gmb_author_custom_desc', $user->ID);
        $author_noindex = get_the_author_meta('gmb_author_noindex', $user->ID);
        $author_facebook = get_the_author_meta('gmb_author_facebook', $user->ID);
        $author_twitter = get_the_author_meta('gmb_author_twitter', $user->ID);
        $author_linkedin = get_the_author_meta('gmb_author_linkedin', $user->ID);
        $author_same_as = get_the_author_meta('gmb_author_same_as', $user->ID);
        ?>
        <h2>GMB Ranker SEO - Author Settings &amp; E-E-A-T</h2>
        <table class="form-table">
            <tr>
                <th><label for="gmb_author_custom_title">Custom Author Archive Title</label></th>
                <td>
                    <input type="text" name="gmb_author_custom_title" id="gmb_author_custom_title" value="<?php echo esc_attr($author_custom_title); ?>" class="regular-text" />
                    <p class="description">Overrides default author archive page title tag.</p>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_custom_desc">Custom Author Meta Description</label></th>
                <td>
                    <textarea name="gmb_author_custom_desc" id="gmb_author_custom_desc" rows="3" cols="50" class="large-text"><?php echo esc_textarea($author_custom_desc); ?></textarea>
                    <p class="description">Overrides author archive meta description for search snippets.</p>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_noindex">Robots Meta</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="gmb_author_noindex" id="gmb_author_noindex" value="1" <?php checked('1', $author_noindex); ?> />
                        Exclude this author's archive from search indexing (noindex, follow)
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="gmb_author_same_as">Schema sameAs Profile URLs</label></th>
                <td>
                    <textarea name="gmb_author_same_as" id="gmb_author_same_as" rows="3" cols="50" class="large-text" placeholder="https://www.wikidata.org/wiki/...&#10;https://scholar.google.com/..."><?php echo esc_textarea($author_same_as); ?></textarea>
                    <p class="description">One URL per line. Injects sameAs entity verification into Author Person Schema.</p>
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

        if (isset($_POST['gmb_author_custom_title'])) {
            update_user_meta($user_id, 'gmb_author_custom_title', sanitize_text_field(wp_unslash($_POST['gmb_author_custom_title'])));
        }
        if (isset($_POST['gmb_author_custom_desc'])) {
            update_user_meta($user_id, 'gmb_author_custom_desc', sanitize_textarea_field(wp_unslash($_POST['gmb_author_custom_desc'])));
        }
        $noindex = isset($_POST['gmb_author_noindex']) ? '1' : '0';
        update_user_meta($user_id, 'gmb_author_noindex', $noindex);
        if (isset($_POST['gmb_author_same_as'])) {
            update_user_meta($user_id, 'gmb_author_same_as', sanitize_textarea_field(wp_unslash($_POST['gmb_author_same_as'])));
        }
    }

    /**
     * Render Single Post AI SEO Auto-Fix Modal in admin_footer
     */
    public function render_ai_post_modal() {
        if (!is_admin()) {
            return;
        }
        global $post;
        $post_id = ($post && isset($post->ID)) ? $post->ID : 0;
        $focus_keyword = $post_id ? get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) : '';
        ?>
        <!-- Single Page AI SEO Optimizer Modal (NeuronWriter-Style 3-Step Flow) -->
        <div id="gmb-ai-post-seo-modal" class="gmb-modal-overlay">
            <div class="gmb-modal-container gmb-modal-lg">
                <div class="gmb-modal-header">
                    <div class="gmb-modal-header-flex">
                        <div>
                            <h3 class="gmb-modal-title">AI SEO Master Strategist</h3>
                            <p class="gmb-modal-subtitle">Data-driven SEO research. Real insights. Better rankings.</p>
                        </div>
                        <div class="gmb-modal-stepper">
                            <span class="gmb-step-badge active" id="gmb-step-badge-1">1. Query Setup</span>
                            <span class="gmb-step-arrow">&rarr;</span>
                            <span class="gmb-step-badge" id="gmb-step-badge-2">2. AI Research</span>
                            <span class="gmb-step-arrow">&rarr;</span>
                            <span class="gmb-step-badge" id="gmb-step-badge-3">3. Optimization</span>
                        </div>
                    </div>
                    <button type="button" class="gmb-modal-close" id="gmb-ai-post-modal-close">&times;</button>
                </div>
                <div class="gmb-modal-body">
                    <!-- STEP 1: Query Setup & Target Region Selection (NeuronWriter Style) -->
                    <div id="gmb-ai-post-modal-setup" class="gmb-ai-setup-card">
                        <div class="gmb-setup-form-grid">
                            <div class="gmb-form-group gmb-col-12">
                                <label class="gmb-form-label">Target URL / Permalink</label>
                                <input type="text" id="gmb-ai-setup-url" class="gmb-integration-input" readonly />
                            </div>
                            <div class="gmb-form-group gmb-col-12">
                                <label class="gmb-form-label">Article Title</label>
                                <input type="text" id="gmb-ai-setup-title" class="gmb-integration-input" placeholder="Enter target article title..." />
                            </div>
                            <div class="gmb-form-group gmb-col-12">
                                <label class="gmb-form-label">What query do you want to rank for? (Target Focus Keyword)</label>
                                <input type="text" id="gmb-ai-setup-query" class="gmb-integration-input gmb-input-lg" value="<?php echo esc_attr($focus_keyword); ?>" placeholder="e.g. Best SEO Strategies..." />
                            </div>
                            <div class="gmb-form-group gmb-col-4">
                                <label class="gmb-form-label">Mode</label>
                                <select id="gmb-ai-setup-mode" class="gmb-integration-select">
                                    <option value="optimize" selected>⚡ Optimize (Improve existing content)</option>
                                    <option value="create">✍️ Create new (Start from keyword)</option>
                                    <option value="deep_serp">🎯 Deep SERP Entity Benchmark</option>
                                </select>
                            </div>
                            <div class="gmb-form-group gmb-col-4">
                                <label class="gmb-form-label">Target Search Engine / Country</label>
                                <select id="gmb-ai-setup-country" class="gmb-integration-select">
                                    <optgroup label="Popular Regions">
                                        <option value="NP|google.com.np" selected>🇳🇵 NEPAL | google.com.np</option>
                                        <option value="US|google.com">🇺🇸 UNITED STATES | google.com</option>
                                        <option value="GB|google.co.uk">🇬🇧 UNITED KINGDOM | google.co.uk</option>
                                        <option value="CA|google.ca">🇨🇦 CANADA | google.ca</option>
                                        <option value="AU|google.com.au">🇦🇺 AUSTRALIA | google.com.au</option>
                                        <option value="IN|google.co.in">🇮🇳 INDIA | google.co.in</option>
                                        <option value="GLOBAL|google.com">🌐 GLOBAL | google.com</option>
                                    </optgroup>
                                    <optgroup label="Asia & Pacific">
                                        <option value="JP|google.co.jp">🇯🇵 JAPAN | google.co.jp</option>
                                        <option value="SG|google.com.sg">🇸🇬 SINGAPORE | google.com.sg</option>
                                        <option value="KR|google.co.kr">🇰🇷 SOUTH KOREA | google.co.kr</option>
                                        <option value="MY|google.com.my">🇲🇾 MALAYSIA | google.com.my</option>
                                        <option value="ID|google.co.id">🇮🇩 INDONESIA | google.co.id</option>
                                        <option value="TH|google.co.th">🇹🇭 THAILAND | google.co.th</option>
                                        <option value="VN|google.com.vn">🇻🇳 VIETNAM | google.com.vn</option>
                                        <option value="PH|google.com.ph">🇵🇭 PHILIPPINES | google.com.ph</option>
                                        <option value="PK|google.com.pk">🇵🇰 PAKISTAN | google.com.pk</option>
                                        <option value="BD|google.com.bd">🇧🇩 BANGLADESH | google.com.bd</option>
                                        <option value="NZ|google.co.nz">🇳🇿 NEW ZEALAND | google.co.nz</option>
                                    </optgroup>
                                    <optgroup label="Europe">
                                        <option value="DE|google.de">🇩🇪 GERMANY | google.de</option>
                                        <option value="FR|google.fr">🇫🇷 FRANCE | google.fr</option>
                                        <option value="ES|google.es">🇪🇸 SPAIN | google.es</option>
                                        <option value="IT|google.it">🇮🇹 ITALY | google.it</option>
                                        <option value="NL|google.nl">🇳🇱 NETHERLANDS | google.nl</option>
                                        <option value="SE|google.se">🇸🇪 SWEDEN | google.se</option>
                                        <option value="NO|google.no">🇳🇴 NORWAY | google.no</option>
                                        <option value="DK|google.dk">🇩🇰 DENMARK | google.dk</option>
                                        <option value="FI|google.fi">🇫🇮 FINLAND | google.fi</option>
                                        <option value="PL|google.pl">🇵🇱 POLAND | google.pl</option>
                                        <option value="IE|google.ie">🇮🇪 IRELAND | google.ie</option>
                                        <option value="PT|google.pt">🇵🇹 PORTUGAL | google.pt</option>
                                        <option value="GR|google.gr">🇬🇷 GREECE | google.gr</option>
                                        <option value="TR|google.com.tr">🇹🇷 TURKEY | google.com.tr</option>
                                    </optgroup>
                                    <optgroup label="Americas">
                                        <option value="BR|google.com.br">🇧🇷 BRAZIL | google.com.br</option>
                                        <option value="MX|google.com.mx">🇲🇽 MEXICO | google.com.mx</option>
                                        <option value="AR|google.com.ar">🇦🇷 ARGENTINA | google.com.ar</option>
                                        <option value="CL|google.cl">🇨🇱 CHILE | google.cl</option>
                                        <option value="CO|google.com.co">🇨🇴 COLOMBIA | google.com.co</option>
                                        <option value="PE|google.com.pe">🇵🇪 PERU | google.com.pe</option>
                                    </optgroup>
                                    <optgroup label="Middle East & Africa">
                                        <option value="AE|google.ae">🇦🇪 UAE | google.ae</option>
                                        <option value="SA|google.com.sa">🇸🇦 SAUDI ARABIA | google.com.sa</option>
                                        <option value="IL|google.co.il">🇮🇱 ISRAEL | google.co.il</option>
                                        <option value="EG|google.com.eg">🇪🇬 EGYPT | google.com.eg</option>
                                        <option value="ZA|google.co.za">🇿🇦 SOUTH AFRICA | google.co.za</option>
                                        <option value="NG|google.com.ng">🇳🇬 NIGERIA | google.com.ng</option>
                                        <option value="KE|google.co.ke">🇰🇪 KENYA | google.co.ke</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="gmb-form-group gmb-col-4">
                                <label class="gmb-form-label">Language</label>
                                <select id="gmb-ai-setup-language" class="gmb-integration-select">
                                    <option value="en" selected>🇬🇧 English</option>
                                    <option value="ne">🇳🇵 Nepali (नेपाली)</option>
                                    <option value="es">🇪🇸 Spanish (Español)</option>
                                    <option value="fr">🇫🇷 French (Français)</option>
                                    <option value="de">🇩🇪 German (Deutsch)</option>
                                    <option value="it">🇮🇹 Italian (Italiano)</option>
                                    <option value="pt">🇵🇹 Portuguese (Português)</option>
                                    <option value="nl">🇳🇱 Dutch (Nederlands)</option>
                                    <option value="ja">🇯🇵 Japanese (日本語)</option>
                                    <option value="zh-cn">🇨🇳 Chinese Simplified (简体中文)</option>
                                    <option value="zh-tw">🇹🇼 Chinese Traditional (繁體中文)</option>
                                    <option value="ar">🇸🇦 Arabic (العربية)</option>
                                    <option value="hi">🇮🇳 Hindi (हिन्दी)</option>
                                    <option value="bn">🇧🇩 Bengali (বাংলা)</option>
                                    <option value="ru">🇷🇺 Russian (Русский)</option>
                                    <option value="sv">🇸🇪 Swedish (Svenska)</option>
                                    <option value="no">🇳🇴 Norwegian (Norsk)</option>
                                    <option value="da">🇩🇰 Danish (Dansk)</option>
                                    <option value="fi">🇫🇮 Finnish (Suomi)</option>
                                    <option value="pl">🇵🇱 Polish (Polski)</option>
                                    <option value="tr">🇹🇷 Turkish (Türkçe)</option>
                                    <option value="id">🇮🇩 Indonesian (Bahasa Indonesia)</option>
                                    <option value="vi">🇻🇳 Vietnamese (Tiếng Việt)</option>
                                    <option value="th">🇹🇭 Thai (ไทย)</option>
                                    <option value="ko">🇰🇷 Korean (한국어)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Modern 3-Column AI Research Workspace -->
                    <div id="gmb-ai-post-modal-loading" class="gmb-ai-research-dashboard gmb-hidden">
                        <!-- Top Live SERP Status Bar -->
                        <div class="gmb-serp-status-bar gmb-mb-16">
                            <div class="gmb-serp-status-left">
                                <svg class="gmb-google-svg" viewBox="0 0 24 24" width="18" height="18">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <strong class="gmb-serp-title">Real SERP Data</strong>
                                <span class="gmb-serp-divider">|</span>
                                <span class="gmb-serp-label">Fetching live results for:</span>
                                <span class="gmb-serp-kw-pill" id="gmb-serp-kw-pill">Target Query</span>
                            </div>
                        </div>

                        <!-- 3-Column Research Workspace Grid -->
                        <div class="gmb-research-3col-grid">
                            <!-- COLUMN 1: Research Steps Timeline (8 Steps) -->
                            <div class="gmb-research-col-steps">
                                <h4 class="gmb-research-col-heading">Research Steps</h4>
                                <div class="gmb-steps-timeline">
                                    <div class="gmb-step-item active" id="gmb-res-step-1">
                                        <div class="gmb-step-num-icon">1</div>
                                        <div class="gmb-step-text">
                                            <strong>Analyzing Current Page</strong>
                                            <small>Extracting content, metadata & SEO signals</small>
                                        </div>
                                        <div class="gmb-step-active-ring"></div>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-2">
                                        <div class="gmb-step-num-icon">2</div>
                                        <div class="gmb-step-text">
                                            <strong>Detecting Search Intent</strong>
                                            <small>Analyzing query intent & SERP features</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-3">
                                        <div class="gmb-step-num-icon">3</div>
                                        <div class="gmb-step-text">
                                            <strong>Fetching SERP Results</strong>
                                            <small>Collecting top ranking pages</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-4">
                                        <div class="gmb-step-num-icon">4</div>
                                        <div class="gmb-step-text">
                                            <strong>Analyzing Competitors</strong>
                                            <small>Extracting content & SEO data</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-5">
                                        <div class="gmb-step-num-icon">5</div>
                                        <div class="gmb-step-text">
                                            <strong>Semantic & Entity Analysis</strong>
                                            <small>Building topic and entity model</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-6">
                                        <div class="gmb-step-num-icon">6</div>
                                        <div class="gmb-step-text">
                                            <strong>Content Gap Analysis</strong>
                                            <small>Identifying missing opportunities</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-7">
                                        <div class="gmb-step-num-icon">7</div>
                                        <div class="gmb-step-text">
                                            <strong>Optimization Strategy</strong>
                                            <small>Generating evidence-based recommendations</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                    <div class="gmb-step-item" id="gmb-res-step-8">
                                        <div class="gmb-step-num-icon">8</div>
                                        <div class="gmb-step-text">
                                            <strong>Finalizing Results</strong>
                                            <small>Validating data & preparing your report</small>
                                        </div>
                                        <span class="gmb-step-status-pill pending">Pending</span>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMN 2: Active Step Execution Panel -->
                            <div class="gmb-research-col-center">
                                <div class="gmb-active-step-card">
                                    <span class="gmb-step-counter-badge" id="gmb-active-step-counter">Step 1 of 8</span>
                                    <h3 class="gmb-active-step-title" id="gmb-active-step-title">Analyzing Current Page Structure & Metadata</h3>
                                    <p class="gmb-active-step-desc" id="gmb-active-step-desc">We're extracting and evaluating key elements from your WordPress post.</p>

                                    <!-- Animated Progress Bar -->
                                    <div class="gmb-progress-bar-wrap">
                                        <div class="gmb-progress-bar-track">
                                            <div class="gmb-progress-bar-fill" id="gmb-active-progress-fill" style="width: 65%;"></div>
                                        </div>
                                        <span class="gmb-progress-percent" id="gmb-active-progress-percent">65%</span>
                                    </div>

                                    <!-- Live Dynamic Tasks List -->
                                    <div class="gmb-live-tasks-list" id="gmb-live-tasks-list">
                                        <div class="gmb-task-row done"><span class="task-check-circle">✓</span> Post content loaded (<span id="gmb-task-word-count">2,314</span> words)</div>
                                        <div class="gmb-task-row done"><span class="task-check-circle">✓</span> Extracting SEO metadata (title, description, schema)</div>
                                        <div class="gmb-task-row done"><span class="task-check-circle">✓</span> Analyzing headings structure (H1-H3)</div>
                                        <div class="gmb-task-row running"><span class="task-spinner"></span> Scanning images and alt text...</div>
                                        <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Analyzing internal and external links</div>
                                        <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Calculating readability metrics</div>
                                        <div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Detecting semantic terms and entities</div>
                                    </div>
                                </div>

                            </div>

                            <!-- COLUMN 3: Overview & What We'll Analyze -->
                            <div class="gmb-research-col-right">
                                <!-- Research Overview Card -->
                                <div class="gmb-side-card">
                                    <h4 class="gmb-side-card-title">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
                                        </svg>
                                        Research Overview
                                    </h4>
                                    <div class="gmb-overview-kv-list">
                                        <div class="gmb-kv-row">
                                            <span class="kv-key">Target Query</span>
                                            <strong class="kv-val" id="gmb-overview-query"><?php echo esc_html($focus_keyword ?: '--'); ?></strong>
                                        </div>
                                        <div class="gmb-kv-row">
                                            <span class="kv-key">Target URL</span>
                                            <a href="<?php echo esc_url($post_id ? get_permalink($post_id) : home_url('/')); ?>" target="_blank" class="kv-val kv-link" id="gmb-overview-url">
                                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                                <?php echo esc_html(home_url('/...')); ?>
                                            </a>
                                        </div>
                                        <div class="gmb-kv-row">
                                            <span class="kv-key">Country / Search Engine</span>
                                            <strong class="kv-val" id="gmb-overview-country">Global (google.com)</strong>
                                        </div>
                                        <div class="gmb-kv-row">
                                            <span class="kv-key">Language</span>
                                            <strong class="kv-val" id="gmb-overview-language">English</strong>
                                        </div>
                                        <div class="gmb-kv-row">
                                            <span class="kv-key">Mode</span>
                                            <strong class="kv-val" id="gmb-overview-mode">Optimize Existing Content</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- What We'll Analyze Card -->
                                <div class="gmb-side-card gmb-mt-16">
                                    <h4 class="gmb-side-card-title">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/><path d="M16.2 7.8l-2 5.6-5.6 2 2-5.6z"/>
                                        </svg>
                                        What We'll Analyze
                                    </h4>
                                    <ul class="gmb-bullets-checklist">
                                        <li><span class="chk-blue">✓</span> Current page SEO health</li>
                                        <li><span class="chk-blue">✓</span> Top 10 SERP competitors</li>
                                        <li><span class="chk-blue">✓</span> Semantic terms & entity coverage</li>
                                        <li><span class="chk-blue">✓</span> Content gaps & missing topics</li>
                                        <li><span class="chk-blue">✓</span> Search intent alignment</li>
                                        <li><span class="chk-blue">✓</span> Readability & content structure</li>
                                        <li><span class="chk-blue">✓</span> Internal link opportunities</li>
                                        <li><span class="chk-blue">✓</span> Evidence-based recommendations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Evidence-Based Recommendations & Controlled Apply Screen -->
                    <div id="gmb-ai-post-modal-content" class="gmb-hidden">
                        <div class="gmb-results-score-banner">
                            <div class="gmb-score-meta">
                                <div class="gmb-target-query-box">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#166534" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <strong>Target Query:</strong> <span id="gmb-ai-result-query-label">--</span>
                                </div>
                            </div>
                            <div class="gmb-score-chips-group">
                                <div class="gmb-score-chip potential-score">
                                    Optimization Potential: <strong id="gmb-ai-potential-score">92 – 99 / 100</strong>
                                </div>
                            </div>
                        </div>

                        <div class="gmb-table-wrap gmb-ai-table-scroll">
                            <table class="gmb-data-table gmb-table-compact">
                                <thead>
                                    <tr>
                                        <th class="gmb-th-checkbox"><input type="checkbox" id="gmb-ai-post-select-all" checked /></th>
                                        <th style="width: 180px;">SEO Factor</th>
                                        <th>AI Recommended Optimization</th>
                                        <th style="width: 130px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="gmb-ai-post-suggestions-tbody">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="gmb-modal-footer">
                    <button type="button" class="button gmb-btn-secondary" id="gmb-ai-post-modal-cancel">Cancel</button>
                    <button type="button" class="button gmb-btn-secondary gmb-hidden" id="gmb-ai-post-modal-prev" style="display: none !important;">Previous</button>
                    <button type="button" class="button button-primary gmb-btn--primary" id="gmb-ai-setup-start-btn">Start AI Analysis</button>
                    <button type="button" class="button button-primary gmb-btn--primary gmb-hidden" id="gmb-ai-running-btn" disabled style="display: none !important;"><span class="task-spinner"></span> Running Analysis...</button>
                    <button type="button" class="button button-primary gmb-btn--primary gmb-hidden" id="gmb-ai-post-apply-btn" disabled style="display: none !important;">Apply Selected AI Optimizations</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render SEO Metabox view template
     *
     * @param WP_Post $post
     */
}
