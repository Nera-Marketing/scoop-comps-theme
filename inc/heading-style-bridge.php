<?php
/**
 * Inject heading-style context for child-only About Us layouts.
 *
 * Parent nera_inject_heading_style() only recognises a hardcoded section list
 * (includes AboutUsPage, not NeraAboutUs*). Bridge via nera_component_data.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('nera_component_data', function ($data, $name, $args) {
    // Only layouts that ship heading-style fields + use them in Twig.
    static $own = [
        'NeraAboutUsHero' => true,
    ];
    if (!isset($own[$name]) || !function_exists('nera_resolve_heading_style')) {
        return $data;
    }
    $hs               = nera_resolve_heading_style(is_array($args) ? $args : []);
    $accent           = $hs['accent_color'] !== '' ? sanitize_hex_color($hs['accent_color']) : '';
    $accent_color     = $accent ?: 'var(--heading-accent)';
    $highlight_font   = $hs['font_family'] !== '' ? $hs['font_family'] : 'var(--heading-highlight-font)';
    $highlight_weight = $hs['font_weight'] !== '' ? (int) $hs['font_weight'] : 'var(--heading-highlight-weight)';

    $data['heading_highlight']    = $hs['highlight'];
    $data['heading_accent_style'] = 'color: ' . $accent_color
        . '; font-family: ' . $highlight_font
        . '; font-weight: ' . $highlight_weight;
    return $data;
}, 10, 3);
