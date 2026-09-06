<?php
/**
 * Blog posts index (when a static front page is set and a "Posts page" is assigned).
 *
 * FORKED FROM: nera-competitions-standard/home.php @ v1.3.11
 * WordPress always renders the posts index through home.php and ignores any page
 * template assigned to the posts page — so there is no no-fork route. This child
 * copy wholly replaces the parent's. See docs/adr/0004-fork-home-php-in-child.md.
 *
 * WHY A FORK: after Blog → Page Components, rows live on the posts *page*
 * (`page_for_posts`). Bare `nera_render_page_components()` / `get_field()` would
 * resolve against the loop's first post and silently return nothing. This fork
 * reads `page_components` with an explicit posts-page ID and iterates layouts.
 *
 * DIVERGENCE FROM PARENT — keep this list current when re-diffing:
 *   1. When the posts page has `page_components` rows, resolve them via
 *      `get_field('page_components', $blog_page_id)` and render each layout
 *      with `nera_render_component()` — never the bare helper (no post-ID arg).
 *   2. Carry `<main id="main" class="nera-blog-home" role="main">` across on
 *      both the Page Components path and the legacy fallback.
 *   3. Legacy fallback (empty rows / no posts page) is the parent's verbatim
 *      page-hero + blog/loop branch so an unmigrated site still renders.
 * Everything else (get_header/get_footer) matches the parent.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();

$blog_page_id = (int) get_option('page_for_posts');
$rows = ($blog_page_id && function_exists('get_field'))
    ? get_field('page_components', $blog_page_id)
    : null;

if (is_array($rows) && !empty($rows)) : ?>
  <main id="main" class="nera-blog-home" role="main">
    <?php
    foreach ($rows as $row) {
        if (!empty($row['acf_fc_layout'])) {
            nera_render_component($row['acf_fc_layout'], ['acf_row' => $row]);
        }
    }
    ?>
  </main>
<?php else :
  $_blog_title = $blog_page_id ? get_the_title($blog_page_id) : '';
  $_blog_title = $_blog_title ?: __('Blog', 'nera-competitions');
  $_blog_desc  = $blog_page_id ? get_the_excerpt($blog_page_id) : '';
  ?>

<main id="main" class="nera-blog-home" role="main">
    <?php
    get_template_part('template-parts/components/shared/page-hero', null, [
        'title'         => $_blog_title,
        'description'   => $_blog_desc,
    ]);
    ?>
    <div class="bg-background-secondary min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-10 md:py-12">

            <?php get_template_part('template-parts/blog/loop'); ?>

        </div>
    </div>
</main>

<?php
endif;

get_footer();
