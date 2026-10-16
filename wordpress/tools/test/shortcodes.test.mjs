// Example tests for the shortcodes in themes/pharmacy/inc/shortcodes.php
// (task 7.9).
//
// Each case is rendered by do_shortcode() (or rx_text()/rx_html()) inside the
// local WordPress install through test/php/wp-shortcodes.php, read-only: DB
// writes and every outbound request are blocked and reported. One PHP
// process per pricing mode:
//
// - "snapshot": pricing is the committed data/pricing.json. Every pricing
//   shortcode must equal esc_html() of the matching core helper, computed
//   by the same PHP process.
// - "null": rx_pricing_data() is forced to null. Pricing shortcodes give ''
//   or their fallback and every [rx_if] block stays hidden.
//
// Site_Settings and page URLs are compared with rx_setting() and rx_url()
// from the same process, so the tests hold before and after the import.
//
// Requirements: 6.8

import { before, describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { runPhp } from './lib/php.mjs';

// Feature unique_names with each kind of plan label in the snapshot:
// "Every plan", "Basic and up", "Advance and up", "Enterprise", a listed
// feature no plan includes (null), and a name the API doesn't list (null).
const FEATS = [
	'feat_sale_bill_limit',
	'feat_role_management',
	'feat_sale_draft',
	'feat_central_admin',
	'feat_pharmacist_verification',
	'feat_not_listed',
];

const URL_KEYS = [ 'pricing', 'demo', 'home', 'retail', 'not-a-page' ];

// Shortcode tag => key in ref.helpers (core helper of the same name).
const PRICING_TEXT = {
	rx_cost_text: 'cost_text',
	rx_compare_text: 'compare_text',
	rx_terms_text: 'terms_text',
	rx_users_text: 'users_text',
	rx_credits_text: 'credits_text',
};

const FREE_FIELDS = [ 'name', 'sales', 'purchase', 'users', 'users_text' ];
const CONDS = [ 'pricing', 'free', 'no_free', 'free_sales_limit', 'free_purchase_limit' ];

// The pricing FAQ answer from design §13, as stored in the data file.
const FAQ_LIMIT =
	'[rx_if cond="free_sales_limit"][rx_free_plan field="name"] includes [rx_free_plan field="sales"] sales bills'
	+ '[rx_free_plan field="purchase" before=" and " after=" purchase bills"] a month for [rx_free_plan field="users_text"].'
	+ ' When you need more, upgrade to a paid plan for unlimited bills.[/rx_if]';

// id => code, or [ code, via ]. The same cases run in both modes.
const CASES = {
	// Brand, settings and links.
	brand: '[rx_brand]',
	'site address': '[rx_site key="address"]',
	'site company': '[rx_site key="company"]',
	'site sales_phone': '[rx_site key="sales_phone"]',
	'site support_phone': '[rx_site key="support_phone"]',
	'site app_login': '[rx_site key="app_login"]',
	'site app_signup': '[rx_site key="app_signup"]',
	'site help_url': '[rx_site key="help_url"]',
	'site calendly': '[rx_site key="calendly"]',
	'site pricing_api': '[rx_site key="pricing_api"]',
	'site social': '[rx_site key="social"]',
	'site hero_pattern': '[rx_site key="hero_pattern"]',
	'site no key': '[rx_site]',
	'tel sales big-link': '[rx_tel key="sales" class="big-link"]',
	'tel support': '[rx_tel key="support"]',
	'tel style': '[rx_tel key="sales" style="color:#fff"]',
	'tel content': '[rx_tel key="sales"]Call [rx_brand][/rx_tel]',
	'tel unknown key': '[rx_tel key="fax"]',
	...Object.fromEntries( URL_KEYS.map( ( k ) => [ `url ${ k }`, `[rx_url key="${ k }"]` ] ) ),
	'url hash': '[rx_url key="pricing" hash="compare"]',
	'url hash replaces anchor': '[rx_url key="retail" hash="#switching"]',
	'url no key': '[rx_url]',
	'link pricing compare': '[rx_link key="pricing" hash="compare" class="x"]plans[/rx_link]',
	'link setting': '[rx_link setting="app_signup" class="btn btn-primary" cta="signup"]Start free[/rx_link]',
	'link setting wins': '[rx_link key="pricing" setting="app_login"]Log in[/rx_link]',
	'link attribute order': '[rx_link key="demo" class="c" style="color:#fff" rel="noopener" cta="demo"]book a demo[/rx_link]',
	'link nested': '[rx_link key="pricing"]See [rx_brand] plans from [rx_free_plan field="name" fallback="free"][/rx_link]',
	'link not a url setting': '[rx_link setting="pricing_api"]API[/rx_link]',
	'link no target': '[rx_link]plain text[/rx_link]',
	'link empty': '[rx_link key="pricing"][/rx_link]',

	// Pricing.
	...Object.fromEntries( Object.keys( PRICING_TEXT ).map( ( tag ) => [ tag, `[${ tag }]` ] ) ),
	...Object.fromEntries( FEATS.map( ( f ) => [ `plan ${ f }`, `[rx_plan feat="${ f }"]` ] ) ),
	...Object.fromEntries( FEATS.map( ( f ) => [ `plan ${ f } wrapped`, `[rx_plan feat="${ f }" fallback="higher plans" before=" (" after=")"]` ] ) ),
	'plan no feat': '[rx_plan fallback="every plan"]',
	...Object.fromEntries( FREE_FIELDS.map( ( f ) => [ `free ${ f }`, `[rx_free_plan field="${ f }"]` ] ) ),
	'free default field': '[rx_free_plan]',
	'free purchase wrapped': '[rx_free_plan field="purchase" before=" and " after=" purchase bills"]',
	'free unknown field': '[rx_free_plan field="price" fallback="n/a"]',
	'free fallback': '[rx_free_plan field="name" fallback="the free plan"]',
	...Object.fromEntries( CONDS.map( ( c ) => [ `if ${ c }`, `[rx_if cond="${ c }"]shown[/rx_if]` ] ) ),
	'if unknown': '[rx_if cond="always"]shown[/rx_if]',
	'if empty': '[rx_if cond="pricing"][/rx_if]',
	'if faq answer': FAQ_LIMIT,

	// Unregistered bracketed text.
	placeholder: '[placeholder]',
	'company placeholder': '[Company legal name]',
	'unknown rx shortcode': '[rx_unknown]',
	'unknown enclosing': '[rx_unknown key="x"]text[/rx_unknown]',
	'placeholder beside shortcode': 'Signed by [Company legal name] for [rx_brand], [Registered address].',

	// Through the print helpers.
	'rx_text cost': [ 'Plans: [rx_cost_text]', 'rx_text' ],
	'rx_html tel': [ '<p>Call [rx_tel key="sales" class="big-link"].</p>', 'rx_html' ],
	'rx_html link': [ '<p>[rx_link key="pricing" hash="compare" cta="pricing"]Compare plans[/rx_link] [Company legal name]</p>', 'rx_html' ],
};

const CASE_LIST = Object.entries( CASES ).map( ( [ id, v ] ) =>
	Array.isArray( v ) ? { id, code: v[ 0 ], via: v[ 1 ] } : { id, code: v }
);

/**
 * Render every case in one PHP process.
 *
 * @param {'snapshot'|'null'} mode
 */
async function render( mode ) {
	const { code, stdout, stderr } = await runPhp(
		'wp-shortcodes.php',
		{ mode, feats: FEATS, urls: URL_KEYS, cases: CASE_LIST },
		{ timeout: 120_000 }
	);
	assert.equal( code, 0, `wp-shortcodes.php (${ mode }) exited with ${ code }.\n${ stderr || stdout }` );
	let report;
	try {
		report = JSON.parse( stdout );
	} catch ( e ) {
		throw new Error( `wp-shortcodes.php (${ mode }) printed invalid JSON: ${ e.message }\n${ stdout.slice( 0, 500 ) }\n${ stderr }` );
	}
	report.byId = Object.fromEntries( report.results.map( ( r ) => [ r.id, r ] ) );
	return report;
}

/**
 * Output of one case; fails when the case raised a PHP error, echoed,
 * attempted a DB write or an outbound request.
 */
function out( report, id ) {
	const r = report.byId[ id ];
	assert.ok( r, `No result for case "${ id }"` );
	assert.equal( r.error, undefined, `PHP error in "${ id }": ${ r.error }` );
	assert.equal( r.output, undefined, `"${ id }" echoed: ${ r.output }` );
	assert.deepEqual( r.writes, [], `"${ id }" attempted DB writes` );
	assert.deepEqual( r.http, [], `"${ id }" attempted outbound requests` );
	return r.out;
}

const reports = {};

before( async () => {
	[ reports.snapshot, reports.null ] = await Promise.all( [ render( 'snapshot' ), render( 'null' ) ] );
} );

describe( 'wp-shortcodes.php environment', () => {
	for ( const mode of [ 'snapshot', 'null' ] ) {
		test( `${ mode }: pharmacy theme active, every shortcode registered`, () => {
			const r = reports[ mode ];
			assert.equal( r.theme.template, 'pharmacy' );
			for ( const [ tag, ok ] of Object.entries( r.shortcodes ) ) {
				assert.ok( ok, `[${ tag }] is not registered` );
			}
			assert.equal( Object.keys( r.shortcodes ).length, 13 );
		} );

		test( `${ mode }: no DB writes, no outbound requests, no stray output`, ( t ) => {
			const r = reports[ mode ];
			assert.deepEqual( r.gates, { query: true, pre_http_request: true, pre_transient_rx_pricing: true }, 'read-only gates are hooked' );
			if ( r.writes.bootstrap.length ) {
				t.diagnostic( `Blocked while WordPress loaded: ${ r.writes.bootstrap.join( ' | ' ) }` );
			}
			assert.deepEqual( r.writes.bootstrap, [], 'WordPress attempted DB writes while loading' );
			assert.deepEqual( r.writes.other, [], 'DB writes attempted after loading' );
			assert.deepEqual( r.http, [], 'Outbound requests attempted' );
			assert.equal( r.bootstrap_output, '' );
		} );
	}

	test( 'snapshot: pricing comes from the snapshot', () => {
		const p = reports.snapshot.pricing;
		assert.equal( p.is_null, false );
		assert.equal( p.equals_snapshot, true );
		assert.equal( p.source, 'cache' );
		assert.ok( reports.snapshot.ref.free, 'The snapshot has a free plan' );
	} );

	test( 'null: rx_pricing_data() is null', () => {
		assert.equal( reports.null.pricing.is_null, true );
		assert.equal( reports.null.ref.helpers, null );
	} );
} );

describe( 'brand, settings and links (both modes)', () => {
	for ( const mode of [ 'snapshot', 'null' ] ) {
		describe( mode, () => {
			let r;
			let esc;
			let urls;
			before( () => {
				r = reports[ mode ];
				esc = r.esc;
				urls = r.esc_url.urls;
			} );

			test( '[rx_brand] is the Site Title', () => {
				assert.equal( out( r, 'brand' ), esc.brand );
			} );

			test( '[rx_site] prints text settings', () => {
				for ( const key of [ 'address', 'company', 'sales_phone', 'support_phone' ] ) {
					assert.equal( out( r, `site ${ key }` ), esc.settings[ key ], key );
				}
				assert.notEqual( r.ref.settings.address, '', 'address setting is set' );
			} );

			test( '[rx_site] never prints URL settings or non-text values', () => {
				for ( const id of [ 'site app_login', 'site app_signup', 'site help_url', 'site calendly', 'site pricing_api', 'site social', 'site hero_pattern', 'site no key' ] ) {
					assert.equal( out( r, id ), '', id );
				}
			} );

			test( '[rx_tel] markup', () => {
				const s = r.ref.settings;
				assert.equal( out( r, 'tel sales big-link' ), `<a class="big-link" href="tel:${ s.sales_tel }">${ esc.settings.sales_phone }</a>` );
				assert.equal( out( r, 'tel support' ), `<a href="tel:${ s.support_tel }">${ esc.settings.support_phone }</a>` );
				assert.equal( out( r, 'tel style' ), `<a href="tel:${ s.sales_tel }" style="color:#fff">${ esc.settings.sales_phone }</a>` );
				assert.equal( out( r, 'tel content' ), `<a href="tel:${ s.sales_tel }">Call ${ esc.brand }</a>` );
				assert.equal( out( r, 'tel unknown key' ), '' );
			} );

			test( '[rx_url] is rx_url() of the key', () => {
				for ( const key of URL_KEYS ) {
					assert.equal( out( r, `url ${ key }` ), urls[ key ], key );
				}
				assert.equal( out( r, 'url hash' ), `${ urls.pricing.replace( /#.*$/, '' ) }#compare` );
				assert.equal( out( r, 'url hash replaces anchor' ), `${ urls.retail.replace( /#.*$/, '' ) }#switching` );
				assert.ok( urls.retail.endsWith( '#solutions' ) || ! urls.retail.includes( '#' ), 'retail falls back to /#solutions until its page exists' );
				assert.equal( out( r, 'url no key' ), '' );
			} );

			test( '[rx_link] markup', () => {
				const pricing = urls.pricing.replace( /#.*$/, '' );
				assert.equal( out( r, 'link pricing compare' ), `<a class="x" href="${ pricing }#compare">plans</a>` );
				assert.equal(
					out( r, 'link setting' ),
					`<a class="btn btn-primary" href="${ r.esc_url.settings.app_signup }" data-cta="signup">Start free</a>`
				);
				assert.equal( out( r, 'link setting wins' ), `<a href="${ r.esc_url.settings.app_login }">Log in</a>` );
				assert.equal(
					out( r, 'link attribute order' ),
					`<a class="c" href="${ urls.demo }" style="color:#fff" rel="noopener" data-cta="demo">book a demo</a>`
				);
				assert.equal( out( r, 'link not a url setting' ), 'API' );
				assert.equal( out( r, 'link no target' ), 'plain text' );
				assert.equal( out( r, 'link empty' ), '' );
			} );

			test( '[rx_link] resolves nested shortcodes', () => {
				const free = mode === 'snapshot' ? esc.free.name : 'free';
				assert.equal( out( r, 'link nested' ), `<a href="${ urls.pricing }">See ${ esc.brand } plans from ${ free }</a>` );
			} );

			test( 'unregistered bracketed text is left unchanged', () => {
				assert.equal( out( r, 'placeholder' ), '[placeholder]' );
				assert.equal( out( r, 'company placeholder' ), '[Company legal name]' );
				assert.equal( out( r, 'unknown rx shortcode' ), '[rx_unknown]' );
				assert.equal( out( r, 'unknown enclosing' ), '[rx_unknown key="x"]text[/rx_unknown]' );
				assert.equal( out( r, 'placeholder beside shortcode' ), `Signed by [Company legal name] for ${ esc.brand }, [Registered address].` );
			} );

			test( 'rx_html() keeps [rx_tel] and [rx_link] markup', () => {
				assert.equal(
					out( r, 'rx_html tel' ),
					`<p>Call <a class="big-link" href="tel:${ r.ref.settings.sales_tel }">${ esc.settings.sales_phone }</a>.</p>`
				);
				assert.equal(
					out( r, 'rx_html link' ),
					`<p><a href="${ urls.pricing.replace( /#.*$/, '' ) }#compare" data-cta="pricing">Compare plans</a> [Company legal name]</p>`
				);
			} );
		} );
	}
} );

describe( 'pricing shortcodes with the snapshot', () => {
	let r;
	let esc;
	before( () => {
		r = reports.snapshot;
		esc = r.esc;
	} );

	test( 'prose shortcodes equal esc_html() of the core helpers', () => {
		for ( const [ tag, key ] of Object.entries( PRICING_TEXT ) ) {
			assert.notEqual( r.ref.helpers[ key ], '', `${ key } is not empty` );
			assert.equal( out( r, tag ), esc.helpers[ key ], `[${ tag }]` );
		}
	} );

	test( 'rx_text() prints [rx_cost_text] the same way', () => {
		assert.equal( out( r, 'rx_text cost' ), `Plans: ${ esc.helpers.cost_text }` );
	} );

	test( '[rx_plan] equals rx_feature_plan_label(), else the fallback', () => {
		const labels = r.ref.plan_labels;
		for ( const feat of FEATS ) {
			const label = labels[ feat ];
			assert.equal( out( r, `plan ${ feat }` ), label === null ? '' : esc.plan_labels[ feat ], feat );
			assert.equal(
				out( r, `plan ${ feat } wrapped` ),
				label === null ? 'higher plans' : ` (${ esc.plan_labels[ feat ] })`,
				`${ feat } with before/after/fallback`
			);
		}
		assert.equal( out( r, 'plan no feat' ), 'every plan' );
	} );

	test( 'the chosen features cover every kind of plan label', () => {
		const labels = Object.values( r.ref.plan_labels );
		assert.ok( labels.includes( 'Every plan' ) );
		assert.ok( labels.some( ( l ) => typeof l === 'string' && l.endsWith( ' and up' ) ) );
		assert.ok( labels.includes( null ), 'an unknown feature has no label' );
		assert.ok( new Set( labels ).size >= 4 );
	} );

	test( '[rx_free_plan] fields equal the free plan facts', () => {
		const free = r.ref.free;
		for ( const field of FREE_FIELDS ) {
			const v = free[ field ];
			assert.equal( out( r, `free ${ field }` ), v === null ? '' : String( esc.free[ field ] ), field );
		}
		assert.equal( out( r, 'free default field' ), esc.free.name );
		assert.equal(
			out( r, 'free purchase wrapped' ),
			free.purchase === null ? '' : ` and ${ free.purchase } purchase bills`
		);
		assert.equal( out( r, 'free unknown field' ), 'n/a' );
		assert.equal( out( r, 'free fallback' ), esc.free.name );
	} );

	test( '[rx_if] shows content only when the condition holds', () => {
		for ( const cond of CONDS ) {
			assert.equal( out( r, `if ${ cond }` ), r.ref.conds[ cond ] ? 'shown' : '', cond );
		}
		assert.equal( r.ref.conds.free, true, 'the snapshot has a free plan, so both outcomes are covered' );
		assert.equal( out( r, 'if unknown' ), '' );
		assert.equal( out( r, 'if empty' ), '' );
	} );

	test( 'pricing FAQ answer matches the PHP site sentence', () => {
		const f = r.ref.free;
		const expected = r.ref.conds.free_sales_limit
			? `${ esc.free.name } includes ${ f.sales } sales bills`
				+ ( f.purchase !== null ? ` and ${ f.purchase } purchase bills` : '' )
				+ ` a month for ${ esc.free.users_text }. When you need more, upgrade to a paid plan for unlimited bills.`
			: '';
		assert.equal( out( r, 'if faq answer' ), expected );
	} );
} );

describe( 'pricing shortcodes when pricing is null', () => {
	let r;
	before( () => {
		r = reports.null;
	} );

	test( 'prose shortcodes give ""', () => {
		for ( const tag of Object.keys( PRICING_TEXT ) ) {
			assert.equal( out( r, tag ), '', `[${ tag }]` );
		}
		assert.equal( out( r, 'rx_text cost' ), 'Plans: ' );
	} );

	test( '[rx_plan] gives "" or its fallback, never before/after', () => {
		for ( const feat of FEATS ) {
			assert.equal( out( r, `plan ${ feat }` ), '', feat );
			assert.equal( out( r, `plan ${ feat } wrapped` ), 'higher plans', feat );
		}
		assert.equal( out( r, 'plan no feat' ), 'every plan' );
	} );

	test( '[rx_free_plan] gives "" or its fallback', () => {
		for ( const field of FREE_FIELDS ) {
			assert.equal( out( r, `free ${ field }` ), '', field );
		}
		assert.equal( out( r, 'free default field' ), '' );
		assert.equal( out( r, 'free purchase wrapped' ), '' );
		assert.equal( out( r, 'free unknown field' ), 'n/a' );
		assert.equal( out( r, 'free fallback' ), 'the free plan' );
	} );

	test( 'every [rx_if] block stays hidden', () => {
		for ( const cond of CONDS ) {
			assert.equal( out( r, `if ${ cond }` ), '', cond );
		}
		assert.equal( out( r, 'if unknown' ), '' );
		assert.equal( out( r, 'if faq answer' ), '' );
	} );
} );
