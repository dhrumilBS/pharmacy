<?php
/**
 * Creates or updates the guide "Hospital pharmacy software: one stock for in-patient and out-patient care"
 * (slug hospital-pharmacy-software-ipd-opd) as a block-editor post, with its two images.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\blog-hospital-pharmacy.php
 *
 * Written from the idea of the Healthray article "PMS for hospital pharmacies: bridging in-patient and
 * out-patient care", in this site's voice and rules: no unverified product figures (the source's savings and
 * waiting-time numbers are left out), no country names, calm about compliance, illustrative numbers labelled.
 *
 * Images (made for this guide, sources in content/images/):
 * - hospital-pharmacy-feature.png  1600x1200 (4:3). The featured image, shown beside the headline.
 * - hospital-pharmacy-flow.png     2400x1040. The two-flows diagram inside the article.
 *
 * Re-runnable: the post and the two attachments are found again and updated. Local only (lib/guard.php).
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

/* ---- images ---------------------------------------------------------------------------- */

/**
 * Uploads a file to the media library once (found again by meta `_rx_src`).
 *
 * @param string $file    Absolute path.
 * @param string $key     Stable key.
 * @param string $title   Attachment title.
 * @param string $alt     Alt text.
 * @param string $caption Caption.
 * @return int Attachment id (0 on failure).
 */
function rx_hp_attachment( string $file, string $key, string $title, string $alt, string $caption ): int {
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
		// Replace the file (so a re-rendered image replaces the old one) and rebuild the sizes.
		$path = get_attached_file( $id );
		copy( $file, $path );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_excerpt' => $caption ) );
	return (int) $id;
}

$img_dir = __DIR__ . '/content/images/';
$feature = rx_hp_attachment( $img_dir . 'hospital-pharmacy-feature.png', 'hospital-pharmacy-feature', 'Hospital pharmacy: one stock, two counters', 'A ward and an OPD counter both drawing from one shared stock', '' );
$flow    = rx_hp_attachment( $img_dir . 'hospital-pharmacy-flow.png', 'hospital-pharmacy-flow', 'Hospital pharmacy: two flows, one stock', 'Two rows of four steps, in-patient ward orders and out-patient counter prescriptions, both drawing from one stock list', 'Illustration of the two flows. It is not a screen of the software.' );
if ( ! $feature || ! $flow ) {
	exit( 1 );
}
$flow_url = (string) wp_get_attachment_url( $flow );

/* ---- the guide ------------------------------------------------------------------------- */

