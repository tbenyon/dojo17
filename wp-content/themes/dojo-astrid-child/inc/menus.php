<?php
    function dojo_get_menu() {
        if (current_user_can('editor') || current_user_can('administrator')) {
            wp_nav_menu(array('theme_location' => 'primary-mentor', 'menu_id' => 'primary-menu-mentor'));
        } else {
            wp_nav_menu(array('theme_location' => 'primary', 'menu_id' => 'primary-menu'));
        }
    }

    add_action( 'after_setup_theme', 'dojo_register_menu' );

    function dojo_register_menu() {
        register_nav_menu( 'primary-mentor', __( 'Primary Mentor Menu', 'theme-text-domain' ) );
    }