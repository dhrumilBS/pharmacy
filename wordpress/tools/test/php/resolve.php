<?php
/**
 * Test helper for Property 2: run rx_pricing_resolve() on described scenarios.
 *
 * Closures can't travel through the JSON bridge, so this script builds the
 * sources and hooks itself from a description. It loads every file in
 * themes/pharmacy/inc/core/ (sorted, with RX_TESTING defined, as bridge.php
 * does), reads a JSON batch of scenarios from STDIN and prints one JSON result
 * per scenario to STDOUT, in the same order. One PHP process per batch.
 *
 * Scenario:
 *
 *     {
 *       "sources":    [ [ "live", { "kind": "valid", "variant": "mini", "form": "closure" } ], … ],
 *       "hooks":      [ [ "on_live", { "kind": "throw", "variant": "exception", "form": "method" } ], … ],
 *       "omit_hooks": false
 *     }
 *
 * - "sources" and "hooks" are [ key, spec ] pairs, added to the $sources and
 *   $hooks arrays in the given order, so the test controls the key order.
 * - Source kinds: "valid" (returns a valid pricing document whose
 *   "_rx_marker" is the key), "invalid" (returns something that is not a
 *   valid document), "throw" (throws), "noncallable" (the entry is a plain
 *   value, not a callable). Hook kinds: "ok", "throw", "noncallable".
 * - "variant" picks the document, value or exception (lists below).
 * - "form" picks the callable shape: "closure", "invokable" (object with
 *   __invoke) or "method" ([ object, 'run' ]).
 * - "omit_hooks": true calls rx_pricing_resolve( $sources ) with one argument.
 *
 * Result per scenario:
 *
 *     {
 *       "threw":     null, or "Class: message" when rx_pricing_resolve() threw,
 *       "shape_ok":  the result is a two-item list,
 *       "source":    the source name it returned (null when shape_ok is false),
 *       "marker":    "_rx_marker" of the returned document, or null for null,
 *       "identical": the returned document === what the source named by its
 *                    marker returned,
 *       "calls":     every source and hook call, in order:
 *                    { kind: "source", name, argc } and
 *                    { kind: "hook", name, argc, marker, same }, where same =
 *                    the hook got exactly the document its source returned
 *                    (on_live → live, on_stale → stale; false for other keys),
 *       "warnings":  PHP warnings, notices or deprecations raised meanwhile,
 *       "output":    anything printed by rx_pricing_resolve()
 *     }
 *
 * Every value is built before rx_pricing_resolve() runs, so a malformed
 * scenario (unknown kind, variant or form) stops the script with a message on
 * STDERR and exit code 2 instead of looking like a failing source.
 *
 * Used by test/prop-pricing-resolve.test.mjs through runPhp(). CLI only.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

defined( 'RX_TESTING' ) || define( 'RX_TESTING', true );

// Shortest round-trip float output, whatever php.ini says.
ini_set( 'serialize_precision', '-1' );

$rx_resolve_fail = static function ( string $message ): never {
	fwrite( STDERR, '[rx resolve] ' . $message . PHP_EOL );
	exit( 2 );
};

// Warnings, notices and deprecations are recorded instead of thrown, so they
// can't change what rx_pricing_resolve() does. The test expects none.
$rx_resolve_warnings = array();
set_error_handler(
	static function ( int $severity, string $message, string $file, int $line ) use ( &$rx_resolve_warnings ): bool {
		if ( ! ( error_reporting() & $severity ) ) {
			return false;
		}
		$rx_resolve_warnings[] = $message . ' in ' . basename( $file ) . ':' . $line;
		return true;
	}
);

// 1. Load the theme's pure core files and the pricing snapshot.
$rx_resolve_theme = dirname( __DIR__, 3 ) . '/themes/pharmacy';
$rx_resolve_files = glob( $rx_resolve_theme . '/inc/core/*.php' );
$rx_resolve_files = is_array( $rx_resolve_files ) ? $rx_resolve_files : array();
sort( $rx_resolve_files, SORT_STRING );

ob_start();
try {
	foreach ( $rx_resolve_files as $rx_resolve_file ) {
		require_once $rx_resolve_file;
	}
} catch ( Throwable $e ) {
	ob_end_clean();
	$rx_resolve_fail( 'Loading core files failed: ' . get_class( $e ) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
}
$rx_resolve_stray = (string) ob_get_clean();
if ( '' !== $rx_resolve_stray ) {
	$rx_resolve_fail( 'Core files printed output while loading: ' . substr( $rx_resolve_stray, 0, 200 ) );
}
if ( $rx_resolve_warnings ) {
	$rx_resolve_fail( 'Core files raised warnings while loading: ' . implode( '; ', $rx_resolve_warnings ) );
}
if ( ! function_exists( 'rx_pricing_resolve' ) ) {
	$rx_resolve_fail( 'rx_pricing_resolve() is not defined in themes/pharmacy/inc/core/.' );
}

$rx_resolve_snapshot_file = $rx_resolve_theme . '/data/pricing.json';
if ( ! is_file( $rx_resolve_snapshot_file ) || ! is_readable( $rx_resolve_snapshot_file ) ) {
	$rx_resolve_fail( 'Missing pricing snapshot: ' . $rx_resolve_snapshot_file );
}
$rx_resolve_json = (string) file_get_contents( $rx_resolve_snapshot_file );
try {
	$rx_resolve_snapshot = json_decode( $rx_resolve_json, true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $e ) {
	$rx_resolve_fail( 'Invalid pricing snapshot JSON: ' . $e->getMessage() );
}
if ( ! is_array( $rx_resolve_snapshot ) ) {
	$rx_resolve_fail( 'The pricing snapshot is not a JSON object.' );
}

// 2. Building blocks.

// Smallest document that has every part a valid one needs (see the design:
// arrays plans, terms, groups, features and statuses, at least one plan and
// one term, an id, name and price on every plan).
$rx_resolve_mini = array(
	'plans'    => array( array( 'id' => 1, 'name' => 'Starter', 'price' => 0 ) ),
	'terms'    => array( array( 'id' => 1, 'years' => 1, 'discount_percent' => 0 ) ),
	'groups'   => array(),
	'features' => array(),
	'statuses' => array(),
);

/** Valid documents, tagged with the source key. */
$rx_resolve_valid = static function ( string $variant, string $marker ) use ( $rx_resolve_mini, $rx_resolve_snapshot ): array {
	$d = $rx_resolve_mini;
	switch ( $variant ) {
		case 'mini':
			break;
		case 'mini_two_plans':
			$d['plans'][] = array( 'id' => 3, 'name' => 'Basic', 'price' => 4999, 'sort_order' => 2 );
			$d['terms'][] = array( 'id' => 2, 'years' => 3, 'discount_percent' => 10 );
			break;
		case 'mini_extra_keys':
			$d += array( 'version' => 2, 'currency' => 'INR', 'notes' => null );
			break;
		case 'mini_string_values':
			$d['plans'] = array( array( 'id' => '1', 'name' => 'Starter', 'price' => '0' ) );
			break;
		case 'snapshot':
			$d = $rx_resolve_snapshot;
			break;
		default:
			throw new InvalidArgumentException( 'Unknown valid variant: ' . $variant );
	}
	$d['_rx_marker'] = $marker;
	return $d;
};

