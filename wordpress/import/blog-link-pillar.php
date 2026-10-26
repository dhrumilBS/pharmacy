<?php
/**
 * Adds one closing line to six related articles that links to the pillar article
 * (pharmacy-inventory-management-tips). Re-runnable: skips an article that already links to it.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-link-pillar.php   (local install only)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();
require_once __DIR__ . '/lib/guide-blocks.php';

$pillar = get_page_by_path( 'pharmacy-inventory-management-tips', OBJECT, 'post' );
if ( ! $pillar ) {
	fwrite( STDERR, 'Run blog-inventory-listicle.php first.' . PHP_EOL );
	exit( 1 );
}
$url = get_permalink( $pillar );

$spokes = array( 'min-max-stock', 'fefo-explained', 'cut-expiry-loss', 'monthly-stock-count', 'non-moving-stock', 'short-book' );
foreach ( $spokes as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $p ) {
		echo "missing: {$slug}" . PHP_EOL;
		continue;
	}
	if ( false !== strpos( $p->post_content, '/pharmacy-inventory-management-tips/' ) ) {
		echo "already linked: {$slug}" . PHP_EOL;
		continue;
	}
	$line = sp( 'This is one of twelve habits in <a href="' . esc_url( $url ) . '">pharmacy inventory management: 12 ways to manage medicine stock efficiently</a>, which puts them in one routine.' );
	$at      = strpos( $p->post_content, '<!-- wp:healthray/faq' ); // Before the FAQ block when there is one, else at the end.
	$content = false === $at ? $p->post_content . "\n\n" . $line : substr( $p->post_content, 0, $at ) . $line . "\n\n" . substr( $p->post_content, $at );
	wp_update_post( wp_slash( array( 'ID' => $p->ID, 'post_content' => $content ) ) );
	echo "linked: {$slug}" . PHP_EOL;
}
