<?php
/*
Plugin Name: Benyon Core Content Sections
Plugin URI: https://www.horshamcoderdojo.org.uk/
description: A plugin to build basic content Sections
Version: 0.1.0
Author: Tom Benyon
Author URI: https://tom.benyon.io/
License: UNLICENSED
*/

define('BENYON_CCS_BASE', __DIR__);
define('BENYON_CCS_INCLUDES', BENYON_CCS_BASE .'/includes');
define('BENYON_CCS_VIEWS', BENYON_CCS_BASE .'/views');
define('BENYON_CCS_DATA', BENYON_CCS_BASE . '/data');


require BENYON_CCS_DATA . '/image.php';
require BENYON_CCS_DATA . '/video.php';
require BENYON_CCS_DATA . '/wysiwyg.php';
require BENYON_CCS_DATA . '/button.php';

require BENYON_CCS_INCLUDES . '/enqueue_js_css.php';
require BENYON_CCS_INCLUDES . '/register_content_sections.php';
