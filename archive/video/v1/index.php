<?php
/* ARCHIVED 15 Oct 2026: product tour ("video") v1, as it ran on the home page #tour.
   View at /pharmacy/archive/video/v1/. Uses this folder's own copies of the markup (product-tour.php), script
   (tour.js) and styles (tour.css), so later changes to the live tour don't alter it. Key frames: frames/*.png.
   ?tour-at=15 freezes it at 15 s. Reference only, noindex. */
$P = __DIR__ . '/../../../partials';
require_once $P . '/config.php';
$title    = page_title('Archive: product tour v1');
$page_key = 'archive';
$noindex  = true;
$extra_css = ['../archive/video/v1/tour.css'];
$extra_js  = ['../archive/video/v1/tour.js'];
$hide_cta  = true;
require $P . '/head.php';
require $P . '/header.php';
?>
<main id="main">
  <section class="section-tight"><div class="container">
    <p class="meta-line">Archived 15 Oct 2026 · product tour v1 (home page). Key frames are in <code>archive/video/v1/frames/</code>.</p>
  </div></section>
  <?php require __DIR__ . '/product-tour.php'; ?>
</main>
<?php require $P . '/footer.php';
