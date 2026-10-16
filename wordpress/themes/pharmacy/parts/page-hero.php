<?php
/**
 * Inner-page hero with breadcrumb. Port of partials/page-hero.php.
 *
 * $args (all optional):
 * - hero       array  The SCF `hero` group of the page: crumb, parent, eyebrow,
 *                     h1, lead, meta, pattern. Top-level keys below override
 *                     the same keys in `hero` (null values are ignored).
 * - crumb      string Current-page crumb. Empty: the queried post's title.
 * - parent     string Middle crumb, plain text unless parent_url is set.
 * - parent_url string Turns the parent crumb into a link (posts link back to the blog).
 * - eyebrow    string
 * - h1         string Heading. Empty: the queried post's title.
 * - lead       string Lead paragraph.
 * - meta       string Meta line, e.g. "Last updated: [date]" on legal pages.
 * - pattern    string|bool Background pattern (.page-hero.ph2 in layout.css):
 *                     'on' / true, 'off' / false, or 'inherit' / empty to follow
 *                     the Site_Settings `hero_pattern` switch (HERO_PATTERN today).
 *
 * Text values go through rx_text(), so shortcodes such as [rx_brand] work.
 * An element whose value renders empty is not printed.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_url() where they are built.

$rx_ph_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_ph      = array_merge(
	isset( $rx_ph_args['hero'] ) && is_array( $rx_ph_args['hero'] ) ? $rx_ph_args['hero'] : array(),
	array_filter(
		array_diff_key( $rx_ph_args, array( 'hero' => true ) ),
		static function ( $v ) {
			return null !== $v;
		}
	)
);

// Background pattern: explicit on/off, else the site-wide switch.
$rx_ph_pattern = $rx_ph['pattern'] ?? 'inherit';
if ( is_bool( $rx_ph_pattern ) ) {
	$rx_ph_on = $rx_ph_pattern;
} elseif ( 'on' === $rx_ph_pattern ) {
	$rx_ph_on = true;
} elseif ( 'off' === $rx_ph_pattern ) {
	$rx_ph_on = false;
} else {
	$rx_ph_on = (bool) rx_setting( 'hero_pattern' );
}

// The page title is today's breadcrumb label; it also stands in for an empty h1.
$rx_ph_object = get_queried_object();
$rx_ph_title  = $rx_ph_object instanceof WP_Post ? rx_text( $rx_ph_object->post_title ) : '';

$rx_ph_text = array();
foreach ( array( 'crumb', 'parent', 'eyebrow', 'h1', 'lead', 'meta' ) as $rx_ph_key ) {
	$rx_ph_text[ $rx_ph_key ] = trim( rx_text( $rx_ph[ $rx_ph_key ] ?? '' ) );
}
if ( '' === $rx_ph_text['crumb'] ) {
	$rx_ph_text['crumb'] = $rx_ph_title;
}
if ( '' === $rx_ph_text['h1'] ) {
	$rx_ph_text['h1'] = $rx_ph_title;
}
$rx_ph_parent_url = isset( $rx_ph['parent_url'] ) && is_string( $rx_ph['parent_url'] ) ? $rx_ph['parent_url'] : '';
?>
<section class="page-hero<?php echo $rx_ph_on ? ' ph2' : ''; ?>">
  <div class="container">
    <ol class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmacy' ); ?>">
      <li><a href="<?php echo esc_url( rx_url( 'home' ) ); ?>"><?php esc_html_e( 'Home', 'pharmacy' ); ?></a></li>
      <?php if ( '' !== $rx_ph_text['parent'] ) : ?><li><?php if ( '' !== $rx_ph_parent_url ) : ?><a href="<?php echo esc_url( $rx_ph_parent_url ); ?>"><?php echo $rx_ph_text['parent']; ?></a><?php else : ?><?php echo $rx_ph_text['parent']; ?><?php endif; ?></li><?php endif; ?>
      <li aria-current="page"><?php echo $rx_ph_text['crumb']; ?></li>
    </ol>
    <?php if ( '' !== $rx_ph_text['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_ph_text['eyebrow']; ?></span><?php endif; ?>
    <h1><?php echo $rx_ph_text['h1']; ?></h1>
    <?php if ( '' !== $rx_ph_text['lead'] ) : ?><p class="lead"><?php echo $rx_ph_text['lead']; ?></p><?php endif; ?>
    <?php if ( '' !== $rx_ph_text['meta'] ) : ?><p class="meta-line"><?php echo $rx_ph_text['meta']; ?></p><?php endif; ?>
  </div>
</section>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
