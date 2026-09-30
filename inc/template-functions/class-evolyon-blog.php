<?php
/**
 * Blog helper functions: reading time, related posts, and the Customizer
 * controls for the archive layout (grid/list).
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Blog {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'customize_register', array( $this, 'register_customizer' ) );
	}

	public function register_customizer( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'evolyon_blog',
			array(
				'title' => __( 'Blog', 'evolyon' ),
				'panel' => 'evolyon',
			)
		);

		$wp_customize->add_setting(
			'evolyon_blog_layout',
			array(
				'default'           => 'grid',
				'sanitize_callback' => array( $this, 'sanitize_layout' ),
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'evolyon_blog_layout',
			array(
				'type'    => 'radio',
				'section' => 'evolyon_blog',
				'label'   => __( 'Archive layout', 'evolyon' ),
				'choices' => array(
					'grid' => __( 'Grid', 'evolyon' ),
					'list' => __( 'List', 'evolyon' ),
				),
			)
		);

		$wp_customize->add_setting(
			'evolyon_blog_show_author_box',
			array(
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'evolyon_blog_show_author_box',
			array(
				'type'    => 'checkbox',
				'section' => 'evolyon_blog',
				'label'   => __( 'Show author box on posts', 'evolyon' ),
			)
		);

		$wp_customize->add_setting(
			'evolyon_blog_show_related',
			array(
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'evolyon_blog_show_related',
			array(
				'type'    => 'checkbox',
				'section' => 'evolyon_blog',
				'label'   => __( 'Show related posts', 'evolyon' ),
			)
		);
	}

	public function sanitize_layout( $value ) {
		return in_array( $value, array( 'grid', 'list' ), true ) ? $value : 'grid';
	}

	public function get_layout() {
		return get_theme_mod( 'evolyon_blog_layout', 'grid' );
	}

	/**
	 * A simple 200-words-per-minute estimate — no external library, this is
	 * a one-line calculation.
	 */
	public function reading_time( $post_id ) {
		$content    = get_post_field( 'post_content', $post_id );
		$word_count = str_word_count( wp_strip_all_tags( $content ) );
		$minutes    = max( 1, (int) ceil( $word_count / 200 ) );

		/* translators: %d: number of minutes */
		return sprintf( _n( '%d min read', '%d min read', $minutes, 'evolyon' ), $minutes );
	}

	/**
	 * Related posts: same primary category, excluding the current post,
	 * newest first. Falls back to recent posts site-wide if the current
	 * post has no category (e.g. uncategorized).
	 */
	public function get_related( $post_id, $count = 3 ) {
		$categories = wp_get_post_categories( $post_id );

		$args = array(
			'post__not_in'   => array( $post_id ),
			'posts_per_page' => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'  => true,
		);

		if ( ! empty( $categories ) ) {
			$args['category__in'] = $categories;
		}

		return new WP_Query( $args );
	}
}

Evolyon_Blog::instance();
