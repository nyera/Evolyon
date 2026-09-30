<?php
/**
 * Logo element — site logo if set, otherwise the site title as text.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Logo extends Evolyon_Header_Element {

	public $id = 'logo';

	public function __construct() {
		$this->label = __( 'Logo', 'evolyon' );
	}

	public function render( $settings ) {
		if ( has_custom_logo() ) {
			the_custom_logo();
			return;
		}

		$name = get_bloginfo( 'name' );
		$mark = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );

		printf(
			'<a class="evln-logo-link" href="%1$s"><span class="evln-logo-mark" aria-hidden="true">%2$s</span><span class="evln-logo-text">%3$s</span></a>',
			esc_url( home_url( '/' ) ),
			esc_html( $mark ),
			esc_html( $name )
		);
	}
}
