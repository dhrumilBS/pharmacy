// Page-side extraction for parity/check.mjs. extractPage() runs inside the
// browser through page.evaluate(), so it must stay self-contained.

export function extractPage() {
	const norm = ( s ) => String( s ).replace( /[\s ]+/g, ' ' ).trim();
	const base = ( u ) => {
		try {
			return new URL( u, location.href ).pathname.split( '/' ).pop() || '(root)';
		} catch {
			return String( u );
		}
	};
	const meta = [ ...document.querySelectorAll( 'meta[name], meta[property]' ) ].map( ( m ) => ( {
		key: m.getAttribute( 'name' ) ? 'name:' + m.getAttribute( 'name' ) : 'property:' + m.getAttribute( 'property' ),
		content: norm( m.getAttribute( 'content' ) || '' ),
	} ) );
	const first = ( key ) => ( meta.find( ( m ) => m.key === key ) || {} ).content;
	const canon = [ ...document.querySelectorAll( 'link[rel="canonical"]' ) ].map( ( l ) => l.href );
	const jsonld = [ ...document.querySelectorAll( 'script[type="application/ld+json"]' ) ].map( ( s ) => s.textContent );

	const text = ( document.body.innerText || '' ).split( /\n+/ ).map( norm ).filter( Boolean );

	const sel = 'main[id], header[id], footer[id], nav[id], aside[id], article[id], section[id]';
	const sections = [ ...document.querySelectorAll( sel ) ].map( ( e ) => e.id ).filter( Boolean );
	const landmarks = [ ...document.querySelectorAll( 'header, nav, main, aside, footer, [role="main"]' ) ].map( ( e ) => e.tagName.toLowerCase() );

	const confirms = [ ...document.querySelectorAll( '[data-confirm]' ) ].map( ( e ) => norm( e.textContent ).slice( 0, 80 ) );

	const imgs = [ ...document.querySelectorAll( 'img' ) ].map( ( i ) =>
		[ base( i.getAttribute( 'src' ) || '' ), i.getAttribute( 'width' ) || '', i.getAttribute( 'height' ) || '', i.getAttribute( 'loading' ) || '' ].join( '|' ) );

	const stylesheets = [ ...document.querySelectorAll( 'link[rel~="stylesheet"][href]' ) ].map( ( l ) => base( l.getAttribute( 'href' ) ) );
	const scripts = [ ...document.querySelectorAll( 'script[src]' ) ].map( ( s ) => base( s.getAttribute( 'src' ) ) );

	const hits = [];
	const urlLike = /https?:\/\/[^\s"'<>)]+/gi;
	const scan = ( where, value ) => {
		const v = String( value ).replace( urlLike, '' );
		if ( /healthray/i.test( v ) ) {
			hits.push( where + ': ' + norm( v ).slice( 0, 100 ) );
		}
	};
	text.forEach( ( t ) => scan( 'text', t ) );
	scan( 'title', document.title );
	for ( const el of document.querySelectorAll( '*' ) ) {
		if ( [ 'SCRIPT', 'STYLE' ].includes( el.tagName ) ) {
			continue;
		}
		for ( const a of el.attributes ) {
			if ( [ 'href', 'src', 'srcset', 'action', 'data-src', 'data-href' ].includes( a.name ) ) {
				continue;
			}
			scan( el.tagName.toLowerCase() + '[' + a.name + ']', a.value );
		}
	}

	const links = [ ...document.querySelectorAll( 'a[href]' ) ].map( ( a ) => ( { href: a.href, raw: a.getAttribute( 'href' ), text: norm( a.textContent ).slice( 0, 40 ) } ) );
	const ids = [ ...document.querySelectorAll( '[id]' ) ].map( ( e ) => e.id );
	const doctext = document.documentElement.innerHTML;
	const phpErrors = ( doctext.match( /(?:<b>)?(?:Fatal error|Warning|Notice|Deprecated|Parse error)(?:<\/b>)?:\s[^<]{0,120}/g ) || [] ).slice( 0, 5 );

	return {
		title: document.title, titleCount: document.querySelectorAll( 'title' ).length,
		description: first( 'name:description' ), robots: first( 'name:robots' ),
		canonical: canon, meta, jsonld, text, sections, landmarks, confirms, imgs, stylesheets, scripts,
		healthray: hits, links, ids, phpErrors,
	};
}

/** Scroll to the bottom and back so lazy images and scroll-reveal blocks load. */
export async function settle( page ) {
	await page.evaluate( async () => {
		const h = () => document.documentElement.scrollHeight;
		for ( let y = 0; y < h(); y += 600 ) {
			window.scrollTo( 0, y );
			await new Promise( ( r ) => setTimeout( r, 40 ) );
		}
		window.scrollTo( 0, 0 );
		await Promise.all( [ ...document.images ].map( ( i ) => ( i.complete ? 1 : new Promise( ( r ) => {
			i.addEventListener( 'load', r );
			i.addEventListener( 'error', r );
			setTimeout( r, 4000 );
		} ) ) ) );
		if ( document.fonts && document.fonts.ready ) {
			await document.fonts.ready;
		}
	} );
	await page.waitForTimeout( 250 );
}
