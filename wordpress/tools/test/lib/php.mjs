// PHP helpers for the Node tests.
//
//   callPhp( calls )         Call the theme's pure rx_* functions through
//                            test/php/bridge.php, one PHP process per batch.
//   runPhp( script, input )  Run any PHP script with input on STDIN.
//
// PHP is D:\xampp\php\php.exe unless the RX_PHP environment variable names
// another binary. Paths are resolved from this file, not from the cwd.

import { spawn } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = path.dirname( fileURLToPath( import.meta.url ) );

/** PHP CLI binary. */
export const PHP_BIN = process.env.RX_PHP || 'D:\\xampp\\php\\php.exe';

/** Folder holding the PHP test scripts (bridge.php, oracle.php, wp-*.php). */
export const PHP_DIR = path.resolve( HERE, '../php' );

/** The bridge used by callPhp(). */
export const BRIDGE = path.join( PHP_DIR, 'bridge.php' );

// Errors go to STDERR (STDOUT stays clean for JSON) and are not written to
// XAMPP's php_error_log.
const PHP_FLAGS = [ '-d', 'display_errors=stderr', '-d', 'log_errors=0', '-d', 'html_errors=0' ];

/**
 * Run a PHP script and collect its output.
 *
 * @param {string} script  Absolute path, or a path relative to test/php/.
 * @param {string|Buffer|object} [input]  Written to STDIN. Anything that is
 *        not a string or Buffer is sent as JSON.
 * @param {object} [options]
 * @param {string[]} [options.args]  Extra arguments passed to the script.
 * @param {object} [options.env]     Extra environment variables.
 * @param {number} [options.timeout] Milliseconds before the process is killed (default 60000).
 * @returns {Promise<{ code: number|null, signal: string|null, stdout: string, stderr: string }>}
 *          Resolves whatever the exit code; rejects only when PHP cannot be
 *          started or the timeout is reached.
 */
export function runPhp( script, input = '', options = {} ) {
	const { args = [], env = {}, timeout = 60_000 } = options;
	const file = path.isAbsolute( script ) ? script : path.resolve( PHP_DIR, script );
	const data = typeof input === 'string' || Buffer.isBuffer( input ) ? input : JSON.stringify( input );

	return new Promise( ( resolve, reject ) => {
		const child = spawn( PHP_BIN, [ ...PHP_FLAGS, file, ...args ], {
			cwd: PHP_DIR,
			env: { ...process.env, ...env },
			stdio: [ 'pipe', 'pipe', 'pipe' ],
			windowsHide: true,
		} );

		const out = [];
		const err = [];
		let timedOut = false;
		const timer = setTimeout( () => {
			timedOut = true;
			child.kill();
		}, timeout );

		child.stdout.on( 'data', ( chunk ) => out.push( chunk ) );
		child.stderr.on( 'data', ( chunk ) => err.push( chunk ) );
		child.on( 'error', ( e ) => {
			clearTimeout( timer );
			reject( new Error( `Could not start PHP (${ PHP_BIN }): ${ e.message }. Set RX_PHP to the PHP CLI path.`, { cause: e } ) );
		} );
		child.on( 'close', ( code, signal ) => {
			clearTimeout( timer );
			const stdout = Buffer.concat( out ).toString( 'utf8' );
			const stderr = Buffer.concat( err ).toString( 'utf8' );
			if ( timedOut ) {
				reject( new Error( `PHP timed out after ${ timeout } ms: ${ file }\n${ stderr }` ) );
				return;
			}
			resolve( { code, signal, stdout, stderr } );
		} );

		// EPIPE when PHP exits before reading STDIN is reported by 'close'.
		child.stdin.on( 'error', () => {} );
		child.stdin.end( data );
	} );
}

/**
 * Call rx_* functions from themes/pharmacy/inc/core/ in one PHP process.
 *
 * @param {{ fn: string, args?: unknown[] }[]} calls
 * @returns {Promise<({ ok: true, value: unknown, output?: string } | { ok: false, error: string })[]>}
 *          One result per call, in order. A failing call does not fail the
 *          batch; the promise rejects only when the bridge itself fails.
 */
export async function callPhp( calls ) {
	if ( ! Array.isArray( calls ) ) {
		throw new TypeError( 'callPhp() expects an array of { fn, args } calls.' );
	}
	if ( calls.length === 0 ) {
		return [];
	}

	const { code, stdout, stderr } = await runPhp( BRIDGE, JSON.stringify( calls ) );
	if ( code !== 0 ) {
		throw new Error( `PHP bridge exited with code ${ code }.\n${ stderr || stdout }` );
	}

	let results;
	try {
		results = JSON.parse( stdout );
	} catch ( e ) {
		throw new Error( `PHP bridge printed invalid JSON: ${ e.message }\n${ stdout.slice( 0, 500 ) }\n${ stderr }`, { cause: e } );
	}
	if ( ! Array.isArray( results ) || results.length !== calls.length ) {
		throw new Error( `PHP bridge returned ${ Array.isArray( results ) ? results.length : typeof results } results for ${ calls.length } calls.` );
	}
	return results;
}
