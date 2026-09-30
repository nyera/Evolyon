<?php
/**
 * Renders the mobile header as its own intentional composition — not the
 * desktop rows compressed by a media query. It has its own primary/
 * secondary row layout, its own configurable element subset and order
 * (drawn from the same Evolyon_Header_Element registry the desktop rows
 * use, so a CTA's text/url/style is never entered twice), and its own
 * drill-down navigation drawer with real nested-level + back-button
 * behavior instead of the desktop menu dumped in vertically.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Mobile_Header_Render {

	private static $instance = null;

	private $panel_counter = 0;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function render( $config ) {
		$mobile = $config['mobile'];

		$style_vars = sprintf(
			'--evln-mh-height:%1$dpx;--evln-mh-padding:%2$dpx;--evln-mh-icon:%3$dpx;--evln-mh-logo-max:%4$dpx;',
			(int) $mobile['height'],
			(int) $mobile['padding_inline'],
			(int) $mobile['icon_size'],
			(int) $mobile['logo_max_width']
		);
		if ( $mobile['bg'] ) {
			$style_vars .= '--evln-mh-bg:' . esc_attr( $mobile['bg'] ) . ';';
		}
		if ( $mobile['text_color'] ) {
			$style_vars .= '--evln-mh-text:' . esc_attr( $mobile['text_color'] ) . ';';
		}

		$classes = array( 'evln-header-mobile', 'evln-header-mobile--mode-' . $mobile['mode'] );
		if ( ! $mobile['shadow'] ) {
			$classes[] = 'evln-header-mobile--no-shadow';
		}
		if ( ! $mobile['border'] ) {
			$classes[] = 'evln-header-mobile--no-border';
		}

		printf( '<div class="%1$s" style="%2$s">', esc_attr( implode( ' ', $classes ) ), esc_attr( $style_vars ) );

		$this->render_primary_row( $config );

		if ( 'expanded' === $mobile['mode'] && ! empty( $mobile['secondary_row']['enabled'] ) ) {
			$this->render_secondary_row( $config, $mobile['secondary_row']['elements'] );
		}

		echo '</div><!-- .evln-header-mobile -->';

		$this->render_drawer( $mobile['menu_style'] );
	}

	private function render_primary_row( $config ) {
		$mobile   = $config['mobile'];
		$registry = Evolyon_Header_Elements::instance();
		$priority = 2; // 0 = menu toggle, 1 = logo.

		echo '<div class="evln-mobile-row evln-mobile-row--primary">';

		if ( 'logo-center' === $mobile['alignment'] ) {
			echo '<div class="evln-mobile-zone evln-mobile-zone--start">';
			$this->render_menu_toggle( $mobile );
			echo '</div>';

			echo '<div class="evln-mobile-zone evln-mobile-zone--center">';
			$this->render_logo_item();
			echo '</div>';

			echo '<div class="evln-mobile-zone evln-mobile-zone--end">';
			foreach ( $mobile['elements'] as $type ) {
				$this->render_priority_item( $registry, $config, $type, $priority++ );
			}
			echo '</div>';
		} else {
			echo '<div class="evln-mobile-zone evln-mobile-zone--start">';
			$this->render_menu_toggle( $mobile );
			$this->render_logo_item();
			echo '</div>';

			echo '<div class="evln-mobile-zone evln-mobile-zone--end">';
			foreach ( $mobile['elements'] as $type ) {
				$this->render_priority_item( $registry, $config, $type, $priority++ );
			}
			echo '</div>';
		}

		echo '</div><!-- .evln-mobile-row--primary -->';
	}

	private function render_secondary_row( $config, $elements ) {
		$registry = Evolyon_Header_Elements::instance();
		$available = array_filter( $elements, function ( $type ) use ( $registry ) {
			$element = $registry->get( $type );
			return $element && $element->is_available();
		} );

		if ( empty( $available ) ) {
			return;
		}

		echo '<div class="evln-mobile-row evln-mobile-row--secondary">';
		foreach ( $available as $type ) {
			$settings = $registry->find_settings_in_rows( $type, $config['rows'] );
			$registry->render( $type, $settings );
		}
		echo '</div>';
	}

	/**
	 * Skips the wrapper entirely when the element type is unavailable (no
	 * WooCommerce for Cart, etc.) — an empty wrapper would still consume a
	 * flex gap and a priority slot for nothing.
	 */
	private function render_priority_item( $registry, $config, $type, $priority ) {
		$element = $registry->get( $type );
		if ( ! $element || ! $element->is_available() ) {
			return;
		}

		$settings = $registry->find_settings_in_rows( $type, $config['rows'] );
		echo '<div class="evln-mobile-item" data-priority="' . (int) $priority . '">';
		$registry->render( $type, $settings );
		echo '</div>';
	}

	private function render_logo_item() {
		echo '<div class="evln-mobile-item evln-mobile-item--logo" data-priority="1">';
		Evolyon_Header_Elements::instance()->render( 'logo', array() );
		echo '</div>';
	}

	private function render_menu_toggle( $mobile ) {
		$target = 'fullscreen' === $mobile['menu_style'] ? 'evln-mnav-fullscreen' : ( 'dropdown' === $mobile['menu_style'] ? 'evln-mnav-dropdown' : 'evln-mnav-offcanvas' );
		?>
		<div class="evln-mobile-item evln-mobile-item--toggle" data-priority="0">
			<button
				type="button"
				class="evln-mobile-toggle"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $target ); ?>"
				data-mnav-open
				data-mnav-style="<?php echo esc_attr( $mobile['menu_style'] ); ?>"
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'evolyon' ); ?></span>
				<span class="evln-mobile-toggle__bar"></span>
				<span class="evln-mobile-toggle__bar"></span>
				<span class="evln-mobile-toggle__bar"></span>
			</button>
		</div>
		<?php
	}

	private function render_drawer( $menu_style ) {
		$tree = Evolyon_Mobile_Nav_Tree::instance()->get_tree();
		$id   = 'fullscreen' === $menu_style ? 'evln-mnav-fullscreen' : ( 'dropdown' === $menu_style ? 'evln-mnav-dropdown' : 'evln-mnav-offcanvas' );

		printf(
			'<div class="evln-mnav-drawer evln-mnav-drawer--%1$s" id="%2$s" hidden>',
			esc_attr( $menu_style ),
			esc_attr( $id )
		);

		if ( 'dropdown' !== $menu_style ) {
			echo '<div class="evln-mnav-drawer__backdrop" data-mnav-close></div>';
		}

		echo '<div class="evln-mnav-drawer__panel" role="dialog" aria-modal="true" tabindex="-1" aria-label="' . esc_attr__( 'Menu', 'evolyon' ) . '">';

		echo '<div class="evln-mnav-drawer__header">';
		printf(
			'<a class="evln-logo-text evln-mnav-drawer__logo" href="%1$s">%2$s</a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
		echo '<button type="button" class="evln-mnav-drawer__close" data-mnav-close>';
		echo '<span class="screen-reader-text">' . esc_html__( 'Close menu', 'evolyon' ) . '</span>';
		echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line></svg>';
		echo '</button>';
		echo '</div><!-- .evln-mnav-drawer__header -->';

		echo '<div class="evln-mnav" data-mnav-root>';
		if ( empty( $tree ) ) {
			echo '<p class="evln-mnav__empty">' . esc_html__( 'No menu has been assigned yet.', 'evolyon' ) . '</p>';
		} else {
			$this->panel_counter = 0;
			$this->render_panel( $tree, 0, 'root', __( 'Menu', 'evolyon' ), true );
		}
		echo '</div><!-- .evln-mnav -->';

		Evolyon_Social_Profiles::instance()->render_icons( 'evln-mnav-drawer__social' );

		echo '</div><!-- .evln-mnav-drawer__panel -->';
		echo '</div><!-- .evln-mnav-drawer -->';
	}

	/**
	 * Emits one panel `<div>` for this level, then recurses to emit each
	 * expandable child's panel as a following sibling — depth-first, so the
	 * result is a flat sequence of sibling panels the JS can show/hide by id,
	 * with a parent→child chain it can walk for "back" navigation.
	 */
	private function render_panel( $nodes, $level, $panel_id, $title, $is_root ) {
		printf(
			'<div class="evln-mnav__panel%1$s" data-panel-id="%2$s" data-level="%3$d"%4$s>',
			$is_root ? ' is-active' : '',
			esc_attr( $panel_id ),
			(int) $level,
			$is_root ? '' : ' hidden'
		);

		if ( ! $is_root ) {
			printf(
				'<button type="button" class="evln-mnav__back" data-mnav-back><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg><span>%s</span></button>',
				esc_html__( 'Back', 'evolyon' )
			);
		}
		printf( '<div class="evln-mnav__panel-title">%s</div>', esc_html( $title ) );

		echo '<ul class="evln-mnav__list">';
		$child_panels = array();

		foreach ( $nodes as $node ) {
			$has_children = ! empty( $node['children'] );
			$child_id     = $has_children ? 'panel-' . ( ++$this->panel_counter ) : '';

			echo '<li class="evln-mnav__item">';
			printf(
				'<a class="evln-mnav__link" href="%1$s"%2$s>%3$s</a>',
				esc_url( $node['url'] ),
				'_blank' === $node['target'] ? ' target="_blank" rel="noopener noreferrer"' : '',
				esc_html( $node['title'] )
			);
			if ( $has_children ) {
				printf(
					'<button type="button" class="evln-mnav__expand" data-mnav-open-panel="%1$s" aria-label="%2$s"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 6 15 12 9 18"></polyline></svg></button>',
					esc_attr( $child_id ),
					esc_attr(
						sprintf(
							/* translators: %s: submenu item title */
							__( 'Open %s submenu', 'evolyon' ),
							$node['title']
						)
					)
				);
				$child_panels[] = array( 'id' => $child_id, 'title' => $node['title'], 'children' => $node['children'] );
			}
			echo '</li>';
		}

		echo '</ul>';
		echo '</div><!-- .evln-mnav__panel -->';

		foreach ( $child_panels as $child ) {
			$this->render_panel( $child['children'], $level + 1, $child['id'], $child['title'], false );
		}
	}
}
