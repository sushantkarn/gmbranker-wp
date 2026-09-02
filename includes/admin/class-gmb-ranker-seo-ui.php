<?php
/**
 * GMB Ranker SEO — Centralized UI Component Engine
 * Provides reusable, semantic, and accessible UI component renderers.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_UI {

    /**
     * Render a standardized Page Header (Title, Subtitle, Status Badge, Quick Actions)
     *
     * @param string $title Page title
     * @param string $subtitle Explanatory subtitle
     * @param string $badge Optional status badge HTML or text
     * @param array  $actions Action buttons array [ 'text' => '...', 'url' => '...', 'variant' => '...', 'icon' => '...' ]
     * @param bool   $echo Whether to echo or return
     * @return string
     */
    public static function render_page_header($title, $subtitle = '', $badge = '', $actions = array(), $echo = true) {
        ob_start();
        ?>
        <div class="gmb-page-header">
            <div class="gmb-page-header__left">
                <div class="gmb-page-header__title-row">
                    <h1 class="gmb-page-header__title"><?php echo esc_html($title); ?></h1>
                    <?php if (!empty($badge)) : ?>
                        <div class="gmb-page-header__badge">
                            <?php echo is_string($badge) && strpos($badge, '<') !== false ? wp_kses_post($badge) : self::render_badge($badge, 'primary', false, false); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($subtitle)) : ?>
                    <p class="gmb-page-header__subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)) : ?>
                <div class="gmb-page-header__actions">
                    <?php foreach ($actions as $action) : ?>
                        <?php if (isset($action['html'])) : ?>
                            <?php echo wp_kses_post($action['html']); ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url($action['url'] ?? '#'); ?>" 
                               class="gmb-btn gmb-btn--<?php echo esc_attr($action['variant'] ?? 'secondary'); ?> <?php echo esc_attr($action['class'] ?? ''); ?>"
                               <?php echo isset($action['id']) ? 'id="' . esc_attr($action['id']) . '"' : ''; ?>
                               <?php echo isset($action['target']) ? 'target="' . esc_attr($action['target']) . '"' : ''; ?>>
                                <?php if (!empty($action['icon'])) echo wp_kses_post($action['icon']); ?>
                                <span><?php echo esc_html($action['text'] ?? ''); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        return $output;
    }

    /**
     * Render Section Header
     */
    public static function render_section_header($title, $description = '', $actions = array(), $echo = true) {
        ob_start();
        ?>
        <div class="gmb-section-header">
            <div class="gmb-section-header__content">
                <h3 class="gmb-section-header__title"><?php echo esc_html($title); ?></h3>
                <?php if (!empty($description)) : ?>
                    <p class="gmb-section-header__description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)) : ?>
                <div class="gmb-section-header__actions">
                    <?php foreach ($actions as $action) : ?>
                        <?php echo is_string($action) ? wp_kses_post($action) : ''; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render a standard 12px-radius Card Container
     */
    public static function render_card($title, $content, $footer = '', $args = array(), $echo = true) {
        $class = 'gmb-card ' . ($args['class'] ?? '');
        $id = isset($args['id']) ? 'id="' . esc_attr($args['id']) . '"' : '';
        $icon = $args['icon'] ?? '';
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr(trim($class)); ?>" <?php echo $id; ?>>
            <?php if (!empty($title)) : ?>
                <div class="gmb-card__header">
                    <div class="gmb-card__title-wrap">
                        <?php if (!empty($icon)) : ?>
                            <span class="gmb-card__icon"><?php echo wp_kses_post($icon); ?></span>
                        <?php endif; ?>
                        <h4 class="gmb-card__title"><?php echo esc_html($title); ?></h4>
                    </div>
                    <?php if (!empty($args['header_action'])) : ?>
                        <div class="gmb-card__header-action">
                            <?php echo wp_kses_post($args['header_action']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="gmb-card__body <?php echo esc_attr($args['body_class'] ?? ''); ?>">
                <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <?php if (!empty($footer)) : ?>
                <div class="gmb-card__footer">
                    <?php echo wp_kses_post($footer); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Analytics Stat Card
     */
    public static function render_stat_card($label, $value, $trend = '', $icon = '', $args = array(), $echo = true) {
        $trend_type = $args['trend_type'] ?? 'positive'; // positive, negative, neutral
        ob_start();
        ?>
        <div class="gmb-stat-card <?php echo esc_attr($args['class'] ?? ''); ?>">
            <div class="gmb-stat-card__top">
                <span class="gmb-stat-card__label"><?php echo esc_html($label); ?></span>
                <?php if (!empty($icon)) : ?>
                    <span class="gmb-stat-card__icon"><?php echo wp_kses_post($icon); ?></span>
                <?php endif; ?>
            </div>
            <div class="gmb-stat-card__value"><?php echo esc_html($value); ?></div>
            <?php if (!empty($trend)) : ?>
                <div class="gmb-stat-card__trend gmb-stat-card__trend--<?php echo esc_attr($trend_type); ?>">
                    <?php if ($trend_type === 'positive') : ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <?php elseif ($trend_type === 'negative') : ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <?php endif; ?>
                    <span><?php echo esc_html($trend); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Standard Button
     */
    public static function render_button($text, $variant = 'primary', $size = 'md', $icon = '', $attributes = array(), $echo = true) {
        $tag = $attributes['href'] ?? '' ? 'a' : 'button';
        $attr_str = '';
        foreach ($attributes as $k => $v) {
            if ($k !== 'class') {
                $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
            }
        }
        $class = 'gmb-btn gmb-btn--' . esc_attr($variant) . ' gmb-btn--' . esc_attr($size) . ' ' . ($attributes['class'] ?? '');

        ob_start();
        ?>
        <<?php echo esc_html($tag); ?> class="<?php echo esc_attr(trim($class)); ?>" <?php echo $attr_str; ?>>
            <?php if (!empty($icon)) echo wp_kses_post($icon); ?>
            <span><?php echo esc_html($text); ?></span>
        </<?php echo esc_html($tag); ?>>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Form Group (Label, Input, Helper text, Validation badge)
     */
    public static function render_form_group($label, $input_html, $help_text = '', $badge = '', $args = array(), $echo = true) {
        $for = $args['for'] ?? '';
        $required = !empty($args['required']);
        ob_start();
        ?>
        <div class="gmb-form-group <?php echo esc_attr($args['class'] ?? ''); ?>">
            <?php if (!empty($label)) : ?>
                <div class="gmb-form-group__label-row">
                    <label class="gmb-form-label" <?php echo $for ? 'for="' . esc_attr($for) . '"' : ''; ?>>
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?>
                            <span class="gmb-required-star">*</span>
                        <?php endif; ?>
                    </label>
                    <?php if (!empty($badge)) : ?>
                        <span class="gmb-form-badge"><?php echo wp_kses_post($badge); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="gmb-form-group__control">
                <?php echo $input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <?php if (!empty($help_text)) : ?>
                <p class="gmb-form-help"><?php echo wp_kses_post($help_text); ?></p>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Linear / iOS Style Toggle Switch
     */
    public static function render_toggle($name, $checked = false, $label = '', $description = '', $attributes = array(), $echo = true) {
        $id = $attributes['id'] ?? 'gmb_toggle_' . sanitize_key($name);
        $value = $attributes['value'] ?? '1';
        $is_checked = (bool) $checked;
        
        ob_start();
        ?>
        <div class="gmb-toggle-wrapper <?php echo esc_attr($attributes['class'] ?? ''); ?>">
            <label class="gmb-switch" for="<?php echo esc_attr($id); ?>">
                <input type="checkbox" 
                       id="<?php echo esc_attr($id); ?>" 
                       name="<?php echo esc_attr($name); ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       <?php checked($is_checked, true); ?> 
                       <?php if (!empty($attributes['data_module'])) echo 'data-module="' . esc_attr($attributes['data_module']) . '"'; ?>
                       class="gmb-switch__input" />
                <span class="gmb-switch__slider"></span>
            </label>
            <?php if (!empty($label) || !empty($description)) : ?>
                <div class="gmb-toggle-text">
                    <?php if (!empty($label)) : ?>
                        <label class="gmb-toggle-label" for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                    <?php endif; ?>
                    <?php if (!empty($description)) : ?>
                        <span class="gmb-toggle-description"><?php echo esc_html($description); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Status Badge / Chip
     */
    public static function render_badge($text, $variant = 'neutral', $dot = false, $echo = true) {
        // Variants: neutral, primary, success, warning, danger, info
        ob_start();
        ?>
        <span class="gmb-badge gmb-badge--<?php echo esc_attr($variant); ?>">
            <?php if ($dot) : ?>
                <span class="gmb-badge__dot"></span>
            <?php endif; ?>
            <span><?php echo esc_html($text); ?></span>
        </span>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Notice / Alert Banner
     */
    public static function render_notice($message, $type = 'info', $dismissible = true, $icon = true, $echo = true) {
        // Types: info, success, warning, danger
        ob_start();
        ?>
        <div class="gmb-notice gmb-notice--<?php echo esc_attr($type); ?> <?php echo $dismissible ? 'is-dismissible' : ''; ?>">
            <?php if ($icon) : ?>
                <span class="gmb-notice__icon">
                    <?php if ($type === 'success') : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php elseif ($type === 'warning') : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php elseif ($type === 'danger') : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <?php else : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <div class="gmb-notice__content">
                <?php echo wp_kses_post($message); ?>
            </div>
            <?php if ($dismissible) : ?>
                <button type="button" class="gmb-notice__dismiss" onclick="this.closest('.gmb-notice').remove();" title="Dismiss">&times;</button>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Empty State
     */
    public static function render_empty_state($icon, $title, $description, $action_button = '', $echo = true) {
        ob_start();
        ?>
        <div class="gmb-empty-state">
            <?php if (!empty($icon)) : ?>
                <div class="gmb-empty-state__icon">
                    <?php echo wp_kses_post($icon); ?>
                </div>
            <?php endif; ?>
            <h4 class="gmb-empty-state__title"><?php echo esc_html($title); ?></h4>
            <p class="gmb-empty-state__description"><?php echo esc_html($description); ?></p>
            <?php if (!empty($action_button)) : ?>
                <div class="gmb-empty-state__action">
                    <?php echo wp_kses_post($action_button); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /**
     * Render Horizontal Navigation Tabs
     */
    public static function render_tabs($tabs = array(), $active_tab = '', $query_param = 'tab', $echo = true) {
        ob_start();
        ?>
        <nav class="gmb-nav-tabs">
            <?php foreach ($tabs as $key => $tab) : ?>
                <?php 
                $is_active = ($key === $active_tab);
                $url = $tab['url'] ?? add_query_arg($query_param, $key);
                ?>
                <a href="<?php echo esc_url($url); ?>" 
                   class="gmb-nav-tab <?php echo $is_active ? 'is-active' : ''; ?>"
                   <?php if (!empty($tab['id'])) echo 'id="' . esc_attr($tab['id']) . '"'; ?>
                   <?php if (!empty($tab['data_target'])) echo 'data-target="' . esc_attr($tab['data_target']) . '"'; ?>>
                    <?php if (!empty($tab['icon'])) echo wp_kses_post($tab['icon']); ?>
                    <span><?php echo esc_html($tab['label'] ?? $tab['title'] ?? $key); ?></span>
                    <?php if (!empty($tab['count'])) : ?>
                        <span class="gmb-nav-tab__count"><?php echo esc_html($tab['count']); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
        $output = ob_get_clean();
        if ($echo) {
            echo $output;
        }
        return $output;
    }
}
