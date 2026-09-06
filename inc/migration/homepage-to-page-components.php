<?php
/**
 * Homepage migrator: Legacy template fields → Page Components.
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_HOMEPAGE_MIGRATOR. CLI entry: homepage-to-page-components-cli.php
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Print a migration report to CLI or HTTP.
 *
 * @param array $report
 */
function scoop_homepage_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: homepage migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['post_id'])) {
        $lines[] = 'Homepage post ID: ' . (int) $report['post_id'];
    }
    if (!empty($report['layouts'])) {
        $lines[] = 'Layouts written: ' . implode(', ', $report['layouts']);
    }
    if (!empty($report['skipped'])) {
        $lines[] = 'Skipped stubs: ' . implode(', ', $report['skipped']);
    }
    if (!empty($report['images_sideloaded'])) {
        $lines[] = 'Images sideloaded: ' . (int) $report['images_sideloaded'];
    }
    if (!empty($report['needs_client_asset'])) {
        $lines[] = 'Needs client asset (external URL skipped): ' . implode('; ', $report['needs_client_asset']);
    }
    if (!empty($report['force'])) {
        $lines[] = 'Force: yes';
    }
    echo implode("\n", $lines) . "\n";
}

/**
 * Resolve the homepage post ID (front page or Nera Homepage template).
 */
function scoop_homepage_migrator_resolve_post_id(): int
{
    $front = (int) get_option('page_on_front');
    if ($front > 0) {
        $tpl = get_page_template_slug($front);
        if ($tpl === 'page-templates/homepage-template.php') {
            return $front;
        }
    }

    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-templates/homepage-template.php',
        'fields'         => 'ids',
    ]);
    if (!empty($pages[0])) {
        return (int) $pages[0];
    }

    return $front > 0 ? $front : 0;
}

/**
 * Ordered layout inventory from homepage_sections, else template defaults.
 *
 * @return array{layouts: list<string>, skipped: list<string>}
 */
function scoop_homepage_migrator_inventory(int $post_id): array
{
    $component_map = [
        'hero'                  => 'HomepageHero',
        'credibility'           => 'Credibility',
        'stats'                 => 'Stats',
        'featured_competitions' => 'FeaturedCompetitions',
        'promo_banner'          => 'PromoBanner',
        'testimonials'          => 'Testimonials',
        'quick_guide'           => 'QuickGuide',
        'about'                 => 'About',
        'categories'            => 'CategoriesCompetitions',
        'faq'                   => 'Faq',
    ];

    $default_order = [
        'HomepageHero',
        'Credibility',
        'FeaturedCompetitions',
        'PromoBanner',
        'Testimonials',
        'QuickGuide',
        'About',
        'CategoriesCompetitions',
        'Faq',
    ];

    $skipped = [];
    $sections = get_field('homepage_sections', $post_id);

    if (!is_array($sections) || empty($sections)) {
        $skipped[] = 'winners';
        return ['layouts' => $default_order, 'skipped' => $skipped];
    }

    $layouts = [];
    foreach ($sections as $row) {
        if (empty($row['show_section'])) {
            continue;
        }
        $slug = isset($row['section']) ? (string) $row['section'] : '';
        if ($slug === 'winners') {
            $skipped[] = 'winners';
            continue;
        }
        if (isset($component_map[$slug])) {
            $layouts[] = $component_map[$slug];
        }
    }

    return ['layouts' => $layouts, 'skipped' => $skipped];
}

/**
 * Resolve a scalar/legacy field: saved legacy → hardcoded get_data()/template fallback.
 *
 * @param mixed $default
 * @return mixed
 */
function scoop_homepage_migrator_resolve(int $post_id, string $legacy_key, $default)
{
    if (!function_exists('get_field')) {
        return $default;
    }
    $value = get_field($legacy_key, $post_id);
    if ($value !== null && $value !== '') {
        return $value;
    }
    if ($value === false || $value === 0 || $value === '0') {
        if (metadata_exists('post', $post_id, $legacy_key)) {
            return $value;
        }
    }
    return $default;
}

/**
 * Whether a URL is on this site's host (sideload allowed).
 */
