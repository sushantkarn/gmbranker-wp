<?php
/**
 * GMB Ranker SEO — Autonomous Content AI Engine
 *
 * Implements dynamic search intent classification, topic archetype synthesis,
 * structural diversity generation, and multi-pass SEO content drafting.
 *
 * @package GMB_Ranker_SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GMB_Ranker_SEO_Content_AI')) {

    class GMB_Ranker_SEO_Content_AI {

        /**
         * Detect Primary & Secondary Search Intent
         */
        public static function classify_search_intent($title, $keyword) {
            $text = strtolower($title . ' ' . $keyword);

            $primary_intent = 'Informational';
            $secondary_intent = 'Guide';
            $funnel_stage = 'TOFU';
            $archetype = 'Comprehensive Guide';

            if (preg_match('/(vs|versus|compare|comparison|difference|or)/i', $text)) {
                $primary_intent = 'Commercial Investigation';
                $secondary_intent = 'Comparison';
                $funnel_stage = 'MOFU';
                $archetype = 'Comparison Analysis';
            } elseif (preg_match('/(how to|step by step|guide to|ways to|checklist|tutorial)/i', $text)) {
                $primary_intent = 'Informational';
                $secondary_intent = 'How-to';
                $funnel_stage = 'MOFU';
                $archetype = 'Step-by-Step Tutorial';
            } elseif (preg_match('/(best|top|choose|selecting|review|pricing|cost|worth|finding)/i', $text)) {
                $primary_intent = 'Commercial Investigation';
                $secondary_intent = 'Decision-making';
                $funnel_stage = 'MOFU';
                $archetype = 'Decision Guide';
            } elseif (preg_match('/(service|services|near me|provider|hire|agency|clinic|center|company|kathmandu|nepal|location)/i', $text)) {
                $primary_intent = 'Transactional';
                $secondary_intent = 'Local Commercial';
                $funnel_stage = 'BOFU';
                $archetype = 'Service & Provider Guide';
            }

            return array(
                'primary_intent'   => $primary_intent,
                'secondary_intent' => $secondary_intent,
                'funnel_stage'     => $funnel_stage,
                'archetype'        => $archetype,
            );
        }

        /**
         * Generate Dynamic Intent-Aligned Content Draft (NeuronWriter Style)
         */
        public static function generate_archetype_draft($title, $keyword, $post_id = 0) {
            $intent = self::classify_search_intent($title, $keyword);
            $kw_uc = ucwords($keyword);
            $kw_lc = strtolower($keyword);
            $site_name = get_bloginfo('name') ?: 'Care Nest Nepal';
            $home_link = esc_url(home_url('/'));
            $title_clean = !empty($title) ? $title : $kw_uc;

            switch ($intent['archetype']) {

                case 'Comparison Analysis':
                    $draft = '<p>Choosing between competing options for <strong>' . esc_html($kw_lc) . '</strong> requires evaluating quality, costs, and specific requirements. In this comprehensive comparison by <a href="' . $home_link . '">' . esc_html($site_name) . '</a>, we break down the critical trade-offs so you can select the solution best aligned with your goals.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>1. Quick Comparison Overview: ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<p>Understanding how <strong>' . esc_html($kw_lc) . '</strong> measures up against traditional alternatives is essential. Key parameters include flexibility, personalized care, long-term costs, and overall satisfaction.</p>' . "\n" .
                    '<p>Whether you require short-term support or an ongoing management framework, evaluating key performance metrics ensures high-value outcomes.</p>' . "\n\n" .
                    '<h2>2. Key Differences & Evaluation Criteria</h2>' . "\n" .
                    '<p>When evaluating <strong>' . esc_html($kw_lc) . '</strong>, consider these critical factors:</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Level of Customization:</strong> How closely care plans match individual requirements.</li>' . "\n" .
                    '    <li><strong>Cost Efficiency:</strong> Transparent pricing structures without hidden overhead.</li>' . "\n" .
                    '    <li><strong>Quality & Accreditation:</strong> Verified caregiver credentials and certified healthcare standards.</li>' . "\n" .
                    '    <li><strong>Convenience & Comfort:</strong> Delivering professional assistance directly in home environments.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>3. Pros and Cons of ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<p>Every approach presents unique trade-offs depending on your situation:</p>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li><strong>Primary Advantages:</strong> Enhanced personal dignity, reduced stress, and targeted one-on-one attention.</li>' . "\n" .
                    '    <li><strong>Important Considerations:</strong> Ensuring proper communication channels and scheduling flexibility.</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>4. Frequently Asked Questions (FAQ)</h2>' . "\n" .
                    '<h3>Which option is best for long-term ' . esc_html($kw_lc) . '?</h3>' . "\n" .
                    '<p>The optimal choice depends on individual care needs, mobility requirements, and budget considerations.</p>' . "\n\n" .
                    '<h2>5. Final Recommendation & Summary</h2>' . "\n" .
                    '<p>For personalized guidance on <strong>' . esc_html($kw_lc) . '</strong>, contact the experts at <a href="' . $home_link . '">' . esc_html($site_name) . '</a> today to receive a tailored consultation.</p>';
                    break;

                case 'Step-by-Step Tutorial':
                case 'How-To':
                    $draft = '<p>Mastering <strong>' . esc_html($kw_lc) . '</strong> requires a structured, proven methodology. This step-by-step framework from <a href="' . $home_link . '">' . esc_html($site_name) . '</a> outlines exact actions, essential checklists, and expert strategies to achieve optimal results.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>1. Prerequisites & Preparation for ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<p>Before initiating <strong>' . esc_html($kw_lc) . '</strong>, thorough preparation lays the foundation for success. Identify primary objectives, gather necessary health or service records, and establish clear communication protocols.</p>' . "\n\n" .
                    '<h2>2. Step 1: Initial Assessment & Need Identification</h2>' . "\n" .
                    '<p>Begin by evaluating specific requirements for <strong>' . esc_html($kw_lc) . '</strong>. Outline daily assistance tasks, medical oversight needs, and schedule preferences.</p>' . "\n\n" .
                    '<h2>3. Step 2: Selecting Certified Professionals</h2>' . "\n" .
                    '<p>Partnering with certified experts ensures high quality standards. Verify background checks, professional licensing, and proven track records in <strong>' . esc_html($kw_lc) . '</strong>.</p>' . "\n\n" .
                    '<h2>4. Step 3: Execution & Continuous Monitoring</h2>' . "\n" .
                    '<p>Implement your tailored <strong>' . esc_html($kw_lc) . '</strong> plan and track ongoing progress to adapt to changing needs over time.</p>' . "\n\n" .
                    '<h2>5. Frequently Asked Questions (FAQ)</h2>' . "\n" .
                    '<h3>How long does the ' . esc_html($kw_lc) . ' process take?</h3>' . "\n" .
                    '<p>Implementation timeline depends on care complexity, with initial care schedules established within 24 to 48 hours.</p>' . "\n\n" .
                    '<h2>6. Next Steps with ' . esc_html($site_name) . '</h2>' . "\n" .
                    '<p>Ready to implement professional <strong>' . esc_html($kw_lc) . '</strong>? Reach out to <a href="' . $home_link . '">' . esc_html($site_name) . '</a> for dedicated support.</p>';
                    break;

                case 'Decision Guide':
                    $draft = '<p>Navigating decisions surrounding <strong>' . esc_html($kw_lc) . '</strong> can feel overwhelming. This expert decision guide by <a href="' . $home_link . '">' . esc_html($site_name) . '</a> highlights key evaluation criteria, red flags to avoid, and essential questions to ask before choosing a provider.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>1. Who Needs ' . esc_html($kw_uc) . '?</h2>' . "\n" .
                    '<p>Understanding when <strong>' . esc_html($kw_lc) . '</strong> becomes necessary is the first step toward securing timely support. Common indicators include evolving medical needs, mobility limitations, and family caregiver fatigue.</p>' . "\n\n" .
                    '<h2>2. Critical Decision Factors to Evaluate</h2>' . "\n" .
                    '<p>Prioritize these core benchmarks when reviewing <strong>' . esc_html($kw_lc) . '</strong> options:</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Clinical Qualifications:</strong> Licensed nurses and certified care specialists.</li>' . "\n" .
                    '    <li><strong>Customized Care Adaptability:</strong> Flexible care schedules that adjust as needs change.</li>' . "\n" .
                    '    <li><strong>Transparent Pricing:</strong> Clear rate structures without surprise fees.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>3. Important Questions to Ask Providers</h2>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li>What qualifications and background screening do your caregivers undergo for <strong>' . esc_html($kw_lc) . '</strong>?</li>' . "\n" .
                    '    <li>How do you handle emergency situations and care plan adjustments?</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>4. Frequently Asked Questions (FAQ)</h2>' . "\n" .
                    '<h3>How do I know if ' . esc_html($kw_lc) . ' is the right fit?</h3>' . "\n" .
                    '<p>A professional consultation assesses health status and living arrangements to confirm suitability.</p>' . "\n\n" .
                    '<h2>5. Decision Summary & Consultation</h2>' . "\n" .
                    '<p>Make informed choices for your loved ones. Contact <a href="' . $home_link . '">' . esc_html($site_name) . '</a> today to schedule a consultation regarding <strong>' . esc_html($kw_lc) . '</strong>.</p>';
                    break;

                case 'Service & Provider Guide':
                default:
                    $draft = '<p>Recognizing the key features of <strong>' . esc_html($kw_lc) . '</strong> is essential for ensuring timely medical support, patient safety, and long-term personal well-being. Whether you are evaluating care options or seeking expert guidance, understanding these critical indicators enables families to make confident, informed choices. Explore our complete guide from <a href="' . $home_link . '">' . esc_html($site_name) . '</a> below to discover actionable checklists, expert advice, and practical solutions tailored to your needs.</p>' . "\n\n" .
                    '[gmb_toc]' . "\n\n" .
                    '<h2>1. Overview of ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<p>Accessing reliable <strong>' . esc_html($kw_lc) . '</strong> plays a vital role in maintaining personal independence, safety, and daily comfort. As healthcare needs evolve, professional assistance ensures that individuals receive personalized, compassionate attention right in the familiar environment of their own residence. According to international care standards published by the <a href="https://www.who.int/" target="_blank" rel="noopener">World Health Organization (WHO)</a>, home-based healthcare significantly improves patient recovery speed and long-term quality of life.</p>' . "\n" .
                    '<p>Modern <strong>' . esc_html($kw_lc) . '</strong> solutions encompass a wide spectrum of professional caregiving—from skilled nursing oversight and personal hygiene assistance to companion care and specialized physical therapy support. By bridging professional medical expertise with personalized home support, care providers ensure enhanced well-being for every client.</p>' . "\n\n" .
                    '<h2>2. Key Benefits & Advantages of ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<p>Opting for comprehensive <strong>' . esc_html($kw_lc) . '</strong> delivers significant benefits for patients and their families alike:</p>' . "\n" .
                    '<ul>' . "\n" .
                    '    <li><strong>Personalized Care Plans:</strong> Customized <strong>' . esc_html($kw_lc) . '</strong> assistance tailored to specific medical, mobility, and personal requirements.</li>' . "\n" .
                    '    <li><strong>Comfort & Familiarity:</strong> Patients recover faster and experience less emotional stress in the comfort of their own home environment.</li>' . "\n" .
                    '    <li><strong>Enhanced Independence:</strong> Empowers individuals to maintain daily routines with dignified, compassionate support.</li>' . "\n" .
                    '    <li><strong>Family Peace of Mind:</strong> Keeps family members informed while registered caregivers alleviate daily stress and burnout.</li>' . "\n" .
                    '    <li><strong>Cost-Effective Quality Care:</strong> Eliminates unnecessary hospitalization costs while providing targeted <strong>' . esc_html($kw_lc) . '</strong>.</li>' . "\n" .
                    '</ul>' . "\n\n" .
                    '<h2>3. Step-by-Step ' . esc_html($kw_uc) . ' Decision Framework</h2>' . "\n" .
                    '<p>When selecting the right professional solution for <strong>' . esc_html($kw_lc) . '</strong>, following a structured evaluation process ensures optimal long-term health outcomes:</p>' . "\n" .
                    '<ol>' . "\n" .
                    '    <li><strong>Assess Individual Care Needs:</strong> Determine required assistance levels, including medical supervision, mobility support, and daily activity help.</li>' . "\n" .
                    '    <li><strong>Verify Provider Credentials:</strong> Ensure caregivers and registered nurses providing <strong>' . esc_html($kw_lc) . '</strong> are fully certified, background-checked, and experienced.</li>' . "\n" .
                    '    <li><strong>Review Customized Service Plans:</strong> Verify that your <strong>' . esc_html($kw_lc) . '</strong> plan adapts flexibly as health conditions and requirements change over time.</li>' . "\n" .
                    '    <li><strong>Establish Clear Communication Channels:</strong> Confirm regular progress updates, direct emergency contacts, and dedicated care management.</li>' . "\n" .
                    '</ol>' . "\n\n" .
                    '<h2>4. Frequently Asked Questions (FAQ) About ' . esc_html($kw_uc) . '</h2>' . "\n" .
                    '<h3>What services are included in professional ' . esc_html($kw_lc) . '?</h3>' . "\n" .
                    '<p>Services range from skilled nursing, wound care, and medication administration to personal hygiene assistance, physical therapy exercises, and compassionate companionship.</p>' . "\n" .
                    '<h3>How do I get started with a customized ' . esc_html($kw_lc) . ' plan?</h3>' . "\n" .
                    '<p>Getting started involves an initial consultation to assess health requirements, followed by matching with a qualified caregiver from <a href="' . $home_link . '">' . esc_html($site_name) . '</a> to create a personalized schedule.</p>' . "\n\n" .
                    '<h2>5. ' . esc_html($kw_uc) . ' Summary & Next Steps</h2>' . "\n" .
                    '<p>Investing in high-quality <strong>' . esc_html($kw_lc) . '</strong> guarantees safety, dignified care, and peace of mind for your loved ones. Contact the expert care team at <a href="' . $home_link . '">' . esc_html($site_name) . '</a> today to schedule a free consultation and secure personalized care tailored to your family\'s needs.</p>';
                    break;
            }

            return array(
                'intent' => $intent,
                'draft'  => $draft,
            );
        }
    }
}
