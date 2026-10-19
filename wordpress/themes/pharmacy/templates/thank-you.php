<?php
/**
 * Template Name: Thank you
 *
 * Where the booking forms land after a successful send (Lead Guard's
 * `lead_guard_redirect`, set on each form by import/forms-setup.php).
 *
 * - The page content is the line under the heading, edited in the editor.
 *   Empty: the default line below.
 * - assets/js/forms.js adds ", {first name}" to the heading when the visitor
 *   came from a form.
 * - Three next steps link to pages that exist, by page key.
 *
 * The page is noindex (set by the import). No timing is promised: the
 * "how soon we call" wording is not confirmed.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by esc_*() / wp_kses_post() where they are built.

$rx_ty_body = '';
if ( have_posts() ) {
	the_post();
	$rx_ty_body = trim( (string) apply_filters( 'the_content', get_the_content() ) );
}

$rx_ty_next = array(
	array( 'features', __( 'See the features', 'pharmacy' ), __( 'Billing, expiry, purchase and reports.', 'pharmacy' ) ),
	array( 'switch', __( 'How switching works', 'pharmacy' ), __( 'Keep billing while you move over.', 'pharmacy' ) ),
	array( 'pricing', __( 'Compare plans', 'pharmacy' ), __( 'From the free Starter plan up.', 'pharmacy' ) ),
);

get_header();
?>
<main id="main">
<section class="section">
  <div class="container thanks">
    <div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></div>
    <h1><?php esc_html_e( 'Thank you', 'pharmacy' ); ?><span data-thanks-name></span><?php esc_html_e( ', we’ll call you soon', 'pharmacy' ); ?></h1>
    <?php if ( '' !== $rx_ty_body ) : ?>
      <?php echo wp_kses_post( $rx_ty_body ); ?>
    <?php else : ?>
      <p><?php esc_html_e( 'We have your request. Keep billing as you do today: nothing changes until you choose.', 'pharmacy' ); ?></p>
    <?php endif; ?>
    <ul class="thanks-next">
      <?php foreach ( $rx_ty_next as $rx_ty_item ) : ?>
      <li><a href="<?php echo esc_url( rx_url( $rx_ty_item[0] ) ); ?>"><?php echo esc_html( $rx_ty_item[1] ); ?></a><span><?php echo esc_html( $rx_ty_item[2] ); ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer( null, array( 'hide_cta' => true ) );
