<?php
function benyon_cal_enq_scripts() {
    wp_enqueue_style('benyon-calendar', plugin_dir_url(BENYON_CAL_PLUGIN_FILE_PATH) . '/benyon-calendar.css');
}

add_action( 'wp_enqueue_scripts', 'benyon_cal_enq_scripts' );
