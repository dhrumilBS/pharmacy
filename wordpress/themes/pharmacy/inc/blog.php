<?php
/**
 * Blog: topics, rewrite rule, slug guard, archive 404s, post queries.
 *
 * WordPress side of the PHP site's partials/data/blog.php:
 *
 *   BLOG_CATS                 RX_TOPICS                 key => [ name, tone ]
 *   blog/index.php?topic=x    /blog/{topic}/            posts page + rx_topic query var
 *   blog_posts()              rx_blog_posts()           published posts, newest first
 *   $count in blog/index.php  rx_blog_topic_counts()
 *   blog_date()               rx_blog_date()            "14 Oct 2026"
 *   blog_author()             rx_blog_author()          "The {brand} team"
 *
 * Routing:
 * - Posts: /blog/{post-slug}/ (permalink structure /blog/%postname%/).
 * - Hub: the posts page, a page with the slug "blog" (page_for_posts).
 * - Topic views: /blog/{topic}/, a rewrite rule at "top" that sends the
 *   five topic keys to the posts page with rx_topic set, so is_home() is
 *   true and home.php renders the view. Any other /blog/{x}/ is a post
 *   lookup and 404s when no post has that slug.
 * - No view is paginated and no archive is migrated: category, tag,
 *   taxonomy, date, author, search, feed, attachment and paged views
 *   return the 404 page.
 *
 * The rule is registered on every init; it reaches the stored rewrite
 * rules only when they are flushed (the setup and import scripts do that).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RX_TOPICS' ) ) {
	/**
	 * Blog topics: key => [ name, tone ]. Same keys, names, tones and order
	 * as BLOG_CATS on the PHP site, and the same keys as the `topic` select
	 * in acf-json/group_rx_post.json. The tone picks the pill and cover
	 * colour (b-pill-{tone}, bc-{tone} in blog.css).
	 *
	 * Names are plain text: escape them on output.
	 */
	define(
		'RX_TOPICS',
		array(
			'expiry'    => array( 'Expiry & stock', 'teal' ),
			'purchase'  => array( 'Purchase', 'blue' ),
			'switching' => array( 'Switching', 'navy' ),
			'staff'     => array( 'Counter & staff', 'amber' ),
			'buying'    => array( 'Buying guides', 'slate' ),
		)
	);
}

/** Path of the posts page and the first segment of every blog URL. */
const RX_BLOG_BASE = 'blog';

/*
 * -------------------------------------------------------------------------
 * Topic views: query var and rewrite rule
 * -------------------------------------------------------------------------
 */

/**
 * Registers the rx_topic query var, so the rewrite rule can pass it.
 *
 * @param array $vars Public query vars.
 * @return array
 */
function rx_blog_query_vars( $vars ): array {
	$vars   = (array) $vars;
	$vars[] = 'rx_topic';
	return $vars;
}
add_filter( 'query_vars', 'rx_blog_query_vars' );

/**
 * Adds the /blog/{topic}/ rule at "top", before the post rules.
 *
 * Only the RX_TOPICS keys match, for example:
 *   ^blog/(expiry|purchase|switching|staff|buying)/?$
 *     → index.php?pagename=blog&rx_topic=$matches[1]
 * so /blog/{post-slug}/ still reaches the post rule.
 */
function rx_blog_rewrite_rules(): void {
	$keys = array_map(
		static fn( $key ): string => preg_quote( (string) $key, '#' ),
		array_keys( RX_TOPICS )
	);
	if ( ! $keys ) {
		return;
	}

	add_rewrite_rule(
		'^' . RX_BLOG_BASE . '/(' . implode( '|', $keys ) . ')/?$',
		'index.php?pagename=' . RX_BLOG_BASE . '&rx_topic=$matches[1]',
		'top'
	);
}
add_action( 'init', 'rx_blog_rewrite_rules' );

