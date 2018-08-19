<?php

add_action( 'plugins_loaded', 'benyon_acc_register_cs' );

function benyon_acc_register_cs() {
    if (class_exists('BenyonContentSections\Content_Sections')) {
        $data_string = json_encode(array()); // NEEDS WORK HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $view_path = BENYON_ACC_VIEWS . '/accordion.php';
        BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_acc_button", "Accordion", $view_path, "block");
    }
}