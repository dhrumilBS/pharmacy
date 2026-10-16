<?php
/**
 * Template Name: Contact
 *
 * The Contact page. Port of the PHP_Site's contact.php: same sections, in
 * the same order, with the same markup. Content comes from group_rx_contact
 * (acf-json/group_rx_contact.json) and Site_Settings:
 *
 * 1. parts/page-hero (`hero`).
 * 2. Contact cards: `cards` [icon, title, text, link_html, extra_html, note,
 *    note_confirmed]. The last card also gets "Or email …" when the
 *    Site_Settings `email` is set (empty today, so the line is hidden, as on
 *    the PHP_Site).
 * 3. Office: `office_head` (eyebrow, title, intro = the address line), the
 *    Google Maps button (`map_label`, Site_Settings `map_query`), "Follow us"
 *    (`social_title`) with the Site_Settings `social` profiles, shown only
 *    when there are profiles, and parts/demo-form (`form_title`, `form_sub`).
 *
 * Every element whose value renders empty is skipped, and so is a section
 * left with nothing to show.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built.

/** Plain-text sub-field of a row or group, rendered and trimmed. */
$rx_ct_text = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_text( $row[ $key ] ?? '' ) ) : '';

/** HTML sub-field of a row or group, rendered and trimmed. */
$rx_ct_html = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_html( $row[ $key ] ?? '' ) ) : '';

/** Whether HTML has visible text. */
$rx_ct_filled = static fn( string $html ): bool => '' !== trim( wp_strip_all_tags( $html ) );

/** A text field for parts/demo-form: null when empty, so the part's default shows. */
$rx_ct_form_value = static fn( $value ): ?string => is_string( $value ) && '' !== trim( $value ) ? $value : null;

// 2. Contact cards.
$rx_ct_cards = array();
$rx_ct_rows  = rx_field( 'cards' );
foreach ( is_array( $rx_ct_rows ) ? $rx_ct_rows : array() as $rx_ct_row ) {
	if ( ! is_array( $rx_ct_row ) ) {
		continue;
	}
	$rx_ct_icon = $rx_ct_row['icon'] ?? '';
	$rx_ct_card = array(
		'icon'  => is_string( $rx_ct_icon ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rx_ct_icon ) ? $rx_ct_icon : '',
		'title' => $rx_ct_text( $rx_ct_row, 'title' ),
		'text'  => $rx_ct_text( $rx_ct_row, 'text' ),
		'link'  => $rx_ct_html( $rx_ct_row, 'link_html' ),
		'extra' => $rx_ct_html( $rx_ct_row, 'extra_html' ),
		'note'  => $rx_ct_text( $rx_ct_row, 'note' ),
		'ok'    => ! isset( $rx_ct_row['note_confirmed'] ) || (bool) $rx_ct_row['note_confirmed'],
		'email' => false,
	);
	if ( '' !== $rx_ct_card['title'] || '' !== $rx_ct_card['text'] || $rx_ct_filled( $rx_ct_card['link'] ) || $rx_ct_filled( $rx_ct_card['extra'] ) ) {
		$rx_ct_cards[] = $rx_ct_card;
	}
}
$rx_ct_email = trim( (string) rx_setting( 'email' ) );
if ( $rx_ct_cards && '' !== $rx_ct_email && is_email( $rx_ct_email ) ) {
	$rx_ct_cards[ count( $rx_ct_cards ) - 1 ]['email'] = true;
}

// 3. Office.
$rx_ct_office    = rx_field( 'office_head' );
$rx_ct_office_e  = $rx_ct_text( $rx_ct_office, 'eyebrow' );
$rx_ct_office_t  = $rx_ct_text( $rx_ct_office, 'title' );
$rx_ct_office_i  = $rx_ct_text( $rx_ct_office, 'intro' );
$rx_ct_map_label = trim( rx_text( rx_field( 'map_label' ) ) );
$rx_ct_map_query = trim( (string) rx_setting( 'map_query' ) );
$rx_ct_map       = '' !== $rx_ct_map_query && '' !== $rx_ct_map_label
	? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $rx_ct_map_query )
	: '';
