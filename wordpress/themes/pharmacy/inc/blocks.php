<?php
/**
 * Block editor support: widget shortcodes and editor styles.
 *
 * Pages built in the block editor (template "Block page", see
 * templates/blocks.php) are ordinary blocks (group, heading, paragraph,
 * columns, image, list, details, buttons) styled by assets/css/blocks.css.
 * The interactive parts of the home page need their scripts and markup, so
 * they are inserted as a Shortcode block. Each shortcode prints the same
 * template part the front page uses:
 *
 *   [rx_hero_lead id="hl1" more="#demo"]              parts/hero-lead      (2-field demo slip)
 *   [rx_trusted_strip title="" note=""]               parts/trusted-strip  (logo marquee)
 *   [rx_product_tour eyebrow="" intro="" end_cta=""]  parts/product-tour   (animated walkthrough)
 *   [rx_estimator cta="#demo"]                        parts/estimator      (expiry loss estimator)
 *   [rx_demo_form title="" sub="" success_title=""]   parts/demo-form      (book a demo)
 *
 * Only the attributes you write are passed on, so a missing attribute keeps
 * the part's own text and an empty one (title="") hides that element, the
 * same rules as the parts. The tour, estimator and demo form use fixed ids:
 * add each of them at most once per page.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints a template part into a string.
 *
 * @param string $slug Part slug under parts/, e.g. 'hero-lead'.
 * @param array  $args Arguments for the part.
 * @return string Markup.
 */
function rx_blocks_render_part( string $slug, array $args = array() ): string {
	ob_start();
	get_template_part( 'parts/' . $slug, null, $args );
	return (string) ob_get_clean();
}

/**
 * Keeps only the shortcode attributes that were written, limited to a list.
 *
 * @param mixed    $atts    Shortcode attributes ('' when none).
 * @param string[] $allowed Attribute names to pass on.
 * @return array<string, string>
 */
function rx_blocks_args( $atts, array $allowed ): array {
	$out = array();
	foreach ( is_array( $atts ) ? $atts : array() as $name => $value ) {
		if ( in_array( $name, $allowed, true ) && is_string( $value ) ) {
			$out[ $name ] = $value;
		}
	}
	return $out;
}

/**
 * Shortcode callbacks, one per widget.
 */
function rx_sc_hero_lead( $atts ): string {
	return rx_blocks_render_part( 'hero-lead', rx_blocks_args( $atts, array( 'id', 'more' ) ) );
}

function rx_sc_trusted_strip( $atts ): string {
	return rx_blocks_render_part( 'trusted-strip', rx_blocks_args( $atts, array( 'title', 'note' ) ) );
}

function rx_sc_product_tour( $atts ): string {
	return rx_blocks_render_part( 'product-tour', rx_blocks_args( $atts, array( 'eyebrow', 'title_html', 'intro', 'end_cta' ) ) );
}

function rx_sc_estimator( $atts ): string {
	return rx_blocks_render_part( 'estimator', rx_blocks_args( $atts, array( 'cta' ) ) );
}

function rx_sc_demo_form( $atts ): string {
	return rx_blocks_render_part( 'demo-form', rx_blocks_args( $atts, array( 'title', 'sub', 'success_title' ) ) );
}

/**
 * Registers the widget shortcodes.
 */
function rx_register_widget_shortcodes(): void {
	$tags = array(
		'rx_hero_lead'     => 'rx_sc_hero_lead',
		'rx_trusted_strip' => 'rx_sc_trusted_strip',
		'rx_product_tour'  => 'rx_sc_product_tour',
		'rx_estimator'     => 'rx_sc_estimator',
		'rx_demo_form'     => 'rx_sc_demo_form',
	);
	foreach ( $tags as $tag => $callback ) {
		add_shortcode( $tag, $callback );
	}
}
add_action( 'init', 'rx_register_widget_shortcodes' );

/*
 * Group blocks print exactly what the editor saved. For themes without a
 * theme.json, WordPress wraps every Group's contents in an extra
 * `wp-block-group__inner-container` div at render time (a back-compat step
 * for very old content). That wrapper breaks the site's `parent > child` CSS
 * (`.section > .container`, `.hero3-grid > …`), and no content here needs it.
 * The saved content is not changed, so the editor is unaffected.
 */
remove_filter( 'render_block_core/group', 'wp_restore_group_inner_container', 10 );

/**
 * Makes the block editor look like the site: Campton, the colour tokens and
 * the block styles load inside the editor canvas.
 */
function rx_blocks_editor_styles(): void {
	add_theme_support( 'editor-styles' );
	add_editor_style(
		array(
			'assets/css/fonts.css',
			'assets/css/site.css',
			'assets/css/blocks.css',
		)
	);
}
add_action( 'after_setup_theme', 'rx_blocks_editor_styles' );
