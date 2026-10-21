<?php
/**
 * Template Name: Feature
 *
 * Feature page: the "Run the counter" and "Run the business" pages (billing,
 * expiry, inventory, purchase, reports, gst, retention, security). Port of
 * the PHP site's partials/feature-page.php, reading the current page's
 * group_rx_feature fields (acf-json/group_rx_feature.json). Same sections,
 * order and markup:
 *
 *   page hero → CTAs, assurance lines and product screen → "Sound familiar?"
 *   diary (scenes) → leak register (worked example or demo card, optional
 *   expiry estimator) → steps and shortcut keys → capabilities spec sheet →
 *   practical guide → demo checklist (proof) → related feature pages → FAQ
 *
 * - A section is skipped when its heading title and its rows are empty, as
 *   the PHP site skips an empty $fp key. Rows and elements whose text renders
 *   empty are not printed, so an assurance line or FAQ wrapped in a pricing
 *   [rx_if] disappears when the condition fails.
 * - Plan lines: the live label for the row's `feat` (a Pricing_API
 *   unique_name, rx_feature_plan_label()), else the row's fixed `plan` text.
 *   With neither, leak cards print "Plan: not listed separately" and
 *   capabilities print no plan line. With no pricing at all, the fixed text
 *   is used (or the same "not listed" wording).
 * - Defaults for empty fields, as in feature-page.php: CTA labels "Book a
 *   free demo" / "Start free", related heading "Also at the counter", FAQ
 *   heading "Asked at the counter". An empty worked-example title prints the
 *   demo card instead.
 * - Ids come from the page key (rx_page_key(), [^a-z0-9] removed): {key}-scenes,
 *   {key}-leaks, {key}-math, {key}-steps, {key}-caps, {key}-guide, {key}-proof,
 *   {key}-rel, {key}-faq, and FAQ answers {key}q0 / buttons {key}q0b from 0.
 * - Structured data is not printed here: Yoast outputs the breadcrumb and
 *   inc/seo.php adds the FAQPage (confirmed answers only).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text(), rx_html(), rx_svg_img() and esc_*() where they are built.

/*
 * -------------------------------------------------------------------------
 * Page key, pricing and small helpers
 * -------------------------------------------------------------------------
 */

$rx_fp_key = rx_page_key();
$rx_fp_id  = 'generic' === $rx_fp_key ? '' : (string) preg_replace( '/[^a-z0-9]/', '', $rx_fp_key );
if ( '' === $rx_fp_id ) {
	$rx_fp_id = 'fp';
}

$rx_fp_pricing = rx_pricing_data();
$rx_fp_demo    = rx_url( 'demo' );
$rx_fp_compare = rx_url( 'pricing' ) . '#compare';

/** Plain-text value, shortcodes resolved, escaped and trimmed. */
$rx_fp_text = static function ( $value ): string {
	return trim( rx_text( $value ) );
};

/** HTML value (_html fields), shortcodes resolved, filtered and trimmed. */
$rx_fp_html = static function ( $value ): string {
	return trim( rx_html( $value ) );
};

/** Rows of a repeater value (SCF gives false when empty), as a list of arrays. */
$rx_fp_rows = static function ( $rows ): array {
	return is_array( $rows ) ? array_values( array_filter( $rows, 'is_array' ) ) : array();
};

/** A `*_head` group (eyebrow, title, intro) as escaped text. */
$rx_fp_head = static function ( string $name ) use ( $rx_fp_text ): array {
	$head = rx_field( $name );
	$head = is_array( $head ) ? $head : array();
	return array(
		'eyebrow' => $rx_fp_text( $head['eyebrow'] ?? '' ),
		'title'   => $rx_fp_text( $head['title'] ?? '' ),
		'intro'   => $rx_fp_text( $head['intro'] ?? '' ),
	);
};

