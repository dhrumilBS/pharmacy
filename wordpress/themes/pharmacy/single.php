<?php
/**
 * Single blog post. Port of the PHP site's post layout, blog_start() and
 * blog_end() in partials/blog.php, with the same markup, classes, ids and
 * data attributes (assets/css/blog.css, assets/js/blog.js):
 *
 *   reading progress bar (.b-progress) → <main id="main" class="b-post">
 *   → page hero (crumbs Home / Blog & guides / {topic}, title, summary line)
 *   → meta bar: topic pill linking to /blog/{topic}/, author, date, read
 *     time, optional "Updated" date, copy-link button ([data-copy])
 *   → cover (the post's product screen, eager)
 *   → aside ("On this page" list + demo box) and the article: the `body`
 *     layouts (parts/blog-layouts.php) with ids added to every <h2> by
 *     rx_blog_toc(), then the byline
 *   → FAQ "Asked about this" (answers bq0, bq1, … / buttons bq0b, …)
 *   → "Keep reading": 3 cards, same topic first, then newest.
 *
 * Fields (acf-json/group_rx_post.json): dek, topic, read_time, updated
 * (Y-m-d), faq [q, a_html, confirmed], body (flexible content), and the
 * cover fields through parts/blog-cover.php. The title is post_title as
 * stored (no the_title filters, so wptexturize can't change the copy).
 *
 * Differences from the PHP site, by design:
 * - data-copy holds the root-relative permalink (/…/blog/{slug}/); blog.js
 *   prepends location.origin.
 * - The topic pill links to /blog/{topic}/ instead of blog/index.php?topic=.
 * - Empty pieces are left out: the read time when it is 0, the "On this
 *   page" list when the article has no <h2>, the FAQ section when no item
 *   renders, "Keep reading" when there is no other post.
 *
 * Structured data is not printed here: Yoast outputs Article and the
 * breadcrumb, and inc/seo.php adds the FAQPage (confirmed answers only).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text(), rx_html(), esc_*() and the parts where they are built.

if ( have_posts() ) {
	the_post();
}
$rx_sp_post = get_post();

// Only a post reaches this template; anything else gets the 404 page.
if ( ! $rx_sp_post instanceof WP_Post ) {
	$rx_sp_404 = get_404_template();
	if ( '' !== $rx_sp_404 ) {
		include $rx_sp_404;
	}
	return;
}

$rx_sp_id = (int) $rx_sp_post->ID;

/*
 * -------------------------------------------------------------------------
 * Post facts
 * -------------------------------------------------------------------------
 */

/** A scalar field of the post: the SCF value, or the raw post meta when SCF is inactive. */
$rx_sp_scalar = static function ( string $name ) use ( $rx_sp_id ) {
	$value = rx_field( $name, $rx_sp_id );
	return null === $value ? get_post_meta( $rx_sp_id, $name, true ) : $value;
};

$rx_sp_topic      = rx_blog_post_topic( $rx_sp_post );
$rx_sp_topic_name = '' !== $rx_sp_topic ? RX_TOPICS[ $rx_sp_topic ][0] : '';
$rx_sp_dek        = $rx_sp_scalar( 'dek' );
$rx_sp_mins       = (int) $rx_sp_scalar( 'read_time' );
$rx_sp_author     = rx_blog_author();
$rx_sp_demo       = rx_url( 'demo' );
$rx_sp_blog       = rx_url( 'blog' );

// Publish date: datetime attribute (Y-m-d) and display text ("14 Oct 2026").
$rx_sp_iso  = '0000-00-00 00:00:00' !== $rx_sp_post->post_date ? (string) mysql2date( 'Y-m-d', $rx_sp_post->post_date ) : '';
$rx_sp_date = rx_blog_date( $rx_sp_post );

// Optional "Updated" date; the byline uses it in place of the publish date.
$rx_sp_updated = $rx_sp_scalar( 'updated' );
$rx_sp_updated = is_string( $rx_sp_updated ) ? rx_blog_date( $rx_sp_updated ) : '';
$rx_sp_as_of   = '' !== $rx_sp_updated ? $rx_sp_updated : $rx_sp_date;