function scoop_homepage_migrator_is_own_host(string $url): bool
{
    $host = wp_parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return false;
    }
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    return $site_host && strcasecmp((string) $host, (string) $site_host) === 0;
}

/**
 * Resolve a legacy image field to an attachment ID.
 *
 * Prefers the raw post meta ID (ACF image fields often use return_format=url,
 * which would otherwise force a re-sideload of an already-attached file).
 *
 * @param array $report
 */
function scoop_homepage_migrator_legacy_image_id(int $post_id, string $legacy_key, array &$report, string $label): ?int
{
    $raw_meta = get_post_meta($post_id, $legacy_key, true);
    if (is_numeric($raw_meta) && (int) $raw_meta > 0) {
        return (int) $raw_meta;
    }
    return scoop_homepage_migrator_image_id(
        scoop_homepage_migrator_resolve($post_id, $legacy_key, null),
        $report,
        $label
    );
}

/**
 * Resolve an image field to an attachment ID, sideloading same-host URLs when needed.
 *
 * @param mixed $raw
 * @param array $report
 */
function scoop_homepage_migrator_image_id($raw, array &$report, string $label): ?int
{
    if (is_numeric($raw) && (int) $raw > 0) {
        return (int) $raw;
    }
    if (is_array($raw)) {
        if (!empty($raw['ID'])) {
            return (int) $raw['ID'];
        }
        if (!empty($raw['id'])) {
            return (int) $raw['id'];
        }
        if (!empty($raw['url']) && is_string($raw['url'])) {
            $raw = $raw['url'];
        } else {
            return null;
        }
    }
    if (!is_string($raw) || $raw === '') {
        return null;
    }

    $url = $raw;

    if ($url[0] === '/' && strpos($url, '//') !== 0) {
        $url = home_url($url);
    }

    if (!preg_match('#^https?://#i', $url)) {
        if (file_exists(get_template_directory() . '/' . ltrim($raw, '/'))) {
            $url = get_template_directory_uri() . '/' . ltrim($raw, '/');
        } elseif (file_exists(get_stylesheet_directory() . '/' . ltrim($raw, '/'))) {
            $url = get_stylesheet_directory_uri() . '/' . ltrim($raw, '/');
        } else {
            $report['needs_client_asset'][] = $label . ' (unresolvable path)';
            return null;
        }
    }

    if (!scoop_homepage_migrator_is_own_host($url)) {
        $report['needs_client_asset'][] = $label . ' → ' . $url;
        return null;
    }

    $existing = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_nera_sideload_source',
        'meta_value'     => $url,
    ]);
    if (!empty($existing[0])) {
        return (int) $existing[0];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $id = media_sideload_image($url, 0, null, 'id');
    if (is_wp_error($id)) {
        $report['needs_client_asset'][] = $label . ' (sideload failed: ' . $id->get_error_message() . ')';
        return null;
    }
    $id = (int) $id;
    if ($id > 0) {
        update_post_meta($id, '_nera_sideload_source', $url);
        $report['images_sideloaded'] = (int) ($report['images_sideloaded'] ?? 0) + 1;
        return $id;
    }
    return null;
}

/**
 * Build one flexible row for a layout name.
 *
 * @param array $report
 */
