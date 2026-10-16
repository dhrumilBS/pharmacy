<?php
/**
 * Shortcodes for live values inside editable text (design §6).
 *
 * Copy in SCF fields stays live through these: the brand, Site_Settings
 * values, phone links, internal links and everything that comes from the
 * Pricing_API. Templates print fields with rx_text() (plain text) or
 * rx_html() (fields ending in _html), which run do_shortcode() first.
 *
 *   [rx_brand]                                    Site Title
 *   [rx_site key=""]                              Site_Settings text value
 *   [rx_tel key="sales|support" class="" style=""]          tel: link
 *   [rx_url key="" hash=""]                       URL of a page key (plain text)
 *   [rx_link key="" setting="" hash="" class="" style="" rel="" cta=""]text[/rx_link]
 *   [rx_plan feat="" fallback="" before="" after=""]         plan label of a feature
 *   [rx_free_plan field="" fallback="" before="" after=""]   fact about the free plan
 *   [rx_cost_text] [rx_compare_text] [rx_terms_text] [rx_users_text] [rx_credits_text]
 *   [rx_if cond=""]…[/rx_if]                      content only when cond holds
 *
 * Output rules:
 *
 * - Text shortcodes return escaped text. rx_text() escapes again with
 *   esc_html(), which doesn't double-encode, so the result is the same.
 * - [rx_tel] and [rx_link] return markup and are meant for _html fields
 *   (rx_text() would print their tags as text).
 * - Unknown keys, fields and conditions give '' (or the text unlinked for
 *   [rx_link]), quietly: no notice, also with WP_DEBUG on.
 * - Every pricing shortcode reads rx_pricing_data() once. When pricing is
 *   null (API, cache, stale copy and snapshot all unusable) it gives '' or
 *   its `fallback`, and every [rx_if] block stays hidden (Requirement 8.3).
 *   No price is ever written here (Requirement 8.4).
 * - Enclosed content ([rx_link], [rx_tel], [rx_if]) is run through
 *   do_shortcode(), so shortcodes nest. WordPress can't nest a shortcode
 *   inside itself, so an [rx_if] inside another [rx_if] doesn't work.
 * - Unregistered bracketed text such as [Company legal name] is left as
 *   it is by do_shortcode(), so legal placeholders survive.
 *
 * Settings that hold URLs (app links, help centre, calendar, pricing API)
 * are link targets only: [rx_link setting=""] can use them, [rx_site]
 * never prints them as text.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'rx_register_shortcodes' );

/**
 * Register every rx_* shortcode.
 */
function rx_register_shortcodes(): void {
	$tags = array(
		'rx_brand'        => 'rx_sc_brand',
		'rx_site'         => 'rx_sc_site',
		'rx_tel'          => 'rx_sc_tel',
		'rx_url'          => 'rx_sc_url',
		'rx_link'         => 'rx_sc_link',
		'rx_plan'         => 'rx_sc_plan',
		'rx_free_plan'    => 'rx_sc_free_plan',
		'rx_cost_text'    => 'rx_sc_cost_text',
		'rx_compare_text' => 'rx_sc_compare_text',
		'rx_terms_text'   => 'rx_sc_terms_text',
		'rx_users_text'   => 'rx_sc_users_text',
		'rx_credits_text' => 'rx_sc_credits_text',
		'rx_if'           => 'rx_sc_if',
	);
	foreach ( $tags as $tag => $callback ) {
		add_shortcode( $tag, $callback );
	}
}

/*
 * -------------------------------------------------------------------------
 * Shared helpers
 * -------------------------------------------------------------------------
 */

/**
 * Site_Settings that hold URLs. Usable as [rx_link setting=""] targets;
 * never printed as text by [rx_site].
 *
 * @return string[]
 */
function rx_sc_link_settings(): array {
	return array( 'app_login', 'app_signup', 'help_url', 'calendly' );
}

/**
 * One attribute as ` name="value"`, or '' when the value is empty.
 *
 * @param string $name  Attribute name.
 * @param string $value Raw value (escaped here).
 */
function rx_sc_attr( string $name, string $value ): string {
	$value = trim( $value );
	return '' === $value ? '' : ' ' . $name . '="' . esc_attr( $value ) . '"';
}