// Copy link: root-relative, as blog_url() was; blog.js prepends location.origin.
$rx_sp_copy = wp_make_link_relative( (string) get_permalink( $rx_sp_post ) );

/*
 * -------------------------------------------------------------------------
 * Article, table of contents, FAQ and related posts
 * -------------------------------------------------------------------------
 */

// Article: the body layouts, then an id on every <h2> and the "On this page" entries.
ob_start();
get_template_part( 'parts/blog-layouts', null, array( 'rows' => rx_field( 'body', $rx_sp_id ) ) );
[ $rx_sp_body, $rx_sp_toc ] = rx_blog_toc( (string) ob_get_clean() );

$rx_sp_toc_html = '';
foreach ( $rx_sp_toc as $rx_sp_entry ) {
	$rx_sp_toc_html .= '<li><a href="#' . esc_attr( $rx_sp_entry['id'] ) . '">' . esc_html( $rx_sp_entry['label'] ) . '</a></li>';
}

// FAQ: rendered into a buffer so the section is skipped when no item is left.
$rx_sp_faq_rows = rx_field( 'faq', $rx_sp_id );
$rx_sp_faq      = '';
if ( is_array( $rx_sp_faq_rows ) && $rx_sp_faq_rows ) {
	ob_start();
	get_template_part(
		'parts/faq-list',
		null,
		array(
			'items'      => $rx_sp_faq_rows,
			'ids'        => array(
				'answer' => 'bq%d',
				'button' => 'bq%db',
				'start'  => 0,
			),
			'confirm_on' => 'item',
		)
	);
	$rx_sp_faq = trim( (string) ob_get_clean() );
}

$rx_sp_sales_phone = trim( (string) rx_setting( 'sales_phone' ) );
$rx_sp_sales_tel   = trim( (string) rx_setting( 'sales_tel' ) );
if ( '' === $rx_sp_sales_tel ) {
	$rx_sp_sales_tel = (string) preg_replace( '/[^0-9+]/', '', $rx_sp_sales_phone );
}

// Keep reading: same topic first, then newest, 3 posts.
$rx_sp_related = rx_blog_posts(
	array(
		'exclude'      => $rx_sp_post,
		'prefer_topic' => $rx_sp_topic,
		'limit'        => 3,
	)
);

