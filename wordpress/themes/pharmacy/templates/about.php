<?php
/**
 * Template Name: About
 *
 * The About page. Port of the PHP_Site's about.php: same sections, in the
 * same order, with the same markup. Content comes from group_rx_about
 * (acf-json/group_rx_about.json):
 *
 * 1. parts/page-hero (`hero`).
 * 2. Why we built it: `why_title`, `why_html` (its own <p> elements) and the
 *    `why_image` product JPG (1x + 2x) with `why_image_alt`.
 * 3. What we stand for: `promises_head`, `promises` [icon, title, text].
 * 4. At a glance: `facts_head`, `facts` [value, label].
 * 5. Contact cards: `cards` [title, text, link_html].
 *
 * Every element whose value renders empty is skipped, and so is a section
 * left with nothing to show.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / rx_img() / esc_attr() where they are built.

/** Rows of a repeater value (non-array rows dropped). */
$rx_ab_rows = static fn( $value ): array => is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();

/** Plain-text sub-field of a row or group, rendered and trimmed. */
$rx_ab_text = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_text( $row[ $key ] ?? '' ) ) : '';

/** Whether HTML has visible text. */
$rx_ab_filled = static fn( string $html ): bool => '' !== trim( wp_strip_all_tags( $html ) );

/** Sprite symbol id from an icon select value, '' when unusable. */
$rx_ab_icon = static fn( $value ): string => is_string( $value ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value ) ? $value : '';

// 2. Why we built it.
$rx_ab_why_title = trim( rx_text( rx_field( 'why_title' ) ) );
$rx_ab_why_html  = trim( rx_html( rx_field( 'why_html' ) ) );
$rx_ab_why_image = rx_field( 'why_image' );
$rx_ab_why_image = is_string( $rx_ab_why_image ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rx_ab_why_image ) ? $rx_ab_why_image : '';
$rx_ab_why_alt   = trim( rx_text( rx_field( 'why_image_alt' ) ) );
$rx_ab_has_why   = '' !== $rx_ab_why_title || $rx_ab_filled( $rx_ab_why_html ) || '' !== $rx_ab_why_image;

// 3. What we stand for.
$rx_ab_prom_head = rx_field( 'promises_head' );
$rx_ab_prom_e    = $rx_ab_text( $rx_ab_prom_head, 'eyebrow' );
$rx_ab_prom_t    = $rx_ab_text( $rx_ab_prom_head, 'title' );
$rx_ab_prom_i    = $rx_ab_text( $rx_ab_prom_head, 'intro' );
$rx_ab_promises  = array();
foreach ( $rx_ab_rows( rx_field( 'promises' ) ) as $rx_ab_row ) {
	$rx_ab_card = array(
		'icon'  => $rx_ab_icon( $rx_ab_row['icon'] ?? '' ),
		'title' => $rx_ab_text( $rx_ab_row, 'title' ),
		'text'  => $rx_ab_text( $rx_ab_row, 'text' ),
	);
	if ( '' !== $rx_ab_card['title'] || '' !== $rx_ab_card['text'] ) {
		$rx_ab_promises[] = $rx_ab_card;
	}
}

// 4. At a glance.
$rx_ab_facts_head = rx_field( 'facts_head' );
$rx_ab_facts_e    = $rx_ab_text( $rx_ab_facts_head, 'eyebrow' );
$rx_ab_facts_t    = $rx_ab_text( $rx_ab_facts_head, 'title' );
$rx_ab_facts_i    = $rx_ab_text( $rx_ab_facts_head, 'intro' );
$rx_ab_facts      = array();
foreach ( $rx_ab_rows( rx_field( 'facts' ) ) as $rx_ab_row ) {
	$rx_ab_fact = array(
		'value' => $rx_ab_text( $rx_ab_row, 'value' ),
		'label' => $rx_ab_text( $rx_ab_row, 'label' ),
	);
	if ( '' !== $rx_ab_fact['value'] || '' !== $rx_ab_fact['label'] ) {
		$rx_ab_facts[] = $rx_ab_fact;
	}
}

