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

// setup the URL
$url = sprintf( "" );
?>

<?php if($show_title) : ?>
    <h2><?php echo esc_html( $title ); ?></h2>
<?php endif; ?>

<iframe <?php echo $wrapper_attr; ?> style="width:100%;height:<?php echo $max_height; ?>px;" src="<?php echo $url; ?>" frameborder="0" loading="lazy"></iframe>
