<?php
/**
 * Plugin Menu Template
 * 
 * @package US Stargazers Plugin
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// return the html string
echo <<<HTML
<nav class="uk-navbar-container uk-navbar-transparent uk-margin-bottom uk-overflow-auto" uk-navbar>
    <div class="uk-navbar-center">
        <ul class="uk-navbar-nav page-nav-divider">
            $the_menu
        </ul>
    </div>
</nav>
HTML;
