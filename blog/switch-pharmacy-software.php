<?php
/* Blog post. Facts: §0 onboarding (email OTP sign-up, Add Initial Data CSV with sample sheet and line errors,
   walkthroughs). Assisted import and offline behaviour are unconfirmed: never claim them. */
require __DIR__ . '/../partials/blog.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$free = pricing_split()['free'][0] ?? null;
blog_start('switch-pharmacy-software');
?>
<?= bk_takeaways([
  'Don’t switch on a single day. Run the old and new systems side by side, then pick your go-live day.',
  'Import items and opening stock <b>with batch and expiry</b>. Item names alone aren’t enough to bill a medicine.',
  'Test with real bills from your own counter before you rely on it in the evening rush.',
  'Give every staff member their own login from the first day, not after go-live.',
]) ?>

<p>Owners rarely worry about whether new software can bill. They worry about the Saturday evening after they switch, with a queue at the counter and staff who learned the screen yesterday. That worry is reasonable, and a plan removes most of it.</p>

<h2>Why “switch over the weekend” goes wrong</h2>
<p>A one-day switch puts three risks on the same day: data that hasn’t been checked, staff who haven’t practised, and a printer nobody has tested. If any of them fails, billing slows down exactly when you can least afford it.</p>
<?= bk_compare([
  'title' => 'Two ways to change software',
  'a' => ['Big-bang switch', 'One day'],
  'b' => ['Parallel run', 'Two to four weeks'],
  'win' => 'b',
  'rows' => [
    ['Old system', 'Switched off on day one', 'Keeps running until you’re confident'],
    ['Data check', 'Found at the counter, in front of customers', 'Found during the trial, with time to fix'],
    ['Staff', 'Learn during the rush', 'Practise on quiet hours first'],
    ['If something goes wrong', 'Billing stops', 'Carry on in the old system'],
  ],
  'verdict' => 'Run both. It costs a few extra minutes a day for a couple of weeks, and it removes the day-one risk.',
]) ?>

<h2>A four-week plan</h2>
<?= bk_steps([
  ['Week 1: set up and import', '<p>Create the store account' . ($free ? ' on the free ' . e($free['name']) . ' plan' : '') . '. Export items and stock from your current software or Excel, match your columns to the sample sheet, and import. Fix the rows that are flagged.</p>', 'Old system: running as usual'],
  ['Week 2: suppliers, staff and printers', '<p>Add your distributors and each staff member with their own login and role. Print a few test bills on your thermal and A5 printers, and scan a few strips with the barcode reader.</p>', 'Old system: running as usual'],
  ['Week 3: bill in quiet hours', '<p>Bill a few real customers on the new system in the morning, while the old one handles the rush. Enter one distributor bill in both and compare the stock.</p>', 'Both systems'],
  ['Week 4: go live on your day', '<p>Pick a quiet day of the week. Do a physical count of fast movers in the morning, switch billing fully, and keep the old system for reference.</p>', 'New system'],
]) ?>
<?= bk_note('note', 'Start small if your item list is long', '<p>If cleaning thousands of items feels like too much, import your top sellers first with batch and expiry, and add the rest as stock arrives. Every purchase bill you import adds its items for you.</p>') ?>

<h2>What to bring across</h2>
<?= bk_table(
  ['Data', 'Bring across?', 'Notes'],
  [
    ['<b>Items with batch, expiry and MRP</b>', 'Yes, before go-live', 'The minimum you need to bill a medicine'],
    ['<b>Opening stock quantity</b>', 'Yes, before go-live', 'Count fast movers on the import day'],
    ['<b>HSN code and GST rate</b>', 'Yes', 'Match them to your current invoices'],
    ['<b>Suppliers</b>', 'Yes', 'Add them in the account master'],
    ['<b>Minimum and maximum stock</b>', 'Helpful', 'Can be set later, item by item'],
    ['<b>Old bills and reports</b>', 'Keep in the old system', 'Export what you need for reference'],
  ],
  'Columns available on the import screen include item, generic name, batch, expiry, MRP, HSN, stock, min/max and supplier.',
  ['hl' => 1]
) ?>
<?= bk_note('caution', 'Clean the file before you import', '<ul><li>Remove items you no longer stock and batches with zero quantity.</li><li>Use one date format for expiry, for example DD-MM-YYYY.</li><li>Check MRP is per strip or per pack, the way you sell.</li></ul>') ?>
<?= bk_cta('feature', 'switch', 'Import items and opening stock from CSV, with a sample sheet and errors flagged line by line.') ?>

<h2>The go-live day checklist</h2>
<?= bk_checklist('On the day you switch', [
  'Count fast-moving items and correct their stock',
  'Every staff member can log in with their own account',
  'Thermal and A5 bills print correctly',
  'The barcode reader finds the right items',
  'One distributor bill entered and checked',
  'The old system is still available for reference',
]) ?>
<?= bk_cta('panel', 'Plan your switch on a call', 'Tell us what you use today and we’ll walk through the import with a sample of your own items.') ?>
<?php
blog_end([
  ['Will billing stop while we switch?', 'No. Your current software keeps running until you choose to go live.'],
  ['Can I bring over data from my current software?', 'Yes, through CSV import. Export your items and stock, match your columns to the sample sheet and import. Rows with errors are flagged so you can fix them.'],
  ['Do I lose my old data?', 'No. Your old system keeps its records. Import what you need to bill from day one, and keep the old reports for reference.'],
]);
