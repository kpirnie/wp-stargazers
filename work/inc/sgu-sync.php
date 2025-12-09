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

        private array $sync_types;
        private ?SGU_Space_Data $space_data; 
        private ?SGU_Space_Requests $space_requests; 
        private ?SGU_Space_Imagery $space_imagery;

        public function __init( ): void {}

        public function __construct( ) {
            $this -> space_data = new SGU_Space_Data( );
            $this -> space_requests = new SGU_Space_Requests( );
            $this -> space_imagery = new SGU_Space_Imagery( );

            // Define all sync types with their display names
            $this -> sync_types = [
                'geo' => 'Geomagnetic Alerts',
                'sf' => 'Solar Flare Alerts',
                'sw' => 'Space Weather Alerts',
                'cme' => 'CME Alerts',
                'neo' => 'Near Earth Objects',
                'apod' => 'Astronomy Photo of the Day',
            ];
        }

        public function __destruct( ) {
            unset( $this -> space_data, $this -> space_requests, $this -> space_imagery, $this -> sync_types );
        }

        /**
         * sync_both
         * 
         * Sync both data and imagery in one command
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void
         */
        public function sync_both( ) : void {
            $this -> cli_line( null, WP_CLI::colorize("%YStarting combined data and imagery sync...%N") );
            
            // Sync data first
            $this -> sync_the_data( );
            
            // Then sync imagery
            $this -> sync_the_imagery( );
            
            $this -> cli_line( WP_CLI::colorize("%GAll syncing complete!%N") );
        }

        /**
         * sync_the_data
         * 
         * Pulls together all syncing methods for the api data
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void
         */
        public function sync_the_data( ) : void {
            $this -> cli_line( null, WP_CLI::colorize("%YStarting the data sync...%N") );

            $successes = 0;
            $skipped = 0;
            
            // Loop over the sync types and process each
            foreach( $this -> sync_types as $type => $display_name ) {
                WP_CLI::line( WP_CLI::colorize("%CProcessing: $display_name%N") );
                
                $synced = $this -> space_requests -> process_sync_data( $type );

                if( $synced ) {
                    $successes++;
                    WP_CLI::success( "$display_name synced successfully" );
                } else {
                    $skipped++;
                    // Silently skip - no error message needed for items that don't update
                }
            }

            // Post-sync cleanup
            $this -> perform_cleanup( );

            // Display final status
            $total = $successes + $skipped;
            $this -> cli_line( WP_CLI::colorize("%GData sync completed!%N") );
            WP_CLI::line( "  - Processed: $total type(s)" );
            WP_CLI::line( "  - Synced: $successes" );
            if( $skipped > 0 ) {
                WP_CLI::line( "  - Skipped: $skipped (no updates needed)" );
            }
        }

        /**
         * sync_the_imagery
         * 
         * Pulls together all syncing methods for the api imagery
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void
         */
        public function sync_the_imagery( ) : void {
            $this -> cli_line( null, WP_CLI::colorize("%YStarting the imagery sync...%N") );

            WP_CLI::line( WP_CLI::colorize("%CProcessing: APOD Imagery%N") );
            
            try {
                $this -> space_imagery -> sync_apod_imagery( );
                WP_CLI::success( "APOD imagery synced successfully" );
            } catch ( Exception $e ) {
                WP_CLI::error( "Failed to sync APOD imagery: " . $e -> getMessage(), false );
            }

            $this -> cli_line( WP_CLI::colorize("%GImagery sync completed!%N") );
        }

        /**
         * perform_cleanup
         * 
         * Perform post-sync cleanup operations
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @return void
         */
        private function perform_cleanup( ) : void {
            WP_CLI::line( WP_CLI::colorize("%YPerforming post-sync cleanup...%N") );
            
            // Clean up duplicate data
            WP_CLI::line( "  - Removing duplicates..." );
            $removed = $this -> space_data -> clean_up( );
            WP_CLI::line( "    Removed $removed duplicate post(s)" );
            
            // Optimize database
            WP_CLI::line( "  - Optimizing database..." );
            WP_CLI::runcommand( 'db optimize', ['return' => 'all', 'launch' => false, 'exit_error' => false] );
            
            WP_CLI::success( "Cleanup completed" );
        }

        /**
         * cli_line
         * 
         * Display a separator line with optional before/after messages
         * 
         * @since 8.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
         * @param string|null $before Optional message before line
         * @param string|null $after Optional message after line
         * 
         * @return void
         */
        private function cli_line( ?string $before = null, ?string $after = null ) : void {
            if( $before ) {
                WP_CLI::line( $before );
            }
            
            WP_CLI::line( WP_CLI::colorize( '%B' . str_repeat( '*', 76 ) . '%n' ) );
            
            if( $after ) {
                WP_CLI::line( $after );
            }
        }
    }
}
