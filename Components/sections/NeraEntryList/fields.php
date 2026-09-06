<?php
namespace Nera\Components\NeraEntryList;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraEntryList',
        'name'       => 'NeraEntryList',
        'label'      => __('Entry List', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraEntryList_empty_title',
                'label'         => __('Empty state heading', 'scoop-comps'),
                'name'          => 'empty_title',
                'type'          => 'text',
                'default_value' => 'No competitions found',
            ],
            [
                'key'           => 'field_pc_NeraEntryList_empty_text',
                'label'         => __('Empty state text', 'scoop-comps'),
                'name'          => 'empty_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'There are no participant lists available yet. Please check back soon.',
            ],
        ],
    ];
}
