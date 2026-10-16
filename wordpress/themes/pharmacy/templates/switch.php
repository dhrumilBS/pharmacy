<?php
/**
 * Template Name: Switch
 *
 * The Switching page. Port of the PHP_Site's switch.php: same sections, in
 * the same order, with the same markup. Content comes from group_rx_switch
 * (acf-json/group_rx_switch.json):
 *
 * 1. parts/page-hero (`hero`).
 * 2. Buttons: `cta_demo` (Demo page) and `cta_free` (Site_Settings `app_signup`).
 * 3. How it works: `steps_head`, `screen` + `screen_alt`, `steps` [title, html, when].
 * 4. What comes across: `columns_head`, `columns` [name], `columns_tip`, and the
 *    checklist card (`checklist_title`, `checklist` [html], `notes` [html, confirmed]).
 * 5. Moving from…: `from_head`, `from` [icon, title, text].
 * 6. Compare: `compare_head`, `compare_cols`, `compare` [what, desktop, app, ours_html],
 *    `compare_note`. A cell whose value renders empty (the live [rx_compare_text]
 *    when pricing is null) shows rx_pricing_notice() instead.
 * 7. Switching FAQ: `faq_head` (eyebrow, title, intro_html) and parts/faq-list
 *    with ids sq%d / sq%db from 0.
 *
 * Every element whose value renders empty is skipped, and so is a section
 * left with nothing to show.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / rx_svg_img() / esc_*() where they are built.

/** Rows of a repeater value (non-array rows dropped). */
$rx_sw_rows = static fn( $value ): array => is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();

/** Plain-text sub-field of a row or group, rendered and trimmed. */
$rx_sw_text = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_text( $row[ $key ] ?? '' ) ) : '';

/** HTML sub-field of a row or group, rendered and trimmed. */
$rx_sw_html = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_html( $row[ $key ] ?? '' ) ) : '';

/** Whether HTML has visible text. */
$rx_sw_filled = static fn( string $html ): bool => '' !== trim( wp_strip_all_tags( $html ) );

/** Sprite symbol id from an icon select value, '' when unusable. */
$rx_sw_icon = static fn( $value ): string => is_string( $value ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value ) ? $value : '';

/** Screen name from a screen select value, '' when unusable. */
$rx_sw_screen_name = static fn( $value ): string => is_string( $value ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value ) ? $value : '';

// 2. Buttons.
$rx_sw_cta_demo = trim( rx_text( rx_field( 'cta_demo' ) ) );
$rx_sw_cta_free = trim( rx_text( rx_field( 'cta_free' ) ) );
$rx_sw_signup   = trim( (string) rx_setting( 'app_signup' ) );
if ( '' === $rx_sw_signup ) {
	$rx_sw_cta_free = '';
}

// 3. How it works.
$rx_sw_steps_head = rx_field( 'steps_head' );
$rx_sw_steps_e    = $rx_sw_text( $rx_sw_steps_head, 'eyebrow' );
$rx_sw_steps_t    = $rx_sw_text( $rx_sw_steps_head, 'title' );
$rx_sw_steps_i    = $rx_sw_text( $rx_sw_steps_head, 'intro' );
$rx_sw_screen     = $rx_sw_screen_name( rx_field( 'screen' ) );
$rx_sw_screen_alt = trim( rx_text( rx_field( 'screen_alt' ) ) );
$rx_sw_steps      = array();
foreach ( $rx_sw_rows( rx_field( 'steps' ) ) as $rx_sw_row ) {
	$rx_sw_step = array(
		'title' => $rx_sw_text( $rx_sw_row, 'title' ),
		'html'  => $rx_sw_html( $rx_sw_row, 'html' ),
		'when'  => $rx_sw_text( $rx_sw_row, 'when' ),
	);
	if ( '' !== $rx_sw_step['title'] || $rx_sw_filled( $rx_sw_step['html'] ) ) {
		$rx_sw_steps[] = $rx_sw_step;
	}
}
$rx_sw_has_steps = '' !== $rx_sw_steps_t || $rx_sw_steps || '' !== $rx_sw_screen;

