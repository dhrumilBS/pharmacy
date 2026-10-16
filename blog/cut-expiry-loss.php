<?php
/* Blog post. Product facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (expiry and stock, purchase). Return terms are set by
   distributors, so the post never states them as fact. */
require __DIR__ . '/../partials/blog.php';
blog_start('cut-expiry-loss');
?>
<?= bk_takeaways([
  'Expiry loss is money you have already paid the distributor. Most stores have never put a number on it.',
  'Selling the <b>first-expiring</b> batch first (FEFO) matters more than selling the first-received batch first (FIFO).',
  'A 15-minute check once a week, grouped by supplier, turns near-expiry stock into returns, pushes or planned write-offs.',
  'Return windows belong to your distributors, not to you. Note each one and act before it closes.',
]) ?>

<p>Ask an owner what they lost to theft last year and you’ll get a guess. Ask what they lost to expiry and you’ll usually get a pause. Expiry doesn’t arrive as one event. It’s a strip here, a box there, found during a shelf check months after it could have been sold or sent back.</p>
<p>This guide is about turning that pause into a number, and the number into a routine.</p>

<h2>Why expiry is the quiet leak</h2>
<p>Every strip that expires on your shelf was paid for in full. Unlike a slow month, there’s no chance to make it up later: the money has already left. And because nobody writes “expired” in a sales report, it rarely shows up until stock-taking.</p>
<?= bk_sum('One percent of purchases', [
  ['Monthly purchase from distributors', '₹8,00,000'],
  ['Share that expires before it sells', '1%'],
  ['Months in a year', '12'],
], ['Expiry loss in a year', '₹96,000'], 'Put in your own numbers. Even half a percent is a figure most owners would rather keep.') ?>
<?= bk_cta('inline', 'Want this with your numbers?', 'Use the expiry loss calculator', url('calculator')) ?>

<h2>FIFO or FEFO: which batch goes out first</h2>
<p>Most stores are taught FIFO: first in, first out. It works for goods without an expiry date. Medicines don’t arrive in expiry order, though. A distributor can send a batch today that expires <i>before</i> the one you received last month.</p>
<?= bk_compare([
  'title' => 'Two ways to pick the batch at the counter',
  'a' => ['FIFO', 'First in, first out'],
  'b' => ['FEFO', 'First expiry, first out'],
  'win' => 'b',
  'rows' => [
    ['Which batch goes out', 'The one received earliest', 'The one that expires earliest'],
    ['When batches arrive out of order', 'A newer batch can expire on the shelf', 'Still sells the shortest-dated batch'],
    ['What staff need to check', 'Delivery date, often not on the strip', 'Expiry date, printed on every strip'],
    ['Fits medicines', 'Sometimes', 'Yes'],
  ],
  'verdict' => 'For medicines, use FEFO. If the software fills in the earliest-expiry batch at billing, staff don’t have to remember it in the rush.',
]) ?>
<?= bk_note('tip', 'Let the shelf match the screen', '<p>FEFO at billing only works if the shortest-dated strips are also the easiest to reach. When you restock, put new boxes <b>behind</b> the old ones, not in front.</p>') ?>

<h2>The 15-minute weekly routine</h2>
<p>A big clean-up twice a year finds expired stock. A small check every week finds stock that’s <i>about</i> to expire, while you can still do something about it.</p>
<?= bk_steps([
  ['Pick a fixed slot', '<p>Same day, same time, for example Monday before opening. A habit beats good intentions.</p>', '2 minutes'],
  ['Pull the next 90 days, by supplier', '<p>Run your stock expiry report for the next 90 days and group it by supplier. Each supplier’s list becomes a returns list for their next visit.</p>', '3 minutes'],
  ['Decide each batch', '<p>Use the table below: return it, push it, or plan for it. Mark the decision next to each line.</p>', '5 minutes'],
  ['Move stock and stop reorders', '<p>Bring short-dated stock to the front, and check slow items before the next order goes out.</p>', '5 minutes'],
]) ?>

<h2>What to do with each near-expiry batch</h2>
<p>A starting point most stores can use. Adjust the windows to your distributors’ return terms and how fast each item sells.</p>
<?= bk_table(
  ['Time to expiry', 'What to do', 'Why'],
  [
    ['<b>90 to 60 days</b>', 'Check against the supplier’s return terms; flag for return if it won’t sell in time', 'Most return windows close before the last month'],
    ['<b>60 to 30 days</b>', 'Move to the front of the shelf; stop reordering the item', 'FEFO billing will sell it first if it’s within reach'],
    ['<b>Under 30 days</b>', 'Return if the supplier still accepts it; otherwise plan the write-off', 'Patients need enough days to finish the course'],
    ['<b>Expired</b>', 'Remove from the shelf and record a stock adjustment', 'Keeps stock and reports true'],
  ],
  'A starting point, not a rule. Return windows differ by distributor and company.',
  ['hl' => 1]
) ?>
<?= bk_note('caution', 'Return windows are the distributor’s, not yours', '<p>Return and credit rules differ by distributor and by company, and they can change. Write down each supplier’s cut-off and act on the earlier date. Nothing in your software can extend a window that has closed.</p>') ?>

<h2>Stop expired strips reaching the counter</h2>
<p>Even with a routine, an expired strip can slip through on a busy evening. Two simple guards help:</p>
<ul>
  <li><b>Block expired sales.</b> A store setting can stop expired items being billed at all, so the bill can’t go through.</li>
  <li><b>Record what you remove.</b> When you take expired stock off the shelf, record a stock adjustment, so the screen and the shelf agree.</li>
</ul>
<?= bk_cta('feature', 'expiry', 'Batch-wise expiry report, FEFO billing, purchase returns with credit notes and a setting to stop expired sales.') ?>

<h2>Stop buying tomorrow’s expiry</h2>
<p>The cheapest expiry to fix is the one you never buy. Before each order, look at what isn’t moving. If an item hasn’t sold in months and an older batch is still on the shelf, it doesn’t need another box.</p>
<?= bk_rule('If it hasn’t sold since the last order, <b>don’t order it again</b> until the shelf is nearly empty.') ?>
<?= bk_note('example', 'A slow item, caught in time', '<p>A cough syrup sells two bottles a month. Ten are on the shelf, the earliest expiring in four months. Ordering another five would leave stock that can’t sell before it expires. Skip this order, push the existing bottles forward and review next month.</p>') ?>

<h2>Your weekly expiry checklist</h2>
<?= bk_checklist('Every week, before opening', [
  'Run the stock expiry report for the next 90 days, grouped by supplier',
  'Mark each batch: return, push or plan',
  'Note each supplier’s return cut-off on their list',
  'Move anything under 60 days to the front of the shelf',
  'Check the non-moving items report before placing orders',
  'Record a stock adjustment for anything removed',
]) ?>
<?= bk_cta('panel', 'See your own expiry report in a demo', 'Bring a few items from your shelf. We’ll show the batch-wise expiry report and how FEFO picks the batch at billing.') ?>
<?php
blog_end([
  ['What is FEFO?', 'First expiry, first out: at billing, the batch that expires first is sold first, whatever order the stock arrived in.'],
  ['Can the software stop expired medicines being billed?', 'Yes. A store setting controls whether expired items can be sold.'],
  ['Can I see near-expiry stock by supplier?', 'Yes. The stock expiry report works on any date range, item-wise or supplier-wise.'],
  ['How do I record a return to the distributor?', 'Make a purchase return against the supplier and record the credit note, so the credit shows on their ledger.'],
]);
