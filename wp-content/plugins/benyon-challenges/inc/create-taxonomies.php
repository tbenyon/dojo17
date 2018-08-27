<?php

function benyon_challenges_create_difficulty_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Difficulty', 'taxonomy general name', 'textdomain' ),
        'singular_name'              => _x( 'Difficulty', 'taxonomy singular name', 'textdomain' ),
        'search_items'               => __( 'Search Difficulties', 'textdomain' ),
        'popular_items'              => __( 'Common Difficulties', 'textdomain' ),
        'all_items'                  => __( 'All Difficulties', 'textdomain' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Difficulty', 'textdomain' ),
        'update_item'                => __( 'Update Difficulty', 'textdomain' ),
        'add_new_item'               => __( 'Add New Difficulty', 'textdomain' ),
        'new_item_name'              => __( 'New Difficulty', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separate difficulties with commas', 'textdomain' ),
        'add_or_remove_items'        => __( 'Add or remove difficulties', 'textdomain' ),
        'choose_from_most_used'      => __( 'Choose from the most used difficulties', 'textdomain' ),
        'not_found'                  => __( 'No difficulties found.', 'textdomain' ),
        'menu_name'                  => __( 'Difficulties', 'textdomain' ),
    );

    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => false,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'difficulty' ),
        'capabilities' => array(
            'manage_terms' => '',
            'edit_terms' => '',
            'delete_terms' => '',
            'assign_terms' => 'edit_posts'
        ),
    );

    register_taxonomy( 'difficulty', 'challenges', $args );
}

add_action( 'init', 'benyon_challenges_create_difficulty_taxonomy', 0 );
