<?php
/**
 * Pharmacy Child: site-specific overrides.
 *
 * The parent theme (pharmacy) holds all templates, parts, styles, scripts
 * and logic. This child theme is the active theme and starts empty, so
 * changes made here survive updates to the parent.
 *
 * Where overrides go:
 * - PHP hooks: add actions and filters at the bottom of this file. The
 *   child's functions.php runs before the parent's, so hook at a priority
 *   later than the parent's callback (or on a later hook such as
 *   after_setup_theme) to change parent behaviour.
 * - Templates and parts: copy the file from the parent into the same path
 *   here (for example parts/cta-band.php). get_template_part() and the
 *   template hierarchy pick up the child copy first.
 * - Assets: rx_asset() resolves through get_theme_file_uri(), so a file at
 *   assets/{path} in this theme replaces the parent's file of the same path.
 * - Trusted logos: files in assets/img/trusted/ here replace the parent's set.
 *
 * Nothing is enqueued here; the parent's inc/enqueue.php loads every asset.
 *
 * @package pharmacy-child
 */

defined( 'ABSPATH' ) || exit;
