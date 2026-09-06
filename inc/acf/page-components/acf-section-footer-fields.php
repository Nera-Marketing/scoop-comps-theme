<?php
/**
 * Injected CMS fields for parent layouts that hard-code copy (footers + body chrome).
 *
 * Child same-name index.php/fields.php are skipped, so we inject sub_fields onto
 * those layouts and feed Twig via nera_component_data_* + child template.twig.
 *
 * Footers: Testimonials / Faq / QuickGuide / CategoriesCompetitions.
 * Body chrome (editability audit): HomepageHero badge + last-winner label; About
 * floating stat; CategoriesCompetitions eyebrow / filter / empty; FeaturedCompetitions
 * zero-product placeholders.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve a published page permalink by path slug, else fallback.
 */
function scoop_permalink_by_slug(string $slug, string $fallback = '#'): string
{
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        $url = get_permalink($page);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }
    return $fallback;
}

/**
 * Default injected values (also used by the homepage migrator).
 *
 * @return array<string, array<string, string>>
 */
function scoop_section_footer_defaults(): array
{
    $browse_url = scoop_permalink_by_slug(
        'all-competitions',
        scoop_permalink_by_slug('shop', home_url('/all-competitions/'))
    );

    return [
        'HomepageHero' => [
            'badge_text'         => '🏆 Premium Giveaways',
            'last_winner_label'  => 'Last Winner:',
        ],
        'About' => [
            'stat_number'        => '150',
            'stat_label'         => 'Happy Winners',
            'image_placeholder'  => 'Image placeholder',
        ],
        'FeaturedCompetitions' => [
            'empty_title' => 'Coming Soon',
            'empty_text'  => 'Coming soon',
        ],
        'QuickGuide' => [
            'cta_text' => 'Browse Competitions',
            'cta_url'  => $browse_url,
        ],
        'CategoriesCompetitions' => [
            'eyebrow'              => 'Browse by Category',
            'all_categories_label' => 'All Categories',
            'empty_title'          => 'No competitions found',
            'empty_text'           => 'Check back soon for new amazing prizes!',
            'cta_text'             => 'View All Competitions',
            'cta_url'              => $browse_url,
        ],
        'Testimonials' => [
            'won_label'        => 'Won:',
            'footer_link_text' => 'Read more winner stories',
            'footer_link_url'  => scoop_permalink_by_slug('winners-page', '#winners'),
        ],
        'Faq' => [
            'footer_prefix'    => 'Still have questions?',
            'footer_link_text' => 'Contact our support team',
            'footer_link_url'  => scoop_permalink_by_slug(
                'contact',
                scoop_permalink_by_slug('contact-us', '#')
            ),
        ],
    ];
}

/**
 * Read a string from the flexible row, else default.
 *
 * @param array  $row
 * @param mixed  $default
 */
function scoop_section_footer_row_string(array $row, string $key, string $default): string
{
    if (!array_key_exists($key, $row)) {
        return $default;
    }
    $value = $row[$key];
    if ($value === null || $value === '') {
        return $default;
    }
    return (string) $value;
}

/**
 * Sub-fields to inject per parent layout name, with render-order splice anchors.
 *
 * Each item: placement `before`|`after` a named sub_field. Do not dump as a block
 * before Styles — badge_text must sit above title, etc.
 *
 * @return array<string, list<array{placement: string, anchor: string, field: array}>>
 */
