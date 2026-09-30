<?php
/**
 * 404 template.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="evln-content" class="evln-main">
	<div class="evln-container evln-container--narrow evln-404">
		<p class="evln-404__code">404</p>
		<h1 class="evln-404__title"><?php esc_html_e( 'This page could not be found', 'evolyon' ); ?></h1>
		<p class="evln-404__desc"><?php esc_html_e( 'The page you were looking for may have been moved or no longer exists. Try searching for what you need.', 'evolyon' ); ?></p>
		<div class="evln-404__search"><?php get_search_form(); ?></div>
		<a class="evln-btn evln-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'evolyon' ); ?></a>
	</div>
</main>
<?php
get_footer();
