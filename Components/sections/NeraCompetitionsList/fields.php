<?php
namespace Nera\Components\NeraCompetitionsList;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraCompetitionsList',
        'name'       => 'NeraCompetitionsList',
        'label'      => __('Competitions List', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraCompetitionsList_empty_title',
                'label'         => __('Empty state heading', 'scoop-comps'),
                'name'          => 'empty_title',
                'type'          => 'text',
                'default_value' => 'No competitions found',
            ],
            [
                'key'           => 'field_pc_NeraCompetitionsList_empty_text',
                'label'         => __('Empty state text', 'scoop-comps'),
                'name'          => 'empty_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Check back soon for new amazing prizes!',
            ],
        ],
    ];
}
