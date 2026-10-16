<?php
/**
 * Import data: the "Stock & reorder" feature page (/features/inventory/).
 *
 * PHP_Site source: features/inventory.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * Every use of feature_plan_label() (the rows' plans and the purchase order
 * plan $po inside copy) is a `feat` value or [rx_plan]. The worked example
 * keeps today's illustrative numbers as text (6 × ₹120 × 3 = ₹2,160).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/inventory.php',
	'key'      => 'inventory',
	'title'    => 'Stock & reorder',
	'slug'     => 'inventory',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy inventory management software: stock alerts, short book and reorder %%sep%% %%sitename%%',
		'desc'    => 'Medical store stock management: min and max stock per item, stock alerts and a short book, purchase orders from the short book, non-moving items, recorded stock adjustments and barcode labels.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Stock & reorder',
			'parent'  => 'Features',
			'eyebrow' => 'Stock & reorder',
			'h1'      => 'Know what’s on the shelf, what’s running out and what isn’t moving',
			'lead'    => 'Set a minimum and maximum for each item. The stock alert and short book fill in as you bill, slow stock shows up in its own report, and every correction is recorded, so the number on screen is one you can trust.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the short book in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'Min and max stock per item' ),
			array( 'text' => 'Short book that builds as you bill' ),
			array( 'text' => '[rx_plan feat="feat_purchase_order" before="Purchase orders from the short book (" after=")"]' ),
		),
		'screen'          => 'v2-05-reorder',
		'screen_alt'      => 'Stock alert and short book grouped by supplier',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Stock problems cost you twice',
			'intro'   => 'Too little and you lose the sale. Too much and your money sits on the shelf.',
		),
		'scenes'          => array(
			array(
				'time'  => 'Sunday morning',
				'title' => 'Out of a fast mover',
				'text'  => 'A regular asks for their monthly medicine. You sold the last strip yesterday and nobody noted it.',
				'cost'  => 'the sale today, and maybe the regular customer.',
			),
			array(
				'time'  => 'Stock check',
				'title' => 'Screen says 5, shelf says 2',
				'text'  => 'Nobody knows if it was a wrong entry, a return that wasn’t recorded, or something worse.',
				'cost'  => 'money you can’t account for, and doubt about your staff.',
			),
			array(
				'time'  => 'Month-end',
				'title' => 'Cash stuck in slow stock',
				'text'  => 'Shelves are full, but the bank balance isn’t. Half the stock hasn’t moved in months.',
				'cost'  => 'working capital you could have used for fast movers.',
			),
			array(
				'time'  => 'Order time',
				'title' => 'Ordering from memory',
				'text'  => 'You call the distributor at night and try to remember what’s low. Two items get missed, one gets ordered twice.',
				'cost'  => 'stock-outs next week and extra stock you didn’t need.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where stock money leaks, and what plugs it',
			'intro'   => 'Each leak, then what plugs it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Running out of fast movers',
				'how'       => 'Nobody notices stock falling until the shelf is empty.',
				'plug_html' => '<b>Minimum stock per item</b> with a <b>stock alert</b>, so low items show up before they run out.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Forgotten items on the order',
				'how'       => 'The order list lives in someone’s head.',
				'plug_html' => '<b>Short book</b> of items below minimum, ready to order from.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Ordering by phone and paper',
				'how'       => 'Orders are retyped and items get missed.',
				'plug_html' => '<b>Reorder processing</b> turns the short book into <b>purchase orders</b>.',
				'feat'      => 'feat_reorder_processing',
				'plan'      => '',
			),
			array(
				'leak'      => 'Money stuck in slow stock',
				'how'       => 'Items that don’t sell keep getting reordered.',
				'plug_html' => '<b>Non-moving items report</b> and <b>top-selling items report</b>, so you buy more of what sells.',
				'feat'      => 'feat_non_moving_item',
				'plan'      => '',
			),
			array(
				'leak'      => 'Overstock of slow items',
				'how'       => 'Orders go beyond what the shelf can sell.',
				'plug_html' => '<b>Maximum stock per item</b>, so you know when there is already enough.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Stock gaps nobody explains',
				'how'       => 'Corrections are made by changing the number.',
				'plug_html' => '<b>Stock adjustment</b> records every correction, and the <b>activity report</b> shows who did what.',
				'feat'      => 'feat_stock_adjustment',
				'plan'      => '',
			),
			array(
				'leak'      => 'Wrong item picked by hand',
				'how'       => 'Look-alike packs are picked by eye.',
				'plug_html' => '<b>Barcode label printing</b>, so stock scans at the counter.',
				'feat'      => 'feat_label_print',
				'plan'      => '',
			),
		),
		'leaks_example'   => array(
			'title'        => 'One fast mover out of stock',
			'lines'        => array(
				array(
					'label' => 'Strips sold on a normal day',
					'value' => '6',
					'op'    => 'auto',
				),
				array(
					'label' => 'Price per strip',
					'value' => '₹120',
					'op'    => 'auto',
				),
				array(
					'label' => 'Days until the next delivery',
					'value' => '3',
					'op'    => 'auto',
				),
			),
			'result_label' => 'Sales missed on one item',
			'result_value' => '₹2,160',
			'note'         => 'Change the numbers to match your store, then multiply by the items you run out of in a month.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How reordering works',
			'title'   => 'From empty shelf to purchase order',
			'intro'   => 'Set the rules once. After that the short book keeps itself up to date.',
		),
		'steps'           => array(
			array(
				'title' => 'Set min and max per item',
				'html'  => 'Start with your top sellers. The minimum is when to reorder; the maximum is how much is enough.',
				'meta'  => 'Once per item',
			),
			array(
				'title' => 'Bill as usual',
				'html'  => 'Every sale reduces stock by batch. Items that drop below minimum appear in the stock alert.',
				'meta'  => 'Live with every bill',
			),
			array(
				'title' => 'Order from the short book',
				'html'  => 'Open the short book grouped by supplier.[rx_plan feat="feat_purchase_order" before=" On " after=", reorder processing turns it into purchase orders."]',
				'meta'  => 'Grouped by supplier',
			),
			array(
				'title' => 'Count and adjust',
				'html'  => 'Do a quick physical count of fast movers and record any difference as a stock adjustment.',
				'meta'  => 'Every change on record',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'Stock tools',
			'title'   => 'Numbers you can act on',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-box',
				'title' => 'Min and max stock',
				'html'  => 'Set reorder and maximum levels for each item.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-alert',
				'title' => 'Stock alert and short book',
				'html'  => 'Items below minimum, ready to order, grouped by supplier.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-truck',
				'title' => 'Reorder processing',
				'html'  => 'Turn the short book into an order without retyping it.',
				'feat'  => 'feat_reorder_processing',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Purchase orders',
				'html'  => 'Make a proper purchase order for the distributor instead of a late-night phone list.',
				'feat'  => 'feat_purchase_order',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-chart',
				'title' => 'Non-moving and top-selling items',
				'html'  => 'See what’s blocking your money and what deserves more shelf space.',
				'feat'  => 'feat_non_moving_item',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-register',
				'title' => 'Stock adjustment',
				'html'  => 'Record breakage, write-offs and count differences, so stock stays true.',
				'feat'  => 'feat_stock_adjustment',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-scan',
				'title' => 'Barcode labels',
				'html'  => 'Print barcode labels for stock that has none.',
				'feat'  => 'feat_label_print',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-shield',
				'title' => 'Activity report',
				'html'  => 'See who changed what, so stock differences can be traced.',
				'feat'  => 'feat_activity_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-stores',
				'title' => 'Transfers between stores',
				'html'  => 'Move stock between linked outlets and see central reports across stores.',
				'feat'  => 'feat_central_admin',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'A simple rule',
			'title'   => 'How to set min and max without overthinking it',
			'intro'   => 'A rule of thumb used by many stores. Start here, then adjust as you learn each item.',
		),
		'guide'           => array(
			array(
				'title' => 'Start with your top 100 items',
				'html'  => 'They’re most of your sales. Set levels for them first; the long tail can wait.',
			),
			array(
				'title' => 'Minimum: sales until the next delivery, plus a little',
				'html'  => 'If you sell 6 strips a day and the distributor delivers in 2 days, a minimum of about 15 strips gives a small cushion.',
			),
			array(
				'title' => 'Maximum: minimum plus one order’s worth',
				'html'  => 'Add what you’d normally order in one go. Going above that just ties up money.',
			),
			array(
				'title' => 'Keep slow items lean',
				'html'  => 'For items that sell a few strips a month, keep a low maximum and check the non-moving items report before reordering.',
			),
			array(
				'title' => 'Review after a month',
				'html'  => 'Look at what ran out and what piled up, and adjust those items’ levels.',
			),
		),
		'guide_note'      => 'Illustrative numbers. Your delivery days and sales decide the right levels.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring this list to a 30-minute demo and see stock behave the way your shelf does.',
		),
		'proof'           => array(
			array( 'text' => 'Set a minimum on an item and watch it appear in the stock alert' ),
			array( 'text' => 'Open the short book grouped by supplier' ),
			array( 'text' => 'Turn the short book into a purchase order[rx_plan feat="feat_purchase_order" before=" (" after=")"]' ),
			array( 'text' => 'Open the non-moving items report' ),
			array( 'text' => 'Record a stock adjustment and find it in the activity report' ),
			array( 'text' => 'Print a barcode label for an item' ),
		),

		'related'         => array( 'billing', 'expiry', 'purchase' ),
		'related_title'   => '',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'How does reordering work?',
				'a_html'    => 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book.[rx_plan feat="feat_purchase_order" before=" On " after=" you can turn the short book into purchase orders."]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see which items aren’t selling?',
				'a_html'    => 'Yes. The non-moving items report shows stock that isn’t selling, and the top-selling items report shows what is.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How do I fix stock that doesn’t match the shelf?',
				'a_html'    => 'Record a stock adjustment. It keeps the correction on record instead of overwriting the number.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does stock update as soon as I bill?',
				'a_html'    => 'Yes. Each sale reduces stock by batch, and each saved purchase bill adds stock straight away.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I move stock between my stores?',
				'a_html'    => 'Yes, on Enterprise: central administration with linked outlets, stock transfers between stores, and central reports.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I print barcode labels?',
				'a_html'    => 'Yes. Print barcode labels for stock that doesn’t have one.[rx_plan feat="feat_label_print" before=" Label printing is on " after="."]',
				'confirmed' => true,
			),
		),
	),
);