/**
 * Drops an rx_topic value that is not a topic key (for example
 * /blog/?rx_topic=x), so home.php only ever sees a known topic. The hub
 * then renders as usual, as ?topic=x does on the PHP site.
 *
 * @param array $vars Query vars of the request.
 * @return array
 */
function rx_blog_request( $vars ): array {
	$vars = (array) $vars;
	if ( isset( $vars['rx_topic'] ) && ! ( is_string( $vars['rx_topic'] ) && isset( RX_TOPICS[ $vars['rx_topic'] ] ) ) ) {
		unset( $vars['rx_topic'] );
	}
	return $vars;
}
add_filter( 'request', 'rx_blog_request' );

/**
 * Topic of the current view: the topic key on /blog/{topic}/, '' on the
 * hub and everywhere else (only the posts page has topic views).
 */
function rx_blog_current_topic(): string {
	if ( ! is_home() ) {
		return '';
	}
	$topic = get_query_var( 'rx_topic' );
	return is_string( $topic ) && isset( RX_TOPICS[ $topic ] ) ? $topic : '';
}

/**
 * URL of a topic view: /blog/{topic}/, or the query form of the same view
 * when pretty permalinks are off.
 *
 * @param string $topic Topic key.
 * @return string Absolute URL, unescaped. '' for an unknown topic.
 */
function rx_blog_topic_url( string $topic ): string {
	if ( ! isset( RX_TOPICS[ $topic ] ) ) {
		return '';
	}
	global $wp_rewrite;
	if ( $wp_rewrite instanceof WP_Rewrite && $wp_rewrite->using_permalinks() ) {
		return home_url( user_trailingslashit( RX_BLOG_BASE . '/' . $topic ) );
	}
	return add_query_arg(
		array(
			'pagename' => RX_BLOG_BASE,
			'rx_topic' => $topic,
		),
		home_url( '/' )
	);
}

/*
 * -------------------------------------------------------------------------
 * Slug guard
 * -------------------------------------------------------------------------
 */

/**
 * Keeps post slugs clear of the topic views (Requirement 7.4).
 *
 * Runs on wp_unique_post_slug, after WordPress has made the slug unique.
 * A post slug equal to a topic key becomes "{slug}-guide" (see
 * rx_safe_post_slug()); if that is taken by another post, "-2", "-3", …
 * is added, the same way WordPress de-duplicates slugs. Pages and other
 * post types are left alone: they don't live under /blog/.
 *
 * WordPress skips this filter for drafts and pending posts and runs it
 * again on publish, so a published post never keeps a topic slug.
 *
 * @param string $slug          Unique slug chosen by WordPress.
 * @param int    $post_id       Post id (0 for a new post).
 * @param string $post_status   Post status.
 * @param string $post_type     Post type.
 * @param int    $post_parent   Parent id.
 * @param string $original_slug Slug before WordPress made it unique.
 * @return string
 */
function rx_blog_unique_post_slug( $slug, $post_id = 0, $post_status = '', $post_type = '', $post_parent = 0, $original_slug = '' ) {
	if ( 'post' !== $post_type || ! is_string( $slug ) || '' === $slug ) {
		return $slug;
	}

	$safe = rx_safe_post_slug( $slug, RX_TOPICS );
	if ( $safe === $slug ) {
		return $slug;
	}

	$candidate = $safe;
	for ( $n = 2; rx_blog_slug_taken( $candidate, (int) $post_id ); $n++ ) {
		$candidate = $safe . '-' . $n;
	}
	return $candidate;
}
add_filter( 'wp_unique_post_slug', 'rx_blog_unique_post_slug', 10, 6 );

/**
 * Whether another post already uses a slug (the same check WordPress runs
 * for non-hierarchical post types: any status, same post type).
 *
 * @param string $slug    Slug to check.
 * @param int    $post_id Post to ignore (the one being saved).
 */
