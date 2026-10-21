<?php
/**
 * Content of the version 2 home page (templates/home-v2.php).
 *
 * Planned from healthray.com/pharmacy-management-system and its pharmacy articles (who it is for, a feature grid, a retail-vs-hospital
 * table, how it is delivered and supported, a FAQ), kept to what this product does: every line is a verified fact from
 * PHARMACY-ICP-AND-UX-STRATEGY.md §0. Not carried over, because it is not verified for this app: AI and OCR-of-prescription claims,
 * drug-interaction alerts, e-way bills, Schedule H/H1/X registers, delivery and cold chain, on-premise hosting, HIPAA or NABH badges,
 * customer counts, ratings, testimonials and 24/7 support. No prices on the home page (owner decision): plans are linked.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * The home v2 content.
 *
 * @return array<string, mixed>
 */
function rx_home2_data(): array {
	return array(
		'seo_title' => 'Pharmacy software: billing, expiry, stock and GST on one screen %%sep%% %%sitename%%',
		'seo_desc'  => 'Browser-based pharmacy software for medical stores, chains and hospital pharmacies: earliest-expiry billing, expiry reports, purchase import, GST records and a free plan to start on.',

		'hero'      => array(
			'h1'      => 'The whole counter on one screen: billing, expiry, stock and GST',
			'lead'    => '[rx_brand] picks the right batch on every bill, imports distributor bills, and keeps stock and GST records right as you sell. For medical stores, chains and hospital pharmacies.',
			'bullets' => array(
				'The batch that expires first is picked on every bill',
				'Bring your items and opening stock from a CSV',
				'Free to start, with walkthroughs inside the app',
			),
			'screen'  => 'v2-01-counter-billing',
			'alt'     => 'The counter billing screen with dummy data',
		),

		'audience'  => array(
			'title' => 'Built for the way you run your pharmacy',
			'intro' => 'Pick the one that sounds like you. Each page shows the same software set up for that job.',
			'items' => array(
				array( 'i-store', 'Retail medical stores', 'One store with a few counters: fast bills, expiry watched, GST records ready.', 'retail' ),
				array( 'i-stores', 'Pharmacy chains', 'Many stores on the same rules, with central admin, transfers and reports across them.', 'chain' ),
				array( 'i-hospital', 'Hospital pharmacies', 'Wards and an OPD counter: EMR prescriptions as tokens, IPD billing, payers and TPA companies.', 'hospital' ),
				array( 'i-sun', 'New pharmacies', 'Start on the free plan on day one, load your items from a CSV and bill your first customer.', 'new-pharmacy' ),
			),
		),

		'features'  => array(
			'title' => 'What it does, from the first bill to the month-end',
			'intro' => 'Twelve things the software handles, each with a page that goes into detail.',
			'items' => array(
				array( 'i-receipt', 'Billing at the counter', 'The batch that expires first is picked for you. Search by brand or generic name, or scan a barcode, and take cash, card, UPI or credit.', 'billing', '' ),
				array( 'i-alert', 'Expiry protection', 'A stock expiry report, by item or by supplier, shows what to return while the distributor will still take it back.', 'expiry', '' ),
				array( 'i-upload', 'Purchase entry', 'Import distributor bills from a CSV with column mapping. Scheme and free quantity are recorded, and PTR and margin are worked out.', 'purchase', '' ),
				array( 'i-box', 'Stock and reorder', 'Minimum and maximum levels, stock alerts and a short book, so reordering starts before an item runs out.', 'inventory', '' ),
				array( 'i-rupee', 'GST-ready records', 'HSN and GST come from the item master, with GST and tax reports for your accountant.', 'gst', '' ),
				array( 'i-chart', 'Reports and dashboard', 'Collection, operator-wise, margin and top-selling reports, and an executive dashboard.', 'reports', 'feat_exec_dashboard' ),
				array( 'i-chat', 'Customer retention', 'Medicine reminders, loyalty cards and online orders for pick-up or delivery.', 'retention', 'feat_medicine_reminder' ),
				array( 'i-lock', 'Security and roles', 'Roles and privileges for each person, with two-step login, IP whitelisting and an activity report on higher plans.', 'security', 'feat_role_management' ),
				array( 'i-stores', 'Many stores, one admin', 'Central admin with linked outlets, stock transfer between stores and reports across them.', 'chain', 'feat_central_admin' ),
				array( 'i-hospital', 'Hospital pharmacy', 'IPD and discharge billing, payers and TPA companies, deposits, departments, kits and packages, and ABDM and ABHA.', 'hospital', 'feat_mrd' ),
				array( 'i-file', 'Your data comes with you', 'Add initial data from a CSV of items and opening stock, with a sample sheet and errors shown line by line.', 'switch', '' ),
				array( 'i-headset', 'Help built in', 'Walkthroughs inside the app for sales, purchase, the item master and the account master, plus phone support and a help centre.', 'support', '' ),
			),
		),

		'compare'   => array(
			'title' => 'Which setup is yours?',
			'intro' => 'The same software, set up for three different jobs.',
			'cols'  => array( 'Retail medical store', 'Pharmacy chain', 'Hospital pharmacy' ),
			'rows'  => array(
				array( 'Orders come from', 'Walk-in customers, prescriptions in hand and refills.', 'The same, at every store.', 'Doctors’ EMR prescriptions as tokens, and ward orders.' ),
				array( 'Billing', 'Cash, card, UPI or credit at the counter, with GST details from the item master.', 'The same bill and the same rules at every store.', 'IPD and discharge billing, payers and TPA companies, deposits and dues.' ),
				array( 'Stock moves', 'Distributor purchases in, counter sales out, earliest expiry first.', 'Stock transfer between stores, with reports across them.', 'Issued to departments, kits and packages, from one pharmacy.' ),
				array( 'Starts on', 'The [rx_free_plan field="name" fallback="free"] plan, then a paid plan when you outgrow it.', '[rx_plan feat="feat_central_admin" fallback="The Enterprise plan"], quoted for each chain.', '[rx_plan feat="feat_mrd" fallback="The Enterprise plan"], quoted for each hospital.' ),
			),
			'links' => array( 'retail', 'chain', 'hospital' ),
		),

		'start'     => array(
			'title' => 'Start the same day',
			'steps' => array(
				array( 'Sign up free', 'Create an account with an email OTP. The free plan has no trial period to run out.' ),
				array( 'Add your initial data', 'Upload your items and opening stock from a CSV, with a sample sheet to follow.' ),
				array( 'Follow the walkthroughs', 'Short walkthroughs inside the app cover sales, purchase, the item master and the account master.' ),
				array( 'Bill your first customer', 'Search the item, pick the batch, take payment and print.' ),
			),
		),

		'holds'     => array(
			'title' => 'How it is delivered and looked after',
			'cols'  => array(
				array( 'Runs in your browser', array( 'Nothing to install: sign in from a browser.', 'Print bills, and search with a USB barcode reader.', 'One account can cover one store or many, on the right plan.' ) ),
				array( 'Brings your data', array( 'CSV import for items and opening stock, with a sample sheet.', 'Distributor bills by CSV with column mapping, or by OCR, which uses credits.', 'Errors are shown line by line.' ) ),
				array( 'Protects your records', array( 'Roles and privileges for each person.', 'Two-step login and IP whitelisting on higher plans.', 'An activity report, on the plans that include it.' ) ),
				array( 'Help when you need it', array( 'Walkthroughs inside the app.', 'A phone number and a help centre.', 'Sales and support phone numbers on the contact page.' ) ),
			),
		),

		'faq'       => array(
			array( 'What is pharmacy software?', 'It is one place to run the day at a medical store: billing, batch and expiry, purchase, stock, GST records and reports. It replaces bill books, registers and separate spreadsheets.' ),
			array( 'Is it the same as medical store or chemist software?', 'Yes. These are different names for the same thing. [rx_brand] is built for stores, chains and hospital pharmacies.' ),
			array( 'Does it handle GST billing?', 'HSN and GST come from the item master and flow into the bill. There is a GST report and a bill-wise and date-wise tax report, and accounts can be exported to Tally on the plans that include it.' ),
			array( 'How does it deal with Schedule H and H1 medicines?', 'Items can be tagged as Schedule H or H1 so they are easy to spot at the counter. The software helps with the records; the rules that apply to your store are for you and your adviser to check.' ),
			array( 'Does it work for a hospital pharmacy?', 'Yes. There is IPD and discharge billing, payer and TPA companies, deposits, departments, kits and packages, and EMR prescriptions arrive as tokens. See the hospital pharmacy page for the detail.' ),
			array( 'Is there ABDM and ABHA support?', 'ABDM and ABHA integration is part of the hospital pharmacy features.' ),
			array( 'Is there a free trial or a free plan?', 'There is no trial. The free plan is free to use, with limits, and you move to a paid plan only if you need more. The pricing page shows the current plans.' ),
			array( 'Can I bring my items and stock from my old system?', 'Yes. Add initial data takes a CSV of items and opening stock, with a sample sheet, and shows errors line by line. How you export from the old system depends on that system.' ),
		),
	);
}
