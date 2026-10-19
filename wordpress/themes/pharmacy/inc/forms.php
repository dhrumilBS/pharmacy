<?php
/**
 * Booking forms: Contact Form 7 behind the demo slip and the demo form.
 *
 * Two CF7 forms are created by import/forms-setup.php and found by their
 * `_rx_form` meta, so the theme never depends on a numeric form id:
 *
 *   hero  the 2-field demo slip in the hero  (parts/hero-lead)
 *   demo  the full booking form              (parts/demo-form: #demo, Book a demo, Contact)
 *
 * After a successful send, Lead Guard for CF7 opens /thank-you/ (the form's
 * `lead_guard_redirect` setting, templates/thank-you.php). When the form or
 * Contact Form 7 is missing, the parts print their original markup, so the
 * site keeps working.
 *
 * No email is sent for any form: `skip_mail` is on in every form's
 * Additional Settings, and the filter below enforces it for forms made later.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/* No mail, from any form, including forms added later in the admin. */
add_filter( 'wpcf7_skip_mail', '__return_true' );

/* The form templates are written as exact markup: no <p>/<br> from wpautop. */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/**
 * Id of the CF7 form with the given `_rx_form` key (0 when there is none).
 *
 * @param string $slug Form key: 'hero' or 'demo'.
 */
function rx_form_id( string $slug ): int {
	static $cache = array();
	if ( ! isset( $cache[ $slug ] ) ) {
		$ids            = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_rx_form', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one lookup per form per request.
				'meta_value'     => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$cache[ $slug ] = $ids ? (int) $ids[0] : 0;
	}
	return $cache[ $slug ];
}

/**
 * Markup of a booking form, or '' when Contact Form 7 or the form is missing.
 *
 * Loads CF7's script (and Lead Guard's, which hangs on it) for this page
 * only: cleanup.php keeps both off everywhere else.
 *
 * @param string $slug  Form key: 'hero' or 'demo'.
 * @param string $label Accessible name of the <form>.
 */
function rx_form_html( string $slug, string $label = '' ): string {
	if ( ! function_exists( 'wpcf7_enqueue_scripts' ) || ! shortcode_exists( 'contact-form-7' ) ) {
		return '';
	}
	$id = rx_form_id( $slug );
	if ( ! $id ) {
		return '';
	}
	wpcf7_enqueue_scripts();
	return do_shortcode(
		sprintf(
			'[contact-form-7 id="%d" html_class="rx-form rx-form-%s"%s]',
			$id,
			esc_attr( $slug ),
			'' !== $label ? ' html_aria_label="' . esc_attr( $label ) . '"' : ''
		)
	);
}

/**
 * Page keys that print a booking form or follow one (styles, thank-you script).
 *
 * @return string[]
 */
function rx_forms_page_keys(): array {
	return array( 'home', 'home-blocks', 'demo', 'contact', 'thank-you' );
}
