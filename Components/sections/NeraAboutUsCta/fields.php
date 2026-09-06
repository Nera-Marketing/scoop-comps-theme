<?php
namespace Nera\Components\NeraAboutUsCta;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraAboutUsCta',
        'name'       => 'NeraAboutUsCta',
        'label'      => __('About Us — Call to Action', 'scoop-comps'),
        'display'    => 'block',
        // Heading style applies to Hero title only (parity with AboutUsPage twig).
        'sub_fields' => [
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_heading',
                'label'         => __('Heading', 'scoop-comps'),
                'name'          => 'cta_heading',
                'type'          => 'text',
                'default_value' => 'Join the community',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_description',
                'label'         => __('Description', 'scoop-comps'),
                'name'          => 'cta_description',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Be part of a transparent, supportive journey where everyone has a chance to win.',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_primary_text',
                'label'         => __('Primary button label', 'scoop-comps'),
                'name'          => 'cta_primary_text',
                'type'          => 'text',
                'default_value' => 'Explore competitions',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_primary_url',
                'label'         => __('Primary button URL', 'scoop-comps'),
                'name'          => 'cta_primary_url',
                'type'          => 'url',
                'placeholder'   => '/shop/',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_secondary_text',
                'label'         => __('Secondary button label', 'scoop-comps'),
                'name'          => 'cta_secondary_text',
                'type'          => 'text',
                'default_value' => 'Get in touch',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsCta_cta_secondary_url',
                'label'         => __('Secondary button URL', 'scoop-comps'),
                'name'          => 'cta_secondary_url',
                'type'          => 'url',
                'placeholder'   => '/contact/',
            ],
        ],
    ];
}
