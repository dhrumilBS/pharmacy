// Feature: wordpress-migration, Property 4: Post slugs never equal a topic
//
// **Validates: Requirements 7.4**
//
// rx_safe_post_slug( $slug, $topics ) in themes/pharmacy/inc/core/blog.php
// must keep post slugs clear of the /blog/{topic}/ views. For any slug and
// any topic set (a list of keys, or a key => [ name, tone ] map like
// RX_TOPICS):
//
//   1. the result is never a topic key;
//   2. a slug that is not a topic key comes back unchanged, byte for byte;
//   3. a topic slug comes back as its trimmed self + one or more "-guide",
//      with no more "-guide" than needed (every shorter form is a topic);
//   4. it is idempotent: f( f( s ) ) === f( s ).
//
// "Is a topic key" uses the same match as the PHP code: PHP trim() (only
// " \t\n\r\0\x0B") and ASCII-only strtolower() on both sides, empty keys
// ignored. So "\u00a0expiry" is not a topic, while " Expiry\t" is.
//
// PHP runs in a few batched processes: the cases fast-check will generate are
// sampled up front with the same seed and computed in two bridge calls (one
// for f( s ), one for f( f( s ) )). Inputs that were not pre-computed (for
// example while shrinking a failure) fall back to one bridge call each.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import fc from 'fast-check';

import { callPhp } from './lib/php.mjs';

const NUM_RUNS = 200;

// Same keys, names and tones as RX_TOPICS in themes/pharmacy/inc/blog.php.
const REAL_MAP = {
	expiry: [ 'Expiry & stock', 'teal' ],
	purchase: [ 'Purchase', 'blue' ],
	switching: [ 'Switching', 'navy' ],
	staff: [ 'Counter & staff', 'amber' ],
	buying: [ 'Buying guides', 'slate' ],
};
const REAL_LIST = Object.keys( REAL_MAP );

// ---------------------------------------------------------------------------
// The PHP match rule, modelled exactly.

/** Characters PHP's trim() removes by default. */
const PHP_TRIM = ' \t\n\r\0\x0B';

/** PHP trim() with the default character list. */
function phpTrim( s ) {
	let a = 0;
	let b = s.length;
	while ( a < b && PHP_TRIM.includes( s[ a ] ) ) {
		a++;
	}
	while ( b > a && PHP_TRIM.includes( s[ b - 1 ] ) ) {
		b--;
	}
	return s.slice( a, b );
}

/** PHP 8 strtolower(): ASCII A-Z only, whatever the locale. */
function asciiLower( s ) {
	return s.replace( /[A-Z]/g, ( c ) => c.toLowerCase() );
}

/** The comparison form used on both sides of the topic match. */
function norm( s ) {
	return asciiLower( phpTrim( s ) );
}

/**
 * Topic keys as rx_safe_post_slug() reads them: a list uses its values, a
 * map its keys; only strings count here (the generators add null and [] as
 * noise, which PHP ignores as non-scalar), and empty keys are dropped.
 */
function topicKeySet( topics ) {
	const raw = Array.isArray( topics ) ? topics : Object.keys( topics );
	const keys = new Set();
	for ( const k of raw ) {
		if ( typeof k !== 'string' ) {
			continue;
		}
		const n = norm( k );
		if ( n !== '' ) {
			keys.add( n );
		}
	}
	return keys;
}

// ---------------------------------------------------------------------------
// Generators.

const PHP_WS = [ ' ', '\t', '\n', '\r', '\0', '\x0B' ];
// Whitespace PHP's trim() keeps: a slug padded with it is not a topic.
const OTHER_WS = [ '\u00a0', '\u2003', '\u3000', '\ufeff' ];

const phpWsArb = fc.array( fc.constantFrom( ...PHP_WS ), { maxLength: 3 } ).map( ( a ) => a.join( '' ) );

/** Random ASCII upper-casing of the letters in s. */
const caseFlipArb = ( s ) =>
	fc
		.array( fc.boolean(), { minLength: s.length, maxLength: s.length } )
		.map( ( flags ) => [ ...s ].map( ( c, i ) => ( flags[ i ] && /[a-z]/.test( c ) ? c.toUpperCase() : c ) ).join( '' ) );

/** s with random ASCII case and PHP-trim whitespace on either side. */
const variantArb = ( s ) => fc.tuple( phpWsArb, caseFlipArb( s ), phpWsArb ).map( ( [ a, b, c ] ) => a + b + c );

// Slug-like topic keys. They start with a letter, so a JSON object of them
// never decodes to a PHP list (which rx_safe_post_slug() would read by value).
const keyArb = fc.stringMatching( /^[a-z][a-z0-9]{0,6}(-[a-z0-9]{1,5}){0,2}$/ );

