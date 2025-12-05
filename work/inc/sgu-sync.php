<?php
/** 
 * Sync Class
 * 
 * This class will contain the api sync methods
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// we also only want to allow CLI access
// defined( 'WP_CLI' ) || die( 'Only CLI access allowed' );

if( ! class_exists( 'SGU_Sync' ) ) {

    /** 
     * Class SGU_Sync
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Sync {

        // hold the internal actions and database class
        private array $actions;
        private ?SGU_Space_Data $space_data; 
        private ?SGU_Space_Requests $space_requests; 

        // we need an empty method, so let's use this
        public function __init( ): void {}

        // fire up the class
        public function __construct( ) {

            // set our space database and requests objects
            $this -> space_data = new SGU_Space_Data( );
            $this -> space_requests = new SGU_Space_Requests( );

            // set the actions array
            $this -> actions = array( 
                'geo' => 'geomagentic_alerts',
                'sf' => 'solar_flare_alerts',
                'sw' => 'space_weather_alerts',
                'cme' => 'cme_alerts',
                'neo' => 'neos',
                'pj' => 'photo_journals',
                'apod' => 'apods',
            );

        }

        // destroyer!
        public function __destruct( ) {

            // clean up
            unset( $this -> space_data, $this -> space_requests, $this -> actions );

        }

        /**
         * sync_data
         * 
         * pulls together all syncing methods for the api data
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void This method returns nothing
         */
        public function sync_the_data( ) : void {

            // show a message that we're starting
            $this -> cli_line( null, WP_CLI::colorize("%YStarting the data sync...%N") );

            // loop over the actions
            foreach( $this -> actions as $which => $action ) {

                // process the data
                $synced = $this -> space_requests -> process_sync_data( $which );

                // if the sync was NOT successful
                if( ! $synced ) {
                    WP_CLI::error( "The sync for: $which was not successful.", false );
                }

            }

            // clean up the data
            WP_CLI::line( WP_CLI::colorize("%YCleaning the data...%N") );
            //$this -> space_data -> clean_up( );

            // optimize the database
            WP_CLI::line( WP_CLI::colorize("%YOptimizing the database...%N") );
            //$_ = WP_CLI::runcommand( 'db optimize', ['return' => 'all'] );

            // end message
            $this -> cli_line( WP_CLI::colorize("%GGood to Go!%N") );

        }

        /**
         * sync_imagery
         * 
         * pulls together all syncing methods for the api imagery
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void This method returns nothing
         */
        public function sync_the_imagery( ) : void {

            // show a message that we're starting
            $this -> cli_line( null, 'Starting the image sync...' );


            // end message
            $this -> cli_line( 'All set.' );

        }

        /**
         * cli_line
         * 
         * this method only displays a blue line to separate notices
         * if before or after are passed, it displays the relevant string
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void This method returns nothing
         */
        private function cli_line( ?string $before = null, ?string $after = null ) : void {

            // if there's a string before
            if( $before ) {
                WP_CLI::line( $before );
            }
            // show the line
            WP_CLI::line( WP_CLI::colorize( '%B' . str_repeat( '*', 76 ) . '%n' ) );
            // if there's a string after
            if( $after ) {
                WP_CLI::line( $after );
            }
        }

    }

}
