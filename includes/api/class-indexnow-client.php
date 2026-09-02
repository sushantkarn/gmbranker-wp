<?php
/**
 * IndexNow & Google Indexing Client for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_IndexNow_Client {

    const INDEXNOW_API = 'https://api.indexnow.org/indexnow';

    /**
     * Submit single or multiple URLs to IndexNow
     *
     * @param string $host
     * @param string $key
     * @param array  $urls
     * @return array|WP_Error
     */
    public function submit_to_indexnow($host, $key, array $urls) {
        $payload = array(
            'host'        => $host,
            'key'         => $key,
            'keyLocation' => home_url('/' . $key . '.txt'),
            'urlList'     => array_values($urls),
        );

        $response = wp_remote_post(self::INDEXNOW_API, array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'    => wp_json_encode($payload),
            'timeout' => 20,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        return array(
            'success'     => ($code === 200 || $code === 202),
            'status_code' => $code,
            'body'        => wp_remote_retrieve_body($response),
        );
    }
}
