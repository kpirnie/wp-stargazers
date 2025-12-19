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
            $this->add_settings();
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
        private function add_settings(): void {

            // hook into the field framework's admin init
            add_action( 'cmb2_admin_init', function() {

                // setup the keys
                $apis_id = 'sgup_apis';
                $epoints_id = 'sgup_endpoints';

                // the apis options page
                $apis = new_cmb2_box( array(
                    'id'           => $apis_id,
                    'title'        => esc_html__( 'US Stargazers Weather API', 'sgup' ),
                    'object_types' => array( 'options-page' ),

                    'option_key'      => $apis_id,
                    'icon_url'        => 'dashicons-location-alt',
                    'menu_title'      => esc_html__( 'SGU Weather', 'sgup' ),
                    'capability'      => 'list_users',
                    'position'        => 2,
                    'save_button'     => esc_html__( 'Save the Settings', 'sgup' ),
                ) );

                // the endpoints options page
                $epoints = new_cmb2_box( array(
                    'id'           => $epoints_id,
                    'title'        => esc_html__( 'US Stargazers Endpoints', 'sgup' ),
                    'object_types' => array( 'options-page' ),

                    'option_key'      => $epoints_id,
                    'icon_url'        => 'dashicons-star-filled',
                    'menu_title'      => esc_html__( 'SGU Endpoints', 'sgup' ),
                    'parent_slug'     => $apis_id,
                    'capability'      => 'list_users',
                    'position'        => 2,
                    'save_button'     => esc_html__( 'Save the Endpoints', 'sgup' ),
                ) );

                // add our fields
                $this->api_key_fields( $apis );
                $this->endpoint_fields( $epoints );

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

            // Open-Meteo notice - no API key required
            $the_box->add_field( 
                array(
                    'name' => esc_html__( 'Open-Meteo Weather API', 'sgup' ),
                    'desc' => esc_html__( 'Open-Meteo provides free weather data without requiring an API key. No configuration needed for basic weather functionality.', 'sgup' ),
                    'id'   => 'sgup_openmeteo_notice',
                    'type' => 'title',
                ) 
            );

            // add the NOAA keys (optional, for extended functionality)
            $the_box->add_field( 
                array(
                    'name'    => esc_html__( 'NOAA User Agent (Optional)', 'sgup' ),
                    'desc'    => esc_html__( 'NOAA Weather API does not require keys, but you can customize the User-Agent string. See: https://www.weather.gov/documentation/services-web-api', 'sgup' ),
                    'id'      => 'sgup_noaa_user_agent',
                    'type'    => 'text',
                    'default' => 'US Star Gazers (iam@kevinpirnie.com)',
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

            // Open-Meteo endpoints info
            $the_box->add_field( 
                array(
                    'name' => esc_html__( 'Open-Meteo Endpoints', 'sgup' ),
                    'desc' => esc_html__( 'Open-Meteo endpoints are pre-configured. See documentation: https://open-meteo.com/en/docs', 'sgup' ),
                    'id'   => 'sgup_openmeteo_endpoints_notice',
                    'type' => 'title',
                )
            );

            // add the NOAA endpoint groups
            $noaa_group = $the_box->add_field( 
                array(
                    'id' => 'noaa_ep_group',
                    'type' => 'group',
                    'name' => esc_html__( 'NOAA Endpoints (Optional Override)', 'sgup' ),
                    'description' => esc_html__( 'NOAA endpoints are pre-configured. Only add custom endpoints if needed. See: https://www.weather.gov/documentation/services-web-api', 'sgup' ),
                    'options'     => array(
                        'group_title'       => __( 'NOAA Endpoint {#}', 'sgup' ),
                        'add_button'        => __( 'Add Another Endpoint', 'sgup' ),
                        'remove_button'     => __( 'Remove Endpoint', 'sgup' ),
                        'sortable'          => true,
                        'closed'         => true,
                    ),
                )
            );

            // NOAA endpoint fields
            $the_box->add_group_field( $noaa_group, array(
                'id'      => 'sgup_noaa_endpoint_type',
                'type'    => 'text_small',
                'name' => __( 'Type', 'sgup' ),
                ) 
            );
            $the_box->add_group_field( $noaa_group, array(
                'id'      => 'sgup_noaa_endpoint_url',
                'type'    => 'text_url',
                'name' => __( 'URL', 'sgup' ),
                'protocols' => array( 'http', 'https', )
                ) 
            );

        }

    }
}
