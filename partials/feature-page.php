<?php
/**
 * Feature page template ("Run the counter" pages: billing, expiry, inventory, purchase).
 * The page file sets $title, $description, $page_key and a $fp data array, then requires this file.
 * Reader logic (PHARMACY-SITE-STRUCTURE.md §4.2): recognise my problem → see where the money leaks and what plugs it
 * → see it in the product → check it myself in a demo → act.
 *
 * Design intent: read like a well-kept register or a spec sheet, not a slide deck. Ruled lines, real tables, plain
 * numbers and written labels; few cards, no decorative capsules or pills (the eyebrow marker is the one exception).
 *
 * $fp keys (all optional except hero):
 *   hero     page-hero.php array
 *   ctas     ['demo' => label, 'free' => label]
 *   assure   [string, ...]                                     check-marked reassurance lines under the CTAs
 *   screen   [svg name, alt]                                    product screen (vector SVG, sample data)
 *   scenes   [eyebrow, title, intro, items => [[time, title, text, cost], ...]]
 *   leaks    [eyebrow, title, intro, rows => [[leak, how, plug, feat|null, plan|null], ...], example|estimator]
 *            feat = API unique_name (plan comes live from the pricing API); plan = fixed text
 *            example = [title, lines => [[label, value], ...], result => [label, value], note]
 *   steps    [eyebrow, title, intro, items => [[title, text, meta], ...], keys => [[key, what], ...]]
 *   caps     [eyebrow, title, intro, items => [[icon, title, text, feat|null, plan|null], ...]]
 *   guide    [eyebrow, title, intro, items => [[title, text], ...], note]
 *   proof    [eyebrow, title, intro, items => [string, ...]]
 *   related  [page key, ...]                                   sibling feature pages (labels from NAV_FEATURES)
 *   related_title  heading for that row (default "Also at the counter"; "Run the business" pages use their own)
 *   faq      [[question, answer html, confirmed?], ...]          unconfirmed answers get data-confirm and stay out of schema
 * All copy must trace to verified product facts (PHARMACY-ICP-AND-UX-STRATEGY.md §0). Worked examples are illustrative
 * and labelled as such.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/pricing-data.php';

$extra_css = array_merge(['css/feature.css'], !empty($fp['leaks']['estimator']) ? ['css/estimator.css'] : []);
$fp_id = preg_replace('/[^a-z0-9]/', '', $page_key ?? 'fp');

/** Plan for a row: live from the API when 'feat' is given, else the fixed 'plan' text. '' when unknown. */
$fp_plan = function (?string $feat, ?string $plan = null): string {
  return (string) (($feat ? feature_plan_label($feat) : null) ?? $plan ?? '');
};
/** Feature-page links by key, with label / blurb / icon from the header menu data. */
$fp_nav = [];
foreach (NAV_FEATURES as $items) foreach ($items as $it) $fp_nav[$it[0]] = $it;

require __DIR__ . '/head.php';
require __DIR__ . '/header.php';
$hero = $fp['hero'];
?>
<main id="main">
<?php require __DIR__ . '/page-hero.php'; ?>

<?php /* ---------- CTAs + product screen ---------- */ ?>
<section class="section-tight fp-top">
  <div class="container">
    <p class="hero-ctas fp-ctas">
      <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo"><?= e($fp['ctas']['demo'] ?? 'Book a free demo') ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-secondary" href="<?= e(SITE['app_signup']) ?>"><?= e($fp['ctas']['free'] ?? 'Start free') ?></a>
    </p>
    <?php if (!empty($fp['assure'])): ?>
      <ul class="hero-assure fp-assure">
        <?php foreach ($fp['assure'] as $a): ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?= e($a) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if (!empty($fp['screen'])): ?>
      <figure class="shot fp-shot"><?= svg_img($fp['screen'][0], 1440, 900, 'Sample screen: ' . $fp['screen'][1], 'loading="eager" fetchpriority="high"') ?></figure>
      <span class="sample-tag shot-note">Sample screen · illustrative data</span>
    <?php endif; ?>
  </div>
</section>

<?php /* ---------- The problem in their words: a diary of moments, not a card grid ---------- */ if (!empty($fp['scenes'])): $s = $fp['scenes']; ?>
<section class="section" aria-labelledby="<?= $fp_id ?>-scenes">
  <div class="container">
    <div class="section-head"><span class="eyebrow"><?= e($s['eyebrow']) ?></span><h2 id="<?= $fp_id ?>-scenes"><?= e($s['title']) ?></h2><?php if (!empty($s['intro'])): ?><p><?= e($s['intro']) ?></p><?php endif; ?></div>
    <ol class="fp-diary">
      <?php foreach ($s['items'] as [$time, $t, $text, $cost]): ?>
        <li>
          <p class="fp-when"><?= e($time) ?></p>
          <div><h3><?= e($t) ?></h3><p><?= e($text) ?></p></div>
          <p class="fp-cost"><span>Cost</span><?= e(ucfirst($cost)) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Leak register: one card per leak. The leak sits above a dashed tear line, the fix below it,
   and the plan on the card's footer line. The last card is the worked example (or a demo card on pages without one).
   Earlier table version: archive/features/v1. ---------- */
