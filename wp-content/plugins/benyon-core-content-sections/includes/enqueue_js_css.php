<?php

function benyon_ccs_add_theme_scripts() {
//    STYLES
    wp_enqueue_style( 'benyon_ccs', plugin_dir_url( __FILE__ ) . '../benyon_ccs_styles.css' );
}

add_action( 'wp_enqueue_scripts', 'benyon_ccs_add_theme_scripts' );
