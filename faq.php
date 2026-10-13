<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('FAQ: pharmacy software questions, answered');
$description = 'Straight answers on the free plan, switching from your current software, FEFO billing, expiry, purchase import, GST and Tally, hospital pharmacy, security and pricing.';
$page_key    = 'faq';
$extra_css   = ['css/pages.css'];

/* [question, answer HTML, confirmed?] — unconfirmed answers are flagged in ?review mode and left out of FAQ schema. */
$faq = [
  'start' => ['Getting started', [
    ['Is there really a free plan?', 'Yes. The Starter plan is free forever for one user, with 10 sales bills and 25 purchase bills a month: enough to set up your items and try billing. Upgrade to Basic or Advance when you need unlimited bills.', true],
    ['Do I need to install anything?', 'No. It runs in your web browser. You sign up with your store name and email, verify with an OTP, and log in from any computer.', true],
    ['How long does setup take?', 'Most of the work is done once: import your items and opening stock from a CSV file, add suppliers and staff, then start billing. Built-in walkthroughs show each step.', true],
  ]],
  'switch' => ['Switching from other software', [
    ['Will my billing stop while we switch?', 'No. Keep your current software running while you set up on the free plan and import your data. Go live on the day you choose.', true],
    ['Can I bring over data from my current software?', 'Yes, through CSV import. Export your items and stock from your current software or Excel, match your columns to ours using the sample sheet, and import. Rows with errors are flagged so you can fix them.', true],
    ['Will you import the data for me?', '[Confirm whether the team offers assisted import and at what cost.]', false],
  ]],
  'billing' => ['Billing & counter', [
    ['Does billing pick the earliest-expiry batch?', 'Yes. When you add an item, the batch that expires first is selected automatically (FEFO). You can still choose another batch.', true],
    ['Can I search by generic name or barcode?', 'Yes. Search by brand or generic name, or use a USB barcode reader. F2 opens search and Ctrl+S saves the bill.', true],
    ['Which payment methods can I record?', 'Cash, card, UPI and credit (udhaar), with the patient’s purchase history and dues on the bill.', true],
    ['Can I send the bill on WhatsApp?', 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits.', true],
    ['Does it work with my printer?', 'Bills print in thermal and A4/A5 formats, and you can print barcode labels for stock. Tell us your printer model during the demo and we’ll check it.', true],
  ]],
  'stock' => ['Expiry & stock', [
    ['How do I see what is expiring?', 'The stock expiry report lists batches expiring in a date range, item-wise or supplier-wise, so you can sell them first or return them in time.', true],
    ['Can I stop expired medicines being billed?', 'Yes. A store setting controls whether expired items can be sold.', true],
    ['How does reordering work?', 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book; on Advance and above you can turn the short book into purchase orders.', true],
  ]],
  'purchase' => ['Purchase & suppliers', [
    ['Do I have to type every distributor bill?', 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). Batch, expiry, MRP, PTR and GST fill in, and margin is worked out on save.', true],
    ['Can I track what I owe each supplier?', 'Yes. Each supplier has a ledger with dues and payment history, plus a supplier due report.', true],
  ]],
  'gst' => ['GST & accounts', [
    ['Does it handle GST correctly?', 'HSN codes and GST rates are applied on every bill from the item master, and the GST report and bill-wise tax report are ready for your accountant. Keep rates in the item master up to date.', true],
    ['Can I export to Tally?', 'Tally XML export is part of the Enterprise plan. On other plans, reports export to Excel for your CA.', true],
    ['Does it handle Schedule H and H1 medicines?', 'Items can be tagged as Schedule H or H1, and bills can carry patient and doctor details. Your licence obligations stay with your pharmacy. Check current rules with your compliance adviser.', true],
  ]],
  'hospital' => ['Hospital pharmacy', [
    ['Is it suitable for a hospital pharmacy?', 'Yes. It supports IPD and discharge billing, payer and TPA companies, patient deposits and dues, departments, patient kits and packages.', true],
    ['Can prescriptions come from the doctor’s EMR?', 'Yes. Prescriptions from the connected hospital EMR arrive at the pharmacy counter as tokens on a token board, ready to bill.', true],
    ['Is it ABDM integrated?', 'Yes. You can create or link a patient’s ABHA with their OTP-based consent.', true],
  ]],
  'security' => ['Security & data', [
    ['Is my login secure?', 'Every user has their own login and role. On Advance and above you can turn on two-step OTP login, allow logins only from your store’s IP addresses, and see an activity report.', true],
    ['What if the internet goes down?', 'It runs in your web browser. [Confirm offline behaviour before publishing.]', false],
    ['Can I take my data with me if I leave?', 'Reports, item lists and ledgers export to Excel or CSV. [Confirm the full-account export process.]', false],
  ]],
  'plans' => ['Plans & payment', [
    ['How much does it cost?', 'Starter is free. Basic is ₹4,999 a year and Advance is ₹9,999 a year, plus 18% GST. Enterprise for chains is quoted. See the <a href="' . url('pricing') . '">pricing page</a>.', true],
    ['Can I cancel anytime?', 'Yes, you can switch or cancel anytime. Refunds are covered by our <a href="' . url('refund') . '">refund policy</a>.', true],
    ['Can I use it for more than one store?', 'Yes, on Enterprise: central administration with linked outlets, stock transfers between stores, and central and multi-store reports.', true],
  ]],
  'support' => ['Support', [
    ['How do I get help?', 'Call support on ' . e(SITE['support_phone']) . ', raise a ticket in the help centre, or follow the step-by-step walkthroughs in the app.', true],
    ['What are your support hours?', '[Confirm support hours and languages before publishing.]', false],
  ]],
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'FAQ', 'parent' => 'Resources', 'eyebrow' => 'FAQ',
  'h1' => 'Questions pharmacy owners ask us',
  'lead' => 'Straight answers on switching, billing, expiry, GST, hospital pharmacy and pricing. Don’t see yours? Call us.'];
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight">
  <div class="container">
    <nav class="faq-cats" aria-label="FAQ topics">
      <?php foreach ($faq as $id => [$name]): ?><a href="#<?= $id ?>"><?= e($name) ?></a><?php endforeach; ?>
    </nav>
    <div style="max-width:860px">
      <?php $n = 0; foreach ($faq as $id => [$name, $items]): ?>
        <section class="faq-block" id="<?= $id ?>" aria-labelledby="<?= $id ?>-h">
          <h2 id="<?= $id ?>-h"><?= e($name) ?></h2>
          <div class="faq-list">
            <?php foreach ($items as [$q, $a, $ok]): $n++; ?>
              <div class="faq-item"<?= $ok ? '' : ' data-confirm' ?>>
                <h3><button class="faq-btn" aria-expanded="false" aria-controls="fq<?= $n ?>" id="fq<?= $n ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
                <div class="faq-ans" id="fq<?= $n ?>" role="region" aria-labelledby="fq<?= $n ?>b" hidden><p><?= $a ?></p></div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php
/* FAQPage structured data — confirmed answers only */
$ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faq as [$name, $items]) foreach ($items as [$q, $a, $ok]) if ($ok)
  $ld['mainEntity'][] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($a)]];
echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
require __DIR__ . '/partials/footer.php';
