<?php
/**
 * "Trusted by" logo marquee. Port of partials/trusted-strip.php: the logo
 * files in assets/img/trusted/ shown as <img> with alt text (crawlable), in
 * two identical rows so the CSS loop (home.css .trust-track) is seamless.
 * It scrolls continuously and never pauses (owner decision; it conflicts
 * with WCAG 2.2.2 Pause, Stop, Hide, revisit before launch).
 *
 * Which logos:
 * - The child theme's assets/img/trusted/ when it holds any logo file, so
 *   real customer logos can replace the placeholders without touching the
 *   parent theme. Otherwise the parent theme's folder, whose files are
 *   INVENTED placeholders.
 * - Files: .svg, .png, .webp, .jpg, .jpeg, in filename order. Alt text is
 *   built from the filename: "green-cross-pharmacy.svg" gives
 *   "Green Cross Pharmacy logo". Logos render 40px tall; the width comes
 *   from the SVG's width attribute (default 160) or the image's aspect ratio.
 * - Placeholders keep the whole strip data-confirm, as today. Logos from the
 *   child theme drop the flag.
 * - No logo files: nothing is printed.
 *
 * $args (all optional; missing or null uses today's text, '' hides the element):
 * - title string Heading. Default "Billing every day at medical stores, chains & hospital pharmacies".
 * - note  string Small note beside the heading. Default "A few of them".
 *
 * Title and note go through rx_text(), so shortcodes work.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_url() / esc_attr() where they are built.

$rx_ts_args = isset( $args ) && is_array( $args ) ? $args : array();

/*
 * Logo files: [ file, name, width ] per logo, from the child theme's folder
 * when it has any, else the parent's.
 */
$rx_ts_logos   = array();
$rx_ts_from    = '';
$rx_ts_folders = array(
	array( get_stylesheet_directory(), get_stylesheet_directory_uri() ),
	array( get_template_directory(), get_template_directory_uri() ),
);
foreach ( $rx_ts_folders as $rx_ts_folder ) {
	$rx_ts_dir   = $rx_ts_folder[0] . '/assets/img/trusted';
	$rx_ts_files = is_dir( $rx_ts_dir ) ? scandir( $rx_ts_dir ) : false;
	foreach ( is_array( $rx_ts_files ) ? $rx_ts_files : array() as $rx_ts_file ) {
		$rx_ts_path = $rx_ts_dir . '/' . $rx_ts_file;
		if ( ! preg_match( '/\.(svg|png|webp|jpe?g)$/i', $rx_ts_file ) || ! is_file( $rx_ts_path ) ) {
			continue;
		}
		$rx_ts_name = ucwords( trim( preg_replace( '/[-_]+/', ' ', pathinfo( $rx_ts_file, PATHINFO_FILENAME ) ) ) );
		$rx_ts_w    = 160;
		if ( preg_match( '/\.svg$/i', $rx_ts_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file, first 600 bytes only.
			$rx_ts_head = is_readable( $rx_ts_path ) ? (string) file_get_contents( $rx_ts_path, false, null, 0, 600 ) : '';
			if ( preg_match( '/\bwidth=[\'"](\d+)/', $rx_ts_head, $rx_ts_m ) ) {
				$rx_ts_w = (int) $rx_ts_m[1];
			}
		} else {
			$rx_ts_size = is_readable( $rx_ts_path ) ? getimagesize( $rx_ts_path ) : false;
			if ( $rx_ts_size ) {
				$rx_ts_w = (int) round( $rx_ts_size[0] * 40 / max( 1, $rx_ts_size[1] ) ); // Keep the aspect ratio at 40px tall.
			}
		}
		$rx_ts_logos[] = array( $rx_ts_file, $rx_ts_name, $rx_ts_w );
	}
	if ( $rx_ts_logos ) {
		$rx_ts_from = $rx_ts_folder[1] . '/assets/img/trusted/';
		break;
	}
	if ( get_stylesheet_directory() === get_template_directory() ) {
		break; // No child theme: the same folder was just read.
	}
}

if ( ! $rx_ts_logos ) {
	return; // Nothing to show: hide the whole strip rather than render an empty band.
}
sort( $rx_ts_logos );

$rx_ts_placeholder = get_template_directory_uri() . '/assets/img/trusted/' === $rx_ts_from;
$rx_ts_title       = trim( rx_text( $rx_ts_args['title'] ?? __( 'Billing every day at medical stores, chains & hospital pharmacies', 'pharmacy' ) ) );
$rx_ts_note        = trim( rx_text( $rx_ts_args['note'] ?? __( 'A few of them', 'pharmacy' ) ) );

/*
 * One row of logos. The duplicate row (for the seamless loop) is hidden
 * from assistive tech and has empty alt text.
 */
$rx_ts_row = static function ( bool $copy ) use ( $rx_ts_logos, $rx_ts_from ): string {
	$out = '';
	foreach ( $rx_ts_logos as $logo ) {
		$out .= '<li><img src="' . esc_url( $rx_ts_from . rawurlencode( $logo[0] ) ) . '" width="' . (int) $logo[2] . '" height="40" loading="lazy" decoding="async" alt="'
			/* translators: %s: business name built from the logo's filename. */
			. ( $copy ? '' : esc_attr( sprintf( __( '%s logo', 'pharmacy' ), $logo[1] ) ) ) . '"></li>';
	}
	return $out;
};
?>
<section class="trust"<?php echo '' !== $rx_ts_title ? ' aria-labelledby="trust-t"' : ''; ?><?php echo $rx_ts_placeholder ? ' data-confirm' : ''; ?>>
  <div class="container trust-head">
    <?php if ( '' !== $rx_ts_title ) : ?><h2 id="trust-t"><?php echo $rx_ts_title; ?></h2><?php endif; ?>
    <?php if ( '' !== $rx_ts_note ) : ?><span class="trust-note"><?php echo $rx_ts_note; ?></span><?php endif; ?>
  </div>
  <div class="trust-marquee">
    <!-- Two identical rows; the track moves by exactly one row width, so the loop is seamless. -->
    <div class="trust-track">
      <ul class="trust-row"><?php echo $rx_ts_row( false ); ?></ul>
      <ul class="trust-row" aria-hidden="true"><?php echo $rx_ts_row( true ); ?></ul>
    </div>
  </div>
</section>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
