<?php
/**
 * Evolyon theme bootstrap. Requires inc/* and wires the handful of hooks
 * that don't belong to any single module (setup, asset enqueueing, module
 * boot). Everything else lives in its own inc/ subsystem.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

define( 'EVOLYON_VERSION', '0.1.0' );
define( 'EVOLYON_DIR', get_template_directory() );
define( 'EVOLYON_URI', get_template_directory_uri() );
define( 'EVOLYON_MIN_PHP', '7.4' );

if ( version_compare( PHP_VERSION, EVOLYON_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version */
						__( 'Evolyon requires PHP %1$s or higher. You are running PHP %2$s — please ask your host to upgrade.', 'evolyon' ),
						EVOLYON_MIN_PHP,
						PHP_VERSION
					)
				)
			);
		}
	);
	return;
}

require EVOLYON_DIR . '/inc/helpers/functions-helpers.php';
require EVOLYON_DIR . '/inc/design-system/class-evolyon-color.php';
require EVOLYON_DIR . '/inc/design-system/class-evolyon-token-registry.php';
require EVOLYON_DIR . '/inc/design-system/class-evolyon-token-output.php';
require EVOLYON_DIR . '/inc/modules/class-evolyon-module.php';
require EVOLYON_DIR . '/inc/modules/class-evolyon-module-loader.php';
require EVOLYON_DIR . '/inc/integrations/class-evolyon-social-profiles.php';

require EVOLYON_DIR . '/inc/header-builder/class-evolyon-header-element.php';
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-nav-walker.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-logo.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-nav.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-search.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-account.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-dark-mode-toggle.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-palette-switcher.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-direction-toggle.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-wishlist.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-language-switcher.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-cta.php';
require EVOLYON_DIR . '/inc/header-builder/elements/class-element-social.php';
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-header-elements.php';
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-header-config.php';

/*
 * Instantiated here, at top-level, not lazily on first use (which used to
 * only happen from inside actual header rendering, in the <body> — well
 * after 'wp_enqueue_scripts' has already fired). Same timing lesson as
 * Evolyon_Elementor below: an element's own
 * constructor (e.g. Evolyon_Element_Palette_Switcher's) can register a
 * 'wp_enqueue_scripts' hook, but only if this registry — which is what
 * actually instantiates every element class — exists before that hook
 * fires. Confirmed live: the palette switcher's all-palettes CSS never
 * got enqueued at all under the old lazy-instantiation timing.
 */
Evolyon_Header_Elements::instance();
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-mobile-nav-tree.php';
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-mobile-header-render.php';
require EVOLYON_DIR . '/inc/header-builder/class-evolyon-header-render.php';

require EVOLYON_DIR . '/inc/footer-builder/class-evolyon-footer-config.php';
require EVOLYON_DIR . '/inc/footer-builder/class-evolyon-footer-render.php';

require EVOLYON_DIR . '/inc/customizer/class-evolyon-customizer.php';
require EVOLYON_DIR . '/inc/customizer/class-evolyon-header-footer-customizer.php';
require EVOLYON_DIR . '/inc/accessibility/class-evolyon-accessibility.php';
require EVOLYON_DIR . '/inc/performance/class-evolyon-performance.php';
require EVOLYON_DIR . '/inc/admin/class-evolyon-requirements.php';
require EVOLYON_DIR . '/inc/template-functions/class-evolyon-blog.php';

/*
 * Elementor loads as a plugin too, so it's already present by the time
 * functions.php runs — same early-instantiation reasoning applied elsewhere
 * in this file (add_theme_support('elementor') needs 'after_setup_theme',
 * which the init:20 module loader would miss).
 */
if ( did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' ) ) {
	require EVOLYON_DIR . '/inc/elementor/class-evolyon-elementor.php';
	Evolyon_Elementor::instance();
}

/**
 * Core theme setup: text domain, supported features, content width.
 */
