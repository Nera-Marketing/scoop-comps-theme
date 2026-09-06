<?php
namespace Nera\Components\NeraCompetitionsList;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product listing / shop competitions grid (advanced filter + cards).
 * Query-driven; only empty-state copy is editable.
 *
 * @param array $args
 * @return array{empty_title: string, empty_text: string, html: string}
 */
function get_data(array $args = []): array
{
    $empty_title = (string) nera_component_field(
        $args,
        'empty_title',
        'product_listing_empty_heading',
        __('No competitions found', 'nera-competitions')
    );
    $empty_text = (string) nera_component_field(
        $args,
        'empty_text',
        'product_listing_empty_description',
        __('Check back soon for new amazing prizes!', 'nera-competitions')
    );

    $heading_filter = static function ($value) use ($empty_title) {
        return $empty_title;
    };
    $text_filter = static function ($value) use ($empty_text) {
        return $empty_text;
    };

    add_filter('acf/load_value/name=product_listing_empty_heading', $heading_filter, 99);
    add_filter('acf/load_value/name=product_listing_empty_description', $text_filter, 99);

    ob_start();
    get_template_part('template-parts/homepage/categories-filter');
    $html = (string) ob_get_clean();

    remove_filter('acf/load_value/name=product_listing_empty_heading', $heading_filter, 99);
    remove_filter('acf/load_value/name=product_listing_empty_description', $text_filter, 99);

    return [
        'empty_title' => $empty_title,
        'empty_text'  => $empty_text,
        'html'        => $html,
    ];
}
