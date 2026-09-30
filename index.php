<?php
/**
 * Fallback template — reached for the main blog index (is_home(), when no
 * home.php exists) and as the ultimate fallback WordPress's template
 * hierarchy uses when nothing more specific matches. Singular content and
 * category/tag/date archives have their own templates (single.php,
 * archive.php) and don't normally reach this file.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();
$evln_layout = Evolyon_Blog::instance()->get_layout();
?>
<main id="evln-content" class="evln-main">
	<div class="evln-container">
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<?php
			/*
			 * The "posts page" itself (Settings → Reading) has its own real
			 * title/excerpt fields that a site owner can already fill in from
			 * wp-admin — this reads them as an eyebrow/subtitle instead of
			 * leaving them unused, rather than hardcoding "Blog" copy into
			 * the theme template itself.
			 */
			$evln_blog_page_id = (int) get_option( 'page_for_posts' );
			$evln_blog_subtitle = $evln_blog_page_id ? get_the_excerpt( $evln_blog_page_id ) : '';
			?>
			<header class="evln-archive-header">
				<p class="evln-archive-header__eyebrow evln-eyebrow"><?php esc_html_e( 'Blog', 'evolyon' ); ?></p>
				<h1 class="evln-archive-header__title"><?php single_post_title(); ?></h1>
				<?php if ( $evln_blog_subtitle ) : ?>
					<p class="evln-archive-header__desc"><?php echo esc_html( $evln_blog_subtitle ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

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
			<p><?php esc_html_e( 'Nothing found.', 'evolyon' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