if (!empty($fp['leaks'])): $l = $fp['leaks']; $n = count($l['rows']); $x = $l['example'] ?? null; ?>
<section class="section bg-soft" aria-labelledby="<?= $fp_id ?>-leaks">
  <div class="container">
    <div class="section-head"><span class="eyebrow"><?= e($l['eyebrow']) ?></span><h2 id="<?= $fp_id ?>-leaks"><?= e($l['title']) ?></h2><?php if (!empty($l['intro'])): ?><p><?= e($l['intro']) ?></p><?php endif; ?></div>
    <ol class="lc-grid">
      <?php foreach ($l['rows'] as $i => $r): [$leak, $how, $plug] = $r; $plan = $fp_plan($r[3] ?? null, $r[4] ?? null); ?>
        <li class="lc">
          <p class="lc-k">Leak <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></p>
          <h3><?= e($leak) ?></h3>
          <p class="lc-how"><?= e($how) ?></p>
          <p class="lc-fix-k"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>How it’s plugged</p>
          <p class="lc-fix"><?= $plug ?></p>
          <p class="lc-plan"><?= $plan !== '' ? 'Plan: <b>' . e($plan) . '</b>' : 'Plan: not listed separately' ?></p>
        </li>
      <?php endforeach; ?>
      <?php if ($x): ?>
        <li class="lc lc-math" aria-labelledby="<?= $fp_id ?>-math">
          <p class="lc-k">Worked example · illustrative</p>
          <h3 id="<?= $fp_id ?>-math"><?= e($x['title']) ?></h3>
          <dl>
            <?php foreach ($x['lines'] as $j => $ln): $op = $ln[2] ?? ($j ? '×' : ''); /* optional 3rd item: operator shown before the value */ ?>
              <div><dt><?= e($ln[0]) ?></dt><dd><?= $op !== '' ? '<i aria-hidden="true">' . e($op) . '</i> ' : '' ?><?= e($ln[1]) ?></dd></div>
            <?php endforeach; ?>
            <div class="lc-total"><dt><?= e($x['result'][0]) ?></dt><dd><?= e($x['result'][1]) ?></dd></div>
          </dl>
          <p class="lc-note"><?= e($x['note']) ?></p>
        </li>
      <?php else: ?>
        <li class="lc lc-cta">
          <p class="lc-k">Your store</p>
          <h3>Go through these with your own stock</h3>
          <p>Bring a few items from your shelf to a 30-minute demo and we’ll check each leak with your numbers.</p>
          <a class="btn btn-secondary" href="<?= url('demo') ?>" data-cta="demo">Book a free demo</a>
        </li>
      <?php endif; ?>
    </ol>
    <p class="lc-foot">Plans as listed in the current <a href="<?= url('pricing') ?>#compare">plan comparison</a>. Ask us in the demo about anything marked “not listed separately”.</p>

    <?php if (!empty($l['estimator'])): ?>
      <div class="fp-est"><?php $est_cta = url('demo'); require __DIR__ . '/estimator.php'; ?></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- How it works: a plain process strip ---------- */ if (!empty($fp['steps'])): $st = $fp['steps']; ?>
<section class="section" aria-labelledby="<?= $fp_id ?>-steps">
  <div class="container">
    <div class="section-head"><span class="eyebrow"><?= e($st['eyebrow']) ?></span><h2 id="<?= $fp_id ?>-steps"><?= e($st['title']) ?></h2><?php if (!empty($st['intro'])): ?><p><?= e($st['intro']) ?></p><?php endif; ?></div>
    <ol class="fp-steps">
      <?php foreach ($st['items'] as $i => [$t, $text, $meta]): ?>
        <li>
          <p class="fp-step-k">Step <?= $i + 1 ?></p>
          <h3><?= e($t) ?></h3>
          <p><?= $text ?></p>
          <?php if ($meta): ?><p class="fp-step-meta"><?= e($meta) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php if (!empty($st['keys'])): ?>
      <p class="fp-keys">
        <span>Shortcuts:</span>
        <?php foreach ($st['keys'] as $j => [$k, $what]): ?>
          <?= $j ? '<span aria-hidden="true">·</span>' : '' ?>
          <span><?php foreach (explode('+', $k) as $m => $part): ?><?= $m ? ' + ' : '' ?><kbd><?= e(trim($part)) ?></kbd><?php endforeach; ?> <?= e($what) ?></span>
        <?php endforeach; ?>
      </p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Capabilities: a spec sheet with a ruled grid ---------- */ if (!empty($fp['caps'])): $c = $fp['caps']; ?>
