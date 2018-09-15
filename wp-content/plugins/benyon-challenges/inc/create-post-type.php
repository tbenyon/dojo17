<?php

add_action( 'init', 'benyon_challenges_create_post_type' );

function benyon_challenges_create_post_type() {
    register_post_type( 'challenges',
        array(
            'labels' => array(
                'name' => __( 'Challenges' ),
                'singular_name' => __( 'Challenge' )
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-awards',
            'rewrite' => array( 'slug' => 'challenges', 'with_front' => false ),
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'comments' )
        )
    );
}
