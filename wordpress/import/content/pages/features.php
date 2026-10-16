<?php
/**
 * Import data: the Features hub (/features/), parent of the eight feature
 * pages.
 *
 * PHP_Site source: features/index.php. Fields: group_rx_features_hub.
 *
 * Module labels, blurbs and icons are not stored here: the template reads
 * them from the Features menu (rx_feature_nav()), as the PHP page reads
 * NAV_FEATURES. Live values are shortcodes: the free plan name from
 * pricing_split() is [rx_free_plan] (its fallback is the PHP page's
 * no-free-plan answer), feature_plan_label() is the points' `feat` or
 * [rx_plan], and url() links are [rx_link]. The plans table itself comes
 * live from the pricing API in the template.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/index.php',
	'key'      => 'features',
	'title'    => 'Features',
	'slug'     => 'features',
	'template' => 'templates/features-hub.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy software features: billing, expiry, stock, purchase, GST and reports %%sep%% %%sitename%%',
		'desc'    => 'Every part of a medical store in one browser-based system: counter billing with FEFO batches, expiry and stock control, distributor bill import, GST and accounts, reports, customer retention and staff security.',
		'noindex' => false,
	),
	'group'    => 'features_hub',
	'fields'   => array(
		'hero'                => array(
			'crumb'   => 'Features',
			'parent'  => '',
			'eyebrow' => 'Features',
			'h1'      => 'Every part of the store, from the counter to the books',
			'lead'    => 'Billing, expiry, stock and distributor bills for the counter. Reports, GST, regular patients and staff access for the back office. One browser-based system, built for how a medical store actually runs.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'            => 'Book a free demo',
		'cta_free'            => 'Start free',
		'assure'              => array(
			// Shown only when there is a free plan (an empty line is skipped).
			array( 'html' => '[rx_free_plan field="name" after=" plan free forever"]' ),
			array( 'html' => 'Runs in your web browser' ),
			array( 'html' => 'Plans named on every feature' ),
		),

		'index_head'          => array(
			'eyebrow' => 'Start with your problem',
			'title'   => 'What’s costing you most right now?',
			'intro'   => 'Pick the line that sounds like your store. Each one opens the page that deals with it.',
		),
		'index'               => array(
			array(
				'pain'     => 'Customers walk out while staff search for a medicine',
				'page_key' => 'billing',
			),
			array(
				'pain'     => 'Strips expire behind newer stock',
				'page_key' => 'expiry',
			),
			array(
				'pain'     => 'You run out of fast movers, and overstock slow ones',
				'page_key' => 'inventory',
			),
			array(
				'pain'     => 'Distributor bills get typed in after closing',
				'page_key' => 'purchase',
			),
			array(
				'pain'     => 'You don’t know how the day went unless you’re there',
				'page_key' => 'reports',
			),
			array(
				'pain'     => 'Month-end GST takes a whole weekend',
				'page_key' => 'gst',
			),
			array(
				'pain'     => 'Regulars buy their refills somewhere else',
				'page_key' => 'retention',
			),
			array(
				'pain'     => 'The whole counter shares one login',
				'page_key' => 'security',
			),
		),

		// One per Features menu group; the group name is the section heading.
		'group_intros'        => array(
			array(
				'group'   => 'Run the counter',
				'eyebrow' => 'At the counter',
				'intro'   => 'What happens between the shutter going up and the last bill of the night.',
			),
			array(
				'group'   => 'Run the business',
				'eyebrow' => 'In the back office',
				'intro'   => 'What the owner and the accountant look at once the counter is quiet.',
			),
		),
		'points'              => array(
			array(
				'page_key' => 'billing',
				'items'    => array(
					array(
						'text' => 'Brand and generic name search',
						'feat' => '',
						'plan' => 'Every plan',
					),
					array(
						'text' => 'Nearest-expiry batch picked first (FEFO)',
						'feat' => '',
						'plan' => 'Every plan',
					),
					array(
						'text' => 'Cash, card, UPI or credit',
						'feat' => '',
						'plan' => 'Every plan',
					),
					array(
						'text' => 'Bills on WhatsApp, SMS or email',
						'feat' => 'feat_bill_sharing',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'expiry',
				'items'    => array(
					array(
						'text' => 'Stock expiry report, item-wise or supplier-wise',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Setting to stop expired sales',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Purchase returns and credit notes',
						'feat' => 'feat_purchase_credit_note',
						'plan' => '',
					),
					array(
						'text' => 'Non-moving items report',
						'feat' => 'feat_non_moving_item',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'inventory',
				'items'    => array(
					array(
						'text' => 'Min and max stock with a stock alert',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Short book of what to order',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Reorder processing and purchase orders',
						'feat' => 'feat_reorder_processing',
						'plan' => '',
					),
					array(
						'text' => 'Recorded stock adjustments',
						'feat' => 'feat_stock_adjustment',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'purchase',
				'items'    => array(
					array(
						'text' => 'CSV import with column mapping',
						'feat' => 'feat_csv_import',
						'plan' => '',
					),
					array(
						'text' => 'OCR import of printed bills',
						'feat' => '',
						'plan' => 'Uses OCR credits',
					),
					array(
						'text' => 'Scheme, free quantity, PTR and margin',
						'feat' => '',
						'plan' => 'Every plan',
					),
					array(
						'text' => 'Supplier ledger and dues',
						'feat' => 'feat_supplier_due_report',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'reports',
				'items'    => array(
					array(
						'text' => 'Executive dashboard',
						'feat' => 'feat_exec_dashboard',
						'plan' => '',
					),
					array(
						'text' => 'Margin, top-selling and non-moving reports',
						'feat' => 'feat_margin_report',
						'plan' => '',
					),
					array(
						'text' => 'The day’s report on WhatsApp',
						'feat' => 'feat_daily_alerts',
						'plan' => '',
					),
					array(
						'text' => 'Collections by staff member',
						'feat' => '',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'gst',
				'items'    => array(
					array(
						'text' => 'HSN and GST rate on every line',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'GST report and bill-wise tax report',
						'feat' => 'feat_gst_report',
						'plan' => '',
					),
					array(
						'text' => 'Tally XML export',
						'feat' => 'feat_tally_export',
						'plan' => '',
					),
					array(
						'text' => 'Expense management',
						'feat' => 'feat_expense_mgmt',
						'plan' => '',
					),
				),
			),
			array(
				'page_key' => 'retention',
				'items'    => array(
					array(
						'text' => 'Medicine reminders',
						'feat' => 'feat_medicine_reminder',
						'plan' => '',
					),
					array(
						'text' => 'Loyalty cards',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Online orders for pick-up or delivery',
						'feat' => '',
						'plan' => '',
					),
					array(
						'text' => 'Patient history and dues on the bill',
						'feat' => '',
						'plan' => 'Every plan',
					),
				),
			),
			array(
				'page_key' => 'security',
				'items'    => array(
					array(
						'text' => 'Own login for every user',
						'feat' => 'feat_user_limit',
						'plan' => '',
					),
					array(
						'text' => 'Role management',
						'feat' => 'feat_role_management',
						'plan' => '',
					),
					array(
						'text' => 'Two-step OTP login and IP whitelisting',
						'feat' => 'feat_2fa',
						'plan' => '',
					),
					array(
						'text' => 'Activity report',
						'feat' => 'feat_activity_report',
						'plan' => '',
					),
				),
			),
		),

		'hospital_head'       => array(
			'eyebrow' => 'Also built in',
			'title'   => 'For hospital pharmacies and chains',
			'intro'   => 'Built by a team that also makes hospital software, so it handles patients, not just customers.',
		),
		'hospital_points'     => array(
			array( 'html' => '<b>IPD and discharge billing</b>, with patient deposits, dues and ledgers.' ),
			array( 'html' => '<b>Payer and TPA companies</b> on the bill.' ),
			array( 'html' => '<b>Prescriptions from the hospital EMR</b> arrive at the counter as tokens on a token board.' ),
			array( 'html' => '<b>ABDM integrated:</b> create or link a patient’s ABHA with their consent.' ),
			array( 'html' => '<b>Several stores?</b> Central administration with linked outlets, stock transfers and central reports[rx_plan feat="feat_central_admin" before=" (" after=")"].' ),
		),
		'hospital_links'      => array(
			array(
				'label'    => 'Hospital pharmacy',
				'link_key' => 'hospital',
			),
			array(
				'label'    => 'Pharmacy chains',
				'link_key' => 'chain',
			),
		),
		'hospital_screen'     => 'v2-07-hospital',
		'hospital_screen_alt' => 'Sample screen: hospital pharmacy token board with an EMR prescription',

		'plans_head'          => array(
			'eyebrow' => 'Plans',
			'title'   => 'The same software on every plan, with more as you grow',
			'intro'   => 'Paid plans add unlimited bills, more users and the features named on each page. Prices exclude 18% GST.',
		),
		'plans_foot_html'     => 'Full feature list by plan on the [rx_link key="pricing" hash="compare"]pricing page[/rx_link].',

		'faq_title'           => 'Before you look closer',
		'faq'                 => array(
			array(
				'q'         => 'Do I need all of this to start?',
				'a_html'    => 'No. You can start with billing, stock and purchase import, and use reports, GST and reminders as you settle in. Everything is in the same software, so there is nothing extra to install later.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Which plan includes what?',
				'a_html'    => 'Each page names the plan a feature starts on, and the [rx_link key="pricing" hash="compare"]plan comparison[/rx_link] lists every feature by plan.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Is it the same software for a single store and a chain?',
				'a_html'    => 'Yes. Chains and hospital pharmacies use the same software, with multi-store management on Enterprise.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I try it before paying?',
				'a_html'    => '[rx_free_plan field="name" before="Yes. The " after=" plan is free forever, so you can set up your items and try billing before choosing a paid plan." fallback="Yes. Book a demo and we’ll show it with your own items."]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it work on my computer?',
				'a_html'    => 'It runs in a web browser on the computer you already use at the counter, with USB barcode readers and thermal or A4/A5 printing.',
				'confirmed' => true,
			),
		),
	),
);
