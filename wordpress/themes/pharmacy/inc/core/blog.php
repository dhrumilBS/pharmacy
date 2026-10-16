<?php
/**
 * Pure blog helpers: rx_safe_post_slug, rx_blog_toc.
 *
 * rx_blog_toc() is a port of the "On this page" logic in blog_end()
 * (partials/blog.php): same heading regex, same slug rule, same id insertion.
 * rx_safe_post_slug() keeps post slugs clear of the /blog/{topic}/ views.
 *
 * No WordPress calls: the test bridge loads this file without WordPress.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;

/**
 * Keep a post slug from being the same as a blog topic key.
 *
 * Topic views live at /blog/{topic}/ and posts at /blog/{post-slug}/, so a
 * post slug equal to a topic key would be unreachable. Such a slug gets
 * "-guide" appended: "expiry" becomes "expiry-guide". Any other slug is
 * returned unchanged, byte for byte.
 *
 * - Matching is case-insensitive after trimming ("Expiry " matches "expiry").
 *   A matched slug is trimmed before "-guide" is appended.
 * - If "{slug}-guide" is itself a topic key, "-guide" is appended again until
 *   the result is not a topic key, so the result is never a topic key and a
 *   second call returns it unchanged (idempotent).
 * - Whether the result is already taken by another post is not checked here;
 *   the wp_unique_post_slug filter in inc/blog.php adds a numeric suffix.
 *
 * @param string $slug   Post slug, usually already sanitised by WordPress.
 * @param array  $topics Topic keys, either as a list ( [ 'expiry', 'purchase' ] )
 *                       or as a map keyed by topic ( RX_TOPICS: key => [ name, tone ] ).
 *                       A list uses its values, any other array its keys.
 *                       Non-scalar and empty keys are ignored.
 * @return string The slug, or the slug with "-guide" appended.
 */
function rx_safe_post_slug( string $slug, array $topics ): string {
	$keys = array();
	foreach ( ( array_is_list( $topics ) ? $topics : array_keys( $topics ) ) as $key ) {
		if ( ! is_scalar( $key ) ) {
			continue;
		}
		$key = strtolower( trim( (string) $key ) );
		if ( '' !== $key ) {
			$keys[ $key ] = true;
		}
	}

	$out = $slug;
	// Terminates: each pass makes the slug longer and there are finitely many keys.
	while ( isset( $keys[ strtolower( trim( $out ) ) ] ) ) {
		$out = trim( $out ) . '-guide';
	}
	return $out;
}

/**
 * Give every article <h2> an id and collect them for the "On this page" list.
 *
 * Port of blog_end():
 * - Heading regex: /<h2(?![^>]*\bid=)([^>]*)>(.*?)<\/h2>/s. An <h2> whose tag
 *   already contains "id=" (as blog_end() reads it) is left unchanged and gets
 *   no TOC entry.
 * - Slug rule: strip_tags(), strtolower(), every run of characters outside
 *   a-z0-9 becomes "-", then "-" is trimmed from both ends. An empty result
 *   (or "0", as with blog_end()'s ?:) becomes "section". Entities are not
 *   decoded, so "&amp;" contributes "amp", as on the PHP site.
 * - Duplicates get "-2", "-3", … in document order ("faq", "faq-2").
 * - The id is inserted as the first attribute: <h2 id="slug" class="…">.
 * - The TOC text is strip_tags() of the heading's inner HTML, unescaped and
 *   untrimmed, exactly what blog_end() printed through e().
 *
 * One addition: quoted ids already present in $html (on any element, for
 * example an <h2 id="x"> that is skipped) are reserved, so a generated id
 * never repeats one. The PHP site's posts carry no ids in their article
 * HTML, so their output is identical to blog_end().
 *
 * @param string $html Article HTML.
 * @return array{0: string, 1: list<array{id: string, label: string}>}
 *         [ html with ids, toc ]. Each TOC entry is [ 'id' => …, 'label' => … ]
 *         in document order. If the regex fails (for example on a PCRE limit),
 *         the html comes back unchanged with an empty TOC.
 */
function rx_blog_toc( string $html ): array {
	$toc  = array();
	$used = array();

	// Reserve existing quoted id values ( id="x" or id='x', any element, any case ).
	if ( preg_match_all( '/<[a-z][^>]*?\sid\s*=\s*(["\'])([^"\'<>]*)\1/i', $html, $found ) ) {
		foreach ( $found[2] as $existing ) {
			$used[ $existing ] = true;
		}
	}

	$out = preg_replace_callback(
		'/<h2(?![^>]*\bid=)([^>]*)>(.*?)<\/h2>/s',
		static function ( array $m ) use ( &$toc, &$used ): string {
			$text = strip_tags( $m[2] );
			$id   = trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( $text ) ), '-' );
			// Same as blog_end()'s "?: 'section'": an empty slug and the slug "0" both become "section".
			$id   = ( '' !== $id && '0' !== $id ) ? $id : 'section';
			$base = $id;
			$n    = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $n++;
			}
			$used[ $id ] = true;
			$toc[]       = array(
				'id'    => $id,
				'label' => $text,
			);
			return '<h2 id="' . $id . '"' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$html
	);

	if ( null === $out ) {
		return array( $html, array() );
	}
	return array( $out, $toc );
}
