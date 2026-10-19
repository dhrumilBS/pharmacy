<?php
/**
 * Test helper: prints a JSON index of the import data files
 * (wordpress/import/content/pages/*.php and posts/*.php).
 *
 * Read only. Loads each data file (plain PHP arrays, no WordPress) and
 * prints one entry per file: source, key, type, slug, parent, template and
 * the number of FAQ rows with `confirmed` on (the `faq` repeater, or the
 * `questions` of every `topics` row on the FAQ page).
 *
 * Used by test/parity-pages.test.mjs.
 *
 * @package pharmacy
 */

PHP_SAPI === 'cli' || exit;

$root  = dirname( __DIR__, 3 ) . '/import/content';
$files = array_merge( glob( $root . '/pages/*.php' ) ?: array(), glob( $root . '/posts/*.php' ) ?: array() );
sort( $files );

$index = array();
foreach ( $files as $file ) {
	$item   = require $file;
	$fields = is_array( $item['fields'] ?? null ) ? $item['fields'] : array();

	$rows = is_array( $fields['faq'] ?? null ) ? array_values( $fields['faq'] ) : array();
	foreach ( is_array( $fields['topics'] ?? null ) ? $fields['topics'] : array() as $topic ) {
		foreach ( is_array( $topic['questions'] ?? null ) ? $topic['questions'] : array() as $row ) {
			$rows[] = $row;
		}
	}
	$confirmed = 0;
	foreach ( $rows as $row ) {
		if ( is_array( $row ) && ! empty( $row['confirmed'] ) ) {
			++$confirmed;
		}
	}

	$index[] = array(
		'file'          => basename( dirname( $file ) ) . '/' . basename( $file ),
		'source'        => (string) ( $item['source'] ?? '' ),
		'key'           => (string) ( $item['key'] ?? '' ),
		'type'          => (string) ( $item['type'] ?? 'page' ),
		'slug'          => (string) ( $item['slug'] ?? '' ),
		'parent'        => (string) ( $item['parent'] ?? '' ),
		'template'      => (string) ( $item['template'] ?? '' ),
		'faq_confirmed' => $confirmed,
	);
}

echo json_encode( $index, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
