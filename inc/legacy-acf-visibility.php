<?php
/**
 * Hide legacy ACF groups when the page is driven by Page Components.
 *
 * Parent `inc/legacy-acf-visibility.php` hides About Us / How It Works only —
 * not `group_homepage_content`, `group_contact_page`, winners groups, or the
 * listing-family groups. Those must be hidden here; do not edit the parent.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', function () {
    global $post;
    if (!$post instanceof WP_Post || $post->post_type !== 'page') {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    $rows = get_field('page_components', $post->ID);
    if (!is_array($rows) || empty($rows)) {
        return;
    }

    remove_meta_box('acf-group_homepage_content', 'page', 'normal');
    remove_meta_box('acf-group_contact_page', 'page', 'normal');
    remove_meta_box('acf-group_winners_page', 'page', 'normal');
    remove_meta_box('acf-group_winners_dynamic_settings', 'page', 'normal');
    remove_meta_box('acf-group_product_listing', 'page', 'normal');
    remove_meta_box('acf-group_nera_shop_listing', 'page', 'normal');
    remove_meta_box('acf-group_closed_prizes_page', 'page', 'normal');
    remove_meta_box('acf-group_entry_list_listing_page', 'page', 'normal');
}, 20);
