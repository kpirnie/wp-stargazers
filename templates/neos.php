<?php
/**
 * Near Earth Object Archive Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// if we're showing the paging links, and it's either top or both
if( $show_paging && in_array( $paging_location, ['top', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// open the display grid
$out[] = <<<HTML
<div uk-grid class="uk-child-width-1-2@s">
HTML;

// loop the data
foreach( $data -> posts as $neo ) {

    // setup the data needed
    $content = maybe_unserialize( $neo -> post_content );
    $title = esc_html( $neo -> post_title );
    $date = esc_html( date( 'Y-m-d', strtotime( $neo -> post_date ) ) );
    $magnitude = esc_html( $content -> magnitude );
    $mindia = number_format( $content -> diameter -> kilometers['estimated_diameter_min'], 4 );
    $maxdia = number_format( $content -> diameter -> kilometers['estimated_diameter_max'], 4 );
    $hazard = SGU_Static::y_or_n( esc_html( $content -> hazardous ) );
    $approach_date = esc_html( $content -> approach_data -> close_approach_date_full );
    $approach_distance = number_format( $content -> approach_data -> miss_distance['kilometers'], 4 );
    $approach_velocity = number_format( $content -> approach_data -> relative_velocity['kilometers_per_second'], 4 );
    $approach_oribiting = esc_html( $content -> approach_data -> orbiting_body );
    $link = esc_url( $content -> jpl_url );

    // render the card
    $out[] = <<<HTML
    <div class="uk-card uk-card-small">
        <div class="uk-card-header uk-padding-small">
            <h3 class="uk-heading-divider uk-card-title">$title - <small>$date</small></h3>
        </div>
        <div class="uk-body uk-padding-small">
            <ul class="uk-list uk-list-disc">
                <li><strong>Magnitude:</strong> $magnitude</li>
                <li>
                    <strong>Diameter:</strong>
                    <ul class="uk-list uk-list-square uk-margin-remove-top">
                        <li><strong>Min:</strong> $mindia km</li>
                        <li><strong>Max:</strong> $maxdia km</li>
                    </ul>
                </li>
                <li><strong>Hazardous:</strong> $hazard</li>
                <li>
                    <strong>Approach Data:</strong>
                    <ul class="uk-list uk-list-square uk-margin-remove-top">
                        <li><strong>Closest At:</strong> $approach_date</li>
                        <li><strong>Distance:</strong> $approach_distance km</li>
                        <li><strong>Velocity:</strong> $approach_velocity km/s</li>
                        <li><strong>Orbiting:</strong> $approach_oribiting</li>
                    </ul>
                </li>
            </ul>
            <a href="$link" class="uk-button uk-button-secondary uk-align-right" target="_blank" title="$title">More Info</a>
        </div>
    </div>
    HTML;

}

// close the output
$out[] = <<<HTML
</div>
HTML;

// if we're showing the paging links, and it's either bottom or both
if( $show_paging && in_array( $paging_location, ['bottom', 'both'] ) ) {
    $out[] = SGU_Static::cpt_pagination( $max_pages, $paged );
}

// if we're going to show the nasa map
if( $show_map ) {
    $out[] = <<<HTML
    <div class="uk-visible@s">
        <h2 class="uk-heading-divider">NASA Eyes on Asteroids</h2>
        <p>Fully interactive real-time map of all asteroids and NEO's in our Solar System.</p>
        <iframe src="https://eyes.nasa.gov/apps/asteroids/#/asteroids" style="width:100%;min-height:750px;"></iframe>
    </div>
    HTML;
}

// return the output
echo implode( '', $out );

get_footer( );
