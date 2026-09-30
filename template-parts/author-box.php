<?php
/**
 * Author box shown under a single post.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="evln-author-box">
	<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', '', array( 'class' => 'evln-author-box__avatar' ) ); ?>
	<div class="evln-author-box__body">
		<span class="evln-author-box__label"><?php esc_html_e( 'Written by', 'evolyon' ); ?></span>
		<h3 class="evln-author-box__name">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
		</h3>
		<?php if ( get_the_author_meta( 'description' ) ) : ?>
			<p class="evln-author-box__bio"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
