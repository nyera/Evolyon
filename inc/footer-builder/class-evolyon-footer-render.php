<?php
/**
 * Renders the footer from the active builder config.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Footer_Render {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function render() {
		$config = Evolyon_Footer_Config::instance()->get_active();

		$brand_items = array();
		$other_items = array();
		foreach ( $config['items'] as $item ) {
			if ( 'brand' === $item['type'] ) {
				$brand_items[] = $item;
			} else {
				$other_items[] = $item;
			}
		}

		echo '<footer id="evln-footer" class="evln-footer">';
		echo '<div class="evln-container">';

		/*
		 * Two-zone layout — a wide brand/intro column alongside a compact
		 * grid of nav/utility columns — rather than N equal-width tracks:
		 * the brand block reads as a paragraph, the nav columns read as a
		 * list, and forcing them to the same column width made neither look
		 * right. Falls back to the plain N-column grid when the config has
		 * no brand item at all (a footer built entirely from nav columns),
		 * so this never breaks otherwise-valid configurations.
		 */
		if ( $brand_items ) {
			echo '<div class="evln-footer-columns evln-footer-columns--split">';

			echo '<div class="evln-footer-primary">';
			foreach ( $brand_items as $item ) {
				$this->render_column( $item['type'], $item['settings'] );
			}
			echo '</div>';

			printf( '<div class="evln-footer-nav-columns evln-footer-nav-columns--%d">', max( 1, (int) $config['columns'] - count( $brand_items ) ) );
			foreach ( $other_items as $item ) {
				echo '<div class="evln-footer-column">';
				$this->render_column( $item['type'], $item['settings'] );
				echo '</div>';
			}
			echo '</div>';

			echo '</div>';
		} else {
			printf( '<div class="evln-footer-columns evln-footer-columns--%d">', (int) $config['columns'] );
			foreach ( $other_items as $item ) {
				echo '<div class="evln-footer-column">';
				$this->render_column( $item['type'], $item['settings'] );
				echo '</div>';
			}
			echo '</div>';
		}

		echo '</div>';

		if ( ! empty( $config['bottom']['enabled'] ) ) {
			$this->render_bottom( $config['bottom'] );
		}

		echo '</footer>';
	}

	private function render_column( $type, $settings ) {
		switch ( $type ) {
			case 'brand':
				$this->render_brand();
				break;
			case 'nav':
				$this->render_nav_column( $settings );
				break;
			case 'custom_html':
				$this->render_custom_html( $settings );
				break;
			case 'newsletter':
				$this->render_newsletter( $settings );
				break;
			case 'social':
				if ( ! empty( $settings['heading'] ) ) {
					echo '<h6 class="evln-footer-column__heading">' . esc_html( $settings['heading'] ) . '</h6>';
				}
				Evolyon_Social_Profiles::instance()->render_icons( 'evln-footer-social' );
				break;
		}
	}

	private function render_brand() {
		echo '<div class="evln-footer-brand">';
		if ( has_custom_logo() ) {
			the_custom_logo();
		} else {
			printf( '<span class="evln-logo-text">%s</span>', esc_html( get_bloginfo( 'name' ) ) );
		}
		$tagline = get_bloginfo( 'description' );
		if ( $tagline ) {
			echo '<p class="evln-footer-brand__tagline">' . esc_html( $tagline ) . '</p>';
		}
		echo '</div>';
	}

	private function render_nav_column( $settings ) {
		if ( ! empty( $settings['heading'] ) ) {
			echo '<h6 class="evln-footer-column__heading">' . esc_html( $settings['heading'] ) . '</h6>';
		}
		wp_nav_menu(
			array(
				'theme_location' => $settings['menu_location'] ?? 'footer-1',
				'container'      => false,
				'menu_class'     => 'evln-footer-column__menu',
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
	}

	private function render_custom_html( $settings ) {
		if ( ! empty( $settings['heading'] ) ) {
			echo '<h6 class="evln-footer-column__heading">' . esc_html( $settings['heading'] ) . '</h6>';
		}
		if ( ! empty( $settings['content'] ) ) {
			echo '<div class="evln-footer-column__html">' . wp_kses_post( $settings['content'] ) . '</div>';
		}
	}

	/**
	 * Renders nothing but a "coming soon" note in the Customizer preview
	 * context only — on the live front end it's simply inert until the
	 * Evolyon Toolkit form builder ships a real newsletter form.
	 */
	private function render_newsletter( $settings ) {
		if ( ! empty( $settings['heading'] ) ) {
			echo '<h6 class="evln-footer-column__heading">' . esc_html( $settings['heading'] ) . '</h6>';
		}

		if ( ! apply_filters( 'evolyon/newsletter/enabled', false ) ) {
			if ( is_customize_preview() ) {
				echo '<p class="evln-footer-column__placeholder">' . esc_html__( 'Newsletter form — requires the Evolyon Toolkit form builder.', 'evolyon' ) . '</p>';
			}
			return;
		}

		/**
		 * The Evolyon Toolkit's form builder renders the real newsletter
		 * form here once it exists.
		 */
		do_action( 'evolyon/newsletter/render' );
	}

	private function render_bottom( $bottom ) {
		echo '<div class="evln-footer-bottom">';
		echo '<div class="evln-container evln-footer-bottom__inner">';

		$copyright = str_replace(
			array( '{year}', '{site_title}' ),
			array( gmdate( 'Y' ), get_bloginfo( 'name' ) ),
			$bottom['copyright']
		);
		echo '<div class="evln-footer-bottom__copyright">' . wp_kses_post( $copyright ) . '</div>';

		if ( ! empty( $bottom['menu_location'] ) ) {
			wp_nav_menu(
				array(
					'theme_location' => $bottom['menu_location'],
					'container'      => false,
					'menu_class'     => 'evln-footer-bottom__menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
		}

		if ( ! empty( $bottom['show_social'] ) ) {
			Evolyon_Social_Profiles::instance()->render_icons( 'evln-footer-bottom__social' );
		}

		echo '</div>';
		echo '</div>';
	}
}