/** Values that are not a valid document. Document-shaped ones carry a marker. */
$rx_resolve_invalid = static function ( string $variant, string $marker ) use ( $rx_resolve_mini, $rx_resolve_snapshot, $rx_resolve_json ): mixed {
	$d               = $rx_resolve_mini;
	$d['_rx_marker'] = $marker . ' (invalid)';
	$s               = $rx_resolve_snapshot;
	$s['_rx_marker'] = $marker . ' (invalid)';

	switch ( $variant ) {
		case 'null':
		case 'void':
			return null;
		case 'false':
			return false;
		case 'true':
			return true;
		case 'zero':
			return 0;
		case 'one':
			return 1;
		case 'empty_string':
			return '';
		case 'string':
			return 'ok';
		case 'json_string':
			return $rx_resolve_json; // The snapshot as undecoded JSON text.
		case 'empty_array':
			return array();
		case 'list':
			return array( 1, 2, 3 );
		case 'object':
			return (object) $d;
		case 'no_plans':
			unset( $d['plans'] );
			return $d;
		case 'empty_plans':
			$d['plans'] = array();
			return $d;
		case 'plans_string':
			$d['plans'] = 'Starter';
			return $d;
		case 'no_terms':
			unset( $d['terms'] );
			return $d;
		case 'empty_terms':
			$d['terms'] = array();
			return $d;
		case 'no_groups':
			unset( $d['groups'] );
			return $d;
		case 'groups_null':
			$d['groups'] = null;
			return $d;
		case 'no_features':
			unset( $d['features'] );
			return $d;
		case 'features_string':
			$d['features'] = 'feat_purchase_order';
			return $d;
		case 'no_statuses':
			unset( $d['statuses'] );
			return $d;
		case 'statuses_int':
			$d['statuses'] = 0;
			return $d;
		case 'plan_no_id':
			unset( $d['plans'][0]['id'] );
			return $d;
		case 'plan_no_name':
			unset( $d['plans'][0]['name'] );
			return $d;
		case 'plan_no_price':
			unset( $d['plans'][0]['price'] );
			return $d;
		case 'plan_null_price':
			$d['plans'][0]['price'] = null;
			return $d;
		case 'plan_not_array':
			$d['plans'] = array( 'Starter' );
			return $d;
		case 'second_plan_no_price':
			$d['plans'][] = array( 'id' => 3, 'name' => 'Basic' );
			return $d;
		case 'snapshot_no_terms':
			unset( $s['terms'] );
			return $s;
		case 'snapshot_plan_no_price':
			unset( $s['plans'][2]['price'] );
			return $s;
		default:
			throw new InvalidArgumentException( 'Unknown invalid variant: ' . $variant );
	}
};

