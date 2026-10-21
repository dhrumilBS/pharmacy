<?php
/**
 * Creates (or updates) the four solution pages from inc/solutions-data.php and gives each the Solution template,
 * its page key (`_rx_key`, which the menus and rx_url() use) and its search title and description.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\solutions-setup.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

foreach ( rx_solutions_data() as $key => $sol ) {
	$existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_key', 'meta_value' => $key ) );
	$data     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $sol['title'],
		'post_name'    => $sol['slug'],
		'post_content' => '',
		'menu_order'   => 20,
	);
	if ( $existing ) {
		$data['ID'] = (int) $existing[0];
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
	}
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, 'FAILED ' . $key . ': ' . $id->get_error_message() . PHP_EOL );
		continue;
	}
	update_post_meta( $id, '_wp_page_template', 'templates/solution.php' );
	update_post_meta( $id, '_rx_key', $key );
	update_post_meta( $id, '_yoast_wpseo_title', $sol['seo_title'] );
	update_post_meta( $id, '_yoast_wpseo_metadesc', $sol['seo_desc'] );
	echo ( $existing ? 'Updated' : 'Created' ) . " {$key}: " . get_permalink( $id ) . PHP_EOL;
}

rx_page_map( true );
