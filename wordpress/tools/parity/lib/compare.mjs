// Pure comparison helpers for parity/check.mjs.

const BLOG_TOPICS = /^(expiry|purchase|switching|staff|buying)$/;

/**
 * Site-relative key for a local URL, so PHP and WordPress links compare equal:
 * base path removed, ".php" and "index" dropped, trailing slash removed and the
 * PHP "blog/?topic=x" view mapped to the WordPress "blog/x/" URL.
 * Returns null for URLs outside the given bases ({ host, path }).
 */
export function normUrl( href, bases ) {
	let u;
	try {
		u = new URL( href );
	} catch {
		return null;
	}
	if ( ! /^https?:$/.test( u.protocol ) ) {
		return null;
	}
	let p = u.pathname;
	let matched = false;
	for ( const b of bases ) {
		if ( u.host === b.host && p.startsWith( b.path ) ) {
			p = p.slice( b.path.length );
			matched = true;
			break;
		}
	}
	if ( ! matched ) {
		return null;
	}
	p = p.replace( /(^|\/)index\.php$/, '$1' ).replace( /\.php$/, '' ).replace( /\/+$/, '' );
	const topic = u.searchParams.get( 'topic' );
	if ( p === 'blog' && topic && BLOG_TOPICS.test( topic ) ) {
		p = 'blog/' + topic;
	}
	return '/' + p + ( u.hash && u.hash !== '#' ? u.hash : '' );
}

/** Multiset difference of two string arrays: { onlyA, onlyB }. */
export function multiDiff( a, b ) {
	const count = new Map();
	b.forEach( ( x ) => count.set( x, ( count.get( x ) || 0 ) + 1 ) );
	const onlyA = [];
	for ( const x of a ) {
		const n = count.get( x ) || 0;
		if ( n > 0 ) {
			count.set( x, n - 1 );
		} else {
			onlyA.push( x );
		}
	}
	const onlyB = [];
	for ( const [ x, n ] of count ) {
		for ( let i = 0; i < n; i++ ) {
			onlyB.push( x );
		}
	}
	return { onlyA, onlyB };
}

/** True when the ids both lists share appear in the same order. */
export function sameOrder( a, b ) {
	const setB = new Set( b );
	const setA = new Set( a );
	const ca = a.filter( ( x ) => setB.has( x ) );
	const cb = b.filter( ( x ) => setA.has( x ) );
	return ca.length === cb.length && ca.every( ( x, i ) => x === cb[ i ] );
}

/** index/noindex + follow/nofollow only (Yoast adds max-snippet etc.). */
export function robotsFlags( content ) {
	const t = String( content || '' ).toLowerCase().split( /[\s,]+/ );
	return [ t.includes( 'noindex' ) ? 'noindex' : 'index', t.includes( 'nofollow' ) ? 'nofollow' : 'follow' ].join( ',' );
}

/** Repeats that are legitimate (several images or tags). */
const REPEATABLE = /^(property:og:image(:.*)?|property:article:tag|property:og:locale:alternate|name:twitter:(label|data)\d+)$/;

export function duplicateMeta( meta, canonical, titleCount ) {
	const seen = new Map();
	meta.forEach( ( m ) => seen.set( m.key, ( seen.get( m.key ) || 0 ) + 1 ) );
	const dups = [ ...seen ].filter( ( [ k, n ] ) => n > 1 && ! REPEATABLE.test( k ) ).map( ( [ k, n ] ) => `${ k } x${ n }` );
	if ( canonical.length > 1 ) {
		dups.push( `canonical x${ canonical.length }` );
	}
	if ( titleCount > 1 ) {
		dups.push( `title x${ titleCount }` );
	}
	return dups;
}

/** Parse JSON-LD blocks: { errors, counts (top-level @type counts), text }. */
export function analyseJsonLd( blocks ) {
	const errors = [];
	const counts = new Map();
	for ( const [ i, raw ] of blocks.entries() ) {
		let data;
		try {
			data = JSON.parse( raw );
		} catch ( e ) {
			errors.push( `block ${ i + 1 } is not valid JSON (${ e.message.slice( 0, 60 ) })` );
			continue;
		}
		const nodes = Array.isArray( data ) ? data : Array.isArray( data[ '@graph' ] ) ? data[ '@graph' ] : [ data ];
		for ( const n of nodes ) {
			if ( ! n || typeof n !== 'object' ) {
				continue;
			}
			[].concat( n[ '@type' ] || [] ).forEach( ( t ) => counts.set( t, ( counts.get( t ) || 0 ) + 1 ) );
		}
	}
	return { errors, counts, text: blocks.join( '\n' ) };
}

export function pct( n, total ) {
	return total ? ( ( n / total ) * 100 ).toFixed( 2 ) : '0.00';
}
