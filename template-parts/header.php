<?php
/**
 * Header — child fork
 *
 * FORKED FROM: nera-competitions-standard/template-parts/header.php @ v1.3.11
 * WordPress resolves `get_template_part('template-parts/header')` child-first, so
 * this file wholly replaces the parent's.
 *
 * WHY A FORK: the parent hardcodes `!max-w-28` on the logo <img>, which Tailwind v4
 * compiles to `max-width: … !important` INSIDE `@layer utilities`. Per CSS Cascade 5,
 * layer order is compared *before* specificity, and for important declarations the
 * order reverses — so an unlayered `!important` rule in child-brand.css can never win,
 * at any specificity. Removing the class is the only way to size the logo from
 * ordinary CSS.
 *
 * DIVERGENCE FROM PARENT — keep this list current when re-diffing:
 *   1. Logo <img> class is `object-contain` only; the parent's `w-auto !max-w-28`
 *      is dropped. Sizing now lives in child-brand.css (`#site-header img`).
 * Everything else is a verbatim copy — nav, CTA buttons, cart badges, mobile menu
 * markup and the inline mobile-menu <script> — and should be re-synced from the
 * parent whenever it releases.
 *
 * DO NOT RENAME OR RE-NEST these three spans:
 *   span.nera-header-cart-count-desktop-wrapper
 *   span.nera-header-cart-count-mobile-wrapper
 *   span.nera-header-cart-count-mobile-nav-wrapper
 * The parent registers them as `woocommerce_add_to_cart_fragments` targets by exact
 * CSS selector (parent functions.php ~1905-1937). Changing them silently breaks the
 * AJAX cart counter on add-to-cart.
 *
 * @package Scoop_Competitions_Child
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

// Get site info
$site_name = get_bloginfo('name');
$site_url = home_url('/');

// CTA labels/URLs (get_theme_mod defaults; no Customizer UI — override via child theme or filters if needed)
$cta_secondary_text = get_theme_mod('nera_header_cta_secondary_text', __('Sign In', 'nera-competitions'));
$cta_secondary_logged_in_text = get_theme_mod('nera_header_cta_secondary_logged_in_text', __('My Account', 'nera-competitions'));
$cta_secondary_url = get_theme_mod('nera_header_cta_secondary_url', '');
$cta_primary_text = get_theme_mod('nera_header_cta_primary_text', __('Enter Now', 'nera-competitions'));
$cta_primary_url = get_theme_mod('nera_header_cta_primary_url', '');
$cta_show_arrow = get_theme_mod('nera_header_cta_show_arrow', true);

// Build URLs with WooCommerce fallbacks
if (empty($cta_secondary_url)) {
  $cta_secondary_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
}
if (empty($cta_primary_url)) {
  $cta_primary_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/shop/');
}

// Logged-in user URL for secondary CTA
$cta_secondary_logged_in_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/');
?>

<header class="sticky top-0 z-50 overflow-visible bg-surface/95 backdrop-blur-sm border-b border-gray-100 shadow-sm py-2" id="site-header">
  <div class="max-w-7xl mx-auto px-4 lg:px-0">
    <nav class="flex items-center justify-between h-16 lg:h-20">

      <!-- Logo -->
      <a href="<?php echo esc_url($site_url); ?>"
        class="flex items-center gap-2 text-primary font-bold text-xl lg:text-2xl">
        <?php if (has_custom_logo()): ?>
          <?php
          $custom_logo_id = get_theme_mod('custom_logo');
          $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
          if ($logo):
            ?>
            <?php // Sized by `#site-header img` in child-brand.css — see the fork note above. ?>
            <img src="<?php echo esc_url($logo[0]); ?>" alt="<?php echo esc_attr($site_name); ?>"
              class="object-contain">
          <?php endif; ?>
        <?php else: ?>
          <span class="text-2xl">🏆</span>
          <span class="font-heading">
            <?php echo esc_html($site_name); ?>
          </span>
        <?php endif; ?>
      </a>

      <!-- Desktop Navigation -->
      <div class="relative hidden lg:flex items-center gap-8 overflow-visible">
        <?php
        if (has_nav_menu('primary-menu')) {
          wp_nav_menu(array(
            'theme_location' => 'primary-menu',
            'container' => false,
            'menu_class' => 'flex items-center gap-8 overflow-visible',
            'fallback_cb' => false,
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'link_before' => '',
            'link_after' => '',
            'depth' => 2,
            'walker' => new Nera_Header_Menu_Walker(),
          ));
        } else {
          // Fallback menu if no menu is assigned
          ?>
          <a href="#competitions" class="text-text-secondary hover:text-primary font-medium transition-colors">
            <?php _e('Competitions', 'nera-competitions'); ?>
          </a>
          <a href="#testimonials" class="text-text-secondary hover:text-primary font-medium transition-colors">
            <?php _e('Testimonials', 'nera-competitions'); ?>
          </a>
          <a href="#faq" class="text-text-secondary hover:text-primary font-medium transition-colors">
            <?php _e('FAQs', 'nera-competitions'); ?>
          </a>
          <a href="#how-it-works" class="text-text-secondary hover:text-primary font-medium transition-colors">
            <?php _e('How It Works', 'nera-competitions'); ?>
          </a>
        <?php } ?>
      </div>

      <!-- CTA Buttons -->
      <div class="hidden lg:flex items-center gap-4">
        <!-- Cart Button -->
        <?php if (function_exists('wc_get_cart_url')): ?>
          <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
            class="relative text-text-secondary hover:text-primary transition-colors p-2" aria-label="View Cart">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
            <span class="nera-header-cart-count-desktop-wrapper absolute -top-1 -right-1">
              <?php if ($cart_count > 0): ?>
                <span
                  class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                  <?php echo esc_html($cart_count); ?>
                </span>
              <?php endif; ?>
            </span>
          </a>
        <?php endif; ?>

        <!-- My Account Icon Button -->
        <?php if (is_user_logged_in()): ?>
          <a href="<?php echo esc_url($cta_secondary_logged_in_url); ?>"
            class="relative flex items-center justify-center text-primary hover:opacity-90 transition-all p-1.5 rounded-full ring-2 ring-primary/20 bg-primary/5"
            aria-label="<?php echo esc_attr($cta_secondary_logged_in_text); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </a>
        <?php else: ?>
          <a href="<?php echo esc_url($cta_secondary_url); ?>"
            class="relative text-text-secondary hover:text-primary transition-colors p-2"
            aria-label="<?php echo esc_attr($cta_secondary_text); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </a>
        <?php endif; ?>

        <!-- <a href="<?php echo esc_url($cta_primary_url); ?>"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-primary text-white font-semibold rounded-xl hover:opacity-90 transition-opacity shadow-md">
          <?php echo esc_html($cta_primary_text); ?>
          <?php if ($cta_show_arrow): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
          <?php endif; ?>
        </a> -->
      </div>

      <!-- Mobile: Cart + Hamburger -->
      <div class="flex lg:hidden items-center gap-1">
        <?php if (function_exists('wc_get_cart_url')): ?>
          <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
            class="relative text-text-secondary hover:text-primary transition-colors p-2" aria-label="View Cart">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
            <span class="nera-header-cart-count-mobile-nav-wrapper absolute -top-1 -right-1">
              <?php if ($cart_count > 0): ?>
                <span
                  class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                  <?php echo esc_html($cart_count); ?>
                </span>
              <?php endif; ?>
            </span>
          </a>
        <?php endif; ?>
        <button type="button" class="p-2 text-text-primary hover:text-primary transition-colors"
          id="mobile-menu-toggle" aria-label="Toggle menu">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            id="menu-icon-open">
            <path d="M3 12h18M3 6h18M3 18h18" />
          </svg>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="hidden" id="menu-icon-close">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
      </div>

    </nav>
  </div>

  <!-- Mobile Menu -->
  <div class="lg:hidden hidden bg-surface border-t border-gray-100 shadow-lg" id="mobile-menu">
    <div class="px-4 py-6 space-y-4">
      <?php
      if (has_nav_menu('primary-menu')) {
        wp_nav_menu(array(
          'theme_location' => 'primary-menu',
          'container' => false,
          'menu_class' => 'space-y-2 nera-mobile-primary-nav',
          'fallback_cb' => false,
          'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
          'depth' => 2,
          'walker' => new Nera_Mobile_Menu_Walker(),
        ));
      } else {
        // Fallback menu if no menu is assigned
        ?>
        <a href="#competitions" class="block text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <?php _e('Competitions', 'nera-competitions'); ?>
        </a>
        <a href="#testimonials" class="block text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <?php _e('Testimonials', 'nera-competitions'); ?>
        </a>
        <a href="#faq" class="block text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <?php _e('FAQs', 'nera-competitions'); ?>
        </a>
        <a href="#how-it-works" class="block text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <?php _e('How It Works', 'nera-competitions'); ?>
        </a>
      <?php } ?>

      <hr class="border-gray-200">

      <!-- Cart Button -->
      <?php if (function_exists('wc_get_cart_url')): ?>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
          class="flex items-center justify-between text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <span class="flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php _e('Cart', 'nera-competitions'); ?>
          </span>
          <?php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
          <span class="nera-header-cart-count-mobile-wrapper">
            <?php if ($cart_count > 0): ?>
              <span class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                <?php echo esc_html($cart_count); ?>
              </span>
            <?php endif; ?>
          </span>
        </a>
      <?php endif; ?>

      <!-- My Account Button -->
      <?php if (is_user_logged_in()): ?>
        <a href="<?php echo esc_url($cta_secondary_logged_in_url); ?>"
          class="flex items-center gap-2 text-primary font-medium py-2 transition-colors hover:opacity-90">
          <span class="flex items-center justify-center p-1.5 rounded-full ring-2 ring-primary/20 bg-primary/5">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </span>
          <?php echo esc_html($cta_secondary_logged_in_text); ?>
        </a>
      <?php else: ?>
        <a href="<?php echo esc_url($cta_secondary_url); ?>"
          class="flex items-center gap-2 text-text-secondary hover:text-primary font-medium py-2 transition-colors">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <?php echo esc_html($cta_secondary_text); ?>
        </a>
      <?php endif; ?>

      <a href="<?php echo esc_url($cta_primary_url); ?>"
        class="block w-full text-center px-6 py-3 bg-gradient-primary text-white font-semibold rounded-xl hover:opacity-90 transition-opacity shadow-md">
        <?php echo esc_html($cta_primary_text); ?>
      </a>
    </div>
  </div>
</header>

<script>
  // Mobile menu toggle with enhanced animations
  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('mobile-menu-toggle');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('menu-icon-open');
    const iconClose = document.getElementById('menu-icon-close');
    let isAnimating = false;

    if (toggle && menu) {
      // Toggle menu open/close
      toggle.addEventListener('click', function () {
        if (isAnimating) return; // Prevent rapid clicking during animation

        const isHidden = menu.classList.contains('hidden');

        if (isHidden) {
          // Opening animation
          menu.classList.remove('hidden');
          menu.classList.add('menu-opening');
          menu.classList.remove('menu-closing');

          // Animate icons
          iconOpen.classList.add('rotating');
          setTimeout(() => {
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
            iconClose.classList.add('rotating');
            setTimeout(() => {
              iconOpen.classList.remove('rotating');
              iconClose.classList.remove('rotating');
            }, 100);
          }, 150);

          isAnimating = true;
          setTimeout(() => {
            menu.classList.remove('menu-opening');
            isAnimating = false;
          }, 300);
        } else {
          // Closing animation
          menu.classList.add('menu-closing');
          menu.classList.remove('menu-opening');

          // Animate icons
          iconClose.classList.add('rotating');
          setTimeout(() => {
            iconClose.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconOpen.classList.add('rotating');
            setTimeout(() => {
              iconOpen.classList.remove('rotating');
              iconClose.classList.remove('rotating');
            }, 100);
          }, 150);

          isAnimating = true;
          setTimeout(() => {
            menu.classList.add('hidden');
            menu.classList.remove('menu-closing');
            isAnimating = false;
          }, 300);
        }
      });

      // Mobile: expand/collapse second-level submenu (delegated)
      menu.addEventListener('click', function (e) {
        const btn = e.target.closest('.nera-mobile-submenu-toggle');
        if (!btn || !menu.contains(btn)) {
          return;
        }
        e.preventDefault();
        const subId = btn.getAttribute('aria-controls');
        const sub = subId ? document.getElementById(subId) : null;
        if (!sub) {
          return;
        }
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        sub.classList.toggle('hidden', expanded);
        if (expanded) {
          sub.setAttribute('hidden', '');
        } else {
          sub.removeAttribute('hidden');
        }
      });

      // Close menu when clicking a navigational link (not submenu toggles)
      menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          if (isAnimating) return;

          menu.classList.add('menu-closing');
          menu.classList.remove('menu-opening');

          // Animate icons
          iconClose.classList.add('rotating');
          setTimeout(() => {
            iconClose.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconOpen.classList.add('rotating');
            setTimeout(() => {
              iconOpen.classList.remove('rotating');
              iconClose.classList.remove('rotating');
            }, 100);
          }, 150);

          isAnimating = true;
          setTimeout(() => {
            menu.classList.add('hidden');
            menu.classList.remove('menu-closing');
            isAnimating = false;
          }, 300);
        });
      });
    }
  });
</script>