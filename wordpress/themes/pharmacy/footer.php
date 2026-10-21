<?php
/**
 * Site footer: closing CTA band, `.ft` footer, mobile bar, then
 * wp_footer() (scripts) and the closing tags. Port of partials/footer.php.
 *
 * $args (from get_footer( null, $args )):
 * - hide_cta bool Skip the CTA band (home and demo pass true).
 * - cta      array Optional title and text for the band (the solution pages pass their own).
 *
 * - CTA band: parts/cta-band (Site_Settings `cta_title` and `cta_text`).
 * - Brand column: logo, Site_Settings `footer_blurb`, the sales and support
 *   phones, `email` and the `social` profiles. Each element is skipped when
 *   its setting is empty (email and social are empty today).
 * - Link columns: the menus at footer_features, footer_solutions,
 *   footer_resources and footer_company (Rx_Footer_Walker); each heading is
 *   the menu's name. A location without a menu or items prints no column.
 * - Bottom row: "© {year} {brand}." plus Site_Settings `footer_note`, and
 *   the `legal` menu. site.js updates [data-year] to the current year.
 * - `.ft-word`: the Site Title, printed only when it is set.
 * - Mobile bar: call sales, Start free (`app_signup`) and Book demo.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- markup comes from rx_logo(), rx_text() and the nav.php menu helpers, which escape their values.

$rx_ft_args   = isset( $args ) && is_array( $args ) ? $args : array();
$rx_ft_brand  = rx_brand();
$rx_ft_signup = (string) rx_setting( 'app_signup' );
$rx_ft_demo   = rx_url( 'demo' );
$rx_ft_blurb  = trim( rx_text( rx_setting( 'footer_blurb' ) ) );
$rx_ft_note   = trim( rx_text( rx_setting( 'footer_note' ) ) );
$rx_ft_email  = trim( (string) rx_setting( 'email' ) );
$rx_ft_social = rx_setting( 'social' );

/*
 * Phone links: "tel:" + the Site_Settings tel value, or the digits of the
 * displayed number when the tel value is empty. Not printed without a number.
 */
$rx_ft_phones = array();
foreach (
	array(
		'sales'   => array( 'i-phone', __( 'Sales', 'pharmacy' ) ),
		'support' => array( 'i-headset', __( 'Support', 'pharmacy' ) ),
	) as $rx_ft_kind => $rx_ft_phone_meta
) {
	$rx_ft_number = trim( (string) rx_setting( $rx_ft_kind . '_phone' ) );
	$rx_ft_tel    = trim( (string) rx_setting( $rx_ft_kind . '_tel' ) );
	if ( '' === $rx_ft_tel ) {
		$rx_ft_tel = (string) preg_replace( '/[^0-9+]/', '', $rx_ft_number );
	}
	$rx_ft_phones[ $rx_ft_kind ] = array(
		'icon'   => $rx_ft_phone_meta[0],
		'label'  => $rx_ft_phone_meta[1],
		'number' => $rx_ft_number,
		'href'   => '' !== $rx_ft_tel ? 'tel:' . $rx_ft_tel : '',
	);
}

// Sprite icon per social network (the `network` select values).
$rx_ft_social_icons = array(
	'LinkedIn'  => 'i-linkedin',
	'YouTube'   => 'i-youtube',
	'Instagram' => 'i-instagram',
	'Facebook'  => 'i-facebook',
	'X'         => 'i-xsocial',
);
$rx_ft_social_rows  = array();
foreach ( is_array( $rx_ft_social ) ? $rx_ft_social : array() as $rx_ft_row ) {
	$rx_ft_network = is_array( $rx_ft_row ) && isset( $rx_ft_row['network'] ) ? (string) $rx_ft_row['network'] : '';
	$rx_ft_url     = is_array( $rx_ft_row ) && isset( $rx_ft_row['url'] ) ? (string) $rx_ft_row['url'] : '';
	if ( '' === $rx_ft_url || ! isset( $rx_ft_social_icons[ $rx_ft_network ] ) ) {
		continue;
	}
	$rx_ft_social_rows[] = array(
		'network' => $rx_ft_network,
		'url'     => $rx_ft_url,
		'icon'    => $rx_ft_social_icons[ $rx_ft_network ],
	);
}

