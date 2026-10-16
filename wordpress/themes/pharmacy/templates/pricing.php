<?php
/**
 * Template Name: Pricing
 *
 * The Pricing page. Port of the PHP site's pricing.php, same sections and
 * markup:
 *
 * 1. page-hero (`hero`).
 * 2. Term bar (radio per API term: name=term, data-discount) and the plan
 *    cards: "Most popular" badge, `plan_fit` line (else the plan's API
 *    description), price with the .term-total line (rx_term_total() for the
 *    shortest term; assets/js/pricing.js updates it when the term changes),
 *    highlights and the button.
 * 3. #compare: the comparison table (table.pc[data-collapse]) with one
 *    collapsible tbody per API feature group, the "Collapse all" button
 *    (.pc-all, shown by pricing.js) and `feature_labels` for friendlier
 *    feature names. Features no plan includes are left out; users get their
 *    own row on top.
 * 4. Message & OCR credits: copy from `credits_head`, the `credits_note`
 *    (data-confirm when not confirmed) and the included-credits table, one
 *    row per `credit_rows` entry with the numbers from each plan's
 *    message_service.
 * 5. Pricing FAQ: parts/faq-list with ids pq%d / pq%db from 0, data-confirm
 *    on the answer.
 *
 * Plans, prices, terms, features, statuses and credit numbers come only
 * from rx_pricing_data() (Requirement 8.4). When it is null the page still
 * renders: rx_pricing_notice() is printed where the plan cards, the
 * comparison table and the credits table would be, and plan-dependent FAQ
 * answers render empty and are skipped (Requirement 8.3).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() / rx_pricing_cell() / rx_pricing_notice() where they are built.

$rx_pr_id = get_queried_object_id();

/**
 * SCF field of this page as an array ('' or false from SCF becomes array()).
 *
 * @param string $name Field name.
 */
$rx_pr_arr = static function ( string $name ) use ( $rx_pr_id ): array {
	$value = rx_field( $name, $rx_pr_id );
	return is_array( $value ) ? $value : array();
};

/**
 * Section heading group (eyebrow, title, intro / intro_html), rendered.
 *
 * @param array $head Group value.
 * @return array{eyebrow: string, title: string, intro: string}
 */
$rx_pr_head = static function ( array $head ): array {
	return array(
		'eyebrow' => trim( rx_text( $head['eyebrow'] ?? '' ) ),
		'title'   => trim( rx_text( $head['title'] ?? '' ) ),
		'intro'   => isset( $head['intro_html'] ) ? trim( rx_html( $head['intro_html'] ) ) : trim( rx_text( $head['intro'] ?? '' ) ),
	);
};

$rx_pr_d = rx_pricing_data();

/*
 * Pricing data, prepared as pricing.php does: plans in the backend's
 * sort_order (keyed by id), statuses by plan and feature, features by group
 * and sort order, group names by id, terms by years.
 */
$rx_pr_plans    = array();
$rx_pr_order    = array();
$rx_pr_status   = array();
$rx_pr_features = array();
$rx_pr_groups   = array();
$rx_pr_terms    = array();
if ( $rx_pr_d ) {
	$rx_pr_plans = rx_pricing_plans( $rx_pr_d );
	$rx_pr_order = array_keys( $rx_pr_plans );
	foreach ( $rx_pr_d['statuses'] as $rx_pr_st ) {
		if ( is_array( $rx_pr_st ) && isset( $rx_pr_st['plan_template_id'], $rx_pr_st['pricing_feature_id'] ) ) {
			$rx_pr_status[ $rx_pr_st['plan_template_id'] ][ $rx_pr_st['pricing_feature_id'] ] = $rx_pr_st;
		}
	}
	$rx_pr_features = array_values( array_filter( $rx_pr_d['features'], 'is_array' ) );
	usort(
		$rx_pr_features,
		static fn( $a, $b ) => array( $a['group_id'] ?? 0, $a['sort_order'] ?? 0 ) <=> array( $b['group_id'] ?? 0, $b['sort_order'] ?? 0 )
	);
	$rx_pr_groups = array_column( $rx_pr_d['groups'], 'name', 'id' );
	$rx_pr_terms  = array_values( array_filter( $rx_pr_d['terms'], 'is_array' ) );
	usort( $rx_pr_terms, static fn( $a, $b ) => (int) ( $a['years'] ?? 0 ) <=> (int) ( $b['years'] ?? 0 ) );
}

