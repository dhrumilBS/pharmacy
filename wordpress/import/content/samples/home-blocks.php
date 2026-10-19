<?php
/**
 * Sample: the home page rebuilt from core blocks, so it can be edited in the
 * block editor (Pages > "Home (block editor sample)").
 *
 * The copy is read from content/pages/home.php (the real home page's field
 * values), so both stay word-for-word the same; only the structure differs.
 * Unlike the real home page (SCF fields + front-page.php), every section here
 * is plain blocks: Group, Heading, Paragraph, Columns, Image, List, Details,
 * Buttons. The interactive parts that need scripts (demo slip, logo strip,
 * product tour, estimator, demo form) are Shortcode blocks (inc/blocks.php).
 *
 * Not in content/pages/ on purpose: import.php imports every file there, and
 * this page is a sample, loaded with `php wordpress/import/home-blocks.php`.
 *
 * Differences from the real home page, by design:
 * - the "Sound familiar?" inbox and the feature tabs are static blocks (cards
 *   and rows), because tabs need scripts (the inbox's "Pick a message" line
 *   becomes "Here is what changes");
 * - the checklist table is an accordion, one Details block per question;
 * - `confirmed => false` flags (data-confirm) are not carried over: the
 *   block editor has no such attribute, so unconfirmed answers read as
 *   normal text; the bracketed [Confirm …] notes stay in the text.
 * - the page is noindex and not in the menus.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

require_once dirname( __DIR__, 2 ) . '/lib/blocks.php';

$home = require dirname( __DIR__ ) . '/pages/home.php';
$f    = $home['fields'];

/** Section heading: eyebrow, h2, intro. */
$head = static function ( array $h, string $eyebrow_class = 'eyebrow' ): string {
	$out = array();
	if ( ! empty( $h['eyebrow'] ) ) {
		$out[] = rx_blk_p( rx_blk_t( $h['eyebrow'] ), $eyebrow_class );
	}
	if ( ! empty( $h['title'] ) ) {
		$out[] = rx_blk_h( 2, rx_blk_t( $h['title'] ) );
	}
	$intro = isset( $h['intro_html'] ) ? rx_blk_html( $h['intro_html'] ) : rx_blk_t( (string) ( $h['intro'] ?? '' ) );
	if ( '' !== $intro ) {
		$out[] = rx_blk_p( $intro );
	}
	return rx_blk_group( $out, 'section-head' );
};

/** A full-width section: group.section > group.container > blocks. */
$section = static function ( string $anchor, string $class, array $inner ): string {
	return rx_blk_group( rx_blk_group( $inner, 'container' ), rx_blk_class( 'section', $class ), $anchor );
};

$demo_link = '#demo';
$blocks    = array();

/* ---------------------------------------------------------------- Hero (#top) */
$title = (string) $f['hero_title_html'];
$title = preg_replace( '~<span class="scribble">(.*?)</span>~s', '$1', $title );
$title = preg_replace( '~<span class="hl">(.*?)</span>~s', '<strong>$1</strong>', (string) $title );

$blocks[] = rx_blk_group(
	rx_blk_group(
		array(
			rx_blk_group(
				array(
					rx_blk_p( rx_blk_t( $f['hero_eyebrow'] ), 'eyebrow' ),
					rx_blk_h( 1, $title ),
					rx_blk_p( rx_blk_html( $f['hero_lead_html'] ), 'hero-lead' ),
					rx_blk_p( '<a href="#tour">' . rx_blk_t( $f['hero_tour_label'] ) . '</a> [rx_link setting="app_signup"]' . rx_blk_t( $f['hero_free_label'] ) . '[/rx_link]', 'hero3-alt' ),
				),
				'hero3-copy'
			),
			rx_blk_group( rx_blk_shortcode( '[rx_hero_lead id="hl1"]' ), 'hero3-form' ),
		),
		'container hero3-grid'
	),
	'hero3',
	'top'
);

/* ------------------------------------------------- Trusted strip and the tour */
$blocks[] = rx_blk_shortcode( '[rx_trusted_strip]' );
$blocks[] = rx_blk_shortcode( '[rx_product_tour end_cta="#top"]' );

