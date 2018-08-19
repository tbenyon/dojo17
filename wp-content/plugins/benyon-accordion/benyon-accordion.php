<?php
/*
Plugin Name: Benyon Accordion
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to generate the accordion content section
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: UNLICENSED
*/

define( 'BENYON_ACC_PLUGIN_FILE_PATH', __FILE__);
define( 'BENYON_ACC_PATH_PLUGIN_BASE', __DIR__);
define( 'BENYON_ACC_INCLUDES', BENYON_ACC_PATH_PLUGIN_BASE . '/inc');
define( 'BENYON_ACC_VIEWS', BENYON_ACC_PATH_PLUGIN_BASE . '/views');

include BENYON_ACC_INCLUDES . '/include_styles_and_scripts.php';
include BENYON_ACC_INCLUDES . '/register_content_sections.php';