// "Who it's for" line per plan id; a plan without one shows its API description.
$rx_pr_fit = array();
foreach ( $rx_pr_arr( 'plan_fit' ) as $rx_pr_row ) {
	if ( is_array( $rx_pr_row ) && isset( $rx_pr_row['plan_id'] ) && '' !== (string) $rx_pr_row['plan_id'] ) {
		$rx_pr_fit[ (int) $rx_pr_row['plan_id'] ] = trim( rx_text( $rx_pr_row['text'] ?? '' ) );
	}
}

// Friendlier labels for API feature names.
$rx_pr_labels = array();
foreach ( $rx_pr_arr( 'feature_labels' ) as $rx_pr_row ) {
	if ( is_array( $rx_pr_row ) && isset( $rx_pr_row['api_name'] ) && '' !== (string) $rx_pr_row['api_name'] ) {
		$rx_pr_label = trim( rx_text( $rx_pr_row['label'] ?? '' ) );
		if ( '' !== $rx_pr_label ) {
			$rx_pr_labels[ (string) $rx_pr_row['api_name'] ] = $rx_pr_label;
		}
	}
}

$rx_pr_signup     = (string) rx_setting( 'app_signup' );
$rx_pr_term_label = trim( rx_text( rx_field( 'term_label', $rx_pr_id ) ) );
$rx_pr_term_note  = trim( rx_text( rx_field( 'term_note', $rx_pr_id ) ) );
$rx_pr_compare    = $rx_pr_head( $rx_pr_arr( 'compare_head' ) );
$rx_pr_credits    = $rx_pr_head( $rx_pr_arr( 'credits_head' ) );
$rx_pr_faq_head   = $rx_pr_head( $rx_pr_arr( 'faq_head' ) );
$rx_pr_note       = trim( rx_text( rx_field( 'credits_note', $rx_pr_id ) ) );
$rx_pr_note_ok    = false !== rx_field( 'credits_note_confirmed', $rx_pr_id );

// Credit table rows: [ message_service key, row label ].
$rx_pr_credit_rows = array();
foreach ( $rx_pr_arr( 'credit_rows' ) as $rx_pr_row ) {
	if ( is_array( $rx_pr_row ) && isset( $rx_pr_row['service'] ) && is_string( $rx_pr_row['service'] ) && '' !== $rx_pr_row['service'] ) {
		$rx_pr_credit_rows[] = array( $rx_pr_row['service'], trim( rx_text( $rx_pr_row['label'] ?? '' ) ) );
	}
}

/*
 * Comparison sections: features grouped by API group, leaving out rows no
 * plan includes; the user limit gets its own row on top.
 */
$rx_pr_sections = array();
foreach ( $rx_pr_features as $rx_pr_f ) {
	$rx_pr_in_any = false;
	foreach ( $rx_pr_order as $rx_pr_plan_id ) {
		if ( ! empty( $rx_pr_status[ $rx_pr_plan_id ][ $rx_pr_f['id'] ?? '' ] ) ) {
			$rx_pr_in_any = true;
		}
	}
	if ( ! $rx_pr_in_any || 'feat_user_limit' === ( $rx_pr_f['unique_name'] ?? '' ) ) {
		continue;
	}
	$rx_pr_sections[ $rx_pr_f['group_id'] ?? 0 ][] = $rx_pr_f;
}

// FAQ, buffered so the section is skipped when no question renders.
ob_start();
get_template_part(
	'parts/faq-list',
	null,
	array(
		'items'      => $rx_pr_arr( 'faq' ),
		'ids'        => array(
			'answer' => 'pq%d',
			'button' => 'pq%db',
			'start'  => 0,
		),
		'confirm_on' => 'answer',
	)
);
$rx_pr_faq = trim( (string) ob_get_clean() );

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => $rx_pr_arr( 'hero' ) ) ); ?>

