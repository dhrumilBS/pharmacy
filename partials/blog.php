<?php
/**
 * Blog kit: cards and covers for the hub, the single-post layout, and article components.
 * Styles: assets/css/blog.css. Behaviour (reading progress, copy link, mobile TOC): assets/js/blog.js.
 *
 * A post file (blog/<slug>.php) looks like:
 *   require __DIR__ . '/../partials/blog.php';
 *   blog_start('slug');            // head, header, hero; starts capturing the article body
 *   ?> <p>…</p> <h2>…</h2> <?= bk_note('tip', 'Title', '<p>…</p>') ?> …
 *   <?php blog_end([[q, a], …]);   // builds the TOC from the h2s, prints the layout, FAQ, related, footer
 *
 * Components (all return HTML; text arguments are escaped, *_html arguments are trusted markup from post files):
 *   bk_takeaways([..])                       "In short" box at the top
 *   bk_note(kind, title, html)               kind: tip | note | caution | example
 *   bk_table(head, rows, caption, opts)      opts: ['hl' => column index to highlight, 'num' => [numeric cols]]
 *   bk_compare(spec)                         two options side by side with a verdict
 *   bk_sum(title, lines, result, note)       a worked sum, written out
 *   bk_rule(text)                            a rule of thumb, set large
 *   bk_checklist(title, items)               printable tick list
 *   bk_steps(items)                          numbered steps with a "when" line
 *   bk_cta(kind, …)                          kind: inline | panel | feature
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/blog.php';

/* ---------- Hub pieces ---------- */

/** Cover: a crop of the product screen the post is about, on a tinted panel. */
function blog_cover(array $p, string $loading = 'lazy'): string {
  [$screen, $zoom, $focus] = $p['cover'];
  /* The screen sits in a "window" that runs off the panel's bottom-right edge, zoomed in on the relevant part */
  return '<span class="bc-cover bc-' . e(BLOG_CATS[$p['cat']][1]) . '" aria-hidden="true"><span class="bc-frame">'
       . '<img src="' . asset('img/screens/v2-' . $screen . '.svg') . '" alt="" width="1440" height="900" loading="' . $loading . '" decoding="async"'
       . ' style="--z:' . (float) $zoom . ';--f:' . e($focus) . '"></span></span>';
}

/** Category pill (pills suit category tags). */
function blog_cat_pill(string $cat, bool $link = false): string {
  [$name, $tone] = BLOG_CATS[$cat];
  $cls = 'b-pill b-pill-' . e($tone);
  return $link ? '<a class="' . $cls . '" href="' . BASE . 'blog/index.php?topic=' . e($cat) . '">' . e($name) . '</a>'
               : '<span class="' . $cls . '">' . e($name) . '</span>';
}

/** Post card. $variant: 'card' (grid) | 'feature' (large, hub top) | 'row' (compact list). */
function blog_card(array $p, string $variant = 'card'): string {
  $u = e(blog_url($p['slug']));
  $meta = '<p class="bc-meta"><time datetime="' . e($p['date']) . '">' . e(blog_date($p['date'])) . '</time><span aria-hidden="true">·</span>' . (int) $p['mins'] . ' min read</p>';
  if ($variant === 'row') {
    return '<li class="bc-row"><a href="' . $u . '"><span class="bc-row-t">' . e($p['title']) . '</span>' . $meta . '</a></li>';
  }
  $h = $variant === 'feature' ? 'h2' : 'h3';
  return '<article class="bc bc-' . $variant . '">'
       . '<a class="bc-link" href="' . $u . '" tabindex="-1" aria-hidden="true">' . blog_cover($p, $variant === 'feature' ? 'eager' : 'lazy') . '</a>'
       . '<div class="bc-body">' . blog_cat_pill($p['cat'])
       . '<' . $h . ' class="bc-title"><a href="' . $u . '">' . e($p['title']) . '</a></' . $h . '>'
       . '<p class="bc-dek">' . e($p['dek']) . '</p>' . $meta
       . ($variant === 'feature' ? '<a class="bc-more" href="' . $u . '">Read the guide <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>' : '')
       . '</div></article>';
}

/* ---------- Article components ---------- */

function bk_takeaways(array $items): string {
  $li = ''; foreach ($items as $t) $li .= '<li>' . $t . '</li>';
  return '<aside class="bk-take" aria-label="In short"><p class="bk-take-h">In short</p><ol>' . $li . '</ol></aside>';
}