function scoop_homepage_migrator_build_row(string $layout, int $post_id, array &$report): ?array
{
    switch ($layout) {
        case 'HomepageHero':
            $image = scoop_homepage_migrator_legacy_image_id($post_id, 'hero_image', $report, 'HomepageHero.image');
            $cta_url = scoop_homepage_migrator_resolve($post_id, 'hero_cta_url', '/shop/');
            return [
                'acf_fc_layout'     => 'HomepageHero',
                'title'             => scoop_homepage_migrator_resolve($post_id, 'hero_title', 'Win Your Dream'),
                'highlight'         => scoop_homepage_migrator_resolve($post_id, 'hero_highlight', 'Lifestyle.'),
                'description'       => scoop_homepage_migrator_resolve(
                    $post_id,
                    'hero_description',
                    "Experience the thrill of high-end giveaways with the UK's most exclusive prize competition platform. Because you deserve a chance to win."
                ),
                'cta_text'          => scoop_homepage_migrator_resolve($post_id, 'hero_cta_text', 'View Active Giveaways'),
                'cta_url'           => $cta_url ?: '/shop/',
                'secondary_text'    => scoop_homepage_migrator_resolve($post_id, 'hero_secondary_text', 'Recent Winners'),
                'secondary_url'     => scoop_homepage_migrator_resolve($post_id, 'hero_secondary_url', '#winners'),
                'image'             => $image,
                'winner_name'       => scoop_homepage_migrator_resolve($post_id, 'last_winner_name', 'Sarah M.'),
                'winner_prize'      => scoop_homepage_migrator_resolve($post_id, 'last_winner_prize', 'Won This Prize'),
                'badge_text'        => scoop_section_footer_defaults()['HomepageHero']['badge_text'],
                'last_winner_label' => scoop_section_footer_defaults()['HomepageHero']['last_winner_label'],
            ];

        case 'Credibility':
            $default_items = [
                ['icon' => 'lock', 'label' => 'Secure Payments'],
                ['icon' => 'verified', 'label' => 'UK Compliant'],
                ['icon' => 'visibility', 'label' => 'Transparent Draws'],
                ['icon' => 'emoji_events', 'label' => 'Real Winners'],
                ['icon' => 'headset_mic', 'label' => 'Fast Support'],
            ];
            $items = scoop_homepage_migrator_resolve($post_id, 'credibility_items', null);
            if (!is_array($items) || empty($items)) {
                $items = $default_items;
            } else {
                $norm = [];
                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $norm[] = [
                        'icon'  => $item['icon'] ?? 'check_circle',
                        'label' => $item['label'] ?? '',
                    ];
                }
                $items = $norm ?: $default_items;
            }
            return [
                'acf_fc_layout' => 'Credibility',
                'items'         => $items,
            ];

        case 'Stats':
            return [
                'acf_fc_layout' => 'Stats',
                'winners'       => scoop_homepage_migrator_resolve($post_id, 'stat_winners', '150'),
                'value'         => scoop_homepage_migrator_resolve($post_id, 'stat_value', '2'),
                'secure'        => scoop_homepage_migrator_resolve($post_id, 'stat_secure', '100'),
                'tp_score'      => scoop_homepage_migrator_resolve($post_id, 'tp_score', '4.8'),
                'tp_reviews'    => scoop_homepage_migrator_resolve($post_id, 'tp_reviews', '1,250'),
            ];

        case 'FeaturedCompetitions':
            $chrome = scoop_section_footer_defaults()['FeaturedCompetitions'];
            return [
                'acf_fc_layout' => 'FeaturedCompetitions',
                'title'         => scoop_homepage_migrator_resolve($post_id, 'featured_title', 'Ending Soon'),
                'subtitle'      => scoop_homepage_migrator_resolve(
                    $post_id,
                    'featured_subtitle',
                    'Grab your tickets before time runs out — these competitions are about to close.'
                ),
                'empty_title'   => $chrome['empty_title'],
                'empty_text'    => $chrome['empty_text'],
            ];

        case 'PromoBanner':
            $default_links = [
                ['platform' => 'facebook', 'url' => '#'],
                ['platform' => 'instagram', 'url' => '#'],
            ];
            $links = scoop_homepage_migrator_resolve($post_id, 'promo_social_links', null);
            if (!is_array($links) || empty($links)) {
                $links = $default_links;
            }
            $bg_id = scoop_homepage_migrator_legacy_image_id($post_id, 'promo_bg_image', $report, 'PromoBanner.bg_image');
            if (!$bg_id) {
                $raw_meta = get_post_meta($post_id, 'promo_bg_image', true);
                if ($raw_meta === '' || $raw_meta === false || $raw_meta === null) {
                    $report['needs_client_asset'][] = 'PromoBanner.bg_image (no saved legacy; Unsplash default not sideloaded)';
                }
            }
            return [
                'acf_fc_layout' => 'PromoBanner',
                'badge'         => scoop_homepage_migrator_resolve($post_id, 'promo_badge', 'Stay Connected'),
                'title'         => scoop_homepage_migrator_resolve($post_id, 'promo_title', 'Follow us on socials'),
                'description'   => scoop_homepage_migrator_resolve(
                    $post_id,
                    'promo_description',
                    'Follow us for updates, new competitions and giveaways.'
                ),
                'bg_image'      => $bg_id,
                'social_links'  => $links,
            ];

        case 'Testimonials':
            $default_list = [
                [
                    'name'      => 'James Robinson',
                    'avatar'    => null,
                    'quote'     => "I still wake up and check my driveway to make sure it's not a dream. The whole process was seamless and the phone call from the team was the best moment of my year.",
                    'prize'     => 'BMW M4 Competition',
                    'prize_url' => '',
                ],
                [
                    'name'      => 'Sarah Lewis',
                    'avatar'    => null,
                    'quote'     => "Winning the £10k cash meant I could finally take my family on the holiday we've been putting off for five years. It truly changed everything for us this summer.",
                    'prize'     => '£10,000 Tax-Free Cash',
                    'prize_url' => '',
                ],
            ];
            $list = scoop_homepage_migrator_resolve($post_id, 'testimonials_list', null);
            if (!is_array($list) || empty($list)) {
                $report['needs_client_asset'][] = 'Testimonials.list avatars (Unsplash defaults not sideloaded)';
                $list = $default_list;
            } else {
                $norm = [];
                foreach ($list as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $avatar_id = null;
                    if (!empty($item['avatar'])) {
                        // Repeater avatar may already be an ID or a URL/array.
                        $avatar_id = scoop_homepage_migrator_image_id($item['avatar'], $report, 'Testimonials.avatar');
                    }
                    $norm[] = [
                        'name'      => $item['name'] ?? '',
                        'avatar'    => $avatar_id,
                        'quote'     => $item['quote'] ?? '',
                        'prize'     => $item['prize'] ?? '',
                        'prize_url' => isset($item['prize_url']) ? (string) $item['prize_url'] : '',
                    ];
                }
                $list = $norm ?: $default_list;
            }
            $footer = function_exists('scoop_section_footer_defaults')
                ? scoop_section_footer_defaults()['Testimonials']
                : [
                    'won_label'        => 'Won:',
                    'footer_link_text' => 'Read more winner stories',
                    'footer_link_url'  => '#winners',
                ];
            return [
                'acf_fc_layout'     => 'Testimonials',
                'title'             => scoop_homepage_migrator_resolve($post_id, 'testimonials_title', 'Stories of the Circle'),
                'subtitle'          => scoop_homepage_migrator_resolve(
                    $post_id,
                    'testimonials_subtitle',
                    'Step inside the lives of those who dared to dream. Real people, life-changing moments.'
                ),
                'won_label'         => $footer['won_label'] ?? 'Won:',
                'list'              => $list,
                'footer_link_text'  => $footer['footer_link_text'],
                'footer_link_url'   => $footer['footer_link_url'],
            ];

        case 'QuickGuide':
            $default_steps = [
                [
                    'number'      => '01',
                    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h6"/><circle cx="12" cy="12" r="2"/><path d="m16 8-2.6 2.6"/><circle cx="18" cy="6" r="3"/></svg>',
                    'title'       => 'Select Prize',
                    'description' => 'Browse our active luxury giveaways and choose the prize you want to win most.',
                ],
                [
                    'number'      => '02',
                    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
                    'title'       => 'Choose Tickets',
                    'description' => 'Select how many entries you want. Each ticket increases your chances of holding the winning number.',
                ],
                [
                    'number'      => '03',
                    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                    'title'       => 'Wait for Draw',
                    'description' => 'Answer the skill-based question correctly and wait for the live draw. Good luck!',
                ],
            ];
            $steps = scoop_homepage_migrator_resolve($post_id, 'guide_steps', null);
            if (!is_array($steps) || empty($steps)) {
                $steps = $default_steps;
            }
            $cta = function_exists('scoop_section_footer_defaults')
                ? scoop_section_footer_defaults()['QuickGuide']
                : [
                    'cta_text' => 'Browse Competitions',
                    'cta_url'  => home_url('/all-competitions/'),
                ];
            return [
                'acf_fc_layout' => 'QuickGuide',
                'title'         => scoop_homepage_migrator_resolve($post_id, 'guide_title', 'How to Play'),
                'subtitle'      => scoop_homepage_migrator_resolve(
                    $post_id,
                    'guide_subtitle',
                    'Win your dream prizes in just three simple steps'
                ),
                'steps'         => $steps,
                'cta_text'      => $cta['cta_text'],
                'cta_url'       => $cta['cta_url'],
            ];

        case 'About':
            $features = scoop_homepage_migrator_resolve($post_id, 'about_features', []);
            if (!is_array($features)) {
                $features = [];
            }
            $image = scoop_homepage_migrator_legacy_image_id($post_id, 'about_image', $report, 'About.image');
            $show_cta = scoop_homepage_migrator_resolve($post_id, 'about_show_cta', 1);
            $chrome = function_exists('scoop_section_footer_defaults')
                ? scoop_section_footer_defaults()['About']
                : [
                    'stat_number'       => '150',
                    'stat_label'        => 'Happy Winners',
                    'image_placeholder' => 'Image placeholder',
                ];
            return [
                'acf_fc_layout'      => 'About',
                'badge'              => scoop_homepage_migrator_resolve($post_id, 'about_badge', 'Who We Are'),
                'title'              => scoop_homepage_migrator_resolve(
                    $post_id,
                    'about_title',
                    'Your Trusted Partner in Premium Giveaways'
                ),
                'subtitle'           => scoop_homepage_migrator_resolve(
                    $post_id,
                    'about_subtitle',
                    'Bringing dreams to life, one competition at a time.'
                ),
                'description'        => scoop_homepage_migrator_resolve(
                    $post_id,
                    'about_description',
                    "We're passionate about creating life-changing moments through fair, transparent, and exciting prize competitions. With over 150 winners and £2M+ in prizes awarded, we've built a trusted community of dreamers and winners."
                ),
                'features'           => $features,
                'image'              => $image,
                'image_position'     => scoop_homepage_migrator_resolve($post_id, 'about_image_position', 'right'),
                'background'         => scoop_homepage_migrator_resolve($post_id, 'about_background', 'gradient'),
                'show_cta'           => $show_cta ? 1 : 0,
                'cta_text'           => scoop_homepage_migrator_resolve($post_id, 'about_cta_text', 'Learn More About Us'),
                'cta_url'            => scoop_homepage_migrator_resolve($post_id, 'about_cta_url', '/about/'),
                'stat_number'        => $chrome['stat_number'],
                'stat_label'         => $chrome['stat_label'],
                'image_placeholder'  => $chrome['image_placeholder'],
            ];

        case 'CategoriesCompetitions':
            $cta = function_exists('scoop_section_footer_defaults')
                ? scoop_section_footer_defaults()['CategoriesCompetitions']
                : [
                    'cta_text' => 'View All Competitions',
                    'cta_url'  => home_url('/all-competitions/'),
                ];
            return [
                'acf_fc_layout'        => 'CategoriesCompetitions',
                'title'                => scoop_homepage_migrator_resolve(
                    $post_id,
                    'categories_section_title',
                    'Find Your Dream Prize'
                ),
                'subtitle'             => scoop_homepage_migrator_resolve(
                    $post_id,
                    'categories_section_subtitle',
                    'Browse competitions by category and discover your next big win.'
                ),
                'eyebrow'              => $cta['eyebrow'] ?? 'Browse by Category',
                'all_categories_label' => $cta['all_categories_label'] ?? 'All Categories',
                'empty_title'          => $cta['empty_title'] ?? 'No competitions found',
                'empty_text'           => $cta['empty_text'] ?? 'Check back soon for new amazing prizes!',
                'cta_text'             => $cta['cta_text'],
                'cta_url'              => $cta['cta_url'],
            ];

        case 'Faq':
            $default_faqs = [
                [
                    'question' => 'Is it legal to enter UK?',
                    'answer'   => 'Yes, our competitions are fully legal in the UK. We operate as a prize competition which requires entrants to demonstrate skill by answering a question correctly. This is fully compliant with UK gambling laws and we are registered with the relevant authorities.',
                ],
                [
                    'question' => 'How do you draw winners?',
                    'answer'   => 'All our draws are conducted live on Facebook and YouTube using a certified random number generator. The draw process is completely transparent and all entries are verified before the winner is announced. You can watch previous draws on our social media channels.',
                ],
                [
                    'question' => 'When are the draws done?',
                    'answer'   => 'Draws are typically conducted when all tickets have sold or when the competition end date is reached. We announce draw times in advance via email and social media so you never miss the excitement. Most draws happen weekly on Sunday evenings.',
                ],
                [
                    'question' => 'How do I receive my prize?',
                    'answer'   => 'For physical prizes, we arrange delivery to your door completely free of charge. Cash prizes are transferred directly to your bank account within 48 hours. For larger prizes like cars, we can either deliver to your address or arrange collection from a convenient location.',
                ],
            ];
            $list = scoop_homepage_migrator_resolve($post_id, 'faq_list', null);
            if (!is_array($list) || empty($list)) {
                $list = $default_faqs;
            }
            $footer = function_exists('scoop_section_footer_defaults')
                ? scoop_section_footer_defaults()['Faq']
                : [
                    'footer_prefix'    => 'Still have questions?',
                    'footer_link_text' => 'Contact our support team',
                    'footer_link_url'  => '#',
                ];
            return [
                'acf_fc_layout'     => 'Faq',
                'title'             => scoop_homepage_migrator_resolve($post_id, 'faq_title', 'Frequently Asked Questions'),
                'list'              => $list,
                'footer_prefix'     => $footer['footer_prefix'],
                'footer_link_text'  => $footer['footer_link_text'],
                'footer_link_url'   => $footer['footer_link_url'],
            ];

        default:
            return null;
    }
}

