<?php
/**
 * Single shared store for social profile URLs, entered once in the
 * Customizer and reused by both the header Social element and the footer's
 * social toggle — avoids asking the site owner to paste the same links twice.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Social_Profiles {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Platform id => array( label, icon path 'd' attribute, viewBox ).
	 * A small hand-picked icon set kept inline (no icon font/library load).
	 */
	public function platforms() {
		return array(
			'facebook'  => array(
				'label' => __( 'Facebook', 'evolyon' ),
				'path'  => 'M13.5 9H15V6.5h-1.8C11 6.5 10 7.6 10 9.6V11H8.5v2.5H10V20h2.6v-6.5H14l.4-2.5h-1.8V9.8c0-.6.2-.8.9-.8z',
			),
			'instagram' => array(
				'label' => __( 'Instagram', 'evolyon' ),
				'path'  => 'M8 4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4zm4 4.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zM17 7.2a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8z',
			),
			'x'         => array(
				'label' => __( 'X (Twitter)', 'evolyon' ),
				'path'  => 'M5 4h3.6l4 5.4L17 4h2.4l-6 7.4L20 20h-3.6l-4.3-5.8L7 20H4.6l6.3-7.8L5 4z',
			),
			'linkedin'  => array(
				'label' => __( 'LinkedIn', 'evolyon' ),
				'path'  => 'M6.9 8.6H4V19h2.9V8.6zM5.4 4.5A1.7 1.7 0 1 0 5.4 8a1.7 1.7 0 0 0 0-3.5zM20 19h-2.9v-5.4c0-1.3-.5-2.2-1.6-2.2-.9 0-1.4.6-1.6 1.2-.1.2-.1.5-.1.8V19H11s.1-9.1 0-10.4h2.9v1.5c.4-.6 1.1-1.5 2.7-1.5 2 0 3.4 1.3 3.4 4.1V19z',
			),
			'youtube'   => array(
				'label' => __( 'YouTube', 'evolyon' ),
				'path'  => 'M21 8.2s-.2-1.5-.8-2.1c-.8-.8-1.6-.8-2-.9C15.7 5 12 5 12 5s-3.7 0-6.2.2c-.4.1-1.2.1-2 .9C3.2 6.7 3 8.2 3 8.2S2.8 10 2.8 11.7v.6c0 1.7.2 3.5.2 3.5s.2 1.5.8 2.1c.8.8 1.8.8 2.3.9 1.7.2 7 .2 7 .2s3.7 0 6.2-.2c.4-.1 1.2-.1 2-.9.6-.6.8-2.1.8-2.1s.2-1.8.2-3.5v-.6C21.2 10 21 8.2 21 8.2zM10 14.6V9.4l5 2.6-5 2.6z',
			),
			'tiktok'    => array(
				'label' => __( 'TikTok', 'evolyon' ),
				'path'  => 'M14 4h2.3c.2 1.6 1.3 2.9 3.2 3.1v2.4c-1.2 0-2.3-.4-3.2-1v5.7a4.9 4.9 0 1 1-4.9-4.9c.2 0 .4 0 .6.1v2.4a2.5 2.5 0 1 0 1.8 2.4V4z',
			),
		);
	}

	/**
	 * Each platform is its own theme_mod ('evolyon_social_{id}') so the
	 * Customizer can use plain built-in url controls — no custom JS needed
	 * for something this simple.
	 */
	public function mod_key( $platform_id ) {
		return 'evolyon_social_' . $platform_id;
	}

	/** platform => url, only platforms with a non-empty URL. */
	public function get_active() {
		$out = array();
		foreach ( $this->platforms() as $id => $meta ) {
			$url = get_theme_mod( $this->mod_key( $id ), '' );
			if ( $url ) {
				$out[ $id ] = esc_url_raw( $url );
			}
		}
		return $out;
	}

	public function sanitize_url( $value ) {
		return esc_url_raw( $value );
	}

	public function render_icons( $wrapper_class = 'evln-social' ) {
		$active    = $this->get_active();
		$platforms = $this->platforms();

		if ( empty( $active ) ) {
			return;
		}

		echo '<ul class="' . esc_attr( $wrapper_class ) . '">';
		foreach ( $active as $id => $url ) {
			if ( ! isset( $platforms[ $id ] ) ) {
				continue;
			}
			printf(
				'<li><a href="%1$s" target="_blank" rel="noopener noreferrer"><span class="screen-reader-text">%2$s</span><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="%3$s"></path></svg></a></li>',
				esc_url( $url ),
				esc_html( $platforms[ $id ]['label'] ),
				esc_attr( $platforms[ $id ]['path'] )
			);
		}
		echo '</ul>';
	}
}
