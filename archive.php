<?php
/**
 * Archive template: blog index, category/tag/date archives, and author
 * archives all share this — the loop and pagination are identical, only
 * the heading text changes.
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
			<h1 class="evln-archive-header__title"><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<div class="evln-archive-header__desc"><?php the_archive_description(); ?></div>
			<?php endif; ?>
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

			<div class="evln-pagination">
				<?php
				the_posts_pagination(
					array(
						'prev_text' => __( '&larr; Newer', 'evolyon' ),
						'next_text' => __( 'Older &rarr;', 'evolyon' ),
					)
				);
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts found.', 'evolyon' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
