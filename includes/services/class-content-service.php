<?php
/**
 * Content Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Content_Service {

    /**
     * Inject an internal link into an HTML content string on the first exact anchor match
     *
     * @param string $html
     * @param string $anchor
     * @param string $url
     * @return array [ 'success' => bool, 'html' => string ]
     */
    public function inject_link_in_html($html, $anchor, $url) {
        $anchor_esc = preg_quote($anchor, '/');
        // Do not inject inside existing <a> tags or headings
        $pattern = '/(?!(?:[^<]+>|[^>]+<\/a>))\b(' . $anchor_esc . ')\b/iu';
        $link_tag = '<a href="' . esc_url($url) . '" title="$1">$1</a>';

        $count = 0;
        $new_html = preg_replace($pattern, $link_tag, $html, 1, $count);

        return array(
            'success' => ($count > 0),
            'html'    => ($count > 0) ? $new_html : $html,
        );
    }

    /**
     * Inject an internal link into Elementor builder json structure
     *
     * @param array  $elements
     * @param string $anchor
     * @param string $url
     * @param bool   $injected
     */
    public function inject_link_in_elementor_data(&$elements, $anchor, $url, &$injected) {
        if ($injected || !is_array($elements)) {
            return;
        }

        foreach ($elements as &$element) {
            if ($injected) break;

            if (isset($element['widgetType']) && in_array($element['widgetType'], array('text-editor', 'heading'), true)) {
                if (isset($element['settings']['editor'])) {
                    $res = $this->inject_link_in_html($element['settings']['editor'], $anchor, $url);
                    if ($res['success']) {
                        $element['settings']['editor'] = $res['html'];
                        $injected = true;
                        break;
                    }
                }
            }

            if (!empty($element['elements']) && is_array($element['elements'])) {
                $this->inject_link_in_elementor_data($element['elements'], $anchor, $url, $injected);
            }
        }
    }

    /**
     * Update post content with new link
     *
     * @param int    $post_id
     * @param string $anchor
     * @param string $url
     * @return bool
     */
    public function inject_link_in_post($post_id, $anchor, $url) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        // Try Elementor first if page was built with it
        $is_elementor = get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder';
        $elementor_data_raw = get_post_meta($post_id, '_elementor_data', true);

        if ($is_elementor && !empty($elementor_data_raw)) {
            $elements = json_decode($elementor_data_raw, true);
            if (is_array($elements)) {
                $injected = false;
                $this->inject_link_in_elementor_data($elements, $anchor, $url, $injected);
                if ($injected) {
                    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($elements)));
                    return true;
                }
            }
        }

        // Fallback to post_content
        $res = $this->inject_link_in_html($post->post_content, $anchor, $url);
        if ($res['success']) {
            wp_update_post(array(
                'ID'           => $post_id,
                'post_content' => $res['html'],
            ));
            return true;
        }

        return false;
    }
}
