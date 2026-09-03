<?php
/**
 * Help & Support Resources Canonical Registry
 *
 * Provides a unified, immutable source of truth for plugin help links,
 * documentation, support entitlements, setup wizard availability, and FAQ data.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Help_Registry {

    /**
     * Get canonical product base URL
     *
     * @param string $path Optional path relative to base.
     * @return string
     */
    public static function get_canonical_url($path = '') {
        $base_url = 'https://gmbranker.org';
        if (defined('GMB_RANKER_SEO_OFFICIAL_URL') && GMB_RANKER_SEO_OFFICIAL_URL) {
            $base_url = GMB_RANKER_SEO_OFFICIAL_URL;
        }
        $url = rtrim($base_url, '/') . '/' . ltrim($path, '/');
        return esc_url_raw($url);
    }

    /**
     * Get support URL
     *
     * @return string
     */
    public static function get_support_url() {
        return self::get_canonical_url('support');
    }

    /**
     * Get documentation URL
     *
     * @return string
     */
    public static function get_documentation_url() {
        return self::get_canonical_url('docs');
    }

    /**
     * Get community URL
     *
     * @return string
     */
    public static function get_community_url() {
        return self::get_canonical_url('community');
    }

    /**
     * Get wizard route URL
     *
     * @return string
     */
    public static function get_wizard_url() {
        return admin_url('admin.php?page=gmb-ranker-wizard');
    }

    /**
     * Check if setup wizard is available
     *
     * @return bool
     */
    public static function is_wizard_available() {
        return current_user_can('manage_options');
    }

    /**
     * Get license entitlement status info
     *
     * @return array
     */
    public static function get_licensing_status() {
        $api_key = get_option('gmb_ranker_api_key', '');
        $cloud_connected = !empty($api_key);

        if ($cloud_connected) {
            return array(
                'label'   => __('Cloud Connected', 'gmb-ranker-seo-automation'),
                'class'   => 'gmb-status-pill--success',
                'active'  => true,
                'details' => __('Direct Priority Support via Connected Platform.', 'gmb-ranker-seo-automation'),
            );
        }

        return array(
            'label'   => __('Standard Support', 'gmb-ranker-seo-automation'),
            'class'   => 'gmb-status-pill--info',
            'active'  => false,
            'details' => __('Community & Standard Support Resources Available.', 'gmb-ranker-seo-automation'),
        );
    }

    /**
     * Get canonical FAQ entries
     *
     * @return array
     */
    public static function get_faq_entries() {
        return array(
            array(
                'id'       => 'instant-indexing',
                'open'     => true,
                'question' => __('How does Instant Indexing differ from regular XML Sitemaps?', 'gmb-ranker-seo-automation'),
                'answer'   => __('XML Sitemaps publish a structured index file for search engine crawlers to discover during periodic site visits. Instant Indexing actively sends URL push requests directly to Google, Bing, and IndexNow endpoints when content is published or updated. Submitting URL requests accelerates crawler discovery, while individual search engine indexing algorithms determine crawl priority and index inclusion.', 'gmb-ranker-seo-automation'),
            ),
            array(
                'id'       => 'importer-safety',
                'open'     => false,
                'question' => __('Can I import settings from Rank Math or Yoast SEO without altering existing data?', 'gmb-ranker-seo-automation'),
                'answer'   => __('Yes. The built-in 1-click importer reads existing Rank Math or Yoast postmeta fields and maps them safely into GMB Ranker options without modifying post permalinks, post content, or underlying core WordPress database structures.', 'gmb-ranker-seo-automation'),
            ),
            array(
                'id'       => 'ai-providers',
                'open'     => false,
                'question' => __('Which AI providers are supported for meta & content generation?', 'gmb-ranker-seo-automation'),
                'answer'   => __('GMB Ranker integrates dynamically with OpenRouter, Groq Cloud, and Ollama (Local AI). Depending on your chosen provider and API plan, OpenRouter offers free & paid instruct models, Groq offers fast inference endpoints, and Ollama allows you to run models locally on your own infrastructure.', 'gmb-ranker-seo-automation'),
            ),
            array(
                'id'       => 'schema-conditions',
                'open'     => false,
                'question' => __('How do Schema Display Conditions and Property Groups work?', 'gmb-ranker-seo-automation'),
                'answer'   => __('Display Conditions allow you to conditionally inject schemas across posts, pages, and custom post types. Rules within a single condition group are evaluated using AND logic, while separate Property Groups use OR logic—giving you granular control over where schemas appear.', 'gmb-ranker-seo-automation'),
            ),
        );
    }
}
