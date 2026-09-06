<?php
/**
 * Template Name: Nera Contact Page
 * Template Post Type: page
 *
 * Contact page template — child fork
 *
 * FORKED FROM: nera-competitions-standard/page-templates/contact-template.php @ v1.3.11
 * WordPress discovers page templates child-first, so this file wholly replaces
 * the parent's for pages assigned "Nera Contact Page".
 *
 * WHY A FORK: after Contact → Page Components, section twigs are `<section>` only.
 * Parent `Contact/template.twig` owned `<main id="main" class="nera-contact-page
 * bg-gray-50" role="main">`. Once that layout is split/hidden, this template is
 * the sole source of the main landmark and page background on the Page Components
 * path. See docs/adr/0003-fork-contact-template-in-child.md.
 *
 * DIVERGENCE FROM PARENT — keep this list current when re-diffing:
 *   1. When `page_components` has rows, wrap `nera_render_page_components()` in
 *      `<main id="main" class="nera-contact-page bg-gray-50" role="main">`
 *      (attributes copied verbatim from Contact/template.twig).
 *   2. Legacy fallback still calls `nera_render_component('Contact')` with no
 *      extra `<main>` — parent Contact Twig already owns one.
 * Everything else (Template Name header, get_header/get_footer) matches the parent.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit();
}

get_header();

$rows = function_exists('get_field') ? get_field('page_components') : null;
if (is_array($rows) && !empty($rows)) :
    ?>
  <main id="main" class="nera-contact-page bg-gray-50" role="main">
    <?php nera_render_page_components(); ?>
  </main>
    <?php
else:
    nera_render_component('Contact');
endif;

get_footer();
