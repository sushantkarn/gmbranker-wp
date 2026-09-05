<?php
/**
 * REST Media Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Media_Controller {

    /**
     * Handle media queries and updates
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_media($request) {
        $method = $request->get_method();

        if ($method === 'GET') {
            $post_id = intval($request->get_param('post_id'));
            $args = array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post_status'    => 'inherit',
                'posts_per_page' => 50,
            );
            if ($post_id) {
                $args['post_parent'] = $post_id;
            }

            $query = new WP_Query($args);
            $media_items = array();

            foreach ($query->posts as $attachment) {
                $media_items[] = array(
                    'id'       => $attachment->ID,
                    'title'    => $attachment->post_title,
                    'alt'      => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
                    'caption'  => $attachment->post_excerpt,
                    'url'      => wp_get_attachment_url($attachment->ID),
                );
            }

            return new WP_REST_Response(array('media' => $media_items), 200);
        }

        // POST - update media alt or title
        $attachment_id = intval($request->get_param('id'));
        if (!$attachment_id) {
            return new WP_REST_Response(array('success' => false, 'message' => 'Attachment ID required'), 400);
        }

        $attachment = get_post($attachment_id);
        if (!$attachment || 'attachment' !== $attachment->post_type || (is_user_logged_in() && !current_user_can('edit_post', $attachment_id))) {
            return new WP_REST_Response(array('success' => false, 'message' => 'You are not allowed to edit this attachment.'), 403);
        }

        $alt = $request->get_param('alt');
        if (isset($alt)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
        }

        return new WP_REST_Response(array('success' => true, 'id' => $attachment_id), 200);
    }
}
