<?php
/** 
 * Settings Class
 * 
 * This class controls the plugins settings
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Settings' ) ) {

    /** 
     * Class SGU_Settings
     * 
     * The primary theme class
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Settings {

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

            // add the primary settings page
            $this -> add_settings( );
        }

        /** 
         * add_settings
         * 
         * Add our admin settings
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function add_settings( ): void {

            // hook into the field framework's admin init
            add_action( 'cmb2_admin_init', function( ) {

                // setup the keys
                $apis_id        = 'sgup_apis';
                $epoints_id     = 'sgup_endpoints';
                $pjournals_id   = 'sgup_photo_journals';

                // the apis options page
                $apis = new_cmb2_box( array(
                    'id'           => $apis_id,
                    'title'        => esc_html__( 'US Stargazers API Keys', 'sgup' ),
                    'object_types' => array( 'options-page' ),

                    'option_key'      => $apis_id, // The option key and admin menu page slug.
                    'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                    'menu_title'      => esc_html__( 'SGU Keys', 'sgup' ), // Falls back to 'title' (above).
                    // 'parent_slug'     => 'themes.php', // Make options page a submenu item of the themes menu.
                    'capability'      => 'list_users', // Cap required to view options-page.
                    'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                    // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                    // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                    'save_button'     => esc_html__( 'Save the Keys', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                    // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                    // 'message_cb'      => 'yourprefix_options_page_message_callback',
                ) );

                // the endpoints options page
                $epoints = new_cmb2_box( array(
                    'id'           => $epoints_id,
                    'title'        => esc_html__( 'US Stargazers Endpoints', 'sgup' ),
                    'object_types' => array( 'options-page' ),

                    'option_key'      => $epoints_id, // The option key and admin menu page slug.
                    'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                    'menu_title'      => esc_html__( 'SGU Endpoints', 'sgup' ), // Falls back to 'title' (above).
                    'parent_slug'     => $apis_id, // Make options page a submenu item of the themes menu.
                    'capability'      => 'list_users', // Cap required to view options-page.
                    'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                    // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                    // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                    'save_button'     => esc_html__( 'Save the Endpoints', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                    // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                    // 'message_cb'      => 'yourprefix_options_page_message_callback',
                ) );

                // the nasa photo journals rss feeds
                $pjournals = new_cmb2_box( array(
                    'id'           => $pjournals_id,
                    'title'        => esc_html__( 'US Stargazers Photo Journals', 'sgup' ),
                    'object_types' => array( 'options-page' ),
                    'option_key'      => $pjournals_id, // The option key and admin menu page slug.
                    'icon_url'        => 'dashicons-star-filled', // Menu icon. Only applicable if 'parent_slug' is left empty.
                    'menu_title'      => esc_html__( 'SGU Journals', 'sgup' ), // Falls back to 'title' (above).
                    'parent_slug'     => $apis_id, // Make options page a submenu item of the themes menu.
                    'capability'      => 'list_users', // Cap required to view options-page.
                    'position'        => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
                    // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
                    // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
                    'save_button'     => esc_html__( 'Save the Feeds', 'sgup' ), // The text for the options-page save button. Defaults to 'Save'.
                    // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
                    // 'message_cb'      => 'yourprefix_options_page_message_callback',
                ) );

                // add our fields
                $this -> api_key_fields( $apis );
                $this -> endpoint_fields( $epoints );
                $this -> photo_journal_fields( $pjournals );

            } );

        }

        /** 
         * api_key_fields
         * 
         * Add our api key fields
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function api_key_fields( \CMB2 $the_box ) : void {

            // add the nasa keys
            $the_box -> add_field( 
                array(
                    'name'    => esc_html__( 'NASA Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://api.nasa.gov/', 'sgup' ),
                    'id'      => 'sgup_nasa_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                ) 
            );

            // add the jwst keys
            $the_box -> add_field( 
                array(
                    'name'    => esc_html__( 'JWST Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://jwst-docs.stsci.edu/accessing-jwst-data', 'sgup' ),
                    'id'      => 'sgup_jwst_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                ) 
            );

            // add the openweather keys
            $the_box -> add_field( 
                array(
                    'name'    => esc_html__( 'OpenWeather Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://openweathermap.org/api', 'sgup' ),
                    'id'      => 'sgup_ow_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                ) 
            );

            // add the NOAA keys
            $the_box -> add_field( 
                array(
                    'name'    => esc_html__( 'NOAA Keys', 'sgup' ),
                    'desc'    => esc_html__( 'See here to get your API keys: https://www.weather.gov/documentation/services-web-api', 'sgup' ),
                    'id'      => 'sgup_noaa_keys',
                    'type'    => 'text',
                    'repeatable' => true,
                    'text' => array(
                        'add_row_text' => 'Add Another Key',
                    ),
                ) 
            );
        }

        /** 
         * endpoint_fields
         * 
         * Add our api endpoint fields
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function endpoint_fields( \CMB2 $the_box ) : void {

            // add the nasa endpoint groups
            $nasa_group = $the_box -> add_field( 
                array(
                    'id' => 'nasa_ep_group',
                    'type' => 'group',
                    'name' => esc_html__( 'NASA Endpoints', 'sgup' ),
                    'description' => esc_html__( 'See here to get your endpoints: https://api.nasa.gov/.', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'NASA Endpoint {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add Another Endpoint', 'sgup' ),
                        'remove_button'     => __( 'Remove Endpoint', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true, // true to have the groups closed by default
                    ),
                )
            );

            // nasa's endpoint fields
            $the_box -> add_group_field( $nasa_group, array(
                'id'      => 'sgup_nasa_endpoint_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box -> add_group_field( $nasa_group, array(
                'id'      => 'sgup_nasa_endpoint_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );

            // add the jwst endpoint groups
            $jwst_group = $the_box -> add_field( 
                array(
                    'id' => 'jwst_ep_group',
                    'type' => 'group',
                    'name' => esc_html__( 'JWST Endpoints', 'sgup' ),
                    'description' => esc_html__( 'See here to get your endpoints: https://jwst-docs.stsci.edu/accessing-jwst-data/mast-api-access#gsc.tab=0', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'JWST Endpoint {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add Another Endpoint', 'sgup' ),
                        'remove_button'     => __( 'Remove Endpoint', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true, // true to have the groups closed by default
                    ),
                )
            );

            // nasa's endpoint fields
            $the_box -> add_group_field( $jwst_group, array(
                'id'      => 'sgup_jwst_endpoint_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box -> add_group_field( $jwst_group, array(
                'id'      => 'sgup_jwst_endpoint_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );

            // add the openweather endpoint groups
            $ow_group = $the_box -> add_field( 
                array(
                    'id' => 'ow_ep_group',
                    'type' => 'group',
                    'name' => esc_html__( 'OpenWeather Endpoints', 'sgup' ),
                    'description' => esc_html__( 'See here to get your endpoints: https://openweathermap.org/api/one-call-3', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'OpenWeather Endpoint {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add Another Endpoint', 'sgup' ),
                        'remove_button'     => __( 'Remove Endpoint', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true, // true to have the groups closed by default
                    ),
                )
            );

            // nasa's endpoint fields
            $the_box -> add_group_field( $ow_group, array(
                'id'      => 'sgup_ow_endpoint_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box -> add_group_field( $ow_group, array(
                'id'      => 'sgup_ow_endpoint_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );

            // add the boaa endpoint groups
            $noaa_group = $the_box -> add_field( 
                array(
                    'id' => 'noaa_ep_group',
                    'type' => 'group',
                    'name' => esc_html__( 'NOAA Endpoints', 'sgup' ),
                    'description' => esc_html__( 'See here to get your endpoints: https://www.weather.gov/documentation/services-web-api', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'NOAA Endpoint {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add Another Endpoint', 'sgup' ),
                        'remove_button'     => __( 'Remove Endpoint', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true, // true to have the groups closed by default
                    ),
                )
            );

            // nasa's endpoint fields
            $the_box -> add_group_field( $noaa_group, array(
                'id'      => 'sgup_noaa_endpoint_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box -> add_group_field( $noaa_group, array(
                'id'      => 'sgup_noaa_endpoint_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );

        }

        /** 
         * photo_journal_fields
         * 
         * Add our nasa photo journals
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        private function photo_journal_fields( \CMB2 $the_box ) : void {

            // add the nasa endpoint groups
            $nasa_group = $the_box -> add_field( 
                array(
                    'id' => 'nasa_pj_group',
                    'type' => 'group',
                    'name' => esc_html__( 'NASA Photo Journals', 'sgup' ),
                    'description' => esc_html__( 'See here to get your feeds: https://science.nasa.gov/photojournal/rss-feeds/', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'NASA Photo Journals {#}', 'sgup' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Add Another Journal', 'sgup' ),
                        'remove_button'     => __( 'Remove Journal', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true, // true to have the groups closed by default
                    ),
                )
            );

            // nasa's endpoint fields
            $the_box -> add_group_field( $nasa_group, array(
                'id'      => 'sgup_nasa_pj_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box -> add_group_field( $nasa_group, array(
                'id'      => 'sgup_nasa_pj_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );
            
        }

    }
}