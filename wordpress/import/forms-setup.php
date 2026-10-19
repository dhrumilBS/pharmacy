<?php
/**
 * Sets up the plugins and the booking forms in the local WordPress install.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\forms-setup.php             apply
 *     D:\xampp\php\php.exe wordpress\import\forms-setup.php --dry-run   report only
 *
 * Re-runnable: everything is found and updated in place. What it does:
 *
 *  1. Activates Lead Guard for CF7, TrustLayer Consent and HandL UTM Grabber
 *     (Contact Form 7 must already be active). Akismet is left alone: it needs
 *     an API key, which this script never handles.
 *  2. Creates / updates two Contact Form 7 forms, found by their `_rx_form` meta:
 *       hero  the 2-field demo slip in the hero
 *       demo  the booking form in the #demo section, the Book a demo page and Contact
 *     Both redirect to /thank-you/ after a successful send (Lead Guard) and
 *     carry HandL's hidden UTM fields.
 *  3. Turns `skip_mail: on` on in EVERY Contact Form 7 form (also forms made by hand),
 *     so no email is sent to anyone.
 *  4. Creates the Thank you page (templates/thank-you.php, noindex).
 *  5. Configures Lead Guard (a country code is required, our short messages) and
 *     TrustLayer Consent (site colours, privacy and cookie policy pages), and adds
 *     "Cookie settings" to the Legal menu.
 *
 * Local only (lib/guard.php). It reads no secrets and writes no keys.
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';
require_once __DIR__ . '/lib/upsert.php';
require_once __DIR__ . '/lib/blocks.php';

$dry = in_array( '--dry-run', $argv ?? array(), true ) || in_array( '-n', $argv ?? array(), true );

rx_bootstrap_wp();
rx_assert_local();

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Prints one report line.
 *
 * @param string $what   Short label.
 * @param string $detail Detail.
 */
function rx_fs_line( string $what, string $detail ): void {
	echo str_pad( $what, 12 ) . $detail . PHP_EOL;
}

/*
 * --- 1. Plugins ---------------------------------------------------------------
 */
$plugin_files = array(
	'lead-guard-cf7/lead-guard-cf7.php',
	'trustlayer-consent/trustlayer-consent.php',
	'handl-utm-grabber-v3/handl-utm-grabber-v3.php',
);

if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
	fwrite( STDERR, 'Contact Form 7 is not active: activate it first.' . PHP_EOL );
	exit( 1 );
}

foreach ( $plugin_files as $file ) {
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
		rx_fs_line( 'plugin', "MISSING $file" );
		continue;
	}
	if ( is_plugin_active( $file ) ) {
		rx_fs_line( 'plugin', "already active: $file" );
		continue;
	}
	if ( $dry ) {
		rx_fs_line( 'plugin', "would activate: $file" );
		continue;
	}
	$result = activate_plugin( $file );
	rx_fs_line( 'plugin', is_wp_error( $result ) ? 'FAILED ' . $file . ': ' . $result->get_error_message() : "activated: $file" );
}

/*
 * --- 2. The two forms -----------------------------------------------------------
 */
// Attribution captured by HandL: this visit's UTMs, click ids, landing page and referrer.
$utm_names = array(
	'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
	'gclid', 'fbclid', 'msclkid',
	'handl_landing_page', 'handl_original_ref', 'handl_ref',
);
$utm_tags  = '';
if ( is_plugin_active( 'handl-utm-grabber-v3/handl-utm-grabber-v3.php' ) || $dry ) {
	foreach ( $utm_names as $name ) {
		$utm_tags .= '[' . $name . '_cf7 ' . $name . ']';
	}
	// The hidden fields sit in one hidden group, so they never take layout space.
	$utm_tags = "\n<div class=\"rx-utm\" hidden>" . $utm_tags . '</div>';
}

$settings_common = "skip_mail: on\nlead_guard_redirect: /thank-you/\nlead_guard_delay: 800\nlead_guard_event: demo_request_submitted\n";

