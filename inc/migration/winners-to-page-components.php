<?php
/**
 * Winners migrator: Legacy winners templates → Page Components
 * (NeraPageHero + NeraWinnersList) for Manual, Dynamic, and Entry List pages.
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_WINNERS_MIGRATOR. CLI entry: winners-to-page-components-cli.php
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return list<string>
 */
function scoop_winners_migrator_templates(): array
{
    return [
        'page-templates/winners-template.php',
        'page-templates/winners-dynamic-template.php',
        'page-templates/winners-entry-list-template.php',
    ];
}

/**
 * @param array $report
 */
function scoop_winners_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: Winners migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['pages']) && is_array($report['pages'])) {
        foreach ($report['pages'] as $page) {
            if (!is_array($page)) {
                continue;
            }
            $status = !empty($page['ok']) ? 'ok' : ('fail: ' . ($page['error'] ?? '?'));
            $layouts = !empty($page['layouts']) && is_array($page['layouts'])
                ? implode(', ', $page['layouts'])
                : '';
            $lines[] = sprintf(
                '  page %d (%s) [%s] %s%s',
                (int) ($page['post_id'] ?? 0),
                (string) ($page['template'] ?? ''),
                (string) ($page['title'] ?? ''),
                $status,
                $layouts !== '' ? ' → ' . $layouts : ''
            );
        }
    }
    if (!empty($report['force'])) {
        $lines[] = 'Force: yes';
    }
    echo implode("\n", $lines) . "\n";
}

/**
 * @return list<array{post_id:int,template:string,title:string}>
 */
function scoop_winners_migrator_resolve_pages(): array
{
    $out = [];
    foreach (scoop_winners_migrator_templates() as $template) {
        $pages = get_posts([
            'post_type'      => 'page',
            'post_status'    => ['publish', 'draft', 'private'],
            'posts_per_page' => 20,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => $template,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ]);
        foreach ($pages as $page) {
            if (!$page instanceof WP_Post) {
                continue;
            }
            $out[] = [
                'post_id'  => (int) $page->ID,
                'template' => $template,
                'title'    => (string) $page->post_title,
            ];
        }
    }
    return $out;
}

/**
 * @param mixed $default
 * @return mixed
 */
function scoop_winners_migrator_resolve(int $post_id, string $legacy_key, $default)
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
 * @return list<array>
 */
function scoop_winners_migrator_build_rows(int $post_id, string $template): array
{
    $is_manual = $template === 'page-templates/winners-template.php';

    if ($is_manual) {
        $title_default = 'Recent Winners';
        $title = scoop_winners_migrator_resolve($post_id, 'winners_heading', $title_default);
        $description = scoop_winners_migrator_resolve($post_id, 'winners_description', '');
        $eyebrow = scoop_winners_migrator_resolve($post_id, 'winners_subheading', 'Our Lucky Winners');

        $empty_title = scoop_winners_migrator_resolve($post_id, 'winners_empty_heading', 'No Winners Yet');
        $empty_text = scoop_winners_migrator_resolve(
            $post_id,
            'winners_empty_description',
            'Check back soon to see our lucky winners!'
        );
    } else {
        $title = get_the_title($post_id);
        if ($title === '') {
            $title = 'Winners';
        }

        $excerpt = '';
        $post = get_post($post_id);
        if ($post instanceof WP_Post) {
            $excerpt = trim((string) $post->post_excerpt);
        }
        if ($excerpt === '') {
            $excerpt = 'Celebrate our latest winners from finished competitions.';
        }
        $description = $excerpt;
        $eyebrow = 'Winners';

        if (function_exists('nera_get_winners_dynamic_empty_copy')) {
            $empty = nera_get_winners_dynamic_empty_copy($post_id);
            $empty_title = is_array($empty) && isset($empty['heading'])
                ? (string) $empty['heading']
                : 'No winners to show yet';
            $empty_text = is_array($empty) && isset($empty['description'])
                ? (string) $empty['description']
                : 'Winners appear here once competitions have ended and winners are selected in the giveaway settings.';
        } else {
            $empty_title = scoop_winners_migrator_resolve(
                $post_id,
                'winners_dynamic_empty_heading',
                'No winners to show yet'
            );
            $empty_text = scoop_winners_migrator_resolve(
                $post_id,
                'winners_dynamic_empty_description',
                'Winners appear here once competitions have ended and winners are selected in the giveaway settings.'
            );
        }
    }

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => $title,
            'description'   => $description,
            'eyebrow_label' => $eyebrow,
            'eyebrow_icon'  => 'emoji_events',
        ],
        [
            'acf_fc_layout' => 'NeraWinnersList',
            'empty_title'   => $empty_title,
            'empty_text'    => $empty_text,
        ],
    ];
}

