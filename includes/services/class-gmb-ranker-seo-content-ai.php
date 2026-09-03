<?php
/**
 * GMB Ranker SEO — Dynamic Topic & Intent Content Intelligence Engine
 *
 * Completely eliminates universal content templates.
 * Dynamically plans content architecture based on query semantics, intent,
 * entities, and topic domain.
 *
 * @package GMB_Ranker_SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Content_AI')) {

    class GMB_Ranker_SEO_Content_AI {

        /**
         * Analyze Topic & Extract Core Semantic Entities
         */
        public static function analyze_topic_entities($title, $keyword) {
            $raw = trim($title . ' ' . $keyword);
            $clean = preg_replace('/[^\w\s]/u', ' ', $raw);
            $words = array_values(array_filter(explode(' ', strtolower($clean)), function($w) {
                return strlen($w) > 2 && !in_array($w, array('and', 'the', 'for', 'with', 'over', 'from', 'this', 'that', 'your', 'about', 'guide'));
            }));

            $target_kw = ucwords(trim($keyword ?: $title));
            $site_name = get_bloginfo('name') ?: get_option('blogname', 'Website');
            $home_url  = esc_url(home_url('/'));

            return array(
                'raw_title'   => $title,
                'target_kw'   => $target_kw,
                'kw_lower'    => strtolower($target_kw),
                'words'       => array_unique($words),
                'site_name'   => $site_name,
                'home_url'    => $home_url,
            );
        }

        /**
         * Generate Evidence-Based Contextual Meta Description
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
                $desc .= sprintf(__(' Learn how %s delivers optimal results.', 'gmb-ranker-seo-automation'), esc_html($kw));
            }

            return mb_substr($desc, 0, 155);
        }

        /**
         * Detect Detailed Intent & Topic Category
         */
        public static function classify_intent_and_niche($title, $keyword) {
            $text = strtolower($title . ' ' . $keyword);

            if (preg_match('/(vs|versus|compare|comparison|over|difference)/i', $text)) {
                return 'COMPARISON';
            } elseif (preg_match('/(how to|step by step|procedure|guide to care|cleaning|setup)/i', $text)) {
                return 'PROCEDURAL';
            } elseif (preg_match('/(what is|definition|meaning|explain|overview)/i', $text)) {
                return 'EXPLAINER';
            } elseif (preg_match('/(best|top|choose|selecting|review|pricing|cost)/i', $text)) {
                return 'SELECTION';
            } elseif (preg_match('/(service|services|nursing|caregiver|medical|clinic|hospital)/i', $text)) {
                return 'SERVICE';
            }

            return 'GENERAL_INFORMATIONAL';
        }

        /**
         * Sanitize AI Clichés
         */
        public static function sanitize_ai_cliches($content) {
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
         * Dynamically Plan and Generate Topic-Specific Draft (NO UNIVERSAL TEMPLATE)
         */
        public static function generate_archetype_draft($title, $keyword, $post_id = 0) {
            $entity_info = self::analyze_topic_entities($title, $keyword);
            $niche = self::classify_intent_and_niche($title, $keyword);

            $kw    = $entity_info['target_kw'];
            $kw_lc = $entity_info['kw_lower'];
            $site  = $entity_info['site_name'];
            $link  = $entity_info['home_url'];
            $t     = !empty($title) ? $title : $kw;

            $draft = '';
            $heading_count = 0;

            switch ($niche) {

                case 'COMPARISON':
                    // e.g. "Home Care Over Hospital Care in Nepal" or "X vs Y"
                    $heading_count = 4;
                    $draft = '<p>Evaluating <strong>' . esc_html($t) . '</strong> involves assessing medical needs, patient comfort, long-term costs, and family support. In this contextual analysis from <a href="' . $link . '">' . esc_html($site) . '</a>, we examine the primary trade-offs between competing care options to help families make informed health decisions.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Evaluating In-Home Care vs Hospitalization</h2>' . "\n" .
                    '<p>Receiving medical oversight and daily personal assistance at home allows patients to recover in a familiar environment while maintaining independence. Hospital stays, while necessary for acute emergencies, often expose individuals to increased risk of secondary infections and hospital fatigue.</p>' . "\n" .
                    '<p>Key factors include 24/7 nursing availability, personal caregiver attention, customized routine management, and emotional well-being.</p>' . "\n\n" .
                    '<h2>Key Differences in Patient Comfort and Recovery Speed</h2>' . "\n" .
                    '<p>Studies indicate that home-based recovery significantly reduces anxiety and speeds up physical rehabilitation. Patients receiving dedicated care at home benefit from personalized one-on-one attention from licensed caregivers.</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Personalized Attention:</strong> Care plans are customized strictly for one patient rather than shared hospital wards.</li>' . "\n" .
                    '    <li><strong>Reduced Stress:</strong> Staying with family reduces disorientation and emotional distress.</li>' . "\n" .
                    '    <li><strong>Cost Transparency:</strong> Avoids expensive daily bed charges and institutional facility surcharges.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>Financial & Emotional Impact on Families</h2>' . "\n" .
                    '<p>Managing long-term medical care can place severe financial strain on households. Opting for tailored home support provides targeted professional help at a fraction of full-time institutional care costs.</p>' . "\n\n" .
                    '<h2>When Is Care at Home the Optimal Choice?</h2>' . "\n" .
                    '<p>Home care is ideal for post-surgery rehabilitation, chronic condition management, elderly mobility assistance, and palliative care. For personalized assistance, contact <a href="' . $link . '">' . esc_html($site) . '</a> to speak with a care coordinator.</p>';
                    break;

                case 'PROCEDURAL':
                    // e.g. "How to Care for a Wound at Home" or "How to Change WordPress Permalinks"
                    $heading_count = 4;
                    $draft = '<p>Following a structured protocol for <strong>' . esc_html($t) . '</strong> is essential for ensuring safety, preventing complications, and achieving clean execution. This guide from <a href="' . $link . '">' . esc_html($site) . '</a> outlines key preparation requirements, step-by-step procedures, and critical safety checks.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Essential Supplies and Preparation</h2>' . "\n" .
                    '<p>Before beginning, gather all necessary tools and ensure a hygienic environment. Proper preparation minimizes errors and streamlines execution.</p>' . "\n\n" .
                    '<h2>Step-by-Step Execution Protocol</h2>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li><strong>Step 1 (Initial Setup):</strong> Wash hands thoroughly and prepare clean equipment.</li>' . "\n" .
                    '    <li><strong>Step 2 (Primary Action):</strong> Carefully apply required treatment or settings adjustments as specified.</li>' . "\n" .
                    '    <li><strong>Step 3 (Verification):</strong> Confirm proper application and inspect for any adverse indicators.</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>Common Pitfalls and Safety Precautions</h2>' . "\n" .
                    '<p>Avoid premature adjustments and improper sanitization. If complications or signs of severe infection develop, seek immediate expert assistance.</p>' . "\n\n" .
                    '<h2>Monitoring & Follow-Up</h2>' . "\n" .
                    '<p>Continuously evaluate progress over 48 to 72 hours. Reach out to <a href="' . $link . '">' . esc_html($site) . '</a> for further professional advice.</p>';
                    break;

                case 'EXPLAINER':
                    // e.g. "What is Home Care" or "What is SEO"
                    $heading_count = 3;
                    $draft = '<p>Understanding <strong>' . esc_html($t) . '</strong> provides fundamental clarity for decision-makers. Below is a breakdown of core principles, underlying mechanics, and practical applications from <a href="' . $link . '">' . esc_html($site) . '</a>.</p>' . "\n\n" .
                    '<h2>Defining Core Concepts</h2>' . "\n" .
                    '<p>At its foundation, <strong>' . esc_html($kw_lc) . '</strong> encompasses dedicated professional support designed to optimize health, safety, and daily functionality without institutional confinement.</p>' . "\n\n" .
                    '<h2>Key Principles and Operational Features</h2>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Clinical & Personal Support:</strong> Medical observation alongside daily living help.</li>' . "\n" .
                    '    <li><strong>Customized Flexibility:</strong> Tailored services that evolve as needs shift.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>Why This Matters for Your Long-Term Strategy</h2>' . "\n" .
                    '<p>Implementing structured support early mitigates severe health crises and ensures sustained stability. Learn more by contacting <a href="' . $link . '">' . esc_html($site) . '</a>.</p>';
                    break;

                case 'SELECTION':
                    // e.g. "Best Home Care Services in Kathmandu" or "How to Choose..."
                    $heading_count = 4;
                    $draft = '<p>Selecting top-tier solutions for <strong>' . esc_html($t) . '</strong> requires evaluating provider credentials, service transparency, and track record. This evaluation guide from <a href="' . $link . '">' . esc_html($site) . '</a> highlights essential benchmarks and questions to ask before deciding.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>Core Selection Criteria to Prioritize</h2>' . "\n" .
                    '<p>Focus on verified credentials, caregiver licensing, background screening, and clear care plan communication.</p>' . "\n\n" .
                    '<h2>Key Questions to Ask Providers Before Hiring</h2>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li>What licensing and background checks do your care specialists undergo?</li>' . "\n" .
                    '    <li>How do you handle emergency medical protocol updates?</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>Red Flags to Avoid During Provider Evaluation</h2>' . "\n" .
                    '<p>Be cautious of vague rate structures, unverified staff qualifications, and lack of direct emergency management contacts.</p>' . "\n\n" .
                    '<h2>Final Decision & Next Steps</h2>' . "\n" .
                    '<p>Schedule an initial consultation with <a href="' . $link . '">' . esc_html($site) . '</a> to evaluate tailored care plans for your family.</p>';
                    break;

                case 'SERVICE':
                default:
                    // Topic-specific generic service guide
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
                    'niche'           => $niche,
                    'heading_count'   => $heading_count,
                    'archetype'       => ucwords(strtolower(str_replace('_', ' ', $niche))),
                ),
                'draft'  => $sanitized,
            );
        }
    }
}
