<?php
/**
 * Weather Alerts Template
 * 
 * Displays active NOAA weather alerts for the user's location
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// Severity color mapping
$severity_colors = [
    'Extreme' => 'danger',
    'Severe' => 'danger',
    'Moderate' => 'warning',
    'Minor' => 'primary',
    'Unknown' => 'muted',
];

?>
<div class="sgu-weather-container sgu-weather-alerts-container" data-weather-type="alerts">
    
    <?php if( $title ) : ?>
    <h2 class="uk-heading-bullet uk-heading-divider"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if( $show_location_picker ) : ?>
        <?php include SGUP_PATH . '/templates/weather/location-picker.php'; ?>
    <?php endif; ?>

    <!-- Weather Alerts Content -->
    <div class="sgu-weather-content">
        
        <?php if( ! $has_location ) : ?>
            <div class="sgu-weather-no-location uk-alert uk-alert-primary">
                <p>Please set your location to view weather alerts.</p>
            </div>
        <?php elseif( empty( $alerts ) ) : ?>
            <div class="uk-alert uk-alert-success">
                <span uk-icon="icon: check"></span>
                No active weather alerts for your area.
            </div>
        <?php else : ?>
            
            <div class="uk-margin-bottom uk-text-muted">
                <span uk-icon="icon: warning"></span>
                <?php echo count( $alerts ); ?> active alert(s) for your area
            </div>

            <ul uk-accordion="multiple: true">
                <?php foreach( $alerts as $index => $alert ) : 
                    $severity = $alert -> severity ?? 'Unknown';
                    $color_class = $severity_colors[ $severity ] ?? 'muted';
                    $urgency = $alert -> urgency ?? '';
                    $event = $alert -> event ?? 'Weather Alert';
                    $headline = $alert -> headline ?? '';
                    $description = $alert -> description ?? '';
                    $instruction = $alert -> instruction ?? '';
                    $effective = $alert -> effective ? date( 'M j, g:i A', strtotime( $alert -> effective ) ) : '';
                    $expires = $alert -> expires ? date( 'M j, g:i A', strtotime( $alert -> expires ) ) : '';
                    $sender = $alert -> senderName ?? 'National Weather Service';
                ?>
                <li class="uk-open">
                    <a class="uk-accordion-title" href="#">
                        <div class="uk-flex uk-flex-between uk-flex-middle">
                            <span>
                                <span class="uk-label uk-label-<?php echo esc_attr( $color_class ); ?> uk-margin-small-right">
                                    <?php echo esc_html( $severity ); ?>
                                </span>
                                <?php echo esc_html( $event ); ?>
                            </span>
                            <?php if( $urgency ) : ?>
                            <span class="uk-text-small uk-text-muted">
                                <?php echo esc_html( $urgency ); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="uk-accordion-content">
                        
                        <?php if( $headline ) : ?>
                        <div class="uk-alert uk-alert-<?php echo esc_attr( $color_class ); ?>">
                            <strong><?php echo esc_html( $headline ); ?></strong>
                        </div>
                        <?php endif; ?>

                        <?php if( $effective || $expires ) : ?>
                        <div class="uk-margin-small-bottom uk-text-small">
                            <?php if( $effective ) : ?>
                            <span><strong>Effective:</strong> <?php echo esc_html( $effective ); ?></span>
                            <?php endif; ?>
                            <?php if( $effective && $expires ) : ?>
                            <span class="uk-margin-small-left uk-margin-small-right">|</span>
                            <?php endif; ?>
                            <?php if( $expires ) : ?>
                            <span><strong>Expires:</strong> <?php echo esc_html( $expires ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if( $description ) : ?>
                        <div class="uk-margin">
                            <h5 class="uk-margin-small-bottom">Description</h5>
                            <pre style="white-space: pre-wrap; font-family: inherit; background: #f8f8f8; padding: 15px; border-radius: 4px;"><?php echo esc_html( $description ); ?></pre>
                        </div>
                        <?php endif; ?>

                        <?php if( $instruction ) : ?>
                        <div class="uk-margin">
                            <h5 class="uk-margin-small-bottom">
                                <span uk-icon="icon: info"></span>
                                Instructions
                            </h5>
                            <div class="uk-alert uk-alert-warning">
                                <?php echo nl2br( esc_html( $instruction ) ); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="uk-text-small uk-text-muted uk-margin-top">
                            Source: <?php echo esc_html( $sender ); ?>
                        </div>

                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

        <?php endif; ?>

    </div>

</div>