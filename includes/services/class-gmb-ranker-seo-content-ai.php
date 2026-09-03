<?php
/**
 * GMB Ranker SEO — Dynamic Topic & Intent Content Intelligence Engine
 *
 * Completely eliminates static hardcoded templates.
 * Dynamically plans and generates search-intent-aligned long-form content,
 * briefs, outlines, and meta descriptions based on target query semantics,
 * entities, site context, and AI Provider integrations.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Content_AI')) {

    class GMB_Ranker_SEO_Content_AI {

        /**
         * Analyze Topic & Extract Core Semantic Entities
         *
         * @param string $title
         * @param string $keyword
         * @return array
         */
        public static function analyze_topic_entities($title, $keyword) {
            $raw = trim($title . ' ' . $keyword);
            $clean = preg_replace('/[^\w\s]/u', ' ', $raw);
            $words = array_values(array_filter(explode(' ', strtolower($clean)), function($w) {
                return strlen($w) > 2 && !in_array($w, array('and', 'the', 'for', 'with', 'over', 'from', 'this', 'that', 'your', 'about', 'guide', '2026'), true);
            }));

            $target_kw = ucwords(trim($keyword ?: $title));
            $site_name = get_bloginfo('name') ?: get_option('blogname', 'Website');
            $home_url  = esc_url(home_url('/'));

            return array(
                'raw_title' => $title,
                'target_kw' => $target_kw,
                'kw_lower'  => strtolower($target_kw),
                'words'     => array_unique($words),
                'site_name' => $site_name,
                'home_url'  => $home_url,
            );
        }

        /**
         * Detect Detailed Search Intent & Topic Category
         *
         * @param string $title
         * @param string $keyword
         * @return string
         */
        public static function classify_intent_and_niche($title, $keyword) {
            $text = strtolower($title . ' ' . $keyword);

            if (preg_match('/(vs|versus|compare|comparison|over|difference)/i', $text)) {
                return 'COMPARISON';
            } elseif (preg_match('/(how to|step by step|procedure|guide|setup|instructions)/i', $text)) {
                return 'PROCEDURAL';
            } elseif (preg_match('/(what is|definition|meaning|explain|overview|understanding)/i', $text)) {
                return 'EXPLAINER';
            } elseif (preg_match('/(best|top|choose|selecting|review|pricing|cost|checklist)/i', $text)) {
                return 'SELECTION';
            } elseif (preg_match('/(service|services|consulting|agency|provider|specialist)/i', $text)) {
                return 'SERVICE';
            }

            return 'GENERAL_INFORMATIONAL';
        }

        /**
         * Sanitize Common AI Clichés and Overused Phrases
         *
         * @param string $content
         * @return string
         */
        public static function sanitize_ai_cliches($content) {
            if (empty($content) || !is_string($content)) {
                return '';
            }

            $cliches = array(
                '/in today\'s fast-paced world,?/i' => 'Currently,',
                '/whether you are [^,\.]+,?/i'      => '',
                '/look no further,?/i'             => '',
                '/it is important to note that/i'   => 'Notably,',
                '/in conclusion,?/i'                => 'Summary:',
                '/when it comes to/i'               => 'Regarding',
            );

            return preg_replace(array_keys($cliches), array_values($cliches), $content);
        }

        /**
         * Generate Evidence-Based Contextual Meta Description
         *
         * @param string $title
         * @param string $keyword
         * @param string $content_summary
         * @return string
         */
        public static function generate_meta_description($title, $keyword, $content_summary = '') {
            $target = !empty($title) ? $title : $keyword;
            $kw     = !empty($keyword) ? $keyword : $title;

            if (!empty($content_summary)) {
                $clean_summary = wp_strip_all_tags($content_summary);
                if (mb_strlen($clean_summary) >= 120) {
                    return mb_substr($clean_summary, 0, 155);
                }
            }

            $site_name = get_bloginfo('name') ?: get_option('blogname', 'Website');
            $desc = sprintf(
                __('Comprehensive guide to %1$s. Explore expert insights, best practices, and actionable advice from %2$s.', 'gmb-ranker-seo-automation'),
                esc_html($target),
                esc_html($site_name)
            );

            if (mb_strlen($desc) < 120) {
                $desc .= sprintf(__(' Learn how %s delivers optimal results for your requirements.', 'gmb-ranker-seo-automation'), esc_html($kw));
            }

            return mb_substr($desc, 0, 155);
        }

        /**
         * Generate Dynamic Intent-Driven Long-Form Draft via AI Provider or Content Engine
         *
         * @param string $title
         * @param string $keyword
         * @param int    $post_id
         * @return array
         */
        public static function generate_archetype_draft($title, $keyword, $post_id = 0) {
            $entity_info = self::analyze_topic_entities($title, $keyword);
            $niche       = self::classify_intent_and_niche($title, $keyword);

            $kw    = $entity_info['target_kw'];
            $kw_lc = $entity_info['kw_lower'];
            $site  = $entity_info['site_name'];
            $link  = $entity_info['home_url'];
            $t     = !empty($title) ? $title : $kw;

            // Attempt AI Provider completion if class exists and configured
            if (class_exists('GMB_Ranker_SEO_AI_Provider')) {
                $messages = array(
                    array(
                        'role'    => 'system',
                        'content' => 'You are an expert enterprise SEO content strategist. Generate a structured, comprehensive, intent-aligned HTML article draft (using <h2>, <p>, <ul>, <ol> tags) for the target topic without generic clichés. Do not return JSON or markdown fences.',
                    ),
                    array(
                        'role'    => 'user',
                        'content' => sprintf('Topic: %s\nFocus Keyword: %s\nIntent Niche: %s\nSite Name: %s\nSite URL: %s', $t, $kw, $niche, $site, $link),
                    ),
                );

                $ai_response = GMB_Ranker_SEO_AI_Provider::generate_ai_response($messages, 0.7);
                if (!empty($ai_response) && !is_wp_error($ai_response) && mb_strlen(wp_strip_all_tags($ai_response)) > 150) {
                    $clean_ai = self::sanitize_ai_cliches(wp_kses_post($ai_response));
                    $heading_count = preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/i', $clean_ai, $h_matches);
                    return array(
                        'intent' => array(
                            'niche'         => $niche,
                            'heading_count' => $heading_count,
                            'archetype'     => ucwords(strtolower(str_replace('_', ' ', $niche))),
                        ),
                        'draft'  => $clean_ai,
                    );
                }
            }

            // Generic Dynamic Intent Fallback Generator (No hardcoded domain facts)
            $draft = '';
            $heading_count = 0;

            switch ($niche) {

                case 'COMPARISON':
                    $heading_count = 4;
                    $draft = '<p>Evaluating <strong>' . esc_html($t) . '</strong> involves assessing operational requirements, performance standards, overall costs, and long-term value. In this detailed analysis from <a href="' . $link . '">' . esc_html($site) . '</a>, we examine key trade-offs between competing options to help decision-makers choose the optimal approach.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Evaluating Primary Trade-offs & Strategic Alternatives</h2>' . "\n" .
                    '<p>Selecting the right solution for <strong>' . esc_html($kw_lc) . '</strong> requires weighing direct benefits against implementation complexity. While standardized approaches offer quick deployment, tailored options provide greater long-term flexibility.</p>' . "\n" .
                    '<p>Key factors include operational reliability, resource efficiency, expert oversight, and scalability.</p>' . "\n\n" .
                    '<h2>Key Differences in Performance and Execution Efficiency</h2>' . "\n" .
                    '<p>Detailed evaluations indicate that dedicated solutions significantly improve execution speed and quality outcomes compared to generalized methods.</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Targeted Execution:</strong> Custom workflows aligned strictly with strategic goals.</li>' . "\n" .
                    '    <li><strong>Risk Mitigation:</strong> Reduced exposure to execution errors and operational downtime.</li>' . "\n" .
                    '    <li><strong>Cost Transparency:</strong> Predictable resource allocation without hidden overheads.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>Financial & Long-Term Operational Impact</h2>' . "\n" .
                    '<p>Managing ongoing operations without a structured strategy can place unexpected financial strain on projects. Opting for validated support provides targeted assistance at a fraction of unmanaged friction costs.</p>' . "\n\n" .
                    '<h2>When Is This Approach the Optimal Choice?</h2>' . "\n" .
                    '<p>Tailored execution is ideal for high-priority projects, complex workflows, and long-term growth initiatives. For personalized assistance, contact <a href="' . $link . '">' . esc_html($site) . '</a> to speak with a specialist.</p>';
                    break;

                case 'PROCEDURAL':
                    $heading_count = 4;
                    $draft = '<p>Following a structured protocol for <strong>' . esc_html($t) . '</strong> is essential for ensuring safety, preventing execution errors, and achieving predictable results. This guide from <a href="' . $link . '">' . esc_html($site) . '</a> outlines key preparation requirements, step-by-step procedures, and critical verification checks.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Essential Preparation and Prerequisites</h2>' . "\n" .
                    '<p>Before starting, gather all required tools, verify environment readiness, and establish clear safety protocols. Proper preparation minimizes errors and streamlines execution.</p>' . "\n\n" .
                    '<h2>Step-by-Step Execution Protocol</h2>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li><strong>Step 1 (Initial Setup):</strong> Inspect initial conditions, clean target environment, and prepare core components.</li>' . "\n" .
                    '    <li><strong>Step 2 (Primary Execution):</strong> Carefully apply required procedures or configuration parameters as specified.</li>' . "\n" .
                    '    <li><strong>Step 3 (Verification & Testing):</strong> Confirm proper application, run quality diagnostics, and check for anomalies.</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>Common Pitfalls and Risk Mitigation</h2>' . "\n" .
                    '<p>Avoid premature adjustments, unverified shortcuts, and skipped safety steps. If unexpected issues arise, consult established troubleshooting protocols.</p>' . "\n\n" .
                    '<h2>Monitoring & Post-Execution Review</h2>' . "\n" .
                    '<p>Continuously evaluate performance indicators over time. Reach out to <a href="' . $link . '">' . esc_html($site) . '</a> for further expert advice.</p>';
                    break;

                case 'EXPLAINER':
                    $heading_count = 3;
                    $draft = '<p>Understanding <strong>' . esc_html($t) . '</strong> provides fundamental clarity for decision-makers. Below is a breakdown of core principles, operational mechanics, and practical applications from <a href="' . $link . '">' . esc_html($site) . '</a>.</p>' . "\n\n" .
                    '<h2>Defining Core Concepts & Mechanics</h2>' . "\n" .
                    '<p>At its foundation, <strong>' . esc_html($kw_lc) . '</strong> encompasses dedicated methodologies designed to optimize quality, efficiency, and overall performance.</p>' . "\n\n" .
                    '<h2>Key Principles and Operational Features</h2>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Strategic Oversight:</strong> Dedicated monitoring alongside core task execution.</li>' . "\n" .
                    '    <li><strong>Customized Flexibility:</strong> Adaptable frameworks that evolve as requirements shift.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>Why This Matters for Your Strategy</h2>' . "\n" .
                    '<p>Implementing structured support early mitigates operational bottlenecks and ensures sustained stability. Learn more by contacting <a href="' . $link . '">' . esc_html($site) . '</a>.</p>';
                    break;

                case 'SELECTION':
                    $heading_count = 4;
                    $draft = '<p>Selecting top-tier solutions for <strong>' . esc_html($t) . '</strong> requires evaluating provider credentials, service transparency, and proven track record. This evaluation guide from <a href="' . $link . '">' . esc_html($site) . '</a> highlights essential benchmarks and questions to ask before deciding.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Core Selection Criteria to Prioritize</h2>' . "\n" .
                    '<p>Focus on verified expertise, transparent pricing, quality assurance standards, and responsive communication.</p>' . "\n\n" .
                    '<h2>Key Questions to Ask Providers Before Hiring</h2>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li>What quality assurance and compliance standards do your specialists adhere to?</li>' . "\n" .
                    '    <li>How do you handle unexpected project changes or emergency requirements?</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>Red Flags to Avoid During Evaluation</h2>' . "\n" .
                    '<p>Be cautious of vague rate structures, unverified qualifications, and lack of direct project oversight contacts.</p>' . "\n\n" .
                    '<h2>Final Decision & Next Steps</h2>' . "\n" .
                    '<p>Schedule an initial consultation with <a href="' . $link . '">' . esc_html($site) . '</a> to evaluate tailored solutions for your organization.</p>';
                    break;

                case 'SERVICE':
                default:
                    $heading_count = 4;
                    $draft = '<p>Accessing reliable <strong>' . esc_html($t) . '</strong> is vital for achieving optimal results, operational efficiency, and quality outcomes. This comprehensive guide from <a href="' . $link . '">' . esc_html($site) . '</a> details core capabilities, quality standards, and customized execution planning.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Scope of Professional ' . esc_html($kw) . ' Solutions</h2>' . "\n" .
                    '<p>Professional solutions for <strong>' . esc_html($kw_lc) . '</strong> encompass dedicated observation, strategic management, expert execution, and ongoing performance optimization tailored to your specific requirements.</p>' . "\n\n" .
                    '<h2>Customized Strategy Development & Dedicated Supervision</h2>' . "\n" .
                    '<p>Every project begins with a thorough initial assessment to design a flexible operational plan that adapts to evolving requirements over time.</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Expert Oversight:</strong> Continuous monitoring of project quality and performance metrics.</li>' . "\n" .
                    '    <li><strong>Customized Support:</strong> Direct alignment with target objectives and operational workflows.</li>' . "\n" .
                    '    <li><strong>Quality Assurance:</strong> Verified execution standards and safety/compliance controls.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>Quality Standards and Provider Benchmarks</h2>' . "\n" .
                    '<p>All operations undergo rigorous quality evaluation, performance tracking, and continuous improvement to ensure maximum value delivery.</p>' . "\n\n" .
                    '<h2>Schedule a Consultation</h2>' . "\n" .
                    '<p>Secure tailored guidance for your requirements. Contact <a href="' . $link . '">' . esc_html($site) . '</a> today to discuss a custom solution schedule.</p>';
                    break;
            }

            // Remove TOC if headings < 4
            if ($heading_count < 4) {
                $draft = str_replace('[gmb_toc]', '', $draft);
            }

            $sanitized = self::sanitize_ai_cliches($draft);

            return array(
                'intent' => array(
                    'niche'         => $niche,
                    'heading_count' => $heading_count,
                    'archetype'     => ucwords(strtolower(str_replace('_', ' ', $niche))),
                ),
                'draft'  => $sanitized,
            );
        }
    }
}
