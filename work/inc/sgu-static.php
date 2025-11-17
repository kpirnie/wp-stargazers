<?php
/** 
 * Static Class
 * 
 * This class will contain the static methods
 * 
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package US Stargazers Plugin
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_Static' ) ) {

    /** 
     * Class SGU_Static
     * 
     * @since 8.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package US Stargazers Plugin
     * 
    */
    class SGU_Static {

        /** 
         * get_cpt_display_name
         * 
         * get our cpt's display name
         * 
         * @since 8.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package US Stargazers Plugin
         * 
        */
        public static function get_cpt_display_name( string $_cpt ) : string {

            // just hold the CPT and display name in an array
            $_cpts = array(
                'page' => 'Pages',
                'post' => 'Articles',
                'sgu_cme_alerts' => 'Coronal Mass Ejection',
                'sgu_sw_alerts' => 'Space Weather',
                'sgu_geo_alerts' => 'Geo Magnetic Storm',
                'sgu_sf_alerts' => 'Solar Flare',
                'sgu_neo' => 'Near Earth Objects', 
                'sgu_journal' => 'Photo Journals', 
                'sgu_apod' => 'Astronomy Photos of the Day',
            );

            // return the display name
            return $_cpts[ $_cpt ] ?: 'Invalid Post Type';

        }


    }

}
