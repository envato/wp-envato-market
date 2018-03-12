/* global tb_remove, JSON */
window.wp = window.wp || {};

(function( $, wp ) {
	'use strict';

	wp.envato = {};

	/**
	 * User nonce for ajax calls.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	wp.envato.ajaxNonce = window._wpUpdatesSettings.ajax_nonce;

	/**
	 * Localized strings.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	wp.envato.l10n = window._wpUpdatesSettings.l10n;

	/**
	 * Whether filesystem credentials need to be requested from the user.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	wp.envato.shouldRequestFilesystemCredentials = null;

	/**
	 * Filesystem credentials to be packaged along with the request.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	wp.envato.filesystemCredentials = {
		ftp: {
			host: null,
			username: null,
			password: null,
			connectionType: null
		},
		ssh: {
			publicKey: null,
			privateKey: null
		}
	};

	/**
	 * Flag if we're waiting for an update to complete.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	wp.envato.updateLock = false;

	/**
	 * * Flag if we've done an update successfully.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	wp.envato.updateDoneSuccessfully = false;

	/**
	 * If the user tries to update a plugin while an update is
	 * already happening, it can be placed in this queue to perform later.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	wp.envato.updateQueue = [];

	/**
	 * Store a jQuery reference to return focus to when exiting the request credentials modal.
	 *
	 * @since 1.0.0
	 *
	 * @var jQuery object
	 */
	wp.envato.$elToReturnFocusToFromCredentialsModal = null;

	/**
	 * Decrement update counts throughout the various menus.
	 *
	 * @since 3.9.0
	 *
	 * @param {string} upgradeType
	 */
	wp.envato.decrementCount = function( upgradeType ) {
		var count,
				pluginCount,
				$adminBarUpdateCount = $( '#wp-admin-bar-updates .ab-label' ),
				$dashboardNavMenuUpdateCount = $( 'a[href="update-core.php"] .update-plugins' ),
				$pluginsMenuItem = $( '#menu-plugins' );

		count = $adminBarUpdateCount.text();
		count = parseInt( count, 10 ) - 1;
		if ( count < 0 || isNaN( count ) ) {
			return;
		}
		$( '#wp-admin-bar-updates .ab-item' ).removeAttr( 'title' );
		$adminBarUpdateCount.text( count );

		$dashboardNavMenuUpdateCount.each(function( index, elem ) {
			elem.className = elem.className.replace( /count-\d+/, 'count-' + count );
		});
		$dashboardNavMenuUpdateCount.removeAttr( 'title' );
		$dashboardNavMenuUpdateCount.find( '.update-count' ).text( count );

		if ( 'plugin' === upgradeType ) {
			pluginCount = $pluginsMenuItem.find( '.plugin-count' ).eq( 0 ).text();
			pluginCount = parseInt( pluginCount, 10 ) - 1;
			if ( pluginCount < 0 || isNaN( pluginCount ) ) {
				return;
			}
			$pluginsMenuItem.find( '.plugin-count' ).text( pluginCount );
			$pluginsMenuItem.find( '.update-plugins' ).each(function( index, elem ) {
				elem.className = elem.className.replace( /count-\d+/, 'count-' + pluginCount );
			});

			if ( pluginCount > 0 ) {
				$( '.subsubsub .upgrade .count' ).text( '(' + pluginCount + ')' );
			} else {
				$( '.subsubsub .upgrade' ).remove();
			}
		}
	};

	/**
	 * Send an Ajax request to the server to update a plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} plugin
	 * @param {string} slug
	 */
	wp.envato.updatePlugin = function( plugin, slug ) {
		var data,
				$message = $( '.envato-card-' + slug ).find( '.update-now' ),
				name = $message.data( 'data-name' );

		$message.attr( 'aria-label', wp.envato.l10n.updating.replace( '%s', name ) );

		$message.addClass( 'updating-message' );
		if ( $message.html() !== wp.envato.l10n.updating ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message.text( wp.envato.l10n.updating );
		wp.a11y.speak( wp.envato.l10n.updatingMsg );

		if ( wp.envato.updateLock ) {
			wp.envato.updateQueue.push({
				type: 'update-plugin',
				data: {
					plugin: plugin,
					slug: slug
				}
			});
			return;
		}

		wp.envato.updateLock = true;

		data = {
			_ajax_nonce: wp.envato.ajaxNonce,
			plugin: plugin,
			slug: slug,
			username: wp.envato.filesystemCredentials.ftp.username,
			password: wp.envato.filesystemCredentials.ftp.password,
			hostname: wp.envato.filesystemCredentials.ftp.hostname,
			connection_type: wp.envato.filesystemCredentials.ftp.connectionType,
			public_key: wp.envato.filesystemCredentials.ssh.publicKey,
			private_key: wp.envato.filesystemCredentials.ssh.privateKey
		};

		wp.ajax.post( 'update-plugin', data )
				.done( wp.envato.updateSuccess )
				.fail( wp.envato.updateError );
	};

	/**
	 * Send an Ajax request to the server to update a theme.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} plugin
	 * @param {string} slug
	 */
	wp.envato.updateTheme = function( slug ) {
		var data,
				$message = $( '.envato-card-' + slug ).find( '.update-now' ),
				name = $message.data( 'data-name' );

		$message.attr( 'aria-label', wp.envato.l10n.updating.replace( '%s', name ) );

		$message.addClass( 'updating-message' );
		if ( $message.html() !== wp.envato.l10n.updating ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message.text( wp.envato.l10n.updating );
		wp.a11y.speak( wp.envato.l10n.updatingMsg );

		if ( wp.envato.updateLock ) {
			wp.envato.updateQueue.push({
				type: 'update-theme',
				data: {
					theme: slug
				}
			});
			return;
		}

		wp.envato.updateLock = true;

		data = {
			_ajax_nonce: wp.envato.ajaxNonce,
			theme: slug,
			slug: slug,
			username: wp.envato.filesystemCredentials.ftp.username,
			password: wp.envato.filesystemCredentials.ftp.password,
			hostname: wp.envato.filesystemCredentials.ftp.hostname,
			connection_type: wp.envato.filesystemCredentials.ftp.connectionType,
			public_key: wp.envato.filesystemCredentials.ssh.publicKey,
			private_key: wp.envato.filesystemCredentials.ssh.privateKey
		};

		wp.ajax.post( 'update-theme', data )
				.done( wp.envato.updateSuccess )
				.fail( wp.envato.updateError );
	};

	/**
	 * On a successful plugin update, update the UI with the result.
	 *
	 * @since 1.0.0
	 *
	 * @param {object} response
	 */
	wp.envato.updateSuccess = function( response ) {
		var $card, $updateColumn, $updateMessage, $updateVersion, name, version, versionText;

		$card = $( '.envato-card-' + response.slug );
		$updateColumn = $card.find( '.column-update' );
		$updateMessage = $card.find( '.update-now' );
		$updateVersion = $card.find( '.version' );

		name = $updateMessage.data( 'name' );
		version = $updateMessage.data( 'version' );
		versionText = $updateVersion.attr( 'aria-label' ).replace( '%s', version );

		$updateMessage.addClass( 'disabled' );
		$updateMessage.attr( 'aria-label', wp.envato.l10n.updatedMsg.replace( '%s', name ) );
		$updateVersion.text( versionText );

		$updateMessage.removeClass( 'updating-message' ).addClass( 'updated-message' );
		$updateMessage.text( wp.envato.l10n.updated );
		wp.a11y.speak( wp.envato.l10n.updatedMsg );
		$updateColumn.addClass( 'update-complete' ).delay( 1000 ).fadeOut();

		wp.envato.decrementCount( 'plugin' );

		wp.envato.updateDoneSuccessfully = true;

		/*
		 * The lock can be released since the update was successful,
		 * and any other updates can commence.
		 */
		wp.envato.updateLock = false;

		$( document ).trigger( 'envato-update-success', response );

		wp.envato.queueChecker();
	};

	/**
	 * On a plugin update error, update the UI appropriately.
	 *
	 * @since 1.0.0
	 *
	 * @param {object} response
	 */
	wp.envato.updateError = function( response ) {
		var $message, name;
		wp.envato.updateDoneSuccessfully = false;
		if ( response.errorCode && 'unable_to_connect_to_filesystem' === response.errorCode && wp.envato.shouldRequestFilesystemCredentials ) {
			wp.envato.credentialError( response, 'update-plugin' );
			return;
		}
		$message = $( '.envato-card-' + response.slug ).find( '.update-now' );

		name = $message.data( 'data-name' );
		$message.attr( 'aria-label', wp.envato.l10n.updateFailed.replace( '%s', name ) );

		$message.removeClass( 'updating-message' );
		$message.html( wp.envato.l10n.updateFailed.replace( '%s', typeof 'undefined' !== response.errorMessage ? response.errorMessage : response.error ) );
		wp.a11y.speak( wp.envato.l10n.updateFailed );

		/*
		 * The lock can be released since this failure was
		 * after the credentials form.
		 */
		wp.envato.updateLock = false;

		$( document ).trigger( 'envato-update-error', response );

		wp.envato.queueChecker();
	};

	/**
	 * Show an error message in the request for credentials form.
	 *
	 * @param {string} message
	 * @since 1.0.0
	 */
	wp.envato.showErrorInCredentialsForm = function( message ) {
		var $modal = $( '.notification-dialog' );

		// Remove any existing error.
		$modal.find( '.error' ).remove();

		$modal.find( 'h3' ).after( '<div class="error">' + message + '</div>' );
	};

	/**
	 * Events that need to happen when there is a credential error
	 *
	 * @since 1.0.0
	 */
	wp.envato.credentialError = function( response, type ) {
		wp.envato.updateQueue.push({
			'type': type,
			'data': {

				// Not cool that we're depending on response for this data.
				// This would feel more whole in a view all tied together.
				plugin: response.plugin,
				slug: response.slug
			}
		});
		wp.envato.showErrorInCredentialsForm( response.error );
		wp.envato.requestFilesystemCredentials();
	};

	/**
	 * If an update job has been placed in the queue, queueChecker pulls it out and runs it.
	 *
	 * @since 1.0.0
	 */
	wp.envato.queueChecker = function() {
		var job;

		if ( wp.envato.updateLock || wp.envato.updateQueue.length <= 0 ) {
			return;
		}

		job = wp.envato.updateQueue.shift();

		wp.envato.updatePlugin( job.data.plugin, job.data.slug );
	};

	/**
	 * Request the users filesystem credentials if we don't have them already.
	 *
	 * @since 1.0.0
	 */
	wp.envato.requestFilesystemCredentials = function( event ) {
		if ( false === wp.envato.updateDoneSuccessfully ) {
			wp.envato.$elToReturnFocusToFromCredentialsModal = $( event.target );

			wp.envato.updateLock = true;

			wp.envato.requestForCredentialsModalOpen();
		}
	};

	/**
	 * Keydown handler for the request for credentials modal.
	 *
	 * Close the modal when the escape key is pressed.
	 * Constrain keyboard navigation to inside the modal.
	 *
	 * @since 1.0.0
	 */
	wp.envato.keydown = function( event ) {
		if ( 27 === event.keyCode ) {
			wp.envato.requestForCredentialsModalCancel();
		} else if ( 9 === event.keyCode ) {

			// #upgrade button must always be the last focusable element in the dialog.
			if ( 'upgrade' === event.target.id && ! event.shiftKey ) {
				$( '#hostname' ).focus();
				event.preventDefault();
			} else if ( 'hostname' === event.target.id && event.shiftKey ) {
				$( '#upgrade' ).focus();
				event.preventDefault();
			}
		}
	};

	/**
	 * Open the request for credentials modal.
	 *
	 * @since 1.0.0
	 */
	wp.envato.requestForCredentialsModalOpen = function() {
		var $modal = $( '#request-filesystem-credentials-dialog' );
		$( 'body' ).addClass( 'modal-open' );
		$modal.show();

		$modal.find( 'input:enabled:first' ).focus();
		$modal.keydown( wp.envato.keydown );
	};

	/**
	 * Close the request for credentials modal.
	 *
	 * @since 1.0.0
	 */
	wp.envato.requestForCredentialsModalClose = function() {
		$( '#request-filesystem-credentials-dialog' ).hide();
		$( 'body' ).removeClass( 'modal-open' );
		wp.envato.$elToReturnFocusToFromCredentialsModal.focus();
	};

	/**
	 * The steps that need to happen when the modal is canceled out
	 *
	 * @since 1.0.0
	 */
	wp.envato.requestForCredentialsModalCancel = function() {
		var slug, $message;

		// No updateLock and no updateQueue means we already have cleared things up
		if ( false === wp.envato.updateLock && 0 === wp.envato.updateQueue.length ) {
			return;
		}

		slug = wp.envato.updateQueue[0].data.slug,

				// Remove the lock, and clear the queue
				wp.envato.updateLock = false;
		wp.envato.updateQueue = [];

		wp.envato.requestForCredentialsModalClose();
		$message = $( '.envato-card-' + slug ).find( '.update-now' );

		$message.removeClass( 'updating-message' );
		$message.html( $message.data( 'originaltext' ) );
		wp.a11y.speak( wp.envato.l10n.updateCancel );
	};
	/**
	 * Potentially add an AYS to a user attempting to leave the page
	 *
	 * If an update is on-going and a user attempts to leave the page,
	 * open an "Are you sure?" alert.
	 *
	 * @since 1.0.0
	 */

	wp.envato.beforeunload = function() {
		if ( wp.envato.updateLock ) {
			return wp.envato.l10n.beforeunload;
		}
	};

	$( document ).ready(function() {
		/*
		 * Check whether a user needs to submit filesystem credentials based on whether
		 * the form was output on the page server-side.
		 *
		 * @see {wp_print_request_filesystem_credentials_modal() in PHP}
		 */
		wp.envato.shouldRequestFilesystemCredentials = ( $( '#request-filesystem-credentials-dialog' ).length <= 0 ) ? false : true;

		// File system credentials form submit noop-er / handler.
		$( '#request-filesystem-credentials-dialog form' ).on( 'submit', function() {

			// Persist the credentials input by the user for the duration of the page load.
			wp.envato.filesystemCredentials.ftp.hostname = $( '#hostname' ).val();
			wp.envato.filesystemCredentials.ftp.username = $( '#username' ).val();
			wp.envato.filesystemCredentials.ftp.password = $( '#password' ).val();
			wp.envato.filesystemCredentials.ftp.connectionType = $( 'input[name="connection_type"]:checked' ).val();
			wp.envato.filesystemCredentials.ssh.publicKey = $( '#public_key' ).val();
			wp.envato.filesystemCredentials.ssh.privateKey = $( '#private_key' ).val();

			wp.envato.requestForCredentialsModalClose();

			// Unlock and invoke the queue.
			wp.envato.updateLock = false;
			wp.envato.queueChecker();

			return false;
		});

		// Close the request credentials modal when
		$( '#request-filesystem-credentials-dialog [data-js-action="close"], .notification-dialog-background' ).on( 'click', function() {
			wp.envato.requestForCredentialsModalCancel();
		});

		// Hide SSH fields when not selected
		$( '#request-filesystem-credentials-dialog input[name="connection_type"]' ).on( 'change', function() {
			$( this ).parents( 'form' ).find( '#private_key, #public_key' ).parents( 'label' ).toggle( ( 'ssh' === $( this ).val() ) );
		}).change();

		// Click handler for plugin updates.
		$( '.envato-card.plugin' ).on( 'click', '.update-now', function( e ) {
			var $button = $( e.target );
			e.preventDefault();

			if ( wp.envato.shouldRequestFilesystemCredentials && ! wp.envato.updateLock ) {
				wp.envato.requestFilesystemCredentials( e );
			}

			wp.envato.updatePlugin( $button.data( 'plugin' ), $button.data( 'slug' ) );
		});

		// Click handler for theme updates.
		$( '.envato-card.theme' ).on( 'click', '.update-now', function( e ) {
			var $button = $( e.target );
			e.preventDefault();

			if ( wp.envato.shouldRequestFilesystemCredentials && ! wp.envato.updateLock ) {
				wp.envato.requestFilesystemCredentials( e );
			}

			wp.envato.updateTheme( $button.data( 'slug' ) );
		});

		// @todo
		$( '#plugin_update_from_iframe' ).on( 'click', function( e ) {
			var target, data;

			target = window.parent === window ? null : window.parent,
					$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target || window.parent.location.pathname.indexOf( 'update-core.php' ) !== -1 ) {
				return;
			}

			e.preventDefault();

			data = {
				'action': 'updatePlugin',
				'slug': $( this ).data( 'slug' )
			};

			target.postMessage( JSON.stringify( data ), window.location.origin );
		});
	});

	$( window ).on( 'message', function( e ) {
		var event = e.originalEvent,
				message,
				loc = document.location,
				expectedOrigin = loc.protocol + '//' + loc.hostname;

		if ( event.origin !== expectedOrigin ) {
			return;
		}

		message = $.parseJSON( event.data );

		try {
			if ( 'undefined' === typeof message.action ) {
				return;
			}
		}
		catch ( error ) {

		}

		try {
			switch ( message.action ) {
				case 'decrementUpdateCount' :
					wp.envato.decrementCount( message.upgradeType );
					break;
				case 'updatePlugin' :
					tb_remove();
					$( '.envato-card-' + message.slug ).find( 'h4 a' ).focus();
					$( '.envato-card-' + message.slug ).find( '[data-slug="' + message.slug + '"]' ).trigger( 'click' );
					break;
				default:
			}
		}
		catch ( error ) {

		}

	});

	$( window ).on( 'beforeunload', wp.envato.beforeunload );

})( jQuery, window.wp, window.ajaxurl );
