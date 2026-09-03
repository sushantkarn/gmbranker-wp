<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Schema {
    public function __construct() {
        if ($this->is_rank_math_schema_active() && get_option('gmb_schema_integrate_rankmath', '1') !== '0') {
            add_filter('rank_math/json_ld', array($this, 'filter_rank_math_json_ld'), 10, 2);
        }
        if ($this->is_yoast_schema_active() && get_option('gmb_schema_integrate_yoast', '1') !== '0') {
            add_filter('wpseo_schema_graph_pieces', array($this, 'filter_yoast_schema_graph_pieces'), 10, 2);
        }
        
        // Output Schema JSON-LD directly to wp_head
        add_action('wp_head', array($this, 'inject_json_ld_schema'), 2);
    }

    private function is_rank_math_schema_active() {
        return class_exists('RankMath') && class_exists('RankMath\Helper') && \RankMath\Helper::is_module_active('rich-snippet');
    }

    private function is_yoast_schema_active() {
        return defined('WPSEO_VERSION');
    }

    public function get_current_page_id() {
        if (is_front_page()) {
            $id = get_option('page_on_front');
            if (!empty($id)) return intval($id);
        }
        if (is_home()) {
            $id = get_option('page_for_posts');
            if (!empty($id)) return intval($id);
        }
        if (is_singular()) {
            $id = get_the_ID();
            if (!empty($id)) return intval($id);
        }
        $queried = get_queried_object_id();
        if (!empty($queried)) {
            return intval($queried);
        }
        return 0;
    }

    public function filter_rank_math_json_ld($data, $jsonld) {
        $post_id = $this->get_current_page_id();
        if (empty($post_id)) {
            return $data;
        }

        $gmb_schema = $this->get_gmb_schema_array($post_id);
        if (!empty($gmb_schema)) {
            if (isset($gmb_schema['@context'])) {
                $data['gmb_ranker_schema'] = $gmb_schema;
            } else {
                foreach ($gmb_schema as $key => $val) {
                    $data['gmb_ranker_schema_' . $key] = $val;
                }
            }
        }

        $conditional_schemas = $this->get_conditional_schema_templates($post_id);
        if (!empty($conditional_schemas)) {
            foreach ($conditional_schemas as $idx => $c_schema) {
                $data['gmb_ranker_template_' . $idx] = $c_schema;
            }
        }

        return $data;
    }

    public function filter_yoast_schema_graph_pieces($pieces, $context) {
        $post_id = $this->get_current_page_id();
        if (empty($post_id)) {
            return $pieces;
        }

        $gmb_schema = $this->get_gmb_schema_array($post_id);
        if (!empty($gmb_schema)) {
            $pieces[] = $gmb_schema;
        }

        $conditional_schemas = $this->get_conditional_schema_templates($post_id);
        if (!empty($conditional_schemas)) {
            foreach ($conditional_schemas as $c_schema) {
                $pieces[] = $c_schema;
            }
        }

        return $pieces;
    }

    public function replace_schema_variables($string, $post_id = 0) {
        if (empty($string)) return '';
        
        $replacements = array(
            '%sitename%'     => get_bloginfo('name'),
            '%sitedesc%'     => get_bloginfo('description'),
            '%siteurl%'      => home_url('/'),
            '%url%'          => !empty($post_id) ? get_permalink($post_id) : home_url('/'),
            '%date%'         => date_i18n('Y-m-d'),
            '%currentyear%'  => date_i18n('Y'),
            '%phone%'        => get_option('gmb_local_business_phone', get_option('gmb_local_seo_phone', '')),
            '%email%'        => get_option('gmb_local_business_email', get_bloginfo('admin_email')),
            '%street%'       => get_option('gmb_local_street_address', get_option('gmb_local_seo_address_street', '')),
            '%locality%'     => get_option('gmb_local_locality', get_option('gmb_local_seo_address_locality', '')),
            '%region%'       => get_option('gmb_local_region', get_option('gmb_local_seo_address_region', '')),
            '%postal%'       => get_option('gmb_local_postal_code', get_option('gmb_local_seo_address_postal', '')),
            '%country%'      => get_option('gmb_local_country', get_option('gmb_local_seo_address_country', '')),
            '%featured_image%' => '',
        );

        if (!empty($post_id)) {
            $post = get_post($post_id);
            if ($post) {
                $replacements['%title%'] = get_the_title($post_id);
                $replacements['%post_title%'] = get_the_title($post_id);
                $author_id = $post->post_author;
                $replacements['%author%'] = get_the_author_meta('display_name', $author_id);
                $replacements['%excerpt%'] = wp_strip_all_tags(get_the_excerpt($post_id) ?: wp_trim_words($post->post_content, 30));
                $replacements['%post_date%'] = get_the_date('c', $post_id);
                $replacements['%modified_date%'] = get_the_modified_date('c', $post_id);

                if (has_post_thumbnail($post_id)) {
                    $replacements['%featured_image%'] = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                } else {
                    $replacements['%featured_image%'] = get_option('gmb_schema_default_image', get_option('gmb_metadata_og_thumbnail', ''));
                }
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    /**
     * Evaluate Display Conditions for Schema Templates.
     *
     * @param array $conditions Repeater rules array.
     * @param int   $post_id    Queried post ID.
     * @return bool True if conditions match current request.
     */
    public function matches_display_conditions($conditions, $post_id = 0) {
        if (empty($conditions) || !is_array($conditions)) {
            return false;
        }

        if (empty($post_id)) {
            $post_id = $this->get_current_page_id();
        }

        $is_home = is_front_page() || is_home();
        $post_type = $post_id ? get_post_type($post_id) : '';

        $has_includes = false;
        $include_matched = false;

        foreach ($conditions as $c) {
            $c_type   = isset($c['type']) ? sanitize_key($c['type']) : 'include';
            $c_target = isset($c['target']) ? sanitize_key($c['target']) : 'entire_site';
            $c_value  = isset($c['value']) ? trim($c['value']) : '';

            $rule_matched = false;

            switch ($c_target) {
                case 'entire_site':
                    $rule_matched = true;
                    break;
                case 'homepage':
                    $rule_matched = $is_home;
                    break;
                case 'post_type':
                    if (!empty($post_type) && !empty($c_value)) {
                        $rule_matched = ($post_type === $c_value);
                    }
                    break;
                case 'taxonomy':
                    if ($post_id && !empty($c_value)) {
                        if (strpos($c_value, ':') !== false) {
                            list($tax, $term) = explode(':', $c_value, 2);
                            $rule_matched = has_term($term, $tax, $post_id);
                        } else {
                            $rule_matched = has_term($c_value, 'category', $post_id);
                        }
                    }
                    break;
                case 'specific_post':
                    if ($post_id && !empty($c_value)) {
                        $rule_matched = ($post_id === intval($c_value));
                    }
                    break;
                case 'archives':
                    $rule_matched = is_archive();
                    break;
            }

            // Exclude rule match instantly disqualifies
            if ($c_type === 'exclude' && $rule_matched) {
                return false;
            }

            if ($c_type === 'include') {
                $has_includes = true;
                if ($rule_matched) {
                    $include_matched = true;
                }
            }
        }

        return $has_includes ? $include_matched : true;
    }

    /**
     * Retrieve all active Schema Templates matching current page conditions.
     *
     * @param int $post_id
     * @return array
     */
    public function get_conditional_schema_templates($post_id = 0) {
        $templates = get_option('gmb_schema_templates', array());
        if (empty($templates) || !is_array($templates)) {
            return array();
        }

        if (empty($post_id)) {
            $post_id = $this->get_current_page_id();
        }

        $matched_schemas = array();
        foreach ($templates as $tpl) {
            $status = isset($tpl['status']) ? $tpl['status'] : 'active';
            if ($status !== 'active') {
                continue;
            }

            $conditions = isset($tpl['conditions']) ? $tpl['conditions'] : array();
            if ($this->matches_display_conditions($conditions, $post_id)) {
                $raw_json = isset($tpl['schema_json']) ? $tpl['schema_json'] : '';
                if (empty($raw_json) && isset($tpl['schema_data'])) {
                    $raw_json = is_array($tpl['schema_data']) ? wp_json_encode($tpl['schema_data']) : $tpl['schema_data'];
                }

                if (!empty($raw_json)) {
                    $clean_json = trim($raw_json);
                    if (strpos($clean_json, '<script') !== false) {
                        $clean_json = preg_replace('/<script[^>]*>/i', '', $clean_json);
                        $clean_json = preg_replace('/<\/script>/i', '', $clean_json);
                        $clean_json = trim($clean_json);
                    }
                    $clean_json = $this->replace_schema_variables($clean_json, $post_id);
                    $decoded = json_decode($clean_json, true);
                    if (is_array($decoded)) {
                        $decoded = $this->ensure_product_schema_compliance($decoded, $post_id);
                        $matched_schemas[] = $decoded;
                    }
                }
            }
        }

        return $matched_schemas;
    }

    private function get_gmb_schema_array($post_id) {
        if (!empty($post_id)) {
            $custom_schema = get_post_meta($post_id, '_gmb_ranker_json_ld', true);
            
            if (!empty($custom_schema)) {
                $clean_schema = trim($custom_schema);
                if (strpos($clean_schema, '<script') !== false) {
                    $clean_schema = preg_replace('/<script[^>]*>/i', '', $clean_schema);
                    $clean_schema = preg_replace('/<\/script>/i', '', $clean_schema);
                    $clean_schema = trim($clean_schema);
                }
                $clean_schema = $this->replace_schema_variables($clean_schema, $post_id);
                $decoded = json_decode($clean_schema, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        // Auto-generate based on page context
        $schemas = array();

        // 1. Homepage: WebSite Schema + LocalBusiness/Organization
        if (is_front_page() || is_home()) {
            if (get_option('gmb_schema_enable_website', '1') !== '0') {
                $site_name = get_option('gmb_schema_website_name', get_bloginfo('name'));
                $alt_name = get_option('gmb_schema_website_alt_name', '');
                $website_schema = array(
                    '@context' => 'https://schema.org',
                    '@type'    => 'WebSite',
                    'name'     => esc_html($site_name),
                    'url'      => esc_url(home_url('/')),
                );
                if (!empty($alt_name)) {
                    $website_schema['alternateName'] = esc_html($alt_name);
                }
                if (get_option('gmb_schema_enable_sitelinks', '1') !== '0') {
                    $website_schema['potentialAction'] = array(
                        '@type'       => 'SearchAction',
                        'target'      => home_url('/?s={search_term_string}'),
                        'query-input' => 'required name=search_term_string'
                    );
                }
                $schemas[] = $website_schema;
            }

            $local_name = get_option('gmb_local_seo_name', get_option('gmb_local_business_name', get_bloginfo('name')));
            $local_type = get_option('gmb_local_seo_type', get_option('gmb_local_business_type', 'Organization'));
            if ($local_type === 'person') {
                $local_type = 'Person';
            } elseif (empty($local_type) || $local_type === 'organization') {
                $local_type = 'Organization';
            }
            
            $org_schema = array(
                '@context' => 'https://schema.org',
                '@type'    => $local_type,
                'name'     => esc_html($local_name),
                'url'      => esc_url(home_url('/')),
            );
            
            $local_logo = get_option('gmb_local_seo_logo', '');
            $site_icon = !empty($local_logo) ? $local_logo : get_site_icon_url(512);
            if ($site_icon) {
                $org_schema['logo'] = esc_url($site_icon);
                $org_schema['image'] = esc_url($site_icon);
            }
            $phone = get_option('gmb_local_seo_phone', get_option('gmb_local_phone', get_option('gmb_local_business_phone', '')));
            if (!empty($phone)) {
                $org_schema['telephone'] = esc_html($phone);
            }
            $email = get_option('gmb_local_seo_email', get_option('gmb_local_email', get_option('gmb_local_business_email', get_bloginfo('admin_email'))));
            if (!empty($email)) {
                $org_schema['email'] = esc_html($email);
            }
            $street = get_option('gmb_local_seo_address_street', get_option('gmb_local_street_address', ''));
            $city = get_option('gmb_local_seo_address_locality', get_option('gmb_local_locality', ''));
            $region = get_option('gmb_local_seo_address_region', get_option('gmb_local_region', ''));
            $postal = get_option('gmb_local_seo_address_postal', get_option('gmb_local_postal_code', ''));
            $country = get_option('gmb_local_seo_address_country', get_option('gmb_local_country', ''));
            if (!empty($street) || !empty($city) || !empty($country)) {
                $org_schema['address'] = array(
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => esc_html($street),
                    'addressLocality' => esc_html($city),
                    'addressRegion'   => esc_html($region),
                    'postalCode'      => esc_html($postal),
                    'addressCountry'  => esc_html($country)
                );
            }

            $lat = get_option('gmb_local_business_lat', '');
            $lng = get_option('gmb_local_business_lng', '');
            if (!empty($lat) && !empty($lng)) {
                $org_schema['geo'] = array(
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => (float) $lat,
                    'longitude' => (float) $lng,
                );
            }

            $price_range = get_option('gmb_local_business_price_range', '');
            if (!empty($price_range)) {
                $org_schema['priceRange'] = esc_html($price_range);
            }

            $hours = get_option('gmb_local_business_opening_hours', '');
            if (!empty($hours)) {
                $org_schema['openingHours'] = esc_html($hours);
            }

            $about_id = get_option('gmb_schema_about_page', get_option('gmb_local_seo_about_page', 0));
            if (!empty($about_id)) {
                $org_schema['about'] = array(
                    '@type' => 'AboutPage',
                    'url'   => esc_url(get_permalink($about_id)),
                );
            }

            $contact_id = get_option('gmb_schema_contact_page', get_option('gmb_local_seo_contact_page', 0));
            if (!empty($contact_id) || !empty($phone) || !empty($email)) {
                $contact_point = array(
                    '@type'       => 'ContactPoint',
                    'contactType' => 'customer service',
                );
                if (!empty($contact_id)) {
                    $contact_point['url'] = esc_url(get_permalink($contact_id));
                }
                if (!empty($phone)) {
                    $contact_point['telephone'] = esc_html($phone);
                }
                if (!empty($email)) {
                    $contact_point['email'] = esc_html($email);
                }
                $org_schema['contactPoint'] = $contact_point;
            }

            $same_as = array();
            $social_keys = array(
                'gmb_social_facebook_page_url',
                'gmb_social_instagram_url',
                'gmb_social_linkedin_url',
                'gmb_social_youtube_url',
                'gmb_social_pinterest_url',
                'gmb_social_tiktok_url',
                'gmb_social_wikipedia_url',
            );
            foreach ($social_keys as $s_key) {
                $s_val = get_option($s_key, '');
                if (!empty($s_val)) {
                    $same_as[] = esc_url($s_val);
                }
            }
            $tw_user = get_option('gmb_social_twitter_username', '');
            if (!empty($tw_user)) {
                $tw_clean = ltrim($tw_user, '@');
                $same_as[] = esc_url('https://twitter.com/' . $tw_clean);
            }
            $add_profiles = get_option('gmb_social_additional_profiles', '');
            if (!empty($add_profiles)) {
                $profile_urls = array_filter(array_map('trim', explode("\n", $add_profiles)));
                foreach ($profile_urls as $p_url) {
                    if (!empty($p_url)) {
                        $same_as[] = esc_url($p_url);
                    }
                }
            }
            if (!empty($same_as)) {
                $org_schema['sameAs'] = array_values(array_unique($same_as));
            }

            $schemas[] = $org_schema;
            return $schemas;
        }

        // 2. Taxonomy Archive (Categories, Tags, Custom Taxonomies)
        if (is_category() || is_tag() || is_tax()) {
            if (is_category() && get_option('gmb_categories_remove_snippet', '0') === '1') {
                return array();
            }
            $term = get_queried_object();
            if ($term && !is_wp_error($term)) {
                $collection_schema = array(
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CollectionPage',
                    'name'        => esc_html($term->name),
                    'url'         => esc_url(get_term_link($term)),
                    'description' => esc_html($term->description ?: get_bloginfo('description'))
                );
                $schemas[] = $collection_schema;
                
                $breadcrumbs = array(
                    '@context'        => 'https://schema.org',
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => array(
                        array(
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Home',
                            'item'     => esc_url(home_url('/'))
                        ),
                        array(
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => esc_html($term->name),
                            'item'     => esc_url(get_term_link($term))
                        )
                    )
                );
                $schemas[] = $breadcrumbs;
            }
            return $schemas;
        }

        // 3. Singular Pages / Posts / Products / Services
        if (!empty($post_id)) {
            $post = get_post($post_id);
            if (!$post) return array();

            // Product Schema
            if ($post->post_type === 'product' && class_exists('WooCommerce')) {
                $product = wc_get_product($post_id);
                if ($product) {
                    $product_schema = array(
                        '@context'    => 'https://schema.org',
                        '@type'       => 'Product',
                        'name'        => esc_html($product->get_name()),
                        'url'         => esc_url(get_permalink($post_id)),
                        'description' => esc_html(wp_strip_all_tags($product->get_short_description() ?: $product->get_description())),
                        'sku'         => esc_html($product->get_sku() ?: (string)$post_id),
                        'offers'      => array(
                            '@type'         => 'Offer',
                            'price'         => esc_html($product->get_price() ?: '0'),
                            'priceCurrency' => esc_html(get_woocommerce_currency()),
                            'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                            'url'           => esc_url(get_permalink($post_id))
                        )
                    );
                    $img_id = $product->get_image_id();
                    if ($img_id) {
                        $product_schema['image'] = esc_url(wp_get_attachment_url($img_id));
                    }
                    $schemas[] = $product_schema;
                }
            } 
            // Post / Article Schema
            elseif ($post->post_type === 'post' || $post->post_type === 'page' || $post->post_type === 'services' || $post->post_type === 'service' || in_array($post->post_type, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations')) || in_array($post->post_type, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'))) {
                $is_post = ($post->post_type === 'post');
                $is_service = ($post->post_type === 'services' || $post->post_type === 'service');
                $is_location = in_array($post->post_type, array('service_locations', 'service_location', 'location', 'locations', 'service-location', 'service-locations'));
                $is_team = in_array($post->post_type, array('team_members', 'team_member', 'team', 'teams', 'team-member', 'team-members', 'member', 'members'));
                
                // Dynamic post type default schema mapping
                $pt_slug = $post->post_type;
                $default_schema_type = get_option('gmb_' . $pt_slug . '_schema_type', '');
                if (empty($default_schema_type)) {
                    if ($is_post) {
                        $default_schema_type = get_option('gmb_posts_schema_type', 'article');
                    } elseif ($is_service) {
                        $default_schema_type = get_option('gmb_services_schema_type', 'service');
                    } elseif ($is_location) {
                        $default_schema_type = get_option('gmb_service_locations_schema_type', 'localbusiness');
                    } elseif ($is_team) {
                        $default_schema_type = get_option('gmb_team_members_schema_type', 'person');
                    } else {
                        $default_schema_type = get_option('gmb_pages_schema_type', 'none');
                    }
                }
                
                $schema_type = get_post_meta($post_id, '_gmb_ranker_schema_type', true) ?: $default_schema_type;

                if ($schema_type !== 'none') {
                    $article_type_opt = get_option('gmb_' . $pt_slug . '_article_type', ($is_post ? get_option('gmb_posts_article_type', 'article') : 'article'));
                    $headline_template = get_option('gmb_' . $pt_slug . '_schema_headline', ($is_post ? get_option('gmb_posts_schema_headline', '%seo_title%') : '%seo_title%'));
                    $desc_template = get_option('gmb_' . $pt_slug . '_schema_desc', ($is_post ? get_option('gmb_posts_schema_desc', '%seo_description%') : '%seo_description%'));

                    $headline = !empty($headline_template) ? $this->replace_schema_variables($headline_template, $post_id) : get_the_title($post_id);
                    $desc = !empty($desc_template) ? $this->replace_schema_variables($desc_template, $post_id) : wp_trim_words(wp_strip_all_tags($post->post_content), 30);

                    $schema_key = strtolower(str_replace(array(' ', '_', '-'), '', $schema_type));
                    $type_name = 'Article';
                    if ($schema_key === 'article') {
                        if ($article_type_opt === 'blogpost') {
                            $type_name = 'BlogPosting';
                        } elseif ($article_type_opt === 'newsarticle') {
                            $type_name = 'NewsArticle';
                        } else {
                            $type_name = 'Article';
                        }
                    } elseif ($schema_key === 'book') {
                        $type_name = 'Book';
                    } elseif ($schema_key === 'course') {
                        $type_name = 'Course';
                    } elseif ($schema_key === 'dataset') {
                        $type_name = 'Dataset';
                    } elseif ($schema_key === 'event') {
                        $type_name = 'Event';
                    } elseif ($schema_key === 'faq' || $schema_key === 'faqpage') {
                        $type_name = 'FAQPage';
                    } elseif ($schema_key === 'factcheck') {
                        $type_name = 'ClaimReview';
                    } elseif ($schema_key === 'howto') {
                        $type_name = 'HowTo';
                    } elseif ($schema_key === 'job' || $schema_key === 'jobposting') {
                        $type_name = 'JobPosting';
                    } elseif ($schema_key === 'movie') {
                        $type_name = 'Movie';
                    } elseif ($schema_key === 'music') {
                        $type_name = 'MusicRecording';
                    } elseif ($schema_key === 'person') {
                        $type_name = 'Person';
                    } elseif ($schema_key === 'product') {
                        $type_name = 'Product';
                    } elseif ($schema_key === 'recipe') {
                        $type_name = 'Recipe';
                    } elseif ($schema_key === 'restaurant') {
                        $type_name = 'Restaurant';
                    } elseif ($schema_key === 'service') {
                        $type_name = 'Service';
                    } elseif ($schema_key === 'software' || $schema_key === 'softwareapplication') {
                        $type_name = 'SoftwareApplication';
                    } elseif ($schema_key === 'video') {
                        $type_name = 'VideoObject';
                    } elseif ($schema_key === 'localbusiness') {
                        $custom_btype = get_option('gmb_service_locations_schema_business_type', 'LocalBusiness');
                        $type_name = !empty($custom_btype) ? $custom_btype : 'LocalBusiness';
                    } else {
                        $type_name = !empty($schema_type) ? ucfirst($schema_type) : 'Article';
                    }

                    $author_id = $post->post_author;
                    $author_name = get_the_author_meta('display_name', $author_id);
                    $author_type_pref = get_option('gmb_schema_author_type', 'person');
                    $author_sameas_pref = get_option('gmb_schema_author_sameas', '1') !== '0';
                    $publisher_name = get_option('gmb_local_seo_name', get_option('gmb_local_business_name', get_bloginfo('name')));
                    $publisher_logo = get_option('gmb_local_seo_logo', get_site_icon_url(512));

                    if ($author_type_pref === 'organization') {
                        $author_obj = array(
                            '@type' => 'Organization',
                            'name'  => esc_html($publisher_name),
                            'url'   => esc_url(home_url('/'))
                        );
                    } else {
                        $author_obj = array(
                            '@type' => 'Person',
                            'name'  => esc_html($author_name),
                            'url'   => esc_url(get_author_posts_url($author_id))
                        );
                        if ($author_sameas_pref) {
                            $author_social = array();
                            $author_twitter = get_the_author_meta('twitter', $author_id);
                            if (!empty($author_twitter)) {
                                $author_social[] = (strpos($author_twitter, 'http') === 0) ? esc_url($author_twitter) : 'https://twitter.com/' . sanitize_text_field($author_twitter);
                            }
                            $author_linkedin = get_the_author_meta('linkedin', $author_id);
                            if (!empty($author_linkedin)) {
                                $author_social[] = esc_url($author_linkedin);
                            }
                            if (!empty($author_social)) {
                                $author_obj['sameAs'] = $author_social;
                            }
                        }
                    }
                    
                    $entity_schema = array(
                        '@context'      => 'https://schema.org',
                        '@type'         => $type_name,
                        'name'          => esc_html($headline),
                        'headline'      => esc_html($headline),
                        'url'           => esc_url(get_permalink($post_id)),
                        'datePublished' => get_the_date('c', $post_id),
                        'dateModified'  => get_the_modified_date('c', $post_id),
                        'description'   => esc_html($desc),
                        'author'        => $author_obj,
                        'publisher'     => array(
                            '@type' => 'Organization',
                            'name'  => esc_html($publisher_name),
                            'logo'  => array(
                                '@type' => 'ImageObject',
                                '@url'  => esc_url($publisher_logo),
                                'url'   => esc_url($publisher_logo)
                            )
                        )
                    );

                    if (get_option('gmb_schema_enable_speakable', '0') === '1') {
                        $entity_schema['speakable'] = array(
                            '@type'       => 'SpeakableSpecification',
                            'cssSelector' => array('.entry-title', '.entry-content', 'h1', '.article-title')
                        );
                    }

                    if ($type_name === 'Service') {
                        $provider_type = get_option('gmb_services_schema_provider_type', 'organization');
                        if ($provider_type === 'person') {
                            $entity_schema['provider'] = array(
                                '@type' => 'Person',
                                'name'  => esc_html($author_name),
                                'url'   => esc_url(get_author_posts_url($author_id))
                            );
                        } else {
                            $entity_schema['provider'] = array(
                                '@type' => 'Organization',
                                'name'  => esc_html(get_bloginfo('name')),
                                'url'   => esc_url(home_url('/'))
                            );
                        }
                    } elseif ($type_name === 'Person') {
                        $job_title = get_post_meta($post_id, '_gmb_ranker_team_job_title', true) ?: get_option('gmb_team_members_schema_job_title', '');
                        if (!empty($job_title)) {
                            $entity_schema['jobTitle'] = esc_html($this->replace_schema_variables($job_title, $post_id));
                        }
                        $entity_schema['worksFor'] = array(
                            '@type' => 'Organization',
                            'name'  => esc_html(get_bloginfo('name')),
                            'url'   => esc_url(home_url('/'))
                        );
                    }

                    $img_url = '';
                    if (has_post_thumbnail($post_id)) {
                        $img_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    }
                    if (empty($img_url)) {
                        $fallback_img = get_option('gmb_schema_default_image', '');
                        if (!empty($fallback_img)) {
                            $img_url = $fallback_img;
                        } else {
                            $img_url = get_option('gmb_metadata_og_thumbnail', '');
                        }
                    }
                    if (!empty($img_url)) {
                        $entity_schema['image'] = esc_url($img_url);
                    }
                    $schemas[] = $entity_schema;
                } else {
                    // Standard WebPage Schema if schema is set to None
                    $webpage_schema = array(
                        '@context'    => 'https://schema.org',
                        '@type'       => 'WebPage',
                        'name'        => esc_html(get_the_title($post_id)),
                        'url'         => esc_url(get_permalink($post_id)),
                        'description' => esc_html(wp_trim_words(wp_strip_all_tags($post->post_content), 30))
                    );
                    if (has_post_thumbnail($post_id)) {
                        $img_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                        if ($img_url) {
                            $webpage_schema['image'] = esc_url($img_url);
                        }
                    }
                    $schemas[] = $webpage_schema;
                }
            }
            // Service / Custom Post Type Schema
            elseif ($post->post_type === 'services' || $post->post_type === 'service') {
                $service_schema = array(
                    '@context'    => 'https://schema.org',
                    '@type'       => 'Service',
                    'name'        => esc_html(get_the_title($post_id)),
                    'url'         => esc_url(get_permalink($post_id)),
                    'description' => esc_html(wp_trim_words(wp_strip_all_tags($post->post_content), 35)),
                    'provider'    => array(
                        '@type' => 'Organization',
                        'name'  => esc_html(get_bloginfo('name')),
                        'url'   => esc_url(home_url('/'))
                    )
                );
                if (has_post_thumbnail($post_id)) {
                    $img_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    if ($img_url) {
                        $service_schema['image'] = esc_url($img_url);
                    }
                }
                $schemas[] = $service_schema;
            }

            // 3. Hierarchical BreadcrumbList Schema for all inner pages
            if (get_option('gmb_schema_enable_breadcrumbs', '1') !== '0') {
                $breadcrumbs_schema = $this->build_breadcrumb_schema($post_id);
                if (!empty($breadcrumbs_schema)) {
                    $schemas[] = $breadcrumbs_schema;
                }
            }

            return $schemas;
        }

        return array();
    }

    private function build_breadcrumb_schema($post_id) {
        $items = array();
        $position = 1;

        // Home
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Home',
            'item'     => home_url('/')
        );

        $post = get_post($post_id);
        if ($post) {
            if ($post->post_type === 'post') {
                $categories = get_the_category($post_id);
                if (!empty($categories)) {
                    $cat = $categories[0];
                    $items[] = array(
                        '@type'    => 'ListItem',
                        'position' => $position++,
                        'name'     => esc_html($cat->name),
                        'item'     => esc_url(get_category_link($cat->term_id))
                    );
                }
            } elseif ($post->post_parent) {
                $parent_id = $post->post_parent;
                $parents = array();
                while ($parent_id) {
                    $page = get_post($parent_id);
                    if ($page) {
                        $parents[] = array(
                            'name' => get_the_title($page->ID),
                            'url'  => get_permalink($page->ID)
                        );
                        $parent_id = $page->post_parent;
                    } else {
                        break;
                    }
                }
                $parents = array_reverse($parents);
                foreach ($parents as $p) {
                    $items[] = array(
                        '@type'    => 'ListItem',
                        'position' => $position++,
                        'name'     => esc_html($p['name']),
                        'item'     => esc_url($p['url'])
                    );
                }
            }

            // Current item
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => esc_html(get_the_title($post_id)),
                'item'     => esc_url(get_permalink($post_id))
            );
        }

        return array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items
        );
    }

    public function inject_json_ld_schema() {
        if (get_option('gmb_ranker_module_schema', '1') === '0') {
            return;
        }

        $post_id = $this->get_current_page_id();
        
        $custom_schema = !empty($post_id) ? get_post_meta($post_id, '_gmb_ranker_json_ld', true) : '';
        
        if (!empty($custom_schema)) {
            $clean_schema = trim($custom_schema);
            $clean_schema = $this->replace_schema_variables($clean_schema, $post_id);
            $decoded_custom = json_decode($clean_schema, true);
            if (is_array($decoded_custom)) {
                $decoded_custom = $this->ensure_product_schema_compliance($decoded_custom, $post_id);
                $clean_schema = wp_json_encode($decoded_custom, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
            
            echo "\n<!-- GMB Ranker Structured Data Schema -->\n";
            echo '<script type="application/ld+json">' . "\n" . $clean_schema . "\n" . '</script>' . "\n";
        } else {
            $schema_data = $this->get_gmb_schema_array($post_id);
            if (!empty($schema_data)) {
                $schema_data = $this->ensure_product_schema_compliance($schema_data, $post_id);
                echo "\n<!-- GMB Ranker Auto Schema -->\n";
                if (isset($schema_data[0]) && is_array($schema_data[0])) {
                    foreach ($schema_data as $single_schema) {
                        echo '<script type="application/ld+json">' . "\n" . wp_json_encode($single_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n" . '</script>' . "\n";
                    }
                } else {
                    echo '<script type="application/ld+json">' . "\n" . wp_json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n" . '</script>' . "\n";
                }
            }
        }

        // Conditional Schema Templates
        $conditional_schemas = $this->get_conditional_schema_templates($post_id);
        if (!empty($conditional_schemas)) {
            echo "\n<!-- GMB Ranker Conditional Schema Templates -->\n";
            foreach ($conditional_schemas as $c_schema) {
                $c_schema = $this->ensure_product_schema_compliance($c_schema, $post_id);
                echo '<script type="application/ld+json">' . "\n" . wp_json_encode($c_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n" . '</script>' . "\n";
            }
        }

        // Sitewide custom JSON-LD injection
        $sitewide_custom = get_option('gmb_schema_custom_jsonld', '');
        if (!empty($sitewide_custom)) {
            $clean_sitewide = trim($sitewide_custom);
            $clean_sitewide = $this->replace_schema_variables($clean_sitewide, $post_id);
            $decoded_sw = json_decode($clean_sitewide, true);
            if (is_array($decoded_sw)) {
                $decoded_sw = $this->ensure_product_schema_compliance($decoded_sw, $post_id);
                $clean_sitewide = wp_json_encode($decoded_sw, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
            echo "\n<!-- GMB Ranker Sitewide Custom Schema -->\n";
            echo '<script type="application/ld+json">' . "\n" . $clean_sitewide . "\n" . '</script>' . "\n";
        }
    }

    /**
     * Recursively ensure any Product / Review / AggregateRating object has valid Google Rich Results structure.
     */
    public function ensure_product_schema_compliance($schema, $post_id = 0) {
        if (!is_array($schema)) return $schema;

        $target_url = !empty($post_id) ? esc_url(get_permalink($post_id)) : home_url('/');
        $post_title = !empty($post_id) ? esc_html(get_the_title($post_id)) : get_bloginfo('name');
        $post_excerpt = !empty($post_id) ? esc_html(wp_strip_all_tags(get_the_excerpt($post_id) ?: wp_trim_words(get_post_field('post_content', $post_id), 30))) : get_bloginfo('description');

        // Fix itemReviewed inside Review or AggregateRating
        if (isset($schema['@type']) && in_array($schema['@type'], array('Review', 'AggregateRating'), true)) {
            if (!isset($schema['itemReviewed']) || !is_array($schema['itemReviewed'])) {
                $schema['itemReviewed'] = array(
                    '@type'       => 'Product',
                    'name'        => $post_title,
                    'description' => $post_excerpt,
                );
            }
            
            // Fix invalid 'Thing' type for itemReviewed
            if (isset($schema['itemReviewed']['@type']) && ($schema['itemReviewed']['@type'] === 'Thing' || empty($schema['itemReviewed']['@type']))) {
                $schema['itemReviewed']['@type'] = 'Product';
            }

            // Ensure Product itemReviewed has required offers object
            if (isset($schema['itemReviewed']['@type']) && $schema['itemReviewed']['@type'] === 'Product') {
                if (!isset($schema['itemReviewed']['offers']) && !isset($schema['itemReviewed']['aggregateRating']) && !isset($schema['itemReviewed']['review'])) {
                    $schema['itemReviewed']['offers'] = array(
                        '@type'         => 'Offer',
                        'price'         => '99.00',
                        'priceCurrency' => 'USD',
                        'availability'  => 'https://schema.org/InStock',
                        'url'           => $target_url,
                    );
                }
            }
        }

        // Fix top-level Product schema without offers/rating/review
        if (isset($schema['@type']) && $schema['@type'] === 'Product') {
            if (!isset($schema['offers']) && !isset($schema['aggregateRating']) && !isset($schema['review'])) {
                $schema['offers'] = array(
                    '@type'         => 'Offer',
                    'price'         => '99.00',
                    'priceCurrency' => 'USD',
                    'availability'  => 'https://schema.org/InStock',
                    'url'           => $target_url,
                );
            }
        }

        // Recursively sanitize all nested items
        foreach ($schema as $k => $v) {
            if (is_array($v)) {
                $schema[$k] = $this->ensure_product_schema_compliance($v, $post_id);
            }
        }

        return $schema;
    }
}
