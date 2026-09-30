<?php
/**
 * CTA button element — text/url/style are per-instance settings edited in
 * the element's gear panel in the builder UI.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_CTA extends Evolyon_Header_Element {

	public $id = 'cta';

	public function __construct() {
		$this->label = __( 'CTA Button', 'evolyon' );
	}

	public function get_defaults() {
		return array(
			'text'  => __( 'Get started', 'evolyon' ),
			'url'   => '#',
			'style' => 'primary',
		);
	}

	public function get_schema() {
		return array(
			array(
				'key'   => 'text',
				'label' => __( 'Button text', 'evolyon' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'url',
				'label' => __( 'Button link', 'evolyon' ),
				'type'  => 'url',
			),
			array(
				'key'     => 'style',
				'label'   => __( 'Style', 'evolyon' ),
				'type'    => 'select',
				'options' => array(
					'primary' => __( 'Primary', 'evolyon' ),
					'accent'  => __( 'Accent', 'evolyon' ),
					'outline' => __( 'Outline', 'evolyon' ),
				),
			),
		);
	}

	public function render( $settings ) {
		$style = in_array( $settings['style'], array( 'primary', 'accent', 'outline' ), true ) ? $settings['style'] : 'primary';
		printf(
			'<a class="evln-btn evln-btn--%1$s evln-header-cta" href="%2$s">%3$s</a>',
			esc_attr( $style ),
			esc_url( $settings['url'] ),
			esc_html( $settings['text'] )
		);
	}
}