<section class="section-tight" style="padding-top:8px">
  <div class="container">
    <?php if ( ! $rx_pr_d ) : ?>
      <?php echo rx_pricing_notice(); ?>
    <?php else : ?>
    <div class="term-bar">
      <?php if ( '' !== $rx_pr_term_label ) : ?><span><?php echo $rx_pr_term_label; ?></span><?php endif; ?>
      <div class="term-seg" role="radiogroup" aria-label="<?php esc_attr_e( 'Billing term', 'pharmacy' ); ?>">
        <?php
		foreach ( $rx_pr_terms as $rx_pr_i => $rx_pr_t ) :
			$rx_pr_off = (float) ( $rx_pr_t['discount_percent'] ?? 0 );
			?>
          <label><input type="radio" name="term" value="<?php echo (int) ( $rx_pr_t['years'] ?? 0 ); ?>" data-discount="<?php echo esc_attr( (string) $rx_pr_off ); ?>"<?php echo 0 === $rx_pr_i ? ' checked' : ''; ?>><span><?php echo esc_html( (string) ( $rx_pr_t['label'] ?? '' ) ); ?><?php echo $rx_pr_off > 0 ? ' · ' . esc_html( sprintf( /* translators: %s: discount percentage, for example 10. */ __( 'save %s%%', 'pharmacy' ), (string) $rx_pr_off ) ) : ''; ?></span></label>
        <?php endforeach; ?>
      </div>
      <?php if ( '' !== $rx_pr_term_note ) : ?><small><?php echo $rx_pr_term_note; ?></small><?php endif; ?>
    </div>

    <div class="price-grid" style="margin-top:28px">
      <?php
		foreach ( $rx_pr_order as $rx_pr_plan_id ) :
			$rx_pr_p       = $rx_pr_plans[ $rx_pr_plan_id ];
			$rx_pr_paid    = $rx_pr_p['price'] > 0;
			$rx_pr_popular = ! empty( $rx_pr_p['is_popular'] );
			$rx_pr_custom  = ! empty( $rx_pr_p['is_custom'] );
			$rx_pr_name    = esc_html( (string) $rx_pr_p['name'] );
			$rx_pr_fit_txt = $rx_pr_fit[ (int) $rx_pr_plan_id ] ?? '';
			if ( '' === $rx_pr_fit_txt ) {
				$rx_pr_fit_txt = esc_html( (string) ( $rx_pr_p['description'] ?? '' ) );
			}
			?>
        <article class="plan<?php echo $rx_pr_popular ? ' featured' : ''; ?>">
          <?php if ( $rx_pr_popular ) : ?><span class="badge"><?php esc_html_e( 'Most popular', 'pharmacy' ); ?></span><?php endif; ?>
          <h2 style="font-size:20px"><?php echo $rx_pr_name; ?></h2>
          <p class="fit"><?php echo $rx_pr_fit_txt; ?></p>
          <div class="quote">
            <?php if ( $rx_pr_custom ) : ?><b><?php esc_html_e( 'Custom', 'pharmacy' ); ?></b><?php esc_html_e( 'Priced for your stores', 'pharmacy' ); ?>
            <?php elseif ( ! $rx_pr_paid ) : ?><b><?php esc_html_e( 'Free', 'pharmacy' ); ?></b><?php esc_html_e( 'Forever', 'pharmacy' ); ?>
            <?php else : ?><b><?php echo esc_html( sprintf( /* translators: %s: yearly price, for example ₹4,999. */ __( '%s / year', 'pharmacy' ), rx_inr( $rx_pr_p['price'] ) ) ); ?></b><span class="term-total" data-price="<?php echo (int) $rx_pr_p['price']; ?>"><?php echo $rx_pr_terms ? esc_html( rx_term_total( (int) $rx_pr_p['price'], $rx_pr_terms[0] ) ) : ''; ?></span>
            <?php endif; ?>
          </div>
          <ul class="checks">
            <?php foreach ( is_array( $rx_pr_p['highlights'] ?? null ) ? $rx_pr_p['highlights'] : array() as $rx_pr_h ) : ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?php echo esc_html( (string) $rx_pr_h ); ?></li><?php endforeach; ?>
          </ul>
          <?php if ( $rx_pr_custom ) : ?>
            <a class="btn btn-secondary" href="<?php echo esc_url( add_query_arg( 'want', 'quote', rx_url( 'demo' ) ) ); ?>" data-cta="quote"><?php esc_html_e( 'Talk to us', 'pharmacy' ); ?></a>
          <?php elseif ( '' !== $rx_pr_signup ) : ?>
            <a class="btn <?php echo $rx_pr_popular ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo esc_url( $rx_pr_signup ); ?>"><?php echo $rx_pr_paid ? esc_html( sprintf( /* translators: %s: plan name. */ __( 'Choose %s', 'pharmacy' ), (string) $rx_pr_p['name'] ) ) : esc_html__( 'Start free', 'pharmacy' ); ?></a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-tight bg-soft" id="compare">
  <div class="container">
    <?php if ( '' !== $rx_pr_compare['eyebrow'] || '' !== $rx_pr_compare['title'] || '' !== $rx_pr_compare['intro'] ) : ?>
    <div class="section-head"><?php if ( '' !== $rx_pr_compare['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_pr_compare['eyebrow']; ?></span><?php endif; ?><?php if ( '' !== $rx_pr_compare['title'] ) : ?><h2><?php echo $rx_pr_compare['title']; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_pr_compare['intro'] ) : ?><p><?php echo $rx_pr_compare['intro']; ?></p><?php endif; ?></div>
    <?php endif; ?>
    <?php if ( ! $rx_pr_d ) : ?>
      <?php echo rx_pricing_notice(); ?>
    <?php else : ?>
    <div class="pc-tools"><button type="button" class="pc-all" aria-expanded="true" hidden><?php esc_html_e( 'Collapse all', 'pharmacy' ); ?></button></div>
    <div class="pc-wrap" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Plan comparison table, scrolls sideways on small screens', 'pharmacy' ); ?>">
      <table class="pc" data-collapse>
        <thead><tr><th scope="col"><?php esc_html_e( 'Feature', 'pharmacy' ); ?></th><?php foreach ( $rx_pr_order as $rx_pr_plan_id ) : ?><th scope="col"><?php echo esc_html( (string) $rx_pr_plans[ $rx_pr_plan_id ]['name'] ); ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <tr><th scope="row"><?php esc_html_e( 'Users', 'pharmacy' ); ?></th><?php foreach ( $rx_pr_order as $rx_pr_plan_id ) : ?><td><span class="pc-val"><?php echo isset( $rx_pr_plans[ $rx_pr_plan_id ]['user_limit'] ) ? esc_html( (string) $rx_pr_plans[ $rx_pr_plan_id ]['user_limit'] ) : esc_html__( 'Unlimited', 'pharmacy' ); ?></span></td><?php endforeach; ?></tr>
        </tbody>
        <?php
		foreach ( $rx_pr_sections as $rx_pr_gid => $rx_pr_rows ) :
			$rx_pr_sid = 'pcg-' . (int) $rx_pr_gid;
			$rx_pr_n   = count( $rx_pr_rows );
			?>
          <tbody class="pc-head-row">
            <tr class="pc-group">
              <th scope="rowgroup"><button type="button" class="pc-toggle" aria-expanded="true" aria-controls="<?php echo esc_attr( $rx_pr_sid ); ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-chev"/></svg><?php echo isset( $rx_pr_groups[ $rx_pr_gid ] ) ? esc_html( (string) $rx_pr_groups[ $rx_pr_gid ] ) : esc_html__( 'Other', 'pharmacy' ); ?>
                <span class="pc-count"><?php echo esc_html( sprintf( /* translators: %d: number of features in the group. */ _n( '%d feature', '%d features', $rx_pr_n, 'pharmacy' ), $rx_pr_n ) ); ?></span></button></th>
              <?php
				foreach ( $rx_pr_order as $rx_pr_plan_id ) :
					$rx_pr_has = 0;
					foreach ( $rx_pr_rows as $rx_pr_f ) {
						if ( ! empty( $rx_pr_status[ $rx_pr_plan_id ][ $rx_pr_f['id'] ?? '' ] ) ) {
							++$rx_pr_has;
						}
					}
					?>
                <td><span class="pc-sum" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: features included, 2: features in the group, 3: plan name. */ __( '%1$d of %2$d included in %3$s', 'pharmacy' ), $rx_pr_has, $rx_pr_n, (string) $rx_pr_plans[ $rx_pr_plan_id ]['name'] ) ); ?>"><?php echo (int) $rx_pr_has; ?>/<?php echo (int) $rx_pr_n; ?></span></td>
              <?php endforeach; ?>
            </tr>
          </tbody>
          <tbody id="<?php echo esc_attr( $rx_pr_sid ); ?>" class="pc-rows">
            <?php foreach ( $rx_pr_rows as $rx_pr_f ) : ?>
              <tr><th scope="row"><?php echo $rx_pr_labels[ (string) ( $rx_pr_f['name'] ?? '' ) ] ?? esc_html( (string) ( $rx_pr_f['name'] ?? '' ) ); ?></th>
                <?php foreach ( $rx_pr_order as $rx_pr_plan_id ) : ?><td><?php echo rx_pricing_cell( $rx_pr_status[ $rx_pr_plan_id ][ $rx_pr_f['id'] ?? '' ] ?? null ); ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
          </tbody>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-tight">
  <div class="container grid-2" style="align-items:start">
    <div>
      <?php if ( '' !== $rx_pr_credits['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_pr_credits['eyebrow']; ?></span><?php endif; ?>
      <?php if ( '' !== $rx_pr_credits['title'] ) : ?><h2 style="font-size:clamp(24px,3vw,32px)"><?php echo $rx_pr_credits['title']; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_pr_credits['intro'] ) : ?><p style="margin-top:12px;color:var(--text-2)"><?php echo $rx_pr_credits['intro']; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_pr_note ) : ?><p class="meta-line"<?php echo $rx_pr_note_ok ? '' : ' data-confirm'; ?>><?php echo $rx_pr_note; ?></p><?php endif; ?>
    </div>
    <?php if ( ! $rx_pr_d ) : ?>
    <div><?php echo rx_pricing_notice(); ?></div>
    <?php elseif ( $rx_pr_credit_rows ) : ?>
    <div class="pc-wrap">
      <table class="pc pc-small">
        <thead><tr><th scope="col"><?php esc_html_e( 'Included', 'pharmacy' ); ?></th><?php foreach ( $rx_pr_order as $rx_pr_plan_id ) : ?><th scope="col"><?php echo esc_html( (string) $rx_pr_plans[ $rx_pr_plan_id ]['name'] ); ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ( $rx_pr_credit_rows as [ $rx_pr_service, $rx_pr_row_label ] ) : ?>
            <tr><th scope="row"><?php echo $rx_pr_row_label; ?></th><?php foreach ( $rx_pr_order as $rx_pr_plan_id ) : ?><td><?php echo (int) ( $rx_pr_plans[ $rx_pr_plan_id ]['message_service'][ $rx_pr_service ] ?? 0 ); ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ( '' !== $rx_pr_faq ) : ?>
<section class="section-tight bg-soft">
  <div class="container faq-grid">
    <div class="faq-intro"><?php if ( '' !== $rx_pr_faq_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_pr_faq_head['eyebrow']; ?></span><?php endif; ?><?php if ( '' !== $rx_pr_faq_head['title'] ) : ?><h2><?php echo $rx_pr_faq_head['title']; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_pr_faq_head['intro'] ) : ?><p><?php echo $rx_pr_faq_head['intro']; ?></p><?php endif; ?></div>
    <?php echo $rx_pr_faq; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
