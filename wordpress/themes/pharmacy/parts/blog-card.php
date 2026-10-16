<?php
/**
 * Blog post card. Port of blog_card() (partials/blog.php).
 *
 * $args (all optional):
 * - post    WP_Post|int|null The post. Default: the current post.
 * - variant string           'card' (default): grid card with an <h3> title.
 *                            'feature': large card at the top of the hub, <h2>
 *                            title, eager cover and a "Read the guide" link.
 *                            'row': compact list item (<li class="bc-row">); the
 *                            caller prints the surrounding <ul>/<ol>.
 *                            Any other value is treated as 'card'.
 *
 * Reads (acf-json/group_rx_post.json): the post title (post_title as stored,
 * without the_title filters, so wptexturize can't change the copy), the
 * permalink, the publish date, and the fields `dek`, `read_time` and `topic`
 * (plus the cover fields through parts/blog-cover.php).
 *
 * Markup, as blog_card():
 *   card/feature:
 *     <article class="bc bc-{variant}">
 *       <a class="bc-link" href="{url}" tabindex="-1" aria-hidden="true">{cover}</a>
 *       <div class="bc-body">{pill}<h3 class="bc-title"><a href="{url}">{title}</a></h3>
 *         <p class="bc-dek">{dek}</p>{meta}[feature: <a class="bc-more" …>Read the guide …</a>]</div>
 *     </article>
 *   row:
 *     <li class="bc-row"><a href="{url}"><span class="bc-row-t">{title}</span>{meta}</a></li>
 *   meta:
 *     <p class="bc-meta"><time datetime="Y-m-d">j M Y</time><span aria-hidden="true">·</span>N min read</p>
 *
 * Elements whose value is empty are left out (an empty dek, a missing or
 * zero read time, a post without a valid topic has no pill).
 *
 * Usage (the post list comes from rx_blog_posts()):
 *   get_template_part( 'parts/blog-card', null, array( 'post' => $feat, 'variant' => 'feature' ) );
 *   foreach ( $grid as $p ) { get_template_part( 'parts/blog-card', null, array( 'post' => $p ) ); }
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_card_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_card_post = get_post( $rx_card_args['post'] ?? null );

if ( $rx_card_post instanceof WP_Post ) {
	$rx_card_variant = $rx_card_args['variant'] ?? 'card';
	$rx_card_variant = in_array( $rx_card_variant, array( 'card', 'feature', 'row' ), true ) ? $rx_card_variant : 'card';

	$rx_card_id    = (int) $rx_card_post->ID;
	$rx_card_url   = esc_url( (string) get_permalink( $rx_card_post ) );
	$rx_card_title = esc_html( $rx_card_post->post_title );

	// SCF value, or the raw post meta when SCF is inactive (scalar fields).
	$rx_card_dek = rx_field( 'dek', $rx_card_id );
	$rx_card_dek = rx_text( null === $rx_card_dek ? get_post_meta( $rx_card_id, 'dek', true ) : $rx_card_dek );

	$rx_card_mins = rx_field( 'read_time', $rx_card_id );
	$rx_card_mins = (int) ( null === $rx_card_mins ? get_post_meta( $rx_card_id, 'read_time', true ) : $rx_card_mins );

	// Meta line: date, then read time.
	$rx_card_iso  = '0000-00-00 00:00:00' !== $rx_card_post->post_date ? (string) mysql2date( 'Y-m-d', $rx_card_post->post_date ) : '';
	$rx_card_date = rx_blog_date( $rx_card_post );
	$rx_card_meta = array();
	if ( '' !== $rx_card_iso && '' !== $rx_card_date ) {
		$rx_card_meta[] = '<time datetime="' . esc_attr( $rx_card_iso ) . '">' . esc_html( $rx_card_date ) . '</time>';
	}
	if ( $rx_card_mins > 0 ) {
		/* translators: %d: reading time in minutes. */
		$rx_card_meta[] = esc_html( sprintf( __( '%d min read', 'pharmacy' ), $rx_card_mins ) );
	}
	$rx_card_meta = $rx_card_meta ? '<p class="bc-meta">' . implode( '<span aria-hidden="true">·</span>', $rx_card_meta ) . '</p>' : '';

	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every piece is escaped where it is built above.
	if ( 'row' === $rx_card_variant ) {
		echo '<li class="bc-row"><a href="' . $rx_card_url . '"><span class="bc-row-t">' . $rx_card_title . '</span>' . $rx_card_meta . '</a></li>';
	} else {
		$rx_card_h = 'feature' === $rx_card_variant ? 'h2' : 'h3';

		echo '<article class="bc bc-' . $rx_card_variant . '">'
			. '<a class="bc-link" href="' . $rx_card_url . '" tabindex="-1" aria-hidden="true">';
		get_template_part(
			'parts/blog-cover',
			null,
			array(
				'post'    => $rx_card_post,
				'loading' => 'feature' === $rx_card_variant ? 'eager' : 'lazy',
			)
		);
		echo '</a><div class="bc-body">';
		get_template_part( 'parts/blog-pill', null, array( 'topic' => rx_blog_post_topic( $rx_card_post ) ) );
		echo '<' . $rx_card_h . ' class="bc-title"><a href="' . $rx_card_url . '">' . $rx_card_title . '</a></' . $rx_card_h . '>'
			. ( '' !== $rx_card_dek ? '<p class="bc-dek">' . $rx_card_dek . '</p>' : '' )
			. $rx_card_meta;
		if ( 'feature' === $rx_card_variant ) {
			echo '<a class="bc-more" href="' . $rx_card_url . '">' . esc_html__( 'Read the guide', 'pharmacy' ) . ' <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>';
		}
		echo '</div></article>';
	}
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
}
