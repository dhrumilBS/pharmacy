<?php
/**
 * Theme setup: supports, menu locations, textdomain, SCF JSON paths,
 * options page, lang and body class filters.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports, menu locations and the textdomain.
 */
function rx_setup_theme(): void {
	load_theme_textdomain( 'pharmacy', get_template_directory() . '/languages' );

	// Yoast fills the <title> through WordPress' title-tag support.
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'script', 'style' ) );

	register_nav_menus(
		array(
			'primary'          => __( 'Main navigation (mega menu)', 'pharmacy' ),
			'footer_features'  => __( 'Footer: Features', 'pharmacy' ),
			'footer_solutions' => __( 'Footer: Solutions', 'pharmacy' ),
			'footer_resources' => __( 'Footer: Resources', 'pharmacy' ),
			'footer_company'   => __( 'Footer: Company', 'pharmacy' ),
			'legal'            => __( 'Footer: Legal links', 'pharmacy' ),
		)
	);
}
add_action( 'after_setup_theme', 'rx_setup_theme' );

/**
 * Folder holding the SCF field groups as JSON (versioned in Git).
 *
 * Always the parent theme's acf-json/, so the child theme stays empty.
 */
function rx_acf_json_dir(): string {
	return get_template_directory() . '/acf-json';
}

/**
 * Saves field group edits to the theme's acf-json/ folder.
 *
 * @param string $path Default save path (the active theme's acf-json/).
 */
function rx_acf_save_json( $path ): string {
	return rx_acf_json_dir();
}
add_filter( 'acf/settings/save_json', 'rx_acf_save_json' );

/**
 * Loads field groups from the theme's acf-json/ folder instead of the
 * default (the active child theme's acf-json/).
 *
 * @param array $paths Load paths.
 */
function rx_acf_load_json( $paths ): array {
	$paths = (array) $paths;
	unset( $paths[0] );
	$paths[] = rx_acf_json_dir();
	return array_values( array_unique( $paths ) );
}
add_filter( 'acf/settings/load_json', 'rx_acf_load_json' );

/**
 * Registers the "Site settings" options page (Site_Settings).
 *
 * Values are stored under the SCF post id "options".
 */
function rx_register_options_page(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Site settings', 'pharmacy' ),
			'menu_title' => __( 'Site settings', 'pharmacy' ),
			'menu_slug'  => 'rx-settings',
			'capability' => 'manage_options',
			'redirect'   => false,
			'post_id'    => 'options',
			'icon_url'   => 'dashicons-admin-generic',
		)
	);
}
add_action( 'acf/init', 'rx_register_options_page' );

/**
 * Prints the same <html> attributes as the PHP site: lang="en-IN" only.
 *
 * Runs late so nothing added earlier (locale, dir) survives.
 *
 * @param string $output Default attributes.
 */
function rx_language_attributes( $output ): string {
	return 'lang="en-IN"';
}
add_filter( 'language_attributes', 'rx_language_attributes', 999 );

/**
 * Reduces the body classes to the PHP site's "page-{key}".
 *
 * Keeps only "admin-bar" and "logged-in" (needed by the admin bar CSS for
 * logged-in editors) and drops every other WordPress or plugin class.
 * Runs late so classes added by plugins are dropped too.
 *
 * @param string[] $classes Body classes.
 */
function rx_body_class( $classes ): array {
	$classes = (array) $classes;
	$key     = sanitize_html_class( rx_page_key() );
	$out     = array( 'page-' . ( '' !== $key ? $key : 'generic' ) );
	if ( 'home-blocks' === $key ) {
		$out[] = 'page-home'; // The block-editor copy of the home page shares home.css's `.page-home` rules.
	}

	foreach ( array( 'admin-bar', 'logged-in' ) as $keep ) {
		if ( in_array( $keep, $classes, true ) ) {
			$out[] = $keep;
		}
	}

	return $out;
}
add_filter( 'body_class', 'rx_body_class', 999 );
