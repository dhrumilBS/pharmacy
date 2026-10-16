<?php
/**
 * Import data: the Contact page (PHP_Site contact.php).
 *
 * Fields: group_rx_contact (templates/contact.php). Phone numbers, the help
 * centre and calendar links and the address come from Site_Settings through
 * [rx_tel], [rx_link setting=""] and [rx_site]. The map link and the social
 * profiles come from Site_Settings in the template.
 *
 * The calendar card's "Or email …" line has no copy here: no shortcode builds
 * a mailto: link, and the line shows only when Site_Settings `email` is set
 * (it is empty today, so the PHP_Site prints nothing). The template prints
 * it from that setting.
 *
 * seo.desc is contact.php's $description verbatim (it writes the two phone
 * numbers as literal text).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'contact.php',
	'key'      => 'contact',
	'title'    => 'Contact',
	'slug'     => 'contact',
	'template' => 'templates/contact.php',
	'seo'      => array(
		'title'   => 'Contact us: sales, support and office %%sep%% %%sitename%%',
		'desc'    => 'Contact us: sales +91 97148 74435, pharmacy support +91 89808 80819, help centre and our Surat office.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'         => array(
			'crumb'   => 'Contact',
			'parent'  => 'Company',
			'eyebrow' => 'Contact',
			'h1'      => 'Talk to a person, not a ticket queue',
			'lead'    => 'Call us, book a demo, or visit our office. Pick whatever is quickest for you.',
		),

		// Sales, support and calendar cards.
		'cards'        => array(
			array(
				'icon'           => 'i-phone',
				'title'          => 'Sales',
				'text'           => 'Plans, pricing, chains and hospital pharmacies.',
				'link_html'      => '[rx_tel key="sales" class="big-link"]',
				'extra_html'     => '[rx_link key="demo" class="link-arrow" cta="demo"]Book a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>[/rx_link]',
				'note'           => '',
				'note_confirmed' => true,
			),
			array(
				'icon'           => 'i-headset',
				'title'          => 'Support',
				'text'           => 'For stores already using our software.',
				'link_html'      => '[rx_tel key="support" class="big-link"]',
				'extra_html'     => '[rx_link setting="help_url" class="link-arrow"]Open the help centre <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>[/rx_link]',
				'note'           => 'Support hours: [confirm before publishing]',
				'note_confirmed' => false,
			),
			array(
				'icon'           => 'i-calendar',
				'title'          => 'Pick a time',
				'text'           => 'Choose a slot for a call with our team.',
				'link_html'      => '[rx_link setting="calendly" class="big-link" rel="noopener"]Open the calendar[/rx_link]',
				'extra_html'     => '',
				'note'           => '',
				'note_confirmed' => true,
			),
		),

		// Office: the address line, the Google Maps button and the social links.
		'office_head'  => array(
			'eyebrow' => 'Office',
			'title'   => 'Visit us in Surat',
			'intro'   => '[rx_site key="address"]',
		),
		'map_label'    => 'Open in Google Maps',
		'social_title' => 'Follow us',

		// Call-back form beside the office block.
		'form_title'   => 'Send us your details',
		'form_sub'     => 'We’ll call you back. For urgent support, please phone instead.',
	),
);
