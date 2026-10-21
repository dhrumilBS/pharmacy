<?php
/**
 * Template Name: Features hub
 *
 * The Features overview, parent of the eight feature pages. Port of the PHP
 * site's features/index.php, same sections and markup (feature.css "fi-"
 * rules):
 *
 * 1. page-hero (`hero`).
 * 2. CTAs (demo page, Site_Settings `app_signup`) and the `assure` lines;
 *    a line that renders empty (the free-plan line without a free plan) is
 *    skipped.
 * 3. "Start with your problem": `index` rows linking to feature pages.
 * 4. One section per Features menu group (rx_feature_nav(), in menu order;
 *    the group name is the h2). `group_intros` gives the eyebrow and intro
 *    of each group. Module label, blurb and icon come from the menu item;
 *    the point lists come from `points`, with the plan line live from the
 *    API (`feat`) or else the fixed `plan` text, printed only when known.
 * 5. Hospital pharmacies and chains: `hospital_*` fields and the screen.
 * 6. Plans at a glance: the live plans table, then `plans_foot_html`.
 * 7. FAQ: the feature pages' fixed intro (eyebrow "Questions", FAQ page and
 *    sales links) with `faq_title`, and parts/faq-list with ids fiq%d /
 *    fiq%db from 0.
 *
 * Plans and prices come only from rx_pricing_data() (Requirement 8.4). When
 * it is null the page still renders: rx_pricing_notice() replaces the plans
 * table, live plan lines are left out and pricing shortcodes render empty
 * (Requirement 8.3).
 *
 * The FAQPage schema is added to Yoast's graph by inc/seo.php.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / rx_svg_img() / esc_*() / rx_pricing_notice() where they are built.

$rx_fh_id = get_queried_object_id();

/**
 * SCF field of this page as an array ('' or false from SCF becomes array()).
 *
 * @param string $name Field name.
 */
$rx_fh_arr = static function ( string $name ) use ( $rx_fh_id ): array {
	$value = rx_field( $name, $rx_fh_id );
	return is_array( $value ) ? $value : array();
};

/**
 * Section heading group (eyebrow, title, intro), rendered.
 *
 * @param array $head Group value.
 * @return array{eyebrow: string, title: string, intro: string}
 */
$rx_fh_head = static function ( array $head ): array {
	return array(
		'eyebrow' => trim( rx_text( $head['eyebrow'] ?? '' ) ),
		'title'   => trim( rx_text( $head['title'] ?? '' ) ),
		'intro'   => trim( rx_text( $head['intro'] ?? '' ) ),
	);
};

$rx_fh_d      = rx_pricing_data();
$rx_fh_nav    = rx_feature_nav();
$rx_fh_demo   = rx_url( 'demo' );
$rx_fh_signup = (string) rx_setting( 'app_signup' );

// CTAs and reassurance lines.
$rx_fh_cta_demo = trim( rx_text( rx_field( 'cta_demo', $rx_fh_id ) ) );
$rx_fh_cta_free = trim( rx_text( rx_field( 'cta_free', $rx_fh_id ) ) );
$rx_fh_assure   = array();
foreach ( $rx_fh_arr( 'assure' ) as $rx_fh_row ) {
	$rx_fh_line = is_array( $rx_fh_row ) ? trim( rx_html( $rx_fh_row['html'] ?? '' ) ) : '';
	if ( '' !== trim( wp_strip_all_tags( $rx_fh_line ) ) ) {
		$rx_fh_assure[] = $rx_fh_line;
	}
}

// "Start with your problem": rows whose page is in the Features menu.
$rx_fh_index_head = $rx_fh_head( $rx_fh_arr( 'index_head' ) );
$rx_fh_index      = array();
foreach ( $rx_fh_arr( 'index' ) as $rx_fh_row ) {
	$rx_fh_key  = is_array( $rx_fh_row ) ? (string) ( $rx_fh_row['page_key'] ?? '' ) : '';
	$rx_fh_pain = is_array( $rx_fh_row ) ? trim( rx_text( $rx_fh_row['pain'] ?? '' ) ) : '';
	if ( '' === $rx_fh_pain || empty( $rx_fh_nav[ $rx_fh_key ] ) ) {
		continue;
	}
	$rx_fh_index[] = array( $rx_fh_pain, $rx_fh_nav[ $rx_fh_key ] );
}