/** A callable that throws the chosen Throwable (most of them for real). */
$rx_resolve_thrower = static function ( string $variant ): Closure {
	return match ( $variant ) {
		'exception'   => static function (): never {
			throw new RuntimeException( 'Source failed' );
		},
		'error'       => static function (): never {
			throw new Error( 'Source broke' );
		},
		'type_error'  => static fn() => ( static fn( int $n ): int => $n )( 'x' ),
		'json'        => static fn() => json_decode( '{', true, 512, JSON_THROW_ON_ERROR ),
		'division'    => static fn() => intdiv( 1, 0 ),
		'value_error' => static fn() => str_repeat( 'x', -1 ),
		default       => throw new InvalidArgumentException( 'Unknown throw variant: ' . $variant ),
	};
};

/** Plain values that is_callable() rejects. */
$rx_resolve_noncallable = static function ( string $variant, string $name ) use ( $rx_resolve_valid ): mixed {
	return match ( $variant ) {
		'null'         => null,
		'true'         => true,
		'false'        => false,
		'int'          => 42,
		'float'        => 1.5,
		'empty_string' => '',
		'string'       => 'rx_no_such_function',
		'key_name'     => $name,
		'empty_array'  => array(),
		'valid_doc'    => $rx_resolve_valid( 'mini', $name ), // The document itself instead of a callable.
		'no_class'     => array( 'Rx_No_Such_Class', 'run' ),
		'no_method'    => array( new stdClass(), 'run' ),
		default        => throw new InvalidArgumentException( 'Unknown noncallable variant: ' . $variant ),
	};
};

/** The body as a Closure, an invokable object or an [ object, 'run' ] array. */
$rx_resolve_wrap = static function ( Closure $body, mixed $form ): callable {
	$probe = new class( $body ) {
		public function __construct( private Closure $body ) {}

		public function __invoke( mixed ...$args ): mixed {
			return ( $this->body )( ...$args );
		}

		public function run( mixed ...$args ): mixed {
			return ( $this->body )( ...$args );
		}
	};
	return match ( $form ) {
		'closure'   => $body,
		'invokable' => $probe,
		'method'    => array( $probe, 'run' ),
		default     => throw new InvalidArgumentException( 'Unknown callable form: ' . var_export( $form, true ) ),
	};
};

/** Marker of a returned or received document. */
$rx_resolve_marker = static function ( mixed $v ): ?string {
	if ( is_array( $v ) ) {
		return isset( $v['_rx_marker'] ) && is_string( $v['_rx_marker'] ) ? $v['_rx_marker'] : '(array without marker)';
	}
	return null === $v ? null : '(' . get_debug_type( $v ) . ')';
};

