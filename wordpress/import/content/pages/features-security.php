<?php
/**
 * Import data: the "Security & roles" feature page (/features/security/).
 *
 * PHP_Site source: features/security.php ($fp, rendered by
 * partials/feature-page.php). Fields: group_rx_feature.
 *
 * The plans of two-step login ($tfa), IP whitelisting ($ip), the activity
 * report ($act) and role management are [rx_plan] / `feat`;
 * plan_users_text() is [rx_users_text] (inside [rx_if cond="pricing"], so
 * the sentence stays whole without pricing), and url() links are [rx_link].
 * No worked example and no estimator: the register ends with the demo card.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'features/security.php',
	'key'      => 'security',
	'title'    => 'Security & roles',
	'slug'     => 'security',
	'parent'   => 'features',
	'template' => 'templates/feature.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Pharmacy software security: staff roles, two-step login and IP whitelisting %%sep%% %%sitename%%',
		'desc'    => 'Control who can do what at your medical store: one login per staff member, role management, two-step OTP login, IP whitelisting, force logout, locked invoices and an activity report.',
		'noindex' => false,
	),
	'group'    => 'feature',
	'fields'   => array(
		'hero'            => array(
			'crumb'   => 'Security & roles',
			'parent'  => 'Features',
			'eyebrow' => 'Security & roles',
			'h1'      => 'Every person on their own login, with only the access they need',
			'lead'    => 'Give each staff member a login and a role, turn on two-step OTP login, allow logins only from your store’s IP addresses, and see who did what in the activity report. Written for owners and the administrators who look after the store’s accounts.',
			'meta'    => '',
			'pattern' => 'inherit',
		),
		'cta_demo'        => 'See the controls in a demo',
		'cta_free'        => 'Start free',
		'assure'          => array(
			array( 'text' => 'Own login and role for every user' ),
			array( 'text' => '[rx_plan feat="feat_2fa" before="Two-step OTP login (" after=")"]' ),
			array( 'text' => '[rx_plan feat="feat_ip_whitelist" before="IP whitelisting (" after=")"]' ),
		),
		'screen'          => 'v2-09-security',
		'screen_alt'      => 'Users and security settings with roles, two-step login, IP whitelist, force logout and the activity report',

		'scenes_head'     => array(
			'eyebrow' => 'Sound familiar?',
			'title'   => 'Where store access goes wrong',
			'intro'   => 'Most problems aren’t break-ins. They’re shared passwords and nobody being sure who did what.',
		),
		'scenes'          => array(
			array(
				'time'  => 'A staff member leaves',
				'title' => 'The password leaves with them',
				'text'  => 'Everyone at the counter used one login, and the person who just left still knows it.',
				'cost'  => 'sales and patient records open to someone who no longer works for you.',
			),
			array(
				'time'  => 'Checking a discount',
				'title' => 'Who gave that discount?',
				'text'  => 'One shared login means every bill shows the same name.',
				'cost'  => 'discounts and voids nobody can be asked about.',
			),
			array(
				'time'  => 'Late evening',
				'title' => 'A login from somewhere unexpected',
				'text'  => 'The store is closed, but someone opens the sales report from outside.',
				'cost'  => 'your numbers in hands you didn’t choose.',
			),
			array(
				'time'  => 'Month-end',
				'title' => 'An old bill has changed',
				'text'  => 'A saved invoice was edited after the day closed, and the totals no longer match.',
				'cost'  => 'reports you can’t trust, and an awkward talk with your CA.',
			),
		),

		'leaks_head'      => array(
			'eyebrow' => 'Leak register',
			'title'   => 'Where access leaks, and what closes it',
			'intro'   => 'Each leak, then the control that closes it and the plan that includes it.',
		),
		'leaks'           => array(
			array(
				'leak'      => 'Shared logins',
				'how'       => 'The whole counter uses one username and password.',
				'plug_html' => '<b>One login per person.</b>[rx_if cond="pricing"] Users included: [rx_users_text].[/rx_if]',
				'feat'      => 'feat_user_limit',
				'plan'      => '',
			),
			array(
				'leak'      => 'Everyone sees everything',
				'how'       => 'New staff can open reports, rates and settings on day one.',
				'plug_html' => '<b>Role management:</b> give each role only the screens it needs.',
				'feat'      => 'feat_role_management',
				'plan'      => '',
			),
			array(
				'leak'      => 'Passwords that get passed around',
				'how'       => 'A password alone is easy to share or guess.',
				'plug_html' => '<b>Two-step OTP login</b> on top of the password.',
				'feat'      => 'feat_2fa',
				'plan'      => '',
			),
			array(
				'leak'      => 'Logins from outside the store',
				'how'       => 'Anyone with a password can log in from anywhere.',
				'plug_html' => '<b>IP whitelisting</b> allows logins only from the IP addresses you choose.',
				'feat'      => 'feat_ip_whitelist',
				'plan'      => '',
			),
			array(
				'leak'      => 'Ex-staff still logged in',
				'how'       => 'Someone leaves, but a browser is still signed in.',
				'plug_html' => '<b>Force logout</b> ends their sessions straight away.',
				'feat'      => 'feat_force_logout',
				'plan'      => '',
			),
			array(
				'leak'      => 'Changes nobody owns',
				'how'       => 'A rate, a stock figure or a setting changes and nobody knows who did it.',
				'plug_html' => '<b>Activity report</b> shows who did what, and when.',
				'feat'      => 'feat_activity_report',
				'plan'      => '',
			),
			array(
				'leak'      => 'Bills edited after the fact',
				'how'       => 'Saved invoices are changed to cover a mistake.',
				'plug_html' => '<b>Lock invoices</b>, and <b>discount verification</b> for item discounts.',
				'feat'      => 'feat_sale_lock_invoices',
				'plan'      => '',
			),
		),
		// No worked example on this page: the register ends with the demo card.
		'leaks_example'   => array(
			'title'        => '',
			'lines'        => array(),
			'result_label' => '',
			'result_value' => '',
			'note'         => '',
		),
		'leaks_estimator' => false,

		'steps_head'      => array(
			'eyebrow' => 'How to set it up',
			'title'   => 'An afternoon of setup, then it runs itself',
			'intro'   => 'Do this once when you start, and again whenever someone joins or leaves.',
		),
		'steps'           => array(
			array(
				'title' => 'Add a user for each person',
				'html'  => 'Add every staff member in Users with their own login.[rx_if cond="pricing"] Users included: [rx_users_text].[/rx_if]',
				'meta'  => 'One person, one login',
			),
			array(
				'title' => 'Give each one a role',
				'html'  => 'Decide what a cashier, a pharmacist and a manager should see, and assign roles to match.',
				'meta'  => 'Role management',
			),
			array(
				'title' => 'Turn on the extra locks',
				'html'  => 'Switch on two-step OTP login[rx_plan feat="feat_2fa" before=" (" after=")"] and add your store’s IP addresses to the whitelist[rx_plan feat="feat_ip_whitelist" before=" (" after=")"].',
				'meta'  => 'OTP · IP whitelist',
			),
			array(
				'title' => 'Review now and then',
				'html'  => 'Check the activity report[rx_plan feat="feat_activity_report" before=" (" after=")"] each week, and force logout anyone who leaves on their last day.',
				'meta'  => 'Activity report',
			),
		),
		'keys'            => array(),

		'caps_head'       => array(
			'eyebrow' => 'The controls',
			'title'   => 'Access control that fits a medical store',
			'intro'   => 'Where a control depends on your plan, the plan it starts on is named.',
		),
		'caps'            => array(
			array(
				'icon'  => 'i-users',
				'title' => 'Own login per user',
				'html'  => '[rx_if cond="pricing"]Users included: [rx_users_text].[/rx_if]',
				'feat'  => 'feat_user_limit',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-register',
				'title' => 'Role management',
				'html'  => 'Roles and privileges, so each person sees only their screens.',
				'feat'  => 'feat_role_management',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-mobile',
				'title' => 'Two-step OTP login',
				'html'  => 'An OTP on top of the password at login.',
				'feat'  => 'feat_2fa',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-shield',
				'title' => 'IP whitelisting',
				'html'  => 'Logins allowed only from the IP addresses you choose.',
				'feat'  => 'feat_ip_whitelist',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-x',
				'title' => 'Force logout',
				'html'  => 'End someone’s sessions at once, for example on their last day.',
				'feat'  => 'feat_force_logout',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-file',
				'title' => 'Activity report',
				'html'  => 'Who did what, and when.',
				'feat'  => 'feat_activity_report',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-lock',
				'title' => 'Lock invoices',
				'html'  => 'Saved sale and purchase bills can’t be quietly changed.',
				'feat'  => 'feat_sale_lock_invoices',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'Discount verification',
				'html'  => 'A check on item discounts given at the counter.',
				'feat'  => 'feat_discount_verification',
				'plan'  => '',
			),
			array(
				'icon'  => 'i-stores',
				'title' => 'Central administration',
				'html'  => 'For chains: run linked outlets from one central account.',
				'feat'  => 'feat_central_admin',
				'plan'  => '',
			),
		),

		'guide_head'      => array(
			'eyebrow' => 'A checklist',
			'title'   => 'When a staff member leaves',
			'intro'   => 'The same day someone leaves, run through this. It takes ten minutes and closes most gaps.',
		),
		'guide'           => array(
			array(
				'title' => 'Force logout their sessions',
				'html'  => 'Do it before they walk out, so no browser stays signed in.',
			),
			array(
				'title' => 'Remove their access in Users',
				'html'  => 'Their login should stop working today, not at month-end.',
			),
			array(
				'title' => 'Change any password they knew',
				'html'  => 'Shared device or Wi-Fi passwords included, not only the software.',
			),
			array(
				'title' => 'Look at their last week in the activity report',
				'html'  => 'Check unusual discounts, voids or edits while the details are fresh.',
			),
			array(
				'title' => 'Check the credit they gave',
				'html'  => 'Patient dues they recorded should match what customers say they owe.',
			),
			array(
				'title' => 'Hand their role to someone else',
				'html'  => 'Give the new person their own login, never the old one.',
			),
		),
		'guide_note'      => 'Good practice for any store system. Plan availability for each control is shown above.',

		'proof_head'      => array(
			'eyebrow' => 'Check it yourself',
			'title'   => 'Ask us to show you these in the demo',
			'intro'   => 'Bring whoever manages your staff. We’ll set up a sample store with two roles and try each control.',
		),
		'proof'           => array(
			array( 'text' => 'Add a user with a cashier role' ),
			array( 'text' => 'Log in as that user and see which screens are hidden' ),
			array( 'text' => 'Turn on two-step OTP login' ),
			array( 'text' => 'Add an IP address to the whitelist' ),
			array( 'text' => 'Force logout a user' ),
			array( 'text' => 'Find a change in the activity report' ),
		),

		'related'         => array( 'reports', 'gst', 'retention' ),
		'related_title'   => 'Also in the back office',

		'faq_title'       => '',
		'faq'             => array(
			array(
				'q'         => 'Can each staff member have their own login?',
				'a_html'    => 'Yes.[rx_if cond="pricing"] Users included: [rx_users_text].[/rx_if] See [rx_link key="pricing" hash="compare"]plans[/rx_link].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I limit what staff can see?',
				'a_html'    => 'Yes. Role management gives each role only the screens and actions it needs[rx_plan feat="feat_role_management" before=" (" after=")"].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Is two-step login available?',
				'a_html'    => 'Yes. Two-step OTP login is on [rx_plan feat="feat_2fa" fallback="higher plans"].',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I stop logins from outside the store?',
				'a_html'    => 'Yes. IP whitelisting allows logins only from the IP addresses you choose[rx_plan feat="feat_ip_whitelist" before=" (" after=")"].',
				'confirmed' => true,
			),
			array(
				'q'         => 'What do I do when someone leaves?',
				'a_html'    => 'Force logout their sessions and remove their access in Users the same day, then check their recent activity.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Where is my data stored, and who can see it?',
				'a_html'    => 'See [rx_link key="data-security"]data &amp; security[/rx_link] for how data is handled.',
				'confirmed' => true,
			),
		),
	),
);
