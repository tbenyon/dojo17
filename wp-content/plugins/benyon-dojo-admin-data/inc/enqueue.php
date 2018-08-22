<?php

add_action( 'admin_enqueue_scripts', 'dojo_admin_enqueue_for_options' );

function dojo_admin_enqueue_for_options($hook) {
    // Load only on the correct admin page
    if($hook != 'toplevel_page_' . BENYON_DOJO_MENU_SLUG) {
        return;
    }

//    Style
    wp_enqueue_style( 'datatables', plugin_dir_url(__FILE__) . '../styles/jquery.dataTables.min.css' );
    wp_enqueue_style( 'dojo-admin-styles', plugin_dir_url(__FILE__) . '../dojo_admin.css' );

//    Scripts
    wp_enqueue_script('jquery', plugin_dir_url(__FILE__) . '../js/jquery-3.3.1.min.js');
    wp_enqueue_script( 'chartjs', plugin_dir_url(__FILE__) . '../js/chart.js', array('jquery') );
    wp_enqueue_script( 'datatables', plugin_dir_url(__FILE__) . '../js/jquery.dataTables.min.js', array('jquery') );
    wp_enqueue_script( 'dojo-admin-validation-tools', plugin_dir_url(__FILE__) . '../js/dojo_admin.js', array('jquery') );
    wp_enqueue_script( 'dojo-admin-datatable-init', plugin_dir_url(__FILE__) . '../js/dojo_datatable_init.js', array('jquery') );
}
