<?php
/**
 * How It Works migrator: Legacy template fields → Page Components (HowItWorks*).
 *
 * Reuses parent layouts HowItWorksHero, HowItWorksDraw, HowItWorksPostal,
 * HowItWorksTransparency. Seeds all defaults including Hero CTA + repeaters.
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_HIW_MIGRATOR. CLI entry: how-it-works-to-page-components-cli.php
 *
 * Reuses scoop_homepage_migrator_* helpers for resolve/image sideload when present.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $report
 */
function scoop_hiw_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: How It Works migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['post_id'])) {
        $lines[] = 'How It Works post ID: ' . (int) $report['post_id'];
    }
    if (!empty($report['layouts'])) {
        $lines[] = 'Layouts written: ' . implode(', ', $report['layouts']);
    }
    if (isset($report['hero_steps'])) {
        $lines[] = 'Hero steps: ' . (int) $report['hero_steps'];
    }
    if (isset($report['postal_steps'])) {
        $lines[] = 'Postal steps: ' . (int) $report['postal_steps'];
    }
    if (isset($report['transparency_features'])) {
        $lines[] = 'Transparency features: ' . (int) $report['transparency_features'];
    }
    if (!empty($report['cta_seeded'])) {
        $lines[] = 'CTA seeded: yes (' . ($report['cta_url'] ?? '') . ')';
    }
    if (!empty($report['images_sideloaded'])) {
        $lines[] = 'Images sideloaded: ' . (int) $report['images_sideloaded'];
    }
    if (!empty($report['needs_client_asset'])) {
        $lines[] = 'Needs client asset: ' . implode('; ', $report['needs_client_asset']);
    }
    if (!empty($report['force'])) {
        $lines[] = 'Force: yes';
    }
    echo implode("\n", $lines) . "\n";
}

function scoop_hiw_migrator_resolve_post_id(): int
{
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-templates/how-it-works-template.php',
        'fields'         => 'ids',
    ]);
    if (!empty($pages[0])) {
        return (int) $pages[0];
    }
    return 0;
}

/**
 * @param mixed $default
 * @return mixed
 */
