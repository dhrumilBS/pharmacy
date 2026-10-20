<?php
/**
 * Import data: the Book a demo page (PHP_Site demo.php).
 *
 * Fields: group_rx_demo (templates/demo.php, dark .final section with the
 * demo form; no CTA band). The calendar link and the sales phone link come
 * from Site_Settings through [rx_link setting="calendly"] and [rx_tel].
 * demo.php passes no form title or text, so form_title and form_sub stay
 * empty and the form shows its own defaults. Text that demo.php writes
 * literally (plan names included) stays literal.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'demo.php',
	'key'      => 'demo',
	'title'    => 'Book a demo',
	'slug'     => 'demo',
	'template' => 'templates/demo.php',
	'seo'      => array(
		'title'   => 'Book a free pharmacy software demo %%sep%% %%sitename%%',
		'desc'    => 'See our pharmacy software working with medicines from your own shelf: billing, expiry report and purchase entry. Free demo on a call.',
		'noindex' => false,
	),
	'fields'   => array(
		'crumb'           => 'Book a demo',
		'eyebrow'         => 'Free demo',
		'title'           => 'See it working with medicines from your own shelf',
		'intro'           => 'A short demo on a call, at a time that suits you. No pressure, no obligation.',

		// What happens next (the list carries data-confirm today).
		'steps'           => array(
			array(
				'title' => 'We call you within one working day',
				'text'  => 'To understand your store and current software.',
			),
			array(
				'title' => 'You see your own items in the demo',
				'text'  => 'Billing, expiry report and purchase entry, the way you’d use them.',
			),
			array(
				'title' => 'You pick the right plan',
				'text'  => 'From the free Starter plan to Enterprise for chains and hospitals.',
			),
		),
		'steps_confirmed' => false,

		'alt_html'        => 'Prefer to choose a time yourself? [rx_link setting="calendly" style="color:#fff" rel="noopener"]Pick a slot on our calendar[/rx_link], or call sales on [rx_tel key="sales" style="color:#fff"].',

		// Empty: the demo form's default heading and text.
		'form_title'      => '',
		'form_sub'        => '',
	),
);
