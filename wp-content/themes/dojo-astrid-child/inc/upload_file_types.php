<?php

function dojo_my_myme_types($mime_types){
    $mime_types['py'] = 'application/x-python-code'; //Python files
    return $mime_types;
}
add_filter('upload_mimes', 'dojo_my_myme_types', 1, 1);
