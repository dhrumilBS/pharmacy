<?php
/**
 * Template Name: Support
 *
 * The Support page. Port of the PHP_Site's support.php: same sections, in
 * the same order, with the same markup. Content comes from group_rx_support
 * (acf-json/group_rx_support.json) and Site_Settings:
 *
 * 1. parts/page-hero (`hero`).
 * 2. Channels: `channels` [icon, title, html] in the 4-up grid, then
 *    `note_html` (data-confirm when `note_confirmed` is off).
 * 3. Getting started (#getting-started): `start_head`, the Start free and
 *    Log in buttons (`cta_free` → Site_Settings `app_signup`, `cta_login` →
 *    `app_login`) and `start_steps` [title, html].
 * 4. Popular topics: `topics_head`, `topics` [title, text], each card linking
 *    to the Site_Settings `help_url` (a plain card when it is empty).
 *
 * Every element whose value renders empty is skipped, and so is a section
 * left with nothing to show.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built.

/** Rows of a repeater value (non-array rows dropped). */
$rx_su_rows = static fn( $value ): array => is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();

/** Plain-text sub-field of a row or group, rendered and trimmed. */
$rx_su_text = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_text( $row[ $key ] ?? '' ) ) : '';

/** HTML sub-field of a row or group, rendered and trimmed. */
$rx_su_html = static fn( $row, string $key ): string => is_array( $row ) ? trim( rx_html( $row[ $key ] ?? '' ) ) : '';

/** Whether HTML has visible text. */
$rx_su_filled = static fn( string $html ): bool => '' !== trim( wp_strip_all_tags( $html ) );

// 2. Channels.
$rx_su_channels = array();
foreach ( $rx_su_rows( rx_field( 'channels' ) ) as $rx_su_row ) {
	$rx_su_icon    = $rx_su_row['icon'] ?? '';
	$rx_su_channel = array(
		'icon'  => is_string( $rx_su_icon ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rx_su_icon ) ? $rx_su_icon : '',
		'title' => $rx_su_text( $rx_su_row, 'title' ),
		'html'  => $rx_su_html( $rx_su_row, 'html' ),
	);
	if ( '' !== $rx_su_channel['title'] || $rx_su_filled( $rx_su_channel['html'] ) ) {
		$rx_su_channels[] = $rx_su_channel;
	}
}
$rx_su_note    = trim( rx_html( rx_field( 'note_html' ) ) );
$rx_su_note_ok = false !== rx_field( 'note_confirmed' );

// 3. Getting started.
$rx_su_start   = rx_field( 'start_head' );
$rx_su_start_e = $rx_su_text( $rx_su_start, 'eyebrow' );
$rx_su_start_t = $rx_su_text( $rx_su_start, 'title' );
$rx_su_start_i = $rx_su_text( $rx_su_start, 'intro' );
$rx_su_signup  = trim( (string) rx_setting( 'app_signup' ) );
$rx_su_login   = trim( (string) rx_setting( 'app_login' ) );
$rx_su_free    = '' !== $rx_su_signup ? trim( rx_text( rx_field( 'cta_free' ) ) ) : '';
$rx_su_log_in  = '' !== $rx_su_login ? trim( rx_text( rx_field( 'cta_login' ) ) ) : '';
$rx_su_steps   = array();
foreach ( $rx_su_rows( rx_field( 'start_steps' ) ) as $rx_su_row ) {
	$rx_su_step = array(
		'title' => $rx_su_text( $rx_su_row, 'title' ),
		'html'  => $rx_su_html( $rx_su_row, 'html' ),
	);
	if ( '' !== $rx_su_step['title'] || $rx_su_filled( $rx_su_step['html'] ) ) {
		$rx_su_steps[] = $rx_su_step;
	}
}

// 4. Popular topics.
$rx_su_topics_head = rx_field( 'topics_head' );
$rx_su_topics_e    = $rx_su_text( $rx_su_topics_head, 'eyebrow' );
$rx_su_topics_t    = $rx_su_text( $rx_su_topics_head, 'title' );
$rx_su_topics_i    = $rx_su_text( $rx_su_topics_head, 'intro' );
$rx_su_help        = trim( (string) rx_setting( 'help_url' ) );
$rx_su_topics      = array();
foreach ( $rx_su_rows( rx_field( 'topics' ) ) as $rx_su_row ) {
	$rx_su_topic = array(
		'title' => $rx_su_text( $rx_su_row, 'title' ),
		'text'  => $rx_su_text( $rx_su_row, 'text' ),
	);
	if ( '' !== $rx_su_topic['title'] || '' !== $rx_su_topic['text'] ) {
		$rx_su_topics[] = $rx_su_topic;
	}
}

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => rx_field( 'hero' ) ) ); ?>

