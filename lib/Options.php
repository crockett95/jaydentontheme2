<?php

namespace Crockett95\JayDenton;

use Twig_SimpleFunction;
use Twig_Loader_Filesystem;
use Twig_Environment;

class Options {
  /**
   * Namespace for options
   *
   * @var     string
   */
  public $namespace;

  public $theme;

  /**
   * The version of this plugin.
   *
   * @access  private
   * @var     array   $admin_pages    Track all registered admin pages by short name
   */
  protected $admin_pages = array();

  /**
   * The version of this plugin.
   *
   * @access  private
   * @var     array   $admin_page_suffixes    Track admin page by generated suffix
   */
  protected $admin_page_suffixes = array();

  protected $twigLoader;
  protected $twig;

  public function __construct($theme)
  {
    $this->theme = $theme;
    $this->namespace = Theme::NAME;

    $this->registerHooks();

    $this->twigLoader = new Twig_Loader_Filesystem($this->theme->path . '/templates');
    $this->twig = new Twig_Environment($this->twigLoader, array(
        'cache' => $this->theme ->path . '/.tmp',
    ));
    $this->addSettingsFunctions();
  }

  protected function registerHooks()
  {
    add_action('admin_init', array($this, 'registerSettings'));
    add_action('admin_menu', array($this, 'registerOptionsPage'));
  }

  public function addSettingsFunctions()
  {
    $doSettingsSections = new Twig_SimpleFunction('do_settings_sections', function ($slug) {
      do_settings_sections($slug);
    });

    $settingsFields = new Twig_SimpleFunction('settings_fields', function ($slug) {
      settings_fields($slug);
    });

    $this->twig->addFunction($doSettingsSections);
    $this->twig->addFunction($settingsFields);
  }

  public function registerOptionsPage()
  {
    $this->add_admin_page(__('Customizable Theme Options', Theme::NAME),
      __('Theme Options', Theme::NAME),
      'options',
      'options.twig',
      'theme',
      null,
      'edit_theme_options'
      );
  }

  public function registerSettings()
  {
    $this->add_settings_section('general', __('General Options', Theme::NAME), 'options');

    $this->add_settings_field('home_title',
      __('Blog Posts Page Title', Theme::NAME),
      'options',
      'general',
      'text',
      null,
      array('regular-text'));

    $this->add_settings_field('show_search',
      __('Show Search Form?', Theme::NAME),
      'options',
      'general',
      'checkbox');

    $this->add_settings_field('show_widgets',
      __('Show Widget Area on Front Page?', Theme::NAME),
      'options',
      'general',
      'checkbox');
  }

  /**
   * Add admin pages with namespacing and manage the hook suffixes for callbacks
   *
   * @param   string  $title  The page title, untranslated
   * @param   string  $link   The text for the admin menu link
   * @param   string  $slug   The short slug for the page
   * @param   string  $view   The view in the admin/partials directory (without `.php`)
   * @param   string  $type   The page type, used to call the correct function
   * @param   string  $parent     The parent page (used for `submenu` type only)
   * @param   string  $capability     The capabilities required for the page
   */
  protected function add_admin_page(
    $title,
    $link,
    $slug,
    $view,
    $type = 'options',
    $parent = null,
    $capability = 'manage_options'
  ) {
    $hook_suffix = '';
    $new_slug = '';

    switch ($type) {
      case 'dashboard':
      case 'posts':
      case 'media':
      case 'pages':
      case 'comments':
      case 'theme':
      case 'plugins':
      case 'users':
      case 'management':
      case 'options':
      case 'menu':
      $this->admin_pages[ $slug ] = "{$this->namespace}_$slug";
      $add_function = "add_{$type}_page";

      $hook_suffix = $add_function(
        $title,
        $link,
        $capability,
        $this->admin_pages[ $slug ],
        array($this, 'do_admin_page')
        );
      break;

      case 'submenu':
      if (isset($this->admin_pages[ $parent ]) ) {
        $parent = $this->admin_pages[ $parent ];
      }

      if (false !== strpos($parent, $this->namespace) ) {
        $this->admin_pages[ $slug ] = $slug;
      } else {
        $this->admin_pages[ $slug ] = "{$this->namespace}_$slug";
      }

      $hook_suffix = add_submenu_page(
        $parent,
        $title,
        $link,
        $capability,
        $this->admin_pages[ $slug ],
        array($this, 'do_admin_page')
        );
      break;

      default:
      throw new Exception("Unknown page type: $type", 1);

    }

    $settings['capability'] = $capability;
    $settings['view'] = $view;
    $settings['slug'] = $this->admin_pages[ $slug ];
    $settings['title'] = $title;


    $this->admin_page_suffixes[ $hook_suffix ] = $settings;
  }

