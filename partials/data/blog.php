<?php
/*
 * Blog posts: metadata only (hub cards, related posts, schema). The article body lives in blog/<slug>.php.
 * cover = [screen name in assets/img/screens (without v2- and .svg), zoom, focus point] so each card shows the
 * product screen the post is about. Visible area starts at focus × (1 − 1/zoom): 1.3 with '24% 33%' starts just
 * right of the app's side rail and below its top bar, so no text is cut mid-word. Dates are publish dates; 'updated' is optional.
 * Keep posts practical and evergreen. GST rates and Schedule H/H1 rules need CA / compliance review before a post
 * on them goes live (PHARMACY-SITE-STRUCTURE.md §4.7), so there are none yet.
 */
const BLOG_CATS = [
  'expiry'    => ['Expiry & stock', 'teal'],
  'purchase'  => ['Purchase', 'blue'],
  'switching' => ['Switching', 'navy'],
  'staff'     => ['Counter & staff', 'amber'],
  'buying'    => ['Buying guides', 'slate'],
];

const BLOG_POSTS = [
  'cut-expiry-loss' => [
    'title'   => 'How to cut expiry loss in a medical store: a 15-minute weekly routine',
    'dek'     => 'Expiry is the leak most owners never put a number on. Here is a simple weekly routine, the maths behind it, and what to do with each near-expiry batch.',
    'cat'     => 'expiry', 'date' => '2026-10-14', 'mins' => 9, 'featured' => true,
    'cover'   => ['03-expiry', 1.3, '24% 33%'],
    'feature' => 'expiry',
  ],
  'switch-pharmacy-software' => [
    'title'   => 'Switching pharmacy software without stopping billing: a four-week plan',
    'dek'     => 'The real risk in switching isn’t the new software, it’s the first week at the counter. A week-by-week plan to run both systems and go live on your own day.',
    'cat'     => 'switching', 'date' => '2026-10-09', 'mins' => 8,
    'cover'   => ['10-initial-import', 1.3, '24% 33%'],
    'feature' => 'switch',
  ],
  'min-max-stock' => [
    'title'   => 'How to set minimum and maximum stock for a medical store',
    'dek'     => 'Too little and you lose the sale; too much and your cash sits on the shelf. A plain rule for setting levels, with worked numbers for fast and slow movers.',
    'cat'     => 'expiry', 'date' => '2026-10-02', 'mins' => 7,
    'cover'   => ['05-reorder', 1.3, '24% 33%'],
    'feature' => 'inventory',
  ],
  'check-distributor-bill' => [
    'title'   => 'Six checks before you save a distributor bill',
    'dek'     => 'Most purchase mistakes are made in the minute before you press save. Six checks that catch wrong batches, missing free quantity and rates that drifted.',
    'cat'     => 'purchase', 'date' => '2026-09-25', 'mins' => 6,
    'cover'   => ['04-purchase-import', 1.3, '24% 33%'],
    'feature' => 'purchase',
  ],
  'desktop-vs-browser' => [
    'title'   => 'Desktop pharmacy software or browser-based: what actually changes',
    'dek'     => 'An honest comparison for owners weighing the switch: where the data lives, what happens when a PC fails, how updates and pricing work, and the questions to ask any vendor.',
    'cat'     => 'buying', 'date' => '2026-09-18', 'mins' => 8,
    'cover'   => ['02-dashboard', 1.3, '24% 33%'],
    'feature' => 'features',
  ],
  'staff-logins-roles' => [
    'title'   => 'Staff logins and roles: a security checklist for pharmacy owners',
    'dek'     => 'Most store problems aren’t break-ins, they’re shared passwords. How to set up logins and roles, and the ten-minute routine for the day someone leaves.',
    'cat'     => 'staff', 'date' => '2026-09-11', 'mins' => 6,
    'cover'   => ['09-security', 1.3, '24% 33%'],
    'feature' => 'security',
  ],
];

/** One post's metadata with its slug, or null. */
function blog_post(string $slug): ?array { return isset(BLOG_POSTS[$slug]) ? BLOG_POSTS[$slug] + ['slug' => $slug] : null; }

/** All posts, newest first. */
function blog_posts(): array {
  $all = [];
  foreach (BLOG_POSTS as $slug => $p) $all[] = $p + ['slug' => $slug];
  usort($all, fn($a, $b) => strcmp($b['date'], $a['date']));
  return $all;
}

/** URL of a post. */
function blog_url(string $slug): string { return BASE . 'blog/' . $slug . '.php'; }

/** "14 Oct 2026" */
function blog_date(string $ymd): string { return date('j M Y', strtotime($ymd)); }

/** Author line: the brand team (no invented people). */
function blog_author(): string { return BRAND ? 'The ' . BRAND . ' team' : 'Our product team'; }
