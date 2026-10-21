<?php
/**
 * Blog hub and topic views: the posts page (/blog/) and /blog/{topic}/.
 * Port of the PHP_Site's blog/index.php (and blog/index.php?topic=x): same
 * sections, in the same order, with the same markup.
 *
 * WordPress renders the posts page with this file. A topic view is the same
 * request with the rx_topic query var (rewrite rule in inc/blog.php), read
 * through rx_blog_current_topic(): '' on the hub, a key of RX_TOPICS on a
 * topic view. The main query's posts and paging are not used; the lists come
 * from rx_blog_posts(), newest first, as blog_posts() today.
 *
 * Copy comes from the Blog page's fields (group_rx_blog, on the posts page):
 *
 * 1. parts/page-hero: `hero` (eyebrow, h1, lead). The crumb is the page
 *    title ("Blog"). A topic view shows the topic name as the crumb,
 *    the page title as a parent crumb linking to the hub, and the h1
 *    "{Topic} guides".
 * 2. Topic links: "All articles" with the total, then every topic that has
 *    posts with its count. The current view gets .is-on and aria-current.
 *    Topic links point at /blog/{topic}/ (rx_blog_topic_url()).
 * 3. Featured article + Start here (hub only, and only when a post has
 *    `featured` on): the first featured post as the large card, beside the
 *    `start_title` reading path of `start_here` posts (in their order) and a
 *    `start_link_label` link to the Switch page.
 * 4. Grid: "Latest articles" on the hub (every post but the featured one),
 *    "N guides" on a topic view (with an "All topics" link), or the empty
 *    message when the topic has no posts.
 * 5. Tools strip: `tools_head`, `tools` [icon, title, text, link_key].
 *
 * Cards and covers come from parts/blog-card.php and parts/blog-cover.php.
 * The Blog + BlogPosting schema is added to Yoast's graph by inc/seo.php.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_*() where they are built.

$rx_bh_topic = rx_blog_current_topic();

/*
 * The Blog page: the queried posts page, else the page_for_posts option.
 * Its fields hold the hub copy; without it, no field is read.
 */
$rx_bh_object  = get_queried_object();
$rx_bh_page_id = $rx_bh_object instanceof WP_Post && 'page' === $rx_bh_object->post_type ? (int) $rx_bh_object->ID : (int) get_option( 'page_for_posts' );
$rx_bh_field   = static fn( string $name ) => $rx_bh_page_id > 0 ? rx_field( $name, $rx_bh_page_id ) : null;

/** Plain-text sub-field of a row or group, rendered and trimmed. */
$rx_bh_text = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_text( $row[ $key ] ?? '' ) ) : '';

/** Read time of a post in minutes: its SCF value, or the raw meta when SCF is inactive. */
$rx_bh_mins = static function ( WP_Post $post ): int {
	return rx_blog_read_mins( $post ); // Counted from the body.
};

$rx_bh_hub_url = rx_url( 'blog' );

// 1. Hero. A topic view changes the crumb, adds the parent link and changes the h1.
$rx_bh_hero      = $rx_bh_field( 'hero' );
$rx_bh_hero_args = array( 'hero' => is_array( $rx_bh_hero ) ? $rx_bh_hero : array() );
if ( '' !== $rx_bh_topic ) {
	$rx_bh_page_title = $rx_bh_page_id > 0 ? (string) get_post_field( 'post_title', $rx_bh_page_id, 'raw' ) : '';
	$rx_bh_topic_name = RX_TOPICS[ $rx_bh_topic ][0];

	$rx_bh_hero_args['crumb']      = $rx_bh_topic_name;
	$rx_bh_hero_args['parent']     = '' !== trim( $rx_bh_page_title ) ? $rx_bh_page_title : __( 'Blog', 'pharmacy' );
	$rx_bh_hero_args['parent_url'] = $rx_bh_hub_url;
	/* translators: %s: blog topic name, e.g. "Expiry & stock". */
	$rx_bh_hero_args['h1'] = sprintf( __( '%s articles', 'pharmacy' ), $rx_bh_topic_name );
}

// 2. Posts and topic counts. The list on this page is the main query, which is paginated (see rx_blog_paginate_main_query()).
global $wp_query;
$rx_bh_all     = rx_blog_posts();
$rx_bh_posts   = $wp_query instanceof WP_Query ? array_values( $wp_query->posts ) : array();
$rx_bh_found   = $wp_query instanceof WP_Query ? (int) $wp_query->found_posts : count( $rx_bh_posts );
list( $rx_bh_paged, $rx_bh_pages ) = rx_blog_page_state();
$rx_bh_counts  = rx_blog_topic_counts();

