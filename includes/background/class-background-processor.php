<?php
/**
 * Background Processor for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Background_Processor {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Background_Processor|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Background_Processor
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Execute a background task asynchronously via non-blocking HTTP request
     *
     * @param string $action
     * @param array  $data
     * @return bool
     */
    public function dispatch_async($action, array $data = array()) {
        $url = admin_url('admin-ajax.php');
        $body = array_merge($data, array(
            'action' => sanitize_key($action),
            'nonce'  => wp_create_nonce('gmb_async_task_nonce'),
        ));

        $args = array(
            'timeout'   => 0.01,
            'blocking'  => false,
            'body'      => $body,
            'cookies'   => array(),
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        );

        wp_remote_post($url, $args);
        return true;
    }
}
