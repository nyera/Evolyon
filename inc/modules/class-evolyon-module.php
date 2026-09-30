<?php
/**
 * Base class every Evolyon feature module extends. Enforces the no-bloat
 * rule: a module's hooks and assets only run when is_active() says so.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

abstract class Evolyon_Module {

	/** @var string Unique module id, e.g. 'mega-menu'. */
	public $id = '';

	/** @var string[] Dependency keys checked by dependency_met(), e.g. array( 'woocommerce' ). */
	public $requires = array();

	/**
	 * Whether this module should run at all: dependency check plus (later)
	 * its own settings toggle.
	 */
	public function is_active() {
		foreach ( $this->requires as $dependency ) {
			if ( ! $this->dependency_met( $dependency ) ) {
				return false;
			}
		}
		return true;
	}

	protected function dependency_met( $dependency ) {
		switch ( $dependency ) {
			case 'woocommerce':
				return class_exists( 'WooCommerce' );
			case 'elementor':
				return did_action( 'elementor/loaded' );
			case 'evolyon-toolkit':
				return defined( 'EVOLYON_TOOLKIT_VERSION' );
			default:
				/**
				 * Resolve a custom dependency key that isn't one of the built-ins above.
				 *
				 * @param bool           $met
				 * @param string         $dependency
				 * @param Evolyon_Module $module
				 */
				return apply_filters( 'evolyon/module/dependency_met', true, $dependency, $this );
		}
	}

	/** Register hooks. Only called once, when is_active() is true. */
	abstract public function register();

	/** Enqueue assets. Only called on wp_enqueue_scripts, when is_active() is true. */
	public function enqueue() {}
}
