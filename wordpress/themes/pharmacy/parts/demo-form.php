<?php
/**
 * Demo request form. Port of partials/demo-form.php (also the form on the
 * home page): same ids, names and attributes, so site.js validates it, syncs
 * #submit-label with #f-want (and ?want=quote) and shows #form-success.
 * There is no action and no endpoint: nothing is sent anywhere.
 *
 * Use once per page (fixed ids).
 *
 * $args (all optional; missing or null uses today's text, '' hides the element):
 * - title         string Form heading. Default "Book your free demo".
 * - sub           string Line under the heading. Default "Tell us about your store and we’ll call you."
 * - success_title string Thank-you heading. Default "Thank you, we’ll call you soon"
 *                        (the home page passes its own "Thank you — …").
 *
 * The three values go through rx_text(), so shortcodes work. Field labels,
 * options and messages are translatable strings: they are tied to the
 * script's ids and option values.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_html__() / esc_url() where they are built.

$rx_df_args    = isset( $args ) && is_array( $args ) ? $args : array();
$rx_df_title   = trim( rx_text( $rx_df_args['title'] ?? __( 'Book your free demo', 'pharmacy' ) ) );
$rx_df_sub     = trim( rx_text( $rx_df_args['sub'] ?? __( 'Tell us about your store and we’ll call you.', 'pharmacy' ) ) );
$rx_df_success = trim( rx_text( $rx_df_args['success_title'] ?? __( 'Thank you, we’ll call you soon', 'pharmacy' ) ) );
$rx_df_form = function_exists( 'rx_form_html' ) ? rx_form_html( 'demo', __( 'Book a free demo', 'pharmacy' ) ) : '';

if ( '' !== $rx_df_form ) :
	// Contact Form 7 booking form: same card, heading and privacy line; Lead Guard opens the thank-you page after a send.
	?>
<div class="form-card" data-lead-guard-wrap>
  <?php if ( '' !== $rx_df_title ) : ?><h3><?php echo $rx_df_title; ?></h3><?php endif; ?>
  <?php if ( '' !== $rx_df_sub ) : ?><p><?php echo $rx_df_sub; ?></p><?php endif; ?>
  <?php echo $rx_df_form; ?>
  <p class="form-small" data-confirm><?php
		printf(
			/* translators: %s: "privacy policy" link. */
			esc_html__( 'We’ll only use your details to contact you about this request. See our %s.', 'pharmacy' ),
			'<a href="' . esc_url( rx_url( 'privacy' ) ) . '">' . esc_html__( 'privacy policy', 'pharmacy' ) . '</a>'
		);
		?></p>
</div>
<?php else : // Fallback: the original script-driven form. ?>
<div class="form-card">
  <form id="demo-form" novalidate>
    <?php if ( '' !== $rx_df_title ) : ?><h3><?php echo $rx_df_title; ?></h3><?php endif; ?>
    <?php if ( '' !== $rx_df_sub ) : ?><p><?php echo $rx_df_sub; ?></p><?php endif; ?>
    <div class="form-grid">
      <div class="fld"><label for="f-name"><?php esc_html_e( 'Your name', 'pharmacy' ); ?></label><input id="f-name" name="name" autocomplete="name" required><span class="err"><?php esc_html_e( 'Please enter your name.', 'pharmacy' ); ?></span></div>
      <div class="fld"><label for="f-phone"><?php esc_html_e( 'Mobile number', 'pharmacy' ); ?></label><input id="f-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="<?php esc_attr_e( '10-digit mobile', 'pharmacy' ); ?>" required pattern="[6-9][0-9]{9}"><span class="err"><?php esc_html_e( 'Please enter a 10-digit mobile number.', 'pharmacy' ); ?></span></div>
      <div class="fld"><label for="f-store"><?php esc_html_e( 'Store or hospital name', 'pharmacy' ); ?></label><input id="f-store" name="store" autocomplete="organization" required><span class="err"><?php esc_html_e( 'Please enter your store name.', 'pharmacy' ); ?></span></div>
      <div class="fld"><label for="f-city"><?php esc_html_e( 'City', 'pharmacy' ); ?></label><input id="f-city" name="city" autocomplete="address-level2" required><span class="err"><?php esc_html_e( 'Please enter your city.', 'pharmacy' ); ?></span></div>
      <div class="fld full"><label for="f-current"><?php esc_html_e( 'What do you use today?', 'pharmacy' ); ?></label>
        <select id="f-current" name="current">
          <option value=""><?php esc_html_e( 'Select one', 'pharmacy' ); ?></option><option><?php esc_html_e( 'Desktop pharmacy software', 'pharmacy' ); ?></option><option><?php esc_html_e( 'General billing app', 'pharmacy' ); ?></option><option><?php esc_html_e( 'Excel or paper registers', 'pharmacy' ); ?></option><option><?php esc_html_e( 'Hospital software', 'pharmacy' ); ?></option><option><?php esc_html_e( 'Opening a new store', 'pharmacy' ); ?></option>
        </select></div>
      <div class="fld full"><label for="f-want"><?php esc_html_e( 'I’d like to', 'pharmacy' ); ?></label>
        <select id="f-want" name="want">
          <option value="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></option><option value="quote"><?php esc_html_e( 'Get Enterprise pricing for my stores', 'pharmacy' ); ?></option><option value="checklist"><?php esc_html_e( 'Get the buyer’s checklist', 'pharmacy' ); ?></option>
        </select></div>
    </div>
    <button class="btn btn-primary" type="submit"><span id="submit-label"><?php esc_html_e( 'Book my free demo', 'pharmacy' ); ?></span> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <p class="form-small" data-confirm><?php
		printf(
			/* translators: %s: "privacy policy" link. */
			esc_html__( 'We’ll only use your details to contact you about this request. See our %s.', 'pharmacy' ),
			'<a href="' . esc_url( rx_url( 'privacy' ) ) . '">' . esc_html__( 'privacy policy', 'pharmacy' ) . '</a>'
		);
		?></p>
  </form>
  <div class="form-success" id="form-success" hidden tabindex="-1">
    <img class="hb" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/heartbeat.gif' ) ); ?>" width="120" height="115" alt="" loading="lazy" decoding="async"><div class="sol-ic hb-still"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></div>
    <?php if ( '' !== $rx_df_success ) : ?><h3><?php echo $rx_df_success; ?></h3><?php endif; ?>
    <p><?php esc_html_e( 'Prototype only: nothing was sent.', 'pharmacy' ); ?></p>
  </div>
</div>
<?php endif; ?>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
