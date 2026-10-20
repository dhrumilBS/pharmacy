<?php
/*
 * Site-wide settings for the pharmacy software marketing site.
 * The brand is "Healthray" (final). It lives only in BRAND below and appears in page titles, the footer and home
 * copy via BRAND; the logo is the image assets/img/healthray-logo.svg. Never hard-code the name in pages.
 * Facts verified 13 Oct 2026 (see PHARMACY-ICP-AND-UX-STRATEGY.md §0). Anything marked [confirm] needs sign-off.
 */

// Base URL path of this site (XAMPP: http://localhost/pharmacy/). Change when deploying.
const BASE = '/pharmacy/';

// Brand name (final). Change it here only: titles, footer, alt text and home copy all read BRAND.
const BRAND = 'Healthray';

const SITE = [
  'name'          => 'Pharmacy software',           // generic descriptor used until BRAND is set
  'company'       => '[Company legal name]',         // [confirm] shown only on legal pages
  'address'       => '1st Floor, A - Millenium Point, opp. Gabani Kidney Hospital, Lal Darwaja, Surat, Gujarat 395003', // [confirm]
  'sales_phone'   => '+91 97148 74435',
  'sales_tel'     => '+919714874435',
  'support_phone' => '+91 89808 80819',
  'support_tel'   => '+918980880819',
  'email'         => '',                             // [confirm] set once the brand domain exists; hidden while empty
  'help_url'      => 'https://support.healthray.com', // current help centre (link only; never shown as text)
  'app_login'     => 'https://pharmacy.healthray.com/login',  // current app URLs (link only)
  'app_signup'    => 'https://pharmacy.healthray.com/signup',
  'calendly'      => 'https://calendly.com/healthray/healthray-technologies-lead-meeting', // link only
  'social'        => [],                             // add brand social profiles when they exist
];

// Live pricing (partials/pricing-data.php). The pricing page, FAQ and switch page read plans from this API,
// so price or plan changes in the backend appear on the site automatically. Link/data source only; never print it.
const PRICING_API   = 'https://pharmacyapi.healthray.com/api/subscription/pricing/public';
const PRICING_TTL   = 300;   // seconds a fetched copy is reused before asking the API again
const PRICING_RETRY = 60;    // if the API is down, seconds before trying it again (last good copy is shown meanwhile)

// Inner-page hero background pattern (faint blister-pack dots, .page-hero.ph2 in layout.css).
// true = on for every inner page, false = off everywhere. A page can still override with $hero['pattern'] = true/false.
const HERO_PATTERN = true;

/** Brand-aware title: "Page | Brand" once BRAND is set, otherwise just "Page". */
function page_title(string $t): string { return BRAND ? $t . ' | ' . BRAND : $t; }

/** Logo markup: the Healthray logo image (heart, wordmark and tagline), 150x83 artwork shown 52px tall.
 *  $dark = the white-text version for dark backgrounds (the footer). */
function logo(bool $dark = false): string {
  return '<img class="brand-logo-img" src="' . asset('img/healthray-logo' . ($dark ? '-dark' : '') . '.svg') . '" width="94" height="52" alt="' . e(BRAND) . '">';
}

/*
 * Page registry. 'file' = built page; 'fallback' = where links go until the page exists.
 * When a page is built, set 'file' and links across the header/footer update automatically.
 */
const PAGES = [
  'home'          => ['file' => 'index.php'],
  // Features
  'billing'       => ['file' => 'features/billing.php'],     // "Run the counter" feature pages (partials/feature-page.php)
  'expiry'        => ['file' => 'features/expiry.php'],
  'inventory'     => ['file' => 'features/inventory.php'],
  'purchase'      => ['file' => 'features/purchase.php'],
  'gst'           => ['file' => 'features/gst.php'],         // "Run the business" feature pages (same template)
  'reports'       => ['file' => 'features/reports.php'],
  'retention'     => ['file' => 'features/retention.php'],
  'security'      => ['file' => 'features/security.php'],
  'features'      => ['file' => 'features/index.php'],
  // Solutions
  'retail'        => ['fallback' => 'index.php#solutions'],
  'chain'         => ['fallback' => 'index.php#solutions'],
  'hospital'      => ['fallback' => 'index.php#solutions'],
  'new-pharmacy'  => ['fallback' => 'index.php#switching'],
  // Core
  'pricing'       => ['file' => 'pricing.php'],
  'switch'        => ['file' => 'switch.php'],
  // Resources
  'calculator'    => ['fallback' => 'index.php#estimator'],
  'checklist'     => ['fallback' => 'index.php#checklist'],
  'guides'        => ['file' => 'blog/index.php'],   // the blog is the guides hub (posts in blog/, data in partials/data/blog.php)
  'blog'          => ['file' => 'blog/index.php'],
  'faq'           => ['file' => 'faq.php'],
  // Company
  'about'         => ['file' => 'about.php'],
  'contact'       => ['file' => 'contact.php'],
  'support'       => ['file' => 'support.php'],
  'demo'          => ['file' => 'demo.php'],
  // Legal
  'privacy'       => ['file' => 'privacy.php'],
  'terms'         => ['file' => 'terms.php'],
  'refund'        => ['file' => 'refund-policy.php'],
  'data-security' => ['file' => 'data-security.php'],
  'cookies'       => ['file' => 'cookie-policy.php'],
];

