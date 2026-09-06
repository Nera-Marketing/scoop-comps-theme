<?php
namespace Nera\Components\NeraWinnersList;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Query / ACF-driven winners grid — empty-state chrome only is CMS-editable.
 *
 * Detects the page template and renders the matching parent template-part so
 * Alpine AJAX Load More and Entry List modal behaviour stay intact.
 *
 * @param array $args
 * @return array{
 *   empty_title: string,
 *   empty_text: string,
 *   grid_html: string,
 * }
 */
function get_data(array $args = []): array
{
    $page_id = (int) get_queried_object_id();
    if ($page_id <= 0) {
        $page_id = (int) get_the_ID();
    }

    $template = $page_id > 0 ? (string) get_page_template_slug($page_id) : '';

    $is_manual = $template === 'page-templates/winners-template.php';
    $is_entry  = $template === 'page-templates/winners-entry-list-template.php';
    // Dynamic + entry-list share the dynamic grid; unknown templates default to dynamic.
    $use_manual = $is_manual;

    $empty_title_default = $use_manual
        ? 'No Winners Yet'
        : 'No winners to show yet';
    $empty_text_default = $use_manual
        ? 'Check back soon to see our lucky winners!'
        : 'Winners appear here once competitions have ended and winners are selected in the giveaway settings.';

    $legacy_title_key = $use_manual ? 'winners_empty_heading' : 'winners_dynamic_empty_heading';
    $legacy_text_key  = $use_manual ? 'winners_empty_description' : 'winners_dynamic_empty_description';

    $empty_title = (string) nera_component_field($args, 'empty_title', $legacy_title_key, $empty_title_default);
    $empty_text  = (string) nera_component_field($args, 'empty_text', $legacy_text_key, $empty_text_default);

    // Parent grids still read legacy ACF keys — bridge CMS values for this render.
    $title_filter = static function ($value) use ($empty_title) {
        return $empty_title;
    };
    $text_filter = static function ($value) use ($empty_text) {
        return $empty_text;
    };

    add_filter('acf/load_value/name=winners_empty_heading', $title_filter, 99);
    add_filter('acf/load_value/name=winners_empty_description', $text_filter, 99);
    add_filter('acf/load_value/name=winners_dynamic_empty_heading', $title_filter, 99);
    add_filter('acf/load_value/name=winners_dynamic_empty_description', $text_filter, 99);

    ob_start();
    if ($use_manual) {
        get_template_part('template-parts/winners/winners-grid');
    } else {
        $grid_args = [];
        if ($is_entry) {
            $grid_args = [
                'show_participants_cta'    => true,
                'include_entry_list_modal' => true,
                'stack_layout'             => true,
            ];
        }
        get_template_part('template-parts/winners-dynamic/winners-grid', null, $grid_args);
    }
    $grid_html = (string) ob_get_clean();

    remove_filter('acf/load_value/name=winners_empty_heading', $title_filter, 99);
    remove_filter('acf/load_value/name=winners_empty_description', $text_filter, 99);
    remove_filter('acf/load_value/name=winners_dynamic_empty_heading', $title_filter, 99);
    remove_filter('acf/load_value/name=winners_dynamic_empty_description', $text_filter, 99);

    return [
        'empty_title' => $empty_title,
        'empty_text'  => $empty_text,
        'grid_html'   => $grid_html,
    ];
}
