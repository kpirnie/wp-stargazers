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
                [ 'class' => 'SGU_Plugin', 'method' => null],
                [ 'class' => 'SGU_CPTs', 'method' => null],
                [ 'class' => 'SGU_CPT_Settings', 'method' => null],
                [ 'class' => 'SGU_CPT_Admin_Cols', 'method' => null],                
                [ 'class' => 'SGU_Settings', 'method' => null],
                [ 'class' => 'SGU_Alert_Shortcodes', 'method' => null],
                [ 'class' => 'SGU_Astro_Shortcodes', 'method' => null],
                
            );

            // loop over each item
            foreach ( $plugin_classes as $item ) {

                // make sure the class actually exists
                if ( class_exists( $item['class'] ) ) {
                    
                    // fire it up
                    $instance = new $item['class']( );

                    // if the method is empty
                    if( empty( $item['method'] ) ) {

                        // fire up the class initializers
                        $instance -> init( );

                    // otherwise
                    } else {

                        // fire up the class initializers
                        $instance -> {$item['method']}( );
                    }
                    
                    // now clean up the instance
                    unset( $instance );

                }

            }

            // now we can clean up the class array
            unset( $theme_classes );

        }

    }

}