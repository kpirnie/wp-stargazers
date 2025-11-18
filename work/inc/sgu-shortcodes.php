<?php
/** 
 * Shortcodes Class
 * 
 * This class will control the shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Shortcodes' ) ) {

    /** 
     * Class SGU_CPT_Settings
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Shortcodes {

        // hold the internal paged global
        private int $paged;

        /** 
         * init
         * 
         * Initilize the class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ): void { 

            // hook into wp init
            add_action( 'init', function( ) :void {

                // setup the paged global
                global $paged;

                // get/set the paged
                $this -> paged = ( $paged ) ?: 1;

                // add the latest alerts menu
                $this -> add_latest_alerts_menu( );

                // add the latest alerts
                $this -> add_latest_alerts( );

                // add in the full alerts
                $this -> add_cme_alerts( );
                $this -> add_solar_flare_alerts( );
                $this -> add_space_weather_alerts( );
                $this -> add_geomagnetic_alerts( );

            }, PHP_INT_MAX );

        }

        /** 
         * add_latest_alerts_menu
         * 
         * Add the latest alerts menu
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_latest_alerts_menu( ) : void {

            // create the shortcode
            add_shortcode( 'sgup_latest_alerts_menu', function( array $atts = [] ) : string {

                // Set default values
                $atts = shortcode_atts( array(
                    'which' => 'alert-menu',
                ), $atts, 'sgup_latest_alerts_menu' );
                
                // configure the menu from our themes menus
                $alert_nav_conf = array(
                    'menu' => sanitize_text_field( $atts['which'] ),
                    'items_wrap' => '%3$s',
                    'depth' => 1,
                    'container' => null,
                    'echo' => false,
                    'menu_class' => '',
                );

                // get the menu
                $the_menu = wp_nav_menu( $alert_nav_conf );

                // return the html string
                return <<<HTML
                <nav class="uk-navbar-container uk-navbar-transparent uk-margin-bottom uk-overflow-auto" uk-navbar>
                    <div class="uk-navbar-center">
                        <ul class="uk-navbar-nav page-nav-divider">
                            $the_menu
                        </ul>
                    </div>
                </nav>
                HTML;

            } );

        }

        /** 
         * add_latest_alerts
         * 
         * Add the latest alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_latest_alerts( ) : void {

            // create the shortcode
            add_shortcode( 'sgup_latest_alerts', function( array $atts = [] ) : string {

                // Set default values
                $atts = shortcode_atts( array(
                    'title' => 'Latest Astronomy Alerts',
                ), $atts, 'sgup_latest_alerts' );

                // fire up the space data class
                $space_data = new SGU_Space_Data( );

                // pull the latest alert data
                $latest_alerts = $space_data -> get_latest_alerts( );

                // clean up the data class
                unset( $space_data );

                // setup the title
                $title = sanitize_text_field( $atts['title'] );

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

                        // hold the post object
                        $alert = reset( $post_obj );

                        // grab all our variables necessary for display
                        $section_title = SGU_Static::get_cpt_display_name( $cpt );
                        $title = $alert -> post_title;
                        $content = $alert -> post_content;
                        $trimd_content = wp_trim_words( $content, 30 );
                        $data = maybe_unserialize( $content );

                        // open the card
                        $out[] = <<<HTML
                        <div class="uk-card uk-card-small">
                            <div class="uk-card-header uk-padding-small">
                                <h3 class="uk-heading-divider uk-card-title">$section_title</h3>
                            </div>
                            <div class="uk-card-body uk-padding-small">
                                <h4>$title</h4>
                        HTML;

                        // try to match our cpts
                        $out[] = match( $cpt ) {
                            'sgu_geo_alerts' => ( function( ) use ( $trimd_content ) {
                                return <<<HTML
                                    $trimd_content
                                    <a class="uk-button uk-button-secondary uk-align-right uk-margin" href="/astronomy-information/latest-alerts/geomagnetic-storm-forecast/">Read More</a>
                                HTML;
                            } )( ),
                            'sgu_sf_alerts' => ( function( ) use ( $data ) {
                                $bdate = date( 'm/d/Y H:i:s', strtotime( $data -> begin ) );
                                $edate = date( 'm/d/Y H:i:s', strtotime( $data -> end ) );
                                $cl = $data -> class;
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
                                $sdate = date( 'm/d/Y H:i:s', strtotime( $data -> start ) );
                                $catalog = $data -> catalog;
                                $source = $data -> source;
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

                // clean up the alerts object
                unset( $latest_alerts );

                // return the output
                return implode( '', $out );

            } );

        }

        /** 
         * add_cme_alerts
         * 
         * Add the cme alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_cme_alerts( ) : void {

            // register the shortcode
            add_shortcode( 'sgup_cme_alerts', function( array $atts = [] ) : string {

                // Set default values
                $atts = shortcode_atts( array(
                    'show_paging' => false,
                    'paging_location' => 'bottom',
                ), $atts, 'sgup_cme_alerts' );

                // show the pagination links?
                $show_pagination = filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN );

                // hold the paging location
                $paging_loc = sanitize_text_field( $atts['paging_location'] );

                // fire up the space data class
                $space_data = new SGU_Space_Data( );

                // hold the data necessary for this method
                $cmes = $space_data -> get_cme_alerts( $this -> paged );

                // clean up
                unset( $space_data );

                // if there is no data, dump out earlier
                if( ! $cmes ) {
                    return '';
                }
                
                // set the maximum number of pages
                $max_pages = ( $cmes -> max_num_pages ) ?: 1;

                // if we're showing the paging links, and it's either top or both
                if( $show_pagination && ( in_array( $paging_loc, ['top', 'both'] ) ) ) {

                    // add the paging
                    $out[] = $this -> alert_pagination( );
                }

                // open the output
                $out[] = <<<HTML
                <div uk-grid class="uk-child-width-1-2@s">
                HTML;

                // loop the results
                foreach( $cmes -> posts as $cme ) {

                    // setup all the data we'll need for display
                    $data = maybe_unserialize( $cme -> post_content );
                    $title = $cme -> post_title;
                    $catalog = $data -> catalog;
                    $start = date( 'r', strtotime( $data -> start ) );
                    $source = $data -> source;
                    $region = $data -> region;
                    $note = $data -> note;
                    $instruments = function( ) use ( $data ) : string {
                        $ins = [];

                        // start the instrument output
                        $ins[] = <<<HTML
                        <a class="uk-accordion-title" href="#">Instruments</a>
                        <div class="uk-accordion-content">
                            <ul class="uk-list uk-list-disc">
                        HTML;

                        // loop the instruments
                        foreach( $data -> instruments as $inst ) {
                            $name = $_inst['displayName'];

                            // add the item
                            $ins[] = <<<HTML
                                <li>$name</li>
                            HTML;
                        }

                        // end the instrument output
                        $ins[] = <<<HTML
                            </ul>
                        </div>
                        HTML;

                        // return the list
                        return implode( '', $ins );
                    };
                    $lat = number_format( $data -> analyses[0]['latitude'], 4 );
                    $lon = number_format( $data -> analyses[0]['longitude'], 4 );
                    $half_width = number_format( $data -> analyses[0]['halfAngle'], 4 );
                    $speed = number_format( $data -> analyses[0]['speed'], 4 );
                    $type = $data -> analyses[0]['type'];
                    $a_note = $data -> analyses[0]['note'];
                    $link = $data -> link;

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
                $out[] = <<<HTML
                </div>
                HTML;

                // if we're showing the paging links, and it's either bottom or both
                if( $show_pagination && ( in_array( $paging_loc, ['top', 'both'] ) ) ) {

                    // add the paging
                    $out[] = $this -> alert_pagination( );
                }

            } );

        }


        private function add_solar_flare_alerts( ) : void {

            // fire up the space data class
            $space_data = new SGU_Space_Data( );

            // hold the data necessary for this method  
            $flares = $space_data -> get_solar_flare_alerts( $this -> paged );

            // clean up
            unset( $space_data );

        }


        private function add_space_weather_alerts( ) : void {

            // fire up the space data class
            $space_data = new SGU_Space_Data( );

            // hold the data necessary for this method
            $weathers = $space_data -> get_space_weather_alerts( $this -> paged );

            // clean up
            unset( $space_data );

        }


        private function add_geomagnetic_alerts( ) : void {

            // fire up the space data class
            $space_data = new SGU_Space_Data( );

            // hold the data necessary for this method
            $geoms = $space_data -> get_geo_magnetic_alerts( $this -> paged );

            // clean up
            unset( $space_data );

        }

        /** 
         * alert_pagination
         * 
         * Add the alert pagination
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function alert_pagination( ) : string {

            // hold the output
            $out = [];



            // return the string version of it
            return implode( '', $out );

        }

    }

}
