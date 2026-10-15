<?php
/*
 * Live plan data from the app's public pricing API (PRICING_API in config.php).
 * Prices, plans, terms, features and per-plan statuses all come from the backend, so a change there shows up
 * on the site within PRICING_TTL seconds with no code edit.
 *
 * Lookup order (the page never breaks if the API is slow or down):
 *   1. fresh cache  (partials/data/cache/pricing-live.json, younger than PRICING_TTL)
 *   2. live API     (saved to the cache on success)
 *   3. stale cache  (last good response; retried again after PRICING_RETRY seconds)
 *   4. snapshot     (partials/data/pricing.json, committed fallback)
 */
require_once __DIR__ . '/config.php';

const PRICING_CACHE    = __DIR__ . '/data/cache/pricing-live.json';
const PRICING_SNAPSHOT = __DIR__ . '/data/pricing.json';

/** True when $d has the shape the pricing page needs. */
function pricing_valid($d): bool {
  if (!is_array($d)) return false;
  foreach (['plans', 'terms', 'groups', 'features', 'statuses'] as $k) if (!isset($d[$k]) || !is_array($d[$k])) return false;
  if (!$d['plans'] || !$d['terms']) return false;
  foreach ($d['plans'] as $p) if (!isset($p['id'], $p['name'], $p['price'])) return false;
  return true;
}

/** GET the API; returns decoded data or null. Short timeouts so a slow API can't hang the page. */
function pricing_fetch(): ?array {
  $body = null;
  if (function_exists('curl_init')) {
    $ch = curl_init(PRICING_API);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3,
      CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 5,
      CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $res  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res !== false && $code === 200) $body = $res;
  } else {
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'header' => "Accept: application/json\r\n"]]);
    $res = @file_get_contents(PRICING_API, false, $ctx);
    if ($res !== false) $body = $res;
  }
  if ($body === null) return null;
  $d = json_decode($body, true);
  return pricing_valid($d) ? $d : null;
}

/** Read a JSON file into validated data, or null. */
function pricing_read(string $file): ?array {
  if (!is_file($file)) return null;
  $d = json_decode((string) file_get_contents($file), true);
  return pricing_valid($d) ? $d : null;
}