// Module groups in menu order: group name => [ page key => nav item ].
$rx_fh_groups = array();
foreach ( $rx_fh_nav as $rx_fh_key => $rx_fh_item ) {
	$rx_fh_groups[ $rx_fh_item['group'] ][ $rx_fh_key ] = $rx_fh_item;
}

// Group eyebrow and intro, by group name (else by position).
$rx_fh_intros = array();
foreach ( array_values( $rx_fh_arr( 'group_intros' ) ) as $rx_fh_i => $rx_fh_row ) {
	if ( ! is_array( $rx_fh_row ) ) {
		continue;
	}
	$rx_fh_intro = array(
		'eyebrow' => trim( rx_text( $rx_fh_row['eyebrow'] ?? '' ) ),
		'intro'   => trim( rx_text( $rx_fh_row['intro'] ?? '' ) ),
	);
	$rx_fh_intros[ 'n:' . $rx_fh_i ] = $rx_fh_intro;
	$rx_fh_name                      = trim( (string) ( $rx_fh_row['group'] ?? '' ) );
	if ( '' !== $rx_fh_name && ! isset( $rx_fh_intros[ 'g:' . $rx_fh_name ] ) ) {
		$rx_fh_intros[ 'g:' . $rx_fh_name ] = $rx_fh_intro;
	}
}

/*
 * Point lists per page key: [ text, plan line ]. The plan line is the live
 * label of `feat`, else the fixed `plan` text, else '' (not printed).
 */
$rx_fh_points = array();
foreach ( $rx_fh_arr( 'points' ) as $rx_fh_row ) {
	if ( ! is_array( $rx_fh_row ) || empty( $rx_fh_row['page_key'] ) || ! is_array( $rx_fh_row['items'] ?? null ) ) {
		continue;
	}
	foreach ( $rx_fh_row['items'] as $rx_fh_pt ) {
		if ( ! is_array( $rx_fh_pt ) ) {
			continue;
		}
		$rx_fh_text = trim( rx_text( $rx_fh_pt['text'] ?? '' ) );
		if ( '' === $rx_fh_text ) {
			continue;
		}
		$rx_fh_feat = trim( (string) ( $rx_fh_pt['feat'] ?? '' ) );
		$rx_fh_live = '' !== $rx_fh_feat && $rx_fh_d ? rx_feature_plan_label( $rx_fh_d, $rx_fh_feat ) : null;
		$rx_fh_plan = null !== $rx_fh_live ? esc_html( $rx_fh_live ) : trim( rx_text( $rx_fh_pt['plan'] ?? '' ) );

		$rx_fh_points[ (string) $rx_fh_row['page_key'] ][] = array( $rx_fh_text, $rx_fh_plan );
	}
}

// Hospital pharmacies and chains.
$rx_fh_hospital_head = $rx_fh_head( $rx_fh_arr( 'hospital_head' ) );
$rx_fh_hospital      = array();
foreach ( $rx_fh_arr( 'hospital_points' ) as $rx_fh_row ) {
	$rx_fh_line = is_array( $rx_fh_row ) ? trim( rx_html( $rx_fh_row['html'] ?? '' ) ) : '';
	if ( '' !== trim( wp_strip_all_tags( $rx_fh_line ) ) ) {
		$rx_fh_hospital[] = $rx_fh_line;
	}
}
$rx_fh_links = array();
foreach ( $rx_fh_arr( 'hospital_links' ) as $rx_fh_row ) {
	$rx_fh_label = is_array( $rx_fh_row ) ? trim( rx_text( $rx_fh_row['label'] ?? '' ) ) : '';
	$rx_fh_key   = is_array( $rx_fh_row ) ? trim( (string) ( $rx_fh_row['link_key'] ?? '' ) ) : '';
	if ( '' !== $rx_fh_label && '' !== $rx_fh_key ) {
		$rx_fh_links[] = array( $rx_fh_label, rx_url( $rx_fh_key ) );
	}
}
$rx_fh_screen     = rx_field( 'hospital_screen', $rx_fh_id );
$rx_fh_screen     = is_string( $rx_fh_screen ) ? trim( $rx_fh_screen ) : '';
$rx_fh_screen_alt = (string) rx_field( 'hospital_screen_alt', $rx_fh_id );

