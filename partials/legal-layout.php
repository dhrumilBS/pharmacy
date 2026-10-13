<?php
/**
 * Legal page body. Set: $hero (see page-hero.php), $sections = [[id, heading, html], ...],
 * $legal_notice (string html, optional).
 * Any [bracketed placeholder] in section html is wrapped in <span data-confirm> automatically,
 * so ?review outlines every item that still needs owner or counsel sign-off.
 */
require __DIR__ . '/page-hero.php';
$legal_mark = static function (string $html): string {
  return preg_replace('/\[([^\]\[<>]{2,})\]/u', '<span class="tbc" data-confirm>[$1]</span>', $html);
};
?>
<section class="section-tight">
  <div class="container legal-wrap">
    <nav class="toc" aria-label="On this page">
      <p>On this page</p>
      <ol><?php foreach ($sections as $s): ?><li><a href="#<?= $s[0] ?>"><?= e($s[1]) ?></a></li><?php endforeach; ?></ol>
    </nav>
    <article class="prose">
      <div class="notice" data-confirm><?= $legal_notice ?? '<b>Draft for legal review.</b> This page has not been approved by the company’s legal team and is not yet in force. Items in [brackets] must be confirmed.' ?></div>
      <?php foreach ($sections as $s): ?>
        <h2 id="<?= $s[0] ?>"><?= e($s[1]) ?></h2>
        <?= $legal_mark($s[2]) ?>
      <?php endforeach; ?>
    </article>
  </div>
</section>
