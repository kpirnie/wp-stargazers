<?php
/** 
 * Alerts Shortcodes Class
 * 
 * This class will control the alert shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Alert_Shortcodes' ) ) {

    /** 
     * Class SGU_Alert_Shortcodes
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Alert_Shortcodes {

        // hold the internals
        private int $paged;
        private SGU_Space_Data $space_data;

        // fire us up
        public function __construct( ) {
            $this -> paged = SGU_Static::safe_get_paged_var( ) ?: 1;
            $this -> space_data = new SGU_Space_Data( );
        }

        /** 
         * init
         * 
         * Initialize the class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ): void { 

            // add the latest alerts
            add_shortcode( 'sgup_latest_alerts', [ $this, 'render_latest_alerts' ] );

            // add the alert shortcodes (unified handler)
            add_shortcode( 'sgup_cme_alerts', [ $this, 'render_alert_shortcode' ] );
            add_shortcode( 'sgup_flare_alerts', [ $this, 'render_alert_shortcode' ] );
            add_shortcode( 'sgup_sw_alerts', [ $this, 'render_alert_shortcode' ] );
            add_shortcode( 'sgup_geomag_alerts', [ $this, 'render_alert_shortcode' ] );

        }

        /** 
         * render_latest_alerts
         * 
         * Render the latest alerts
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function render_latest_alerts( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Latest Astronomy Alerts',
            ], $atts, 'sgup_latest_alerts' );

            // pull the latest alert data
            $latest_alerts = $this -> space_data -> get_latest_alerts( );

            // setup the title
            $title = esc_html( $atts['title'] );

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

                    // add the alert content
                    $out[] = $this -> render_latest_alert_content( $cpt, $alert );

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
            return implode( '', $out );

        }

        /** 
         * render_latest_alert_content
         * 
         * Render content for a single latest alert
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function render_latest_alert_content( string $cpt, object $alert ) : string {

            $content = $alert -> post_content;
            $data = maybe_unserialize( $content );

            return match( $cpt ) {
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

        }

        /** 
         * render_alert_shortcode
         * 
         * Universal handler for all alert type shortcodes
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function render_alert_shortcode( array $atts = [], string $content = '', string $tag = '' ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'show_paging' => false,
                'paging_location' => 'bottom',
                'per_page' => 6,
            ], $atts, $tag );

            // show the pagination links?
            $show_pagination = filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN );

            // hold the paging location
            $paging_loc = sanitize_text_field( $atts['paging_location'] );

            // how many per page
            $per_page = absint( $atts['per_page'] ) ?: 6;

            // setup the data using match
            $data = match( $tag ) {
                'sgup_cme_alerts' => $this -> space_data -> get_cme_alerts( $this -> paged ),
                'sgup_flare_alerts' => $this -> space_data -> get_solar_flare_alerts( $this -> paged ),
                'sgup_sw_alerts' => $this -> space_data -> get_space_weather_alerts( $this -> paged ),
                'sgup_geomag_alerts' => $this -> space_data -> get_geo_magnetic_alerts( $this -> paged ),
                default => null,
            };

            // make sure there is data, if not dump out early
            if( ! $data || ! $data -> posts ) {
                return '';
            }

            // set the maximum number of pages
            $max_pages = $data -> max_num_pages ?: 1;

            // hold the output
            $out = [];

            // if we're showing the paging links, and it's either top or both
            if( $show_pagination && in_array( $paging_loc, ['top', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max_pages, $this -> paged );
            }

            // setup the rendered output using match
            $out[] = match( $tag ) {
                'sgup_cme_alerts' => $this -> render_cme_alerts( $data ),
                'sgup_flare_alerts' => $this -> render_flare_alerts( $data ),
                'sgup_sw_alerts' => $this -> render_sw_alerts( $data ),
                'sgup_geomag_alerts' => $this -> render_geomag_alerts( $data ),
                default => '',
            };

            // if we're showing the paging links, and it's either bottom or both
            if( $show_pagination && in_array( $paging_loc, ['bottom', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max_pages, $this -> paged );
            }

            // return the output
            return implode( '', $out );

        }

        /** 
         * render_cme_alerts
         * 
         * Render the CME alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function render_cme_alerts( \WP_Query $data ) : string {

            // open the output
            $out = [];
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

            // return the output
            return implode( '', $out );
            
        }

        /** 
         * render_flare_alerts
         * 
         * Render the Solar Flare alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function render_flare_alerts( \WP_Query $data ) : string {

            // start the output
            $out = [];
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

            // return the output
            return implode( '', $out );

        }

        /** 
         * render_sw_alerts
         * 
         * Render the Space Weather alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function render_sw_alerts( \WP_Query $data ) : string {

            // start the output
            $out = [];
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

            // return the output
            return implode( '', $out );

        }

        /** 
         * render_geomag_alerts
         * 
         * Render the Geo Magnetic alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function render_geomag_alerts( \WP_Query $data ) : string {

            // hold the output
            $out = [];

            // loop the results
            foreach( $data -> posts as $geomag ) {

                // setup the data to return
                $content = esc_html( maybe_unserialize( $geomag -> post_content ) );

                // return the content
                $out[] = "<pre>$content</pre>";

            }

            // return the output
            return implode( '', $out );

        }

    }

}