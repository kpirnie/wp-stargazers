<?php
/** 
 * CPT Settings Class
 * 
 * This class will control the cpts settings
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_CPT_Settings' ) ) {

    /** 
     * Class SGU_CPT_Settings
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_CPT_Settings {

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

            // hook into the field framework's admin init
            add_action( 'cmb2_admin_init', function( ) {

                // add cme settings
                $this -> add_cme_settings( );

                // add solar flare settings
                $this -> add_flare_settings( );

                // add geomagnetic settings
                $this -> add_geomag_settings( );

                // add spaceweather settings
                $this -> add_sw_settings( );

                // add neo settings
                $this -> add_neo_settings( );

                // add the journal settings
                $this -> add_journal_settings( );

                // add the apod settings
                $this -> add_apod_settings( );

            } );

        }

        /** 
         * add_cme_settings
         * 
         * Add in the CME settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_cme_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_cme_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers CME Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_cme_alerts', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_cme_api_endpoint',
                    'type'    => 'text_url',
                ) 
            );

            // the api keys
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'API Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_cme_api_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                ) 
            );

        }

        /** 
         * add_flare_settings
         * 
         * Add in the Solar Flare settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_flare_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_flare_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Solar Flare Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_sf_alerts', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_flare_api_endpoint',
                    'type'    => 'text_url',
                ) 
            );

            // should we use CME keys?
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Use CME Keys?', 'sgup' ),
                    'desc'    => esc_html__( 'Should we use the same keys you set for the CME\'s?', 'sgup' ),
                    'id'      => 'sgup_flare_use_cme',
                    'type'    => 'checkbox',
                ) 
            );

            // the api keys
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'API Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_flare_api_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                    'before_row' => '<div data-conditional-id="sgup_flare_use_cme" data-conditional-value="off">',
                    'after_row' => '</div>',
                ) 
            );

        }

        /** 
         * add_geomag_settings
         * 
         * Add in the GeoMagnetic settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_geomag_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_geomag_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Geo-Magnetic Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_geo_alerts', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://services.swpc.noaa.gov/text/', 'sgup' ),
                    'id'      => 'sgup_geomag_endpoint',
                    'type'    => 'text_url',
                ) 
            );

        }

        /** 
         * add_sw_settings
         * 
         * Add in the Space Weather settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_sw_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_sw_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Space Weather Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_sw_alerts', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://services.swpc.noaa.gov/products/', 'sgup' ),
                    'id'      => 'sgup_sw_endpoint',
                    'type'    => 'text_url',
                ) 
            );
            
        }

        /** 
         * add_neo_settings
         * 
         * Add in the Near Earth Object settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_neo_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_neo_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Near Earth Object Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_neo', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_neo_endpoint',
                    'type'    => 'text_url',
                ) 
            );

            // should we use CME keys?
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Use CME Keys?', 'sgup' ),
                    'desc'    => esc_html__( 'Should we use the same keys you set for the CME\'s?', 'sgup' ),
                    'id'      => 'sgup_neo_cme',
                    'type'    => 'checkbox',
                ) 
            );

            // the api keys
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'API Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_neo_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                    'before_row' => '<div data-conditional-id="sgup_neo_cme" data-conditional-value="off">',
                    'after_row' => '</div>',
                ) 
            );

        }

        /** 
         * add_journal_settings
         * 
         * Add in the Photo Journal settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_journal_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_journal_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Photo Journal Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_journal', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // create the journal group
            $j_group = $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'RSS Feeds', 'sgup' ),
                    'desc'    => esc_html__( 'Please see here to retrieve the feeds you want to utilize: https://science.nasa.gov/photojournal/rss-feeds/', 'sgup' ),
                    'id'      => 'sgup_journal_feeds',
                    'type'    => 'group',
                    'options'     => array(
                        'group_title'       => __( 'Entry {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add a Feed', 'sgup' ),
                        'remove_button'     => __( 'Remove Feed', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true,
                    )
                ) 
            );

            // lets add the feed type field
            $cmes -> add_group_field( $j_group,
                array(
                    'name'    => esc_html__( 'Type', 'sgup' ),
                    'id'      => 'sgup_journal_type',
                    'type'    => 'text_small',
                    'classes' => 'col-1',
                ) 
            );

            // now we need the URL field            
            $cmes -> add_group_field( $j_group,
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'id'      => 'sgup_journal_feed',
                    'type'    => 'text_url',
                    'classes' => 'col-2',
                ) 
            );

            // hack in a little css
            add_action( 'admin_head', function() {
                ?>
                <style>
                    .col-1{width:20%; float:left;}
                    .col-2{width:79%;float:left;}
                </style>
                <?php
            } );

        }


        private function add_apod_settings( ) : void {

            // setup the cme settings ID
            $cme_id = 'sgup_apod_settings';

            // the apis options page
            $cmes = new_cmb2_box( array(
                'id'           => $cme_id,
                'title'        => esc_html__( 'US Stargazers Astronomy Photo of the Day Settings', 'sgup' ),
                'object_types' => array( 'options-page' ),
                'option_key'      => $cme_id, // The option key and admin menu page slug.
                'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                'menu_title'      => esc_html__( 'Settings', 'sgup' ), // Falls back to 'title' (above).
                'parent_slug'     => 'edit.php?post_type=sgu_apod', // Make options page a submenu item of the themes menu.
                'capability'      => 'list_users', // Cap required to view options-page.
                // 'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                'save_button'     => esc_html__( 'Save the Settings', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                // 'message_cb'      => 'yourprefix_options_page_message_callback',
            ) );

            // the endpoint
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Endpoint', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your endpoint: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_apod_endpoint',
                    'type'    => 'text_url',
                ) 
            );

            // should we use CME keys?
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'Use CME Keys?', 'sgup' ),
                    'desc'    => esc_html__( 'Should we use the same keys you set for the CME\'s?', 'sgup' ),
                    'id'      => 'sgup_apod_cme',
                    'type'    => 'checkbox',
                ) 
            );

            // the api keys
            $cmes -> add_field( 
                array(
                    'name'    => esc_html__( 'API Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_apod_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                    'before_row' => '<div data-conditional-id="sgup_apod_cme" data-conditional-value="off">',
                    'after_row' => '</div>',
                ) 
            );
            
        }

    }

}
