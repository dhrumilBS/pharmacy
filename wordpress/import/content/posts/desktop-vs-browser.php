<?php
/**
 * Import data: post "Desktop pharmacy software or browser-based".
 *
 * Source: blog/desktop-vs-browser.php (article and FAQ) and its entry in
 * partials/data/blog.php (title, summary, topic, date, read time, cover),
 * copied exactly. Each <h2>, run of literal HTML and bk_*() call is one
 * `body` row, in order; the FAQ is the blog_end() list (all confirmed).
 *
 * Live value: pricing_cost_text() in "What it costs, honestly" becomes
 * [rx_cost_text]. The comparison has no winner ('win' => null → 'none').
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/desktop-vs-browser.php',
	'type'   => 'post',
	'title'  => 'Desktop pharmacy software or browser-based: what actually changes',
	'slug'   => 'desktop-vs-browser',
	'date'   => '2026-09-18',
	'status' => 'publish',
	'seo'    => array(
		'title'   => 'Desktop vs browser-based pharmacy software %%sep%% %%sitename%%',
		'desc'    => 'Weighing a switch? Where your data lives, what happens when a PC fails, how updates and pricing work, and what to ask any vendor.',
		'noindex' => false,
	),
	'fields' => array(
		'dek'          => 'Weighing a switch? Where your data lives, what happens when a PC fails, how updates and pricing work, and what to ask any vendor.',
		'topic'        => 'buying',
		'read_time'    => 8,
		'featured'     => false,
		'cover_screen' => 'v2-02-dashboard',
		'cover_zoom'   => 1.3,
		'cover_focus'  => '24% 33%',
		'body'         => array(
			array(
				'acf_fc_layout' => 'takeaways',
				'items'         => array(
					array( 'html' => 'The real difference is <b>where your data lives</b>: on one PC in the store, or on the vendor’s servers.' ),
					array( 'html' => 'Neither is risk-free. Ask every vendor the same questions and compare the answers.' ),
					array( 'html' => 'Published, yearly pricing is easier to plan for than licence fees plus maintenance by quote.' ),
					array( 'html' => 'Whatever you choose, make sure you can get your data out if you leave.' ),
				),
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Many medical stores run software installed on one PC, bought through a local dealer years ago. Browser-based software works differently. Neither is automatically better. Here’s what actually changes, so you can judge for your store.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'What changes when you move',
			),
			array(
				'acf_fc_layout' => 'compare',
				'title'         => 'Typical desktop software vs typical browser-based software',
				'a_name'        => 'Installed on a PC',
				'a_tag'         => 'Desktop',
				'b_name'        => 'Runs in a web browser',
				'b_tag'         => 'Browser-based',
				'winner'        => 'none',
				'rows'          => array(
					array(
						'label'  => 'Where the data lives',
						'a_html' => 'On the store PC (and whatever backups you keep)',
						'b_html' => 'On the vendor’s servers',
					),
					array(
						'label'  => 'If the PC fails',
						'a_html' => 'Data is only as safe as your last backup',
						'b_html' => 'Log in from another computer',
					),
					array(
						'label'  => 'Checking the store from outside',
						'a_html' => 'Usually needs remote-desktop tools',
						'b_html' => 'Log in from any browser',
					),
					array(
						'label'  => 'Updates',
						'a_html' => 'Installed by a dealer or by you',
						'b_html' => 'Arrive without visiting the store',
					),
					array(
						'label'  => 'If the internet drops',
						'a_html' => 'Billing carries on',
						'b_html' => 'Depends on the product: ask',
					),
					array(
						'label'  => 'Pricing',
						'a_html' => 'Often a licence plus yearly maintenance, by quote',
						'b_html' => 'Often a published yearly or monthly fee',
					),
				),
				'verdict_html'  => 'Browser-based software removes the single-PC risk and lets you see the store from anywhere. In exchange, you depend on the internet and the vendor. Ask about both before you decide.',
			),
			array(
				'acf_fc_layout' => 'note',
				'kind'          => 'caution',
				'title'         => 'Ask about the internet, every time',
				'html'          => '<p>Ask any browser-based vendor what happens at the counter when the connection drops, and how long billing can continue. Get the answer in writing. For our software, ask us in the demo.</p>',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'Eight questions to ask any vendor',
			),
			array(
				'acf_fc_layout' => 'table',
				'head'          => "Question\nWhy it matters\nA good answer sounds like",
				'rows'          => array(
					array( 'cells' => "<b>Where is my data stored?</b>\nYou are responsible for patient records\nA clear answer, in writing" ),
					array( 'cells' => "<b>Can I export all my data if I leave?</b>\nYou shouldn’t be locked in\nYes, in a common format such as Excel or CSV" ),
					array( 'cells' => "<b>What happens if the internet drops?</b>\nThe counter can’t stop\nA specific answer, not “it rarely happens”" ),
					array( 'cells' => "<b>Does it pick the earliest-expiry batch?</b>\nExpiry is your biggest silent loss\nYes, at billing, automatically" ),
					array( 'cells' => "<b>How do I bring my items and stock across?</b>\nSwitching is the risky part\nA sample sheet and a clear import" ),
					array( 'cells' => "<b>Does every user get their own login?</b>\nShared logins hide mistakes\nYes, with roles" ),
					array( 'cells' => "<b>What will year two cost?</b>\nRenewals are where surprises hide\nA published price" ),
					array( 'cells' => "<b>Who do I call at 8 pm?</b>\nProblems don’t keep office hours\nA named number and stated hours" ),
				),
				'caption'       => 'Ask every vendor the same questions and compare the answers side by side.',
				'highlight_col' => '2',
				'numeric_cols'  => '',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'What it costs, honestly',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>Compare the total over three years, not the first invoice: licence, yearly maintenance, per-user charges and any fees to move your data. Our plans are published: [rx_cost_text]</p>',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'inline',
				'text'          => 'Compare every feature by plan',
				'link_label'    => 'See pricing',
				'link_key'      => 'pricing',
			),
			array(
				'acf_fc_layout' => 'heading',
				'text'          => 'If you decide to switch',
			),
			array(
				'acf_fc_layout' => 'text',
				'html'          => '<p>The software matters less than how you switch. Run the old and new systems side by side for a couple of weeks and go live on a quiet day.</p>',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'feature',
				'feature_key'   => 'switch',
				'text'          => 'A four-step switch: set up free, import items and stock by CSV, train staff, and go live on your day.',
			),
			array(
				'acf_fc_layout' => 'cta',
				'kind'          => 'panel',
				'title'         => 'Ask us the eight questions',
				'text'          => 'Book a demo and go through the list above. We’ll answer each one, including the ones we’re still confirming.',
			),
		),
		'faq'          => array(
			array(
				'q'         => 'Do I need to install anything?',
				'a_html'    => 'No. It runs in your web browser. You sign up with your store name and email, verify with an OTP, and log in from any computer.',
				'confirmed' => true,
			),
			array(
				'q'         => 'Can I take my reports with me?',
				'a_html'    => 'Reports, item lists and ledgers export to Excel or CSV.',
				'confirmed' => true,
			),
		),
	),
);
