<?php
/**
 * Post Published Trigger for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Post_Published_Trigger implements GMB_Ranker_SEO_Trigger_Interface {

    public function get_id() {
        return 'post_published';
    }

    public function get_name() {
        return 'Post / Page Published or Updated';
    }

    public function register_listener(callable $dispatcher) {
        add_action('transition_post_status', function($new_status, $old_status, $post) use ($dispatcher) {
            if ($new_status === 'publish' && !wp_is_post_revision($post) && !wp_is_post_autosave($post)) {
                call_user_func($dispatcher, 'post_published', array(
                    'post_id'    => $post->ID,
                    'post_type'  => $post->post_type,
                    'is_new'     => ($old_status !== 'publish'),
                    'post_title' => $post->post_title,
                ));
            }
        }, 10, 3);
    }
}
