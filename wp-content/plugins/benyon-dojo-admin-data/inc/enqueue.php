<?php

add_action( 'admin_enqueue_scripts', 'dojo_admin_enqueue_for_options' );

function dojo_admin_enqueue_for_options($hook) {
    // Load only on the correct admin page
    if($hook != 'toplevel_page_' . BENYON_DOJO_MENU_SLUG) {
        return;
    }

//    Style
    wp_enqueue_style( 'dojo_admin-styles', plugin_dir_url(__FILE__) . '../dojo_admin.css' );

//    Scripts
    wp_enqueue_script('jquery', plugin_dir_url(__FILE__) . '../js/jquery-3.3.1.min.js');
    wp_enqueue_script( 'dojo_admin-validation-tools', plugin_dir_url(__FILE__) . '../js/dojo_admin.js', array('jquery') );
}
