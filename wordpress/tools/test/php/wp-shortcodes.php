<?php
/**
 * Test helper for the shortcode example tests (task 7.9): render rx_*
 * shortcodes inside the local WordPress install, read-only.
 *
 * Loads pharmacy-wp through wordpress/import/lib/guard.php
 * (rx_bootstrap_wp() + rx_assert_local()), with three gates registered
 * before WordPress loads, so they cover the whole process:
 *
 * - `query` filter: only SELECT, SHOW, DESCRIBE and SET statements reach the
 *   database. Anything else is dropped (wpdb::query() returns false) and
 *   listed in the output, split into "bootstrap" (while WordPress loads)
 *   and per case.
 * - `pre_http_request` filter: every outbound request is blocked and listed.
 * - `pre_transient_rx_pricing` filter: the pricing cache "hits" with the
 *   committed snapshot (themes/pharmacy/data/pricing.json), so
 *   rx_pricing_data() resolves from source "cache" without calling the
 *   Pricing_API and without writing a transient or the stale copy.
 *
 * Input on STDIN (JSON):
 *
 *     {
 *       "mode":  "snapshot" | "null",
 *       "feats": [ "feat_2fa", … ],     unique_names for the plan label references
 *       "urls":  [ "pricing", … ],      page keys for the rx_url() references
 *       "cases": [ { "id": "brand", "code": "[rx_brand]", "via": "do_shortcode" }, … ]
 *     }
 *
 * - mode "snapshot": pricing is the snapshot document.
 * - mode "null": add_filter( 'rx_pricing_data', '__return_null' ), the
 *   documented test hook for the "no pricing" path (Requirement 8.3).
 * - via (optional): "do_shortcode" (default), "rx_text" or "rx_html", the
 *   helper the code is passed through.
 *
 * Output on STDOUT (JSON):
 *
 *     {
 *       "mode", "theme": { template, stylesheet }, "shortcodes": { tag: registered },
 *       "gates": { query, pre_http_request, pre_transient_rx_pricing }: each gate is hooked,
 *       "pricing": { "source", "is_null", "equals_snapshot" },
 *       "ref":  references computed in PHP: brand, settings (rx_setting()),
 *               urls (rx_url()), helpers (core pricing prose), plan_labels
 *               (rx_feature_plan_label()), free (free plan facts from
 *               rx_pricing_split() + rx_plan_bill_limits()), conds;
 *               pricing references are null in mode "null",
 *       "esc":  the same references with esc_html() applied to every string,
 *       "esc_url": { urls: { key: esc_url( rx_url( key ) ) }, settings: { name: esc_url( value ) } },
 *       "results": [ { id, out, error?, output?, writes: [], http: [] }, … ],
 *       "writes":  { "bootstrap": [ statements blocked while WordPress loaded ],
 *                    "other":     [ { phase, query } blocked afterwards ] },
 *       "http":    [ { phase, host, url } blocked outbound requests ],
 *       "bootstrap_output": anything printed while WordPress loaded
 *     }
 *
 * While a case runs, error_reporting is E_ALL and any warning, notice or
 * deprecation fails that case ("error"); the other cases still run.
 * Invalid input or an inactive pharmacy theme exits with code 2; a guard
 * failure exits with code 1 (from guard.php).
 *
 * Used by test/shortcodes.test.mjs through runPhp(). CLI only.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

// Shortest round-trip float output, whatever php.ini says.
ini_set( 'serialize_precision', '-1' );

/** wordpress/ folder of the repository. */
define( 'RX_WPSC_ROOT', dirname( __DIR__, 3 ) );

/** Shortcodes the theme must register (design §6). */
const RX_WPSC_TAGS = array(
	'rx_brand',
	'rx_site',
	'rx_tel',
	'rx_url',
	'rx_link',
	'rx_plan',
	'rx_free_plan',
	'rx_cost_text',
	'rx_compare_text',
	'rx_terms_text',
	'rx_users_text',
	'rx_credits_text',
	'rx_if',
);

/** Site_Settings reported as references (text and URL settings). */
const RX_WPSC_SETTINGS = array(
	'sales_phone',
	'sales_tel',
	'support_phone',
	'support_tel',
	'email',
	'company',
	'address',
	'map_query',
	'app_login',
	'app_signup',
	'help_url',
	'calendly',
	'pricing_api',
);

