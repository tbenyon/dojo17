<?php
/*
Plugin Name: Benyon Content Sections
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to view generate the content sections system
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: UNLICENSED
*/

define('BENYON_CS_BASE', __DIR__);
define('BENYON_CS_VENDOR', BENYON_CS_BASE . '/vendor');
define('BENYON_CS_INC', BENYON_CS_BASE .'/includes');
define('BENYON_CS_VIEWS', BENYON_CS_BASE .'/views');

define('BENYON_CS_VIEW_OPTION_BASE', 'benyon_cs_view_path_');

require BENYON_CS_VENDOR . '/autoload.php';

add_action( 'init', 'benyon_cs_add_field_group' );
function benyon_cs_add_field_group() {
    if( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group(BenyonContentSections\Content_Sections::instance()->build_content_sections());
    }
}
