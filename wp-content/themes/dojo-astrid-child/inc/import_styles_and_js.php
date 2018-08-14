<?php

function dojo_theme_enqueue_styles() {

//    Enqueue JS
    wp_enqueue_script( 'dojo_accordion', get_stylesheet_directory_uri() . '/js/accordion.js', array('jquery'));

//    Enqueue Styles
    wp_enqueue_style( 'dojo_css', get_stylesheet_directory_uri() . '/style.css' );

//    Enqueue parent styles
    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'dojo_theme_enqueue_styles' );
