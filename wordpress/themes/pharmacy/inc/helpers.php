<?php
/**
 * Template helpers: settings, fields, URLs, page key, assets, images, text.
 *
 * WordPress ports of the PHP site's partials/config.php helpers. Same
 * arguments and markup; names get the rx_ prefix:
 *
 *   BRAND                            rx_brand()         Site Title
 *   SITE[], HERO_PATTERN, PRICING_*  rx_setting()       Site_Settings (SCF options page rx-settings)
 *   –                                rx_field()         get_field() wrapper
 *   url()                            rx_url()           page found by its _rx_key meta, or a fallback
 *   $page_key                        rx_page_key()
 *   asset()                          rx_asset()
 *   img_tag(), svg_img(), logo()     rx_img(), rx_svg_img(), rx_logo()
 *   NAV_FEATURES lookups             rx_feature_nav()   read from the primary menu
 *   –                                rx_text(), rx_html()  print SCF values (shortcodes run here)
 *
 * Every SCF call is guarded with function_exists(), so the theme still
 * renders when SCF is inactive.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * Brand, settings and fields
 * -------------------------------------------------------------------------
 */

/**
 * Working brand name: the WordPress Site Title (Settings → General).
 *
 * Plain text with HTML entities decoded; escape it on output. '' when the
 * Site Title is empty, in which case the header and footer show the logo
 * mark only (as BRAND = '' does on the PHP site).
 */
function rx_brand(): string {
	return trim( wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ) );
}

/**
 * Default Site_Settings values: the PHP site's partials/config.php
 * (SITE, HERO_PATTERN and PRICING_*), plus the map query from contact.php
 * and the promo image from header.php.
 *
 * Used until the import has saved Site_Settings, or when SCF is inactive,
 * so the site works before the import runs. Field names match
 * acf-json/group_rx_settings.json. Copy fields (footer blurb, CTA band,
 * promo text) have no default: the import fills them.
 *
 * The URLs are link targets only and are never printed as text.
 *
 * @return array<string, string|int|bool|array> Setting name => default.
 */
function rx_setting_defaults(): array {
	return array(
		// Contacts.
		'sales_phone'      => '+91 97148 74435',
		'sales_tel'        => '+919714874435',
		'support_phone'    => '+91 89808 80819',
		'support_tel'      => '+918980880819',
		'email'            => '',
		'company'          => '[Company legal name]',
		'address'          => '1st Floor, A - Millenium Point, opp. Gabani Kidney Hospital, Lal Darwaja, Surat, Gujarat 395003',
		'map_query'        => 'Millenium Point, Lal Darwaja, Surat, Gujarat 395003',
		// Links.
		'app_login'        => 'https://pharmacy.healthray.com/login',
		'app_signup'       => 'https://pharmacy.healthray.com/signup',
		'help_url'         => 'https://support.healthray.com',
		'calendly'         => 'https://calendly.com/healthray/healthray-technologies-lead-meeting',
		// Social.
		'social'           => array(),
		// Layout.
		'hero_pattern'     => true,
		'footer_blurb'     => '',
		'footer_note'      => '',
		'cta_title'        => '',
		'cta_text'         => '',
		'promo_image'      => 'v2-02-dashboard',
		'promo_alt'        => '',
		'promo_title'      => '',
		'promo_text'       => '',
		'promo_link_label' => '',
		// Pricing.
		'pricing_api'      => 'https://pharmacyapi.healthray.com/api/subscription/pricing/public',
		'pricing_ttl'      => 300,
		'pricing_retry'    => 60,
	);
}

/**
 * A Site_Settings value (SCF options page rx-settings, post id "options").
 *
 * - SCF inactive, or the setting never saved (the import has not run):
 *   the default from rx_setting_defaults().
 * - Saved: the saved value, typed like its default. A saved empty text
 *   stays '' so the element that uses it is hidden, as `email` and
 *   `social` are today.
 *
 * Types:
 * - Text, URL and select settings: string.
 * - `hero_pattern`: bool.
 * - `pricing_ttl`, `pricing_retry`: int, the default when not a positive
 *   number (0 would make the pricing transient never expire).
 * - `social`: list of [ 'network' => string, 'url' => string ]; rows
 *   without a URL are dropped.
 * - A key with no default: the SCF value, or '' when empty.
 *
 * Before SCF's acf/init (for example an HTTP request made while plugins
 * load) the stored option is read directly, so SCF never warns about early
 * field access.
 *
 * @param string $key Setting name, e.g. 'sales_phone' or 'pricing_api'.
 * @return mixed
 */