// 3. Featured article: on page 1 of the hub only (the list leaves it out, on every page).
$rx_bh_feat = '' === $rx_bh_topic && $rx_bh_paged <= 1 ? rx_blog_featured_post() : null;
$rx_bh_grid = $rx_bh_posts;

// 4. Grid heading: "Latest articles" on the hub, "N articles" on a topic view.
$rx_bh_grid_h = '' !== $rx_bh_topic
	/* translators: %d: number of articles on the topic. */
	? esc_html( sprintf( _n( '%d article', '%d articles', $rx_bh_found, 'pharmacy' ), $rx_bh_found ) )
	: esc_html__( 'Latest articles', 'pharmacy' );
$rx_bh_page_label = '';
if ( $rx_bh_pages > 1 ) {
	/* translators: 1: current page, 2: number of pages. */
	$rx_bh_page_label = esc_html( sprintf( __( 'Page %1$d of %2$d', 'pharmacy' ), $rx_bh_paged, $rx_bh_pages ) );
}

// Start here: published posts from `start_here`, in their order.
$rx_bh_path       = array();
$rx_bh_start_t    = '';
$rx_bh_start_more = '';
if ( $rx_bh_feat ) {
	$rx_bh_start_t    = trim( rx_text( $rx_bh_field( 'start_title' ) ) );
	$rx_bh_start_more = trim( rx_text( $rx_bh_field( 'start_link_label' ) ) );
	$rx_bh_refs       = $rx_bh_field( 'start_here' );
	foreach ( is_array( $rx_bh_refs ) ? $rx_bh_refs : array() as $rx_bh_ref ) {
		$rx_bh_post = get_post( $rx_bh_ref instanceof WP_Post ? $rx_bh_ref->ID : (int) $rx_bh_ref );
		if ( $rx_bh_post instanceof WP_Post && 'post' === $rx_bh_post->post_type && 'publish' === $rx_bh_post->post_status ) {
			$rx_bh_path[ (int) $rx_bh_post->ID ] = $rx_bh_post;
		}
	}
	$rx_bh_path = array_values( $rx_bh_path );
}

// 5. Tools strip.
$rx_bh_tools_head = $rx_bh_field( 'tools_head' );
$rx_bh_tools_e    = $rx_bh_text( $rx_bh_tools_head, 'eyebrow' );
$rx_bh_tools_t    = $rx_bh_text( $rx_bh_tools_head, 'title' );
$rx_bh_tools_i    = $rx_bh_text( $rx_bh_tools_head, 'intro' );
$rx_bh_tools      = array();
$rx_bh_tool_rows  = $rx_bh_field( 'tools' );
foreach ( is_array( $rx_bh_tool_rows ) ? $rx_bh_tool_rows : array() as $rx_bh_row ) {
	if ( ! is_array( $rx_bh_row ) ) {
		continue;
	}
	$rx_bh_icon = $rx_bh_row['icon'] ?? '';
	$rx_bh_key  = $rx_bh_row['link_key'] ?? '';
	$rx_bh_tool = array(
		'icon'  => is_string( $rx_bh_icon ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rx_bh_icon ) ? $rx_bh_icon : '',
		'title' => $rx_bh_text( $rx_bh_row, 'title' ),
		'text'  => $rx_bh_text( $rx_bh_row, 'text' ),
		'url'   => rx_url( is_string( $rx_bh_key ) ? $rx_bh_key : '' ),
	);
	if ( '' !== $rx_bh_tool['title'] || '' !== $rx_bh_tool['text'] ) {
		$rx_bh_tools[] = $rx_bh_tool;
	}
}

get_header();
?>
<main id="main" class="b-hub">
<?php get_template_part( 'parts/page-hero', null, $rx_bh_hero_args ); ?>

<section class="section-tight b-filter-sec">
  <div class="container b-filter">
    <nav aria-label="<?php esc_attr_e( 'Topics', 'pharmacy' ); ?>">
      <ul class="b-topics">
        <li><a class="b-topic<?php echo '' !== $rx_bh_topic ? '' : ' is-on'; ?>" href="<?php echo esc_url( $rx_bh_hub_url ); ?>"<?php echo '' !== $rx_bh_topic ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'All articles', 'pharmacy' ); ?> <span><?php echo (int) count( $rx_bh_all ); ?></span></a></li>
        <?php
        foreach ( RX_TOPICS as $rx_bh_k => $rx_bh_def ) :
			if ( empty( $rx_bh_counts[ $rx_bh_k ] ) ) {
				continue;
			}
			$rx_bh_on = $rx_bh_topic === $rx_bh_k;
			?>
          <li><a class="b-topic<?php echo $rx_bh_on ? ' is-on' : ''; ?>" href="<?php echo esc_url( rx_blog_topic_url( $rx_bh_k ) ); ?>"<?php echo $rx_bh_on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $rx_bh_def[0] ); ?> <span><?php echo (int) $rx_bh_counts[ $rx_bh_k ]; ?></span></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</section>

