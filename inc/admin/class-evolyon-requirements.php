<?php
/**
 * Minimal admin notice pointing to the companion plugin. The full theme
 * dashboard / setup wizard (brief §47-48) is built once there are actual
 * setup steps (demo import, header/footer builder) worth walking through.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Requirements {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_notices', array( $this, 'maybe_notice_toolkit' ) );
	}

	public function maybe_notice_toolkit() {
		if ( defined( 'EVOLYON_TOOLKIT_VERSION' ) ) {
			return;
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
			esc_html__( 'Install and activate the Evolyon Toolkit plugin to enable the form builder, authentication, and the user dashboard.', 'evolyon' )
		);
	}
}

Evolyon_Requirements::instance();
