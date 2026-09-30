<?php
/**
 * Builds a plain nested tree for the 'evolyon-primary' menu location, used
 * by the mobile drill-down navigation panels. WordPress's Walker API is
 * built around inline `<ul>`-inside-`<li>` nesting, which is exactly what a
 * drill-down panel UI can't use — each nesting level needs to become its
 * own sibling panel, not a nested list — so this builds a simple array tree
 * instead of extending Walker_Nav_Menu.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Mobile_Nav_Tree {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return array[] Each node: array( 'id', 'title', 'url', 'target', 'children' => [...] ).
	 */
	public function get_tree() {
		$locations = get_nav_menu_locations();
		if ( empty( $locations['evolyon-primary'] ) ) {
			return $this->fallback_tree();
		}

		$menu  = wp_get_nav_menu_object( $locations['evolyon-primary'] );
		$items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : false;

		if ( ! $items ) {
			return $this->fallback_tree();
		}

		$by_parent = array();
		foreach ( $items as $item ) {
			$by_parent[ (int) $item->menu_item_parent ][] = $item;
		}

		return $this->build_level( $by_parent, 0 );
	}

	private function build_level( $by_parent, $parent_id ) {
		if ( empty( $by_parent[ $parent_id ] ) ) {
			return array();
		}

		$nodes = array();
		foreach ( $by_parent[ $parent_id ] as $item ) {
			$nodes[] = array(
				'id'       => (int) $item->ID,
				'title'    => $item->title,
				'url'      => $item->url,
				'target'   => $item->target,
				'children' => $this->build_level( $by_parent, (int) $item->ID ),
			);
		}
		return $nodes;
	}

	/**
	 * Shown when no menu is assigned to the primary location yet, so the
	 * mobile drawer never looks broken during setup — a flat list of
	 * top-level published pages, no fabricated nesting.
	 */
	private function fallback_tree() {
		$pages = get_pages(
			array(
				'parent'   => 0,
				'sort_column' => 'menu_order',
			)
		);

		$nodes = array();
		foreach ( $pages as $page ) {
			$nodes[] = array(
				'id'       => (int) $page->ID,
				'title'    => get_the_title( $page ),
				'url'      => get_permalink( $page ),
				'target'   => '',
				'children' => array(),
			);
		}
		return $nodes;
	}
}
