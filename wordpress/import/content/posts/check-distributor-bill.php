<?php
/**
 * Import data: post "Six checks before you save a distributor bill".
 *
 * Source: blog/check-distributor-bill.php (article and FAQ) and its entry in
 * partials/data/blog.php (title, summary, topic, date, read time, cover),
 * copied exactly. Each <h2>, run of literal HTML and bk_*() call is one
 * `body` row, in order; the FAQ is the blog_end() list (all confirmed).
 *
 * Live value: $rc = feature_plan_label( 'feat_supplier_rate_contract' )
 * becomes [rx_plan feat="feat_supplier_rate_contract"], with its sentence in
 * before/after so it prints only when the plan is known, as on the PHP_Site.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/check-distributor-bill.php',
	'type'   => 'post',
	'title'  => 'Six checks before you save a distributor bill',
	'slug'   => 'check-distributor-bill',
	'date'   => '2026-09-25',
	'status' => 'publish',
	'seo'    => array(
		'desc'    => 'Most purchase mistakes are made in the minute before you press save. Six checks that catch wrong batches, missing free quantity and rates that drifted.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'Most purchase mistakes are made in the minute before you press save. Six checks that catch wrong batches, missing free quantity and rates that drifted.',
		'topic'        => 'purchase',
		'read_time'    => 6,
		'featured'     => false,
		'cover_screen' => 'v2-04-purchase-import',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'Most purchase mistakes happen in the minute before you press save, and they surface weeks later.' ),
					array( 'html' => 'Free quantity belongs on the line as stock, not as a discount.' ),
					array( 'html' => 'A margin that looks low on save is your cue to check the rate.' ),
					array( 'html' => 'Decide about short-expiry stock now, while the delivery is still in front of you.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>A distributor bill decides most of a medical store’s margin: the rate you paid, the scheme you got, and what you can still send back. It also tends to be entered last thing at night. Importing the bill instead of typing it removes most typing errors. These six checks catch the rest.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '1. Batch and expiry match the carton',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Pick three lines, especially anything short-dated, and compare the batch and expiry on screen with the carton in front of you. A wrong batch now means a wrong FEFO pick at the counter later.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '2. Free quantity is on the line',
			),
			array(
				'acf_fc_layout' => 'compare',
				'title'         => 'The same 10 + 1 scheme, entered two ways',
				'a_name'        => 'Entered as a discount',
				'a_tag'         => 'Common mistake',
				'b_name'        => 'Entered as free quantity',
				'b_tag'         => 'Correct',
				'winner'        => 'b',
				'rows'          => array(
					array(
						'label'  => 'Stock on screen',
						'a_html' => '10 strips',
						'b_html' => '11 strips',
					),
					array(
						'label'  => 'Cost per strip',
						'a_html' => 'Lower rate, but one strip missing',
						'b_html' => 'True cost across 11',
					),
					array(
						'label'  => 'At the counter',
						'a_html' => 'The 11th strip can’t be billed',
						'b_html' => 'All 11 sell normally',
					),
				),
				'verdict_html'  => 'Record free quantity on the purchase line, so stock and cost are both right.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '3. The rate is what you agreed',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Compare the rate (PTR) with the last bill from the same distributor. The margin shown when you save is a quick sense check: if a fast mover’s margin suddenly looks thin, look at the rate before you save.</p>',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'tip',
				'title'         => 'Read the Diff % column',
				'html'          => '<p>The purchase screen has a Diff % column that compares each line’s margin with your last purchase, or with the purchase average. A negative number means you now earn less on that item than last time: check the rate before you save. It isn’t a pop-up alert, so make glancing at it a habit.[rx_plan feat="feat_supplier_rate_contract" before=" On " after=", supplier rate contracts record agreed rates per supplier."]</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '4. MRP is per strip or per pack, the way you sell',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>If you sell by the strip, the MRP on the line should be per strip. A pack MRP entered as a strip MRP shows up at the counter as a price that’s ten times too high.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '5. GST matches the item master',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>If the rate on the distributor’s bill and the rate in your item master disagree, find out which is right and fix the item before saving. Sales of that item take their tax from the item master.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => '6. Short-expiry stock is flagged',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>If the delivery includes stock close to expiry, decide now: accept it, or keep it aside to return on the rep’s next visit. It’s much harder to argue a week later.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Common mistakes and where they show up',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Mistake on the bill\nWhere you notice it\nHow long it can hide",
				'rows'          => array(
					array( 'cells' => "<b>Wrong batch or expiry</b>\nExpiry report and FEFO picks are wrong\nMonths" ),
					array( 'cells' => "<b>Free quantity missing</b>\nStock count is short; margin is off\nUntil stock-taking" ),
					array( 'cells' => "<b>Rate higher than agreed</b>\nMargin report for the item\nEvery bill until someone looks" ),
					array( 'cells' => "<b>Pack MRP as strip MRP</b>\nA customer at the counter\nThe first sale" ),
					array( 'cells' => "<b>Credit note not entered</b>\nSupplier statement disagrees with your ledger\nUntil payment day" ),
				),
				'caption'       => 'Ranked roughly by how long each mistake can go unnoticed.',
				'highlight_col' => '',
				'numeric_cols'  => '',
			),
			array(
				'acf_fc_layout' => 'checklist',
				'title'         => 'Before you press save',
				'items'         => array(
					array( 'html' => 'Three lines checked against the carton for batch and expiry' ),
					array( 'html' => 'Free quantity entered as stock on the line' ),
					array( 'html' => 'Margin on save looks normal for fast movers' ),
					array( 'html' => 'MRP is per strip or pack, the way you sell' ),
					array( 'html' => 'GST on the bill matches the item master' ),
					array( 'html' => 'Short-expiry stock set aside, or accepted on purpose' ),
				),
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'purchase',
				'text'          => 'Import bills by CSV or OCR, with scheme and free quantity on the line and margin worked out on save.',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'Bring a distributor bill to a demo',
				'text'          => 'We’ll import it, show the free quantity in stock and the margin on save.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'Do I have to type every distributor bill?',
				'a_html'    => 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). You can still type a bill by hand when needed.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it record scheme and free quantity?',
				'a_html'    => 'Yes. Scheme and free quantity are recorded on the purchase line, and PTR and margin are worked out when you save.',
				'confirmed' => true,
			),
		),
	),
);
