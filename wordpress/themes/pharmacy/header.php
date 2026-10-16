<?php
/**
 * Site header: document head, body start and the `.hd` header with the
 * mega menu. Port of partials/head.php and partials/header.php.
 *
 * - <head>: charset and viewport meta, then wp_head(). Yoast prints the
 *   title, description, robots, canonical, social tags and JSON-LD;
 *   inc/enqueue.php prints the Campton preloads, the favicon and the
 *   stylesheets.
 * - <body>: wp_body_open(), then the PHP site's order: SVG sprite, skip
 *   link, review banner (shown by site.js when the URL has ?review;
 *   elements with data-confirm need product-owner sign-off), header.
 * - The primary menu is the `primary` location printed by Rx_Mega_Walker
 *   (inc/nav.php). Nothing is printed for it until a menu is assigned.
 * - Log in, Start free and Book a free demo: Site_Settings `app_login`,
 *   `app_signup` and the demo page. A link whose setting is empty is not
 *   printed.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_hd_login  = (string) rx_setting( 'app_login' );
$rx_hd_signup = (string) rx_setting( 'app_signup' );
$rx_hd_demo   = rx_url( 'demo' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'parts/sprite' ); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Skip to main content', 'pharmacy' ); ?></a>
<div class="review-banner" role="note"><?php esc_html_e( 'Review mode: outlined items need product-owner confirmation before launch.', 'pharmacy' ); ?></div>

<header class="hd">
  <div class="container hd-row">
    <a class="brand" href="<?php echo esc_url( rx_url( 'home' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'pharmacy' ); ?>"><?php echo rx_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rx_logo() escapes the brand name. ?></a>

    <nav class="hd-nav" id="hd-nav" aria-label="<?php esc_attr_e( 'Primary', 'pharmacy' ); ?>">
      <?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul class="hd-menu">%3$s</ul>',
				'depth'          => 3,
				'fallback_cb'    => false,
				'walker'         => new Rx_Mega_Walker(),
			)
		);
		?>
      <div class="hd-mobile-actions">
        <a class="btn btn-primary" href="<?php echo esc_url( $rx_hd_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></a>
        <?php if ( '' !== $rx_hd_signup ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_hd_signup ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
        <?php if ( '' !== $rx_hd_login ) : ?><a class="hd-login" href="<?php echo esc_url( $rx_hd_login ); ?>"><?php esc_html_e( 'Log in', 'pharmacy' ); ?></a><?php endif; ?>
      </div>
    </nav>

    <div class="hd-actions">
      <?php if ( '' !== $rx_hd_login ) : ?><a class="hd-login" href="<?php echo esc_url( $rx_hd_login ); ?>"><?php esc_html_e( 'Log in', 'pharmacy' ); ?></a><?php endif; ?>
      <?php if ( '' !== $rx_hd_signup ) : ?><a class="btn btn-secondary btn-sm" href="<?php echo esc_url( $rx_hd_signup ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
      <a class="btn btn-primary btn-sm" href="<?php echo esc_url( $rx_hd_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></a>
      <button class="hd-burger" type="button" aria-expanded="false" aria-controls="hd-nav" aria-label="<?php esc_attr_e( 'Open menu', 'pharmacy' ); ?>"><svg class="icon" aria-hidden="true"><use href="#i-menu"/></svg></button>
    </div>
  </div>
</header>
<?php
unset( $rx_hd_login, $rx_hd_signup, $rx_hd_demo );
