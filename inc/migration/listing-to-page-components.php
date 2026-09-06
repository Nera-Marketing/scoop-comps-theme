<?php
/**
 * Competition listing pages migrator → Page Components.
 *
 * Targets (resolved by template / WC page ID, not slug):
 *   - Nera Product Listing  → NeraPageHero + NeraCompetitionsList + NeraWhyChooseUs
 *   - WooCommerce Shop      → same inventory (shop-template-bridge)
 *   - Nera Closed Prizes    → NeraPageHero + NeraClosedPrizesList
 *   - Nera Entry List       → NeraPageHero + NeraEntryList
 *
 * Loaded from the child theme. HTTP admin-post registration is gated by
 * NERA_ALLOW_LISTING_MIGRATOR. CLI entry: listing-to-page-components-cli.php
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $report
 */
function scoop_listing_migrator_print_report(array $report): void
{
    $lines = [];
    $lines[] = !empty($report['ok']) ? 'OK: Listing migration complete.' : 'FAIL: ' . ($report['error'] ?? 'unknown error');
    if (!empty($report['pages']) && is_array($report['pages'])) {
        foreach ($report['pages'] as $page) {
            if (!is_array($page)) {
                continue;
            }
            $label = (string) ($page['label'] ?? 'page');
            if (!empty($page['skipped'])) {
                $lines[] = sprintf('%s: skipped (%s)', $label, (string) ($page['reason'] ?? 'n/a'));
                continue;
            }
            if (!empty($page['error'])) {
                $lines[] = sprintf('%s: FAIL — %s', $label, (string) $page['error']);
                continue;
            }
            $id = (int) ($page['post_id'] ?? 0);
            $layouts = !empty($page['layouts']) && is_array($page['layouts'])
                ? implode(', ', $page['layouts'])
                : '';
            $lines[] = sprintf('%s: post %d — %s', $label, $id, $layouts);
        }
    }
    if (!empty($report['force'])) {
        $lines[] = 'Force: yes';
    }
    echo implode("\n", $lines) . "\n";
}

/**
 * @param mixed $default
 * @return mixed
 */
function scoop_listing_migrator_resolve(int $post_id, string $legacy_key, $default)
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
 * @return list<int>
 */
function scoop_listing_migrator_pages_by_template(string $template): array
{
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => $template,
        'fields'         => 'ids',
    ]);
    return array_map('intval', $pages ?: []);
}

/**
 * Default Why Choose Us items (SVG + copy from trust-features.php).
 *
 * @return list<array{icon: string, title: string, description: string}>
 */
function scoop_listing_migrator_default_trust_items(): array
{
    return [
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
            'title'       => 'Secure Checkout',
            'description' => 'Your payment details are protected with bank-level encryption.',
        ],
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            'title'       => 'Certified Winners',
            'description' => 'All draws are independently verified and transparent.',
        ],
        [
            'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
            'title'       => '24/7 Support',
            'description' => 'Our friendly team is here to help anytime you need.',
        ],
    ];
}

/**
 * @return list<array{icon: string, title: string, description: string}>
 */
function scoop_listing_migrator_trust_items(int $post_id): array
{
    $items = [];
    if (function_exists('have_rows') && have_rows('trust_badges', $post_id)) {
        while (have_rows('trust_badges', $post_id)) {
            the_row();
            $title = (string) get_sub_field('title');
            if ($title === '') {
                continue;
            }
            $items[] = [
                'icon'        => (string) get_sub_field('icon'),
                'title'       => $title,
                'description' => (string) get_sub_field('description'),
            ];
        }
    }
    return $items !== [] ? $items : scoop_listing_migrator_default_trust_items();
}

/**
 * @return list<array>
 */
function scoop_listing_migrator_build_product_listing_rows(int $post_id): array
{
    $title = get_the_title($post_id);
    if ($title === '') {
        $title = 'Competitions';
    }
    $excerpt = get_post_field('post_excerpt', $post_id);
    $excerpt = is_string($excerpt) ? trim($excerpt) : '';
    if ($excerpt === '') {
        $excerpt = 'Enter to win amazing prizes with our exclusive competitions.';
    }

    $empty_title = scoop_listing_migrator_resolve($post_id, 'product_listing_empty_heading', 'No competitions found');
    $empty_text  = scoop_listing_migrator_resolve(
        $post_id,
        'product_listing_empty_description',
        'Check back soon for new amazing prizes!'
    );

    $trust_title = scoop_listing_migrator_resolve($post_id, 'trust_title', 'Why Choose Us');
    $trust_subtitle = scoop_listing_migrator_resolve(
        $post_id,
        'trust_subtitle',
        'Join thousands of happy winners who trust us for fair and exciting competitions.'
    );

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => $title,
            'description'   => $excerpt,
            'variant'       => 'default',
            'eyebrow_label' => '',
            'eyebrow_icon'  => '',
        ],
        [
            'acf_fc_layout' => 'NeraCompetitionsList',
            'empty_title'   => is_string($empty_title) ? $empty_title : 'No competitions found',
            'empty_text'    => is_string($empty_text) ? $empty_text : 'Check back soon for new amazing prizes!',
        ],
        [
            'acf_fc_layout' => 'NeraWhyChooseUs',
            'title'         => is_string($trust_title) ? $trust_title : 'Why Choose Us',
            'subtitle'      => is_string($trust_subtitle) ? $trust_subtitle : 'Join thousands of happy winners who trust us for fair and exciting competitions.',
            'items'         => scoop_listing_migrator_trust_items($post_id),
        ],
    ];
}

