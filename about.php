<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('About us');
$description = 'Pharmacy software built by a Surat-based team that also builds hospital and EMR software, for medical stores, pharmacy chains and hospital pharmacies.';
$page_key    = 'about';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'About', 'parent' => 'Company', 'eyebrow' => 'About us',
  'h1' => 'Pharmacy software from people who build healthcare software every day',
  'lead' => 'We also build hospital, EMR and clinic software. We built this one for the person standing at the counter.'];
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight">
  <div class="container grid-2" style="align-items:center">
    <div>
      <h2 style="font-size:clamp(26px,3vw,34px)">Why we built it</h2>
      <p style="margin-top:14px;color:var(--text-2);font-size:17px">Most medical stores already use something: a desktop package bought years ago, a general billing app, or registers. What owners tell us is the same: expired strips found too late, slow billing in the evening rush, distributor bills typed line by line at night, and month-end GST that eats a weekend.</p>
      <p style="margin-top:12px;color:var(--text-2);font-size:17px">Our software is the answer: batch-wise billing that sells the nearest expiry first, purchase entry by CSV or OCR, clean GST reports, and a free plan so you can try it without risk.</p>
    </div>
    <figure class="shot" style="margin:0"><?= img_tag('v2-02-dashboard', 1440, 900, 'Sample pharmacy dashboard', '(max-width: 900px) calc(100vw - 32px), 860px') ?></figure>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container">
    <div class="section-head"><span class="eyebrow">What we stand for</span><h2>Four promises we design around</h2></div>
    <div class="grid-2">
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-users"/></svg></div><h3>Built for the counter</h3><p>Every screen is designed for a busy medical store: keyboard shortcuts, batch auto-selection and in-app walkthroughs for new staff.</p></article>
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-rupee"/></svg></div><h3>Prices on the page</h3><p>Starter is free forever. Basic and Advance have published yearly prices. No guessing what year two will cost.</p></article>
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-upload"/></svg></div><h3>Switching without stopping</h3><p>Set up on the free plan, import your items and stock from CSV, and go live on the day you choose.</p></article>
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-hospital"/></svg></div><h3>Connected to the hospital</h3><p>For hospital pharmacies, prescriptions from the hospital’s EMR arrive at the counter as tokens, and patient ABHA records can be linked through ABDM.</p></article>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="section-head"><span class="eyebrow">At a glance</span><h2>The software today</h2></div>
    <div class="facts">
      <div class="fact"><b>4 plans</b><span>Starter (free), Basic, Advance and Enterprise</span></div>
      <div class="fact"><b>25+ reports</b><span>Sales, purchase, GST, margin, expiry, collections and more</span></div>
      <div class="fact"><b>ABDM</b><span>Integrated: create or link a patient’s ABHA</span></div>
      <div class="fact"><b>Surat, Gujarat</b><span>Where our team is based</span></div>
    </div>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container grid-3">
    <article class="info-card"><h3>Talk to sales</h3><p>Questions about plans, chains or hospital pharmacies.</p><a class="big-link" href="tel:<?= SITE['sales_tel'] ?>"><?= e(SITE['sales_phone']) ?></a></article>
    <article class="info-card"><h3>Get support</h3><p>Already a customer? Call support or open the help centre.</p><a class="big-link" href="tel:<?= SITE['support_tel'] ?>"><?= e(SITE['support_phone']) ?></a></article>
    <article class="info-card"><h3>Visit us</h3><p><?= e(SITE['address']) ?></p><a class="link-arrow" href="<?= url('contact') ?>" style="margin-top:12px">All contact details <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></article>
  </div>
</section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
