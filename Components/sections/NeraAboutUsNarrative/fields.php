<?php
namespace Nera\Components\NeraAboutUsNarrative;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraAboutUsNarrative',
        'name'       => 'NeraAboutUsNarrative',
        'label'      => __('About Us — Narrative', 'scoop-comps'),
        'display'    => 'block',
        // Heading style applies to Hero title only (parity with AboutUsPage twig).
        'sub_fields' => [
            [
                'key'          => 'field_pc_NeraAboutUsNarrative_narrative',
                'label'        => __('Main content', 'scoop-comps'),
                'name'         => 'narrative',
                'type'         => 'wysiwyg',
                'tabs'         => 'all',
                'toolbar'      => 'full',
                'media_upload' => 1,
            ],
        ],
    ];
}