$content = implode(
	"\n\n",
	array(
		sb( 'healthray/takeaways', array(), sli( array(
			'A hospital pharmacy serves two different customers: <strong>wards</strong> that need the right dose at the right time, and an <strong>OPD counter</strong> that needs a short queue and an accurate bill.',
			'Most daily trouble comes from the two sides keeping <strong>separate stock lists and separate bills</strong>, not from the work itself.',
			'Look for <strong>one stock, one patient record and one bill</strong> shared by both sides, and test it with a real ward order and a real OPD queue.',
			'Move over one counter or one ward at a time, and keep billing as it is today until the first week has passed.',
		), true ) ),

		sp( 'A hospital pharmacy has two jobs under one roof. On the ward side, a nurse needs the right medicine for a named patient at a set time. At the OPD counter, a patient with a prescription wants it filled quickly and billed correctly, with a queue behind them.' ),
		sp( 'Each job is manageable on its own. The trouble starts when they run on separate lists: the ward sees a strip in stock, the counter finds the shelf empty, and the bill for a ward issue is entered by hand hours later. This article sets out where those gaps appear, what to check in any pharmacy software, and how to move over without stopping dispensing.' ),

		sh( 'Two jobs, one pharmacy' ),
		sp( 'In-patient (IPD) and out-patient (OPD) work look alike from outside, because both end with a medicine handed over. Inside, they differ in almost everything that matters to the person doing the job.' ),
		sb( 'healthray/compare', array( 'title' => 'In-patient and out-patient dispensing', 'aName' => 'In-patient (IPD)', 'aTag' => 'Ward', 'bName' => 'Out-patient (OPD)', 'bTag' => 'Counter', 'winner' => '', 'verdict' => 'The two flows need different screens, but they should <strong>draw from the same stock</strong> and write to the same patient record.' ),
			sb( 'healthray/compare-row', array( 'label' => 'Who asks', 'a' => 'A nurse or ward, for a named patient', 'b' => 'The patient or a family member, with a prescription' ) ) . "\n" .
			sb( 'healthray/compare-row', array( 'label' => 'What matters most', 'a' => 'The right dose at the right time', 'b' => 'A short wait and an accurate bill' ) ) . "\n" .
			sb( 'healthray/compare-row', array( 'label' => 'How orders arrive', 'a' => 'Ward orders, often together by dose time', 'b' => 'One prescription at a time, in a queue' ) ) . "\n" .
			sb( 'healthray/compare-row', array( 'label' => 'What a delay costs', 'a' => 'A late or missed dose', 'b' => 'A longer queue and an unhappy patient' ) ) . "\n" .
			sb( 'healthray/compare-row', array( 'label' => 'How it is billed', 'a' => 'To the patient’s hospital bill', 'b' => 'At the counter: cash, card, UPI or credit' ) ) ),
		simg( $flow, $flow_url, 'Two rows of four steps, in-patient ward orders and out-patient counter prescriptions, both drawing from one stock list', 'Illustration of the two flows. It is not a screen of the software.' ),

		sh( 'Where the gaps show up' ),
		sp( 'When the two sides run on separate systems, the same few gaps appear in almost every hospital pharmacy. None of them is dramatic on its own. Together they cost time every shift.' ),
		stable( array( 'Gap', 'What happens at the counter or ward', 'Why it matters' ), array(
			array( 'Two stock lists', 'The ward sees a medicine in stock; the OPD counter finds the shelf empty', 'Late dispensing, urgent purchases, and stock that expires unseen in the other store' ),
			array( 'Orders on paper', 'Ward orders and indents are copied by hand', 'Copying mistakes and delays, most of all at shift change' ),
			array( 'Bills entered later', 'Ward issues are charged after the fact', 'Medicines given but never billed' ),
			array( 'A slow OPD queue', 'Each prescription is checked and typed in one by one', 'Long waits at peak hours' ),
			array( 'No single record', 'The pharmacy, the ward and billing each hold part of the story', 'Hard to answer “what was this patient given?”' ),
			array( 'A scattered trail', 'Who issued which batch is written in several places', 'Time lost when a doctor or an auditor asks' ),
		), 'guide', 'Typical gaps when in-patient and out-patient dispensing run separately.' ),
		sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'Watch out for two stock lists' ), sp( 'If the ward store and the OPD counter each keep their own list, every transfer between them is a chance for the numbers to drift. Ask any vendor how stock moves between stores, and ask to see it happen.' ) ),

		sh( 'The in-patient side: what to ask for' ),
		sp( 'On the ward, accuracy matters more than speed. The pharmacist is the last check between an order and a patient, so the software should make that check easy rather than skip it.' ),
		sb( 'healthray/checklist', array( 'title' => 'In-patient pharmacy checklist' ), sli( array(
			'Orders are placed per patient and per dose time.',
			'The pharmacist can check an order against the patient’s record before issue.',
			'Medicines are issued dose by dose or by ward kit, with the batch recorded.',
			'The batch that expires first is picked first (FEFO).',
			'Unused strips can be returned to stock, with a record of who returned them.',
			'Every issue is charged to the patient’s bill at the time it is made.',
		) ) ),
		sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Ask for one order, start to finish' ), sp( 'Do not accept a list of features. Ask to see a single ward order go from request to bill, with no re-typing on the way.' ) ),

		sh( 'The out-patient side: speed without mistakes' ),
		sp( 'The OPD counter is a queue. What a patient remembers is how long they waited and whether the bill was right, so the goal is the same steps every time, in the same order, for every prescription.' ),
		sb( 'healthray/rule', array( 'label' => 'Rule of thumb' ), sp( 'One prescription, one token, one bill.' ) ),
		sp( 'Small delays add up faster than they seem. A minute lost on each prescription is easy to ignore until you multiply it by a day’s queue.' ),
		sb( 'healthray/sum', array( 'title' => 'What one extra minute per prescription costs', 'resultLabel' => 'Counter time lost a day', 'resultValue' => '400 minutes', 'note' => 'Illustrative numbers: about 6 hours 40 minutes. Use your own prescription count.' ),
			sb( 'healthray/sum-line', array( 'label' => 'Prescriptions at the OPD counter in a day', 'value' => '400', 'op' => 'none' ) ) . "\n" .
			sb( 'healthray/sum-line', array( 'label' => 'Extra minutes lost on each one', 'value' => '1', 'op' => '×' ) ) ),

		sh( 'One stock, two views' ),
		sp( 'The ward store and the counter can be separate places and still be one stock. Each shows its own balance, and a transfer between them is recorded once, so neither side is surprised by an empty shelf.' ),
		sb( 'healthray/cta', array( 'kind' => 'feature', 'text' => 'Stock alerts and a short book show what has fallen below its minimum, so a refill is raised before the ward or the counter runs out.', 'featureKey' => 'inventory' ) ),
		sp( 'Set a minimum for each store, not one for the whole hospital. The ward and the counter use medicines at different speeds, and one shared number hides that.' ),

		sh( 'Checking a system before you buy' ),
		sp( 'Demonstrations are easy to polish. These questions are harder to answer with a prepared script, and the answers tell you what the daily work will be like.' ),
		stable( array( 'Ask', 'Why it matters', 'A good answer' ), array(
			array( 'Show me a ward order from request to bill.', 'It tests the in-patient flow end to end', 'You see the order, the check, the batch issued and the charge, with no re-typing' ),
			array( 'Show me ten OPD prescriptions in a row.', 'It tests speed at the counter', 'The same steps every time, with a token or queue number' ),
			array( 'Where does stock live?', 'One list or two', 'One stock, with each store’s balance shown' ),
			array( 'Who can see and change what?', 'Wards, the counter and billing do different work', 'Separate roles, so each person sees only what they need' ),
			array( 'How do I get my data out?', 'Leaving should never cost you your records', 'A clear export, in a format you can open elsewhere' ),
		), 'guide', 'Five questions for any hospital pharmacy software demonstration.' ),
		sb( 'healthray/cta', array( 'kind' => 'inline', 'text' => 'Want to see a ward order and an OPD queue on one stock?', 'linkLabel' => 'Book a free demo', 'linkKey' => 'demo' ) ),

		sh( 'A rollout in four steps' ),
		sp( 'Switching a hospital pharmacy is not a weekend job. The calmer way is to move one piece at a time and keep the old way available until the new one has earned trust.' ),
		sb( 'healthray/steps', array(),
			sb( 'healthray/step', array( 'title' => 'Load items and opening stock', 'when' => 'Week 1' ), sp( 'Bring in your item list and stock with batch and expiry, store by store. Check a few items against the shelf.' ) ) . "\n" .
			sb( 'healthray/step', array( 'title' => 'Run one OPD counter alongside the old system', 'when' => 'Weeks 1 and 2' ), sp( 'Bill every prescription in both. Compare the totals at the end of each day.' ) ) . "\n" .
			sb( 'healthray/step', array( 'title' => 'Add one ward', 'when' => 'Weeks 2 and 3' ), sp( 'Start with a ward whose staff are keen to try it. Fix what the first week shows before adding another.' ) ) . "\n" .
			sb( 'healthray/step', array( 'title' => 'Move the remaining counters and wards', 'when' => 'Week 4 onwards' ), sp( 'Switch billing last, once stock balances agree between the two systems.' ) ) ),

		sb( 'healthray/cta', array( 'kind' => 'panel', 'title' => 'See it with your own stock', 'text' => 'A 30-minute demo using items from your pharmacy. No payment, no obligation.' ) ),

		sb( 'healthray/faq', array( 'heading' => 'Asked about this' ),
			sb( 'healthray/faq-item', array( 'question' => 'Do the wards and the OPD counter need separate stock?' ), sp( 'They can keep separate stores, but both should be part of one stock list. That way a transfer is recorded once and each side can see the other’s balance.' ) ) . "\n" .
			sb( 'healthray/faq-item', array( 'question' => 'Can we keep our current software while we test?' ), sp( 'Yes, and it is the safer way. Run the new system on one counter or one ward first and compare the two for a few weeks.' ) ) . "\n" .
			sb( 'healthray/faq-item', array( 'question' => 'Does software replace a pharmacist’s or nurse’s checks?' ), sp( 'No. Pharmacists and clinicians stay responsible for the checks. Good software keeps the record and removes re-typing, so those checks are easier to do.' ) ) . "\n" .
			sb( 'healthray/faq-item', array( 'question' => 'How long does a rollout take?' ), sp( 'It depends on the number of stores, wards and counters. Plan on a few weeks, and start with one counter so you learn on a small part.' ) ) ),
	)
);

