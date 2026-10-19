// Interaction check for the parity run (task 11.1; Requirements 9.1, 9.2, 10.2).
//
//   runInteractions( page, url, options? )
//       Loads url in the given Playwright page, finds which interactions the
//       page has (by the selectors site.js, pricing.js, tour.js and blog.js
//       look for), runs each one and resolves to [{ name, ok, detail }].
//       A failing check never throws; it is reported with ok: false.
//       options.status: expected HTTP status (pages.json "status", e.g. 404);
//       without it any status below 400 counts as loaded.
//
//   guardLocal( pageOrContext )
//       Aborts every browser request to a host that is not local
//       (localhost, 127.0.0.1, ::1, 192.168.x.x). runInteractions() installs
//       it on its page; check.mjs can also install it on the context.
//
// The page is navigated (url, then url?tour-at=15 and the autoplay reloads on
// pages with the product tour) and its state is changed (forms submitted,
// menus toggled), so call this after any screenshots. The viewport and the
// reduced-motion setting are restored before it returns.
//
// CLI (manual runs only, not part of npm test):
//   node parity/interactions.mjs [--json] [--headed] <url> [<url> ...]
// Uses the installed Microsoft Edge (channel msedge). A URL listed in
// parity/pages.json with a "status" (the 404 page) is checked against it.
// Exit code 1 when a check fails, 2 on bad arguments.

import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DESKTOP = { width: 1440, height: 900 };
const TABLET = { width: 1024, height: 900 }; // below 1025px the feature tabs are plain tabs
const PHONE = { width: 375, height: 812 };
const T = 5000; // timeout for single actions (ms)
const TOUR_TOTAL = 43000; // TOTAL in tour.js (ms)

// Request types a submit may cause without sending anything (lazy images,
// fonts). Anything else during a submit counts as a sent request.
const INCIDENTAL = new Set( [ 'image', 'font', 'media' ] );

// ---------------------------------------------------------------------------
// Local-only guard
// ---------------------------------------------------------------------------

/** True for localhost, 127.0.0.1, ::1 and 192.168.x.x (full IPv4 match). */
export function isLocalHost( host ) {
	const h = String( host ).toLowerCase().replace( /^\[|\]$/g, '' );
	if ( h === 'localhost' || h === '127.0.0.1' || h === '::1' ) {
		return true;
	}
	const m = /^192\.168\.(\d{1,3})\.(\d{1,3})$/.exec( h );
	return !! m && +m[ 1 ] <= 255 && +m[ 2 ] <= 255;
}

/** True when a browser request to url stays on this PC. */
export function isLocalUrl( url ) {
	let u;
	try {
		u = new URL( url );
	} catch {
		return false;
	}
	if ( [ 'data:', 'blob:', 'about:' ].includes( u.protocol ) ) {
		return true;
	}
	if ( ! [ 'http:', 'https:', 'ws:', 'wss:' ].includes( u.protocol ) ) {
		return false;
	}
	return isLocalHost( u.hostname );
}

const guarded = new WeakMap();

/**
 * Abort every non-local request made by a page or context. Local requests
 * fall through to any other route handlers (route.fallback()).
 *
 * @param {import('playwright-core').Page|import('playwright-core').BrowserContext} target
 * @returns {Promise<string[]>} The live list of blocked URLs for this target.
 */
export async function guardLocal( target ) {
	if ( guarded.has( target ) ) {
		return guarded.get( target );
	}
	const blocked = [];
	guarded.set( target, blocked );
	await target.route( '**/*', ( route ) => {
		const url = route.request().url();
		if ( isLocalUrl( url ) ) {
			return route.fallback();
		}
		blocked.push( url );
		return route.abort( 'blockedbyclient' );
	} );
	return blocked;
}

// ---------------------------------------------------------------------------
// Small helpers
// ---------------------------------------------------------------------------

function assert( cond, message ) {
	if ( ! cond ) {
		throw new Error( message );
	}
}

/** Digits of a formatted amount as a number ("₹8,00,000" → 800000). */
const digits = ( s ) => Number( String( s ).replace( /[^\d]/g, '' ) );

/** Collapse whitespace (including no-break spaces) for text comparisons. */
const norm = ( s ) => String( s ?? '' ).replace( /[\s\u00a0\u202f]+/g, ' ' ).trim();

/** url with one query parameter set. */
function withQuery( url, key, value ) {
	const u = new URL( url );
	u.searchParams.set( key, value );
	return u.href;
}

/** Wait n animation frames in the page. */
function frames( page, n = 2 ) {
	return page.evaluate( ( count ) => new Promise( ( resolve ) => {
		let i = 0;
		const step = () => ( ++i >= count ? resolve() : requestAnimationFrame( step ) );
		requestAnimationFrame( step );
	} ), n );
}

/** Resolve true once fn( arg ) is truthy in the page, false on timeout. */
async function until( page, fn, arg, timeout = 2000 ) {
	try {
		await page.waitForFunction( fn, arg, { timeout } );
		return true;
	} catch {
		return false;
	}
}

/** Wait until window.scrollY has not moved for a few frames (smooth scrolling). */
function settleScroll( page, timeout = 3000 ) {
	return page.evaluate( ( limit ) => new Promise( ( resolve ) => {
		const t0 = performance.now();
		let last = -1;
		let same = 0;
		const step = () => {
			same = scrollY === last ? same + 1 : 0;
			last = scrollY;
			if ( same >= 5 || performance.now() - t0 > limit ) {
				resolve();
			} else {
				requestAnimationFrame( step );
			}
		};
		requestAnimationFrame( step );
	} ), timeout );
}

/** Jump (no smooth scrolling) to y and let scroll handlers run. */
async function scrollToY( page, y ) {
	await page.evaluate( ( top ) => window.scrollTo( { top, behavior: 'instant' } ), Math.max( 0, Math.round( y ) ) );
	await frames( page, 3 );
}

/** Scroll an element to the middle of the viewport (clear of the sticky header). */
async function center( locator ) {
	await locator.evaluate( ( el ) => el.scrollIntoView( { block: 'center', inline: 'nearest', behavior: 'instant' } ) );
	await frames( locator.page(), 2 );
}

async function clickCentered( locator ) {
	await center( locator );
	await locator.click( { timeout: T } );
}

async function setViewport( page, size ) {
	const now = page.viewportSize();
	if ( ! now || now.width !== size.width || now.height !== size.height ) {
		await page.setViewportSize( size );
		await frames( page, 3 );
	}
}

const isFocused = ( locator ) => locator.evaluate( ( el ) => el === document.activeElement );

/**
 * Run fn while recording page requests and report anything that looks like
 * data being sent: a non-GET request, a request of a type outside
 * INCIDENTAL, or a change of URL.
 */
async function watchRequests( page, fn, settle = 600 ) {
	const seen = [];
	const onRequest = ( req ) => seen.push( { url: req.url(), method: req.method(), type: req.resourceType() } );
	const before = page.url();
	page.on( 'request', onRequest );
	try {
		await fn();
		await page.waitForTimeout( settle );
	} finally {
		page.off( 'request', onRequest );
	}
	const sent = seen.filter( ( r ) => r.method !== 'GET' || ! INCIDENTAL.has( r.type ) );
	return { sent, incidental: seen.length - sent.length, navigated: page.url() !== before };
}

/** A point in the viewport with nothing clickable under it (for "click outside"). */
function quietPoint( page ) {
	return page.evaluate( () => {
		const busy = 'a,button,input,select,textarea,label,summary,details,[tabindex],[role=tab],.hd-has,[data-tour]';
		const spots = [ [ .02, .96 ], [ .98, .96 ], [ .5, .97 ], [ .02, .6 ], [ .98, .6 ], [ .5, .75 ], [ .3, .85 ] ];
		for ( const [ fx, fy ] of spots ) {
			const x = Math.round( innerWidth * fx );
			const y = Math.round( innerHeight * fy );
			const el = document.elementFromPoint( x, y );
			if ( el && ! el.closest( busy ) ) {
				return { x, y };
			}
		}
		return null;
	} );
}

/** Which interactions the page has. */
function detect( page ) {
	return page.evaluate( () => {
		const q = ( s ) => document.querySelector( s );
		const n = ( s ) => document.querySelectorAll( s ).length;
		return {
			menus: n( '.hd-has > .hd-link' ),
			burger: !! q( '.hd-burger' ) && !! q( '#hd-nav' ),
			tablists: n( '[role=tablist]' ),
			scrollTabs: !! q( '[data-scroll-tabs] [role=tablist]' ),
			faq: n( '.faq-btn' ),
			estimator: !! ( q( '#purchase' ) && q( '#expiry' ) ),
			terms: n( 'input[name=term]' ),
			collapse: !! q( '.pc[data-collapse]' ),
			tour: !! q( '[data-tour]' ),
			article: !! q( '.b-article' ),
			progress: !! ( q( '.b-progress' ) && q( '.b-article' ) ),
			toc: !! ( q( '.b-toc' ) && q( '.b-article' ) ),
			copy: !! ( q( '[data-copy]' ) && q( '.b-article' ) ),
			print: !! ( q( '[data-print]' ) && q( '.b-article' ) ),
			slip: n( '[data-lead-form]' ),
			demoForm: !! q( '#demo-form' ),
			wantQuote: !! q( '#demo-form #f-want option[value="quote"]' ),
		};
	} );
}

// ---------------------------------------------------------------------------
// Header: mega menus and the mobile menu (site.js)
// ---------------------------------------------------------------------------

