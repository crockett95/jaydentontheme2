<?php

namespace Crockett95\JayDenton;

class Assets {
  protected $theme;

  public function __construct($theme)
  {
    $this->theme = $theme;

    $this->registerHooks();
  }

  public function registerStyles()
  {
    if (is_admin()) {
      wp_enqueue_style(Theme::NAME . '-style', get_stylesheet_uri());
    } else {
      wp_register_style('shadows-into-light', 'https://fonts.googleapis.com/css?family=Shadows+Into+Light');
      wp_register_style('lato', 'https://fonts.googleapis.com/css?family=Lato:300,400,700');
      wp_enqueue_style(Theme::NAME . '-style',
        $this->theme->uri . '/assets/css/main.css',
        array('shadows-into-light', 'lato'),
        $this->theme->info('Version')
      );
    }
  }

  public function registerScripts()
  {
    wp_register_script('bootstrap',
      $this->theme->uri . '/bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js',
      array('jquery'),
      '3.3.6',
      true);

    wp_register_script('headroom',
      $this->theme->uri . '/bower_components/headroom.js/dist/headroom.min.js',
      array(),
      '0.7.0',
      true);

    wp_register_script('modernizr',
      $this->theme->uri . '/bower_components/modernizr-built/dist/modernizr.min.js',
      array(),
      '3.2.0',
      false);

    wp_register_script('stellar',
      $this->theme->uri . '/bower_components/jquery.stellar/jquery.stellar.min.js',
      array('jquery'),
      '0.6.2',
      true);

    wp_enqueue_script(Theme::NAME . '-scripts',
      $this->theme->uri . '/assets/js/main.js',
      array('headroom', 'modernizr', 'bootstrap', 'jquery', 'stellar'),
      $this->theme->info('Version'),
      true);
  }

  protected function registerHooks()
  {
    add_action('wp_enqueue_scripts', array($this, 'registerStyles'));
    add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
  }
}
