<?php
/**
 * Front page (Home). Port of the PHP site's index.php: same sections in the
 * same order, with the same classes, ids, ARIA and data attributes, filled
 * from the Home field group (acf-json/group_rx_home.json).
 *
 * Sections:
 *  #top        hero + demo slip (parts/hero-lead, id prefix hl1)
 *  .trust      trusted strip (parts/trusted-strip)
 *  #tour       product tour (parts/product-tour)
 *  #problems   "Sound familiar?" inbox (parts/problems-chat)
 *  #estimator  expiry loss estimator (parts/estimator, button to #demo)
 *  #day        a day at the pharmacy
 *  #features   feature tabs (data-scroll-tabs, site.js)
 *  #switching  switching made safe
 *  #solutions  who it's for
 *  #checklist  buyer's checklist
 *  #support    support
 *  #faq        FAQ (parts/faq-list, ids faq-%d / faq-b%d from 1)
 *  #demo       final CTA + demo form (parts/demo-form)
 * The footer is printed without the CTA band, as on the PHP site.
 *
 * Fields are read from the page set as the front page. Plain text goes
 * through rx_text() and *_html / `html` values through rx_html(), so
 * shortcodes such as [rx_brand] or [rx_free_plan] resolve on output. An
 * item whose `confirmed` flag is off gets data-confirm. Elements whose value
 * renders empty are not printed, and a section with nothing left in it is
 * skipped. The hero, estimator and demo sections always print: they hold
 * the page's forms and tools.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built.

/*
 * Fields of the front page. Nothing when the front page is not a page
 * (Settings → Reading shows the latest posts): the parts then use their
 * own default copy and the repeater sections are skipped.
 */
$rx_fp_id = is_page() ? (int) get_queried_object_id() : 0;
$rx_fp    = static function ( string $name ) use ( $rx_fp_id ) {
	return $rx_fp_id ? rx_field( $name, $rx_fp_id ) : null;
};

/**
 * Rows of a repeater value (an empty list when unset or not an array).
 *
 * @param mixed $value Repeater value.
 * @return array[]
 */
$rx_fp_rows = static function ( $value ): array {
	return is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
};

/**
 * Whether a row or flag counts as confirmed: missing or null (SCF
 * inactive) counts as confirmed, as the field defaults to on.
 *
 * @param mixed $value `confirmed` value.
 */
$rx_fp_ok = static function ( $value ): bool {
	return null === $value || (bool) $value;
};

/**
 * Heading lines of a section from a `*_head` group: eyebrow, <h2> and the
 * intro paragraph, each printed only when it renders non-empty.
 *
 * @param mixed $head    Group value: [ eyebrow, title, intro ] or [ …, intro_html ].
 * @param array $options Optional:
 *                       - tag   string Eyebrow element, 'span' (default) or 'p'.
 *                       - class string Eyebrow class (default 'eyebrow').
 *                       - id    string Id of the <h2>.
 * @return array{html: string, title: string} Markup (one line per element) and the rendered title.
 */
$rx_fp_head = static function ( $head, array $options = array() ): array {
	$head    = is_array( $head ) ? $head : array();
	$tag     = 'p' === ( $options['tag'] ?? 'span' ) ? 'p' : 'span';
	$class   = (string) ( $options['class'] ?? 'eyebrow' );
	$id      = (string) ( $options['id'] ?? '' );
	$eyebrow = trim( rx_text( $head['eyebrow'] ?? '' ) );
	$title   = trim( rx_text( $head['title'] ?? '' ) );
	$intro   = array_key_exists( 'intro_html', $head ) ? trim( rx_html( $head['intro_html'] ) ) : trim( rx_text( $head['intro'] ?? '' ) );

	$lines = array();
	if ( '' !== $eyebrow ) {
		$lines[] = '<' . $tag . ' class="' . esc_attr( $class ) . '">' . $eyebrow . '</' . $tag . '>';
	}
	if ( '' !== $title ) {
		$lines[] = '<h2' . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>' . $title . '</h2>';
	}
	if ( '' !== $intro ) {
		$lines[] = '<p>' . $intro . '</p>';
	}
	return array(
		'html'  => implode( "\n", $lines ),
		'title' => $title,
	);
};

/**
 * Sprite icon markup.
 *
 * @param mixed $id Symbol id, e.g. 'i-check'.
 */
