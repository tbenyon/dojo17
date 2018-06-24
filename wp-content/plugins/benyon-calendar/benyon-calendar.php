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
    }

    // output the widget content on the front-end
    public function widget( $args, $instance ) {
        $numResults = 6;
        if ( isset( $instance[ 'amountToView' ] ) ) {
            $numResults = intval($instance[ 'amountToView' ]);
        }
        $numResults += 1; //Google Max results seems to return 1 less than the max result

        $calendarEntries = 'maxResults=' . $numResults;

        try {
            $response = file_get_contents("https://www.googleapis.com/calendar/v3/calendars/" . get_field('benyon_cal_id', 'option') . "/events?key=" . get_field('benyon_cal_api_key', 'option') . "&" . $calendarEntries);
            $this->rawData = json_decode($response, true)['items'];
        } catch(Exception $e) {
            error_log("FAIL!");
            $this->rawData = array();
        }

        if ( isset( $instance[ 'title' ] ) ) {
            echo "<h3>" . $instance[ 'title' ] . "</h3>";
        }

        foreach ($this->rawData as &$value) {
            echo "<div>" . json_encode($value['summary']) . "</div>";
            echo "<div>" . json_encode($value['start']['dateTime']) . "</div>";
            echo "<div>" . json_encode($value['end']['dateTime']) . "</div>";
        }
    }

    // output the option form field in admin Widgets screen
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'wpb_widget_domain' );
        }

        if ( isset( $instance[ 'amountToView' ] ) ) {
            $amountToView = $instance[ 'amountToView' ];
        }
        else {
            $amountToView = __( '6', 'wpb_widget_domain' );
        }
// Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'amountToView' ); ?>"><?php _e( 'Number of Entries to Show:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'amountToView' ); ?>" name="<?php echo $this->get_field_name( 'amountToView' ); ?>" type="number" value="<?php echo esc_attr( $amountToView ); ?>" />
        </p>
        <?php
    }


    // save options
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['amountToView'] = ( ! empty( $new_instance['amountToView'] ) ) ? strip_tags( $new_instance['amountToView'] ) : '';
        return $instance;
    }
}