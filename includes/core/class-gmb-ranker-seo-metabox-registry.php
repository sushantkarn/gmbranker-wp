<?php
/**
 * Canonical Metabox, Author SEO & Content Analysis Registry
 *
 * Centralizes post type eligibility, search markets, locale options,
 * score badge calculations, and meta persistence for post & author SEO.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Metabox_Registry {

    /**
     * Get eligible public post types for SEO Metabox
     *
     * @return array<string>
     */
    public static function get_eligible_post_types() {
        $public_types = get_post_types(array('public' => true), 'names');
        $excluded = array('attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request');
        foreach ($excluded as $ex) {
            unset($public_types[$ex]);
        }
        $types = array_values($public_types);
        return !empty($types) ? $types : array('post', 'page');
    }

    /**
     * Get search engine & country target options
     *
     * @return array
     */
    public static function get_search_market_options() {
        return array(
            'popular' => array(
                'GLOBAL|google.com'   => __('GLOBAL | google.com', 'gmb-ranker-seo-automation'),
                'US|google.com'      => __('UNITED STATES | google.com', 'gmb-ranker-seo-automation'),
                'GB|google.co.uk'    => __('UNITED KINGDOM | google.co.uk', 'gmb-ranker-seo-automation'),
                'CA|google.ca'       => __('CANADA | google.ca', 'gmb-ranker-seo-automation'),
                'AU|google.com.au'   => __('AUSTRALIA | google.com.au', 'gmb-ranker-seo-automation'),
                'IN|google.co.in'    => __('INDIA | google.co.in', 'gmb-ranker-seo-automation'),
                'NP|google.com.np'   => __('NEPAL | google.com.np', 'gmb-ranker-seo-automation'),
            ),
            'asia_pacific' => array(
                'JP|google.co.jp'    => __('JAPAN | google.co.jp', 'gmb-ranker-seo-automation'),
                'SG|google.com.sg'   => __('SINGAPORE | google.com.sg', 'gmb-ranker-seo-automation'),
                'KR|google.co.kr'    => __('SOUTH KOREA | google.co.kr', 'gmb-ranker-seo-automation'),
                'MY|google.com.my'   => __('MALAYSIA | google.com.my', 'gmb-ranker-seo-automation'),
                'ID|google.co.id'    => __('INDONESIA | google.co.id', 'gmb-ranker-seo-automation'),
                'TH|google.co.th'    => __('THAILAND | google.co.th', 'gmb-ranker-seo-automation'),
                'VN|google.com.vn'   => __('VIETNAM | google.com.vn', 'gmb-ranker-seo-automation'),
                'PH|google.com.ph'   => __('PHILIPPINES | google.com.ph', 'gmb-ranker-seo-automation'),
                'PK|google.com.pk'   => __('PAKISTAN | google.com.pk', 'gmb-ranker-seo-automation'),
                'BD|google.com.bd'   => __('BANGLADESH | google.com.bd', 'gmb-ranker-seo-automation'),
                'NZ|google.co.nz'   => __('NEW ZEALAND | google.co.nz', 'gmb-ranker-seo-automation'),
            ),
            'europe' => array(
                'DE|google.de'       => __('GERMANY | google.de', 'gmb-ranker-seo-automation'),
                'FR|google.fr'       => __('FRANCE | google.fr', 'gmb-ranker-seo-automation'),
                'ES|google.es'       => __('SPAIN | google.es', 'gmb-ranker-seo-automation'),
                'IT|google.it'       => __('ITALY | google.it', 'gmb-ranker-seo-automation'),
                'NL|google.nl'       => __('NETHERLANDS | google.nl', 'gmb-ranker-seo-automation'),
                'SE|google.se'       => __('SWEDEN | google.se', 'gmb-ranker-seo-automation'),
                'NO|google.no'       => __('NORWAY | google.no', 'gmb-ranker-seo-automation'),
                'DK|google.dk'       => __('DENMARK | google.dk', 'gmb-ranker-seo-automation'),
                'FI|google.fi'       => __('FINLAND | google.fi', 'gmb-ranker-seo-automation'),
                'PL|google.pl'       => __('POLAND | google.pl', 'gmb-ranker-seo-automation'),
                'IE|google.ie'       => __('IRELAND | google.ie', 'gmb-ranker-seo-automation'),
                'PT|google.pt'       => __('PORTUGAL | google.pt', 'gmb-ranker-seo-automation'),
                'GR|google.gr'       => __('GREECE | google.gr', 'gmb-ranker-seo-automation'),
                'TR|google.com.tr'   => __('TURKEY | google.com.tr', 'gmb-ranker-seo-automation'),
            ),
            'americas' => array(
                'BR|google.com.br'   => __('BRAZIL | google.com.br', 'gmb-ranker-seo-automation'),
                'MX|google.com.mx'   => __('MEXICO | google.com.mx', 'gmb-ranker-seo-automation'),
                'AR|google.com.ar'   => __('ARGENTINA | google.com.ar', 'gmb-ranker-seo-automation'),
                'CL|google.cl'       => __('CHILE | google.cl', 'gmb-ranker-seo-automation'),
                'CO|google.com.co'   => __('COLOMBIA | google.com.co', 'gmb-ranker-seo-automation'),
                'PE|google.com.pe'   => __('PERU | google.com.pe', 'gmb-ranker-seo-automation'),
            ),
            'middle_east_africa' => array(
                'AE|google.ae'       => __('UAE | google.ae', 'gmb-ranker-seo-automation'),
                'SA|google.com.sa'   => __('SAUDI ARABIA | google.com.sa', 'gmb-ranker-seo-automation'),
                'IL|google.co.il'    => __('ISRAEL | google.co.il', 'gmb-ranker-seo-automation'),
                'EG|google.com.eg'   => __('EGYPT | google.com.eg', 'gmb-ranker-seo-automation'),
                'ZA|google.co.za'   => __('SOUTH AFRICA | google.co.za', 'gmb-ranker-seo-automation'),
                'NG|google.com.ng'   => __('NIGERIA | google.com.ng', 'gmb-ranker-seo-automation'),
                'KE|google.co.ke'   => __('KENYA | google.co.ke', 'gmb-ranker-seo-automation'),
            ),
        );
    }

    /**
     * Get language options
     *
     * @return array
     */
    public static function get_language_options() {
        return array(
            'en'    => __('English', 'gmb-ranker-seo-automation'),
            'ne'    => __('Nepali (नेपाली)', 'gmb-ranker-seo-automation'),
            'es'    => __('Spanish (Español)', 'gmb-ranker-seo-automation'),
            'fr'    => __('French (Français)', 'gmb-ranker-seo-automation'),
            'de'    => __('German (Deutsch)', 'gmb-ranker-seo-automation'),
            'it'    => __('Italian (Italiano)', 'gmb-ranker-seo-automation'),
            'pt'    => __('Portuguese (Português)', 'gmb-ranker-seo-automation'),
            'nl'    => __('Dutch (Nederlands)', 'gmb-ranker-seo-automation'),
            'ja'    => __('Japanese (日本語)', 'gmb-ranker-seo-automation'),
            'zh-cn' => __('Chinese Simplified (简体中文)', 'gmb-ranker-seo-automation'),
            'zh-tw' => __('Chinese Traditional (繁體中文)', 'gmb-ranker-seo-automation'),
            'ar'    => __('Arabic (العربية)', 'gmb-ranker-seo-automation'),
            'hi'    => __('Hindi (हिन्दी)', 'gmb-ranker-seo-automation'),
            'bn'    => __('Bengali (বাংলা)', 'gmb-ranker-seo-automation'),
            'ru'    => __('Russian (Русский)', 'gmb-ranker-seo-automation'),
            'sv'    => __('Swedish (Svenska)', 'gmb-ranker-seo-automation'),
            'no'    => __('Norwegian (Norsk)', 'gmb-ranker-seo-automation'),
            'da'    => __('Danish (Dansk)', 'gmb-ranker-seo-automation'),
            'fi'    => __('Finnish (Suomi)', 'gmb-ranker-seo-automation'),
            'pl'    => __('Polish (Polski)', 'gmb-ranker-seo-automation'),
            'tr'    => __('Turkish (Türkçe)', 'gmb-ranker-seo-automation'),
            'id'    => __('Indonesian (Bahasa Indonesia)', 'gmb-ranker-seo-automation'),
            'vi'    => __('Vietnamese (Tiếng Việt)', 'gmb-ranker-seo-automation'),
            'th'    => __('Thai (ไทย)', 'gmb-ranker-seo-automation'),
            'ko'    => __('Korean (한국어)', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get score badge CSS class based on numerical score
     *
     * @param int $score
     * @param string $style 'publish' or 'table'
     * @return string
     */
    public static function get_score_badge_class($score, $style = 'publish') {
        $score = intval($score);
        if ($style === 'table') {
            if ($score >= 80) {
                return 'gmb-score-badge--good';
            }
            if ($score >= 50) {
                return 'gmb-score-badge--ok';
            }
            return 'gmb-score-badge--poor';
        }

        if ($score >= 80) {
            return 'green';
        }
        if ($score < 60) {
            return 'red';
        }
        return 'orange';
    }

    /**
     * Save post SEO metadata safely and synchronize third-party plugin meta keys
     *
     * @param int $post_id
     * @param array $request_data
     * @return void
     */
    public static function save_post_metadata($post_id, array $request_data) {
        // Title
        if (isset($request_data['gmb_seo_title'])) {
            $title = sanitize_text_field(wp_unslash($request_data['gmb_seo_title']));
            update_post_meta($post_id, '_gmb_ranker_seo_title', $title);
            update_post_meta($post_id, '_yoast_wpseo_title', $title);
            update_post_meta($post_id, 'rank_math_title', $title);
            update_post_meta($post_id, '_rank_math_title', $title);
            update_post_meta($post_id, '_aioseo_title', $title);
            update_post_meta($post_id, '_seopress_titles_title', $title);
        }

        // Description
        if (isset($request_data['gmb_seo_description'])) {
            $desc = sanitize_textarea_field(wp_unslash($request_data['gmb_seo_description']));
            update_post_meta($post_id, '_gmb_ranker_seo_description', $desc);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
            update_post_meta($post_id, 'rank_math_description', $desc);
            update_post_meta($post_id, '_rank_math_description', $desc);
            update_post_meta($post_id, '_aioseo_description', $desc);
            update_post_meta($post_id, '_seopress_titles_desc', $desc);
        }

        // Canonical URL
        if (isset($request_data['gmb_seo_canonical'])) {
            $canonical = filter_var(wp_unslash($request_data['gmb_seo_canonical']), FILTER_VALIDATE_URL) ? esc_url_raw(wp_unslash($request_data['gmb_seo_canonical'])) : '';
            update_post_meta($post_id, '_gmb_ranker_seo_canonical', $canonical);
            update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical);
            update_post_meta($post_id, 'rank_math_canonical_url', $canonical);
        }

        // Robots
        if (isset($request_data['gmb_seo_robots'])) {
            if (is_array($request_data['gmb_seo_robots'])) {
                $robots_arr = array_map('sanitize_key', $request_data['gmb_seo_robots']);
                update_post_meta($post_id, '_gmb_ranker_seo_robots', implode(', ', array_unique($robots_arr)));
            } else {
                $robots_str = sanitize_text_field(wp_unslash($request_data['gmb_seo_robots']));
                update_post_meta($post_id, '_gmb_ranker_seo_robots', $robots_str);
            }
        } else {
            update_post_meta($post_id, '_gmb_ranker_seo_robots', '');
        }

        // Advanced Robots Controls
        if (isset($request_data['gmb_seo_max_snippet'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_snippet', sanitize_text_field(wp_unslash($request_data['gmb_seo_max_snippet'])));
        }
        if (isset($request_data['gmb_seo_max_video'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_video', sanitize_text_field(wp_unslash($request_data['gmb_seo_max_video'])));
        }
        if (isset($request_data['gmb_seo_max_image'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_max_image', sanitize_text_field(wp_unslash($request_data['gmb_seo_max_image'])));
        }

        // Breadcrumb Title Override
        if (isset($request_data['gmb_seo_breadcrumb_title'])) {
            update_post_meta($post_id, '_gmb_ranker_breadcrumb_title', sanitize_text_field(wp_unslash($request_data['gmb_seo_breadcrumb_title'])));
        }

        // Page Redirection
        if (isset($request_data['gmb_seo_redirect_url'])) {
            $red_url = filter_var(wp_unslash($request_data['gmb_seo_redirect_url']), FILTER_VALIDATE_URL) ? esc_url_raw(wp_unslash($request_data['gmb_seo_redirect_url'])) : '';
            update_post_meta($post_id, '_gmb_ranker_redirect_url', $red_url);
        }
        if (isset($request_data['gmb_seo_redirect_code'])) {
            $code = intval($request_data['gmb_seo_redirect_code']);
            if (in_array($code, array(301, 302, 307, 308, 410, 451), true)) {
                update_post_meta($post_id, '_gmb_ranker_redirect_code', (string)$code);
            }
        }

        // Focus Keyword
        $focus_kw = '';
        if (isset($request_data['gmb_seo_focus_keyword'])) {
            $focus_kw = sanitize_text_field(wp_unslash($request_data['gmb_seo_focus_keyword']));
        } elseif (isset($request_data['gmb_seo_focus_kw'])) {
            $focus_kw = sanitize_text_field(wp_unslash($request_data['gmb_seo_focus_kw']));
        }
        if (!empty($focus_kw)) {
            update_post_meta($post_id, '_gmb_ranker_focus_keyword', $focus_kw);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_kw);
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus_kw);
        }

        // Pillar Content
        $is_pillar = isset($request_data['gmb_seo_is_pillar']) ? '1' : '0';
        update_post_meta($post_id, '_gmb_ranker_seo_is_pillar', $is_pillar);

        // SEO Score (Calculated server side or passed from validated analyzer)
        if (isset($request_data['gmb_seo_score'])) {
            update_post_meta($post_id, '_gmb_ranker_seo_score', intval(wp_unslash($request_data['gmb_seo_score'])));
        }

        // Schema JSON-LD & Active Schemas
        if (isset($request_data['gmb_seo_active_schemas'])) {
            $active_schemas_raw = sanitize_text_field(wp_unslash($request_data['gmb_seo_active_schemas']));
            $active_schemas_arr = !empty($active_schemas_raw) ? array_filter(array_map('trim', explode(',', $active_schemas_raw))) : array();
            update_post_meta($post_id, '_gmb_ranker_active_schemas', $active_schemas_arr);
            if (!empty($active_schemas_arr)) {
                $primary_schema = reset($active_schemas_arr);
                update_post_meta($post_id, '_gmb_ranker_schema_type', $primary_schema);
                update_post_meta($post_id, 'rank_math_rich_snippet', strtolower($primary_schema));
            }
        }

        if (isset($request_data['gmb_seo_schema'])) {
            $schema_clean = wp_unslash($request_data['gmb_seo_schema']);
            update_post_meta($post_id, '_gmb_ranker_seo_schema', $schema_clean);
            update_post_meta($post_id, '_gmb_ranker_json_ld', $schema_clean);
        }

        // Facebook (OpenGraph)
        if (isset($request_data['gmb_seo_facebook_title'])) {
            $fb_title = sanitize_text_field(wp_unslash($request_data['gmb_seo_facebook_title']));
            update_post_meta($post_id, '_gmb_ranker_facebook_title', $fb_title);
            update_post_meta($post_id, '_gmb_ranker_og_title', $fb_title);
        }
        if (isset($request_data['gmb_seo_facebook_desc'])) {
            $fb_desc = sanitize_textarea_field(wp_unslash($request_data['gmb_seo_facebook_desc']));
            update_post_meta($post_id, '_gmb_ranker_facebook_desc', $fb_desc);
            update_post_meta($post_id, '_gmb_ranker_og_description', $fb_desc);
        }
        if (isset($request_data['gmb_seo_facebook_image'])) {
            $fb_img = filter_var(wp_unslash($request_data['gmb_seo_facebook_image']), FILTER_VALIDATE_URL) ? esc_url_raw(wp_unslash($request_data['gmb_seo_facebook_image'])) : '';
            update_post_meta($post_id, '_gmb_ranker_facebook_image', $fb_img);
            update_post_meta($post_id, '_gmb_ranker_og_image', $fb_img);
        }

        // Twitter / X
        if (isset($request_data['gmb_seo_twitter_card_type'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_card_type', sanitize_text_field(wp_unslash($request_data['gmb_seo_twitter_card_type'])));
        }
        if (isset($request_data['gmb_seo_twitter_title'])) {
            update_post_meta($post_id, '_gmb_ranker_twitter_title', sanitize_text_field(wp_unslash($request_data['gmb_seo_twitter_title'])));
        }
        if (isset($request_data['gmb_seo_twitter_desc'])) {
            $tw_desc = sanitize_textarea_field(wp_unslash($request_data['gmb_seo_twitter_desc']));
            update_post_meta($post_id, '_gmb_ranker_twitter_desc', $tw_desc);
            update_post_meta($post_id, '_gmb_ranker_twitter_description', $tw_desc);
        }
        if (isset($request_data['gmb_seo_twitter_image'])) {
            $tw_img = filter_var(wp_unslash($request_data['gmb_seo_twitter_image']), FILTER_VALIDATE_URL) ? esc_url_raw(wp_unslash($request_data['gmb_seo_twitter_image'])) : '';
            update_post_meta($post_id, '_gmb_ranker_twitter_image', $tw_img);
        }
    }

    /**
     * Save Author user profile SEO metadata
     *
     * @param int $user_id
     * @param array $request_data
     * @return void
     */
    public static function save_author_metadata($user_id, array $request_data) {
        if (isset($request_data['gmb_author_custom_title'])) {
            update_user_meta($user_id, 'gmb_author_custom_title', sanitize_text_field(wp_unslash($request_data['gmb_author_custom_title'])));
        }
        if (isset($request_data['gmb_author_custom_desc'])) {
            update_user_meta($user_id, 'gmb_author_custom_desc', sanitize_textarea_field(wp_unslash($request_data['gmb_author_custom_desc'])));
        }
        $noindex = isset($request_data['gmb_author_noindex']) ? '1' : '0';
        update_user_meta($user_id, 'gmb_author_noindex', $noindex);

        if (isset($request_data['gmb_author_same_as'])) {
            $raw_urls = sanitize_textarea_field(wp_unslash($request_data['gmb_author_same_as']));
            $lines = array_filter(array_map('trim', explode("\n", $raw_urls)));
            $clean_urls = array();
            foreach ($lines as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $clean_urls[] = esc_url_raw($url);
                }
            }
            update_user_meta($user_id, 'gmb_author_same_as', implode("\n", array_unique($clean_urls)));
        }
    }
}
