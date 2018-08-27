<?php
/*
Plugin Name: Restrict Editing Own Profile
Plugin URI: http://www.philosophydesign.com
Description: Restricts non-admin users from editing their own profile information.
Author: Tom Benyon
Version: 0.1.0
Author URI: tom.benyon.io
*/

add_action( 'admin_menu', 'stop_access_profile' );
function stop_access_profile() {
    remove_menu_page( 'profile.php' );
    remove_submenu_page( 'users.php', 'profile.php' );
    if(IS_PROFILE_PAGE === true && !(current_user_can('administrator'))) {
        wp_die( 'You are not permitted to change your own profile information. Blocked by "Restrict Editing Own Profile"' );
    }
}