/**
 * @return list<array>
 */
function scoop_listing_migrator_build_closed_prizes_rows(int $post_id): array
{
    $title = get_the_title($post_id);
    if ($title === '') {
        $title = 'Closed Prizes';
    }
    $excerpt = get_post_field('post_excerpt', $post_id);
    $excerpt = is_string($excerpt) ? trim($excerpt) : '';
    if ($excerpt === '') {
        $excerpt = 'Browse our past competitions and see the lucky winners.';
    }

    $empty_title = scoop_listing_migrator_resolve($post_id, 'closed_prizes_empty_heading', 'No closed prizes yet');
    $empty_text  = scoop_listing_migrator_resolve(
        $post_id,
        'closed_prizes_empty_description',
        'Check back after our competitions have drawn their winners.'
    );

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => $title,
            'description'   => $excerpt,
            'variant'       => 'compact',
            'eyebrow_label' => 'Past Competitions',
            'eyebrow_icon'  => 'trophy',
        ],
        [
            'acf_fc_layout' => 'NeraClosedPrizesList',
            'empty_title'   => is_string($empty_title) ? $empty_title : 'No closed prizes yet',
            'empty_text'    => is_string($empty_text) ? $empty_text : 'Check back after our competitions have drawn their winners.',
        ],
    ];
}

/**
 * @return list<array>
 */
function scoop_listing_migrator_build_entry_list_rows(int $post_id): array
{
    $title = get_the_title($post_id);
    if ($title === '') {
        $title = 'Entry List';
    }
    $excerpt = get_post_field('post_excerpt', $post_id);
    $excerpt = is_string($excerpt) ? trim($excerpt) : '';
    if ($excerpt === '') {
        $excerpt = 'Browse every competition and view its participant list in one place.';
    }

    $empty_title = scoop_listing_migrator_resolve($post_id, 'entry_list_empty_heading', 'No competitions found');
    $empty_text  = scoop_listing_migrator_resolve(
        $post_id,
        'entry_list_empty_description',
        'There are no participant lists available yet. Please check back soon.'
    );

    return [
        [
            'acf_fc_layout' => 'NeraPageHero',
            'title'         => $title,
            'description'   => $excerpt,
            'variant'       => 'compact',
            'eyebrow_label' => 'Competition Participants',
            'eyebrow_icon'  => 'groups',
        ],
        [
            'acf_fc_layout' => 'NeraEntryList',
            'empty_title'   => is_string($empty_title) ? $empty_title : 'No competitions found',
            'empty_text'    => is_string($empty_text) ? $empty_text : 'There are no participant lists available yet. Please check back soon.',
        ],
    ];
}

/**
 * @param callable $builder
 * @return array{label: string, post_id: int, layouts?: list<string>, error?: string, skipped?: bool, reason?: string}
 */
function scoop_listing_migrator_write_page(string $label, int $post_id, callable $builder, bool $force): array
{
    $page = [
        'label'   => $label,
        'post_id' => $post_id,
    ];

    if ($post_id <= 0) {
        $page['skipped'] = true;
        $page['reason']  = 'page not found';
        return $page;
    }

    $existing = get_field('page_components', $post_id);
    if (is_array($existing) && !empty($existing) && !$force) {
        $page['error'] = 'page_components already has rows; pass force to overwrite.';
        return $page;
    }

    $rows = $builder($post_id);
    if (!is_array($rows) || $rows === []) {
        $page['error'] = 'No layout rows to write.';
        return $page;
    }

    $layouts = [];
    foreach ($rows as $row) {
        if (!empty($row['acf_fc_layout'])) {
            $layouts[] = (string) $row['acf_fc_layout'];
        }
    }
    $page['layouts'] = $layouts;

    $ok = update_field('page_components', $rows, $post_id);
    if (!$ok) {
        $check = get_field('page_components', $post_id);
        if (!is_array($check) || empty($check)) {
            $page['error'] = 'update_field(page_components) failed.';
            return $page;
        }
    }

    return $page;
}

/**
 * @return array Report
 */
