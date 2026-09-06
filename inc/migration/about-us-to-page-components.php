<?php
/**
 * About Us migrator: Legacy template fields → Page Components (NeraAboutUs*).
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_ABOUT_US_MIGRATOR. CLI entry: about-us-to-page-components-cli.php
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
function scoop_about_us_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: About Us migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['post_id'])) {
        $lines[] = 'About Us post ID: ' . (int) $report['post_id'];
    }
    if (!empty($report['layouts'])) {
        $lines[] = 'Layouts written: ' . implode(', ', $report['layouts']);
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

function scoop_about_us_migrator_resolve_post_id(): int
{
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-templates/about-us-template.php',
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
function scoop_about_us_migrator_resolve(int $post_id, string $legacy_key, $default)
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
 * Columns body must be plain text — strip HTML (card/section chrome paste failure mode).
 */
function scoop_about_us_migrator_plain_text(int $post_id, string $legacy_key, string $default = ''): string
{
    $value = scoop_about_us_migrator_resolve($post_id, $legacy_key, $default);
    if (!is_string($value) && !is_numeric($value)) {
        return $default;
    }
    $text = trim(wp_strip_all_tags((string) $value));
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim($text);
}

/**
 * @param array $report
 */
function scoop_about_us_migrator_legacy_image_id(int $post_id, string $legacy_key, array &$report, string $label): ?int
{
    if (function_exists('scoop_homepage_migrator_legacy_image_id')) {
        return scoop_homepage_migrator_legacy_image_id($post_id, $legacy_key, $report, $label);
    }
    return null;
}

/**
 * @return list<array>
 */
function scoop_about_us_migrator_build_rows(int $post_id, array &$report): array
{
    $title_default = get_the_title($post_id);
    if ($title_default === '') {
        $title_default = 'About us';
    }

    $primary_url = scoop_about_us_migrator_resolve($post_id, 'about_cta_primary_btn_url', '');
    if ($primary_url === '' || $primary_url === null) {
        $primary_url = home_url('/shop/');
    }
    $secondary_url = scoop_about_us_migrator_resolve($post_id, 'about_cta_secondary_btn_url', '');
    if ($secondary_url === '' || $secondary_url === null) {
        $secondary_url = home_url('/contact/');
    }

    $hero_image = scoop_about_us_migrator_legacy_image_id($post_id, 'about_hero_image', $report, 'NeraAboutUsHero.hero_image');

    return [
        [
            'acf_fc_layout' => 'NeraAboutUsHero',
            'hero_eyebrow'  => scoop_about_us_migrator_resolve($post_id, 'about_hero_eyebrow', 'About us'),
            'title'         => scoop_about_us_migrator_resolve($post_id, 'about_title', $title_default),
            'hero_tagline'  => scoop_about_us_migrator_resolve(
                $post_id,
                'about_hero_tagline',
                'Building a community rooted in transparency, trust, and exciting opportunities for everyone.'
            ),
            'hero_image'    => $hero_image,
        ],
        [
            'acf_fc_layout' => 'NeraAboutUsNarrative',
            'narrative'     => scoop_about_us_migrator_resolve($post_id, 'about_narrative', ''),
        ],
        [
            'acf_fc_layout'       => 'NeraAboutUsColumns',
            'story_left_icon'     => 'groups',
            'story_left_title'    => scoop_about_us_migrator_resolve($post_id, 'about_story_left_title', 'Our story'),
            'story_left_content'  => scoop_about_us_migrator_plain_text($post_id, 'about_story_left_content', ''),
            'story_right_icon'    => 'lightbulb',
            'story_right_title'   => scoop_about_us_migrator_resolve($post_id, 'about_story_right_title', 'What drives us'),
            'story_right_content' => scoop_about_us_migrator_plain_text($post_id, 'about_story_right_content', ''),
        ],
        [
            'acf_fc_layout'      => 'NeraAboutUsCta',
            'cta_heading'        => scoop_about_us_migrator_resolve($post_id, 'about_cta_heading', 'Join the community'),
            'cta_description'    => scoop_about_us_migrator_resolve(
                $post_id,
                'about_cta_description',
                'Be part of a transparent, supportive journey where everyone has a chance to win.'
            ),
            'cta_primary_text'   => scoop_about_us_migrator_resolve($post_id, 'about_cta_primary_btn_text', 'Explore competitions'),
            'cta_primary_url'    => $primary_url,
            'cta_secondary_text' => scoop_about_us_migrator_resolve($post_id, 'about_cta_secondary_btn_text', 'Get in touch'),
            'cta_secondary_url'  => $secondary_url,
        ],
    ];
}

/**
 * @return array Report
 */
function scoop_migrate_about_us_to_page_components(bool $force = false): array
{
    $report = [
        'ok'                 => false,
        'force'              => $force,
        'post_id'            => 0,
        'layouts'            => [],
        'images_sideloaded'  => 0,
        'needs_client_asset' => [],
        'error'              => null,
    ];

    if (!function_exists('update_field') || !function_exists('get_field')) {
        $report['error'] = 'ACF is not available.';
        return $report;
    }

    $post_id = scoop_about_us_migrator_resolve_post_id();
    $report['post_id'] = $post_id;
    if ($post_id <= 0) {
        $report['error'] = 'Could not resolve About Us page (template page-templates/about-us-template.php).';
        return $report;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $report;
    }

    $rows = scoop_about_us_migrator_build_rows($post_id, $report);
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

function scoop_about_us_migrator_token_option(): string
{
    return 'scoop_about_us_migrator_token';
}

function scoop_about_us_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_about_us_migrator_token_option(), $token, false);
    return $token;
}

function scoop_about_us_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_about_us_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_about_us_migrator_token_option());
    return true;
}

function scoop_about_us_migrator_admin_url(bool $force = false): string
{
    $token = scoop_about_us_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_about_us_migrate',
        '_wpnonce' => wp_create_nonce('nera_about_us_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_ABOUT_US_MIGRATOR') && NERA_ALLOW_ABOUT_US_MIGRATOR) {
    add_action('admin_post_scoop_about_us_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_about_us_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_about_us_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_about_us_to_page_components($force);
        scoop_about_us_migrator_print_report($report);
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
        $url       = esc_url(scoop_about_us_migrator_admin_url(false));
        $force_url = esc_url(scoop_about_us_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>About Us migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_ABOUT_US_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