<?php if ( $rx_su_channels || $rx_su_filled( $rx_su_note ) ) : ?>
<section class="section-tight">
  <div class="container">
    <?php if ( $rx_su_channels ) : ?>
    <div class="channels four">
      <?php foreach ( $rx_su_channels as $rx_su_channel ) : ?>
      <article class="channel"><?php if ( '' !== $rx_su_channel['icon'] ) : ?><div class="sol-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_su_channel['icon'] ); ?>"/></svg></div><?php endif; ?><?php if ( '' !== $rx_su_channel['title'] ) : ?><h3><?php echo $rx_su_channel['title']; ?></h3><?php endif; ?><?php if ( $rx_su_filled( $rx_su_channel['html'] ) ) : ?><p><?php echo $rx_su_channel['html']; ?></p><?php endif; ?></article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ( $rx_su_filled( $rx_su_note ) ) : ?><p class="meta-line"<?php echo $rx_su_note_ok ? '' : ' data-confirm'; ?> style="margin-top:16px"><?php echo $rx_su_note; ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( '' !== $rx_su_start_t || $rx_su_steps ) : ?>
<section class="section-tight bg-soft" id="getting-started">
  <div class="container switch-grid">
    <div class="switch-copy">
      <?php if ( '' !== $rx_su_start_e ) : ?><span class="eyebrow"><?php echo $rx_su_start_e; ?></span><?php endif; ?>
      <?php if ( '' !== $rx_su_start_t ) : ?><h2><?php echo $rx_su_start_t; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_su_start_i ) : ?><p><?php echo $rx_su_start_i; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_su_free || '' !== $rx_su_log_in ) : ?><p style="margin-top:20px"><?php if ( '' !== $rx_su_free ) : ?><a class="btn btn-primary" href="<?php echo esc_url( $rx_su_signup ); ?>"><?php echo $rx_su_free; ?></a><?php endif; ?><?php echo '' !== $rx_su_free && '' !== $rx_su_log_in ? ' ' : ''; ?><?php if ( '' !== $rx_su_log_in ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_su_login ); ?>"><?php echo $rx_su_log_in; ?></a><?php endif; ?></p><?php endif; ?>
    </div>
    <?php if ( $rx_su_steps ) : ?>
    <ol class="steps">
      <?php foreach ( $rx_su_steps as $rx_su_step ) : ?>
      <li class="step"><div><?php if ( '' !== $rx_su_step['title'] ) : ?><h3><?php echo $rx_su_step['title']; ?></h3><?php endif; ?><?php if ( $rx_su_filled( $rx_su_step['html'] ) ) : ?><p><?php echo $rx_su_step['html']; ?></p><?php endif; ?></div></li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( '' !== $rx_su_topics_t || $rx_su_topics ) : ?>
<section class="section-tight">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_su_topics_e ) : ?><span class="eyebrow"><?php echo $rx_su_topics_e; ?></span><?php endif; ?><?php if ( '' !== $rx_su_topics_t ) : ?><h2><?php echo $rx_su_topics_t; ?></h2><?php endif; ?><?php if ( '' !== $rx_su_topics_i ) : ?><p><?php echo $rx_su_topics_i; ?></p><?php endif; ?></div>
    <?php if ( $rx_su_topics ) : ?>
    <div class="grid-3">
      <?php foreach ( $rx_su_topics as $rx_su_topic ) : ?>
      <?php if ( '' !== $rx_su_help ) : ?><a class="info-card" style="text-decoration:none" href="<?php echo esc_url( $rx_su_help ); ?>"><?php else : ?><div class="info-card"><?php endif; ?><?php if ( '' !== $rx_su_topic['title'] ) : ?><h3><?php echo $rx_su_topic['title']; ?></h3><?php endif; ?><?php if ( '' !== $rx_su_topic['text'] ) : ?><p><?php echo $rx_su_topic['text']; ?></p><?php endif; ?><?php echo '' !== $rx_su_help ? '</a>' : '</div>'; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
