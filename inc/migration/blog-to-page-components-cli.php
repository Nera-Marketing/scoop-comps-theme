<?php
/**
 * CLI entry for the Blog → Page Components migrator.
 *
 * Usage (Local PHP on Windows):
 *   php -c %APPDATA%\Local\run\<siteId>\conf\php\php.ini blog-to-page-components-cli.php [--force]
 *
 * @package Scoop_Competitions_Child
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$force = in_array('--force', $argv, true);

// …/wp-content/themes/scoop-competitions-child/inc/migration → …/app/public
$wp_root = dirname(__DIR__, 5);
if (!is_readable($wp_root . '/wp-load.php')) {
    fwrite(STDERR, "Could not locate wp-load.php at {$wp_root}\n");
    exit(1);
}

$site_root = dirname($wp_root);
$db_host   = '127.0.0.1:10108';
$sites_json = '';
if (getenv('APPDATA')) {
    $sites_json = getenv('APPDATA') . '/Local/sites.json';
} elseif (getenv('USERPROFILE')) {
    $sites_json = getenv('USERPROFILE') . '/AppData/Roaming/Local/sites.json';
}
if ($sites_json && is_readable($sites_json)) {
    $sites = json_decode((string) file_get_contents($sites_json), true);
    $expect = str_replace('\\', '/', $site_root);
    if (is_array($sites)) {
        foreach ($sites as $site) {
            if (!is_array($site)) {
                continue;
            }
            $path = str_replace('\\', '/', (string) ($site['path'] ?? ''));
            if ($path !== $expect) {
                continue;
            }
            $ports = $site['services']['mysql']['ports']['MYSQL'] ?? null;
            if (is_array($ports) && !empty($ports[0])) {
                $db_host = '127.0.0.1:' . (int) $ports[0];
            }
            break;
        }
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST', $db_host);
}

require_once $wp_root . '/wp-load.php';
require_once dirname(__FILE__) . '/blog-to-page-components.php';

$report = scoop_migrate_blog_to_page_components($force);
scoop_blog_migrator_print_report($report);
exit(!empty($report['ok']) ? 0 : 1);
