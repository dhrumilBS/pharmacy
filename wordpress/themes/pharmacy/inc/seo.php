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
 * Yoast prints one JSON-LD graph per page: WebSite, Organization, WebPage
 * and BreadcrumbList, plus Article (and a Person for the post author) on
 * posts. The theme prints no JSON-LD of its own. It changes that graph in
 * one `wpseo_schema_graph` callback, so each type appears once
 * (Requirements 12.3, 12.4, 12.5):
 *
 * - Every page: Person nodes are removed and every `author` points at the
 *   Organization, so the article author is the brand, as on the PHP site.
 *   inLanguage is en-IN, like lang="en-IN".
 * - Posts: FAQPage. The Article also gets the PHP site's description
 *   (summary line), image (cover screen) and dateModified (the `updated`
 *   field, else the publish date), which Yoast doesn't fill from SCF.
 * - Features hub, feature pages and the FAQ page: FAQPage.
 * - Blog hub and topic views: Blog with one BlogPosting per post. On topic
 *   views the WebPage and BreadcrumbList also move to /blog/{topic}/.
 * - Pricing: SoftwareApplication with one Offer per non-custom plan.
 *   Omitted when rx_pricing_data() is null.
 *
 * Theme nodes are added only when the graph has no node of that type yet
 * (for example a Yoast FAQ block would already make a FAQPage).
 *
 * The page comes from Yoast's context (the indexable's post id), not from
 * the main query, so the graph is the same wherever Yoast builds it. The
 * topic is the exception: only the main query of /blog/{topic}/ has it.
 */

/** Language of the schema pieces: English (India), as lang="en-IN". */
const RX_SCHEMA_LANGUAGE = 'en-IN';

/** Currency of the plan prices in the Offer nodes. */
const RX_SCHEMA_CURRENCY = 'INR';

/**
 * Page templates whose FAQ goes into FAQPage schema, with the kind of page
 * they are (see rx_schema_target()).
 */
const RX_SCHEMA_TEMPLATES = array(
	'templates/feature.php'      => 'feature',
	'templates/features-hub.php' => 'features-hub',
	'templates/faq.php'          => 'faq',
	'templates/pricing.php'      => 'pricing',
);

/**
 * `wpseo_schema_piece_language`: inLanguage of every Yoast piece (WebSite,
 * WebPage, Article, …). Yoast's default is the WordPress locale (en-US).
 *
 * @return string
 */
function rx_schema_language(): string {
	return RX_SCHEMA_LANGUAGE;
}
add_filter( 'wpseo_schema_piece_language', 'rx_schema_language' );

/**
 * `wpseo_schema_graph`: the theme's changes to Yoast's graph.
 *
 * @param mixed $graph   List of graph nodes built by Yoast.
 * @param mixed $context Yoast's Meta_Tags_Context for the page.
 * @return mixed The graph, still a list of nodes.
 */
function rx_schema_graph( $graph, $context = null ) {
	if ( ! is_array( $graph ) ) {
		return $graph;
	}
	$graph = array_values( $graph );

	[ $post_id, $kind ] = rx_schema_target( $context );

	if ( 'blog' === $kind ) {
		$graph = rx_schema_topic_view( $graph, $post_id );
	}

	$graph  = rx_schema_brand_authors( $graph, $context );
	$org_id = rx_schema_org_id( $graph, $context );

	if ( 'post' === $kind ) {
		$graph = rx_schema_post_dates( $graph, $post_id );
		$graph = rx_schema_post_article( $graph, $post_id );
	}

	$page_id  = rx_schema_webpage_id( $graph, $context );
	$page_url = rx_schema_page_url( $context, $post_id );

	$nodes = array();
	switch ( $kind ) {
		case 'post':
		case 'feature':
		case 'features-hub':
		case 'faq':
			$nodes[] = rx_schema_faq_node( $post_id, $kind, $page_url, $page_id );
			break;
		case 'blog':
			$nodes[] = rx_schema_blog_node( $post_id, $org_id );
			break;
		case 'pricing':
			$nodes[] = rx_schema_software_node( $page_url, $org_id );
			break;
	}

	foreach ( $nodes as $node ) {
		if ( is_array( $node ) && ! rx_schema_graph_has_type( $graph, (string) $node['@type'] ) ) {
			$graph[] = $node;
		}
	}
	return $graph;
}
add_filter( 'wpseo_schema_graph', 'rx_schema_graph', 10, 2 );

