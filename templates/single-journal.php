<?php
/**
 * Single Photo Journal Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

get_header( );

$title = esc_html( $post -> post_title );
$content = apply_filters( 'the_content', $post -> post_content );

$img = ( function( ) use( $post ) {
    $_local_media = get_post_meta( $post -> ID, 'sgu_journal_local_image', true );
    if( $_local_media ) {
        $_img_id = SGU_Static::get_attachment_id( $_local_media );
        return esc_url( wp_get_attachment_image_url( $_img_id, 'full' ) );
    } else {
        return esc_url( get_post_meta( $post -> ID, 'sgu_journal_orignal_image', true ) );
    }
} )( );

$back_link = esc_url( home_url( '/astronomy-information/nasa-photo-journal/' ) );

echo <<<HTML
<article class="sgu-journal-single">
    <h1 class="uk-heading-divider">$title</h1>
    <div class="uk-margin-large">
        <img src="$img" alt="$title" class="uk-width-1-1">
    </div>
    <div class="uk-margin-large">
        $content
    </div>
    <div class="uk-margin-large">
        <a href="$back_link" class="uk-button uk-button-secondary">Back to Photo Journals</a>
    </div>
</article>
HTML;

get_footer( );
