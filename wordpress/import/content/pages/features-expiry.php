<?php
/**
 * Import data: the "Expiry & batch" feature page (/features/expiry/).
 *
 * PHP_Site source: features/expiry.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The leak register has no worked example (empty title = the demo card) and
 * embeds the expiry loss estimator (leaks_estimator). Plan labels from
 * feature_plan_label() are the rows' `feat` or [rx_plan]; url() links are
 * [rx_link].
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/expiry.php',
	'key'      => 'expiry',
	'title'    => 'Expiry & batch',
	'slug'     => 'expiry',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy expiry management software: batch tracking and expiry reports %%sep%% %%sitename%%',
		'desc'    => 'Track every medicine by batch and expiry: stock expiry report item-wise or supplier-wise, first-expiry-first-out billing, purchase returns with credit notes, and a setting to stop expired sales.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Expiry & batch',
			'parent'  => 'Features',
			'eyebrow' => 'Expiry & batch tracking',
			'h1'      => 'Catch expiry while you can still sell it or send it back',
			'lead'    => 'Every batch carries its expiry from the moment the purchase bill is saved. See what expires in the date range you choose, bill the oldest batch first, and return short-expiry stock to the distributor on record.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the expiry report in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'Expiry report item-wise or supplier-wise' ),
			array( 'text' => 'Oldest batch billed first (FEFO)' ),
			array( 'text' => 'Setting to stop expired sales' ),
		),
		'screen'          => 'v2-03-expiry',
		'screen_alt'      => 'Stock expiry report, supplier-wise, next 60 days',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Expiry is the leak you only see too late',
			'intro'   => 'Money you already paid to the distributor, sitting on the shelf until it can’t be sold.',
		),
		'scenes'          => array(
			array(
				'time'  => 'Found during a shelf check',
				'title' => 'Strips behind the new stock',
				'text'  => 'A full box turns up behind newer batches. It expired two months ago, and it was paid for in full.',
				'cost'  => 'the purchase price of every strip, written off.',
			),
			array(
				'time'  => 'The distributor visit',
				'title' => 'The return window has closed',
				'text'  => 'You find near-expiry stock the week after the distributor’s return terms ran out. It stays on your books.',
				'cost'  => 'stock that could have gone back for credit.',
			),
			array(
				'time'  => 'A patient comes back',
				'title' => 'An expired strip was billed',
				'text'  => 'A busy staff member picked a strip without checking the date. The patient noticed before you did.',
				'cost'  => 'trust at your counter, and a conversation you didn’t want.',
			),
			array(
				'time'  => 'Ordering day',
				'title' => 'Reordering what’s already there',
				'text'  => 'An item is reordered while an older batch of the same item hasn’t moved in months.',
				'cost'  => 'more money stuck in stock that may expire too.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where expiry eats your margin, and what plugs it',
			'intro'   => 'Each leak, then what plugs it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Older batches left behind',
				'how'       => 'New stock goes on the front of the shelf and sells first.',
				'plug_html' => '<b>FEFO billing:</b> the batch that expires first fills in on every bill, so older stock goes out first.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'Expiry you don’t see coming',
				'how'       => 'Nobody checks dates until the shelf check.',
				'plug_html' => '<b>Stock expiry report</b> for any date range, item-wise or supplier-wise, so you can act 30, 60 or 90 days early.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Returns that come too late',
				'how'       => 'Near-expiry stock isn’t grouped by who supplied it.',
				'plug_html' => '<b>Supplier-wise expiry list</b> plus <b>purchase returns with credit notes</b>, so short-expiry stock goes back on record.',
				'feat'      => 'feat_purchase_credit_note',
				'plan'      => '',
			),
			array(
				'leak'      => 'Expired strips at the counter',
				'how'       => 'Dates are checked by eye, in a hurry.',
				'plug_html' => '<b>A store setting</b> controls whether expired items can be billed at all.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Slow stock that will expire next',
				'how'       => 'Items that don’t sell keep getting reordered.',
				'plug_html' => '<b>Non-moving items report</b> shows what isn’t selling, before it reaches its expiry date.',
				'feat'      => 'feat_non_moving_item',
				'plan'      => '',
			),
			array(
				'leak'      => 'Batch and expiry typed wrong',
				'how'       => 'A mistyped date on purchase entry hides the problem.',
				'plug_html' => '<b>CSV import</b> brings batch and expiry from the distributor’s file instead of typing them.',
				'feat'      => 'feat_csv_import',
				'plan'      => '',
			),
			array(
				'leak'      => 'Write-offs nobody records',
				'how'       => 'Expired stock is thrown out, but the system still shows it.',
				'plug_html' => '<b>Stock adjustment</b> records the write-off, so stock and reports stay true.',
				'feat'      => 'feat_stock_adjustment',
				'plan'      => '',
			),
		),
		// No worked example on this page: the register ends with the demo card.
		'leaks_example'   => array(
			'title'        => '',
			'lines'        => array(),
			'result_label' => '',
			'result_value' => '',
			'note'         => '',
		),
		'leaks_estimator' => true,

		'steps_head'      => array(
			'eyebrow' => 'How expiry tracking works',
			'title'   => 'Expiry goes in once, then follows the batch',
			'intro'   => 'You don’t keep a separate expiry list. It builds itself from your purchase bills.',
		),
		'steps'           => array(
			array(
				'title' => 'Batch and expiry come in with the bill',
				'html'  => 'Every purchase line carries batch, expiry and MRP, whether you import a CSV, scan with OCR or type it.',
				'meta'  => 'CSV · OCR · manual',
			),
			array(
				'title' => 'The counter sells oldest first',
				'html'  => 'At billing, the batch that expires first is selected automatically. Staff can still pick another.',
				'meta'  => 'FEFO on every bill',
			),
			array(
				'title' => 'Check the expiry report weekly',
				'html'  => 'Pick a date range, then view it item-wise or supplier-wise to see what needs action.',
				'meta'  => 'Item-wise or supplier-wise',
			),
			array(
				'title' => 'Push, return or write off',
				'html'  => 'Move near-expiry stock forward, return it to the supplier with a credit note, or record a stock adjustment.',
				'meta'  => 'Every outcome on record',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'Expiry and batch tools',
			'title'   => 'Built around the batch, not just the item',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-alert',
				'title' => 'Stock expiry report',
				'html'  => 'Batches expiring in any date range, item-wise or supplier-wise. Export to Excel for a returns list.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-receipt',
				'title' => 'FEFO billing',
				'html'  => 'The earliest-expiry batch fills in first on every bill.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-lock',
				'title' => 'Block expired sales',
				'html'  => 'A store setting controls whether expired items can be billed.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-return',
				'title' => 'Purchase returns and credit notes',
				'html'  => 'Send short-expiry stock back to the supplier and keep the credit on their ledger.',
				'feat'  => 'feat_purchase_credit_note',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-box',
				'title' => 'Non-moving items report',
				'html'  => 'See what isn’t selling, so you stop reordering it before it expires.',
				'feat'  => 'feat_non_moving_item',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Stock adjustment',
				'html'  => 'Record write-offs and corrections, so stock on screen matches the shelf.',
				'feat'  => 'feat_stock_adjustment',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-scan',
				'title' => 'Barcode labels',
				'html'  => 'Print barcode labels for stock that has none, so it scans at the counter instead of being picked by hand.',
				'feat'  => 'feat_label_print',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-shield',
				'title' => 'Item flags',
				'html'  => 'Tag items as Schedule H or H1, and flag LASA, high-risk, antimicrobial and DPCO items in the item master.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-upload',
				'title' => 'Opening stock with expiry',
				'html'  => 'Import items and opening stock with batch and expiry from a CSV file when you start.',
				'feat'  => 'feat_csv_import',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'A weekly routine',
			'title'   => 'A 15-minute expiry check that pays for itself',
			'intro'   => 'Practical habits from how careful stores manage expiry. The software gives you the list; these steps turn it into money saved.',
		),
		'guide'           => array(
			array(
				'title' => 'Pick one day a week',
				'html'  => 'Run the stock expiry report at the same time each week, for example Monday before opening. A habit beats a big clean-up twice a year.',
			),
			array(
				'title' => 'Look 90 days ahead, by supplier',
				'html'  => 'View it supplier-wise. Each supplier’s list becomes a ready returns list for their next visit.',
			),
			array(
				'title' => 'Check each distributor’s return terms',
				'html'  => 'Return rules differ by distributor and company. Note the cut-off for each, and return before it, not after.',
			),
			array(
				'title' => 'Move near-expiry stock to the front',
				'html'  => 'Anything inside 60 days goes to the front of the shelf. FEFO billing then sells it first.',
			),
			array(
				'title' => 'Stop reordering slow items',
				'html'  => 'Cross-check with the non-moving items report. If it hasn’t sold, don’t order more while old stock is still on the shelf.',
			),
			array(
				'title' => 'Record what you throw away',
				'html'  => 'Write off expired stock with a stock adjustment, so your stock value and reports stay honest.',
			),
		),
		'guide_note'      => 'Return terms are set by your distributors, not by the software. Check them with each supplier.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring this list to a 30-minute demo. We’ll run it with sample stock, or with a few of your own items.',
		),
		'proof'           => array(
			array( 'text' => 'Run the stock expiry report for the next 90 days' ),
			array( 'text' => 'Switch it from item-wise to supplier-wise' ),
			array( 'text' => 'Bill an item with two batches and watch the older one fill in' ),
			array( 'text' => 'Turn on the setting that stops expired items being billed' ),
			array( 'text' => 'Make a purchase return with a credit note' ),
			array( 'text' => 'Open the non-moving items report' ),
		),

		'related'         => array( 'billing', 'inventory', 'purchase' ),
		'related_title'   => '',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'How far ahead can I see expiring stock?',
				'a_html'    => 'As far as you like. The stock expiry report works on any date range you choose, item-wise or supplier-wise.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see expiry by supplier for returns?',
				'a_html'    => 'Yes. Supplier-wise view groups near-expiry batches by who supplied them, so each list is ready for that distributor’s visit.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I stop expired medicines being billed?',
				'a_html'    => 'Yes. A store setting controls whether expired items can be sold.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does FEFO force staff to sell a batch?',
				'a_html'    => 'No. The earliest-expiry batch fills in first, and staff can still choose another batch when needed.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How do expiry returns work?',
				'a_html'    => 'Make a purchase return against the supplier and record the credit note, so the credit shows on their ledger.[rx_plan feat="feat_purchase_credit_note" before=" Purchase credit notes are on " after="."]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Will expiry dates come across when I switch?',
				'a_html'    => 'Yes, if your file has them. The initial data import takes batch number and expiry date with your opening stock. See [rx_link key="switch"]switching[/rx_link].',
				'confirmed' => true,
			),
		),
	),
);
