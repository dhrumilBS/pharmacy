<?php
/**
 * Import data: the Pricing page (PHP_Site pricing.php).
 *
 * Fields: group_rx_pricing (templates/pricing.php). Plans, prices, terms,
 * features, statuses and credit numbers come from the Pricing_API at render
 * time; only the copy around them is stored here.
 *
 * - plan_fit: pricing.php's $fit ("who it's for" lines), keyed by API plan id.
 * - feature_labels: pricing.php's $label map (exact API feature names).
 * - FAQ: the free-plan limit answer is wrapped in [rx_if cond="free_sales_limit"]
 *   and the terms answer in [rx_if cond="pricing"], so they render empty (and
 *   faq-list skips them) when the data isn't there, as today's array spread does.
 * - seo.desc is the fallback used when pricing is null: the live description
 *   (today's text with every plan and its yearly price) is built by a
 *   wpseo_metadesc filter from rx_pricing_meta_bits() (task 14.1). No price
 *   is written here.
 * - Plan names that pricing.php writes as literal text ("Enterprise" in the
 *   title and the FAQ) stay literal, as on the PHP_Site.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'pricing.php',
	'key'      => 'pricing',
	'title'    => 'Pricing',
	'slug'     => 'pricing',
	'template' => 'templates/pricing.php',
	'seo'      => array(
		'title'   => 'Pricing: pharmacy software plans from free to Enterprise %%sep%% %%sitename%%',
		'desc'    => 'Simple yearly plans for medical stores. Prices exclude 18% GST. Compare every feature.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'                   => array(
			'crumb'   => 'Pricing',
			'eyebrow' => 'Pricing',
			'h1'      => 'Simple yearly plans. Prices on the page.',
			'lead'    => 'Start free on one counter. Upgrade when you need unlimited bills, more staff or more stores. No setup quotes, no guessing what year two costs.',
		),

		// Term bar.
		'term_label'             => 'Pay for',
		'term_note'              => 'Prices in ₹, exclusive of 18% GST · switch or cancel anytime',

		// "Who it's for" line under each plan name ($fit in pricing.php), by API plan id.
		'plan_fit'               => array(
			array(
				'plan_id' => 1,
				'text'    => 'Try it on one counter',
			),
			array(
				'plan_id' => 3,
				'text'    => 'A single store getting started',
			),
			array(
				'plan_id' => 2,
				'text'    => 'A busy store with more staff',
			),
			array(
				'plan_id' => 5,
				'text'    => 'Chains and hospital pharmacies',
			),
		),

		// Comparison table.
		'compare_head'           => array(
			'eyebrow' => 'Compare plans',
			'title'   => 'Every feature, plan by plan',
			'intro'   => 'Taken from the plan settings in the app. Features marked “uses credits” send messages or scan bills using credits.',
		),
		'feature_labels'         => array(
			array(
				'api_name' => 'Draft',
				'label'    => 'Save bills as draft',
			),
			array(
				'api_name' => 'Bill Limit',
				'label'    => 'Bills per month',
			),
			array(
				'api_name' => 'MRD',
				'label'    => 'MRD (medical records)',
			),
			array(
				'api_name' => '2FA',
				'label'    => 'Two-step login (OTP)',
			),
			array(
				'api_name' => 'Non Moving Item',
				'label'    => 'Non-moving items report',
			),
			array(
				'api_name' => 'Top Selling Item',
				'label'    => 'Top-selling items report',
			),
			array(
				'api_name' => 'Lock Invoices',
				'label'    => 'Lock invoices',
			),
		),

		// Message & OCR credits.
		'credits_head'           => array(
			'eyebrow' => 'Message & OCR credits',
			'title'   => 'What credits are for',
			'intro'   => 'Sending a bill or the daily report on WhatsApp, SMS or email, and scanning a distributor bill with OCR, use credits. Each plan includes some, and you can buy more as an add-on.',
		),
		'credit_rows'            => array(
			array(
				'service' => 'whatsapp',
				'label'   => 'WhatsApp',
			),
			array(
				'service' => 'sms',
				'label'   => 'SMS',
			),
			array(
				'service' => 'email',
				'label'   => 'Email',
			),
			array(
				'service' => 'ocr',
				'label'   => 'OCR scans',
			),
		),
		'credits_note'           => '[Confirm whether included credits are per month or per term.]',
		'credits_note_confirmed' => false,

		// Pricing FAQ (ids pq0…, data-confirm on the answer).
		'faq_head'               => array(
			'eyebrow'    => 'Pricing FAQ',
			'title'      => 'Questions about plans',
			'intro_html' => 'More answers on the [rx_link key="faq"]FAQ page[/rx_link].',
		),
		'faq'                    => array(
			array(
				'q'         => 'Is GST included in the price?',
				'a_html'    => 'No. Prices are in rupees (₹) and exclude GST, currently 18%, which is added at checkout.',
				'confirmed' => true,
			),
			array(
				'q'         => 'What happens when I reach the [rx_free_plan field="name"] limit?',
				'a_html'    => '[rx_if cond="free_sales_limit"][rx_free_plan field="name"] includes [rx_free_plan field="sales"] sales bills[rx_free_plan field="purchase" before=" and " after=" purchase bills"] a month for [rx_free_plan field="users_text"]. When you need more, upgrade to a paid plan for unlimited bills.[/rx_if]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I pay for more than one year?',
				'a_html'    => '[rx_if cond="pricing"]Yes. Paid plans can be bought for [rx_terms_text].[/rx_if]',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I change or cancel my plan?',
				'a_html'    => 'Yes, you can switch or cancel anytime. Refunds are covered by our refund policy.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How is Enterprise priced?',
				'a_html'    => 'Enterprise is quoted for your stores: multi-store management, unlimited users and bills, Tally XML export and priority support. Book a call and we’ll send a quote.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Do you charge for setup or importing data?',
				'a_html'    => 'Importing your items and opening stock from a CSV file is built into the app on every plan. [Confirm whether any assisted setup is charged.]',
				'confirmed' => false,
			),
		),
	),
);
