<?php

namespace Crockett95\JayDenton;

use Timber;

class Editor {
  protected $theme;

  public function __construct($theme)
  {
    $this->theme = $theme;

    $this->registerHooks();
  }

  public function addStyles()
  {
    add_editor_style('https://fonts.googleapis.com/css?family=Shadows+Into+Light');
    add_editor_style('assets/css/editor.css');
  }

  public function setupTinyMce($settings)
  {
    echo "<!--\n";
    var_dump($settings);
    echo "-->";
    static $defaults = array();
    if (empty($defaults) && $settings['selector'] === '#content') {
        $defaults = $settings;
        $opts = '*,*[*]';
        if (!isset( $defaults['valid_elements'])) {
            $defaults['valid_elements'] = $opts;
        } else {
            $defaults['valid_elements'] .= ',' . $opts;
        }
    }
    if (!empty($defaults)) {
        $selector = $settings['selector'];
        $settings = $defaults;
        $settings['selector'] = $selector;
    }
    return $settings;
  }

  public function addResponsiveImageClass($class)
  {
    if (is_array($class)) {
      $class[] = 'img-responsive';
    } elseif (is_string($class)) {
      $class .= ' img-responsive';
    }

    return $class;
  }

  protected function registerHooks()
  {
    add_action('after_setup_theme', array($this, 'addStyles'));
    add_filter('tiny_mce_before_init', array($this, 'setupTinyMce'));
    add_filter('get_image_tag_class', array($this, 'addResponsiveImageClass'));
  }
}
