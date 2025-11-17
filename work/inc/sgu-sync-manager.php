<?php
/**
 * SGU Sync Manager
 *
 * Handles all API synchronization for space and weather data
 *
 * @package StarGazersUP
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SGU_Sync_Manager {

    /**
     * Instance of this class
     *
     * @var SGU_Sync_Manager
     */
    private static $instance = null;

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Get instance
     *
     * @return SGU_Sync_Manager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/sgu-sync.log';

        // Register cron hooks
        add_action( 'sgu_sync_cme_alerts', array( $this, 'sync_cme_alerts' ) );
        add_action( 'sgu_sync_solar_flare', array( $this, 'sync_solar_flare' ) );
        add_action( 'sgu_sync_geomagnetic', array( $this, 'sync_geomagnetic' ) );
        add_action( 'sgu_sync_space_weather', array( $this, 'sync_space_weather' ) );
        add_action( 'sgu_sync_neo', array( $this, 'sync_neo' ) );
        add_action( 'sgu_sync_photo_journal', array( $this, 'sync_photo_journal' ) );
        add_action( 'sgu_sync_apod', array( $this, 'sync_apod' ) );

        // Manual sync actions
        add_action( 'admin_post_sgu_manual_sync', array( $this, 'handle_manual_sync' ) );
    }

    /**
     * Log message
     *
     * @param string $message Message to log
     * @param string $level Log level (info, error, warning)
     */
    private function log( $message, $level = 'info' ) {
        $timestamp = current_time( 'Y-m-d H:i:s' );
        $log_entry = sprintf( "[%s] [%s] %s\n", $timestamp, strtoupper( $level ), $message );
        error_log( $log_entry, 3, $this->log_file );

        if ( $level === 'error' ) {
            error_log( $message );
        }
    }

    /**
     * Make API request with caching
     *
     * @param string $url API URL
     * @param array $args Request arguments
     * @param int $cache_duration Cache duration in seconds (default 1 hour)
     * @return array|WP_Error Response data or error
     */
    private function api_request( $url, $args = array(), $cache_duration = 3600 ) {
        $cache_key = 'sgu_api_' . md5( $url . serialize( $args ) );
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            $this->log( "Using cached response for: $url" );
            return $cached;
        }

        $this->log( "Making API request to: $url" );

        $defaults = array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'WordPress/US-Stargazers-Plugin'
            )
        );

        $args = wp_parse_args( $args, $defaults );
        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log( "API request failed: " . $response->get_error_message(), 'error' );
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $error_msg = "API returned status code: $status_code";
            $this->log( $error_msg, 'error' );
            return new WP_Error( 'api_error', $error_msg );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log( "JSON decode error: " . json_last_error_msg(), 'error' );
            return new WP_Error( 'json_error', 'Failed to decode JSON response' );
        }

        set_transient( $cache_key, $data, $cache_duration );
        return $data;
    }

    /**
     * Get API key from settings
     *
     * @param string $option_name Option name
     * @param string $key_field Field name for API keys
     * @param string $fallback_option Fallback option name (for shared keys)
     * @param string $fallback_field Fallback field name
     * @return string|null API key or null if not found
     */
    private function get_api_key( $option_name, $key_field, $fallback_option = null, $fallback_field = null ) {
        $settings = get_option( $option_name );

        if ( ! empty( $settings[ $key_field ] ) && is_array( $settings[ $key_field ] ) ) {
            $keys = array_filter( $settings[ $key_field ] );
            if ( ! empty( $keys[0] ) ) {
                return $keys[0];
            }
        }

        // Try fallback
        if ( $fallback_option && $fallback_field ) {
            $fallback_settings = get_option( $fallback_option );
            if ( ! empty( $fallback_settings[ $fallback_field ] ) && is_array( $fallback_settings[ $fallback_field ] ) ) {
                $keys = array_filter( $fallback_settings[ $fallback_field ] );
                if ( ! empty( $keys[0] ) ) {
                    return $keys[0];
                }
            }
        }

        return null;
    }

    /**
     * Sync CME (Coronal Mass Ejection) Alerts
     */
    public function sync_cme_alerts() {
        $this->log( "Starting CME alerts sync" );

        $settings = get_option( 'sgup_cme_settings' );
        if ( empty( $settings['sgup_cme_api_endpoint'] ) ) {
            $this->log( "CME endpoint not configured", 'warning' );
            return;
        }

        $api_key = $this->get_api_key( 'sgup_cme_settings', 'sgup_cme_api_keys' );
        if ( ! $api_key ) {
            $this->log( "CME API key not configured", 'error' );
            return;
        }

        $endpoint = $settings['sgup_cme_api_endpoint'];
        $url = add_query_arg( 'api_key', $api_key, $endpoint );

        $data = $this->api_request( $url, array(), 3600 ); // 1 hour cache

        if ( is_wp_error( $data ) ) {
            return;
        }

        // Process and store CME alerts
        if ( ! empty( $data ) && is_array( $data ) ) {
            foreach ( $data as $alert ) {
                $this->create_or_update_cme_alert( $alert );
            }
            $this->log( "CME alerts synced successfully. Processed " . count( $data ) . " alerts" );
        }
    }

    /**
     * Create or update CME alert post
     *
     * @param array $alert_data Alert data from API
     */
    private function create_or_update_cme_alert( $alert_data ) {
        if ( empty( $alert_data ) ) {
            return;
        }

        // Use activityID as unique identifier
        $activity_id = isset( $alert_data['activityID'] ) ? $alert_data['activityID'] : null;
        if ( ! $activity_id ) {
            return;
        }

        // Check if post already exists
        $existing = get_posts( array(
            'post_type' => 'sgu_cme_alerts',
            'meta_key' => 'sgu_activity_id',
            'meta_value' => $activity_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        $post_id = $existing ? $existing[0]->ID : 0;

        // Prepare post data
        $title = sprintf(
            'CME Alert - %s',
            isset( $alert_data['startTime'] ) ? date( 'Y-m-d H:i', strtotime( $alert_data['startTime'] ) ) : 'Unknown'
        );

        $content = '';
        if ( isset( $alert_data['note'] ) ) {
            $content .= wpautop( sanitize_textarea_field( $alert_data['note'] ) );
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field( $title ),
            'post_content' => $content,
            'post_type' => 'sgu_cme_alerts',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create CME alert post: " . $result->get_error_message(), 'error' );
            return;
        }

        // Store all alert data as post meta
        update_post_meta( $result, 'sgu_activity_id', $activity_id );
        update_post_meta( $result, 'sgu_alert_data', $alert_data );

        if ( isset( $alert_data['startTime'] ) ) {
            update_post_meta( $result, 'sgu_start_time', sanitize_text_field( $alert_data['startTime'] ) );
        }
    }

    /**
     * Sync Solar Flare Alerts
     */
    public function sync_solar_flare() {
        $this->log( "Starting Solar Flare alerts sync" );

        $settings = get_option( 'sgup_flare_settings' );
        if ( empty( $settings['sgup_flare_api_endpoint'] ) ) {
            $this->log( "Solar Flare endpoint not configured", 'warning' );
            return;
        }

        // Check if using shared CME keys
        $api_key = $this->get_api_key(
            'sgup_flare_settings',
            'sgup_flare_api_keys',
            'sgup_cme_settings',
            'sgup_cme_api_keys'
        );

        if ( ! $api_key ) {
            $this->log( "Solar Flare API key not configured", 'error' );
            return;
        }

        $endpoint = $settings['sgup_flare_api_endpoint'];
        $url = add_query_arg( 'api_key', $api_key, $endpoint );

        $data = $this->api_request( $url, array(), 3600 ); // 1 hour cache

        if ( is_wp_error( $data ) ) {
            return;
        }

        // Process and store Solar Flare alerts
        if ( ! empty( $data ) && is_array( $data ) ) {
            foreach ( $data as $alert ) {
                $this->create_or_update_solar_flare_alert( $alert );
            }
            $this->log( "Solar Flare alerts synced successfully. Processed " . count( $data ) . " alerts" );
        }
    }

    /**
     * Create or update Solar Flare alert post
     *
     * @param array $alert_data Alert data from API
     */
    private function create_or_update_solar_flare_alert( $alert_data ) {
        if ( empty( $alert_data ) ) {
            return;
        }

        // Use flrID as unique identifier
        $flare_id = isset( $alert_data['flrID'] ) ? $alert_data['flrID'] : null;
        if ( ! $flare_id ) {
            return;
        }

        // Check if post already exists
        $existing = get_posts( array(
            'post_type' => 'sgu_sf_alerts',
            'meta_key' => 'sgu_flare_id',
            'meta_value' => $flare_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        $post_id = $existing ? $existing[0]->ID : 0;

        // Prepare post data
        $title = sprintf(
            'Solar Flare - %s - Class %s',
            isset( $alert_data['beginTime'] ) ? date( 'Y-m-d H:i', strtotime( $alert_data['beginTime'] ) ) : 'Unknown',
            isset( $alert_data['classType'] ) ? $alert_data['classType'] : 'Unknown'
        );

        $content = '';
        if ( isset( $alert_data['note'] ) ) {
            $content .= wpautop( sanitize_textarea_field( $alert_data['note'] ) );
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field( $title ),
            'post_content' => $content,
            'post_type' => 'sgu_sf_alerts',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create Solar Flare alert post: " . $result->get_error_message(), 'error' );
            return;
        }

        // Store all alert data as post meta
        update_post_meta( $result, 'sgu_flare_id', $flare_id );
        update_post_meta( $result, 'sgu_alert_data', $alert_data );

        if ( isset( $alert_data['beginTime'] ) ) {
            update_post_meta( $result, 'sgu_begin_time', sanitize_text_field( $alert_data['beginTime'] ) );
        }

        if ( isset( $alert_data['classType'] ) ) {
            update_post_meta( $result, 'sgu_class_type', sanitize_text_field( $alert_data['classType'] ) );
        }
    }

    /**
     * Sync Geomagnetic Alerts
     */
    public function sync_geomagnetic() {
        $this->log( "Starting Geomagnetic alerts sync" );

        $settings = get_option( 'sgup_geomag_settings' );
        if ( empty( $settings['sgup_geomag_endpoint'] ) ) {
            $this->log( "Geomagnetic endpoint not configured", 'warning' );
            return;
        }

        $endpoint = $settings['sgup_geomag_endpoint'];

        // NOAA endpoint doesn't require API key
        $data = $this->api_request( $endpoint, array(), 1800 ); // 30 min cache

        if ( is_wp_error( $data ) ) {
            return;
        }

        // For text-based NOAA data, parse the response
        if ( is_string( $data ) ) {
            $this->parse_and_store_geomagnetic_text( $data );
        } else {
            $this->log( "Unexpected Geomagnetic data format", 'warning' );
        }
    }

    /**
     * Parse and store geomagnetic text data
     *
     * @param string $text_data Text data from NOAA
     */
    private function parse_and_store_geomagnetic_text( $text_data ) {
        // Simple parser for NOAA text format
        $lines = explode( "\n", $text_data );

        $alert_data = array(
            'content' => $text_data,
            'timestamp' => current_time( 'mysql' )
        );

        // Create a single post with the latest data
        $existing = get_posts( array(
            'post_type' => 'sgu_geo_alerts',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ) );

        // Check if content has changed
        if ( $existing ) {
            $existing_content = get_post_meta( $existing[0]->ID, 'sgu_alert_content', true );
            if ( $existing_content === $text_data ) {
                $this->log( "Geomagnetic data unchanged, skipping" );
                return;
            }
        }

        $title = sprintf( 'Geomagnetic Alert - %s', current_time( 'Y-m-d H:i' ) );

        $post_data = array(
            'post_title' => sanitize_text_field( $title ),
            'post_content' => wpautop( sanitize_textarea_field( $text_data ) ),
            'post_type' => 'sgu_geo_alerts',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create Geomagnetic alert post: " . $result->get_error_message(), 'error' );
            return;
        }

        update_post_meta( $result, 'sgu_alert_content', $text_data );
        update_post_meta( $result, 'sgu_sync_time', current_time( 'mysql' ) );

        $this->log( "Geomagnetic alert synced successfully" );
    }

    /**
     * Sync Space Weather Alerts
     */
    public function sync_space_weather() {
        $this->log( "Starting Space Weather alerts sync" );

        $settings = get_option( 'sgup_sw_settings' );
        if ( empty( $settings['sgup_sw_endpoint'] ) ) {
            $this->log( "Space Weather endpoint not configured", 'warning' );
            return;
        }

        $endpoint = $settings['sgup_sw_endpoint'];

        // NOAA endpoint doesn't require API key
        $response = wp_remote_get( $endpoint, array( 'timeout' => 30 ) );

        if ( is_wp_error( $response ) ) {
            $this->log( "Space Weather API request failed: " . $response->get_error_message(), 'error' );
            return;
        }

        $data = wp_remote_retrieve_body( $response );

        // Try to parse as JSON first
        $json_data = json_decode( $data, true );

        if ( json_last_error() === JSON_ERROR_NONE && is_array( $json_data ) ) {
            // Process JSON data
            foreach ( $json_data as $alert ) {
                $this->create_or_update_space_weather_alert( $alert );
            }
            $this->log( "Space Weather alerts synced successfully. Processed " . count( $json_data ) . " alerts" );
        } else {
            // Process as text data
            $this->parse_and_store_space_weather_text( $data );
        }
    }

    /**
     * Create or update Space Weather alert post
     *
     * @param array $alert_data Alert data from API
     */
    private function create_or_update_space_weather_alert( $alert_data ) {
        if ( empty( $alert_data ) ) {
            return;
        }

        // Use a hash of the alert data as unique identifier
        $alert_hash = md5( serialize( $alert_data ) );

        // Check if post already exists
        $existing = get_posts( array(
            'post_type' => 'sgu_sw_alerts',
            'meta_key' => 'sgu_alert_hash',
            'meta_value' => $alert_hash,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        if ( $existing ) {
            return; // Alert already exists
        }

        // Prepare post data
        $title = isset( $alert_data['title'] ) ? $alert_data['title'] : 'Space Weather Alert - ' . current_time( 'Y-m-d H:i' );
        $content = isset( $alert_data['description'] ) ? $alert_data['description'] : '';

        $post_data = array(
            'post_title' => sanitize_text_field( $title ),
            'post_content' => wpautop( sanitize_textarea_field( $content ) ),
            'post_type' => 'sgu_sw_alerts',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create Space Weather alert post: " . $result->get_error_message(), 'error' );
            return;
        }

        update_post_meta( $result, 'sgu_alert_hash', $alert_hash );
        update_post_meta( $result, 'sgu_alert_data', $alert_data );
    }

    /**
     * Parse and store space weather text data
     *
     * @param string $text_data Text data from NOAA
     */
    private function parse_and_store_space_weather_text( $text_data ) {
        $title = sprintf( 'Space Weather Alert - %s', current_time( 'Y-m-d H:i' ) );

        // Check if content has changed
        $existing = get_posts( array(
            'post_type' => 'sgu_sw_alerts',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ) );

        if ( $existing ) {
            $existing_content = get_post_meta( $existing[0]->ID, 'sgu_alert_content', true );
            if ( $existing_content === $text_data ) {
                $this->log( "Space Weather data unchanged, skipping" );
                return;
            }
        }

        $post_data = array(
            'post_title' => sanitize_text_field( $title ),
            'post_content' => wpautop( sanitize_textarea_field( $text_data ) ),
            'post_type' => 'sgu_sw_alerts',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create Space Weather alert post: " . $result->get_error_message(), 'error' );
            return;
        }

        update_post_meta( $result, 'sgu_alert_content', $text_data );
        update_post_meta( $result, 'sgu_sync_time', current_time( 'mysql' ) );

        $this->log( "Space Weather alert synced successfully" );
    }

    /**
     * Sync Near Earth Objects
     */
    public function sync_neo() {
        $this->log( "Starting NEO sync" );

        $settings = get_option( 'sgup_neo_settings' );
        if ( empty( $settings['sgup_neo_endpoint'] ) ) {
            $this->log( "NEO endpoint not configured", 'warning' );
            return;
        }

        // Check if using shared CME keys
        $api_key = $this->get_api_key(
            'sgup_neo_settings',
            'sgup_neo_keys',
            'sgup_cme_settings',
            'sgup_cme_api_keys'
        );

        if ( ! $api_key ) {
            $this->log( "NEO API key not configured", 'error' );
            return;
        }

        $endpoint = $settings['sgup_neo_endpoint'];

        // Add date range for today
        $start_date = current_time( 'Y-m-d' );
        $end_date = date( 'Y-m-d', strtotime( '+7 days' ) );

        $url = add_query_arg( array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'api_key' => $api_key
        ), $endpoint );

        $data = $this->api_request( $url, array(), 7200 ); // 2 hour cache

        if ( is_wp_error( $data ) ) {
            return;
        }

        // Process NEO data
        if ( ! empty( $data['near_earth_objects'] ) ) {
            $count = 0;
            foreach ( $data['near_earth_objects'] as $date => $objects ) {
                foreach ( $objects as $neo ) {
                    $this->create_or_update_neo( $neo );
                    $count++;
                }
            }
            $this->log( "NEO data synced successfully. Processed $count objects" );
        }
    }

    /**
     * Create or update NEO post
     *
     * @param array $neo_data NEO data from API
     */
    private function create_or_update_neo( $neo_data ) {
        if ( empty( $neo_data['id'] ) ) {
            return;
        }

        $neo_id = $neo_data['id'];

        // Check if post already exists
        $existing = get_posts( array(
            'post_type' => 'sgu_neo',
            'meta_key' => 'sgu_neo_id',
            'meta_value' => $neo_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        $post_id = $existing ? $existing[0]->ID : 0;

        $name = isset( $neo_data['name'] ) ? $neo_data['name'] : "NEO $neo_id";
        $is_hazardous = isset( $neo_data['is_potentially_hazardous_asteroid'] ) ? $neo_data['is_potentially_hazardous_asteroid'] : false;

        // Build content
        $content = '';
        if ( isset( $neo_data['estimated_diameter'] ) ) {
            $content .= '<h3>Estimated Diameter</h3>';
            $diameter = $neo_data['estimated_diameter'];
            if ( isset( $diameter['kilometers'] ) ) {
                $content .= sprintf(
                    '<p>%s - %s km</p>',
                    number_format( $diameter['kilometers']['estimated_diameter_min'], 4 ),
                    number_format( $diameter['kilometers']['estimated_diameter_max'], 4 )
                );
            }
        }

        if ( isset( $neo_data['close_approach_data'] ) && is_array( $neo_data['close_approach_data'] ) ) {
            $content .= '<h3>Close Approach Data</h3>';
            foreach ( $neo_data['close_approach_data'] as $approach ) {
                $content .= sprintf(
                    '<p><strong>Date:</strong> %s<br><strong>Velocity:</strong> %s km/h<br><strong>Miss Distance:</strong> %s km</p>',
                    isset( $approach['close_approach_date_full'] ) ? $approach['close_approach_date_full'] : 'Unknown',
                    isset( $approach['relative_velocity']['kilometers_per_hour'] ) ? number_format( $approach['relative_velocity']['kilometers_per_hour'] ) : 'Unknown',
                    isset( $approach['miss_distance']['kilometers'] ) ? number_format( $approach['miss_distance']['kilometers'] ) : 'Unknown'
                );
            }
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field( $name ),
            'post_content' => $content,
            'post_type' => 'sgu_neo',
            'post_status' => 'publish'
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create NEO post: " . $result->get_error_message(), 'error' );
            return;
        }

        // Store metadata
        update_post_meta( $result, 'sgu_neo_id', $neo_id );
        update_post_meta( $result, 'sgu_neo_hazardous', $is_hazardous ? 'yes' : 'no' );
        update_post_meta( $result, 'sgu_neo_data', $neo_data );

        if ( isset( $neo_data['absolute_magnitude_h'] ) ) {
            update_post_meta( $result, 'sgu_neo_magnitude', $neo_data['absolute_magnitude_h'] );
        }
    }

    /**
     * Sync NASA Photo Journal RSS
     */
    public function sync_photo_journal() {
        $this->log( "Starting Photo Journal sync" );

        $settings = get_option( 'sgup_journal_settings' );
        if ( empty( $settings['sgup_journal_feeds'] ) ) {
            $this->log( "Photo Journal feeds not configured", 'warning' );
            return;
        }

        $feeds = $settings['sgup_journal_feeds'];

        foreach ( $feeds as $feed_config ) {
            if ( empty( $feed_config['sgup_journal_feed'] ) ) {
                continue;
            }

            $feed_url = $feed_config['sgup_journal_feed'];
            $category = isset( $feed_config['sgup_journal_type'] ) ? $feed_config['sgup_journal_type'] : 'General';

            $this->sync_rss_feed( $feed_url, $category );
        }
    }

    /**
     * Sync RSS feed
     *
     * @param string $feed_url Feed URL
     * @param string $category Category name
     */
    private function sync_rss_feed( $feed_url, $category ) {
        include_once( ABSPATH . WPINC . '/feed.php' );

        $rss = fetch_feed( $feed_url );

        if ( is_wp_error( $rss ) ) {
            $this->log( "Failed to fetch RSS feed: " . $rss->get_error_message(), 'error' );
            return;
        }

        $maxitems = $rss->get_item_quantity( 20 );
        $items = $rss->get_items( 0, $maxitems );

        if ( empty( $items ) ) {
            $this->log( "No items in RSS feed: $feed_url", 'warning' );
            return;
        }

        $count = 0;
        foreach ( $items as $item ) {
            $this->create_or_update_journal_post( $item, $category );
            $count++;
        }

        $this->log( "Photo Journal synced successfully for category '$category'. Processed $count items" );
    }

    /**
     * Create or update journal post from RSS item
     *
     * @param SimplePie_Item $item RSS item
     * @param string $category Category name
     */
    private function create_or_update_journal_post( $item, $category ) {
        $title = $item->get_title();
        $link = $item->get_permalink();

        // Check if post already exists by URL
        $existing = get_posts( array(
            'post_type' => 'sgu_journal',
            'meta_key' => 'sgu_journal_url',
            'meta_value' => $link,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        if ( $existing ) {
            return; // Post already exists
        }

        $content = $item->get_content();
        $description = $item->get_description();

        // Try to extract image
        $image_url = null;
        if ( $enclosure = $item->get_enclosure() ) {
            $image_url = $enclosure->get_link();
        } else {
            // Try to find image in content
            preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches );
            if ( ! empty( $matches[1] ) ) {
                $image_url = $matches[1];
            }
        }

        $post_data = array(
            'post_title' => sanitize_text_field( $title ),
            'post_content' => wp_kses_post( $content ?: $description ),
            'post_type' => 'sgu_journal',
            'post_status' => 'publish',
            'post_date' => $item->get_date( 'Y-m-d H:i:s' )
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create Photo Journal post: " . $result->get_error_message(), 'error' );
            return;
        }

        // Store metadata
        update_post_meta( $result, 'sgu_journal_url', esc_url_raw( $link ) );
        update_post_meta( $result, 'sgu_journal_category', sanitize_text_field( $category ) );

        // Download and attach image
        if ( $image_url ) {
            $attachment_id = $this->download_and_attach_image( $image_url, $result );
            if ( $attachment_id ) {
                update_post_meta( $result, 'sgu_journal_local_image', $attachment_id );
                set_post_thumbnail( $result, $attachment_id );
            }
        }
    }

    /**
     * Sync APOD (Astronomy Picture of the Day)
     */
    public function sync_apod() {
        $this->log( "Starting APOD sync" );

        $settings = get_option( 'sgup_apod_settings' );
        if ( empty( $settings['sgup_apod_endpoint'] ) ) {
            $this->log( "APOD endpoint not configured", 'warning' );
            return;
        }

        // Check if using shared CME keys
        $api_key = $this->get_api_key(
            'sgup_apod_settings',
            'sgup_apod_keys',
            'sgup_cme_settings',
            'sgup_cme_api_keys'
        );

        if ( ! $api_key ) {
            $this->log( "APOD API key not configured", 'error' );
            return;
        }

        $endpoint = $settings['sgup_apod_endpoint'];
        $url = add_query_arg( 'api_key', $api_key, $endpoint );

        $data = $this->api_request( $url, array(), 43200 ); // 12 hour cache (APOD updates daily)

        if ( is_wp_error( $data ) ) {
            return;
        }

        $this->create_or_update_apod( $data );
        $this->log( "APOD synced successfully" );
    }

    /**
     * Create or update APOD post
     *
     * @param array $apod_data APOD data from API
     */
    private function create_or_update_apod( $apod_data ) {
        if ( empty( $apod_data['date'] ) ) {
            return;
        }

        $date = $apod_data['date'];

        // Check if post already exists for this date
        $existing = get_posts( array(
            'post_type' => 'sgu_apod',
            'meta_key' => 'sgu_apod_date',
            'meta_value' => $date,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ) );

        $post_id = $existing ? $existing[0]->ID : 0;

        $title = isset( $apod_data['title'] ) ? $apod_data['title'] : "APOD - $date";
        $content = '';

        if ( isset( $apod_data['explanation'] ) ) {
            $content .= wpautop( sanitize_textarea_field( $apod_data['explanation'] ) );
        }

        if ( isset( $apod_data['copyright'] ) ) {
            $content .= sprintf( '<p><em>Copyright: %s</em></p>', sanitize_text_field( $apod_data['copyright'] ) );
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field( $title ),
            'post_content' => $content,
            'post_type' => 'sgu_apod',
            'post_status' => 'publish',
            'post_date' => date( 'Y-m-d H:i:s', strtotime( $date ) )
        );

        $result = wp_insert_post( $post_data, true );

        if ( is_wp_error( $result ) ) {
            $this->log( "Failed to create APOD post: " . $result->get_error_message(), 'error' );
            return;
        }

        // Store metadata
        update_post_meta( $result, 'sgu_apod_date', $date );
        update_post_meta( $result, 'sgu_apod_data', $apod_data );

        $media_type = isset( $apod_data['media_type'] ) ? $apod_data['media_type'] : 'image';
        update_post_meta( $result, 'sgu_apod_local_media_type', $media_type );

        // Download and attach media
        if ( $media_type === 'image' && ! empty( $apod_data['url'] ) ) {
            $attachment_id = $this->download_and_attach_image( $apod_data['url'], $result );
            if ( $attachment_id ) {
                update_post_meta( $result, 'sgu_apod_local_media', $attachment_id );
                set_post_thumbnail( $result, $attachment_id );
            }
        } elseif ( $media_type === 'video' && ! empty( $apod_data['url'] ) ) {
            update_post_meta( $result, 'sgu_apod_video_url', esc_url_raw( $apod_data['url'] ) );
        }

        // Store HD URL if available
        if ( ! empty( $apod_data['hdurl'] ) ) {
            update_post_meta( $result, 'sgu_apod_hd_url', esc_url_raw( $apod_data['hdurl'] ) );
        }
    }

    /**
     * Download and attach image to post
     *
     * @param string $image_url Image URL
     * @param int $post_id Post ID to attach to
     * @return int|false Attachment ID or false on failure
     */
    private function download_and_attach_image( $image_url, $post_id ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $tmp = download_url( $image_url );

        if ( is_wp_error( $tmp ) ) {
            $this->log( "Failed to download image: " . $tmp->get_error_message(), 'error' );
            return false;
        }

        $file_array = array(
            'name' => basename( $image_url ),
            'tmp_name' => $tmp
        );

        $attachment_id = media_handle_sideload( $file_array, $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $file_array['tmp_name'] );
            $this->log( "Failed to attach image: " . $attachment_id->get_error_message(), 'error' );
            return false;
        }

        return $attachment_id;
    }

    /**
     * Handle manual sync request
     */
    public function handle_manual_sync() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        check_admin_referer( 'sgu_manual_sync' );

        $sync_type = isset( $_GET['sync_type'] ) ? sanitize_text_field( $_GET['sync_type'] ) : 'all';

        switch ( $sync_type ) {
            case 'cme':
                $this->sync_cme_alerts();
                break;
            case 'flare':
                $this->sync_solar_flare();
                break;
            case 'geomag':
                $this->sync_geomagnetic();
                break;
            case 'space_weather':
                $this->sync_space_weather();
                break;
            case 'neo':
                $this->sync_neo();
                break;
            case 'journal':
                $this->sync_photo_journal();
                break;
            case 'apod':
                $this->sync_apod();
                break;
            case 'all':
            default:
                $this->sync_cme_alerts();
                $this->sync_solar_flare();
                $this->sync_geomagnetic();
                $this->sync_space_weather();
                $this->sync_neo();
                $this->sync_photo_journal();
                $this->sync_apod();
                break;
        }

        wp_redirect( add_query_arg( 'sgu_sync', 'success', wp_get_referer() ) );
        exit;
    }

    /**
     * Schedule all cron events
     */
    public static function schedule_events() {
        // CME alerts - every hour
        if ( ! wp_next_scheduled( 'sgu_sync_cme_alerts' ) ) {
            wp_schedule_event( time(), 'hourly', 'sgu_sync_cme_alerts' );
        }

        // Solar Flare - every hour
        if ( ! wp_next_scheduled( 'sgu_sync_solar_flare' ) ) {
            wp_schedule_event( time(), 'hourly', 'sgu_sync_solar_flare' );
        }

        // Geomagnetic - every 30 minutes (needs custom interval)
        if ( ! wp_next_scheduled( 'sgu_sync_geomagnetic' ) ) {
            wp_schedule_event( time(), 'sgu_30min', 'sgu_sync_geomagnetic' );
        }

        // Space Weather - every 30 minutes
        if ( ! wp_next_scheduled( 'sgu_sync_space_weather' ) ) {
            wp_schedule_event( time(), 'sgu_30min', 'sgu_sync_space_weather' );
        }

        // NEO - twice daily
        if ( ! wp_next_scheduled( 'sgu_sync_neo' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'sgu_sync_neo' );
        }

        // Photo Journal - daily
        if ( ! wp_next_scheduled( 'sgu_sync_photo_journal' ) ) {
            wp_schedule_event( time(), 'daily', 'sgu_sync_photo_journal' );
        }

        // APOD - daily
        if ( ! wp_next_scheduled( 'sgu_sync_apod' ) ) {
            wp_schedule_event( time(), 'daily', 'sgu_sync_apod' );
        }
    }

    /**
     * Unschedule all cron events
     */
    public static function unschedule_events() {
        wp_clear_scheduled_hook( 'sgu_sync_cme_alerts' );
        wp_clear_scheduled_hook( 'sgu_sync_solar_flare' );
        wp_clear_scheduled_hook( 'sgu_sync_geomagnetic' );
        wp_clear_scheduled_hook( 'sgu_sync_space_weather' );
        wp_clear_scheduled_hook( 'sgu_sync_neo' );
        wp_clear_scheduled_hook( 'sgu_sync_photo_journal' );
        wp_clear_scheduled_hook( 'sgu_sync_apod' );
    }
}

// Add custom cron interval
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['sgu_30min'] = array(
        'interval' => 1800, // 30 minutes in seconds
        'display'  => __( 'Every 30 Minutes' )
    );
    return $schedules;
} );

// Initialize sync manager
add_action( 'init', function() {
    SGU_Sync_Manager::get_instance();
} );

// Schedule events on plugin activation
register_activation_hook( dirname( dirname( __FILE__ ) ) . '/stargazers.php', array( 'SGU_Sync_Manager', 'schedule_events' ) );

// Unschedule events on plugin deactivation
register_deactivation_hook( dirname( dirname( __FILE__ ) ) . '/stargazers.php', array( 'SGU_Sync_Manager', 'unschedule_events' ) );
