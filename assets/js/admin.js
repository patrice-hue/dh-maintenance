/* global wp, jQuery */
( function ( $ ) {
    'use strict';

    var mediaFrame;

    // ── Media uploader ──────────────────────────────────────────────────
    $( '#dh-upload-logo' ).on( 'click', function ( e ) {
        e.preventDefault();

        if ( mediaFrame ) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media( {
            title:    'Select or Upload Logo',
            button:   { text: 'Use this image' },
            multiple: false,
            library:  { type: 'image' },
        } );

        mediaFrame.on( 'select', function () {
            var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
            $( '#dh-logo-url' ).val( attachment.url );
            $( '#dh-logo-preview-img' ).attr( 'src', attachment.url ).show();
            $( '#dh-remove-logo' ).show();
        } );

        mediaFrame.open();
    } );

    // ── Remove logo ─────────────────────────────────────────────────────
    $( document ).on( 'click', '#dh-remove-logo', function ( e ) {
        e.preventDefault();
        $( '#dh-logo-url' ).val( '' );
        $( '#dh-logo-preview-img' ).attr( 'src', '' ).hide();
        $( this ).hide();
    } );

    // ── Live preview refresh ─────────────────────────────────────────────
    // Reload the preview iframe whenever colour pickers change.
    $( '#dh-bg-color, #dh-text-color' ).on( 'change', debounce( refreshPreview, 400 ) );

    function refreshPreview() {
        var frame = document.getElementById( 'dh-preview-frame' );
        if ( frame ) {
            frame.contentWindow.location.reload();
        }
    }

    function debounce( fn, delay ) {
        var timer;
        return function () {
            clearTimeout( timer );
            timer = setTimeout( fn, delay );
        };
    }

} )( jQuery );
