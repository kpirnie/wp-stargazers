<?php
/** 
 * Space Imagery
 * 
 * This file contains methods for downloading and managing astronomy imagery.
 * Handles downloading images from external APIs (primarily NASA) and storing
 * them in the WordPress media library for local serving.
 * 
 * @since 8.0
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Star Gazers
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure this class does not already exist
if( ! class_exists( 'SGU_Space_Imagery' ) ) {

    /** 
     * Class SGU_Space_Imagery
     * 
     * Manages downloading and storing astronomy images locally.
     * 
     * Key responsibilities:
     * - Download images from NASA URLs
     * - Upload to WordPress media library
     * - Update post meta with local URLs
     * - Skip already-downloaded images
     * - Handle both images and videos appropriately
     * 
     * @since 8.0
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Star Gazers
     * 
    */
    class SGU_Space_Imagery {

        /** 
         * sync_apod_imagery
         * 
         * Downloads APOD images from NASA and stores them locally.
         * Only processes posts that don't have local copies yet.
         * 
         * Process:
         * 1. Query all APOD posts
         * 2. For each post:
         *    - Check if local copy already exists
         *    - If not, download from NASA
         *    - Upload to WordPress media library
         *    - Update post meta with local URL
         * 
         * Images only - videos remain as external embeds.
         * 
         * @since 8.0
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return void This method returns nothing
         * 
        */
        public function sync_apod_imagery( ) : void {

            // Query all published APOD posts
            $args = [
                'post_type' => 'sgu_apod',
                'post_status' => ['publish', 'future'],
                'posts_per_page' => -1  // Get all posts
            ];

            $qry = new WP_Query( $args );
            $rs = $qry -> get_posts( );

            // Early return if no posts found
            if( ! $rs ) {
                return;
            }

            // Loop through each APOD post
            foreach( $rs as $post ) {

                // Extract post ID and meta data
                $id = $post -> ID;
                $post_meta = get_post_meta( $id );
                
                // Get media URLs and type from post meta
                $orig_media = $post_meta['sgu_apod_orignal_media'][0] ?? null;
                $local_media = $post_meta['sgu_apod_local_media'][0] ?? null;
                $media_type = $post_meta['sgu_apod_local_media_type'][0] ?? null;
                
                // Check if we need to download this image
                // TRUE if local_media is empty or null
                $does_not_have_local = empty( $local_media ) || is_null( $local_media );

                // Only process if:
                // 1. We have an original URL from NASA
                // 2. No local copy exists yet
                // 3. Media type is image (not video)
                if( $orig_media && $does_not_have_local && $media_type == 'image' ) {

                    // Extract filename from NASA URL
                    // Example: https://apod.nasa.gov/apod/image/2024/galaxy.jpg -> galaxy.jpg
                    $filepath = basename( wp_parse_url( $orig_media, PHP_URL_PATH ) );

                    // Check if this file already exists in media library
                    // post_exists() returns post ID if title matches, 0 if not
                    $attach_id = post_exists( $filepath );

                    // If attachment already exists, just update the meta and skip download
                    if( $attach_id ) {
                        $attach_url = wp_get_attachment_url( $attach_id );
                        if( $attach_url ) {
                            update_post_meta( $id, 'sgu_apod_local_media', $attach_url );
                            continue; // Skip to next post
                        }
                    }

                    // Attachment doesn't exist, need to download
                    // Download image from NASA using WordPress HTTP API
                    // 90 second timeout for large HD images
                    $response = wp_safe_remote_get( $orig_media, ['timeout' => 90] );

                    // Check for download errors
                    if( ! is_wp_error( $response ) ) {

                        // Extract image binary data from response
                        $bits = wp_remote_retrieve_body( $response );

                        // Upload to WordPress using wp_upload_bits()
                        // This handles file storage and generates thumbnails
                        $upload = wp_upload_bits( $filepath, null, $bits );

                        // Check if upload succeeded
                        if( ! isset( $upload['error'] ) || ! $upload['error'] ) {

                            // Prepare attachment post data
                            $attachment = [
                                'post_title' => $filepath,
                                'post_mime_type' => $upload['type'],  // Detected MIME type
                                'guid' => $upload['url']               // Permanent URL
                            ];

                            // Insert attachment into media library
                            // Returns new attachment post ID
                            $attach_id = wp_insert_attachment( $attachment, $upload['file'], 0 );

                            // Generate attachment metadata (dimensions, thumbnails, etc.)
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                            wp_update_attachment_metadata( $attach_id, $attach_data );

                            // Update APOD post with local media URL
                            update_post_meta( $id, 'sgu_apod_local_media', $upload['url'] );

                        } else {
                            // Log upload error
                            error_log( sprintf( "Upload failed for %s: %s", $filepath, $upload['error'] ) );
                        }

                    } else {
                        // Log download error for debugging
                        error_log( sprintf( "There was an issue pulling: %s", $orig_media ) );
                        error_log( $response -> get_error_message( ) );
                    }
                }
            }
        }

        /** 
         * sync_apod_imagery_with_progress
         * 
         * Downloads APOD images from NASA and stores them locally with CLI progress.
         * Only processes posts that don't have local copies yet.
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Stargazers.us Theme
         * 
         * @return void This method returns nothing
         * 
        */
        public function sync_apod_imagery_with_progress( ) : void {

            // Query all published APOD posts that need imagery
            $args = [
                'post_type' => 'sgu_apod',
                'post_status' => [ 'publish', 'future' ],
                'posts_per_page' => -1,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'sgu_apod_local_media_type',
                        'value' => 'image',
                        'compare' => '=',
                    ],
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'sgu_apod_local_media',
                            'value' => '',
                            'compare' => '=',
                        ],
                        [
                            'key' => 'sgu_apod_local_media',
                            'compare' => 'NOT EXISTS',
                        ],
                    ],
                ],
            ];

            $qry = new WP_Query( $args );
            $rs = $qry -> get_posts( );

            // Early return if no posts found
            if( ! $rs ) {
                if( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::success( "No images to download." );
                }
                return;
            }

            $total = count( $rs );
            $downloaded = 0;
            $skipped = 0;
            $failed = 0;

            // Create progress bar if WP-CLI is available
            $progress = null;
            if( defined( 'WP_CLI' ) && WP_CLI ) {
                $progress = \WP_CLI\Utils\make_progress_bar( 'Downloading images', $total );
            }

            // Loop through each APOD post
            foreach( $rs as $post ) {

                // Extract post ID and meta data
                $id = $post -> ID;
                $orig_media = get_post_meta( $id, 'sgu_apod_orignal_media', true );

                if( ! $orig_media ) {
                    $skipped++;
                    if( $progress ) $progress -> tick( );
                    continue;
                }

                // Extract filename from NASA URL
                $filepath = basename( wp_parse_url( $orig_media, PHP_URL_PATH ) );

                // Check if this file already exists in media library
                $attach_id = post_exists( $filepath );

                // If attachment already exists, just update the meta and skip download
                if( $attach_id ) {
                    $attach_url = wp_get_attachment_url( $attach_id );
                    if( $attach_url ) {
                        update_post_meta( $id, 'sgu_apod_local_media', $attach_url );
                        $skipped++;
                        if( $progress ) $progress -> tick( );
                        continue;
                    }
                }

                // Download image from NASA
                $response = wp_safe_remote_get( $orig_media, [ 'timeout' => 90 ] );

                // Check for download errors
                if( ! is_wp_error( $response ) ) {

                    // Extract image binary data from response
                    $bits = wp_remote_retrieve_body( $response );

                    // Upload to WordPress using wp_upload_bits()
                    $upload = wp_upload_bits( $filepath, null, $bits );

                    // Check if upload succeeded
                    if( ! isset( $upload['error'] ) || ! $upload['error'] ) {

                        // Prepare attachment post data
                        $attachment = [
                            'post_title' => $filepath,
                            'post_mime_type' => $upload['type'],
                            'guid' => $upload['url'],
                        ];

                        // Insert attachment into media library
                        $attach_id = wp_insert_attachment( $attachment, $upload['file'], 0 );

                        // Generate attachment metadata
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        // Update APOD post with local media URL
                        update_post_meta( $id, 'sgu_apod_local_media', $upload['url'] );

                        $downloaded++;

                    } else {
                        $failed++;
                        error_log( sprintf( "Upload failed for %s: %s", $filepath, $upload['error'] ) );
                    }

                } else {
                    $failed++;
                    error_log( sprintf( "Download failed for %s: %s", $orig_media, $response -> get_error_message( ) ) );
                }

                if( $progress ) $progress -> tick( );

                // Small delay to avoid overwhelming servers
                usleep( 100000 ); // 0.1 second

            }

            if( $progress ) $progress -> finish( );

            // Summary
            if( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( sprintf( "  - Downloaded: %d", $downloaded ) );
                WP_CLI::line( sprintf( "  - Skipped (already exist): %d", $skipped ) );
                if( $failed > 0 ) {
                    WP_CLI::line( WP_CLI::colorize( sprintf( "  - %%RFailed: %d%%N", $failed ) ) );
                }
            }

        }
    }
}