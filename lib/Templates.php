<?php

namespace Crockett95\JayDenton;

use Timber;
use TimberTerm;
use TimberUser;

class Templates {
  protected $theme;

  public function __construct($theme)
  {
    $this->theme = $theme;

    $this->timber = new Timber();
    Timber::$dirname = array('templates', 'templates/shared');

    $this->registerHooks();
  }

  public function templateInclude($template)
  {
    $this->selectAndLoadTemplate($template);

    return $template;
  }

  public function setContentWidth()
  {
    $GLOBALS['content_width'] = apply_filters(Theme::NAME . '_width', 640);
  }

  public function searchForm($form)
  {
    $context = Timber::get_context();
    $context['query'] = get_search_query();
    $context['placeholder'] = esc_attr__('Search', Theme::NAME);
    $context['label'] = __('Search', Theme::NAME);
    $context['submit'] = __('Search', Theme::NAME);

    $twigForm = Timber::fetch('searchform.twig', $context);

    return $twigForm;
  }

  public function addStringsToContext($data)
  {
    $data['strings'] = array(
      'copyright' => __('&copy; 2016 Jay Denton. All rights reserved.', Theme::NAME),
      'toggleNav' => __('Toggle navigation', Theme::NAME),
      'moreNews'  => __('More News', Theme::NAME)
    );

    return $data;
  }

  protected function registerHooks()
  {
    add_action('after_setup_theme', array($this, 'setContentWidth'), 0);
    add_filter('template_include', array($this, 'templateInclude'));
    add_filter('timber/context', array($this, 'addStringsToContext'));
    add_filter('get_search_form', array($this, 'searchForm'));
  }

  protected function loadIndexTemplate(&$templates)
  {
    $templates[] = 'index.twig';
  }

  protected function load404Template(&$templates)
  {
    $templates[] = '404.twig';
  }

  protected function loadSearchTemplate(&$templates)
  {
    $templates[] = 'search.twig';
  }

  protected function loadFrontPageTemplate(&$templates)
  {
    $templates[] = 'front-page.twig';
  }

  protected function loadHomeTemplate(&$templates)
  {
    $templates[] = 'home.twig';
  }

  protected function loadPostTypeArchiveTemplate(&$templates)
  {
    $post_type = get_query_var('post_type');

    if (is_array($post_type)) {
      $post_type = reset($post_type);
    }

    $obj = get_post_type_object($post_type);

    if (!$obj->has_archive) return;

    $this->loadArchiveTemplate($templates);
  }

  protected function loadArchiveTemplate(&$templates)
  {
    $post_types = array_filter((array)get_query_var('post_type'));

    if (count($post_types) == 1) {
      $post_type = reset($post_types);
      $templates[] = "archive-{$post_type}.twig";
    }

    $templates[] = 'archive.twig';
  }

  protected function loadTaxonomyTemplate(&$templates, &$context)
  {
    $term = get_queried_object();

    if (!empty($term->slug)) {
      $taxonomy = $term->taxonomy;
      $templates[] = "taxonomy-$taxonomy-{$term->slug}.twig";
      $templates[] = "taxonomy-$taxonomy.twig";
    }
    $templates[] = 'taxonomy.twig';

    $context['term'] =  new TimberTerm($term->id);
  }

  protected function loadAttachmentTemplate(&$templates)
  {
    $attachment = get_queried_object();

    if ($attachment) {
      if (false !== strpos($attachment->post_mime_type, '/')) {
        list($type, $subtype) = explode('/', $attachment->post_mime_type);
      } else {
        list($type, $subtype) = array($attachment->post_mime_type, '');
      }

      if (!empty($subtype)) {
        $templates[] = "{$type}-{$subtype}.twig";
        $templates[] = "{$subtype}.twig";
      }

      $templates[] = "{$type}.twig";
    }

    $templates[] = 'attachment.twig';
  }

