<?php
/**
 * Solar Flare Alerts Template
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

$out[] = '<div uk-grid class="uk-child-width-1-2@s">';

// loop the results
foreach( $data -> posts as $flare ) {

    // setup the data to be displayed
    $title = esc_html( $flare -> post_title );
    $flare_data = maybe_unserialize( $flare -> post_content );
    $fbegin = esc_html( date( 'r', strtotime( $flare_data -> begin ) ) );
    $fend = esc_html( date( 'r', strtotime( $flare_data -> end ) ) );
    $fpeak = esc_html( date( 'r', strtotime( $flare_data -> peak ) ) );
    $fclass = esc_html( $flare_data -> class );
    $fsource = esc_html( $flare_data -> source );
    $fregion = esc_html( $flare_data -> region );
    
    // build instruments
    $finstruments = [];
    $finstruments[] = '<a class="uk-accordion-title" href="#">Instruments</a>';
    $finstruments[] = '<div class="uk-accordion-content"><ul class="uk-list uk-list-disc">';
    foreach( $flare_data -> instruments as $inst ) {
        $name = esc_html( $inst['displayName'] );
        $finstruments[] = "<li>$name</li>";
    }
    $finstruments[] = '</ul></div>';
    $finstruments = implode( '', $finstruments );

    $flink = esc_url( $flare_data -> link );

    // create the card
    $out[] = <<<HTML
    <div class="uk-card-small" uk-card>
        <div class="uk-card-header alert-title">
            <h3 class="uk-card-title">$title</h3>
        </div>
        <div class="uk-card-body">
            <ul class="uk-list uk-list-disc">
                <li><strong>Begin: </strong>$fbegin</li>
                <li><strong>End: </strong>$fend</li>
                <li><strong>Peak: </strong>$fpeak</li>
                <li><strong>Class: </strong>$fclass</li>
                <li><strong>Source: </strong>$fsource</li>
                <li><strong>Region: </strong>$fregion</li>
                <li>
                    <ul uk-accordion>
                        <li>$finstruments</li>
                    </ul>
                </li>
            </ul>
            <a class="uk-button uk-button-secondary uk-align-right" href="$flink" target="_blank">More Info</a>
        </div>
    </div>
    HTML;

}

// end the output
$out[] = '</div>';

// if we're showing the paging links, and it's either bottom or both
if( $show_paging && in_array( $paging_location, ['bottom', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// return the output
echo implode( '', $out );
