<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Pricing: pharmacy software plans from free to Enterprise');
$description = 'Simple yearly plans for medical stores: Starter free forever, Basic ₹4,999/year, Advance ₹9,999/year, Enterprise for chains. Prices exclude 18% GST. Compare every feature.';
$page_key    = 'pricing';
$extra_js    = ['js/pricing.js'];
$extra_css   = ['css/pages.css'];

/* Plan data: snapshot of the app's public pricing API (partials/data/pricing.json, refreshed 13 Oct 2026).
   Refresh with: curl -s <pricing API in CLAUDE.md> -o partials/data/pricing.json */
$data   = json_decode(file_get_contents(__DIR__ . '/partials/data/pricing.json'), true);
$order  = [1, 3, 2, 5];                                    // Starter, Basic, Advance, Enterprise
$plans  = array_column($data['plans'], null, 'id');
$status = [];
foreach ($data['statuses'] as $st) $status[$st['plan_template_id']][$st['pricing_feature_id']] = $st;
$features = $data['features'];
usort($features, fn($a, $b) => [$a['group_id'], $a['sort_order']] <=> [$b['group_id'], $b['sort_order']]);
$groups = array_column($data['groups'], 'name', 'id');
$fit = [1 => 'Try it on one counter', 3 => 'A single store getting started', 2 => 'A busy store with more staff', 5 => 'Chains and hospital pharmacies'];

/* Friendlier labels for a few API feature names */
$label = ['Draft' => 'Save bills as draft', 'Bill Limit' => 'Bills per month', 'MRD' => 'MRD (medical records)', '2FA' => 'Two-step login (OTP)',
          'Non Moving Item' => 'Non-moving items report', 'Top Selling Item' => 'Top-selling items report', 'Lock Invoices' => 'Lock invoices'];

function cell(?array $st): string {
  if (!$st) return '<span class="pc-no" aria-label="Not included">—</span>';
  if ($st['display_value']) return '<span class="pc-val">' . e($st['display_value']) . '</span>';
  if ($st['credit_amount'] !== null) return '<span class="pc-yes" aria-label="Included">✓</span><small>uses credits</small>';
  return '<span class="pc-yes" aria-label="Included">✓</span>';
}

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Pricing', 'eyebrow' => 'Pricing',
  'h1' => 'Simple yearly plans. Prices on the page.',
  'lead' => 'Start free on one counter. Upgrade when you need unlimited bills, more staff or more stores. No setup quotes, no guessing what year two costs.'];
?>
<main id="main">
<?php require __DIR__ . '/partials/page-hero.php'; ?>

