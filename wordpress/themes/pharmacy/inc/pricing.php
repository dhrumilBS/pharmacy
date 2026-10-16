<?php
/**
 * Pricing service: rx_pricing_data and pricing template helpers.
 *
 * WordPress port of pricing_data() in the PHP site's partials/pricing-data.php.
 * Same lookup order, so a page never breaks when the Pricing_API is slow or
 * down (Requirements 8.1, 8.3):
 *
 *   1. cache     transient rx_pricing (Site_Settings pricing_ttl, 300 s)
 *   2. live      GET Site_Settings pricing_api (5 s timeout); on success the
 *                cache and the stale copy are refreshed
 *   3. stale     option rx_pricing_stale (last good response, autoload off);
 *                cached for pricing_retry (60 s) so the API is retried later,
 *                like the PHP site's touch()
 *   4. snapshot  the theme's data/pricing.json (child theme first)
 *
 * Choosing between them is rx_pricing_resolve() in inc/core/pricing.php,
 * which validates every source with rx_pricing_valid(). Unlike the PHP site,
 * which throws when every source fails, rx_pricing_data() then returns null
 * and templates print rx_pricing_notice() where prices would be.
 *
 * The live request goes through the WordPress HTTP API, so the outbound
 * allow-list in inc/http.php applies. A blocked or failed request counts as
 * "API down" and the next source is used.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the pricing document once per request.
 *
 * @param bool $refresh Resolve again, ignoring the memo (for CLI scripts and
 *                      tests that run several scenarios in one process).
 * @return array{0: ?array, 1: string} [ document or null, source name ]:
 *         'cache', 'live', 'stale', 'snapshot' or 'none'.
 */
function rx_pricing_load( bool $refresh = false ): array {
	static $result = null;

	if ( null === $result || $refresh ) {
		$result = rx_pricing_resolve( rx_pricing_sources(), rx_pricing_hooks() );
	}
	return $result;
}

/**
 * The pricing document (decoded Pricing_API JSON), or null when the API, the
 * cache, the stale copy and the snapshot are all unusable.
 *
 * Memoised per request. The result passes through the `rx_pricing_data`
 * filter on every call (arguments: document or null, source name), so a
 * test can force the "no pricing" path with
 * add_filter( 'rx_pricing_data', '__return_null' ). A filtered value that is
 * not a valid document gives null.
 *
 * @return array|null Valid pricing document, or null.
 */
function rx_pricing_data(): ?array {
	[ $data, $source ] = rx_pricing_load();

	/**
	 * Filters the pricing document used by templates, shortcodes and schema.
	 *
	 * @param array|null $data   Valid pricing document, or null.
	 * @param string     $source 'cache', 'live', 'stale', 'snapshot' or 'none'.
	 */
	$data = apply_filters( 'rx_pricing_data', $data, $source );

	return rx_pricing_valid( $data ) ? $data : null;
}

/**
 * Where this request's pricing came from: 'cache', 'live', 'stale',
 * 'snapshot' or 'none'. For debugging (the PHP site's $d['_source']).
 */
function rx_pricing_source(): string {
	return rx_pricing_load()[1];
}

/**
 * The four pricing sources for rx_pricing_resolve(). Each is called only
 * when every earlier source failed.
 *
 * @return array<string, callable> Keys 'cache', 'live', 'stale', 'snapshot'.
 */
function rx_pricing_sources(): array {
	return array(
		'cache'    => static fn() => get_transient( 'rx_pricing' ),
		'live'     => 'rx_pricing_fetch',
		'stale'    => static fn() => get_option( 'rx_pricing_stale' ),
		'snapshot' => 'rx_pricing_read_snapshot',
	);
}

/**
 * Cache writes for rx_pricing_resolve().
 *
 * - on_live:  keep the response for pricing_ttl seconds and save it as the
 *   stale copy (not autoloaded: it is only read when the API is down).
 * - on_stale: keep the stale copy for pricing_retry seconds, so the API is
 *   asked again after that rather than on every request.
 *
 * @return array<string, callable> Keys 'on_live', 'on_stale'.
 */
function rx_pricing_hooks(): array {
	return array(
		'on_live'  => static function ( array $d ): void {
			set_transient( 'rx_pricing', $d, (int) rx_setting( 'pricing_ttl' ) );
			update_option( 'rx_pricing_stale', $d, false );
		},
		'on_stale' => static function ( array $d ): void {
			set_transient( 'rx_pricing', $d, (int) rx_setting( 'pricing_retry' ) );
		},
	);
}

