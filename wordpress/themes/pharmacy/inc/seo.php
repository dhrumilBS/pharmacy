<?php
/**
 * SEO: Yoast filters and theme schema pieces.
 *
 * Yoast SEO owns the head: <title>, meta description, robots, canonical,
 * Open Graph and Twitter tags, the sitemap and the schema graph. The theme
 * prints none of these itself; it only adjusts Yoast's values where the
 * PHP site's head differs from what Yoast would print on its own
 * (Requirements 12.1, 12.5). Every filter here replaces a value, so each
 * tag is still printed once, by Yoast.
 *
 * Sections:
 * 1. Head: shared       debug markers off, en_IN locale.
 * 2. Head: topic views  /blog/{topic}/ title, canonical and og:url.
 * 3. Head: pricing      meta description built from the live plans.
 * 4. Schema graph       theme pieces added to Yoast's graph (task 14.2).
 *
 * All callbacks are plain add_filter() calls: they never run when Yoast is
 * inactive, and nothing here calls Yoast directly except through
 * function_exists() checks.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * 1. Head: shared
 * -------------------------------------------------------------------------
 */

/** Open Graph locale of the site: English (India), like lang="en-IN". */
const RX_SEO_LOCALE = 'en_IN';

/*
 * No "This site is optimized with the Yoast SEO plugin" / "/ Yoast SEO
 * plugin." comments around the head tags.
 */
add_filter( 'wpseo_debug_markers', '__return_false' );

/**
 * Locale for Yoast's Open Graph output: en_IN.
 *
 * Hooked twice:
 * - `wpseo_locale` replaces get_locale() as Yoast's starting point.
 * - `wpseo_og_locale` sets the printed og:locale. Yoast maps any locale
 *   that is not on Facebook's list to en_US after `wpseo_locale`, and
 *   en_IN is not on that list, so only this later filter keeps en_IN.
 *
 * @return string
 */
function rx_seo_locale(): string {
	return RX_SEO_LOCALE;
}
add_filter( 'wpseo_locale', 'rx_seo_locale' );
add_filter( 'wpseo_og_locale', 'rx_seo_locale' );

/*
 * -------------------------------------------------------------------------
 * 2. Head: blog topic views (/blog/{topic}/)
 * -------------------------------------------------------------------------
 *
 * A topic view is the posts page with rx_topic set (see inc/blog.php), so
 * Yoast treats it as the Blog page: hub title, hub description and the
 * /blog/ canonical. The PHP site's blog/index.php?topic=x changes only the
 * title ("{Topic} guides for medical stores | {brand}"); the description
 * stays the hub's, so Yoast's stored Blog page description is kept.
 */

/**
 * Title of the current topic view, resolved the way Yoast resolves titles:
 * "{Topic} guides for medical stores %%sep%% %%sitename%%", giving for
 * example "Expiry & stock guides for medical stores | Rxtiva" with the
 * separator and site name from the Yoast settings.
 *
 * @return string Plain text (unescaped). '' outside topic views, or when
 *                Yoast's replace-vars function is unavailable.
 */
function rx_seo_topic_title(): string {
	$topic = rx_blog_current_topic();
	if ( '' === $topic || ! function_exists( 'wpseo_replace_vars' ) ) {
		return '';
	}

	$text = sprintf(
		/* translators: %s: blog topic name, for example "Expiry & stock". */
		__( '%s guides for medical stores', 'pharmacy' ),
		RX_TOPICS[ $topic ][0]
	);

	// %%sep%% and %%sitename%% don't depend on the post, so no source object is needed.
	$title = wpseo_replace_vars( $text . ' %%sep%% %%sitename%%', array() );
	return is_string( $title ) ? trim( $title ) : '';
}

/**
 * `wpseo_title` and `wpseo_opengraph_title`: the topic title on topic
 * views (so <title> and og:title match, as on the PHP site); the value
 * unchanged elsewhere.
 *
 * @param mixed $title Title resolved by Yoast.
 * @return mixed
 */
function rx_seo_title( $title ) {
	$topic_title = rx_seo_topic_title();
	return '' !== $topic_title ? $topic_title : $title;
}
add_filter( 'wpseo_title', 'rx_seo_title' );
add_filter( 'wpseo_opengraph_title', 'rx_seo_title' );

/**
 * `wpseo_twitter_title`: the topic title on topic views, but only when
 * Yoast prints a twitter:title at all. Yoast leaves it empty when
 * og:title already covers it, and that stays empty.
 *
 * @param mixed $title Twitter title resolved by Yoast ('' = not printed).
 * @return mixed
 */
function rx_seo_twitter_title( $title ) {
	if ( ! is_string( $title ) || '' === trim( $title ) ) {
		return $title;
	}
	return rx_seo_title( $title );
}
add_filter( 'wpseo_twitter_title', 'rx_seo_twitter_title' );

