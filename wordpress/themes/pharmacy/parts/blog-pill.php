<?php
/**
 * Blog topic pill. Port of blog_cat_pill() (partials/blog.php).
 *
 * $args:
 * - topic string Topic key, a key of RX_TOPICS (for example
 *                rx_blog_post_topic( $post )). An empty or unknown key
 *                prints nothing.
 * - link  bool   true: a link to the topic view /blog/{topic}/
 *                (rx_blog_topic_url(), replacing blog/index.php?topic=x).
 *                false (default): a plain <span>, as on the cards.
 *
 * Output:
 *   <span class="b-pill b-pill-{tone}">{name}</span>
 *   <a class="b-pill b-pill-{tone}" href="…/blog/{topic}/">{name}</a>
 *
 * Used by parts/blog-card.php (span) and the single post's meta bar (link):
 *   get_template_part( 'parts/blog-pill', null, array( 'topic' => rx_blog_post_topic(), 'link' => true ) );
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_pill_args  = isset( $args ) && is_array( $args ) ? $args : array();
$rx_pill_topic = isset( $rx_pill_args['topic'] ) && is_string( $rx_pill_args['topic'] ) ? $rx_pill_args['topic'] : '';

if ( defined( 'RX_TOPICS' ) && isset( RX_TOPICS[ $rx_pill_topic ] ) ) {
	$rx_pill_class = 'b-pill b-pill-' . RX_TOPICS[ $rx_pill_topic ][1];
	$rx_pill_name  = RX_TOPICS[ $rx_pill_topic ][0];
	$rx_pill_url   = empty( $rx_pill_args['link'] ) ? '' : rx_blog_topic_url( $rx_pill_topic );

	if ( '' !== $rx_pill_url ) {
		echo '<a class="' . esc_attr( $rx_pill_class ) . '" href="' . esc_url( $rx_pill_url ) . '">' . esc_html( $rx_pill_name ) . '</a>';
	} else {
		echo '<span class="' . esc_attr( $rx_pill_class ) . '">' . esc_html( $rx_pill_name ) . '</span>';
	}
}
