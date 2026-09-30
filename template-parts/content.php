<?php
/**
 * Post card used in the blog archive/search loop. Renders as a grid card or
 * a list row depending on the "Archive layout" Customizer setting.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

$evln_layout = Evolyon_Blog::instance()->get_layout();
?>
<article <?php post_class( 'evln-post-card evln-post-card--' . $evln_layout ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="evln-post-card__thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="evln-post-card__body">
		<div class="evln-post-card__meta">
			<?php
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				printf( '<span class="evln-post-card__cat">%s</span>', esc_html( $categories[0]->name ) );
			}
			?>
			<span class="evln-post-card__dot" aria-hidden="true">&middot;</span>
			<span class="evln-post-card__reading-time"><?php echo esc_html( Evolyon_Blog::instance()->reading_time( get_the_ID() ) ); ?></span>
		</div>

		<h2 class="evln-post-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="evln-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>

		<div class="evln-post-card__footer">
			<span class="evln-post-card__author"><?php the_author(); ?></span>
			<span class="evln-post-card__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
		</div>
	</div>
</article>
