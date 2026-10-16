// Feature: wordpress-migration, Property 5: The "On this page" list matches the article headings
//
// **Validates: Requirements 7.3**
//
// rx_blog_toc( $html ) (themes/pharmacy/inc/core/blog.php) gives every article
// <h2> without an id= an id and returns [ html_with_ids, toc ]. Articles are
// built from a small grammar, so the expected headings, labels and ids are
// known exactly on the JS side:
//
//   blocks:  <p>…</p>, <h3>…</h3> (both optionally with id="…"),
//            <h2>…</h2> with no attributes, a class or a data attribute,
//            <h2 … id="given">…</h2> in four attribute forms (skipped by rx_blog_toc)
//   inline:  text, <b>/<i>/<em>/<strong>/<code>, <a href>, <span id="…">, <br>
//   text:    random strings (punctuation, quotes, &, newlines, non-ASCII,
//            emoji, no "<" or ">"), a pool of common and colliding headings,
//            entities such as &amp;, and numbers
//
// All cases go to PHP in one bridge call. When a case fails, fast-check
// replays the same seed with one PHP call per case so it can shrink the
// article to a minimal counterexample. Set RX_PBT_SEED to replay a run.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import fc from 'fast-check';
import { callPhp } from './lib/php.mjs';

const NUM_RUNS = 200;
const ID_RE = /^[a-z0-9]+(-[a-z0-9]+)*$/;

// ---------------------------------------------------------------------------
// Generators
// ---------------------------------------------------------------------------

// Text characters. No "<" or ">", so text never forms a tag. Includes
// characters whose JS toLowerCase() would give ASCII (U+0130 İ, U+212A Kelvin
// sign), which PHP's ASCII-only strtolower() leaves alone.
const CHARS = [
	...'abzABZ019',
	' ', ' ', '-', '_', '.', ',', ':', ';', '!', '?', '&', '#', '"', "'", '(', ')', '/', '%',
	'\n', '\t', 'é', '\u0130', '\u212A', '₹', '’', '😀',
];

// Headings that repeat, slug to "section", or collide with the given ids below.
const COMMON_TEXT = [
	'', ' ', '0', '00', '12', '!!!', '---',
	'FAQ', 'faq', 'Faq ', 'Section', 'section 2', 'section-2', 'amp',
	'Stock & reorder', 'Stock &amp; reorder', 'Expiry, batch & FEFO', '  Spaces  ', '₹ 1,000',
];

const ENTITIES = [ '&amp;', '&nbsp;', '&#8217;', '&lt;' ];

const text = fc.oneof(
	{ weight: 4, arbitrary: fc.string( { unit: fc.constantFrom( ...CHARS ), maxLength: 12 } ) },
	{ weight: 3, arbitrary: fc.constantFrom( ...COMMON_TEXT ) },
	{ weight: 1, arbitrary: fc.constantFrom( ...ENTITIES ) },
	{ weight: 1, arbitrary: fc.nat( 999 ).map( String ) },
);

// Ids already in the article: slugs of the common headings (so generated ids
// must step around them), a case variant, and random slug-shaped values.
const givenId = fc.oneof(
	fc.constantFrom(
		'section', 'section-2', 'section-3', 'faq', 'faq-2', 'amp', '00', '12', 'Faq',
		'stock-reorder', 'stock-amp-reorder', 'expiry-batch-fefo', 'spaces',
	),
	fc.stringMatching( /^[a-z0-9]{1,5}(-[a-z0-9]{1,3})?$/ ),
);

/** One inline piece: its HTML, its strip_tags() text and the ids it carries. */
const piece = fc.oneof(
	{ weight: 5, arbitrary: text.map( ( t ) => ( { html: t, text: t, ids: [] } ) ) },
	{
		weight: 2,
		arbitrary: fc
			.tuple( fc.constantFrom( 'b', 'i', 'em', 'strong', 'code' ), text )
			.map( ( [ tag, t ] ) => ( { html: `<${ tag }>${ t }</${ tag }>`, text: t, ids: [] } ) ),
	},
	{ weight: 1, arbitrary: text.map( ( t ) => ( { html: `<a href="/blog/stock/">${ t }</a>`, text: t, ids: [] } ) ) },
	{
		weight: 1,
		arbitrary: fc
			.tuple( givenId, text )
			.map( ( [ id, t ] ) => ( { html: `<span id="${ id }">${ t }</span>`, text: t, ids: [ id ] } ) ),
	},
	{ weight: 1, arbitrary: fc.constant( { html: '<br>', text: '', ids: [] } ) },
);