/** A required string field of a spec. */
$rx_resolve_str = static function ( array $spec, string $key ): string {
	if ( ! isset( $spec[ $key ] ) || ! is_string( $spec[ $key ] ) ) {
		throw new InvalidArgumentException( 'Spec needs a string "' . $key . '": ' . json_encode( $spec ) );
	}
	return $spec[ $key ];
};

/** One source entry. Callable kinds log each call into $log. */
$rx_resolve_source = static function ( string $name, array $spec, array &$log, array &$returned ) use ( $rx_resolve_str, $rx_resolve_valid, $rx_resolve_invalid, $rx_resolve_thrower, $rx_resolve_noncallable, $rx_resolve_wrap ): mixed {
	$kind    = $rx_resolve_str( $spec, 'kind' );
	$variant = $rx_resolve_str( $spec, 'variant' );

	if ( 'noncallable' === $kind ) {
		$value = $rx_resolve_noncallable( $variant, $name );
		if ( is_callable( $value ) ) {
			throw new LogicException( 'Noncallable variant is callable: ' . $variant );
		}
		return $value;
	}

	$value = null;
	$throw = null;
	switch ( $kind ) {
		case 'valid':
			$value = $rx_resolve_valid( $variant, $name );
			break;
		case 'invalid':
			$value = $rx_resolve_invalid( $variant, $name );
			break;
		case 'throw':
			$throw = $rx_resolve_thrower( $variant );
			break;
		default:
			throw new InvalidArgumentException( 'Unknown source kind: ' . $kind );
	}
	$void = 'invalid' === $kind && 'void' === $variant;

	$body = static function ( mixed ...$args ) use ( $name, $value, $throw, $void, &$log, &$returned ) {
		$log[] = array(
			'kind' => 'source',
			'name' => $name,
			'argc' => count( $args ),
		);
		if ( $throw ) {
			return $throw();
		}
		if ( $void ) {
			return;
		}
		$returned[ $name ] = $value;
		return $value;
	};
	return $rx_resolve_wrap( $body, $spec['form'] ?? null );
};

/** One hook entry. Callable kinds log each call, with the document received. */
$rx_resolve_hook = static function ( string $name, array $spec, array &$log, array &$returned ) use ( $rx_resolve_str, $rx_resolve_thrower, $rx_resolve_noncallable, $rx_resolve_wrap, $rx_resolve_marker ): mixed {
	$kind = $rx_resolve_str( $spec, 'kind' );

	if ( 'noncallable' === $kind ) {
		$value = $rx_resolve_noncallable( $rx_resolve_str( $spec, 'variant' ), $name );
		if ( is_callable( $value ) ) {
			throw new LogicException( 'Noncallable variant is callable: ' . $spec['variant'] );
		}
		return $value;
	}

	$throw = match ( $kind ) {
		'ok'    => null,
		'throw' => $rx_resolve_thrower( $rx_resolve_str( $spec, 'variant' ) ),
		default => throw new InvalidArgumentException( 'Unknown hook kind: ' . $kind ),
	};
	$for = array(
		'on_live'  => 'live',
		'on_stale' => 'stale',
	)[ $name ] ?? null;

	$body = static function ( mixed ...$args ) use ( $name, $for, $throw, $rx_resolve_marker, &$log, &$returned ) {
		$d     = $args[0] ?? null;
		$log[] = array(
			'kind'   => 'hook',
			'name'   => $name,
			'argc'   => count( $args ),
			'marker' => $rx_resolve_marker( $d ),
			'same'   => null !== $for && array_key_exists( $for, $returned ) && $d === $returned[ $for ],
		);
		if ( $throw ) {
			$throw();
		}
		return null;
	};
	return $rx_resolve_wrap( $body, $spec['form'] ?? null );
};

/** [ name, spec ] from one pair, checking the shape and that the key is new. */
$rx_resolve_pair = static function ( mixed $pair, array $taken ): array {
	if ( ! is_array( $pair ) || ! array_is_list( $pair ) || 2 !== count( $pair ) || ! is_string( $pair[0] ) || ! is_array( $pair[1] ) ) {
		throw new InvalidArgumentException( 'Each entry must be a [ key, spec ] pair: ' . json_encode( $pair ) );
	}
	if ( array_key_exists( $pair[0], $taken ) ) {
		throw new InvalidArgumentException( 'Duplicate key: ' . $pair[0] );
	}
	return $pair;
};