$rx_ct_social_t  = trim( rx_text( rx_field( 'social_title' ) ) );
$rx_ct_social    = rx_setting( 'social' );
$rx_ct_social    = is_array( $rx_ct_social ) ? $rx_ct_social : array();
$rx_ct_has_copy  = '' !== $rx_ct_office_t || '' !== $rx_ct_office_i || '' !== $rx_ct_map || $rx_ct_social;

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => rx_field( 'hero' ) ) ); ?>

<?php if ( $rx_ct_cards ) : ?>
<section class="section-tight">
  <div class="container grid-3">
    <?php foreach ( $rx_ct_cards as $rx_ct_card ) : ?>
    <article class="info-card">
      <?php if ( '' !== $rx_ct_card['icon'] ) : ?><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_ct_card['icon'] ); ?>"/></svg></div><?php endif; ?>
      <?php if ( '' !== $rx_ct_card['title'] ) : ?><h2><?php echo $rx_ct_card['title']; ?></h2><?php endif; ?><?php if ( '' !== $rx_ct_card['text'] ) : ?><p><?php echo $rx_ct_card['text']; ?></p><?php endif; ?>
      <?php echo $rx_ct_card['link']; ?>
      <?php if ( $rx_ct_filled( $rx_ct_card['extra'] ) ) : ?><p><?php echo $rx_ct_card['extra']; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_ct_card['note'] ) : ?><p class="meta-line"<?php echo $rx_ct_card['ok'] ? '' : ' data-confirm'; ?>><?php echo $rx_ct_card['note']; ?></p><?php endif; ?>
      <?php if ( $rx_ct_card['email'] ) : ?><p><?php /* translators: %s: email address link. */ printf( esc_html__( 'Or email %s', 'pharmacy' ), '<a href="' . esc_url( 'mailto:' . $rx_ct_email ) . '">' . esc_html( $rx_ct_email ) . '</a>' ); ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section-tight bg-soft">
  <div class="container grid-2" style="align-items:start">
    <?php if ( $rx_ct_has_copy ) : ?>
    <div>
      <?php if ( '' !== $rx_ct_office_e ) : ?><span class="eyebrow"><?php echo $rx_ct_office_e; ?></span><?php endif; ?>
      <?php if ( '' !== $rx_ct_office_t ) : ?><h2 style="font-size:clamp(26px,3vw,34px)"><?php echo $rx_ct_office_t; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_ct_office_i ) : ?><p style="margin-top:14px;color:var(--text-2);font-size:17px"><?php echo $rx_ct_office_i; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_ct_map ) : ?><p style="margin-top:16px"><a class="btn btn-secondary" href="<?php echo esc_url( $rx_ct_map ); ?>" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><?php echo $rx_ct_map_label; ?></a></p><?php endif; ?>
      <?php if ( $rx_ct_social ) : ?>
      <?php if ( '' !== $rx_ct_social_t ) : ?><h3 style="font-size:17px;margin-top:32px"><?php echo $rx_ct_social_t; ?></h3><?php endif; ?>
      <p style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px 18px">
        <?php foreach ( $rx_ct_social as $rx_ct_profile ) : ?><a href="<?php echo esc_url( $rx_ct_profile['url'] ); ?>" rel="noopener"><?php echo esc_html( '' !== $rx_ct_profile['network'] ? $rx_ct_profile['network'] : $rx_ct_profile['url'] ); ?></a><?php endforeach; ?>
      </p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
	get_template_part(
		'parts/demo-form',
		null,
		array(
			'title' => $rx_ct_form_value( rx_field( 'form_title' ) ),
			'sub'   => $rx_ct_form_value( rx_field( 'form_sub' ) ),
		)
	);
	?>
  </div>
</section>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
