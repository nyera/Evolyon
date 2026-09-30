<?php
/**
 * Registers the Header Builder, Footer Builder, and Social Profiles
 * Customizer sections. Nav menu locations used by the builders are
 * registered here too, since they only mean something once the builder
 * elements/columns that consume them exist.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Header_Footer_Customizer {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'after_setup_theme', array( $this, 'register_nav_menus' ) );
		add_action( 'customize_register', array( $this, 'register' ) );
	}

	public function register_nav_menus() {
		register_nav_menus(
			array(
				'evolyon-primary' => __( 'Primary (Header)', 'evolyon' ),
				'footer-1'        => __( 'Footer Column 1', 'evolyon' ),
				'footer-2'        => __( 'Footer Column 2', 'evolyon' ),
				'footer-3'        => __( 'Footer Column 3', 'evolyon' ),
				'footer-legal'    => __( 'Footer Bottom Bar', 'evolyon' ),
			)
		);
	}

	public function register( WP_Customize_Manager $wp_customize ) {
		require_once EVOLYON_DIR . '/inc/customizer/class-evolyon-builder-control.php';

		// --- Header Builder ------------------------------------------------
		$wp_customize->add_section(
			'evolyon_header_builder',
			array(
				'title'    => __( 'Header Builder', 'evolyon' ),
				'panel'    => 'evolyon',
				'priority' => 5,
			)
		);

		$wp_customize->add_setting(
			'evolyon_header_config',
			array(
				'default'           => wp_json_encode( Evolyon_Header_Config::instance()->defaults() ),
				'sanitize_callback' => array( Evolyon_Header_Config::instance(), 'sanitize' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new Evolyon_Builder_Control(
				$wp_customize,
				'evolyon_header_config',
				array(
					'section' => 'evolyon_header_builder',
					'builder' => 'header',
					'label'   => __( 'Header Layout', 'evolyon' ),
				)
			)
		);

		// --- Footer Builder --------------------------------------------------
		$wp_customize->add_section(
			'evolyon_footer_builder',
			array(
				'title'    => __( 'Footer Builder', 'evolyon' ),
				'panel'    => 'evolyon',
				'priority' => 6,
			)
		);

		$wp_customize->add_setting(
			'evolyon_footer_config',
			array(
				'default'           => wp_json_encode( Evolyon_Footer_Config::instance()->defaults() ),
				'sanitize_callback' => array( Evolyon_Footer_Config::instance(), 'sanitize' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new Evolyon_Builder_Control(
				$wp_customize,
				'evolyon_footer_config',
				array(
					'section' => 'evolyon_footer_builder',
					'builder' => 'footer',
					'label'   => __( 'Footer Layout', 'evolyon' ),
				)
			)
		);

		// --- Social Profiles (shared by header + footer) --------------------
		$wp_customize->add_section(
			'evolyon_social_profiles',
			array(
				'title'       => __( 'Social Profiles', 'evolyon' ),
				'panel'       => 'evolyon',
				'priority'    => 7,
				'description' => __( 'Enter a URL to show that platform\'s icon anywhere a Social Icons element or the footer bottom bar is enabled.', 'evolyon' ),
			)
		);

		$social = Evolyon_Social_Profiles::instance();
		foreach ( $social->platforms() as $id => $meta ) {
			$setting_id = $social->mod_key( $id );

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => '',
					'sanitize_callback' => array( $social, 'sanitize_url' ),
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$setting_id,
				array(
					'type'    => 'url',
					'section' => 'evolyon_social_profiles',
					'label'   => $meta['label'],
				)
			);
		}
	}
}

Evolyon_Header_Footer_Customizer::instance();