/** The standard section heading block (eyebrow, h2 with id, optional intro). */
$rx_fp_section_head = static function ( array $head, string $h2_id ): string {
	return '<div class="section-head">'
		. ( '' !== $head['eyebrow'] ? '<span class="eyebrow">' . $head['eyebrow'] . '</span>' : '' )
		. '<h2 id="' . esc_attr( $h2_id ) . '">' . $head['title'] . '</h2>'
		. ( '' !== $head['intro'] ? '<p>' . $head['intro'] . '</p>' : '' )
		. '</div>';
};

/**
 * Plan for a row: the live label when `feat` is set and the Pricing_API
 * maps it, else the fixed `plan` text. Escaped; '' when unknown.
 */
$rx_fp_plan = static function ( array $row ) use ( $rx_fp_pricing, $rx_fp_text ): string {
	$feat = isset( $row['feat'] ) && is_string( $row['feat'] ) ? trim( $row['feat'] ) : '';
	if ( '' !== $feat && null !== $rx_fp_pricing ) {
		$label = rx_feature_plan_label( $rx_fp_pricing, $feat );
		if ( null !== $label ) {
			return esc_html( $label );
		}
	}
	return $rx_fp_text( $row['plan'] ?? '' );
};

/** "Plan: <b>{plan}</b>" for an escaped plan label. */
$rx_fp_plan_line = static function ( string $plan ): string {
	return esc_html__( 'Plan:', 'pharmacy' ) . ' <b>' . $plan . '</b>';
};

/*
 * -------------------------------------------------------------------------
 * Field values
 * -------------------------------------------------------------------------
 */

// CTAs, assurance lines and the product screen.
$rx_fp_hero     = rx_field( 'hero' );
$rx_fp_cta_demo = $rx_fp_text( rx_field( 'cta_demo' ) );
$rx_fp_cta_free = $rx_fp_text( rx_field( 'cta_free' ) );
$rx_fp_signup   = (string) rx_setting( 'app_signup' );

$rx_fp_assure = array();
foreach ( $rx_fp_rows( rx_field( 'assure' ) ) as $rx_fp_row ) {
	$rx_fp_line = $rx_fp_text( $rx_fp_row['text'] ?? '' );
	if ( '' !== $rx_fp_line ) {
		$rx_fp_assure[] = $rx_fp_line;
	}
}

$rx_fp_screen = rx_field( 'screen' );
$rx_fp_screen = is_string( $rx_fp_screen ) && preg_match( '/^[a-z0-9-]+$/', $rx_fp_screen ) ? $rx_fp_screen : '';
$rx_fp_alt    = rx_field( 'screen_alt' );
$rx_fp_alt    = is_scalar( $rx_fp_alt ) && ! is_bool( $rx_fp_alt ) ? trim( wp_strip_all_tags( do_shortcode( (string) $rx_fp_alt ) ) ) : '';

// Scenes.
$rx_fp_scenes_head = $rx_fp_head( 'scenes_head' );
$rx_fp_scenes      = array();
foreach ( $rx_fp_rows( rx_field( 'scenes' ) ) as $rx_fp_row ) {
	$rx_fp_item = array(
		'time'  => $rx_fp_text( $rx_fp_row['time'] ?? '' ),
		'title' => $rx_fp_text( $rx_fp_row['title'] ?? '' ),
		'text'  => $rx_fp_text( $rx_fp_row['text'] ?? '' ),
		'cost'  => ucfirst( $rx_fp_text( $rx_fp_row['cost'] ?? '' ) ),
	);
	if ( implode( '', $rx_fp_item ) !== '' ) {
		$rx_fp_scenes[] = $rx_fp_item;
	}
}

// Leak register.
$rx_fp_leaks_head = $rx_fp_head( 'leaks_head' );
$rx_fp_leaks      = array();
foreach ( $rx_fp_rows( rx_field( 'leaks' ) ) as $rx_fp_row ) {
	$rx_fp_item = array(
		'leak' => $rx_fp_text( $rx_fp_row['leak'] ?? '' ),
		'how'  => $rx_fp_text( $rx_fp_row['how'] ?? '' ),
		'plug' => $rx_fp_html( $rx_fp_row['plug_html'] ?? '' ),
	);
	if ( implode( '', $rx_fp_item ) !== '' ) {
		$rx_fp_item['plan'] = $rx_fp_plan( $rx_fp_row );
		$rx_fp_leaks[]      = $rx_fp_item;
	}
}

