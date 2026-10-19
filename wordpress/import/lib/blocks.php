<?php
/**
 * Block markup builders for import content.
 *
 * Each function returns the serialised markup the block editor itself saves
 * for a core block (comment delimiters, wrapper element, classes), so the
 * blocks open in the editor as normal, editable blocks with no "unexpected or
 * invalid content" warning. Only blocks whose saved markup is stable are used:
 * paragraph, heading, group, columns/column, buttons/button, list/list-item,
 * image, details and shortcode.
 *
 * Rules that keep the markup valid:
 * - A block's `className` goes in the comment attributes AND on its wrapper.
 * - `anchor` is an HTML id on the wrapper (not a comment attribute).
 * - Default attributes are left out (heading level 2, list unordered).
 * - Text is passed through rx_blk_t() (plain text) or rx_blk_html() (HTML
 *   that already holds inline markup); neither adds block-level tags.
 *
 * Load after rx_bootstrap_wp(): it uses serialize_block_attributes().
 *
 * @package pharmacy
 */

PHP_SAPI === 'cli' || exit;

/**
 * Escapes plain text for block content (& < > only; quotes stay as typed).
 *
 * @param string $text Plain text.
 */
function rx_blk_t( string $text ): string {
	return htmlspecialchars( $text, ENT_NOQUOTES, 'UTF-8', false );
}

/**
 * Cleans an HTML fragment for rich-text blocks: <b> becomes <strong> (the
 * editor's Bold format), and inline marker spans the editor would drop are
 * unwrapped.
 *
 * @param string $html Inner HTML of a field.
 */
function rx_blk_html( string $html ): string {
	$html = str_replace( array( '<b>', '</b>' ), array( '<strong>', '</strong>' ), $html );
	$html = preg_replace( '~<span (?:data-confirm|class="scribble(?:-u)?")>(.*?)</span>~s', '$1', $html );
	return (string) $html;
}

/**
 * Joins the class names that are not empty.
 *
 * @param string ...$names Class names (may hold several, space separated).
 */
function rx_blk_class( string ...$names ): string {
	return trim( preg_replace( '/\s+/', ' ', implode( ' ', array_filter( $names, static fn( $n ) => '' !== trim( $n ) ) ) ) );
}

/**
 * Opening comment of a block.
 *
 * @param string $name  Block name without "core/".
 * @param array  $attrs Comment attributes.
 */
function rx_blk_open( string $name, array $attrs = array() ): string {
	return '<!-- wp:' . $name . ( $attrs ? ' ' . serialize_block_attributes( $attrs ) : '' ) . ' -->';
}

/**
 * Joins inner blocks.
 *
 * @param string|string[] $inner Block markup, or a list of it.
 */
function rx_blk_join( $inner ): string {
	return is_array( $inner ) ? implode( "\n\n", $inner ) : (string) $inner;
}

/**
 * Paragraph.
 *
 * @param string $html  Inner HTML.
 * @param string $class Extra class.
 */
function rx_blk_p( string $html, string $class = '' ): string {
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	return rx_blk_open( 'paragraph', $attrs ) . "\n<p" . ( '' !== $class ? ' class="' . $class . '"' : '' ) . '>' . $html . "</p>\n<!-- /wp:paragraph -->";
}

/**
 * Heading.
 *
 * @param int    $level 1-6 (2 is the default and is left out of the comment).
 * @param string $html  Inner HTML.
 * @param string $class Extra class.
 */
function rx_blk_h( int $level, string $html, string $class = '' ): string {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( '' !== $class ) {
		$attrs['className'] = $class;
	}
	return rx_blk_open( 'heading', $attrs ) . "\n<h{$level} class=\"" . rx_blk_class( 'wp-block-heading', $class ) . "\">{$html}</h{$level}>\n<!-- /wp:heading -->";
}

/**
 * Group (a div).
 *
 * @param string|string[] $inner  Inner blocks.
 * @param string          $class  Extra class.
 * @param string          $anchor HTML id.
 */
function rx_blk_group( $inner, string $class = '', string $anchor = '' ): string {
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	$tag   = '<div class="' . rx_blk_class( 'wp-block-group', $class ) . '"' . ( '' !== $anchor ? ' id="' . $anchor . '"' : '' ) . '>';
	return rx_blk_open( 'group', $attrs ) . "\n" . $tag . rx_blk_join( $inner ) . "</div>\n<!-- /wp:group -->";
}

