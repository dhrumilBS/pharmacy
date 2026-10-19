<?php
/**
 * Front-end cleanup: WordPress extras, jQuery and CF7 assets off.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * Contact Form 7
 * --------------
 * The booking forms are CF7 forms (inc/forms.php). CF7 enqueues its script
 * (with the swv dependency and the inline `wpcf7` config) and its stylesheet
 * on every front-end page unless these filters say no. The stylesheet is
 * never needed (assets/css/forms.css styles the forms); the script is loaded
 * by rx_form_html() on the pages that print a form. The admin screens
 * enqueue their own assets and are not affected.
 */
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

/*
 * Front-end only
 * --------------
 * Everything below is registered from `template_redirect`, which WordPress
 * fires only for front-end template loads: never in wp-admin (including the
 * block editor), admin-ajax, cron, wp-login.php or REST requests (those are
 * served and end at `parse_request`). rx_cleanup_is_front_end() checks the
 * same cases again and also skips the Customizer preview, so the admin and
 * the editing tools keep every core default, jQuery included.
 *
 * Hook names and priorities match wp-includes/default-filters.php of the
 * installed WordPress (7.1). remove_action() on a hook core no longer adds
 * is a no-op, so the older names stay as a safety net.
 */

/**
 * Whether the current request is a visitor-facing front-end page view.
 */
function rx_cleanup_is_front_end(): bool {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_login() ) {
		return false;
	}
	if ( wp_is_serving_rest_request() || is_customize_preview() ) {
		return false;
	}
	return true;
}

/**
 * Registers the front-end cleanup for this request.
 *
 * Priority 0, so the `template_redirect` callbacks at 11 (REST and
 * shortlink headers) are removed before they run.
 */
function rx_cleanup_front_end(): void {
	if ( ! rx_cleanup_is_front_end() ) {
		return;
	}

	rx_cleanup_emoji();
	rx_cleanup_block_assets();
	rx_cleanup_head_links();
	rx_cleanup_embeds();

	// Speculative loading: no <script type="speculationrules"> in the footer.
	add_filter( 'wp_speculation_rules_configuration', '__return_null' );

	// jQuery and wp-embed: deregistered once core and plugins have enqueued.
	add_action( 'wp_enqueue_scripts', 'rx_cleanup_scripts', 100 );
}
add_action( 'template_redirect', 'rx_cleanup_front_end', 0 );

/*
 * Emoji
 * -----
 * The detection script (inline module + `wp-emoji-settings` JSON, which
 * points at s.w.org), the `wp-emoji-styles` inline CSS and the filters that
 * swap emoji for <img> tags in feeds and mail.
 */

/**
 * Removes the emoji script, styles and filters.
 */
function rx_cleanup_emoji(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

/*
 * Block and global styles
 * -----------------------
 * The theme prints no blocks, so no block CSS loads (design §10):
 * - `global-styles` (theme.json CSS) is enqueued at wp_enqueue_scripts and
 *   again at wp_footer 1 (for classic themes it is printed late and hoisted
 *   into <head> by the template output buffer); both hooks are removed.
 * - `classic-theme-styles` comes from wp_enqueue_classic_theme_styles() at
 *   wp_enqueue_scripts and at enqueue_block_assets.
 * - `wp-img-auto-sizes-contain` is the inline `img:is([sizes=auto i]…)` fix
 *   added at wp_head 0 (the wp_head 1 printer is its older form).
 * - `wp-block-library` (common block CSS), `wp-block-library-theme` and the
 *   per-block styles WordPress enqueues on demand while a block renders
 *   (`wp-block-paragraph`, …) are dequeued just before the head and the
 *   late (footer) styles are printed. wp_common_block_scripts_and_styles()
 *   stays hooked, so plugins' `enqueue_block_assets` callbacks still run.
 */

/**
 * Unhooks the global, classic-theme and image auto-sizes styles, and
 * schedules the block style dequeue.
 */
function rx_cleanup_block_assets(): void {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
	remove_action( 'enqueue_block_assets', 'wp_enqueue_classic_theme_styles' );
	remove_action( 'wp_head', 'wp_enqueue_img_auto_sizes_contain_css_fix', 0 );
	remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );

	// Head styles print at wp_head 8 (wp_print_styles fires this action first).
	add_action( 'wp_print_styles', 'rx_cleanup_dequeue_block_styles', 100 );
	// Late styles print or are captured for hoisting at wp_footer 20.
	add_action( 'wp_footer', 'rx_cleanup_dequeue_block_styles', 19 );
}

/**
 * Whether a style handle is block or global-styles CSS.
 *
 * @param string $handle Style handle.
 */
function rx_cleanup_is_block_style( string $handle ): bool {
	$handles = array(
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'wp-global-styles-placeholder',
	);

	return in_array( $handle, $handles, true )
		|| str_starts_with( $handle, 'wp-block-' )
		|| str_starts_with( $handle, 'core-block-supports' );
}

/**
 * Dequeues every queued block or global-styles stylesheet.
 */
function rx_cleanup_dequeue_block_styles(): void {
	foreach ( wp_styles()->queue as $handle ) {
		if ( rx_cleanup_is_block_style( (string) $handle ) ) {
			wp_dequeue_style( $handle );
		}
	}
}

/*
 * Head links and headers
 * ----------------------
 * Generator meta, RSD (EditURI), Windows Live Writer manifest, shortlink,
 * the REST API discovery link (plus the post's JSON alternate link), feed
 * links and adjacent-post links. The REST API itself stays available; only
 * its discovery link and the matching `Link:` response headers (REST and
 * shortlink, sent at template_redirect 11) are removed.
 */

/**
 * Removes the head links and their response headers.
 */
function rx_cleanup_head_links(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}

/*
 * oEmbed
 * ------
 * The discovery links (`application/json+oembed` and `text/xml+oembed`,
 * printed at wp_head 4 with a back-compat hook at 10) and the host script
 * `wp-embed`, which core enqueues when content holds a post embed. The
 * oEmbed REST route is left alone.
 */

/**
 * Removes oEmbed discovery and the host script.
 */
function rx_cleanup_embeds(): void {
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_filter( 'embed_oembed_html', 'wp_maybe_enqueue_oembed_host_js' );
}

/*
 * Scripts
 * -------
 * jQuery is deregistered on the front end only: the theme's rx-* scripts have
 * no dependencies, and the admin bar shown to logged-in editors needs only
 * `hoverintent-js` (core's `admin-bar` script). `jquery` is an alias of
 * `jquery-core` + `jquery-migrate`, so all three go, and a script enqueued
 * with a jQuery dependency is not printed. wp-admin is untouched.
 */

/**
 * Deregisters jQuery and the oEmbed host script for this page view.
 */
function rx_cleanup_scripts(): void {
	foreach ( array( 'jquery', 'jquery-core', 'jquery-migrate', 'wp-embed' ) as $handle ) {
		wp_deregister_script( $handle );
	}
}
