<?php
/**
 * REST Automation Controller for GMB Ranker SEO Automation
 *
 * Exposes webhooks and endpoints for external triggers (Zapier, n8n, Make, Cloud Engine).
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Automation_Controller {

    /**
     * @var GMB_Ranker_SEO_Automation_Manager
     */
    protected $manager;

    public function __construct(GMB_Ranker_SEO_Automation_Manager $manager = null) {
        $this->manager = $manager ?: GMB_Ranker_SEO_Automation_Manager::get_instance();
    }

    /**
     * Handle manual or webhook trigger dispatch
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_trigger($request) {
        $trigger_id = $request->get_param('trigger') ?: 'manual';
        $context    = $request->get_param('context') ?: array();
        if (!is_array($context)) {
            $context = array();
        }

        $this->manager->handle_trigger_event($trigger_id, $context);

        return new WP_REST_Response(array(
            'success'   => true,
            'message'   => 'Trigger ' . esc_html($trigger_id) . ' dispatched successfully.',
            'timestamp' => time(),
        ), 200);
    }
}
