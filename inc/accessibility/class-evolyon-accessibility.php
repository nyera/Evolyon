<?php
/**
 * Accessibility behavior: skip link, the CSS contract (focus-visible,
 * reduced-motion, screen-reader text lives in base.css), and a small
 * Customizer panel for the one setting worth exposing — a bolder focus
 * ring for visitors who need higher-contrast keyboard focus indication
 * than the default 2px outline.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Accessibility {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'evolyon/template/before_site', array( $this, 'skip_link' ) );
		add_action( 'customize_register', array( $this, 'register_customizer' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
	}

	public function skip_link() {
		printf(
			'<a class="evln-skip-link" href="#evln-content">%s</a>',
			esc_html__( 'Skip to content', 'evolyon' )
		);
	}

	public function register_customizer( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'evolyon_accessibility',
			array(
				'title' => __( 'Accessibility', 'evolyon' ),
				'panel' => 'evolyon',
			)
		);

		$wp_customize->add_setting(
			'evolyon_a11y_enhanced_focus',
			array(
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'evolyon_a11y_enhanced_focus',
			array(
				'type'        => 'checkbox',
				'section'     => 'evolyon_accessibility',
				'label'       => __( 'Enhanced focus outlines', 'evolyon' ),
				'description' => __( 'Thicker, higher-contrast focus ring for keyboard navigation, site-wide.', 'evolyon' ),
			)
		);
	}

	public function body_classes( $classes ) {
		if ( get_theme_mod( 'evolyon_a11y_enhanced_focus', false ) ) {
			$classes[] = 'evln-a11y-enhanced-focus';
		}
		return $classes;
	}
}

Evolyon_Accessibility::instance();
