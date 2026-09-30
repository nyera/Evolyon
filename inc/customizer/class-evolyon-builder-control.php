<?php
/**
 * Shared Customize_Control for both the Header and Footer builders. The
 * control itself only prints a mount point + localizes the schema the
 * setting needs (element/column-type registry, starter variants, row/zone
 * labels); customizer-builder.js builds the actual rows/zones/columns UI
 * client-side from the current JSON setting value.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Builder_Control extends WP_Customize_Control {

	public $type = 'evolyon_builder';

	/** 'header' or 'footer'. */
	public $builder = 'header';

	public function enqueue() {
		wp_enqueue_style(
			'evolyon-customizer-builder',
			EVOLYON_URI . '/assets/css/customizer-builder.css',
			array(),
			evolyon_asset_version( '/assets/css/customizer-builder.css' )
		);

		wp_enqueue_script(
			'evolyon-customizer-builder',
			EVOLYON_URI . '/assets/js/customizer-builder.js',
			array( 'customize-controls' ),
			evolyon_asset_version( '/assets/js/customizer-builder.js' ),
			true
		);

		if ( 'header' === $this->builder ) {
			$registry           = Evolyon_Header_Elements::instance()->get_registry_for_js();
			$mobile_element_ids = Evolyon_Header_Config::MOBILE_ELEMENT_TYPES;
			$mobile_elements    = array();
			foreach ( $mobile_element_ids as $id ) {
				if ( isset( $registry[ $id ] ) ) {
					$mobile_elements[ $id ] = $registry[ $id ];
				}
			}

			wp_localize_script(
				'evolyon-customizer-builder',
				'evlnHeaderBuilderData',
				array(
					'elements'       => $registry,
					'mobileElements' => $mobile_elements,
					'mobileModeCaps' => Evolyon_Header_Config::MOBILE_MODE_CAPS,
					'variants'       => Evolyon_Header_Config::instance()->variants(),
					'rows'           => array(
						'top'  => __( 'Top Bar', 'evolyon' ),
						'main' => __( 'Main Row', 'evolyon' ),
					),
					'zones'          => array(
						'start'  => __( 'Start', 'evolyon' ),
						'center' => __( 'Center', 'evolyon' ),
						'end'    => __( 'End', 'evolyon' ),
					),
					'defaults'       => Evolyon_Header_Config::instance()->defaults(),
					'i18n'           => $this->shared_i18n(),
				)
			);
		} else {
			wp_localize_script(
				'evolyon-customizer-builder',
				'evlnFooterBuilderData',
				array(
					'columnTypes'   => Evolyon_Footer_Config::instance()->column_types(),
					'menuLocations' => Evolyon_Footer_Config::instance()->menu_locations(),
					'defaults'      => Evolyon_Footer_Config::instance()->defaults(),
					'i18n'          => $this->shared_i18n(),
				)
			);
		}
	}

	private function shared_i18n() {
		return array(
			'addElement'       => __( 'Add element', 'evolyon' ),
			'remove'           => __( 'Remove', 'evolyon' ),
			'settings'         => __( 'Settings', 'evolyon' ),
			'enableRow'        => __( 'Enable this row', 'evolyon' ),
			'applyVariant'     => __( 'Use this layout', 'evolyon' ),
			'confirmVariant'   => __( 'Replace the current layout with this one? Behavior settings are kept.', 'evolyon' ),
			'unavailableNote'  => __( 'Unavailable:', 'evolyon' ),
			'empty'            => __( 'This zone is empty — add an element below.', 'evolyon' ),
			'layoutTab'        => __( 'Layout', 'evolyon' ),
			'behaviorTab'      => __( 'Behavior', 'evolyon' ),
			'variantsTab'      => __( 'Variants', 'evolyon' ),
			'mobileTab'        => __( 'Mobile', 'evolyon' ),
			'columnsTab'       => __( 'Columns', 'evolyon' ),
			'bottomBarTab'     => __( 'Bottom Bar', 'evolyon' ),
			'mobileModeHint'   => __( 'Compact shows only the essentials; Standard is the balanced default; Expanded adds a second row for search or a CTA.', 'evolyon' ),
			'mobileElements'   => __( 'Elements shown in the mobile bar', 'evolyon' ),
			'mobileElementCap' => __( 'This mode allows up to %d element(s) here.', 'evolyon' ),
			'moveUp'           => __( 'Move up', 'evolyon' ),
			'moveDown'         => __( 'Move down', 'evolyon' ),
			'secondaryRow'     => __( 'Secondary row (Expanded mode only)', 'evolyon' ),
		);
	}

	public function render_content() {
		if ( $this->label ) {
			echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
		}
		if ( $this->description ) {
			echo '<span class="description customize-control-description">' . wp_kses_post( $this->description ) . '</span>';
		}
		?>
		<div class="evln-builder" data-builder="<?php echo esc_attr( $this->builder ); ?>" data-customize-setting-link="<?php echo esc_attr( $this->id ); ?>">
			<p class="evln-builder__loading"><?php esc_html_e( 'Loading builder…', 'evolyon' ); ?></p>
		</div>
		<?php
	}
}