// 5. Contact cards.
$rx_ab_cards = array();
foreach ( $rx_ab_rows( rx_field( 'cards' ) ) as $rx_ab_row ) {
	$rx_ab_card = array(
		'title' => $rx_ab_text( $rx_ab_row, 'title' ),
		'text'  => $rx_ab_text( $rx_ab_row, 'text' ),
		'link'  => trim( rx_html( $rx_ab_row['link_html'] ?? '' ) ),
	);
	if ( '' !== $rx_ab_card['title'] || '' !== $rx_ab_card['text'] || $rx_ab_filled( $rx_ab_card['link'] ) ) {
		$rx_ab_cards[] = $rx_ab_card;
	}
}

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => rx_field( 'hero' ) ) ); ?>

<?php if ( $rx_ab_has_why ) : ?>
<section class="section-tight">
  <div class="container grid-2" style="align-items:center">
    <div>
      <?php if ( '' !== $rx_ab_why_title ) : ?><h2 style="font-size:clamp(26px,3vw,34px)"><?php echo $rx_ab_why_title; ?></h2><?php endif; ?>
      <?php echo $rx_ab_why_html; ?>
    </div>
    <?php if ( '' !== $rx_ab_why_image ) : ?><figure class="shot" style="margin:0"><?php echo rx_img( $rx_ab_why_image, 1440, 900, $rx_ab_why_alt, '(max-width: 900px) calc(100vw - 32px), 860px' ); ?></figure><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( '' !== $rx_ab_prom_t || $rx_ab_promises ) : ?>
<section class="section-tight bg-soft">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_ab_prom_e ) : ?><span class="eyebrow"><?php echo $rx_ab_prom_e; ?></span><?php endif; ?><?php if ( '' !== $rx_ab_prom_t ) : ?><h2><?php echo $rx_ab_prom_t; ?></h2><?php endif; ?><?php if ( '' !== $rx_ab_prom_i ) : ?><p><?php echo $rx_ab_prom_i; ?></p><?php endif; ?></div>
    <?php if ( $rx_ab_promises ) : ?>
    <div class="grid-2">
      <?php foreach ( $rx_ab_promises as $rx_ab_card ) : ?>
      <article class="info-card"><?php if ( '' !== $rx_ab_card['icon'] ) : ?><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_ab_card['icon'] ); ?>"/></svg></div><?php endif; ?><?php if ( '' !== $rx_ab_card['title'] ) : ?><h3><?php echo $rx_ab_card['title']; ?></h3><?php endif; ?><?php if ( '' !== $rx_ab_card['text'] ) : ?><p><?php echo $rx_ab_card['text']; ?></p><?php endif; ?></article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( '' !== $rx_ab_facts_t || $rx_ab_facts ) : ?>
<section class="section-tight">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_ab_facts_e ) : ?><span class="eyebrow"><?php echo $rx_ab_facts_e; ?></span><?php endif; ?><?php if ( '' !== $rx_ab_facts_t ) : ?><h2><?php echo $rx_ab_facts_t; ?></h2><?php endif; ?><?php if ( '' !== $rx_ab_facts_i ) : ?><p><?php echo $rx_ab_facts_i; ?></p><?php endif; ?></div>
    <?php if ( $rx_ab_facts ) : ?>
    <div class="facts">
      <?php foreach ( $rx_ab_facts as $rx_ab_fact ) : ?>
      <div class="fact"><?php if ( '' !== $rx_ab_fact['value'] ) : ?><b><?php echo $rx_ab_fact['value']; ?></b><?php endif; ?><?php if ( '' !== $rx_ab_fact['label'] ) : ?><span><?php echo $rx_ab_fact['label']; ?></span><?php endif; ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( $rx_ab_cards ) : ?>
<section class="section-tight bg-soft">
  <div class="container grid-3">
    <?php foreach ( $rx_ab_cards as $rx_ab_card ) : ?>
    <article class="info-card"><?php if ( '' !== $rx_ab_card['title'] ) : ?><h3><?php echo $rx_ab_card['title']; ?></h3><?php endif; ?><?php if ( '' !== $rx_ab_card['text'] ) : ?><p><?php echo $rx_ab_card['text']; ?></p><?php endif; ?><?php echo $rx_ab_card['link']; ?></article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