/** URL settings reported with esc_url(). */
const RX_WPSC_URL_SETTINGS = array( 'app_login', 'app_signup', 'help_url', 'calendly', 'pricing_api' );

/**
 * Print a message to STDERR and stop with exit code 2.
 *
 * @param string $message What went wrong.
 */
function rx_wpsc_fail( string $message ): never {
	fwrite( STDERR, '[rx wp-shortcodes] ' . $message . PHP_EOL );
	exit( 2 );
}

/*
 * -------------------------------------------------------------------------
 * Gates (registered before WordPress loads)
 * -------------------------------------------------------------------------
 */

/**
 * Name of the current phase: 'bootstrap' while WordPress loads, then the
 * id of the running case, or '' between cases.
 *
 * @param string|null $set New phase, or null to read.
 */
function rx_wpsc_phase( ?string $set = null ): string {
	static $phase = 'bootstrap';
	if ( null !== $set ) {
		$phase = $set;
	}
	return $phase;
}

/**
 * Record a blocked write or request, or return the log.
 *
 * @param string     $kind  'writes' or 'http'.
 * @param array|null $entry Entry to add, or null to read.
 * @return array<int, array> Entries of that kind, each with its phase.
 */
function rx_wpsc_log( string $kind, ?array $entry = null ): array {
	static $log = array(
		'writes' => array(),
		'http'   => array(),
	);
	if ( null !== $entry ) {
		$log[ $kind ][] = array( 'phase' => rx_wpsc_phase() ) + $entry;
	}
	return $log[ $kind ];
}

/**
 * Whether a SQL statement only reads: SELECT, SHOW, DESCRIBE or SET, after
 * leading whitespace, block comments and opening parentheses.
 *
 * @param string $query SQL statement.
 */
function rx_wpsc_is_read( string $query ): bool {
	$q = ltrim( $query );
	while ( '' !== $q ) {
		if ( str_starts_with( $q, '/*' ) ) {
			$end = strpos( $q, '*/' );
			if ( false === $end ) {
				return false;
			}
			$q = ltrim( substr( $q, $end + 2 ) );
			continue;
		}
		if ( str_starts_with( $q, '(' ) ) {
			$q = ltrim( substr( $q, 1 ) );
			continue;
		}
		break;
	}
	return 1 === preg_match( '/^(SELECT|SHOW|DESCRIBE|SET)\b/i', $q );
}

/**
 * `query` filter: pass reads, drop and record everything else.
 *
 * @param string $query SQL statement.
 * @return string The statement, or '' (wpdb::query() then returns false).
 */
function rx_wpsc_query_gate( $query ) {
	$query = (string) $query;
	if ( rx_wpsc_is_read( $query ) ) {
		return $query;
	}
	rx_wpsc_log( 'writes', array( 'query' => substr( preg_replace( '/\s+/', ' ', trim( $query ) ), 0, 300 ) ) );
	return '';
}

/**
 * `pre_http_request` filter: block every outbound request and record it.
 *
 * @param false|array|WP_Error $preempt     Response from earlier filters, or false.
 * @param array                $parsed_args HTTP request arguments.
 * @param string               $url         Request URL.
 * @return WP_Error
 */
function rx_wpsc_http_gate( $preempt, $parsed_args, $url ) {
	$url = is_string( $url ) ? $url : '';
	rx_wpsc_log(
		'http',
		array(
			'host' => (string) parse_url( $url, PHP_URL_HOST ),
			'url'  => substr( $url, 0, 300 ),
		)
	);
	return new WP_Error( 'rx_test_blocked', 'Outbound request blocked by wp-shortcodes.php.' );
}

/**
 * The committed pricing snapshot, decoded once.
 *
 * @return array Snapshot document.
 */
function rx_wpsc_snapshot(): array {
	static $doc = null;
	if ( null === $doc ) {
		$file = RX_WPSC_ROOT . '/themes/pharmacy/data/pricing.json';
		$json = is_file( $file ) ? file_get_contents( $file ) : false;
		$doc  = false === $json ? null : json_decode( $json, true );
		if ( ! is_array( $doc ) ) {
			rx_wpsc_fail( 'Cannot read the pricing snapshot ' . $file );
		}
	}
	return $doc;
}

/**
 * `pre_transient_rx_pricing` filter: the pricing cache holds the snapshot.
 *
 * @return array Snapshot document.
 */
