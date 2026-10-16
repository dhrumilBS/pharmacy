<?php
/* Blog post. Facts: §0 expiry and stock (min/max, stock alert, short book, non-moving, reorder + PO on Advance).
   The numbers are an illustrative rule of thumb, labelled as such. */
require __DIR__ . '/../partials/blog.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$po = feature_plan_label('feat_purchase_order');
blog_start('min-max-stock');
?>
<?= bk_takeaways([
  'Minimum = what you sell until the next delivery, plus a small cushion.',
  'Maximum = minimum plus one normal order. Anything above that is cash sitting on a shelf.',
  'Set levels for your top 100 items first. They are most of your sales.',
  'Review once a month: what ran out, and what piled up.',
]) ?>

<p>Stock problems cost a medical store twice. Run out, and the customer buys elsewhere. Overstock, and the money that should be paying distributors sits on the shelf, sometimes until it expires. Minimum and maximum levels are how you stay between the two.</p>

<h2>The rule</h2>
<?= bk_rule('Minimum = daily sales × days to next delivery + a cushion.<br>Maximum = minimum + one normal order.') ?>
<p>The cushion covers a busy day or a late delivery. For most items, one extra day of sales is enough. For items a regular can’t do without, be more generous.</p>

<h2>A worked example</h2>
<?= bk_sum('A fast-moving BP tablet', [
  ['Strips sold on a normal day', '6'],
  ['Days until the next delivery', '2'],
  ['Plus a one-day cushion', '6 strips', '+'],
], ['Minimum stock', '18 strips'], 'Sales × days = 12 strips, plus 6 for the cushion. If you usually order 30 at a time, the maximum is 48.') ?>

<h2>Fast, medium and slow movers</h2>
<p>One rule, different results. The slower an item sells, the more its maximum should be held back.</p>
<?= bk_table(
  ['Item', 'Sold a day', 'Delivery in', 'Minimum', 'Maximum'],
  [
    ['<b>Fast mover</b><br><small>BP or diabetes tablet</small>', '6 strips', '2 days', '18', '48'],
    ['<b>Medium mover</b><br><small>Antacid, vitamins</small>', '2 strips', '2 days', '6', '20'],
    ['<b>Slow mover</b><br><small>Seasonal syrup</small>', '2 a month', '3 days', '1', '3'],
  ],
  'Illustrative numbers. Your delivery days and sales decide the right levels.',
  ['num' => [1, 3, 4], 'hl' => 3]
) ?>
<?= bk_note('tip', 'Keep slow items lean', '<p>For an item that sells a few times a month, keep the maximum low and check the non-moving items report before reordering. A missed sale on a slow item costs less than a box that expires.</p>') ?>

<h2>Where to start</h2>
<ol>
  <li><b>Your top 100 items.</b> Sort last month’s sales and set levels for the top of the list.</li>
  <li><b>Items regulars depend on.</b> Monthly BP, diabetes and thyroid medicines get a bigger cushion.</li>
  <li><b>Everything else, as you go.</b> Set levels when you place the next order for each item.</li>
</ol>
<?= bk_cta('inline', 'Running out of a few items every week?', 'See how the short book works', url('inventory')) ?>

<h2>Review once a month</h2>
<p>Levels drift as seasons change and doctors prescribe differently. Once a month, look at two lists: what ran out (raise the minimum) and what has barely moved (lower the maximum, or stop ordering).</p>
<?= bk_cta('feature', 'inventory', 'Min and max per item, a stock alert and short book' . ($po ? ', and purchase orders from the short book (' . $po . ')' : '') . '.') ?>
<?= bk_cta('panel', 'See the short book with your items', 'We’ll set levels on a few of your fast movers in a demo and show the stock alert fill in as you bill.') ?>
<?php
blog_end([
  ['How does reordering work in the software?', 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book.' . ($po ? ' On ' . e($po) . ' you can turn the short book into purchase orders.' : '')],
  ['Can I see which items aren’t selling?', 'Yes. The non-moving items report shows stock that isn’t selling, and the top-selling items report shows what is.'],
]);
