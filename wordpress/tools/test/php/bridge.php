<?php
/**
 * Test bridge: call the theme's pure PHP functions from Node.
 *
 * Loads every file in themes/pharmacy/inc/core/ (pure PHP, no WordPress
 * calls) with RX_TESTING defined, reads a JSON batch of calls from STDIN and
 * prints one JSON result per call to STDOUT, in the same order:
 *
 *     in:  [ { "fn": "rx_inr", "args": [ 1000 ] }, { "fn": "rx_nope" } ]
 *     out: [ { "ok": true, "value": "1,000" },
 *            { "ok": false, "error": "Error: Unknown function: rx_nope" } ]
 *
 * - Only user-defined functions whose name starts with rx_ can be called.
 * - "args" is optional and must be a JSON array (positional arguments).
 * - A failing call (exception, PHP warning or notice, unencodable result)
 *   gives { ok: false, error } and the rest of the batch still runs.
 * - Anything a call echoes is returned in "output" (only when non-empty).
 * - Invalid input, or a warning or error while loading the core files, prints
 *   a message to STDERR and exits with code 2.
 *
 * Used through callPhp() in tools/test/lib/php.mjs. CLI only.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

defined( 'RX_TESTING' ) || define( 'RX_TESTING', true );

// Shortest round-trip float output, whatever php.ini says.
ini_set( 'serialize_precision', '-1' );

// Every warning, notice and deprecation becomes an exception, so a call that
// triggers one fails instead of passing silently. @-suppressed errors are skipped.
set_error_handler(
	static function ( int $severity, string $message, string $file, int $line ): bool {
		if ( ! ( error_reporting() & $severity ) ) {
			return false;
		}
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

$rx_bridge_fail = static function ( string $message ): never {
	fwrite( STDERR, '[rx bridge] ' . $message . PHP_EOL );
	exit( 2 );
};

$rx_bridge_describe = static function ( Throwable $e ): string {
	return get_class( $e ) . ': ' . $e->getMessage();
};

// 1. Load the theme's pure core files (themes/pharmacy/inc/core/*.php).
//    Missing directory or no files yet: nothing is loaded and every call
//    reports an unknown function.
$rx_bridge_core = dirname( __DIR__, 3 ) . '/themes/pharmacy/inc/core';
$rx_bridge_files = glob( $rx_bridge_core . '/*.php' );
$rx_bridge_files = is_array( $rx_bridge_files ) ? $rx_bridge_files : array();
sort( $rx_bridge_files, SORT_STRING );

ob_start();
try {
	foreach ( $rx_bridge_files as $rx_bridge_file ) {
		require_once $rx_bridge_file;
	}
} catch ( Throwable $e ) {
	ob_end_clean();
	$rx_bridge_fail( 'Loading core files failed: ' . $rx_bridge_describe( $e ) . ' in ' . $e->getFile() . ':' . $e->getLine() );
}
$rx_bridge_stray = ob_get_clean();
if ( '' !== $rx_bridge_stray ) {
	$rx_bridge_fail( 'Core files printed output while loading: ' . substr( $rx_bridge_stray, 0, 200 ) );
}

// 2. Read and validate the batch.
$rx_bridge_input = stream_get_contents( STDIN );
if ( false === $rx_bridge_input || '' === trim( $rx_bridge_input ) ) {
	$rx_bridge_fail( 'Expected a JSON array of calls on STDIN, got nothing.' );
}
try {
	$rx_bridge_calls = json_decode( $rx_bridge_input, true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $e ) {
	$rx_bridge_fail( 'Invalid JSON on STDIN: ' . $e->getMessage() );
}
if ( ! is_array( $rx_bridge_calls ) || ! array_is_list( $rx_bridge_calls ) ) {
	$rx_bridge_fail( 'Expected a JSON array of calls on STDIN.' );
}

// 3. Run each call on its own.
$rx_bridge_run = static function ( mixed $call ) use ( $rx_bridge_describe ): array {
	if ( ! is_array( $call ) || array_is_list( $call ) || ! isset( $call['fn'] ) || ! is_string( $call['fn'] ) ) {
		return array( 'ok' => false, 'error' => 'Error: Each call must be an object with a string "fn".' );
	}
	$fn   = $call['fn'];
	$args = $call['args'] ?? array();

	if ( ! preg_match( '/^rx_[A-Za-z0-9_]+$/D', $fn ) ) {
		return array( 'ok' => false, 'error' => 'Error: Only rx_* functions can be called, got: ' . $fn );
	}
	if ( ! function_exists( $fn ) || ! ( new ReflectionFunction( $fn ) )->isUserDefined() ) {
		return array( 'ok' => false, 'error' => 'Error: Unknown function: ' . $fn );
	}
	if ( ! is_array( $args ) || ! array_is_list( $args ) ) {
		return array( 'ok' => false, 'error' => 'Error: "args" must be a JSON array for ' . $fn );
	}

	ob_start();
	try {
		$value = $fn( ...$args );
	} catch ( Throwable $e ) {
		ob_end_clean();
		return array( 'ok' => false, 'error' => $rx_bridge_describe( $e ) );
	}
	$output = (string) ob_get_clean();

	// Check the value encodes (invalid UTF-8, INF, NAN fail here, per call).
	try {
		json_encode( $value, JSON_THROW_ON_ERROR );
	} catch ( JsonException $e ) {
		return array( 'ok' => false, 'error' => 'JsonException: Result of ' . $fn . ' cannot be encoded: ' . $e->getMessage() );
	}

	$result = array( 'ok' => true, 'value' => $value );
	if ( '' !== $output ) {
		$result['output'] = $output;
	}
	return $result;
};

$rx_bridge_results = array();
foreach ( $rx_bridge_calls as $rx_bridge_call ) {
	$rx_bridge_results[] = $rx_bridge_run( $rx_bridge_call );
}

// 4. Print the results.
try {
	echo json_encode( $rx_bridge_results, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), "\n";
} catch ( JsonException $e ) {
	$rx_bridge_fail( 'Encoding results failed: ' . $e->getMessage() );
}
