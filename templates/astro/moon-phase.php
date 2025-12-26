<?php
/**
 * Template: Moon Phase
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
 * @var string|null $moon_phase_url
 * @var bool $has_credentials
 */

defined( 'ABSPATH' ) || die( 'No direct script access allowed' );
?>

<div class="sgu-moon-phase-container" <?php echo $wrapper_attr; ?>>

    <?php if ( $show_title && ! empty( $title ) ) : ?>
        <h2 class="sgu-moon-phase-title"><?php echo esc_html( $title ); ?></h2>
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
        <div class="sgu-moon-phase-notice">
            <p>Configure AstronomyAPI credentials to display moon phase image.</p>
        </div>
    <?php elseif ( $moon_phase_url ) : ?>
        <div class="sgu-moon-phase-card">
            <img 
                src="<?php echo esc_url( $moon_phase_url ); ?>" 
                alt="Current Moon Phase" 
                class="sgu-moon-phase-image"
                loading="lazy"
            />
        </div>
    <?php else : ?>
        <p class="sgu-moon-phase-error">Unable to retrieve moon phase image.</p>
    <?php endif; ?>

</div>