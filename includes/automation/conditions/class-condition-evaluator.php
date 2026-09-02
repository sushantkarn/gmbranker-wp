<?php
/**
 * Condition Evaluator for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Condition_Evaluator implements GMB_Ranker_SEO_Condition_Interface {

    /**
     * Evaluate rule against context
     *
     * @param array $context
     * @param array $rule
     * @return bool
     */
    public function evaluate(array $context, array $rule) {
        if (empty($rule['field'])) {
            return true;
        }

        $field = $rule['field'];
        $operator = isset($rule['operator']) ? $rule['operator'] : '==';
        $target_value = isset($rule['value']) ? $rule['value'] : null;

        $actual_value = isset($context[$field]) ? $context[$field] : null;

        switch ($operator) {
            case '==':
            case 'equals':
                return ($actual_value == $target_value);

            case '!=':
            case 'not_equals':
                return ($actual_value != $target_value);

            case '>':
                return (floatval($actual_value) > floatval($target_value));

            case '<':
                return (floatval($actual_value) < floatval($target_value));

            case 'contains':
                return is_string($actual_value) && stripos($actual_value, (string) $target_value) !== false;

            case 'in':
                return is_array($target_value) && in_array($actual_value, $target_value);

            default:
                return true;
        }
    }
}
