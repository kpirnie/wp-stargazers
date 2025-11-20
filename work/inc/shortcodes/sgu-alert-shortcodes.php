<?php
/** 
 * Shortcodes Class
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

            // setup the internal paged
            $this -> paged = ( SGU_Static::safe_get_paged_var( ) ) ?: 1;

            // setup the internal data class
            $this -> space_data = new SGU_Space_Data( );

            // add the latest alerts menu
            $this -> add_latest_alerts_menu( );

            // add the latest alerts
            $this -> add_latest_alerts( );

            // add the alert shortcodes
            $this -> add_alerts( );

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
         * add_alerts
         * 
         * Add the alerts
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_alerts( ) : void {

            // what shortcodes do we need created
            $shortcodes = array( 'sgup_cme_alerts', 'sgup_flare_alerts', 'sgup_sw_alerts', 'sgup_geomag_alerts' );

            // loop them
            foreach( $shortcodes as $shortcode ) {

                // register the shortcode
                add_shortcode( $shortcode, function( array $atts = [] ) use( $shortcode ) : string {

                    // Set default values
                    $atts = shortcode_atts( array(
                        'show_paging' => false,
                        'paging_location' => 'bottom',
                        'per_page' => 6,
                    ), $atts, $shortcode );

                    // show the pagination links?
                    $show_pagination = filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN );

                    // hold the paging location
                    $paging_loc = sanitize_text_field( $atts['paging_location'] );

                    // how many per page
                    $per_page = absint( $atts['per_page'] ) ?: 6;

                    // setup the data
                    $data = match( $shortcode ) {
                        'sgup_cme_alerts' => $this -> space_data -> get_cme_alerts( $this -> paged ),
                        'sgup_flare_alerts' => $this -> space_data -> get_solar_flare_alerts( $this -> paged ),
                        'sgup_sw_alerts' => $this -> space_data -> get_space_weather_alerts( $this -> paged ),
                        'sgup_geomag_alerts' => $this -> space_data -> get_geo_magnetic_alerts( $this -> paged ),
                        default => null,
                    };

                    // make sure there is data, if not dump out early
                    if( ! $data ) {
                        return '';
                    }

                    // set the maximum number of pages
                    $max_pages = ( $data -> max_num_pages ) ?: 1;

                    // if we're showing the paging links, and it's either top or both
                    if( $show_pagination && ( in_array( $paging_loc, ['top', 'both'] ) ) ) {

                        // add the paging
                        $out[] = $this -> alert_pagination( $max_pages );
                    }

                    // setup the rendered output
                    $out[] = match( $shortcode ) {
                        'sgup_cme_alerts' => $this -> render_cme_alerts( $data ),
                        'sgup_flare_alerts' => $this -> render_flare_alerts( $data ),
                        'sgup_sw_alerts' => $this -> render_sw_alerts( $data ),
                        'sgup_geomag_alerts' => $this -> render_geomag_alerts( $data ),
                        default => '',
                    };

                    // if we're showing the paging links, and it's either bottom or both
                    if( $show_pagination && ( in_array( $paging_loc, ['bottom', 'both'] ) ) ) {

                        // add the paging
                        $out[] = $this -> alert_pagination( $max_pages );
                    }

                    // return the output
                    return implode( '', $out );

                } );

            }

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
            $out[] = <<<HTML
            <div uk-grid class="uk-child-width-1-2@s">
            HTML;

            // loop the results
            foreach( $data -> posts as $cme ) {

                // setup all the data we'll need for display
                $cme_data = maybe_unserialize( $cme -> post_content );
                $title = $cme -> post_title;
                $catalog = $cme_data -> catalog;
                $start = date( 'r', strtotime( $cme_data -> start ) );
                $source = $cme_data -> source;
                $region = $cme_data -> region;
                $note = $cme_data -> note;
                $instruments = ( function( ) use ( $cme_data ) : string {
                    $ins = [];

                    // start the instrument output
                    $ins[] = <<<HTML
                    <a class="uk-accordion-title" href="#">Instruments</a>
                    <div class="uk-accordion-content">
                        <ul class="uk-list uk-list-disc">
                    HTML;

                    // loop the instruments
                    foreach( $cme_data -> instruments as $inst ) {
                        $name = $inst['displayName'];

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
                } )( );
                $lat = number_format( $cme_data -> analyses[0]['latitude'], 4 );
                $lon = number_format( $cme_data -> analyses[0]['longitude'], 4 );
                $half_width = number_format( $cme_data -> analyses[0]['halfAngle'], 4 );
                $speed = number_format( $cme_data -> analyses[0]['speed'], 4 );
                $type = $cme_data -> analyses[0]['type'];
                $a_note = $cme_data -> analyses[0]['note'];
                $link = $cme_data -> link;

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
            $out[] = <<<HTML
            <div uk-grid class="uk-child-width-1-2@s">
            HTML;

            // loop the results
            foreach( $data -> posts as $flare ) {

                // setup the data to be displayed
                $title = $flare -> post_title;
                $flare_data = maybe_unserialize( $flare -> post_content );
                $fbegin = date( 'r', strtotime( $flare_data -> begin ) );
                $fend = date( 'r', strtotime( $flare_data -> end ) );
                $fpeak = date( 'r', strtotime( $flare_data -> peak ) );
                $fclass = $flare_data -> class;
                $fsource = $flare_data -> source;
                $fregion = $flare_data -> region;
                $finstruments = ( function( ) use ( $flare_data ) : string {
                    $ins = [];

                    // start the instrument output
                    $ins[] = <<<HTML
                    <a class="uk-accordion-title" href="#">Instruments</a>
                    <div class="uk-accordion-content">
                        <ul class="uk-list uk-list-disc">
                    HTML;

                    // loop the instruments
                    foreach( $flare_data -> instruments as $inst ) {
                        $name = $inst['displayName'];

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
                } )( );
                $flink= $flare_data -> link;

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
            $out[] = <<<HTML
            </div>
            HTML;

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
            $out[] = <<<HTML
            <div>
            HTML;

            // loop the results
            foreach( $data -> posts as $sw ) {

                // setup the output
                $out[] = <<<HTML
                <ul class="uk-list">
                HTML;

                // setup the data we need for the list items
                $sw_data = maybe_unserialize( $sw -> post_content );
                $issued = date( 'r', strtotime( $sw_data -> issued ) );
                $message = $sw_data -> message;

                // the list item
                $out[] = <<<HTML
                <li class="uk-padding-small uk-padding-remove-top uk-padding-remove-horizontal">
                    <h4><strong>Issued:</strong> $issued</h4>
                    <pre>$message</pre>
                </li>
                HTML;

                // end the output
                $out[] = <<<HTML
                </ul>
                HTML;

            }

            // start the output
            $out[] = <<<HTML
            </div>
            HTML;

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

            // loop the results
            foreach( $data -> posts as $geomag ) {

                // setup the data to return
                $content = maybe_unserialize( $geomag -> post_content );

                // return the content
                $out[] = <<<HTML
                <pre>$content</pre>
                HTML;

            }

            // return the output
            return implode( '', $out );

        }

        /** 
         * alert_pagination
         * 
         * Render pagination links with first and last page buttons
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param int $max_pages Maximum number of pages
         * @return string The rendered pagination HTML
         * 
        */
        private function alert_pagination( int $max_pages = 1 ) : string {

            // hold the output
            $out = array();

            // get current page
            $current_page = max( 1, $this -> paged );

            // build our pagination links
            $page_links = paginate_links( array(
                'prev_text'          => ' <span uk-icon="icon: chevron-left"></span> ', 
                'next_text'          => ' <span uk-icon="icon: chevron-right"></span> ', 
                'screen_reader_text' => ' ', 
                'current'            => $current_page, 
                'total'              => $max_pages, 
                'type'               => 'array', 
                'mid_size'           => 4,
                'base'               => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'format'             => '?paged=%#%',
            ) );

            // return empty string if no links
            if( ! $page_links ) {
                return '';
            }

            // open the pagination list
            $out[] = '<ul class="uk-pagination uk-flex-center uk-margin-medium-top">';

            // add first page link if we're not on page 1
            if( $current_page > 1 ) {
                
                // build first page link using get_pagenum_link
                $first_url = get_pagenum_link( 1 );
                
                // build first page link
                $first_link = '<a href="' . esc_url( $first_url ) . '"><span uk-icon="icon: chevron-double-left"></span></a>';
                
                $out[] = "<li>$first_link</li>";
            }

            // add each page link as a list item
            foreach( $page_links as $link ) {
                $out[] = "<li>$link</li>";
            }

            // add last page link if we're not on the last page
            if( $current_page < $max_pages ) {
                
                // build last page link using get_pagenum_link
                $last_url = get_pagenum_link( $max_pages );
                
                // build last page link
                $last_link = '<a href="' . esc_url( $last_url ) . '"><span uk-icon="icon: chevron-double-right"></span></a>';
                
                $out[] = "<li>$last_link</li>";
            }

            // close the pagination list
            $out[] = '</ul>';

            // return the complete pagination HTML
            return implode( '', $out );

        }

    }

}
