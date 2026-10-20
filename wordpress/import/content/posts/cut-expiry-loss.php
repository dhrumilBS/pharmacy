<?php
/**
 * Import data: post "How to cut expiry loss in a medical store".
 *
 * Source: blog/cut-expiry-loss.php (article and FAQ) and its entry in
 * partials/data/blog.php (title, summary, topic, date, read time, cover,
 * featured), copied exactly. Each <h2>, run of literal HTML and bk_*() call
 * is one `body` row, in order; the FAQ is the blog_end() list (all
 * confirmed: the PHP_Site puts every answer in FAQPage schema).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/cut-expiry-loss.php',
	'type'   => 'post',
	'title'  => 'How to cut expiry loss in a medical store: a 15-minute weekly routine',
	'slug'   => 'cut-expiry-loss',
	'date'   => '2026-10-14',
	'status' => 'publish',
	'seo'    => array(
		'title'   => 'Cut expiry loss in a medical store: weekly routine %%sep%% %%sitename%%',
		'desc'    => 'Expiry is the leak most owners never measure. A simple weekly routine, the maths behind it, and what to do with each near-expiry batch.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'Expiry is the leak most owners never measure. A simple weekly routine, the maths behind it, and what to do with each near-expiry batch.',
		'topic'        => 'expiry',
		'read_time'    => 9,
		'featured'     => true,
		'cover_screen' => 'v2-03-expiry',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'Expiry loss is money you have already paid the distributor. Most stores have never put a number on it.' ),
					array( 'html' => 'Selling the <b>first-expiring</b> batch first (FEFO) matters more than selling the first-received batch first (FIFO).' ),
					array( 'html' => 'A 15-minute check once a week, grouped by supplier, turns near-expiry stock into returns, pushes or planned write-offs.' ),
					array( 'html' => 'Return windows belong to your distributors, not to you. Note each one and act before it closes.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => "<p>Ask an owner what they lost to theft last year and you’ll get a guess. Ask what they lost to expiry and you’ll usually get a pause. Expiry doesn’t arrive as one event. It’s a strip here, a box there, found during a shelf check months after it could have been sold or sent back.</p>\n<p>This guide is about turning that pause into a number, and the number into a routine.</p>",
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Why expiry is the quiet leak',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Every strip that expires on your shelf was paid for in full. Unlike a slow month, there’s no chance to make it up later: the money has already left. And because nobody writes “expired” in a sales report, it rarely shows up until stock-taking.</p>',
			),
			array(
				'acf_fc_layout' => 'sum',
				'title'         => 'One percent of purchases',
				'lines'         => array(
					array(
						'label' => 'Monthly purchase from distributors',
						'value' => '₹8,00,000',
						'op'    => 'auto',
					),
					array(
						'label' => 'Share that expires before it sells',
						'value' => '1%',
						'op'    => 'auto',
					),
					array(
						'label' => 'Months in a year',
						'value' => '12',
						'op'    => 'auto',
					),
				),
				'result_label'  => 'Expiry loss in a year',
				'result_value'  => '₹96,000',
				'note'          => 'Put in your own numbers. Even half a percent is a figure most owners would rather keep.',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'inline',
				'text'          => 'Want this with your numbers?',
				'link_label'    => 'Use the expiry loss calculator',
				'link_key'      => 'calculator',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'FIFO or FEFO: which batch goes out first',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Most stores are taught FIFO: first in, first out. It works for goods without an expiry date. Medicines don’t arrive in expiry order, though. A distributor can send a batch today that expires <i>before</i> the one you received last month.</p>',
			),
			array(
				'acf_fc_layout' => 'compare',
				'title'         => 'Two ways to pick the batch at the counter',
				'a_name'        => 'FIFO',
				'a_tag'         => 'First in, first out',
				'b_name'        => 'FEFO',
				'b_tag'         => 'First expiry, first out',
				'winner'        => 'b',
				'rows'          => array(
					array(
						'label'  => 'Which batch goes out',
						'a_html' => 'The one received earliest',
						'b_html' => 'The one that expires earliest',
					),
					array(
						'label'  => 'When batches arrive out of order',
						'a_html' => 'A newer batch can expire on the shelf',
						'b_html' => 'Still sells the shortest-dated batch',
					),
					array(
						'label'  => 'What staff need to check',
						'a_html' => 'Delivery date, often not on the strip',
						'b_html' => 'Expiry date, printed on every strip',
					),
					array(
						'label'  => 'Fits medicines',
						'a_html' => 'Sometimes',
						'b_html' => 'Yes',
					),
				),
				'verdict_html'  => 'For medicines, use FEFO. If the software fills in the earliest-expiry batch at billing, staff don’t have to remember it in the rush.',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'tip',
				'title'         => 'Let the shelf match the screen',
				'html'          => '<p>FEFO at billing only works if the shortest-dated strips are also the easiest to reach. When you restock, put new boxes <b>behind</b> the old ones, not in front.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'The 15-minute weekly routine',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>A big clean-up twice a year finds expired stock. A small check every week finds stock that’s <i>about</i> to expire, while you can still do something about it.</p>',
			),
			array(
				'acf_fc_layout' => 'steps',
				'items'         => array(
					array(
						'title' => 'Pick a fixed slot',
						'html'  => '<p>Same day, same time, for example Monday before opening. A habit beats good intentions.</p>',
						'when'  => '2 minutes',
					),
					array(
						'title' => 'Pull the next 90 days, by supplier',
						'html'  => '<p>Run your stock expiry report for the next 90 days and group it by supplier. Each supplier’s list becomes a returns list for their next visit.</p>',
						'when'  => '3 minutes',
					),
					array(
						'title' => 'Decide each batch',
						'html'  => '<p>Use the table below: return it, push it, or plan for it. Mark the decision next to each line.</p>',
						'when'  => '5 minutes',
					),
					array(
						'title' => 'Move stock and stop reorders',
						'html'  => '<p>Bring short-dated stock to the front, and check slow items before the next order goes out.</p>',
						'when'  => '5 minutes',
					),
				),
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'What to do with each near-expiry batch',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>A starting point most stores can use. Adjust the windows to your distributors’ return terms and how fast each item sells.</p>',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Time to expiry\nWhat to do\nWhy",
				'rows'          => array(
					array( 'cells' => "<b>90 to 60 days</b>\nCheck against the supplier’s return terms; flag for return if it won’t sell in time\nMost return windows close before the last month" ),
					array( 'cells' => "<b>60 to 30 days</b>\nMove to the front of the shelf; stop reordering the item\nFEFO billing will sell it first if it’s within reach" ),
					array( 'cells' => "<b>Under 30 days</b>\nReturn if the supplier still accepts it; otherwise plan the write-off\nPatients need enough days to finish the course" ),
					array( 'cells' => "<b>Expired</b>\nRemove from the shelf and record a stock adjustment\nKeeps stock and reports true" ),
				),
				'caption'       => 'A starting point, not a rule. Return windows differ by distributor and company.',
				'highlight_col' => '1',
				'numeric_cols'  => '',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'caution',
				'title'         => 'Return windows are the distributor’s, not yours',
				'html'          => '<p>Return and credit rules differ by distributor and by company, and they can change. Write down each supplier’s cut-off and act on the earlier date. Nothing in your software can extend a window that has closed.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Stop expired strips reaching the counter',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => "<p>Even with a routine, an expired strip can slip through on a busy evening. Two simple guards help:</p>\n<ul>\n  <li><b>Block expired sales.</b> A store setting can stop expired items being billed at all, so the bill can’t go through.</li>\n  <li><b>Record what you remove.</b> When you take expired stock off the shelf, record a stock adjustment, so the screen and the shelf agree.</li>\n</ul>",
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'expiry',
				'text'          => 'Batch-wise expiry report, FEFO billing, purchase returns with credit notes and a setting to stop expired sales.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Stop buying tomorrow’s expiry',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>The cheapest expiry to fix is the one you never buy. Before each order, look at what isn’t moving. If an item hasn’t sold in months and an older batch is still on the shelf, it doesn’t need another box.</p>',
			),
			array(
				'acf_fc_layout' => 'rule',
				'html'          => 'If it hasn’t sold since the last order, <b>don’t order it again</b> until the shelf is nearly empty.',
				'label'         => 'Rule of thumb',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'example',
				'title'         => 'A slow item, caught in time',
				'html'          => '<p>A cough syrup sells two bottles a month. Ten are on the shelf, the earliest expiring in four months. Ordering another five would leave stock that can’t sell before it expires. Skip this order, push the existing bottles forward and review next month.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Your weekly expiry checklist',
			),
			array(
				'acf_fc_layout' => 'checklist',
				'title'         => 'Every week, before opening',
				'items'         => array(
					array( 'html' => 'Run the stock expiry report for the next 90 days, grouped by supplier' ),
					array( 'html' => 'Mark each batch: return, push or plan' ),
					array( 'html' => 'Note each supplier’s return cut-off on their list' ),
					array( 'html' => 'Move anything under 60 days to the front of the shelf' ),
					array( 'html' => 'Check the non-moving items report before placing orders' ),
					array( 'html' => 'Record a stock adjustment for anything removed' ),
				),
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'See your own expiry report in a demo',
				'text'          => 'Bring a few items from your shelf. We’ll show the batch-wise expiry report and how FEFO picks the batch at billing.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'What is FEFO?',
				'a_html'    => 'First expiry, first out: at billing, the batch that expires first is sold first, whatever order the stock arrived in.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can the software stop expired medicines being billed?',
				'a_html'    => 'Yes. A store setting controls whether expired items can be sold.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see near-expiry stock by supplier?',
				'a_html'    => 'Yes. The stock expiry report works on any date range, item-wise or supplier-wise.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How do I record a return to the distributor?',
				'a_html'    => 'Make a purchase return against the supplier and record the credit note, so the credit shows on their ledger.',
				'confirmed' => true,
			),
		),
	),
);