function rx_blog_slug_taken( string $slug, int $post_id ): bool {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- mirrors wp_unique_post_slug(); must see uncached rows.
	$found = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = 'post' AND ID != %d LIMIT 1",
			$slug,
			$post_id
		)
	);
	return null !== $found;
}

/*
 * -------------------------------------------------------------------------
 * Archive views → 404
 * -------------------------------------------------------------------------
 */

/**
 * Whether the main query is a view the site doesn't have: category, tag
 * (and any other taxonomy, such as post formats), date, author, search,
 * feed (including comment feeds), attachment, or a paged list.
 *
 * Sitemap requests (core and Yoast) are never counted, even when paged.
 */
function rx_blog_is_disabled_view(): bool {
	if ( get_query_var( 'sitemap' ) || get_query_var( 'sitemap-stylesheet' ) || get_query_var( 'yoast-sitemap-xsl' ) ) {
		return false;
	}
	return is_category() || is_tag() || is_tax() || is_date() || is_author()
		|| is_search() || is_feed() || is_attachment() || is_paged();
}

/**
 * Sends the views listed in rx_blog_is_disabled_view() to the 404 page.
 *
 * Hooked twice, both at priority 0, and idempotent:
 * - `wp`: runs before Yoast's own handling on `wp` (with disable-date or
 *   disable-author on, Yoast 301s date and author archives to the home
 *   page; with disable-attachment on, it 301s attachments to the file).
 * - `template_redirect`: the hook WordPress documents for this, before
 *   redirect_canonical() (10) and Yoast's attachment redirect (1). It
 *   catches a view whose query flags changed after `wp`.
 *
 * The admin (whose post lists run wp() too), AJAX, cron and REST requests
 * are never touched. The page then renders 404.php with HTTP 404 and
 * no-cache headers.
 */
function rx_blog_disable_archives(): void {
	global $wp_query;

	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( ! $wp_query instanceof WP_Query || $wp_query->is_404() || ! rx_blog_is_disabled_view() ) {
		return;
	}

	$wp_query->set_404();

	/*
	 * set_404() keeps is_feed, and the template loader serves a feed
	 * whenever is_feed is true, so feeds would still print. Clear it, and
	 * drop the posts the archive query found, so this request behaves
	 * like any other 404 (no global $post, no queried object).
	 */
	$wp_query->is_feed           = false;
	$wp_query->is_comment_feed   = false;
	$wp_query->posts             = array();
	$wp_query->post_count        = 0;
	$wp_query->current_post      = -1;
	$wp_query->found_posts       = 0;
	$wp_query->max_num_pages     = 0;
	$wp_query->post              = null;
	$wp_query->queried_object    = null;
	$wp_query->queried_object_id = null;
	$GLOBALS['post']             = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- the archive's first post must not leak into the 404 page.

	status_header( 404 );
	nocache_headers();

	if ( ! headers_sent() ) {
		// A feed request already got a feed Content-Type and an ETag in WP::send_headers().
		header_remove( 'ETag' );
		header( 'Content-Type: ' . get_option( 'html_type', 'text/html' ) . '; charset=' . get_option( 'blog_charset', 'UTF-8' ) );
	}

	rx_blog_unhook_404_redirects();
}
add_action( 'wp', 'rx_blog_disable_archives', 0 );
add_action( 'template_redirect', 'rx_blog_disable_archives', 0 );

/**
 * Keeps a 404 set by rx_blog_disable_archives() from being redirected,
 * for the current request only.
 *
 * - redirect_canonical() and wp_old_slug_redirect() try to "repair" a 404:
 *   a post comment feed (/blog/{post}/feed/) would be redirected to a
 *   guessed feed URL, ?attachment_id= to the attachment page.
 * - With disable-date on, Yoast 301s any URL that carries a date query var
 *   (?m=, ?year=, …) to the same URL without it, even on a 404, which
 *   would turn /?m=2026 into a redirect to the home page.
 *
 * Nothing happens when a callback is not hooked (for example when Yoast
 * is inactive).
 */
