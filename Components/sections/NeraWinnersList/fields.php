<?php
namespace Nera\Components\NeraWinnersList;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraWinnersList',
        'name'       => 'NeraWinnersList',
        'label'      => __('Winners List', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraWinnersList_empty_title',
                'label'         => __('Empty state title', 'scoop-comps'),
                'name'          => 'empty_title',
                'type'          => 'text',
                'default_value' => 'No winners to show yet',
                'instructions'  => __('Shown when this page has no winners yet.', 'scoop-comps'),
            ],
            [
                'key'           => 'field_pc_NeraWinnersList_empty_text',
                'label'         => __('Empty state text', 'scoop-comps'),
                'name'          => 'empty_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Winners appear here once competitions have ended and winners are selected in the giveaway settings.',
                'instructions'  => __('Supporting copy under the empty-state title. Leave blank to hide.', 'scoop-comps'),
            ],
        ],
    ];
}
