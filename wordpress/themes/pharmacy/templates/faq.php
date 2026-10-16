<?php
/**
 * Template Name: FAQ
 *
 * The FAQ page. Port of the PHP_Site's faq.php: same sections and markup.
 * Content comes from group_rx_faq (acf-json/group_rx_faq.json):
 *
 * 1. parts/page-hero (`hero`).
 * 2. The topic jump links (nav.faq-cats), then one section.faq-block per
 *    `topics` row [slug, name, questions [q, a_html, confirmed]], each with
 *    parts/faq-list. Answer and button ids are fq%d / fq%db, numbered from 1
 *    across all topics (the part hands the next number back through `next`).
 *
 * A topic is skipped, in the jump links too, when its name is empty or none
 * of its questions renders (plan-dependent questions disappear when pricing
 * is null). The FAQPage schema is added by inc/seo.php, not here.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_attr() / the faq-list part where they are built.

$rx_fq_topics = rx_field( 'topics' );
$rx_fq_blocks = array();
$rx_fq_seen   = array();
$rx_fq_n      = 1;

foreach ( is_array( $rx_fq_topics ) ? $rx_fq_topics : array() as $rx_fq_topic ) {
	if ( ! is_array( $rx_fq_topic ) ) {
		continue;
	}
	$rx_fq_name = trim( rx_text( $rx_fq_topic['name'] ?? '' ) );
	if ( '' === $rx_fq_name ) {
		continue;
	}

	// Section id and jump target: the slug, else the name; unique on the page.
	$rx_fq_slug = sanitize_title( is_string( $rx_fq_topic['slug'] ?? null ) ? $rx_fq_topic['slug'] : '' );
	if ( '' === $rx_fq_slug ) {
		$rx_fq_slug = sanitize_title( wp_strip_all_tags( html_entity_decode( $rx_fq_name, ENT_QUOTES, 'UTF-8' ) ) );
	}
	if ( '' === $rx_fq_slug ) {
		$rx_fq_slug = 'topic';
	}
	$rx_fq_base = $rx_fq_slug;
	for ( $rx_fq_i = 2; isset( $rx_fq_seen[ $rx_fq_slug ] ); ++$rx_fq_i ) {
		$rx_fq_slug = $rx_fq_base . '-' . $rx_fq_i;
	}

	ob_start();
	get_template_part(
		'parts/faq-list',
		null,
		array(
			'items' => is_array( $rx_fq_topic['questions'] ?? null ) ? $rx_fq_topic['questions'] : array(),
			'ids'   => array(
				'answer' => 'fq%d',
				'button' => 'fq%db',
				'start'  => $rx_fq_n,
			),
			'next'  => &$rx_fq_n,
		)
	);
	$rx_fq_list = trim( (string) ob_get_clean() );
	if ( '' === $rx_fq_list ) {
		continue;
	}

	$rx_fq_seen[ $rx_fq_slug ] = true;
	$rx_fq_blocks[]            = array(
		'id'   => $rx_fq_slug,
		'name' => $rx_fq_name,
		'list' => $rx_fq_list,
	);
}

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => rx_field( 'hero' ) ) ); ?>

<?php if ( $rx_fq_blocks ) : ?>
<section class="section-tight">
  <div class="container">
    <nav class="faq-cats" aria-label="<?php esc_attr_e( 'FAQ topics', 'pharmacy' ); ?>">
      <?php foreach ( $rx_fq_blocks as $rx_fq_block ) : ?><a href="#<?php echo esc_attr( $rx_fq_block['id'] ); ?>"><?php echo $rx_fq_block['name']; ?></a><?php endforeach; ?>
    </nav>
    <div style="max-width:860px">
      <?php foreach ( $rx_fq_blocks as $rx_fq_block ) : ?>
        <section class="faq-block" id="<?php echo esc_attr( $rx_fq_block['id'] ); ?>" aria-labelledby="<?php echo esc_attr( $rx_fq_block['id'] ); ?>-h">
          <h2 id="<?php echo esc_attr( $rx_fq_block['id'] ); ?>-h"><?php echo $rx_fq_block['name']; ?></h2>
          <?php echo $rx_fq_block['list']; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