function rx_blog_unhook_404_redirects(): void {
	global $wp_filter;

	remove_action( 'template_redirect', 'redirect_canonical' );
	remove_action( 'template_redirect', 'wp_old_slug_redirect' );

	if ( ! isset( $wp_filter['template_redirect'] ) || ! $wp_filter['template_redirect'] instanceof WP_Hook ) {
		return;
	}
	foreach ( $wp_filter['template_redirect']->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$fn = $callback['function'] ?? null;
			if (
				is_array( $fn ) && isset( $fn[0], $fn[1] ) && is_object( $fn[0] )
				&& 'disable_date_queries' === $fn[1]
				&& str_starts_with( get_class( $fn[0] ), 'Yoast\\WP\\SEO\\' )
			) {
				remove_action( 'template_redirect', $fn, $priority );
			}
		}
	}
}

/*
 * -------------------------------------------------------------------------
 * Post queries and post facts
 * -------------------------------------------------------------------------
 */

/**
 * Every published post, newest first (post date, then id). One query per
 * request; post meta (and so the SCF fields) is primed with it. The list
 * is rebuilt after any post cache is cleaned, so a post saved during the
 * same request is included.
 *
 * @param bool $reset Forget the cached list (returns an empty array).
 * @return list<WP_Post>
 */
function rx_blog_all_posts( bool $reset = false ): array {
	static $posts = null;

	if ( $reset ) {
		$posts = null;
		return array();
	}
	if ( null === $posts ) {
		$posts = array_values(
			get_posts(
				array(
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'numberposts'            => -1,
					'orderby'                => array(
						'date' => 'DESC',
						'ID'   => 'DESC',
					),
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
				)
			)
		);
	}
	return $posts;
}

/**
 * Drops the cached post list when a post changes.
 */
function rx_blog_all_posts_reset(): void {
	rx_blog_all_posts( true );
}
add_action( 'clean_post_cache', 'rx_blog_all_posts_reset' );

/**
 * Published posts, newest first. Port of blog_posts(), plus the filters
 * the hub, topic views and "Keep reading" need. The hub ignores the main
 * query's paging and lists every post, as today.
 *
 * Arguments (all optional):
 * - `topic`        Topic key: only posts with that topic. '' (default) = all
 *                  posts; a key outside RX_TOPICS gives an empty list.
 * - `exclude`      Post id, WP_Post, or a list of them, to leave out.
 * - `limit`        Maximum number of posts; 0 (default) = no limit.
 * - `prefer_topic` Topic key whose posts come first; each group stays
 *                  newest first. '' (default) = plain date order.
 *
 * Examples:
 *   rx_blog_posts()                                          hub: all posts
 *   rx_blog_posts( [ 'topic' => rx_blog_current_topic() ] )  topic view
 *   rx_blog_posts( [ 'exclude' => $post, 'prefer_topic' => rx_blog_post_topic( $post ), 'limit' => 3 ] )
 *                                                            "Keep reading": same topic first, then newest
 *
 * The featured guide is the first post in rx_blog_posts() with the SCF
 * `featured` field on.
 *
 * @param array $args See above.
 * @return list<WP_Post>
 */