/*
 * Page detection and graph lookups
 */

/**
 * The page a Yoast context describes.
 *
 * @param mixed $context Yoast's Meta_Tags_Context (or any object with the
 *                       same `indexable` and `id` properties).
 * @return array{0: int, 1: string} [ post id, kind ]. Kind is 'post' (a
 *         single post), 'blog' (the posts page: hub and topic views),
 *         'feature', 'features-hub', 'faq', 'pricing' (by page template)
 *         or '' (anything else, with post id 0 when it is not a post).
 */
function rx_schema_target( $context ): array {
	$indexable = rx_schema_context( $context, 'indexable' );
	$type      = is_object( $indexable ) ? (string) ( $indexable->object_type ?? '' ) : '';
	$post_id   = (int) rx_schema_context( $context, 'id' );
	if ( 'post' !== $type || $post_id <= 0 ) {
		return array( 0, '' );
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return array( 0, '' );
	}
	if ( 'post' === $post->post_type ) {
		return array( $post_id, 'post' );
	}
	if ( 'page' !== $post->post_type ) {
		return array( $post_id, '' );
	}
	if ( (int) get_option( 'page_for_posts' ) === $post_id ) {
		return array( $post_id, 'blog' );
	}

	$template = get_page_template_slug( $post_id );
	return array( $post_id, is_string( $template ) ? ( RX_SCHEMA_TEMPLATES[ $template ] ?? '' ) : '' );
}

/**
 * A property of Yoast's context. Yoast builds most of them on first read,
 * which can throw; that counts as "not available".
 *
 * @param mixed  $context Yoast's Meta_Tags_Context.
 * @param string $name    Property name, for example 'canonical'.
 * @return mixed The value, or null.
 */
function rx_schema_context( $context, string $name ) {
	if ( ! is_object( $context ) ) {
		return null;
	}
	try {
		return $context->{$name} ?? null;
	} catch ( Throwable $e ) {
		return null;
	}
}

/**
 * Types of a graph node, as a list ('@type' may be a string or a list).
 *
 * @param mixed $node Graph node.
 * @return string[]
 */
function rx_schema_types( $node ): array {
	if ( ! is_array( $node ) || ! isset( $node['@type'] ) ) {
		return array();
	}
	$types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );
	return array_values( array_filter( $types, 'is_string' ) );
}

/**
 * Whether a graph node has a type.
 *
 * @param mixed  $node Graph node.
 * @param string $type Schema type, for example 'WebPage'.
 */
function rx_schema_is( $node, string $type ): bool {
	return in_array( $type, rx_schema_types( $node ), true );
}

/**
 * Whether any node of the graph has a type.
 *
 * @param array  $graph Graph nodes.
 * @param string $type  Schema type.
 */
