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
            add_shortcode( 'sgup_astro_menu', [ $this, 'add_astro_nav' ] );

            // add the neos content
            add_shortcode( 'sgup_neos', [ $this, 'add_neos' ] );

            // add the apod
            add_shortcode( 'sgup_latest_apod', [ $this, 'add_latest_apod' ] );

        }

        /** 
         * add_astro_nav
         * 
         * Render the astronomy menu
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_astro_nav( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'which' => 'astro-menu',
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

            // hold the data we're going to pass to the template
            $sc_data = [
                'the_menu' => $the_menu
            ];

            unset( $the_menu );

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/menu.php",
                "sgu/menu.php",
                "stargazers/menu.php",
            ] );

            if( $theme_template ) {
                $template = $theme_template;
            } else {
                // Use plugin template
                $template = SGUP_PATH . '/templates/menu.php';
            }

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start( );

            // Extract attributes to variables
            extract( $sc_data );

            // Include the template
            include $template;

            // clean up
            unset( $sc_data );

            // Return the buffered content
            return ob_get_clean( );            

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

            // setup the paged
            $paged = SGU_Static::safe_get_paged_var( ) ?: 1;

            // setup the data we need to use
            $space_data = new SGU_Space_Data( );

            // get the neos
            $neos = $space_data -> get_neos( $paged );

            // setup the data we will pass to the template
            $sc_data = [
                'show_paging' => filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN ),
                'max_pages' => $neos -> max_num_pages ?: 1,
                'per_page' => absint( $atts['per_page'] ) ?: 6,
                'paging_location' => sanitize_text_field( $atts['paging_location'] ),
                'paged' => $paged,
                'show_map' => filter_var( $atts['show_map'], FILTER_VALIDATE_BOOLEAN ),
                'data' => $neos,
            ];

            // clean up
            unset( $neos );

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/neos.php",
                "sgu/neos.php",
                "stargazers/neos.php",
            ] );

            if( $theme_template ) {
                $template = $theme_template;
            } else {
                // Use plugin template
                $template = SGUP_PATH . '/templates/neos.php';
            }

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start( );

            // Extract attributes to variables
            extract( $sc_data );

            // Include the template
            include $template;

            // clean up
            unset( $sc_data );

            // Return the buffered content
            return ob_get_clean( );            

        }

        /** 
         * add_apod
         * 
         * Add an Astronomy Photo of the Day
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_latest_apod( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Astronomy Photo of the Day',
            ], $atts, 'sgup_apod' );

            // setup the data we need to use
            $space_data = new SGU_Space_Data( );

            // get the data we neeed
            $data = $space_data -> get_apod( );

            // setup the shortcode data
            $sc_data = array(
                'id' => $data -> posts[0] -> ID,
                'block_title' => esc_html( $atts['title'] ),
                'title' => $data -> posts[0] -> post_title,
                'content' => $data -> posts[0] -> post_content,
                'meta' => get_post_meta( $data -> posts[0] -> ID )
            );

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/apod/home.php",
                "sgu/apod/home.php",
                "stargazers/apod/home.php",
            ] );

            if( $theme_template ) {
                $template = $theme_template;
            } else {
                // Use plugin template
                $template = SGUP_PATH . '/templates/apod/home.php';
            }

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start( );

            // Extract attributes to variables
            extract( $sc_data );

            // Include the template
            include $template;

            // clean up
            unset( $sc_data );

            // Return the buffered content
            return ob_get_clean( );

        }

    }

}
