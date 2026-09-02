<?php
if (!defined('ABSPATH')) exit;

class GMB_Ranker_SEO_Local {
    public function __construct() {
        add_action('wp_head', array($this, 'inject_local_business_schema'), 3);
    }

    private function is_rank_math_local_seo_active() {
        return class_exists('RankMath\Helper') && \RankMath\Helper::is_module_active('local-seo');
    }

    public function inject_local_business_schema() {
        if ($this->is_rank_math_local_seo_active()) {
            return;
        }

        $about_page = get_option('gmb_local_seo_about_page', '');
        $contact_page = get_option('gmb_local_seo_contact_page', '');
        $current_post_id = get_the_ID();

        $should_render = is_front_page() || 
                         (!empty($about_page) && intval($about_page) === $current_post_id) || 
                         (!empty($contact_page) && intval($contact_page) === $current_post_id);

        if (!$should_render) {
            return;
        }

        $use_multiple = get_option('gmb_local_use_multiple_locations', '0');
        $schemas = array();

        if ($use_multiple === '1') {
            $locations = get_option('gmb_local_business_locations', array());
            foreach ($locations as $loc) {
                if (empty($loc['name'])) {
                    continue;
                }
                $item = array(
                    '@context' => 'https://schema.org',
                    '@type'    => 'LocalBusiness',
                    'name'     => esc_html($loc['name']),
                    'telephone'=> esc_html($loc['phone']),
                    'address'  => array(
                        '@type'           => 'PostalAddress',
                        'streetAddress'   => esc_html($loc['address'])
                    ),
                    'url'      => esc_url(home_url())
                );
                
                if (!empty($loc['lat']) && !empty($loc['lng'])) {
                    $item['geo'] = array(
                        '@type'     => 'GeoCoordinates',
                        'latitude'  => floatval($loc['lat']),
                        'longitude' => floatval($loc['lng'])
                    );
                }
                
                if (!empty($loc['hours']) && is_array($loc['hours'])) {
                    $hours_spec = array();
                    foreach ($loc['hours'] as $day_hours) {
                        if (empty($day_hours['day']) || empty($day_hours['opens']) || empty($day_hours['closes'])) {
                            continue;
                        }
                        $hours_spec[] = array(
                            '@type'     => 'OpeningHoursSpecification',
                            'dayOfWeek' => esc_html($day_hours['day']),
                            'opens'     => esc_html($day_hours['opens']),
                            'closes'    => esc_html($day_hours['closes'])
                        );
                    }
                    if (!empty($hours_spec)) {
                        $item['openingHoursSpecification'] = $hours_spec;
                    }
                }
                
                $schemas[] = $item;
            }
        } else {
            $local_type = get_option('gmb_local_seo_type', 'organization');
            $local_subtype = get_option('gmb_local_seo_business_subtype', 'LocalBusiness');
            $local_web_name = get_option('gmb_local_seo_website_name', get_bloginfo('name'));
            $local_web_alt = get_option('gmb_local_seo_website_alternate_name', '');
            $local_seo_name = get_option('gmb_local_seo_name', get_option('gmb_local_business_name', get_bloginfo('name')));
            $local_logo = get_option('gmb_local_seo_logo', get_site_icon_url(512));
            $local_url = get_option('gmb_local_seo_url', home_url());
            $local_email = get_option('gmb_local_seo_email', get_bloginfo('admin_email'));
            $local_phone = get_option('gmb_local_seo_phone', get_option('gmb_local_business_phone', ''));

            $addr_street = get_option('gmb_local_seo_address_street', get_option('gmb_local_business_address', ''));
            $addr_locality = get_option('gmb_local_seo_address_locality', '');
            $addr_region = get_option('gmb_local_seo_address_region', '');
            $addr_postal = get_option('gmb_local_seo_address_postal', '');
            $addr_country = get_option('gmb_local_seo_address_country', '');

            $type_mapping = ($local_type === 'person') ? 'Person' : (!empty($local_subtype) ? $local_subtype : 'LocalBusiness');

            if (!empty($local_seo_name)) {
                $schema = array(
                    '@context' => 'https://schema.org',
                    '@type'    => $type_mapping,
                    'name'     => esc_html($local_seo_name),
                    'url'      => esc_url($local_url)
                );

                if (!empty($local_web_name)) {
                    $schema['websiteName'] = esc_html($local_web_name);
                }
                if (!empty($local_web_alt)) {
                    $schema['alternateName'] = esc_html($local_web_alt);
                }
                if (!empty($local_logo)) {
                    $schema['logo'] = esc_url($local_logo);
                    $schema['image'] = esc_url($local_logo);
                }
                if (!empty($local_email)) {
                    $schema['email'] = esc_html($local_email);
                }
                if (!empty($local_phone)) {
                    $schema['telephone'] = esc_html($local_phone);
                }

                $geo_lat = get_option('gmb_local_business_lat', '');
                $geo_lng = get_option('gmb_local_business_lng', '');
                if (!empty($geo_lat) && !empty($geo_lng)) {
                    $schema['geo'] = array(
                        '@type'     => 'GeoCoordinates',
                        'latitude'  => floatval($geo_lat),
                        'longitude' => floatval($geo_lng)
                    );
                }

                $maps_url = get_option('gmb_local_business_maps_url', '');
                if (!empty($maps_url)) {
                    $schema['hasMap'] = esc_url($maps_url);
                }

                $price_range = get_option('gmb_local_business_price_range', '');
                if (!empty($price_range)) {
                    $schema['priceRange'] = esc_html($price_range);
                }

                $currencies = get_option('gmb_local_business_currencies', '');
                if (!empty($currencies)) {
                    $schema['currenciesAccepted'] = esc_html($currencies);
                }

                $opening_hours_text = get_option('gmb_local_business_opening_hours', '');
                if (!empty($opening_hours_text)) {
                    $schema['openingHours'] = esc_html($opening_hours_text);
                }

                $opening_hours = get_option('gmb_local_business_hours', array());
                if (!empty($opening_hours) && is_array($opening_hours)) {
                    $hours_spec = array();
                    foreach ($opening_hours as $day_hours) {
                        if (empty($day_hours['day']) || empty($day_hours['opens']) || empty($day_hours['closes'])) {
                            continue;
                        }
                        $hours_spec[] = array(
                            '@type'     => 'OpeningHoursSpecification',
                            'dayOfWeek' => esc_html($day_hours['day']),
                            'opens'     => esc_html($day_hours['opens']),
                            'closes'    => esc_html($day_hours['closes'])
                        );
                    }
                    if (!empty($hours_spec)) {
                        $schema['openingHoursSpecification'] = $hours_spec;
                    }
                }

                // Granular Address
                if (!empty($addr_street) || !empty($addr_locality) || !empty($addr_region) || !empty($addr_postal) || !empty($addr_country)) {
                    $address_schema = array(
                        '@type' => 'PostalAddress'
                    );
                    if (!empty($addr_street)) {
                        $address_schema['streetAddress'] = esc_html($addr_street);
                    }
                    if (!empty($addr_locality)) {
                        $address_schema['addressLocality'] = esc_html($addr_locality);
                    }
                    if (!empty($addr_region)) {
                        $address_schema['addressRegion'] = esc_html($addr_region);
                    }
                    if (!empty($addr_postal)) {
                        $address_schema['postalCode'] = esc_html($addr_postal);
                    }
                    if (!empty($addr_country)) {
                        $address_schema['addressCountry'] = esc_html($addr_country);
                    }
                    $schema['address'] = $address_schema;
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
                    $schema['sameAs'] = array_values(array_unique($same_as));
                }

                $schemas[] = $schema;
            }
        }

        if (!empty($schemas)) {
            echo "\n<!-- GMB Ranker Local Business Schema -->\n";
            echo '<script type="application/ld+json">' . wp_json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
}
