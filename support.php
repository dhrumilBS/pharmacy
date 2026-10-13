<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Support');
$description = 'Get help with our pharmacy software: phone support, the help centre, in-app walkthroughs and a getting-started guide for new stores.';
$page_key    = 'support';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Support', 'parent' => 'Company', 'eyebrow' => 'Support',
  'h1' => 'Help when the counter is busy',
  'lead' => 'Call us, open a ticket, or follow the step-by-step guides built into the app.'];
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight">
  <div class="container">
    <div class="channels four">
      <article class="channel"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-headset"/></svg></div><h3>Phone support</h3><p>Call <a href="tel:<?= SITE['support_tel'] ?>"><?= e(SITE['support_phone']) ?></a>. The same number is shown at the top of the app.</p></article>
      <article class="channel"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg></div><h3>Help centre</h3><p>Raise and track a ticket in the <a href="<?= e(SITE['help_url']) ?>">help centre</a>.</p></article>
      <article class="channel"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-play"/></svg></div><h3>In-app walkthroughs</h3><p>Step-by-step guides for sales, purchase, item master and account master, inside the app.</p></article>
      <article class="channel"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-screen"/></svg></div><h3>Keyboard shortcuts</h3><p>The shortcuts panel shows the keys for every screen, for example F2 to search and Ctrl+S to save.</p></article>
    </div>
    <p class="meta-line" data-confirm style="margin-top:16px">Support hours and languages: [confirm before publishing]. Priority support is included on the Enterprise plan.</p>
  </div>
</section>

<section class="section-tight bg-soft" id="getting-started">
  <div class="container switch-grid">
    <div class="switch-copy">
      <span class="eyebrow">New here?</span>
      <h2>Get your store ready in four steps</h2>
      <p>Most of the setup is done once: your items, opening stock and suppliers. After that, billing is search, pick, save.</p>
      <p style="margin-top:20px"><a class="btn btn-primary" href="<?= e(SITE['app_signup']) ?>">Start free</a> <a class="btn btn-secondary" href="<?= e(SITE['app_login']) ?>">Log in</a></p>
    </div>
    <ol class="steps">
      <li class="step"><div><h3>Create your store account</h3><p>Sign up with your store name, address and email, and verify with an OTP.</p></div></li>
      <li class="step"><div><h3>Add your items and opening stock</h3><p>Use Initialization → Add Initial Data to import a CSV. Download the sample sheet, match your columns, and fix any rows that are flagged.</p></div></li>
      <li class="step"><div><h3>Add suppliers and users</h3><p>Add distributors in Account Master, then add staff in Users and give each a role.</p></div></li>
      <li class="step"><div><h3>Bill your first customer</h3><p>Open Sale, search an item (F2), let the batch fill in, choose the payment method and save &amp; print (Ctrl+S).</p></div></li>
    </ol>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="section-head"><h2>Popular topics</h2><p>Detailed articles live in the help centre.</p></div>
    <div class="grid-3">
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>Importing items &amp; stock (CSV)</h3><p>Column mapping, sample sheet, fixing rejected rows.</p></a>
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>Purchase entry &amp; OCR import</h3><p>Distributor bills, scheme and free quantity, credits.</p></a>
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>Printing &amp; barcode labels</h3><p>Print settings, thermal and A4/A5 formats, label print.</p></a>
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>GST &amp; Tally reports</h3><p>GST report, bill-wise tax report, Tally XML (Enterprise).</p></a>
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>Users, roles &amp; security</h3><p>Privileges, two-step login, IP whitelisting.</p></a>
      <a class="info-card" style="text-decoration:none" href="<?= e(SITE['help_url']) ?>"><h3>Plans &amp; subscription</h3><p>Upgrades, renewals, message and OCR credits.</p></a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