/* ------------------------------------------------ "Sound familiar?" (#problems) */
$cards = array();
foreach ( $f['problems'] as $p ) {
	$cards[] = rx_blk_column(
		rx_blk_group(
			array(
				rx_blk_p( rx_blk_t( $p['topic'] . ' · ' . $p['who'] ), 'rx-label' ),
				rx_blk_h( 3, '“' . rx_blk_t( $p['quote'] ) . '”' ),
				rx_blk_p( rx_blk_t( $p['why'] ) ),
				rx_blk_p( '<strong>What changes</strong>' . rx_blk_t( $p['fix'] ), 'rx-fix' ),
			),
			'rx-card'
		)
	);
}
// The inbox's "Pick a message…" line and "Illustrative conversation" tag only make sense for the interactive
// inbox; as static cards the section reads the same without them.
$problems_head          = $f['problems_head'];
$problems_head['intro'] = 'Each one quietly costs owners money, time and sleep. Here is what changes.';
$blocks[]               = $section(
	'problems',
	'bg-soft',
	array(
		$head( $problems_head ),
		rx_blk_columns( array_slice( $cards, 0, 3 ), 'rx-cols rx-cols-3' ),
		rx_blk_columns( array_slice( $cards, 3, 3 ), 'rx-cols rx-cols-3' ),
	)
);

/* ----------------------------------------------------------- Estimator (#estimator) */
$blocks[] = $section( 'estimator', '', array( rx_blk_shortcode( '[rx_estimator cta="#demo"]' ) ) );

/* ------------------------------------------------------------------ Day (#day) */
$rows = array();
foreach ( $f['day'] as $d ) {
	$rows[] = rx_blk_group(
		array(
			rx_blk_p( rx_blk_t( $d['time'] . ' ' . $d['ampm'] ), 'rx-time' ),
			rx_blk_h( 3, rx_blk_t( $d['title'] ) ),
			rx_blk_p( rx_blk_t( $d['text'] ) ),
		),
		'rx-shift-row'
	);
}
$blocks[] = $section( 'day', '', array( $head( $f['day_head'] ), rx_blk_group( $rows, 'rx-shift' ) ) );

/* ----------------------------------------------------------- Features (#features) */
$features = array( $head( $f['features_head'] ) );
foreach ( $f['tabs'] as $t ) {
	$points     = array_map( static fn( $pt ) => rx_blk_html( $pt['html'] ), $t['points'] );
	$features[] = rx_blk_columns(
		array(
			rx_blk_column( rx_blk_image( get_theme_file_uri( 'assets/img/screens/' . $t['screen'] . '.svg' ), $t['screen_alt'] ) ),
			rx_blk_column(
				array(
					rx_blk_h( 3, rx_blk_t( $t['title'] ) ),
					rx_blk_p( rx_blk_t( $t['lead'] ) ),
					rx_blk_list( $points, 'rx-checks' ),
				)
			),
		),
		'rx-cols rx-cols-feature rx-feature'
	);
}
$blocks[] = $section( 'features', '', $features );

/* ----------------------------------------------------------- Switching (#switching) */
$from = array_map( static fn( $x ) => rx_blk_p( rx_blk_html( $x['html'] ) ), $f['from'] );
$steps = array();
foreach ( $f['switch_steps'] as $s ) {
	$steps[] = rx_blk_group(
		array(
			rx_blk_h( 3, rx_blk_t( $s['title'] ) ),
			rx_blk_p( rx_blk_html( $s['text'] ) ),
			rx_blk_p( rx_blk_t( $s['when'] ), 'rx-when' ),
		),
		'rx-step'
	);
}
$blocks[] = $section(
	'switching',
	'bg-soft',
	array(
		rx_blk_columns(
			array(
				rx_blk_column(
					array_merge(
						array(
							$head( $f['switching_head'] ),
							rx_blk_group(
								array(
									rx_blk_h( 3, rx_blk_t( $f['moved_title'] ) ),
									rx_blk_list( array_map( static fn( $m ) => rx_blk_t( $m['text'] ), $f['moved'] ), 'rx-checks' ),
								),
								'rx-card'
							),
						),
						$from,
						array( rx_blk_buttons( array( array( 'text' => $f['switch_button'], 'url' => $demo_link ) ) ) )
					)
				),
				rx_blk_column( rx_blk_group( $steps, 'rx-steps' ) ),
			),
			'rx-cols rx-cols-switch'
		),
	)
);