function rx_blog_posts( array $args = array() ): array {
	$args = wp_parse_args(
		$args,
		array(
			'topic'        => '',
			'exclude'      => array(),
			'limit'        => 0,
			'prefer_topic' => '',
		)
	);

	$topic = is_string( $args['topic'] ) ? $args['topic'] : '';
	if ( '' !== $topic && ! isset( RX_TOPICS[ $topic ] ) ) {
		return array();
	}

	$exclude = array();
	foreach ( is_array( $args['exclude'] ) ? $args['exclude'] : array( $args['exclude'] ) as $item ) {
		$id = $item instanceof WP_Post ? (int) $item->ID : (int) $item;
		if ( $id > 0 ) {
			$exclude[ $id ] = true;
		}
	}

	$posts = array();
	foreach ( rx_blog_all_posts() as $post ) {
		if ( isset( $exclude[ (int) $post->ID ] ) ) {
			continue;
		}
		if ( '' !== $topic && rx_blog_post_topic( $post ) !== $topic ) {
			continue;
		}
		$posts[] = $post;
	}

	$prefer = is_string( $args['prefer_topic'] ) ? $args['prefer_topic'] : '';
	if ( '' !== $prefer && isset( RX_TOPICS[ $prefer ] ) ) {
		$first = array();
		$rest  = array();
		foreach ( $posts as $post ) {
			if ( rx_blog_post_topic( $post ) === $prefer ) {
				$first[] = $post;
			} else {
				$rest[] = $post;
			}
		}
		$posts = array_merge( $first, $rest );
	}

	$limit = (int) $args['limit'];
	return $limit > 0 ? array_slice( $posts, 0, $limit ) : $posts;
}

/**
 * Topic key of a post: its SCF `topic` field (meta key `topic`), read
 * from post meta so it works without SCF too.
 *
 * @param int|WP_Post|null $post Post, post id, or null for the current post.
 * @return string A key of RX_TOPICS, or '' when the post has no valid topic.
 */
function rx_blog_post_topic( $post = null ): string {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	$topic = get_post_meta( $post->ID, 'topic', true );
	return is_string( $topic ) && isset( RX_TOPICS[ $topic ] ) ? $topic : '';
}

/**
 * Number of published posts per topic, for the hub's topic links.
 *
 * Every RX_TOPICS key is present, in RX_TOPICS order, including topics
 * with 0 posts (the hub hides those, as today). Posts without a valid
 * topic count only towards the "All guides" total, count( rx_blog_posts() ).
 *
 * @return array<string, int> Topic key => count.
 */
function rx_blog_topic_counts(): array {
	$counts = array_fill_keys( array_keys( RX_TOPICS ), 0 );
	foreach ( rx_blog_all_posts() as $post ) {
		$topic = rx_blog_post_topic( $post );
		if ( '' !== $topic ) {
			++$counts[ $topic ];
		}
	}
	return $counts;
}

/**
 * A date as the blog prints it: "14 Oct 2026" ('j M Y'). Port of blog_date().
 *
 * - A post (WP_Post, id, or null for the current post): its publish date.
 * - A string: a date such as the SCF `updated` value ('Y-m-d', or the
 *   stored 'Ymd'), or a MySQL datetime.
 *
 * Month names go through the site locale (date_i18n), so an English site
 * prints exactly what the PHP site prints.
 *
 * @param int|string|WP_Post|null $date Post or date.
 * @return string Plain text, '' when there is no valid date.
 */
function rx_blog_date( $date = null ): string {
	if ( is_string( $date ) ) {
		$raw = trim( $date );
		if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $raw, $m ) ) {
			$raw = $m[1] . '-' . $m[2] . '-' . $m[3];
		}
	} else {
		$post = get_post( $date );
		if ( ! $post instanceof WP_Post ) {
			return '';
		}
		$raw = (string) $post->post_date;
	}

	if ( '' === $raw || str_starts_with( $raw, '0000-00-00' ) ) {
		return '';
	}
	$out = mysql2date( 'j M Y', $raw );
	return is_string( $out ) ? $out : '';
}

/**
 * Author line: the brand team (no invented people). Port of blog_author().
 *
 * @return string Plain text: "The {Site Title} team", or "Our product team"
 *                when the Site Title is empty.
 */
function rx_blog_author(): string {
	$brand = rx_brand();
	if ( '' === $brand ) {
		return __( 'Our product team', 'pharmacy' );
	}
	/* translators: %s: brand name (the Site Title). */
	return sprintf( __( 'The %s team', 'pharmacy' ), $brand );
}
