<?php
/**
 * REST Redirects Controller for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_REST_Redirects_Controller {

    /**
     * @var GMB_Ranker_SEO_Redirect_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Redirect_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Redirect_Repository();
    }

    /**
     * Handle redirects REST endpoint
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_redirects($request) {
        $method = $request->get_method();

        if ($method === 'GET') {
            return new WP_REST_Response(array(
                'rules' => $this->repository->get_all_rules(),
            ), 200);
        }

        // POST - Add or update rule
        $source = $request->get_param('source');
        $target = $request->get_param('target');
        $code   = intval($request->get_param('code')) ?: 301;

        if (empty($source) || empty($target)) {
            return new WP_REST_Response(array('success' => false, 'message' => 'Source and target required'), 400);
        }

        $rule = array(
            'id'      => 'rule_' . substr(md5(uniqid(wp_rand(), true)), 0, 8),
            'source'  => sanitize_text_field($source),
            'target'  => sanitize_text_field($target),
            'code'    => $code,
            'enabled' => 1,
            'type'    => 'exact',
        );

        $this->repository->save_rule($rule);

        return new WP_REST_Response(array(
            'success' => true,
            'rule'    => $rule,
        ), 200);
    }
}
