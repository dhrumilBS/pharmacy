<?php
/**
 * One-time setup of the local pharmacy-wp install (safe to re-run).
 *
 * Usage, from the repository root:
 *
 *     D:\xampp\php\php.exe wordpress\import\setup.php           run the setup
 *     D:\xampp\php\php.exe wordpress\import\setup.php --check   report the state; changes nothing itself
 *
 * What the setup does (design §13):
 * 1. Guards: CLI only, then rx_bootstrap_wp() and rx_assert_local() before
 *    any write. The theme's outbound HTTP allow-list is active for the
 *    whole run, and every outbound request attempt is listed.
 * 2. Checks that the theme junctions and the plugin folders exist. When one
 *    is missing it prints the mklink / robocopy command and stops before
 *    writing anything. It never runs those commands itself.
 * 3. Activates Secure Custom Fields, Yoast SEO and Contact Form 7 with
 *    activate_plugin(), and switches to the pharmacy-child theme.
 * 4. Sets permalink_structure, default_comment_status and default_ping_status.
 * 5. Sets the Yoast options with WPSEO_Options::set() and reads each one back
 *    with WPSEO_Options::get().
 * 6. flush_rewrite_rules( false ).
 *
 * Each value is written only when it differs from the target, so a second
 * run changes nothing and ends in the same state. It creates no users and
 * changes no other settings; plugin activation writes its own data (for
 * example Yoast's options and roles, and Contact Form 7's sample form).
 *
 * Why three PHP processes: a plugin activated in a running request has
 * missed plugins_loaded, so Yoast's options API is only half set up in that
 * process. Yoast's activation also clears the rewrite rules when its process
 * shuts down. So the default run (the "driver") loads no WordPress and starts
 * this file three times, one after the other, with the same PHP binary:
 *
 *     --step=activate    steps 1–4  (plugins, theme, core options)
 *     --step=configure   steps 1, 5, 6 in a fresh process where Yoast and the
 *                        active theme are fully loaded; runs after the
 *                        activate process (and Yoast's shutdown hook) ended
 *     --check            final read-only check in a third fresh process
 *
 * The driver stops at the first step that fails. Every step exits 1 on any
 * failure, so the run exits non-zero too.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

require_once __DIR__ . '/lib/guard.php';

/** Plugins to activate: main file => folder copied from labora-wp. */
const RX_SETUP_PLUGINS = array(
	'secure-custom-fields/secure-custom-fields.php' => 'secure-custom-fields',
	'wordpress-seo/wp-seo.php'                      => 'wordpress-seo',
	'contact-form-7/wp-contact-form-7.php'          => 'contact-form-7',
);

/** Theme folders that must be junctions into wordpress/themes/. */
const RX_SETUP_THEMES = array( 'pharmacy', 'pharmacy-child' );

/** Theme to activate and its parent. */
const RX_SETUP_STYLESHEET = 'pharmacy-child';
const RX_SETUP_TEMPLATE   = 'pharmacy';

/** WordPress options: name => value. */
const RX_SETUP_OPTIONS = array(
	'permalink_structure'    => '/blog/%postname%/',
	'default_comment_status' => 'closed',
	'default_ping_status'    => 'closed',
);

/** Yoast options: key => [ option group, value ]. */
const RX_SETUP_YOAST = array(
	'separator'            => array( 'wpseo_titles', 'sc-pipe' ),
	'title-page'           => array( 'wpseo_titles', '%%title%% %%sep%% %%sitename%%' ),
	'title-post'           => array( 'wpseo_titles', '%%title%% %%sep%% %%sitename%%' ),
	'company_or_person'    => array( 'wpseo_titles', 'company' ),
	'company_name'         => array( 'wpseo_titles', '' ),
	'disable-author'       => array( 'wpseo_titles', true ),
	'disable-date'         => array( 'wpseo_titles', true ),
	'noindex-tax-category' => array( 'wpseo_titles', true ),
	'noindex-tax-post_tag' => array( 'wpseo_titles', true ),
	'breadcrumbs-enable'   => array( 'wpseo_titles', false ),
	'tracking'             => array( 'wpseo', false ),
);

