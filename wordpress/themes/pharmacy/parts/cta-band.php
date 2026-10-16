<?php
/**
 * Closing CTA band above the footer. Port of the band at the top of
 * partials/footer.php.
 *
 * footer.php prints it unless the template calls
 * get_footer( null, array( 'hide_cta' => true ) ) (home and demo do).
 *
 * $args (all optional):
 * - title      string Heading. Missing or null: Site_Settings `cta_title`.
 * - text       string Sentence under the heading. Missing or null: Site_Settings `cta_text`.
 * - demo_url   string "Book a free demo" target. Default: rx_url( 'demo' ).
 * - signup_url string "Start free" target. Default: Site_Settings `app_signup`;
 *                     the button is hidden when it is empty.
 *
 * Title and text go through rx_text(), so shortcodes such as
 * [rx_free_plan field="name"] work. The whole band is hidden when both
 * render empty.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_url() where they are built.

$rx_cta_args  = isset( $args ) && is_array( $args ) ? $args : array();
$rx_cta_title = trim( rx_text( $rx_cta_args['title'] ?? rx_setting( 'cta_title' ) ) );
$rx_cta_text  = trim( rx_text( $rx_cta_args['text'] ?? rx_setting( 'cta_text' ) ) );

if ( '' === $rx_cta_title && '' === $rx_cta_text ) {
	return;
}

$rx_cta_demo   = (string) ( $rx_cta_args['demo_url'] ?? rx_url( 'demo' ) );
$rx_cta_signup = (string) ( $rx_cta_args['signup_url'] ?? rx_setting( 'app_signup' ) );
?>
<section class="cta-band"<?php echo '' !== $rx_cta_title ? ' aria-labelledby="cta-band-title"' : ''; ?>>
  <div class="container cta-band-row">
    <div>
      <?php if ( '' !== $rx_cta_title ) : ?><h2 id="cta-band-title"><?php echo $rx_cta_title; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_cta_text ) : ?><p><?php echo $rx_cta_text; ?></p><?php endif; ?>
    </div>
    <div class="cta-band-btns">
      <a class="btn btn-light" href="<?php echo esc_url( $rx_cta_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <?php if ( '' !== $rx_cta_signup ) : ?><a class="btn btn-ghost-light" href="<?php echo esc_url( $rx_cta_signup ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
    </div>
  </div>
</section>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
