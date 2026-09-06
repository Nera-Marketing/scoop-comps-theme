<?php
namespace Nera\Components\NeraAboutUsHero;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraAboutUsHero',
        'name'       => 'NeraAboutUsHero',
        // Page-prefixed on purpose: the Add Component dropdown is global, so a bare
        // "Hero" is ambiguous next to the homepage's HomepageHero (also "Hero") and
        // "How It Works — Hero". Matches the parent's em-dash convention.
        'label'      => __('About Us — Hero', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_NeraAboutUsHero_hero_eyebrow',
                'label'         => __('Eyebrow label', 'scoop-comps'),
                'name'          => 'hero_eyebrow',
                'type'          => 'text',
                'default_value' => 'About us',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsHero_title',
                'label'         => __('Heading', 'scoop-comps'),
                'name'          => 'title',
                'type'          => 'text',
                'instructions'  => __('Defaults to the page title if empty.', 'scoop-comps'),
            ],
            [
                'key'           => 'field_pc_NeraAboutUsHero_hero_tagline',
                'label'         => __('Tagline', 'scoop-comps'),
                'name'          => 'hero_tagline',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Building a community rooted in transparency, trust, and exciting opportunities for everyone.',
            ],
            [
                'key'           => 'field_pc_NeraAboutUsHero_hero_image',
                'label'         => __('Hero image', 'scoop-comps'),
                'name'          => 'hero_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
            ],
        ], 'NeraAboutUsHero'),
    ];
}
