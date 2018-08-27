<?php
/*
Plugin Name: Challenges Post Type
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to create the challenges post type
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: UNLICENSED
*/


function benyon_challenges_create_post_type() {
    register_post_type( 'benyon-challenges',
        array(
            'labels' => array(
                'name' => __( 'Challenges' ),
                'singular_name' => __( 'Challenge' )
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-awards'
        )
    );
}
add_action( 'init', 'benyon_challenges_create_post_type' );