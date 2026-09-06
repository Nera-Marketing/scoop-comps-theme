<?php
namespace Nera\Components\NeraAboutUsHero;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   hero_eyebrow: string,
 *   title: string,
 *   hero_tagline: string,
 *   hero_image_url: string,
 *   hero_image_alt: string,
 *   i18n: array{image_placeholder: string},
 * }
 */
function get_data(array $args = []): array
{
    $hero_image = nera_component_field($args, 'hero_image', 'about_hero_image', null);

    $url = '';
    $alt = '';
    if (is_array($hero_image)) {
        $url = (string) ($hero_image['url'] ?? '');
        $alt = (string) ($hero_image['alt'] ?? '');
    } elseif (is_numeric($hero_image) && (int) $hero_image > 0) {
        $id  = (int) $hero_image;
        $src = wp_get_attachment_image_url($id, 'large');
        $url = $src ? (string) $src : '';
        $alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
    } elseif (is_string($hero_image) && $hero_image !== '') {
        $url = $hero_image;
    }

    $title_default = '';
    if (function_exists('get_queried_object_id')) {
        $qid = (int) get_queried_object_id();
        if ($qid > 0) {
            $title_default = get_the_title($qid);
        }
    }
    if ($title_default === '') {
        $title_default = get_the_title();
    }

    return [
        'hero_eyebrow'   => nera_component_field($args, 'hero_eyebrow', 'about_hero_eyebrow', __('About us', 'nera-competitions')),
        'title'          => nera_component_field($args, 'title', 'about_title', $title_default),
        'hero_tagline'   => nera_component_field(
            $args,
            'hero_tagline',
            'about_hero_tagline',
            __('Building a community rooted in transparency, trust, and exciting opportunities for everyone.', 'nera-competitions')
        ),
        'hero_image_url' => $url,
        'hero_image_alt' => $alt,
        'i18n'           => [
            'image_placeholder' => __('Image placeholder', 'nera-competitions'),
        ],
    ];
}
