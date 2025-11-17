<?php
/**
 * SGU WP-CLI Commands
 *
 * WP-CLI commands for managing API synchronization
 *
 * @package StarGazersUP
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_CLI' ) ) {
    return;
}

/**
 * Manage StarGazers API synchronization
 */
class SGU_CLI_Command {

    /**
     * Sync all APIs
     *
     * ## EXAMPLES
     *
     *     wp sgu sync all
     *
     * @when after_wp_load
     */
    public function all( $args, $assoc_args ) {
        WP_CLI::line( 'Starting full sync of all APIs...' );

        $sync_manager = SGU_Sync_Manager::get_instance();

        WP_CLI::line( 'Syncing CME Alerts...' );
        $sync_manager->sync_cme_alerts();

        WP_CLI::line( 'Syncing Solar Flare Alerts...' );
        $sync_manager->sync_solar_flare();

        WP_CLI::line( 'Syncing Geomagnetic Alerts...' );
        $sync_manager->sync_geomagnetic();

        WP_CLI::line( 'Syncing Space Weather Alerts...' );
        $sync_manager->sync_space_weather();

        WP_CLI::line( 'Syncing Near Earth Objects...' );
        $sync_manager->sync_neo();

        WP_CLI::line( 'Syncing NASA Photo Journal...' );
        $sync_manager->sync_photo_journal();

        WP_CLI::line( 'Syncing APOD...' );
        $sync_manager->sync_apod();

        WP_CLI::success( 'All APIs synced successfully!' );
    }

    /**
     * Sync CME Alerts
     *
     * ## EXAMPLES
     *
     *     wp sgu sync cme
     *
     * @when after_wp_load
     */
    public function cme( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing CME Alerts...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_cme_alerts();
        WP_CLI::success( 'CME Alerts synced!' );
    }

    /**
     * Sync Solar Flare Alerts
     *
     * ## EXAMPLES
     *
     *     wp sgu sync flare
     *
     * @when after_wp_load
     */
    public function flare( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing Solar Flare Alerts...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_solar_flare();
        WP_CLI::success( 'Solar Flare Alerts synced!' );
    }

    /**
     * Sync Geomagnetic Alerts
     *
     * ## EXAMPLES
     *
     *     wp sgu sync geomag
     *
     * @when after_wp_load
     */
    public function geomag( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing Geomagnetic Alerts...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_geomagnetic();
        WP_CLI::success( 'Geomagnetic Alerts synced!' );
    }

    /**
     * Sync Space Weather Alerts
     *
     * ## EXAMPLES
     *
     *     wp sgu sync space-weather
     *
     * @when after_wp_load
     */
    public function space_weather( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing Space Weather Alerts...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_space_weather();
        WP_CLI::success( 'Space Weather Alerts synced!' );
    }

    /**
     * Sync Near Earth Objects
     *
     * ## EXAMPLES
     *
     *     wp sgu sync neo
     *
     * @when after_wp_load
     */
    public function neo( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing Near Earth Objects...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_neo();
        WP_CLI::success( 'Near Earth Objects synced!' );
    }

    /**
     * Sync NASA Photo Journal
     *
     * ## EXAMPLES
     *
     *     wp sgu sync journal
     *
     * @when after_wp_load
     */
    public function journal( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing NASA Photo Journal...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_photo_journal();
        WP_CLI::success( 'NASA Photo Journal synced!' );
    }

    /**
     * Sync Astronomy Photo of the Day
     *
     * ## EXAMPLES
     *
     *     wp sgu sync apod
     *
     * @when after_wp_load
     */
    public function apod( $args, $assoc_args ) {
        WP_CLI::line( 'Syncing APOD...' );
        $sync_manager = SGU_Sync_Manager::get_instance();
        $sync_manager->sync_apod();
        WP_CLI::success( 'APOD synced!' );
    }