// 4. What comes across.
$rx_sw_cols_head = rx_field( 'columns_head' );
$rx_sw_cols_e    = $rx_sw_text( $rx_sw_cols_head, 'eyebrow' );
$rx_sw_cols_t    = $rx_sw_text( $rx_sw_cols_head, 'title' );
$rx_sw_cols_i    = $rx_sw_text( $rx_sw_cols_head, 'intro' );
$rx_sw_cols      = array_values( array_filter( array_map( static fn( $row ) => $rx_sw_text( $row, 'name' ), $rx_sw_rows( rx_field( 'columns' ) ) ), 'strlen' ) );
$rx_sw_cols_tip  = trim( rx_text( rx_field( 'columns_tip' ) ) );
$rx_sw_check_t   = trim( rx_text( rx_field( 'checklist_title' ) ) );
$rx_sw_checks    = array_values( array_filter( array_map( static fn( $row ) => $rx_sw_html( $row, 'html' ), $rx_sw_rows( rx_field( 'checklist' ) ) ), $rx_sw_filled ) );
$rx_sw_notes     = array();
foreach ( $rx_sw_rows( rx_field( 'notes' ) ) as $rx_sw_row ) {
	$rx_sw_note = $rx_sw_html( $rx_sw_row, 'html' );
	if ( $rx_sw_filled( $rx_sw_note ) ) {
		$rx_sw_notes[] = array(
			'html' => $rx_sw_note,
			'ok'   => ! isset( $rx_sw_row['confirmed'] ) || (bool) $rx_sw_row['confirmed'],
		);
	}
}
$rx_sw_has_cols_copy = '' !== $rx_sw_cols_t || $rx_sw_cols;
$rx_sw_has_checklist = '' !== $rx_sw_check_t || $rx_sw_checks || $rx_sw_notes;

// 5. Moving from…
$rx_sw_from_head = rx_field( 'from_head' );
$rx_sw_from_e    = $rx_sw_text( $rx_sw_from_head, 'eyebrow' );
$rx_sw_from_t    = $rx_sw_text( $rx_sw_from_head, 'title' );
$rx_sw_from_i    = $rx_sw_text( $rx_sw_from_head, 'intro' );
$rx_sw_from      = array();
foreach ( $rx_sw_rows( rx_field( 'from' ) ) as $rx_sw_row ) {
	$rx_sw_card = array(
		'icon'  => $rx_sw_icon( $rx_sw_row['icon'] ?? '' ),
		'title' => $rx_sw_text( $rx_sw_row, 'title' ),
		'text'  => $rx_sw_text( $rx_sw_row, 'text' ),
	);
	if ( '' !== $rx_sw_card['title'] || '' !== $rx_sw_card['text'] ) {
		$rx_sw_from[] = $rx_sw_card;
	}
}

// 6. Compare.
$rx_sw_cmp_head = rx_field( 'compare_head' );
$rx_sw_cmp_e    = $rx_sw_text( $rx_sw_cmp_head, 'eyebrow' );
$rx_sw_cmp_t    = $rx_sw_text( $rx_sw_cmp_head, 'title' );
$rx_sw_cmp_i    = $rx_sw_text( $rx_sw_cmp_head, 'intro' );
$rx_sw_cmp_cols = rx_field( 'compare_cols' );
$rx_sw_cmp_th   = array();
foreach ( array( 'what', 'desktop', 'app', 'ours' ) as $rx_sw_col ) {
	$rx_sw_cmp_th[ $rx_sw_col ] = $rx_sw_text( $rx_sw_cmp_cols, $rx_sw_col );
}
$rx_sw_cmp_rows = array();
foreach ( $rx_sw_rows( rx_field( 'compare' ) ) as $rx_sw_row ) {
	$rx_sw_ours = $rx_sw_html( $rx_sw_row, 'ours_html' );
	// A live value that renders empty (pricing null): the notice goes where the prices would be.
	if ( ! $rx_sw_filled( $rx_sw_ours ) && is_string( $rx_sw_row['ours_html'] ?? null ) && '' !== trim( $rx_sw_row['ours_html'] ) ) {
		$rx_sw_ours = rx_pricing_notice();
	}
	$rx_sw_line = array(
		'what'    => $rx_sw_text( $rx_sw_row, 'what' ),
		'desktop' => $rx_sw_text( $rx_sw_row, 'desktop' ),
		'app'     => $rx_sw_text( $rx_sw_row, 'app' ),
		'ours'    => $rx_sw_ours,
	);
	if ( '' !== $rx_sw_line['what'] ) {
		$rx_sw_cmp_rows[] = $rx_sw_line;
	}
}
$rx_sw_cmp_note = trim( rx_text( rx_field( 'compare_note' ) ) );

