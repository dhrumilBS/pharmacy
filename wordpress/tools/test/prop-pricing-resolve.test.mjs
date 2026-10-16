// Feature: wordpress-migration, Property 2: Pricing sources are tried in order
//
// **Validates: Requirements 8.1, 8.3**
//
// For any combination of source outcomes (cache, live, stale and snapshot
// each return a valid document, return something invalid, throw, are not
// callable, or are missing), rx_pricing_resolve() from
// themes/pharmacy/inc/core/pricing.php:
//
//   1. returns [ document, name ] for the first valid source in the fixed
//      order cache → live → stale → snapshot, whatever the key order of
//      $sources, and the document is exactly what that source returned;
//      [ null, 'none' ] when no source is valid;
//   2. calls the callable sources in that order, once each and with no
//      arguments, up to and including the winner and never one after it (so
//      live is not called while the cache is valid). Keys other than the four
//      names are never called;
//   3. calls on_live once, with the winning document, only when live wins,
//      and on_stale likewise only when stale wins. No other hook key is ever
//      called. A hook that throws or is not callable doesn't change the result;
//   4. never throws, never raises a PHP warning or notice, and prints nothing.
//
// "Valid" follows the design: arrays plans, terms, groups, features and
// statuses, at least one plan and one term, and an id, name and price on
// every plan. The 'invalid' variants each break one of those rules (or are
// not an array at all), so a wrong rx_pricing_valid() verdict shows up here
// as a wrong winner.
//
// Closures can't pass through the JSON bridge, so test/php/resolve.php builds
// the sources and hooks from the description sent here and records every
// call they receive.
//
// Speed: as in prop-faq-schema, fc.sample() with the same seed yields the
// values fc.assert() tests, so every case (and the fixed examples) goes to
// PHP in one batched process up front and the property reads the results
// from a cache. Only shrinking after a failure starts extra PHP processes.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import fc from 'fast-check';
import { runPhp } from './lib/php.mjs';

const NUM_RUNS = 300;

// Replay a failure with FC_SEED=<seed from the report>.
const ENV_SEED = Number.parseInt( process.env.FC_SEED ?? '', 10 );
const SEED = Number.isSafeInteger( ENV_SEED ) ? ENV_SEED : ( Date.now() ^ ( Math.random() * 0x7fffffff ) ) | 0;

// ---------------------------------------------------------------------------
// Vocabulary shared with test/php/resolve.php
// ---------------------------------------------------------------------------

/** The fixed source order. */
const ORDER = [ 'cache', 'live', 'stale', 'snapshot' ];

/** The hook each source triggers when it wins. */
const HOOK_FOR = { live: 'on_live', stale: 'on_stale' };

// Keys that look like the real ones but aren't: never called.
const EXTRA_SOURCES = [ 'Cache', 'LIVE', ' stale', 'snapshot ', 'backup', 'none', '0' ];
const EXTRA_HOOKS = [ 'on_cache', 'on_snapshot', 'on_none', 'onLive', 'on_Stale', 'on_live ', 'live', 'stale' ];

const VALID_DOCS = [ 'mini', 'mini_two_plans', 'mini_extra_keys', 'mini_string_values', 'snapshot' ];
const INVALID_VALUES = [
	'null', 'void', 'false', 'true', 'zero', 'one', 'empty_string', 'string', 'json_string', 'empty_array', 'list', 'object',
	'no_plans', 'empty_plans', 'plans_string', 'no_terms', 'empty_terms', 'no_groups', 'groups_null', 'no_features',
	'features_string', 'no_statuses', 'statuses_int', 'plan_no_id', 'plan_no_name', 'plan_no_price', 'plan_null_price',
	'plan_not_array', 'second_plan_no_price', 'snapshot_no_terms', 'snapshot_plan_no_price',
];
const THROWS = [ 'exception', 'error', 'type_error', 'json', 'division', 'value_error' ];
const NON_CALLABLE = [ 'null', 'true', 'false', 'int', 'float', 'empty_string', 'string', 'key_name', 'empty_array', 'valid_doc', 'no_class', 'no_method' ];
const FORMS = [ 'closure', 'invokable', 'method' ];

