<?php
/*
Plugin Name: Benyon Calendar
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to view a public Google Calendar
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: GPL2
*/

define( 'BENYON_CAL_PLUGIN_FILE_PATH', __FILE__);
define( 'BENYON_CAL_PATH_PLUGIN_BASE', __DIR__);
define( 'BENYON_CAL_INCLUDES', BENYON_CAL_PATH_PLUGIN_BASE . '/inc');

try {
    $result = file_get_contents("https://www.googleapis.com/calendar/v3/calendars/" . get_field('benyon_cal_id', 'option') . "/events?key=" . get_field('benyon_cal_api_key', 'option'));
} catch(Exception $e) {
    error_log("FAIL!");
    $result = false;
}

error_log("result" . var_export($result, true));

include BENYON_CAL_INCLUDES . '/options_page.php';
