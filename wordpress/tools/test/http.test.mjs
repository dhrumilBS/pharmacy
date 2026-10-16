// Unit tests for the outbound host checks in themes/pharmacy/inc/core/http.php
// (task 1.5). Called through the PHP bridge in one PHP process.
//
// rx_host_allowed() backs the pre_http_request filter, so the only hosts the
// WordPress site may call are this PC's local hosts and the Pricing_API host.
//
// Requirements: 1.3, 1.4

import { before, describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { callPhp } from './lib/php.mjs';

// Same value as PRICING_API in the PHP site's partials/config.php. Only used
// as a string here; nothing is requested.
const API = 'https://pharmacyapi.healthray.com/api/subscription/pricing/public';

// Each group: [ fn, args, expected ].
const GROUPS = {
	'rx_is_local_host: local hosts': [
		[ 'rx_is_local_host', [ 'localhost' ], true ],
		[ 'rx_is_local_host', [ '127.0.0.1' ], true ],
		[ 'rx_is_local_host', [ '192.168.0.25' ], true ],
		[ 'rx_is_local_host', [ '192.168.0.0' ], true ],
		[ 'rx_is_local_host', [ '192.168.255.255' ], true ],
		[ 'rx_is_local_host', [ 'LocalHost' ], true ],
		[ 'rx_is_local_host', [ 'localhost.' ], true ],
	],
	'rx_is_local_host: other hosts': [
		[ 'rx_is_local_host', [ '' ], false ],
		[ 'rx_is_local_host', [ 'localhost.evil.com' ], false ],
		[ 'rx_is_local_host', [ 'evil-localhost' ], false ],
		[ 'rx_is_local_host', [ 'localhost..' ], false ],
		[ 'rx_is_local_host', [ '192.168.evil.com' ], false ],
		[ 'rx_is_local_host', [ '192.168.0.25.evil.com' ], false ],
		[ 'rx_is_local_host', [ 'evil192.168.0.25' ], false ],
		[ 'rx_is_local_host', [ '192.168.0' ], false ],
		[ 'rx_is_local_host', [ '192.168.0.256' ], false ],
		[ 'rx_is_local_host', [ '192.168.00.25' ], false ],
		[ 'rx_is_local_host', [ '192.168.0.25\n' ], false ],
		[ 'rx_is_local_host', [ '192.169.0.25' ], false ],
		[ 'rx_is_local_host', [ '10.0.0.1' ], false ],
		[ 'rx_is_local_host', [ '127.0.0.2' ], false ],
		[ 'rx_is_local_host', [ '127.1' ], false ],
		[ 'rx_is_local_host', [ '[::1]' ], false ],
		[ 'rx_is_local_host', [ '::1' ], false ],
		[ 'rx_is_local_host', [ 'api.wordpress.org' ], false ],
		[ 'rx_is_local_host', [ 'pharmacy.healthray.com' ], false ],
	],
	'rx_parse_host and rx_normalise_host': [
		[ 'rx_normalise_host', [ 'Example.COM.' ], 'example.com' ],
		[ 'rx_normalise_host', [ 'example.com..' ], 'example.com.' ],
		[ 'rx_parse_host', [ 'http://example.com:8080/path?q=1' ], 'example.com' ],
		[ 'rx_parse_host', [ 'http://localhost@evil.com/' ], 'evil.com' ],
		[ 'rx_parse_host', [ 'http://user:pass@LocalHost./x' ], 'localhost' ],
		[ 'rx_parse_host', [ '//LocalHost/x' ], 'localhost' ],
		[ 'rx_parse_host', [ 'http://[::1]:80/' ], '[::1]' ],
		[ 'rx_parse_host', [ 'http://localhost\\@evil.com/' ], '' ],
		[ 'rx_parse_host', [ ' http://localhost/' ], '' ],
		[ 'rx_parse_host', [ '/pharmacy-wp/' ], '' ],
		[ 'rx_parse_host', [ 'localhost' ], '' ],
		[ 'rx_parse_host', [ '' ], '' ],
	],
	'rx_host_allowed: allowed (local hosts and the Pricing_API host)': [
		[ 'rx_host_allowed', [ 'http://localhost/pharmacy-wp/', API ], true ],
		[ 'rx_host_allowed', [ 'http://127.0.0.1/', API ], true ],
		[ 'rx_host_allowed', [ 'http://192.168.0.25/pharmacy-wp/wp-cron.php?doing_wp_cron', API ], true ],
		[ 'rx_host_allowed', [ API, API ], true ],
		[ 'rx_host_allowed', [ 'https://PharmacyAPI.Healthray.com/other/path', API ], true ],
		[ 'rx_host_allowed', [ 'http://pharmacyapi.healthray.com:8443/', API ], true ],
		[ 'rx_host_allowed', [ 'HTTP://LOCALHOST:8080/x', API ], true ],
		[ 'rx_host_allowed', [ 'http://localhost./x', API ], true ],
		[ 'rx_host_allowed', [ '//localhost/x', API ], true ],
		[ 'rx_host_allowed', [ API, '  https://PHARMACYAPI.HEALTHRAY.COM/api/  ' ], true ],
	],
	'rx_host_allowed: blocked (task list)': [
		[ 'rx_host_allowed', [ 'http://localhost.evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://192.168.evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://localhost@evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'https://api.wordpress.org/core/version-check/1.7/', API ], false ],
		[ 'rx_host_allowed', [ 'https://pharmacy.healthray.com/', API ], false ],
	],
	'rx_host_allowed: blocked (bypass attempts)': [
		[ 'rx_host_allowed', [ 'http://pharmacyapi.healthray.com@evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://localhost:80@evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://pharmacyapi.healthray.com.evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://evil-pharmacyapi.healthray.com/', API ], false ],
		[ 'rx_host_allowed', [ 'https://healthray.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://evil.com/?host=localhost', API ], false ],
		[ 'rx_host_allowed', [ 'http://evil.com/#@localhost', API ], false ],
		[ 'rx_host_allowed', [ 'http://localhost%2eevil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://192.168.0.256/', API ], false ],
		[ 'rx_host_allowed', [ 'http://[::1]/', API ], false ],
		[ 'rx_host_allowed', [ 'http://127.1/', API ], false ],
		[ 'rx_host_allowed', [ 'http://localhost\\@evil.com/', API ], false ],
		[ 'rx_host_allowed', [ 'http://evil.com\\@localhost/', API ], false ],
		[ 'rx_host_allowed', [ 'http://evil.com\t.localhost/', API ], false ],
		[ 'rx_host_allowed', [ ' http://localhost/', API ], false ],
		[ 'rx_host_allowed', [ 'file:///C:/xampp/htdocs/', API ], false ],
		[ 'rx_host_allowed', [ 'localhost', API ], false ],
		[ 'rx_host_allowed', [ '', API ], false ],
	],
	'rx_host_allowed: no usable Pricing_API URL allows only local hosts': [
		[ 'rx_host_allowed', [ 'http://localhost/', '' ], true ],
		[ 'rx_host_allowed', [ 'http://192.168.0.25/', '' ], true ],
		[ 'rx_host_allowed', [ API, '' ], false ],
		[ 'rx_host_allowed', [ API, '   ' ], false ],
		[ 'rx_host_allowed', [ API, 'not a url' ], false ],
		[ 'rx_host_allowed', [ API, '/api/subscription/pricing/public' ], false ],
		[ 'rx_host_allowed', [ 'https://evil.com/', 'https://evil.com\\@pharmacyapi.healthray.com/' ], false ],
		[ 'rx_host_allowed', [ '', '' ], false ],
	],
};

const CASES = Object.entries( GROUPS ).flatMap( ( [ group, rows ] ) =>
	rows.map( ( [ fn, args, expected ] ) => ( { group, fn, args, expected } ) )
);

let results;

before( async () => {
	results = await callPhp( CASES.map( ( { fn, args } ) => ( { fn, args } ) ) );
} );

for ( const group of Object.keys( GROUPS ) ) {
	describe( group, () => {
		CASES.forEach( ( c, i ) => {
			if ( c.group !== group ) {
				return;
			}
			const label = `${ c.fn }( ${ c.args.map( ( a ) => JSON.stringify( a ) ).join( ', ' ) } ) === ${ JSON.stringify( c.expected ) }`;
			test( label, () => {
				const r = results[ i ];
				assert.ok( r.ok, `PHP call failed: ${ r.error }` );
				assert.equal( r.output, undefined, `Unexpected output: ${ r.output }` );
				assert.strictEqual( r.value, c.expected );
			} );
		} );
	} );
}