// ---------------------------------------------------------------------------
// Oracle
// ---------------------------------------------------------------------------

const isCallable = ( spec ) => spec !== undefined && spec.kind !== 'noncallable';

/** Expected resolve.php result for one scenario. */
function expectedRun( scenario ) {
	const sources = new Map( scenario.sources );
	const hooks = scenario.omit_hooks ? new Map() : new Map( scenario.hooks );

	const calls = [];
	let winner = null;
	for ( const name of ORDER ) {
		const spec = sources.get( name );
		if ( ! isCallable( spec ) ) {
			continue;
		}
		calls.push( { kind: 'source', name, argc: 0 } );
		if ( spec.kind === 'valid' ) {
			winner = name;
			break;
		}
	}

	const hookName = winner === null ? undefined : HOOK_FOR[ winner ];
	if ( hookName !== undefined && isCallable( hooks.get( hookName ) ) ) {
		calls.push( { kind: 'hook', name: hookName, argc: 1, marker: winner, same: true } );
	}

	return {
		threw: null,
		shape_ok: true,
		source: winner ?? 'none',
		marker: winner,
		identical: winner !== null,
		calls,
		warnings: [],
		output: '',
	};
}

// ---------------------------------------------------------------------------
// Generators
// ---------------------------------------------------------------------------

const form = fc.constantFrom( ...FORMS );

const sourceSpec = fc.oneof(
	{ weight: 3, arbitrary: fc.record( { kind: fc.constant( 'valid' ), variant: fc.constantFrom( ...VALID_DOCS ), form } ) },
	{ weight: 4, arbitrary: fc.record( { kind: fc.constant( 'invalid' ), variant: fc.constantFrom( ...INVALID_VALUES ), form } ) },
	{ weight: 2, arbitrary: fc.record( { kind: fc.constant( 'throw' ), variant: fc.constantFrom( ...THROWS ), form } ) },
	{ weight: 2, arbitrary: fc.record( { kind: fc.constant( 'noncallable' ), variant: fc.constantFrom( ...NON_CALLABLE ) } ) },
);

const hookSpec = fc.oneof(
	{ weight: 3, arbitrary: fc.record( { kind: fc.constant( 'ok' ), form } ) },
	{ weight: 2, arbitrary: fc.record( { kind: fc.constant( 'throw' ), variant: fc.constantFrom( ...THROWS ), form } ) },
	{ weight: 1, arbitrary: fc.record( { kind: fc.constant( 'noncallable' ), variant: fc.constantFrom( ...NON_CALLABLE ) } ) },
);

// Present 4 times out of 5.
const maybe = ( arb ) => fc.option( arb, { nil: undefined, freq: 4 } );

// Up to three look-alike keys, each with its own spec.
const extras = ( keys, spec ) => fc.uniqueArray( fc.tuple( fc.constantFrom( ...keys ), spec ), { selector: ( [ key ] ) => key, maxLength: 3 } );

// One random rank per entry (at most 7 entries), ties kept in entry order.
const ranks = fc.array( fc.nat( { max: 99 } ), { minLength: 8, maxLength: 8 } );
const inRankOrder = ( entries, rank ) =>
	entries
		.map( ( entry, i ) => [ rank[ i ], i, entry ] )
		.sort( ( a, b ) => a[ 0 ] - b[ 0 ] || a[ 1 ] - b[ 1 ] )
		.map( ( [ , , entry ] ) => entry );

