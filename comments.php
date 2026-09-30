<?php
/**
 * Comments template.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div class="evln-comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="evln-comments__title">
			<?php
			$count = get_comments_number();
			printf(
				/* translators: %s: number of comments */
				esc_html( _n( '%s comment', '%s comments', $count, 'evolyon' ) ),
				esc_html( number_format_i18n( $count ) )
			);
			?>
		</h2>

		<ol class="evln-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 44,
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="evln-comments__closed"><?php esc_html_e( 'Comments are closed.', 'evolyon' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'    => 'evln-comment-form',
			'class_submit'  => 'evln-btn evln-btn--primary',
			'title_reply'   => __( 'Leave a comment', 'evolyon' ),
			'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'evolyon' ) . '</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
		)
	);
	?>
</div>