/**
 * Link target for a page key or a URL setting, with an optional fragment.
 *
 * - `setting` set: that Site_Settings URL, if it is one of
 *   rx_sc_link_settings() and not empty.
 * - Otherwise `key`: rx_url( $key ) (fallback anchors and home for
 *   unknown keys, like url() on the PHP site).
 * - `hash` ("compare" or "#compare") replaces any fragment of the URL.
 *
 * @param string $key     Page key, e.g. 'pricing'.
 * @param string $setting URL setting name, e.g. 'app_signup'.
 * @param string $hash    Fragment, with or without '#'.
 * @return string URL, unescaped, or '' when there is no target.
 */
function rx_sc_href( string $key, string $setting, string $hash ): string {
	$key     = sanitize_key( $key );
	$setting = sanitize_key( $setting );

	if ( '' !== $setting ) {
		if ( ! in_array( $setting, rx_sc_link_settings(), true ) ) {
			return '';
		}
		$url = trim( (string) rx_setting( $setting ) );
	} elseif ( '' !== $key ) {
		$url = rx_url( $key );
	} else {
		return '';
	}
	if ( '' === $url ) {
		return '';
	}

	$hash = (string) preg_replace( '/[^A-Za-z0-9_.:-]/', '', ltrim( trim( $hash ), '#' ) );
	if ( '' !== $hash ) {
		$url = (string) preg_replace( '/#.*$/s', '', $url ) . '#' . $hash;
	}
	return $url;
}

/**
 * A live value with its optional before/after text, or the fallback.
 *
 * before and after print only when the value is not empty; the fallback
 * prints alone.
 *
 * @param string $value Live value ('' when unknown).
 * @param array  $atts  Shortcode attributes with before, after, fallback.
 * @return string Escaped text.
 */
function rx_sc_wrap( string $value, array $atts ): string {
	if ( '' === $value ) {
		return esc_html( (string) $atts['fallback'] );
	}
	return esc_html( (string) $atts['before'] . $value . (string) $atts['after'] );
}

/**
 * Facts about the free plan, as text: the first free plan in sort_order
 * (not custom, price 0), as pricing_split()['free'][0] on the PHP site.
 *
 * - name:       plan name.
 * - sales:      monthly sales bill limit, '' when unlimited.
 * - purchase:   monthly purchase bill limit, '' when unlimited.
 * - users:      user limit, '' when not set.
 * - users_text: "one user" or "N users" (an unset limit counts as one),
 *               as in pricing.php and faq.php.
 *
 * @param array|null $d Pricing document, or null.
 * @return array<string, string>|null Facts, or null when pricing is null
 *                                    or there is no free plan.
 */
function rx_sc_free_plan_facts( ?array $d ): ?array {
	if ( null === $d ) {
		return null;
	}
	$free = rx_pricing_split( $d )['free'][0] ?? null;
	if ( ! $free ) {
		return null;
	}

	$limits = rx_plan_bill_limits( $d, (int) $free['id'] );
	$users  = $free['user_limit'] ?? null;
	$count  = (int) ( $users ?? 1 );

	return array(
		'name'       => (string) $free['name'],
		'sales'      => null === $limits['sales'] ? '' : (string) $limits['sales'],
		'purchase'   => null === $limits['purchase'] ? '' : (string) $limits['purchase'],
		'users'      => null === $users ? '' : (string) (int) $users,
		'users_text' => 1 === $count ? 'one user' : $count . ' users',
	);
}

/**
 * Whether an [rx_if] condition holds. Always false when pricing is null.
 *
 * - pricing:             pricing data is available.
 * - free:                there is a free plan.
 * - no_free:             pricing is known and there is no free plan.
 * - free_sales_limit:    the free plan has a monthly sales bill limit.
 * - free_purchase_limit: the free plan has a monthly purchase bill limit.
 * - anything else:       false.
 *
 * @param string     $cond Condition name.
 * @param array|null $d    Pricing document, or null.
 */
function rx_sc_condition( string $cond, ?array $d ): bool {
	if ( null === $d ) {
		return false;
	}
	$free = rx_sc_free_plan_facts( $d );

	switch ( $cond ) {
		case 'pricing':
			return true;
		case 'free':
			return null !== $free;
		case 'no_free':
			return null === $free;
		case 'free_sales_limit':
			return null !== $free && '' !== $free['sales'];
		case 'free_purchase_limit':
			return null !== $free && '' !== $free['purchase'];
		default:
			return false;
	}
}

/**
 * Prose from a core pricing helper, escaped, or '' when pricing is null.
 *
 * @param callable $helper Core helper taking the pricing document.
 */
function rx_sc_pricing_text( callable $helper ): string {
	$d = rx_pricing_data();
	return null === $d ? '' : esc_html( (string) $helper( $d ) );
}

