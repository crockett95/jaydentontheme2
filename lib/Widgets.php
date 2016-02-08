<?php

namespace Crockett95\JayDenton;

use Timber;

class Widgets {
  protected $theme;

  public function __construct($theme)
  {
    $this->theme = $theme;

    $this->registerHooks();
  }

  public function registerWidgetAreas()
  {
    register_sidebar(array(
      'name'          => esc_html__('Homepage Widgets', Theme::NAME),
      'id'            => 'home_widgets',
      'description'   => '',
      'before_widget' => '<div class="col-md-4"><aside id="%1$s" class="widget well %2$s">',
      'after_widget'  => '</aside></div>',
      'before_title'  => '<h3 class="widget-title thin-top">',
      'after_title'   => '</h3>',
    ));
  }

  public function getWidgetsForHomepage($data)
  {
    if (!is_front_page()) return $data;

    $data['widgets'] = Timber::get_widgets('home_widgets');
    return $data;
  }

  public function addTagCloudClass($data)
  {
    return array_map(function ($datum) {
      $datum['class'] .= ' label label-primary';

      return $datum;
    }, $data);
  }

  protected function registerHooks()
  {
    add_action('widgets_init', array($this, 'registerWidgetAreas'));
    add_filter('timber/context', array($this, 'getWidgetsForHomepage'));
    add_filter('wp_generate_tag_cloud_data', array($this, 'addTagCloudClass'));
  }
}