const inner = fc.array( piece, { maxLength: 4 } ).map( ( ps ) => ( {
	html: ps.map( ( p ) => p.html ).join( '' ),
	text: ps.map( ( p ) => p.text ).join( '' ),
	ids: ps.flatMap( ( p ) => p.ids ),
} ) );

// <h2> attributes without id= (these headings get a TOC entry).
const H2_ATTRS = [ '', '', ' class="x"', ' class="b-h2 lead"', ' data-tone="teal"' ];

// <h2> attribute forms with an id (left unchanged, no TOC entry).
const H2_ID_FORMS = [ ' id="{id}"', ' class="x" id="{id}"', " id='{id}'", ' id="{id}" class="x"' ];

const plainH2 = fc.tuple( fc.constantFrom( ...H2_ATTRS ), inner ).map( ( [ attrs, c ] ) => ( {
	kind: 'h2',
	attrs,
	inner: c,
	ids: c.ids,
	html: `<h2${ attrs }>${ c.html }</h2>`,
} ) );

const givenH2 = fc.tuple( fc.constantFrom( ...H2_ID_FORMS ), givenId, inner ).map( ( [ form, id, c ] ) => {
	const attrs = form.replace( '{id}', id );
	return { kind: 'h2-id', attrs, inner: c, ids: [ id, ...c.ids ], html: `<h2${ attrs }>${ c.html }</h2>` };
} );

const otherBlock = fc
	.tuple(
		fc.constantFrom( 'p', 'p', 'h3' ),
		fc.oneof( { weight: 3, arbitrary: fc.constant( null ) }, { weight: 1, arbitrary: givenId } ),
		inner,
	)
	.map( ( [ tag, id, c ] ) => {
		const attrs = null === id ? '' : ` id="${ id }"`;
		return {
			kind: tag,
			attrs,
			inner: c,
			ids: null === id ? c.ids : [ id, ...c.ids ],
			html: `<${ tag }${ attrs }>${ c.html }</${ tag }>`,
		};
	} );

const block = fc.oneof(
	{ weight: 5, arbitrary: plainH2 },
	{ weight: 2, arbitrary: givenH2 },
	{ weight: 3, arbitrary: otherBlock },
);

const article = fc
	.record( {
		blocks: fc.array( block, { maxLength: 12 } ),
		sep: fc.constantFrom( '', '\n', '\n\n', ' ' ),
	} )
	.map( ( { blocks, sep } ) => ( { blocks, sep, html: blocks.map( ( b ) => b.html ).join( sep ) } ) );

// ---------------------------------------------------------------------------
// Expected values
// ---------------------------------------------------------------------------

/**
 * The slug rule of blog_end(): ASCII-only lowercase (PHP 8 strtolower), runs
 * of characters outside a-z0-9 become "-", "-" trimmed, "" or "0" → "section".
 */
function slugify( label ) {
	const lower = label.replace( /[A-Z]/g, ( c ) => c.toLowerCase() );
	const slug = lower.replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' );
	return '' === slug || '0' === slug ? 'section' : slug;
}

const show = ( v ) => JSON.stringify( v );

