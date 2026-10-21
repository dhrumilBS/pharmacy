<?php
/**
 * Import data: the six WordPress menus (header mega menu, four footer
 * columns and the legal links).
 *
 * Read by import.php and passed one by one to rx_upsert_menu(); the shape of
 * each menu is documented at the top of lib/upsert.php.
 *
 * Sources in the PHP_Site (copied exactly, curly quotes and dashes included):
 * - Primary: partials/header.php with NAV_FEATURES, NAV_SOLUTIONS and
 *   NAV_RESOURCES from partials/config.php (labels, descriptions, icons).
 * - Features, Solutions, Resources, Company, Legal: partials/footer.php.
 *   The menu name is the footer column heading.
 *
 * Links:
 * - 'page' => '{key}' links a page by its `_rx_key` (the PHP_Site url() key).
 * - 'url' => '#rx:{key}' is resolved when the menu is printed (inc/nav.php):
 *   app_login, app_signup and help_url read Site_Settings; any other key goes
 *   through rx_url(), so pages not built yet (retail, chain, hospital,
 *   new-pharmacy, calculator, checklist) keep pointing at today's home page
 *   anchors and switch over once a page with that key exists.
 * - 'url' => '#' is a group item: a mega menu button or a column heading.
 *
 * Item ids are fixed so menu item IDs survive label edits. Page links use
 * the page key, '#rx:' links use the key, groups use the slug of the label.
 * The Features top item has id 'features', the Features hub page key, which
 * today's header also marks active on the hub.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(

	/*
	 * Header mega menu (partials/header.php).
	 */
	array(
		'name'     => 'Primary',
		'location' => 'primary',
		'items'    => array(
			array(
				'id'       => 'features',
				'title'    => 'Features',
				'url'      => '#',
				'children' => array(
					array(
						'id'       => 'run-the-counter',
						'title'    => 'Run the counter',
						'url'      => '#',
						'children' => array(
							array(
								'title'       => 'Billing & counter',
								'page'        => 'billing',
								'icon'        => 'i-receipt',
								'description' => 'Bill in seconds, nearest expiry first',
							),
							array(
								'title'       => 'Expiry & batch',
								'page'        => 'expiry',
								'icon'        => 'i-alert',
								'description' => 'Catch expiry before it’s a loss',
							),
							array(
								'title'       => 'Stock & reorder',
								'page'        => 'inventory',
								'icon'        => 'i-box',
								'description' => 'Short book to purchase order',
							),
							array(
								'title'       => 'Purchase & suppliers',
								'page'        => 'purchase',
								'icon'        => 'i-truck',
								'description' => 'Import distributor bills by CSV or OCR',
							),
						),
					),
					array(
						'id'       => 'run-the-business',
						'title'    => 'Run the business',
						'url'      => '#',
						'children' => array(
							array(
								'title'       => 'Reports & dashboard',
								'page'        => 'reports',
								'icon'        => 'i-chart',
								'description' => 'See the day on WhatsApp',
							),
							array(
								'title'       => 'GST & accounts',
								'page'        => 'gst',
								'icon'        => 'i-rupee',
								'description' => 'GST report, Tally export',
							),
							array(
								'title'       => 'Customer retention',
								'page'        => 'retention',
								'icon'        => 'i-users',
								'description' => 'Reminders, loyalty, online orders',
							),
							array(
								'title'       => 'Security & roles',
								'page'        => 'security',
								'icon'        => 'i-shield',
								'description' => '2-step login, IP whitelisting',
							),
						),
					),
				),
			),
			array(
				'id'       => 'solutions',
				'title'    => 'Solutions',
				'url'      => '#',
				'children' => array(
					array(
						'id'          => 'retail',
						'title'       => 'Retail medical store',
						'url'         => '#rx:retail',
						'icon'        => 'i-store',
						'description' => 'One store, 1–3 counters',
					),
					array(
						'id'          => 'chain',
						'title'       => 'Pharmacy chain',
						'url'         => '#rx:chain',
						'icon'        => 'i-stores',
						'description' => 'Many stores, one set of rules',
					),
					array(
						'id'          => 'hospital',
						'title'       => 'Hospital pharmacy',
						'url'         => '#rx:hospital',
						'icon'        => 'i-hospital',
						'description' => 'IPD, TPA, EMR prescriptions, ABDM',
					),
					array(
						'id'          => 'new-pharmacy',
						'title'       => 'Opening a new pharmacy',
						'url'         => '#rx:new-pharmacy',
						'icon'        => 'i-sun',
						'description' => 'Start free from day one',
					),
				),
			),
			array(
				'title' => 'Pricing',
				'page'  => 'pricing',
			),
			array(
				'title' => 'Switch',
				'page'  => 'switch',
			),
			array(
				'id'       => 'resources',
				'title'    => 'Resources',
				'url'      => '#',
				'children' => array(
					array(
						'id'          => 'calculator',
						'title'       => 'Expiry loss calculator',
						'url'         => '#rx:calculator',
						'icon'        => 'i-chart',
						'description' => 'Put a number on expiry loss',
					),
					array(
						'id'          => 'checklist',
						'title'       => 'Buyer’s checklist',
						'url'         => '#rx:checklist',
						'icon'        => 'i-file',
						'description' => '8 questions to ask any vendor',
					),
					array(
						'title'       => 'Blog',
						'page'        => 'blog',
						'icon'        => 'i-register',
						'description' => 'Practical reading for the counter',
					),
					array(
						'title'       => 'FAQ',
						'page'        => 'faq',
						'icon'        => 'i-chat',
						'description' => 'Straight answers',
					),
					array(
						'title'       => 'Help & support',
						'page'        => 'support',
						'icon'        => 'i-headset',
						'description' => 'Phone, help centre, walkthroughs',
					),
				),
			),
		),
	),

	/*
	 * Footer column "Features": every NAV_FEATURES item, both groups in order.
	 */
	array(
		'name'     => 'Features',
		'location' => 'footer_features',
		'items'    => array(
			array(
				'title' => 'Billing & counter',
				'page'  => 'billing',
			),
			array(
				'title' => 'Expiry & batch',
				'page'  => 'expiry',
			),
			array(
				'title' => 'Stock & reorder',
				'page'  => 'inventory',
			),
			array(
				'title' => 'Purchase & suppliers',
				'page'  => 'purchase',
			),
			array(
				'title' => 'Reports & dashboard',
				'page'  => 'reports',
			),
			array(
				'title' => 'GST & accounts',
				'page'  => 'gst',
			),
			array(
				'title' => 'Customer retention',
				'page'  => 'retention',
			),
			array(
				'title' => 'Security & roles',
				'page'  => 'security',
			),
		),
	),

	/*
	 * Footer column "Solutions": NAV_SOLUTIONS, then Pricing and Switch.
	 */
	array(
		'name'     => 'Solutions',
		'location' => 'footer_solutions',
		'items'    => array(
			array(
				'id'    => 'retail',
				'title' => 'Retail medical store',
				'url'   => '#rx:retail',
			),
			array(
				'id'    => 'chain',
				'title' => 'Pharmacy chain',
				'url'   => '#rx:chain',
			),
			array(
				'id'    => 'hospital',
				'title' => 'Hospital pharmacy',
				'url'   => '#rx:hospital',
			),
			array(
				'id'    => 'new-pharmacy',
				'title' => 'Opening a new pharmacy',
				'url'   => '#rx:new-pharmacy',
			),
			array(
				'title' => 'Pricing',
				'page'  => 'pricing',
			),
			array(
				'title' => 'Switch from your current software',
				'page'  => 'switch',
			),
		),
	),

	/*
	 * Footer column "Resources": NAV_RESOURCES without Help & support, then
	 * the help centre (Site_Settings help_url).
	 */
	array(
		'name'     => 'Resources',
		'location' => 'footer_resources',
		'items'    => array(
			array(
				'id'    => 'calculator',
				'title' => 'Expiry loss calculator',
				'url'   => '#rx:calculator',
			),
			array(
				'id'    => 'checklist',
				'title' => 'Buyer’s checklist',
				'url'   => '#rx:checklist',
			),
			array(
				'title' => 'Blog',
				'page'  => 'blog',
			),
			array(
				'title' => 'FAQ',
				'page'  => 'faq',
			),
			array(
				'id'    => 'help_url',
				'title' => 'Help centre',
				'url'   => '#rx:help_url',
			),
		),
	),

	/*
	 * Footer column "Company": company pages, then the app links
	 * (Site_Settings app_login and app_signup).
	 */
	array(
		'name'     => 'Company',
		'location' => 'footer_company',
		'items'    => array(
			array(
				'title' => 'About',
				'page'  => 'about',
			),
			array(
				'title' => 'Contact',
				'page'  => 'contact',
			),
			array(
				'title' => 'Support',
				'page'  => 'support',
			),
			array(
				'title' => 'Book a demo',
				'page'  => 'demo',
			),
			array(
				'id'    => 'app_login',
				'title' => 'Log in',
				'url'   => '#rx:app_login',
			),
			array(
				'id'    => 'app_signup',
				'title' => 'Sign up free',
				'url'   => '#rx:app_signup',
			),
		),
	),

	/*
	 * Legal links in the footer bottom row.
	 */
	array(
		'name'     => 'Legal',
		'location' => 'legal',
		'items'    => array(
			array(
				'title' => 'Privacy',
				'page'  => 'privacy',
			),
			array(
				'title' => 'Terms',
				'page'  => 'terms',
			),
			array(
				'title' => 'Refund policy',
				'page'  => 'refund',
			),
			array(
				'title' => 'Data & security',
				'page'  => 'data-security',
			),
			array(
				'title' => 'Cookies',
				'page'  => 'cookies',
			),
			// Opens the cookie preferences of the TrustLayer Consent plugin (it handles this link in the browser).
			array(
				'title' => 'Cookie settings',
				'id'    => 'cookie-settings',
				'url'   => '#tlc-preferences',
			),
		),
	),
);
