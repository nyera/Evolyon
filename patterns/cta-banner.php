<?php
/**
 * Title: CTA Banner
 * Slug: evolyon/cta-banner
 * Categories: call-to-action
 * Description: A full-width band with a heading and a button, for the bottom of a page.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","backgroundColor":"primary","textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--2-xl);padding-bottom:var(--wp--preset--spacing--2-xl)">
	<!-- wp:heading {"textAlign":"center","level":2} -->
	<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Ready to see it for yourself?', 'evolyon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"accent"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-accent-background-color has-background wp-element-button"><?php echo esc_html__( 'Start free trial', 'evolyon' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
