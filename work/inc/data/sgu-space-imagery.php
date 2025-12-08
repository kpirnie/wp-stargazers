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
if( ! class_exists( 'SGU_Space_Imagery' ) ) {

    /** 
     * Class SGU_Space_Imagery
     * 
     * The actual class running the space imagery
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
         * Sync the Photo of the Day imagery
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

            // get the posts for this post type
            $args = array(
                'post_type' => 'sgu_apod', 
                'post_status' => array( 'publish', 'future' ),
                'posts_per_page' => -1
            );

            // query for our neo items
            $qry = new WP_Query( $args );
            $rs = $qry -> get_posts( );

            // make sure we have records here
            if( $rs ) {

                // loop them
                foreach( $rs as $post ) {

                    // setup the data we need
                    $id = $post -> ID;
                    $post_meta = get_post_meta( $id );
                    $orig_media = $post_meta['sgu_apod_orignal_media'][0] ?? null;
                    $local_media = $post_meta['sgu_apod_local_media'][0] ?? null;
                    $media_type = $post_meta['sgu_apod_local_media_type'][0] ?? null;
                    $does_not_have_local = ( ( empty( $local_media ) ) || ( is_null( $local_media ) ) ) ?: false;

                    // now... make sure we have original media AND there is no local media
                    if( $orig_media && $does_not_have_local ) {

                        // setup the filename
                        $filepath = basename( wp_parse_url( $orig_media, PHP_URL_PATH ) );

                        // hold the file attachment ID if this already exists
                        $attach_id = post_exists( $filepath );

                        // get the attachment URL
                        $attach_uri = wp_get_attachment_url( $attach_id );

                        $file_exists = SGU_Static::attachment_url_to_path( $attach_uri );

                        // make sure it doesn't already exits
                        if( ! $file_exists && $media_type == 'image' ) {

                            // try to get the remote image
                            $response = wp_safe_remote_get( $orig_media, array( 'timeout' => 90 ) );

                            // if we don't have an error on the remote pull
                            if( ! is_wp_error( $response ) ) {

                                // get our files "bits"
                                $bits = wp_remote_retrieve_body( $response );

                                // "upload" it locally based on said "bits"
                                $upload = wp_upload_bits( $filepath, null, $bits );

                                // setup our attachment options
                                $attachment = array(
                                    'post_title'=> $filepath,
                                    'post_mime_type' => $upload['type'],
                                    'guid' => $upload['url']
                                );

                                // insert the attachment to our media library
                                $attach_id = wp_insert_attachment( $attachment, $upload['file'], 0 );

                                // setup the meta data
                                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                                wp_update_attachment_metadata( $attach_id, $attach_data );

                                // update the post meta
                                update_post_meta( $id, 'sgu_apod_local_media', $upload['url'] );

                            // ugg... there was
                            } else {

                                // log it
                                error_log( sprintf( "There was an issue pulling: %s", $orig_media ) );

                                // now log the full error message
                                error_log( $response -> get_error_message( ) );

                            }

                        // yeah, it already exists, so don't do anything
                        }

                    }

                }

            }

        }

    }

}