/*
 * -------------------------------------------------------------------------
 * Output and bookkeeping
 * -------------------------------------------------------------------------
 */

/**
 * Print one result line and count it.
 *
 * Statuses: 'ok' (already as wanted, or verified), 'set' (changed now and
 * verified), 'fail', 'info' (not counted). Called without arguments it only
 * returns the counts.
 *
 * @param string|null $status  Status, or null to read the counts.
 * @param string      $message Line to print (may span several lines).
 * @return array<string, int> Counts per status.
 */
function rx_setup_log( ?string $status = null, string $message = '' ): array {
	static $counts = array(
		'ok'   => 0,
		'set'  => 0,
		'fail' => 0,
	);

	if ( null !== $status ) {
		if ( isset( $counts[ $status ] ) ) {
			++$counts[ $status ];
		}
		$label = str_pad( 'fail' === $status ? 'FAIL' : $status, 6 );
		$lines = explode( "\n", $message );
		echo '  ', $label, array_shift( $lines ), PHP_EOL;
		foreach ( $lines as $line ) {
			echo '        ', $line, PHP_EOL;
		}
	}

	return $counts;
}

/**
 * A value as short readable text: strings quoted, booleans and null as words,
 * arrays as JSON.
 *
 * @param mixed $value Value to show.
 */
function rx_setup_show( $value ): string {
	if ( is_string( $value ) ) {
		return '"' . $value . '"';
	}
	if ( is_bool( $value ) ) {
		return $value ? 'true' : 'false';
	}
	if ( null === $value ) {
		return 'null';
	}
	if ( is_array( $value ) ) {
		return (string) wp_json_encode( array_values( $value ) );
	}
	return (string) $value;
}

/**
 * A path with the separators Windows commands expect.
 *
 * @param string $path Path with any separators.
 */
function rx_setup_os_path( string $path ): string {
	return 'Windows' === PHP_OS_FAMILY ? str_replace( '/', '\\', $path ) : str_replace( '\\', '/', $path );
}

/*
 * -------------------------------------------------------------------------
 * Outbound HTTP: allow-list for the whole process, and a log of attempts
 * -------------------------------------------------------------------------
 */

/**
 * Record an outbound request attempt, or return the list.
 *
 * @param array|null $entry [ 'result' => 'blocked'|'allowed', 'host' => …, 'url' => …, 'note' => … ].
 * @return array<int, array> Every recorded attempt.
 */
function rx_setup_http_record( ?array $entry = null ): array {
	static $log = array();
	if ( null !== $entry ) {
		$log[] = $entry;
	}
	return $log;
}

/**
 * Register rx_setup_http_gate() before WordPress loads.
 *
 * add_filter() does not exist yet, so the callback goes into the global
 * $wp_filter array, which wp-includes/plugin.php turns into a real hook
 * (WP_Hook::build_preinitialized_hooks(), the way WP-CLI registers early
 * hooks). The gate then covers the whole process, including plugin loading
 * before the theme's functions.php runs.
 */
function rx_setup_register_http_gate(): void {
	if ( function_exists( 'add_filter' ) ) {
		add_filter( 'pre_http_request', 'rx_setup_http_gate', PHP_INT_MAX, 3 );
		return;
	}
	$GLOBALS['wp_filter']['pre_http_request'][ PHP_INT_MAX ][] = array(
		'function'      => 'rx_setup_http_gate',
		'accepted_args' => 3,
	);
}

/**
 * pre_http_request gate for this script: applies and logs the theme's
 * allow-list.
 *
 * - Before the theme's rx_http_allow_list() exists, every request is
 *   blocked (fail closed).
 * - Afterwards the gate returns exactly what rx_http_allow_list() decides,
 *   and records blocked and allowed attempts. The theme's own filter (same
 *   priority, registered later) still runs after it and decides the same,
 *   so the gate can only make the allow-list stricter, never looser.
 *
 * @param false|array|WP_Error $preempt     Response from earlier filters, or false.
 * @param array                $parsed_args HTTP request arguments.
 * @param string               $url         Request URL.
 * @return false|array|WP_Error
 */
