<?php
/**
 * Base class for every placeable header element (Logo, Nav, Search, Account,
 * Cart, Wishlist, Language Switcher, CTA, Social). The builder UI reads
 * get_schema() to know what settings fields to show in an element's gear
 * panel; the renderer calls is_available() before render() so an inactive
 * dependency (no WooCommerce, no WPML) never outputs markup or assets.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

abstract class Evolyon_Header_Element {

	/** @var string Unique element type id, e.g. 'logo'. */
	public $id = '';

	/** @var string Label shown in the "add element" picker. */
	public $label = '';

	/**
	 * Per-element extra settings schema, beyond the universal
	 * visible_desktop/visible_mobile toggles every element already has.
	 * Each entry: array( 'key', 'label', 'type' => text|url|select, 'options' => [...] ).
	 *
	 * @return array
	 */
	public function get_schema() {
		return array();
	}

	/**
	 * Default values for this element's own settings (merged under the
	 * universal visibility defaults).
	 */
	public function get_defaults() {
		return array();
	}

	/**
	 * Whether this element can render right now — dependency check (e.g. no
	 * WooCommerce means the Cart element is unavailable). Independent of the
	 * per-instance visible_desktop/visible_mobile settings, which are CSS-only.
	 */
	public function is_available() {
		return true;
	}

	/**
	 * A short reason shown next to this element in the "add" picker when
	 * is_available() is false, e.g. "Requires WooCommerce".
	 */
	public function unavailable_reason() {
		return '';
	}

	/**
	 * Output this element's markup. $settings is this instance's saved
	 * settings, already merged with get_defaults().
	 */
	abstract public function render( $settings );
}
