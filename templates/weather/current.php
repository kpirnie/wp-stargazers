<?php
/**
 * Current Weather Template
 * 
 * Displays current weather conditions from OpenWeather API
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>
<div class="sgu-weather-container sgu-weather-current-container" data-weather-type="current">
    
    <h2 class="uk-heading-bullet uk-heading-divider"><?php echo esc_html( $title ); ?></h2>

    <?php if( $show_location_picker ) : ?>
        <?php 
        // Include the location picker
        include SGUP_PATH . '/templates/weather/location-picker.php';
        ?>
    <?php endif; ?>

    <!-- Loading State -->
    <div class="sgu-weather-loading" style="display:none;">
        <div class="uk-text-center uk-padding">
            <div uk-spinner="ratio: 2"></div>
            <p class="uk-margin-small-top">Loading weather data...</p>
        </div>
    </div>

    <!-- Weather Content -->
    <div class="sgu-weather-content">
        
        <?php if( ! $has_location ) : ?>
            <div class="sgu-weather-no-location uk-alert uk-alert-primary">
                <p>Please set your location above to view weather data.</p>
            </div>
        <?php elseif( ! $weather ) : ?>
            <div class="sgu-weather-error uk-alert uk-alert-warning">
                <p>Unable to retrieve weather data. Please try again later.</p>
            </div>
        <?php else : ?>
            
            <?php
            // Extract weather data
            $temp = round( $weather -> main -> temp ?? 0 );
            $feels_like = round( $weather -> main -> feels_like ?? 0 );
            $humidity = $weather -> main -> humidity ?? 0;
            $pressure = $weather -> main -> pressure ?? 0;
            $wind_speed = round( $weather -> wind -> speed ?? 0 );
            $wind_deg = $weather -> wind -> deg ?? 0;
            $description = ucfirst( $weather -> weather[0] -> description ?? '' );
            $icon = $weather -> weather[0] -> icon ?? '01d';
            $visibility = isset( $weather -> visibility ) ? round( $weather -> visibility / 1609.34, 1 ) : null; // Convert to miles
            $clouds = $weather -> clouds -> all ?? 0;
            $sunrise = isset( $weather -> sys -> sunrise ) ? date( 'g:i A', $weather -> sys -> sunrise ) : '';
            $sunset = isset( $weather -> sys -> sunset ) ? date( 'g:i A', $weather -> sys -> sunset ) : '';
            
            // Location name
            $loc_display = '';
            if( $location_name ) {
                $loc_display = $location_name -> name;
                if( ! empty( $location_name -> state ) ) {
                    $loc_display .= ', ' . $location_name -> state;
                }
            }
            
            // Wind direction to compass
            $wind_directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
            $wind_dir = $wind_directions[ round( $wind_deg / 22.5 ) % 16 ];
            ?>

            <div class="uk-card uk-card-default">
                <div class="uk-card-header">
                    <div class="uk-flex uk-flex-between uk-flex-middle">
                        <h3 class="uk-card-title uk-margin-remove">
                            <span uk-icon="icon: location"></span>
                            <?php echo esc_html( $loc_display ); ?>
                        </h3>
                        <button type="button" class="uk-button uk-button-small uk-button-default sgu-weather-refresh" title="Refresh">
                            <span uk-icon="icon: refresh"></span>
                        </button>
                    </div>
                </div>

                <div class="uk-card-body">
                    <div uk-grid class="uk-grid-small uk-child-width-1-2@s">
                        
                        <!-- Main Temperature -->
                        <div class="uk-text-center">
                            <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $icon ); ?>@4x.png" 
                                 alt="<?php echo esc_attr( $description ); ?>"
                                 class="sgu-weather-icon"
                                 style="width: 150px; height: 150px;">
                            <div class="sgu-weather-temp" style="font-size: 4rem; font-weight: 700; line-height: 1;">
                                <?php echo esc_html( $temp ); ?>°F
                            </div>
                            <div class="sgu-weather-desc uk-text-lead">
                                <?php echo esc_html( $description ); ?>
                            </div>
                            <div class="uk-text-muted">
                                Feels like <?php echo esc_html( $feels_like ); ?>°F
                            </div>
                        </div>

                        <!-- Weather Details -->
                        <?php if( $show_details ) : ?>
                        <div>
                            <ul class="uk-list uk-list-divider">
                                <li>
                                    <span uk-icon="icon: cloud"></span>
                                    <strong>Humidity:</strong>
                                    <span class="sgu-weather-humidity"><?php echo esc_html( $humidity ); ?>%</span>
                                </li>
                                <li>
                                    <span uk-icon="icon: move"></span>
                                    <strong>Wind:</strong>
                                    <span class="sgu-weather-wind"><?php echo esc_html( $wind_speed ); ?> mph <?php echo esc_html( $wind_dir ); ?></span>
                                </li>
                                <li>
                                    <span uk-icon="icon: settings"></span>
                                    <strong>Pressure:</strong>
                                    <?php echo esc_html( $pressure ); ?> hPa
                                </li>
                                <?php if( $visibility ) : ?>
                                <li>
                                    <span uk-icon="icon: search"></span>
                                    <strong>Visibility:</strong>
                                    <?php echo esc_html( $visibility ); ?> mi
                                </li>
                                <?php endif; ?>
                                <li>
                                    <span uk-icon="icon: cloud"></span>
                                    <strong>Cloud Cover:</strong>
                                    <?php echo esc_html( $clouds ); ?>%
                                </li>
                                <?php if( $sunrise && $sunset ) : ?>
                                <li>
                                    <span uk-icon="icon: future"></span>
                                    <strong>Sunrise:</strong> <?php echo esc_html( $sunrise ); ?>
                                    <br>
                                    <span uk-icon="icon: future"></span>
                                    <strong>Sunset:</strong> <?php echo esc_html( $sunset ); ?>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="uk-card-footer uk-text-small uk-text-muted">
                    <span uk-icon="icon: clock; ratio: 0.8"></span>
                    Last updated: <?php echo esc_html( date( 'g:i A, M j', $weather -> dt ?? time() ) ); ?>
                    <span class="uk-float-right">Data: OpenWeather</span>
                </div>
            </div>

        <?php endif; ?>

    </div>

</div>