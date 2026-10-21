<?php
/**
 * Creates or updates the guide "Checking a distributor bill before you enter it: six things to look at"
 * (slug check-distributor-bill-before-entry) as a block-editor post with its feature image.
 *
 * A plain guide on purpose: the short version, notes, a tip and two tables, and a closing block. No steps, no
 * comparison, no boxes. Nothing here is a product claim beyond the Diff % column of the purchase screen
 * (margin against the last purchase or the purchase average), and the numbers in the second table are
 * illustrative and labelled as such. No tax rates and no scheduled-drug rules.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-distributor-bill.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once __DIR__ . '/lib/guide-blocks.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** Uploads the feature image once (found again by meta `_rx_src`) and replaces the file on re-runs. */
function rx_db_attachment( string $file, string $key, string $title, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $key ) );
	$id    = $found ? (int) $found[0] : 0;
	if ( ! $id ) {
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $title );
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, 'Upload failed: ' . $id->get_error_message() . PHP_EOL );
			return 0;
		}
		update_post_meta( $id, '_rx_src', $key );
	} else {
		$path = get_attached_file( $id );
		copy( $file, $path );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
	return (int) $id;
}

$feature = rx_db_attachment( __DIR__ . '/content/images/distributor-bill-feature.png', 'distributor-bill-feature', 'Checking a distributor bill', 'A sample distributor bill with three checks: batch and expiry, quantity, and rate against the last purchase' );
if ( ! $feature ) {
	exit( 1 );
}

$content = implode(
	"\n\n",
	array(
		sb( 'healthray/takeaways', array(), sli( array(
			'A distributor bill is the one place a wrong <strong>rate, batch or quantity</strong> can enter your stock without anyone noticing.',
			'Check six things before you enter it. It takes about a minute a bill once it is a habit.',
			'The check that pays most is the <strong>rate against your last purchase</strong>, because a small rise repeats on every strip.',
		) ) ),

		sp( 'Most stock errors in a medical store do not start at the counter. They start when a delivery is entered: a batch typed with one digit wrong, a free quantity left out, a rate that went up a little. The bill is already on your desk, so it is the cheapest place to catch them.' ),
		sp( 'This is the order we would check a bill in. It works with paper and with software.' ),

		sh( 'Before you open the bill' ),
		sp( 'Keep the delivery and the bill together. Count the boxes first, then the bill. If the count is off, say so to the delivery person while they are still at the counter, not an hour later.' ),
		sb( 'healthray/note', array( 'kind' => 'note', 'title' => 'Why the order matters' ), sp( 'Checking quantity before price means a short delivery is found while it can still be fixed on the spot. A rate difference can wait until the next call to the distributor.' ) ),

		sh( 'Six things to check' ),
		sp( 'Go down the bill line by line. Most lines pass every check in a second; the habit is what catches the one that does not.' ),
		stable(
			array( 'Check', 'What to look at', 'If it is wrong' ),
			array(
				array( 'Batch and expiry', 'Match the bill to the strip in your hand, character by character.', 'Return the line or have the bill corrected. Do not enter a guessed batch.' ),
				array( 'Quantity', 'Count strips or boxes against the bill, including any free quantity.', 'Write the short quantity on the bill and ask for a credit note.' ),
				array( 'Free quantity and scheme', 'Is the scheme on the bill the one you were promised?', 'Ask before you enter it, while the salesperson remembers the offer.' ),
				array( 'Rate', 'Compare with your last purchase of the same item.', 'Ask the distributor why it changed. See the example below.' ),
				array( 'MRP', 'Compare with the strip, and with the last batch of the same item.', 'A changed MRP changes your margin. Note it before you price the stock.' ),
				array( 'Expiry date', 'Short-dated stock is easy to miss on a busy morning.', 'Set it aside and ask about returns before you shelve it.' ),
			),
			'guide',
			'Six checks for every line of a distributor bill.'
		),
		sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Check short-dated lines first' ), sp( 'Look at the expiry column before anything else. A batch that expires soon is the one you can still return, and the return window is shorter than most people think.' ) ),

		sh( 'The rate check, with numbers' ),
		sp( 'A small rate rise looks harmless on one strip. It is the repeat that matters: the same item, bought every week, at a slightly higher rate, quietly takes margin from the shelf.' ),
		stable(
			array( 'Item', 'Last purchase', 'This bill', 'Change' ),
			array(
				array( 'Pantoprazole 40, strip of 10', '₹92.00', '₹95.50', '+3.8%' ),
				array( 'Metformin 500, strip of 10', '₹18.40', '₹18.40', '0%' ),
				array( 'Cetirizine 10, strip of 10', '₹14.20', '₹15.80', '+11.3%' ),
				array( 'ORS sachet, box of 20', '₹126.00', '₹124.00', '−1.6%' ),
			),
			'guide-hl-last',
			'Illustrative numbers, not a real bill. The last column is the one to read first.'
		),
		sp( 'Here the cetirizine rate rose by more than a tenth while everything else stayed close. That is the line to ask about; the rest of the bill is fine. The purchase screen in the software shows a Diff % column for exactly this: your margin against the last purchase or the purchase average.' ),
		sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'Do not enter first and ask later' ), sp( 'Once a bill is entered, the old rate is no longer in front of you and the stock is on the shelf. Query a rate difference before you post the bill, or you will be arguing from memory.' ) ),

		sh( 'Making it a habit' ),
		sp( 'Three small rules keep the check from becoming a chore:' ),
		sli( array(
			'One person enters bills, and a second person looks at any line marked with a question.',
			'Write the query on the bill itself, with the date, so the next call to the distributor starts from facts.',
			'Do the check at a fixed time of day, not between customers.',
		) ),
		sp( 'If you do this for a month, you will know which distributors are precise and which need watching, and that changes whom you order from.' ),

		sb( 'healthray/cta', array( 'kind' => 'panel', 'title' => 'Start free, and see what the plans add', 'text' => 'The Starter plan is free to use, with limits. There is no trial: it stays free, and you move up only if you need more. The pricing page always shows the current limits.' ) ),
	)
);

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'name' => 'check-distributor-bill-before-entry' ) );
$data     = array(
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'post_title'    => 'Checking a distributor bill before you enter it: six things to look at',
	'post_name'     => 'check-distributor-bill-before-entry',
	'post_content'  => $content,
	'post_date'     => '2026-10-21 10:00:00',
	'post_date_gmt' => get_gmt_from_date( '2026-10-21 10:00:00' ),
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

update_post_meta( $id, 'topic', 'purchase' );
update_post_meta( $id, 'dek', 'Six checks to make on every distributor bill before it goes into stock, in the order that catches the most.' );
update_post_meta( $id, 'updated', '2026-10-21' );
update_post_meta( $id, '_yoast_wpseo_title', 'Checking a distributor bill: six things to look at %%sep%% %%sitename%%' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'Six checks to make on every distributor bill before you enter it: batch, quantity, scheme, rate, MRP and expiry, and why the rate check pays most.' );
delete_post_meta( $id, '_rx_read_mins' );
set_post_thumbnail( $id, $feature );
wp_update_post( array( 'ID' => $id ) );

echo ( $existing ? 'Updated' : 'Created' ) . " post #{$id}: " . get_permalink( $id ) . PHP_EOL;
echo 'Edit: ' . admin_url( "post.php?post={$id}&action=edit" ) . PHP_EOL;
echo 'Read time: ' . rx_blog_read_mins( $id ) . ' min' . PHP_EOL;
