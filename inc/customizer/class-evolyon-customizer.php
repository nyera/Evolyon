<?php
/**
 * Foundation-phase Customizer controls: palette + color-scheme selection.
 * The full Header/Footer Builder UI (Phase 2) adds its own panels here later.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Customizer {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'customize_register', array( $this, 'register' ) );
	}

	public function register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_panel(
			'evolyon',
			array(
				'title'    => __( 'Evolyon', 'evolyon' ),
				'priority' => 20,
			)
		);

		$wp_customize->add_section(
			'evolyon_design_system',
			array(
				'title' => __( 'Design System', 'evolyon' ),
				'panel' => 'evolyon',
			)
		);

		$wp_customize->add_setting(
			'evolyon_palette',
			array(
				'default'           => 'evolyon-default',
				'sanitize_callback' => array( $this, 'sanitize_palette' ),
				'transport'         => 'refresh',
			)
		);

		$choices = array();
		foreach ( Evolyon_Token_Registry::instance()->get_palettes() as $id => $palette ) {
			$choices[ $id ] = $palette['label'];
		}

		$wp_customize->add_control(
			'evolyon_palette',
			array(
				'type'        => 'select',
				'section'     => 'evolyon_design_system',
				'label'       => __( 'Color palette', 'evolyon' ),
				'description' => __( 'Swaps primary, accent, and neutral tones across the whole site.', 'evolyon' ),
				'choices'     => $choices,
			)
		);

		$wp_customize->add_setting(
			'evolyon_color_scheme',
			array(
				'default'           => 'system',
				'sanitize_callback' => array( $this, 'sanitize_color_scheme' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'evolyon_color_scheme',
			array(
				'type'    => 'radio',
				'section' => 'evolyon_design_system',
				'label'   => __( 'Color scheme', 'evolyon' ),
				'choices' => array(
					'system' => __( 'Follow visitor system setting', 'evolyon' ),
					'light'  => __( 'Always light', 'evolyon' ),
					'dark'   => __( 'Always dark', 'evolyon' ),
				),
			)
		);
	}

	public function sanitize_palette( $value ) {
		$palettes = array_keys( Evolyon_Token_Registry::instance()->get_palettes() );
		return in_array( $value, $palettes, true ) ? $value : 'evolyon-default';
	}

	public function sanitize_color_scheme( $value ) {
		return in_array( $value, array( 'system', 'light', 'dark' ), true ) ? $value : 'system';
	}
}

Evolyon_Customizer::instance();
