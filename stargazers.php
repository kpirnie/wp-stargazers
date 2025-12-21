<?php

// We don't want to allow direct access to this
defined( 'ABSPATH' ) OR die( 'No direct script access allowed' );

/*
Plugin Name: US Star Gazers
Description: generates all the functionality needed to display the weather and space info for the site
Plugin URI: https://kevinpirnie.com
Author: Kevin C. Pirnie
Author URI: https://kevinpirnie.com/
Requires at least: 6.0.9
Requires PHP: 8.1
Version: 0.0.1
Network: false
Text Domain: sgup
License: MIT
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// setup the full page to this plugin
define( 'SGUP_PATH', dirname( __FILE__ ) );

// setup the directory name
define( 'SGUP_DIRNAME', basename( dirname( __FILE__ ) ) );

// setup the primary plugin file name
define( 'SGUP_FILENAME', basename( __FILE__ ) );

// let's define our plugin URI
defined( 'SGUP_URI' ) || define( 'SGUP_URI', plugins_url( '', __FILE__ ) . '/' );

// fire us up on initialization
add_action( 'init', function( ) {

	// include our autoloader
    include_once SGUP_PATH . '/vendor/autoload.php';

    // we can fire up the rest of the theme's functionality now
    $sgu = new SGUP( );

    // initialize the theme
    $sgu -> init( );

    // clean up
    unset( $sgu );
    
}, 5 );

// hook into WP CLI initialization, if it's being utilized
if( defined( 'WP_CLI' ) ) {

    // hook into the cli initialization
    add_action( 'cli_init', function( ) : void {

        // include our autoloader
        include_once SGUP_PATH . '/vendor/autoload.php';

        // create sync commands
        WP_CLI::add_command( 'sgu sync data', ['SGU_Sync', 'sync_the_data'], 
            ['shortdesc' => 'This method syncs the remote data to WordPress.  We then utilize WP to display.'] );
        WP_CLI::add_command( 'sgu sync imagery', ['SGU_Sync', 'sync_the_imagery'], 
            ['shortdesc' => 'This method syncs the remote imagery to WordPress.  We then utilize WP to display.'] );
        WP_CLI::add_command( 'sgu sync both', ['SGU_Sync', 'sync_both'], 
            ['shortdesc' => 'This method syncs both the data and the imagery.  We then utilize WP to display.'] );
        WP_CLI::add_command( 'sgu sync cleanup', ['SGU_Sync', 'perform_cleanup'], 
            ['shortdesc' => 'This method cleans up the data.'] );

        

        // Historical sync commands
        

        WP_CLI::add_command( 'sgu historical apod', 'sgu_cli_historical_apod', [
            'shortdesc' => 'Sync historical APOD data from NASA (one-time bulk import)',
            'synopsis' => [
                [
                    'type' => 'assoc',
                    'name' => 'start',
                    'description' => 'Start date in Y-m-d format (default: 2015-01-01)',
                    'optional' => true,
                    'default' => '2015-01-01',
                ],
                [
                    'type' => 'assoc',
                    'name' => 'end',
                    'description' => 'End date in Y-m-d format (default: today)',
                    'optional' => true,
                ],
            ],
        ] );

        WP_CLI::add_command( 'sgu historical imagery', 'sgu_cli_historical_imagery', [
            'shortdesc' => 'Sync all missing APOD imagery (one-time bulk download)',
        ] );

        WP_CLI::add_command( 'sgu historical both', 'sgu_cli_historical_both', [
            'shortdesc' => 'Sync both historical APOD data and imagery (one-time bulk import)',
            'synopsis' => [
                [
                    'type' => 'assoc',
                    'name' => 'start',
                    'description' => 'Start date in Y-m-d format (default: 2015-01-01)',
                    'optional' => true,
                    'default' => '2015-01-01',
                ],
                [
                    'type' => 'assoc',
                    'name' => 'end',
                    'description' => 'End date in Y-m-d format (default: today)',
                    'optional' => true,
                ],
            ],
        ] );

    }, PHP_INT_MAX );

}

/**
 * CLI callback for historical APOD data sync
 */
function sgu_cli_historical_apod( $args, $assoc_args ) {
    $sync = new SGU_Historical_Sync( );
    $start = $assoc_args['start'] ?? '2015-01-01';
    $end = $assoc_args['end'] ?? null;
    $sync -> sync_historical_apod( $start, $end );
}

/**
 * CLI callback for historical imagery sync
 */
function sgu_cli_historical_imagery( $args, $assoc_args ) {
    $sync = new SGU_Historical_Sync( );
    $sync -> sync_historical_imagery( );
}

/**
 * CLI callback for both historical data and imagery
 */
function sgu_cli_historical_both( $args, $assoc_args ) {
    $sync = new SGU_Historical_Sync( );
    $start = $assoc_args['start'] ?? '2015-01-01';
    $end = $assoc_args['end'] ?? null;
    $sync -> sync_both_historical( $start, $end );
}

// fire this up in admin_init to inject it
add_action( 'admin_init', function( ) {

    /**
     * CMB2 Conditional Fields Handler
     */

    add_action('admin_footer', 'sgu_cmb2_conditional_fields');
    function sgu_cmb2_conditional_fields() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            function handleConditionals() {
                $('[data-conditional-id]').each(function() {
                    var $wrapper = $(this);
                    var targetId = $(this).data('conditional-id');
                    var targetValue = $(this).data('conditional-value');
                    var $target = $('#' + targetId);
                    
                    if (!$target.length) return;
                    
                    var shouldShow = false;
                    
                    if ($target.is(':checkbox')) {
                        shouldShow = (targetValue === 'on') ? $target.is(':checked') : !$target.is(':checked');
                    } else {
                        shouldShow = $target.val() == targetValue;
                    }
                    
                    shouldShow ? $wrapper.show() : $wrapper.hide();
                });
            }
            
            handleConditionals();
            $('.cmb2-wrap').on('change', 'input, select, textarea', handleConditionals);
        });
        </script>
        <?php
    }

}, 999 );