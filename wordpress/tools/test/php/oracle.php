<?php
/**
 * Oracle for Property 1 (pricing parity): run the PHP site's own pricing
 * helpers on one generated pricing document.
 *
 *     php oracle.php <copy-dir>
 *     stdin:  { "doc": { plans, terms, groups, features, statuses },
 *               "calls": [ { "fn": "plan_price", "args": [ 3 ] }, ... ] }
 *     stdout: { "source": "cache", "api": "…", "cache": "…",
 *               "results": [ { "ok": true, "value": "₹4,999" } | { "ok": false, "error": "…" }, … ] }
 *
 * <copy-dir> is a temporary folder made by prop-pricing-parity.test.mjs with:
 * - config.php        copy of partials/config.php, PRICING_API pointed at an unreachable local address
 * - pricing-data.php  unchanged copy of partials/pricing-data.php (its cache path is __DIR__-relative,
 *                     so it caches inside the copy and requires the copied config.php)
 * - pricing-page.php  term_total() and the meta-description loop, copied verbatim from pricing.php
 *                     (pricing.php itself renders a page, so it is never loaded)
 *
 * Steps:
 * 1. Refuse to run when the copy is inside the repository, when its API is not
 *    a 127.0.0.1 address or when its cache file is outside the copy.
 * 2. Write "doc" to the copy's PRICING_CACHE with a fresh mtime, so
 *    pricing_data() reads the cache and never calls the API.
 * 3. Call pricing_data() and stop unless it reports _source 'cache'.
 * 4. Run each call; a failing call (exception, warning, notice) gives
 *    { ok: false, error } and the others still run.
 *
 * One document per process: pricing_data() and feature_plan() memoise in
 * static variables. Setup errors print to STDERR and exit with code 2.
 * The PHP site itself is only read (by the test), never loaded or written.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

// Shortest round-trip float output, whatever php.ini says (same as bridge.php).
ini_set( 'serialize_precision', '-1' );

// Warnings, notices and deprecations become exceptions, as in bridge.php.
set_error_handler(
	static function ( int $severity, string $message, string $file, int $line ): bool {
		if ( ! ( error_reporting() & $severity ) ) {
			return false;
		}
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

$rx_oracle_fail = static function ( string $message ): never {
	fwrite( STDERR, '[rx oracle] ' . $message . PHP_EOL );
	exit( 2 );
};

/** Lower-case path with forward slashes and a trailing slash, for prefix checks. */
$rx_oracle_norm = static function ( string $path ): string {
	return rtrim( strtolower( str_replace( '\\', '/', $path ) ), '/' ) . '/';
};

const RX_ORACLE_FNS = array(
	'pricing_valid',
	'pricing_plans',
	'pricing_plan',
	'plan_price',
	'plan_bill_limits',
	'pricing_terms_text',
	'pricing_split',
	'pricing_cost_text',
	'pricing_compare_text',
	'feature_plan',
	'feature_plan_label',
	'plan_users_text',
	'plan_credits_text',
	'inr',
	'term_total',
	'pricing_page_meta_bits',
);

