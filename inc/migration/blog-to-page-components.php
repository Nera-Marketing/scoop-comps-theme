<?php
/**
 * Blog migrator: seed Page Components on the posts page
 * (NeraPageHero + NeraBlogList).
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_BLOG_MIGRATOR. CLI entry: blog-to-page-components-cli.php
 *
 * There is no legacy ACF group for the blog index — hero title/description come
 * from the posts page itself; empty-state copy is seeded from blog/loop.php.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $report
 */
function scoop_blog_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: Blog migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['post_id'])) {
        $lines[] = 'Posts page ID: ' . (int) $report['post_id'];
    }
    if (!empty($report['layouts'])) {
        $lines[] = 'Layouts written: ' . implode(', ', $report['layouts']);
    }
    if (!empty($report['force'])) {
        $lines[] = 'Force: yes';
    }
    echo implode("\n", $lines) . "\n";
}

/**
 * Resolve the WordPress posts page. Refuse when unset or not a page.
 */
function scoop_blog_migrator_resolve_post_id(): int
{
    $id = (int) get_option('page_for_posts');
    if ($id <= 0) {
        return 0;
    }
    $post = get_post($id);
    if (!$post instanceof WP_Post || $post->post_type !== 'page') {
        return 0;
    }
    return $id;
}

/**
 * @return list<array>
 */
function scoop_blog_migrator_build_rows(int $post_id): array
{
    $title = get_the_title($post_id);
    if ($title === '') {
        $title = 'Blog';
    }
    $description = get_the_excerpt($post_id);

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => $title,
            'description'   => $description,
            'eyebrow_label' => '',
            'eyebrow_icon'  => '',
        ],
        [
            'acf_fc_layout' => 'NeraBlogList',
            // Leave featured empty — component falls back to the latest post.
            'featured_post' => null,
            'empty_title'   => 'No posts found',
            'empty_text'    => "It seems we can't find what you're looking for.",
        ],
    ];
}

/**
 * @return array Report
 */
function scoop_migrate_blog_to_page_components(bool $force = false): array
{
    $report = [
        'ok'      => false,
        'force'   => $force,
        'post_id' => 0,
        'layouts' => [],
        'error'   => null,
    ];

    if (!function_exists('update_field') || !function_exists('get_field')) {
        $report['error'] = 'ACF is not available.';
        return $report;
    }

    $post_id = scoop_blog_migrator_resolve_post_id();
    $report['post_id'] = $post_id;
    if ($post_id <= 0) {
        $report['error'] = 'Could not resolve posts page (Settings → Reading → Posts page). Refusing to write to post 0.';
        return $report;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $report['error'] = 'page_components already has rows; pass force to overwrite.';
        return $report;
    }

    $rows = scoop_blog_migrator_build_rows($post_id);
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

function scoop_blog_migrator_token_option(): string
{
    return 'scoop_blog_migrator_token';
}

function scoop_blog_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_blog_migrator_token_option(), $token, false);
    return $token;
}

function scoop_blog_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_blog_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_blog_migrator_token_option());
    return true;
}

function scoop_blog_migrator_admin_url(bool $force = false): string
{
    $token = scoop_blog_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_blog_migrate',
        '_wpnonce' => wp_create_nonce('nera_blog_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_BLOG_MIGRATOR') && NERA_ALLOW_BLOG_MIGRATOR) {
    add_action('admin_post_scoop_blog_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_blog_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_blog_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_blog_to_page_components($force);
        scoop_blog_migrator_print_report($report);
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
        $url       = esc_url(scoop_blog_migrator_admin_url(false));
        $force_url = esc_url(scoop_blog_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Blog migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_BLOG_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
