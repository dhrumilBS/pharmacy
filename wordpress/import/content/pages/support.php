<?php
/**
 * Import data: the Support page (PHP_Site support.php).
 *
 * Fields: group_rx_support (templates/support.php). The support phone link
 * and the help centre link come from Site_Settings through [rx_tel] and
 * [rx_link setting="help_url"]; the Start free / Log in buttons and the topic
 * cards link to the app and help centre settings in the template. Text that
 * support.php writes literally (plan names included) stays literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'support.php',
	'key'      => 'support',
	'title'    => 'Support',
	'slug'     => 'support',
	'template' => 'templates/support.php',
	'seo'      => array(
		'desc'    => 'Get help with our pharmacy software: phone support, the help centre, in-app walkthroughs and a getting-started guide for new stores.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'           => array(
			'crumb'   => 'Support',
			'parent'  => 'Company',
			'eyebrow' => 'Support',
			'h1'      => 'Help when the counter is busy',
			'lead'    => 'Call us, open a ticket, or follow the step-by-step guides built into the app.',
		),

		// Support channels.
		'channels'       => array(
			array(
				'icon'  => 'i-headset',
				'title' => 'Phone support',
				'html'  => 'Call [rx_tel key="support"]. The same number is shown at the top of the app.',
			),
			array(
				'icon'  => 'i-chat',
				'title' => 'Help centre',
				'html'  => 'Raise and track a ticket in the [rx_link setting="help_url"]help centre[/rx_link].',
			),
			array(
				'icon'  => 'i-play',
				'title' => 'In-app walkthroughs',
				'html'  => 'Step-by-step guides for sales, purchase, item master and account master, inside the app.',
			),
			array(
				'icon'  => 'i-screen',
				'title' => 'Keyboard shortcuts',
				'html'  => 'The shortcuts panel shows the keys for every screen, for example F2 to search and Ctrl+S to save.',
			),
		),
		'note_html'      => 'Support hours and languages: [confirm before publishing]. Priority support is included on the Enterprise plan.',
		'note_confirmed' => false,

		// Getting started (#getting-started).
		'start_head'     => array(
			'eyebrow' => 'New here?',
			'title'   => 'Get your store ready in four steps',
			'intro'   => 'Most of the setup is done once: your items, opening stock and suppliers. After that, billing is search, pick, save.',
		),
		'cta_free'       => 'Start free',
		'cta_login'      => 'Log in',
		'start_steps'    => array(
			array(
				'title' => 'Create your store account',
				'html'  => 'Sign up with your store name, address and email, and verify with an OTP.',
			),
			array(
				'title' => 'Add your items and opening stock',
				'html'  => 'Use Initialization → Add Initial Data to import a CSV. Download the sample sheet, match your columns, and fix any rows that are flagged.',
			),
			array(
				'title' => 'Add suppliers and users',
				'html'  => 'Add distributors in Account Master, then add staff in Users and give each a role.',
			),
			array(
				'title' => 'Bill your first customer',
				'html'  => 'Open Sale, search an item (F2), let the batch fill in, choose the payment method and save &amp; print (Ctrl+S).',
			),
		),

		// Popular topics (each card links to the help centre).
		'topics_head'    => array(
			'title' => 'Popular topics',
			'intro' => 'Detailed articles live in the help centre.',
		),
		'topics'         => array(
			array(
				'title' => 'Importing items & stock (CSV)',
				'text'  => 'Column mapping, sample sheet, fixing rejected rows.',
			),
			array(
				'title' => 'Purchase entry & OCR import',
				'text'  => 'Distributor bills, scheme and free quantity, credits.',
			),
			array(
				'title' => 'Printing & barcode labels',
				'text'  => 'Print settings, thermal and A4/A5 formats, label print.',
			),
			array(
				'title' => 'GST & Tally reports',
				'text'  => 'GST report, bill-wise tax report, Tally XML (Enterprise).',
			),
			array(
				'title' => 'Users, roles & security',
				'text'  => 'Privileges, two-step login, IP whitelisting.',
			),
			array(
				'title' => 'Plans & subscription',
				'text'  => 'Upgrades, renewals, message and OCR credits.',
			),
		),
	),
);
