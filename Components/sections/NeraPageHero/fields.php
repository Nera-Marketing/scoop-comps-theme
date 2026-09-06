<?php
namespace Nera\Components\NeraPageHero;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraPageHero',
        'name'       => 'NeraPageHero',
        // Shared role label — unique in the global Add Component dropdown.
        'label'      => __('Page Hero', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraPageHero_title',
                'label'         => __('Heading', 'scoop-comps'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Contact Us',
            ],
            [
                'key'           => 'field_pc_NeraPageHero_description',
                'label'         => __('Description', 'scoop-comps'),
                'name'          => 'description',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => "We'd love to hear from you regarding the competition. Our team is ready to answer any questions.",
            ],
            [
                'key'           => 'field_pc_NeraPageHero_variant',
                'label'         => __('Variant', 'scoop-comps'),
                'name'          => 'variant',
                'type'          => 'select',
                'choices'       => [
                    'default' => __('Default', 'scoop-comps'),
                    'compact' => __('Compact', 'scoop-comps'),
                ],
                'default_value' => 'default',
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_pc_NeraPageHero_eyebrow_label',
                'label'        => __('Eyebrow label', 'scoop-comps'),
                'name'         => 'eyebrow_label',
                'type'         => 'text',
                'instructions' => __('Optional pill above the heading (legacy contact_subheading).', 'scoop-comps'),
            ],
            [
                'key'          => 'field_pc_NeraPageHero_eyebrow_icon',
                'label'        => __('Eyebrow icon', 'scoop-comps'),
                'name'         => 'eyebrow_icon',
                'type'         => 'text',
                'instructions' => __('Optional Material Symbols ligature name (e.g. mail, groups).', 'scoop-comps'),
            ],
        ],
    ];
}
