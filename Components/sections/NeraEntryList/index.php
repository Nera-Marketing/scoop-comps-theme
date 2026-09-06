<?php
namespace Nera\Components\NeraEntryList;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Entry-list competitions grid (query + AJAX + participants modal).
 * Empty-state copy only is editable.
 *
 * @param array $args
 * @return array{empty_title: string, empty_text: string, html: string}
 */
function get_data(array $args = []): array
{
    $empty_title = (string) nera_component_field(
        $args,
        'empty_title',
        'entry_list_empty_heading',
        __('No competitions found', 'nera-competitions')
    );
    $empty_text = (string) nera_component_field(
        $args,
        'empty_text',
        'entry_list_empty_description',
        __('There are no participant lists available yet. Please check back soon.', 'nera-competitions')
    );

    $heading_filter = static function ($value) use ($empty_title) {
        return $empty_title;
    };
    $text_filter = static function ($value) use ($empty_text) {
        return $empty_text;
    };

    add_filter('acf/load_value/name=entry_list_empty_heading', $heading_filter, 99);
    add_filter('acf/load_value/name=entry_list_empty_description', $text_filter, 99);

    ob_start();
    get_template_part('template-parts/entry-list/listing-grid');
    $html = (string) ob_get_clean();

    remove_filter('acf/load_value/name=entry_list_empty_heading', $heading_filter, 99);
    remove_filter('acf/load_value/name=entry_list_empty_description', $text_filter, 99);

    return [
        'empty_title' => $empty_title,
        'empty_text'  => $empty_text,
        'html'        => $html,
    ];
}