  /**
   * Add settings sections
   *
   * @param   string  $slug   The slug for the section
   * @param   string  $title  The untranslated section title
   * @param   string  $page   The page slug
   */
  public function add_settings_section($slug, $title, $page) {
    if (isset($this->admin_pages[ $page ]) ) {
      $page = $this->admin_pages[ $page ];
    }

    add_settings_section(
      $slug,
      $title,
      array($this, 'do_settings_section'),
      $page
      );
  }

  /**
   * Callback for admin pages
   */
  public function do_admin_page() {
    $settings = $this->admin_page_suffixes[ current_filter() ];
    if (! current_user_can($settings['capability']) ) {
      wp_die(__('You do not have sufficient permissions to access this page') );
    }

    $context = array(
      'form_action' => admin_url('options.php'),
      'submit_button' => get_submit_button()
    );

    $context = array_merge($context, $settings);

    $template = $this->twig->loadTemplate($settings['view']);
    echo $template->render($context);
  }

  /**
   * Callback for settings sections
   */
  public function do_settings_section($settings) {
      //
  }

  /**
   * Add a settings field, with namespace and settings for callback
   *
   * @param   string  $id     The ID for the field without namespacing
   * @param   string  $label  The untranslated label for the field
   * @param   string  $page   The page name without namespacing
   * @param   string  $section    The name of the section
   * @param   string  $type   The field type
   * @param   array   $classes    The classes to be applied to the field
   */
  public function add_settings_field(
    $id,
    $label,
    $page,
    $section,
    $type = 'text',
    $callback = null,
    $classes = array()
  ) {
    if (isset($this->admin_pages[ $page ]) ) {
      $page = $this->admin_pages[ $page ];
    }

    $args = array(
      'type' => $type,
      'name' => $id,
      'label' => $label,
      'classes' => $classes,
      'label_for' => $this->get_option_name($id)
      );

    add_settings_field(
      $this->get_option_name($id),
      $label,
      array($this, 'make_settings_field'),
      $page,
      $section,
      $args
      );

    if (null === $callback) {
      register_setting($page, $this->get_option_name($id));
    } else {
      register_setting($page, $this->get_option_name($id), array($this, $callback) );
    }
  }

  /**
   * Returns the value of the option
   *
   * @param   string  $name   The name of the option without namespacing
   * @return  mixed   The value of the option
   */
  public function get_option($name, $default = null) {
    return get_option($this->get_option_name($name), $default);
  }

  /**
   * Convenience method for namespaced options
   *
   * @param   string  $name   The name of the option without namespacing
   * @return  string  The name of the option with namespacing
   */
  protected function get_option_name($name) {
    return "{$this->namespace}_$name";
  }

  /**
   * Makes the settings field
   *
   * @param   array   $args   The array of arguments passed in
   */
  public function make_settings_field($args) {
    $type = $args['type'];
    $id = $args['name'];
    $label = $args['label'];
    $classes = $args['classes'];

    $value = $this->get_option($id);
    $additionalAttributes = array();

    switch ($type) {
      case 'checkbox':
        if ($value) $additionalAttributes[] = 'checked="checked"';
        $value = '1';
      case 'text':
      default:
      return printf(
        '<input id="%1$s" name="%1$s" class="%2$s" value="%3$s" type="%4$s" %5$s />',
        esc_attr($this->get_option_name($id)),
        implode(' ', $classes),
        esc_attr($value),
        $type,
        implode(' ', $additionalAttributes)
        );
    }
  }
}