function menuState( page ) {
	return page.evaluate( () => [ ...document.querySelectorAll( '.hd-has > .hd-link' ) ].map( ( b ) => {
		const p = document.getElementById( b.getAttribute( 'aria-controls' ) );
		return { id: b.getAttribute( 'aria-controls' ), exp: b.getAttribute( 'aria-expanded' ), hidden: p ? p.hidden : null };
	} ) );
}

/** Assert that only menu openIndex is open (-1: all closed), scoped to a container. */
async function expectMenus( page, openIndex, when ) {
	const s = await menuState( page );
	s.forEach( ( m, j ) => {
		const open = j === openIndex;
		assert( m.exp === String( open ) && m.hidden === ! open,
			`${ when }: #${ m.id } has aria-expanded=${ m.exp } and hidden=${ m.hidden }` );
	} );
}

async function closeAllMenus( page ) {
	await page.evaluate( () => {
		const nav = document.querySelector( '#hd-nav.open' );
		const burger = document.querySelector( '.hd-burger' );
		if ( nav && burger ) {
			burger.click();
		}
		document.body.click(); // site.js closes mega menus on any click outside .hd-has
	} );
}

async function checkMegaClick( page ) {
	await setViewport( page, DESKTOP );
	await scrollToY( page, 0 );
	const btns = page.locator( '.hd-has > .hd-link' );
	const n = await btns.count();
	const ids = [];
	for ( let i = 0; i < n; i++ ) {
		const b = btns.nth( i );
		const id = await b.getAttribute( 'aria-controls' );
		assert( id && ( await page.locator( `[id="${ id }"]` ).count() ) === 1, `menu ${ i + 1 }: aria-controls="${ id }" has no panel` );
		ids.push( id );
		await b.click( { timeout: T } );
		await expectMenus( page, i, `after clicking ${ id }` );
		assert( await page.locator( `[id="${ id }"]` ).isVisible(), `#${ id } is not visible when open` );
		await b.click( { timeout: T } );
		await expectMenus( page, -1, `after clicking ${ id } again` );
	}
	return `${ n } menus open and close on click (${ ids.map( ( x ) => '#' + x ).join( ', ' ) })`;
}

async function checkMegaOutside( page ) {
	await setViewport( page, DESKTOP );
	await scrollToY( page, 0 );
	const btns = page.locator( '.hd-has > .hd-link' );
	const n = await btns.count();
	const notes = [];
	await btns.nth( 0 ).click( { timeout: T } );
	if ( n > 1 ) {
		await btns.nth( 1 ).click( { timeout: T } );
		await expectMenus( page, 1, 'opening a second menu' );
		notes.push( 'opening one closes the other' );
	}
	const pt = await quietPoint( page );
	if ( pt ) {
		await page.mouse.click( pt.x, pt.y );
		notes.push( `click outside at ${ pt.x },${ pt.y } closes` );
	} else {
		await page.evaluate( () => document.body.click() );
		notes.push( 'click on body closes' );
	}
	await expectMenus( page, -1, 'after a click outside' );
	return notes.join( '; ' );
}

async function checkMegaKeyboard( page ) {
	await setViewport( page, DESKTOP );
	await scrollToY( page, 0 );
	const btns = page.locator( '.hd-has > .hd-link' );
	const n = await btns.count();
	for ( let i = 0; i < n; i++ ) {
		const b = btns.nth( i );
		await b.focus();
		await page.keyboard.press( 'Enter' );
		await expectMenus( page, i, `Enter on menu ${ i + 1 }` );
		await page.keyboard.press( 'Escape' );
		await expectMenus( page, -1, `Escape in menu ${ i + 1 }` );
		assert( await isFocused( b ), `Escape did not return focus to menu button ${ i + 1 }` );
	}
	return `${ n } menus: Enter opens, Escape closes and returns focus to the button`;
}

async function checkBurger( page ) {
	await setViewport( page, PHONE );
	await scrollToY( page, 0 );
	const burger = page.locator( '.hd-burger' );
	const nav = page.locator( '#hd-nav' );
	assert( await burger.isVisible(), 'the burger is not visible at 375px' );
	assert( ! ( await nav.isVisible() ), '#hd-nav is visible at 375px before the burger is used' );

	await burger.click( { timeout: T } );
	const open = await page.evaluate( () => {
		const nav = document.getElementById( 'hd-nav' );
		const b = document.querySelector( '.hd-burger' );
		const r = nav.getBoundingClientRect();
		return {
			cls: nav.classList.contains( 'open' ), body: document.body.classList.contains( 'nav-open' ),
			exp: b.getAttribute( 'aria-expanded' ), label: b.getAttribute( 'aria-label' ),
			icon: b.querySelector( 'use' )?.getAttribute( 'href' ), pos: getComputedStyle( nav ).position,
			rect: { top: r.top, left: r.left, right: r.right, bottom: r.bottom }, vw: innerWidth, vh: innerHeight,
		};
	} );
	assert( open.cls && open.body, 'the burger did not add .open to #hd-nav and .nav-open to body' );
	assert( open.exp === 'true' && open.label === 'Close menu' && open.icon === '#i-x',
		`burger state when open: aria-expanded=${ open.exp }, aria-label="${ open.label }", icon ${ open.icon }` );
	assert( await nav.isVisible(), '#hd-nav is not visible after the burger click' );
	const full = open.pos === 'fixed' && open.rect.left <= 0.5 && open.rect.right >= open.vw - 0.5 && open.rect.bottom >= open.vh - 0.5 && open.rect.top <= 100;
	assert( full, `menu is not full screen: position ${ open.pos }, box ${ JSON.stringify( open.rect ) } in ${ open.vw }×${ open.vh }` );

	// A mega menu inside the mobile menu, then Escape twice: menu first, then the mobile menu.
	const inner = nav.locator( '.hd-has > .hd-link' ).first();
	let innerNote = '';
	if ( await inner.count() ) {
		await clickCentered( inner );
		const id = await inner.getAttribute( 'aria-controls' );
		assert( await inner.getAttribute( 'aria-expanded' ) === 'true' && await page.locator( `[id="${ id }"]` ).isVisible(),
			`#${ id } did not open inside the mobile menu` );
		await page.keyboard.press( 'Escape' );
		assert( await inner.getAttribute( 'aria-expanded' ) === 'false', 'Escape did not close the open menu inside the mobile menu' );
		assert( await nav.evaluate( ( el ) => el.classList.contains( 'open' ) ), 'the first Escape closed the mobile menu instead of the open sub-menu' );
		innerNote = `#${ id } opens inside it; `;
	}
	await page.keyboard.press( 'Escape' );
	const closed = await page.evaluate( () => ( {
		cls: document.getElementById( 'hd-nav' ).classList.contains( 'open' ),
		body: document.body.classList.contains( 'nav-open' ),
		exp: document.querySelector( '.hd-burger' ).getAttribute( 'aria-expanded' ),
		label: document.querySelector( '.hd-burger' ).getAttribute( 'aria-label' ),
	} ) );
	assert( ! closed.cls && ! closed.body && closed.exp === 'false' && closed.label === 'Open menu',
		`Escape did not close the mobile menu (open=${ closed.cls }, aria-expanded=${ closed.exp })` );
	assert( await isFocused( burger ), 'Escape did not return focus to the burger' );
	return `full-screen menu ${ Math.round( open.rect.right - open.rect.left ) }×${ Math.round( open.rect.bottom - open.rect.top ) } below the header; ${ innerNote }Escape closes and refocuses the burger`;
}

// ---------------------------------------------------------------------------
// Tabs (WAI-ARIA keyboard pattern) and the scroll-driven feature tabs
// ---------------------------------------------------------------------------

function tabState( list ) {
	return list.evaluate( ( l ) => [ ...l.querySelectorAll( '[role=tab]' ) ].map( ( t ) => {
		const p = document.getElementById( t.getAttribute( 'aria-controls' ) );
		return { sel: t.getAttribute( 'aria-selected' ) === 'true', ti: t.tabIndex, focus: t === document.activeElement, panel: !! p, shown: !! p && ! p.hidden };
	} ) );
}

async function expectTab( list, k, when, needFocus = true ) {
	const s = await tabState( list );
	s.forEach( ( t, j ) => {
		const on = j === k;
		assert( t.panel, `tab ${ j + 1 } has no panel` );
		assert( t.sel === on && t.shown === on && t.ti === ( on ? 0 : -1 ),
			`${ when }: tab ${ j + 1 } aria-selected=${ t.sel }, panel shown=${ t.shown }, tabindex=${ t.ti } (expected tab ${ k + 1 })` );
	} );
	if ( needFocus ) {
		assert( s[ k ].focus, `${ when }: focus is not on tab ${ k + 1 }` );
	}
}