function scoop_section_footer_injected_fields(): array
{
    return [
        'HomepageHero' => [
            [
                'placement' => 'before',
                'anchor'    => 'title',
                'field'     => [
                    'key'           => 'field_scoop_pc_hero_badge_text',
                    'label'         => __('Badge Text', 'scoop-competitions-child'),
                    'name'          => 'badge_text',
                    'type'          => 'text',
                    'instructions'  => __('Hero pill label. Include any emoji (e.g. 🏆 Premium Giveaways).', 'scoop-competitions-child'),
                    'default_value' => '🏆 Premium Giveaways',
                ],
            ],
            [
                'placement' => 'before',
                'anchor'    => 'winner_name',
                'field'     => [
                    'key'           => 'field_scoop_pc_hero_last_winner_label',
                    'label'         => __('Last Winner Label', 'scoop-competitions-child'),
                    'name'          => 'last_winner_label',
                    'type'          => 'text',
                    'instructions'  => __('Label above the winner name on the hero image chip.', 'scoop-competitions-child'),
                    'default_value' => 'Last Winner:',
                ],
            ],
        ],
        'About' => [
            [
                'placement' => 'before',
                'anchor'    => 'image_position',
                'field'     => [
                    'key'           => 'field_scoop_pc_about_image_placeholder',
                    'label'         => __('Image Placeholder Text', 'scoop-competitions-child'),
                    'name'          => 'image_placeholder',
                    'type'          => 'text',
                    'instructions'  => __('Shown when no About image is set.', 'scoop-competitions-child'),
                    'default_value' => 'Image placeholder',
                ],
            ],
            [
                'placement' => 'before',
                'anchor'    => 'image_position',
                'field'     => [
                    'key'           => 'field_scoop_pc_about_stat_number',
                    'label'         => __('Floating Stat Number', 'scoop-competitions-child'),
                    'name'          => 'stat_number',
                    'type'          => 'text',
                    'instructions'  => __('Number on the floating badge (the “+” stays in the design).', 'scoop-competitions-child'),
                    'default_value' => '150',
                ],
            ],
            [
                'placement' => 'before',
                'anchor'    => 'image_position',
                'field'     => [
                    'key'           => 'field_scoop_pc_about_stat_label',
                    'label'         => __('Floating Stat Label', 'scoop-competitions-child'),
                    'name'          => 'stat_label',
                    'type'          => 'text',
                    'default_value' => 'Happy Winners',
                ],
            ],
        ],
        'FeaturedCompetitions' => [
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_featured_empty_title',
                    'label'         => __('Empty State Title', 'scoop-competitions-child'),
                    'name'          => 'empty_title',
                    'type'          => 'text',
                    'instructions'  => __('Placeholder card title when no competitions are live.', 'scoop-competitions-child'),
                    'default_value' => 'Coming Soon',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_featured_empty_text',
                    'label'         => __('Empty State Text', 'scoop-competitions-child'),
                    'name'          => 'empty_text',
                    'type'          => 'text',
                    'instructions'  => __('Small status line on placeholder cards.', 'scoop-competitions-child'),
                    'default_value' => 'Coming soon',
                ],
            ],
        ],
        'QuickGuide' => [
            [
                'placement' => 'after',
                'anchor'    => 'steps',
                'field'     => [
                    'key'           => 'field_scoop_pc_guide_cta_text',
                    'label'         => __('CTA Label', 'scoop-competitions-child'),
                    'name'          => 'cta_text',
                    'type'          => 'text',
                    'instructions'  => __('Section footer CTA button label.', 'scoop-competitions-child'),
                    'default_value' => 'Browse Competitions',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'steps',
                'field'     => [
                    'key'           => 'field_scoop_pc_guide_cta_url',
                    'label'         => __('CTA URL', 'scoop-competitions-child'),
                    'name'          => 'cta_url',
                    'type'          => 'text',
                    'instructions'  => __('Section footer CTA button URL.', 'scoop-competitions-child'),
                    'default_value' => '/all-competitions/',
                ],
            ],
        ],
        'CategoriesCompetitions' => [
            [
                'placement' => 'before',
                'anchor'    => 'title',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_eyebrow',
                    'label'         => __('Eyebrow', 'scoop-competitions-child'),
                    'name'          => 'eyebrow',
                    'type'          => 'text',
                    'default_value' => 'Browse by Category',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_all_label',
                    'label'         => __('All Categories Label', 'scoop-competitions-child'),
                    'name'          => 'all_categories_label',
                    'type'          => 'text',
                    'default_value' => 'All Categories',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_empty_title',
                    'label'         => __('Empty State Title', 'scoop-competitions-child'),
                    'name'          => 'empty_title',
                    'type'          => 'text',
                    'default_value' => 'No competitions found',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_empty_text',
                    'label'         => __('Empty State Text', 'scoop-competitions-child'),
                    'name'          => 'empty_text',
                    'type'          => 'text',
                    'default_value' => 'Check back soon for new amazing prizes!',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_cta_text',
                    'label'         => __('CTA Label', 'scoop-competitions-child'),
                    'name'          => 'cta_text',
                    'type'          => 'text',
                    'instructions'  => __('Section footer CTA button label.', 'scoop-competitions-child'),
                    'default_value' => 'View All Competitions',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'subtitle',
                'field'     => [
                    'key'           => 'field_scoop_pc_categories_cta_url',
                    'label'         => __('CTA URL', 'scoop-competitions-child'),
                    'name'          => 'cta_url',
                    'type'          => 'text',
                    'instructions'  => __('Section footer CTA button URL.', 'scoop-competitions-child'),
                    'default_value' => '/all-competitions/',
                ],
            ],
        ],
        'Testimonials' => [
            [
                'placement' => 'before',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_testimonials_won_label',
                    'label'         => __('Won Label', 'scoop-competitions-child'),
                    'name'          => 'won_label',
                    'type'          => 'text',
                    'instructions'  => __('Prefix before each story’s prize (e.g. “Won:”).', 'scoop-competitions-child'),
                    'default_value' => 'Won:',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_testimonials_footer_link_text',
                    'label'         => __('Footer Link Label', 'scoop-competitions-child'),
                    'name'          => 'footer_link_text',
                    'type'          => 'text',
                    'instructions'  => __('Section footer link label under the stories.', 'scoop-competitions-child'),
                    'default_value' => 'Read more winner stories',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_testimonials_footer_link_url',
                    'label'         => __('Footer Link URL', 'scoop-competitions-child'),
                    'name'          => 'footer_link_url',
                    'type'          => 'text',
                    'instructions'  => __('Section footer link URL.', 'scoop-competitions-child'),
                    'default_value' => '/winners-page/',
                ],
            ],
        ],
        'Faq' => [
            [
                'placement' => 'after',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_faq_footer_prefix',
                    'label'         => __('Footer Prefix', 'scoop-competitions-child'),
                    'name'          => 'footer_prefix',
                    'type'          => 'text',
                    'instructions'  => __('Text before the FAQ footer link (e.g. “Still have questions?”).', 'scoop-competitions-child'),
                    'default_value' => 'Still have questions?',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_faq_footer_link_text',
                    'label'         => __('Footer Link Label', 'scoop-competitions-child'),
                    'name'          => 'footer_link_text',
                    'type'          => 'text',
                    'instructions'  => __('FAQ section footer link label.', 'scoop-competitions-child'),
                    'default_value' => 'Contact our support team',
                ],
            ],
            [
                'placement' => 'after',
                'anchor'    => 'list',
                'field'     => [
                    'key'           => 'field_scoop_pc_faq_footer_link_url',
                    'label'         => __('Footer Link URL', 'scoop-competitions-child'),
                    'name'          => 'footer_link_url',
                    'type'          => 'text',
                    'instructions'  => __('FAQ section footer link URL.', 'scoop-competitions-child'),
                    'default_value' => '/contact/',
                ],
            ],
        ],
    ];
}