$rx_fp_example = rx_field( 'leaks_example' );
$rx_fp_example = is_array( $rx_fp_example ) ? $rx_fp_example : array();
$rx_fp_math    = null;
if ( '' !== $rx_fp_text( $rx_fp_example['title'] ?? '' ) ) {
	$rx_fp_math = array(
		'title'        => $rx_fp_text( $rx_fp_example['title'] ),
		'lines'        => array(),
		'result_label' => $rx_fp_text( $rx_fp_example['result_label'] ?? '' ),
		'result_value' => $rx_fp_text( $rx_fp_example['result_value'] ?? '' ),
		'note'         => $rx_fp_text( $rx_fp_example['note'] ?? '' ),
	);
	foreach ( $rx_fp_rows( $rx_fp_example['lines'] ?? null ) as $rx_fp_row ) {
		$rx_fp_label = $rx_fp_text( $rx_fp_row['label'] ?? '' );
		$rx_fp_value = $rx_fp_text( $rx_fp_row['value'] ?? '' );
		if ( '' === $rx_fp_label && '' === $rx_fp_value ) {
			continue;
		}
		// Operator shown before the value: 'auto' is × on every line but the first.
		$rx_fp_op = isset( $rx_fp_row['op'] ) && is_string( $rx_fp_row['op'] ) && '' !== $rx_fp_row['op'] ? $rx_fp_row['op'] : 'auto';
		if ( 'auto' === $rx_fp_op ) {
			$rx_fp_op = $rx_fp_math['lines'] ? '×' : '';
		} elseif ( 'none' === $rx_fp_op ) {
			$rx_fp_op = '';
		}
		$rx_fp_math['lines'][] = array(
			'label' => $rx_fp_label,
			'value' => $rx_fp_value,
			'op'    => esc_html( $rx_fp_op ),
		);
	}
}
$rx_fp_estimator = (bool) rx_field( 'leaks_estimator' );

// Steps and shortcut keys.
$rx_fp_steps_head = $rx_fp_head( 'steps_head' );
$rx_fp_steps      = array();
foreach ( $rx_fp_rows( rx_field( 'steps' ) ) as $rx_fp_row ) {
	$rx_fp_item = array(
		'title' => $rx_fp_text( $rx_fp_row['title'] ?? '' ),
		'html'  => $rx_fp_html( $rx_fp_row['html'] ?? '' ),
		'meta'  => $rx_fp_text( $rx_fp_row['meta'] ?? '' ),
	);
	if ( implode( '', $rx_fp_item ) !== '' ) {
		$rx_fp_steps[] = $rx_fp_item;
	}
}

$rx_fp_keys = array();
foreach ( $rx_fp_rows( rx_field( 'keys' ) ) as $rx_fp_row ) {
	$rx_fp_combo = isset( $rx_fp_row['key'] ) && is_scalar( $rx_fp_row['key'] ) ? trim( do_shortcode( (string) $rx_fp_row['key'] ) ) : '';
	if ( '' === $rx_fp_combo ) {
		continue;
	}
	// "Ctrl + S" → <kbd>Ctrl</kbd> + <kbd>S</kbd>.
	$rx_fp_kbd = array();
	foreach ( explode( '+', $rx_fp_combo ) as $rx_fp_part ) {
		$rx_fp_kbd[] = '<kbd>' . esc_html( trim( $rx_fp_part ) ) . '</kbd>';
	}
	$rx_fp_keys[] = array(
		'kbd'  => implode( ' + ', $rx_fp_kbd ),
		'what' => $rx_fp_text( $rx_fp_row['what'] ?? '' ),
	);
}

