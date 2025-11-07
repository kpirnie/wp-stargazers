<?php

// We don't want to allow direct access to this
defined( 'ABSPATH' ) OR die( 'No direct script access allowed' );

/*
Plugin Name: US Star Gazers
Description: generates all the functionality needed to display the weather and space info for the plugin
Plugin URI: https://kevinpirnie.com
Author: Kevin C. Pirnie
Author URI: https://kevinpirnie.com/
Requires at least: 6.0.9
Requires PHP: 8.1
Version: 0.0.1
Network: false
Text Domain: sgu-plug
License: MIT
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// setup the full page to this plugin
define( 'SGUP_PATH', dirname( __FILE__ ) );

// setup the directory name
define( 'SGUP_DIRNAME', basename( dirname( __FILE__ ) ) );

// setup the primary plugin file name
define( 'SGUP_FILENAME', basename( __FILE__ ) );

// At our earliest point, fire this up
add_action( 'plugins_loaded', function( ) {

	// include our autoloader
    include_once SGUP_PATH . '/vendor/autoload.php';

    // we can fire up the rest of the theme's functionality now
    $sgu = new SGUP( );

    // initialize the theme
    $sgu -> init( );

    // clean up
    unset( $sgu );
    
}, 999 );
