<?php
/** 
 * Settings Class
 * 
 * This class controls the plugins settings
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Settings' ) ) {

    /** 
     * Class SGU_Settings
     * 
     * The primary theme class
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Settings {

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

            // add the primary settings page
            $this -> add_settings( );
        }


        private function add_settings( ): void {

            // setup the key
            $_settings_id = 'sgup_settings';

            // create the main options page
            SGU_FW::createOptions( $_settings_id, array(
                'menu_title' => __( 'SGU Settings', 'sgup' ),
                'menu_slug'  => $_settings_id,
                'menu_capability' => 'list_users',
                'menu_icon' => 'dashicons-star-filled',
                'admin_bar_menu_icon' => 'dashicons-star-filled',
                'menu_position' => 2,
                'show_in_network' => false,
                'show_reset_all' => false,
                'show_reset_section' => false,   
                'sticky_header' => false,  
                'ajax_save' => false,           
                'footer_text' => '<a href="https://kevinpirnie.com" target="_blank"><img src="https://cdn.kevp.cc/kp/kevinpirnie-logo-color.svg" alt="Kevin Pirnie: https://kevinpirnie.com" style="width:250px !important;" /></a>',
                'framework_title' => __( '', 'sgup' ),
                'footer_credit' => __( '', 'sgup' ),
            ) );

            // create a settings section
            SGU_FW::createSection( $_settings_id, 
                array(
                    'title'  => __( 'API Keys', 'sgup' ),
                    'fields' => $this -> api_settings( ),
                    'description' => __( 'You will need to apply for keys at each service in order to utilize them.', 'sgup' ),
                )
            );

        }


        private function api_settings() : array {

            // return an array of the fields needed
            return array(

                // apply to admin
                array(
                    'id' => 'adpi',
                    'type' => 'switcher',
                    'title' => __( 'Apply to Admin?', 'security-header-generator' ),
                    'desc' => __( 'This will attempt to apply all headers to the admin side of your site in addition to the front-end.', 'security-header-generator' ),
                    'default' => false,
                ),

            );

        }

    }
}