/**
 * GET the Pricing_API. Port of pricing_fetch().
 *
 * Read-only request with the PHP site's settings: 5 s timeout, up to 3
 * redirects, Accept: application/json. Skipped (no request) when the
 * pricing_api setting is empty.
 *
 * @return mixed Decoded JSON (validated by rx_pricing_resolve()), or null
 *               when the setting is empty, the request failed or was
 *               blocked, the status is not 200 or the body is not JSON.
 */
function rx_pricing_fetch() {
	$api = trim( (string) rx_setting( 'pricing_api' ) );
	if ( '' === $api ) {
		return null;
	}

	$response = wp_remote_get(
		$api,
		array(
			'timeout'     => 5,
			'redirection' => 3,
			'headers'     => array( 'Accept' => 'application/json' ),
		)
	);
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	return json_decode( wp_remote_retrieve_body( $response ), true );
}

/**
 * The committed snapshot, data/pricing.json in the child theme or else the
 * parent theme. Port of pricing_read( PRICING_SNAPSHOT ).
 *
 * @return mixed Decoded JSON (validated by rx_pricing_resolve()), or null
 *               when the file is missing or unreadable.
 */
function rx_pricing_read_snapshot() {
	$file = get_theme_file_path( 'data/pricing.json' );
	if ( ! is_file( $file ) || ! is_readable( $file ) ) {
		return null;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file, not a URL.
	$json = file_get_contents( $file );
	return false === $json ? null : json_decode( $json, true );
}

/**
 * One cell of the plan comparison table. Port of cell() in pricing.php,
 * same markup:
 *
 * - no status (the plan lacks the feature): a dash, "Not included";
 * - a display value (for example "10" or "Unlimited"): the value;
 * - a credit amount (0 counts): a tick plus "uses credits";
 * - otherwise: a tick, "Included".
 *
 * @param array|null $st One entry of $d['statuses'] for this plan and
 *                       feature, or null.
 * @return string Cell content markup (escaped).
 */
function rx_pricing_cell( ?array $st ): string {
	if ( ! $st ) {
		return '<span class="pc-no" aria-label="' . esc_attr__( 'Not included', 'pharmacy' ) . '">—</span>';
	}
	if ( ! empty( $st['display_value'] ) ) {
		return '<span class="pc-val">' . esc_html( (string) $st['display_value'] ) . '</span>';
	}

	$yes = '<span class="pc-yes" aria-label="' . esc_attr__( 'Included', 'pharmacy' ) . '">✓</span>';
	if ( isset( $st['credit_amount'] ) ) {
		return $yes . '<small>' . esc_html__( 'uses credits', 'pharmacy' ) . '</small>';
	}
	return $yes;
}

/**
 * Notice printed where prices would be when rx_pricing_data() is null:
 * "Prices are updating. Call sales on {sales_phone} or book a free demo."
 *
 * The phone number links to sales_tel and "book a free demo" to the Demo
 * page. Without a sales_phone setting the sentence asks for a demo only.
 * Styled with the site's existing .notice box (layout.css).
 *
 * @return string Notice markup (escaped).
 */
function rx_pricing_notice(): string {
	$phone = trim( (string) rx_setting( 'sales_phone' ) );
	$tel   = trim( (string) rx_setting( 'sales_tel' ) );
	$demo  = '<a href="' . esc_url( rx_url( 'demo' ) ) . '">' . esc_html__( 'book a free demo', 'pharmacy' ) . '</a>';

	if ( '' !== $phone ) {
		$call = '' !== $tel
			? '<a href="' . esc_url( 'tel:' . $tel ) . '">' . esc_html( $phone ) . '</a>'
			: esc_html( $phone );
		$text = sprintf(
			/* translators: 1: sales phone number (a tel: link), 2: "book a free demo" link. */
			esc_html__( 'Prices are updating. Call sales on %1$s or %2$s.', 'pharmacy' ),
			$call,
			$demo
		);
	} else {
		$text = sprintf(
			/* translators: %s: "book a free demo" link. */
			esc_html__( 'Prices are updating. Please %s.', 'pharmacy' ),
			$demo
		);
	}

	return '<p class="notice">' . $text . '</p>';
}
