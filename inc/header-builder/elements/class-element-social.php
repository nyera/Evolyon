<?php
/**
 * Social icons element — reads from the single shared social-profiles store
 * (Evolyon_Social_Profiles) so a URL is entered once and can be used by both
 * the header and the footer, rather than duplicated per placement.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Social extends Evolyon_Header_Element {

	public $id = 'social';

	public function __construct() {
		$this->label = __( 'Social Icons', 'evolyon' );
	}

	public function is_available() {
		return count( Evolyon_Social_Profiles::instance()->get_active() ) > 0;
	}

	public function unavailable_reason() {
		return __( 'Add links in Evolyon → Social Profiles', 'evolyon' );
	}

	public function render( $settings ) {
		Evolyon_Social_Profiles::instance()->render_icons( 'evln-header-social' );
	}
}
