<?php
/**
 * Footer — child fork
 *
 * FORKED FROM: nera-competitions-standard/template-parts/footer.php @ v1.3.11
 * WordPress resolves `get_template_part('template-parts/footer')` child-first,
 * so this file wholly replaces the parent's.
 *
 * WHY A FORK: the parent's brand column contains nothing but
 * `dynamic_sidebar('footer-1')`, and the parent template exposes no `do_action`
 * hooks at all. The NERA Competitions logo and lorem-ipsum text that shipped in
 * that column are *widget* content in the database, not template markup — so
 * neither a CSS override nor a `dynamic_sidebar_before` hook can remove them.
 * Replacing the widget call with code is the only way to guarantee they are gone
 * regardless of DB state. See docs/adr/0001-fork-footer-template-in-child.md.
 *
 * DIVERGENCE FROM PARENT — keep this list current when re-diffing:
 *   1. Brand column no longer renders `dynamic_sidebar('footer-1')`. It renders
 *      `the_custom_logo()`, the `scoop_footer_brand_text` ACF field, and social
 *      icons from the `scoop_footer_*` ACF URL fields.
 *   2. Fourth column no longer renders `dynamic_sidebar('footer-4')`. It renders
 *      the `scoop_footer_contact_{heading,phone,email}` ACF fields, reproducing the
 *      parent's widget wrapper markup so it matches columns 2 and 3.
 *   3. Dropped the parent's four `$*_url = get_theme_mod('nera_*_url', '#')`
 *      assignments. They were dead in the parent (assigned, never output) and
 *      have no admin UI, so any icon wired to them was permanently unsettable.
 *   4. Dropped the unused `$current_year` variable (the parent calls `date('Y')`
 *      inline in the copyright block anyway).
 * Everything else — the footer-2/3 columns, the dark bottom bar, the scroll-to-top
 * button — is a verbatim copy and should be re-synced from the parent on update.
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

$site_name = get_bloginfo('name');
$site_url  = home_url('/');

// Brand column content. `get_field()` is guarded because ACF is a plugin and can
// be deactivated without taking the whole footer down with it.
$brand_text = function_exists('get_field') ? (string) get_field('scoop_footer_brand_text', 'option') : '';

// Only networks with a URL set are rendered — no placeholder '#' links.
$social_links = [];
if (function_exists('get_field')) {
    $social_config = [
        'facebook'  => __('Facebook', 'scoop-comps'),
        'instagram' => __('Instagram', 'scoop-comps'),
        'twitter'   => __('X', 'scoop-comps'),
        'youtube'   => __('YouTube', 'scoop-comps'),
    ];
    foreach ($social_config as $network => $label) {
        $url = (string) get_field('scoop_footer_' . $network, 'option');
        if ($url !== '') {
            $social_links[$network] = ['url' => $url, 'label' => $label];
        }
    }
}

// Fourth column content, replacing the `footer-4` widget area.
$contact_heading = '';
$contact_phone   = '';
$contact_email   = '';
if (function_exists('get_field')) {
    $contact_heading = trim((string) get_field('scoop_footer_contact_heading', 'option'));
    $contact_phone   = trim((string) get_field('scoop_footer_contact_phone', 'option'));
    $contact_email   = trim((string) get_field('scoop_footer_contact_email', 'option'));
}

// The heading gets a real fallback because it is a structural label — a phone
// number sitting under no heading reads as broken. Brand text above deliberately
// gets none: substituting invented copy on a live site is how the inherited lorem
// ipsum arrived in the first place.
if ($contact_heading === '') {
    $contact_heading = __('Contact Us', 'scoop-comps');
}

// Suppress the whole column rather than leaving a lone heading behind.
$has_contact = ($contact_phone !== '' || $contact_email !== '');

// Inline SVG paths keyed by network — avoids depending on the parent's
// Material Symbols icon font for brand glyphs it does not provide.
$social_icon_paths = [
    'facebook'  => 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z',
    'instagram' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z',
    'twitter'   => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z',
    'youtube'   => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z',
];
?>

<footer class="ncs-site-footer bg-surface border-t border-gray-200" id="site-footer">

  <!-- Main Footer -->
  <div class="max-w-7xl mx-auto px-4 lg:px-0 py-12 lg:py-16 text-text-secondary">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">

      <!-- Brand Column (Spans 2) — code-driven; footer-1 widget intentionally not rendered -->
      <div class="lg:col-span-2">

        <?php if (has_custom_logo()): ?>
          <div class="mb-6">
            <?php the_custom_logo(); ?>
          </div>
        <?php else: ?>
          <a href="<?php echo esc_url($site_url); ?>"
            class="inline-block mb-6 font-heading text-xl font-bold text-text-primary">
            <?php echo esc_html($site_name); ?>
          </a>
        <?php endif; ?>

        <?php if ($brand_text !== ''): ?>
          <div class="ncs-site-footer__brand-text max-w-md text-sm leading-relaxed">
            <?php // Already paragraph-wrapped: the field declares 'new_lines' => 'wpautop'. ?>
            <?php echo wp_kses_post($brand_text); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($social_links)): ?>
          <ul class="mt-6 flex items-center gap-3" role="list">
            <?php foreach ($social_links as $network => $link): ?>
              <li>
                <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer"
                  class="flex size-9 items-center justify-center rounded-full border border-gray-200 text-text-secondary transition-colors hover:border-primary hover:text-primary"
                  aria-label="<?php echo esc_attr($link['label']); ?>">
                  <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                    <path d="<?php echo esc_attr($social_icon_paths[$network]); ?>" />
                  </svg>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      </div>

      <!-- Quick Links -->
      <div>
        <?php if (is_active_sidebar('footer-2')): ?>
          <?php dynamic_sidebar('footer-2'); ?>
        <?php endif; ?>
      </div>

      <!-- Support -->
      <div>
        <?php if (is_active_sidebar('footer-3')): ?>
          <?php dynamic_sidebar('footer-3'); ?>
        <?php endif; ?>
      </div>

      <!-- Contact — code-driven; footer-4 widget intentionally not rendered.
           Markup mirrors the parent's widget wrapper (`footer-widget` + the same
           <h4> classes from register_sidebar's before_title) so this column is
           visually indistinguishable from columns 2 and 3. -->
      <div>
        <?php if ($has_contact): ?>
          <div class="footer-widget">
            <h4 class="font-semibold text-text-primary mb-4 text-sm uppercase tracking-wide">
              <?php echo esc_html($contact_heading); ?>
            </h4>
            <ul class="space-y-2" role="list">
              <?php if ($contact_phone !== ''): ?>
                <li>
                  <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $contact_phone)); ?>"
                    class="hover:text-primary transition-colors">
                    <?php echo esc_html($contact_phone); ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($contact_email !== ''): ?>
                <li>
                  <?php
                  // antispambot() returns entity-encoded ASCII, which is already safe
                  // in both attribute and text contexts. Do NOT wrap it in esc_attr()
                  // or esc_html() — that double-encodes the entities and the address
                  // renders as literal `&#104;...`.
                  $safe_email = antispambot(sanitize_email($contact_email));
                  ?>
                  <a href="mailto:<?php echo $safe_email; ?>"
                    class="hover:text-primary transition-colors">
                    <?php echo $safe_email; ?>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Dark Bottom Bar -->
  <div class="bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 lg:px-0 py-4">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">

        <!-- Copyright -->
        <div class="text-text-secondary text-sm">
          <?php
          $copyright_text = function_exists('get_field') ? get_field('footer_copyright', 'option') : '';
          if ($copyright_text) {
            echo str_replace('{year}', date('Y'), $copyright_text);
          } else {
            echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. ' . __('All rights reserved.', 'nera-competitions');
          }
          ?>
        </div>

        <!-- Payment Methods / Bottom Right -->
        <div class="flex items-center gap-4">
          <?php
          $bottom_right = function_exists('get_field') ? get_field('footer_bottom_right', 'option') : '';
          if ($bottom_right) {
            echo $bottom_right;
          } else {
            // Default content if field is empty (matching original design as fallback)
            ?>
            <span class="text-text-secondary text-xs"><?php _e('Secure payments:', 'nera-competitions'); ?></span>
            <div class="text-gray-400 text-xs font-medium flex gap-2">
              <span>Visa</span> <span>Mastercard</span> <span>PayPal</span> <span>Apple Pay</span>
            </div>
            <?php
          }
          ?>
        </div>

      </div>
    </div>
  </div>

  <!-- Scroll to Top Button -->
  <button id="nera-scroll-top"
    class="fixed bottom-8 cursor-pointer right-8 z-[100] p-3 bg-background-dark text-white rounded-full shadow-xl hover:bg-black hover:shadow-xl hover:scale-110 hover:-translate-y-1 transform transition-all duration-300 translate-y-20 opacity-0 invisible border border-gray-800"
    aria-label="<?php esc_attr_e('Scroll to top', 'nera-competitions'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
      stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
  </button>

</footer>
