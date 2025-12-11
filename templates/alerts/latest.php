<?php
/**
 * Latest Alerts Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// hold the output
$out = [];

// begin the html output
$out[] = <<<HTML
<h2 class="uk-heading-bullet uk-heading-divider">$title</h2>
<div uk-grid class="uk-child-width-1-2@s uk-grid-divider latest-alerts-margin">
HTML;

// make sure we actually have alerts to display
if( $latest_alerts ) {

    // loop them
    foreach( $latest_alerts as $cpt => $post_obj ) {

        // make sure we have a post object
        if( ! $post_obj ) {
            continue;
        }

        // hold the post object
        $alert = reset( $post_obj );

        // grab all our variables necessary for display
        $section_title = esc_html( SGU_Static::get_cpt_display_name( $cpt ) );
        $alert_title = esc_html( $alert -> post_title );

        // open the card
        $out[] = <<<HTML
        <div class="uk-card uk-card-small">
            <div class="uk-card-header uk-padding-small">
                <h3 class="uk-heading-divider uk-card-title">$section_title</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
                <h4>$alert_title</h4>
        HTML;

        $content = $alert -> post_content;
        $data = maybe_unserialize( $content );

        $out[] = match( $cpt ) {
            'sgu_geo_alerts' => ( function( ) use ( $content ) {
                $trimd_content = wp_trim_words( $content, 30 );
                return <<<HTML
                    $trimd_content
                    <a class="uk-button uk-button-secondary uk-align-right uk-margin" href="/astronomy-information/latest-alerts/geomagnetic-storm-forecast/">Read More</a>
                HTML;
            } )( ),

            'sgu_sf_alerts' => ( function( ) use ( $data ) {
                $bdate = esc_html( date( 'm/d/Y H:i:s', strtotime( $data -> begin ) ) );
                $edate = esc_html( date( 'm/d/Y H:i:s', strtotime( $data -> end ) ) );
                $cl = esc_html( $data -> class );
                return <<<HTML
                <ul class="uk-list uk-list-disc">
                    <li>Begins: $bdate</li>
                    <li>Ends: $edate</li>
                    <li>Class: $cl</li>
                </ul>
                <a class="uk-button uk-button-secondary uk-align-right uk-margin" href="/astronomy-information/latest-alerts/solar-flare-alerts/">Read More</a>
                HTML;
            } )( ),

            'sgu_cme_alerts' => ( function( ) use ( $data ) {
                $sdate = esc_html( date( 'm/d/Y H:i:s', strtotime( $data -> start ) ) );
                $catalog = esc_html( $data -> catalog );
                $source = esc_html( $data -> source );
                return <<<HTML
                    <ul class="uk-list uk-list-disc">
                        <li>Start: $sdate</li>
                        <li>Catalog: $catalog</li>
                        <li>Source: $source</li>
                    </ul>
                    <a class="uk-button uk-button-secondary uk-align-right uk-margin" href="/astronomy-information/latest-alerts/coronal-mass-ejection-alerts/">Read More</a>
                HTML;
            } )( ),

            'sgu_sw_alerts' => ( function( ) use ( $data ) {
                $message = wp_trim_words( $data -> message, 30 );
                return <<<HTML
                $message
                <a class="uk-button uk-button-secondary uk-align-right uk-margin" href="/astronomy-information/latest-alerts/space-weather-alerts/">Read More</a>
                HTML;
            } )( ),

            default => '',
        };

        // close up the card
        $out[] = <<<HTML
            </div>
        </div>
        HTML;

    }

}

// end the html output
$out[] = <<<HTML
</div>
HTML;

// return the output
echo implode( '', $out );
