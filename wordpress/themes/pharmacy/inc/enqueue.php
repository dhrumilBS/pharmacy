<?php
/**
 * Asset loading: asset map, font preload, favicon.
 *
 * Same files and order as the PHP site (partials/head.php and footer.php):
 * site, fonts and layout CSS plus site.js everywhere, then the page extras.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Describes the current request for rx_asset_map().
 *
 * @return array{key: string, feature: bool, single: bool, estimator: bool}
 */
function rx_asset_context(): array {
	$feature = is_page_template( 'templates/feature.php' );

	return array(
		'key'       => rx_asset_page_key(),
		'feature'   => $feature,
		'single'    => is_singular( 'post' ),
		'estimator' => $feature && (bool) rx_asset_field( 'leaks_estimator' ),
	);
}

/**
 * The page key from rx_page_key(), with the same rules as a fallback.
 */
function rx_asset_page_key(): string {
	if ( function_exists( 'rx_page_key' ) ) {
		return (string) rx_page_key();
	}

	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_home() || is_singular( 'post' ) ) {
		return 'blog';
	}
	if ( is_page() ) {
		$key = get_post_meta( get_queried_object_id(), '_rx_key', true );
		return is_string( $key ) && '' !== $key ? $key : 'generic';
	}
	return 'generic';
}

/**
 * Reads an SCF field of the current page through rx_field(), or get_field()
 * when the helpers are not loaded. Null when SCF is inactive.
 *
 * @param string $name Field name.
 * @return mixed
 */
function rx_asset_field( string $name ) {
	if ( function_exists( 'rx_field' ) ) {
		return rx_field( $name );
	}
	return function_exists( 'get_field' ) ? get_field( $name ) : null;
}

/**
 * Stylesheets and scripts for a page, in output order (design §2).
 *
 * @param array|null $ctx {
 *     Page description. Defaults to the current request (rx_asset_context()).
 *
 *     @type string $key       Page key (home, pricing, features, blog, …).
 *     @type bool   $feature   Page uses the Feature template.
 *     @type bool   $single    Single blog post.
 *     @type bool   $estimator Feature page with the leaks estimator on.
 * }
 * @return array{styles: array<string, string>, scripts: array<string, string>} Handle => path under assets/.
 */
function rx_asset_map( ?array $ctx = null ): array {
	$ctx = wp_parse_args(
		$ctx ?? rx_asset_context(),
		array(
			'key'       => 'generic',
			'feature'   => false,
			'single'    => false,
			'estimator' => false,
		)
	);
	$key = (string) $ctx['key'];

	$styles  = array(
		'rx-site'   => 'css/site.css',
		'rx-fonts'  => 'css/fonts.css',
		'rx-layout' => 'css/layout.css',
	);
	$scripts = array(
		'rx-site-js' => 'js/site.js',
	);

	if ( 'home' === $key ) {
		// Same order as index.php: home, estimator, tour.
		$styles['rx-home']      = 'css/home.css';
		$styles['rx-estimator'] = 'css/estimator.css';
		$styles['rx-tour']      = 'css/tour.css';
		$scripts['rx-tour-js']  = 'js/tour.js';
	} elseif ( in_array( $key, array( 'pricing', 'faq', 'switch' ), true ) ) {
		$styles['rx-pages'] = 'css/pages.css';
		if ( 'pricing' === $key ) {
			$scripts['rx-pricing-js'] = 'js/pricing.js';
		}
	} elseif ( 'features' === $key || $ctx['feature'] ) {
		$styles['rx-feature'] = 'css/feature.css';
		if ( $ctx['feature'] && $ctx['estimator'] ) {
			$styles['rx-estimator'] = 'css/estimator.css';
		}
	} elseif ( 'blog' === $key ) {
		$styles['rx-blog'] = 'css/blog.css';
		if ( $ctx['single'] ) {
			$scripts['rx-blog-js'] = 'js/blog.js';
		}
	}

	return array(
		'styles'  => $styles,
		'scripts' => $scripts,
	);
}

/**
 * Enqueues the current page's assets. Scripts load deferred in the footer
 * with no dependencies; get_theme_file_uri() lets the child theme override
 * any single file.
 */
function rx_enqueue_assets(): void {
	$map = rx_asset_map();

	foreach ( $map['styles'] as $handle => $path ) {
		wp_enqueue_style( $handle, get_theme_file_uri( 'assets/' . $path ), array(), RX_VERSION );
	}

	foreach ( $map['scripts'] as $handle => $path ) {
		wp_enqueue_script(
			$handle,
			get_theme_file_uri( 'assets/' . $path ),
			array(),
			RX_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'rx_enqueue_assets' );

/**
 * Preloads Campton 400 and 700, the only web fonts (self-hosted).
 *
 * @param array $resources Preload resources.
 */
function rx_preload_fonts( $resources ): array {
	$resources = is_array( $resources ) ? $resources : array();

	foreach ( array( '400', '700' ) as $weight ) {
		$resources[] = array(
			'href'        => get_theme_file_uri( 'assets/fonts/campton/campton-' . $weight . '.woff2' ),
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $resources;
}
add_filter( 'wp_preload_resources', 'rx_preload_fonts' );

/**
 * Prints the SVG favicon (the logo mark), as partials/head.php does.
 */
function rx_favicon(): void {
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( get_theme_file_uri( 'assets/img/logo-mark.svg' ) )
	);
}
add_action( 'wp_head', 'rx_favicon', 1 );
