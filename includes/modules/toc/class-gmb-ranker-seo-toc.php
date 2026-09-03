<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_TOC {
    public function __construct() {
        add_filter('the_content', array($this, 'inject_table_of_contents'), 12);
        add_shortcode('toc', array($this, 'toc_shortcode_callback'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'print_frontend_script'));
    }

    public function enqueue_frontend_assets() {
        if (is_singular()) {
            wp_enqueue_style(
                'gmb-ranker-seo-frontend',
                GMB_Ranker_SEO_Helpers::asset_url('css/frontend.css'),
                array(),
                '2.1.2'
            );
        }
    }

    public function print_frontend_script() {
        if (!is_singular()) {
            return;
        }
        echo '<script>document.addEventListener("click",function(e){var t=e.target.closest(".gmb-toc-toggle");if(!t)return;e.preventDefault();var b=t.closest(".gmb-toc-box");if(!b)return;b.classList.toggle("gmb-toc-collapsed");t.textContent=b.classList.contains("gmb-toc-collapsed")?"[Show]":"[Hide]";});</script>' . "\n";
    }

    public function toc_shortcode_callback() {
        global $post;
        if (!$post) {
            return '';
        }
        return $this->generate_toc_markup($post->post_content, true);
    }

    public function inject_table_of_contents($content) {
        if (!is_singular()) {
            return $content;
        }

        $all_public_types = array_values(get_post_types(array('public' => true)));
        $toc_post_types   = get_option('gmb_toc_post_types', $all_public_types);
        if (!is_array($toc_post_types) || empty($toc_post_types)) {
            $toc_post_types = $all_public_types;
        }

        $current_type = get_post_type();
        if ($current_type && !in_array($current_type, $toc_post_types, true) && !in_array($current_type, $all_public_types, true)) {
            return $content;
        }

        // Avoid infinite recursion or double rendering
        if (doing_filter('the_content') && isset($GLOBALS['gmb_rendering_toc'])) {
            return $content;
        }

        // Check if shortcode [toc] is present
        if (has_shortcode($content, 'toc')) {
            return $this->add_heading_anchors($content);
        }

        // Automatic injection
        $auto_insert = get_option('gmb_toc_auto_insert', '1');
        if ($auto_insert !== '1') {
            return $content;
        }

        $GLOBALS['gmb_rendering_toc'] = true;
        $toc_markup = $this->generate_toc_markup($content, false);
        unset($GLOBALS['gmb_rendering_toc']);

        if (!empty($toc_markup)) {
            $content_with_anchors = $this->add_heading_anchors($content);
            return $this->insert_toc_into_content($content_with_anchors, $toc_markup);
        }

        return $content;
    }

    private function insert_toc_into_content($content, $toc_markup) {
        $position = get_option('gmb_toc_position', 'before_first_heading');

        if ($position === 'top') {
            return $toc_markup . $content;
        } elseif ($position === 'bottom') {
            return $content . $toc_markup;
        } elseif ($position === 'after_first_paragraph') {
            $pos = strpos($content, '</p>');
            if ($pos !== false) {
                return substr($content, 0, $pos + 4) . $toc_markup . substr($content, $pos + 4);
            }
            return $toc_markup . $content;
        } else {
            // Default: before first heading
            $levels = $this->get_target_headings();
            $pattern = '/(<(' . implode('|', $levels) . ')[^>]*>)/i';
            if (preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
                $offset = $match[0][1];
                return substr($content, 0, $offset) . $toc_markup . substr($content, $offset);
            }
            return $toc_markup . $content;
        }
    }

    private function get_target_headings() {
        $levels = get_option('gmb_toc_levels', array('h1', 'h2', 'h3', 'h4'));
        if (!is_array($levels) || empty($levels)) {
            $levels = array('h1', 'h2', 'h3', 'h4');
        }
        $valid = array_intersect($levels, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'));
        return !empty($valid) ? array_values($valid) : array('h1', 'h2', 'h3', 'h4');
    }

    private function generate_toc_markup($content, $force = false) {
        $headings = $this->parse_headings($content);
        $min_headings = intval(get_option('gmb_toc_min_headings', 1));

        if (count($headings) < $min_headings && !$force) {
            return '';
        }

        if (empty($headings)) {
            return '';
        }

        $title = get_option('gmb_toc_title', __('Table of Contents', 'gmb-ranker-seo-automation'));
        $collapsible = get_option('gmb_toc_collapsible', '1');

        $html = '<div class="gmb-toc-box">';
        
        $html .= '<div class="gmb-toc-header">';
        $html .= '<span class="gmb-toc-title">';
        $html .= '<svg class="gmb-toc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>';
        $html .= esc_html($title);
        $html .= '</span>';
        
        if ($collapsible === '1') {
            $html .= '<button type="button" class="gmb-toc-toggle" onclick="var box = this.closest(\'.gmb-toc-box\'); box.classList.toggle(\'gmb-toc-collapsed\'); this.textContent = box.classList.contains(\'gmb-toc-collapsed\') ? \'[Show]\' : \'[Hide]\';">[Hide]</button>';
        }
        $html .= '</div>';

        $html .= '<ul class="gmb-toc-list">';
        
        foreach ($headings as $heading) {
            $html .= '<li class="gmb-toc-item level-' . esc_attr($heading['level']) . '">';
            $html .= '<a href="#' . esc_attr($heading['anchor']) . '">' . esc_html($heading['text']) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    private function parse_headings($content) {
        $headings = array();
        if (empty($content)) {
            return $headings;
        }

        $levels = $this->get_target_headings();
        $pattern = '/<(' . implode('|', $levels) . ')[^>]*>(.*?)<\/\1>/is';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = intval(substr($match[1], 1));
                $text = wp_strip_all_tags($match[2]);
                $anchor = sanitize_title($text);

                if (!empty($text)) {
                    $headings[] = array(
                        'level' => $level,
                        'text' => $text,
                        'anchor' => $anchor
                    );
                }
            }
        }

        return $headings;
    }

    private function add_heading_anchors($content) {
        $levels = $this->get_target_headings();
        $pattern = '/<(' . implode('|', $levels) . ')([^>]*)>(.*?)<\/\1>/is';

        return preg_replace_callback($pattern, function($matches) {
            $tag = $matches[1];
            $attrs = $matches[2];
            $text = $matches[3];

            // If ID attribute already exists, preserve it
            if (strpos($attrs, 'id=') !== false) {
                return $matches[0];
            }

            $anchor = sanitize_title(wp_strip_all_tags($text));
            return '<' . $tag . $attrs . ' id="' . esc_attr($anchor) . '">' . $text . '</' . $tag . '>';
        }, $content);
    }
}
