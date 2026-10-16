<?php
/**
 * Fallback template: the last stop in the template hierarchy.
 *
 * Every view the site has gets a more specific template (front-page.php,
 * page.php and templates/*.php, home.php, single.php, 404.php), and the
 * archive views are sent to the 404 page by inc/blog.php. This file only
 * renders a view nothing else claims, in the same layout as page.php:
 * - parts/page-hero. On a single item the breadcrumb and h1 are its title
 *   (the part's default); on a list the heading is the posts page title,
 *   the archive title or the Site Title.
 * - The content of each item in the .prose column (layout.css). On a list
 *   each item gets an h2 linking to it.
 * - "Nothing found." when the query is empty or the view is a 404.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_index_single = is_singular();

// Heading of a list view. Empty on a single item: page-hero uses its title.
$rx_index_title = '';
if ( ! $rx_index_single ) {
	if ( is_home() && ! is_front_page() ) {
		$rx_index_title = single_post_title( '', false );
	} elseif ( is_archive() ) {
		$rx_index_title = wp_strip_all_tags( get_the_archive_title() );
	}
	if ( ! is_string( $rx_index_title ) || '' === trim( $rx_index_title ) ) {
		$rx_index_title = rx_brand();
	}
}

get_header();
?>
<main id="main">
<?php
get_template_part(
	'parts/page-hero',
	null,
	array(
		'crumb' => $rx_index_title,
		'h1'    => $rx_index_title,
	)
);
?>
<section class="section-tight">
  <div class="container">
    <?php
	/*
	 * On a 404 the main query still holds the default post list (an
	 * unmatched URL runs the home query), so it is not looped over.
	 * 404.php handles real 404s; this keeps the fallback correct on its own.
	 */
	if ( have_posts() && ! is_404() ) :
		while ( have_posts() ) :
			the_post();
			?>
    <article class="prose">
			<?php if ( ! $rx_index_single ) : ?>
      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php endif; ?>
			<?php
			the_content();
			wp_link_pages();
			?>
    </article>
		<?php endwhile; ?>
    <?php else : ?>
    <div class="prose">
      <p><?php esc_html_e( 'Nothing found.', 'pharmacy' ); ?></p>
    </div>
    <?php endif; ?>
  </div>
</section>
</main>
<?php
unset( $rx_index_single, $rx_index_title );

get_footer();