/**
 * Index of the Styles tab, or null.
 *
 * @param list<array> $sub_fields
 */
function scoop_section_footer_styles_index(array $sub_fields): ?int
{
    foreach ($sub_fields as $i => $f) {
        if (!is_array($f)) {
            continue;
        }
        if (($f['type'] ?? '') === 'tab' && (($f['name'] ?? '') === 'tab_styles' || ($f['label'] ?? '') === 'Styles')) {
            return (int) $i;
        }
    }
    return null;
}

/**
 * Splice one field before/after a named anchor. Fallback: before Styles (or append).
 *
 * @param list<array> $sub_fields
 * @param array       $new_field
 * @return list<array>
 */
function scoop_section_footer_splice_one(array $sub_fields, array $new_field, string $anchor, string $placement): array
{
    $inject_names = [];
    foreach (scoop_section_footer_injected_fields() as $items) {
        foreach ($items as $item) {
            $inject_names[(string) ($item['field']['name'] ?? '')] = true;
        }
    }

    if ($placement === 'after') {
        $anchor_i = null;
        foreach ($sub_fields as $i => $sf) {
            if (is_array($sf) && ($sf['name'] ?? '') === $anchor) {
                $anchor_i = (int) $i;
                break;
            }
        }
        if ($anchor_i !== null) {
            $insert_at = $anchor_i + 1;
            // Keep sequential "after same anchor" inserts in declaration order.
            while (
                isset($sub_fields[$insert_at])
                && is_array($sub_fields[$insert_at])
                && isset($sub_fields[$insert_at]['name'])
                && isset($inject_names[(string) $sub_fields[$insert_at]['name']])
            ) {
                $insert_at++;
            }
            array_splice($sub_fields, $insert_at, 0, [$new_field]);
            return $sub_fields;
        }
    }

    if ($placement === 'before') {
        $out    = [];
        $placed = false;
        foreach ($sub_fields as $sf) {
            if (!$placed && is_array($sf) && ($sf['name'] ?? '') === $anchor) {
                $out[]  = $new_field;
                $placed = true;
            }
            $out[] = $sf;
        }
        if ($placed) {
            return $out;
        }
    }

    // Missing anchor (parent upgrade): insert before Styles, else append.
    $styles_at = scoop_section_footer_styles_index($sub_fields);
    if ($styles_at !== null) {
        array_splice($sub_fields, $styles_at, 0, [$new_field]);
        error_log(
            sprintf(
                "scoop: anchor '%s' missing for '%s' — placed before Styles",
                $anchor,
                (string) ($new_field['name'] ?? '')
            )
        );
        return $sub_fields;
    }
    error_log(
        sprintf(
            "scoop: anchor '%s' missing for '%s' — appended",
            $anchor,
            (string) ($new_field['name'] ?? '')
        )
    );
    $sub_fields[] = $new_field;
    return $sub_fields;
}