/**
 * Migrate one winners page.
 *
 * @return array{ok:bool,post_id:int,template:string,title:string,layouts:list<string>,error:?string}
 */
function scoop_winners_migrator_migrate_page(int $post_id, string $template, string $title, bool $force): array
{
    $page_report = [
        'ok'       => false,
        'post_id'  => $post_id,
        'template' => $template,
        'title'    => $title,
        'layouts'  => [],
        'error'    => null,
    ];

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $page_report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $page_report;
    }

    $rows = scoop_winners_migrator_build_rows($post_id, $template);
    foreach ($rows as $row) {
        if (!empty($row['acf_fc_layout'])) {
            $page_report['layouts'][] = $row['acf_fc_layout'];
        }
    }

    if (empty($rows)) {
        $page_report['error'] = 'No layout rows to write.';
        return $page_report;
    }

    $ok = update_field('page_components', $rows, $post_id);
    if (!$ok) {
        $check = get_field('page_components', $post_id);
        if (!is_array($check) || empty($check)) {
            $page_report['error'] = 'update_field(page_components) failed.';
            return $page_report;
        }
    }

    $page_report['ok'] = true;
    return $page_report;
}

/**
 * @return array Report
 */
function scoop_migrate_winners_to_page_components(bool $force = false): array
{
    $report = [
        'ok'     => false,
        'force'  => $force,
        'pages'  => [],
        'error'  => null,
    ];

    if (!function_exists('update_field') || !function_exists('get_field')) {
        $report['error'] = 'ACF is not available.';
        return $report;
    }

    $pages = scoop_winners_migrator_resolve_pages();
    if (empty($pages)) {
        $report['error'] = 'No winners pages found (Manual / Dynamic / Entry List templates).';
        return $report;
    }

    $any_ok = false;
    $any_fail = false;
    foreach ($pages as $page) {
        $page_report = scoop_winners_migrator_migrate_page(
            (int) $page['post_id'],
            (string) $page['template'],
            (string) $page['title'],
            $force
        );
        $report['pages'][] = $page_report;
        if (!empty($page_report['ok'])) {
            $any_ok = true;
        } else {
            $any_fail = true;
        }
    }

    if (!$any_ok) {
        $report['error'] = 'No winners pages were migrated.';
        return $report;
    }

    $report['ok'] = !$any_fail;
    if ($any_fail) {
        $report['error'] = 'Some winners pages failed; see per-page lines.';
        // Partial success still useful — keep ok false but pages listed.
        $report['ok'] = false;
    }

    return $report;
}

function scoop_winners_migrator_token_option(): string
{
    return 'scoop_winners_migrator_token';
}

function scoop_winners_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_winners_migrator_token_option(), $token, false);
    return $token;
}

function scoop_winners_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_winners_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_winners_migrator_token_option());
    return true;
}

function scoop_winners_migrator_admin_url(bool $force = false): string
{
    $token = scoop_winners_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_winners_migrate',
        '_wpnonce' => wp_create_nonce('nera_winners_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_WINNERS_MIGRATOR') && NERA_ALLOW_WINNERS_MIGRATOR) {
    add_action('admin_post_scoop_winners_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_winners_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_winners_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_winners_to_page_components($force);
        scoop_winners_migrator_print_report($report);
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
        $url       = esc_url(scoop_winners_migrator_admin_url(false));
        $force_url = esc_url(scoop_winners_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Winners migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_WINNERS_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
