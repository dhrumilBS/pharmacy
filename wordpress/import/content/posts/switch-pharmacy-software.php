<?php
/**
 * Import data: post "Switching pharmacy software without stopping billing".
 *
 * Source: blog/switch-pharmacy-software.php (article and FAQ) and its entry
 * in partials/data/blog.php (title, summary, topic, date, read time, cover),
 * copied exactly. Each <h2>, run of literal HTML and bk_*() call is one
 * `body` row, in order; the FAQ is the blog_end() list (all confirmed).
 *
 * Live value: the free plan name in week 1 (pricing_split()['free'][0]) is
 * [rx_free_plan field="name"], printed with " on the free … plan" only when
 * there is a free plan, as the PHP_Site does.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/switch-pharmacy-software.php',
	'type'   => 'post',
	'title'  => 'Switching pharmacy software without stopping billing: a four-week plan',
	'slug'   => 'switch-pharmacy-software',
	'date'   => '2026-10-09',
	'status' => 'publish',
	'seo'    => array(
		'title'   => 'Switch pharmacy software without stopping billing %%sep%% %%sitename%%',
		'desc'    => 'The real risk in switching is the first week at the counter. A week-by-week plan to run both systems and go live on your own day.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'The real risk in switching is the first week at the counter. A week-by-week plan to run both systems and go live on your own day.',
		'topic'        => 'switching',
		'read_time'    => 8,
		'featured'     => false,
		'cover_screen' => 'v2-10-initial-import',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'Don’t switch on a single day. Run the old and new systems side by side, then pick your go-live day.' ),
					array( 'html' => 'Import items and opening stock <b>with batch and expiry</b>. Item names alone aren’t enough to bill a medicine.' ),
					array( 'html' => 'Test with real bills from your own counter before you rely on it in the evening rush.' ),
					array( 'html' => 'Give every staff member their own login from the first day, not after go-live.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Owners rarely worry about whether new software can bill. They worry about the Saturday evening after they switch, with a queue at the counter and staff who learned the screen yesterday. That worry is reasonable, and a plan removes most of it.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Why “switch over the weekend” goes wrong',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>A one-day switch puts three risks on the same day: data that hasn’t been checked, staff who haven’t practised, and a printer nobody has tested. If any of them fails, billing slows down exactly when you can least afford it.</p>',
			),
			array(
				'acf_fc_layout' => 'compare',
				'title'         => 'Two ways to change software',
				'a_name'        => 'Big-bang switch',
				'a_tag'         => 'One day',
				'b_name'        => 'Parallel run',
				'b_tag'         => 'Two to four weeks',
				'winner'        => 'b',
				'rows'          => array(
					array(
						'label'  => 'Old system',
						'a_html' => 'Switched off on day one',
						'b_html' => 'Keeps running until you’re confident',
					),
					array(
						'label'  => 'Data check',
						'a_html' => 'Found at the counter, in front of customers',
						'b_html' => 'Found during the trial, with time to fix',
					),
					array(
						'label'  => 'Staff',
						'a_html' => 'Learn during the rush',
						'b_html' => 'Practise on quiet hours first',
					),
					array(
						'label'  => 'If something goes wrong',
						'a_html' => 'Billing stops',
						'b_html' => 'Carry on in the old system',
					),
				),
				'verdict_html'  => 'Run both. It costs a few extra minutes a day for a couple of weeks, and it removes the day-one risk.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'A four-week plan',
			),
			array(
				'acf_fc_layout' => 'steps',
				'items'         => array(
					array(
						'title' => 'Week 1: set up and import',
						'html'  => '<p>Create the store account[rx_free_plan field="name" before=" on the free " after=" plan"]. Export items and stock from your current software or Excel, match your columns to the sample sheet, and import. Fix the rows that are flagged.</p>',
						'when'  => 'Old system: running as usual',
					),
					array(
						'title' => 'Week 2: suppliers, staff and printers',
						'html'  => '<p>Add your distributors and each staff member with their own login and role. Print a few test bills on your thermal and A5 printers, and scan a few strips with the barcode reader.</p>',
						'when'  => 'Old system: running as usual',
					),
					array(
						'title' => 'Week 3: bill in quiet hours',
						'html'  => '<p>Bill a few real customers on the new system in the morning, while the old one handles the rush. Enter one distributor bill in both and compare the stock.</p>',
						'when'  => 'Both systems',
					),
					array(
						'title' => 'Week 4: go live on your day',
						'html'  => '<p>Pick a quiet day of the week. Do a physical count of fast movers in the morning, switch billing fully, and keep the old system for reference.</p>',
						'when'  => 'New system',
					),
				),
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'note',
				'title'         => 'Start small if your item list is long',
				'html'          => '<p>If cleaning thousands of items feels like too much, import your top sellers first with batch and expiry, and add the rest as stock arrives. Every purchase bill you import adds its items for you.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'What to bring across',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Data\nBring across?\nNotes",
				'rows'          => array(
					array( 'cells' => "<b>Items with batch, expiry and MRP</b>\nYes, before go-live\nThe minimum you need to bill a medicine" ),
					array( 'cells' => "<b>Opening stock quantity</b>\nYes, before go-live\nCount fast movers on the import day" ),
					array( 'cells' => "<b>HSN code and GST rate</b>\nYes\nMatch them to your current invoices" ),
					array( 'cells' => "<b>Suppliers</b>\nYes\nAdd them in the account master" ),
					array( 'cells' => "<b>Minimum and maximum stock</b>\nHelpful\nCan be set later, item by item" ),
					array( 'cells' => "<b>Old bills and reports</b>\nKeep in the old system\nExport what you need for reference" ),
				),
				'caption'       => 'Columns available on the import screen include item, generic name, batch, expiry, MRP, HSN, stock, min/max and supplier.',
				'highlight_col' => '1',
				'numeric_cols'  => '',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'caution',
				'title'         => 'Clean the file before you import',
				'html'          => '<ul><li>Remove items you no longer stock and batches with zero quantity.</li><li>Use one date format for expiry, for example DD-MM-YYYY.</li><li>Check MRP is per strip or per pack, the way you sell.</li></ul>',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'switch',
				'text'          => 'Import items and opening stock from CSV, with a sample sheet and errors flagged line by line.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'The go-live day checklist',
			),
			array(
				'acf_fc_layout' => 'checklist',
				'title'         => 'On the day you switch',
				'items'         => array(
					array( 'html' => 'Count fast-moving items and correct their stock' ),
					array( 'html' => 'Every staff member can log in with their own account' ),
					array( 'html' => 'Thermal and A5 bills print correctly' ),
					array( 'html' => 'The barcode reader finds the right items' ),
					array( 'html' => 'One distributor bill entered and checked' ),
					array( 'html' => 'The old system is still available for reference' ),
				),
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'Plan your switch on a call',
				'text'          => 'Tell us what you use today and we’ll walk through the import with a sample of your own items.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'Will billing stop while we switch?',
				'a_html'    => 'No. Your current software keeps running until you choose to go live.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I bring over data from my current software?',
				'a_html'    => 'Yes, through CSV import. Export your items and stock, match your columns to the sample sheet and import. Rows with errors are flagged so you can fix them.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Do I lose my old data?',
				'a_html'    => 'No. Your old system keeps its records. Import what you need to bill from day one, and keep the old reports for reference.',
				'confirmed' => true,
			),
		),
	),
);
