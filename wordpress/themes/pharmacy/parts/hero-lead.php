<?php
/**
 * Hero lead capture: a 2-field "demo slip" styled like a counter bill
 * (perforation + barcode). Port of partials/hero-lead.php.
 *
 * Behaviour in assets/js/site.js ([data-lead-form]): validates, shows the
 * inline thank-you and copies name + mobile into the full #demo form.
 * Nothing is sent anywhere.
 *
 * $args (all optional):
 * - id   string Id prefix, unique per page (today's $lead_id). Default 'hl';
 *               the home hero passes 'hl1'.
 * - more string Target of the "Add your store details" link. Default '#demo'.
 *
 * The slip's copy is a set of translatable strings: it is tool UI tied to
 * the script. The slip code follows the Site Title, as it follows BRAND today.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by esc_attr() / esc_html() where they are built; rx_barcode_svg() returns escaped markup.

$rx_hl_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_hl_id   = esc_attr( isset( $rx_hl_args['id'] ) && is_string( $rx_hl_args['id'] ) && '' !== $rx_hl_args['id'] ? $rx_hl_args['id'] : 'hl' );
$rx_hl_more = isset( $rx_hl_args['more'] ) && is_string( $rx_hl_args['more'] ) && '' !== $rx_hl_args['more'] ? $rx_hl_args['more'] : '#demo';
$rx_hl_code = strtoupper( mb_substr( '' !== rx_brand() ? rx_brand() : 'RX', 0, 3 ) ) . ' · DEMO · 0001'; // Follows the Site Title, no hard-coded name.
?>
<form class="slip" data-lead-form novalidate aria-labelledby="<?php echo $rx_hl_id; ?>-t">
  <div class="slip-body">
    <div class="slip-top">
      <h2 class="slip-title" id="<?php echo $rx_hl_id; ?>-t"><?php esc_html_e( 'Get a free demo call', 'pharmacy' ); ?></h2>
      <span class="slip-no" aria-hidden="true"><?php esc_html_e( 'DEMO&nbsp;SLIP · No.&nbsp;0001', 'pharmacy' ); ?></span>
    </div>
    <div class="slip-fields">
      <div class="fld"><label for="<?php echo $rx_hl_id; ?>-name"><?php esc_html_e( 'Your name', 'pharmacy' ); ?></label>
        <input id="<?php echo $rx_hl_id; ?>-name" name="name" autocomplete="name" required>
        <span class="err"><?php esc_html_e( 'Please enter your name.', 'pharmacy' ); ?></span></div>
      <div class="fld"><label for="<?php echo $rx_hl_id; ?>-phone"><?php esc_html_e( 'Mobile number', 'pharmacy' ); ?></label>
        <span class="slip-tel"><span aria-hidden="true">+91</span><input id="<?php echo $rx_hl_id; ?>-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="98xxxxxx21" required pattern="[6-9][0-9]{9}"></span>
        <span class="err"><?php esc_html_e( 'Please enter a 10-digit mobile number.', 'pharmacy' ); ?></span></div>
    </div>
    <button class="btn btn-primary slip-btn" type="submit"><?php esc_html_e( 'Call me for a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <ul class="slip-terms">
      <li><?php esc_html_e( '30-minute call', 'pharmacy' ); ?></li><li><?php esc_html_e( 'Shown with your own medicines', 'pharmacy' ); ?></li><li><?php esc_html_e( 'No payment, no obligation', 'pharmacy' ); ?></li>
    </ul>
  </div>
  <div class="slip-done" hidden tabindex="-1">
    <svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg>
    <div><b><?php
		printf(
			/* translators: %s: empty placeholder the script fills with ", {first name}". */
			esc_html__( 'Done%s, we’ll call you.', 'pharmacy' ),
			'<span data-lead-name></span>'
		);
		?></b>
      <span data-confirm><?php esc_html_e( 'Usually within one working day.', 'pharmacy' ); ?></span> <?php esc_html_e( 'Want a better demo?', 'pharmacy' ); ?> <a href="<?php echo esc_url( $rx_hl_more ); ?>"><?php esc_html_e( 'Add your store details', 'pharmacy' ); ?></a>.
      <small><?php esc_html_e( 'Prototype: nothing was sent.', 'pharmacy' ); ?></small></div>
  </div>
  <div class="slip-foot" aria-hidden="true">
    <?php echo rx_barcode_svg( $rx_hl_code, 44, 'slip-bc' ); ?>
    <span><?php echo esc_html( $rx_hl_code ); ?></span>
  </div>
</form>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
