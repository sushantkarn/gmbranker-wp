<?php
/**
 * Condition Evaluator for GMB Ranker SEO Automation
 *
 * Implements GMB_Ranker_SEO_Condition_Interface as a fail-closed, strongly-typed
 * condition evaluation boundary for automation workflows.
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Condition_Evaluator implements GMB_Ranker_SEO_Condition_Interface {

    /**
     * Sensitive field keys that cannot be queried by automation rules
     *
     * @var array
     */
    protected static $sensitive_keys = array(
        'api_key',
        'access_token',
        'refresh_token',
        'password',
        'secret',
        'authorization',
        'private_key',
        'bearer',
    );

    /**
     * Resolve field value from context safely (supports dot-notation up to 3 levels)
     *
     * @param array  $context
     * @param string $field_path
     * @return mixed|null
     */
    protected function resolve_field_value(array $context, $field_path) {
        if (empty($field_path) || !is_string($field_path)) {
            return null;
        }

        $parts = explode('.', trim($field_path));
        if (count($parts) > 3) {
            return null; // Bound nested depth
        }

        $current = $context;
        foreach ($parts as $part) {
            $key = strtolower(trim($part));

            // Prevent inspection of sensitive credentials
            if (in_array($key, self::$sensitive_keys, true)) {
                return null;
            }

            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } elseif (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Evaluate rule against context with fail-closed security semantics
     *
     * @param array $context Automation execution context
     * @param array $rule    Condition rule definition
     * @return bool
     */
    public function evaluate(array $context, array $rule) {
        // Fail closed on empty rule or missing field
        if (empty($rule) || empty($rule['field']) || !is_string($rule['field'])) {
            return false;
        }

        $field_path   = trim($rule['field']);
        $operator     = !empty($rule['operator']) ? strtolower(trim($rule['operator'])) : '==';
        $target_value = isset($rule['value']) ? $rule['value'] : null;

        $actual_value = $this->resolve_field_value($context, $field_path);

        switch ($operator) {
            case '==':
            case 'equals':
            case 'eq':
                if (is_numeric($actual_value) && is_numeric($target_value)) {
                    return floatval($actual_value) == floatval($target_value);
                }
                return (string)$actual_value === (string)$target_value;

            case '!=':
            case 'not_equals':
            case 'neq':
                if (is_numeric($actual_value) && is_numeric($target_value)) {
                    return floatval($actual_value) != floatval($target_value);
                }
                return (string)$actual_value !== (string)$target_value;

            case '>':
            case 'greater_than':
            case 'gt':
                return is_numeric($actual_value) && is_numeric($target_value) && floatval($actual_value) > floatval($target_value);

            case '>=':
            case 'greater_or_equal':
            case 'gte':
                return is_numeric($actual_value) && is_numeric($target_value) && floatval($actual_value) >= floatval($target_value);

            case '<':
            case 'less_than':
            case 'lt':
                return is_numeric($actual_value) && is_numeric($target_value) && floatval($actual_value) < floatval($target_value);

            case '<=':
            case 'less_or_equal':
            case 'lte':
                return is_numeric($actual_value) && is_numeric($target_value) && floatval($actual_value) <= floatval($target_value);

            case 'contains':
                if (is_string($actual_value)) {
                    return stripos($actual_value, (string)$target_value) !== false;
                }
                if (is_array($actual_value)) {
                    return in_array($target_value, $actual_value, false);
                }
                return false;

            case 'not_contains':
                if (is_string($actual_value)) {
                    return stripos($actual_value, (string)$target_value) === false;
                }
                if (is_array($actual_value)) {
                    return !in_array($target_value, $actual_value, false);
                }
                return true;

            case 'in':
                return is_array($target_value) && in_array($actual_value, $target_value, false);

            case 'not_in':
                return is_array($target_value) && !in_array($actual_value, $target_value, false);

            case 'is_empty':
            case 'empty':
                return empty($actual_value);

            case 'is_not_empty':
            case 'not_empty':
                return !empty($actual_value);

            default:
                // FAIL CLOSED: Unknown operator returns false!
                return false;
        }
    }
}
