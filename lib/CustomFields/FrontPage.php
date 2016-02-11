<?php

namespace Crockett95\JayDenton\CustomFields;

class FrontPage {
  public function __construct()
  {
    register_field_group(array (
      'id' => 'acf_home-page-content',
      'title' => 'Home Page Content',
      'fields' => array (
        array (
          'key' => 'field_56b687d395d70',
          'label' => 'Jumbotron',
          'name' => '',
          'type' => 'tab',
        ),
        array (
          'key' => 'field_56b686ff95d6d',
          'label' => 'Right Side Content',
          'name' => 'jaydenton_jumbotron_right',
          'type' => 'wysiwyg',
          'default_value' => '',
          'toolbar' => 'full',
          'media_upload' => 'yes',
        ),
        array (
          'key' => 'field_56b687fd95d71',
          'label' => 'First Content Section',
          'name' => '',
          'type' => 'tab',
        ),
        array (
          'key' => 'field_56b6881c95d72',
          'label' => 'Title',
          'name' => 'jaydenton_home_title1',
          'type' => 'text',
          'default_value' => '',
          'placeholder' => '',
          'prepend' => '',
          'append' => '',
          'formatting' => 'none',
          'maxlength' => 80,
        ),
        array (
          'key' => 'field_56b6874a95d6e',
          'label' => 'Content',
          'name' => 'jaydenton_home_section1',
          'type' => 'wysiwyg',
          'default_value' => '',
          'toolbar' => 'full',
          'media_upload' => 'yes',
        ),
        array (
          'key' => 'field_56b6886e95d74',
          'label' => 'Second Content Section',
          'name' => '',
          'type' => 'tab',
        ),
        array (
          'key' => 'field_56b6884795d73',
          'label' => 'Title',
          'name' => 'jaydenton_home_title2',
          'type' => 'text',
          'default_value' => '',
          'placeholder' => '',
          'prepend' => '',
          'append' => '',
          'formatting' => 'none',
          'maxlength' => 80,
        ),
        array (
          'key' => 'field_56b6877795d6f',
          'label' => 'Content',
          'name' => 'jaydenton_home_section2',
          'type' => 'wysiwyg',
          'default_value' => '',
          'toolbar' => 'full',
          'media_upload' => 'yes',
        ),
      ),
      'location' => array (
        array (
          array (
            'param' => 'page_type',
            'operator' => '==',
            'value' => 'front_page',
            'order_no' => 0,
            'group_no' => 0,
          ),
        ),
      ),
      'options' => array (
        'position' => 'acf_after_title',
        'layout' => 'no_box',
        'hide_on_screen' => array (
          0 => 'the_content',
          1 => 'custom_fields',
        ),
      ),
      'menu_order' => 0,
    ));
  }
}
