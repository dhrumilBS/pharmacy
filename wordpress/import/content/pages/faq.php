<?php
/**
 * Import data: the FAQ page (PHP_Site faq.php).
 *
 * Fields: group_rx_faq (templates/faq.php): one topic per block of $faq, in
 * order, with the slug used for the jump links and section ids.
 *
 * Live values:
 * - "Is there really a free plan?": the free plan's name, users and monthly
 *   bill limits come from [rx_free_plan] (pricing_plan(1) and
 *   plan_bill_limits(1) today; plan 1 is the free plan). The question is
 *   wrapped in [rx_if cond="free"], so the item is skipped when there is no
 *   free plan or pricing is null (shortcodes can't nest an [rx_if] inside
 *   another, and the answer needs one for the bill limits).
 * - "How much does it cost?": [rx_cost_text] (pricing_cost_text()).
 * - Support phone: [rx_site key="support_phone"]; internal links: [rx_link].
 * Text that faq.php writes literally (plan names included) stays literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'faq.php',
	'key'      => 'faq',
	'title'    => 'FAQ',
	'slug'     => 'faq',
	'template' => 'templates/faq.php',
	'seo'      => array(
		'title'   => 'FAQ: pharmacy software questions, answered %%sep%% %%sitename%%',
		'desc'    => 'Straight answers on the free plan, switching from your current software, FEFO billing, expiry, purchase import, GST and Tally, hospital pharmacy, security and pricing.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'   => array(
			'crumb'   => 'FAQ',
			'parent'  => 'Resources',
			'eyebrow' => 'FAQ',
			'h1'      => 'Questions pharmacy owners ask us',
			'lead'    => 'Straight answers on switching, billing, expiry, GST, hospital pharmacy and pricing. Don’t see yours? Call us.',
		),
		'topics' => array(
			array(
				'slug'      => 'start',
				'name'      => 'Getting started',
				'questions' => array(
					array(
						'q'         => '[rx_if cond="free"]Is there really a free plan?[/rx_if]',
						'a_html'    => 'Yes. The [rx_free_plan field="name"] plan is free forever for [rx_free_plan field="users_text"][rx_if cond="free_sales_limit"], with [rx_free_plan field="sales"] sales bills[rx_free_plan field="purchase" before=" and " after=" purchase bills"] a month[/rx_if]: enough to set up your items and try billing. Upgrade to a paid plan when you need unlimited bills.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Do I need to install anything?',
						'a_html'    => 'No. It runs in your web browser. You sign up with your store name and email, verify with an OTP, and log in from any computer.',
						'confirmed' => true,
					),
					array(
						'q'         => 'How long does setup take?',
						'a_html'    => 'Most of the work is done once: import your items and opening stock from a CSV file, add suppliers and staff, then start billing. Built-in walkthroughs show each step.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'switch',
				'name'      => 'Switching from other software',
				'questions' => array(
					array(
						'q'         => 'Will my billing stop while we switch?',
						'a_html'    => 'No. Keep your current software running while you set up on the free plan and import your data. Go live on the day you choose.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I bring over data from my current software?',
						'a_html'    => 'Yes, through CSV import. Export your items and stock from your current software or Excel, match your columns to ours using the sample sheet, and import. Rows with errors are flagged so you can fix them.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Will you import the data for me?',
						'a_html'    => '[Confirm whether the team offers assisted import and at what cost.]',
						'confirmed' => false,
					),
				),
			),
			array(
				'slug'      => 'billing',
				'name'      => 'Billing & counter',
				'questions' => array(
					array(
						'q'         => 'Does billing pick the earliest-expiry batch?',
						'a_html'    => 'Yes. When you add an item, the batch that expires first is selected automatically (FEFO). You can still choose another batch.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I search by generic name or barcode?',
						'a_html'    => 'Yes. Search by brand or generic name, or use a USB barcode reader. F2 opens search and Ctrl+S saves the bill.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Which payment methods can I record?',
						'a_html'    => 'Cash, card, UPI and credit (udhaar), with the patient’s purchase history and dues on the bill.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I send the bill on WhatsApp?',
						'a_html'    => 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Does it work with my printer?',
						'a_html'    => 'Bills print in thermal and A4/A5 formats, and you can print barcode labels for stock. Tell us your printer model during the demo and we’ll check it.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'stock',
				'name'      => 'Expiry & stock',
				'questions' => array(
					array(
						'q'         => 'How do I see what is expiring?',
						'a_html'    => 'The stock expiry report lists batches expiring in a date range, item-wise or supplier-wise, so you can sell them first or return them in time.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I stop expired medicines being billed?',
						'a_html'    => 'Yes. A store setting controls whether expired items can be sold.',
						'confirmed' => true,
					),
					array(
						'q'         => 'How does reordering work?',
						'a_html'    => 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book; on Advance and above you can turn the short book into purchase orders.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'purchase',
				'name'      => 'Purchase & suppliers',
				'questions' => array(
					array(
						'q'         => 'Do I have to type every distributor bill?',
						'a_html'    => 'No. Import the bill as a CSV file, or scan a printed bill with OCR import (uses OCR credits). Batch, expiry, MRP, PTR and GST fill in, and margin is worked out on save.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I track what I owe each supplier?',
						'a_html'    => 'Yes. Each supplier has a ledger with dues and payment history, plus a supplier due report.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'gst',
				'name'      => 'GST & accounts',
				'questions' => array(
					array(
						'q'         => 'Does it handle GST correctly?',
						'a_html'    => 'HSN codes and GST rates are applied on every bill from the item master, and the GST report and bill-wise tax report are ready for your accountant. Keep rates in the item master up to date.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I export to Tally?',
						'a_html'    => 'Tally XML export is part of the Enterprise plan. On other plans, reports export to Excel for your CA.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Does it handle Schedule H and H1 medicines?',
						'a_html'    => 'Items can be tagged as Schedule H or H1, and bills can carry patient and doctor details. Your licence obligations stay with your pharmacy. Check current rules with your compliance adviser.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'hospital',
				'name'      => 'Hospital pharmacy',
				'questions' => array(
					array(
						'q'         => 'Is it suitable for a hospital pharmacy?',
						'a_html'    => 'Yes. It supports IPD and discharge billing, payer and TPA companies, patient deposits and dues, departments, patient kits and packages.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can prescriptions come from the doctor’s EMR?',
						'a_html'    => 'Yes. Prescriptions from the connected hospital EMR arrive at the pharmacy counter as tokens on a token board, ready to bill.',
						'confirmed' => true,
					),
					array(
						'q'         => 'Is it ABDM integrated?',
						'a_html'    => 'Yes. You can create or link a patient’s ABHA with their OTP-based consent.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'security',
				'name'      => 'Security & data',
				'questions' => array(
					array(
						'q'         => 'Is my login secure?',
						'a_html'    => 'Every user has their own login and role. On Advance and above you can turn on two-step OTP login, allow logins only from your store’s IP addresses, and see an activity report.',
						'confirmed' => true,
					),
					array(
						'q'         => 'What if the internet goes down?',
						'a_html'    => 'It runs in your web browser. [Confirm offline behaviour before publishing.]',
						'confirmed' => false,
					),
					array(
						'q'         => 'Can I take my data with me if I leave?',
						'a_html'    => 'Reports, item lists and ledgers export to Excel or CSV. [Confirm the full-account export process.]',
						'confirmed' => false,
					),
				),
			),
			array(
				'slug'      => 'plans',
				'name'      => 'Plans & payment',
				'questions' => array(
					array(
						'q'         => 'How much does it cost?',
						'a_html'    => '[rx_cost_text] See the [rx_link key="pricing"]pricing page[/rx_link].',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I cancel anytime?',
						'a_html'    => 'Yes, you can switch or cancel anytime. Refunds are covered by our [rx_link key="refund"]refund policy[/rx_link].',
						'confirmed' => true,
					),
					array(
						'q'         => 'Can I use it for more than one store?',
						'a_html'    => 'Yes, on Enterprise: central administration with linked outlets, stock transfers between stores, and central and multi-store reports.',
						'confirmed' => true,
					),
				),
			),
			array(
				'slug'      => 'support',
				'name'      => 'Support',
				'questions' => array(
					array(
						'q'         => 'How do I get help?',
						'a_html'    => 'Call support on [rx_site key="support_phone"], raise a ticket in the help centre, or follow the step-by-step walkthroughs in the app.',
						'confirmed' => true,
					),
					array(
						'q'         => 'What are your support hours?',
						'a_html'    => '[Confirm support hours and languages before publishing.]',
						'confirmed' => false,
					),
				),
			),
		),
	),
);
