<?php
/**
 * Hide parent AboutUsPage from the Page Components "Add Component" dropdown.
 *
 * Admin only — removing a layout definition on the front end would break
 * rendering of any page that still has an AboutUsPage row saved.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('acf/load_field/name=page_components', function ($field) {
    if (!is_admin()) {
        return $field;
    }
    if (empty($field['layouts']) || !is_array($field['layouts'])) {
        return $field;
    }
    $field['layouts'] = array_values(array_filter(
        $field['layouts'],
        static fn ($layout) => ($layout['name'] ?? '') !== 'AboutUsPage'
    ));
    return $field;
});
