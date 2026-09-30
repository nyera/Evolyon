<?php
/**
 * Renders the header shell: the desktop rows/zones/elements composition in
 * `.evln-header-desktop`, and a fully independent `.evln-header-mobile`
 * composition (Evolyon_Mobile_Header_Render) as its sibling — CSS toggles
 * which one shows based on the configurable mobile_breakpoint. Desktop and
 * mobile are never the same DOM compressed by media query; see
 * Evolyon_Mobile_Header_Render's docblock for why.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Header_Render {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function render() {
		$config = Evolyon_Header_Config::instance()->get_active();

		$wrapper_classes = array( 'evln-header' );
		if ( $config['sticky'] ) {
			$wrapper_classes[] = 'evln-header--sticky';
		}
		if ( $config['sticky_shrink'] ) {
			$wrapper_classes[] = 'evln-header--shrink';
		}
		if ( 'home' === $config['transparent'] && is_front_page() ) {
			$wrapper_classes[] = 'evln-header--transparent';
		}
		if ( $config['hide_on_scroll'] ) {
			$wrapper_classes[] = 'evln-header--hide-on-scroll';
		}
		if ( ! empty( $config['mobile']['sticky'] ) ) {
			$wrapper_classes[] = 'evln-header--mobile-sticky';
		}

		printf(
			'<header id="evln-header" class="%1$s" data-scroll-threshold="%2$d" data-mobile-breakpoint="%3$d">',
			esc_attr( implode( ' ', $wrapper_classes ) ),
			(int) $config['scroll_threshold'],
			(int) $config['mobile_breakpoint']
		);

		$this->render_desktop( $config );
		Evolyon_Mobile_Header_Render::instance()->render( $config );

		echo '</header>';
	}

	private function render_desktop( $config ) {
		$registry = Evolyon_Header_Elements::instance();

		echo '<div class="evln-header-desktop">';

		foreach ( array( 'top', 'main' ) as $row_key ) {
			$row = $config['rows'][ $row_key ];
			if ( empty( $row['enabled'] ) ) {
				continue;
			}

			printf( '<div class="evln-header-row evln-header-row--%s">', esc_attr( $row_key ) );
			echo '<div class="evln-container evln-header-row__inner">';

			foreach ( array( 'start', 'center', 'end' ) as $zone_key ) {
				echo '<div class="evln-header-zone evln-header-zone--' . esc_attr( $zone_key ) . '">';
				foreach ( $row['zones'][ $zone_key ] as $item ) {
					$registry->render( $item['type'], $item['settings'] );
				}
				echo '</div>';
			}

			echo '</div></div>';
		}

		echo '</div><!-- .evln-header-desktop -->';
	}
}
