<?php
/**
 * Weekly Weather Forecast Template
 * 
 * Displays 7-day forecast with NOAA detailed predictions
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>
<div class="sgu-weather-container sgu-weather-weekly-container" data-weather-type="weekly">
    
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
                <p>Please set your location to view the 7-day forecast.</p>
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

            <!-- OpenWeather Daily Forecast Grid -->
            <?php if( isset( $forecast -> daily ) && ! empty( $forecast -> daily ) ) : ?>
            <div class="uk-card uk-card-default uk-margin-bottom">
                <div class="uk-card-header">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: calendar"></span>
                        <?php echo esc_html( $days_to_show ); ?>-Day Forecast
                        <?php if( $loc_display ) : ?>
                        <small class="uk-text-muted"> — <?php echo esc_html( $loc_display ); ?></small>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <div uk-grid class="uk-grid-collapse uk-child-width-expand@m uk-text-center">
                        <?php 
                        $day_count = 0;
                        foreach( $forecast -> daily as $day ) : 
                            if( $day_count >= $days_to_show ) break;
                            $day_count++;
                            
                            $day_name = date( 'D', $day['dt'] );
                            $day_date = date( 'M j', $day['dt'] );
                            $day_high = round( $day['temp']['max'] );
                            $day_low = round( $day['temp']['min'] );
                            $day_icon = $day['weather'][0]['icon'] ?? '01d';
                            $day_desc = $day['weather'][0]['description'] ?? '';
                            $day_pop = isset( $day['pop'] ) ? round( $day['pop'] * 100 ) : 0;
                            
                            // Highlight today
                            $is_today = ( date( 'Y-m-d', $day['dt'] ) === date( 'Y-m-d' ) );
                            $day_class = $is_today ? 'uk-background-muted' : '';
                        ?>
                        <div class="<?php echo esc_attr( $day_class ); ?>" style="padding: 15px; border-right: 1px solid #e5e5e5;">
                            <div class="uk-text-bold <?php echo $is_today ? 'uk-text-primary' : ''; ?>">
                                <?php echo $is_today ? 'Today' : esc_html( $day_name ); ?>
                            </div>
                            <div class="uk-text-small uk-text-muted"><?php echo esc_html( $day_date ); ?></div>
                            <img src="https://openweathermap.org/img/wn/<?php echo esc_attr( $day_icon ); ?>@2x.png" 
                                 alt="<?php echo esc_attr( $day_desc ); ?>"
                                 style="width: 60px; height: 60px;">
                            <div>
                                <span class="uk-text-bold"><?php echo esc_html( $day_high ); ?>°</span>
                                <span class="uk-text-muted"><?php echo esc_html( $day_low ); ?>°</span>
                            </div>
                            <div class="uk-text-small" title="<?php echo esc_attr( ucfirst( $day_desc ) ); ?>">
                                <?php echo esc_html( ucfirst( $day_desc ) ); ?>
                            </div>
                            <?php if( $day_pop > 0 ) : ?>
                            <div class="uk-text-small uk-text-primary">
                                <span uk-icon="icon: cloud; ratio: 0.7"></span>
                                <?php echo esc_html( $day_pop ); ?>%
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="uk-card-footer uk-text-small uk-text-muted">
                    <span>Data: OpenWeather</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- NOAA Extended Forecast -->
            <?php if( $use_noaa && $noaa_forecast && ! empty( $noaa_forecast -> periods ) ) : ?>
            <div class="uk-card uk-card-default">
                <div class="uk-card-header">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: file-text"></span>
                        Extended Forecast
                    </h3>
                </div>
                <div class="uk-card-body">
                    <ul uk-accordion="multiple: true">
                        <?php foreach( $noaa_forecast -> periods as $index => $period ) : ?>
                        <li <?php echo $index < 2 ? 'class="uk-open"' : ''; ?>>
                            <a class="uk-accordion-title" href="#">
                                <div class="uk-flex uk-flex-between uk-flex-middle">
                                    <span>
                                        <?php if( ! empty( $period['icon'] ) ) : ?>
                                        <img src="<?php echo esc_url( $period['icon'] ); ?>" 
                                             alt=""
                                             style="width: 40px; height: 40px; vertical-align: middle; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo esc_html( $period['name'] ); ?>
                                    </span>
                                    <span class="uk-text-primary uk-text-bold">
                                        <?php echo esc_html( $period['temperature'] ); ?>°<?php echo esc_html( $period['temperatureUnit'] ); ?>
                                    </span>
                                </div>
                            </a>
                            <div class="uk-accordion-content">
                                <p class="uk-text-muted uk-margin-remove-bottom">
                                    <strong><?php echo esc_html( $period['shortForecast'] ); ?></strong>
                                </p>
                                <p>
                                    <?php echo esc_html( $period['detailedForecast'] ); ?>
                                </p>
                                <?php if( ! empty( $period['windSpeed'] ) || ! empty( $period['windDirection'] ) ) : ?>
                                <p class="uk-text-small uk-text-muted">
                                    <span uk-icon="icon: move; ratio: 0.8"></span>
                                    Wind: <?php echo esc_html( $period['windSpeed'] ?? '' ); ?> <?php echo esc_html( $period['windDirection'] ?? '' ); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="uk-card-footer uk-text-small uk-text-muted">
                    <span>Data: National Weather Service</span>
                    <?php if( ! empty( $noaa_forecast -> generatedAt ) ) : ?>
                    <span class="uk-float-right">
                        Updated: <?php echo esc_html( date( 'M j, g:i A', strtotime( $noaa_forecast -> generatedAt ) ) ); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>