/* ------------------------------------------------------------ Solutions (#solutions) */
$sol = array();
foreach ( $f['solutions'] as $s ) {
	$sol[] = rx_blk_column(
		rx_blk_group(
			array(
				rx_blk_p( rx_blk_t( $s['who'] ), 'rx-label' ),
				rx_blk_h( 3, rx_blk_t( $s['title'] ) ),
				rx_blk_p( rx_blk_t( $s['text'] ) ),
				rx_blk_list( array_map( static fn( $c ) => rx_blk_t( $c['text'] ), $s['checks'] ), 'rx-checks' ),
			),
			rx_blk_class( 'rx-card', ! empty( $s['featured'] ) ? 'is-featured' : '' )
		)
	);
}
$blocks[] = $section( 'solutions', '', array( $head( $f['solutions_head'] ), rx_blk_columns( $sol, 'rx-cols rx-cols-3' ) ) );

/* ------------------------------------------------------------ Checklist (#checklist) */
$qa = array();
foreach ( $f['checklist'] as $c ) {
	$qa[] = rx_blk_details(
		$c['ask'],
		array(
			rx_blk_p( '<strong>Why it matters:</strong> ' . rx_blk_t( $c['why'] ) ),
			rx_blk_p( '<strong>Our answer:</strong> ' . rx_blk_html( $c['answer_html'] ) ),
		)
	);
}
$blocks[] = $section(
	'checklist',
	'bg-soft',
	array(
		$head( $f['checklist_head'] ),
		rx_blk_group( $qa, 'rx-faq' ),
		rx_blk_p( rx_blk_html( $f['checklist_foot_html'] ) ),
		rx_blk_buttons( array( array( 'text' => $f['checklist_button'], 'url' => $demo_link, 'class' => 'is-secondary' ) ) ),
	)
);

/* ---------------------------------------------------------------- Support (#support) */
$channels = array();
foreach ( $f['channels'] as $c ) {
	$channels[] = rx_blk_group( array( rx_blk_h( 3, rx_blk_t( $c['title'] ) ), rx_blk_p( rx_blk_html( $c['html'] ) ) ), 'rx-card' );
}
$blocks[] = $section(
	'support',
	'',
	array(
		rx_blk_columns(
			array(
				rx_blk_column(
					array(
						$head( $f['support_head'] ),
						rx_blk_list( array_map( static fn( $p ) => rx_blk_html( $p['html'] ), $f['support_points'] ), 'rx-checks' ),
					)
				),
				rx_blk_column( rx_blk_group( $channels, 'rx-grid-2' ) ),
			),
			'rx-cols rx-cols-2'
		),
	)
);

/* ------------------------------------------------------------------------ FAQ (#faq) */
$faq = array();
foreach ( $f['faq'] as $q ) {
	$faq[] = rx_blk_details( $q['q'], rx_blk_p( rx_blk_html( $q['a_html'] ) ) );
}
$blocks[] = $section(
	'faq',
	'',
	array(
		$head( $f['faq_head'] ),
		rx_blk_group( $faq, 'rx-faq' ),
		rx_blk_p( rx_blk_t( 'Call ' ) . '[rx_tel key="support"]' ),
	)
);

/* ----------------------------------------------------------------- Demo (#demo) */
$next = array_map(
	static fn( $s ) => '<strong>' . rx_blk_html( rx_blk_t( $s['title'] ) ) . '</strong>' . rx_blk_html( $s['text'] ),
	$f['demo_steps']
);
$blocks[] = rx_blk_group(
	rx_blk_group(
		array(
			rx_blk_group(
				array(
					rx_blk_p( rx_blk_t( $f['demo_head']['eyebrow'] ), 'eyebrow light' ),
					rx_blk_h( 2, rx_blk_t( $f['demo_head']['title'] ) ),
					rx_blk_p( rx_blk_html( $f['demo_html'] ) ),
					rx_blk_list( $next, 'rx-next', true ),
				),
				'final-copy'
			),
			rx_blk_group(
				rx_blk_shortcode( '[rx_demo_form title="' . $f['form_title'] . '" sub="' . $f['form_sub'] . '" success_title="' . $f['form_success'] . '"]' )
			),
		),
		'container final-grid'
	),
	'final',
	'demo'
);

return array(
	'source'   => 'samples/home-blocks.php',
	'key'      => 'home-blocks',
	'title'    => 'Home (block editor sample)',
	'slug'     => 'home-blocks',
	'template' => 'templates/blocks.php',
	'status'   => 'publish',
	'seo'      => array(
		'title'   => 'Home (block editor sample) %%sep%% %%sitename%%',
		'desc'    => 'The home page rebuilt from core blocks, as a sample for the block editor.',
		'noindex' => true,
	),
	'content'  => implode( "\n\n", $blocks ),
);