/**
 * `wpseo_canonical` and `wpseo_opengraph_url`: /blog/{topic}/ on topic
 * views instead of the hub's /blog/. An empty value (Yoast prints no tag)
 * stays empty.
 *
 * @param mixed $url URL from Yoast.
 * @return mixed
 */
function rx_seo_topic_url( $url ) {
	if ( ! is_string( $url ) || '' === trim( $url ) ) {
		return $url;
	}
	$topic = rx_blog_current_topic();
	if ( '' === $topic ) {
		return $url;
	}
	$topic_url = rx_blog_topic_url( $topic );
	return '' !== $topic_url ? $topic_url : $url;
}
add_filter( 'wpseo_canonical', 'rx_seo_topic_url' );
add_filter( 'wpseo_opengraph_url', 'rx_seo_topic_url' );

/*
 * -------------------------------------------------------------------------
 * 3. Head: pricing meta description
 * -------------------------------------------------------------------------
 *
 * The PHP site's pricing.php builds its description from the live plans:
 *   'Simple yearly plans for medical stores: ' . implode( ', ', $bits )
 *   . '. Prices exclude 18% GST. Compare every feature.'
 * The stored Yoast description of the Pricing page holds the same sentence
 * without the plan list and no prices; it is used when pricing is null.
 */

/**
 * Live meta description of the Pricing page.
 *
 * @return string Plain text. '' when the current page is not the Pricing
 *                page, or rx_pricing_data() is null, or it lists no plans.
 */
function rx_seo_pricing_description(): string {
	if ( 'pricing' !== rx_page_key() ) {
		return '';
	}
	$d = rx_pricing_data();
	if ( null === $d ) {
		return '';
	}
	$bits = rx_pricing_meta_bits( $d );
	if ( ! $bits ) {
		return '';
	}

	return sprintf(
		/* translators: %s: comma-separated plans with yearly prices, for example "Starter free forever, Basic ₹4,999/year, Enterprise for chains". */
		__( 'Simple yearly plans for medical stores: %s. Prices exclude 18%% GST. Compare every feature.', 'pharmacy' ),
		implode( ', ', $bits )
	);
}

/**
 * `wpseo_metadesc`: the live description on the Pricing page; the stored
 * Yoast description everywhere else, and on Pricing when pricing is null.
 *
 * @param mixed $description Description resolved by Yoast.
 * @return mixed
 */
function rx_seo_metadesc( $description ) {
	$live = rx_seo_pricing_description();
	return '' !== $live ? $live : $description;
}
add_filter( 'wpseo_metadesc', 'rx_seo_metadesc' );

/**
 * Social description on the Pricing page: follows the live meta
 * description when Yoast derived it from the stored one.
 *
 * Left alone when Yoast prints none ('' in, '' out) or when an Editor set
 * this social description in Yoast's Social tab.
 *
 * @param mixed  $description  Description resolved by Yoast.
 * @param mixed  $presentation Yoast's Indexable_Presentation, or null.
 * @param string $field        Indexable column of the editor-set value:
 *                             'open_graph_description' or 'twitter_description'.
 * @return mixed
 */
function rx_seo_social_description( $description, $presentation, string $field ) {
	if ( ! is_string( $description ) || '' === trim( $description ) ) {
		return $description;
	}
	$model = is_object( $presentation ) && isset( $presentation->model ) && is_object( $presentation->model ) ? $presentation->model : null;
	if ( $model && ! empty( $model->{$field} ) ) {
		return $description;
	}
	$live = rx_seo_pricing_description();
	return '' !== $live ? $live : $description;
}

/**
 * `wpseo_opengraph_desc`: see rx_seo_social_description().
 *
 * @param mixed $description  og:description resolved by Yoast.
 * @param mixed $presentation Yoast's Indexable_Presentation.
 * @return mixed
 */
function rx_seo_opengraph_desc( $description, $presentation = null ) {
	return rx_seo_social_description( $description, $presentation, 'open_graph_description' );
}
add_filter( 'wpseo_opengraph_desc', 'rx_seo_opengraph_desc', 10, 2 );

/**
 * `wpseo_twitter_description`: see rx_seo_social_description().
 *
 * @param mixed $description  twitter:description resolved by Yoast.
 * @param mixed $presentation Yoast's Indexable_Presentation.
 * @return mixed
 */
function rx_seo_twitter_desc( $description, $presentation = null ) {
	return rx_seo_social_description( $description, $presentation, 'twitter_description' );
}
add_filter( 'wpseo_twitter_description', 'rx_seo_twitter_desc', 10, 2 );

/*
 * -------------------------------------------------------------------------
 * 4. Schema graph (wpseo_schema_graph)
 * -------------------------------------------------------------------------
 *
 * Theme pieces for Yoast's graph: Article author → Organization, FAQPage,
 * Blog + BlogPosting, SoftwareApplication. Added in task 14.2.
 */
