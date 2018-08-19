<?php

$benyon_ccs_button_data = array(
  array(
    'key' => 'field_5a9d108dd684c',
    'label' => 'Button Text',
    'name' => 'button_text',
    'type' => 'text',
    'instructions' => '',
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
    'key' => 'field_5a9d10a7d684d',
    'label' => 'Button Style',
    'name' => 'button_style',
    'type' => 'radio',
    'instructions' => 'Choose the button style that you are looking for:<br>
Primary - Gold texture with white text<br>
Secondary - Grey texture with black text',
    'required' => 0,
    'conditional_logic' => 0,
    'wrapper' => array(
      'width' => '',
      'class' => '',
      'id' => '',
    ),
    'choices' => array(
      'primary' => 'Primary',
      'secondary' => 'Secondary',
    ),
    'allow_null' => 0,
    'other_choice' => 0,
    'save_other_choice' => 0,
    'default_value' => '',
    'layout' => 'vertical',
    'return_format' => 'value',
  ),
  array(
    'key' => 'field_5a97f3e1cd144',
    'label' => 'Hyperlink',
    'name' => 'hyperlink',
    'type' => 'radio',
    'instructions' => 'This is where you can choose a hyperlink for the image. For links within this site use internal links. For links outside the site use external links.',
    'required' => 1,
    'conditional_logic' => 0,
    'wrapper' => array(
      'width' => '',
      'class' => '',
      'id' => '',
    ),
    'choices' => array(
      'internal' => 'Internal Link',
      'external' => 'External Link',
    ),
    'allow_null' => 0,
    'other_choice' => 0,
    'save_other_choice' => 0,
    'default_value' => '',
    'layout' => 'vertical',
    'return_format' => 'value',
  ),
  array(
    'key' => 'field_5a97f487cd145',
    'label' => 'Internal Link',
    'name' => 'internal_link',
    'type' => 'post_object',
    'instructions' => 'The internal item to link to.',
    'required' => 1,
    'conditional_logic' => array(
      array(
        array(
          'field' => 'field_5a97f3e1cd144',
          'operator' => '==',
          'value' => 'internal',
        ),
      ),
    ),
    'wrapper' => array(
      'width' => '',
      'class' => '',
      'id' => '',
    ),
    'post_type' => array(
      0 => 'post',
      1 => 'page',
      2 => 'social-posts',
    ),
    'taxonomy' => array(
    ),
    'allow_null' => 0,
    'multiple' => 0,
    'return_format' => 'id',
    'ui' => 1,
  ),
  array(
    'key' => 'field_5a97f4bccd146',
    'label' => 'External Link',
    'name' => 'external_link',
    'type' => 'url',
    'instructions' => 'The external hyperlink.',
    'required' => 1,
    'conditional_logic' => array(
      array(
        array(
          'field' => 'field_5a97f3e1cd144',
          'operator' => '==',
          'value' => 'external',
        ),
      ),
    ),
    'wrapper' => array(
      'width' => '',
      'class' => '',
      'id' => '',
    ),
    'default_value' => '',
    'placeholder' => '',
  )
  );