/**
 * Merge all injected fields for one layout using per-field splice anchors.
 *
 * @param list<array>                                              $sub_fields
 * @param list<array{placement: string, anchor: string, field: array}> $items
 * @return list<array>
 */
function scoop_section_footer_merge_sub_fields(array $sub_fields, array $items): array
{
    $existing_names = [];
    foreach ($sub_fields as $f) {
        if (is_array($f) && isset($f['name'])) {
            $existing_names[(string) $f['name']] = true;
        }
    }

    foreach ($items as $item) {
        if (!is_array($item) || empty($item['field']) || !is_array($item['field'])) {
            continue;
        }
        $f    = $item['field'];
        $name = (string) ($f['name'] ?? '');
        if ($name === '' || isset($existing_names[$name])) {
            continue;
        }
        $placement = (($item['placement'] ?? 'before') === 'after') ? 'after' : 'before';
        $anchor    = (string) ($item['anchor'] ?? '');
        if ($anchor === '') {
            continue;
        }
        $sub_fields = scoop_section_footer_splice_one($sub_fields, $f, $anchor, $placement);
        $existing_names[$name] = true;
    }

    return $sub_fields;
}

/**
 * Inject prize_url into the Testimonials list repeater (after prize).
 *
 * Nested walk — dedupe on the repeater's own sub_fields, not the layout's.
 *
 * @param list<array> $sub_fields
 * @param string      $layout_key ACF layout key for parent_layout on the new field
 * @return list<array>
 */
