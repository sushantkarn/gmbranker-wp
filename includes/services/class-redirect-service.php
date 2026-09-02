<?php
/**
 * Redirect Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Redirect_Service {

    /**
     * @var GMB_Ranker_SEO_Redirect_Repository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param GMB_Ranker_SEO_Redirect_Repository|null $repository
     */
    public function __construct(GMB_Ranker_SEO_Redirect_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Redirect_Repository();
    }

    /**
     * Check if incoming requested URI matches any redirect rule
     *
     * @param string $request_uri
     * @return array|null [ 'url' => string, 'code' => int ]
     */
    public function match_redirect($request_uri) {
        $rules = $this->repository->get_all_rules();
        if (empty($rules)) {
            return null;
        }

        $clean_uri = trim($request_uri, '/');

        foreach ($rules as $rule) {
            if (empty($rule['enabled']) || empty($rule['source'])) {
                continue;
            }

            $source = trim($rule['source'], '/');
            $type = isset($rule['type']) ? $rule['type'] : 'exact';

            $matched = false;
            if ($type === 'exact') {
                $matched = (strcasecmp($clean_uri, $source) === 0);
            } elseif ($type === 'contains') {
                $matched = (stripos($clean_uri, $source) !== false);
            } elseif ($type === 'regex') {
                $pattern = '/' . str_replace('/', '\/', $source) . '/i';
                $matched = (bool) @preg_match($pattern, $clean_uri);
            }

            if ($matched && !empty($rule['target'])) {
                $code = !empty($rule['code']) ? intval($rule['code']) : 301;
                return array(
                    'url'  => $rule['target'],
                    'code' => in_array($code, array(301, 302, 307, 410, 451), true) ? $code : 301,
                );
            }
        }

        return null;
    }

    /**
     * Record a 404 access log entry
     *
     * @param string $uri
     * @param string $referrer
     * @param string $user_agent
     * @return bool
     */
    public function log_404($uri, $referrer = '', $user_agent = '') {
        $logs = $this->repository->get_404_logs();
        $uri = sanitize_text_field($uri);

        $found = false;
        foreach ($logs as $key => $log) {
            if (isset($log['uri']) && $log['uri'] === $uri) {
                $logs[$key]['hits'] = intval($log['hits']) + 1;
                $logs[$key]['last_accessed'] = current_time('mysql');
                $found = true;
                break;
            }
        }

        if (!$found) {
            $logs[] = array(
                'id'            => 'log_' . substr(md5($uri . microtime()), 0, 8),
                'uri'           => $uri,
                'hits'          => 1,
                'referrer'      => sanitize_text_field($referrer),
                'user_agent'    => sanitize_text_field($user_agent),
                'first_accessed'=> current_time('mysql'),
                'last_accessed' => current_time('mysql'),
            );
        }

        return $this->repository->save_404_logs($logs);
    }

    /**
     * Bulk import redirect rules from raw text
     * Format: /source /target [301]
     *
     * @param string $text
     * @return int Count of imported rules
     */
    public function import_from_text($text) {
        $lines = explode("\n", str_replace("\r", "", $text));
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $source = sanitize_text_field($parts[0]);
                $target = sanitize_text_field($parts[1]);
                $code   = (isset($parts[2]) && in_array(intval($parts[2]), array(301, 302, 307, 410))) ? intval($parts[2]) : 301;

                $this->repository->save_rule(array(
                    'source'  => $source,
                    'target'  => $target,
                    'code'    => $code,
                    'type'    => 'exact',
                    'enabled' => 1,
                ));
                $count++;
            }
        }

        return $count;
    }
}
