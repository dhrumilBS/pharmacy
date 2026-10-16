// Feature: wordpress-migration, Property 1: Pricing helpers match the PHP site
//
// **Validates: Requirements 8.1, 8.4, 6.8**
//
// For any valid pricing document, the theme's helpers (themes/pharmacy/inc/core/
// pricing.php and rx_inr from format.php, called through bridge.php) return
// exactly what the PHP site's own helpers return for the same document:
//
//   theme                            PHP site                       called with
//   rx_pricing_valid( $d )           pricing_valid( $d )            $d and broken copies of $d
//   rx_pricing_plans( $d )           pricing_plans()
//   rx_pricing_plan( $d, id )        pricing_plan( id )             every plan id, plus 0 and 999
//   rx_plan_price( $d, id )          plan_price( id )               "
//   rx_plan_bill_limits( $d, id )    plan_bill_limits( id )         "
//   rx_pricing_terms_text( $d )      pricing_terms_text()
//   rx_pricing_split( $d )           pricing_split()
//   rx_pricing_cost_text( $d )       pricing_cost_text()
//   rx_pricing_compare_text( $d )    pricing_compare_text()
//   rx_feature_plan( $d, u )         feature_plan( u )              every listed unique_name, plus an unknown one
//   rx_feature_plan_label( $d, u )   feature_plan_label( u )        "
//   rx_plan_users_text( $d )         plan_users_text()
//   rx_plan_credits_text( $d )       plan_credits_text()
//   rx_term_total( price, term )     term_total( price, term )      every plan price (cast to int) × every term
//   rx_pricing_meta_bits( $d )       the $bits loop of pricing.php
//   rx_inr( n )                      inr( n )                       every plan price, plus random numbers
//
// How the PHP site runs (it is only read, never loaded in place or written):
// before() copies partials/config.php (PRICING_API → http://127.0.0.1:9/),
// partials/pricing-data.php (unchanged: its cache path is __DIR__-relative) and
// term_total() plus the $bits loop cut verbatim out of pricing.php into a temp
// folder. test/php/oracle.php writes each document into the copy's cache file
// with a fresh mtime, checks that pricing_data() reports _source 'cache' (so
// the API is never asked), and runs the helpers. One PHP process per document,
// because pricing_data() and feature_plan() memoise in static variables. Four
// copies let four oracle processes run at once.
//
// Order of plans: JSON.parse sorts integer-like object keys, so the key order
// of pricing_plans() is not visible here. The order is checked through the
// list results instead (split, users/credits text, meta bits, feature_plan).
//
// Speed: fc.sample() with the same seed yields the same values fc.assert()
// tests, so every case runs up front (oracle processes in parallel, bridge
// calls batched) and the property reads the results from a cache. Only
// shrinking after a failure starts new PHP processes.
//
// Replay a failure with FC_SEED=<seed from the report>.

