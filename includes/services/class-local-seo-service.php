<?php
/**
 * Local SEO Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Local_Service {

    const OPTION_LOCATIONS = 'gmb_ranker_business_locations';

    /**
     * Get all local business locations
     *
     * @return array
     */
    public function get_locations() {
        $locations = get_option(self::OPTION_LOCATIONS, array());
        return is_array($locations) ? $locations : array();
    }

    /**
     * Add or update location
     *
     * @param array $data
     * @return bool
     */
    public function save_location(array $data) {
        $locations = $this->get_locations();
        if (empty($data['id'])) {
            $data['id'] = 'loc_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
        }

        $updated = false;
        foreach ($locations as $i => $loc) {
            if (isset($loc['id']) && $loc['id'] === $data['id']) {
                $locations[$i] = array_merge($loc, $data);
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $locations[] = $data;
        }

        return update_option(self::OPTION_LOCATIONS, $locations);
    }

    /**
     * Delete location
     *
     * @param string $id
     * @return bool
     */
    public function delete_location($id) {
        $locations = $this->get_locations();
        $filtered = array();
        foreach ($locations as $loc) {
            if (!isset($loc['id']) || $loc['id'] !== $id) {
                $filtered[] = $loc;
            }
        }
        return update_option(self::OPTION_LOCATIONS, $filtered);
    }
}