function rx_setting( string $key ) {
	$defaults    = rx_setting_defaults();
	$has_default = array_key_exists( $key, $defaults );
	$default     = $has_default ? $defaults[ $key ] : '';

	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	// SCF stores options page values as options_{name} (the row count for a repeater).
	$raw = get_option( 'options_' . $key, null );
	if ( null === $raw ) {
		return $default;
	}

	$value = did_action( 'acf/init' ) ? get_field( $key, 'option' ) : $raw;

	if ( ! $has_default ) {
		return ( null === $value || false === $value ) ? '' : $value;
	}
	if ( is_bool( $default ) ) {
		return (bool) $value;
	}
	if ( is_int( $default ) ) {
		return ( is_numeric( $value ) && (int) $value > 0 ) ? (int) $value : $default;
	}
	if ( is_array( $default ) ) {
		return 'social' === $key ? rx_social_rows( $value ) : ( is_array( $value ) ? $value : $default );
	}
	return ( is_scalar( $value ) && ! is_bool( $value ) ) ? (string) $value : '';
}

/**
 * Social profile rows from the `social` repeater, cleaned up.
 *
 * @param mixed $rows Repeater value (array of rows, or false when empty).
 * @return array<int, array{network: string, url: string}>
 */
function rx_social_rows( $rows ): array {
	$out = array();
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$url = is_array( $row ) && isset( $row['url'] ) && is_string( $row['url'] ) ? trim( $row['url'] ) : '';
		if ( '' === $url ) {
			continue;
		}
		$out[] = array(
			'network' => isset( $row['network'] ) && is_string( $row['network'] ) ? $row['network'] : '',
			'url'     => $url,
		);
	}
	return $out;
}

/**
 * An SCF field value: get_field() with the same arguments.
 *
 * @param string          $name    Field name.
 * @param int|string|null $post_id Post id, 'option', a WP_Post or null for the current post.
 * @return mixed Formatted value, or null when SCF is inactive.
 */
function rx_field( string $name, $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return get_field( $name, null === $post_id ? false : $post_id );
}

/*
 * -------------------------------------------------------------------------
 * URLs and the page key
 * -------------------------------------------------------------------------
 */

/**
 * Published pages that carry a page key, as [ _rx_key => page id ].
 *
 * Built once per request (one query; post meta is primed with it) and
 * rebuilt after any post cache is cleaned, so a page saved during the same
 * request is found. When two pages share a key, the oldest one wins.
 *
 * @param bool $reset Forget the cached map (returns an empty array).
 * @return array<string, int>
 */
function rx_page_map( bool $reset = false ): array {
	static $map = null;

	if ( $reset ) {
		$map = null;
		return array();
	}
	if ( null !== $map ) {
		return $map;
	}

	$map   = array();
	$pages = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'numberposts'            => -1,
			'meta_key'               => '_rx_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one small query per request.
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	foreach ( $pages as $page ) {
		$key = get_post_meta( $page->ID, '_rx_key', true );
		if ( is_string( $key ) && '' !== $key && ! isset( $map[ $key ] ) ) {
			$map[ $key ] = (int) $page->ID;
		}
	}
	return $map;
}

/**
 * Drops the cached page map when a post changes.
 */
function rx_page_map_reset(): void {
	rx_page_map( true );
}
add_action( 'clean_post_cache', 'rx_page_map_reset' );

/**
 * URL of a page key. Port of url().
 *
 * - `home`: the front page.
 * - A key with a published page (`_rx_key` meta): that page's permalink.
 * - `guides`: the same as `blog` (the blog is the guides hub). `blog`
 *   falls back to the WordPress posts page.
 * - Pages not built yet keep pointing at today's home page anchors:
 *   `retail`, `chain`, `hospital` → /#solutions, `new-pharmacy` →
 *   /#switching, `calculator` → /#estimator, `checklist` → /#checklist.
 *   Once a page with that key exists, links switch to it automatically.
 * - Anything else: the home page.
 *
 * @param string $key Page key, e.g. 'pricing', 'billing', 'refund'.
 * @return string Absolute URL, unescaped (escape with esc_url() on output).
 */
function rx_url( string $key ): string {
	if ( 'home' === $key ) {
		return home_url( '/' );
	}
	if ( 'guides' === $key ) {
		$key = 'blog';
	}

	$map = rx_page_map();
	if ( isset( $map[ $key ] ) ) {
		$url = get_permalink( $map[ $key ] );
		if ( $url ) {
			return $url;
		}
	}

	if ( 'blog' === $key ) {
		$blog = (int) get_option( 'page_for_posts' );
		$url  = $blog ? get_permalink( $blog ) : false;
		if ( $url ) {
			return $url;
		}
	}

	$anchors = array(
		'retail'       => 'solutions',
		'chain'        => 'solutions',
		'hospital'     => 'solutions',
		'new-pharmacy' => 'switching',
		'calculator'   => 'estimator',
		'checklist'    => 'checklist',
	);
	if ( isset( $anchors[ $key ] ) ) {
		return home_url( '/#' . $anchors[ $key ] );
	}

	return home_url( '/' );
}

