<?php
if (!defined('ABSPATH')) {
    exit;
}

define('SCOOP_COMPETITIONS_CHILD_VERSION', '1.0.0');

/**
 * Load child `inc/` files.
 *
 * Mirrors the parent's convention: `inc/acf/<page>/acf-<page>.php` holds pure ACF
 * field group registration, one file per admin screen. Files are required
 * unconditionally and must have no side-effects beyond registering hooks.
 *
 * Three globs rather than one, covering the depths this theme actually uses:
 *   inc/*.php               feature bootstrappers  (e.g. legacy-acf-visibility.php)
 *   inc/<dir>/*.php         grouped features       (e.g. migration/…, acf/…)
 *   inc/acf/<page>/acf-*.php  per-screen ACF groups (the original convention)
 *
 * A single narrow glob was a silent trap: anything dropped outside
 * `inc/acf/<dir>/acf-*.php` simply never loaded — no error, no hook registered,
 * the feature just did nothing. `require_once` makes the overlap between globs
 * harmless, and `sort()` keeps load order stable across platforms so ordering
 * bugs are reproducible rather than filesystem-dependent.
 *
 * `*-cli.php` is EXCLUDED and that exclusion is load-bearing. CLI entry points
 * bootstrap `wp-load.php` and guard with `if (PHP_SAPI !== 'cli') { exit(1); }`.
 * Auto-including one during a web request aborts `functions.php` mid-load and
 * white-screens the whole site. Entry-point scripts are *invoked*, never
 * *included* — keep the `-cli.php` suffix for anything meant to be run directly.
 */
foreach (['/inc/*.php', '/inc/*/*.php', '/inc/acf/*/acf-*.php'] as $scoop_inc_glob) {
    $scoop_inc_files = glob(get_stylesheet_directory() . $scoop_inc_glob) ?: [];
    sort($scoop_inc_files);
    foreach ($scoop_inc_files as $scoop_inc_file) {
        if (substr($scoop_inc_file, -8) === '-cli.php') {
            continue;
        }
        require_once $scoop_inc_file;
    }
}
unset($scoop_inc_glob, $scoop_inc_files, $scoop_inc_file);

require_once get_stylesheet_directory() . '/inc/legacy-acf-visibility.php';

// Homepage migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/homepage-to-page-components.php';

// About Us migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/about-us-to-page-components.php';

// Contact migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/contact-to-page-components.php';

// How It Works migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/how-it-works-to-page-components.php';

// Blog migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/blog-to-page-components.php';

// Winners migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/winners-to-page-components.php';

// Listing migrator: functions always loaded; HTTP endpoint only when constant is on.
require_once get_stylesheet_directory() . '/inc/migration/listing-to-page-components.php';

function scoop_competitions_child_category_colors(): array {
    return [];
}
add_filter('nera_competition_card_category_colors', 'scoop_competitions_child_category_colors');
add_filter('nera_advanced_filter_category_colors', 'scoop_competitions_child_category_colors');
add_filter('nera_competition_card_fallback_accent', fn() => '#5B9BD5');

function scoop_competitions_child_get_parent_vite_css_handle(): ?string {
    global $wp_styles;
    if (!$wp_styles instanceof WP_Styles) {
        return null;
    }
    $max = -1;
    foreach (array_keys($wp_styles->registered) as $handle) {
        if (preg_match('/^nera-vite-css-(\d+)$/', $handle, $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return $max >= 0 ? 'nera-vite-css-' . $max : null;
}

function scoop_competitions_child_parent_style_deps(): array {
    $handle = scoop_competitions_child_get_parent_vite_css_handle();
    return [$handle ?? 'nera-style'];
}

add_action('wp_enqueue_scripts', function () {
    $brand_path = get_stylesheet_directory() . '/child-brand.css';
    if (is_readable($brand_path)) {
        wp_enqueue_style(
            'scoop-competitions-child-brand',
            get_stylesheet_directory_uri() . '/child-brand.css',
            scoop_competitions_child_parent_style_deps(),
            (string) filemtime($brand_path)
        );
    }

    // Hide the WP admin bar while the page is scrolled. Enqueued only when the
    // bar is actually present, so logged-out visitors download nothing. Paired
    // with the `#wpadminbar` rules in child-brand.css.
    if (is_admin_bar_showing()) {
        $adminbar_js = get_stylesheet_directory() . '/assets/js/adminbar-scroll.js';
        if (is_readable($adminbar_js)) {
            wp_enqueue_script(
                'scoop-competitions-child-adminbar-scroll',
                get_stylesheet_directory_uri() . '/assets/js/adminbar-scroll.js',
                [],
                (string) filemtime($adminbar_js),
                true
            );
        }
    }

    $manifest_path = get_stylesheet_directory() . '/frontend/dist/.vite/manifest.json';
    if (!is_readable($manifest_path)) {
        return;
    }
    $manifest = json_decode(file_get_contents($manifest_path), true);
    if (empty($manifest['src/main.js'])) {
        return;
    }
    $entry   = $manifest['src/main.js'];
    $base_uri = get_stylesheet_directory_uri() . '/frontend/dist/';
    $deps    = scoop_competitions_child_parent_style_deps();

    if (!empty($entry['css'])) {
        foreach ($entry['css'] as $i => $chunk) {
            wp_enqueue_style(
                'scoop-competitions-child-vite-css-' . $i,
                $base_uri . $chunk,
                $deps,
                null
            );
            $deps = ['scoop-competitions-child-vite-css-' . $i];
        }
    }

    if (!empty($entry['file'])) {
        wp_enqueue_script(
            'scoop-competitions-child-vite-js',
            $base_uri . $entry['file'],
            [],
            null,
            true
        );
    }
}, 100);