function evolyon_setup() {
	load_theme_textdomain( 'evolyon', EVOLYON_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 160,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	$GLOBALS['content_width'] = 1180;
}
add_action( 'after_setup_theme', 'evolyon_setup' );

/**
 * Enqueue fonts, base stylesheet, and the generated token CSS on the front end.
 */
function evolyon_enqueue_assets() {
	$fonts_url = evolyon_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'evolyon-fonts', $fonts_url, array(), null );
	}

	wp_enqueue_style( 'evolyon-base', EVOLYON_URI . '/assets/css/base.css', array(), evolyon_asset_version( '/assets/css/base.css' ) );
	wp_add_inline_style( 'evolyon-base', Evolyon_Token_Output::instance()->get_css() );

	wp_enqueue_style( 'evolyon-header', EVOLYON_URI . '/assets/css/header.css', array( 'evolyon-base' ), evolyon_asset_version( '/assets/css/header.css' ) );
	wp_enqueue_style( 'evolyon-mobile-header', EVOLYON_URI . '/assets/css/mobile-header.css', array( 'evolyon-header' ), evolyon_asset_version( '/assets/css/mobile-header.css' ) );
	wp_enqueue_style( 'evolyon-footer', EVOLYON_URI . '/assets/css/footer.css', array( 'evolyon-base' ), evolyon_asset_version( '/assets/css/footer.css' ) );

	$evln_bp = (int) Evolyon_Header_Config::instance()->get_active()['mobile_breakpoint'];
	wp_add_inline_style(
		'evolyon-mobile-header',
		sprintf(
			'@media (max-width:%1$dpx){.evln-header-desktop{display:none;}.evln-header-mobile{display:block;}}',
			$evln_bp - 1
		)
	);

	wp_enqueue_script( 'evolyon-header-frontend', EVOLYON_URI . '/assets/js/header-frontend.js', array(), evolyon_asset_version( '/assets/js/header-frontend.js' ), true );
	wp_enqueue_script( 'evolyon-mobile-nav', EVOLYON_URI . '/assets/js/mobile-nav.js', array(), evolyon_asset_version( '/assets/js/mobile-nav.js' ), true );

	if ( is_singular() || is_home() || is_archive() || is_search() || is_404() ) {
		wp_enqueue_style( 'evolyon-blog', EVOLYON_URI . '/assets/css/blog.css', array( 'evolyon-base' ), evolyon_asset_version( '/assets/css/blog.css' ) );
	}

	if ( is_singular() ) {
		wp_enqueue_style( 'evolyon-content-blocks', EVOLYON_URI . '/assets/css/content-blocks.css', array( 'evolyon-base' ), evolyon_asset_version( '/assets/css/content-blocks.css' ) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'evolyon_enqueue_assets' );

/**
 * Renders the header/footer builder output. Called from header.php/footer.php.
 */
function evolyon_render_header() {
	Evolyon_Header_Render::instance()->render();
}

function evolyon_render_footer() {
	Evolyon_Footer_Render::instance()->render();
}

/**
 * Load the same tokens + a thin editor stylesheet inside the block editor,
 * so what an editor sees while writing matches the front end.
 */
function evolyon_editor_assets() {
	wp_enqueue_style( 'evolyon-editor', EVOLYON_URI . '/assets/css/editor.css', array(), evolyon_asset_version( '/assets/css/editor.css' ) );
	wp_add_inline_style( 'evolyon-editor', Evolyon_Token_Output::instance()->get_css() );

	wp_enqueue_style( 'evolyon-content-blocks', EVOLYON_URI . '/assets/css/content-blocks.css', array( 'evolyon-editor' ), evolyon_asset_version( '/assets/css/content-blocks.css' ) );
}
add_action( 'enqueue_block_editor_assets', 'evolyon_editor_assets' );

/**
 * Boot the module system. No modules are registered yet in Phase 1 — this
 * proves the loader mechanism without inventing placeholder features.
 */
function evolyon_boot_modules() {
	Evolyon_Module_Loader::instance()->boot();
}
add_action( 'init', 'evolyon_boot_modules', 20 );
