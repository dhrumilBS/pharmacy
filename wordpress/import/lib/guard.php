<?php
/**
 * Local-only guards for the setup and import scripts.
 *
 * - rx_bootstrap_wp() loads the local WordPress install (pharmacy-wp) from the CLI.
 * - rx_assert_local() aborts unless that install is this PC's pharmacy-wp,
 *   with a local database host and local site URLs.
 *
 * Both functions only read. They create no users, write no options and
 * change no WordPress settings. Scripts call them in this order:
 *
 *     require_once __DIR__ . '/lib/guard.php';
 *     rx_bootstrap_wp();
 *     rx_assert_local();
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

/**
 * Print a guard error to STDERR and stop with exit code 1.
 *
 * @param string $message What failed and what to do about it.
 */
function rx_guard_fail( string $message ): never {
	fwrite( STDERR, '[rx guard] ' . $message . PHP_EOL );
	exit( 1 );
}

/**
 * Normalise a filesystem path for comparison: forward slashes, no trailing
 * slash, and lower case on Windows (case-insensitive filesystem).
 *
 * @param string $path Path to normalise.
 */
function rx_guard_norm_path( string $path ): string {
	$path = rtrim( str_replace( '\\', '/', $path ), '/' );
	return 'Windows' === PHP_OS_FAMILY ? strtolower( $path ) : $path;
}

/**
 * Load the local WordPress install for a CLI script.
 *
 * Exits unless running under the CLI. Sets the request variables WordPress
 * expects, then requires htdocs/pharmacy-wp/wp-load.php, resolved relative to
 * this file (wordpress/import/lib → htdocs).
 */
function rx_bootstrap_wp(): void {
	/*
	 * wp-load.php runs wp-config.php and wp-settings.php inside this function's
	 * scope. wp-config.php assigns $table_prefix as a plain variable, and core
	 * reads it with `global $table_prefix`, so it must be declared global here
	 * (the same approach WP-CLI uses). $wp_filter is read the same way when
	 * an advanced-cache.php drop-in is present.
	 */
	global $table_prefix, $wp_filter;

	if ( PHP_SAPI !== 'cli' ) {
		exit;
	}

	// Already loaded in this process.
	if ( defined( 'ABSPATH' ) && function_exists( 'get_option' ) ) {
		return;
	}

	$wp_load = dirname( __DIR__, 4 ) . '/pharmacy-wp/wp-load.php';
	if ( ! is_file( $wp_load ) ) {
		rx_guard_fail( 'WordPress not found: ' . $wp_load . ' does not exist.' );
	}

	// Request variables WordPress and plugins read; none exist under the CLI.
	$_SERVER['HTTP_HOST']       = 'localhost';
	$_SERVER['SERVER_NAME']     = 'localhost';
	$_SERVER['REQUEST_URI']     = '/pharmacy-wp/';
	$_SERVER['REQUEST_METHOD']  = 'GET';
	$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

	/*
	 * Runtime constant for this process only (wp-config.php is not touched):
	 * stops the script from spawning a WP-Cron loopback, which would run
	 * scheduled update checks against outside hosts (Requirement 1.4).
	 */
	if ( ! defined( 'DISABLE_WP_CRON' ) ) {
		define( 'DISABLE_WP_CRON', true );
	}

	require_once $wp_load;
}

/**
 * Abort unless WordPress is this PC's local pharmacy-wp install.
 *
 * Checks, in order:
 * 1. WordPress is loaded (rx_bootstrap_wp() ran).
 * 2. DB_HOST is localhost or 127.0.0.1 (an optional :port or :socket suffix is allowed).
 * 3. The siteurl and home hosts pass rx_is_local_host().
 * 4. ABSPATH is htdocs/pharmacy-wp/, next to this repository.
 *
 * rx_is_local_host() comes from the theme's inc/core/http.php, which is
 * required here only when the function is not defined yet (for example
 * before the pharmacy theme is active).
 */
function rx_assert_local(): void {
	if ( PHP_SAPI !== 'cli' ) {
		exit;
	}

	if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_option' ) ) {
		rx_guard_fail( 'WordPress is not loaded. Call rx_bootstrap_wp() before rx_assert_local().' );
	}

	if ( ! function_exists( 'rx_is_local_host' ) ) {
		$http = dirname( __DIR__, 2 ) . '/themes/pharmacy/inc/core/http.php';
		if ( ! is_file( $http ) ) {
			rx_guard_fail( 'Missing ' . $http . ' (it provides rx_is_local_host()).' );
		}
		require_once $http;
		if ( ! function_exists( 'rx_is_local_host' ) ) {
			rx_guard_fail( $http . ' does not define rx_is_local_host().' );
		}
	}

	// 2. Database host: the part before any ":port" or ":/socket" suffix.
	$db_host = defined( 'DB_HOST' ) ? (string) DB_HOST : '';
	$db_name = strtolower( trim( explode( ':', $db_host, 2 )[0] ) );
	if ( ! in_array( $db_name, array( 'localhost', '127.0.0.1' ), true ) ) {
		rx_guard_fail( 'DB_HOST "' . $db_host . '" is not localhost or 127.0.0.1. Aborting before any write.' );
	}

	// 3. Site URLs.
	foreach ( array( 'siteurl', 'home' ) as $option ) {
		$url  = (string) get_option( $option );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host || ! rx_is_local_host( strtolower( $host ) ) ) {
			rx_guard_fail( 'Option "' . $option . '" (' . $url . ') is not on a local host. Aborting before any write.' );
		}
	}

	// 4. Install path.
	$expected = realpath( dirname( __DIR__, 4 ) . '/pharmacy-wp' );
	$actual   = realpath( ABSPATH );
	if (
		false === $expected
		|| false === $actual
		|| rx_guard_norm_path( $actual ) !== rx_guard_norm_path( $expected )
		|| ! str_ends_with( rx_guard_norm_path( $actual ), '/htdocs/pharmacy-wp' )
	) {
		rx_guard_fail( 'ABSPATH "' . ABSPATH . '" is not the local htdocs/pharmacy-wp install. Aborting before any write.' );
	}
}
