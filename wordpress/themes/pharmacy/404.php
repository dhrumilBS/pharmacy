<?php
/**
 * 404 page: unknown URLs, unknown /blog/{x}/ slugs and the archive views
 * inc/blog.php sends here (category, tag, date, author, search, feed,
 * attachment, paged). WordPress sends the 404 status; Yoast prints the
 * title and robots meta.
 *
 * New on the WordPress site (the PHP site has no 404 page), built from the
 * existing design system only (no new CSS):
 * - parts/page-hero: breadcrumb, eyebrow, h1 and a short message.
 * - Four .info-card links in a .grid-2 (two columns, one on phones): home,
 *   features, pricing and the blog. URLs come from rx_url(), so they follow
 *   the pages' `_rx_key` (and point at the home page until a page exists).
 * - The usual CTA band and footer.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_404_links = array(
	array(
		'key'   => 'home',
		'icon'  => 'i-store',
		'title' => __( 'Home', 'pharmacy' ),
		'text'  => __( 'Start again from the home page.', 'pharmacy' ),
		'label' => __( 'Go to the home page', 'pharmacy' ),
	),
	array(
		'key'   => 'features',
		'icon'  => 'i-box',
		'title' => __( 'Features', 'pharmacy' ),
		'text'  => __( 'Billing, expiry, stock, purchase, reports, GST, customer retention and security.', 'pharmacy' ),
		'label' => __( 'See all features', 'pharmacy' ),
	),
	array(
		'key'   => 'pricing',
		'icon'  => 'i-rupee',
		'title' => __( 'Pricing', 'pharmacy' ),
		'text'  => __( 'Compare the plans and what each one includes.', 'pharmacy' ),
		'label' => __( 'See pricing', 'pharmacy' ),
	),
	array(
		'key'   => 'blog',
		'icon'  => 'i-file',
		'title' => __( 'Blog', 'pharmacy' ),
		'text'  => __( 'Practical articles for running a medical store.', 'pharmacy' ),
		'label' => __( 'Read the blog', 'pharmacy' ),
	),
);

get_header();
?>
<main id="main">
<?php
get_template_part(
	'parts/page-hero',
	null,
	array(
		'crumb'   => __( 'Page not found', 'pharmacy' ),
		'eyebrow' => __( 'Error 404', 'pharmacy' ),
		'h1'      => __( 'We can’t find that page', 'pharmacy' ),
		'lead'    => __( 'The link may be old or mistyped, or the page has moved. These pages are a good place to start.', 'pharmacy' ),
	)
);
?>
<section class="section-tight">
  <div class="container grid-2">
    <?php foreach ( $rx_404_links as $rx_404_link ) : ?>
    <article class="info-card">
      <div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_404_link['icon'] ); ?>"/></svg></div>
      <h2><?php echo esc_html( $rx_404_link['title'] ); ?></h2>
      <p><?php echo esc_html( $rx_404_link['text'] ); ?></p>
      <p><a class="link-arrow" href="<?php echo esc_url( rx_url( $rx_404_link['key'] ) ); ?>"><?php echo esc_html( $rx_404_link['label'] ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>
    </article>
    <?php endforeach; ?>
  </div>
</section>
</main>
<?php
unset( $rx_404_links, $rx_404_link );

get_footer();
