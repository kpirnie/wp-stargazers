<?php
/** 
 * Weather Data
 * 
 * This file contains the weather data methods for fetching and processing
 * weather information from OpenWeather and NOAA APIs.
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure this class does not already exist
if( ! class_exists( 'SGU_Weather_Data' ) ) {

    /** 
     * Class SGU_Weather_Data
     * 
     * Handles all weather data operations including API requests,
     * data caching, and response formatting for display.
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Weather_Data {

        /** 
         * @var array In-memory cache of API keys
         */
        private array $keys_cache = [];

        /** 
         * @var array In-memory cache of endpoints
         */
        private array $endpoints_cache = [];

        /** 
         * get_current_weather
         * 
         * Get current weather conditions from OpenWeather API
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return object|bool Weather data object or false on failure
         * 
        */
        public function get_current_weather( float $lat, float $lon ) : object|bool {

            // Build cache key based on location (rounded to 2 decimal places for cache efficiency)
            $cache_key = sprintf( 'sgu_current_weather_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache first (current weather cached for 30 minutes)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get API key
            $api_key = $this -> get_openweather_key( );
            if( ! $api_key ) {
                return false;
            }

            // Build the OpenWeather API URL for current weather
            $url = sprintf(
                'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&appid=%s&units=imperial',
                $lat,
                $lon,
                $api_key
            );

            // Make the request
            $response = $this -> make_api_request( $url );

            if( ! $response ) {
                return false;
            }

            // Convert to object
            $weather = (object) $response;

            // Cache for 30 minutes
            wp_cache_set( $cache_key, $weather, 'sgu_weather', 30 * MINUTE_IN_SECONDS );

            return $weather;
        }

        /** 
         * get_hourly_forecast
         * 
         * Get hourly forecast (up to 48 hours) from OpenWeather One Call API
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return object|bool Forecast data object or false on failure
         * 
        */
        public function get_hourly_forecast( float $lat, float $lon ) : object|bool {

            // Build cache key
            $cache_key = sprintf( 'sgu_hourly_forecast_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (hourly forecast cached for 1 hour)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get API key
            $api_key = $this -> get_openweather_key( );
            if( ! $api_key ) {
                return false;
            }

            // Build the OpenWeather One Call API 3.0 URL
            $url = sprintf(
                'https://api.openweathermap.org/data/3.0/onecall?lat=%s&lon=%s&appid=%s&units=imperial&exclude=minutely,alerts',
                $lat,
                $lon,
                $api_key
            );

            // Make the request
            $response = $this -> make_api_request( $url );

            if( ! $response ) {
                return false;
            }

            // Convert to object
            $forecast = (object) $response;

            // Cache for 1 hour
            wp_cache_set( $cache_key, $forecast, 'sgu_weather', HOUR_IN_SECONDS );

            return $forecast;
        }

        /** 
         * get_daily_forecast
         * 
         * Get daily forecast (up to 8 days) from OpenWeather One Call API
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return object|bool Forecast data object or false on failure
         * 
        */
        public function get_daily_forecast( float $lat, float $lon ) : object|bool {

            // Build cache key
            $cache_key = sprintf( 'sgu_daily_forecast_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (daily forecast cached for 3 hours)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get API key
            $api_key = $this -> get_openweather_key( );
            if( ! $api_key ) {
                return false;
            }

            // Build the OpenWeather One Call API 3.0 URL
            $url = sprintf(
                'https://api.openweathermap.org/data/3.0/onecall?lat=%s&lon=%s&appid=%s&units=imperial&exclude=minutely,hourly,alerts',
                $lat,
                $lon,
                $api_key
            );

            // Make the request
            $response = $this -> make_api_request( $url );

            if( ! $response ) {
                return false;
            }

            // Convert to object
            $forecast = (object) $response;

            // Cache for 3 hours
            wp_cache_set( $cache_key, $forecast, 'sgu_weather', 4 * HOUR_IN_SECONDS );

            return $forecast;
        }

        /** 
         * get_noaa_forecast
         * 
         * Get detailed forecast from NOAA Weather API
         * NOAA provides more detailed text forecasts for US locations
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return object|bool NOAA forecast data or false on failure
         * 
        */
        public function get_noaa_forecast( float $lat, float $lon ) : object|bool {

            // Build cache key
            $cache_key = sprintf( 'sgu_noaa_forecast_%s_%s', round( $lat, 4 ), round( $lon, 4 ) );
            
            // Check cache (NOAA forecast cached for 1 hour)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Step 1: Get the grid point from NOAA
            $points_url = sprintf(
                'https://api.weather.gov/points/%s,%s',
                round( $lat, 4 ),
                round( $lon, 4 )
            );

            $points_response = $this -> make_api_request( $points_url, true );

            if( ! $points_response || ! isset( $points_response['properties'] ) ) {
                return false;
            }

            // Step 2: Get the forecast URL from the points response
            $forecast_url = $points_response['properties']['forecast'] ?? null;
            $forecast_hourly_url = $points_response['properties']['forecastHourly'] ?? null;

            if( ! $forecast_url ) {
                return false;
            }

            // Step 3: Get the actual forecast
            $forecast_response = $this -> make_api_request( $forecast_url, true );

            if( ! $forecast_response || ! isset( $forecast_response['properties'] ) ) {
                return false;
            }

            // Build comprehensive forecast object
            $forecast = (object) [
                'location' => (object) [
                    'city' => $points_response['properties']['relativeLocation']['properties']['city'] ?? '',
                    'state' => $points_response['properties']['relativeLocation']['properties']['state'] ?? '',
                    'gridId' => $points_response['properties']['gridId'] ?? '',
                    'gridX' => $points_response['properties']['gridX'] ?? '',
                    'gridY' => $points_response['properties']['gridY'] ?? '',
                ],
                'periods' => $forecast_response['properties']['periods'] ?? [],
                'generatedAt' => $forecast_response['properties']['generatedAt'] ?? '',
                'updateTime' => $forecast_response['properties']['updateTime'] ?? '',
            ];

            // Optionally get hourly forecast
            if( $forecast_hourly_url ) {
                $hourly_response = $this -> make_api_request( $forecast_hourly_url, true );
                if( $hourly_response && isset( $hourly_response['properties']['periods'] ) ) {
                    $forecast -> hourly = $hourly_response['properties']['periods'];
                }
            }

            // Cache for 1 hour
            wp_cache_set( $cache_key, $forecast, 'sgu_weather', HOUR_IN_SECONDS );

            return $forecast;
        }

        /** 
         * get_noaa_alerts
         * 
         * Get active weather alerts from NOAA for a location
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return array Array of active alerts
         * 
        */
        public function get_noaa_alerts( float $lat, float $lon ) : array {

            // Build cache key
            $cache_key = sprintf( 'sgu_noaa_alerts_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (alerts cached for 15 minutes - they're time-sensitive)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Build the alerts URL
            $url = sprintf(
                'https://api.weather.gov/alerts/active?point=%s,%s',
                round( $lat, 4 ),
                round( $lon, 4 )
            );

            $response = $this -> make_api_request( $url, true );

            if( ! $response || ! isset( $response['features'] ) ) {
                return [];
            }

            // Extract alert information
            $alerts = [];
            foreach( $response['features'] as $feature ) {
                $props = $feature['properties'] ?? [];
                $alerts[] = (object) [
                    'event' => $props['event'] ?? '',
                    'severity' => $props['severity'] ?? '',
                    'urgency' => $props['urgency'] ?? '',
                    'headline' => $props['headline'] ?? '',
                    'description' => $props['description'] ?? '',
                    'instruction' => $props['instruction'] ?? '',
                    'effective' => $props['effective'] ?? '',
                    'expires' => $props['expires'] ?? '',
                    'senderName' => $props['senderName'] ?? '',
                ];
            }

            // Cache for 15 minutes
            wp_cache_set( $cache_key, $alerts, 'sgu_weather', 15 * MINUTE_IN_SECONDS );

            return $alerts;
        }

        /** 
         * geocode_zip
         * 
         * Convert a ZIP code to latitude/longitude coordinates
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param string $zip US ZIP code
         * 
         * @return object|bool Object with lat/lon or false on failure
         * 
        */
        public function geocode_zip( string $zip ) : object|bool {

            // Sanitize ZIP code
            $zip = preg_replace( '/[^0-9]/', '', $zip );
            
            // Validate ZIP code format (5 digits)
            if( strlen( $zip ) !== 5 ) {
                return false;
            }

            // Build cache key
            $cache_key = sprintf( 'sgu_geocode_zip_%s', $zip );
            
            // Check cache (ZIP geocoding cached for 30 days - doesn't change)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get API key
            $api_key = $this -> get_openweather_key( );
            if( ! $api_key ) {
                return false;
            }

            // Use OpenWeather Geocoding API
            $url = sprintf(
                'https://api.openweathermap.org/geo/1.0/zip?zip=%s,US&appid=%s',
                $zip,
                $api_key
            );

            $response = $this -> make_api_request( $url );

            if( ! $response || ! isset( $response['lat'] ) ) {
                return false;
            }

            // Build location object
            $location = (object) [
                'lat' => (float) $response['lat'],
                'lon' => (float) $response['lon'],
                'name' => $response['name'] ?? '',
                'zip' => $zip,
                'country' => $response['country'] ?? 'US',
            ];

            // Cache for 30 days
            wp_cache_set( $cache_key, $location, 'sgu_weather', 30 * DAY_IN_SECONDS );

            return $location;
        }

        /** 
         * reverse_geocode
         * 
         * Convert latitude/longitude to location name
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return object|bool Location object or false on failure
         * 
        */
        public function reverse_geocode( float $lat, float $lon ) : object|bool {

            // Build cache key
            $cache_key = sprintf( 'sgu_reverse_geocode_%s_%s', round( $lat, 4 ), round( $lon, 4 ) );
            
            // Check cache
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get API key
            $api_key = $this -> get_openweather_key( );
            if( ! $api_key ) {
                return false;
            }

            // Use OpenWeather Reverse Geocoding API
            $url = sprintf(
                'https://api.openweathermap.org/geo/1.0/reverse?lat=%s&lon=%s&limit=1&appid=%s',
                $lat,
                $lon,
                $api_key
            );

            $response = $this -> make_api_request( $url );

            if( ! $response || empty( $response ) ) {
                return false;
            }

            // Get first result
            $data = $response[0];

            // Build location object
            $location = (object) [
                'lat' => (float) $data['lat'],
                'lon' => (float) $data['lon'],
                'name' => $data['name'] ?? '',
                'state' => $data['state'] ?? '',
                'country' => $data['country'] ?? '',
            ];

            // Cache for 30 days
            wp_cache_set( $cache_key, $location, 'sgu_weather', 30 * DAY_IN_SECONDS );

            return $location;
        }

        /** 
         * make_api_request
         * 
         * Execute HTTP request to external API
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param string $url Full URL to request
         * @param bool $is_noaa Whether this is a NOAA request (needs special headers)
         * 
         * @return array|bool Parsed response or false on failure
         * 
        */
        private function make_api_request( string $url, bool $is_noaa = false ) : array|bool {

            // Configure HTTP request parameters
            $args = [
                'timeout' => 30,
                'redirection' => 3,
                'user-agent' => 'US Star Gazers Weather ( iam@kevinpirnie.com )',
            ];

            // NOAA requires Accept header
            if( $is_noaa ) {
                $args['headers'] = [
                    'Accept' => 'application/geo+json',
                    'User-Agent' => '(US Star Gazers, iam@kevinpirnie.com)',
                ];
            }

            // Execute WordPress HTTP GET request
            $request = wp_safe_remote_get( $url, $args );

            // Check for request errors
            if ( is_wp_error( $request ) ) { 
                error_log( sprintf( 'SGU Weather API Error: %s', $request -> get_error_message( ) ) );
                return false; 
            }

            // Check response code
            $response_code = wp_remote_retrieve_response_code( $request );
            if( $response_code !== 200 ) {
                error_log( sprintf( 'SGU Weather API HTTP Error: %s for URL: %s', $response_code, $url ) );
                return false;
            }

            // Extract response body
            $body = wp_remote_retrieve_body( $request );

            if( ! $body ) {
                return false;
            }
        
            // Decode JSON response
            $data = json_decode( $body, true );
            
            // Check for JSON decode errors
            if( json_last_error( ) !== JSON_ERROR_NONE ) {
                error_log( sprintf( 'SGU Weather JSON Error: %s', json_last_error_msg( ) ) );
                return false;
            }

            return $data;
        }

        /** 
         * get_openweather_key
         * 
         * Get an OpenWeather API key from settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return string|bool API key or false if none configured
         * 
        */
        private function get_openweather_key( ) : string|bool {

            // Check cache
            if( isset( $this -> keys_cache['openweather'] ) ) {
                return $this -> keys_cache['openweather'];
            }

            // Get keys from settings
            $keys = SGU_Static::get_sgu_option( 'sgup_apis' ) -> sgup_ow_keys ?? [];

            if( empty( $keys ) ) {
                return false;
            }

            // Select random key for rate limit distribution
            $key = $keys[ array_rand( $keys ) ];

            // Cache it
            $this -> keys_cache['openweather'] = $key;

            return $key;
        }

    }

}