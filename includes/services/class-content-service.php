<?php
/**
 * Content Service for GMB Ranker SEO Automation
 *
 * Production SEO content mutation service providing DOM-aware HTML link injection,
 * safe anchor matching, URL security, internal-link validation, Elementor JSON traversal,
 * Gutenberg block support, atomicity, duplicate prevention, and backward-compatible public APIs.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Content_Service {

    /**
     * Forbidden HTML tags that must never contain injected links
     *
     * @var array
     */
    protected static $forbidden_tags = array(
        'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'script', 'style', 'code', 'pre', 'textarea',
        'option', 'select', 'title', 'head', 'noscript',
        'svg', 'math', 'button', 'input', 'iframe', 'canvas',
    );

    /**
     * Forbidden Gutenberg blocks that must not be modified for link injection
     *
     * @var array
     */
    protected static $forbidden_blocks = array(
        'core/heading',
        'core/code',
        'core/preformatted',
        'core/html',
        'core/button',
        'core/buttons',
    );

    /**
     * Elementor widgets containing editable text settings (excluding headings)
     *
     * @var array
     */
    protected static $elementor_text_widgets = array(
        'text-editor'    => array('editor'),
        'icon-box'       => array('description'),
        'image-box'      => array('description'),
        'testimonial'    => array('testimonial_content'),
        'alert'          => array('alert_title', 'alert_description'),
        'call-to-action' => array('description'),
        'toggle'         => array('tab_content'),
        'accordion'      => array('tab_content'),
    );

    /**
     * Inject an internal link into an HTML content string using DOM-aware parsing
     *
     * @param string $html     HTML content string
     * @param string $anchor   Anchor text to match
     * @param string $url      Target URL
     * @param array  $options  Optional execution settings
     * @return array Structured result contract [ 'success' => bool, 'status' => string, 'html' => string, ... ]
     */
    public function inject_link_in_html($html, $anchor, $url, array $options = array()) {
        $raw_html       = (string)$html;
        $anchor_trimmed = trim((string)$anchor);

        // 1. Basic Input Validation
        if (empty($raw_html)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_input',
                'html'           => $raw_html,
                'insertions'     => 0,
                'matched_anchor' => '',
                'target_url'     => (string)$url,
                'reason'         => __('HTML content is empty.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'invalid_input',
            );
        }

        if (empty($anchor_trimmed)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_input',
                'html'           => $raw_html,
                'insertions'     => 0,
                'matched_anchor' => '',
                'target_url'     => (string)$url,
                'reason'         => __('Anchor text cannot be empty.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'invalid_input',
            );
        }

        // 2. URL Validation & Sanitization
        $clean_url = esc_url_raw(trim((string)$url));
        if (empty($clean_url)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_target',
                'html'           => $raw_html,
                'insertions'     => 0,
                'matched_anchor' => $anchor_trimmed,
                'target_url'     => (string)$url,
                'reason'         => __('Target URL is invalid or unsafe.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'invalid_target',
            );
        }

        $scheme = strtolower((string)parse_url($clean_url, PHP_URL_SCHEME));
        if (!empty($scheme) && !in_array($scheme, array('http', 'https'), true)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_target',
                'html'           => $raw_html,
                'insertions'     => 0,
                'matched_anchor' => $anchor_trimmed,
                'target_url'     => $clean_url,
                'reason'         => sprintf(__('URL scheme "%s" is not allowed for SEO link injection.', 'gmb-ranker-seo-automation'), esc_html($scheme)),
                'error_code'     => 'invalid_target',
            );
        }

        // 3. Self-Link Prevention
        $target_post_id = !empty($options['target_post_id']) ? intval($options['target_post_id']) : 0;
        if ($target_post_id > 0) {
            $post_permalink = get_permalink($target_post_id);
            if ($post_permalink && $this->urls_are_equivalent($clean_url, $post_permalink)) {
                return array(
                    'success'        => false,
                    'status'         => 'invalid_target',
                    'html'           => $raw_html,
                    'insertions'     => 0,
                    'matched_anchor' => $anchor_trimmed,
                    'target_url'     => $clean_url,
                    'reason'         => __('Self-linking to the same post is prohibited.', 'gmb-ranker-seo-automation'),
                    'error_code'     => 'self_link_prevented',
                );
            }
        }

        $max_insertions = isset($options['max_insertions']) ? max(1, intval($options['max_insertions'])) : 1;

        // 4. DOM-Aware Parsing & Mutation
        try {
            if (!class_exists('DOMDocument') || !class_exists('DOMXPath')) {
                return $this->inject_link_in_html_fallback($raw_html, $anchor_trimmed, $clean_url, $max_insertions);
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);

            // Wrap in XML encoding container for UTF-8 preservation in libxml
            $xml_container = '<?xml encoding="UTF-8"><div id="gmb-content-wrapper">' . $raw_html . '</div>';
            $loaded = $dom->loadHTML($xml_container, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            if (!$loaded) {
                return $this->inject_link_in_html_fallback($raw_html, $anchor_trimmed, $clean_url, $max_insertions);
            }

            $xpath = new DOMXPath($dom);

            // Duplicate Link Protection: Check if target link already exists in DOM
            $existing_a_tags = $xpath->query('//a');
            if ($existing_a_tags) {
                foreach ($existing_a_tags as $a_tag) {
                    if ($a_tag instanceof DOMElement) {
                        $href = $a_tag->getAttribute('href');
                        if (!empty($href) && $this->urls_are_equivalent($href, $clean_url)) {
                            return array(
                                'success'        => false,
                                'status'         => 'already_linked',
                                'html'           => $raw_html,
                                'insertions'     => 0,
                                'matched_anchor' => $anchor_trimmed,
                                'target_url'     => $clean_url,
                                'reason'         => __('Target URL already exists in content.', 'gmb-ranker-seo-automation'),
                                'error_code'     => 'already_linked',
                            );
                        }
                    }
                }
            }

            // Query text nodes
            $text_nodes = $xpath->query('//text()');
            if (!$text_nodes || $text_nodes->length === 0) {
                return array(
                    'success'        => false,
                    'status'         => 'no_match',
                    'html'           => $raw_html,
                    'insertions'     => 0,
                    'matched_anchor' => $anchor_trimmed,
                    'target_url'     => $clean_url,
                    'reason'         => __('No text nodes found in HTML content.', 'gmb-ranker-seo-automation'),
                    'error_code'     => 'no_match',
                );
            }

            $insertions_count    = 0;
            $last_matched_anchor = '';
            $escaped_anchor      = preg_quote($anchor_trimmed, '/');
            // Multibyte Unicode-aware word boundary matching
            $pattern             = '/(?<![\p{L}\p{N}_])(' . $escaped_anchor . ')(?![\p{L}\p{N}_])/iu';

            foreach ($text_nodes as $node) {
                if ($insertions_count >= $max_insertions) {
                    break;
                }

                if (!($node instanceof DOMText)) {
                    continue;
                }

                // Ancestor check for forbidden tags
                $parent = $node->parentNode;
                $is_forbidden = false;
                while ($parent && $parent->nodeType === XML_ELEMENT_NODE) {
                    $tag_name = strtolower($parent->nodeName);
                    if (in_array($tag_name, self::$forbidden_tags, true)) {
                        $is_forbidden = true;
                        break;
                    }
                    if ($parent->hasAttribute('data-gmb-no-link')) {
                        $is_forbidden = true;
                        break;
                    }
                    $parent = $parent->parentNode;
                }

                if ($is_forbidden) {
                    continue;
                }

                $text_value = $node->nodeValue;
                if (empty($text_value) || strlen(trim($text_value)) === 0) {
                    continue;
                }

                if (preg_match($pattern, $text_value, $matches, PREG_OFFSET_CAPTURE)) {
                    $matched_str  = $matches[1][0];
                    $match_offset = $matches[1][1];
                    $match_len    = strlen($matched_str);

                    $text_before = substr($text_value, 0, $match_offset);
                    $text_after  = substr($text_value, $match_offset + $match_len);

                    // Build link element
                    $link_node = $dom->createElement('a');
                    $link_node->setAttribute('href', $clean_url);
                    $link_node->setAttribute('data-gmb-link', 'injected');
                    $link_node->nodeValue = $matched_str;

                    $parent_elem = $node->parentNode;
                    if ($text_before !== '') {
                        $parent_elem->insertBefore($dom->createTextNode($text_before), $node);
                    }
                    $parent_elem->insertBefore($link_node, $node);
                    if ($text_after !== '') {
                        $parent_elem->insertBefore($dom->createTextNode($text_after), $node);
                    }
                    $parent_elem->removeChild($node);

                    $insertions_count++;
                    $last_matched_anchor = $matched_str;
                }
            }

            if ($insertions_count === 0) {
                return array(
                    'success'        => false,
                    'status'         => 'no_match',
                    'html'           => $raw_html,
                    'insertions'     => 0,
                    'matched_anchor' => $anchor_trimmed,
                    'target_url'     => $clean_url,
                    'reason'         => __('Anchor text match not found in eligible text elements.', 'gmb-ranker-seo-automation'),
                    'error_code'     => 'no_match',
                );
            }

            // Extract inner HTML of gmb-content-wrapper container
            $wrapper = $dom->getElementById('gmb-content-wrapper');
            $new_html = '';
            if ($wrapper) {
                foreach ($wrapper->childNodes as $child) {
                    $new_html .= $dom->saveHTML($child);
                }
            } else {
                $new_html = $dom->saveHTML();
                $new_html = preg_replace('/^<\?xml encoding="UTF-8"\?>\s*/i', '', $new_html);
            }

            return array(
                'success'        => true,
                'status'         => 'completed',
                'html'           => $new_html,
                'insertions'     => $insertions_count,
                'matched_anchor' => $last_matched_anchor,
                'target_url'     => $clean_url,
                'reason'         => __('Link injected successfully into HTML content.', 'gmb-ranker-seo-automation'),
                'error_code'     => '',
            );

        } catch (\Throwable $e) {
            return array(
                'success'        => false,
                'status'         => 'error',
                'html'           => $raw_html,
                'insertions'     => 0,
                'matched_anchor' => $anchor_trimmed,
                'target_url'     => $clean_url,
                'reason'         => sprintf(__('DOM parsing error during link injection: %s', 'gmb-ranker-seo-automation'), esc_html($e->getMessage())),
                'error_code'     => 'parser_exception',
            );
        }
    }

    /**
     * Safe regex fallback parser if DOM extension is missing or fails
     *
     * @param string $html
     * @param string $anchor
     * @param string $url
     * @param int    $max_insertions
     * @return array
     */
    protected function inject_link_in_html_fallback($html, $anchor, $url, $max_insertions = 1) {
        $escaped_anchor = preg_quote($anchor, '/');
        // Negative lookahead preventing injection inside HTML tags or existing <a> tags
        $pattern = '/(?!(?:[^<]+>|[^>]+<\/a>))(?<![\p{L}\p{N}_])(' . $escaped_anchor . ')(?![\p{L}\p{N}_])/iu';
        $link_tag = '<a href="' . esc_url($url) . '" data-gmb-link="injected">$1</a>';

        $count = 0;
        $new_html = preg_replace($pattern, $link_tag, $html, $max_insertions, $count);

        $success = ($count > 0);
        return array(
            'success'        => $success,
            'status'         => $success ? 'completed' : 'no_match',
            'html'           => $success ? $new_html : $html,
            'insertions'     => $count,
            'matched_anchor' => $anchor,
            'target_url'     => $url,
            'reason'         => $success ? __('Link injected successfully via fallback.', 'gmb-ranker-seo-automation') : __('Anchor text not found.', 'gmb-ranker-seo-automation'),
            'error_code'     => $success ? '' : 'no_match',
        );
    }

    /**
     * Inject an internal link into Elementor builder json structure (Pass-by-reference API)
     *
     * @param array  $elements  Elementor elements array
     * @param string $anchor    Anchor text
     * @param string $url       Target URL
     * @param bool   $injected  Updated to true if injection succeeded
     */
    public function inject_link_in_elementor_data(&$elements, $anchor, $url, &$injected) {
        if (!is_array($elements)) {
            return;
        }

        $max_insertions = 1;
        $current_count  = is_int($injected) ? $injected : ($injected ? 1 : 0);
        if ($current_count >= $max_insertions) {
            return;
        }

        foreach ($elements as &$element) {
            if ($current_count >= $max_insertions) {
                break;
            }

            if (!is_array($element)) {
                continue;
            }

            $widget_type = !empty($element['widgetType']) ? sanitize_key($element['widgetType']) : '';

            // NEVER inject into heading content
            if ($widget_type === 'heading') {
                continue;
            }

            if (!empty($widget_type) && isset($element['settings']) && is_array($element['settings'])) {
                $target_settings = isset(self::$elementor_text_widgets[$widget_type])
                    ? self::$elementor_text_widgets[$widget_type]
                    : array('editor', 'description', 'text', 'content');

                foreach ($target_settings as $setting_key) {
                    if ($current_count >= $max_insertions) {
                        break;
                    }

                    if (isset($element['settings'][$setting_key]) && is_string($element['settings'][$setting_key])) {
                        $content_str = $element['settings'][$setting_key];

                        if (!empty($content_str) && strlen(trim($content_str)) > 5) {
                            $res = $this->inject_link_in_html($content_str, $anchor, $url);
                            if (!empty($res['success'])) {
                                $element['settings'][$setting_key] = $res['html'];
                                $current_count++;
                                $injected = true;
                                break;
                            }
                        }
                    }
                }
            }

            // Recursively process child elements
            if (!empty($element['elements']) && is_array($element['elements'])) {
                $this->inject_link_in_elementor_data($element['elements'], $anchor, $url, $current_count);
                if ($current_count > 0) {
                    $injected = true;
                }
            }
        }
    }

    /**
     * Update post content with new link (Backward-compatible API returning bool)
     *
     * @param int    $post_id
     * @param string $anchor
     * @param string $url
     * @return bool
     */
    public function inject_link_in_post($post_id, $anchor, $url) {
        $result = $this->inject_link_in_post_ex($post_id, $anchor, $url);
        return !empty($result['success']);
    }

    /**
     * Extended structured mutation method for post content
     *
     * @param int    $post_id
     * @param string $anchor
     * @param string $url
     * @param array  $options
     * @return array Structured result contract
     */
    public function inject_link_in_post_ex($post_id, $anchor, $url, array $options = array()) {
        $clean_id = intval($post_id);
        if ($clean_id <= 0) {
            return array(
                'success'        => false,
                'status'         => 'invalid_input',
                'reason'         => __('Invalid post ID provided.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'invalid_post_id',
                'post_id'        => 0,
                'content_source' => 'none',
                'persistence'    => false,
            );
        }

        $post = get_post($clean_id);
        if (!$post || in_array($post->post_status, array('trash', 'auto-draft'), true)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_input',
                'reason'         => sprintf(__('Post ID %d does not exist or is in an invalid status.', 'gmb-ranker-seo-automation'), $clean_id),
                'error_code'     => 'post_not_found',
                'post_id'        => $clean_id,
                'content_source' => 'none',
                'persistence'    => false,
            );
        }

        // Capability check if user session is active
        if (is_user_logged_in() && !current_user_can('edit_post', $clean_id)) {
            return array(
                'success'        => false,
                'status'         => 'security_rejected',
                'reason'         => __('User lacks capability to edit target post.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'authorization_failed',
                'post_id'        => $clean_id,
                'content_source' => 'none',
                'persistence'    => false,
            );
        }

        $clean_url = esc_url_raw(trim((string)$url));

        // Self-Link Prevention
        $post_permalink = get_permalink($clean_id);
        if ($post_permalink && $this->urls_are_equivalent($clean_url, $post_permalink)) {
            return array(
                'success'        => false,
                'status'         => 'invalid_target',
                'reason'         => __('Self-linking to the same post is prohibited.', 'gmb-ranker-seo-automation'),
                'error_code'     => 'self_link_prevented',
                'post_id'        => $clean_id,
                'content_source' => 'none',
                'persistence'    => false,
            );
        }

        // 1. Content Source Discovery: Elementor Builder
        $is_elementor  = get_post_meta($clean_id, '_elementor_edit_mode', true) === 'builder';
        $elementor_raw = get_post_meta($clean_id, '_elementor_data', true);

        if ($is_elementor && !empty($elementor_raw)) {
            $elements = json_decode($elementor_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($elements)) {
                $injected = false;
                $this->inject_link_in_elementor_data($elements, $anchor, $clean_url, $injected);
                if ($injected) {
                    $encoded = wp_slash(wp_json_encode($elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    $updated = update_post_meta($clean_id, '_elementor_data', $encoded);

                    // Clear Elementor CSS cache if present
                    delete_post_meta($clean_id, '_elementor_css');

                    return array(
                        'success'        => true,
                        'status'         => 'completed',
                        'reason'         => __('Link injected successfully into Elementor builder content.', 'gmb-ranker-seo-automation'),
                        'error_code'     => '',
                        'post_id'        => $clean_id,
                        'content_source' => 'elementor',
                        'persistence'    => true,
                        'insertions'     => 1,
                        'matched_anchor' => $anchor,
                        'target_url'     => $clean_url,
                    );
                }
            }
        }

        // 2. Content Source Discovery: Gutenberg Blocks
        if (function_exists('has_blocks') && function_exists('parse_blocks') && function_exists('serialize_blocks') && has_blocks($post->post_content)) {
            $blocks = parse_blocks($post->post_content);
            $injected_count = 0;

            if ($this->inject_link_in_gutenberg_blocks($blocks, $anchor, $clean_url, $injected_count)) {
                $new_content = serialize_blocks($blocks);
                $updated_id  = wp_update_post(array(
                    'ID'           => $clean_id,
                    'post_content' => $new_content,
                ));

                if (is_wp_error($updated_id) || $updated_id <= 0) {
                    return array(
                        'success'        => false,
                        'status'         => 'persistence_failed',
                        'reason'         => __('Failed to persist mutated Gutenberg block content to database.', 'gmb-ranker-seo-automation'),
                        'error_code'     => 'persistence_failed',
                        'post_id'        => $clean_id,
                        'content_source' => 'gutenberg',
                        'persistence'    => false,
                    );
                }

                return array(
                    'success'        => true,
                    'status'         => 'completed',
                    'reason'         => __('Link injected successfully into Gutenberg block content.', 'gmb-ranker-seo-automation'),
                    'error_code'     => '',
                    'post_id'        => $clean_id,
                    'content_source' => 'gutenberg',
                    'persistence'    => true,
                    'insertions'     => $injected_count,
                    'matched_anchor' => $anchor,
                    'target_url'     => $clean_url,
                );
            }
        }

        // 3. Content Source Discovery: Standard HTML / Classic Post Content
        $res = $this->inject_link_in_html($post->post_content, $anchor, $clean_url, array('target_post_id' => $clean_id));

        if (!empty($res['success'])) {
            $updated_id = wp_update_post(array(
                'ID'           => $clean_id,
                'post_content' => $res['html'],
            ));

            if (is_wp_error($updated_id) || $updated_id <= 0) {
                return array(
                    'success'        => false,
                    'status'         => 'persistence_failed',
                    'reason'         => __('Failed to persist mutated HTML post content to database.', 'gmb-ranker-seo-automation'),
                    'error_code'     => 'persistence_failed',
                    'post_id'        => $clean_id,
                    'content_source' => 'classic',
                    'persistence'    => false,
                );
            }

            return array(
                'success'        => true,
                'status'         => 'completed',
                'reason'         => __('Link injected successfully into post content.', 'gmb-ranker-seo-automation'),
                'error_code'     => '',
                'post_id'        => $clean_id,
                'content_source' => 'classic',
                'persistence'    => true,
                'insertions'     => isset($res['insertions']) ? $res['insertions'] : 1,
                'matched_anchor' => isset($res['matched_anchor']) ? $res['matched_anchor'] : $anchor,
                'target_url'     => $clean_url,
            );
        }

        return array(
            'success'        => false,
            'status'         => isset($res['status']) ? $res['status'] : 'no_match',
            'reason'         => isset($res['reason']) ? $res['reason'] : __('Anchor text match not found in post content.', 'gmb-ranker-seo-automation'),
            'error_code'     => isset($res['error_code']) ? $res['error_code'] : 'no_match',
            'post_id'        => $clean_id,
            'content_source' => $is_elementor ? 'elementor' : 'classic',
            'persistence'    => false,
            'insertions'     => 0,
            'matched_anchor' => $anchor,
            'target_url'     => $clean_url,
        );
    }

    /**
     * Recursively process Gutenberg blocks for link injection
     *
     * @param array  $blocks
     * @param string $anchor
     * @param string $url
     * @param int    $injected_count
     * @return bool
     */
    protected function inject_link_in_gutenberg_blocks(&$blocks, $anchor, $url, &$injected_count) {
        $mutated = false;
        if (!is_array($blocks)) {
            return false;
        }

        foreach ($blocks as &$block) {
            if ($injected_count >= 1) {
                break;
            }

            $block_name = !empty($block['blockName']) ? strtolower($block['blockName']) : '';

            // Skip forbidden blocks (headings, code, preformatted, html, buttons)
            if (in_array($block_name, self::$forbidden_blocks, true)) {
                continue;
            }

            if (!empty($block['innerHTML']) && is_string($block['innerHTML'])) {
                $res = $this->inject_link_in_html($block['innerHTML'], $anchor, $url);
                if (!empty($res['success'])) {
                    $block['innerHTML'] = $res['html'];
                    $mutated = true;
                    $injected_count++;

                    if (!empty($block['innerContent']) && is_array($block['innerContent'])) {
                        foreach ($block['innerContent'] as $idx => $content_part) {
                            if (is_string($content_part) && !empty($content_part)) {
                                $part_res = $this->inject_link_in_html($content_part, $anchor, $url);
                                if (!empty($part_res['success'])) {
                                    $block['innerContent'][$idx] = $part_res['html'];
                                }
                            }
                        }
                    }
                    break;
                }
            }

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                if ($this->inject_link_in_gutenberg_blocks($block['innerBlocks'], $anchor, $url, $injected_count)) {
                    $mutated = true;
                }
            }
        }

        return $mutated;
    }

    /**
     * Helper to compare two URLs for equivalence (strips trailing slashes, schemes, ports)
     *
     * @param string $url1
     * @param string $url2
     * @return bool
     */
    protected function urls_are_equivalent($url1, $url2) {
        $norm1 = strtolower(rtrim(preg_replace('/^https?:\/\//i', '', trim($url1)), '/'));
        $norm2 = strtolower(rtrim(preg_replace('/^https?:\/\//i', '', trim($url2)), '/'));

        return ($norm1 !== '' && $norm1 === $norm2);
    }
}

