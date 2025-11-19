<?php
/** 
 * Admin Columns
 * 
 * This class will control the cpt admin columns
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_CPT_Admin_Cols' ) ) {

    /** 
     * Class SGU_CPT_Admin_Cols
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_CPT_Admin_Cols {

        /** 
         * init
         * 
         * Initilize the class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ): void {

            // now we can modify the admin columns
            $this -> admin_cols_cme_alerts( );

            // now we can modify the admin columns
            $this -> admin_cols_space_weather_alerts( );

            // now we can modify the admin columns
            $this -> admin_cols_geo_magnetic_alerts( );

            // now we can modify the admin columns
            $this -> admin_cols_solar_flare_alerts( );

            // setup the admin columns and permissions
            $this -> admin_cols_neos( );

            // setup the admin columns and permissions
            $this -> admin_cols_photo_journal( );
            
            // setup the admin columns and permissions
            $this -> admin_cols_apod( );

            // remove and format the links
            $this -> cpt_links( );

        }
        
        /** 
         * cpt_links
         * 
         * This method is utilized for removing the admin links from the admin side for specified post types
         * 
         * @since 8.0
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @param string $_pt The post type
         * 
         * @return void This method returns nothing
         * 
        */
        private function cpt_links( ) : void {

            // hold the cpts list
            $cpts = array( 'sgu_cme_alerts', 'sgu_sw_alerts', 'sgu_geo_alerts', 'sgu_sf_alerts', 'sgu_neo', 'sgu_journal', 'sgu_apod', );

            // remove the links from admin
            add_filter( 'post_row_actions', function( $acts, $post ) use ( $cpts ) {

                // make sure our post type matches that of the post
                if( in_array( $post -> post_type, $cpts ) ) {

                    // remove the items
                    unset( $acts['edit'] );
                    unset( $acts['view'] );
                    unset( $acts['inline hide-if-no-js'] );

                }

                // return the actions
                return $acts;

            }, 10, 2 );

            // add CSS to disable title link pointer
            add_action( 'admin_head', function( ) use ( $cpts ) {
                global $typenow;
                if( in_array( $typenow, $cpts ) ) {
                    echo '<style>.row-title{pointer-events:none;cursor:default;color:#000;}</style>';
                }
            } );

            // clean up
            unset( $cpts );

        }

        /** 
         * admin_cols_cme_alerts
         * 
         * This method is utilized for creating the CME Alerts CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_cme_alerts( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_cme_alerts_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

        }

        /** 
         * admin_cols_space_weather_alerts
         * 
         * This method is utilized for creating the Space Weather Alerts CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_space_weather_alerts( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_sw_alerts_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

        }

        /** 
         * admin_cols_geo_magnetic_alerts
         * 
         * This method is utilized for creating the Geo Magnetic Alerts CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_geo_magnetic_alerts( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_geo_alerts_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

        }

        /** 
         * admin_cols_solar_flare_alerts
         * 
         * This method is utilized for creating the Solar Flare Alerts CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_solar_flare_alerts( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_sf_alerts_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

        }

        /** 
         * admin_cols_neos
         * 
         * This method is utilized for creating the Near Earth Object CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_neos( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_neo_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

        }
        
        /** 
         * admin_cols_photo_journal
         * 
         * This method is utilized for creating the Photo Journal CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_photo_journal( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_journal_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'img' => __( 'Image', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

            // add the custom data to the columns
            add_action( 'manage_sgu_journal_posts_custom_column', function( $_col, $_pid ) {

                // if our column is the image column
                if( 'img' === $_col ) {

                    // get the image
                    $_img = get_post_meta( $_pid, 'sgu_journal_local_image', true );

                    // get the image id from the image url
                    $_img_id = attachment_url_to_postid( $_img );

                    // get the proper image
                    $_back_img = wp_get_attachment_image_src( $_img_id, 'thumbnail' );
                    
                    // check if the attachment is an array
                    if( is_array( $_back_img ) ) {

                        // it is, so set the background image accordingly
                        $_back_img = $_back_img[0];

                    } else {

                        // it's not, so set it to the meta image
                        $_back_img = $_img;

                    }

                    // write it out
                    echo '<img src="' . $_back_img . '" alt="' . __( get_the_title( $_pid ), 'sgup' ) . '" style="height:150px;" />';

                }

            }, 10, 2);

        }

        /** 
         * admin_cols_apod
         * 
         * This method is utilized for creating the APOD CPT's admin columns and managing it's permissions
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function admin_cols_apod( ) : void {

            // rework the columns
            add_filter( 'manage_sgu_apod_posts_columns', function( $_cols ) {

                // setup the columns we want
                $_cols = array(
                    'cb' => __( 'Select All', 'sgup' ),
                    'media' => __( 'Media', 'sgup' ),
                    'title' => __( 'Title', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

            // add the custom data to the columns
            add_action( 'manage_sgu_apod_posts_custom_column', function( $_col, $_pid ) {

                // if our column is the image column
                if( 'media' === $_col ) {

                    // get the media
                    $_media = get_post_meta( $_pid, 'sgu_apod_local_media', true );

                    // get the media type
                    $_media_type = get_post_meta( $_pid, 'sgu_apod_local_media_type', true );

                    // if it's an image
                    if( $_media_type == 'image' ) {

                        // write it out
                        echo '<img src="' . $_media . '" alt="' . __( get_the_title( $_pid ), 'sgu' ) . '" style="height:125px;" />';

                    } else {

                        // it's a video
                        echo '<object height="125" data="' . $_media . '"></object>';

                    }

                }

            }, 10, 2);

        }

    }
    
}
