// Shape test for parity/pages.json (task 15.1), the page list read by
// parity/check.mjs. No network: URLs are only parsed, PHP_Site files are only
// checked for existence, and the import data is read through
// test/php/content-index.php.
//
// - 34 PHP ↔ WordPress pairs plus the WordPress-only 404.
// - Every PHP URL points at an existing PHP_Site file; every page and post of
//   the import data has its pair, with the WordPress URL its permalink.
// - expectTypes follows design §12: Yoast's WebPage, BreadcrumbList, WebSite
//   and Organization (+ Article on posts, CollectionPage on the posts page),
//   plus the theme's FAQPage, Blog and SoftwareApplication where they belong.
//
// Requirements: 13.1, 13.4

import { before, describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync } from 'node:fs';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { runPhp } from './lib/php.mjs';

const HERE = path.dirname( fileURLToPath( import.meta.url ) );
const PAGES_JSON = path.resolve( HERE, '../parity/pages.json' );
const PHP_SITE = path.resolve( HERE, '../../..' );

const PHP_BASE = 'http://localhost/pharmacy/';
const WP_BASE = 'http://192.168.0.25/pharmacy-wp/';

// Topic keys of BLOG_CATS (partials/data/blog.php) and RX_TOPICS (inc/blog.php), in order.
const TOPICS = [ 'expiry', 'purchase', 'switching', 'staff', 'buying' ];

// Page templates whose confirmed FAQ items become FAQPage schema (inc/seo.php).
const FAQ_TEMPLATES = [ 'templates/feature.php', 'templates/features-hub.php', 'templates/faq.php' ];

const YOAST_BASE = [ 'WebPage', 'BreadcrumbList', 'WebSite', 'Organization' ];

let cfg;
let pages;
let content;

/** Path of a URL below a base, without query string. */
function rel( url, base ) {
	assert.ok( url.startsWith( base ), `${ url } is not under ${ base }` );
	return url.slice( base.length ).split( '?' )[ 0 ];
}

/** Expected WordPress path of an import data item. */
function permalink( item ) {
	if ( 'home' === item.key ) {
		return '';
	}
	if ( 'post' === item.type ) {
		return `blog/${ item.slug }/`;
	}
	return ( item.parent ? `${ item.parent }/` : '' ) + `${ item.slug }/`;
}

/** Expected top-level JSON-LD types of an import data item's WordPress page. */
function expectedTypes( item ) {
	const types = [ ...YOAST_BASE ];
	if ( 'post' === item.type ) {
		types.push( 'Article' );
	}
	if ( 'blog' === item.key ) {
		types.push( 'CollectionPage', 'Blog' );
	}
	if ( 'pricing' === item.key ) {
		types.push( 'SoftwareApplication' );
	}
	if ( item.faq_confirmed > 0 && ( 'post' === item.type || FAQ_TEMPLATES.includes( item.template ) ) ) {
		types.push( 'FAQPage' );
	}
	return types.sort();
}

before( async () => {
	cfg = JSON.parse( await readFile( PAGES_JSON, 'utf8' ) );
	pages = cfg.pages;

	const { code, stdout, stderr } = await runPhp( 'content-index.php' );
	assert.equal( code, 0, `content-index.php failed: ${ stderr }` );
	content = JSON.parse( stdout );
} );

