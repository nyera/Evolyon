<?php
/**
 * Palette switcher — lets a visitor live-preview any of the 9 built-in
 * palettes without a page reload, the same way a visitor can flip dark
 * mode (Evolyon_Element_Dark_Mode_Toggle): a data-palette attribute on
 * <html>, persisted in localStorage, read back by an early inline script.
 *
 * Deliberately NOT part of the header's default zone config — this is a
 * "try before you buy" feature for a theme-demo/marketing site (like the
 * Design Suite demo), not something every real client site should show
 * its visitors by default. A site owner places it explicitly, or a demo
 * importer configures it as part of that demo's own header setup.
 *
 * Because switching requires every palette's CSS to be reachable at once
 * (not just the site owner's one active palette), this element enqueues
 * Evolyon_Token_Output::get_all_palettes_css() itself, on demand — a real
 * site that never places this element never pays that CSS cost.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Palette_Switcher extends Evolyon_Header_Element {

	public $id = 'palette_switcher';

	public function __construct() {
		$this->label = __( 'Palette switcher', 'evolyon' );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_all_palettes_css' ), 20 );
	}

	/**
	 * Only enqueues once this element is confirmed to actually be placed
	 * somewhere in the active header config — checked directly rather than
	 * assuming, since the header can be reconfigured without this file
	 * changing.
	 */
	public function maybe_enqueue_all_palettes_css() {
		if ( ! $this->is_placed() ) {
			return;
		}
		wp_add_inline_style( 'evolyon-header', Evolyon_Token_Output::instance()->get_all_palettes_css() );
	}

	private function is_placed() {
		$config = Evolyon_Header_Config::instance()->get_active();
		foreach ( (array) $config['rows'] as $row ) {
			foreach ( (array) ( $row['zones'] ?? array() ) as $items ) {
				foreach ( (array) $items as $item ) {
					if ( isset( $item['type'] ) && 'palette_switcher' === $item['type'] ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function render( $settings ) {
		$registry = Evolyon_Token_Registry::instance();
		$active   = $registry->get_active_palette_id();
		?>
		<div class="evln-palette-switcher">
			<label class="evln-palette-switcher__label" for="evln-palette-select"><?php esc_html_e( 'Palette', 'evolyon' ); ?></label>
			<select id="evln-palette-select" class="evln-palette-switcher__select" data-evln-palette-select>
				<?php foreach ( $registry->get_palettes() as $id => $palette ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $active ); ?>><?php echo esc_html( $palette['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}
}
