<?php
namespace Nera\Components\NeraAboutUsColumns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Curated Material Symbols for column icons (value = ligature).
 *
 * @return array<string, string>
 */
function scoop_about_us_columns_icon_choices(): array
{
    return [
        'groups'      => 'groups',
        'lightbulb'   => 'lightbulb',
        'favorite'    => 'favorite',
        'verified'    => 'verified',
        'diversity_3' => 'diversity_3',
        'handshake'   => 'handshake',
    ];
}

function get_acf_layout(): array
{
    $icon_choices = scoop_about_us_columns_icon_choices();

    return [
        'key'        => 'layout_NeraAboutUsColumns',
        'name'       => 'NeraAboutUsColumns',
        'label'      => __('About Us — Two Columns', 'scoop-comps'),
        'display'    => 'block',
        // Render order: left icon → title → body → right icon → title → body.
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_left_icon',
                'label'         => __('Left column icon', 'scoop-comps'),
                'name'          => 'story_left_icon',
                'type'          => 'select',
                'choices'       => $icon_choices,
                'default_value' => 'groups',
                'return_format' => 'value',
                'allow_null'    => 0,
            ],
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_left_title',
                'label'         => __('Left column title', 'scoop-comps'),
                'name'          => 'story_left_title',
                'type'          => 'text',
                'default_value' => 'Our story',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_left_content',
                'label'         => __('Left column content', 'scoop-comps'),
                'name'          => 'story_left_content',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => '',
                'instructions'  => __('Plain text. Blank lines become paragraphs.', 'scoop-comps'),
            ],
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_right_icon',
                'label'         => __('Right column icon', 'scoop-comps'),
                'name'          => 'story_right_icon',
                'type'          => 'select',
                'choices'       => $icon_choices,
                'default_value' => 'lightbulb',
                'return_format' => 'value',
                'allow_null'    => 0,
            ],
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_right_title',
                'label'         => __('Right column title', 'scoop-comps'),
                'name'          => 'story_right_title',
                'type'          => 'text',
                'default_value' => 'What drives us',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsColumns_story_right_content',
                'label'         => __('Right column content', 'scoop-comps'),
                'name'          => 'story_right_content',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => '',
                'instructions'  => __('Plain text. Blank lines become paragraphs.', 'scoop-comps'),
            ],
        ],
    ];
}
