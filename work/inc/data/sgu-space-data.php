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

        /** 
         * insert_geo
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_geo( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // get the product from the content of the object
            preg_match( '/:Product:\ (.*)/', $data[0], $match );                    
            // hold the product 
            $product = ( $match[1] ) ?? '3-Day Forecast';
            // get the issued date from the content of the object
            preg_match( '/:Issued:\ (.*)/', $data[0], $match );
            // hold the issued date
            $issued = ( SGU_Static::parse_alert_date( $match[1] ) ) ?? '';
            // combine them for the title
            $title = $product . ' - ' . $issued;
            
            // get a post ID by the title
            $the_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_geo_alerts' ) ) ?: 0;

            // if the post ID is equals 0
            if( $the_id == 0 ) {

                // the post arguments
                $args = array(
                    'post_status' => 'publish',
                    'post_title' => sanitize_text_field( $title ),
                    'post_content' => maybe_serialize( $data[0] ), // serialze the entire object
                    'post_type' => 'sgu_geo_alerts',
                    'post_author' => 16,
                    'post_date' => sanitize_text_field( $issued ),
                );

                // insert and get the ID
                $the_id = wp_insert_post( $args );

            }

            // return the boolean value from the id on insert
            return filter_var( $the_id, FILTER_VALIDATE_BOOLEAN );

        }

        /** 
         * insert_neo
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_neo( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // all we want is the neos node from this
            $neos = $data['near_earth_objects'];

            // loop them
            foreach( $neos as $key => $val ) {

                // hold the key as the date... because... that's what it is
                $date = $key;

                // now loop each item
                foreach( $val as $object ) {

                    // setup the data we need
                    $name = str_replace( ')', '', str_replace( '(', '', $object['name'] ) );
                    $hazardous = filter_var( $object['is_potentially_hazardous_asteroid'], FILTER_VALIDATE_BOOLEAN );
                    $posted = date( 'Y-m-d', strtotime( ( $date ) ?: date( "Y-m-d" ) ) );

                    // get a post by the title
                    $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $name ), 'sgu_neo' ) ) ?: 0;

                    // if the post ID is equals 0
                    if( $existing_id == 0 ) {

                        // setup the data to insert
                        $args = array(
                            'post_status' => 'publish',
                            'post_date' => sanitize_text_field( $posted  . ' 00:00 UTC' ),
                            'post_title' => sanitize_text_field( $name ),
                            'post_content' => maybe_serialize( $object ), // serialze the entire object
                            'post_type' => 'sgu_neo',
                            'post_author' => 16,
                        );

                        // insert and get the ID
                        $the_id = wp_insert_post( $args );

                        // update the hazardous field
                        update_post_meta( $the_id, 'sgu_neo_hazardous', $hazardous );

                    }

                }

            }

            // clean up
            unset( $neos );

            // just return true
            return true;

        }

        /** 
         * insert_solar_flare
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_solar_flare( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // loop the flares
            foreach( $data as $flare ) {

                // setup the data to insert
                $title = sanitize_text_field( $flare['flrID'] );
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $flare['beginTime'] ) ) );

                // get a post by the title
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_sf_alerts' ) ) ?: 0;

                // the post arguments
                $args = array(
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => maybe_serialize( $flare ), // serialze the entire object
                    'post_type' => 'sgu_sf_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                );

                // see if it exists yet
                if( $existing_id == 0 ) {

                    // insert the flare
                    wp_insert_post( $args );

                // otherwise, nasa changed the data... and so can we
                } else {

                    // append the ID to the arguments
                    $args['ID'] = $existing_id;

                    // update
                    wp_update_post( $args );

                }

            }

            // just return true
            return true;

        }

        /** 
         * insert_space_weather
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_space_weather( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // loop the weather data
            foreach( $data as $object ) {

                // hold the data we need
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $object['issue_datetime'] ) ) );
                $title = sanitize_text_field( $object['product_id'] . ' ' . $date );

                // get an existing post if there is one
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_sw_alerts' ) ) ?: 0;

                // setup the arguments
                $args = array(
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => maybe_serialize( $object ), // serialze the entire object
                    'post_type' => 'sgu_sw_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                );

                // if the post does not already exist we can insert
                if( $existing_id == 0 ) {
                    wp_insert_post( $args );
                // otherwise, nasa changed the data, and so can we
                } else {
                    // add the existing ID to the arguments
                    $args['ID'] = $existing_id;
                    wp_update_post( $args );
                }

            }

            // just do it
            return true;

        }

        /** 
         * insert_cme
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_cme( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // loop the cme data
            foreach( $data as $object ) {

                // setup the data we "need"
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $object['startTime'] ) ) );
                $title = sanitize_text_field( $object['activityID'] );
                $content = maybe_serialize( $object );

                // see if we have an existing post
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_cme_alerts' ) ) ?: 0;

                // setup the arguments
                $args = array(
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_type' => 'sgu_cme_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                );

                // if we do not have a record, insert one
                if( $existing_id == 0 ) {
                    wp_insert_post( $args );
                // otherwise, nasa updated the data... so can we
                } else {
                    // set the arguments ID
                    $args['ID'] = $existing_id;
                    wp_update_post( $args );
                }

            }

            // return
            return true;

        }

        /** 
         * insert_apod
         * 
         * Process the data from the sync and insert it
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data The data array
         * 
         * @return bool This method returns the if it was successful or not
         * 
        */
        public function insert_apod( array $data ) : bool {

            // first things first, if there's no data, just dump out
            if( ! $data ) { return false; }

            // setup the data we need
            $title = sanitize_text_field( $data['title'] );
            $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $data['date'] ) ) );
            $content = sanitize_text_field ($data['explanation'] );
            $media = sanitize_url( $data['hdurl'] );
            $copyright = sanitize_text_field( ( array_key_exists( 'copyright', $data ) ) ? $data['copyright'] : 'NASA/JPL');
            $media_type = sanitize_text_field ($data['media_type'] );

            // get an existing item
            $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_apod' ) ) ?: 0;

            // setup the insertable arguments
            $args = array(
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'sgu_apod',
                'post_author' => 16,
                'post_date' => $date,
            );

            // if there isn't one yet, insert it
            if( $existing_id == 0 ) {
                // insert and get the ID
                $existing_id = wp_insert_post( $args );

            }

            // update the media type
            update_post_meta( $existing_id, 'sgu_apod_local_media_type', $media_type );

            // update the original image field
            update_post_meta( $existing_id, 'sgu_apod_orignal_media', $media );

            // update the local media field to blank for now
            update_post_meta( $existing_id, 'sgu_apod_local_media', '' );

            // update the copyright
            update_post_meta( $existing_id, 'sgu_apod_copyright', $copyright );

            // return the boolean value from the id on insert
            return filter_var( $existing_id, FILTER_VALIDATE_BOOLEAN );

        }
        

        /** 
         * clean_up
         * 
         * This method is utilized to clean up our data. 
         * Even though we are checking and attempting to avoid duplicates,
         * somehow we still get them...
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        public function clean_up( ) : void {

            // we need our database global because we're going to run a couple custom queries
            global $wpdb;

            // this one will remove the posts that are duplciated on the title
            $_rem_p_sql = "DELETE c1 FROM $wpdb->posts c1 INNER JOIN $wpdb->posts c2 WHERE ( c1.post_type IN ( 'sgu_cme_alerts', 'sgu_sw_alerts', 'sgu_geo_alerts', 'sgu_sf_alerts', 'sgu_neo', 'sgu_apod' ) ) AND ( c1.id > c2.id AND c1.post_title = c2.post_title );";

            // this one will get rid of the orphaned post meta data
            $_rem_pm_sql = "DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;";

            // run the post query
            $wpdb -> query( $_rem_p_sql );

            // run the postmeta query
            $wpdb -> query( $_rem_pm_sql );

        }
        
    }

}
