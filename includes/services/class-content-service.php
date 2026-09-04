<?php
/**
 * Content Service for GMB Ranker SEO Automation
 *
 * Production SEO content mutation & brief/prompt orchestration service providing:
 * - DOM-aware HTML link injection
 * - Safe anchor matching & URL security
 * - Internal-link validation & duplicate link prevention
 * - Elementor JSON traversal & Gutenberg block support
 * - Dynamic, site-context-driven structured content brief normalization
 * - Prompt-injection-safe dynamic LLM writing prompt construction
 * - AI-generated content validation before persistence
 * - Backward-compatible public APIs
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

    /* ==========================================================================
       SECTION 1: STRUCTURED CONTENT BRIEF & DYNAMIC PROMPT BUILDER
       ========================================================================== */

    /**
     * Build and normalize a structured SEO content brief from raw input data
     *
     * @param array $data Input brief parameters
     * @return array Normalized structured content brief
     */
    public function build_content_brief(array $data = array()) {
        $site_name = get_bloginfo('name') ?: get_option('blogname', 'WordPress Site');
        $site_desc = get_bloginfo('description') ?: '';
        $home_url  = esc_url(home_url('/'));
        $locale    = get_locale();

        // 1. Site Context
        $site_context = array(
            'site_name'        => !empty($data['site_name']) ? sanitize_text_field($data['site_name']) : sanitize_text_field($site_name),
            'home_url'         => !empty($data['home_url']) ? esc_url_raw($data['home_url']) : $home_url,
            'site_description' => !empty($data['site_description']) ? sanitize_text_field($data['site_description']) : sanitize_text_field($site_desc),
            'language'         => !empty($data['language']) ? sanitize_text_field($data['language']) : $locale,
            'brand_voice'      => !empty($data['brand_voice']) ? sanitize_text_field($data['brand_voice']) : '',
        );

        // 2. Core Topic & Keywords
        $title            = !empty($data['title']) ? sanitize_text_field($data['title']) : '';
        $primary_keyword  = !empty($data['primary_keyword']) ? sanitize_text_field($data['primary_keyword']) : (!empty($data['topic']) ? sanitize_text_field($data['topic']) : '');
        
        $secondary_kws = array();
        if (!empty($data['secondary_keywords']) && is_array($data['secondary_keywords'])) {
            foreach ($data['secondary_keywords'] as $kw) {
                if (is_string($kw) && strlen(trim($kw)) > 0) {
                    $secondary_kws[] = sanitize_text_field(trim($kw));
                }
            }
        } elseif (!empty($data['secondary_keywords']) && is_string($data['secondary_keywords'])) {
            $parts = explode(',', $data['secondary_keywords']);
            foreach ($parts as $p) {
                if (strlen(trim($p)) > 0) {
                    $secondary_kws[] = sanitize_text_field(trim($p));
                }
            }
        }

        // 3. Search Intent & Strategic Angle
        $intent   = !empty($data['intent']) ? sanitize_text_field($data['intent']) : '';
        $audience = !empty($data['audience']) ? sanitize_text_field($data['audience']) : '';
        $angle    = !empty($data['angle']) ? sanitize_text_field($data['angle']) : '';
        $objective= !empty($data['objective']) ? sanitize_text_field($data['objective']) : '';
        $opp_reason= !empty($data['opportunity_reason']) ? sanitize_text_field($data['opportunity_reason']) : (!empty($data['why_it_wins']) ? sanitize_text_field($data['why_it_wins']) : '');

        // 4. GSC / Performance Evidence
        $gsc_evidence = array();
        if (!empty($data['gsc_evidence']) && is_array($data['gsc_evidence'])) {
            $raw_gsc = $data['gsc_evidence'];
            $gsc_evidence = array(
                'query'       => !empty($raw_gsc['query']) ? sanitize_text_field($raw_gsc['query']) : $primary_keyword,
                'impressions' => isset($raw_gsc['impressions']) ? intval($raw_gsc['impressions']) : null,
                'clicks'      => isset($raw_gsc['clicks']) ? intval($raw_gsc['clicks']) : null,
                'ctr'         => isset($raw_gsc['ctr']) ? floatval($raw_gsc['ctr']) : null,
                'position'    => isset($raw_gsc['position']) ? floatval($raw_gsc['position']) : null,
                'reason'      => !empty($raw_gsc['reason']) ? sanitize_text_field($raw_gsc['reason']) : $opp_reason,
            );
        }

        // 5. Outline Structure
        $outline = array();
        if (!empty($data['outline']) && is_array($data['outline'])) {
            foreach ($data['outline'] as $item) {
                if (is_string($item)) {
                    $clean_item = sanitize_text_field(trim($item));
                    if (!empty($clean_item)) {
                        $outline[] = array('level' => 2, 'heading' => $clean_item, 'purpose' => '');
                    }
                } elseif (is_array($item)) {
                    $heading = !empty($item['heading']) ? sanitize_text_field($item['heading']) : (!empty($item['text']) ? sanitize_text_field($item['text']) : '');
                    if (!empty($heading)) {
                        $level = !empty($item['level']) ? max(2, min(6, intval($item['level']))) : 2;
                        $purpose = !empty($item['purpose']) ? sanitize_text_field($item['purpose']) : '';
                        $outline[] = array('level' => $level, 'heading' => $heading, 'purpose' => $purpose);
                    }
                }
            }
        }

        // Delegate to Content AI service for intent/outline enrichment if available and outline is empty
        if (empty($outline) && class_exists('GMB_Ranker_SEO_Content_AI') && (!empty($title) || !empty($primary_keyword))) {
            $ai_brief = GMB_Ranker_SEO_Content_AI::build_content_brief($title, $primary_keyword);
            if (empty($intent) && !empty($ai_brief['search_intent'])) {
                $intent = sanitize_text_field($ai_brief['search_intent']);
            }
            $ai_outline = GMB_Ranker_SEO_Content_AI::generate_dynamic_outline($ai_brief);
            if (is_array($ai_outline) && !empty($ai_outline)) {
                foreach ($ai_outline as $h) {
                    $outline[] = array('level' => 2, 'heading' => sanitize_text_field($h), 'purpose' => '');
                }
            }
        }

        // 6. Content Requirements
        $requirements = array();
        if (!empty($data['requirements']) && is_array($data['requirements'])) {
            foreach ($data['requirements'] as $req) {
                if (is_string($req) && strlen(trim($req)) > 0) {
                    $requirements[] = sanitize_text_field(trim($req));
                }
            }
        }

        // 7. Tone & CTA
        $tone = !empty($data['tone']) ? sanitize_text_field($data['tone']) : 'Professional, informative, and engaging';
        $cta  = !empty($data['cta']) ? sanitize_text_field($data['cta']) : '';

        // 8. Internal Links
        $internal_links = array();
        if (!empty($data['internal_links']) && is_array($data['internal_links'])) {
            foreach ($data['internal_links'] as $link) {
                if (is_array($link) && !empty($link['url'])) {
                    $clean_link_url = esc_url_raw($link['url']);
                    $clean_link_anchor = !empty($link['anchor']) ? sanitize_text_field($link['anchor']) : sanitize_text_field($link['text'] ?? '');
                    if (!empty($clean_link_url)) {
                        $internal_links[] = array(
                            'anchor' => $clean_link_anchor,
                            'url'    => $clean_link_url,
                        );
                    }
                }
            }
        }

        // 9. Constraints & Meta
        $constraints = array(
            'post_type'      => !empty($data['post_type']) ? sanitize_key($data['post_type']) : 'post',
            'post_id'        => !empty($data['post_id']) ? intval($data['post_id']) : 0,
            'word_count_min' => !empty($data['word_count_min']) ? intval($data['word_count_min']) : 0,
        );

        return array(
            'site_context'       => $site_context,
            'title'              => $title,
            'primary_keyword'    => $primary_keyword,
            'secondary_keywords' => array_values(array_unique($secondary_kws)),
            'intent'             => $intent,
            'audience'           => $audience,
            'angle'              => $angle,
            'objective'          => $objective,
            'opportunity_reason' => $opp_reason,
            'gsc_evidence'       => $gsc_evidence,
            'outline'            => $outline,
            'requirements'       => $requirements,
            'tone'               => $tone,
            'cta'                => $cta,
            'internal_links'     => $internal_links,
            'constraints'        => $constraints,
        );
    }

    /**
     * Build an LLM writing prompt from a structured content brief
     *
     * @param array $brief Structured content brief
     * @return array Structured result contract [ 'success' => bool, 'prompt' => string, 'brief' => array, ... ]
     */
    public function build_content_prompt(array $brief) {
        $normalized_brief = $this->build_content_brief($brief);

        if (empty($normalized_brief['title']) && empty($normalized_brief['primary_keyword'])) {
            return array(
                'success'    => false,
                'status'     => 'invalid_brief',
                'prompt'     => '',
                'brief'      => $normalized_brief,
                'warnings'   => array(__('Content brief requires at least a title or primary keyword.', 'gmb-ranker-seo-automation')),
                'metadata'   => array(),
                'reason'     => __('Content brief requires at least a title or primary keyword.', 'gmb-ranker-seo-automation'),
                'error_code' => 'invalid_brief',
            );
        }

        $warnings = array();
        $sections = array();

        // 1. Task Definition
        $sections[] = "### CONTENT CREATION TASK\nWrite a high-quality, search-intent-aligned article based strictly on the following structured SEO brief.";

        // 2. Site Context
        $sc = $normalized_brief['site_context'];
        $sections[] = "### SITE & BRAND CONTEXT\n" .
            "- Site Name: " . $this->sanitize_prompt_value($sc['site_name']) . "\n" .
            "- Site URL: " . $this->sanitize_prompt_value($sc['home_url']) .
            (!empty($sc['site_description']) ? "\n- Description: " . $this->sanitize_prompt_value($sc['site_description']) : "") .
            (!empty($sc['brand_voice']) ? "\n- Brand Voice: " . $this->sanitize_prompt_value($sc['brand_voice']) : "");

        // 3. Core Brief Details
        $brief_lines = array();
        if (!empty($normalized_brief['title'])) {
            $brief_lines[] = "- Target Title: " . $this->sanitize_prompt_value($normalized_brief['title']);
        }
        if (!empty($normalized_brief['primary_keyword'])) {
            $brief_lines[] = "- Primary Keyword / Topic: " . $this->sanitize_prompt_value($normalized_brief['primary_keyword']);
        }
        if (!empty($normalized_brief['secondary_keywords'])) {
            $brief_lines[] = "- Secondary Keywords: " . implode(', ', array_map(array($this, 'sanitize_prompt_value'), $normalized_brief['secondary_keywords']));
        }
        if (!empty($normalized_brief['intent'])) {
            $brief_lines[] = "- Search Intent: " . $this->sanitize_prompt_value($normalized_brief['intent']);
        }
        if (!empty($normalized_brief['audience'])) {
            $brief_lines[] = "- Target Audience: " . $this->sanitize_prompt_value($normalized_brief['audience']);
        }
        if (!empty($normalized_brief['angle'])) {
            $brief_lines[] = "- Strategic Angle: " . $this->sanitize_prompt_value($normalized_brief['angle']);
        }
        if (!empty($normalized_brief['objective'])) {
            $brief_lines[] = "- Content Objective: " . $this->sanitize_prompt_value($normalized_brief['objective']);
        }

        if (!empty($brief_lines)) {
            $sections[] = "### ARTICLE BRIEF\n" . implode("\n", $brief_lines);
        }

        // 4. Opportunity / Reason
        if (!empty($normalized_brief['opportunity_reason'])) {
            $sections[] = "### WHY THIS CONTENT / OPPORTUNITY\n" . $this->sanitize_prompt_value($normalized_brief['opportunity_reason']);
        }

        // 5. GSC Evidence
        if (!empty($normalized_brief['gsc_evidence']) && !empty($normalized_brief['gsc_evidence']['query'])) {
            $ge = $normalized_brief['gsc_evidence'];
            $gsc_str = "- Query: " . $this->sanitize_prompt_value($ge['query']);
            if (isset($ge['impressions']) && $ge['impressions'] !== null) {
                $gsc_str .= "\n- Impressions: " . number_format($ge['impressions']);
            }
            if (isset($ge['clicks']) && $ge['clicks'] !== null) {
                $gsc_str .= " | Clicks: " . number_format($ge['clicks']);
            }
            if (isset($ge['ctr']) && $ge['ctr'] !== null) {
                $gsc_str .= " | CTR: " . number_format($ge['ctr'], 2) . "%";
            }
            if (isset($ge['position']) && $ge['position'] !== null) {
                $gsc_str .= " | Avg Position: " . number_format($ge['position'], 1);
            }
            if (!empty($ge['reason'])) {
                $gsc_str .= "\n- Context: " . $this->sanitize_prompt_value($ge['reason']);
            }
            $sections[] = "### SEARCH PERFORMANCE EVIDENCE\n" . $gsc_str;
        }

        // 6. Outline
        if (!empty($normalized_brief['outline'])) {
            $outline_lines = array();
            foreach ($normalized_brief['outline'] as $item) {
                $tag = "H" . $item['level'];
                $line = "- " . $tag . ": " . $this->sanitize_prompt_value($item['heading']);
                if (!empty($item['purpose'])) {
                    $line .= " (" . $this->sanitize_prompt_value($item['purpose']) . ")";
                }
                $outline_lines[] = $line;
            }
            $sections[] = "### SUGGESTED OUTLINE\n" . implode("\n", $outline_lines);
        }

        // 7. Requirements & Guidelines
        $req_lines = array(
            "- Maintain a clean, professional heading hierarchy (H2, H3).",
            "- Use natural, search-intent-aligned language without keyword stuffing.",
            "- Provide actionable, practical advice tailored to the target audience.",
        );
        if (!empty($normalized_brief['requirements'])) {
            foreach ($normalized_brief['requirements'] as $req) {
                $req_lines[] = "- " . $this->sanitize_prompt_value($req);
            }
        }
        $sections[] = "### CONTENT REQUIREMENTS\n" . implode("\n", $req_lines);

        // 8. Voice & Tone
        if (!empty($normalized_brief['tone'])) {
            $sections[] = "### VOICE & TONE\n" . $this->sanitize_prompt_value($normalized_brief['tone']);
        }

        // 9. Call to Action (CTA)
        if (!empty($normalized_brief['cta'])) {
            $sections[] = "### CALL TO ACTION (CTA)\n" . $this->sanitize_prompt_value($normalized_brief['cta']);
        }

        // 10. Internal Link Opportunities
        if (!empty($normalized_brief['internal_links'])) {
            $link_lines = array();
            foreach ($normalized_brief['internal_links'] as $il) {
                $link_lines[] = "- Link anchor \"" . $this->sanitize_prompt_value($il['anchor']) . "\" to " . esc_url_raw($il['url']);
            }
            $sections[] = "### INTERNAL LINKING OPPORTUNITIES\n" . implode("\n", $link_lines);
        }

        // 11. Output Format Constraints
        $sections[] = "### OUTPUT FORMATTING CONSTRAINTS\n" .
            "- Return valid HTML content using <h2>, <h3>, <p>, <ul>, <ol>, <strong> tags.\n" .
            "- Do NOT wrap output in markdown code fences (such as ```html).\n" .
            "- Do NOT write introductory filler or conversational response text (such as 'Here is your article').";

        $full_prompt = implode("\n\n", $sections);

        return array(
            'success'    => true,
            'status'     => 'completed',
            'prompt'     => $full_prompt,
            'brief'      => $normalized_brief,
            'warnings'   => $warnings,
            'metadata'   => array(
                'token_estimate' => (int)ceil(strlen($full_prompt) / 4),
                'has_gsc_data'   => !empty($normalized_brief['gsc_evidence']),
                'has_outline'    => !empty($normalized_brief['outline']),
            ),
            'reason'     => __('Content prompt generated successfully from structured brief.', 'gmb-ranker-seo-automation'),
            'error_code' => '',
        );
    }

    /**
     * Validate AI-generated content before mutation and database persistence
     *
     * @param string $content  Generated content string
     * @param array  $expected Optional validation expectations
     * @return array Structured result contract [ 'success' => bool, 'status' => string, 'content' => string, ... ]
     */
    public function validate_generated_content($content, array $expected = array()) {
        $raw_content = (string)$content;
        $clean_text  = trim(wp_strip_all_tags($raw_content));

        if (empty($clean_text)) {
            return array(
                'success'    => false,
                'status'     => 'empty_content',
                'content'    => '',
                'reason'     => __('Generated content is empty or contains no readable text.', 'gmb-ranker-seo-automation'),
                'error_code' => 'empty_content',
            );
        }

        $min_words = isset($expected['min_words']) ? intval($expected['min_words']) : 30;
        $word_count = str_word_count($clean_text);
        if ($word_count < $min_words) {
            return array(
                'success'    => false,
                'status'     => 'content_too_short',
                'content'    => $raw_content,
                'reason'     => sprintf(__('Generated content is too short (%d words, expected at least %d).', 'gmb-ranker-seo-automation'), $word_count, $min_words),
                'error_code' => 'content_too_short',
            );
        }

        // Check for malicious scripts or unsafe executable content
        if (preg_match('/<script|javascript:|on\w+\s*=/i', $raw_content)) {
            return array(
                'success'    => false,
                'status'     => 'script_detected',
                'content'    => '',
                'reason'     => __('Generated content contained forbidden script elements or event handlers.', 'gmb-ranker-seo-automation'),
                'error_code' => 'script_detected',
            );
        }

        // Check for prompt leakage
        if (preg_match('/### CONTENT CREATION TASK|### OUTPUT FORMATTING CONSTRAINTS|SYSTEM PROMPT:/i', $raw_content)) {
            return array(
                'success'    => false,
                'status'     => 'prompt_leakage',
                'content'    => $raw_content,
                'reason'     => __('Generated content contained system prompt leakage.', 'gmb-ranker-seo-automation'),
                'error_code' => 'prompt_leakage',
            );
        }

        // Sanitize allowed HTML
        $safe_html = wp_kses_post($raw_content);

        return array(
            'success'    => true,
            'status'     => 'valid',
            'content'    => $safe_html,
            'reason'     => __('Generated content passed all safety and structural validation checks.', 'gmb-ranker-seo-automation'),
            'error_code' => '',
        );
    }

    /**
     * Sanitize string value for LLM prompt inclusion (prevents prompt injection & markdown fence breaking)
     *
     * @param string $str
     * @return string
     */
    protected function sanitize_prompt_value($str) {
        if (!is_string($str)) {
            return '';
        }
        $clean = wp_strip_all_tags($str);
        $clean = str_replace(array("```", "###", "System:", "User:"), array("'''", "---", "System-Data:", "User-Data:"), $clean);
        return trim($clean);
    }

    /* ==========================================================================
       SECTION 2: CONTENT MUTATION & HTML LINK INJECTION
       ========================================================================== */

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