// 7. Switching FAQ.
$rx_sw_faq_head = rx_field( 'faq_head' );
$rx_sw_faq_e    = $rx_sw_text( $rx_sw_faq_head, 'eyebrow' );
$rx_sw_faq_t    = $rx_sw_text( $rx_sw_faq_head, 'title' );
$rx_sw_faq_i    = $rx_sw_html( $rx_sw_faq_head, 'intro_html' );
ob_start();
get_template_part(
	'parts/faq-list',
	null,
	array(
		'items' => $rx_sw_rows( rx_field( 'faq' ) ),
		'ids'   => array(
			'answer' => 'sq%d',
			'button' => 'sq%db',
			'start'  => 0,
		),
	)
);
$rx_sw_faq_list = trim( (string) ob_get_clean() );

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => rx_field( 'hero' ) ) ); ?>

<?php if ( '' !== $rx_sw_cta_demo || '' !== $rx_sw_cta_free ) : ?>
<section class="section-tight" style="padding-top:8px">
  <div class="container">
    <p class="hero-ctas" style="justify-content:flex-start;margin-top:0">
      <?php if ( '' !== $rx_sw_cta_demo ) : ?><a class="btn btn-primary" href="<?php echo esc_url( rx_url( 'demo' ) ); ?>" data-cta="demo"><?php echo $rx_sw_cta_demo; ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?>
      <?php if ( '' !== $rx_sw_cta_free ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_sw_signup ); ?>"><?php echo $rx_sw_cta_free; ?></a><?php endif; ?>
    </p>
  </div>
</section>
<?php endif; ?>
<?php if ( $rx_sw_has_steps ) : ?>
<section class="section-tight bg-soft">
  <div class="container switch-grid">
    <div class="switch-copy">
      <?php if ( '' !== $rx_sw_steps_e ) : ?><span class="eyebrow"><?php echo $rx_sw_steps_e; ?></span><?php endif; ?>
      <?php if ( '' !== $rx_sw_steps_t ) : ?><h2><?php echo $rx_sw_steps_t; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_sw_steps_i ) : ?><p><?php echo $rx_sw_steps_i; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_sw_screen ) : ?><figure class="shot" style="margin-top:24px"><?php echo rx_svg_img( $rx_sw_screen, 1440, 900, $rx_sw_screen_alt ); ?></figure>
      <span class="sample-tag shot-note"><?php esc_html_e( 'Sample screen · illustrative data', 'pharmacy' ); ?></span><?php endif; ?>
    </div>
    <?php if ( $rx_sw_steps ) : ?>
    <ol class="steps">
      <?php foreach ( $rx_sw_steps as $rx_sw_step ) : ?>
      <li class="step"><div><?php if ( '' !== $rx_sw_step['title'] ) : ?><h3><?php echo $rx_sw_step['title']; ?></h3><?php endif; ?><?php if ( $rx_sw_filled( $rx_sw_step['html'] ) ) : ?><p><?php echo $rx_sw_step['html']; ?></p><?php endif; ?><?php if ( '' !== $rx_sw_step['when'] ) : ?><span class="when"><?php echo $rx_sw_step['when']; ?></span><?php endif; ?></div></li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php if ( $rx_sw_has_cols_copy || $rx_sw_has_checklist ) : ?>
