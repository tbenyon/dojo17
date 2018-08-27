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

define('BENYON_CHALLENGES_POST_TYPE_BASE', __DIR__);


include BENYON_CHALLENGES_POST_TYPE_BASE . "/inc/create-post-type.php";
include BENYON_CHALLENGES_POST_TYPE_BASE . "/inc/create-taxonomies.php";

/*
 *  RADIO BUTTON DIFFICULTY
 *  Commented out this functionality for now - not in working state.
 *  Radio buttons won't work with current js
 *  Conecept for this is here: https://code.tutsplus.com/articles/how-to-use-radio-buttons-with-taxonomies--wp-24779
 *  github code for it here: https://github.com/stephenh1988/Radio-Buttons-for-Taxonomies
 *  // include BENYON_CHALLENGES_POST_TYPE_BASE . "/inc/create-radio-buttons.php";
*/