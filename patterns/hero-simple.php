<?php
/**
 * Title: Simple Hero
 * Slug: evolyon/hero-simple
 * Categories: featured
 * Description: A centered headline, supporting text, and a primary call to action.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--3-xl);padding-bottom:var(--wp--preset--spacing--3-xl)">
	<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"x-large"} -->
	<h1 class="wp-block-heading has-text-align-center has-x-large-font-size"><?php echo esc_html__( 'A headline that states the one thing you do', 'evolyon' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","style":{"color":{}}} -->
	<p class="has-text-align-center"><?php echo esc_html__( 'One or two sentences backing that claim up — specific enough to be believable, short enough to actually get read.', 'evolyon' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button"><?php echo esc_html__( 'Get started', 'evolyon' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
