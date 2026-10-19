// Parity check for the WordPress migration (task 15.2; Requirements 10, 12, 13).
// Run from wordpress/tools:  node parity/check.mjs [options]   (npm run parity)

import { mkdir, readFile, stat, open, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { guardLocal, isLocalHost, isLocalUrl, runInteractions } from './interactions.mjs';
import { analysePage } from './lib/analyse.mjs';
import { pixelDiff, visit } from './lib/browse.mjs';
import { normUrl } from './lib/compare.mjs';
import { buildReport } from './lib/report.mjs';

const HERE = path.dirname( fileURLToPath( import.meta.url ) );
const TOOLS = path.dirname( HERE );

const HELP = `Parity check: every PHP page against its WordPress page (design "Parity check").

Usage: node parity/check.mjs [options]

  --only <text>          only pages whose name contains <text>
  --limit <n>            only the first n pages (after --only)
  --skip-interactions    do not call runInteractions()
  --pixel-threshold <p>  pixel difference (percent) above which a screenshot fails (default 5)
  --pages <file>         pages list (default parity/pages.json)
  --out <dir>            output folder (default tools/out)
  --wp-root <dir>        WordPress folder, read for debug.log (default D:\\xampp\\htdocs\\pharmacy-wp)
  --headed               show the browser
  --help                 this text

Only local hosts are ever requested (localhost, 127.0.0.1, 192.168.x.x). External
links are listed, never requested. Writes <out>/report.md, <out>/report.json and
screenshots under <out>/parity/. Exit code: 0 all pass, 1 a check failed, 2 bad arguments.
`;

function parseArgs( argv ) {
	const a = { only: null, limit: null, skipInteractions: false, pixelThreshold: 5, pages: path.join( HERE, 'pages.json' ), out: path.join( TOOLS, 'out' ), wpRoot: 'D:\\xampp\\htdocs\\pharmacy-wp', headed: false };
	const need = ( i ) => {
		if ( i + 1 >= argv.length ) {
			throw new Error( `${ argv[ i ] } needs a value` );
		}
		return argv[ i + 1 ];
	};
	for ( let i = 0; i < argv.length; i++ ) {
		switch ( argv[ i ] ) {
			case '--help': case '-h': console.log( HELP ); process.exit( 0 ); break;
			case '--only': a.only = need( i++ ); break;
			case '--limit': a.limit = Number( need( i++ ) ); if ( ! Number.isInteger( a.limit ) || a.limit < 1 ) { throw new Error( '--limit needs a positive integer' ); } break;
			case '--skip-interactions': a.skipInteractions = true; break;
			case '--pixel-threshold': a.pixelThreshold = Number( need( i++ ) ); if ( ! ( a.pixelThreshold >= 0 ) ) { throw new Error( '--pixel-threshold needs a number' ); } break;
			case '--pages': a.pages = path.resolve( need( i++ ) ); break;
			case '--out': a.out = path.resolve( need( i++ ) ); break;
			case '--wp-root': a.wpRoot = need( i++ ); break;
			case '--headed': a.headed = true; break;
			default: throw new Error( `unknown argument ${ argv[ i ] }` );
		}
	}
	return a;
}

let args;
try {
	args = parseArgs( process.argv.slice( 2 ) );
} catch ( e ) {
	console.error( `${ e.message }\n\n${ HELP }` );
	process.exit( 2 );
}

const slug = ( s ) => s.replace( /[^a-z0-9-]+/gi, '-' );
const base = ( u ) => ( { host: new URL( u ).host, path: new URL( u ).pathname } );

async function main() {
	const started = Date.now();
	const config = JSON.parse( await readFile( args.pages, 'utf8' ) );
	args.phpBase = config.phpBase;
	args.wpBase = config.wpBase;
	args.outRel = path.relative( path.dirname( TOOLS ), args.out ).replace( /\\/g, '/' );
	let pages = config.pages.filter( ( p ) => ! args.only || p.name.includes( args.only ) );
	if ( args.limit ) {
		pages = pages.slice( 0, args.limit );
	}
	if ( ! pages.length ) {
		console.error( 'No pages match.' );
		process.exit( 2 );
	}
	const offenders = pages.flatMap( ( p ) => [ p.php, p.wp ] ).filter( ( u ) => u && ! isLocalUrl( u ) );
	if ( offenders.length || ! isLocalUrl( config.phpBase ) || ! isLocalUrl( config.wpBase ) ) {
		console.error( `Local URLs only. Refusing: ${ offenders.join( ', ' ) || 'base URL' }` );
		process.exit( 2 );
	}
	const shotDir = path.join( args.out, 'parity' );
	await mkdir( shotDir, { recursive: true } );

	// debug.log (optional; only when WP_DEBUG_LOG is on).
	const debugLogPath = path.join( args.wpRoot, 'wp-content', 'debug.log' );
	let debugOn = false;
	try {
		const cfg = await readFile( path.join( args.wpRoot, 'wp-config.php' ), 'utf8' );
		debugOn = /define\(\s*['"]WP_DEBUG_LOG['"]\s*,\s*(true|['"][^'"]+['"])\s*\)/i.test( cfg ) && /define\(\s*['"]WP_DEBUG['"]\s*,\s*true\s*\)/i.test( cfg );
	} catch {
		// unreadable config: treat as off
	}
	const debugNote = debugOn
		? `debug.log (${ debugLogPath }): new lines written while each WordPress page loaded are listed under "notices".`
		: 'debug.log was not read because WP_DEBUG_LOG is off in wp-config.php (the PHP notice column only scans the page HTML for Fatal error / Warning / Notice / Deprecated text).';
	console.log( debugOn ? 'WP_DEBUG_LOG is on: reading new debug.log lines per page.' : 'Note: WP_DEBUG_LOG is off, skipping debug.log (task 15.3 switches it on).' );
	const logSize = async () => ( debugOn ? ( await stat( debugLogPath ).catch( () => ( { size: 0 } ) ) ).size : 0 );
	const newLogLines = async ( from ) => {
		if ( ! debugOn ) {
			return undefined;
		}
		const size = await logSize();
		if ( size <= from ) {
			return [];
		}
		const fh = await open( debugLogPath, 'r' );
		try {
			const buf = Buffer.alloc( Math.min( size - from, 200000 ) );
			await fh.read( buf, 0, buf.length, from );
			return buf.toString( 'utf8' ).split( /\r?\n/ ).filter( Boolean ).slice( 0, 6 ).map( ( l ) => l.slice( 0, 200 ) );
		} finally {
			await fh.close();
		}
	};

	const { chromium } = await import( 'playwright-core' );
	const browser = await chromium.launch( { channel: 'msedge', headless: ! args.headed } );
	const context = await browser.newContext( { viewport: { width: 1440, height: 900 }, serviceWorkers: 'block' } );
	const blocked = await guardLocal( context );

	const ctx = {
		pixelThreshold: args.pixelThreshold,
		phpBases: [ base( config.phpBase ) ],
		wpBases: [ base( config.wpBase ) ],
		isLocalHost,
		debugLog: debugOn,
	};
	const results = [];
	const pageIds = new Map(); // normalised WP url -> Set of ids
	const wpLinks = []; // { result, links, ids }

	try {
		for ( const [ i, entry ] of pages.entries() ) {
			const t0 = Date.now();
			const files = ( site ) => ( w ) => path.join( shotDir, `${ slug( entry.name ) }-${ site }-${ w }.png` );
			const logFrom = await logSize();
			// Sequential on purpose: two pages loading at once made full-page screenshots unreliable.
			const php = entry.php ? await visit( context, entry.php, { motion: entry.motion, outFile: files( 'php' ) } ) : null;
			const wp = await visit( context, entry.wp, { motion: entry.motion, outFile: files( 'wp' ) } );
			wp.debugLines = await newLogLines( logFrom );

			if ( php && wp && php.shots[ 1440 ] && wp.shots[ 1440 ] ) {
				php.pixels = {};
				wp.pixels = {};
				for ( const w of [ 1440, 375 ] ) {
					try {
						php.pixels[ w ] = await pixelDiff( php.shots[ w ], wp.shots[ w ], path.join( shotDir, `${ slug( entry.name ) }-diff-${ w }.png` ) );
					} catch ( e ) {
						try { // one retry: a file may still be flushing to disk
							await new Promise( ( r ) => setTimeout( r, 1500 ) );
							php.pixels[ w ] = await pixelDiff( php.shots[ w ], wp.shots[ w ], path.join( shotDir, `${ slug( entry.name ) }-diff-${ w }.png` ) );
						} catch ( e2 ) {
							php.pixels[ w ] = { percent: 100, heightA: 0, heightB: 0, width: w }; // counted as a failure
						}
					}
				}
				wp.pixels = php.pixels;
			}

			const res = analysePage( entry, php, wp, ctx );
			res.name = entry.name;
			res.php = entry.php;
			res.wp = entry.wp;

			// Interactions run last: they change page state.
			if ( args.skipInteractions ) {
				res.checks.interactions = { pass: null, summary: 'skipped' };
			} else {
				const opt = entry.status ? { status: entry.status } : {};
				const run = async ( url ) => {
					const page = await context.newPage();
					try {
						return await runInteractions( page, url, opt );
					} catch ( e ) {
						return [ { name: 'runInteractions', ok: false, detail: String( e.message ).split( '\n' )[ 0 ] } ];
					} finally {
						await page.close().catch( () => {} );
					}
				};
				const wpi = await run( entry.wp );
				const phpi = entry.php ? await run( entry.php ) : [];
				const bad = wpi.filter( ( r ) => ! r.ok );
				bad.forEach( ( r ) => res.diffs.push( { check: 'interactions', text: `WordPress: ${ r.name } failed: ${ r.detail }` } ) );
				phpi.filter( ( r ) => ! r.ok ).forEach( ( r ) => res.diffs.push( { check: 'interactions-note', text: `PHP site also fails: ${ r.name }: ${ r.detail }` } ) );
				res.checks.interactions = { pass: ! bad.length, summary: `${ wpi.length - bad.length }/${ wpi.length }` };
			}

			if ( wp.data ) {
				const key = normUrl( wp.finalUrl || entry.wp, ctx.wpBases );
				pageIds.set( ( key || '' ).replace( /#.*/, '' ), new Set( wp.data.ids ) );
				wpLinks.push( { res, links: wp.data.links, ids: new Set( wp.data.ids ) } );
			}
			results.push( res );
			const bad = Object.entries( res.checks ).filter( ( [ , c ] ) => c.pass === false ).map( ( [ k ] ) => k );
			console.log( `[${ i + 1 }/${ pages.length }] ${ entry.name }  ${ bad.length ? 'FAIL: ' + bad.join( ', ' ) : 'ok' }  (${ Math.round( ( Date.now() - t0 ) / 1000 ) } s)` );
		}

		// 7. Link crawl: local links on WordPress pages (external are only listed).
		const externalLinks = {};
		const cache = new Map();
		const targets = new Set();
		const wpBase = ctx.wpBases[ 0 ];
		for ( const { res, links } of wpLinks ) {
			res.localLinks = new Set();
			for ( const l of links ) {
				let u;
				try {
					u = new URL( l.href );
				} catch {
					continue;
				}
				if ( ! /^https?:$/.test( u.protocol ) ) {
					continue;
				}
				if ( ! isLocalUrl( l.href ) ) {
					( externalLinks[ u.host ] ||= new Set() ).add( l.href.replace( /#.*/, '' ) );
					continue;
				}
				if ( u.host !== wpBase.host || ! u.pathname.startsWith( wpBase.path ) ) {
					res.diffs.push( { check: 'links', text: `link leaves the WordPress site: ${ l.href } ("${ l.text }")` } );
					continue;
				}
				res.localLinks.add( l.href );
				if ( ! /\/wp-(admin|login)/.test( u.pathname ) && l.href.replace( /#.*/, '' ) !== res.wp ) {
					targets.add( l.href.replace( /#.*/, '' ) );
				}
			}
		}
		const queue = [ ...targets ];
		await Promise.all( Array.from( { length: 6 }, async () => {
			while ( queue.length ) {
				const url = queue.shift();
				if ( ! isLocalUrl( url ) ) {
					continue;
				}
				try {
					const r = await context.request.get( url, { timeout: 30000, maxRedirects: 5, failOnStatusCode: false } );
					cache.set( url, r.status() );
				} catch ( e ) {
					cache.set( url, `error ${ String( e.message ).split( '\n' )[ 0 ].slice( 0, 60 ) }` );
				}
			}
		} ) );
		for ( const { res, links, ids } of wpLinks ) {
			const seen = new Set();
			for ( const l of links ) {
				if ( seen.has( l.href ) ) {
					continue;
				}
				seen.add( l.href );
				const u = ( () => { try { return new URL( l.href ); } catch { return null; } } )();
				if ( ! u || ! res.localLinks.has( l.href ) ) {
					continue;
				}
				const plain = l.href.replace( /#.*/, '' );
				if ( cache.has( plain ) && ( typeof cache.get( plain ) !== 'number' || cache.get( plain ) >= 400 ) ) {
					res.diffs.push( { check: 'links', text: `${ l.href } returns ${ cache.get( plain ) }` } );
				}
				const frag = decodeURIComponent( u.hash.replace( /^#/, '' ) );
				if ( frag && frag !== 'top' ) {
					const key = ( normUrl( plain, ctx.wpBases ) || '' );
					const targetIds = l.raw.startsWith( '#' ) || plain === res.wp.replace( /#.*/, '' ) ? ids : pageIds.get( key );
					if ( targetIds && ! targetIds.has( frag ) ) {
						res.diffs.push( { check: 'links', text: `anchor #${ frag } not found on ${ plain }` } );
					}
				}
			}
			const bad = res.diffs.filter( ( d ) => d.check === 'links' ).length;
			res.checks.links = { pass: ! bad, summary: `${ res.localLinks.size } local${ bad ? `, ${ bad } bad` : '' }` };
		}

		// Report
		const notes = [
			'URLs are compared by site-relative key: the base path is removed, ".php" and "index" are dropped, trailing slashes are removed, so `features/billing.php` and `features/billing/` are equal.',
			'The PHP blog topic views (`blog/?topic=expiry`) are mapped to the WordPress `/blog/expiry/` URLs before the link sets are compared (intentional difference).',
			'The WordPress-only 404 page has no PHP counterpart: it is checked for status 404, console errors, meta, schema, Healthray, overflow and network only.',
			`Pages with motion are loaded with ?tour-at=15; every page uses reduced motion and disabled animations for screenshots and text. Pixel diffs pad the shorter page with white; a difference above ${ args.pixelThreshold }% fails (differences below it are recorded in the table only).`,
			'Visible text is document.body.innerText split into lines (whitespace collapsed) and compared as a multiset; order is covered by the section-id check. Robots compares only index/noindex and follow/nofollow (Yoast adds max-snippet and similar). og:image, article:tag and twitter:label/data may repeat.',
			'Assets: a stylesheet or script loaded only by the PHP page is a difference; one loaded only by WordPress (block library, Yoast, jQuery) is listed under Notes.',
			'Local requests blocked because they left the local machine are recorded as network failures. Console messages "Failed to load resource" are reported through the failed-request list instead.',
		];
		const md = buildReport( { results, args, started, notes, externalLinks, debugNote } );
		await writeFile( path.join( args.out, 'report.md' ), md );
		await writeFile( path.join( args.out, 'report.json' ), JSON.stringify( results.map( ( r ) => ( { name: r.name, checks: r.checks, diffs: r.diffs } ) ), null, 2 ) );
		if ( blocked.length ) {
			console.log( `Blocked ${ blocked.length } non-local request(s), e.g. ${ blocked[ 0 ] }` );
		}
		console.log( `\nReport: ${ path.join( args.out, 'report.md' ) }` );
		const failed = results.filter( ( r ) => Object.values( r.checks ).some( ( c ) => c.pass === false ) );
		console.log( failed.length ? `${ failed.length } of ${ results.length } page(s) have failing checks.` : 'All checks passed.' );
		process.exitCode = failed.length ? 1 : 0;
	} finally {
		await browser.close();
	}
}

main().catch( ( e ) => {
	console.error( e );
	process.exit( 2 );
} );
