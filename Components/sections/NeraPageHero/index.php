<?php
namespace Nera\Components\NeraPageHero;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared inner-page banner (page-hero.php family) — not HomepageHero.
 *
 * @param array $args
 * @return array{
 *   title: string,
 *   description: string,
 *   variant: string,
 *   eyebrow_label: string,
 *   eyebrow_icon: string,
 * }
 */
function get_data(array $args = []): array
{
    $title_default = 'Contact Us';
    if (function_exists('get_queried_object_id')) {
        $qid = (int) get_queried_object_id();
        if ($qid > 0) {
            $from_post = get_the_title($qid);
            if ($from_post !== '') {
                $title_default = $from_post;
            }
        }
    }

    $variant = (string) nera_component_field($args, 'variant', 'variant', 'default');
    if ($variant !== 'compact') {
        $variant = 'default';
    }

    return [
        'title'         => nera_component_field($args, 'title', 'contact_heading', $title_default),
        'description'   => nera_component_field(
            $args,
            'description',
            'contact_description',
            "We'd love to hear from you regarding the competition. Our team is ready to answer any questions."
        ),
        'variant'       => $variant,
        'eyebrow_label' => nera_component_field($args, 'eyebrow_label', 'contact_subheading', ''),
        'eyebrow_icon'  => nera_component_field($args, 'eyebrow_icon', 'eyebrow_icon', ''),
    ];
}
