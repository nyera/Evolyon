<?php
/**
 * Instantiates and boots every registered Evolyon module, but only calls
 * register()/enqueue() on the ones whose is_active() check passes — this is
 * the single mechanism that keeps inactive features (WooCommerce styling
 * with no WooCommerce, Elementor widgets with no Elementor, ...) from
 * loading any hook or asset at all.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Module_Loader {

	private static $instance = null;

	/** @var Evolyon_Module[] */
	private $modules = array();

	private $booted = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register( Evolyon_Module $module ) {
		$this->modules[ $module->id ] = $module;
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		/**
		 * Fires once, before modules are checked and registered — theme and
		 * child-theme code hook in here and call $loader->register( $module ).
		 *
		 * @param Evolyon_Module_Loader $loader
		 */
		do_action( 'evolyon/modules/register', $this );

		foreach ( $this->modules as $module ) {
			if ( $module->is_active() ) {
				$module->register();
				add_action( 'wp_enqueue_scripts', array( $module, 'enqueue' ) );
			}
		}
	}

	/** @return Evolyon_Module[] */
	public function get_active_modules() {
		return array_filter(
			$this->modules,
			function ( $module ) {
				return $module->is_active();
			}
		);
	}
}
