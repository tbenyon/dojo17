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
            'menu_icon' => 'dashicons-awards'
        )
    );
}
