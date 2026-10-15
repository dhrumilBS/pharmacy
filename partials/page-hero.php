<?php
/**
 * Inner-page hero with breadcrumb. Set $hero = ['crumb' => 'About', 'h1' => '...', 'lead' => '...', 'eyebrow' => optional, 'meta' => optional].
 * Background pattern (.page-hero.ph2 in layout.css) follows HERO_PATTERN in config.php; a page can override it
 * with 'pattern' => true or false.
 */
$ph_pattern = (bool) ($hero['pattern'] ?? HERO_PATTERN);
?>
<section class="page-hero<?= $ph_pattern ? ' ph2' : '' ?>">
  <div class="container">
    <ol class="crumbs" aria-label="Breadcrumb">
      <li><a href="<?= url('home') ?>">Home</a></li>
      <?php if (!empty($hero['parent'])): ?><li><?= e($hero['parent']) ?></li><?php endif; ?>
      <li aria-current="page"><?= e($hero['crumb']) ?></li>
    </ol>
    <?php if (!empty($hero['eyebrow'])): ?><span class="eyebrow"><?= e($hero['eyebrow']) ?></span><?php endif; ?>
    <h1><?= e($hero['h1']) ?></h1>
    <?php if (!empty($hero['lead'])): ?><p class="lead"><?= e($hero['lead']) ?></p><?php endif; ?>
    <?php if (!empty($hero['meta'])): ?><p class="meta-line"><?= e($hero['meta']) ?></p><?php endif; ?>
  </div>
</section>
