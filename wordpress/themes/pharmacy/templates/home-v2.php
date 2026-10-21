<?php
/**
 * Template Name: Home v2
 *
 * Version 2 of the home page, shown at /home-v2/ next to the current home page. Sections, in order:
 *
 *   hero (headline, lead, three points, two buttons, a product screen)
 *   → trusted-by strip (parts/trusted-strip)
 *   → "Built for the way you run your pharmacy": four audiences, each linking to its solution page
 *   → "What it does": twelve things, each linking to its page, with a live plan label where a feature is plan-gated
 *   → "Which setup is yours?": retail, chain and hospital side by side
 *   → "Start the same day": four steps
 *   → "How it is delivered and looked after": four columns
 *   → "Put a number on expiry loss": a link to the calculator
 *   → "From the blog": the three newest articles
 *   → questions (with FAQPage data) → "See it with your own stock" with the booking form
 *
 * The words are in inc/home2-data.php (every line is a verified product fact, no prices on the home page, no customer counts).
 * Run import/home-v2-setup.php to create the page.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built.

$rx_h2      = rx_home2_data();
$rx_h2_demo = rx_url( 'demo' );
$rx_h2_free = trim( (string) rx_setting( 'app_signup' ) );
$rx_h2_d    = rx_pricing_data();

// The questions, printed once so the schema can use the same rows.
$rx_h2_rows = array();
foreach ( $rx_h2['faq'] as $rx_h2_q ) {
	$rx_h2_rows[] = array( 'q' => $rx_h2_q[0], 'a_html' => '<p>' . $rx_h2_q[1] . '</p>' );
}
ob_start();
get_template_part(
	'parts/faq-list',
	null,
	array(
		'items' => $rx_h2_rows,
		'ids'   => array(
			'answer' => 'h2q%d',
			'button' => 'h2q%db',
			'start'  => 0,
		),
	)
);
$rx_h2_faq = trim( (string) ob_get_clean() );

$rx_h2_posts = rx_blog_posts( array( 'limit' => 3 ) );
$rx_h2_form  = function_exists( 'rx_form_html' ) ? rx_form_html( 'hero', __( 'Get a free demo call', 'pharmacy' ) ) : '';

get_header();
?>
<main id="main" class="h2">

<?php /* ---------- Hero ---------- */ ?>
<header class="h2-hero">
  <div class="container h2-hero-grid">
    <div class="h2-hero-copy">
      <h1><?php echo esc_html( $rx_h2['hero']['h1'] ); ?></h1>
      <p class="h2-lead"><?php echo rx_text( $rx_h2['hero']['lead'] ); ?></p>
      <ul class="h2-points">
        <?php foreach ( $rx_h2['hero']['bullets'] as $rx_h2_b ) : ?>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><?php echo esc_html( $rx_h2_b ); ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="hero-ctas h2-ctas">
        <a class="btn btn-primary" href="<?php echo esc_url( $rx_h2_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        <?php if ( '' !== $rx_h2_free ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_h2_free ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
      </p>
    </div>
    <figure class="shot h2-shot">
      <?php echo rx_svg_img( $rx_h2['hero']['screen'], 1440, 900, sprintf( /* translators: %s: description of the product screen. */ __( 'Sample screen: %s', 'pharmacy' ), $rx_h2['hero']['alt'] ), 'loading="eager" fetchpriority="high"' ); ?>
      <figcaption class="sample-tag"><?php esc_html_e( 'Sample screen, dummy data', 'pharmacy' ); ?></figcaption>
    </figure>
  </div>
</header>

<?php get_template_part( 'parts/trusted-strip' ); ?>