function bk_note(string $kind, string $title, string $html): string {
  $map = ['tip' => ['Tip', 'i-check-c'], 'note' => ['Note', 'i-file'], 'caution' => ['Watch out', 'i-alert'], 'example' => ['Example', 'i-rupee']];
  [$label, $icon] = $map[$kind] ?? $map['note'];
  return '<aside class="bk-note bk-note-' . e($kind) . '"><p class="bk-note-h"><svg class="icon" aria-hidden="true"><use href="#' . $icon . '"/></svg>'
       . '<span class="bk-note-k">' . $label . '</span>' . ($title !== '' ? '<b>' . e($title) . '</b>' : '') . '</p><div class="bk-note-b">' . $html . '</div></aside>';
}

function bk_table(array $head, array $rows, string $caption = '', array $opts = []): string {
  $hl = $opts['hl'] ?? null; $num = $opts['num'] ?? [];
  $cls = fn($i) => trim(($i === $hl ? 'is-hl ' : '') . (in_array($i, $num, true) ? 'is-num' : ''));
  $th = ''; foreach ($head as $i => $c) $th .= '<th scope="col"' . (($k = $cls($i)) ? ' class="' . $k . '"' : '') . '>' . e($c) . '</th>';
  $tb = '';
  foreach ($rows as $r) {
    $tb .= '<tr>';
    foreach ($r as $i => $c) $tb .= $i === 0 ? '<th scope="row">' . $c . '</th>' : '<td' . (($k = $cls($i)) ? ' class="' . $k . '"' : '') . '>' . $c . '</td>';
    $tb .= '</tr>';
  }
  return '<figure class="bk-table"><div class="bk-table-scroll" tabindex="0" role="region" aria-label="' . e($caption ?: 'Table') . '"><table>'
       . '<thead><tr>' . $th . '</tr></thead><tbody>' . $tb . '</tbody></table></div>'
       . ($caption ? '<figcaption>' . e($caption) . '</figcaption>' : '') . '</figure>';
}

/**
 * Two options side by side. $s = ['title' => …, 'a' => [name, tag], 'b' => [name, tag], 'win' => 'a'|'b'|null,
 *   'rows' => [[label, a_html, b_html], …], 'verdict' => html]
 */
function bk_compare(array $s): string {
  $col = function (string $k) use ($s) {
    [$name, $tag] = $s[$k];
    $win = ($s['win'] ?? null) === $k;
    $li = '';
    foreach ($s['rows'] as [$label, $a, $b]) $li .= '<li><span class="bk-cmp-l">' . e($label) . '</span><span>' . ($k === 'a' ? $a : $b) . '</span></li>';
    return '<div class="bk-cmp-col' . ($win ? ' is-win' : '') . '"><p class="bk-cmp-h"><b>' . e($name) . '</b>'
         . ($tag ? '<span class="b-pill ' . ($win ? 'b-pill-teal' : 'b-pill-slate') . '">' . e($tag) . '</span>' : '') . '</p><ul>' . $li . '</ul></div>';
  };
  return '<figure class="bk-cmp">' . (!empty($s['title']) ? '<p class="bk-cmp-t">' . e($s['title']) . '</p>' : '')
       . '<div class="bk-cmp-grid">' . $col('a') . '<span class="bk-vs" aria-hidden="true">vs</span>' . $col('b') . '</div>'
       . (!empty($s['verdict']) ? '<figcaption class="bk-cmp-v"><b>Verdict.</b> ' . $s['verdict'] . '</figcaption>' : '') . '</figure>';
}

/** $lines = [[label, value, operator?], …]; operator shown before the value ('×' by default after the first line). */
function bk_sum(string $title, array $lines, array $result, string $note = ''): string {
  $dl = '';
  foreach ($lines as $j => $ln) { $op = $ln[2] ?? ($j ? '×' : ''); $dl .= '<div><dt>' . e($ln[0]) . '</dt><dd>' . ($op !== '' ? '<i aria-hidden="true">' . e($op) . '</i>' : '') . e($ln[1]) . '</dd></div>'; }
  $dl .= '<div class="bk-sum-t"><dt>' . e($result[0]) . '</dt><dd><i aria-hidden="true">=</i>' . e($result[1]) . '</dd></div>';
  return '<figure class="bk-sum"><p class="bk-sum-k">Worked example · illustrative numbers</p><p class="bk-sum-h">' . e($title) . '</p><dl>' . $dl . '</dl>'
       . ($note ? '<figcaption>' . e($note) . '</figcaption>' : '') . '</figure>';
}

function bk_rule(string $text, string $label = 'Rule of thumb'): string {
  return '<aside class="bk-rule"><p class="bk-rule-k">' . e($label) . '</p><p class="bk-rule-t">' . $text . '</p></aside>';
}

