<?php
/**
 * Contact migrator: Legacy template fields → Page Components
 * (NeraPageHero + NeraContact).
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_CONTACT_MIGRATOR. CLI entry: contact-to-page-components-cli.php
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
function scoop_contact_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: Contact migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['post_id'])) {
        $lines[] = 'Contact post ID: ' . (int) $report['post_id'];
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

function scoop_contact_migrator_resolve_post_id(): int
{
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-templates/contact-template.php',
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
function scoop_contact_migrator_resolve(int $post_id, string $legacy_key, $default)
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
 * Image helper kept for consistency (Contact v1 has no images).
 *
 * @param array $report
 */
function scoop_contact_migrator_legacy_image_id(int $post_id, string $legacy_key, array &$report, string $label): ?int
{
    if (function_exists('scoop_homepage_migrator_legacy_image_id')) {
        return scoop_homepage_migrator_legacy_image_id($post_id, $legacy_key, $report, $label);
    }
    return null;
}

/**
 * @return list<array>
 */
function scoop_contact_migrator_build_rows(int $post_id, array &$report): array
{
    // Contact v1 has no images; $report kept for sideload helper parity with sister migrators.
    $title_default = get_the_title($post_id);
    if ($title_default === '') {
        $title_default = 'Contact Us';
    }

    $show_cards = scoop_contact_migrator_resolve($post_id, 'show_contact_cards', 1);
    if ($show_cards === true) {
        $show_cards = 1;
    } elseif ($show_cards === false) {
        $show_cards = 0;
    } else {
        $show_cards = (int) $show_cards ? 1 : 0;
    }

    $fluent_id = scoop_contact_migrator_resolve($post_id, 'fluent_form_id', 0);
    $fluent_id = is_numeric($fluent_id) ? (int) $fluent_id : 0;

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => scoop_contact_migrator_resolve($post_id, 'contact_heading', $title_default),
            'description'   => scoop_contact_migrator_resolve(
                $post_id,
                'contact_description',
                "We'd love to hear from you regarding the competition. Our team is ready to answer any questions."
            ),
            'eyebrow_label' => scoop_contact_migrator_resolve($post_id, 'contact_subheading', ''),
            'eyebrow_icon'  => '',
        ],
        [
            'acf_fc_layout'            => 'NeraContact',
            'get_in_touch_heading'     => 'Get in Touch',
            'get_in_touch_description' => "Have questions about our competitions? We're here to help.",
            'show_contact_cards'       => $show_cards,
            'contact_address'          => scoop_contact_migrator_resolve(
                $post_id,
                'contact_address',
                '123 Innovation Blvd, Tech City, TC 12345'
            ),
            'contact_email'            => scoop_contact_migrator_resolve($post_id, 'contact_email', 'support@competition.com'),
            'contact_phone'            => scoop_contact_migrator_resolve($post_id, 'contact_phone', '+1 (555) 012-3456'),
            'form_heading'             => scoop_contact_migrator_resolve($post_id, 'form_heading', 'Send Us a Message'),
            'form_description'         => scoop_contact_migrator_resolve($post_id, 'form_description', ''),
            'fluent_form_id'           => $fluent_id,
        ],
    ];
}

/**
 * @return array Report
 */
function scoop_migrate_contact_to_page_components(bool $force = false): array
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

    $post_id = scoop_contact_migrator_resolve_post_id();
    $report['post_id'] = $post_id;
    if ($post_id <= 0) {
        $report['error'] = 'Could not resolve Contact page (template page-templates/contact-template.php).';
        return $report;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $report;
    }

    $rows = scoop_contact_migrator_build_rows($post_id, $report);
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

function scoop_contact_migrator_token_option(): string
{
    return 'scoop_contact_migrator_token';
}

function scoop_contact_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_contact_migrator_token_option(), $token, false);
    return $token;
}

function scoop_contact_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_contact_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_contact_migrator_token_option());
    return true;
}

function scoop_contact_migrator_admin_url(bool $force = false): string
{
    $token = scoop_contact_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_contact_migrate',
        '_wpnonce' => wp_create_nonce('nera_contact_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_CONTACT_MIGRATOR') && NERA_ALLOW_CONTACT_MIGRATOR) {
    add_action('admin_post_scoop_contact_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_contact_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_contact_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_contact_to_page_components($force);
        scoop_contact_migrator_print_report($report);
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
        $url       = esc_url(scoop_contact_migrator_admin_url(false));
        $force_url = esc_url(scoop_contact_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Contact migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_CONTACT_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
