// Unit tests for the ported format helpers in themes/pharmacy/inc/core/format.php:
// rx_inr, rx_join, rx_barcode_svg and rx_legal_mark.
//
// rx_barcode_svg() is compared with the PHP site's own barcode_svg(), run from a
// temporary copy of partials/config.php. The PHP site is only read, never written.
//
// Validates: Requirements 2.6, 5.3

import { after, before, describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import { callPhp, runPhp } from './lib/php.mjs';

const HERE = path.dirname( fileURLToPath( import.meta.url ) );

// PHP site root (d:\xampp\htdocs\pharmacy\): test/ → tools/ → wordpress/ → root.
const PHP_SITE = path.resolve( HERE, '../../..' );
const CONFIG_PHP = path.join( PHP_SITE, 'partials', 'config.php' );

/** Call one rx_* function and return its value, failing the test on a PHP error. */
async function rx( fn, ...args ) {
	const [ result ] = await callPhp( [ { fn, args } ] );
	assert.ok( result.ok, `${ fn } failed: ${ result.error }` );
	return result.value;
}

/** Call one rx_* function once per argument list, in one PHP process. */
async function rxMany( fn, argLists ) {
	const results = await callPhp( argLists.map( ( args ) => ( { fn, args } ) ) );
	return results.map( ( result, i ) => {
		assert.ok( result.ok, `${ fn }(${ JSON.stringify( argLists[ i ] ) }) failed: ${ result.error }` );
		assert.equal( result.output, undefined, `${ fn } printed output` );
		return result.value;
	} );
}

describe( 'rx_inr()', () => {
	const examples = [
		[ 0, '₹0' ],
		[ 999, '₹999' ],
		[ 1000, '₹1,000' ],
		[ 100000, '₹1,00,000' ],
		[ 1092480, '₹10,92,480' ],
		[ 10000000, '₹1,00,00,000' ],
		[ 999.5, '₹1,000' ], // Rounded to a whole rupee before grouping.
	];

	test( 'formats rupees with lakh and crore grouping', async () => {
		const values = await rxMany( 'rx_inr', examples.map( ( [ n ] ) => [ n ] ) );
		examples.forEach( ( [ n, expected ], i ) => {
			assert.equal( values[ i ], expected, `rx_inr( ${ n } )` );
		} );
	} );
} );

describe( 'rx_join()', () => {
	test( 'joins lists as prose without a serial comma', async () => {
		const cases = [
			[ [ [] ], '' ],
			[ [ [ '1 year' ] ], '1 year' ],
			[ [ [ 'a', 'b' ] ], 'a and b' ],
			[ [ [ 'a', 'b', 'c' ] ], 'a, b and c' ],
			[ [ [ '1 year', '2 years', '3 years' ], 'or' ], '1 year, 2 years or 3 years' ],
		];
		const values = await rxMany( 'rx_join', cases.map( ( [ args ] ) => args ) );
		cases.forEach( ( [ args, expected ], i ) => {
			assert.equal( values[ i ], expected, `rx_join( ${ JSON.stringify( args ) } )` );
		} );
	} );
} );

describe( 'rx_legal_mark()', () => {
	const span = ( inner ) => `<span class="tbc" data-confirm>[${ inner }]</span>`;

	const examples = [
		[ 'one placeholder', 'Operated by [Company legal name].', `Operated by ${ span( 'Company legal name' ) }.` ],
		[ 'several placeholders', '<p>[First] and [Second]</p>', `<p>${ span( 'First' ) } and ${ span( 'Second' ) }</p>` ],
		[ 'two characters is the minimum', 'Clause [ab].', `Clause ${ span( 'ab' ) }.` ],
		[ 'one character is left alone', 'Item [x] and [1].', 'Item [x] and [1].' ],
		[ 'empty brackets are left alone', 'List: [].', 'List: [].' ],
		[ 'a < inside the brackets is left alone', '[a<b] text', '[a<b] text' ],
		[ 'a > inside the brackets is left alone', '[a>b] text', '[a>b] text' ],
		[ 'brackets around a tag are left alone', '[<strong>Name</strong>]', '[<strong>Name</strong>]' ],
		[ 'only the inner pair of nested brackets', '[[Outer]]', `[${ span( 'Outer' ) }]` ],
		[ 'placeholder inside a link', '<a href="/contact/">[Registered office]</a>', `<a href="/contact/">${ span( 'Registered office' ) }</a>` ],
		[ 'UTF-8 placeholder', 'पता: [पता] ₹', `पता: ${ span( 'पता' ) } ₹` ],
		[ 'no placeholder', '<p>Plain text with no brackets.</p>', '<p>Plain text with no brackets.</p>' ],
	];

	test( 'wraps [placeholders] of two or more characters in span.tbc[data-confirm]', async () => {
		const values = await rxMany( 'rx_legal_mark', examples.map( ( [ , html ] ) => [ html ] ) );
		examples.forEach( ( [ name, html, expected ], i ) => {
			assert.equal( values[ i ], expected, `${ name }: rx_legal_mark( ${ JSON.stringify( html ) } )` );
		} );
	} );

	test( 'leaves resolved shortcode output beside a placeholder untouched', async () => {
		const html = '<a href="tel:+919714874435">+91 97148 74435</a>, [Company legal name]';
		assert.equal(
			await rx( 'rx_legal_mark', html ),
			`<a href="tel:+919714874435">+91 97148 74435</a>, ${ span( 'Company legal name' ) }`,
		);
	} );
} );

describe( 'rx_barcode_svg() matches the PHP site\'s barcode_svg()', () => {
	let tmpDir;
	let oracle;

	// Argument lists passed unchanged to both functions. A list with only the
	// text checks that the defaults match too.
	const cases = [
		[ '' ],
		[ 'Rxtiva' ],
		[ 'INV-2026-00142' ],
		[ '₹' ],
		[ 'मेडिकल' ],
		[ 'Paracetamol 650 · Batch B2417 · Exp 03/27 · ₹30.50' ],
		[ 'x'.repeat( 400 ) ],
		[ 'Rxtiva', 24 ],
		[ 'Rxtiva', 40, 'barcode bc-sm' ],
		[ 'Lal Darwaja, Surat', 18, 'tag "quoted" & <odd>' ],
		[ 'मेडिकल स्टोर', 1, '' ],
	];

	before( async () => {
		tmpDir = await mkdtemp( path.join( os.tmpdir(), 'rx-format-' ) );

		// config.php has no includes and makes no request at include time. Its
		// PRICING_API constant is pointed at an unreachable local address anyway,
		// so nothing in the copy can reach a live host.
		const source = await readFile( CONFIG_PHP, 'utf8' );
		const copy = source.replace( /(const\s+PRICING_API\s*=\s*)'[^']*'/, "$1'http://127.0.0.1:9/'" );
		assert.doesNotMatch( copy, /PRICING_API\s*=\s*'https?:\/\/(?!127\.0\.0\.1)/, 'PRICING_API was not neutralised in the copy' );
		await writeFile( path.join( tmpDir, 'config.php' ), copy, 'utf8' );

		oracle = path.join( tmpDir, 'barcode-oracle.php' );
		await writeFile(
			oracle,
			[
				'<?php',
				"PHP_SAPI === 'cli' || exit;",
				"require __DIR__ . '/config.php';",
				'$cases = json_decode( stream_get_contents( STDIN ), true, 512, JSON_THROW_ON_ERROR );',
				'$out = array();',
				'foreach ( $cases as $args ) { $out[] = barcode_svg( ...$args ); }',
				'echo json_encode( $out, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );',
				'',
			].join( '\n' ),
			'utf8',
		);
	} );

	after( async () => {
		if ( tmpDir ) {
			await rm( tmpDir, { recursive: true, force: true } );
		}
	} );

	test( 'same markup for every sample text, height and class', async () => {
		const { code, stdout, stderr } = await runPhp( oracle, cases );
		assert.equal( code, 0, `PHP site oracle failed:\n${ stderr || stdout }` );
		assert.equal( stderr, '', 'PHP site oracle printed warnings' );
		const expected = JSON.parse( stdout );
		assert.equal( expected.length, cases.length );

		const actual = await rxMany( 'rx_barcode_svg', cases );
		cases.forEach( ( args, i ) => {
			assert.equal( actual[ i ], expected[ i ], `rx_barcode_svg( ${ JSON.stringify( args ) } )` );
		} );
	} );

	test( 'is decorative SVG with escaped class and deterministic bars', async () => {
		const [ a, b, other, odd ] = await rxMany( 'rx_barcode_svg', [
			[ 'Rxtiva' ],
			[ 'Rxtiva' ],
			[ 'Rxtiva!' ],
			[ 'x', 10, 'a"b' ],
		] );
		assert.match( a, /^<svg class="barcode" viewBox="0 0 \d+ 40" preserveAspectRatio="none" aria-hidden="true" focusable="false">(<rect x="\d+" width="[1-4]" height="40"\/>)+<\/svg>$/ );
		assert.equal( a, b, 'same text gives the same barcode' );
		assert.notEqual( a, other, 'different text gives a different barcode' );
		assert.ok( odd.startsWith( '<svg class="a&quot;b" viewBox="0 0 ' ), odd.slice( 0, 60 ) );
	} );
} );