async function checkTabsKeyboard( page, scrollTabs ) {
	// Inside the scroll track the tabs follow the page scroll at 1025px and up;
	// test the keys where they are plain tabs.
	await setViewport( page, scrollTabs ? TABLET : DESKTOP );
	const lists = page.locator( '[role=tablist]' );
	const count = await lists.count();
	const notes = [];
	for ( let li = 0; li < count; li++ ) {
		const list = lists.nth( li );
		const tabs = list.locator( '[role=tab]' );
		const n = await tabs.count();
		assert( n > 1, `tablist ${ li + 1 } has ${ n } tab(s)` );
		const s0 = ( await tabState( list ) ).findIndex( ( t ) => t.sel );
		assert( s0 >= 0, `tablist ${ li + 1 } has no selected tab` );
		await expectTab( list, s0, `tablist ${ li + 1} on load`, false );
		await center( tabs.nth( s0 ) );
		await tabs.nth( s0 ).focus();
		const steps = [
			[ 'ArrowRight', ( s0 + 1 ) % n ], [ 'ArrowLeft', s0 ], [ 'End', n - 1 ], [ 'Home', 0 ],
			[ 'ArrowLeft', n - 1 ], [ 'ArrowDown', 0 ], [ 'ArrowUp', n - 1 ],
		];
		for ( const [ key, k ] of steps ) {
			await page.keyboard.press( key );
			await expectTab( list, k, `tablist ${ li + 1 }, ${ key }` );
		}
		await page.keyboard.press( 'Home' );
		for ( let i = 0; i < s0; i++ ) {
			await page.keyboard.press( 'ArrowRight' );
		}
		await expectTab( list, s0, `tablist ${ li + 1 } restored` );
		notes.push( `${ n } tabs` );
	}
	return `${ count } tablist(s) (${ notes.join( ', ' ) }) at ${ scrollTabs ? TABLET.width : DESKTOP.width }px: arrows wrap, Home/End jump, panels and tabindex follow`;
}

async function checkScrollTabs( page ) {
	await setViewport( page, DESKTOP );
	const g = await page.evaluate( () => {
		const track = document.querySelector( '[data-scroll-tabs]' );
		const block = track.querySelector( '.tabs' );
		const box = track.getBoundingClientRect();
		return {
			hasBlock: !! block, shown: track.offsetParent !== null,
			top: box.top + scrollY - 100, // STICK in site.js
			len: Math.max( 1, track.offsetHeight - ( block ? block.offsetHeight : 0 ) ),
			n: track.querySelectorAll( '[role=tablist] [role=tab]' ).length,
		};
	} );
	assert( g.hasBlock && g.shown, 'the [data-scroll-tabs] track has no visible .tabs block' );
	const selected = ( k ) => until( page, ( i ) => {
		const t = document.querySelectorAll( '[data-scroll-tabs] [role=tablist] [role=tab]' )[ i ];
		return t && t.getAttribute( 'aria-selected' ) === 'true';
	}, k, 1500 );

	const wrong = [];
	for ( let i = 0; i < g.n; i++ ) {
		await scrollToY( page, g.top + ( i + 0.5 ) / g.n * g.len );
		if ( ! ( await selected( i ) ) ) {
			wrong.push( i + 1 );
		}
	}
	assert( wrong.length === 0, `scrolling through the track did not select tab(s) ${ wrong.join( ', ' ) } of ${ g.n }` );

	// Clicking a tab scrolls to its stretch of the track and keeps it selected.
	await scrollToY( page, g.top + 0.5 / g.n * g.len );
	await selected( 0 );
	const k = Math.floor( g.n / 2 );
	await page.locator( '[data-scroll-tabs] [role=tablist] [role=tab]' ).nth( k ).click( { timeout: T } );
	await settleScroll( page );
	const want = g.top + ( k + 0.5 ) / g.n * g.len;
	const y = await page.evaluate( () => scrollY );
	assert( Math.abs( y - want ) <= 4, `clicking tab ${ k + 1 } scrolled to ${ Math.round( y ) }, expected about ${ Math.round( want ) }` );
	await frames( page, 3 );
	assert( await selected( k ), `tab ${ k + 1 } is not selected after its click-scroll` );
	await scrollToY( page, 0 );
	return `${ g.n } tabs follow the scroll at 1440px; clicking tab ${ k + 1 } scrolls to its stretch (${ Math.round( y ) }px)`;
}

// ---------------------------------------------------------------------------
// FAQ accordion
// ---------------------------------------------------------------------------

async function checkFaq( page ) {
	await setViewport( page, DESKTOP );
	// Every button through its real click handler.
	const bad = await page.evaluate( () => {
		const out = [];
		document.querySelectorAll( '.faq-btn' ).forEach( ( b, i ) => {
			const id = b.getAttribute( 'aria-controls' );
			const p = id && document.getElementById( id );
			const name = `#${ b.id || '?' } (${ i + 1 })`;
			if ( ! p ) {
				out.push( `${ name }: aria-controls="${ id }" has no panel` );
				return;
			}
			if ( b.getAttribute( 'aria-expanded' ) !== 'false' || ! p.hidden ) {
				out.push( `${ name }: not collapsed on load` );
			}
			b.click();
			if ( b.getAttribute( 'aria-expanded' ) !== 'true' || p.hidden ) {
				out.push( `${ name }: did not open` );
			}
			b.click();
			if ( b.getAttribute( 'aria-expanded' ) !== 'false' || ! p.hidden ) {
				out.push( `${ name }: did not close` );
			}
		} );
		return out;
	} );
	assert( bad.length === 0, bad.slice( 0, 5 ).join( '; ' ) + ( bad.length > 5 ? ` (+${ bad.length - 5 } more)` : '' ) );

	// The first visible button with a real click and the keyboard.
	const btn = page.locator( '.faq-btn' ).filter( { visible: true } ).first();
	assert( await btn.count(), 'no visible FAQ button' );
	const panel = page.locator( `[id="${ await btn.getAttribute( 'aria-controls' ) }"]` );
	await clickCentered( btn );
	assert( await btn.getAttribute( 'aria-expanded' ) === 'true' && await panel.isVisible(), 'mouse click did not open the first answer' );
	await btn.focus();
	await page.keyboard.press( 'Enter' );
	assert( await btn.getAttribute( 'aria-expanded' ) === 'false' && ! ( await panel.isVisible() ), 'Enter did not close the first answer' );
	await page.keyboard.press( 'Space' );
	assert( await btn.getAttribute( 'aria-expanded' ) === 'true', 'Space did not open the first answer' );
	await page.keyboard.press( 'Space' );
	assert( await btn.getAttribute( 'aria-expanded' ) === 'false', 'Space did not close the first answer' );
	const n = await page.locator( '.faq-btn' ).count();
	return `${ n } answers toggle aria-expanded and hidden; click, Enter and Space work`;
}

// ---------------------------------------------------------------------------
// Expiry estimator
// ---------------------------------------------------------------------------

function estimatorState( page ) {
	return page.evaluate( () => {
		const t = ( id ) => document.getElementById( id )?.textContent.trim() ?? null;
		const r = ( el ) => ( { v: +el.value, min: +el.min, max: +el.max, step: +el.step, fill: el.style.getPropertyValue( '--fill' ) } );
		return {
			p: r( document.getElementById( 'purchase' ) ), x: r( document.getElementById( 'expiry' ) ),
			out: { purchase: t( 'purchase-out' ), expiry: t( 'expiry-out' ), month: t( 'loss-month' ), year: t( 'loss-year' ), half: t( 'loss-half' ) },
		};
	} );
}

/** Problems with the estimator outputs for its current slider values (site.js calc()). */
function estimatorProblems( s ) {
	const monthly = s.p.v * ( s.x.v / 100 );
	const want = {
		purchase: Math.round( s.p.v ), expiry: s.x.v.toFixed( 1 ) + '%',
		month: Math.round( monthly ), year: Math.round( monthly * 12 ), half: Math.round( monthly * 6 ),
	};
	const out = [];
	for ( const k of [ 'purchase', 'month', 'year', 'half' ] ) {
		if ( s.out[ k ] === null || digits( s.out[ k ] ) !== want[ k ] || ! s.out[ k ].includes( '₹' ) ) {
			out.push( `${ k } shows "${ s.out[ k ] }", expected ₹${ want[ k ].toLocaleString( 'en-IN' ) }` );
		}
	}
	if ( s.out.expiry !== want.expiry ) {
		out.push( `expiry shows "${ s.out.expiry }", expected "${ want.expiry }"` );
	}
	for ( const [ name, r ] of [ [ 'purchase', s.p ], [ 'expiry', s.x ] ] ) {
		const fill = ( r.v - r.min ) / ( r.max - r.min ) * 100;
		if ( Math.abs( parseFloat( r.fill ) - fill ) > 0.01 ) {
			out.push( `${ name } --fill is "${ r.fill }", expected ${ fill.toFixed( 2 ) }%` );
		}
	}
	return out;
}

async function checkEstimator( page ) {
	await setViewport( page, DESKTOP );
	const s0 = await estimatorState( page );
	let bad = estimatorProblems( s0 );
	assert( bad.length === 0, `on load: ${ bad.join( '; ' ) }` );

	const purchase = page.locator( '#purchase' );
	const expiry = page.locator( '#expiry' );
	await center( purchase );
	await purchase.focus();
	await page.keyboard.press( 'ArrowRight' );
	await page.keyboard.press( 'ArrowRight' );
	await expiry.focus();
	for ( let i = 0; i < 3; i++ ) {
		await page.keyboard.press( 'ArrowRight' );
	}
	const s1 = await estimatorState( page );
	const wantP = Math.min( s0.p.max, s0.p.v + 2 * s0.p.step );
	const wantX = Math.min( s0.x.max, Math.round( ( s0.x.v + 3 * s0.x.step ) * 1000 ) / 1000 );
	assert( s1.p.v === wantP && Math.abs( s1.x.v - wantX ) < 1e-9, `sliders moved to ${ s1.p.v } / ${ s1.x.v }, expected ${ wantP } / ${ wantX }` );
	assert( s1.out.year !== s0.out.year && s1.out.month !== s0.out.month, 'the loss outputs did not change when the sliders moved' );
	bad = estimatorProblems( s1 );
	assert( bad.length === 0, `after moving the sliders: ${ bad.join( '; ' ) }` );

	// Back to the starting values.
	await page.evaluate( ( v ) => {
		for ( const [ id, val ] of [ [ 'purchase', v.p ], [ 'expiry', v.x ] ] ) {
			const el = document.getElementById( id );
			el.value = String( val );
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		}
	}, { p: s0.p.v, x: s0.x.v } );
	return `${ s0.out.purchase } at ${ s0.out.expiry } → ${ s1.out.purchase } at ${ s1.out.expiry }: year ${ s0.out.year } → ${ s1.out.year }, month ${ s1.out.month }, half ${ s1.out.half }`;
}

