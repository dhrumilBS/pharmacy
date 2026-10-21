<?php
/**
 * Creates (or updates) the "Home v2" page: template templates/home-v2.php, page key `home-v2`, slug /home-v2/, with its
 * search title and description from inc/home2-data.php. The current home page is not touched.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\home-v2-setup.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

$data     = rx_home2_data();
$existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_key', 'meta_value' => 'home-v2' ) );
$page     = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => 'Home v2',
	'post_name'    => 'home-v2',
	'post_content' => '',
);
if ( $existing ) {
	$page['ID'] = (int) $existing[0];
	$id         = wp_update_post( wp_slash( $page ), true );
} else {
	$id = wp_insert_post( wp_slash( $page ), true );
}
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'FAILED: ' . $id->get_error_message() . PHP_EOL );
	exit( 1 );
}
update_post_meta( $id, '_wp_page_template', 'templates/home-v2.php' );
update_post_meta( $id, '_rx_key', 'home-v2' );
update_post_meta( $id, '_yoast_wpseo_title', $data['seo_title'] );
update_post_meta( $id, '_yoast_wpseo_metadesc', $data['seo_desc'] );
update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', '1' ); // A draft of the next home page: not for search engines until it replaces the current one.
rx_page_map( true );

echo ( $existing ? 'Updated' : 'Created' ) . ' Home v2: ' . get_permalink( $id ) . PHP_EOL;