/** URL for a registered page (built file, else its fallback). */
function url(string $key): string {
  $p = PAGES[$key] ?? ['fallback' => 'index.php'];
  return BASE . ($p['file'] ?? $p['fallback']);
}

/** Indian rupee format: inr(1092480) → ₹10,92,480 */
function inr(int|float $n): string {
  $s = (string) (int) round($n);
  if (strlen($s) <= 3) return '₹' . $s;
  $rest = preg_replace('/\B(?=(\d{2})+$)/', ',', substr($s, 0, -3));
  return '₹' . $rest . ',' . substr($s, -3);
}

function asset(string $path): string { return BASE . 'assets/' . ltrim($path, '/'); }

/**
 * Sharp product image: 1x + 2x JPGs from scripts/export-images.ps1 (img/<name>.jpg and img/<name>@2x.jpg).
 * $w/$h = 1x pixel size; $sizes = rendered CSS width hint so the browser picks one file and resamples once.
 */
function img_tag(string $name, int $w, int $h, string $alt, string $sizes, string $extra = 'loading="lazy"'): string {
  $one = asset("img/$name.jpg"); $two = asset("img/$name@2x.jpg");
  return '<img src="' . $one . '" srcset="' . $one . ' ' . $w . 'w, ' . $two . ' ' . ($w * 2) . 'w" sizes="' . e($sizes) . '"'
       . ' width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '" decoding="async" ' . $extra . '>';
}

/**
 * Product screen as a vector SVG (assets/img/screens/<name>.svg, built by scripts/make-svg-screens.*).
 * Stays sharp at any size or pixel density, so no srcset. Same width/height as the JPG it replaces.
 */
function svg_img(string $name, int $w, int $h, string $alt, string $extra = 'loading="lazy"'): string {
  return '<img src="' . asset("img/screens/$name.svg") . '" width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '" decoding="async" ' . $extra . '>';
}

/**
 * Decorative barcode as crisp inline SVG. Bars are derived from $text, so the same text always draws the
 * same barcode. Visual only (not a scannable symbology); always aria-hidden.
 */
function barcode_svg(string $text, int $height = 40, string $class = 'barcode'): string {
  $bars = ''; $x = 0; $h = crc32($text);
  $pattern = [1, 1, 2, 1, 3, 1, 1, 2, 1, 4, 2, 1];             // quiet start
  for ($i = 0; $i < strlen($text) * 4 + 18; $i++) { $h = ($h * 1103515245 + 12345) & 0x7fffffff; $pattern[] = 1 + ($h >> 16) % 4; }
  $pattern = array_merge($pattern, [1, 2, 1, 1, 3, 1]);         // stop
  foreach ($pattern as $i => $w) { if ($i % 2 === 0) $bars .= '<rect x="' . $x . '" width="' . $w . '" height="' . $height . '"/>'; $x += $w; }
  return '<svg class="' . e($class) . '" viewBox="0 0 ' . $x . ' ' . $height . '" preserveAspectRatio="none" aria-hidden="true" focusable="false">' . $bars . '</svg>';
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* Navigation (header mega-menu + footer share this data). */
const NAV_FEATURES = [
  'Run the counter' => [
    ['billing',   'Billing & counter',     'Bill in seconds, nearest expiry first', 'i-receipt'],
    ['expiry',    'Expiry & batch',        'Catch expiry before it’s a loss',       'i-alert'],
    ['inventory', 'Stock & reorder',       'Short book to purchase order',          'i-box'],
    ['purchase',  'Purchase & suppliers',  'Import distributor bills by CSV or OCR','i-truck'],
  ],
  'Run the business' => [
    ['reports',   'Reports & dashboard',   'See the day on WhatsApp',               'i-chart'],
    ['gst',       'GST & accounts',        'GST report, Tally export',              'i-rupee'],
    ['retention', 'Customer retention',    'Reminders, loyalty, online orders',     'i-users'],
    ['security',  'Security & roles',      '2-step login, IP whitelisting',         'i-shield'],
  ],
];
const NAV_SOLUTIONS = [
  ['retail',       'Retail medical store',   'One store, 1–3 counters',               'i-store'],
  ['chain',        'Pharmacy chain',         'Many stores, one set of rules',         'i-stores'],
  ['hospital',     'Hospital pharmacy',      'IPD, TPA, EMR prescriptions, ABDM',     'i-hospital'],
  ['new-pharmacy', 'Opening a new pharmacy', 'Start free from day one',               'i-sun'],
];
const NAV_RESOURCES = [
  ['calculator', 'Expiry loss calculator', 'Put a number on expiry loss',   'i-chart'],
  ['checklist',  'Buyer’s checklist',      '8 questions to ask any vendor', 'i-file'],
  ['blog',       'Blog & guides',          'Practical guides for the counter', 'i-register'],
  ['faq',        'FAQ',                    'Straight answers',              'i-chat'],
  ['support',    'Help & support',         'Phone, help centre, guides',    'i-headset'],
];