// ---------------------------------------------------------------------------
// Pricing: term switch and comparison table (pricing.js)
// ---------------------------------------------------------------------------

function termState( page ) {
	return page.evaluate( () => ( {
		radios: [ ...document.querySelectorAll( 'input[name=term]' ) ].map( ( r ) => ( {
			checked: r.checked, years: +r.value || 1, discount: +r.getAttribute( 'data-discount' ) || 0,
		} ) ),
		totals: [ ...document.querySelectorAll( '.term-total' ) ].map( ( el ) => ( { price: +el.getAttribute( 'data-price' ), text: el.textContent } ) ),
	} ) );
}

/** "About ₹X a month" / "Billed ₹X for N years" → { years, amount }. */
function parseTerm( text ) {
	const t = norm( text );
	let m = /^About ₹ ?([\d,]+) a month$/.exec( t );
	if ( m ) {
		return { years: 1, amount: digits( m[ 1 ] ) };
	}
	m = /^Billed ₹ ?([\d,]+) for (\d+) years$/.exec( t );
	return m ? { years: +m[ 2 ], amount: digits( m[ 1 ] ) } : null;
}

/** Same rule as update() in pricing.js. */
function termExpected( price, years, discount ) {
	const total = price * years * ( 1 - discount / 100 );
	return { years, amount: years === 1 ? Math.round( total / 12 ) : Math.round( total ) };
}

function termProblems( s, when, tolerance = 0 ) {
	const on = s.radios.findIndex( ( r ) => r.checked );
	if ( on < 0 ) {
		return [ `${ when }: no term is checked` ];
	}
	const { years, discount } = s.radios[ on ];
	const out = [];
	s.totals.forEach( ( t, i ) => {
		const got = parseTerm( t.text );
		const want = termExpected( t.price, years, discount );
		if ( ! got || got.years !== want.years || Math.abs( got.amount - want.amount ) > tolerance ) {
			out.push( `${ when }: total ${ i + 1 } (₹${ t.price }/year) shows "${ norm( t.text ) }", expected ${ want.years === 1 ? `About ₹${ want.amount } a month` : `Billed ₹${ want.amount } for ${ years } years` }` );
		}
	} );
	return out;
}

async function checkTermSwitch( page ) {
	await setViewport( page, DESKTOP );
	const s0 = await termState( page );
	assert( s0.totals.length > 0, 'the term switch is on the page but there is no .term-total to update' );
	const first = s0.radios.findIndex( ( r ) => r.checked );
	assert( first === 0, `term ${ first + 1 } is checked on load, expected the first` );
	// The PHP-rendered text should match pricing.js for the first term (±₹1 for rounding).
	let bad = termProblems( s0, 'on load', 1 );
	assert( bad.length === 0, bad.join( '; ' ) );
	const initialTexts = s0.totals.map( ( t ) => norm( t.text ) );

	const radios = page.locator( 'input[name=term]' );
	const n = await radios.count();
	const order = [ ...Array.from( { length: n - 1 }, ( _, i ) => i + 1 ), 0 ];
	for ( const j of order ) {
		const label = radios.nth( j ).locator( 'xpath=ancestor::label[1]' );
		await clickCentered( ( await label.count() ) ? label : radios.nth( j ) );
		const s = await termState( page );
		assert( s.radios[ j ].checked, `clicking term ${ j + 1 } did not check it` );
		bad = termProblems( s, `term ${ j + 1 } (${ s.radios[ j ].years } yr, ${ s.radios[ j ].discount }% off)` );
		assert( bad.length === 0, bad.slice( 0, 3 ).join( '; ' ) );
	}
	const back = ( await termState( page ) ).totals.map( ( t ) => norm( t.text ) );
	const drift = back.filter( ( t, i ) => t !== initialTexts[ i ] ).length;

	// Keyboard: arrows move through the radio group.
	let keys = '';
	if ( n > 1 ) {
		await radios.nth( 0 ).focus();
		await page.keyboard.press( 'ArrowRight' );
		let s = await termState( page );
		assert( s.radios[ 1 ].checked, 'ArrowRight did not move to the second term' );
		bad = termProblems( s, 'ArrowRight' );
		assert( bad.length === 0, bad.join( '; ' ) );
		await page.keyboard.press( 'ArrowLeft' );
		s = await termState( page );
		assert( s.radios[ 0 ].checked, 'ArrowLeft did not move back to the first term' );
		keys = '; arrow keys switch terms';
	}
	return `${ n } terms × ${ s0.totals.length } totals match pricing.js` + keys +
		( drift ? `; note: ${ drift } first-term text(s) differ from the server text by rounding` : '' );
}

function collapseState( page ) {
	return page.evaluate( () => {
		const all = document.querySelector( '.pc-all' );
		return {
			groups: [ ...document.querySelectorAll( '.pc[data-collapse] .pc-toggle' ) ].map( ( b ) => {
				const p = document.getElementById( b.getAttribute( 'aria-controls' ) );
				return { exp: b.getAttribute( 'aria-expanded' ), panel: !! p, hidden: p ? p.hidden : null };
			} ),
			all: all ? { hidden: all.hidden, exp: all.getAttribute( 'aria-expanded' ), text: all.textContent.trim() } : null,
		};
	} );
}

function expectGroups( s, open, when ) {
	s.groups.forEach( ( g, i ) => {
		const on = open( i );
		assert( g.panel, `group ${ i + 1 } has no panel` );
		assert( g.exp === String( on ) && g.hidden === ! on, `${ when }: group ${ i + 1 } aria-expanded=${ g.exp }, hidden=${ g.hidden }` );
	} );
	if ( s.all ) {
		const anyClosed = s.groups.some( ( g ) => g.exp !== 'true' );
		assert( s.all.exp === String( ! anyClosed ) && s.all.text === ( anyClosed ? 'Expand all' : 'Collapse all' ),
			`${ when }: .pc-all reads "${ s.all.text }" with aria-expanded=${ s.all.exp }` );
	}
}

async function checkCompareTable( page ) {
	await setViewport( page, DESKTOP );
	let s = await collapseState( page );
	const n = s.groups.length;
	assert( n > 0, 'the comparison table has no .pc-toggle buttons' );
	expectGroups( s, ( i ) => i === 0, 'on load' );

	const toggles = page.locator( '.pc[data-collapse] .pc-toggle' );
	const k = n > 1 ? 1 : 0;
	const startOpen = k === 0;
	await clickCentered( toggles.nth( k ) );
	expectGroups( await collapseState( page ), ( i ) => ( i === k ? ! startOpen : i === 0 ), `clicking group ${ k + 1 }` );
	await toggles.nth( k ).click( { timeout: T } );
	expectGroups( await collapseState( page ), ( i ) => i === 0, `clicking group ${ k + 1 } again` );

	// The plan counts in the group row toggle too.
	let row = '';
	const cell = toggles.nth( k ).locator( 'xpath=ancestor::tr[1]' ).locator( 'td' ).first();
	if ( await cell.count() ) {
		await cell.click( { timeout: T } );
		expectGroups( await collapseState( page ), ( i ) => ( i === k ? ! startOpen : i === 0 ), `clicking group row ${ k + 1 }` );
		await cell.click( { timeout: T } );
		expectGroups( await collapseState( page ), ( i ) => i === 0, `clicking group row ${ k + 1 } again` );
		row = ', group rows toggle';
	}

	let all = '';
	s = await collapseState( page );
	if ( s.all ) {
		assert( ! s.all.hidden, '.pc-all is still hidden (pricing.js shows it)' );
		const btn = page.locator( '.pc-all' );
		const first = s.all.text;
		await clickCentered( btn );
		const s1 = await collapseState( page );
		const opened = s1.groups.every( ( g ) => g.exp === 'true' );
		expectGroups( s1, () => opened, `"${ first }"` );
		await btn.click( { timeout: T } );
		const s2 = await collapseState( page );
		expectGroups( s2, () => ! opened, `"${ s1.all.text }"` );
		if ( ! opened ) {
			// The first click collapsed everything (only possible with a single group): open again.
			await btn.click( { timeout: T } );
		}
		// Back to the load state: first group open, the rest closed.
		for ( let i = 0; i < n; i++ ) {
			const g = ( await collapseState( page ) ).groups[ i ];
			if ( ( g.exp === 'true' ) !== ( i === 0 ) ) {
				await toggles.nth( i ).evaluate( ( b ) => b.click() );
			}
		}
		expectGroups( await collapseState( page ), ( i ) => i === 0, 'restoring the load state' );
		all = `, "${ first }" / "${ s1.all.text }" work`;
	}
	return `${ n } groups (first open on load)${ row }${ all }`;
}

// ---------------------------------------------------------------------------
// Product tour (tour.js)
// ---------------------------------------------------------------------------