function scoop_testimonials_inject_prize_url(array $sub_fields, string $layout_key = ''): array
{
    foreach ($sub_fields as &$sf) {
        if (($sf['name'] ?? '') !== 'list' || empty($sf['sub_fields']) || !is_array($sf['sub_fields'])) {
            continue;
        }
        $child_names = array_column($sf['sub_fields'], 'name');
        if (in_array('prize_url', $child_names, true)) {
            break;
        }
        $prize_url = [
            'key'          => 'field_scoop_pc_testimonials_prize_url',
            'label'        => __('Prize URL', 'scoop-competitions-child'),
            'name'         => 'prize_url',
            'type'         => 'url',
            'instructions' => __('Optional link for the prize label (competition / product page).', 'scoop-competitions-child'),
        ];
        if ($layout_key !== '') {
            $prize_url['parent_layout'] = $layout_key;
        }
        if (!empty($sf['key'])) {
            $prize_url['parent'] = $sf['key'];
        }
        if (function_exists('acf_get_valid_field')) {
            $prize_url = acf_get_valid_field($prize_url);
        }
        $out = [];
        foreach ($sf['sub_fields'] as $child) {
            $out[] = $child;
            if (($child['name'] ?? '') === 'prize') {
                $out[] = $prize_url;
            }
        }
        $sf['sub_fields'] = $out;
        break;
    }
    unset($sf);
    return $sub_fields;
}

add_filter('acf/load_field/name=page_components', function ($field) {
    if (empty($field['layouts']) || !is_array($field['layouts'])) {
        return $field;
    }
    $inject = scoop_section_footer_injected_fields();
    $labels = [
        'HomepageHero'           => 'Homepage — Hero',
        'Credibility'            => 'Homepage — Credibility / Trust Bar',
        'Stats'                  => 'Homepage — Stats',
        'FeaturedCompetitions'   => 'Homepage — Ending Soon',
        'PromoBanner'            => 'Homepage — Follow Us on Socials',
        'Testimonials'           => 'Homepage — Testimonials',
        'QuickGuide'             => 'Homepage — How to Play',
        'About'                  => 'Homepage — About / Who We Are',
        'CategoriesCompetitions' => 'Homepage — Categories',
        'Faq'                    => 'Homepage — FAQ',
    ];
    foreach ($field['layouts'] as $layout_key => $layout) {
        if (!is_array($layout)) {
            continue;
        }
        $name = (string) ($layout['name'] ?? '');
        if ($name === '') {
            continue;
        }
        if (isset($labels[$name])) {
            $field['layouts'][$layout_key]['label'] = $labels[$name];
        }
        $sub = isset($layout['sub_fields']) && is_array($layout['sub_fields']) ? $layout['sub_fields'] : [];
        if (!empty($inject[$name])) {
            // acf_get_valid_field sets `_name` (= original name). Without it, flexible
            // format_value writes into $row[''] and the CMS keys never surface in get_field.
            $items = [];
            foreach ($inject[$name] as $item) {
                if (!is_array($item) || empty($item['field']) || !is_array($item['field'])) {
                    continue;
                }
                $f = $item['field'];
                if (!empty($layout['key'])) {
                    $f['parent_layout'] = $layout['key'];
                }
                $items[] = [
                    'placement' => $item['placement'] ?? 'before',
                    'anchor'    => $item['anchor'] ?? '',
                    'field'     => function_exists('acf_get_valid_field') ? acf_get_valid_field($f) : $f,
                ];
            }
            $sub = scoop_section_footer_merge_sub_fields($sub, $items);
        }
        if ($name === 'Testimonials') {
            $sub = scoop_testimonials_inject_prize_url($sub, (string) ($layout['key'] ?? ''));
        }
        $field['layouts'][$layout_key]['sub_fields'] = $sub;
    }
    return $field;
});

add_filter('nera_component_data_HomepageHero', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['HomepageHero'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['badge_text']        = scoop_section_footer_row_string($row, 'badge_text', $defaults['badge_text']);
    $data['last_winner_label'] = scoop_section_footer_row_string($row, 'last_winner_label', $defaults['last_winner_label']);
    // Keep parent i18n keys in sync for any consumer still reading them.
    if (!isset($data['i18n']) || !is_array($data['i18n'])) {
        $data['i18n'] = [];
    }
    $data['i18n']['badge']       = $data['badge_text'];
    $data['i18n']['last_winner'] = $data['last_winner_label'];
    return $data;
}, 10, 2);

