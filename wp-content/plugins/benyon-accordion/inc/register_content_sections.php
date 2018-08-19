<?php

add_action( 'plugins_loaded', 'benyon_acc_register_cs' );

function benyon_acc_register_cs() {
    if (class_exists('BenyonContentSections\Content_Sections')) {
        include BENYON_ACC_DATA . '/accordion_acf_data.php';
        $data_string = json_encode($benyon_acc_acf_data); // NEEDS WORK HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $view_path = BENYON_ACC_VIEWS . '/accordion.php';
        BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_accordion", "Accordion", $view_path, "block");
    }
}