// Plans at a glance.
$rx_fh_plans_head = $rx_fh_head( $rx_fh_arr( 'plans_head' ) );
$rx_fh_plans_foot = trim( rx_html( rx_field( 'plans_foot_html', $rx_fh_id ) ) );
$rx_fh_plans      = $rx_fh_d ? rx_pricing_plans( $rx_fh_d ) : array();

// FAQ, buffered so the section is skipped when no question renders.
$rx_fh_faq_title = trim( rx_text( rx_field( 'faq_title', $rx_fh_id ) ) );
$rx_fh_phone     = trim( (string) rx_setting( 'sales_phone' ) );
$rx_fh_tel       = trim( (string) rx_setting( 'sales_tel' ) );
if ( '' === $rx_fh_tel ) {
	$rx_fh_tel = (string) preg_replace( '/[^0-9+]/', '', $rx_fh_phone );
}
$rx_fh_faq_link = '<a href="' . esc_url( rx_url( 'faq' ) ) . '">' . esc_html__( 'FAQ page', 'pharmacy' ) . '</a>';
if ( '' !== $rx_fh_phone && '' !== $rx_fh_tel ) {
	$rx_fh_faq_intro = sprintf(
		/* translators: 1: "FAQ page" link, 2: sales phone number (a tel: link). */
		esc_html__( 'More answers on the %1$s, or call sales on %2$s.', 'pharmacy' ),
		$rx_fh_faq_link,
		'<a href="' . esc_url( 'tel:' . $rx_fh_tel ) . '">' . esc_html( $rx_fh_phone ) . '</a>'
	);
} else {
	$rx_fh_faq_intro = sprintf(
		/* translators: %s: "FAQ page" link. */
		esc_html__( 'More answers on the %s.', 'pharmacy' ),
		$rx_fh_faq_link
	);
}

ob_start();
get_template_part(
	'parts/faq-list',
	null,
	array(
		'items' => $rx_fh_arr( 'faq' ),
		'ids'   => array(
			'answer' => 'fiq%d',
			'button' => 'fiq%db',
			'start'  => 0,
		),
	)
);
$rx_fh_faq = trim( (string) ob_get_clean() );

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => $rx_fh_arr( 'hero' ) ) ); ?>

<?php if ( '' !== $rx_fh_cta_demo || ( '' !== $rx_fh_cta_free && '' !== $rx_fh_signup ) || $rx_fh_assure ) : ?>
<section class="section-tight fp-top">
  <div class="container">
    <?php if ( '' !== $rx_fh_cta_demo || ( '' !== $rx_fh_cta_free && '' !== $rx_fh_signup ) ) : ?>
    <p class="hero-ctas fp-ctas">
      <?php if ( '' !== $rx_fh_cta_demo ) : ?><a class="btn btn-primary" href="<?php echo esc_url( $rx_fh_demo ); ?>" data-cta="demo"><?php echo $rx_fh_cta_demo; ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><?php endif; ?>
      <?php if ( '' !== $rx_fh_cta_free && '' !== $rx_fh_signup ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_fh_signup ); ?>"><?php echo $rx_fh_cta_free; ?></a><?php endif; ?>
    </p>
    <?php endif; ?>
    <?php if ( $rx_fh_assure ) : ?>
    <ul class="hero-assure fp-assure">
      <?php foreach ( $rx_fh_assure as $rx_fh_line ) : ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?php echo $rx_fh_line; ?></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( $rx_fh_index ) : ?>