function rx_schema_graph_has_type( array $graph, string $type ): bool {
	foreach ( $graph as $node ) {
		if ( rx_schema_is( $node, $type ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @id of the page's WebPage node: Yoast's main schema id when the graph
 * has that node, else the first WebPage node's @id.
 *
 * @param array $graph   Graph nodes.
 * @param mixed $context Yoast's Meta_Tags_Context.
 * @return string '' when the graph has no WebPage node.
 */
function rx_schema_webpage_id( array $graph, $context ): string {
	$main  = rx_schema_context( $context, 'main_schema_id' );
	$first = '';
	foreach ( $graph as $node ) {
		if ( ! rx_schema_is( $node, 'WebPage' ) || ! isset( $node['@id'] ) || ! is_string( $node['@id'] ) ) {
			continue;
		}
		if ( $node['@id'] === $main ) {
			return $main;
		}
		if ( '' === $first ) {
			$first = $node['@id'];
		}
	}
	return $first;
}

/**
 * @id of the Organization node Yoast built for the brand: the node the
 * site represents when it is in the graph, else the first Organization.
 *
 * Yoast only outputs it with a company name and logo (the logo attachment
 * comes from the import), so it can be missing.
 *
 * @param array $graph   Graph nodes.
 * @param mixed $context Yoast's Meta_Tags_Context.
 * @return string '' when the graph has no Organization node.
 */
function rx_schema_org_id( array $graph, $context ): string {
	$ref  = rx_schema_context( $context, 'site_represents_reference' );
	$want = is_array( $ref ) && isset( $ref['@id'] ) && is_string( $ref['@id'] ) ? $ref['@id'] : '';

	$first = '';
	foreach ( $graph as $node ) {
		if ( ! rx_schema_is( $node, 'Organization' ) || ! isset( $node['@id'] ) || ! is_string( $node['@id'] ) ) {
			continue;
		}
		if ( '' !== $want && $node['@id'] === $want ) {
			return $want;
		}
		if ( '' === $first ) {
			$first = $node['@id'];
		}
	}
	return $first;
}

/**
 * URL of the page the graph describes: Yoast's canonical, else the
 * permalink of the post.
 *
 * @param mixed $context Yoast's Meta_Tags_Context.
 * @param int   $post_id Post id from rx_schema_target(), or 0.
 * @return string '' when neither is known.
 */
function rx_schema_page_url( $context, int $post_id ): string {
	$canonical = rx_schema_context( $context, 'canonical' );
	if ( is_string( $canonical ) && '' !== $canonical ) {
		return $canonical;
	}
	$url = $post_id > 0 ? get_permalink( $post_id ) : false;
	return is_string( $url ) ? $url : '';
}

/**
 * Plain text for schema: tags stripped, entities decoded, whitespace
 * collapsed (the way Yoast cleans its own names and headlines).
 *
 * @param mixed $value Text or HTML.
 */
function rx_schema_plain( $value ): string {
	if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) {
		return '';
	}
	$text = wp_strip_all_tags( (string) $value );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
}

/*
 * Author: the brand, never a Person
 */

/**
 * Removes Person nodes and points every `author` at the Organization.
 *
 * - Nodes typed Person are removed (Yoast adds one for the post's WordPress
 *   user; the site has no named authors).
 * - Every `author` of the remaining nodes (Article, and WebPage when Yoast
 *   adds one there) becomes { "@id": Organization }. Any other reference to
 *   a removed Person becomes the same reference.
 * - Without an Organization node, the reference is an inline Organization
 *   named after the brand, like the PHP site's Article author; with no
 *   brand either, `author` is left out.
 *
 * @param array $graph   Graph nodes.
 * @param mixed $context Yoast's Meta_Tags_Context.
 * @return array
 */
function rx_schema_brand_authors( array $graph, $context ): array {
	$removed = array();
	foreach ( $graph as $i => $node ) {
		if ( rx_schema_is( $node, 'Person' ) ) {
			if ( isset( $node['@id'] ) && is_string( $node['@id'] ) ) {
				$removed[ $node['@id'] ] = true;
			}
			unset( $graph[ $i ] );
		}
	}
	$graph = array_values( $graph );

	$org_id = rx_schema_org_id( $graph, $context );
	if ( '' !== $org_id ) {
		$author = array( '@id' => $org_id );
	} elseif ( '' !== rx_brand() ) {
		$author = array(
			'@type' => 'Organization',
			'name'  => rx_brand(),
			'url'   => home_url( '/' ),
		);
	} else {
		$author = null;
	}

	foreach ( $graph as $i => $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		if ( $removed && null !== $author ) {
			$node = rx_schema_replace_refs( $node, $removed, $author );
		}
		if ( array_key_exists( 'author', $node ) ) {
			if ( null === $author ) {
				unset( $node['author'] );
			} else {
				$node['author'] = $author;
			}
		}
		$graph[ $i ] = $node;
	}
	return $graph;
}

/**
 * Replaces references to removed nodes ({ "@id": x } with x in $ids)
 * anywhere inside a node.
 *
 * @param array               $node Graph node or part of one.
 * @param array<string, true> $ids  Removed @ids.
 * @param array               $with Replacement reference.
 * @return array
 */
function rx_schema_replace_refs( array $node, array $ids, array $with ): array {
	foreach ( $node as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}
		if ( isset( $value['@id'] ) && is_string( $value['@id'] ) && isset( $ids[ $value['@id'] ] ) && ! isset( $value['@type'] ) ) {
			$node[ $key ] = $with;
		} else {
			$node[ $key ] = rx_schema_replace_refs( $value, $ids, $with );
		}
	}
	return $node;
}

/*
 * Posts: Article details and dates
 */

/**
 * dateModified of a post as the PHP site gives it: the SCF `updated` date
 * (midnight, site time zone), else the publish date. The import rewrites
 * posts, so WordPress's own modified date says nothing about the content.
 *
 * @param int    $post_id   Post id.
 * @param string $published datePublished from Yoast's node (W3C format).
 * @return string W3C date-time, or $published.
 */
function rx_schema_post_modified( int $post_id, string $published ): string {
	$updated = rx_field( 'updated', $post_id );
	if ( is_string( $updated ) ) {
		$updated = trim( $updated );
		if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $updated, $m ) ) {
			$updated = $m[1] . '-' . $m[2] . '-' . $m[3];
		}
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $updated ) ) {
			$date = date_create_immutable_from_format( '!Y-m-d', $updated, wp_timezone() );
			if ( $date ) {
				return $date->format( DATE_W3C );
			}
		}
	}
	return $published;
}