/** A scenario exactly as resolve.php reads it. */
const scenarioArb = fc
	.record( {
		fixed: fc.tuple( ...ORDER.map( () => maybe( sourceSpec ) ) ),
		extraSources: extras( EXTRA_SOURCES, sourceSpec ),
		sourceRank: ranks,
		onLive: maybe( hookSpec ),
		onStale: maybe( hookSpec ),
		extraHooks: extras( EXTRA_HOOKS, hookSpec ),
		hookRank: ranks,
		omitHooks: fc.oneof( { weight: 7, arbitrary: fc.constant( false ) }, { weight: 1, arbitrary: fc.constant( true ) } ),
	} )
	.map( ( g ) => ( {
		sources: inRankOrder(
			[ ...ORDER.flatMap( ( name, i ) => ( g.fixed[ i ] ? [ [ name, g.fixed[ i ] ] ] : [] ) ), ...g.extraSources ],
			g.sourceRank,
		),
		hooks: inRankOrder(
			[ ...( g.onLive ? [ [ 'on_live', g.onLive ] ] : [] ), ...( g.onStale ? [ [ 'on_stale', g.onStale ] ] : [] ), ...g.extraHooks ],
			g.hookRank,
		),
		omit_hooks: g.omitHooks,
	} ) );

// Fixed cases run first, before the generated ones.
const valid = ( variant = 'mini', f = 'closure' ) => ( { kind: 'valid', variant, form: f } );
const invalid = ( variant = 'null', f = 'closure' ) => ( { kind: 'invalid', variant, form: f } );
const thrower = ( variant = 'exception', f = 'closure' ) => ( { kind: 'throw', variant, form: f } );
const plain = ( variant ) => ( { kind: 'noncallable', variant } );
const ok = ( f = 'closure' ) => ( { kind: 'ok', form: f } );
const allHooks = [ [ 'on_live', ok() ], [ 'on_stale', ok() ] ];

const EXAMPLES = [
	// Nothing at all.
	{ sources: [], hooks: [], omit_hooks: false },
	{ sources: [], hooks: [], omit_hooks: true },
	// Everything valid, keys reversed: the cache wins and no hook runs.
	{ sources: [ ...ORDER ].reverse().map( ( n ) => [ n, valid() ] ), hooks: allHooks, omit_hooks: false },
	// Cache misses, live answers, on_live throws: live still wins.
	{ sources: [ [ 'cache', invalid( 'false' ) ], [ 'live', valid( 'snapshot' ) ], [ 'stale', valid() ] ], hooks: [ [ 'on_live', thrower( 'error' ) ] ], omit_hooks: false },
	// Cache throws, live is a document instead of a callable, stale answers.
	{
		sources: [ [ 'snapshot', valid() ], [ 'stale', valid( 'mini', 'method' ) ], [ 'live', plain( 'valid_doc' ) ], [ 'cache', thrower() ] ],
		hooks: allHooks,
		omit_hooks: false,
	},
	// Only the snapshot is valid; look-alike keys hold valid sources.
	{
		sources: [ [ 'Cache', valid() ], [ 'LIVE', valid() ], [ 'backup', valid() ], [ 'snapshot', valid( 'snapshot', 'invokable' ) ] ],
		hooks: [ ...allHooks, [ 'on_snapshot', ok() ], [ 'on_cache', ok() ] ],
		omit_hooks: false,
	},
	// Every source throws.
	{ sources: ORDER.map( ( n, i ) => [ n, thrower( THROWS[ i ] ) ] ), hooks: allHooks, omit_hooks: false },
	// Live wins with the hooks argument left out.
	{ sources: [ [ 'live', valid() ], [ 'cache', invalid( 'empty_plans' ) ] ], hooks: allHooks, omit_hooks: true },
];

// ---------------------------------------------------------------------------
// PHP results, batched
// ---------------------------------------------------------------------------

const cache = new Map();
const keyOf = ( scenario ) => JSON.stringify( scenario );

