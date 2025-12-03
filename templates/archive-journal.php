<?php
/**
 * Archive Photo Journal Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

get_header( );

$space_data = new SGU_Space_Data();

$paged = SGU_Static::safe_get_paged_var( ) ?: 1;
$photo_journals = $space_data -> get_photojournals( $paged );

if( ! $photo_journals ) { return ''; }

$out = [];
$max = $photo_journals -> max_num_pages ?: 1;

$out[] = SGU_Static::cpt_pagination( $max, $paged );

$idx = 0;

foreach( $photo_journals -> posts as $journal) {

    $id = $journal -> ID;
    $title = esc_html( $journal -> post_title );
    $content = wp_trim_words( $journal -> post_content, 30 );
    $img = ( function( ) use( $id ) {
        $_local_media = get_post_meta( $id, 'sgu_journal_local_image', true );
        if( $_local_media ) {
            $_img_id = SGU_Static::get_attachment_id( $_local_media );
            return esc_url( wp_get_attachment_image_url( $_img_id, 'pageimages' ) );
        } else {
            return esc_url( get_post_meta( $id, 'sgu_journal_orignal_image', true ) );
        }
    } )( );
    $side = ( $idx % 2 == 0 ) ? 'left' : 'right uk-flex-last@s';
    
    $link = esc_url( get_permalink( $journal -> ID ) );

    $out[] = <<<HTML
    <div class="uk-card uk-grid-collapse uk-child-width-1-2@s uk-margin-large" uk-grid>
        <div class="uk-card-media-$side uk-cover-container">
            <img src="$img" alt="$title" uk-cover uk-img="loading: lazy">
        </div>
        <div>
            <div class="uk-card-body">
                <h2 class="uk-card-title">$title</h2>
                $content
                <p class="uk-text-right">
                    <a href="$link" class="uk-button uk-button-secondary uk-border" title="$title">Read More...</a>
                </p>
            </div>
        </div>
    </div>
    HTML;

    ++$idx;
}

$out[] = SGU_Static::cpt_pagination( $max, $paged );

echo implode( '', $out );

get_footer( );