/**
 * Columns.
 *
 * @param string[] $columns Markup of rx_blk_column() items.
 * @param string   $class   Extra class.
 */
function rx_blk_columns( array $columns, string $class = '' ): string {
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	return rx_blk_open( 'columns', $attrs ) . "\n" . '<div class="' . rx_blk_class( 'wp-block-columns', $class ) . '">' . rx_blk_join( $columns ) . "</div>\n<!-- /wp:columns -->";
}

/**
 * One column.
 *
 * @param string|string[] $inner Inner blocks.
 * @param string          $class Extra class.
 */
function rx_blk_column( $inner, string $class = '' ): string {
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	return rx_blk_open( 'column', $attrs ) . "\n" . '<div class="' . rx_blk_class( 'wp-block-column', $class ) . '">' . rx_blk_join( $inner ) . "</div>\n<!-- /wp:column -->";
}

/**
 * Buttons.
 *
 * @param array[] $buttons Items: [ 'text' => '', 'url' => '', 'class' => '' ] (class goes on the button wrapper).
 * @param string  $class   Extra class for the row.
 */
function rx_blk_buttons( array $buttons, string $class = '' ): string {
	$items = array();
	foreach ( $buttons as $b ) {
		$bc      = (string) ( $b['class'] ?? '' );
		$attrs   = '' !== $bc ? array( 'className' => $bc ) : array();
		$items[] = rx_blk_open( 'button', $attrs ) . "\n" . '<div class="' . rx_blk_class( 'wp-block-button', $bc ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( (string) $b['url'] ) . '">' . rx_blk_t( (string) $b['text'] ) . "</a></div>\n<!-- /wp:button -->";
	}
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	return rx_blk_open( 'buttons', $attrs ) . "\n" . '<div class="' . rx_blk_class( 'wp-block-buttons', $class ) . '">' . rx_blk_join( $items ) . "</div>\n<!-- /wp:buttons -->";
}

/**
 * List.
 *
 * @param string[] $items   Inner HTML of each item.
 * @param string   $class   Extra class.
 * @param bool     $ordered Numbered list.
 */
function rx_blk_list( array $items, string $class = '', bool $ordered = false ): string {
	$attrs = array();
	if ( $ordered ) {
		$attrs['ordered'] = true;
	}
	if ( '' !== $class ) {
		$attrs['className'] = $class;
	}
	$tag   = $ordered ? 'ol' : 'ul';
	$lis   = array_map( static fn( $html ) => rx_blk_open( 'list-item' ) . "\n<li>" . $html . "</li>\n<!-- /wp:list-item -->", $items );
	return rx_blk_open( 'list', $attrs ) . "\n<{$tag} class=\"" . rx_blk_class( 'wp-block-list', $class ) . '">' . rx_blk_join( $lis ) . "</{$tag}>\n<!-- /wp:list -->";
}

/**
 * Image from a URL (not in the media library).
 *
 * @param string $url URL.
 * @param string $alt Alt text.
 */
function rx_blk_image( string $url, string $alt ): string {
	return rx_blk_open( 'image', array( 'sizeSlug' => 'large', 'linkDestination' => 'none' ) ) . "\n" . '<figure class="wp-block-image size-large"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"/></figure>' . "\n<!-- /wp:image -->";
}

/**
 * Details (an accordion item).
 *
 * @param string          $summary Question (plain text).
 * @param string|string[] $inner   Answer blocks.
 * @param string          $class   Extra class.
 */
function rx_blk_details( string $summary, $inner, string $class = '' ): string {
	$attrs = '' !== $class ? array( 'className' => $class ) : array();
	return rx_blk_open( 'details', $attrs ) . "\n" . '<details class="' . rx_blk_class( 'wp-block-details', $class ) . '"><summary>' . rx_blk_t( $summary ) . '</summary>' . rx_blk_join( $inner ) . "</details>\n<!-- /wp:details -->";
}

/**
 * Shortcode block.
 *
 * @param string $shortcode e.g. '[rx_estimator cta="#demo"]'.
 */
function rx_blk_shortcode( string $shortcode ): string {
	return rx_blk_open( 'shortcode' ) . "\n" . $shortcode . "\n<!-- /wp:shortcode -->";
}
