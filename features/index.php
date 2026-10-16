<?php
/* Features overview (features/index.php): the hub for the eight feature pages.
   Reader logic: find my problem → see what each part of the store covers → check plans → open the right page.
   Same visual language as the feature pages (assets/css/feature.css, "fi-" rules). Plans come live from the pricing API.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy software features: billing, expiry, stock, purchase, GST and reports');
$description = 'Every part of a medical store in one browser-based system: counter billing with FEFO batches, expiry and stock control, distributor bill import, GST and accounts, reports, customer retention and staff security.';
$page_key    = 'features';
$extra_css   = ['css/feature.css'];

/** Plan text for a point: live from the API when 'feat' is given, else the fixed text, else ''. */
$plan_of = fn(?string $feat, ?string $fixed = null): string => (string) (($feat ? feature_plan_label($feat) : null) ?? $fixed ?? '');

/* What each page covers. Labels, blurbs and icons come from NAV_FEATURES so the menu and this page never drift. */
$points = [
  'billing'   => [['Brand and generic name search', null, 'Every plan'], ['Nearest-expiry batch picked first (FEFO)', null, 'Every plan'], ['Cash, card, UPI or credit', null, 'Every plan'], ['Bills on WhatsApp, SMS or email', 'feat_bill_sharing']],
  'expiry'    => [['Stock expiry report, item-wise or supplier-wise', null], ['Setting to stop expired sales', null], ['Purchase returns and credit notes', 'feat_purchase_credit_note'], ['Non-moving items report', 'feat_non_moving_item']],
  'inventory' => [['Min and max stock with a stock alert', null], ['Short book of what to order', null], ['Reorder processing and purchase orders', 'feat_reorder_processing'], ['Recorded stock adjustments', 'feat_stock_adjustment']],
  'purchase'  => [['CSV import with column mapping', 'feat_csv_import'], ['OCR import of printed bills', null, 'Uses OCR credits'], ['Scheme, free quantity, PTR and margin', null, 'Every plan'], ['Supplier ledger and dues', 'feat_supplier_due_report']],
  'reports'   => [['Executive dashboard', 'feat_exec_dashboard'], ['Margin, top-selling and non-moving reports', 'feat_margin_report'], ['The day’s report on WhatsApp', 'feat_daily_alerts'], ['Collections by staff member', null]],
  'gst'       => [['HSN and GST rate on every line', null], ['GST report and bill-wise tax report', 'feat_gst_report'], ['Tally XML export', 'feat_tally_export'], ['Expense management', 'feat_expense_mgmt']],
  'retention' => [['Medicine reminders', 'feat_medicine_reminder'], ['Loyalty cards', null], ['Online orders for pick-up or delivery', null], ['Patient history and dues on the bill', null, 'Every plan']],
  'security'  => [['Own login for every user', 'feat_user_limit'], ['Role management', 'feat_role_management'], ['Two-step OTP login and IP whitelisting', 'feat_2fa'], ['Activity report', 'feat_activity_report']],
];

/* "Start with your problem": the moment an owner recognises → the page that deals with it */
$index = [
  ['Customers walk out while staff search for a medicine', 'billing'],
  ['Strips expire behind newer stock', 'expiry'],
  ['You run out of fast movers, and overstock slow ones', 'inventory'],
  ['Distributor bills get typed in after closing', 'purchase'],
  ['You don’t know how the day went unless you’re there', 'reports'],
  ['Month-end GST takes a whole weekend', 'gst'],
  ['Regulars buy their refills somewhere else', 'retention'],
  ['The whole counter shares one login', 'security'],
];

$nav = [];
foreach (NAV_FEATURES as $items) foreach ($items as $it) $nav[$it[0]] = $it;
$plans = pricing_plans();
$free  = pricing_split()['free'][0] ?? null;