function bk_checklist(string $title, array $items): string {
  $li = ''; foreach ($items as $t) $li .= '<li><span class="bk-box" aria-hidden="true"></span><span>' . $t . '</span></li>';
  return '<figure class="bk-check"><p class="bk-check-h"><b>' . e($title) . '</b><button type="button" class="bk-print" data-print>Print this list</button></p><ul>' . $li . '</ul></figure>';
}

/** $items = [[title, html, when?], …] */
function bk_steps(array $items): string {
  $li = '';
  foreach ($items as $i => $s) $li .= '<li><span class="bk-step-n" aria-hidden="true">' . ($i + 1) . '</span><div><p class="bk-step-t">' . e($s[0]) . '</p>' . $s[1]
                                     . (!empty($s[2]) ? '<p class="bk-step-w">' . e($s[2]) . '</p>' : '') . '</div></li>';
  return '<ol class="bk-steps">' . $li . '</ol>';
}

/**
 * CTAs, three looks:
 *   bk_cta('inline', text, link_label, href)            slim line inside the text
 *   bk_cta('panel', title, text)                        navy panel: demo + start free
 *   bk_cta('feature', page_key, text)                   points at the feature page this guide relates to
 */
function bk_cta(string $kind, string $a, string $b = '', string $c = ''): string {
  if ($kind === 'inline') {
    return '<p class="bk-cta-inline"><span>' . e($a) . '</span><a href="' . e($c) . '">' . e($b) . ' <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>';
  }
  if ($kind === 'feature') {
    $nav = [];
    foreach (NAV_FEATURES as $items) foreach ($items as $it) $nav[$it[0]] = $it;
    $label = $nav[$a][1] ?? ($a === 'switch' ? 'Switching from other software' : 'All features');
    $icon  = $nav[$a][3] ?? ($a === 'switch' ? 'i-upload' : 'i-box');
    return '<aside class="bk-cta-feat"><span class="bk-cta-ic"><svg class="icon" aria-hidden="true"><use href="#' . e($icon) . '"/></svg></span>'
         . '<div><p class="bk-cta-k">How the software handles this</p><p class="bk-cta-t">' . e($b) . '</p></div>'
         . '<a class="btn btn-secondary btn-sm" href="' . e(url($a)) . '">' . e($label) . '</a></aside>';
  }
  return '<aside class="bk-cta-panel"><div><p class="bk-cta-t">' . e($a) . '</p><p>' . e($b) . '</p></div><p class="bk-cta-btns">'
       . '<a class="btn btn-light" href="' . url('demo') . '" data-cta="demo">Book a free demo</a>'
       . '<a class="btn btn-ghost-light" href="' . e(SITE['app_signup']) . '">Start free</a></p></aside>';
}

/* ---------- Single-post layout ---------- */

function blog_start(string $slug): void {
  global $title, $description, $page_key, $extra_css, $extra_js, $blog_cur;
  $p = blog_post($slug);
  if (!$p) { http_response_code(404); exit('Post not found'); }
  $blog_cur = $p;
  $title = page_title($p['title']); $description = $p['dek']; $page_key = 'blog';
  $extra_css = ['css/blog.css']; $extra_js = ['js/blog.js'];
  require __DIR__ . '/head.php';
  require __DIR__ . '/header.php';
  $hero = ['crumb' => BLOG_CATS[$p['cat']][0], 'parent' => 'Blog & guides', 'parent_url' => url('blog'), 'h1' => $p['title'], 'lead' => $p['dek']];
  echo '<div class="b-progress" aria-hidden="true"><i></i></div><main id="main" class="b-post">';
  require __DIR__ . '/page-hero.php';
  ob_start();
}