<?php /* ---------- Who it is for ---------- */ ?>
<section class="section h2-aud" aria-labelledby="h2-aud-h">
  <div class="container">
    <div class="h2-head"><h2 id="h2-aud-h"><?php echo esc_html( $rx_h2['audience']['title'] ); ?></h2><p><?php echo esc_html( $rx_h2['audience']['intro'] ); ?></p></div>
    <ul class="h2-aud-list">
      <?php foreach ( $rx_h2['audience']['items'] as $rx_h2_a ) : ?>
      <li>
        <a href="<?php echo esc_url( rx_url( $rx_h2_a[3] ) ); ?>">
          <span class="h2-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_h2_a[0] ); ?>"/></svg></span>
          <b><?php echo esc_html( $rx_h2_a[1] ); ?></b>
          <span class="h2-aud-t"><?php echo esc_html( $rx_h2_a[2] ); ?></span>
          <span class="h2-more"><?php esc_html_e( 'See how', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php /* ---------- What it does ---------- */ ?>
<section class="section h2-feat" aria-labelledby="h2-feat-h">
  <div class="container">
    <div class="h2-head"><h2 id="h2-feat-h"><?php echo esc_html( $rx_h2['features']['title'] ); ?></h2><p><?php echo esc_html( $rx_h2['features']['intro'] ); ?></p></div>
    <ul class="h2-grid">
      <?php
		foreach ( $rx_h2['features']['items'] as $rx_h2_f ) :
			$rx_h2_plan = ( null !== $rx_h2_d && '' !== $rx_h2_f[4] ) ? rx_feature_plan_label( $rx_h2_d, $rx_h2_f[4] ) : null;
			?>
      <li>
        <span class="h2-ic"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_h2_f[0] ); ?>"/></svg></span>
        <h3><a href="<?php echo esc_url( rx_url( $rx_h2_f[3] ) ); ?>"><?php echo esc_html( $rx_h2_f[1] ); ?></a></h3>
        <p><?php echo esc_html( $rx_h2_f[2] ); ?></p>
        <?php if ( $rx_h2_plan ) : ?><p class="h2-plan"><?php echo esc_html( $rx_h2_plan ); ?></p><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php /* ---------- Retail, chain, hospital ---------- */ ?>
<section class="section h2-cmp" aria-labelledby="h2-cmp-h">
  <div class="container">
    <div class="h2-head"><h2 id="h2-cmp-h"><?php echo esc_html( $rx_h2['compare']['title'] ); ?></h2><p><?php echo esc_html( $rx_h2['compare']['intro'] ); ?></p></div>
    <div class="h2-table" role="region" aria-label="<?php echo esc_attr( $rx_h2['compare']['title'] ); ?>" tabindex="0">
      <table>
        <thead>
          <tr>
            <th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Question', 'pharmacy' ); ?></span></th>
            <?php foreach ( $rx_h2['compare']['cols'] as $rx_h2_i => $rx_h2_c ) : ?>
            <th scope="col"><a href="<?php echo esc_url( rx_url( $rx_h2['compare']['links'][ $rx_h2_i ] ) ); ?>"><?php echo esc_html( $rx_h2_c ); ?></a></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $rx_h2['compare']['rows'] as $rx_h2_r ) : ?>
          <tr>
            <th scope="row"><?php echo esc_html( $rx_h2_r[0] ); ?></th>
            <?php for ( $rx_h2_n = 1; $rx_h2_n <= 3; $rx_h2_n++ ) : ?><td><?php echo rx_text( $rx_h2_r[ $rx_h2_n ] ); ?></td><?php endfor; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php /* ---------- Start the same day ---------- */ ?>
