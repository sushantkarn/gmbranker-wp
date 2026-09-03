<?php
/**
 * Admin Layout Header
 *
 * Enterprise-grade, accessible admin layout header.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

$logo_ver = defined('GMB_RANKER_SEO_VERSION') ? GMB_RANKER_SEO_VERSION : '2.1.0';
$logo_url = defined('GMB_RANKER_SEO_URL') ? GMB_RANKER_SEO_URL . 'assets/gmbranker.svg' : plugins_url('assets/gmbranker.svg', dirname(dirname(dirname(dirname(__FILE__)))));
$auto_mode = get_option('gmb_ranker_automation_mode', 'advanced');
$mode_label = ($auto_mode === 'easy') ? esc_html__('Easy Mode', 'gmb-ranker-seo-automation') : esc_html__('Advanced Mode', 'gmb-ranker-seo-automation');
?>
<div class="rm-wrap gmb-admin-wrap">
    <header class="rm-header gmb-header" role="banner">
        <div class="rm-header-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-automation')); ?>" class="rm-logo-link" aria-label="<?php esc_attr_e('GMB Ranker SEO Dashboard', 'gmb-ranker-seo-automation'); ?>">
                <img src="<?php echo esc_url($logo_url . '?v=' . $logo_ver); ?>" alt="<?php esc_attr_e('GMB Ranker', 'gmb-ranker-seo-automation'); ?>" class="rm-full-logo-img" />
            </a>
            <span class="rm-header-badge">
                <?php echo esc_html($mode_label); ?>
            </span>
        </div>
        <div class="rm-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-wizard')); ?>" class="rm-header-btn-wizard">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4V2m0 20v-2M8 9l-1.5-1.5M20.5 4.5L19 6M4 15H2m20 0h-2M9 20l-1.5 1.5M20.5 19.5L19 18M14.5 9.5l-8 8a2.121 2.121 0 0 0 3 3l8-8a2.121 2.121 0 0 0-3-3z"/></svg>
                <span><?php esc_html_e('Setup Wizard', 'gmb-ranker-seo-automation'); ?></span>
            </a>
            <div class="rm-header-search-container">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="gmb-search-options" placeholder="<?php esc_attr_e('Search Options', 'gmb-ranker-seo-automation'); ?>" aria-label="<?php esc_attr_e('Search Options', 'gmb-ranker-seo-automation'); ?>" oninput="gmbFilterSettings(this.value)" autocomplete="off" />
                <span class="clear-search" id="gmb-clear-search" onclick="gmbClearSearch()" title="<?php esc_attr_e('Clear search', 'gmb-ranker-seo-automation'); ?>" role="button" tabindex="0">&times;</span>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-ranker-help')); ?>" class="rm-header-help-link" title="<?php esc_attr_e('Help & Support', 'gmb-ranker-seo-automation'); ?>" aria-label="<?php esc_attr_e('Help & Support', 'gmb-ranker-seo-automation'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </a>
        </div>
    </header>
