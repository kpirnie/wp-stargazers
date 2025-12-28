<?php
/**
 * Template: Star Chart
 * 
 * @package US Star Gazers
 * @since 8.4
 * 
 * @var string $title
 * @var bool $show_title
 * @var bool $show_location_picker
 * @var string $wrapper_attr
 * @var bool $has_location
 * @var object|null $location
 * @var string $location_name
 * @var float $latitude
 * @var float $longitude
 * @var string|null $star_chart_url
 * @var bool $has_credentials
 */

defined( 'ABSPATH' ) || die( 'No direct script access allowed' );
var_dump($star_chart_url);
?>

<div class="sgu-star-chart-container" <?php echo $wrapper_attr; ?>>

    <?php if ( $show_title && ! empty( $title ) ) : ?>
        <h2 class="sgu-star-chart-title"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if ( $show_location_picker ) : ?>
        <?php 
        $theme_template = locate_template( [
            'templates/weather/partials/location-picker-inline.php',
            'sgu/weather/partials/location-picker-inline.php',
        ] );
        $partial = $theme_template ?: SGUP_PATH . '/templates/weather/partials/location-picker-inline.php';
        if ( file_exists( $partial ) ) {
            include $partial;
        }
        ?>
    <?php endif; ?>

    <?php if ( ! $has_credentials ) : ?>
        <div class="sgu-star-chart-notice">
            <p>Configure AstronomyAPI credentials to display star chart.</p>
        </div>
    <?php elseif ( $star_chart_url ) : ?>
        <div class="sgu-star-chart-card">
            <img 
                src="<?php echo esc_url( $star_chart_url ); ?>" 
                alt="Star Chart" 
                class="sgu-star-chart-image"
                loading="lazy"
            />
        </div>
    <?php else : ?>
        <p class="sgu-star-chart-error">Unable to retrieve star chart.</p>
    <?php endif; ?>

</div>