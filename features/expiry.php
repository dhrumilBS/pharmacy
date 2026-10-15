<?php
/* Feature page: Expiry & batch. Data only; layout in partials/feature-page.php (embeds the expiry loss estimator).
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (expiry and stock, purchase). Plan badges come live from the pricing API. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy expiry management software: batch tracking and expiry reports');
$description = 'Track every medicine by batch and expiry: stock expiry report item-wise or supplier-wise, first-expiry-first-out billing, purchase returns with credit notes, and a setting to stop expired sales.';
$page_key    = 'expiry';

$fp = [
  'hero' => ['crumb' => 'Expiry & batch', 'parent' => 'Features', 'eyebrow' => 'Expiry & batch tracking',
    'h1' => 'Catch expiry while you can still sell it or send it back',
    'lead' => 'Every batch carries its expiry from the moment the purchase bill is saved. See what expires in the date range you choose, bill the oldest batch first, and return short-expiry stock to the distributor on record.'],
  'ctas'   => ['demo' => 'See the expiry report in a demo', 'free' => 'Start free'],
  'assure' => ['Expiry report item-wise or supplier-wise', 'Oldest batch billed first (FEFO)', 'Setting to stop expired sales'],
  'screen' => ['v2-03-expiry', 'Stock expiry report, supplier-wise, next 60 days'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Expiry is the leak you only see too late',
    'intro' => 'Money you already paid to the distributor, sitting on the shelf until it can’t be sold.',
    'items' => [
      ['Found during a shelf check', 'Strips behind the new stock', 'A full box turns up behind newer batches. It expired two months ago, and it was paid for in full.', 'the purchase price of every strip, written off.'],
      ['The distributor visit', 'The return window has closed', 'You find near-expiry stock the week after the distributor’s return terms ran out. It stays on your books.', 'stock that could have gone back for credit.'],
      ['A patient comes back', 'An expired strip was billed', 'A busy staff member picked a strip without checking the date. The patient noticed before you did.', 'trust at your counter, and a conversation you didn’t want.'],
      ['Ordering day', 'Reordering what’s already there', 'An item is reordered while an older batch of the same item hasn’t moved in months.', 'more money stuck in stock that may expire too.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where expiry eats your margin, and what plugs it',
    'intro' => 'Each leak, then what plugs it and the plan that includes it.',
    'rows' => [
      ['Older batches left behind', 'New stock goes on the front of the shelf and sells first.', '<b>FEFO billing:</b> the batch that expires first fills in on every bill, so older stock goes out first.', null, 'Every plan'],
      ['Expiry you don’t see coming', 'Nobody checks dates until the shelf check.', '<b>Stock expiry report</b> for any date range, item-wise or supplier-wise, so you can act 30, 60 or 90 days early.', null],
      ['Returns that come too late', 'Near-expiry stock isn’t grouped by who supplied it.', '<b>Supplier-wise expiry list</b> plus <b>purchase returns with credit notes</b>, so short-expiry stock goes back on record.', 'feat_purchase_credit_note'],
      ['Expired strips at the counter', 'Dates are checked by eye, in a hurry.', '<b>A store setting</b> controls whether expired items can be billed at all.', null],
      ['Slow stock that will expire next', 'Items that don’t sell keep getting reordered.', '<b>Non-moving items report</b> shows what isn’t selling, before it reaches its expiry date.', 'feat_non_moving_item'],
      ['Batch and expiry typed wrong', 'A mistyped date on purchase entry hides the problem.', '<b>CSV import</b> brings batch and expiry from the distributor’s file instead of typing them.', 'feat_csv_import'],
      ['Write-offs nobody records', 'Expired stock is thrown out, but the system still shows it.', '<b>Stock adjustment</b> records the write-off, so stock and reports stay true.', 'feat_stock_adjustment'],
    ],
    'estimator' => true],

  'steps' => ['eyebrow' => 'How expiry tracking works', 'title' => 'Expiry goes in once, then follows the batch',
    'intro' => 'You don’t keep a separate expiry list. It builds itself from your purchase bills.',
    'items' => [
      ['Batch and expiry come in with the bill', 'Every purchase line carries batch, expiry and MRP, whether you import a CSV, scan with OCR or type it.', 'CSV · OCR · manual'],
      ['The counter sells oldest first', 'At billing, the batch that expires first is selected automatically. Staff can still pick another.', 'FEFO on every bill'],
      ['Check the expiry report weekly', 'Pick a date range, then view it item-wise or supplier-wise to see what needs action.', 'Item-wise or supplier-wise'],
      ['Push, return or write off', 'Move near-expiry stock forward, return it to the supplier with a credit note, or record a stock adjustment.', 'Every outcome on record'],
    ]],

  'caps' => ['eyebrow' => 'Expiry and batch tools', 'title' => 'Built around the batch, not just the item',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-alert', 'Stock expiry report', 'Batches expiring in any date range, item-wise or supplier-wise. Export to Excel for a returns list.', null],
      ['i-receipt', 'FEFO billing', 'The earliest-expiry batch fills in first on every bill.', null, 'Every plan'],
      ['i-lock', 'Block expired sales', 'A store setting controls whether expired items can be billed.', null],
      ['i-return', 'Purchase returns and credit notes', 'Send short-expiry stock back to the supplier and keep the credit on their ledger.', 'feat_purchase_credit_note'],
      ['i-box', 'Non-moving items report', 'See what isn’t selling, so you stop reordering it before it expires.', 'feat_non_moving_item'],
      ['i-file', 'Stock adjustment', 'Record write-offs and corrections, so stock on screen matches the shelf.', 'feat_stock_adjustment'],
      ['i-scan', 'Barcode labels', 'Print barcode labels for stock that has none, so it scans at the counter instead of being picked by hand.', 'feat_label_print'],
      ['i-shield', 'Item flags', 'Tag items as Schedule H or H1, and flag LASA, high-risk, antimicrobial and DPCO items in the item master.', null],
      ['i-upload', 'Opening stock with expiry', 'Import items and opening stock with batch and expiry from a CSV file when you start.', 'feat_csv_import'],
    ]],

  'guide' => ['eyebrow' => 'A weekly routine', 'title' => 'A 15-minute expiry check that pays for itself',
    'intro' => 'Practical habits from how careful stores manage expiry. The software gives you the list; these steps turn it into money saved.',
    'items' => [
      ['Pick one day a week', 'Run the stock expiry report at the same time each week, for example Monday before opening. A habit beats a big clean-up twice a year.'],
      ['Look 90 days ahead, by supplier', 'View it supplier-wise. Each supplier’s list becomes a ready returns list for their next visit.'],
      ['Check each distributor’s return terms', 'Return rules differ by distributor and company. Note the cut-off for each, and return before it, not after.'],
      ['Move near-expiry stock to the front', 'Anything inside 60 days goes to the front of the shelf. FEFO billing then sells it first.'],
      ['Stop reordering slow items', 'Cross-check with the non-moving items report. If it hasn’t sold, don’t order more while old stock is still on the shelf.'],
      ['Record what you throw away', 'Write off expired stock with a stock adjustment, so your stock value and reports stay honest.'],
    ],
    'note' => 'Return terms are set by your distributors, not by the software. Check them with each supplier.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring this list to a 30-minute demo. We’ll run it with sample stock, or with a few of your own items.',
    'items' => [
      'Run the stock expiry report for the next 90 days',
      'Switch it from item-wise to supplier-wise',
      'Bill an item with two batches and watch the older one fill in',
      'Turn on the setting that stops expired items being billed',
      'Make a purchase return with a credit note',
      'Open the non-moving items report',
    ]],

  'related' => ['billing', 'inventory', 'purchase'],

  'faq' => [
    ['How far ahead can I see expiring stock?', 'As far as you like. The stock expiry report works on any date range you choose, item-wise or supplier-wise.', true],
    ['Can I see expiry by supplier for returns?', 'Yes. Supplier-wise view groups near-expiry batches by who supplied them, so each list is ready for that distributor’s visit.', true],
    ['Can I stop expired medicines being billed?', 'Yes. A store setting controls whether expired items can be sold.', true],
    ['Does FEFO force staff to sell a batch?', 'No. The earliest-expiry batch fills in first, and staff can still choose another batch when needed.', true],
    ['How do expiry returns work?', 'Make a purchase return against the supplier and record the credit note, so the credit shows on their ledger.' . (($cn = feature_plan_label('feat_purchase_credit_note')) ? ' Purchase credit notes are on ' . e($cn) . '.' : ''), true],
    ['Will expiry dates come across when I switch?', 'Yes, if your file has them. The initial data import takes batch number and expiry date with your opening stock. See <a href="' . url('switch') . '">switching</a>.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
