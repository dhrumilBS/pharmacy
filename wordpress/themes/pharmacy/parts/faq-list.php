<?php
/**
 * FAQ accordion list (<div class="faq-list"> with .faq-item rows). Same
 * markup as the FAQ lists on the PHP_Site's home, pricing, features, FAQ,
 * switch and blog pages; site.js toggles .faq-btn / aria-expanded.
 *
 * $args:
 * - items      array  FAQ rows as stored by SCF: [ 'q' => string, 'a_html' => string,
 *                     'confirmed' => bool ]. A missing `confirmed` counts as confirmed.
 * - ids        array  Optional id scheme, sprintf() formats taking the item number:
 *                     - answer: answer id      (default 'faq-%d')
 *                     - button: button id      (default 'faq-b%d')
 *                     - start:  first number   (default 1)
 *                     Home: faq-%d / faq-b%d from 1. Feature pages: {key}q%d / {key}q%db
 *                     from 0. Features hub fiq%d / fiq%db, pricing pq%d / pq%db, switch
 *                     sq%d / sq%db and posts bq%d / bq%db, all from 0. FAQ page: fq%d /
 *                     fq%db from 1, numbered across topics (see `next`).
 * - confirm_on string Where an unconfirmed item gets data-confirm: 'item' (default,
 *                     on .faq-item) or 'answer' (on the answer <p>, as on pricing).
 * - next       int    Optional, pass by reference ( 'next' => &$n ) to receive the
 *                     number after the last printed item, so the next list can
 *                     continue the numbering.
 *
 * The question goes through rx_text() and the answer through rx_html(), so
 * shortcodes work. Items whose question or answer renders empty are skipped
 * and use no number, which is how plan-dependent questions disappear. Nothing
 * is printed when no item is left.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_attr() where they are built.

$rx_faq_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_faq_ids  = array_merge(
	array(
		'answer' => 'faq-%d',
		'button' => 'faq-b%d',
		'start'  => 1,
	),
	isset( $rx_faq_args['ids'] ) && is_array( $rx_faq_args['ids'] ) ? $rx_faq_args['ids'] : array()
);
$rx_faq_on_answer = isset( $rx_faq_args['confirm_on'] ) && 'answer' === $rx_faq_args['confirm_on'];

$rx_faq_n    = (int) $rx_faq_ids['start'];
$rx_faq_rows = array();
foreach ( isset( $rx_faq_args['items'] ) && is_array( $rx_faq_args['items'] ) ? $rx_faq_args['items'] : array() as $rx_faq_item ) {
	if ( ! is_array( $rx_faq_item ) ) {
		continue;
	}
	$rx_faq_q = trim( rx_text( $rx_faq_item['q'] ?? '' ) );
	$rx_faq_a = trim( rx_html( $rx_faq_item['a_html'] ?? '' ) );
	if ( '' === $rx_faq_q || '' === trim( wp_strip_all_tags( $rx_faq_a ) ) ) {
		continue;
	}
	$rx_faq_rows[] = array(
		'q'      => $rx_faq_q,
		'a'      => $rx_faq_a,
		'ok'     => ! isset( $rx_faq_item['confirmed'] ) || (bool) $rx_faq_item['confirmed'],
		'answer' => esc_attr( sprintf( (string) $rx_faq_ids['answer'], $rx_faq_n ) ),
		'button' => esc_attr( sprintf( (string) $rx_faq_ids['button'], $rx_faq_n ) ),
	);
	++$rx_faq_n;
}

// Hand the next free number back when the caller passed 'next' by reference.
if ( array_key_exists( 'next', $rx_faq_args ) ) {
	$args['next'] = $rx_faq_n;
}

if ( ! $rx_faq_rows ) {
	return;
}
?>
<div class="faq-list">
  <?php foreach ( $rx_faq_rows as $rx_faq_row ) : ?>
    <div class="faq-item"<?php echo ( $rx_faq_row['ok'] || $rx_faq_on_answer ) ? '' : ' data-confirm'; ?>><h3><button class="faq-btn" aria-expanded="false" aria-controls="<?php echo $rx_faq_row['answer']; ?>" id="<?php echo $rx_faq_row['button']; ?>"><?php echo $rx_faq_row['q']; ?><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button></h3>
      <div class="faq-ans" id="<?php echo $rx_faq_row['answer']; ?>" role="region" aria-labelledby="<?php echo $rx_faq_row['button']; ?>" hidden><p<?php echo ( $rx_faq_row['ok'] || ! $rx_faq_on_answer ) ? '' : ' data-confirm'; ?>><?php echo $rx_faq_row['a']; ?></p></div></div>
  <?php endforeach; ?>
</div>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
