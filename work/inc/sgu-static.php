<?php
/** 
 * Static Class
 * 
 * This class will contain the static methods
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Static' ) ) {

    /** 
     * Class SGU_Static
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Static {

        /** 
         * get_cpt_display_name
         * 
         * get our cpt's display name
         * 
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public static function get_cpt_display_name( string $_cpt ) : string {

            // just hold the CPT and display name in an array
            $_cpts = array(
                'page' => 'Pages',
                'post' => 'Articles',
                'sgu_cme_alerts' => 'Coronal Mass Ejection',
                'sgu_sw_alerts' => 'Space Weather',
                'sgu_geo_alerts' => 'Geo Magnetic Storm',
                'sgu_sf_alerts' => 'Solar Flare',
                'sgu_neo' => 'Near Earth Objects', 
                'sgu_journal' => 'Photo Journals', 
                'sgu_apod' => 'Astronomy Photos of the Day',
            );

            // return the display name
            return $_cpts[ $_cpt ] ?: 'Invalid Post Type';

        }

        /**
         * Safely retrieves the current page number from various WordPress pagination sources.
         *
         * Checks multiple pagination sources in order of reliability: query vars (paged, page),
         * global $wp_query, $_GET parameter, and finally the REQUEST_URI. All values are
         * sanitized using absint() to ensure positive integers only.
         *
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @global WP_Query $wp_query WordPress query object.
         *
         * @return int The current page number. Returns 1 if no valid page number is found.
         */
        public static function safe_get_paged_var( ) : int {
            global $wp_query;
            
            // Check multiple possible sources
            $sources = [
                get_query_var( 'paged' ),
                get_query_var( 'page' ),
                isset( $wp_query -> query_vars['paged'] ) ? absint( $wp_query -> query_vars['paged'] ) : 0,
                isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0
            ];
            
            // loop the sources
            foreach ( $sources as $source ) {
                // ensure it's an integer, and return it if it's greater than 0
                $source = absint( $source );
                if ( $source > 0 ) {
                    return $source;
                }
            }
            
            // Parse from request URI as last resort
            if ( isset( $_SERVER['REQUEST_URI'] ) ) {

                // make sure to escape the url before parsing it and returning the page
                $request_uri = esc_url_raw( $_SERVER['REQUEST_URI'] );
                if ( preg_match( '/\/page\/(\d+)/', $request_uri, $matches ) ) {
                    return absint( $matches[1] );
                }
            }
            
            // default return
            return 1;
        }


    }

}
