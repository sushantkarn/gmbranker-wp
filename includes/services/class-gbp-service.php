<?php
/**
 * Google Business Profile Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Gbp_Service {

    /**
     * @var GMB_Ranker_SEO_GBP_Client
     */
    protected $client;

    public function __construct(GMB_Ranker_SEO_GBP_Client $client = null) {
        $this->client = $client ?: new GMB_Ranker_SEO_GBP_Client();
    }

    /**
     * Format GBP location data into Schema.org LocalBusiness JSON-LD structure
     *
     * @param array $location
     * @return array
     */
    public function format_location_schema(array $location) {
        $type = !empty($location['type']) ? $location['type'] : 'LocalBusiness';
        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => $type,
            'name'     => isset($location['name']) ? $location['name'] : get_bloginfo('name'),
        );

        if (!empty($location['address'])) {
            $schema['address'] = array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => isset($location['address']['street']) ? $location['address']['street'] : '',
                'addressLocality' => isset($location['address']['city']) ? $location['address']['city'] : '',
                'addressRegion'   => isset($location['address']['state']) ? $location['address']['state'] : '',
                'postalCode'      => isset($location['address']['zip']) ? $location['address']['zip'] : '',
                'addressCountry'  => isset($location['address']['country']) ? $location['address']['country'] : 'NP',
            );
        }

        if (!empty($location['phone'])) {
            $schema['telephone'] = $location['phone'];
        }
        if (!empty($location['geo']['lat']) && !empty($location['geo']['lng'])) {
            $schema['geo'] = array(
                '@type'     => 'GeoCoordinates',
                'latitude'  => $location['geo']['lat'],
                'longitude' => $location['geo']['lng'],
            );
        }

        return $schema;
    }
}
