<?php
/**
 * Template Name: Block page
 *
 * A page built entirely in the block editor: no breadcrumb hero and no prose
 * column, so each top-level block (usually a Group with the "section" class)
 * is a full-width section, as on the home page. Used by the block-editor copy
 * of the home page (import/home-blocks.php).
 *
 * Interactive widgets (tour, estimator, forms, logo strip) are Shortcode
 * blocks, see inc/blocks.php. The page has its own demo section, so the
 * footer is printed without the CTA band, as on the front page.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) {
	the_post();
	the_content();
}
?>
</main>
<?php
get_footer( null, array( 'hide_cta' => true ) );