/** Plan data (memoised per request). $d['_source'] = 'live' | 'cache' | 'stale' | 'snapshot' (for debugging). */
function pricing_data(): array {
  static $data = null;
  if ($data !== null) return $data;

  $age    = is_file(PRICING_CACHE) ? time() - filemtime(PRICING_CACHE) : PHP_INT_MAX;
  $cached = $age < PRICING_TTL ? pricing_read(PRICING_CACHE) : null;
  if ($cached) return $data = $cached + ['_source' => 'cache'];

  if ($live = pricing_fetch()) {
    $dir = dirname(PRICING_CACHE);
    if (is_dir($dir) || @mkdir($dir, 0775, true)) {
      $tmp = PRICING_CACHE . '.' . getmypid() . '.tmp';                 // write then rename: readers never see half a file
      if (@file_put_contents($tmp, json_encode($live, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false) @rename($tmp, PRICING_CACHE);
      @unlink($tmp);
    }
    return $data = $live + ['_source' => 'live'];
  }

  if ($stale = pricing_read(PRICING_CACHE)) {
    @touch(PRICING_CACHE, time() - PRICING_TTL + PRICING_RETRY);         // API down: don't retry on every request
    return $data = $stale + ['_source' => 'stale'];
  }
  $snap = pricing_read(PRICING_SNAPSHOT);
  if (!$snap) throw new RuntimeException('Pricing data unavailable: API failed and partials/data/pricing.json is missing or invalid.');
  return $data = $snap + ['_source' => 'snapshot'];
}

/** Plans sorted by the backend's sort_order, keyed by id. */
function pricing_plans(): array {
  $plans = pricing_data()['plans'];
  usort($plans, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
  return array_column($plans, null, 'id');
}

/** One plan by id (1 Starter, 3 Basic, 2 Advance, 5 Enterprise), or null. */
function pricing_plan(int $id): ?array { return pricing_plans()[$id] ?? null; }

/** "₹4,999" for a paid plan, "Free" for a free one, "Custom" for a quoted one. */
function plan_price(int $id): string {
  $p = pricing_plan($id);
  if (!$p) return '';
  if (!empty($p['is_custom'])) return 'Custom';
  return $p['price'] > 0 ? inr($p['price']) : 'Free';
}

/** Monthly bill limits of a plan: ['sales' => 10, 'purchase' => 25]; null entries = unlimited. */
function plan_bill_limits(int $id): array {
  $l = pricing_plan($id)['bill_limit'] ?? null;
  $l = is_array($l) ? $l : [];
  return ['sales' => isset($l['INE']) ? (int) $l['INE'] : null, 'purchase' => isset($l['PUR']) ? (int) $l['PUR'] : null];
}

/** Term labels as prose: "1, 3 or 5 years". */
function pricing_terms_text(): string {
  $years = array_map(fn($t) => (int) $t['years'], pricing_data()['terms']);
  sort($years);
  if (count($years) === 1) return $years[0] . ($years[0] === 1 ? ' year' : ' years');
  $last = array_pop($years);
  return implode(', ', $years) . ' or ' . $last . ' years';
}

/** Join a list as prose: "a", "a and b", "a, b and c" ($word = 'and' | 'or'). */
function pricing_join(array $items, string $word = 'and'): string {
  if (count($items) < 2) return implode('', $items);
  $last = array_pop($items);
  return implode(', ', $items) . ' ' . $word . ' ' . $last;
}

/** Plans split into free / paid / custom (each in sort_order). */
function pricing_split(): array {
  $out = ['free' => [], 'paid' => [], 'custom' => []];
  foreach (pricing_plans() as $p) $out[!empty($p['is_custom']) ? 'custom' : ($p['price'] > 0 ? 'paid' : 'free')][] = $p;
  return $out;
}

/** FAQ answer: "Starter is free. Basic is ₹4,999 a year and Advance is ₹9,999 a year, plus 18% GST. Enterprise for chains is quoted." */
function pricing_cost_text(): string {
  $s = pricing_split(); $out = [];
  if ($s['free'])   $out[] = pricing_join(array_column($s['free'], 'name')) . (count($s['free']) > 1 ? ' are' : ' is') . ' free.';
  if ($s['paid'])   $out[] = pricing_join(array_map(fn($p) => $p['name'] . ' is ' . inr($p['price']) . ' a year', $s['paid'])) . ', plus 18% GST.';
  if ($s['custom']) $out[] = pricing_join(array_column($s['custom'], 'name')) . ' for chains is quoted.';
  return implode(' ', $out);
}

/** Comparison cell: "Free Starter; ₹4,999 or ₹9,999 a year, published". */
function pricing_compare_text(): string {
  $s = pricing_split(); $out = [];
  if ($s['free']) $out[] = 'Free ' . pricing_join(array_column($s['free'], 'name'));
  if ($s['paid']) $out[] = pricing_join(array_map(fn($p) => inr($p['price']), $s['paid']), 'or') . ' a year, published';
  return implode('; ', $out);
}

/**
 * Lowest plan (by sort_order) that includes a feature, looked up live by the API's unique_name
 * (e.g. 'feat_purchase_order'). Null when the API doesn't list the feature or no plan includes it.
 */
function feature_plan(string $unique): ?array {
  static $idx = null;
  if ($idx === null) {
    $d = pricing_data(); $idx = [];
    $byId = array_column($d['features'], 'unique_name', 'id');
    foreach ($d['statuses'] as $st) if (isset($byId[$st['pricing_feature_id']])) $idx[$byId[$st['pricing_feature_id']]][] = (int) $st['plan_template_id'];
  }
  if (empty($idx[$unique])) return null;
  foreach (pricing_plans() as $id => $p) if (in_array((int) $id, $idx[$unique], true)) return $p;   // plans are in sort_order
  return null;
}

/** Plan badge text for a feature: "Every plan", "Basic and up", "Enterprise". Null when not mapped. */
function feature_plan_label(string $unique): ?string {
  $p = feature_plan($unique);
  if (!$p) return null;
  $plans = pricing_plans();
  $first = reset($plans); $last = end($plans);
  if ((int) $p['id'] === (int) $first['id']) return 'Every plan';
  if ((int) $p['id'] === (int) $last['id']) return $p['name'];
  return $p['name'] . ' and up';
}

/** Users per plan as prose, live from the API: "Starter 1, Basic 3, Advance 10 and Enterprise unlimited". */
function plan_users_text(): string {
  return pricing_join(array_map(fn($p) => $p['name'] . ' ' . ($p['user_limit'] ?? 'unlimited'), array_values(pricing_plans())));
}

/** Included message credits per plan, live from the API: "Starter 10 WhatsApp, 10 SMS and 10 email; Basic …".
    Free plans first, custom plans included. Wording stays neutral on the period (pricing page flags it [confirm]). */
function plan_credits_text(): string {
  $out = [];
  foreach (pricing_plans() as $p) {
    $m = $p['message_service'] ?? [];
    $out[] = $p['name'] . ' ' . pricing_join([(int) ($m['whatsapp'] ?? 0) . ' WhatsApp', (int) ($m['sms'] ?? 0) . ' SMS', (int) ($m['email'] ?? 0) . ' email']);
  }
  return implode('; ', $out);
}