<section class="section h2-start" aria-labelledby="h2-start-h">
  <div class="container">
    <div class="h2-head"><h2 id="h2-start-h"><?php echo esc_html( $rx_h2['start']['title'] ); ?></h2></div>
    <ol class="h2-steps">
      <?php foreach ( $rx_h2['start']['steps'] as $rx_h2_i => $rx_h2_s ) : ?>
      <li><span class="h2-n" aria-hidden="true"><?php echo (int) $rx_h2_i + 1; ?></span><h3><?php echo esc_html( $rx_h2_s[0] ); ?></h3><p><?php echo esc_html( $rx_h2_s[1] ); ?></p></li>
      <?php endforeach; ?>
    </ol>
    <p class="h2-links"><a href="<?php echo esc_url( rx_url( 'pricing' ) ); ?>"><?php esc_html_e( 'See the plans', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><a href="<?php echo esc_url( rx_url( 'switch' ) ); ?>"><?php esc_html_e( 'Switching from other software', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></p>
  </div>
</section>

<?php /* ---------- Delivered and looked after ---------- */ ?>
<section class="section h2-holds" aria-labelledby="h2-holds-h">
  <div class="container">
    <div class="h2-head"><h2 id="h2-holds-h"><?php echo esc_html( $rx_h2['holds']['title'] ); ?></h2></div>
    <div class="h2-cols">
      <?php foreach ( $rx_h2['holds']['cols'] as $rx_h2_col ) : ?>
      <div>
        <h3><?php echo esc_html( $rx_h2_col[0] ); ?></h3>
        <ul><?php foreach ( $rx_h2_col[1] as $rx_h2_li ) : ?><li><?php echo esc_html( $rx_h2_li ); ?></li><?php endforeach; ?></ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ---------- A number on expiry loss ---------- */ ?>
<section class="section-tight h2-calc" aria-labelledby="h2-calc-h">
  <div class="container h2-calc-box">
    <div>
      <h2 id="h2-calc-h"><?php esc_html_e( 'How much does expiry cost your store in a year?', 'pharmacy' ); ?></h2>
      <p><?php esc_html_e( 'Most owners have never put a number on it. Two sliders and ten seconds give you a first estimate.', 'pharmacy' ); ?></p>
    </div>
    <a class="btn btn-primary" href="<?php echo esc_url( rx_url( 'calculator' ) ); ?>"><?php esc_html_e( 'Try the expiry loss calculator', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
  </div>
</section>

<?php /* ---------- From the blog ---------- */ ?>
<?php if ( $rx_h2_posts ) : ?>
<section class="section h2-blog" aria-labelledby="h2-blog-h">
  <div class="container">
    <div class="b-sec-head"><h2 id="h2-blog-h"><?php esc_html_e( 'From the blog', 'pharmacy' ); ?></h2><a href="<?php echo esc_url( rx_url( 'blog' ) ); ?>"><?php esc_html_e( 'All articles', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a></div>
    <div class="b-grid">
      <?php foreach ( $rx_h2_posts as $rx_h2_p ) { get_template_part( 'parts/blog-card', null, array( 'post' => $rx_h2_p ) ); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Questions ---------- */ ?>
<?php if ( '' !== $rx_h2_faq ) : ?>
<section class="section h2-faq" aria-labelledby="h2-faq-h">
  <div class="container h2-split">
    <div class="h2-head"><h2 id="h2-faq-h"><?php esc_html_e( 'Questions', 'pharmacy' ); ?></h2><p><?php printf( /* translators: %s: link to the FAQ page. */ esc_html__( 'More answers on the %s.', 'pharmacy' ), '<a href="' . esc_url( rx_url( 'faq' ) ) . '">' . esc_html__( 'FAQ page', 'pharmacy' ) . '</a>' ); ?></p></div>
    <div><?php echo $rx_h2_faq; ?></div>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Closing: the booking form ---------- */ ?>
<section class="section h2-close" id="demo" aria-labelledby="h2-close-h">
  <div class="container h2-split">
    <div class="h2-head">
      <h2 id="h2-close-h"><?php esc_html_e( 'See it with the medicines on your shelf', 'pharmacy' ); ?></h2>
      <p><?php esc_html_e( 'A 30-minute call, shown with your own medicines. No payment, no obligation.', 'pharmacy' ); ?></p>
      <?php if ( '' !== $rx_h2_free ) : ?><p class="h2-or"><?php esc_html_e( 'Prefer to try it yourself?', 'pharmacy' ); ?> <a href="<?php echo esc_url( $rx_h2_free ); ?>"><?php esc_html_e( 'Start free', 'pharmacy' ); ?></a></p><?php endif; ?>
    </div>
    <div class="h2-form" data-lead-guard-wrap>
      <?php if ( '' !== $rx_h2_form ) : ?>
        <?php echo $rx_h2_form; ?>
      <?php else : ?>
        <p><a class="btn btn-primary" href="<?php echo esc_url( $rx_h2_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></a></p>
      <?php endif; ?>
    </div>
  </div>
</section>

</main>
<?php
get_footer( null, array( 'hide_cta' => true ) );
