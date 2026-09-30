<?php
/**
 * Small standalone helper functions with no class dependencies of their own.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds the Google Fonts URL for the approved type system:
 * Bricolage Grotesque (display), IBM Plex Sans + IBM Plex Sans Arabic (body/UI),
 * IBM Plex Mono (data). Returns '' when disabled via the performance filter.
 */
function evolyon_fonts_url() {
	if ( apply_filters( 'evolyon/fonts/disable_google_fonts', false ) ) {
		return '';
	}

	$families = array(
		'Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800',
		'IBM+Plex+Sans:wght@400;500;600;700',
		'IBM+Plex+Sans+Arabic:wght@400;500;600;700',
		'IBM+Plex+Mono:wght@400;500',
	);

	$fonts_url = add_query_arg(
		array(
			'family'  => implode( '&family=', $families ),
			'display' => 'swap',
		),
		'https://fonts.googleapis.com/css2'
	);

	return esc_url( $fonts_url );
}

/**
 * PHP-side access to an active-palette token value — for the rare case
 * (inline SVG fill, email HTML) that can't reference a CSS custom property.
 */
function evolyon_token( $key, $default = '' ) {
	return Evolyon_Token_Registry::instance()->get_value( $key, $default );
}

/**
 * Cache-busting version string for a theme asset, based on its file mtime
 * in development and the theme version in production.
 */
function evolyon_asset_version( $relative_path ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$path = EVOLYON_DIR . $relative_path;
		if ( file_exists( $path ) ) {
			return filemtime( $path );
		}
	}
	return EVOLYON_VERSION;
}

/**
 * True when $post_id was built with Elementor's page builder — false
 * (never fatal) when Elementor isn't installed at all.
 */
function evolyon_is_built_with_elementor( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	return \Elementor\Plugin::$instance->documents->get( $post_id ) && \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
}
