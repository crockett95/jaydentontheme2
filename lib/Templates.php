<?php

namespace Crockett95\JayDenton;

use Timber;
use TimberTerm;
use TimberUser;
use TimberHelper;

class Templates {
  protected $theme;

  protected $content_width = 800;

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
    $GLOBALS['content_width'] = apply_filters(Theme::NAME . '_width', $this->content_width);
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
      'moreNews'  => __('More News', Theme::NAME),
      'message_404' => __('Sorry, we couldn\'t find what you\'re looking for.', Theme::NAME)
    );

    return $data;
  }

  protected function loadOptions(&$context)
  {
    $context['show_search_form'] = (bool) $this->theme->options->get_option('show_search', true);
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

  protected function load404Template(&$templates, &$context)
  {
    $templates[] = '404.twig';

    $context['title'] = __('Error 404', Theme::NAME);
  }

  protected function loadSearchTemplate(&$templates, &$context)
  {
    $templates[] = 'search.twig';

    $searchQuery = get_search_query();
    $context['search_query'] = $searchQuery;
    $context['title'] = sprintf(__('Search Results for: %s', Theme::NAME), $searchQuery);
  }

  protected function loadFrontPageTemplate(&$templates)
  {
    $templates[] = 'front-page.twig';
  }

  protected function loadHomeTemplate(&$templates, &$context)
  {
    $templates[] = 'home.twig';
    $context['title'] = $this->theme->options->get_option('home_title', 'Posts');
  }

  protected function loadPostTypeArchiveTemplate(&$templates, &$context)
  {
    $post_type = get_query_var('post_type');

    if (is_array($post_type)) {
      $post_type = reset($post_type);
    }

    $obj = get_post_type_object($post_type);

    if (!$obj->has_archive) return;

    $context['post_type'] = $obj;

    $this->loadArchiveTemplate($templates, $context);
  }

  protected function loadArchiveTemplate(&$templates, &$context)
  {
    $post_types = array_filter((array)get_query_var('post_type'));

    if (count($post_types) == 1) {
      $post_type = reset($post_types);
      $templates[] = "archive-{$post_type}.twig";
    }

    $templates[] = 'archive.twig';

    $context['title'] = get_the_archive_title();
    $context['description'] = get_the_archive_description();
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

  protected function loadSingleTemplate(&$templates, &$context)
  {
    $object = get_queried_object();

    if (!empty($object->post_type)) {
      $templates[] = "single-{$object->post_type}-{$object->post_name}.twig";
      $templates[] = "single-{$object->post_type}.twig";
    }
    $templates[] = 'single.twig';

    $context['comment_form'] = TimberHelper::get_comment_form(null, $this->getCommentFormArgs());
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
    $context['debug'] = defined('WP_DEBUG') && WP_DEBUG;

    $this->loadOptions($context);

    Timber::render($templates, $context);
  }

  protected function getCommentFormArgs()
  { 
    $req      = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    $html_req = ( $req ? " required='required'" : '' );
    $commenter = wp_get_current_commenter();

    return array(
      'title_reply_before' => '<h4>',
      'title_reply_after' => '</h4>',
      'fields' => array(
        'author' => '<div class="comment-form-author form-group">' .
          '<label for="author">' . __('Name') . ($req ? ' <span class="required">*</span>' : '') . '</label> ' .
          '<input id="author" class="form-control" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30" maxlength="245"' . $aria_req . $html_req . ' />' .
          '</p>',
        'email'  => '<div class="comment-form-email form-group">' .
          '<label for="email">' . __('Email') . ($req ? ' <span class="required">*</span>' : '') . '</label> ' .
          '<input id="email" class="form-control" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email']) . '" size="30" maxlength="100" aria-describedby="email-notes"' . $aria_req . $html_req  . ' />' .
          '</div>',
        'url'    => '<div class="comment-form-url form-group">' .
          '<label for="url">' . __('Website') . '</label> ' .
          '<input id="url" class="form-control" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" />' .
          '</div>',
        ),
      'comment_field' => '<div class="comment-form-comment form-group"><label for="comment">' .
        _x('Comment', 'noun') .
        '</label><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></div>',
      'must_log_in' => '<p class="must-log-in text-danger">' .
        sprintf(__('You must be <a href="%s">logged in</a> to post a comment.'), wp_login_url(apply_filters('the_permalink', get_permalink()))) .
        '</p>',
      'class_submit' => 'btn btn-block btn-primary',
    );
  }
}
