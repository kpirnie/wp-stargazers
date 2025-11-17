<?php
/**
 * SGU Sync Admin Interface
 *
 * Provides admin interface for managing API synchronization
 *
 * @package StarGazersUP
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SGU_Sync_Admin {

    /**
     * Initialize
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 100 );
        add_action( 'admin_notices', array( $this, 'show_sync_notices' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'sgu-weather',
            'API Sync Status',
            'Sync Status',
            'manage_options',
            'sgu-sync-status',
            array( $this, 'render_sync_status_page' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'sgu-weather_page_sgu-sync-status' ) {
            return;
        }

        wp_enqueue_style( 'sgu-sync-admin', $this->get_inline_css() );
    }

    /**
     * Get inline CSS
     */
    private function get_inline_css() {
        return 'data:text/css;base64,' . base64_encode('
            .sgu-sync-status {
                background: #fff;
                padding: 20px;
                margin-top: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .sgu-sync-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .sgu-sync-card {
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
            }
            .sgu-sync-card h3 {
                margin-top: 0;
                color: #23282d;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .sgu-sync-status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .sgu-sync-status-badge.active {
                background: #46b450;
                color: white;
            }
            .sgu-sync-status-badge.inactive {
                background: #dc3232;
                color: white;
            }
            .sgu-sync-info {
                margin: 10px 0;
                font-size: 13px;
            }
            .sgu-sync-info strong {
                display: inline-block;
                width: 120px;
            }
            .sgu-sync-actions {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }
            .sgu-sync-log {
                background: #23282d;
                color: #f0f0f1;
                padding: 15px;
                margin-top: 20px;
                border-radius: 4px;
                font-family: Consolas, Monaco, monospace;
                font-size: 12px;
                max-height: 400px;
                overflow-y: auto;
            }
            .sgu-sync-log-entry {
                margin: 5px 0;
                padding: 5px;
            }
            .sgu-sync-log-entry.error {
                color: #ff6b6b;
            }
            .sgu-sync-log-entry.warning {
                color: #ffa500;
            }
            .sgu-sync-log-entry.info {
                color: #4ecdc4;
            }
            .sgu-bulk-actions {
                background: #f0f0f1;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
        ');
    }

    /**
     * Show sync notices
     */
    public function show_sync_notices() {
        if ( isset( $_GET['sgu_sync'] ) && $_GET['sgu_sync'] === 'success' ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>API Sync Complete!</strong> Data has been synchronized successfully.</p>
            </div>
            <?php
        }
    }

    /**
     * Render sync status page
     */
    public function render_sync_status_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $sync_configs = $this->get_sync_configs();
        $log_entries = $this->get_recent_log_entries( 50 );

        ?>
        <div class="wrap">
            <h1>API Sync Status</h1>
            <p>Monitor and manage API synchronization for space and weather data.</p>

            <div class="sgu-bulk-actions">
                <h2>Bulk Actions</h2>
                <p>
                    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=sgu_manual_sync&sync_type=all' ), 'sgu_manual_sync' ); ?>"
                       class="button button-primary button-large">
                        🔄 Sync All APIs Now
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sgu-sync-status' ); ?>"
                       class="button button-secondary">
                        🔃 Refresh Status
                    </a>
                </p>
            </div>

            <div class="sgu-sync-status">
                <h2>Individual API Status</h2>
                <div class="sgu-sync-grid">
                    <?php foreach ( $sync_configs as $config ) : ?>
                        <?php $this->render_sync_card( $config ); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ( ! empty( $log_entries ) ) : ?>
                <div class="sgu-sync-status">
                    <h2>Recent Sync Log (Last 50 Entries)</h2>
                    <div class="sgu-sync-log">
                        <?php foreach ( $log_entries as $entry ) : ?>
                            <div class="sgu-sync-log-entry <?php echo esc_attr( $entry['level'] ); ?>">
                                <?php echo esc_html( $entry['message'] ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render sync card
     */
    private function render_sync_card( $config ) {
        $is_configured = $this->is_sync_configured( $config['option'], $config['required_fields'] );
        $next_run = wp_next_scheduled( $config['hook'] );
        $post_count = $this->get_post_count( $config['post_type'] );

        ?>
        <div class="sgu-sync-card">
            <h3>
                <?php echo esc_html( $config['name'] ); ?>
                <span class="sgu-sync-status-badge <?php echo $is_configured ? 'active' : 'inactive'; ?>">
                    <?php echo $is_configured ? 'Active' : 'Inactive'; ?>
                </span>
            </h3>

            <div class="sgu-sync-info">
                <div><strong>Schedule:</strong> <?php echo esc_html( $config['schedule'] ); ?></div>
                <?php if ( $next_run ) : ?>
                    <div><strong>Next Run:</strong> <?php echo esc_html( human_time_diff( $next_run, current_time( 'timestamp' ) ) ); ?> from now</div>
                <?php else : ?>
                    <div><strong>Next Run:</strong> <span style="color: #dc3232;">Not scheduled</span></div>
                <?php endif; ?>
                <div><strong>Posts Stored:</strong> <?php echo number_format( $post_count ); ?></div>
                <?php if ( ! $is_configured ) : ?>
                    <div style="color: #dc3232; margin-top: 10px;">
                        ⚠️ API credentials not configured
                    </div>
                <?php endif; ?>
            </div>

            <div class="sgu-sync-actions">
                <?php if ( $is_configured ) : ?>
                    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=sgu_manual_sync&sync_type=' . $config['type'] ), 'sgu_manual_sync' ); ?>"
                       class="button button-primary button-small">
                        Sync Now
                    </a>
                <?php endif; ?>
                <a href="<?php echo admin_url( 'edit.php?post_type=' . $config['post_type'] ); ?>"
                   class="button button-secondary button-small">
                    View Posts
                </a>
                <?php if ( ! empty( $config['settings_page'] ) ) : ?>
                    <a href="<?php echo admin_url( $config['settings_page'] ); ?>"
                       class="button button-secondary button-small">
                        Settings
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get sync configurations
     */
    private function get_sync_configs() {
        return array(
            array(
                'name' => 'CME Alerts',
                'type' => 'cme',
                'hook' => 'sgu_sync_cme_alerts',
                'schedule' => 'Hourly',
                'post_type' => 'sgu_cme_alerts',
                'option' => 'sgup_cme_settings',
                'required_fields' => array( 'sgup_cme_api_endpoint', 'sgup_cme_api_keys' ),
                'settings_page' => 'admin.php?page=sgup_cme_settings'
            ),
            array(
                'name' => 'Solar Flare Alerts',
                'type' => 'flare',
                'hook' => 'sgu_sync_solar_flare',
                'schedule' => 'Hourly',
                'post_type' => 'sgu_sf_alerts',
                'option' => 'sgup_flare_settings',
                'required_fields' => array( 'sgup_flare_api_endpoint' ),
                'settings_page' => 'admin.php?page=sgup_flare_settings'
            ),
            array(
                'name' => 'Geomagnetic Alerts',
                'type' => 'geomag',
                'hook' => 'sgu_sync_geomagnetic',
                'schedule' => 'Every 30 Minutes',
                'post_type' => 'sgu_geo_alerts',
                'option' => 'sgup_geomag_settings',
                'required_fields' => array( 'sgup_geomag_endpoint' ),
                'settings_page' => 'admin.php?page=sgup_geomag_settings'
            ),
            array(
                'name' => 'Space Weather Alerts',
                'type' => 'space_weather',
                'hook' => 'sgu_sync_space_weather',
                'schedule' => 'Every 30 Minutes',
                'post_type' => 'sgu_sw_alerts',
                'option' => 'sgup_sw_settings',
                'required_fields' => array( 'sgup_sw_endpoint' ),
                'settings_page' => 'admin.php?page=sgup_sw_settings'
            ),
            array(
                'name' => 'Near Earth Objects',
                'type' => 'neo',
                'hook' => 'sgu_sync_neo',
                'schedule' => 'Twice Daily',
                'post_type' => 'sgu_neo',
                'option' => 'sgup_neo_settings',
                'required_fields' => array( 'sgup_neo_endpoint' ),
                'settings_page' => 'admin.php?page=sgup_neo_settings'
            ),
            array(
                'name' => 'NASA Photo Journal',
                'type' => 'journal',
                'hook' => 'sgu_sync_photo_journal',
                'schedule' => 'Daily',
                'post_type' => 'sgu_journal',
                'option' => 'sgup_journal_settings',
                'required_fields' => array( 'sgup_journal_feeds' ),
                'settings_page' => 'admin.php?page=sgup_journal_settings'
            ),
            array(
                'name' => 'Astronomy Photo of the Day',
                'type' => 'apod',
                'hook' => 'sgu_sync_apod',
                'schedule' => 'Daily',
                'post_type' => 'sgu_apod',
                'option' => 'sgup_apod_settings',
                'required_fields' => array( 'sgup_apod_endpoint' ),
                'settings_page' => 'admin.php?page=sgup_apod_settings'
            )
        );
    }

    /**
     * Check if sync is configured
     */
    private function is_sync_configured( $option, $required_fields ) {
        $settings = get_option( $option );

        if ( empty( $settings ) ) {
            return false;
        }

        foreach ( $required_fields as $field ) {
            if ( empty( $settings[ $field ] ) ) {
                return false;
            }

            // Check if it's an array (like API keys) and has at least one value
            if ( is_array( $settings[ $field ] ) ) {
                $filtered = array_filter( $settings[ $field ] );
                if ( empty( $filtered ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get post count for a post type
     */
    private function get_post_count( $post_type ) {
        $count = wp_count_posts( $post_type );
        return isset( $count->publish ) ? $count->publish : 0;
    }

    /**
     * Get recent log entries
     */
    private function get_recent_log_entries( $limit = 50 ) {
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/sgu-sync.log';

        if ( ! file_exists( $log_file ) ) {
            return array();
        }

        $lines = file( $log_file );
        if ( ! $lines ) {
            return array();
        }

        // Get last N lines
        $lines = array_slice( $lines, -$limit );
        $lines = array_reverse( $lines );

        $entries = array();
        foreach ( $lines as $line ) {
            if ( preg_match( '/\[([^\]]+)\] \[([^\]]+)\] (.+)/', $line, $matches ) ) {
                $entries[] = array(
                    'timestamp' => $matches[1],
                    'level' => strtolower( $matches[2] ),
                    'message' => trim( $matches[3] )
                );
            }
        }

        return $entries;
    }
}
