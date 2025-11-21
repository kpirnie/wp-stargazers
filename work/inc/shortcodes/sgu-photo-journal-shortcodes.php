<?php
/** 
 * Alerts Shortcodes Class
 * 
 * This class will control the alert shortcodes and their rendering
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Photo_Journal_Shortcodes' ) ) {

    /** 
     * Class SGU_Photo_Journal_Shortcodes
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Photo_Journal_Shortcodes {

        // hold the internals
        private int $paged;
        private SGU_Space_Data $space_data;

        /** 
         * init
         * 
         * Initialize the class
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function init( ): void { 

            // setup the internal paged
            $this -> paged = SGU_Static::safe_get_paged_var( ) ?: 1;

            // setup the internal data class (single instance)
            $this -> space_data = new SGU_Space_Data( );

            // create the shortcodes
            add_shortcode( 'sgup_photo_journals', [ $this, 'add_photo_journals' ] );

        }

        /** 
         * add_photo_journals
         * 
         * Add in the photo journal's shortcode
         * This will control both the listing page and the single
         * article page
         * 
         * @since 8.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public function add_photo_journals( array $atts = [] ) : string {

            // Set default values
            $atts = shortcode_atts( [
                'show_paging' => false,
                'paging_location' => 'bottom',
                'per_page' => 6,
            ], $atts, 'sgup_photo_journals' );

            // show the pagination links?
            $show_pagination = filter_var( $atts['show_paging'], FILTER_VALIDATE_BOOLEAN );

            // hold the paging location
            $paging_loc = sanitize_text_field( $atts['paging_location'] );

            // how many per page
            $per_page = absint( $atts['per_page'] ) ?: 6;

            // setup the paged 
            $pjpaged = SGU_Static::safe_get_paged_var( ) ?: 1;

            // grab the photo journals
            $photo_journals = $this -> space_data -> get_photojournals( $pjpaged );

            // if we don't have anything, just dump out
            if( ! $photo_journals ) { return ''; }

            var_dump(is_single());

            // hold the max pages
            $max = $photo_journals -> max_num_pages ?: 1;

            // if we're showing the paging links, and it's either top or both
            if( $show_pagination && in_array( $paging_loc, ['top', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $pjpaged );
            }

            // hold an index
            $idx = 0;

            // loop the journals
            $max = $photo_journals -> max_num_pages ?: 1;
            foreach( $photo_journals -> posts as $journal) {

                // settup the necessary data
                $id = $journal -> ID;
                $title = $journal -> post_title;
                $content = wp_trim_words( $journal -> post_content, 30 );
                $img = ( function( ) use( $id ) {
                    // hold the local media/image
                    $_local_media = get_post_meta( $id, 'sgu_journal_local_image', true );
                    // let's see if we have a local image
                    if( $_local_media ) {
                        // get the image ID
                        $_img_id = SGU_Static::get_attachment_id( $_local_media );
                        // return the resized image for display
                        return esc_url( wp_get_attachment_image_url( $_img_id, 'pageimages' ) );
                    // we don't
                    } else {
                        // return the remote one instead
                        return esc_url( get_post_meta( $id, 'sgu_journal_orignal_image', true ) );
                    }
                } )( );
                $side = ( $idx % 2 == 0 ) ? 'left' : 'right uk-flex-last@s';
                $link = get_the_permalink( $id );//var_dump($img);exit;

                // render the card
                $out[] = <<<HTML
                <div class="uk-card uk-grid-collapse uk-child-width-1-2@s uk-margin-large" uk-grid>
                    <div class="uk-card-media-$side uk-cover-container">
                        <img src="$img" alt="$title" uk-cover uk-img="loading: lazy">
                    </div>
                    <div>
                        <div class="uk-card-body">
                            <h2 class="uk-card-title">$title</h2>
                            $content
                            <p class="uk-text-right">
                                <a href="$link" class="uk-button uk-button-secondary uk-border" title="$title">Read More...</a>
                            </p>
                        </div>
                    </div>
                </div>
                HTML;

                // increment our index
                ++$idx;
            }

            // if we're showing the paging links, and it's either bottom or both
            if( $show_pagination && in_array( $paging_loc, ['bottom', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $pjpaged );
            }

            // return the output
            return implode( '', $out );

        }

    }

}
