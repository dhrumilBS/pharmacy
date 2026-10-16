<?php
/**
 * Default page template: pages without a custom template.
 *
 * Every imported page uses one of templates/*.php, so this only renders
 * pages added later in the admin:
 * - parts/page-hero: breadcrumb and h1 from the page title (no SCF
 *   fields here). A child page gets its parent as the linked middle crumb.
 * - The page content (the_content filters, so the editor's paragraphs and
 *   shortcodes such as [rx_brand] work) in the long-form prose column used
 *   by the legal pages (.prose in layout.css). No block CSS loads on the
 *   front end, so the content stays plain HTML.
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

	$rx_page_parent = (int) wp_get_post_parent_id( get_the_ID() );
	get_template_part(
		'parts/page-hero',
		null,
		array(
			'parent'     => $rx_page_parent ? get_post_field( 'post_title', $rx_page_parent, 'raw' ) : null,
			'parent_url' => $rx_page_parent ? get_permalink( $rx_page_parent ) : null,
		)
	);

	// Same steps as the_content(), kept in a variable so an empty page prints no section.
	$rx_page_content = str_replace( ']]>', ']]&gt;', (string) apply_filters( 'the_content', get_the_content() ) );
	if ( '' !== trim( $rx_page_content ) ) {
		?>
<section class="section-tight">
  <div class="container">
    <article class="prose">
      <?php
		echo $rx_page_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- post content after the_content filters, as the_content() prints it.
		wp_link_pages();
		?>
    </article>
  </div>
</section>
		<?php
	}
}
unset( $rx_page_parent, $rx_page_content );
?>
</main>
<?php
get_footer();
