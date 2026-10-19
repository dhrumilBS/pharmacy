// Turns the PHP and WordPress visits of one page into check results.
// Every check is { pass: boolean|null, summary: string }; null = not applicable.
// Differences are pushed to diffs as { check, text }.

import { analyseJsonLd, duplicateMeta, multiDiff, normUrl, pct, robotsFlags, sameOrder } from './compare.mjs';
import { OVERFLOW_WIDTHS } from './browse.mjs';

const cap = ( list, n = 8 ) => list.slice( 0, n ).map( ( x ) => `"${ String( x ).slice( 0, 110 ) }"` ).join( '; ' ) + ( list.length > n ? ` … (+${ list.length - n } more)` : '' );

export function analysePage( entry, php, wp, ctx ) {
	const checks = {};
	const diffs = [];
	const add = ( check, text ) => diffs.push( { check, text } );
	const expectStatus = entry.status || 200;
	const hasPhp = !! php;

	// 1. Status and errors ----------------------------------------------------
	{
		const probs = [];
		for ( const [ label, v ] of [ [ 'PHP', php ], [ 'WP', wp ] ] ) {
			if ( ! v ) {
				continue;
			}
			if ( v.error ) {
				probs.push( `${ label }: ${ v.error }` );
			}
			if ( v.status !== expectStatus ) {
				probs.push( `${ label }: HTTP ${ v.status } (expected ${ expectStatus })` );
			}
			v.consoleErrors.forEach( ( e ) => probs.push( `${ label } console: ${ e }` ) );
			v.pageErrors.forEach( ( e ) => probs.push( `${ label } page error: ${ e }` ) );
			v.failedLocal.forEach( ( e ) => probs.push( `${ label } failed request: ${ e }` ) );
		}
		probs.forEach( ( p ) => add( 'load', p ) );
		checks.load = { pass: ! probs.length, summary: probs.length ? `${ probs.length } issue(s)` : `${ wp.status }` };
		const notices = [ ...( php && php.data ? php.data.phpErrors.map( ( e ) => 'PHP site: ' + e ) : [] ), ...( wp.data ? wp.data.phpErrors.map( ( e ) => 'WP site: ' + e ) : [] ), ...( wp.debugLines || [] ).map( ( l ) => 'debug.log: ' + l ) ];
		notices.forEach( ( n ) => add( 'notices', n ) );
		checks.notices = { pass: ! notices.length, summary: wp.debugLines === undefined && ! ctx.debugLog ? `${ notices.length } (log off)` : String( notices.length ) };
	}
	if ( ! wp.data ) {
		const skip = { pass: null, summary: 'no data' };
		Object.assign( checks, { pixels: skip, content: skip, overflow: skip, meta: skip, dupmeta: skip, schema: skip, healthray: skip, links: skip, network: skip } );
		return { checks, diffs };
	}
	const W = wp.data;
	const P = hasPhp && php.data ? php.data : null;

	// 2. Pixels ---------------------------------------------------------------
	if ( P && php.pixels && wp.pixels ) {
		const d = php.pixels[ 1440 ];
		const m = php.pixels[ 375 ];
		const bad = [ d, m ].some( ( x ) => x && x.percent > ctx.pixelThreshold );
		const hd = ( x, w ) => x.heightA !== x.heightB ? ` (page height PHP ${ x.heightA }px, WP ${ x.heightB }px at ${ w })` : '';
		if ( d && d.percent > ctx.pixelThreshold ) {
			add( 'pixels', `1440px: ${ d.percent.toFixed( 2 ) }% of pixels differ${ hd( d, 1440 ) }` );
		}
		if ( m && m.percent > ctx.pixelThreshold ) {
			add( 'pixels', `375px: ${ m.percent.toFixed( 2 ) }% of pixels differ${ hd( m, 375 ) }` );
		}
		checks.pixels = { pass: ! bad, summary: `${ d ? d.percent.toFixed( 1 ) : '-' } / ${ m ? m.percent.toFixed( 1 ) : '-' }` };
	} else {
		checks.pixels = { pass: null, summary: hasPhp ? 'n/a' : 'WP only' };
	}

	// 3. Content --------------------------------------------------------------
	if ( P ) {
		const n = diffs.length;
		const t = multiDiff( P.text, W.text );
		if ( t.onlyA.length ) {
			add( 'text', `${ t.onlyA.length } line(s) only on the PHP page: ${ cap( t.onlyA ) }` );
		}
		if ( t.onlyB.length ) {
			add( 'text', `${ t.onlyB.length } line(s) only on the WordPress page: ${ cap( t.onlyB ) }` );
		}
		const s = multiDiff( P.sections, W.sections );
		if ( s.onlyA.length || s.onlyB.length ) {
			add( 'sections', `section ids differ. Only PHP: ${ s.onlyA.join( ', ' ) || '-' }. Only WP: ${ s.onlyB.join( ', ' ) || '-' }` );
		} else if ( ! sameOrder( P.sections, W.sections ) ) {
			add( 'sections', `same section ids in a different order. PHP: ${ P.sections.join( ' > ' ) }. WP: ${ W.sections.join( ' > ' ) }` );
		}
		if ( P.landmarks.join() !== W.landmarks.join() ) {
			add( 'sections', `landmark order differs. PHP: ${ P.landmarks.join( ' ' ) }. WP: ${ W.landmarks.join( ' ' ) }` );
		}
		if ( P.confirms.length !== W.confirms.length ) {
			const c = multiDiff( P.confirms, W.confirms );
			add( 'data-confirm', `count PHP ${ P.confirms.length }, WP ${ W.confirms.length }. Only PHP: ${ cap( c.onlyA, 4 ) } Only WP: ${ cap( c.onlyB, 4 ) }` );
		}
		const im = multiDiff( P.imgs, W.imgs );
		if ( im.onlyA.length || im.onlyB.length ) {
			add( 'images', `img (src|width|height|loading) only PHP: ${ cap( im.onlyA ) }; only WP: ${ cap( im.onlyB ) }` );
		}
		for ( const [ kind, a, b ] of [ [ 'stylesheet', P.stylesheets, W.stylesheets ], [ 'script', P.scripts, W.scripts ] ] ) {
			const d = multiDiff( [ ...new Set( a ) ], [ ...new Set( b ) ] );
			if ( d.onlyA.length ) {
				add( 'assets', `${ kind }(s) loaded only by the PHP page: ${ d.onlyA.join( ', ' ) }` );
			}
			if ( d.onlyB.length ) {
				add( 'assets-note', `${ kind }(s) loaded only by the WordPress page: ${ d.onlyB.join( ', ' ) }` );
			}
		}
		// Link set (normalised)
		const norm = ( data, bases ) => [ ...new Set( data.links.map( ( l ) => normUrl( l.href, bases ) ).filter( Boolean ) ) ];
		const pl = norm( P, ctx.phpBases );
		const wl = norm( W, ctx.wpBases );
		const ld = multiDiff( pl, wl );
		if ( ld.onlyA.length || ld.onlyB.length ) {
			add( 'linkset', `local links only on PHP page: ${ ld.onlyA.join( ', ' ) || '-' }; only on WP page: ${ ld.onlyB.join( ', ' ) || '-' }` );
		}
		const fail = diffs.slice( n ).filter( ( d ) => ! d.check.endsWith( '-note' ) );
		checks.content = { pass: ! fail.length, summary: `${ t.onlyA.length + t.onlyB.length } text, ${ fail.length - ( t.onlyA.length ? 1 : 0 ) - ( t.onlyB.length ? 1 : 0 ) } other` };
	} else {
		checks.content = { pass: null, summary: 'WP only' };
	}

	// 4. Overflow -------------------------------------------------------------
	{
		const over = OVERFLOW_WIDTHS.filter( ( w ) => wp.overflow[ w ] > w );
		over.forEach( ( w ) => add( 'overflow', `WordPress scrollWidth ${ wp.overflow[ w ] } > ${ w }px` ) );
		if ( hasPhp ) {
			OVERFLOW_WIDTHS.filter( ( w ) => php.overflow[ w ] > w ).forEach( ( w ) => add( 'overflow-note', `PHP site also overflows: scrollWidth ${ php.overflow[ w ] } > ${ w }px` ) );
		}
		checks.overflow = { pass: ! over.length, summary: over.length ? over.join( ',' ) : 'ok' };
	}

	// 5. Title, description, robots, duplicate meta, JSON-LD -------------------
	{
		const n = diffs.length;
		if ( P ) {
			if ( P.title !== W.title ) {
				add( 'meta', `title differs. PHP: "${ P.title }" WP: "${ W.title }"` );
			}
			if ( P.description !== W.description ) {
				add( 'meta', `description differs. PHP: "${ P.description }" WP: "${ W.description }"` );
			}
			if ( robotsFlags( P.robots ) !== robotsFlags( W.robots ) ) {
				add( 'meta', `robots differs. PHP: "${ P.robots || '(none)' }" WP: "${ W.robots || '(none)' }"` );
			}
		} else if ( ! W.title || ( expectStatus < 400 && ! W.description ) ) {
			add( 'meta', `title or description missing (title "${ W.title }", description "${ W.description || '' }")` );
		}
		checks.meta = { pass: diffs.length === n, summary: diffs.length === n ? 'match' : `${ diffs.length - n } differ` };
		const dups = duplicateMeta( W.meta, W.canonical, W.titleCount );
		dups.forEach( ( d ) => add( 'dupmeta', `duplicate on WordPress page: ${ d }` ) );
		checks.dupmeta = { pass: ! dups.length, summary: dups.length ? String( dups.length ) : '0' };

		const j = analyseJsonLd( W.jsonld );
		const probs = [ ...j.errors ];
		const types = [ ...j.counts.keys() ];
		types.filter( ( t ) => j.counts.get( t ) > 1 ).forEach( ( t ) => probs.push( `@type ${ t } appears ${ j.counts.get( t ) } times` ) );
		const want = entry.expectTypes || [];
		const miss = want.filter( ( t ) => ! j.counts.has( t ) );
		const extra = types.filter( ( t ) => ! want.includes( t ) );
		if ( miss.length ) {
			probs.push( `missing @type: ${ miss.join( ', ' ) }` );
		}
		if ( extra.length ) {
			probs.push( `unexpected @type: ${ extra.join( ', ' ) }` );
		}
		probs.forEach( ( p ) => add( 'schema', p ) );
		checks.schema = { pass: ! probs.length, summary: probs.length ? `${ probs.length } issue(s)` : `${ types.length } types ok` };

		// 6. Healthray ----------------------------------------------------------
		const hits = [ ...W.healthray ];
		if ( /healthray/i.test( j.text.replace( /https?:\/\/[^\s"']+/gi, '' ) ) ) {
			hits.push( 'json-ld: contains the word' );
		}
		hits.forEach( ( h ) => add( 'healthray', `WordPress page: ${ h }` ) );
		checks.healthray = { pass: ! hits.length, summary: String( hits.length ) };
	}

	// 7. Links (filled in later by the crawl) and 8. network ------------------
	checks.links = { pass: null, summary: 'pending' };
	{
		const hosts = [ ...new Set( [ ...wp.hosts, ...( php ? php.hosts : [] ) ] ) ];
		const bad = hosts.filter( ( h ) => ! ctx.isLocalHost( h.replace( /:\d+$/, '' ) ) );
		bad.forEach( ( h ) => add( 'network', `request to non-local host ${ h }` ) );
		checks.network = { pass: ! bad.length, summary: bad.length ? bad.join( ',' ) : `${ hosts.length } host(s)` };
	}
	return { checks, diffs, pct };
}
