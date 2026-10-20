<?php
/**
 * Import data: post "Staff logins and roles: a security checklist".
 *
 * Source: blog/staff-logins-roles.php (article and FAQ) and its entry in
 * partials/data/blog.php (title, summary, topic, date, read time, cover),
 * copied exactly. Each <h2>, run of literal HTML and bk_*() call is one
 * `body` row, in order; the FAQ is the blog_end() list (all confirmed).
 *
 * Live values, as on the PHP_Site:
 * - feature_plan_label() for feat_role_management, feat_2fa,
 *   feat_ip_whitelist and feat_activity_report → [rx_plan feat="…"], with
 *   the surrounding words in before/after (printed only when the plan is
 *   known) or fallback="higher plans" where the PHP_Site uses $tfa ?: ….
 * - plan_users_text() → [rx_users_text].
 * The role table is an illustrative setup and stays literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/staff-logins-roles.php',
	'type'   => 'post',
	'title'  => 'Staff logins and roles: a security checklist for pharmacy owners',
	'slug'   => 'staff-logins-roles',
	'date'   => '2026-09-11',
	'status' => 'publish',
	'seo'    => array(
		'title'   => 'Staff logins and roles: pharmacy security checklist %%sep%% %%sitename%%',
		'desc'    => 'Most store problems aren’t break-ins, they’re shared passwords. How to set up logins and roles, and the ten-minute routine for the day someone leaves.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'Most store problems aren’t break-ins, they’re shared passwords. How to set up logins and roles, and the ten-minute routine for the day someone leaves.',
		'topic'        => 'staff',
		'read_time'    => 6,
		'featured'     => false,
		'cover_screen' => 'v2-09-security',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'One person, one login. Shared logins make every report about “who did it” useless.' ),
					array( 'html' => 'Give each role only the screens it needs, starting from nothing and adding.' ),
					array( 'html' => 'Two-step OTP login and IP whitelisting close most of the remaining gaps.' ),
					array( 'html' => 'The day someone leaves, force logout and remove their access before they walk out.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>When owners think about security, they picture a break-in. In practice, the problems are quieter: a shared password that an ex-employee still knows, a discount nobody can explain, a bill edited after the day closed. Most of them are fixed by setting up logins properly once.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Why shared logins cost you',
			),
			array(
				'acf_fc_layout' => 'compare',
				'title'         => '',
				'a_name'        => 'One shared login',
				'a_tag'         => 'Common',
				'b_name'        => 'A login per person',
				'b_tag'         => 'Recommended',
				'winner'        => 'b',
				'rows'          => array(
					array(
						'label'  => 'Who gave that discount?',
						'a_html' => 'Unknown',
						'b_html' => 'Shown on the bill and in the activity report',
					),
					array(
						'label'  => 'Collections by staff member',
						'a_html' => 'Impossible',
						'b_html' => 'Operator-wise collection report',
					),
					array(
						'label'  => 'Someone leaves',
						'a_html' => 'Change the password for everyone',
						'b_html' => 'Remove one user',
					),
					array(
						'label'  => 'What new staff can see',
						'a_html' => 'Everything',
						'b_html' => 'Only their screens',
					),
				),
				'verdict_html'  => 'Give everyone their own login from day one. It’s the single change that makes every other control work.',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Set up roles in an afternoon',
			),
			array(
				'acf_fc_layout' => 'steps',
				'items'         => array(
					array(
						'title' => 'List who does what',
						'html'  => '<p>Write down each person and the jobs they do: billing, purchase entry, reports, settings.</p>',
						'when'  => '15 minutes',
					),
					array(
						'title' => 'Create a few roles',
						'html'  => '<p>Start from nothing and add screens to each role.[rx_plan feat="feat_role_management" before=" Role management is on " after="."]</p>',
						'when'  => '30 minutes',
					),
					array(
						'title' => 'Add each person as a user',
						'html'  => '<p>One login each, with the role that matches their job. Users per plan: [rx_users_text].</p>',
						'when'  => '15 minutes',
					),
					array(
						'title' => 'Turn on the extra locks',
						'html'  => '<p>Switch on two-step OTP login[rx_plan feat="feat_2fa" before=" (" after=")"], and allow logins only from your store’s IP addresses[rx_plan feat="feat_ip_whitelist" before=" (" after=")"].</p>',
						'when'  => '10 minutes',
					),
				),
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'A starting set of roles',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Screen\nCashier\nPharmacist\nPurchase clerk\nManager",
				'rows'          => array(
					array( 'cells' => "<b>Sale</b>\n✓\n✓\n—\n✓" ),
					array( 'cells' => "<b>Purchase entry and import</b>\n—\n—\n✓\n✓" ),
					array( 'cells' => "<b>Stock and short book</b>\n—\n✓\n✓\n✓" ),
					array( 'cells' => "<b>Reports and margins</b>\n—\n—\n—\n✓" ),
					array( 'cells' => "<b>Item rates and settings</b>\n—\n—\n—\n✓" ),
				),
				'caption'       => 'An illustrative setup. Adjust it to how your store divides the work.',
				'highlight_col' => '4',
				'numeric_cols'  => '',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'tip',
				'title'         => 'Start strict, then loosen',
				'html'          => '<p>It’s easier to add a screen when someone asks for it than to take one away later. Begin each role with the least it needs.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'The day someone leaves',
			),
			array(
				'acf_fc_layout' => 'checklist',
				'title'         => 'Before they walk out',
				'items'         => array(
					array( 'html' => 'Force logout all their sessions' ),
					array( 'html' => 'Remove or deactivate their user' ),
					array( 'html' => 'Change any password they knew, including shared device and Wi-Fi passwords' ),
					array( 'html' => 'Look through their last week in the activity report[rx_plan feat="feat_activity_report" before=" (" after=")"]' ),
					array( 'html' => 'Check the credit they gave against what customers say they owe' ),
					array( 'html' => 'Give their replacement a new login, never the old one' ),
				),
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'caution',
				'title'         => 'Don’t wait until month-end',
				'html'          => '<p>Access left open for a few weeks is how most problems start. Do this on the person’s last day, not when you next have time.</p>',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'security',
				'text'          => 'Own login per user, roles, two-step OTP login, IP whitelisting, force logout and an activity report.',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'inline',
				'text'          => 'How the software handles store data',
				'link_label'    => 'Read data & security',
				'link_key'      => 'data-security',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'Set up a sample store in a demo',
				'text'          => 'We’ll create two roles, log in as each, and show what they can and can’t see.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'Can each staff member have their own login?',
				'a_html'    => 'Yes. Users included: [rx_users_text].',
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
		),
	),
);
