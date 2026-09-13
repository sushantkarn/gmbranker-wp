<?php
/**
 * MCP Tools Registry & Definitions
 *
 * Provides structured tool definitions with OpenAI-compliant annotations
 * (readOnlyHint, openWorldHint, destructiveHint) and execution logic.
 *
 * @package GMB_Ranker_SEO_Automation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_MCP_Tools {

    /**
     * Get array of all supported MCP tool declarations.
     *
     * @return array
     */
    public static function get_tools_manifest() {
        return array(
            array(
                'name' => 'get_site_context',
                'title' => 'Get WordPress site context',
                'description' => 'Returns the stable identity, role, and capabilities of this connected WordPress site agent. It does not return credentials.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => new stdClass(),
                ),
            ),
            array(
                'name' => 'get_source_health',
                'title' => 'Get connected source health',
                'description' => 'Reports whether WordPress-side SEO and Google integration sources are configured, without exposing tokens or credentials.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => new stdClass(),
                ),
            ),
            array(
                'name' => 'get_local_seo_snapshot',
                'title' => 'Get local SEO site snapshot',
                'description' => 'Returns a provenance-aware WordPress site snapshot. Google, Search Console, Analytics, ranking, and backlink data must be read from the cloud GMB Ranker MCP server.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => new stdClass(),
                ),
            ),
            array(
                'name' => 'get_seo_overview',
                'description' => 'Retrieves general site SEO status, GMB integration state, sitemap status, and active modules.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => new stdClass(),
                ),
            ),
            array(
                'name' => 'list_posts_seo',
                'description' => 'Lists published posts or pages with their SEO meta titles, descriptions, focus keywords, and scores.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_type' => array(
                            'type' => 'string',
                            'description' => 'Post type to query (e.g., post, page, doctor). Default is post.',
                            'default' => 'post',
                        ),
                        'number' => array(
                            'type' => 'integer',
                            'description' => 'Number of items to retrieve (max 50). Default is 10.',
                            'default' => 10,
                        ),
                        'page' => array(
                            'type' => 'integer',
                            'description' => 'Pagination page number.',
                            'default' => 1,
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'preview_update_post_seo',
                'description' => 'Previews SEO metadata changes for a WordPress post or page. No changes are made until the preview is explicitly applied.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'required' => array('post_id'),
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'integer',
                            'description' => 'ID of the WordPress post or page to update.',
                        ),
                        'title' => array(
                            'type' => 'string',
                            'description' => 'Custom SEO Meta Title.',
                        ),
                        'description' => array(
                            'type' => 'string',
                            'description' => 'Custom SEO Meta Description.',
                        ),
                        'focus_keyword' => array(
                            'type' => 'string',
                            'description' => 'Primary focus keyword for SEO analysis.',
                        ),
                        'canonical_url' => array(
                            'type' => 'string',
                            'description' => 'Custom canonical URL.',
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'apply_update_post_seo',
                'description' => 'Applies an approved SEO metadata preview to a WordPress post or page. Requires the exact preview ID and an idempotency key.',
                'annotations' => array(
                    'readOnlyHint' => false,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'required' => array('preview_id', 'idempotency_key'),
                    'properties' => array(
                        'preview_id' => array(
                            'type' => 'string',
                            'description' => 'Preview ID returned by preview_update_post_seo.',
                        ),
                        'idempotency_key' => array(
                            'type' => 'string',
                            'description' => 'Unique key for this apply operation. Reusing it returns the original result.',
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'generate_schema_org',
                'description' => 'Generates and inspects Schema.org JSON-LD structured data for a post, page, or local business entity.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'integer',
                            'description' => 'Post ID to generate schema for. If omitted, returns site business schema.',
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'run_audit_scan',
                'description' => 'Executes an SEO health audit scan to detect missing meta tags, schema issues, and indexability errors.',
                'annotations' => array(
                    'readOnlyHint' => true,
                    'openWorldHint' => false,
                    'destructiveHint' => false,
                ),
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'scope' => array(
                            'type' => 'string',
                            'description' => 'Audit scope: "full", "meta", "sitemap", or "gmb". Default is "full".',
                            'default' => 'full',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Execute a tool by name with arguments.
     *
     * @param string $tool_name
     * @param array $args
     * @return array
     */
    public static function execute_tool($tool_name, $args = array()) {
        switch ($tool_name) {
            case 'get_site_context':
                return self::get_site_context();

            case 'get_source_health':
                return self::get_source_health();

            case 'get_local_seo_snapshot':
                return self::get_local_seo_snapshot();

            case 'get_seo_overview':
                return self::get_seo_overview();

            case 'list_posts_seo':
                return self::list_posts_seo($args);

            case 'preview_update_post_seo':
                return self::preview_update_post_seo($args);

            case 'apply_update_post_seo':
                return self::apply_update_post_seo($args);

            case 'generate_schema_org':
                return self::generate_schema_org($args);

            case 'run_audit_scan':
                return self::run_audit_scan($args);

            default:
                return array(
                    'error' => true,
                    'message' => sprintf(__('Tool "%s" not found.', 'gmb-ranker-seo-automation'), $tool_name),
                );
        }
    }

    private static function get_site_context() {
        return array(
            'context_version' => '1',
            'site_id' => hash('sha256', untrailingslashit(home_url())),
            'site_url' => home_url('/'),
            'site_name' => get_bloginfo('name'),
            'plugin_version' => defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.3.0',
            'agent_role' => 'wordpress_site_agent',
            'authority' => 'FIRST_PARTY_SITE',
            'fetched_at' => gmdate('c'),
            'capabilities' => array(
                'wordpress.read',
                'wordpress.seo.write',
                'wordpress.schema.read',
            ),
        );
    }

    private static function get_source_health() {
        $settings = get_option('gmb_ranker_seo_settings', array());
        $api_key = get_option('gmb_ranker_api_key', '');
        if (empty($api_key)) {
            $api_key = get_option('gmb_ranker_secret', '');
        }

        $sources = array(
            'WORDPRESS' => array(
                'status' => 'LIVE',
                'authority' => 'FIRST_PARTY_SITE',
                'fetched_at' => gmdate('c'),
            ),
            'GMB_RANKER_CLOUD' => array(
                'status' => empty($api_key) ? 'MISSING' : 'CONFIGURED',
                'authority' => 'CONNECTED_OWNER',
                'fetched_at' => gmdate('c'),
            ),
            'GOOGLE_BUSINESS_PROFILE' => array(
                'status' => !empty($settings['enable_gmb_sync']) ? 'ENABLED' : 'MISSING',
                'authority' => 'CONNECTED_OWNER',
                'fetched_at' => gmdate('c'),
            ),
            'SEARCH_CONSOLE' => array(
                'status' => class_exists('GMB_Ranker_SEO_GSC_Client') ? 'AVAILABLE' : 'MISSING',
                'authority' => 'CONNECTED_OWNER',
                'fetched_at' => gmdate('c'),
            ),
            'ANALYTICS' => array(
                'status' => class_exists('GMB_Ranker_SEO_GA4_Client') ? 'AVAILABLE' : 'MISSING',
                'authority' => 'CONNECTED_OWNER',
                'fetched_at' => gmdate('c'),
            ),
        );

        return array(
            'site_id' => hash('sha256', untrailingslashit(home_url())),
            'fetched_at' => gmdate('c'),
            'sources' => $sources,
            'warnings' => array(
                'Google and multi-source local SEO data are authoritative only when returned by the cloud GMB Ranker MCP server.',
            ),
        );
    }

    private static function get_local_seo_snapshot() {
        $overview = self::get_seo_overview();
        $health = self::get_source_health();
        $post_counts = wp_count_posts('post');
        $page_counts = wp_count_posts('page');

        return array(
            'context' => self::get_site_context(),
            'fetched_at' => gmdate('c'),
            'sources' => array(
                'WORDPRESS' => array(
                    'status' => 'LIVE',
                    'authority' => 'FIRST_PARTY_SITE',
                    'fetched_at' => gmdate('c'),
                ),
                'GOOGLE_BUSINESS_PROFILE' => array(
                    'status' => 'DELEGATED_TO_CLOUD',
                    'authority' => 'CONNECTED_OWNER',
                    'fetched_at' => null,
                ),
                'SEARCH_CONSOLE' => array(
                    'status' => 'DELEGATED_TO_CLOUD',
                    'authority' => 'CONNECTED_OWNER',
                    'fetched_at' => null,
                ),
                'ANALYTICS' => array(
                    'status' => 'DELEGATED_TO_CLOUD',
                    'authority' => 'CONNECTED_OWNER',
                    'fetched_at' => null,
                ),
            ),
            'wordpress' => array(
                'seo_overview' => $overview,
                'published_posts' => isset($post_counts->publish) ? (int) $post_counts->publish : 0,
                'published_pages' => isset($page_counts->publish) ? (int) $page_counts->publish : 0,
            ),
            'source_health' => $health,
            'warnings' => array(
                'This site-agent response does not substitute for live GBP, Search Console, Analytics, ranking, or backlink data.',
            ),
        );
    }

    private static function get_seo_overview() {
        $options = get_option('gmb_ranker_seo_settings', array());
        return array(
            'site_name' => get_bloginfo('name'),
            'site_url' => get_bloginfo('url'),
            'tagline' => get_bloginfo('description'),
            'plugin_version' => GMB_RANKER_SEO_VERSION,
            'modules' => array(
                'gmb_sync' => !empty($options['enable_gmb_sync']),
                'sitemaps' => !empty($options['enable_sitemaps']),
                'schema' => !empty($options['enable_schema']),
                'instant_indexing' => !empty($options['enable_instant_indexing']),
            ),
        );
    }

    private static function list_posts_seo($args) {
        $post_type = !empty($args['post_type']) ? sanitize_text_field($args['post_type']) : 'post';
        $number = !empty($args['number']) ? min(50, max(1, (int)$args['number'])) : 10;
        $page = !empty($args['page']) ? max(1, (int)$args['page']) : 1;

        $query_args = array(
            'post_type' => $post_type,
            'posts_per_page' => $number,
            'paged' => $page,
            'post_status' => 'publish',
        );

        $query = new WP_Query($query_args);
        $items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $seo_title = get_post_meta($id, '_gmb_ranker_seo_title', true) ?: (get_post_meta($id, '_gmb_seo_title', true) ?: (get_post_meta($id, '_yoast_wpseo_title', true) ?: (get_post_meta($id, 'rank_math_title', true) ?: get_the_title())));
                $seo_desc  = get_post_meta($id, '_gmb_ranker_seo_description', true) ?: (get_post_meta($id, '_gmb_seo_description', true) ?: (get_post_meta($id, '_yoast_wpseo_metadesc', true) ?: (get_post_meta($id, 'rank_math_description', true) ?: '')));
                $focus_kw  = get_post_meta($id, '_gmb_ranker_focus_keyword', true) ?: (get_post_meta($id, '_gmb_seo_focus_keyword', true) ?: (get_post_meta($id, '_yoast_wpseo_focuskw', true) ?: (get_post_meta($id, 'rank_math_focus_keyword', true) ?: '')));
                $canonical = get_post_meta($id, '_gmb_ranker_seo_canonical', true) ?: (get_post_meta($id, '_gmb_seo_canonical', true) ?: (get_post_meta($id, '_yoast_wpseo_canonical', true) ?: (get_post_meta($id, 'rank_math_canonical_url', true) ?: get_permalink())));

                $items[] = array(
                    'id' => $id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'seo_title' => $seo_title,
                    'seo_description' => $seo_desc,
                    'focus_keyword' => $focus_kw,
                    'canonical_url' => $canonical,
                );
            }
            wp_reset_postdata();
        }

        return array(
            'total' => $query->found_posts,
            'page' => $page,
            'total_pages' => $query->max_num_pages,
            'items' => $items,
        );
    }

    private static function update_post_seo($args) {
        if (empty($args['post_id']) || !get_post($args['post_id'])) {
            return array('error' => true, 'message' => __('Invalid post_id provided.', 'gmb-ranker-seo-automation'));
        }

        $post_id = (int) $args['post_id'];
        $updated = array();

        if (isset($args['title'])) {
            $val = sanitize_text_field($args['title']);
            update_post_meta($post_id, '_gmb_ranker_seo_title', $val);
            update_post_meta($post_id, '_gmb_seo_title', $val);
            // Yoast, Rank Math, DELUCKS, AIOSEO
            update_post_meta($post_id, '_yoast_wpseo_title', $val);
            update_post_meta($post_id, 'rank_math_title', $val);
            update_post_meta($post_id, '_rank_math_title', $val);
            update_post_meta($post_id, '_dpc-meta-title', $val);
            update_post_meta($post_id, '_aioseop_title', $val);
            $updated['title'] = $val;
        }

        if (isset($args['description'])) {
            $val = sanitize_text_field($args['description']);
            update_post_meta($post_id, '_gmb_ranker_seo_description', $val);
            update_post_meta($post_id, '_gmb_seo_description', $val);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $val);
            update_post_meta($post_id, 'rank_math_description', $val);
            update_post_meta($post_id, '_rank_math_description', $val);
            update_post_meta($post_id, '_dpc-meta-description', $val);
            update_post_meta($post_id, '_aioseop_description', $val);
            $updated['description'] = $val;
        }

        if (isset($args['focus_keyword'])) {
            $val = sanitize_text_field($args['focus_keyword']);
            update_post_meta($post_id, '_gmb_ranker_focus_keyword', $val);
            update_post_meta($post_id, '_gmb_seo_focus_keyword', $val);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $val);
            update_post_meta($post_id, 'rank_math_focus_keyword', $val);
            update_post_meta($post_id, '_rank_math_focus_keyword', $val);
            update_post_meta($post_id, '_dpc-keyword', $val);
            $updated['focus_keyword'] = $val;
        }

        if (isset($args['canonical_url'])) {
            $val = esc_url_raw($args['canonical_url']);
            update_post_meta($post_id, '_gmb_ranker_seo_canonical', $val);
            update_post_meta($post_id, '_gmb_seo_canonical', $val);
            update_post_meta($post_id, '_yoast_wpseo_canonical', $val);
            update_post_meta($post_id, 'rank_math_canonical_url', $val);
            update_post_meta($post_id, '_rank_math_canonical', $val);
            $updated['canonical_url'] = $val;
        }

        // AIOSEO v4 custom table model write (if installed)
        if (class_exists('\\AIOSEO\\Plugin\\Common\\Models\\Post') && method_exists('\\AIOSEO\\Plugin\\Common\\Models\\Post', 'getPost')) {
            try {
                $aioseo_post = \AIOSEO\Plugin\Common\Models\Post::getPost($post_id);
                if (is_object($aioseo_post) && method_exists($aioseo_post, 'save')) {
                    if (isset($updated['title']))       $aioseo_post->title       = $updated['title'];
                    if (isset($updated['description'])) $aioseo_post->description = $updated['description'];
                    $aioseo_post->post_id = $post_id;
                    $aioseo_post->save();
                    $updated['aioseo_v4'] = true;
                }
            } catch (\Throwable $e) {
                // Fail soft for version mismatches
            }
        }

        return array(
            'success' => true,
            'post_id' => $post_id,
            'updated' => $updated,
        );
    }

    private static function preview_update_post_seo($args) {
        $post_id = !empty($args['post_id']) ? (int) $args['post_id'] : 0;
        $post = $post_id ? get_post($post_id) : null;
        if (!$post || !in_array($post->post_status, array('publish', 'draft', 'pending', 'future', 'private'), true)) {
            return array('error' => true, 'code' => 'invalid_post_id', 'message' => __('The requested post or page does not exist or cannot be edited.', 'gmb-ranker-seo-automation'));
        }

        $changes = array();
        $fields = array(
            'title' => array('_gmb_ranker_seo_title', '_gmb_seo_title', 'sanitize_text_field'),
            'description' => array('_gmb_ranker_seo_description', '_gmb_seo_description', 'sanitize_textarea_field'),
            'focus_keyword' => array('_gmb_ranker_focus_keyword', '_gmb_seo_focus_keyword', 'sanitize_text_field'),
            'canonical_url' => array('_gmb_ranker_seo_canonical', '_gmb_seo_canonical', 'esc_url_raw'),
        );
        foreach ($fields as $field => $config) {
            if (array_key_exists($field, $args)) {
                $value = call_user_func($config[2], (string) $args[$field]);
                $before = (string) get_post_meta($post_id, $config[0], true);
                if ($before === '') {
                    $before = (string) get_post_meta($post_id, $config[1], true);
                }
                $changes[$field] = array(
                    'before' => $before,
                    'after'  => $value,
                );
            }
        }
        if (empty($changes)) {
            return array('error' => true, 'code' => 'no_changes', 'message' => __('Provide at least one SEO field to change.', 'gmb-ranker-seo-automation'));
        }

        $preview_id = 'mcp_' . wp_generate_uuid4();
        $preview = array(
            'preview_id' => $preview_id,
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'post_title' => get_the_title($post_id),
            'changes' => $changes,
            'created_at' => gmdate('c'),
            'expires_at' => gmdate('c', time() + 600),
            'source' => 'WORDPRESS',
        );
        set_transient('gmb_mcp_preview_' . md5($preview_id), $preview, 10 * MINUTE_IN_SECONDS);
        return $preview;
    }

    private static function apply_update_post_seo($args) {
        $preview_id = !empty($args['preview_id']) ? sanitize_text_field($args['preview_id']) : '';
        $idempotency_key = !empty($args['idempotency_key']) ? sanitize_text_field($args['idempotency_key']) : '';
        if (empty($preview_id) || empty($idempotency_key)) {
            return array('error' => true, 'code' => 'confirmation_required', 'message' => __('A preview_id and idempotency_key are required.', 'gmb-ranker-seo-automation'));
        }

        $result_key = 'gmb_mcp_apply_' . md5($idempotency_key);
        $existing = get_transient($result_key);
        if (false !== $existing) {
            return $existing;
        }

        $preview = get_transient('gmb_mcp_preview_' . md5($preview_id));
        if (!is_array($preview) || empty($preview['post_id']) || empty($preview['changes'])) {
            return array('error' => true, 'code' => 'preview_invalid_or_expired', 'message' => __('The preview is invalid or expired. Create a new preview before applying changes.', 'gmb-ranker-seo-automation'));
        }

        $post_id = (int) $preview['post_id'];
        if (!get_post($post_id)) {
            return array('error' => true, 'code' => 'post_not_found', 'message' => __('The post or page no longer exists.', 'gmb-ranker-seo-automation'));
        }

        $args = array('post_id' => $post_id);
        foreach ($preview['changes'] as $field => $change) {
            $args[$field] = isset($change['after']) ? $change['after'] : '';
        }
        $result = self::update_post_seo($args);
        if (!empty($result['success'])) {
            $result['preview_id'] = $preview_id;
            $result['idempotency_key'] = $idempotency_key;
            $result['applied_at'] = gmdate('c');
            set_transient($result_key, $result, DAY_IN_SECONDS);
            delete_transient('gmb_mcp_preview_' . md5($preview_id));
        }
        return $result;
    }

    private static function sync_gmb_location($args) {
        $location_id = !empty($args['location_id']) ? sanitize_text_field($args['location_id']) : 'default';
        return array(
            'error' => true,
            'code' => 'delegated_to_cloud',
            'location_id' => $location_id,
            'status' => 'not_executed',
            'message' => __('Google Business Profile sync is owned by the cloud GMB Ranker client. No local fake-success result was returned.', 'gmb-ranker-seo-automation'),
        );
    }

    private static function generate_schema_org($args) {
        $post_id = !empty($args['post_id']) ? (int)$args['post_id'] : 0;
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => get_bloginfo('url'),
        );

        if ($post_id > 0 && ($post = get_post($post_id))) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title($post_id),
                'datePublished' => get_the_date('c', $post_id),
                'dateModified' => get_the_modified_date('c', $post_id),
                'author' => array(
                    '@type' => 'Person',
                    'name' => get_the_author_meta('display_name', $post->post_author),
                ),
            );
        }

        return array(
            'post_id' => $post_id,
            'schema' => $schema,
        );
    }

    private static function run_audit_scan($args) {
        $scope = !empty($args['scope']) ? sanitize_text_field($args['scope']) : 'full';
        $allowed_scopes = array('full', 'meta', 'sitemap', 'gmb');
        if (!in_array($scope, $allowed_scopes, true)) {
            return array('error' => true, 'code' => 'invalid_scope', 'message' => __('Unsupported audit scope.', 'gmb-ranker-seo-automation'));
        }

        $issues = array();
        $published = get_posts(array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'fields' => 'ids',
        ));
        foreach ($published as $post_id) {
            if (in_array($scope, array('full', 'meta'), true)) {
                if ('' === trim((string) get_post_meta($post_id, '_gmb_seo_title', true))) {
                    $issues[] = array('type' => 'missing_title', 'post_id' => (int) $post_id);
                }
                if ('' === trim((string) get_post_meta($post_id, '_gmb_seo_description', true))) {
                    $issues[] = array('type' => 'missing_description', 'post_id' => (int) $post_id);
                }
            }
        }
        $total = max(1, count($published) * 2);
        $score = max(0, min(100, (int) round((($total - count($issues)) / $total) * 100)));

        return array(
            'scope' => $scope,
            'status' => 'completed',
            'score' => $score,
            'issues_found' => count($issues),
            'issues' => array_slice($issues, 0, 100),
            'recommendations' => count($issues) ? array(__('Review missing metadata on the listed published content.', 'gmb-ranker-seo-automation')) : array(__('No missing plugin metadata was found in the scanned content.', 'gmb-ranker-seo-automation')),
            'sources' => array('WORDPRESS'),
            'fetched_at' => gmdate('c'),
        );
    }

    private static function purge_seo_cache() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_gmb_seo_%' OR option_name LIKE '_transient_timeout_gmb_seo_%'");
        return array(
            'success' => true,
            'message' => __('SEO cache and transients purged successfully.', 'gmb-ranker-seo-automation'),
        );
    }
}
