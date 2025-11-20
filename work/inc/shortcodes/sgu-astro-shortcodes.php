<?php
/** 
 * Astronomy Shortcodes Class
 * 
 * This class will control the rest of the astronomy 
 * shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Astro_Shortcodes' ) ) {

    /** 
     * Class SGU_Astro_Shortcodes
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Astro_Shortcodes {

        /** 
         * init
         * 
         * Initialize the class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ): void { 

            // add the astronomy menu
            add_shortcode( 'sgup_astro_menu', [ $this, 'add_main_astro_nav' ] );

        }

        /** 
         * add_main_astro_nav
         * 
         * Render the astronomy menu
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_main_astro_nav( ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'which' => 'alert-menu',
            ], $atts, 'sgup_astro_menu' );
            
            // configure the menu from our themes menus
            $alert_nav_conf = [
                'menu' => sanitize_text_field( $atts['which'] ),
                'items_wrap' => '%3$s',
                'depth' => 1,
                'container' => null,
                'echo' => false,
                'menu_class' => '',
            ];

            // get the menu
            $the_menu = wp_nav_menu( $alert_nav_conf );

            // return the html string
            return <<<HTML
            <nav class="uk-navbar-container uk-navbar-transparent uk-margin-bottom uk-overflow-auto" uk-navbar>
                <div class="uk-navbar-center">
                    <ul class="uk-navbar-nav page-nav-divider">
                        $the_menu
                    </ul>
                </div>
            </nav>
            HTML;

        }

    }

}