$forms = array(
	'hero' => array(
		'title'    => 'Booking form: hero demo slip',
		'form'     => <<<'CF7'
<div class="slip-fields">
<div class="fld"><label for="hero-name">Your name</label>[text* your_name id:hero-name autocomplete:name]</div>
<div class="fld"><label for="hero-phone">Mobile number</label><span class="slip-tel"><span class="slip-cc" aria-hidden="true">+91</span>[tel* your_phone id:hero-phone autocomplete:tel placeholder "98xxxxxx21"]</span></div>
</div>
[hidden form-source "hero"]%UTM%
[submit class:btn class:btn-primary class:slip-btn "Call me for a free demo"]
CF7,
		'settings' => $settings_common . "lead_guard_required_your_name: Please enter your name.\nlead_guard_required_your_phone: Please enter your mobile number.\n",
	),
	'demo' => array(
		'title'    => 'Booking form: book a demo',
		'form'     => <<<'CF7'
<div class="form-grid">
<div class="fld"><label for="f-name">Your name</label>[text* your_name id:f-name autocomplete:name]</div>
<div class="fld"><label for="f-phone">Mobile number</label>[tel* your_phone id:f-phone autocomplete:tel placeholder "10-digit mobile"]</div>
<div class="fld"><label for="f-store">Store or hospital name</label>[text* your_store id:f-store autocomplete:organization]</div>
<div class="fld"><label for="f-city">City</label>[text* your_city id:f-city autocomplete:address-level2]</div>
<div class="fld full"><label for="f-current">What do you use today?</label>[select your_current id:f-current first_as_label "Select one" "Desktop pharmacy software" "General billing app" "Excel or paper registers" "Hospital software" "Opening a new store"]</div>
<div class="fld full"><label for="f-want">I’d like to</label>[select your_want id:f-want "Book a free demo" "Get Enterprise pricing for my stores" "Get the buyer’s checklist"]</div>
</div>
[hidden form-source "demo"]%UTM%
[submit id:submit-label class:btn class:btn-primary "Book my free demo"]
CF7,
		'settings' => $settings_common
			. "lead_guard_required_your_name: Please enter your name.\n"
			. "lead_guard_required_your_phone: Please enter your mobile number.\n"
			. "lead_guard_required_your_store: Please enter your store name.\n"
			. "lead_guard_required_your_city: Please enter your city.\n",
	),
);

$messages = array(
	'mail_sent_ok'           => 'Thank you, we’ll call you soon.',
	'mail_sent_ng'           => 'Sorry, that did not go through. Please try again.',
	'validation_error'       => 'Please check the highlighted fields.',
	'spam'                   => 'Sorry, that did not go through. Please try again.',
	'invalid_required'       => 'Please fill in this field.',
	'invalid_too_long'       => 'This is too long.',
	'invalid_too_short'      => 'This is too short.',
	'invalid_tel'            => 'Please enter a valid mobile number.',
);

if ( class_exists( 'WPCF7_ContactForm' ) ) {
	foreach ( $forms as $slug => $def ) {
		$existing = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_rx_form',
				'meta_value'     => $slug,
			)
		);
		$cf       = $existing ? WPCF7_ContactForm::get_instance( (int) $existing[0] ) : WPCF7_ContactForm::get_template();
		if ( ! $cf ) {
			rx_fs_line( 'form', "FAILED to load form $slug" );
			continue;
		}
		$mail = (array) $cf->prop( 'mail' );
		$mail = array_merge(
			$mail,
			array(
				'subject' => 'New demo request: [your_name]',
				'body'    => "Name: [your_name]\nMobile: [your_phone]\n" . ( 'demo' === $slug ? "Store: [your_store]\nCity: [your_city]\nUses today: [your_current]\nWants: [your_want]\n" : '' ) . "Source: [form-source]\n",
			)
		);
		if ( $dry ) {
			rx_fs_line( 'form', ( $existing ? 'would update' : 'would create' ) . " $slug (" . $def['title'] . ')' );
			continue;
		}
		$cf->set_title( $def['title'] );
		$cf->set_properties(
			array(
				'form'                => str_replace( '%UTM%', $utm_tags, $def['form'] ),
				'mail'                => $mail,
				'messages'            => array_merge( (array) $cf->prop( 'messages' ), $messages ),
				'additional_settings' => $def['settings'],
			)
		);
		$id = $cf->save();
		if ( ! $id ) {
			rx_fs_line( 'form', "FAILED to save $slug" );
			continue;
		}
		update_post_meta( $id, '_rx_form', $slug );
		rx_fs_line( 'form', ( $existing ? 'updated' : 'created' ) . " #$id $slug  [contact-form-7 id=\"$id\"]" );
	}

	/*
	 * --- 3. skip_mail on in every form --------------------------------------------
	 */
	$all = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $all as $form_id ) {
		$cf = WPCF7_ContactForm::get_instance( (int) $form_id );
		if ( ! $cf ) {
			continue;
		}
		$settings = (string) $cf->prop( 'additional_settings' );
		if ( preg_match( '/^\s*skip_mail\s*:\s*on\s*$/mi', $settings ) ) {
			rx_fs_line( 'skip_mail', "#$form_id already on (" . $cf->title() . ')' );
			continue;
		}
		if ( $dry ) {
			rx_fs_line( 'skip_mail', "would turn on #$form_id (" . $cf->title() . ')' );
			continue;
		}
		// Replace an explicit "skip_mail: off" line, else add the line.
		$settings = preg_replace( '/^\s*skip_mail\s*:.*$/mi', '', $settings );
		$cf->set_properties( array( 'additional_settings' => "skip_mail: on\n" . trim( (string) $settings ) ) );
		$cf->save();
		rx_fs_line( 'skip_mail', "turned on #$form_id (" . $cf->title() . ')' );
	}
}

/*
 * --- 3b. UTM fields in every form (forms made by hand get the hidden fields added at the end) ---
 */
