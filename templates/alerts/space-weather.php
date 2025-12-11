<?php
/**
 * Space Weather Alerts Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// open the output
$out = [];

// if we're showing the paging links, and it's either top or both
if( $show_paging && in_array( $paging_location, ['top', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// start the output
$out[] = '<div><ul class="uk-list">';

// loop the results
foreach( $data -> posts as $sw ) {

    // setup the data we need for the list items
    $sw_data = maybe_unserialize( $sw -> post_content );
    $issued = esc_html( date( 'r', strtotime( $sw_data -> issued ) ) );
    $message = esc_html( $sw_data -> message );

    // the list item
    $out[] = <<<HTML
    <li class="uk-padding-small uk-padding-remove-top uk-padding-remove-horizontal">
        <h4><strong>Issued:</strong> $issued</h4>
        <pre>$message</pre>
    </li>
    HTML;

}

// end the output
$out[] = '</ul></div>';

// if we're showing the paging links, and it's either bottom or both
if( $show_paging && in_array( $paging_location, ['bottom', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// return the output
echo implode( '', $out );