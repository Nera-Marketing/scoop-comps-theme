/**
 * Get The Scoop — hide the WordPress admin bar while the page is scrolled.
 *
 * The bar is only useful at rest; while reading it just steals 32px and covers
 * the top of the sticky site header (which is `sticky top-0`, i.e. pinned to
 * viewport y=0, underneath the bar's `position: fixed`).
 *
 * Behaviour: visible only at the top of the document, hidden everywhere else.
 * There is deliberately no scroll-direction tracking — a directional
 * show-on-scroll-up flickers while adjusting position on long prize grids.
 *
 * We move the bar with `transform` and never touch the `html { margin-top }`
 * that core prints in wp-includes/admin-bar.php. That margin lives at the top
 * of the *document*, so once you have scrolled it is already off-screen and the
 * bar can slide away leaving no gap and causing no layout shift.
 *
 * Below 600px core already sets `#wpadminbar { position: absolute }`, so the bar
 * scrolls out of view unaided; this module is a harmless no-op there.
 */
(function () {
  'use strict';

  // Matches the `#wpadminbar.scoop-adminbar--hidden` rule in child-brand.css.
  const HIDDEN_CLASS = 'scoop-adminbar--hidden';

  // Small tolerance rather than `> 0` so sub-pixel and rubber-band scroll
  // positions do not toggle the bar while the page is visually at rest.
  const TOP_THRESHOLD = 4;

  function initAdminBarScroll() {
    const bar = document.getElementById('wpadminbar');

    // Logged-out visitors have no admin bar — nothing to do, and no listener.
    if (!bar) return;

    let ticking = false;

    function update() {
      ticking = false;
      bar.classList.toggle(HIDDEN_CLASS, window.scrollY >= TOP_THRESHOLD);
    }

    function onScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    // Set the correct state for a page loaded already scrolled (anchor link,
    // browser scroll restoration on back-navigation).
    update();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminBarScroll);
  } else {
    initAdminBarScroll();
  }
})();