$faq = [
  ['Do I need all of this to start?', 'No. You can start with billing, stock and purchase import, and use reports, GST and reminders as you settle in. Everything is in the same software, so there is nothing extra to install later.', true],
  ['Which plan includes what?', 'Each page names the plan a feature starts on, and the <a href="' . url('pricing') . '#compare">plan comparison</a> lists every feature by plan.', true],
  ['Is it the same software for a single store and a chain?', 'Yes. Chains and hospital pharmacies use the same software, with multi-store management on Enterprise.', true],
  ['Can I try it before paying?', $free ? 'Yes. The ' . e($free['name']) . ' plan is free forever, so you can set up your items and try billing before choosing a paid plan.' : 'Yes. Book a demo and we’ll show it with your own items.', true],
  ['Does it work on my computer?', 'It runs in a web browser on the computer you already use at the counter, with USB barcode readers and thermal or A4/A5 printing.', true],
];

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
$hero = ['crumb' => 'Features', 'eyebrow' => 'Features',
  'h1' => 'Every part of the store, from the counter to the books',
  'lead' => 'Billing, expiry, stock and distributor bills for the counter. Reports, GST, regular patients and staff access for the back office. One browser-based system, built for how a medical store actually runs.'];
?>
<main id="main">
<?php require __DIR__ . '/../partials/page-hero.php'; ?>

<section class="section-tight fp-top">
  <div class="container">
    <p class="hero-ctas fp-ctas">
      <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo">Book a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-secondary" href="<?= e(SITE['app_signup']) ?>">Start free</a>
    </p>
    <ul class="hero-assure fp-assure">
      <?php if ($free): ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?= e($free['name']) ?> plan free forever</li><?php endif; ?>
      <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Runs in your web browser</li>
      <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Plans named on every feature</li>
    </ul>
  </div>
</section>

<?php /* ---------- Start with your problem ---------- */ ?>
<section class="section" aria-labelledby="fi-idx">
  <div class="container fi-index-wrap">
    <div class="section-head"><span class="eyebrow">Start with your problem</span><h2 id="fi-idx">What’s costing you most right now?</h2>
      <p>Pick the line that sounds like your store. Each one opens the page that deals with it.</p></div>
    <ol class="fi-index">
      <?php foreach ($index as [$pain, $k]): if (empty($nav[$k])) continue; ?>
        <li><a href="<?= e(url($k)) ?>"><span class="fi-pain"><?= e($pain) ?></span><span class="fi-go"><?= e($nav[$k][1]) ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span></a></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php /* ---------- The two groups, as in the header menu ---------- */
$g = 0; foreach (NAV_FEATURES as $group => $items): $g++; $gid = 'fi-g' . $g; ?>
<section class="section<?= $g === 1 ? ' bg-soft' : '' ?>" aria-labelledby="<?= $gid ?>">
  <div class="container">
    <div class="section-head"><span class="eyebrow"><?= $g === 1 ? 'At the counter' : 'In the back office' ?></span><h2 id="<?= $gid ?>"><?= e($group) ?></h2>
      <p><?= $g === 1 ? 'What happens between the shutter going up and the last bill of the night.' : 'What the owner and the accountant look at once the counter is quiet.' ?></p></div>
    <ul class="fi-mods">
      <?php foreach ($items as [$k, $label, $desc, $icon]): ?>
        <li class="fi-mod">
          <h3><svg class="icon" aria-hidden="true"><use href="#<?= e($icon) ?>"/></svg><?= e($label) ?></h3>
          <p class="fi-desc"><?= e($desc) ?></p>
          <ul class="fi-pts">
            <?php foreach ($points[$k] ?? [] as $pt): $pl = $plan_of($pt[1] ?? null, $pt[2] ?? null); ?>
              <li><span><?= e($pt[0]) ?></span><?php if ($pl !== ''): ?><small><?= e($pl) ?></small><?php endif; ?></li>
            <?php endforeach; ?>
          </ul>
          <a class="fi-open" href="<?= e(url($k)) ?>">Open <?= e($label) ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endforeach; ?>

