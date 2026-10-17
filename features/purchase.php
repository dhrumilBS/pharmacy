<?php
/* Feature page: Purchase & suppliers. Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (purchase). The purchase grid has a Diff % column (margin vs last purchase or purchase average; seen in the app 15 Oct 2026). Don't claim a pop-up rate alert.
   Plan badges come live from the pricing API. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy purchase management software: import distributor bills by CSV or OCR');
$description = 'Stop typing distributor bills line by line: import them by CSV or scan with OCR, record scheme and free quantity, see PTR and margin on save, and keep supplier ledgers, dues and credit notes in one place.';
$page_key    = 'purchase';

$free   = pricing_split()['free'][0] ?? null;
$limits = $free ? plan_bill_limits((int) $free['id']) : ['sales' => null, 'purchase' => null];

/* Worked example (illustrative): time spent typing distributor bills */
$te = ['lines' => 40, 'secs' => 30, 'bills' => 3, 'days' => 30];
$te_day_min   = (int) round($te['lines'] * $te['secs'] * $te['bills'] / 60);
$te_month_hrs = (int) round($te_day_min * $te['days'] / 60);
$rc = feature_plan_label('feat_supplier_rate_contract');

$fp = [
  'hero' => ['crumb' => 'Purchase & suppliers', 'parent' => 'Features', 'eyebrow' => 'Purchase & suppliers',
    'h1' => 'Stop typing distributor bills line by line after closing',
    'lead' => 'Import the bill as a CSV file or scan the printed page with OCR. Batch, expiry, MRP, PTR and GST come in once, scheme and free quantity are recorded, margin is worked out on save, and the supplier’s ledger updates by itself.'],
  'ctas'   => ['demo' => 'Import a bill in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['CSV import with column mapping', 'OCR scan of printed bills (uses credits)', $free && $limits['purchase'] !== null ? $free['name'] . ' plan: ' . $limits['purchase'] . ' purchase bills a month, free' : null])),
  'screen' => ['v2-04-purchase-import', 'Distributor bill imported from a CSV file'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Most of your margin is decided on the purchase bill',
    'intro' => 'Purchase rate, scheme and returns decide what a medical store keeps. They’re also where the tiredest typing happens.',
    'items' => [
      ['11 pm, after closing', 'Three bills still to enter', 'A 40-line distributor bill, typed line by line: batch, expiry, MRP, rate, GST. One wrong digit and stock is wrong for months.', 'your evening, and errors that surface later.'],
      ['Delivery day', 'The free quantity goes missing', 'The bill says 10 + 1 free. The free strip isn’t entered, so cost per strip and margin are both wrong.', 'margin you earned but can’t see.'],
      ['Checking the rate', 'Was that the rate you agreed?', 'The distributor rep promised a rate on the phone. Nobody compares it with the bill.', 'a few rupees a strip, every bill, without anyone noticing.'],
      ['Payment day', 'What do we actually owe?', 'Bills, returns and part payments are in three places. You pay what the rep says.', 'overpaying, or a strained relationship with a good supplier.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where purchase money leaks, and what plugs it',
    'intro' => 'Each leak, then what plugs it and the plan that includes it.',
    'rows' => [
      ['Typing errors on every bill', 'Batch, expiry and rate retyped by hand, late at night.', '<b>CSV import</b> with column mapping, or <b>OCR import</b> from a scanned bill (uses OCR credits).', 'feat_csv_import'],
      ['Free quantity not recorded', 'Schemes like 10 + 1 are skipped or entered as a discount.', '<b>Scheme and free quantity</b> are recorded on the line, so cost and stock are right.', null, 'Every plan'],
      ['Margin you can’t see', 'Nobody works out margin per item at entry.', '<b>PTR and margin are calculated on save</b>, plus a <b>margin report</b> to review later.', 'feat_margin_report'],
      ['Rates drifting from what was agreed', 'Promised rates live in a phone call.', 'A <b>Diff % column</b> compares each line’s margin with the last purchase or the purchase average, so a rate that went up stands out before you save.' . ($rc ? ' <b>Supplier rate contracts</b> record agreed rates (' . e($rc) . ').' : ''), null],
      ['Returns that never become credit', 'Stock goes back, but the credit is never adjusted.', '<b>Purchase returns with credit notes</b>, kept on the supplier’s ledger.', 'feat_purchase_credit_note'],
      ['Paying the wrong amount', 'Dues are tracked from memory and the rep’s statement.', '<b>Supplier ledger and dues</b> with a <b>supplier due report</b>, and supplier vouchers for payments.', 'feat_supplier_due_report'],
      ['Bills changed or lost later', 'Paper bills go missing; saved entries get edited.', '<b>Bill attachment</b> keeps the original, and <b>lock invoices</b> stops quiet changes.', 'feat_bill_attachment'],
    ],
    'example' => ['title' => 'Typing three bills a day',
      'lines' => [['Lines on a distributor bill', (string) $te['lines']], ['Seconds to type one line', (string) $te['secs']], ['Bills a day', (string) $te['bills']], ['Time every day', $te_day_min . ' minutes', '='], ['Days in a month', (string) $te['days'], '×']],
      'result' => ['Typing in a month', 'About ' . $te_month_hrs . ' hours'],
      'note' => 'Change the numbers to match your deliveries. That time doesn’t count fixing the mistakes later.'],
  ],

  'steps' => ['eyebrow' => 'How a purchase bill works', 'title' => 'From delivery to stock on the shelf',
    'intro' => 'Set up each supplier once. After that, every bill is import, check, save.',
    'items' => [
      ['Add the supplier once', 'Add the distributor in the account master with their details. Their ledger starts from here.', 'One time per supplier'],
      ['Bring the bill in', 'Import the distributor’s CSV and map their columns once, or scan the printed bill with OCR. You can still type a bill by hand.', 'CSV · OCR · manual'],
      ['Check the lines', 'Batch, expiry, MRP, PTR, GST, scheme and free quantity are on each line. Fix anything that looks off before saving.', 'Margin shown on save'],
      ['Save: stock and ledger update', 'Stock goes live straight away, by batch, and the bill posts to the supplier’s ledger. Returns go back with a credit note.', 'Stock live instantly'],
    ]],

  'caps' => ['eyebrow' => 'Purchase and supplier tools', 'title' => 'One place for bills, returns and dues',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-upload', 'CSV import', 'Map the distributor’s columns once, then import their bills.', 'feat_csv_import'],
      ['i-scan', 'OCR import', 'Scan a printed bill into a purchase entry. Uses OCR credits; included credits vary by plan.', null, 'Uses OCR credits'],
      ['i-rupee', 'Scheme, free quantity and margin', 'Free quantity recorded on the line; PTR and margin worked out on save.', null, 'Every plan'],
      ['i-return', 'Purchase returns and credit notes', 'Send stock back and keep the credit on the supplier’s ledger.', 'feat_purchase_credit_note'],
      ['i-register', 'Supplier ledger and dues', 'Every bill, return and payment per supplier, with a supplier due report.', 'feat_supplier_due_report'],
      ['i-receipt', 'Supplier vouchers', 'Record payments to suppliers against their dues.', 'feat_supplier_voucher'],
      ['i-file', 'Bill attachment and drafts', 'Attach the original bill, or save a draft and finish it later.', 'feat_bill_attachment'],
      ['i-lock', 'Lock purchase invoices', 'Saved purchase bills can’t be quietly edited.', 'feat_purchase_lock_invoices'],
      ['i-users', 'Supplier rate contracts', 'Record the rates you’ve agreed with each supplier.', 'feat_supplier_rate_contract'],
    ]],

  'guide' => ['eyebrow' => 'On every bill', 'title' => 'Six things to check before you save a distributor bill',
    'intro' => 'A short habit that catches most purchase mistakes. It takes a minute once the lines are imported.',
    'items' => [
      ['Batch and expiry match the box', 'Check a few lines against the cartons in front of you, especially short-expiry stock.'],
      ['Free quantity is on the line', 'If the bill says 10 + 1, the free strip should be in stock, not missing or turned into a discount.'],
      ['Rate is what you agreed', 'Compare PTR with the last bill or your rate contract. A margin that looks low is your warning sign.'],
      ['MRP is per strip or pack, the way you sell', 'A pack MRP entered as a strip MRP will show up at the counter as a wrong price.'],
      ['GST rate matches the item master', 'If the bill and your item master disagree, fix the item before saving, so sales are taxed right too.'],
      ['Short-expiry stock is flagged', 'If the distributor sent stock close to expiry, decide now: accept, or return it on the next visit.'],
    ],
    'note' => 'The software calculates margin and records each field. Checking them against the carton is still your call.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring a recent distributor bill, or the CSV file your distributor sends, to a 30-minute demo.',
    'items' => [
      'Import a distributor CSV and map its columns',
      'Scan a printed bill with OCR import',
      'Enter a 10 + 1 scheme and see the free quantity in stock',
      'See PTR and margin when the bill is saved',
      'Make a purchase return with a credit note',
      'Open the supplier ledger and the supplier due report',
    ]],

  'related' => ['billing', 'expiry', 'inventory'],

  'faq' => [
    ['Do I have to type every distributor bill?', 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). You can still type a bill by hand when needed.', true],
    ['Do I map the CSV columns every time?', 'No. Map the distributor’s columns once, then import their bills.', true],
    ['Does it record scheme and free quantity?', 'Yes. Scheme and free quantity are recorded on the purchase line, and PTR and margin are worked out when you save.', true],
    ['Does it show when a rate is different from last time?', 'Yes, on the purchase screen. A Diff % column compares each line’s margin with the last purchase (or the purchase average, your choice), so a line where you now earn less stands out before you save. It isn’t a pop-up alert.' . ($rc ? ' On ' . e($rc) . ' you can also record agreed rates per supplier with rate contracts.' : ''), true],
    ['How do I track what I owe each supplier?', 'Each supplier has a ledger with bills, returns, payments and dues, plus a supplier due report.', true],
    ['How much does OCR import cost?', 'OCR import uses OCR credits. Each plan includes some, and you can buy more as an add-on. See <a href="' . url('pricing') . '">pricing</a>.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
