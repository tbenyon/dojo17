<?php

namespace BenyonContentSections;

/**
 * Class Content_Sections
 * @package BenyonContentSections
 */
class Content_Sections {
 private static $instance;
 private static $content_sections;

 public static function instance() {
   if( !isset(self::$instance) ) {
     $c = __CLASS__;
     self::$instance = new $c();
   }

   return self::$instance;
 }

 private function __clone() {}

 private function __constrcutor() {}

   /**
    *
    * Registers a new content section from a plugin
    *
    * @param string $json  JSON string of content section data
    * @param string $unique_section_id  A unique identifier. Only used internally. MUST be unique. Will also be used as the section slug.
    * @param string $label  This is the visible name of the section in the CMS
    * @param string $display  This is how the section will be displayed in the CMS. (optional, "block" is default)
    * @return boolean
    */
 public function register($json, $unique_section_id, $label, $view_path, $display = "block") {

   $sub_fields = json_decode($json, true);

   $additional_fields = self::build_default_fields($view_path, $unique_section_id);

   $sub_fields = array_merge($sub_fields, $additional_fields);

   self::$content_sections[$unique_section_id] = array(
       'key' => $unique_section_id,
       'name' => $unique_section_id,
       'label' => $label,
       'display' => $display,
       'sub_fields' => $sub_fields,
       'min' => '',
       'max' => '',
   );

   return true;
 }

 public function get_content_sections_view() {
   include BENYON_CS_VIEWS . '/content_sections_loop.php';
 }

 public function get_sections() {
   return self::$content_sections;
 }

 private function build_default_fields($view_path, $unique_section_id) {
   return array(
   array(
     'key' => $unique_section_id . 'benyon_cs_view_path',
     'label' => 'View Path',
     'name' => $unique_section_id . 'benyon_cs_view_path',
     'type' => 'text',
     'instructions' => 'This is not editable as it is set by developers in the code.',
     'required' => 0,
     'conditional_logic' => array(
       array(
         array(
           'field' => 'DO_NOT_SHOW',
           'operator' => '==',
           'value' => '1',
         ),
       ),
     ),
     'wrapper' => array(
       'width' => '',
       'class' => '',
       'id' => '',
     ),
     'default_value' => $view_path,
     'placeholder' => '',
     'prepend' => '',
     'append' => '',
     'maxlength' => '',
     'readonly' => 1,
   ),
    array(
       'key' => $unique_section_id . 'benyon_cs_section_id',
       'label' => 'Section ID',
       'name' => $unique_section_id . 'benyon_cs_section_id',
       'type' => 'text',
       'instructions' => 'A word with no spaces that can be used to target CSS for this specific section. This should only be modified by developers.',
       'required' => 0,
       'conditional_logic' => 0,
       'wrapper' => array(
         'width' => '',
         'class' => '',
         'id' => '',
       ),
       'default_value' => '',
       'placeholder' => '',
       'prepend' => '',
       'append' => '',
       'maxlength' => '',
     ),
     array(
        'key' => $unique_section_id . 'benyon_cs_section_classes',
        'label' => 'Section Classes',
        'name' => $unique_section_id . 'benyon_cs_section_classes',
        'type' => 'text',
        'instructions' => 'A series of classes, separated by spaces, that will be added to the section. This should only be modified by developers.',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'maxlength' => '',
      ),
    );
 }

 public function build_content_sections() {

   return array(
     'key' => 'group_5a900509a0d04',
     'title' => 'Content Sections',
     'fields' => array(
       array(
         'key' => 'field_5a9005304090e',
         'label' => 'Content Sections',
         'name' => 'benyon_content_sections',
         'type' => 'flexible_content',
         'instructions' => '',
         'required' => 0,
         'conditional_logic' => 0,
         'wrapper' => array(
           'width' => '',
           'class' => '',
           'id' => '',
         ),
         'layouts' => self::get_sections(),
         'button_label' => 'Add Section',
         'min' => '',
         'max' => '',
       ),
     ),
     'location' => array(
       array(
         array(
           'param' => 'post_type',
           'operator' => '==',
           'value' => 'post',
         ),
       ),
       array(
         array(
           'param' => 'post_type',
           'operator' => '==',
           'value' => 'page',
         ),
       ),
     ),
     'menu_order' => -5,
     'position' => 'normal',
     'style' => 'default',
     'label_placement' => 'top',
     'instruction_placement' => 'label',
     'hide_on_screen' => array(0 => 'the_content'),
     'active' => 1,
     'description' => '',
   );
 }
}