// Chains such as [ 'a', 'a-guide', 'a-guide-guide' ], so "-guide" must be
// appended more than once.
const chainArb = fc
	.tuple( keyArb, fc.integer( { min: 1, max: 4 } ) )
	.map( ( [ base, n ] ) => Array.from( { length: n }, ( _, i ) => base + '-guide'.repeat( i ) ) );

// Generated key lists: random keys, chains and optionally the real keys, in
// random order, some spelled with other case or padding.
const genKeysArb = fc
	.tuple( fc.boolean(), fc.array( keyArb, { maxLength: 5 } ), fc.array( chainArb, { maxLength: 2 } ) )
	.map( ( [ real, keys, chains ] ) => [ ...new Set( [ ...( real ? REAL_LIST : [] ), ...keys, ...chains.flat() ] ) ] )
	.chain( ( keys ) => fc.shuffledSubarray( keys, { minLength: keys.length, maxLength: keys.length } ) )
	.chain( ( keys ) =>
		fc.tuple(
			...keys.map( ( k ) =>
				fc.oneof( { weight: 4, arbitrary: fc.constant( k ) }, { weight: 1, arbitrary: variantArb( k ) } )
			)
		)
	);

// List form: keys plus entries rx_safe_post_slug() must ignore.
const genListArb = fc
	.tuple( genKeysArb, fc.array( fc.constantFrom( '', '   ', '\t', null, [] ), { maxLength: 2 } ) )
	.chain( ( [ keys, noise ] ) => {
		const all = [ ...keys, ...noise ];
		return fc.shuffledSubarray( all, { minLength: all.length, maxLength: all.length } );
	} );

// Map form: key => [ name, tone ], the RX_TOPICS shape.
const genMapArb = genKeysArb.map( ( keys ) => Object.fromEntries( keys.map( ( k ) => [ k, [ `Topic ${ k }`, 'teal' ] ] ) ) );

const topicsArb = fc.oneof(
	{ weight: 2, arbitrary: fc.constant( REAL_LIST ) },
	{ weight: 2, arbitrary: fc.constant( REAL_MAP ) },
	{ weight: 3, arbitrary: genListArb },
	{ weight: 3, arbitrary: genMapArb }
);

// Slugs that are not built from a topic: slugs WordPress could produce,
// arbitrary ASCII and Unicode strings, empty and whitespace-only.
const OTHER_SLUGS = [
	{ weight: 2, arbitrary: fc.stringMatching( /^[a-z0-9]+(-[a-z0-9]+)*$/ ) },
	{ weight: 1, arbitrary: fc.string( { maxLength: 12 } ) },
	{ weight: 1, arbitrary: fc.string( { unit: 'binary', maxLength: 12 } ) },
	{ weight: 1, arbitrary: fc.oneof( fc.constant( '' ), phpWsArb ) },
];

// Slugs for a topic set: mostly topic-related, plus OTHER_SLUGS.
const slugArb = ( keys ) => {
	const other = OTHER_SLUGS;
	if ( keys.length === 0 ) {
		return fc.oneof( ...other );
	}
	const keyPick = fc.constantFrom( ...keys );
	return fc.oneof(
		// Exact topic key.
		{ weight: 3, arbitrary: keyPick },
		// Case and whitespace variant of a topic key.
		{ weight: 3, arbitrary: keyPick.chain( variantArb ) },
		// "{topic}-guide" and "{topic}-guide-guide", sometimes as a variant.
		{
			weight: 2,
			arbitrary: fc
				.tuple( keyPick, fc.integer( { min: 1, max: 2 } ), fc.boolean() )
				.chain( ( [ k, n, vary ] ) => ( vary ? variantArb( k + '-guide'.repeat( n ) ) : fc.constant( k + '-guide'.repeat( n ) ) ) ),
		},
		// Topic key padded with whitespace PHP does not trim: not a topic.
		{
			weight: 1,
			arbitrary: fc
				.tuple( fc.constantFrom( ...OTHER_WS ), keyPick, fc.boolean() )
				.map( ( [ ws, k, before ] ) => ( before ? ws + k : k + ws ) ),
		},
		...other
	);
};

const caseArb = topicsArb.chain( ( topics ) =>
	slugArb( [ ...topicKeySet( topics ) ] ).map( ( slug ) => ( { slug, topics } ) )
);

