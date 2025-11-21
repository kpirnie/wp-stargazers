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

        /** 
         * cpt_pagination
         * 
         * Render pagination links with first and last page buttons
         * 
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param int $max_pages Maximum number of pages
         * @return string The rendered pagination HTML
         * 
        */
        public static function cpt_pagination( int $max_pages = 1, int $_paged = 1 ) : string {

            // hold the output
            $out = [];

            // get current page
            $current_page = max( 1, $_paged );

            // build our pagination links
            $page_links = paginate_links( [
                'prev_text'          => ' <span uk-icon="icon: chevron-left"></span> ', 
                'next_text'          => ' <span uk-icon="icon: chevron-right"></span> ', 
                'screen_reader_text' => ' ', 
                'current'            => $current_page, 
                'total'              => $max_pages, 
                'type'               => 'array', 
                'mid_size'           => 4,
                'base'               => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'format'             => '?paged=%#%',
            ] );

            // return empty string if no links
            if( ! $page_links ) {
                return '';
            }

            // open the pagination list
            $out[] = '<ul class="uk-pagination uk-flex-center uk-margin-medium-top">';

            // add first page link if we're not on page 1
            if( $current_page > 1 ) {
                $first_url = esc_url( get_pagenum_link( 1 ) );
                $out[] = '<li><a href="' . $first_url . '"><span uk-icon="icon: chevron-double-left"></span></a></li>';
            }

            // add each page link as a list item
            foreach( $page_links as $link ) {
                $out[] = "<li>$link</li>";
            }

            // add last page link if we're not on the last page
            if( $current_page < $max_pages ) {
                $last_url = esc_url( get_pagenum_link( $max_pages ) );
                $out[] = '<li><a href="' . $last_url . '"><span uk-icon="icon: chevron-double-right"></span></a></li>';
            }

            // close the pagination list
            $out[] = '</ul>';

            // return the complete pagination HTML
            return implode( '', $out );

        }

        /** 
         * parse_alert_date
         * 
         * This method is utilized to parse the date returned from some of the space API's
         * 
         * @since 7.3
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return string The string representing the parsed date
         * 
        */
        public static function parse_alert_date( string $_str_to_parse ) : string {

            // get the year
            $_year = substr( $_str_to_parse, 0, 4 );

            // get the month
            $_month = substr( $_str_to_parse, 5, 3 );

            // get the day
            $_day = substr( $_str_to_parse, 9, 2 );

            // concatenate them to a date string
            $_date = $_year . '-' . $_month . '-' . $_day . ' 00:00 UTC';

            // return the date 
            return date( 'Y-m-d H:i:s', strtotime( $_date ) );

        }

        /** 
         * y_or_n
         * 
         * This method is utilized to convert a boolean value to Yes or No
         * 
         * @since 7.3
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param bool $should_cache The boolean value to convert
         * 
         * @return string The Yes or No string
         * 
        */
        public static function y_or_n( bool $_val ) : string {

            // return yes or no
            return $_val == true ? 'Yes' : 'No';

        }

        /** 
         * get_attachment_id
         * 
         * This method is utilized for getting the posts attachment ID
         * by it's full URL
         * 
         * @since 8.0
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param string $_url The url of the media to look up
         * 
         * @return void This method returns nothing
         * 
        */
        public static function get_attachment_id( string $_url ) : int {

            // we'll need the db global
            global $wpdb;

            // get the attachment
            $_att = $wpdb -> get_col( $wpdb -> prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $_url ) ); 
            
            // return the value
            return ( $_att[0] ) ?? 0;

        }

        /** 
         * get_archive_url
         * 
         * Get the URL for the photo journal archive page
         * Searches for a page containing the sgup_photo_journals shortcode
         * 
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return string The URL to the archive page or '/' if not found
         * 
        */
        public static function get_archive_url( string $shortcode ) : string {

            // seutp the cache key
            $cache_key = sprintf( "sgu_%s_archive_url", $shortcode );
            
            // Check cache first
            $cached_url = wp_cache_get( $cache_key, 'sgu_urls' );
            if( $cached_url !== false ) {
                return $cached_url;
            }
            
            // Search for page with the shortcode
            $args = [
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                's' => sprintf( "[%s", $shortcode ),
            ];
            
            $query = new WP_Query( $args );
            
            if( $query -> have_posts() ) {
                $url = get_permalink( $query -> posts[0] -> ID );
                // Cache for 24 hours
                wp_cache_set( $cache_key, $url, 'sgu_urls', DAY_IN_SECONDS );
                return $url;
            }
            
            // Default fallback
            $default_url = '/';
            wp_cache_set( $cache_key, $default_url, 'sgu_urls', DAY_IN_SECONDS );
            
            return $default_url;
        }

        /** 
         * get_the_single_url
         * 
         * Get the URL for a single photo journal post
         * 
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string $slug The post slug
         * @return string The URL to the single journal post
         * 
        */
        public static function get_the_single_url( string $shortcode, string $slug ) : string {
            
            $base_url = self::get_archive_url( $shortcode );
    
            // If base is just '/', return it as is
            if( $base_url === '/' ) {
                return $base_url;
            }
            
            // Build the proper URL
            return rtrim( $base_url, '/' ) . '/' . $slug . '/';
        }

        /**
         * add_cpt_rewrites
         * 
         * Add rewrite rules for CPTs that have single view shortcodes
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $cpts Array of CPT slugs that need single view rewrites
         * @return void This method returns nothing
         * 
        */
        public static function add_cpt_rewrites( array $cpts ) : void {
                        
            // Register query vars
            add_filter( 'query_vars', function( $vars ) use ( $cpts ) {
                foreach( $cpts as $cpt ) {
                    $vars[] = $cpt;
                }
                return $vars;
            } );
            
            // Add rewrite rules - hardcoded paths
            add_rewrite_rule(
                '^astronomy-information/nasa-photo-journal/([^/]+)/?$',
                'index.php?pagename=astronomy-information/nasa-photo-journal&sgu_journal=$matches[1]',
                'top'
            );
            
            add_rewrite_rule(
                '^astronomy-information/nasa-astronomy-photo-of-the-day/([^/]+)/?$',
                'index.php?pagename=astronomy-information/nasa-astronomy-photo-of-the-day&sgu_apod=$matches[1]',
                'top'
            );

        }


    }

}
