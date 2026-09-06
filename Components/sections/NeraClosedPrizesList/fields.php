<?php
namespace Nera\Components\NeraClosedPrizesList;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraClosedPrizesList',
        'name'       => 'NeraClosedPrizesList',
        'label'      => __('Closed Prizes List', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraClosedPrizesList_empty_title',
                'label'         => __('Empty state heading', 'scoop-comps'),
                'name'          => 'empty_title',
                'type'          => 'text',
                'default_value' => 'No closed prizes yet',
            ],
            [
                'key'           => 'field_pc_NeraClosedPrizesList_empty_text',
                'label'         => __('Empty state text', 'scoop-comps'),
                'name'          => 'empty_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Check back after our competitions have drawn their winners.',
            ],
        ],
    ];
}