/**
 * Page key of the current request (today's $page_key).
 *
 * Front page → 'home'; a page → its `_rx_key` meta; the posts page, a blog
 * topic view or a single post → 'blog'; anything else (404, a page without
 * a key) → 'generic'.
 */
function rx_page_key(): string {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_home() || is_singular( 'post' ) ) {
		return 'blog';
	}
	if ( is_page() ) {
		$key = get_post_meta( get_queried_object_id(), '_rx_key', true );
		return is_string( $key ) && '' !== $key ? $key : 'generic';
	}
	return 'generic';
}

/**
 * Page key a menu item points at.
 *
 * - A custom link whose URL is `#rx:{key}` → {key}.
 * - A link to a page → that page's `_rx_key` meta.
 * - Anything else → ''.
 *
 * @param object $item Menu item from wp_get_nav_menu_items() or a nav walker.
 */
function rx_menu_item_key( $item ): string {
	if ( ! is_object( $item ) ) {
		return '';
	}

	$url = isset( $item->url ) && is_string( $item->url ) ? $item->url : '';
	if ( str_starts_with( $url, '#rx:' ) ) {
		return substr( $url, 4 );
	}

	if ( isset( $item->type, $item->object, $item->object_id ) && 'post_type' === $item->type && 'page' === $item->object ) {
		$key = get_post_meta( (int) $item->object_id, '_rx_key', true );
		return is_string( $key ) ? $key : '';
	}
	return '';
}

/**
 * Feature pages as listed under "Features" in the primary menu, in menu
 * order. Replaces the PHP site's NAV_FEATURES lookups, so the menu, the
 * features hub, related rows and blog CTAs can't drift apart.
 *
 * The Features item is the top-level item titled "Features" (or else the
 * first top-level item with grandchildren). Its children are the groups
 * ("Run the counter", "Run the business"); their children are the pages.
 * Icon and description are the SCF fields of each menu item (the core
 * menu item description is used when the SCF field is empty).
 *
 * Empty until a menu is assigned to the primary location.
 *
 * @return array<string, array{label: string, description: string, icon: string, url: string, group: string}>
 *         Page key => item.
 */
function rx_feature_nav(): array {
	static $nav = null;
	if ( null !== $nav ) {
		return $nav;
	}
	$nav = array();

	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return $nav;
	}
	$items = wp_get_nav_menu_items( (int) $locations['primary'], array( 'update_post_term_cache' => false ) );
	if ( ! is_array( $items ) || ! $items ) {
		return $nav;
	}

	$children = array();
	foreach ( $items as $item ) {
		$children[ (int) $item->menu_item_parent ][] = $item;
	}

	$top = null;
	foreach ( $children[0] ?? array() as $item ) {
		if ( 'features' === sanitize_title( $item->title ) ) {
			$top = $item;
			break;
		}
	}
	if ( ! $top ) {
		foreach ( $children[0] ?? array() as $item ) {
			foreach ( $children[ (int) $item->ID ] ?? array() as $group ) {
				if ( ! empty( $children[ (int) $group->ID ] ) ) {
					$top = $item;
					break 2;
				}
			}
		}
	}
	if ( ! $top ) {
		return $nav;
	}

	foreach ( $children[ (int) $top->ID ] ?? array() as $group ) {
		foreach ( $children[ (int) $group->ID ] ?? array() as $item ) {
			$key = rx_menu_item_key( $item );
			if ( '' === $key || isset( $nav[ $key ] ) ) {
				continue;
			}
			$description = rx_field( 'description', (int) $item->ID );
			if ( ! is_string( $description ) || '' === $description ) {
				$description = isset( $item->description ) && is_string( $item->description ) ? $item->description : '';
			}
			$icon = rx_field( 'icon', (int) $item->ID );

			$nav[ $key ] = array(
				'label'       => wp_specialchars_decode( (string) $item->title, ENT_QUOTES ),
				'description' => $description,
				'icon'        => is_string( $icon ) ? $icon : '',
				'url'         => rx_url( $key ),
				'group'       => wp_specialchars_decode( (string) $group->title, ENT_QUOTES ),
			);
		}
	}
	return $nav;
}

/*
 * -------------------------------------------------------------------------
 * Assets, images and logo
 * -------------------------------------------------------------------------
 */

/**
 * URL of a theme asset. Port of asset().
 *
 * Uses get_theme_file_uri(), so the child theme can override a single file
 * by placing it at the same path under its own assets/ folder.
 *
 * @param string $path Path under assets/, e.g. 'css/site.css'.
 * @return string URL, unescaped (escape with esc_url() on output).
 */
