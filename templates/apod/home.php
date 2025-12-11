<?php
/**
 * APOD Shortcode Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

?>
<h1><?php _e( $block_title, 'sgup' ); ?></h1>
<h2><?php _e( $title, 'sgup' ); ?></h2>
<div>
    <?php
    //if it's an image
    if( $meta['sgu_apod_local_media_type'][0] == 'image' ) {
        echo '<img src="' . $meta['sgu_apod_local_media'][0] . '" style="max-height:315px;height:315px;float:right;margin:0 10px;" class="parallax" />';
    } else {
        echo '<iframe width="560" height="315" src="' . $meta['sgu_apod_orignal_media'][0] . '" frameborder="0" allowfullscreen></iframe>';
    }
    ?>

    <?php _e( $content, 'sgup' ); ?>

</div>
<p>Copyright &copy; <?php echo $meta['sgu_apod_copyright'][0]; ?></p>
