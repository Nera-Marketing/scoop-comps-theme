<?php
namespace Nera\Components\NeraAboutUsNarrative;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   narrative: string,
 *   i18n: array{narrative_label: string, narrative_placeholder: string},
 * }
 */
function get_data(array $args = []): array
{
    return [
        'narrative' => wp_kses_post(nera_component_field($args, 'narrative', 'about_narrative', '')),
        'i18n'      => [
            'narrative_label'       => __('Our narrative', 'nera-competitions'),
            'narrative_placeholder' => __('More about us is coming soon…', 'nera-competitions'),
        ],
    ];
}
