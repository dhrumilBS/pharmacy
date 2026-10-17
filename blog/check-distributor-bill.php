<?php
/* Blog post. Facts: §0 purchase (CSV/OCR import, scheme & free qty, PTR and margin on save, credit notes,
   supplier ledger). The purchase grid's Diff % column compares margin with the last purchase or purchase average (seen in the app 15 Oct 2026); there is no pop-up rate alert. */
require __DIR__ . '/../partials/blog.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$rc = feature_plan_label('feat_supplier_rate_contract');
blog_start('check-distributor-bill');
?>
<?= bk_takeaways([
  'Most purchase mistakes happen in the minute before you press save, and they surface weeks later.',
  'Free quantity belongs on the line as stock, not as a discount.',
  'A margin that looks low on save is your cue to check the rate.',
  'Decide about short-expiry stock now, while the delivery is still in front of you.',
]) ?>

<p>A distributor bill decides most of a medical store’s margin: the rate you paid, the scheme you got, and what you can still send back. It also tends to be entered last thing at night. Importing the bill instead of typing it removes most typing errors. These six checks catch the rest.</p>

<h2>1. Batch and expiry match the carton</h2>
<p>Pick three lines, especially anything short-dated, and compare the batch and expiry on screen with the carton in front of you. A wrong batch now means a wrong FEFO pick at the counter later.</p>

<h2>2. Free quantity is on the line</h2>
<?= bk_compare([
  'title' => 'The same 10 + 1 scheme, entered two ways',
  'a' => ['Entered as a discount', 'Common mistake'],
  'b' => ['Entered as free quantity', 'Correct'],
  'win' => 'b',
  'rows' => [
    ['Stock on screen', '10 strips', '11 strips'],
    ['Cost per strip', 'Lower rate, but one strip missing', 'True cost across 11'],
    ['At the counter', 'The 11th strip can’t be billed', 'All 11 sell normally'],
  ],
  'verdict' => 'Record free quantity on the purchase line, so stock and cost are both right.',
]) ?>

<h2>3. The rate is what you agreed</h2>
<p>Compare the rate (PTR) with the last bill from the same distributor. The margin shown when you save is a quick sense check: if a fast mover’s margin suddenly looks thin, look at the rate before you save.</p>
<?= bk_note('tip', 'Read the Diff % column', '<p>The purchase screen has a Diff % column that compares each line’s margin with your last purchase, or with the purchase average. A negative number means you now earn less on that item than last time: check the rate before you save. It isn’t a pop-up alert, so make glancing at it a habit.' . ($rc ? ' On ' . e($rc) . ', supplier rate contracts record agreed rates per supplier.' : '') . '</p>') ?>

<h2>4. MRP is per strip or per pack, the way you sell</h2>
<p>If you sell by the strip, the MRP on the line should be per strip. A pack MRP entered as a strip MRP shows up at the counter as a price that’s ten times too high.</p>

<h2>5. GST matches the item master</h2>
<p>If the rate on the distributor’s bill and the rate in your item master disagree, find out which is right and fix the item before saving. Sales of that item take their tax from the item master.</p>

<h2>6. Short-expiry stock is flagged</h2>
<p>If the delivery includes stock close to expiry, decide now: accept it, or keep it aside to return on the rep’s next visit. It’s much harder to argue a week later.</p>

<h2>Common mistakes and where they show up</h2>
<?= bk_table(
  ['Mistake on the bill', 'Where you notice it', 'How long it can hide'],
  [
    ['<b>Wrong batch or expiry</b>', 'Expiry report and FEFO picks are wrong', 'Months'],
    ['<b>Free quantity missing</b>', 'Stock count is short; margin is off', 'Until stock-taking'],
    ['<b>Rate higher than agreed</b>', 'Margin report for the item', 'Every bill until someone looks'],
    ['<b>Pack MRP as strip MRP</b>', 'A customer at the counter', 'The first sale'],
    ['<b>Credit note not entered</b>', 'Supplier statement disagrees with your ledger', 'Until payment day'],
  ],
  'Ranked roughly by how long each mistake can go unnoticed.'
) ?>
<?= bk_checklist('Before you press save', [
  'Three lines checked against the carton for batch and expiry',
  'Free quantity entered as stock on the line',
  'Margin on save looks normal for fast movers',
  'MRP is per strip or pack, the way you sell',
  'GST on the bill matches the item master',
  'Short-expiry stock set aside, or accepted on purpose',
]) ?>
<?= bk_cta('feature', 'purchase', 'Import bills by CSV or OCR, with scheme and free quantity on the line and margin worked out on save.') ?>
<?= bk_cta('panel', 'Bring a distributor bill to a demo', 'We’ll import it, show the free quantity in stock and the margin on save.') ?>
<?php
blog_end([
  ['Do I have to type every distributor bill?', 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). You can still type a bill by hand when needed.'],
  ['Does it record scheme and free quantity?', 'Yes. Scheme and free quantity are recorded on the purchase line, and PTR and margin are worked out when you save.'],
]);
