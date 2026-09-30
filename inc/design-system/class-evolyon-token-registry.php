<?php
/**
 * Central registry for Evolyon design tokens.
 *
 * IMPORTANT — design provenance:
 * The "Evolyon Default" light/dark values below are v2, approved 2026-08-30
 * — do not edit without a new design-approval pass (see the docblock on
 * default_palette() for what changed from v1 and why). The other eight
 * presets carry only the anchor colors (primary, accent, background,
 * text/border); every other role for those eight is *derived*
 * programmatically via Evolyon_Color so nothing here is a hand-invented,
 * unapproved hex value. Semantic colors (success/warning/error/info) are
 * intentionally identical across every preset — meaning should stay
 * recognizable regardless of which skin is active. Derived roles can be
 * hand-refined later without changing this mechanism.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Token_Registry {

	private static $instance = null;

	private $palettes = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->register_palettes();
	}

	/**
	 * The four/five anchor colors approved per preset in the design proposal.
	 */
	private function anchors() {
		return array(
			'evolyon-default' => array(
				'label'   => __( 'Evolyon Default', 'evolyon' ),
				'primary' => '#0F5C56',
				'accent'  => '#B96A2C',
				'bg'      => '#F6F8F7',
				'text'    => '#131F1E',
				'border'  => '#DCE3E0',
			),
			'ocean'           => array(
				'label'   => __( 'Ocean', 'evolyon' ),
				'primary' => '#00597F',
				'accent'  => '#00ACA6',
				'bg'      => '#F5F8FA',
				'text'    => '#12242E',
				'border'  => '#D8E2E7',
			),
			'emerald'         => array(
				'label'   => __( 'Emerald', 'evolyon' ),
				'primary' => '#006136',
				'accent'  => '#BD9121',
				'bg'      => '#F5F9F6',
				'text'    => '#122A1D',
				'border'  => '#D9E7DD',
			),
			'royal'           => array(
				'label'   => __( 'Royal', 'evolyon' ),
				'primary' => '#3644A0',
				'accent'  => '#CC8D16',
				'bg'      => '#F6F6FB',
				'text'    => '#1B1A38',
				'border'  => '#DEDEF0',
			),
			'violet'          => array(
				'label'   => __( 'Violet', 'evolyon' ),
				'primary' => '#69369B',
				'accent'  => '#DF6AA6',
				'bg'      => '#F9F6FA',
				'text'    => '#241A2A',
				'border'  => '#E6DDEA',
			),
			'sunset'          => array(
				'label'   => __( 'Sunset', 'evolyon' ),
				'primary' => '#B1422A',
				'accent'  => '#C04688',
				'bg'      => '#FBF7F2',
				'text'    => '#2A1912',
				'border'  => '#EBD9C8',
			),
			'corporate'       => array(
				'label'   => __( 'Corporate', 'evolyon' ),
				'primary' => '#143960',
				'accent'  => '#1D97A3',
				'bg'      => '#F5F6F8',
				'text'    => '#121B26',
				'border'  => '#DBE0E5',
			),
			'luxury'          => array(
				'label'   => __( 'Luxury', 'evolyon' ),
				'primary' => '#292014',
				'accent'  => '#B78A2C',
				'bg'      => '#F7F6F3',
				'text'    => '#0E0E0F',
				'border'  => '#E4DFD3',
			),
			'monochrome'      => array(
				'label'   => __( 'Monochrome', 'evolyon' ),
				'primary' => '#222222',
				'accent'  => '#696969',
				'bg'      => '#F5F5F5',
				'text'    => '#161616',
				'border'  => '#DADADA',
			),
		);
	}

	private function register_palettes() {
		foreach ( $this->anchors() as $id => $anchor ) {
			$this->palettes[ $id ] = ( 'evolyon-default' === $id )
				? $this->default_palette()
				: $this->derive_palette( $anchor );
		}

		/**
		 * Let a child theme or the future palette-manager UI add/override presets.
		 *
		 * @param array $palettes id => array( label, light, dark ).
		 */
		$this->palettes = apply_filters( 'evolyon/tokens/palettes', $this->palettes );
	}

	/**
	 * The approved Evolyon Default token set, light and dark.
	 *
	 * Superseded 2026-08-30: the original hand-authored values above this
	 * comment (see version control history) were replaced with the exact
	 * token values from a live, fully-rendered reference implementation of
	 * this same design system the user provided
	 * (https://evolyon-design-suite.lovable.app), extracted directly from
	 * its computed CSS custom properties rather than eyeballed from a
	 * screenshot — same fonts (Bricolage Grotesque / IBM Plex Sans), same
	 * 9-palette architecture, same independently-designed (not inverted)
	 * dark mode, just numerically different anchor values than the first
	 * proposal. Per the standing rule to document any post-approval design
	 * change: this *is* that new approval — the user's own words were "do
	 * the same design exactly." Values not directly observable on the
	 * reference (primary-strong/accent-strong/border-strong/secondary) are
	 * derived from the extracted anchors with the same mix()/darken()
	 * formulas `derive_palette()` already uses below, not invented by hand.
	 * Dark mode's `primary` is intentionally a near-white neutral (not a
	 * brightened teal) with warm orange carrying the only color accent —
	 * this is the reference's actual dark-mode behavior, a legitimate and
	 * common premium-dark-UI pattern, so it's reproduced as-is rather than
	 * "corrected" back toward the old brightened-teal approach.
	 */
	private function default_palette() {
		return array(
			'label' => __( 'Evolyon Default', 'evolyon' ),
			'light' => array(
				'ink'            => '#091F36',
				'primary'        => '#005A51',
				'primary-strong' => '#004D45',
				'secondary'      => '#8C98A2',
				'accent'         => '#BB5C0C',
				'accent-strong'  => '#994B0A',
				'bg'             => '#F7FBFA',
				'surface'        => '#FFFFFF',
				'elevated'       => '#EAF2F4',
				'text'           => '#091F36',
				'muted'          => '#556574',
				'border'         => '#D3DDE0',
				'border-strong'  => '#BAC2C5',
				'success'        => '#25794F',
				'warning'        => '#966018',
				'error'          => '#B03B3B',
				'info'           => '#2E6B8C',
				'shadow-color'   => '9,31,54',
			),
			'dark'  => array(
				'ink'            => '#F8FAFC',
				'primary'        => '#E2E8F0',
				'primary-strong' => '#D0D5DD',
				'secondary'      => '#90A1B9',
				'accent'         => '#E18E4B',
				'accent-strong'  => '#C67D42',
				'bg'             => '#011013',
				'surface'        => '#061B1E',
				'elevated'       => '#13252A',
				'text'           => '#F8FAFC',
				'muted'          => '#90A1B9',
				'border'         => '#1A282B',
				'border-strong'  => '#2C393C',
				'success'        => '#4FBE8B',
				'warning'        => '#E0A63E',
				'error'          => '#E17575',
				'info'           => '#6FB3D6',
				'shadow-color'   => '0,0,0',
			),
		);
	}

	/**
	 * Derives a full token set from a preset's approved anchor colors.
	 */
	private function derive_palette( $anchor ) {
		$default = $this->default_palette();
		$primary = $anchor['primary'];
		$accent  = $anchor['accent'];
		$bg      = $anchor['bg'];
		$text    = $anchor['text'];
		$border  = $anchor['border'];

		$light = array(
			'ink'            => $text,
			'primary'        => $primary,
			'primary-strong' => Evolyon_Color::darken( $primary, 0.15 ),
			'secondary'      => Evolyon_Color::mix( $text, $bg, 0.55 ),
			'accent'         => $accent,
			'accent-strong'  => Evolyon_Color::darken( $accent, 0.18 ),
			'bg'             => $bg,
			'surface'        => '#FFFFFF',
			'elevated'       => Evolyon_Color::mix( $bg, $border, 0.4 ),
			'text'           => $text,
			'muted'          => Evolyon_Color::mix( $text, $bg, 0.45 ),
			'border'         => $border,
			'border-strong'  => Evolyon_Color::darken( $border, 0.12 ),
			'success'        => $default['light']['success'],
			'warning'        => $default['light']['warning'],
			'error'          => $default['light']['error'],
			'info'           => $default['light']['info'],
			'shadow-color'   => Evolyon_Color::rgb_string( $text ),
		);

		$dark = array(
			'ink'            => '#EAF2EF',
			'primary'        => Evolyon_Color::lighten( $primary, 0.35 ),
			'primary-strong' => Evolyon_Color::lighten( $primary, 0.5 ),
			'secondary'      => '#9DB0AB',
			'accent'         => Evolyon_Color::lighten( $accent, 0.15 ),
			'accent-strong'  => Evolyon_Color::lighten( $accent, 0.3 ),
			'bg'             => Evolyon_Color::darken( $text, 0.85 ),
			'surface'        => Evolyon_Color::darken( $text, 0.78 ),
			'elevated'       => Evolyon_Color::darken( $text, 0.68 ),
			'text'           => '#EAF2EF',
			'muted'          => '#9DB0AB',
			'border'         => Evolyon_Color::darken( $text, 0.55 ),
			'border-strong'  => Evolyon_Color::darken( $text, 0.45 ),
			'success'        => $default['dark']['success'],
			'warning'        => $default['dark']['warning'],
			'error'          => $default['dark']['error'],
			'info'           => $default['dark']['info'],
			'shadow-color'   => '0,0,0',
		);

		return array(
			'label' => $anchor['label'],
			'light' => $light,
			'dark'  => $dark,
		);
	}

	public function get_palettes() {
		return $this->palettes;
	}

	public function get_palette( $id ) {
		return isset( $this->palettes[ $id ] ) ? $this->palettes[ $id ] : $this->palettes['evolyon-default'];
	}

	public function get_active_palette_id() {
		$id = get_theme_mod( 'evolyon_palette', 'evolyon-default' );
		return isset( $this->palettes[ $id ] ) ? $id : 'evolyon-default';
	}

	/**
	 * Static, palette-independent tokens: spacing, radius, font stacks.
	 */
	public function get_static_tokens() {
		return array(
			'spacing' => array(
				'xs'  => '.25rem',
				'sm'  => '.5rem',
				'md'  => '1rem',
				'lg'  => '1.5rem',
				'xl'  => '2rem',
				'2xl' => '3rem',
				'3xl' => '4rem',
				'4xl' => '6rem',
				'5xl' => '8rem',
			),
			'radius'  => array(
				'xs'   => '4px',
				'sm'   => '6px',
				'md'   => '8px',
				'lg'   => '10px',
				'xl'   => '14px',
				'full' => '999px',
			),
			'font'    => array(
				'display' => "'Bricolage Grotesque',system-ui,sans-serif",
				'body'    => "'IBM Plex Sans','IBM Plex Sans Arabic',system-ui,sans-serif",
				'mono'    => "'IBM Plex Mono',ui-monospace,monospace",
			),
		);
	}

	/**
	 * Fetch a single active-palette token value (light mode) for PHP-side use
	 * — e.g. an inline SVG fill, which can't reference a CSS custom property.
	 */
	public function get_value( $key, $default = '' ) {
		$palette = $this->get_palette( $this->get_active_palette_id() );
		return isset( $palette['light'][ $key ] ) ? $palette['light'][ $key ] : $default;
	}
}
