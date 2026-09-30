<?php
/**
 * Performance settings: toggles for things the theme can already do, wired
 * up as a Customizer panel rather than new functionality. Every toggle here
 * maps to a genuine WordPress core removal/behavior, not a placeholder.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Performance {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'customize_register', array( $this, 'register_customizer' ) );
		add_action( 'init', array( $this, 'apply_settings' ) );
		add_filter( 'evolyon/fonts/disable_google_fonts', array( $this, 'filter_disable_fonts' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
	}

	public function register_customizer( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'evolyon_performance',
			array(
				'title' => __( 'Performance', 'evolyon' ),
				'panel' => 'evolyon',
			)
		);

		$this->add_toggle( $wp_customize, 'evolyon_perf_disable_emojis', true, __( 'Disable emoji scripts', 'evolyon' ), __( 'Removes the ~15KB emoji-detection script and inline styles WordPress loads on every page by default.', 'evolyon' ) );
		$this->add_toggle( $wp_customize, 'evolyon_perf_disable_embeds', false, __( 'Disable oEmbed discovery', 'evolyon' ), __( "Stops loading wp-embed.js and the oEmbed discovery links. Turn this off only if you don't embed other WordPress sites' content.", 'evolyon' ) );
		$this->add_toggle( $wp_customize, 'evolyon_perf_disable_fonts', false, __( 'Disable Google Fonts', 'evolyon' ), __( 'Falls back to system fonts instead of loading Bricolage Grotesque/IBM Plex — use this if you self-host fonts or need to avoid third-party requests entirely.', 'evolyon' ) );

		$wp_customize->add_setting(
			'evolyon_perf_animations',
			array(
				'default'           => 'full',
				'sanitize_callback' => array( $this, 'sanitize_animations' ),
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'evolyon_perf_animations',
			array(
				'type'    => 'radio',
				'section' => 'evolyon_performance',
				'label'   => __( 'Site-wide animations', 'evolyon' ),
				'description' => __( 'Independent of each visitor\'s OS-level "reduce motion" setting, which is always respected regardless of this.', 'evolyon' ),
				'choices' => array(
					'full'    => __( 'Full', 'evolyon' ),
					'reduced' => __( 'Reduced (short, essential transitions only)', 'evolyon' ),
					'off'     => __( 'Off', 'evolyon' ),
				),
			)
		);
	}

	private function add_toggle( $wp_customize, $id, $default, $label, $description ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'type'        => 'checkbox',
				'section'     => 'evolyon_performance',
				'label'       => $label,
				'description' => $description,
			)
		);
	}

	public function sanitize_animations( $value ) {
		return in_array( $value, array( 'full', 'reduced', 'off' ), true ) ? $value : 'full';
	}

	public function filter_disable_fonts( $disabled ) {
		return get_theme_mod( 'evolyon_perf_disable_fonts', false ) ? true : $disabled;
	}

	public function body_classes( $classes ) {
		$animations = get_theme_mod( 'evolyon_perf_animations', 'full' );
		if ( 'full' !== $animations ) {
			$classes[] = 'evln-animations-' . $animations;
		}
		return $classes;
	}

	public function apply_settings() {
		if ( get_theme_mod( 'evolyon_perf_disable_emojis', true ) ) {
			$this->disable_emojis();
		}
		if ( get_theme_mod( 'evolyon_perf_disable_embeds', false ) ) {
			$this->disable_embeds();
		}
	}

	private function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'remove_tinymce_emoji' ) );
		add_filter( 'wp_resource_hints', array( $this, 'remove_emoji_dns_prefetch' ), 10, 2 );
	}

	public function remove_tinymce_emoji( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

	public function remove_emoji_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_filter( $urls, function ( $url ) {
				return false === strpos( $url, 's.w.org' );
			} );
		}
		return $urls;
	}

	private function disable_embeds() {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister_embed_script' ), 100 );
	}

	public function deregister_embed_script() {
		wp_deregister_script( 'wp-embed' );
	}
}

Evolyon_Performance::instance();