function tourState( page ) {
	return page.evaluate( () => {
		const p = document.querySelector( '[data-tour]' );
		const bar = p.querySelector( '.tour-progress i' );
		const m = /scaleX\(([-\d.e]+)\)/.exec( bar ? bar.style.transform : '' );
		const stage = p.querySelector( '.tour-stage' );
		return {
			playing: p.classList.contains( 'is-playing' ),
			play: p.querySelector( '[data-tour-play]' )?.getAttribute( 'aria-label' ),
			fsLabel: p.querySelector( '[data-tour-fs]' )?.getAttribute( 'aria-label' ),
			progress: m ? +m[ 1 ] : null,
			isFs: p.classList.contains( 'is-fs' ),
			fsEl: document.fullscreenElement === p,
			ended: p.classList.contains( 'is-ended' ) || ! p.querySelector( '.tour-end' )?.hidden,
			scene: stage?.getAttribute( 'data-scene' ),
			row2: !! stage?.querySelector( '.t-row2.on' ),
			qty: stage?.querySelector( '.t-qty' )?.textContent.trim() ?? null,
		};
	} );
}

const playLabel = ( playing ) => ( playing ? 'Pause product tour' : 'Play product tour' );

async function checkTourPlayPause( page ) {
	await setViewport( page, DESKTOP );
	const player = page.locator( '[data-tour]' );
	const btn = player.locator( '[data-tour-play]' );
	assert( await btn.count(), 'no [data-tour-play] button' );
	await center( player );
	await page.waitForTimeout( 800 ); // let autoplay start where it applies
	const s0 = await tourState( page );
	assert( s0.progress !== null, 'the tour progress bar has no scaleX() transform' );
	assert( s0.play === playLabel( s0.playing ), `play button reads "${ s0.play }" while ${ s0.playing ? 'playing' : 'paused' }` );
	const notes = [ `starts ${ s0.playing ? 'playing' : 'paused' }` ];
	for ( let round = 1; round <= 2; round++ ) {
		await btn.click( { timeout: T } );
		const a = await tourState( page );
		const want = round === 1 ? ! s0.playing : s0.playing;
		assert( a.playing === want && a.play === playLabel( want ), `click ${ round }: is-playing=${ a.playing }, button "${ a.play }"` );
		await page.waitForTimeout( 600 );
		const b = await tourState( page );
		if ( want ) {
			assert( b.progress !== a.progress, `click ${ round }: playing but the progress bar did not move` );
		} else {
			assert( b.progress === a.progress, `click ${ round }: paused but the progress bar moved (${ a.progress } → ${ b.progress })` );
		}
		notes.push( want ? 'click plays (progress moves)' : 'click pauses (progress stops)' );
	}
	return notes.join( ', ' );
}

async function checkTourFullscreen( page ) {
	await setViewport( page, DESKTOP );
	const player = page.locator( '[data-tour]' );
	const btn = player.locator( '[data-tour-fs]' );
	assert( await btn.count(), 'no [data-tour-fs] button' );
	await center( player );
	// Record requestFullscreen()/exitFullscreen() calls (and their errors) without changing what they do.
	await page.evaluate( () => {
		const E = Element.prototype;
		const D = Document.prototype;
		window.__rxFs = { req: 0, onPlayer: 0, exit: 0, err: null, orig: { req: E.requestFullscreen, exit: D.exitFullscreen } };
		E.requestFullscreen = function ( ...a ) {
			window.__rxFs.req++;
			if ( this.matches( '[data-tour]' ) ) {
				window.__rxFs.onPlayer++;
			}
			const r = window.__rxFs.orig.req.apply( this, a );
			if ( r && r.catch ) {
				r.catch( ( e ) => { window.__rxFs.err = String( e && e.message || e ); } );
			}
			return r;
		};
		D.exitFullscreen = function ( ...a ) {
			window.__rxFs.exit++;
			return window.__rxFs.orig.exit.apply( this, a );
		};
	} );
	const calls = () => page.evaluate( () => ( { req: window.__rxFs.req, onPlayer: window.__rxFs.onPlayer, exit: window.__rxFs.exit, err: window.__rxFs.err } ) );
	try {
		await btn.click( { timeout: T } );
		await until( page, () => document.querySelector( '[data-tour]' ).classList.contains( 'is-fs' ), null, 1500 );
		const on = await tourState( page );
		const c = await calls();
		assert( c.onPlayer === 1, `the full-screen button made ${ c.onPlayer } requestFullscreen() call(s) on the player` );
		assert( on.playing, 'entering full screen did not start the tour' );
		if ( ! on.fsEl ) {
			// Headless browsers may refuse full screen; the request itself is the behaviour under test.
			return `requestFullscreen() called on the player and the tour plays; the browser did not grant full screen (${ c.err || 'no error' })`;
		}
		assert( on.isFs && on.fsLabel === 'Exit full screen', `in full screen: is-fs=${ on.isFs }, button "${ on.fsLabel }"` );
		await btn.click( { timeout: T } );
		const left = await until( page, () => ! document.fullscreenElement && ! document.querySelector( '[data-tour]' ).classList.contains( 'is-fs' ), null, 2000 );
		const off = await tourState( page );
		assert( left && off.fsLabel === 'Full screen', `leaving full screen: fullscreenElement=${ off.fsEl }, is-fs=${ off.isFs }, button "${ off.fsLabel }"` );
		assert( ( await calls() ).exit === 1, 'the second click did not call exitFullscreen()' );
		return 'enters full screen on the player (is-fs, "Exit full screen", plays) and leaves it on the second click';
	} finally {
		await page.evaluate( async () => {
			if ( document.fullscreenElement ) {
				await window.__rxFs.orig.exit.call( document ).catch( () => {} );
			}
			Element.prototype.requestFullscreen = window.__rxFs.orig.req;
			Document.prototype.exitFullscreen = window.__rxFs.orig.exit;
		} ).catch( () => {} );
	}
}

async function checkTourAt( page, url ) {
	await setViewport( page, DESKTOP );
	const res = await page.goto( withQuery( url, 'tour-at', '15' ), { waitUntil: 'load', timeout: 30_000 } );
	assert( res && res.status() < 400, `?tour-at=15 returned HTTP ${ res && res.status() }` );
	await center( page.locator( '[data-tour]' ) );
	await page.waitForTimeout( 700 );
	const a = await tourState( page );
	await page.waitForTimeout( 700 );
	const b = await tourState( page );
	const want = 15000 / TOUR_TOTAL;
	assert( ! a.playing && ! b.playing, 'the tour plays with ?tour-at=15 (it should stay frozen)' );
	assert( a.progress !== null && Math.abs( a.progress - want ) < 0.002, `progress is ${ a.progress }, expected ${ want.toFixed( 4 ) } (15 s of 43 s)` );
	assert( b.progress === a.progress, 'the frozen tour moved' );
	assert( a.scene === 'bill' && a.row2 && a.qty === '2' && ! a.ended,
		`frame at 15 s: scene "${ a.scene }", second row ${ a.row2 ? 'shown' : 'missing' }, qty "${ a.qty }"` );
	return `frozen at ${ ( a.progress * TOUR_TOTAL / 1000 ).toFixed( 1 ) } s on the sale screen (second item, qty 2), not playing`;
}

async function checkTourAutoplay( page, url ) {
	await setViewport( page, DESKTOP );
	const playing = ( on, timeout ) => until( page, ( want ) => document.querySelector( '[data-tour]' ).classList.contains( 'is-playing' ) === want, on, timeout );
	const notes = [];

	await page.emulateMedia( { reducedMotion: 'no-preference' } );
	await page.goto( url, { waitUntil: 'load', timeout: 30_000 } );
	const player = page.locator( '[data-tour]' );
	await scrollToY( page, await page.evaluate( () => document.documentElement.scrollHeight ) );
	assert( await playing( false, 1000 ), 'the tour plays while it is off screen' );
	await center( player );
	assert( await playing( true, 2500 ), 'the tour did not autoplay when scrolled into view' );
	notes.push( 'autoplays in view' );
	await page.waitForTimeout( 600 );
	await scrollToY( page, await page.evaluate( () => document.documentElement.scrollHeight ) );
	assert( await playing( false, 2000 ), 'the tour kept playing after it was scrolled away' );
	notes.push( 'pauses when scrolled away' );
	await center( player );
	assert( await playing( true, 2500 ), 'the tour did not start again when scrolled back' );
	const back = await tourState( page );
	assert( back.progress < 0.05, `scrolled back, the tour resumed at ${ ( back.progress * 43 ).toFixed( 1 ) } s instead of restarting` );
	notes.push( 'restarts from 0 s when back in view' );

	await page.emulateMedia( { reducedMotion: 'reduce' } );
	await page.goto( url, { waitUntil: 'load', timeout: 30_000 } );
	await center( player );
	await page.waitForTimeout( 1500 );
	assert( ! ( await tourState( page ) ).playing, 'the tour autoplays with prefers-reduced-motion: reduce' );
	notes.push( 'no autoplay with reduced motion' );
	return notes.join( ', ' );
}

// ---------------------------------------------------------------------------
// Blog post tools (blog.js)
// ---------------------------------------------------------------------------

