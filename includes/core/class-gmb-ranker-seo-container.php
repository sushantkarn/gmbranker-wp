<?php
/**
 * Lightweight Service & Dependency Container for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Container {

    /**
     * Singleton container instance
     *
     * @var GMB_Ranker_SEO_Container|null
     */
    private static $instance = null;

    /**
     * Bindings registry (closures / factories)
     *
     * @var array<string, callable>
     */
    private $bindings = array();

    /**
     * Shared singleton instances
     *
     * @var array<string, object>
     */
    private $instances = array();

    /**
     * Get singleton container instance
     *
     * @return GMB_Ranker_SEO_Container
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a binding factory
     *
     * @param string $key
     * @param callable $resolver
     * @param bool $singleton
     */
    public function bind($key, $resolver, $singleton = false) {
        $this->bindings[$key] = array(
            'resolver'  => $resolver,
            'singleton' => (bool) $singleton,
        );
    }

    /**
     * Register a singleton instance
     *
     * @param string $key
     * @param object $instance
     */
    public function set($key, $instance) {
        $this->instances[$key] = $instance;
    }

    /**
     * Resolve a service from the container
     *
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        // Return existing shared instance if available
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        // Check if binding factory exists
        if (isset($this->bindings[$key])) {
            $binding = $this->bindings[$key];
            $object = call_user_func($binding['resolver'], $this);

            if ($binding['singleton']) {
                $this->instances[$key] = $object;
            }

            return $object;
        }

        // Auto-instantiate if class exists
        if (class_exists($key)) {
            $object = new $key();
            return $object;
        }

        return null;
    }

    /**
     * Check if a service is bound or instantiated
     *
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return isset($this->instances[$key]) || isset($this->bindings[$key]) || class_exists($key);
    }
}
