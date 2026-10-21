<?php
/**
 * Creates or updates the sample guide that shows every guide component in one post:
 * Posts > "Blog components (sample)", slug guide-components-sample.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\blog-components-sample.php
 *
 * How to use it: open the post in the block editor, find the component you need, copy the block (or
 * select several and copy) into your own post, and change the text. Nothing in a guide is typed into
 * custom fields: the standfirst, topic, cover and updated date are in the "Guide settings" panel of the
 * post sidebar, and the read time is counted from the text.
 *
 * The sample is hidden from the blog hub, topic views and "Keep reading" (meta `_rx_sample`) and is
 * noindex, so it never reaches readers or search engines; delete it when you no longer need it.
 * Re-running replaces the post's content with the text below.
 *
 * Local only (lib/guard.php).
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once __DIR__ . '/lib/guide-blocks.php';

/* ---- the guide -------------------------------------------------------------------------- */

$img = get_theme_file_uri( 'assets/img/v2-03-expiry.jpg' );

$content = implode(
	"\n\n",
	array(
		// In short: the first one becomes "The short version" in the hero.
		sb( 'healthray/takeaways', array(), sli( array(
			'<strong>In short</strong> is the first block of a post. It is shown at the top of the page as <em>The short version</em>, so write the answer a busy owner needs in 20 seconds.',
			'Three to five points is enough. Each point is one list item; bold the key words.',
			'Everything below this is a component you can copy into your own post. Delete what you do not need.',
		), true ) ),

		sp( 'This sample post shows every component we use in posts, in one place. Copy a block, paste it into your own post and change the words. Headings, paragraphs, lists, images and quotes are normal editor blocks; the boxed parts are the <em>Healthray blog</em> blocks.' ),
		sp( 'Open with the problem in the reader’s own words, in two or three sentences, then say what the article gives them. A post of 2,000 words or more works the same way: the same blocks, as many times as the topic needs. The read time at the top is counted from the text, so it follows every edit.' ),

		sh( 'Headings and the table of contents' ),
		sp( 'Every <strong>Heading 2</strong> becomes an entry in the “On this page” list at the side of the post. Use Heading 3 for smaller parts inside a section; those do not appear in the list.' ),
		sh( 'A smaller heading', 3 ),
		sp( 'Body text is plain paragraphs. Use <strong>bold</strong> for the words a reader should not miss, <em>italics</em> sparingly, and <a href="#">links</a> to the feature pages where the software handles the topic.' ),
		sli( array( 'A bulleted list for items that have no order.', 'Keep each item to one line where you can.', 'Use a numbered list when the order matters.' ) ),
		sb( 'quote', array(), "<blockquote class=\"wp-block-quote\">\n" . sp( 'A quote is for a sentence an owner would repeat to their partner. Use it once or twice in a long post.' ) . "\n<cite>Name, role (only with permission)</cite></blockquote>" ),

		sh( 'Notes: tip, note, watch out and example' ),
		sp( 'The <strong>Note</strong> block has four types. Pick the type in the sidebar; it sets the colour of the box. Put one idea in each box.' ),
		sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Tip' ), sp( 'A tip is a small, practical thing that saves time at the counter. Optional heading above, then one or two sentences.' ) ),
		sb( 'healthray/note', array( 'kind' => 'note', 'title' => 'Good to know' ), sp( 'A note adds context that is not a rule: how something works, or what to check first.' ) ),
		sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'Watch out for this' ), sp( 'Watch out is for the mistake that costs money or time. Say what happens and what to do instead.' ) . "\n" . sli( array( 'A note can hold a list too.', 'Keep it short.' ) ) ),
		sb( 'healthray/note', array( 'kind' => 'example', 'title' => 'Example' ), sp( 'An example puts numbers or a real situation next to the advice. Label made-up numbers as illustrative.' ) ),

		sh( 'Tables' ),
		sp( 'The standard <strong>Table</strong> block, with five looks in the sidebar under Styles: <em>Plain table</em>, <em>numbers right</em>, and a highlighted column (first, second or last). Turn on “Header section” for the heading row.' ),
		stable( array( 'Item', 'Stock', 'Minimum', 'Order' ), array( array( 'Paracetamol 650', '48 strips', '30', '—' ), array( 'Azithromycin 500', '8 strips', '20', '12 strips' ), array( 'Pantoprazole 40', '15 strips', '20', '5 strips' ) ), 'guide', 'Plain table: plain, for words and mixed columns. Sample data.' ),
		stable( array( 'Month', 'Sales', 'Purchase', 'Margin' ), array( array( 'August', '₹4,10,000', '₹3,10,000', '24.4%' ), array( 'September', '₹4,48,000', '₹3,38,000', '24.6%' ), array( 'October (to 13th)', '₹1,86,000', '₹1,42,000', '23.7%' ) ), 'guide-num', 'Numbers right: figures line up on the right. Sample data.', array( 'Total', '₹10,44,000', '₹7,90,000', '24.3%' ) ),
		stable( array( 'Option', 'Setup', 'Yearly cost', 'Best for' ), array( array( 'Option A', 'Same day', '₹4,999', 'One counter' ), array( 'Option B', 'One week', '₹9,999', 'Two counters' ), array( 'Option C', 'By quote', 'On request', 'Chains' ) ), 'guide-hl2', 'Highlight column 2: points the eye at the column you recommend. Sample data.' ),
		stable( array( 'Batch', 'Expiry', 'Quantity', 'Days left' ), array( array( 'AZ1189', '10-11-2026', '14 strips', '28' ), array( 'SC7731', '01-12-2026', '22 strips', '49' ), array( 'ML0388', '27-11-2026', '10 strips', '45' ) ), 'guide-hl-last', 'Highlight last column: for a result such as days left. Sample data.' ),

		sh( 'A verdict' ),
		sp( 'A verdict closes a comparison in one or two lines. It is a note of its own, set apart by a dark rule, so it can follow a table, a list of options or a plain paragraph.' ),
		sb( 'healthray/note', array( 'kind' => 'verdict', 'title' => 'Weekly beats monthly' ), sp( 'A weekly check takes <strong>15 minutes</strong>; a monthly one takes an hour and still misses the batches that expire in between.' ) ),

		sh( 'Images' ),
		sb( 'image', array( 'sizeSlug' => 'large', 'linkDestination' => 'none' ), "<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url( $img ) . "\" alt=\"The stock expiry report grouped by supplier\"/><figcaption class=\"wp-element-caption\">Sample screen, dummy data. Add a caption under every product screen.</figcaption></figure>" ),

		sh( 'Calls to action' ),
		sp( 'Three kinds. The <strong>inline link</strong> sits in a line of text, the <strong>Start free panel</strong> ends a post with Start free, the plans and a demo, and the <strong>demo panel with form</strong> lets a reader book a call without leaving the page. Use one closing block per post.' ),
		sb( 'healthray/cta', array( 'kind' => 'inline', 'text' => 'Want to see this report on your own stock?', 'linkLabel' => 'Book a free demo', 'linkKey' => 'demo' ) ),
		sb( 'healthray/cta', array( 'kind' => 'form', 'title' => 'See it with your own stock', 'text' => 'A 30-minute demo using items from your shelf.' ) ),
		sb( 'healthray/cta', array( 'kind' => 'panel', 'title' => 'Start free, and see what the plans add', 'text' => 'The Starter plan is free to use, with limits. There is no trial: it stays free, and you move up only if you need more. The pricing page always shows the current limits.' ) ),

		sb( 'healthray/faq', array( 'heading' => 'Asked about this' ),
			sb( 'healthray/faq-item', array( 'question' => 'How long should a post be?' ), sp( 'As long as the topic needs. A 2,000-word post uses the same blocks as a short one; the read time is counted from the text.' ) ) . "\n" .
			sb( 'healthray/faq-item', array( 'question' => 'Where do the standfirst, topic and cover go?' ), sp( 'In the “Article settings” panel in the post sidebar. They are not part of the body.' ) ) . "\n" .
			sb( 'healthray/faq-item', array( 'question' => 'Does the FAQ block help search?' ), sp( 'Yes: the questions are also sent to search engines as FAQ data, so only write answers you are sure of.' ) ) ),
	)
);

