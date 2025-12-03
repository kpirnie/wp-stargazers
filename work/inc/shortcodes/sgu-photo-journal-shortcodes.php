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

           // CHECK IF WE'RE VIEWING A SINGLE POST VIA QUERY VAR
            $journal_slug = get_query_var( 'sgu_journal' );
            
            // DEBUG: Uncomment these lines to see what's happening
            error_log( 'Journal slug from query var: ' . $journal_slug );
            error_log( 'All query vars: ' . print_r( $GLOBALS['wp_query']->query_vars, true ) );
            
            if( ! empty( $journal_slug ) ) {
                return $this -> render_single_journal_by_slug( $journal_slug );
            }

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

            // hold the output
            $out = [];

            // hold the max pages
            $max = $photo_journals -> max_num_pages ?: 1;

            // if we're showing the paging links, and it's either top or both
            if( $show_pagination && in_array( $paging_loc, ['top', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $pjpaged );
            }

            // hold an index
            $idx = 0;

            // loop the journals
            foreach( $photo_journals -> posts as $journal) {

                // setup the necessary data
                $id = $journal -> ID;
                $title = esc_html( $journal -> post_title );
                $content = wp_trim_words( $journal -> post_content, 30 );
                $img = ( function( ) use( $id ) {
                    $_local_media = get_post_meta( $id, 'sgu_journal_local_image', true );
                    if( $_local_media ) {
                        $_img_id = SGU_Static::get_attachment_id( $_local_media );
                        return esc_url( wp_get_attachment_image_url( $_img_id, 'pageimages' ) );
                    } else {
                        return esc_url( get_post_meta( $id, 'sgu_journal_orignal_image', true ) );
                    }
                } )( );
                $side = ( $idx % 2 == 0 ) ? 'left' : 'right uk-flex-last@s';
                
                // USE THE STATIC METHOD TO GET THE LINK WITH SHORTCODE NAME
                $base_url = SGU_Static::get_archive_url( 'sgup_photo_journals' );
                $link = esc_url( rtrim( $base_url, '/' ) . '/' . $journal -> post_name . '/' );

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

                ++$idx;
            }

            // if we're showing the paging links, and it's either bottom or both
            if( $show_pagination && in_array( $paging_loc, ['bottom', 'both'] ) ) {
                $out[] = SGU_Static::cpt_pagination( $max, $pjpaged );
            }

            return implode( '', $out );
        }

        /**
         * Render single photo journal by slug
         */
        private function render_single_journal_by_slug( string $slug ) : string {
            
            // Get the post by slug
            $args = [
                'name' => $slug,
                'post_type' => 'sgu_journal',
                'posts_per_page' => 1,
            ];
            
            $query = new WP_Query( $args );
            
            if( ! $query -> have_posts() ) {
                return '<p>Photo journal not found.</p>';
            }
            
            $post = $query -> posts[0];
            
            $title = esc_html( $post -> post_title );
            $content = apply_filters( 'the_content', $post -> post_content );
            
            $img = ( function( ) use( $post ) {
                $_local_media = get_post_meta( $post -> ID, 'sgu_journal_local_image', true );
                if( $_local_media ) {
                    $_img_id = SGU_Static::get_attachment_id( $_local_media );
                    return esc_url( wp_get_attachment_image_url( $_img_id, 'full' ) );
                } else {
                    return esc_url( get_post_meta( $post -> ID, 'sgu_journal_orignal_image', true ) );
                }
            } )( );

            // USE THE STATIC METHOD TO GET THE BACK LINK WITH SHORTCODE NAME
            $back_link = esc_url( SGU_Static::get_archive_url( 'sgup_photo_journals' ) );

            return <<<HTML
            <article class="sgu-journal-single">
                <h1 class="uk-heading-divider">$title</h1>
                <div class="uk-margin-large">
                    <img src="$img" alt="$title" class="uk-width-1-1">
                </div>
                <div class="uk-margin-large">
                    $content
                </div>
                <div class="uk-margin-large">
                    <a href="$back_link" class="uk-button uk-button-secondary">Back to Photo Journals</a>
                </div>
            </article>
            HTML;
        }

    }

}
