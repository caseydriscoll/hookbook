var finished = true;
var last, current = 0;

jQuery( document ).ready( function() {

    jQuery( '#generate-posts' ).on( 'click', function(e) {

        jQuery( '#generate-progress' ).show();

        last = jQuery( '.hook-list li' ).length;

        generatePost( current, jQuery( '.hook-list li:first' ).data( 'hook' ) );

    } );


    jQuery( '#actions-only' ).on( 'click', function(e) {

        if ( jQuery( '.hook-list' ).hasClass( 'actions-only' ) )
            jQuery( '.hook-list' ).removeClass( 'actions-only' );
        else
            jQuery( '.hook-list' ).removeClass( 'filters-only' ).addClass( 'actions-only' );

    } );

    jQuery( '#filters-only' ).on( 'click', function(e) {
        if ( jQuery( '.hook-list' ).hasClass( 'filters-only' ) )
            jQuery( '.hook-list' ).removeClass( 'filters-only' );
        else
            jQuery( '.hook-list' ).removeClass( 'actions-only' ).addClass( 'filters-only' );
    } );

} );


function generatePost( i, hook ) {

    jQuery.post(
        '/wp-admin/admin-ajax.php',
        {
            'action' : 'generate_hook_post',
            'hook'   : hook,
            'i'      : i
        },
        function( response ) {
            data = response.data;

            jQuery( jQuery( '.hook-list li' ).get( data.i ) ).addClass( 'complete' );

            if ( current == last ) {
                jQuery( '#generate-progress' ).html( 'Complete!' ).addClass( 'complete' );
                return;
            }

            jQuery( '#generate-progress span.total' ).text( data.i + '/' + last );

            console.log( current, last, response );

            generatePost( ++current, jQuery( jQuery( '.hook-list li' ).get( data.i ) ).data( 'hook' ) )
        }
    );

}