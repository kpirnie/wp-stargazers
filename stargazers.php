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
    
}, 999 );

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