/** Check one rx_blog_toc() result against the article it was built from. */
function checkCase( a, res ) {
	assert.ok( res && res.ok, `rx_blog_toc failed: ${ res && res.error }` );
	assert.ok( Array.isArray( res.value ) && 2 === res.value.length, `expected [ html, toc ], got ${ show( res.value ) }` );

	const [ out, toc ] = res.value;
	assert.equal( typeof out, 'string', 'html is a string' );
	assert.ok( Array.isArray( toc ), 'toc is a list' );

	const eligible = a.blocks.filter( ( b ) => 'h2' === b.kind );
	const given = new Set( a.blocks.flatMap( ( b ) => b.ids ) );

	// (1) One entry per <h2> without id=.
	assert.equal( toc.length, eligible.length, 'one TOC entry per <h2> without id=' );

	// (2) Labels, in order, are the strip_tags() text of those headings (untrimmed, entities kept).
	assert.deepEqual(
		toc.map( ( e ) => e.label ),
		eligible.map( ( b ) => b.inner.text ),
		'TOC labels equal the heading texts in document order',
	);

	// (3) Ids are unique, slug-shaped and never repeat an id already in the article.
	const ids = toc.map( ( e ) => e.id );
	assert.equal( new Set( ids ).size, ids.length, `TOC ids are unique: ${ show( ids ) }` );
	for ( const id of ids ) {
		assert.match( id, ID_RE, `id ${ show( id ) } is slug-shaped` );
		assert.ok( ! given.has( id ), `id ${ show( id ) } does not repeat an existing id` );
	}

	// (6) Each id is the slug of its label, or that slug plus the first free "-N" (N >= 2).
	const used = new Set( given );
	toc.forEach( ( e, k ) => {
		const base = slugify( eligible[ k ].inner.text );
		const suffix = e.id.startsWith( base + '-' ) ? e.id.slice( base.length + 1 ) : null;
		assert.ok(
			e.id === base || ( null !== suffix && /^[0-9]+$/.test( suffix ) && Number( suffix ) >= 2 ),
			`id ${ show( e.id ) } is slug ${ show( base ) } with an optional -N suffix`,
		);
		let want = base;
		for ( let n = 2; used.has( want ); n++ ) {
			want = `${ base }-${ n }`;
		}
		used.add( want );
		assert.equal( e.id, want, `id for heading ${ k } (${ show( e.label ) }) uses the first free suffix` );
	} );

	// (4) In document order: each TOC heading appears as <h2 id="{id}"…>, and every
	//     <h2> that already had an id appears unchanged.
	let cursor = 0;
	let k = 0;
	for ( const b of a.blocks ) {
		let needle;
		if ( 'h2' === b.kind ) {
			needle = `<h2 id="${ toc[ k++ ].id }"${ b.attrs }>${ b.inner.html }</h2>`;
		} else if ( 'h2-id' === b.kind ) {
			needle = b.html;
		} else {
			continue;
		}
		const at = out.indexOf( needle, cursor );
		assert.ok( at >= 0, `output has ${ show( needle ) } after offset ${ cursor }` );
		cursor = at + needle.length;
	}

	// (5) Removing the inserted ` id="…"` attributes gives back the input exactly.
	let restored = '';
	let pos = 0;
	for ( const e of toc ) {
		const needle = `<h2 id="${ e.id }"`;
		const at = out.indexOf( needle, pos );
		assert.ok( at >= 0, `output has ${ show( needle ) } after offset ${ pos }` );
		restored += out.slice( pos, at + 3 ); // keep "<h2"
		pos = at + needle.length;
	}
	restored += out.slice( pos );
	assert.equal( restored, a.html, 'output without the inserted ids equals the input' );
}

const tocOf = ( a ) => ( { fn: 'rx_blog_toc', args: [ a.html ] } );

// ---------------------------------------------------------------------------
// Property
// ---------------------------------------------------------------------------

test( 'Property 5: The "On this page" list matches the article headings', async () => {
	const seed = process.env.RX_PBT_SEED ? Number( process.env.RX_PBT_SEED ) : Math.floor( Math.random() * 0x7fffffff );

	// Fast path: every case in one PHP process.
	const cases = fc.sample( article, { seed, numRuns: NUM_RUNS } );
	const results = await callPhp( cases.map( tocOf ) );

	let failure = null;
	for ( let i = 0; i < cases.length && null === failure; i++ ) {
		try {
			checkCase( cases[ i ], results[ i ] );
		} catch ( e ) {
			failure = { i, e };
		}
	}
	if ( null === failure ) {
		return;
	}

	// A case failed: replay the same seed one case per PHP call, so fast-check
	// reports a shrunk counterexample.
	await fc.assert(
		fc.asyncProperty( article, async ( a ) => {
			const [ res ] = await callPhp( [ tocOf( a ) ] );
			checkCase( a, res );
		} ),
		{ seed, numRuns: NUM_RUNS },
	);

	// The replay did not reproduce it: report the original case.
	assert.fail(
		`Seed ${ seed }, case ${ failure.i }: ${ failure.e.message }\nInput html: ${ show( cases[ failure.i ].html ) }`,
	);
} );
