<?php
/** 
 * Hero Slider Shortcode Class
 * 
 * This class will control the hero slider shortcode and rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Hero_Slider_Shortcode' ) ) {

    /** 
     * Class SGU_Hero_Slider_Shortcode
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Hero_Slider_Shortcode {

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

            // add the hero slider shortcode
            add_shortcode( 'sgup_hero_slider', [ $this, 'render_hero_slider' ] );

        }

        /** 
         * render_hero_slider
         * 
         * Render the hero slider using template
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param array $atts Shortcode attributes
         * 
         * @return string The rendered HTML
         * 
        */
        public function render_hero_slider( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                // Add your default attributes here
            ], $atts, 'sgup_hero_slider' );

            // Check theme for override first
            $theme_template = locate_template( [
                'sgu-hero-slider.php',
                'sgu/hero-slider.php',
                "templates/sgu-hero-slider.php",
                "partials/sgu-hero-slider.php",
            ] );

            if( $theme_template ) {
                $template = $theme_template;
            } else {
                // Use plugin template
                $template = SGUP_PATH . '/templates/shortcodes/hero-slider.php';
            }

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start();

            // Extract attributes to variables
            extract( $atts );

            // Include the template
            include $template;

            // Return the buffered content
            return ob_get_clean();

        }

    }

}