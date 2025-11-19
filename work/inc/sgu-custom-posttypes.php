<?php
/** 
 * SPT Class
 * 
 * This class will control the cpts
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Custom_PostTypes' ) ) {

    /** 
     * Class SGU_Custom_PostTypes
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Custom_PostTypes {

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

            // create the cme alerts post type
            $this -> create_cme_alerts( );

            // create the solar flare alert post type
            $this -> create_solar_flare_alerts( );

            // create the geomagnetic alert post type
            $this -> create_geo_magnetic_alerts( );

            // space weather alerts
            $this -> create_space_weather_alerts( );

            // create our near earth objects post type
            $this -> create_neos( );

            // create our photo journals
            $this -> create_photo_journals( );

            // create our astronomy photos of the day
            $this -> create_apods( );

            // inject in some css
            add_action( 'admin_head', function( ) {
                ?>
                <style type="text/css">
                    .check-column{width:2% !important;}
                    .column-media, .column-img, .column-title{width:20% !important;}
                </style>
                <?php
            } );

        }

        /** 
         * create_cme_alerts
         * 
         * This method is utilized for creating the CME Alerts CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_cme_alerts( ) : void {

            // near earth objects
            $labels = array( 'name' => __( 'CME Alerts', 'sgup' ), 
                'singular_name' => __( 'Alert', 'sgup' ), 
                'menu_name' => __( 'CME Alerts', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor' ), 
                'hierarchical' => false, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-warning', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_cme_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'cme-alerts' ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_cme_alerts', $args );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // now we can modify the admin columns
            $this -> admin_cols_cme_alerts( );

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

            // modify the CPT links
            $this -> cpt_links( 'sgu_cme_alerts' );

        }

        /** 
         * create_space_weather_alerts
         * 
         * This method is utilized for creating the Space Weather Alerts CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_space_weather_alerts( ) : void {

            // near earth objects
            $labels = array( 'name' => __( 'Space Weather Alerts', 'sgup' ), 
                'singular_name' => __( 'Alert', 'sgup' ), 
                'menu_name' => __( 'Space Weather Alerts', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor' ), 
                'hierarchical' => false, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-warning', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_sw_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'space-weather-alerts' ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_sw_alerts', $args );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // now we can modify the admin columns
            $this -> admin_cols_space_weather_alerts( );
            
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

            // modify the CPT links
            $this -> cpt_links( 'sgu_sw_alerts' );

        }

        /** 
         * create_geo_magnetic_alerts
         * 
         * This method is utilized for creating the Geo Magnetic Alerts CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_geo_magnetic_alerts( ) : void {

            // near earth objects
            $labels = array( 'name' => __( 'Geo Magnetic Alerts', 'sgup' ), 
                'singular_name' => __( 'Alert', 'sgup' ), 
                'menu_name' => __( 'Geo Magnetic Alerts', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor' ), 
                'hierarchical' => false, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-warning', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_geo_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'geo-magnetic-alerts' ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_geo_alerts', $args );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // now we can modify the admin columns
            $this -> admin_cols_geo_magnetic_alerts( );


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

            // modify the CPT links
            $this -> cpt_links( 'sgu_geo_alerts' );

        }

        /** 
         * create_solar_flare_alerts
         * 
         * This method is utilized for creating the Solar Flare Alerts CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_solar_flare_alerts( ) : void {

            // near earth objects
            $labels = array( 'name' => __( 'Solar Flare Alerts', 'sgup' ), 
                'singular_name' => __( 'Alert', 'sgup' ), 
                'menu_name' => __( 'Solar Flare Alerts', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor' ), 
                'hierarchical' => false, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-warning', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_sf_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'solar-flare-alerts' ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_sf_alerts', $args );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // now we can modify the admin columns
            $this -> admin_cols_solar_flare_alerts( );

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

            // modify the CPT links
            $this -> cpt_links( 'sgu_sf_alerts' );

        }

        /** 
         * create_neos
         * 
         * This method is utilized for creating the Near Earth Objects CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_neos( ) : void {

            // near earth objects
            $labels = array( 'name' => __( 'NASA Near Earth Objects', 'sgup' ), 
                'singular_name' => __( 'NEO', 'sgup' ), 
                'menu_name' => __( 'NASA NEOs', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor' ), 
                'hierarchical' => false, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-rest-api', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_neo', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'near-earth-objects' ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_neo', $args );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // setup the admin columns and permissions
            $this -> admin_cols_neos( );

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
                    'haz' => __( 'Hazardous', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

            // add the custom data to the columns
            add_action( 'manage_sgu_neo_posts_custom_column', function( $_col, $_pid ) {

                // if our column is the Hazardous column
                if( 'haz' === $_col ) {

                    // get the Hazardous
                    $_haz = get_post_meta( $_pid, 'sgu_neo_hazardous', true );
                    
                    // write it out
                    _e( $_haz, 'sgup' );

                }

            }, 10, 2);

            // add the sorting for the category
            add_filter( 'manage_edit-sgu_neo_sortable_columns', function( $_cols ) {

                // add the category to the column list
                $_cols['haz'] = __( 'Hazardous', 'sgup' );

                // return them
                return $_cols;            

            } );

            // we're going to add a new filter dropdown
            add_action( 'admin_init', function() {

                // get the screen that we are on
                $_screen = get_current_screen( );
    
                // make sure we're only in the alerts admin
                if( $_screen -> id == 'edit-sgu_neo' ) {
                    
                    // hold our hazardous
                    $_cats = array(
                        __( 'Yes', 'sgup' ) => true,
                    );
                    ?>
                    <select name="hazardous">
                        <option value="">All Harzards</option>
                        <?php
                            
                            // hold the current selection
                            $current_v = isset( $_GET['hazardous'] ) ? $_GET['hazardous'] : '';
                            
                            // loop over the list of hazardous
                            foreach ( $_cats as $label => $value ) {

                                // print out the options
                                printf
                                    (
                                        '<option value="%s"%s>%s</option>',
                                        $value,
                                        $value == $current_v? ' selected="selected"':'',
                                        $label
                                    );
                            }
                        ?>
                    </select>
                    <?php
                    
                }

            } );

            // we'll need to modify the query run
            add_action( 'admin_init', function( $_qry ) {

                // make sure this is only in admin
                if( ! is_admin( ) ) {
                    
                    // it's not, so just return
                    return;
                }

                // get the screen that we are on
                $_screen = get_current_screen( );
    
                // make sure we're only in the alerts admin
                if( $_screen -> id == 'edit-sgu_neo' ) {

                    // make sure we're on the right post type, admin, edit page, and have a filter
                    if ( isset( $_GET['hazardous']) && $_GET['hazardous'] != '') {
                        
                        // append a meta query
                        $_qry -> query_vars['meta_key'] = 'sgu_neo_hazardous';
                        $_qry -> query_vars['meta_value'] = sanitize_text_field( $_GET['hazardous'] );
                    }

                }

            } );

            // modify the CPT links
            $this -> cpt_links( 'sgu_neo' );

        }

        /** 
         * create_photo_journals
         * 
         * This method is utilized for creating the photo journals CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_photo_journals( ) : void {

            // photo journal
            $labels = array( 'name' => __( 'NASA Photo Journal', 'sgup' ), 
                'singular_name' => __( 'Photo', 'sgup' ), 
                'menu_name' => __( 'NASA Journal', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor', 'excerpt' ), 
                'hierarchical' => true, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-images-alt2', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_journal', 
                'show_in_rest' => false,
                'capability_type' => 'page',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'astronomy-information/nasa-photo-journal', 'with_front' => false ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_journal', $args );

            // we have to create a rewrite rule here, otherwise the pagination links do not work
            add_rewrite_rule(
                '^astronomy-information/nasa-photo-journal/page/(\d+)/?$',
                'index.php?pagename=astronomy-information/nasa-photo-journal&paged=$matches[1]',
                'top'
            );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // setup the admin columns and permissions
            $this -> admin_cols_photo_journal( );

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
                    'cat' => __( 'Category', 'sgup' ),
                    'date' => __( 'Date', 'sgup' ),
                );

                // reutrn the columns
                return $_cols;

            } );

            // add the custom data to the columns
            add_action( 'manage_sgu_journal_posts_custom_column', function( $_col, $_pid ) {

                // if our column is the category column
                if( 'cat' === $_col ) {

                    // get the Hazardous
                    $_cat = get_post_meta( $_pid, 'sgu_journal_category', true );
                    
                    // write it out
                    _e( $_cat, 'sgup' );

                }

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

            // add the sorting for the category
            add_filter( 'manage_edit-sgu_journal_sortable_columns', function( $_cols ) {

                // add the category to the column list
                $_cols['cat'] = __( 'Categories', 'sgup' );

                // return them
                return $_cols;            

            } );

            // we're going to add a new filter dropdown
            add_action( 'admin_init', function( ) {

                // get the screen that we are on
                $_screen = get_current_screen( );
    
                // make sure we're only in the alerts admin
                if( $_screen -> id == 'edit-sgu_journal' ) {
                    
                    // we need our db global
                    global $wpdb;

                    // get our results
                    $_res = $wpdb -> get_col( $wpdb -> prepare( "
                            SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
                            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                            WHERE pm.meta_key = '%s' 
                            AND p.post_status = 'publish'
                            ORDER BY pm.meta_value", 
                            'sgu_journal_category'
                        ) 
                    );

                    ?>
                    <select name="categories">
                        <option value="">All Categories</option>
                        <?php
                            
                            // hold the current selection
                            $current_v = isset( $_GET['categories'] ) ? $_GET['categories'] : '';
                            
                            // loop over the list of hazardous
                            foreach ( $_res as $_result ) {

                                // format the value
                                $_val = sanitize_title( $_result );

                                // print out the options
                                printf
                                    (
                                        '<option value="%s"%s>%s</option>',
                                        $_val,
                                        $_val == $current_v? ' selected="selected"':'',
                                        $_result
                                    );
                            }
                        ?>
                    </select>
                    <?php

                    // clean up the resultset
                    unset( $_res );
                    
                }

            } );

            // we'll need to modify the query run
            add_action( 'admin_init', function( $_qry ) {

                // make sure this is only in admin
                if( ! is_admin( ) ) {
                    
                    // it's not, so just return
                    return;
                }

                // get the screen that we are on
                $_screen = get_current_screen( );
    
                // make sure we're only in the alerts admin
                if( $_screen && $_screen -> id == 'edit-sgu_journal' ) {

                    // make sure we're on the right post type, admin, edit page, and have a filter
                    if ( isset( $_GET['categories']) && $_GET['categories'] != '') {
                        
                        // append a meta query
                        $_qry -> query_vars['meta_key'] = 'sgu_journal_category';
                        $_qry -> query_vars['meta_value'] = sanitize_text_field( $_GET['categories'] );
                    }

                }

            } );

            // modify the CPT links
            $this -> cpt_links( 'sgu_journal' );

        }

        /** 
         * create_apods
         * 
         * This method is utilized for creating the Astronomy Photo of the Day CPT
         * 
         * @since 8.0
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Star Gazers
         * 
         * @return void This method returns nothing
         * 
        */
        private function create_apods( ) : void {

            // photo journal
            $labels = array( 'name' => __( 'NASA Astronomy Photo of the Day', 'sgup' ), 
                'singular_name' => __( 'Photo of the Day', 'sgup' ), 
                'menu_name' => __( 'NASA APOD', 'sgup' ), 
            );

            // setup the arguments for the post type
            $args = array( 'label' => '', 
                'labels' => $labels, 
                'supports' => array( 'title', 'editor', 'thumbnail' ), 
                'hierarchical' => true, 
                'public' => false, 
                'show_ui' => true, 
                'show_in_menu' => true, 
                'menu_position' => 5, 
                'menu_icon'  => 'dashicons-images-alt2', 
                'show_in_admin_bar' => false, 
                'show_in_nav_menus' => false, 
                'can_export' => true, 
                'has_archive' => false, 
                'exclude_from_search' => false, 
                'publicly_queryable' => true, 
                'query_var' => 'sgu_apod', 
                'show_in_rest' => false,
                'capability_type' => 'page',
                'delete_with_user' => false,
                'rewrite' => array( 'slug' => 'astronomy-information/nasas-astronomy-photo-of-the-day', 'with_front' => false ),
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_apod', $args );

            // we have to create a rewrite rule here, otherwise the pagination links do not work
            add_rewrite_rule(
                '^astronomy-information/nasas-astronomy-photo-of-the-day/page/(\d+)/?$',
                'index.php?pagename=astronomy-information/nasas-astronomy-photo-of-the-day&paged=$matches[1]',
                'top'
            );

            // flush the rewrites so we can have permalinks
            flush_rewrite_rules( );

            // setup the admin columns and permissions
            $this -> admin_cols_apod( );

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

            // modify the CPT links
            $this -> cpt_links( 'sgu_apod' );

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
        private function cpt_links( $_pt ) : void {

            // remove the links from admin
            add_filter( 'post_row_actions', function( $_acts, $_post ) use( $_pt ) {

                // make sure our post type matches that of the post
                if( $_post -> post_type == $_pt ) {

                    // remove the items
                    unset( $_acts['edit'] );
                    unset( $_acts['view'] );
                    //unset( $_acts['trash'] ); // we do still want to be able to trash them
                    unset( $_acts['inline hide-if-no-js'] );

                }

                // return the actions
                return $_acts;

            }, 10, 2 );
            
            // swap the link for a #
            add_filter( 'get_edit_post_link', function( $_url, $_id, $context ) use( $_pt ) { 
                
                // if it's the alerts post type
                if( get_post_type( $_id ) == $_pt ) {

                    // get the frontend permalink to this item
                    $_link = get_the_permalink( $_id );

                    // return the #
                    return $_link; 

                }

                // default to the url
                return $_url;
            
            }, 10, 3 );

            add_filter( 'edit_post_link', function( $link, $post_id, $text ) {

                $link = str_replace( '<a ', '<a target="_blank" ', $link );

                return $link;
            
            }, 10, 3 );

        }

    }

}
