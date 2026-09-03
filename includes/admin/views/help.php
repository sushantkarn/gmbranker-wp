<?php
/**
 * Admin Help & Support View
 *
 * Enterprise-grade presentation layer for plugin documentation,
 * support channels, configuration wizard links, and technical FAQs.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '');

// Fetch canonical resource metadata
$support_url    = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_support_url() : 'https://gmbranker.org/support';
$docs_url       = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_documentation_url() : 'https://gmbranker.org/docs';
$community_url  = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_community_url() : 'https://gmbranker.org/community';
$wizard_url     = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_wizard_url() : admin_url('admin.php?page=gmb-ranker-wizard');
$lic_status     = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_licensing_status() : array('label' => __('Standard Support', 'gmb-ranker-seo-automation'), 'class' => 'gmb-status-pill--info', 'active' => false, 'details' => __('Standard Support Available.', 'gmb-ranker-seo-automation'));
$faq_entries    = class_exists('GMB_Ranker_SEO_Help_Registry') ? GMB_Ranker_SEO_Help_Registry::get_faq_entries() : array();
?>
<?php if ($current_page === 'gmb-ranker-help') : ?>
<div class="rm-tab-content active" id="rm-tab-help">
    <div class="gmb-help-container">
        <div class="gmb-settings-panel-header gmb-text-left">
            <h2 class="gmb-heading-2"><?php esc_html_e('Help & Support Resources', 'gmb-ranker-seo-automation'); ?></h2>
            <p class="gmb-form-help">
                <?php esc_html_e('Access official support channels, documentation guides, community discussions, or launch the interactive setup wizard.', 'gmb-ranker-seo-automation'); ?> 
                <a href="<?php echo esc_url($docs_url); ?>" target="_blank" rel="noopener noreferrer" class="gmb-help-link">
                    <?php esc_html_e('Learn more', 'gmb-ranker-seo-automation'); ?>
                </a>.
            </p>
        </div>
        
        <div class="gmb-help-grid">
            <!-- Card 1: Direct Support -->
            <div class="gmb-help-card">
                <div>
                    <div class="gmb-help-card-header">
                        <div class="gmb-help-icon-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </div>
                        <div>
                            <h3 class="gmb-help-card-title"><?php esc_html_e('Direct Support', 'gmb-ranker-seo-automation'); ?></h3>
                            <span class="gmb-status-pill <?php echo esc_attr($lic_status['class']); ?> gmb-status-pill-sub">
                                <span class="gmb-status-dot"></span>
                                <?php echo esc_html($lic_status['label']); ?>
                            </span>
                        </div>
                    </div>
                    <p class="gmb-help-card-body"><?php echo esc_html($lic_status['details']); ?></p>
                </div>
                <div class="gmb-help-card-footer">
                    <a href="<?php echo esc_url($support_url); ?>" target="_blank" rel="noopener noreferrer" class="gmb-btn gmb-btn-primary gmb-btn--primary gmb-btn-help-link">
                        <?php esc_html_e('Open Support Ticket', 'gmb-ranker-seo-automation'); ?> &nearr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Documentation -->
            <div class="gmb-help-card">
                <div>
                    <div class="gmb-help-card-header">
                        <div class="gmb-help-icon-badge gmb-help-icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <div>
                            <h3 class="gmb-help-card-title"><?php esc_html_e('Documentation & Tutorials', 'gmb-ranker-seo-automation'); ?></h3>
                            <span class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Guides & Walkthroughs', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    </div>
                    <p class="gmb-help-card-body"><?php esc_html_e('Learn how to configure Local Business Schema, Instant Indexing protocols, automated AI keywords, and XML sitemaps.', 'gmb-ranker-seo-automation'); ?></p>
                </div>
                <div class="gmb-help-card-footer">
                    <a href="<?php echo esc_url($docs_url); ?>" target="_blank" rel="noopener noreferrer" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                        <?php esc_html_e('Browse Knowledge Base', 'gmb-ranker-seo-automation'); ?> &nearr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Interactive Setup Wizard -->
            <div class="gmb-help-card">
                <div>
                    <div class="gmb-help-card-header">
                        <div class="gmb-help-icon-badge gmb-help-icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        </div>
                        <div>
                            <h3 class="gmb-help-card-title"><?php esc_html_e('Configuration Wizard', 'gmb-ranker-seo-automation'); ?></h3>
                            <span class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Onboarding & Setup', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    </div>
                    <p class="gmb-help-card-body"><?php esc_html_e('Need to reconfigure website business parameters, indexing options, or social profiles? Rerun the step-by-step wizard at any time.', 'gmb-ranker-seo-automation'); ?></p>
                </div>
                <div class="gmb-help-card-footer">
                    <a href="<?php echo esc_url($wizard_url); ?>" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                        <?php esc_html_e('Start Setup Wizard', 'gmb-ranker-seo-automation'); ?> &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 4: Community & Feedback -->
            <div class="gmb-help-card">
                <div>
                    <div class="gmb-help-card-header">
                        <div class="gmb-help-icon-badge gmb-help-icon-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        </div>
                        <div>
                            <h3 class="gmb-help-card-title"><?php esc_html_e('Community & Feedback', 'gmb-ranker-seo-automation'); ?></h3>
                            <span class="gmb-text-muted gmb-text-xs"><?php esc_html_e('Roadmap & Discussions', 'gmb-ranker-seo-automation'); ?></span>
                        </div>
                    </div>
                    <p class="gmb-help-card-body"><?php esc_html_e('Join other SEO webmasters, submit product feedback, and participate in feature discussions.', 'gmb-ranker-seo-automation'); ?></p>
                </div>
                <div class="gmb-help-card-footer">
                    <a href="<?php echo esc_url($community_url); ?>" target="_blank" rel="noopener noreferrer" class="gmb-btn gmb-btn-secondary gmb-btn-help-link">
                        <?php esc_html_e('Join Community', 'gmb-ranker-seo-automation'); ?> &nearr;
                    </a>
                </div>
            </div>
        </div>

        <!-- FAQ Reference Section (Toggle Based) -->
        <div class="gmb-faq-section">
            <div class="gmb-faq-header-row">
                <h3 class="gmb-faq-header-title"><?php esc_html_e('Frequently Asked Questions', 'gmb-ranker-seo-automation'); ?></h3>
                <span class="gmb-text-muted gmb-faq-header-tip"><?php esc_html_e('Click questions to expand', 'gmb-ranker-seo-automation'); ?></span>
            </div>
            <div class="gmb-faq-list">
                <?php if (!empty($faq_entries)) : ?>
                    <?php foreach ($faq_entries as $faq) : ?>
                        <details class="gmb-faq-accordion" <?php echo !empty($faq['open']) ? 'open' : ''; ?>>
                            <summary class="gmb-faq-summary">
                                <span><?php echo esc_html($faq['question']); ?></span>
                                <svg class="gmb-faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </summary>
                            <div class="gmb-faq-content">
                                <?php echo esc_html($faq['answer']); ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