/** $faq = [[question, answer html], …] (answers must be confirmed facts; they go into FAQPage schema). */
function blog_end(array $faq = []): void {
  global $blog_cur, $extra_js, $page_key;   // footer.php reads $extra_js (blog.js)
  $p = $blog_cur;
  $body = ob_get_clean();

  /* Give every h2 an id and collect them for the table of contents */
  $toc = []; $used = [];
  $body = preg_replace_callback('/<h2(?![^>]*\bid=)([^>]*)>(.*?)<\/h2>/s', function ($m) use (&$toc, &$used) {
    $id = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strip_tags($m[2]))), '-') ?: 'section';
    $base = $id; $n = 2; while (isset($used[$id])) $id = $base . '-' . $n++;
    $used[$id] = true; $toc[] = [$id, strip_tags($m[2])];
    return '<h2 id="' . $id . '"' . $m[1] . '>' . $m[2] . '</h2>';
  }, $body);

  $tocHtml = '';
  foreach ($toc as [$id, $t]) $tocHtml .= '<li><a href="#' . e($id) . '">' . e($t) . '</a></li>';
  $u = blog_url($p['slug']);
  ?>
  <div class="container b-meta-bar">
    <p class="b-meta"><?= blog_cat_pill($p['cat'], true) ?><span><?= e(blog_author()) ?></span><span aria-hidden="true">·</span>
      <time datetime="<?= e($p['date']) ?>"><?= e(blog_date($p['date'])) ?></time><span aria-hidden="true">·</span><span><?= (int) $p['mins'] ?> min read</span>
      <?php if (!empty($p['updated'])): ?><span aria-hidden="true">·</span><span>Updated <?= e(blog_date($p['updated'])) ?></span><?php endif; ?></p>
    <button type="button" class="b-copy" data-copy="<?= e($u) ?>"><svg class="icon" aria-hidden="true"><use href="#i-file"/></svg><span>Copy link</span></button>
  </div>
  <div class="container"><figure class="b-hero-cover"><?= blog_cover($p, 'eager') ?></figure></div>

  <div class="container b-layout">
    <aside class="b-aside">
      <details class="b-toc" open>
        <summary>On this page</summary>
        <ol><?= $tocHtml ?></ol>
      </details>
      <div class="b-aside-cta">
        <p><b>See it with your own stock</b>A 30-minute demo using items from your shelf.</p>
        <a class="btn btn-primary btn-sm" href="<?= url('demo') ?>" data-cta="demo">Book a free demo</a>
      </div>
    </aside>
    <article class="b-article">
      <?= $body ?>
      <footer class="b-byline">
        <span class="b-byline-mark" aria-hidden="true"><svg class="brand-logo"><use href="#logo-mark"/></svg></span>
        <div><p><b>Written by <?= e(blog_author()) ?></b></p>
          <p>We build pharmacy software for medical stores, chains and hospital pharmacies. Where this guide describes the software, it matches the app as of <?= e(blog_date($p['updated'] ?? $p['date'])) ?>. Practical advice here is general; check rules that apply to your store with your adviser.</p></div>
      </footer>
    </article>
  </div>

  <?php if ($faq): ?>
  <section class="section bg-soft" aria-labelledby="b-faq">
    <div class="container faq-grid">
      <div class="faq-intro"><span class="eyebrow">Questions</span><h2 id="b-faq">Asked about this</h2>
        <p>More on the <a href="<?= url('faq') ?>">FAQ page</a>, or call sales on <a href="tel:<?= SITE['sales_tel'] ?>"><?= e(SITE['sales_phone']) ?></a>.</p></div>
      <div class="faq-list">
        <?php foreach ($faq as $i => [$q, $a]): $qid = 'bq' . $i; ?>
          <div class="faq-item"><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?= $qid ?>" id="<?= $qid ?>b"><?= e($q) ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
            <div class="faq-ans" id="<?= $qid ?>" role="region" aria-labelledby="<?= $qid ?>b" hidden><p><?= $a ?></p></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php /* Related: same topic first, then newest */
  $rel = array_values(array_filter(blog_posts(), fn($x) => $x['slug'] !== $p['slug']));
  usort($rel, fn($a, $b) => [($b['cat'] === $p['cat']), $b['date']] <=> [($a['cat'] === $p['cat']), $a['date']]);
  ?>
  <section class="section" aria-labelledby="b-rel">
    <div class="container">
      <div class="b-sec-head"><h2 id="b-rel">Keep reading</h2><a href="<?= url('blog') ?>">All guides <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></div>
      <div class="b-grid"><?php foreach (array_slice($rel, 0, 3) as $r) echo blog_card($r); ?></div>
    </div>
  </section>
  </main>
  <?php
  /* Structured data: Article + breadcrumb + FAQ */
  $origin = isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '';
  $ld = [
    ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $p['title'], 'description' => $p['dek'], 'datePublished' => $p['date'],
     'dateModified' => $p['updated'] ?? $p['date'], 'author' => ['@type' => 'Organization', 'name' => BRAND ?: SITE['name']],
     'image' => $origin . asset('img/screens/v2-' . $p['cover'][0] . '.svg'), 'mainEntityOfPage' => $origin . $u],
    ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $origin . url('home')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog & guides', 'item' => $origin . url('blog')],
      ['@type' => 'ListItem', 'position' => 3, 'name' => $p['title'], 'item' => $origin . $u]]],
  ];
  if ($faq) {
    $f = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
    foreach ($faq as [$q, $a]) $f['mainEntity'][] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($a))]];
    $ld[] = $f;
  }
  foreach ($ld as $block) echo '<script type="application/ld+json">' . json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
  require __DIR__ . '/footer.php';
}