/**
 * Sets dateModified on the nodes that carry the post's dates (Article and
 * WebPage): see rx_schema_post_modified().
 *
 * @param array $graph   Graph nodes.
 * @param int   $post_id Post id.
 * @return array
 */
function rx_schema_post_dates( array $graph, int $post_id ): array {
	foreach ( $graph as $i => $node ) {
		if ( ! is_array( $node ) || ! isset( $node['datePublished'] ) || ! is_string( $node['datePublished'] ) ) {
			continue;
		}
		$node['dateModified'] = rx_schema_post_modified( $post_id, $node['datePublished'] );
		$graph[ $i ]          = $node;
	}
	return $graph;
}

/**
 * Adds the PHP site's Article description (the post's summary line) and
 * image (its cover screen) when Yoast's Article has none.
 *
 * @param array $graph   Graph nodes.
 * @param int   $post_id Post id.
 * @return array
 */
function rx_schema_post_article( array $graph, int $post_id ): array {
	foreach ( $graph as $i => $node ) {
		if ( ! rx_schema_is_article( $node ) ) {
			continue;
		}

		if ( empty( $node['description'] ) ) {
			$dek = rx_schema_plain( do_shortcode( (string) rx_field( 'dek', $post_id ) ) );
			if ( '' !== $dek ) {
				$node['description'] = $dek;
			}
		}

		if ( empty( $node['image'] ) ) {
			$cover = rx_field( 'cover_screen', $post_id );
			$cover = is_string( $cover ) ? sanitize_file_name( $cover ) : '';
			if ( '' !== $cover && is_file( get_theme_file_path( 'assets/img/screens/' . $cover . '.svg' ) ) ) {
				$node['image'] = rx_asset( 'img/screens/' . $cover . '.svg' );
			}
		}

		$graph[ $i ] = $node;
	}
	return $graph;
}

/**
 * Whether a node is an Article (Article, BlogPosting, NewsArticle, …).
 *
 * @param mixed $node Graph node.
 */
function rx_schema_is_article( $node ): bool {
	foreach ( rx_schema_types( $node ) as $type ) {
		if ( str_contains( $type, 'Article' ) || 'BlogPosting' === $type ) {
			return true;
		}
	}
	return false;
}

/*
 * FAQPage
 */

/**
 * FAQ rows of a page or post, with shortcodes resolved, in page order.
 *
 * - Posts, feature pages and the features hub: the `faq` repeater.
 * - FAQ page: the `questions` of every row of `topics`, topic by topic.
 *
 * Shortcodes run with do_shortcode() only (no wp_kses_post(), which would
 * turn a bare "&" into "&amp;"); rx_faq_schema() strips the tags.
 *
 * @param int    $post_id Post id.
 * @param string $kind    Kind from rx_schema_target().
 * @return array[] Rows [ q, a_html, confirmed ].
 */