/* ---- write the post -------------------------------------------------------------------- */

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'name' => 'hospital-pharmacy-software-ipd-opd' ) );
$data     = array(
	'post_type'    => 'post',
	'post_status'  => 'publish',
	'post_title'   => 'Hospital pharmacy software: one stock for in-patient and out-patient care',
	'post_name'    => 'hospital-pharmacy-software-ipd-opd',
	'post_content' => $content,
	'post_date'    => '2026-10-20 12:00:00', // Kept in the past so the post publishes at once whatever the site time zone.
	'post_date_gmt' => get_gmt_from_date( '2026-10-20 12:00:00' ),
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

update_post_meta( $id, 'topic', 'buying' );
update_post_meta( $id, 'dek', 'A hospital pharmacy runs two jobs at once: ward orders that must be right to the dose, and a counter that must be quick. What to look for so both draw from one stock.' );
update_post_meta( $id, 'updated', '2026-10-21' );
update_post_meta( $id, 'cover_screen', 'v2-07-hospital' );
update_post_meta( $id, '_yoast_wpseo_title', 'Hospital pharmacy software: one stock for IPD and OPD %%sep%% %%sitename%%' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'How a hospital pharmacy can serve wards and the OPD counter from one stock: where the gaps appear, what to ask any vendor, and a four-step rollout.' );
delete_post_meta( $id, '_rx_read_mins' );
set_post_thumbnail( $id, $feature );
wp_update_post( array( 'ID' => $id ) ); // Recounts the read time on save.

echo ( $existing ? 'Updated' : 'Created' ) . " post #{$id}: " . get_permalink( $id ) . PHP_EOL;
echo 'Edit: ' . admin_url( "post.php?post={$id}&action=edit" ) . PHP_EOL;
echo 'Read time: ' . rx_blog_read_mins( $id ) . ' min' . PHP_EOL;