  protected function loadSingleTemplate(&$templates)
  {
    $object = get_queried_object();

    if (!empty($object->post_type)) {
      $templates[] = "single-{$object->post_type}-{$object->post_name}.twig";
      $templates[] = "single-{$object->post_type}.twig";
    }
    $templates[] = 'single.twig';
  }

  protected function loadPageTemplate(&$templates)
  {
    $id = get_queried_object_id();
    $template = get_page_template_slug();
    $pagename = get_query_var('pagename');

    if (!$pagename && $id) {
      // If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
      $post = get_queried_object();

      if ($post) {
        $pagename = $post->post_name;
      }
    }

    if ($template && 0 === validate_file($template)) {
      $templates[] = $template;
    }

    if ($pagename) {
      $templates[] = "page-$pagename.twig";
    }

    if ($id) {
      $templates[] = "page-$id.twig";
    }

    $templates[] = 'page.twig';
  }

  protected function loadSingularTemplate(&$templates, &$context)
  {
    $templates[] = 'singular.twig';

    $context['post'] = Timber::get_posts()[0];
  }

  protected function loadCategoryTemplate(&$templates, &$context)
  {
    $category = get_queried_object();

    if (!empty($category->slug)) {
      $templates[] = "category-{$category->slug}.twig";
      $templates[] = "category-{$category->term_id}.twig";
    }

    $templates[] = 'category.twig';

    $context['category'] = new TimberTerm($category->id);
    $context['term'] = $context['category'];
  }

  protected function loadTagTemplate(&$templates, &$context)
  {
    $tag = get_queried_object();

    if (!empty($tag->slug)) {
      $templates[] = "tag-{$tag->slug}.twig";
      $templates[] = "tag-{$tag->term_id}.twig";
    }

    $templates[] = 'tag.twig';

    $context['tag'] = new TimberTerm($tag->id);
    $context['term'] = $context['tag'];
  }

  protected function loadAuthorTemplate(&$templates, &$context)
  {
    $author = get_queried_object();

    if ($author instanceof WP_User) {
      $templates[] = "author-{$author->user_nicename}.twig";
      $templates[] = "author-{$author->ID}.twig";
    }

    $templates[] = 'author.twig';

    if ($author instanceof WP_User) {
      $context['author'] = new TimberUser($author->ID);
    }
  }

  protected function loadDateTemplate(&$templates)
  {
    $templates[] = 'date.twig';
  }

  protected function loadPagedTemplate(&$templates)
  {
    $templates[] = 'paged.twig';
  }

  private function selectAndLoadTemplate($template)
  {
    $templates = array();
    $context = array();

    if (is_404()) $this->load404Template($templates, $context);
    if (is_search()) $this->loadSearchTemplate($teplates, $context);
    if (is_front_page()) $this->loadFrontPageTemplate($templates, $context);
    if (is_home()) $this->loadHomeTemplate($templates, $context);
    if (is_post_type_archive()) $this->loadPostTypeArchiveTemplate($templates, $context);
    if (is_tax()) $this->loadTaxonomyTemplate($templates, $context);
    if (is_attachment()) $this->loadAttachmentTemplate($templates, $context);
    if (is_single()) $this->loadSingleTemplate($templates, $context);
    if (is_page()) $this->loadPageTemplate($templates, $context);
    if (is_singular()) $this->loadSingularTemplate($templates, $context);
    if (is_category()) $this->loadCategoryTemplate($templates, $context);
    if (is_tag()) $this->loadTagTemplate($templates, $context);
    if (is_author()) $this->loadAuthorTemplate($templates, $context);
    if (is_date()) $this->loadDateTemplate($templates, $context);
    if (is_archive()) $this->loadArchiveTemplate($templates, $context);
    if (is_paged()) $this->loadPagedTemplate($templates, $context);

    $this->loadIndexTemplate($templates, $context);

    $context = array_merge(Timber::get_context(), $context);
    $context['posts'] = Timber::get_posts();

    Timber::render($templates, $context);
  }
}
