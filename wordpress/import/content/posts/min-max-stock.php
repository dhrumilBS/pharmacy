<?php
/**
 * Import data: post "How to set minimum and maximum stock for a medical store".
 *
 * Source: blog/min-max-stock.php (article and FAQ) and its entry in
 * partials/data/blog.php (title, summary, topic, date, read time, cover),
 * copied exactly. Each <h2>, run of literal HTML and bk_*() call is one
 * `body` row, in order; the FAQ is the blog_end() list (all confirmed).
 *
 * Live value: $po = feature_plan_label( 'feat_purchase_order' ) becomes
 * [rx_plan feat="feat_purchase_order"], with the surrounding words in
 * before/after so they print only when the plan is known, as on the PHP_Site.
 * The worked numbers are illustrative and stay literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/min-max-stock.php',
	'type'   => 'post',
	'title'  => 'How to set minimum and maximum stock for a medical store',
	'slug'   => 'min-max-stock',
	'date'   => '2026-10-02',
	'status' => 'publish',
	'seo'    => array(
		'title'   => 'Set minimum and maximum stock for a medical store %%sep%% %%sitename%%',
		'desc'    => 'Too little stock loses the sale; too much ties up cash. A plain rule for setting levels, with worked numbers for fast and slow movers.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'Too little stock loses the sale; too much ties up cash. A plain rule for setting levels, with worked numbers for fast and slow movers.',
		'topic'        => 'expiry',
		'read_time'    => 7,
		'featured'     => false,
		'cover_screen' => 'v2-05-reorder',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'Minimum = what you sell until the next delivery, plus a small cushion.' ),
					array( 'html' => 'Maximum = minimum plus one normal order. Anything above that is cash sitting on a shelf.' ),
					array( 'html' => 'Set levels for your top 100 items first. They are most of your sales.' ),
					array( 'html' => 'Review once a month: what ran out, and what piled up.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Stock problems cost a medical store twice. Run out, and the customer buys elsewhere. Overstock, and the money that should be paying distributors sits on the shelf, sometimes until it expires. Minimum and maximum levels are how you stay between the two.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'The rule',
			),
			array(
				'acf_fc_layout' => 'rule',
				'html'          => 'Minimum = daily sales × days to next delivery + a cushion.<br>Maximum = minimum + one normal order.',
				'label'         => 'Rule of thumb',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>The cushion covers a busy day or a late delivery. For most items, one extra day of sales is enough. For items a regular can’t do without, be more generous.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'A worked example',
			),
			array(
				'acf_fc_layout' => 'sum',
				'title'         => 'A fast-moving BP tablet',
				'lines'         => array(
					array(
						'label' => 'Strips sold on a normal day',
						'value' => '6',
						'op'    => 'auto',
					),
					array(
						'label' => 'Days until the next delivery',
						'value' => '2',
						'op'    => 'auto',
					),
					array(
						'label' => 'Plus a one-day cushion',
						'value' => '6 strips',
						'op'    => '+',
					),
				),
				'result_label'  => 'Minimum stock',
				'result_value'  => '18 strips',
				'note'          => 'Sales × days = 12 strips, plus 6 for the cushion. If you usually order 30 at a time, the maximum is 48.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Fast, medium and slow movers',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>One rule, different results. The slower an item sells, the more its maximum should be held back.</p>',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Item\nSold a day\nDelivery in\nMinimum\nMaximum",
				'rows'          => array(
					array( 'cells' => "<b>Fast mover</b><br><small>BP or diabetes tablet</small>\n6 strips\n2 days\n18\n48" ),
					array( 'cells' => "<b>Medium mover</b><br><small>Antacid, vitamins</small>\n2 strips\n2 days\n6\n20" ),
					array( 'cells' => "<b>Slow mover</b><br><small>Seasonal syrup</small>\n2 a month\n3 days\n1\n3" ),
				),
				'caption'       => 'Illustrative numbers. Your delivery days and sales decide the right levels.',
				'highlight_col' => '3',
				'numeric_cols'  => '1, 3, 4',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'tip',
				'title'         => 'Keep slow items lean',
				'html'          => '<p>For an item that sells a few times a month, keep the maximum low and check the non-moving items report before reordering. A missed sale on a slow item costs less than a box that expires.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Where to start',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => "<ol>\n  <li><b>Your top 100 items.</b> Sort last month’s sales and set levels for the top of the list.</li>\n  <li><b>Items regulars depend on.</b> Monthly BP, diabetes and thyroid medicines get a bigger cushion.</li>\n  <li><b>Everything else, as you go.</b> Set levels when you place the next order for each item.</li>\n</ol>",
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'inline',
				'text'          => 'Running out of a few items every week?',
				'link_label'    => 'See how the short book works',
				'link_key'      => 'inventory',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Review once a month',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Levels drift as seasons change and doctors prescribe differently. Once a month, look at two lists: what ran out (raise the minimum) and what has barely moved (lower the maximum, or stop ordering).</p>',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'inventory',
				'text'          => 'Min and max per item, a stock alert and short book[rx_plan feat="feat_purchase_order" before=", and purchase orders from the short book (" after=")"].',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'See the short book with your items',
				'text'          => 'We’ll set levels on a few of your fast movers in a demo and show the stock alert fill in as you bill.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'How does reordering work in the software?',
				'a_html'    => 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book.[rx_plan feat="feat_purchase_order" before=" On " after=" you can turn the short book into purchase orders."]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see which items aren’t selling?',
				'a_html'    => 'Yes. The non-moving items report shows stock that isn’t selling, and the top-selling items report shows what is.',
				'confirmed' => true,
			),
		),
	),
);
