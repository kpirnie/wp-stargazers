<?php
/**
 * CME Alerts Template
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
foreach( $data -> posts as $cme ) {

    // setup all the data we'll need for display
    $cme_data = maybe_unserialize( $cme -> post_content );
    $title = esc_html( $cme -> post_title );
    $catalog = esc_html( $cme_data -> catalog );
    $start = esc_html( date( 'r', strtotime( $cme_data -> start ) ) );
    $source = esc_html( $cme_data -> source );
    $region = esc_html( $cme_data -> region );
    $note = esc_html( $cme_data -> note );
    
    // build instruments
    $instruments = [];
    $instruments[] = '<a class="uk-accordion-title" href="#">Instruments</a>';
    $instruments[] = '<div class="uk-accordion-content"><ul class="uk-list uk-list-disc">';
    foreach( $cme_data -> instruments as $inst ) {
        $name = esc_html( $inst['displayName'] );
        $instruments[] = "<li>$name</li>";
    }
    $instruments[] = '</ul></div>';
    $instruments = implode( '', $instruments );

    $lat = number_format( $cme_data -> analyses[0]['latitude'], 4 );
    $lon = number_format( $cme_data -> analyses[0]['longitude'], 4 );
    $half_width = number_format( $cme_data -> analyses[0]['halfAngle'], 4 );
    $speed = number_format( $cme_data -> analyses[0]['speed'], 4 );
    $type = esc_html( $cme_data -> analyses[0]['type'] );
    $a_note = esc_html( $cme_data -> analyses[0]['note'] );
    $link = esc_url( $cme_data -> link );

    // create the card
    $out[] = <<<HTML
    <div class="uk-card-small" uk-card>
        <div class="uk-card-header alert-title">
            <h3 class="uk-card-title">$title</h3>
        </div>
        <div class="uk-card-body">
            <ul class="uk-list uk-list-disc">
                <li><strong>Catalog:</strong> $catalog</li>
                <li><strong>Start:</strong> $start</li>
                <li><strong>Source:</strong> $source</li>
                <li><strong>Region:</strong> $region</li>
                <li><strong>Note:</strong> $note</li>
                <li>
                    <ul uk-accordion>
                        <li>$instruments</li>
                        <li>
                            <a class="uk-accordion-title" href="#">Analysis</a>
                            <div class="uk-accordion-content">
                                <ul class="uk-list uk-list-disc">
                                    <li><strong>Latitude:</strong> $lat</li>
                                    <li><strong>Longitude:</strong> $lon</li>
                                    <li><strong>Half Width:</strong> $half_width</li>
                                    <li><strong>Speed:</strong> $speed</li>
                                    <li><strong>Type:</strong> $type</li>
                                    <li><strong>Note:</strong> $a_note</li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
            <a class="uk-button uk-button-secondary uk-align-right" href="$link" target="_blank">More Info</a>
        </div>
    </div>
    HTML;

}

// close the output
$out[] = '</div>';

// if we're showing the paging links, and it's either bottom or both
if( $show_paging && in_array( $paging_location, ['bottom', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// return the output
echo implode( '', $out );
