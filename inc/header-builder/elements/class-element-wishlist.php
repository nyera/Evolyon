<?php
/**
 * Wishlist element — placeable now so a header layout is ready in advance,
 * but inert (unavailable, no markup) until the Evolyon Toolkit's wishlist
 * feature ships and flips the evolyon/wishlist/enabled filter.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Wishlist extends Evolyon_Header_Element {

	public $id = 'wishlist';

	public function __construct() {
		$this->label = __( 'Wishlist', 'evolyon' );
	}

	public function is_available() {
		/**
		 * Flip to true once a wishlist implementation (Evolyon Toolkit or a
		 * third-party plugin) registers itself.
		 *
		 * @param bool $enabled
		 */
		return apply_filters( 'evolyon/wishlist/enabled', false );
	}

	public function unavailable_reason() {
		return __( 'Requires Evolyon Toolkit', 'evolyon' );
	}

	public function render( $settings ) {
		$count = (int) apply_filters( 'evolyon/wishlist/count', 0 );
		$url   = apply_filters( 'evolyon/wishlist/url', home_url( '/' ) );
		?>
		<a class="evln-icon-btn evln-wishlist-link" href="<?php echo esc_url( $url ); ?>">
			<span class="screen-reader-text"><?php esc_html_e( 'Wishlist', 'evolyon' ); ?></span>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 20s-7-4.35-9.5-8.5C.7 8.1 2.4 4.5 6 4.5c2 0 3.3 1 4 2.1.7-1.1 2-2.1 4-2.1 3.6 0 5.3 3.6 3.5 7C19 15.65 12 20 12 20z"></path></svg>
			<?php if ( $count > 0 ) : ?>
				<span class="evln-badge-count"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}
}
