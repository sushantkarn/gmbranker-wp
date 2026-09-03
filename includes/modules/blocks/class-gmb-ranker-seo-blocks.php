<?php
/**
 * GMB Ranker SEO — Lightweight Gutenberg Schema Blocks (FAQ & HowTo)
 *
 * Provides Server-Side Rendered (SSR) FAQ and HowTo blocks
 * with automatic JSON-LD Schema generation and zero React bloat.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Blocks {

    /**
     * Singleton instance
     *
     * @var GMB_Ranker_SEO_Blocks|null
     */
    private static $instance = null;

    /**
     * Stored FAQ items for footer JSON-LD injection
     *
     * @var array
     */
    private $faq_items = array();

    /**
     * Stored HowTo items for footer JSON-LD injection
     *
     * @var array
     */
    private $howto_items = array();

    /**
     * Get singleton instance
     *
     * @return GMB_Ranker_SEO_Blocks
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('wp_footer', array($this, 'render_block_schema_jsonld'), 99);
    }

    /**
     * Register Gutenberg Blocks
     */
    public function register_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register FAQ Block
        register_block_type('gmb-ranker/faq', array(
            'attributes' => array(
                'items' => array(
                    'type'    => 'array',
                    'default' => array(),
                ),
                'title' => array(
                    'type'    => 'string',
                    'default' => 'Frequently Asked Questions',
                ),
            ),
            'render_callback' => array($this, 'render_faq_block'),
        ));

        // Register HowTo Block
        register_block_type('gmb-ranker/howto', array(
            'attributes' => array(
                'title'       => array('type' => 'string', 'default' => ''),
                'description' => array('type' => 'string', 'default' => ''),
                'steps'       => array('type' => 'array', 'default' => array()),
            ),
            'render_callback' => array($this, 'render_howto_block'),
        ));
    }

    /**
     * Render FAQ Block HTML
     *
     * @param array $attributes
     * @return string
     */
    public function render_faq_block($attributes = array()) {
        $items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : array();
        $title = isset($attributes['title']) ? sanitize_text_field($attributes['title']) : 'Frequently Asked Questions';

        if (empty($items)) {
            return '';
        }

        $this->faq_items = array_merge($this->faq_items, $items);

        $html = '<div class="gmb-faq-block" itemscope itemtype="https://schema.org/FAQPage">';
        if (!empty($title)) {
            $html .= '<h3 class="gmb-faq-title">' . esc_html($title) . '</h3>';
        }
        $html .= '<div class="gmb-faq-items">';

        foreach ($items as $item) {
            $q = isset($item['question']) ? sanitize_text_field($item['question']) : '';
            $a = isset($item['answer']) ? wp_kses_post($item['answer']) : '';

            if (empty($q) || empty($a)) {
                continue;
            }

            $html .= '<div class="gmb-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
            $html .= '<h4 class="gmb-faq-question" itemprop="name">' . esc_html($q) . '</h4>';
            $html .= '<div class="gmb-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
            $html .= '<div itemprop="text">' . $a . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Render HowTo Block HTML
     *
     * @param array $attributes
     * @return string
     */
    public function render_howto_block($attributes = array()) {
        $title = isset($attributes['title']) ? sanitize_text_field($attributes['title']) : '';
        $desc  = isset($attributes['description']) ? sanitize_text_field($attributes['description']) : '';
        $steps = isset($attributes['steps']) && is_array($attributes['steps']) ? $attributes['steps'] : array();

        if (empty($steps)) {
            return '';
        }

        $this->howto_items[] = array(
            'title'       => $title,
            'description' => $desc,
            'steps'       => $steps,
        );

        $html = '<div class="gmb-howto-block" itemscope itemtype="https://schema.org/HowTo">';
        if (!empty($title)) {
            $html .= '<h3 class="gmb-howto-title" itemprop="name">' . esc_html($title) . '</h3>';
        }
        if (!empty($desc)) {
            $html .= '<p class="gmb-howto-desc" itemprop="description">' . esc_html($desc) . '</p>';
        }

        $html .= '<ol class="gmb-howto-steps">';
        foreach ($steps as $index => $step) {
            $step_title = isset($step['title']) ? sanitize_text_field($step['title']) : 'Step ' . ($index + 1);
            $step_text  = isset($step['text']) ? wp_kses_post($step['text']) : '';

            $html .= '<li class="gmb-howto-step" itemscope itemprop="step" itemtype="https://schema.org/HowToStep">';
            $html .= '<h4 class="gmb-howto-step-title" itemprop="name">' . esc_html($step_title) . '</h4>';
            $html .= '<div class="gmb-howto-step-text" itemprop="text">' . $step_text . '</div>';
            $html .= '</li>';
        }
        $html .= '</ol>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render JSON-LD schema for active blocks in footer
     */
    public function render_block_schema_jsonld() {
        if (empty($this->faq_items) && empty($this->howto_items)) {
            return;
        }

        $schemas = array();

        // FAQ Schema
        if (!empty($this->faq_items)) {
            $faq_schema = array(
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => array(),
            );

            foreach ($this->faq_items as $item) {
                if (!empty($item['question']) && !empty($item['answer'])) {
                    $faq_schema['mainEntity'][] = array(
                        '@type'          => 'Question',
                        'name'           => $item['question'],
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => wp_strip_all_tags($item['answer']),
                        ),
                    );
                }
            }

            if (!empty($faq_schema['mainEntity'])) {
                $schemas[] = $faq_schema;
            }
        }

        // HowTo Schema
        if (!empty($this->howto_items)) {
            foreach ($this->howto_items as $ht) {
                $ht_schema = array(
                    '@context'    => 'https://schema.org',
                    '@type'       => 'HowTo',
                    'name'        => $ht['title'],
                    'description' => $ht['description'],
                    'step'        => array(),
                );

                foreach ($ht['steps'] as $idx => $step) {
                    $ht_schema['step'][] = array(
                        '@type' => 'HowToStep',
                        'name'  => isset($step['title']) ? $step['title'] : 'Step ' . ($idx + 1),
                        'text'  => isset($step['text']) ? wp_strip_all_tags($step['text']) : '',
                    );
                }

                $schemas[] = $ht_schema;
            }
        }

        if (!empty($schemas)) {
            echo '<script type="application/ld+json">' . wp_json_encode($schemas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
}
