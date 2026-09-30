<?php
/**
 * Text-direction (LTR/RTL) toggle — a per-visitor preview, not a substitute
 * for actually setting the site to an RTL language. The theme's layout is
 * already built entirely on CSS logical properties (margin-inline-start,
 * not margin-left — see DEVELOPER.md), specifically so flipping the native
 * `dir` attribute mirrors the whole layout automatically with no
 * dir-specific CSS of its own required here, the same way it already does
 * when a site's actual language is RTL.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Direction_Toggle extends Evolyon_Header_Element {

	public $id = 'direction_toggle';

	public function __construct() {
		$this->label = __( 'Direction toggle', 'evolyon' );
	}

	public function render( $settings ) {
		?>
		<button type="button" class="evln-icon-btn" aria-pressed="<?php echo is_rtl() ? 'true' : 'false'; ?>" data-evln-direction-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle text direction', 'evolyon' ); ?></span>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3c2.5 2.5 4 5.5 4 9s-1.5 6.5-4 9c-2.5-2.5-4-5.5-4-9s1.5-6.5 4-9Z"></path></svg>
		</button>
		<?php
	}
}