add_filter('nera_component_data_About', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['About'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['stat_number']       = scoop_section_footer_row_string($row, 'stat_number', $defaults['stat_number']);
    $data['stat_label']        = scoop_section_footer_row_string($row, 'stat_label', $defaults['stat_label']);
    $data['image_placeholder'] = scoop_section_footer_row_string($row, 'image_placeholder', $defaults['image_placeholder']);
    return $data;
}, 10, 2);

add_filter('nera_component_data_FeaturedCompetitions', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['FeaturedCompetitions'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['empty_title'] = scoop_section_footer_row_string($row, 'empty_title', $defaults['empty_title']);
    $data['empty_text']  = scoop_section_footer_row_string($row, 'empty_text', $defaults['empty_text']);
    return $data;
}, 10, 2);

add_filter('nera_component_data_QuickGuide', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['QuickGuide'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['cta_text'] = scoop_section_footer_row_string($row, 'cta_text', $defaults['cta_text']);
    $data['cta_url']  = scoop_section_footer_row_string($row, 'cta_url', $defaults['cta_url']);
    return $data;
}, 10, 2);

add_filter('nera_component_data_CategoriesCompetitions', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['CategoriesCompetitions'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['eyebrow']              = scoop_section_footer_row_string($row, 'eyebrow', $defaults['eyebrow']);
    $data['all_categories_label'] = scoop_section_footer_row_string($row, 'all_categories_label', $defaults['all_categories_label']);
    $data['empty_title']          = scoop_section_footer_row_string($row, 'empty_title', $defaults['empty_title']);
    $data['empty_text']           = scoop_section_footer_row_string($row, 'empty_text', $defaults['empty_text']);
    $data['cta_text']             = scoop_section_footer_row_string($row, 'cta_text', $defaults['cta_text']);
    $data['cta_url']              = scoop_section_footer_row_string($row, 'cta_url', $defaults['cta_url']);
    return $data;
}, 10, 2);

add_filter('nera_component_data_Testimonials', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['Testimonials'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['won_label']        = scoop_section_footer_row_string($row, 'won_label', $defaults['won_label']);
    $data['footer_link_text'] = scoop_section_footer_row_string($row, 'footer_link_text', $defaults['footer_link_text']);
    $data['footer_link_url']  = scoop_section_footer_row_string($row, 'footer_link_url', $defaults['footer_link_url']);

    // Preserve prize_url from the ACF row — parent get_data() keeps list items but editors
    // may save the URL only on the flexible row; merge by index when present.
    $row_list = (isset($row['list']) && is_array($row['list'])) ? $row['list'] : [];
    if (!empty($data['list']) && is_array($data['list'])) {
        foreach ($data['list'] as $i => &$story) {
            if (!is_array($story)) {
                continue;
            }
            if (array_key_exists('prize_url', $story) && $story['prize_url'] !== null && $story['prize_url'] !== '') {
                $story['prize_url'] = (string) $story['prize_url'];
                continue;
            }
            if (isset($row_list[$i]) && is_array($row_list[$i]) && !empty($row_list[$i]['prize_url'])) {
                $story['prize_url'] = (string) $row_list[$i]['prize_url'];
            } else {
                $story['prize_url'] = $story['prize_url'] ?? '';
            }
        }
        unset($story);
    }
    return $data;
}, 10, 2);

add_filter('nera_component_data_Faq', function (array $data, array $args): array {
    $defaults = scoop_section_footer_defaults()['Faq'];
    $row      = (isset($args['acf_row']) && is_array($args['acf_row'])) ? $args['acf_row'] : [];
    $data['footer_prefix']    = scoop_section_footer_row_string($row, 'footer_prefix', $defaults['footer_prefix']);
    $data['footer_link_text'] = scoop_section_footer_row_string($row, 'footer_link_text', $defaults['footer_link_text']);
    $data['footer_link_url']  = scoop_section_footer_row_string($row, 'footer_link_url', $defaults['footer_link_url']);
    // Keep parent contract key in sync for any consumer still reading contact_url.
    $data['contact_url'] = $data['footer_link_url'];
    return $data;
}, 10, 2);
