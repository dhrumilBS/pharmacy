<?php
/**
 * Template Name: Demo
 *
 * The Book a demo page. Port of the PHP_Site's demo.php: one dark .final
 * section (#demo) with its own breadcrumb and heading next to the demo form,
 * and no CTA band in the footer. Content comes from group_rx_demo
 * (acf-json/group_rx_demo.json):
 *
 * - `crumb`, `eyebrow`, `title` (the h1) and `intro`. An empty crumb or
 *   title falls back to the page title.
 * - What happens next: `steps` [title, text] in ol.next, which carries
 *   data-confirm while `steps_confirmed` is off.
 * - `alt_html`: the calendar / sales phone line.
 * - parts/demo-form with `form_title` and `form_sub`; empty values show the
 *   form's own heading and text, as demo.php does today.
 *
 * Every element whose value renders empty is skipped.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_url() where they are built.

/** A text field for parts/demo-form: null when empty, so the part's default shows. */
$rx_dm_form_value = static fn( $value ): ?string => is_string( $value ) && '' !== trim( $value ) ? $value : null;

$rx_dm_object = get_queried_object();
$rx_dm_page   = $rx_dm_object instanceof WP_Post ? trim( rx_text( $rx_dm_object->post_title ) ) : '';

$rx_dm_crumb   = trim( rx_text( rx_field( 'crumb' ) ) );
$rx_dm_crumb   = '' !== $rx_dm_crumb ? $rx_dm_crumb : $rx_dm_page;
$rx_dm_eyebrow = trim( rx_text( rx_field( 'eyebrow' ) ) );
$rx_dm_title   = trim( rx_text( rx_field( 'title' ) ) );
$rx_dm_title   = '' !== $rx_dm_title ? $rx_dm_title : $rx_dm_page;
$rx_dm_intro   = trim( rx_text( rx_field( 'intro' ) ) );

$rx_dm_steps = array();
$rx_dm_rows  = rx_field( 'steps' );
foreach ( is_array( $rx_dm_rows ) ? $rx_dm_rows : array() as $rx_dm_row ) {
	if ( ! is_array( $rx_dm_row ) ) {
		continue;
	}
	$rx_dm_step = array(
		'title' => trim( rx_text( $rx_dm_row['title'] ?? '' ) ),
		'text'  => trim( rx_text( $rx_dm_row['text'] ?? '' ) ),
	);
	if ( '' !== $rx_dm_step['title'] || '' !== $rx_dm_step['text'] ) {
		$rx_dm_steps[] = $rx_dm_step;
	}
}
$rx_dm_steps_ok = false !== rx_field( 'steps_confirmed' );
$rx_dm_alt      = trim( rx_html( rx_field( 'alt_html' ) ) );

get_header();
?>
<main id="main">
<section class="final" id="demo" aria-labelledby="demo-title" style="padding-top:72px">
  <div class="container final-grid">
    <div class="final-copy">
      <ol class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmacy' ); ?>" style="color:#9FB3C8"><li><a href="<?php echo esc_url( rx_url( 'home' ) ); ?>" style="color:#9FB3C8"><?php esc_html_e( 'Home', 'pharmacy' ); ?></a></li><li aria-current="page"><?php echo $rx_dm_crumb; ?></li></ol>
      <?php if ( '' !== $rx_dm_eyebrow ) : ?><span class="eyebrow light"><?php echo $rx_dm_eyebrow; ?></span><?php endif; ?>
      <h1 id="demo-title" style="color:#fff;font-size:clamp(30px,4vw,46px)"><?php echo $rx_dm_title; ?></h1>
      <?php if ( '' !== $rx_dm_intro ) : ?><p><?php echo $rx_dm_intro; ?></p><?php endif; ?>
      <?php if ( $rx_dm_steps ) : ?>
      <ol class="next"<?php echo $rx_dm_steps_ok ? '' : ' data-confirm'; ?>>
        <?php foreach ( $rx_dm_steps as $rx_dm_step ) : ?>
        <li><div><?php if ( '' !== $rx_dm_step['title'] ) : ?><b><?php echo $rx_dm_step['title']; ?></b><?php endif; ?><?php echo $rx_dm_step['text']; ?></div></li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
      <?php if ( '' !== trim( wp_strip_all_tags( $rx_dm_alt ) ) ) : ?><p style="margin-top:28px"><?php echo $rx_dm_alt; ?></p><?php endif; ?>
    </div>
    <?php
	get_template_part(
		'parts/demo-form',
		null,
		array(
			'title' => $rx_dm_form_value( rx_field( 'form_title' ) ),
			'sub'   => $rx_dm_form_value( rx_field( 'form_sub' ) ),
		)
	);
	?>
  </div>
</section>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer( null, array( 'hide_cta' => true ) );
