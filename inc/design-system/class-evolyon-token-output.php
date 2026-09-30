<?php
/**
 * Renders the active token set as CSS custom properties and caches the
 * result — regenerated only when the palette/scheme setting changes.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Token_Output {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'customize_save_after', array( $this, 'flush_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_cache' ) );
	}

	private function cache_key() {
		return 'evolyon_token_css_' . Evolyon_Token_Registry::instance()->get_active_palette_id();
	}

	public function flush_cache() {
		foreach ( array_keys( Evolyon_Token_Registry::instance()->get_palettes() ) as $id ) {
			delete_transient( 'evolyon_token_css_' . $id );
		}
	}

	public function get_css() {
		$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$cached   = $is_debug ? false : get_transient( $this->cache_key() );

		if ( false !== $cached ) {
			return $cached;
		}

		$css = $this->build_css();

		if ( ! $is_debug ) {
			set_transient( $this->cache_key(), $css, WEEK_IN_SECONDS );
		}

		return $css;
	}

	private function build_css() {
		$registry = Evolyon_Token_Registry::instance();
		$palette  = $registry->get_palette( $registry->get_active_palette_id() );
		$static   = $registry->get_static_tokens();

		$css  = ':root{' . $this->tokens_to_css( $palette['light'] ) . $this->static_to_css( $static ) . '}';
		$css .= '@media (prefers-color-scheme: dark){:root:not([data-theme="light"]){' . $this->tokens_to_css( $palette['dark'] ) . '}}';
		$css .= ':root[data-theme="dark"]{' . $this->tokens_to_css( $palette['dark'] ) . '}';

		/**
		 * Filter the final generated token CSS before it's enqueued/cached.
		 *
		 * @param string $css
		 * @param array  $palette
		 */
		return apply_filters( 'evolyon/tokens/css', $css, $palette );
	}

	/**
	 * CSS for every registered palette, scoped under [data-palette="id"] —
	 * an opt-in addition to get_css() (which only ever outputs the site
	 * owner's one active palette, unscoped, at :root). Only needed when a
	 * Palette Switcher element is actually placed on a page, so it's a
	 * separate method rather than folded into the always-loaded get_css()
	 * — a real site with no switcher never pays for 9 palettes of CSS it
	 * can't reach. Not cached per-palette like get_css(): this covers all
	 * palettes at once and only runs on the rare page that has the
	 * switcher element.
	 */
	public function get_all_palettes_css() {
		$registry = Evolyon_Token_Registry::instance();
		$css      = '';

		foreach ( $registry->get_palettes() as $id => $palette ) {
			$css .= '[data-palette="' . esc_attr( $id ) . '"]{' . $this->tokens_to_css( $palette['light'] ) . '}';
			$css .= '@media (prefers-color-scheme: dark){[data-palette="' . esc_attr( $id ) . '"]:not([data-theme="light"]){' . $this->tokens_to_css( $palette['dark'] ) . '}}';
			$css .= '[data-palette="' . esc_attr( $id ) . '"][data-theme="dark"]{' . $this->tokens_to_css( $palette['dark'] ) . '}';
		}

		return $css;
	}

	private function tokens_to_css( $tokens ) {
		$out = '';
		foreach ( $tokens as $key => $value ) {
			$out .= '--evln-' . $key . ':' . $value . ';';
		}
		return $out;
	}

	private function static_to_css( $static ) {
		$out = '';

		foreach ( $static['spacing'] as $key => $value ) {
			$out .= '--evln-sp-' . $key . ':' . $value . ';';
		}
		foreach ( $static['radius'] as $key => $value ) {
			$out .= '--evln-r-' . $key . ':' . $value . ';';
		}
		foreach ( $static['font'] as $key => $value ) {
			$out .= '--evln-font-' . $key . ':' . $value . ';';
		}

		$out .= '--evln-shadow-sm:0 1px 2px rgba(var(--evln-shadow-color),.08);';
		$out .= '--evln-shadow-md:0 6px 16px -4px rgba(var(--evln-shadow-color),.14);';
		$out .= '--evln-shadow-lg:0 16px 40px -8px rgba(var(--evln-shadow-color),.22);';

		return $out;
	}
}