function rx_schema_faq_items( int $post_id, string $kind ): array {
	if ( 'faq' === $kind ) {
		$rows   = array();
		$topics = rx_field( 'topics', $post_id );
		foreach ( is_array( $topics ) ? $topics : array() as $topic ) {
			if ( is_array( $topic ) && isset( $topic['questions'] ) && is_array( $topic['questions'] ) ) {
				array_push( $rows, ...array_values( $topic['questions'] ) );
			}
		}
	} else {
		$rows = rx_field( 'faq', $post_id );
		$rows = is_array( $rows ) ? $rows : array();
	}

	$items = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$items[] = array(
			'q'         => is_string( $row['q'] ?? null ) ? do_shortcode( $row['q'] ) : '',
			'a_html'    => is_string( $row['a_html'] ?? null ) ? do_shortcode( $row['a_html'] ) : '',
			'confirmed' => $row['confirmed'] ?? null,
		);
	}
	return $items;
}

/**
 * FAQPage node of a page or post: confirmed answers only (rx_faq_schema()),
 * part of the page's WebPage.
 *
 * @param int    $post_id  Post id.
 * @param string $kind     Kind from rx_schema_target().
 * @param string $page_url URL of the page (Yoast's canonical).
 * @param string $page_id  @id of the WebPage node, or ''.
 * @return array|null Null when no confirmed answer is left.
 */
function rx_schema_faq_node( int $post_id, string $kind, string $page_url, string $page_id ): ?array {
	$faq = rx_faq_schema( rx_schema_faq_items( $post_id, $kind ) );
	if ( null === $faq ) {
		return null;
	}

	$node = array( '@type' => 'FAQPage' );
	if ( '' !== $page_url ) {
		$node['@id'] = $page_url . '#faq';
	}
	if ( '' !== $page_id ) {
		$node['isPartOf'] = array( '@id' => $page_id );
	}
	$node['inLanguage'] = RX_SCHEMA_LANGUAGE;
	$node['mainEntity'] = $faq['mainEntity'];
	return $node;
}

/*
 * Blog hub and topic views
 */

/**
 * Blog node of the posts page, as the PHP site's blog/index.php prints it:
 * the blog's name and URL, and one BlogPosting (headline, datePublished,
 * url) per published post, newest first. Topic views list every post too:
 * the node describes the whole blog at /blog/, as today.
 *
 * @param int    $page_id Posts page id.
 * @param string $org_id  @id of the Organization node, or ''.
 * @return array|null Null when the posts page has no URL.
 */
function rx_schema_blog_node( int $page_id, string $org_id ): ?array {
	$url = get_permalink( $page_id );
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	$posts = array();
	foreach ( rx_blog_posts() as $post ) {
		$link = get_permalink( $post );
		if ( ! is_string( $link ) || '' === $link ) {
			continue;
		}
		$posts[] = array(
			'@type'         => 'BlogPosting',
			'headline'      => rx_schema_plain( get_post_field( 'post_title', $post, 'raw' ) ),
			'datePublished' => (string) mysql2date( 'Y-m-d', $post->post_date, false ),
			'url'           => $link,
		);
	}

	$node = array(
		'@type' => 'Blog',
		'@id'   => $url . '#blog',
		'name'  => rx_schema_plain( get_post_field( 'post_title', $page_id, 'raw' ) ),
		'url'   => $url,
	);
	if ( '' !== $org_id ) {
		$node['publisher'] = array( '@id' => $org_id );
	}
	$node['inLanguage'] = RX_SCHEMA_LANGUAGE;
	$node['blogPost']   = $posts;
	return $node;
}

/**
 * On a topic view (/blog/{topic}/), moves Yoast's posts-page nodes from
 * /blog/ to the topic URL, so the graph matches the canonical and og:url
 * set in section 2:
 *
 * - Every @id equal to the hub URL, or starting with it plus "#", is moved
 *   (the WebPage, its #breadcrumb, a #primaryimage), references included.
 * - The WebPage gets url = topic URL and name = the topic title.
 * - The BreadcrumbList gets the topic as a last crumb after "Blog & guides",
 *   which now links to the hub, as the page hero shows it.
 *
 * The hub itself (no topic) is left as Yoast built it.
 *
 * @param array $graph   Graph nodes.
 * @param int   $page_id Posts page id.
 * @return array
 */