function rx_wpsc_cached_pricing() {
	return rx_wpsc_snapshot();
}

/**
 * Add a callback to $GLOBALS['wp_filter'] before plugin.php exists, as
 * setup.php does; WordPress turns it into a real hook
 * (WP_Hook::build_preinitialized_hooks()).
 *
 * @param string $hook     Hook name.
 * @param string $function Callback name.
 * @param int    $args     Accepted arguments.
 */
function rx_wpsc_preregister( string $hook, string $function, int $args ): void {
	$GLOBALS['wp_filter'][ $hook ][ PHP_INT_MAX ][] = array(
		'function'      => $function,
		'accepted_args' => $args,
	);
}

/*
 * -------------------------------------------------------------------------
 * Input
 * -------------------------------------------------------------------------
 */

$rx_wpsc_raw = stream_get_contents( STDIN );
if ( false === $rx_wpsc_raw || '' === trim( $rx_wpsc_raw ) ) {
	rx_wpsc_fail( 'Expected a JSON object on STDIN, got nothing.' );
}
try {
	$rx_wpsc_in = json_decode( $rx_wpsc_raw, true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $e ) {
	rx_wpsc_fail( 'Invalid JSON on STDIN: ' . $e->getMessage() );
}
if ( ! is_array( $rx_wpsc_in ) || array_is_list( $rx_wpsc_in ) ) {
	rx_wpsc_fail( 'Expected a JSON object on STDIN.' );
}

$rx_wpsc_mode = $rx_wpsc_in['mode'] ?? '';
if ( ! in_array( $rx_wpsc_mode, array( 'snapshot', 'null' ), true ) ) {
	rx_wpsc_fail( '"mode" must be "snapshot" or "null".' );
}

$rx_wpsc_list = static function ( string $key ) use ( $rx_wpsc_in ): array {
	$value = $rx_wpsc_in[ $key ] ?? array();
	if ( ! is_array( $value ) || ! array_is_list( $value ) ) {
		rx_wpsc_fail( '"' . $key . '" must be a JSON array.' );
	}
	return $value;
};

$rx_wpsc_feats = $rx_wpsc_list( 'feats' );
$rx_wpsc_urls  = $rx_wpsc_list( 'urls' );
$rx_wpsc_cases = $rx_wpsc_list( 'cases' );

foreach ( array_merge( $rx_wpsc_feats, $rx_wpsc_urls ) as $rx_wpsc_item ) {
	if ( ! is_string( $rx_wpsc_item ) ) {
		rx_wpsc_fail( '"feats" and "urls" must hold strings.' );
	}
}
foreach ( $rx_wpsc_cases as $rx_wpsc_case ) {
	if (
		! is_array( $rx_wpsc_case )
		|| ! isset( $rx_wpsc_case['id'], $rx_wpsc_case['code'] )
		|| ! is_string( $rx_wpsc_case['id'] )
		|| ! is_string( $rx_wpsc_case['code'] )
		|| ! in_array( $rx_wpsc_case['via'] ?? 'do_shortcode', array( 'do_shortcode', 'rx_text', 'rx_html' ), true )
	) {
		rx_wpsc_fail( 'Each case needs a string "id" and "code", and "via" do_shortcode, rx_text or rx_html.' );
	}
}

/*
 * -------------------------------------------------------------------------
 * Load WordPress (read-only)
 * -------------------------------------------------------------------------
 */

rx_wpsc_snapshot();
rx_wpsc_preregister( 'query', 'rx_wpsc_query_gate', 1 );
rx_wpsc_preregister( 'pre_http_request', 'rx_wpsc_http_gate', 3 );
rx_wpsc_preregister( 'pre_transient_rx_pricing', 'rx_wpsc_cached_pricing', 0 );

require_once RX_WPSC_ROOT . '/import/lib/guard.php';

ob_start();
rx_bootstrap_wp();
rx_assert_local();
$rx_wpsc_boot_output = (string) ob_get_clean();
rx_wpsc_phase( '' );

if ( 'pharmacy' !== get_template() ) {
	rx_wpsc_fail( 'The pharmacy theme is not active (template: ' . get_template() . '). Run wordpress/import/setup.php first.' );
}
foreach ( array( 'rx_text', 'rx_html', 'rx_pricing_data', 'rx_setting', 'rx_url', 'rx_brand' ) as $rx_wpsc_fn ) {
	if ( ! function_exists( $rx_wpsc_fn ) ) {
		rx_wpsc_fail( 'Theme function ' . $rx_wpsc_fn . '() is missing.' );
	}
}

if ( 'null' === $rx_wpsc_mode ) {
	add_filter( 'rx_pricing_data', '__return_null', PHP_INT_MAX );
}

/*
 * -------------------------------------------------------------------------
 * References
 * -------------------------------------------------------------------------
 */

$rx_wpsc_d = rx_pricing_data();

$rx_wpsc_ref = array(
	'brand'       => rx_brand(),
	'settings'    => array(),
	'urls'        => array(),
	'helpers'     => null,
	'plan_labels' => null,
	'free'        => null,
	'conds'       => null,
);
foreach ( RX_WPSC_SETTINGS as $rx_wpsc_key ) {
	$rx_wpsc_ref['settings'][ $rx_wpsc_key ] = rx_setting( $rx_wpsc_key );
}
foreach ( $rx_wpsc_urls as $rx_wpsc_key ) {
	$rx_wpsc_ref['urls'][ $rx_wpsc_key ] = rx_url( $rx_wpsc_key );
}

if ( null !== $rx_wpsc_d ) {
	$rx_wpsc_ref['helpers'] = array(
		'cost_text'    => rx_pricing_cost_text( $rx_wpsc_d ),
		'compare_text' => rx_pricing_compare_text( $rx_wpsc_d ),
		'terms_text'   => rx_pricing_terms_text( $rx_wpsc_d ),
		'users_text'   => rx_plan_users_text( $rx_wpsc_d ),
		'credits_text' => rx_plan_credits_text( $rx_wpsc_d ),
	);

	$rx_wpsc_ref['plan_labels'] = array();
	foreach ( $rx_wpsc_feats as $rx_wpsc_feat ) {
		$rx_wpsc_ref['plan_labels'][ $rx_wpsc_feat ] = rx_feature_plan_label( $rx_wpsc_d, $rx_wpsc_feat );
	}

	// Free plan facts, as pricing.php and faq.php on the PHP site build them.
	$rx_wpsc_free = rx_pricing_split( $rx_wpsc_d )['free'][0] ?? null;
	if ( $rx_wpsc_free ) {
		$rx_wpsc_limits      = rx_plan_bill_limits( $rx_wpsc_d, (int) $rx_wpsc_free['id'] );
		$rx_wpsc_ref['free'] = array(
			'name'       => (string) $rx_wpsc_free['name'],
			'sales'      => $rx_wpsc_limits['sales'],
			'purchase'   => $rx_wpsc_limits['purchase'],
			'users'      => isset( $rx_wpsc_free['user_limit'] ) ? (int) $rx_wpsc_free['user_limit'] : null,
			'users_text' => (int) ( $rx_wpsc_free['user_limit'] ?? 1 ) === 1 ? 'one user' : (int) $rx_wpsc_free['user_limit'] . ' users',
		);
	}

	$rx_wpsc_ref['conds'] = array(
		'pricing'             => true,
		'free'                => null !== $rx_wpsc_ref['free'],
		'no_free'             => null === $rx_wpsc_ref['free'],
		'free_sales_limit'    => null !== $rx_wpsc_ref['free'] && null !== $rx_wpsc_ref['free']['sales'],
		'free_purchase_limit' => null !== $rx_wpsc_ref['free'] && null !== $rx_wpsc_ref['free']['purchase'],
	);
}

/**
 * esc_html() on every string of a value, recursively.
 *
 * @param mixed $value Value.
 * @return mixed
 */
function rx_wpsc_esc( $value ) {
	if ( is_string( $value ) ) {
		return esc_html( $value );
	}
	if ( is_array( $value ) ) {
		return array_map( 'rx_wpsc_esc', $value );
	}
	return $value;
}

$rx_wpsc_esc_url = array(
	'urls'     => array_map( 'esc_url', $rx_wpsc_ref['urls'] ),
	'settings' => array(),
);
foreach ( RX_WPSC_URL_SETTINGS as $rx_wpsc_key ) {
	$rx_wpsc_esc_url['settings'][ $rx_wpsc_key ] = esc_url( (string) $rx_wpsc_ref['settings'][ $rx_wpsc_key ] );
}

$rx_wpsc_shortcodes = array();
foreach ( RX_WPSC_TAGS as $rx_wpsc_tag ) {
	$rx_wpsc_shortcodes[ $rx_wpsc_tag ] = shortcode_exists( $rx_wpsc_tag );
}

/*
 * -------------------------------------------------------------------------
 * Cases
 * -------------------------------------------------------------------------
 */

$rx_wpsc_results = array();
foreach ( $rx_wpsc_cases as $rx_wpsc_case ) {
	$rx_wpsc_id     = $rx_wpsc_case['id'];
	$rx_wpsc_via    = $rx_wpsc_case['via'] ?? 'do_shortcode';
	$rx_wpsc_writes = count( rx_wpsc_log( 'writes' ) );
	$rx_wpsc_http   = count( rx_wpsc_log( 'http' ) );
	$rx_wpsc_result = array( 'id' => $rx_wpsc_id );

	rx_wpsc_phase( $rx_wpsc_id );
	$rx_wpsc_level = error_reporting( E_ALL );
	set_error_handler(
		static function ( int $severity, string $message, string $file, int $line ): bool {
			if ( ! ( error_reporting() & $severity ) ) {
				return false;
			}
			throw new ErrorException( $message, 0, $severity, $file, $line );
		}
	);

	ob_start();
	try {
		$rx_wpsc_result['out'] = (string) $rx_wpsc_via( $rx_wpsc_case['code'] );
	} catch ( Throwable $e ) {
		$rx_wpsc_result['out']   = null;
		$rx_wpsc_result['error'] = get_class( $e ) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
	}
	$rx_wpsc_echo = (string) ob_get_clean();

	restore_error_handler();
	error_reporting( $rx_wpsc_level );
	rx_wpsc_phase( '' );

	if ( '' !== $rx_wpsc_echo ) {
		$rx_wpsc_result['output'] = $rx_wpsc_echo;
	}
	$rx_wpsc_result['writes'] = array_column( array_slice( rx_wpsc_log( 'writes' ), $rx_wpsc_writes ), 'query' );
	$rx_wpsc_result['http']   = array_column( array_slice( rx_wpsc_log( 'http' ), $rx_wpsc_http ), 'url' );
	$rx_wpsc_results[]        = $rx_wpsc_result;
}

/*
 * -------------------------------------------------------------------------
 * Output
 * -------------------------------------------------------------------------
 */

$rx_wpsc_all_writes = rx_wpsc_log( 'writes' );
$rx_wpsc_boot       = array_values( array_filter( $rx_wpsc_all_writes, static fn( $w ) => 'bootstrap' === $w['phase'] ) );

$rx_wpsc_report = array(
	'mode'             => $rx_wpsc_mode,
	'theme'            => array(
		'template'   => get_template(),
		'stylesheet' => get_stylesheet(),
	),
	'shortcodes'       => $rx_wpsc_shortcodes,
	'gates'            => array(
		'query'                    => PHP_INT_MAX === has_filter( 'query', 'rx_wpsc_query_gate' ),
		'pre_http_request'         => PHP_INT_MAX === has_filter( 'pre_http_request', 'rx_wpsc_http_gate' ),
		'pre_transient_rx_pricing' => PHP_INT_MAX === has_filter( 'pre_transient_rx_pricing', 'rx_wpsc_cached_pricing' ),
	),
	'pricing'          => array(
		'source'          => rx_pricing_source(),
		'is_null'         => null === $rx_wpsc_d,
		'equals_snapshot' => $rx_wpsc_d === rx_wpsc_snapshot(),
	),
	'ref'              => $rx_wpsc_ref,
	'esc'              => rx_wpsc_esc( $rx_wpsc_ref ),
	'esc_url'          => $rx_wpsc_esc_url,
	'results'          => $rx_wpsc_results,
	'writes'           => array(
		'bootstrap' => array_column( $rx_wpsc_boot, 'query' ),
		'other'     => array_values( array_filter( $rx_wpsc_all_writes, static fn( $w ) => 'bootstrap' !== $w['phase'] ) ),
	),
	'http'             => rx_wpsc_log( 'http' ),
	'bootstrap_output' => $rx_wpsc_boot_output,
);

try {
	echo json_encode( $rx_wpsc_report, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), "\n";
} catch ( JsonException $e ) {
	rx_wpsc_fail( 'Encoding the results failed: ' . $e->getMessage() );
}
