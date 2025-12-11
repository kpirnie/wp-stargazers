<?php
/** 
 * CPT Templates Class
 * 
 * This class handles custom templates for CPTs using WooCommerce-style approach
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_CPT_Templates' ) ) {

    /** 
     * Class SGU_CPT_Templates
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_CPT_Templates {

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

            // Hook into template system
            add_filter( 'template_include', [ $this, 'cpt_template_loader' ], 99 );

        }

        /** 
         * cpt_template_loader
         * 
         * Override templates for CPTs using template hierarchy
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string $template The current template path
         * 
         * @return string The modified template path
         * 
        */
        public function cpt_template_loader( string $template ) : string {

            // we'll need the global post
            global $post;

            // current post type
            $currrent_post_type = $post -> post_type;

            // hold an array of our CPTs that should have an archive and single post template
            // i'm actually going to use this array for more than just the CPTs
            $cpts = [
                'apod' => 'sgu_apod',
            ];

            // now loop them
            foreach( $cpts as $path => $cpt) {

                // if the current post type matches
                if( $cpt === $currrent_post_type ) {
                    
                    // Handle single for a single
                    if( is_singular( $cpt ) ) {
                        return $this -> get_template( "$path/single", $template );
                    }

                    // only load this if we're on the archive page for the CPT
                    if( is_post_type_archive( $cpt ) ) {
                        return $this -> get_template( "$path/archive", $template );
                    }

                    // default
                    return $template;

                }

            }

            // default return
            return $template;
        }

        /** 
         * get_template
         * 
         * Get the template file, checking theme first then plugin
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string $template_name The template name without extension
         * @param string $default_template The default template path
         * 
         * @return string The template path
         * 
        */
        private function get_template( string $template_name, string $default_template ) : string {
    
            // Check theme for override first
            $theme_template = locate_template( [
                "templates/{$template_name}.php",
                "sgu/{$template_name}.php",
                "stargazers/{$template_name}.php",
            ] );          
            if( $theme_template ) {
                return $theme_template;
            }
            
            // Use plugin template if it exists
            $plugin_template = SGUP_PATH . "/templates/{$template_name}.php";
            if( file_exists( $plugin_template ) ) { 
                return $plugin_template;
            }
            
            // if we made it here, none of the ones above exist and we need to use the default
            return $default_template;
        }

    }

}