/**
 * Run the homepage → page_components migration.
 *
 * @return array Report
 */
function scoop_migrate_homepage_to_page_components(bool $force = false): array
{
    $report = [
        'ok'                 => false,
        'force'              => $force,
        'post_id'            => 0,
        'layouts'            => [],
        'skipped'            => [],
        'images_sideloaded'  => 0,
        'needs_client_asset' => [],
        'error'              => null,
    ];

    if (!function_exists('update_field') || !function_exists('get_field')) {
        $report['error'] = 'ACF is not available.';
        return $report;
    }

    $post_id = scoop_homepage_migrator_resolve_post_id();
    $report['post_id'] = $post_id;
    if ($post_id <= 0) {
        $report['error'] = 'Could not resolve homepage post ID.';
        return $report;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $report;
    }

    $inventory = scoop_homepage_migrator_inventory($post_id);
    $report['skipped'] = $inventory['skipped'];

    $rows = [];
    foreach ($inventory['layouts'] as $layout) {
        $row = scoop_homepage_migrator_build_row($layout, $post_id, $report);
        if ($row === null) {
            continue;
        }
        $rows[] = $row;
        $report['layouts'][] = $layout;
    }

    if (empty($rows)) {
        $report['error'] = 'No layout rows to write.';
        return $report;
    }

    $ok = update_field('page_components', $rows, $post_id);
    if (!$ok) {
        $check = get_field('page_components', $post_id);
        if (!is_array($check) || empty($check)) {
            $report['error'] = 'update_field(page_components) failed.';
            return $report;
        }
    }

    $report['ok'] = true;
    return $report;
}

/**
 * One-time token helpers for the HTTP path.
 */
function scoop_homepage_migrator_token_option(): string
{
    return 'scoop_homepage_migrator_token';
}

function scoop_homepage_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_homepage_migrator_token_option(), $token, false);
    return $token;
}

function scoop_homepage_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_homepage_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_homepage_migrator_token_option());
    return true;
}

function scoop_homepage_migrator_admin_url(bool $force = false): string
{
    $token = scoop_homepage_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_homepage_migrate',
        '_wpnonce' => wp_create_nonce('nera_homepage_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_HOMEPAGE_MIGRATOR') && NERA_ALLOW_HOMEPAGE_MIGRATOR) {
    add_action('admin_post_scoop_homepage_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_homepage_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_homepage_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_homepage_to_page_components($force);
        scoop_homepage_migrator_print_report($report);
        exit;
    });

    add_action('admin_notices', function () {
        if (!current_user_can('manage_options')) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }
        $url       = esc_url(scoop_homepage_migrator_admin_url(false));
        $force_url = esc_url(scoop_homepage_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Homepage migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_HOMEPAGE_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
