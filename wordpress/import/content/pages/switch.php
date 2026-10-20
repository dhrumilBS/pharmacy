<?php
/**
 * Import data: the Switching page (PHP_Site switch.php).
 *
 * Fields: group_rx_switch (templates/switch.php). The pricing cell of the
 * comparison table is live ([rx_compare_text], pricing_compare_text() today).
 * Text that switch.php writes literally stays literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'switch.php',
	'key'      => 'switch',
	'title'    => 'Switching',
	'slug'     => 'switch',
	'template' => 'templates/switch.php',
	'seo'      => array(
		'title'   => 'Switching from your current pharmacy software %%sep%% %%sitename%%',
		'desc'    => 'Change pharmacy software without stopping a bill: set up free, import items and stock from CSV, train staff with walkthroughs, go live on your day.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Switching',
			'eyebrow' => 'Switching from other software',
			'h1'      => 'Change your software without stopping a single bill',
			'lead'    => 'The biggest worry about switching is the first week. Keep your old system running, set up on the free plan, bring your items and stock in from a CSV file, and go live when you’re ready.',
		),

		// Hero buttons (demo page, then the app's sign-up link).
		'cta_demo'        => 'Plan my switch on a call',
		'cta_free'        => 'Start free and try the import',

		// How it works: four steps with the import screen.
		'steps_head'      => array(
			'eyebrow' => 'How it works',
			'title'   => 'Four steps, at your pace',
			'intro'   => 'Nothing changes at your counter until you decide. We recommend testing with a few days of real bills before switching fully.',
		),
		'screen'          => 'v2-10-initial-import',
		'screen_alt'      => 'Sample screen: Add initial data, importing items and opening stock from a CSV with problem rows flagged line by line',
		'steps'           => array(
			array(
				'title' => 'Create your store account, free',
				'html'  => 'Sign up with your store name, address and email, and verify with an OTP. The Starter plan costs nothing.',
				'when'  => 'A few minutes',
			),
			array(
				'title' => 'Import items and opening stock',
				'html'  => 'Export a list from your current software or Excel. In <b>Initialization → Add Initial Data</b>, download the sample sheet, match your columns, and import. Errors are flagged line by line.',
				'when'  => 'Same day',
			),
			array(
				'title' => 'Add suppliers and staff',
				'html'  => 'Add distributors in Account Master and staff in Users, each with their own login and role. Walkthroughs inside the app show every screen.',
				'when'  => 'An hour or two',
			),
			array(
				'title' => 'Run both, then go live',
				'html'  => 'Bill a few customers on the new system while the old one keeps running. When you’re confident, switch fully and move to a paid plan for unlimited bills.',
				'when'  => 'You choose the day',
			),
		),

		// What comes across: import columns and the checklist card.
		'columns_head'    => array(
			'eyebrow' => 'What comes across',
			'title'   => 'Columns you can import',
			'intro'   => 'These are the fields on the import screen. You don’t need all of them. Item name, batch, expiry, MRP and quantity are enough to start billing.',
		),
		'columns'         => array(
			array( 'name' => 'Item name' ),
			array( 'name' => 'Generic name' ),
			array( 'name' => 'Item category' ),
			array( 'name' => 'Sub-category' ),
			array( 'name' => 'Item type' ),
			array( 'name' => 'Company' ),
			array( 'name' => 'Pack size' ),
			array( 'name' => 'HSN code' ),
			array( 'name' => 'Batch number' ),
			array( 'name' => 'Expiry date' ),
			array( 'name' => 'MRP' ),
			array( 'name' => 'Sale rate' ),
			array( 'name' => 'Min stock' ),
			array( 'name' => 'Max stock' ),
			array( 'name' => 'Supplier' ),
		),
		'columns_tip'     => 'Tip: MRP and PTR should be per strip (or per pack), matching how you sell.',
		'checklist_title' => 'Before you import, a quick checklist',
		'checklist'       => array(
			array( 'html' => 'Export items <b>with batch and expiry</b>, not just item names.' ),
			array( 'html' => 'Remove items you no longer stock, and zero-quantity batches.' ),
			array( 'html' => 'Check expiry dates use one format (e.g. DD-MM-YYYY).' ),
			array( 'html' => 'Match HSN codes and GST rates to your current invoices.' ),
			array( 'html' => 'Do a physical count of fast movers on the import day.' ),
		),
		'notes'           => array(
			array(
				'html'      => 'Need help preparing the file? [Confirm whether assisted import is offered.]',
				'confirmed' => false,
			),
		),

		// Moving from…
		'from_head'       => array(
			'eyebrow' => 'Moving from…',
			'title'   => 'Wherever you’re coming from',
		),
		'from'            => array(
			array(
				'icon'  => 'i-screen',
				'title' => 'Desktop pharmacy software',
				'text'  => 'Most desktop packages can export item and stock lists to Excel or CSV from their reports. Export items with batch, expiry and MRP, then import here. Keep the old PC running until you go live.',
			),
			array(
				'icon'  => 'i-receipt',
				'title' => 'A general billing app',
				'text'  => 'General apps often don’t track batch or expiry. Export your item list, add batch and expiry from your shelf or latest purchase bills, and import. From then on, every purchase adds batch and expiry for you.',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Excel or paper registers',
				'text'  => 'Use our sample sheet as your template. Start with your top-selling items, import, and add the rest as stock arrives, since each purchase bill can be imported by CSV or OCR.',
			),
		),

		// Comparison by type of software.
		'compare_head'    => array(
			'eyebrow' => 'Compare',
			'title'   => 'Why stores switch',
			'intro'   => 'A plain comparison by type of software, not by brand.',
		),
		'compare_cols'    => array(
			'what'    => 'What matters',
			'desktop' => 'Typical desktop licence software',
			'app'     => 'Typical general billing app',
			'ours'    => 'Our software',
		),
		'compare'         => array(
			array(
				'what'      => 'Nearest-expiry batch at billing',
				'desktop'   => 'Varies by package',
				'app'       => 'Usually no batch tracking',
				'ours_html' => 'Batch auto-selected by nearest expiry (FEFO)',
			),
			array(
				'what'      => 'Where it runs',
				'desktop'   => 'One PC in the store',
				'app'       => 'Phone or browser',
				'ours_html' => 'Any web browser; log in from anywhere',
			),
			array(
				'what'      => 'Distributor bill entry',
				'desktop'   => 'Often typed line by line',
				'app'       => 'Manual entry',
				'ours_html' => 'CSV import or OCR scan',
			),
			array(
				'what'      => 'Pricing',
				'desktop'   => 'Licence plus yearly AMC, often by quote',
				'app'       => 'Low monthly fee',
				'ours_html' => '[rx_compare_text]',
			),
			array(
				'what'      => 'Hospital pharmacy (IPD, TPA, EMR)',
				'desktop'   => 'Separate modules, if any',
				'app'       => 'Not covered',
				'ours_html' => 'Built in, plus ABDM',
			),
			array(
				'what'      => 'Staff controls',
				'desktop'   => 'Varies',
				'app'       => 'Basic',
				'ours_html' => 'Roles, two-step login, IP whitelisting (Advance)',
			),
		),
		'compare_note'    => 'Descriptions of other software types are general and vary by product.',

		// Switching FAQ (ids sq0…, data-confirm on the item).
		'faq_head'        => array(
			'eyebrow'    => 'Switching FAQ',
			'title'      => 'Common worries',
			'intro_html' => 'More in the [rx_link key="faq" hash="switch"]FAQ[/rx_link].',
		),
		'faq'             => array(
			array(
				'q'         => 'Will billing stop while we switch?',
				'a_html'    => 'No. Your current software keeps running until you choose to go live.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Do I lose my old data?',
				'a_html'    => 'No. Your old system keeps its records. Import what you need to bill from day one (items and opening stock), and keep the old reports for reference.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How long until my staff are comfortable?',
				'a_html'    => 'The counter screen is search, pick, save. Built-in walkthroughs and keyboard shortcuts help new staff, and we recommend testing with real bills for a few days before switching.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can someone help me with the import?',
				'a_html'    => '[Confirm whether assisted import is offered, and at what cost.]',
				'confirmed' => false,
			),
		),
	),
);
