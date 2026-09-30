<?php
/**
 * Language switcher — soft-detects WPML or Polylang and renders their
 * language list; unavailable (no markup) when neither is active.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Language_Switcher extends Evolyon_Header_Element {

	public $id = 'language_switcher';

	public function __construct() {
		$this->label = __( 'Language Switcher', 'evolyon' );
	}

	public function is_available() {
		return $this->has_polylang() || $this->has_wpml();
	}

	public function unavailable_reason() {
		return __( 'Requires WPML or Polylang', 'evolyon' );
	}

	private function has_polylang() {
		return function_exists( 'pll_the_languages' );
	}

	private function has_wpml() {
		return defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'icl_get_languages' );
	}

	public function render( $settings ) {
		echo '<div class="evln-lang-switcher">';

		if ( $this->has_polylang() ) {
			pll_the_languages(
				array(
					'show_flags'  => 0,
					'show_names'  => 1,
					'dropdown'    => 0,
					'echo'        => 1,
				)
			);
		} elseif ( $this->has_wpml() ) {
			$languages = icl_get_languages( 'skip_missing=0' );
			if ( is_array( $languages ) ) {
				echo '<ul class="evln-lang-switcher__list">';
				foreach ( $languages as $lang ) {
					printf(
						'<li><a href="%1$s"%2$s>%3$s</a></li>',
						esc_url( $lang['url'] ),
						! empty( $lang['active'] ) ? ' class="is-active"' : '',
						esc_html( $lang['native_name'] )
					);
				}
				echo '</ul>';
			}
		}

		echo '</div>';
	}
}
