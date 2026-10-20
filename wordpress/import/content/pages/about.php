<?php
/**
 * Import data: the About page (PHP_Site about.php).
 *
 * Fields: group_rx_about (templates/about.php). Phone links and the address
 * come from Site_Settings through [rx_tel] and [rx_site]. Text that about.php
 * writes literally (plan names and the facts included) stays literal.
 *
 * why_html keeps one <p> per paragraph with the inline styles about.php puts
 * on them, so the section looks the same.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'about.php',
	'key'      => 'about',
	'title'    => 'About',
	'slug'     => 'about',
	'template' => 'templates/about.php',
	'seo'      => array(
		'title'   => 'About us: the team behind our pharmacy software %%sep%% %%sitename%%',
		'desc'    => 'Pharmacy software built by a Surat-based team that also builds hospital and EMR software, for medical stores, pharmacy chains and hospital pharmacies.',
		'noindex' => false,
	),
	'fields'   => array(
		'hero'          => array(
			'crumb'   => 'About',
			'parent'  => 'Company',
			'eyebrow' => 'About us',
			'h1'      => 'Pharmacy software from people who build healthcare software every day',
			'lead'    => 'We also build hospital, EMR and clinic software. We built this one for the person standing at the counter.',
		),

		// Why we built it, with the dashboard screen.
		'why_title'     => 'Why we built it',
		'why_html'      => '<p style="margin-top:14px;color:var(--text-2);font-size:17px">Most medical stores already use something: a desktop package bought years ago, a general billing app, or registers. What owners tell us is the same: expired strips found too late, slow billing in the evening rush, distributor bills typed line by line at night, and month-end GST that eats a weekend.</p>'
			. "\n" . '<p style="margin-top:12px;color:var(--text-2);font-size:17px">Our software is the answer: batch-wise billing that sells the nearest expiry first, purchase entry by CSV or OCR, clean GST reports, and a free plan so you can try it without risk.</p>',
		'why_image'     => 'v2-02-dashboard',
		'why_image_alt' => 'Sample pharmacy dashboard',

		// What we stand for.
		'promises_head' => array(
			'eyebrow' => 'What we stand for',
			'title'   => 'Four promises we design around',
		),
		'promises'      => array(
			array(
				'icon'  => 'i-users',
				'title' => 'Built for the counter',
				'text'  => 'Every screen is designed for a busy medical store: keyboard shortcuts, batch auto-selection and in-app walkthroughs for new staff.',
			),
			array(
				'icon'  => 'i-rupee',
				'title' => 'Prices on the page',
				'text'  => 'Starter is free forever. Basic and Advance have published yearly prices. No guessing what year two will cost.',
			),
			array(
				'icon'  => 'i-upload',
				'title' => 'Switching without stopping',
				'text'  => 'Set up on the free plan, import your items and stock from CSV, and go live on the day you choose.',
			),
			array(
				'icon'  => 'i-hospital',
				'title' => 'Connected to the hospital',
				'text'  => 'For hospital pharmacies, prescriptions from the hospital’s EMR arrive at the counter as tokens, and patient ABHA records can be linked through ABDM.',
			),
		),

		// At a glance.
		'facts_head'    => array(
			'eyebrow' => 'At a glance',
			'title'   => 'The software today',
		),
		'facts'         => array(
			array(
				'value' => '4 plans',
				'label' => 'Starter (free), Basic, Advance and Enterprise',
			),
			array(
				'value' => '25+ reports',
				'label' => 'Sales, purchase, GST, margin, expiry, collections and more',
			),
			array(
				'value' => 'ABDM',
				'label' => 'Integrated: create or link a patient’s ABHA',
			),
			array(
				'value' => 'Surat, Gujarat',
				'label' => 'Where our team is based',
			),
		),

		// Sales, support and visit cards.
		'cards'         => array(
			array(
				'title'     => 'Talk to sales',
				'text'      => 'Questions about plans, chains or hospital pharmacies.',
				'link_html' => '[rx_tel key="sales" class="big-link"]',
			),
			array(
				'title'     => 'Get support',
				'text'      => 'Already a customer? Call support or open the help centre.',
				'link_html' => '[rx_tel key="support" class="big-link"]',
			),
			array(
				'title'     => 'Visit us',
				'text'      => '[rx_site key="address"]',
				'link_html' => '[rx_link key="contact" class="link-arrow" style="margin-top:12px"]All contact details <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>[/rx_link]',
			),
		),
	),
);