<section class="section-tight" style="padding-top:8px">
  <div class="container">
    <div class="term-bar">
      <span>Pay for</span>
      <div class="term-seg" role="radiogroup" aria-label="Billing term">
        <?php foreach ($data['terms'] as $i => $t): ?>
          <label><input type="radio" name="term" value="<?= (int) $t['years'] ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($t['label']) ?></span></label>
        <?php endforeach; ?>
      </div>
      <small>Prices in ₹, exclusive of 18% GST · switch or cancel anytime</small>
    </div>

    <div class="price-grid" style="margin-top:28px">
      <?php foreach ($order as $id): $p = $plans[$id]; $paid = $p['price'] > 0; ?>
        <article class="plan<?= $p['is_popular'] ? ' featured' : '' ?>">
          <?php if ($p['is_popular']): ?><span class="badge">Most popular</span><?php endif; ?>
          <h2 style="font-size:20px"><?= e($p['name']) ?></h2>
          <p class="fit"><?= e($fit[$id]) ?></p>
          <div class="quote">
            <?php if ($p['is_custom']): ?><b>Custom</b>Priced for your stores
            <?php elseif (!$paid): ?><b>Free</b>Forever
            <?php else: ?><b><?= inr($p['price']) ?> / year</b><span class="term-total" data-price="<?= (int) $p['price'] ?>">About <?= inr($p['price'] / 12) ?> a month</span>
            <?php endif; ?>
          </div>
          <ul class="checks">
            <?php foreach ($p['highlights'] as $h): ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?= e($h) ?></li><?php endforeach; ?>
          </ul>
          <?php if ($p['is_custom']): ?>
            <a class="btn btn-secondary" href="<?= url('demo') ?>?want=quote" data-cta="quote">Talk to us</a>
          <?php else: ?>
            <a class="btn <?= $p['is_popular'] ? 'btn-primary' : 'btn-secondary' ?>" href="<?= e(SITE['app_signup']) ?>"><?= $paid ? 'Choose ' . e($p['name']) : 'Start free' ?></a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-tight bg-soft" id="compare">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Compare plans</span><h2>Every feature, plan by plan</h2>
      <p>Taken from the plan settings in the app. Features marked “uses credits” send messages or scan bills using credits.</p></div>
    <div class="pc-wrap" tabindex="0" role="region" aria-label="Plan comparison table, scrolls sideways on small screens">
      <table class="pc">
        <thead><tr><th scope="col">Feature</th><?php foreach ($order as $id): ?><th scope="col"><?= e($plans[$id]['name']) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <tr><th scope="row">Users</th><?php foreach ($order as $id): ?><td><span class="pc-val"><?= $plans[$id]['user_limit'] ?? 'Unlimited' ?></span></td><?php endforeach; ?></tr>
          <?php $cur = null; foreach ($features as $f):
            $inAny = false; foreach ($order as $id) if (!empty($status[$id][$f['id']])) $inAny = true;
            if (!$inAny || $f['unique_name'] === 'feat_user_limit') continue;   // skip rows no plan includes; users shown above
            if ($cur !== $f['group_id']): $cur = $f['group_id']; ?>
              <tr class="pc-group"><th colspan="5" scope="colgroup"><?= e($groups[$cur]) ?></th></tr>
            <?php endif; ?>
            <tr><th scope="row"><?= e($label[$f['name']] ?? $f['name']) ?></th>
              <?php foreach ($order as $id): ?><td><?= cell($status[$id][$f['id']] ?? null) ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container grid-2" style="align-items:start">
    <div>
      <span class="eyebrow">Message &amp; OCR credits</span>
      <h2 style="font-size:clamp(24px,3vw,32px)">What credits are for</h2>
      <p style="margin-top:12px;color:var(--text-2)">Sending a bill or the daily report on WhatsApp, SMS or email, and scanning a distributor bill with OCR, use credits. Each plan includes some, and you can buy more as an add-on.</p>
      <p class="meta-line" data-confirm>[Confirm whether included credits are per month or per term.]</p>
    </div>
    <div class="pc-wrap">
      <table class="pc pc-small">
        <thead><tr><th scope="col">Included</th><?php foreach ($order as $id): ?><th scope="col"><?= e($plans[$id]['name']) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach (['whatsapp' => 'WhatsApp', 'sms' => 'SMS', 'email' => 'Email', 'ocr' => 'OCR scans'] as $k => $name): ?>
            <tr><th scope="row"><?= $name ?></th><?php foreach ($order as $id): ?><td><?= (int) ($plans[$id]['message_service'][$k] ?? 0) ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="section-tight bg-soft">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow">Pricing FAQ</span><h2>Questions about plans</h2>
      <p>More answers on the <a href="<?= url('faq') ?>">FAQ page</a>.</p></div>
    <div class="faq-list">
      <?php $qs = [
        ['Is GST included in the price?', 'No. Prices are in rupees (₹) and exclude GST, currently 18%, which is added at checkout.'],
        ['What happens when I reach the Starter limit?', 'Starter includes 10 sales bills and 25 purchase bills a month for one user. When you need more, upgrade to Basic or Advance for unlimited bills.'],
        ['Can I pay for more than one year?', 'Yes. Paid plans can be bought for 1, 3 or 5 years.'],
        ['Can I change or cancel my plan?', 'Yes, you can switch or cancel anytime. Refunds are covered by our refund policy.'],
        ['How is Enterprise priced?', 'Enterprise is quoted for your stores: multi-store management, unlimited users and bills, Tally XML export and priority support. Book a call and we’ll send a quote.'],
        ['Do you charge for setup or importing data?', 'Importing your items and opening stock from a CSV file is built into the app on every plan. [Confirm whether any assisted setup is charged.]'],
      ]; foreach ($qs as $i => [$q, $a]): $n = 'pq' . $i; ?>
        <div class="faq-item"><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?= $n ?>" id="<?= $n ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
          <div class="faq-ans" id="<?= $n ?>" role="region" aria-labelledby="<?= $n ?>b" hidden><p<?= str_contains($a, '[Confirm') ? ' data-confirm' : '' ?>><?= e($a) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
