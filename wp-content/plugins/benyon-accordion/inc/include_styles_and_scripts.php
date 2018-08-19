<?php
function benyon_acc_enq_scripts() {
    wp_enqueue_style('benyon-accordion', plugin_dir_url(BENYON_ACC_PLUGIN_FILE_PATH) . '/benyon-accordion.css');

    wp_enqueue_script( 'jquery', plugin_dir_url(BENYON_ACC_PLUGIN_FILE_PATH) . '/js/vendor/jquery-3.3.1.min.js');

    wp_enqueue_script( 'benyon_accordion', plugin_dir_url(BENYON_ACC_PLUGIN_FILE_PATH) . '/js/accordion.js', array('jquery'));
}

add_action( 'wp_enqueue_scripts', 'benyon_acc_enq_scripts' );
