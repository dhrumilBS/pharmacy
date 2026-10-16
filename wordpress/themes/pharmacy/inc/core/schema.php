<?php
/**
 * Pure schema builders: rx_faq_schema.
 *
 * Port of the FAQPage JSON-LD the PHP site prints in faq.php,
 * features/index.php, partials/feature-page.php and partials/blog.php
 * (blog_end()): confirmed answers only, Question name = the question text,
 * Answer text = trim( strip_tags( answer HTML ) ).
 *
 * No WordPress calls: the test bridge loads this file without WordPress.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;

/**
 * FAQPage node built from FAQ rows, for Yoast's schema graph.
 *
 * Items use the SCF FAQ row shape: [ 'q' => string, 'a_html' => string,
 * 'confirmed' => bool|int|string ]. The caller resolves shortcodes in both
 * texts first (do_shortcode(), not wp_kses_post(), so a bare "&" is not turned
 * into "&amp;"); this function only strips tags and trims.
 *
 * - Only items whose `confirmed` is true, 1 or '1' are used. A missing flag,
 *   false, 0, '0', '' or any other value leaves the item out (it is flagged
 *   with data-confirm on the page instead).
 * - Question name is the question text, trimmed. Tags are not stripped: the
 *   question is a plain-text field printed escaped, so the literal text is
 *   what the visitor sees.
 * - Answer text is trim( strip_tags( a_html ) ), as on the PHP site.
 *   Entities are not decoded: the PHP site's JSON-LD keeps them as written
 *   (strip_tags() leaves "&amp;" as "&amp;").
 * - An item whose question or answer text is empty is skipped, the same way
 *   parts/faq-list skips items that render empty (plan-dependent FAQs). The
 *   schema never lists a question the page does not show.
 * - Items keep their original order. Non-array items and non-scalar texts
 *   count as empty.
 *
 * The node has no '@context' (the graph wrapper supplies it) and no '@id'
 * (the caller can add one, for example canonical URL + '#faq').
 *
 * @param array $items FAQ rows in page order.
 * @return array|null FAQPage node, or null when no confirmed item with text is left.
 */
function rx_faq_schema( array $items ): ?array {
	$entities = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$confirmed = $item['confirmed'] ?? null;
		if ( true !== $confirmed && 1 !== $confirmed && '1' !== $confirmed ) {
			continue;
		}

		$question = trim( rx_schema_text( $item['q'] ?? '' ) );
		$answer   = trim( strip_tags( rx_schema_text( $item['a_html'] ?? '' ) ) );
		if ( '' === $question || '' === $answer ) {
			continue;
		}

		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}

	if ( ! $entities ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}

/**
 * A field value as a string: strings as they are, numbers cast, anything
 * else (null, bool, array, object) as ''.
 *
 * @param mixed $value Raw field value.
 * @return string
 */
function rx_schema_text( mixed $value ): string {
	if ( is_string( $value ) ) {
		return $value;
	}
	if ( is_int( $value ) || is_float( $value ) ) {
		return (string) $value;
	}
	return '';
}
