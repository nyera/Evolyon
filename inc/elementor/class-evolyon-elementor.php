<?php
/**
 * Elementor compatibility layer. Purely additive: nothing in earlier phases
 * assumes Elementor is present, and this file's own hooks only ever
 * register when Elementor actually is (checked once in functions.php,
 * matching the same pattern as the WooCommerce integration). The theme
 * remains fully usable without Elementor installed at all.
 *
 * Deliberately not building a competing page-builder feature — this is a
 * theme-style bridge (fonts/colors match in the editor) and one real fix
 * (full-width pages don't get clipped by the theme's own container), not
 * an attempt to add page-building capability of our own.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Elementor {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_support' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_assets' ) );
		/*
		 * Elementor's real hook is 'after_enqueue_styles' (plural) — this
		 * was 'after_enqueue_style' (singular), a hook name that simply
		 * doesn't exist, so frontend_assets() never fired on any page ever.
		 * Confirmed live: elementor.css was completely absent from every
		 * loaded stylesheet on an Elementor-built test page, and a native
		 * Elementor Heading widget rendered in Elementor's default Roboto
		 * instead of the theme's own display font as a direct result.
		 */
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'frontend_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );

		/*
		 * Compatibility fix for a bug in a bundled Elementor sub-package
		 * (vendor/elementor/wp-one-package's admin Page class), not anything
		 * in Evolyon: it registers a "Theme Builder"-style submenu item
		 * whose *slug* is a full URL instead of a page slug. WordPress
		 * core's own wp-admin/menu-header.php then does
		 * `file_exists( WP_PLUGIN_DIR . "/$slug" )` while building that
		 * submenu's link (menu-header.php line ~263), which — with a URL
		 * instead of a slug — produces exactly this environment's reported
		 * warning ("open_basedir restriction... wp-content/plugins/http://...")
		 * and a non-functional link regardless. Confirmed directly against
		 * Elementor's own source (not a guess) and reproduced/verified fixed
		 * by rendering wp-admin/menu.php + menu-header.php in isolation.
		 *
		 * An earlier version of this fix hooked 'admin_menu' at a fixed late
		 * priority (99999) — not reliable, since the culprit registration
		 * can itself run at PHP_INT_MAX priority on the SAME 'admin_menu'
		 * action (confirmed in Elementor's own Page::upgrade_menu_item(),
		 * a related admin-menu callback in the same class family), which
		 * would run AFTER any fixed priority we pick. 'admin_head' is used
		 * instead: it's a *later, separate* action that core always fires
		 * after 'admin_menu' has fully finished (see wp-admin/admin-header.php)
		 * and strictly before menu-header.php is included — so it's
		 * guaranteed to run after every 'admin_menu' callback regardless of
		 * that callback's own priority.
		 */
		add_action( 'admin_head', array( $this, 'fix_elementor_malformed_submenu_slugs' ) );
	}

	/**
	 * Removes any admin submenu item whose slug is a full URL instead of a
	 * page slug — see the compatibility-fix docblock above. A URL there is
	 * never something core can route to correctly regardless of which
	 * plugin put it there, so removing the broken item (rather than
	 * guessing at Elementor's intended destination) is the safe fix.
	 */
	public function fix_elementor_malformed_submenu_slugs() {
		global $submenu;

		if ( empty( $submenu ) || ! is_array( $submenu ) ) {
			return;
		}

		foreach ( $submenu as $parent_slug => $items ) {
			foreach ( $items as $item ) {
				$slug = $item[2] ?? '';
				if ( is_string( $slug ) && ( 0 === strpos( $slug, 'http://' ) || 0 === strpos( $slug, 'https://' ) ) ) {
					remove_submenu_page( $parent_slug, $slug );
				}
			}
		}
	}

	public function theme_support() {
		add_theme_support( 'elementor' );
		// Elementor asks the theme for a max content width for its own
		// "boxed" layout setting — reuse the same figure the header/footer/
		// blog containers already use, so a boxed Elementor page lines up
		// with the rest of the site instead of guessing its own number.
		add_theme_support( 'align-wide' );
	}

	/**
	 * Loads the same design tokens + base typography into the Elementor
	 * editor's preview iframe, so a widget looks like the front end while
	 * it's being edited, not like an unstyled default.
	 */
	public function editor_assets() {
		wp_enqueue_style( 'evolyon-base', EVOLYON_URI . '/assets/css/base.css', array(), evolyon_asset_version( '/assets/css/base.css' ) );
		wp_add_inline_style( 'evolyon-base', Evolyon_Token_Output::instance()->get_css() );
	}

	public function frontend_assets() {
		wp_enqueue_style( 'evolyon-elementor', EVOLYON_URI . '/assets/css/elementor.css', array( 'evolyon-base' ), evolyon_asset_version( '/assets/css/elementor.css' ) );
	}

	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'evolyon',
			array(
				'title' => __( 'Evolyon', 'evolyon' ),
				'icon'  => 'font',
			)
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once EVOLYON_DIR . '/inc/elementor/widgets/class-widget-button.php';
		$widgets_manager->register( new Evolyon_Elementor_Widget_Button() );
	}
}
