<?php
/**
 * Single APOD Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

get_header( );

$title = esc_html( $post -> post_title );
$content = apply_filters( 'the_content', $post -> post_content );

$media_type = get_post_meta( $post -> ID, 'sgu_apod_local_media_type', true );
$_local_media = get_post_meta( $post -> ID, 'sgu_apod_local_media', true );

$media_html = '';

if( $media_type == 'image' ) {
    $img = '';
    if( $_local_media ) {
        $_img_id = SGU_Static::get_attachment_id( $_local_media );
        $img = esc_url( wp_get_attachment_image_url( $_img_id, 'full' ) );
    } else {
        $img = esc_url( get_post_meta( $post -> ID, 'sgu_apod_original_media', true ) );
    }
    $media_html = '<img src="' . $img . '" alt="' . $title . '" class="uk-width-1-1">';
} else {
    $media_html = '<iframe src="' . esc_url( $_local_media ) . '" class="uk-width-1-1" style="min-height:500px;"></iframe>';
}

$back_link = esc_url( get_post_type_archive_link( 'sgu_apod' ) );

echo <<<HTML
<article class="sgu-apod-single">
    <h1 class="uk-heading-divider">$title</h1>
    <div class="uk-margin-large">
        $media_html
    </div>
    <div class="uk-margin-large">
        $content
    </div>
    <div class="uk-margin-large">
        <a href="$back_link" class="uk-button uk-button-secondary">Back to APOD</a>
    </div>
</article>
HTML;

get_footer( );