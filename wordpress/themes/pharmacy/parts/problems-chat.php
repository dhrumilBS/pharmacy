<?php
/**
 * "Sound familiar?" as a message inbox (home #problems). Port of
 * partials/problems-chat.php. Left: the messages owners typically send
 * (WAI-ARIA tabs, handled by the generic tabs block in site.js). Right: the
 * conversation, with the owner's message, a short typing indicator, then the
 * reply with what changes. Roles only, no invented names: an illustrative
 * conversation, not testimonials.
 *
 * $args:
 * - items      array  Messages, the Home `problems` repeater rows:
 *                     [ icon, topic, who, time, quote, why, fix ]. `icon` is a
 *                     sprite symbol id such as 'i-alert'. Rows without a topic
 *                     or quote are skipped; nothing is printed when none is left.
 * - head       array  Optional section heading, the Home `problems_head` group:
 *                     [ eyebrow, title, intro ]. Missing or null keys use today's
 *                     text; '' hides the element.
 * - note       string Optional tag after the intro ("Illustrative conversation").
 *                     Missing or null: today's text; '' hides it.
 * - cta        string Optional "See this on a demo call" target. Default '#demo'.
 * - reply_from string Optional sender of the replies. Default: the Site Title,
 *                     or "Us" when it is empty.
 *
 * Copy from SCF goes through rx_text(), so shortcodes work. The tab list's
 * label, "What changes", "read" and the call-to-action are translatable.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / esc_attr() / esc_url() where they are built.

$rx_pc_args = isset( $args ) && is_array( $args ) ? $args : array();

$rx_pc_items = array();
foreach ( isset( $rx_pc_args['items'] ) && is_array( $rx_pc_args['items'] ) ? $rx_pc_args['items'] : array() as $rx_pc_row ) {
	if ( ! is_array( $rx_pc_row ) ) {
		continue;
	}
	$rx_pc_item = array( 'icon' => esc_attr( is_string( $rx_pc_row['icon'] ?? null ) ? $rx_pc_row['icon'] : '' ) );
	foreach ( array( 'topic', 'who', 'time', 'quote', 'why', 'fix' ) as $rx_pc_key ) {
		$rx_pc_item[ $rx_pc_key ] = trim( rx_text( $rx_pc_row[ $rx_pc_key ] ?? '' ) );
	}
	if ( '' === $rx_pc_item['topic'] || '' === $rx_pc_item['quote'] ) {
		continue;
	}
	$rx_pc_item['n'] = count( $rx_pc_items ) + 1; // Tab number, from 1.
	$rx_pc_items[]   = $rx_pc_item;
}
if ( ! $rx_pc_items ) {
	return;
}

$rx_pc_head    = isset( $rx_pc_args['head'] ) && is_array( $rx_pc_args['head'] ) ? $rx_pc_args['head'] : array();
$rx_pc_eyebrow = trim( rx_text( $rx_pc_head['eyebrow'] ?? __( 'Sound familiar?', 'pharmacy' ) ) );
$rx_pc_title   = trim( rx_text( $rx_pc_head['title'] ?? __( 'Six messages every pharmacy owner has typed. Here’s our reply.', 'pharmacy' ) ) );
$rx_pc_intro   = trim( rx_text( $rx_pc_head['intro'] ?? __( 'Pick a message to see what changes.', 'pharmacy' ) ) );
$rx_pc_note    = trim( rx_text( $rx_pc_args['note'] ?? __( 'Illustrative conversation', 'pharmacy' ) ) );
$rx_pc_cta     = isset( $rx_pc_args['cta'] ) && is_string( $rx_pc_args['cta'] ) && '' !== $rx_pc_args['cta'] ? $rx_pc_args['cta'] : '#demo';
$rx_pc_from    = trim( rx_text( $rx_pc_args['reply_from'] ?? ( '' !== rx_brand() ? rx_brand() : __( 'Us', 'pharmacy' ) ) ) );
?>
<section class="section bg-soft" id="problems"<?php echo '' !== $rx_pc_title ? ' aria-labelledby="pc-title"' : ''; ?>>
  <div class="container">
    <div class="pc-head">
      <div>
        <?php if ( '' !== $rx_pc_eyebrow ) : ?><p class="eyebrow"><?php echo $rx_pc_eyebrow; ?></p><?php endif; ?>
        <?php if ( '' !== $rx_pc_title ) : ?><h2 id="pc-title"><?php echo $rx_pc_title; ?></h2><?php endif; ?>
      </div>
      <?php if ( '' !== $rx_pc_intro || '' !== $rx_pc_note ) : ?><p><?php echo $rx_pc_intro; ?><?php if ( '' !== $rx_pc_note ) : ?> <span class="pc-note"><?php echo $rx_pc_note; ?></span><?php endif; ?></p><?php endif; ?>
    </div>

    <div class="pc">
      <div class="pc-list" role="tablist" aria-label="<?php esc_attr_e( 'Common pharmacy problems', 'pharmacy' ); ?>" aria-orientation="vertical">
        <?php foreach ( $rx_pc_items as $rx_pc_item ) : ?>
          <button class="pc-tab" role="tab" id="pc-t<?php echo (int) $rx_pc_item['n']; ?>" aria-controls="pc-p<?php echo (int) $rx_pc_item['n']; ?>" aria-selected="<?php echo 1 === $rx_pc_item['n'] ? 'true' : 'false'; ?>"<?php echo 1 === $rx_pc_item['n'] ? '' : ' tabindex="-1"'; ?>>
            <span class="pc-av"><svg class="icon" aria-hidden="true"><use href="#<?php echo $rx_pc_item['icon']; ?>"/></svg></span>
            <span class="pc-meta"><b><?php echo $rx_pc_item['topic']; ?></b><small><?php echo $rx_pc_item['quote']; ?></small></span>
            <time><?php echo $rx_pc_item['time']; ?></time>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="pc-thread">
        <?php foreach ( $rx_pc_items as $rx_pc_item ) : ?>
          <div class="pc-panel" role="tabpanel" id="pc-p<?php echo (int) $rx_pc_item['n']; ?>" aria-labelledby="pc-t<?php echo (int) $rx_pc_item['n']; ?>"<?php echo 1 === $rx_pc_item['n'] ? '' : ' hidden'; ?>>
            <p class="pc-day"><span><?php echo $rx_pc_item['topic']; ?></span></p>
            <div class="bub in"><p>“<?php echo $rx_pc_item['quote']; ?>”</p><?php if ( '' !== $rx_pc_item['why'] ) : ?><p class="bub-why"><?php echo $rx_pc_item['why']; ?></p><?php endif; ?><span class="bub-meta"><?php echo $rx_pc_item['who']; ?> · <?php echo $rx_pc_item['time']; ?></span></div>
            <div class="pc-typing" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="bub out"><span class="bub-label"><?php esc_html_e( 'What changes', 'pharmacy' ); ?></span><p><?php echo $rx_pc_item['fix']; ?></p><span class="bub-meta"><?php echo $rx_pc_from; ?> · <?php esc_html_e( 'read', 'pharmacy' ); ?></span></div>
            <a class="link-arrow pc-cta" href="<?php echo esc_url( $rx_pc_cta ); ?>" data-cta="demo"><?php esc_html_e( 'See this on a demo call', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
