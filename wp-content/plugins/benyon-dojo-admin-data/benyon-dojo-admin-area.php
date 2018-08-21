<?php
/*
Plugin Name: Dojo Admin
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to show data to Mentors
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: UNLICENSED
*/

define( 'BENYON_DOJO_ADMIN_PLUGIN_FILE_PATH', __FILE__);
define( 'BENYON_DOJO_ADMIN_PATH_PLUGIN_BASE', __DIR__);
define( 'BENYON_DOJO_ADMIN_INCLUDES', BENYON_DOJO_ADMIN_PATH_PLUGIN_BASE . '/inc');
define( 'BENYON_DOJO_ADMIN_VIEWS', BENYON_DOJO_ADMIN_PATH_PLUGIN_BASE . '/views');
define( 'BENYON_DOJO_ADMIN_DATA', BENYON_DOJO_ADMIN_PATH_PLUGIN_BASE . '/data');
define( 'BENYON_DOJO_MENU_SLUG', 'benyon-dojo-admin');

require BENYON_DOJO_ADMIN_INCLUDES . '/create_options_page.php';
require BENYON_DOJO_ADMIN_INCLUDES . '/enqueue.php';
require BENYON_DOJO_ADMIN_INCLUDES . '/query.php';