import { after, before, test } from 'node:test';
import assert from 'node:assert/strict';
import { mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { isDeepStrictEqual } from 'node:util';
import fc from 'fast-check';

import { callPhp, runPhp } from './lib/php.mjs';

const NUM_RUNS = 100;
const WORKERS = 4; // Parallel oracle processes, one copy folder each.
const BRIDGE_CHUNK = 10; // Cases per bridge process.

const ENV_SEED = Number.parseInt( process.env.FC_SEED ?? '', 10 );
const SEED = Number.isSafeInteger( ENV_SEED ) ? ENV_SEED : ( Date.now() ^ ( Math.random() * 0x7fffffff ) ) | 0;

const HERE = path.dirname( fileURLToPath( import.meta.url ) );
// PHP site root (d:\xampp\htdocs\pharmacy\): test/ → tools/ → wordpress/ → root.
const PHP_SITE = path.resolve( HERE, '../../..' );
const SNAPSHOT = path.resolve( HERE, '../../themes/pharmacy/data/pricing.json' );

const OFFLINE_API = 'http://127.0.0.1:9/'; // Discard port on this PC: nothing listens.
const UNKNOWN_ID = 950; // Never a generated plan id (1–60) or feature id (1–200).
const UNKNOWN_FEATURE = '__not_listed__'; // Longer than any generated unique_name.

let tmpDir;
let workerDirs = [];

// ---------------------------------------------------------------------------
// PHP site copies
// ---------------------------------------------------------------------------

/**
 * term_total() and the meta-description loop of pricing.php, copied verbatim
 * into a file the oracle can load (pricing.php itself renders a page).
 */
function pricingPageFile( source ) {
	const one = ( re, what ) => {
		const found = [ ...source.matchAll( re ) ];
		assert.equal( found.length, 1, `pricing.php: expected exactly one ${ what }, found ${ found.length }` );
		return found[ 0 ][ 0 ];
	};
	// The loop runs on $plans = pricing_plans(), as in pricing.php.
	one( /^\$plans\s*=\s*pricing_plans\(\);/gm, '"$plans = pricing_plans();" line' );
	const termTotal = one( /^function term_total\(int \$price, array \$term\): string \{[\s\S]*?^\}$/gm, 'term_total() function' );
	const metaLoop = one( /^\$bits = \[\];\s*^foreach \(\$plans as \$p\) \{[\s\S]*?^\}$/gm, 'meta-description $bits loop' );
	for ( const piece of [ termTotal, metaLoop ] ) {
		assert.doesNotMatch( piece, /<\?|\?>/, 'extracted pricing.php code contains a PHP tag' );
	}
	return [
		'<?php',
		'// Generated by prop-pricing-parity.test.mjs from the PHP site\'s pricing.php (read only).',
		'// term_total() and the $bits loop are copied verbatim.',
		'',
		termTotal,
		'',
		'function pricing_page_meta_bits(): array {',
		'$plans = pricing_plans();',
		metaLoop,
		'return $bits;',
		'}',
		'',
	].join( '\n' );
}

before( async () => {
	const [ config, pricingData, pricingPage ] = await Promise.all(
		[ 'partials/config.php', 'partials/pricing-data.php', 'pricing.php' ].map( ( file ) => readFile( path.join( PHP_SITE, file ), 'utf8' ) ),
	);

	// config.php has no includes and makes no request at include time; its API
	// is pointed at a local port where nothing listens anyway.
	const configCopy = config.replace( /(const\s+PRICING_API\s*=\s*)'[^']*'/, `$1'${ OFFLINE_API }'` );
	assert.match( configCopy, /const\s+PRICING_API\s*=\s*'http:\/\/127\.0\.0\.1:9\/'/, 'PRICING_API was not replaced in the copy' );
	assert.doesNotMatch( configCopy, /PRICING_API\s*=\s*'https?:\/\/(?!127\.0\.0\.1)/, 'PRICING_API was not neutralised in the copy' );

	// pricing-data.php is copied unchanged. These lines make the copy load the
	// copied config.php and keep its cache (and missing snapshot) in the copy.
	assert.match( pricingData, /^require_once __DIR__ \. '\/config\.php';$/m );
	assert.match( pricingData, /^const PRICING_CACHE\s*=\s*__DIR__ \. '\/data\/cache\/[^']+';$/m );
	assert.match( pricingData, /^const PRICING_SNAPSHOT\s*=\s*__DIR__ \. '\/data\/pricing\.json';$/m );

	const pageCopy = pricingPageFile( pricingPage );

	tmpDir = await mkdtemp( path.join( os.tmpdir(), 'rx-pricing-parity-' ) );
	workerDirs = await Promise.all(
		Array.from( { length: WORKERS }, async ( _, w ) => {
			const dir = path.join( tmpDir, `w${ w }` );
			await mkdir( dir );
			await Promise.all( [
				writeFile( path.join( dir, 'config.php' ), configCopy, 'utf8' ),
				writeFile( path.join( dir, 'pricing-data.php' ), pricingData, 'utf8' ),
				writeFile( path.join( dir, 'pricing-page.php' ), pageCopy, 'utf8' ),
			] );
			return dir;
		} ),
	);
} );

after( async () => {
	if ( tmpDir ) {
		await rm( tmpDir, { recursive: true, force: true } );
	}
} );

// ---------------------------------------------------------------------------
// Generators
// ---------------------------------------------------------------------------

// A value, or undefined (the key is then left out of the JSON sent to PHP).
const missingOr = ( arb, weight = 4 ) => fc.oneof( { weight: 1, arbitrary: fc.constant( undefined ) }, { weight, arbitrary: arb } );
const orNull = ( arb, weight = 4 ) => fc.oneof( { weight, arbitrary: arb }, { weight: 1, arbitrary: fc.constant( null ) } );

const PLAN_NAMES = [ 'Starter', 'Basic', 'Advance', 'Enterprise', 'Pro', 'Clinic', 'Chain' ];
const planName = fc.oneof(
	{ weight: 5, arbitrary: fc.constantFrom( ...PLAN_NAMES ) },
	{ weight: 1, arbitrary: fc.string( { unit: 'grapheme', maxLength: 12 } ) },
);

const price = fc.oneof(
	{ weight: 3, arbitrary: fc.constant( 0 ) },
	{ weight: 2, arbitrary: fc.constantFrom( 4999, 9999, 14999 ) },
	{ weight: 1, arbitrary: fc.integer( { min: 1, max: 999 } ) },
	{ weight: 2, arbitrary: fc.integer( { min: 1000, max: 99999 } ) },
	{ weight: 1, arbitrary: fc.integer( { min: 100000, max: 99999999 } ) }, // Lakh and crore sized.
	{ weight: 1, arbitrary: fc.constantFrom( 0.5, 499.5, 999.5, 1499.49, 2500.75 ) }, // inr() rounds these.
);

const isCustom = fc.oneof(
	{ weight: 4, arbitrary: fc.constant( false ) },
	{ weight: 2, arbitrary: fc.constant( true ) },
	{ weight: 1, arbitrary: fc.constantFrom( 0, 1 ) },
);

const credit = missingOr( orNull( fc.integer( { min: 0, max: 5000 } ) ) );
const messageService = fc.oneof(
	{ weight: 1, arbitrary: fc.constantFrom( null, [] ) },
	{ weight: 5, arbitrary: fc.record( { ocr: credit, sms: credit, email: credit, whatsapp: credit } ) },
);

const billCount = missingOr( orNull( fc.integer( { min: 0, max: 500 } ) ) );
const billLimit = fc.oneof(
	{ weight: 2, arbitrary: fc.constantFrom( null, [] ) }, // The API sends [] or null for unlimited.
	{ weight: 3, arbitrary: fc.record( { INE: billCount, PUR: billCount } ) },
);

// A plan without its id; ids are assigned below so they stay mostly unique.
// is_custom is always present: pricing.php reads it without a fallback.
const planBody = fc.record( {
	name: planName,
	price,
	is_popular: fc.boolean(),
	is_custom: isCustom,
	sort_order: missingOr( orNull( fc.integer( { min: 0, max: 6 } ), 5 ) ), // Small range: ties are common.
	user_limit: missingOr( orNull( fc.integer( { min: 0, max: 50 } ), 3 ) ),
	message_service: missingOr( messageService ),
	bill_limit: missingOr( billLimit ),
} );

const term = fc.record( {
	id: fc.integer( { min: 1, max: 9 } ),
	label: fc.constantFrom( '1 Year', '2 Years', '3 Years', '5 Years' ),
	years: fc.oneof( { weight: 3, arbitrary: fc.constantFrom( 1, 3, 5 ) }, { weight: 2, arbitrary: fc.integer( { min: 1, max: 10 } ) } ),
	discount_percent: missingOr(
		fc.oneof(
			{ weight: 3, arbitrary: fc.constant( 0 ) },
			{ weight: 2, arbitrary: fc.integer( { min: 1, max: 60 } ) },
			{ weight: 1, arbitrary: fc.constantFrom( 12.5, 33.3, null ) },
		),
	),
} );

const FEATURE_NAMES = [
	'feat_sale_draft', 'feat_purchase_draft', 'feat_purchase_order', 'feat_role_management',
	'feat_2fa', 'feat_ip_whitelisting', 'feat_tally_export', 'feat_bill_limit',
];
const featureName = fc.oneof(
	{ weight: 4, arbitrary: fc.constantFrom( ...FEATURE_NAMES ) },
	{ weight: 1, arbitrary: fc.string( { unit: 'grapheme-ascii', minLength: 1, maxLength: 10 } ) },
);
const feature = fc.record( {
	id: fc.integer( { min: 1, max: 200 } ), // Repeats now and then, as a messy API might send.
	unique_name: missingOr( featureName, 12 ),
	group_id: fc.integer( { min: 1, max: 4 } ),
	name: fc.constantFrom( 'Draft', 'Bill Limit', '2FA', 'Purchase Order' ),
	sort_order: fc.integer( { min: 1, max: 9 } ),
} );

// A status refers to a plan and a feature by position (-1: an id the document doesn't list).
const statusRef = fc.record( {
	plan: fc.oneof( { weight: 6, arbitrary: fc.nat( 5 ) }, { weight: 1, arbitrary: fc.constant( -1 ) } ),
	feature: fc.oneof( { weight: 6, arbitrary: fc.nat( 9 ) }, { weight: 1, arbitrary: fc.constant( -1 ) } ),
} );

const GROUPS = [ { id: 1, unique_name: 'sales_return', name: 'Sales / Return', sort_order: 1 } ];

/** A valid pricing document shaped like themes/pharmacy/data/pricing.json. */
const pricingDoc = fc
	.record( {
		plans: fc.array( planBody, { minLength: 1, maxLength: 6 } ),
		planIds: fc.uniqueArray( fc.integer( { min: 1, max: 60 } ), { minLength: 6, maxLength: 6 } ),
		duplicateId: fc.integer( { min: 0, max: 7 } ), // 0: the last plan reuses the first plan's id.
		terms: fc.array( term, { minLength: 1, maxLength: 4 } ),
		features: fc.array( feature, { maxLength: 10 } ),
		statuses: fc.array( statusRef, { maxLength: 30 } ),
		groups: fc.constantFrom( [], GROUPS ),
	} )
	.map( ( g ) => {
		const plans = g.plans.map( ( plan, i ) => ( { id: g.planIds[ i ], ...plan } ) );
		if ( g.duplicateId === 0 && plans.length > 1 ) {
			plans[ plans.length - 1 ].id = plans[ 0 ].id;
		}
		const statuses = g.statuses.map( ( s ) => ( {
			plan_template_id: s.plan >= 0 ? plans[ s.plan % plans.length ].id : UNKNOWN_ID,
			pricing_feature_id: s.feature >= 0 && g.features.length ? g.features[ s.feature % g.features.length ].id : UNKNOWN_ID,
			status: 'included',
			display_value: null,
			credit_amount: null,
		} ) );
		return { plans, terms: g.terms, groups: g.groups, features: g.features, statuses };
	} );

const without = ( obj, key ) => {
	const copy = { ...obj };
	delete copy[ key ];
	return copy;
};

// Broken copies of a document, for rx_pricing_valid() only.
const BROKEN = {
	'a string': () => 'pricing',
	'null': () => null,
	'an empty list': () => [],
	'no plans key': ( d ) => without( d, 'plans' ),
	'no terms key': ( d ) => without( d, 'terms' ),
	'no groups key': ( d ) => without( d, 'groups' ),
	'no features key': ( d ) => without( d, 'features' ),
	'no statuses key': ( d ) => without( d, 'statuses' ),
	'statuses null': ( d ) => ( { ...d, statuses: null } ),
	'groups a string': ( d ) => ( { ...d, groups: 'x' } ),
	'no plans': ( d ) => ( { ...d, plans: [] } ),
	'no terms': ( d ) => ( { ...d, terms: [] } ),
	'a plan without a price': ( d ) => ( { ...d, plans: d.plans.map( ( p, i ) => ( i === 0 ? without( p, 'price' ) : p ) ) } ),
	'a plan without an id': ( d ) => ( { ...d, plans: d.plans.map( ( p, i ) => ( i === d.plans.length - 1 ? without( p, 'id' ) : p ) ) } ),
	'a plan with a null name': ( d ) => ( { ...d, plans: d.plans.map( ( p, i ) => ( i === 0 ? { ...p, name: null } : p ) ) } ),
};

const inrNumber = fc.oneof(
	fc.constantFrom( 0, 999, 1000, 999.5, 100000, 1092480, 10000000 ),
	fc.integer( { min: -100000, max: 2147483647 } ),
	fc.double( { min: -1e6, max: 1e13, noNaN: true } ),
);

/** One test case: a document, broken copies to validate and extra numbers for rx_inr(). */
const pricingCase = fc.record( {
	doc: pricingDoc,
	broken: fc.subarray( Object.keys( BROKEN ), { maxLength: 3 } ),
	numbers: fc.array( inrNumber, { maxLength: 4 } ),
} );

// ---------------------------------------------------------------------------
// Calls and runs
// ---------------------------------------------------------------------------

// What PHP receives: callPhp() and runPhp() send JSON, so keys holding
// undefined vanish and -0 becomes 0.
const asSent = ( value ) => JSON.parse( JSON.stringify( value ) );

/** Matching { label, site, theme } calls for one case. */
function callsFor( c ) {
	const d = asSent( c.doc );
	const calls = [];
	const add = ( label, siteFn, siteArgs, themeFn, themeArgs ) => {
		calls.push( { label, site: { fn: siteFn, args: siteArgs }, theme: { fn: themeFn, args: themeArgs } } );
	};
	// Helpers that read the document: pricing_data() on the PHP site, $d in the theme.
	const withDoc = ( siteFn, themeFn, ...args ) => {
		add( `${ themeFn }( $d${ args.map( ( a ) => ', ' + JSON.stringify( a ) ).join( '' ) } )`, siteFn, args, themeFn, [ d, ...args ] );
	};

	add( 'rx_pricing_valid( $d )', 'pricing_valid', [ d ], 'rx_pricing_valid', [ d ] );
	for ( const name of c.broken ) {
		const broken = asSent( BROKEN[ name ]( d ) );
		add( `rx_pricing_valid( ${ name } )`, 'pricing_valid', [ broken ], 'rx_pricing_valid', [ broken ] );
	}

	withDoc( 'pricing_plans', 'rx_pricing_plans' );
	for ( const id of [ ...new Set( d.plans.map( ( p ) => p.id ) ), 0, 999 ] ) {
		withDoc( 'pricing_plan', 'rx_pricing_plan', id );
		withDoc( 'plan_price', 'rx_plan_price', id );
		withDoc( 'plan_bill_limits', 'rx_plan_bill_limits', id );
	}
	withDoc( 'pricing_terms_text', 'rx_pricing_terms_text' );
	withDoc( 'pricing_split', 'rx_pricing_split' );
	withDoc( 'pricing_cost_text', 'rx_pricing_cost_text' );
	withDoc( 'pricing_compare_text', 'rx_pricing_compare_text' );
	withDoc( 'plan_users_text', 'rx_plan_users_text' );
	withDoc( 'plan_credits_text', 'rx_plan_credits_text' );
	withDoc( 'pricing_page_meta_bits', 'rx_pricing_meta_bits' );

	const uniques = new Set( d.features.map( ( f ) => f.unique_name ).filter( ( u ) => typeof u === 'string' ) );
	for ( const unique of [ ...uniques, UNKNOWN_FEATURE ] ) {
		withDoc( 'feature_plan', 'rx_feature_plan', unique );
		withDoc( 'feature_plan_label', 'rx_feature_plan_label', unique );
	}

	// pricing.php calls term_total( (int) $p['price'], $term ).
	for ( const plan of d.plans ) {
		for ( const t of d.terms ) {
			const yearly = Math.trunc( plan.price );
			add( `rx_term_total( ${ yearly }, ${ JSON.stringify( t ) } )`, 'term_total', [ yearly, t ], 'rx_term_total', [ yearly, t ] );
		}
	}
	for ( const n of new Set( [ ...d.plans.map( ( p ) => p.price ), ...asSent( c.numbers ) ] ) ) {
		add( `rx_inr( ${ JSON.stringify( n ) } )`, 'inr', [ n ], 'rx_inr', [ n ] );
	}
	return calls;
}

const norm = ( p ) => path.normalize( p ).toLowerCase().replace( /[\\/]+$/, '' ) + path.sep;

/** PHP site results for one case, from oracle.php in the given copy folder. */
async function siteResults( c, calls, dir ) {
	const { code, stdout, stderr } = await runPhp( 'oracle.php', { doc: asSent( c.doc ), calls: calls.map( ( call ) => call.site ) }, { args: [ dir ] } );
	assert.equal( code, 0, `oracle.php exited with code ${ code }:\n${ stderr || stdout }` );
	assert.equal( stderr, '', 'oracle.php printed to STDERR' );
	const out = JSON.parse( stdout );
	// The PHP site read its cache, the API it would have called is local, and
	// the cache it wrote is in the temp copy.
	assert.equal( out.source, 'cache', 'pricing_data() did not read the cache' );
	assert.equal( out.api, OFFLINE_API );
	assert.ok( norm( out.cache ).startsWith( norm( dir ) ), `cache file outside the copy: ${ out.cache }` );
	assert.equal( out.results.length, calls.length );
	return out.results;
}

/** Theme results for several cases, in one bridge process. */
async function themeResults( callLists ) {
	const results = await callPhp( callLists.flatMap( ( calls ) => calls.map( ( call ) => call.theme ) ) );
	let at = 0;
	return callLists.map( ( calls ) => results.slice( at, ( at += calls.length ) ) );
}

/** Run worker( item, workerIndex ) over items with at most `size` running at once. */
async function pool( items, size, worker ) {
	const out = new Array( items.length );
	let next = 0;
	await Promise.all(
		Array.from( { length: Math.min( size, items.length ) }, async ( _, w ) => {
			while ( next < items.length ) {
				const i = next++;
				out[ i ] = await worker( items[ i ], w );
			}
		} ),
	);
	return out;
}

const chunks = ( items, size ) => Array.from( { length: Math.ceil( items.length / size ) }, ( _, i ) => items.slice( i * size, ( i + 1 ) * size ) );

const cache = new Map();
const keyOf = ( c ) => JSON.stringify( c );

/** Run many cases: oracle processes in parallel, bridge calls in batches. */
async function prefetch( cases ) {
	const planned = cases.map( ( c ) => ( { c, calls: callsFor( c ) } ) );
	const [ site, theme ] = await Promise.all( [
		pool( planned, WORKERS, ( p, w ) => siteResults( p.c, p.calls, workerDirs[ w ] ) ),
		pool( chunks( planned, BRIDGE_CHUNK ), 2, ( group ) => themeResults( group.map( ( p ) => p.calls ) ) ).then( ( r ) => r.flat() ),
	] );
	planned.forEach( ( p, i ) => cache.set( keyOf( p.c ), { calls: p.calls, site: site[ i ], theme: theme[ i ] } ) );
}

async function resultsFor( c ) {
	const key = keyOf( c );
	if ( ! cache.has( key ) ) {
		const calls = callsFor( c );
		const [ site, [ theme ] ] = await Promise.all( [ siteResults( c, calls, workerDirs[ 0 ] ), themeResults( [ calls ] ) ] );
		cache.set( key, { calls, site, theme } );
	}
	return cache.get( key );
}

/** Every call whose theme result differs from the PHP site's. */
function differences( { calls, site, theme } ) {
	const out = [];
	calls.forEach( ( call, i ) => {
		const s = site[ i ];
		const t = theme[ i ];
		if ( ! s.ok ) {
			out.push( `${ call.label }: PHP site ${ call.site.fn }() failed: ${ s.error }` );
		} else if ( ! t.ok ) {
			out.push( `${ call.label } failed: ${ t.error }` );
		} else if ( s.output !== undefined || t.output !== undefined ) {
			out.push( `${ call.label }: printed output` );
		} else if ( ! isDeepStrictEqual( t.value, s.value ) ) {
			out.push( `${ call.label }:\n      theme    ${ JSON.stringify( t.value ) }\n      PHP site ${ JSON.stringify( s.value ) }` );
		}
	} );
	return out;
}

function assertSame( run ) {
	const diffs = differences( run );
	if ( diffs.length ) {
		assert.fail( `${ diffs.length } of ${ run.calls.length } results differ from the PHP site:\n  ${ diffs.slice( 0, 10 ).join( '\n  ' ) }` );
	}
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

test( 'Property 1: Pricing helpers match the PHP site', async () => {
	const samples = fc.sample( pricingCase, { seed: SEED, numRuns: NUM_RUNS } );
	await prefetch( samples );

	// The generator must reach the interesting outcomes, or the property says little.
	const labels = { every: 0, andUp: 0, last: 0, none: 0 };
	let allKinds = 0;
	for ( const c of samples ) {
		const { calls, site } = cache.get( keyOf( c ) );
		calls.forEach( ( call, i ) => {
			const value = site[ i ].value;
			if ( call.site.fn === 'feature_plan_label' && call.site.args[ 0 ] !== UNKNOWN_FEATURE ) {
				if ( value === null ) labels.none++;
				else if ( value === 'Every plan' ) labels.every++;
				else if ( value.endsWith( ' and up' ) ) labels.andUp++;
				else labels.last++;
			}
			if ( call.site.fn === 'pricing_split' && value.free.length && value.paid.length && value.custom.length ) {
				allKinds++;
			}
		} );
	}
	for ( const [ kind, count ] of Object.entries( labels ) ) {
		assert.ok( count > 0, `no generated feature gets a "${ kind }" plan label` );
	}
	assert.ok( allKinds > 0, 'no generated document has free, paid and custom plans together' );

	await fc.assert(
		fc.asyncProperty( pricingCase, async ( c ) => {
			assertSame( await resultsFor( c ) );
		} ),
		{ seed: SEED, numRuns: NUM_RUNS },
	);
} );

test( 'the committed pricing snapshot gives the same results as the PHP site', async () => {
	const doc = JSON.parse( ( await readFile( SNAPSHOT, 'utf8' ) ).replace( /^\uFEFF/, '' ) );
	assertSame( await resultsFor( { doc, broken: Object.keys( BROKEN ), numbers: [ 0, 999, 1000, 100000, 1092480 ] } ) );
} );