$rx_fp_icon = static function ( $id ): string {
	$id = is_string( $id ) ? trim( $id ) : '';
	return '' === $id ? '' : '<svg class="icon" aria-hidden="true"><use href="#' . esc_attr( $id ) . '"/></svg>';
};

get_header();
?>

<main id="main">
<?php
/*
 * ---------------------------------------------------------------------
 * Hero (#top): promise, proof links and the demo slip.
 * ---------------------------------------------------------------------
 */
$rx_hero_eyebrow = trim( rx_text( $rx_fp( 'hero_eyebrow' ) ) );
$rx_hero_title   = trim( rx_html( $rx_fp( 'hero_title_html' ) ) );
$rx_hero_lead    = trim( rx_html( $rx_fp( 'hero_lead_html' ) ) );
$rx_hero_tour    = trim( rx_text( $rx_fp( 'hero_tour_label' ) ) );
$rx_hero_free    = trim( rx_text( $rx_fp( 'hero_free_label' ) ) );
$rx_hero_signup  = trim( (string) rx_setting( 'app_signup' ) );
if ( '' === $rx_hero_title ) {
	// Empty heading: the page title, else the Site Title.
	$rx_hero_title = esc_html( $rx_fp_id ? get_the_title( $rx_fp_id ) : rx_brand() );
}
?>
  <section class="hero3" id="top">
    <div class="container hero3-grid">
      <div class="hero3-copy">
        <?php if ( '' !== $rx_hero_eyebrow ) : ?><p class="eyebrow"><?php echo $rx_hero_eyebrow; ?></p><?php endif; ?>
        <?php if ( '' !== $rx_hero_title ) : ?><h1><?php echo $rx_hero_title; ?></h1><?php endif; ?>
        <?php if ( '' !== $rx_hero_lead ) : ?><p class="hero-lead"><?php echo $rx_hero_lead; ?></p><?php endif; ?>
        <?php if ( '' !== $rx_hero_tour || ( '' !== $rx_hero_free && '' !== $rx_hero_signup ) ) : ?>
        <p class="hero3-alt">
          <?php if ( '' !== $rx_hero_tour ) : ?><a href="#tour"><?php echo $rx_fp_icon( 'i-play' ); ?><?php echo $rx_hero_tour; ?></a><?php endif; ?>
          <?php if ( '' !== $rx_hero_free && '' !== $rx_hero_signup ) : ?><a href="<?php echo esc_url( $rx_hero_signup ); ?>"><?php echo $rx_hero_free; ?> <?php echo $rx_fp_icon( 'i-arrow' ); ?></a><?php endif; ?>
        </p>
        <?php endif; ?>
      </div>
      <div class="hero3-form">
        <?php get_template_part( 'parts/hero-lead', null, array( 'id' => 'hl1' ) ); ?>
      </div>
    </div>
  </section>

<?php
/*
 * ---------------------------------------------------------------------
 * Trusted strip, product tour (#tour) and the "Sound familiar?" inbox
 * (#problems). Each part prints its own section, or nothing.
 * ---------------------------------------------------------------------
 */
get_template_part(
	'parts/trusted-strip',
	null,
	array(
		'title' => $rx_fp( 'trusted_title' ),
		'note'  => $rx_fp( 'trusted_note' ),
	)
);

get_template_part(
	'parts/product-tour',
	null,
	array(
		'eyebrow'    => $rx_fp( 'tour_eyebrow' ),
		'title_html' => $rx_fp( 'tour_title_html' ),
		'intro'      => $rx_fp( 'tour_intro' ),
	)
);

get_template_part(
	'parts/problems-chat',
	null,
	array(
		'items' => $rx_fp( 'problems' ),
		'head'  => $rx_fp( 'problems_head' ),
		'note'  => $rx_fp( 'problems_note' ),
	)
);
?>

  <section class="section" id="estimator" aria-labelledby="est-title">
    <div class="container">
      <?php get_template_part( 'parts/estimator', null, array( 'cta' => '#demo' ) ); ?>
    </div>
  </section>

<?php
/*
 * ---------------------------------------------------------------------
 * A day at the pharmacy (#day): a shift register.
 * ---------------------------------------------------------------------
 */
