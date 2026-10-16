<?php
/**
 * Article body: renders a post's `body` flexible content, one row per
 * block, with the markup of the PHP site's article components
 * (bk_*() in partials/blog.php) and the plain HTML between them.
 *
 * $args:
 * - rows array The `body` value: rx_field( 'body' ) / get_field( 'body' ),
 *              a list of rows, each with 'acf_fc_layout' and its sub-fields.
 *              Anything else (false, null, '') prints nothing.
 *
 * Echoes the article HTML, one block per line. Rows with an unknown layout,
 * and blocks whose content renders empty, print nothing.
 *
 * Headings come out as plain <h2>…</h2>, without an id: single.php buffers
 * this part and passes the whole article through rx_blog_toc(), which adds
 * the ids and builds the "On this page" list, as blog_end() did:
 *
 *   ob_start();
 *   get_template_part( 'parts/blog-layouts', null, array( 'rows' => rx_field( 'body' ) ) );
 *   [ $rx_body, $rx_toc ] = rx_blog_toc( (string) ob_get_clean() );
 *
 * Layouts and the component each one reproduces:
 *
 *   text       html                                           paragraphs and lists as written
 *   heading    text                                           <h2>
 *   takeaways  items [html]                                   bk_takeaways( items )
 *   note       kind, title, html                              bk_note( kind, title, html )
 *   table      head, rows [cells], caption, highlight_col,    bk_table( head, rows, caption,
 *              numeric_cols                                     [ 'hl' => …, 'num' => [ … ] ] )
 *   compare    title, a_name, a_tag, b_name, b_tag, winner,   bk_compare( spec )
 *              rows [label, a_html, b_html], verdict_html
 *   sum        title, lines [label, value, op],               bk_sum( title, lines, result, note )
 *              result_label, result_value, note
 *   rule       html, label                                    bk_rule( html, label )
 *   checklist  title, items [html]                            bk_checklist( title, items )
 *   steps      items [title, html, when]                      bk_steps( items )
 *   cta        kind, title, text, link_label, link_key,       bk_cta( kind, … )
 *              feature_key
 *
 * Field rules:
 * - Fields ending in `html` (and table cells) are HTML: rx_html(), so
 *   shortcodes run and the markup is filtered with wp_kses. Every other
 *   field is plain text: rx_text() (shortcodes, then escaped).
 * - table `head` holds one column heading per line, starting with the
 *   heading above the row labels; each row's `cells` holds one cell per
 *   line, starting with the row label (printed as <th scope="row">).
 *   Trailing blank lines are ignored; a blank line in between is an empty
 *   cell. `highlight_col` and `numeric_cols` count columns the way bk_table()
 *   does: 0 is the label column, 1 the first column after it.
 * - sum `op`: 'auto' (default) prints × before every line but the first,
 *   as bk_sum() does when no operator is given; 'none' prints no operator;
 *   ×, +, −, ÷ or = print that sign.
 * - cta `kind`: 'inline' (text + link_label → rx_url( link_key )),
 *   'feature' (text + a button to feature_key: label, icon and URL from the
 *   Features menu, rx_feature_nav()), anything else is the demo 'panel'
 *   (title + text, "Book a free demo" + "Start free").
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every value is escaped by rx_text(), rx_html(), esc_*() where it is built.

$rx_bl_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_bl_rows = isset( $rx_bl_args['rows'] ) && is_array( $rx_bl_args['rows'] ) ? $rx_bl_args['rows'] : array();

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

		// bk_cta( 'panel', title, text ): Book a free demo + Start free.
		$title  = rx_text( $r['title'] ?? '' );
		$signup = (string) rx_setting( 'app_signup' );
		return '<aside class="bk-cta-panel"><div>' . ( '' !== $title ? '<p class="bk-cta-t">' . $title . '</p>' : '' ) . ( '' !== $text ? '<p>' . $text . '</p>' : '' ) . '</div><p class="bk-cta-btns">'
			. '<a class="btn btn-light" href="' . esc_url( rx_url( 'demo' ) ) . '" data-cta="demo">' . esc_html__( 'Book a free demo', 'pharmacy' ) . '</a>'
			. ( '' !== $signup ? '<a class="btn btn-ghost-light" href="' . esc_url( $signup ) . '">' . esc_html__( 'Start free', 'pharmacy' ) . '</a>' : '' ) . '</p></aside>';
	},
);

foreach ( $rx_bl_rows as $rx_bl_row ) {
	if ( ! is_array( $rx_bl_row ) ) {
		continue;
	}
	$rx_bl_layout = $rx_bl_str( $rx_bl_row['acf_fc_layout'] ?? '' );
	if ( ! isset( $rx_bl_render[ $rx_bl_layout ] ) ) {
		continue;
	}
	$rx_bl_html = $rx_bl_render[ $rx_bl_layout ]( $rx_bl_row );
	if ( '' !== $rx_bl_html ) {
		echo $rx_bl_html, "\n";
	}
}

// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
