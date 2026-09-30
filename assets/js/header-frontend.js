/**
 * Vanilla-JS header behavior shared by desktop and mobile: sticky/shrink
 * state past the scroll threshold, hide-on-scroll-down, and the search
 * toggle. Mobile menu (toggle + drawer + drill-down panels) lives in its
 * own file, mobile-nav.js — see that file's docblock for why.
 */
( function () {
	'use strict';

	var header = document.getElementById( 'evln-header' );
	if ( ! header ) {
		return;
	}

	var threshold = parseInt( header.getAttribute( 'data-scroll-threshold' ), 10 ) || 80;
	var lastScrollY = window.scrollY;
	var ticking = false;

	function onScroll() {
		var y = window.scrollY;

		if ( y > threshold ) {
			header.classList.add( 'is-stuck' );
		} else {
			header.classList.remove( 'is-stuck' );
		}

		if ( header.classList.contains( 'evln-header--hide-on-scroll' ) ) {
			if ( y > lastScrollY && y > threshold ) {
				header.classList.add( 'is-hidden' );
			} else {
				header.classList.remove( 'is-hidden' );
			}
		}

		lastScrollY = y;
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( onScroll );
				ticking = true;
			}
		},
		{ passive: true }
	);

	onScroll();

	/*
	 * Search modal. The Search element can render more than once per page
	 * (desktop row + mobile row both exist in the DOM at once), so this
	 * binds every instance rather than assuming a single button/modal pair.
	 * A full-screen modal (not the old inline dropdown panel) — closing is
	 * backdrop click, the × button, or Escape; a "popular search" pill
	 * fills the field and submits immediately rather than requiring a
	 * second click, since there's nothing else useful to do with a
	 * pre-chosen term.
	 */
	document.querySelectorAll( '.evln-search-toggle' ).forEach( function ( wrapper ) {
		var btn = wrapper.querySelector( '.evln-search-toggle__btn' );
		var modal = wrapper.querySelector( '.evln-search-modal' );
		if ( ! btn || ! modal ) {
			return;
		}

		var input = modal.querySelector( 'input[type="search"]' );
		var form = modal.querySelector( 'form' );

		function open() {
			modal.hidden = false;
			btn.setAttribute( 'aria-expanded', 'true' );
			if ( input ) {
				input.focus();
			}
		}

		function close() {
			modal.hidden = true;
			btn.setAttribute( 'aria-expanded', 'false' );
		}

		btn.addEventListener( 'click', function () {
			if ( modal.hidden ) {
				open();
			} else {
				close();
			}
		} );

		modal.querySelectorAll( '[data-evln-search-close]' ).forEach( function ( closer ) {
			closer.addEventListener( 'click', close );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! modal.hidden ) {
				close();
				btn.focus();
			}
		} );

		modal.querySelectorAll( '[data-evln-search-term]' ).forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				if ( input ) {
					input.value = pill.getAttribute( 'data-evln-search-term' );
				}
				if ( form ) {
					form.submit();
				}
			} );
		} );
	} );

	/*
	 * Dark mode toggle. The header.css icon-swap and the token CSS itself
	 * both key off the html element's data-theme attribute / the OS
	 * prefers-color-scheme media query — this just flips that same
	 * attribute and remembers the choice, it doesn't invent a second
	 * theming mechanism.
	 */
	document.querySelectorAll( '[data-evln-theme-toggle]' ).forEach( function ( btn ) {
		function isCurrentlyDark() {
			var explicit = document.documentElement.getAttribute( 'data-theme' );
			if ( 'dark' === explicit ) {
				return true;
			}
			if ( 'light' === explicit ) {
				return false;
			}
			return window.matchMedia( '(prefers-color-scheme: dark)' ).matches;
		}

		btn.setAttribute( 'aria-pressed', isCurrentlyDark() ? 'true' : 'false' );

		btn.addEventListener( 'click', function () {
			var next = isCurrentlyDark() ? 'light' : 'dark';
			document.documentElement.setAttribute( 'data-theme', next );
			btn.setAttribute( 'aria-pressed', 'dark' === next ? 'true' : 'false' );
			try {
				localStorage.setItem( 'evln-theme', next );
			} catch ( e ) {}
		} );
	} );

	/*
	 * Palette switcher — sets the same data-palette attribute
	 * Evolyon_Token_Output::get_all_palettes_css() scopes its rules under.
	 */
	/*
	 * Direction toggle. Flips the native `dir` attribute — no dir-specific
	 * CSS needed here since the theme's layout is already built entirely
	 * on logical properties (see DEVELOPER.md), the same mechanism that
	 * already mirrors the layout for an actual RTL site language.
	 */
	document.querySelectorAll( '[data-evln-direction-toggle]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var next = 'rtl' === document.documentElement.dir ? 'ltr' : 'rtl';
			document.documentElement.dir = next;
			btn.setAttribute( 'aria-pressed', 'rtl' === next ? 'true' : 'false' );
			try {
				localStorage.setItem( 'evln-direction', next );
			} catch ( e ) {}
		} );
	} );

	document.querySelectorAll( '[data-evln-palette-select]' ).forEach( function ( select ) {
		// The server always renders `selected` for the SITE's own active
		// palette — sync the dropdown to a visitor's restored choice
		// instead, if one was applied by the early inline script.
		var restored = document.documentElement.getAttribute( 'data-palette' );
		if ( restored ) {
			select.value = restored;
		}

		select.addEventListener( 'change', function () {
			document.documentElement.setAttribute( 'data-palette', select.value );
			try {
				localStorage.setItem( 'evln-palette', select.value );
			} catch ( e ) {}
		} );
	} );
} )();
