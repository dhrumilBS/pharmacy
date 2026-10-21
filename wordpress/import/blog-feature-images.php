<?php
/**
 * Gives the six guides from the first import a 2:1 featured image, made by content/images/gen-feature-images.py
 * and rendered to PNG (wordpress/tools/render-image.mjs, 1200x600 at 2x). The guides written later
 * (blog-hospital-pharmacy.php, blog-distributor-bill.php) set their own.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-feature-images.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$guides = array(
	'cut-expiry-loss'          => 'A weekly expiry check: items within 30 days, 31 to 60 days and beyond 60 days',
	'min-max-stock'            => 'A minimum stock worked out from daily sales, delivery days and a cushion',
	'check-distributor-bill'   => 'Six checks before saving a distributor bill: batch, expiry, quantity, free quantity, rate and MRP',
	'desktop-vs-browser'       => 'A general comparison of desktop and browser-based software',
	'staff-logins-roles'       => 'Example staff roles and what each can see',
	'switch-pharmacy-software' => 'A four-week plan for switching without stopping billing',
);

foreach ( $guides as $slug => $alt ) {
	$post = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'name' => $slug ) );
	if ( ! $post ) {
		echo "skip (no post): {$slug}" . PHP_EOL;
		continue;
	}
	$post  = $post[0];
	$file  = __DIR__ . '/content/images/' . $slug . '-feature.png';
	$key   = $slug . '-feature';
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $key ) );
	$id    = $found ? (int) $found[0] : 0;
	if ( ! $id ) {
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), $post->ID, $post->post_title );
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, "Upload failed ({$slug}): " . $id->get_error_message() . PHP_EOL );
			continue;
		}
		update_post_meta( $id, '_rx_src', $key );
	} else {
		$path = get_attached_file( $id );
		copy( $file, $path );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	set_post_thumbnail( $post->ID, $id );
	echo "ok: {$slug} -> attachment #{$id}" . PHP_EOL;
}
