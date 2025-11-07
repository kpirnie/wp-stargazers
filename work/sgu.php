<?php
/** 
 * Plugin Class
 * 
 * This is the primary plugin class file. It will be responsible for pulling together everything for us to use
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGUP' ) ) {

    /** 
     * Class SGUP
     * 
     * The primary theme class
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGUP {

        // fire us up
        public function __construct( ) {


        }

        /** 
         * init
         * 
         * Initilize the plugin
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ) : void {

            // hold our classes and the public method we'll be using
            $plugin_classes = array(
                //[ 'class' => '', 'method' => ''],
            );

            // loop over each item
            foreach ( $plugin_classes as $item ) {

                // make sure the class actually exists
                if ( class_exists( $item['class'] ) ) {
                    
                    // fire it up
                    $instance = new $item['class']( );
                    
                    // fire up the class initializers
                    $instance -> init( );
                    
                    // now clean up the instance
                    unset( $instance );

                }

            }

            // now we can clean up the class array
            unset( $theme_classes );

        }

    }

}