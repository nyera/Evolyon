/**
 * Customizer UI for the Header and Footer builders. Reads/writes a single
 * JSON Customizer setting per builder (evolyon_header_config /
 * evolyon_footer_config) — the PHP sanitize callbacks are the source of
 * truth for what's actually allowed to persist; this file just makes
 * editing that JSON feel like a real visual builder instead of a textarea.
 */
( function () {
	'use strict';

	function sprintf( template ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var i = 0;
		return String( template ).replace( /%[ds]/g, function () {
			return i < args.length ? args[ i++ ] : '';
		} );
	}

	function el( tag, attrs, children ) {
		var node = document.createElement( tag );
		attrs = attrs || {};
		Object.keys( attrs ).forEach( function ( key ) {
			if ( key === 'class' ) {
				node.className = attrs[ key ];
			} else if ( key === 'text' ) {
				node.textContent = attrs[ key ];
			} else if ( key === 'html' ) {
				node.innerHTML = attrs[ key ];
			} else if ( key === 'disabled' ) {
				node.disabled = !! attrs[ key ] && 'disabled' === attrs[ key ];
			} else if ( key.indexOf( 'on' ) === 0 && typeof attrs[ key ] === 'function' ) {
				node.addEventListener( key.slice( 2 ).toLowerCase(), attrs[ key ] );
			} else if ( null !== attrs[ key ] && undefined !== attrs[ key ] ) {
				node.setAttribute( key, attrs[ key ] );
			}
		} );
		( children || [] ).forEach( function ( child ) {
			if ( child ) {
				node.appendChild( child );
			}
		} );
		return node;
	}

	/* ----------------------------------------------------------------
	 * HEADER BUILDER
	 * ---------------------------------------------------------------- */

	function initHeaderBuilder( root, setting ) {
		var data = window.evlnHeaderBuilderData;
		if ( ! data ) {
			return;
		}

		var state;
		try {
			state = JSON.parse( setting.get() );
		} catch ( e ) {
			state = data.defaults;
		}
		state = Object.assign( {}, data.defaults, state );
		state.rows = Object.assign( {}, data.defaults.rows, state.rows );
		state.mobile = Object.assign( {}, data.defaults.mobile, state.mobile );
		state.mobile.secondary_row = Object.assign( {}, data.defaults.mobile.secondary_row, state.mobile.secondary_row );

		var activeTab = 'layout';

		function commit() {
			setting.set( JSON.stringify( state ) );
			render();
		}

		function render() {
			root.innerHTML = '';
			root.appendChild( renderTabs() );
			var panel = el( 'div', { class: 'evln-builder__panel' } );
			if ( 'layout' === activeTab ) {
				panel.appendChild( renderLayout() );
			} else if ( 'mobile' === activeTab ) {
				panel.appendChild( renderMobile() );
			} else if ( 'behavior' === activeTab ) {
				panel.appendChild( renderBehavior() );
			} else {
				panel.appendChild( renderVariants() );
			}
			root.appendChild( panel );
		}

		function renderTabs() {
			var tabs = el( 'div', { class: 'evln-builder__tabs' } );
			[ [ 'layout', data.i18n.layoutTab ], [ 'mobile', data.i18n.mobileTab ], [ 'behavior', data.i18n.behaviorTab ], [ 'variants', data.i18n.variantsTab ] ].forEach( function ( t ) {
				var btn = el( 'button', {
					type: 'button',
					class: 'evln-builder__tab' + ( activeTab === t[ 0 ] ? ' is-active' : '' ),
					text: t[ 1 ],
					onclick: function () {
						activeTab = t[ 0 ];
						render();
					},
				} );
				tabs.appendChild( btn );
			} );
			return tabs;
		}

		function renderLayout() {
			var wrap = el( 'div', { class: 'evln-builder__rows' } );
			Object.keys( data.rows ).forEach( function ( rowKey ) {
				wrap.appendChild( renderRow( rowKey ) );
			} );
			return wrap;
		}

		function renderRow( rowKey ) {
			var row = state.rows[ rowKey ];
			var rowEl = el( 'div', { class: 'evln-builder__row' } );

			var head = el( 'label', { class: 'evln-builder__row-head' }, [
				el( 'input', {
					type: 'checkbox',
					onchange: function ( e ) {
						row.enabled = e.target.checked;
						commit();
					},
				} ),
				el( 'span', { text: ' ' + data.rows[ rowKey ] } ),
			] );
			head.querySelector( 'input' ).checked = !! row.enabled;
			rowEl.appendChild( head );

			var zones = el( 'div', { class: 'evln-builder__zones' } );
			Object.keys( data.zones ).forEach( function ( zoneKey ) {
				zones.appendChild( renderZone( rowKey, zoneKey ) );
			} );
			rowEl.appendChild( zones );

			return rowEl;
		}

		function renderZone( rowKey, zoneKey ) {
			var items = state.rows[ rowKey ].zones[ zoneKey ];
			var zoneEl = el( 'div', { class: 'evln-builder__zone' }, [
				el( 'h4', { text: data.zones[ zoneKey ] } ),
			] );

			var list = el( 'ul', { class: 'evln-builder__chip-list' } );
			if ( ! items.length ) {
				list.appendChild( el( 'li', { class: 'evln-builder__empty', text: data.i18n.empty } ) );
			}

			list.addEventListener( 'dragover', function ( e ) {
				e.preventDefault();
			} );
			list.addEventListener( 'drop', function ( e ) {
				e.preventDefault();
				var raw = e.dataTransfer.getData( 'text/plain' );
				if ( ! raw ) {
					return;
				}
				var from = JSON.parse( raw );
				var moved = state.rows[ from.row ].zones[ from.zone ].splice( from.index, 1 )[ 0 ];
				var targetIndex = findDropIndex( list, e.clientY );
				state.rows[ rowKey ].zones[ zoneKey ].splice( targetIndex, 0, moved );
				commit();
			} );

			items.forEach( function ( item, index ) {
				list.appendChild( renderChip( rowKey, zoneKey, item, index ) );
			} );

			zoneEl.appendChild( list );
			zoneEl.appendChild( renderAddElement( rowKey, zoneKey ) );

			return zoneEl;
		}

		function findDropIndex( list, clientY ) {
			var chips = Array.prototype.slice.call( list.querySelectorAll( '.evln-el-chip' ) );
			for ( var i = 0; i < chips.length; i++ ) {
				var rect = chips[ i ].getBoundingClientRect();
				if ( clientY < rect.top + rect.height / 2 ) {
					return i;
				}
			}
			return chips.length;
		}

		function renderChip( rowKey, zoneKey, item, index ) {
			var meta = data.elements[ item.type ] || { label: item.type, schema: [] };
			var chip = el( 'li', { class: 'evln-el-chip', draggable: 'true' } );

			chip.addEventListener( 'dragstart', function ( e ) {
				e.dataTransfer.setData( 'text/plain', JSON.stringify( { row: rowKey, zone: zoneKey, index: index } ) );
				chip.classList.add( 'is-dragging' );
			} );
			chip.addEventListener( 'dragend', function () {
				chip.classList.remove( 'is-dragging' );
			} );

			var head = el( 'div', { class: 'evln-el-chip__head' }, [
				el( 'span', { class: 'evln-el-chip__handle', text: '⋮⋮' } ),
				el( 'span', { class: 'evln-el-chip__label', text: meta.label } ),
			] );

			var actions = el( 'div', { class: 'evln-el-chip__actions' } );
			var hasSettings = ( meta.schema && meta.schema.length ) || ! meta.available;
			if ( hasSettings ) {
				actions.appendChild( el( 'button', {
					type: 'button',
					class: 'evln-el-chip__gear',
					title: data.i18n.settings,
					text: '⚙',
					onclick: function () {
						settingsPanel.hidden = ! settingsPanel.hidden;
					},
				} ) );
			}
			var removeBtn = el( 'button', {
				type: 'button',
				class: 'evln-el-chip__remove',
				title: data.i18n.remove,
				text: '×',
				onclick: function () {
					state.rows[ rowKey ].zones[ zoneKey ].splice( index, 1 );
					commit();
				},
			} );
			actions.appendChild( removeBtn );
			head.appendChild( actions );
			chip.appendChild( head );

			var settingsPanel = renderChipSettings( rowKey, zoneKey, item, index, meta );
			settingsPanel.hidden = true;
			chip.appendChild( settingsPanel );

			return chip;
		}

		function renderChipSettings( rowKey, zoneKey, item, index, meta ) {
			var panel = el( 'div', { class: 'evln-el-chip__settings' } );

			( meta.schema || [] ).forEach( function ( field ) {
				panel.appendChild( schemaField( field, item.settings, function () {
					setting.set( JSON.stringify( state ) );
				} ) );
			} );

			if ( ! meta.available && meta.unavailable ) {
				panel.appendChild( el( 'p', { class: 'evln-el-chip__note', text: data.i18n.unavailableNote + ' ' + meta.unavailable } ) );
			}

			return panel;
		}

		function renderAddElement( rowKey, zoneKey ) {
			var wrap = el( 'div', { class: 'evln-builder__add' } );
			var select = el( 'select', {} );
			Object.keys( data.elements ).forEach( function ( id ) {
				var meta = data.elements[ id ];
				var label = meta.label + ( meta.available ? '' : ' (' + meta.unavailable + ')' );
				select.appendChild( el( 'option', { value: id, text: label } ) );
			} );
			var addBtn = el( 'button', {
				type: 'button',
				class: 'button',
				text: '+ ' + data.i18n.addElement,
				onclick: function () {
					state.rows[ rowKey ].zones[ zoneKey ].push( {
						type: select.value,
						settings: Object.assign( {}, data.elements[ select.value ].defaults ),
					} );
					commit();
				},
			} );
			wrap.appendChild( select );
			wrap.appendChild( addBtn );
			return wrap;
		}

		function renderMobile() {
			var wrap = el( 'div', { class: 'evln-builder__mobile' } );
			var mobile = state.mobile;
			var cap = data.mobileModeCaps[ mobile.mode ] || 3;

			wrap.appendChild( selectField( 'Mobile header mode', mobile.mode, { compact: 'Compact', standard: 'Standard', expanded: 'Expanded' }, function ( v ) {
				mobile.mode = v;
				var newCap = data.mobileModeCaps[ v ] || 3;
				if ( mobile.elements.length > newCap ) {
					mobile.elements = mobile.elements.slice( 0, newCap );
				}
				if ( 'expanded' !== v ) {
					mobile.secondary_row.enabled = false;
				}
				commit();
			} ) );
			wrap.appendChild( el( 'p', { class: 'evln-builder__hint', text: data.i18n.mobileModeHint } ) );

			wrap.appendChild( selectField( 'Logo position', mobile.alignment, { 'logo-center': 'Centered', 'logo-start': 'Next to menu button' }, function ( v ) { mobile.alignment = v; commit(); } ) );
			wrap.appendChild( selectField( 'Mobile menu style', mobile.menu_style, { offcanvas: 'Off-canvas drawer', fullscreen: 'Full-screen', dropdown: 'Inline dropdown' }, function ( v ) { mobile.menu_style = v; commit(); } ) );

			wrap.appendChild( el( 'h4', { text: data.i18n.mobileElements } ) );
			wrap.appendChild( el( 'p', { class: 'evln-builder__hint', text: sprintf( data.i18n.mobileElementCap, cap ) } ) );
			wrap.appendChild( renderMobileElementPicker( mobile.elements, cap, function () {
				setting.set( JSON.stringify( state ) );
				render();
			} ) );

			if ( 'expanded' === mobile.mode ) {
				wrap.appendChild( el( 'h4', { text: data.i18n.secondaryRow } ) );
				wrap.appendChild( checkboxField( 'Enable secondary row', !! mobile.secondary_row.enabled, function ( v ) { mobile.secondary_row.enabled = v; commit(); } ) );
				if ( mobile.secondary_row.enabled ) {
					wrap.appendChild( renderMobileElementPicker( mobile.secondary_row.elements, 2, function () {
						setting.set( JSON.stringify( state ) );
						render();
					} ) );
				}
			}

			wrap.appendChild( el( 'h4', { text: 'Sizing & style' } ) );
			wrap.appendChild( numberField( 'Header height (px)', mobile.height, function ( v ) { mobile.height = v; commit(); } ) );
			wrap.appendChild( numberField( 'Horizontal padding (px)', mobile.padding_inline, function ( v ) { mobile.padding_inline = v; commit(); } ) );
			wrap.appendChild( numberField( 'Icon size (px)', mobile.icon_size, function ( v ) { mobile.icon_size = v; commit(); } ) );
			wrap.appendChild( numberField( 'Logo max width (px)', mobile.logo_max_width, function ( v ) { mobile.logo_max_width = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Sticky on mobile', !! mobile.sticky, function ( v ) { mobile.sticky = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Shadow', !! mobile.shadow, function ( v ) { mobile.shadow = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Bottom border', !! mobile.border, function ( v ) { mobile.border = v; commit(); } ) );
			wrap.appendChild( textField( 'Background color (hex, blank = inherit)', mobile.bg || '', function ( v ) { mobile.bg = v; setting.set( JSON.stringify( state ) ); } ) );
			wrap.appendChild( textField( 'Text color (hex, blank = inherit)', mobile.text_color || '', function ( v ) { mobile.text_color = v; setting.set( JSON.stringify( state ) ); } ) );

			return wrap;
		}

		function renderMobileElementPicker( list, cap, onChange ) {
			var wrap = el( 'div', { class: 'evln-builder__mobile-picker' } );

			Object.keys( data.mobileElements ).forEach( function ( type ) {
				var meta = data.mobileElements[ type ];
				var index = list.indexOf( type );
				var checked = index !== -1;
				var atCap = ! checked && list.length >= cap;

				var row = el( 'div', { class: 'evln-mobile-picker-row' } );
				var checkbox = el( 'input', {
					type: 'checkbox',
					onchange: function ( e ) {
						if ( e.target.checked ) {
							list.push( type );
						} else {
							var i = list.indexOf( type );
							if ( i !== -1 ) {
								list.splice( i, 1 );
							}
						}
						onChange();
					},
				} );
				checkbox.checked = checked;
				checkbox.disabled = atCap;
				row.appendChild( checkbox );
				row.appendChild( el( 'span', { class: 'evln-mobile-picker-row__label', text: meta.label + ( meta.available ? '' : ' (' + meta.unavailable + ')' ) } ) );

				if ( checked ) {
					row.appendChild( el( 'button', {
						type: 'button',
						class: 'evln-mobile-picker-row__move',
						title: data.i18n.moveUp,
						text: '↑',
						disabled: 0 === index ? 'disabled' : null,
						onclick: function () {
							if ( index > 0 ) {
								list.splice( index - 1, 0, list.splice( index, 1 )[ 0 ] );
								onChange();
							}
						},
					} ) );
					row.appendChild( el( 'button', {
						type: 'button',
						class: 'evln-mobile-picker-row__move',
						title: data.i18n.moveDown,
						text: '↓',
						onclick: function () {
							if ( index < list.length - 1 && index !== -1 ) {
								list.splice( index + 1, 0, list.splice( index, 1 )[ 0 ] );
								onChange();
							}
						},
					} ) );
				}

				wrap.appendChild( row );
			} );

			return wrap;
		}

		function renderBehavior() {
			var wrap = el( 'div', { class: 'evln-builder__behavior' } );

			wrap.appendChild( checkboxField( 'Sticky header', !! state.sticky, function ( v ) { state.sticky = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Shrink when stuck', !! state.sticky_shrink, function ( v ) { state.sticky_shrink = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Hide on scroll down', !! state.hide_on_scroll, function ( v ) { state.hide_on_scroll = v; commit(); } ) );

			wrap.appendChild( selectField( 'Transparent header', state.transparent, { off: 'Disabled', home: 'Home page only' }, function ( v ) { state.transparent = v; commit(); } ) );
			wrap.appendChild( numberField( 'Scroll threshold (px)', state.scroll_threshold, function ( v ) { state.scroll_threshold = v; commit(); } ) );
			wrap.appendChild( selectField( 'Mobile menu style', state.mobile_style, { offcanvas: 'Off-canvas panel', dropdown: 'Inline dropdown' }, function ( v ) { state.mobile_style = v; commit(); } ) );
			wrap.appendChild( numberField( 'Mobile breakpoint (px)', state.mobile_breakpoint, function ( v ) { state.mobile_breakpoint = v; commit(); } ) );

			return wrap;
		}

		function renderVariants() {
			var wrap = el( 'div', { class: 'evln-builder__variants' } );
			Object.keys( data.variants ).forEach( function ( id ) {
				var variant = data.variants[ id ];
				var card = el( 'div', { class: 'evln-builder__variant-card' }, [
					el( 'h4', { text: variant.label } ),
					el( 'button', {
						type: 'button',
						class: 'button',
						text: data.i18n.applyVariant,
						onclick: function () {
							if ( window.confirm( data.i18n.confirmVariant ) ) {
								state.rows = JSON.parse( JSON.stringify( variant.rows ) );
								state.variant = id;
								activeTab = 'layout';
								commit();
							}
						},
					} ),
				] );
				wrap.appendChild( card );
			} );
			return wrap;
		}

		render();
	}

	/* ----------------------------------------------------------------
	 * FOOTER BUILDER
	 * ---------------------------------------------------------------- */

	function initFooterBuilder( root, setting ) {
		var data = window.evlnFooterBuilderData;
		if ( ! data ) {
			return;
		}

		var state;
		try {
			state = JSON.parse( setting.get() );
		} catch ( e ) {
			state = data.defaults;
		}
		state = Object.assign( {}, data.defaults, state );
		state.bottom = Object.assign( {}, data.defaults.bottom, state.bottom );

		var activeTab = 'columns';

		function commit() {
			setting.set( JSON.stringify( state ) );
			render();
		}

		function render() {
			root.innerHTML = '';
			var tabs = el( 'div', { class: 'evln-builder__tabs' } );
			[ [ 'columns', data.i18n.columnsTab ], [ 'bottom', data.i18n.bottomBarTab ] ].forEach( function ( t ) {
				tabs.appendChild( el( 'button', {
					type: 'button',
					class: 'evln-builder__tab' + ( activeTab === t[ 0 ] ? ' is-active' : '' ),
					text: t[ 1 ],
					onclick: function () {
						activeTab = t[ 0 ];
						render();
					},
				} ) );
			} );
			root.appendChild( tabs );

			var panel = el( 'div', { class: 'evln-builder__panel' } );
			panel.appendChild( 'columns' === activeTab ? renderColumns() : renderBottom() );
			root.appendChild( panel );
		}

		function renderColumns() {
			var wrap = el( 'div', {} );
			wrap.appendChild( numberField( 'Columns (1–4)', state.columns, function ( v ) {
				v = Math.max( 1, Math.min( 4, v ) );
				var items = state.items.slice( 0, v );
				while ( items.length < v ) {
					items.push( { type: 'custom_html', settings: {} } );
				}
				state.columns = v;
				state.items = items;
				commit();
			} ) );

			var cols = el( 'div', { class: 'evln-builder__zones' } );
			state.items.forEach( function ( item, index ) {
				cols.appendChild( renderColumnCard( item, index ) );
			} );
			wrap.appendChild( cols );

			return wrap;
		}

		function renderColumnCard( item, index ) {
			var card = el( 'div', { class: 'evln-builder__zone' }, [
				el( 'h4', { text: 'Column ' + ( index + 1 ) } ),
			] );

			card.appendChild( selectField( 'Content type', item.type, data.columnTypes, function ( v ) {
				item.type = v;
				item.settings = {};
				commit();
			} ) );

			if ( 'nav' === item.type || 'custom_html' === item.type || 'social' === item.type ) {
				card.appendChild( textField( 'Heading', item.settings.heading || '', function ( v ) {
					item.settings.heading = v;
					setting.set( JSON.stringify( state ) );
				} ) );
			}

			if ( 'nav' === item.type ) {
				card.appendChild( selectField( 'Menu', item.settings.menu_location || Object.keys( data.menuLocations )[ 0 ], data.menuLocations, function ( v ) {
					item.settings.menu_location = v;
					setting.set( JSON.stringify( state ) );
				} ) );
			}

			if ( 'custom_html' === item.type ) {
				card.appendChild( textareaField( 'Content', item.settings.content || '', function ( v ) {
					item.settings.content = v;
					setting.set( JSON.stringify( state ) );
				} ) );
			}

			return card;
		}

		function renderBottom() {
			var wrap = el( 'div', {} );
			wrap.appendChild( checkboxField( 'Show bottom bar', !! state.bottom.enabled, function ( v ) { state.bottom.enabled = v; commit(); } ) );
			wrap.appendChild( textareaField( 'Copyright text (use {year} and {site_title})', state.bottom.copyright, function ( v ) {
				state.bottom.copyright = v;
				setting.set( JSON.stringify( state ) );
			} ) );
			var menuChoices = Object.assign( { '': '— None —' }, data.menuLocations, { 'footer-legal': 'Footer Bottom Bar' } );
			wrap.appendChild( selectField( 'Legal menu', state.bottom.menu_location, menuChoices, function ( v ) { state.bottom.menu_location = v; commit(); } ) );
			wrap.appendChild( checkboxField( 'Show social icons', !! state.bottom.show_social, function ( v ) { state.bottom.show_social = v; commit(); } ) );
			return wrap;
		}

		render();
	}

	/* ----------------------------------------------------------------
	 * Shared field builders
	 * ---------------------------------------------------------------- */

	function checkboxField( label, checked, onChange ) {
		var input = el( 'input', {
			type: 'checkbox',
			onchange: function ( e ) {
				onChange( e.target.checked );
			},
		} );
		input.checked = checked;
		return el( 'label', { class: 'evln-field evln-field--checkbox' }, [ input, el( 'span', { text: ' ' + label } ) ] );
	}

	function textField( label, value, onChange, type ) {
		var input = el( 'input', {
			type: type || 'text',
			value: value,
			oninput: function ( e ) {
				onChange( e.target.value );
			},
		} );
		return el( 'label', { class: 'evln-field' }, [ el( 'span', { text: label } ), input ] );
	}

	function textareaField( label, value, onChange ) {
		var input = el( 'textarea', {
			rows: '3',
			oninput: function ( e ) {
				onChange( e.target.value );
			},
		} );
		input.value = value;
		return el( 'label', { class: 'evln-field' }, [ el( 'span', { text: label } ), input ] );
	}

	function numberField( label, value, onChange ) {
		var input = el( 'input', {
			type: 'number',
			value: value,
			onchange: function ( e ) {
				onChange( parseInt( e.target.value, 10 ) || 0 );
			},
		} );
		return el( 'label', { class: 'evln-field' }, [ el( 'span', { text: label } ), input ] );
	}

	function selectField( label, value, choices, onChange ) {
		var select = el( 'select', {
			onchange: function ( e ) {
				onChange( e.target.value );
			},
		} );
		Object.keys( choices ).forEach( function ( key ) {
			select.appendChild( el( 'option', { value: key, text: choices[ key ] } ) );
		} );
		select.value = value;
		return el( 'label', { class: 'evln-field' }, [ el( 'span', { text: label } ), select ] );
	}

	function schemaField( field, settingsObj, onChange ) {
		var value = settingsObj[ field.key ];
		if ( 'select' === field.type ) {
			return selectField( field.label, value, field.options, function ( v ) {
				settingsObj[ field.key ] = v;
				onChange();
			} );
		}
		return textField( field.label, value || '', function ( v ) {
			settingsObj[ field.key ] = v;
			onChange();
		}, 'url' === field.type ? 'url' : 'text' );
	}

	/* ----------------------------------------------------------------
	 * Boot: hook every .evln-builder mount point once the Customizer is ready.
	 * ---------------------------------------------------------------- */

	wp.customize.bind( 'ready', function () {
		document.querySelectorAll( '.evln-builder' ).forEach( function ( root ) {
			var settingId = root.getAttribute( 'data-customize-setting-link' );
			var kind = root.getAttribute( 'data-builder' );
			var setting = wp.customize( settingId );
			if ( ! setting ) {
				return;
			}
			if ( 'header' === kind ) {
				initHeaderBuilder( root, setting );
			} else if ( 'footer' === kind ) {
				initFooterBuilder( root, setting );
			}
		} );
	} );
} )();