function scoop_hiw_migrator_resolve(int $post_id, string $legacy_key, $default)
{
    if (function_exists('scoop_homepage_migrator_resolve')) {
        return scoop_homepage_migrator_resolve($post_id, $legacy_key, $default);
    }
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
 * @param array $report
 */
function scoop_hiw_migrator_legacy_image_id(int $post_id, string $legacy_key, array &$report, string $label): ?int
{
    if (function_exists('scoop_homepage_migrator_legacy_image_id')) {
        return scoop_homepage_migrator_legacy_image_id($post_id, $legacy_key, $report, $label);
    }
    return null;
}

/**
 * @param mixed $raw
 * @param array $report
 */
function scoop_hiw_migrator_image_id($raw, array &$report, string $label): ?int
{
    if (function_exists('scoop_homepage_migrator_image_id')) {
        return scoop_homepage_migrator_image_id($raw, $report, $label);
    }
    if (is_numeric($raw) && (int) $raw > 0) {
        return (int) $raw;
    }
    if (is_array($raw) && !empty($raw['ID'])) {
        return (int) $raw['ID'];
    }
    return null;
}

/**
 * Default shop URL for Hero CTA.
 */
function scoop_hiw_migrator_default_cta_url(): string
{
    if (function_exists('wc_get_page_id')) {
        $url = (string) get_permalink(wc_get_page_id('shop'));
        if ($url !== '') {
            return $url;
        }
    }
    return home_url('/');
}

/**
 * Default draw content HTML (matches HowItWorksDraw get_data fallback).
 */
function scoop_hiw_migrator_default_draw_content(): string
{
    return '<p>Our draws are conducted with absolute transparency. We use the <strong>Google Random Number Generator</strong> to ensure every entry has an equal and fair chance of winning.</p>'
        . '<p>Join us live on our social media channels for every draw! We broadcast the entire process in real-time, announcing winners as they happen and celebrating with our community.</p>';
}

/**
 * @param array $report
 * @return list<array{title: string, description: string, step_icon: int|null}>
 */
function scoop_hiw_migrator_hero_steps(int $post_id, array &$report): array
{
    $defaults = [];
    if (function_exists('nera_get_hiw_default_steps')) {
        foreach (nera_get_hiw_default_steps() as $step) {
            $defaults[] = [
                'title'       => (string) ($step['title'] ?? ''),
                'description' => (string) ($step['description'] ?? ''),
                'step_icon'   => null,
            ];
        }
    }
    if (empty($defaults)) {
        $defaults = [
            [
                'title'       => 'Select a Prize',
                'description' => 'Browse our exciting competitions and choose your favourite prize from our curated selection.',
                'step_icon'   => null,
            ],
            [
                'title'       => 'Choose Your Tickets',
                'description' => 'Select how many entries you want and pick your lucky numbers from the available options.',
                'step_icon'   => null,
            ],
            [
                'title'       => 'Answer Question',
                'description' => 'Answer a simple skill-based question correctly to validate and confirm your entry.',
                'step_icon'   => null,
            ],
            [
                'title'       => 'Win Your Prize!',
                'description' => 'Watch the live draw and be our next lucky winner! We deliver prizes directly to you.',
                'step_icon'   => null,
            ],
        ];
    }

    $raw = scoop_hiw_migrator_resolve($post_id, 'hiw_hero_steps', null);
    if (!is_array($raw) || empty($raw)) {
        return $defaults;
    }

    $out = [];
    foreach ($raw as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $title = isset($row['title']) ? trim((string) $row['title']) : '';
        $description = isset($row['description']) ? trim((string) $row['description']) : '';
        if ($title === '' && $description === '') {
            continue;
        }
        $fallback = $defaults[$i] ?? $defaults[0];
        $icon_id  = null;
        if (!empty($row['step_icon'])) {
            $icon_id = scoop_hiw_migrator_image_id($row['step_icon'], $report, 'HowItWorksHero.hero_steps[' . $i . '].step_icon');
        }
        $out[] = [
            'title'       => $title !== '' ? $title : $fallback['title'],
            'description' => $description !== '' ? $description : $fallback['description'],
            'step_icon'   => $icon_id,
        ];
    }

    return !empty($out) ? $out : $defaults;
}

/**
 * @return list<array{number: string, title: string, icon: string, text: string}>
 */
function scoop_hiw_migrator_postal_steps(int $post_id): array
{
    $defaults = [
        [
            'number' => '1',
            'title'  => 'Include Your Details',
            'icon'   => 'contact_page',
            'text'   => 'Send your name, address, date of birth, contact phone number, and the name of the competition you wish to enter.',
        ],
        [
            'number' => '2',
            'title'  => 'Send Your Postcard',
            'icon'   => 'mail',
            'text'   => 'Send your entry on an unenclosed postcard via first or second class post to our registered business address.',
        ],
        [
            'number' => '3',
            'title'  => 'We Process It',
            'icon'   => 'verified_user',
            'text'   => 'Once received, your entry will be processed and included in the draw just like a paid entry.',
        ],
    ];

    $raw = scoop_hiw_migrator_resolve($post_id, 'hiw_postal_steps', null);
    if (!is_array($raw) || empty($raw)) {
        return $defaults;
    }

    $out = [];
    foreach ($raw as $i => $row) {
        if (!is_array($row) || empty($row['text'])) {
            continue;
        }
        $fallback = $defaults[$i] ?? $defaults[0];
        $out[] = [
            'number' => !empty($row['number']) ? (string) $row['number'] : (string) ($i + 1),
            'title'  => !empty($row['title']) ? (string) $row['title'] : $fallback['title'],
            'icon'   => !empty($row['icon']) ? (string) $row['icon'] : $fallback['icon'],
            'text'   => (string) $row['text'],
        ];
    }

    return !empty($out) ? $out : $defaults;
}

/**
 * @return list<array{icon: string, title: string, description: string}>
 */
function scoop_hiw_migrator_transparency_features(int $post_id): array
{
    $defaults = [
        [
            'icon'        => 'verified_user',
            'title'       => 'Fully Insured',
            'description' => 'We are a legally registered UK business, fully insured and compliant with all regulations.',
        ],
        [
            'icon'        => 'diversity_3',
            'title'       => 'Community Focused',
            'description' => 'Our mission is to support our community and provide life-changing opportunities for everyone.',
        ],
        [
            'icon'        => 'shield_with_heart',
            'title'       => 'Secure & Safe',
            'description' => 'We use industry-standard security protocols to ensure your data and entries are always protected.',
        ],
    ];

    $raw = scoop_hiw_migrator_resolve($post_id, 'hiw_transparency_features', null);
    if (!is_array($raw) || empty($raw)) {
        return $defaults;
    }

    $out = [];
    foreach ($raw as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $fallback = $defaults[$i] ?? $defaults[0];
        $out[] = [
            'icon'        => !empty($row['icon']) ? (string) $row['icon'] : $fallback['icon'],
            'title'       => !empty($row['title']) ? (string) $row['title'] : $fallback['title'],
            'description' => !empty($row['description']) ? (string) $row['description'] : $fallback['description'],
        ];
    }

    return !empty($out) ? $out : $defaults;
}

/**
 * Normalize legacy ACF link → array with title/url/target.
 *
 * @param mixed $raw
 * @return array{title: string, url: string, target: string}
 */
function scoop_hiw_migrator_cta_link(int $post_id): array
{
    $default = [
        'title'  => 'Start Winning Today',
        'url'    => scoop_hiw_migrator_default_cta_url(),
        'target' => '',
    ];

    $raw = scoop_hiw_migrator_resolve($post_id, 'hiw_cta_button_link', null);
    if (!is_array($raw) || empty($raw['url'])) {
        return $default;
    }

    return [
        'title'  => !empty($raw['title']) ? (string) $raw['title'] : $default['title'],
        'url'    => (string) $raw['url'],
        'target' => (!empty($raw['target']) && $raw['target'] === '_blank') ? '_blank' : '',
    ];
}

/**
 * @param array $report
 * @return list<array>
 */
function scoop_hiw_migrator_build_rows(int $post_id, array &$report): array
{
    $title_default = get_the_title($post_id);
    if ($title_default === '') {
        $title_default = 'How It Works';
    }

    $hero_steps = scoop_hiw_migrator_hero_steps($post_id, $report);
    $postal_steps = scoop_hiw_migrator_postal_steps($post_id);
    $features   = scoop_hiw_migrator_transparency_features($post_id);
    $cta_link   = scoop_hiw_migrator_cta_link($post_id);

    $report['hero_steps']             = count($hero_steps);
    $report['postal_steps']           = count($postal_steps);
    $report['transparency_features']  = count($features);
    $report['cta_seeded']             = true;
    $report['cta_url']                = $cta_link['url'];

    $draw_image = scoop_hiw_migrator_legacy_image_id($post_id, 'hiw_draw_image', $report, 'HowItWorksDraw.image');

    return [
        [
            'acf_fc_layout'   => 'HowItWorksHero',
            'hero_title'      => scoop_hiw_migrator_resolve($post_id, 'hiw_hero_title', $title_default),
            'hero_subtitle'   => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_hero_subtitle',
                'Win your dream prizes in just 4 simple steps'
            ),
            'hero_badge'      => scoop_hiw_migrator_resolve($post_id, 'hiw_hero_badge', 'Simple & Fair'),
            'hero_steps'      => $hero_steps,
            'cta_button_link' => $cta_link,
            'cta_footer_text' => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_cta_footer_text',
                'Join thousands of winners • New competitions added daily'
            ),
        ],
        [
            'acf_fc_layout'     => 'HowItWorksDraw',
            'eyebrow'           => scoop_hiw_migrator_resolve($post_id, 'hiw_draw_eyebrow', 'Fair & Transparent'),
            'title'             => scoop_hiw_migrator_resolve($post_id, 'hiw_draw_title', 'The Draw Process'),
            'content'           => scoop_hiw_migrator_resolve($post_id, 'hiw_draw_content', scoop_hiw_migrator_default_draw_content()),
            'image'             => $draw_image,
            'placeholder_title' => scoop_hiw_migrator_resolve($post_id, 'hiw_draw_placeholder_title', 'Live Draw Streams'),
            'placeholder_text'  => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_draw_placeholder_text',
                'Watch us live on Facebook and Instagram'
            ),
            'placeholder_icon'  => scoop_hiw_migrator_resolve($post_id, 'hiw_draw_placeholder_icon', 'videocam'),
        ],
        [
            'acf_fc_layout' => 'HowItWorksPostal',
            'title'         => scoop_hiw_migrator_resolve($post_id, 'hiw_postal_title', 'Free Postal Entry Route'),
            'intro'         => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_postal_intro',
                'We offer a free entry route via post for all of our competitions.'
            ),
            'steps'         => $postal_steps,
            'note'          => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_postal_note',
                'Please note: One entry per postcard. Entries must be received before the competition closes.'
            ),
        ],
        [
            'acf_fc_layout' => 'HowItWorksTransparency',
            'title'         => scoop_hiw_migrator_resolve($post_id, 'hiw_transparency_title', 'Transparency & Fairness'),
            'subtitle'      => scoop_hiw_migrator_resolve(
                $post_id,
                'hiw_transparency_subtitle',
                'We pride ourselves on being a registered UK business that operates with full integrity and a passion for giving back.'
            ),
            'features'      => $features,
        ],
    ];
}

