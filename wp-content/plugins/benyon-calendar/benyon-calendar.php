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

error_log("result" . var_export($result, true));

include BENYON_CAL_INCLUDES . '/options_page.php';


// register My_Widget
add_action( 'widgets_init', function(){
    register_widget( 'My_Widget' );
});

class My_Widget extends WP_Widget {
    public $rawData;

    // class constructor
    public function __construct() {
        $widget_ops = array(
            'classname' => 'my_widget',
            'description' => 'A plugin for Kinsta blog readers',
        );
        parent::__construct( 'my_widget', 'My Widget', $widget_ops );

        try {
            $response = file_get_contents("https://www.googleapis.com/calendar/v3/calendars/" . get_field('benyon_cal_id', 'option') . "/events?key=" . get_field('benyon_cal_api_key', 'option'));
            $this->rawData = json_decode($response, true)['items'];
        } catch(Exception $e) {
            error_log("FAIL!");
            $this->rawData = array();
        }
    }

    // output the widget content on the front-end
    public function widget( $args, $instance ) {
        foreach ($this->rawData as &$value) {
            echo "<div>" . json_encode($value['summary']) . "</div>";
            echo "<div>" . json_encode($value['start']['dateTime']) . "</div>";
            echo "<div>" . json_encode($value['end']['dateTime']) . "</div>";
        }
    }

    // output the option form field in admin Widgets screen
    public function form( $instance ) {
    }

    // save options
    public function update( $new_instance, $old_instance ) {}
}