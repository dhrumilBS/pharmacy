<?php
/**
 * Pure formatting helpers: rx_inr, rx_join, rx_barcode_svg, rx_legal_mark.
 *
 * Ports of the PHP site's inr() and barcode_svg() (partials/config.php),
 * pricing_join() (partials/pricing-data.php) and the $legal_mark closure
 * (partials/legal-layout.php). Output is byte-for-byte the same.
 *
 * No WordPress calls: the test bridge loads this file without WordPress.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;

/**
 * Indian rupee format with lakh/crore grouping: rx_inr( 1092480 ) gives "₹10,92,480".
 *
 * Rounds to a whole rupee first. Port of inr().
 *
 * @param int|float $n Amount in rupees.
 * @return string Formatted amount, for example "₹999", "₹1,000" or "₹1,00,000".
 */
function rx_inr( int|float $n ): string {
	$s = (string) (int) round( $n );
	if ( strlen( $s ) <= 3 ) {
		return '₹' . $s;
	}
	$rest = preg_replace( '/\B(?=(\d{2})+$)/', ',', substr( $s, 0, -3 ) );
	return '₹' . $rest . ',' . substr( $s, -3 );
}

/**
 * Join a list as prose: "a", "a and b", "a, b and c".
 *
 * Port of pricing_join(). No serial comma.
 *
 * @param string[] $items Items in order.
 * @param string   $word  Last joining word, usually 'and' or 'or'.
 * @return string Joined text ('' for an empty list).
 */
function rx_join( array $items, string $word = 'and' ): string {
	if ( count( $items ) < 2 ) {
		return implode( '', $items );
	}
	$last = array_pop( $items );
	return implode( ', ', $items ) . ' ' . $word . ' ' . $last;
}

/**
 * Decorative barcode as crisp inline SVG.
 *
 * Bars are derived from $text, so the same text always draws the same
 * barcode. Visual only (not a scannable symbology); always aria-hidden.
 * Port of barcode_svg(): same bars, same markup.
 *
 * @param string $text   Text the bar widths are derived from (never printed).
 * @param int    $height Bar height in SVG units.
 * @param string $class  Class attribute of the <svg>.
 * @return string SVG markup.
 */
function rx_barcode_svg( string $text, int $height = 40, string $class = 'barcode' ): string {
	$bars    = '';
	$x       = 0;
	$h       = crc32( $text );
	$pattern = array( 1, 1, 2, 1, 3, 1, 1, 2, 1, 4, 2, 1 ); // Quiet start.
	for ( $i = 0; $i < strlen( $text ) * 4 + 18; $i++ ) {
		$h         = ( $h * 1103515245 + 12345 ) & 0x7fffffff;
		$pattern[] = 1 + ( $h >> 16 ) % 4;
	}
	$pattern = array_merge( $pattern, array( 1, 2, 1, 1, 3, 1 ) ); // Stop.
	foreach ( $pattern as $i => $w ) {
		if ( 0 === $i % 2 ) {
			$bars .= '<rect x="' . $x . '" width="' . $w . '" height="' . $height . '"/>';
		}
		$x += $w;
	}
	return '<svg class="' . htmlspecialchars( $class, ENT_QUOTES, 'UTF-8' ) . '" viewBox="0 0 ' . $x . ' ' . $height . '" preserveAspectRatio="none" aria-hidden="true" focusable="false">' . $bars . '</svg>';
}

/**
 * Wrap every [bracketed placeholder] in legal HTML in <span class="tbc" data-confirm>,
 * so ?review outlines what still needs owner or counsel sign-off.
 *
 * Same regex as $legal_mark in partials/legal-layout.php: brackets holding at
 * least two characters and no brackets or angle brackets. Run it after
 * shortcodes have been resolved, so only unregistered placeholders remain.
 *
 * @param string $html Section HTML.
 * @return string HTML with placeholders wrapped; $html unchanged if the regex
 *                fails (for example on invalid UTF-8).
 */
function rx_legal_mark( string $html ): string {
	$out = preg_replace( '/\[([^\]\[<>]{2,})\]/u', '<span class="tbc" data-confirm>[$1]</span>', $html );
	return $out ?? $html;
}
