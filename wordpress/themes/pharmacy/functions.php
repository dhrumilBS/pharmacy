<?php
/**
 * Pharmacy parent theme bootstrap.
 *
 * Loads the pure helpers in inc/core/ first (no WordPress calls; the tests
 * load them too), then the WordPress-aware files in inc/.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/** Theme version, used as the asset version. Keep in step with style.css. */
define( 'RX_VERSION', '1.0.0' );

// Pure PHP: formatting, host checks, pricing, blog and schema logic.
foreach ( array( 'format', 'http', 'pricing', 'blog', 'schema' ) as $rx_file ) {
	require_once __DIR__ . '/inc/core/' . $rx_file . '.php';
}

// WordPress-aware: hooks, helpers, templates support, SEO.
foreach ( array( 'http', 'setup', 'helpers', 'enqueue', 'cleanup', 'nav', 'shortcodes', 'pricing', 'blog', 'seo', 'blocks' ) as $rx_file ) {
	require_once __DIR__ . '/inc/' . $rx_file . '.php';
}

unset( $rx_file );