if ( empty( $rx_ft_args['hide_cta'] ) ) {
	get_template_part( 'parts/cta-band', null, isset( $rx_ft_args['cta'] ) && is_array( $rx_ft_args['cta'] ) ? $rx_ft_args['cta'] : array() );
}
?>

<footer class="ft" id="contact-footer">
  <div class="container">
    <div class="ft-top">
      <div class="ft-brand">
        <a class="brand" href="<?php echo esc_url( rx_url( 'home' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'pharmacy' ); ?>"><?php echo rx_logo( true ); ?></a>
        <?php if ( '' !== $rx_ft_blurb ) : ?><p><?php echo $rx_ft_blurb; ?></p><?php endif; ?>
        <ul class="ft-contact">
          <?php foreach ( $rx_ft_phones as $rx_ft_phone ) : ?>
            <?php if ( '' !== $rx_ft_phone['number'] && '' !== $rx_ft_phone['href'] ) : ?><li><a href="<?php echo esc_url( $rx_ft_phone['href'] ); ?>"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_ft_phone['icon'] ); ?>"/></svg><?php echo esc_html( $rx_ft_phone['label'] . ' ' . $rx_ft_phone['number'] ); ?></a></li><?php endif; ?>
          <?php endforeach; ?>
          <?php if ( '' !== $rx_ft_email ) : ?><li><a href="<?php echo esc_url( 'mailto:' . $rx_ft_email ); ?>"><svg class="icon" aria-hidden="true"><use href="#i-mail"/></svg><?php echo esc_html( $rx_ft_email ); ?></a></li><?php endif; ?>
        </ul>
        <?php if ( $rx_ft_social_rows ) : ?>
        <ul class="ft-social" aria-label="<?php esc_attr_e( 'Social media', 'pharmacy' ); ?>">
          <?php foreach ( $rx_ft_social_rows as $rx_ft_row ) : ?>
            <li><a href="<?php echo esc_url( $rx_ft_row['url'] ); ?>" aria-label="<?php echo esc_attr( $rx_ft_row['network'] ); ?>" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_ft_row['icon'] ); ?>"/></svg></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <?php
		foreach (
			array(
				'features'  => 'footer_features',
				'solutions' => 'footer_solutions',
				'resources' => 'footer_resources',
				'company'   => 'footer_company',
			) as $rx_id => $rx_location
		) {
			$rx_links = rx_footer_menu( $rx_location );
			if ( '' === $rx_links ) {
				continue;
			}
			?><nav aria-labelledby="ft-h-<?php echo esc_attr( $rx_id ); ?>"><h3 id="ft-h-<?php echo esc_attr( $rx_id ); ?>"><?php echo esc_html( rx_menu_name( $rx_location ) ); ?></h3><?php echo $rx_links; ?></nav>
      <?php
		}
		?>
    </div>
    <div class="ft-bottom">
      <span>© <span data-year><?php echo esc_html( wp_date( 'Y' ) ); ?></span><?php echo '' !== $rx_ft_brand ? ' ' . esc_html( $rx_ft_brand ) : ''; ?>.<?php echo '' !== $rx_ft_note ? ' ' . $rx_ft_note : ''; ?></span>
      <?php $rx_legal = rx_legal_links(); ?>
      <?php if ( '' !== $rx_legal ) : ?>
      <nav aria-label="<?php esc_attr_e( 'Legal', 'pharmacy' ); ?>"><?php echo $rx_legal; ?></nav>
      <?php endif; ?>
    </div>
    <?php if ( '' !== $rx_ft_brand ) : ?><p class="ft-word" aria-hidden="true"><?php echo esc_html( $rx_ft_brand ); ?></p><?php endif; ?>
  </div>
</footer>

<div class="mobile-bar" aria-label="<?php esc_attr_e( 'Quick actions', 'pharmacy' ); ?>">
  <?php if ( '' !== $rx_ft_phones['sales']['href'] ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_ft_phones['sales']['href'] ); ?>"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg><?php esc_html_e( 'Call', 'pharmacy' ); ?></a><?php endif; ?>
  <?php if ( '' !== $rx_ft_signup ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_ft_signup ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
  <a class="btn btn-primary" href="<?php echo esc_url( $rx_ft_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book demo', 'pharmacy' ); ?></a>
</div>

<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
wp_footer();
?>
</body>
</html>
