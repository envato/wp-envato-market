/* global _envatoMarket, tb_click */

/**
 * Envato Market sripts.
 *
 * @since 1.0.0
 */
( function( $ ) {
	'use strict';
	var dialog;

	var envatoMarket = {

		cache: {},

		init: function() {
			this.cacheElements();
			this.bindEvents();
		},

		cacheElements: function() {
			this.cache = {
				$window: $( window ),
				$document: $( document )
			};
		},

		bindEvents: function() {
			var self = this;

			self.cache.$window.on( 'resize', $.proxy( self.tbPosition, self ) );

			self.cache.$document.on( 'ready', function() {
				self.addItem();
				self.removeItem();
				self.tabbedNav();
				self.addReadmore();

				$( '.envato-card' ).on( 'click', 'a.thickbox', function() {
					tb_click.call( this );
					$( '#TB_title' ).css( { 'background-color': '#23282d', 'color': '#cfcfcf' } );
					self.cache.$window.trigger( 'resize' );
					return false;
				} );
			} );
		},

		addReadmore: function() {
			$( '.envato-card .envato-card-top .column-description .description' ).each( function() {
				if ( 15 < parseInt( $.trim( $( this ).html() ).split( /[\s\.\(\),]+/ ).length, 10 ) ) {
					$( this ).addClass( 'closed' ).after( '<a href="#" class="read-more closed">&hellip;</a>' );
				}
			} );

			$( '.envato-card' ).on( 'click', 'a.read-more', function( event ) {
				event.preventDefault();
				$( this ).prev( '.description' ).toggleClass( 'closed' );
				$( this ).toggleClass( 'closed' );
			} );
		},

		addItem: function() {
			$( '.add-envato-market-item' ).on( 'click', function( event ) {
				var id = 'envato-market-dialog-form';
				event.preventDefault();

				if ( 0 === $( '#' + id ).length ) {
					$( 'body' ).append( wp.template( id ) );
				}

				dialog = $( '#' + id ).dialog( {
					autoOpen: true,
					modal: true,
					width: 350,
					buttons: {
						Save: {
							text: _envatoMarket.i18n.save,
							click: function() {
								var form = $( this ),
									request, token, id;

								form.on( 'submit', function( event ) {
									event.preventDefault();
								} );

								token = form.find( 'input[name="token"]' ).val();
								id = form.find( 'input[name="id"]' ).val();

								request = wp.ajax.post( _envatoMarket.action + '_add_item', {
									nonce: _envatoMarket.nonce,
									token: token,
									id: id
								} );

								request.done( function( response ) {
									var item = wp.template( 'envato-market-item' ),
										card = wp.template( 'envato-market-card' ),
										button = wp.template( 'envato-market-auth-check-button' );

									$( '.nav-tab-wrapper' ).find( '[data-id="' + response.type + '"]' ).removeClass( 'hidden' );

									response.item.type = response.type;
									$( '#' + response.type + 's' ).append( card( response.item ) ).removeClass( 'hidden' );

									$( '#envato-market-items' ).append( item( {
										name: response.name,
										token: response.token,
										id: response.id,
										key: response.key,
										type: response.type,
										authorized: response.authorized
									} ) );

									if ( 0 === $( '.auth-check-button' ).length ) {
										$( 'p.submit' ).append( button );
									}

									dialog.dialog( 'close' );
									envatoMarket.addReadmore();
								} );

								request.fail( function( response ) {
									var template = wp.template( 'envato-market-dialog-error' ),
										data = {
											message: ( response.message ? response.message : _envatoMarket.i18n.error )
										};

									dialog.find( '.notice' ).remove();
									dialog.find( 'form' ).prepend( template( data ) );
									dialog.find( '.notice' ).fadeIn( 'fast' );
								} );
							}
						},
						Cancel: {
							text: _envatoMarket.i18n.cancel,
							click: function() {
								dialog.dialog( 'close' );
							}
						}
					},
					close: function() {
						dialog.find( '.notice' ).remove();
						dialog.find( 'form' )[ 0 ].reset();
					}
				} );
			} );
		},

		removeItem: function() {
			$( '#envato-market-items' ).on( 'click', '.item-delete', function( event ) {
				var self = this, id = 'envato-market-dialog-remove';
				event.preventDefault();

				if ( 0 === $( '#' + id ).length ) {
					$( 'body' ).append( wp.template( id ) );
				}

				dialog = $( '#' + id ).dialog( {
					autoOpen: true,
					modal: true,
					width: 350,
					buttons: {
						Save: {
							text: _envatoMarket.i18n.remove,
							click: function() {
								var form = $( this ),
									request, id;

								form.on( 'submit', function( event ) {
									event.preventDefault();
								} );

								id = $( self ).parents( 'li' ).data( 'id' );

								request = wp.ajax.post( _envatoMarket.action + '_remove_item', {
									nonce: _envatoMarket.nonce,
									id: id
								} );

								request.done( function() {
									var item = $( '.col[data-id="' + id + '"]' ),
										type = ( item.find( '.envato-card' ).hasClass( 'theme' ) ? 'theme' : 'plugin' );

									item.remove();

									if ( 0 === $( '#' + type + 's' ).find( '.col' ).length ) {
										$( '.nav-tab-wrapper' ).find( '[data-id="' + type + '"]' ).addClass( 'hidden' );
										$( '#' + type + 's' ).addClass( 'hidden' );
									}

									$( self ).parents( 'li' ).remove();

									$( '#envato-market-items li' ).each( function( index ) {
										$( this ).find( 'input' ).each( function() {
											$( this ).attr( 'name', $( this ).attr( 'name' ).replace( /\[\d\]/g, '[' + index + ']' ) );
										} );
									} );

									if ( 0 !== $( '.auth-check-button' ).length && 0 === $( '#envato-market-items li' ).length ) {
										$( 'p.submit .auth-check-button' ).remove();
									}

									dialog.dialog( 'close' );
								} );

								request.fail( function( response ) {
									var template = wp.template( 'envato-market-dialog-error' ),
										data = {
											message: ( response.message ? response.message : _envatoMarket.i18n.error )
										};

									dialog.find( '.notice' ).remove();
									dialog.find( 'form' ).prepend( template( data ) );
									dialog.find( '.notice' ).fadeIn( 'fast' );
								} );
							}
						},
						Cancel: {
							text: _envatoMarket.i18n.cancel,
							click: function() {
								dialog.dialog( 'close' );
							}
						}
					}
				} );
			} );
		},

		tabbedNav: function() {
			var self = this,
				$wrap = $( '.about-wrap' );

			// Hide all panels
			$( 'div.panel', $wrap ).hide();

			this.cache.$window.on( 'load', function() {
				var tab = self.getParameterByName( 'tab' ),
					hashTab = window.location.hash.substr( 1 );

				if ( tab ) {
					$( '.nav-tab-wrapper a[href="#' + tab + '"]', $wrap ).click();
				} else if ( hashTab ) {
					$( '.nav-tab-wrapper a[href="#' + hashTab + '"]', $wrap ).click();
				} else {
					$( 'div.panel:not(.hidden)', $wrap ).first().show();
				}
			} );

			// Listen for the click event.
			$( '.nav-tab-wrapper a', $wrap ).on( 'click', function() {

				// Deactivate and hide all tabs & panels.
				$( '.nav-tab-wrapper a', $wrap ).removeClass( 'nav-tab-active' );
				$( 'div.panel', $wrap ).hide();

				// Activate and show the selected tab and panel.
				$( this ).addClass( 'nav-tab-active' );
				$( 'div' + $( this ).attr( 'href' ), $wrap ).show();

				return false;
			} );
		},

		getParameterByName: function( name ) {
			var regex, results;
			name = name.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
			regex = new RegExp( '[\\?&]' + name + '=([^&#]*)' );
			results = regex.exec( location.search );
			return null === results ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
		},

		tbPosition: function() {
			var $tbWindow = $( '#TB_window' ),
				$tbFrame = $( '#TB_iframeContent' ),
				windowWidth = this.cache.$window.width(),
				newHeight = this.cache.$window.height() - ( ( 792 < windowWidth ) ? 90 : 50 ),
				newWidth = ( 792 < windowWidth ) ? 772 : windowWidth - 20;

			if ( $tbWindow.size() ) {
				$tbWindow
					.width( newWidth )
					.height( newHeight )
					.css( { 'margin-left': '-' + parseInt( ( newWidth / 2 ), 10 ) + 'px' } );

				$tbFrame.width( newWidth ).height( newHeight );

				if ( 'undefined' !== typeof document.body.style.maxWidth ) {
					$tbWindow.css( {
						'top': ( 792 < windowWidth ? 30 : 10 ) + 'px',
						'margin-top': '0'
					} );
				}
			}

			return $( 'a.thickbox' ).each( function() {
				var href = $( this ).attr( 'href' );

				if ( ! href ) {
					return;
				}

				href = href.replace( /&width=[0-9]+/g, '' );
				href = href.replace( /&height=[0-9]+/g, '' );
				href = href + '&width=' + newWidth + '&height=' + newHeight;

				$( this ).attr( 'href', href );
			} );
		}

	};

	envatoMarket.init();

} )( jQuery );
