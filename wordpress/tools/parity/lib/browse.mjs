// Browser-side helpers for parity/check.mjs: visit one URL, record everything
// the checks need, and compare two screenshots with pixelmatch.

import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { isLocalUrl } from '../interactions.mjs';
import { extractPage, settle } from './extract.mjs';

export const SHOT_WIDTHS = [ 1440, 375 ];
export const OVERFLOW_WIDTHS = [ 360, 375, 390, 414, 768, 1024 ];

export function withTourAt( url, motion ) {
	if ( ! motion ) {
		return url;
	}
	return url + ( url.includes( '?' ) ? '&' : '?' ) + 'tour-at=15';
}

/**
 * Open url, wait for it to settle and collect: status, console/page errors,
 * failed local requests, every request host, extracted page data, screenshots
 * (1440 and 375) and the horizontal-overflow measurements.
 */
export async function visit( context, url, opts ) {
	const { motion, outFile } = opts;
	const out = {
		url, status: null, consoleErrors: [], pageErrors: [], failedLocal: [], hosts: new Set(),
		data: null, shots: {}, overflow: {}, error: null,
	};
	if ( ! isLocalUrl( url ) ) {
		out.error = 'refused: not a local URL';
		return out;
	}
	const page = await context.newPage();
	try {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.setViewportSize( { width: 1440, height: 900 } );
		const target = withTourAt( url, motion );
		page.on( 'console', ( m ) => {
			if ( m.type() === 'error' && ! /^Failed to load resource/.test( m.text() ) ) {
				out.consoleErrors.push( m.text().split( '\n' )[ 0 ].slice( 0, 160 ) );
			}
		} );
		page.on( 'pageerror', ( e ) => out.pageErrors.push( String( e && e.message || e ).split( '\n' )[ 0 ].slice( 0, 160 ) ) );
		page.on( 'request', ( r ) => {
			try {
				out.hosts.add( new URL( r.url() ).host || r.url().slice( 0, 12 ) );
			} catch {
				out.hosts.add( r.url().slice( 0, 30 ) );
			}
		} );
		page.on( 'requestfailed', ( r ) => {
			const err = ( r.failure() || {} ).errorText || '';
			if ( isLocalUrl( r.url() ) && ! /ERR_ABORTED|blockedbyclient/i.test( err ) ) {
				out.failedLocal.push( `${ r.url() } (${ err })` );
			}
		} );
		page.on( 'response', ( r ) => {
			if ( isLocalUrl( r.url() ) && r.status() >= 400 && r.request().resourceType() !== 'document' ) {
				out.failedLocal.push( `${ r.url() } (HTTP ${ r.status() })` );
			}
		} );

		const res = await page.goto( target, { waitUntil: 'load', timeout: 60000 } );
		out.status = res ? res.status() : null;
		out.finalUrl = page.url();
		await page.waitForLoadState( 'networkidle', { timeout: 6000 } ).catch( () => {} );
		await settle( page );
		out.data = await page.evaluate( extractPage );

		for ( const w of SHOT_WIDTHS ) {
			await page.setViewportSize( { width: w, height: w === 375 ? 812 : 900 } );
			await page.waitForTimeout( 350 );
			await settle( page );
			const file = outFile( w );
			try {
				await page.screenshot( { path: file, fullPage: true, animations: 'disabled' } );
			} catch {
				await page.screenshot( { path: file, clip: { x: 0, y: 0, width: w, height: 15000 }, animations: 'disabled' } );
			}
			out.shots[ w ] = file;
		}
		for ( const w of OVERFLOW_WIDTHS ) {
			await page.setViewportSize( { width: w, height: 900 } );
			await page.waitForTimeout( 200 );
			out.overflow[ w ] = await page.evaluate( () => Math.max( document.documentElement.scrollWidth, document.body ? document.body.scrollWidth : 0 ) );
		}
	} catch ( e ) {
		out.error = String( e && e.message || e ).split( '\n' )[ 0 ];
	} finally {
		await page.close().catch( () => {} );
	}
	return out;
}

/** Percentage of differing pixels. Images are padded to the larger size (white). */
export async function pixelDiff( fileA, fileB, diffFile ) {
	const { PNG } = await import( 'pngjs' );
	const pixelmatch = ( await import( 'pixelmatch' ) ).default;
	const a = PNG.sync.read( await readFile( fileA ) );
	const b = PNG.sync.read( await readFile( fileB ) );
	const w = Math.max( a.width, b.width );
	const h = Math.max( a.height, b.height );
	const pad = ( img ) => {
		const p = new PNG( { width: w, height: h } );
		p.data.fill( 255 );
		PNG.bitblt( img, p, 0, 0, img.width, img.height, 0, 0 );
		return p;
	};
	const pa = pad( a );
	const pb = pad( b );
	const diff = new PNG( { width: w, height: h } );
	const n = pixelmatch( pa.data, pb.data, diff.data, w, h, { threshold: 0.1 } );
	await mkdir( path.dirname( diffFile ), { recursive: true } );
	await writeFile( diffFile, PNG.sync.write( diff ) );
	return { percent: ( n / ( w * h ) ) * 100, heightA: a.height, heightB: b.height, width: w };
}