async function checkProgress( page ) {
	await setViewport( page, DESKTOP );
	const read = () => page.evaluate( () => {
		const v = document.querySelector( '.b-progress' ).style.getPropertyValue( '--p' );
		return v === '' ? null : +v;
	} );
	const g = await page.evaluate( () => {
		const a = document.querySelector( '.b-article' ).getBoundingClientRect();
		return { top: a.top + scrollY, height: a.height, vh: innerHeight, max: document.documentElement.scrollHeight - innerHeight };
	} );
	const total = g.height - g.vh * 0.6;
	await scrollToY( page, 0 );
	const p0 = await read();
	await scrollToY( page, Math.min( g.max, g.top + total / 2 ) );
	const p1 = await read();
	await scrollToY( page, Math.min( g.max, g.top + total + 20 ) );
	const p2 = await read();
	await scrollToY( page, 0 );
	assert( p0 !== null, 'blog.js never set --p on .b-progress' );
	assert( p0 === 0, `--p is ${ p0 } at the top of the page, expected 0` );
	assert( p1 > 0 && p1 < 1, `--p is ${ p1 } half-way through the article` );
	assert( p2 >= 0.999, `--p is ${ p2 } at the end of the article, expected 1` );
	const style = await page.evaluate( () => ( {
		display: getComputedStyle( document.querySelector( '.b-progress' ) ).display,
		reduce: matchMedia( '(prefers-reduced-motion: reduce)' ).matches,
	} ) );
	if ( style.reduce ) {
		assert( style.display === 'none', 'the progress bar is shown with reduced motion (blog.js hides it)' );
	} else {
		assert( style.display !== 'none', 'the progress bar is hidden' );
	}
	return `--p ${ p0 } → ${ p1.toFixed( 2 ) } → ${ p2 }${ style.reduce ? ' (bar hidden for reduced motion)' : '' }`;
}

