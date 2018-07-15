<?php

class My_Widget extends WP_Widget {

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
        $rawData = $this->get_raw_calendar_data();
        $events = array();

        foreach ($rawData as $eventData) {
            array_push(
              $events,
              array(
                "event_summary"   => $eventData['summary'],
                "event_date_time" => $this->get_date_time_string($eventData),
                "cancellation"    => stripos($eventData['summary'], 'no') !== false
              )
            );
        }

        include BENYON_CAL_VIEWS . '/widget.php';
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

    private function get_date_time_string($eventData) {
        $start = new DateTime($eventData['start']['dateTime']);
        $end = new DateTime($eventData['end']['dateTime']);
        $sameDay = $this->is_same_day($start, $end);
        if ($sameDay) {
            $startFormat = "jS F H:i";
            $endFormat = "H:i";
        } else {
            $startFormat = $endFormat = "jS F";
        }
        return $start->format($startFormat) . " - " . $end->format($endFormat);
    }

    private function get_raw_calendar_data() {
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
            return json_decode($response, true)['items'];
        } catch(Exception $e) {
            error_log("FAILED TO GET CALENDAR DATA!");
            return array();
        }
    }
}