<?php
/**
 * Google Places API Client for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Places_Client {

    const PLACES_BASE = 'https://maps.googleapis.com/maps/api/place';

    /**
     * Fetch place details by Place ID
     *
     * @param string $api_key
     * @param string $place_id
     * @return array|WP_Error
     */
    public function get_place_details($api_key, $place_id) {
        if (empty($api_key) || empty($place_id)) {
            return new WP_Error('missing_params', 'Google Places API key and place ID are required.');
        }

        $url = add_query_arg(array(
            'place_id' => urlencode($place_id),
            'key'      => urlencode($api_key),
            'fields'   => 'name,formatted_address,geometry,rating,user_ratings_total,opening_hours,formatted_phone_number,website',
        ), self::PLACES_BASE . '/details/json');

        $response = wp_remote_get($url, array('timeout' => 15));
        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : new WP_Error('invalid_places_response', 'Malformed response from Places API.');
    }
}
