<?php
/**
 * Header builder configuration: defaults, starter variants, and the
 * sanitize callback that keeps the saved JSON limited to known row/zone/
 * element-type keys no matter what the builder JS sends.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Header_Config {

	private static $instance = null;

	const ROWS  = array( 'top', 'main' );
	const ZONES = array( 'start', 'center', 'end' );

	/** Element types placeable in the mobile primary/secondary rows — logo and nav are handled structurally, not as list items. */
	const MOBILE_ELEMENT_TYPES = array( 'search', 'account', 'wishlist', 'language_switcher', 'cta', 'social' );

	/** Max primary-row elements per mode, so a mode can't be configured into a crowded bar. */
	const MOBILE_MODE_CAPS = array(
		'compact'  => 1,
		'standard' => 3,
		'expanded' => 2,
	);

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function empty_row() {
		return array(
			'enabled' => false,
			'zones'   => array(
				'start'  => array(),
				'center' => array(),
				'end'    => array(),
			),
		);
	}

	public function defaults() {
		$rows                 = array(
			'top'  => $this->empty_row(),
			'main' => $this->empty_row(),
		);
		$rows['main']['enabled']            = true;
		$rows['main']['zones']['start'][]   = array( 'type' => 'logo', 'settings' => array() );
		$rows['main']['zones']['center'][]  = array( 'type' => 'nav', 'settings' => array() );
		$rows['main']['zones']['end'][]     = array( 'type' => 'search', 'settings' => array() );
		$rows['main']['zones']['end'][]     = array( 'type' => 'dark_mode_toggle', 'settings' => array() );
		$rows['main']['zones']['end'][]     = array( 'type' => 'account', 'settings' => array() );
		$rows['main']['zones']['end'][]     = array(
			'type'     => 'cta',
			'settings' => array(
				'text'  => __( 'Get started', 'evolyon' ),
				'url'   => '#',
				'style' => 'primary',
			),
		);

		return array(
			'variant'           => 'modern-row',
			'sticky'            => true,
			'sticky_shrink'     => true,
			'transparent'       => 'off',
			'hide_on_scroll'    => false,
			'scroll_threshold'  => 80,
			'mobile_breakpoint' => 992,
			'rows'              => $rows,
			'mobile'            => $this->mobile_defaults(),
		);
	}

	/**
	 * Defaults for the dedicated mobile header composition — deliberately
	 * its own row/element list, not a hidden subset of the desktop rows.
	 */
	public function mobile_defaults() {
		return array(
			'mode'           => 'standard',
			'alignment'      => 'logo-center',
			'elements'       => array( 'search', 'account' ),
			'menu_style'     => 'offcanvas',
			'logo_max_width' => 120,
			'height'         => 64,
			'padding_inline' => 16,
			'icon_size'      => 22,
			'sticky'         => true,
			'shadow'         => true,
			'border'         => true,
			'bg'             => '',
			'text_color'     => '',
			'secondary_row'  => array(
				'enabled'  => false,
				'elements' => array( 'search' ),
			),
		);
	}

	/**
	 * Named starter layouts the builder's "Variants" tab can apply. Applying
	 * one overwrites `rows` (and updates the `variant` label) but leaves the
	 * Behavior tab settings untouched.
	 */
	public function variants() {
		$two_row              = array(
			'top'  => $this->empty_row(),
			'main' => $this->empty_row(),
		);
		$two_row['top']['enabled']           = true;
		$two_row['top']['zones']['start'][]  = array( 'type' => 'social', 'settings' => array() );
		$two_row['top']['zones']['end'][]    = array( 'type' => 'language_switcher', 'settings' => array() );
		$two_row['top']['zones']['end'][]    = array( 'type' => 'account', 'settings' => array() );
		$two_row['main']['enabled']          = true;
		$two_row['main']['zones']['start'][] = array( 'type' => 'logo', 'settings' => array() );
		$two_row['main']['zones']['center'][] = array( 'type' => 'nav', 'settings' => array() );
		$two_row['main']['zones']['end'][]   = array( 'type' => 'search', 'settings' => array() );
		$two_row['main']['zones']['end'][]   = array(
			'type'     => 'cta',
			'settings' => array( 'text' => __( 'Get started', 'evolyon' ), 'url' => '#', 'style' => 'primary' ),
		);

		$centered              = array(
			'top'  => $this->empty_row(),
			'main' => $this->empty_row(),
		);
		$centered['main']['enabled']           = true;
		$centered['main']['zones']['start'][]  = array( 'type' => 'nav', 'settings' => array() );
		$centered['main']['zones']['center'][] = array( 'type' => 'logo', 'settings' => array() );
		$centered['main']['zones']['end'][]    = array( 'type' => 'search', 'settings' => array() );
		$centered['main']['zones']['end'][]    = array( 'type' => 'account', 'settings' => array() );
		$centered['main']['zones']['end'][]    = array(
			'type'     => 'cta',
			'settings' => array( 'text' => __( 'Get started', 'evolyon' ), 'url' => '#', 'style' => 'primary' ),
		);

		$defaults = $this->defaults();

		return array(
			'modern-row'    => array(
				'label' => __( 'Modern Row', 'evolyon' ),
				'rows'  => $defaults['rows'],
			),
			'two-row'       => array(
				'label' => __( 'Classic Two-Row', 'evolyon' ),
				'rows'  => $two_row,
			),
			'centered-logo' => array(
				'label' => __( 'Centered Logo', 'evolyon' ),
				'rows'  => $centered,
			),
		);
	}

	public function get_active() {
		$raw = get_theme_mod( 'evolyon_header_config', '' );
		if ( ! $raw ) {
			return $this->defaults();
		}

		$decoded = json_decode( $raw, true );
		return is_array( $decoded ) ? wp_parse_args( $decoded, $this->defaults() ) : $this->defaults();
	}

	/**
	 * Sanitize callback registered on the `evolyon_header_config` setting.
	 * Rejects anything outside known rows/zones/element types so a tampered
	 * or buggy client payload can never inject arbitrary markup.
	 */
	public function sanitize( $raw ) {
		$decoded = json_decode( (string) $raw, true );
		if ( ! is_array( $decoded ) ) {
			return wp_json_encode( $this->defaults() );
		}

		$defaults = $this->defaults();
		$clean    = array(
			'variant'           => isset( $decoded['variant'] ) ? sanitize_key( $decoded['variant'] ) : $defaults['variant'],
			'sticky'             => ! empty( $decoded['sticky'] ),
			'sticky_shrink'      => ! empty( $decoded['sticky_shrink'] ),
			'transparent'        => in_array( $decoded['transparent'] ?? '', array( 'off', 'home' ), true ) ? $decoded['transparent'] : 'off',
			'hide_on_scroll'     => ! empty( $decoded['hide_on_scroll'] ),
			'scroll_threshold'   => isset( $decoded['scroll_threshold'] ) ? max( 0, (int) $decoded['scroll_threshold'] ) : $defaults['scroll_threshold'],
			'mobile_breakpoint'  => isset( $decoded['mobile_breakpoint'] ) ? max( 480, (int) $decoded['mobile_breakpoint'] ) : $defaults['mobile_breakpoint'],
			'rows'               => array(),
			'mobile'             => $this->sanitize_mobile( $decoded['mobile'] ?? array() ),
		);

		$registry = Evolyon_Header_Elements::instance();

		foreach ( self::ROWS as $row_key ) {
			$row_in = isset( $decoded['rows'][ $row_key ] ) && is_array( $decoded['rows'][ $row_key ] ) ? $decoded['rows'][ $row_key ] : array();
			$row    = $this->empty_row();
			$row['enabled'] = ! empty( $row_in['enabled'] );

			foreach ( self::ZONES as $zone_key ) {
				$items_in = isset( $row_in['zones'][ $zone_key ] ) && is_array( $row_in['zones'][ $zone_key ] ) ? $row_in['zones'][ $zone_key ] : array();

				foreach ( $items_in as $item ) {
					if ( ! is_array( $item ) || empty( $item['type'] ) ) {
						continue;
					}
					$type = sanitize_key( $item['type'] );
					if ( ! $registry->get( $type ) ) {
						continue;
					}

					$settings_in = isset( $item['settings'] ) && is_array( $item['settings'] ) ? $item['settings'] : array();
					$settings    = array();

					foreach ( $registry->get( $type )->get_schema() as $field ) {
						$key = $field['key'];
						if ( ! isset( $settings_in[ $key ] ) ) {
							continue;
						}
						$settings[ $key ] = $this->sanitize_field( $field, $settings_in[ $key ] );
					}

					$row['zones'][ $zone_key ][] = array(
						'type'     => $type,
						'settings' => $settings,
					);
				}
			}

			$clean['rows'][ $row_key ] = $row;
		}

		return wp_json_encode( $clean );
	}

	/**
	 * Sanitizes the dedicated mobile header composition. Mobile is its own
	 * row/element list (not a hidden subset of the desktop rows) — see the
	 * class docblock in Evolyon_Mobile_Header_Render for why.
	 */
	private function sanitize_mobile( $mobile_in ) {
		$defaults = $this->mobile_defaults();
		$mobile_in = is_array( $mobile_in ) ? $mobile_in : array();

		$mode = in_array( $mobile_in['mode'] ?? '', array_keys( self::MOBILE_MODE_CAPS ), true ) ? $mobile_in['mode'] : $defaults['mode'];
		$cap  = self::MOBILE_MODE_CAPS[ $mode ];

		$elements_in = is_array( $mobile_in['elements'] ?? null ) ? $mobile_in['elements'] : $defaults['elements'];
		$elements    = array();
		foreach ( $elements_in as $type ) {
			$type = sanitize_key( $type );
			if ( in_array( $type, self::MOBILE_ELEMENT_TYPES, true ) && ! in_array( $type, $elements, true ) ) {
				$elements[] = $type;
			}
			if ( count( $elements ) >= $cap ) {
				break;
			}
		}

		$secondary_in      = is_array( $mobile_in['secondary_row'] ?? null ) ? $mobile_in['secondary_row'] : array();
		$secondary_elements = array();
		foreach ( ( is_array( $secondary_in['elements'] ?? null ) ? $secondary_in['elements'] : $defaults['secondary_row']['elements'] ) as $type ) {
			$type = sanitize_key( $type );
			if ( in_array( $type, self::MOBILE_ELEMENT_TYPES, true ) && ! in_array( $type, $secondary_elements, true ) ) {
				$secondary_elements[] = $type;
			}
		}

		return array(
			'mode'           => $mode,
			'alignment'      => in_array( $mobile_in['alignment'] ?? '', array( 'logo-center', 'logo-start' ), true ) ? $mobile_in['alignment'] : $defaults['alignment'],
			'elements'       => $elements,
			'menu_style'     => in_array( $mobile_in['menu_style'] ?? '', array( 'offcanvas', 'fullscreen', 'dropdown' ), true ) ? $mobile_in['menu_style'] : $defaults['menu_style'],
			'logo_max_width' => isset( $mobile_in['logo_max_width'] ) ? max( 60, min( 240, (int) $mobile_in['logo_max_width'] ) ) : $defaults['logo_max_width'],
			'height'         => isset( $mobile_in['height'] ) ? max( 48, min( 96, (int) $mobile_in['height'] ) ) : $defaults['height'],
			'padding_inline' => isset( $mobile_in['padding_inline'] ) ? max( 8, min( 32, (int) $mobile_in['padding_inline'] ) ) : $defaults['padding_inline'],
			'icon_size'      => isset( $mobile_in['icon_size'] ) ? max( 18, min( 32, (int) $mobile_in['icon_size'] ) ) : $defaults['icon_size'],
			'sticky'         => ! isset( $mobile_in['sticky'] ) || ! empty( $mobile_in['sticky'] ),
			'shadow'         => ! isset( $mobile_in['shadow'] ) || ! empty( $mobile_in['shadow'] ),
			'border'         => ! isset( $mobile_in['border'] ) || ! empty( $mobile_in['border'] ),
			'bg'             => isset( $mobile_in['bg'] ) ? sanitize_hex_color( $mobile_in['bg'] ) ?? '' : '',
			'text_color'     => isset( $mobile_in['text_color'] ) ? sanitize_hex_color( $mobile_in['text_color'] ) ?? '' : '',
			'secondary_row'  => array(
				'enabled'  => 'expanded' === $mode && ! empty( $secondary_in['enabled'] ),
				'elements' => $secondary_elements,
			),
		);
	}

	private function sanitize_field( $field, $value ) {
		switch ( $field['type'] ) {
			case 'url':
				return esc_url_raw( $value );
			case 'select':
				$options = array_keys( $field['options'] ?? array() );
				return in_array( $value, $options, true ) ? $value : reset( $options );
			default:
				return sanitize_text_field( $value );
		}
	}
}
