<?php
/** 
 * SPT Class
 * 
 * This class will control the cpts
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_CPTs' ) ) {

    /** 
     * Class SGU_CPTs
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_CPTs {

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

            // register all custom post types
            $this -> register_all_cpts( );

            // inject admin styling for column widths
            add_action( 'admin_head', function( ) {
                ?>
                <style type="text/css">
                    .check-column{width:2% !important;}
                    .column-media, .column-img, .column-title{width:20% !important;}
                </style>
                <?php
            } );

        }

        /** 
         * register_all_cpts
         * 
         * Register all custom post types using a consolidated definition array
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void This method returns nothing
         * 
        */
        private function register_all_cpts( ) : void {

            // get the journal archive slug
            $journal_archive_slug = ( function( ) : string {
                $page_id = SGU_Static::get_sgu_option( 'sgup_journal_settings' ) -> sgup_journal_archive ?: 0;
                return get_page_uri( $page_id );
            } )( );

            // get the apod archive slug
            $apod_archive_slug = ( function( ) : string {
                $page_id = SGU_Static::get_sgu_option( 'sgup_apod_settings' ) -> sgup_apod_archive ?: 0;
                return get_page_uri( $page_id );
            } )( );

            // define all CPT configurations with their unique properties
            $cpt_definitions = [
                'sgu_cme_alerts' => [
                    'labels' => [ 
                        'name' => __( 'CME Alerts', 'sgup' ), 
                        'singular_name' => __( 'Alert', 'sgup' ), 
                        'menu_name' => __( 'CME Alerts', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-warning',
                    'supports' => ['title', 'editor'],
                    'menu_position' => 5,
                ],
                'sgu_sf_alerts' => [
                    'labels' => [ 
                        'name' => __( 'Solar Flare Alerts', 'sgup' ), 
                        'singular_name' => __( 'Alert', 'sgup' ), 
                        'menu_name' => __( 'Solar Flare Alerts', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-warning',
                    'supports' => ['title', 'editor'],
                    'menu_position' => 5,
                ],
                'sgu_geo_alerts' => [
                    'labels' => [ 
                        'name' => __( 'Geo Magnetic Alerts', 'sgup' ), 
                        'singular_name' => __( 'Alert', 'sgup' ), 
                        'menu_name' => __( 'Geo Magnetic Alerts', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-warning',
                    'supports' => ['title', 'editor'],
                    'menu_position' => 5,
                ],
                'sgu_sw_alerts' => [
                    'labels' => [ 
                        'name' => __( 'Space Weather Alerts', 'sgup' ), 
                        'singular_name' => __( 'Alert', 'sgup' ), 
                        'menu_name' => __( 'Space Weather Alerts', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-warning',
                    'supports' => ['title', 'editor'],
                    'menu_position' => 5,
                ],
                'sgu_neo' => [
                    'labels' => [ 
                        'name' => __( 'NASA Near Earth Objects', 'sgup' ), 
                        'singular_name' => __( 'NEO', 'sgup' ), 
                        'menu_name' => __( 'NASA NEOs', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-rest-api',
                    'supports' => ['title', 'editor'],
                    'menu_position' => 5,
                ],

                'sgu_journal' => [
                    'labels' => [ 
                        'name' => __( 'NASA Photo Journal', 'sgup' ), 
                        'singular_name' => __( 'Photo', 'sgup' ), 
                        'menu_name' => __( 'NASA Journal', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-images-alt2',
                    'supports' => ['title', 'editor', 'excerpt'],
                    'menu_position' => 5,
                    'publicly_queryable' => true,
                    'has_archive' => $journal_archive_slug,
                    'public' => true,
                    'show_in_nav_menus' => true,
                    'rewrite' => ['slug' => "$journal_archive_slug/journal", 'with_front' => false],
                ],

                'sgu_apod' => [
                    'labels' => [ 
                        'name' => __( 'NASA Astronomy Photo of the Day', 'sgup' ), 
                        'singular_name' => __( 'Photo of the Day', 'sgup' ), 
                        'menu_name' => __( 'NASA APOD', 'sgup' ), 
                    ],
                    'menu_icon' => 'dashicons-images-alt2',
                    'supports' => ['title', 'editor', 'thumbnail'],
                    'menu_position' => 5,
                    'publicly_queryable' => true,
                    'has_archive' => $apod_archive_slug,
                    'public' => true, // ADD THIS
                    'show_in_nav_menus' => true, // CHANGE THIS 
                    'rewrite' => false,
                    'rewrite' => ['slug' => "$apod_archive_slug/apod", 'with_front' => false],
                ],
            ];

            // set default arguments that apply to all CPTs unless overridden
            $defaults = [
                'hierarchical' => false,
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_admin_bar' => false,
                'show_in_nav_menus' => false,
                'can_export' => true,
                'has_archive' => false,
                'exclude_from_search' => false,
                'show_in_rest' => false,
                'capability_type' => 'post',
                'delete_with_user' => false,
                'rewrite' => false,
                'publicly_queryable' => false,
                'map_meta_cap' => true,
                'capabilities' => ['create_posts' => 'do_not_allow'], // prevent manual creation
            ];

            // loop through each CPT definition and register it
            foreach ( $cpt_definitions as $post_type => $args ) {

                // check if this CPT needs a custom rewrite rule for pagination
                $has_rewrite_rule = $args['has_rewrite_rule'] ?? false;

                // remove our custom flag before merging with defaults
                unset( $args['has_rewrite_rule'] );
                
                // merge CPT-specific args with defaults (CPT args override defaults)
                $args = array_merge( $defaults, $args );

                // set the query var to match the post type name
                $args['query_var'] = $post_type;
                
                // register this custom post type
                register_post_type( $post_type, $args );

            }

            // clean up the definitions array
            unset( $cpt_definitions, $defaults );

        }
        
    }

}