<section class="section-tight">
  <div class="container grid-2" style="align-items:start">
    <?php if ( $rx_sw_has_cols_copy ) : ?>
    <div>
      <?php if ( '' !== $rx_sw_cols_e ) : ?><span class="eyebrow"><?php echo $rx_sw_cols_e; ?></span><?php endif; ?>
      <?php if ( '' !== $rx_sw_cols_t ) : ?><h2 style="font-size:clamp(26px,3vw,34px)"><?php echo $rx_sw_cols_t; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_sw_cols_i ) : ?><p style="margin-top:12px;color:var(--text-2)"><?php echo $rx_sw_cols_i; ?></p><?php endif; ?>
      <?php if ( $rx_sw_cols ) : ?><div class="csv-cols"><?php foreach ( $rx_sw_cols as $rx_sw_col ) : ?><span><?php echo $rx_sw_col; ?></span><?php endforeach; ?></div><?php endif; ?>
      <?php if ( '' !== $rx_sw_cols_tip ) : ?><p class="meta-line" style="margin-top:16px"><?php echo $rx_sw_cols_tip; ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ( $rx_sw_has_checklist ) : ?>
    <div class="info-card">
      <?php if ( '' !== $rx_sw_check_t ) : ?><h3><?php echo $rx_sw_check_t; ?></h3><?php endif; ?>
      <?php if ( $rx_sw_checks ) : ?>
      <ul class="checks">
        <?php foreach ( $rx_sw_checks as $rx_sw_check ) : ?>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><?php echo $rx_sw_check; ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php foreach ( $rx_sw_notes as $rx_sw_note ) : ?>
      <p class="meta-line"<?php echo $rx_sw_note['ok'] ? '' : ' data-confirm'; ?>><?php echo $rx_sw_note['html']; ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php if ( '' !== $rx_sw_from_t || $rx_sw_from ) : ?>
<section class="section-tight bg-soft">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_sw_from_e ) : ?><span class="eyebrow"><?php echo $rx_sw_from_e; ?></span><?php endif; ?><?php if ( '' !== $rx_sw_from_t ) : ?><h2><?php echo $rx_sw_from_t; ?></h2><?php endif; ?><?php if ( '' !== $rx_sw_from_i ) : ?><p><?php echo $rx_sw_from_i; ?></p><?php endif; ?></div>
    <?php if ( $rx_sw_from ) : ?>
    <div class="grid-3">
      <?php foreach ( $rx_sw_from as $rx_sw_card ) : ?>
      <article class="info-card"><?php if ( '' !== $rx_sw_card['icon'] ) : ?><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_sw_card['icon'] ); ?>"/></svg></div><?php endif; ?><?php if ( '' !== $rx_sw_card['title'] ) : ?><h3><?php echo $rx_sw_card['title']; ?></h3><?php endif; ?><?php if ( '' !== $rx_sw_card['text'] ) : ?><p><?php echo $rx_sw_card['text']; ?></p><?php endif; ?></article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php if ( $rx_sw_cmp_rows ) : ?>
<section class="section-tight">
  <div class="container">
    <?php if ( '' !== $rx_sw_cmp_t ) : ?><div class="section-head"><?php if ( '' !== $rx_sw_cmp_e ) : ?><span class="eyebrow"><?php echo $rx_sw_cmp_e; ?></span><?php endif; ?><h2><?php echo $rx_sw_cmp_t; ?></h2><?php if ( '' !== $rx_sw_cmp_i ) : ?><p><?php echo $rx_sw_cmp_i; ?></p><?php endif; ?></div><?php endif; ?>

    <table class="compare-table">
      <thead><tr><th scope="col"><?php echo $rx_sw_cmp_th['what']; ?></th><th scope="col"><?php echo $rx_sw_cmp_th['desktop']; ?></th><th scope="col"><?php echo $rx_sw_cmp_th['app']; ?></th><th scope="col"><?php echo $rx_sw_cmp_th['ours']; ?></th></tr></thead>
      <tbody>
        <?php foreach ( $rx_sw_cmp_rows as $rx_sw_line ) : ?>
        <tr><td><?php echo $rx_sw_line['what']; ?></td><td><?php echo $rx_sw_line['desktop']; ?></td><td><?php echo $rx_sw_line['app']; ?></td><td><?php echo $rx_sw_line['ours']; ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ( '' !== $rx_sw_cmp_note ) : ?><p class="meta-line"><?php echo $rx_sw_cmp_note; ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php if ( '' !== $rx_sw_faq_list ) : ?>
<section class="section-tight bg-soft">
  <div class="container faq-grid">
    <div class="faq-intro"><?php if ( '' !== $rx_sw_faq_e ) : ?><span class="eyebrow"><?php echo $rx_sw_faq_e; ?></span><?php endif; ?><?php if ( '' !== $rx_sw_faq_t ) : ?><h2><?php echo $rx_sw_faq_t; ?></h2><?php endif; ?><?php if ( $rx_sw_filled( $rx_sw_faq_i ) ) : ?><p><?php echo $rx_sw_faq_i; ?></p><?php endif; ?></div>
    <?php echo $rx_sw_faq_list; ?>

  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
