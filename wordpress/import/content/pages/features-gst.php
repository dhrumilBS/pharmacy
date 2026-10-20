<?php
/**
 * Import data: the "GST & accounts" feature page (/features/gst/).
 *
 * PHP_Site source: features/gst.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The Tally export plan ($tally), the expense plan ($exp) and the rows'
 * plans are [rx_plan] / `feat`; "the Enterprise plan" stays the fallback text
 * of the Tally answer, as on the PHP page. The worked example keeps today's
 * illustrative numbers as text (60 × 3 minutes × 12 months = about 36 hours).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/gst.php',
	'key'      => 'gst',
	'title'    => 'GST & accounts',
	'slug'     => 'gst',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'GST billing software for medical stores %%sep%% %%sitename%%',
		'desc'    => 'HSN and GST rate on every bill, GST and bill-wise tax reports for your CA, ledgers and vouchers, with Tally XML export on Enterprise.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'GST & accounts',
			'parent'  => 'Features',
			'eyebrow' => 'GST & accounts',
			'h1'      => 'Month-end without a weekend of fixing entries',
			'lead'    => 'HSN code and GST rate come from the item master on every bill. The GST report and bill-wise tax report are ready for your accountant, and ledgers, vouchers and expenses sit next to the bills they belong to.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the GST report in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'HSN and GST rate on every line' ),
			array( 'text' => 'GST report and bill-wise tax report' ),
			array( 'text' => '[rx_plan feat="feat_tally_export" before="Tally XML export (" after=")"]' ),
		),
		'screen'          => 'v2-06-gst',
		'screen_alt'      => 'GST report for September with an HSN-wise summary',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Where the books drift from the bills',
			'intro'   => 'Nobody plans to have messy accounts. It happens one small entry at a time.',
		),
		'scenes'          => array(
			array(
				'time'  => 'Last week of the month',
				'title' => 'The CA asks for “the GST data”',
				'text'  => 'You export from three places and send a spreadsheet that doesn’t quite match the bills.',
				'cost'  => 'your weekend, and the CA’s time spent fixing it.',
			),
			array(
				'time'  => 'After a rate change',
				'title' => 'Some items still carry the old rate',
				'text'  => 'The item master wasn’t updated for a few items, so their bills keep the old GST rate.',
				'cost'  => 'tax worked out wrong on every bill for those items.',
			),
			array(
				'time'  => 'Cash count',
				'title' => 'Expenses paid from the drawer',
				'text'  => 'Tea, courier, an electrician: paid from the counter cash and never written down.',
				'cost'  => 'a drawer that never matches, and profit that looks higher than it is.',
			),
			array(
				'time'  => 'Supplier statement',
				'title' => 'Your ledger and theirs disagree',
				'text'  => 'The distributor’s statement is off by a credit note nobody entered.',
				'cost'  => 'credit you never claim, or a payment made twice.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where accounts leak time and money, and what plugs it',
			'intro'   => 'Each leak, then what the software does about it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Wrong tax on a bill',
				'how'       => 'Rates are typed at the counter, or remembered.',
				'plug_html' => '<b>HSN code and GST rate</b> applied on every line from the item master.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Month-end export chaos',
				'how'       => 'Data comes from several places and never agrees.',
				'plug_html' => '<b>GST report</b> and <b>bill and date-wise tax report</b>, exported to Excel for your CA.',
				'feat'      => 'feat_gst_report',
				'plan'      => '',
			),
			array(
				'leak'      => 'Retyping into Tally',
				'how'       => 'Someone keys the month’s sales and purchases in again.',
				'plug_html' => '<b>Tally XML export</b> of sales, purchases, returns and payments.',
				'feat'      => 'feat_tally_export',
				'plan'      => '',
			),
			array(
				'leak'      => 'Drawer expenses unrecorded',
				'how'       => 'Small cash expenses vanish from the books.',
				'plug_html' => '<b>Expense management</b>, so cash paid out is on record.',
				'feat'      => 'feat_expense_mgmt',
				'plan'      => '',
			),
			array(
				'leak'      => 'Payments without a record',
				'how'       => 'Money paid to suppliers or received from patients is noted on paper.',
				'plug_html' => '<b>Supplier and patient vouchers</b> posted to their ledgers.',
				'feat'      => 'feat_supplier_voucher',
				'plan'      => '',
			),
			array(
				'leak'      => 'Credit notes that go missing',
				'how'       => 'Returns are made, but the credit isn’t adjusted.',
				'plug_html' => '<b>Purchase credit notes</b> on the supplier’s ledger.',
				'feat'      => 'feat_purchase_credit_note',
				'plan'      => '',
			),
			array(
				'leak'      => 'Bills edited after the month closes',
				'how'       => 'An old invoice changes and the reports no longer match what was filed.',
				'plug_html' => '<b>Lock invoices</b> so saved bills can’t be quietly changed.',
				'feat'      => 'feat_sale_lock_invoices',
				'plan'      => '',
			),
		),
		'leaks_example'   => array(
			'title'        => 'Fixing entries by hand',
			'lines'        => array(
				array(
					'label' => 'Entries fixed before the CA gets them',
					'value' => '60 a month',
					'op'    => 'auto',
				),
				array(
					'label' => 'Minutes to fix each',
					'value' => '3',
					'op'    => 'auto',
				),
				array(
					'label' => 'Months in a year',
					'value' => '12',
					'op'    => 'auto',
				),
			),
			'result_label' => 'Time in a year',
			'result_value' => 'About 36 hours',
			'note'         => 'Change the numbers to match your store. That doesn’t count what your CA charges for the same work.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How the accounts work',
			'title'   => 'Get the item master right, and the tax follows',
			'intro'   => 'GST on a bill is only as right as the item it comes from, so the work starts there.',
		),
		'steps'           => array(
			array(
				'title' => 'Set HSN and GST per item',
				'html'  => 'Fill in the HSN code and GST rate in the item master, or bring them in with your CSV import.',
				'meta'  => 'Once per item',
			),
			array(
				'title' => 'Bill as usual',
				'html'  => 'Every line takes its HSN and GST rate from the item. Purchases record input GST the same way.',
				'meta'  => 'Tax on every line',
			),
			array(
				'title' => 'Record money as it moves',
				'html'  => 'Supplier and patient vouchers, credit notes and expenses go to the right ledger on the day.',
				'meta'  => 'Ledgers stay current',
			),
			array(
				'title' => 'Month-end: hand it over',
				'html'  => 'Export the GST report and bill-wise tax report to Excel for your CA[rx_plan feat="feat_tally_export" before=", or export to Tally XML on "].',
				'meta'  => 'Excel or Tally XML',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'Accounts tools',
			'title'   => 'Clean data your accountant can use',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-receipt',
				'title' => 'HSN and GST on every line',
				'html'  => 'Applied from the item master on sales and purchases.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'GST report',
				'html'  => 'Monthly GST figures with an HSN-wise summary, ready to export.',
				'feat'  => 'feat_gst_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Bill and date-wise tax report',
				'html'  => 'Tax bill by bill, for checking against what was filed.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-upload',
				'title' => 'Tally XML export',
				'html'  => 'Sales, purchases, returns and payments into Tally without retyping.',
				'feat'  => 'feat_tally_export',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-register',
				'title' => 'Ledgers',
				'html'  => 'Account, patient and supplier ledgers that update as you bill and pay.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-users',
				'title' => 'Patient and supplier vouchers',
				'html'  => 'Record money received and paid against the right ledger.',
				'feat'  => 'feat_supplier_voucher',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-box',
				'title' => 'Expense management',
				'html'  => 'Cash paid out for the store, on record.',
				'feat'  => 'feat_expense_mgmt',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-return',
				'title' => 'Purchase credit notes',
				'html'  => 'Returns and credits kept on the supplier’s ledger.',
				'feat'  => 'feat_purchase_credit_note',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-lock',
				'title' => 'Lock invoices',
				'html'  => 'Closed months stay as they were filed.',
				'feat'  => 'feat_sale_lock_invoices',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'Before you send the month',
			'title'   => 'A month-end checklist for your accountant',
			'intro'   => 'A short routine that keeps the numbers you send in line with the bills you made.',
		),
		'guide'           => array(
			array(
				'title' => 'Check HSN and GST on new items',
				'html'  => 'Items added this month are where wrong rates usually hide. Look at them first.',
			),
			array(
				'title' => 'Update the item master when a rate changes',
				'html'  => 'Bills use the rate saved on the item. Change it there the day a new rate applies, and new bills follow.',
			),
			array(
				'title' => 'Enter every credit note',
				'html'  => 'Match returns with the distributor’s statement so their ledger agrees with yours.',
			),
			array(
				'title' => 'Record drawer expenses daily',
				'html'  => 'A small habit at closing saves a lot of guessing at month-end.',
			),
			array(
				'title' => 'Send the GST report and the bill-wise tax report together',
				'html'  => 'Same month, same export, so your CA can check one against the other.',
			),
			array(
				'title' => 'Lock the month once it’s filed',
				'html'  => 'Locking invoices keeps last month’s bills as they were filed.',
			),
		),
		'guide_note'      => 'General good practice, not tax advice. Check filing requirements with your CA.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring this list, or bring your CA. We’ll go through each one with sample data.',
		),
		'proof'           => array(
			array( 'text' => 'Open the GST report for a month with the HSN-wise summary' ),
			array( 'text' => 'Open the bill and date-wise tax report' ),
			array( 'text' => 'Export both to Excel' ),
			array( 'text' => 'Record a shop expense and a supplier payment voucher' ),
			array( 'text' => 'Find a credit note on a supplier’s ledger' ),
			array( 'text' => 'See what a Tally XML export looks like[rx_plan feat="feat_tally_export" before=" (" after=")"]' ),
		),

		'related'         => array( 'reports', 'retention', 'security' ),
		'related_title'   => 'Also in the back office',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Does it handle GST correctly?',
				'a_html'    => 'HSN codes and GST rates are applied on every bill from the item master, and the GST report and bill-wise tax report are ready for your accountant. Keep rates in the item master up to date.',
				'confirmed' => true,
			),
			array(
				'q'         => 'What happens when a GST rate changes?',
				'a_html'    => 'Bills use the rate saved on each item, so update the item master when a new rate applies and new bills follow it.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I export to Tally?',
				'a_html'    => 'Tally XML export is part of [rx_plan feat="feat_tally_export" fallback="the Enterprise plan"]. On other plans, reports export to Excel for your CA.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I record shop expenses?',
				'a_html'    => 'Yes. Expense management records cash paid out for the store[rx_plan feat="feat_expense_mgmt" before=", on "].',
				'confirmed' => true,
			),
			array(
				'q'         => 'What can I give my accountant?',
				'a_html'    => 'The GST report, the bill and date-wise tax report, and account, patient and supplier ledgers, exported to Excel or CSV.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it file GST returns?',
				'a_html'    => 'The software prepares the GST report and tax reports. Filing is done by you or your CA using them.',
				'confirmed' => true,
			),
		),
	),
);
