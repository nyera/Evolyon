<?php
/**
 * Primary navigation element — renders the 'evolyon-primary' menu location.
 * The mobile off-canvas/dropdown panel is rendered separately by
 * Evolyon_Header_Render (once per header, not once per placed nav element),
 * reusing the same menu location.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Nav extends Evolyon_Header_Element {

	public $id = 'nav';

	public function __construct() {
		$this->label = __( 'Navigation', 'evolyon' );
	}

	public function render( $settings ) {
		wp_nav_menu(
			array(
				'theme_location' => 'evolyon-primary',
				'container'      => false,
				'menu_class'     => 'evln-nav__menu',
				'depth'          => 3,
				'fallback_cb'    => array( $this, 'fallback_menu' ),
				'walker'         => new Evolyon_Nav_Walker(),
			)
		);
	}

	/**
	 * Shown until the site owner assigns a menu, so the header never looks broken.
	 */
	public function fallback_menu() {
		echo '<ul class="evln-nav__menu evln-nav__menu--fallback">';
		wp_list_pages(
			array(
				'title_li' => '',
				'depth'    => 1,
			)
		);
		echo '</ul>';
	}
}
