<?php
/**
 * Article components: one renderer per layout, the single source of the markup of every
 * component in a guide (In short, notes, tables, comparison, worked sum, rule, checklist, steps, CTA).
 *
 * Two things call it, so a component looks the same whichever way a guide is written:
 * - parts/blog-layouts.php  the flexible-content `body` of the six imported guides;
 * - inc/blog-blocks.php     the Gutenberg blocks (healthray/*) of new guides.
 *
 * Each renderer takes a row (the field names documented in parts/blog-layouts.php) and returns
 * the HTML, or '' when it has nothing to show.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/**
 * The renderers, keyed by layout name.
 *
 * @return array<string, callable>
 */
function rx_blog_components(): array {
	static $render = null;
	if ( null !== $render ) {
		return $render;
	}

/** A scalar field value as a string ('' for null, booleans and arrays). */
$rx_bl_str = static function ( $v ): string {
	return is_scalar( $v ) && ! is_bool( $v ) ? (string) $v : '';
};

/** Repeater rows: a list of arrays (an empty repeater is false or ''). */
$rx_bl_list = static function ( $v ): array {
	return is_array( $v ) ? array_values( array_filter( $v, 'is_array' ) ) : array();
};

/** One entry per line, each trimmed; trailing blank lines dropped. */
$rx_bl_lines = static function ( $v ) use ( $rx_bl_str ): array {
	$v = rtrim( $rx_bl_str( $v ) );
	if ( '' === trim( $v ) ) {
		return array();
	}
	return array_map( 'trim', (array) preg_split( '/\r\n|\r|\n/', $v ) );
};

/** HTML that is not just whitespace. */
$rx_bl_has = static function ( string $html ): bool {
	return '' !== trim( $html );
};

$rx_bl_arrow = ' <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>';

$rx_bl_render = array(

	// Paragraphs and lists as written.
	'text'      => static function ( array $r ) use ( $rx_bl_has ): string {
		$html = rx_html( $r['html'] ?? '' );
		return $rx_bl_has( $html ) ? $html : '';
	},

	// Section heading; rx_blog_toc() adds the id later.
	'heading'   => static function ( array $r ): string {
		$text = trim( rx_text( $r['text'] ?? '' ) );
		return '' !== $text ? '<h2>' . $text . '</h2>' : '';
	},

	// bk_takeaways(): "In short" box.
	'takeaways' => static function ( array $r ) use ( $rx_bl_list, $rx_bl_has ): string {
		$li = '';
		foreach ( $rx_bl_list( $r['items'] ?? null ) as $item ) {
			$html = rx_html( $item['html'] ?? '' );
			if ( $rx_bl_has( $html ) ) {
				$li .= '<li>' . $html . '</li>';
			}
		}
		if ( '' === $li ) {
			return '';
		}
		return '<aside class="bk-take" aria-label="' . esc_attr__( 'In short', 'pharmacy' ) . '"><p class="bk-take-h">' . esc_html__( 'In short', 'pharmacy' ) . '</p><ol>' . $li . '</ol></aside>';
	},

	// bk_note(): tip | note | caution | example.
	'note'      => static function ( array $r ) use ( $rx_bl_str, $rx_bl_has ): string {
		$map  = array(
			'tip'     => array( __( 'Tip', 'pharmacy' ), 'i-check-c' ),
			'note'    => array( __( 'Note', 'pharmacy' ), 'i-file' ),
			'caution' => array( __( 'Watch out', 'pharmacy' ), 'i-alert' ),
			'example' => array( __( 'Example', 'pharmacy' ), 'i-rupee' ),
			'verdict' => array( __( 'Verdict', 'pharmacy' ), 'i-check-c' ),
		);
		$kind = $rx_bl_str( $r['kind'] ?? '' );
		$kind = isset( $map[ $kind ] ) ? $kind : 'note';

		$title = rx_text( $r['title'] ?? '' );
		$html  = rx_html( $r['html'] ?? '' );
		if ( '' === $title && ! $rx_bl_has( $html ) ) {
			return '';
		}
		return '<aside class="bk-note bk-note-' . $kind . '"><p class="bk-note-h"><svg class="icon" aria-hidden="true"><use href="#' . $map[ $kind ][1] . '"/></svg>'
			. '<span class="bk-note-k">' . esc_html( $map[ $kind ][0] ) . '</span>' . ( '' !== $title ? '<b>' . $title . '</b>' : '' ) . '</p>'
			. '<div class="bk-note-b">' . $html . '</div></aside>';
	},

	// bk_table(): column headings, rows with a row header, optional highlighted and number columns.
	'table'     => static function ( array $r ) use ( $rx_bl_str, $rx_bl_list, $rx_bl_lines ): string {
		$head = $rx_bl_lines( $r['head'] ?? '' );

		$hl  = is_numeric( $r['highlight_col'] ?? null ) ? (int) $r['highlight_col'] : null;
		$num = array();
		foreach ( (array) preg_split( '/[\s,;]+/', $rx_bl_str( $r['numeric_cols'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY ) as $col ) {
			if ( ctype_digit( (string) $col ) ) {
				$num[] = (int) $col;
			}
		}
		$cls = static function ( int $i ) use ( $hl, $num ): string {
			$k = trim( ( $i === $hl ? 'is-hl ' : '' ) . ( in_array( $i, $num, true ) ? 'is-num' : '' ) );
			return '' !== $k ? ' class="' . $k . '"' : '';
		};

		$th = '';
		foreach ( $head as $i => $cell ) {
			$th .= '<th scope="col"' . $cls( $i ) . '>' . rx_text( $cell ) . '</th>';
		}

		$tb = '';
		foreach ( $rx_bl_list( $r['rows'] ?? null ) as $row ) {
			$cells = $rx_bl_lines( $row['cells'] ?? '' );
			if ( ! $cells ) {
				continue;
			}
			$tb .= '<tr>';
			foreach ( $cells as $i => $cell ) {
				$tb .= 0 === $i ? '<th scope="row">' . rx_html( $cell ) . '</th>' : '<td' . $cls( $i ) . '>' . rx_html( $cell ) . '</td>';
			}
			$tb .= '</tr>';
		}

		if ( '' === $th && '' === $tb ) {
			return '';
		}

		$caption = rx_text( $r['caption'] ?? '' );
		return '<figure class="bk-table"><div class="bk-table-scroll" tabindex="0" role="region" aria-label="' . ( '' !== $caption ? $caption : esc_attr__( 'Table', 'pharmacy' ) ) . '"><table>'
			. '<thead><tr>' . $th . '</tr></thead><tbody>' . $tb . '</tbody></table></div>'
			. ( '' !== $caption ? '<figcaption>' . $caption . '</figcaption>' : '' ) . '</figure>';
	},

	// bk_compare(): two options side by side, optional winner and verdict.
	'compare'   => static function ( array $r ) use ( $rx_bl_str, $rx_bl_list, $rx_bl_has ): string {
		$rows = array();
		foreach ( $rx_bl_list( $r['rows'] ?? null ) as $row ) {
			$cells = array(
				'label' => rx_text( $row['label'] ?? '' ),
				'a'     => rx_html( $row['a_html'] ?? '' ),
				'b'     => rx_html( $row['b_html'] ?? '' ),
			);
			if ( '' !== $cells['label'] || $rx_bl_has( $cells['a'] ) || $rx_bl_has( $cells['b'] ) ) {
				$rows[] = $cells;
			}
		}
		$win = $rx_bl_str( $r['winner'] ?? '' );

		$col = static function ( string $k ) use ( $r, $rows, $win ): string {
			$name   = rx_text( $r[ $k . '_name' ] ?? '' );
			$tag    = rx_text( $r[ $k . '_tag' ] ?? '' );
			$is_win = $win === $k;
			$li     = '';
			foreach ( $rows as $row ) {
				$li .= '<li><span class="bk-cmp-l">' . $row['label'] . '</span><span>' . $row[ $k ] . '</span></li>';
			}
			return '<div class="bk-cmp-col' . ( $is_win ? ' is-win' : '' ) . '"><p class="bk-cmp-h"><b>' . $name . '</b>'
				. ( '' !== $tag ? '<span class="b-pill ' . ( $is_win ? 'b-pill-teal' : 'b-pill-slate' ) . '">' . $tag . '</span>' : '' ) . '</p><ul>' . $li . '</ul></div>';
		};

		if ( ! $rows && '' === rx_text( $r['a_name'] ?? '' ) && '' === rx_text( $r['b_name'] ?? '' ) ) {
			return '';
		}

		$title   = rx_text( $r['title'] ?? '' );
		$verdict = rx_html( $r['verdict_html'] ?? '' );
		return '<figure class="bk-cmp">' . ( '' !== $title ? '<p class="bk-cmp-t">' . $title . '</p>' : '' )
			. '<div class="bk-cmp-grid">' . $col( 'a' ) . '<span class="bk-vs" aria-hidden="true">' . esc_html__( 'vs', 'pharmacy' ) . '</span>' . $col( 'b' ) . '</div>'
			. ( $rx_bl_has( $verdict ) ? '<figcaption class="bk-cmp-v"><b>' . esc_html__( 'Verdict.', 'pharmacy' ) . '</b> ' . $verdict . '</figcaption>' : '' ) . '</figure>';
	},

	// bk_sum(): a worked sum, written out.
	'sum'       => static function ( array $r ) use ( $rx_bl_str, $rx_bl_list ): string {
		$dl = '';
		$j  = 0;
		foreach ( $rx_bl_list( $r['lines'] ?? null ) as $line ) {
			$label = rx_text( $line['label'] ?? '' );
			$value = rx_text( $line['value'] ?? '' );
			if ( '' === $label && '' === $value ) {
				continue;
			}
			$op = $rx_bl_str( $line['op'] ?? '' );
			if ( '' === $op || 'auto' === $op ) {
				$op = $j ? '×' : '';
			} elseif ( 'none' === $op ) {
				$op = '';
			}
			$dl .= '<div><dt>' . $label . '</dt><dd>' . ( '' !== $op ? '<i aria-hidden="true">' . esc_html( $op ) . '</i>' : '' ) . $value . '</dd></div>';
			++$j;
		}

		$result_label = rx_text( $r['result_label'] ?? '' );
		$result_value = rx_text( $r['result_value'] ?? '' );
		if ( '' !== $result_label || '' !== $result_value ) {
			$dl .= '<div class="bk-sum-t"><dt>' . $result_label . '</dt><dd><i aria-hidden="true">=</i>' . $result_value . '</dd></div>';
		}

		$title = rx_text( $r['title'] ?? '' );
		if ( '' === $dl && '' === $title ) {
			return '';
		}

		$note = rx_text( $r['note'] ?? '' );
		return '<figure class="bk-sum"><p class="bk-sum-k">' . esc_html__( 'Worked example · illustrative numbers', 'pharmacy' ) . '</p>'
			. ( '' !== $title ? '<p class="bk-sum-h">' . $title . '</p>' : '' ) . '<dl>' . $dl . '</dl>'
			. ( '' !== $note ? '<figcaption>' . $note . '</figcaption>' : '' ) . '</figure>';
	},

	// bk_rule(): a rule of thumb, set large.
	'rule'      => static function ( array $r ) use ( $rx_bl_has ): string {
		$html = rx_html( $r['html'] ?? '' );
		if ( ! $rx_bl_has( $html ) ) {
			return '';
		}
		$label = rx_text( $r['label'] ?? '' );
		if ( '' === $label ) {
			$label = esc_html__( 'Rule of thumb', 'pharmacy' );
		}
		// A formula ("Minimum stock = daily sales × days to next delivery + a cushion") is laid out term by term.
		$plain = rtrim( trim( wp_strip_all_tags( $html ) ), '.' );
		if ( preg_match( '/^(.+?)\s=\s(.+)$/u', $plain, $m ) && preg_match( '/\s[×÷+−-]\s/u', $m[2] ) ) {
			$parts = (array) preg_split( '/\s([×÷+−-])\s/u', $m[2], -1, PREG_SPLIT_DELIM_CAPTURE );
			$rows  = '<span class="bk-f-row"><i aria-hidden="true">=</i><b>' . esc_html( $parts[0] ) . '</b></span>';
			for ( $i = 1; $i < count( $parts ); $i += 2 ) {
				$rows .= '<span class="bk-f-row"><i aria-hidden="true">' . esc_html( $parts[ $i ] ) . '</i><b>' . esc_html( $parts[ $i + 1 ] ?? '' ) . '</b></span>';
			}
			return '<aside class="bk-rule bk-rule-f"><p class="bk-rule-k">' . $label . '</p><div class="bk-f"><p class="bk-f-r">' . esc_html( $m[1] ) . '</p><div class="bk-f-eq">' . $rows . '</div></div></aside>';
		}
		return '<aside class="bk-rule"><p class="bk-rule-k">' . $label . '</p><p class="bk-rule-t">' . $html . '</p></aside>';
	},

	// bk_checklist(): printable tick list (blog.js handles [data-print]).
	'checklist' => static function ( array $r ) use ( $rx_bl_list, $rx_bl_has ): string {
		$li = '';
		foreach ( $rx_bl_list( $r['items'] ?? null ) as $item ) {
			$html = rx_html( $item['html'] ?? '' );
			if ( $rx_bl_has( $html ) ) {
				$li .= '<li><span class="bk-box" aria-hidden="true"></span><span>' . $html . '</span></li>';
			}
		}
		if ( '' === $li ) {
			return '';
		}
		$title = rx_text( $r['title'] ?? '' );
		return '<figure class="bk-check"><p class="bk-check-h">' . ( '' !== $title ? '<b>' . $title . '</b>' : '' )
			. '<button type="button" class="bk-print" data-print>' . esc_html__( 'Print this list', 'pharmacy' ) . '</button></p><ul>' . $li . '</ul></figure>';
	},

	// bk_steps(): numbered steps with an optional "when" line.
	'steps'     => static function ( array $r ) use ( $rx_bl_list, $rx_bl_has ): string {
		$li = '';
		$n  = 0;
		foreach ( $rx_bl_list( $r['items'] ?? null ) as $item ) {
			$title = rx_text( $item['title'] ?? '' );
			$html  = rx_html( $item['html'] ?? '' );
			$when  = rx_text( $item['when'] ?? '' );
			if ( '' === $title && ! $rx_bl_has( $html ) && '' === $when ) {
				continue;
			}
			++$n;
			$li .= '<li><span class="bk-step-n" aria-hidden="true">' . $n . '</span><div>'
				. ( '' !== $title ? '<p class="bk-step-t">' . $title . '</p>' : '' ) . $html
				. ( '' !== $when ? '<p class="bk-step-w">' . $when . '</p>' : '' ) . '</div></li>';
		}
		return '' !== $li ? '<ol class="bk-steps">' . $li . '</ol>' : '';
	},

	// bk_cta(): inline | feature | panel.
	'cta'       => static function ( array $r ) use ( $rx_bl_str, $rx_bl_arrow ): string {
		$kind = $rx_bl_str( $r['kind'] ?? '' );
		$text = rx_text( $r['text'] ?? '' );

		// bk_cta( 'inline', text, link_label, url( link_key ) ).
		if ( 'inline' === $kind ) {
			$label = rx_text( $r['link_label'] ?? '' );
			if ( '' === $label ) {
				return '';
			}
			$key = $rx_bl_str( $r['link_key'] ?? '' );
			return '<p class="bk-cta-inline">' . ( '' !== $text ? '<span>' . $text . '</span>' : '' )
				. '<a href="' . esc_url( rx_url( '' !== $key ? $key : 'demo' ) ) . '">' . $label . $rx_bl_arrow . '</a></p>';
		}

		// bk_cta( 'feature', feature_key, text ).
		if ( 'feature' === $kind ) {
			$key = $rx_bl_str( $r['feature_key'] ?? '' );
			$key = '' !== $key ? $key : 'features';
			$nav = rx_feature_nav();

			if ( isset( $nav[ $key ] ) ) {
				$label = $nav[ $key ]['label'];
				$icon  = '' !== $nav[ $key ]['icon'] ? $nav[ $key ]['icon'] : 'i-box';
				$url   = $nav[ $key ]['url'];
			} else {
				// Same fallbacks as bk_cta(); a feature page missing from the menu uses its page title.
				$map   = rx_page_map();
				$label = __( 'All features', 'pharmacy' );
				$icon  = 'i-box';
				if ( 'switch' === $key ) {
					$label = __( 'Switching from other software', 'pharmacy' );
					$icon  = 'i-upload';
				} elseif ( 'features' !== $key && isset( $map[ $key ] ) ) {
					$label = (string) get_post_field( 'post_title', $map[ $key ], 'raw' );
				}
				$url = rx_url( $key );
			}

			return '<aside class="bk-cta-feat"><span class="bk-cta-ic"><svg class="icon" aria-hidden="true"><use href="#' . esc_attr( $icon ) . '"/></svg></span>'
				. '<div><p class="bk-cta-k">' . esc_html__( 'How the software handles this', 'pharmacy' ) . '</p>' . ( '' !== $text ? '<p class="bk-cta-t">' . $text . '</p>' : '' ) . '</div>'
				. '<a class="btn btn-secondary btn-sm" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></aside>';
		}

		// bk_cta( 'form', title, text ): the benefits of a demo beside the booking form (falls back to the panel when the form is not available).
		if ( 'form' === $kind ) {
			$form = function_exists( 'rx_form_html' ) ? rx_form_html( 'hero', __( 'Get a free demo call', 'pharmacy' ) ) : '';
			if ( '' !== $form ) {
				$title = rx_text( $r['title'] ?? '' );
				return '<aside class="bk-cta-panel bk-cta-split"><div class="bk-cta-l">' . ( '' !== $title ? '<p class="bk-cta-t">' . $title . '</p>' : '' ) . ( '' !== $text ? '<p>' . $text . '</p>' : '' )
					. '<ul class="bk-cta-ul"><li>' . esc_html__( '30-minute call', 'pharmacy' ) . '</li><li>' . esc_html__( 'Shown with your own medicines', 'pharmacy' ) . '</li><li>' . esc_html__( 'No payment, no obligation', 'pharmacy' ) . '</li></ul></div>'
					. '<div class="bk-cta-r" data-lead-guard-wrap><p class="bk-cta-fh">' . esc_html__( 'Get a free demo call', 'pharmacy' ) . '</p>' . $form . '</div></aside>';
			}
		}

		// bk_cta( 'panel', title, text ): the software is free to start (the Starter plan, with limits, no trial);
		// the plans themselves are on the pricing page, which is kept up to date from the pricing API.
		$title  = rx_text( $r['title'] ?? '' );
		$signup = (string) rx_setting( 'app_signup' );
		return '<aside class="bk-cta-panel"><div>' . ( '' !== $title ? '<p class="bk-cta-t">' . $title . '</p>' : '' )
			. ( '' !== $text ? '<p>' . $text . '</p>' : '' ) . '</div><p class="bk-cta-btns">'
			. ( '' !== $signup ? '<a class="btn btn-primary" href="' . esc_url( $signup ) . '">' . esc_html__( 'Start free', 'pharmacy' ) . '</a>' : '' )
			. '<a href="' . esc_url( rx_url( 'pricing' ) ) . '">' . esc_html__( 'See the plans', 'pharmacy' ) . '</a>'
			. '<a href="' . esc_url( rx_url( 'demo' ) ) . '" data-cta="demo">' . esc_html__( 'Book a demo', 'pharmacy' ) . '</a></p></aside>';
	},
);


	$render = $rx_bl_render;
	return $render;
}

/**
 * Renders one component.
 *
 * @param string $layout Layout name: takeaways, note, table, compare, sum, rule, checklist, steps, cta, text, heading.
 * @param array  $row    Its fields.
 * @return string HTML ('' when the layout is unknown or empty).
 */
function rx_blog_render_component( string $layout, array $row ): string {
	$all = rx_blog_components();
	return isset( $all[ $layout ] ) ? (string) $all[ $layout ]( $row ) : '';
}
