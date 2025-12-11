<?php
/**
 * Daily Weather Forecast Template
 * 
 * Displays today's detailed forecast with hourly breakdown
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>
<div class="sgu-weather-container sgu-weather-daily-container" data-weather-type="daily">
    
    <h2 class="uk-heading-bullet uk-heading-divider"><?php echo esc_html( $title ); ?></h2>

    <?php if( $show_location_picker ) : ?>
        <?php include SGUP_PATH . '/templates/weather/location-picker.php'; ?>
    <?php endif; ?>

    <!-- Loading State -->
    <div class="sgu-weather-loading" style="display:none;">
        <div class="uk-text-center uk-padding">
            <div uk-spinner="ratio: 2"></div>
            <p class="uk-margin-small-top">Loading forecast...</p>
        </div>
    </div>

    <!-- Weather Content -->
    <div class="sgu-weather-content">
        
        <?php if( ! $has_location ) : ?>
            <div class="sgu-weather-no-location uk-alert uk-alert-primary">
                <p>Please set your location to view the forecast.</p>
            </div>
        <?php elseif( ! $forecast ) : ?>
            <div class="sgu-weather-error uk-alert uk-alert-warning">
                <p>Unable to retrieve forecast data. Please try again later.</p>
            </div>
        <?php else : ?>

            <?php
            // Location name
            $loc_display = '';
            if( $location_name ) {
                $loc_display = $location_name -> name;
                if( ! empty( $location_name -> state ) ) {
                    $loc_display .= ', ' . $location_name -> state;
                }
            }
            ?>

            <!-- NOAA Detailed Forecast -->
            <?php if( $use_noaa && $noaa_forecast && ! empty( $noaa_forecast -> periods ) ) : ?>
            <div class="uk-card uk-card-default uk-margin-bottom">
                <div class="uk-card-header">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: file-text"></span>
                        Detailed Forecast
                        <?php if( $loc_display ) : ?>
                        <small class="uk-text-muted"> — <?php echo esc_html( $loc_display ); ?></small>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="uk-card-body">
                    <?php 
                    // Show first 2 periods (today and tonight)
                    $shown = 0;
                    foreach( $noaa_forecast -> periods as $period ) : 
                        if( $shown >= 2 ) break;
                        $shown++;
                    ?>
                    <div class="uk-margin-bottom">
                        <div class="uk-flex uk-flex-middle">
                            <?php if( ! empty( $period['icon'] ) ) : ?>
                            <img src="<?php echo esc_url( $period['icon'] ); ?>" 
                                 alt="<?php echo esc_attr( $period['shortForecast'] ); ?>"
                                 class="uk-margin-small-right"
                                 style="width: 80px; height: 80px;">
                            <?php endif; ?>
                            <div>
                                <h4 class="uk-margin-remove">
                                    <?php echo esc_html( $period['name'] ); ?>
                                    <span class="uk-text-primary"><?php echo esc_html( $period['temperature'] ); ?>°<?php echo esc_html( $period['temperatureUnit'] ); ?></span>
                                </h4>
                                <p class="uk-margin-remove uk-text-muted">
                                    <?php echo esc_html( $period['shortForecast'] ); ?>
                                </p>
                            </div>
                        </div>
                        <p class="uk-margin-small-top">
                            <?php echo esc_html( $period['detailedForecast'] ); ?>
                        </p>
                        <?php if( $shown < 2 ) : ?>
                        <hr>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="uk-card-footer uk-text-small uk-text-muted">
                    <span>Data: National Weather Service</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Hourly Forecast -->
            <?php if( $show_hourly && isset( $forecast -> hourly ) && ! empty( $forecast -> hourly ) ) : ?>
            <div class="uk-card uk-card-default">
                <div class="uk-card-header">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: clock"></span>
                        Hourly Forecast
                    </h3>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <div class="uk-overflow-auto">
                        <div class="uk-flex uk-flex-nowrap" style="padding: 15px;">
                            <?php 
                            $hour_count = 0;
                            foreach( $forecast -> hourly as $hour ) : 
                                if( $hour_count >= $hours_to_show ) break;
                                $hour_count++;
                                
                                $hour_time = date( 'g A', $hour['dt'] );
                                $hour_temp = round( $hour['temp'] );
                                $hour_icon = $hour['weather'][0]['icon'] ?? '01d';
                                $hour_desc = $hour['weather'][0]['description'] ?? '';
                                $hour_pop = isset( $hour['pop'] ) ? round( $hour['pop'] * 100 ) : 0;
                            ?>
                            <div class="uk-text-center uk-margin-small-right" style="min-width: 80px;">
                                <div class="uk-text-small uk-text-muted"><?php echo esc_html( $hour_time ); ?></div>
                                <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $hour_icon ); ?>.png" 
                                     alt="<?php echo esc_attr( $hour_desc ); ?>"
                                     style="width: 50px; height: 50px;">
                                <div class="uk-text-bold"><?php echo esc_html( $hour_temp ); ?>°</div>
                                <?php if( $hour_pop > 0 ) : ?>
                                <div class="uk-text-small uk-text-primary">
                                    <span uk-icon="icon: cloud; ratio: 0.7"></span>
                                    <?php echo esc_html( $hour_pop ); ?>%
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="uk-card-footer uk-text-small uk-text-muted">
                    <span>Data: OpenWeather</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Current Day Summary from OpenWeather -->
            <?php if( isset( $forecast -> daily ) && ! empty( $forecast -> daily ) ) : ?>
            <?php 
            $today = $forecast -> daily[0];
            $today_high = round( $today['temp']['max'] ?? 0 );
            $today_low = round( $today['temp']['min'] ?? 0 );
            $today_sunrise = date( 'g:i A', $today['sunrise'] ?? 0 );
            $today_sunset = date( 'g:i A', $today['sunset'] ?? 0 );
            $today_uvi = $today['uvi'] ?? 0;
            $today_summary = $today['summary'] ?? '';
            ?>
            <div class="uk-card uk-card-secondary uk-card-small uk-margin-top">
                <div class="uk-card-body">
                    <div uk-grid class="uk-grid-small uk-child-width-auto uk-flex-center">
                        <div>
                            <span uk-icon="icon: arrow-up"></span>
                            High: <strong><?php echo esc_html( $today_high ); ?>°F</strong>
                        </div>
                        <div>
                            <span uk-icon="icon: arrow-down"></span>
                            Low: <strong><?php echo esc_html( $today_low ); ?>°F</strong>
                        </div>
                        <div>
                            <span uk-icon="icon: world"></span>
                            UV Index: <strong><?php echo esc_html( $today_uvi ); ?></strong>
                        </div>
                        <div>
                            <span uk-icon="icon: future"></span>
                            Sunrise: <strong><?php echo esc_html( $today_sunrise ); ?></strong>
                        </div>
                        <div>
                            <span uk-icon="icon: future"></span>
                            Sunset: <strong><?php echo esc_html( $today_sunset ); ?></strong>
                        </div>
                    </div>
                    <?php if( $today_summary ) : ?>
                    <p class="uk-margin-small-top uk-margin-remove-bottom uk-text-center">
                        <?php echo esc_html( $today_summary ); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>