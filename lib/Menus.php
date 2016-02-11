<?php

namespace Crockett95\JayDenton;

use TimberMenu;
use Crockett95\JayDenton\CustomFields\NavItems;

class Menus {
  protected $theme;
  protected $customFields;

  public function __construct($theme)
  {
    $this->theme = $theme;

    $this->registerHooks();
    $this->customFields = new NavItems($this->getCustomFields());
  }

  public function addMenusToContext($data)
  {
    $headerMenu = new TimberMenu('primary');
    $this->addChildClasses($headerMenu->items);
    $data['primaryMenu'] = $headerMenu;
    $data['footerMenu']  = new TimberMenu('footer');

    if (is_front_page()) {
      $data['socialMenu']  = new TimberMenu('social');
    }

    return $data;
  }

  public function registerMenuLocations()
  {
    register_nav_menus(array(
      'primary' => esc_html__('Primary', Theme::NAME),
      'footer'  => esc_html__('Footer', Theme::NAME),
      'social'  => esc_html__('Social Media Links', Theme::NAME)
    ));
  }

  public function addActiveClass($classes)
  {
    if (in_array('current-menu-item', $classes) ||
        in_array('current-page-ancestor', $classes) ||
        in_array('current-menu-ancestor', $classes))
      $classes[]  =   'active';

    return $classes;
  }

  public function addChildClasses($items)
  {
    if (!is_array($items) || !count($items)) return;

    foreach ($items as $item) {
      if (is_array($item->children) && count($item->children)) {
        $item->add_class('dropdown');
        $this->addChildClasses($item->children);
      }
    }
  }

  protected function getCustomFields()
  {
    return array(
      'icon' => __('Font-Awesome icon (used only for social media)', Theme::NAME)
    );
  }

  protected function registerHooks()
  {
    add_action('after_setup_theme', array($this, 'registerMenuLocations'));
    add_filter('timber/context', array($this, 'addMenusToContext'));
    add_filter('nav_menu_css_class', array($this, 'addActiveClass'));
  }
}