/* ---- write the post --------------------------------------------------------------------- */

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_sample', 'meta_value' => 'components' ) );
$data     = array(
	'post_type'    => 'post',
	'post_status'  => 'publish',
	'post_title'   => 'Blog components: every block in one sample post',
	'post_name'    => 'blog-components-sample',
	'post_content' => $content,
	'post_date'    => '2026-10-20 10:00:00',
);
if ( $existing ) {
	$data['ID'] = (int) $existing[0];
	$id         = wp_update_post( wp_slash( $data ), true );
} else {
	$id = wp_insert_post( wp_slash( $data ), true );
}
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'FAILED: ' . $id->get_error_message() . PHP_EOL );
	exit( 1 );
}

update_post_meta( $id, '_rx_sample', 'components' );
update_post_meta( $id, 'topic', 'buying' );
update_post_meta( $id, 'dek', 'Every component we use in a post, in one place: copy a block into your own post and change the words. Sample text only.' );
update_post_meta( $id, 'updated', '2026-10-20' );
update_post_meta( $id, 'cover_screen', 'v2-03-expiry' );
update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', '1' );
update_post_meta( $id, '_yoast_wpseo_title', 'Blog components (sample) %%sep%% %%sitename%%' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'Sample post with every component: In short, notes, tables, verdict and calls to action.' );
// Featured image: a sample picture (a product screen), so the wide photo under the hero shows.
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$att = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_sample_img', 'meta_value' => '1' ) );
$att_id = $att ? (int) $att[0] : 0;
if ( ! $att_id ) {
	$tmp = wp_tempnam( 'sample-feature.jpg' );
	copy( dirname( __DIR__ ) . '/themes/pharmacy/assets/img/v2-03-expiry@2x.jpg', $tmp );
	$att_id = media_handle_sideload( array( 'name' => 'sample-feature-image.jpg', 'tmp_name' => $tmp ), $id, 'Sample featured image' );
	if ( ! is_wp_error( $att_id ) ) {
		update_post_meta( $att_id, '_rx_sample_img', '1' );
		update_post_meta( $att_id, '_wp_attachment_image_alt', 'The stock expiry report grouped by supplier' );
		wp_update_post( array( 'ID' => $att_id, 'post_excerpt' => 'Sample featured image (a product screen with dummy data). Replace it in the post sidebar.' ) );
	}
}
if ( $att_id && ! is_wp_error( $att_id ) ) {
	set_post_thumbnail( $id, $att_id );
}
delete_post_meta( $id, '_rx_read_mins' );
wp_update_post( array( 'ID' => $id ) ); // Recounts the read time on save.

echo ( $existing ? 'Updated' : 'Created' ) . " post #{$id}: " . get_permalink( $id ) . PHP_EOL;
echo 'Edit: ' . admin_url( "post.php?post={$id}&action=edit" ) . PHP_EOL;
echo 'Read time: ' . rx_blog_read_mins( $id ) . ' min' . PHP_EOL;
