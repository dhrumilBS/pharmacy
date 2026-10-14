<?php
/**
 * "Trusted by" logo marquee: SVG logo files in assets/img/trusted/ shown as <img> with alt text (crawlable).
 * Scrolls continuously (CSS, home.css .trust-track) and never pauses: no hover/focus pause, and it keeps moving
 * under prefers-reduced-motion. Owner decision; it conflicts with WCAG 2.2.2 (Pause, Stop, Hide), revisit before launch.
 *
 * PLACEHOLDERS: invented businesses so the layout can be reviewed (built by scripts/make-trusted-logos.ps1).
 * Replace each SVG with the real customer's single-colour logo (with written permission), update the list
 * below, and keep the real business name in 'alt'. The whole strip is data-confirm.
 */
$trusted = [
  // [file in assets/img/trusted, business name for alt text]
  ['sanjivani-medicos',  'Sanjivani Medicos'],
  ['kaveri-pharma',      'Kaveri Pharma'],
  ['arogya-chemists',    'Arogya Chemists'],
  ['nilgiri-healthcare', 'Nilgiri Healthcare'],
  ['vardhan-pharma',     'Vardhan Pharma Distributors'],
  ['lifeline-pharmacy',  'Lifeline Pharmacy'],
  ['shree-ram-medical',  'Shree Ram Medical Hall'],
  ['medisetu',           'Medisetu'],
  ['dhanvantari-drug',   'Dhanvantari Drug House'],
  ['pragati-medical',    'Pragati Medical Stores'],
];
$logo_row = function (bool $copy) use ($trusted) {
  foreach ($trusted as [$file, $name]) {
    $path = __DIR__ . '/../assets/img/trusted/' . $file . '.svg';
    $w = preg_match("/width='(\d+)'/", (string) @file_get_contents($path), $m) ? (int) $m[1] : 200;
    // The duplicate row (for the seamless loop) is hidden from assistive tech and has empty alt text
    echo '<li><img src="' . asset('img/trusted/' . $file . '.svg') . '" width="' . $w . '" height="40" loading="lazy" decoding="async" alt="'
       . ($copy ? '' : e($name) . ' logo') . '"></li>';
  }
};
?>
<section class="trust" aria-labelledby="trust-t" data-confirm>
  <div class="container trust-head">
    <h2 id="trust-t">Billing every day at medical stores, chains &amp; hospital pharmacies</h2>
    <span class="trust-note">A few of them</span>
  </div>
  <div class="trust-marquee">
    <!-- Two identical rows; the track moves by exactly one row width, so the loop is seamless. -->
    <div class="trust-track">
      <ul class="trust-row"><?php $logo_row(false); ?></ul>
      <ul class="trust-row" aria-hidden="true"><?php $logo_row(true); ?></ul>
    </div>
  </div>
</section>
