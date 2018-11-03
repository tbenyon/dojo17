<?php

function dojo_login_redirect() {
    return '/';
}

add_filter('login_redirect', 'dojo_login_redirect');