<?php /* ---------- Also built in: hospital pharmacy and chains ---------- */ ?>
<section class="section bg-soft" aria-labelledby="fi-also">
  <div class="container fi-also">
    <div>
      <span class="eyebrow">Also built in</span>
      <h2 id="fi-also">For hospital pharmacies and chains</h2>
      <p class="fi-also-lead">Built by a team that also makes hospital software, so it handles patients, not just customers.</p>
      <ul class="checks">
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><b>IPD and discharge billing</b>, with patient deposits, dues and ledgers.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><b>Payer and TPA companies</b> on the bill.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><b>Prescriptions from the hospital EMR</b> arrive at the counter as tokens on a token board.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><b>ABDM integrated:</b> create or link a patient’s ABHA with their consent.</span></li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><b>Several stores?</b> Central administration with linked outlets, stock transfers and central reports<?= ($ca = feature_plan_label('feat_central_admin')) ? ' (' . e($ca) . ')' : '' ?>.</span></li>
      </ul>
      <p class="fi-also-links"><a href="<?= url('hospital') ?>">Hospital pharmacy</a><a href="<?= url('chain') ?>">Pharmacy chains</a></p>
    </div>
    <div>
      <figure class="shot"><?= svg_img('v2-07-hospital', 1440, 900, 'Sample screen: hospital pharmacy token board with an EMR prescription') ?></figure>
      <span class="sample-tag shot-note">Sample screen · illustrative data</span>
    </div>
  </div>
</section>

<?php /* ---------- Plans at a glance (live) ---------- */ ?>
<section class="section" aria-labelledby="fi-plans">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Plans</span><h2 id="fi-plans">The same software on every plan, with more as you grow</h2>
      <p>Paid plans add unlimited bills, more users and the features named on each page. Prices exclude 18% GST.</p></div>
    <div class="fi-plans-wrap">
      <table class="fi-plans">
        <thead><tr><th scope="col">Plan</th><th scope="col">Price</th><th scope="col">Users</th><th scope="col">Sales bills a month</th></tr></thead>
        <tbody>
          <?php foreach ($plans as $id => $p): $lim = plan_bill_limits((int) $id); ?>
            <tr>
              <th scope="row"><?= e($p['name']) ?><?php if (!empty($p['is_popular'])): ?> <span class="fi-pop">Most popular</span><?php endif; ?></th>
              <td><?= $p['is_custom'] ? 'Quoted for your stores' : ($p['price'] > 0 ? e(inr($p['price'])) . ' a year' : 'Free forever') ?></td>
              <td><?= e((string) ($p['user_limit'] ?? 'Unlimited')) ?></td>
              <td><?= $lim['sales'] !== null ? (int) $lim['sales'] : 'Unlimited' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="lc-foot">Full feature list by plan on the <a href="<?= url('pricing') ?>#compare">pricing page</a>.</p>
  </div>
</section>

<?php /* ---------- FAQ ---------- */ ?>
<section class="section bg-soft" aria-labelledby="fi-faq">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow">Questions</span><h2 id="fi-faq">Before you look closer</h2>
      <p>More answers on the <a href="<?= url('faq') ?>">FAQ page</a>, or call sales on <a href="tel:<?= SITE['sales_tel'] ?>"><?= e(SITE['sales_phone']) ?></a>.</p></div>
    <div class="faq-list">
      <?php foreach ($faq as $i => [$q, $a, $ok]): $qid = 'fiq' . $i; ?>
        <div class="faq-item"<?= $ok ? '' : ' data-confirm' ?>><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?= $qid ?>" id="<?= $qid ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
          <div class="faq-ans" id="<?= $qid ?>" role="region" aria-labelledby="<?= $qid ?>b" hidden><p><?= $a ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php
$ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faq as [$q, $a, $ok]) if ($ok) $ld['mainEntity'][] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($a))]];
echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
require __DIR__ . '/../partials/footer.php';
