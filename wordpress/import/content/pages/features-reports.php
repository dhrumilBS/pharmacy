<?php
/**
 * Import data: the "Reports & dashboard" feature page (/features/reports/).
 *
 * PHP_Site source: features/reports.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The executive dashboard plan ($dash) and the rows' plans are [rx_plan] /
 * `feat`; url() links are [rx_link]. The worked example keeps today's
 * illustrative numbers as text (1% of ₹8,00,000 = ₹8,000).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/reports.php',
	'key'      => 'reports',
	'title'    => 'Reports & dashboard',
	'slug'     => 'reports',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy reports software: dashboard, margin reports and a daily WhatsApp report %%sep%% %%sitename%%',
		'desc'    => 'Medical store reports without standing at the counter: executive dashboard, 25+ reports including margin, top-selling, non-moving, collection and supplier due reports, and the day’s report on WhatsApp.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Reports & dashboard',
			'parent'  => 'Features',
			'eyebrow' => 'Reports & dashboard',
			'h1'      => 'Know how the store did without standing at the counter',
			'lead'    => 'An executive dashboard, 25+ reports and the day’s report on WhatsApp show sales, collections, margin, slow stock and supplier dues. Check the store from wherever you are, in a web browser.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the reports in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => '25+ reports, export to Excel' ),
			array( 'text' => 'The day’s report on WhatsApp (uses credits)' ),
			array( 'text' => '[rx_plan feat="feat_exec_dashboard" before="Executive dashboard (" after=")"]' ),
		),
		'screen'          => 'v2-02-dashboard',
		'screen_alt'      => 'Pharmacy dashboard with sales, collections, expiry and stock alerts',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Running a store on guesses',
			'intro'   => 'Most owners know the day’s total. Fewer know where the margin went.',
		),
		'scenes'          => array(
			array(
				'time'  => 'Sunday, at a family function',
				'title' => 'How did the store do today?',
				'text'  => 'You call the counter twice for the total and get a rounded number between customers.',
				'cost'  => 'decisions made on guesses, and a day off that isn’t one.',
			),
			array(
				'time'  => 'Month-end',
				'title' => 'Sales are up, the bank balance isn’t',
				'text'  => 'Turnover looks fine, but margin slipped on a few fast movers after a rate change nobody spotted.',
				'cost'  => 'a little on every strip, every day it goes unnoticed.',
			),
			array(
				'time'  => 'Payment week',
				'title' => 'Supplier dues arrive together',
				'text'  => 'Three distributors want payment in the same week, and you only find out when the reps walk in.',
				'cost'  => 'cash-flow stress, or a late payment that strains a good supplier.',
			),
			array(
				'time'  => 'Staff review',
				'title' => 'Who billed what?',
				'text'  => 'Collections and discounts by staff member are a feeling, not a number.',
				'cost'  => 'no fair way to reward good staff or correct mistakes.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where blind spots cost you, and what shows them',
			'intro'   => 'Each blind spot, then the report that shows it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'No view of the day from outside',
				'how'       => 'You only know the numbers if you’re in the store.',
				'plug_html' => '<b>The day’s report on WhatsApp</b> after closing. It uses WhatsApp credits; included credits vary by plan.',
				'feat'      => 'feat_daily_alerts',
				'plan'      => '',
			),
			array(
				'leak'      => 'Margin slipping quietly',
				'how'       => 'A rate change or a big discount eats margin on a fast mover.',
				'plug_html' => '<b>Margin report</b>, so a drop shows up before month-end.',
				'feat'      => 'feat_margin_report',
				'plan'      => '',
			),
			array(
				'leak'      => 'Buying the wrong things',
				'how'       => 'Slow items get reordered; fast movers run short.',
				'plug_html' => '<b>Top-selling and non-moving items reports</b>, side by side.',
				'feat'      => 'feat_top_selling_item',
				'plan'      => '',
			),
			array(
				'leak'      => 'Supplier dues as surprises',
				'how'       => 'Payment dates live in the reps’ heads.',
				'plug_html' => '<b>Supplier due report</b> for every distributor, with their ledger.',
				'feat'      => 'feat_supplier_due_report',
				'plan'      => '',
			),
			array(
				'leak'      => 'A drawer that doesn’t add up',
				'how'       => 'Several people take payments across a shift.',
				'plug_html' => '<b>Collection and operator-wise collection reports</b> by payment method and by staff member.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Too many numbers, no overview',
				'how'       => 'Reports exist, but nobody opens ten of them every morning.',
				'plug_html' => '<b>Executive dashboard</b> with sales, collections, expiry and stock alerts on one screen.',
				'feat'      => 'feat_exec_dashboard',
				'plan'      => '',
			),
			array(
				'leak'      => 'Changes nobody can explain',
				'how'       => 'A figure changed and nobody remembers why.',
				'plug_html' => '<b>Activity report</b> shows who did what, and when.',
				'feat'      => 'feat_activity_report',
				'plan'      => '',
			),
		),
		'leaks_example'   => array(
			'title'        => 'One point of margin',
			'lines'        => array(
				array(
					'label' => 'Monthly sales',
					'value' => '₹8,00,000',
					'op'    => 'auto',
				),
				array(
					'label' => 'Margin that slipped unnoticed',
					'value' => '1%',
					'op'    => 'auto',
				),
			),
			'result_label' => 'Lost in a month',
			'result_value' => '₹8,000',
			'note'         => 'Change the numbers to match your store. A margin report doesn’t stop the slip; it shows it while you can still act.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How reporting works',
			'title'   => 'Bill as usual, and the reports fill themselves',
			'intro'   => 'There’s nothing extra to enter. Every bill, purchase and return you save feeds the reports.',
		),
		'steps'           => array(
			array(
				'title' => 'Every entry counts',
				'html'  => 'Sales, purchases and returns go into the reports as they’re saved, along with who billed and how each bill was paid.',
				'meta'  => 'Nothing to re-enter',
			),
			array(
				'title' => 'Morning: one screen',
				'html'  => 'The executive dashboard shows sales, collections, expiry and stock alerts on one screen[rx_plan feat="feat_exec_dashboard" before=" (" after=")"].',
				'meta'  => 'Executive dashboard',
			),
			array(
				'title' => 'Evening: on WhatsApp',
				'html'  => 'After closing, the day’s report can arrive on WhatsApp, so you see the numbers even when you’re away.',
				'meta'  => 'Uses WhatsApp credits',
			),
			array(
				'title' => 'Month-end: for your CA',
				'html'  => 'Margin, GST, collection and supplier due reports export to Excel for your accountant.',
				'meta'  => 'Export to Excel',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'The reports',
			'title'   => '25+ reports, the ones owners actually open',
			'intro'   => 'Where a report depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-chart',
				'title' => 'Executive dashboard',
				'html'  => 'Sales, collections, expiry and stock alerts on one screen, updated with every bill.',
				'feat'  => 'feat_exec_dashboard',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-receipt',
				'title' => 'Sales, purchase and return reports',
				'html'  => 'Sales, purchases and returns for the dates you choose, ready to export.',
				'feat'  => 'feat_spr_reports',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'Margin report',
				'html'  => 'See the margin on what you sell, so a slip shows up early.',
				'feat'  => 'feat_margin_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-box',
				'title' => 'Top-selling and non-moving items',
				'html'  => 'What deserves shelf space, and what’s blocking your money.',
				'feat'  => 'feat_top_selling_item',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-users',
				'title' => 'Collection and operator-wise collection',
				'html'  => 'The day split by payment method and by staff member.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-truck',
				'title' => 'Supplier due report',
				'html'  => 'What you owe each distributor, from their ledger.',
				'feat'  => 'feat_supplier_due_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-chat',
				'title' => 'The day’s report on WhatsApp',
				'html'  => 'Daily numbers sent to your phone after closing. Uses WhatsApp credits.',
				'feat'  => 'feat_daily_alerts',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Analytics reports',
				'html'  => 'Deeper analysis, with more depth on higher plans.',
				'feat'  => 'feat_analytics_reports',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-stores',
				'title' => 'Central reports across stores',
				'html'  => 'One view of every linked outlet for chains.',
				'feat'  => 'feat_central_reports',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'A simple routine',
			'title'   => 'Ten minutes with your reports, every morning',
			'intro'   => 'Habits that careful owners keep. The reports give you the numbers; the routine turns them into decisions.',
		),
		'guide'           => array(
			array(
				'title' => 'Match yesterday’s collections to the cash',
				'html'  => 'Check cash, card and UPI in the collection report against the drawer and the bank before the day starts.',
			),
			array(
				'title' => 'Look at collections by staff member',
				'html'  => 'Big differences between people on similar shifts are worth a quiet conversation.',
			),
			array(
				'title' => 'Read the short book and the expiry list',
				'html'  => 'Two minutes on what to order and what to push today saves a stock-out and a write-off.',
			),
			array(
				'title' => 'Once a week, check margin on your top 20 items',
				'html'  => 'That’s where a rate change or a discount habit hurts most.',
			),
			array(
				'title' => 'Before payment week, open the supplier due report',
				'html'  => 'Plan payments by date instead of by who calls first.',
			),
			array(
				'title' => 'Send the month to your CA in one go',
				'html'  => 'Export the GST, sales and purchase reports together, from the same month, so the numbers agree.',
			),
		),
		'guide_note'      => 'Report names and depth vary by plan; see the plan comparison.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring this list to a 30-minute demo. We’ll open each report with sample data.',
		),
		'proof'           => array(
			array( 'text' => 'Open the executive dashboard for today' ),
			array( 'text' => 'Export yesterday’s sales report to Excel' ),
			array( 'text' => 'Open the margin report and find a fast-moving item' ),
			array( 'text' => 'Split a day’s collections by staff member' ),
			array( 'text' => 'Open the supplier due report' ),
			array( 'text' => 'See what the day’s report on WhatsApp looks like' ),
		),

		'related'         => array( 'gst', 'retention', 'security' ),
		'related_title'   => 'Also in the back office',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Can I check the store when I’m not there?',
				'a_html'    => 'Yes. It runs in a web browser, so you can log in from anywhere. The day’s report can also arrive on WhatsApp, which uses WhatsApp credits.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How many reports are there?',
				'a_html'    => 'More than 25, including sales, purchase, return, margin, top-selling, non-moving, collection, operator-wise collection, GST and supplier due reports.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I export reports for my accountant?',
				'a_html'    => 'Yes. Reports, item lists and ledgers export to Excel or CSV.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Which plan has the executive dashboard?',
				'a_html'    => '[rx_plan feat="feat_exec_dashboard" before="The executive dashboard is on " after=". "]See the [rx_link key="pricing" hash="compare"]plan comparison[/rx_link].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see collections for each staff member?',
				'a_html'    => 'Yes. The operator-wise collection report splits the day’s collections by the person who billed.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see reports across several stores?',
				'a_html'    => 'Yes, on Enterprise: a central reports dashboard covers every linked outlet.',
				'confirmed' => true,
			),
		),
	),
);
