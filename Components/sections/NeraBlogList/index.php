<?php
namespace Nera\Components\NeraBlogList;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a parent blog post-card for a post ID (featured or standard).
 */
function render_post_card_html(int $post_id, string $variant): string
{
    $post = get_post($post_id);
    if (!$post instanceof \WP_Post || $post->post_type !== 'post') {
        return '';
    }

    $previous = $GLOBALS['post'] ?? null;
    $GLOBALS['post'] = $post;
    setup_postdata($post);

    ob_start();
    get_template_part('template-parts/blog/post-card', null, [
        'variant' => $variant === 'featured' ? 'featured' : 'standard',
    ]);
    $html = (string) ob_get_clean();

    if ($previous instanceof \WP_Post) {
        $GLOBALS['post'] = $previous;
        setup_postdata($previous);
    } else {
        wp_reset_postdata();
    }

    return $html;
}

/**
 * Query-driven blog listing: optional featured lead + grid + pagination.
 *
 * @param array $args
 * @return array{
 *   empty_title: string,
 *   empty_text: string,
 *   has_posts: bool,
 *   featured_html: string,
 *   cards: list<string>,
 *   navigation_html: string,
 * }
 */
function get_data(array $args = []): array
{
    $empty_title = (string) nera_component_field(
        $args,
        'empty_title',
        'empty_title',
        __('No posts found', 'nera-competitions')
    );
    $empty_text = (string) nera_component_field(
        $args,
        'empty_text',
        'empty_text',
        __('It seems we can\'t find what you\'re looking for.', 'nera-competitions')
    );

    $featured_raw = nera_component_field($args, 'featured_post', 'featured_post', 0);
    $featured_id  = is_numeric($featured_raw) ? (int) $featured_raw : 0;

    if ($featured_id > 0) {
        $featured_post = get_post($featured_id);
        if (!$featured_post instanceof \WP_Post || $featured_post->post_status !== 'publish' || $featured_post->post_type !== 'post') {
            $featured_id = 0;
        }
    }

    // Empty picker → most recent post (do not render with no lead when posts exist).
    if ($featured_id <= 0) {
        $latest = get_posts([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);
        $featured_id = !empty($latest[0]) ? (int) $latest[0] : 0;
    }

    $paged = (int) get_query_var('paged');
    if ($paged < 1) {
        $paged = (int) get_query_var('page');
    }
    $paged = max(1, $paged);

    $featured_html = '';
    if ($featured_id > 0 && $paged <= 1) {
        $featured_html = render_post_card_html($featured_id, 'featured');
    }

    $per_page = (int) get_option('posts_per_page');
    if ($per_page < 1) {
        $per_page = 10;
    }

    $query_args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $per_page,
        'paged'               => $paged,
        'ignore_sticky_posts' => true,
        'orderby'             => 'date',
        'order'               => 'DESC',
    ];
    // Exclude featured from the grid so it never appears twice.
    if ($featured_id > 0) {
        $query_args['post__not_in'] = [$featured_id];
    }

    $query = new \WP_Query($query_args);

    $card_ids = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            if ($p instanceof \WP_Post) {
                $card_ids[] = (int) $p->ID;
            }
        }
    }

    $cards = [];
    foreach ($card_ids as $card_id) {
        $html = render_post_card_html($card_id, 'standard');
        if ($html !== '') {
            $cards[] = $html;
        }
    }

    $navigation_html = '';
    if ($query->max_num_pages > 1) {
        global $wp_query;
        $saved_query = $wp_query;
        $wp_query    = $query;
        ob_start();
        get_template_part('template-parts/blog/posts-navigation');
        $navigation_html = (string) ob_get_clean();
        $wp_query = $saved_query;
    }

    wp_reset_postdata();

    $has_posts = ($featured_html !== '') || !empty($cards);

    return [
        'empty_title'      => $empty_title,
        'empty_text'       => $empty_text,
        'has_posts'        => $has_posts,
        'featured_html'    => $featured_html,
        'cards'            => $cards,
        'navigation_html'  => $navigation_html,
    ];
}