try {
	// 1. The copy folder: outside the repository, with the three files.
	$rx_oracle_dir = isset( $argv[1] ) ? realpath( $argv[1] ) : false;
	if ( false === $rx_oracle_dir || ! is_dir( $rx_oracle_dir ) ) {
		$rx_oracle_fail( 'Usage: php oracle.php <copy-dir>' );
	}
	$rx_oracle_repo = realpath( dirname( __DIR__, 4 ) );
	if ( false !== $rx_oracle_repo && str_starts_with( $rx_oracle_norm( $rx_oracle_dir ), $rx_oracle_norm( $rx_oracle_repo ) ) ) {
		$rx_oracle_fail( 'The copy folder must be outside the repository: ' . $rx_oracle_dir );
	}
	foreach ( array( 'config.php', 'pricing-data.php', 'pricing-page.php' ) as $rx_oracle_file ) {
		if ( ! is_file( $rx_oracle_dir . '/' . $rx_oracle_file ) ) {
			$rx_oracle_fail( 'Missing ' . $rx_oracle_file . ' in ' . $rx_oracle_dir );
		}
	}

	// 2. Read the document and the calls.
	$rx_oracle_input = json_decode( (string) stream_get_contents( STDIN ), true, 512, JSON_THROW_ON_ERROR );
	if ( ! is_array( $rx_oracle_input ) || ! array_key_exists( 'doc', $rx_oracle_input ) || ! isset( $rx_oracle_input['calls'] ) || ! is_array( $rx_oracle_input['calls'] ) || ! array_is_list( $rx_oracle_input['calls'] ) ) {
		$rx_oracle_fail( 'Expected { "doc": …, "calls": [ … ] } on STDIN.' );
	}
	$rx_oracle_doc = $rx_oracle_input['doc'];

	// 3. Load the copies. They only define constants and functions.
	ob_start();
	require $rx_oracle_dir . '/pricing-data.php'; // Requires the copied config.php.
	require $rx_oracle_dir . '/pricing-page.php';
	$rx_oracle_stray = ob_get_clean();
	if ( '' !== $rx_oracle_stray ) {
		$rx_oracle_fail( 'The copies printed output while loading: ' . substr( $rx_oracle_stray, 0, 200 ) );
	}

	// 4. Guards before anything can reach the network or the disk.
	if ( '127.0.0.1' !== parse_url( PRICING_API, PHP_URL_HOST ) ) {
		$rx_oracle_fail( 'PRICING_API of the copy is not a 127.0.0.1 address: ' . PRICING_API );
	}
	if ( ! str_starts_with( $rx_oracle_norm( PRICING_CACHE ), $rx_oracle_norm( $rx_oracle_dir ) ) ) {
		$rx_oracle_fail( 'PRICING_CACHE of the copy is outside the copy folder: ' . PRICING_CACHE );
	}
	if ( ! pricing_valid( $rx_oracle_doc ) ) {
		$rx_oracle_fail( 'The document is not valid for pricing_valid(), so the cache would be skipped.' );
	}

	// 5. Write the cache with a fresh mtime and check it reads back unchanged.
	$rx_oracle_cache_dir = dirname( PRICING_CACHE );
	if ( ! is_dir( $rx_oracle_cache_dir ) && ! mkdir( $rx_oracle_cache_dir, 0775, true ) ) {
		$rx_oracle_fail( 'Could not create ' . $rx_oracle_cache_dir );
	}
	$rx_oracle_json = json_encode( $rx_oracle_doc, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( false === file_put_contents( PRICING_CACHE, $rx_oracle_json ) || ! touch( PRICING_CACHE, time() ) ) {
		$rx_oracle_fail( 'Could not write ' . PRICING_CACHE );
	}
	clearstatcache( true, PRICING_CACHE );
	if ( json_decode( (string) file_get_contents( PRICING_CACHE ), true ) !== $rx_oracle_doc ) {
		$rx_oracle_fail( 'The cache file does not read back as the document.' );
	}

	// 6. pricing_data() must take the cache path (no API call at all).
	$rx_oracle_data = pricing_data();
	if ( 'cache' !== ( $rx_oracle_data['_source'] ?? null ) ) {
		$rx_oracle_fail( 'pricing_data() did not read the cache: _source = ' . var_export( $rx_oracle_data['_source'] ?? null, true ) );
	}
} catch ( Throwable $e ) {
	$rx_oracle_fail( 'Setup failed: ' . get_class( $e ) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
}

// 7. Run the calls.
$rx_oracle_run = static function ( mixed $call ): array {
	if ( ! is_array( $call ) || ! isset( $call['fn'] ) || ! is_string( $call['fn'] ) || ! in_array( $call['fn'], RX_ORACLE_FNS, true ) ) {
		return array( 'ok' => false, 'error' => 'Error: Unknown oracle function: ' . json_encode( $call['fn'] ?? null ) );
	}
	$args = $call['args'] ?? array();
	if ( ! is_array( $args ) || ! array_is_list( $args ) ) {
		return array( 'ok' => false, 'error' => 'Error: "args" must be a JSON array for ' . $call['fn'] );
	}

	ob_start();
	try {
		$value = ( $call['fn'] )( ...$args );
	} catch ( Throwable $e ) {
		ob_end_clean();
		return array( 'ok' => false, 'error' => get_class( $e ) . ': ' . $e->getMessage() );
	}
	$output = (string) ob_get_clean();

	try {
		json_encode( $value, JSON_THROW_ON_ERROR );
	} catch ( JsonException $e ) {
		return array( 'ok' => false, 'error' => 'JsonException: Result of ' . $call['fn'] . ' cannot be encoded: ' . $e->getMessage() );
	}

	$result = array( 'ok' => true, 'value' => $value );
	if ( '' !== $output ) {
		$result['output'] = $output;
	}
	return $result;
};

$rx_oracle_results = array();
foreach ( $rx_oracle_input['calls'] as $rx_oracle_call ) {
	$rx_oracle_results[] = $rx_oracle_run( $rx_oracle_call );
}

try {
	echo json_encode(
		array(
			'source'  => $rx_oracle_data['_source'],
			'api'     => PRICING_API,
			'cache'   => PRICING_CACHE,
			'results' => $rx_oracle_results,
		),
		JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	), "\n";
} catch ( JsonException $e ) {
	$rx_oracle_fail( 'Encoding results failed: ' . $e->getMessage() );
}
