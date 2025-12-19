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
        private SGU_Space_Data_Get $space_data;

        // fire us up
        public function __construct( ) {
            $this -> paged = SGU_Static::safe_get_paged_var( ) ?: 1;
            $this -> space_data = new SGU_Space_Data_Get( );
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

            // setup the data we'll be passing to the template
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'latest_alerts' => $latest_alerts,
            ];

            // clean up the data
            unset( $latest_alerts );

            // get the template
            $template = $this -> pull_template( "alerts/latest.php", $sc_data );

            // clean up
            unset( $sc_data );

            // return the rendered template
            return $template;

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

            // hold the data we'll pass to the template
            $sc_data = [
                'show_paging' => filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN ),
                'max_pages' => $data -> max_num_pages ?: 1,
                'per_page' => absint( $atts['per_page'] ) ?: 6,
                'paging_location' => sanitize_text_field( $atts['paging_location'] ),
                'paged' => $this -> paged,
                'data' => $data,
            ];

            // clean up the data
            unset( $data );

            // hold the output
            $out = match( $tag ) {
                'sgup_cme_alerts' => $this -> pull_template( "alerts/cme.php", $sc_data ),
                'sgup_flare_alerts' => $this -> pull_template( "alerts/solar-flare.php", $sc_data ),
                'sgup_sw_alerts' => $this -> pull_template( "alerts/space-weather.php", $sc_data ),
                'sgup_geomag_alerts' => $this -> pull_template( "alerts/geomagnetic.php", $sc_data ),
                default => '',
            };

            // clean up
            unset( $sc_data );

            // return the output
            return $out;

        }

        /** 
         * pull_template
         * 
         * Pull and render the shortcodes template file
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function pull_template( string $path, array $data ) : string {

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/$path",
                "sgu/$path",
                "stargazers/$path",
            ] );

            if( $theme_template ) {
                $template = $theme_template;
            } else {
                // Use plugin template
                $template = SGUP_PATH . "/templates/$path";
            }

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start( );

            // Extract attributes to variables
            extract( $data );

            // Include the template
            include $template;

            // Return the buffered content
            return ob_get_clean( );

        }

    }

}