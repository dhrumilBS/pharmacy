<?php
/**
 * Import data: Site_Settings (SCF options page `rx-settings`, field group
 * group_rx_settings), written with rx_write_fields( 'option', 'settings', … ).
 *
 * Returns a flat map of field name => value. Sources in the PHP_Site, copied
 * exactly:
 * - partials/config.php: SITE[] (contacts, links, social), PRICING_API,
 *   PRICING_TTL, PRICING_RETRY and HERO_PATTERN.
 * - contact.php: the Google Maps search text ($map).
 * - partials/footer.php: CTA band title and text, footer blurb, and the
 *   footer note printed after "© {year} {brand}.".
 * - partials/header.php: the promo panel of the Features mega menu.
 *
 * Values left empty on the PHP_Site stay empty here (email, social), so the
 * elements that use them stay hidden. "[Company legal name]" is today's
 * placeholder and stays literal until the legal name is confirmed.
 *
 * URL values are link targets and data sources only; the theme never prints
 * them as text.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(

	// Contacts (SITE in partials/config.php; map text from contact.php).
	'sales_phone'      => '+91 97148 74435',
	'sales_tel'        => '+919714874435',
	'support_phone'    => '+91 89808 80819',
	'support_tel'      => '+918980880819',
	'email'            => '',
	'company'          => '[Company legal name]',
	'address'          => '1st Floor, A - Millenium Point, opp. Gabani Kidney Hospital, Lal Darwaja, Surat, Gujarat 395003',
	'map_query'        => 'Millenium Point, Lal Darwaja, Surat, Gujarat 395003',

	// Links (link targets only, never shown as text).
	'app_login'        => 'https://pharmacy.healthray.com/login',
	'app_signup'       => 'https://pharmacy.healthray.com/signup',
	'help_url'         => 'https://support.healthray.com',
	'calendly'         => 'https://calendly.com/healthray/healthray-technologies-lead-meeting',

	// Social profiles: none yet, so the footer row stays hidden.
	'social'           => array(),

	// Layout.
	'hero_pattern'     => true,
	'footer_blurb'     => 'Billing, expiry, stock, purchase and GST software for medical stores, pharmacy chains and hospital pharmacies.',
	'footer_note'      => 'Draft website, not yet published.',
	'cta_title'        => 'See it working with medicines from your own shelf',
	'cta_text'         => 'Book a short demo, or start free[rx_free_plan field="name" before=" on the " after=" plan"], no payment needed.',
	'promo_image'      => 'v2-02-dashboard',
	'promo_alt'        => 'Sample pharmacy dashboard',
	'promo_title'      => 'Your whole pharmacy on one screen',
	'promo_text'       => 'Sales, expiry, stock alerts and collections, updated with every bill.',
	'promo_link_label' => 'See all features',

	// Pricing API (data source only, never printed).
	'pricing_api'      => 'https://pharmacyapi.healthray.com/api/subscription/pricing/public',
	'pricing_ttl'      => 300,
	'pricing_retry'    => 60,
);