<section class="section" aria-labelledby="fi-idx">
  <div class="container fi-index-wrap">
    <div class="section-head"><?php if ( '' !== $rx_fh_index_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fh_index_head['eyebrow']; ?></span><?php endif; ?><h2 id="fi-idx"><?php echo $rx_fh_index_head['title']; ?></h2>
      <?php if ( '' !== $rx_fh_index_head['intro'] ) : ?><p><?php echo $rx_fh_index_head['intro']; ?></p><?php endif; ?></div>
    <ol class="fi-index">
      <?php foreach ( $rx_fh_index as [ $rx_fh_pain, $rx_fh_item ] ) : ?>
        <li><a href="<?php echo esc_url( $rx_fh_item['url'] ); ?>"><span class="fi-pain"><?php echo $rx_fh_pain; ?></span><span class="fi-go"><?php echo esc_html( $rx_fh_item['label'] ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span></a></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php
$rx_fh_g = 0;
foreach ( $rx_fh_groups as $rx_fh_group => $rx_fh_items ) :
	$rx_fh_intro = $rx_fh_intros[ 'g:' . $rx_fh_group ] ?? $rx_fh_intros[ 'n:' . $rx_fh_g ] ?? array(
		'eyebrow' => '',
		'intro'   => '',
	);
	++$rx_fh_g;
	$rx_fh_gid = 'fi-g' . $rx_fh_g;
	?>
<section class="section<?php echo 1 === $rx_fh_g ? ' bg-soft' : ''; ?>" aria-labelledby="<?php echo esc_attr( $rx_fh_gid ); ?>">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_fh_intro['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fh_intro['eyebrow']; ?></span><?php endif; ?><h2 id="<?php echo esc_attr( $rx_fh_gid ); ?>"><?php echo esc_html( $rx_fh_group ); ?></h2>
      <?php if ( '' !== $rx_fh_intro['intro'] ) : ?><p><?php echo $rx_fh_intro['intro']; ?></p><?php endif; ?></div>
    <ul class="fi-mods">
      <?php foreach ( $rx_fh_items as $rx_fh_key => $rx_fh_item ) : ?>
        <li class="fi-mod">
          <h3><?php if ( '' !== $rx_fh_item['icon'] ) : ?><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_fh_item['icon'] ); ?>"/></svg><?php endif; ?><?php echo esc_html( $rx_fh_item['label'] ); ?></h3>
          <?php if ( '' !== $rx_fh_item['description'] ) : ?><p class="fi-desc"><?php echo esc_html( $rx_fh_item['description'] ); ?></p><?php endif; ?>
          <?php if ( ! empty( $rx_fh_points[ $rx_fh_key ] ) ) : ?>
          <ul class="fi-pts">
            <?php foreach ( $rx_fh_points[ $rx_fh_key ] as [ $rx_fh_text, $rx_fh_plan ] ) : ?>
              <li><span><?php echo $rx_fh_text; ?></span><?php if ( '' !== $rx_fh_plan ) : ?><small><?php echo $rx_fh_plan; ?></small><?php endif; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <a class="fi-open" href="<?php echo esc_url( $rx_fh_item['url'] ); ?>"><?php echo esc_html( sprintf( /* translators: %s: feature page name, for example Billing & counter. */ __( 'Open %s', 'pharmacy' ), $rx_fh_item['label'] ) ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endforeach; ?>

<?php if ( '' !== $rx_fh_hospital_head['title'] || $rx_fh_hospital || '' !== $rx_fh_screen ) : ?>
<section class="section bg-soft" aria-labelledby="fi-also">
  <div class="container fi-also">
    <div>
      <?php if ( '' !== $rx_fh_hospital_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fh_hospital_head['eyebrow']; ?></span><?php endif; ?>
      <h2 id="fi-also"><?php echo $rx_fh_hospital_head['title']; ?></h2>
      <?php if ( '' !== $rx_fh_hospital_head['intro'] ) : ?><p class="fi-also-lead"><?php echo $rx_fh_hospital_head['intro']; ?></p><?php endif; ?>
      <?php if ( $rx_fh_hospital ) : ?>
      <ul class="checks">
        <?php foreach ( $rx_fh_hospital as $rx_fh_line ) : ?>
        <li><svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg><span><?php echo $rx_fh_line; ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php if ( $rx_fh_links ) : ?><p class="fi-also-links"><?php foreach ( $rx_fh_links as [ $rx_fh_label, $rx_fh_url ] ) : ?><a href="<?php echo esc_url( $rx_fh_url ); ?>"><?php echo $rx_fh_label; ?></a><?php endforeach; ?></p><?php endif; ?>
    </div>
    <?php if ( '' !== $rx_fh_screen ) : ?>
    <div>
      <figure class="shot"><?php echo rx_svg_img( $rx_fh_screen, 1440, 900, wp_strip_all_tags( do_shortcode( $rx_fh_screen_alt ) ) ); ?></figure>
      
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="fi-plans">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_fh_plans_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fh_plans_head['eyebrow']; ?></span><?php endif; ?><h2 id="fi-plans"><?php echo '' !== $rx_fh_plans_head['title'] ? $rx_fh_plans_head['title'] : esc_html__( 'Plans', 'pharmacy' ); ?></h2>
      <?php if ( '' !== $rx_fh_plans_head['intro'] ) : ?><p><?php echo $rx_fh_plans_head['intro']; ?></p><?php endif; ?></div>
    <?php if ( ! $rx_fh_d ) : ?>
      <?php echo rx_pricing_notice(); ?>
    <?php else : ?>
    <div class="fi-plans-wrap">
      <table class="fi-plans">
        <thead><tr><th scope="col"><?php esc_html_e( 'Plan', 'pharmacy' ); ?></th><th scope="col"><?php esc_html_e( 'Price', 'pharmacy' ); ?></th><th scope="col"><?php esc_html_e( 'Users', 'pharmacy' ); ?></th><th scope="col"><?php esc_html_e( 'Sales bills a month', 'pharmacy' ); ?></th></tr></thead>
        <tbody>
          <?php
			foreach ( $rx_fh_plans as $rx_fh_plan_id => $rx_fh_p ) :
				$rx_fh_lim = rx_plan_bill_limits( $rx_fh_d, (int) $rx_fh_plan_id );
				if ( ! empty( $rx_fh_p['is_custom'] ) ) {
					$rx_fh_price = esc_html__( 'Quoted for your stores', 'pharmacy' );
				} elseif ( $rx_fh_p['price'] > 0 ) {
					/* translators: %s: yearly price, for example ₹4,999. */
					$rx_fh_price = esc_html( sprintf( __( '%s a year', 'pharmacy' ), rx_inr( $rx_fh_p['price'] ) ) );
				} else {
					$rx_fh_price = esc_html__( 'Free forever', 'pharmacy' );
				}
				?>
            <tr>
              <th scope="row"><?php echo esc_html( (string) $rx_fh_p['name'] ); ?><?php if ( ! empty( $rx_fh_p['is_popular'] ) ) : ?> <span class="fi-pop"><?php esc_html_e( 'Most popular', 'pharmacy' ); ?></span><?php endif; ?></th>
              <td><?php echo $rx_fh_price; ?></td>
              <td><?php echo isset( $rx_fh_p['user_limit'] ) ? esc_html( (string) $rx_fh_p['user_limit'] ) : esc_html__( 'Unlimited', 'pharmacy' ); ?></td>
              <td><?php echo null !== $rx_fh_lim['sales'] ? (int) $rx_fh_lim['sales'] : esc_html__( 'Unlimited', 'pharmacy' ); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <?php if ( '' !== $rx_fh_plans_foot ) : ?><p class="lc-foot"><?php echo $rx_fh_plans_foot; ?></p><?php endif; ?>
  </div>
</section>

<?php if ( '' !== $rx_fh_faq ) : ?>
<section class="section bg-soft" aria-labelledby="fi-faq">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow"><?php esc_html_e( 'Questions', 'pharmacy' ); ?></span><h2 id="fi-faq"><?php echo '' !== $rx_fh_faq_title ? $rx_fh_faq_title : esc_html__( 'Before you look closer', 'pharmacy' ); ?></h2>
      <p><?php echo $rx_fh_faq_intro; ?></p></div>
    <?php echo $rx_fh_faq; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
