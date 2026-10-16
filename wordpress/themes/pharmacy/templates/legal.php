<?php
/**
 * Template Name: Legal
 *
 * Legal page: hero with the "Last updated" meta line, the "On this page"
 * list and the article with its review notice and sections. Port of
 * partials/legal-layout.php, used by privacy, terms, refund-policy,
 * cookie-policy and data-security. Same markup as today.
 *
 * Fields (acf-json/group_rx_legal.json):
 * - hero        Group printed by parts/page-hero (crumb, parent, eyebrow,
 *               h1, lead, meta, pattern).
 * - notice_html Notice box at the top of the article, always marked with
 *               data-confirm. Printed with rx_html() only, so its
 *               "[brackets]" are not wrapped (as today). Empty: the default
 *               draft notice of legal-layout.php.
 * - sections    Rows of anchor, heading, body_html, screen, screen_alt.
 *               - anchor: the h2 id and the "On this page" link target.
 *               - body_html: rx_html() and then rx_legal_mark(), so
 *                 shortcodes resolve before every remaining [placeholder]
 *                 is wrapped in <span class="tbc" data-confirm>.
 *               - screen: optional product screen printed after the body
 *                 with the "Sample screen" caption (data-security).
 *               A row whose heading, body and screen are all empty is
 *               skipped; a row without an anchor or heading gets no
 *               "On this page" entry.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text(), rx_html(), rx_svg_img() and esc_*() where they are built.

get_header();

if ( have_posts() ) {
	the_post();
}

$rx_legal_hero = rx_field( 'hero' );

// Notice: the page's own text, else today's default draft notice.
$rx_legal_notice = rx_html( rx_field( 'notice_html' ) );
if ( '' === trim( $rx_legal_notice ) ) {
	$rx_legal_notice = '<b>' . esc_html__( 'Draft for legal review.', 'pharmacy' ) . '</b> '
		. esc_html__( 'This page has not been approved by the company’s legal team and is not yet in force. Items in [brackets] must be confirmed.', 'pharmacy' );
}

// Sections, ready to print.
$rx_legal_rows     = rx_field( 'sections' );
$rx_legal_sections = array();
foreach ( is_array( $rx_legal_rows ) ? $rx_legal_rows : array() as $rx_legal_row ) {
	if ( ! is_array( $rx_legal_row ) ) {
		continue;
	}

	$rx_legal_anchor  = isset( $rx_legal_row['anchor'] ) && is_string( $rx_legal_row['anchor'] ) ? trim( $rx_legal_row['anchor'] ) : '';
	$rx_legal_heading = trim( rx_text( $rx_legal_row['heading'] ?? '' ) );
	$rx_legal_body    = rx_legal_mark( rx_html( $rx_legal_row['body_html'] ?? '' ) );

	// Screen name from the field's choices (files in assets/img/screens/).
	$rx_legal_screen = isset( $rx_legal_row['screen'] ) && is_string( $rx_legal_row['screen'] ) && preg_match( '/^[a-z0-9-]+$/', $rx_legal_row['screen'] ) ? $rx_legal_row['screen'] : '';
	$rx_legal_alt    = isset( $rx_legal_row['screen_alt'] ) && is_scalar( $rx_legal_row['screen_alt'] ) ? trim( wp_strip_all_tags( do_shortcode( (string) $rx_legal_row['screen_alt'] ) ) ) : '';

	if ( '' === $rx_legal_heading && '' === trim( $rx_legal_body ) && '' === $rx_legal_screen ) {
		continue;
	}

	$rx_legal_sections[] = array(
		'anchor'  => $rx_legal_anchor,
		'heading' => $rx_legal_heading,
		'body'    => $rx_legal_body,
		'screen'  => $rx_legal_screen,
		/* translators: %s: description of the sample product screen. */
		'alt'     => '' !== $rx_legal_alt ? sprintf( __( 'Sample screen: %s', 'pharmacy' ), $rx_legal_alt ) : __( 'Sample screen', 'pharmacy' ),
	);
}

$rx_legal_toc = array_filter(
	$rx_legal_sections,
	static function ( $s ) {
		return '' !== $s['anchor'] && '' !== $s['heading'];
	}
);
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => is_array( $rx_legal_hero ) ? $rx_legal_hero : array() ) ); ?>
<section class="section-tight">
  <div class="container legal-wrap">
    <?php if ( $rx_legal_toc ) : ?>
    <nav class="toc" aria-label="<?php esc_attr_e( 'On this page', 'pharmacy' ); ?>">
      <p><?php esc_html_e( 'On this page', 'pharmacy' ); ?></p>
      <ol><?php foreach ( $rx_legal_toc as $rx_legal_s ) : ?><li><a href="#<?php echo esc_attr( $rx_legal_s['anchor'] ); ?>"><?php echo $rx_legal_s['heading']; ?></a></li><?php endforeach; ?></ol>
    </nav>
    <?php endif; ?>
    <article class="prose">
      <div class="notice" data-confirm><?php echo $rx_legal_notice; ?></div>
      <?php foreach ( $rx_legal_sections as $rx_legal_s ) : ?>
        <?php if ( '' !== $rx_legal_s['heading'] ) : ?><h2<?php echo '' !== $rx_legal_s['anchor'] ? ' id="' . esc_attr( $rx_legal_s['anchor'] ) . '"' : ''; ?>><?php echo $rx_legal_s['heading']; ?></h2><?php endif; ?>
        <?php echo $rx_legal_s['body']; ?>
        <?php if ( '' !== $rx_legal_s['screen'] ) : ?>
        <figure class="shot" style="margin:20px 0 6px"><?php echo rx_svg_img( $rx_legal_s['screen'], 1440, 900, $rx_legal_s['alt'] ); ?></figure><p class="meta-line" style="margin:0 0 12px"><?php esc_html_e( 'Sample screen · illustrative data', 'pharmacy' ); ?></p>
        <?php endif; ?>
      <?php endforeach; ?>
    </article>
  </div>
</section>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
