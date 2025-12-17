<?php
/** 
 * Space Data
 * 
 * This file contains the space data methods for querying and inserting
 * astronomy-related custom post types. Handles all database operations
 * for CME alerts, solar flares, NEOs, APOD, and other space data.
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
     * The actual class running the space data operations.
     * Provides unified methods for querying custom post types with caching,
     * and handles insertion/updating of space-related content from APIs.
     * 
     * @since 8.0
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Space_Data {

        /** 
         * get_paginated_posts
         * 
         * Generic method for paginated post queries with automatic caching.
         * This consolidates repeated WP_Query patterns across all space data types,
         * reducing code duplication and ensuring consistent caching behavior.
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param string $post_type The custom post type to query (e.g., 'sgu_cme_alerts')
         * @param int $paged Current page number for pagination (default: 1)
         * @param int $posts_per_page Number of posts per page (default: 6)
         * @param string $cache_group WordPress cache group name for organized caching
         * 
         * @return object|bool WP_Query object containing posts, or false on failure
         * 
        */
        private function get_paginated_posts( string $post_type, int $paged = 1, int $posts_per_page = 6, string $cache_group = '' ) : object|bool {
            
            // Build unique cache key combining group name and page number
            $cache_key = "{$cache_group}_{$paged}";
            
            // Attempt to retrieve cached query results
            $cached = wp_cache_get( $cache_key, $cache_group );
            
            // Return cached data if available, avoiding expensive database query
            if( $cached !== false ) {
                return $cached;
            }
            
            // Build query arguments for WP_Query
            $args = [
                'post_type' => $post_type,              // Which CPT to query
                'posts_per_page' => $posts_per_page,    // Limit results per page
                'orderby' => 'date',                     // Sort by publication date
                'order' => 'DESC',                       // Newest first
                'paged' => $paged ?: 1                   // Current page, default to 1
            ];
            
            // Execute the WordPress query
            $query = new WP_Query( $args );
            
            // Cache the query object for 24 hours to reduce database load
            wp_cache_add( $cache_key, $query, $cache_group, DAY_IN_SECONDS );
            
            return $query;
        }

        /** 
         * get_latest_alerts
         * 
         * Retrieves the single most recent alert post from each alert type.
         * Used primarily for the "Latest Alerts" dashboard widget showing
         * the most current space weather conditions across all categories.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return object|bool Object containing latest posts by type, or false if none found
         * 
        */
        public function get_latest_alerts( ) : object|bool {
            
            // Check cache first to avoid repeated queries
            $cached = wp_cache_get( 'ussg_latest_alerts', 'ussg_latest_alerts' );
            
            if( $cached !== false ) {
                return $cached;
            }
            
            // Define all alert custom post types
            $cpts = ['sgu_cme_alerts', 'sgu_sw_alerts', 'sgu_geo_alerts', 'sgu_sf_alerts'];
            
            // Array to store latest post from each type
            $alerts = [];
            
            // Loop through each alert type
            foreach( $cpts as $cpt ) {
                
                // Query for single most recent post of this type
                $args = [
                    'post_type' => $cpt,
                    'posts_per_page' => 1,      // Only need the latest
                    'orderby' => 'date',
                    'order' => 'DESC',
                ];
                
                // Execute query
                $query = new WP_Query( $args );
                
                // Store the posts array (or false if none) keyed by CPT
                $alerts[$cpt] = $query -> posts ?: false;
            }
            
            // Convert associative array to object for consistent return type
            $alerts = (object) $alerts;
            
            // Cache for 24 hours - these don't change frequently
            wp_cache_add( 'ussg_latest_alerts', $alerts, 'ussg_latest_alerts', DAY_IN_SECONDS );
            
            return $alerts;
        }

        /** 
         * get_cme_alerts
         * 
         * Retrieves paginated Coronal Mass Ejection alert posts.
         * CMEs are large expulsions of plasma from the Sun's corona.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_cme_alerts( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_cme_alerts', $paged, 6, 'ussg_cme_alerts' );
        }

        /** 
         * get_solar_flare_alerts
         * 
         * Retrieves paginated solar flare alert posts.
         * Solar flares are sudden flashes of increased brightness on the Sun.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_solar_flare_alerts( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_sf_alerts', $paged, 6, 'ussg_solar_flare_alerts' );
        }

        /** 
         * get_space_weather_alerts
         * 
         * Retrieves paginated space weather alert posts.
         * Space weather includes conditions in space that can affect Earth.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_space_weather_alerts( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_sw_alerts', $paged, 6, 'ussg_space_weather_alerts' );
        }

        /** 
         * get_geo_magnetic_alerts
         * 
         * Retrieves paginated geomagnetic storm alert posts.
         * Geomagnetic storms are disturbances of Earth's magnetosphere.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_geo_magnetic_alerts( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_geo_alerts', $paged, 6, 'ussg_geo_magnetic_alerts' );
        }

        /** 
         * get_apod
         * 
         * Retrieves the current Astronomy Picture of the Day post.
         * NASA's APOD features a different astronomical image each day.
         * Typically used for homepage display of today's featured image.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return object|bool WP_Query object with single post, or false if none found
         * 
        */
        public function get_apod( ) : object|bool {
            
            // Check cache - APOD only changes once per day
            $cached = wp_cache_get( 'ussg_todays_apod', 'ussg_todays_apod' );
            
            if( $cached !== false ) {
                return $cached;
            }
            
            // Query for single most recent APOD post
            $args = [
                'post_type' => 'sgu_apod',
                'posts_per_page' => 1,
                'orderby' => 'date',
                'order' => 'DESC',
            ];
            
            $query = new WP_Query( $args );
            
            // Cache for full day since APOD updates once daily
            wp_cache_add( 'ussg_todays_apod', $query, 'ussg_todays_apod', DAY_IN_SECONDS );
            
            return $query;
        }

        /** 
         * get_neos
         * 
         * Retrieves paginated Near Earth Object posts.
         * NEOs are asteroids and comets with orbits that bring them close to Earth.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_neos( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_neo', $paged, 6, 'ussg_neos' );
        }

        /** 
         * get_apods
         * 
         * Retrieves paginated archive of Astronomy Picture of the Day posts.
         * Used for browsing historical APOD entries.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param int $paged The current page of records (default: 1)
         * 
         * @return object|bool WP_Query object with posts, or false if none found
         * 
        */
        public function get_apods( int $paged = 1 ) : object|bool {
            return $this -> get_paginated_posts( 'sgu_apod', $paged, 6, 'ussg_apods' );
        }

        /** 
         * insert_geo
         * 
         * Processes and inserts geomagnetic storm forecast data from NOAA.
         * Parses plain text format data to extract product name and issue date.
         * Only inserts new posts - existing posts are not updated.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data Array containing plain text forecast data from NOAA API
         * 
         * @return bool True if successfully inserted, false if data empty or insert failed
         * 
        */
        public function insert_geo( array $data ) : bool {
            
            // Early return if no data provided
            if( ! $data ) { 
                return false; 
            }

            // Extract product name from text using regex
            // Example: ":Product: 3-Day Forecast"
            preg_match( '/:Product:\ (.*)/', $data[0], $match );
            $product = ( $match[1] ) ?? '3-Day Forecast';
            
            // Extract issue date from text
            // Example: ":Issued: 2024 Dec 11 0030 UTC"
            preg_match( '/:Issued:\ (.*)/', $data[0], $match );
            $issued = ( SGU_Static::parse_alert_date( $match[1] ) ) ?? '';
            
            // Construct post title from product and date
            $title = $product . ' - ' . $issued;
            
            // Check if post already exists by slug
            $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_geo_alerts' ) ) ?: 0;

            // Only insert if post doesn't exist
            if( $existing_id == 0 ) {
                
                // Build post data array
                $args = [
                    'post_status' => 'publish',
                    'post_title' => sanitize_text_field( $title ),
                    'post_content' => maybe_serialize( $data[0] ),  // Store full text as serialized data
                    'post_type' => 'sgu_geo_alerts',
                    'post_author' => 16,                            // System user ID
                    'post_date' => sanitize_text_field( $issued ),
                ];

                // Insert the new post
                $existing_id = wp_insert_post( $args );
            }

            // Convert post ID to boolean (0 = false, any other number = true)
            return filter_var( $existing_id, FILTER_VALIDATE_BOOLEAN );
        }

        /** 
         * insert_neo
         * 
         * Processes and inserts Near Earth Object data from NASA's NEO API.
         * Handles nested data structure where NEOs are grouped by date.
         * Stores complete object data as serialized post content.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data Array containing NEO feed data from NASA API
         * 
         * @return bool Always returns true (bulk operation doesn't track individual failures)
         * 
        */
        public function insert_neo( array $data ) : bool {
            
            // Early return if no data
            if( ! $data ) { 
                return false; 
            }

            // Extract the nested NEO data grouped by date
            $neos = $data['near_earth_objects'];

            // Loop through each date in the feed
            foreach( $neos as $key => $val ) {
                
                // The key is the approach date
                $date = $key;

                // Loop through each NEO on this date
                foreach( $val as $object ) {
                    
                    // Clean up NEO name by removing parentheses
                    // Example: "(2024 AB1)" becomes "2024 AB1"
                    $name = str_replace( ['(', ')'], '', $object['name'] );
                    
                    // Extract hazardous status as boolean
                    $hazardous = filter_var( $object['is_potentially_hazardous_asteroid'], FILTER_VALIDATE_BOOLEAN );
                    
                    // Format post date from approach date
                    $posted = date( 'Y-m-d', strtotime( $date ?: date( "Y-m-d" ) ) );

                    // Check if this NEO already exists by name
                    $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $name ), 'sgu_neo' ) ) ?: 0;

                    // Only insert if new
                    if( $existing_id == 0 ) {
                        
                        // Build post data
                        $args = [
                            'post_status' => 'publish',
                            'post_date' => sanitize_text_field( $posted  . ' 00:00 UTC' ),
                            'post_title' => sanitize_text_field( $name ),
                            'post_content' => maybe_serialize( $object ),  // Store complete NEO data
                            'post_type' => 'sgu_neo',
                            'post_author' => 16,
                        ];

                        // Insert post and get new ID
                        $existing_id = wp_insert_post( $args );
                        
                        // Store hazardous status as post meta for easier querying
                        update_post_meta( $existing_id, 'sgu_neo_hazardous', $hazardous );
                    }
                }
            }

            // Always return true for bulk operations
            return true;
        }

        /** 
         * insert_solar_flare
         * 
         * Processes and inserts solar flare event data from NASA's DONKI API.
         * Updates existing posts if NASA revises the data (common with flares).
         * Each flare is uniquely identified by its flrID from NASA.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data Array of solar flare objects from NASA DONKI API
         * 
         * @return bool True if processing succeeded, false if no data
         * 
        */
        public function insert_solar_flare( array $data ) : bool {
            
            // Early return if no data
            if( ! $data ) { 
                return false; 
            }

            // Loop through each flare in the feed
            foreach( $data as $flare ) {
                
                // Extract unique flare ID from NASA
                $title = sanitize_text_field( $flare['flrID'] );
                
                // Parse begin time to WordPress format
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $flare['beginTime'] ) ) );

                // Check if this flare already exists
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_sf_alerts' ) ) ?: 0;

                // Build post data
                $args = [
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => maybe_serialize( $flare ),  // Store complete flare data
                    'post_type' => 'sgu_sf_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                ];

                // Insert new post or update existing
                // NASA sometimes revises flare classifications
                if( $existing_id == 0 ) {
                    wp_insert_post( $args );
                } else {
                    // Update existing post with revised data
                    $args['ID'] = $existing_id;
                    wp_update_post( $args );
                }
            }

            return true;
        }

        /** 
         * insert_space_weather
         * 
         * Processes and inserts space weather alert messages from NOAA.
         * These are official notifications from the Space Weather Prediction Center.
         * Updates existing alerts if NOAA revises them.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data Array of space weather alert objects from NOAA API
         * 
         * @return bool True if processing succeeded, false if no data
         * 
        */
        public function insert_space_weather( array $data ) : bool {
            
            // Early return if no data
            if( ! $data ) { 
                return false; 
            }

            // Loop through each alert
            foreach( $data as $object ) {
                
                // Parse issue datetime to WordPress format
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $object['issue_datetime'] ) ) );
                
                // Build unique title from product ID and issue time
                // Example: "ALTK05 2024-12-11 14:30:00"
                $title = sanitize_text_field( $object['product_id'] . ' ' . $date );

                // Check if alert already exists
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_sw_alerts' ) ) ?: 0;

                // Build post data
                $args = [
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => maybe_serialize( $object ),  // Store complete alert data
                    'post_type' => 'sgu_sw_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                ];

                // Insert new or update existing
                if( $existing_id == 0 ) {
                    wp_insert_post( $args );
                } else {
                    // Update existing with revised alert
                    $args['ID'] = $existing_id;
                    wp_update_post( $args );
                }
            }

            return true;
        }

        /** 
         * insert_cme
         * 
         * Processes and inserts Coronal Mass Ejection data from NASA's DONKI API.
         * CMEs are identified by their activityID from NASA.
         * Updates existing CMEs if NASA revises the analysis.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data Array of CME objects from NASA DONKI API
         * 
         * @return bool True if processing succeeded, false if no data
         * 
        */
        public function insert_cme( array $data ) : bool {
            
            // Early return if no data
            if( ! $data ) { 
                return false; 
            }

            // Loop through each CME event
            foreach( $data as $object ) {
                
                // Parse start time to WordPress format
                $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $object['startTime'] ) ) );
                
                // Get unique activity ID from NASA
                $title = sanitize_text_field( $object['activityID'] );
                
                // Serialize complete CME data for storage
                $content = maybe_serialize( $object );

                // Check if CME already exists
                $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_cme_alerts' ) ) ?: 0;

                // Build post data
                $args = [
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_type' => 'sgu_cme_alerts',
                    'post_author' => 16,
                    'post_date' => $date,
                ];

                // Insert new or update existing
                // NASA refines CME analysis as more data comes in
                if( $existing_id == 0 ) {
                    wp_insert_post( $args );
                } else {
                    // Update with revised analysis
                    $args['ID'] = $existing_id;
                    wp_update_post( $args );
                }
            }

            return true;
        }

        /** 
         * insert_apod
         * 
         * Processes and inserts Astronomy Picture of the Day from NASA's APOD API.
         * Stores image metadata and explanation. Does not insert duplicates.
         * Image files are downloaded separately by SGU_Space_Imagery class.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @param array $data APOD object from NASA APOD API containing title, date, explanation, URLs
         * 
         * @return bool True if insert succeeded, false if data empty or insert failed
         * 
        */
        public function insert_apod( array $data ) : bool {
            
            // Early return if no data
            if( ! $data ) { 
                return false; 
            }

            // Extract and sanitize APOD data
            $title = sanitize_text_field( $data['title'] );
            $date = sanitize_text_field( date( 'Y-m-d H:i:s', strtotime( $data['date'] ) ) );
            $content = sanitize_text_field( $data['explanation'] );
            
            // Prefer HD URL if available, fall back to standard
            $media = sanitize_url( ( $data['hdurl'] ) ?? $data['url'] );
            
            // Extract copyright or default to NASA
            $copyright = sanitize_text_field( ( $data['copyright'] ) ?? 'NASA/JPL' );
            
            // Media type: 'image' or 'video'
            $media_type = sanitize_text_field( $data['media_type'] );

            // Check if APOD already exists by title
            $existing_id = ( SGU_Static::get_id_from_slug( sanitize_title( $title ), 'sgu_apod' ) ) ?: 0;

            // Build post data
            $args = [
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'sgu_apod',
                'post_author' => 16,
                'post_date' => $date,
            ];

            // Only insert if new - APODs are never updated
            if( $existing_id == 0 ) {
                $existing_id = wp_insert_post( $args );
                
                // ONLY blank out local_media on NEW posts
                // This allows imagery sync to populate it later
                // Store APOD metadata in post meta
                update_post_meta( $existing_id, 'sgu_apod_local_media_type', $media_type );  // 'image' or 'video'
                update_post_meta( $existing_id, 'sgu_apod_orignal_media', $media );          // Original NASA URL
                update_post_meta( $existing_id, 'sgu_apod_local_media', '' );                // Local URL (filled by imagery sync)
                update_post_meta( $existing_id, 'sgu_apod_copyright', $copyright );          // Copyright/credit line
            }

            // Convert ID to boolean
            return filter_var( $existing_id, FILTER_VALIDATE_BOOLEAN );
        }

        /** 
         * clean_up
         * 
         * Removes duplicate posts and orphaned post meta.
         * Uses optimized bulk queries for performance on large datasets.
         * Keeps the oldest post (lowest ID) when duplicates exist.
         * 
         * Process:
         * 1. Find duplicate posts (same title + post type)
         * 2. Delete newer duplicates and their meta
         * 3. Clean up orphaned post meta (meta without posts)
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return int Number of posts removed
         * 
        */
        public function clean_up( ) : int {
            global $wpdb;

            // Find IDs of duplicate posts using subquery
            // Keeps oldest post (lowest ID) for each title/type combination
            $delete_ids = $wpdb -> get_col(
                "SELECT p1.ID 
                FROM $wpdb->posts p1
                WHERE p1.post_type IN ('sgu_cme_alerts', 'sgu_sw_alerts', 'sgu_geo_alerts', 'sgu_sf_alerts', 'sgu_neo', 'sgu_apod')
                AND EXISTS (
                    SELECT 1 
                    FROM $wpdb->posts p2 
                    WHERE p2.post_title = p1.post_title 
                    AND p2.post_type = p1.post_type
                    AND p2.ID < p1.ID
                )"
            );

            $removed_count = 0;

            // Process deletions if duplicates found
            if( ! empty( $delete_ids ) ) {
                
                // Delete in batches of 100 to avoid query length limits
                $batches = array_chunk( $delete_ids, 100 );
                
                foreach( $batches as $batch ) {
                    // Build comma-separated ID list with proper escaping
                    $ids_string = implode( ',', array_map( 'absint', $batch ) );
                    
                    // Delete post meta first (avoid foreign key issues)
                    $wpdb -> query( "DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids_string)" );
                    
                    // Delete posts
                    $wpdb -> query( "DELETE FROM $wpdb->posts WHERE ID IN ($ids_string)" );
                }
                
                // Track total removed
                $removed_count = count( $delete_ids );
            }

            // Clean up orphaned post meta
            // Uses LEFT JOIN to find meta rows without corresponding posts
            $wpdb -> query(
                "DELETE pm FROM $wpdb->postmeta pm 
                LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id 
                WHERE p.ID IS NULL"
            );

            return $removed_count;
        }
    }
}