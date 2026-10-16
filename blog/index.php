<?php
/* Blog & guides hub (blog/index.php). Posts: partials/data/blog.php; cards and covers: partials/blog.php; styles: blog.css.
   Topic filter is a plain link (?topic=expiry), so it works without JavaScript and each topic has its own URL. */
require_once __DIR__ . '/../partials/blog.php';
$topic = isset($_GET['topic'], BLOG_CATS[$_GET['topic']]) ? $_GET['topic'] : null;

$title       = page_title($topic ? BLOG_CATS[$topic][0] . ' guides for medical stores' : 'Blog & guides: practical help for running a medical store');
$description = 'Plain, practical guides for pharmacy owners: cutting expiry loss, setting stock levels, checking distributor bills, switching software safely and setting up staff logins.';
$page_key    = 'blog';
$extra_css   = ['css/blog.css'];

$all   = blog_posts();
$posts = $topic ? array_values(array_filter($all, fn($p) => $p['cat'] === $topic)) : $all;
$feat  = null;
if (!$topic) { foreach ($all as $p) if (!empty($p['featured'])) { $feat = $p; break; } }
$grid  = array_values(array_filter($posts, fn($p) => !$feat || $p['slug'] !== $feat['slug']));
$count = []; foreach ($all as $p) $count[$p['cat']] = ($count[$p['cat']] ?? 0) + 1;

/* "Start here" reading path for someone thinking of switching */
$path = array_filter(array_map('blog_post', ['desktop-vs-browser', 'switch-pharmacy-software', 'staff-logins-roles']));

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
$hero = ['crumb' => $topic ? BLOG_CATS[$topic][0] : 'Blog & guides', 'parent' => $topic ? 'Blog & guides' : null, 'parent_url' => $topic ? url('blog') : null,
  'eyebrow' => 'Blog & guides',
  'h1' => $topic ? BLOG_CATS[$topic][0] . ' guides' : 'Practical guides for running a medical store',
  'lead' => 'Written for owners and pharmacists at the counter: what to check, what to count and what to change, with worked numbers. No hype, and no tax or legal advice dressed up as a blog post.'];
?>
<main id="main" class="b-hub">
<?php require __DIR__ . '/../partials/page-hero.php'; ?>

<section class="section-tight b-filter-sec">
  <div class="container b-filter">
    <nav aria-label="Topics">
      <ul class="b-topics">
        <li><a class="b-topic<?= $topic ? '' : ' is-on' ?>" href="<?= url('blog') ?>"<?= $topic ? '' : ' aria-current="page"' ?>>All guides <span><?= count($all) ?></span></a></li>
        <?php foreach (BLOG_CATS as $k => [$name]): if (empty($count[$k])) continue; $on = $topic === $k; ?>
          <li><a class="b-topic<?= $on ? ' is-on' : '' ?>" href="<?= url('blog') ?>?topic=<?= e($k) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($name) ?> <span><?= $count[$k] ?></span></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</section>

<?php if ($feat): ?>
<section class="section-tight" aria-label="Featured guide">
  <div class="container b-top">
    <?= blog_card($feat, 'feature') ?>
    <aside class="b-path" aria-labelledby="b-path-h">
      <p class="b-path-k">Start here</p>
      <h2 id="b-path-h">Thinking of switching? Read these in order</h2>
      <ol class="b-path-list">
        <?php foreach ($path as $p): ?>
          <li><a href="<?= e(blog_url($p['slug'])) ?>"><b><?= e($p['title']) ?></b><span><?= (int) $p['mins'] ?> min read</span></a></li>
        <?php endforeach; ?>
      </ol>
      <a class="b-path-more" href="<?= url('switch') ?>">The full switching guide <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </aside>
  </div>
</section>
<?php endif; ?>

<section class="section-tight" aria-labelledby="b-latest">
  <div class="container">
    <div class="b-sec-head"><h2 id="b-latest"><?= $topic ? e(count($posts) . ' ' . (count($posts) === 1 ? 'guide' : 'guides')) : 'Latest guides' ?></h2>
      <?php if ($topic): ?><a href="<?= url('blog') ?>">All topics <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?></div>
    <?php if ($grid): ?>
      <div class="b-grid"><?php foreach ($grid as $p) echo blog_card($p); ?></div>
    <?php else: ?>
      <p class="b-empty">No guides on this topic yet. <a href="<?= url('blog') ?>">See all guides</a>.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section bg-soft" aria-labelledby="b-tools">
  <div class="container">
    <div class="b-sec-head"><h2 id="b-tools">Tools that go with the guides</h2></div>
    <ul class="b-tools">
      <li><a href="<?= url('calculator') ?>"><span class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-chart"/></svg></span><b>Expiry loss calculator</b><span>Put a number on what expiry costs your store in a year.</span></a></li>
      <li><a href="<?= url('checklist') ?>"><span class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-file"/></svg></span><b>Buyer’s checklist</b><span>Eight questions to ask any pharmacy software vendor.</span></a></li>
      <li><a href="<?= url('features') ?>"><span class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-box"/></svg></span><b>All features</b><span>What each part of the software does, with the plan it’s on.</span></a></li>
    </ul>
  </div>
</section>
</main>
<?php
$origin = isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '';
$ld = ['@context' => 'https://schema.org', '@type' => 'Blog', 'name' => 'Blog & guides', 'url' => $origin . url('blog'), 'blogPost' => []];
foreach ($all as $p) $ld['blogPost'][] = ['@type' => 'BlogPosting', 'headline' => $p['title'], 'datePublished' => $p['date'], 'url' => $origin . blog_url($p['slug'])];
echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
require __DIR__ . '/../partials/footer.php';
