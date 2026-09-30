<?php
/**
 * Static page template — a title and content, no post-specific meta.
 *
 * When a page is built with Elementor, the title/thumbnail chrome and the
 * narrow reading-width container are both skipped: Elementor's own sections
 * are meant to run full-bleed, and the theme's own page title would just
 * duplicate whatever heading the Elementor layout already has. This is the
 * one template-level check the Elementor compatibility layer needs — see
 * inc/elementor/class-evolyon-elementor.php for the rest.
 *
 * The same bypass applies to a demo-generated page (marked with
 * _evolyon_demo_marker, e.g. About/Contact from the SaaS demo): its content
 * already opens with a full-width, two-column hero section supplying its
 * own <h1> — the theme's own generic centered title would duplicate it, and
 * the narrow container would clip every full-width section below it, the
 * same problem front-page.php exists to avoid for the front page itself.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$evln_is_elementor    = function_exists( 'evolyon_is_built_with_elementor' ) && evolyon_is_built_with_elementor( get_the_ID() );
$evln_is_demo_content = (bool) get_post_meta( get_the_ID(), '_evolyon_demo_marker', true );
$evln_skip_chrome     = $evln_is_elementor || $evln_is_demo_content;
?>
<main id="evln-content" class="evln-main <?php echo $evln_skip_chrome ? 'evln-main--elementor' : ''; ?>">
	<?php if ( $evln_skip_chrome ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	<?php else : ?>
		<div class="evln-container evln-container--narrow">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'evln-page' ); ?>>
					<header class="evln-page__header">
						<h1 class="evln-page__title"><?php the_title(); ?></h1>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="evln-page__thumb"><?php the_post_thumbnail( 'large' ); ?></div>
					<?php endif; ?>

					<div class="evln-page__content">
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

					<?php if ( comments_open() || get_comments_number() ) : ?>
						<?php comments_template(); ?>
					<?php endif; ?>
				</article>
				<?php
			endwhile;
			?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
