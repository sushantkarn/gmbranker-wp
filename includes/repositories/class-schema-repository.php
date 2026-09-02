<?php
/**
 * Schema Repository for GMB Ranker SEO Automation
 *
 * @package GMB_Ranker_SEO_Automation
 */

if (!defined('ABSPATH')) {
    exit;
}

class GMB_Ranker_SEO_Schema_Repository {

    const OPTION_TEMPLATES = 'gmb_schema_templates';

    /**
     * Get all custom schema templates
     *
     * @return array
     */
    public function get_all_templates() {
        $templates = get_option(self::OPTION_TEMPLATES, array());
        return is_array($templates) ? $templates : array();
    }

    /**
     * Get a specific template by ID
     *
     * @param string $template_id
     * @return array|null
     */
    public function get_template($template_id) {
        $templates = $this->get_all_templates();
        foreach ($templates as $tmpl) {
            if (isset($tmpl['id']) && $tmpl['id'] === $template_id) {
                return $tmpl;
            }
        }
        return null;
    }

    /**
     * Save all templates
     *
     * @param array $templates
     * @return bool
     */
    public function save_templates(array $templates) {
        return update_option(self::OPTION_TEMPLATES, $templates);
    }

    /**
     * Save or update a single template
     *
     * @param array $template
     * @return bool
     */
    public function save_template(array $template) {
        $templates = $this->get_all_templates();
        if (empty($template['id'])) {
            $template['id'] = 'schema_' . substr(md5(uniqid(wp_rand(), true)), 0, 8);
        }
        $updated = false;
        foreach ($templates as $index => $existing) {
            if (isset($existing['id']) && $existing['id'] === $template['id']) {
                $templates[$index] = array_merge($existing, $template);
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $templates[] = $template;
        }
        return $this->save_templates($templates);
    }

    /**
     * Delete a template by ID
     *
     * @param string $template_id
     * @return bool
     */
    public function delete_template($template_id) {
        $templates = $this->get_all_templates();
        $filtered = array();
        foreach ($templates as $tmpl) {
            if (!isset($tmpl['id']) || $tmpl['id'] !== $template_id) {
                $filtered[] = $tmpl;
            }
        }
        return $this->save_templates($filtered);
    }
}
