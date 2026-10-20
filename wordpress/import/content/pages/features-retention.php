<?php
/**
 * Import data: the "Customer retention" feature page (/features/retention/).
 *
 * PHP_Site source: features/retention.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The reminder plan ($rem) and the rows' plans are [rx_plan] / `feat`,
 * plan_credits_text() is [rx_credits_text] (inside [rx_if cond="pricing"],
 * so the sentence stays whole without pricing), and url() links are
 * [rx_link]. The worked example keeps today's illustrative numbers as text
 * (10 × ₹900 × 12 = ₹1,08,000).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/retention.php',
	'key'      => 'retention',
	'title'    => 'Customer retention',
	'slug'     => 'retention',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy customer retention: reminders and loyalty %%sep%% %%sitename%%',
		'desc'    => 'Bring regular patients back: bills on WhatsApp, SMS or email, medicine reminders, loyalty cards, and patient history and dues on every bill.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Customer retention',
			'parent'  => 'Features',
			'eyebrow' => 'Customer retention',
			'h1'      => 'Bring regulars back for their next refill',
			'lead'    => 'Share bills on WhatsApp, SMS or email, send medicine reminders, keep loyalty cards and take online orders for pick-up or delivery. Every bill shows the patient’s history and dues, so your counter remembers them.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See it in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'Bills on WhatsApp, SMS or email' ),
			array( 'text' => '[rx_plan feat="feat_medicine_reminder" before="Medicine reminders (" after=")"]' ),
			array( 'text' => 'Online orders for pick-up or delivery' ),
		),
		'screen'          => 'v2-08-retention',
		'screen_alt'      => 'Patient profile with purchase history, medicine reminders, loyalty card, online orders and bill sharing',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'How regular patients slip away',
			'intro'   => 'Regulars on monthly medicine are the steadiest part of a medical store’s sales. They rarely leave in one go.',
		),
		'scenes'          => array(
			array(
				'time'  => 'First week of the month',
				'title' => 'A regular doesn’t come in',
				'text'  => 'A patient on monthly BP medicine runs out while near another chemist, and buys there.',
				'cost'  => 'that month’s sale, and often the months after.',
			),
			array(
				'time'  => '8 pm, a phone call',
				'title' => '“Can you keep it ready?”',
				'text'  => 'A customer wants their medicines packed for pick-up. The order goes on a sticky note that falls behind the counter.',
				'cost'  => 'a forgotten order, and a customer who stops calling.',
			),
			array(
				'time'  => 'At the counter',
				'title' => '“I lost the bill”',
				'text'  => 'A patient needs last month’s bill for a claim. Someone digs through the bill book while the queue waits.',
				'cost'  => 'counter time, and a frustrated patient.',
			),
			array(
				'time'  => 'Festival season',
				'title' => 'Ten years loyal, treated like a walk-in',
				'text'  => 'The chain store nearby gives points. Your oldest regulars get exactly what a stranger gets.',
				'cost'  => 'loyalty you’ve earned but don’t use.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where regulars leak away, and what keeps them',
			'intro'   => 'Each leak, then what the software does about it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Refills bought elsewhere',
				'how'       => 'Patients on regular medicine forget, then buy wherever they are.',
				'plug_html' => '<b>Medicine reminders</b> nudge them to come back to your counter.',
				'feat'      => 'feat_medicine_reminder',
				'plan'      => '',
			),
			array(
				'leak'      => 'Lost paper bills',
				'how'       => 'Patients ask for copies weeks later.',
				'plug_html' => '<b>Bills shared on WhatsApp, SMS or email</b>, so the patient keeps their own copy. Uses message credits.',
				'feat'      => 'feat_bill_sharing',
				'plan'      => '',
			),
			array(
				'leak'      => 'Phone orders forgotten',
				'how'       => 'Orders for pick-up or delivery live on paper.',
				'plug_html' => '<b>Online sales orders</b> for pick-up or delivery, in the software instead of on paper.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => 'Regulars treated like walk-ins',
				'how'       => 'Long-standing customers get nothing for staying.',
				'plug_html' => '<b>Loyalty cards</b> for the customers you want to keep.',
				'feat'      => '',
				'plan'      => '',
			),
			array(
				'leak'      => '“What did you take last time?”',
				'how'       => 'Staff guess the brand or strength from memory.',
				'plug_html' => '<b>Purchase history on the bill</b> when you pick the patient.',
				'feat'      => '',
				'plan'      => 'Every plan',
			),
			array(
				'leak'      => 'Credit customers who drift',
				'how'       => 'Dues pile up and the customer starts avoiding the store.',
				'plug_html' => '<b>Dues shown on every bill</b>, a <b>patient ledger</b> and <b>patient vouchers</b> for part payments.',
				'feat'      => 'feat_patient_voucher',
				'plan'      => '',
			),
			array(
				'leak'      => 'Messages that cost more than planned',
				'how'       => 'Every bill sent and reminder uses a credit.',
				'plug_html' => '<b>Included message credits</b> on every plan, and more as an add-on when you need them.',
				'feat'      => '',
				'plan'      => 'Varies by plan',
			),
		),
		'leaks_example'   => array(
			'title'        => 'Ten regulars drift away',
			'lines'        => array(
				array(
					'label' => 'Regular patients who move to another chemist',
					'value' => '10',
					'op'    => 'auto',
				),
				array(
					'label' => 'Their average monthly bill',
					'value' => '₹900',
					'op'    => 'auto',
				),
				array(
					'label' => 'Months in a year',
					'value' => '12',
					'op'    => 'auto',
				),
			),
			'result_label' => 'Sales lost in a year',
			'result_value' => '₹1,08,000',
			'note'         => 'Change the numbers to match your store. That’s sales, not profit, and it doesn’t count the family members who follow them.',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How it works',
			'title'   => 'It starts with the patient on the bill',
			'intro'   => 'Everything here builds on one habit: picking the patient when you bill.',
		),
		'steps'           => array(
			array(
				'title' => 'Pick the patient at billing',
				'html'  => 'Pick the patient on the bill (OPD or IPD, with UHID search). Their purchase history and dues show on screen.',
				'meta'  => 'History on every bill',
			),
			array(
				'title' => 'Send the bill',
				'html'  => 'Share it on WhatsApp, SMS or email instead of printing, or as well as printing.',
				'meta'  => 'Uses message credits',
			),
			array(
				'title' => 'Remind regulars',
				'html'  => 'Medicine reminders bring patients on regular medicine back for their refill[rx_plan feat="feat_medicine_reminder" before=" (" after=")"].',
				'meta'  => 'For regular medicine',
			),
			array(
				'title' => 'Orders and loyalty',
				'html'  => 'Take online orders for pick-up or delivery, and give loyalty cards to the customers you want to keep.',
				'meta'  => 'Pick-up or delivery',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'Retention tools',
			'title'   => 'Small things that keep customers coming back',
			'intro'   => 'Where a feature depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-calendar',
				'title' => 'Medicine reminders',
				'html'  => 'For patients on regular medicine, so the refill comes back to you.',
				'feat'  => 'feat_medicine_reminder',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-chat',
				'title' => 'Bills on WhatsApp, SMS or email',
				'html'  => 'The patient keeps a copy; you print less. Uses message credits.',
				'feat'  => 'feat_bill_sharing',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-store',
				'title' => 'Online orders',
				'html'  => 'Sales orders for pick-up or delivery, kept in the software instead of on paper.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-shield',
				'title' => 'Loyalty cards',
				'html'  => 'Recognise the customers who have stayed with you.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-users',
				'title' => 'Patient history and dues',
				'html'  => 'Shown on the bill when you pick the patient.',
				'feat'  => '',
				'plan'  => 'Every plan',
			),
			array(
				'icon'  => 'i-register',
				'title' => 'Patient ledger and vouchers',
				'html'  => 'Credit, part payments and dues per patient.',
				'feat'  => 'feat_patient_voucher',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-mail',
				'title' => 'Included message credits',
				'html'  => 'Each plan includes some[rx_if cond="pricing"]: [rx_credits_text][/rx_if]. More as an add-on.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-hospital',
				'title' => 'For hospital pharmacies',
				'html'  => 'IPD and OPD patients with UHID search on the same counter screen.',
				'feat'  => '',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-receipt',
				'title' => 'Doctor and GST details',
				'html'  => 'Doctor name and the patient’s GST number on the bill when needed.',
				'feat'  => '',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'Good practice',
			'title'   => 'Keeping regulars without annoying them',
			'intro'   => 'Messages work when they’re useful and expected. A few habits keep them that way.',
		),
		'guide'           => array(
			array(
				'title' => 'Ask before you message',
				'html'  => 'Ask the patient if they’d like their bill and reminders on WhatsApp, and note it. People answer messages they agreed to.',
			),
			array(
				'title' => 'Get the mobile number right on the first bill',
				'html'  => 'One wrong digit and every bill and reminder after that goes nowhere.',
			),
			array(
				'title' => 'Use reminders for regular medicine only',
				'html'  => 'BP, diabetes and thyroid refills are welcome reminders. Promotions every week are not.',
			),
			array(
				'title' => 'Keep messages short and from the store’s name',
				'html'  => 'Patients should know who it’s from at a glance.',
			),
			array(
				'title' => 'Talk about dues in person',
				'html'  => 'Check the patient ledger weekly, then raise dues quietly at the counter, not in front of the queue.',
			),
			array(
				'title' => 'Reward the long-standing regulars',
				'html'  => 'A loyalty card for someone who has bought from you for years says more than any discount.',
			),
		),
		'guide_note'      => 'General good practice. Follow the messaging and privacy rules that apply to your store.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring this list to a 30-minute demo. We’ll try each one with a sample patient.',
		),
		'proof'           => array(
			array( 'text' => 'Pick a patient and see their purchase history on the bill' ),
			array( 'text' => 'Send the bill to your own phone on WhatsApp' ),
			array( 'text' => 'Set up a medicine reminder for a regular patient' ),
			array( 'text' => 'Take an online order for pick-up' ),
			array( 'text' => 'Give a patient a loyalty card' ),
			array( 'text' => 'See how many message credits your plan includes' ),
		),

		'related'         => array( 'reports', 'gst', 'security' ),
		'related_title'   => 'Also in the back office',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Can I send bills on WhatsApp?',
				'a_html'    => 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits, and each plan includes some. See [rx_link key="pricing"]pricing[/rx_link].',
				'confirmed' => true,
			),
			array(
				'q'         => 'How do medicine reminders work?',
				'a_html'    => 'Medicine reminders bring patients on regular medicine back for their refill[rx_plan feat="feat_medicine_reminder" before=" (" after=")"]. [Confirm how reminders are set up and how they reach the patient.]',
				'confirmed' => false,
			),
			array(
				'q'         => 'Can customers order for pick-up or delivery?',
				'a_html'    => 'Yes. Online sales orders can be for pick-up or delivery.',
				'confirmed' => true,
			),
			array(
				'q'         => 'How many message credits are included?',
				'a_html'    => 'Each plan includes some[rx_if cond="pricing"]: [rx_credits_text][/rx_if]. You can buy more as an add-on. See [rx_link key="pricing"]pricing[/rx_link].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I see what a patient bought before?',
				'a_html'    => 'Yes. When you pick the patient on a bill, their purchase history and dues show on screen.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Is patient information kept safe?',
				'a_html'    => 'Each staff member has their own login and role, with more controls on higher plans. See [rx_link key="security"]security &amp; roles[/rx_link] and [rx_link key="data-security"]data &amp; security[/rx_link].',
				'confirmed' => true,
			),
		),
	),
);
