<?php
namespace Nera\Components\NeraAboutUsColumns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plain text → safe HTML paragraphs. Strips any accidental HTML first.
 */
function scoop_about_us_columns_plain_to_html(string $text): string
{
    $text = trim(wp_strip_all_tags($text));
    if ($text === '') {
        return '';
    }
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    return wpautop(esc_html($text));
}

/**
 * @param mixed $value
 */
function scoop_about_us_columns_sanitize_icon($value, string $default): string
{
    $icon = is_string($value) ? trim($value) : '';
    $allowed = scoop_about_us_columns_icon_choices();
    if ($icon !== '' && isset($allowed[$icon])) {
        return $icon;
    }
    return $default;
}

/**
 * @param array $args
 * @return array{
 *   story_left_icon: string,
 *   story_left_title: string,
 *   story_left_content: string,
 *   story_right_icon: string,
 *   story_right_title: string,
 *   story_right_content: string,
 *   i18n: array{story_left_placeholder: string, story_right_placeholder: string},
 * }
 */
function get_data(array $args = []): array
{
    $left_raw  = (string) nera_component_field($args, 'story_left_content', 'about_story_left_content', '');
    $right_raw = (string) nera_component_field($args, 'story_right_content', 'about_story_right_content', '');

    return [
        'story_left_icon'     => scoop_about_us_columns_sanitize_icon(
            nera_component_field($args, 'story_left_icon', '', 'groups'),
            'groups'
        ),
        'story_left_title'    => nera_component_field($args, 'story_left_title', 'about_story_left_title', __('Our story', 'nera-competitions')),
        'story_left_content'  => scoop_about_us_columns_plain_to_html($left_raw),
        'story_right_icon'    => scoop_about_us_columns_sanitize_icon(
            nera_component_field($args, 'story_right_icon', '', 'lightbulb'),
            'lightbulb'
        ),
        'story_right_title'   => nera_component_field($args, 'story_right_title', 'about_story_right_title', __('What drives us', 'nera-competitions')),
        'story_right_content' => scoop_about_us_columns_plain_to_html($right_raw),
        'i18n'                => [
            'story_left_placeholder'  => __('We will share more about our journey here.', 'nera-competitions'),
            'story_right_placeholder' => __('Insights and values will appear here.', 'nera-competitions'),
        ],
    ];
}
