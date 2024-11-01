/**
 * JavaScript code delegated to the backend functionality of the plugin.
 *
 * @package SimpleForm Akismet
 * @subpackage SimpleForm Akismet/admin
 */

(function( $ ) {
	'use strict';

	$( window ).on(
		'load',
		function() {

			$( '#akismet' ).on(
				'click',
				function() {
					if ( $( this ).prop( 'checked' ) == true ) {
						$( '.trakismet' ).removeClass( 'unseen' );
						$( '#tdakismet' ).removeClass( 'last' );
						if ( $( '#blocked-message' ).prop( 'checked' ) == true ) {
							$( '.trspammark' ).addClass( 'unseen' );
							$( '#tdakismetaction' ).addClass( 'last' );
						} else {
							$( '.trspammark' ).removeClass( 'unseen' );
							$( '#tdakismetaction' ).removeClass( 'last' );
						}
					} else {
						$( '.trakismet' ).addClass( 'unseen' );
						$( '#tdakismet' ).addClass( 'last' );
					}
				}
			);

			$( '#blocked-message' ).on(
				'click',
				function() {
					$( '#akismet_action_notes' ).addClass( 'invisible' );
					if ( $( this ).prop( 'checked' ) == true ) {
						$( '.trspammark' ).addClass( 'unseen' );
						$( '#tdakismetaction' ).addClass( 'last' );
					} else {
						$( '.trspammark' ).removeClass( 'unseen' );
						$( '#tdakismetaction' ).removeClass( 'last' );
					}
				}
			);

			$( '#flagged-message' ).on(
				'click',
				function() {
					$( '#akismet_action_notes' ).removeClass( 'invisible' );
					if ( $( this ).prop( 'checked' ) == true ) {
						$( '.trspammark' ).removeClass( 'unseen' );
						$( '#tdakismetaction' ).removeClass( 'last' );
					} else {
						$( '.trspammark' ).addClass( 'unseen' );
						$( '#tdakismetaction' ).addClass( 'last' );
					}
				}
			);

		}
	);

})( jQuery );
