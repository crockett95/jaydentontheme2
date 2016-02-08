<?php

namespace Crockett95\JayDenton;

use Dotenv\Dotenv;

class Theme {
  const NAME = 'jaydenton';

  public $path;
  public $name;
  public $uri;

  public $assets;
  public $menus;
  public $templates;
  public $widgets;

  protected $dotenv;
  protected $themeInfo;

  public static function start($path)
  {
    return new Theme($path);
  }

  public function __construct($path = '')
  {
    if (!$path) $path = get_template_directory();

    $this->path = $path;
    $this->uri = get_template_directory_uri();
    $this->name = apply_filters(self::NAME . '_name', self::NAME);

    $this->dotenv = new Dotenv($this->path);
    $this->themeInfo = wp_get_theme();

    $this->options = new Options($this);

    $this->assets = new Assets($this);
    $this->menus = new Menus($this);
    $this->templates = new Templates($this);
    $this->widgets = new Widgets($this);

    if (is_admin()) {
      $this->editor = new Editor($this);
    }

    $this->registerHooks();
  }

  public function setup()
  {
    load_theme_textdomain(self::NAME, $this->path . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support('title-tag');

    /*
     * Enable support for Post Thumbnails on posts and pages.
     */
    add_theme_support('post-thumbnails');

    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support('html5', array(
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    ));

    /*
     * Enable support for Post Formats.
     * See https://developer.wordpress.org/themes/functionality/post-formats/
     */
    add_theme_support('post-formats', array(
      'aside',
      'image',
      'video',
      'quote',
      'link',
    ));
  }

  public function info($key)
  {
    if (!$key)
      return $this->themeInfo;

    return $this->themeInfo->$key;
  }

  protected function registerHooks()
  {
    add_action('after_setup_theme', array($this, 'setup'));
  }
}
