<?php
/**
 * Footer builder configuration: up to 4 columns, each with a content type
 * (Brand, Navigation, Custom HTML, Newsletter, Social), plus a bottom bar
 * (copyright text with {year}/{site_title} placeholders, a legal menu,
 * and a social-icons toggle).
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Footer_Config {

	private static $instance = null;

	const TYPES = array( 'brand', 'nav', 'custom_html', 'newsletter', 'social' );

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function defaults() {
		return array(
			'columns' => 4,
			'items'   => array(
				array( 'type' => 'brand', 'settings' => array() ),
				array( 'type' => 'nav', 'settings' => array( 'heading' => __( 'Product', 'evolyon' ), 'menu_location' => 'footer-1' ) ),
				array( 'type' => 'nav', 'settings' => array( 'heading' => __( 'Company', 'evolyon' ), 'menu_location' => 'footer-2' ) ),
				array( 'type' => 'nav', 'settings' => array( 'heading' => __( 'Legal', 'evolyon' ), 'menu_location' => 'footer-3' ) ),
			),
			'bottom'  => array(
				'enabled'       => true,
				'copyright'     => __( '© {year} {site_title}. All rights reserved.', 'evolyon' ),
				'menu_location' => 'footer-legal',
				'show_social'   => true,
			),
		);
	}

	public function column_types() {
		return array(
			'brand'       => __( 'Brand Block', 'evolyon' ),
			'nav'         => __( 'Navigation Menu', 'evolyon' ),
			'custom_html' => __( 'Custom HTML', 'evolyon' ),
			'newsletter'  => __( 'Newsletter', 'evolyon' ),
			'social'      => __( 'Social Icons', 'evolyon' ),
		);
	}

	public function menu_locations() {
		return array(
			'footer-1' => __( 'Footer Column 1', 'evolyon' ),
			'footer-2' => __( 'Footer Column 2', 'evolyon' ),
			'footer-3' => __( 'Footer Column 3', 'evolyon' ),
		);
	}

	public function get_active() {
		$raw = get_theme_mod( 'evolyon_footer_config', '' );
		if ( ! $raw ) {
			return $this->defaults();
		}

		$decoded = json_decode( $raw, true );
		return is_array( $decoded ) ? wp_parse_args( $decoded, $this->defaults() ) : $this->defaults();
	}

	public function sanitize( $raw ) {
		$decoded = json_decode( (string) $raw, true );
		if ( ! is_array( $decoded ) ) {
			return wp_json_encode( $this->defaults() );
		}

		$defaults = $this->defaults();
		$columns  = isset( $decoded['columns'] ) ? max( 1, min( 4, (int) $decoded['columns'] ) ) : $defaults['columns'];

		$items_in = is_array( $decoded['items'] ?? null ) ? $decoded['items'] : array();
		$items    = array();

		for ( $i = 0; $i < $columns; $i++ ) {
			$item = isset( $items_in[ $i ] ) && is_array( $items_in[ $i ] ) ? $items_in[ $i ] : array();
			$type = in_array( $item['type'] ?? '', self::TYPES, true ) ? $item['type'] : 'custom_html';

			$settings_in = is_array( $item['settings'] ?? null ) ? $item['settings'] : array();
			$settings    = array();

			if ( isset( $settings_in['heading'] ) ) {
				$settings['heading'] = sanitize_text_field( $settings_in['heading'] );
			}
			if ( 'nav' === $type ) {
				$locations               = array_keys( $this->menu_locations() );
				$settings['menu_location'] = in_array( $settings_in['menu_location'] ?? '', $locations, true ) ? $settings_in['menu_location'] : $locations[0];
			}
			if ( 'custom_html' === $type && isset( $settings_in['content'] ) ) {
				$settings['content'] = wp_kses_post( $settings_in['content'] );
			}

			$items[] = array(
				'type'     => $type,
				'settings' => $settings,
			);
		}

		$bottom_in = is_array( $decoded['bottom'] ?? null ) ? $decoded['bottom'] : array();
		$locations = array_merge( array( '' ), array_keys( $this->menu_locations() ), array( 'footer-legal' ) );

		$bottom = array(
			'enabled'       => ! empty( $bottom_in['enabled'] ),
			'copyright'     => isset( $bottom_in['copyright'] ) ? wp_kses_post( $bottom_in['copyright'] ) : $defaults['bottom']['copyright'],
			'menu_location' => in_array( $bottom_in['menu_location'] ?? '', $locations, true ) ? $bottom_in['menu_location'] : 'footer-legal',
			'show_social'   => ! empty( $bottom_in['show_social'] ),
		);

		return wp_json_encode(
			array(
				'columns' => $columns,
				'items'   => $items,
				'bottom'  => $bottom,
			)
		);
	}
}
