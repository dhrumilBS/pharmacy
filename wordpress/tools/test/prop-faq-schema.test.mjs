// Feature: wordpress-migration, Property 3: FAQ schema holds only confirmed answers
//
// **Validates: Requirements 6.6, 12.4**
//
// For any list of FAQ items (arbitrary question text, answer HTML and
// confirmed flags), rx_faq_schema() from themes/pharmacy/inc/core/schema.php
// returns null when no item is left, and otherwise a FAQPage whose mainEntity
// lists the confirmed items in their original order, each with
// name = trim( q ) and text = trim( strip_tags( a_html ) ).
//
// Two refinements of the design wording, both as implemented in task 7.6:
// - "Confirmed" means exactly true, 1 or '1'. Every other value (false, 0,
//   '0', '', 'true', 2, null, a missing key, ...) leaves the item out.
// - A confirmed item whose question or answer text is empty after trimming
//   (and stripping, for the answer) is skipped too, so the schema never lists
//   a question the page does not show (parts/faq-list skips such items). The
//   oracle below applies the same skip; the design says "exactly the
//   confirmed items", this test checks "exactly the confirmed items with text".
//
// How the expected answer text is computed (soundness of the oracle):
// PHP's strip_tags() is a state machine with special cases ("< " stays text,
// NUL bytes are dropped, comments, "<?" and quoted '>' inside tags). Instead
// of reimplementing it, answers are generated from a grammar on which a JS
// strip is exact: text pieces never contain '<' or NUL, and every tag is one
// of TAGS (each starts with "<" + a letter or "/", has no '>' inside and no
// "<!" or "<?"). On that grammar strip_tags() removes each tag token and keeps
// every text byte, and so does /<[^>]*>/g. Text pieces still carry entities
// (&amp; stays &amp;: entities are not decoded), '>', quotes, control
// characters, Unicode spaces and any code point. Questions are not stripped,
// so they use unrestricted strings, '<' and NUL included.
//
// Speed: fc.sample() with the same seed yields the same values fc.assert()
// tests, so every case is sent to PHP in one batched callPhp() up front and
// the property reads the results from a cache. Only shrinking after a failure
// starts extra PHP processes (one per new candidate).

import { test } from 'node:test';
import assert from 'node:assert/strict';
import fc from 'fast-check';
import { callPhp } from './lib/php.mjs';

const NUM_RUNS = 200;

// Replay a failure with FC_SEED=<seed from the report>.
const ENV_SEED = Number.parseInt( process.env.FC_SEED ?? '', 10 );
const SEED = Number.isSafeInteger( ENV_SEED ) ? ENV_SEED : ( Date.now() ^ ( Math.random() * 0x7fffffff ) ) | 0;

// ---------------------------------------------------------------------------
// Oracle
// ---------------------------------------------------------------------------

// PHP trim() with its default list " \t\n\r\0\x0B". Unlike String#trim() it
// keeps U+00A0, U+2003 and the other Unicode spaces.
const phpTrim = ( s ) => s.replace( /^[ \t\n\r\x00\x0B]+|[ \t\n\r\x00\x0B]+$/g, '' );

// rx_schema_text(): strings as they are, numbers cast, anything else ''.
// Generated numbers are integers or 1.5, which PHP and JS print the same way.
const asText = ( v ) => ( typeof v === 'string' ? v : typeof v === 'number' ? String( v ) : '' );

// strip_tags() on the answer grammar below (see the header comment).
const stripTags = ( s ) => s.replace( /<[^>]*>/g, '' );

// What PHP receives: callPhp() sends JSON, so keys holding undefined vanish
// (PHP reads them as missing) and JSON objects and lists both become arrays.
const asSent = ( items ) => JSON.parse( JSON.stringify( items ) );

/** Expected rx_faq_schema() result for the decoded items. */
function expectedSchema( items ) {
	const mainEntity = [];
	for ( const item of items ) {
		// Non-arrays are skipped. A JSON list is a PHP array without a
		// 'confirmed' key, so it is skipped as unconfirmed.
		if ( item === null || typeof item !== 'object' || Array.isArray( item ) ) {
			continue;
		}
		const c = item.confirmed;
		if ( c !== true && c !== 1 && c !== '1' ) {
			continue;
		}
		const name = phpTrim( asText( item.q ) );
		const text = phpTrim( stripTags( asText( item.a_html ) ) );
		if ( name === '' || text === '' ) {
			continue;
		}
		mainEntity.push( { '@type': 'Question', name, acceptedAnswer: { '@type': 'Answer', text } } );
	}
	return mainEntity.length ? { '@type': 'FAQPage', mainEntity } : null;
}

// ---------------------------------------------------------------------------
// Generators
// ---------------------------------------------------------------------------

const TAGS = [
	'<b>', '</b>', '<strong>', '</strong>', '<em>', '</em>',
	'<br>', '<br/>', '<br />', '<p>', '</p>', '<ul>', '</ul>', '<li>', '</li>',
	'<code>', '</code>', '<span class="hl">', '</span>',
	'<a href="x">', '<a href="/pricing/?plan=1&amp;term=2">', '<a href=\'/faq/#q1\' class="link">', '</a>',
];
const tag = fc.constantFrom( ...TAGS );

const oneUnit = ( unit ) => fc.string( { unit, minLength: 1, maxLength: 1 } );

