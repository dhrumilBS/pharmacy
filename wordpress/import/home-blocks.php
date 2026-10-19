<?php
/**
 * Creates or updates the block-editor sample of the home page in the local
 * WordPress install: Pages > "Home (block editor sample)", slug home-blocks.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\home-blocks.php             create / update
 *     D:\xampp\php\php.exe wordpress\import\home-blocks.php --dry-run   build and check only
 *
 * Re-runnable: the page is found by its `_rx_source` and updated in place, so
 * running it again replaces the page's content with the built markup (edits
 * made in the editor are overwritten). It touches only this one page: nothing
 * else is imported, and the real home page is not changed.
 *
 * Checks before writing, all of which must pass:
 * - Local guard (lib/guard.php), the Block page template exists.
 * - The markup parses into blocks, every block is a registered block, there
 *   is no stray "freeform" HTML between blocks, and re-serialising the parsed
 *   blocks gives the same markup (the comment delimiters are well formed).
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';
require_once __DIR__ . '/lib/upsert.php';

$dry = in_array( '--dry-run', $argv ?? array(), true ) || in_array( '-n', $argv ?? array(), true );

rx_bootstrap_wp();
rx_assert_local();

require_once __DIR__ . '/lib/blocks.php';
$item = require __DIR__ . '/content/samples/home-blocks.php';

// --- Check the markup -------------------------------------------------------
$blocks   = parse_blocks( $item['content'] );
$registry = WP_Block_Type_Registry::get_instance();
$names    = array();
$problems = array();

$walk = static function ( array $list ) use ( &$walk, &$names, &$problems, $registry ): void {
	foreach ( $list as $b ) {
		$name = (string) ( $b['blockName'] ?? '' );
		if ( '' === $name ) {
			if ( '' !== trim( (string) ( $b['innerHTML'] ?? '' ) ) ) {
				$problems[] = 'stray HTML between blocks: ' . mb_substr( trim( wp_strip_all_tags( $b['innerHTML'] ) ), 0, 60 );
			}
			continue;
		}
		$names[ $name ] = ( $names[ $name ] ?? 0 ) + 1;
		if ( 'core/freeform' === $name ) {
			$problems[] = 'a freeform (classic) block was created: some HTML is outside a block';
		} elseif ( ! $registry->is_registered( $name ) ) {
			$problems[] = "unregistered block {$name}";
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			$walk( $b['innerBlocks'] );
		}
	}
};
$walk( $blocks );

if ( serialize_blocks( $blocks ) !== $item['content'] ) {
	// Whitespace between blocks may differ; compare without it.
	$strip = static fn( string $s ): string => preg_replace( '/>\s+</', '><', trim( $s ) );
	if ( $strip( serialize_blocks( $blocks ) ) !== $strip( $item['content'] ) ) {
		$problems[] = 'the markup does not survive parse + serialize unchanged (check the block comments)';
	}
}

ksort( $names );
echo 'Blocks: ' . array_sum( $names ) . ' (' . implode( ', ', array_map( static fn( $n, $c ) => str_replace( 'core/', '', $n ) . " x{$c}", array_keys( $names ), $names ) ) . ')' . PHP_EOL;
echo 'Markup: ' . strlen( $item['content'] ) . ' bytes' . PHP_EOL;

if ( $problems ) {
	echo 'PROBLEMS:' . PHP_EOL . '  - ' . implode( PHP_EOL . '  - ', array_unique( $problems ) ) . PHP_EOL;
	exit( 1 );
}
echo 'Check: ok' . PHP_EOL;

if ( $dry ) {
	echo 'Dry run: nothing written.' . PHP_EOL;
	exit( 0 );
}

// --- Write the page ---------------------------------------------------------
$report = array();
$id     = rx_upsert_post( $item, $report );
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'FAILED: ' . $id->get_error_message() . PHP_EOL );
	exit( 1 );
}

echo ucfirst( $report['action'] ) . ' page #' . $id . ': ' . get_the_title( $id ) . PHP_EOL;
echo 'View: ' . get_permalink( $id ) . PHP_EOL;
echo 'Edit: ' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . PHP_EOL;