if ( class_exists( 'WPCF7_ContactForm' ) && ! $dry && '' !== $utm_tags ) {
	foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $form_id ) {
		$cf = WPCF7_ContactForm::get_instance( (int) $form_id );
		if ( ! $cf ) {
			continue;
		}
		$body = (string) $cf->prop( 'form' );
		// Rebuild the group: drop the old wrapper and every HandL tag (also the retired first_utm_* ones), then add the current set.
		$clean = preg_replace( '~\s*<div class="rx-utm" hidden>.*?</div>~s', '', $body );
		$clean = preg_replace( '~\s*\[(?:first_)?(?:utm_[a-z]+|gclid|fbclid|msclkid|handl_[a-z_]+)_cf7 [a-z_]+\]~', '', (string) $clean );
		$next  = rtrim( (string) $clean ) . $utm_tags;
		if ( $next !== $body ) {
			$cf->set_properties( array( 'form' => $next ) );
			$cf->save();
			rx_fs_line( 'utm', 'set in #' . $form_id . ' (' . $cf->title() . ')' );
		}
	}
}

/*
 * --- 4. Thank you page ------------------------------------------------------------
 */
$thanks = array(
	'source'   => 'thank-you.php',
	'key'      => 'thank-you',
	'title'    => 'Thank you',
	'slug'     => 'thank-you',
	'template' => 'templates/thank-you.php',
	'seo'      => array(
		'title'   => 'Thank you %%sep%% %%sitename%%',
		'desc'    => 'We have your request and will call you to book your demo.',
		'noindex' => true,
	),
	'content'  => rx_blk_p( 'We have your request. Keep billing as you do today: nothing changes until you choose.' ),
);
if ( $dry ) {
	rx_fs_line( 'page', 'would create / update /thank-you/' );
} else {
	$report = array();
	$page   = rx_upsert_post( $thanks, $report );
	rx_fs_line( 'page', is_wp_error( $page ) ? 'FAILED: ' . $page->get_error_message() : ucfirst( $report['action'] ) . " #$page " . get_permalink( $page ) );
}

/*
 * --- 5. Plugin settings -----------------------------------------------------------
 */
if ( ! $dry && is_plugin_active( 'lead-guard-cf7/lead-guard-cf7.php' ) ) {
	$lg = get_option( 'lead_guard_cf7', array() );
	$lg = is_array( $lg ) ? $lg : array();
	update_option(
		'lead_guard_cf7',
		array_merge(
			$lg,
			array(
				// A number typed without "+" is a national number with the default country code (Contact > Lead Guard).
				'phone_default'        => 'code',
				'phone_code'           => '91',
				'phone_national_len'   => 10,
				'phone_national_first' => '6789',
				'msg_phone'            => 'Please enter a valid mobile number.',
				'msg_phone_national'   => 'Please enter a 10-digit mobile number.',
				'msg_phone_country'    => 'Please add your country code, e.g. +44.',
				'msg_duplicate'     => 'We already have your request from the last {hours} hours. We will call you soon.',
			)
		)
	);
	rx_fs_line( 'lead guard', 'settings saved (phone: default country code +91, 10 digits)' );
}

if ( ! $dry && class_exists( 'TLC_Settings' ) ) {
	$page_by_key = static function ( string $key ): int {
		$ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_rx_key',
				'meta_value'     => $key,
			)
		);
		return $ids ? (int) $ids[0] : 0;
	};
	$privacy = $page_by_key( 'privacy' );
	$cookies = $page_by_key( 'cookies' );

	TLC_Settings::save_sections(
		array(
			'banner' => TLC_Settings::sanitize_section(
				'banner',
				array(
					'layout'      => 'box-left',
					'delay'       => 600,
					'show_reject' => 1,
					'equal_buttons' => 1,
					'css'         => 'full',
					'radius'      => 12,
					'colors'      => array(
						'bg'           => '#ffffff',
						'text'         => '#475569',
						'heading'      => '#081C2C',
						'muted'        => '#64748B',
						'border'       => '#E2E8F0',
						'accent'       => '#2563EB',
						'primary'      => '#2563EB',
						'primary_text' => '#ffffff',
					),
				),
				true
			),
			'policy' => TLC_Settings::sanitize_section(
				'policy',
				array(
					'privacy_page' => $privacy,
					'cookie_page'  => $cookies,
					'show_privacy' => 1,
					'show_cookie'  => 1,
				),
				true
			),
		)
	);
	rx_fs_line( 'trustlayer', "settings saved (privacy page #$privacy, cookie page #$cookies, strict opt-in unless a region says otherwise)" );

	// "Cookie settings" in the Legal menu: re-run that one menu from content/menus.php.
	$menus = require __DIR__ . '/content/menus.php';
	foreach ( $menus as $menu ) {
		if ( 'Legal' === ( $menu['name'] ?? '' ) ) {
			$report = array();
			$id     = rx_upsert_menu( $menu, $report );
			rx_fs_line( 'menu', is_wp_error( $id ) ? 'FAILED: ' . $id->get_error_message() : 'Legal menu ' . $report['action'] . ' (items created: ' . (int) ( $report['items_created'] ?? 0 ) . ')' );
		}
	}
}

echo ( $dry ? 'Dry run: nothing written.' : 'Done.' ) . PHP_EOL;
