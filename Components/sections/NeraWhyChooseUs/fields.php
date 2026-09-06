<?php
namespace Nera\Components\NeraWhyChooseUs;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraWhyChooseUs',
        'name'       => 'NeraWhyChooseUs',
        'label'      => __('Why Choose Us', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraWhyChooseUs_title',
                'label'         => __('Heading', 'scoop-comps'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Why Choose Us',
            ],
            [
                'key'           => 'field_pc_NeraWhyChooseUs_subtitle',
                'label'         => __('Subtitle', 'scoop-comps'),
                'name'          => 'subtitle',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Join thousands of happy winners who trust us for fair and exciting competitions.',
            ],
            [
                'key'          => 'field_pc_NeraWhyChooseUs_items',
                'label'        => __('Items', 'scoop-comps'),
                'name'         => 'items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => __('Add item', 'scoop-comps'),
                'sub_fields'   => [
                    [
                        'key'          => 'field_pc_NeraWhyChooseUs_item_icon',
                        'label'        => __('Icon (SVG)', 'scoop-comps'),
                        'name'         => 'icon',
                        'type'         => 'textarea',
                        'rows'         => 4,
                        'instructions' => __('Paste SVG markup for the icon.', 'scoop-comps'),
                    ],
                    [
                        'key'   => 'field_pc_NeraWhyChooseUs_item_title',
                        'label' => __('Title', 'scoop-comps'),
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_NeraWhyChooseUs_item_description',
                        'label' => __('Description', 'scoop-comps'),
                        'name'  => 'description',
                        'type'  => 'textarea',
                        'rows'  => 2,
                    ],
                ],
            ],
        ],
    ];
}