describe( 'parity/pages.json shape', () => {
	test( 'bases are the local PHP_Site and WP_Site', () => {
		assert.equal( cfg.phpBase, PHP_BASE );
		assert.equal( cfg.wpBase, WP_BASE );
	} );

	test( '34 pairs plus one WordPress-only 404', () => {
		assert.ok( Array.isArray( pages ) );
		assert.equal( pages.length, 35 );
		const pairs = pages.filter( ( p ) => null !== p.php );
		const wpOnly = pages.filter( ( p ) => null === p.php );
		assert.equal( pairs.length, 34 );
		assert.equal( wpOnly.length, 1 );
		assert.equal( wpOnly[ 0 ].status, 404 );
		assert.ok( pairs.every( ( p ) => undefined === p.status || 200 === p.status ) );
	} );

	test( 'every entry has name, php, wp, expectTypes and motion', () => {
		for ( const p of pages ) {
			assert.equal( typeof p.name, 'string', JSON.stringify( p ) );
			assert.match( p.name, /^[a-z0-9]+(-[a-z0-9]+)*$/ );
			assert.ok( null === p.php || 'string' === typeof p.php, p.name );
			assert.equal( typeof p.wp, 'string', p.name );
			assert.equal( typeof p.motion, 'boolean', p.name );
			assert.ok( Array.isArray( p.expectTypes ) && p.expectTypes.length > 0, p.name );
		}
	} );

	test( 'names and URLs are unique, on the local bases', () => {
		const names = pages.map( ( p ) => p.name );
		const php = pages.filter( ( p ) => p.php ).map( ( p ) => p.php );
		const wp = pages.map( ( p ) => p.wp );
		assert.equal( new Set( names ).size, names.length, 'duplicate name' );
		assert.equal( new Set( php ).size, php.length, 'duplicate php URL' );
		assert.equal( new Set( wp ).size, wp.length, 'duplicate wp URL' );
		php.forEach( ( u ) => rel( u, PHP_BASE ) );
		wp.forEach( ( u ) => rel( u, WP_BASE ) );
	} );

	test( 'every PHP URL points at an existing PHP_Site file', () => {
		for ( const p of pages.filter( ( x ) => x.php ) ) {
			const file = path.join( PHP_SITE, rel( p.php, PHP_BASE ) );
			assert.ok( existsSync( file ), `${ p.name }: ${ file } does not exist` );
		}
	} );

	test( 'only the home page has motion', () => {
		assert.deepEqual( pages.filter( ( p ) => p.motion ).map( ( p ) => p.name ), [ 'home' ] );
	} );
} );

describe( 'parity/pages.json covers the import data', () => {
	test( 'one pair per imported page and post, with the WordPress permalink', () => {
		assert.equal( content.length, 29, 'import data: 23 pages + 6 posts' );
		for ( const item of content ) {
			const pair = pages.find( ( p ) => p.php && rel( p.php, PHP_BASE ) === item.source && ! p.php.includes( '?' ) );
			assert.ok( pair, `no pair for ${ item.file } (${ item.source })` );
			assert.equal( rel( pair.wp, WP_BASE ), permalink( item ), `${ pair.name }: wp URL` );
		}
	} );

	test( 'five topic views: blog/index.php?topic=x ↔ /blog/x/', () => {
		const views = pages.filter( ( p ) => p.php && p.php.includes( '?topic=' ) );
		assert.deepEqual( views.map( ( p ) => new URL( p.php ).searchParams.get( 'topic' ) ), TOPICS );
		for ( const p of views ) {
			const topic = new URL( p.php ).searchParams.get( 'topic' );
			assert.equal( rel( p.php, PHP_BASE ), 'blog/index.php' );
			assert.equal( rel( p.wp, WP_BASE ), `blog/${ topic }/` );
		}
	} );

	test( 'the 404 URL matches no imported slug or topic', () => {
		const nf = pages.find( ( p ) => null === p.php );
		const slug = rel( nf.wp, WP_BASE ).replace( /\/$/, '' ).split( '/' ).pop();
		assert.ok( slug.length > 0 );
		assert.ok( ! content.some( ( item ) => item.slug === slug ), slug );
		assert.ok( ! TOPICS.includes( slug ), slug );
	} );
} );

describe( 'parity/pages.json expectTypes', () => {
	test( 'unique types; WebSite and Organization everywhere; never Person', () => {
		for ( const p of pages ) {
			assert.equal( new Set( p.expectTypes ).size, p.expectTypes.length, `${ p.name }: duplicate type` );
			assert.ok( p.expectTypes.includes( 'WebSite' ), p.name );
			assert.ok( p.expectTypes.includes( 'Organization' ), p.name );
			assert.ok( ! p.expectTypes.includes( 'Person' ), p.name );
		}
	} );

	test( 'imported pages and posts: Yoast types plus the theme pieces of design §12', () => {
		for ( const item of content ) {
			const pair = pages.find( ( p ) => p.php && rel( p.php, PHP_BASE ) === item.source && ! p.php.includes( '?' ) );
			assert.deepEqual( [ ...pair.expectTypes ].sort(), expectedTypes( item ), pair.name );
		}
	} );

	test( 'topic views have the blog hub types', () => {
		const hub = pages.find( ( p ) => 'blog' === p.name );
		for ( const p of pages.filter( ( x ) => x.php && x.php.includes( '?topic=' ) ) ) {
			assert.deepEqual( [ ...p.expectTypes ].sort(), [ ...hub.expectTypes ].sort(), p.name );
		}
	} );

	test( '404: WebSite and Organization only (Yoast skips WebPage and BreadcrumbList)', () => {
		const nf = pages.find( ( p ) => null === p.php );
		assert.deepEqual( [ ...nf.expectTypes ].sort(), [ 'Organization', 'WebSite' ] );
	} );
} );
