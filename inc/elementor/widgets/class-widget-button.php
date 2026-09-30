<?php
/**
 * A single demonstration Elementor widget — a button styled with the
 * theme's own design tokens (primary/accent/outline, matching the header
 * CTA element) rather than Elementor's generic default button, so a page
 * built with Elementor still looks like it belongs to the rest of the site.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Elementor_Widget_Button extends \Elementor\Widget_Base {

	public function get_name() {
		return 'evolyon_button';
	}

	public function get_title() {
		return __( 'Evolyon Button', 'evolyon' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return array( 'evolyon' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'evolyon' ) )
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'evolyon' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Get started', 'evolyon' ),
			)
		);

		$this->add_control(
			'url',
			array(
				'label'       => __( 'Link', 'evolyon' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => array( 'url' => '#' ),
				'show_external' => true,
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'evolyon' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => array(
					'primary' => __( 'Primary', 'evolyon' ),
					'accent'  => __( 'Accent', 'evolyon' ),
					'outline' => __( 'Outline', 'evolyon' ),
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$style    = in_array( $settings['style'], array( 'primary', 'accent', 'outline' ), true ) ? $settings['style'] : 'primary';

		$this->add_link_attributes( 'url', $settings['url'] );

		printf(
			'<a class="evln-btn evln-btn--%1$s" %2$s>%3$s</a>',
			esc_attr( $style ),
			$this->get_render_attribute_string( 'url' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor core escapes its own link attribute string.
			esc_html( $settings['text'] )
		);
	}
}
