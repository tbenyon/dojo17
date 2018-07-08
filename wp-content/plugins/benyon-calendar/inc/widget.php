<?php

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

        $query_string_items = [
          'maxResults=' . $numResults,
          'timeMin=' . date('Y-m-d\TH:i:s\Z'),
          'singleEvents=True',
          'orderBy=startTime',
          'key=' . get_field('benyon_cal_api_key', 'option')
        ];
        try {
            $url =
                "https://www.googleapis.com/calendar/v3/calendars/" .
                get_field('benyon_cal_id', 'option') .
                "/events?" .
                implode('&', $query_string_items);

            $response = file_get_contents($url);
            $this->rawData = json_decode($response, true)['items'];
        } catch(Exception $e) {
            error_log("FAIL!");
            $this->rawData = array();
        }

        echo "<div class='benyon-calendar-widget-container'>";

        if ( isset( $instance[ 'title' ] ) && $instance[ 'title' ] !== "" ) {
            echo "<h3>" . $instance[ 'title' ] . "</h3>";
        }

        foreach ($this->rawData as &$value) {
            $start = new DateTime($value['start']['dateTime']);
            $end = new DateTime($value['end']['dateTime']);
            $sameDay = $this->is_same_day($start, $end);
            if ($sameDay) {
                $startFormat = "jS F H:i";
                $endFormat = "H:i";
            } else {
                $startFormat = $endFormat = "jS F";
            }

            echo "<div class='event'>";
            echo "<div class='event-title'>" . $value['summary'] . "</div>";
            echo "<div class='event-times'>" . $start->format($startFormat) . " - " . $end->format($endFormat) . "</div>";
            echo "</div>";

        }

        echo "</div>";

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

    // Check if start date and end date are in the same day
    private function is_same_day($start, $end) {
        $firstDate = $start->format('Y-m-d');
        $secondDate = $end->format('Y-m-d');
        return $firstDate === $secondDate;
    }
}