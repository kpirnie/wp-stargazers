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

        // hold the internals
        private ?SGU_Space_Data $space_data = null;

        // fire us up
        public function __construct( ) {
            $this -> space_data = new SGU_Space_Data( );
        }

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
            add_shortcode( 'sgup_astro_menu', [ $this, 'add_astro_nav' ] );

            // add the neos content
            add_shortcode( 'sgup_neos', [ $this, 'add_neos' ] );

            // add the apod
            add_shortcode( 'sgup_latest_apod', [ $this, 'add_latest_apod' ] );

        }

        /** 
         * render_template
         * 
         * Consolidated template rendering with theme override support
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string $template_path Relative path to template file
         * @param array $data Data to extract as variables in template
         * 
         * @return string Rendered template content
         * 
        */
        private function render_template( string $template_path, array $data ) : string {

            // Check theme for override first
            $theme_template = locate_template( [
                "templates/$template_path",
                "sgu/$template_path",
                "stargazers/$template_path",
            ] );

            // Use theme template if found, otherwise plugin template
            $template = $theme_template ?: SGUP_PATH . "/templates/$template_path";

            // Check if template exists
            if( ! file_exists( $template ) ) {
                return '';
            }

            // Start output buffering
            ob_start( );

            // Extract attributes to variables
            extract( $data );

            // Include the template
            include $template;

            // Return the buffered content
            return ob_get_clean( );
        }

        /** 
         * add_astro_nav
         * 
         * Render the astronomy menu
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_astro_nav( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'which' => 'astro-menu',
                'is_inline' => false,
            ], $atts, 'sgup_astro_menu' );
            
            $menu_slug = sanitize_text_field( $atts['which'] );
            $is_inline = filter_var( $atts['is_inline'], FILTER_VALIDATE_BOOLEAN );
            
            // Create cache key for this specific menu
            $cache_key = "sgup_astro_menu_{$menu_slug}";
            
            // Try to get cached menu
            $cached = wp_cache_get( $cache_key, 'sgup_menus' );
            if( $cached !== false ) {
                return $cached;
            }

            // configure the menu from our themes menus
            $alert_nav_conf = [
                'menu' => $menu_slug,
                'items_wrap' => '%3$s',
                'depth' => 1,
                'container' => null,
                'echo' => false,
                'menu_class' => '',
            ];

            // get the menu
            $the_menu = wp_nav_menu( $alert_nav_conf );

            // Render the template
            $output = $this -> render_template( 'menu.php', [
                'the_menu' => $the_menu,
                'is_inline' => $is_inline
            ] );

            // Cache for 12 hours (menus don't change often)
            wp_cache_set( $cache_key, $output, 'sgup_menus', 12 * HOUR_IN_SECONDS );

            return $output;
        }

        /** 
         * add_neos
         * 
         * Render the Near Earth Objects
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_neos( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'show_paging' => false,
                'show_map' => false,
                'paging_location' => 'bottom',
                'per_page' => 6,
            ], $atts, 'sgup_neos' );

            // get the neos (reusing class instance)
            $neos = $this -> space_data -> get_neos( SGU_Static::safe_get_paged_var( ) ?: 1 );

            // Render the template
            return $this -> render_template( 'neos.php', [
                'show_paging' => filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN ),
                'max_pages' => $neos -> max_num_pages ?: 1,
                'per_page' => absint( $atts['per_page'] ) ?: 6,
                'paging_location' => sanitize_text_field( $atts['paging_location'] ),
                'paged' => SGU_Static::safe_get_paged_var( ) ?: 1,
                'show_map' => filter_var( $atts['show_map'], FILTER_VALIDATE_BOOLEAN ),
                'data' => $neos,
            ] );
        }

        /** 
         * add_latest_apod
         * 
         * Add an Astronomy Photo of the Day
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_latest_apod( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'title' => 'Astronomy Photo of the Day',
            ], $atts, 'sgup_apod' );

            // get the data we need (reusing class instance)
            $data = $this -> space_data -> get_apod( );

            // Early return if no data
            if( ! $data || empty( $data -> posts ) ) {
                return '';
            }

            // setup the post
            $post = $data -> posts[0];

            // Render the template
            return $this -> render_template( 'apod/home.php', [
                'id' => $post -> ID,
                'block_title' => esc_html( $atts['title'] ),
                'title' => $post -> post_title,
                'content' => $post -> post_content,
                'meta' => get_post_meta( $post -> ID )
            ] );
        }

    }

}