// 3. Run one scenario.
$rx_resolve_run = static function ( mixed $scenario ) use ( $rx_resolve_pair, $rx_resolve_source, $rx_resolve_hook, $rx_resolve_marker, &$rx_resolve_warnings ): array {
	if ( ! is_array( $scenario ) ) {
		throw new InvalidArgumentException( 'Each scenario must be an object.' );
	}
	foreach ( array( 'sources', 'hooks' ) as $list ) {
		if ( ! isset( $scenario[ $list ] ) || ! is_array( $scenario[ $list ] ) || ! array_is_list( $scenario[ $list ] ) ) {
			throw new InvalidArgumentException( 'Scenario needs a "' . $list . '" list.' );
		}
	}
	if ( ! isset( $scenario['omit_hooks'] ) || ! is_bool( $scenario['omit_hooks'] ) ) {
		throw new InvalidArgumentException( 'Scenario needs a boolean "omit_hooks".' );
	}

	$warn_from = count( $rx_resolve_warnings );
	$log       = array();
	$returned  = array();

	$sources = array();
	foreach ( $scenario['sources'] as $pair ) {
		[ $name, $spec ]  = $rx_resolve_pair( $pair, $sources );
		$sources[ $name ] = $rx_resolve_source( $name, $spec, $log, $returned );
	}
	$hooks = array();
	foreach ( $scenario['hooks'] as $pair ) {
		[ $name, $spec ] = $rx_resolve_pair( $pair, $hooks );
		$hooks[ $name ]  = $rx_resolve_hook( $name, $spec, $log, $returned );
	}

	$result = null;
	$threw  = null;
	ob_start();
	try {
		$result = $scenario['omit_hooks'] ? rx_pricing_resolve( $sources ) : rx_pricing_resolve( $sources, $hooks );
	} catch ( Throwable $e ) {
		$threw = get_class( $e ) . ': ' . $e->getMessage();
	}
	$output = (string) ob_get_clean();

	$shape_ok = is_array( $result ) && array_is_list( $result ) && 2 === count( $result );
	$doc      = $shape_ok ? $result[0] : null;
	$marker   = $rx_resolve_marker( $doc );

	return array(
		'threw'     => $threw,
		'shape_ok'  => $shape_ok,
		'source'    => $shape_ok ? $result[1] : null,
		'marker'    => $marker,
		'identical' => is_array( $doc ) && is_string( $marker ) && array_key_exists( $marker, $returned ) && $doc === $returned[ $marker ],
		'calls'     => $log,
		'warnings'  => array_slice( $rx_resolve_warnings, $warn_from ),
		'output'    => $output,
	);
};

// 4. Read the batch, run it, print the results.
$rx_resolve_input = stream_get_contents( STDIN );
if ( false === $rx_resolve_input || '' === trim( $rx_resolve_input ) ) {
	$rx_resolve_fail( 'Expected a JSON array of scenarios on STDIN, got nothing.' );
}
try {
	$rx_resolve_scenarios = json_decode( $rx_resolve_input, true, 512, JSON_THROW_ON_ERROR );
} catch ( JsonException $e ) {
	$rx_resolve_fail( 'Invalid JSON on STDIN: ' . $e->getMessage() );
}
if ( ! is_array( $rx_resolve_scenarios ) || ! array_is_list( $rx_resolve_scenarios ) ) {
	$rx_resolve_fail( 'Expected a JSON array of scenarios on STDIN.' );
}

$rx_resolve_results = array();
foreach ( $rx_resolve_scenarios as $rx_resolve_i => $rx_resolve_scenario ) {
	try {
		$rx_resolve_results[] = $rx_resolve_run( $rx_resolve_scenario );
	} catch ( Throwable $e ) {
		$rx_resolve_fail( 'Scenario ' . $rx_resolve_i . ': ' . get_class( $e ) . ': ' . $e->getMessage() );
	}
}

try {
	echo json_encode( $rx_resolve_results, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), "\n";
} catch ( JsonException $e ) {
	$rx_resolve_fail( 'Encoding results failed: ' . $e->getMessage() );
}
