<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Links {
    public function __construct() {
        add_filter('the_content', array($this, 'process_content_links'));
    }

    public function process_content_links($content) {
        if (empty($content) || !is_singular()) {
            return $content;
        }

        $home_url = home_url();
        $home_host = strtolower(wp_parse_url($home_url, PHP_URL_HOST) ?: '');
        
        $nofollow_ext = get_option('gmb_nofollow_external_links', 'on');
        $nofollow_img = get_option('gmb_nofollow_image_links', 'off');
        $new_window   = get_option('gmb_new_window_external_links', 'on');
        
        $exclude_string = get_option('gmb_links_exclude_domains', 'google.com, wikipedia.org');
        $exclude_domains = array_filter(array_map('trim', explode(',', strtolower($exclude_string))));
        
        $affiliate_string = get_option('gmb_affiliate_link_prefixes', '');
        $affiliate_prefixes = array_filter(array_map('trim', explode("\n", strtolower($affiliate_string))));

        return preg_replace_callback('/<a\s+([^>]*)/i', function($matches) use ($home_url, $home_host, $nofollow_ext, $nofollow_img, $new_window, $exclude_domains, $affiliate_prefixes) {
            $link_attribs = $matches[1];
            
            if (preg_match('/href\s*=\s*["\']([^"\']+)["\']/i', $link_attribs, $href_match)) {
                $href = trim($href_match[1]);
                $lower_href = strtolower($href);
                
                // Skip jump anchors and non-http schemes
                if (strpos($href, '#') === 0 || strpos($href, 'mailto:') === 0 || strpos($href, 'tel:') === 0 || strpos($href, 'javascript:') === 0) {
                    return '<a ' . $link_attribs;
                }

                $is_affiliate = false;
                if (!empty($affiliate_prefixes)) {
                    $parsed_url = wp_parse_url($href);
                    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                    
                    foreach ($affiliate_prefixes as $prefix) {
                        if (empty($prefix)) {
                            continue;
                        }
                        
                        if (strpos($prefix, '/') === 0) {
                            if (strpos($path, $prefix) === 0 || strpos($lower_href, home_url($prefix)) === 0) {
                                $is_affiliate = true;
                                break;
                            }
                        } else {
                            if (strpos($lower_href, $prefix) !== false) {
                                $is_affiliate = true;
                                break;
                            }
                        }
                    }
                }

                $is_external = false;
                $target_host = strtolower(wp_parse_url($href, PHP_URL_HOST) ?: '');

                if ($is_affiliate) {
                    $is_external = true;
                } elseif (!empty($target_host)) {
                    if ($target_host !== $home_host && !empty($home_host)) {
                        // Strip leading www. for comparison
                        $norm_target = preg_replace('/^www\./i', '', $target_host);
                        $norm_home = preg_replace('/^www\./i', '', $home_host);
                        if ($norm_target !== $norm_home) {
                            $is_external = true;
                        }
                    }
                }

                if ($is_external) {
                    if ($new_window === 'on') {
                        if (!preg_match('/\btarget\s*=\s*["\']/i', $link_attribs)) {
                            $link_attribs .= ' target="_blank"';
                        }
                    }

                    $is_image_link = preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)(\?.*)?$/i', $href);

                    $should_nofollow = false;
                    $is_domain_excluded = false;

                    if (!$is_affiliate && !empty($exclude_domains) && !empty($target_host)) {
                        foreach ($exclude_domains as $domain) {
                            if (!empty($domain) && ($target_host === $domain || (strlen($target_host) > strlen($domain) && substr($target_host, -strlen('.' . $domain)) === '.' . $domain))) {
                                $is_domain_excluded = true;
                                break;
                            }
                        }
                    }

                    if (!$is_domain_excluded) {
                        if ($nofollow_ext === 'on') {
                            $should_nofollow = true;
                        } elseif ($is_image_link && $nofollow_img === 'on') {
                            $should_nofollow = true;
                        }
                    }

                    // Rel attribute handling
                    $rel_parts = array();
                    if (preg_match('/\brel\s*=\s*["\']([^"\']*)["\']/i', $link_attribs, $rel_match)) {
                        $rel_parts = array_filter(explode(' ', trim($rel_match[1])));
                    }

                    if ($new_window === 'on') {
                        if (!in_array('noopener', $rel_parts)) $rel_parts[] = 'noopener';
                    }

                    if ($should_nofollow) {
                        if (!in_array('nofollow', $rel_parts)) $rel_parts[] = 'nofollow';
                        if ($is_affiliate && !in_array('sponsored', $rel_parts)) $rel_parts[] = 'sponsored';
                    }

                    if (!empty($rel_parts)) {
                        $new_rel_str = implode(' ', array_unique($rel_parts));
                        if (preg_match('/\brel\s*=\s*["\'][^"\']*["\']/i', $link_attribs)) {
                            $link_attribs = preg_replace('/\brel\s*=\s*["\'][^"\']*["\']/i', 'rel="' . esc_attr($new_rel_str) . '"', $link_attribs);
                        } else {
                            $link_attribs .= ' rel="' . esc_attr($new_rel_str) . '"';
                        }
                    }
                }
            }
            
            return '<a ' . $link_attribs;
        }, $content);
    }
}
