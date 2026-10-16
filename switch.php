<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Switching from your current pharmacy software');
$description = 'Change pharmacy software without stopping a single bill: set up free, import items and opening stock from CSV, train staff with in-app walkthroughs, and go live on your day.';
$page_key    = 'switch';
$extra_css   = ['css/pages.css'];
require_once __DIR__ . '/partials/pricing-data.php';   // live plan prices for the comparison table
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Switching', 'eyebrow' => 'Switching from other software',
  'h1' => 'Change your software without stopping a single bill',
  'lead' => 'The biggest worry about switching is the first week. Keep your old system running, set up on the free plan, bring your items and stock in from a CSV file, and go live when you’re ready.'];
/* Columns from the app's "Add Initial Data" import screen */
$cols = ['Item name', 'Generic name', 'Item category', 'Sub-category', 'Item type', 'Company', 'Pack size', 'HSN code', 'Batch number', 'Expiry date', 'MRP', 'Sale rate', 'Min stock', 'Max stock', 'Supplier'];
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight" style="padding-top:8px">
  <div class="container">
    <p class="hero-ctas" style="justify-content:flex-start;margin-top:0">
      <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo">Plan my switch on a call <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-secondary" href="<?= e(SITE['app_signup']) ?>">Start free and try the import</a>
    </p>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container switch-grid">
    <div class="switch-copy">
      <span class="eyebrow">How it works</span>
      <h2>Four steps, at your pace</h2>
      <p>Nothing changes at your counter until you decide. We recommend testing with a few days of real bills before switching fully.</p>
      <figure class="shot" style="margin-top:24px"><?= svg_img('v2-10-initial-import', 1440, 900, 'Sample screen: Add initial data, importing items and opening stock from a CSV with problem rows flagged line by line') ?></figure>
      <span class="sample-tag shot-note">Sample screen · illustrative data</span>
    </div>
    <ol class="steps">
      <li class="step"><div><h3>Create your store account, free</h3><p>Sign up with your store name, address and email, and verify with an OTP. The Starter plan costs nothing.</p><span class="when">A few minutes</span></div></li>
      <li class="step"><div><h3>Import items and opening stock</h3><p>Export a list from your current software or Excel. In <b>Initialization → Add Initial Data</b>, download the sample sheet, match your columns, and import. Errors are flagged line by line.</p><span class="when">Same day</span></div></li>
      <li class="step"><div><h3>Add suppliers and staff</h3><p>Add distributors in Account Master and staff in Users, each with their own login and role. Walkthroughs inside the app show every screen.</p><span class="when">An hour or two</span></div></li>
      <li class="step"><div><h3>Run both, then go live</h3><p>Bill a few customers on the new system while the old one keeps running. When you’re confident, switch fully and move to a paid plan for unlimited bills.</p><span class="when">You choose the day</span></div></li>
    </ol>
  </div>
</section>

<section class="section-tight">
  <div class="container grid-2" style="align-items:start">
    <div>
      <span class="eyebrow">What comes across</span>
      <h2 style="font-size:clamp(26px,3vw,34px)">Columns you can import</h2>
      <p style="margin-top:12px;color:var(--text-2)">These are the fields on the import screen. You don’t need all of them. Item name, batch, expiry, MRP and quantity are enough to start billing.</p>
      <div class="csv-cols"><?php foreach ($cols as $c): ?><span><?= e($c) ?></span><?php endforeach; ?></div>
      <p class="meta-line" style="margin-top:16px">Tip: MRP and PTR should be per strip (or per pack), matching how you sell.</p>
    </div>
    <div class="info-card">
      <h3>Before you import, a quick checklist</h3>
      <ul class="checks">
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span>Export items <b>with batch and expiry</b>, not just item names.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span>Remove items you no longer stock, and zero-quantity batches.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span>Check expiry dates use one format (e.g. DD-MM-YYYY).</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span>Match HSN codes and GST rates to your current invoices.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span>Do a physical count of fast movers on the import day.</span></li>
      </ul>
      <p class="meta-line" data-confirm>Need help preparing the file? [Confirm whether assisted import is offered.]</p>
    </div>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Moving from…</span><h2>Wherever you’re coming from</h2></div>
    <div class="grid-3">
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-screen"/></svg></div><h3>Desktop pharmacy software</h3><p>Most desktop packages can export item and stock lists to Excel or CSV from their reports. Export items with batch, expiry and MRP, then import here. Keep the old PC running until you go live.</p></article>
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-receipt"/></svg></div><h3>A general billing app</h3><p>General apps often don’t track batch or expiry. Export your item list, add batch and expiry from your shelf or latest purchase bills, and import. From then on, every purchase adds batch and expiry for you.</p></article>
      <article class="info-card"><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-file"/></svg></div><h3>Excel or paper registers</h3><p>Use our sample sheet as your template. Start with your top-selling items, import, and add the rest as stock arrives, since each purchase bill can be imported by CSV or OCR.</p></article>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Compare</span><h2>Why stores switch</h2><p>A plain comparison by type of software, not by brand.</p></div>
    <table class="compare-table">
      <thead><tr><th scope="col">What matters</th><th scope="col">Typical desktop licence software</th><th scope="col">Typical general billing app</th><th scope="col">Our software</th></tr></thead>
      <tbody>
        <tr><td>Nearest-expiry batch at billing</td><td>Varies by package</td><td>Usually no batch tracking</td><td>Batch auto-selected by nearest expiry (FEFO)</td></tr>
        <tr><td>Where it runs</td><td>One PC in the store</td><td>Phone or browser</td><td>Any web browser; log in from anywhere</td></tr>
        <tr><td>Distributor bill entry</td><td>Often typed line by line</td><td>Manual entry</td><td>CSV import or OCR scan</td></tr>
        <tr><td>Pricing</td><td>Licence plus yearly AMC, often by quote</td><td>Low monthly fee</td><td><?= e(pricing_compare_text()) ?></td></tr>
        <tr><td>Hospital pharmacy (IPD, TPA, EMR)</td><td>Separate modules, if any</td><td>Not covered</td><td>Built in, plus ABDM</td></tr>
        <tr><td>Staff controls</td><td>Varies</td><td>Basic</td><td>Roles, two-step login, IP whitelisting (Advance)</td></tr>
      </tbody>
    </table>
    <p class="meta-line">Descriptions of other software types are general and vary by product.</p>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow">Switching FAQ</span><h2>Common worries</h2><p>More in the <a href="<?= url('faq') ?>#switch">FAQ</a>.</p></div>
    <div class="faq-list">
      <?php $qs = [
        ['Will billing stop while we switch?', 'No. Your current software keeps running until you choose to go live.', true],
        ['Do I lose my old data?', 'No. Your old system keeps its records. Import what you need to bill from day one (items and opening stock), and keep the old reports for reference.', true],
        ['How long until my staff are comfortable?', 'The counter screen is search, pick, save. Built-in walkthroughs and keyboard shortcuts help new staff, and we recommend testing with real bills for a few days before switching.', true],
        ['Can someone help me with the import?', '[Confirm whether assisted import is offered, and at what cost.]', false],
      ]; foreach ($qs as $i => [$q, $a, $ok]): $n = 'sq' . $i; ?>
        <div class="faq-item"<?= $ok ? '' : ' data-confirm' ?>><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?= $n ?>" id="<?= $n ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
          <div class="faq-ans" id="<?= $n ?>" role="region" aria-labelledby="<?= $n ?>b" hidden><p><?= e($a) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
