<?php

if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title' 	=> 'Calendar Settings',
        'menu_title'	=> 'Calendar',
        'menu_slug' 	=> 'benyon_cal_menu',
        'capability'	=> 'edit_posts',
        'redirect'		=> false
    ));
}

if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_5b26c1697e09d',
        'title' => 'Benyon Calendar Settings',
        'fields' => array(
            array(
                'key' => 'field_5b26c1f3b4d81',
                'label' => 'API Key',
                'name' => 'benyon_cal_api_key',
                'type' => 'text',
                'instructions' => 'An API key required for making the requests to Google calendar.',
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
                'key' => 'field_5b2as6c1f3b4d8112as',
                'label' => 'Calendar ID',
                'name' => 'benyon_cal_id',
                'type' => 'text',
                'instructions' => 'The public calendar ID to interrogate.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'horshamdojomentor@gmail.com',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'benyon_cal_menu',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));

endif;