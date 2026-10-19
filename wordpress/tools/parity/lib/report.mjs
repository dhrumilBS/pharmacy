// Markdown report for parity/check.mjs.

export const CHECK_COLUMNS = [
	[ 'load', 'Status / errors' ],
	[ 'notices', 'PHP notices' ],
	[ 'pixels', 'Pixel diff % (1440 / 375)' ],
	[ 'content', 'Content (text / other)' ],
	[ 'overflow', 'Overflow' ],
	[ 'meta', 'Title, desc, robots' ],
	[ 'dupmeta', 'Duplicate meta' ],
	[ 'schema', 'Schema' ],
	[ 'healthray', 'Healthray' ],
	[ 'links', 'Links' ],
	[ 'network', 'Network' ],
	[ 'interactions', 'Interactions' ],
];

const mark = ( c ) => ( ! c || c.pass === null ? `n/a ${ c && c.summary && c.summary !== 'n/a' ? c.summary : '' }`.trim() : `${ c.pass ? 'ok' : 'FAIL' } ${ c.summary }`.trim() );
const esc = ( s ) => String( s ).replace( /\|/g, '\\|' ).replace( /\n/g, ' ' );

export function buildReport( { results, args, started, notes, externalLinks, debugNote } ) {
	const L = [];
	const when = new Date().toISOString().replace( 'T', ' ' ).slice( 0, 19 );
	L.push( '# Parity report: PHP site vs WordPress site', '' );
	L.push( `Generated ${ when } (run time ${ Math.round( ( Date.now() - started ) / 1000 ) } s). Pages checked: ${ results.length }.` );
	L.push( `PHP site: ${ args.phpBase }  |  WordPress site: ${ args.wpBase }`, '' );

	L.push( '## Summary by check', '', '| Check | Pass | Fail | n/a |', '|---|---|---|---|' );
	for ( const [ key, label ] of CHECK_COLUMNS ) {
		const c = results.map( ( r ) => r.checks[ key ] );
		L.push( `| ${ label } | ${ c.filter( ( x ) => x && x.pass === true ).length } | ${ c.filter( ( x ) => x && x.pass === false ).length } | ${ c.filter( ( x ) => ! x || x.pass === null ).length } |` );
	}
	L.push( '' );

	L.push( '## Per-page results', '', `| Page | ${ CHECK_COLUMNS.map( ( c ) => c[ 1 ] ).join( ' | ' ) } |`, `|---|${ CHECK_COLUMNS.map( () => '---' ).join( '|' ) }|` );
	for ( const r of results ) {
		L.push( `| ${ r.name } | ${ CHECK_COLUMNS.map( ( [ k ] ) => esc( mark( r.checks[ k ] ) ) ).join( ' | ' ) } |` );
	}
	L.push( '' );

	L.push( '## Differences', '' );
	let any = false;
	for ( const r of results ) {
		const real = r.diffs.filter( ( d ) => ! d.check.endsWith( '-note' ) );
		if ( ! real.length ) {
			continue;
		}
		any = true;
		L.push( `### ${ r.name }`, '', `PHP ${ r.php || '(none)' }  |  WP ${ r.wp }`, '' );
		const groups = new Map();
		real.forEach( ( d ) => groups.set( d.check, [ ...( groups.get( d.check ) || [] ), d.text ] ) );
		for ( const [ check, texts ] of groups ) {
			L.push( `- **${ check }**` );
			texts.forEach( ( t ) => L.push( `  - ${ esc( t ) }` ) );
		}
		L.push( '' );
	}
	if ( ! any ) {
		L.push( 'No differences found.', '' );
	}

	L.push( '## Notes (not failures)', '' );
	let anyNote = false;
	for ( const r of results ) {
		const n = r.diffs.filter( ( d ) => d.check.endsWith( '-note' ) );
		if ( n.length ) {
			anyNote = true;
			L.push( `- **${ r.name }**` );
			n.forEach( ( d ) => L.push( `  - ${ d.check.replace( '-note', '' ) }: ${ esc( d.text ) }` ) );
		}
	}
	if ( ! anyNote ) {
		L.push( 'None.' );
	}
	L.push( '' );

	L.push( '## External links (listed only, never requested)', '' );
	const hosts = Object.entries( externalLinks ).sort( ( a, b ) => b[ 1 ].size - a[ 1 ].size );
	if ( ! hosts.length ) {
		L.push( 'None.' );
	}
	for ( const [ host, urls ] of hosts ) {
		L.push( `- ${ host } (${ urls.size } distinct): ${ [ ...urls ].slice( 0, 6 ).join( ', ' ) }${ urls.size > 6 ? ', …' : '' }` );
	}
	L.push( '' );

	L.push( '## How the comparison was normalised', '' );
	notes.forEach( ( n ) => L.push( `- ${ n }` ) );
	L.push( `- ${ debugNote }`, '' );
	L.push( `Screenshots and diff images: \`${ args.outRel }/parity/\` (name-php-1440.png, name-wp-1440.png, name-diff-1440.png, and the same at 375).`, '' );
	return L.join( '\n' );
}
