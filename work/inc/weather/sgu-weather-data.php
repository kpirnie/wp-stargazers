<?php
/** 
 * Weather Data
 * 
 * This file contains the weather data methods for fetching and processing
 * weather information from NOAA APIs.
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Weather_Data' ) ) {

    /** 
     * Class SGU_Weather_Data
     * 
     * Handles all weather data operations using NOAA APIs exclusively.
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Weather_Data {

        /** 
         * @var array In-memory cache of grid point data
         */
        private array $grid_cache = [];

        /** 
         * get_current_weather
         * 
         * Get current weather conditions from NOAA API
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

            // Build cache key
            $cache_key = sprintf( 'sgu_noaa_current_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (30 minutes)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get grid point info first
            $grid = $this -> get_grid_point( $lat, $lon );
            if( ! $grid ) {
                return false;
            }

            // Get observation stations
            $stations_url = $grid['observationStations'] ?? null;
            if( ! $stations_url ) {
                return false;
            }

            $stations_response = $this -> make_api_request( $stations_url );
            if( ! $stations_response || empty( $stations_response['features'] ) ) {
                return false;
            }

            // Get the nearest station
            $station_id = $stations_response['features'][0]['properties']['stationIdentifier'] ?? null;
            if( ! $station_id ) {
                return false;
            }

            // Get latest observation from the station
            $obs_url = sprintf( 'https://api.weather.gov/stations/%s/observations/latest', $station_id );
            $obs_response = $this -> make_api_request( $obs_url );

            if( ! $obs_response || ! isset( $obs_response['properties'] ) ) {
                return false;
            }

            $props = $obs_response['properties'];

            // Convert to object matching expected format
            $weather = (object) [
                'main' => (object) [
                    'temp' => $this -> celsius_to_fahrenheit( $props['temperature']['value'] ?? null ),
                    'feels_like' => $this -> celsius_to_fahrenheit( $props['windChill']['value'] ?? $props['temperature']['value'] ?? null ),
                    'humidity' => $props['relativeHumidity']['value'] ?? 0,
                    'pressure' => $props['barometricPressure']['value'] ? round( $props['barometricPressure']['value'] / 100 ) : 0,
                ],
                'weather' => [
                    (object) [
                        'description' => $props['textDescription'] ?? '',
                        'icon' => $this -> noaa_icon_to_code( $props['icon'] ?? '' ),
                    ]
                ],
                'wind' => (object) [
                    'speed' => $this -> mps_to_mph( $props['windSpeed']['value'] ?? 0 ),
                    'deg' => $props['windDirection']['value'] ?? 0,
                ],
                'visibility' => $props['visibility']['value'] ?? null,
                'clouds' => (object) [
                    'all' => $this -> extract_cloud_cover( $props['cloudLayers'] ?? [] ),
                ],
                'dt' => strtotime( $props['timestamp'] ?? 'now' ),
                'sys' => (object) [
                    'sunrise' => null,
                    'sunset' => null,
                ],
                'name' => $grid['location']['city'] ?? '',
                'state' => $grid['location']['state'] ?? '',
            ];

            // Cache for 30 minutes
            wp_cache_set( $cache_key, $weather, 'sgu_weather', 30 * MINUTE_IN_SECONDS );

            return $weather;
        }

        /** 
         * get_hourly_forecast
         * 
         * Get hourly forecast from NOAA API
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
            $cache_key = sprintf( 'sgu_noaa_hourly_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (1 hour)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get grid point info
            $grid = $this -> get_grid_point( $lat, $lon );
            if( ! $grid ) {
                return false;
            }

            // Get hourly forecast URL
            $hourly_url = $grid['forecastHourly'] ?? null;
            if( ! $hourly_url ) {
                return false;
            }

            $response = $this -> make_api_request( $hourly_url );
            if( ! $response || ! isset( $response['properties']['periods'] ) ) {
                return false;
            }

            // Convert to expected format
            $hourly = [];
            foreach( $response['properties']['periods'] as $period ) {
                $hourly[] = [
                    'dt' => strtotime( $period['startTime'] ),
                    'temp' => $period['temperature'],
                    'weather' => [
                        [
                            'description' => $period['shortForecast'],
                            'icon' => $this -> noaa_icon_to_code( $period['icon'] ?? '' ),
                        ]
                    ],
                    'pop' => $this -> extract_precipitation_chance( $period['detailedForecast'] ?? '' ),
                    'wind_speed' => $this -> extract_wind_speed( $period['windSpeed'] ?? '' ),
                    'wind_deg' => $this -> wind_direction_to_degrees( $period['windDirection'] ?? '' ),
                ];
            }

            $forecast = (object) [
                'hourly' => $hourly,
                'daily' => [], // Will be populated separately if needed
            ];

            // Cache for 1 hour
            wp_cache_set( $cache_key, $forecast, 'sgu_weather', HOUR_IN_SECONDS );

            return $forecast;
        }

        /** 
         * get_daily_forecast
         * 
         * Get daily forecast from NOAA API
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
            $cache_key = sprintf( 'sgu_noaa_daily_%s_%s', round( $lat, 2 ), round( $lon, 2 ) );
            
            // Check cache (3 hours)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get grid point info
            $grid = $this -> get_grid_point( $lat, $lon );
            if( ! $grid ) {
                return false;
            }

            // Get forecast URL
            $forecast_url = $grid['forecast'] ?? null;
            if( ! $forecast_url ) {
                return false;
            }

            $response = $this -> make_api_request( $forecast_url );
            if( ! $response || ! isset( $response['properties']['periods'] ) ) {
                return false;
            }

            // NOAA returns day/night periods, we need to combine them into daily forecasts
            $daily = [];
            $periods = $response['properties']['periods'];
            
            for( $i = 0; $i < count( $periods ); $i += 2 ) {
                $day_period = $periods[$i] ?? null;
                $night_period = $periods[$i + 1] ?? null;

                if( ! $day_period ) continue;

                $daily[] = [
                    'dt' => strtotime( $day_period['startTime'] ),
                    'temp' => [
                        'max' => $day_period['temperature'] ?? 0,
                        'min' => $night_period['temperature'] ?? $day_period['temperature'] ?? 0,
                    ],
                    'weather' => [
                        [
                            'description' => $day_period['shortForecast'],
                            'icon' => $this -> noaa_icon_to_code( $day_period['icon'] ?? '' ),
                        ]
                    ],
                    'pop' => $this -> extract_precipitation_chance( $day_period['detailedForecast'] ?? '' ),
                    'summary' => $day_period['detailedForecast'] ?? '',
                    'sunrise' => 0,
                    'sunset' => 0,
                    'uvi' => 0,
                ];
            }

            $forecast = (object) [
                'daily' => $daily,
                'hourly' => [],
            ];

            // Cache for 3 hours
            wp_cache_set( $cache_key, $forecast, 'sgu_weather', 3 * HOUR_IN_SECONDS );

            return $forecast;
        }

        /** 
         * get_noaa_forecast
         * 
         * Get detailed forecast from NOAA Weather API
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
            
            // Check cache (1 hour)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Get grid point info
            $grid = $this -> get_grid_point( $lat, $lon );
            if( ! $grid ) {
                return false;
            }

            // Get forecast URL
            $forecast_url = $grid['forecast'] ?? null;
            if( ! $forecast_url ) {
                return false;
            }

            $forecast_response = $this -> make_api_request( $forecast_url );
            if( ! $forecast_response || ! isset( $forecast_response['properties'] ) ) {
                return false;
            }

            // Build forecast object
            $forecast = (object) [
                'location' => (object) [
                    'city' => $grid['location']['city'] ?? '',
                    'state' => $grid['location']['state'] ?? '',
                    'gridId' => $grid['gridId'] ?? '',
                    'gridX' => $grid['gridX'] ?? '',
                    'gridY' => $grid['gridY'] ?? '',
                ],
                'periods' => $forecast_response['properties']['periods'] ?? [],
                'generatedAt' => $forecast_response['properties']['generatedAt'] ?? '',
                'updateTime' => $forecast_response['properties']['updateTime'] ?? '',
            ];

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
            
            // Check cache (15 minutes)
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

            $response = $this -> make_api_request( $url );

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
         * Convert a ZIP code to latitude/longitude coordinates using Census Bureau API
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
            
            // Check cache (30 days)
            $cached = wp_cache_get( $cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                return $cached;
            }

            // Use Census Bureau Geocoding API (free, no key required)
            $url = sprintf(
                'https://geocoding.geo.census.gov/geocoder/locations/onelineaddress?address=%s&benchmark=Public_AR_Current&format=json',
                urlencode( $zip )
            );

            $response = $this -> make_api_request( $url, false );

            if( ! $response || ! isset( $response['result']['addressMatches'][0] ) ) {
                return false;
            }

            $match = $response['result']['addressMatches'][0];
            $coords = $match['coordinates'] ?? [];

            if( empty( $coords['x'] ) || empty( $coords['y'] ) ) {
                return false;
            }

            // Build location object
            $location = (object) [
                'lat' => (float) $coords['y'],
                'lon' => (float) $coords['x'],
                'name' => $match['matchedAddress'] ?? $zip,
                'zip' => $zip,
                'country' => 'US',
            ];

            // Cache for 30 days
            wp_cache_set( $cache_key, $location, 'sgu_weather', 30 * DAY_IN_SECONDS );

            return $location;
        }

        /** 
         * reverse_geocode
         * 
         * Convert latitude/longitude to location name using NOAA
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

            // Get grid point info (includes location data)
            $grid = $this -> get_grid_point( $lat, $lon );
            if( ! $grid ) {
                return false;
            }

            // Build location object
            $location = (object) [
                'lat' => (float) $lat,
                'lon' => (float) $lon,
                'name' => $grid['location']['city'] ?? '',
                'state' => $grid['location']['state'] ?? '',
                'country' => 'US',
            ];

            // Cache for 30 days
            wp_cache_set( $cache_key, $location, 'sgu_weather', 30 * DAY_IN_SECONDS );

            return $location;
        }

        /** 
         * get_grid_point
         * 
         * Get NOAA grid point data for coordinates (cached)
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param float $lat Latitude
         * @param float $lon Longitude
         * 
         * @return array|bool Grid point data or false on failure
         * 
        */
        private function get_grid_point( float $lat, float $lon ) : array|bool {

            // Build cache key
            $cache_key = sprintf( '%s_%s', round( $lat, 4 ), round( $lon, 4 ) );

            // Check in-memory cache
            if( isset( $this -> grid_cache[ $cache_key ] ) ) {
                return $this -> grid_cache[ $cache_key ];
            }

            // Check WP cache (grid points don't change, cache for 7 days)
            $wp_cache_key = sprintf( 'sgu_noaa_grid_%s', $cache_key );
            $cached = wp_cache_get( $wp_cache_key, 'sgu_weather' );
            if( $cached !== false ) {
                $this -> grid_cache[ $cache_key ] = $cached;
                return $cached;
            }

            // Get the grid point from NOAA
            $points_url = sprintf(
                'https://api.weather.gov/points/%s,%s',
                round( $lat, 4 ),
                round( $lon, 4 )
            );

            $response = $this -> make_api_request( $points_url );

            if( ! $response || ! isset( $response['properties'] ) ) {
                return false;
            }

            $props = $response['properties'];

            // Build grid data array
            $grid = [
                'gridId' => $props['gridId'] ?? '',
                'gridX' => $props['gridX'] ?? '',
                'gridY' => $props['gridY'] ?? '',
                'forecast' => $props['forecast'] ?? null,
                'forecastHourly' => $props['forecastHourly'] ?? null,
                'observationStations' => $props['observationStations'] ?? null,
                'location' => [
                    'city' => $props['relativeLocation']['properties']['city'] ?? '',
                    'state' => $props['relativeLocation']['properties']['state'] ?? '',
                ],
            ];

            // Cache in memory and WP
            $this -> grid_cache[ $cache_key ] = $grid;
            wp_cache_set( $wp_cache_key, $grid, 'sgu_weather', 7 * DAY_IN_SECONDS );

            return $grid;
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
         * @param bool $is_noaa Whether this is a NOAA request (default true)
         * 
         * @return array|bool Parsed response or false on failure
         * 
        */
        private function make_api_request( string $url, bool $is_noaa = true ) : array|bool {

            // Configure HTTP request parameters
            $args = [
                'timeout' => 30,
                'redirection' => 3,
            ];

            // NOAA requires specific headers
            if( $is_noaa ) {
                $args['headers'] = [
                    'Accept' => 'application/geo+json',
                    'User-Agent' => '(US Star Gazers, iam@kevinpirnie.com)',
                ];
            } else {
                $args['user-agent'] = 'US Star Gazers Weather ( iam@kevinpirnie.com )';
            }

            // Execute WordPress HTTP GET request
            $request = wp_safe_remote_get( $url, $args );

            // Check for request errors
            if ( is_wp_error( $request ) ) { 
                error_log( sprintf( 'SGU Weather API Error: %s', $request -> get_error_message( ) ) );
                return false; 
            }

            // Extract response body
            $body = wp_remote_retrieve_body( $request );

            // Check response code
            $response_code = wp_remote_retrieve_response_code( $request );
            if( $response_code !== 200 ) {
                error_log( sprintf( 'SGU Weather API HTTP Error: %s for URL: %s', $response_code, $url ) );
                return false;
            }

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
         * celsius_to_fahrenheit
         * 
         * Convert Celsius to Fahrenheit
         * 
         * @param float|null $celsius Temperature in Celsius
         * @return float Temperature in Fahrenheit
         * 
        */
        private function celsius_to_fahrenheit( ?float $celsius ) : float {
            if( $celsius === null ) {
                return 0;
            }
            return round( ( $celsius * 9/5 ) + 32 );
        }

        /** 
         * mps_to_mph
         * 
         * Convert meters per second to miles per hour
         * 
         * @param float|null $mps Speed in m/s
         * @return float Speed in mph
         * 
        */
        private function mps_to_mph( ?float $mps ) : float {
            if( $mps === null ) {
                return 0;
            }
            return round( $mps * 2.237 );
        }

        /** 
         * noaa_icon_to_code
         * 
         * Convert NOAA icon URL to simple weather code
         * 
         * @param string $icon_url NOAA icon URL
         * @return string Weather icon code
         * 
        */
        private function noaa_icon_to_code( string $icon_url ) : string {
            // Extract condition from NOAA icon URL
            // Example: https://api.weather.gov/icons/land/day/skc
            if( preg_match( '/icons\/land\/(day|night)\/([^,?]+)/', $icon_url, $matches ) ) {
                $time = $matches[1] === 'day' ? 'd' : 'n';
                $condition = $matches[2];
                
                // Map NOAA conditions to OpenWeather-style codes
                $map = [
                    'skc' => '01',      // Clear
                    'few' => '02',      // Few clouds
                    'sct' => '03',      // Scattered clouds
                    'bkn' => '04',      // Broken clouds
                    'ovc' => '04',      // Overcast
                    'rain' => '10',     // Rain
                    'rain_showers' => '09', // Showers
                    'tsra' => '11',     // Thunderstorm
                    'snow' => '13',     // Snow
                    'fog' => '50',      // Fog
                ];
                
                $code = $map[ $condition ] ?? '01';
                return $code . $time;
            }
            
            return '01d';
        }

        /** 
         * extract_cloud_cover
         * 
         * Extract cloud cover percentage from NOAA cloud layers
         * 
         * @param array $layers Cloud layer data
         * @return int Cloud cover percentage
         * 
        */
        private function extract_cloud_cover( array $layers ) : int {
            if( empty( $layers ) ) {
                return 0;
            }
            
            $coverage_map = [
                'CLR' => 0,
                'FEW' => 25,
                'SCT' => 50,
                'BKN' => 75,
                'OVC' => 100,
            ];
            
            $max_cover = 0;
            foreach( $layers as $layer ) {
                $amount = $layer['amount'] ?? '';
                $cover = $coverage_map[ $amount ] ?? 0;
                $max_cover = max( $max_cover, $cover );
            }
            
            return $max_cover;
        }

        /** 
         * extract_precipitation_chance
         * 
         * Extract precipitation chance from forecast text
         * 
         * @param string $text Detailed forecast text
         * @return float Precipitation probability (0-1)
         * 
        */
        private function extract_precipitation_chance( string $text ) : float {
            // Look for percentage in text like "Chance of precipitation is 40%"
            if( preg_match( '/(\d+)\s*%/', $text, $matches ) ) {
                return (float) $matches[1] / 100;
            }
            
            // Check for keywords
            $keywords = [
                'slight chance' => 0.2,
                'chance' => 0.4,
                'likely' => 0.7,
                'rain' => 0.5,
                'showers' => 0.5,
                'thunderstorms' => 0.5,
                'snow' => 0.5,
            ];
            
            $text_lower = strtolower( $text );
            foreach( $keywords as $keyword => $prob ) {
                if( strpos( $text_lower, $keyword ) !== false ) {
                    return $prob;
                }
            }
            
            return 0;
        }

        /** 
         * extract_wind_speed
         * 
         * Extract wind speed from NOAA wind string
         * 
         * @param string $wind_str Wind string like "10 to 15 mph"
         * @return float Wind speed in mph
         * 
        */
        private function extract_wind_speed( string $wind_str ) : float {
            if( preg_match( '/(\d+)\s*to\s*(\d+)/', $wind_str, $matches ) ) {
                return ( (float) $matches[1] + (float) $matches[2] ) / 2;
            }
            if( preg_match( '/(\d+)/', $wind_str, $matches ) ) {
                return (float) $matches[1];
            }
            return 0;
        }

        /** 
         * wind_direction_to_degrees
         * 
         * Convert wind direction string to degrees
         * 
         * @param string $direction Wind direction like "NW"
         * @return int Degrees
         * 
        */
        private function wind_direction_to_degrees( string $direction ) : int {
            $directions = [
                'N' => 0, 'NNE' => 22, 'NE' => 45, 'ENE' => 67,
                'E' => 90, 'ESE' => 112, 'SE' => 135, 'SSE' => 157,
                'S' => 180, 'SSW' => 202, 'SW' => 225, 'WSW' => 247,
                'W' => 270, 'WNW' => 292, 'NW' => 315, 'NNW' => 337,
            ];
            
            return $directions[ strtoupper( $direction ) ] ?? 0;
        }

    }

}
