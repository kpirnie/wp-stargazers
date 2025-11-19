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

if( ! class_exists( 'SGU_CPTs' ) ) {

    /** 
     * Class SGU_CPTs
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_CPTs {

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
                'query_var' => 'sgu_cme_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_cme_alerts', $args );

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
                'query_var' => 'sgu_sw_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_sw_alerts', $args );
            
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
                'query_var' => 'sgu_geo_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_geo_alerts', $args );

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
                'query_var' => 'sgu_sf_alerts', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_sf_alerts', $args );

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
                'query_var' => 'sgu_neo', 
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => array(
                    'create_posts' => 'do_not_allow'
                )
            );

            // register the post type
            register_post_type( 'sgu_neo', $args );

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

        }

    }

}
