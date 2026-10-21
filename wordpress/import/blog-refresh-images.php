<?php
/**
 * Re-attaches the feature image of every blog (content/images/<slug>-feature.png, made by gen-playful-images.py and
 * scenes.py), so a re-rendered picture replaces the old one without touching the posts, and copies the category images
 * into the theme. Posts without a PNG are left alone.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-refresh-images.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dir = __DIR__ . '/content/images/';

foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $post ) {
	$file = $dir . $post->post_name . '-feature.png';
	if ( ! is_readable( $file ) ) {
		continue;
	}
	$key   = $post->post_name . '-feature-playful';
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $key ) );
	$id    = $found ? (int) $found[0] : 0;
	if ( ! $id ) {
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $post->post_title );
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, 'Upload failed (' . $post->post_name . '): ' . $id->get_error_message() . PHP_EOL );
			continue;
		}
		update_post_meta( $id, '_rx_src', $key );
	} else {
		$path = get_attached_file( $id );
		copy( $file, $path );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', 'Illustration for: ' . $post->post_title );
	wp_update_post( array( 'ID' => $id, 'post_title' => $post->post_title ) );
	set_post_thumbnail( $post->ID, $id );
	echo "ok {$post->post_name}" . PHP_EOL;
}

$dest = dirname( __DIR__ ) . '/themes/pharmacy/assets/img/topics/';
foreach ( array( 'expiry', 'purchase', 'switching', 'staff', 'buying' ) as $topic ) {
	copy( $dir . 'cat-' . $topic . '.png', $dest . $topic . '.png' );
}
echo 'category images copied' . PHP_EOL;
