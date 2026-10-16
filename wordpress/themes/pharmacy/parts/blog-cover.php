<?php
/**
 * Blog cover: a crop of the product screen the post is about, on a panel
 * tinted with the topic colour. Port of blog_cover() (partials/blog.php).
 *
 * $args (all optional):
 * - post    WP_Post|int|null The post. Default: the current post.
 * - loading string           'lazy' (default) or 'eager'. Cards use lazy, the
 *                            featured card and the single post's cover eager.
 *
 * Reads the post's fields (acf-json/group_rx_post.json):
 * - topic         Tone of the panel (RX_TOPICS[ topic ][1] → .bc-{tone});
 *                 'slate' when the post has no valid topic.
 * - cover_screen  Screen file name, e.g. 'v2-03-expiry' → assets/img/screens/v2-03-expiry.svg.
 *                 The PHP site stored '03-expiry' and added 'v2-'; the field holds the full name.
 *                 Empty or not a plain file name: the panel prints without an image.
 * - cover_zoom    --z, default 1.3.
 * - cover_focus   --f, default '24% 33%'. Only digits, letters, '.', '%', '-' and
 *                 spaces are accepted (it ends up in a style attribute).
 *
 * Output (same markup as blog_cover()):
 *   <span class="bc-cover bc-{tone}" aria-hidden="true"><span class="bc-frame">
 *   <img src="…/screens/{screen}.svg" alt="" width="1440" height="900" loading="lazy"
 *   decoding="async" style="--z:1.3;--f:24% 33%"></span></span>
 *
 * The image is printed directly (not through the_content), so no WordPress
 * image filter changes its attributes.
 *
 * Usage:
 *   get_template_part( 'parts/blog-cover', null, array( 'post' => $post, 'loading' => 'eager' ) );
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_cover_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_cover_post = get_post( $rx_cover_args['post'] ?? null );

if ( $rx_cover_post instanceof WP_Post ) {
	/*
	 * An SCF value of the post, or its raw post meta when SCF is inactive
	 * (all cover fields are plain scalars, stored under their own names).
	 */
	$rx_cover_get = static function ( string $name, int $post_id ) {
		$value = rx_field( $name, $post_id );
		return null === $value ? get_post_meta( $post_id, $name, true ) : $value;
	};

	$rx_cover_topic = rx_blog_post_topic( $rx_cover_post );
	$rx_cover_tone  = '' !== $rx_cover_topic ? RX_TOPICS[ $rx_cover_topic ][1] : 'slate';

	$rx_cover_loading = ( $rx_cover_args['loading'] ?? 'lazy' ) === 'eager' ? 'eager' : 'lazy';

	$rx_cover_screen = $rx_cover_get( 'cover_screen', (int) $rx_cover_post->ID );
	$rx_cover_screen = is_string( $rx_cover_screen ) && preg_match( '/^[a-z0-9][a-z0-9-]*$/', $rx_cover_screen ) ? $rx_cover_screen : '';

	$rx_cover_zoom = $rx_cover_get( 'cover_zoom', (int) $rx_cover_post->ID );
	$rx_cover_zoom = is_numeric( $rx_cover_zoom ) && (float) $rx_cover_zoom > 0 ? (float) $rx_cover_zoom : 1.3;

	$rx_cover_focus = $rx_cover_get( 'cover_focus', (int) $rx_cover_post->ID );
	$rx_cover_focus = is_string( $rx_cover_focus ) ? trim( $rx_cover_focus ) : '';
	if ( '' === $rx_cover_focus || ! preg_match( '/^[a-z0-9.%\s-]+$/i', $rx_cover_focus ) ) {
		$rx_cover_focus = '24% 33%';
	}

	echo '<span class="bc-cover bc-' . esc_attr( $rx_cover_tone ) . '" aria-hidden="true"><span class="bc-frame">';
	if ( '' !== $rx_cover_screen ) {
		echo '<img src="' . esc_url( rx_asset( 'img/screens/' . $rx_cover_screen . '.svg' ) ) . '" alt="" width="1440" height="900"'
			. ' loading="' . esc_attr( $rx_cover_loading ) . '" decoding="async"'
			. ' style="' . esc_attr( '--z:' . $rx_cover_zoom . ';--f:' . $rx_cover_focus ) . '">';
	}
	echo '</span></span>';
}
