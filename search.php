<?php
/**
 * Search results template.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();
$evln_layout = Evolyon_Blog::instance()->get_layout();
?>
<main id="evln-content" class="evln-main">
	<div class="evln-container">
		<header class="evln-archive-header">
			<h1 class="evln-archive-header__title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Search results for: %s', 'evolyon' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="evln-post-grid evln-post-grid--<?php echo esc_attr( $evln_layout ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content' );
				endwhile;
				?>
			</div>
			<div class="evln-pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<div class="evln-404" style="padding-block: var(--evln-sp-2xl)">
				<p class="evln-404__desc"><?php esc_html_e( 'Nothing matched your search. Try different keywords, or browse recent posts instead.', 'evolyon' ); ?></p>
				<div class="evln-404__search"><?php get_search_form(); ?></div>
				<a class="evln-btn evln-btn--primary" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Browse the blog', 'evolyon' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