// Hand-picked cases, run first.
const EXAMPLES = [
	[ { slug: 'expiry', topics: REAL_MAP } ],
	[ { slug: ' Expiry\t', topics: REAL_MAP } ],
	[ { slug: 'expiry-guide', topics: REAL_MAP } ],
	[ { slug: 'expiry', topics: REAL_LIST } ],
	[ { slug: 'cut-expiry-loss', topics: REAL_MAP } ],
	[ { slug: '\u00a0staff', topics: REAL_MAP } ],
	[ { slug: 'a', topics: [ 'a', 'a-guide', 'a-guide-guide' ] } ],
	[ { slug: 'A-GUIDE ', topics: { a: [ 'A', 'teal' ], 'a-guide': [ 'A guide', 'teal' ] } } ],
	[ { slug: '', topics: [ '', '  ', null ] } ],
];

// ---------------------------------------------------------------------------
// Batched PHP calls with a cache.

const cache = new Map();
let bridgeCalls = 0;

const cacheKey = ( slug, topics ) => JSON.stringify( [ slug, topics ] );

/** rx_safe_post_slug() for many [ slug, topics ] pairs, one PHP process for the misses. */
async function safeSlugs( pairs ) {
	const missing = [];
	for ( const [ slug, topics ] of pairs ) {
		const key = cacheKey( slug, topics );
		if ( ! cache.has( key ) && ! missing.some( ( m ) => m.key === key ) ) {
			missing.push( { key, slug, topics } );
		}
	}
	if ( missing.length > 0 ) {
		bridgeCalls++;
		const results = await callPhp( missing.map( ( m ) => ( { fn: 'rx_safe_post_slug', args: [ m.slug, m.topics ] } ) ) );
		results.forEach( ( res, i ) => cache.set( missing[ i ].key, res ) );
	}
	return pairs.map( ( [ slug, topics ] ) => {
		const res = cache.get( cacheKey( slug, topics ) );
		if ( ! res.ok ) {
			throw new Error( `rx_safe_post_slug(${ JSON.stringify( slug ) }, …) failed in PHP: ${ res.error }` );
		}
		return res.value;
	} );
}

// ---------------------------------------------------------------------------

test( 'Property 4: Post slugs never equal a topic', async ( t ) => {
	const seed = process.env.RX_FC_SEED ? Number( process.env.RX_FC_SEED ) : ( Date.now() ^ Math.floor( Math.random() * 0x7fffffff ) ) | 0;

	// Pre-compute f( s ) and f( f( s ) ) for every case in two PHP processes.
	const cases = [ ...EXAMPLES.map( ( [ c ] ) => c ), ...fc.sample( caseArb, { seed, numRuns: NUM_RUNS } ) ];
	const first = await safeSlugs( cases.map( ( c ) => [ c.slug, c.topics ] ) );
	await safeSlugs( cases.map( ( c, i ) => [ first[ i ], c.topics ] ) );
	const warmCalls = bridgeCalls;

	await fc.assert(
		fc.asyncProperty( caseArb, async ( { slug, topics } ) => {
			const keys = topicKeySet( topics );
			const isTopic = ( s ) => keys.has( norm( s ) );

			const [ out ] = await safeSlugs( [ [ slug, topics ] ] );
			assert.equal( typeof out, 'string', 'result is a string' );

			// 1. Never a topic key.
			assert.ok( ! isTopic( out ), `result ${ JSON.stringify( out ) } is a topic key` );

			if ( ! isTopic( slug ) ) {
				// 2. A non-topic slug comes back unchanged.
				assert.equal( out, slug, 'a non-topic slug must come back unchanged' );
			} else {
				// 3. Trimmed slug + one or more "-guide", and no more than needed.
				const base = phpTrim( slug );
				assert.ok( out.startsWith( base ), `result ${ JSON.stringify( out ) } does not start with the trimmed slug ${ JSON.stringify( base ) }` );
				const tail = out.slice( base.length );
				assert.match( tail, /^(?:-guide)+$/, `result ${ JSON.stringify( out ) } is not the trimmed slug + "-guide"…` );
				const n = tail.length / '-guide'.length;
				for ( let m = 1; m < n; m++ ) {
					const shorter = base + '-guide'.repeat( m );
					assert.ok( isTopic( shorter ), `${ JSON.stringify( shorter ) } is not a topic, so "-guide" was appended too often` );
				}
			}

			// 4. Idempotent.
			const [ again ] = await safeSlugs( [ [ out, topics ] ] );
			assert.equal( again, out, 'f( f( s ) ) must equal f( s )' );
		} ),
		{ seed, numRuns: NUM_RUNS, examples: EXAMPLES }
	);

	t.diagnostic( `seed ${ seed }; ${ cases.length } cases pre-computed in ${ warmCalls } PHP processes; ${ bridgeCalls - warmCalls } extra during the run` );
} );
