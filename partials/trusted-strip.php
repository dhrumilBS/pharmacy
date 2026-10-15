<?php
/**
 * "Trusted by" logo marquee: SVG logo files in assets/img/trusted/ shown as <img> with alt text (crawlable).
 * Scrolls continuously (CSS, home.css .trust-track) and never pauses: no hover/focus pause, and it keeps moving
 * under prefers-reduced-motion. Owner decision; it conflicts with WCAG 2.2.2 (Pause, Stop, Hide), revisit before launch.
 *
 * HOW TO SWAP IN REAL LOGOS (no code change): put each customer's single-colour logo (SVG preferred, or PNG/WebP;
 * with their written permission) in assets/img/trusted/ and delete the placeholder files. The strip lists every
 * image in that folder in filename order, and builds the alt text from the filename:
 *   "green-cross-pharmacy.svg" -> alt "Green Cross Pharmacy logo". Name files after the real business.
 * Logos render 40px tall. The current files are INVENTED placeholders (made by scripts/make-trusted-logos.ps1), and
 * the whole strip stays data-confirm until they are replaced.
 */
$trusted = [];
foreach (glob(__DIR__ . '/../assets/img/trusted/*.{svg,png,webp,jpg,jpeg}', GLOB_BRACE) ?: [] as $path) {
  $file = basename($path);
  $name = ucwords(trim(preg_replace('/[-_]+/', ' ', pathinfo($file, PATHINFO_FILENAME))));
  if (preg_match('/\.svg$/i', $file)) {
    $w = preg_match('/\bwidth=[\'"](\d+)/', (string) @file_get_contents($path, false, null, 0, 600), $m) ? (int) $m[1] : 160;
  } else {
    $size = @getimagesize($path);
    $w = $size ? (int) round($size[0] * 40 / max(1, $size[1])) : 160;   // keep the aspect ratio at 40px tall
  }
  $trusted[] = [$file, $name, $w];
}
sort($trusted);
$logo_row = function (bool $copy) use ($trusted) {
  foreach ($trusted as [$file, $name, $w]) {
    // The duplicate row (for the seamless loop) is hidden from assistive tech and has empty alt text
    echo '<li><img src="' . asset('img/trusted/' . rawurlencode($file)) . '" width="' . $w . '" height="40" loading="lazy" decoding="async" alt="'
       . ($copy ? '' : e($name) . ' logo') . '"></li>';
  }
};
if (!$trusted) return;   // nothing to show: hide the whole strip rather than render an empty band
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
