<?php
/**
 * Import data: the "Purchase & suppliers" feature page (/features/purchase/).
 *
 * PHP_Site source: features/purchase.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The free plan's purchase bill limit (pricing_split() / plan_bill_limits())
 * is [rx_free_plan] inside [rx_if], the rate contract plan ($rc) is
 * [rx_plan], and url() links are [rx_link]. The worked example keeps today's
 * illustrative numbers as text (40 lines × 30 s × 3 bills = 60 minutes a
 * day; × 30 days = about 30 hours), including its explicit = and ×
 * operators.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/purchase.php',
	'key'      => 'purchase',
	'title'    => 'Purchase & suppliers',
	'slug'     => 'purchase',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy purchase software: CSV and OCR bill import %%sep%% %%sitename%%',
		'desc'    => 'Stop typing distributor bills line by line: import by CSV or OCR, record scheme and free quantity, see PTR and margin on save, keep supplier dues.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Purchase & suppliers',
			'parent'  => 'Features',
			'eyebrow' => 'Purchase & suppliers',
			'h1'      => 'Stop typing distributor bills line by line after closing',
			'lead'    => 'Import the bill as a CSV file or scan the printed page with OCR. Batch, expiry, MRP, PTR and GST come in once, scheme and free quantity are recorded, margin is worked out on save, and the supplier’s ledger updates by itself.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'Import a bill in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'CSV import with column mapping' ),
			array( 'text' => 'OCR scan of printed bills (uses credits)' ),
			array( 'text' => '[rx_if cond="free_purchase_limit"][rx_free_plan field="name"] plan: [rx_free_plan field="purchase"] purchase bills a month, free[/rx_if]' ),
		),
		'screen'          => 'v2-04-purchase-import',
		'screen_alt'      => 'Distributor bill imported from a CSV file',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Most of your margin is decided on the purchase bill',
			'intro'   => 'Purchase rate, scheme and returns decide what a medical store keeps. They’re also where the tiredest typing happens.',
		),
		'scenes'          => array(
			array(
				'time'  => '11 pm, after closing',
				'title' => 'Three bills still to enter',
				'text'  => 'A 40-line distributor bill, typed line by line: batch, expiry, MRP, rate, GST. One wrong digit and stock is wrong for months.',
				'cost'  => 'your evening, and errors that surface later.',
			),
			array(
				'time'  => 'Delivery day',
				'title' => 'The free quantity goes missing',
				'text'  => 'The bill says 10 + 1 free. The free strip isn’t entered, so cost per strip and margin are both wrong.',
				'cost'  => 'margin you earned but can’t see.',
			),
			array(
				'time'  => 'Checking the rate',
				'title' => 'Was that the rate you agreed?',
				'text'  => 'The distributor rep promised a rate on the phone. Nobody compares it with the bill.',
				'cost'  => 'a few rupees a strip, every bill, without anyone noticing.',
			),
			array(
				'time'  => 'Payment day',
				'title' => 'What do we actually owe?',
				'text'  => 'Bills, returns and part payments are in three places. You pay what the rep says.',
				'cost'  => 'overpaying, or a strained relationship with a good supplier.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where purchase money leaks, and what plugs it',
			'intro'   => 'Each leak, then what plugs it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Typing errors on every bill',
				'how'       => 'Batch, expiry and rate retyped by hand, late at night.',
				'plug_html' => '<b>CSV import</b> with column mapping, or <b>OCR import</b> from a scanned bill (uses OCR credits).',
				'feat'      => 'feat_csv_import',
				'plan'      => '',
			),
			array(
				'leak'      => 'Free quantity not recorded',
				'how'       => 'Schemes like 10 + 1 are skipped or entered as a discount.',
				'plug_html' => '<b>Scheme and free quantity</b> are recorded on the line, so cost and stock are right.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'Margin you can’t see',
				'how'       => 'Nobody works out margin per item at entry.',
				'plug_html' => '<b>PTR and margin are calculated on save</b>, plus a <b>margin report</b> to review later.',
				'feat'      => 'feat_margin_report',
				'plan'      => '',
			),
			// The PHP page drops the rate contract sentence when the API doesn't
			// list feat_supplier_rate_contract; here only its plan label is live.
			array(
				'leak'      => 'Rates drifting from what was agreed',
				'how'       => 'Promised rates live in a phone call.',
				'plug_html' => 'A <b>Diff % column</b> compares each line’s margin with the last purchase or the purchase average, so a rate that went up stands out before you save. <b>Supplier rate contracts</b> record agreed rates[rx_plan feat="feat_supplier_rate_contract" before=" (" after=")"].',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Returns that never become credit',
				'how'       => 'Stock goes back, but the credit is never adjusted.',
				'plug_html' => '<b>Purchase returns with credit notes</b>, kept on the supplier’s ledger.',
				'feat'      => 'feat_purchase_credit_note',
				'plan'      => '',
			),
			array(
				'leak'      => 'Paying the wrong amount',
				'how'       => 'Dues are tracked from memory and the rep’s statement.',
				'plug_html' => '<b>Supplier ledger and dues</b> with a <b>supplier due report</b>, and supplier vouchers for payments.',
				'feat'      => 'feat_supplier_due_report',
				'plan'      => '',
			),
			array(
				'leak'      => 'Bills changed or lost later',
				'how'       => 'Paper bills go missing; saved entries get edited.',
				'plug_html' => '<b>Bill attachment</b> keeps the original, and <b>lock invoices</b> stops quiet changes.',
				'feat'      => 'feat_bill_attachment',
				'plan'      => '',
			),
		),
		'leaks_example'   => array(
			'title'        => 'Typing three bills a day',
			'lines'        => array(
				array(
					'label' => 'Lines on a distributor bill',
					'value' => '40',
					'op'    => 'auto',
				),
				array(
					'label' => 'Seconds to type one line',
					'value' => '30',
					'op'    => 'auto',
				),
				array(
					'label' => 'Bills a day',
					'value' => '3',
					'op'    => 'auto',
				),
				array(
					'label' => 'Time every day',
					'value' => '60 minutes',
					'op'    => '=',
				),
				array(
					'label' => 'Days in a month',
					'value' => '30',
					'op'    => '×',
				),
			),
			'result_label' => 'Typing in a month',
			'result_value' => 'About 30 hours',
			'note'         => 'Change the numbers to match your deliveries. That time doesn’t count fixing the mistakes later.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How a purchase bill works',
			'title'   => 'From delivery to stock on the shelf',
			'intro'   => 'Set up each supplier once. After that, every bill is import, check, save.',
		),
		'steps'           => array(
			array(
				'title' => 'Add the supplier once',
				'html'  => 'Add the distributor in the account master with their details. Their ledger starts from here.',
				'meta'  => 'One time per supplier',
			),
			array(
				'title' => 'Bring the bill in',
				'html'  => 'Import the distributor’s CSV and map their columns once, or scan the printed bill with OCR. You can still type a bill by hand.',
				'meta'  => 'CSV · OCR · manual',
			),
			array(
				'title' => 'Check the lines',
				'html'  => 'Batch, expiry, MRP, PTR, GST, scheme and free quantity are on each line. Fix anything that looks off before saving.',
				'meta'  => 'Margin shown on save',
			),
			array(
				'title' => 'Save: stock and ledger update',
				'html'  => 'Stock goes live straight away, by batch, and the bill posts to the supplier’s ledger. Returns go back with a credit note.',
				'meta'  => 'Stock live instantly',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'Purchase and supplier tools',
			'title'   => 'One place for bills, returns and dues',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-upload',
				'title' => 'CSV import',
				'html'  => 'Map the distributor’s columns once, then import their bills.',
				'feat'  => 'feat_csv_import',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-scan',
				'title' => 'OCR import',
				'html'  => 'Scan a printed bill into a purchase entry. Uses OCR credits; included credits vary by plan.',
				'feat'  => '',
				'plan'  => 'Uses OCR credits',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'Scheme, free quantity and margin',
				'html'  => 'Free quantity recorded on the line; PTR and margin worked out on save.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-return',
				'title' => 'Purchase returns and credit notes',
				'html'  => 'Send stock back and keep the credit on the supplier’s ledger.',
				'feat'  => 'feat_purchase_credit_note',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-register',
				'title' => 'Supplier ledger and dues',
				'html'  => 'Every bill, return and payment per supplier, with a supplier due report.',
				'feat'  => 'feat_supplier_due_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-receipt',
				'title' => 'Supplier vouchers',
				'html'  => 'Record payments to suppliers against their dues.',
				'feat'  => 'feat_supplier_voucher',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Bill attachment and drafts',
				'html'  => 'Attach the original bill, or save a draft and finish it later.',
				'feat'  => 'feat_bill_attachment',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-lock',
				'title' => 'Lock purchase invoices',
				'html'  => 'Saved purchase bills can’t be quietly edited.',
				'feat'  => 'feat_purchase_lock_invoices',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-users',
				'title' => 'Supplier rate contracts',
				'html'  => 'Record the rates you’ve agreed with each supplier.',
				'feat'  => 'feat_supplier_rate_contract',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'On every bill',
			'title'   => 'Six things to check before you save a distributor bill',
			'intro'   => 'A short habit that catches most purchase mistakes. It takes a minute once the lines are imported.',
		),
		'guide'           => array(
			array(
				'title' => 'Batch and expiry match the box',
				'html'  => 'Check a few lines against the cartons in front of you, especially short-expiry stock.',
			),
			array(
				'title' => 'Free quantity is on the line',
				'html'  => 'If the bill says 10 + 1, the free strip should be in stock, not missing or turned into a discount.',
			),
			array(
				'title' => 'Rate is what you agreed',
				'html'  => 'Compare PTR with the last bill or your rate contract. A margin that looks low is your warning sign.',
			),
			array(
				'title' => 'MRP is per strip or pack, the way you sell',
				'html'  => 'A pack MRP entered as a strip MRP will show up at the counter as a wrong price.',
			),
			array(
				'title' => 'GST rate matches the item master',
				'html'  => 'If the bill and your item master disagree, fix the item before saving, so sales are taxed right too.',
			),
			array(
				'title' => 'Short-expiry stock is flagged',
				'html'  => 'If the distributor sent stock close to expiry, decide now: accept, or return it on the next visit.',
			),
		),
		'guide_note'      => 'The software calculates margin and records each field. Checking them against the carton is still your call.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring a recent distributor bill, or the CSV file your distributor sends, to a 30-minute demo.',
		),
		'proof'           => array(
			array( 'text' => 'Import a distributor CSV and map its columns' ),
			array( 'text' => 'Scan a printed bill with OCR import' ),
			array( 'text' => 'Enter a 10 + 1 scheme and see the free quantity in stock' ),
			array( 'text' => 'See PTR and margin when the bill is saved' ),
			array( 'text' => 'Make a purchase return with a credit note' ),
			array( 'text' => 'Open the supplier ledger and the supplier due report' ),
		),

		'related'         => array( 'billing', 'expiry', 'inventory' ),
		'related_title'   => '',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Do I have to type every distributor bill?',
				'a_html'    => 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). You can still type a bill by hand when needed.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Do I map the CSV columns every time?',
				'a_html'    => 'No. Map the distributor’s columns once, then import their bills.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it record scheme and free quantity?',
				'a_html'    => 'Yes. Scheme and free quantity are recorded on the purchase line, and PTR and margin are worked out when you save.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it show when a rate is different from last time?',
				'a_html'    => 'Yes, on the purchase screen. A Diff % column compares each line’s margin with the last purchase (or the purchase average, your choice), so a line where you now earn less stands out before you save. It isn’t a pop-up alert.[rx_plan feat="feat_supplier_rate_contract" before=" On " after=" you can also record agreed rates per supplier with rate contracts."]',
				'confirmed' => true,
			),
			array(
				'q'         => 'How do I track what I owe each supplier?',
				'a_html'    => 'Each supplier has a ledger with bills, returns, payments and dues, plus a supplier due report.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How much does OCR import cost?',
				'a_html'    => 'OCR import uses OCR credits. Each plan includes some, and you can buy more as an add-on. See [rx_link key="pricing"]pricing[/rx_link].',
				'confirmed' => true,
			),
		),
	),
);
