<?php
/** Site header with mega-menus. Requires head.php (config loaded). Uses $page_key for the active state. */
function nav_item_link(array $it): string {
  [$key, $label, $desc, $icon] = $it;
  return '<a class="mm-item" href="' . e(url($key)) . '"><span class="mm-ic"><svg class="icon" aria-hidden="true"><use href="#' . $icon . '"/></svg></span>'
       . '<span><b>' . e($label) . '</b><small>' . e($desc) . '</small></span></a>';
}
$active = function (array $keys) use ($page_key) { return in_array($page_key, $keys, true) ? ' is-active' : ''; };
?>
<a class="skip" href="#main">Skip to main content</a>
<div class="review-banner" role="note">Review mode: outlined items need product-owner confirmation before launch.</div>

<header class="hd">
  <div class="container hd-row">
    <a class="brand" href="<?= url('home') ?>" aria-label="Home"><?= logo() ?></a>

    <nav class="hd-nav" id="hd-nav" aria-label="Primary">
      <ul class="hd-menu">
        <li class="hd-has">
          <button class="hd-link<?= $active(['features','billing','expiry','inventory','purchase','gst','reports','retention','security']) ?>" type="button" aria-expanded="false" aria-controls="mm-features">Features<svg class="icon chev" aria-hidden="true"><use href="#i-chev"/></svg></button>
          <div class="mm mm-wide" id="mm-features" hidden>
            <div class="mm-cols">
              <?php foreach (NAV_FEATURES as $group => $items): ?>
                <div class="mm-col">
                  <p class="mm-h"><?= e($group) ?></p>
                  <?php foreach ($items as $it) echo nav_item_link($it); ?>
                </div>
              <?php endforeach; ?>
              <aside class="mm-promo">
                <img src="<?= asset('img/v2-02-dashboard.jpg') ?>" alt="Sample pharmacy dashboard" loading="lazy" width="640" height="400">
                <p><b>Your whole pharmacy on one screen</b>Sales, expiry, stock alerts and collections, updated with every bill.</p>
                <a class="link-arrow" href="<?= url('features') ?>">See all features <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
              </aside>
            </div>
          </div>
        </li>
        <li class="hd-has">
          <button class="hd-link<?= $active(['retail','chain','hospital','new-pharmacy']) ?>" type="button" aria-expanded="false" aria-controls="mm-solutions">Solutions<svg class="icon chev" aria-hidden="true"><use href="#i-chev"/></svg></button>
          <div class="mm" id="mm-solutions" hidden>
            <div class="mm-col"><?php foreach (NAV_SOLUTIONS as $it) echo nav_item_link($it); ?></div>
          </div>
        </li>
        <li><a class="hd-link<?= $active(['pricing']) ?>" href="<?= url('pricing') ?>">Pricing</a></li>
        <li><a class="hd-link<?= $active(['switch']) ?>" href="<?= url('switch') ?>">Switch</a></li>
        <li class="hd-has">
          <button class="hd-link<?= $active(['calculator','checklist','guides','blog','faq','support']) ?>" type="button" aria-expanded="false" aria-controls="mm-resources">Resources<svg class="icon chev" aria-hidden="true"><use href="#i-chev"/></svg></button>
          <div class="mm" id="mm-resources" hidden>
            <div class="mm-col"><?php foreach (NAV_RESOURCES as $it) echo nav_item_link($it); ?></div>
          </div>
        </li>
      </ul>
      <div class="hd-mobile-actions">
        <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo">Book a free demo</a>
        <a class="btn btn-secondary" href="<?= e(SITE['app_signup']) ?>">Start free</a>
        <a class="hd-login" href="<?= e(SITE['app_login']) ?>">Log in</a>
      </div>
    </nav>

    <div class="hd-actions">
      <a class="hd-login" href="<?= e(SITE['app_login']) ?>">Log in</a>
      <a class="btn btn-secondary btn-sm" href="<?= e(SITE['app_signup']) ?>">Start free</a>
      <a class="btn btn-primary btn-sm" href="<?= url('demo') ?>" data-cta="demo">Book a free demo</a>
      <button class="hd-burger" type="button" aria-expanded="false" aria-controls="hd-nav" aria-label="Open menu"><svg class="icon" aria-hidden="true"><use href="#i-menu"/></svg></button>
    </div>
  </div>
</header>
