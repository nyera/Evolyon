<?php
/**
 * Front page template. Takes precedence over page.php automatically when
 * Settings → Reading is set to a static front page — deliberately does NOT
 * repeat the generic page-title treatment page.php uses, since a rich
 * homepage (built from patterns, a demo import, or Elementor) always
 * supplies its own heading as the first thing in its content. Rendering
 * both stacked one page title directly on top of the content's own hero
 * heading, which is exactly the kind of redundant, unpolished result a
 * "just reuse page.php" shortcut produces.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="evln-content" class="evln-main evln-main--front">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
