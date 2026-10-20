<?php
/**
 * Import data: the "Billing & counter" feature page (/features/billing/).
 *
 * PHP_Site source: features/billing.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * Live values are shortcodes: plan labels from feature_plan_label() are the
 * rows' `feat` (or [rx_plan] inside copy), the free plan facts from
 * pricing_split() / plan_bill_limits() are [rx_free_plan] inside [rx_if],
 * and url() links are [rx_link]. The worked example keeps today's
 * illustrative numbers as text (2 × ₹350 × 30 = ₹21,000).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/billing.php',
	'key'      => 'billing',
	'title'    => 'Billing & counter',
	'slug'     => 'billing',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Medical store billing software with FEFO batches %%sep%% %%sitename%%',
		'desc'    => 'Pharmacy billing for the counter rush: search by brand or generic, nearest-expiry batch picked first, cash, card, UPI or credit, bills on WhatsApp.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Billing & counter',
			'parent'  => 'Features',
			'eyebrow' => 'Billing & counter',
			'h1'      => 'Bill the evening rush without losing the queue',
			'lead'    => 'Search by brand or generic name, let the nearest-expiry batch fill in by itself, take cash, card, UPI or credit, then print or share the bill. A counter screen built for medicine, not a general shop.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the counter in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'Batch picked by nearest expiry (FEFO)' ),
			array( 'text' => 'F2 to search, Ctrl+S to save' ),
			array( 'text' => '[rx_if cond="free_sales_limit"][rx_free_plan field="name"] plan: [rx_free_plan field="sales"] sales bills a month, free[/rx_if]' ),
		),
		'screen'          => 'v2-01-counter-billing',
		'screen_alt'      => 'Counter billing screen with batch picker and bill summary',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Where the counter loses money',
			'intro'   => 'Nobody notices these on the day. They add up over a month.',
		),
		'scenes'          => array(
			array(
				'time'  => '7:45 pm, five people waiting',
				'title' => 'The customer asks for the brand',
				'text'  => 'You stock it under its generic name. A new staff member scrolls the list while the queue grows, and two people walk out to the shop next door.',
				'cost'  => 'sales that went to someone else, and a regular who may not come back.',
			),
			array(
				'time'  => 'Saturday rush',
				'title' => 'The front box goes out first',
				'text'  => 'Staff grab the strip that’s easiest to reach, not the batch that expires first. The older batch slides to the back of the shelf.',
				'cost'  => 'stock you already paid for, written off months later.',
			),
			array(
				'time'  => 'End of the month',
				'title' => 'Udhaar in a notebook',
				'text'  => '“Write it down, I’ll pay on the 1st.” The entry has a name and an amount, but no bill number and no items. By month-end nobody is sure what was taken.',
				'cost'  => 'credit you can’t chase with confidence.',
			),
			array(
				'time'  => '10 pm, closing',
				'title' => 'The drawer doesn’t add up',
				'text'  => 'Cash, card and UPI were taken by three people across two counters. The total is short and there’s no way to see where.',
				'cost'  => 'money you can’t trace, and trust between you and your staff.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Every leak at the counter, and what plugs it',
			'intro'   => 'Read it like a register: each leak, then what plugs it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Walk-outs while staff search',
				'how'       => 'Customers ask by brand, salt or generic name; staff don’t know where you’ve filed it.',
				'plug_html' => '<b>Search by brand or generic name</b>, or scan the strip with a USB barcode reader. F2 opens search from anywhere on the bill.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'The wrong batch goes out',
				'how'       => 'The newest strip is sold while an older batch sits behind it until it expires.',
				'plug_html' => '<b>FEFO batch selection:</b> when you add an item, the batch that expires first fills in. Staff can still pick another.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'Credit nobody can trace',
				'how'       => 'Udhaar written in a notebook, without bill or items.',
				'plug_html' => '<b>Credit as a payment method</b>, with the patient’s purchase history and dues shown on the bill.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'Discounts given too freely',
				'how'       => 'Each staff member decides the discount at the counter.',
				'plug_html' => '<b>Discount verification</b> adds a check on item discounts. Ask us to show how it works in your demo.',
				'feat'      => 'feat_discount_verification',
				'plan'      => '',
			),
			array(
				'leak'      => 'Bills changed after the fact',
				'how'       => 'A saved bill is edited later and nobody can tell.',
				'plug_html' => '<b>Lock invoices</b> so saved bills can’t be quietly changed.',
				'feat'      => 'feat_sale_lock_invoices',
				'plan'      => '',
			),
			array(
				'leak'      => 'Refills that go elsewhere',
				'how'       => 'A regular on monthly medicine forgets, then buys wherever they are.',
				'plug_html' => '<b>Medicine reminders</b> nudge the patient to come back to your counter.',
				'feat'      => 'feat_medicine_reminder',
				'plan'      => '',
			),
			array(
				'leak'      => 'A drawer that doesn’t match',
				'how'       => 'Several people take payments across a shift.',
				'plug_html' => '<b>Collection and operator-wise collection reports</b> split the day by payment method and by staff member.',
				'feat'      => '',
				'plan'      => '',
			),
		),
		'leaks_example'   => array(
			'title'        => 'Two walk-outs a day',
			'lines'        => array(
				array(
					'label' => 'Customers who leave the queue',
					'value' => '2 a day',
					'op'    => 'auto',
				),
				array(
					'label' => 'Average bill',
					'value' => '₹350',
					'op'    => 'auto',
				),
				array(
					'label' => 'Days open',
					'value' => '30',
					'op'    => 'auto',
				),
			),
			'result_label' => 'Sales lost in a month',
			'result_value' => '₹21,000',
			'note'         => 'Change the numbers to match your store. That’s sales, not profit, and it doesn’t count the customer who stops coming back.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How a bill works',
			'title'   => 'Search, pick, take payment, save',
			'intro'   => 'Four moves for a counter bill. Built-in walkthroughs show new staff each screen.',
		),
		'steps'           => array(
			array(
				'title' => 'Find the medicine',
				'html'  => 'Type a brand or generic name, or scan the barcode. Search opens with <b>F2</b>, so hands stay on the keyboard.',
				'meta'  => 'Brand, generic or barcode',
			),
			array(
				'title' => 'The batch fills in',
				'html'  => 'The batch that expires first is selected automatically (FEFO), with its expiry and MRP. Change it if the customer needs another.',
				'meta'  => 'Nearest expiry first',
			),
			array(
				'title' => 'Patient and payment',
				'html'  => 'Add the patient (OPD or IPD, search by UHID), doctor name and GST number if needed. Take cash, card, UPI or credit.',
				'meta'  => 'Cash · card · UPI · credit',
			),
			array(
				'title' => 'Save, print or share',
				'html'  => 'Choose <b>Done</b> or <b>Save &amp; print</b>[rx_plan feat="feat_sale_draft" before=", or save a draft (" after=")"]. Bills print on thermal or A4/A5, or go out on WhatsApp, SMS or email.',
				'meta'  => 'Ctrl+S saves the bill',
			),
		),
		'keys'            => array(
			array(
				'key'  => 'F2',
				'what' => 'open item search',
			),
			array(
				'key'  => 'Ctrl + S',
				'what' => 'save the bill',
			),
		),

		'caps_head'       => array(
			'eyebrow' => 'What’s on the counter screen',
			'title'   => 'Everything a medicine bill needs',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-search',
				'title' => 'Brand and generic search',
				'html'  => 'Find items by brand or generic name, so substitutes on your shelf are easy to see.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-scan',
				'title' => 'USB barcode readers',
				'html'  => 'Scan the strip to add it to the bill instead of searching for it.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-alert',
				'title' => 'FEFO batch selection',
				'html'  => 'The earliest-expiry batch fills in first, with expiry and MRP on the line.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'Cash, card, UPI or credit',
				'html'  => 'Record how each bill was paid, with patient history and dues on screen.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-chat',
				'title' => 'Bills on WhatsApp, SMS or email',
				'html'  => 'Share the bill instead of printing it. Sending uses message credits; included credits vary by plan.',
				'feat'  => 'feat_bill_sharing',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-printer',
				'title' => 'Thermal, A4 and A5 bills',
				'html'  => 'Print in the format your counter uses.[rx_plan feat="feat_label_print" before=" Barcode label printing for stock is on " after="."]',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Draft bills',
				'html'  => 'Park a bill while the customer decides, then finish it later.',
				'feat'  => 'feat_sale_draft',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-lock',
				'title' => 'Lock invoices',
				'html'  => 'Saved bills can’t be edited quietly afterwards.',
				'feat'  => 'feat_sale_lock_invoices',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-users',
				'title' => 'Patient vouchers and reminders',
				'html'  => 'Patient vouchers for payments against credit, and medicine reminders for regulars.',
				'feat'  => 'feat_patient_voucher',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'Before you go live',
			'title'   => 'Set up a counter that stays fast in the rush',
			'intro'   => 'Practical steps from how busy stores run, not product rules. Do them once and the counter stays quick.',
		),
		'guide'           => array(
			array(
				'title' => 'Clean up item names first',
				'html'  => 'Duplicate items (“Paracetamol 500”, “PCM 500 tab”) slow search more than anything else. Merge them before you import, and keep the generic name filled in.',
			),
			array(
				'title' => 'Give every staff member their own login',
				'html'  => 'Shared logins make the operator-wise collection report useless. One person, one login, one role.',
			),
			array(
				'title' => 'Test your printer on day one',
				'html'  => 'Print a few real bills in thermal and A5 before the first rush, and check the paper width and your store details.',
			),
			array(
				'title' => 'Plug in the barcode reader',
				'html'  => 'Most USB barcode readers type into the search box like a keyboard. Scan a few strips and check they find the right item.',
			),
			array(
				'title' => 'Decide your credit rules',
				'html'  => 'Agree who can bill on credit and up to how much, before customers start asking. Then check dues on the patient ledger weekly.',
			),
		),
		'guide_note'      => 'Billing runs in a web browser on the computer you already use at the counter.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'We don’t have customer quotes to show yet, so here’s a better test. Bring this list to a 30-minute demo and tick each one off with medicines from your own shelf.',
		),
		'proof'           => array(
			array( 'text' => 'Find a medicine by its generic name, then by brand' ),
			array( 'text' => 'Add an item with two batches and watch the earlier expiry fill in' ),
			array( 'text' => 'Scan a strip with a USB barcode reader' ),
			array( 'text' => 'Bill a regular on credit and see their dues on the bill' ),
			array( 'text' => 'Print the same bill on thermal and A5' ),
			array( 'text' => 'Send the bill to your own phone on WhatsApp' ),
			array( 'text' => 'Open today’s operator-wise collection report' ),
		),

		'related'         => array( 'expiry', 'inventory', 'purchase' ),
		'related_title'   => '',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Can staff bill by generic name?',
				'a_html'    => 'Yes. Search by brand or generic name, or scan the barcode with a USB reader. F2 opens search from the bill.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Does it pick the batch by itself?',
				'a_html'    => 'Yes. When you add an item, the batch that expires first is selected automatically (FEFO). Staff can still choose another batch.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Which payment methods can I record?',
				'a_html'    => 'Cash, card, UPI and credit (udhaar), with the patient’s purchase history and dues on the bill.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I send bills on WhatsApp?',
				'a_html'    => 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits, and each plan includes some. See [rx_link key="pricing"]pricing[/rx_link].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Will it work with my printer and barcode scanner?',
				'a_html'    => 'Bills print in thermal and A4/A5 formats, and USB barcode readers work for search. Tell us your printer model during the demo and we’ll check it.',
				'confirmed' => true,
			),
			// Shown only when the free plan has a sales bill limit (empty answer = item skipped).
			array(
				'q'         => 'How many bills can I make on the free plan?',
				'a_html'    => '[rx_if cond="free_sales_limit"]The [rx_free_plan field="name"] plan includes [rx_free_plan field="sales"] sales bills a month for one user. Paid plans have unlimited bills. See [rx_link key="pricing"]pricing[/rx_link].[/rx_if]',
				'confirmed' => true,
			),
			array(
				'q'         => 'What if the internet goes down during billing?',
				'a_html'    => 'It runs in your web browser. [Confirm offline behaviour before publishing.]',
				'confirmed' => false,
			),
		),
	),
);
