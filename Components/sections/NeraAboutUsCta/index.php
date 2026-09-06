<?php
namespace Nera\Components\NeraAboutUsCta;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   cta_heading: string,
 *   cta_description: string,
 *   cta_primary_text: string,
 *   cta_primary_url: string,
 *   cta_secondary_text: string,
 *   cta_secondary_url: string,
 * }
 */
function get_data(array $args = []): array
{
    return [
        'cta_heading'        => nera_component_field($args, 'cta_heading', 'about_cta_heading', __('Join the community', 'nera-competitions')),
        'cta_description'    => nera_component_field(
            $args,
            'cta_description',
            'about_cta_description',
            __('Be part of a transparent, supportive journey where everyone has a chance to win.', 'nera-competitions')
        ),
        'cta_primary_text'   => nera_component_field($args, 'cta_primary_text', 'about_cta_primary_btn_text', __('Explore competitions', 'nera-competitions')),
        'cta_primary_url'    => nera_component_field($args, 'cta_primary_url', 'about_cta_primary_btn_url', home_url('/shop/')),
        'cta_secondary_text' => nera_component_field($args, 'cta_secondary_text', 'about_cta_secondary_btn_text', __('Get in touch', 'nera-competitions')),
        'cta_secondary_url'  => nera_component_field($args, 'cta_secondary_url', 'about_cta_secondary_btn_url', home_url('/contact/')),
    ];
}
