<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Image {
    public function __construct() {
        add_filter('the_content', array($this, 'auto_image_attributes'));
        add_filter('wp_get_attachment_image_attributes', array($this, 'auto_attachment_image_attributes'), 10, 3);
    }

    private function get_image_replacements($post_id, $filename_clean = '', $count = 1) {
        $post = get_post($post_id);
        $post_title = $post ? get_the_title($post_id) : '';
        $author_id = $post ? $post->post_author : 0;
        $author_name = $author_id ? get_the_author_meta('display_name', $author_id) : '';
        $categories = get_the_category($post_id);
        $category_name = (!empty($categories) && !is_wp_error($categories)) ? $categories[0]->name : '';
        $focus_kw = get_post_meta($post_id, '_gmb_ranker_focus_keyword', true) ?: '';

        return array(
            '%title%'         => esc_attr($post_title),
            '%sitename%'      => esc_attr(get_bloginfo('name')),
            '%filename%'      => esc_attr($filename_clean),
            '%focus_keyword%' => esc_attr($focus_kw),
            '%category%'      => esc_attr($category_name),
            '%author%'        => esc_attr($author_name),
            '%count%'         => strval($count),
            '%currentyear%'   => date_i18n('Y'),
        );
    }

    public function auto_image_attributes($content) {
        if (!is_singular()) {
            return $content;
        }

        global $post;
        if (empty($post)) {
            return $content;
        }

        $alt_template = get_option('gmb_image_seo_alt_template', '%title%');
        $title_template = get_option('gmb_image_seo_title_template', '%title%');
        $img_counter = 0;

        return preg_replace_callback('/<img\s+([^>]*)/i', function($matches) use ($post, $alt_template, $title_template, &$img_counter) {
            $img_counter++;
            $img_attribs = $matches[1];
            
            $filename_clean = '';
            if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $img_attribs, $src_match)) {
                $src = $src_match[1];
                $clean_src = strtok($src, '?');
                $filename = pathinfo(basename($clean_src), PATHINFO_FILENAME);
                $filename_clean = ucwords(str_replace(array('-', '_'), ' ', $filename));
            }

            $replacements = $this->get_image_replacements($post->ID, $filename_clean, $img_counter);

            // Handle ALT attribute (if missing or empty)
            if (preg_match('/\balt\s*=\s*["\']\s*["\']/i', $img_attribs)) {
                $alt_val = str_replace(array_keys($replacements), array_values($replacements), $alt_template);
                $img_attribs = preg_replace('/\balt\s*=\s*["\']\s*["\']/i', 'alt="' . esc_attr(trim($alt_val)) . '"', $img_attribs);
            } elseif (!preg_match('/\balt\s*=\s*["\']/i', $img_attribs)) {
                $alt_val = str_replace(array_keys($replacements), array_values($replacements), $alt_template);
                $img_attribs .= ' alt="' . esc_attr(trim($alt_val)) . '"';
            }

            // Handle Title attribute (if missing or empty)
            if (preg_match('/\btitle\s*=\s*["\']\s*["\']/i', $img_attribs)) {
                $title_val = str_replace(array_keys($replacements), array_values($replacements), $title_template);
                $img_attribs = preg_replace('/\btitle\s*=\s*["\']\s*["\']/i', 'title="' . esc_attr(trim($title_val)) . '"', $img_attribs);
            } elseif (!preg_match('/\btitle\s*=\s*["\']/i', $img_attribs)) {
                $title_val = str_replace(array_keys($replacements), array_values($replacements), $title_template);
                $img_attribs .= ' title="' . esc_attr(trim($title_val)) . '"';
            }

            return '<img ' . $img_attribs;
        }, $content);
    }

    public function auto_attachment_image_attributes($attr, $attachment, $size) {
        $post_id = get_the_ID();
        if (!$post_id) {
            return $attr;
        }

        $alt_template = get_option('gmb_image_seo_alt_template', '%title%');
        $title_template = get_option('gmb_image_seo_title_template', '%title%');

        $attachment_url = is_object($attachment) ? $attachment->guid : (is_numeric($attachment) ? wp_get_attachment_url($attachment) : '');
        $filename = pathinfo(basename($attachment_url), PATHINFO_FILENAME);
        $filename_clean = ucwords(str_replace(array('-', '_'), ' ', $filename));

        $replacements = $this->get_image_replacements($post_id, $filename_clean);

        if (empty($attr['alt'])) {
            $attr['alt'] = trim(str_replace(array_keys($replacements), array_values($replacements), $alt_template));
        }

        if (empty($attr['title'])) {
            $attr['title'] = trim(str_replace(array_keys($replacements), array_values($replacements), $title_template));
        }

        return $attr;
    }
}