// Capabilities.
$rx_fp_caps_head = $rx_fp_head( 'caps_head' );
$rx_fp_caps      = array();
foreach ( $rx_fp_rows( rx_field( 'caps' ) ) as $rx_fp_row ) {
	$rx_fp_item = array(
		'title' => $rx_fp_text( $rx_fp_row['title'] ?? '' ),
		'html'  => $rx_fp_html( $rx_fp_row['html'] ?? '' ),
	);
	if ( implode( '', $rx_fp_item ) === '' ) {
		continue;
	}
	$rx_fp_item['icon'] = isset( $rx_fp_row['icon'] ) && is_string( $rx_fp_row['icon'] ) ? trim( $rx_fp_row['icon'] ) : '';
	$rx_fp_item['plan'] = $rx_fp_plan( $rx_fp_row );
	$rx_fp_caps[]       = $rx_fp_item;
}

// Practical guide.
$rx_fp_guide_head = $rx_fp_head( 'guide_head' );
$rx_fp_guide_note = $rx_fp_text( rx_field( 'guide_note' ) );
$rx_fp_guide      = array();
foreach ( $rx_fp_rows( rx_field( 'guide' ) ) as $rx_fp_row ) {
	$rx_fp_item = array(
		'title' => $rx_fp_text( $rx_fp_row['title'] ?? '' ),
		'html'  => $rx_fp_html( $rx_fp_row['html'] ?? '' ),
	);
	if ( implode( '', $rx_fp_item ) !== '' ) {
		$rx_fp_guide[] = $rx_fp_item;
	}
}

// Demo checklist.
$rx_fp_proof_head = $rx_fp_head( 'proof_head' );
$rx_fp_proof      = array();
foreach ( $rx_fp_rows( rx_field( 'proof' ) ) as $rx_fp_row ) {
	$rx_fp_line = $rx_fp_text( $rx_fp_row['text'] ?? '' );
	if ( '' !== $rx_fp_line ) {
		$rx_fp_proof[] = $rx_fp_line;
	}
}

// Related feature pages (labels, descriptions and URLs from the primary menu).
$rx_fp_related = rx_field( 'related' );
if ( is_string( $rx_fp_related ) ) {
	$rx_fp_related = '' !== $rx_fp_related ? array( $rx_fp_related ) : array();
}
$rx_fp_related       = is_array( $rx_fp_related ) ? array_values( array_filter( $rx_fp_related, 'is_string' ) ) : array();
$rx_fp_related_title = $rx_fp_text( rx_field( 'related_title' ) );
$rx_fp_nav           = $rx_fp_related ? rx_feature_nav() : array();

// FAQ: rendered into a buffer so the section is skipped when no item is left.
$rx_fp_faq_title = $rx_fp_text( rx_field( 'faq_title' ) );
$rx_fp_faq_rows  = $rx_fp_rows( rx_field( 'faq' ) );
$rx_fp_faq       = '';
if ( $rx_fp_faq_rows ) {
	ob_start();
	get_template_part(
		'parts/faq-list',
		null,
		array(
			'items'      => $rx_fp_faq_rows,
			'ids'        => array(
				'answer' => $rx_fp_id . 'q%d',
				'button' => $rx_fp_id . 'q%db',
				'start'  => 0,
			),
			'confirm_on' => 'item',
		)
	);
	$rx_fp_faq = trim( (string) ob_get_clean() );
}
$rx_fp_sales_phone = trim( (string) rx_setting( 'sales_phone' ) );
$rx_fp_sales_tel   = trim( (string) rx_setting( 'sales_tel' ) );
if ( '' === $rx_fp_sales_tel ) {
	$rx_fp_sales_tel = (string) preg_replace( '/[^0-9+]/', '', $rx_fp_sales_phone );
}

get_header();
?>
<main id="main">
<?php get_template_part( 'parts/page-hero', null, array( 'hero' => is_array( $rx_fp_hero ) ? $rx_fp_hero : array() ) ); ?>

