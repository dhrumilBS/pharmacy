<?php
/**
 * Template Name: Solution
 *
 * A solution page: retail medical store, pharmacy chain, hospital pharmacy, opening a new pharmacy.
 * One layout for all four; the words are in inc/solutions-data.php, picked by the page's key (`_rx_key`).
 *
 *   hero (crumbs, h1, lead, two buttons, a product screen)
 *   → "What it does": six items, each with a link to its feature page and a live plan label where the feature is plan-gated
 *   → "How it starts": four steps
 *   → "What it costs": live pricing text and links
 *   → questions (with FAQPage data) → the closing band from the footer.
 *
 * Plans and prices are never written here; they come from the pricing shortcodes. Run import/solutions-setup.php
 * to create the pages.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built.

$rx_sl_key  = rx_page_key();
$rx_sl_all  = rx_solutions_data();
$rx_sl      = $rx_sl_all[ $rx_sl_key ] ?? null;
$rx_sl_demo = rx_url( 'demo' );
$rx_sl_free = trim( (string) rx_setting( 'app_signup' ) );
$rx_sl_d    = rx_pricing_data();

if ( null === $rx_sl ) {
	get_header();
	echo '<main id="main" class="section"><div class="container"><h1>' . esc_html( get_the_title() ) . '</h1></div></main>';
	get_footer();
	return;
}

// The questions, printed once so the section can be skipped and the schema built from the same rows.
$rx_sl_faq = '';
if ( ! empty( $rx_sl['faq'] ) ) {
	$rx_sl_rows = array();
	foreach ( $rx_sl['faq'] as $rx_sl_q ) {
		$rx_sl_rows[] = array( 'q' => $rx_sl_q[0], 'a_html' => '<p>' . $rx_sl_q[1] . '</p>' );
	}
	ob_start();
	get_template_part(
		'parts/faq-list',
		null,
		array(
			'items' => $rx_sl_rows,
			'ids'   => array(
				'answer' => 'slq%d',
				'button' => 'slq%db',
				'start'  => 0,
			),
		)
	);
	$rx_sl_faq = trim( (string) ob_get_clean() );
}

get_header();
?>
<main id="main" class="sl">

<header class="sl-hero">
  <div class="container sl-hero-grid">
    <div class="sl-hero-copy">
      <ol class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmacy' ); ?>">
        <li><a href="<?php echo esc_url( rx_url( 'home' ) ); ?>"><?php esc_html_e( 'Home', 'pharmacy' ); ?></a></li>
        <li aria-current="page"><?php echo esc_html( $rx_sl['crumb'] ); ?></li>
      </ol>
      <h1><?php echo esc_html( $rx_sl['h1'] ); ?></h1>
      <p class="sl-lead"><?php echo esc_html( $rx_sl['lead'] ); ?></p>
      <p class="hero-ctas sl-ctas">
        <a class="btn btn-primary" href="<?php echo esc_url( $rx_sl_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        <?php if ( '' !== $rx_sl_free ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_sl_free ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
      </p>
    </div>
    <figure class="shot sl-shot">
      <?php echo rx_svg_img( $rx_sl['screen'], 1440, 900, sprintf( /* translators: %s: description of the product screen. */ __( 'Sample screen: %s', 'pharmacy' ), $rx_sl['screen_alt'] ), 'loading="eager" fetchpriority="high"' ); ?>
      <figcaption class="sample-tag"><?php esc_html_e( 'Sample screen, dummy data', 'pharmacy' ); ?></figcaption>
    </figure>
  </div>
</header>

<section class="section sl-items" aria-labelledby="sl-items-h">
  <div class="container sl-split">
    <div class="sl-head">
      <h2 id="sl-items-h"><?php echo esc_html( $rx_sl['items_title'] ); ?></h2>
      <?php if ( '' !== $rx_sl['items_intro'] ) : ?><p><?php echo esc_html( $rx_sl['items_intro'] ); ?></p><?php endif; ?>
    </div>
    <ul class="sl-list">
      <?php
		foreach ( $rx_sl['items'] as $rx_sl_item ) :
			$rx_sl_plan = ( null !== $rx_sl_d && '' !== $rx_sl_item[3] ) ? rx_feature_plan_label( $rx_sl_d, $rx_sl_item[3] ) : null;
			$rx_sl_link = '' !== $rx_sl_item[2] ? rx_url( $rx_sl_item[2] ) : '';
			?>
      <li>
        <h3><?php echo esc_html( $rx_sl_item[0] ); ?></h3>
        <p><?php echo esc_html( $rx_sl_item[1] ); ?></p>
        <?php if ( $rx_sl_plan || '' !== $rx_sl_link ) : ?>
        <p class="sl-meta">
          <?php if ( $rx_sl_plan ) : ?><span><?php echo esc_html( $rx_sl_plan ); ?></span><?php endif; ?>
          <?php if ( '' !== $rx_sl_link ) : ?><a href="<?php echo esc_url( $rx_sl_link ); ?>"><?php esc_html_e( 'More', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?>
        </p>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section sl-flow" aria-labelledby="sl-flow-h">
  <div class="container">
    <h2 id="sl-flow-h"><?php echo esc_html( $rx_sl['flow_title'] ); ?></h2>
    <ol class="sl-steps">
      <?php foreach ( $rx_sl['flow'] as $rx_sl_i => $rx_sl_step ) : ?>
      <li>
        <span class="sl-n" aria-hidden="true"><?php echo (int) $rx_sl_i + 1; ?></span>
        <h3><?php echo esc_html( $rx_sl_step[0] ); ?></h3>
        <p><?php echo esc_html( $rx_sl_step[1] ); ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section sl-plan" aria-labelledby="sl-plan-h">
  <div class="container sl-split">
    <div class="sl-head"><h2 id="sl-plan-h"><?php echo esc_html( $rx_sl['plan_title'] ); ?></h2></div>
    <div class="sl-plan-body">
      <?php echo rx_html( $rx_sl['plan_html'] ); ?>
      <p class="sl-links">
        <?php foreach ( $rx_sl['plan_links'] as $rx_sl_l ) : ?>
        <a href="<?php echo esc_url( rx_url( $rx_sl_l[1] ) ); ?>"><?php echo esc_html( $rx_sl_l[0] ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        <?php endforeach; ?>
      </p>
    </div>
  </div>
</section>

<?php if ( '' !== $rx_sl_faq ) : ?>
<section class="section sl-faq" aria-labelledby="sl-faq-h">
  <div class="container sl-split">
    <div class="sl-head"><h2 id="sl-faq-h"><?php esc_html_e( 'Questions', 'pharmacy' ); ?></h2></div>
    <div><?php echo $rx_sl_faq; ?></div>
  </div>
</section>
<?php endif; ?>

</main>
<?php
get_footer(
	null,
	array(
		'cta' => array(
			'title' => $rx_sl['cta_title'],
			'text'  => $rx_sl['cta_text'],
		),
	)
);
