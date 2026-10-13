<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Book a free demo');
$description = 'See our pharmacy software working with medicines from your own shelf: billing, expiry report and purchase entry. Free demo on a call.';
$page_key    = 'demo';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main">
<section class="final" id="demo" aria-labelledby="demo-title" style="padding-top:72px">
  <div class="container final-grid">
    <div class="final-copy">
      <ol class="crumbs" aria-label="Breadcrumb" style="color:#9FB3C8"><li><a href="<?= url('home') ?>" style="color:#9FB3C8">Home</a></li><li aria-current="page">Book a demo</li></ol>
      <span class="eyebrow light">Free demo</span>
      <h1 id="demo-title" style="color:#fff;font-size:clamp(30px,4vw,46px)">See it working with medicines from your own shelf</h1>
      <p>A short demo on a call, at a time that suits you. No pressure, no obligation.</p>
      <ol class="next" data-confirm>
        <li><div><b>We call you within one working day</b>To understand your store and current software.</div></li>
        <li><div><b>You see your own items in the demo</b>Billing, expiry report and purchase entry, the way you’d use them.</div></li>
        <li><div><b>You pick the right plan</b>From the free Starter plan to Enterprise for chains and hospitals.</div></li>
      </ol>
      <p style="margin-top:28px">Prefer to choose a time yourself? <a href="<?= e(SITE['calendly']) ?>" style="color:#fff" rel="noopener">Pick a slot on our calendar</a>, or call sales on <a href="tel:<?= SITE['sales_tel'] ?>" style="color:#fff"><?= e(SITE['sales_phone']) ?></a>.</p>
    </div>
    <?php require __DIR__ . '/partials/demo-form.php'; ?>
  </div>
</section>
</main>
<?php $hide_cta = true; require __DIR__ . '/partials/footer.php'; ?>
