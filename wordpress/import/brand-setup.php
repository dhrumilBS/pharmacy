<?php
/**
 * Applies the final brand ("Healthray") to the local WordPress install:
 * the Site Title and Yoast's organisation name and logo.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\brand-setup.php
 *
 * Re-runnable. The theme reads the name from the Site Title (rx_brand()), so
 * titles, the footer, logo alt text and the demo slip code follow it. Yoast
 * gets the 512px heart icon as the organisation logo: search engines need a
 * raster logo, and the SVG logo (with its tagline) is a poor fit for the schema.
 *
 * Local only (lib/guard.php).
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

const RX_BRAND = 'Healthray';

update_option( 'blogname', RX_BRAND );
echo 'Site Title: ' . RX_BRAND . PHP_EOL;

if ( class_exists( 'WPSEO_Options' ) ) {
	$file = dirname( __DIR__ ) . '/themes/pharmacy/assets/img/favicon-512.png';
	$ids  = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_rx_brand_icon',
			'meta_value'     => '1',
		)
	);
	$id   = $ids ? (int) $ids[0] : 0;
	if ( ! $id && is_file( $file ) ) {
		$tmp = wp_tempnam( 'healthray-icon.png' );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => 'healthray-icon.png', 'tmp_name' => $tmp ), 0, RX_BRAND );
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, 'Logo upload failed: ' . $id->get_error_message() . PHP_EOL );
			exit( 1 );
		}
		update_post_meta( $id, '_rx_brand_icon', '1' );
		update_post_meta( $id, '_wp_attachment_image_alt', RX_BRAND . ' logo' );
	}
	if ( $id ) {
		WPSEO_Options::set( 'company_name', RX_BRAND );
		WPSEO_Options::set( 'website_name', RX_BRAND );
		WPSEO_Options::set( 'company_logo', (string) wp_get_attachment_url( $id ) );
		WPSEO_Options::set( 'company_logo_id', $id );
		echo "Yoast organisation: " . RX_BRAND . ", logo attachment #$id" . PHP_EOL;
	}
}
echo 'Done.' . PHP_EOL;