    /**
     * Show sync status
     *
     * ## EXAMPLES
     *
     *     wp sgu status
     *
     * @when after_wp_load
     */
    public function status( $args, $assoc_args ) {
        $cron_events = array(
            array(
                'name' => 'CME Alerts',
                'hook' => 'sgu_sync_cme_alerts',
                'post_type' => 'sgu_cme_alerts'
            ),
            array(
                'name' => 'Solar Flare',
                'hook' => 'sgu_sync_solar_flare',
                'post_type' => 'sgu_sf_alerts'
            ),
            array(
                'name' => 'Geomagnetic',
                'hook' => 'sgu_sync_geomagnetic',
                'post_type' => 'sgu_geo_alerts'
            ),
            array(
                'name' => 'Space Weather',
                'hook' => 'sgu_sync_space_weather',
                'post_type' => 'sgu_sw_alerts'
            ),
            array(
                'name' => 'NEO',
                'hook' => 'sgu_sync_neo',
                'post_type' => 'sgu_neo'
            ),
            array(
                'name' => 'Photo Journal',
                'hook' => 'sgu_sync_photo_journal',
                'post_type' => 'sgu_journal'
            ),
            array(
                'name' => 'APOD',
                'hook' => 'sgu_sync_apod',
                'post_type' => 'sgu_apod'
            )
        );

        $table_data = array();

        foreach ( $cron_events as $event ) {
            $next_run = wp_next_scheduled( $event['hook'] );
            $post_count = wp_count_posts( $event['post_type'] );
            $count = isset( $post_count->publish ) ? $post_count->publish : 0;

            $table_data[] = array(
                'API' => $event['name'],
                'Status' => $next_run ? 'Scheduled' : 'Not Scheduled',
                'Next Run' => $next_run ? human_time_diff( $next_run, current_time( 'timestamp' ) ) . ' from now' : 'N/A',
                'Posts' => $count
            );
        }

        WP_CLI\Utils\format_items( 'table', $table_data, array( 'API', 'Status', 'Next Run', 'Posts' ) );
    }

    /**
     * Clear all transient caches
     *
     * ## EXAMPLES
     *
     *     wp sgu clear-cache
     *
     * @when after_wp_load
     */
    public function clear_cache( $args, $assoc_args ) {
        global $wpdb;

        $result = $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sgu_api_%' OR option_name LIKE '_transient_timeout_sgu_api_%'"
        );

        WP_CLI::success( "Cleared $result API cache transients!" );
    }

    /**
     * View sync logs
     *
     * ## OPTIONS
     *
     * [--lines=<number>]
     * : Number of log lines to display (default: 20)
     *
     * ## EXAMPLES
     *
     *     wp sgu logs
     *     wp sgu logs --lines=50
     *
     * @when after_wp_load
     */
    public function logs( $args, $assoc_args ) {
        $lines = isset( $assoc_args['lines'] ) ? intval( $assoc_args['lines'] ) : 20;

        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/sgu-sync.log';

        if ( ! file_exists( $log_file ) ) {
            WP_CLI::warning( 'No log file found.' );
            return;
        }

        $log_lines = file( $log_file );
        if ( ! $log_lines ) {
            WP_CLI::warning( 'Log file is empty.' );
            return;
        }

        $log_lines = array_slice( $log_lines, -$lines );

        WP_CLI::line( '' );
        WP_CLI::line( 'Recent Sync Logs:' );
        WP_CLI::line( str_repeat( '=', 80 ) );

        foreach ( $log_lines as $line ) {
            if ( strpos( $line, '[ERROR]' ) !== false ) {
                WP_CLI::error_multi_line( array( $line ) );
            } elseif ( strpos( $line, '[WARNING]' ) !== false ) {
                WP_CLI::warning( trim( $line ) );
            } else {
                WP_CLI::line( trim( $line ) );
            }
        }
    }
}

// Register sync subcommand
WP_CLI::add_command( 'sgu sync', 'SGU_CLI_Command' );

// Register status command
WP_CLI::add_command( 'sgu status', array( 'SGU_CLI_Command', 'status' ) );

// Register clear-cache command
WP_CLI::add_command( 'sgu clear-cache', array( 'SGU_CLI_Command', 'clear_cache' ) );

// Register logs command
WP_CLI::add_command( 'sgu logs', array( 'SGU_CLI_Command', 'logs' ) );
