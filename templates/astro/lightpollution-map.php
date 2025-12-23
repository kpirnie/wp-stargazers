<?php
/**
 * Template: Light Pollution Map
 * 
 * Displays the configured map from Windy.com
 * 
 * @package US Star Gazers
 * @since 8.4
 * 
 * Available variables:
 * @var string $title The block title
 * @var bool $show_title Whether to show the title
 * @var bool $show_location_picker Whether to show location picker
 * @var int $max_height Number of pixels for the map height
 * @var string $wrapper_attr Extra wrapper attributes (in this case, class="")
 * @var float $latitude User's latitude
 * @var float $latitude User's longitude
 */

// Prevent direct access
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>

<?php if($show_title) : ?>
    <h2><?php echo esc_html( $title ); ?></h2>
<?php endif; ?>

<iframe <?php echo $wrapper_attr; ?> style="width:100%;height:<?php echo $max_height; ?>px;" src="//embed.windy.com/embed2.html?lat=<?php echo $latitude; ?>&lon=<?php echo $longitude; ?>&zoom=7&overlay=<?php echo $map_layer; ?>&pressure=true&detailLat=<?php echo $latitude; ?>&detailLon=<?php echo $longitude; ?>&metricTemp=°F&product=ecmwf&level=surface&message=true&type=map&location=coordinates&radarRange=-1&pressure=true" frameborder="0" loading="lazy"></iframe>
