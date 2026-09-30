<?php
/**
 * Renders the document shell and the header, built from the Header Builder
 * config (see inc/header-builder/class-evolyon-header-render.php).
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

$evln_scheme = get_theme_mod( 'evolyon_color_scheme', 'system' );
$evln_forced = in_array( $evln_scheme, array( 'light', 'dark' ), true ) ? $evln_scheme : '';
?><!doctype html>
<html <?php language_attributes(); ?><?php echo $evln_forced ? ' data-theme="' . esc_attr( $evln_forced ) . '"' : ''; ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
/*
 * Applies a visitor's own dark-mode-toggle choice (see
 * Evolyon_Element_Dark_Mode_Toggle) before the stylesheet below paints,
 * so a returning visitor who chose a different scheme than the site
 * owner's own Customizer default doesn't see one theme flash before the
 * other applies. Inline and synchronous — a deferred/footer script would
 * run too late to prevent the flash it exists to avoid.
 */
try {
	var evlnStoredTheme = localStorage.getItem( 'evln-theme' );
	if ( 'light' === evlnStoredTheme || 'dark' === evlnStoredTheme ) {
		document.documentElement.setAttribute( 'data-theme', evlnStoredTheme );
	}
	// Same reasoning, for a visitor's own Palette Switcher choice (see
	// Evolyon_Element_Palette_Switcher) — only takes effect where that
	// element's all-palettes CSS is actually enqueued, harmless elsewhere.
	var evlnStoredPalette = localStorage.getItem( 'evln-palette' );
	if ( evlnStoredPalette ) {
		document.documentElement.setAttribute( 'data-palette', evlnStoredPalette );
	}
	// Same reasoning, for a visitor's own Direction Toggle choice (see
	// Evolyon_Element_Direction_Toggle).
	var evlnStoredDirection = localStorage.getItem( 'evln-direction' );
	if ( 'ltr' === evlnStoredDirection || 'rtl' === evlnStoredDirection ) {
		document.documentElement.dir = evlnStoredDirection;
	}
} catch ( e ) {}
</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
/**
 * Fires right after the opening body tag, before any header content.
 * Evolyon_Accessibility hooks the skip link in here.
 */
do_action( 'evolyon/template/before_site' );
?>
<div id="evln-page" class="evln-site">
<?php evolyon_render_header(); ?>
