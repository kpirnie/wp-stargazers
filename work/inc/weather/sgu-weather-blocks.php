<?php
/** 
 * Weather Blocks Class
 * 
 * Handles Gutenberg block registration for weather-related blocks.
 * Provides block editor integration with real-time previews.
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Weather_Blocks' ) ) {

    /** 
     * Class SGU_Weather_Blocks
     * 
     * Registers and manages Gutenberg blocks for displaying weather data.
     * 
     * Available blocks:
     * - sgup/weather-current: Current weather conditions
     * - sgup/weather-daily: Daily forecast
     * - sgup/weather-weekly: 7-day forecast
     * - sgup/weather-alerts: NOAA weather alerts
     * - sgup/weather-full: Full weather dashboard
     * - sgup/weather-location: Location picker component
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers Plugin
     * 
    */
    class SGU_Weather_Blocks {

        /** 
         * init
         * 
         * Initialize the blocks system
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers Plugin
         * 
        */
        public function init( ) : void {

            // Register all weather blocks during WordPress initialization
            add_action( 'init', [ $this, 'register_blocks' ] );

        }

        /** 
         * register_blocks
         * 
         * Register all weather Gutenberg blocks
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers Plugin
         * 
        */
        public function register_blocks( ) : void {

            // Register Current Weather block
            register_block_type( 'sgup/weather-current', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_current_weather( [
                        'title' => $attributes['title'] ?? 'Current Weather',
                        'show_location_picker' => $attributes['showLocationPicker'] ?? true,
                        'show_details' => $attributes['showDetails'] ?? true,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => 'Current Weather' ],
                    'showLocationPicker' => [ 'type' => 'boolean', 'default' => true ],
                    'showDetails' => [ 'type' => 'boolean', 'default' => true ],
                ]
            ] );

            // Register Daily Forecast block
            register_block_type( 'sgup/weather-daily', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_daily_forecast( [
                        'title' => $attributes['title'] ?? 'Today\'s Forecast',
                        'show_location_picker' => $attributes['showLocationPicker'] ?? true,
                        'show_hourly' => $attributes['showHourly'] ?? true,
                        'hours_to_show' => $attributes['hoursToShow'] ?? 24,
                        'use_noaa' => $attributes['useNoaa'] ?? true,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => 'Today\'s Forecast' ],
                    'showLocationPicker' => [ 'type' => 'boolean', 'default' => true ],
                    'showHourly' => [ 'type' => 'boolean', 'default' => true ],
                    'hoursToShow' => [ 'type' => 'number', 'default' => 24 ],
                    'useNoaa' => [ 'type' => 'boolean', 'default' => true ],
                ]
            ] );

            // Register Weekly Forecast block
            register_block_type( 'sgup/weather-weekly', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_weekly_forecast( [
                        'title' => $attributes['title'] ?? '7-Day Forecast',
                        'show_location_picker' => $attributes['showLocationPicker'] ?? true,
                        'days_to_show' => $attributes['daysToShow'] ?? 7,
                        'use_noaa' => $attributes['useNoaa'] ?? true,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => '7-Day Forecast' ],
                    'showLocationPicker' => [ 'type' => 'boolean', 'default' => true ],
                    'daysToShow' => [ 'type' => 'number', 'default' => 7 ],
                    'useNoaa' => [ 'type' => 'boolean', 'default' => true ],
                ]
            ] );

            // Register Weather Alerts block
            register_block_type( 'sgup/weather-alerts', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_weather_alerts( [
                        'title' => $attributes['title'] ?? 'Weather Alerts',
                        'show_location_picker' => $attributes['showLocationPicker'] ?? false,
                        'max_alerts' => $attributes['maxAlerts'] ?? 5,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => 'Weather Alerts' ],
                    'showLocationPicker' => [ 'type' => 'boolean', 'default' => false ],
                    'maxAlerts' => [ 'type' => 'number', 'default' => 5 ],
                ]
            ] );

            // Register Full Weather Dashboard block
            register_block_type( 'sgup/weather-full', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_full_dashboard( [
                        'title' => $attributes['title'] ?? 'Weather Dashboard',
                        'show_current' => $attributes['showCurrent'] ?? true,
                        'show_hourly' => $attributes['showHourly'] ?? true,
                        'show_daily' => $attributes['showDaily'] ?? true,
                        'show_alerts' => $attributes['showAlerts'] ?? true,
                        'show_noaa' => $attributes['showNoaa'] ?? true,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => 'Weather Dashboard' ],
                    'showCurrent' => [ 'type' => 'boolean', 'default' => true ],
                    'showHourly' => [ 'type' => 'boolean', 'default' => true ],
                    'showDaily' => [ 'type' => 'boolean', 'default' => true ],
                    'showAlerts' => [ 'type' => 'boolean', 'default' => true ],
                    'showNoaa' => [ 'type' => 'boolean', 'default' => true ],
                ]
            ] );

            // Register Location Picker block
            register_block_type( 'sgup/weather-location', [
                'api_version' => 2,
                'render_callback' => function( $attributes ) {
                    $sc = new SGU_Weather_Shortcodes( );
                    return $sc -> render_location_picker( [
                        'title' => $attributes['title'] ?? 'Set Your Location',
                        'compact' => $attributes['compact'] ?? false,
                    ] );
                },
                'attributes' => [
                    'title' => [ 'type' => 'string', 'default' => 'Set Your Location' ],
                    'compact' => [ 'type' => 'boolean', 'default' => false ],
                ]
            ] );

        }

    }

}