<?php
/**
 * Dark mode toggle — a per-visitor override on top of the site owner's own
 * Customizer light/dark/system choice (Appearance → Customize → Evolyon →
 * Design System), not a replacement for it. That setting still decides what
 * a first-time visitor sees (via the data-theme attribute header.php writes
 * on <html>); this button lets a visitor flip it for their own session,
 * persisted in localStorage and read back by an early inline script in
 * header.php so there's no flash of the wrong theme on reload.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Dark_Mode_Toggle extends Evolyon_Header_Element {

	public $id = 'dark_mode_toggle';

	public function __construct() {
		$this->label = __( 'Dark mode toggle', 'evolyon' );
	}

	public function render( $settings ) {
		?>
		<button type="button" class="evln-icon-btn evln-theme-toggle" aria-pressed="false" data-evln-theme-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle dark mode', 'evolyon' ); ?></span>
			<svg class="evln-theme-toggle__icon evln-theme-toggle__icon--sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>
			<svg class="evln-theme-toggle__icon evln-theme-toggle__icon--moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z"></path></svg>
		</button>
		<?php
	}
}