get_header();
?>
<div class="b-progress" aria-hidden="true"><i></i></div><main id="main" class="b-post">
<?php
get_template_part(
	'parts/page-hero',
	null,
	array(
		'crumb'      => $rx_sp_topic_name,
		'parent'     => __( 'Blog & guides', 'pharmacy' ),
		'parent_url' => $rx_sp_blog,
		'h1'         => $rx_sp_post->post_title,
		'lead'       => is_scalar( $rx_sp_dek ) ? (string) $rx_sp_dek : '',
	)
);
?>
  <div class="container b-meta-bar">
    <p class="b-meta"><?php get_template_part( 'parts/blog-pill', null, array( 'topic' => $rx_sp_topic, 'link' => true ) ); ?><span><?php echo esc_html( $rx_sp_author ); ?></span><span aria-hidden="true">·</span>
      <?php if ( '' !== $rx_sp_iso && '' !== $rx_sp_date ) : ?><time datetime="<?php echo esc_attr( $rx_sp_iso ); ?>"><?php echo esc_html( $rx_sp_date ); ?></time><?php endif; ?><?php if ( $rx_sp_mins > 0 ) : ?><span aria-hidden="true">·</span><span><?php /* translators: %d: reading time in minutes. */ echo esc_html( sprintf( __( '%d min read', 'pharmacy' ), $rx_sp_mins ) ); ?></span><?php endif; ?>
      <?php if ( '' !== $rx_sp_updated ) : ?><span aria-hidden="true">·</span><span><?php /* translators: %s: date the post was last updated, e.g. "14 Oct 2026". */ echo esc_html( sprintf( __( 'Updated %s', 'pharmacy' ), $rx_sp_updated ) ); ?></span><?php endif; ?></p>
    <button type="button" class="b-copy" data-copy="<?php echo esc_attr( $rx_sp_copy ); ?>"><svg class="icon" aria-hidden="true"><use href="#i-file"/></svg><span><?php esc_html_e( 'Copy link', 'pharmacy' ); ?></span></button>
  </div>
  <div class="container"><figure class="b-hero-cover"><?php get_template_part( 'parts/blog-cover', null, array( 'post' => $rx_sp_post, 'loading' => 'eager' ) ); ?></figure></div>

  <div class="container b-layout">
    <aside class="b-aside">
      <?php if ( '' !== $rx_sp_toc_html ) : ?>
      <details class="b-toc" open>
        <summary><?php esc_html_e( 'On this page', 'pharmacy' ); ?></summary>
        <ol><?php echo $rx_sp_toc_html; ?></ol>
      </details>
      <?php endif; ?>
      <div class="b-aside-cta">
        <p><b><?php esc_html_e( 'See it with your own stock', 'pharmacy' ); ?></b><?php esc_html_e( 'A 30-minute demo using items from your shelf.', 'pharmacy' ); ?></p>
        <a class="btn btn-primary btn-sm" href="<?php echo esc_url( $rx_sp_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></a>
      </div>
    </aside>
    <article class="b-article">
      <?php echo $rx_sp_body; ?>
      <footer class="b-byline">
        <span class="b-byline-mark" aria-hidden="true"><svg class="brand-logo"><use href="#logo-mark"/></svg></span>
        <div><p><b><?php /* translators: %s: author line, e.g. "The Rxtiva team". */ echo esc_html( sprintf( __( 'Written by %s', 'pharmacy' ), $rx_sp_author ) ); ?></b></p>
          <p><?php
			if ( '' !== $rx_sp_as_of ) {
				/* translators: %s: date the guide was written or last updated, e.g. "14 Oct 2026". */
				echo esc_html( sprintf( __( 'We build pharmacy software for medical stores, chains and hospital pharmacies. Where this guide describes the software, it matches the app as of %s. Practical advice here is general; check rules that apply to your store with your adviser.', 'pharmacy' ), $rx_sp_as_of ) );
			} else {
				esc_html_e( 'We build pharmacy software for medical stores, chains and hospital pharmacies. Practical advice here is general; check rules that apply to your store with your adviser.', 'pharmacy' );
			}
			?></p></div>
      </footer>
    </article>
  </div>

  <?php if ( '' !== $rx_sp_faq ) : ?>
  <section class="section bg-soft" aria-labelledby="b-faq">
    <div class="container faq-grid">
      <div class="faq-intro"><span class="eyebrow"><?php esc_html_e( 'Questions', 'pharmacy' ); ?></span><h2 id="b-faq"><?php esc_html_e( 'Asked about this', 'pharmacy' ); ?></h2>
        <p><?php
			$rx_sp_faq_link = '<a href="' . esc_url( rx_url( 'faq' ) ) . '">' . esc_html__( 'FAQ page', 'pharmacy' ) . '</a>';
			if ( '' !== $rx_sp_sales_phone && '' !== $rx_sp_sales_tel ) {
				printf(
					/* translators: 1: "FAQ page" link, 2: sales phone number (a tel: link). */
					esc_html__( 'More on the %1$s, or call sales on %2$s.', 'pharmacy' ),
					$rx_sp_faq_link,
					'<a href="' . esc_url( 'tel:' . $rx_sp_sales_tel ) . '">' . esc_html( $rx_sp_sales_phone ) . '</a>'
				);
			} else {
				printf(
					/* translators: %s: "FAQ page" link. */
					esc_html__( 'More on the %s.', 'pharmacy' ),
					$rx_sp_faq_link
				);
			}
			?></p></div>
      <?php echo $rx_sp_faq; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $rx_sp_related ) : ?>
  <section class="section" aria-labelledby="b-rel">
    <div class="container">
      <div class="b-sec-head"><h2 id="b-rel"><?php esc_html_e( 'Keep reading', 'pharmacy' ); ?></h2><a href="<?php echo esc_url( $rx_sp_blog ); ?>"><?php esc_html_e( 'All guides', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></div>
      <div class="b-grid"><?php
		foreach ( $rx_sp_related as $rx_sp_rel ) {
			get_template_part( 'parts/blog-card', null, array( 'post' => $rx_sp_rel ) );
		}
		?></div>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