<section class="section fp-caps-sec" aria-labelledby="<?= $fp_id ?>-caps">
  <div class="container">
    <div class="section-head"><span class="eyebrow"><?= e($c['eyebrow']) ?></span><h2 id="<?= $fp_id ?>-caps"><?= e($c['title']) ?></h2>
      <p><?= e($c['intro'] ?? '') ?> Plans come from the current <a href="<?= url('pricing') ?>#compare">plan comparison</a>.</p></div>
    <ul class="fp-spec">
      <?php foreach ($c['items'] as $it): [$icon, $t, $text] = $it; $plan = $fp_plan($it[3] ?? null, $it[4] ?? null); ?>
        <li>
          <h3><svg class="icon" aria-hidden="true"><use href="#<?= e($icon) ?>"/></svg><?= e($t) ?></h3>
          <p><?= $text ?></p>
          <?php if ($plan !== ''): ?><p class="fp-spec-plan">Plan: <b><?= e($plan) ?></b></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Practical guide: useful context for the owner, not product claims ---------- */ if (!empty($fp['guide'])): $g = $fp['guide']; ?>
<section class="section bg-soft" aria-labelledby="<?= $fp_id ?>-guide">
  <div class="container fp-guide">
    <div class="fp-guide-copy">
      <span class="eyebrow"><?= e($g['eyebrow']) ?></span>
      <h2 id="<?= $fp_id ?>-guide"><?= e($g['title']) ?></h2>
      <?php if (!empty($g['intro'])): ?><p><?= e($g['intro']) ?></p><?php endif; ?>
      <?php if (!empty($g['note'])): ?><p class="meta-line"><?= e($g['note']) ?></p><?php endif; ?>
    </div>
    <ol class="fp-guide-list">
      <?php foreach ($g['items'] as [$t, $text]): ?><li><h3><?= e($t) ?></h3><p><?= $text ?></p></li><?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Proof you can check yourself: a printed-style checklist (no testimonials until verified ones exist) ---------- */ if (!empty($fp['proof'])): $p = $fp['proof']; ?>
<section class="section" aria-labelledby="<?= $fp_id ?>-proof">
  <div class="container fp-proof">
    <div class="fp-proof-copy">
      <span class="eyebrow"><?= e($p['eyebrow']) ?></span>
      <h2 id="<?= $fp_id ?>-proof"><?= e($p['title']) ?></h2>
      <p><?= e($p['intro']) ?></p>
      <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo">Book a demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </div>
    <div class="fp-sheet">
      <p class="fp-sheet-h"><b>Demo checklist</b><span><?= count($p['items']) ?> items · about 30 minutes</span></p>
      <ol>
        <?php foreach ($p['items'] as $item): ?><li><span class="fp-box" aria-hidden="true"></span><?= e($item) ?></li><?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Related feature pages: a plain link row ---------- */ if (!empty($fp['related'])): ?>
<section class="section-tight fp-rel-sec" aria-labelledby="<?= $fp_id ?>-rel">
  <div class="container">
    <h2 class="fp-rel-h" id="<?= $fp_id ?>-rel"><?= e($fp['related_title'] ?? 'Also at the counter') ?></h2>
    <ul class="fp-related">
      <?php foreach ($fp['related'] as $k): if (empty($fp_nav[$k])) continue; [, $label, $desc] = $fp_nav[$k]; ?>
        <li><a href="<?= e(url($k)) ?>"><b><?= e($label) ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></b><span><?= e($desc) ?></span></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('switch') ?>"><b>Switching from other software <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></b><span>Bring items and stock across by CSV</span></a></li>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- FAQ ---------- */ if (!empty($fp['faq'])): ?>
<section class="section bg-soft" aria-labelledby="<?= $fp_id ?>-faq">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow">Questions</span><h2 id="<?= $fp_id ?>-faq"><?= e($fp['faq_title'] ?? 'Asked at the counter') ?></h2>
      <p>More answers on the <a href="<?= url('faq') ?>">FAQ page</a>, or call sales on <a href="tel:<?= SITE['sales_tel'] ?>"><?= e(SITE['sales_phone']) ?></a>.</p></div>
    <div class="faq-list">
      <?php foreach ($fp['faq'] as $i => [$q, $a, $ok]): $qid = $fp_id . 'q' . $i; ?>
        <div class="faq-item"<?= $ok ? '' : ' data-confirm' ?>><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?= $qid ?>" id="<?= $qid ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
          <div class="faq-ans" id="<?= $qid ?>" role="region" aria-labelledby="<?= $qid ?>b" hidden><p><?= $a ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<?php
/* Structured data: breadcrumb + FAQPage (confirmed answers only) */
$origin = (isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '');
$ld = [['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
  ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $origin . url('home')],
  ['@type' => 'ListItem', 'position' => 2, 'name' => $fp['hero']['crumb'], 'item' => $origin . url($page_key)],
]]];
if (!empty($fp['faq'])) {
  $faqLd = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
  foreach ($fp['faq'] as [$q, $a, $ok]) if ($ok) $faqLd['mainEntity'][] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($a))]];
  if ($faqLd['mainEntity']) $ld[] = $faqLd;
}
foreach ($ld as $block) echo '<script type="application/ld+json">' . json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
require __DIR__ . '/footer.php';
