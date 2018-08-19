<?php

if (class_exists('BenyonContentSections\Content_Sections')) {
  // Add image cotent section
  $data_string = json_encode($benyon_ccs_image_data);
  $view_path = BENYON_CCS_VIEWS . '/image.php';
  BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_ccs_image", "Image", $view_path, "block");

  // Add video cotent section
  $data_string = json_encode($benyon_ccs_video_data);
  $view_path = BENYON_CCS_VIEWS . '/video.php';
  BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_ccs_video", "Video", $view_path, "block");

  // Add WYSIWYG cotent section
  $data_string = json_encode($benyon_ccs_wysiwyg_data);
  $view_path = BENYON_CCS_VIEWS . '/wysiwyg.php';
  BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_ccs_wysiwyg", "WYSIWYG", $view_path, "block");

  // Add button cotent section
  $data_string = json_encode($benyon_ccs_button_data);
  $view_path = BENYON_CCS_VIEWS . '/button.php';
  BenyonContentSections\Content_Sections::instance()->register($data_string, "benyon_ccs_button", "Button", $view_path, "block");
}