function rx_setup_http_gate( $preempt, $parsed_args, $url ) {
	$url  = is_string( $url ) ? $url : '';
	$host = function_exists( 'rx_parse_host' ) ? rx_parse_host( $url ) : (string) parse_url( $url, PHP_URL_HOST );

	if ( ! function_exists( 'rx_http_allow_list' ) ) {
		rx_setup_http_record(
			array(
				'result' => 'blocked',
				'host'   => $host,
				'url'    => $url,
				'note'   => 'allow-list not loaded yet',
			)
		);
		return new WP_Error( 'rx_blocked', 'Outbound request blocked by setup.php: the theme allow-list is not loaded yet.' );
	}

	$decision = rx_http_allow_list( $preempt, $parsed_args, $url );

	if ( is_wp_error( $decision ) && 'rx_blocked' === $decision->get_error_code() ) {
		rx_setup_http_record(
			array(
				'result' => 'blocked',
				'host'   => $host,
				'url'    => $url,
				'note'   => 'theme allow-list',
			)
		);
	} elseif ( false === $decision ) {
		rx_setup_http_record(
			array(
				'result' => 'allowed',
				'host'   => $host,
				'url'    => $url,
				'note'   => 'theme allow-list',
			)
		);
	}

	return $decision;
}

/**
 * Make sure the theme's allow-list filter is hooked in this process.
 *
 * When the pharmacy theme is active, its inc/http.php already added the
 * filter. Otherwise this requires the parent theme's inc/core/http.php and
 * inc/http.php from the repository (they only need ABSPATH). Without
 * rx_setting() the Pricing_API host is unknown, so only local hosts pass.
 * Stops the script when the filter still is not hooked.
 */
function rx_setup_require_allow_list(): void {
	$source = 'hooked by the active theme';

	if ( ! has_filter( 'pre_http_request', 'rx_http_allow_list' ) ) {
		$inc = dirname( __DIR__ ) . '/themes/pharmacy/inc';
		if ( ! function_exists( 'rx_host_allowed' ) ) {
			require_once $inc . '/core/http.php';
		}
		if ( function_exists( 'rx_http_allow_list' ) ) {
			add_filter( 'pre_http_request', 'rx_http_allow_list', PHP_INT_MAX, 3 );
		} else {
			require_once $inc . '/http.php';
		}
		$source = 'loaded by setup.php from ' . rx_setup_os_path( $inc . '/http.php' );
	}

	if ( ! has_filter( 'pre_http_request', 'rx_http_allow_list' ) || ! has_filter( 'pre_http_request', 'rx_setup_http_gate' ) ) {
		rx_guard_fail( 'The outbound HTTP allow-list (rx_http_allow_list) is not active. Nothing was changed.' );
	}

	rx_setup_log( 'info', 'Outbound HTTP allow-list active (' . $source . ').' );
}

/**
 * Print the outbound request attempts of this process.
 */
function rx_setup_print_http_log(): void {
	$log = rx_setup_http_record();
	if ( array() === $log ) {
		rx_setup_log( 'info', 'Outbound requests: none attempted.' );
		return;
	}
	foreach ( $log as $entry ) {
		rx_setup_log(
			'info',
			sprintf( 'Outbound request %s (%s): %s', $entry['result'], $entry['note'], '' !== $entry['host'] ? $entry['host'] : $entry['url'] )
		);
	}
}

/*
 * -------------------------------------------------------------------------
 * State and checks
 * -------------------------------------------------------------------------
 */

/**
 * The settings this script manages, read straight from the options table.
 *
 * Yoast values are read from the raw wpseo / wpseo_titles option arrays,
 * so they can be shown before Yoast is loaded.
 *
 * @return array<string, mixed>
 */
function rx_setup_state(): array {
	$state = array(
		'template'       => get_option( 'template' ),
		'stylesheet'     => get_option( 'stylesheet' ),
		'active_plugins' => (array) get_option( 'active_plugins', array() ),
	);
	foreach ( array_keys( RX_SETUP_OPTIONS ) as $name ) {
		$state[ $name ] = get_option( $name );
	}

	$groups = array();
	foreach ( RX_SETUP_YOAST as $key => $spec ) {
		$group            = $spec[0];
		$groups[ $group ] = $groups[ $group ] ?? get_option( $group );
		$state[ 'yoast ' . $key ] = is_array( $groups[ $group ] ) && array_key_exists( $key, $groups[ $group ] )
			? $groups[ $group ][ $key ]
			: '(not set)';
	}

	return $state;
}

