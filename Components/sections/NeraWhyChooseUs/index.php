<?php
namespace Nera\Components\NeraWhyChooseUs;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trust / Why Choose Us bar — fully editable (replaces trust-features hard-coding).
 *
 * @param array $args
 * @return array{
 *   title: string,
 *   subtitle: string,
 *   items: list<array{icon: string, title: string, description: string}>,
 * }
 */
function get_data(array $args = []): array
{
    $title = (string) nera_component_field($args, 'title', 'trust_title', __('Why Choose Us', 'nera-competitions'));
    $subtitle = (string) nera_component_field(
        $args,
        'subtitle',
        'trust_subtitle',
        __('Join thousands of happy winners who trust us for fair and exciting competitions.', 'nera-competitions')
    );

    $defaults = default_items();
    $items_raw = nera_component_field($args, 'items', 'trust_badges', $defaults);

    $items = [];
    if (is_array($items_raw)) {
        foreach ($items_raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item_title = isset($row['title']) ? (string) $row['title'] : '';
            if ($item_title === '') {
                continue;
            }
            $items[] = [
                'icon'        => isset($row['icon']) ? (string) $row['icon'] : '',
                'title'       => $item_title,
                'description' => isset($row['description']) ? (string) $row['description'] : '',
            ];
        }
    }

    if ($items === []) {
        $items = $defaults;
    }

    return [
        'title'    => $title,
        'subtitle' => $subtitle,
        'items'    => $items,
    ];
}

/**
 * @return list<array{icon: string, title: string, description: string}>
 */
function default_items(): array
{
    return [
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
            'title'       => __('Secure Checkout', 'nera-competitions'),
            'description' => __('Your payment details are protected with bank-level encryption.', 'nera-competitions'),
        ],
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            'title'       => __('Certified Winners', 'nera-competitions'),
            'description' => __('All draws are independently verified and transparent.', 'nera-competitions'),
        ],
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
            'title'       => __('24/7 Support', 'nera-competitions'),
            'description' => __('Our friendly team is here to help anytime you need.', 'nera-competitions'),
        ],
    ];
}
