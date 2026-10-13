<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Contact us: sales, support and office');
$description = 'Contact us: sales +91 97148 74435, pharmacy support +91 89808 80819, help centre and our Surat office.';
$page_key    = 'contact';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Contact', 'parent' => 'Company', 'eyebrow' => 'Contact',
  'h1' => 'Talk to a person, not a ticket queue',
  'lead' => 'Call us, book a demo, or visit our office. Pick whatever is quickest for you.'];
$map = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Millenium Point, Lal Darwaja, Surat, Gujarat 395003');
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight">
  <div class="container grid-3">
    <article class="info-card">
      <div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg></div>
      <h2>Sales</h2><p>Plans, pricing, chains and hospital pharmacies.</p>
      <a class="big-link" href="tel:<?= SITE['sales_tel'] ?>"><?= e(SITE['sales_phone']) ?></a>
      <p><a class="link-arrow" href="<?= url('demo') ?>" data-cta="demo">Book a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>
    </article>
    <article class="info-card">
      <div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-headset"/></svg></div>
      <h2>Support</h2><p>For stores already using our software.</p>
      <a class="big-link" href="tel:<?= SITE['support_tel'] ?>"><?= e(SITE['support_phone']) ?></a>
      <p><a class="link-arrow" href="<?= e(SITE['help_url']) ?>">Open the help centre <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>
      <p class="meta-line" data-confirm>Support hours: [confirm before publishing]</p>
    </article>
    <article class="info-card">
      <div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg></div>
      <h2>Pick a time</h2><p>Choose a slot for a call with our team.</p>
      <a class="big-link" href="<?= e(SITE['calendly']) ?>" rel="noopener">Open the calendar</a>
      <?php if (SITE['email']): ?><p>Or email <a href="mailto:<?= e(SITE['email']) ?>"><?= e(SITE['email']) ?></a></p><?php endif; ?>
    </article>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container grid-2" style="align-items:start">
    <div>
      <span class="eyebrow">Office</span>
      <h2 style="font-size:clamp(26px,3vw,34px)">Visit us in Surat</h2>
      <p style="margin-top:14px;color:var(--text-2);font-size:17px"><?= e(SITE['address']) ?></p>
      <p style="margin-top:16px"><a class="btn btn-secondary" href="<?= e($map) ?>" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>Open in Google Maps</a></p>
      <?php if (SITE['social']): ?>
      <h3 style="font-size:17px;margin-top:32px">Follow us</h3>
      <p style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px 18px">
        <?php foreach (SITE['social'] as $name => $href): ?><a href="<?= e($href) ?>" rel="noopener"><?= e($name) ?></a><?php endforeach; ?>
      </p>
      <?php endif; ?>
    </div>
    <?php $form_title = 'Send us your details'; $form_sub = 'We’ll call you back. For urgent support, please phone instead.'; require __DIR__ . '/partials/demo-form.php'; ?>
  </div>
</section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