function rx_asset( string $path ): string {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

/**
 * Sharp product image: 1x + 2x JPGs (assets/img/{name}.jpg and {name}@2x.jpg).
 * Port of img_tag(): same arguments and markup.
 *
 * @param string $name  Image name without extension, e.g. 'v2-02-dashboard'.
 * @param int    $w     1x pixel width.
 * @param int    $h     1x pixel height.
 * @param string $alt   Alt text.
 * @param string $sizes Rendered CSS width hint, so the browser picks one file.
 * @param string $extra Extra attributes, trusted markup from the template
 *                      (default 'loading="lazy"').
 * @return string <img> markup.
 */
function rx_img( string $name, int $w, int $h, string $alt, string $sizes, string $extra = 'loading="lazy"' ): string {
	$one = esc_url( rx_asset( "img/$name.jpg" ) );
	$two = esc_url( rx_asset( "img/$name@2x.jpg" ) );
	return '<img src="' . $one . '" srcset="' . $one . ' ' . $w . 'w, ' . $two . ' ' . ( $w * 2 ) . 'w" sizes="' . esc_attr( $sizes ) . '"'
		. ' width="' . $w . '" height="' . $h . '" alt="' . esc_attr( $alt ) . '" decoding="async" ' . $extra . '>';
}

/**
 * Product screen as a vector SVG (assets/img/screens/{name}.svg). Stays
 * sharp at any size, so no srcset. Port of svg_img(): same arguments and
 * markup.
 *
 * @param string $name  Screen name without extension, e.g. 'v2-01-counter-billing'.
 * @param int    $w     Width attribute.
 * @param int    $h     Height attribute.
 * @param string $alt   Alt text.
 * @param string $extra Extra attributes, trusted markup from the template
 *                      (default 'loading="lazy"').
 * @return string <img> markup.
 */
function rx_svg_img( string $name, int $w, int $h, string $alt, string $extra = 'loading="lazy"' ): string {
	return '<img src="' . esc_url( rx_asset( "img/screens/$name.svg" ) ) . '" width="' . $w . '" height="' . $h . '" alt="' . esc_attr( $alt ) . '" decoding="async" ' . $extra . '>';
}

/**
 * Logo markup: the mark always, the wordmark only when the Site Title is
 * set. Port of logo().
 *
 * @return string Markup (the sprite holds #logo-mark).
 */
function rx_logo(): string {
	$brand = rx_brand();
	return '<svg class="brand-logo" aria-hidden="true"><use href="#logo-mark"/></svg>' . ( '' !== $brand ? '<span>' . esc_html( $brand ) . '</span>' : '' );
}

/*
 * -------------------------------------------------------------------------
 * Printing SCF values
 * -------------------------------------------------------------------------
 */

/**
 * Plain-text SCF value, ready to print: shortcodes resolved, then escaped.
 *
 * Every plain-text field goes through here, so live values such as
 * [rx_brand] or [rx_plan] work in any text field. Unregistered
 * [placeholders] are left as they are.
 *
 * @param mixed $v Field value (non-scalar values give '').
 * @return string Escaped text.
 */
function rx_text( $v ): string {
	if ( ! is_scalar( $v ) || is_bool( $v ) ) {
		return '';
	}
	return esc_html( do_shortcode( (string) $v ) );
}

/**
 * HTML SCF value (fields ending in _html), ready to print: shortcodes
 * resolved, then filtered with rx_kses_allowed_html().
 *
 * @param mixed $v Field value (non-scalar values give '').
 * @return string Safe HTML.
 */
function rx_html( $v ): string {
	if ( ! is_scalar( $v ) || is_bool( $v ) ) {
		return '';
	}
	return wp_kses( do_shortcode( (string) $v ), rx_kses_allowed_html() );
}

/**
 * Tags allowed in HTML fields: wp_kses_post()'s list plus the inline icon
 * markup the copy uses (<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>)
 * and the extra <img> attributes the image helpers print.
 *
 * @return array<string, array<string, bool>>
 */
function rx_kses_allowed_html(): array {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['svg'] = array(
		'class'               => true,
		'id'                  => true,
		'role'                => true,
		'aria-hidden'         => true,
		'aria-label'          => true,
		'focusable'           => true,
		'viewbox'             => true,
		'width'               => true,
		'height'              => true,
		'preserveaspectratio' => true,
		'xmlns'               => true,
	);
	$allowed['use'] = array(
		'href'       => true,
		'xlink:href' => true,
	);
	$allowed['img'] = ( $allowed['img'] ?? array() ) + array(
		'decoding'      => true,
		'srcset'        => true,
		'sizes'         => true,
		'fetchpriority' => true,
	);

	return $allowed;
}
