/**
 * Post settings
 */
( function( window ) {
	jQuery( function( $ ) {
		var $font_list = $( '#arps_font' ),
			$font_license = $( '#utc-time' ).find( 'code' );

		// selected font changed
		$font_list.on( 'arps.fontChange change', function() {
			// clear license
			$font_license.html( '-' );

			// check font
			if ( arps.fonts.hasOwnProperty( this.value ) ) {
				// show license
				$font_license.html( arps.fonts[ this.value ].license );
			}
		} ).trigger( 'arps.fontChange' );
	} );
} )( window );