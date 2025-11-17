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

                // add the latest alerts menu
                $this -> add_latest_alerts_menu( );

                // add the latest alerts
                $this -> add_latest_alerts( );

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

                // setup the title
                $title = sanitize_text_field( $atts['title'] );

                // clean up the data class
                unset( $space_data );

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


    }

}
