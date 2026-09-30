<?php
/**
 * Small color-math utility used to derive full token sets from a preset's
 * approved anchor colors. No hue/saturation guessing — linear RGB mixing only.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Color {

	public static function hex_to_rgb( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return array(
			'r' => hexdec( substr( $hex, 0, 2 ) ),
			'g' => hexdec( substr( $hex, 2, 2 ) ),
			'b' => hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	public static function rgb_to_hex( $r, $g, $b ) {
		return sprintf( '#%02X%02X%02X', max( 0, min( 255, (int) round( $r ) ) ), max( 0, min( 255, (int) round( $g ) ) ), max( 0, min( 255, (int) round( $b ) ) ) );
	}

	/**
	 * Comma-separated "r,g,b" string for use inside an rgba() token.
	 */
	public static function rgb_string( $hex ) {
		$rgb = self::hex_to_rgb( $hex );
		return "{$rgb['r']},{$rgb['g']},{$rgb['b']}";
	}

	/**
	 * Linear-mix two colors. $weight of 0 returns $hex_a, 1 returns $hex_b.
	 */
	public static function mix( $hex_a, $hex_b, $weight = 0.5 ) {
		$a = self::hex_to_rgb( $hex_a );
		$b = self::hex_to_rgb( $hex_b );

		return self::rgb_to_hex(
			$a['r'] + ( $b['r'] - $a['r'] ) * $weight,
			$a['g'] + ( $b['g'] - $a['g'] ) * $weight,
			$a['b'] + ( $b['b'] - $a['b'] ) * $weight
		);
	}

	public static function darken( $hex, $amount = 0.15 ) {
		return self::mix( $hex, '#000000', $amount );
	}

	public static function lighten( $hex, $amount = 0.15 ) {
		return self::mix( $hex, '#FFFFFF', $amount );
	}
}
