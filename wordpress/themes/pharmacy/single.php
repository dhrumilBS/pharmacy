<?php
/**
 * Single blog post. Port of the PHP site's post layout, blog_start() and
 * blog_end() in partials/blog.php, with the same markup, classes, ids and
 * data attributes (assets/css/blog.css, assets/js/blog.js):
 *
 *   reading progress bar (.b-progress) → <main id="main" class="b-post">
 *   → page hero (crumbs Home / Blog / {topic}, title, summary line)
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
$rx_sp_mins       = 0; // counted from the body below
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

// Article: the guide's body, then an id on every <h2> and the "On this page" entries.
$rx_sp_short = array();
ob_start();
if ( rx_blog_is_legacy( $rx_sp_post ) ) {
	// Guides written with the SCF `body` field (the six imported ones).
	$rx_sp_rows = rx_field( 'body', $rx_sp_id );
	if ( is_array( $rx_sp_rows ) ) {
		foreach ( $rx_sp_rows as $rx_sp_k => $rx_sp_row ) {
			if ( is_array( $rx_sp_row ) && 'takeaways' === ( $rx_sp_row['acf_fc_layout'] ?? '' ) ) {
				// The first "In short" list is the short answer in the hero, not part of the article body.
				foreach ( is_array( $rx_sp_row['items'] ?? null ) ? $rx_sp_row['items'] : array() as $rx_sp_item ) {
					$rx_sp_li = rx_html( is_array( $rx_sp_item ) ? ( $rx_sp_item['html'] ?? '' ) : '' );
					if ( '' !== trim( wp_strip_all_tags( $rx_sp_li ) ) ) {
						$rx_sp_short[] = $rx_sp_li;
					}
				}
				unset( $rx_sp_rows[ $rx_sp_k ] );
				break;
			}
		}
	}
	get_template_part( 'parts/blog-layouts', null, array( 'rows' => $rx_sp_rows ) );
} else {
	// Guides written in the block editor: the blocks as written; the first "In short" goes to the hero.
	$rx_sp_blocks = parse_blocks( $rx_sp_post->post_content );
	foreach ( $rx_sp_blocks as $rx_sp_k => $rx_sp_block ) {
		if ( 'healthray/takeaways' === ( $rx_sp_block['blockName'] ?? '' ) ) {
			$rx_sp_inner = '';
			foreach ( $rx_sp_block['innerBlocks'] ?? array() as $rx_sp_in ) {
				$rx_sp_inner .= render_block( $rx_sp_in );
			}
			foreach ( rx_bb_items( $rx_sp_inner ) as $rx_sp_li ) {
				$rx_sp_short[] = wp_kses_post( $rx_sp_li );
			}
			unset( $rx_sp_blocks[ $rx_sp_k ] );
			break;
		}
	}
	foreach ( $rx_sp_blocks as $rx_sp_block ) {
		echo render_block( $rx_sp_block ), "\n";
	}
}
[ $rx_sp_body, $rx_sp_toc ] = rx_blog_toc( (string) ob_get_clean() );

// Read time: counted from the text of the guide (the short version and the body), not typed in.
$rx_sp_mins = max( 1, (int) ceil( rx_blog_words( $rx_sp_body . ' ' . implode( ' ', $rx_sp_short ) ) / RX_BLOG_WPM ) );
if ( (int) get_post_meta( $rx_sp_id, '_rx_read_mins', true ) !== $rx_sp_mins ) {
	update_post_meta( $rx_sp_id, '_rx_read_mins', $rx_sp_mins ); // Cards and the home page show the same figure.
}

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

// Table colour to compare: ?tbl=5 is plain grey; none is slate blue.
$rx_sp_tb = isset( $_GET['tbl'] ) && in_array( (string) $_GET['tbl'], array( '5' ), true ) ? (string) $_GET['tbl'] : '2'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput -- read-only preview switch, whitelisted.

get_header();
?>
<div class="b-progress" aria-hidden="true"><i></i></div><main id="main" class="b-post b-ui-6 tb-<?php echo esc_attr( $rx_sp_tb ); ?>">

  <?php
	$rx_sp_thumb   = (int) get_post_thumbnail_id( $rx_sp_post );
	$rx_sp_has_img = $rx_sp_thumb || '' !== trim( (string) $rx_sp_scalar( 'cover_screen' ) );
	$rx_sp_share   = rawurlencode( (string) get_permalink( $rx_sp_post ) );
	?>
  <header class="bz">
    <div class="container">
      <nav class="bz-crumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmacy' ); ?>"><a href="<?php echo esc_url( rx_url( 'home' ) ); ?>"><?php esc_html_e( 'Home', 'pharmacy' ); ?></a><span aria-hidden="true">/</span><a href="<?php echo esc_url( $rx_sp_blog ); ?>"><?php esc_html_e( 'Blog', 'pharmacy' ); ?></a><?php if ( '' !== $rx_sp_topic_name ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( rx_blog_topic_url( $rx_sp_topic ) ); ?>"><?php echo esc_html( $rx_sp_topic_name ); ?></a><?php endif; ?></nav>
      <div class="bz-grid<?php echo $rx_sp_has_img ? '' : ' no-img'; ?>">
        <div class="bz-copy">
          <?php get_template_part( 'parts/blog-pill', null, array( 'topic' => $rx_sp_topic, 'link' => true ) ); ?>
          <h1><?php echo esc_html( $rx_sp_post->post_title ); ?></h1>
          <div class="bz-by">
            <div class="bz-who"><span class="bz-av" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/favicon-32.png' ) ); ?>" width="28" height="28" alt=""></span><p><small><?php esc_html_e( 'Written by', 'pharmacy' ); ?></small><b><?php echo esc_html( $rx_sp_author ); ?></b></p></div>
            <ul class="bz-facts">
              <li><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg><?php /* translators: %d: reading time in minutes. */ echo esc_html( sprintf( __( '%d min read', 'pharmacy' ), $rx_sp_mins ) ); ?></li>
              <?php if ( '' !== $rx_sp_as_of ) : ?><li><svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg><?php /* translators: %s: date the guide was written or last updated, e.g. "14 Oct 2026". */ echo esc_html( sprintf( __( 'Matches the app as of %s', 'pharmacy' ), $rx_sp_as_of ) ); ?></li><?php endif; ?>
            </ul>
          </div>
        </div>
        <?php if ( $rx_sp_thumb ) : ?>
        <figure class="bz-img"><?php echo wp_get_attachment_image( $rx_sp_thumb, 'rx-guide-feature', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(min-width: 1101px) 520px, 100vw' ) ); ?></figure>
        <?php elseif ( $rx_sp_has_img ) : ?>
        <figure class="bz-img b-hero-cover"><?php get_template_part( 'parts/blog-cover', null, array( 'post' => $rx_sp_post, 'loading' => 'eager' ) ); ?><figcaption><?php esc_html_e( 'Sample screen, dummy data', 'pharmacy' ); ?></figcaption></figure>
        <?php endif; ?>
      </div>
    </div>
  </header>
  <div class="container b-layout">
    <aside class="b-aside">
      <?php if ( '' !== $rx_sp_toc_html ) : ?>
      <div class="bz-card"><details class="b-toc" open><summary><?php esc_html_e( 'On this page', 'pharmacy' ); ?></summary><ol><?php echo $rx_sp_toc_html; ?></ol></details></div>
      <?php endif; ?>
      <?php if ( $rx_sp_related ) : ?>
      <div class="bz-card bz-rel"><h3><?php esc_html_e( 'Related articles', 'pharmacy' ); ?></h3><ul>
        <?php
		foreach ( $rx_sp_related as $rx_sp_r ) :
			$rx_sp_rt = rx_blog_post_topic( $rx_sp_r );
			?>
        <li><a href="<?php echo esc_url( get_permalink( $rx_sp_r ) ); ?>"><?php if ( '' !== $rx_sp_rt ) : ?><small><?php echo esc_html( RX_TOPICS[ $rx_sp_rt ][0] ); ?></small><?php endif; ?><b><?php echo esc_html( $rx_sp_r->post_title ); ?></b></a></li>
        <?php endforeach; ?>
      </ul></div>
      <?php endif; ?>
    </aside>
    <article class="b-article" id="guide-start">
      <?php if ( '' !== $rx_sp_toc_html ) : ?>
      <details class="b-toc bz-toc-m"><summary><?php esc_html_e( 'On this page', 'pharmacy' ); ?></summary><ol><?php echo $rx_sp_toc_html; ?></ol></details>
      <?php endif; ?>
      <?php if ( $rx_sp_short ) : ?>
      <aside class="b-h2-slip sv-a" aria-label="<?php esc_attr_e( 'The short version', 'pharmacy' ); ?>">
        <p class="b-h2-slip-h"><b><?php esc_html_e( 'The short version', 'pharmacy' ); ?></b><span><?php esc_html_e( '20 seconds', 'pharmacy' ); ?></span></p>
        <ol><?php foreach ( $rx_sp_short as $rx_sp_li ) { echo '<li>' . $rx_sp_li . '</li>'; } ?></ol>
        <p class="b-h2-slip-f"><span><?php /* translators: %d: reading time in minutes. */ echo esc_html( sprintf( __( '%d min for the full article', 'pharmacy' ), $rx_sp_mins ) ); ?></span><a href="#guide-start"><?php esc_html_e( 'Read it', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>
      </aside>
      <?php endif; ?>
      <?php echo $rx_sp_body; ?>
      <footer class="b-byline">
        <span class="b-byline-mark" aria-hidden="true"><img class="brand-logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/favicon-32.png' ) ); ?>" width="32" height="32" alt=""></span>
        <div><p><b><?php /* translators: %s: author line, e.g. "The Healthray team". */ echo esc_html( sprintf( __( 'Written by %s', 'pharmacy' ), $rx_sp_author ) ); ?></b></p>
          <p><?php
			if ( '' !== $rx_sp_as_of ) {
				/* translators: %s: date the guide was written or last updated, e.g. "14 Oct 2026". */
				echo esc_html( sprintf( __( 'We build pharmacy software for medical stores, chains and hospital pharmacies. Where this article describes the software, it matches the app as of %s. Practical advice here is general; check rules that apply to your store with your adviser.', 'pharmacy' ), $rx_sp_as_of ) );
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
      <div class="b-sec-head"><h2 id="b-rel"><?php esc_html_e( 'Keep reading', 'pharmacy' ); ?></h2><a href="<?php echo esc_url( $rx_sp_blog ); ?>"><?php esc_html_e( 'All articles', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></div>
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
