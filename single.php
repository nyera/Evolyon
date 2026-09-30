<?php
/**
 * Single post template: featured image, meta, content, tags, author box,
 * post navigation, related posts, and comments.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="evln-content" class="evln-main">
	<div class="evln-container evln-container--narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'evln-single-post' ); ?>>
				<header class="evln-single-post__header">
					<?php
					$categories = get_the_category();
					if ( ! empty( $categories ) ) :
						?>
						<a class="evln-single-post__cat" href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
					<?php endif; ?>

					<h1 class="evln-single-post__title"><?php the_title(); ?></h1>

					<div class="evln-single-post__meta">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
						<span><?php the_author(); ?></span>
						<span class="evln-post-card__dot" aria-hidden="true">&middot;</span>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span class="evln-post-card__dot" aria-hidden="true">&middot;</span>
						<span><?php echo esc_html( Evolyon_Blog::instance()->reading_time( get_the_ID() ) ); ?></span>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="evln-single-post__thumb">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="evln-single-post__content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="evln-page-links" aria-label="' . esc_attr__( 'Page', 'evolyon' ) . '">' . esc_html__( 'Pages:', 'evolyon' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php
				$tags = get_the_tags();
				if ( $tags ) :
					?>
					<div class="evln-single-post__tags">
						<?php foreach ( $tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="evln-tag"><?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( get_theme_mod( 'evolyon_blog_show_author_box', true ) ) : ?>
					<?php get_template_part( 'template-parts/author-box' ); ?>
				<?php endif; ?>

				<nav class="evln-post-nav" aria-label="<?php esc_attr_e( 'More posts', 'evolyon' ); ?>">
					<div class="evln-post-nav__prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
					<div class="evln-post-nav__next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
				</nav>

				<?php if ( get_theme_mod( 'evolyon_blog_show_related', true ) ) : ?>
					<?php get_template_part( 'template-parts/related-posts' ); ?>
				<?php endif; ?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
