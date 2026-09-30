<?php
/**
 * Registry of every placeable header element type. Builds the schema the
 * Customizer builder control localizes to JS, and renders one placed element
 * with its universal visibility wrapper.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Header_Elements {

	private static $instance = null;

	/** @var Evolyon_Header_Element[] */
	private $elements = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->register_defaults();
	}

	private function register_defaults() {
		$this->register( new Evolyon_Element_Logo() );
		$this->register( new Evolyon_Element_Nav() );
		$this->register( new Evolyon_Element_Search() );
		$this->register( new Evolyon_Element_Account() );
		$this->register( new Evolyon_Element_Dark_Mode_Toggle() );
		$this->register( new Evolyon_Element_Palette_Switcher() );
		$this->register( new Evolyon_Element_Direction_Toggle() );
		$this->register( new Evolyon_Element_Wishlist() );
		$this->register( new Evolyon_Element_Language_Switcher() );
		$this->register( new Evolyon_Element_CTA() );
		$this->register( new Evolyon_Element_Social() );

		/**
		 * Register additional header element types.
		 *
		 * @param Evolyon_Header_Elements $registry
		 */
		do_action( 'evolyon/header/register_elements', $this );
	}

	public function register( Evolyon_Header_Element $element ) {
		$this->elements[ $element->id ] = $element;
	}

	public function get( $id ) {
		return isset( $this->elements[ $id ] ) ? $this->elements[ $id ] : null;
	}

	/** @return Evolyon_Header_Element[] */
	public function all() {
		return $this->elements;
	}

	/**
	 * Schema handed to the Customizer builder JS: id, label, availability,
	 * defaults, and extra-field schema per element type.
	 */
	public function get_registry_for_js() {
		$out = array();
		foreach ( $this->elements as $id => $element ) {
			$out[ $id ] = array(
				'id'          => $id,
				'label'       => $element->label,
				'available'   => $element->is_available(),
				'unavailable' => $element->unavailable_reason(),
				'defaults'    => wp_parse_args(
					$element->get_defaults(),
					array(
						'visible_desktop' => true,
						'visible_mobile'  => true,
					)
				),
				'schema'      => $element->get_schema(),
			);
		}
		return $out;
	}

	/**
	 * Render one placed element instance: skip entirely if its type is
	 * unknown or unavailable (no markup, no assets). Desktop and mobile each
	 * have their own composition (see Evolyon_Mobile_Header_Render), so an
	 * element placed on desktop is simply always shown there — there is no
	 * per-instance desktop/mobile visibility toggle to apply here.
	 */
	public function render( $type, $settings ) {
		$element = $this->get( $type );
		if ( ! $element || ! $element->is_available() ) {
			return;
		}

		$settings = wp_parse_args( (array) $settings, $element->get_defaults() );

		echo '<div class="evln-el evln-el--' . esc_attr( $type ) . '">';
		$element->render( $settings );
		echo '</div>';
	}

	/**
	 * Look up an element type's settings from wherever it's placed in the
	 * desktop rows (first match wins), falling back to its defaults. Lets
	 * the mobile composition reuse a desktop-configured CTA's text/url/style
	 * instead of asking the site owner to enter it twice.
	 */
	public function find_settings_in_rows( $type, $rows ) {
		$element = $this->get( $type );
		if ( ! $element ) {
			return array();
		}

		foreach ( (array) $rows as $row ) {
			foreach ( (array) ( $row['zones'] ?? array() ) as $items ) {
				foreach ( (array) $items as $item ) {
					if ( isset( $item['type'] ) && $type === $item['type'] ) {
						return wp_parse_args( $item['settings'] ?? array(), $element->get_defaults() );
					}
				}
			}
		}

		return $element->get_defaults();
	}
}