<?php if ( $rx_bh_feat ) : ?>
<section class="section-tight" aria-label="<?php esc_attr_e( 'Featured article', 'pharmacy' ); ?>">
  <div class="container b-top">
    <?php get_template_part( 'parts/blog-card', null, array( 'post' => $rx_bh_feat, 'variant' => 'feature' ) ); ?>
    <?php if ( '' !== $rx_bh_start_t || $rx_bh_path || '' !== $rx_bh_start_more ) : ?>
    <aside class="b-path"<?php echo '' !== $rx_bh_start_t ? ' aria-labelledby="b-path-h"' : ' aria-label="' . esc_attr__( 'Start here', 'pharmacy' ) . '"'; ?>>
      <p class="b-path-k"><?php esc_html_e( 'Start here', 'pharmacy' ); ?></p>
      <?php if ( '' !== $rx_bh_start_t ) : ?><h2 id="b-path-h"><?php echo $rx_bh_start_t; ?></h2><?php endif; ?>
      <?php if ( $rx_bh_path ) : ?>
      <ol class="b-path-list">
        <?php
        foreach ( $rx_bh_path as $rx_bh_p ) :
			$rx_bh_p_mins = $rx_bh_mins( $rx_bh_p );
			?>
          <li><a href="<?php echo esc_url( (string) get_permalink( $rx_bh_p ) ); ?>"><b><?php echo esc_html( $rx_bh_p->post_title ); ?></b><?php if ( $rx_bh_p_mins > 0 ) : ?><span><?php /* translators: %d: reading time in minutes. */ echo esc_html( sprintf( __( '%d min read', 'pharmacy' ), $rx_bh_p_mins ) ); ?></span><?php endif; ?></a></li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
      <?php if ( '' !== $rx_bh_start_more ) : ?><a class="b-path-more" href="<?php echo esc_url( rx_url( 'switch' ) ); ?>"><?php echo $rx_bh_start_more; ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?>
    </aside>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="section-tight" aria-labelledby="b-latest">
  <div class="container">
    <div class="b-sec-head"><h2 id="b-latest"><?php echo $rx_bh_grid_h; ?></h2><?php if ( '' !== $rx_bh_page_label ) : ?><p class="b-sec-meta"><?php echo $rx_bh_page_label; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_bh_topic ) : ?><a href="<?php echo esc_url( $rx_bh_hub_url ); ?>"><?php esc_html_e( 'All topics', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?></div>
    <?php if ( $rx_bh_grid ) : ?>
      <div class="b-grid"><?php foreach ( $rx_bh_grid as $rx_bh_p ) { get_template_part( 'parts/blog-card', null, array( 'post' => $rx_bh_p ) ); } ?></div>
      <?php echo rx_blog_pagination(); ?>
    <?php else : ?>
      <p class="b-empty"><?php esc_html_e( 'No articles on this topic yet.', 'pharmacy' ); ?> <a href="<?php echo esc_url( $rx_bh_hub_url ); ?>"><?php esc_html_e( 'See all articles', 'pharmacy' ); ?></a>.</p>
    <?php endif; ?>
  </div>
</section>

<?php if ( '' !== $rx_bh_tools_t || $rx_bh_tools ) : ?>
<section class="section bg-soft"<?php echo '' !== $rx_bh_tools_t ? ' aria-labelledby="b-tools"' : ''; ?>>
  <div class="container">
    <?php if ( '' !== $rx_bh_tools_e ) : ?><span class="eyebrow"><?php echo $rx_bh_tools_e; ?></span><?php endif; ?>
    <?php if ( '' !== $rx_bh_tools_t ) : ?><div class="b-sec-head"><h2 id="b-tools"><?php echo $rx_bh_tools_t; ?></h2></div><?php endif; ?>
    <?php if ( '' !== $rx_bh_tools_i ) : ?><p><?php echo $rx_bh_tools_i; ?></p><?php endif; ?>
    <?php if ( $rx_bh_tools ) : ?>
    <ul class="b-tools">
      <?php foreach ( $rx_bh_tools as $rx_bh_tool ) : ?>
      <li><a href="<?php echo esc_url( $rx_bh_tool['url'] ); ?>"><?php if ( '' !== $rx_bh_tool['icon'] ) : ?><span class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_bh_tool['icon'] ); ?>"/></svg></span><?php endif; ?><?php if ( '' !== $rx_bh_tool['title'] ) : ?><b><?php echo $rx_bh_tool['title']; ?></b><?php endif; ?><?php if ( '' !== $rx_bh_tool['text'] ) : ?><span><?php echo $rx_bh_tool['text']; ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
