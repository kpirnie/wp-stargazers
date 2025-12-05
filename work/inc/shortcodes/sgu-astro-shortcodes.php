<?php
/** 
 * Astronomy Shortcodes Class
 * 
 * This class will control the rest of the astronomy 
 * shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Astro_Shortcodes' ) ) {

    /** 
     * Class SGU_Astro_Shortcodes
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Astro_Shortcodes {

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

            // add the astronomy menu
            add_shortcode( 'sgup_astro_menu', [ $this, 'add_main_astro_nav' ] );

            // add the neos content
            add_shortcode( 'sgup_neos', [ $this, 'add_neos' ] );

        }

        /** 
         * add_main_astro_nav
         * 
         * Render the astronomy menu
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_main_astro_nav( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'which' => 'alert-menu',
            ], $atts, 'sgup_astro_menu' );
            
            // configure the menu from our themes menus
            $alert_nav_conf = [
                'menu' => sanitize_text_field( $atts['which'] ),
                'items_wrap' => '%3$s',
                'depth' => 1,
                'container' => null,
                'echo' => false,
                'menu_class' => '',
            ];

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

        }

        /** 
         * add_neos
         * 
         * Render the Near Earth Objects
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_neos( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'show_paging' => false,
                'show_map' => false,
                'paging_location' => 'bottom',
                'per_page' => 6,
            ], $atts, 'sgup_neos' );

            // show the nasa asteroid map?
            $show_map = filter_var( $atts['show_map'], FILTER_VALIDATE_BOOLEAN );

            // show the pagination links?
            $show_pagination = filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN );

            // hold the paging location
            $paging_loc = sanitize_text_field( $atts['paging_location'] );

            // how many per page
            $per_page = absint( $atts['per_page'] ) ?: 6;

            // setup the paged 
            $npaged = SGU_Static::safe_get_paged_var( ) ?: 1;

            // setup the data we need to use
            $space_data = new SGU_Space_Data( );

            // get the neos
            $neos = $space_data -> get_neos( $npaged );

            // clean up
            unset( $space_data );

            // if there is no data, just dump out
            if( ! $neos ) { return ''; }

            // hold the max pages
            $max = $neos -> max_num_pages ?: 1;

            // if we're showing the paging links, and it's either top or both
            if( $show_pagination && in_array( $paging_loc, ['top', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $npaged );
            }

            // open the display grid
            $out[] = <<<HTML
            <div uk-grid class="uk-child-width-1-2@s">
            HTML;

            // loop the data
            foreach( $neos -> posts as $neo ) {

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
            if( $show_pagination && in_array( $paging_loc, ['bottom', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $npaged );
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
            return implode( '', $out );

        }

        /** 
         * add_hero_slider
         * 
         * Render the Hero Slider
         * It contains both APOD's and Photo Journals
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_hero_slider( array $atts = [] ) : string {



        }

    }

}
