<?php
/**
 * Document head. Set before including:
 *   $title (string), $description (string), optional $page_key (string, for nav highlighting),
 *   optional $noindex (bool, e.g. drafts/legal drafts), optional $extra_css (array of asset paths).
 */
require_once __DIR__ . '/config.php';
$title       = $title ?? SITE['name'];  // pages pass page_title('…')
$description = $description ?? '';
$page_key    = $page_key ?? '';
?><!doctype html>
<html lang="en-IN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <?php if ($description): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>
  <?php if (!empty($noindex)): ?><meta name="robots" content="noindex"><?php endif; ?>
  <meta property="og:title" content="<?= e($title) ?>">
  <?php if (BRAND): ?><meta property="og:site_name" content="<?= e(BRAND) ?>"><?php endif; ?>
  <link rel="icon" href="<?= asset('img/favicon.ico') ?>" sizes="any">
  <link rel="icon" href="<?= asset('img/favicon-32.png') ?>" type="image/png" sizes="32x32">
  <link rel="icon" href="<?= asset('img/favicon-192.png') ?>" type="image/png" sizes="192x192">
  <link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">
  <?php // The only typeface is self-hosted Campton (assets/fonts/campton, @font-face in fonts.css). No external fonts. ?>
  <link rel="preload" href="<?= asset('fonts/campton/campton-400.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= asset('fonts/campton/campton-700.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= asset('css/site.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
  <?php foreach (($extra_css ?? []) as $css): ?><link rel="stylesheet" href="<?= asset($css) ?>"><?php endforeach; ?>
  <!-- Strategy: PHARMACY-ICP-AND-UX-STRATEGY.md · Structure: PHARMACY-SITE-STRUCTURE.md
       Elements with data-confirm need product-owner sign-off. Add ?review to the URL to outline them. -->
</head>
<body class="page-<?= e($page_key ?: 'generic') ?>">
<?php include __DIR__ . '/sprite.php'; ?>
