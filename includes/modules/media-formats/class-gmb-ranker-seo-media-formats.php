<?php
/**
 * Media Formats & Safe SVG Support Module
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Media_Formats {

    /**
     * Constructor
     */
    public function __construct() {
        if (!GMB_Ranker_SEO_Helpers::is_module_enabled('media_formats')) {
            return;
        }

        // Add support for extended MIME types
        add_filter('upload_mimes', array($this, 'filter_upload_mimes'), 99, 1);

        // Fix filetype and extension check in WordPress 4.7.1+
        add_filter('wp_check_filetype_and_ext', array($this, 'filter_filetype_and_ext'), 99, 4);

        // Sanitize uploaded SVG files to prevent XSS / XXE vulnerabilities
        add_filter('wp_handle_upload_prefilter', array($this, 'sanitize_svg_upload'));

        // Generate accurate attachment metadata for SVGs (dimensions from viewBox/width/height)
        add_filter('wp_generate_attachment_metadata', array($this, 'generate_svg_metadata'), 10, 2);

        // Fix image dimensions when requested by themes/plugins
        add_filter('wp_get_attachment_image_src', array($this, 'fix_svg_attachment_image_src'), 10, 4);

        // Admin CSS for SVG display in Media Library & Editor
        if (is_admin()) {
            add_action('admin_head', array($this, 'inject_admin_media_css'));
        }
    }

    /**
     * Enable extended modern image and structured data MIME types
     *
     * @param array $mimes
     * @return array
     */
    public function filter_upload_mimes($mimes = array()) {
        if (!is_array($mimes)) {
            $mimes = array();
        }

        // SVGs
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';

        // Modern Image Formats
        $mimes['webp'] = 'image/webp';
        $mimes['avif'] = 'image/avif';
        $mimes['ico']  = 'image/x-icon';

        // Structured Data & SEO Formats
        $mimes['json']    = 'application/json';
        $mimes['csv']     = 'text/csv';
        $mimes['xml']     = 'text/xml';
        $mimes['geojson'] = 'application/geo+json';

        return $mimes;
    }

    /**
     * Fix WordPress core strict filetype verification for SVG & modern formats
     *
     * @param array $data
     * @param string $file
     * @param string $filename
     * @param array $mimes
     * @return array
     */
    public function filter_filetype_and_ext($data, $file, $filename, $mimes) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'svg') {
            $data['ext']  = 'svg';
            $data['type'] = 'image/svg+xml';
        } elseif ($ext === 'svgz') {
            $data['ext']  = 'svgz';
            $data['type'] = 'image/svg+xml';
        } elseif ($ext === 'webp') {
            $data['ext']  = 'webp';
            $data['type'] = 'image/webp';
        } elseif ($ext === 'avif') {
            $data['ext']  = 'avif';
            $data['type'] = 'image/avif';
        } elseif ($ext === 'json') {
            $data['ext']  = 'json';
            $data['type'] = 'application/json';
        }

        return $data;
    }

    /**
     * Sanitize SVG upload to strip scripts, foreign objects, and XXE payloads
     *
     * @param array $file
     * @return array
     */
    public function sanitize_svg_upload($file) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return $file;
        }

        $filename = isset($file['name']) ? $file['name'] : '';
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext !== 'svg' && $ext !== 'svgz') {
            return $file;
        }

        $tmp_file = $file['tmp_name'];
        if (!file_exists($tmp_file) || !is_readable($tmp_file)) {
            return $file;
        }

        $content = file_get_contents($tmp_file);
        if ($content === false) {
            return $file;
        }

        // Decompress if gzip SVGZ
        $is_gzipped = (strpos($content, "\x1f\x8b\x08") === 0);
        if ($is_gzipped && function_exists('gzdecode')) {
            $decoded = @gzdecode($content);
            if ($decoded !== false) {
                $content = $decoded;
            }
        }

        $sanitized = self::sanitize_svg_content($content);
        if ($sanitized === false) {
            $file['error'] = 'Security Alert: Malicious SVG content detected and blocked.';
            return $file;
        }

        // Re-encode or write sanitized content back to temporary file
        if ($is_gzipped && function_exists('gzencode')) {
            $sanitized = gzencode($sanitized);
        }

        file_put_contents($tmp_file, $sanitized);

        return $file;
    }

    /**
     * Pure SVG Sanitizer method
     *
     * @param string $svg_content
     * @return string|false Sanitized SVG string or false if non-recoverable vulnerability
     */
    public static function sanitize_svg_content($svg_content) {
        if (empty($svg_content) || !is_string($svg_content)) {
            return false;
        }

        // 1. Block XML Entity Expansion / XXE Attacks
        if (preg_match('/<!ENTITY|SYSTEM|PUBLIC/i', $svg_content)) {
            // Remove DOCTYPE with custom entities safely
            $svg_content = preg_replace('/<!DOCTYPE[^>]*(\[[^\]]*\])?>/i', '', $svg_content);
            if (preg_match('/<!ENTITY/i', $svg_content)) {
                return false;
            }
        }

        // 2. Remove executable tags (<script>, <foreignObject>, <applet>, <iframe>, <embed>, <object>)
        $dangerous_tags = array('script', 'foreignObject', 'applet', 'iframe', 'embed', 'object', 'meta', 'link');
        foreach ($dangerous_tags as $tag) {
            $svg_content = preg_replace('#<\s*' . $tag . '\b[^>]*>.*?<\s*/\s*' . $tag . '\s*>#is', '', $svg_content);
            $svg_content = preg_replace('#<\s*' . $tag . '\b[^>]*\/?>#is', '', $svg_content);
        }

        // 3. Remove inline javascript attributes (onclick, onload, onerror, etc.)
        $svg_content = preg_replace('/\s*on[a-zA-Z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $svg_content);

        // 4. Remove javascript: and data: pseudo-protocol URIs inside attributes
        $svg_content = preg_replace('/(href|xlink:href|src)\s*=\s*["\']\s*(javascript|vbscript|data):[^"\']*["\']/is', '', $svg_content);

        // 5. Verify valid SVG root element
        if (!preg_match('/<svg\b[^>]*>/i', $svg_content)) {
            return false;
        }

        return $svg_content;
    }

    /**
     * Generate accurate SVG metadata (width, height, orientation) from SVG content
     *
     * @param array $metadata
     * @param int $attachment_id
     * @return array
     */
    public function generate_svg_metadata($metadata, $attachment_id) {
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext !== 'svg' && $ext !== 'svgz') {
            return $metadata;
        }

        if (!is_array($metadata)) {
            $metadata = array();
        }

        $dimensions = self::get_svg_dimensions($file);
        if ($dimensions) {
            $metadata['width']  = $dimensions['width'];
            $metadata['height'] = $dimensions['height'];
            $metadata['file']   = _wp_relative_upload_path($file);
        }

        return $metadata;
    }

    /**
     * Extract dimensions from an SVG file
     *
     * @param string $svg_path
     * @return array|false Array with 'width' and 'height' keys, or false
     */
    public static function get_svg_dimensions($svg_path) {
        if (!file_exists($svg_path) || !is_readable($svg_path)) {
            return false;
        }

        $content = file_get_contents($svg_path);
        if (!$content) {
            return false;
        }

        // If gzipped, decompress
        if (strpos($content, "\x1f\x8b\x08") === 0 && function_exists('gzdecode')) {
            $content = @gzdecode($content);
        }

        $width = 0;
        $height = 0;

        // Try extracting width/height attributes
        if (preg_match('/<svg\b[^>]*\bwidth=["\']([0-9\.]+)px?["\']/i', $content, $m_w)) {
            $width = (float) $m_w[1];
        }
        if (preg_match('/<svg\b[^>]*\bheight=["\']([0-9\.]+)px?["\']/i', $content, $m_h)) {
            $height = (float) $m_h[1];
        }

        // Try viewBox fallback if width/height not explicitly given
        if (($width <= 0 || $height <= 0) && preg_match('/<svg\b[^>]*\bviewBox=["\']\s*([0-9\.\-]+)\s+([0-9\.\-]+)\s+([0-9\.\-]+)\s+([0-9\.\-]+)\s*["\']/i', $content, $m_vb)) {
            $vb_w = (float) $m_vb[3];
            $vb_h = (float) $m_vb[4];
            if ($vb_w > 0 && $vb_h > 0) {
                $width = $width > 0 ? $width : $vb_w;
                $height = $height > 0 ? $height : $vb_h;
            }
        }

        if ($width > 0 && $height > 0) {
            return array(
                'width'  => (int) round($width),
                'height' => (int) round($height),
            );
        }

        return array('width' => 200, 'height' => 200);
    }

    /**
     * Fix SVG attachment dimensions when queried via wp_get_attachment_image_src
     *
     * @param array|false $image
     * @param int $attachment_id
     * @param string|array $size
     * @param bool $icon
     * @return array|false
     */
    public function fix_svg_attachment_image_src($image, $attachment_id, $size, $icon) {
        if ($image) {
            $file = get_attached_file($attachment_id);
            if ($file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'svg') {
                if (empty($image[1]) || empty($image[2])) {
                    $dims = self::get_svg_dimensions($file);
                    if ($dims) {
                        $image[1] = $dims['width'];
                        $image[2] = $dims['height'];
                    }
                }
            }
        }
        return $image;
    }

    /**
     * Injects CSS for responsive SVG previews in WordPress Media Library
     */
    public function inject_admin_media_css() {
        ?>
        <style type="text/css">
            .attachment-266x266, .thumbnail img[src$=".svg"],
            .media-modal img[src$=".svg"],
            .attachment-preview img[src$=".svg"],
            .media-frame .attachment .thumbnail img[src$=".svg"] {
                width: 100% !important;
                height: auto !important;
                max-height: 100% !important;
            }
        </style>
        <?php
    }
}