async function checkToc( page ) {
	await setViewport( page, DESKTOP );
	const info = await page.evaluate( () => {
		const toc = document.querySelector( '.b-toc' );
		const heads = [ ...document.querySelectorAll( '.b-article h2' ) ];
		return {
			open: toc.open, narrow: matchMedia( '(max-width: 1100px)' ).matches,
			links: [ ...toc.querySelectorAll( 'a' ) ].map( ( a ) => {
				const id = ( a.getAttribute( 'href' ) || '' ).replace( /^#/, '' );
				const h = id ? document.getElementById( id ) : null;
				return {
					id, text: a.textContent, target: !! h, isH2: !! h && h.tagName === 'H2' && !! h.closest( '.b-article' ),
					head: h ? h.textContent : '', order: h ? heads.indexOf( h ) : -1,
				};
			} ),
			h2: heads.length,
		};
	} );
	const n = info.links.length;
	assert( n > 0, 'the table of contents has no links' );
	assert( info.open === ! info.narrow, `.b-toc open=${ info.open } at ${ info.narrow ? '≤' : '>' }1100px` );
	const bad = info.links.filter( ( l ) => ! l.isH2 || norm( l.text ) !== norm( l.head ) ).map( ( l ) => `#${ l.id }` );
	assert( bad.length === 0, `TOC links without a matching article h2: ${ bad.join( ', ' ) }` );
	const orders = info.links.map( ( l ) => l.order );
	assert( orders.every( ( o, i ) => i === 0 || o > orders[ i - 1 ] ), 'TOC links are not in document order' );

	// Follow a link, then check the current-section marker.
	const k = Math.min( 1, n - 1 );
	const link = page.locator( '.b-toc a' ).nth( k );
	if ( info.narrow ) {
		await page.locator( '.b-toc summary' ).click( { timeout: T } );
	}
	await link.click( { timeout: T } );
	await settleScroll( page );
	const id = info.links[ k ].id;
	const after = await page.evaluate( ( hid ) => ( { hash: location.hash, top: document.getElementById( hid ).getBoundingClientRect().top, vh: innerHeight } ), id );
	assert( decodeURIComponent( after.hash ) === `#${ id }`, `clicking the TOC link left the hash at "${ after.hash }"` );
	assert( after.top >= -2 && after.top < after.vh * 0.4, `#${ id } is at ${ Math.round( after.top ) }px after the TOC click` );
	const marked = await until( page, ( i ) => {
		const on = [ ...document.querySelectorAll( '.b-toc a.is-on' ) ];
		return on.length === 1 && on[ 0 ] === document.querySelectorAll( '.b-toc a' )[ i ] && on[ 0 ].getAttribute( 'aria-current' ) === 'location';
	}, k, 2500 );
	assert( marked, `#${ id } is not marked as the current section (.is-on + aria-current) after scrolling to it` );
	await scrollToY( page, 0 );
	return `${ n } links for ${ info.h2 } article h2s, in order; link ${ k + 1 } jumps to #${ id } and is marked current`;
}

async function checkCopyLink( page ) {
	await setViewport( page, DESKTOP );
	// The real clipboard is never touched: navigator.clipboard is shadowed by a
	// recorder (an own property, so it works on plain-http LAN addresses too,
	// where the browser has no clipboard API) and prompt() is recorded. That
	// runs all three branches of blog.js on any origin: writeText succeeds,
	// writeText is refused (prompt fallback), and no clipboard API (prompt).
	await page.evaluate( () => {
		const rec = window.__rxCopy = { got: [], mode: 'ok', prompt: window.prompt };
		window.prompt = ( msg, value ) => {
			rec.got.push( { via: 'prompt', text: value } );
			return null;
		};
		const clip = { writeText: ( text ) => {
			rec.got.push( { via: 'clipboard', text } );
			return rec.mode === 'ok' ? Promise.resolve() : Promise.reject( new Error( 'not allowed' ) );
		} };
		Object.defineProperty( navigator, 'clipboard', { configurable: true, get: () => ( rec.mode === 'none' ? undefined : clip ) } );
	} );
	const got = () => page.evaluate( () => window.__rxCopy.got.map( ( g ) => `${ g.via }:${ g.text }` ) );
	const label = () => page.evaluate( () => document.querySelector( '[data-copy] span' )?.textContent.trim() ?? null );
	try {
		const btn = page.locator( '[data-copy]' ).first();
		const attr = await btn.getAttribute( 'data-copy' );
		assert( attr && attr.startsWith( '/' ) && ! attr.startsWith( '//' ), `data-copy="${ attr }" is not a root-relative URL` );
		const loc = await page.evaluate( () => ( { origin: location.origin, path: location.pathname } ) );
		const url = loc.origin + attr;
		assert( new URL( url ).pathname === loc.path, `data-copy="${ attr }" is not this page (${ loc.path })` );
		assert( await label() === 'Copy link', `the button reads "${ await label() }" before copying, expected "Copy link"` );

		// 1. Clipboard available.
		await clickCentered( btn );
		assert( await until( page, () => window.__rxCopy.got.length > 0, null, 1500 ), 'clicking Copy link copied nothing' );
		let g = await got();
		assert( g.length === 1 && g[ 0 ] === `clipboard:${ url }`, `copied ${ g.join( ', ' ) }, expected clipboard:${ url }` );
		assert( await until( page, () => document.querySelector( '[data-copy] span' )?.textContent.trim() === 'Link copied', null, 1500 ),
			`the button reads "${ await label() }" after copying, expected "Link copied"` );
		assert( await until( page, () => document.querySelector( '[data-copy] span' )?.textContent.trim() === 'Copy link', null, 3500 ),
			`the button still reads "${ await label() }" after 2 s, expected "Copy link" again` );

		// 2. Clipboard refuses: the link is offered in a prompt instead.
		await page.evaluate( () => { window.__rxCopy.got = []; window.__rxCopy.mode = 'refuse'; } );
		await btn.click( { timeout: T } );
		await until( page, () => window.__rxCopy.got.length > 1, null, 1500 );
		g = await got();
		assert( g.join( ' ' ) === `clipboard:${ url } prompt:${ url }`, `with the clipboard refused: ${ g.join( ', ' ) || 'nothing' }, expected the clipboard then a prompt` );
		assert( await label() === 'Copy link', `with the clipboard refused the button reads "${ await label() }"` );

		// 3. No clipboard API at all (plain http on a LAN address): prompt only.
		await page.evaluate( () => { window.__rxCopy.got = []; window.__rxCopy.mode = 'none'; } );
		await btn.click( { timeout: T } );
		await until( page, () => window.__rxCopy.got.length > 0, null, 1500 );
		g = await got();
		assert( g.length === 1 && g[ 0 ] === `prompt:${ url }`, `without a clipboard API: ${ g.join( ', ' ) || 'nothing' }, expected prompt:${ url }` );
		return `copies ${ url } ("Link copied", back to "Copy link" after 2 s); prompt() fallback when the clipboard is refused or missing`;
	} finally {
		await page.evaluate( () => {
			window.prompt = window.__rxCopy.prompt;
			delete navigator.clipboard; // removes the own-property recorder only
		} ).catch( () => {} );
	}
}

async function checkPrint( page ) {
	await setViewport( page, DESKTOP );
	await page.evaluate( () => {
		window.__rxPrint = { n: 0, orig: window.print };
		window.print = () => { window.__rxPrint.n++; };
	} );
	try {
		const btns = page.locator( '[data-print]' );
		const n = await btns.count();
		for ( let i = 0; i < n; i++ ) {
			await clickCentered( btns.nth( i ) );
			const got = await page.evaluate( () => window.__rxPrint.n );
			assert( got === i + 1, `print button ${ i + 1 } did not call window.print()` );
		}
		await scrollToY( page, 0 );
		return `${ n } print button(s) call window.print()`;
	} finally {
		await page.evaluate( () => { window.print = window.__rxPrint.orig; } ).catch( () => {} );
	}
}

// ---------------------------------------------------------------------------
// Forms: hero demo slip and demo form (site.js; nothing is sent)
// ---------------------------------------------------------------------------

function sentProblems( net ) {
	const out = [];
	if ( net.sent.length ) {
		out.push( `requests during submit: ${ net.sent.slice( 0, 3 ).map( ( r ) => `${ r.method } ${ r.type } ${ r.url }` ).join( ', ' ) }` );
	}
	if ( net.navigated ) {
		out.push( 'the page URL changed on submit' );
	}
	return out;
}

async function checkSlip( page ) {
	await setViewport( page, DESKTOP );
	const slip = page.locator( '[data-lead-form]' ).filter( { visible: true } ).first();
	assert( await slip.count(), 'no visible [data-lead-form]' );
	const name = slip.locator( 'input[name=name]' );
	const phone = slip.locator( 'input[name=phone]' );
	const submit = slip.locator( '[type=submit]' );
	const state = () => slip.evaluate( ( f ) => {
		const fld = ( el ) => ( { invalid: el.closest( '.fld' ).classList.contains( 'invalid' ), aria: el.getAttribute( 'aria-invalid' ) } );
		const done = f.querySelector( '.slip-done' );
		return {
			name: fld( f.elements.name ), phone: fld( f.elements.phone ), done: !! done && ! done.hidden,
			isDone: f.classList.contains( 'is-done' ), lead: f.querySelector( '[data-lead-name]' )?.textContent ?? null,
			focus: document.activeElement === f.elements.name ? 'name' : document.activeElement === f.elements.phone ? 'phone' : document.activeElement === done ? 'done' : 'other',
			fName: document.getElementById( 'f-name' )?.value ?? null, fPhone: document.getElementById( 'f-phone' )?.value ?? null,
		};
	} );
	await center( slip );
	await name.fill( '' );
	await phone.fill( '' );
	const hasForm = await page.locator( '#demo-form' ).count();
	if ( hasForm ) {
		await page.evaluate( () => {
			document.getElementById( 'f-name' ).value = '';
			document.getElementById( 'f-phone' ).value = '';
		} );
	}
	const steps = [];
	const net = await watchRequests( page, async () => {
		await submit.click( { timeout: T } );
		let s = await state();
		assert( s.name.invalid && s.name.aria === 'true' && s.phone.invalid && s.phone.aria === 'true' && s.focus === 'name' && ! s.done,
			`empty submit: name invalid=${ s.name.invalid }, phone invalid=${ s.phone.invalid }, focus on ${ s.focus }, thank-you shown=${ s.done }` );
		steps.push( 'empty → both errors, focus on name' );

		await name.fill( 'Test Owner' );
		await phone.fill( '12345' );
		await submit.click( { timeout: T } );
		s = await state();
		assert( ! s.name.invalid && s.name.aria === 'false' && s.phone.invalid && s.focus === 'phone' && ! s.done,
			`bad phone: name invalid=${ s.name.invalid }, phone invalid=${ s.phone.invalid }, focus on ${ s.focus }` );
		steps.push( 'bad mobile → phone error only' );

		await phone.fill( '98765 43210' );
		await submit.click( { timeout: T } );
		s = await state();
		assert( s.isDone && s.done && s.focus === 'done' && s.lead === ', Test',
			`valid submit: is-done=${ s.isDone }, thank-you shown=${ s.done }, focus on ${ s.focus }, name "${ s.lead }"` );
		steps.push( 'valid → thank-you ("Done, Test")' );
		if ( hasForm ) {
			assert( s.fName === 'Test Owner' && s.fPhone === '9876543210', `#demo-form was not prefilled (name "${ s.fName }", phone "${ s.fPhone }")` );
			steps.push( '#demo-form prefilled' );
		}
	} );
	const bad = sentProblems( net );
	assert( bad.length === 0, bad.join( '; ' ) );
	return `${ steps.join( '; ' ) }; nothing sent${ net.incidental ? ` (${ net.incidental } incidental image/font request(s))` : '' }`;
}

const VALID = { name: 'Test Owner', phone: '9876543210', store: 'Test Medical Store', city: 'Surat' };
const LABELS = { demo: 'Book my free demo', quote: 'Get Enterprise pricing', checklist: 'Send me the checklist' };

async function checkDemoForm( page ) {
	await setViewport( page, DESKTOP );
	const form = page.locator( '#demo-form' );
	const submit = form.locator( '[type=submit]' );
	const required = await form.evaluate( ( f ) => [ ...f.querySelectorAll( 'input[required]' ) ].map( ( i ) => ( { id: i.id, name: i.name, pattern: !! i.pattern } ) ) );
	assert( required.length > 0, '#demo-form has no required inputs' );
	const state = () => page.evaluate( () => {
		const f = document.getElementById( 'demo-form' );
		const ok = document.getElementById( 'form-success' );
		return {
			fields: [ ...f.querySelectorAll( 'input[required]' ) ].map( ( i ) => ( { id: i.id, invalid: i.closest( '.fld' ).classList.contains( 'invalid' ), aria: i.getAttribute( 'aria-invalid' ) } ) ),
			focus: document.activeElement?.id || document.activeElement?.tagName,
			formHidden: f.hidden, success: !! ok && ! ok.hidden, successFocus: document.activeElement === ok,
			label: document.getElementById( 'submit-label' )?.textContent.trim() ?? null,
		};
	} );
	await center( form );
	await page.waitForTimeout( 300 ); // lazy images near the form settle before the watch starts

	// Intent select drives the submit label.
	const notes = [];
	const want = form.locator( '#f-want' );
	if ( await want.count() ) {
		const values = await want.evaluate( ( s ) => [ ...s.options ].map( ( o ) => o.value ) );
		const start = await want.inputValue();
		for ( const v of [ ...values.filter( ( x ) => x !== start && LABELS[ x ] ), start ] ) {
			await want.selectOption( v );
			const label = ( await state() ).label;
			assert( ! LABELS[ v ] || label === LABELS[ v ], `"I’d like to" = ${ v } shows "${ label }", expected "${ LABELS[ v ] }"` );
		}
		notes.push( 'intent sets the button label' );

		// Same-page CTAs ([data-cta], href="#…") set the intent, one per distinct value.
		const ctas = await page.evaluate( () => {
			const sel = document.getElementById( 'f-want' );
			const opts = [ ...sel.options ].map( ( o ) => o.value );
			const start = sel.value;
			const set = ( v ) => { sel.value = v; sel.dispatchEvent( new Event( 'change' ) ); };
			const out = [];
			const seen = new Set();
			for ( const a of document.querySelectorAll( 'a[data-cta]:not([data-focus-lead])' ) ) {
				const v = a.getAttribute( 'data-cta' );
				if ( seen.has( v ) || ! opts.includes( v ) || ! ( a.getAttribute( 'href' ) || '' ).startsWith( '#' ) ) {
					continue;
				}
				seen.add( v );
				set( opts.find( ( o ) => o !== v ) ); // start from another intent so the click has to change it
				a.click();
				out.push( { v, got: sel.value, label: document.getElementById( 'submit-label' )?.textContent.trim() ?? null } );
			}
			set( start );
			return out;
		} );
		for ( const c of ctas ) {
			assert( c.got === c.v && ( ! LABELS[ c.v ] || c.label === LABELS[ c.v ] ),
				`a data-cta="${ c.v }" link set the intent to "${ c.got }" with the button "${ c.label }"` );
		}
		if ( ctas.length ) {
			notes.push( `same-page CTAs set the intent (${ ctas.map( ( c ) => c.v ).join( ', ' ) })` );
		}
	}

	for ( const f of required ) {
		await form.locator( `[id="${ f.id }"]` ).fill( '' );
	}
	const net = await watchRequests( page, async () => {
		await clickCentered( submit );
		let s = await state();
		const missing = s.fields.filter( ( x ) => ! x.invalid || x.aria !== 'true' ).map( ( x ) => '#' + x.id );
		assert( missing.length === 0, `empty submit: no error on ${ missing.join( ', ' ) }` );
		assert( s.focus === required[ 0 ].id && ! s.formHidden && ! s.success, `empty submit: focus on ${ s.focus }, form hidden=${ s.formHidden }, thank-you shown=${ s.success }` );
		notes.push( `empty → ${ s.fields.length } errors, focus on #${ s.focus }` );

		const phoneField = required.find( ( x ) => x.pattern ) || null;
		for ( const f of required ) {
			const v = f === phoneField ? '12345' : ( VALID[ f.name ] || 'Test' );
			await form.locator( `[id="${ f.id }"]` ).fill( v );
		}
		if ( phoneField ) {
			await clickCentered( submit );
			s = await state();
			const wrong = s.fields.filter( ( x ) => x.invalid !== ( x.id === phoneField.id ) ).map( ( x ) => '#' + x.id );
			assert( wrong.length === 0 && s.focus === phoneField.id && ! s.success, `bad mobile: wrong error state on ${ wrong.join( ', ' ) || 'none' }, focus on ${ s.focus }` );
			notes.push( 'bad mobile → phone error only' );
			await form.locator( `[id="${ phoneField.id }"]` ).fill( VALID.phone );
		}
		await clickCentered( submit );
		s = await state();
		assert( s.formHidden && s.success && s.successFocus, `valid submit: form hidden=${ s.formHidden }, thank-you shown=${ s.success }, focused=${ s.successFocus }` );
		notes.push( 'valid → form hidden, thank-you focused' );
	} );
	const bad = sentProblems( net );
	assert( bad.length === 0, bad.join( '; ' ) );
	return `${ notes.join( '; ' ) }; nothing sent${ net.incidental ? ` (${ net.incidental } incidental image/font request(s))` : '' }`;
}

/** ?want=quote (the pricing page's "Talk to us" link) preselects the intent; the query string must survive routing. */
async function checkWantParam( page, url ) {
	await setViewport( page, DESKTOP );
	const target = withQuery( url, 'want', 'quote' );
	const res = await page.goto( target, { waitUntil: 'load', timeout: 30_000 } );
	assert( res && res.status() < 400, `${ target } returned HTTP ${ res && res.status() }` );
	assert( new URL( page.url() ).searchParams.get( 'want' ) === 'quote', `?want=quote was dropped (landed on ${ page.url() })` );
	const s = await page.evaluate( () => ( {
		want: document.getElementById( 'f-want' )?.value ?? null,
		label: document.getElementById( 'submit-label' )?.textContent.trim() ?? null,
	} ) );
	assert( s.want === 'quote' && s.label === LABELS.quote, `with ?want=quote the intent is "${ s.want }" and the button reads "${ s.label }"` );
	return `?want=quote preselects "quote"; the button reads "${ s.label }"`;
}

// ---------------------------------------------------------------------------
// Runner
// ---------------------------------------------------------------------------

/**
 * Run every interaction check that applies to url.
 *
 * @param {import('playwright-core').Page} page
 * @param {string} url  A local URL (localhost / 192.168.x.x).
 * @param {{ status?: number }} [options]  status: the expected HTTP status
 *        (pages.json "status", e.g. 404 for the 404 page). Default: any status below 400.
 * @returns {Promise<{ name: string, ok: boolean, detail: string }[]>}
 */
export async function runInteractions( page, url, options = {} ) {
	const expectStatus = Number.isInteger( options.status ) ? options.status : null;
	const results = [];
	const run = async ( name, fn ) => {
		try {
			results.push( { name, ok: true, detail: ( await fn() ) || '' } );
		} catch ( e ) {
			results.push( { name, ok: false, detail: String( e && e.message || e ).split( '\n' )[ 0 ] } );
		}
	};

	if ( ! isLocalUrl( url ) || ! /^https?:/.test( url ) ) {
		return [ { name: 'page: local URL', ok: false, detail: `refusing to open ${ url } (local hosts only)` } ];
	}

	const blocked = await guardLocal( page );
	const blockedBefore = blocked.length;
	const errors = [];
	const onError = ( e ) => errors.push( String( e && e.message || e ).split( '\n' )[ 0 ] );
	page.on( 'pageerror', onError );
	const viewport0 = page.viewportSize();
	let reduce0 = null;

	try {
		await setViewport( page, DESKTOP );
		let res;
		try {
			res = await page.goto( url, { waitUntil: 'load', timeout: 30_000 } );
		} catch ( e ) {
			results.push( { name: 'page: loads', ok: false, detail: String( e.message ).split( '\n' )[ 0 ] } );
			return results;
		}
		const status = res ? res.status() : null;
		if ( expectStatus !== null ? status !== expectStatus : ( status === null || status >= 400 ) ) {
			results.push( { name: 'page: loads', ok: false, detail: `HTTP ${ status ?? 'no response' }${ expectStatus !== null ? `, expected ${ expectStatus }` : '' }` } );
			return results;
		}
		await page.waitForLoadState( 'networkidle', { timeout: 5000 } ).catch( () => {} );
		await page.evaluate( () => document.fonts && document.fonts.ready ).catch( () => {} );
		reduce0 = await page.evaluate( () => matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
		const has = await detect( page );

		const tidy = () => closeAllMenus( page ).catch( () => {} );
		if ( has.menus ) {
			await run( 'menus: open and close on click', () => checkMegaClick( page ) );
			await tidy();
			await run( 'menus: one at a time, click outside closes', () => checkMegaOutside( page ) );
			await tidy();
			await run( 'menus: Enter opens, Escape closes', () => checkMegaKeyboard( page ) );
			await tidy();
		}
		if ( has.burger ) {
			await run( 'mobile menu: burger at 375px', () => checkBurger( page ) );
			await tidy();
		}
		if ( has.tablists ) {
			await run( 'tabs: arrow, Home and End keys', () => checkTabsKeyboard( page, has.scrollTabs ) );
		}
		if ( has.scrollTabs ) {
			await run( 'tabs: follow the scroll at 1440px', () => checkScrollTabs( page ) );
		}
		if ( has.faq ) {
			await run( 'faq: aria-expanded toggles', () => checkFaq( page ) );
		}
		if ( has.estimator ) {
			await run( 'estimator: outputs follow the sliders', () => checkEstimator( page ) );
		}
		if ( has.terms ) {
			await run( 'pricing: term switch updates .term-total', () => checkTermSwitch( page ) );
		}
		if ( has.collapse ) {
			await run( 'pricing: group and Expand-all toggles', () => checkCompareTable( page ) );
		}
		if ( has.progress ) {
			await run( 'blog: reading progress bar', () => checkProgress( page ) );
		}
		if ( has.toc ) {
			await run( 'blog: table of contents', () => checkToc( page ) );
		}
		if ( has.copy ) {
			await run( 'blog: copy link', () => checkCopyLink( page ) );
		}
		if ( has.print ) {
			await run( 'blog: print checklist', () => checkPrint( page ) );
		}
		if ( has.tour ) {
			await run( 'tour: play and pause', () => checkTourPlayPause( page ) );
			await run( 'tour: full screen', () => checkTourFullscreen( page ) );
		}
		if ( has.slip ) {
			await run( 'forms: demo slip validates, sends nothing', () => checkSlip( page ) );
		}
		if ( has.demoForm ) {
			await run( 'forms: demo form validates, sends nothing', () => checkDemoForm( page ) );
		}
		// Checks that reload the page come last.
		if ( has.wantQuote ) {
			await run( 'forms: ?want=quote preselects the intent', () => checkWantParam( page, url ) );
		}
		if ( has.tour ) {
			await run( 'tour: ?tour-at=15 freezes the frame', () => checkTourAt( page, url ) );
			await run( 'tour: autoplay rules', () => checkTourAutoplay( page, url ) );
		}
		if ( ! results.length ) {
			results.push( { name: 'page: interactions', ok: true, detail: 'no interactive elements found' } );
		}
	} finally {
		page.off( 'pageerror', onError );
		if ( reduce0 !== null ) {
			await page.emulateMedia( { reducedMotion: reduce0 ? 'reduce' : 'no-preference' } ).catch( () => {} );
		}
		if ( viewport0 ) {
			await page.setViewportSize( viewport0 ).catch( () => {} );
		}
	}

	results.push( {
		name: 'page: no script errors during the checks', ok: errors.length === 0,
		detail: errors.length ? errors.slice( 0, 3 ).join( ' | ' ) : '',
	} );
	const outside = blocked.slice( blockedBefore );
	results.push( {
		name: 'network: no outside requests', ok: outside.length === 0,
		detail: outside.length ? `blocked ${ outside.length }: ${ [ ...new Set( outside.map( ( u ) => new URL( u ).host ) ) ].join( ', ' ) }` : '',
	} );
	return results;
}

// ---------------------------------------------------------------------------
// CLI
// ---------------------------------------------------------------------------

const HERE = fileURLToPath( import.meta.url );
const isMain = process.argv[ 1 ] && path.resolve( process.argv[ 1 ] ).toLowerCase() === HERE.toLowerCase();

if ( isMain ) {
	const args = process.argv.slice( 2 );
	const json = args.includes( '--json' );
	const headed = args.includes( '--headed' );
	const urls = args.filter( ( a ) => ! a.startsWith( '--' ) );
	if ( ! urls.length ) {
		console.error( 'Usage: node parity/interactions.mjs [--json] [--headed] <url> [<url> ...]' );
		process.exit( 2 );
	}
	const remote = urls.filter( ( u ) => ! isLocalUrl( u ) || ! /^https?:/.test( u ) );
	if ( remote.length ) {
		console.error( `Local URLs only (localhost, 127.0.0.1, 192.168.x.x): ${ remote.join( ', ' ) }` );
		process.exit( 2 );
	}

	// Expected statuses from pages.json (only the 404 page has one today).
	const statusOf = new Map();
	try {
		const { readFile } = await import( 'node:fs/promises' );
		const list = JSON.parse( await readFile( path.join( path.dirname( HERE ), 'pages.json' ), 'utf8' ) ).pages || [];
		for ( const p of list ) {
			if ( Number.isInteger( p.status ) ) {
				[ p.php, p.wp ].filter( Boolean ).forEach( ( u ) => statusOf.set( u, p.status ) );
			}
		}
	} catch {
		// pages.json is optional for manual runs.
	}

	const { chromium } = await import( 'playwright-core' );
	const browser = await chromium.launch( { channel: 'msedge', headless: ! headed } );
	const context = await browser.newContext( { viewport: DESKTOP, serviceWorkers: 'block' } );
	await guardLocal( context );
	const all = {};
	let failed = 0;
	try {
		for ( const url of urls ) {
			const page = await context.newPage();
			const t0 = Date.now();
			const res = await runInteractions( page, url, statusOf.has( url ) ? { status: statusOf.get( url ) } : {} );
			await page.close();
			all[ url ] = res;
			failed += res.filter( ( r ) => ! r.ok ).length;
			if ( ! json ) {
				console.log( `\n${ url }  (${ ( ( Date.now() - t0 ) / 1000 ).toFixed( 1 ) } s)` );
				for ( const r of res ) {
					console.log( `  ${ r.ok ? 'PASS' : 'FAIL' }  ${ r.name }${ r.detail ? ` — ${ r.detail }` : '' }` );
				}
			}
		}
	} finally {
		await browser.close();
	}
	if ( json ) {
		console.log( JSON.stringify( all, null, 2 ) );
	} else {
		console.log( `\n${ failed ? `${ failed } check(s) failed` : 'All checks passed' }` );
	}
	process.exitCode = failed ? 1 : 0;
}