// One piece of answer text: anything except '<' and NUL.
const textUnit = fc
	.oneof(
		{ weight: 5, arbitrary: oneUnit( 'grapheme-ascii' ) },
		{ weight: 2, arbitrary: oneUnit( 'binary' ) },
		{
			weight: 3,
			arbitrary: fc.constantFrom(
				' ', '  ', '\t', '\n', '\r\n', '\x0B', '\u00a0', '\u2003',
				'&amp;', '&lt;', '&quot;', '&#8377;', '>', '"', "'", '₹', 'é', '\u{1F48A}',
			),
		},
	)
	.filter( ( u ) => ! u.includes( '<' ) && ! u.includes( '\0' ) );
const text = fc.string( { unit: textUnit, maxLength: 12 } );

// Answer HTML: text and tags in any order (nested, unbalanced, tag-only,
// whitespace-only), plus a few fixed edge cases.
const answerHtml = fc.oneof(
	{
		weight: 6,
		arbitrary: fc
			.array( fc.oneof( { weight: 3, arbitrary: text }, { weight: 2, arbitrary: tag } ), { maxLength: 8 } )
			.map( ( parts ) => parts.join( '' ) ),
	},
	{
		weight: 1,
		arbitrary: fc.constantFrom( '', ' ', '\n\t ', '\u00a0', '<b></b>', '<br>', ' <p> </p> ', '<a href="x"></a>', '&amp;', ' <b>Yes</b>. ' ),
	},
);

// Questions: unrestricted strings (tags are kept, not stripped), padded
// strings and whitespace-only values.
const question = fc.oneof(
	{ weight: 3, arbitrary: fc.string( { unit: 'grapheme-ascii', maxLength: 30 } ) },
	{ weight: 2, arbitrary: fc.string( { unit: 'binary', maxLength: 20 } ) },
	{ weight: 2, arbitrary: answerHtml },
	{
		weight: 1,
		arbitrary: fc
			.tuple( fc.constantFrom( ' ', '\n', '\t', '\x0B', '\0', '\u00a0' ), fc.string( { maxLength: 15 } ) )
			.map( ( [ pad, s ] ) => pad + s + pad ),
	},
	{ weight: 1, arbitrary: fc.constantFrom( '', '   ', '\t\n', '\0', '\u00a0', '<b></b>', '0' ) },
);

// A field value: mostly a string, sometimes missing, null, a bool, a number
// or an array (rx_schema_text() turns the non-strings into '' or a number).
const field = ( strings ) =>
	fc.oneof(
		{ weight: 12, arbitrary: strings },
		{ weight: 1, arbitrary: fc.constantFrom( undefined, null, true, false, 1.5, [], [ 'x' ] ) },
		{ weight: 1, arbitrary: fc.integer( { min: -50, max: 500 } ) },
	);

// About half the items count as confirmed.
const COUNTS = [ true, 1, '1' ];
const DOES_NOT_COUNT = [ undefined, null, false, 0, '0', '', 'true', 'yes', 'on', ' 1', '1 ', '01', 2, -1, 1.5, [], [ 1 ], {} ];
const confirmed = fc.oneof( fc.constantFrom( ...COUNTS ), fc.constantFrom( ...DOES_NOT_COUNT ) );

const faqItem = fc.record( { q: field( question ), a_html: field( answerHtml ), confirmed } );

// Malformed rows the function must skip without failing.
const junkItem = fc.constantFrom( null, 'Is it free?', 42, true, [], [ '1', 'Q', 'A' ], {} );

const faqItems = fc.array( fc.oneof( { weight: 12, arbitrary: faqItem }, { weight: 1, arbitrary: junkItem } ), { maxLength: 10 } );

// ---------------------------------------------------------------------------
// PHP results, batched
// ---------------------------------------------------------------------------

const cache = new Map();
const keyOf = ( items ) => JSON.stringify( items );
const callFor = ( items ) => ( { fn: 'rx_faq_schema', args: [ items ] } );

async function prefetch( samples ) {
	const results = await callPhp( samples.map( callFor ) );
	samples.forEach( ( items, i ) => cache.set( keyOf( items ), results[ i ] ) );
}

async function phpSchema( items ) {
	const key = keyOf( items );
	if ( ! cache.has( key ) ) {
		const [ result ] = await callPhp( [ callFor( items ) ] );
		cache.set( key, result );
	}
	return cache.get( key );
}

// ---------------------------------------------------------------------------
// Property
// ---------------------------------------------------------------------------

test( 'Property 3: FAQ schema holds only confirmed answers', async () => {
	const samples = fc.sample( faqItems, { seed: SEED, numRuns: NUM_RUNS } );
	await prefetch( samples );

	// The generator must reach both outcomes, or the property says little.
	const outcomes = samples.map( ( items ) => expectedSchema( asSent( items ) ) );
	assert.ok( outcomes.some( ( o ) => o === null ), 'no generated case expects null' );
	assert.ok( outcomes.some( ( o ) => o !== null ), 'no generated case expects a FAQPage' );

	await fc.assert(
		fc.asyncProperty( faqItems, async ( items ) => {
			const result = await phpSchema( items );
			assert.equal( result.ok, true, `rx_faq_schema() failed: ${ result.error }` );
			assert.equal( result.output, undefined, 'rx_faq_schema() printed output' );
			assert.deepStrictEqual( result.value, expectedSchema( asSent( items ) ) );
		} ),
		{ seed: SEED, numRuns: NUM_RUNS },
	);
} );
