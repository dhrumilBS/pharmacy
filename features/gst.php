<?php
/* Feature page: GST & accounts ("Run the business"). Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (accounts and reporting). Don't claim automatic GST-rate updates, filing,
   or guaranteed compliance; keep the tone calm. Plans come live from the pricing API ('feat_*'). */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('GST billing software for medical stores: GST reports, ledgers and Tally export');
$description = 'HSN and GST rate on every bill from the item master, GST report and bill-wise tax report for your CA, account, patient and supplier ledgers, vouchers, expenses and Tally XML export on Enterprise.';
$page_key    = 'gst';

$tally = feature_plan_label('feat_tally_export');
$exp   = feature_plan_label('feat_expense_mgmt');
/* Worked example (illustrative): time spent fixing entries before the CA gets the data */
$fx = ['entries' => 60, 'mins' => 3, 'months' => 12];
$fx_hrs = (int) round($fx['entries'] * $fx['mins'] * $fx['months'] / 60);

$fp = [
  'hero' => ['crumb' => 'GST & accounts', 'parent' => 'Features', 'eyebrow' => 'GST & accounts',
    'h1' => 'Month-end without a weekend of fixing entries',
    'lead' => 'HSN code and GST rate come from the item master on every bill. The GST report and bill-wise tax report are ready for your accountant, and ledgers, vouchers and expenses sit next to the bills they belong to.'],
  'ctas'   => ['demo' => 'See the GST report in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['HSN and GST rate on every line', 'GST report and bill-wise tax report', $tally ? 'Tally XML export (' . $tally . ')' : null])),
  'screen' => ['v2-06-gst', 'GST report for September with an HSN-wise summary'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Where the books drift from the bills',
    'intro' => 'Nobody plans to have messy accounts. It happens one small entry at a time.',
    'items' => [
      ['Last week of the month', 'The CA asks for “the GST data”', 'You export from three places and send a spreadsheet that doesn’t quite match the bills.', 'your weekend, and the CA’s time spent fixing it.'],
      ['After a rate change', 'Some items still carry the old rate', 'The item master wasn’t updated for a few items, so their bills keep the old GST rate.', 'tax worked out wrong on every bill for those items.'],
      ['Cash count', 'Expenses paid from the drawer', 'Tea, courier, an electrician: paid from the counter cash and never written down.', 'a drawer that never matches, and profit that looks higher than it is.'],
      ['Supplier statement', 'Your ledger and theirs disagree', 'The distributor’s statement is off by a credit note nobody entered.', 'credit you never claim, or a payment made twice.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where accounts leak time and money, and what plugs it',
    'intro' => 'Each leak, then what the software does about it and the plan that includes it.',
    'rows' => [
      ['Wrong tax on a bill', 'Rates are typed at the counter, or remembered.', '<b>HSN code and GST rate</b> applied on every line from the item master.', null],
      ['Month-end export chaos', 'Data comes from several places and never agrees.', '<b>GST report</b> and <b>bill and date-wise tax report</b>, exported to Excel for your CA.', 'feat_gst_report'],
      ['Retyping into Tally', 'Someone keys the month’s sales and purchases in again.', '<b>Tally XML export</b> of sales, purchases, returns and payments.', 'feat_tally_export'],
      ['Drawer expenses unrecorded', 'Small cash expenses vanish from the books.', '<b>Expense management</b>, so cash paid out is on record.', 'feat_expense_mgmt'],
      ['Payments without a record', 'Money paid to suppliers or received from patients is noted on paper.', '<b>Supplier and patient vouchers</b> posted to their ledgers.', 'feat_supplier_voucher'],
      ['Credit notes that go missing', 'Returns are made, but the credit isn’t adjusted.', '<b>Purchase credit notes</b> on the supplier’s ledger.', 'feat_purchase_credit_note'],
      ['Bills edited after the month closes', 'An old invoice changes and the reports no longer match what was filed.', '<b>Lock invoices</b> so saved bills can’t be quietly changed.', 'feat_sale_lock_invoices'],
    ],
    'example' => ['title' => 'Fixing entries by hand',
      'lines' => [['Entries fixed before the CA gets them', $fx['entries'] . ' a month'], ['Minutes to fix each', (string) $fx['mins']], ['Months in a year', (string) $fx['months']]],
      'result' => ['Time in a year', 'About ' . $fx_hrs . ' hours'],
      'note' => 'Change the numbers to match your store. That doesn’t count what your CA charges for the same work.'],
  ],

  'steps' => ['eyebrow' => 'How the accounts work', 'title' => 'Get the item master right, and the tax follows',
    'intro' => 'GST on a bill is only as right as the item it comes from, so the work starts there.',
    'items' => [
      ['Set HSN and GST per item', 'Fill in the HSN code and GST rate in the item master, or bring them in with your CSV import.', 'Once per item'],
      ['Bill as usual', 'Every line takes its HSN and GST rate from the item. Purchases record input GST the same way.', 'Tax on every line'],
      ['Record money as it moves', 'Supplier and patient vouchers, credit notes and expenses go to the right ledger on the day.', 'Ledgers stay current'],
      ['Month-end: hand it over', 'Export the GST report and bill-wise tax report to Excel for your CA' . ($tally ? ', or export to Tally XML on ' . e($tally) : '') . '.', 'Excel or Tally XML'],
    ]],

  'caps' => ['eyebrow' => 'Accounts tools', 'title' => 'Clean data your accountant can use',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-receipt', 'HSN and GST on every line', 'Applied from the item master on sales and purchases.', null],
      ['i-rupee', 'GST report', 'Monthly GST figures with an HSN-wise summary, ready to export.', 'feat_gst_report'],
      ['i-file', 'Bill and date-wise tax report', 'Tax bill by bill, for checking against what was filed.', null],
      ['i-upload', 'Tally XML export', 'Sales, purchases, returns and payments into Tally without retyping.', 'feat_tally_export'],
      ['i-register', 'Ledgers', 'Account, patient and supplier ledgers that update as you bill and pay.', null],
      ['i-users', 'Patient and supplier vouchers', 'Record money received and paid against the right ledger.', 'feat_supplier_voucher'],
      ['i-box', 'Expense management', 'Cash paid out for the store, on record.', 'feat_expense_mgmt'],
      ['i-return', 'Purchase credit notes', 'Returns and credits kept on the supplier’s ledger.', 'feat_purchase_credit_note'],
      ['i-lock', 'Lock invoices', 'Closed months stay as they were filed.', 'feat_sale_lock_invoices'],
    ]],

  'guide' => ['eyebrow' => 'Before you send the month', 'title' => 'A month-end checklist for your accountant',
    'intro' => 'A short routine that keeps the numbers you send in line with the bills you made.',
    'items' => [
      ['Check HSN and GST on new items', 'Items added this month are where wrong rates usually hide. Look at them first.'],
      ['Update the item master when a rate changes', 'Bills use the rate saved on the item. Change it there the day a new rate applies, and new bills follow.'],
      ['Enter every credit note', 'Match returns with the distributor’s statement so their ledger agrees with yours.'],
      ['Record drawer expenses daily', 'A small habit at closing saves a lot of guessing at month-end.'],
      ['Send the GST report and the bill-wise tax report together', 'Same month, same export, so your CA can check one against the other.'],
      ['Lock the month once it’s filed', 'Locking invoices keeps last month’s bills as they were filed.'],
    ],
    'note' => 'General good practice, not tax advice. Check filing requirements with your CA.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring this list, or bring your CA. We’ll go through each one with sample data.',
    'items' => [
      'Open the GST report for a month with the HSN-wise summary',
      'Open the bill and date-wise tax report',
      'Export both to Excel',
      'Record a shop expense and a supplier payment voucher',
      'Find a credit note on a supplier’s ledger',
      'See what a Tally XML export looks like' . ($tally ? ' (' . $tally . ')' : ''),
    ]],

  'related' => ['reports', 'retention', 'security'],
  'related_title' => 'Also in the back office',

  'faq' => [
    ['Does it handle GST correctly?', 'HSN codes and GST rates are applied on every bill from the item master, and the GST report and bill-wise tax report are ready for your accountant. Keep rates in the item master up to date.', true],
    ['What happens when a GST rate changes?', 'Bills use the rate saved on each item, so update the item master when a new rate applies and new bills follow it.', true],
    ['Can I export to Tally?', 'Tally XML export is part of ' . e($tally ?: 'the Enterprise plan') . '. On other plans, reports export to Excel for your CA.', true],
    ['Can I record shop expenses?', 'Yes. Expense management records cash paid out for the store' . ($exp ? ', on ' . e($exp) : '') . '.', true],
    ['What can I give my accountant?', 'The GST report, the bill and date-wise tax report, and account, patient and supplier ledgers, exported to Excel or CSV.', true],
    ['Does it file GST returns?', 'The software prepares the GST report and tax reports. Filing is done by you or your CA using them.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
