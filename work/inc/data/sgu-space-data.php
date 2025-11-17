<?php
/** 
 * Space Data
 * 
 * This file contains the space data methods
 * 
 * @since 8.0
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure this class does not already exist
if( ! class_exists( 'SGU_Space_Data' ) ) {

    /** 
     * Class SGU_Space_Data
     * 
     * The actual class running the space data
     * 
     * @since 8.0
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Space_Data {

        /** 
         * get_latest_alerts
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_latest_alerts( ) : object|bool {

            // check the cache
            $_alerts = wp_cache_get( 'ussg_latest_alerts', 'ussg_latest_alerts' );

            // see if we have something in the cache for this
            if( ! $_alerts ) {

                // hold the categories
                $_cpts = array( 'sgu_cme_alerts', 'sgu_sw_alerts', 'sgu_geo_alerts', 'sgu_sf_alerts', );

                // hold the returnable object
                $_alerts = array( );

                // loop them
                foreach( $_cpts as $_cpt ) {

                    // setup our arguments for the query
                    $_args = array(
                        'post_type' => $_cpt, 
                        'posts_per_page' => 1, 
                        'orderby'=> 'date',
                        'order' => 'DESC',
                    );

                    // hold the results
                    $_res = new WP_Query( $_args );

                    // hold our recordset
                    $_alerts[$_cpt] = $_res -> posts ?: false;

                    // clean up the query and arguments
                    unset( $_res, $_args );

                }

                // force the return to a stdClass object
                $_alerts = ( object ) $_alerts;

                // set it to cache for a week
                wp_cache_add( 'ussg_latest_alerts', $_alerts, 'ussg_latest_alerts', DAY_IN_SECONDS );

            }

            // default return
            return $_alerts;

        }

        /** 
         * get_cme_alerts
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_cme_alerts( int $_paged = 1 ) : object|bool {

            // check the cache
            $_alerts = wp_cache_get( 'ussg_cme_alerts_' . $_paged, 'ussg_cme_alerts_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_alerts ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_cme_alerts', 
                    'posts_per_page' => 6, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_res = new WP_Query( $_args );

                // force the return to a stdClass object
                $_alerts = ( object ) $_res;

                // set it to cache for a week
                wp_cache_add( 'ussg_cme_alerts_' . $_paged, $_alerts, 'ussg_cme_alerts_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_alerts;

        }

        /** 
         * get_solar_flare_alerts
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_solar_flare_alerts( int $_paged = 1 ) : object|bool {

            // check the cache
            $_alerts = wp_cache_get( 'ussg_solar_flare_alerts_' . $_paged, 'ussg_solar_flare_alerts_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_alerts ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_sf_alerts', 
                    'posts_per_page' => 6, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_res = new WP_Query( $_args );

                // force the return to a stdClass object
                $_alerts = ( object ) $_res;

                // set it to cache for a week
                wp_cache_add( 'ussg_solar_flare_alerts_' . $_paged, $_alerts, 'ussg_solar_flare_alerts_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_alerts;
            
        }

        /** 
         * get_space_weather_alerts
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_space_weather_alerts( int $_paged = 1 ) : object|bool {

            // check the cache
            $_alerts = wp_cache_get( 'ussg_space_weather_alerts_' . $_paged, 'ussg_space_weather_alerts_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_alerts ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_sw_alerts', 
                    'posts_per_page' => 5, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_res = new WP_Query( $_args );

                // force the return to a stdClass object
                $_alerts = ( object ) $_res;

                // set it to cache for a week
                wp_cache_add( 'ussg_space_weather_alerts_' . $_paged, $_alerts, 'ussg_space_weather_alerts_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_alerts;

        }

        /** 
         * get_geo_magnetic_alerts
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_geo_magnetic_alerts( int $_paged = 1 ) : object|bool {

            // check the cache
            $_alerts = wp_cache_get( 'ussg_geo_magnetic_alerts_' . $_paged, 'ussg_geo_magnetic_alerts_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_alerts ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_geo_alerts', 
                    'posts_per_page' => 5, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_res = new WP_Query( $_args );

                // force the return to a stdClass object
                $_alerts = ( object ) $_res;

                // set it to cache for a week
                wp_cache_add( 'ussg_geo_magnetic_alerts_' . $_paged, $_alerts, 'ussg_geo_magnetic_alerts_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_alerts;

        }

        /** 
         * get_home_apod
         * 
         * This method returns the post for todays astronomy photo of the day
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_home_apod( ) : object|bool {

            // check the cache
            $_apod = wp_cache_get( 'ussg_todays_apod', 'ussg_todays_apod' );

            // see if we have something in the cache for this
            if( ! $_apod ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_apod', 
                    'posts_per_page' => 1, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                );

                // hold the results
                $_res = new WP_Query( $_args );

                // force the return to a stdClass object
                $_apod = ( object ) $_res;

                // set it to cache for a week
                wp_cache_add( 'ussg_todays_apod', $_apod, 'ussg_todays_apod', DAY_IN_SECONDS );

            }

            // return
            return $_apod;

        }

        /** 
         * get_neos
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_neos( int $_paged = 1 ) : object|bool {

            // check the cache
            $_neos = wp_cache_get( 'ussg_neos_' . $_paged, 'ussg_neos_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_neos ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_neo', 
                    'posts_per_page' => 6, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_neos = new WP_Query( $_args );

                // force the return to a stdClass object
                $_neos = ( object ) $_neos;

                // set it to cache for a week
                wp_cache_add( 'ussg_neos_' . $_paged, $_neos, 'ussg_neos_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_neos;

        }

        /** 
         * get_apods
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_apods( int $_paged = 1 ) : object|bool {

            // check the cache
            $_apods = wp_cache_get( 'ussg_apods_' . $_paged, 'ussg_apods_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_apods ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_apod', 
                    'posts_per_page' => 5, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_apods = new WP_Query( $_args );

                // force the return to a stdClass object
                $_apods = ( object ) $_apods;

                // set it to cache for a week
                wp_cache_add( 'ussg_apods_' . $_paged, $_apods, 'ussg_apods_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_apods;

        }

        /** 
         * get_photojournals
         * 
         * This method returns the post objects or false if none are found
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $_paged The current page of records we are on
         * 
         * @return object|bool This method returns the post objects or false if none are found
         * 
        */
        public function get_photojournals( int $_paged = 1 ) : object|bool {

            // check the cache
            $_pjs = wp_cache_get( 'ussg_pjs_' . $_paged, 'ussg_pjs_' . $_paged );

            // see if we have something in the cache for this
            if( ! $_pjs ) {

                // setup our arguments for the query
                $_args = array(
                    'post_type' => 'sgu_journal', 
                    'posts_per_page' => 5, 
                    'orderby'=> 'date',
                    'order' => 'DESC',
                    'paged' => $_paged ?: 1
                );

                // hold the results
                $_pjs = new WP_Query( $_args );

                // force the return to a stdClass object
                $_pjs = ( object ) $_pjs;

                // set it to cache for a week
                wp_cache_add( 'ussg_pjs_' . $_paged, $_pjs, 'ussg_pjs_' . $_paged, DAY_IN_SECONDS );

            }

            // return
            return $_pjs;

        }

    }

}
