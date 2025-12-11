<?php
/**
 * Full Weather Dashboard Template
 * 
 * Comprehensive weather display combining all components
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>
<div class="sgu-weather-dashboard">
    
    <h2 class="uk-heading-bullet uk-heading-divider"><?php echo esc_html( $title ); ?></h2>

    <!-- Location Picker -->
    <?php 
    $compact = true;
    include SGUP_PATH . '/templates/weather/location-picker.php'; 
    ?>

    <?php if( ! $has_location ) : ?>
        <div class="sgu-weather-no-location uk-alert uk-alert-primary uk-margin-top">
            <p>Please set your location above to view weather data.</p>
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

        <!-- Weather Alerts (if any) -->
        <?php if( $show_alerts && ! empty( $alerts ) ) : ?>
        <div class="uk-margin-top">
            <?php 
            // Include alerts template
            $title = '';
            $show_location_picker = false;
            $max_alerts = 3;
            include SGUP_PATH . '/templates/weather/alerts.php';
            ?>
        </div>
        <?php endif; ?>

        <!-- Current Weather + Daily Overview -->
        <div uk-grid class="uk-grid-match uk-child-width-1-2@m uk-margin-top">
            
            <?php if( $show_current && $current_weather ) : ?>
            <!-- Current Conditions -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: cloud"></span>
                        Current Conditions
                        <?php if( $loc_display ) : ?>
                        <small class="uk-text-muted uk-text-small"> — <?php echo esc_html( $loc_display ); ?></small>
                        <?php endif; ?>
                    </h3>
                    
                    <?php
                    $temp = round( $current_weather -> main -> temp ?? 0 );
                    $feels_like = round( $current_weather -> main -> feels_like ?? 0 );
                    $humidity = $current_weather -> main -> humidity ?? 0;
                    $wind_speed = round( $current_weather -> wind -> speed ?? 0 );
                    $description = ucfirst( $current_weather -> weather[0] -> description ?? '' );
                    $icon = $current_weather -> weather[0] -> icon ?? '01d';
                    ?>
                    
                    <div class="uk-flex uk-flex-middle">
                        <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $icon ); ?>@2x.png" 
                             alt="<?php echo esc_attr( $description ); ?>"
                             style="width: 100px; height: 100px;">
                        <div>
                            <div style="font-size: 3rem; font-weight: 700; line-height: 1;">
                                <?php echo esc_html( $temp ); ?>°F
                            </div>
                            <div class="uk-text-muted">
                                <?php echo esc_html( $description ); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div uk-grid class="uk-grid-small uk-child-width-1-3 uk-margin-top uk-text-center">
                        <div>
                            <div class="uk-text-muted uk-text-small">Feels Like</div>
                            <div class="uk-text-bold"><?php echo esc_html( $feels_like ); ?>°F</div>
                        </div>
                        <div>
                            <div class="uk-text-muted uk-text-small">Humidity</div>
                            <div class="uk-text-bold"><?php echo esc_html( $humidity ); ?>%</div>
                        </div>
                        <div>
                            <div class="uk-text-muted uk-text-small">Wind</div>
                            <div class="uk-text-bold"><?php echo esc_html( $wind_speed ); ?> mph</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if( $show_noaa && $noaa_forecast && ! empty( $noaa_forecast -> periods ) ) : ?>
            <!-- Today's NOAA Forecast -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: file-text"></span>
                        Today's Forecast
                    </h3>
                    
                    <?php $period = $noaa_forecast -> periods[0]; ?>
                    <div class="uk-flex uk-flex-middle">
                        <?php if( ! empty( $period['icon'] ) ) : ?>
                        <img src="<?php echo esc_url( $period['icon'] ); ?>" 
                             alt=""
                             style="width: 80px; height: 80px; margin-right: 15px;">
                        <?php endif; ?>
                        <div>
                            <div class="uk-text-bold">
                                <?php echo esc_html( $period['name'] ); ?>: 
                                <span class="uk-text-primary"><?php echo esc_html( $period['temperature'] ); ?>°<?php echo esc_html( $period['temperatureUnit'] ); ?></span>
                            </div>
                            <div class="uk-text-muted">
                                <?php echo esc_html( $period['shortForecast'] ); ?>
                            </div>
                        </div>
                    </div>
                    
                    <p class="uk-margin-top uk-text-small">
                        <?php echo esc_html( $period['detailedForecast'] ); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Hourly Forecast -->
        <?php if( $show_hourly && $hourly_forecast && isset( $hourly_forecast -> hourly ) ) : ?>
        <div class="uk-card uk-card-default uk-margin-top">
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
                        foreach( $hourly_forecast -> hourly as $hour ) : 
                            if( $hour_count >= 24 ) break;
                            $hour_count++;
                            
                            $hour_time = date( 'g A', $hour['dt'] );
                            $hour_temp = round( $hour['temp'] );
                            $hour_icon = $hour['weather'][0]['icon'] ?? '01d';
                            $hour_pop = isset( $hour['pop'] ) ? round( $hour['pop'] * 100 ) : 0;
                        ?>
                        <div class="uk-text-center uk-margin-small-right" style="min-width: 70px;">
                            <div class="uk-text-small uk-text-muted"><?php echo esc_html( $hour_time ); ?></div>
                            <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $hour_icon ); ?>.png" 
                                 style="width: 40px; height: 40px;">
                            <div class="uk-text-bold"><?php echo esc_html( $hour_temp ); ?>°</div>
                            <?php if( $hour_pop > 0 ) : ?>
                            <div class="uk-text-small uk-text-primary"><?php echo esc_html( $hour_pop ); ?>%</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 7-Day Forecast -->
        <?php if( $show_daily && $daily_forecast && isset( $daily_forecast -> daily ) ) : ?>
        <div class="uk-card uk-card-default uk-margin-top">
            <div class="uk-card-header">
                <h3 class="uk-card-title">
                    <span uk-icon="icon: calendar"></span>
                    7-Day Forecast
                </h3>
            </div>
            <div class="uk-card-body uk-padding-remove">
                <ul class="uk-list uk-list-divider uk-margin-remove">
                    <?php 
                    $day_count = 0;
                    foreach( $daily_forecast -> daily as $day ) : 
                        if( $day_count >= 7 ) break;
                        $day_count++;
                        
                        $is_today = ( date( 'Y-m-d', $day['dt'] ) === date( 'Y-m-d' ) );
                        $day_name = $is_today ? 'Today' : date( 'l', $day['dt'] );
                        $day_date = date( 'M j', $day['dt'] );
                        $day_high = round( $day['temp']['max'] );
                        $day_low = round( $day['temp']['min'] );
                        $day_icon = $day['weather'][0]['icon'] ?? '01d';
                        $day_desc = ucfirst( $day['weather'][0]['description'] ?? '' );
                        $day_pop = isset( $day['pop'] ) ? round( $day['pop'] * 100 ) : 0;
                    ?>
                    <li class="uk-padding-small <?php echo $is_today ? 'uk-background-muted' : ''; ?>">
                        <div class="uk-flex uk-flex-middle uk-flex-between">
                            <div class="uk-width-1-4">
                                <div class="uk-text-bold <?php echo $is_today ? 'uk-text-primary' : ''; ?>">
                                    <?php echo esc_html( $day_name ); ?>
                                </div>
                                <div class="uk-text-small uk-text-muted"><?php echo esc_html( $day_date ); ?></div>
                            </div>
                            <div class="uk-width-1-4 uk-text-center">
                                <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $day_icon ); ?>.png" 
                                     style="width: 40px; height: 40px;">
                            </div>
                            <div class="uk-width-1-4 uk-text-center">
                                <span class="uk-text-bold"><?php echo esc_html( $day_high ); ?>°</span>
                                <span class="uk-text-muted">/<?php echo esc_html( $day_low ); ?>°</span>
                            </div>
                            <div class="uk-width-1-4 uk-text-right">
                                <div class="uk-text-small"><?php echo esc_html( $day_desc ); ?></div>
                                <?php if( $day_pop > 0 ) : ?>
                                <div class="uk-text-small uk-text-primary"><?php echo esc_html( $day_pop ); ?>% rain</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="uk-margin-top uk-text-small uk-text-muted uk-text-center">
            Weather data from OpenWeather and National Weather Service
            <br>
            <button type="button" class="uk-button uk-button-text sgu-weather-refresh">
                <span uk-icon="icon: refresh; ratio: 0.8"></span>
                Refresh Data
            </button>
        </div>

    <?php endif; ?>

</div>