async function runBatch( scenarios ) {
	const { code, stdout, stderr } = await runPhp( 'resolve.php', JSON.stringify( scenarios ) );
	if ( code !== 0 ) {
		throw new Error( `resolve.php exited with code ${ code }.\n${ stderr || stdout }` );
	}
	let results;
	try {
		results = JSON.parse( stdout );
	} catch ( e ) {
		throw new Error( `resolve.php printed invalid JSON: ${ e.message }\n${ stdout.slice( 0, 500 ) }\n${ stderr }`, { cause: e } );
	}
	if ( ! Array.isArray( results ) || results.length !== scenarios.length ) {
		throw new Error( `resolve.php returned ${ Array.isArray( results ) ? results.length : typeof results } results for ${ scenarios.length } scenarios.` );
	}
	scenarios.forEach( ( s, i ) => cache.set( keyOf( s ), results[ i ] ) );
}

async function phpRun( scenario ) {
	const key = keyOf( scenario );
	if ( ! cache.has( key ) ) {
		await runBatch( [ scenario ] );
	}
	return cache.get( key );
}

// ---------------------------------------------------------------------------
// Property
// ---------------------------------------------------------------------------

/** Kinds of the fixed sources tried before the winner (or all, when none wins). */
function triedBefore( scenario ) {
	const sources = new Map( scenario.sources );
	const winner = expectedRun( scenario ).source;
	const until = winner === 'none' ? ORDER.length : ORDER.indexOf( winner );
	return ORDER.slice( 0, until ).map( ( n ) => sources.get( n )?.kind ?? 'missing' );
}

test( 'Property 2: Pricing sources are tried in order', async () => {
	const samples = fc.sample( scenarioArb, { seed: SEED, numRuns: NUM_RUNS } );
	await runBatch( [ ...EXAMPLES, ...samples ] );

	// The generator must reach every outcome, or the property says little.
	const winners = new Set( samples.map( ( s ) => expectedRun( s ).source ) );
	for ( const outcome of [ ...ORDER, 'none' ] ) {
		assert.ok( winners.has( outcome ), `no generated case has winner "${ outcome }"` );
	}
	const hookThrows = ( s, source ) =>
		! s.omit_hooks && expectedRun( s ).source === source && new Map( s.hooks ).get( HOOK_FOR[ source ] )?.kind === 'throw';
	assert.ok( samples.some( ( s ) => hookThrows( s, 'live' ) ), 'no generated case has live winning with a throwing on_live' );
	assert.ok( samples.some( ( s ) => hookThrows( s, 'stale' ) ), 'no generated case has stale winning with a throwing on_stale' );
	for ( const kind of [ 'invalid', 'throw', 'noncallable', 'missing' ] ) {
		assert.ok(
			samples.some( ( s ) => expectedRun( s ).source !== 'none' && triedBefore( s ).includes( kind ) ),
			`no generated case has a "${ kind }" source before the winner`,
		);
	}
	const at = ( s, name ) => s.sources.findIndex( ( [ n ] ) => n === name );
	assert.ok(
		samples.some( ( s ) => at( s, 'snapshot' ) >= 0 && at( s, 'snapshot' ) < at( s, 'cache' ) ),
		'no generated case lists snapshot before cache',
	);
	assert.ok( samples.some( ( s ) => s.sources.some( ( [ n, spec ] ) => EXTRA_SOURCES.includes( n ) && spec.kind === 'valid' ) ), 'no generated case has a valid look-alike source' );

	await fc.assert(
		fc.asyncProperty( scenarioArb, async ( scenario ) => {
			const actual = await phpRun( scenario );
			const expected = expectedRun( scenario );

			assert.equal( actual.threw, null, `rx_pricing_resolve() threw: ${ actual.threw }` );
			assert.deepStrictEqual( actual.warnings, [], 'rx_pricing_resolve() raised PHP warnings' );
			assert.equal( actual.output, '', 'rx_pricing_resolve() printed output' );
			assert.equal( actual.shape_ok, true, 'rx_pricing_resolve() did not return a [ document, source ] pair' );
			assert.deepStrictEqual( actual, expected );
		} ),
		{ seed: SEED, numRuns: NUM_RUNS, examples: EXAMPLES.map( ( s ) => [ s ] ) },
	);
} );