$rx_day_head = $rx_fp_head( $rx_fp( 'day_head' ), array( 'tag' => 'p' ) );
$rx_day_rows = array();
foreach ( $rx_fp_rows( $rx_fp( 'day' ) ) as $rx_row ) {
	$rx_item = array(
		'time'  => trim( rx_text( $rx_row['time'] ?? '' ) ),
		'ampm'  => trim( rx_text( $rx_row['ampm'] ?? '' ) ),
		'title' => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'text'  => trim( rx_text( $rx_row['text'] ?? '' ) ),
	);
	if ( '' !== $rx_item['title'] || '' !== $rx_item['text'] ) {
		$rx_day_rows[] = $rx_item;
	}
}
if ( '' !== $rx_day_head['html'] || $rx_day_rows ) :
	?>
  <section class="section" id="day">
    <div class="container">
      <?php if ( '' !== $rx_day_head['html'] ) : ?>
      <div class="section-head">
        <?php echo $rx_day_head['html']; ?>
      </div>
      <?php endif; ?>
      <?php if ( $rx_day_rows ) : ?>
      <ol class="shift">
        <?php foreach ( $rx_day_rows as $rx_item ) : ?>
        <li>
          <?php if ( '' !== $rx_item['time'] ) : ?><time><?php echo $rx_item['time']; ?><?php if ( '' !== $rx_item['ampm'] ) : ?><small><?php echo $rx_item['ampm']; ?></small><?php endif; ?></time><?php endif; ?>
          <?php if ( '' !== $rx_item['title'] ) : ?><h3><?php echo $rx_item['title']; ?></h3><?php endif; ?>
          <?php if ( '' !== $rx_item['text'] ) : ?><p><?php echo $rx_item['text']; ?></p><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Feature tabs (#features). On wide screens the tabs stick and advance
 * as you scroll (data-scroll-tabs, site.js).
 * ---------------------------------------------------------------------
 */
$rx_feat_head = $rx_fp_head( $rx_fp( 'features_head' ) );
$rx_feat_tabs = array();
foreach ( $rx_fp_rows( $rx_fp( 'tabs' ) ) as $rx_row ) {
	$rx_label = trim( rx_text( $rx_row['label'] ?? '' ) );
	if ( '' === $rx_label ) {
		continue;
	}
	$rx_slug   = sanitize_title( is_string( $rx_row['slug'] ?? null ) ? $rx_row['slug'] : '' );
	$rx_points = array();
	foreach ( $rx_fp_rows( $rx_row['points'] ?? null ) as $rx_point ) {
		$rx_point_html = trim( rx_html( $rx_point['html'] ?? '' ) );
		if ( '' !== $rx_point_html ) {
			$rx_points[] = $rx_point_html;
		}
	}
	$rx_screen      = is_string( $rx_row['screen'] ?? null ) ? sanitize_file_name( $rx_row['screen'] ) : '';
	$rx_feat_tabs[] = array(
		'slug'   => '' !== $rx_slug ? $rx_slug : 'feature-' . ( count( $rx_feat_tabs ) + 1 ),
		'icon'   => $rx_row['icon'] ?? '',
		'label'  => $rx_label,
		'title'  => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'lead'   => trim( rx_text( $rx_row['lead'] ?? '' ) ),
		'points' => $rx_points,
		'screen' => '' !== $rx_screen ? rx_svg_img( $rx_screen, 1440, 900, (string) ( $rx_row['screen_alt'] ?? '' ) ) : '',
	);
}
if ( '' !== $rx_feat_head['html'] || $rx_feat_tabs ) :
	?>
  <section class="section" id="features">
    <div class="container">
      <?php if ( '' !== $rx_feat_head['html'] ) : ?>
      <div class="section-head">
        <?php echo $rx_feat_head['html']; ?>
      </div>
      <?php endif; ?>
      <?php if ( $rx_feat_tabs ) : ?>
      <div class="feat-track" data-scroll-tabs>
      <div class="tabs">
        <div class="tablist" role="tablist" aria-label="<?php esc_attr_e( 'Product features', 'pharmacy' ); ?>">
          <?php foreach ( $rx_feat_tabs as $rx_i => $rx_tab ) : ?>
          <button class="tab" role="tab" id="tab-<?php echo esc_attr( $rx_tab['slug'] ); ?>" aria-controls="panel-<?php echo esc_attr( $rx_tab['slug'] ); ?>" aria-selected="<?php echo 0 === $rx_i ? 'true' : 'false'; ?>"<?php echo 0 === $rx_i ? '' : ' tabindex="-1"'; ?>><?php echo $rx_fp_icon( $rx_tab['icon'] ); ?><?php echo $rx_tab['label']; ?></button>
          <?php endforeach; ?>
        </div>

        <div>
          <?php foreach ( $rx_feat_tabs as $rx_i => $rx_tab ) : ?>
          <div class="panel<?php echo '' !== $rx_tab['screen'] ? ' panel-shot' : ''; ?>" role="tabpanel" id="panel-<?php echo esc_attr( $rx_tab['slug'] ); ?>" aria-labelledby="tab-<?php echo esc_attr( $rx_tab['slug'] ); ?>"<?php echo 0 === $rx_i ? '' : ' hidden'; ?>>
            <div>
              <?php if ( '' !== $rx_tab['title'] ) : ?><h3><?php echo $rx_tab['title']; ?></h3><?php endif; ?>
              <?php if ( '' !== $rx_tab['lead'] ) : ?><p><?php echo $rx_tab['lead']; ?></p><?php endif; ?>
              <?php if ( $rx_tab['points'] ) : ?>
              <ul class="checks">
                <?php foreach ( $rx_tab['points'] as $rx_point_html ) : ?>
                <li><?php echo $rx_fp_icon( 'i-check-c' ); ?><span><?php echo $rx_point_html; ?></span></li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </div>
            <?php if ( '' !== $rx_tab['screen'] ) : ?><figure class="shot"><?php echo $rx_tab['screen']; ?></figure><?php endif; ?>
          </div>

          <?php endforeach; ?>
        </div>
      </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Switching made safe (#switching).
 * ---------------------------------------------------------------------
 */
$rx_sw_head  = $rx_fp_head( $rx_fp( 'switching_head' ) );
$rx_sw_moved = trim( rx_text( $rx_fp( 'moved_title' ) ) );
$rx_sw_items = array();
foreach ( $rx_fp_rows( $rx_fp( 'moved' ) ) as $rx_row ) {
	$rx_text = trim( rx_text( $rx_row['text'] ?? '' ) );
	if ( '' !== $rx_text ) {
		$rx_sw_items[] = $rx_text;
	}
}
$rx_sw_from = array();
foreach ( $rx_fp_rows( $rx_fp( 'from' ) ) as $rx_row ) {
	$rx_html = trim( rx_html( $rx_row['html'] ?? '' ) );
	if ( '' !== $rx_html ) {
		$rx_sw_from[] = array(
			'html' => $rx_html,
			'ok'   => $rx_fp_ok( $rx_row['confirmed'] ?? null ),
		);
	}
}
$rx_sw_button = trim( rx_text( $rx_fp( 'switch_button' ) ) );
$rx_sw_steps  = array();
foreach ( $rx_fp_rows( $rx_fp( 'switch_steps' ) ) as $rx_row ) {
	$rx_item = array(
		'title' => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'text'  => trim( rx_text( $rx_row['text'] ?? '' ) ),
		'when'  => trim( rx_text( $rx_row['when'] ?? '' ) ),
	);
	if ( '' !== $rx_item['title'] || '' !== $rx_item['text'] ) {
		$rx_sw_steps[] = $rx_item;
	}
}
if ( '' !== $rx_sw_head['html'] || $rx_sw_items || $rx_sw_from || $rx_sw_steps ) :
	?>
  <section class="section bg-soft" id="switching">
    <div class="container switch-grid">
      <div class="switch-copy">
        <?php echo $rx_sw_head['html']; ?>
        <?php if ( '' !== $rx_sw_moved || $rx_sw_items ) : ?>
        <div class="moved">
          <?php if ( '' !== $rx_sw_moved ) : ?><h3><?php echo $rx_sw_moved; ?></h3><?php endif; ?>
          <?php if ( $rx_sw_items ) : ?>
          <ul>
            <?php foreach ( $rx_sw_items as $rx_text ) : ?>
            <li><?php echo $rx_fp_icon( 'i-check' ); ?><?php echo $rx_text; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php foreach ( $rx_sw_from as $rx_item ) : ?>
        <p class="from"<?php echo $rx_item['ok'] ? '' : ' data-confirm'; ?>><?php echo $rx_item['html']; ?></p>
        <?php endforeach; ?>
        <?php if ( '' !== $rx_sw_button ) : ?>
        <p class="switch-cta">
          <a class="btn btn-primary" href="#demo" data-cta="demo"><?php echo $rx_sw_button; ?> <?php echo $rx_fp_icon( 'i-arrow' ); ?></a>
        </p>
        <?php endif; ?>
      </div>
      <?php if ( $rx_sw_steps ) : ?>
      <ol class="steps">
        <?php foreach ( $rx_sw_steps as $rx_item ) : ?>
        <li class="step">
          <div>
            <?php if ( '' !== $rx_item['title'] ) : ?><h3><?php echo $rx_item['title']; ?></h3><?php endif; ?>
            <?php if ( '' !== $rx_item['text'] ) : ?><p><?php echo $rx_item['text']; ?></p><?php endif; ?><?php if ( '' !== $rx_item['when'] ) : ?><span class="when"><?php echo $rx_item['when']; ?></span><?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Who it's for (#solutions).
 * ---------------------------------------------------------------------
 */
$rx_sol_head  = $rx_fp_head( $rx_fp( 'solutions_head' ) );
$rx_sol_cards = array();
foreach ( $rx_fp_rows( $rx_fp( 'solutions' ) ) as $rx_row ) {
	$rx_checks = array();
	foreach ( $rx_fp_rows( $rx_row['checks'] ?? null ) as $rx_check ) {
		$rx_text = trim( rx_text( $rx_check['text'] ?? '' ) );
		if ( '' !== $rx_text ) {
			$rx_checks[] = $rx_text;
		}
	}
	$rx_item = array(
		'icon'     => $rx_row['icon'] ?? '',
		'who'      => trim( rx_text( $rx_row['who'] ?? '' ) ),
		'title'    => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'text'     => trim( rx_text( $rx_row['text'] ?? '' ) ),
		'featured' => ! empty( $rx_row['featured'] ),
		'checks'   => $rx_checks,
	);
	if ( '' !== $rx_item['title'] || '' !== $rx_item['text'] ) {
		$rx_sol_cards[] = $rx_item;
	}
}
if ( '' !== $rx_sol_head['html'] || $rx_sol_cards ) :
	?>
  <section class="section" id="solutions">
    <div class="container">
      <?php if ( '' !== $rx_sol_head['html'] ) : ?>
      <div class="section-head center">
        <?php echo $rx_sol_head['html']; ?>
      </div>
      <?php endif; ?>
      <?php if ( $rx_sol_cards ) : ?>
      <div class="sol-grid">
        <?php foreach ( $rx_sol_cards as $rx_item ) : ?>
        <article class="sol<?php echo $rx_item['featured'] ? ' featured' : ''; ?>">
          <?php if ( '' !== $rx_fp_icon( $rx_item['icon'] ) ) : ?><div class="sol-ic"><?php echo $rx_fp_icon( $rx_item['icon'] ); ?></div><?php endif; ?>
          <?php if ( '' !== $rx_item['who'] ) : ?><p class="who"><?php echo $rx_item['who']; ?></p><?php endif; ?>
          <?php if ( '' !== $rx_item['title'] ) : ?><h3><?php echo $rx_item['title']; ?></h3><?php endif; ?>
          <?php if ( '' !== $rx_item['text'] ) : ?><p><?php echo $rx_item['text']; ?></p><?php endif; ?>
          <?php if ( $rx_item['checks'] ) : ?>
          <ul class="checks">
            <?php foreach ( $rx_item['checks'] as $rx_text ) : ?>
            <li><?php echo $rx_fp_icon( 'i-check' ); ?><?php echo $rx_text; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Buyer's checklist (#checklist).
 * ---------------------------------------------------------------------
 */
$rx_ck_head = $rx_fp_head( $rx_fp( 'checklist_head' ) );
$rx_ck_cols = array();
foreach ( $rx_fp_rows( $rx_fp( 'checklist_cols' ) ) as $rx_row ) {
	$rx_ck_cols[] = trim( rx_text( $rx_row['label'] ?? '' ) );
}
if ( '' === implode( '', $rx_ck_cols ) ) {
	$rx_ck_cols = array();
}
$rx_ck_rows = array();
foreach ( $rx_fp_rows( $rx_fp( 'checklist' ) ) as $rx_row ) {
	$rx_item = array(
		'ask'    => trim( rx_text( $rx_row['ask'] ?? '' ) ),
		'why'    => trim( rx_text( $rx_row['why'] ?? '' ) ),
		'answer' => trim( rx_html( $rx_row['answer_html'] ?? '' ) ),
		'ok'     => $rx_fp_ok( $rx_row['confirmed'] ?? null ),
	);
	if ( '' !== $rx_item['ask'] ) {
		$rx_ck_rows[] = $rx_item;
	}
}
$rx_ck_foot      = trim( rx_html( $rx_fp( 'checklist_foot_html' ) ) );
$rx_ck_button    = trim( rx_text( $rx_fp( 'checklist_button' ) ) );
$rx_ck_button_ok = $rx_fp_ok( $rx_fp( 'checklist_button_confirmed' ) );
if ( '' !== $rx_ck_head['html'] || $rx_ck_rows ) :
	?>
  <section class="section bg-soft" id="checklist">
    <div class="container">
      <?php if ( '' !== $rx_ck_head['html'] ) : ?>
      <div class="section-head">
        <?php echo $rx_ck_head['html']; ?>
      </div>
      <?php endif; ?>
      <?php if ( $rx_ck_rows ) : ?>
      <table class="check-table">
        <?php if ( $rx_ck_cols ) : ?>
        <thead>
          <tr>
            <?php foreach ( $rx_ck_cols as $rx_text ) : ?>
            <th scope="col"><?php echo $rx_text; ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <?php endif; ?>
        <tbody>
          <?php foreach ( $rx_ck_rows as $rx_item ) : ?>
          <tr<?php echo $rx_item['ok'] ? '' : ' data-confirm'; ?>>
            <td><?php echo $rx_item['ask']; ?></td>
            <td><?php echo $rx_item['why']; ?></td>
            <td><?php echo '' !== $rx_item['answer'] ? $rx_fp_icon( 'i-check-c' ) . $rx_item['answer'] : ''; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
      <?php if ( '' !== $rx_ck_foot || '' !== $rx_ck_button ) : ?>
      <div class="check-foot">
        <?php if ( '' !== $rx_ck_foot ) : ?><p><?php echo $rx_ck_foot; ?></p><?php endif; ?>
        <?php if ( '' !== $rx_ck_button ) : ?><a class="btn btn-secondary" href="#demo" data-cta="checklist"<?php echo $rx_ck_button_ok ? '' : ' data-confirm'; ?>><?php echo $rx_fp_icon( 'i-file' ); ?><?php echo $rx_ck_button; ?></a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Support (#support).
 * ---------------------------------------------------------------------
 */
$rx_sp_head   = $rx_fp_head( $rx_fp( 'support_head' ) );
$rx_sp_points = array();
foreach ( $rx_fp_rows( $rx_fp( 'support_points' ) ) as $rx_row ) {
	$rx_html = trim( rx_html( $rx_row['html'] ?? '' ) );
	if ( '' !== $rx_html ) {
		$rx_sp_points[] = array(
			'html' => $rx_html,
			'ok'   => $rx_fp_ok( $rx_row['confirmed'] ?? null ),
		);
	}
}
$rx_sp_channels = array();
foreach ( $rx_fp_rows( $rx_fp( 'channels' ) ) as $rx_row ) {
	$rx_item = array(
		'icon'  => $rx_row['icon'] ?? '',
		'title' => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'html'  => trim( rx_html( $rx_row['html'] ?? '' ) ),
	);
	if ( '' !== $rx_item['title'] || '' !== $rx_item['html'] ) {
		$rx_sp_channels[] = $rx_item;
	}
}
if ( '' !== $rx_sp_head['html'] || $rx_sp_points || $rx_sp_channels ) :
	?>
  <section class="section" id="support">
    <div class="container support-grid">
      <div class="support-copy">
        <?php echo $rx_sp_head['html']; ?>
        <?php if ( $rx_sp_points ) : ?>
        <ul class="checks">
          <?php foreach ( $rx_sp_points as $rx_item ) : ?>
          <li<?php echo $rx_item['ok'] ? '' : ' data-confirm'; ?>><?php echo $rx_fp_icon( 'i-check-c' ); ?><span><?php echo $rx_item['html']; ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <?php if ( $rx_sp_channels ) : ?>
      <div class="channels">
        <?php foreach ( $rx_sp_channels as $rx_item ) : ?>
        <article class="channel">
          <?php if ( '' !== $rx_fp_icon( $rx_item['icon'] ) ) : ?><div class="sol-ic"><?php echo $rx_fp_icon( $rx_item['icon'] ); ?></div><?php endif; ?>
          <?php if ( '' !== $rx_item['title'] ) : ?><h3><?php echo $rx_item['title']; ?></h3><?php endif; ?>
          <?php if ( '' !== $rx_item['html'] ) : ?><p><?php echo $rx_item['html']; ?></p><?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * FAQ (#faq): intro with a call button, then the accordion.
 * ---------------------------------------------------------------------
 */
$rx_faq_head  = $rx_fp_head( $rx_fp( 'faq_head' ) );
$rx_faq_label = trim( rx_text( $rx_fp( 'faq_call_label' ) ) );
$rx_faq_tel   = trim( (string) rx_setting( 'support_tel' ) );
if ( '' === $rx_faq_tel ) {
	$rx_faq_tel = (string) preg_replace( '/[^0-9+]/', '', (string) rx_setting( 'support_phone' ) );
}
ob_start();
get_template_part(
	'parts/faq-list',
	null,
	array(
		'items' => $rx_fp( 'faq' ),
		'ids'   => array(
			'answer' => 'faq-%d',
			'button' => 'faq-b%d',
			'start'  => 1,
		),
	)
);
$rx_faq_list = trim( (string) ob_get_clean() );
if ( '' !== $rx_faq_head['html'] || '' !== $rx_faq_list ) :
	?>
  <section class="section" id="faq">
    <div class="container faq-grid">
      <div class="faq-intro">
        <?php echo $rx_faq_head['html']; ?>
        <?php if ( '' !== $rx_faq_label && '' !== $rx_faq_tel ) : ?><a class="btn btn-secondary" href="<?php echo esc_url( 'tel:' . $rx_faq_tel ); ?>"><?php echo $rx_fp_icon( 'i-phone' ); ?><?php echo $rx_faq_label; ?></a><?php endif; ?>
      </div>
      <?php echo $rx_faq_list; ?>
    </div>
  </section>
	<?php
endif;

/*
 * ---------------------------------------------------------------------
 * Final CTA + demo form (#demo).
 * ---------------------------------------------------------------------
 */
$rx_demo_head  = $rx_fp_head(
	$rx_fp( 'demo_head' ),
	array(
		'class' => 'eyebrow light',
		'id'    => 'demo-title',
	)
);
$rx_demo_html  = trim( rx_html( $rx_fp( 'demo_html' ) ) );
$rx_demo_steps = array();
foreach ( $rx_fp_rows( $rx_fp( 'demo_steps' ) ) as $rx_row ) {
	$rx_item = array(
		'title' => trim( rx_text( $rx_row['title'] ?? '' ) ),
		'text'  => trim( rx_text( $rx_row['text'] ?? '' ) ),
	);
	if ( '' !== $rx_item['title'] || '' !== $rx_item['text'] ) {
		$rx_demo_steps[] = $rx_item;
	}
}
$rx_demo_ok = $rx_fp_ok( $rx_fp( 'demo_steps_confirmed' ) );

/*
 * Form copy: an empty field keeps the form's default text (null), so the
 * form never loses its heading.
 */
$rx_demo_form = array();
foreach (
	array(
		'title'         => 'form_title',
		'sub'           => 'form_sub',
		'success_title' => 'form_success',
	) as $rx_arg => $rx_name
) {
	$rx_value                = $rx_fp( $rx_name );
	$rx_demo_form[ $rx_arg ] = is_string( $rx_value ) && '' !== trim( $rx_value ) ? $rx_value : null;
}
?>
  <section class="final" id="demo"<?php echo '' !== $rx_demo_head['title'] ? ' aria-labelledby="demo-title"' : ''; ?>>
    <div class="container final-grid">
      <div class="final-copy">
        <?php echo $rx_demo_head['html']; ?>
        <?php if ( '' !== $rx_demo_html ) : ?><p><?php echo $rx_demo_html; ?></p><?php endif; ?>
        <?php if ( $rx_demo_steps ) : ?>
        <ol class="next"<?php echo $rx_demo_ok ? '' : ' data-confirm'; ?>>
          <?php foreach ( $rx_demo_steps as $rx_item ) : ?>
          <li>
            <div><?php if ( '' !== $rx_item['title'] ) : ?><b><?php echo $rx_item['title']; ?></b><?php endif; ?><?php echo $rx_item['text']; ?></div>
          </li>
          <?php endforeach; ?>
        </ol>
        <?php endif; ?>
      </div>

      <?php get_template_part( 'parts/demo-form', null, $rx_demo_form ); ?>
    </div>
  </section>

</main>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer( null, array( 'hide_cta' => true ) );
