<?php
/** 
 * Space Requests
 * 
 * This file contains the space api requests
 * 
 * @since 8.0
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure this class does not already exist
if( ! class_exists( 'SGU_Space_Requests' ) ) {

    /** 
     * Class SGU_Space_Requests
     * 
     * The actual class making the space api requests
     * 
     * @since 8.0
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Space_Requests {

        // hold the database object
        private ?SGU_Space_Data $space_data;

        // fire us up
        public function __construct( ) {

            // setup the internal space data class object
            $this -> space_data = new SGU_Space_Data( );
        }

        /** 
         * process_sync_data
         * 
         * Process the data from the API's and 
         * sync it to the database
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return bool This method returns if the data was synced or not
         * 
        */
        public function process_sync_data( string $which ) : bool {

            // get the data we need to process
            $data = $this -> get_the_data( $which );

            // return the results from the match of the type
            return match( $which ) {
                'geo' => true,//$this -> space_data -> insert_geo( $data ),
                'neo' => true,//$this -> space_data -> insert_neo( $data ),
                'sf' => true,//$this -> space_data -> insert_solar_flare( $data ),
                'sw' => true,//$this -> space_data -> insert_space_weather( $data ),
                'pj' => $this -> space_data -> insert_photo_journal( $data ),
                'apod' => ( function( ) use( $data ) {

                    // default return
                    return false;
                } )( ),
                'cme' => true,//$this -> space_data -> insert_cme( $data ),
                default => true,
            };

        }

        /** 
         * get_the_data
         * 
         * Request the api data we'll need for processing
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return array Returns an array containing the api data
         * 
        */
        private function get_the_data( string $which ) : array {

            // setup the return
            $ret = [];

            // get our api keys
            $keys = $this -> get_keys( $which );

            // now get our endpoints
            $endpoints = $this -> get_endpoints( $which );
        
            // if we are pulling photo journals
            if( $which === 'pj' ) {
                // hold a temp array
                $temp = [];
                // loop over them 
                foreach( $endpoints as $ep ) {
                    $temp[] = $ep['sgup_journal_feed'];
                }
                
                // now merge it to the full endpoints
                $endpoints = $temp;
                unset( $temp );
            }
            
            // loop the endpoints
            foreach( $endpoints as $endpoint ) {

                // get a random key from the list of our keys
                $key = ( in_array( $which, ['cme', 'sf', 'neo', 'apod'] ) ) ? $keys[ array_rand( $keys, 1 ) ] : '';
                
                // format the url endpoint
                $url = sprintf( $endpoint, $key );

                // get the data
                $ret[] = $this -> request_data( $url );
                
            }

            // return the data
            return ( $ret[0] ) ?: [];

        }

        /** 
         * request_data
         * 
         * Make the actual request
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return array Returns an array containing the api data
         * 
        */
        private function request_data( string $endpoint ) : array {

            // setup some headers to pass along with the request
            $headers = array(
                'timeout' => 30,
                'redirection' => 1,
                'user-agent' => 'US Star Gazers ( iam@kevinpirnie.com )' // I use this to make requests from NASA's API, and they like to know who they are dealing with... feel free to change it
            );

            // hold the return
            $ret = [];

            // perform the request
            $request = wp_safe_remote_get( $endpoint, $headers );

            // see if there's an error thrown
            if ( is_wp_error( $request ) ) { return $ret; }

            // hold the response
            $resp = wp_remote_retrieve_body( $request );

            // make sure we actually have a response body...
            if( $resp ) {
            
                // try to decode the response
                $ret = json_decode( $resp, true );
                
                // let's check for an error in the decoding
                if( json_last_error( ) !== JSON_ERROR_NONE ) {
                
                    // there was an error, so the response is plain text
                    $ret[] = $resp;
                
                }
                
            }

            // cleanup
            unset( $request, $resp, $headers );

            // return the object
            return $ret;

        }

        /** 
         * get_keys
         * 
         * Get our API keys
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return array Returns an array of the configured keys
         * 
        */
        private function get_keys( string $type = '' ) : array {

            // get the CME keys by default
            $cme_keys = SGU_Static::get_sgu_option( 'sgup_cme_settings' ) -> sgup_cme_api_keys ?: [ ];

            // hold the return
            $ret = match( $type ) {
                'geo' => ( function( ) {
                    // there's no keys necessary for this one
                    return [];
                } )( ),
                'sf' => ( function( ) use( $cme_keys ) {
                    // see if we are using CME keys, and if so return them
                    $use_cme = filter_var( SGU_Static::get_sgu_option( 'sgup_flare_settings' ) -> sgup_flare_use_cme ?: false, FILTER_VALIDATE_BOOLEAN );
                    if( $use_cme ) {
                        return $cme_keys;
                    }
                    // if we made it here, pull the flare keys and return them
                    return SGU_Static::get_sgu_option( 'sgup_flare_settings' ) -> sgup_flare_api_keys ?: [ ];
                } )( ),
                'sw' => ( function( ) {
                    // there's no keys necessary for this one
                    return [];
                } )( ),
                'neo' => ( function( ) use( $cme_keys ) {
                    // see if we are using CME keys, and if so return them
                    $use_cme = filter_var( SGU_Static::get_sgu_option( 'sgup_neo_settings' ) -> sgup_neo_cme ?: false, FILTER_VALIDATE_BOOLEAN );
                    if( $use_cme ) {
                        return $cme_keys;
                    }
                    // if we made it here, pull the flare keys and return them
                    return SGU_Static::get_sgu_option( 'sgup_neo_settings' ) -> sgup_neo_keys ?: [ ];
                } )( ),
                'pj' => ( function( ) {
                    // there's no keys necessary for this one
                    return [];
                } )( ),
                'apod' => ( function( ) use( $cme_keys ) {
                    // see if we are using CME keys, and if so return them
                    $use_cme = filter_var( SGU_Static::get_sgu_option( 'sgup_apod_settings' ) -> sgup_apod_cme ?: false, FILTER_VALIDATE_BOOLEAN );
                    if( $use_cme ) {
                        return $cme_keys;
                    }
                    // if we made it here, pull the flare keys and return them
                    return SGU_Static::get_sgu_option( 'sgup_apod_settings' ) -> sgup_apod_keys ?: [ ];
                } )( ),
                default => $cme_keys,
            };

            // return them
            return $ret;
        }

        /** 
         * get_endpoints
         * 
         * Get our API endpoints
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return array Returns an array of the configured endpoints
         * 
        */
        private function get_endpoints( string $type ) : array {

            // setup the return match
            $ret = match( $type ) {
                'cme' =>  ( array ) SGU_Static::get_sgu_option( 'sgup_cme_settings' ) -> sgup_cme_api_endpoint ?: [ ],
                'geo' => ( array ) SGU_Static::get_sgu_option( 'sgup_geomag_settings' ) -> sgup_geomag_endpoint ?: [ ],
                'neo' => ( array ) SGU_Static::get_sgu_option( 'sgup_neo_settings' ) -> sgup_neo_endpoint ?: [ ],
                'sf' => ( array ) SGU_Static::get_sgu_option( 'sgup_flare_settings' ) -> sgup_flare_api_endpoint ?: [ ],
                'sw' => ( array ) SGU_Static::get_sgu_option( 'sgup_sw_settings' ) -> sgup_sw_endpoint ?: [ ],
                'pj' => ( array ) SGU_Static::get_sgu_option( 'sgup_journal_settings' ) -> sgup_journal_feeds ?: [ ],
                'apod' => ( array ) SGU_Static::get_sgu_option( 'sgup_apod_settings' ) -> sgup_apod_endpoint ?: [ ],
                default => [ ],
            };

            // return the endpoint array
            return $ret;

        }


    }

}
