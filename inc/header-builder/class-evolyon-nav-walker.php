<?php
/**
 * Nav walker for the primary menu's mega-menu dropdowns: WordPress core's
 * default Walker_Nav_Menu silently drops a menu item's "description" field
 * (populated from the Appearance → Menus screen's "Description" box, under
 * Screen Options) — this one outputs it as a subtitle line under the link,
 * only when a description is actually set, so a plain single-level item
 * (no children, no description) renders identically to core's own output.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * A top-level item's own description ("Three distinct starting
	 * points...", "Every essential page, ready to adapt.") isn't for the
	 * nav bar link — it's the intro line at the top of the panel that
	 * item's children open into, confirmed against the reference's own
	 * markup. Walker_Nav_Menu's start_lvl()/end_lvl() hooks aren't passed
	 * the parent item, so it's stashed here in start_el() and consumed by
	 * the very next start_lvl() call.
	 */
	private $pending_panel_intro = '';

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes     = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[]   = 'menu-item-' . $item->ID;
		$class_names = implode( ' ', array_filter( array_unique( $classes ) ) );

		$output .= '<li class="' . esc_attr( $class_names ) . '">';

		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ' href="' . esc_url( $item->url ) . '"';

		$output .= '<a' . $attributes . '>';
		$output .= '<span class="evln-nav__item-title">' . esc_html( $item->title ) . '</span>';
		if ( 0 === $depth ) {
			$this->pending_panel_intro = $item->description;
		} elseif ( ! empty( $item->description ) ) {
			$output .= '<span class="evln-nav__item-desc">' . esc_html( $item->description ) . '</span>';
		}
		$output .= '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="evln-nav__submenu-panel">';
		if ( ! empty( $this->pending_panel_intro ) ) {
			$output .= '<p class="evln-nav__submenu-intro">' . esc_html( $this->pending_panel_intro ) . '</p>';
		}
		$this->pending_panel_intro = '';
		$output                   .= '<ul class="sub-menu evln-nav__submenu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div>';
	}
}
