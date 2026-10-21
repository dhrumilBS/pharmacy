<?php
/**
 * Content of the four solution pages (templates/solution.php), keyed by page key.
 *
 * Data only: the layout is in the template. Every claim here is one of the verified product facts in
 * PHARMACY-ICP-AND-UX-STRATEGY.md §0. Plans and prices are never written out: the shortcodes
 * [rx_cost_text], [rx_free_plan] and [rx_plan feat=""] read them live from the pricing API, and
 * a plan label is shown for a feature through its `feat` key. No customer figures, no compliance guarantees.
 *
 * Each page:
 *   title, seo_title, seo_desc   WordPress title and search snippet (set by import/solutions-setup.php)
 *   crumb                        breadcrumb label
 *   h1, lead                     hero
 *   screen, screen_alt           a product screen from assets/img/screens/
 *   items_title, items_intro, items [ title, text, key (feature page), feat (plan label key) ]
 *   flow_title, flow [ title, text ]
 *   plan_title, plan_html, plan_links [ label, key ]
 *   faq [ q, a_html ]
 *   cta_title, cta_text          closing band
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * The solution pages' content.
 *
 * @return array<string, array<string, mixed>>
 */
function rx_solutions_data(): array {
	return array(

		'retail'       => array(
			'slug'        => 'retail-medical-store',
			'title'       => 'Retail medical store',
			'seo_title'   => 'Pharmacy software for a retail medical store %%sep%% %%sitename%%',
			'seo_desc'    => 'Faster bills, less expiry loss and clean GST records for one store: FEFO billing, expiry reports, purchase import and a free plan to start on.',
			'crumb'       => 'Retail medical store',
			'h1'          => 'Faster bills, less expiry loss and clean GST, for one store',
			'lead'        => 'For the owner who is also at the counter. Pick the right batch, print the bill and see what is about to expire, without a second person keeping a register.',
			'screen'      => 'v2-01-counter-billing',
			'screen_alt'  => 'The counter billing screen with dummy data',
			'items_title' => 'What it does at your counter',
			'items_intro' => 'The jobs of a busy day, in the order they come.',
			'items'       => array(
				array( 'Billing that keeps up with the queue', 'The batch that expires first is picked for you. Search by brand or generic name, or scan a barcode, and take cash, card, UPI or credit.', 'billing', '' ),
				array( 'Expiry before it costs you', 'The stock expiry report, by item or by supplier, shows what to return while the distributor will still take it back.', 'expiry', '' ),
				array( 'Purchase bills in minutes', 'Import a distributor bill from a CSV with your own column mapping. Scheme and free quantity are recorded, and PTR and margin are worked out.', 'purchase', '' ),
				array( 'Stock that matches the shelf', 'Set minimum and maximum levels, get stock alerts and a short book, and see the items that have stopped moving.', 'inventory', '' ),
				array( 'GST-ready records', 'HSN and GST come from the item master. The GST report and the bill-wise and date-wise tax reports give your accountant clean data.', 'gst', '' ),
				array( 'Reports without the spreadsheet', 'Collection and operator-wise reports, margin and top-selling items, all inside the app.', 'reports', '' ),
			),
			'flow_title'  => 'Start the same day',
			'flow'        => array(
				array( 'Sign up free', 'Create an account with an email OTP. The free plan has no trial period.' ),
				array( 'Bring your items', 'Add your initial data from a CSV: items and opening stock, with a sample sheet and line-by-line error messages.' ),
				array( 'Learn in the app', 'Short walkthroughs inside the app cover sales, purchase, the item master and the account master.' ),
				array( 'Move up when you need to', 'When the monthly bill limit or the single user starts to bite, change plan from the pricing page.' ),
			),
			'plan_title'  => 'What it costs',
			'plan_html'   => '<p>[rx_cost_text]</p><p>The pricing page always shows the current plans and their limits.</p>',
			'plan_links'  => array( array( 'See the plans', 'pricing' ), array( 'Switching from other software', 'switch' ) ),
			'faq'         => array(
				array( 'Do I have to install anything?', 'No. It is a browser-based app: you sign in from a browser.' ),
				array( 'Can I bring my item list and stock?', 'Yes. Add initial data takes a CSV of items and opening stock, gives you a sample sheet and reports errors line by line.' ),
				array( 'Is there a free trial?', 'There is no trial. The free plan is free to use, with limits, and the pricing page shows what each paid plan adds.' ),
				array( 'Does it work with a barcode scanner?', 'Yes. You can search on the sale screen with a USB barcode reader.' ),
			),
			'cta_title'   => 'See it with the medicines on your shelf',
			'cta_text'    => 'Book a short demo, or start free and bill your first customer today.',
		),

		'chain'        => array(
			'slug'        => 'pharmacy-chain',
			'title'       => 'Pharmacy chain',
			'seo_title'   => 'Pharmacy chain software: every store on the same rules %%sep%% %%sitename%%',
			'seo_desc'    => 'Central admin, linked outlets, stock transfers, shared roles and reports across stores for a pharmacy chain.',
			'crumb'       => 'Pharmacy chain',
			'h1'          => 'Every store on the same rules',
			'lead'        => 'For owners and operations heads who run several stores: one place to manage the outlets, the same roles everywhere, and reports that cover them all.',
			'screen'      => 'v2-02-dashboard',
			'screen_alt'  => 'The dashboard with dummy data',
			'items_title' => 'What changes when you have more than one store',
			'items_intro' => 'The work of keeping stores consistent, and what the software does about it.',
			'items'       => array(
				array( 'Central admin and linked outlets', 'Manage your stores from one admin account, with each outlet linked to it.', 'pricing', 'feat_central_admin' ),
				array( 'Reports across stores', 'See central reports across your outlets, instead of putting together one file per store.', 'reports', 'feat_central_reports' ),
				array( 'Stock transfer between stores', 'Move stock from one store to another and keep both stock lists right.', 'inventory', '' ),
				array( 'Roles and access', 'Give each person only what they need to see and change, the same way in every store.', 'security', 'feat_role_management' ),
				array( 'Supplier rate contracts', 'Record the rates you have agreed with a supplier as a contract.', 'purchase', 'feat_supplier_rate_contract' ),
				array( 'Accounts in Tally', 'Export your accounts to Tally in XML for your accountant.', 'gst', 'feat_tally_export' ),
			),
			'flow_title'  => 'How a chain usually starts',
			'flow'        => array(
				array( 'See it with your own data', 'Book a demo and tell us how many stores you run and how they buy.' ),
				array( 'Start with one store', 'Load its items and opening stock from a CSV and bill on it for a few days.' ),
				array( 'Link the other stores', 'Add outlets to the central admin and set the same roles in each.' ),
				array( 'Read the central reports', 'Check that the numbers across stores agree with your own books.' ),
			),
			'plan_title'  => 'How a chain is priced',
			'plan_html'   => '<p>The multi-store features are on [rx_plan feat="feat_central_admin" fallback="the Enterprise plan"], which is quoted for each chain. The pricing page lists what every plan includes.</p>',
			'plan_links'  => array( array( 'See what each plan includes', 'pricing' ) ),
			'faq'         => array(
				array( 'How is a chain priced?', 'Chain pricing is a quote, based on how you run. The pricing page shows what each plan includes.' ),
				array( 'Can we see it with our own data?', 'Yes. Book a demo and we can work with items from one of your stores.' ),
				array( 'Does it export to Tally?', 'Yes. Tally XML export is available as a feature of [rx_plan feat="feat_tally_export" fallback="the Enterprise plan"].' ),
			),
			'cta_title'   => 'Tell us about your stores',
			'cta_text'    => 'Book a demo and we will walk through it with the way your chain buys and sells.',
		),

		'hospital'     => array(
			'slug'        => 'hospital-pharmacy',
			'title'       => 'Hospital pharmacy',
			'seo_title'   => 'Hospital pharmacy software: prescription to bill, connected %%sep%% %%sitename%%',
			'seo_desc'    => 'In-patient and discharge billing, payer and TPA companies, deposits, departments, kits and EMR prescriptions as tokens, for a hospital or clinic pharmacy.',
			'crumb'       => 'Hospital pharmacy',
			'h1'          => 'From the doctor’s prescription to the pharmacy bill, connected',
			'lead'        => 'For a hospital or clinic pharmacy that serves wards and an OPD counter: prescriptions arrive as tokens, patients are billed to their stay, and the pharmacy keeps one record.',
			'screen'      => 'v2-07-hospital',
			'screen_alt'  => 'The hospital pharmacy screen with dummy data',
			'items_title' => 'What a hospital pharmacy needs',
			'items_intro' => 'The parts of the work that a counter-only setup does not cover.',
			'items'       => array(
				array( 'Prescriptions as tokens', 'EMR prescriptions arrive as tokens on a token board at the pharmacy, so the queue is a list rather than a pile of papers.', '', '' ),
				array( 'In-patient and discharge billing', 'Bill against an in-patient’s stay and settle at discharge.', '', '' ),
				array( 'Payers and TPA companies', 'Bill a payer or a TPA company and see what is due from them.', '', '' ),
				array( 'Deposits and dues', 'Record patient deposits and see what is outstanding at any time.', '', '' ),
				array( 'Departments, kits and packages', 'Issue to departments, and bill patient kits and packages.', '', '' ),
				array( 'ABDM and ABHA', 'Works with ABDM and ABHA.', '', '' ),
			),
			'flow_title'  => 'A calm way to roll it out',
			'flow'        => array(
				array( 'Talk through wards and counters', 'Tell us how orders reach the pharmacy today and who issues what.' ),
				array( 'Load items and opening stock', 'Bring in your items and stock with batch and expiry, store by store.' ),
				array( 'Run one OPD counter alongside the old system', 'Bill each prescription in both for a week or two and compare the totals.' ),
				array( 'Add one ward at a time', 'Fix what the first ward shows, then move the next.' ),
			),
			'plan_title'  => 'Plans for a hospital pharmacy',
			'plan_html'   => '<p>The hospital features, such as MRD, are on [rx_plan feat="feat_mrd" fallback="the Enterprise plan"], which is quoted for each hospital. The pricing page lists what every plan includes.</p>',
			'plan_links'  => array( array( 'See what each plan includes', 'pricing' ), array( 'Read the hospital pharmacy article', 'blog' ) ),
			'faq'         => array(
				array( 'Which prescriptions arrive as tokens?', 'Prescriptions from the EMR arrive as tokens on a token board at the pharmacy.' ),
				array( 'Does it replace the pharmacist’s check?', 'No. It records the order and the issue. The pharmacist is still the last check between an order and a patient.' ),
				array( 'Can we run it next to our current system first?', 'Yes. The usual way is to start with one OPD counter, compare the totals each day, and then add a ward at a time.' ),
			),
			'cta_title'   => 'Walk through your pharmacy with us',
			'cta_text'    => 'Book a demo and tell us how orders and prescriptions reach your pharmacy.',
		),

		'new-pharmacy' => array(
			'slug'        => 'new-pharmacy',
			'title'       => 'Opening a new pharmacy',
			'seo_title'   => 'Software for a new pharmacy: start free on day one %%sep%% %%sitename%%',
			'seo_desc'    => 'Opening a medical store? Start on the free plan, load your items from a CSV, learn with in-app walkthroughs and move up when you need to.',
			'crumb'       => 'Opening a new pharmacy',
			'h1'          => 'Start right on day one, free',
			'lead'        => 'Opening a store? Begin on the free plan, load your items and bill from your first customer. Move up when you outgrow the limits.',
			'screen'      => 'v2-10-initial-import',
			'screen_alt'  => 'The initial data import screen with dummy data',
			'items_title' => 'Everything you need on the first day',
			'items_intro' => 'Set up in the order you will use it.',
			'items'       => array(
				array( 'Free to start', 'The free plan is free to use, with limits, and has no trial period to run out.', 'pricing', '' ),
				array( 'Add your items from a CSV', 'Load items and opening stock from a CSV. You get a sample sheet, and errors are shown line by line.', 'switch', '' ),
				array( 'Walkthroughs in the app', 'Short walkthroughs cover sales, purchase, the item master and the account master.', '', '' ),
				array( 'Bill, take payment, print', 'Take cash, card, UPI or credit. Save a draft, finish the bill, or save and print.', 'billing', '' ),
				array( 'Expiry watched from the first batch', 'The batch that expires first is picked for you, and a stock expiry report shows what is coming up.', 'expiry', '' ),
				array( 'A way up', 'When you need more users or reports, the paid plans add them, and the pricing page shows exactly what.', 'pricing', '' ),
			),
			'flow_title'  => 'From sign-up to the first bill',
			'flow'        => array(
				array( 'Sign up free', 'Create an account with an email OTP.' ),
				array( 'Add initial data', 'Upload your items and opening stock from a CSV.' ),
				array( 'Follow the walkthroughs', 'Learn sales and purchase on a few sample entries.' ),
				array( 'Bill your first customer', 'Search the item, pick the batch, take payment and print.' ),
			),
			'plan_title'  => 'The free plan, in numbers',
			'plan_html'   => '<p>[rx_cost_text]</p><p>The pricing page always shows the current limits of every plan.</p>',
			'plan_links'  => array( array( 'See the plans', 'pricing' ) ),
			'faq'         => array(
				array( 'Is it really free?', 'The free plan is free to use, with limits. There is no trial: you move to a paid plan only if you need more.' ),
				array( 'What if I already have an item list?', 'Add initial data takes a CSV of items and opening stock, and reports errors line by line.' ),
				array( 'Can I add staff later?', 'Yes. The number of users depends on the plan, and the pricing page shows it for each one.' ),
			),
			'cta_title'   => 'Open on day one',
			'cta_text'    => 'Start free, or book a short demo and we will set it up with you.',
		),
	);
}
