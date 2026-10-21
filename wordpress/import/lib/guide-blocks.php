<?php
/**
 * Block-markup builders for guides written from a script (the sample guide and imported guides).
 *
 * Each function returns the serialised markup the editor itself saves, so the post opens in the block editor
 * as normal, editable blocks. Load after rx_bootstrap_wp() (serialize_block_attributes() is core).
 *
 *   sb( name, attrs, inner )   any block (name without core/ for core blocks, healthray/x for ours)
 *   sp( html )                 paragraph
 *   sh( text, level )          heading
 *   sli( items, ordered )      list
 *   stable( head, rows, style, caption, foot )   Table block in a guide style
 *   simg( id, url, alt, caption )                Image block from the media library
 *
 * @package pharmacy
 */

PHP_SAPI === 'cli' || exit;

/** A block with its comment attributes and inner markup. */
function sb( string $name, array $attrs, string $inner = '' ): string {
	$a = $attrs ? ' ' . serialize_block_attributes( $attrs ) : '';
	return '' === $inner ? "<!-- wp:{$name}{$a} /-->" : "<!-- wp:{$name}{$a} -->\n{$inner}\n<!-- /wp:{$name} -->";
}

/** Paragraph. */
function sp( string $html ): string {
	return sb( 'paragraph', array(), "<p>{$html}</p>" );
}

/** Heading (level 2 by default). */
function sh( string $text, int $level = 2 ): string {
	$attrs = 2 === $level ? array() : array( 'level' => $level );
	return sb( 'heading', $attrs, "<h{$level} class=\"wp-block-heading\">{$text}</h{$level}>" );
}

/** List. */
function sli( array $items, bool $ordered = false ): string {
	$lis = implode( "\n", array_map( static fn( $t ) => sb( 'list-item', array(), "<li>{$t}</li>" ), $items ) );
	$tag = $ordered ? 'ol' : 'ul';
	return sb( 'list', $ordered ? array( 'ordered' => true ) : array(), "<{$tag} class=\"wp-block-list\">\n{$lis}\n</{$tag}>" );
}

/** A core Table block in one of the guide styles (guide, guide-num, guide-hl1, guide-hl2, guide-hl-last). */
function stable( array $head, array $rows, string $style, string $caption = '', array $foot = array() ): string {
	$th = implode( '', array_map( static fn( $c ) => "<th>{$c}</th>", $head ) );
	$tb = '';
	foreach ( $rows as $row ) {
		$tb .= '<tr>' . implode( '', array_map( static fn( $c ) => "<td>{$c}</td>", $row ) ) . '</tr>';
	}
	$tf  = $foot ? '<tfoot><tr>' . implode( '', array_map( static fn( $c ) => "<td>{$c}</td>", $foot ) ) . '</tr></tfoot>' : '';
	$cap = '' !== $caption ? "<figcaption class=\"wp-element-caption\">{$caption}</figcaption>" : '';
	return sb( 'table', array( 'hasFixedLayout' => true, 'className' => 'is-style-' . $style ), "<figure class=\"wp-block-table is-style-{$style}\"><table class=\"has-fixed-layout\"><thead><tr>{$th}</tr></thead><tbody>{$tb}</tbody>{$tf}</table>{$cap}</figure>" );
}

/** An Image block for a media-library attachment. */
function simg( int $id, string $url, string $alt, string $caption = '' ): string {
	$cap = '' !== $caption ? "<figcaption class=\"wp-element-caption\">{$caption}</figcaption>" : '';
	return sb( 'image', array( 'id' => $id, 'sizeSlug' => 'large', 'linkDestination' => 'none' ), '<figure class="wp-block-image size-large"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . $id . '"/>' . $cap . '</figure>' );
}
