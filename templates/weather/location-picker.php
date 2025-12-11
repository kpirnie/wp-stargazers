<?php
/**
 * Weather Location Picker Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// Determine display state
$prompt_class = $has_location ? '' : 'active';
$compact_class = $compact ? 'sgu-weather-location-compact' : '';

?>
<div class="sgu-weather-location-picker <?php echo esc_attr( $compact_class ); ?>">
    
    <?php if( ! $compact ) : ?>
    <h3 class="uk-heading-bullet"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <!-- Current Location Display -->
    <div class="sgu-weather-has-location" <?php echo $has_location ? '' : 'style="display:none;"'; ?>>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <div>
                <span uk-icon="icon: location"></span>
                <span class="sgu-weather-location-name">
                    <?php 
                    if( $location ) {
                        echo esc_html( $location -> name );
                        if( ! empty( $location -> state ) ) {
                            echo ', ' . esc_html( $location -> state );
                        }
                    }
                    ?>
                </span>
            </div>
            <button type="button" class="uk-button uk-button-small uk-button-default sgu-weather-change-location">
                Change Location
            </button>
        </div>
    </div>

    <!-- Location Prompt -->
    <div class="sgu-weather-location-prompt <?php echo esc_attr( $prompt_class ); ?>">
        <div class="uk-card uk-card-default uk-card-body uk-card-small">
            
            <p class="uk-text-muted">Set your location to see local weather data.</p>

            <!-- Geolocation Button -->
            <div class="uk-margin">
                <button type="button" class="uk-button uk-button-primary uk-width-1-1 sgu-weather-geolocate">
                    <span uk-icon="icon: location"></span>
                    Use My Current Location
                </button>
            </div>

            <div class="uk-text-center uk-margin">
                <span class="uk-text-muted">— or —</span>
            </div>

            <!-- ZIP Code Form -->
            <form class="sgu-weather-zip-form uk-form-stacked">
                <div class="uk-margin">
                    <label class="uk-form-label" for="sgu-weather-zip">Enter ZIP Code</label>
                    <div class="uk-form-controls uk-flex">
                        <input type="text" 
                               class="uk-input sgu-weather-zip-input" 
                               id="sgu-weather-zip"
                               placeholder="12345"
                               maxlength="5"
                               pattern="[0-9]{5}"
                               inputmode="numeric"
                               required>
                        <button type="submit" class="uk-button uk-button-secondary uk-margin-small-left">
                            Find
                        </button>
                    </div>
                </div>
            </form>

            <?php if( $has_location ) : ?>
            <div class="uk-text-center uk-margin-top">
                <button type="button" class="uk-button uk-button-text sgu-weather-cancel-change">
                    Cancel
                </button>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>