/**
 * @return array Report
 */
function scoop_migrate_hiw_to_page_components(bool $force = false): array
{
    $report = [
        'ok'                     => false,
        'force'                  => $force,
        'post_id'                => 0,
        'layouts'                => [],
        'hero_steps'             => 0,
        'postal_steps'           => 0,
        'transparency_features'  => 0,
        'cta_seeded'             => false,
        'cta_url'                => '',
        'images_sideloaded'      => 0,
        'needs_client_asset'     => [],
        'error'                  => null,
    ];

    if (!function_exists('update_field') || !function_exists('get_field')) {
        $report['error'] = 'ACF is not available.';
        return $report;
    }

    $post_id = scoop_hiw_migrator_resolve_post_id();
    $report['post_id'] = $post_id;
    if ($post_id <= 0) {
        $report['error'] = 'Could not resolve How It Works page (template page-templates/how-it-works-template.php).';
        return $report;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $report;
    }

    $rows = scoop_hiw_migrator_build_rows($post_id, $report);
    foreach ($rows as $row) {
        if (!empty($row['acf_fc_layout'])) {
            $report['layouts'][] = $row['acf_fc_layout'];
        }
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

function scoop_hiw_migrator_token_option(): string
{
    return 'scoop_hiw_migrator_token';
}

function scoop_hiw_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_hiw_migrator_token_option(), $token, false);
    return $token;
}

function scoop_hiw_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_hiw_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_hiw_migrator_token_option());
    return true;
}

function scoop_hiw_migrator_admin_url(bool $force = false): string
{
    $token = scoop_hiw_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_hiw_migrate',
        '_wpnonce' => wp_create_nonce('nera_hiw_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_HIW_MIGRATOR') && NERA_ALLOW_HIW_MIGRATOR) {
    add_action('admin_post_scoop_hiw_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_hiw_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_hiw_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_hiw_to_page_components($force);
        scoop_hiw_migrator_print_report($report);
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
        $url       = esc_url(scoop_hiw_migrator_admin_url(false));
        $force_url = esc_url(scoop_hiw_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>How It Works migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_HIW_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
