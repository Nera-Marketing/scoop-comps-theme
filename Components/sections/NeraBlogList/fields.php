<?php
namespace Nera\Components\NeraBlogList;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraBlogList',
        'name'       => 'NeraBlogList',
        'label'      => __('Blog List', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraBlogList_featured_post',
                'label'         => __('Featured post', 'scoop-comps'),
                'name'          => 'featured_post',
                'type'          => 'post_object',
                'post_type'     => ['post'],
                'return_format' => 'id',
                'allow_null'    => 1,
                'instructions'  => __(
                    'Lead article at the top of the list. Leave empty to use the most recent post. Excluded from the grid below so it does not appear twice.',
                    'scoop-comps'
                ),
            ],
            [
                'key'           => 'field_pc_NeraBlogList_empty_title',
                'label'         => __('Empty state heading', 'scoop-comps'),
                'name'          => 'empty_title',
                'type'          => 'text',
                'default_value' => 'No posts found',
            ],
            [
                'key'           => 'field_pc_NeraBlogList_empty_text',
                'label'         => __('Empty state text', 'scoop-comps'),
                'name'          => 'empty_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => "It seems we can't find what you're looking for.",
            ],
        ],
    ];
}