/*
 * -------------------------------------------------------------------------
 * Brand, settings and links
 * -------------------------------------------------------------------------
 */

/**
 * [rx_brand]: the Site Title.
 *
 * @return string Escaped text.
 */
function rx_sc_brand(): string {
	return esc_html( rx_brand() );
}

/**
 * [rx_site key="address"]: a Site_Settings text value (company, address,
 * sales_phone, support_phone, email, map_query, …).
 *
 * '' for an empty key, a value that is not text (social, hero_pattern) and
 * the URL settings.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string Escaped text.
 */
function rx_sc_site( $atts ): string {
	$atts = shortcode_atts( array( 'key' => '' ), $atts, 'rx_site' );
	$key  = sanitize_key( (string) $atts['key'] );

	if ( '' === $key || 'pricing_api' === $key || in_array( $key, rx_sc_link_settings(), true ) ) {
		return '';
	}

	$value = rx_setting( $key );
	if ( ! is_scalar( $value ) || is_bool( $value ) ) {
		return '';
	}
	return esc_html( (string) $value );
}

/**
 * [rx_tel key="sales|support" class="" style=""]: a tel: link,
 * `<a class="big-link" href="tel:+91…">+91 …</a>`.
 *
 * The link text is the phone number, or the enclosed content
 * ([rx_tel key="sales"]Call us[/rx_tel]). Without a tel setting the text
 * prints unlinked. '' for an unknown key or an empty number.
 *
 * @param array|string $atts    Shortcode attributes.
 * @param string|null  $content Optional link text.
 * @return string Markup.
 */
function rx_sc_tel( $atts, $content = null ): string {
	$atts = shortcode_atts(
		array(
			'key'   => 'sales',
			'class' => '',
			'style' => '',
		),
		$atts,
		'rx_tel'
	);
	$key = sanitize_key( (string) $atts['key'] );
	if ( ! in_array( $key, array( 'sales', 'support' ), true ) ) {
		return '';
	}

	$phone = trim( (string) rx_setting( $key . '_phone' ) );
	$tel   = trim( (string) rx_setting( $key . '_tel' ) );
	$label = null !== $content && '' !== trim( $content ) ? do_shortcode( $content ) : esc_html( $phone );
	if ( '' === $label ) {
		return '';
	}
	if ( '' === $tel ) {
		return $label;
	}

	return '<a' . rx_sc_attr( 'class', (string) $atts['class'] )
		. ' href="' . esc_url( 'tel:' . $tel ) . '"'
		. rx_sc_attr( 'style', (string) $atts['style'] ) . '>' . $label . '</a>';
}

/**
 * [rx_url key="pricing" hash=""]: URL of a page key, for plain-text use.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string Escaped URL, or '' without a key.
 */
function rx_sc_url( $atts ): string {
	$atts = shortcode_atts(
		array(
			'key'  => '',
			'hash' => '',
		),
		$atts,
		'rx_url'
	);
	return esc_url( rx_sc_href( (string) $atts['key'], '', (string) $atts['hash'] ) );
}

/**
 * [rx_link key="pricing" hash="compare" class=""]text[/rx_link]: a link to
 * a page key, or to a URL setting with setting="app_signup" (setting wins
 * when both are given).
 *
 * Optional style, rel and cta (printed as data-cta) keep markup such as
 * `<a href="…" style="color:#fff" rel="noopener">` and
 * `<a class="btn btn-primary" href="…" data-cta="demo">`. Attributes print
 * in the PHP site's order: class, href, style, rel, data-cta.
 *
 * Empty content gives ''. Without a usable target (unknown setting, empty
 * URL) the content prints unlinked, so the sentence stays whole.
 *
 * @param array|string $atts    Shortcode attributes.
 * @param string|null  $content Link text (HTML, shortcodes allowed).
 * @return string Markup.
 */
function rx_sc_link( $atts, $content = null ): string {
	$atts = shortcode_atts(
		array(
			'key'     => '',
			'setting' => '',
			'hash'    => '',
			'class'   => '',
			'style'   => '',
			'rel'     => '',
			'cta'     => '',
		),
		$atts,
		'rx_link'
	);

	$text = do_shortcode( (string) $content );
	if ( '' === trim( $text ) ) {
		return '';
	}

	$href = esc_url( rx_sc_href( (string) $atts['key'], (string) $atts['setting'], (string) $atts['hash'] ) );
	if ( '' === $href ) {
		return $text;
	}

	return '<a' . rx_sc_attr( 'class', (string) $atts['class'] )
		. ' href="' . $href . '"'
		. rx_sc_attr( 'style', (string) $atts['style'] )
		. rx_sc_attr( 'rel', (string) $atts['rel'] )
		. rx_sc_attr( 'data-cta', (string) $atts['cta'] ) . '>' . $text . '</a>';
}