/**
 * Print rx_setup_state() under a heading.
 *
 * @param string $heading For example "Before".
 */
function rx_setup_print_state( string $heading ): void {
	echo $heading, ':', PHP_EOL;
	foreach ( rx_setup_state() as $name => $value ) {
		echo '        ', str_pad( $name, 30 ), ' ', '(not set)' === $value ? $value : rx_setup_show( $value ), PHP_EOL;
	}
}

/**
 * Check the theme junctions and plugin folders. Read-only.
 *
 * Prints the command that would fix each missing item; never runs it.
 *
 * @return bool True when everything is in place.
 */
function rx_setup_check_folders(): bool {
	$ok     = true;
	$htdocs = dirname( __DIR__, 3 );

	foreach ( RX_SETUP_THEMES as $slug ) {
		$link        = WP_CONTENT_DIR . '/themes/' . $slug;
		$target      = dirname( __DIR__ ) . '/themes/' . $slug;
		$real_link   = realpath( $link );
		$real_target = realpath( $target );

		if ( false === $real_target ) {
			rx_setup_log( 'fail', 'Theme source missing: ' . rx_setup_os_path( $target ) );
			$ok = false;
		} elseif ( false === $real_link || rx_guard_norm_path( $real_link ) !== rx_guard_norm_path( $real_target ) ) {
			rx_setup_log(
				'fail',
				'Theme junction missing or pointing elsewhere: ' . rx_setup_os_path( $link ) . "\n"
				. 'Create it with: cmd /c mklink /J "' . rx_setup_os_path( $link ) . '" "' . rx_setup_os_path( $real_target ) . '"'
			);
			$ok = false;
		} else {
			rx_setup_log( 'ok', 'Theme junction ' . $slug . ' → ' . rx_setup_os_path( $real_target ) );
		}
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	foreach ( RX_SETUP_PLUGINS as $file => $folder ) {
		$path = WP_PLUGIN_DIR . '/' . $file;
		if ( ! is_file( $path ) ) {
			rx_setup_log(
				'fail',
				'Plugin missing: ' . rx_setup_os_path( $path ) . "\n"
				. 'Copy it with: robocopy "' . rx_setup_os_path( $htdocs . '/labora-wp/wp-content/plugins/' . $folder ) . '" "'
				. rx_setup_os_path( WP_PLUGIN_DIR . '/' . $folder ) . '" /E'
			);
			$ok = false;
			continue;
		}
		$data = get_plugin_data( $path, false, false );
		rx_setup_log( 'ok', 'Plugin folder ' . $folder . ' (' . $data['Name'] . ' ' . $data['Version'] . ')' );
	}

	return $ok;
}

/**
 * Check that the child theme is installed, has its parent and meets its
 * PHP and WordPress requirements. Read-only.
 *
 * @return bool True when pharmacy-child can be activated.
 */
function rx_setup_check_theme(): bool {
	$child = wp_get_theme( RX_SETUP_STYLESHEET );

	if ( ! $child->exists() ) {
		rx_setup_log( 'fail', 'Theme ' . RX_SETUP_STYLESHEET . ' is not installed.' );
		return false;
	}
	if ( $child->errors() ) {
		rx_setup_log( 'fail', 'Theme ' . RX_SETUP_STYLESHEET . ' has errors: ' . $child->errors()->get_error_message() );
		return false;
	}
	if ( RX_SETUP_TEMPLATE !== $child->get_template() || ! $child->parent() ) {
		rx_setup_log( 'fail', 'Theme ' . RX_SETUP_STYLESHEET . ' does not use ' . RX_SETUP_TEMPLATE . ' as its parent.' );
		return false;
	}
	$requirements = validate_theme_requirements( RX_SETUP_STYLESHEET );
	if ( is_wp_error( $requirements ) ) {
		rx_setup_log( 'fail', 'Theme ' . RX_SETUP_STYLESHEET . ': ' . $requirements->get_error_message() );
		return false;
	}

	return true;
}

/*
 * -------------------------------------------------------------------------
 * Steps
 * -------------------------------------------------------------------------
 */

/**
 * Step "activate": plugins, theme and core options.
 *
 * Stops before any write when a junction or plugin folder is missing.
 */
function rx_setup_activate(): void {
	rx_setup_print_state( 'Before' );

	echo 'Folders:', PHP_EOL;
	if ( ! rx_setup_check_folders() ) {
		rx_setup_log( 'info', 'Nothing was changed. Fix the items above, then run setup.php again.' );
		return;
	}

	echo 'Plugins:', PHP_EOL;
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	foreach ( array_keys( RX_SETUP_PLUGINS ) as $file ) {
		if ( is_plugin_active( $file ) ) {
			rx_setup_log( 'ok', $file . ' already active' );
			continue;
		}
		$result = activate_plugin( $file );
		if ( is_wp_error( $result ) ) {
			$output = $result->get_error_data();
			rx_setup_log(
				'fail',
				'activate_plugin( ' . $file . ' ): ' . $result->get_error_message()
				. ( is_string( $output ) && '' !== trim( $output ) ? "\nOutput: " . trim( wp_strip_all_tags( $output ) ) : '' )
			);
			continue;
		}
		if ( in_array( $file, (array) get_option( 'active_plugins', array() ), true ) ) {
			rx_setup_log( 'set', $file . ' activated' );
		} else {
			rx_setup_log( 'fail', $file . ': activate_plugin() returned no error, but the plugin is not in active_plugins.' );
		}
	}

	echo 'Theme:', PHP_EOL;
	if ( rx_setup_check_theme() ) {
		if ( RX_SETUP_STYLESHEET === get_stylesheet() && RX_SETUP_TEMPLATE === get_template() ) {
			rx_setup_log( 'ok', RX_SETUP_STYLESHEET . ' already active (parent ' . RX_SETUP_TEMPLATE . ')' );
		} else {
			$from = get_stylesheet();
			switch_theme( RX_SETUP_STYLESHEET );
			if ( RX_SETUP_STYLESHEET === get_stylesheet() && RX_SETUP_TEMPLATE === get_template() ) {
				rx_setup_log( 'set', 'switch_theme(): ' . rx_setup_show( $from ) . ' → ' . RX_SETUP_STYLESHEET . ' (parent ' . RX_SETUP_TEMPLATE . ')' );
			} else {
				rx_setup_log( 'fail', 'switch_theme( ' . RX_SETUP_STYLESHEET . ' ) did not take: stylesheet ' . get_stylesheet() . ', template ' . get_template() );
			}
		}
	}

	echo 'Options:', PHP_EOL;
	foreach ( RX_SETUP_OPTIONS as $name => $value ) {
		$before = get_option( $name );
		if ( $value === $before ) {
			rx_setup_log( 'ok', $name . ' = ' . rx_setup_show( $value ) );
			continue;
		}
		if ( 'permalink_structure' === $name ) {
			// Saves the option and re-initialises $wp_rewrite, as Settings → Permalinks does.
			$GLOBALS['wp_rewrite']->set_permalink_structure( $value );
		} else {
			update_option( $name, $value );
		}
		$after = get_option( $name );
		if ( $value === $after ) {
			rx_setup_log( 'set', $name . ': ' . rx_setup_show( $before ) . ' → ' . rx_setup_show( $after ) );
		} else {
			rx_setup_log( 'fail', $name . ' is ' . rx_setup_show( $after ) . ', expected ' . rx_setup_show( $value ) );
		}
	}
}

/**
 * Step "configure": Yoast options and the rewrite flush.
 *
 * Runs in a fresh process, so Yoast went through plugins_loaded like on any
 * request and the active theme's rewrite rules are registered.
 */
function rx_setup_configure(): void {
	echo 'Yoast SEO:', PHP_EOL;
	if ( ! class_exists( 'WPSEO_Options' ) || ! defined( 'WPSEO_VERSION' ) ) {
		rx_setup_log( 'fail', 'Yoast SEO is not loaded. Is wordpress-seo/wp-seo.php active? Run the activate step first.' );
	} else {
		rx_setup_log( 'info', 'Yoast SEO ' . WPSEO_VERSION . ' loaded.' );
		foreach ( RX_SETUP_YOAST as $key => $spec ) {
			list( $group, $value ) = $spec;

			WPSEO_Options::clear_cache();
			$before = WPSEO_Options::get( $key );
			if ( $value === $before ) {
				rx_setup_log( 'ok', $key . ' = ' . rx_setup_show( $value ) );
				continue;
			}

			WPSEO_Options::set( $key, $value, $group );

			WPSEO_Options::clear_cache();
			$after = WPSEO_Options::get( $key );
			if ( $value === $after ) {
				rx_setup_log( 'set', $key . ': ' . rx_setup_show( $before ) . ' → ' . rx_setup_show( $after ) );
			} else {
				rx_setup_log( 'fail', $key . ' reads back as ' . rx_setup_show( $after ) . ', expected ' . rx_setup_show( $value ) );
			}
		}
	}

	echo 'Rewrite rules:', PHP_EOL;
	flush_rewrite_rules( false );
	rx_setup_check_rewrites();
}

/**
 * Check the stored rewrite rules: present, and with the /blog/%postname%/
 * post rule. Read-only.
 */
function rx_setup_check_rewrites(): void {
	$rules = get_option( 'rewrite_rules' );
	if ( ! is_array( $rules ) || array() === $rules ) {
		rx_setup_log( 'fail', 'rewrite_rules is empty.' );
		return;
	}

	$post_rule = false;
	$topics    = 0;
	foreach ( $rules as $pattern => $query ) {
		if ( str_starts_with( (string) $pattern, 'blog/' ) && str_starts_with( (string) $query, 'index.php?name=$matches[1]' ) ) {
			$post_rule = true;
		}
		if ( str_contains( (string) $query, 'rx_topic=' ) ) {
			++$topics;
		}
	}

	if ( $post_rule ) {
		rx_setup_log( 'ok', count( $rules ) . ' rewrite rules stored, including the /blog/{post-slug}/ rule' );
	} else {
		rx_setup_log( 'fail', count( $rules ) . ' rewrite rules stored, but none for /blog/{post-slug}/.' );
	}
	rx_setup_log( 'info', 'Blog topic view rules (rx_topic, from the theme): ' . $topics );
}

/**
 * Step "check": compare the install with the target state. Changes nothing.
 */
function rx_setup_check(): void {
	rx_setup_print_state( 'State' );

	echo 'Folders:', PHP_EOL;
	rx_setup_check_folders();

	echo 'Plugins:', PHP_EOL;
	$active = (array) get_option( 'active_plugins', array() );
	foreach ( array_keys( RX_SETUP_PLUGINS ) as $file ) {
		if ( in_array( $file, $active, true ) ) {
			rx_setup_log( 'ok', $file . ' active' );
		} else {
			rx_setup_log( 'fail', $file . ' is not active' );
		}
	}

	echo 'Theme:', PHP_EOL;
	if ( rx_setup_check_theme() ) {
		if ( RX_SETUP_STYLESHEET === get_stylesheet() && RX_SETUP_TEMPLATE === get_template() ) {
			rx_setup_log( 'ok', 'get_stylesheet() = ' . rx_setup_show( get_stylesheet() ) . ', get_template() = ' . rx_setup_show( get_template() ) );
		} else {
			rx_setup_log( 'fail', 'get_stylesheet() = ' . rx_setup_show( get_stylesheet() ) . ', get_template() = ' . rx_setup_show( get_template() ) );
		}
	}

	echo 'Options:', PHP_EOL;
	foreach ( RX_SETUP_OPTIONS as $name => $value ) {
		$actual = get_option( $name );
		rx_setup_log( $value === $actual ? 'ok' : 'fail', $name . ' = ' . rx_setup_show( $actual ) . ( $value === $actual ? '' : ', expected ' . rx_setup_show( $value ) ) );
	}

	echo 'Yoast SEO:', PHP_EOL;
	if ( ! class_exists( 'WPSEO_Options' ) || ! defined( 'WPSEO_VERSION' ) ) {
		rx_setup_log( 'fail', 'Yoast SEO is not loaded.' );
	} else {
		rx_setup_log( 'info', 'Yoast SEO ' . WPSEO_VERSION . ' loaded.' );
		WPSEO_Options::clear_cache();
		foreach ( RX_SETUP_YOAST as $key => $spec ) {
			$actual = WPSEO_Options::get( $key );
			rx_setup_log( $spec[1] === $actual ? 'ok' : 'fail', $key . ' = ' . rx_setup_show( $actual ) . ( $spec[1] === $actual ? '' : ', expected ' . rx_setup_show( $spec[1] ) ) );
		}
	}

	echo 'Rewrite rules:', PHP_EOL;
	rx_setup_check_rewrites();
}

/**
 * Run one step in this process: guards, the step, the outbound log, totals.
 *
 * @param string $step 'activate', 'configure' or 'check'.
 * @return int Exit code: 0, or 1 when anything failed.
 */
function rx_setup_step( string $step ): int {
	echo '== setup.php ', $step, ' ==', PHP_EOL;

	rx_setup_register_http_gate();
	rx_bootstrap_wp();
	rx_assert_local();
	rx_setup_log( 'info', 'Local install: ' . rx_setup_os_path( rtrim( ABSPATH, '/\\' ) ) . ' (' . home_url( '/' ) . ')' );
	rx_setup_require_allow_list();

	if ( 'activate' === $step ) {
		rx_setup_activate();
	} elseif ( 'configure' === $step ) {
		rx_setup_configure();
	} else {
		rx_setup_check();
	}

	rx_setup_print_http_log();

	$counts = rx_setup_log();
	printf( '== %s: %d ok, %d set, %d failed ==%s', $step, $counts['ok'], $counts['set'], $counts['fail'], PHP_EOL );

	return 0 === $counts['fail'] ? 0 : 1;
}

/**
 * Default run: start the activate, configure and check steps one after
 * another, each in its own PHP process. Loads no WordPress itself.
 *
 * @return int Exit code: 0, or 1 when a step failed.
 */
function rx_setup_driver(): int {
	foreach ( array( '--step=activate', '--step=configure', '--check' ) as $arg ) {
		$process = proc_open(
			array( PHP_BINARY, __FILE__, $arg ),
			array(
				0 => STDIN,
				1 => STDOUT,
				2 => STDERR,
			),
			$pipes
		);
		if ( ! is_resource( $process ) ) {
			fwrite( STDERR, '[rx setup] Could not start ' . PHP_BINARY . ' ' . $arg . PHP_EOL );
			return 1;
		}
		$code = proc_close( $process );
		echo PHP_EOL;
		if ( 0 !== $code ) {
			fwrite( STDERR, '[rx setup] Stopped: ' . $arg . ' exited with code ' . $code . '.' . PHP_EOL );
			return 1;
		}
	}

	echo 'Setup complete: plugins active, ', RX_SETUP_STYLESHEET, ' active, options set and verified.', PHP_EOL;
	return 0;
}

/*
 * Dispatch only when this file is the script being run, so a test script can
 * require it for its functions without starting the setup.
 */
$rx_setup_entry = realpath( (string) ( $_SERVER['SCRIPT_FILENAME'] ?? '' ) );
if ( false === $rx_setup_entry || rx_guard_norm_path( $rx_setup_entry ) !== rx_guard_norm_path( __FILE__ ) ) {
	return;
}

$rx_setup_arg = $_SERVER['argv'][1] ?? '';

if ( '' === $rx_setup_arg ) {
	exit( rx_setup_driver() );
}
if ( '--check' === $rx_setup_arg ) {
	exit( rx_setup_step( 'check' ) );
}
if ( '--step=activate' === $rx_setup_arg ) {
	exit( rx_setup_step( 'activate' ) );
}
if ( '--step=configure' === $rx_setup_arg ) {
	exit( rx_setup_step( 'configure' ) );
}

fwrite( STDERR, 'Usage: php setup.php [--check]' . PHP_EOL );
exit( 2 );
