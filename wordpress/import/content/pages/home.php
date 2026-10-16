<?php
/**
 * Import data: the Home page (front page), from the PHP_Site's index.php.
 *
 * Fields follow group_rx_home.json, section by section in today's order:
 * hero (+ demo slip), trusted strip, product tour, "Sound familiar?" inbox
 * (partials/problems-chat.php), estimator, day, feature tabs, switching,
 * who it's for, buyer's checklist, support, FAQ and the demo section.
 * The demo slip, the estimator, the tour animation and the demo form fields
 * are template parts with their own strings; only their headings live here.
 *
 * Copy rules:
 * - Text is copied exactly (curly quotes, dashes, spacing as rendered).
 *   Plain-text fields hold plain text ('&', not '&amp;'); fields ending in
 *   _html, and the `html` sub-fields, hold the inner HTML of their element
 *   (no wrapping <p>; the template prints the element and any icon).
 * - Every `data-confirm` on index.php is a `confirmed` / `*_confirmed`
 *   false here. The inline <span data-confirm> in FAQ 6 stays as markup.
 * - Live values are shortcodes: [rx_brand] for $brand, [rx_site] /
 *   [rx_tel] for the support number, [rx_link] for url() and SITE links,
 *   [rx_free_plan] for the free plan's name, users and bill limits, and
 *   [rx_terms_text] for the payment terms. With live pricing they render
 *   exactly today's text. When pricing is unavailable the free-plan phrases
 *   drop out ("start free" instead of "start free on the {free plan} plan"),
 *   and FAQ 1 and the terms sentence are hidden.
 * - Paid plan names (Basic, Advance, Enterprise) and "3 to 10 users" are
 *   literal on index.php and no shortcode prints them, so they stay literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'index.php',
	'key'    => 'home',
	'title'  => 'Home',
	'slug'   => 'home',
	'group'  => 'home',
	'seo'    => array(
		'title'   => 'Pharmacy software for medical stores: billing, expiry & stock %%sep%% %%sitename%%',
		'desc'    => 'Pharmacy billing software for medical stores: batch-wise FEFO billing, stock expiry reports, CSV and OCR purchase import, and GST reports. Book a free demo or start free.',
		'noindex' => false,
	),
	'fields' => array(

		/*
		 * Hero (#top). The demo slip beside it is parts/hero-lead.php.
		 */
		'hero_eyebrow'               => 'For medical stores & pharmacies',
		'hero_title_html'            => 'Bill faster at the counter. <span class="hl">Stop losing money to <span class="scribble">expiry</span>.</span>',
		'hero_lead_html'             => '[rx_brand] picks the batch that expires first on every bill, imports distributor bills from CSV, and keeps GST and stock right as you sell. Leave your number and we’ll show you, with medicines from your own shelf.',
		'hero_tour_label'            => 'Watch the 40-second tour',
		'hero_free_label'            => 'Or start free yourself',

		/*
		 * Trusted strip (partials/trusted-strip.php).
		 */
		'trusted_title'              => 'Billing every day at medical stores, chains & hospital pharmacies',
		'trusted_note'               => 'A few of them',

		/*
		 * Product tour (#tour, partials/product-tour.php).
		 */
		'tour_eyebrow'               => 'Product tour · 40 seconds',
		'tour_title_html'            => 'Watch one bill go from <span class="scribble-u">scan to print</span>',
		'tour_intro'                 => 'Then a distributor bill comes in with its margins checked, and the expiry report lines up what to send back. The same screens your counter staff use.',

		/*
		 * "Sound familiar?" inbox (#problems, partials/problems-chat.php).
		 */
		'problems_head'              => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Six messages every pharmacy owner has typed. Here’s our reply.',
			'intro'   => 'Pick a message to see what changes.',
		),
		'problems_note'              => 'Illustrative conversation',
		'problems'                   => array(
			array(
				'icon'  => 'i-alert',
				'topic' => 'Expired stock',
				'who'   => 'Store owner',
				'time'  => '9:12',
				'quote' => 'We found expired strips behind the new stock. Again.',
				'why'   => 'Money already paid to the distributor, now dead stock, and we only find out during a shelf check.',
				'fix'   => 'Billing picks the batch that expires first, and the stock expiry report shows what’s expiring, item-wise or supplier-wise.',
			),
			array(
				'icon'  => 'i-users',
				'topic' => 'Counter rush',
				'who'   => 'Pharmacist',
				'time'  => '19:40',
				'quote' => 'Customers walk out while staff search for the medicine.',
				'why'   => 'Evening rush, five people waiting, and the new staff member can’t find the item or the right batch.',
				'fix'   => 'Search by brand or generic name, the batch fills in by itself, and keyboard shortcuts save the bill without the mouse.',
			),
			array(
				'icon'  => 'i-truck',
				'topic' => 'Distributor bills',
				'who'   => 'Store owner',
				'time'  => '22:05',
				'quote' => 'Every night I sit typing distributor bills, line by line.',
				'why'   => 'Batch, expiry, MRP, PTR, scheme, free quantity. Forty lines a bill, every bill.',
				'fix'   => 'Import the bill as a CSV file, or scan it with OCR import. Margin, PTR and GST are worked out when you save.',
			),
			array(
				'icon'  => 'i-box',
				'topic' => 'Stock mismatch',
				'who'   => 'Chain operations head',
				'time'  => '11:30',
				'quote' => 'The software says five boxes. The shelf has two.',
				'why'   => 'Wrong entries, missed returns, or worse. Either way, I’ve stopped trusting the stock numbers.',
				'fix'   => 'Stock is tracked batch-wise, adjustments are recorded, and each user has their own login and role, so you can see who did what.',
			),
			array(
				'icon'  => 'i-rupee',
				'topic' => 'Month-end GST',
				'who'   => 'Store owner',
				'time'  => '18:15',
				'quote' => 'Month-end GST is a mess for me and my CA.',
				'why'   => 'Wrong rates mean wrong invoices, and a weekend spent fixing entries.',
				'fix'   => 'HSN codes and GST rates are applied on every bill from the item master, and the GST report is ready for your accountant.',
			),
			array(
				'icon'  => 'i-mobile',
				'topic' => 'Away from the shop',
				'who'   => 'Store owner',
				'time'  => '21:50',
				'quote' => 'I can’t leave the shop without worrying.',
				'why'   => 'Sales, cash, credit customers, distributor dues: it’s all in the shop, or in a notebook.',
				'fix'   => 'Get the daily report on WhatsApp, and log in from any browser to check collections, patient dues and supplier dues.',
			),
		),

		/*
		 * Day at the pharmacy (#day). The estimator (#estimator) before it is
		 * parts/estimator.php and has no fields.
		 */
		'day_head'                   => array(
			'eyebrow' => 'A day at your pharmacy',
			'title'   => 'Built around how a medical store actually runs',
			'intro'   => 'Not a generic shop billing app. Here’s a normal day, from shutter up to shutter down.',
		),
		'day'                        => array(
			array(
				'time'  => '9:00',
				'ampm'  => 'am',
				'title' => 'Opening',
				'text'  => 'Before the first customer, the dashboard shows what’s expiring soon, what’s below minimum stock in the short book, and which supplier payments are due.',
			),
			array(
				'time'  => '3:00',
				'ampm'  => 'pm',
				'title' => 'Distributor delivery',
				'text'  => 'The bill comes in as a CSV or a scanned page, so batch, expiry, scheme and free quantity go in once. PTR and margin are worked out when you save.',
			),
			array(
				'time'  => '7:30',
				'ampm'  => 'pm',
				'title' => 'Counter rush',
				'text'  => 'Staff search by brand or generic name, the earliest-expiry batch fills in, and the bill is paid by cash, card, UPI or credit.',
			),
			array(
				'time'  => '10:00',
				'ampm'  => 'pm',
				'title' => 'Closing',
				'text'  => 'Collections are split by payment method and by staff member, so the drawer adds up. The day’s report arrives on WhatsApp.',
			),
		),

		/*
		 * Feature tabs (#features). Slugs give the ids tab-{slug} / panel-{slug}.
		 */
		'features_head'              => array(
			'eyebrow'    => 'Features',
			'title'      => 'Everything a medical store needs. Nothing it doesn’t.',
			'intro_html' => 'Keep scrolling to walk through each part of the store, or pick one. Some features depend on your plan — see [rx_link key="pricing"]plans[/rx_link].',
		),
		'tabs'                       => array(
			array(
				'slug'       => 'billing',
				'icon'       => 'i-receipt',
				'label'      => 'Billing & counter',
				'title'      => 'A counter screen built for the evening rush',
				'lead'       => 'Search, pick, save, print — with step-by-step walkthroughs for new staff built into the app.',
				'points'     => array(
					array( 'html' => '<b>Fast item search</b> — by brand or generic name, or with a barcode reader.' ),
					array( 'html' => '<b>Batch auto-selection (FEFO)</b> — the earliest-expiry batch fills in first.' ),
					array( 'html' => '<b>Cash, card, UPI or credit</b> — with patient history and dues on the bill.' ),
					array( 'html' => '<b>Print or share</b> — send the bill by WhatsApp, SMS or email (uses message credits).' ),
					array( 'html' => '<b>Bring customers back</b> — medicine reminders, loyalty cards and online orders for pick-up or delivery.' ),
				),
				'screen'     => 'v2-01-counter-billing',
				'screen_alt' => 'Sample screen: Counter billing screen with batch picker and bill summary',
			),
			array(
				'slug'       => 'expiry',
				'icon'       => 'i-alert',
				'label'      => 'Expiry & batch tracking',
				'title'      => 'Catch expiring stock while you can still act',
				'lead'       => 'Every item is tracked by batch and expiry from the moment the purchase bill is saved.',
				'points'     => array(
					array( 'html' => '<b>Stock expiry report</b> — filter by date, item-wise or supplier-wise.' ),
					array( 'html' => '<b>First-expiry-first-out billing</b> — older batches go out first.' ),
					array( 'html' => '<b>Purchase returns &amp; credit notes</b> — send short-expiry stock back to the supplier, on record.' ),
					array( 'html' => '<b>Stop expired sales</b> — a store setting controls whether expired items can be billed.' ),
				),
				'screen'     => 'v2-03-expiry',
				'screen_alt' => 'Sample screen: Stock expiry report, supplier-wise, next 60 days',
			),
			array(
				'slug'       => 'stock',
				'icon'       => 'i-box',
				'label'      => 'Stock & reorder',
				'title'      => 'Stock numbers you can finally trust',
				'lead'       => 'Know what you have, what’s running low and what isn’t moving.',
				'points'     => array(
					array( 'html' => '<b>Min and max stock per item</b> — stock alerts and a short book of what to order.' ),
					array( 'html' => '<b>Purchase orders &amp; reorder processing</b> — turn the short book into an order (Advance plan).' ),
					array( 'html' => '<b>Non-moving items report</b> — see what’s blocking your money.' ),
					array( 'html' => '<b>Barcode label printing</b> and recorded stock adjustments.' ),
				),
				'screen'     => 'v2-05-reorder',
				'screen_alt' => 'Sample screen: Stock alert and short book grouped by supplier',
			),
			array(
				'slug'       => 'purchase',
				'icon'       => 'i-truck',
				'label'      => 'Purchase & suppliers',
				'title'      => 'Distributor bills in, without the late nights',
				'lead'       => 'Record purchases, schemes, returns and payments in one place.',
				'points'     => array(
					array( 'html' => '<b>CSV import</b> — map the distributor’s columns once, then import.' ),
					array( 'html' => '<b>OCR import</b> — scan a printed bill into a purchase entry (uses OCR credits).' ),
					array( 'html' => '<b>PTR, margin and GST input</b> — calculated when you save; stock goes live instantly.' ),
					array( 'html' => '<b>Supplier ledger &amp; dues</b> — plus supplier rate contracts on Enterprise.' ),
				),
				'screen'     => 'v2-04-purchase-import',
				'screen_alt' => 'Sample screen: Distributor bill imported from a CSV file',
			),
			array(
				'slug'       => 'gst',
				'icon'       => 'i-rupee',
				'label'      => 'GST & accounts',
				'title'      => 'GST-ready bills and clean accounts',
				'lead'       => 'Stop fixing entries at month-end. Hand your accountant clean data.',
				'points'     => array(
					array( 'html' => '<b>HSN and GST rate on every line</b> — applied from the item master.' ),
					array( 'html' => '<b>GST report and bill-wise tax report</b> — export to Excel for your CA.' ),
					array( 'html' => '<b>Tally XML export</b> — sales, purchases, returns and payments (Enterprise plan).' ),
					array( 'html' => '<b>Ledgers, vouchers &amp; expenses</b> — patient, supplier and account ledgers.' ),
				),
				'screen'     => 'v2-06-gst',
				'screen_alt' => 'Sample screen: September GST report with HSN-wise summary',
			),
			array(
				'slug'       => 'patients',
				'icon'       => 'i-hospital',
				'label'      => 'Patients & hospital pharmacy',
				'title'      => 'Built for hospital pharmacies too',
				'lead'       => 'Built by a team that also makes hospital software, so it handles patients, not just customers.',
				'points'     => array(
					array( 'html' => '<b>IPD and discharge billing</b> — with patient deposits, dues and ledgers.' ),
					array( 'html' => '<b>Payer and TPA companies</b> — split TPA and non-TPA sales.' ),
					array( 'html' => '<b>Prescriptions from the hospital EMR</b> — arrive at the counter as tokens, shown on a token board.' ),
					array( 'html' => '<b>ABDM integrated</b> — create or link a patient’s ABHA.' ),
					array( 'html' => '<b>Departments, patient kits &amp; packages</b> — plus Schedule H / H1 item tagging.' ),
				),
				'screen'     => 'v2-07-hospital',
				'screen_alt' => 'Sample screen: Hospital pharmacy token board with EMR prescription',
			),
			array(
				'slug'       => 'reports',
				'icon'       => 'i-chart',
				'label'      => 'Reports & control',
				'title'      => 'See the business, control who does what',
				'lead'       => 'Reports that answer real questions, and controls that protect your stock and cash.',
				'points'     => array(
					array( 'html' => '<b>Margin, top-selling and collection reports</b> — plus an executive dashboard on Advance.' ),
					array( 'html' => '<b>Daily report on WhatsApp</b> — the day’s numbers without visiting the shop.' ),
					array( 'html' => '<b>Roles and privileges</b> — staff see only what they need to.' ),
					array( 'html' => '<b>Two-step login, IP whitelisting and activity report</b> — on the Advance plan.' ),
				),
				'screen'     => 'v2-02-dashboard',
				'screen_alt' => 'Sample screen: Dashboard with sales, expiry and stock alerts',
			),
		),

		/*
		 * Switching (#switching).
		 */
		'switching_head'             => array(
			'eyebrow' => 'Switching made safe',
			'title'   => 'Change your software without stopping a single bill',
			'intro'   => 'The biggest worry about switching is the first week. Your old system can keep running while you set up the new software on the free plan, then switch over when you’re ready.',
		),
		'moved_title'                => 'What you can bring in from a CSV file',
		'moved'                      => array(
			array( 'text' => 'Items with generic name & HSN' ),
			array( 'text' => 'Opening stock with batch & expiry' ),
			array( 'text' => 'MRP, sale rate & pack size' ),
			array( 'text' => 'Supplier for each stock line' ),
		),
		'from'                       => array(
			array(
				'html'      => 'Export from your current software, Excel or registers. A sample sheet is provided, and you match your columns to ours before importing.',
				'confirmed' => true,
			),
			array(
				'html'      => 'Want help? Our team can review your file with you before you import.',
				'confirmed' => false,
			),
		),
		'switch_steps'               => array(
			array(
				'title' => 'Create your store account',
				'text'  => 'Sign up with your store name and email, and verify with an OTP.[rx_free_plan field="name" before=" The " after=" plan is free."]',
				'when'  => 'A few minutes',
			),
			array(
				'title' => 'Import your items and stock',
				'text'  => 'Upload a CSV of your items and opening stock. Errors are flagged line by line so you can fix them before going live.',
				'when'  => 'Same day',
			),
			array(
				'title' => 'Let your staff learn',
				'text'  => 'Built-in walkthroughs for sales, purchase and item master show each step on screen. Keyboard shortcuts keep billing quick.',
				'when'  => 'A short session',
			),
			array(
				'title' => 'Go live, then upgrade if you need to',
				'text'  => 'Run both systems side by side if you like. Move to Basic or Advance when you need unlimited bills and more users — switch or cancel anytime.',
				'when'  => 'You choose the day',
			),
		),
		'switch_button'              => 'Book a free demo',

		/*
		 * Who it's for (#solutions). The first card is the featured one.
		 */
		'solutions_head'             => array(
			'eyebrow' => 'Who it’s for',
			'title'   => 'One store, a chain, or a hospital — it fits the way you work',
			'intro'   => '',
		),
		'solutions'                  => array(
			array(
				'icon'     => 'i-store',
				'who'      => 'Basic or Advance plan',
				'title'    => 'Single medical store',
				'text'     => 'For the owner-pharmacist who wants faster billing, fewer expiry losses and less paperwork.',
				'featured' => true,
				'checks'   => array(
					array( 'text' => 'Billing, expiry, purchase & GST' ),
					array( 'text' => '3 to 10 users with roles' ),
					array( 'text' => 'Start free, upgrade anytime' ),
				),
			),
			array(
				'icon'     => 'i-stores',
				'who'      => 'Enterprise plan',
				'title'    => 'Pharmacy chains',
				'text'     => 'For owners and operations heads who need every store to follow the same rules.',
				'featured' => false,
				'checks'   => array(
					array( 'text' => 'Central administration & linked outlets' ),
					array( 'text' => 'Stock transfers between stores' ),
					array( 'text' => 'Central & multi-store reports' ),
				),
			),
			array(
				'icon'     => 'i-hospital',
				'who'      => 'Hospitals & clinics',
				'title'    => 'In-house pharmacy',
				'text'     => 'For hospitals and nursing homes that run a pharmacy for OPD and admitted patients.',
				'featured' => false,
				'checks'   => array(
					array( 'text' => 'IPD, discharge & TPA billing' ),
					array( 'text' => 'Department stock transfers' ),
					array( 'text' => 'Works with hospital EMR & ABDM' ),
				),
			),
		),

		/*
		 * Buyer's checklist (#checklist).
		 */
		'checklist_head'             => array(
			'eyebrow' => 'Before you choose',
			'title'   => '8 questions to ask any pharmacy software company',
			'intro'   => 'Whoever you buy from, get clear answers to these. Here are ours.',
		),
		'checklist_cols'             => array(
			array( 'label' => 'Ask this' ),
			array( 'label' => 'Why it matters' ),
			array( 'label' => 'Our answer' ),
		),
		'checklist'                  => array(
			array(
				'ask'         => 'Can a new staff member bill on day one?',
				'why'         => 'Staff change often. Training shouldn’t take weeks.',
				'confirmed'   => true,
				'answer_html' => 'Built-in walkthroughs show each step on screen, and shortcuts keep billing quick.',
			),
			array(
				'ask'         => 'Does billing pick the earliest-expiry batch?',
				'why'         => 'This is where most expiry losses start.',
				'confirmed'   => true,
				'answer_html' => 'Yes — batch auto-selection (FEFO), plus a stock expiry report.',
			),
			array(
				'ask'         => 'How do I get my data in?',
				'why'         => 'Re-typing thousands of items is not an option.',
				'confirmed'   => true,
				'answer_html' => 'Import items and opening stock from CSV, with column matching and line-by-line error checks.',
			),
			array(
				'ask'         => 'What happens when the internet goes down?',
				'why'         => 'The counter can’t stop in the evening rush.',
				'confirmed'   => false,
				'answer_html' => 'It runs in your web browser. [Confirm offline behaviour before publishing.]',
			),
			array(
				'ask'         => 'What will I pay in year two?',
				'why'         => 'Low first-year prices and costly renewals are common.',
				'confirmed'   => true,
				'answer_html' => 'Prices are published on our [rx_link key="pricing"]pricing page[/rx_link].[rx_if cond="pricing"] Pay for [rx_terms_text]; switch or cancel anytime.[/rx_if]',
			),
			array(
				'ask'         => 'Who do I call when billing stops?',
				'why'         => 'Support hours should match shop hours.',
				'confirmed'   => false,
				'answer_html' => 'Call support on [rx_site key="support_phone"] or raise a ticket at the help centre.',
			),
			array(
				'ask'         => 'Can I take my data with me if I leave?',
				'why'         => 'It’s your business record, not the vendor’s.',
				'confirmed'   => false,
				'answer_html' => 'Item lists and reports export to CSV or Excel. [Confirm full data export.]',
			),
			array(
				'ask'         => 'Will it work with my printer and scanner?',
				'why'         => 'New hardware is an unplanned cost.',
				'confirmed'   => true,
				'answer_html' => 'Thermal and A4/A5 bill formats, barcode label printing, and USB barcode readers.',
			),
		),
		'checklist_foot_html'        => '<b>Comparing a few options?</b> Get this checklist as a one-page PDF to use in every demo.',
		'checklist_button'           => 'Send me the checklist',
		'checklist_button_confirmed' => false,

		/*
		 * Support (#support).
		 */
		'support_head'               => array(
			'eyebrow' => 'Support',
			'title'   => 'Real people who understand pharmacies',
			'intro'   => 'When something goes wrong at the counter, you need an answer fast — from people who build software for pharmacies and hospitals every day.',
		),
		'support_points'             => array(
			array(
				'html'      => '<b>Phone support</b> on [rx_site key="support_phone"]',
				'confirmed' => true,
			),
			array(
				'html'      => '<b>Priority support</b> on the Enterprise plan',
				'confirmed' => true,
			),
			array(
				'html'      => '<b>Support hours and languages</b> — [confirm before publishing]',
				'confirmed' => false,
			),
		),
		'channels'                   => array(
			array(
				'icon'  => 'i-headset',
				'title' => 'Phone',
				'html'  => 'Call [rx_tel key="support"] and talk it through.',
			),
			array(
				'icon'  => 'i-chat',
				'title' => 'Help centre',
				'html'  => 'Raise and track a ticket at the [rx_link setting="help_url"]help centre[/rx_link].',
			),
			array(
				'icon'  => 'i-play',
				'title' => 'In-app walkthroughs',
				'html'  => 'Step-by-step guides for sales, purchase, items and accounts, inside the app.',
			),
			array(
				'icon'  => 'i-screen',
				'title' => 'Keyboard shortcuts',
				'html'  => 'A shortcuts panel in the app shows the keys for every screen.',
			),
		),

		/*
		 * FAQ (#faq). Ids faq-%d / faq-b%d from 1 come from the template.
		 */
		'faq_head'                   => array(
			'eyebrow' => 'FAQ',
			'title'   => 'Questions pharmacy owners ask us',
			'intro'   => 'Don’t see yours? Call us — you’ll get a straight answer.',
		),
		'faq_call_label'             => 'Call [rx_site key="support_phone"]',
		'faq'                        => array(
			array(
				'q'         => 'Is there really a free plan?',
				'confirmed' => true,
				// Hidden (item skipped) when pricing is unknown or the free plan has no sales bill limit.
				'a_html'    => '[rx_if cond="free_sales_limit"]Yes. The [rx_free_plan field="name"] plan is free forever, for [rx_free_plan field="users_text"], with [rx_free_plan field="sales"] sales bills[rx_free_plan field="purchase" before=" and " after=" purchase bills"] a month. It’s enough to set up your items and try billing. When you need unlimited bills, move to Basic or Advance.[/rx_if]',
			),
			array(
				'q'         => 'Will my billing stop while we switch?',
				'confirmed' => true,
				'a_html'    => 'No. Keep your current software running while you set up the new software and import your items and stock. Go live on the day you choose.',
			),
			array(
				'q'         => 'Can I bring over data from my current software?',
				'confirmed' => true,
				'a_html'    => 'Yes, through CSV import. Export your items and stock from your current software or Excel, match your columns to ours using the sample sheet, and import. Rows with errors are flagged so you can fix them.',
			),
			array(
				'q'         => 'What if the internet goes down?',
				'confirmed' => false,
				'a_html'    => 'It runs in your web browser. [To confirm with the product team: describe exactly how billing behaves offline. Do not publish until confirmed.]',
			),
			array(
				'q'         => 'Can I export to Tally?',
				'confirmed' => true,
				'a_html'    => 'Tally XML export is part of the Enterprise plan. On Basic and Advance, the GST report and other reports export to Excel for your accountant.',
			),
			array(
				'q'         => 'Does it handle Schedule H and H1 medicines?',
				'confirmed' => true,
				'a_html'    => 'Items can be tagged as Schedule H or H1 in the item master, and bills can carry patient and doctor details. <span data-confirm>[Confirm whether a dedicated H1 register report is available.]</span> Your licence obligations stay with your pharmacy — check current rules with your compliance adviser.',
			),
			array(
				'q'         => 'Does it work with my printer and barcode scanner?',
				'confirmed' => true,
				'a_html'    => 'Bills print in thermal and A4/A5 formats, and you can print barcode labels for your stock. USB barcode readers work for item entry. Tell us your models during the demo and we’ll check them.',
			),
			array(
				'q'         => 'Is my login secure?',
				'confirmed' => true,
				'a_html'    => 'Every user has their own login and role. On Advance and above you can turn on two-step OTP login, limit access to your store’s IP addresses, and force-logout a user.',
			),
			array(
				'q'         => 'Can I use it for more than one store?',
				'confirmed' => true,
				'a_html'    => 'Yes, on the Enterprise plan: central administration with linked outlets, stock transfers between stores, and central and multi-store reports.',
			),
		),

		/*
		 * Demo section (#demo) and its form (parts/demo-form.php).
		 */
		'demo_head'                  => array(
			'eyebrow' => 'Free demo',
			'title'   => 'See it working with medicines from your own shelf',
		),
		'demo_html'                  => 'A short demo on a call, at a time that suits you. No pressure, no obligation. Prefer to try it yourself? [rx_link setting="app_signup" style="color:#fff"]Start free[rx_free_plan field="name" before=" on the " after=" plan"][/rx_link].',
		'demo_steps'                 => array(
			array(
				'title' => 'We call you within one working day',
				'text'  => 'To understand your store and current software.',
			),
			array(
				'title' => 'You see your own items in the demo',
				'text'  => 'Billing, expiry report and purchase entry — the way you’d use them.',
			),
			array(
				'title' => 'You pick the right plan',
				'text'  => 'From the free [rx_free_plan field="name" after=" "]plan to Enterprise for chains.',
			),
		),
		'demo_steps_confirmed'       => false,
		'form_title'                 => 'Book your free demo',
		'form_sub'                   => 'Tell us about your store and we’ll call you.',
		'form_success'               => 'Thank you — we’ll call you soon',
	),
);
