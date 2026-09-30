<?php
/**
 * Related posts shown under a single post — same primary category.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

$evln_related = Evolyon_Blog::instance()->get_related( get_the_ID() );
if ( ! $evln_related->have_posts() ) {
	return;
}
?>
<section class="evln-related-posts" aria-labelledby="evln-related-heading">
	<h2 id="evln-related-heading" class="evln-related-posts__title"><?php esc_html_e( 'Related posts', 'evolyon' ); ?></h2>
	<div class="evln-related-posts__grid">
		<?php
		while ( $evln_related->have_posts() ) :
			$evln_related->the_post();
			?>
			<article class="evln-related-card">
				<?php if ( has_post_thumbnail() ) : ?>
					<a class="evln-related-card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php the_post_thumbnail( 'medium' ); ?>
					</a>
				<?php endif; ?>
				<h3 class="evln-related-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<time class="evln-related-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
