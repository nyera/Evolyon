/**
 * Mobile navigation: opens/closes whichever drawer variant is configured
 * (offcanvas / fullscreen / dropdown) and drives the drill-down panel
 * system inside it — a real "go into a submenu, then come back" mobile nav
 * experience instead of the desktop menu dumped in vertically. Kept in its
 * own file, separate from header-frontend.js's sticky/search behavior,
 * because this is meaningfully more complex (focus trap, scroll lock,
 * panel stack) and is the part of the mobile header redesign most worth
 * being able to reason about in isolation.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	document.querySelectorAll( '[data-mnav-open]' ).forEach( function ( toggle ) {
		var targetId = toggle.getAttribute( 'aria-controls' );
		var drawer = targetId ? document.getElementById( targetId ) : null;
		if ( ! drawer ) {
			return;
		}

		var style = toggle.getAttribute( 'data-mnav-style' );
		var locksScroll = 'dropdown' !== style;
		var panelStack = [ 'root' ];

		function panels() {
			return drawer.querySelectorAll( '.evln-mnav__panel' );
		}

		function showPanel( id ) {
			panels().forEach( function ( panel ) {
				panel.hidden = panel.getAttribute( 'data-panel-id' ) !== id;
			} );
			var active = drawer.querySelector( '.evln-mnav__panel[data-panel-id="' + id + '"]' );
			if ( active ) {
				var focusTarget = active.querySelector( '.evln-mnav__back' ) || active.querySelector( '.evln-mnav__link' );
				if ( focusTarget ) {
					focusTarget.focus();
				}
			}
		}

		function open() {
			toggle.setAttribute( 'aria-expanded', 'true' );
			drawer.hidden = false;
			requestAnimationFrame( function () {
				drawer.classList.add( 'is-open' );
			} );
			if ( locksScroll ) {
				document.body.classList.add( 'evln-scroll-lock' );
			}
			document.addEventListener( 'keydown', onKeydown );
			var closeBtn = drawer.querySelector( '.evln-mnav-drawer__close' );
			var panelEl = drawer.querySelector( '.evln-mnav-drawer__panel' );
			( closeBtn || panelEl || drawer ).focus();
		}

		function close() {
			toggle.setAttribute( 'aria-expanded', 'false' );
			drawer.classList.remove( 'is-open' );
			if ( locksScroll ) {
				document.body.classList.remove( 'evln-scroll-lock' );
			}
			document.removeEventListener( 'keydown', onKeydown );
			setTimeout( function () {
				drawer.hidden = true;
				panelStack = [ 'root' ];
				showPanel( 'root' );
			}, 300 );
			toggle.focus();
		}

		function onKeydown( event ) {
			if ( 'Escape' === event.key ) {
				close();
				return;
			}
			if ( 'Tab' !== event.key ) {
				return;
			}
			var focusable = Array.prototype.filter.call(
				drawer.querySelectorAll( FOCUSABLE ),
				function ( el ) {
					return el.offsetParent !== null;
				}
			);
			if ( ! focusable.length ) {
				return;
			}
			var first = focusable[ 0 ];
			var last = focusable[ focusable.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}

		toggle.addEventListener( 'click', function () {
			if ( toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
				close();
			} else {
				open();
			}
		} );

		drawer.querySelectorAll( '[data-mnav-close]' ).forEach( function ( el ) {
			el.addEventListener( 'click', close );
		} );

		drawer.addEventListener( 'click', function ( event ) {
			var openBtn = event.target.closest( '[data-mnav-open-panel]' );
			if ( openBtn ) {
				var id = openBtn.getAttribute( 'data-mnav-open-panel' );
				panelStack.push( id );
				showPanel( id );
				return;
			}

			if ( event.target.closest( '[data-mnav-back]' ) ) {
				panelStack.pop();
				showPanel( panelStack[ panelStack.length - 1 ] );
			}
		} );
	} );
} )();
