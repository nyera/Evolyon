<?php
/**
 * Search element — a toggle button that opens a full-screen search modal
 * (matching the approved design system's own reference implementation),
 * submitting to the real WordPress search results page. No AJAX/live-
 * results yet (that's a later phase); this ships a real, working
 * submit-to-results search from day one.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Search extends Evolyon_Header_Element {

	public $id = 'search';

	public function __construct() {
		$this->label = __( 'Search', 'evolyon' );
	}

	/**
	 * A handful of common, generic destinations a visitor is likely to
	 * search for — not results of an actual search index (this element
	 * doesn't have one), just quick-fill shortcuts. A child theme or
	 * plugin can replace these with real popular-search terms once
	 * search analytics exist.
	 *
	 * @return array label => search term
	 */
	private function popular_searches() {
		return apply_filters(
			'evolyon/search/popular_terms',
			array(
				__( 'Pricing', 'evolyon' )  => __( 'pricing', 'evolyon' ),
				__( 'Blog', 'evolyon' )     => __( 'blog', 'evolyon' ),
				__( 'Shop', 'evolyon' )     => __( 'shop', 'evolyon' ),
				__( 'Contact', 'evolyon' )  => __( 'contact', 'evolyon' ),
			)
		);
	}

	public function render( $settings ) {
		/*
		 * This element can render more than once per page (a desktop
		 * placement and a separate mobile placement both exist in the DOM
		 * at once — CSS decides which is visible). No fixed id, so no
		 * duplicate-id HTML: header-frontend.js finds each button's own
		 * modal via closest('.evln-search-toggle') instead.
		 */
		?>
		<div class="evln-search-toggle">
			<button type="button" class="evln-icon-btn evln-search-toggle__btn" aria-expanded="false" aria-haspopup="dialog">
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'evolyon' ); ?></span>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
			</button>
			<div class="evln-search-modal" hidden>
				<div class="evln-search-modal__backdrop" data-evln-search-close></div>
				<div class="evln-search-modal__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site search', 'evolyon' ); ?>">
					<div class="evln-search-modal__header">
						<h2 class="evln-search-modal__title">
							<?php
							printf(
								/* translators: %s: site name */
								esc_html__( 'Search %s', 'evolyon' ),
								esc_html( get_bloginfo( 'name' ) )
							);
							?>
						</h2>
						<button type="button" class="evln-search-modal__close" data-evln-search-close>
							<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'evolyon' ); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
						</button>
					</div>
					<form role="search" method="get" class="evln-search-modal__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<svg class="evln-search-modal__form-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
						<input type="search" class="evln-search-modal__input" name="s" placeholder="<?php esc_attr_e( 'Search pages, articles, products…', 'evolyon' ); ?>" autocomplete="off">
					</form>
					<?php $popular = $this->popular_searches(); ?>
					<?php if ( $popular ) : ?>
						<div class="evln-search-modal__popular">
							<span class="evln-search-modal__popular-label"><?php esc_html_e( 'Popular searches', 'evolyon' ); ?></span>
							<div class="evln-search-modal__popular-list">
								<?php foreach ( $popular as $label => $term ) : ?>
									<button type="button" class="evln-search-modal__pill" data-evln-search-term="<?php echo esc_attr( $term ); ?>"><?php echo esc_html( $label ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