function scoop_migrate_listing_to_page_components(bool $force = false): array
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

    $product_ids = scoop_listing_migrator_pages_by_template('page-templates/product-listing-template.php');
    if ($product_ids === []) {
        $report['pages'][] = [
            'label'   => 'Product Listing',
            'post_id' => 0,
            'skipped' => true,
            'reason'  => 'no page with product-listing-template.php',
        ];
    } else {
        foreach ($product_ids as $pid) {
            $report['pages'][] = scoop_listing_migrator_write_page(
                'Product Listing',
                $pid,
                'scoop_listing_migrator_build_product_listing_rows',
                $force
            );
        }
    }

    $shop_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0;
    if ($shop_id > 0) {
        $report['pages'][] = scoop_listing_migrator_write_page(
            'Shop',
            $shop_id,
            'scoop_listing_migrator_build_product_listing_rows',
            $force
        );
    } else {
        $report['pages'][] = [
            'label'   => 'Shop',
            'post_id' => 0,
            'skipped' => true,
            'reason'  => 'WooCommerce shop page not set',
        ];
    }

    $closed_ids = scoop_listing_migrator_pages_by_template('page-templates/closed-prizes-template.php');
    if ($closed_ids === []) {
        $report['pages'][] = [
            'label'   => 'Closed Prizes',
            'post_id' => 0,
            'skipped' => true,
            'reason'  => 'no page with closed-prizes-template.php',
        ];
    } else {
        foreach ($closed_ids as $pid) {
            $report['pages'][] = scoop_listing_migrator_write_page(
                'Closed Prizes',
                $pid,
                'scoop_listing_migrator_build_closed_prizes_rows',
                $force
            );
        }
    }

    $entry_ids = scoop_listing_migrator_pages_by_template('page-templates/entry-list-listing-template.php');
    if ($entry_ids === []) {
        // Auto-applied template: fall back to the Lottery entry-list page ID.
        $entry_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('lty_lottery_entry_list') : 0;
        if ($entry_id > 0) {
            $entry_ids = [$entry_id];
        }
    }
    if ($entry_ids === []) {
        $report['pages'][] = [
            'label'   => 'Entry List',
            'post_id' => 0,
            'skipped' => true,
            'reason'  => 'no entry-list page found',
        ];
    } else {
        foreach ($entry_ids as $pid) {
            $report['pages'][] = scoop_listing_migrator_write_page(
                'Entry List',
                $pid,
                'scoop_listing_migrator_build_entry_list_rows',
                $force
            );
        }
    }

    $wrote_any = false;
    $hard_fail = false;
    foreach ($report['pages'] as $page) {
        if (!empty($page['error'])) {
            $hard_fail = true;
        }
        if (empty($page['skipped']) && empty($page['error']) && !empty($page['layouts'])) {
            $wrote_any = true;
        }
    }

    if ($hard_fail && !$wrote_any) {
        $report['error'] = 'All writable targets failed; see per-page errors.';
        return $report;
    }

    if (!$wrote_any) {
        $report['error'] = 'No listing pages were migrated (none found, or all already had rows).';
        return $report;
    }

    $report['ok'] = true;
    if ($hard_fail) {
        $report['error'] = 'Partial success — some pages failed; see per-page errors.';
    }

    return $report;
}

function scoop_listing_migrator_token_option(): string
{
    return 'scoop_listing_migrator_token';
}

function scoop_listing_migrator_issue_token(): string
{
    $token = bin2hex(random_bytes(16));
    update_option(scoop_listing_migrator_token_option(), $token, false);
    return $token;
}

function scoop_listing_migrator_consume_token(string $provided): bool
{
    $stored = (string) get_option(scoop_listing_migrator_token_option(), '');
    if ($stored === '' || !hash_equals($stored, $provided)) {
        return false;
    }
    delete_option(scoop_listing_migrator_token_option());
    return true;
}

function scoop_listing_migrator_admin_url(bool $force = false): string
{
    $token = scoop_listing_migrator_issue_token();
    $args  = [
        'action'   => 'scoop_listing_migrate',
        '_wpnonce' => wp_create_nonce('nera_listing_migrate'),
        'token'    => $token,
    ];
    if ($force) {
        $args['force'] = '1';
    }
    return add_query_arg($args, admin_url('admin-post.php'));
}

if (defined('NERA_ALLOW_LISTING_MIGRATOR') && NERA_ALLOW_LISTING_MIGRATOR) {
    add_action('admin_post_scoop_listing_migrate', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? (string) wp_unslash($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'nera_listing_migrate')) {
            wp_die('Invalid nonce', 403);
        }
        $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';
        if (!scoop_listing_migrator_consume_token($token)) {
            wp_die('Invalid or reused token', 403);
        }
        $force = isset($_GET['force']) && (string) $_GET['force'] === '1';
        header('Content-Type: text/plain; charset=utf-8');
        $report = scoop_migrate_listing_to_page_components($force);
        scoop_listing_migrator_print_report($report);
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
        $url       = esc_url(scoop_listing_migrator_admin_url(false));
        $force_url = esc_url(scoop_listing_migrator_admin_url(true));
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Listing migrator enabled.</strong> ';
        echo '<a href="' . $url . '">Run migration</a> · ';
        echo '<a href="' . $force_url . '">Force overwrite</a>. ';
        echo 'Disable <code>NERA_ALLOW_LISTING_MIGRATOR</code> after use.';
        echo '</p></div>';
    });
}
