<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Refund policy');
$description = 'Refunds and cancellations for pharmacy software subscriptions, plan changes and message or OCR credits.';
$page_key    = 'refund';
$noindex     = true; // draft until finance/legal sign-off
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Refund policy', 'parent' => 'Legal', 'h1' => 'Refund & cancellation policy',
  'lead' => 'What happens to your payment if you cancel, change your plan, or are charged by mistake.',
  'meta' => 'Effective date: [date to be set on approval] · Last updated: [date]'];

$company = e(SITE['company']);
$address = e(SITE['address']);
$sales   = e(SITE['sales_phone']);
$support = e(SITE['support_phone']);
$u_demo  = url('demo');
$u_terms = url('terms');

$sections = [
 ['summary', 'In short', <<<HTML
  <ul>
   <li>Try the free Starter plan or book a demo before you pay.</li>
   <li>You can switch or cancel a plan at any time.</li>
   <li>Duplicate or failed payments are always refunded [confirm].</li>
   <li>Refunds on paid terms and credits follow the rules below [to be confirmed by finance].</li>
  </ul>
HTML],

 ['free', 'Try before you pay', <<<HTML
  <p>The Starter plan is free, with 10 sales bills and 25 purchase bills a month for one user. We recommend trying billing, purchase entry and reports on Starter, or booking a <a href="{$u_demo}">free demo</a>, before buying a paid plan.</p>
HTML],

 ['cancel', 'Cancelling a paid plan', <<<HTML
  <p>You can cancel at any time from the subscription page in the app, or by calling us. After you cancel, [confirm: access continues until the end of the paid term / ends immediately], and your account then [confirm: moves to Starter limits / becomes read-only for a set period].</p>
  <p>Export your reports and records before your access changes. See the <a href="{$u_terms}">terms of service</a> for how long data stays available after an account closes.</p>
HTML],

 ['refunds', 'Refunds on subscriptions', <<<HTML
  <table><thead><tr><th>Situation</th><th>Refund</th></tr></thead><tbody>
   <tr><td>Cancel within [number] days of first payment</td><td>[Full / partial / none]</td></tr>
   <tr><td>Cancel later in a 1-year term</td><td>[Confirm, for example no refund for the current year]</td></tr>
   <tr><td>Cancel during a 3- or 5-year term</td><td>[Confirm, for example refund of unused full years at the standard 1-year rate]</td></tr>
   <tr><td>Service not provided as described and we cannot fix it</td><td>[Confirm, for example pro-rated refund of the unused term]</td></tr>
  </tbody></table>
  <p>Any one-time onboarding, data migration or training fees are [confirm: refundable / non-refundable once the work has started].</p>
HTML],

 ['changes', 'Upgrades and downgrades', <<<HTML
  <ul>
   <li><b>Upgrade</b>: [confirm, for example you pay the difference for the rest of your term and the new plan starts immediately].</li>
   <li><b>Downgrade</b>: [confirm, for example the lower plan starts at your next renewal, with no refund of the difference]. Check that the lower plan’s user limit and features suit you, because extra users may be deactivated.</li>
  </ul>
HTML],

 ['credits', 'Message and OCR credits', <<<HTML
  <p>Credits for WhatsApp, SMS and email messages and for OCR imports are used as soon as a message is sent or a file is processed, so used credits cannot be refunded. For unused credits: [confirm whether purchased credits are refundable, whether they expire, and whether they carry over at renewal].</p>
HTML],

 ['errors', 'Duplicate or failed payments', <<<HTML
  <p>If you are charged twice for the same invoice, or money is deducted but the payment fails, tell us with the payment reference. We will refund the extra or failed amount to the original payment method once our payment gateway confirms it [confirm timeline, for example within 7 working days].</p>
HTML],

 ['gst', 'GST on refunds', <<<HTML
  <p>Where a refund is approved, the GST charged on the refunded amount is adjusted as GST law requires, usually through a credit note issued to you. [Confirm with the finance team.]</p>
HTML],

 ['how', 'How to request a refund', <<<HTML
  <ol>
   <li>Call {$sales} or email [billing email], from or quoting your registered email.</li>
   <li>Share your store name, invoice number and the reason for the request.</li>
   <li>We confirm the decision within [number] working days.</li>
   <li>Approved refunds are paid to the original payment method within [number] working days. Your bank may take a few more days to show it.</li>
  </ol>
  <p>Please contact us before raising a chargeback with your bank. It is usually faster, and a chargeback may pause your account while it is reviewed [confirm].</p>
HTML],

 ['contact', 'Contact', <<<HTML
  <p>Billing questions: {$sales}. Support: {$support}. Email: [billing email].</p>
  <p>{$company}, {$address}.</p>
HTML],
];
?>
<main id="main">
<?php require __DIR__ . '/partials/legal-layout.php'; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
