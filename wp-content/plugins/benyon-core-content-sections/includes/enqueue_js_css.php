<?php

function benyon_ccs_add_theme_scripts() {
//    STYLES
    wp_enqueue_style( 'benyon_ccs', plugin_dir_url( __FILE__ ) . '../benyon_ccs_styles.css' );

//    STYLES
    wp_enqueue_script( 'jquery', plugin_dir_url(__FILE__) . '../js/vendor/jquery-3.3.1.min.js');
    wp_enqueue_script( 'swipebox', plugin_dir_url(__FILE__) . '../js/vendor/jquery.swipebox.min.js', 'jquery' );
    wp_enqueue_script( 'benyon_swipebox', plugin_dir_url(__FILE__) . '../js/benyon_swipebox.js', ['jquery', 'swipebox'] );

}

add_action( 'wp_enqueue_scripts', 'benyon_ccs_add_theme_scripts' );
