<?php
namespace Nera\Components\NeraClosedPrizesList;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Closed prizes grid (query + AJAX Load More). Empty-state copy only is editable.
 *
 * @param array $args
 * @return array{empty_title: string, empty_text: string, html: string}
 */
function get_data(array $args = []): array
{
    $empty_title = (string) nera_component_field(
        $args,
        'empty_title',
        'closed_prizes_empty_heading',
        __('No closed prizes yet', 'nera-competitions')
    );
    $empty_text = (string) nera_component_field(
        $args,
        'empty_text',
        'closed_prizes_empty_description',
        __('Check back after our competitions have drawn their winners.', 'nera-competitions')
    );

    $heading_filter = static function ($value) use ($empty_title) {
        return $empty_title;
    };
    $text_filter = static function ($value) use ($empty_text) {
        return $empty_text;
    };

    add_filter('acf/load_value/name=closed_prizes_empty_heading', $heading_filter, 99);
    add_filter('acf/load_value/name=closed_prizes_empty_description', $text_filter, 99);

    ob_start();
    get_template_part('template-parts/closed-prizes/prizes-grid');
    $html = (string) ob_get_clean();

    remove_filter('acf/load_value/name=closed_prizes_empty_heading', $heading_filter, 99);
    remove_filter('acf/load_value/name=closed_prizes_empty_description', $text_filter, 99);

    return [
        'empty_title' => $empty_title,
        'empty_text'  => $empty_text,
        'html'        => $html,
    ];
}