function rx_schema_topic_view( array $graph, int $page_id ): array {
	$topic = rx_blog_current_topic();
	if ( '' === $topic ) {
		return $graph;
	}
	$hub = get_permalink( $page_id );
	$url = rx_blog_topic_url( $topic );
	if ( ! is_string( $hub ) || '' === $hub || '' === $url || $hub === $url ) {
		return $graph;
	}

	$title = rx_seo_topic_title();
	foreach ( $graph as $i => $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$node = rx_schema_move_ids( $node, $hub, $url );

		if ( rx_schema_is( $node, 'WebPage' ) && ( $node['@id'] ?? '' ) === $url ) {
			$node['url'] = $url;
			if ( '' !== $title ) {
				$node['name'] = $title;
			}
		}
		if ( rx_schema_is( $node, 'BreadcrumbList' ) ) {
			$node = rx_schema_topic_crumbs( $node, $hub, RX_TOPICS[ $topic ][0] );
		}
		$graph[ $i ] = $node;
	}
	return $graph;
}

/**
 * Moves @ids from one page URL to another inside a node: the URL itself
 * and fragment ids under it ("{from}#breadcrumb" → "{to}#breadcrumb").
 *
 * @param array  $node Graph node or part of one.
 * @param string $from Old page URL.
 * @param string $to   New page URL.
 * @return array
 */
function rx_schema_move_ids( array $node, string $from, string $to ): array {
	foreach ( $node as $key => $value ) {
		if ( '@id' === $key && is_string( $value ) ) {
			if ( $value === $from ) {
				$node[ $key ] = $to;
			} elseif ( str_starts_with( $value, $from . '#' ) ) {
				$node[ $key ] = $to . substr( $value, strlen( $from ) );
			}
		} elseif ( is_array( $value ) ) {
			$node[ $key ] = rx_schema_move_ids( $value, $from, $to );
		}
	}
	return $node;
}

/**
 * Breadcrumb of a topic view: Yoast's posts-page crumbs, with the last one
 * (the Blog page, which has no URL as the current page) linked to the hub,
 * then the topic name as the new last crumb.
 *
 * @param array  $node  BreadcrumbList node.
 * @param string $hub   Hub URL.
 * @param string $topic Topic name, for example "Expiry & stock".
 * @return array
 */
function rx_schema_topic_crumbs( array $node, string $hub, string $topic ): array {
	$items = isset( $node['itemListElement'] ) && is_array( $node['itemListElement'] ) ? array_values( $node['itemListElement'] ) : array();
	$last  = count( $items ) - 1;
	if ( $last < 0 || ! is_array( $items[ $last ] ) || isset( $items[ $last ]['item'] ) ) {
		return $node;
	}

	$items[ $last ]['item'] = $hub;
	$items[]                = array(
		'@type'    => 'ListItem',
		'position' => count( $items ) + 1,
		'name'     => $topic,
	);
	$node['itemListElement'] = $items;
	return $node;
}

/*
 * Pricing: SoftwareApplication
 */

/**
 * SoftwareApplication node of the pricing page, built from the live plans:
 * name = brand, BusinessApplication, runs in a web browser, and one Offer
 * (plan name, yearly price, INR) per plan that is not quoted (custom).
 *
 * @param string $page_url URL of the pricing page (Yoast's canonical).
 * @param string $org_id   @id of the Organization node, or ''.
 * @return array|null Null when rx_pricing_data() is null, the brand is
 *                    empty or no plan has a price.
 */
function rx_schema_software_node( string $page_url, string $org_id ): ?array {
	$d     = rx_pricing_data();
	$brand = rx_brand();
	if ( null === $d || '' === $brand ) {
		return null;
	}

	$offers = array();
	foreach ( rx_pricing_plans( $d ) as $plan ) {
		if ( ! empty( $plan['is_custom'] ) || ! is_numeric( $plan['price'] ?? null ) ) {
			continue;
		}
		$offers[] = array(
			'@type'         => 'Offer',
			'name'          => rx_schema_plain( $plan['name'] ?? '' ),
			'price'         => 0 + $plan['price'],
			'priceCurrency' => RX_SCHEMA_CURRENCY,
		);
	}
	if ( ! $offers ) {
		return null;
	}

	$node = array( '@type' => 'SoftwareApplication' );
	if ( '' !== $page_url ) {
		$node['@id'] = $page_url . '#software';
	}
	$node['name']                = $brand;
	$node['applicationCategory'] = 'BusinessApplication';
	$node['operatingSystem']     = 'Web browser';
	if ( '' !== $org_id ) {
		$node['publisher'] = array( '@id' => $org_id );
	}
	$node['offers'] = $offers;
	return $node;
}
