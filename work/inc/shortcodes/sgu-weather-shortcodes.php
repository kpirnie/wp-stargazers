<?php
/** 
 * Weather Shortcodes Class
 * 
 * This class handles all weather-related shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Weather_Shortcodes' ) ) {

    /** 
     * Class SGU_Weather_Shortcodes
     * 
     * Registers and handles all weather shortcodes including:
     * - [sgup_weather_current] - Current conditions
     * - [sgup_weather_daily] - Daily forecast (today)
     * - [sgup_weather_weekly] - 7-day forecast
     * - [sgup_weather_full] - Full weather dashboard
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Weather_Shortcodes {

        /** @var SGU_Weather_Data Weather data handler */
        private ?SGU_Weather_Data $weather_data = null;

        /** @var SGU_Weather_Location Location handler */
        private ?SGU_Weather_Location $location_handler = null;

        /** 
         * __construct
         * 
         * Initialize the shortcodes class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function __construct( ) {
            $this -> weather_data = new SGU_Weather_Data( );
            $this -> location_handler = new SGU_Weather_Location( );
        }

        /** 
         * init
         * 
         * Initialize and register shortcodes
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ) : void {

            // Register all weather shortcodes
            add_shortcode( 'sgup_weather_current', [ $this, 'render_current_weather' ] );
            add_shortcode( 'sgup_weather_daily', [ $this, 'render_daily_forecast' ] );
            add_shortcode( 'sgup_weather_weekly', [ $this, 'render_weekly_forecast' ] );
            add_shortcode( 'sgup_weather_alerts', [ $this, 'render_weather_alerts' ] );
            add_shortcode( 'sgup_weather_full', [ $this, 'render_full_dashboard' ] );
            add_shortcode( 'sgup_weather_location', [ $this, 'render_location_picker' ] );

        }

        /** 
         * render_current_weather
         * 
         * Render current weather conditions
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_current_weather( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Current Weather',
                'show_location_picker' => true,
                'show_details' => true,
            ], $atts, 'sgup_weather_current' );

            // Get location
            $location = $this -> location_handler -> get_stored_location( );

            // Build template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'show_location_picker' => filter_var( $atts['show_location_picker'], FILTER_VALIDATE_BOOLEAN ),
                'show_details' => filter_var( $atts['show_details'], FILTER_VALIDATE_BOOLEAN ),
                'has_location' => (bool) $location,
                'location' => $location,
                'weather' => null,
            ];

            // If we have a location, fetch weather data
            if( $location ) {
                $sc_data['weather'] = $this -> weather_data -> get_current_weather( $location -> lat, $location -> lon );
                $sc_data['location_name'] = $this -> weather_data -> reverse_geocode( $location -> lat, $location -> lon );
            }

            return $this -> render_template( 'weather/current.php', $sc_data );
        }

        /** 
         * render_daily_forecast
         * 
         * Render daily forecast (today's detailed forecast)
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_daily_forecast( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Today\'s Forecast',
                'show_location_picker' => true,
                'show_hourly' => true,
                'hours_to_show' => 24,
                'use_noaa' => true,
            ], $atts, 'sgup_weather_daily' );

            // Get location
            $location = $this -> location_handler -> get_stored_location( );

            // Build template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'show_location_picker' => filter_var( $atts['show_location_picker'], FILTER_VALIDATE_BOOLEAN ),
                'show_hourly' => filter_var( $atts['show_hourly'], FILTER_VALIDATE_BOOLEAN ),
                'hours_to_show' => absint( $atts['hours_to_show'] ),
                'use_noaa' => filter_var( $atts['use_noaa'], FILTER_VALIDATE_BOOLEAN ),
                'has_location' => (bool) $location,
                'location' => $location,
                'forecast' => null,
                'noaa_forecast' => null,
            ];

            // If we have a location, fetch forecast data
            if( $location ) {
                // Get OpenWeather hourly forecast
                $sc_data['forecast'] = $this -> weather_data -> get_hourly_forecast( $location -> lat, $location -> lon );
                
                // Get NOAA detailed text forecast if enabled
                if( $sc_data['use_noaa'] ) {
                    $sc_data['noaa_forecast'] = $this -> weather_data -> get_noaa_forecast( $location -> lat, $location -> lon );
                }

                $sc_data['location_name'] = $this -> weather_data -> reverse_geocode( $location -> lat, $location -> lon );
            }

            return $this -> render_template( 'weather/day.php', $sc_data );
        }

        /** 
         * render_weekly_forecast
         * 
         * Render 7-day weekly forecast
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_weekly_forecast( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => '7-Day Forecast',
                'show_location_picker' => true,
                'days_to_show' => 7,
                'use_noaa' => true,
            ], $atts, 'sgup_weather_weekly' );

            // Get location
            $location = $this -> location_handler -> get_stored_location( );

            // Build template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'show_location_picker' => filter_var( $atts['show_location_picker'], FILTER_VALIDATE_BOOLEAN ),
                'days_to_show' => min( absint( $atts['days_to_show'] ), 8 ), // Max 8 days from API
                'use_noaa' => filter_var( $atts['use_noaa'], FILTER_VALIDATE_BOOLEAN ),
                'has_location' => (bool) $location,
                'location' => $location,
                'forecast' => null,
                'noaa_forecast' => null,
            ];

            // If we have a location, fetch forecast data
            if( $location ) {
                // Get OpenWeather daily forecast
                $sc_data['forecast'] = $this -> weather_data -> get_daily_forecast( $location -> lat, $location -> lon );
                
                // Get NOAA detailed forecast if enabled
                if( $sc_data['use_noaa'] ) {
                    $sc_data['noaa_forecast'] = $this -> weather_data -> get_noaa_forecast( $location -> lat, $location -> lon );
                }

                $sc_data['location_name'] = $this -> weather_data -> reverse_geocode( $location -> lat, $location -> lon );
            }

            return $this -> render_template( 'weather/week.php', $sc_data );
        }

        /** 
         * render_weather_alerts
         * 
         * Render NOAA weather alerts
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_weather_alerts( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Weather Alerts',
                'show_location_picker' => false,
                'max_alerts' => 5,
            ], $atts, 'sgup_weather_alerts' );

            // Get location
            $location = $this -> location_handler -> get_stored_location( );

            // Build template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'show_location_picker' => filter_var( $atts['show_location_picker'], FILTER_VALIDATE_BOOLEAN ),
                'max_alerts' => absint( $atts['max_alerts'] ),
                'has_location' => (bool) $location,
                'location' => $location,
                'alerts' => [],
            ];

            // If we have a location, fetch alerts
            if( $location ) {
                $alerts = $this -> weather_data -> get_noaa_alerts( $location -> lat, $location -> lon );
                $sc_data['alerts'] = array_slice( $alerts, 0, $sc_data['max_alerts'] );
            }

            return $this -> render_template( 'weather/alerts.php', $sc_data );
        }

        /** 
         * render_full_dashboard
         * 
         * Render comprehensive weather dashboard with all components
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_full_dashboard( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Weather Dashboard',
                'show_current' => true,
                'show_hourly' => true,
                'show_daily' => true,
                'show_alerts' => true,
                'show_noaa' => true,
                'show_location_picker' => true,
            ], $atts, 'sgup_weather_full' );

            // Get location
            $location = $this -> location_handler -> get_stored_location( );

            // Build comprehensive template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'show_current' => filter_var( $atts['show_current'], FILTER_VALIDATE_BOOLEAN ),
                'show_hourly' => filter_var( $atts['show_hourly'], FILTER_VALIDATE_BOOLEAN ),
                'show_daily' => filter_var( $atts['show_daily'], FILTER_VALIDATE_BOOLEAN ),
                'show_alerts' => filter_var( $atts['show_alerts'], FILTER_VALIDATE_BOOLEAN ),
                'show_noaa' => filter_var( $atts['show_noaa'], FILTER_VALIDATE_BOOLEAN ),
                'show_location_picker' => filter_var( $atts['show_location_picker'], FILTER_VALIDATE_BOOLEAN ),
                'has_location' => (bool) $location,
                'location' => $location,
                'current_weather' => null,
                'hourly_forecast' => null,
                'daily_forecast' => null,
                'noaa_forecast' => null,
                'alerts' => [],
            ];

            // If we have a location, fetch all weather data
            if( $location ) {
                if( $sc_data['show_current'] ) {
                    $sc_data['current_weather'] = $this -> weather_data -> get_current_weather( $location -> lat, $location -> lon );
                }
                
                if( $sc_data['show_hourly'] ) {
                    $sc_data['hourly_forecast'] = $this -> weather_data -> get_hourly_forecast( $location -> lat, $location -> lon );
                }
                
                if( $sc_data['show_daily'] ) {
                    $sc_data['daily_forecast'] = $this -> weather_data -> get_daily_forecast( $location -> lat, $location -> lon );
                }
                
                if( $sc_data['show_noaa'] ) {
                    $sc_data['noaa_forecast'] = $this -> weather_data -> get_noaa_forecast( $location -> lat, $location -> lon );
                }
                
                if( $sc_data['show_alerts'] ) {
                    $sc_data['alerts'] = $this -> weather_data -> get_noaa_alerts( $location -> lat, $location -> lon );
                }

                $sc_data['location_name'] = $this -> weather_data -> reverse_geocode( $location -> lat, $location -> lon );
            }

            return $this -> render_template( 'weather/forecast.php', $sc_data );
        }

        /** 
         * render_location_picker
         * 
         * Render standalone location picker component
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string Rendered HTML
         * 
        */
        public function render_location_picker( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Set Your Location',
                'compact' => false,
            ], $atts, 'sgup_weather_location' );

            // Get current location
            $location = $this -> location_handler -> get_stored_location( );

            // Build template data
            $sc_data = [
                'title' => esc_html( $atts['title'] ),
                'compact' => filter_var( $atts['compact'], FILTER_VALIDATE_BOOLEAN ),
                'has_location' => (bool) $location,
                'location' => $location,
            ];

            return $this -> render_template( 'weather/location-picker.php', $sc_data );
        }

        /** 
         * render_template
         * 
         * Render a template file with provided data
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string $template_path Relative path to template
         * @param array $data Data to pass to template
         * 
         * @return string Rendered HTML
         * 
        */
        private function render_template( string $template_path, array $data ) : string {

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/$template_path",
                "sgu/$template_path",
                "stargazers/$template_path",
            ] );

            // Use theme template if found, otherwise plugin template
            $template = $theme_template ?: SGUP_PATH . "/templates/$template_path";

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return sprintf( '<!-- SGU Weather: Template not found: %s -->', esc_html( $template_path ) );
            }

            // Start output buffering
            ob_start( );

            // Extract data to variables
            extract( $data );

            // Include the template
            include $template;

            // Return buffered content
            return ob_get_clean( );
        }

    }

}