<?php /* ---------- CTAs + product screen ---------- */ ?>
<section class="section-tight fp-top">
  <div class="container">
    <p class="hero-ctas fp-ctas">
      <a class="btn btn-primary" href="<?php echo esc_url( $rx_fp_demo ); ?>" data-cta="demo"><?php echo '' !== $rx_fp_cta_demo ? $rx_fp_cta_demo : esc_html__( 'Book a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <?php if ( '' !== $rx_fp_signup ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( $rx_fp_signup ); ?>"><?php echo '' !== $rx_fp_cta_free ? $rx_fp_cta_free : esc_html__( 'Start free', 'pharmacy' ); ?></a><?php endif; ?>
    </p>
    <?php if ( $rx_fp_assure ) : ?>
      <ul class="hero-assure fp-assure">
        <?php foreach ( $rx_fp_assure as $rx_fp_line ) : ?><li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?php echo $rx_fp_line; ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ( '' !== $rx_fp_screen ) : ?>
      <figure class="shot fp-shot"><?php echo rx_svg_img( $rx_fp_screen, 1440, 900, sprintf( /* translators: %s: description of the product screen. */ __( 'Sample screen: %s', 'pharmacy' ), $rx_fp_alt ), 'loading="eager" fetchpriority="high"' ); ?></figure>
      
    <?php endif; ?>
  </div>
</section>

<?php /* ---------- The problem in their words: a diary of moments, not a card grid ---------- */ ?>
<?php if ( '' !== $rx_fp_scenes_head['title'] || $rx_fp_scenes ) : ?>
<section class="section" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-scenes">
  <div class="container">
    <?php echo $rx_fp_section_head( $rx_fp_scenes_head, $rx_fp_id . '-scenes' ); ?>
    <ol class="fp-diary">
      <?php foreach ( $rx_fp_scenes as $rx_fp_item ) : ?>
        <li>
          <?php if ( '' !== $rx_fp_item['time'] ) : ?><p class="fp-when"><?php echo $rx_fp_item['time']; ?></p><?php endif; ?>
          <div><h3><?php echo $rx_fp_item['title']; ?></h3><?php if ( '' !== $rx_fp_item['text'] ) : ?><p><?php echo $rx_fp_item['text']; ?></p><?php endif; ?></div>
          <?php if ( '' !== $rx_fp_item['cost'] ) : ?><p class="fp-cost"><span><?php esc_html_e( 'Cost', 'pharmacy' ); ?></span><?php echo $rx_fp_item['cost']; ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php
/* ---------- Leak register: one card per leak. The leak sits above a dashed tear line, the fix below it,
   and the plan on the card's footer line. The last card is the worked example (or a demo card on pages without one). ---------- */
?>
<?php if ( '' !== $rx_fp_leaks_head['title'] || $rx_fp_leaks ) : ?>
<section class="section bg-soft" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-leaks">
  <div class="container">
    <?php echo $rx_fp_section_head( $rx_fp_leaks_head, $rx_fp_id . '-leaks' ); ?>
    <ol class="lc-grid">
      <?php foreach ( $rx_fp_leaks as $rx_fp_i => $rx_fp_item ) : ?>
        <li class="lc">
          <p class="lc-k"><?php echo esc_html( sprintf( /* translators: %s: leak number with a leading zero, e.g. 01. */ __( 'Leak %s', 'pharmacy' ), str_pad( (string) ( $rx_fp_i + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?></p>
          <h3><?php echo $rx_fp_item['leak']; ?></h3>
          <?php if ( '' !== $rx_fp_item['how'] ) : ?><p class="lc-how"><?php echo $rx_fp_item['how']; ?></p><?php endif; ?>
          <?php if ( '' !== $rx_fp_item['plug'] ) : ?>
          <p class="lc-fix-k"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><?php esc_html_e( 'How it’s plugged', 'pharmacy' ); ?></p>
          <p class="lc-fix"><?php echo $rx_fp_item['plug']; ?></p>
          <?php endif; ?>
          <p class="lc-plan"><?php echo '' !== $rx_fp_item['plan'] ? $rx_fp_plan_line( $rx_fp_item['plan'] ) : esc_html__( 'Plan: not listed separately', 'pharmacy' ); ?></p>
        </li>
      <?php endforeach; ?>
      <?php if ( $rx_fp_math ) : ?>
        <li class="lc lc-math" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-math">
          <p class="lc-k"><?php esc_html_e( 'Worked example · illustrative', 'pharmacy' ); ?></p>
          <h3 id="<?php echo esc_attr( $rx_fp_id ); ?>-math"><?php echo $rx_fp_math['title']; ?></h3>
          <dl>
            <?php foreach ( $rx_fp_math['lines'] as $rx_fp_line ) : ?>
              <div><dt><?php echo $rx_fp_line['label']; ?></dt><dd><?php echo '' !== $rx_fp_line['op'] ? '<i aria-hidden="true">' . $rx_fp_line['op'] . '</i> ' : ''; ?><?php echo $rx_fp_line['value']; ?></dd></div>
            <?php endforeach; ?>
            <?php if ( '' !== $rx_fp_math['result_label'] || '' !== $rx_fp_math['result_value'] ) : ?>
            <div class="lc-total"><dt><?php echo $rx_fp_math['result_label']; ?></dt><dd><?php echo $rx_fp_math['result_value']; ?></dd></div>
            <?php endif; ?>
          </dl>
          <?php if ( '' !== $rx_fp_math['note'] ) : ?><p class="lc-note"><?php echo $rx_fp_math['note']; ?></p><?php endif; ?>
        </li>
      <?php else : ?>
        <li class="lc lc-cta">
          <p class="lc-k"><?php esc_html_e( 'Your store', 'pharmacy' ); ?></p>
          <h3><?php esc_html_e( 'Go through these with your own stock', 'pharmacy' ); ?></h3>
          <p><?php esc_html_e( 'Bring a few items from your shelf to a 30-minute demo and we’ll check each leak with your numbers.', 'pharmacy' ); ?></p>
          <a class="btn btn-secondary" href="<?php echo esc_url( $rx_fp_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?></a>
        </li>
      <?php endif; ?>
    </ol>
    <p class="lc-foot"><?php
		printf(
			/* translators: %s: "plan comparison" link to the pricing page. */
			esc_html__( 'Plans as listed in the current %s. Ask us in the demo about anything marked “not listed separately”.', 'pharmacy' ),
			'<a href="' . esc_url( $rx_fp_compare ) . '">' . esc_html__( 'plan comparison', 'pharmacy' ) . '</a>'
		);
		?></p>

    <?php if ( $rx_fp_estimator ) : ?>
      <div class="fp-est"><?php get_template_part( 'parts/estimator', null, array( 'cta' => $rx_fp_demo ) ); ?></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- How it works: a plain process strip ---------- */ ?>
<?php if ( '' !== $rx_fp_steps_head['title'] || $rx_fp_steps ) : ?>
<section class="section" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-steps">
  <div class="container">
    <?php echo $rx_fp_section_head( $rx_fp_steps_head, $rx_fp_id . '-steps' ); ?>
    <ol class="fp-steps">
      <?php foreach ( $rx_fp_steps as $rx_fp_i => $rx_fp_item ) : ?>
        <li>
          <p class="fp-step-k"><?php echo esc_html( sprintf( /* translators: %d: step number. */ __( 'Step %d', 'pharmacy' ), $rx_fp_i + 1 ) ); ?></p>
          <h3><?php echo $rx_fp_item['title']; ?></h3>
          <?php if ( '' !== $rx_fp_item['html'] ) : ?><p><?php echo $rx_fp_item['html']; ?></p><?php endif; ?>
          <?php if ( '' !== $rx_fp_item['meta'] ) : ?><p class="fp-step-meta"><?php echo $rx_fp_item['meta']; ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php if ( $rx_fp_keys ) : ?>
      <p class="fp-keys">
        <span><?php esc_html_e( 'Shortcuts:', 'pharmacy' ); ?></span>
        <?php foreach ( $rx_fp_keys as $rx_fp_j => $rx_fp_item ) : ?>
          <?php echo $rx_fp_j ? '<span aria-hidden="true">·</span>' : ''; ?>
          <span><?php echo $rx_fp_item['kbd']; ?><?php echo '' !== $rx_fp_item['what'] ? ' ' . $rx_fp_item['what'] : ''; ?></span>
        <?php endforeach; ?>
      </p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Capabilities: a spec sheet with a ruled grid ---------- */ ?>
<?php if ( '' !== $rx_fp_caps_head['title'] || $rx_fp_caps ) : ?>
<section class="section fp-caps-sec" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-caps">
  <div class="container">
    <div class="section-head"><?php if ( '' !== $rx_fp_caps_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fp_caps_head['eyebrow']; ?></span><?php endif; ?><h2 id="<?php echo esc_attr( $rx_fp_id ); ?>-caps"><?php echo $rx_fp_caps_head['title']; ?></h2>
      <p><?php
		echo '' !== $rx_fp_caps_head['intro'] ? $rx_fp_caps_head['intro'] . ' ' : '';
		printf(
			/* translators: %s: "plan comparison" link to the pricing page. */
			esc_html__( 'Plans come from the current %s.', 'pharmacy' ),
			'<a href="' . esc_url( $rx_fp_compare ) . '">' . esc_html__( 'plan comparison', 'pharmacy' ) . '</a>'
		);
		?></p></div>
    <ul class="fp-spec">
      <?php foreach ( $rx_fp_caps as $rx_fp_item ) : ?>
        <li>
          <h3><?php if ( '' !== $rx_fp_item['icon'] ) : ?><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $rx_fp_item['icon'] ); ?>"/></svg><?php endif; ?><?php echo $rx_fp_item['title']; ?></h3>
          <?php if ( '' !== $rx_fp_item['html'] ) : ?><p><?php echo $rx_fp_item['html']; ?></p><?php endif; ?>
          <?php if ( '' !== $rx_fp_item['plan'] ) : ?><p class="fp-spec-plan"><?php echo $rx_fp_plan_line( $rx_fp_item['plan'] ); ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Practical guide: useful context for the owner, not product claims ---------- */ ?>
<?php if ( '' !== $rx_fp_guide_head['title'] || $rx_fp_guide ) : ?>
<section class="section bg-soft" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-guide">
  <div class="container fp-guide">
    <div class="fp-guide-copy">
      <?php if ( '' !== $rx_fp_guide_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fp_guide_head['eyebrow']; ?></span><?php endif; ?>
      <h2 id="<?php echo esc_attr( $rx_fp_id ); ?>-guide"><?php echo $rx_fp_guide_head['title']; ?></h2>
      <?php if ( '' !== $rx_fp_guide_head['intro'] ) : ?><p><?php echo $rx_fp_guide_head['intro']; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_fp_guide_note ) : ?><p class="meta-line"><?php echo $rx_fp_guide_note; ?></p><?php endif; ?>
    </div>
    <ol class="fp-guide-list">
      <?php foreach ( $rx_fp_guide as $rx_fp_item ) : ?><li><h3><?php echo $rx_fp_item['title']; ?></h3><?php if ( '' !== $rx_fp_item['html'] ) : ?><p><?php echo $rx_fp_item['html']; ?></p><?php endif; ?></li><?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Proof you can check yourself: a printed-style checklist (no testimonials until verified ones exist) ---------- */ ?>
<?php if ( '' !== $rx_fp_proof_head['title'] || $rx_fp_proof ) : ?>
<section class="section" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-proof">
  <div class="container fp-proof">
    <div class="fp-proof-copy">
      <?php if ( '' !== $rx_fp_proof_head['eyebrow'] ) : ?><span class="eyebrow"><?php echo $rx_fp_proof_head['eyebrow']; ?></span><?php endif; ?>
      <h2 id="<?php echo esc_attr( $rx_fp_id ); ?>-proof"><?php echo $rx_fp_proof_head['title']; ?></h2>
      <?php if ( '' !== $rx_fp_proof_head['intro'] ) : ?><p><?php echo $rx_fp_proof_head['intro']; ?></p><?php endif; ?>
      <a class="btn btn-primary" href="<?php echo esc_url( $rx_fp_demo ); ?>" data-cta="demo"><?php esc_html_e( 'Book a demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </div>
    <?php if ( $rx_fp_proof ) : ?>
    <div class="fp-sheet">
      <p class="fp-sheet-h"><b><?php esc_html_e( 'Demo checklist', 'pharmacy' ); ?></b><span><?php echo esc_html( sprintf( /* translators: %d: number of checklist items. */ _n( '%d item · about 30 minutes', '%d items · about 30 minutes', count( $rx_fp_proof ), 'pharmacy' ), count( $rx_fp_proof ) ) ); ?></span></p>
      <ol>
        <?php foreach ( $rx_fp_proof as $rx_fp_line ) : ?><li><span class="fp-box" aria-hidden="true"></span><?php echo $rx_fp_line; ?></li><?php endforeach; ?>
      </ol>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- Related feature pages: a plain link row ---------- */ ?>
<?php if ( $rx_fp_related ) : ?>
<section class="section-tight fp-rel-sec" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-rel">
  <div class="container">
    <h2 class="fp-rel-h" id="<?php echo esc_attr( $rx_fp_id ); ?>-rel"><?php echo '' !== $rx_fp_related_title ? $rx_fp_related_title : esc_html__( 'Also at the counter', 'pharmacy' ); ?></h2>
    <ul class="fp-related">
      <?php
		foreach ( $rx_fp_related as $rx_fp_rel_key ) :
			if ( $rx_fp_rel_key === $rx_fp_key || empty( $rx_fp_nav[ $rx_fp_rel_key ] ) ) {
				continue;
			}
			$rx_fp_rel = $rx_fp_nav[ $rx_fp_rel_key ];
			?>
        <li><a href="<?php echo esc_url( $rx_fp_rel['url'] ); ?>"><b><?php echo esc_html( $rx_fp_rel['label'] ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></b><span><?php echo $rx_fp_text( $rx_fp_rel['description'] ); ?></span></a></li>
      <?php endforeach; ?>
      <li><a href="<?php echo esc_url( rx_url( 'switch' ) ); ?>"><b><?php esc_html_e( 'Switching from other software', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></b><span><?php esc_html_e( 'Bring items and stock across by CSV', 'pharmacy' ); ?></span></a></li>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php /* ---------- FAQ ---------- */ ?>
<?php if ( '' !== $rx_fp_faq ) : ?>
<section class="section bg-soft" aria-labelledby="<?php echo esc_attr( $rx_fp_id ); ?>-faq">
  <div class="container faq-grid">
    <div class="faq-intro"><span class="eyebrow"><?php esc_html_e( 'Questions', 'pharmacy' ); ?></span><h2 id="<?php echo esc_attr( $rx_fp_id ); ?>-faq"><?php echo '' !== $rx_fp_faq_title ? $rx_fp_faq_title : esc_html__( 'Asked at the counter', 'pharmacy' ); ?></h2>
      <p><?php
		$rx_fp_faq_link = '<a href="' . esc_url( rx_url( 'faq' ) ) . '">' . esc_html__( 'FAQ page', 'pharmacy' ) . '</a>';
		if ( '' !== $rx_fp_sales_phone ) {
			printf(
				/* translators: 1: "FAQ page" link, 2: sales phone number (a tel: link). */
				esc_html__( 'More answers on the %1$s, or call sales on %2$s.', 'pharmacy' ),
				$rx_fp_faq_link,
				'<a href="' . esc_url( 'tel:' . $rx_fp_sales_tel ) . '">' . esc_html( $rx_fp_sales_phone ) . '</a>'
			);
		} else {
			printf(
				/* translators: %s: "FAQ page" link. */
				esc_html__( 'More answers on the %s.', 'pharmacy' ),
				$rx_fp_faq_link
			);
		}
		?></p></div>
    <?php echo $rx_fp_faq; ?>
  </div>
</section>
<?php endif; ?>
</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