/*
 * -------------------------------------------------------------------------
 * Pricing
 * -------------------------------------------------------------------------
 */

/**
 * [rx_plan feat="feat_2fa" fallback="higher plans" before=" (" after=")"]:
 * the plan a feature starts on ("Every plan", "Basic and up",
 * "Enterprise"), from rx_feature_plan_label(). Port of feature_plan_label().
 *
 * The fallback prints when the feature isn't listed, feat is empty or
 * pricing is null.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string Escaped text.
 */
function rx_sc_plan( $atts ): string {
	$atts = shortcode_atts(
		array(
			'feat'     => '',
			'fallback' => '',
			'before'   => '',
			'after'    => '',
		),
		$atts,
		'rx_plan'
	);

	$d     = rx_pricing_data();
	$feat  = trim( (string) $atts['feat'] );
	$label = null !== $d && '' !== $feat ? rx_feature_plan_label( $d, $feat ) : null;

	return rx_sc_wrap( (string) $label, $atts );
}

/**
 * [rx_free_plan field="name|sales|purchase|users|users_text" fallback=""
 * before="" after=""]: a fact about the free plan (see
 * rx_sc_free_plan_facts()). field defaults to name.
 *
 * The fallback (default '') prints when there is no free plan, the fact is
 * empty (an unlimited bill limit), the field is unknown or pricing is null.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string Escaped text.
 */
function rx_sc_free_plan( $atts ): string {
	$atts = shortcode_atts(
		array(
			'field'    => 'name',
			'fallback' => '',
			'before'   => '',
			'after'    => '',
		),
		$atts,
		'rx_free_plan'
	);

	$facts = rx_sc_free_plan_facts( rx_pricing_data() );
	$field = sanitize_key( (string) $atts['field'] );

	return rx_sc_wrap( (string) ( $facts[ $field ] ?? '' ), $atts );
}

/**
 * [rx_cost_text]: "Starter is free. Basic is ₹4,999 a year and Advance is
 * ₹9,999 a year, plus 18% GST. Enterprise for chains is quoted."
 *
 * @return string Escaped text.
 */
function rx_sc_cost_text(): string {
	return rx_sc_pricing_text( 'rx_pricing_cost_text' );
}

/**
 * [rx_compare_text]: "Free Starter; ₹4,999 or ₹9,999 a year, published".
 *
 * @return string Escaped text.
 */
function rx_sc_compare_text(): string {
	return rx_sc_pricing_text( 'rx_pricing_compare_text' );
}

/**
 * [rx_terms_text]: "1, 3 or 5 years".
 *
 * @return string Escaped text.
 */
function rx_sc_terms_text(): string {
	return rx_sc_pricing_text( 'rx_pricing_terms_text' );
}

/**
 * [rx_users_text]: "Starter 1, Basic 3, Advance 10 and Enterprise unlimited".
 *
 * @return string Escaped text.
 */
function rx_sc_users_text(): string {
	return rx_sc_pricing_text( 'rx_plan_users_text' );
}

/**
 * [rx_credits_text]: "Starter 10 WhatsApp, 10 SMS and 10 email; Basic …".
 *
 * @return string Escaped text.
 */
function rx_sc_credits_text(): string {
	return rx_sc_pricing_text( 'rx_plan_credits_text' );
}

/**
 * [rx_if cond="pricing|free|no_free|free_sales_limit|free_purchase_limit"]…[/rx_if]:
 * the enclosed content (shortcodes resolved) only when the condition holds
 * (see rx_sc_condition()); '' otherwise, and always when pricing is null.
 *
 * @param array|string $atts    Shortcode attributes.
 * @param string|null  $content Enclosed content.
 * @return string Content or ''.
 */
function rx_sc_if( $atts, $content = null ): string {
	$atts = shortcode_atts( array( 'cond' => '' ), $atts, 'rx_if' );
	if ( null === $content || '' === $content ) {
		return '';
	}
	if ( ! rx_sc_condition( sanitize_key( (string) $atts['cond'] ), rx_pricing_data() ) ) {
		return '';
	}
	return do_shortcode( $content );
}
