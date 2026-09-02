<?php
/**
 * Keyword Service for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Keyword_Service {

    /**
     * @var GMB_Ranker_SEO_Keyword_Repository
     */
    protected $repository;

    public function __construct(GMB_Ranker_SEO_Keyword_Repository $repository = null) {
        $this->repository = $repository ?: new GMB_Ranker_SEO_Keyword_Repository();
    }

    /**
     * Calculate keyword density for post
     *
     * @param int    $post_id
     * @param string $keyword
     * @return array [ 'count' => int, 'density' => float, 'words' => int ]
     */
    public function calculate_density($post_id, $keyword) {
        $post = get_post($post_id);
        if (!$post || empty($keyword)) {
            return array('count' => 0, 'density' => 0.0, 'words' => 0);
        }

        $content = wp_strip_all_tags($post->post_content);
        $total_words = str_word_count($content);
        if ($total_words <= 0) {
            return array('count' => 0, 'density' => 0.0, 'words' => 0);
        }

        $kw_clean = mb_strtolower(trim($keyword));
        $count = mb_substr_count(mb_strtolower($content), $kw_clean);
        $density = ($count / $total_words) * 100;

        return array(
            'count'   => $count,
            'density' => round($density, 2),
            'words'   => $total_words,
        );
    }
}
